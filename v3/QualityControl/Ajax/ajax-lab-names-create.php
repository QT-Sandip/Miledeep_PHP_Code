<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_LAB_MASTERS;

$LabId = trim($_POST["labID"]);
$LabName = trim($_POST["labName"]);
$LabAddress = trim($_POST["addressName"]);
$Description = trim($_POST["description"]);

$bodyContentPost = array(
    "LabId" => $LabId,
    "LabName" => $LabName,
    "Address" => $LabAddress,
    "Description" => $Description

);

$result = callAPI($api, "POST", "", $bodyContentPost);

header('Content-Type: application/json');
echo json_encode($result);
