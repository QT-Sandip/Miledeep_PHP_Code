<?php

$Status = $_SESSION['Status'];

$PageTitle = "Employee Bank Details";
$PageTitleFolder = "HRM";
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
extract($_POST);
extract($_GET);

$EmpID = $_GET['EmpID'];

$SqlAdminBank = "SELECT * FROM `AdminBankDetails`";
$StmtAdminBank = $iconn->query($SqlAdminBank);

$SqlEmpBankDetails = "SELECT * FROM `HrmEmpBankAccountDetails` WHERE EmpID='$EmpID'";
$StmtEmpBankDetails = $iconn->query($SqlEmpBankDetails);
$RowEmpBankDetails = $StmtEmpBankDetails->fetch_array();
$Count = mysqli_num_rows($StmtEmpBankDetails);
if (isset($_POST['submit'])) {

	if ($Count == 0) {

		$SqlNewEmployee = "INSERT INTO `HrmEmpBankAccountDetails`(`EmpID`, `AccNumber`, `BankID`, `IFSC`, `AccType`, `DateOfCreation`) 
VALUES('$EmpID','$AccNumber','$BankID','$IFSC','$AccType','$DateOfCreation') ";
	} else {
		$SqlNewEmployee = "UPDATE `HrmEmpBankAccountDetails` SET 
	`AccNumber`='$AccNumber',`BankID`='$BankID',`IFSC`='$IFSC',`AccType`='$AccType',`DateOfCreation`='$DateOfCreation'
	WHERE  `EmpID`='$EmpID'";
	}


	$oldData = null;
	if ($Count != 0) {
		$oldData = legacyAuditFetchRowById('HrmEmpBankAccountDetails', 'EmpID', $EmpID, 'Hrm');
	}

	if ($StmtNewEmployee = $iconn->query($SqlNewEmployee)) {
		if ($Count == 0) {
			legacyAuditLogInsertById('HrmEmpBankAccountDetails', 'EmpID', $EmpID, $_POST, array(), 'Hrm');
		} else {
			legacyAuditLogUpdateById('HrmEmpBankAccountDetails', 'EmpID', $EmpID, $oldData, $_POST, array(), 'Hrm');
		}
		$_SESSION['Status'] = 'Updated Bank Details Successfully';

		header("Location:EmployeeBankDetails.php");
	} else {
		$_SESSION['Status'] = $iconn->error . "Unable To Update Bank Details";
		header("Location:EmployeeBankDetails.php");
	}
}




?>




<!DOCTYPE html>


<html lang="en">

<head>

	<?php include($IncludesPath . "title-meta.php"); ?>
	<?php include("Includes/DbLinks.php"); ?>


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

											<div class="form-group">
												<label for="exampleInputEmail1">Bank Name</label>

												<select class="form-control select2" name="BankID" id="BankID">
													<option value="">Select Bank Name</option>

													<?php
													if ($EmpID == '') {

														while ($RowAdminBank = $StmtAdminBank->fetch_array()) { ?>

															<option value="<?php echo $RowAdminBank['BankID']; ?>">
																<?php echo $RowAdminBank['BankName']; ?></option>
														<?php    }
													} else {
														while ($RowAdminBank = $StmtAdminBank->fetch_array()) { ?>

															<option <?php if ($RowEmpBankDetails['BankID'] == $RowAdminBank['BankID']) { ?> selected="selected" <?php } ?> value="<?php echo $RowAdminBank['BankID']; ?>">
																<?php echo $RowAdminBank['BankName']; ?></option>
													<?php    }
													} ?>



												</select>
											</div>


											<div class="form-group  col-lg-3">
												<label for="exampleInputEmail1">Account Number</label>

												<input class="form-control" type="text" maxlength="18" id="AccNumber" name="AccNumber" placeholder="Enter Account Number" value="<?php echo $RowEmpBankDetails['AccNumber']  ?>" required>
											</div>

											<div class="form-group  col-lg-3">
												<label for="exampleInputEmail1">IFSC</label>

												<input class="form-control" type="text" maxlength="11" onkeyup="this.value = this.value.toUpperCase();" id="IFSC" name="IFSC" placeholder="Enter IFSC" value="<?php echo $RowEmpBankDetails['IFSC'] ?>">
											</div>







											<div class="form-group  col-lg-3">
												<label for="exampleInputEmail1">Account Type</label>
												<input class="form-control" type="text" placeholder="Enter Account Type" value="<?php echo $RowEmpBankDetails['AccType'] ?>" id="AccType" name="AccType" required>
											</div>


											<div class="form-group  col-lg-3">
												<label for="exampleInputEmail1">Date Of Creation</label>
												<input class="form-control" type="date" placeholder="" value="<?php echo $RowEmpBankDetails['DateOfCreation'] ?>" id="DateOfCreation" name="DateOfCreation" required>
											</div>





											<!-- end row -->
										</div>
										<div class="col-xs-12 box-footer" align="right">
											<?php if ($Count == 0) { ?>
												<button type="submit" name='submit' class="btn btn-primary waves-effect waves-light">
													<i class='ri-add-fill align-middle ml-2'>
													</i>
													Add</button>
											<?php } else {
											?>

												<button type="submit" name='submit' class="btn btn-primary waves-effect waves-light">
													<i class='fa fa-edit align-middle ml-2'>
													</i>
													Update</button><?php } ?>
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
	<?php include("Includes/NewScripts.php"); ?>



</body>

</html>

<script>
	$("#AccNumber").keyup(function() {
		$("#AccNumber").val(this.value.match(/[0-9]*/));
	});

	$("#IFSC").keyup(function() {
		$("#IFSC").val(this.value.match(/[0-9,A-Z]*/));
	});
</script>
