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


   $SqlRmLotDetatils="SELECT * from RmLotDetatils as RLD where PurchaseDate between '$DateStart' and '$DateEnd'   $Query order by PurchaseDate desc limit 50;";  
 
 //echo $SqlRmLotDetatils; exit; 

$StmtRmLotDetatils=$iconn->query($SqlRmLotDetatils);



function getArrivalWeight($ArrivalID){
global $iconn;
	$SqlRmLotDetatils="SELECT sum(Weight) as Weight FROM `RmArrivalBreakUpDetails` WHERE ArrivalBrkUpID='$ArrivalID'";
	$StmtRmLotDetatils=$iconn->query($SqlRmLotDetatils);
	$Data=$StmtRmLotDetatils->fetch_array();
	return $Data['Weight'];

}


function getAbStatus($ArrivalID){
	global $iconn;
		$SqlRmLotDetatils="SELECT AbStatus,Name,Colour FROM `QcRmAbReport` as ABR left join QcABOptions as ABO on ABR.AbStatus=ABO.OptID WHERE ABR.LotID='$ArrivalID'";
		$StmtRmLotDetatils=$iconn->query($SqlRmLotDetatils);
		$Data=$StmtRmLotDetatils->fetch_array();
		return $Data;
	
	}


	function getDhWeight($ArrivalID){
		global $iconn;
			$SqlRmLotDetatils="SELECT sum(WtAfterDH) as Weight FROM `PpcDeheading` WHERE LotID='$ArrivalID'";
			$StmtRmLotDetatils=$iconn->query($SqlRmLotDetatils);
			$Data=$StmtRmLotDetatils->fetch_array();
			return $Data['Weight'];
		
		}
	function getGrdWeight($ArrivalID){
		global $iconn;
			$SqlRmLotDetatils="SELECT sum(HLWeight) as Weight FROM `PpcHLGradingBreakup` WHERE LotID='$ArrivalID'";
			$StmtRmLotDetatils=$iconn->query($SqlRmLotDetatils);
			$Data=$StmtRmLotDetatils->fetch_array();
			return $Data['Weight'];
		
		}
	function getVAWeight($ArrivalID){
		global $iconn;
			$SqlRmLotDetatils="SELECT sum(WtAftrVA) as Weight FROM `PpcValueAddition` WHERE LotID='$ArrivalID'";
			$StmtRmLotDetatils=$iconn->query($SqlRmLotDetatils);
			$Data=$StmtRmLotDetatils->fetch_array();
			return $Data['Weight'];
		
		}


		
	function getSoakingWeight($ArrivalID){
		global $iconn;
			$SqlRmLotDetatils="SELECT sum(SoakInQty) as Weight FROM `ProdSoakingDetails` WHERE LotID='$ArrivalID'";
			$StmtRmLotDetatils=$iconn->query($SqlRmLotDetatils);
			$Data=$StmtRmLotDetatils->fetch_array();
			return $Data['Weight'];
		
		}


		
	function getFreezingWeight($ArrivalID){
		global $iconn;
			$SqlRmLotDetatils="SELECT sum(NetWeight) as Weight FROM `ProdProductionData`  WHERE LotID='$ArrivalID'";
			$StmtRmLotDetatils=$iconn->query($SqlRmLotDetatils);
			$Data=$StmtRmLotDetatils->fetch_array();
			return $Data['Weight'];
		
		}

		function getStoreWeight($ArrivalID){
			global $iconn;
				$SqlRmLotDetatils="SELECT sum(WtAftrVA) as Weight FROM `PpcValueAddition` WHERE LotID='$ArrivalID'";
				$StmtRmLotDetatils=$iconn->query($SqlRmLotDetatils);
				$Data=$StmtRmLotDetatils->fetch_array();
				return $Data['Weight'];
			
			}
?>

<table  id="scroll-vertical-datatable"  data-paging="false" class="table dt-responsive nowrap w-100" >													
		
<thead class="thead-light">
      

                  <th>ID</th>
                  <th>Arrival Date</th>
				  <th>Lot Number</th>
				  <th>Arrival Qty</th>
				  <th>Ab Status</th>
				  <th>Deheading Qty</th>
				  <th>Grading Qty</th>
				  <th>VA Qty</th>
				  <th>Soaking Qty</th>
				  <th>Packed Qty</th>
				  
                </thead>
                <tbody>

			   

<?php	
	while($RowRmLotDetatils=$StmtRmLotDetatils->fetch_array()){
		$LotID=$RowRmLotDetatils['RmLotID'];
		$ArrivalID=$RowRmLotDetatils['BreakUpID'];
		$ArrivalWeight=getArrivalWeight($ArrivalID);
		$AryAbStatus=getAbStatus($LotID);
		$DhWeight=getDhWeight($LotID);
		$GrdWeight=getGrdWeight($LotID);
		$VAWeight=getVAWeight($LotID);
		$SoakingWeight=getSoakingWeight($LotID);
		$PackedWeight=getFreezingWeight($LotID);



if($AryAbStatus['AbStatus']!=''){	
$AbStatus=$AryAbStatus['Name'];
 $Colour=$AryAbStatus['Colour'];
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

                <td><?php echo $LotID; ?></td>
				<td><?php echo date('d-m-Y',strtotime($RowRmLotDetatils['PurchaseDate'])); ?></td>
                <td><B><?php echo $RowRmLotDetatils['LotNumber']; ?></B></td>
				<td><?php echo $ArrivalWeight ?></td>	
				<td  width="10%">

			<div class="btn  badge badge-success font-size-12 " 
				style="background: <?php echo $Colour; ?>;"			>
			<span class="label"  ><?php echo $AbStatus; ?></span>
			</div>
			
			</td>		
                <td><?php echo round($DhWeight,2); ?></td>  
                <td><?php echo round($GrdWeight,2); ?></td>
                <td><?php echo round($VAWeight,2); ?></td>
                <td><?php echo round($SoakingWeight,2); ?></td>
                <td><?php echo round($PackedWeight,2); ?></td>
              
			



			

		 
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

	