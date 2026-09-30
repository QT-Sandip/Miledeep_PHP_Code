<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_CONTAINER_RETURN_DETAILS;

$bodyContentPost = array(

	"PoID" => trim($_POST["poID"]), 
	"ReceivedDate" => trim($_POST["recievedDate"]), 
	"ReturnBLNo" => trim($_POST["returnBillNo"]), 
	"ReturnContainerNo" => trim($_POST["containerNo"]), 
	"ContainerWeight" => trim($_POST["containerWeight"]), 
	"ContainerReturnPort" => trim($_POST["returnPort"]), 
	"BillofEntryNo" => trim($_POST["billEntryNo"]), 
	"BillofEntryDate" => trim($_POST["billEntryDate"]), 
	"Remarks" => trim($_POST["remarks"]), 

);
$result = callAPI($api, "POST", "", $bodyContentPost);

header('Content-Type: application/json');
echo json_encode($result);
