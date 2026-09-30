<?php
//session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include ("../RestAPI.php");

$BillsType = trim($_POST["BillsType"]);
$Year = trim($_POST["Year"]);
$Exporter = trim($_POST["Exporter"]);
$Consignee = trim($_POST["Consignee"]);
$Country = trim($_POST["Country"]);
$Processor = trim($_POST["Processor"]);

$api = GetShipingBillList_API;
$bodyContent = array(
			'BillsType'=>$BillsType,
            'Year'=>$Year,
            'Exporter'=>$Exporter,
			'Processor'=>$Processor,
            'Consignee'=> (int)$Consignee,
            'Country'=>$Country);        

$resGetShipingBillsAPI = CallAPI($api, "GET", "", $bodyContent);

$List = $resGetShipingBillsAPI->Data->Data;

?>
<table  id="key-datatable" data-paging="false" class="table  wrap w-100" >
	<thead class="thead-light">
		<tr>
			<th>S.No</th>
			<th>ID</th>	
			<th>InvNo</th>
			<!-- <th>ExporterID</th>
			<th>ProcessorID</th> -->
			<th>Po No.</th>
			<th>Consignee Name</th>
			<th>Shipping Bill No.</th>
			<th>Invoice Status</th>
			<th style="width: 70px;">Actions</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($List as $item) { ?>
		
		<tr>
			<td><?php echo $item->Sno ?></td>	
			<td><?php echo $item->PoID ?></td>
            <td><?php echo $item->CInvNo ?></td>
			<td><?php echo $item->PoNO ?></td>			
			<td><?php echo $item->ConsigneeName ?></td>
			<!-- <td><?php // echo $item->ExporterID ?></td>
			<td><?php // echo $item->ProcessorID ?></td> -->
			<td><?php echo $item->ShippingBillNo ?></td>
			
			<td>
				<?php 
					if($BillsType == "0") {
						echo "<span class='btn btn-link badge badge-warning font-size-10' style='background: #666666;'>Pending</span>";
					}else{
						echo "<span class='btn btn-link badge badge-warning font-size-10' style='background: #025009;'>Completed</span>";
					}
				?>
			</td>
			<td>		
				<a href='<?php echo $resGetShipingBillsAPI->Data->Link.$item->PoID ?>' class='mr-3 text-primary' title='Add Shipping Bill'><i class='mdi mdi-pencil font-size-18'></i></a>
			</td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<?php 
/*
$uid=$_SESSION['r_id'];
$D_ID=$_SESSION["D_ID"];

  $AccessType=$_SESSION["ACCESS_TYPE"]; 
$YEAR=$_POST['YEAR'];
 $EXPORTER=$_POST['EXPORTER']; 
$TYPE=$_POST['TYPE'];
$DateFilter=getFinancialYear($YEAR);
$Buyer=$_POST['Buyer']; 
$Country=$_POST['Country'];
$InvType=$_POST['InvType'];

$Query='';	
$SubQuery='';
$SQuery='';	
$IQuery='';	

if($EXPORTER!=" "){
	$Query='and POD.ExporterID in ('.$EXPORTER.')';
}
if($Buyer!=" "){
	$SubQuery='and POD.ConsigneeID='.$Buyer;
}

if($Country!=" "){
	$SQuery='and POD.DestinationCountry='.$Country;
}
if($InvType=="0"){
	$IQuery=' SIFD.PIID not in (SELECT PoID FROM `ScrmCommercialData`) ';
}
else if($InvType=="1"){
	$IQuery="SCI.CInvDate $DateFilter and SCI.PiID  in 
		(SELECT PoID FROM `ScrmCommercialData`)";	
}

if($YEAR!=''){
	

//IF($TYPE=='POLIST'){
		
		


$sql="SELECT *,POD.ExporterID as ExporterID FROM  ScrmPoData as POD
left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID 
left join ScrmCommercialData as SCOM on POD.PoID=SCOM.PoID 
WHERE $IQuery and POD.Status not in (3,7)   
 $Query $SQuery  $SubQuery order by CommercialID desc";  
//exit;


$stmt=$iconn->query($sql);

//dt-responsive
$data='<table  id="key-datatable" data-paging="false" 
class="table  wrap w-100" >										
		
<thead class="thead-light">

	<tr>
	<th>S.No</th>
	<th>ID</th>
	<th>P.Inv No</th>
	<th>Inv No</th>
	<th>Inv Date</th>	
	<th >Po.No</th>
	<!-- <th>Country</th>	 -->  
	<th style="width: 20%;">Buyer</th>		
	<th>Status</th>		
	<th style="width: 20%;">Actions</th>
	</tr>

    </thead>
	<tbody>';
				
$C=1;
				
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

//$ConsigneeName=mb_strimwidth($row['ConsigneeName'], 0, 30, "...");
$ConsigneeName=$row['ConsigneeName'];

		
	//uma	
		if($row['CommercialID']==''){
			$PoStatus='Pending';
			$CLR='red';
		}else if($row['CommercialID']!=''){
			$PoStatus='Completed';
			$CLR='#008000';
		}
		

$PoID=$row['PiID'];
$ExporterID=$row['ExporterID'];
 $CInvNo=getInvoiceNumber($iconn,$ExporterID,$PoID);
$InvNo=getPIInvoiceNumber($iconn,$ExporterID,$PoID);

	 $CInvNo=checkEmpty($CInvNo);
	 
	$data.='<tr> 
		<td>'. $C++. '</td>
	   <td><a target="_blank" id="PoDetails" href="#" 
	  data-id='.$row['PiID'] .'data-value="
	  '. $row['PoNo'] .' class="fill-div">'. $row['PiID'] .'</a></td>	
	
		<td id="'.$row['ID'].'" > '. $InvNo.'</td>
		<td id="'.$row['ID'].'" > '. $CInvNo.'</td>
		<td id="'.$row['ID'].'" > '. $CInvDate.'</td>
		
		<td id="'.$row['ID'].'" > '.  $row['PoNo'].'</td>       
		<!--<td id="'.$row['ID'].'" > '.  $row['CountryName'].'</td>  -->     
		
		
		<td id="'.$row['ID'].'" > '.$ConsigneeName  .'</td>';
		
		$data.=' 
	<td id="'.$row['ID'].'" >	<div  class=" badge badge-warning font-size-10 "
	style="width:70px;background:'.$CLR.';color:white"
					 >
							 '.$PoStatus.'
							 </div>	</td>
		
	<td>	';
		
		
		if($row['CommercialID']==''){
			 
			
		$data.='	<a  href="AddCommercialInvoice?PIID='.$row['PiID'].'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="Add Commercial" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>
			';
			
		}else{
		
			$data.=' 
			
			
			<a href="AddCommercialInvoice?PIID='.$row['PiID'].'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						  title="Edit Commercial" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
          
			<a target="_blank" href="Reports/DocComInvoice.php?id='.$row['PiID'].'" 
	 class="mr-3 text-primary"
	 data-toggle="tooltip" data-placement="top"
	 title="Commercial Invoice" data-original-title="View">
	 <i class="mdi mdi-printer font-size-18"></i>
	</a>
	 <a target="_blank" href="Reports/DocPackingList.php?id='.$row['PiID'].'" 
	 class="mr-3 text-success"
	 data-toggle="tooltip" data-placement="top"
	 title="Packing List" data-original-title="View">
	 <i class="mdi mdi-printer font-size-18"></i>
	</a>	
          
			<a href="DocAddReports.php?PoID='.$row['PiID'].'" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 title="Add Invoices" data-original-title="View"><i class="dripicons-preview font-size-18"></i>
						 </a>
			     ';        
						
		}
		
						 $data.='</td></tr>';}
	  

	echo $data .='</tbody></table>';
	
}
 function checkEmpty($Value){
	if($Value==''){
		$Value='';
	}
	else{
		 $Value;
	}
	return $Value;
} */
?>


<script>
/*

 $(document).on('click','#PoDetails',function(e){
	 			e.preventDefault();

	var PoNo=$(this).data('value');
	  var PoID =$(this).data('id');
	  
	     $('#Update').val(PoID);	 
	     showPoPopUp(PoNo,PoID);
	  
    	});
		
function showPoPopUp(PoNo,PoID){
	
			 $.ajax({
		   url:'Ajax/AjaxPopUpPoDetails.php',
		  type:'POST',
		  data:{'PoID':PoID},
		  success:function(data){
			//  $("#Update").hide();

			 $('.modal-title').text('PO Status Details - '+PoNo);
			$("#PopUpTable").html(data);

			$('#myModal').modal('show');
					}
			});

}
$(document).on('click','#Update',function(e){
	 
	 var PoID =$('#Update').data('id');
	 var PoID =$('#Update').val();
	
	var Url='CreateShipmentStatus.php?PoID='+ PoID;
		window.open(Url,"_blank");
 });	*/
/*


$(document).on('click','#list',function(e){
	
     var SelectedDate =$('#Year').val(); 	
	 var Url='<?php // echo $CurrentUrl ?>?Year='+SelectedDate;
     window.open(Url,"_self");
	

 });
 
 $(document).on('click','#chbkExp',function(e){
	var chbkExp = $("#chbkExp").is(':checked');
	var chbkShip = $("#chbkShip").is(':checked');
	var SelectedDate='<?php // echo $PresentYear ?>';
/*
	 var Url='<?php // echo $CurrentUrl ?>?Year='+SelectedDate+'&chbkExp='+chbkExp+'&chbkShip='+chbkShip;
		window.open(Url,"_self");
		*/
		
		/*updateGraph(chbkExp,chbkShip); 
 }); 
 
 $(document).on('click','#chbkShip',function(e){
	var chbkExp = $("#chbkExp").is(':checked');
	var chbkShip = $("#chbkShip").is(':checked');
	var SelectedDate='<?php // echo $PresentYear ?>';
		
		updateGraph(chbkExp,chbkShip);
 });

 
 
 $(document).on('click','#BuyerDetails',function(e){
	 
	 var ConID=$('#ConID').val();

		 $.ajax({
		   url:'Ajax/AjaxBuyerDetails.php',
		  type:'POST',
		  data:{'ConID':ConID },
		  success:function(data){
			 
			
			$("#PopUpTable").html(data);
			$('#myModal').modal('show');
			
					}
			});
			
    	});	

*/

</script>

