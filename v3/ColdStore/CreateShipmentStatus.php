<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include "Constants.php";

$PageTitleFolder="Coldstore";
$PageTitle="Shipment Status";

$PoID=$_GET['PoID'];


extract($_POST);
extract($_GET);

 $SqlCount="SELECT SSDB.*,SPD.LotNo FROM `ScrmShipmentDetailsBreif` as SSDB
left join ScrmPoData as SPD on SPD.PoID=SSDB.PiID
 WHERE PiID='$PoID'";
$StmtCount=$iconn->query($SqlCount);
$RowCustomInvoice=$StmtCount->fetch_array();



if(isset($_POST['submit'])){
	 
	
	
	
	$Count=mysqli_num_rows($StmtCount);

	
	if($Count==0){
	 $SqlInsert="
	 INSERT INTO `ScrmShipmentDetailsBreif`(`PiID`, `ShippedOn`, `BLNumber`, `ETA`, 
	 `ProductionStartedDate`, `TransportedDate`,`ContainerNo`, `AvailableDate`, `ClosedDate`, `DeliveredDate`,
	 `CInvNo`, `CInvDate`) VALUES 
	 ('$PoID','$ShippedOn','$BLNumber','$ETA','$ProductionStartedDate','$TransportedDate',
	 '$ContainerNo','$ProductionStartedDate','$ClosedDate','$DeliveredDate','$CInvNo','$CInvDate')";
	
	}
	else{
		$SqlInsert="UPDATE `ScrmShipmentDetailsBreif` SET `ShippedOn`='$ShippedOn',
		`BLNumber`='$BLNumber',`ETA`='$ETA',`ProductionStartedDate`='$ProductionStartedDate',
		`TransportedDate`='$TransportedDate',
		`ContainerNo`='$ContainerNo',`AvailableDate`='$ProductionStartedDate',`ClosedDate`='$ClosedDate',
		`DeliveredDate`='$DeliveredDate',`CInvNo`='$CInvNo',`CInvDate`='$CInvDate' WHERE PiID='$PoID'";
	}
	$stmt1=$iconn->query($SqlInsert);
	
	//echo $SqlInsert; exit;
	
	if($stmt1){
		
		if($CInvNo&&$CInvDate){
		$SqlPoUpdate="UPDATE `ScrmPoData` SET `InvNo`='$CInvNo',`InvDate`='$CInvDate' WHERE  `PoID`='$PoID'";
		$StmtPoUpdate=$iconn->query($SqlPoUpdate);
			
		}
		
		
		
	
		//if($LotNo!=''){
		$SqlPoUpdate="UPDATE `ScrmPoData` SET `LotNo`='$LotNo' WHERE  `PoID`='$PoID'"; 
		$StmtPoUpdate=$iconn->query($SqlPoUpdate);

		if($StmtPoUpdate){

		}

		echo '<script language="javascript" type="text/javascript"> 
                //alert("Packing Style Already Exists");
                window.location = "CreateShipmentStatus.php?PoID='.$PoID.'";</script>';
			
		
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
				<input  readonly type="text" class="form-control" name='CInvNo' id="CInvNo" 
				  placeholder="Enter Custom Invoice No" value="<?php echo $RowCustomInvoice['CInvNo']?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Custom Invoice Date</label>
 <input  readonly type="date" class="form-control" name='CInvDate' id="CInvDate" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['CInvDate']?>">
                </div>
				
             	<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">BL Number</label>
				<input readonly type="text" class="form-control" name='BLNumber' id="BLNumber" 
				  placeholder="Enter BL Number" value="<?php echo $RowCustomInvoice['BLNumber']?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Container No</label>
				<input readonly type="text" class="form-control" name='ContainerNo' id="ContainerNo" 
				  placeholder="Enter Container No" value="<?php echo $RowCustomInvoice['ContainerNo']?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Production Started Date</label>
 <input   type="date" class="form-control" name='ProductionStartedDate' id="ProductionStartedDate" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['ProductionStartedDate']?>">
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
 <input readonly  type="date" class="form-control" name='ETA' id="ETA" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['ETA']?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Delivered Date</label>
 <input readonly  type="date" class="form-control" name='DeliveredDate' id="DeliveredDate" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['DeliveredDate']?>">
                </div>
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Closed Date</label>
 <input readonly  type="date" class="form-control" name='ClosedDate' id="ClosedDate" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['ClosedDate']?>">
                </div>
			
			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Internal Lot Number</label>
 <input   type="text" class="form-control" name='LotNo' id="LotNo" 
				  placeholder=" Enter Internal Lot Number" value="<?php echo $RowCustomInvoice['LotNo']?>">
                </div>
				
				
				
				 
                </div>
				
				
			<div class="col-xs-12 box-footer"  allign="right">
						  <a target="" href="Shipments" class="btn btn-danger  "> Close</a>
						  <!-- <a target="" href="#"  onclick="window.close();" class="btn btn-danger  "> Close</a> -->

                <button type="submit"  name='submit' class="btn btn-success">Submit</button>
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
