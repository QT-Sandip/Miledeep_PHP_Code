<?php

	// ini_set('display_errors', 1);
	// ini_set('display_startup_errors', 1);
	// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

	$api = API_FS_AUDIT_MILL;

	if ($methodType == "get") {

		$FsaID = trim($_POST['fsaID']);

		$bodyContentGet = array(
			"FsaID" => $FsaID,
		);

		$result = CallAPI($api, "GET", "", $bodyContentGet);
	} 

	if ($methodType == "post") {

		$AuditID = trim($_POST["auditID"]);
		$AuditType = trim($_POST["auditType"]);
		$Farmer = trim($_POST["farmer"]);
		$Hatchery = trim($_POST["hatchery"]);
		$CertBody = trim($_POST["certBody"]);
		$AssocFactory = trim($_POST["assocFactory"]);
		$GovtAuth = trim($_POST["govtAuth"]);
		$RegiNo = trim($_POST["regiNo"]);
		$BAPNo = trim($_POST["bapNo"]);
		$Tonnage = trim($_POST["tonnage"]);
		$Water = trim($_POST["water"]);
		$AuditDate = trim($_POST["auditDate"]);
		$IssuedDate = trim($_POST["issuedDate"]);
		$ValidDate = trim($_POST["validDate"]);
		$Status = trim($_POST["status"]);

		$bodyContentPost = array(
			"AuditID" => $AuditID,
			"AuditType" => $AuditType, 
			"FarmerID" => $Farmer,
			"HatchryID" => $Hatchery,
			"CertifiedBody" => $CertBody,
			"PlantID" => $AssocFactory,
			"AuthorityID" => $GovtAuth,
			"RegistrationNo" => $RegiNo,
			"BAPNo" => $BAPNo,
			"ProductionPerAnnum" => $Tonnage,
			"WaterSpreadArea" => $Water,
			"AuditDate" => $AuditDate,
			"IssuedOn" => $IssuedDate,
			"ValidtyUpto" => $ValidDate,
			"Status" => $Status
		);
		$result = callAPI($api, "POST", "", $bodyContentPost);
		header('Content-Type: application/json');
		echo json_encode($result);

	}

}

?>