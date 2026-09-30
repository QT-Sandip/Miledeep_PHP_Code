<?php
//$Status=$_SESSION['Status']; 
session_start();

$QueryParms=$_GET['Q'];  
$QueryParm=$_GET['Q']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
  

$PoID=$_GET['PoID'];

 $SqlPo="SELECT * FROM `$TblScrmPoData` where PoID='$PoID'"; 
$StmtPo=$iconn->query($SqlPo);
$RowPo=$StmtPo->fetch_array();

$PageTitle="DS Report - ".$RowPo['PoNo'];
$PageTitleFolder="Quality Control";

$SqlDspReport="SELECT * FROM `$TblQcDspReports` WHERE InvID='$PoID'";

$StmtDspReport=$iconn->query($SqlDspReport);

$RowDspReport=$StmtDspReport->fetch_array();

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
	$Count=mysqli_num_rows($StmtDspReport);
	 if($Count==0){
	
	
 	 $SqlUpdate="INSERT INTO `$TblQcDspReports`(`InvID`, `ExportDate`, `ReportDate`,`ExpiryDate`) VALUES ('$PoID','$ExportDate','$ReportDate','$ExpiryDate')";
		
	$StmtUpdate=$iconn->query($SqlUpdate);
	if($StmtUpdate){
		legacyAuditLogInsertById($TblQcDspReports, 'ID', $iconn->insert_id, $_POST, ['Remarks' => 'DS Report Created'], 'QualityControl');
	}
	}else{
		
	 $oldRow = legacyAuditFetchRowById($TblQcDspReports, 'InvID', $PoID, 'QualityControl');
	 $SqlUpdate="UPDATE `$TblQcDspReports` SET `ExportDate`='$ExportDate',`ReportDate`='$ReportDate',`ExpiryDate` ='$ExpiryDate'
	WHERE InvID='$PoID' ";
		
	$StmtUpdate=$iconn->query($SqlUpdate);
	if($StmtUpdate){
		legacyAuditLogUpdateById($TblQcDspReports, 'InvID', $PoID, $oldRow, $_POST, ['Remarks' => 'DS Report Updated'], 'QualityControl');
	}
	}
   IF($StmtUpdate){
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
                  <label for="exampleInputEmail1">Date of Export</label>
                  <input type="date" class="form-control" name='ExportDate' id="ExportDate" 
				  placeholder="Enter Date of Export " value="<?php echo $RowDspReport['ExportDate'] ?>"
				    required>
                </div>  
				 
				  <div class="form-group col-lg-3" >

                  <label for="exampleInputEmail1">Date Of Submission</label>
                  <input type="date" class="form-control" name='ReportDate' id="ReportDate" 
				  placeholder="Enter Date Of Submission" value="<?php echo $RowDspReport['ReportDate'] ?>"
				    required>
                </div>  
                 
				  <div class="form-group col-lg-3" >

                  <label for="exampleInputEmail1">Date Of Expire</label>
                  <input type="date" class="form-control" name='ExpiryDate' id="ExpiryDate" 
				  placeholder="Enter Date Of Expire" value="<?php echo $RowDspReport['ExpiryDate'] ?>"
				    required>
                </div>
				                 </div>
				
				
				

				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			  			  <a  href="DocAddReports.php?Q=<?php echo $QueryParm ?>" class="btn btn-danger">Close</a>

                <button type="submit"  name='submit' class="btn btn-success">Submit</button>
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
</html>				

