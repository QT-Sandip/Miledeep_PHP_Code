<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_FARM_MASTER;

$bodyContentPost = array(
	"FarmID" => trim($_POST["farmID"]),
	"FarmName" => trim($_POST["farmName"]),
	"FarmRegistrationNo" => trim($_POST["registrationNo"]),
	"FarmerName" => trim($_POST["farmerName"]),
	"FarmerAddress" => trim($_POST["farmerAddress"]),
	"PhoneNumber" => trim($_POST["farmerPhoneNumber"]),
	"TonnageValue" => trim($_POST["tonnageValue"]),
	"Status" => trim($_POST["status"]),
);
// echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
// exit;
$result = callAPI($api, "POST", "", $bodyContentPost);

// echo json_encode($result, JSON_PRETTY_PRINT);
// exit;

header('Content-Type: application/json');
echo json_encode($result);
