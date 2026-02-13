<?php
session_start();
require_once("/home/prostage/connections/config.php");
require_once("/home/prostage/www/params.php");
require_once("/home/prostage/www/debug.php");
//require_once("/home/prostage/www/display_error.php");

require_once APP . 'order/services/RetrieveFullOrderByStageStudent.php';
require_once APP . 'order/services/CheckIfStageAlreadyPay.php';
require_once APP . 'stage/services/IsStagePlaceAvailableForSale.php';
require_once APP . 'payment/Utils/SessionManage.php';
require_once APP . 'upsell/services/RetrieveNextUpSellToPay.php';
require_once APP . 'order/services/CheckIfUpsellIsAlreadyOrder.php';

$cardNumber = $_POST['cardNumber'];
$cardExpiry = $_POST['cardExpiry'];
$cardCVC = $_POST['cardCVC'];
$studentId = $_POST['studentId'];
$upsellId = $_POST['upsellId'];
$isOrderBump = $_POST['isOrderBump'];
$email = $_POST['email'];
$funnelId = $_POST['funnelId'];

$_SESSION['cardNumber'] = $cardNumber;
$_SESSION['cardExpiry'] = $cardExpiry;
$_SESSION['cardCVC'] = $cardCVC;
$_SESSION["dossier_inscription"] = 0;
$_SESSION['studentId'] = $studentId;
$_SESSION['id_stagiaire'] = $studentId;
$_SESSION['i_stagiaireId'] = $studentId;
$_SESSION['upsellId'] = $upsellId;
$_SESSION['isOrderBump'] = $isOrderBump;
$_SESSION['funnelId'] = $funnelId;

$oldUpsellOrder = (new CheckIfUpsellIsAlreadyOrder())->__invoke($studentId, $upsellId, $mysqli);
$order_id = $oldUpsellOrder->id;
$reference_order = $oldUpsellOrder->reference_order;

SessionManage::saveSessionData(
    [
        'studentId' => $studentId,
        'email' => $email,
        'orderId' => $order_id,
        'upsellId' => $upsellId,
        'reference' => $reference_order,
        'cardNumber' => $cardNumber,
        'memberId' => 0,
        'cardExpiry' => $cardExpiry,
        'cardCVC' => $cardCVC,
        'isOrderBump' => $isOrderBump,
        'funnelId' => $funnelId
    ],
    session_id()
);

echo json_encode(['isAvailable' => true, 'isAlreadyPay' => false]);
exit;


?>
