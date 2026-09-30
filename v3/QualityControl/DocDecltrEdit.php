<?php
session_start();

$QueryParms = $_GET['Q'];
$QueryParm = $_GET['Q'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

extract($_POST);
extract($_GET);

$PoID = $_GET['PoID'];

$SqlPO = "SELECT * FROM  ScrmPoData  where PoID='$PoID'";
$StmtPO = $iconn->query($SqlPO);
$RowPO = $StmtPO->fetch_array();
$ClientID = $_SESSION["ClientID"];


$SqlCinv = "SELECT * FROM ScrmCustomInvoices  where PiID='$PoID'";
$StmtCinv = $iconn->query($SqlCinv);
$rowCInv = $StmtCinv->fetch_array();

$sqlCvrngLtr = "SELECT * FROM QcCoveringLetter  where InvID='$PoID'";
$stmtCvrngLtr = $iconn->query($sqlCvrngLtr);
$CvrngLtrdetails = $stmtCvrngLtr->fetch_array();

$InvoiceID = $CvrngLtrdetails['InvID'];

$SqlLabUsers = "SELECT * FROM QcLabTechnologist where LbuStatus=1 ";
$StmtLabUsers = $iconn->query($SqlLabUsers);

$PageTitle = "Consignment Covering Note - " . $RowPO['PoNo'];
$PageTitleFolder = "Quality Control";



extract($_POST);
extract($_GET);



if (isset($_POST['submit'])) {

	if ($InvoiceID == '') {

		$oldRowCvrng = null;

		$SqlCvrngLtrInset = "INSERT INTO QcCoveringLetter
	 (`InvID`, `Date`, `VerifiedBy`, `Quantity`,
	 `QCertNo`, `TypeProcessing`, `TypeTransport`, 
	 `TypeTreated`, `PortDischarge`, `SealNo`,`VesselNo`,
	 `ContainerNo`,`DeclarationDate`) 
	 VALUES ('$PoID','$Date','$VerifiedBy','$Quantity',
	 '$QCertNo','$TypeProcessing','$TypeTransport',
	 '$TypeTreated','$PortDischarge','$SealNo',
	 '$VesselNo','$ContainerNo','$DeclarationDate')";
	} else {

		$oldRowCvrng = legacyAuditFetchRowById('QcCoveringLetter', 'InvID', $PoID, 'QualityControl');

		$SqlCvrngLtrInset = "UPDATE `QcCoveringLetter`
		SET `Date`='$Date',
		`VerifiedBy`='$VerifiedBy',
		`Quantity`='$Quantity',
		`QCertNo`='$QCertNo',
		`TypeProcessing`='$TypeProcessing',
		`TypeTransport`='$TypeTransport',
		`TypeTreated`='$TypeTreated',
		`PortDischarge`='$PortDischarge',
		`SealNo`='$SealNo',
		`VesselNo`='$VesselNo',
		`ContainerNo`='$ContainerNo',
		`DeclarationDate`='$DeclarationDate'
		WHERE InvID='$PoID'";
	}

	$StmtCvrngLtrInset = $iconn->query($SqlCvrngLtrInset);

	if ($InvoiceID == '') {
		$newInvId = $iconn->insert_id;
		legacyAuditLogInsertById('QcCoveringLetter', 'InvID', $newInvId, $_POST, ['Remarks' => 'Created Covering Letter'], 'QualityControl');
	} else {
		legacyAuditLogUpdateById('QcCoveringLetter', 'InvID', $PoID, $oldRowCvrng, $_POST, ['Remarks' => 'Updated Covering Letter'], 'QualityControl');
	}

	if ($StmtCvrngLtrInset) {
		header('Location: DocAddReports.php?Q=' . $QueryParm);
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

					<?php include($IncludesPath . "page-title.php"); ?>



					<div class="row">
						<div class="col-12">
							<div class="card">
								<form role="form" method="post">
									<div class="card-body">

										<div class="row">

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Means of Transport</label>
												<input readonly type="text" class="form-control" name='TypeTransport' id="TypeTransport" value="BY SEA" required>
											</div>


											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Container No</label>
												<input readonly type="text" class="form-control" name='ContainerNo' id="ContainerNo" placeholder="Enter Container Number" value="<?php echo $rowCInv['ContainerNo']; ?>" required>
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Vessel Name</label>
												<input readonly type="text" class="form-control" name='VesselNo' id="VesselNo" placeholder="Enter Vessel Name" value="TO BE SUBMITTED" required>
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Seal No</label>
												<input readonly type="text" class="form-control" name='SealNo' id="SealNo" placeholder="Enter Seal Number" value="<?php echo $rowCInv['LinerSealNo']; ?>" required>
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">State of type Processing</label>
												<input type="text" class="form-control" name='TypeProcessing' id="TypeProcessing" value="Processed(frozen)product" placeholder="Enter type of Processing" required>
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Type of Treatment</label>

												<select class="form-control select2" name="TypeTreated" id="TypeTreated">
													<option value="TREATED" <?php if (
																				$CvrngLtrdetails['TypeTreated']
																				== 'TREATED'
																			)  echo 'selected="selected"'; ?>>PHOSPHATE TREATED</option>
													<option value="NOTTREATED" <?php if (
																					$CvrngLtrdetails['TypeTreated']
																					== 'NOTTREATED'
																				) echo 'selected="selected"'; ?>>NON-PHOSPHATE</option>
													<option value="TREATEDNNOTTREATED" <?php if (
																							$CvrngLtrdetails['TypeTreated']
																							== 'TREATEDNNOTTREATED'
																						) echo 'selected="selected"'; ?>>PHOSPHATE TREATED & NON-PHOSPHATE</option>
													<option value="SALTTREATMENT" <?php if (
																							$CvrngLtrdetails['TypeTreated']
																							== 'SALTTREATMENT'
																						) echo 'selected="selected"'; ?>>SALT TREATMENT</option>
													<option value="UNTREATED" <?php if (
																							$CvrngLtrdetails['TypeTreated']
																							== 'UNTREATED'
																						) echo 'selected="selected"'; ?>>UNTREATED</option>


													<select>

											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Q Certificate Number</label>
												<input type="text" class="form-control" name='QCertNo' id="QCertNo" value="<?php echo $CvrngLtrdetails['QCertNo'] ?>" placeholder="Enter Q Certificate NO " required>
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Date</label>
												<input type="date" class="form-control" name='Date' id="Date" value="<?php echo $CvrngLtrdetails['Date'] ?>">
											</div>








											<div hidden class="form-group col-lg-3">
												<label for="exampleInputEmail1">Net Weight</label>
												<input type="text" class="form-control" name='Quantity' id="Quantity" placeholder="Enter NetWeight" value="<?php echo $CvrngLtrdetails['Quantity'] ?>">
											</div>

											<div hidden class="form-group col-lg-3">
												<label for="exampleInputEmail1">Exporting Port</label>


												<select class="form-control select2" name="PortDischarge" id="PortDischarge">
													<option value="Visakhapatnam Port - INDIA" <?php if ($CvrngLtrdetails['PortDischarge'] == 'Visakhapatnam Port - INDIA')
																									echo 'selected="selected"'; ?>>Visakhapatnam Port</option>
													<option value="Krishnapatnam port - INDIA" <?php if ($CvrngLtrdetails['PortDischarge'] == 'Krishnapatnam port - INDIA')
																									echo 'selected="selected"'; ?>>Krishnapatnam port</option>
													<option value="Kattupalli Port - INDIA" <?php if ($CvrngLtrdetails['PortDischarge'] == 'Kattupalli Port - INDIA')
																								echo 'selected="selected"'; ?>>Kattupalli Port</option>
													<option value="Ennore Port - INDIA" <?php if ($CvrngLtrdetails['PortDischarge'] == 'Ennore Port - INDIA')
																							echo 'selected="selected"'; ?>>Ennore Port</option>
													<option value="Chennai Port - INDIA" <?php if ($CvrngLtrdetails['PortDischarge'] == 'Chennai Port - INDIA')
																								echo 'selected="selected"'; ?>>Chennai Port</option>


													<select>

											</div>


											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Verified By </label>
												<select class="form-control select2" name="VerifiedBy" id="VerifiedBy">

													<?php while ($RowLabUserDetails = $StmtLabUsers->fetch_array()) { ?>
														<option value="<?php echo $RowLabUserDetails['LbuName'] ?>" <?php if ($CvrngLtrdetails['VerifiedBy'] == $RowLabUserDetails['LbuName']) { ?> selected="selected" <?PHP } ?>><?php echo $RowLabUserDetails['LbuName'] ?></option>

													<?php  } ?>
												</select>

											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Declaration Date</label>
												<input type="date" class="form-control" name='DeclarationDate' id="DeclarationDate" value="<?php echo $CvrngLtrdetails['DeclarationDate'] ?>">
											</div>





										</div>

										<div class="col-xs-12 box-footer" align="right">
											<a href="DocAddReports.php?Q=<?php echo $QueryParm ?>" class="btn btn-danger">Close</a>

											<button type="submit" name="submit" class="btn btn-success waves-effect waves-light">

												Submit</button>
										</div>
								</form>

							</div>
						</div>

						<!-- end col -->
					</div> <!-- end row -->

				</div> <!-- end card body-->
			</div> <!-- end card -->
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
