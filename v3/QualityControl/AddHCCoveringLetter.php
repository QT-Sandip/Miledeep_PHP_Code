<?php

$QueryParms=$_GET['Q']; 
$QueryParm=$_GET['Q']; 
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


$ClientID=$_SESSION["ClientID"];



/*if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }

 $QueryParm;*/
$PoID=$_GET['PoID'];

 $SqlPo="SELECT * FROM  ScrmPoData   WHERE PoID='$PoID'";  

$StmtPo=$iconn->query($SqlPo);

$RowPo=$StmtPo->fetch_array();


$PageTitle="Hc Covering Letter - ".$RowPo['PoNo'];
$PageTitleFolder="Quality Control";


$sqlSamplingLetter="SELECT * FROM QcHCCoveringLetter where InvID='$PoID'";
$stmtSamplingLetter=$iconn->query($sqlSamplingLetter);
$RowSamplingLetter=$stmtSamplingLetter->fetch_array();


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
	$Count=mysqli_num_rows($stmtSamplingLetter);
	if($Count==0){
	 $sql_org_insert="INSERT INTO `QcHCCoveringLetter`(`InvID`, `BLSubmissionDate`, `HealthApplicationNo`, `HealthApplicationDate`) 
	 VALUES('$PoID','$BLSubmissionDate','$HealthApplicationNo','$HealthApplicationDate')";
	}
	else{
	$sql_org_insert="UPDATE `QcHCCoveringLetter` SET `BLSubmissionDate`='$BLSubmissionDate',
	`HealthApplicationNo`='$HealthApplicationNo',`HealthApplicationDate`='$HealthApplicationDate' WHERE `InvID`='$PoID'"	;
	}

	$stmt1=$iconn->query($sql_org_insert);
	
	
	if($stmt1){
		if($Count==0){
			legacyAuditLogInsertById('QcHCCoveringLetter', 'ID', $iconn->insert_id, $_POST, ['Remarks' => 'HC Covering Letter Created'], 'QualityControl');
		} else {
			legacyAuditLogUpdateById('QcHCCoveringLetter', 'ID', $RowSamplingLetter['ID'], $RowSamplingLetter, $_POST, ['Remarks' => 'HC Covering Letter Updated'], 'QualityControl');
		}
		
	header('Location: DocAddReports.php?Q='.$QueryParm);
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

                        <?php include ($IncludesPath."page-title.php");?>
			

      
       <div class="row">
                            <div class="col-12">
                                <div class="card">
								 <form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">
			<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">BL Submission Date </label>
                  <input required type="date" class="form-control" name='BLSubmissionDate' id="BLSubmissionDate"
				  placeholder="Enter Date" value="<?php echo $RowSamplingLetter['BLSubmissionDate']?>">
                </div>
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Health Application Number</label>
                  <input required type="text" class="form-control" name='HealthApplicationNo' id="HealthApplicationNo"
				  placeholder="Enter Health Application Number" value="<?php echo $RowSamplingLetter['HealthApplicationNo']?>">
                </div>
			     <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Health Application Date </label>
                  <input required type="date" class="form-control" name='HealthApplicationDate' id="HealthApplicationDate"
				  placeholder="Enter Date" value="<?php echo $RowSamplingLetter['HealthApplicationDate']?>">
                </div>
                </div>
				  
					

			  <div class="col-xs-12 box-footer"  align="right">
			  			   <a  href="DocAddReports.php?Q=<?php echo $QueryParm ?>" class="btn btn-danger">Close</a>

                <button type="submit"  name="submit"   class="btn btn-success waves-effect waves-light">
				
				Submit</button>
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

                 <?php include ($IncludesPath."footer.php");?>
                
         </div>
            <!-- end main content-->
 </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
      
  
       
    </body>

