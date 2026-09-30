<?php
include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';

$methodType = trim($_POST["methodType"]);

if(isset($methodType) && !empty($methodType)) {

     $api = API_RM_ARRIVAL;

    $CenterID = trim($_POST["centerID"]);
    $ArrivalDate = trim($_POST["arrivalDate"]);
    $BillDate = trim($_POST["billDate"]);
    $ArrivalID = trim($_POST["ArrivalID"]);

    $bodyContent = array(
        'ArrivalStationID' => $CenterID,
        'ArrivalDate' => $ArrivalDate,
        'BillDate' => $BillDate,
        'ArrivalID' => $ArrivalID
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);  
    
    header('Content-Type: application/json');
    echo json_encode($resList);

} else {

    $api = API_RM_ARRIVAL_BALANCE_WEIGHT;

    $CenterID = trim($_POST["CenterID"]);
    $PlantID = trim($_POST["PlantID"]);
    $ArrivalDate = trim($_POST["ArrivalDate"]);
    $Species = trim($_POST["Species"]);

    $bodyContent = array(
        'CenterID' => $CenterID,
        'PlantID' => $PlantID,
        'ArrivalDate' => $ArrivalDate,
        'Species' => $Species
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resList);
}

?>

