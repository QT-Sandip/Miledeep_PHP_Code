<?php
$PageTitle = "Update Duplicate Supc";
$PageTitleFolder = "Sales";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

// updateSupc();
// exit;

// function updateSupc()
// {
//     global $iconn;
//     $processedVarietyIDs = [];

//     echo "Fetching duplicate DeviSUPC groups...<br>";

//     // Fetch all duplicate DeviSUPC groups
//     $duplicateSql = "SELECT * FROM ScrmPIVarietyList WHERE DeviSUPC IN (
//                         SELECT DeviSUPC FROM ScrmPIVarietyList GROUP BY DeviSUPC HAVING COUNT(*) > 1
//                     ) AND DeviSUPC != 0 ORDER BY DeviSUPC ASC, VarietyID ASC";

//     $duplicateExec = $iconn->query($duplicateSql);
//     echo "Duplicate records fetched.<br>";

//     $groupedRecords = [];
//     while ($row = $duplicateExec->fetch_array()) {
//         $groupedRecords[$row['DeviSUPC']][] = $row;
//     }
//     echo "Grouped records by DeviSUPC.<br>";

//     foreach ($groupedRecords as $DeviSUPC => $records) {
//         echo "Processing DeviSUPC: $DeviSUPC<br>";
        
//         array_shift($records);

//         foreach ($records as $record) {
//             $VarietyID = $record['VarietyID'];
//             $TypeProduct = $record['TypeProduct'];
//             $FreezingType = $record['FreezingType'];
//             $PreparationType = $record['PreparationType'];

//             if (in_array($VarietyID, $processedVarietyIDs)) {
//                 echo "Skipping already processed VarietyID: $VarietyID<br>";
//                 continue;
//             }

//             $processedVarietyIDs[] = $VarietyID;

//             if ($FreezingType == 'IQF' || $FreezingType == 'SIQF') {
//                 $FreezingType = 'IQF';
//             }

//             echo "Validating combination: $TypeProduct - $FreezingType - $PreparationType<br>";
//             $SqlClientCode = "SELECT * FROM ScrmPreparationMaster WHERE ShortCode='$PreparationType' AND ItemType='$TypeProduct' AND FType='$FreezingType'";
//             $StmtProduction = $iconn->query($SqlClientCode);
//             $ValidCombo = ($StmtProduction->num_rows > 0);

//             if (!$ValidCombo) {
//                 echo "Invalid Combination: $FreezingType - $TypeProduct - $PreparationType<br>";
//                 continue;
//             }

//             if ($TypeProduct == 'COOKED') {
//                 $CodeStart = 3;
//                 $ColumnName = "WHERE FreezingType='$FreezingType' AND TypeProduct='COOKED'";
//             } elseif ($TypeProduct == 'BLANCHED') {
//                 $CodeStart = 4;
//                 $ColumnName = "WHERE FreezingType='$FreezingType' AND TypeProduct='BLANCHED'";
//             } elseif ($FreezingType == 'IQF' || $FreezingType == 'SIQF') {
//                 $CodeStart = 5;
//                 $ColumnName = "WHERE FreezingType='$FreezingType' AND TypeProduct='$TypeProduct'";
//             } elseif ($FreezingType == 'BLOCK') {
//                 $CodeStart = 1;
//                 $ColumnName = "WHERE FreezingType='BLOCK' AND TypeProduct='$TypeProduct'";
//             } else {
//                 $CodeStart = 'NA';
//             }
//             echo "CodeStart determined: $CodeStart<br>";

//             $SqlClientCode = "SELECT MAX(DeviSUPC) AS ClientCode FROM ScrmPIVarietyList $ColumnName";
//             $StmtProduction = $iconn->query($SqlClientCode);
//             $RowData = $StmtProduction->fetch_array();
//             $ClientCode = $RowData['ClientCode'] + 1;

//             if ($ClientCode == 1) {
//                 $NewDeviSUPC = $CodeStart . '001';
//             } else {
//                 $NewDeviSUPC = $ClientCode;
//             }

//             // check whether the newly generated DeviSUPC already exists in the db or not
//             while (isDeviSUPCExists($iconn, $NewDeviSUPC)) {
//                 $NewDeviSUPC++;
//             }

//             echo "Updating VarietyID: $VarietyID | Old DeviSUPC: $DeviSUPC -> New DeviSUPC: $NewDeviSUPC<br>";
//             $SqlUpdate = "UPDATE ScrmPIVarietyList SET DeviSUPC='$NewDeviSUPC' WHERE VarietyID='$VarietyID'";
//             $iconn->query($SqlUpdate);
//             echo "Update successful for VarietyID: $VarietyID<br><br><br>";
//         }
//     }
// }

// function isDeviSUPCExists($iconn, $DeviSUPC)
// {
//     $SqlCheck = "SELECT COUNT(*) AS count FROM ScrmPIVarietyList WHERE DeviSUPC='$DeviSUPC'";
//     $StmtCheck = $iconn->query($SqlCheck);
//     $RowCheck = $StmtCheck->fetch_array();
//     return ($RowCheck['count'] > 0);
// }
