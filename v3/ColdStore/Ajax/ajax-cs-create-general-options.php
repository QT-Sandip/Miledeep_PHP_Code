<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


// $methodType = trim($_POST["methodType"]);

// if (isset($methodType) && !empty($methodType)) {

	$api = API_STORE_GENERAL_OPTIONS;

	// if ($methodType == "get") {

	// 	$ID = trim($_POST['ID']);

	// 	$bodyContentGet = array(
	// 		"ID" => $ID
	// 	);

	// 	$result = CallAPI($api, "GET", "", $bodyContentGet);
	// } else if ($methodType == "post") {

		$OptID = trim($_POST["optID"]);
		$optionType = trim($_POST["optionType"]);
		$name = trim($_POST["name"]);
		$value = trim($_POST["value"]);
		$status = trim($_POST["status"]);		

		$bodyContentPost = array(
			"OptID" => $OptID,
			"OptionType" => $optionType,
			"Name" => $name,
			"Value" => $value,
			"Status" => $status

		);
		$result = callAPI($api, "POST", "", $bodyContentPost);

        header('Content-Type: application/json');
        echo json_encode($result);

// 	}
// }