<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_INDEX_PAGE;

$bodyContent = array(

    'UnitID' => trim($_POST["coldStore"]),
    'Month' => trim($_POST["month"]),
    'Year' => trim($_POST["year"]),

);

$resList = CallAPI($api, "GET", "", $bodyContent);
header('Content-Type: application/json');
echo json_encode($resList);

// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
