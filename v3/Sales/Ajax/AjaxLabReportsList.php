
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
$Query.="and SPD.ExporterID in ($Exporter)";
}

if($Consignee!=''){
$Query.="and SPD.ConsigneeID in ($Consignee)";
}
if($Country!=''){
$Query.="and SPD.DestinationCountry in ($Country)";
}




$file_name='Orders List -'.$from_date.'-'.$to_date.'.xlsx';
$date_end=$to_date.' 23:59:59';  
		
/*$sql="SELECT GROUP_CONCAT(PoID) as PoID  FROM ScrmPoData as SPD 
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
			 $OrderCount= Count($AryPoID);*/ 

		
	
	
		/* $SqlGroup="SELECT Category,ShortName,GROUP_CONCAT(VarietyID) as VarietyID 
		FROM `ScrmPIVarietyList` GROUP BY Category order by Category";
		$StmtGroup=$iconn->query($SqlGroup);
		
		
		while($RowData=$StmtGroup-> fetch_array()){
		
		$AryData= getTableDate($RowData['VarietyID'],$PoID);	
		
		if($AryData['RowCount']>0){
			//echo '<h3>'.$RowData['Category']. '-'. $RowData['ShortName']  .'</h3>';
			
			echo $AryData['TableData'];
		}
		}*/
	

	
	//function getTableDate($VarietyID,$PoID){
	global $iconn,$Query;
	 $sql="SELECT ConsigneeName,SCI.PiID,PoNo,PODate,PreparedBy,SCL.name as DestinationCountry,ISF.ContainerNo as ctnnmbr
FROM ScrmCustomInvoices as  SCI 
left join ScrmPoData as SPD  on SPD.PoID=SCI.PiID
LEFT JOIN ScrmPoGradeData as SPGD on SPGD.InvId=SPD.PoID 
LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
LEFT JOIN ScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge 
left join ScrmCountriesList  SCL on SCL.id=SPD.DestinationCountry
left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID

WHERE  SCI.CInvDate $DateFilter $Query ";


$stmt=$iconn->query($sql);
	


$data='<table  id="key-datatable" data-paging="false" 
class="table  wrap w-150" >										
		
<thead class="thead-light">
				   <th  align="center"   >S No.</th>              
				   <th  align="center"  >PO.NO</th>
				   <th  align="center"  >Customer Name</th>					      	   
				   <th  align="center"  >Item</th>	
				   <th  align="center"  >DayCode</th>
				   <th  align="center"  >Cases</th>
				   <th  align="center"  >LabName</th>
				   <th  align="center"  >LabDate</th>
				   <th  align="center"  >ResultDate</th>
                  <th  align="center"  >Status</th>	
				   <th  align="center"  >Comments</th>	
		
    </thead>
	<tbody>';
				
$Sno=1;
				
  while($row= $stmt->fetch_array()){
	  $PoID=$row['PiID'];
	  $ConsigneeName=$row['ConsigneeName'];
	  $PoNo=$row['PoNo'];
	  $DestinationCountry=$row['DestinationCountry'];
	  $ContainerNo = $row['ctnnmbr'];

	
	$CInvNo=getInvoiceNumber($iconn,$ExporterID,$PoID);
		 


$LastUpdatedDate = date('d-m-Y',strtotime($row['LastUpdatedDate']));



//$PoNo=mb_strimwidth($row['PoNo'], 0, 15, "...");
 $SqlVarietyMcs="SELECT Description,Variety
			FROM `ScrmPoGradeData`  WHERE InvId='$PoID' GROUP by Variety,PackingStyleID,Brand 
			 Order by ScrmPoGradeData.ID asc"; 
			$StmtVarietyMcs=$iconn->query($SqlVarietyMcs);
			unset($AryDescription);
			unset($AryVariety);
			while($VarietyMcsRow=$StmtVarietyMcs->fetch_assoc()){
				
			$AryDescription[]=$VarietyMcsRow['Description'];
			$AryVariety[]=$VarietyMcsRow['Variety'];
			$Variety=implode('<br>',$AryVariety);
			$Description=implode('<br>',$AryDescription);

			}
			if($Description==''){
			$Description=$Variety;
             }
			 else{
				$Description; 
			 }	
	$data.='<tr> 
	<td> '.$Sno.'</td>		
	<td>'.$PoNo.'</td> 
	<td>'.$ConsigneeName.'</td>
	<td></td>
	<td></td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>';
				   
	
$Sno++;	 
}
  $data .='</tbody></table>';
$AryData['TableData']=$data;
//$AryData['RowCount']=$Rcount;
	

	 echo $data;
	
	 
	//}



?>