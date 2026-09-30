<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// $methodType = trim($_POST["methodType"]);

// if (isset($methodType) && !empty($methodType)) {

	$api = API_STORE_TENANTS;

	// if ($methodType == "get") {

	// 	$TenantID = trim($_POST['tenantID']);

	// 	$bodyContentGet = array(
	// 		"TenantID" => $TenantID
	// 	);

	// 	$result = CallAPI($api, "GET", "", $bodyContentGet);
	// } else if ($methodType == "post") {

		$TenantID = trim($_POST["tenantID"]);
		$TenantName = trim($_POST["tenantName"]);
		$TenantAddress = trim($_POST["tenantAddress"]);
		$TenantType = trim($_POST["tenantType"]);
		$Processor = trim($_POST["processor"]);
		$Status = trim($_POST["status"]);

		$bodyContentPost = array(
			"TenantID" => $TenantID,
			"TenantName" => $TenantName,
			"TenantAddress" => $TenantAddress,
			"TenantType" => $TenantType,
			"ProcessorID" => $Processor,
			"TenantStatus" => $Status

		);
		
		$result = callAPI($api, "POST", "", $bodyContentPost);

		header('Content-Type: application/json');
		echo json_encode($result);
// 	}
// }
