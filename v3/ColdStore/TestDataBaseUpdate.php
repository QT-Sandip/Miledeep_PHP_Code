<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");


$sqlRackData = "SELECT RowID,BarCodeID FROM `CsStoresRackData` WHERE BarCodeID!=''";
$stmtRackData = $iconn->query($sqlRackData);

$data = '';
while ($dataRow = $stmtRackData->fetch_array()) {

    $RowID = $dataRow['RowID'];
    $BarCodeID = $dataRow['BarCodeID'];

    echo $sqlUpdateRack = "UPDATE CsStockData SET StoreRackID='$RowID',StockFloorStatus=1 WHERE BarCodeID='$BarCodeID'";
    // $stmtUpdateRack = $iconn->query($sqlUpdateRack);
    // if ($stmtRackData) {
    //     $data = "Rack Data Updated";
    // } else {
    //     $data = "Rack Data Updated failed";
    // }
}

echo json_encode($data, JSON_PRETTY_PRINT);
