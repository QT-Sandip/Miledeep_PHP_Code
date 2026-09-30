<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('max_execution_time', '0'); // for infinite time of execution 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


/*
$RmArrivalDetatils="SELECT * FROM `RmArrivalDetatils` WHERE HarvestCommission !=''"; 
$stmt_RmArrivalDetatils=$iconn->query($RmArrivalDetatils);
while($row_stmt_RmArrivalDetatils=$stmt_RmArrivalDetatils->fetch_array()){

	$RmArrivalID=$row_stmt_RmArrivalDetatils['RmArrivalID'];
	$HarvestCommission=$row_stmt_RmArrivalDetatils['HarvestCommission'];
	$ArrivalDate=$row_stmt_RmArrivalDetatils['ArrivalDate'];
	$ArrivalStationID=$row_stmt_RmArrivalDetatils['ArrivalStationID'];
	$ProcessingStationID=$row_stmt_RmArrivalDetatils['ProcessingStationID'];
	
$sql_count="SELECT sum(Weight) as Weight FROM `RmArrivalBreakUpDetails`   WHERE ArrivalID in ($RmArrivalID);"; 
$stmt_count=$iconn->query($sql_count);
while($row_count=$stmt_count->fetch_array()){

	$Weight=$row_count['Weight'];
	$Amount=$row_count['Weight'] * $HarvestCommission;
	//$Variety=$row_count['Variety']; 
 
	echo $SqlOverAll="update  `RmArrivalDetatils` set Extra='$Amount' where RmArrivalID=$RmArrivalID";


	$stmt_SQLIns=$iconn->query($SqlOverAll);

}
	
	
}

exit;

*/


$RmArrivalDetatils="SELECT sum(Extra) as Extra,ArrivalStationID,ProcessingStationID,ArrivalDate,GROUP_CONCAT(RmArrivalID) as RmArrivalID 
FROM `RmArrivalDetatils`  WHERE ArrivalDate between '2023-04-01' and '2024-03-31'
 GROUP by ArrivalDate,ArrivalStationID,ProcessingStationID,Species;"; 
$stmt_RmArrivalDetatils=$iconn->query($RmArrivalDetatils);
while($row_stmt_RmArrivalDetatils=$stmt_RmArrivalDetatils->fetch_array()){

	$Extra=$row_stmt_RmArrivalDetatils['Extra'];
	$RmArrivalID=$row_stmt_RmArrivalDetatils['RmArrivalID'];
	$ArrivalDate=$row_stmt_RmArrivalDetatils['ArrivalDate'];
	$ArrivalStationID=$row_stmt_RmArrivalDetatils['ArrivalStationID'];
	$ProcessingStationID=$row_stmt_RmArrivalDetatils['ProcessingStationID'];
	
$sql_count="SELECT sum(Weight) as Weight,sum(Weight*Rate) as Amount,Variety FROM `RmArrivalBreakUpDetails` WHERE ArrivalID in ($RmArrivalID);"; 
$stmt_count=$iconn->query($sql_count);
while($row_count=$stmt_count->fetch_array()){

	$Weight=$row_count['Weight'];
	$Amount=($row_count['Amount']+$Extra); 
	$Variety=$row_count['Variety']; 
 
	echo $SqlOverAll="INSERT INTO `RmOverAllArrival`( `ArrivalDate`, `CompanyID`, `PlantID`, `CenterID`, `Species`, `TotalWeight`, `TotalAmount`, `IsEdited`, `PostedID`) 
				VALUES ('$ArrivalDate','1','$ProcessingStationID','$ArrivalStationID','$Variety','$Weight','$Amount','0','0')";


	//$stmt_SQLIns=$iconn->query($SqlOverAll);

}
	
	
}

exit;

/*
	 
	echo $RmArrivalDetatils="SELECT *  FROM `RmArrivalDetatils` where RmArrivalID > 25000  order by RmArrivalID asc limit 4000";  
	$stmt_RmArrivalDetatils=$iconn->query($RmArrivalDetatils);
	while($row_stmt_RmArrivalDetatils=$stmt_RmArrivalDetatils->fetch_array()){

		echo $RmArrivalID=$row_stmt_RmArrivalDetatils['RmArrivalID'];
		$ArrivalDate=$row_stmt_RmArrivalDetatils['ArrivalDate'];
		$ArrivalNumber=$row_stmt_RmArrivalDetatils['ArrivalNumber'];
		
	$sql_count="SELECT Variety,SUM(FinalQunantity) as FinalQunantity,PurchaseRate,PurchaseCount FROM `RmDataDump`  WHERE BillNo ='$ArrivalNumber' AND PurchaseDate='$ArrivalDate' 
	 GROUP BY PurchaseRate,PurchaseCount,Variety;"; 
	$stmt_count=$iconn->query($sql_count);
	while($row_count=$stmt_count->fetch_array()){

		$Variety=$row_count['Variety'];
		$FinalQunantity=$row_count['FinalQunantity']; 
		$PurchaseRate=$row_count['PurchaseRate'];
		$PurchaseCount=$row_count['PurchaseCount'];
		
		echo $SQLIns="INSERT INTO `RmArrivalBreakUpDetails`( `ArrivalID`, `Variety`, `Count`, `Weight`, `Rate`, `BrkUpStatus`) 
		VALUES ('$RmArrivalID','$Variety','$PurchaseCount','$FinalQunantity','$PurchaseRate','1')";

		$stmt_SQLIns=$iconn->query($SQLIns);

	}
		
		
	}
	

	exit;
	
	
	
		 	$SqlFinalGrading="SELECT * FROM `PpcValueAddition` WHERE VLID > 528";
			$StmtFinalGrading=$iconn->query($SqlFinalGrading);
			//$StmtFinalGrading=$iconn->query($SqlFinalGrading);
				
			
				
			
			while($RowFinalCounts=$StmtFinalGrading->fetch_array()){

			$ProcessingDate=date("Y-m-d", strtotime($RowFinalCounts['ProcessingDate']));				
			$VLID=$RowFinalCounts['VLID'];
			
		echo	$GradesUsdQuery="UPDATE PpcValueAddition set ProcessingDate='$ProcessingDate' where VLID='$VLID'";
			$GradesUsdExec=$iconn->query($GradesUsdQuery);
			
			}
			
			/*
		 	$SqlFinalGrading="SELECT * FROM `PpcHLGradingBreakup` WHERE PackingGrade='' order by LotID DESC";
			$StmtFinalGrading=$iconn->query($SqlFinalGrading);
			//$StmtFinalGrading=$iconn->query($SqlFinalGrading);
				
			
				
			
			while($RowFinalCounts=$StmtFinalGrading->fetch_array()){
				
				$HLCount	=$RowFinalCounts['HLCount'];			
			$HlWeight	=$RowFinalCounts['HlWeight'];							
			$LotID	=$RowFinalCounts['LotID'];	
				
				 $SqlTrackId="SELECT * FROM `SalesCostingRatesHistory` 
				where FromDate <= (select PurchaseDate from RmLotDetatils where RmLotID='$LotID')
				order by FromDate DESC, ID DESC  LIMIT 1";
		 
			$StmtTrackId=$iconn->query($SqlTrackId);
			$RowTrackId=$StmtTrackId->fetch_array();
			$TrackID=$RowTrackId['ID'];
				
									
				 
			$SqlCostingRates="SELECT * FROM SalesCostingRates WHERE TrackID='$TrackID' 
			and LowerLimit < '$HLCount' and UpperLimit >= '$HLCount'";
			$StmtCostingRates=$iconn->query($SqlCostingRates);
			$RowCostingRates =$StmtCostingRates->fetch_array();
			
			$Usd=$RowCostingRates['USD'];	
			$Grade=$RowCostingRates['Grade'];
			
		echo	$GradesUsdQuery="UPDATE PpcHLGradingBreakup set PackingGrade='$Grade',Usd='$Usd' 
			where LotID='$LotID' and HLCount='$HLCount'";
			$GradesUsdExec=$iconn->query($GradesUsdQuery);
			
			}
	
	
?>