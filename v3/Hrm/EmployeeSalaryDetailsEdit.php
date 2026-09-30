<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
$Status=$_SESSION['Status'];

$PageTitleFolder="HRM";
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
extract($_POST);
extract($_GET);

 $EmpID=$_GET['EmpID']; 

$SqlEmpSalaryDetails="SELECT
		    HRED.*,
		    HRSD.*,
		    HRED.EmpCategory,
		    HRWS.CategoryName,
		    Village.VillageName,
		    Batch.BatchName,
		    Section.SectionName
		FROM HrmEmployeeDetails HRED
		LEFT JOIN `HrmEmpSalaryDetails` HRSD ON 
			 HRED.EmpID = HRSD.EmpID
		LEFT JOIN EmpWorkerSubCategories HRWS ON
		    HRWS.CategoryID = HRED.WorkerSubCategory
		LEFT JOIN EmpWorkerSectionsMaster Section ON
		    Section.SectionID = HRED.SectionID
		LEFT JOIN EmpWorkerBatchMaster Batch ON
		    Batch.BatchID = HRED.BatchID
		LEFT JOIN EmpWorkerVillageMaster Village ON
		    Village.VID = HRED.VillageID
		WHERE
		    HRED.EmpID = '$EmpID'";
$StmtEmpSalaryDetails=$iconn->query($SqlEmpSalaryDetails);
$RowEmpSalaryDetails=$StmtEmpSalaryDetails->fetch_array();
$TitleText="";
if($RowEmpSalaryDetails['EmpCategory']!="" && $RowEmpSalaryDetails['EmpCategory'] =='Staff'){
	if($RowEmpSalaryDetails['EmpCategory']!=""){
	 $TitleText.=' - '.$RowEmpSalaryDetails['EmpCategory'];
	}	
}else{
	if($RowEmpSalaryDetails['CategoryName']!=""){
	 $TitleText.=' - '.$RowEmpSalaryDetails['CategoryName'];
	}
	if($RowEmpSalaryDetails['VillageName']!=""){
	 $TitleText.=' - '.$RowEmpSalaryDetails['VillageName'];
	}
	if($RowEmpSalaryDetails['SectionName']!=""){
	 $TitleText.=' - '.$RowEmpSalaryDetails['SectionName'];
	}
	if($RowEmpSalaryDetails['BatchName']!=""){
	 $TitleText.=' - '.$RowEmpSalaryDetails['BatchName'];
	}
}
$PageTitle="Employee Salary Details$TitleText";

$SqlEmpSalaryDetailsCount="SELECT * FROM `HrmEmpSalaryDetails` WHERE EmpID='$EmpID'";
$StmtEmpSalaryDetailsCount=$iconn->query($SqlEmpSalaryDetailsCount);
$Count=mysqli_num_rows($StmtEmpSalaryDetailsCount);

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$SplitStaffAndWorkerSalarySheets = $ArrayClientConfigData['SplitStaffAndWorkerSalarySheets'];
$SalaryDetails=[];
if($SplitStaffAndWorkerSalarySheets){
	$isHideFileds=true;
	$SalaryDetails['Basic']=[
    'amount' => $RowEmpSalaryDetails['Basic'],
    'status' => ''
	];
	$SalaryDetails['HRA']=[
    'amount' => 0,
    'status' => 'disabled',
    'display'=> 'none'
	];
	$SalaryDetails['DailyAllowance']=[
    'amount' => 0,
    'status' => 'disabled',
    'display'=> 'none'
	];
	$SalaryDetails['SpecialAllowance']=[
    'amount' => 0,
    'status' => 'disabled',
    'display'=> 'none'
	];
	$SalaryDetails['MedicalAllowance']=[
    'amount' => 0,
    'status' => 'disabled',
    'display'=> 'none'
	];
	$SalaryDetails['Conveyence']=[
    'amount' => 0,
    'status' => 'disabled',
    'display'=> 'none'
	];
	$SalaryDetails['Incentives']=[
    'amount' => 0,
    'status' => 'disabled',
    'display'=> 'none'
	];
	$SalaryDetails['Others']=[
    'amount' => 0,
    'status' => 'disabled',
    'display'=> 'none'
	];
	$SalaryDetails['AutoChargePerDay']=[
    'amount' => $RowEmpSalaryDetails['AutoChargePerDay'],
    'status' => 'disabled',
    'display'=> 'none'
	];
}else{
	$isHideFileds=false;
	$SalaryDetails['Basic']=[
    'amount' => $RowEmpSalaryDetails['Basic'],
    'status' => ''
	];
	$SalaryDetails['HRA']=[
    'amount' => $RowEmpSalaryDetails['HRA'],
    'status' => ''
	];
	$SalaryDetails['DailyAllowance']=[
    'amount' => $RowEmpSalaryDetails['DailyAllowance'],
    'status' => ''
	];
	$SalaryDetails['SpecialAllowance']=[
    'amount' => $RowEmpSalaryDetails['SpecialAllowance'],
    'status' => ''
	];
	$SalaryDetails['MedicalAllowance']=[
    'amount' => $RowEmpSalaryDetails['MedicalAllowance'],
    'status' => ''
	];
	$SalaryDetails['Conveyence']=[
    'amount' => $RowEmpSalaryDetails['Conveyence'],
    'status' => ''
	];
	$SalaryDetails['Incentives']=[
    'amount' => $RowEmpSalaryDetails['Incentives'],
    'status' => ''
	];
	$SalaryDetails['Others']=[
    'amount' => $RowEmpSalaryDetails['Others'],
    'status' => ''
	];
	$SalaryDetails['AutoChargePerDay']=[
    'amount' => $RowEmpSalaryDetails['AutoChargePerDay'],
    'status' => ''
	];
}

if(isset($_POST['submit'])){
	
	IF($RowEmpSalaryDetails['Basic']==""){
		
$SqlNewEmployee="INSERT INTO `HrmEmpSalaryDetails`(`EmpID`, `Basic`, `HRA`, `DailyAllowance`,
 `SpecialAllowance`, `MedicalAllowance`, `Conveyence`, `Others`, `Incentives`,`AutoChargePerDay`) VALUES 
 ('$EmpID','$Basic','$HRA','$DailyAllowance','$SpecialAllowance','$MedicalAllowance','$Conveyence','$Others',
 '$Incentives','$AutoChargePerDay')";		
	

	}
	
	else{
	$SqlNewEmployee="UPDATE `HrmEmpSalaryDetails` SET `Basic`='$Basic',
	`HRA`='$HRA',`DailyAllowance`='$DailyAllowance',`SpecialAllowance`='$SpecialAllowance',`MedicalAllowance`='$MedicalAllowance',
	`Conveyence`='$Conveyence',
	`AutoChargePerDay`='$AutoChargePerDay',
	`Others`='$Others',`Incentives`='$Incentives' WHERE  `EmpID`='$EmpID'";
	}
	$oldData = null;
	if ($RowEmpSalaryDetails['Basic'] != "") {
		$oldData = legacyAuditFetchRowById('HrmEmpSalaryDetails', 'EmpID', $EmpID, 'Hrm');
	}

	if($StmtNewEmployee=$iconn->query($SqlNewEmployee)){
		if ($RowEmpSalaryDetails['Basic'] == "") {
			legacyAuditLogInsertById('HrmEmpSalaryDetails', 'EmpID', $EmpID, $_POST, array(), 'Hrm');
		} else {
			legacyAuditLogUpdateById('HrmEmpSalaryDetails', 'EmpID', $EmpID, $oldData, $_POST, array(), 'Hrm');
		}
	$_SESSION['Status'] = 'Updated Salary Details Successfully';
	$lastInsertedId = $iconn->insert_id; 
	$_SESSION['Status'] = 'Updated Salary Details Successfully. Please add Employee Details';
    echo "
    <script type='text/javascript'>
        var answer = confirm('Salary Details Updated successfully. Do you want to add Employee Details?');
        if (answer) {
            window.location.href = 'EmployeePersonalDetailsEdit?EmpID=$EmpID';
        } else {
            window.location.href = 'EmployeeList.php';
        }
    </script>
    ";	
	//header("Location:EmployeeSalaryDetails.php");
	
	}else{
	$_SESSION['Status'] = $iconn->error ."Unable To Update Salary Details";
	header("Location:EmployeeSalaryDetails.php");	
	}
			
}
	  
	  


?>




<!DOCTYPE html>


<html lang="en">

    <head>
        
                <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php  include ("Includes/DbLinks.php");?>
                <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php	include ($IncludesPath."PreLoader.php"); ?>

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
				
				  
				  <div class="form-group  col-lg-3">
				                    <label for="exampleInputEmail1" ><?php echo ($SplitStaffAndWorkerSalarySheets) ? "Gross Salary" : 'Basic Salary' ?></label>

                    <input class="form-control" type="text" 
					id="Basic" name="Basic"
					placeholder="<?php echo ($SplitStaffAndWorkerSalarySheets) ? "Enter Gross Salary" : 'Enter Basic Salary' ?>"  value="<?php echo $SalaryDetails['Basic']['amount']  ?>"  required>                 
                  </div>
				
				<div class="form-group  col-lg-3 <?php echo ($SplitStaffAndWorkerSalarySheets) ? "hide-emp-field" : '' ?>">
				                  <label for="exampleInputEmail1">HRA</label>

                    <input class="form-control" type="text"
					id="HRA" name="HRA"
					placeholder="Enter HRA" <?php echo $SalaryDetails['HRA']['status']  ?> value="<?php echo $SalaryDetails['HRA']['amount'] ?>" >                 
                  </div>
				
                
			
				
				
				

				<div class="form-group  col-lg-3 <?php echo ($SplitStaffAndWorkerSalarySheets) ? "hide-emp-field" : '' ?>">
				<label for="exampleInputEmail1">Daily Allowance</label>
                    <input class="form-control" type="text"
					placeholder="Enter Daily Allowance" <?php echo $SalaryDetails['DailyAllowance']['status']  ?> value="<?php echo $SalaryDetails['DailyAllowance']['amount'] ?>" 
					id="DailyAllowance" name="DailyAllowance" required>                 
                  </div>
				
				
				<div class="form-group  col-lg-3 <?php echo ($SplitStaffAndWorkerSalarySheets) ? "hide-emp-field" : '' ?>">
				<label for="exampleInputEmail1">Special Allowance</label>
                    <input class="form-control" type="text"
					placeholder="Enter Special Allowance" <?php echo $SalaryDetails['SpecialAllowance']['status']  ?> value="<?php echo $SalaryDetails['SpecialAllowance']['amount'] ?>" 
					id="SpecialAllowance" name="SpecialAllowance" required>                 
                  </div>
				
				
				<div class="form-group  col-lg-3 <?php echo ($SplitStaffAndWorkerSalarySheets) ? "hide-emp-field" : '' ?>">
				<label for="exampleInputEmail1">Medical Allowance</label>
                    <input class="form-control" type="text"
					placeholder="Enter Medical Allowance" <?php echo $SalaryDetails['MedicalAllowance']['status']  ?> value="<?php echo $SalaryDetails['MedicalAllowance']['amount'] ?>" 
					id="MedicalAllowance" name="MedicalAllowance" required>                 
                  </div>
				  
				<div class="form-group  col-lg-3 <?php echo ($SplitStaffAndWorkerSalarySheets) ? "hide-emp-field" : '' ?>">
								<label for="exampleInputEmail1">Conveyance</label>

                    <input class="form-control" type="text"
					placeholder="Enter Conveyance" <?php echo $SalaryDetails['Conveyence']['status']  ?> value="<?php echo $SalaryDetails['Conveyence']['amount'] ?>" 
					id="Conveyence" name="Conveyence"  required>                 
                  </div>
				<div class="form-group  col-lg-3 <?php echo ($SplitStaffAndWorkerSalarySheets) ? "hide-emp-field" : '' ?>">
	<label for="exampleInputEmail1">Incentives</label>

                    <input class="form-control" type="text"
					placeholder="Enter Incentives" <?php echo $SalaryDetails['Incentives']['status']  ?> value="<?php echo $SalaryDetails['Incentives']['amount'] ?>" 
					id="Incentives" name="Incentives"  >                 
                  </div>
				  <div class="form-group  col-lg-3 <?php echo ($SplitStaffAndWorkerSalarySheets) ? "hide-emp-field" : '' ?>">
				  <label for="exampleInputEmail1">Others</label>

                    <input class="form-control" type="text"
					placeholder="Others" <?php echo $SalaryDetails['Others']['status']  ?> value="<?php echo $SalaryDetails['Others']['amount'] ?>" 
					id="Others" name="Others"  >                 
                  </div>

				  <?php if ($SplitStaffAndWorkerSalarySheets){
				  	?>
						  <div class="form-group  col-lg-3">
						<label for="exampleInputEmail1">Auto Charge Per Day</label>
		                    <input class="form-control" type="text"
							placeholder="Enter Auto Charge/Day" value="<?php echo $SalaryDetails['AutoChargePerDay']['amount'] ?>" 
							id="AutoChargePerDay" name="AutoChargePerDay" required>                 
		                  </div>				  				  	
				  	<?php
				  }
				  ?>

				
			  
        <!-- end row -->
									</div>
									<div class="col-xs-12 box-footer"  align="right">
                <!-- <?php //IF($Count==0){ ?>
				<button  type="submit"  name='submit'  class="btn btn-primary waves-effect waves-light">
				<i class= 'ri-add-fill align-middle ml-2'>
				 </i>
				Add</button>
				<?php// } else{
					?>

				<button  type="submit"  name='submit'  class="btn btn-primary waves-effect waves-light">
				<i class= 'fa fa-edit align-middle ml-2'>
				 </i>
				Update</button><?php //} ?> -->

				<a target="" href="EmployeeSalaryDetails" class="btn btn-danger"> Close</a>
                                            <button type="submit" name='submit' class="btn btn-success"> Submit </button>
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
		<?php include ("Includes/NewScripts.php");?>
      
  
       
    </body>
</html>				




