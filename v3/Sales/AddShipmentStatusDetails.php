<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="SalesNew";
$PageTitle="Shipment Status Details";


$PIId=$_GET['PIID'];



extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	  $sql_org_insert="
	 INSERT INTO `$ScrmShipmentStatusDetails`(`PIID`,`ActualShipmentDate`, `ETA`, `PaymentRequestDate`, `BankPaymentDate`, `FDAReleaseDate`, `FDAReleaseStatus`)  
	 VALUES ('$PIId','$ActualShipmentDate','$ETA','$PaymentRequestDate','$BankPaymentDate', '$FDAReleaseDate','$FDAReleaseStatus')";
	

	$stmt1=$iconn->query($sql_org_insert);
	$NewID = $iconn->insert_id;

	if($stmt1){
		legacyAuditLogInsertById($ScrmShipmentStatusDetails, 'PIID', $PIId, $_POST, [
			'ReferenceNo' => $PIId,
			'Remarks' => 'Shipment status details created',
		], 'Sales');
		
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
                  <label for="exampleInputEmail1">Actual Shipment Date</label>
				<input required type="date" class="form-control" name='ActualShipmentDate' id="ActualShipmentDate" 
				  placeholder="Enter Actual Shipment Date" value="">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">ETA</label>
<input  required type="date" class="form-control" name='ETA' id="ETA" 
				  placeholder=" Enter ETA" value="">
                </div> 
				
				
				
			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Payment Request Date</label>
<input  required type="date" class="form-control" name='PaymentRequestDate' id="PaymentRequestDate" 
				  placeholder=" Enter Payment Request Date" value="">
                </div> 	
				 
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Bank Payment Date</label>
				<input required type="date" class="form-control" name='BankPaymentDate' id="BankPaymentDate" 
				  placeholder="Enter Bank Payment Date" value="">		
		
                </div>

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">FDA Release Date</label>
				<input required type="date" class="form-control" name='FDAReleaseDate' id="FDAReleaseDate" 
				  placeholder="Enter FDA Release Date" value="">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">FDA Release Status</label>
				<input required type="text" class="form-control" name='FDAReleaseStatus' id="FDAReleaseStatus" 
				  placeholder="Enter FDA Release Status" value="">		
		
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

