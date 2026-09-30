<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_PREPARATION_MASTARS;

$bodyContentPost = array(

"ID" => trim($_POST["id"]),
"PreparationName" => trim($_POST["prepName"]),
"PreparationDesc" => trim($_POST["prepDesc"]), 
"ShortCode" => trim($_POST["shortCode"]),
"QcPreparationShortCode" => trim($_POST["qcPrepShortCode"]),
"TypeProduct" => trim($_POST["prodType"]),
"FreezingType" => trim($_POST["freezingType"]),
"HSCode" => trim($_POST["hsCode"]),
"GstPerc" => trim($_POST["gstPerc"])

);
$result = callAPI($api, "POST", "", $bodyContentPost);

// echo json_encode($result, JSON_PRETTY_PRINT);
// exit;
header('Content-Type: application/json');
echo json_encode($result);
