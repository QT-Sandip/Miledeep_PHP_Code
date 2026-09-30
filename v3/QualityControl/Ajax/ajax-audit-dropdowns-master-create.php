<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_CATEGORY_OPTIONS_MASTERS;

$bodyContentPost = array(
	"OptionID" => trim($_POST["optionID"]),
	// "PlantID" => trim($_POST["productionPlant"]),
	"OptionCategoryName" => trim($_POST["optionCategory"]),
	"OptionName" => trim($_POST["optionName"]),
	"Status" => trim($_POST["status"])
);
// echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
// exit;
$result = callAPI($api, "POST", "", $bodyContentPost);

// echo json_encode($result, JSON_PRETTY_PRINT);
// exit;

header('Content-Type: application/json');
echo json_encode($result);
