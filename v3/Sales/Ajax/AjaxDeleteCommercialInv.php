<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$DeleteClearingMoreTrans = trim($_POST["DeleteClearingMoreTrans"]);

$OrderID = trim($_POST["OrderID"]);
// echo $BreakUpID;

$api = API_DELETE_COMMERCIAL_DATA;
$bodyContent = array("OrderID"=> $OrderID);
$result = CallAPI($api, "DELETE", "", $bodyContent);

$jsonResponse = json_encode($result);
header('Content-Type: application/json');
echo $jsonResponse;

?>