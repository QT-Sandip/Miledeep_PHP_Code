<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$ClientID = $_SESSION["ClientID"];



$sqlPlant = "SELECT ProcessingStationID,StationName FROM `RmProcessingStations` WHERE StationStatus=1";
$stmtPlant = $iconn->query($sqlPlant); // Execute the first query
while ($row = $stmtPlant->fetch_array()) {

    $PlantId = $row['ProcessingStationID'];
    if ($PlantId == 8) {


        echo 'PlantId -- ' . $PlantId;
        echo '<hr></hr>';

        // update opening and closing   AND StockDate>= '2025-05-02'

        $sqlOpening = "SELECT SPV.ShortName,SPV.TypeProduct,SPV.Grade,SPV.Treatment,SPV.FreezingType,SPV.Glaze,SPV.Species,SPV.PreparationType,SPV.PackingStyle,AD.ProductionDate,AD.ADID FROM AdAntiDumpingDetails AS AD LEFT JOIN ScrmPIVarietyList AS SPV ON AD.VarietyID=SPV.VarietyID WHERE `PlantID`=$PlantId AND ProductionTypeID='Shipment Out' ORDER BY ProductionDate ASC";
        // echo $sqlOpening;exit;
        $stmtOpening = $iconn->query($sqlOpening);
        while ($dataRow = $stmtOpening->fetch_array()) {
            $sqlAdDetails = "SELECT SPD.PoID FROM `ScrmPoGradeData` AS SPGD LEFT JOIN `ScrmPoData` AS SPD ON SPGD.InvId=SPD.PoID 
        LEFT JOIN ScrmCustomInvoices AS SCI ON SPD.PoID=SCI.PiID LEFT JOIN ScrmPIVarietyList AS SPV ON SPGD.VarietyID=SPV.VarietyID
        WHERE SPV.ShortName='" . $dataRow['ShortName'] . "' AND SPV.TypeProduct='" . $dataRow['TypeProduct'] . "' AND SPV.Grade='" . $dataRow['Grade'] . "' AND SPV.Treatment='" . $dataRow['Treatment'] . "' AND SPV.FreezingType='" . $dataRow['FreezingType'] . "'
         AND SPV.Glaze='" . $dataRow['Glaze'] . "' AND SPV.Species='" . $dataRow['Species'] . "' AND SPV.PreparationType='" . $dataRow['PreparationType'] . "' AND SPV.PackingStyle='" . $dataRow['PackingStyle'] . "' AND SCI.StockOutDate='" . $dataRow['ProductionDate'] . "' AND FIND_IN_SET($PlantId, SPD.ProductionPlantID) ORDER BY SPD.PoID DESC LIMIT 1";
            echo $sqlAdDetails;
            $stmtAd = $iconn->query($sqlAdDetails);
            $dataRowAd = $stmtAd->fetch_array();
            // if ($dataRowAd) {
            //     echo print_r($dataRowAd);
            //     $sqlUpdate = "UPDATE `AdAntiDumpingDetails` SET `PoID`= " . $dataRowAd['PoID'] . " where ADID = " . $dataRow['ADID'];
            //     $stmtUpdate = $iconn->prepare($sqlUpdate);
            //     $stmtUpdate->execute();
            //     echo $sqlUpdate;
            // }
            echo '<hr></hr>';
            // exit;
        }
    }
    echo '================================================================================================================';
    echo '<hr></hr>';
}
?>