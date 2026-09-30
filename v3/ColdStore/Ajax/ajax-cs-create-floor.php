<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


// $methodType = trim($_POST["methodType"]);

// if (isset($methodType) && !empty($methodType)) {

	// $api = API_STORE_GENERAL_OPTIONS;

	// if ($methodType == "get") {

	// 	$ID = trim($_POST['ID']);

	// 	$bodyContentGet = array(
	// 		"ID" => $ID
	// 	);

	// 	$result = CallAPI($api, "GET", "", $bodyContentGet);
	// } else if ($methodType == "post") {

		$api = API_FLOOR_DATA;

		$FloorID = trim($_POST["floorID"]);
		$FloorName = trim($_POST["floorName"]);
		$Status = trim($_POST["status"]);		

		$bodyContent = array(
			"FloorID" => $FloorID,
			"FloorName" => $FloorName,
			"Status" => $Status,
		);
		$result = callAPI($api, "POST", "", $bodyContent);

		// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
		// echo json_encode($result, JSON_PRETTY_PRINT);

        header('Content-Type: application/json');
        echo json_encode($result);

// 	}
// }