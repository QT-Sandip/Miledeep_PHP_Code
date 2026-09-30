<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="SalesNew";
$PageTitle="Cargo Dispatch Details";


$PIId=$_GET['PIID'];



extract($_POST);
extract($_GET);
$SqlShipmentDispatchCount="SELECT * FROM `$ScrmShipmentDispatchDetails` WHERE PIID='$PIId'";
		$StmtShipmentDispatchCount=$iconn->query($SqlShipmentDispatchCount);
					$ShipmentDispatchCount=mysqli_num_rows($StmtShipmentDispatchCount);				
		$RowShipmentDispatch=$StmtShipmentDispatchCount->fetch_array();



if(isset($_POST['submit'])){
	
	
		
	
	IF($ShipmentDispatchCount ==0){
		
	  $sqlShipmentDispatch="
	 INSERT INTO `$ScrmShipmentDispatchDetails`(`PIID`,
	 `ETD`, `ETA`, `BLDate`, `SBNo`, `SBDate`, `SealTime`,`BLNo`)  
	 VALUES ('$PIId','$ETD','$ETA','$BLDate',
	 '$SBNo', '$SBDate','$SealTime','$BLNo')";
	
	}ELSE{
	$oldDispatchData = legacyAuditFetchRowById($ScrmShipmentDispatchDetails, 'PIID', $PIId, 'Admin');
	$sqlShipmentDispatch="
	UPDATE `$ScrmShipmentDispatchDetails` SET 
	ETD='$ETD',
	ETA='$ETA',
	BLDate='$BLDate',
	SBNo='$SBNo',
	SBDate='$SBDate',
	SealTime='$SealTime',
	BLNo='$BLNo' WHERE PIID='$PIId'";
	}
	$stmtShipmentDispatch=$iconn->query($sqlShipmentDispatch);
	

	if($stmtShipmentDispatch){
		if($ShipmentDispatchCount ==0){
			legacyAuditLogInsertById($ScrmShipmentDispatchDetails, 'PIID', $PIId, $_POST, ['Remarks' => 'Cargo dispatch created'], 'Admin');
		} else {
			legacyAuditLogUpdateById($ScrmShipmentDispatchDetails, 'PIID', $PIId, $oldDispatchData, $_POST, ['Remarks' => 'Cargo dispatch updated'], 'Admin');
		}
		
	echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "DocAddReports.php?PoID='.$PIId.'";
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
	 
<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">

    
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">ETA</label>
              <input  required type="date" class="form-control" name='ETA' id="ETA" 
				  placeholder=" Enter ETA" value="<?php echo $RowShipmentDispatch['ETA']; ?>">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">ETD</label>
				<input required type="date" class="form-control" name='ETD' id="ETD" 
				  placeholder="Enter ETD" value="<?php echo $RowShipmentDispatch['ETD']; ?>">		
		
                </div>
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">BL No</label>
				<input required type="text" class="form-control" name='BLNo' id="BLNo" 
				  placeholder="Enter BL Number" value="<?php echo $RowShipmentDispatch['BLNo']; ?>">		
		
                </div>
				
			  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">BL Date</label>
                  <input  required type="date" class="form-control"
				  name='BLDate' id="BLDate" placeholder=" Enter BLDate" value="<?php echo $RowShipmentDispatch['BLDate']; ?>">
                </div> 	
				 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">SB No</label>
				<input required type="text" class="form-control" name='SBNo' id="SBNo" 
				  placeholder="Enter SB Number" value="<?php echo $RowShipmentDispatch['SBNo']; ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">SB Date</label>
				<input required type="date" class="form-control" name='SBDate' id="SBDate" 
				  placeholder="Enter SB Date" value="<?php echo $RowShipmentDispatch['SBDate']; ?>">		
		
                </div>
				
				

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">SealTime</label>
				<input type="time" class="form-control" name='SealTime' id="SealTime" 
				  placeholder="Enter Seal Time" value="<?php echo $RowShipmentDispatch['SealTime']; ?>">		
		
                </div> 
				
				
                </div>
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="DocAddReports.php?PoID=<?php echo $PIId ?>"
       class="btn btn-danger  "> Close</a>
                <button type="submit"  name='submit' class="btn btn-primary">ADD</button>
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

