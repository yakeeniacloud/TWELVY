<?php
require_once("/home/prostage/connections/config.php");
require_once("/home/prostage/www/params.php");
require_once ROOT . '/debug.php';

require_once APP . 'order/services/RetrieveUserUpsellOrder.php';
require_once APP . 'payment/services/ValidateRefundUpsell.php';
require_once ROOT . '/mails_v3/mail_annulation_upsell.php';

$status = false;
$message = 'Erreur lors du traitement de votre demande';

if (
    isset($_POST['studentId'], $_POST['orderId'], $_POST['amount'])
) {

    $studentId  =   $_POST['studentId'];
    $orderId    =   $_POST['orderId'];
    $amount    =   $_POST['amount'];

    $isRefund   =   (new ValidateRefundUpsell())->__invoke($orderId, $mysqli);
    if ($isRefund) {
        $status = true;
        $message = 'Remboursement ';
        try {
            require_once ("/home/prostage/common_bootstrap2/notifications.php");
            $type_interlocuteur = 0;
            $id_interlocuteur = $studentId;
            $notifie = 1;
            $type_destinataire = 1;
            $content = "Bonjour, un remboursement de ". $amount ." euros a été effectué. Cette transaction sera visible sur votre relevé bancaire sous 5 jours (carte à débit immédiat) ou en fin de mois (carte à débit différé).";
            notification($type_interlocuteur, $id_interlocuteur, $type_destinataire, $notifie, $content);
        } catch (\Exception $e) {}
    }
}

echo json_encode(['status' => $status, 'message' => $message]);

