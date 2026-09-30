<?php

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

	$api = API_CHEM_BUYER;

	if ($methodType == "get") {

		$ChemBuyerID = trim($_POST['chemBuyerID']);

		$bodyContentGet = array(
			"ChemBuyerID" => $ChemBuyerID,
		);

		$result = CallAPI($api, "GET", "", $bodyContentGet);
	} 

	if ($methodType == "post") {

		$ChemBuyerID = trim($_POST["chemBuyerID"]);
		$BuyerName = trim($_POST["buyerName"]);
		$BuyerAddress = trim($_POST["buyerAdd"]);
		$BuyerPhoneNumber = trim($_POST["buyerPhone"]);
		$BuyerCountry = trim($_POST["country"]);
		$EmailAddress = trim($_POST["buyerEmail"]);
		$Status = trim($_POST["status"]);

		$bodyContentPost = array(
			"ChemBuyerID" => $ChemBuyerID,
			"BuyerName" => $BuyerName, 
			"BuyerAddress" => $BuyerAddress,
			"BuyerPhoneNumber" => $BuyerPhoneNumber,
			"BuyerCountry" => $BuyerCountry,
			"EmailAddress" => $EmailAddress,
			"Status" => $Status
		);
		$result = callAPI($api, "POST", "", $bodyContentPost);
		header('Content-Type: application/json');
		echo json_encode($result);

	}

}

?>