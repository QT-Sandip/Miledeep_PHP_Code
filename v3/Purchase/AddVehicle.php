<?php
$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PageTitle="Add New Vehicle";

$PageTitleFolder="Purchase";



extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
	
	$SqlVehicalDetailsInsert="INSERT INTO `RmVehicalDetails`(
					 `VehicalNumber`, `VehicalCapacity`, `VehicalMake`
					) 
				VALUES ('$VehicalNumber','$VehicalCapacity','$VehicalMake')";
	

	$StmtVehicalDetailsInsert=$iconn->query($SqlVehicalDetailsInsert);
	
	if($StmtVehicalDetailsInsert){
		$insertedVehicleId = $iconn->insert_id;
		if (empty($insertedVehicleId)) {
			$SqlLatestVehicle = "SELECT `ID` FROM `RmVehicalDetails`
			WHERE `VehicalNumber`='$VehicalNumber' AND `VehicalCapacity`='$VehicalCapacity' AND `VehicalMake`='$VehicalMake'
			ORDER BY `ID` DESC LIMIT 1";
			$StmtLatestVehicle = $iconn->query($SqlLatestVehicle);
			if ($StmtLatestVehicle && $StmtLatestVehicle->num_rows > 0) {
				$RowLatestVehicle = $StmtLatestVehicle->fetch_array();
				$insertedVehicleId = $RowLatestVehicle['ID'];
			}
		}

		if (!empty($insertedVehicleId)) {
			legacyAuditLogInsertById('RmVehicalDetails', 'ID', $insertedVehicleId, array(
				'VehicalNumber' => $VehicalNumber,
				'VehicalCapacity' => $VehicalCapacity,
				'VehicalMake' => $VehicalMake,
			), array(
				'ReferenceNo' => $insertedVehicleId,
				'Remarks' => 'Vehicle created from legacy purchase screen',
			), 'Purchase');
		}
		
		
		echo "<script>alert('Success');</script>";
		
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
                  <label for="exampleInputEmail1">Vehicle Make</label>
                  <input required type="text" class="form-control" name='VehicalMake' id="VehicalMake"
				  placeholder="Enter Manufacturer">
                </div>
				
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Vehicle Number</label>
                  <input type="text" class="form-control"  name="VehicalNumber" id="VehicalNumber" 
				  placeholder="Enter Vehicle Number">
                </div>
				
				
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Vehicle Capacity</label>
                  <input type="text" class="form-control"  name="VehicalCapacity" id="VehicalCapacity" 
				  placeholder="Enter Vehicle Capacity(in Tones) ">
                </div>
                </div>
				
				
				

				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
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

