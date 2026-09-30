<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_COUNTRY_ADDRESSES;

$id = trim($_POST["id"]);
$ExporterID = trim($_POST["type"]);
$ExpName = trim($_POST["name"]);
$Address = trim($_POST["address"]);
$Country = trim($_POST["country"]);
$DocType = trim($_POST["docType"]);

$bodyContentPost = array(
	"ID" => $id,
	"ExporterID" => $ExporterID,
	"ExporterName" => $ExpName,
	"ExporterAddress" => $Address,
	"CountryID" => $Country,
	"DocType" => $DocType
);
$result = callAPI($api, "POST", "", $bodyContentPost);

header('Content-Type: application/json');
echo json_encode($result);
