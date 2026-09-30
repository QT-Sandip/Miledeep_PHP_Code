<?php
$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$IndentID=$_GET['IndentID'];
$Indentby=$_SESSION["UserName"];
$IndentDate=date('Y-m-d');

extract($_POST);
extract($_GET);

/*$SqlLots="SELECT * FROM `RmLotDetatils` as RLD 
LEFT JOIN RmVehicalDetails as RVD on RVD.ID=RLD.VehicalID 
WHERE RmLotID='$LotID'";*/

 $SqlLots="SELECT * FROM `RmBookingDetatils` WHERE BookingDate>= DATE_ADD(CURDATE(), INTERVAL -7 DAY) or RmBookingID='1'";
$StmtLots1=$iconn->query($SqlLots);


$SqlVaStations="SELECT * FROM `RmVAStations` WHERE UnitStatus=1";
$StmtVaStations=$iconn->query($SqlVaStations);


$SqlRmVehicalDetails="SELECT * FROM `RmVehicalDetails` ";
$StmtRmVehicalDetails=$iconn->query($SqlRmVehicalDetails);


$SqlIndentCategory="SELECT * FROM `IndentCategoryList` WHERE Status=1";
$StmtIndentCategory=$iconn->query($SqlIndentCategory);


 $SqlCrateDetails="SELECT * FROM `RmIndentDetails` WHERE ID='$IndentID'";
$StmtCrateDetails=$iconn->query($SqlCrateDetails);
$RowCrateDetails=$StmtCrateDetails->fetch_array();


$PageTitle="Add Crate Details ";
$PageTitleFolder="Purchase";
if(isset($_POST['submit'])){
	
	
	//function generate_random_IndentCode() {
   

for ($randomNumber = mt_rand(1, 9), $i = 1; $i < 10; $i++) {
    $randomNumber .= mt_rand(0, 8);
}
/*return $randomNumber;
}*/
  $IndentCode = $randomNumber;
 $LotIds=implode(',', $SelectedLotID);
 $existingIndentRow = null;
 if($IndentID!=''){
 	$existingIndentRow = legacyAuditFetchRowById('RmIndentDetails', 'ID', $IndentID, 'Purchase');
 }
 

	
	if($IndentID==''){
	$SqlInsert="INSERT INTO `RmIndentDetails`(`LotID`, `Category`, `IndentCode`,`VehicalID`, `VAStationId`, `IndentDateTime`, `IndentIceCrates`, `IndentEmptyCrates`, `IndentNetCrates`, `Indentby`, `Remarks1`)
	VALUES ('$LotIds','$Category','$IndentCode','$VehicalID','$VAStationId','$IndentDate','$IndentIceCrates','$IndentEmptyCrates','$IndentNetCrates','$Indentby','$Remarks1')";
		 	
	}
	else{
		$SqlInsert="UPDATE `RmIndentDetails` SET `LotID`='$LotIds' ,`Category`='$Category' ,`VehicalID`='$VehicalID',`VAStationId`='$VAStationId' ,`IndentDateTime`='$IndentDate',
		`IndentIceCrates`='$IndentIceCrates',`IndentEmptyCrates`='$IndentEmptyCrates',`IndentNetCrates`='$IndentNetCrates' ,`Indentby`='$Indentby',`Remarks1`='$Remarks1' WHERE ID='$IndentID'";
		
	}
	//echo $SqlInsert; exit;
	$StmtInsert=$iconn->query($SqlInsert);
	if($StmtInsert){
		if($IndentID==''){
			$insertedIndentId = $iconn->insert_id;
			legacyAuditLogInsertById('RmIndentDetails', 'ID', $insertedIndentId, array(
				'LotID' => $LotIds,
				'Category' => $Category,
				'VehicalID' => $VehicalID,
				'VAStationId' => $VAStationId,
			), array(
				'ReferenceNo' => $insertedIndentId,
				'Remarks' => 'Indent created from legacy purchase screen',
			), 'Purchase');
		}else{
			legacyAuditLogUpdateById('RmIndentDetails', 'ID', $IndentID, $existingIndentRow, array(
				'LotID' => $LotIds,
				'Category' => $Category,
				'VehicalID' => $VehicalID,
				'VAStationId' => $VAStationId,
			), array(
				'ReferenceNo' => $IndentID,
				'Remarks' => 'Indent updated from legacy purchase screen',
			), 'Purchase');
		}
		
		 		echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "IndentList.php";
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
                  <label for="exampleInputEmail1">Category</label>

				  <select required class="form-control select2" name="Category" id="Category">
					<option value=""> Select</option>
				   <?php while($RowIndentCategory=$StmtIndentCategory->fetch_array()) {

				?>
				<option <?php if($RowIndentCategory['CategoryId']==$RowCrateDetails["Category"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $RowIndentCategory['CategoryId'];?>">
				<?php echo $RowIndentCategory['CategoryName'];?></OPTION>
				<?php } ?>
				<select>
			</div>
			
			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">VA Stations</label>

				  <select  class="form-control select2" name="VAStationId" id="VAStationId">
					<option value=""> Select</option>
				   <?php while($RowVaStations=$StmtVaStations->fetch_array()) {

				?>
				<option <?php if($RowVaStations['StationID']==$RowCrateDetails["VAStationId"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $RowVaStations['StationID'];?>">
				<?php echo $RowVaStations['UnitName'];?></OPTION>
				<?php } ?>
				<select>
			</div>
    
            	<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Lots</label>
                 
                <select multiple class="form-control" name="SelectedLotID[]" id="SelectedLotID" required>
					
					 <?php while($RowLot=$StmtLots1->fetch_array()){
						 $LotID=$RowLot['RmBookingID'];
						 $LotNumber=$RowLot['BookingNumber'];?>
						 <option value="<?php echo $LotID ?>"<?php if( $LotID==$RowCrateDetails["LotID"])
					  echo 'selected="selected"'; ?>><?php echo $LotNumber;?></option>
					
					<?php  }?>
				<select>
				
				</div>
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Vehicle Number</label>

				  <select required class="form-control select2" name="VehicalID" id="VehicalID">
					
				   <?php while($RowRmVehicalDetails=$StmtRmVehicalDetails->fetch_array()) {

				?>
				<option <?php if($RowRmVehicalDetails['ID']==$RowCrateDetails["VehicalID"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $RowRmVehicalDetails['ID'];?>">
				<?php echo $RowRmVehicalDetails['VehicalNumber'];?></OPTION>
				<?php } ?>
				<select>
			</div>
				
				<!-- <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Indent Date</label>
                  <input required type="datetime-local" class="form-control" name='IndentDateTime' id="IndentDateTime"
				  max="<?php echo $today_date; ?>"placeholder="" value="<?php echo $RowCrateDetails['IndentDateTime']; ?>">
          </div>-->
				  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Indent Ice Crates</label>
                  <input required type="text" class="form-control" name='IndentIceCrates' id="IndentIceCrates"
				  placeholder="Enter No.Of Ice Crates " value="<?php echo $RowCrateDetails['IndentIceCrates']; ?>">
                </div>
				
			
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Indent Empty Crates</label>
                  <input required type="text" class="form-control" name='IndentEmptyCrates' id="IndentEmptyCrates"
				  placeholder="Enter No.Of Empty Crates" value="<?php echo $RowCrateDetails['IndentEmptyCrates']; ?>">
                </div>
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Indent Net Crates</label>
                  <input type="text" class="form-control"  name="IndentNetCrates" id="IndentNetCrates" 
				  placeholder="Enter No.Of Net Crates " value="<?php echo $RowCrateDetails['IndentNetCrates']; ?>">
                </div>
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Indent Remarks</label>
                  <input type="text" class="form-control"  name="Remarks1" id="Remarks1" 
				  placeholder="Enter Indent Remarks" value="<?php echo $RowCrateDetails['Remarks1']; ?>">
                </div>
				
				<!--<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Checked Remarks</label>
                  <input type="text" class="form-control"  name="Remarks2" id="Remarks2" 
				  placeholder="Enter Checked Remarks" value="<?php echo $RowCrateDetails['Remarks2']; ?>">
                </div>-->
                </div>
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			   <a target="" href="IndentList.php"   class="btn btn-danger  "> Close</a>
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
<script>			
$('document').ready(function(){
	
	  $("#SelectedLotID").select2();
	  
	  
});
</script>
