<?php

require_once("/home/prostage/connections/config.php");
require_once("/home/prostage/www/params.php");
require_once ROOT . '/debug.php';
//require_once ROOT . '/display_error.php';
require_once APP . "payment/Utils/SessionManage.php";
require_once APP . 'upsell/services/RetrieveUpsellById.php';
require_once APP . 'order/applications/command/RetrieveNextUpsellOrder.php';

$data = [];
$session_id = $_POST['session_id'];
$data = SessionManage::retrieveSessionData($session_id);
$arrData = SessionManage::parseSessionData($data);

$autorisation = 'XXXXXX';
$numTrans = uniqid();
$numAppel = uniqid();

list(
    $studentId,
    $upsellId,
    $orderId,
    $reference,
    $email,
    $cardNumber,
    $cardExpiry,
    $cardCVC,
    $isOrderBump,
    $funnelId
    ) = SessionManage::initPaymentOneProductUpsellData($arrData);

$upsell  =  (new RetrieveUpsellById())->__invoke($upsellId, $mysqli);

require APP . 'payment/update/update_sell_payment_data.php';

$page_redirection = (new RetrieveNextUpsellOrder())->__invoke(
    $studentId,
    $session_id,
    $upsellId,
    $isOrderBump,
    $mysqli,
    $funnelId
);

echo json_encode(['page_redirection' => $page_redirection]);
exit;

