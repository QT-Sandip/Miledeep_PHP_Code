<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


$PageTitleFolder="Master";
$PageTitle="Liner Details";

$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));



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
	 INSERT INTO `$ScrmLinerMaster`(`LinerName`, `LinerAddress`,`LinerCode`, `ContactNo`, `AlternateContactNo`, 
	 `EmailID`, `ContainerTrackingUrl`, `Status`)  VALUES 
	 ('$LinerName','$LinerAddress','$LinerCode','$ContactNo','$AlternateContactNo','$EmailID',
	 '$ContainerTrackingUrl','1')";
	

     $stmt1=$iconn->query($sql_org_insert);

     if($stmt1){
        $insertedLinerId = $iconn->insert_id;
        legacyAuditLogInsertById('ScrmLinerMaster', 'LinerID', $insertedLinerId, $_POST, array(
            'ReferenceNo' => $insertedLinerId,
            'Remarks' => 'Liner created',
        ), 'Sales');
     
         header('Location: LinersList.php');
     }
}

else{
	$existingLiner = legacyAuditFetchRowById('ScrmLinerMaster', 'LinerID', $LinerID, 'Sales');
	$sql_org_insert="
	UPDATE `ScrmLinerMaster` SET `LinerName`='$LinerName',`LinerAddress`='$LinerAddress',`LinerCode`='$LinerCode',
	`ContactNo`='$ContactNo',`AlternateContactNo`='$AlternateContactNo',
	`EmailID`='$EmailID',`ContainerTrackingUrl`='$ContainerTrackingUrl' WHERE LinerID='$LinerID'";

    $stmt1=$iconn->query($sql_org_insert);

	if($stmt1){
		legacyAuditLogUpdateById('ScrmLinerMaster', 'LinerID', $LinerID, $existingLiner, $_POST, array(
			'ReferenceNo' => $LinerID,
			'Remarks' => 'Liner updated',
		), 'Sales');

        $ArgumentsArray[PostedUserId]=$PostedUserId;
            $ArgumentsArray[PageUrl]=$PageUrl;
            $ArgumentsArray[Remarks]=$Remarks;
            $ArgumentsArray[ReferenceID]=$LinerID;
            postUserEditLog($ArgumentsArray);
	
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
                  <label for="exampleInputEmail1">Liner Name<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='LinerName' id="LinerName" 
				  placeholder="Enter Liner Name" value="<?php echo $RowLiner['LinerName'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Liner Address<span style="color:red">*</span></label>
<input  required type="text" class="form-control" name='LinerAddress' id="LinerAddress" 
				  placeholder=" Enter Liner Address" value="<?php echo $RowLiner['LinerAddress'] ?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Liner Code</label>
<input   type="text" class="form-control" name='LinerCode' id="LinerCode" 
				  placeholder=" Enter Liner Code" value="<?php echo $RowLiner['LinerCode'] ?>">
                </div> 
				
				
				
				
			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Contact No</label>
<input   type="number" class="form-control" name='ContactNo' id="ContactNo" 
				  placeholder=" Enter Contact No" value="<?php echo $RowLiner['ContactNo'] ?>">
                </div> 	
				 
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Alternate Contact No</label>
<input   type="number" class="form-control" name='AlternateContactNo' id="AlternateContactNo" 
				  placeholder="Enter Alternate Contact No" value="<?php echo $RowLiner['AlternateContactNo'] ?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Email ID</label>
				<input  type="text" class="form-control" name='EmailID' id="EmailID" 
				  placeholder="Enter Email ID" value="<?php echo $RowLiner['EmailID'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Container Tracking Url<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='ContainerTrackingUrl' id="ContainerTrackingUrl" 
				  placeholder="Enter Container Tracking Url" value="<?php echo $RowLiner['ContainerTrackingUrl'] ?>">		
		
                </div>
                </div>
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="LinersList.php"
       class="btn btn-danger  "> Close</a>
			     <?php    if($Count==0){ ?>
  
                <button type="submit"  name='submit' class="btn btn-success">ADD</button>
				 <?php    } else { ?>
 
                <button type="submit"  name='submit' class="btn btn-success">Update</button>
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

