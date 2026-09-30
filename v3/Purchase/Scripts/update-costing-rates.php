<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$PageTitle="Barcode List";
$PageTitleFolder="Cold Store";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

// $ClientID=$_SESSION["ClientID"];
// $BarcodeDate=$_GET["id"];

$sql = "SELECT RAD.Species as SType,RmLotID,RmReferenceID, LotNumber, PurchaseDate, RMD.LotStatus, RmPC.PurchaseType, 
    GD.GradingDate, GD.LotID as GdAval, RMBD.ArrivalID as BillAval, RMBD.BillStatus, QCAbO.Name as AbStatus, 
    RmFin.FinancierName as FinancierName, RMBD.ArrivalID AS CostingAval, 
    (SELECT COUNT(*) from QcRmOrgnReport as ORGD WHERE ORGD.LotID=RMD.RmLotID ) as OrgnReport from RmLotDetatils as RMD 
    LEFT JOIN PpcHLGradingDetails as GD on RMD.RmLotID=GD.LotID 
    LEFT JOIN RmArrivalDetatils as RAD on RAD.RmArrivalID=RMD.RmReferenceID
    LEFT JOIN RmBillDetails as RMBD on RMBD.ArrivalID=RMD.RmReferenceID 
    LEFT JOIN QcRmAbReport as QCAbR on RMD.RmLotID=QCAbR.LotID 
    LEFT JOIN QcABOptions as QCAbO on QCAbO.OptID=QCAbR.AbStatus 
    LEFT JOIN RmSuppliers as RmFin on RmFin.ID=RMBD.FinancierID 
    LEFT JOIN RmPurchaseCategory as RmPC on RmPC.CatID=RAD.PurchaseCategoryID 
    where PurchaseDate>='2025-02-20' AND GD.LotID!='' and RMBD.ArrivalID!=''";
$stmt = $iconn->query($sql); // Execute the first query
while ($row1 = $stmt->fetch_array()) {
        $SqlTrackId = "SELECT * FROM `SalesCostingRatesHistory` 
                WHERE FromDate <= '" . $row1['PurchaseDate'] . "' 
                AND SType = '" . $row1['SType'] . "' 
                ORDER BY FromDate DESC, ID DESC  
                LIMIT 1";
        $TrackData = $iconn->query($SqlTrackId);        
        $TrackData=$TrackData->fetch_array();
        $TrackID=$TrackData['ID'];

        $SqlFinalGrading = "SELECT * FROM `PpcHLGradingBreakup` WHERE LotID='".$row1['RmLotID']."' AND Usd='0.00' order by HLCount ASC";
        $SmtFinalGrading = $iconn->query($SqlFinalGrading); 
        while ($RowFinalCounts =  $SmtFinalGrading->fetch_array( MYSQLI_ASSOC)) {
            $HLCount = $RowFinalCounts['HLCount'];
            $HLWeight = $RowFinalCounts['HLWeight'];
             $SqlCostingRates = "SELECT * FROM SalesCostingRates WHERE TrackID='$TrackID' and LowerLimit <= '$HLCount' and UpperLimit >= '$HLCount'";
            $SmtCostingRates = $iconn->query($SqlCostingRates);        
            $CostingRates=$SmtCostingRates->fetch_array();
            $Usd = $CostingRates['USD'];
            $Grade = $CostingRates['Grade'];
            if($Usd > 0){
                    $GradesUsdQuery = "UPDATE PpcHLGradingBreakup set Usd='$Usd' 
            where LotID='".$row1['RmLotID']."' and HLCount='$HLCount'";
                //echo "Updated: ".$row1['LotNumber']." : ".$RowFinalCounts['Usd']." : ".$Usd." : ".$RowFinalCounts['PackingGrade'];
                //echo "<br/>";
                $updateQuery = $iconn->query($GradesUsdQuery);
                if($updateQuery){
                  echo "Updated: ".$row1['LotNumber']." : ".$RowFinalCounts['Usd']." : ".$Usd." : ".$RowFinalCounts['PackingGrade'];
                  echo "<br/>";
                }else{
                    echo "Error '".$row1['RmLotID']."'<br/>";
                }

            }

        }    

    //         $SqlCostingRates = "SELECT * FROM SalesCostingRates WHERE TrackID='".$TrackId."' 
    // and LowerLimit <= '$HLCount' and UpperLimit >= '$HLCount'";
    //         $StmtCostingRates = mysqli_query($this->db, $SqlCostingRates);
    //         $RowCostingRates = mysqli_fetch_array($StmtCostingRates, MYSQLI_ASSOC);
            // $HLCount = $RowFinalCounts['HLCount'];
            // $HlWeight = $RowFinalCounts['HLWeight'];

    // echo "<br/>";    
}
exit;



?>
