<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");



$YEAR=$_POST['YEAR'];
$EXPORTER=$_POST['EXPORTER'];
$TYPE=$_POST['TYPE'];
$AccessType=$_SESSION["AccessType"];
$Buyer=$_POST['Buyer']; 
$Country=$_POST['Country']; 
$InvType=$_POST['InvType']; 
 $DateFilter=getFinancialYear($YEAR); 

$Query='';
$SubQuery='';
$SQuery='';
$IQuery='';	
if($EXPORTER!=" "){
	$Query='and POD.ExporterID in ('.$EXPORTER.')';
}
if($Buyer!=" "){
	$SubQuery='and SCD.ConID='.$Buyer;
}

if($Country!=" "){
	$SQuery='and SCL.id='.$Country;
}
if($InvType=="0"){

$IQuery=' POD.PoID  not in (SELECT PiID FROM `ScrmCustomInvoices` )';
}
else if($InvType=="1"){
$IQuery=" SCI.CInvDate $DateFilter and POD.PoID  in 
(SELECT PiID FROM `ScrmCustomInvoices` )";	
}

    $sql="SELECT PoID,CInvNo,StockOutDate,CInvDate,name,PoNO,PoDate,ConsigneeName,POD.ExporterID as ExporterID 
FROM ScrmPoData as POD 
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
WHERE $IQuery  $SubQuery $SQuery $Query order by SCI.CInvID Asc" ; 

 
$stmt=$iconn->query($sql);
//dt-responsive

$data=' 	<table  id="key-datatable" data-paging="false" 
class="table  wrap w-100" >										
		
<thead class="thead-light">

      
	 
	  <th>S.No</th>
	  <th>ID</th>
	  <th>Inv No	</th>	
	  <th>Inv Date</th>
	  <th>Po No	</th>	
	  <!--  <th>Po Date</th>
	  <th >Country</th>	-->
	  <th style="width: 20%;" >Buyer</th>
	  <th>P.Inv No	</th>	
	  <th  style="min-width: 160px;">Stock Status </th>
	  <th  style="min-width: 200px;">Actions</th>
	  

    </thead>
	<tbody>';
				

$C=1;				
  while($row= $stmt->fetch_array()){
	

$ExporterID = $row['ExporterID'];
$PoID = $row['PoID'];
$ConsigneeName=mb_strimwidth($row['ConsigneeName'], 0, 25, "...");
$PoNumber=mb_strimwidth($row['PoNO'], 0, 15, "...");
$PoNumber=$row['PoNO'];
$ConsigneeName=$row['ConsigneeName'];

if($row['StockOutDate']!=''){
$PoStatus="Stock Out";
$PoStatus=getFormatedDateStar($row['StockOutDate']);;
$CLR="Green";
}else{
$PoStatus='Pending';
$CLR="red";
}
$InvNo=getPIInvoiceNumber($iconn,$ExporterID,$PoID);
$CInvNo=getInvoiceNumber($iconn,$ExporterID,$PoID);

 $CInvNo=checkEmpty($CInvNo);
	$data.='
	<td> '.$C++.'</td>
	<td><a target="_blank" id="PoDetails" href="#" 
	  data-id='.$row['PoID'] .' data-value="'. $PoNumber .'" class="fill-div">'. $row['PoID'] .'</a></td>	
	
	  <td id="'.$row['ID'].'" class="tdCInvNo" > '.$CInvNo.'</td>
	  <td id="'.$row['ID'].'" > '.getFormatedDateStar($row['CInvDate']).'</td>
	  <td id="'.$row['ID'].'" > '.$PoNumber.'</td>
	  <!--	<td id="'.$row['ID'].'" > '.getFormatedDateStar($row['PoDate']).'</td>
	  <td id="'.$row['ID'].'" > '.$row['name'].'</td>	-->
	  <td id="'.$row['ID'].'" > '.$ConsigneeName.'</td>
	  <td id="'.$row['ID'].'" > '.$InvNo.'</td>
		
	
		';
		$data.=' 
	<td id="'.$row['ID'].'" >	<div  class=" badge badge-warning font-size-10 "
	style="width:120px;background:'.$CLR.';color:white"
					 >
							 '.$PoStatus.'
							 </div>	</td>
		
		';
		
		if($row['CInvNo']!=''){
			$Icon='pencil';
		}else{
			$Icon='plus';
		}
		
	$data.='<td> 
	  
	 <a href="AddCustomInvoice.php?PIID='.$row['PoID'].'" 
	 class="mr-1 text-primary "
	 data-toggle="tooltip" data-placement="top"
	 title="Add Custom Invoice" data-original-title="View">
	 <i class="mdi mdi-'.$Icon.' font-size-18"></i>
		</a>';
		$data.='<button id="Duplicate"  
						 class="mr-1 text-primary "  data-placement="top"
						 title="Duplicate" data-original-title="Edit" data-id="'.$row['PoID'].'"  data-value="'.$PoNumber.'"
						 ><i class="ri-file-copy-line  font-size-16"></i></button>
				<a href="#"  class="mr-3 text-success aViewDocRpt" data-toggle="modal" data-target="#modalView" data-placement="top" data-id="'.$row['PoID'].'" 
					title="View" data-original-title="View"><i class="dripicons-preview font-size-18"></i>
				</a>';
		
	if($row['CInvNo']!=''){	
		
		$data.='<a target="_blank" href="Reports/DocProformaExcel.php?id='.$row['PoID'].'"
	 class="mr-1 text-warning  aPrint"
	 data-toggle="tooltip" data-placement="top"
	 title="Excel" data-original-title="View">
	 <i class="mdi mdi-download font-size-16"></i><br><span style="font-size: 11px;">Excel</span>
	</a>
	<!--<a target="_blank" href="Reports/DocCustomInv.php?id='.$row['PoID'].'&PType=LBS" 
	 class="mr-1 text-primary  aPrint"
	 data-toggle="tooltip" data-placement="top"
	 title="Print Lbs" data-original-title="View">
	 <i class="mdi mdi-printer font-size-16"></i><br><span style="font-size: 11px;">LBs</span>
	</a>
	 <a target="_blank" href="Reports/DocCustomInv.php?id='.$row['PoID'].'&PType=KGS" 
	 class="mr-1 text-success aPrint"
	 data-toggle="tooltip" data-placement="top"
	 title="Print Kgs" data-original-title="View">
	 <i class="mdi mdi-printer font-size-16"></i><br><span style="font-size: 11px;">KGs</span>
	</a>
	<a target="_blank" href="Reports/DocGlazeInvoicePrint.php?id='.$row['PoID'].'" 
	 class="mr-1 text-info aPrint"
	 data-toggle="tooltip" data-placement="top"
	 title="Glaze Invoice" data-original-title="View">
	 <i class="mdi mdi-printer font-size-16"></i><br><span style="font-size: 11px;">Glaze</span>
	</a> 
	<a target="_blank" href="Reports/DocProformaExcel.php?id='.$row['PoID'].'"
	 class="mr-1 text-warning  aPrint"
	 data-toggle="tooltip" data-placement="top"
	 title="Excel" data-original-title="View">
	 <i class="mdi mdi-download font-size-16"></i><br><span style="font-size: 11px;">Excel</span>
	</a>
	
	<a target="_blank" href="Reports/DocProformaInvoicePrint.php?id='.$row['PoID'].'" 
						 class="mr-1 text-info aPrint"  data-placement="top"
						 title="Print Proforma " data-original-title="Edit"
						 ><i class="mdi mdi-printer  font-size-16"></i><br><span style="font-size: 11px;">Print PI</span></a>-->
	
	';						
	}
	  
						if($AccessType==1){ 
						 
					/*$data.='<button class="btn btn-link text-danger Delete" data-id="'.$row['PoID'].'" 
			
							data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button>'; 						
						*/
						}
	
	  
$data.='</td></tr>';
	  
}
	 echo $data .='</tbody></table>';

					
					



//}
function checkEmpty($Value){
	if($Value==''){
		$Value='';
	}
	else{
		$Value;
	}
	return $Value;
}

 
?>

<script>
 

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
	
	var Url='PoBreakUp.php?id='+ PoID;
	var Url='CreateShipmentStatus.php?PoID='+ PoID;
		window.open(Url,"_blank");
 });



</script>

