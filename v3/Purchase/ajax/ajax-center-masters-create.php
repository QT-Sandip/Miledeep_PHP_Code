<?php

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


// $methodType = trim($_POST["methodType"]);

// if (isset($methodType) && !empty($methodType)) {

	$api = API_CHEM_BUYER;

	// if ($methodType == "get") {

	// 	$ChemBuyerID = trim($_POST['chemBuyerID']);

	// 	$bodyContentGet = array(
	// 		"ChemBuyerID" => $ChemBuyerID,
	// 	);

	// 	$result = CallAPI($api, "GET", "", $bodyContentGet);
	// } 

	// if ($methodType == "post") {

		$CenterID = trim($_POST["centerID"]);
		$CenterName = trim($_POST["centerName"]);
		$Shortcode = trim($_POST["shortcode"]);
		$Group = trim($_POST["group"]);
		$Type = trim($_POST["type"]);

		$bodyContentPost = array(
			"ChemBuyerID" => $CenterID,
			"BuyerName" => $CenterName, 
			"BuyerAddress" => $Shortcode,
			"BuyerPhoneNumber" => $Group,
			"BuyerCountry" => $Type
		);
		$result = callAPI($api, "POST", "", $bodyContentPost);
		header('Content-Type: application/json');
		echo json_encode($result);

// 	}

// }

?>