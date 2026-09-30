<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_MASTERS_COUNTS;

$bodyContentPost = array(

    "RmCount" => trim($_POST["count"]),
    "Stype" => trim($_POST["species"]),
    "RateId" => trim($_POST["rateId"]),
);
//echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);

$result = CallAPI($api, "POST", "", $bodyContentPost);

// echo json_encode($result, JSON_PRETTY_PRINT);
// exit;
header('Content-Type: application/json');
echo json_encode($result);
