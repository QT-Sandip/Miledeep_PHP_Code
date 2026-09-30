<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include($cp."Includes/IncludePage.php");
$ColdstoreID=$_GET['ColdstoreID']; 
 $TenantID=$_GET['TenantID']; 


	if(isset($_GET['ToDate'])){
		
			$FromDate=$_GET['FromDate'] ;
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
			if($_GET['Group']!='BOTH'){
				 $Group=$_GET['Group'];
			$query.=" and ProductionTypeID='$Group'";	
			}
			if($ColdstoreID!='BOTH'){
			//$Station=$_GET['Station'];
			$query.=" and CSD.PlantID ='$ColdstoreID'";	
			}
			
			if($TenantID!='BOTH'){
         $query .=" and CSD.StockHolderID='$TenantID'";
         }
			
			 $SqlStockIds="SELECT COALESCE(GROUP_CONCAT(CSOD.StockID),0) as OutStockIds,
					(SELECT COALESCE(GROUP_CONCAT(CSD.StockID),0) from CsStockData AS CSD WHERE CSD.ProductionDate
					$DateFilter and (AvalMcs !=0  or LooseBags !=0) $query) as AvalStockId
					from CsStockData AS CSD 
					left join CsStockOutDetails as CSOD on CSD.StockID=CSOD.StockID
					WHERE CSD.ProductionDate $DateFilter $query "; 
				$StmtStockIds=$iconn->query($SqlStockIds);	
				$RowStockIds=$StmtStockIds->fetch_array();
				$AvalStockId=$RowStockIds['AvalStockId'];
				$OutStockIds=$RowStockIds['OutStockIds'];
		   if($OutStockIds!=0){
				$TotalIds=$AvalStockId.','.$OutStockIds; 
				}
			
	if($AvalStockId!=0){
	 $SqlStock="SELECT *,CSD.FreezingType,HSD.ShiftName,CSD.Grade,CSP.PackingStyle,AGOU.StationName as Unit, (SELECT sum(AvalMcs*TotalSlabs*SlabWeight*MFactor) 
	from CsStockData as MCPID where MCPID.StockID=CSD.StockID) as KGS, 
	(SELECT sum(LooseBags*SlabWeight*MFactor) 
	from CsStockData as MCPID where MCPID.StockID=CSD.StockID) as LKGS 
	
	FROM
	`CsStockData` as CSD left join AdminPackingStyles as CSP on CSP.PCKID = CSD.PackingStyleID
	left join ScrmPoData as CSPOS on CSPOS.PoID = CSD.PoID 
	left join CsBarCodes as CBD on CSD.BarCodeID=CBD.BarCodeID
	left join ScrmPIVarietyList as AGO on CSD.VarietyID=AGO.VarietyID 
	left join RmProcessingStations as AGOU on CSD.UnitID=AGOU.ProcessingStationID 
	left join AdminShiftDetails as HSD on HSD.ShiftName=CSD.ShiftID 
	left join CsStoresRackData as CRD
	on CRD.BarCodeID=CBD.BarCodeID where CSD.StockID  in ($AvalStockId) ";	
	$StmtStock=$iconn->query($SqlStock);
	
	while($RowStock=$StmtStock->fetch_array()){
		$AryTotalStock[]=$RowStock;
	}
	
	}
	
	if($OutStockIds!=0){
	
		$SqlStock="SELECT *,AGOU.Name as Unit, 
(SELECT sum(OutMcs*TotalSlabs*SlabWeight*MFactor) from CsStockData as MCPID where MCPID.StockID=CSD.StockID) as KGS, 
(SELECT sum(OutLooseBags*SlabWeight*MFactor) from CsStockData as MCPID where MCPID.StockID=CSD.StockID) as LKGS 
FROM `CsStockData` as CSD 
left join `CsStockOutDetails` as CSOD on CSOD.StockID = CSD.StockID 
left join AdminPackingStyles as CSP on CSP.PCKID = CSD.PackingStyleID 
left join ScrmPoData as CSPOS on CSPOS.PoID = CSD.PoID left join CsBarCodes as CBD on CSD.BarCodeID=CBD.BarCodeID 
left join ScrmPIVarietyList as AGO on CSD.VarietyID=AGO.VarietyID 
left join AdminGeneralOptions as AGOU on CSD.UnitID=AGOU.Value 
left join CsStoresRackData as CRD on CRD.BarCodeID=CBD.BarCodeID 
left join HrmShiftDetails as HSD on HSD.ShiftID=CSD.ShiftID 
where  CSD.StockID in ($OutStockIds) ";	
	$StmtStock=$iconn->query($SqlStock);
	
	while($RowStock=$StmtStock->fetch_array()){
		$RowStock['BarCodeStatus']=3;
		$AryTotalStock[]=$RowStock;
	}
	
	}	
	
	
	$w1="8";
	
	 $data ='<tr>  

           		<th  align="LEFT"  width="100%">
				<B>From Date:'.getFormatedDate($FromDate).
				'<b style="color:white">hgbhnjnjhuy</b>
				To Date:'.getFormatedDate($ToDate).'</B></th>

               </tr>';
	 
	 $data ='	 <table id="example1"  border="1" cellspacing="0" cellpadding="5" >										
		
<thead class="thead-light">
		<tr>
		
		<th width="'.$w1.'%">Date</th>
		<th width="'.$w1.'%">Unit</th>
		<th width="'.$w1.'%">Po NO</th>
		<!--<th width="'.$w1.'%">Lot No</th> -->
		<th width="'.$w1.'%">Bar Code</th>
		<th width="'.$w1.'%">Variety</th>
		<th width="">Freezing</th>	
		<th width="'.$w1.'%">Group</th>
		<th width="">Grade</th>
		<th width="'.$w1.'%">Packing Style</th> 
		<th width="">Category</th>
		<th width="'.$w1.'%">Location</th>
		<th width="'.$w1.'%">Mcs</th>	
		<th width="'.$w1.'%" >Shift</th>
		<th width="'.$w1.'%">Remarks</th>	
		<th width="'.$w1.'%">Loose</th>	
		<th width="'.$w1.'%">Kgs</th>
		</tr></thead><tbody>
		
		
		';
		$C=0;
$WEIGHT_TOTAL=0;
$MCS_TOTAL=0;
$$LooseTotal=0;
	 foreach($AryTotalStock as $RowStock){
			$KGS=$RowStock['KGS']+$RowStock['LKGS'];
			$Remarks=$RowStock['Remarks'];

		$StockID=$RowStock['StockID'];
		$RackName=$RowStock['RackName'];
		$BlockID=$RowStock['BlockID'];
		$BarCodeStatus=$RowStock['BarCodeStatus'];
		$OnFloor=$RowStock['OnFloor'];
				$LKGS=$RowStock['LKGS'];		

				$LooseBags=$RowStock['LooseBags'];	
				$AvalMcs=$RowStock['AvalMcs'];	
				$LotNumber=$RowStock['PoNo'];	
				$LooseTotal +=$LooseBags;

		$BlockName='';
		$WEIGHT_TOTAL+=$KGS;
		$MCS_TOTAL+=$AvalMcs;
		
		if($BarCodeStatus==3){
		$BlockName='Stock Out';
		}else if($OnFloor==0){
		$BlockName='--';	
		} else{
		$BlockName.=' Store -'.$RackName;		
		}
		
		

				$data .='
				<tr>
			

			<td align="center">'.getFormatedDate($RowStock['ProductionDate']).'</td>
		<td align="center">'.$RowStock['Unit'].'</td>
		<td align="center">'.$RowStock['PoNo'].'</td>
		<!--<td align="center">'.$LotNumber.'</td> -->
		<td align="center">'.$RowStock['BarCodeValue'].'</td>
		<td align="center">'.$RowStock['ShortName'].'</td>
		<td align="center">'.$RowStock['FreezingType'].'</td>
		<td align="center">'.$RowStock['ProductionTypeID'].'</td>
		<td align="center">'.$RowStock['Grade'].'</td>
		
		<td align="center">'.$RowStock['PackingStyle'].'</td>
		
		<td align="center">'.$RowStock['ProductCategoryID'].'</td>
		<td align="center">'.$BlockName.'</td>
		<td align="center">'.$AvalMcs.'</td>
		<td align="center">'.$RowStock['ShiftName'].'</td>
		<td align="center">'.$Remarks.'</td>
		<td align="center">'.$LooseBags.'</td>
		<td align="center">'.round($KGS,2).'</td>
		
	
			</tr>';
$C++;					  
}
	$data .='	<tr> 
           		

                <th  align="center"width="" >Total </th>
                <th  align="center"width="13%" >'.$C.'Pallets</th>

<th  align="center" ></th>
<th  align="center" ></th>
<th  align="center" ></th>
<th  align="center" ></th>
<th  align="center" ></th>
<th  align="center" ></th>
				<th  align="center" ></th>
				<th  align="center" ></th>
				<th  align="center" ></th>
				
                
                
				<th  align="center"width="13%" >'.$MCS_TOTAL.'  Mcs</th>
				                <th  align="center"width="13%" ></th>
				<th  align="center" ></th>

				<th  align="center"width="13%" >'.$LooseTotal.'  Pouches</th>
				
				<th  align="center"width="13%" >'.round($WEIGHT_TOTAL,2).'  Kgs</th>


   
               </tr>';				
		echo $data .='</tbody></table>';
		
		
		
		
		
	//	echo json_encode($OUT);
	}
	
?>
