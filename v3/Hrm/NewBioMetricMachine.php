<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$SqlDeviceCount="SELECT MaxBioMetricMachines,(select count(*) from HrmBioMetricDevices)as MaxCount FROM `AdminConfigDetails`";
 $StmtDeviceCount=$iconn->query($SqlDeviceCount);
 
// ECHO $SqlDeviceCount; EXIT;
  $RowDeviceCount=$StmtDeviceCount->fetch_array();
  $MaxBioMetricMachines=$RowDeviceCount['MaxBioMetricMachines']; 
  $MaxCount=$RowDeviceCount['MaxCount']; 
  if($MaxCount >=$MaxBioMetricMachines){
	$PageTitle="Add Bio Metric machine - (Maximum Limit Reached)";
	
  
  }
		
       
else{
$PageTitle="Add Bio Metric machine";}
$PageTitleFolder="HRM";

$BioMetricID=$_GET['id'];



 


  
	  $BioMetricID =$_GET['BM_ID'];
	  
	   $SqlBioMetricList="select * from HrmBioMetricDevices where ID='$BioMetricID'  ";
             $StmtBioMetricList=$iconn->query($SqlBioMetricList);
             $RowBioMetricList=$StmtBioMetricList->fetch_array();
		
extract($_POST);
extract($_GET);
	  
        if(isset($_POST['submitt'])){
           
     if($BioMetricID==''){
	 
	  $SqlInsert="INSERT INTO `HrmBioMetricDevices`( `IPAddress`, `DeviceLocation`, `DeviceType`) 
	  VALUES ('$IPAddress','$Location','$DeviceType')";
	  
	
	 }
	 else{
	 
			 $SqlInsert="UPDATE `HrmBioMetricDevices` SET `IPAddress`='$IPAddress',`DeviceLocation`='$Location',
			 `DeviceType`='$DeviceType'  WHERE ID='$BioMetricID'";

	 $SqlInsert;
	
	 }
	  
	$stmt1=$iconn->query($SqlInsert);
	if($stmt1){
	if($BioMetricID==''){
	legacyAuditLogInsertById('HrmBioMetricDevices', 'ID', $iconn->insert_id, $_POST, array(), 'Hrm');
	}else{
	$oldBioData = legacyAuditFetchRowById('HrmBioMetricDevices', 'ID', $BioMetricID, 'Hrm');
	legacyAuditLogUpdateById('HrmBioMetricDevices', 'ID', $BioMetricID, $oldBioData, $_POST, array(), 'Hrm');
	}
	}
	header("Location:NewBioMetricMachine.php");
		}
	  
	   ?>

  <!DOCTYPE html>

<html lang="en">

    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php  include ("Includes/DbLinks.php");?>
        <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

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
			
<!--<h2 align="center">Add Bio Metric machine</h2>-->

      
       <div class="row">
                            <div class="col-12">
                                <div class="card">
																 <form role="form" method="post">

                                    <div class="card-body">
									
										<div class="row">
			

			
             <div class="form-group col-lg-12">
                  <label for="exampleInputPassword1"> <U> Biometric Machine Details </U> </label>
                  
                </div>
	
               <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Machine IP Address</label>
                  <input required   type="text" class="form-control" name="IPAddress" id="IPAddress"
				  placeholder="Enter Machine IP Address" value="<?php echo $RowBioMetricList['IPAddress']?>" >
                </div>
			  
			   <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">  Machine Location</label>
                  <input   required type="text" class="form-control" name="Location" id="Location" 
				  placeholder="Enter Machine Location" value="<?php echo $RowBioMetricList['Location']?>">
                </div>

               <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Machine Type</label>
                   <select class="form-control select2 " name="DeviceType" id="DeviceType"  required>
				  <option >CHECK IN</option>
				  <option >CHECK OUT</option>
				 
				  <select>
                </div>
                </div>
				<div class="col-xs-12 box-footer"  align="right">
					 <a target="" href="BioMetricDeviceList.php"   class="btn btn-danger  "> Close</a>
					<?php  if($MaxCount < $MaxBioMetricMachines){ ?>
					<button type="submitt"  name='submitt' class="btn btn-success waves-effect waves-light">Submit</button>
					<?php } ?>
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
		<?php include ("Includes/NewScripts.php");?>
      

       
    </body>
</html>				
