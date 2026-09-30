<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_HATCHERY_MASTERS;

$bodyContentPost = array(

    "HatcheryID" => trim($_POST["hatcheryID"]),
    "HatcryName" => trim($_POST["hatcheryName"]),
    "HatcryLocation" => trim($_POST["hatcheryLocation"]),
    "HatcryCapacity" => trim($_POST["hatcryCapacity"])

);
$result = callAPI($api, "POST", "", $bodyContentPost);

header('Content-Type: application/json');
echo json_encode($result);
