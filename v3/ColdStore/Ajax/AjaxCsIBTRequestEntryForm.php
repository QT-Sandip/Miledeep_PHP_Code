<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

	$api = API_IBT_REQUEST;

	if ($methodType == "getOrderNumber") {

		$bodyContentGet = array(
			"OrderDate" => trim($_POST["orderDate"]),
			"FromStore" => trim($_POST["fromStore"]),
			"PlantID" => trim($_POST["plantID"]),
			"Type" => trim($_POST["type"]),
		);
		$result = CallAPI($api, "GET", "", $bodyContentGet);
		$IBTCodeData = $result->IBTCodeData;
	
		header('Content-Type: application/json');
		echo json_encode($IBTCodeData);
	} else if ($methodType == "post") {

		$IBTID = trim($_POST["ibtID"]);
		$PlantID = trim($_POST["plantID"]);
		$AutoIBTNo = trim($_POST["autoIBTNo"]);
		$OrderName = trim($_POST["orderNum"]);
		$OrderDate = trim($_POST["orderDate"]);
		$FromStore = trim($_POST["fromStore"]);
		$ToStore = trim($_POST["toStore"]);
		$Remarks = trim($_POST["remarks"]);
		$Status = trim($_POST["status"]);

		$bodyContentPost = array(
			"IBTID" => $IBTID,
			"PlantID" => $PlantID,
			"AutoIBTNo" => $AutoIBTNo,
			"IBTOrderNumber" => $OrderName,
			"OrderDate" => $OrderDate,
			"FromStore" => $FromStore,
			"ToStore" => $ToStore,
			"Remarks" => $Remarks,
			"RequestStatus" => $Status
		);
		// echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
		// exit;
		$result = callAPI($api, "POST", "", $bodyContentPost);
		$List = $result->Data;
		header('Content-Type: application/json');
		echo json_encode($result);
	}
}
