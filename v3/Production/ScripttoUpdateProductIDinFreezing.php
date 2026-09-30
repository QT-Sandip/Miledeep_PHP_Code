<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");



$sqlVarietyID = "SELECT DISTINCT VarietyID FROM `ProdFreezingData` WHERE ProductID=0 ORDER BY `ProductionID` DESC";
$stmtVarietyID = $iconn->query($sqlVarietyID);




$data = '';
while ($dataRowVarietyID = $stmtVarietyID->fetch_array()) {
    $VarietyID = $dataRowVarietyID['VarietyID'];

    $sqlProductID = "SELECT ProductID FROM `ScrmPIVarietyList` where VarietyID='$VarietyID'";
    $stmtProductID = $iconn->query($sqlProductID);
    $dataRowProductID = $stmtProductID->fetch_array();

    $ProductID = $dataRowProductID['ProductID'];

    echo $sqlUpdateProductID = "UPDATE `ProdFreezingData` SET `ProductID`='$ProductID' WHERE `VarietyID`='$VarietyID'  ";

    // }
    $stmtUpdateProductID = $iconn->query($sqlUpdateProductID);
    if ($stmtUpdateProductID) {
        echo 'HI';

    }
}

echo json_encode($data, JSON_PRETTY_PRINT);
