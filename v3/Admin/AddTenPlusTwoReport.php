<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="SalesNew";
$PageTitle="10+2 Report Creation";


$CID=$_GET['ID'];
 $PIId=$_GET['PIID'];


	

$SqlLogistics="SELECT * FROM `$ScrmLogisticsMaster`";
		$StmtLogistics=$iconn->query($SqlLogistics);
		
		$SqlLiner="SELECT * FROM `$ScrmLinerMaster` WHERE Status=1";
$StmtLiner=$iconn->query($SqlLiner);

$SqlCount="SELECT * FROM `$ScrmLogisticsDetails` WHERE PiID='$PIId'";
		$StmtCount=$iconn->query($SqlCount);
					$Count=mysqli_num_rows($StmtCount);				
		$RowTenPlusTwo=$StmtCount->fetch_array();


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	  if($Count==0){

       $SqlInsert="INSERT INTO `$ScrmLogisticsDetails`(`PiID`, `VesselName`, `VOY`, `VesselDepartureDate`, `ImportingVessel`, `ImportingVOY`, 
	   `SteamshipCompany`, `ConsolidatorNameAddress`, `ShippingToNameAddress`, `VesselNumber`)VALUES ('$PIId','$VesselName','$VOY','$VesselDepartureDate','$ImportingVessel','$ImportingVOY',
	'$SteamshipCompany','$ConsolidatorNameAddress','$ShippingToNameAddress','$VesselNumber')";
		$StmtInsert=$iconn->query($SqlInsert);
	
	if($StmtInsert){
		legacyAuditLogInsertById($ScrmLogisticsDetails, 'ID', $iconn->insert_id, array(), array(), 'Admin');
		
	echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "DocAddReports.php?PoID='.$PIId.'";
        </script>';
	}	 
			 
			 }
			 else{
		$oldLogisticsRow = legacyAuditFetchRowById($ScrmLogisticsDetails, 'PiID', $PIId, 'Admin');
			$SqlUpdate= "UPDATE `$ScrmLogisticsDetails` SET `VesselName`='$VesselName',`VOY`='$VOY',
			`VesselDepartureDate`='$VesselDepartureDate',`ImportingVessel`='$ImportingVessel',`ImportingVOY`='$ImportingVOY',`SteamshipCompany`='$SteamshipCompany',
			`ConsolidatorNameAddress`='$ConsolidatorNameAddress',`ShippingToNameAddress`='$ShippingToNameAddress'
			,`VesselNumber`='$VesselNumber' WHERE `PiID`='$PIId'";
			 $StmtUpdate=$iconn->query($SqlUpdate);
			 
	
	if($StmtUpdate){
		legacyAuditLogUpdateById($ScrmLogisticsDetails, 'PiID', $PIId, $oldLogisticsRow, array(), array(), 'Admin');
		
	echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "DocAddReports.php?PoID='.$PIId.'";
        </script>';
	
		//header('Location: PIList.php');
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

    
                 	<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Vessel Name</label>
				<input required type="text" class="form-control" name='VesselName' id="VesselName" 
				  placeholder="Enter Vessel Name" value="<?php echo $RowTenPlusTwo['VesselName']?>">		
		
                </div>
				
			<!--	<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Vessel Number</label>
				<input  type="text" class="form-control" name='VesselNumber' id="VesselNumber" 
				  placeholder="Enter Vessel Number" value="<?php echo $RowTenPlusTwo['VesselNumber']?>">		
		
                </div>-->
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">VOY</label>
<input  required type="text" class="form-control" name='VOY' id="VOY" 
				  placeholder="Enter VOY " value="<?php echo $RowTenPlusTwo['VOY']?>">
                </div>


					<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Departure Date</label>
				<input  type="date" class="form-control" name='VesselDepartureDate' id="VesselDepartureDate" 
				  placeholder="Enter Discount" value="<?php echo $RowTenPlusTwo['VesselDepartureDate']?>">		
		
                </div>
				
					
					
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Final Importing Vessel Name</label>
				<input  type="text" class="form-control" name='ImportingVessel' id="ImportingVessel" 
				  placeholder="Enter Final Importing Vessel" value="<?php echo $RowTenPlusTwo['ImportingVessel']?>">		
		
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Importing VOY</label>
				<input  type="text" class="form-control" name='ImportingVOY' id="ImportingVOY" 
				  placeholder="Enter Importing VOY" value="<?php echo $RowTenPlusTwo['ImportingVOY']?>">		
		
                </div>
				
			<!--	<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Steamship Company</label>
				<input  type="text" class="form-control" name='SteamshipCompany' id="SteamshipCompany" 
				  placeholder="Enter Steamship Company" value="<?php echo $RowTenPlusTwo['SteamshipCompany']?>">	
</div>	-->

                  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Steamship Company</label>
                 
                <select class="form-control select2" name="SteamshipCompany" id="SteamshipCompany">
									  <option   value="">Select</option>

					 <?php while ($RowLiner=$StmtLiner->fetch_array()){
						$Name= $RowLiner['LinerName'];
						$VALUE= $RowLiner['LinerID'];
						 ?>
			 
					<option value="<?php echo $VALUE ?>"<?php if( $VALUE==$RowTenPlusTwo['SteamshipCompany'])
					  echo 'selected="selected"'; ?>><?php echo $Name;?></option>
					<?php  }?>
				<select>
				
				</div>
				
			<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Ship To Name</label>
				<input  type="text" class="form-control" name='ShippingToNameAddress' id="ShippingToNameAddress" 
				  placeholder="Enter Ship To Name" value="<?php echo $RowTenPlusTwo['ShippingToNameAddress']?>">	
</div>		
		<!--	<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">ETD</label>
				<input  type="date" class="form-control" name='ETD' id="ETD" 
				  placeholder="Enter Discount" value="<?php echo $RowTenPlusTwo['ETD']?>">		
		
                </div><div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">ETA</label>
				<input  type="date" class="form-control" name='ETA' id="ETA" 
				  placeholder="Enter Discount" value="<?php echo $RowTenPlusTwo['ETA']?>">		
		
                </div>	-->
		
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Consolidator</label>
                 
                <select class="form-control select2" name="ConsolidatorNameAddress" id="ConsolidatorNameAddress">
									  <option   value="">Select</option>

					 <?php while ($RowLogistics=$StmtLogistics->fetch_array()){
						$Name= $RowLogistics['Name'];
						$VALUE= $RowLogistics['ID'];
						 ?>
			 
					<option value="<?php echo $VALUE ?>"<?php if( $VALUE==$RowTenPlusTwo['ConsolidatorNameAddress'])
					  echo 'selected="selected"'; ?>><?php echo $Name;?></option>
					<?php  }?>
				<select>
				
				</div>
				</div>
				
				
					
				
				
								<?php if($Count==0){	 ?>
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="DocAddReports.php?PoID=<?php echo $PIId ?>"
       class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-primary">Add</button>

              </div>
					<?php } else{	 ?>
					
			  <div class="col-xs-12 box-footer"  align="right">
<a target="" href="DocAddReports.php?PoID=<?php echo $PIId ?>"
       class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-primary">Update</button>



              </div>
					<?php } ?>

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

