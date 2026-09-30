<?php

$Status=$_SESSION['Status'];

$PageTitle="Employee Personal Details";
$PageTitleFolder="HRM";
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
extract($_POST);
extract($_GET);

 $EmpID=$_GET['EmpID']; 
$SqlEmpName="SELECT * FROM `HrmEmployeeDetails` WHERE EmpID='$EmpID'";
$StmtEmpName=$iconn->query($SqlEmpName);
$RowEmpName=$StmtEmpName->fetch_array();



/***Bank Details***/
$SqlAdminBank="SELECT * FROM `AdminBankDetails`";
$StmtAdminBank=$iconn->query($SqlAdminBank);

$SqlEmpBankDetails="SELECT * FROM `HrmEmpBankAccountDetails` WHERE EmpID='$EmpID'";
$StmtEmpBankDetails=$iconn->query($SqlEmpBankDetails);
$RowEmpBankDetails=$StmtEmpBankDetails->fetch_array();
$BankDetailsCount=mysqli_num_rows($StmtEmpBankDetails);
/***Bank Details***/

/***Deduction Details***/
$SqlEmpDeductionDetails="SELECT * FROM `EmpDeductionDetails` WHERE EmpID='$EmpID'";
$StmtEmpDeductionDetails=$iconn->query($SqlEmpDeductionDetails);
$RowEmpDeductionDetails=$StmtEmpDeductionDetails->fetch_array();
$DeductionDetailsCount=mysqli_num_rows($StmtEmpDeductionDetails);
/***Deduction Details***/


/***Salary Details***/
$SqlEmpSalaryDetails="SELECT * FROM `HrmEmpSalaryDetails` WHERE EmpID='$EmpID'";
$StmtEmpSalaryDetails=$iconn->query($SqlEmpSalaryDetails);
$RowEmpSalaryDetails=$StmtEmpSalaryDetails->fetch_array();
$SalaryDetailsCount=mysqli_num_rows($StmtEmpSalaryDetails);
/***Salary Details***/


/***Personal Details***/
$SqlEmpDetails="SELECT * FROM `HrmEmpPersonalDetails` WHERE EmpID='$EmpID'";
$StmtEmpDetails=$iconn->query($SqlEmpDetails);
$RowEmpDetails=$StmtEmpDetails->fetch_array();
$PersonalDetailsCount=mysqli_num_rows($StmtEmpDetails);
/***Personal Details***/








if(isset($_POST['submit'])){
	
	
	
	/***Bank Details***/
IF($BankDetailsCount==0){
		
$SqlBank="INSERT INTO `HrmEmpBankAccountDetails`(`EmpID`, `AccNumber`, `BankID`, `IFSC`, `AccType`, `DateOfCreation`) 
VALUES('$EmpID','$AccNumber','$BankID','$IFSC','$AccType','$DateOfCreation') ";		
	

	}
	
	else{
	$SqlBank="UPDATE `HrmEmpBankAccountDetails` SET 
	`AccNumber`='$AccNumber',`BankID`='$BankID',`IFSC`='$IFSC',`AccType`='$AccType',`DateOfCreation`='$DateOfCreation'
	WHERE  `EmpID`='$EmpID'";
	}
/***Bank Details***/

/***Personal Details***/
IF($PersonalDetailsCount==0){
		
$SqlPersonal="INSERT INTO `HrmEmpPersonalDetails`(`EmpID`, `PhoneNumber`, `AltPhoneNumber`, `MaritalStatus`,
 `PresentAddress`, `PermanentAddress`, `DateOfBirth`, `AadharNumber`, `ESICNo`, `PANNo`) VALUES
 ('$EmpID','$PhoneNumber','$AltPhoneNumber','$MaritalStatus','$PresentAddress','$PermanentAddress','$DateOfBirth'
 ,'$AadharNumber','$ESICNo','$PANNo')";		
	

	}
	
	else{
	$SqlPersonal="UPDATE `HrmEmpPersonalDetails` SET `PhoneNumber`='$PhoneNumber',
		`AltPhoneNumber`='$AltPhoneNumber',`MaritalStatus`='$MaritalStatus',`PresentAddress`='$PresentAddress',`PermanentAddress`='$PermanentAddress',
		`DateOfBirth`='$DateOfBirth',`AadharNumber`='$AadharNumber',
		`ESICNo`='$ESICNo',`PANNo`='$PANNo' WHERE `EmpID`='$EmpID'";
	}
/***Personal Details***/


/***Deduction Details***/
IF($DeductionDetailsCount==0){
		
$SqlDeduction="INSERT INTO `EmpDeductionDetails`(`EmpID`,`ESI`, `PFPerc`, `MessChargePerDay`, `DailyDeduction`,
 `MonthlyDeduction`,`EsiNumber`, `PFNumber`) VALUES ('$EmpID','$ESI','$PFPerc','$MessChargePerDay','$DailyDeduction','$MonthlyDeduction','$EsiNumber', '$PFNumber')";		
	

	}
	
	else{
	 $SqlDeduction="UPDATE `EmpDeductionDetails` SET `ESI`='$ESI',`PFPerc`='$PFPerc',
	`MessChargePerDay`='$MessChargePerDay',`DailyDeduction`='$DailyDeduction',
	`MonthlyDeduction`='$MonthlyDeduction',
	`EsiNumber`='$EsiNumber',
	`PFNumber`='$PFNumber'  WHERE  `EmpID`='$EmpID'"; 
	}
/***Deduction Details***/

/***Salary Details***/
IF($SalaryDetailsCount==0){
		
 $SqlSalary="INSERT INTO `HrmEmpSalaryDetails`(`EmpID`, `Basic`, `HRA`, `DailyAllowance`,
 `SpecialAllowance`, `MedicalAllowance`, `Conveyence`, `Others`, `Incentives`) VALUES 
 ('$EmpID','$Basic','$HRA','$DailyAllowance','$SpecialAllowance','$MedicalAllowance','$Conveyence','$Others',
 '$Incentives')";		
	

	}
	
	else{
	$SqlSalary="UPDATE `HrmEmpSalaryDetails` SET `Basic`='$Basic',
	`HRA`='$HRA',`DailyAllowance`='$DailyAllowance',`SpecialAllowance`='$SpecialAllowance',`MedicalAllowance`='$MedicalAllowance',
	`Conveyence`='$Conveyence',
	`Others`='$Others',`Incentives`='$Incentives' WHERE  `EmpID`='$EmpID'";
	}
/***Salary Details***/
/*echo  $SqlBank;
echo  $SqlPersonal;
echo  $SqlDeduction;
echo  $SqlSalary; exit;*/

$oldBankData = $BankDetailsCount > 0 ? legacyAuditFetchRowById('HrmEmpBankAccountDetails', 'EmpID', $EmpID, 'Hrm') : null;
$oldPersonalData = $PersonalDetailsCount > 0 ? legacyAuditFetchRowById('HrmEmpPersonalDetails', 'EmpID', $EmpID, 'Hrm') : null;
$oldDeductionData = $DeductionDetailsCount > 0 ? legacyAuditFetchRowById('EmpDeductionDetails', 'EmpID', $EmpID, 'Hrm') : null;
$oldSalaryData = $SalaryDetailsCount > 0 ? legacyAuditFetchRowById('HrmEmpSalaryDetails', 'EmpID', $EmpID, 'Hrm') : null;

$StmtBank=$iconn->query($SqlBank);
$StmtPersonal=$iconn->query($SqlPersonal);
$StmtDeduction=$iconn->query($SqlDeduction);
$StmtSalary=$iconn->query($SqlSalary);
	  
	if($StmtBank&&$StmtPersonal&&$StmtDeduction&&$StmtSalary){
	if ($BankDetailsCount == 0) {
		legacyAuditLogInsertById('HrmEmpBankAccountDetails', 'EmpID', $EmpID, $_POST, array(), 'Hrm');
	} else {
		legacyAuditLogUpdateById('HrmEmpBankAccountDetails', 'EmpID', $EmpID, $oldBankData, $_POST, array(), 'Hrm');
	}
	if ($PersonalDetailsCount == 0) {
		legacyAuditLogInsertById('HrmEmpPersonalDetails', 'EmpID', $EmpID, $_POST, array(), 'Hrm');
	} else {
		legacyAuditLogUpdateById('HrmEmpPersonalDetails', 'EmpID', $EmpID, $oldPersonalData, $_POST, array(), 'Hrm');
	}
	if ($DeductionDetailsCount == 0) {
		legacyAuditLogInsertById('EmpDeductionDetails', 'EmpID', $EmpID, $_POST, array(), 'Hrm');
	} else {
		legacyAuditLogUpdateById('EmpDeductionDetails', 'EmpID', $EmpID, $oldDeductionData, $_POST, array(), 'Hrm');
	}
	if ($SalaryDetailsCount == 0) {
		legacyAuditLogInsertById('HrmEmpSalaryDetails', 'EmpID', $EmpID, $_POST, array(), 'Hrm');
	} else {
		legacyAuditLogUpdateById('HrmEmpSalaryDetails', 'EmpID', $EmpID, $oldSalaryData, $_POST, array(), 'Hrm');
	}
	$_SESSION['Status'] = 'Success';
	$Msg="Success";
	 echo "<script type='text/javascript'>alert('$Msg');</script>";

	
	header("Location:EmployeeList.php");
	
	}else{
	$_SESSION['Status'] = $iconn->error ."Failed";
	$Msg="Failed";
		 echo "<script type='text/javascript'>alert('$Msg');</script>";

	header("Location:EmployeeList.php");	
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
<h3 align="Center"><B>EMPLOYEE DETAILS-<?php echo $RowEmpName['EmpName'];?></B></h3>
                        <?php include ($IncludesPath."page-title.php");?>

      								

       <div class="row">
                            <div class="col-12">
														<form role="form" method="post">
	
		<div class="card">
								
							<!--	<form role="form" method="post">-->
                                    <div class="card-body">
									
										<div class="row">
			

			


				
				<div class="form-group col-lg-3">
													<label for="exampleInputEmail1">Marital Status</label>

                   <select class="form-control select2" name='MaritalStatus' id="MaritalStatus" required>
				   <option  value="">Select Marital Status </option>
				   <option  <?php if($RowEmpDetails['MaritalStatus']=="Married"){ ?> selected="selected" <?php }?>
				   
				   value="Married">Married</option>
				   <option  <?php if($RowEmpDetails['MaritalStatus']=="UnMarried"){ ?> selected="selected" <?php }?>
				   
				   value="UnMarried">UnMarried</option>
				  </select>
                </div>
				
				
				
				  
				  <div class="form-group  col-lg-3">
				  	<label for="exampleInputEmail1">Phone Number</label>

                    <input class="form-control" type="text" 
					id="PhoneNumber" name="PhoneNumber"
					placeholder="Enter Phone Number"  onchange="stringlength($(this).val());" 
					value="<?php echo $RowEmpDetails['PhoneNumber']  ?>"  required>                 
                  </div>
				
				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Alternate Phone Number</label>

                    <input class="form-control" type="text"
					id="AltPhoneNumber" name="AltPhoneNumber"
					placeholder="Enter Alternate Phone Number" maxlength="10" value="<?php echo $RowEmpDetails['AltPhoneNumber'] ?>" >                 
                  </div>
				
                
			
				
				
				

				<div class="form-group  col-lg-3">
			    <label for="exampleInputEmail1">Present Address</label>

                    <input class="form-control" type="text"
					placeholder="Enter Present Address" value="<?php echo $RowEmpDetails['PresentAddress'] ?>" 
					id="PresentAddress" name="PresentAddress" required>                 
                  </div>
				
				
				<div class="form-group  col-lg-3">
									<label for="exampleInputEmail1">Permanent Address</label>

                    <input class="form-control" type="text"
					placeholder="Enter Permanent Address" value="<?php echo $RowEmpDetails['PermanentAddress'] ?>" 
					id="PermanentAddress" name="PermanentAddress" required>                 
                  </div>
				
				
				<div class="form-group  col-lg-3">
									<label for="exampleInputEmail1">DOB</label>

                    <input class="form-control" type="date"
					placeholder="Enter Date Of Birth" value="<?php echo $RowEmpDetails['DateOfBirth'] ?>" 
					id="DateOfBirth" name="DateOfBirth" required>                 
                  </div>
				  
				<div class="form-group  col-lg-3">
					<label for="exampleInputEmail1">Aadhar Number</label>

                    <input class="form-control" type="text"
					placeholder="Enter Aadhar Number" value="<?php echo $RowEmpDetails['AadharNumber'] ?>" 
					id="AadharNumber" name="AadharNumber" maxlength="12" required>                 
                  </div>
				
				  
				<div class="form-group  col-lg-3">
					<label for="exampleInputEmail1">PAN Number</label>

                    <input class="form-control" type="text"
					placeholder="Enter PAN Number" value="<?php echo $RowEmpDetails['PANNo'] ?>" 
					id="PANNo" name="PANNo" maxlength="10"  required>                 
                  </div>
				
				
				
			  
        <!-- end row -->
									</div>
									
									</div>
									</div>
        					
			<h6 align="LEFT"><B>EMPLOYEE BANK DETAILS</B></h6>
				
							
                                <div class="card">

							<!--	<form role="form" method="post">-->
                                    <div class="card-body">
									
										<div class="row">
										
                            <div class="form-group">
	                     <label for="exampleInputEmail1" >Bank Name</label>

							<select class="form-control select2" name="BankID" id="BankID">
                      <option value="">Select Bank Name</option>
														
				<?php 
				if($EmpID==''){
				
				while($RowAdminBank=$StmtAdminBank->fetch_array()
){?>	
				 
					<option value="<?php echo $RowAdminBank['BankID'] ;?>" >
					<?php  echo $RowAdminBank['BankName']; ?></option>
				<?php    } } else{
while($RowAdminBank=$StmtAdminBank->fetch_array()){ ?>

		<option 
	<?php if($RowEmpBankDetails['BankID']==$RowAdminBank['BankID']){ ?>	selected="selected" <?php }?>
		
		value="<?php echo $RowAdminBank['BankID'] ;?>" >
					<?php  echo $RowAdminBank['BankName']; ?></option>
				<?php    }} ?>			

				
				
                  </select>
           </div>
				
				  
				  <div class="form-group  col-lg-3">
				                    <label for="exampleInputEmail1" >Account Number</label>

                    <input class="form-control" type="text"  maxlength="18"
					id="AccNumber" name="AccNumber"
					placeholder="Enter Account Number"   value="<?php echo $RowEmpBankDetails['AccNumber']  ?>"  required>                 
                  </div>
				
				<div class="form-group  col-lg-3">
				                  <label for="exampleInputEmail1">IFSC</label>

                    <input class="form-control" type="text" maxlength="11" onkeyup="this.value = this.value.toUpperCase();"
					id="IFSC" name="IFSC"
					placeholder="Enter IFSC"  value="<?php echo $RowEmpBankDetails['IFSC'] ?>" >                 
                  </div>
				
                
			
				
				
				

				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Account Type</label>
                    <input class="form-control" type="text"
					placeholder="Enter Account Type" value="<?php echo $RowEmpBankDetails['AccType'] ?>" 
					id="AccType" name="AccType" required>                 
                  </div>
				  
				  
				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Date Of Creation</label>
                    <input class="form-control" type="date"
					placeholder="" value="<?php echo $RowEmpBankDetails['DateOfCreation'] ?>" 
					id="DateOfCreation" name="DateOfCreation" required>                 
                  </div>
				
				
				  
				
			  
        <!-- end row -->
									</div>
								
								
									</div>
									</div>
									
									<h6 align="LEFT"><B>EMPLOYEE SALARY DETAILS</B></h6>

                                <div class="card">
								
								<!--<form role="form" method="post">-->
                                    <div class="card-body">
									
										<div class="row">
				
				  
				  <div class="form-group  col-lg-3">
				                    <label for="exampleInputEmail1" >Basic Salary</label>

                    <input class="form-control" type="text" 
					id="Basic" name="Basic"
					placeholder="Enter Basic Salary"   value="<?php echo $RowEmpSalaryDetails['Basic']  ?>"  required>                 
                  </div>
				
				<div class="form-group  col-lg-3">
				                  <label for="exampleInputEmail1">HRA</label>

                    <input class="form-control" type="text"
					id="HRA" name="HRA"
					placeholder="Enter HRA"  value="<?php echo $RowEmpSalaryDetails['HRA'] ?>" >                 
                  </div>
				
                
			
				
				
				

				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Daily Allowance</label>
                    <input class="form-control" type="text"
					placeholder="Enter Daily Allowance" value="<?php echo $RowEmpSalaryDetails['DailyAllowance'] ?>" 
					id="DailyAllowance" name="DailyAllowance" required>                 
                  </div>
				
				
				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Special Allowance</label>
                    <input class="form-control" type="text"
					placeholder="Enter Special Allowance" value="<?php echo $RowEmpSalaryDetails['SpecialAllowance'] ?>" 
					id="SpecialAllowance" name="SpecialAllowance" required>                 
                  </div>
				
				
				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Medical Allowance</label>
                    <input class="form-control" type="text"
					placeholder="Enter Medical Allowance" value="<?php echo $RowEmpSalaryDetails['MedicalAllowance'] ?>" 
					id="MedicalAllowance" name="MedicalAllowance" required>                 
                  </div>
				  
				<div class="form-group  col-lg-3">
								<label for="exampleInputEmail1">Conveyance</label>

                    <input class="form-control" type="text"
					placeholder="Enter Conveyance" value="<?php echo $RowEmpSalaryDetails['Conveyence'] ?>" 
					id="Conveyence" name="Conveyence"  required>                 
                  </div>
				<div class="form-group  col-lg-3">
	<label for="exampleInputEmail1">Incentives</label>

                    <input class="form-control" type="text"
					placeholder="Enter Incentives" value="<?php echo $RowEmpSalaryDetails['Incentives'] ?>" 
					id="Incentives" name="Incentives"  >                 
                  </div>
				  <div class="form-group  col-lg-3">
				  <label for="exampleInputEmail1">Others</label>

                    <input class="form-control" type="text"
					placeholder="Others" value="<?php echo $RowEmpSalaryDetails['Others'] ?>" 
					id="Others" name="Others"  >                 
                  </div>
				  
				
			  
        <!-- end row -->
									</div>
									</div>
									</div>
												<h6 align="LEFT"><B>EMPLOYEE DEDUCTION DETAILS</B></h6>
						
									 <div class="card">
								
							<!--	<form role="form" method="post">-->
                                    <div class="card-body">
									
										<div class="row">

				  <div class="form-group  col-lg-3">
				                    <label for="exampleInputEmail1" >ESI</label>

                    <input class="form-control" type="text" 
					id="ESI" name="ESI"
					placeholder="Enter  ESI"   value="<?php echo $RowEmpDeductionDetails['ESI']  ?>"  required>                 
                  </div>
				
				<div class="form-group  col-lg-3">
				                  <label for="exampleInputEmail1">PF Amount/Month</label>

                    <input class="form-control" type="text"
					id="PFPerc" name="PFPerc"
					placeholder="Enter PF Percentage"  value="<?php echo $RowEmpDeductionDetails['PFPerc'] ?>" >                 
                  </div>
				
                

				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Mess Charge Per Day</label>
                    <input class="form-control" type="text"
					placeholder="Enter Mess Charge Per Day" value="<?php echo $RowEmpDeductionDetails['MessChargePerDay'] ?>" 
					id="MessChargePerDay" name="MessChargePerDay" required>                 
                  </div>
				
				
				  		
				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Daily Deduction</label>
                    <input class="form-control" type="text"
					placeholder="Enter Daily Deduction" value="<?php echo $RowEmpDeductionDetails['DailyDeduction'] ?>" 
					id="DailyDeduction" name="DailyDeduction" required>                 
                  </div>
				
				
				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Monthly Deduction</label>
                    <input class="form-control" type="text"
					placeholder="Enter Monthly Deduction" value="<?php echo $RowEmpDeductionDetails['MonthlyDeduction'] ?>" 
					id="MonthlyDeduction" name="MonthlyDeduction" required>                 
                  </div>
				
				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">ESI Number</label>
                    <input class="form-control" type="text"
					placeholder="Enter ESI Number" value="<?php echo $RowEmpDeductionDetails['EsiNumber'] ?>" 
					id="EsiNumber" name="EsiNumber" required>                 
                  </div>
				  <div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">PF Number</label>
                    <input class="form-control" type="text"
					placeholder="Enter PF Number" value="<?php echo $RowEmpDeductionDetails['PFNumber'] ?>" 
					id="PFNumber" name="PFNumber" required>                 
                  </div>
				  
				   <!-- end row -->
									</div>
									<div class="col-xs-12 box-footer"  align="right">
				
				<button  type="submit"  name='submit'  class="btn btn-primary waves-effect waves-light">
				
				Submit</button>
				
              </div>
			  
									</div>
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
		<?php include ("Includes/NewScripts.php");?>
      
  
       
    </body>
</html>				

<script>
$("#AccNumber").keyup(function() {
    $("#AccNumber").val(this.value.match(/[0-9]*/));
});

$("#IFSC").keyup(function() {
    $("#IFSC").val(this.value.match(/[0-9,A-Z]*/));
});
</script>


