<?php
 $Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$PageTitle="Rm Report Creation " ;

$PageTitleFolder="Purchase";

$LotId=$_GET['ID'];


extract($_POST);
extract($_GET);
//$UNIT=$_SESSION["UNIT"];
$UserTypeId=$_GET["Type"];

if($_SESSION['Msg']!=''){
	$Msg=$_SESSION['Msg'];
	echo "<script>alert(' $Msg ');</script>";
	$_SESSION['Msg']='';
}




			
 $SqlRmQualityReport="SELECT *  FROM `$RmQualityReport` WHERE LotID='$LotId'"; 
$StmtRmQualityReport=$iconn->query($SqlRmQualityReport);
 $Count=mysqli_num_rows ( $StmtRmQualityReport ); 
$RowRmQualityReport=$StmtRmQualityReport->fetch_array();


if(isset($_POST['submit'])){
	$auditLogger = legacyAuditGetLogger('Purchase');
	$existingReportRow = null;
	if ($Count != 0 && $auditLogger) {
		$existingReportRows = $auditLogger->fetchRowsByConditions($RmQualityReport, array('LotID' => $LotId));
		if (!empty($existingReportRows)) {
			$existingReportRow = $existingReportRows[0];
		}
	}
	 if($Count==0){
	 	$SqlUserCount="SELECT count(*) as Total FROM `$RmUsersList` WHERE 
	UserName='$NAME' and UserType='$UserTypeId'";
	$StmtUserCount=$iconn->query($SqlUserCount);

	 $RowUserCount=$StmtUserCount -> fetch_array();	

	 $SqlRm="INSERT INTO `$RmQualityReport`( `LotID`, `RmDate`, `HarvestStartTime`, `HarvestEndTime`, 
`HonReceiveTime`, `HonCountCheckingTime`, `CheckingCount`, `PurBoxNo`, `PurBoxQty`, `CheckingBoxNo`,
 `CheckingBoxQty`, `SoftPercent`, `PieceTemp`, `Remarks`) VALUES ('$LotId','$RmDate','$HarvestStartTime',
 '$HarvestEndTime','$HonReceiveTime',
'$HonCountCheckingTime','$CheckingCount','$PurBoxNo','$PurBoxQty','$CheckingBoxNo','$CheckingBoxQty','$SoftPercent','$PieceTemp','$Remarks')
   	 ";
	
	
	$StmtRm=$iconn->query($SqlRm);
	if($StmtRm){
		legacyAuditLogInsertById($RmQualityReport, 'LotID', $LotId, array(
			'LotID' => $LotId,
			'RmDate' => $RmDate,
			'HarvestStartTime' => $HarvestStartTime,
			'HarvestEndTime' => $HarvestEndTime,
		), array(
			'ReferenceNo' => $LotId,
			'Remarks' => 'RM report created from legacy purchase screen',
		), 'Purchase');
	}
	
	
	header('Location: LotsList.php');
	
	 }
	 else{
		$SqlRmUpdate="UPDATE `CreateRmReport` SET `RmDate`='$RmDate',
	`HarvestStartTime`='$HarvestStartTime',`HarvestEndTime`='$HarvestEndTime',`HonReceiveTime`='$HonReceiveTime',
	`HonCountCheckingTime`='$HonCountCheckingTime',`CheckingCount`='$CheckingCount',`PurBoxNo`='$PurBoxNo',`PurBoxQty`='$PurBoxQty',
	`CheckingBoxNo`='$CheckingBoxNo',`CheckingBoxQty`='$CheckingBoxQty',
	`SoftPercent`='$SoftPercent',`PieceTemp`='$PieceTemp',`Remarks`='$Remarks' WHERE `LotID`='$LotId'
   	 ";
	
	
	
	
	
	$StmtRmUpdate=$iconn->query($SqlRmUpdate);	
	if($StmtRmUpdate && $existingReportRow !== null){
		legacyAuditLogUpdateById($RmQualityReport, 'LotID', $LotId, $existingReportRow, array(
			'LotID' => $LotId,
			'RmDate' => $RmDate,
			'HarvestStartTime' => $HarvestStartTime,
			'HarvestEndTime' => $HarvestEndTime,
		), array(
			'ReferenceNo' => $LotId,
			'Remarks' => 'RM report updated from legacy purchase screen',
		), 'Purchase');
	}
			
			header('Location: LotsList.php');
 
		 
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
                  <label for="exampleInputEmail1">RM Date</label>
                  <input type="date" class="form-control" name='RmDate' id="RmDate"
				  max="<?php echo date("Y-m-d"); ?>"placeholder="" value="<?php echo $RowRmQualityReport['RmDate'];?>">
                </div>

		
			   <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Harvest Start Time</label>

				  <input type="Time" class="form-control" name='HarvestStartTime' id="HarvestStartTime"
				 placeholder="" value="<?php echo $RowRmQualityReport['HarvestStartTime'];?>">
				  </div>


				  <div class="form-group col-lg-3" >
				   <label for="exampleInputEmail1">Harvest End Time</label>

				  <input type="Time" class="form-control" name='HarvestEndTime' id="HarvestEndTime"
				 placeholder="" value="<?php echo $RowRmQualityReport['HarvestEndTime'];?>">
				  
                 

				</div>


			  


				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Hon Receiving Time</label>
                  <input type="Time" class="form-control" name='HonReceiveTime' id="HonReceiveTime"
				  placeholder="" value="<?php echo $RowRmQualityReport['HonReceiveTime'];?>">
                </div>

				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Hon Count Checking Time</label>
                  <input type="Time" class="form-control" name='HonCountCheckingTime' id="HonCountCheckingTime"
				  placeholder="" value="<?php echo $RowRmQualityReport['HonCountCheckingTime'];?>">
                </div>

				 <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Checking Count</label>

				  <input type="text" class="form-control" name='CheckingCount' id="CheckingCount"
				  placeholder="Enter Checking Count" value="<?php echo $RowRmQualityReport['CheckingCount'];?>">
				  </div>

				   <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Purchase Box No</label>
				  <input type="text" class="form-control" name='PurBoxNo' id="PurBoxNo"
				  placeholder="Enter Purchase Box No" value="<?php echo $RowRmQualityReport['PurBoxNo'];?>">
				 
				  </div>
 
                  <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Purchase Box Qty</label>

				  <input type="text" class="form-control" name='PurBoxQty' id="PurBoxQty"
				  placeholder="Enter Purchase Box Qty" value="<?php echo $RowRmQualityReport['PurBoxQty'];?>">
				  </div>
           
			  <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Checking Box No</label>
				  <input type="text" class="form-control" name='CheckingBoxNo' id="CheckingBoxNo"
				  placeholder="Enter Checking Box No" value="<?php echo $RowRmQualityReport['CheckingBoxNo'];?>">
				 
				  </div>

				  <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Checking Box Qty</label>

				  <input type="text" class="form-control" name='CheckingBoxQty' id="CheckingBoxQty"
				  placeholder="Enter Checking Box Qty" value="<?php echo $RowRmQualityReport['CheckingBoxQty'];?>">
				  </div>

          <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Soft(%)</label>

          <input type="text" class="form-control" name='SoftPercent' id="SoftPercent"
				  placeholder=" Enter soft Percentage" value="<?php echo $RowRmQualityReport['SoftPercent'];?>">
         </div>
    
    
		  
		  <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Piece Temperature</label>

          <input type="text" class="form-control" name='PieceTemp' id="PieceTemp"
				  placeholder=" Enter Piece Temperature" value="<?php echo $RowRmQualityReport['PieceTemp'];?>">
         </div>



              
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Ice Packing(Remarks)</label>
                 <input type="text" class="form-control"  name="Remarks" id="Remarks"
				 placeholder="Enter Remarks"value="<?php echo $RowRmQualityReport['Remarks'];?>">
                </div>
                </div>
  
				  
				<?php if($Count==0){ ?> 
				  
			 <div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-success">Submit</button>
              </div> 
				<?php }else{  ?>
				
			  <div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-success"><i class="fa fa-edit"></i>Update</button>
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



