<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="Sales";
$PageTitle="Shipping Bill";


$PIId=$_GET['PIID'];



extract($_POST);
extract($_GET);
$SqlShipmentDispatchCount="SELECT * FROM `$ScrmShipmentDispatchDetails` WHERE PIID='$PIId'";
		$StmtShipmentDispatchCount=$iconn->query($SqlShipmentDispatchCount);
					$ShipmentDispatchCount=mysqli_num_rows($StmtShipmentDispatchCount);				
		$RowShipmentDispatch=$StmtShipmentDispatchCount->fetch_array();



if(isset($_POST['submit'])){
	$existingShipmentDispatch = null;
	if($ShipmentDispatchCount != 0){
		$existingShipmentDispatch = legacyAuditFetchRowById('ScrmShipmentDispatchDetails', 'PIID', $PIId, 'Sales');
	}
	
	
		
	
	IF($ShipmentDispatchCount ==0){
		
	  $sqlShipmentDispatch="
	 INSERT INTO `ScrmShipmentDispatchDetails`(`PIID`, `SBNo`, `SBDate`, `LEODate`, `SBInvValINR`, `RotationNo`, `RotationDate`, `DutyDrawBackAmt`, `MPEDADtls`, `OriginalRecd`, `OtherDeductionsUSD`)  
	 VALUES ('$PIId','$SBNo', '$SBDate','$LEODate', '$SBInvValINR','$RotationNo', '$RotationDate','$DutyDrawBackAmt', '$MPEDADtls', '$OriginalRecd', '$OtherDeductionsUSD')";
	
	}ELSE{
		
	$sqlShipmentDispatch="
	UPDATE `ScrmShipmentDispatchDetails` SET 
	SBNo='$SBNo',
	SBDate='$SBDate',
	LEODate='$LEODate',
	SBInvValINR='$SBInvValINR',
	RotationNo='$RotationNo',
	RotationDate='$RotationDate',
	DutyDrawBackAmt='$DutyDrawBackAmt',
	MPEDADtls='$MPEDADtls',
	OriginalRecd='$OriginalRecd',
	OtherDeductionsUSD='$OtherDeductionsUSD'
	 WHERE PIID='$PIId'";
	}
	$stmtShipmentDispatch=$iconn->query($sqlShipmentDispatch);
	

	if($stmtShipmentDispatch){
		if($ShipmentDispatchCount == 0){
			legacyAuditLogInsertById('ScrmShipmentDispatchDetails', 'PIID', $PIId, $_POST, array(
				'ReferenceNo' => $PIId,
				'Remarks' => 'Shipping bill created',
			), 'Sales');
		}else{
			legacyAuditLogUpdateById('ScrmShipmentDispatchDetails', 'PIID', $PIId, $existingShipmentDispatch, $_POST, array(
				'ReferenceNo' => $PIId,
				'Remarks' => 'Shipping bill updated',
			), 'Sales');
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
                  <label for="exampleInputEmail1">Shipping Bill No</label>
				<input  type="text" class="form-control" name='SBNo' id="SBNo" 
				  placeholder="Enter Shipping Bill No" value="<?php echo $RowShipmentDispatch['SBNo']; ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Shipping Date</label>
				<input  type="date" class="form-control" name='SBDate' id="SBDate" 
				  placeholder="Enter SB Date" value="<?php echo $RowShipmentDispatch['SBDate']; ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">LEO Date</label>
				<input  type="date" class="form-control" name='LEODate' id="LEODate" 
				  placeholder="Enter SB Date" value="<?php echo $RowShipmentDispatch['LEODate']; ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">SB Inv Val(INR)</label>
				<input  type="text" class="form-control" name='SBInvValINR' id="SBInvValINR" 
				  placeholder="Enter SB Inv Val(INR)" value="<?php echo $RowShipmentDispatch['SBInvValINR']; ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Rotation No</label>
				<input  type="text" class="form-control" name='RotationNo' id="RotationNo" 
				  placeholder="Enter Rotation No" value="<?php echo $RowShipmentDispatch['RotationNo']; ?>">		
		         </div>
				 
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Rotation Date</label>
				<input  type="date" class="form-control" name='RotationDate' id="RotationDate" 
				  placeholder="Enter Rotation Date" value="<?php echo $RowShipmentDispatch['RotationDate']; ?>">		
		         </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Duty DrawBack Amount</label>
				<input  type="text" class="form-control" name='DutyDrawBackAmt' id="DutyDrawBackAmt" 
				  placeholder="Enter Duty DrawBack Amount" value="<?php echo $RowShipmentDispatch['DutyDrawBackAmt']; ?>">		
		         </div>
				 
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">MPEDA Details</label>
				<input  type="text" class="form-control" name='MPEDADtls' id="MPEDADtls" 
				   value="<?php echo $RowShipmentDispatch['MPEDADtls']; ?>">		
		         </div>
				 
			<!--	<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Original Recd</label>
				<input  type="text" class="form-control" name='OriginalRecd' id="OriginalRecd" 
				  value="FROZEN SHRIMPS" value="<?php echo $RowShipmentDispatch['OriginalRecd']; ?>">		
		         </div>-->
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Other Deductions $</label>
				<input  type="text" class="form-control" name='OtherDeductionsUSD' id="OtherDeductionsUSD" 
				  placeholder="Enter Other Deductions $" value="<?php echo $RowShipmentDispatch['OtherDeductionsUSD']; ?>">		
		         </div>
				
				

				
                </div>
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="DocAddReports.php?PoID=<?php echo $PIId ?>"
       class="btn btn-danger  "> Close</a>
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

