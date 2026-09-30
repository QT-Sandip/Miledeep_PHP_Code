<?php

session_start();
$QueryParms=$_GET['Q']; 
$QueryParm=$_GET['Q']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$PoID=$_GET['PoID'];

extract($_POST);
extract($_GET);

 $SqlPayment="SELECT * FROM `QcPayments` WHERE InvID='$PoID'";
$StmtPayment=$iconn->query($SqlPayment);
$RowPayment=$StmtPayment->fetch_array();
$Count=mysqli_num_rows($StmtPayment);
if($Count==0){
$PageTitle="Payment Creation";
}
else{
	$PageTitle="Payment Update";
}
$PageTitleFolder="Quality Control";

if(isset($_POST['submit'])){
$Count=mysqli_num_rows($StmtPayment);
if($Count==0){
	$SqlInsert=" INSERT INTO `$TblQcPayments`(`FobValue`, `TrnsID`, `TrnsDate`, `InvID`)
		VALUES ('$FobValue','$TrnsID','$TrnsDate','$PoID')	";  
}
else{
	$SqlInsert="UPDATE `$TblQcPayments` SET `FobValue`='$FobValue',`TrnsID`='$TrnsID',`TrnsDate`='$TrnsDate' WHERE `InvID`='$PoID'";
}
	

	$StmtInsert=$iconn->query($SqlInsert);


	if($StmtInsert){
		if($Count==0){
			legacyAuditLogInsertById('QcPayments', 'PaymentID', $iconn->insert_id, $_POST, ['Remarks' => 'Payment Created'], 'QualityControl');
		} else {
			legacyAuditLogUpdateById('QcPayments', 'PaymentID', $RowPayment['PaymentID'], $RowPayment, $_POST, ['Remarks' => 'Payment Updated'], 'QualityControl');
		}
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

                        <?php include ($IncludesPath."page-title.php");?>
			

      
       <div class="row">
                            <div class="col-12">
                                <div class="card">
								 <form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">
			
               <div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">FOB Value</label>
                  <input   type="text" class="form-control" name="FobValue" id="FobValue" 
				  placeholder="Enter FOB Vlaue" value="<?php echo $RowPayment['FobValue'];?>">
                </div>
				
				<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Transaction ID</label>
                  <input   type="text" class="form-control" name="TrnsID" id="TrnsID" 
				  placeholder="Enter Transaction ID" value="<?php echo $RowPayment['TrnsID'];?>">
                </div>
				
				<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Transaction Date</label>
                  <input   type="date" class="form-control" name="TrnsDate" id="TrnsDate" 
				  value="<?php echo $RowPayment['TrnsDate'];?>" placeholder="Enter Transaction Date" >
                </div>
					
				
				
				</div>
              
					

			  <div class="col-xs-12 box-footer"  align="right">
			   <a  href="DocAddReports.php?Q=<?php echo $QueryParm ?>" class="btn btn-danger">Close</a>

                <button type="submit"  name="submit"   class="btn btn-success waves-effect waves-light">
				 </i>
				Submit</button>
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
		<?php include ($IncludesPath."NewScripts.php");?>
      
  
       
    </body>
</html>				
