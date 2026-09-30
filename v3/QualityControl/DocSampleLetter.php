<?php

//ini_set( 'display_errors', 1 );

session_start();

$QueryParms = $_GET['Q'];
$QueryParm = $_GET['Q'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$ClientConfigData = $_SESSION['ClientConfigData'];
$ClientConfigArray = json_decode(json_encode($ClientConfigData), true);
$EnableQcLabSamplingType = !empty($ClientConfigArray['EnableQcLabSamplingType']);
$IsShowQcAddOnFields = $ClientConfigData->IsShowQcAddOnFields;  // uncomment this line after auth-login.php converted to API modal
// $IsShowQcAddOnFields = $ClientConfigData['IsShowQcAddOnFields']; // remove this line after auth-login.php converted to API modal


$PoID = $_GET['PoID'];
$month = date('m');
$year = date('Y');

if ($month >= 4) {
	$LastDate = $year . '-03-31';
	$FinancialYear = $year . '-' . substr($year + 1, -2);
} else {
	$FinancialYear = ($year - 1) . '-' . substr($year, -2);
	$LastDate = ($year - 1) . '-03-31';
}

$CurrentYear = date("Y");
$CurrentMonth = date('n');
if ($CurrentMonth < 3) {
	$NextYear = $CurrentYear;
	$CurrentYear = $CurrentYear - 1;

} else {

	$NextYear = $CurrentYear + 1;
}
//$LastDate = $CurrentYear . '-03-31';


$YearNumber = substr($NextYear, -2);
//$FinancialYear = $CurrentYear . '-' . $YearNumber;


$SqlPoData = "SELECT * FROM `ScrmPoData` where PoID='$PoID'";
$StmtPoData = $iconn->query($SqlPoData);
$RowPoData = $StmtPoData->fetch_array();


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


$SqlReferenceNumber = "Select count(*) as COUNT from `QcSamplingLettersExt` where Date > '$LastDate'  ";
$StmtReferenceNumber = $iconn->query($SqlReferenceNumber);
$ReportReferenceNumber = $StmtReferenceNumber->fetch_array();
$Count = $ReportReferenceNumber['COUNT'];
$Number = $Count + 1;
$ReferenceNumber = $ShortName . '/' . $Number . '/' . $FinancialYear;

$LabSamplingMasterType = 0;
if ($EnableQcLabSamplingType && $PoID != '') {
	$LabSamplingTypeStmt = $iconn->query("SELECT type FROM QcPoLabSamplingType WHERE poid='$PoID' LIMIT 1");
	if ($LabSamplingTypeStmt && $LabSamplingTypeRow = $LabSamplingTypeStmt->fetch_assoc()) {
		$LabSamplingMasterType = (int) $LabSamplingTypeRow['type'];
	}
}


$PageTitle = "External Labs Sampling Letter - " . $RowPoData['PoNo'];

$PageTitleFolder = "Quality Control";

extract($_POST);
extract($_GET);

if (isset($_POST['submit'])) {
	if ($PoID == '') {
		$PoID = 0;
	}

	$SamplingType = $EnableQcLabSamplingType ? intval(isset($_POST['SamplingType']) ? $_POST['SamplingType'] : 0) : 2;
	if (!in_array($SamplingType, array(1, 2))) {
		$SamplingType = ($LabSamplingMasterType == 1 || $LabSamplingMasterType == 2) ? $LabSamplingMasterType : 2;
	}

	$doa = date("d-m-Y", strtotime($doa));
	$doi = date("d-m-Y", strtotime($doi));

	$SqlReportCount = "Select count(*) as COUNT from `QcSamplingLettersExt` where Date > '$LastDate'  ";
	$StmtReportCount = $iconn->query($SqlReportCount);
	$ReportCount = $StmtReportCount->fetch_array();
	$Count = $ReportCount['COUNT'];

	$Number = $Count + 1;
	//echo $SqlReportCount; 
	// $ReferenceNumber='SME/'.$Number.'/'.$FinancialYear;
	//  $ReferenceNumber=$ShortName.'/'.$Number.'/'.$FinancialYear;	
	//exit;
	$sql_org_insert = "INSERT INTO `QcSamplingLettersExt`
	(`SampleNumber`,`SampleLocation`, `TypeProduct`, `SampleDrawnBy`,`AnalysisReq`, 
	`TestingLab`, `Date`, `VerifiedBy`, `InvID`, `ExtraColumnName`, `ExtraColumnValue`, `Amount`, `LabInvoiceNumber`, `InvoicercvedBY`, `ReportNumber`,`ReportDate`,`InvoiceNumber`,`InvoiceDate`,`Status`,`InvAmount`,`ApprovedByID`
	,PreparedBy,AccountantName,CompletedDate,SamplingType
	)
	VALUES ('$ReferenceNumber','$SampleLocation','$TypeProduct',
	'$SampleDrawnBy','$ar','$TestingLab','$DATE','$VerifiedBy','$PoID','$ExtraColumnName','$ExtraColumnValue','$Amount','$LabInvoiceNumber','$InvoicercvedBY','$ReportNumber','$ReportDate','$InvoiceNumber','$InvoiceDate','$Status','$InvoiceAmount','$ApprovedBy'
	,'$PreparedBy','$AccountantName','$CompletedDate','$SamplingType'
	)";

	$sql_org_insert;
	$stmt1 = $iconn->query($sql_org_insert);
	$iid = $iconn->insert_id;
	legacyAuditLogInsertById('QcSamplingLettersExt', 'SMPLID', $iid, $_POST, ['Remarks' => 'Created External Sampling Letter'], 'QualityControl');
	$ar = explode(",", $ar);
	$type = explode(",", $type);
	$length = count($ar);

	$length1 = count($type);

	if ($stmt1) {

		for ($i = 0; $i < $length; $i++) {
			for ($k = 0; $k < $length1; $k++) {

				if ($k == $i) {
					$sql_org_insert1 = "INSERT INTO `$TblQcEiaAnalysisRequired`
 (`ReportId`, `Type`, `TestName`) VALUES ('$iid','$type[$k]','$ar[$i]')";
					// $sql_org_insert1;

					$stmt1 = $iconn->query($sql_org_insert1);
					$eaSampleId = $iconn->insert_id;
					legacyAuditLogInsertById('QcEiaAnalysisRequired', 'ID', $eaSampleId, $_POST, ['Remarks' => 'Created EIA Analysis Required'], 'QualityControl');
				}
			}
		}

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
													<option value="">Select</option>
													<?php while ($RowEiaLabs = $StmtLabName->fetch_array()) { ?>
														<option value="<?php echo $RowEiaLabs['LabName']; ?>">
															<?php echo $RowEiaLabs['LabName'] . ' - ' . $RowEiaLabs['Address']; ?>
														</option>

													<?php } ?>
													<select>
											</div>
											<?php if ($EnableQcLabSamplingType) {
												$SamplingTypeValue = ($LabSamplingMasterType == 1 || $LabSamplingMasterType == 2) ? $LabSamplingMasterType : '';
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
													<option>RAW</option>
													<option>COOKED</option>
													<option>BOTH</option>
													<select>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Sample Location</label>
												<input type="text" class="form-control" name='SampleLocation'
													id="SampleLocation" placeholder="Enter Sample Location"
													value="<?php echo $RowPoData['SAMPLE_LOCATION'] ?>" required>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Reference Date</label>
												<input type="date" value="<?php echo date("Y-m-d"); ?>"
													class="form-control" name='DATE' id="DATE"
													value="<?php echo $RowPoData[''] ?>"
													max="<?php echo date('Y-m-d'); ?>" required>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Name of the Representation</label>

												<select class="form-control select2" name="SampleDrawnBy"
													id="SampleDrawnBy">
													<option>Select</option>
													<?php while ($RowRepresentationDescription = $StmtLabRepresentationDescription->fetch_array()) { ?>
														<option
															value="<?php echo $RowRepresentationDescription['RepresentationDescription']; ?>">
															<?php echo $RowRepresentationDescription['RepresentationDescription']; ?>
														</option>

													<?php } ?>

												</select>

											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Verified By </label>


												<select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
													<option value="">Select</option>
													<?php
													foreach ($LabUsersArr as $RowLabUsers) {
														//while ($RowLabUsers = $StmtLab->fetch_array()) { ?>
														<option value="<?php echo $RowLabUsers['LbuName'] ?>">
															<?php echo $RowLabUsers['LbuName'] ?>
														</option>

													<?php } ?>

												</select>
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputPassword1">Analysis Required</label>
												<input type="text" autocomplete="off" class="form-control" name="ar"
													id="ar" placeholder="Click To select Required Analysis" required>
												<input type="hidden" class="form-control" name="type" id="type"
													required>

											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Test Report No / Reference No</label>
												<input type="text" class="form-control" name='ReferenceNumber'
													id="ReferenceNumber" placeholder="Enter Name of Reference Number"
													value="<?php echo $ReferenceNumber ?>" required>
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Extra Column Name</label>
												<input type="text" class="form-control" name='ExtraColumnName'
													id="ExtraColumnName" placeholder="Enter Extra Column Name"
													value="<?php echo $ExtraColumnName ?>">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputEmail1">Extra Column Value</label>
												<input type="text" class="form-control" name='ExtraColumnValue'
													id="ExtraColumnValue" placeholder="Enter Extra Column Value"
													value="<?php echo $ExtraColumnValue ?>">
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
														//while ($RowLabUsers = $StmtLab->fetch_array()) { ?>
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
													<label for="ReportNumber">Report No</label>
													<input type="text" class="form-control" name='ReportNumber'
														id="ReportNumber" placeholder="Enter Report Number"
														value="<?php echo $ReportNumber ?>">
												</div>

												<div class="form-group col-sm-3">
													<label for="ReportDate">Report Date</label>
													<input type="Date" class="form-control" name='ReportDate'
														id="ReportDate" placeholder="Enter Report Date"
														value="<?php echo $ReportDate ?>">
												</div>

												<div class="form-group col-sm-3">
													<label for="InvoiceNumber">Invoice No</label>
													<input type="text" class="form-control" name='InvoiceNumber'
														id="InvoiceNumber" placeholder="Enter Invoice Number"
														value="<?php echo $InvoiceNo ?>">
												</div>

												<div class="form-group col-sm-3">
													<label for="InvoiceDate">Invoice Date</label>
													<input type="Date" class="form-control" name='InvoiceDate'
														id="InvoiceDate" placeholder="Enter Invoice Date"
														value="<?php echo $InvoiceDate ?>">
												</div>

												<div class="form-group col-sm-3">
													<label for="InvoiceAmount">Invoice Amount</label>
													<input type="text" class="form-control" name='InvoiceAmount'
														id="InvoiceAmount" placeholder="Enter Invoice Amount"
														value="<?php echo $InvoiceAmount ?>">
												</div>

												<div class="form-group col-sm-3">
													<label for="Status">Status</label>
													<select class="form-control select2" name="Status" id="Status" required>
														<option>Pass</option>
														<option>Fail</option>
														<select>
												</div>

												<div class="form-group col-sm-3">
													<label for="exampleInputEmail1">Approved By </label>

													<select class="form-control select2" name="ApprovedBy" id="ApprovedBy">
														<option value="">Select</option>
														<?php
														foreach ($LabUsersArr as $RowLabUsers) {
															//while ($RowLabUsers = $StmtLab1->fetch_array()) { ?>
															<option value="<?php echo $RowLabUsers['LUID'] ?>">
																<?php echo $RowLabUsers['LbuName'] ?>
															</option>

														<?php } ?>

													</select>
												</div>

											</div>
										</div>
									<?php } ?>
									<div class="col-xs-12 box-footer" align="right">
										<a href="DocAddReports.php?Q=<?php echo $QueryParm ?>"
											class="btn btn-danger">Close</a>

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
<Script>

	$('document').ready(function () {
		var arr = [];
		$('.checkdiv input').change(function () {
			if (this.checked) {
				arr.push(this.value);
			}
			else {
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
			}
			else {
				ar.splice(ar.indexOf(this.value), 1);
				type.splice(type.indexOf(this.value), 1);

			}
			$('#ar').val(ar + '');
			$('#type').val(type + '');

		});
	});
</script>
