<?php
$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$RstrictNoOfPersons = $ClientConfigData->RstrictNoOfPersons;  //anil

$PageTitleFolder="PreProcessing";





$SubContactorID=$_GET['ID'];

$SqlContract=" SELECT * FROM `PpcSubContractorMaster` where SubContactorID='$SubContactorID'";
$StmtContract=$iconn->query($SqlContract);
$RowSubContract=$StmtContract->fetch_array();
$ContactorID=$RowSubContract['ContactorID'];
$Count=mysqli_num_rows($StmtContract); 

$SqlContractor="SELECT * FROM `PpcContractMaster` where ContractorID='$ContactorID'";
$StmtContractor=$iconn->query($SqlContractor);
$Type=$StmtContractor->fetch_array()['Type'];

$SqlContractor="SELECT * FROM `PpcContractMaster` where Type='$Type' ";
$StmtContractor=$iconn->query($SqlContractor);


if($Count==1){
$PageTitle="Edit Batch";
}

else{
$PageTitle="Add Batch";
}

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	$insertFields = $insertValues = $updateFields = '';
	if(!empty($RstrictNoOfPersons)){
		$insertFields = ",NoOfPersonsPerBatch";
		$insertValues = ',"'.$_POST['NoOfPersonsPerBatch'].'"';
		$updateFields = ',NoOfPersonsPerBatch="'.$_POST['NoOfPersonsPerBatch'].'"';
	}

if($Count==1){
	$oldSubContractorData = legacyAuditFetchRowById('PpcSubContractorMaster', 'SubContactorID', $SubContactorID, 'PreProcessing');
	
	$SqlClientInsert="UPDATE `PpcSubContractorMaster` 
	SET `SubContractorName`='$SubContractorName',
	`Comissions`='$Comissions',
	`AutoCharge`='$AutoCharge',
	`Extras`='$Extras',
	`Area`='$Area',`ContactorID`='$ContactorID'
	 $updateFields
	 WHERE SubContactorID='$SubContactorID'";
	
	
}
	else{
	 $SqlClientInsert="INSERT INTO `PpcSubContractorMaster`
	 ( `SubContractorName`, `Comissions`, `AutoCharge`, `Extras`, `Area`, `ContactorID`$insertFields)
	 VALUES ('$SubContractorName','$Comissions','$AutoCharge','$Extras','$Area','$ContactorID'$insertValues)"; 
	}
	
	
	

	$StmtClientInsert=$iconn->query($SqlClientInsert);
	
	if($StmtClientInsert){
		if($Count==1){
			legacyAuditLogUpdateById('PpcSubContractorMaster', 'SubContactorID', $SubContactorID, $oldSubContractorData, $_POST, [
				'ReferenceNo' => $SubContactorID,
				'Remarks' => 'Sub contractor updated',
			], 'PreProcessing');
		}else{
			$newSubContractorID = $iconn->insert_id;
			legacyAuditLogInsertById('PpcSubContractorMaster', 'SubContactorID', $newSubContractorID, $_POST, [
				'ReferenceNo' => $newSubContractorID,
				'Remarks' => 'Sub contractor created',
			], 'PreProcessing');
		}
		
		echo "<script>alert('Success');</script>";
		
	}
	
		header('Location:SubContractorList.php');

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
				<label for="exampleInputEmail1">Supplier Type<span style="color:red">*</span></label>
                 <select class="form-control select2" id="Type" name="Type" required>
				<option value=""></option>				
				<option <?php if($Type==""){  ?>selected=="selected" <?php } ?>
					
					value="" >Select Supplier Type</option>
					<option <?php if($Type==1){  ?>selected=="selected" <?php } ?>
					
					value="1" >Contractor</option>
					
					<option <?php if($Type==2){  ?>selected=="selected" <?php } ?>
					
					value="2" >Mastri</option>
					
						</select>
					</div> 
										
										 
					
					<div class="form-group col-lg-3" > 
				<label for="exampleInputEmail1"> Contractor/Mastri</label>
                 <select class="form-control select2" id="ContactorID" name="ContactorID" required>
				  <option value="">Select</option>
				 <?php
				
				 while($RowContractor=$StmtContractor->fetch_array()){  ?> 
				
					<option <?php if($RowSubContract['ContactorID']==$RowContractor['ContractorID']){  ?>selected=="selected" <?php } ?>
					
					value="<?php echo $RowContractor['ContractorID'] ;?>" >
				 <?php  echo $RowContractor['ContrctrName']; ?>
					</option>
					<?php }   ?>
						</select>
					</div> 

    
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Batch Name<span style="color:red">*</span></label>
                  <input required type="text" class="form-control" name='SubContractorName' id="SubContractorName"
				  placeholder="Enter Batch Name" value="<?php echo $RowSubContract['SubContractorName'] ?>">
                </div>
				<?php 
				if(!empty($RstrictNoOfPersons)){?>
					<div class="form-group col-lg-3">
						<label for="exampleInputPassword1"> No Of Persons Per Batch<span style="color:red">*</span></label>
						<input type="number" class="form-control"  name="NoOfPersonsPerBatch" id="NoOfPersonsPerBatch" 
						placeholder="Enter No Of Persons Per Batch" value="<?php echo $RowSubContract['NoOfPersonsPerBatch'] ?>" required>
					</div>
				<?php }?>
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Comissions(Per Person)<span style="color:red">*</span></label>
                  <input required type="text" class="form-control"  name="Comissions" id="Comissions" 
				  placeholder="Enter Comissions" value="<?php echo $RowSubContract['Comissions'] ?>" required>
                </div>
				
				
	
				<div class="form-group col-lg-3">
                 <label for="exampleInputPassword1">Auto Charge(Per Person)<span style="color:red">*</span> </label>
                  <input required type="text" class="form-control"  name="AutoCharge" id="AutoCharge" 
				  placeholder="Enter Auto Charge Per Person" value="<?php echo $RowSubContract['AutoCharge'] ?>" required>
                </div>
				
				<div class="form-group col-lg-3">
                 <label for="exampleInputPassword1">Area/Locality<span style="color:red">*</span></label>
                  <input required type="text" class="form-control"  name="Area" id="Area" 
				  placeholder="Enter Area/Locality" value="<?php echo $RowSubContract['Area'] ?>">
                </div>
				<div class="form-group col-lg-3">
                 <label for="exampleInputPassword1">Extras<span style="color:red">*</span></label>
                  <input required type="text" class="form-control"  name="Extras" id="Extras" 
				  placeholder="Enter Extras"  required value="<?php echo $RowSubContract['Extras'] ?>">
                </div>
                </div>
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			 <a target="" href="SubContractorList.php"   class="btn btn-danger  "> Close</a>
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
		
  
       
    </body>
</html>	

<script>
$("#Type").change(function(e){
			e.preventDefault();	
			getBatches();
		});	
		
		
function getBatches(){


var Type=$('#Type').val(); 

 
	 $.ajax({
    type: 'POST',
    url: 'Ajax/AjSupplierDetails.php',
    data: {'Type' : Type},  
    success: function(response){
		 $("#ContactorID").html(response); 
	 }
  });	  
  }

</script>
			

