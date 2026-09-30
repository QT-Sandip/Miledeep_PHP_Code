<?php
//session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$uid=$_SESSION['r_id'];
$D_ID=$_SESSION["D_ID"];

  $AccessType=$_SESSION["ACCESS_TYPE"]; 
$YEAR=$_POST['YEAR'];
 $EXPORTER=$_POST['EXPORTER']; 
$TYPE=$_POST['TYPE'];
$DateStart=$YEAR.'-04-01';
$YearEnd=$YEAR+1;
$DateEnd=$YearEnd.'-03-31';
//
	
	
if($EXPORTER!=" "){
	$Query='and POD.ExporterID='.$EXPORTER;
}
else{
	$Query='';
}

if($YEAR!=''){
	

IF($TYPE=='POLIST'){
		
		

  
 /*$sql="SELECT *,name as CountryName,POD.ExporterID as ExporterID FROM $ScrmCustomInvoices as SCI
 left join $TblScrmPoData as POD on SCI.PiID=POD.PoID

left join $TblScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join $TblScrmCountriesList as  SCL on SCL.id=POD.DestinationCountry
WHERE SCI.CInvDate between '$DateStart' and '$DateEnd' and POD.Status !=3    $Query order by PoID desc";*/  

$sql="SELECT *,name as CountryName,POD.ExporterID as ExporterID FROM $ScrmCustomInvoices as SCI
 left join $TblScrmPoData as POD on SCI.PiID=POD.PoID

left join $TblScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join $TblScrmCountriesList as  SCL on SCL.id=POD.DestinationCountry
WHERE SCI.CInvDate between '$DateStart' and '$DateEnd' and POD.Status not in (3,7)    $Query order by PoID desc";  

	}


$stmt=$iconn->query($sql);


$data=' <table id="key-datatable" 
										class="table dt-responsive nowrap w-100">
				
<thead>

         <tr>
	   <th>ID</th>
	   <th>C.Inv No</th>
	   <th>C.Inv Date</th>	
	   <th>Po.No</th>
       <th>Country</th>	   
	   <th>Buyer</th>		
       <th>Actions</th>
		</tr>

    </thead>
	<tbody>';
				

				
  while($row= $stmt->fetch_array()){
	
			 
$PO_TIME_STAMP = strtotime($row['DO_SAMPLING']);
$DO_SAMPLING = date("d-m-Y", $PO_TIME_STAMP);

if($DO_SAMPLING=='01-01-1970'){
	$DO_SAMPLING='*****';
}

$PO_TIME_STAMP = strtotime($row['InvDate']);
$INVOICE_DATE = date("d-m-Y", $PO_TIME_STAMP);
 
$PODate = strtotime($row['PoDate']);
$PO_Date = date("d-m-Y", $PODate);

$CInvDate = strtotime($row['CInvDate']);
$CInvDate = date("d-m-Y", $CInvDate);

if($PO_Date=='01-01-1970'){
	$PO_Date='*****';
} 

if($INVOICE_DATE=='01-01-1970'){
	
	$INVOICE_DATE='****';
}

if($CInvDate=='01-01-1970'){
	$CInvDate='*****';
} 

if($CInvDate=='01-01-1970'){
	
	$CInvDate='****';
}

$ConsigneeName=mb_strimwidth($row['ConsigneeName'], 0, 30, "...");

		
	//uma	
		if($row['Status']==0){
			$PoStatus='Approval Pending';
			$CLR='#666';
		}else if($row['Status']==1){
			$PoStatus='Approved';
			$CLR='#008000';
		}
		else if($row['Status']==2){
			$PoStatus='Cancelled';
			$CLR='#4459FD';
		}
		
		else if($row['Status']==3){
			$PoStatus='Shipped';
			$CLR='#04c';
		}
		
		else if($row['Status']==4){
			$PoStatus='Rejected';
			$CLR='#FA0F04';
		}
		else if($row['Status']==5){
			$PoStatus='Po Received';
			$CLR='#025009';
		}
		

$PoID=$row['PoID'];
//echo $ExporterID=$row['ExporterID'];
 	
		
	$data.='<tr> <td id="'.$row['ID'].'" > '. $row['PoID'].'</td>
	   
		<td id="'.$row['ID'].'" > '. $row['CInvNo'] .'</td>
		<td id="'.$row['ID'].'" > '. $CInvDate.'</td>
		
		<td id="'.$row['ID'].'" > '.  $row['PoNo'].'</td>       
		<td id="'.$row['ID'].'" > '.  $row['CountryName'].'</td>       
		
		
		<td id="'.$row['ID'].'" > '.$ConsigneeName  .'</td>
		
			<td> 
			
			<!--<a target="_blank" href="EditPI?PIID='.$row['PoID'].'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
          
		  <a target="_blank" href="DocAddEIRReport?id='.$row['PoID'].'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="Add Export Information" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>-->
          
<a href="DocAddReports.php?PoID='.$row['PoID'].'" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 title="Add Invoices" data-original-title="View"><i class="dripicons-preview font-size-18"></i>
						 </a>
			     </td>        
						
	
		
  </tr>';}
	  
	 echo $data .='</tbody></table>';

					
					



}


 
?>
