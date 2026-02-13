<?php
include("/home/prostage/connections/stageconnect0.php");
$conn = new mysqli($hostname_stageconnect, $username_stageconnect, $password_stageconnect, $database_stageconnect);
$conn->set_charset("utf8");
if(mysqli_connect_errno()){

	echo mysqli_connect_error();

}

?>
