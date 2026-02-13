<?php

//require_once APP . "payment/Utils/Logging.php";
require_once APP . "payment/Utils/SessionManage.php";
//require_once APP . "payment/Utils/Utils.php";

$data = [];

foreach($_GET as $key => $value){
    $param = explode('_',$key);
    if($param[0] == "d2305"){
        session_id($param[1]);
        session_start();
        $data = SessionManage::retrieveSessionData($param[1]);
    }
}

$arrData  = SessionManage::parseSessionData($data);

//Logging::onlyLogForDebug('Session Data ', 'debug_redirect_transaction', json_encode($data) , 'debug_redirect_transaction');
//Logging::onlyLogForDebug('GET Data ', 'debug_redirect_transaction', json_encode($_GET) , 'debug_redirect_transaction');
//Logging::onlyLogForDebug('POST Data ', 'debug_redirect_transaction', json_encode($_POST) , 'debug_redirect_transaction');
