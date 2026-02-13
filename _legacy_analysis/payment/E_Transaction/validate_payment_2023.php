<?php
require_once("/home/prostage/connections/config.php");
require_once '/home/prostage/www/params.php';
require_once "/home/prostage/www/debug.php";
require_once ROOT . "/display_error.php";
require_once APP . "logging/Logging.php";
require_once APP . "logging/LogCommission.php";
require_once APP . "logging/LogPayment.php";
require_once APP . "payment/E_Transaction/E_TransactionPayment_2023.php";
require_once APP . "payment/E_Transaction/E_TransactionError.php";
require_once APP . "payment/Utils/SessionManage.php";
require_once APP . 'order/services/RetrieveFullOrderByStageStudent.php';
require_once APP . 'order/services/GenerateReferenceOrder.php';
require_once APP . 'payment/services/email/SendTicketPaymentEmail.php';
require_once APP . 'payment/services/email/SendPaymentSuccessEmail.php';
require_once APP . 'payment/services/UpdateStagePaymentData.php';
require_once APP . 'stage/services/IsStagePlaceAvailableForSale.php';
require_once APP . 'upsell/services/RetrieveNextUpSellToPay.php';
require_once APP . 'upsell/services/RetrieveOrderBumpUpsell.php';
require_once APP . 'funnel/services/RetrieveFunnelByType.php';
require_once APP . 'upsell/services/RetrieveUpsellById.php';
require_once APP . 'student/services/RetrieveStudentById.php';
require_once APP . 'Api/Sms/sendSMS.php';


$data = [];

foreach ($_GET as $key => $value) {
    $param = explode('_', $key);
    if ($param[0] == "d2305") {
        session_id($param[1]);
        session_start();
        $data = SessionManage::retrieveSessionData($param[1]);
    }
}

$eTransaction = new E_TransactionPayment();
$arrData = SessionManage::parseSessionData($data);
$page_redirection = '';

list(
    $studentId,
    $stageId,
    $orderId,
    $memberId,
    $email,
    $cardNumber,
    $cardExpiry,
    $cardCVC,
    $isOrderBump,
    $funnelId
    ) = SessionManage::initPaymentData($arrData);

$funnel = (new RetrieveFunnelByType())->__invoke('stage', $mysqli);
$upsellIdToPay = (new RetrieveNextUpSellToPay())->__invoke($studentId, $mysqli, $funnel->id);

if (!empty($_POST) && !empty($data)) {
    if (
        (isset($_POST['ID3D']) && $_POST['ID3D'] != '')
    ) {

        $ID3D = $_POST['ID3D'];

        $_SESSION["paiement_error"] = '';
        $_SESSION["cardNumber"] = $cardNumber;
        $_SESSION["cardCVC"] = $cardCVC;
        $_SESSION["cardExpiry"] = $cardExpiry;
        $_SESSION['id_stagiaire'] = $studentId;
        $_SESSION['i_stagiaireId'] = $studentId;
        $_SESSION['i_stageId'] = $stageId;
        $_SESSION['i_membreId'] = $memberId;
        $_SESSION['memberId'] = $memberId;
        $_SESSION['stageId'] = $stageId;
        $_SESSION['studentId'] = $studentId;
        $_SESSION['funnelId'] = $funnelId;

        $logPayment = new LogPayment();
        $logCommission = new LogCommission();

        $isPlaceAvailableForSale = (new IsStagePlaceAvailableForSale())->__invoke($stageId, $mysqli);
        $stage = (new RetrieveFullOrderByStageStudent())->__invoke($studentId, $stageId, $mysqli);
        $amount = $stage->paiement;
        //bugs2023
        $memberId = $stage->member_id;
        $arrReference = (new GenerateReferenceOrder())->__invoke($studentId, $mysqli);
        $isAlreadyBuyStage = false;

        if (
            $stage->status == 'inscrit' &&
            $stage->supprime == 0 &&
            $stage->numappel != '' &&
            $stage->numtrans != ''
        ) {
            $isAlreadyBuyStage = true;
        }

        if (
            empty($arrReference) ||
            !$isPlaceAvailableForSale ||
            $isAlreadyBuyStage
        ) {
            $_SESSION["paiement_error"] = $isAlreadyBuyStage ? "<div style='color:red;'>Vous êtes déjà inscrit à un stage.</div>" : "<div style='color:red;'>Nous n'avons plus de place de disponible sur ce stage, veuillez en chosir un autre.</div>";
            $page_redirection = 'https://www.reservermonstagepermis.fr/fiche.php?id=' . $studentId . '&s=' . $stageId . '&m=' . $memberId;
            header('Content-type: text/html; charset=utf-8'); // make sure this is set
            header('Location:' . $page_redirection);
            flush();
            ob_flush();
            die();
        }

        $reference = $arrReference['reference'];
        $numSuivi = $arrReference['num_suivi'];
        
        $arrResponse = $eTransaction->validateTransaction(
            $amount,
            $reference,
            $cardNumber,
            $cardExpiry,
            $cardCVC,
            $ID3D
        );

        $response = $arrResponse['response'];

        $codereponse = intval($eTransaction->decodeResponse($response, 'codereponse'));
        $numTrans = $eTransaction->decodeResponse($response, 'numtrans');
        $numAppel = $eTransaction->decodeResponse($response, 'numappel');
        $autorisation = $eTransaction->decodeResponse($response, 'autorisation');

        list($commentaire, $msg) = $eTransaction->parseETransactionComment($response);

        if ($codereponse != "00000") {

            $Error_Etransaction = new E_TransactionError();

            $_SESSION["paiement_error"] = "<div style='color:red;'>" . $Error_Etransaction->parseErrorCode($codereponse) . "</div>";

            $logPayment->errorPaymentMessage($commentaire,
                $msg,
                " - STAGE - Reference=" . $reference." - ID3D=".$ID3D,
                $studentId,
                $amount,
                $email,
                $cardNumber
            );

            $page_redirection = 'https://www.reservermonstagepermis.fr/fiche.php?id=' . $studentId . '&s=' . $stageId . '&m=' . $memberId;

        }

        if ($codereponse == "00000") {

            $autorisation = $eTransaction->decodeResponse($response, 'autorisation');

            SessionManage::saveSessionData($arrData, session_id());

            $logPayment->successPaymentMessage(
                $commentaire,
                $msg,
                " - STAGE - Reference = " . $reference,
                $studentId,
                $amount,
                $email
            );

            $logCommission->loggingCommission($studentId, $stageId, $stage->partenariat, $stage->stage_commission);

            (new SendTicketPaymentEmail())->__invoke(
                $reference,
                $autorisation,
                $amount,
                $email
            );

            $studentOBJ = (new RetrieveStudentById())->__invoke($studentId, $mysqli);
            $arrPhones[] = $studentOBJ->tel;

            // TODO update webservice RPPC after complete information,

            (new UpdateStagePaymentData())->__invoke(
                $stageId,
                $studentId,
                $stage->member_id,
                $orderId,
                $autorisation,
                $cardNumber,
                $numAppel,
                $numTrans,
                $stage->partenariat,
                $stage->stage_commission,
                $reference,
                $numSuivi,
                $mysqli
            );

            (new SendPaymentSuccessEmail())->__invoke($studentId, $stage->member_id);

            if ($isOrderBump == 1) {
                $upsellOrderBump = (new RetrieveUpsellById())->__invoke($funnel->order_bump_id, $mysqli);
                $page_redirection = 'https://www.reservermonstagepermis.fr/bump.php?upsell='. $upsellOrderBump->id .'&s=' . session_id();
            } else {
                if ($upsellIdToPay) {
                    $page_redirection = 'https://www.reservermonstagepermis.fr/upsell.php?upsell='. $upsellIdToPay .'&s=' . session_id();
                } else {
                    $page_redirection = 'https://www.reservermonstagepermis.fr/confirmation.php?&s=' . session_id();
                }
            }

            /*if (!DEBUG){
                $key = md5($studentId . '!psp13#');
                $key = substr($key, 0, 5);
                $url_join = HOST . "/es/login.php?id=$studentId&k=$key";
                try {
                    (new sendSMS())->sendSMS(
                        "Bonjour " . ucfirst($studentOBJ->prenom) . ", merci pour votre inscription au stage de récupération de points. Complétez votre dossier au plus vite sur votre Espace Stagiaire en cliquant sur le lien suivant " . $url_join,
                        $arrPhones
                    );
                } catch (\Exception $e) {
                }
            }*/

        }
    }
} else {
    $_SESSION["paiement_error"] = "<div style='color:red;'>Votre paiement n'a pas pu être traité</div>";
}


$mysqli->close();

header('Content-type: text/html; charset=utf-8'); // make sure this is set
header('Location:' . $page_redirection);
flush();
ob_flush();
die();
