<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_AUDIT_CATEGORIES_OPTIONS;

$bodyContentPost = array(
	"AuditOptionID" => trim($_POST["auditOptionID"]),
	"AuditID" =>trim($_POST["categoryName"]),
	"OptionName" =>trim($_POST["optionName"]),
	"OptionValue" =>trim($_POST["optionValue"]),
	"Status" => trim($_POST["status"]),
	//"PlantID" => trim($_POST["arrivalPlant"]),
);
$result = callAPI($api, "POST", "", $bodyContentPost);

header('Content-Type: application/json');
echo json_encode($result);
