<?php
//session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

?>


<?PHP
$uid=$_SESSION['r_id'];
$D_ID=$_SESSION["D_ID"];

  $AccessType=$_SESSION["ACCESS_TYPE"]; 
$Year=$_POST['YEAR'];
 $EXPORTER=$_POST['EXPORTER'];
 $Buyer=$_POST['Buyer']; 
 $Country=$_POST['Country']; 
$TYPE=$_POST['TYPE'];



 $_SESSION["SelectedYear"]=$_POST['YEAR'];

//

	$Query='';
/*if($Year!=""){

$DateFilter=getFinancialYear($Year);


$Query='and InvDate '.$DateFilter;
 
	//$Query='and year(InvDate)='.$YEAR;
}

*/

if($EXPORTER!=""){
	$Query.='and POD.ExporterID in ('.$EXPORTER.')';
}



  /*$sql="SELECT *,name as CountryName,POD.ExporterID as ExporterID,POD.PoID as PoID FROM ScrmPoData as POD 
left join ScrmPackingData as SPD on SPD.PoID=POD.PoID 
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
left join ScrmCountriesList as SCL on SCL.id=POD.DestinationCountry 
left join ScrmPoGradeData as SPDG on SPDG.InvId=POD.PoID
left join AdminPackingStyles as APS on APS.PCKID=SPDG.PackingStyleID
left join ScrmPIVarietyList as PVL on PVL.VarietyID=SPDG.VarietyID
left join ScrmPackingData as SPCKD on SPCKD.PoID=POD.PoID
WHERE POD.Status in(5,6) and POD.PoID not 
in(SELECT PIID FROM `ScrmShipmentDispatchDetails` WHERE SealTime!='') 
$Query    ORDER by abs(Grade) asc,ShortName asc";  */

$sql="SELECT *,name as CountryName,POD.ExporterID as ExporterID,POD.PoID as PoID FROM ScrmPoData as POD 
left join ScrmPackingData as SPD on SPD.PoID=POD.PoID 
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
left join ScrmCountriesList as SCL on SCL.id=POD.DestinationCountry 
left join ScrmPoGradeData as SPDG on SPDG.InvId=POD.PoID
left join AdminPackingStyles as APS on APS.PCKID=SPDG.PackingStyleID
left join ScrmPIVarietyList as PVL on PVL.VarietyID=SPDG.VarietyID
left join ScrmPackingData as SPCKD on SPCKD.PoID=POD.PoID
left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=POD.PoID

WHERE AvailableDate!='' and (SDB.TransportedDate ='' or SDB.TransportedDate is null) 
$Query    ORDER by abs(Grade) asc,ShortName asc"; 

$stmt=$iconn->query($sql);


$data='	<table  id="key-datatable" data-paging="false" 
class="table dt-responsive wrap w-100" >										
		
<thead class="thead-light">
	   <th>S.No</th>		  	
	   <th>ID</th>	
       <th>Variety</th>
	   <th>Grade</th>	   
	   <th>Po No</th>
	   <th>Buyer</th>
	   <th>Ship by Date</th>
       <th>L.Sample Date</th>
	   <th>Pck Status</th>		
	   <th>Art Work Date</th>		
	   <th>Pck ApprovalDate</th>		
	   <th>Pck OrderDate</th>		   
	   	   
	   <th>Brand</th>	
	   <th>Packing Style</th>	
       <th>Code</th>	
       <!--<th>Mcs</th>-->     
	  <!-- <th>Actions</th>  -->
		
    </thead>
	<tbody>';
				
$Sno=1;
				
  while($row= $stmt->fetch_array()){
	
			 







if($row['AvalMcs']==''){
	$AvalMcs='0';
} 
else{
	$AvalMcs=$row['AvalMcs'];
}


$ConsigneeName=mb_strimwidth($row['ConsigneeName'], 0, 20, "...");
$PoNo=mb_strimwidth($row['PoNo'], 0, 15, "...");

$ShipByDate=getFormatedDateNA($row['ShipByDate']);		
$MailSentDate=getFormatedDateNA($row['MailSentDate']);		
$ApprovalDate=getFormatedDateNA($row['ApprovalDate']);		
$OrderedDate=getFormatedDateNA($row['OrderedDate']);		
$ReceivedDate=getFormatedDateNA($row['ReceivedDate']);		
$LabSampleDate=getFormatedDateNA($row['LDate']);		
		

$PoID=$row['PoID'];
$MaterialStatus=$row['MaterialStatus'];

$Rcount=0;		
	
	
	$data.='<tr> 
	<td> '. $Sno.'</td>
<td> 
	 <a target="_blank" id="PoDetails" href="#"
	 data-id="'. $row['PoID'].'" data-value="'.$row['PoNo'].'"class="fill-div">'. $row['PoID'].' </a>
	</td>	
    <td>'.$row['ShortName'].'</td>
	 <td>'.$row['Grade'].'</td>	
	<td> '. $PoNo.'</td> 
	
	<td> '. $ConsigneeName.'</td>	
	<td> '. $ShipByDate.'</td>
	<td> '. $LabSampleDate.'</td>
	<td> '. $MaterialStatus.'</td>
	<td> '. $MailSentDate.'</td>
	<td> '. $ApprovalDate.'</td>
	<td> '. $OrderedDate.'</td>
	
	<td>'.$row['Brand'].'</td>
	<td>'.$row['PackingStyle'].'</td>
	<td>'.$row['Code'].'</td>
	<!--<td>'.$row['Mcs'].'</td>-->
	
	
	 </tr>';
				   	
	$Sno++; 
}
	 echo $data .='</tbody></table>';

					
					



//}


 
?>




