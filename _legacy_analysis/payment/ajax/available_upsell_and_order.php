<?php
session_start();
require_once("/home/prostage/connections/config.php");
require_once("/home/prostage/www/params.php");
require_once ROOT . '/debug.php';
//require_once ROOT . '/display_error.php';
require_once APP . 'payment/Utils/SessionManage.php';
require_once APP . 'order/services/CheckIfUpsellIsAlreadyOrder.php';
require_once APP . 'upsell/services/RetrieveUpsellById.php';
require_once APP . 'order/services/SaveUpsellOrder.php';

$cardNumber = $_POST['cardNumber'];
$cardExpiry = $_POST['cardExpiry'];
$cardCVC = $_POST['cardCVC'];
$studentId = $_POST['studentId'];
$upsellId = $_POST['upsellId'];
$amount = $_POST['amount'];
$email = $_POST['email'];
$stageId = $_POST['stageId'];
$funnelId = $_POST['funnelId'];

$_SESSION['cardNumber'] = $cardNumber;
$_SESSION['cardExpiry'] = $cardExpiry;
$_SESSION['cardCVC'] = $cardCVC;
$_SESSION['studentId'] = $studentId;
$_SESSION['email'] = $email;
$_SESSION['upsellId'] = $upsellId;
$_SESSION['stageId'] = $stageId;
$_SESSION['funnelId'] = $funnelId;

$isAlreadyPay = false;
$isAlreadyOrder = false;

$oldUpsellOrder = (new CheckIfUpsellIsAlreadyOrder())->__invoke($studentId, $upsellId, $mysqli);

if ($oldUpsellOrder) {
    $isAlreadyOrder = true;
    if ($oldUpsellOrder->is_paid == true) {
        $isAlreadyPay = true;
    }
}

if (!$isAlreadyPay) {

    $upsell = (new RetrieveUpsellById())->__invoke($upsellId, $mysqli);

    if (!DEBUG) {
        $logfile = "/home/prostage/www/logs/paiements_demandes.txt";
        $tmpfile = file_get_contents($logfile);
        $msg = date("d-m-Y H:i:s") . " - UPSELL - TITRE = " . $upsell->titre . " - Stagiaire=" . $studentId . " - Montant=" . $upsell->prix_remise . " - Email=" . $email . " - Porteur=" . $cardNumber;
        file_put_contents($logfile, "\n" . $msg . "\n" . $tmpfile);
    }

    if ($isAlreadyOrder) {
        $order_id = $oldUpsellOrder->id;
        $reference_order = $oldUpsellOrder->reference_order;
    } else {
        $upsellOrder = (new SaveUpsellOrder())->__invoke($studentId, $amount, $upsellId, $mysqli);
        $order_id = $upsellOrder->getOrderId();
        $reference_order = $upsellOrder->referenceOrder();
    }


    SessionManage::saveSessionData(
        [
            'studentId' => $studentId,
            'upsellId' => $upsellId,
            'stageId' => $stageId,
            'email' => $email,
            'orderId' => $order_id,
            'reference' => $reference_order,
            'cardNumber' => $cardNumber,
            'cardExpiry' => $cardExpiry,
            'cardCVC' => $cardCVC,
            'funnelId' => $funnelId
        ],
        session_id()
    );

    $mysqli->close();
    echo json_encode(
        [
            'isAlreadyPay' => false,
            'redirectToNextUpsell' => '/page_upsell.php?upsell=2&s=' . session_id(),
        ]
    );
    exit;
} else {
    $mysqli->close();
    echo json_encode(['isAlreadyPay' => true, 'redirectToNextUpsell' => '/page_upsell.php?upsell=2&s=' . session_id()]);
    exit;
}
?>
