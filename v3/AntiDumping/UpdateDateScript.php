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

    if ($PlantId == 2) {
        // ✅ Use query + fetch_array (MySQLi)
        $sqlStore = "SELECT * FROM `CsStores` WHERE ProductionPlantID = '$PlantId'";
        $stmtStore = $iconn->query($sqlStore);
        $rowStore = $stmtStore->fetch_array(MYSQLI_ASSOC);

        $UnitID = $rowStore['StoreID'];

        // Variety update
        $sql = "SELECT ShortName, MAX(VarietyID) AS MaxVarietyID, 
                       GROUP_CONCAT(VarietyID) AS VID
                FROM ScrmPIVarietyList 
                WHERE STATUS=1
                GROUP BY ShortName, PackingStyle, Species, TypeProduct, FreezingType, Treatment, Grade,Glaze, PreparationType,(CASE WHEN GlazePerc IS NULL OR GlazePerc = 0 THEN 0 ELSE GlazePerc END)";
                echo $sql;


        $stmt = $iconn->query($sql);

        while ($dataRow = $stmt->fetch_array(MYSQLI_ASSOC)) {
            echo print_r($dataRow);
            $VarietyID = $dataRow['MaxVarietyID'];

            if ($VarietyID == 985) {
                // Start date
                $startDate = new DateTime("2025-02-01");
                // End date (today)
                $endDate = new DateTime();

                // // Loop from start date to end date
                // for ($date = $startDate; $date <= $endDate; $date->modify('+1 day')) {
                //     $formattedDate = $date->format("Y-m-d");

                //     // Check if record already exists
                //     $sqlGet = "SELECT * 
                //                FROM `AdVarietyWiseOpeningStock` 
                //                WHERE StockDate='$formattedDate' 
                //                AND VarietyID='$VarietyID' 
                //                AND PlantID='$PlantId'";
                //     $stmtGet = $iconn->query($sqlGet);

                //     if ($stmtGet->num_rows == 0) {
                //         // Insert if not found
                //         $sqlInsert = "INSERT INTO `AdVarietyWiseOpeningStock`
                //                      (`StockDate`, `VarietyID`, `OpeningStock`, `ClosingStock`, `PlantID`, `UnitID`) 
                //                       VALUES 
                //                      ('$formattedDate', '$VarietyID', 0, 0, '$PlantId', '$UnitID')";
                //         $iconn->query($sqlInsert);
                //     }
                // }
            }
        }

        echo 'PlantId -- ' . $PlantId . "<br><hr>";
    }

    echo '================================================================================================================<br><hr>';
}
?>
