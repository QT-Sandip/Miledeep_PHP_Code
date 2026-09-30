<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");


//  $sqlPiData = "SELECT PoID,ProcessorID FROM ScrmPIData WHERE ProductionPlantID='' OR ProductionPlantID=0";
$sqlPiData = "SELECT PoID,ProcessorID,ProductionPlantID FROM ScrmPIData";
$stmtPiData = $iconn->query($sqlPiData);

$data = [];
while ($dataRow = $stmtPiData->fetch_array()) {

    $PoID = $dataRow['PoID'];
    $ProcessorID = $dataRow['ProcessorID'];
    $ProductionPlantID = $dataRow['ProductionPlantID'];

    $sqlAED = "SELECT InvShortCode FROM AdminExporterDetails WHERE ExpID='$ProcessorID'";
    $stmtAED = $iconn->query($sqlAED);
    $dataAED = $stmtAED->fetch_array();
    $invShortCode = $dataAED['InvShortCode'];

    // $sqlRmPS = "SELECT GROUP_CONCATE(ProcessingStationID), ProdCode  FROM  RmProcessingStations WHERE ProdCode='$invShortCode'";
    $sqlRmPS = "SELECT GROUP_CONCAT(ProcessingStationID SEPARATOR ', ') AS ProcessingStationIDs, ProdCode FROM RmProcessingStations
    WHERE ProdCode = '$invShortCode' GROUP BY ProdCode";
    $stmtRmPS = $iconn->query($sqlRmPS);
    $dataRmPS = $stmtRmPS->fetch_array();
    $ProcessingStationID = $dataRmPS['ProcessingStationIDs'];

    // $errorInfo = $stmtRmPS->errorInfo();
    // var_dump($errorInfo);

    $sqlInsertPlant = "UPDATE ScrmPIData SET ProductionPlantID='$ProcessingStationID' WHERE PoID='$PoID'";
    $stmtInsertPlant = $iconn->query($sqlInsertPlant);

    if ($stmtInsertPlant) {
        $sqlUpdatePlantId = "UPDATE ScrmPoData SET ProductionPlantID='$ProcessingStationID' WHERE PoID='$PoID'";
        $stmtUpdatePlantId = $iconn->query($sqlUpdatePlantId);
        if ($stmtUpdatePlantId) {
            echo "success";
        }
    }

    $list['PoID'] = $PoID;
    $list['ProcessorID'] = $ProcessorID;
    $list['ProductionPlantID'] = $ProductionPlantID;
    $list['invShortCode'] = $invShortCode;
    $list['ProcessingStationID'] = $ProcessingStationID;

    $data[] = $list;
}

echo json_encode($data, JSON_PRETTY_PRINT);
