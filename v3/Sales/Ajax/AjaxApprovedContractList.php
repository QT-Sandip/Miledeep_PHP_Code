
<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
   $Header='ORDER`S LIST REPORT';



 $Year=$_POST['Year']; 
 $Exporter=$_POST['Exporter']; 
 $Consignee=$_POST['Consignee']; 
 $Country=$_POST['Country']; 
 
 
 $DateFilter=getFinancialYear($Year);
 
$Query='';
if($Exporter!=''){
$Query.=" and ExporterID in ($Exporter)";
}


 if($Consignee!=""){
	$Query.=' and SPD.ConsigneeID='.$Consignee;
 }
 if($Country!=""){
	$Query.='  and SPD.DestinationCountry='.$Country;
 }
 
 



$file_name='Orders List -'.$from_date.'-'.$to_date.'.xlsx';
$date_end=$to_date.' 23:59:59';  
		
  $sql="SELECT GROUP_CONCAT(PoID) as PoID  FROM ScrmPIData as SPD 
					left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
					left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
					left join ScrmShipmentStatusCodes as SSC on SPD.Status=SSC.Code
					left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
					WHERE  (SDB.TransportedDate ='' or SDB.TransportedDate is null) and SPD.Status=1
					$Query order by PoID desc";
					
				//	ECHO $sql; EXIT;

			$stmt=$iconn->query($sql);					
			$RowData=$stmt-> fetch_array();
             $PoID=$RowData['PoID'];
			 $AryPoID=explode(',',$PoID);
			 $OrderCount= Count($AryPoID); 

		
	
	
		

		$AryData= getTableDate($PoID);	
		echo $AryData['TableData'];
	

	/*if($PoID!=''){
		$Query=" and SPD.PoID  in($PoID)";
	}
*/
	
	function getTableDate($PoID){
	global $iconn,$Query;
	  /*$sql="SELECT *,PoID,PoNo,PODate,PreparedBy,Name,ShipByDate,
sum(SPGD.Mcs) as Mcs,GROUP_CONCAT(SPGD.Grade) as Grade FROM ScrmPIData as SPD
LEFT JOIN ScrmPoGradeData as SPGD on SPGD.InvId=SPD.PoID 
LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
LEFT JOIN ScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge 
WHERE    $Query 

GROUP by PoID order by PoID Asc";  



	

$stmt=$iconn->query($sql);*/


$data='<table  id="key-datatable" data-paging="false" 
class="table  wrap w-100" >										
		
<thead class="thead-light">
				   <th  align="center"   >S No.</th>  
				   <th  align="center"  >ORDER NO</th>
				   <th  align="center"  ><B>BUYER</B></th>					   
				  	<th  align="center"  ><B>BRAND</B></th>	
				   <th  align="center"  ><B>VARIETY</B></th>
                   <th  align="center"  ><B>MCS</B></th>
				   <th  align="center"  ><B>PACK</B></th>			
				   <th  align="center"  ><B>GRADE</B></th>						   
				 	<th  align="center"  ><B>SHIPMENT DATE AS PER PO</B></th>			
				   <th  align="center"  ><B>DESTINATION</B></th>	
		
    </thead>
	<tbody>';
			if($PoID!=''){
$Sno=0;
$sql="SELECT *,PoID,PoNo,PODate,PreparedBy,Name,ShipByDate,
sum(SPGD.Mcs) as Mcs,GROUP_CONCAT(SPGD.Grade) as Grade FROM ScrmPIData as SPD
LEFT JOIN ScrmPoGradeData as SPGD on SPGD.InvId=SPD.PoID 
LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
LEFT JOIN ScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge 
WHERE    SPD.PoID  in($PoID) 

GROUP by PoID order by PoID Asc";  



	

$stmt=$iconn->query($sql);
				
  while($row= $stmt->fetch_array()){

	/*$InvId=$row['PoID'];

	$SqlBreakupDetails="SELECT Brand FROM  ScrmPoGradeData as SPGD 
WHERE   InvId=$InvId"; 
$StmtBreakupDetails=$iconn->query($SqlBreakupDetails);
$RowBreakupDetails=$StmtBreakupDetails->fetch_array();

$Brand=$RowBreakupDetails['Brand'];	
			 */


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



$Destination=$row['Name'];
$ShipByDate=$row['ShipByDate'];
$Brand=$row['Brand'];	

$Rcount=0;		
$AryPoID[]=$PoID;	
	$data.='<tr> 
	<td> '. ++$Sno.'</td>
	<td> '. $PoNo.'-('.$PoDate.')</td> 
	<td> '. $ConsigneeName.'</td>
	<td> '. $Brand.'</td>';



				

				 $SqlVariety="SELECT sum(Mcs) as Mcs,GROUP_CONCAT(Distinct ShortName) as ShortName,
				 GROUP_CONCAT(Distinct APS.PackingStyle) as PackingStyle,
				GROUP_CONCAT(Distinct SPDT.Grade,'-',SPDT.Mcs) as Grade	FROM `ScrmPoGradeData` SPDT lEFT join AdminPackingStyles APS on SPDT.PackingStyleID=APS.PCKID LEFT join ScrmPackingData SPD on SPDT.ID=SPD.POGradeId left Join ScrmPIVarietyList as SVL on 
				SVL.VarietyID=SPDT.VarietyID WHERE InvId='$PoID' 
				 GROUP By SPDT.InvId ";   
				 
				 
			$Stmtvariety=$iconn->query($SqlVariety);
			 $RowCount=mysqli_num_rows($Stmtvariety);
			if($RowCount==0){
				
	$data.='<td ></td>
	
	<td ></td>
	<td ></td>
	<td ></td>
	<td >'.getFormatedDatelines($ShipByDate).'</td>
	<td >'.$Destination.'</td>
	</tr>';
	}
			else{
			while($RowVariety=$Stmtvariety->fetch_array()){
			
				if($Rcount==0){
				
				}else{
					$data .='
					 
					 
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
				 <!--<td>'.getFormatedDatelines($ShipByDate).'</td>-->
				
				';
				 
				 if($Rcount==0){
				$data .='
				<td>'.getFormatedDatelines($ShipByDate).'</td>
				 <td >'.$Destination.'</td>'; 
			}else{
				$data .='
				 
				 <td ></td>
				 <td ></td>'; 
			}
			
				$Rcount++;		 
        $data .=' </tr>';
				  
				 
			}	
		}
	 
}

	}
  $data .='</tbody></table>';
$AryData['TableData']=$data;

	

	 return $AryData;
	
	 
	}



?>

