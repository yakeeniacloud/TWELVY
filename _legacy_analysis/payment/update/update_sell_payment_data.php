<?php

require_once APP . 'payment/services/UpdatePaymentUpsellData.php';
require_once APP . 'student/services/RetrieveStudentById.php';
require_once APP . 'payment/Utils/EmailBuild.php';
require_once ROOT . '/debug.php';
require_once ROOT . '/mails_v3/functions.php';
require_once APP . 'mail/Mail.php';
require_once APP . 'order/email/SendEmailOrderUpsell.php';

$adwords = '';
$intitule = '';
$provenance = 0;


(new UpdatePaymentUpsellData())->__invoke(
    $autorisation,
    $numTrans,
    $numAppel,
    $cardNumber,
    $orderId,
    $mysqli
);


$student = (new RetrieveStudentById())->__invoke($studentId, $mysqli);

try {

    (new SendEmailOrderUpsell())->__invoke(
        $upsell,
        $funnel,
        $student,
        $mysqli
    );
} catch (\Exception $e) {
}
