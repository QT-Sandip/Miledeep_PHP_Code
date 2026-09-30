<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include($cp."Includes/IncludePage.php");

	if(isset($_POST['ToDate'])){
		
			$FromDate=$_POST['FromDate'];
			$ToDate=$_POST['ToDate'].' 23:59:59';
			
			$query='';
			
			if($_POST['Station']!='BOTH'){
			$Station=$_POST['Station'];
			$query.=" and UnitID='$Station'";	
			}
			if($_POST['PackData']!='BOTH'){
			$PackData=$_POST['PackData'];
			$query.=" and PackingTypeID='$PackData'";	
			}
			if($_POST['Variety']!='BOTH'){
				 $Variety=$_POST['Variety'];
			$query.=" and AGO.VarietyID='$Variety'";	
			}
	
	if($_POST['PackData']=='BOTH'){
	  $sql="
	  SELECT ShortName,CPID.Grade,PackingTypeID, 
	  sum(AvalMcs) AS TotalMcs,
	  round(sum(AvalMcs*TotalSlabs*SlabWeight*MFactor),2) as KGS,
	  PoNo,Usd,SUM(LooseBags)AS LooseBags,
	  round(sum(LooseBags*SlabWeight*MFactor*2.204*Usd),2) as LooseBagsPrice,
	  round(sum(LooseBags*SlabWeight*MFactor),2) as LooseBagsKgs,
	sum(AvalMcs) AS TotalMcs,
	round(sum(AvalMcs*TotalSlabs*SlabWeight*MFactor*2.204*Usd),2) as TotalUSD,
	(SELECT Price from CsSalePrices WHERE Grade=Grade AND Variety=AGO.VarietyID ) as Price	
	from ScrmPIVarietyList as AGO 
	left join CsStockData as CPID on CPID.VarietyID=AGO.VarietyID
	left join AdminPackingStyles as CPS on CPID.PackingStyleID=CPS.PCKID 
	LEFT JOIN ScrmPoData as po on po.PoID=CPID.PoID 
	left join ScrmShipmentDetailsBreif as SDB on po.PoID=SDB.PiID
	LEFT JOIN CsPoGradeData as pgd on pgd.PoGradeID=CPID.PoGradeId
    where AGO.VarietyID !='' and `ProductionTypeID`='PRODUCTION'
    and CPID.ProductionDate between '$FromDate' and '$ToDate' $query  
	and ProductionStartedDate!=''and (TransportedDate='' or TransportedDate is null)
    group by AGO.VarietyID,CPID.Grade ORDER BY AGO.VarietyID,ABS(CPID.Grade) ASC";	
	 }else if($_POST['PackData']=='MASTER'){
	   $sql="SELECT AvalCPID.Grade,ShortName,
COALESCE(sum(AvalMcs*TotalSlabs*SlabWeight*MFactor),AvalMcs) as KGS,
COALESCE(sum(AvalMcs*TotalSlabs*SlabWeight*Usd),0) as usd 
from CsStockData as AvalCPID  
left join ScrmPIVarietyList as AGO on AvalCPID.VarietyID=AGO.VarietyID
left join AdminPackingStyles as AvalCps on AvalCPID.PackingStyleID=AvalCps.PCKID
left join CsPoGradeData as PGD on AvalCPID.PoGradeId=PGD.PoGradeID
left join CsBarCodes as AvalCbd on AvalCbd.BarCodeID=AvalCPID.BarCodeID 
WHERE  (AvalCbd.BarCodeStatus =1 or AvalCbd.BarCodeStatus=2) and
 AvalCPID.ProductionDate between '$FromDate' and '$ToDate' and AvalCPID.PoGradeId !=0 $query 
GROUP by AGO.VarietyID,AvalCPID.Grade  order by AGO.VarietyID,abs(AvalCPID.Grade)";		
	 }
	 
	else{   
	
	 $sql="SELECT ShortName,AvalCPID.Grade,PackingTypeID ,
COALESCE(sum(AvalMcs*TotalSlabs*SlabWeight*MFactor),0)  as KGS,
COALESCE(sum(LooseBags*SlabWeight*MFactor),0)  as LooseBagsKgs,
(SELECT Price from CsSalePrices WHERE Grade=Grade AND Variety=AGO.VarietyID ) as Price 
 FROM CsStockData as AvalCPID 
 left join CsBarCodes as AvalCbd on AvalCbd.BarCodeID=AvalCPID.BarCodeID
 left join ScrmPIVarietyList as AGO on AvalCPID.VarietyID=AGO.VarietyID
 left join AdminPackingStyles as AvalCps on AvalCPID.PackingStyleID=AvalCps.PCKID
 where AvalCPID.PoGradeId=0 and AvalCPID.ProductionDate between '$FromDate' and '$ToDate'
and  (AvalCbd.BarCodeStatus =1 or AvalCbd.BarCodeStatus=2) $query GROUP by AGO.VarietyID,AvalCPID.Grade  order by AGO.VarietyID,abs(AvalCPID.Grade)";	
	
	
	}	
	   
	  
	  
	 //echo $sql; exit;
	  
	  
	$StmtData=$iconn->query($sql);
	
	//'$FromDate' and '$ToDate' $query 
	 $sql;	 
	
	
	
	 $data ='<table id="key-datatable"  data-paging="false"
										class="table dt-responsive nowrap w-100">
		
		
		<thead>
		<tr>
		<th>Variety</th>
		<th>Grade</th>
		<th>Final Packed Qty(Kgs)</th>
		<th>USD</th>		
		<th>Total USD</th>		
		<th>Total INR </th>		
		</tr></thead><tbody>
		
		
		';
		$count=1;
	 while($RowData=$StmtData->fetch_array()){
		 
		 $QtyInKgs=$RowData['KGS']+$RowData['LooseBagsKgs'];
		 
		 if($PackData=='MASTER'){	
		 //$UsdPerLb='-';
         $UsdQty=round($RowData['usd'],2);
		 
		 $UsdPerLb=ROUND(($UsdQty/($QtyInKgs*2.2)),2);	
		 		 
		 }else if($PackData=='DUMMY'){
		 $UsdQty=round(($QtyInKgs*$RowData['Price']*2.2),2);
		 $UsdPerLb=$RowData['Price'];
		 }
		 
		 $InrQty=round($UsdQty*$Exchange);

		 $Variety=$RowData['ShortName'];
		 $Grade=$RowData['Grade'];
		
		 $AvalTotalKgs+=$QtyInKgs;
		 $AvalTotalLKgs+=$row_csrd['LooseBagsKgs'];	
		 $UsdQtyTotal+=$UsdQty;	
		 $InrQtyTotal+=$InrQty;	
		
		
		
		
		
		$data .='
		<tr>
		<td >'.$Variety.'</td>				
		<td >'.$Grade.'</td>	
		<td >'.$QtyInKgs.'</td>
		<td >$'.$UsdPerLb.'</td>
		<td > $'.$UsdQty.'</td>
		<td >'.$InrQty.'</td>
		';
		
		$data .='
		
		</tr>';
	$count++;				  
}

	$data .='
	<tr style="font-weight: bold">
		<td >Total </td>
		<td ></td>
		<td >'.$AvalTotalKgs.'</td>
		<td ></td>
		<td >'.$UsdQtyTotal.'</td>
		<td >'.$InrQtyTotal.'</td></tr>
		';
					
		echo $data .='</tbody></table>';
		
		
		
		
		
	//	echo json_encode($OUT);
	}
	
?>