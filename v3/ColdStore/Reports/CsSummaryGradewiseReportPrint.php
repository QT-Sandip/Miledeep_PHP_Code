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
			$query.=" and CSD.FreezingType='$FreezingType'";	
			}
			if($TenantID!='BOTH'){
           $query .=" and CSD.StockHolderID='$TenantID'";
           }
			
			
		/*$SqlStockIds="SELECT COALESCE(GROUP_CONCAT(CSOD.StockID),0) as ProdStockIds,
					
                    (SELECT COALESCE(GROUP_CONCAT(CSD.StockID),0) from CsStockData AS CSD 
					WHERE CSD.ProductionDate $DateFilter  and (AvalMcs !=0  or LooseBags !=0)  $query ) as AvalStockId,
                   
                   (SELECT COALESCE(GROUP_CONCAT(CSD.StockID),0) from CsStockOutDetails AS CSD 
				   WHERE CSD.PostedDate $DateFilter  $query  ) as OutStockIds
					from CsStockData AS CSD 
					left join CsStockOutDetails as CSOD on CSD.StockID=CSOD.StockID
					WHERE CSD.ProductionDate $DateFilter and CSD.UnitID ='$ColdstoreID' $query ";  */
			$SqlStockIds="SELECT COALESCE(GROUP_CONCAT(CSOD.StockID),0) as ProdStockIds,
					
                    (SELECT COALESCE(GROUP_CONCAT(CSD.StockID),0) from CsStockData AS CSD 
					WHERE CSD.ProductionDate $DateFilter  and (AvalMcs !=0  or LooseBags !=0)  $query ) as AvalStockId,
                   
                   (SELECT COALESCE(GROUP_CONCAT(CSD.StockID),0) from CsStockOutDetails AS CSD 
				   WHERE CSD.PostedDate $DateFilter   ) as OutStockIds
					from CsStockData AS CSD 
					left join CsStockOutDetails as CSOD on CSD.StockID=CSOD.StockID
					WHERE CSD.ProductionDate $DateFilter and CSD.UnitID ='$ColdstoreID' $query ";  
					
				$StmtStockIds=$iconn->query($SqlStockIds);	
				$RowStockIds=$StmtStockIds->fetch_array();
				$ProdStockIds=$RowStockIds['ProdStockIds'];
				$AvalStockId=$RowStockIds['AvalStockId'];
				$OutStockIds=$RowStockIds['OutStockIds'];
				
				$TotalIds=$ProdStockIds.','.$AvalStockId.','.$OutStockIds;
				
		$SqlVariety="SELECT CSD.Grade from CsStockData as CSD WHERE CSD.StockID in ($TotalIds)
group by CSD.Grade ORDER BY Abs(CSD.Grade) ASC
";
		$StmtVariety=$iconn->query($SqlVariety);	
								 
		
	//'$FromDate' and '$ToDate' $query 
	//echo $SqlSummary;	 exit;
	
	
	
	 $data ='<table id="example2"  border="1" cellspacing="0" cellpadding="5" > 
		
		<thead class="thead-light">
		<tr>
		
		<th width="20%">Grade</th>
		<th width="20%"> InWard(Kgs)</th>
		<th width="20%">OutWard(Kgs)</th>
		<th width="20%">Present Stock(Kgs)</th>
		
		</tr></thead><tbody>
		
		
		';
	 while($RowVariety=$StmtVariety->fetch_array()){

		 
		 $Grade=$RowVariety['Grade'];
		
		 
		 $SqlOsData= "SELECT CSD.VarietyID,PackingStyle,ShortName,
		  COALESCE(sum(AvalMcs*TotalSlabs*SlabWeight*MFactor) ,0) as OsKgs,
		  COALESCE(sum(LooseBags*SlabWeight*MFactor) ,0) as OsLkgs
		  from CsStockData as CSD 
		  left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
          left join ScrmPIVarietyList as VL on VL.VarietyID=CSD.VarietyID
          WHERE CSD.Grade='$Grade'  and 
		  StockID in ($AvalStockId) 
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
							WHERE CSD.Grade='$Grade' and   CSD.StockID in ($ProdStockIds) 
							
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
							WHERE CSD.Grade='$Grade' and  CSD.StockID in ($OutStockIds) 
							
							ORDER BY VarietyID,Abs(PackingStyle) ASC";
							

			$StmtOutData=$iconn->query($SqlOutData);	
			$RowOutData=$StmtOutData->fetch_array();
			
			$OutStanding=$RowOsData['OsKgs']+$RowOsData['OsLkgs'];
			$Production=$RowProdOutData['ProdOutKgs']+$RowProdOutData['ProdOutLkgs'];
			$Issues=$RowOutData['OutKgs']+$RowOutData['OutLkgs'];
			

		  
		  
		
		 
		
	
			
				$data .='
				<tr>
		<td width="20%" align="center">'.$Grade.'</td>
		<td width="20%" align="center">'.round(($OutStanding+$Production),2).'</td>
		<td width="20%" align="center">'.$Issues.'</td>
		<td width="20%" align="center">'.$OutStanding.'</td>
		
	
			</tr>';
					  
}
					
		echo $data .='</tbody></table>';
		
		
		
		
		
	//	echo json_encode($OUT);
	}
	
?>