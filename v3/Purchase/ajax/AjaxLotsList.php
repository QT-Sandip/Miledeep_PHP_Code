<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$Month = $_POST['Month'];
$Month = sprintf("%02d", $Month);

$Year = $_POST['Year'];
$Type = $_POST['Type'];
$PsId = $_POST['PsId'];
$FromDate = $_POST['FromDate'];
$ToDate = $_POST['ToDate'];
//$btn_id=$_POST['ID'];
$DateStart = $Year . '-' . $Month . '-01';
$DateEnd = $Year . '-' . $Month . '-31 23:59:59';

if ($PsId != '') {
	$Query = "and RLD.PurchaseStationID='$PsId'";
}
$where = '';
if(empty($FromDate) && empty($ToDate)){
	$where = "  PurchaseDate between '$DateStart' and '$DateEnd'  ";
}else{
	if(!empty($FromDate) && !empty($ToDate)){
		$where = "  PurchaseDate between '$FromDate' and '$ToDate'  ";
	}elseif(!empty($FromDate)){
		$where = "  PurchaseDate >= '$FromDate'  ";
	}elseif(!empty($ToDate)){
		$where = "  PurchaseDate >= '$ToDate'  ";
	}
}


 $SqlRmLotDetatils = "SELECT RmLotID,AbStatus,Name,Colour,PurchaseType,LotNumber,PurchaseCount,PurchaseWeight,FarmAddress,RLD.LotStatus,
  PurchaseDate,RUL_G.UserName as GraderName,RUL_A.UserName as AgentName from RmLotDetatils as RLD 
LEFT JOIN RmArrivalBreakUpDetails AS RLBD ON RLD.BreakUpID=RLBD.ArrivalBrkUpID
left join RmPurchaseStations as RPS on RLD.PurchaseStationID=RPS.PurchaseStationID 
left join RmUsersList as RUL_G on RLD.GraderID=RUL_G.ID 
left join RmUsersList as RUL_A on RLD.AgentID=RUL_A.ID
LEFT JOIN (
    SELECT q.*
    FROM QcRmAbReport q
    INNER JOIN (
        SELECT LotID, MAX(AbRepID) AS MaxReportID
        FROM QcRmAbReport
        GROUP BY LotID
    ) latest
    ON q.LotID = latest.LotID AND q.AbRepID = latest.MaxReportID
) QCAB
    ON RLD.RmLotID = QCAB.LotID
LEFT join QcABOptions as ABO on ABO.OptID=QCAB.AbStatus
left join RmPurchaseCategory as RPC on RLD.PurchaseCategoryID=RPC.CatID 

where $where  $Query order by PurchaseDate,LotNumber desc";

// echo $SqlRmLotDetatils; exit; //AND RLBD.Variety='Head On'

$StmtRmLotDetatils = $iconn->query($SqlRmLotDetatils);
?>

<table id="scroll-vertical-datatable" data-paging="false" class="table dt-responsive nowrap w-100">

	<thead class="thead-light">
		<th>SNO</th>
		<!-- <th>ID</th> -->
		<th>Arrival Number</th>
		<th>Bill Date</th>
		<th>Purchase Type</th>
		<th>Grader</th>
		<th>Agent</th>
		<th>Arrival Count</th>
		<th>Arrival Weight</th>
		<th>AB Status</th>
		<th style="width: 100px;">Actions</th>
	</thead>
	<tbody>

		<?php

		$i = 0;
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
				<td><?php echo ++$i ?></td>
				<!-- <td><?php echo $RowRmLotDetatils['RmLotID']; ?></td> -->
				<td><B><?php echo $RowRmLotDetatils['LotNumber']; ?></B></td>
				<td><?php echo date('d-m-Y', strtotime($RowRmLotDetatils['PurchaseDate'])); ?></td>
				<td><?php echo $RowRmLotDetatils['PurchaseType']; ?></td>
				<td><?php echo $RowRmLotDetatils['GraderName'] ? $RowRmLotDetatils['GraderName'] : 'NA'; ?></td>
				<td><?php echo $RowRmLotDetatils['AgentName'] ? $RowRmLotDetatils['AgentName'] : 'NA'; ?></td>
				<td><?php echo $RowRmLotDetatils['PurchaseCount']; ?></td>
				<td><?php echo $RowRmLotDetatils['PurchaseWeight']; ?></td>

				<td width="10%">

					<div class="btn  badge badge-success font-size-12 " style="background: <?php echo $Colour; ?>;">
						<span class="label"><?php echo $AbStatus; ?></span>
					</div>

				</td>

				<td style="width: 100px;">

					<a target="_blank" href="CreateLot.php?LotID=<?php echo $RowRmLotDetatils['RmLotID']; ?>" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Lot Edit" data-original-title="Edit"
						 ><i class="mdi mdi-pencil font-size-16"></i>
					</a>

					<!--<a target="_blank" href="AdMorCnts.php?ID=<?php echo $RowRmLotDetatils['RmLotID']; ?>"
					title="Add More Counts"><i class="mdi mdi-plus font-size-18">
					</i></a>
		
					<a target="_blank" href="AddLotRmDefects.php?LotID=<?php echo $RowRmLotDetatils['RmLotID']; ?>"
					title="Add Defects"><i class="mdi mdi-plus font-size-18">
					</i>
					</a> -->

					<!--<a target="_blank" href="AddCrateDetails.php?LotID=<?php echo $RowRmLotDetatils['RmLotID']; ?>"
					title="Add "><i class="mdi mdi-truck font-size-18">
					</i>
					</a>-->
					<?php if ($RowRmLotDetatils['LotStatus'] == 2) {
						echo "Returned";
					} else { ?>
						<button class="btn btn-link text-danger Return" style="background:sky blue;" data-id="<?php echo $RowRmLotDetatils['RmLotID']; ?>" data-toggle="tooltip" data-placement="top" title="" value="" data-original-title="Return">Return Lot</button>
					<?php } ?>
					<!--	<div class="btn-group">
					<button type="button" class="btn btn-info">Options</button>

					<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
						<span class="caret"></span>
						<span class="sr-only">Toggle Dropdown</span>
					</button>

                  <ul class="dropdown-menu" role="menu">
                   	<li>
					<!--uma<a target="_blank" href="AdMorCnts.php?ID=<?php echo $RowRmLotDetatils['RmLotID']; ?>"
					>Add More Counts</a>
					</li>
				   	<li>
					<a target="_blank" href="AddLotRmDefects.php?LotID=<?php echo $RowRmLotDetatils['RmLotID']; ?>"
					>Add Defects</a>
					</li>
					<li>
					<a target="_blank" href="CreateLot.php?LotID=<?php echo $RowRmLotDetatils['RmLotID']; ?>"
					>Edit Lot</a>
					</li>
					<li>
					<button class="btn btn-link text-danger Return" data-id="<?php echo $RowRmLotDetatils['RmLotID']; ?>" 
			
							data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Return">Return Lot</button> 						
					</li>
					</ul>

						</div>
							-->
				</td>
			</tr>

		<?php }
		?>
	</tbody>
</table>

<script>
	$(document).on('click', '.Return', function(e) {
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
				data: {
					'LotId': LotId,
					'Type': Type
				},

				success: function(data) {
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
		} else {}
	});
</script>