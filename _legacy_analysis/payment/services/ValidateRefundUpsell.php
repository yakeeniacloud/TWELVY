<?php

require_once APP . 'payment/repositories/PaymentUpsellRepository.php';

class ValidateRefundUpsell
{

    public function __invoke(
        $orderId,
        $mysqli
    ) {
        $paymentRepo    =   new PaymentUpsellRepository($mysqli);
        return $paymentRepo->validateRefundTransaction($orderId);
    }
}