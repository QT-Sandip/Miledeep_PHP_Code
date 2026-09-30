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
$DateFilter=getFinancialYear($YEAR);
// $DateFilter="BETWEEN '$DateStart' and '$DateEnd'";

if($YEAR!=''){
	

 $sql="SELECT *,SCL.name as CountryName,POD.ExporterID as ExporterID,SSC.Name as PoStatus
 FROM $TblScrmPIData as POD 
left join ScrmShipmentStatusCodes as SSC on POD.Status=SSC.Code
left join $TblScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join $TblScrmCountriesList as  SCL on SCL.id=POD.DestinationCountry
left join ScrmShipmentDispatchDetails as SSDD on POD.PoID=SSDD.PIID
left join ScrmCustomInvoices as SCI on POD.PoID=SCI.PiID
left join ScrmPaymentTerms as SPTM on POD.PaymentTerms=SPTM.ID
left join ScrmNotifyparty as SNP on POD.NotifyPartyID=SNP.NotifyPartyID
WHERE POD.InvDate $DateFilter  AND POD.Status!=7  
$Query $SubQuery $SQuery  order by PoID desc";  

 //echo $sql; exit;
$stmt=$iconn->query($sql);


$data=' <table  id="key-datatable" data-paging="false" class="table dt-responsive wrap w-150" >										
		
<thead class="thead-light">

        
	   <th>ID</th> 
	   <th>P.Inv No</th>
	   <th>P.Inv Date</th>	
	   <th>Po No</th>
	   <th>Po Date</th>	
		<th>Country</th>
		<th>Buyer</th>
		<th>NotifyParty</th>
		<th>Shipment Terms</th>	   
		<th>Quantity Units</th>	   
		<th>IGST/Discount</th>	   
		<th>Discount</th>	   
		<th>Advance Amount</th>	   
	   <th>LC-1/N-LC-0</th>	
		 
     
		

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
$NotifyPartyName=mb_strimwidth($row['NotifyPartyName'], 0, 20, "...");
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
		<td id="'.$row['ID'].'" > '.$ConsigneeName .'</td>
		<td id="'.$row['ID'].'" > '.$NotifyPartyName .'</td>
		<td id="'.$row['ID'].'" > '.$row['ShippingTerms'] .'</td>
		<td id="'.$row['ID'].'" > '.$row['QtyUnit'] .'</td>
		<td id="'.$row['ID'].'" > '.$row['IGST'] .'</td>
		<td id="'.$row['ID'].'" > '.$row['Discount'] .'</td>
		<td id="'.$row['ID'].'" > '.$row['AdvanceAmount'] .'</td>
		<td id="'.$row['ID'].'" > '.$row['LcStatus'] .'</td>';
		

		
	$data .='	</td></tr>';
	  
}
	 echo $data .='</tbody></table>';

					
					



}


 
?>

