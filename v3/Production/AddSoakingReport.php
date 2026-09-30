<?php

$Status = $_SESSION['Status'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");



$LotId = $_GET['ID'];
$SoakRefId = $_GET['SckID'];
$PostedUserID = $_SESSION["r_id"];
$PostedTimeDate = date("Y-m-d H:i:s");
$CurrentYear = date("Y");


$SqlLots = "SELECT * FROM `RmLotDetatils` where RmLotID='$LotId'";
$StmtLots = $iconn->query($SqlLots);
$RowLots = $StmtLots->fetch_array();
$LotNumber = $RowLots['LotNumber'];

//$SqlSoakingData="SELECT * FROM `ProdSoakingDetails` where LotID='$LotId' AND  SckID='$SoakRefId'";
$SqlSoakingData = "SELECT * FROM `ProdSoakingDetails` where LotID='$LotId'  and CreatedFrom=0  order by SckID desc limit 1";
$StmtSoakingData = $iconn->query($SqlSoakingData);
$RowSoakingData = $StmtSoakingData->fetch_array();

if ($LotId == 0) {
	$SqlSoakingData = "SELECT * FROM `ProdSoakingDetails` where LotID='$LotId' and CreatedFrom=0 and Year(SoakingDate)='$CurrentYear'order by SckID desc limit 100";
} else {
	$SqlSoakingData = "SELECT * FROM `ProdSoakingDetails` where LotID='$LotId' and CreatedFrom=0  order by SckID desc ";
}
$StmtSoakingData = $iconn->query($SqlSoakingData);


$SqlVAStations = "SELECT * FROM PpcVAStations";
$StmtVAStations = $iconn->query($SqlVAStations);

$SqlProdVariety = "SELECT ShortName as Name FROM `ScrmProductList` GROUP BY ShortName ORDER BY ShortName";
// $SqlProdVariety = "SELECT * FROM `PpcOptions` WHERE Value='ProdVariety'";
$StmtProdVariety = $iconn->query($SqlProdVariety);

$SqlSHIFTS = "SELECT * FROM `PpcOptions` WHERE Value='SHIFTS'";
$StmtShifts = $iconn->query($SqlSHIFTS);

$SqlTimePeriod = "SELECT * FROM `PpcOptions` WHERE Value='Timeperiod'";
$StmtTimePeriod = $iconn->query($SqlTimePeriod);

$PageTitle = "Soaking Details-" . $LotNumber;
$PageTitleFolder = "Production";



extract($_POST);
extract($_GET);

if (isset($_POST['submit'])) {



	$SqlInsert = "INSERT INTO `ProdSoakingDetails`(`SoakingDate`, `LotID`, `Source`, `Variety`,
	`TubNumber`, `SaltQty`, `ChemicalQty`, `SoakInTime`, `TimePeriod`, `SoakInCount`, `SoakInQty`,
	`SoakOutTime`, `SoakOutCount`, `SoakOutQty`, `Destination`, `PostedUserID`, `Remarks`, `Shift`, `PostedTimeDate`,CreatedFrom) 
				VALUES ('$SoakingDate','$LotId','$Source','$Variety','$TubNumber','$SaltQty',
				'$ChemicalQty','$SoakInTime','$TimePeriod','$SoakInCount','$SoakInQty','0',
				'$SoakOutCount','$SoakOutQty','0','$PostedUserID','$Remarks','$Shift','$PostedTimeDate',0)";


	$stmt = $iconn->query($SqlInsert);
	$SckID = $iconn->insert_id;

	if ($stmt) {
		legacyAuditLogInsertById('ProdSoakingDetails', 'SckID', $SckID, $_POST, [
			'ReferenceNo' => $SckID,
			'Remarks' => 'Soaking report created',
		], 'Production');
		echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "AddSoakingReport.php?ID=' . $LotId . '";
		</script>';
	}
}


?>



<!DOCTYPE html>

<html lang="en">
<link href="<?php echo  $href ?>" rel="stylesheet" type="text/css" />

<head>

	<?php include($IncludesPath . "title-meta.php"); ?>
	<?php include($IncludesPath . "DbLinks.php"); ?>
	<?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">


	<?php include($IncludesPath . "PreLoader.php"); ?>

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
						//Show Alert 
					?>
						<div class="alert alert-success alert-dismissible fade show" role="alert">
							<i class="mdi mdi-check-all mr-2"></i>
							<?php echo $Status;
							$_SESSION['Status'] = '';	  ?>
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>

					<?php } ?>

					<?php include($IncludesPath . "page-title.php"); ?>




					<div class="row">
						<div class="col-12">
							<div class="card">

								<form role="form" method="post">
									<div class="card-body">

										<div class="row">

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Soaking Date</label>
												<input required type="date" class="form-control" name='SoakingDate' id="SoakingDate" value="<?php echo $RowSoakingData['SoakingDate'] ?>" max="<?php echo date("Y-m-d"); ?>" placeholder="Enter Soaking Date">
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Start Time</label>
												<input required type="time" class="form-control" name='SoakInTime' id="SoakInTime" value="<?php echo $RowSoakingData['SoakInTime'] ?>" placeholder="Enter Start Time">
											</div>



											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Source Station</label>
												<select class="form-control select2" name="Source" id="Source">
													<option value="">Select Source Station</option>

													<?php while ($DataRow = $StmtVAStations->fetch_array()) {
														if ($RowSoakingData['Source'] == $DataRow['StationID']) { ?>

															<option selected=="selected" value="<?php echo $DataRow['StationID']; ?>">
																<?php echo $DataRow['UnitName']; ?></option>
														<?php    } else { ?>
															<option value="<?php echo $DataRow['StationID']; ?>">
																<?php echo $DataRow['UnitName']; ?></option>
													<?php    }
													} ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Time Period</label>
												<select class="form-control select2" name="TimePeriod" id="TimePeriod">
													<option value="">Select Time Period</option>
													<?php while ($DataRow = $StmtTimePeriod->fetch_array()) {
														if ($RowSoakingData['TimePeriod'] == $DataRow['Name']) { ?>

															<option selected=="selected" value="<?php echo $DataRow['Name']; ?>">
																<?php echo $DataRow['Name']; ?></option>
														<?php   } else { ?>
															<option value="<?php echo $DataRow['Name']; ?>">
																<?php echo $DataRow['Name']; ?></option>
													<?php   }
													} ?>
												</select>
											</div>




											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Variety</label>
												<select class="form-control select2" name="Variety" id="Variety">
													<option value="">Select Variety</option>

													<?php while ($DataRow = $StmtProdVariety->fetch_array()) {
														if ($RowSoakingData['Variety'] == $DataRow['Name']) { ?>
															<option selected=="selected" value="<?php echo $DataRow['Name']; ?>">
																<?php echo $DataRow['Name']; ?></option>
														<?php    } else { ?>
															<option value="<?php echo $DataRow['Name']; ?>">
																<?php echo $DataRow['Name']; ?></option>
													<?php    }
													} ?>
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Select Shift</label>
												<select class="form-control select2" name="Shift" id="Shift">
													<option value="">Select Shift</option>

													<?php while ($DataRow = $StmtShifts->fetch_array()) {
														if ($RowSoakingData['Shift'] == $DataRow['Name']) { ?>
															<option selected=="selected" value="<?php echo $DataRow['Name']; ?>">
																<?php echo $DataRow['Name']; ?></option>
														<?php    } else { ?>
															<option value="<?php echo $DataRow['Name']; ?>">
																<?php echo $DataRow['Name']; ?></option>
													<?php    }
													} ?>
												</select>
											</div>




											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Salt/Kg</label>
												<input required type="text" class="form-control" name='SaltQty' id="SaltQty" value="<?php echo $RowSoakingData['SaltQty'] ?>" placeholder="Enter Salt/Kg">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Chemical Quantity</label>
												<input required type="text" class="form-control" name='ChemicalQty' id="ChemicalQty" value="<?php echo $RowSoakingData['ChemicalQty'] ?>" placeholder="Enter Chemical Quantity">
											</div>

											<!--<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Incharge Name</label>
                  <input required type="text" class="form-control" name='NetWeight' id="NetWeight"
				  value="" placeholder="Enter Incharge Name">
                </div>-->

											<div class="form-group col-sm-12">
												<H3 style="color:GREEN"> Soaking Details</H3>
											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Tub Number</label>
												<input required type="text" class="form-control" name='TubNumber' id="TubNumber" value="" placeholder="Enter Tub Number">
												</select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">In Count</label>
												<input required type="text" class="form-control" name='SoakInCount' id="SoakInCount" value="<?php echo $RowSoakingData['SoakInCount'] ?>" placeholder="Enter In Count">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">In Quantity</label>
												<input required type="text" class="form-control" name='SoakInQty' id="SoakInQty" value="<?php echo $RowSoakingData['SoakInQty'] ?>" placeholder="Enter In Quantity">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Out Count</label>
												<input required type="text" class="form-control" name='SoakOutCount' id="SoakOutCount" value="<?php echo $RowSoakingData['SoakOutCount'] ?>" placeholder="Enter Out Count">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Out Quantity</label>
												<input required type="text" class="form-control" name='SoakOutQty' id="SoakOutQty" value="<?php echo $RowSoakingData['SoakOutQty'] ?>" placeholder="Enter Out Quantity">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Remarks</label>
												<input required type="text" class="form-control" name='Remarks' id="Remarks" value="<?php echo $RowSoakingData['Remarks'] ?>" placeholder="Enter Remarks">
											</div>

											<div align="right" style="align-self: right" class="col-lg-12">
												<!--<button id="AddCount" align="center" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span>ADD</button>-->
												<?php if ($LotId != 0) { ?>
													<a target="" href="#" onclick="window.close();" class="btn btn-danger  "> Close</a>
												<?php } ?>
												<button type="submit" name='submit' class="btn btn-success"><span class="glyphicon glyphicon-plus"></span>Submit</button>

											</div>

								</form>


							</div> <!-- end row -->

						</div> <!-- end card body-->
					</div> <!-- end card -->
				</div><!-- end col-->
			</div>
			<!-- end row-->

		</div> <!-- container-fluid -->





		<div class="col-12">
			<div class="card">
				<div class="card-body" id="ShowDataTable">

					<table id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">

						<thead class="thead-light">
							<tr>

								<th>Tub No</th>
								<th>Date</th>
								<th>Variety</th>
								<th>Time Period</th>
								<th>Start Time</th>
								<th>In Count</th>
								<th>In Qty</th>
								<th>Out Count</th>
								<th>Out Qty</th>
								<th>Yield %</th>
								<th>Station</th>
								<th>Remarks</th>
								<th>Actions</th>

							</tr>
						</thead>
						<tbody>

							<?php

							while ($RowData = $StmtSoakingData->fetch_array()) {
								$StationID = $RowData['Source'];
								$SqlVAStations = "SELECT * FROM `PpcVAStations` where StationID='$StationID'";
								$StmtVAStations = $iconn->query($SqlVAStations);
								$RowVAStations = $StmtVAStations->fetch_array();

								$InQty = $RowData['SoakInQty'];
								$OutQty = $RowData['SoakOutQty'];
								$Yield = (($OutQty / $InQty) * 100) - 100;
							?>
								<tr>

									<td> <?php echo $RowData['TubNumber']; ?> </td>
									<td> <?php echo date('d-m-Y', strtotime($RowData['SoakingDate'])); ?> </td>
									<td> <?php echo $RowData['Variety']; ?> </td>
									<td> <?php echo $RowData['TimePeriod']; ?> </td>
									<td> <?php echo $RowData['SoakInTime']; ?> </td>
									<td> <?php echo $RowData['SoakInCount']; ?> </td>
									<td> <?php echo $RowData['SoakInQty']; ?> </td>
									<td> <?php echo $RowData['SoakOutCount']; ?> </td>
									<td> <?php echo $RowData['SoakOutQty']; ?> </td>
									<td> <?php echo round($Yield, 2) . '%'; ?> </td>
									<td> <?php echo $RowVAStations['UnitName']; ?> </td>
									<td> <?php echo $RowData['Remarks']; ?> </td>
									<TD>



										<button class="btn btn-link text-danger Delete" data-id="<?php echo $RowData['SckID']; ?>" data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete"><i class="mdi mdi-trash-can font-size-16">
											</i></button>
									</TD>

								</tr>


							<?php

							}
							?>

						</tbody>

					</table>
				</div>
			</div>
		</div>


	</div>
	<!-- end main content-->
	<?php include($IncludesPath . "footer.php"); ?>
	</div>
	<!-- END layout-wrapper -->



	<?php include($IncludesPath . "vendor-scripts.php"); ?>
	<?php include($IncludesPath . "NewScripts.php"); ?>
	<?php include($IncludesPath . "ShowDataTable.php"); ?>



</body>

</html>
<script>
	$('document').ready(function() {
		showData();
	});
	$(document).on('click', '.Delete', function(e) {
		var SckID = $(this).data('id');

		var r = confirm("Are You Sure You Want To Delete " + SckID + "?");
		if (r == true) {

			//alert(SckID);		  
			$.ajax({
				url: 'Ajax/AjDltAl.php',
				type: 'POST',
				data: {
					'TYPE': 'SOACKING_REPORT',
					'D_ID': SckID
				},
				success: function(response) {
					var data = JSON.parse(response);
					if (data['sts'] == 'Failed') {
						alert(data['msg']);
						location.reload();
					} else {
						$('#myModal').modal('hide');
						alert(data['msg']);
						location.reload();

					}
				}
			});
		} else {}
	});
</script>


</html>
