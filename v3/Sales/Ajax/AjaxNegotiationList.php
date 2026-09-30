<?php
//session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include ("../RestAPI.php");

$uid=$_SESSION['UserID'];
$D_ID=$_SESSION["DeptID"];
$AccessType=$_SESSION["AccessType"]; 


$BillsType = trim($_POST["BillsType"]);
$Year = trim($_POST["Year"]);
$Exporter = trim($_POST["Exporter"]);
$Consignee = trim($_POST["Consignee"]);
$Country = trim($_POST["Country"]);

$api = GetCollectionList_API;
$bodyContent = array(
	'BillsType'=>$BillsType,
	'Year'=>$Year,
	'Exporter'=>$Exporter,
	'Consignee'=>$Consignee,
	'PaymentType'=>'2',
	'Country'=>$Country);        	

$resGetNegotiationListAPI = CallAPI($api, "GET", "", $bodyContent);
$resList = $resGetNegotiationListAPI->Data->CollectionList;

?>

<table  id="key-datatable" data-paging="false" class="table wrap w-100" >		
	<thead class="thead-light">        
	   <tr>
			<th>S.No</th>
			<th>ID</th>	
			<th>InvNo</th>
			<th>Po No.</th>
			<th>Consignee Name</th>
			<th>Bank Ref No</th>
			<th>Invoice Status</th>
			<th style="width: 70px;">Actions</th>
		</tr>
    </thead>
	<tbody>
		<?php foreach($resList as $item) { ?>
		<tr>
			<td><?php echo $item->Sno ?></td>
			<td><?php echo $item->PoID ?></td>
			<td><?php echo $item->CInvNo ?></td>
			<td><?php echo $item->PoNO ?></td>
			<td><?php echo $item->ConsigneeName ?></td>
			<td><?php echo $item->BankRefNo ?></td>

			<?php  if($BillsType == "1") {	?>
				<td><span class='btn btn-link badge badge-success font-size-10 complete'>Completed</span></td>
				<td class="text-center">
					<a href='<?php echo $resGetNegotiationListAPI->Data->Link.$item->PoID ?>' class='mx-2 text-primary' title='Edit Collections'><i class='mdi mdi-pencil font-size-18'></i></a>
					<a href='<?php echo $resGetNegotiationListAPI->Data->CreateBankLetterLink.$item->PoID ?>' class='mx-2 text-success' title='Edit Bill of exchange'><i class='mdi mdi-eye font-size-18'></i></a>
				
				</td>
			<?php } else { ?>
				<td><span class='btn btn-link badge badge-danger font-size-10 pending'>Pending</span></td>
				<td class="text-center">
					<a href='<?php echo $resGetNegotiationListAPI->Data->Link.$item->PoID ?>' class='mx-2 text-primary' title='Add Collections'><i class='mdi mdi-plus font-size-18'></i></a>
					<a href='<?php echo $resGetNegotiationListAPI->Data->CreateBankLetterLink.$item->PoID ?>' class='mx-2 text-success' title='Add Bill of exchange'><i class='mdi mdi-eye font-size-18'></i></a>
				
				</td>
			<?php } ?>

			
		</tr>
		<?php } ?>
	</tbody>
</table>

<?php 

/*
$Year=$_POST['Year'];
$Exporter=$_POST['Exporter']; 
$Consignee=$_POST['Consignee']; 
$Country=$_POST['Country']; 
$InvType=$_POST['InvType']; 
// $PymtType=$_POST['PymtType']; 



 $_SESSION["SelectedYear"]=$_POST['Year'];
	$Query='';
	$SubQuery='';
	$SQuery='';
	
if($Exporter!=" "){
	$Query='and POD.ExporterID in ('.$Exporter.')';
}

if($Consignee!=" "){
	$SubQuery='and SCD.ConID='.$Consignee;
}

if($Country!=" "){
	$SQuery='and SCL.id='.$Country;
}
$DateFilter=getFinancialYear($Year);
// $DateFilter="BETWEEN '$DateStart' and '$DateEnd'";

if($Year!=''){
	

 $sql="SELECT *,SCL.name as CountryName,POD.ExporterID as ExporterID,SSC.Name as PoStatus
 FROM ScrmPIData as POD 
left join ScrmShipmentStatusCodes as SSC on POD.Status=SSC.Code
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join ScrmCountriesList as  SCL on SCL.id=POD.DestinationCountry
left join ScrmCustomInvoices as SCIS on POD.PoID=SCIS.PiID
WHERE POD.InvDate $DateFilter  AND POD.Status!=7 AND 
SCIS.PaymentType='Negotiations'  
$Query $SubQuery $SQuery  order by PoID desc";  

 //echo $sql; exit;
$stmt=$iconn->query($sql);


$data=' <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">

        
	   <th>ID</th> 
	   <th>Inv No</th>
	   <th>P.Inv Date</th>	
	   <th>Po No</th>
	   <th>Po Date</th>	
		<th>Country</th>
		<th>Consignee</th>
	   <th style="width:120px;">Status</th>	
       <th style="width:100px;">Actions</th>
		

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
		if($AccessType==1) {
			$data.=' <div class="bg-active color-palette" align="center" >
						<button class="btn btn-link badge badge-warning font-size-10 "
								style="width:70px;background:'.$CLR.';color:white"
					 			data-toggle="popover" data-trigger="focus" 
								data-id="'.$row['PoID'].'" id="BtnPIStatus" 
							 	value="'.$row['PoID'].'">'.$PoStatus.'
						</button>	
		
		</div>';
	} else{
		$data.='    <div  class=" badge badge-warning font-size-10 " style="width:70px;background:'.$CLR.';color:white">'.$PoStatus.'</div>	';
	}
			
		$data.='	</td>
				<!--<td id="'.$row['ID'].'" > '.$row['Remarks'].'</td>-->
	
			'; 
			$data .='<td>';
			             
						//  IF($PymtType=='Collections'){
						//  $data.='<a target="_blank" href="AddCollection.php?PIID='.$row['PoID'].'" 
						//  class="mr-3 text-primary"  data-placement="top"
						//  title="Add Collection" data-original-title="Collections"
						//  ><i class="mdi mdi-plus font-size-16"></i></a>';
						//  }ELSE{
							 $data.='<a target="_blank" href="AddNegotiation.php?PIID='.$row['PoID'].'" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Add Negotiations" data-original-title="Break Up"
						 ><i class="mdi mdi-plus font-size-16"></i></a>'; 
							 
						//  }
						 
				$data.='<a target="_blank" href="PIEdit?id='.$row['PoID'].'" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Edit Proform" data-original-title="Edit"
						 ><i class="mdi mdi-pencil font-size-16"></i></a>';

	
		
	$data .='	</td></tr>';
	  
}
	 echo $data .='</tbody></table>';

					
					



}
*/

 
?>

