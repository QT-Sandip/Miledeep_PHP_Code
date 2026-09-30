<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = "ColdStore/CsDashBoard";

$bodyContent = array(
    "FromDate" => trim($_POST["FromDate"]),
    "ToDate" => trim($_POST["ToDate"]),
    "Species" => trim($_POST["Species"]),
    "PlantID" => trim($_POST["Plant"]),
    "CenterID" => trim($_POST["Center"]),
    "Consignee" => trim($_POST["Consignee"]),
    "PoID" => trim($_POST["PoID"]),
    "ProductCategory" => trim($_POST["ProductCategory"]),
    "PackingTypeData" => trim($_POST["PackingTypeData"]),
    "Variety" => trim($_POST["Variety"])
);

$resList = CallAPI($api, "POST", "", $bodyContent);
echo json_encode($resList);
?>