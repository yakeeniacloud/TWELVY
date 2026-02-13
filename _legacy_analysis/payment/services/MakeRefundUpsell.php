<?php

require_once APP . 'payment/repositories/PaymentUpsellRepository.php';

class MakeRefundUpsell
{

    public function __invoke(
        $orderId,
        $mysqli
    ) {
        $paymentRepo    =   new PaymentUpsellRepository($mysqli);
        return $paymentRepo->refundTransaction($orderId);
    }
}