
<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");
   $Header='ORDER`S LIST REPORT';



 $Year=$_GET['Year']; 
 $Exporter=$_GET['Exporter']; 
 
 
 $DateFilter=getFinancialYear($Year);
 
$Query='';
if($Exporter!=''){
$Query.="and ExporterID in ($Exporter)";
}



$file_name='Orders List -'.$from_date.'-'.$to_date.'.xlsx';
$date_end=$to_date.' 23:59:59';  
		
$sql="SELECT GROUP_CONCAT(PoID) as PoID  FROM ScrmPoData as SPD 
					left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
					left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
					left join ScrmShipmentStatusCodes as SSC on SPD.Status=SSC.Code
					left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
					WHERE AvailableDate!='' and (SDB.TransportedDate ='' or SDB.TransportedDate is null) 
					$Query order by PoID desc";

			$stmt=$iconn->query($sql);					
			$RowData=$stmt-> fetch_array();
             $PoID=$RowData['PoID'];
			 $AryPoID=explode(',',$PoID);
			 $OrderCount= Count($AryPoID); 

		
	
	
		 $SqlGroup="SELECT Category,ShortName,GROUP_CONCAT(VarietyID) as VarietyID 
		FROM `ScrmPIVarietyList` GROUP BY Category order by Category";
		$StmtGroup=$iconn->query($SqlGroup);
		
		
		while($RowData=$StmtGroup-> fetch_array()){
		
		$AryData= getTableDate($RowData['VarietyID'],$PoID);	
		
		if($AryData['RowCount']>0){
			//echo '<h3>'.$RowData['Category']. '-'. $RowData['ShortName']  .'</h3>';
			
			echo $AryData['TableData'];
		}
		}
	

	
	function getTableDate($VarietyID,$PoID){
	global $iconn,$Query;
	 $sql="SELECT *,PoID,PoNo,PODate,PreparedBy,Name,ShipByDate,
sum(SPGD.Mcs) as Mcs,GROUP_CONCAT(SPGD.Grade) as Grade FROM ScrmPoData as SPD
LEFT JOIN ScrmPoGradeData as SPGD on SPGD.InvId=SPD.PoID 
LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
LEFT JOIN ScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge 
WHERE   SPGD.VarietyID in ($VarietyID)
and SPD.PoID  in($PoID) 

GROUP by PoID order by PoID Asc"; 


$stmt=$iconn->query($sql);


$data='<table  id="key-datatable" data-paging="false" 
class="table  wrap w-150" >										
		
<thead class="thead-light">
				   <th  align="center"   >S No.</th>              
				   <th  align="center"  >REPORT ID</th>
				   <th  align="center"  >ORDER NO</th>
				   <th  align="center"  ><B> BUYER NAME</B></th>					   
				  <!-- <th  align="center"  ><B> BUYER AGENT</B></th>					   
				   <th  align="center"  ><B> PLANT</B></th>-->					   
				   <th  align="center"  ><B>BRAND</B></th>	
				   <th  align="center"  ><B>VARIETY</B></th>
                   <th  align="center"  ><B>MASTER CARTONS</B></th>
				   <th  align="center"  ><B>PACK</B></th>			
				   <th  align="center"  ><B>GRADE</B></th>						   
				   <!--<th  align="center"  ><B>PACKAGING READY</B></th>				   
				   <th  align="center"  ><B>CODE ISSUE</B></th>			
				   <th  align="center"  ><B>LAB SAMPLE</B></th>	 		
				   <th  align="center"  ><B>QTY</B></th>			
				   <th  align="center"  ><B>MAJOR GRADE</B></th>	-->			
				   <th  align="center"  ><B>SHIPMENT DATE AS PER PO</B></th>			
				   <th  align="center"  ><B>DESTINATION</B></th>	
		
    </thead>
	<tbody>';
				
$Sno=0;
				
  while($row= $stmt->fetch_array()){
	
			 


$LastUpdatedDate = date('d-m-Y',strtotime($row['LastUpdatedDate']));




if($row['AvalMcs']==''){
	$AvalMcs='0';
} 
else{
	$AvalMcs=$row['AvalMcs'];
}


$ConsigneeName=mb_strimwidth($row['ConsigneeName'], 0, 20, "...");
$PoNo=mb_strimwidth($row['PoNo'], 0, 15, "...");

$ShipByDate=getFormatedDate($row['ShipByDate']);		
$PoDate=getFormatedDate($row['PoDate']);		
$ConsigneeName=$row['ConsigneeName'];		


$PoID=$row['PoID'];
$BuyerAgent=$row['PoID'];
$Plant=$row['PoID'];
$Brand=$row['Brand'];
$Grade=$row['Grade'];
 $PreparedBy=$row['PreparedBy'];
$Destination=$row['Name'];
$ShipByDate=$row['ShipByDate'];
$Rcount=0;		
$AryPoID[]=$PoID;	
	$data.='<tr> 
	<td> '. ++$Sno.'</td>		
	<td> '. $PoID.'</td> 
	<td> '. $PoNo.'-('.$PoDate.')</td> 
	<td> '. $ConsigneeName.'</td>
	<!--<td> '. $BuyerAgent.'</td>
	<td> '. $Plant.'</td>-->
	<td> '. $Brand.'</td>';
				   
				   
				/* $SqlVariety="SELECT sum(Mcs) as Mcs,ShortName,
				 GROUP_CONCAT(PackingStyle) as PackingStyle,
				GROUP_CONCAT(Grade) as Grade				 FROM `ScrmPoGradeData` SPDT lEFT join AdminPackingStyles APS on SPDT.PackingStyleID=APS.PCKID LEFT join ScrmPackingData SPD on SPDT.ID=SPD.POGradeId left Join ScrmPIVarietyList as SVL on 
				SVL.VarietyID=SPDT.VarietyID WHERE InvId='$PoID' 
				and SPDT.VarietyID in ($VarietyID) GROUP By SPDT.VarietyID";  */
				
				$SqlVariety="SELECT sum(Mcs) as Mcs,ShortName,
				 GROUP_CONCAT(APS.PackingStyle) as PackingStyle,
				GROUP_CONCAT(SPDT.Grade,'-',SPDT.Mcs) as Grade	FROM `ScrmPoGradeData` SPDT lEFT join AdminPackingStyles APS on SPDT.PackingStyleID=APS.PCKID LEFT join ScrmPackingData SPD on SPDT.ID=SPD.POGradeId left Join ScrmPIVarietyList as SVL on 
				SVL.VarietyID=SPDT.VarietyID WHERE InvId='$PoID' 
				and SPDT.VarietyID in ($VarietyID) GROUP By SPDT.VarietyID";  
			$Stmtvariety=$iconn->query($SqlVariety);
			$RowCount=mysqli_num_rows($Stmtvariety);
			if($RowCount==0){
				
	$data.='<td ></td>
	
	<td ></td>
	<td ></td>
	<td ></td>
				
	</tr>';
			}
				
			while($RowVariety=$Stmtvariety->fetch_array()){
			
			if($Rcount==0){
				
			}else{
				$data .='
				 
				 <td ></td>
				 <td ></td>
				 <td ></td>
				 <td ></td>
				 <td ></td>'; 
			}
			
			
			
		   

				  $data .='
				 <td>'.$RowVariety['ShortName'].'</td>
				 <td>'.$RowVariety['Mcs'].'</td>
				 <td>'.$RowVariety['PackingStyle'].'</td>
				 <td>'.$RowVariety['Grade'].''.$RowVariety['GradeMcs'].'</td>
				 <td>'.getFormatedDate($ShipByDate).'</td>
				';
				 
				 if($Rcount==0){
				$data .='
				 <td >'.$Destination.'</td>'; 
			}else{
				$data .='
				 <td ></td>
				 <td ></td>'; 
			}
			
				$Rcount++;		 
        $data .='  </tr>';
				  
				 
			}	
	 
}
  $data .='</tbody></table>';
$AryData['TableData']=$data;
$AryData['RowCount']=$Rcount;
	

	 return $AryData;
	
	 
	}



?>