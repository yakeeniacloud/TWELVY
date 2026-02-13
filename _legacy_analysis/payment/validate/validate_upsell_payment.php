<?php
require_once("/home/prostage/connections/config.php");
require_once "/home/prostage/www/params.php";
require_once ROOT . "/debug.php";
//require_once ROOT . "/display_error.php";
require_once APP . "logging/Logging.php";
require_once APP . "logging/LogCommission.php";
require_once APP . "logging/LogPayment.php";
require_once APP . "payment/E_Transaction/E_TransactionPayment.php";
require_once APP . "payment/E_Transaction/E_TransactionError.php";
require_once APP . "payment/Utils/SessionManage.php";
require_once APP . 'payment/services/email/SendTicketPaymentEmail.php';
require_once APP . 'upsell/services/RetrieveUpsellById.php';
require_once APP . 'upsell/services/RetrieveNextUpSellToPay.php';
require_once APP . 'order/applications/command/RetrieveNextUpsellOrder.php';
require_once APP . 'funnel/services/RetrieveFunnelById.php';
require_once APP . 'student/services/UpdateOneFieldStudent.php';
require_once ROOT . '/mails_v3/mail_echec_paiement.php';

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
    $upsellId,
    $orderId,
    $reference,
    $email,
    $cardNumber,
    $cardExpiry,
    $cardCVC,
    $funnelId
    ) = SessionManage::initPaymentUpsellData($arrData);


if (!empty($_POST) && !empty($data)) {
    if (
        (isset($_POST['ID3D']) && $_POST['ID3D'] != '')
    ) {

        $ID3D = $_POST['ID3D'];

        $_SESSION["paiement_error"] = '';
        $_SESSION["cardNumber"] = $cardNumber;
        $_SESSION["cardCVC"] = $cardCVC;
        $_SESSION["cardExpiry"] = $cardExpiry;

        $upsell = (new RetrieveUpsellById())->__invoke($upsellId, $mysqli);
        $funnel = (new RetrieveFunnelById())->__invoke($funnelId, $mysqli);

        $amount = $upsell->prix_remise;


        if ($upsell->type == 'twelvy_app' || $upsell->type == 'alerte_point') {

            $arrResponse = $eTransaction->validateTransactionAbonnement(
                $amount,
                $reference,
                $studentId,
                $cardNumber,
                $cardExpiry,
                $cardCVC,
                $ID3D
            );

            $autorisation = 'XXXXXX';
            $arrData['token_abonne'] = $arrResponse['token'];
            $arrData['numAppel'] = $arrResponse['numAppel'];
            $arrData['numTrans'] = $arrResponse['numTrans'];
            $arrData['referenceAbonne'] = $arrResponse['referenceAbonne'];

            SessionManage::saveSessionData($arrData, session_id());

            (new SendTicketPaymentEmail())->__invoke($reference, $autorisation, $amount, $email);

            (new UpdateOneFieldStudent())->__invoke(
                $studentId,
                'e_transaction_token',
                $arrData['token_abonne'],
                $mysqli
            );

            require APP . 'payment/update/update_sell_payment_data.php';

            $page_redirection = (new RetrieveNextUpsellOrder())->__invoke(
                $studentId,
                session_id(),
                $upsellId,
                false,
                $mysqli,
                $funnelId
            );

        } else {

            $arrResponse = $eTransaction->validateTransaction(
                $amount,
                $reference,
                $cardNumber,
                $cardExpiry,
                $cardCVC,
                $ID3D
            );

            $response = $arrResponse['response'];
            $logPayment = new LogPayment();
            $logCommission = new LogCommission();

            $codereponse = intval($eTransaction->decodeResponse($response, 'codereponse'));
            $numTrans = $eTransaction->decodeResponse($response, 'numtrans');
            $numAppel = $eTransaction->decodeResponse($response, 'numappel');

            list($commentaire, $msg) = $eTransaction->parseETransactionComment($response);

            if ($codereponse != "00000" || empty($numTrans) || empty($numAppel)) {

                $Error_Etransaction = new E_TransactionError();

                $_SESSION["paiement_error"] = "<div style='color:red;'>" . $Error_Etransaction->parseErrorCode($codereponse) . "</div>";

                $logPayment->errorPaymentMessage($commentaire,
                    $msg,
                    " - UPSELL - Reference = " . $reference,
                    $studentId,
                    $amount,
                    $email,
                    $cardNumber
                );

                $page_redirection = '/page_upsell.php?upsell=' . $upsellId . '&s=' . session_id();
                
                $data['redirection'] = $page_redirection.'&order=5';
                $data['email'] = $email;
                $data['id_stage'] = 0;
                $data['id_stagiaire'] = $studentId;
                mail_echec_paiement($data);

            } else {

                $autorisation = $eTransaction->decodeResponse($response, 'autorisation');

                SessionManage::saveSessionData($arrData, session_id());

                $logPayment->successPaymentMessage(
                    $commentaire,
                    $msg,
                    " - UPSELL - Reference = " . $reference,
                    $studentId,
                    $amount,
                    $email
                );

                (new SendTicketPaymentEmail())->__invoke($reference, $autorisation, $amount, $email);

                require APP . 'payment/update/update_sell_payment_data.php';

                $page_redirection = (new RetrieveNextUpsellOrder())->__invoke(
                    $studentId,
                    session_id(),
                    $upsellId,
                    false,
                    $mysqli,
                    $funnelId
                );
            }
        }

    }

} else {
    $_SESSION["paiement_error"] = "<div style='color:red;'>Votre paiement n'a pas pu être traité</div>";
}


header('Content-type: text/html; charset=utf-8'); // make sure this is set
header('Location:' . $page_redirection);
flush();
ob_flush();
die();
