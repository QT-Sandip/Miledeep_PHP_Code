<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="SalesNew";
$PageTitle="New Payment Terms";


$ID=$_GET['ID'];

$SqlSelect="SELECT * FROM `$TblScrmPaymentTerms` WHERE ID='$ID'";
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
	$SqlInsert="INSERT INTO `$TblScrmPaymentTerms`( `PaymentName`, `PaymentMode`, `LcStatus`) 
				VALUES ('$PaymentName','$PaymentMode','$LcStatus')";
	

	$stmt=$iconn->query($SqlInsert);
	//$ID = $iconn->insert_id;
  }else{
		$oldPymntData = legacyAuditFetchRowById($TblScrmPaymentTerms, 'ID', $ID, 'Admin');
	   $SqlInsert="UPDATE `$TblScrmPaymentTerms` SET 
	  `PaymentName`= '$PaymentName',
	  `PaymentMode`= '$PaymentMode',
	  `LcStatus`= '$LcStatus' WHERE ID='$ID'"; 
	  
	$stmt=$iconn->query($SqlInsert);
	
	}
	if($stmt){
		if($COUNT==0){
			legacyAuditLogInsertById($TblScrmPaymentTerms, 'ID', $iconn->insert_id, $_POST, ['Remarks' => 'Payment term created'], 'Admin');
		} else {
			legacyAuditLogUpdateById($TblScrmPaymentTerms, 'ID', $ID, $oldPymntData, $_POST, ['Remarks' => 'Payment term updated'], 'Admin');
		}
		
		echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "PymntTrmList.php";
		</script>';
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
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">
	 
				
				 <div class="form-group col-lg-3" > 
				<label for="exampleInputEmail1">Payment Mode</label>
                 <select class="form-control select2" id="PaymentMode" name="PaymentMode" >
				  <option  value="">Select</option>
				  <option <?php if($RowSelect['PaymentMode']=='LC') {?>selected="selected"<?php } ?>
				  
				  value="LC">LC</option>
				  <option  <?php if($RowSelect['PaymentMode']=='Non LC') {?>selected="selected"<?php } ?>
				  value="Non LC">Non LC</option>
				
				
						</select>
					</div>
				
              
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Payment Name</label>
                  <input required type="text" class="form-control" name='PaymentName' id="PaymentName"
				  placeholder="Enter Payment Name"  value="<?php echo $RowSelect['PaymentName']?>">
                </div>
			            </div>
				
				
				
			  <div class="col-lg-12 box-footer"  align="right">
			     <?php    if($COUNT==0){ ?>
			  <a target="" href="PymntTrmList.php"
       class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-primary">ADD</button>
				 <?php    } else { ?>
			  <a target="" href="PymntTrmList.php"   class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-primary">Update</button>
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
		
  
       
    </body>
		


