<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


$PageTitleFolder="Master";
$PageTitle="Add Payment Terms";


$ID=$_GET['ID'];
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));


$SqlSelect="SELECT * FROM `ScrmPaymentTerms` WHERE ID='$ID'";
$stmtSelect=$iconn->query($SqlSelect);
$RowSelect=$stmtSelect->fetch_array();

$COUNT=mysqli_num_rows($stmtSelect);

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	//echo $PaymentMode; exit;
	 if($PaymentMode=='LC'){
	 $LcStatus=1;}
			 
	else if($PaymentMode=='Non LC'){
	$LcStatus=0;}
	 			
	
	 IF($COUNT==0){	
	 
	$SqlInsert="INSERT INTO `ScrmPaymentTerms`( `PaymentName`, 
	`PaymentMode`, `LcStatus`, `NoOfDays`, `ConditionDate`,`ShortName`) 
				VALUES ('$PaymentName','$PaymentMode','$LcStatus',
				'$NoOfDays','$ConditionDate','$ShortName')";
	

	$stmt=$iconn->query($SqlInsert);
	if($stmt){
		$insertedPaymentTermId = $iconn->insert_id;
		legacyAuditLogInsertById('ScrmPaymentTerms', 'ID', $insertedPaymentTermId, $_POST, array(
			'ReferenceNo' => $insertedPaymentTermId,
			'Remarks' => 'Payment terms created',
		), 'Sales');
		
		echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "PymntTrmList.php";
		</script>';
	}  }
	else{
	   $existingPaymentTerm = legacyAuditFetchRowById('ScrmPaymentTerms', 'ID', $ID, 'Sales');
	   $SqlInsert="UPDATE `ScrmPaymentTerms` SET 
	  `PaymentName`= '$PaymentName',
	  `PaymentMode`= '$PaymentMode',
	  `NoOfDays`= '$NoOfDays',
	  `ConditionDate`= '$ConditionDate', 
	  `ShortName`= '$ShortName',
	  `LcStatus`= '$LcStatus' WHERE ID='$ID'"; 
	  
	$stmt=$iconn->query($SqlInsert);
	if($stmt){
		legacyAuditLogUpdateById('ScrmPaymentTerms', 'ID', $ID, $existingPaymentTerm, $_POST, array(
			'ReferenceNo' => $ID,
			'Remarks' => 'Payment terms updated',
		), 'Sales');
		$ArgumentsArray[PostedUserId]=$PostedUserId;
            $ArgumentsArray[PageUrl]=$PageUrl;
            $ArgumentsArray[Remarks]=$Remarks;
            $ArgumentsArray[ReferenceID]=$ID;
            postUserEditLog($ArgumentsArray);
		
		echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "PymntTrmList.php";
		</script>';
	}
	
	}
	
}

 ?>


<!DOCTYPE html>
<html lang="en">

    <head>
        
            <?php include ($IncludesPath."title-meta.php"); ?>
		 	<?php  include ($IncludesPath."DbLinks.php");?>
            <?php include ($IncludesPath."head-css.php");?>

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
$_SESSION['Status']='';	  ?>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post" id="frmPaymentTerms">
                                    <div class="card-body">
									
										<div class="row">
	 
				
				 <div class="form-group col-lg-3" > 
				<label for="exampleInputEmail1">Payment Mode<span style="color:red">*</span></label>
                 <select required class="form-control select2" id="PaymentMode" name="PaymentMode" >
				  <option  value="">Select</option>
				  <option <?php if($RowSelect['PaymentMode']=='LC') {?>selected="selected"<?php } ?>
				  
				  value="LC">LC</option>
				  <option  <?php if($RowSelect['PaymentMode']=='Non LC') {?>selected="selected"<?php } ?>
				  value="Non LC">Non LC</option>
				
				
						</select>
					</div>
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Number of Days<span style="color:red">*</span></label>
                  <input required  type="text" class="form-control" name='NoOfDays' id="NoOfDays"
				  placeholder="Enter No Of Days"  value="<?php echo $RowSelect['NoOfDays']?>">
                </div>
				
				<div class="form-group col-lg-3" > 
				<label for="exampleInputEmail1"> Condition<span style="color:red">*</span></label>
                 <select required class="form-control select2" id="ConditionDate" name="ConditionDate" >
				  <option  value="">Select</option>
				  
				  <option  <?php if($RowSelect['ConditionDate']=='GetOut') {?>selected="selected"<?php } ?>
				  value="GetOut">From GetOut Full Date</option>
				 
				  <option  <?php if($RowSelect['ConditionDate']=='BLDate') {?>selected="selected"<?php } ?>
				  value="BLDate">From BL Date</option>
				 <option  <?php if($RowSelect['ConditionDate']=='InvoiceDate') {?>selected="selected"<?php } ?>
				  value="InvoiceDate">From Invoice Date</option>
				 <option  <?php if($RowSelect['ConditionDate']=='OnBoardDate') {?>selected="selected"<?php } ?>
				  value="OnBoardDate">From OnBoard Date</option>
				<option  <?php if($RowSelect['ConditionDate']=='FDADate') {?>selected="selected"<?php } ?>
				  value="FDADate">From FDA Clear Date</option>
				  
				  <option  <?php if($RowSelect['ConditionDate']=='LCDate') {?>selected="selected"<?php } ?>
				  value="LCDate">From LC Date </option>
				  
				  <option  <?php if($RowSelect['ConditionDate']=='DraftDate') {?>selected="selected"<?php } ?>
				  value="LCDate">From Draft Date </option>
				
						</select>
					</div>
              
               <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Short Name</label>
                  <input   type="text" class="form-control" name='ShortName' id="ShortName"
				  placeholder="Enter ShortName"  value="<?php echo $RowSelect['ShortName']?>">
                </div>
				
				<div class="form-group col-lg-6" >
                  <label for="exampleInputEmail1">Payment Name<span style="color:red">*</span></label>
				  
				  <textarea required name="PaymentName" class="form-control" rows="2" cols="50"
                    id="PaymentName"   
					placeholder="Enter Payment Name" ><?php echo $RowSelect['PaymentName'] ?></textarea>
				 		
		
                </div>
				
		         
				
				
				
			            </div>
				
				
				
			  <div class="col-lg-12 box-footer"  align="right">
			     <?php    if($COUNT==0){ ?>
			  <a target="" href="PymntTrmList.php"
       class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-success">Add</button>
				 <?php    } else { ?>
			  <a target="" href="PymntTrmList.php"   class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-success">Update</button>
				 <?php    } ?>

              </div>
            </form>
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

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>
<script>
	jQuery.validator.addMethod("noSpace", function(value, element) {
		return value == '' || value.trim().length != 0;
	}, "No space please and don't leave it empty");

	jQuery.validator.addMethod('selectcheck', function(value) {
		return (value != '0' || value != '');
	}, "year required");

  $("#frmPaymentTerms").validate({

            onkeyup: false,
            onfocusout: false,
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    firstInvalidElement.focus();
                }
            },
            ignore: "",
            rules: {
                PaymentMode: {
                    required: true,
                },
                NoOfDays: {
                    required: true,
					noSpace: true
                },
                ConditionDate: {
                    required: true,
                },
                ShortName: {
                    required: true,
					noSpace: true
                },
                PaymentName: {
                    required: true,
					noSpace: true
                }
            },
            errorPlacement: function() {
                return false;
            },
            // submitHandler: function() {

            //     $.ajax({
            //         url: 'Ajax/ajax-add-container-return-details.php',
            //         type: 'POST',
            //         data: {
            //             poID: $("#hdnPoID").val(),
            //             recievedDate: $("#txtRecievedDate").val(),
            //             returnBillNo: $("#txtReturnBillNo").val(),
            //             containerNo: $("#txtContainerNo").val(),
            //             containerWeight: $("#txtContainerWeight").val(),
            //             returnPort: $("#drpReturnPort").val(),
            //             billEntryNo: $("#txtBillOfEntryNo").val(),
            //             billEntryDate: $("#txtBillOfEntryDate").val(),
            //             remarks: $("#txtRemarks").val()
            //         },
            //         success: function(data) {
            //             console.log(data);
            //             if (data.Error != '') {
            //                 alert(data.Msg);
            //                 window.location.href = "fda-status";
            //             } else {
            //                 alert(data.Error);
            //             }

            //             return false;
            //         }
            //     });

            //     return false;
            // }
        });
</script>       
    </body>
		
</html>


