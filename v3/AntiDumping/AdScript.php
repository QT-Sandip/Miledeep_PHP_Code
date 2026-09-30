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
    if ($PlantId == 1) {

        // variety update
//         $sql = "
// CREATE TEMPORARY TABLE tmp_variety_map AS
// SELECT
//     ShortName,
//     PackingStyle,
//     Species,
//     TypeProduct,
//     FreezingType,
//     Treatment,
//     Grade,
//     MAX(VarietyID) AS MaxVarietyID
// FROM ScrmPIVarietyList
// WHERE STATUS = 1
// GROUP BY
//     ShortName,
//     PackingStyle,
//     Species,
//     TypeProduct,
//     FreezingType,
//     Treatment,
//     Grade
// ";

//         if (!$iconn->query($sql)) {
//             die("Temp table creation failed: " . $iconn->error);
//         }

//         // 2️⃣ Update AdVarietyWiseOpeningStock (single query)
//         $sqlUpdateOpening = "
// UPDATE AdVarietyWiseOpeningStock AS A
// JOIN ScrmPIVarietyList AS S
//     ON A.VarietyID = S.VarietyID
// JOIN tmp_variety_map AS T
//     ON T.ShortName = S.ShortName
//    AND T.PackingStyle = S.PackingStyle
//    AND T.Species = S.Species
//    AND T.TypeProduct = S.TypeProduct
//    AND T.FreezingType = S.FreezingType
//    AND T.Treatment = S.Treatment
//    AND T.Grade = S.Grade
// SET A.VarietyID = T.MaxVarietyID
// ";

//         if (!$iconn->query($sqlUpdateOpening)) {
//             die("Opening stock update failed: " . $iconn->error);
//         }

//         // 3️⃣ Update AdAntiDumpingDetails (single query)
//         $sqlUpdateAntiDump = "
// UPDATE AdAntiDumpingDetails AS A
// JOIN ScrmPIVarietyList AS S
//     ON A.VarietyID = S.VarietyID
// JOIN tmp_variety_map AS T
//     ON T.ShortName = S.ShortName
//    AND T.PackingStyle = S.PackingStyle
//    AND T.Species = S.Species
//    AND T.TypeProduct = S.TypeProduct
//    AND T.FreezingType = S.FreezingType
//    AND T.Treatment = S.Treatment
//    AND T.Grade = S.Grade
// SET A.VarietyID = T.MaxVarietyID
// ";

//         if (!$iconn->query($sqlUpdateAntiDump)) {
//             die("Anti dumping update failed: " . $iconn->error);
//         }

//         // 4️⃣ Optional cleanup
//         $iconn->query("DROP TEMPORARY TABLE IF EXISTS tmp_variety_map");

//         echo "Update completed successfully.";

        




//////// variety update for grade

        // $sqlPreview = "
// SELECT a.ADID AS AdAntiDumpingDetailsID, b.MaxVarietyID
// FROM AdAntiDumpingDetails a
// LEFT JOIN ScrmPIVarietyList SPVL ON a.VarietyID = SPVL.VarietyID
// JOIN (
//     SELECT 
//         ShortName,
//         PackingStyle,
//         Species,
//         TypeProduct,
//         FreezingType,
//         Treatment,
//         Grade,
//         Glaze,
//         PreparationType,
//         (CASE WHEN GlazePerc IS NULL OR GlazePerc = 0 THEN 0 ELSE GlazePerc END) AS GlazePerc,
//         MAX(VarietyID) AS MaxVarietyID
//     FROM ScrmPIVarietyList
//     WHERE Status = 1
//     GROUP BY 
//         ShortName, PackingStyle, Species, TypeProduct, 
//         FreezingType, Treatment, Grade, 
//         Glaze, PreparationType, 
//         (CASE WHEN GlazePerc IS NULL OR GlazePerc = 0 THEN 0 ELSE GlazePerc END)
// ) b 
// ON SPVL.ShortName = b.ShortName
// AND a.PackingStyleID = b.PackingStyle
// AND SPVL.Species = b.Species
// AND SPVL.TypeProduct = b.TypeProduct
// AND a.FreezingType = b.FreezingType
// AND a.SockingType = b.Treatment
// AND a.Grade = b.Grade
// AND SPVL.Glaze = b.Glaze
// AND SPVL.PreparationType = b.PreparationType
// AND (CASE WHEN SPVL.GlazePerc IS NULL OR SPVL.GlazePerc = 0 THEN 0 ELSE SPVL.GlazePerc END) = b.GlazePerc
// WHERE a.VarietyID <> b.MaxVarietyID
// ";

        // // Execute and fetch rows before update
// $result = $iconn->query($sqlPreview);
// while ($row = $result->fetch_assoc()) {
//         //     $result = $iconn->query($sqlPreview);

        //         // while ($row = $result->fetch_array()) {
//     echo "Row ID {$row['AdAntiDumpingDetailsID']} will be updated to VarietyID {$row['MaxVarietyID']}\n";
//     echo '<hr></hr>';
// }

        // $sqlUpdate = "UPDATE AdAntiDumpingDetails a
// LEFT JOIN ScrmPIVarietyList SPVL ON a.VarietyID = SPVL.VarietyID
// JOIN (
//     SELECT 
//         ShortName,
//         PackingStyle,
//         Species,
//         TypeProduct,
//         FreezingType,
//         Treatment,
//         Grade,
//         Glaze,
//         PreparationType,
//         (CASE WHEN GlazePerc IS NULL OR GlazePerc = 0 THEN 0 ELSE GlazePerc END) AS GlazePerc,
//         MAX(VarietyID) AS MaxVarietyID
//     FROM ScrmPIVarietyList
//     WHERE Status = 1
//     GROUP BY 
//         ShortName, 
//         PackingStyle, 
//         Species, 
//         TypeProduct, 
//         FreezingType, 
//         Treatment, 
//         Grade,
//         Glaze, 
//         PreparationType,
//         (CASE WHEN GlazePerc IS NULL OR GlazePerc = 0 THEN 0 ELSE GlazePerc END)
// ) b 
// ON SPVL.ShortName = b.ShortName
// AND a.PackingStyleID = b.PackingStyle
// AND SPVL.Species = b.Species
// AND SPVL.TypeProduct = b.TypeProduct
// AND a.FreezingType = b.FreezingType
// AND a.SockingType = b.Treatment
// AND a.Grade = b.Grade
// AND SPVL.Glaze = b.Glaze
// AND SPVL.PreparationType = b.PreparationType
// AND (CASE WHEN SPVL.GlazePerc IS NULL OR SPVL.GlazePerc = 0 THEN 0 ELSE SPVL.GlazePerc END) = b.GlazePerc
// SET a.VarietyID = b.MaxVarietyID;
// ";

        // $iconn->query($sqlUpdate);


        echo 'PlantId -- ' . $PlantId;
        echo '<hr></hr>';

       ////// // update opening and closing   AND StockDate>= '2025-02-17'

        $sqlOpening = "SELECT * FROM AdVarietyWiseOpeningStock WHERE `PlantID` = $PlantId AND StockDate>= '2025-10-01' ORDER BY StockDate ASC";
        // echo $sqlOpening;exit;
        $stmtOpening = $iconn->query($sqlOpening);
        while ($dataRow = $stmtOpening->fetch_array()) {
            echo 'StockDate -- ' . $dataRow['StockDate'];
            // $sqlAdDetails = "SELECT SUM(ActualSlabs) AS TotalSlabs,GroupType FROM `AdAntiDumpingDetails` WHERE `VarietyID`='" . $dataRow['VarietyID'] . "' AND `ProductionDate` <'" . $dataRow['StockDate'] . "'  AND `UnitID` = '" . $dataRow['UnitID'] . "' GROUP BY GroupType";
            // $stmtAdDetails = $iconn->query($sqlAdDetails);
            // $inward = 0;
            // $outward = 0;
            // while ($dataRow1 = $stmtAdDetails->fetch_array()) {
            //     if ($dataRow1['GroupType'] == 1) {
            //         $inward = $dataRow1['TotalSlabs'];
            //     } else if ($dataRow1['GroupType'] == 2) {
            //         $outward = $dataRow1['TotalSlabs'];
            //     }
            // }
            // $opening = 0;
            // $sqlAd = "SELECT SUM(ActualSlabs) AS TotalSlabs FROM `AdAntiDumpingDetails` WHERE `VarietyID`='" . $dataRow['VarietyID'] . "' AND `ProductionDate` ='" . $dataRow['StockDate'] . "'  AND `UnitID` = '" . $dataRow['UnitID'] . "' AND ProductionTypeID='Opening Stock'";
            // $stmtAd = $iconn->query($sqlAd);
            // $dataRowAd = $stmtAd->fetch_array();
            // if ($dataRowAd) {
            //     $opening = $dataRowAd['TotalSlabs'];
            // }
            // $sqldump = "SELECT SUM(ActualSlabs) AS TotalSlabs,GroupType FROM `AdAntiDumpingDetails` WHERE `VarietyID`='" . $dataRow['VarietyID'] . "' AND `ProductionDate` ='" . $dataRow['StockDate'] . "'  AND `UnitID` = '" . $dataRow['UnitID'] . "' AND ProductionTypeID!='Opening Stock' GROUP BY GroupType";
            // $stmtDump = $iconn->query($sqldump);
            // $dayInward = 0;
            // $dayOutward = 0;
            // while ($dataRowDump = $stmtDump->fetch_array()) {
            //     if ($dataRowDump['GroupType'] == 1) {
            //         $dayInward = $dataRowDump['TotalSlabs'];
            //     } else if ($dataRowDump['GroupType'] == 2) {
            //         $dayOutward = $dataRowDump['TotalSlabs'];
            //     }
            // }

            // $openingStock = ($inward - $outward) + $opening;
            // $closingStock = $openingStock + $dayInward - $dayOutward;

            // $sqlUpdate = "UPDATE `AdVarietyWiseOpeningStock` SET `OpeningStock`=$openingStock,`ClosingStock`=$closingStock where DataID = " . $dataRow['DataID'];
            // $stmtUpdate = $iconn->prepare($sqlUpdate);
            // $stmtUpdate->execute();
                    echo $sqlUpdate;
            echo '<hr></hr>';
            // exit;
        }
    }
    echo '================================================================================================================';
    echo '<hr></hr>';
}
?>