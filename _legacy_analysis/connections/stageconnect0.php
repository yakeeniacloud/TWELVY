<?php
// Mettre la valeur PROD en productiongi
const ENV = 'PROD';

if (ENV == 'LOCAL') {
    $hostname_stageconnect = "prostage-db";
    $database_stageconnect = "prostage";
    $username_stageconnect = "root";
    $password_stageconnect = "prostage";
    $home_version = "local";
    $home_folder = "/home/prostage/www/";
    $home_url = "http://locahost:9000";
} else {
    if(substr_count($_SERVER['SCRIPT_FILENAME'],"sandbox") > 0){
        $hostname_stageconnect = "ma27831-001.privatesql:35300";
        $database_stageconnect = "sandbox_prostagepsp";
        $username_stageconnect = "sandbox_psp";
        $password_stageconnect = "14Tralouramilanou1725";
        $home_folder = "/home/prostage/www_sandbox/";
        $home_url = "https://sandbox.prostagespermis.fr/";
    }else{
        $hostname_stageconnect = "prostagepsp.mysql.db";
        $database_stageconnect = "prostagepsp";
        $username_stageconnect = "prostagepsp";
        $password_stageconnect = "24Jretuilarossorytai3612";
        $home_version = "prod";
        $home_folder = "/home/prostage/psycho/";
        $home_url = "https://www.prostagespermis.fr/";
    }
}
?>
