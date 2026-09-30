<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$HarvestFormNewDesignForSME = !empty($ClientConfigData->HarvestFormNewDesignForSME)?$ClientConfigData->HarvestFormNewDesignForSME:'';//SMEV3



$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$hasHarvestDate = $ArrayClientConfigData['hasHarvestDate'];

$BookingID = isset($_GET['BookingID']) && !empty($_GET['BookingID']) ? $_GET['BookingID'] : "";
$HarvestID = $_GET['HarvestingID'];

$api = API_RM_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resRmGraders = $resDropdownsAPI->Data->RmGraders;
$resRmVariety = $resDropdownsAPI->Data->RmVariety;
$resBoysGroup = $resDropdownsAPI->Data->BoysGroup;
$Vehicles = $resDropdownsAPI->Data->Vehicles;

$apiHarvestData = API_HARVESTING_DETAILS;

$bodyContent = array(
    "HarvestingID" => $HarvestID,
    "BookingID" => $BookingID,
    'HarvestFormNewDesignForSME' => $HarvestFormNewDesignForSME
);

$result = CallAPI($apiHarvestData, "GET", "", $bodyContent);
$HarvestData = $result->Data[0];
// echo json_encode($result, JSON_PRETTY_PRINT);
// exit;
if($HarvestFormNewDesignForSME  == 1){
    include_once  ROOT.'v3/Purchase/create-harvesting-sme.php';
}else{
    include_once  ROOT.'v3/Purchase/create-harvesting-coastal.php';
}

?>

