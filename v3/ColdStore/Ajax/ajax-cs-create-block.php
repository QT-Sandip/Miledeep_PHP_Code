<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


// $methodType = trim($_POST["methodType"]);

// if (isset($methodType) && !empty($methodType)) {

	$api = API_BLOCK_DATA;

	// if ($methodType == "get") {

	// 	$ID = trim($_POST['ID']);

	// 	$bodyContentGet = array(
	// 		"ID" => $ID
	// 	);

	// 	$result = CallAPI($api, "GET", "", $bodyContentGet);
	// } else if ($methodType == "post") {

		$BlockID = trim($_POST["blockID"]);
		$BlockName = trim($_POST["blockName"]);
		$Floor = trim($_POST["floor"]);
		$Status = trim($_POST["status"]);		

		$bodyContent = array(
			"BlockID" => $BlockID,
			"BlockName" => $BlockName,
			"Floor" => $Floor,
			"Status" => $Status,
		);
		// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
		// exit;
		$result = callAPI($api, "POST", "", $bodyContent);
		// echo json_encode($result, JSON_PRETTY_PRINT);

        header('Content-Type: application/json');
        echo json_encode($result);

// 	}
// }