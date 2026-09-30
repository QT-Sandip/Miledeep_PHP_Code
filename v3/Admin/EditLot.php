<?php
$Status=$_SESSION['Status']; 
$UserID=$_SESSION['UserID']; 
 $LotID=$_GET['ID'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


extract($_POST);
extract($_GET);
$UNIT=$GET["UNIT"];
$today_date=date('Y-m-d');


//ORIGINAL
$SqlConfig = "SELECT * FROM `AdminConfigDetails`";
$StmtConfig = $iconn->query($SqlConfig);
$RowConfig=$StmtConfig->fetch_assoc();
//print_r($_SESSION);
$ApiUrl=$RowConfig['ApiUrl'];


$SqlRmLots="SELECT * FROM `RmLotStatusDetails` WHERE LotStatusID=$LotID";  
$StmtRmLots=$iconn->query($SqlRmLots);
$RowRmLots=$StmtRmLots->fetch_array(); 
$PageTitle="Edit Arrival Details";

$ArrivalDate= $RowRmLots['ArrivalDate'];

extract($_POST);
extract($_GET);


if(isset($_POST['submit'])){
	
	//echo 'hiii';
	$oldLotRow = legacyAuditFetchRowById('RmLotStatusDetails', 'LotStatusID', $LotID, 'Admin');
	$SqlInsertTracebility="UPDATE `RmLotStatusDetails`
	SET `ArrivedWeight`='$ArrivedWeight',
	    `ArrivedCount`='$ArrivedCount',
		`ArrivedRate`='$ArrivedRate',
		`ArrivalDate`='$ArrivalDate'
		 WHERE LotStatusID='$LotID'";
		 	//echo $SqlInsertTracebility;exit;

    $StmtInsertTracebility=$iconn->query($SqlInsertTracebility);

if($StmtInsertTracebility){
		legacyAuditLogUpdateById('RmLotStatusDetails', 'LotStatusID', $LotID, $oldLotRow, array(), array(), 'Admin');
		echo "<script>alert('Success')</script>";

		
//header('Location: LotsList.php');
?>
<script> window.close();
</script>
<?php }
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

	
	 <?php		include ($IncludesPath."PreLoader.php"); ?>

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
                  <label for="exampleInputEmail1">Arrived Weight	</label>
                  <input type="text" class="form-control" name="ArrivedWeight" id="ArrivedWeight"
				  placeholder="Enter Arrived Weight	" value="<?php echo $RowRmLots['ArrivedWeight'];?>">
            </div>
	
				
         <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Arrived Count</label>
                 <input type="text" class="form-control"  name="ArrivedCount" id="ArrivedCount"
				 placeholder="Enter Arrived Count" value="<?php echo $RowRmLots['ArrivedCount'];?>">
        </div>
			
		  
				
		  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Arrived Date</label>
                  <input type="date" class="form-control" name='ArrivalDate' id="ArrivalDate"
				  max="<?php echo $today_date; ?>"placeholder="" value="<?php echo $ArrivalDate; ?>">
          </div>
		<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Arrived Rate</label>
                 <input type="text" class="form-control"  name="ArrivedRate" id="ArrivedRate"
				 placeholder="Enter Arrived Rate" value="<?php echo $RowRmLots['ArrivedRate'];?>">
        </div>


				</div>
              <!-- /.box-body -->


            

			<div class="col-xs-12 box-footer"  align="right">
                
			
			 <button  type="submit" name="submit"  class="btn btn-success">Update</button>

				
				
				
              </div>
</form>
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



