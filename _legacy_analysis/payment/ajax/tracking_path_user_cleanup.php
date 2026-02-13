<?php
session_start();
require_once("/home/prostage/connections/config.php");
require_once("/home/prostage/www/params.php");
require_once ROOT . '/debug.php';
require_once APP . '/payment/repositories/TrackingPathUserRepository.php';

$tracking = new TrackingPathUserRepository($mysqli);

$dateLimit = date('Y-m-d', strtotime('-7 days'));
$tracking->cleanUp($dateLimit);

echo json_encode(["success" => true]);
