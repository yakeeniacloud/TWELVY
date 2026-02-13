<?php

require_once APP . 'payment/repositories/PaymentRepository.php';
require_once APP . 'stage/repositories/StageStateRepository.php';
require_once APP . 'order/repositories/OrderStageRepository.php';
require_once APP . 'stage/services/UpdateStageAfterPayment.php';

class UpdateStagePaymentData
{

    public function __invoke(
        $stageId,
        $studentId,
        $memberId,
        $orderId,
        $autorisation,
        $cardNumber,
        $numAppel,
        $numTrans,
        $partenariat,
        $commission_ht,
        $reference,
        $numSuivi,
        $mysqli,
        $marge_commerciale='',
        $taux_marge_commerciale=''
    )
    {
        $paymentRepo = new PaymentRepository($mysqli);
        $orderRepo = new OrderStageRepository($mysqli);

        $orderRepo->updateReferenceOrder($orderId, $reference, $numSuivi);

        $paymentRepo->updateTransactionData($stageId,
            $studentId,
            $memberId,
            $orderId,
            $autorisation
        );

        $paymentRepo->updateStudentData(
            $studentId,
            $stageId,
            $memberId,
            $cardNumber,
            $numAppel,
            $numTrans,
            $partenariat,
            $commission_ht,
            $numSuivi,
            $marge_commerciale,
            $taux_marge_commerciale
        );

        (new UpdateStageAfterPayment())->__invoke($stageId, $mysqli);

    }
}