<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include($cp."Includes/IncludePage.php");
$ColdstoreID=$_GET['ColdstoreID']; 
 $TenantID=$_GET['TenantID'];
	if(isset($_GET['ToDate'])){
		
			$FromDate=$_GET['FromDate'];
			$ToDate=$_GET['ToDate'].' 23:59:59';
			$DateFilter="between '$FromDate' and '$ToDate'";
			$query='';
			
			/*if($_GET['Station']!='BOTH'){
			$Station=$_GET['Station'];
			$query.=" and UnitID='$Station'";	
			}*/
			if($_GET['PackData']!='BOTH'){
			$PackData=$_GET['PackData'];
			$query.=" and PackingTypeID='$PackData'";	
			}
			if($_GET['FreezingType']!='BOTH'){
				 $FreezingType=$_GET['FreezingType'];
			$query.=" and CPID.FreezingType='$FreezingType'";	
			}
			if($TenantID!='BOTH'){
         $query .=" and CSD.StockHolderID='$TenantID'";
         }
			
			
		$SqlStockIds="SELECT COALESCE(GROUP_CONCAT(CSOD.StockID),0) as ProdStockIds,
					
                    (SELECT COALESCE(GROUP_CONCAT(CSD.StockID),0) from CsStockData AS CSD 
					WHERE CSD.ProductionDate $DateFilter  and (AvalMcs !=0  or LooseBags !=0) $query and CSD.UnitID ='$ColdstoreID') as AvalStockId,
                   
                   (SELECT COALESCE(GROUP_CONCAT(CSD.StockID),0) from CsStockOutDetails AS CSD 
				   WHERE CSD.PostedDate $DateFilter $query  and CSD.UnitID ='$ColdstoreID') as OutStockIds
					from CsStockData AS CSD 
					left join CsStockOutDetails as CSOD on CSD.StockID=CSOD.StockID
					WHERE CSD.ProductionDate $DateFilter and CSD.UnitID ='$ColdstoreID' $query ";  
					
				$StmtStockIds=$iconn->query($SqlStockIds);	
				$RowStockIds=$StmtStockIds->fetch_array();
				$ProdStockIds=$RowStockIds['ProdStockIds'];
				$AvalStockId=$RowStockIds['AvalStockId'];
				$OutStockIds=$RowStockIds['OutStockIds'];
				
				$TotalIds=$ProdStockIds.','.$AvalStockId.','.$OutStockIds;
				
		$SqlVariety="SELECT CSD.VarietyID,PackingStyle,PackingStyleID,ShortName
					 from CsStockData as CSD 
					left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
					left join ScrmPIVarietyList as VL on VL.VarietyID=CSD.VarietyID
					WHERE  CSD.StockID in ($TotalIds)  group by CSD.VarietyID,PackingStyleID 
					ORDER BY VarietyID,Abs(PackingStyle) ASC;";
		$StmtVariety=$iconn->query($SqlVariety);	
								 
		
	//'$FromDate' and '$ToDate' $query 
	//echo $SqlSummary;	 exit;
	
	
	
	 $data ='<table id="example1" border="1" cellspacing="0" cellpadding="5">
		
		<thead class="thead-light">
		<tr>
		
		<th width="'.$w1.'%">Variety</th>
		<th width="'.$w1.'%">InWard(Kgs)</th>
		<th width="'.$w1.'%">OutWard(Kgs)</th>
		<th width="'.$w1.'%">Present Stock(Kgs)</th>
		
		</tr></thead><tbody>
		
		
		';
		$C=0;
		$TotalInward=0;
		$TotalOutWard=0;
		$TotaPresentStock=0;
		
	 while($RowVariety=$StmtVariety->fetch_array()){

		 
		 $PackingStyle=$RowVariety['PackingStyle'];
		 $ShortName=$RowVariety['ShortName'];
		 $VarietyID=$RowVariety['VarietyID'];
		 $PackingStyleID=$RowVariety['PackingStyleID'];
		 
		 $SqlOsData= "SELECT CSD.VarietyID,PackingStyle,ShortName,
		  COALESCE(sum(AvalMcs*TotalSlabs*SlabWeight*MFactor) ,0) as OsKgs,
		  COALESCE(sum(LooseBags*SlabWeight*MFactor) ,0) as OsLkgs
		  from CsStockData as CSD 
		  left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
          left join ScrmPIVarietyList as VL on VL.VarietyID=CSD.VarietyID
          WHERE CSD.VarietyID='$VarietyID' and CSD.PackingStyleID='$PackingStyleID' and 
		  StockID in ($AvalStockId) group by CSD.VarietyID,PackingStyleID 
          ORDER BY VarietyID,Abs(PackingStyle) ASC";
		  $StmtOsData=$iconn->query($SqlOsData);	
		  $RowOsData=$StmtOsData->fetch_array();
		  
		  $SqlProdOutData= "SELECT CSD.VarietyID,PackingStyle,ShortName,
							COALESCE(sum(OutMcs*TotalSlabs*SlabWeight*MFactor) ,0) as ProdOutKgs,
							COALESCE(sum(OutLooseBags*SlabWeight*MFactor) ,0) as ProdOutLkgs
							from CsStockData as CSD 
							left join CsStockOutDetails as CSOD on CSOD.StockID=CSD.StockID
							left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
							left join ScrmPIVarietyList as VL on VL.VarietyID=CSD.VarietyID
							WHERE CSD.VarietyID='$VarietyID' and CSD.PackingStyleID='$PackingStyleID' and   CSD.StockID in ($ProdStockIds) group by CSD.VarietyID,PackingStyleID 
							ORDER BY VarietyID,Abs(PackingStyle) ASC";
							

			$StmtProdOutData=$iconn->query($SqlProdOutData);	
			$RowProdOutData=$StmtProdOutData->fetch_array();


		  $SqlOutData= "SELECT CSD.VarietyID,PackingStyle,ShortName,
							COALESCE(sum(OutMcs*TotalSlabs*SlabWeight*MFactor) ,0) as OutKgs,
							COALESCE(sum(OutLooseBags*SlabWeight*MFactor) ,0) as OutLkgs
							from CsStockData as CSD 
							left join CsStockOutDetails as CSOD on CSOD.StockID=CSD.StockID
							left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
							left join ScrmPIVarietyList as VL on VL.VarietyID=CSD.VarietyID
							WHERE CSD.VarietyID='$VarietyID' and CSD.PackingStyleID='$PackingStyleID' and  CSD.StockID in ($OutStockIds) group by CSD.VarietyID,PackingStyleID 
							ORDER BY VarietyID,Abs(PackingStyle) ASC";
							

			$StmtOutData=$iconn->query($SqlOutData);	
			$RowOutData=$StmtOutData->fetch_array();
			
			$OutStanding=$RowOsData['OsKgs']+$RowOsData['OsLkgs'];
			$Production=$RowProdOutData['ProdOutKgs']+$RowProdOutData['ProdOutLkgs'];
			$Issues=$RowOutData['OutKgs']+$RowOutData['OutLkgs'];
			

		  
		  
		$TotalInward  += ($OutStanding+$Production);
		$TotalOutWard +=$Issues;
		$TotaPresentStock +=$OutStanding;
		 
		
	
			
				$data .='
				<tr>
		<td align="center"width="13%">'.$ShortName.' - '. $PackingStyle.'</td>
		<td align="center"width="13%">'.round(($OutStanding+$Production),2).'</td>
		<td align="center"width="13%">'.$Issues.'</td>
		<td align="center"width="13%">'.$OutStanding.'</td>
		
	
			</tr>';
	$C++;				  
}
	$data .= '


		<tr> 
           		
                <th  align="left"width="" >Total <b style="color:white;">sdhfvhddh</b>'.$C.'</font></th>



				<th  align="center"width="13%" >'.$TotalInward.'  Kgs</th>
				<th  align="center"width="13%" >'.$TotalOutWard.'  Kgs</th>
				<th  align="center"width="13%" >'.$TotaPresentStock.'  Kgs</th>


   
               </tr>';
				
		echo $data .='</tbody></table>';
		
		
		
		
		
	//	echo json_encode($OUT);
	}
	
?>