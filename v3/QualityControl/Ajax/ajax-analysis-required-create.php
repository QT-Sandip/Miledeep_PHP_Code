<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_REQUIRED_ANALYSIS;

$TestID = trim($_POST["testID"]);
$TestCategory = trim($_POST["testCatName"]);
$TestName = trim($_POST["testName"]);

$bodyContentPost = array(
    "TestID" => $TestID,
    "TestCategory" => $TestCategory,
    "TestName" => $TestName

);

$result = callAPI($api, "POST", "", $bodyContentPost);

header('Content-Type: application/json');
echo json_encode($result);
