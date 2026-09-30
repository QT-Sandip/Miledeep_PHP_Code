<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/BackgroundApiCall.php';

 $methodType = trim($_POST["methodType"]);

    $api = API_TDS_CALC_BG_PROCESS;

    $ArrivalID = $_POST["ArrivalID"];
    $methodType	= $_POST["methodType"];
    $BillDate	= $_POST["BillDate"];	
    $bodyContent = array(
        "methodType" => $methodType,
        "ArrivalID" => $ArrivalID,
        "BillDate" => $BillDate
    );

    $result = BackgroundApiCall($api, "POST", "", $bodyContent);
    
if($result->Error!=null){

}

?>