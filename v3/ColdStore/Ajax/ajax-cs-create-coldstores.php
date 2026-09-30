<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


// $methodType = trim($_POST["methodType"]);

// if (isset($methodType) && !empty($methodType)) {

$api = API_STORE_COLDSTORES;

// if ($methodType == "get") {

// 	$ID = trim($_POST['ID']);

// 	$bodyContentGet = array(
// 		"ID" => $ID
// 	);

// 	$result = CallAPI($api, "GET", "", $bodyContentGet);
// } else if ($methodType == "post") {

$StoreID = trim($_POST["storeID"]);
$storeName = trim($_POST["storeName"]);
$storeAddress = trim($_POST["storeAddress"]);
$storeLocation = trim($_POST["storeLocation"]);
$inCharge = trim($_POST["inCharge"]);
$phoneNumber = trim($_POST["phoneNumber"]);
$gstNumber = trim($_POST["gstNumber"]);
$storeBlocks = trim($_POST["storeBlocks"]);
$ProductionPlantID = trim($_POST["productionPlant"]);
$status = trim($_POST["status"]);

$bodyContent = array(
	"StoreID" => $StoreID,
	"StoreName" => $storeName,
	"StoreAddress" => $storeAddress,
	"StoreLocation" => $storeLocation,
	"Incharge" => $inCharge,
	"StorePhoneNumber" => $phoneNumber,
	"StoreGSTNumber" => $gstNumber,
	"StoreBlocks" => $storeBlocks,
	"ProductionPlantID" => $ProductionPlantID,
	"StoreStatus" => $status
);
// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;	


$result = callAPI($api, "POST", "", $bodyContent);

header('Content-Type: application/json');
echo json_encode($result);

		// $error = $result->Error;
		// $msg = $result->Msg;

		// if (!isset($error) && empty($error)) {

		// 	$List = $result->Data;
		// 	if (isset($List) && !empty($List)) {
		// 		echo "<script>alert('$msg');</script>";
		// 		echo '<script>window.location.href = "agent-list.php";</script>';
		// 		exit;
		// 	}
		// } else {
		// 	echo "<div class='text-center mt-3 mb-3 text-bold' style='color: red;'>NOTE : " . $error . "</div>";
		// }
	// }
// }
