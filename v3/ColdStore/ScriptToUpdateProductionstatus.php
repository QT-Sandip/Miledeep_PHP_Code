<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");

$sqlFields = "ProductionID,(ActualMcs*TotalSlabs*SlabWeight*MFactor) as Kgs, (ActualLooseBags*SlabWeight*MFactor) as LKgs";

  $sqlQuery = "SELECT $sqlFields  FROM `CsDailyProductionData` as CSD
left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID ";  

$stmtStockData = $iconn->query($sqlQuery);

while ($dataRow = $stmtStockData->fetch_array()) {
    $TotalProductionWeight = $dataRow['Kgs'] + $dataRow['LKgs'];
    $ProductionID = $dataRow['ProductionID'];

    $sqlFields = "COALESCE(sum(ActualMcs*TotalSlabs*SlabWeight*MFactor) ,0)as INKgs,COALESCE(sum(ActualLooseBags*SlabWeight*MFactor),0) as INLKgs ";

    $sqlBalancekgsQuery = "SELECT $sqlFields FROM `CsStockData` as CSD
    left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
    left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
       where ProductionID='$ProductionID' ";
    $stmtBalancekgsQuery = $iconn->query($sqlBalancekgsQuery);


   $dataRowBalancekgsQuery= $stmtBalancekgsQuery->fetch_array();
   $TotalInwardProductionWeight = $dataRowBalancekgsQuery['INKgs'] + $dataRowBalancekgsQuery['INLKgs'];
    
    $TotalWeight = round($TotalProductionWeight - $TotalInwardProductionWeight, 2);
    $BalanceKgs = $TotalWeight;
echo $BalanceKgs .'<br>'.$ProductionID.'<br>';

if ($BalanceKgs <= 0) {
//   echo  $ProductionID.'<br>';
    // $Status = 1;

 echo   $SqlUpdateDailyProductionStatus="UPDATE `CsDailyProductionData` SET `StockStatus`='1' WHERE `ProductionID`='$ProductionID'";
   $stmtUpdateDailyProductionStatus = $iconn->query($SqlUpdateDailyProductionStatus);

    
    
}
else{
    echo   $SqlUpdateDailyProductionStatus="UPDATE `CsDailyProductionData` SET `StockStatus`='0' WHERE `ProductionID`='$ProductionID'";
   $stmtUpdateDailyProductionStatus = $iconn->query($SqlUpdateDailyProductionStatus); 
}
if($stmtUpdateDailyProductionStatus){
    echo "success";
        }
}
echo json_encode($data, JSON_PRETTY_PRINT);

 