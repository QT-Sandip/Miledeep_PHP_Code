<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_CURRENCY_MASTERS;

$CurrencyID = trim($_POST["currencyID"]);
$CurrencyName = trim($_POST["currencyName"]);
$CurrencyCode = trim($_POST["currencyCode"]);
$CurrencySymbol = trim($_POST["currencySymbol"]);
$ShortCode = trim($_POST["shortCode"]);
$Status = trim($_POST["status"]);

$bodyContentPost = array(
    "CurrencyID" => $CurrencyID,
    "CurrencyName" => $CurrencyName,
    "CurrencyCode" => $CurrencyCode,
    "CurrenySymbol" => $CurrencySymbol,
    "CurrencyShortCode" => $ShortCode,
    "Status" => $Status

);

$result = callAPI($api, "POST", "", $bodyContentPost);

header('Content-Type: application/json');
echo json_encode($result);
