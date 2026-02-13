<?php

class TrackingPathUserRepository
{
  private $mysqli;

  public function __construct($mysqli)
  {
    $this->mysqli = $mysqli;
  }


  /**
   * Enregistrer une étape dans la base
   */
  public function addTracking($step, $where_clause = "session", $id_stagiaire = null)
  {

    if (!session_id()) {
      session_start();
    }

    $session_id = session_id();
    $rowSession = $this->getBySessionId($session_id);

    $exist_session = ($rowSession != NULL && $rowSession != false) ? true : false;

    if (!$exist_session) {
      $this->initTracking($session_id);
    }

    $now = date('Y-m-d H:i:s');

    $id_stagiaire = $id_stagiaire ? "'" . $this->mysqli->real_escape_string($id_stagiaire) . "'" : "NULL";

    if ($where_clause == 'session') {
      $where = " WHERE session_id = '" . $this->mysqli->real_escape_string($session_id) . "'";
    } else {
      $where = " WHERE id_stagiaire = $id_stagiaire";
    }

    if ($id_stagiaire == 'NULL') {
      $update_clause = "";
    } else {
      $update_clause = " , id_stagiaire = $id_stagiaire";
    }

    $query = "UPDATE tracking_inscription 
                  SET $step = '$now'
                  $update_clause
                  $where";

    return $this->mysqli->query($query);
  }

  public function getBySessionId($session_id)
  {
    $query = "SELECT * FROM tracking_inscription WHERE session_id LIKE '$session_id'";
    $result = $this->mysqli->query($query);

    return $result->fetch_assoc();
  }

  public function initTracking($session_id)
  {
    $query = "INSERT INTO tracking_inscription (session_id) VALUES 
                  ('" . $this->mysqli->real_escape_string($session_id) . "')";

    return $this->mysqli->query($query);
  }

  public function cleanUp($date_limit)
  {
    $query = "DELETE FROM tracking_inscription WHERE timestamp < '$date_limit'";

    return $this->mysqli->query($query);
  }
}
