<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);



if ($methodType == "post") {

	$api = API_GRADE_MASTER_HL_COUNT;

	$bodyContentPost = array(
		"HeadonDataID" => trim($_POST["headonDataID"]),
		"Species" => trim($_POST["species"]),
		"TypeProduct" => trim($_POST["typeProduct"]),
		"ProductForm" => trim($_POST["productForm"]),
		"FreezingType" => trim($_POST["freezingType"]),
		"Preparation" => trim($_POST["preparation"]),
		"GlazeStatus" => trim($_POST["glazeStatus"]),
		"GlazePerc" => trim($_POST["glazePerc"]),
		"GlazeType" => trim($_POST["glazeType"]),
		"Treatment" => trim($_POST["treatment"]),
		"Grade" => trim($_POST["grade"]),
		"PackingCount" => trim($_POST["packingCount"]),
		"HonCount" => trim($_POST["honCount"]),
		"Yield" => trim($_POST["yield"]),
		"Status" => trim($_POST["status"]),
		"Type" => "formSave",

	);
	// echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
	// exit;
	$result = callAPI($api, "POST", "", $bodyContentPost);

	header('Content-Type: application/json');
	echo json_encode($result);

} else if ($methodType == "checkCombo") {

	$api = API_GRADE_MASTER_HL_COUNT;

	$bodyContentPost = array(
		"TypeProduct" => trim($_POST["typeProduct"]),
		"Preparation" => trim($_POST["preparation"]),
		"FreezingType" => trim($_POST["freezingType"]),
		"Type" => "checkCombo",
	);
	$result = callAPI($api, "POST", "", $bodyContentPost);
	header('Content-Type: application/json');
	echo json_encode($result);
}

// 	}
// }