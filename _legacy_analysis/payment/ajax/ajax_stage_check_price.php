<?php
  session_start();

  require_once("/home/prostage/connections/config.php");
  require_once("/home/prostage/www/params.php");

  require_once APP . 'stage/services/RetrieveStageById.php';
  require_once APP . 'student/services/RetrieveStudentById.php';

  $studentId = $_POST['studentId'];
  $stageId =  $_POST['stageId'];

  $stage = (new RetrieveStageById())->__invoke($stageId, $mysqli);
  $student = (new RetrieveStudentById())->__invoke($studentId, $mysqli);

  $response = [
    'changed' => false, 
    'stagePrice' => $stage->prix,
    'studentPrice' => $student->paiement
  ];

  if($stage->prix != $student->paiement) {
    $response['changed'] = true;
  }

  $mysqli->close();

  echo json_encode($response);
  exit;
?>
