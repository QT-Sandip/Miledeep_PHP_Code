<?php
$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$RstrictNoOfPersons = $ClientConfigData->RstrictNoOfPersons;  //anil

$PageTitleFolder="PreProcessing";

if(!empty($_POST['id']) &&!empty($_POST['request']) && $_POST['request'] == 'getTotalPersonsBySubContractorID'){
    $SqlCntWorkers="SELECT COUNT(WorkerID) AS CNT,WorkerID FROM `PpcWorkersMaster` WHERE SubContractorID=".$_POST['id'];
    $StmtCntWorkers=$iconn->query($SqlCntWorkers);
    $RowCntWorkers=$StmtCntWorkers->fetch_array();
    $data = ['WorkerID' => $RowCntWorkers['WorkerID'],'CNT' => $RowCntWorkers['CNT']];
    echo json_encode($data,true);die;
}

$SqlContractor="SELECT * FROM `PpcSubContractorMaster`";
$StmtContractor=$iconn->query($SqlContractor);


$WorkerID=$_GET['ID'];

 $SqlContract="SELECT pwm.* ,psm.`NoOfPersonsPerBatch`
                FROM `PpcWorkersMaster` pwm
                LEFT JOIN PpcSubContractorMaster psm ON psm.`SubContactorID`=pwm.`SubContractorID`
                WHERE WorkerID='$WorkerID'"; 
$StmtContract=$iconn->query($SqlContract);
 $RowWorkers=$StmtContract->fetch_array();
	 $Count=mysqli_num_rows($StmtContract); 
	 
if($Count==1){
$PageTitle="Edit Worker";
}

else{
$PageTitle="Add Worker";
}

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	 //$Count=mysqli_num_rows($StmtClient); 


if($Count==1){
	$oldWorkerData = legacyAuditFetchRowById('PpcWorkersMaster', 'WorkerID', $WorkerID, 'PreProcessing');
	 $SqlClientInsert="UPDATE `PpcWorkersMaster` SET `SubContractorID`='$SubContractorID',`WorkerName`='$WorkerName',
	`PhoneNumber`='$PhoneNumber',`Address`='$Address',`AutoCharge`='$AutoCharge',`Extra`='$Extra',
	`Wage`='$Wage',`BloodGroup`='$BloodGroup',`PF`='$PF' WHERE WorkerID='$WorkerID'";
	
	
}
	else{
	 $SqlClientInsert="INSERT INTO `PpcWorkersMaster`(`SubContractorID`, `WorkerName`, `PhoneNumber`,
	 `Address`, `AutoCharge`, `Extra`, `Wage`, `BloodGroup`, `PF`) VALUES ('$SubContractorID','$WorkerName','$PhoneNumber','$Address','$AutoCharge','$Extra','$Wage','$BloodGroup','$PF')"; 
	}
	
	
	

	$StmtClientInsert=$iconn->query($SqlClientInsert);
	
	if($StmtClientInsert){
		if($Count==1){
			legacyAuditLogUpdateById('PpcWorkersMaster', 'WorkerID', $WorkerID, $oldWorkerData, $_POST, [
				'ReferenceNo' => $WorkerID,
				'Remarks' => 'Worker updated',
			], 'PreProcessing');
		}else{
			$newWorkerID = $iconn->insert_id;
			legacyAuditLogInsertById('PpcWorkersMaster', 'WorkerID', $newWorkerID, $_POST, [
				'ReferenceNo' => $newWorkerID,
				'Remarks' => 'Worker created',
			], 'PreProcessing');
		}
		
		echo "<script>alert('Success');</script>";
		
	}
	
		header('Location:WorkerList.php');

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
				<label for="exampleInputEmail1">Sub Contractor</label>
                 <select class="form-control select2" id="SubContractorID" name="SubContractorID" required>
				  <option value="">Select Sub Contractor</option>
				 <?php
				
				 while($RowContractor=$StmtContractor->fetch_array()){
                    $dataAttr = '';
                    if(!empty($RstrictNoOfPersons)){
                        $dataAttr = "data-no_of_persons='".$RowContractor['NoOfPersonsPerBatch']."'";
                    }?>
					<option <?php if($RowWorkers['SubContractorID']==$RowContractor['SubContactorID']){  ?>selected=="selected" <?php } ?>
					
					value="<?php echo $RowContractor['SubContactorID'] ;?>" <?php echo $dataAttr;?> >
				 <?php  echo $RowContractor['SubContractorName']; ?>
					</option>
					<?php }   ?>
						</select>
					</div> 

    
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Worker Name</label>
                  <input required type="text" class="form-control" name='WorkerName' id="WorkerName"
				  placeholder="Enter Worker Name" value="<?php echo $RowWorkers['WorkerName'] ?>">
                </div>
				
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Phone Number</label>
                  <input type="text" class="form-control"  name="PhoneNumber" id="PhoneNumber" 
				  placeholder="Enter Phone Number" value="<?php echo $RowWorkers['PhoneNumber'] ?>" required>
                </div>
				
				
	
				<div class="form-group col-lg-3">
                 <label for="exampleInputPassword1">Address</label>
                  <input type="text" class="form-control"  name="Address" id="Address" 
				  placeholder="Enter Address" value="<?php echo $RowWorkers['Address'] ?>" required>
                </div>
				
				<div class="form-group col-lg-3">
                 <label for="exampleInputPassword1">Auto Charge</label>
                  <input type="text" class="form-control"  name="AutoCharge" id="AutoCharge" 
				  placeholder="Enter Auto Charge" value="<?php echo $RowWorkers['AutoCharge'] ?>">
                </div>
				<div class="form-group col-lg-3">
                 <label for="exampleInputPassword1">Extra</label>
                  <input type="text" class="form-control"  name="Extra" id="Extra" 
				  placeholder="Enter Extra"  required value="<?php echo $RowWorkers['Extra'] ?>">
                </div>
				
				<div class="form-group col-lg-3">
                 <label for="exampleInputPassword1">Wage</label>
                  <input type="text" class="form-control"  name="Wage" id="Wage" 
				  placeholder="Enter Wage Per Day"  required value="<?php echo $RowWorkers['Wage'] ?>">
                </div>
				
				<div class="form-group col-lg-3">
                 <label for="exampleInputPassword1">Blood Group</label>
                  <input type="text" class="form-control"  name="BloodGroup" id="BloodGroup" 
				  placeholder="Enter Blood Group"   value="<?php echo $RowWorkers['BloodGroup'] ?>">
                </div>
				
				<div class="form-group col-lg-3">
                 <label for="exampleInputPassword1">PF</label>
                <select class="form-control select2" id="PF" name="PF" required>
				  <option <?php if($RowWorkers['PF']=='Yes'){ ?>  selected=="selected" <?php } ?>
				  value="Yes">Yes</option>
				  <option <?php if($RowWorkers['PF']=='No'){ ?> selected=="selected" <?php } ?>
				  value="No">No</option>
				
						</select>
                </div>
				
				
				
                </div>
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			   <a target="" href="WorkerList.php"   class="btn btn-danger  "> Close</a>
			<?php	if($Count==1){  ?>

                <button type="submit"  name='submit' class="btn btn-success">Update</button>
             
	<?php		}
			else{?>
                <button type="submit"  name='submit' class="btn btn-success">Submit</button>
			 <?php		}?></div>
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
		
        <script>
            <?php 
            if(!empty($RstrictNoOfPersons)){?>
                var getId = "<?php echo !empty($_GET['ID'])?$_GET['ID']:'';?>"
                var SubContractorID = "<?php echo !empty($RowWorkers['SubContractorID'])?$RowWorkers['SubContractorID']:'';?>"
                $('#SubContractorID').on('change',function(){
                    $("button[name='submit']").prop("disabled", false);
                    var value = $(this).val();
                    if(value){
                        var no_of_persons = $(this).find("option:selected").attr("data-no_of_persons");
                        $.ajax({
                            type: 'POST',
                            data: {request : 'getTotalPersonsBySubContractorID',id:value}, 
                            dataType: "json",  
                            success: function(responce){
                                var resCnt = responce.CNT;
                                var WId = responce.WorkerID;
                                var CNT1 = parseInt(resCnt)+1;//5
                                //if(getId != WId){
                                    var condition = no_of_persons>=CNT1;
                                    //1==1
                                    if(SubContractorID == value){
                                        condition = resCnt<=no_of_persons;
                                    }
                                    if(!condition){
                                        alert('Workers exceeded for this Batch. Please Choose Another Batch.');
                                        $("button[name='submit']").prop("disabled", true);
                                    }else{
                                        $("button[name='submit']").prop("disabled", false);
                                    }
                                //}                                
                            }
                        });
                    }
                })
            <?php }?>
        </script>
       
    </body>
</html>				

