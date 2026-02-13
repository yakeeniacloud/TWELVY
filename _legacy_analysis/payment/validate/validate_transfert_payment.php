<?php
require_once("/home/prostage/connections/config.php");
require_once "/home/prostage/www/params.php";
require_once ROOT . "/debug.php";
//require_once ROOT . "/display_error.php";
require_once("/home/prostage/www/es/ajax_functions.php");
require_once APP . "logging/Logging.php";
require_once APP . "logging/LogCommission.php";
require_once APP . "logging/LogPayment.php";
require_once APP . "payment/E_Transaction/E_TransactionPayment.php";
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
require_once ROOT . '/mails_v3/mail_echec_paiement.php';

$data = [];

$eTransaction = new E_TransactionPayment();

if (
    (isset($_GET['ID3D']) && $_GET['ID3D'] != '')
) {

    $cardNumber = $_SESSION['cardNumber'];
    $cardExpiry = $_SESSION['cardExpiry'];
    $cardCVC = $_SESSION['cardCVC'];
    $studentId = $_SESSION['id_stagiaire'];
    $id_transaction = $_SESSION['reference'];
    $order_reference = $_SESSION['order_reference'];
    $montant_restant = $_SESSION['montant_restant'];
    $email = $_SESSION['email'];
    $new_stage = $_SESSION['new_stage'];
    $old_stage = $_SESSION['old_stage'];
    $id_horaire = $_SESSION['id_horaire'];
    $prix = $_SESSION['prix'];
    $_SESSION["paiement_error"] = '';
    $_SESSION["codereponse"] = '';
    $ID3D = $_GET["ID3D"];


    $logPayment = new LogPayment();
    $logCommission = new LogCommission();

    $isPlaceAvailableForSale = (new IsStagePlaceAvailableForSale())->__invoke($new_stage, $mysqli);

    $arrResponse = $eTransaction->validateTransaction(
        $montant_restant,
        $order_reference,
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
            " - STAGE - Transfert ERROR = " . $order_reference,
            $studentId,
            $montant_restant,
            $email,
            $cardNumber
        );

        $data['redirection'] = 'https://www.prostagespermis.fr?order=4';
        $data['email'] = $email;
        $data['id_stage'] = 0;
        $data['id_stagiaire'] = $studentId;
        mail_echec_paiement($data);
    }

    if ($codereponse == "00000") {
        $autorisation = $eTransaction->decodeResponse($response, 'autorisation');

        (new SendTicketPaymentEmail())->__invoke(
            $order_reference,
            $autorisation,
            $montant_restant,
            $email
        );


        $_SESSION["codereponse"] = '00000';

        transfert($id_transaction, $old_stage, $new_stage, $prix);

    }
}