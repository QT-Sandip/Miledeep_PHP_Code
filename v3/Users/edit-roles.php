<?php
// API_HRM_ACCESS_ROLES
// $_SESSION["ClientID"]

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ID = (isset($_GET["id"]) && !empty($_GET["id"])) ? $_GET["id"] : "";

$deptId = (isset($_GET["deptId"]) && !empty($_GET["deptId"])) ? $_GET["deptId"] : ""; ;

if($deptId == ""){
    $deptId = $_SESSION['DeptID'];    
}

$api = API_APP_ACCESS_ROLES;

$bodyContent = array(        
    "AccessRoleID" => $ID,
    "ClientID" => $_SESSION["ClientID"]
);
    
$resAccessRoles = CallAPI($api, "GET", "", $bodyContent);

$resAllValues = $resAccessRoles->Data->EditAccess;
$resAllValues = $resAccessRoles->Data->AllValues;
$resSelectedHeaderIDs = $resAccessRoles->Data->SelectedValues->HeaderIDs;
$resSelectedOptionsIDs = $resAccessRoles->Data->SelectedValues->OptionsIDs;
$RoleName = (isset($resAccessRoles->Data->RoleName) && !empty($resAccessRoles->Data->RoleName)) ? $resAccessRoles->Data->RoleName : "";

$headerValues = explode(',',$resAdminUserRoles["Header"]);
$optionsValue = explode(',',$resAdminUserRoles["Options"]);
$EditAccess = $resAccessRoles->Data->EditAccess;
$DeleteAccess = $resAccessRoles->Data->DeleteAccess;

// echo json_encode($resAccessRoles, JSON_PRETTY_PRINT);
// exit;

$PageTitleFolder="Master";
$PageTitle="Admin Users and Privileges";

// $urlparam = $_SERVER['REQUEST_URI'];
// $url = explode('?',$urlparam);
// $url = $result[0];

if(isset($_POST['submit'])){
	$role = $_POST["role"];
	$headerId = $_POST["headerId"];
	$optionId = $_POST["optionId"];
	$editAccess = isset($_POST["editAccess"]) ? "1" : "0";
	$deleteAccess = isset($_POST["deleteAccess"]) ? "1" : "0";

	$chkHeaderId = "";  
	foreach($headerId as $chk1)  
	{  
		$chkHeaderId .= $chk1.",";  
	}  

	$chkOptionsId = "";  
	foreach($optionId as $chk2)  
	{  
		$chkOptionsId .= $chk2.",";  
	}  

	$HeaderIDs = rtrim($chkHeaderId, ',');
	$OptionsIDs = rtrim($chkOptionsId, ',');

    $bodyContent = array(        
        "RoleName" => trim($_POST["role"]),
        "ClientID" => $_SESSION["ClientID"], // trim($_POST["drpBranch"]), 
        "HeaderID" => $HeaderIDs,
        "SubMenuID" => $OptionsIDs,
        "DeleteAccess" => $deleteAccess,
        "EditAccess" => $editAccess,
        "AccessRoleID" => $ID
    );

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // exit; 
    $resAccessRoles = CallAPI($api, "POST", "", $bodyContent);


    $AccessRoleID = $resAccessRoles->Data->AccessRoleID;
    $error = $resAccessRoles->Error;
    
    if(!isset($error) || !empty($error)) {
        if(isset($_GET["id"])) {		
            header('Location:' . $_SERVER['REQUEST_URI']);
        }else{		
            header('Location:' . $_SERVER['REQUEST_URI']."?id=".$AccessRoleID);
        }
    } else {
        
    }

}


?>

<!DOCTYPE html>
<html lang="en">
    <head>        
		<?php include ($IncludesPath."title-meta.php"); ?>
		<?php include ($IncludesPath."DbLinks.php"); ?>
		<?php include ($IncludesPath."head-css.php"); ?>
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
								if($Status !=''){
								//Show Alert 
								?>
								<div class="alert alert-success alert-dismissible fade show" role="alert">
									<i class="mdi mdi-check-all mr-2"></i>
										<?php echo $Status;
											$_SESSION['Status']='';	  
										?>
									<button type="button" class="close" data-dismiss="alert" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
						<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>
	
						<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form action="edit-roles?id=<?php echo $_GET["id"] ?>" role="form" method="post" id="frmEditRoles">
                                    <div class="card-body">

									<div class="row">
										<div class="form-group col-sm-3" >
											<label for="role">Role<span style="color:red">*</span></label>
											<input type="text" required class="form-control" name="role" id="role" value="<?php echo $RoleName ?>" />
										</div>
										<div class="form-check col-sm-3" >
											<br /><br />
											<input type="checkbox" class="form-check-input" name='editAccess' id="editAccess" 
												<?php echo $EditAccess == "1" ? "checked" : "" ; ?> />
											<label for="editAccess">Edit Access</label>
										</div>
										<div class="form-check col-sm-3" >
											<br /><br />
											<input type="checkbox" class="form-check-input" name='deleteAccess' id="deleteAccess" 
												<?php echo $DeleteAccess == "1" ? "checked" : "" ; ?> />
											<label for="deleteAccess">Delete Access</label>
										</div>
									</div>
									<br /><br />
									<?php 
                                        $i = 1;
                                        foreach($resAllValues as $Allval) {
									?>
										<div class="row">
											<div class="form-group col-sm-12 checkdiv1" >
													<div class="form-check">
														<input class="form-check-input headerCheck" type="checkbox" id="headerCheck<?php echo $i; ?>" name="headerId[]"
															<?php 
																if(isset($_GET["id"]) && !empty($_GET["id"])) {
																	if(isset($resSelectedHeaderIDs) && !empty($resSelectedHeaderIDs)) {
																		echo in_array($Allval->HeaderID, $resSelectedHeaderIDs) ? "checked" : "" ;
																	}
																}
															?> value="<?php echo $Allval->HeaderID; ?>" />
														<label class="form-check-label text-warning font-weight-bold" for="headerCheck<?php echo $i; ?>">
															<?php  echo $Allval->Header ?>
														</label>
													</div>
												<?php 
													$j = 1;
                                                    foreach($Allval->Options as $item) {
												?>												
												<div class="form-check form-check-inline">
													<input type="checkbox" id="optionId<?php echo $i.$j; ?>" name="optionId[]"
														<?php 
															if(isset($_GET["id"]) && !empty($_GET["id"])) {
																if(isset($resSelectedOptionsIDs) && !empty($resSelectedOptionsIDs)) {
																	echo in_array($item->SubMenuID, $resSelectedOptionsIDs) ? "checked" : "" ;
																}
															}
														?> value="<?php echo $item->SubMenuID; ?>" class="form-check-input optionsCheck" />
													<label for="optionId<?php  echo $i.$j; ?>" class="form-check-label"><?php  echo $item->SubMenuName; ?></label>              
												</div>
												<?php  $j++; } ?>
											</div>  
										</div> 
										
										<hr />
									<?php  $i++; } ?>
				
									<div class="col-xs-12 box-footer text-right"> 
										<!-- <input type="hidden" id="hdnHeaderOptions" name="hdnHeaderOptions" value="<?php // echo implode(", ",$headerValues); ?>" /> 
										<input type="hidden" id="hdnOptions" name="hdnOptions" value="<?php // echo implode(", ", $optionsValue); ?>" />  -->
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

		<script>
		$('document').ready(function(){
			$('.checkdiv1 input').change(function() {
			//function getOptionIds(){
				var OptionIds = $("input[name='OptionId']:checked").map(function() {
							return this.value;
						}).get().join(', ');
						$('#OptionId').val(OptionIds + '');
			})
		}); 
		</script>

		<script>

			// function checkHeader() {
			// 	var selected = new Array();
			// 	$("input.headerCheck:checked").each(function () {
			// 		selected.push(this.value);
			// 	});
			// 	$('#hdnHeaderOptions').val(selected.join(","));				
			// }

			// function checkOptions() {
			// 	var selectedOptions = new Array();
			// 	$("input.optionsCheck:checked").each(function () {
			// 		selectedOptions.push(this.value);
			// 	});
			// 	$('#hdnOptions').val(selectedOptions.join(","));
			// }

			$(document).on("change", ".headerCheck", function() {
            	//checkHeader();

				if ($(this).is(':checked')){
					$(this).closest(".row").find(".optionsCheck").each(function (){
						$(this).prop("checked", true);
					});
				}else{
					$(this).closest(".row").find(".optionsCheck").each(function (){
						$(this).prop("checked", false);
					});
				}

				//checkOptions();

			});

			$(document).on("change", ".optionsCheck", function() {
				// checkOptions();

				var rowOptionsCheckedList = $(this).closest(".row").find(".optionsCheck:checked").length;
				if(rowOptionsCheckedList == 0) {
					$(this).closest(".row").find(".headerCheck").prop("checked", false);
				}else{
					$(this).closest(".row").find(".headerCheck").prop("checked", true);
				}

				// checkHeader();
			});

		</script>
		<?php if(!isset($_GET["id"])){ ?>		
		<script>
			$('document').ready(function(){
			     $('input:checkbox').prop('checked', true);    
			});

		</script>
		<?php } ?>
    </body>
</html>	

