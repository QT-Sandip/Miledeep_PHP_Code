<?php


// phpinfo();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

if($_SESSION["ClientID"]!='serp'){
  $SqlProcessingStations="SELECT ProcessingStationID,StationName FROM `RmProcessingStations` WHERE StationStatus=1"; 
$stmtProcessingStations = $iconn->query($SqlProcessingStations);

}

$accessApi = GET_ACCESS_ROLES;

$resultAccessRoles = CallAPI($accessApi, "GET", "", "");

$List = $resultAccessRoles->Data;



$usersApi = GET_USER_LIST;

$bodyContent = array(
	"ReqUserID" => $_GET['id'],
	"Edituser" => ture,
	// "PoID" => $_POST["poID"]
);


$result = CallAPI($usersApi, "GET", "", $bodyContent);

// var_dump($result);
$error  = $result->Error;


$UserData = $result->Data[0];
$accessType = $UserData->AccessType;
$UnitID = $UserData->UnitID; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	if (isset($_POST['submit'])) {
		
		$bodyContent = array(
			// 'ClientID' => $_SESSION["ClientID"],
			// 'Token' => "Token",
			"userID" => $_GET["id"],
			"username" => $_POST["username"],
			"designation" => $_POST["designation"],
			"AccessIPs" => $_POST["AccessIp"],
			"AccessType" => $_POST["accessType"],
			"role" => $_POST["role"],
			"status" => $_POST["status"],
			"productionplant" => $_POST["productionplant"],
		);
// print_r($bodyContent) ; exit;
		$result = CallAPI($usersApi, "POST", "", $bodyContent);
// var_dump($result->Error); exit;
		if ($result->Error) {
			$_SESSION["Error"] = $result->Error;
		} else {
			$_SESSION["Msg"] = $result->Msg;
			echo '<script language="javascript" type="text/javascript"> 
                alert("Success!");
                window.location = "GetUsersList.php";
        </script>';
			// header("Location: GetUsersList");
		}

		// if (!empty($resUpdate)) {
		// 	$_SESSION['Status'] = $resUpdate->Msg;
		// 	header("Location: GetUsersList");  
		// }
			// var_dump($resUpdate);
			
	}
}

$disableStyle = 'style="background: #eee; color: #505d69;"';

?>

<!doctype html>
<html lang="en">

<head>

	<?php include_once ROOT . 'includes/head.php' ?>

</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

	<?php include_once ROOT . 'includes/menu.php' ?>

	<!-- ============================================================== -->
	<!-- Start right Content here -->
	<!-- ============================================================== -->
	<div class="main-content">

		<div class="page-content">
			<div class="container-fluid">

				<?php include_once ROOT . 'includes/page-header.php' ?>

				<!-- start page title -->
				<div class="row">
					<div class="col-12">


						<div class="card">
							<div class="card-body">

								<div id="divDataList">
									<!-- -->
									<form action="edit-user?id=<?php echo $_GET["id"] ?>" method="post" id="frmEditUser">
										<div class="card-body">

											<div class="row">

												<div class="form-group col-sm-3">
													<label for="loginID">Login ID<span style="color:red">*</span></label>
													<input <?php echo $disableStyle; ?> disabled type="text" class="form-control" name='loginID' id="loginID" value="<?php echo $UserData->UserLoginID; ?>" />
												</div>

												<div class="form-group col-sm-3">
													<label for="deptName">Department Name<span style="color:red">*</span></label>
													<input <?php echo $disableStyle; ?> disabled type="text" class="form-control" name='deptName' id="deptName" value="<?php echo $UserData->DepartmentName; ?>" />
												</div>
												<?php if($_SESSION["ClientID"]!='serp'){ ?>
												<div class="form-group col-sm-3">
													<label for="productionplant">Production Plant</label>
													<select class="form-control select2" name="productionplant" id="productionplant">
														<option value=""> Select Plant </Option>
														<?php while ($dataProcessingStations = $stmtProcessingStations->fetch_array()) { ?>
															<!-- echo $UnitID=$UserData->UnitID; -->
														<option  <?php if ($UnitID == $dataProcessingStations['ProcessingStationID']) {
																				echo "selected";
																			}  ?> value="<?php echo $dataProcessingStations['ProcessingStationID'] ;?>" > 
																			<?php echo $dataProcessingStations['StationName'] ;?></option>
														<?php } ?>
													</select>
												</div>
												
												<?php } ?>      
												
												<div class="form-group col-sm-3">
													<label for="accessType">Access Type<span style="color:red">*</span></label>
													<select class="form-control select2" name="accessType" id="accessType">
														<option value=""> Select options </Option>
														<option value="1" <?php if ($accessType == "1") {
																				echo "selected";
																			}  ?>>Super Admin</option>
														<option value="2" <?php if ($accessType == "2") {
																				echo "selected";
																			}  ?>>Admin</option>
														<option value="3" <?php if ($accessType == "3") {
																				echo "selected";
																			}  ?>>User</option>
													</select>
												</div>

												<div class="form-group col-sm-3">
													<label for="username">Username<span style="color:red">*</span></label>
													<input type="text" class="form-control" name='username' id="username" value="<?php echo $UserData->UserName; ?>" />
												</div>

												<div class="form-group col-sm-3">
													<label for="designation">Designation<span style="color:red">*</span></label>
													<input type="text" class="form-control" name='designation' id="designation" value="<?php echo $UserData->DesignationID; ?>" />
												</div>

												<div class="form-group col-sm-3">
													<label for="AccessIp">Access Ips</label>
													<input type="text" class="form-control" name='AccessIp' id="AccessIp" placeholder="Ex 123.01.01.01,123.02.01.01" value="<?php echo $UserData->AccessIPs; ?>" />
												</div>

												<div class="form-group col-sm-3">
													<label for="role">Role</label>
													<select class="form-control select2" name="role" id="role">
														<option value="">Select</option>
														<?php foreach ($List as $item) { ?>
															<option value="<?php echo $item->ID; ?>" 
															<?php if ($UserData->UserRoleID == $item->ID) { echo "selected"; } ?>>
																<?php echo $item->RoleName; ?></option>
														<?php } ?>
													</select>
												</div>

												<div class="form-group col-sm-3">
													<label for="status">Status</label>
													<select class="drpSelect" name="status" id="status">
														<option value="1" <?php echo ($UserData->Status == 1) ? 'selected' : ''; ?>>Active</option>
														<option value="0" <?php echo ($UserData->Status == 0) ? 'selected' : ''; ?>>In-Active</option>
													</select>
												</div>

											</div>
											<br /><br />

											<div class="col-xs-12 box-footer text-center">
												<!--<a target="" href="LinersList.php" class="btn btn-danger">Close</a>-->
												<button type="submit" name='submit' class="btn btn-success">
													<?php
													if (isset($_GET["id"]) && !empty($_GET["id"])) {
														echo "Update";
													} else {
														echo "ADD";
													}
													?>
												</button>
											</div>
										</div>
									</form>
								</div>

							</div>
						</div>


					</div>
				</div>

			</div> <!-- container-fluid -->
		</div>
		<!-- End Page-content -->
		<?php include_once ROOT . 'includes/footer.php' ?>

	</div>
	<!-- end main content-->

</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . 'includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . 'includes/app-js.php' ?>

<script>
	$(document).ready(function() {

		jQuery.validator.addMethod('selectcheck', function(value) {
			return (value != '0' || value != '');
		}, "Field required");

		jQuery.validator.addMethod("noSpace", function(value, element) {
			return value == '' || value.trim().length != 0;
		}, "No space please and don't leave it empty");

		$("#frmEditUser").validate({
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
				username: {
					required: true,
					noSpace: true
				},
				designation: {
					required: true,
					noSpace: true
				},
				// role: {
				// 	required: true,
				// 	selectcheck: true
				// }
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