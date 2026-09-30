<?php


session_start();
$QueryParms = $_GET['Q'];
$QueryParm = $_GET['Q'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

extract($_POST);
extract($_GET);
$ClientConfigData = $_SESSION['ClientConfigData'];

// $IsShowQcAddOnFields = $ClientConfigData->IsShowQcAddOnFields;  // uncomment this line after auth-login.php converted to API modal

$IsShowQcAddOnFields = $ClientConfigData['IsShowQcAddOnFields']; // remove this line after auth-login.php converted to API modal


$PageTitle = "Purchase Register MPEDA";

$PageTitleFolder = "Quality Control";


$PoID = $_GET['PoID'];

$sql_CODE_LIST = "SELECT CLID,Tracebility,DayCode,ShortName FROM `QcCodeList` as QCL
left Join QcPoVarietyList as PVL on QCL.VarietyID=PVL.VarietyID
left join QcTracebilityList As QTL on QTL.TraceID=QCL.TracebilityID
where QCL.InvID='$PoID' GROUP by QCL.TracebilityID,QCL.VarietyID,QCL.DayCode;";
$stmt_CODE_lIST = $iconn->query($sql_CODE_LIST);

$sql_LAB_USERS = "SELECT * FROM QcLabTechnologist WHERE LbuStatus=1";
$stmt_LAB_USERS = $iconn->query($sql_LAB_USERS);



$SqlPurchaseReg = "SELECT * FROM QcRawMaterialPurchaseReg WHERE InvID='$PoID'";
$StmtPurchaseReg = $iconn->query($SqlPurchaseReg);

if (isset($_POST['submit'])) {
	$Sql_query = "INSERT INTO `QcRawMaterialPurchaseReg`(`InvID`,`CLID`,`DateOfReciept`, 
	 `ProcessingDate`,`Yield`, `UsedQty`,`ReceivedQty`, `FinishedQty`,`Source`,`BalanceQuantity`) 
	 VALUES ('$PoID','$CLID','$DateOfReciept',
 '$ProcessingDate','$Yield', '$UsedQty','$ReceivedQty','$FinishedQty','$Source','$BalanceQuantity')";



	$stmt1 = $iconn->query($Sql_query);
	//$iid = $iconn->insert_id;
	//echo $iid;
	if ($stmt1) {
		$insertId = $iconn->insert_id;
		legacyAuditLogInsertById('QcRawMaterialPurchaseReg', 'RmPurID', $insertId, $_POST, ['Remarks' => 'MPEDA Purchase Register Entry Created'], 'QualityControl');
		header('Location: DocAddMpedaPurchaseReg.php?Q=' . $QueryParm);
	} else {
		$message = "Data Exist For Traceability";
		echo "<script type='text/javascript'>alert('$message');</script>";
	}
}

?>

<!DOCTYPE html>

<html lang="en">
<link href="https://serp.silverlights.in/Clients/JME/User.css" rel="stylesheet" type="text/css" />

<head>

	<?php include($IncludesPath . "title-meta.php"); ?>
	<?php include($IncludesPath . "DbLinks.php"); ?>


	<?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">


	<?php
	include($IncludesPath . "PreLoader.php"); ?>

	<!-- Begin page -->
	<div id="layout-wrapper">


		<?php
		include($IncludesPath . "topbar.php");
		include($IncludesPath . "sidebar.php");
		?>



		<!-- ============================================================== -->
		<!-- Start right Content here -->
		<!-- ============================================================== -->
		<div class="main-content">

			<div class="page-content">
				<div class="container-fluid">

					<?php
					if ($Status != '') {
						?>
						<div class="alert alert-success alert-dismissible fade show" role="alert">
							<i class="mdi mdi-check-all mr-2"></i>
							<?php echo $Status;
							$_SESSION['Status'] = ''; ?>
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>

					<?php } ?>

					<?php include($IncludesPath . "page-title.php"); ?>
					<div class="modal fade" id="myModal" role="dialog">

						<div class="modal-dialog">

							<!-- Modal content-->

							<div class="modal-content">

								<div class="modal-header">

									<button type="button" class="close" data-dismiss="modal">&times;</button>

									<h4 class="modal-title modheading"></h4>

								</div>

								<div class="modal-body">


									<div class="box-body" id="ATT_DATA">

									</div>




								</div>

								<div class="modal-footer">

									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>



								</div>

							</div>

						</div>

					</div>




					<!-- Main content -->



					<div class="row">
						<div class="col-12">
							<div class="card">

								<form role="form" method="post">
									<div class="card-body">

										<div class="row">

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Farm Registration No</label>

												<select class="form-control select2" name="CLID" id="CLID">
													<?php while ($RowCl = $stmt_CODE_lIST->fetch_array()) {
														$Tracibility = $RowCl['Tracebility'] . " - " . $RowCl['DayCode'] . " - " . $RowCl['ShortName'];





														?>
														<option value="<?php echo $RowCl['CLID'] ?>">
															<?php echo $Tracibility; ?>
														</option>
													<?php } ?>
													<select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Date of receipt of RAW Material</label>
												<input type="date" class="form-control" name='DateOfReciept'
													id="DateOfReciept"
													value="<?php echo $MPEDA_DATA['DateOfReciept'] ?>">
											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Date of Processing</label>
												<input type="date" class="form-control" name='ProcessingDate'
													id="ProcessingDate"
													value="<?php echo $MPEDA_DATA['ProcessingDate'] ?>"
													placeholder="Enter type of Processing" required>
											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Received Quantity in KGS </label>
												<input type="float" class="form-control" name='ReceivedQty'
													id="ReceivedQty" value="<?php echo $MPEDA_DATA['ReceivedQty']; ?>"
													placeholder="Enter Received Quantity in KGS" required>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Used Quantity in KGS </label>
												<input type="float" class="form-control" name='UsedQty' id="UsedQty"
													value="<?php echo $MPEDA_DATA['UsedQty']; ?>"
													placeholder="Enter Used Quantity in KGS" required>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Finished Quantity in KGS </label>
												<input type="float" class="form-control" name='FinishedQty'
													id="FinishedQty" value="<?php echo $MPEDA_DATA['FinishedQty']; ?>"
													placeholder="Enter Finished Quantity in KGS" required>
											</div>
											<?php if ($IsShowQcAddOnFields == 1) { ?>
												<div class="form-group col-sm-3">
													<label for="exampleInputEmail1">Balance Quantity in KGS </label>
													<input type="float" class="form-control" name='BalanceQuantity'
														id="BalanceQuantity"
														value="<?php echo $MPEDA_DATA['BalanceQuantity']; ?>"
														placeholder="Enter Balance Quantity in KGS" required>
												</div>
											<?php } ?>



											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Yield </label>
												<input type="float" class="form-control" name='Yield' id="Yield"
													value="<?php echo $MPEDA_DATA['Yield'] ?>" placeholder="Enter Yield"
													required>
											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1"> Source </label>
												<input type="text" class="form-control" name='Source' id="Source"
													value="AQUACULTURE" placeholder="Enter Source" required>
											</div>
										</div>


										<div class="col-xs-12 " align="right">

											<!-- <a  id='btn_close' class="btn btn-danger">Close</a>-->
											<a href="DocAddReports.php?Q=<?php echo $QueryParm; ?>"
												class="btn btn-danger">Close</a>
											<button type="submit" name='submit' class="btn btn-success">Submit</button>

										</div>
								</form>

							</div> <!-- end row -->

						</div> <!-- end card body-->
					</div> <!-- end card -->






					<div id="tdetails" class="table-responsive col-lg-12">
						<div class="card">

							<div class="card-body" id="ABReportList">






								<!--<table id="datatable-buttons" 
										class="table table-striped 
										table-bordered dt-responsive nowrap" >-->
								<table id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">

									<thead class="thead-light">

										<tr>

											<th>S No</th>
											<th>Farm Reg No</th>
											<th>Receipt Date</th>
											<th>Processing Date</th>
											<th>Received Quantity</th>
											<th>Used Quantity</th>
											<th>Finished Quantity</th>
											<th>Yield</th>
											<th>Source</th>
											<th>Action</th>



										</tr>

									</thead>
									<tbody>



										<?php

										//echo $sql;
										

										$SNO = 1;
										while ($row = $StmtPurchaseReg->fetch_array()) {



											$ClId = $row['CLID'];
											$RmPurID = $row['RmPurID'];

											$SqlTrace = "SELECT TracebilityID,TraceNumber,Tracebility FROM `QcCodeList` QCL 
LEFT JOIN QcTracebilityList QTL ON QTL.TraceID=QCL.TracebilityID WHERE CLID='$ClId'";
											$StmtTrace = $iconn->query($SqlTrace);
											$RowTrace = $StmtTrace->fetch_array();

											$Tracebility = $RowTrace['Tracebility'];


											$DateOfReciept = date("d-m-Y", strtotime($row['DateOfReciept']));
											$ProcessingDate = date("d-m-Y", strtotime($row['ProcessingDate']));
											?>
											<tr>



												<td id="<?php echo $row['RmPurID']; ?>"> <?php echo $SNO; ?></td>
												<td id="<?php echo $row['RmPurID']; ?>"> <?php echo $Tracebility; ?></td>
												<td id="<?php echo $row['RmPurID']; ?>"> <?php echo $DateOfReciept; ?></td>
												<td id="<?php echo $row['RmPurID']; ?>"> <?php echo $ProcessingDate; ?></td>


												<td id="<?php echo $row['RmPurID']; ?>"> <?php echo $row['ReceivedQty']; ?>
												</td>
												<td id="<?php echo $row['RmPurID']; ?>"> <?php echo $row['UsedQty']; ?></td>

												<td id="<?php echo $row['RmPurID']; ?>"> <?php echo $row['FinishedQty']; ?>
												</td>
												<td id="<?php echo $row['RmPurID']; ?>"> <?php echo $row['Yield']; ?></td>
												<td id="<?php echo $row['RmPurID']; ?>"> <?php echo $row['Source']; ?></td>


												<td style="width:250px;">


													<button class="btn btn-link text-danger Delete"
														data-id="<?php echo $row['RmPurID']; ?>" data-toggle="tooltip"
														data-placement="top" title="" value=""
														data-original-title="Delete"><i
															class="mdi mdi-trash-can font-size-18"></i></button>



												</td>



											</tr>

											<?php
											$SNO++;
										}
										?>





									</tbody>

								</table>
							</div><!-- end col-->
						</div>
						<!-- end row-->

					</div> <!-- container-fluid -->
				</div>
				<!-- End Page-content -->

				<?php include($IncludesPath . "footer.php"); ?>

			</div>
			<!-- end main content-->

		</div>
		<!-- END layout-wrapper -->



		<?php include($IncludesPath . "vendor-scripts.php"); ?>
		<?php include($IncludesPath . "NewScripts.php"); ?>



</body>

</html>
<script>
	//function reply_click(ClickedId) {
	$(document).on('click', '.Delete', function (e) {
		e.preventDefault();

		var RmPurID = '<?php echo $RmPurID ?>';
		var ClickedId = $(this).data('id');

		// alert("Are you sure to delete this Daycode?");
		var r = confirm("Are You Sure Do You Want To Delete?");
		if (r == true) {
			$.ajax({
				url: 'Ajax/AjaxBDdelete.php',
				type: 'POST',
				data: {
					'Type': 'MpedaRegDelete',
					'D_ID': ClickedId,
					'RmPurID': RmPurID
				},
				success: function (data) {
					var obj = JSON.parse(JSON.stringify(data));
					if (obj.error == null) {
						alert("Deleted Successfully");
						//showData(data);

						location.reload();
					} else {
						alert(obj.error);
					}

				}
			});
		} else { }
	});
</script>






<Script>
	$('document').ready(function () {
		showData(data);
	});

	function showData(data) {

		$("#ABReportList").html(data);

		var table = $('#key-datatable').DataTable({
			scrollY: "600px",
			scrollX: true,
			//sScrollX: "100%",
			sScrollXInner: "150%",
			// scrollCollapse:true,
			paging: true,
			destroy: true,
			fixedColumns: true,
			bSort: true,
			"bStateSave": true,
			"fnStateSave": function (oSettings, oData) {
				localStorage.setItem('key-datatable', JSON.stringify(oData));
			},
			"fnStateLoad": function (oSettings) {
				return JSON.parse(localStorage.getItem('key-datatable'));
			}
		});

	}

	$('#btn_close').on("click", function () {
		window.open('', '_parent', '');
		window.close();
	});
</script>
