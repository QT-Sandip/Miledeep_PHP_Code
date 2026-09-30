<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include "Constants.php";

$PageTitleFolder="Production";
$PageTitle="New Grades";




extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	 
	 
	$SqlCount="SELECT * FROM `ProductionGrades` WHERE ID='$PckID'";
	$StmtCount=$iconn->query($SqlCount);
	$Count=mysqli_num_rows($StmtCount);
	
	if($Count==0){
	 $sql_org_insert="
	 INSERT INTO `ProductionGrades`
	 (`DeptId`, `Type`, `value`, `Options`)  VALUES 
	 ('5','GRADES','$value','$value')";
	

	$stmt1=$iconn->query($sql_org_insert);
	$MB_REPORT_ID = $iconn->insert_id;
//echo $iid;
	if($stmt1){
		legacyAuditLogInsertById('ProductionGrades', 'ID', $MB_REPORT_ID, $_POST, [
			'ReferenceNo' => $MB_REPORT_ID,
			'Remarks' => 'Grade created',
		], 'Production');
		header('Location: GradesList.php');
	}
	}
	else{
		echo '<script language="javascript" type="text/javascript"> 
                alert("Packing Style Already Exists");
                window.location = "CreateGrades.php";
        </script>';
			//header('Location: CreatePackingStyles.php');
	
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
                  <label for="exampleInputEmail1">Grade</label>
				<input required type="text" class="form-control" name='value' id="value" 
				  placeholder="Enter Grades" value="">		
		
                </div>
				
	 
                </div>
				
				
			<div class="col-xs-12 box-footer"  align="right">
					<!--	  <a target="" href="PackingStyleList.php"   class="btn btn-danger  "> Close</a>
-->
                <button type="submit"  name='submit' class="btn btn-primary">Submit</button>
              </div>
				
				 
				
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
