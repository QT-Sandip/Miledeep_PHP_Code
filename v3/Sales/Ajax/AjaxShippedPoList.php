<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


$YEAR=$_POST['YEAR'];
$EXPORTER=$_POST['EXPORTER'];
$TYPE=$_POST['TYPE'];

$DateFilter=getFinancialYear($YEAR);
 $_SESSION["SelectedYear"]=$_POST['YEAR'];
$Query='';

if($EXPORTER!=" "){
	$Query='and POD.ExporterID in ('.$EXPORTER.')';
}

//if($YEAR!=''){
	
	  /*$sql="SELECT * FROM $TblScrmPoData as POD 
left join $TblScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join $TblScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
left join ScrmCustomInvoices as SCI on POD.PoID=SCI.PiID 
left join $ScrmShipmentStatusDetails as SSSD on SSSD.PIID=POD.PoID 
left JOIN AdminExporterDetails as AED on AED.ExpID=POD.ExporterID 
WHERE SCI.CInvDate $DateFilter  AND POD.Status=6 $Query order by PoID desc"; 
*/
 $sql="SELECT * FROM ScrmPoData as POD 
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
left join ScrmCustomInvoices as SCI on POD.PoID=SCI.PiID 
left join ScrmShipmentStatusDetails as SSSD on SSSD.PIID=POD.PoID 
left JOIN AdminExporterDetails as AED on AED.ExpID=POD.ExporterID 
left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=POD.PoID
WHERE TransportedDate!='' and (SDB.ClosedDate ='' or SDB.ClosedDate is null) $Query order by PoID desc"; 


$stmt=$iconn->query($sql);


$data=' 	<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">

      <tr>
	  <th>ID</th>
       	   <th>C.Inv No</th>
	   <th>C.Inv Date</th>	

	   <th>Po.No</th>
	   <th>Po.Date</th>
	   <th>Shipment Date</th>		
	   <!--<th>Buyer</th>	  -->
	   <th>Country</th>
	   <th>ETA</th>		
	 	
		<th>Actions</th>
		</tr>

    </thead>
	<tbody>';
				

				
  while($row= $stmt->fetch_array()){
	
$CsInvDate = getFormatedDateStar($row['CInvDate']);
$ActualShipmentDate = getFormatedDateStar($row['ActualShipmentDate']);
$ETA = getFormatedDateStar($row['ETA']);
$PO_Date = getFormatedDateStar($row['PoDate']);
$PoID=$row['PoID']; 
$Cstmnumber=$row['InvShortCode'].$row['CInvNo'];
$PoNumber=mb_strimwidth($row['PoNo'], 0, 15, "...");
 


 
	$data.=' <td id="'.$row['ID'].'" > '. $row['PoID'].'</td>
		
		<td id="'.$row['ID'].'" > '. $Cstmnumber .'</td>
		<td id="'.$row['ID'].'" > '. $CsInvDate.'</td>
		
		<td id="'.$row['ID'].'" > '.  $PoNumber.'</td>       
		<td id="'.$row['ID'].'" > '. $PO_Date.'</td>       
		<td id="'.$row['ID'].'" > '.  $ActualShipmentDate.'</td>
		
		<!--<td id="'.$row['ID'].'" > '.   $row['ConsigneeName'] .'</td>-->		
		<td id="'.$row['ID'].'" > '.  $row['name'].'</td>
		<td id="'.$row['ID'].'" > '.  $ETA .'</td>';
		
	$data.='   <td style="width:160px;"> 
	  <a target="_blank" href="DocAddEIRReport?PIID='.$row['PoID'].'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="Add Export Information" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>
          
<a href="DocAddReports.php?PoID='.$row['PoID'].'" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 title="Add Invoices" data-original-title="View"><i class="dripicons-preview font-size-18"></i>
						 </a>

	
				
				</td></tr>';
	  
}
	 echo $data .='</tbody></table>';

//}


 
?>
