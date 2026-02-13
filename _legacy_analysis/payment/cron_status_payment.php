<?php

if (!isset($_GET['hash']) && $_GET['hash'] == '6e395m74ng') {
  return;
  die();
}

require_once("/home/prostage/www/functions_commission.php");
require_once("/home/prostage/connections/config.php");
require_once "/home/prostage/www/params.php";
require_once("/home/prostage/gae/functions.php");
require_once("/home/prostage/www/src/order/services/RetrieveFullOrderByStageStudent.php");
require_once APP . 'student/repositories/StudentRepository.php';

$sql = "
  SELECT  
    stagiaire.id AS stagiaire_id,
    stage.id AS stage_id,
    stagiaire.numtrans,
    stagiaire.numappel
  FROM
    stagiaire, transaction, stage, membre
  WHERE
    stage.id_membre = membre.id AND
    transaction.id_stagiaire = stagiaire.id AND
    stagiaire.id_stage = stage.id AND
    stagiaire.date_inscription >= DATE_ADD(NOW(), INTERVAL -2 DAY) AND
    stagiaire.date_inscription <= NOW() AND
    stagiaire.paiement > 0 AND
    stagiaire.status = 'inscrit' AND
    stagiaire.up2pay_status IS NULL
  ORDER BY
    stagiaire.date_inscription ASC
";

$rs = mysqli_query($mysqli, $sql) or die(mysql_error());
while ($row = mysqli_fetch_assoc($rs)) {
  $statut_paiement_up2pay = "N/A";

  $orderStage = (new RetrieveFullOrderByStageStudent())->__invoke($row['stagiaire_id'], $row['stage_id'], $mysqli);
  if ($orderStage) {
    $reference = $orderStage->reference_order;
  }

  $ret = retour_consultation($reference, $row['numtrans'], $row['numappel']);

  if ($ret && sizeof($ret) == 2) {
    $statut_paiement_up2pay = utf8_encode($ret[1]);
    (new StudentRepository($mysqli))->updateUp2payStatus($row['stagiaire_id'], $statut_paiement_up2pay);
  }

  var_dump($ret);
}

var_dump('-- END');
