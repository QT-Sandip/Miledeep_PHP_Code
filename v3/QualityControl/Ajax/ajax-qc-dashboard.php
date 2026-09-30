<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = "QualityControl/QcDashBoard";

$bodyContent = array(
    "FromDate" => trim($_POST["FromDate"]),
    "ToDate" => trim($_POST["ToDate"]),
    "Species" => trim($_POST["Species"]),
    "PlantID" => trim($_POST["Plant"]),
    "CenterID" => trim($_POST["Center"]),
    "Counts" => trim($_POST["Counts"]),
    "RmFromDate" => trim($_POST["RmFromDate"]),
    "RmToDate" => trim($_POST["RmToDate"]),
);

$resList = CallAPI($api, "POST", "", $bodyContent);
echo json_encode($resList);
?>