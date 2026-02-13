<?php

class TrackingUserPaymentErrorCode
{
  private $mysqli;

  public function __construct($mysqli)
  {
    $this->mysqli = $mysqli;
  }

  public function addTrackingError($id_stagiaire, $error_code, $source = 'up2pay')
  {
    $query = "INSERT INTO tracking_payment_error_code (id_stagiaire, error_code, source) VALUES 
                  ('" . $this->mysqli->real_escape_string($id_stagiaire) . "', '" . $this->mysqli->real_escape_string($error_code) . "', '" . $this->mysqli->real_escape_string($source) . "')";

    return $this->mysqli->query($query);
  }

  public function cleanUp($date_limit)
  {
    $query = "DELETE FROM tracking_payment_error_code WHERE timestamp < '$date_limit'";

    return $this->mysqli->query($query);
  }
}
