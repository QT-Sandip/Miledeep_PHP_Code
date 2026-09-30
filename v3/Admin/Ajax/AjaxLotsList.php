<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$Month=$_POST['Month'];
$Month=sprintf("%02d", $Month);

$Year=$_POST['Year'];
$Type=$_POST['Type'];  
$PsId=$_POST['PsId'];
$OnDate=$_POST['OnDate']; 
 
//$btn_id=$_POST['ID'];
/*$DateStart=$Year.'-'.$Month.'-01';
$DateEnd=$Year.'-'.$Month.'-31 23:59:59';*/

if($PsId!=''){
	$Query="and RMD.PurchaseStationID ='$PsId'";
}


/*  $SqlRmLotDetatils="
SELECT `RmLotID`,`LotID`, `RmReferenceID`, `PostedUserID`, `LotNumber`, 
RMD.PurchaseStationID, StationName ,`VehicalID`, `AgentID`, `DriverID`,
 `GraderID`, `PurchaseCategoryID`,`CreatedDateTime`,  `Year`, RMD.LotStatus,
round(sum(ArrivedWeight),2) as PurchaseWeight,
GROUP_CONCAT(ArrivedCount) as PurchaseCount,
GROUP_CONCAT(ArrivedRate) as ArrivedRates,
(SELECT UserName from  RmUsersList  as RUL where RUL.ID=RMD.AgentID) as AgentName,
(SELECT UserName from  RmUsersList  as RUL where RUL.ID=RMD.GraderID) as GraderName,
COALESCE('Arrived') as LotStatusName, RLSD.ArrivalDate as PurchaseDate,
(SELECT PurchaseType from  RmPurchaseCategory  as RUL where RUL.CatID=RMD.PurchaseCategoryID) as PurchaseType
FROM `RmLotDetatils` as RMD 
left JOIN RmPurchaseStations as RPS on RPS.PurchaseStationID=RMD.PurchaseStationID
left join RmLotStatusDetails  as RLSD on RLSD.LotID=RMD.RmLotID
where RMD.LotStatus='1' $Query and ArrivalDate >= DATE_SUB(now(), 
INTERVAL 30 DAY) group by RMD.RmLotID,ArrivalDate
ORDER BY RMD.PurchaseDate DESC, RMD.LotNumber DESC LIMIT 100;";  */
 
 
 $SqlRmLotDetatils="
SELECT `RmLotID`,`LotID`, `RmReferenceID`, `PostedUserID`, `LotNumber`, 
RMD.PurchaseStationID, StationName ,`VehicalID`, `AgentID`, `DriverID`,
 `GraderID`, `PurchaseCategoryID`,`CreatedDateTime`,  `Year`, RMD.LotStatus,
round(sum(ArrivedWeight),2) as PurchaseWeight,
GROUP_CONCAT(ArrivedCount) as PurchaseCount,
GROUP_CONCAT(ArrivedRate) as ArrivedRates,
(SELECT UserName from  RmUsersList  as RUL where RUL.ID=RMD.AgentID) as AgentName,
(SELECT UserName from  RmUsersList  as RUL where RUL.ID=RMD.GraderID) as GraderName,
COALESCE('Arrived') as LotStatusName, RLSD.ArrivalDate as PurchaseDate,
(SELECT PurchaseType from  RmPurchaseCategory  as RUL where RUL.CatID=RMD.PurchaseCategoryID) as PurchaseType
FROM `RmLotDetatils` as RMD 
left JOIN RmPurchaseStations as RPS on RPS.PurchaseStationID=RMD.PurchaseStationID
left join RmLotStatusDetails  as RLSD on RLSD.LotID=RMD.RmLotID
where RMD.LotStatus='1' $Query and ArrivalDate='$OnDate' group by RMD.RmLotID,ArrivalDate
ORDER BY RMD.PurchaseDate DESC, RMD.LotNumber DESC LIMIT 100;";  
 
 
 //echo $SqlRmLotDetatils; exit;

$StmtRmLotDetatils=$iconn->query($SqlRmLotDetatils);
?>

<table  id="scroll-vertical-datatable"  data-paging="false" class="table dt-responsive wrap w-100" >													
		
<thead class="thead-light">
      

                  <th>ID</th>
                  <th>LOT</th>
				  <th>Arrival Date</th>
				   <th>Type</th>
				  <th>Grader</th>
				  <th>Agent</th>
				  <th>Count</th>
				  <th>Weight</th>
				  <th>Rate</th>
				  <th>Actions</th>
                </thead>
                <tbody>

			   

<?php	
	while($RowRmLotDetatils=$StmtRmLotDetatils->fetch_array()){
		$LotID=$RowRmLotDetatils['RmLotID'];
		$ReltdLotID=$RowRmLotDetatils['LotID'];
		$Agentarray = EXPLODE('->',$RowRmLotDetatils['AgentName']);
for($i=0;$i<count($Agentarray);$i++){
	 $Agent[]=$Agentarray[$i];
	 $AgentName=implode('<br>',$Agent);
}
unset($Agent);
		
	?>
			
				<tr>

                <td><?php echo $RowRmLotDetatils['RmLotID']; ?></td>
                <td><B><?php echo $RowRmLotDetatils['LotNumber']; ?></B></td>
                <td><?php echo date('d-m-Y',strtotime($RowRmLotDetatils['PurchaseDate'])); ?></td>			
                <td><?php echo $RowRmLotDetatils['PurchaseType']; ?></td>  
                <td><?php echo $RowRmLotDetatils['GraderName']; ?></td>
                <td><?php echo $AgentName; ?></td>
                <td><?php echo $RowRmLotDetatils['PurchaseCount']; ?></td>
                <td><?php echo $RowRmLotDetatils['PurchaseWeight']; ?></td>
                <td><?php echo $RowRmLotDetatils['ArrivedRates']; ?></td>
			
		 <td >
		 
		<!-- <a target="_blank" href="RelatedLotslist.php?LotID=<?php echo $ReltdLotID; ?>" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Lot Edit" data-original-title="Edit"
						 ><i class="mdi mdi-pencil font-size-16"></i>
		</a>-->
		
		<a target="_blank" href="#"  id="LotDetailss" data-id="<?php echo $RowRmLotDetatils['RmLotID'] ?>" 
		data-value="<?php echo $RowRmLotDetatils['LotNumber'] ?>"
						 class="mr-3 text-primary"  data-placement="top"
						 title="Lot Edit" data-original-title="Edit"
						 ><i class="mdi mdi-pencil font-size-16"></i>
		</a>
		
		<button class="btn btn-link text-danger Delete" 
		data-id="<?php echo $RowRmLotDetatils['RmLotID']; ?>" 
			
							data-toggle="tooltip" data-placement="top" title="Delete" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-16">
							</i></button>
		
		
		
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

	