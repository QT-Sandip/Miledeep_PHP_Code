<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
// include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");

// $TotalMcs = 0;
// $TotalBags = 0;
// $TotalKgs = 0;

// $sqlQuery = "
//     SELECT DISTINCT(CSD.StockID) AS StockId

//     FROM CsStockData AS CSD

//     LEFT JOIN ScrmPoGradeData AS SPGD
//         ON CSD.PoGradeId = SPGD.ID
//         AND CSD.VarietyID = SPGD.VarietyID

//     WHERE
//         CSD.PoGradeId != 0
//         AND SPGD.ID IS NULL
// ";

// $stmtStockData = $iconn->query($sqlQuery);

// while ($dataRow = $stmtStockData->fetch_array()) {

//     $StockId = $dataRow['StockId'];

//     $sqlStockOutQuery = "
//         SELECT *
//         FROM CsStockOutDetails
//         WHERE StockID = '$StockId'
//     ";

//     $stmtStockOutQuery = $iconn->query($sqlStockOutQuery);

//     $OutMcs = 0;
//     $OutBags = 0;
//     $OutKgs = 0;

//     $StockOutID = "";
//     $CsStockOutId = "";

//     while ($DataRowStockOut = $stmtStockOutQuery->fetch_array()) {

//         if ($StockOutID == "") {
//             $StockOutID = $DataRowStockOut['StockOutID'];
//         } else {
//             $StockOutID .= "," . $DataRowStockOut['StockOutID'];
//         }

//         if ($CsStockOutId == "") {
//             $CsStockOutId = $DataRowStockOut['CsStockOutID'];
//         } else {
//             $CsStockOutId .= "," . $DataRowStockOut['CsStockOutID'];
//         }

//         $OutMcs += (float)$DataRowStockOut['OutMcs'];
//         $OutBags += (float)$DataRowStockOut['OutLooseBags'];
//         $OutKgs += (float)$DataRowStockOut['ActualOutKgs'];

//         $SqlUpdateDailyStockOut = "

//             UPDATE CsDailyStockOutData

//             SET

//                 ActualOutMcs =
//                     ActualOutMcs - " . (float)$DataRowStockOut['OutMcs'] . ",

//                 ActualOutLooseBags =
//                     ActualOutLooseBags - " . (float)$DataRowStockOut['OutLooseBags'] . ",

//                 AvalOutMcs =
//                     AvalOutMcs - " . (float)$DataRowStockOut['OutMcs'] . ",

//                 AvalOutLooseBags =
//                     AvalOutLooseBags - " . (float)$DataRowStockOut['OutLooseBags'] . ",

//                 ActualOutKgs =
//                     ActualOutKgs - " . (float)$DataRowStockOut['ActualOutKgs'] . ",

//                 AvalOutKgs =
//                     AvalOutKgs - " . (float)$DataRowStockOut['ActualOutKgs'] . "

//             WHERE CsStockOutID = '" . $DataRowStockOut['CsStockOutID'] . "'

//         ";

//         $StmtUpdateDailyStockOut = $iconn->query($SqlUpdateDailyStockOut);

//         if ($StmtUpdateDailyStockOut) {

//             echo "success StmtUpdateDailyStockOut : "
//                 . $DataRowStockOut['CsStockOutID'] . "<br>";
                
//         }
//     }

//     $SqlUpdateDailyProductionStatus = "

//         UPDATE CsStockData

//         SET
//             PoGradeId = '0',
//             PoID = '0',
//             PackingTypeID = 'DUMMY'

//         WHERE StockID = '$StockId'

//     ";

//     $stmtUpdateDailyProductionStatus =
//         $iconn->query($SqlUpdateDailyProductionStatus);

//     if ($stmtUpdateDailyProductionStatus) {

//         echo "success stmtUpdateDailyProductionStatus : "
//             . $StockId . "<br>";

//         $SqlDelete =  "UPDATE `CsStockOutDetails` SET `OutType`='DUMMY OUT',`OutDate`='2026-03-31',`CsStockOutID`='99999999' WHERE `StockID`= '$StockId' ";
//         $stmtDelete = $iconn->query($SqlDelete);
//         if($stmtDelete){
//             echo "delete scuess : $StockId";
//         }
//     }
//     $TotalMcs += (float)$OutMcs;
//     $TotalBags += (float)$OutBags;
//     $TotalKgs += (float)$OutKgs;
// }

// $sqlDailyout = "INSERT INTO `CsDailyStockOutData`( `ProductionID`, `StockOutDate`, `Reason`, `ActualOutMcs`, `ActualOutLooseBags`, `AvalOutMcs`, `AvalOutLooseBags`, `ActualOutKgs`, `AvalOutKgs`, `Remarks`, `OutBatchCode`, `DailyStockOutStatus`) VALUES (0,'2026-03-31','DUMMY OUT',$TotalMcs,$TotalBags,$TotalMcs,$TotalBags,$TotalKgs,$TotalKgs,'','20260331-99',0)";
//  $stmtDailyout = $iconn->query($sqlDailyout);
//  if ($stmtDailyout) {

//     $LastInsertedID = $iconn->insert_id;
//     $SqlDelete1 =  "UPDATE `CsStockOutDetails` SET `CsStockOutID`=$LastInsertedID WHERE `CsStockOutID`='99999999' ";
//         $stmtDelete1 = $iconn->query($SqlDelete1);
//         if($stmtDelete1){
//             echo "delete scuess : $StockId";
//         }
    
// }
?>