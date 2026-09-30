<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


    $api = API_CS_GROUP_MASTERS;


		$TrnsID = trim($_POST["trnsID"]);
		$GroupName = trim($_POST["groupName"]);
        $Type = trim($_POST["type"]);
        $IsReturnable = trim($_POST["isReturnable"]);
		$Status = trim($_POST["status"]);

		$bodyContentPost = array(
			"TrnsID" => $TrnsID,
			"TransCategory" => $GroupName,
            "TransType" => $Type,
			"IsReturnable" => $IsReturnable,
			"TransStatus" => $Status

		);
		$result = callAPI($api, "POST", "", $bodyContentPost);

		header('Content-Type: application/json');
		echo json_encode($result);
