<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="SalesNew";
$PageTitle="Bank Details";


$BankID=$_GET['id'];
$SqlBank="SELECT * FROM `$ScrmBankMaster` WHERE BankID='$BankID'";
$StmtBank=$iconn->query($SqlBank);
$RowBank=$StmtBank->fetch_array();
$Count=mysqli_num_rows($StmtBank);

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	  if($Count==0){
	 $sql_org_insert="
	 INSERT INTO `$ScrmBankMaster`(`BankName`, `BankAddress`, `ContactNo`, `EmailID`, `BranchCode`, `City`,`ADCode`, `Status`)  VALUES 
	 ('$BankName','$BankAddress','$ContactNo','$EmailID', '$BranchCode','$City','$ADCode','1')";
	
	  
	$stmt1=$iconn->query($sql_org_insert);
	

	if($stmt1){
		$newBankId = $iconn->insert_id;
		legacyAuditLogInsertById($ScrmBankMaster, 'BankID', $newBankId, $_POST, ['Remarks' => 'Bank created'], 'Admin');
		header('Location: AddBank.php');
	}
}

else{
	$oldBank = legacyAuditFetchRowById($ScrmBankMaster, 'BankID', $BankID, 'Admin');
	$sql_org_insert="
	UPDATE `$ScrmBankMaster` SET `BankName`='$BankName',`BankAddress`='$BankAddress',
	`ContactNo`='$ContactNo',`EmailID`='$EmailID',
	`BranchCode`='$BranchCode',`City`='$City',`ADCode`='$ADCode' WHERE BankID='$BankID'";
	
	  
	$stmt1=$iconn->query($sql_org_insert);

	if($stmt1){
		legacyAuditLogUpdateById($ScrmBankMaster, 'BankID', $BankID, $oldBank, $_POST, ['Remarks' => 'Bank updated'], 'Admin');
		header('Location: BanksList.php');
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
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">
	<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Bank Name</label>
				<input required type="text" class="form-control" name='BankName' id="BankName" 
				  placeholder="Enter Bank Name" value="<?php echo $RowBank['BankName'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Bank Address</label>
<input  required type="text" class="form-control" name='BankAddress' id="BankAddress" 
				  placeholder=" Enter Bank Address" value="<?php echo $RowBank['BankAddress'] ?>">
                </div> 
				
				
				
			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Contact No</label>
<input  required type="number" class="form-control" name='ContactNo' id="ContactNo" 
				  placeholder=" Enter Contact No" value="<?php echo $RowBank['ContactNo'] ?>">
                </div> 	
				 
				
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Email ID</label>
				<input required type="text" class="form-control" name='EmailID' id="EmailID" 
				  placeholder="Enter Email ID" value="<?php echo $RowBank['EmailID'] ?>">		
		
                </div>

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Branch Code</label>
				<input required type="text" class="form-control" name='BranchCode' id="BranchCode" 
				  placeholder="Enter Branch Code" value="<?php echo $RowBank['BranchCode'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">AD Code</label>
				<input required type="text" class="form-control" name='ADCode' id="ADCode" 
				  placeholder="Enter AD Code" value="<?php echo $RowBank['ADCode'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">City</label>
				<input required type="text" class="form-control" name='City' id="City" 
				  placeholder="Enter City" value="<?php echo $RowBank['City'] ?>">		
		
                </div>
                </div>
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			     <?php    if($Count==0){ ?>
			  <a target="" href="BanksList.php"
       class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-primary">ADD</button>
				 <?php    } else { ?>
			  <a target="" href="BanksList.php"   class="btn btn-danger  "> Close</a>
  		
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
		


