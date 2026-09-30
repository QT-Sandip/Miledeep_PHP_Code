<?php
$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="PreProcessing";

$ContactorID=$_GET['ID'];



 $SqlContract=" SELECT * FROM `PpcContractMaster` where ContractorID='$ContactorID'";
$StmtContract=$iconn->query($SqlContract);
 $RowContract=$StmtContract->fetch_array();

	 $Count=mysqli_num_rows($StmtContract); 
	 if($Count==1){
$PageTitle="Edit  Contractor";
}

else{
$PageTitle="Add  Contractor";
}

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){

if($Count==1){
	$oldContractorData = legacyAuditFetchRowById('PpcContractMaster', 'ContractorID', $ContactorID, 'PreProcessing');
	 $SqlClientInsert="UPDATE `PpcContractMaster` SET `ContrctrName`='$ContrctrName',`PhoneNumber`='$PhoneNumber',
	`Comission`='$Comission',`AutoCharge`='$AutoCharge',
	`Extras`='$Extras',
	`Type`='$Type' WHERE ContractorID='$ContactorID'"; 

	
} else
	{
	 $SqlClientInsert="INSERT INTO `PpcContractMaster`
	 ( `ContrctrName`, `PhoneNumber`, `Comission`, `AutoCharge`, `Extras`,`Type`)
	 VALUES ('$ContrctrName','$PhoneNumber','$Comission','$AutoCharge',
	 '$Extras','$Type')"; 
	}
	
	
	

	$StmtClientInsert=$iconn->query($SqlClientInsert);
	
	if($StmtClientInsert){
		if($Count==1){
			legacyAuditLogUpdateById('PpcContractMaster', 'ContractorID', $ContactorID, $oldContractorData, $_POST, [
				'ReferenceNo' => $ContactorID,
				'Remarks' => 'Contractor updated',
			], 'PreProcessing');
		}else{
			$newContractorID = $iconn->insert_id;
			legacyAuditLogInsertById('PpcContractMaster', 'ContractorID', $newContractorID, $_POST, [
				'ReferenceNo' => $newContractorID,
				'Remarks' => 'Contractor created',
			], 'PreProcessing');
		}
		
		echo "<script>alert('Success');
		//window.close();
		</script>";
		
	}
	
		header('Location:ContractorList.php');

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
                 <select required class="form-control select2" id="Type" name="Type" required>
				
				
				
				
					<option <?php if($RowContract['Type']==1){  ?>selected=="selected" <?php } ?>
					
					value="1" >Contractor</option>
					<option <?php if($RowContract['Type']==2){  ?>selected=="selected" <?php } ?>
					
					value="2" >Mastri</option>
					
						</select>
					</div> 
    
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Name(Contractor/Mastri)<span style="color:red">*</span></label>
                  <input required type="text" class="form-control" name='ContrctrName' id="ContrctrName"
				  placeholder="Enter Contractor Name" value="<?php echo $RowContract['ContrctrName'] ?>">
                </div>
				
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Phone Number</label>
                  <input type="text" class="form-control"  name="PhoneNumber" id="PhoneNumber" 
				  placeholder="Enter Phone Number" value="<?php echo $RowContract['PhoneNumber'] ?>" required>
                </div>
				
				
	
				<div class="form-group col-lg-3">
                 <label for="exampleInputPassword1">Comission(Per Person)<span style="color:red">*</span></label>
                  <input required type="text" class="form-control"  name="Comission" id="Comission" 
				  placeholder="Enter Comission" value="<?php echo $RowContract['Comission'] ?>"  required>
                </div>
				
				<div class="form-group col-lg-3">
                 <label for="exampleInputPassword1">Auto Charge(Per Person)<span style="color:red">*</span></label>
                  <input required type="text" class="form-control"  name="AutoCharge" id="AutoCharge" 
				  placeholder="Enter Auto Charge" value="<?php echo $RowContract['AutoCharge'] ?>">
                </div>
				<div class="form-group col-lg-3">
                 <label for="exampleInputPassword1">Extras<span style="color:red">*</span></label>
                  <input required type="text" class="form-control"  name="Extras" id="Extras" 
				  placeholder="Enter Extras"  required value="<?php echo $RowContract['Extras'] ?>">
                </div>
                </div>
				
				
			 
		
			  <div class="col-xs-12 box-footer"  align="right">
			  						  <a target="" href="ContractorList.php"   class="btn btn-danger  "> Close</a>
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

