<?php


session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

extract($_POST);
extract($_GET);
$LUID = $_GET['ID'];
$PlantID = $_SESSION['UnitID'];
if ($LUID == '') {
	$PageTitle = "Create LabUser Name";
} else {
	$PageTitle = "Edit LabUser Name";
}
$PageTitleFolder = "Quality Control";

$SqlLabUsersList = "SELECT * FROM `QcLabTechnologist` where LUID='$LUID' ";
$StmtLabUsersList = $iconn->query($SqlLabUsersList);
$RowLabUsersList = $StmtLabUsersList->fetch_array();


$SqlProductionPlant = "SELECT ProcessingStationID,StationName,FullName FROM `RmProcessingStations` where StationStatus=1";
$StmtProductionPlant = $iconn->query($SqlProductionPlant);



if (isset($_POST['submit'])) {
	$ID = $_GET['ID'];
	if ($ID == '') {


		$SqlLabNameInsert = "INSERT INTO `QcLabTechnologist`( `LbuDesignation`, `LbuName`, 
	`LbuUserType`, `LbuAccessType`, `LbuUnitID`, `LbuStatus`, `PlantID`)  VALUES 
		('$LbuDesignation','$LbuName','MB','3','1','1','$PlantID')";
		$StmtInsert = $iconn->query($SqlLabNameInsert);
		$MB_REPORT_ID = $iconn->insert_id;
		if ($StmtInsert) {
			legacyAuditLogInsertById('QcLabTechnologist', 'LUID', $MB_REPORT_ID, $_POST, ['Remarks' => 'Lab User Created'], 'QualityControl');
		}
	} else {
		$oldRow = legacyAuditFetchRowById('QcLabTechnologist', 'LUID', $ID, 'QualityControl');
		$SqlLabNameUpdate = "UPDATE `QcLabTechnologist` SET `LbuDesignation`='$LbuDesignation',
	`LbuName`='$LbuName' WHERE LUID='$ID'";
		$StmtInsert = $iconn->query($SqlLabNameUpdate);
		if ($StmtInsert) {
			legacyAuditLogUpdateById('QcLabTechnologist', 'LUID', $ID, $oldRow, $_POST, ['Remarks' => 'Lab User Updated'], 'QualityControl');
		}
	}
	if ($StmtInsert) {

		echo "<script>alert('Success');

		window.close('CreateTraceability.php');		

		</script>";
		header('Location: LabUsersList.php');
		//window.location.replace('TraceabilityList.php');

	}
}

?>

<!DOCTYPE html>

<html lang="en">

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
							$_SESSION['Status'] = ''; ?>
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>

					<?php } ?>

					<?php include($IncludesPath . "page-title.php"); ?>



					<!-- Main content -->



					<div class="row">
						<div class="col-12">
							<div class="card">

								<form role="form" method="post">
									<div class="card-body">

										<div class="row">

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Production Plant<span
														style="color:red">*</span></label>
												<select required class="form-control select2" name="drpArrivalPlant"
													id="drpArrivalPlant" disabled>
													<option value=""> Select </option>
													<?php while ($RowProductionPlant = $StmtProductionPlant->fetch_array()) { ?>

														<option <?php if ($RowProductionPlant['ProcessingStationID'] == $PlantID) { ?>
																selected=="selected" <?PHP } ?>
															Value="<?php echo $RowProductionPlant['ProcessingStationID']; ?>">
															<?php echo $RowProductionPlant['FullName'] . ' - ' . $RowProductionPlant['StationName']; ?>
														</OPTION>

													<?php } ?>
													<select>

											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Lab User Name </label>
												<input required type="text" class="form-control" name='LbuName'
													id="LbuName" placeholder="Enter Lab User Name"
													value="<?php echo $RowLabUsersList['LbuName'] ?>">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">User Designation</label>
												<input required type="text" class="form-control" name='LbuDesignation'
													id="LbuDesignation" placeholder="Enter Designation"
													value="<?php echo $RowLabUsersList['LbuDesignation'] ?>">
											</div>



										</div>


										<div class="col-xs-12 box-footer" align="right">

											<a target="" href="#" onclick="window.close();" class="btn btn-danger  ">
												Close</a>

											<button type="submit" name='submit' class="btn btn-success">Submit</button>
										</div>
								</form>

							</div> <!-- end row -->

						</div> <!-- end card body-->
					</div> <!-- end card -->


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
