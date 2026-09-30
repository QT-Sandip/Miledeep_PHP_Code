<?php

	// ini_set('display_errors', 1);
	// ini_set('display_startup_errors', 1);
	// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

	$api = API_FS_AUDIT;

	if ($methodType == "get") {

		$FsaID = trim($_POST['fsaID']);

		$bodyContentGet = array(
			"FsaID" => $FsaID,
		);

		$result = CallAPI($api, "GET", "", $bodyContentGet);
	} 

	if ($methodType == "post") {

		$FsaID = trim($_POST["fsaID"]);
		$Plant = trim($_POST["plant"]);
		$AuditName = trim($_POST["auditName"]);
		$AuditType = trim($_POST["auditType"]);
		$CertAuth = trim($_POST["certAuth"]);
		$RegiNo = trim($_POST["regiNo"]);
		$CertNo = trim($_POST["certNo"]);
		$StartDate = trim($_POST["startDate"]);
		$EndDate = trim($_POST["endDate"]);
		$AuditProg = trim($_POST["auditProg"]);
		$IssuedDate = trim($_POST["issuedDate"]);
		$ValidDate = trim($_POST["validDate"]);
		$RenewalData = trim($_POST["renewalDate"]);
		$AuditGrade = trim($_POST["auditGrade"]);
		$Status = trim($_POST["status"]);

		$bodyContentPost = array(
			"FsaID" => $FsaID,
			"PlantID" => $Plant, 
			"AuditNameID" => $AuditName,
			"AuditTypeID" => $AuditType,
			"CertAuthorityID" => $CertAuth,
			"RegistrartionNo" => $RegiNo,
			"CertificateNo" => $CertNo,
			"AuditStartDt" => $StartDate,
			"AuditEndDt" => $EndDate,
			"AuditProgram" => $AuditProg,
			"IssuedOnDt" => $IssuedDate,
			"ValidUpTo" => $ValidDate,
			"RenewaLDt" => $RenewalData,
			"AuditGrade" => $AuditGrade,
			"Status" => $Status
		);
		$result = callAPI($api, "POST", "", $bodyContentPost);
		header('Content-Type: application/json');
		echo json_encode($result);

	}

}

?>