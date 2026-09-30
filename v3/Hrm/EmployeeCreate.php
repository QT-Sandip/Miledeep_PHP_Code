<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


$Status = $_SESSION['Status'];


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$SqlShiftDetails = "Select * from HrmShiftDetails";
$StmtShiftDetails = $iconn->query($SqlShiftDetails);
extract($_POST);
extract($_GET);
$EmpID = $_GET['EmpID'];

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$isEmpTypesEnabled = $ArrayClientConfigData['SplitStaffAndWorkerSalarySheets'];

$SqlAdminConfig = "SELECT MaxEmpoyees,
(Select count(*) from HrmEmployeeDetails where EmpStatus='1') as PresentEmployees 
FROM `AdminConfigDetails`";

// $SqlAdminConfig = "SELECT MaxEmpoyees,
// (Select count(*) from HrmEmployeeDetails ) as PresentEmployees 
// FROM `AdminConfigDetails`";
$StmtAdminConfig = $iconn->query($SqlAdminConfig);

//ECHO $SqlAdminConfig; EXIT;
$RowAdminConfig = $StmtAdminConfig->fetch_array();

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$IsEmpCategoryEnabled  = $ArrayClientConfigData['SplitStaffAndWorkerSalarySheets'];

$MaxEmpoyees = $RowAdminConfig['MaxEmpoyees'];
$PresentEmployees = $RowAdminConfig['PresentEmployees'];
if ($PresentEmployees >= $MaxEmpoyees) {
	$MaxLimit = true;
} else {
	$MaxLimit = false;
}



$SqlEmployeeDetails = "Select * from HrmEmployeeDetails where EmpID='$EmpID'";
$StmtEmployeeDetails = $iconn->query($SqlEmployeeDetails);
$RowEmployeeDetails = $StmtEmployeeDetails->fetch_array();
$Count = mysqli_num_rows($StmtEmployeeDetails);

if ($Count == 0) {
	$PageTitle = "New Employee";
} else {
	$PageTitle = "Edit Employee";
}


if ($MaxLimit) {
	$PageTitle .= " (Max Limit Reached )";
} else if (($MaxEmpoyees - $PresentEmployees) < 20) {
	$PageTitle .= " ( " . ($PresentEmployees . "/" . $MaxEmpoyees) . " Employee )";
}
$PageTitleFolder = "HRM";

$SqlWorkerCategory = "SELECT * FROM `EmpWorkerSubCategories`";
$SqlWorkerCategoryResult = $iconn->query($SqlWorkerCategory);

$SqlBatch = "SELECT * FROM `EmpWorkerBatchMaster`";
$SqlBatchResult = $iconn->query($SqlBatch);

$SqlWorkerSection = "SELECT * FROM `EmpWorkerSectionsMaster`";
$SqlWorkerSectionRes = $iconn->query($SqlWorkerSection);

$SqlWorkerVillage = "SELECT * FROM `EmpWorkerVillageMaster`";
$SqlWorkerVillageRes = $iconn->query($SqlWorkerVillage);

if (isset($_POST['submit'])) {

	$SqlEmployeeDetails = "Select * from HrmEmployeeDetails where BioMetricID='$BioMetricID'";
	$StmtEmployeeDetails = $iconn->query($SqlEmployeeDetails);
	$BioCount = mysqli_num_rows($StmtEmployeeDetails);
	//if($BioCount==0){
	$DateOfCreation = date('Y-m-d');
	if ($Count == 0) {
		if ($BioCount == 0) {
			$SqlNewEmployee = "INSERT INTO `HrmEmployeeDetails`
( `BioMetricID`, `DeptID`, `SubDeptID`, `EmpName`, `EmpGender`, 
`EmpCategory`,`EmpSubCategory`, `DesigID`, `ShiftID`, `DateOfCreation`,
`JoiningDate`, `EmpStatus`,`BranchID`,`ShiftStartFrom`,`ShiftsAvailable`,`ContractorID`,`WorkerSubCategory`,`BatchID`,`SectionID`,`VillageID`) 
VALUES ('$BioMetricID','$DeptID','$SubDeptID','$EmpName','$EmpGender','$EmpCategory',
'$EmpSubCategory','$DesigID','$ShiftID','$DateOfCreation','$JoiningDate','1','$BranchID',
'$ShiftStartFrom','$ShiftsAvailable','$ContractorID','$WorkerSubCategory','$BatchID','$SectionID','$VillageID')";
			if ($StmtNewEmployee = $iconn->query($SqlNewEmployee)) {
				$lastInsertedId = $iconn->insert_id; 
				legacyAuditLogInsertById('HrmEmployeeDetails', 'EmpID', $lastInsertedId, $_POST, array(), 'Hrm');
				$_SESSION['Status'] = 'Employee added successfully. Please add Salary Details';
				if($EmpCategory == "Staff" ){
					echo "
					    <script type='text/javascript'>
					        var answer = confirm('Employee added successfully. Do you want to add bank details now?');
					        if (answer) {
					            window.location.href = 'employee-bank-details-create.php?EmpID=$lastInsertedId';
					        } else {
					            window.location.href = 'EmployeeList.php';
					        }
					    </script>
					    ";
				}else{
				    echo "
				    <script type='text/javascript'>
				        var answer = confirm('Employee added successfully. Do you want to add salary details now?');
				        if (answer) {
				            window.location.href = 'EmployeeSalaryDetailsEdit?EmpID=$lastInsertedId';
				        } else {
				            window.location.href = 'EmployeeList.php';
				        }
				    </script>
				    ";
				}
			} else {
				$_SESSION['Status'] = $iconn->error . "Unable To Create Employee";
				header("Location:EmployeeCreate.php");
			}
		} else {
			$message = "Biometric Id Alreay Exists";
			echo "<script type='text/javascript'>alert('$message');</script>";
		}
	} else {
		$SqlNewEmployee = "UPDATE `HrmEmployeeDetails` SET 
    `BioMetricID`='$BioMetricID',`DeptID`='$DeptID',
	`SubDeptID`='$SubDeptID',`EmpName`='$EmpName',
	`EmpGender`='$EmpGender',`EmpCategory`='$EmpCategory',
	`EmpSubCategory`='$EmpSubCategory',`DesigID`='$DesigID',
	`ShiftID`='$ShiftID',`JoiningDate`='$JoiningDate',
	`BatchID`='$BatchID',
	`SectionID`='$SectionID',
	`VillageID`='$VillageID',
	`ShiftStartFrom`='$ShiftStartFrom',
	`ShiftsAvailable`='$ShiftsAvailable',
	`ContractorID`='$ContractorID',
	`WorkerSubCategory`='$WorkerSubCategory'
	WHERE EmpID='$EmpID'";
		//}
		//ECHO $SqlNewEmployee;
		//EXIT;
		$oldEmployeeData = legacyAuditFetchRowById('HrmEmployeeDetails', 'EmpID', $EmpID, 'Hrm');

		if ($StmtNewEmployee = $iconn->query($SqlNewEmployee)) {
			legacyAuditLogUpdateById('HrmEmployeeDetails', 'EmpID', $EmpID, $oldEmployeeData, $_POST, array(), 'Hrm');
			$_SESSION['Status'] = 'Employee Updated successfully';
			header("Location:EmployeeList.php");
		} else {
			$_SESSION['Status'] = $iconn->error . "Unable To Update Employee";
			header("Location:EmployeeList.php");
		}
	}
}

$SqlShiftList = "SELECT * FROM HrmShiftDetails where ShiftName!='General' Order by ShiftName";
$stmtShiftList = $iconn->query($SqlShiftList);
$SqlShiftAvailable = "SELECT * FROM HrmShiftDetails where ShiftName!='General' Order by ShiftName";
$stmtShiftAvailable = $iconn->query($SqlShiftAvailable);
?>




<!DOCTYPE html>


<html lang="en">

<head>

	<?php include($IncludesPath . "title-meta.php"); ?>
	<?php include("Includes/DbLinks.php"); ?>
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


					<div class="row">
						<div class="col-12">
							<div class="card">

								<form role="form" method="post">
									<div class="card-body">

										<div class="row">

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Branch</label>
												<select class="form-control select2" id="BranchID" name="BranchID">
													<option value="">Select Branch</option>
													<?php
													$SqlBranches = "SELECT * FROM HrmBranchMaster Order by BranchID";
													$StmtBranches = $iconn->query($SqlBranches);
													while ($BranchesRow = $StmtBranches->fetch_array()) { ?>
														<option <?php if ($RowEmployeeDetails['BranchID'] == $BranchesRow['BranchID']) { ?>selected="selected" <?php } ?> value="<?php echo $BranchesRow['BranchID']; ?>">
															<?php echo $BranchesRow['BranchName'].'('.$BranchesRow['BranchCode'].')'; ?>
															<!-- <?php echo $BranchesRow['BranchName']; ?> -->
														</option>

													<?php } ?>
												</select>
											</div>



											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Department</label>

												<select class="form-control select2" id="DeptID" name="DeptID" required>
													<option value="">Select Department</option>
													<?php
													$SqlDeptList = "SELECT * FROM HrmDepartments Order by DepartmentName";
													$StmtDept = $iconn->query($SqlDeptList);
													if ($EmpID == '') {
														while ($DeptRow = $StmtDept->fetch_array()) { ?>
															<option value="<?php echo $DeptRow['DeptID']; ?>">
																<?php echo $DeptRow['DepartmentName']; ?>
															</option>

														<?php }
													} else {

														while ($DeptRow = $StmtDept->fetch_array()) { ?>

															<option <?php if ($RowEmployeeDetails['DeptID'] == $DeptRow['DeptID']) { ?>selected="selected" <?php } ?> value="<?php echo $DeptRow['DeptID']; ?>">
																<?php echo $DeptRow['DepartmentName']; ?>
															</option>

													<?php }
													} ?>
												</select>
											</div>



											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Sub Department</label>
												<select class="form-control select2" id="SubDeptID" name="SubDeptID">
													<option value="">Select Sub Department</option>
													<?php if ($EmpID != '') {
														$SqlDeptList = "SELECT * FROM HrmSubDepartments Order by SubDeptName  ";
														$StmtDept = $iconn->query($SqlDeptList);
														while ($DeptRow = $StmtDept->fetch_array()) { ?>
															<option <?php if ($RowEmployeeDetails['SubDeptID'] == $DeptRow['SubDeptID']) { ?>selected="selected" <?php } ?> value="<?php echo $DeptRow['SubDeptID']; ?>">
																<?php echo $DeptRow['SubDeptName']; ?>
															</option>

													<?php }
													} ?>
												</select>
											</div>

											<div class="form-group  col-lg-3">
												<label for="exampleInputEmail1">Biometric Id</label>
												<input class="form-control" type="text" id="BioMetricID" name="BioMetricID" placeholder="Biometric Id" maxlength="4" value="<?php echo $RowEmployeeDetails['BioMetricID'] ?>" required>
											</div>

											<div class="form-group  col-lg-3">
												<label for="exampleInputEmail1">Employee Name</label>
												<input class="form-control" type="search" id="EmpName" name="EmpName" placeholder="Employee Name" value="<?php echo $RowEmployeeDetails['EmpName'] ?>" required>
											</div>
											<?php 
											if(!$isEmpTypesEnabled){
												?>
													<div class="form-group col-lg-3">
														<label for="exampleInputEmail1">Contractor Name</label>
														<select class="form-control select2" id="ContractorID" name="ContractorID">
															<option value="">Select Contractor</option>
															<?php
															$SqlContractorList = "SELECT * FROM HrmContractorList Order by ContractorID";
															$StmtContractor = $iconn->query($SqlContractorList);
															while ($DeptRow = $StmtContractor->fetch_array()) { ?>
																<option <?php

																		if ($RowEmployeeDetails['ContractorID'] == $DeptRow['ContractorID']) { ?>selected="selected" <?php } ?> value="<?php echo $DeptRow['ContractorID']; ?>">
																	<?php echo $DeptRow['ContractorName']; ?>
																</option>

															<?php } ?>
														</select>
													</div>
												<?php
											}
											?>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Gender</label>
												<select class="form-control select2" name='EmpGender' id="EmpGender">
													<option value="">Select Gender </option>
													<option <?php if ($RowEmployeeDetails['EmpGender'] == "Male") { ?>selected="selected" <?php } ?> value="Male">Male</option>
													<option <?php if ($RowEmployeeDetails['EmpGender'] == "Female") { ?>selected="selected" <?php } ?> value="Female">Female</option>
													<option <?php if ($RowEmployeeDetails['EmpGender'] == "Others") { ?>selected="selected" <?php } ?> value="Others">Others</option>
												</select>
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Category</label>
												<select class="form-control select2" name='EmpCategory' id="EmpCategory" required>
													<option value="">Select Category </option>
													<option <?php if ($RowEmployeeDetails['EmpCategory'] == "Staff") { ?>selected="selected" <?php } ?> value="Staff">Staff</option>
													<option <?php if ($RowEmployeeDetails['EmpCategory'] == "Worker") { ?>selected="selected" <?php } ?> value="Worker">Worker</option>
												</select>
											</div>

                                            <?php 
                                            if($IsEmpCategoryEnabled){
                                            ?>
											<div class="form-group col-lg-3" id="worker-type-select">
												<label for="exampleInputEmail1">Worker Category</label>
												<select class="form-control select2" name='WorkerSubCategory' id="WorkerSubCategory">
													<option value="">Select SubCategory </option>
														<?php while ($RowWorkerType = $SqlWorkerCategoryResult->fetch_array()) {
														?>	
 															<option <?php if ($RowWorkerType['CategoryID'] == $RowEmployeeDetails['WorkerSubCategory']) { ?>selected="selected" <?php } ?> value="<?php echo $RowWorkerType['CategoryID']; ?>">
  																<?php echo $RowWorkerType['CategoryName']; ?>
  															</option>

  													<?php } ?>
												</select>
											</div>
                                            <?php    
                                            }
                                            ?>
                                            <?php 
                                            if($IsEmpCategoryEnabled){
                                            ?>
											<div class="form-group col-lg-3" id="worker-village-select">
												<label for="exampleInputEmail1">Worker Village</label>
												<select class="form-control select2" name='VillageID' id="VillageID">
													<option value="">Select SubCategory </option>
														<?php while ($RowWorkerVillage = $SqlWorkerVillageRes->fetch_array()) {
														?>	
 															<option <?php if ($RowWorkerVillage['VID'] == $RowEmployeeDetails['VillageID']) { ?>selected="selected" <?php } ?> value="<?php echo $RowWorkerVillage['VID']; ?>">
  																<?php echo $RowWorkerVillage['VillageName']; ?>
  															</option>

  													<?php } ?>
												</select>
											</div>
                                            <?php    
                                            }
                                            ?>

											<div class="form-group col-lg-3" id="worker-batch-select">
												<label for="exampleInputEmail1">Batch Name</label>
												<select class="form-control select2" name='BatchID' id="BatchID">
													<option value="">Select Batch Name </option>
														<?php while ($RowBatch = $SqlBatchResult->fetch_array()) {
														?>	
 															<option <?php if ($RowBatch['BatchID'] == $RowEmployeeDetails['BatchID']) { ?>selected="selected" <?php } ?> value="<?php echo $RowBatch['BatchID']; ?>">
  																<?php echo $RowBatch['BatchName']; ?>
  															</option>

  													<?php } ?>
												</select>
											</div>

											<div class="form-group col-lg-3" id="worker-section-select">
												<label for="exampleInputEmail1">Section Name</label>
												<select class="form-control select2" name='SectionID' id="SectionID">
													<option value="">Select Section Name </option>
														<?php while ($RowSection = $SqlWorkerSectionRes->fetch_array()) {
														?>	
 															<option <?php if ($RowSection['SectionID'] == $RowEmployeeDetails['SectionID']) { ?>selected="selected" <?php } ?> value="<?php echo $RowSection['SectionID']; ?>">
  																<?php echo $RowSection['SectionName']; ?>
  															</option>

  													<?php } ?>
												</select>
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Sub Category </label>
												<select class="form-control select2" name='EmpSubCategory' id="EmpSubCategory">
													<option value="">Select SubCategory </option>
													<option <?php if ($RowEmployeeDetails['EmpSubCategory'] == "Regular") { ?>selected="selected" <?php } ?> value="Regular">Regular
													</option>
													<option <?php if ($RowEmployeeDetails['EmpSubCategory'] == "ContractWise") { ?>selected="selected" <?php } ?> value="ContractWise">Contract
														Wise</option>
												</select>
											</div>

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Designation</label>
												<select class="form-control select2" id="DesigID" name="DesigID">
													<option value="">Select Designation</option>
													<?php
													$SqlDeptList = "SELECT * FROM HrmDesignations Order by Designation";
													$StmtDept = $iconn->query($SqlDeptList);
													while ($DeptRow = $StmtDept->fetch_array()) { ?>
														<option <?php if ($RowEmployeeDetails['DesigID'] == $DeptRow['DesigID']) { ?>selected="selected" <?php } ?> value="<?php echo $DeptRow['DesigID']; ?>">
															<?php echo $DeptRow['Designation']; ?>
														</option>

													<?php } ?>
												</select>
											</div>
											<!--	<div class="form-group col-lg-3" > 
				<label for="exampleInputEmail1">Shift</label>
				 <select class="form-control select2" id="ShiftID" name="ShiftID" >
				  <option value="">Select Shift</option>
				 <?php
					$SqlDeptList = "SELECT * FROM HrmShiftDetails Order by ShiftName";
					$StmtDept = $iconn->query($SqlDeptList);
					while ($DeptRow = $StmtDept->fetch_array()) { ?>
					<option <?php if ($RowEmployeeDetails['ShiftID'] == $DeptRow['ShiftID']) { ?> selected="selected" <?php } ?>
					
					value="<?php echo $DeptRow['ShiftID']; ?>" >
					<?php echo $DeptRow['ShiftName']; ?>
					</option>
				
						 <?php } ?>
						</select>
				</div>
				-->

											<div class="form-group col-lg-3">
												<label for="exampleInputEmail1">Shift Category</label>
												<select class="form-control select2" name='ShiftCategory' id="ShiftCategory">

													<option <?php if ($RowEmployeeDetails['ShiftCategory'] == "General") { ?>selected="selected" <?php } ?> value="General">General
													</option>
													<option <?php if ($RowEmployeeDetails['ShiftCategory'] == "Rotational") { ?>selected="selected" <?php } ?> value="Rotational">Rotational
													</option>
												</select>
											</div>

											<div class="form-group  col-lg-3">
												<label for="exampleInputEmail1">Joining Date</label>
												<input class="form-control" type="date" placeholder="Employee Name" value="<?php echo $RowEmployeeDetails['JoiningDate'] ?>" id="JoiningDate" name="JoiningDate">
											</div>




											<!-- end row -->
										</div>
										<div class="col-xs-12 box-footer" align="right">
											<a href="" class="btn btn-danger">Close</a>
											<?php if ($MaxLimit) {
											} else if ($Count == 0) { ?> <button type="submit" name='submit' class="btn btn-success waves-effect waves-light">

													Submit</button>
											<?php } else { ?>

												<button type="submit" name='submit' class="btn btn-success waves-effect waves-light">

													Update</button>
											<?php } ?>
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
	$("#BioMetricID").keyup(function() {
		$("#BioMetricID").val(this.value.match(/[0-9]*/));
	});

	$(document).on('change', '#DeptID', function(e) {
		e.preventDefault();

		var DeptId = $(this).val();
		$.ajax({
			url: 'Ajax/AjDropDowns',
			type: 'POST',
			data: {
				'Type': 'SubDept',
				'DeptId': DeptId
			},
			success: function(data) {
				$("#SubDeptID").html(data);
			}
		})
	});

	/*$(document).on('change','#ShiftCategory',function(e){
			e.preventDefault();
			
			var DeptId=$(this).val();
				$.ajax({
			  url:'Ajax/AjDropDowns',
			  type:'POST',
			   data:{'Type':'SubDept','DeptId':DeptId},
			  success:function(data){
				$("#SubDeptID").html(data);
			}
				})
			});*/

	$(document).on('change', '#ShiftCategory', function(e) {

		var ShiftCategory = $(this).val();


		if (ShiftCategory == 'Rotational') {
			// $('#myModal1').modal('toggle');
		}

	});

	$('#EmpCategory').on('change', function (e) {	  
	  const EmpCategory = $(this).val();
	  if(EmpCategory == 'Worker'){
	  	$('#worker-type-select').show();
	  	$('#worker-village-select').show();
	  }else{
	  	$('#worker-type-select').hide();
	  	$('#worker-village-select').hide();
	  }
	});

	$(document).ready(function(){
		const EmpCategory = $('#EmpCategory').val();
		const BranchID = $('#BranchID').val();
		const VillageID = $('#VillageID').val();
		const SectionID = $('#SectionID').val();
		const WorkerSubCategory= $('#WorkerSubCategory').val();
		if(EmpCategory == "Worker"){
	  		$('#worker-type-select').show();
		}
		
		if(WorkerSubCategory == 1){
			$('#worker-village-select').show(); 
		    $('#worker-batch-select').show(); 			
			$('#worker-section-select').show(); 
		}
		if(WorkerSubCategory == 2){
			$('#worker-village-select').show(); 
		    $('#worker-batch-select').show(); 			
		}
	});

$('#WorkerSubCategory').on('change', function (e) {	  
  const WorkerType = $(this).val();

  if (WorkerType == '1') {
    $('#worker-batch-select').show();
    $('#worker-section-select').show();
  } else if (WorkerType == '2') {
    $('#worker-batch-select').show();
    
    // clear and hide section select
    $('#worker-section-select select').val(null).trigger("change");
    $('#worker-section-select').hide();
  } else {
    // clear both and hide both
    $('#worker-batch-select select').val(null).trigger("change");
    $('#worker-section-select select').val(null).trigger("change");
    $('#worker-batch-select').hide();
    $('#worker-section-select').hide();
  }
});

</script>
