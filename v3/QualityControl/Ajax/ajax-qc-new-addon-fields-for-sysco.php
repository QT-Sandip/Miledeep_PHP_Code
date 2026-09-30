<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_NEW_ADDON_FIELDS_FOR_SYSCO;

$bodyContentPost = array(

    "PoID" => trim($_POST["id"]),
    "FactoryID" => trim($_POST["factoryId"]),
    "ShipmentDate" => trim($_POST["shipmentDate"]),
    "RequestSentDate" => trim($_POST["requestSentDate"]),
    "SampleSendingDate" => trim($_POST["sampleSendingDate"]),
    "ReportExpectingDate" => trim($_POST["reportExpectingDate"]),
    "SGSRequestDate" => trim($_POST["sgsRequestDate"]),
    "SimpSentDate" => trim($_POST["simpSentDate"]),
    "LotCode" => trim($_POST["lotCode"]),
    "Status" => trim($_POST["status"])
);
// var_dump($bodyContentPost);
$result = callAPI($api, "POST", "", $bodyContentPost);

// echo json_encode($result, JSON_PRETTY_PRINT);
// exit;
header('Content-Type: application/json');
echo json_encode($result);
