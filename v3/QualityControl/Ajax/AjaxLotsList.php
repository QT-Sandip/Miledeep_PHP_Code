<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$Month = $_POST['Month'];
$Month = sprintf("%02d", $Month);

$Year = $_POST['Year'];
$Type = $_POST['Type'];
$PsId = $_POST['PsId'];
//$btn_id=$_POST['ID'];
$DateStart = $Year . '-' . $Month . '-01';
$DateEnd = $Year . '-' . $Month . '-31 23:59:59';

if ($PsId != '') {
	$Query = "and RLD.PurchaseStationID='$PsId'";
}


$SqlRmLotDetatils = "SELECT RmLotID,AbStatus,Name,Colour,PurchaseType,LotNumber,PurchaseCount,PurchaseWeight,FarmAddress,PurchaseDate,RUL_G.UserName as GraderName,RUL_A.UserName as AgentName from RmLotDetatils as RLD 
left join RmPurchaseStations as RPS on RLD.PurchaseStationID=RPS.PurchaseStationID 
left join RmUsersList as RUL_G on RLD.GraderID=RUL_G.ID 
left join RmUsersList as RUL_A on RLD.AgentID=RUL_A.ID 
left join QcRmAbReport as QCAB on RLD.RmLotID=QCAB.LotID
LEFT join QcABOptions as ABO on ABO.OptID=QCAB.AbStatus
left join RmPurchaseCategory as RPC on RLD.PurchaseCategoryID=RPC.CatID 

where PurchaseDate between '$DateStart' and '$DateEnd' $Query order by PurchaseDate,LotNumber desc";

//echo $SqlRmLotDetatils; exit;

$StmtRmLotDetatils = $iconn->query($SqlRmLotDetatils);
?>

<table id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">

	<thead class="thead-light">


		<th align="CENTER">ID</th>
		<th align="CENTER">LOT</th>
		<th align="CENTER">Rm Date</th>
		<th align="CENTER">Type</th>
		<th align="CENTER">Grader</th>
		<th align="CENTER">Agent</th>
		<th align="CENTER">Count</th>
		<th align="CENTER">Weight</th>
		<th align="CENTER">AB Status</th>
		<th align="CENTER">Actions</th>
	</thead>
	<tbody>



		<?php
		while ($RowRmLotDetatils = $StmtRmLotDetatils->fetch_array()) {
			$LotID = $RowRmLotDetatils['RmLotID'];
			if ($RowRmLotDetatils['AbStatus'] != '') {
				$AbStatus = $RowRmLotDetatils['Name'];
				$Colour = $RowRmLotDetatils['Colour'];
			} else {
				$AbStatus = 'Test Pending';
				$Colour = 'gray';
			}
			if ($RowRmLotDetatils['LotStatus'] == 2) {
				$AbStatus .= '  (R)';
			}

			if ($RowRmLotDetatils['PurchaseType'] == 'Shed weighment') {
				$AbStatus = 'NO TEST ';
				$Colour = '#FFA500';
			}
			?>

			<tr>

				<td align="CENTER" width="5%">
					<font size="'.$text.'"><?php echo $RowRmLotDetatils['RmLotID']; ?></font>
				</td>
				<td align="CENTER" width="10%">
					<font size="'.$text.'"><B><?php echo $RowRmLotDetatils['LotNumber']; ?></B></font>
				</td>
				<td align="CENTER" width="10%">
					<font size="'.$text.'"><?php echo date('d-m-Y', strtotime($RowRmLotDetatils['PurchaseDate'])); ?></font>
				</td>
				<td align="CENTER" width="10%">
					<font size="'.$text.'"><?php echo $RowRmLotDetatils['PurchaseType']; ?></font>
				</td>
				<td align="CENTER" width="10%">
					<font size="'.$text.'"><?php echo $RowRmLotDetatils['GraderName']; ?></font>
				</td>
				<td align="CENTER" width="10%">
					<font size="'.$text.'"><?php echo $RowRmLotDetatils['AgentName']; ?></font>
				</td>
				<td align="CENTER" width="10%">
					<font size="'.$text.'"><?php echo $RowRmLotDetatils['PurchaseCount']; ?></font>
				</td>
				<td align="CENTER" width="10%">
					<font size="'.$text.'"><?php echo $RowRmLotDetatils['PurchaseWeight']; ?></font>
				</td>




				<td>

					<div class="btn  badge badge-success font-size-12 " align="center"
						style="background: <?php echo $Colour; ?>;">
						<span class="label" align="center"><?php echo $AbStatus; ?></span>
					</div>

				</td>

				<td>





					<a target="_blank" href="LotAbReportPrint.php?LotID=<?php echo $RowRmLotDetatils['RmLotID']; ?>"
						title="Add Defects"><i class=" mdi mdi-eye font-size-18">
						</i>
					</a>



				</td>
			</tr>


		<?php } ?>
	</tbody>
</table>

<script>
	$(document).on('click', '.Return', function (e) {
		e.preventDefault();
		//alert('hiiiii');

		var LotId = $(this).data('id');
		// var ClientStatus = $(this).val();
		var Type = 'ReturnLot';
		var r = confirm("Are You Sure Do You Want To Return This Lot?");
		if (r == true) {


			$.ajax({

				type: 'POST',
				url: 'Ajax/AjaxDelete.php',
				datatype: JSON,
				data: { 'LotId': LotId, 'Type': Type },

				success: function (data) {
					/*showData(data);
					location.reload();*/
					var obj = JSON.parse(JSON.stringify(data));



					if (obj.error == null) {
						alert("Lot Returned Successfully");
						location.reload();
					} else {
						alert(obj.error);
					}




				}
			});
		} else { }
	});


</script>