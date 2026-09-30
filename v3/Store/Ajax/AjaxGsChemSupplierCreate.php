<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


// $methodType = trim($_POST["methodType"]);

// if (isset($methodType) && !empty($methodType)) {

$api = API_CHEMICAL_SUPPLIER;

// 	if ($methodType == "get") {

// 		$ChemBuyerID = trim($_POST['chemBuyerID']);

// 		$bodyContentGet = array(
// 			"ChemBuyerID" => $ChemBuyerID,
// 		);

// 		$result = CallAPI($api, "GET", "", $bodyContentGet);
// 	} 

// 	if ($methodType == "post") {

$ChemSupplierID = trim($_POST["chemSupplierID"]);
$SupplierName = trim($_POST["supplierName"]);
$SupplierAddress = trim($_POST["supplierAdd"]);
$SupplierPhoneNumber = trim($_POST["supplierPhone"]);
$SupplierCountry = trim($_POST["country"]);
$EmailAddress = trim($_POST["supplierEmail"]);
$Status = trim($_POST["status"]);

$bodyContentPost = array(
	"ChemSupplierID" => $ChemSupplierID,
	"SupplierName" => $SupplierName,
	"SupplierAddress" => $SupplierAddress,
	"SupplierPhoneNumber" => $SupplierPhoneNumber,
	"SupplierCountry" => $SupplierCountry,
	"SupplierEmailAddress" => $EmailAddress,
	"Status" => $Status
);
$result = callAPI($api, "POST", "", $bodyContentPost);
header('Content-Type: application/json');
echo json_encode($result);

// 	}

// }
