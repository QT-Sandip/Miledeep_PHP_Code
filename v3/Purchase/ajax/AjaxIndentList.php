<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

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


   $SqlRmLotDetatils="SELECT *,RCD.ID as IndentID FROM `RmIndentDetails` as RCD 
  left join RmVehicalDetails as RVD on RVD.ID=RCD.VehicalID 
where IndentDateTime between '$DateStart' and '$DateEnd' $Query ";  
 
 //echo $SqlRmLotDetatils; exit;

$StmtRmLotDetatils=$iconn->query($SqlRmLotDetatils);
?>

<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
      

                  <th>ID</th>
                  <th>Lot No</th>
                  <th>Rm Date</th>
                  <th>Vehicle No</th>
				  <th>Indent Date</th>
				  <th>Indent Ice Crates</th>
				  <th>Indent Empty Crates</th>
				  <th>Indent Net Crates</th>
				  <th>Actions</th>
                </thead>
                <tbody>

			   

<?php	

//$LotNumbers[]=array();
	while($RowRmLotDetatils=$StmtRmLotDetatils->fetch_array()){
		
		$LotIDs=$RowRmLotDetatils['LotID'];


 $SqllotDetails="SELECT * FROM `RmBookingDetatils` WHERE RmBookingID in($LotIDs)";
$StmtlotDetails=$iconn->query($SqllotDetails);
while($RowlotDetails=$StmtlotDetails->fetch_array()){
	$LotNumber[]=$RowlotDetails['BookingNumber'];
	$PurchaseDate[]=getFormatedDate($RowlotDetails['BookingDate']);
	$LotNumbers=implode(',',$LotNumber);
	$PurchaseDates=implode(',',$PurchaseDate);
}
unset($LotNumber);
unset($PurchaseDate);
	?>
			
				<tr>

                <td><?php echo $RowRmLotDetatils['IndentID']; ?></td>
                <td><B><?php echo $LotNumbers; ?></B></td>
                <td><?php echo $PurchaseDates; ?></td>			
                <td><?php echo $RowRmLotDetatils['VehicalNumber']; ?></td>			
                <td><?php echo getFormatedDate($RowRmLotDetatils['IndentDateTime']); ?></td>			
                <td><?php echo $RowRmLotDetatils['IndentIceCrates']; ?></td>  
                <td><?php echo $RowRmLotDetatils['IndentEmptyCrates']; ?></td>
                <td><?php echo $RowRmLotDetatils['IndentNetCrates']; ?></td>
             

		 <td>
		 
		 <a target="_blank" href="AddIndentDetails.php?IndentID=<?php echo $RowRmLotDetatils['IndentID']; ?>" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Crate Details Edit" data-original-title="Edit"
						 ><i class="mdi mdi-pencil font-size-16"></i>
		</a> 
		
		<a target="_blank" href="Reports/IndentDetailsPrint.php?IndentID=<?php echo $RowRmLotDetatils['IndentID']; ?>" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Indent Details" data-original-title="Print"
						 ><i class="mdi mdi-printer font-size-16"></i>
		</a>
		
		
	
	   </td>
		</tr>


	<?php } ?>
	</tbody>
	</table>


	