<?php

class PaymentUpsellRepository
{

    private $mysqli;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function updateDataAfterPayment(
        $auto,
        $numTrans,
        $numAppel,
        $cardNumber,
        $orderId
    )
    {
        $date = date('Y-m-d h:i:s');
        $sql = "INSERT INTO upsell_transaction(order_id,date_ajout,cb_autorisation,numtrans,numappel,numero_cb, status)
                    VALUES (" . $orderId . ", '". $date ."', '" . $auto . "','" . $numTrans . "','" . $numAppel . "','" . $cardNumber . "', 1)";
        $this->mysqli->query($sql);

        $sql = "UPDATE order_upsell SET  is_paid = 1 WHERE id =$orderId";
        $this->mysqli->query($sql);
    }

    public function refundTransaction($orderId) {
        $date  = date('Y-m-d h:i:s');
        $sql = "UPDATE upsell_transaction SET status = 2, date_demande_remboursement = '$date' WHERE order_id = $orderId";
        if ($this->mysqli->query($sql)) {
            return true;
        }
        return false;
    }

    public function validateRefundTransaction($orderId) {
        $date  = date('Y-m-d h:i:s');
        $sql = "UPDATE upsell_transaction SET status = 3, date_remboursement = '$date' WHERE order_id = $orderId";
        if ($this->mysqli->query($sql)) {
            return true;
        }
        return false;
    }

}
