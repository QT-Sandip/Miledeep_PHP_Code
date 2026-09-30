<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$PageTitleFolder = "Master";
$PageTitle = "Add Exporter";

$ID = isset($_GET['id']) ? $_GET['id'] : "";
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));

$SqlCountries = "SELECT * FROM `ScrmCountriesList`";
$StmtCountries = $iconn->query($SqlCountries);

 $SqlMaxExporters = "SELECT MaxExporters FROM `AdminConfigDetails`"; 
$StmtMaxExporters = $iconn->query($SqlMaxExporters);
$RowMaxExporters = $StmtMaxExporters->fetch_array();
$MaxExporters = $RowMaxExporters['MaxExporters'];

$sqlExporterCount = "SELECT * FROM `AdminExporterDetails` WHERE ExpType=1 ";
$StmtExporterCount = $iconn->query($sqlExporterCount);
 $ExporterCount = mysqli_num_rows($StmtExporterCount); 

if (isset($_GET['id'])) { // In Update Mode
	$sqlGetExporterDetails = "SELECT * FROM `AdminExporterDetails` WHERE ExpID = " . $ID . " LIMIT 1";
	$GetExporterDetails = $iconn->query($sqlGetExporterDetails);
	$results = $GetExporterDetails->fetch_array();
}



extract($_POST);
extract($_GET);

if (isset($_POST['submit'])) {
	$exporterType = "1";
	$exporterName = $_POST["exporterName"];
	$countryID = $_POST["countryID"];
	$address = $_POST["address"];
	$city = $_POST["city"];
	$zipCode = $_POST["zipCode"];
	$phone = $_POST["phone"];
	$fax = $_POST["fax"];
	$iec = $_POST["iec"];
	$apVat = $_POST["apVat"];
	$custAIG = $_POST["custAIG"];
	$pan = $_POST["pan"];
	$tan = $_POST["tan"];
	$gstIN = $_POST["gstIN"];
	//$processorBAPNo = $_POST["processorBAPNo"];
	$cinNo = $_POST["cinNo"];
	$invoiceCode = $_POST["invoiceCode"];
	$authSign = $_POST["authSign"];

	if ($ID == "") {  // New Exporter
		if($ExporterCount<$MaxExporters){
		$sqlQuery = "INSERT INTO `AdminExporterDetails`(`ExpType`, `ExpName`, `ExpCountry`, `ExpAddress`, `ExpCity`, `ExpZipCode`, `ExpPhoneNumber`, `ExpFax`, `ExpIeCode`, `ExpAPVAT`, 
					`ExpCustAIFNo`, `ExpPanNumber`, `ExpTax`, `ExpGstNo`, `ExpCINNO`, `InvShortCode`, `AuthSign`) VALUES 
				  	('$exporterType','$exporterName','$countryID','$address','$city','$zipCode','$phone','$fax','$iec','$apVat',
				  	'$custAIG','$pan','$tan','$gstIN','$cinNo','$invoiceCode', '$authSign')";
		$statment = $iconn->query($sqlQuery);
		if ($statment) {
			$insertedExporterId = $iconn->insert_id;
			legacyAuditLogInsertById('AdminExporterDetails', 'ExpID', $insertedExporterId, $_POST, array(
				'ReferenceNo' => $insertedExporterId,
				'Remarks' => 'Exporter created',
			), 'Sales');

			echo '<script language="javascript" type="text/javascript"> 
				alert("Exporter Posted Successfully");
				window.location = "ExporterDetails.php";
			</script>';
			// header('Location: AddExporter.php');
		}
	}

	else{
		echo '<script language="javascript" type="text/javascript"> 
                	alert("max Limit reached");
                	window.location = "ExporterDetails.php";
				</script>';
	}
	} else {  // Update the Exporter
		$existingExporter = legacyAuditFetchRowById('AdminExporterDetails', 'ExpID', $ID, 'Sales');
		$sqlQuery = "UPDATE `AdminExporterDetails` SET `ExpType`='$exporterType', `ExpName`='$exporterName', `ExpCountry`='$countryID', `ExpAddress`='$address',
					`ExpCity`='$city', `ExpZipCode`='$zipCode', `ExpPhoneNumber`='$phone', `ExpFax`='$fax', `ExpIeCode`='$iec', `ExpAPVAT`='$apVat', `ExpCustAIFNo`='$custAIG', 
					`ExpPanNumber`='$pan', `ExpTax`='$tan', `ExpGstNo`='$gstIN', `ExpCINNO`='$cinNo', `InvShortCode`='$invoiceCode', `AuthSign` = '$authSign'
					WHERE `ExpID`='$ID'";
		$statment = $iconn->query($sqlQuery);
		if ($statment) {
			legacyAuditLogUpdateById('AdminExporterDetails', 'ExpID', $ID, $existingExporter, $_POST, array(
				'ReferenceNo' => $ID,
				'Remarks' => 'Exporter updated',
			), 'Sales');
			$ArgumentsArray[PostedUserId] = $PostedUserId;
			$ArgumentsArray[PageUrl] = $PageUrl;
			$ArgumentsArray[Remarks] = $Remarks;
			$ArgumentsArray[ReferenceID] = $ID;
			postUserEditLog($ArgumentsArray);
			echo '<script language="javascript" type="text/javascript"> 
                	alert("Exporter Updated Successfully");
                	window.location = "ExporterDetails.php";
				</script>';
		}
	}

	//echo 	$sqlQuery; exit;

}

// check weather value exits or not 
function funPrintValue($key)
{
	echo (isset($key) && $key != "") ? $key : "";
}



?>


<!DOCTYPE html>
<html lang="en">

<head>

	<?php include($IncludesPath . "title-meta.php"); ?>
	<?php include($IncludesPath . "DbLinks.php"); ?>
	<?php include($IncludesPath . "head-css.php"); ?>
	<style>
		select.error+.select2-container,
		input.error,
		textarea.error {
			border: 1px solid red;
		}
	</style>

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

					<?php if ($Status != '') { 	//Show Alert  	
					?>
						<div class="alert alert-success alert-dismissible fade show" role="alert">
							<i class="mdi mdi-check-all mr-2"></i>
							<?php
							echo $Status;
							$_SESSION['Status'] = '';
							?>
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
					<?php } ?>

					<?php include($IncludesPath . "page-title.php"); ?>

					<div class="row">
						<div class="col-12">
							<div class="card">

								<form role="form" method="post" id="frmAddExporter">
									<div class="card-body">

										<div class="row">

											<div class="form-group col-sm-3">
												<label for="exporterName">Exporter Name<span style="color:red">*</span></label>
												<input required type="text" step="any" class="form-control" name='exporterName' id="exporterName" placeholder="Enter Exporter name" value="<?php funPrintValue($results["ExpName"]) ?>" />
											</div>

											<div class="form-group col-lg-3">
												<label for="countryID">Select Country<span style="color:red">*</span></label>
												<select required class="form-control select2" name="countryID" id="countryID">
													<option value=""> All </Option>
													<?php while ($RowCountries = $StmtCountries->fetch_array()) { ?>
														<option <?php if ($results['ExpCountry'] == $RowCountries['name']) { ?> selected="selected" <?php } ?> value="<?php echo $RowCountries['name']; ?>"><?php echo $RowCountries['name']; ?></option>
													<?php } ?>

													<select>
											</div>

											<div class="form-group col-sm-3">
												<label for="address">Address<span style="color:red">*</span></label>
												<textarea required type="text" step="any" class="form-control" name='address' id="address" placeholder="Enter Address"><?php funPrintValue($results["ExpAddress"]) ?></textarea>
											</div>

											<div class="form-group col-sm-3">
												<label for="city">City<span style="color:red">*</span></label>
												<input required type="text" step="any" class="form-control" name='city' id="city" placeholder="Enter City" value="<?php funPrintValue($results["ExpCity"]) ?>" />
											</div>

											<div class="form-group col-sm-3">
												<label for="zipCode">Zip Code</label>
												<input type="text" step="any" class="form-control" name='zipCode' id="zipCode" placeholder="Enter Zip Code" value="<?php funPrintValue($results["ExpZipCode"]) ?>" />
											</div>

											<div class="form-group col-sm-3">
												<label for="phone">Phone</label>
												<input type="text" step="any" class="form-control" name='phone' id="phone" placeholder="Enter Phone" value="<?php funPrintValue($results["ExpPhoneNumber"]) ?>" />
											</div>

											<div class="form-group col-sm-3">
												<label for="fax">Fax</label>
												<input type="text" class="form-control" name='fax' id="fax" placeholder="Enter Fax" value="<?php funPrintValue($results["ExpFax"]) ?>" />
											</div>

											<div class="form-group col-sm-3">
												<label for="iec">IEC</label>
												<input type="text" class="form-control" step="any" name='iec' id="iec" placeholder="Enter IEC" value="<?php funPrintValue($results["ExpIeCode"]) ?>" />
											</div>

											<div class="form-group col-sm-3">
												<label for="apVat">APVAT</label>
												<input type="text" step="any" class="form-control" name='apVat' id="apVat" placeholder="Enter APVAT" value="<?php funPrintValue($results["ExpAPVAT"]) ?>" />
											</div>
											<div class="form-group col-sm-3">
												<label for="custAIG">Cust AIG No</label>
												<input type="text" step="any" class="form-control" name='custAIG' id="custAIG" placeholder="Enter Cust AIG No" value="<?php funPrintValue($results["ExpCustAIFNo"]) ?>" />
											</div>

											<div class="form-group col-sm-3">
												<label for="pan">PAN</label>
												<input type="text" step="any" class="form-control" name='pan' id="pan" placeholder="Enter PAN no." value="<?php funPrintValue($results["ExpPanNumber"]) ?>" />
											</div>

											<div class="form-group col-sm-3">
												<label for="tan">TAX</label>
												<input type="text" step="any" class="form-control" name='tan' id="tan" placeholder="Enter TAX" value="<?php funPrintValue($results["ExpTax"]) ?>" />
											</div>

											<div class="form-group col-sm-3">
												<label for="gstIN">GSTIN</label>
												<input type="text" step="any" class="form-control" name='gstIN' id="gstIN" placeholder="Enter GSTIN" value="<?php funPrintValue($results["ExpGstNo"]) ?>" />
											</div>


											<div class="form-group col-sm-3">
												<label for="cinNo">CINNo</label>
												<input type="text" step="any" class="form-control" name='cinNo' id="cinNo" placeholder="Enter CINNo" value="<?php funPrintValue($results["ExpCINNO"]) ?>" />
											</div>

											<div class="form-group col-sm-3">
												<label for="invoiceCode">Invoice Code</label>
												<input type="text" step="any" class="form-control" name='invoiceCode' id="invoiceCode" placeholder="Enter Invoice Code" value="<?php funPrintValue($results["InvShortCode"]) ?>" />
											</div>
											<div class="form-group col-sm-3">
												<label for="invoiceCode">Auth Sign</label>
												<input type="text" step="any" class="form-control" name='authSign' id="authSign" placeholder="Enter Auth Sign" value="<?php funPrintValue($results["AuthSign"]) ?>" />
											</div>

										</div>

										<div class="col-xs-12 box-footer text-right">
											<a target="" href="ExporterDetails.php" class="btn btn-danger"> Close</a>
											<button type="submit" name='submit' class="btn btn-success">
												<?php
												if ($ID == '') {
													echo "Save";
												} else {
													echo "Update";
												}
												?>
											</button>
										</div>
									</div>
								</form>
								<!-- end col -->
							</div> <!-- end card -->
						</div> <!-- end col-->
					</div> <!-- end row -->

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

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>

	<script>
		jQuery.validator.addMethod('selectcheck', function(value) {
			return (value != '0' || value != '');
		}, "year required");
		jQuery.validator.addMethod("noSpace", function(value, element) {
			return value == '' || value.trim().length != 0;
		}, "No space please and don't leave it empty");
		$(document).ready(function() {
			$("#frmAddExporter").validate({
				onkeyup: false,
				onfocusout: false,
				invalidHandler: function(form, validator) {
					var errors = validator.numberOfInvalids();
					if (errors) {
						var firstInvalidElement = $(validator.errorList[0].element);
						//$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
						firstInvalidElement.focus();
					}
				},
				ignore: "",
				rules: {
					exporterName: {
						required: true,
						noSpace: true
					},
					countryID: {
						selectcheck: true
					},
					address: {
						required: true,
						noSpace: true
					},
					city: {
						required: true,
						noSpace: true
					},
					// zipCode: { required: true, noSpace: true },
					// phone: { required: true, noSpace: true, number: true },
					// fax: { required: true, noSpace: true },
					// iec: { required: true, noSpace: true },
					// apVat: { required: true, noSpace: true },
					// custAIG: { required: true, noSpace: true },
					// pan: { required: true, noSpace: true },
					// tan: { required: true, noSpace: true },
					// gstIN: { required: true, noSpace: true },
					// cinNo: { required: true, noSpace: true },
					// invoiceCode: { required: true, noSpace: true },
					// authSign: { required: true, noSpace: true }
				},
				errorPlacement: function() {
					return false; // suppresses error message text
				},
				submitHandler: function() {
					return true;
				}
			});
		});
	</script>


</body>

</html>
