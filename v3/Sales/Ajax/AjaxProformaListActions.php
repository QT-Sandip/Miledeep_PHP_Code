<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_ORDER_STATUS;

$Action = trim($_POST["Action"]);

if($Action == "Delete") {

    $ID = trim($_POST["ID"]);
    
    $bodyContent = array('OrderID' => $ID);
    $resProforma = CallAPI($api, "DELETE", "", $bodyContent);

    header('Content-Type: application/json');    
    echo json_encode($resProforma);

    // $resProforma->Error
    // $resProforma->Msg

} else if($Action == "Duplicate") {

    $ID = trim($_POST["ID"]);

    $bodyContent = array('OrderID' => $ID);
    $resProforma = CallAPI($api, "POST", "", $bodyContent);
   
    header('Content-Type: application/json');
    echo json_encode($resProforma);
    
} else if($Action == "Get") {

    $bodyContent = array('ApprovalType' => "Bulk");
    $resProforma = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resProforma);

} else if ($Action == "Put") {
    
    $Status = trim($_POST["Status"]);
    $OrderIDs = $_POST["OrderIDs"];
    
    $orderID = implode(', ', $OrderIDs);
    
    $bodyContent = array(
        "ApprovalType" => "Bulk",
        "Status" => $Status,
        "OrderIDs" => $orderID,
        "PoNumber" => "",
        "PoDate" => "",
        "Remarks" => ""
    );
    
    $resProforma = CallAPI($api, "PUT", "", $bodyContent);
    
    header('Content-Type: application/json');    
    echo json_encode($resProforma);

} else if ($Action == "SingleApprove") {

    $Status = trim($_POST["Status"]);
    $OrderId = trim($_POST["OrderId"]);
    $PoNumber = $_POST["PoNumber"];
    $PoDate = $_POST["PoDate"];
    $Remarks = $_POST["Remarks"];
    $ProdPlant = $_POST["ProdPlant"];

    $bodyContent = array(
        "ApprovalType" => "Single",
        "Status" => $Status,
        "OrderIDs" => $OrderId,
        "PoNumber" => $PoNumber,
        "PoDate" => $PoDate,
        "Remarks" => $Remarks,
        "ProdPlant" => $ProdPlant
    );

    $resProforma = CallAPI($api, "PUT", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resProforma);

} else if ($Action == "GetPoDetails") {

    $POID = trim($_POST["POID"]);

    $api = API_PO_STATUS_DETAILS;

    $bodyContent = array(
        "POID" => $POID
    );

    $resProDetails = CallAPI($api, "GET", "", $bodyContent);
    // echo json_encode($resProDetails , JSON_PRETTY_PRINT);

    header('Content-Type: application/json');
    echo json_encode($resProDetails);
} 

?>