<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$DeleteClearingMoreTrans = trim($_POST["DeleteClearingMoreTrans"]);

/* Payment Temmplate Delete */
if(isset($DeleteClearingMoreTrans) && !empty($DeleteClearingMoreTrans)) {

	$BreakUpID = trim($_POST["BreakUpID"]);
	// echo $BreakUpID;

	$api = API_DELETE_CLEARING_AGENT_BREACKUP;
	// echo $api;
	$bodyContent = array("BreakUpID"=> $BreakUpID);
	// echo "<br>";
	// var_dump($bodyContent);
	// echo "<br>";
	$result = CallAPI($api, "DELETE", "", $bodyContent);
	// var_dump($result);
	// $res = $resDeleteClearingAgentBreakup;

    $jsonResponse = json_encode($result);
    header('Content-Type: application/json');
    echo $jsonResponse;
    

}

?>