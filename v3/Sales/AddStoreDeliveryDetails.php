<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

include ("RestAPI.php");

$PageTitle="Add Store Delivery Details";
$PageTitleFolder="Master";

$uid=$_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));


$ID = isset($_GET['id']) ? $_GET['id'] : "";

if($ID != "") {
	$apiGet = GetEditPageStoreDeliveryDetailsAPI;
	$bodyContent = array("id"=>$ID);
	$resGetEditPageStoreDeliveryDetailsAPI = CallAPI($apiGet, "GET", "", $bodyContent);
	$resList = $resGetEditPageStoreDeliveryDetailsAPI->Data->EditPageStoreDeliveryDetails[0]; 
	
}


$SqlCountries="SELECT * FROM `ScrmCountriesList`";
$StmtCountries=$iconn->query($SqlCountries);

$SqlConsignee="SELECT * FROM `ScrmConsigneeDetails` WHERE Status=1 Order By ConID asc";
$StmtConsignee=$iconn->query($SqlConsignee);

$SqlPort="SELECT * FROM `ScrmPortCities` ORDER by PortId desc";
$StmtPortofDischarge=$iconn->query($SqlPort);
$StmtFinalDestination=$iconn->query($SqlPort);

extract($_POST);
extract($_GET);

if(isset($_POST['submit'])){
	
	$apiPost = PostStoreDeliveryDetailsAPI;

	// $processorType = "2";	
	$name = $_POST["name"];	
	$drpBuyer = $_POST["drpBuyer"];	
	$contactPerson = $_POST["contactPerson"];	
	$phone = $_POST["phone"];	
	$address1 = $_POST["address1"];	
	$address2 = $_POST["address2"];	
	$emailID = $_POST["emailID"];	
	$state = $_POST["state"];	
	$country = $_POST["drpcountry"];	
	$portDischarge = $_POST["drpportDischarge"];	
	$finalDestination = $_POST["drpfinalDestination"];	

	$bodyContent = array(
		"name"=>$name,
		"address1"=>$address1,
		"address2"=>$address2,
		"phone"=>$phone,
		"emailID"=>$emailID,
		"state"=>$state,
		"drpcountry"=>$country,
		"drpportDischarge"=>$portDischarge,
		"drpfinalDestination"=>$finalDestination,
		"contactPerson"=>$contactPerson,
		"drpBuyer"=>$drpBuyer,
		"userid"=>$uid,
		"StoreID"=>$ID);
		
	$resPostStoreDeliveryDetailsAPI = CallAPI($apiPost, "GET", "", $bodyContent);
	$resPost = $resPostStoreDeliveryDetailsAPI->Data; 
	
	if(isset($resPost->Error)){
		$Status = "Error has Occurred";
		$_SESSION['danger'] = 'T';
	}else{
		$Status = $resPost->Msg;
		$_SESSION['Status'] = 'T';
	}
	if($ID != ""){
		$url = "AddStoreDeliveryDetails?id=".$ID;
	}else{
		$url = "AddStoreDeliveryDetails";
	}
	header("Location: $url");
	

}

// check weather value exits or not 
function funPrintValue($key) {
	echo (isset($key) && $key != "") ? $key : ""; 
}

?>


<!DOCTYPE html>
<html lang="en">

    <head>
        
            <?php include ($IncludesPath."title-meta.php"); ?>
		 	<?php  include ($IncludesPath."DbLinks.php");?>
            <?php include ($IncludesPath."head-css.php");?>
			<style>
				select.error + .select2-container, input.error, textarea.error {
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
					
						

                        <?php include ($IncludesPath."page-title.php");?>       
						
						<?php if($Status !=''){ 	//Show Alert  	?>
								<div class="alert alert-success alert-dismissible fade show" role="alert">
									<i class="mdi mdi-check-all mr-2"></i>
										<?php 
											echo $Status;
											$_SESSION['Status']='';	  
										?>
									<button type="button" class="close" data-dismiss="alert" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
						<?php } ?>

						<?php if($Status !=''){ 	//Show Alert  	?>
								<div class="alert alert-danger alert-dismissible fade show" role="alert">
									<i class="mdi mdi-check-all mr-2"></i>
										<?php 
											echo $Status;
											$_SESSION['danger']='';	  
										?>
									<button type="button" class="close" data-dismiss="alert" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
						<?php } ?>
	
						<div class="row">
                            <div class="col-12">
                                <div class="card">
								
									<form role="form" method="post" id="frmAddStoreDeliveryDetails">
										<div class="card-body">
										
											<div class="row">
												
												<div class="form-group col-sm-3" >
													<label for="name">Name<span style="color:red">*</span></label>
													<input required type="text" step="any" class="form-control" name='name' id="name" 
														placeholder="Enter name" value="<?php echo funPrintValue($resList->StoreName) ?>"  />		
												</div>

                                                <div class="form-group col-lg-3">
                                                    <label for="drpBuyer">Buyer<span style="color:red">*</span></label>              			   
                                                    <select required class="form-control select2" name="drpBuyer" id="drpBuyer">
                                                    <option value=""> All <?php echo $resList->BuyerID ?></option>
                                                    <?php while($RowConsignee=$StmtConsignee->fetch_array()) { ?>  
                                                        <option  
                                                            <?php if($resList->BuyerID == $RowConsignee['ConID']){ ?> selected="selected"  <?php } ?>
                                                            value="<?php echo $RowConsignee['ConID']; ?>"><?php echo $RowConsignee['ConsigneeName'];?></option>
                                                    <?php } ?>

                                                    <select>   
                                                </div>

												<div class="form-group col-sm-3" >
													<label for="contactPerson">Contact Person</label>
													<input  type="text" step="any" class="form-control" name='contactPerson' id="contactPerson" 
														placeholder="Enter Contact Person" value="<?php echo funPrintValue($resList->ContactPerson) ?>"  />		
												</div>

												<div class="form-group col-sm-3" >
													<label for="phone">Phone</label>
													<input  type="text" step="any" class="form-control" name='phone' id="phone" 
														placeholder="Enter Phone" value="<?php echo funPrintValue($resList->PhoneNumber) ?>"  />		
												</div>

												

												<div class="form-group col-sm-3" >
													<label for="address1">Address 1<span style="color:red">*</span></label>
													<textarea  type="text" step="any" class="form-control" name='address1' id="address1" rows="4" cols="50"
														placeholder="Enter Address 1" value=""  ><?php echo funPrintValue($resList->StoreAddress1) ?></textarea>		
												</div>
												
												<div class="form-group col-sm-3" >
													<label for="address2">Address 2</label>
													<textarea  type="text" step="any" class="form-control" name='address2' id="address2" rows="4" cols="50"
														placeholder="Enter Address 2" value=""  ><?php echo funPrintValue($resList->StoreAddress2) ?></textarea>		
												</div>

											

												<div class="form-group col-sm-3" >
													<label for="emailID">Email ID</label>
													<input  type="text" step="any" class="form-control" name='emailID' id="emailID" 
														placeholder="Enter Email ID" value="<?php echo funPrintValue($resList->EmailID) ?>"  />		
												</div>

												<div class="form-group col-sm-3" >
													<label for="state">State</label>
													<input  type="text" step="any" class="form-control" name='state' id="state" 
														placeholder="Enter state" value="<?php echo funPrintValue($resList->State) ?>"  />		
												</div>


												<div class="form-group col-lg-3">
                                                    <label for="drpcountry">Country</label>              			   
                                                    <select  class="form-control select2" name="drpcountry" id="drpcountry">
                                                    <option value=""> All </Option>
                                                    <?php while($RowCountries=$StmtCountries->fetch_array()) { ?>  
                                                        <option  
                                                            <?php if($resList->CountryID == $RowCountries['id']){ ?> selected="selected"  <?php } ?>
                                                            value="<?php echo $RowCountries['id']; ?>"><?php echo $RowCountries['name'];?></option>
                                                    <?php } ?>

                                                    <select>   
                                                </div>
                                                

												<div class="form-group col-lg-3">
                                                    <label for="drpportDischarge">Port of Discharge</label>              			   
                                                    <select  class="form-control select2" name="drpportDischarge" id="drpportDischarge">
                                                    <option value=""> All </Option>
                                                    <?php while($RowPortofDischarge=$StmtPortofDischarge->fetch_array()) { ?>  
                                                        <option  
                                                            <?php if($resList->PortofDischarge == $RowPortofDischarge['PortId']){ ?> selected="selected"  <?php } ?>
                                                            value="<?php echo $RowPortofDischarge['PortId']; ?>"><?php echo $RowPortofDischarge['Name'];?></option>
                                                    <?php } ?>

                                                    <select>   
                                                </div>
												
												
												<div class="form-group col-lg-3">
                                                    <label for="drpfinalDestination">Final Destination</label>              			   
                                                    <select  class="form-control select2" name="drpfinalDestination" id="drpfinalDestination">
                                                    <option value=""> All </Option>
                                                    <?php while($RowFinalDestination=$StmtFinalDestination->fetch_array()) { ?>  
                                                        <option  
                                                            <?php if($resList->FinalDestination == $RowFinalDestination['PortId']){ ?> selected="selected"  <?php } ?>
                                                            value="<?php echo $RowFinalDestination['PortId']; ?>"><?php echo $RowFinalDestination['Name'];?></option>
                                                    <?php } ?>

                                                    <select>   
                                                </div>


                                                
										
											</div>

											<div class="col-xs-12 box-footer text-right">
												<a href="StoreDeliveryDetailsList"  class="btn btn-danger"> Close</a>
												<button type="submit"  name='submit' class="btn btn-success">
													<?php    
														if($ID =='') { 
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

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>
		
		<script>
			jQuery.validator.addMethod('selectcheck', function (value) {
				return (value != '0' || value != '');
			}, "year required");
			jQuery.validator.addMethod("noSpace", function(value, element) { 
				return value == '' || value.trim().length != 0;  
			}, "No space please and don't leave it empty");
			$(document).ready(function(){
				$("#frmAddStoreDeliveryDetails").validate({
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
						name: { required: true, noSpace: true },
						drpBuyer: { selectcheck: true },
						address1: { required: true, noSpace: true}
						
						
					},
					errorPlacement: function () {
						return false;  // suppresses error message text
					},
					submitHandler: function () {
						return true;
					}
				});
			});
		</script>
  
       
    </body>
</html>				


