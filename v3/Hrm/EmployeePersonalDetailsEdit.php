<?php

$Status = $_SESSION['Status'];

$PageTitle = "Employee Personal Details";
$PageTitleFolder = "HRM";
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
extract($_POST);
extract($_GET);

$EmpID = $_GET['EmpID'];

$SqlEmpDetails = "SELECT * FROM `HrmEmpPersonalDetails` WHERE EmpID='$EmpID'";
$StmtEmpDetails = $iconn->query($SqlEmpDetails);
$RowEmpDetails = $StmtEmpDetails->fetch_array();
$Count = mysqli_num_rows($StmtEmpDetails);
if (isset($_POST['submit'])) {

	if ($Count == 0) {

		$SqlNewEmployee = "INSERT INTO `HrmEmpPersonalDetails`(`EmpID`, `PhoneNumber`, `AltPhoneNumber`, `MaritalStatus`,
 `PresentAddress`, `PermanentAddress`, `DateOfBirth`, `AadharNumber`, `ESICNo`, `PANNo`) VALUES
 ('$EmpID','$PhoneNumber','$AltPhoneNumber','$MaritalStatus','$PresentAddress','$PermanentAddress','$DateOfBirth'
 ,'$AadharNumber','$ESICNo','$PANNo')";
	} else {
		$SqlNewEmployee = "UPDATE `HrmEmpPersonalDetails` SET `PhoneNumber`='$PhoneNumber',
		`AltPhoneNumber`='$AltPhoneNumber',`MaritalStatus`='$MaritalStatus',`PresentAddress`='$PresentAddress',`PermanentAddress`='$PermanentAddress',
		`DateOfBirth`='$DateOfBirth',`AadharNumber`='$AadharNumber',
		`ESICNo`='$ESICNo',`PANNo`='$PANNo' WHERE `EmpID`='$EmpID'";
	}


	$oldData = null;
	if ($Count != 0) {
		$oldData = legacyAuditFetchRowById('HrmEmpPersonalDetails', 'EmpID', $EmpID, 'Hrm');
	}

	if ($StmtNewEmployee = $iconn->query($SqlNewEmployee)) {
		if ($Count == 0) {
			legacyAuditLogInsertById('HrmEmpPersonalDetails', 'EmpID', $EmpID, $_POST, array(), 'Hrm');
		} else {
			legacyAuditLogUpdateById('HrmEmpPersonalDetails', 'EmpID', $EmpID, $oldData, $_POST, array(), 'Hrm');
		}
		$_SESSION['Status'] = 'Updated Employee Details Successfully';

		header("Location:EmployeePersonalDetails.php");
	} else {
		$_SESSION['Status'] = $iconn->error . "Unable To Update Employee Details";
		header("Location:EmployeePersonalDetails.php");
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


	<?php	include($IncludesPath . "PreLoader.php"); ?>

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

					<?php }





					?>

					<?php include($IncludesPath . "page-title.php"); ?>


					<div class="row">
						<div class="col-12">
							<div class="card">

								<form role="form" method="post">
									<div class="card-body">

										<div class="row">






											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Marital Status</label>

												<select class="form-control select2" name='MaritalStatus' id="MaritalStatus" required>
													<option value="">Select Marital Status </option>
													<option <?php if ($RowEmpDetails['MaritalStatus'] == "Married") { ?> selected="selected" <?php } ?> value="Married">Married</option>
													<option <?php if ($RowEmpDetails['MaritalStatus'] == "UnMarried") { ?> selected="selected" <?php } ?> value="UnMarried">UnMarried</option>
												</select>
											</div>




											<div class="form-group  col-lg-3">
												<label for="exampleInputEmail1">Phone Number</label>

												<input class="form-control" type="text" id="PhoneNumber" name="PhoneNumber" placeholder="Enter Phone Number" onchange="stringlength($(this).val());" value="<?php echo $RowEmpDetails['PhoneNumber']  ?>" required>
											</div>

											<div class="form-group  col-lg-3">
												<label for="exampleInputEmail1">Alternate Phone Number</label>

												<input class="form-control" type="text" id="AltPhoneNumber" name="AltPhoneNumber" placeholder="Enter Alternate Phone Number" maxlength="10" value="<?php echo $RowEmpDetails['AltPhoneNumber'] ?>">
											</div>







											<div class="form-group  col-lg-3">
												<label for="exampleInputEmail1">Present Address</label>

												<input class="form-control" type="text" placeholder="Enter Present Address" value="<?php echo $RowEmpDetails['PresentAddress'] ?>" id="PresentAddress" name="PresentAddress" required>
											</div>


											<div class="form-group  col-lg-3">
												<label for="exampleInputEmail1">Permanent Address</label>

												<input class="form-control" type="text" placeholder="Enter Permanent Address" value="<?php echo $RowEmpDetails['PermanentAddress'] ?>" id="PermanentAddress" name="PermanentAddress" required>
											</div>


											<div class="form-group  col-lg-3">
												<label for="exampleInputEmail1">DOB</label>

												<input class="form-control" type="date" placeholder="Enter Date Of Birth" value="<?php echo $RowEmpDetails['DateOfBirth'] ?>" id="DateOfBirth" name="DateOfBirth" required>
											</div>

											<div class="form-group  col-lg-3">
												<label for="exampleInputEmail1">Aadhar Number</label>

												<input class="form-control" type="text" placeholder="Enter Aadhar Number" value="<?php echo $RowEmpDetails['AadharNumber'] ?>" id="AadharNumber" name="AadharNumber" maxlength="12" required>
											</div>
											<div class="form-group  col-lg-3">
												<label for="exampleInputEmail1">ESIC Number</label>

												<input class="form-control" type="text" placeholder="Enter ESIC Number" value="<?php echo $RowEmpDetails['ESICNo'] ?>" id="ESICNo" name="ESICNo" maxlength="17" required>
											</div>

											<div class="form-group  col-lg-3">
												<label for="exampleInputEmail1">PAN Number</label>

												<input class="form-control" type="text" placeholder="Enter PAN Number" value="<?php echo $RowEmpDetails['PANNo'] ?>" id="PANNo" name="PANNo" maxlength="10" required>
											</div>




											<!-- end row -->
										</div>
										<div class="col-xs-12 box-footer" align="right">
											<a href="EmployeeList" class="btn btn-danger">Close</a>
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


<Script>
	function stringlength(PhoneNumber, NA, 10) {
		var field = PhoneNumber.value;
		var mnlen = NA;
		var mxlen = 10;

		if (field.length < = mxlen) {
			alert("Please enter Phone Number or Enter NA");
			return false;
		} else {

			return true;
		}
	}








	$("#PhoneNumber").keyup(function() {
		$("#PhoneNumber").val(this.value.match(/[0-9]*+/));
	});
	$("#AltPhoneNumber").keyup(function() {
		$("#AltPhoneNumber").val(this.value.match(/[0-9]*+/));
	});

	$("#AadharNumber").keyup(function() {
		$("#AadharNumber").val(this.value.match(/[0-9]*/));
	});
</script>
