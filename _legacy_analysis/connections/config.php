<?php
include("/home/prostage/connections/stageconnect0.php");
$mysqli = new mysqli($hostname_stageconnect,$username_stageconnect,$password_stageconnect,$database_stageconnect);
$mysqli->set_charset('utf8');
if($mysqli->connect_errno)
    exit;

?>