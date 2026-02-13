<?php
require_once("/home/prostage/connections/config.php");
require_once("/home/prostage/www/params.php");
require_once ROOT . '/debug.php';

require_once APP . 'order/services/RetrieveUserUpsellOrder.php';
require_once APP . 'payment/services/MakeRefundUpsell.php';
require_once ROOT . '/mails_v3/mail_annulation_upsell.php';

$status = false;
$message = 'Erreur lors du traitement de votre demande';

if (
    isset($_POST['studentId'], $_POST['orderId'], $_POST['upsellId'])
) {

    $studentId  =   $_POST['studentId'];
    $orderId    =   $_POST['orderId'];
    $upsellId    =   $_POST['upsellId'];

    $orderUpsell    =   (new RetrieveUserUpsellOrder())->__invoke($studentId, $upsellId, $mysqli);

    if ($orderUpsell) {
        $isRefund   =   (new MakeRefundUpsell())->__invoke($orderId, $mysqli);
        if ($isRefund) {
            $status = true;
            $message = 'votre demande de remboursement a été prise en compte.';
            try {
                mail_annulation_upsell($studentId);
            } catch (\Exception $e) {}
        }
    }
}

echo json_encode(['status' => $status, 'message' => $message]);

