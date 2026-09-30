<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$ClientID = $_SESSION["ClientID"];

$sqlPlant = "SELECT ProcessingStationID, StationName 
             FROM `RmProcessingStations` 
             WHERE StationStatus=1";
$stmtPlant = $iconn->query($sqlPlant);

while ($row = $stmtPlant->fetch_array(MYSQLI_ASSOC)) {

    $PlantId = $row['ProcessingStationID'];

    // if ($PlantId == 3) {

    $sqlGetQuery = "SELECT * FROM `AdAntiDumpingDetails` where PlantID='$PlantId'";
    $stmtGetQuery = $iconn->query($sqlGetQuery);

    while ($dataRow = $stmtGetQuery->fetch_array(MYSQLI_ASSOC)) {

        $ProductionDate = $dataRow['ProductionDate'];
        $VarietyID = $dataRow['VarietyID'];
        $PlantID = $dataRow['PlantID'];
        $ProductionID = $dataRow['ADID'];

        // $sqlLastUpdatedDate = "SELECT `ProductionDate`,ADID from AdAntiDumpingDetails where ProductionDate<'$ProductionDate' AND VarietyID='$VarietyID' AND PlantID='$PlantID'  ORDER BY ProductionDate DESC LIMIT 1";
        // $stmtLastUpdatedDate = $iconn->query($sqlLastUpdatedDate);
        // $rowLastUpdatedDate = $stmtLastUpdatedDate->fetch_array(MYSQLI_ASSOC);
        // if ($rowLastUpdatedDate) {
        //     $LastUpdatedDate = $rowLastUpdatedDate['ProductionDate'];
        //     $LastUpdatedId = $rowLastUpdatedDate['ADID'];
        // } else {
        //     $LastUpdatedDate = $ProductionDate;
        //     $LastUpdatedId = 0;
        // }
        // $sqlQuery = "UPDATE `AdAntiDumpingDetails` SET `LastUpdatedDate`='$LastUpdatedDate',`LastUpdatedId`=$LastUpdatedId where `ADID`='$ProductionID'";
        // $stmt = $iconn->query($sqlQuery);
    }
    echo 'PlantId -- ' . $PlantId . "<br><hr>";
    // }

    echo '================================================================================================================<br><hr>';
}
?>