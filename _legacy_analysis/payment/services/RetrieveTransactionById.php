<?php

require_once  APP . 'payment/repositories/PaymentRepository.php';

class RetrieveTransactionById
{

    public function __invoke(
        $transactionId,
        $mysqli
    ) {
        $paymentRepo = new PaymentRepository($mysqli);
        return $paymentRepo->getTransactionById($transactionId);
    }
}