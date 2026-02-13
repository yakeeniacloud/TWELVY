<?php
include("/home/prostage/connections/stageconnect0.php");
$stageconnect = mysqli_connect($hostname_stageconnect, $username_stageconnect, $password_stageconnect, $database_stageconnect) or trigger_error(mysql_error(),E_USER_ERROR);
$stageconnect->set_charset("utf8");
?>