<?php

session_start();
$QueryParms = $_GET['Q'];
$QueryParm = $_GET['Q'];


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$ClientConfigArray = json_decode(json_encode($ClientConfigData), true);
$EnableQcLabSamplingType = !empty($ClientConfigArray['EnableQcLabSamplingType']);
$IsShowQcAddOnFields = $ClientConfigData->IsShowQcAddOnFields;  // uncomment this line after auth-login.php converted to API modal
// $IsShowQcAddOnFields = $ClientConfigData['IsShowQcAddOnFields']; // remove this line after auth-login.php converted to API modal


if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);
	$queries = array();
	parse_str($QueryParms, $_GET);
}

$REFERENCE_ID = $_GET['RID'];
$ClientID = $_SESSION["ClientID"];
$PoID = $_GET['PoID'];

$CurrentYear = date("Y");
$LastDate = $CurrentYear . '-03-31';
$NextYear = $CurrentYear + 1;
$YearNumber = substr($NextYear, -2);
$FinancialYear = $CurrentYear . '-' . $YearNumber;

$SqlPoData = "SELECT * FROM `QcSamplingLettersExt` where SMPLID='$REFERENCE_ID'";
$StmtPoData = $iconn->query($SqlPoData);
$RowPoData = $StmtPoData->fetch_array();

$LabSamplingMasterType = 0;
if ($EnableQcLabSamplingType && $PoID != '') {
	$LabSamplingTypeStmt = $iconn->query("SELECT type FROM QcPoLabSamplingType WHERE poid='$PoID' LIMIT 1");
	if ($LabSamplingTypeStmt && $LabSamplingTypeRow = $LabSamplingTypeStmt->fetch_assoc()) {
		$LabSamplingMasterType = (int) $LabSamplingTypeRow['type'];
	}
}


$SqlTests = "SELECT * FROM QcTestList ORDER BY `TestCategory` ASC";
$StmtTests = $iconn->query($SqlTests);


$SqlLab = "SELECT * FROM `QcLabTechnologist` where LbuStatus=1";
$StmtLab = $iconn->query($SqlLab);
//$StmtLab1 = $iconn->query($SqlLab);
$LabUsersArr = [];
while ($RowLabUsers = $StmtLab->fetch_array()) {
	$LabUsersArr[] = $RowLabUsers;
}

$SqlLabName = "SELECT * FROM `QcEiaLabNames` WHERE Status=1";
$StmtLabName = $iconn->query($SqlLabName);

$SqlLabRepresentationDescription = "SELECT distinct RepresentationDescription FROM `QcEiaLabNames` WHERE Status=1";
$StmtLabRepresentationDescription = $iconn->query($SqlLabRepresentationDescription);



$PageTitle = " Edit Sample Registration  Letter(External)";
$PageTitleFolder = "Quality Control";

// $LabInvoiceNumber = $_POST['labInvoiceNumber'];
// $Amount = $_POST['amount'];

$LabInvoiceNumber = isset($_POST['labInvoiceNumber']) ? $_POST['labInvoiceNumber'] : '';
$Amount = isset($_POST['amount']) ? $_POST['amount'] : '';



extract($_POST);
extract($_GET);


if (isset($_POST['submit'])) {
	$SamplingType = $EnableQcLabSamplingType ? intval(isset($_POST['SamplingType']) ? $_POST['SamplingType'] : 0) : 2;
	if (!in_array($SamplingType, array(1, 2))) {
		$SamplingType = ($LabSamplingMasterType == 1 || $LabSamplingMasterType == 2) ? $LabSamplingMasterType : 2;
	}

	$oldRowSamplingExt = legacyAuditFetchRowById('QcSamplingLettersExt', 'SMPLID', $REFERENCE_ID, 'QualityControl');
	$sql_update =
		"UPDATE `QcSamplingLettersExt` SET 
	 `SampleNumber`='$ReferenceNumber',
	 `SampleLocation`='$SampleLocation',
	 `TypeProduct`='$TypeProduct',
	 `SampleDrawnBy`='$SampleDrawnBy',
	 `AnalysisReq`='$ar',
	 `Date`='$DATE',
	 `TestingLab`='$TestingLab',
	 `VerifiedBy`='$VerifiedBy',
	 `ExtraColumnName`='$ExtraColumnName',
	 `ExtraColumnValue`='$ExtraColumnValue',
	 `ReportNumber`='$txtReportNo',
	 `ReportDate`='$txtReportDate',
	 `InvoiceNumber`='$txtInvoiceNo',
	 `InvoiceDate`='$txtInvoiceDate',
	 `Status`='$txtStatus',
	 `InvAmount`='$InvoiceAmount',
	 `ApprovedByID`='$ApprovedBy',
	 `InvoicercvedBY`='$InvoicercvedBY',
	 `LabInvoiceNumber`='" . $_POST['LabInvoiceNumber'] . "',
     `Amount`='" . $_POST['Amount'] . "',

	 `PreparedBy`='$PreparedBy',
	 `AccountantName`='$AccountantName',
	 `CompletedDate`='$CompletedDate',
	 SamplingType='$SamplingType'

	  
	 WHERE SMPLID=$REFERENCE_ID";

	$sql_update;
	$stmt1 = $iconn->query($sql_update);
	legacyAuditLogUpdateById('QcSamplingLettersExt', 'SMPLID', $REFERENCE_ID, $oldRowSamplingExt, $_POST, ['Remarks' => 'Updated External Sampling Letter'], 'QualityControl');

	// echo print_r($sql_update);
	// exit;

	if ($stmt1) {



		$ar = explode(",", $ar);
		$type = explode(",", $type);

		if ($type[0] != '') {
			$oldRowAnalysisExt = legacyAuditFetchRowById('QcEiaAnalysisRequired', 'ReportId', $REFERENCE_ID, 'QualityControl');
			echo $sql_update = "DELETE FROM `QcEiaAnalysisRequired` WHERE ReportId='$REFERENCE_ID'";
			$stmt1 = $iconn->query($sql_update);
			legacyAuditLogDeleteById('QcEiaAnalysisRequired', 'ReportId', $REFERENCE_ID, $oldRowAnalysisExt, ['Remarks' => 'Deleted EIA Analysis Required records'], 'QualityControl');

			$length = count($ar);
			$length1 = count($type);

			if ($stmt1) {

				for ($i = 0; $i < $length; $i++) {
					for ($k = 0; $k < $length1; $k++) {

						if ($k == $i) {
							echo $sql_org_insert1 = "INSERT INTO `QcEiaAnalysisRequired`(`ReportId`, `Type`, `TestName`) VALUES
				('$REFERENCE_ID','$type[$k]','$ar[$i]')";
						$stmt2 = $iconn->query($sql_org_insert1);
						$eaExtId = $iconn->insert_id;
						legacyAuditLogInsertById('QcEiaAnalysisRequired', 'ID', $eaExtId, $_POST, ['Remarks' => 'Re-inserted EIA Analysis Required'], 'QualityControl');
						}
					}
				}

				header('Location: DocAddReports.php?Q=' . $QueryParm);
			}
		} else {
			header('Location: DocAddReports.php?Q=' . $QueryParm);
		}
	}
}
//$IsShowQcAddOnFields = 0;
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
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


					<div class="modal fade" id="myModal1" role="dialog">
						<div class="modal-dialog">
							<!-- Modal content-->
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title modheading"></h4>
								</div>
								<div class="modal-body checkdiv1">

									<?php

									$Name = '';
									?>
									<?php while ($RowTests = $StmtTests->fetch_array()) {

										if ($Name != $RowTests['TestCategory']) {
											$Name = $RowTests['TestCategory'];
									?>

											<h3><?php echo $RowTests['TestCategory']; ?></h3>
										<?php } ?>


										<div class="checkbox">
											<label>


												<input type="checkbox" id="<?php echo $RowTests['TestCategory']; ?>"
													value="<?php echo $RowTests['TestName']; ?>">
												<?php echo $RowTests['TestName']; ?>

											</label>
										</div>

									<?php } ?>

								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								</div>
							</div>
						</div>
					</div>








					<div class="row">
						<div class="col-12">
							<div class="card">

								<form role="form" method="post">
									<div class="card-body">

										<div class="row">



											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Lab Name</label>
												<select class="form-control select2" name="TestingLab" id="TestingLab"
													required>

													<?php while ($RowEiaLabs = $StmtLabName->fetch_array()) { ?>
														<option <?php if ($RowPoData['TestingLab'] == $RowEiaLabs['LabName']) { ?> selected="selected" <?php } ?>
															value="<?php echo $RowEiaLabs['LabName']; ?>">
															<?php echo $RowEiaLabs['LabName'] . ' - ' . $RowEiaLabs['Address']; ?>
														</option>

													<?php } ?>
													<select>
											</div>
											<?php if ($EnableQcLabSamplingType) {
												$SamplingTypeValue = !empty($RowPoData['SamplingType']) ? (int) $RowPoData['SamplingType'] : (($LabSamplingMasterType == 1 || $LabSamplingMasterType == 2) ? $LabSamplingMasterType : '');
												$SamplingTypeDisabled = ($LabSamplingMasterType == 1 || $LabSamplingMasterType == 2);
												?>
												<div class="form-group col-sm-3">
													<label for="SamplingType">Sampling Type</label>
													<select class="form-control select2"
														name="<?php echo $SamplingTypeDisabled ? 'SamplingTypeView' : 'SamplingType'; ?>"
														id="SamplingType" required <?php echo $SamplingTypeDisabled ? 'disabled' : ''; ?>>
														<option value="">Select</option>
														<option value="1" <?php echo $SamplingTypeValue == 1 ? 'selected' : ''; ?>>Regular</option>
														<option value="2" <?php echo $SamplingTypeValue == 2 ? 'selected' : ''; ?>>Buyer</option>
													</select>
													<?php if ($SamplingTypeDisabled) { ?>
														<input type="hidden" name="SamplingType"
															value="<?php echo $SamplingTypeValue; ?>">
													<?php } ?>
												</div>
											<?php } ?>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Nature of Material</label>
												<select class="form-control select2" name="TypeProduct" id="TypeProduct"
													required>
													<option value="RAW" <?php if ($RowPoData['TypeProduct'] == "RAW")
																			echo 'selected="selected"'; ?>>RAW</option>
													<option value="COOKED" <?php if ($RowPoData['TypeProduct'] == "COOKED")
																				echo 'selected="selected"'; ?>>COOKED</option>
													<option value="BOTH" <?php if ($RowPoData['TypeProduct'] == "BOTH")
																				echo 'selected="selected"'; ?>>BOTH</option>

													<select>
											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Sample Location</label>
												<input type="text" class="form-control" name='SampleLocation'
													id="SampleLocation" placeholder="Enter Sample Location"
													value="<?php echo $RowPoData['SampleLocation'] ?>" required>
											</div>





											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Reference Date</label>
												<input type="date" value="<?php echo $RowPoData['Date'] ?>"
													class="form-control" name='DATE' id="DATE"
													value="<?php echo $RowPoData[''] ?>" required>
											</div>






											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Name of the Representation</label>




												<select class="form-control select2" name="SampleDrawnBy"
													id="SampleDrawnBy">
													<option>Select</option>

													<?php while ($RowRepresentationDescription = $StmtLabRepresentationDescription->fetch_array()) { ?>
														<option <?php if ($RowPoData['SampleDrawnBy'] == $RowRepresentationDescription['RepresentationDescription']) { ?> selected="selected" <?php } ?>
															value="<?php echo $RowRepresentationDescription['RepresentationDescription']; ?>">
															<?php echo $RowRepresentationDescription['RepresentationDescription']; ?>
														</option>

													<?php } ?>


												</select>

											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Verified By</label>

												<select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
													<option value="">Select</option>
													<?php
													foreach ($LabUsersArr as $RowLabUsers) {
														//while ($RowLabUsers = $StmtLab->fetch_array()) { 
													?>
														<option value="<?php echo $RowLabUsers['LbuName'] ?>" <?php if ($RowLabUsers['LbuName'] == $RowPoData['VerifiedBy']) {
																													echo 'selected="selected"';
																												} ?>>
															<?php echo $RowLabUsers['LbuName'] ?>
														</option>

														<?php echo $RowLabUsers['LbuName'] ?></option>

													<?php } ?>
												</select>
											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Analysis Required</label>
												<input type="text" autocomplete="off" class="form-control" name="ar"
													id="ar" placeholder="Enter Required Analysis"
													value="<?php echo $RowPoData['AnalysisReq'] ?>">
												<input type="hidden" class="form-control" name="type" id="type">

											</div>
											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Test Report No / Reference No</label>
												<input type="text" class="form-control" name='ReferenceNumber'
													id="ReferenceNumber" placeholder="Enter Name of Reference Number"
													value="<?php echo $RowPoData['SampleNumber'] ?>" required>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Extra Column Name</label>
												<input type="text" class="form-control" name='ExtraColumnName'
													id="ExtraColumnName" placeholder="Enter Extra Column Name"
													value="<?php echo $RowPoData['ExtraColumnName'] ?>">
											</div>


											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Extra Column Value</label>
												<input type="text" class="form-control" name='ExtraColumnValue'
													id="ExtraColumnValue" placeholder="Enter Extra Column Value"
													value="<?php echo $RowPoData['ExtraColumnValue'] ?>">
											</div>

											<div class="form-group col-sm-3 mb-3">
												<label for="exampleInputEmail1" class="form-label">Amount</label>
												<input type="text" class="form-control" id="Amount" name="Amount"
													placeholder="Enter Amount"
													value="<?php echo $RowPoData['Amount'] ?>">

											</div>

											<div class="form-group col-sm-3 mb-3">
												<label for="exampleInputEmail1" class="form-label">Tax Invoice
													Number/Lab Invoice Number</label>
												<input type="text" class="form-control" id="LabInvoiceNumber"
													name="LabInvoiceNumber" placeholder="Enter lab Invoice Number"
													value="<?php echo $RowPoData['LabInvoiceNumber'] ?>">

											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Invoice Received by</label>

												<select class="form-control select2" name="InvoicercvedBY"
													id="InvoicercvedBY">
													<option value="">Select</option>
													<?php
													foreach ($LabUsersArr as $RowLabUsers) {
														//while ($RowLabUsers = $StmtLab1->fetch_array()) { 
														?>
														<option value="<?php echo $RowLabUsers['LbuName'] ?>" <?php if ($RowLabUsers['LbuName'] == $RowPoData['InvoicercvedBY']) {
															   echo 'selected="selected"';
														   } ?>>
															<?php echo $RowLabUsers['LbuName'] ?>
														</option>

														<?php echo $RowLabUsers['LbuName'] ?></option>

													<?php } ?>
												</select>
											</div>

											<!-- <div class="form-group col-sm-3 mb-3">
												<label for="labInvoiceNumber" class="form-label">lab Invoice Number</label>
												<input type="text" class="form-control" id="labInvoiceNumber"
													name="labInvoiceNumber">

											</div> -->
											<div class="form-group col-sm-3">
												<label for="CompletedDate">Completed Date</label>
												<input type="Date" class="form-control" name='CompletedDate'
													id="CompletedDate" placeholder="Enter Completed Date"
													value="<?php echo $RowPoData['CompletedDate'] ?>">
											</div>
											<div class="form-group col-sm-3">
												<label for="AccountantName">Accountant name</label>
												<input type="text" class="form-control" name='AccountantName'
													id="AccountantName" placeholder="Enter Accountant name"
													value="<?php echo $RowPoData['AccountantName'] ?>">
											</div>
											<div class="form-group col-sm-3">
												<label for="PreparedBy">Prepared by</label>

												<select class="form-control select2" name="PreparedBy" id="PreparedBy">
													<option value="">Select</option>
													<?php
													foreach ($LabUsersArr as $RowLabUsers) {
														//while ($RowLabUsers = $StmtLab->fetch_array()) { 
														?>
														<option value="<?php echo $RowLabUsers['LbuName'] ?>" <?php if ($RowLabUsers['LbuName'] == $RowPoData['PreparedBy']) {
															   echo 'selected="selected"';
														   } ?>>
															<?php echo $RowLabUsers['LbuName'] ?>
														</option>

														<?php echo $RowLabUsers['LbuName'] ?></option>

													<?php } ?>
												</select>
											</div>
										</div>

										<?php if ($IsShowQcAddOnFields == 1) { ?>
											<div class="form-group col-lg-12"></div>
											<hr>
											<div class="row">

												<div class="form-group col-sm-3">
													<label for="txtReportNo">Report No</label>
													<input type="text" class="form-control" name='txtReportNo'
														id="txtReportNo" placeholder="Enter Report Number"
														value="<?php echo $RowPoData['ReportNumber'] ?>">
												</div>

												<div class="form-group col-sm-3">
													<label for="txtReportDate">Report Date</label>
													<input type="Date" class="form-control" name='txtReportDate'
														id="txtReportDate" placeholder="Enter Report Date"
														value="<?php echo $RowPoData['ReportDate'] ?>">
												</div>

												<div class="form-group col-sm-3">
													<label for="txtInvoiceNo">Invoice No</label>
													<input type="text" class="form-control" name='txtInvoiceNo'
														id="txtInvoiceNo" placeholder="Enter Invoice Number"
														value="<?php echo $RowPoData['InvoiceNumber'] ?>">
												</div>

												<div class="form-group col-sm-3">
													<label for="txtInvoiceDate">Invoice Date</label>
													<input type="Date" class="form-control" name='txtInvoiceDate'
														id="txtInvoiceDate" placeholder="Enter Invoice Date"
														value="<?php echo $RowPoData['InvoiceDate'] ?>">
												</div>

												<div class="form-group col-sm-3">
													<label for="InvoiceAmount">Invoice Amount</label>
													<input type="text" class="form-control" name='InvoiceAmount'
														id="InvoiceAmount" placeholder="Enter Invoice Amount"
														value="<?php echo $RowPoData['InvAmount'] ?>">
												</div>

												<div class="form-group col-sm-3">
													<label for="txtStatus">Status</label>
													<select class="form-control select2" name="txtStatus" id="txtStatus"
														required>
														<option <?php if ($RowPoData['Status'] == 'Pass') {
															echo 'selected="selected"';
														} ?>>Pass</option>
														<option <?php if ($RowPoData['Status'] == 'Fail') {
															echo 'selected="selected"';
														} ?>>Fail</option>
														<select>
												</div>

												<div class="form-group col-sm-3">
													<label for="exampleInputEmail1">Approved By </label>

													<select class="form-control select2" name="ApprovedBy" id="ApprovedBy">
														<option value="">Select</option>
														<?php
														foreach ($LabUsersArr as $RowLabUsers) {
															//while ($RowLabUsers = $StmtLab1->fetch_array()) { 
															?>
															<option value="<?php echo $RowLabUsers['LUID'] ?>" <?php if ($RowLabUsers['LUID'] == $RowPoData['ApprovedByID']) {
																   echo 'selected="selected"';
															   } ?>>
																<?php echo $RowLabUsers['LbuName'] ?>
															</option>

															<?php echo $RowLabUsers['LbuName'] ?></option>

														<?php } ?>
													</select>
												</div>

											</div>
										<?php } ?>


										<div class="col-xs-12 box-footer" align="right">
											<a href="DocAddReports.php?Q=<?php echo $QueryParm ?>"
												class="btn btn-danger">Close</a>

											<button type="submit" name='submit' class="btn btn-primary">Submit</button>
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


<Script>
	$('document').ready(function () {
		var arr = [];
		$('.checkdiv input').change(function () {
			if (this.checked) {
				arr.push(this.value);
			} else {
				arr.splice(arr.indexOf(this.value), 1);
			}
			$('#tnofproduct').val(arr + '');
		});

		$('#continuebtn').on('click', function () {

			$('form').submit();
		});

		$('#tnofproduct').click(function (e) {
			$('#myModal').modal('toggle');

		});
		$('#ar').click(function (e) {
			$('#myModal1').modal('toggle');

		});


		var ar = [];
		var type = [];
		$('.checkdiv1 input').change(function () {
			if (this.checked) {
				ar.push(this.value);
				type.push(this.id);
			} else {
				ar.splice(ar.indexOf(this.value), 1);
				type.splice(type.indexOf(this.value), 1);

			}
			$('#ar').val(ar + '');
			$('#type').val(type + '');

		});
	});
</script>
