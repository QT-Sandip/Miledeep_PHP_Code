<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_CERTIFICATION_BODY_MASTER;

$bodyContentPost = array(
	"CertificationBodyID" => trim($_POST["certificationBodyID"]),
	"AuditNameID" => trim($_POST["nameOfAudit"]),
	//"PlantID" => trim($_POST["arrivalPlant"]),
	"CertificationBodyName" => trim($_POST["certicationBodyName"]),
	"Status" => trim($_POST["status"]),
);
// echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
// exit;
$result = callAPI($api, "POST", "", $bodyContentPost);

// echo json_encode($result, JSON_PRETTY_PRINT);
// exit;

header('Content-Type: application/json');
echo json_encode($result);
