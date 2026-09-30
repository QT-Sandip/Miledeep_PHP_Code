<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="SalesNew";
$PageTitle="Liner Details";


$LinerID=$_GET['id'];
$SqlLiner="SELECT * FROM `$ScrmLinerMaster` WHERE LinerID='$LinerID'";
$StmtLiner=$iconn->query($SqlLiner);
$RowLiner=$StmtLiner->fetch_array();
$Count=mysqli_num_rows($StmtLiner);

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	     if($Count==0){

	 $sql_org_insert="
	 INSERT INTO `$ScrmLinerMaster`(`LinerName`, `LinerAddress`, `ContactNo`, `AlternateContactNo`, 
	 `EmailID`, `ContainerTrackingUrl`, `Status`)  VALUES 
	 ('$LinerName','$LinerAddress','$ContactNo','$AlternateContactNo','$EmailID',
	 '$ContainerTrackingUrl','1')";
	

	$stmt1=$iconn->query($sql_org_insert);
	
	if($stmt1){
		$newLinerId = $iconn->insert_id;
		legacyAuditLogInsertById($ScrmLinerMaster, 'LinerID', $newLinerId, $_POST, ['Remarks' => 'Liner created'], 'Admin');
		header('Location: AddLiner.php');
	}
}

else{
	$oldLiner = legacyAuditFetchRowById($ScrmLinerMaster, 'LinerID', $LinerID, 'Admin');
	$sql_org_insert="
	UPDATE `ScrmLinerMaster` SET `LinerName`='$LinerName',`LinerAddress`='$LinerAddress',
	`ContactNo`='$ContactNo',`AlternateContactNo`='$AlternateContactNo',
	`EmailID`='$EmailID',`ContainerTrackingUrl`='$ContainerTrackingUrl' WHERE LinerID='$LinerID'";
	
	  
	$stmt1=$iconn->query($sql_org_insert);

	if($stmt1){
		legacyAuditLogUpdateById($ScrmLinerMaster, 'LinerID', $LinerID, $oldLiner, $_POST, ['Remarks' => 'Liner updated'], 'Admin');
		header('Location: LinersList.php');
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
                  <label for="exampleInputEmail1">Liner Name</label>
				<input required type="text" class="form-control" name='LinerName' id="LinerName" 
				  placeholder="Enter Liner Name" value="<?php echo $RowLiner['LinerName'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Liner Address</label>
<input  required type="text" class="form-control" name='LinerAddress' id="LinerAddress" 
				  placeholder=" Enter Liner Address" value="<?php echo $RowLiner['LinerAddress'] ?>">
                </div> 
				
				
				
				
			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Contact No</label>
<input  required type="number" class="form-control" name='ContactNo' id="ContactNo" 
				  placeholder=" Enter Contact No" value="<?php echo $RowLiner['ContactNo'] ?>">
                </div> 	
				 
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Alternate Contact No</label>
<input   type="number" class="form-control" name='AlternateContactNo' id="AlternateContactNo" 
				  placeholder=" Enter Alternate Contact No" value="<?php echo $RowLiner['AlternateContactNo'] ?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Email ID</label>
				<input required type="text" class="form-control" name='EmailID' id="EmailID" 
				  placeholder="Enter Email ID" value="<?php echo $RowLiner['EmailID'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Container Tracking Url</label>
				<input required type="text" class="form-control" name='ContainerTrackingUrl' id="ContainerTrackingUrl" 
				  placeholder="Enter Container Tracking Url" value="<?php echo $RowLiner['ContainerTrackingUrl'] ?>">		
		
                </div>
                </div>
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			     <?php    if($Count==0){ ?>
  <a target="" href="LinersList.php"
       class="btn btn-danger  "> Close</a>
                <button type="submit"  name='submit' class="btn btn-primary">ADD</button>
				 <?php    } else { ?>
  <a target="" href="LinersList.php"
       class="btn btn-danger  "> Close</a>
                <button type="submit"  name='submit' class="btn btn-primary">Update</button>
				 <?php    } ?>

              </div>	</div>
				
				
					
				
				
								

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
</html>				

