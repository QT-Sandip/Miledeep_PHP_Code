<?php

$Status=$_SESSION['Status'];

$PageTitle="Employee Deduction Details";
$PageTitleFolder="HRM";
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
extract($_POST);
extract($_GET);

 $EmpID=$_GET['EmpID']; 

$SqlEmpSalaryDetails="SELECT * FROM `EmpDeductionDetails` WHERE EmpID='$EmpID'";
$StmtEmpSalaryDetails=$iconn->query($SqlEmpSalaryDetails);
$RowEmpSalaryDetails=$StmtEmpSalaryDetails->fetch_array();
$Count=mysqli_num_rows($StmtEmpSalaryDetails);
if(isset($_POST['submit'])){
	
	IF($Count==0){
		
$SqlNewEmployee="INSERT INTO `EmpDeductionDetails`(`EmpID`,`ESI`, `PFPerc`, `MessChargePerDay`, `DailyDeduction`,
 `MonthlyDeduction`,`EsiNumber`, `PFNumber`) VALUES ('$EmpID','$ESI','$PFPerc','$MessChargePerDay','$DailyDeduction','$MonthlyDeduction','$EsiNumber', '$PFNumber')";		
	

	}
	
	else{
	 $SqlNewEmployee="UPDATE `EmpDeductionDetails` SET `ESI`='$ESI',`PFPerc`='$PFPerc',
	`MessChargePerDay`='$MessChargePerDay',`DailyDeduction`='$DailyDeduction',
	`MonthlyDeduction`='$MonthlyDeduction',
	`EsiNumber`='$EsiNumber',
	`PFNumber`='$PFNumber'  WHERE  `EmpID`='$EmpID'"; 
	}

	  
	$oldData = null;
	if ($Count != 0) {
		$oldData = legacyAuditFetchRowById('EmpDeductionDetails', 'EmpID', $EmpID, 'Hrm');
	}

	if($StmtNewEmployee=$iconn->query($SqlNewEmployee)){
		if ($Count == 0) {
			legacyAuditLogInsertById('EmpDeductionDetails', 'EmpID', $EmpID, $_POST, array(), 'Hrm');
		} else {
			legacyAuditLogUpdateById('EmpDeductionDetails', 'EmpID', $EmpID, $oldData, $_POST, array(), 'Hrm');
		}
	$_SESSION['Status'] = 'Deduction Updated  Successfully';
	
	header("Location:EmployeeDeductionDetails.php");
	
	}else{
	$_SESSION['Status'] = $iconn->error ."Unable To Update Deduction";
	header("Location:EmployeeDeductionDetails.php");	
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

	
	 <?php include ($IncludesPath."PreLoader.php"); ?>

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
				                    <label for="exampleInputEmail1" >ESI</label>

                    <input class="form-control" type="text" 
					id="ESI" name="ESI"
					placeholder="Enter  ESI"   value="<?php echo $RowEmpSalaryDetails['ESI']  ?>"  required>                 
                  </div>
				
				<div class="form-group  col-lg-3">
				                  <label for="exampleInputEmail1">PF Amount/Month</label>

                    <input class="form-control" type="text"
					id="PFPerc" name="PFPerc"
					placeholder="Enter PF Percentage"  value="<?php echo $RowEmpSalaryDetails['PFPerc'] ?>" >                 
                  </div>
				
                
				<!--<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Mess Charge Per Day</label>
                    <input class="form-control" type="text"
					placeholder="Enter Mess Charge Per Day" value="<?php echo $RowEmpSalaryDetails['MessChargePerDay'] ?>" 
					id="MessChargePerDay" name="MessChargePerDay" required>                 
                  </div>-->
				
				
				 		
				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Daily Deduction</label>
                    <input class="form-control" type="text"
					placeholder="Enter Daily Deduction" value="<?php echo $RowEmpSalaryDetails['DailyDeduction'] ?>" 
					id="DailyDeduction" name="DailyDeduction" required>                 
                  </div>
				
				
				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Monthly Deduction</label>
                    <input class="form-control" type="text"
					placeholder="Enter Monthly Deduction" value="<?php echo $RowEmpSalaryDetails['MonthlyDeduction'] ?>" 
					id="MonthlyDeduction" name="MonthlyDeduction" required>                 
                  </div>
				
				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Esi Number</label>
                    <input class="form-control" type="text"
					placeholder="Enter Esi Number" value="<?php echo $RowEmpSalaryDetails['EsiNumber'] ?>" 
					id="EsiNumber" name="EsiNumber" required>                 
                  </div>
				  <div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">PF Number</label>
                    <input class="form-control" type="text"
					placeholder="Enter PF Number" value="<?php echo $RowEmpSalaryDetails['PFNumber'] ?>" 
					id="PFNumber" name="PFNumber" required>                 
                  </div>
				  
				   <!-- end row -->
									</div>
									<div class="col-xs-12 box-footer"  align="right">
                <?php IF($Count==0){ ?>
				<button  type="submit"  name='submit'  class="btn btn-primary waves-effect waves-light">
				<i class= 'ri-add-fill align-middle ml-2'>
				 </i>
				Add</button>
				<?php } else{
					?>

				<button  type="submit"  name='submit'  class="btn btn-primary waves-effect waves-light">
				<i class= 'fa fa-edit align-middle ml-2'>
				 </i>
				Update</button><?php } ?>
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




