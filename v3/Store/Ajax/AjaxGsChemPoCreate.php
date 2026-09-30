<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientID = $_SESSION["ClientID"];
$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

	$api = API_GS_CHEM_PO;

	if ($methodType == "generatePoNumber") {

		$SupplierID = trim($_POST['supplierID']);
		$MethodType = trim($_POST['methodType']);

		$bodyContentGet = array(
			"SupplierID" => $SupplierID,
			"MethodType" => $MethodType,
			"ClientID" => $ClientID
		);

		$result = CallAPI($api, "GET", "", $bodyContentGet);
		echo json_encode($result);
	}

	if ($methodType == "get") {

		$ChemPoID = trim($_POST['ChemPoID']);

		$bodyContentGet = array(
			"ChemPoID" => $ChemPoID,
		);

		$result = CallAPI($api, "GET", "", $bodyContentGet);
	}

	if ($methodType == "post") {

		$ChemPoID = trim($_POST["chemPoID"]);
		$SupplierID = trim($_POST["supplierID"]);
		$BuyerID = trim($_POST["buyerID"]);
		$PoNo = trim($_POST["poNo"]);
		$Port = trim($_POST["portID"]);
		$Mode = trim($_POST["mode"]);
		$Year = trim($_POST["year"]);
		$ETDDate = trim($_POST["etdDate"]);
		$ETADate = trim($_POST["etaDate"]);
		$Remarks = trim($_POST["remarks"]);

		$bodyContentPost = array(
			"ChemPoID" => $ChemPoID,
			"SupplierPartyID" => $SupplierID,
			"BuyerPartryID" => $BuyerID,
			"ChemPoNo" => $PoNo,
			"PortID" => $Port,
			"ModeOfImport" => $Mode,
			"ChemPoDate" => $Year,
			"ChemETADate" => $ETADate,
			"ChemETDDate" => $ETDDate,
			"Remarks" => $Remarks
		);
		$result = callAPI($api, "POST", "", $bodyContentPost);
		header('Content-Type: application/json');
		echo json_encode($result);
	}
}
