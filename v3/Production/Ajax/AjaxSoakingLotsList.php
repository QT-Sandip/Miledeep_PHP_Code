<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$Month=$_POST['Month'];
$Month=sprintf("%02d", $Month);

$Year=$_POST['Year'];
$Type=$_POST['Type'];  
$PsId=$_POST['PsId'];  
//$btn_id=$_POST['ID'];
$DateStart=$Year.'-'.$Month.'-01';
$DateEnd=$Year.'-'.$Month.'-31 23:59:59';

if($PsId!=''){
	$Query="and RLD.PurchaseStationID='$PsId'";
}


 $SqlRmLotDetatils="
SELECT RmLotID,PurchaseType,LotNumber,PurchaseCount,PurchaseWeight,
FarmAddress,PurchaseDate,
(SELECT sum(SoakInQty) FROM `ProdSoakingDetails` PSD where  PSD.LotID=RLD.RmLotID AND PSD.CreatedFrom=0)as SoakInQty
from RmLotDetatils as RLD 
LEFT JOIN RmArrivalBreakUpDetails AS RLBD ON RLD.BreakUpID=RLBD.ArrivalBrkUpID
left join RmPurchaseStations as RPS on RLD.PurchaseStationID=RPS.PurchaseStationID 
left join RmPurchaseCategory as RPC on RLD.PurchaseCategoryID=RPC.CatID 

where PurchaseDate between '$DateStart' and '$DateEnd' AND RLBD.Variety='Head On' $Query and LotStatus!=2 order by PurchaseDate,LotNumber desc";  
 
 //echo $SqlRmLotDetatils; //exit;

$StmtRmLotDetatils=$iconn->query($SqlRmLotDetatils);
?>

<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">

                  <th >ID</th>
                  <th >Lot</th>
				  <th >Rm Date</th>
				   <th >Type</th>
				  <th > In Qty(Kgs)</th>
				  <th >Actions</th>
                </thead>
                <tbody>

			   

<?php	
	while($RowRmLotDetatils=$StmtRmLotDetatils->fetch_array()){
		$LotID=$RowRmLotDetatils['RmLotID'];



if($RowRmLotDetatils['AbStatus']!=''){	
$AbStatus=$RowRmLotDetatils['Name'];
 $Colour=$RowRmLotDetatils['Colour'];
}else{
$AbStatus='Test Pending';
$Colour='gray';
}




		if($RowRmLotDetatils['LotStatus']==2){			
			$AbStatus.='  (R)';
		}
		
		IF($RowRmLotDetatils['PurchaseType']=='Shed weighment'){
			$AbStatus='NO TEST ';
			$Colour='#FFA500';
		}
	?>
			
				<tr>

                <td   ><?php echo $RowRmLotDetatils['RmLotID']; ?></td>
                <td   ><B><?php echo $RowRmLotDetatils['LotNumber']; ?></B></td>
                <td   ><?php echo date('d-m-Y',strtotime($RowRmLotDetatils['PurchaseDate'])); ?></td>			
                <td   ><?php echo $RowRmLotDetatils['PurchaseType']; ?></td>  
                <td   ><?php echo $RowRmLotDetatils['SoakInQty']; ?></td>  
             



			<!--<td  width="10%">

			<div class="btn  badge badge-success font-size-12 " 
				style="background: <?php echo $Colour; ?>;"			>
			<span class="label"  ><?php echo $AbStatus; ?></span>
			</div>
			
			</td>-->

		 <td >
		 <a target="_blank" href="AddSoakingReport.php?ID=<?php echo $LotID ?>"
						 class="mr-3 text-primary Add" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>
						 
				

	   </td>
		</tr>


	<?php } ?>
	</tbody>
	</table>

<script>
$(document).on('click', '.Return', function(e){
e.preventDefault();
	//alert('hiiiii');
	
	 var LotId =$(this).data('id');
	// var ClientStatus = $(this).val();
	 var Type = 'ReturnLot';
	  var r = confirm("Are You Sure Do You Want To Return This Lot?");
  if (r == true) {
  
	
	$.ajax({
	
	type: 'POST',
     url: 'Ajax/AjaxDelete.php',
     datatype:JSON,
    data: {'LotId': LotId, 'Type' : Type},
   
    success: function(data){
		/*showData(data);
        location.reload();*/
		var obj = JSON.parse(JSON.stringify(data));
		 
		 
    
		 if(obj.error == null){
			 alert("Lot Returned Successfully");
			location.reload();
		 }else{
			 alert(obj.error);
		 }
			 

     

     }
  });
 }else{}
  });
    

</script>

	