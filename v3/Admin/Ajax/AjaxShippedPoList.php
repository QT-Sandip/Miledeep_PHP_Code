<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$YEAR=$_POST['YEAR'];
$EXPORTER=$_POST['EXPORTER'];
$TYPE=$_POST['TYPE'];
$DateStart=$YEAR.'-04-01';
$YearEnd=$YEAR+1;
$DateEnd=$YearEnd.'-03-31';
 $_SESSION["SelectedYear"]=$_POST['YEAR'];

if($EXPORTER!=" "){
	$Query='and POD.ExporterID='.$EXPORTER;
}
else{
	$Query='';
}
//
//if($YEAR!=''){
	
	  /*$sql="SELECT * FROM $TblScrmPoData as POD 
left join $TblScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join $TblScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
left join ScrmCustomInvoices as SCI on POD.PoID=SCI.PiID 
left join $ScrmShipmentStatusDetails as SSSD on SSSD.PIID=POD.PoID 
left JOIN AdminExporterDetails as AED on AED.ExpID=POD.ExporterID 
WHERE SCI.CInvDate between '$DateStart' and '$DateEnd'  AND POD.Status=6 $Query order by PoID desc"; */

$sql="SELECT * FROM $TblScrmPoData as POD 
left join $TblScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join $TblScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
left join ScrmCustomInvoices as SCI on POD.PoID=SCI.PiID 
left join $ScrmShipmentStatusDetails as SSSD on SSSD.PIID=POD.PoID 
left JOIN AdminExporterDetails as AED on AED.ExpID=POD.ExporterID 
left join ScrmShipmentDetailsBreif as SDB on POD.PoID=SDB.PiID

WHERE TransportedDate!='' and ClosedDate='' $Query order by PoID desc"; 


$stmt=$iconn->query($sql);


$data=' <!--<table id="scroll-vertical-datatable" 
										class="table table-striped 
										table-bordered dt-responsive  w-100">-->
<table id="key-datatable"  class="table dt-responsive nowrap w-100">
				
<thead>

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
	


$ASDate = strtotime($row['ActualShipmentDate']);
$ActualShipmentDate = date("d-m-Y", $ASDate);
 
$ETADate = strtotime($row['ETA']);
$ETA = date("d-m-Y", $ETADate);
  
 
$PODate = strtotime($row['PoDate']);
$PO_Date = date("d-m-Y", $PODate);


if($ETA=='01-01-1970'){
	$ETA='*****';
}

if($PO_Date=='01-01-1970'){
	$PO_Date='*****';
} 

if($ActualShipmentDate=='01-01-1970'){
	
	$ActualShipmentDate='****';
}
 $PoID=$row['PoID'];                

/*
$SqlCstm="SELECT * FROM `ScrmCustomInvoices` WHERE PiID='$PoID'";
 $stmtCstm=$iconn->query($SqlCstm);
 $Cstm= $stmtCstm->fetch_array();
 */

 $Cstmnumber=$row['InvShortCode'].$row['CInvNo'];
 $CstmDate=$row['CInvDate'];
 
$CDate = strtotime($CstmDate);
$CsInvDate = date("d-m-Y", $CDate);
    
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
	 <!-- <a target="_blank" href="DocAddEIRReport?PIID='.$row['PoID'].'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="Add Export Information" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>
          
<a href="DocAddReports.php?PoID='.$row['PoID'].'" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 title="Add Invoices" data-original-title="View"><i class="dripicons-preview font-size-18"></i>
						 </a>-->

<a href="CreateShipmentStatus.php?PoID='.$row['PoID'].'" 
	 class="mr-3 text-primary"
	 data-toggle="tooltip" data-placement="top"
						 title="Update Status" data-original-title="View"><i class="mdi mdi-pencil font-size-18"></i>
						 </a>
						 
	
				
				</td></tr>';
	  
}
	 echo $data .='</tbody></table>';

	

//}


 
?>
