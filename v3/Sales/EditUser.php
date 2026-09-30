<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PageTitleFolder="Master";
$PageTitle="Edit User Details";
$SideBarOptionTbl=$_SESSION["SideBarOptionTbl"];
$SideBarHeaderTbl=$_SESSION["SideBarHeaderTbl"];
//$UserID=$_SESSION["UserID"];

extract($_POST);
extract($_GET);
$ID = $_GET["id"];

$disableStyle = 'style="background: #eee; color: #505d69;"';

$SqlDepartments = "SELECT `DeptID`, `DepartmentName` FROM `AdminDepartments`";
$StmtDepartments = $iconn->query($SqlDepartments);

$SqlRoles = "SELECT ID,`RoleName` FROM `AdminUserRoles`";
$StmtRoles = $iconn->query($SqlRoles);

function GetUsersListAPI(){
    $curl = curl_init();
    
    $headers = array("Content-Type: application/json");

    $data = (object)array(
    'ClientID' => $_SESSION["ClientID"],
    'Token' => "Token");

    $json = json_encode($data);           
    $json = ltrim(rtrim($json, ']'),'[');
                    
    curl_setopt($curl,CURLOPT_URL, 'https://serp.silverlights.in/Api/ApiV1.php?f=getUserList'); 
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl,CURLOPT_POSTFIELDS, $json);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);        

    $MainData = curl_exec($curl);
    $result = json_decode($MainData);
    curl_close($curl);
    
    return $result;
}    

function GetUpdateUsersAPI($userID, $deptID, $accessType,$loginID, $username, 
$designation, $role,$AccessIp){
    $curl = curl_init();
    
    $headers = array("Content-Type: application/json");

    $data = (object)array(
		// 'ClientID' => $_SESSION["ClientID"],
		// 'Token' => "Token",
		"userID"=> $userID,
		"deptID"=> $deptID,
		"accessType"=> $accessType,
		"loginID"=> $loginID,
		"username"=> $username,
		"designation"=> $designation,
		"role"=> $role,
		"AccessIp"=> $AccessIp
	);

     $json = json_encode($data);          
    $json = ltrim(rtrim($json, ']'),'[');
                    
    curl_setopt($curl,CURLOPT_URL, 'https://serp.silverlights.in/Api/ApiV1.php?f=updateUserDetails'); 
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl,CURLOPT_POSTFIELDS, $json);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);        

    $MainData = curl_exec($curl);
    $result = json_decode($MainData);
    curl_close($curl);
    
    return $result;
}    
    
$resUsersList = GetUsersListAPI();

// var_dump($resUsersList);
if(!empty($resUsersList->Data)) {
	foreach ($resUsersList->Data as $items) { 
		if($items->UserID == $_GET["id"]) {
			$deptId = $items->DeptID;
			$departmentName = $items->DepartmentName;
			$accessType = $items->AccessType;
			$userLoginID = $items->UserLoginID;
			$userName = $items->UserName;
			$designation = $items->DesignationID;
	       $UserRoleid  = $items->UserRoleID ;
	       $AccessIPs  = $items->AccessIPs ;

		}
	}
}
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
// $currentURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$currentURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ;
// echo $currentURL;


if(isset($_POST['submit'])){

	$userID = $_GET["id"];
	$deptID = $_POST["deptID"];
	$accessType = $_POST["accessType"];
	$loginID = $_POST["loginID"]; 
	$username = $_POST["username"];
	$designation = $_POST["designation"];
	$role = $_POST["role"];
 	$AccessIp = $_POST["AccessIp"]; 

	$resUpdate = GetUpdateUsersAPI($userID, $deptID, $accessType,$loginID, 
	$username, $designation, $role,$AccessIp);
	
	if(!empty($resUpdate)){
		$_SESSION['Status'] = $resUpdate->Msg;
		$_SESSION['Error'] = $resUpdate->Error;
		header("Location: GetUsersList");
	}
// 	// var_dump($resUpdate);
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>        
		<?php include ($IncludesPath."title-meta.php"); ?>
		<?php include ($IncludesPath."DbLinks.php"); ?>
		<?php include ($IncludesPath."head-css.php"); ?>

		<style>
			select.error + .select2-container, input.error, textarea.error, input.error:focus, textarea.error:focus {
				border: 1px solid red;
			}
		</style>
    </head>
    <body data-sidebar="dark">	
	 <?php
			include ($IncludesPath."PreLoader.php"); ?>

        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php
				include ($IncludesPath."topbar.php");
				include ($IncludesPath."sidebar.php"); 
			?>
 			<!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">
					
						 <?php
								if(isset($_SESSION['Status']) && !empty($_SESSION['Status'])){
								//Show Alert 
								?>
								<div class="alert alert-success alert-dismissible fade show" role="alert">
									<i class="mdi mdi-check-all mr-2"></i>
										<?php  echo $_SESSION['Status'];
										 	$_SESSION['Status'] = '';	  
										?>
									<button type="button" class="close" data-dismiss="alert" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
						<?php  } ?>

						 <?php
								if(isset($_SESSION['Error']) && !empty($_SESSION['Error'])){
								//Show Alert 
								?>
								<div class="alert alert-danger alert-dismissible fade show" role="alert">
									<i class="mdi mdi-check-all mr-2"></i>
										<?php  echo $_SESSION['Error'];
										 	$_SESSION['Error'] = '';	  
										?>
									<button type="button" class="close" data-dismiss="alert" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
						<?php  } ?>

                        <?php include ($IncludesPath."page-title.php");?>
	
						<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post" id="frmEditUser">
                                    <div class="card-body">

									<div class="row">
										<div class="form-group col-lg-3" >
											<label for="deptID">Department Name<span style="color:red">*</span></label>
											<select class="form-control select2" name="deptID" id="deptID" disabled>
												<option value=""> Select options </Option>
												<?php while($RowDepartments=$StmtDepartments->fetch_array()) { ?>  
													<option value="<?php echo $RowDepartments['DeptID'];?>"
															<?php if($deptId == $RowDepartments['DeptID']) { echo "selected"; }  ?>	
														><?php echo $RowDepartments['DepartmentName'];?></OPTION>
												<?php } ?>
											<select>
										</div>
										<div class="form-group col-sm-3" >
											<label for="accessType">Access Type<span style="color:red">*</span></label>
											<select class="form-control select2" name="accessType" id="accessType" disabled>
												<option value=""> Select options </Option>
												<option value="1" <?php if($accessType == "1") { echo "selected"; }  ?> >Super Admin</option>
												<option value="2" <?php if($accessType == "2") { echo "selected"; }  ?> >Admin</option>
												<option value="3" <?php if($accessType == "3") { echo "selected"; }  ?> >User</option>
											<select>
										</div>

										<div class="form-group col-sm-3" >
											<label for="loginID">Login ID<span style="color:red">*</span></label>
											<input <?php echo $disableStyle; ?> disabled type="text" class="form-control" name='loginID' id="loginID" value="<?php echo $userLoginID; ?>" />		
										</div>
										<div class="form-group col-sm-3" >
											<label for="username">Username<span style="color:red">*</span></label>
											<input type="text" class="form-control" name='username' id="username" value="<?php echo $userName; ?>" />		
										</div>
										<div class="form-group col-sm-3" >
											<label for="designation">Designation<span style="color:red">*</span></label>
											<input type="text" class="form-control" name='designation' id="designation" value="<?php echo $designation; ?>" />		
										</div>
										
										<div class="form-group col-sm-3" >
											<label for="AccessIp">Access Ips</label>
											<input type="text" class="form-control" 
											name='AccessIp' id="AccessIp" placeholder="Ex 123.01.01.01,123.02.01.01" value="<?php echo $AccessIPs; ?>" />		
										</div>
										<div class="form-group col-sm-3" >
											<label for="role">Role<span style="color:red">*</span></label>
											<select required class="form-control select2" name="role" id="role">
												<option value="">Select</option>
												
                                                <?php 
													 while($RowRoles=$StmtRoles->fetch_array()) { ?>
														<option <?php if($UserRoleid==$RowRoles['ID']){ ?> selected=="selected"
														<?php } ?>	value="<?php echo $RowRoles['ID']?>"><?php echo$RowRoles['RoleName']?></option>
													 <?php  } 
												?>
											<select>
										</div>
									</div>
									<br /><br />
				
									<div class="col-xs-12 box-footer text-right">
									<!--<a target="" href="LinersList.php" class="btn btn-danger">Close</a>-->
										<button type="submit"  name='submit' class="btn btn-success">
										<?php 
											if(isset($_GET["id"]) && !empty($_GET["id"])) { 
												echo "Update";
											} else { 
												echo "ADD";
											} 
										?>
										</button>
									</div>	
								</div>
            				</form>
       						<!-- end col -->
                        </div> <!-- end row -->
    
					</div> <!-- end card body-->
				</div> <!-- end card -->
                            
                <!-- End Page-content -->

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>
		<script>
			$(document).ready(function(){

				jQuery.validator.addMethod('selectcheck', function (value) {
					return (value != '0' || value != '');
				}, "Field required");

				jQuery.validator.addMethod("noSpace", function(value, element) { 
					return value == '' || value.trim().length != 0;  
				}, "No space please and don't leave it empty");

				$("#frmEditUser").validate({
					onkeyup: false,
					onfocusout: false,
					invalidHandler: function (form, validator) {
						var errors = validator.numberOfInvalids();
						if (errors) {
							var firstInvalidElement = $(validator.errorList[0].element);
							//$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
							firstInvalidElement.focus();
						}
					},
					ignore: "",
					rules: {
						username: { required: true, noSpace: true },
						designation: { required: true, noSpace: true },
						role: { required: true, selectcheck: true }
					},
					errorPlacement: function () {
						return false;  // suppresses error message text
					},
					submitHandler: function () {
						<?php //echo "var userID = '".$_GET["id"]."';"; ?>
						// var deptID = $("#deptID").val();
						// var accessType = $("#accessType").val();
						// var loginID = $("#loginID").val();
						// var username = $("#username").val();
						// var designation = $("#designation").val();
						// var role = $("#role").val();
						// var frmdata = JSON.stringify({userID: userID,deptID: deptID, accessType: accessType, loginID: loginID, username: username, designation: designation, role: role});

						// console.log(frmdata);

						return true;
					}
				});
			});
		</script>
    </body>
</html>	

