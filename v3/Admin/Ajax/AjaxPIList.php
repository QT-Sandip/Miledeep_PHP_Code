<?php
//session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$uid=$_SESSION['UserID'];
$D_ID=$_SESSION["DeptID"];
$AccessType=$_SESSION["AccessType"]; 

$YEAR=$_POST['YEAR'];
$EXPORTER=$_POST['EXPORTER']; 
$Buyer=$_POST['Buyer']; 
$Country=$_POST['Country']; 
$TYPE=$_POST['TYPE'];
$DateStart=$YEAR.'-04-01';
$YearEnd=$YEAR+1;
$DateEnd=$YearEnd.'-03-31';
 $_SESSION["SelectedYear"]=$_POST['YEAR'];

//
	
	
if($EXPORTER!=" "){
	$Query='and POD.ExporterID='.$EXPORTER;
}
else{
	$Query='';
}
 $Query;
	
if($Buyer!=" "){
	$SubQuery='and SCD.ConID='.$Buyer;
}
else{
	$SubQuery='';
}

if($Country!=" "){
	$SQuery='and SCL.id='.$Country;
}
else{
	$SQuery='';
}


if($YEAR!=''){
	
/* $sql="SELECT *,SCL.name as CountryName,POD.ExporterID as ExporterID,SSC.Name as PoStatus
 FROM $TblScrmPIData as POD 
left join ScrmShipmentStatusCodes as SSC on POD.Status=SSC.Code
left join $TblScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join $TblScrmCountriesList as  SCL on SCL.id=POD.DestinationCountry
WHERE POD.InvDate between '$DateStart' and '$DateEnd'  AND Status>=0   
$Query $SubQuery $SQuery  order by PoID desc";  */
$sql="SELECT *,SCL.name as CountryName,POD.ExporterID as ExporterID,SSC.Name as PoStatus
 FROM $TblScrmPIData as POD 
left join ScrmShipmentStatusCodes as SSC on POD.Status=SSC.Code
left join $TblScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join $TblScrmCountriesList as  SCL on SCL.id=POD.DestinationCountry
WHERE POD.InvDate between '$DateStart' and '$DateEnd'  AND Status!=7  
$Query $SubQuery $SQuery  order by PoID desc";  

 //echo $sql;
$stmt=$iconn->query($sql);


$data=' <table id="key-datatable" data-paging="false" 
										class="table dt-responsive nowrap w-100">
				
<thead>

         <tr>
	   <th>ID</th> 
	   <th>P.Inv No</th>
	   <th>P.Inv Date</th>	
	   <th>Po No</th>
	   <th>Po Date</th>	
		<th>Country</th>
		<th>Buyer</th>	   
	   <th>Status</th>	
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



$ConsigneeName=mb_strimwidth($row['ConsigneeName'], 0, 20, "...");
$PoNumber=mb_strimwidth($row['PoNo'], 0, 15, "...");

		$PoStatus=$row['PoStatus'];
			$CLR=$row['Color'];
	
$PoID=$row['PoID'];


		
	$data.='<tr> <td id="'.$row['ID'].'" > '. $row['PoID'].'</td>			
	    <td id="'.$row['ID'].'" > '.  $row['InvNo'].'</td>
		<td id="'.$row['ID'].'" > '. $INVOICE_DATE .'</td>
		<td id="'.$row['ID'].'" > '.  $PoNumber.'</td>       
		<td id="'.$row['ID'].'" > '. $PO_Date.'</td>       
		<td id="'.$row['ID'].'" > '.  $row['CountryName'].'</td>
		<td id="'.$row['ID'].'" > '.$ConsigneeName  .'</td>

		
		
		<td id="'.$row['PoID'].'" > ';
		if($AccessType==1) {$data.=' <div class="bg-active color-palette" align="center" >
	
	<button  class="btn btn-link badge badge-warning font-size-10 "
	style="width:70px;background:'.$CLR.';color:white"
					 data-toggle="popover" data-trigger="focus" 
					 
					 data-id="'.$row['PoID'].'" id="BtnPIStatus" 
							 value="'.$row['PoID'].'">
							 '.$PoStatus.'
							 </button>	
		
		</div>';}

else{
		$data.=' 
		<div  class=" badge badge-warning font-size-10 "
	style="width:70px;background:'.$CLR.';color:white"
					 >
							 '.$PoStatus.'
							 </div>	
		
		';}
			
		$data.='	</td>
				<!--<td id="'.$row['ID'].'" > '.$row['Remarks'].'</td>-->
	
			'; 
			$data .='<td>';
			             
						
		IF($AccessType == 1 ){
   
					
                   // if($row['Status']==0 ||  $row['Status']==1){ #uma 15/09/2022
					
                $data.='<a target="_blank" href="PIEdit?id='.$row['PoID'].'" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="PI Edit" data-original-title="Edit"
						 ><i class="mdi mdi-pencil font-size-16"></i></a>';
					//}
					
			   }ELSE{
				 
                      if($row['Status']==0){
					
                $data.='<a target="_blank" href="PIEdit?id='.$row['PoID'].'" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="PI Edit" data-original-title="Edit"
						 ><i class="mdi mdi-pencil font-size-16"></i></a>';
					}				 
				   
			   }	
		//if($row['Status']==1&$AccessType!=1){	
		if($row['Status']!=0&$AccessType!=1){	
				$data.='<a target="_blank" href="EditPI?PIID='.$row['PoID'].'" 
						 class="mr-3 text-primary"  data-placement="top"
						 data-toggle="tooltip"
						 title="PI Edit"><i class="mdi mdi-pencil font-size-16"></i></a>';
					
				}	
			
		 $data.='<a href="DocAddReports.php?PoID='.$row['PoID'].'" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 title="Add Invoices" data-original-title="View">
						 <i class="dripicons-preview font-size-18"></i>
						 </a>';
		
		if($AccessType==1) { $data.= '<button class="btn btn-link text-danger Delete" 
		data-id="'.$row['PoID'].'" 
			
							data-toggle="tooltip" data-placement="top" title="Delete" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-16">
							</i></button>';
							} 

		
	$data .='	</td></tr>';
	  
}
	 echo $data .='</tbody></table>';

					
					



}


 
?>
