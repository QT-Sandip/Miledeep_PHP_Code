<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_GS_CHEM_PO;

$ChemPoID = trim($_GET['ChemPoID']);

$bodyContent = array(
    "ChemPoID" => $ChemPoID
);

$response = CallAPI($api, "GET", "", $bodyContent);

// if ($response && isset($response->Data[0])) {

    // $data = $response->Data[0];

    header('Content-Type: application/json');
    echo json_encode($response->Data[0]);

// } else {
    
//     echo json_encode(array());
// }

?>