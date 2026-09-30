<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include "Constants.php";

$PageTitleFolder="Orders";
$PageTitle="Shipment Status";

$PoID=$_GET['PoID'];


extract($_POST);
extract($_GET);

 $SqlCount="SELECT * FROM `ScrmShipmentDetailsBreif` WHERE PiID='$PoID'";
$StmtCount=$iconn->query($SqlCount);
$RowCustomInvoice=$StmtCount->fetch_array();



if(isset($_POST['submit'])){
	 
	
	
	
	$Count=mysqli_num_rows($StmtCount);

	$oldShipmentRow = null;
	if($Count==0){
	 $SqlInsert="
	 INSERT INTO `ScrmShipmentDetailsBreif`(`PiID`, `ShippedOn`, `BLNumber`, `ETA`, 
	 `ProductionStartedDate`,`TransferDate`, `TransportedDate`,`ContainerNo`, `AvailableDate`, `ClosedDate`, `DeliveredDate`,
	 `CInvNo`, `CInvDate`) VALUES 
	 ('$PoID','$ShippedOn','$BLNumber','$ETA','$ProductionStartedDate','$TransferDate','$TransportedDate',
	 '$ContainerNo','$ProductionStartedDate','$ClosedDate','$DeliveredDate','$CInvNo','$CInvDate')";
	
	}
	else{
		$oldShipmentRow = legacyAuditFetchRowById('ScrmShipmentDetailsBreif', 'PiID', $PoID, 'Admin');
		$SqlInsert="UPDATE `ScrmShipmentDetailsBreif` SET `ShippedOn`='$ShippedOn',
		`BLNumber`='$BLNumber',`ETA`='$ETA',`ProductionStartedDate`='$ProductionStartedDate',
		`TransferDate`='$TransferDate',
		`TransportedDate`='$TransportedDate',
		`ContainerNo`='$ContainerNo',`AvailableDate`='$ProductionStartedDate',`ClosedDate`='$ClosedDate',
		`DeliveredDate`='$DeliveredDate',`CInvNo`='$CInvNo',`CInvDate`='$CInvDate' WHERE PiID='$PoID'";
	}
	$stmt1=$iconn->query($SqlInsert);
	
	if($stmt1){
		if($Count==0){
			legacyAuditLogInsertById('ScrmShipmentDetailsBreif', 'PiID', $PoID, array(), array(), 'Admin');
		} else {
			legacyAuditLogUpdateById('ScrmShipmentDetailsBreif', 'PiID', $PoID, $oldShipmentRow, array(), array(), 'Admin');
		}
		
		if($CInvNo&&$CInvDate){
		$oldPoRow = legacyAuditFetchRowById('ScrmPoData', 'PoID', $PoID, 'Admin');
		$SqlPoUpdate="UPDATE `ScrmPoData` SET `InvNo`='$CInvNo',`InvDate`='$CInvDate' WHERE  `PoID`='$PoID'";
		$StmtPoUpdate=$iconn->query($SqlPoUpdate);
		if($StmtPoUpdate){
			legacyAuditLogUpdateById('ScrmPoData', 'PoID', $PoID, $oldPoRow, array(), array(), 'Admin');
		}
			
		}
		header('Location: index.php');
	}
	
	/*else{
		echo '<script language="javascript" type="text/javascript"> 
                //alert("Packing Style Already Exists");
                window.location = "index.php";
        </script>';
			//header('Location: CreatePackingStyles.php');
	
	}*/
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
                  <label for="exampleInputEmail1">Custom Invoice</label>
				<input  type="text" class="form-control" name='CInvNo' id="CInvNo" 
				  placeholder="Enter Custom Invoice No" value="<?php echo $RowCustomInvoice['CInvNo']?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Custom Invoice Date</label>
 <input   type="date" class="form-control" name='CInvDate' id="CInvDate" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['CInvDate']?>">
                </div>
				
             	<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">BL Number</label>
				<input  type="text" class="form-control" name='BLNumber' id="BLNumber" 
				  placeholder="Enter BL Number" value="<?php echo $RowCustomInvoice['BLNumber']?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Container No</label>
				<input  type="text" class="form-control" name='ContainerNo' id="ContainerNo" 
				  placeholder="Enter Container No" value="<?php echo $RowCustomInvoice['ContainerNo']?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Production Started Date</label>
 <input   type="date" class="form-control" name='ProductionStartedDate' id="ProductionStartedDate" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['ProductionStartedDate']?>">
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Transfer Date</label>
               <input   type="date" class="form-control" 
			   name='TransferDate' id="TransferDate" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['TransferDate']?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Transported Date</label>
 <input   type="date" class="form-control" name='TransportedDate' id="TransportedDate" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['TransportedDate']?>">
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Shipped Date</label>
 <input   type="date" class="form-control" name='ShippedOn' id="ShippedOn" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['ShippedOn']?>">
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">ETA</label>
 <input   type="date" class="form-control" name='ETA' id="ETA" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['ETA']?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Delivered Date</label>
 <input   type="date" class="form-control" name='DeliveredDate' id="DeliveredDate" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['DeliveredDate']?>">
                </div>
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Closed Date</label>
 <input   type="date" class="form-control" name='ClosedDate' id="ClosedDate" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['ClosedDate']?>">
                </div>
				
				
				
				 
                </div>
				
				
			<div class="col-xs-12 box-footer"  align="right">
						  <a target="" href="index.php"   class="btn btn-danger  "> Close</a>

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
