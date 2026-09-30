<?php
session_start();
$QueryParms=$_GET['Q']; 
$QueryParm=$_GET['Q']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


$PoID=$_GET['PoID'];

$SqlPo="SELECT * FROM  $TblScrmPoData   WHERE PoID='$PoID'"; 

$StmtPo=$iconn->query($SqlPo);

$RowPo=$StmtPo->fetch_array();


$PageTitle="Short Shipment Declaration - ".$RowPo['PoNo'];
$PageTitleFolder="Quality Control";


$SqlShipment="SELECT * FROM `$TblQcShortShipmentLetter`  WHERE InvID='$PoID'"; 

$StmtShipment=$iconn->query($SqlShipment);

$RowShipment=$StmtShipment->fetch_array();

//$InvID=$RowShipment['InvID'];


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
	$Count=mysqli_num_rows($StmtShipment);
	if($Count =='0'){
		
 	 $SqlInvUpdate="INSERT INTO `$TblQcShortShipmentLetter`(`InvID`, `McsOriginal`, `McsLoading`, `Reason`) 
VALUES ('$PoID','$McsOriginal','$McsLoading','$Reason')";
		
		}else{
		$oldRowShipment = legacyAuditFetchRowById('QcShortShipmentLetter', 'InvID', $PoID, 'QualityControl');
	 $SqlInvUpdate="UPDATE `$TblQcShortShipmentLetter` SET `McsOriginal`='$McsOriginal',`McsLoading`='$McsLoading', `Reason`='$Reason' 
	WHERE InvID='$PoID' ";
		
	
	}
	//echo $SqlInvUpdate; exit;
	$StmtInvUpdate=$iconn->query($SqlInvUpdate);
   IF($StmtInvUpdate){
	if($Count =='0'){
		$newShipmentId = $iconn->insert_id;
		legacyAuditLogInsertById('QcShortShipmentLetter', 'ID', $newShipmentId, $_POST, ['Remarks' => 'Created Short Shipment Letter'], 'QualityControl');
	} else {
		legacyAuditLogUpdateById('QcShortShipmentLetter', 'InvID', $PoID, $oldRowShipment, $_POST, ['Remarks' => 'Updated Short Shipment Letter'], 'QualityControl');
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
			 <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Original No of MCS</label>
                  <input type="number" class="form-control" name='McsOriginal' id="McsOriginal" 
				  placeholder="Enter Original number of MCS " value="<?php echo $RowShipment['McsOriginal'] ?>"
				    required>
                </div>  
                 <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Loading MCS</label>
                  <input type="number" class="form-control" name='McsLoading' id="McsLoading" 
				  placeholder="Enter number of loading MCS" value="<?php echo $RowShipment['McsLoading'] ?>"
				    required>
                </div> 

				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Reason</label>
                  <input type="text" class="form-control" name='Reason' id="Reason" 
				  placeholder="Enter Reason" value="<?php echo $RowShipment['Reason'] ?>"
				    required>
                </div> 	</div>
              
					

			  <div class="col-xs-12 box-footer"  align="right">
			  			   <a  href="DocAddReports.php?Q=<?php echo $QueryParm ?>" class="btn btn-danger">Close</a>

                <button type="submit"  name="submit"   class="btn btn-success waves-effect waves-light">
				
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

