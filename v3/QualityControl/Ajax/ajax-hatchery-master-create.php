<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_HATCHERY_MASTER;
// {"HatcheryNameID":"","HatcheryName":"TEST NAME","HatcheryLocation":"TEST LOCATION","PlantID":"1","Status":"1"}

$bodyContentPost = array(
	"HatcheryNameID" => trim($_POST["hatcheryNameID"]),
	//"PlantID" => trim($_POST["arrivalPlant"]),
	"HatcheryLocation" => trim($_POST["location"]),
	"HatcheryName" => trim($_POST["hatcheryName"]),
	"Status" => trim($_POST["status"]),
);
// echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
// exit;
$result = callAPI($api, "POST", "", $bodyContentPost);

// echo json_encode($result, JSON_PRETTY_PRINT);
// exit;

header('Content-Type: application/json');
echo json_encode($result);
