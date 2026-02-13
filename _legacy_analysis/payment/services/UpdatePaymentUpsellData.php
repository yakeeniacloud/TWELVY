<?php

require_once APP . "payment/repositories/PaymentUpsellRepository.php";

class UpdatePaymentUpsellData
{

    public function __invoke(
        $auto,
        $numTrans,
        $numAppel,
        $cardNumber,
        $orderId,
        $mysqli
    ) {

        $upsellRepo = new PaymentUpsellRepository($mysqli);
        $upsellRepo->updateDataAfterPayment(
            $auto,
            $numTrans,
            $numAppel,
            $cardNumber,
            $orderId
        );
    }
}