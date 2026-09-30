<?php

// ini_set('display_errors', 1);

// ini_set('display_startup_errors', 1);

// error_reporting(E_ALL);

// include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

// /*
// 7   DFL UNIT-1 (VSP)
// 6   DFL UNIT-2 (KKD)
// 2   DFL UNIT-3 (PSP)
// 3   DFL UNIT-4 (PND)
// 1   DFL UNIT-5 (JPT)
// 8   DFL UNIT-6 (JPT-II)
// 4   TALLAREVU
// 5   TALLAREVU-II*/

// $ProductionDateQuery = "'2025-11-24' AND '2025-12-12'";

// $PlantID = 1; // Plant - RmProcessingStations


// $sqlStock = "SELECT CSD.* FROM CsStockData AS CSD LEFT JOIN CsDailyProductionData AS CSPD ON CSD.ProductionID=CSPD.ProductionID 
// WHERE CSD.ProductionDate BETWEEN $ProductionDateQuery AND CSPD.ProductionID IS NULL";

// $stmtStock = $iconn->query($sqlStock);

// while ($dataStock = $stmtStock->fetch_array()) {
//     echo print_r($dataStock);
// echo '<hr></hr>';
//     $productionDate = $dataStock['ProductionDate'];
//     $varietyID = $dataStock['VarietyID'];
//     $Mcs = $dataStock['ActualMcs'];
//     $LooseBags = $dataStock['ActualLooseBags'];
//     $ProductionTypeID = $dataStock['ProductionTypeID'];
//     $ProductCategoryID = $dataStock['ProductCategoryID'];
//     $PlantID = $dataStock['PlantID'];
//     $ShiftID = $dataStock['ShiftID'];
//     $AbStatus = $dataStock['AbStatus'];
//     $StockID = $dataStock['StockID'];
//     $UnitID = $dataStock['UnitID'];
    
//     $sqlProduction = "SELECT * FROM CsDailyProductionData WHERE ProductionDate='$productionDate' AND VarietyID=$varietyID AND ActualMcs=$Mcs AND AbStatus='$AbStatus'
//     AND ActualLooseBags=$LooseBags AND ProductionTypeID='$ProductionTypeID' AND ProductCategoryID='$ProductCategoryID' AND PlantID=$PlantID AND ShiftID=$ShiftID AND (UnitID IS NULL OR UnitID = '')";

//     $stmtProduction = $iconn->query($sqlProduction);

//     $dataProduction = $stmtProduction->fetch_array();
//     if($dataProduction && $dataProduction['ProductionID'] && $dataProduction['ProductionID'] != null && $dataProduction['ProductionID'] != ''){
//         $ProductionID = $dataProduction['ProductionID'];
//         $updateStock = "UPDATE `CsStockData` SET `ProductionID`=$ProductionID WHERE StockID=$StockID";

//         $stmtUpdateStock = $iconn->query($updateStock);

//         echo print_r($stmtUpdateStock);

//         $updateProduction = "UPDATE `CsDailyProductionData` SET `UnitID`=$UnitID WHERE ProductionID=$ProductionID";
//         $stmtupdateProduction = $iconn->query($updateProduction);

//         echo print_r($stmtupdateProduction);
//     }
//     echo print_r($dataProduction);
// echo '<hr></hr>';
// echo '<hr></hr>';
// }

// echo '<hr></hr>';


?>