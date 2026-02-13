<?php
session_start();
require_once("/home/prostage/connections/config.php");
require_once("/home/prostage/www/params.php");
require_once ROOT . '/debug.php';
require_once APP . '/payment/repositories/TrackingPathUserRepository.php';

$tracking = new TrackingPathUserRepository($mysqli);

// Récupérer l'action AJAX
$action = isset($_POST['action']) ? $_POST['action'] : null;

if ($action == "add_tracking") {
  $etape = isset($_POST['etape']) ? $_POST['etape'] : null;
  $whereclause = isset($_POST['whereclause']) ? $_POST['whereclause'] : 'session';
  $id_stagiaire = isset($_POST['id_stagiaire']) ? $_POST['id_stagiaire'] : null;

  if ($etape) {
    $tracking->addTracking($etape, $whereclause, $id_stagiaire);
    echo json_encode(["success" => true]);
  } else {
    echo json_encode(["success" => false, "error" => "Aucune étape fournie"]);
  }
}
