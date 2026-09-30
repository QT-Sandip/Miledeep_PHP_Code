<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$ShowTracebilityIds = $ClientConfigData->ShowTracebilityIds;  //anil

$api = API_TRACEABILITY_DROP_DOWN_LOAD_MORE;

$bodyContent = array(
    'SearchKey' => trim($_GET['q']),
    'PageNo' => trim($_GET["page"]),
    'Length' => 100,
    'ShowTracebilityIds' => $ShowTracebilityIds
);
$result = callAPI($api, "POST", "", $bodyContent);
//echo "<pre>"; print_r($result); exit;
header('Content-Type: application/json');
echo json_encode($result);
exit;
?>