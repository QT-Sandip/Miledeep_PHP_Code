<?php
//session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$uid=$_SESSION['UserID'];
$D_ID=$_SESSION["DeptID"];
$AccessType=$_SESSION["AccessType"]; 

$YEAR=$_POST['YEAR'];
$EXPORTER=$_POST['EXPORTER']; 
$Buyer=$_POST['Buyer']; 
$Country=$_POST['Country']; 
$TYPE=$_POST['TYPE'];
$InvoiceStatus=$_POST['InvoiceStatus'];



 $_SESSION["SelectedYear"]=$_POST['YEAR'];
	$Query='';
	$SubQuery='';
	$SQuery='';
	
if($EXPORTER!=" "){
	$Query='and POD.ExporterID in ('.$EXPORTER.')';
}

if($Buyer!=" "){
	$SubQuery='and SCD.ConID='.$Buyer;
}

if($Country!=" "){
	$SQuery='and SCL.id='.$Country;
}

if($InvoiceStatus!=""){
	$Query.='and POD.Status='.$InvoiceStatus;
}

try {
$SqlReport="SELECT * FROM `ScrmReports` where ReportCode='PI'";
$StmtReport=$iconn->query($SqlReport);


if($StmtReport != FALSE)
{
	$RowReport=$StmtReport->fetch_array();
	$PrintLink=$RowReport['Path'].$RowReport['PrintLink'].$RowReport['QueryID'].'=';
}

}catch(Exception $e){

}


if($PrintLink==''){
	$PrintLink='Reports/DocProformaInvoicePrint.php?id=';
}

/*
$SqlCustomInvoices="SELECT COALESCE(GROUP_CONCAT(SCI.PiID),0) as CompletatedPIID FROM
 `ScrmCustomInvoices` as SCI left Join ScrmShipmentDetailsBreif as SSDB on SCI.PiID=SSDB.PiID
		";
	$StmtCInv=$iconn->query($SqlCustomInvoices);
	$RowCInv=$StmtCInv->fetch_array();
	 $CompletatedPIID=$RowCInv['CompletatedPIID'];
	 */

 $sql="SELECT *,SCL.name as CountryName,POD.ExporterID as ExporterID,SSC.Name as PoStatus
 FROM ScrmPIData as POD 
left join ScrmShipmentStatusCodes as SSC on POD.Status=SSC.Code
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join ScrmCountriesList as  SCL on SCL.id=POD.DestinationCountry
WHERE POD.PoID not in (SELECT PiID FROM `ScrmCustomInvoices`) $DateFilter   
$Query $SubQuery $SQuery  order by InvNo asc limit 500 ";  
 

 //echo $sql; exit;
$stmt=$iconn->query($sql);

// dt-responsive remove Responsive To Remove + Icon	
$data=' <table  id="key-datatable" data-paging="false" 
class="table  wrap w-100" >										
	
<thead class="thead-light">

        <tr>
	   <th>S.No</th> 
	   <th>ID</th> 
	   <th>Processor </th> 
	   <th>P.Inv No</th>
	   <th>P.Inv Date</th>	 
	   <th>Po No</th>
	 <!--  <th>Po Date</th>	
		<th>Country</th>-->
		<th style="width: 15%;" >Buyer</th>
		<th>Ship By Date </th>	   
	   <th>Status</th>	
       <th>Actions</th>
		</tr>

    </thead>
	<tbody>';
	$C=1;			

				
  while($row= $stmt->fetch_array()){
	
			 
$ExporterID = $row['ExporterID'];
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
$PoNumber=$row['PoNo'];
$ConsigneeName=$row['ConsigneeName'];

		$PoStatus=$row['PoStatus'];
			$CLR=$row['Color'];
	
$PoID=$row['PoID'];
$ShipByDate=getFormatedDateNA($row['ShipByDate']);
$ExpApprovalNo=getExporterDetails($row['ProcessorID'])['ExpApprovalNo'];

$InvNo=getPIInvoiceNumber($iconn,$ExporterID,$PoID);
//$PrintLink='Reports/DocProformaInvoicePrint.php?id='.$row['PoID'];

		
	$data.='<tr>
<td>'.$C++.'</td>	
		<td id="'.$row['ID'].'" > '. $row['PoID'].'</td>			
	    <td id="'.$row['ID'].'" > '.  $ExpApprovalNo.'</td>
	    <td id="'.$row['ID'].'" > '.  $InvNo.'</td>
		<td id="'.$row['ID'].'" > '. $INVOICE_DATE .'</td>
		<td id="'.$row['ID'].'" > '.  $PoNumber.'</td>       
		<!--<td id="'.$row['ID'].'" > '. $PO_Date.'</td>       
		<td id="'.$row['ID'].'" > '.  $row['CountryName'].'</td>-->
		<td id="'.$row['ID'].'" > '.$ConsigneeName  .'</td>
		<td id="'.$row['ID'].'" > '.$ShipByDate.'</td>

		
		
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
			             
			$data.='<!--<a target="_blank" href="PoBreakUp.php?id='.$row['PoID'].'" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Break Up" data-original-title="Break Up"
						 ><i class="mdi mdi-plus font-size-16"></i></a> -->';
						 
				$data.='<a href="PoEdit?id='.$row['PoID'].'" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Edit Proform" data-original-title="Edit"
						 ><i class="mdi mdi-pencil font-size-16"></i></a>';

				$data.='<a target="_blank" href="'.$PrintLink.$row['PoID'].'" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Print Proforma " data-original-title="Edit"
						 ><i class="mdi mdi-printer  font-size-16"></i></a>';	

				$data.='<button id="Duplicate"  
						 class="mr-3 text-primary"  data-placement="top"
						 title="Duplicate" data-original-title="Edit" data-id="'.$row['PoID'].'"  data-value="'.$PoNumber.'"
						 ><i class="ri-file-copy-line  font-size-16"></i></button>';  							 
				$data.= '<button class="btn btn-link text-danger Delete" 
							data-id="'.$row['PoID'].'" 
			
							data-toggle="tooltip" data-placement="top" title="Delete" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-16">
							</i></button>';
						 
		/*				
						
		IF($AccessType == 1 ){
   
					
                 
					
                $data.='<a target="_blank" href="PIEdit?id='.$row['PoID'].'" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="PI Edit" data-original-title="Edit"
						 ><i class="mdi mdi-pencil font-size-16"></i></a>';
					
					
			   }ELSE{
				 
                      if($row['Status']==0){
					
                $data.='<a target="_blank" href="PIEdit?id='.$row['PoID'].'" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="PI Edit" data-original-title="Edit"
						 ><i class="mdi mdi-pencil font-size-16"></i></a>';
					}				 
				   
			   }	
	
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
*/
		
	$data .='	</td></tr>';
	  
}
	 echo $data .='</tbody></table>';

					
					



//}


 
?>

