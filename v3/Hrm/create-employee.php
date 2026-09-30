<?php 
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

// API_HRM_EMPLOYEE_CREATION

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT.'config/api-call.php';

$apiGetEmployee = API_HRM_EMPLOYEE_CREATION;

$bodyContent = array(        
    "EmployeeID" => trim($_GET["EmpID"])
);

$resGetEmployee = CallAPI($apiGetEmployee, "GET", "", $bodyContent);

$resGetEmp = $resGetEmployee->Data->EmployeeData;
$resSalaryStructureObj = $resGetEmployee->Data->SalaryStructureObj;

 //echo json_encode($resGetEmployee, JSON_PRETTY_PRINT);
//exit;

$api = API_HRM_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resDepartment  = $resDropdownsAPI->Data->Department;
$resSubDepartments  = $resDropdownsAPI->Data->SubDepartments;
$resDesignations  = $resDropdownsAPI->Data->Designations;
$resSalaryStructure = $resDropdownsAPI->Data->SalaryStructure;
$resBranches  = $resDropdownsAPI->Data->Branches;
$resContractors  = $resDropdownsAPI->Data->Contractors;
$resCategory  = $resDropdownsAPI->Data->Category;
$resSubCategory  = $resDropdownsAPI->Data->SubCategory;
$resShift  = $resDropdownsAPI->Data->Shift;
$resMaritalStatus  = $resDropdownsAPI->Data->MaritalStatus;
$resESIApplicable  = $resDropdownsAPI->Data->ESIApplicable;
$resPFApplicable  = $resDropdownsAPI->Data->PFApplicable;
$resTDSApplicable  = $resDropdownsAPI->Data->TDSApplicable;
$resBloodGroup  = $resDropdownsAPI->Data->BloodGroup;
$resBankDetails  = $resDropdownsAPI->Data->BankDetails;

// echo json_encode($resDropdownsAPI, JSON_PRETTY_PRINT);
// exit;

function fnIsExits($val){
    if(isset($val) && !empty($val)){
        return $val;
    }else {
        return "";
    }
}



?>
<!doctype html>
<html lang="en">

    <head>
        
        <?php include_once ROOT . '/includes/head.php' ?>

    </head>

    <?php  include_once ROOT . '/includes/body-preloader.php' ?>  

        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php include_once ROOT . '/includes/menu.php' ?>                   

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <?php include_once ROOT . '/includes/page-header.php' ?>   

                        <?php // include_once ROOT . 'Sales/includes/page-filter.php' ?>
                        <?php // include_once WORKING_MODULE . '/includes/page-filter.php' ?>

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">

                            <form role="form" method="post" id="frmCreateEmployee">
                                <div class="card">
                                    <div class="card-body">
                                        
                                            <div class="row">

                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpBranch">Branch<span style="color:red">*</span></label>              			   
													<select  class="form-control drpSelect" name="drpBranch" id="drpBranch">
													    <option value=""> Select </option>
                                                        <?php 
                                                            $bID = fnIsExits($resGetEmp->BranchID);
                                                            foreach($resBranches as $Branches) { 
                                                        ?>
													        <option value="<?php echo $Branches->BranchID?>"
                                                                <?php  if($bID == $Branches->BranchID) {
                                                                        echo ' selected';
                                                                     } ?> ><?php echo $Branches->BranchName?> </option>
                                                        <?php } ?>
                                                    </select>   
												</div>
                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpDepartment">Department<span style="color:red">*</span></label>              			   
													<select  class="form-control drpSelect" name="drpDepartment" id="drpDepartment">
													    <option value=""> Select </option>
                                                        <?php
                                                        $dID = fnIsExits($resGetEmp->DepartmentID);
                                                         foreach($resDepartment as $Department) {?>
													    <option value="<?php echo $Department->DeptID?>"
                                                                <?php  if($dID == $Department->DeptID) {
                                                                            echo ' selected';
                                                                        } ?>
                                                            ><?php echo $Department->DepartmentName?> </option>
                                                        <?php } ?>
                                                    </select>   
												</div>
                                                <!-- <div class="form-group mb-3 col-lg-3">
													<label for="drpSubDepartment">Sub Department<span style="color:red">*</span></label>              			   
													<select  class="form-control drpSelect" name="drpSubDepartment" id="drpSubDepartment">
													    <option value=""> Select </option>
                                                    </select>   
												</div> -->
												
												<div class="form-group mb-3 col-sm-3" >
													<label for="txtBiometricId">Biometric ID <span style="color:red">*</span></label>
													<input  type="number"  class="form-control" name='txtBiometricId' id="txtBiometricId" 
														placeholder="Enter Biometric ID" value="<?php echo fnIsExits($resGetEmp->BioMetricID) ?>" />		
												</div>												 

                                                <div class="form-group mb-3 col-sm-3" >
													<label for="txtEmployeeName">Employee Name <span style="color:red">*</span></label>
													<input  type="text"  class="form-control" name='txtEmployeeName' id="txtEmployeeName" 
														placeholder="Enter Employee Name"  value="<?php echo fnIsExits($resGetEmp->EmployeeName) ?>" />		
												</div>                                                                                                

                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpGender">Gender<span style="color:red">*</span></label>  
                                                    <?php $gender = fnIsExits($resGetEmp->EmpGender); ?>
													<select  class="form-control drpSelect " name="drpGender" id="drpGender">
													    <option value=""> Select </option>
                                                        <option value="Male" <?php echo ($gender == "Male") ? 'selected' : "" ?>> Male </option>
													    <option value="Female"  <?php echo ($gender == "Female") ? 'selected' : "" ?>> Female </option>
													    <option value="Transgender"  <?php echo ($gender == "Transgender") ? 'selected' : "" ?>> Transgender </option>
                                                    </select>   
												</div> 
                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpCategory">Category<span style="color:red">*</span></label>              			   
													<select  class="form-control drpSelect " name="drpCategory" id="drpCategory">
													    <option value=""> Select </option>
                                                        <?php 
                                                            $catID = fnIsExits($resGetEmp->EmployeeCategory);
                                                            foreach($resCategory as $Category) {?>
                                                                <option value="<?php echo $Category?>"
                                                                <?php  if($catID == $Category) { echo ' selected'; } ?>
                                                                ><?php echo $Category?> </option>
                                                            <?php } ?>
                                                    </select>   
												</div> 
                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpSubCategory">Sub Category</label>              			   
													<select  class="form-control drpSelect " name="drpSubCategory" id="drpSubCategory">
                                                        <?php 
                                                            $empSubCat = fnIsExits($resGetEmp->EmpSubCategory);
                                                        foreach($resSubCategory as $SubCategory) { ?>
													    <option value="<?php echo $SubCategory ?>"
                                                            <?php  if($empSubCat == $SubCategory) { echo ' selected'; } ?>
                                                        ><?php echo $SubCategory ?> </option>
                                                        <?php } ?>
                                                    </select>   
												</div> 
                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpDesignation">Designation<span style="color:red">*</span></label>              			   
													<select  class="form-control drpSelect " name="drpDesignation" id="drpDesignation">
													    <option value=""> Select </option>
                                                        <?php 
                                                            $desigID = fnIsExits($resGetEmp->DesigID);
                                                        foreach($resDesignations as $Designations) {?>
													    <option value="<?php echo $Designations->DesigID?>" 
                                                        <?php  if($desigID == $Designations->DesigID) { echo ' selected'; } ?>
                                                        ><?php echo $Designations->Designation?> </option>
                                                        <?php } ?>
                                                    </select>   
												</div> 
                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpShift">Shift <span style="color:red">*</span></label>              			   
													<select  class="form-control drpSelect " name="drpShift" id="drpShift">
													    <option value=""> Select </option>
                                                        <?php 
                                                            $shifID = fnIsExits($resGetEmp->ShiftID);
                                                            foreach($resShift as $Shift) { ?>
													    <option value="<?php echo $Shift->ShiftID ?>"
                                                            <?php  if($shifID == $Shift->ShiftID) { echo ' selected'; } ?>
                                                        ><?php echo $Shift->ShiftName ?> </option>
                                                        <?php  } ?>
                                                    </select>   
												</div> 
                                                <div class="form-group mb-3 col-sm-3" >
													<label for="txtJoiningDate">Joining Date <span style="color:red">*</span></label>
													<input  type="date"  class="form-control" name='txtJoiningDate' id="txtJoiningDate" 
														placeholder="Enter Joining Date"  value="<?php echo fnIsExits($resGetEmp->JoiningDate) ?>" />		
												</div>

                                                <!-- <div class="form-group mb-3 col-sm-3" >
                                                    <label for="txtDateOfLeaving">Date of Leaving <span style="color:red">*</span></label>
                                                    <input  type="text"  class="form-control" name='txtDateOfLeaving' id="txtDateOfLeaving" 
                                                        placeholder="Enter Date of Leaving"  />		
                                                </div> -->

                                                <!-- <div class="form-group mb-3 col-sm-3" >
                                                    <label for="txtReasonForLeaving">Reason for leaving <span style="color:red">*</span></label>
                                                    <input  type="text"  class="form-control" name='txtReasonForLeaving' id="txtReasonForLeaving" 
                                                        placeholder="Enter Reason for leaving"  />		
                                                </div> -->

                                                <div class="form-group mb-3 col-sm-3" >
                                                    <label for="txtReJoinee">Re-Joinee </label>
                                                    <input  type="text"  class="form-control" name='txtReJoinee' id="txtReJoinee" 
                                                        placeholder="Enter Re-Joinee"  value="<?php echo fnIsExits($resGetEmp->Rejoinee) ?>" />		
                                                </div>
                                                <div class="form-group mb-3 col-sm-3" >
                                                    <label for="txtPrevEmployeeIDRefno">Prev Employee ID Refno </label>
                                                    <input  type="text"  class="form-control" name='txtPrevEmployeeIDRefno' id="txtPrevEmployeeIDRefno" 
                                                        placeholder="Enter Prev Employee ID Refno"  value="<?php echo fnIsExits($resGetEmp->PrevEmployeeIDReferenceNO) ?>" />		
                                                </div>

                                                <!-- <div class="form-group mb-3 col-sm-3" >
                                                    <label for="txtEmployeeCategory">Employee Category <span style="color:red">*</span></label>
                                                    <input  type="text"  class="form-control" name='txtEmployeeCategory' id="txtEmployeeCategory" 
                                                        placeholder="Enter Employee Category" value="<?php // echo fnIsExits($resGetEmp->EmployeeCategory) ?>" />		
                                                </div> -->

                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpContractorName">Contractor Name</label>              			   
													<select  class="form-control drpSelect" name="drpContractorName" id="drpContractorName">
													    <option value=""> Select </option>													    
                                                        <?php
                                                        $conID = fnIsExits($resGetEmp->ContractorID);
                                                        foreach($resContractors as $Contractors) {?>
													    <option value="<?php echo $Contractors->ContractorID?>"
                                                        <?php  if($conID == $Contractors->ContractorID) { echo ' selected'; } ?>    
                                                        ><?php echo $Contractors->ContractorName ?> </option>
                                                        <?php } ?>
                                                    </select>   
												</div> 

                                            </div>

                                                                                

                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">

                                            
                                            

                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtFatherName">Father Name </label>
                                                <input  type="text"  class="form-control" name='txtFatherName' id="txtFatherName" 
                                                    placeholder="Enter Father Name" value="<?php echo fnIsExits($resGetEmp->FatherName) ?>" />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtSpouseName">Spouse Name </label>
                                                <input  type="text"  class="form-control" name='txtSpouseName' id="txtSpouseName" 
                                                    placeholder="Enter Spouse Name"  value="<?php echo fnIsExits($resGetEmp->SpouseName) ?>" />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtGrade">Grade </label>
                                                <input  type="text"  class="form-control" name='txtGrade' id="txtGrade" 
                                                    placeholder="Enter Grade"  value="<?php echo fnIsExits($resGetEmp->Grade) ?>" />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtDivision">Division </label>
                                                <input  type="text"  class="form-control" name='txtDivision' id="txtDivision" 
                                                    placeholder="Enter Division"  value="<?php echo fnIsExits($resGetEmp->Division) ?>" />		
                                            </div>

                                            <div class="form-group mb-3 col-lg-3">
                                                <label for="drpMaritalStatus">Marital Status<span style="color:red">*</span></label>              			   
                                                <select  class="form-control drpSelect " name="drpMaritalStatus" id="drpMaritalStatus">
                                                    <option value=""> Select </option>
                                                    <?php $ms = fnIsExits($resGetEmp->MaritalStatus); ?>
                                                    <option value="Married" <?php echo ($ms == "Married") ? 'selected' : "" ?>> Married </option>
                                                    <option value="UnMarried" <?php echo ($ms == "UnMarried") ? 'selected' : "" ?>> UnMarried </option>
                                                </select>   
                                            </div> 

                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtPhoneNumber">Phone Number <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtPhoneNumber' id="txtPhoneNumber" 
                                                    placeholder="Enter Element Value"  value="<?php echo fnIsExits($resGetEmp->PhoneNumber) ?>" />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtAlternatePhoneNumber">Alternate Phone Number </label>
                                                <input  type="text"  class="form-control" name='txtAlternatePhoneNumber' id="txtAlternatePhoneNumber" 
                                                    placeholder="Enter Element Value"  value="<?php echo fnIsExits($resGetEmp->AlternatePhoneNumber) ?>" />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtPresentAddress">Present Address <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtPresentAddress' id="txtPresentAddress" 
                                                    placeholder="Enter Element Value"  value="<?php echo fnIsExits($resGetEmp->PresentAddress) ?>" />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtPermanentAddress">Permanent Address <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtPermanentAddress' id="txtPermanentAddress" 
                                                    placeholder="Enter Element Value"  value="<?php echo fnIsExits($resGetEmp->PermanentAddress) ?>" />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtDOB">DOB <span style="color:red">*</span></label>
                                                <input  type="date"  class="form-control" name='txtDOB' id="txtDOB" 
                                                    placeholder="Enter Element Value"  value="<?php echo fnIsExits($resGetEmp->DateOfBirth) ?>" />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtAadharNumber">Aadhar Number <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtAadharNumber' id="txtAadharNumber" 
                                                    placeholder="Enter Element Value"  value="<?php echo fnIsExits($resGetEmp->AadharNumber) ?>" />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtPANNumber">PAN Number </label>
                                                <input  type="text"  class="form-control" name='txtPANNumber' id="txtPANNumber" 
                                                    placeholder="Enter Element Value"  value="<?php echo fnIsExits($resGetEmp->PanNumber) ?>" />		
                                            </div>

                                            <div class="form-group mb-3 col-lg-3">
                                                <label for="drpBloodGroup">Blood Group<span style="color:red">*</span></label>              			   
                                                <select  class="form-control drpSelect" name="drpBloodGroup" id="drpBloodGroup">
                                                    <option value=""> Select </option>
                                                    <?php 
                                                        $bg = fnIsExits($resGetEmp->BloodGroup);
                                                    foreach($resBloodGroup as $BloodGroup) {?>
													    <option value="<?php echo $BloodGroup ?>"
                                                        <?php  if($bg == $BloodGroup) { echo ' selected'; } ?>
                                                        ><?php echo $BloodGroup?> </option>
                                                    <?php } ?>
                                                </select>   
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="form-group mb-3 col-lg-3">
                                                <label for="txtSalaryCalculateFrom">Salary Calculate From<span style="color:red">*</span></label>              			   
                                                <input  type="date"  class="form-control" name='txtSalaryCalculateFrom' id="txtSalaryCalculateFrom" 
                                                    value="<?php echo fnIsExits($resGetEmp->SalaryCalculateFrom) ?>"  />
                                                <!-- <select  class="form-control drpSelect" name="drpSalaryCalculate" id="drpSalaryCalculate">
                                                    <option value=""> Select </option>
                                                    </select>    -->
                                            </div>

                                            
                                            
                                            <div class="form-group mb-3 col-lg-3">
                                                <label for="drpESIApplicable">ESI Applicable</label>              			   
                                                <select  class="form-control drpSelect" name="drpESIApplicable" id="drpESIApplicable">
                                                    <option value=""> Select </option>
                                                    <?php 
                                                        $esiApp = fnIsExits($resGetEmp->ESIApplicable);
                                                    foreach($resESIApplicable as $ESIApplicable) {?>
													    <option value="<?php  echo $ESIApplicable ?>"
                                                        <?php  if($esiApp == $ESIApplicable) { echo ' selected'; } ?>
                                                        ><?php echo $ESIApplicable?> </option>
                                                    <?php } ?>
                                                </select>   
                                            </div>
                                            

                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtESIDispensary">ESI Dispensary </label>
                                                <input  type="text"  class="form-control" name='txtESIDispensary' id="txtESIDispensary" 
                                                    placeholder="Enter ESI Dispensary"  value="<?php echo fnIsExits($resGetEmp->ESIDispensary) ?>" />		
                                            </div>

                                            
                                            <div class="form-group mb-3 col-lg-3">
                                                <label for="drpPFApplicable">PF Applicable</label>              			   
                                                <select  class="form-control drpSelect" name="drpPFApplicable" id="drpPFApplicable">
                                                    <option value=""> Select </option>
                                                    <?php 
                                                        $pfApp = fnIsExits($resGetEmp->PFApplicable);
                                                        foreach($resPFApplicable as $PFApplicable) {?>
													    <option value="<?php echo $PFApplicable ?>"
                                                        <?php  if($pfApp == $PFApplicable) { echo ' selected'; } ?>
                                                        ><?php echo $PFApplicable ?> </option>
                                                    <?php } ?>
                                                </select>   
                                            </div>

                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtPFNoForDeptFile">PF No for Dept File </label>
                                                <input  type="text"  class="form-control" name='txtPFNoForDeptFile' id="txtPFNoForDeptFile" 
                                                    placeholder="Enter PF No for Dept File"  value="<?php echo fnIsExits($resGetEmp->PFNoforDeptFile) ?>" />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtRestrictPF">Restrict PF </label>
                                                <input  type="text"  class="form-control" name='txtRestrictPF' id="txtRestrictPF" 
                                                    placeholder="Enter Restrict PF"  value="<?php echo fnIsExits($resGetEmp->RestrictPF) ?>" />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtZeroPension">Zero Pension </label>
                                                <input  type="text"  class="form-control" name='txtZeroPension' id="txtZeroPension" 
                                                    placeholder="Enter Zero Pension"  value="<?php echo fnIsExits($resGetEmp->ZeroPension) ?>" />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtZeroPT">Zero PT </label>
                                                <input  type="text"  class="form-control" name='txtZeroPT' id="txtZeroPT" 
                                                    placeholder="Enter Zero PT"  value="<?php echo fnIsExits($resGetEmp->ZeroPT) ?>" />		
                                            </div>

                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtUANNo">UAN NO </label>
                                                <input  type="text"  class="form-control" name='txtUANNo' id="txtUANNo" 
                                                    placeholder="Enter UAN NO"  value="<?php echo fnIsExits($resGetEmp->UANNumber) ?>" />		
                                            </div>
                                            
                                            
                                            <!-- <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtWashingAllowance">Washing Allowance </label>
                                                <input  type="text"  class="form-control" name='txtWashingAllowance' id="txtWashingAllowance" 
                                                    placeholder="Enter Washing Allowance"  />		
                                            </div> -->

                                            
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtPFDateOfJoining">PF Date of Joining </label>
                                                <input  type="date"  class="form-control" name='txtPFDateOfJoining' id="txtPFDateOfJoining" 
                                                    placeholder="Enter PF Date of Joining"  value="<?php echo fnIsExits($resGetEmp->PFDateofJoinining) ?>" />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtESICRegistrationDate">ESIC Registration Date </label>
                                                <input  type="date"  class="form-control" name='txtESICRegistrationDate' id="txtESICRegistrationDate" 
                                                    placeholder="Enter ESIC Registration Date"  value="<?php echo fnIsExits($resGetEmp->ESICRegistrationDate) ?>" />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtCTC">CTC <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtCTC' id="txtCTC" 
                                                    placeholder="Enter CTC"  value="<?php echo fnIsExits($resGetEmp->CTC) ?>" />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtGrossSalary">Gross Salary <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtGrossSalary' id="txtGrossSalary" 
                                                    placeholder="Enter Gross Salary"  value="<?php echo fnIsExits($resGetEmp->GrossSalary) ?>" />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtTDSApplicable">TDS Applicable </label>
                                                <select  class="form-control drpSelect" name="txtTDSApplicable" id="txtTDSApplicable">
                                                    <option value=""> Select </option>
                                                    <?php 
                                                    $tdsApp = fnIsExits($resGetEmp->TDSApplicable);
                                                    foreach($resESIApplicable as $ESIApplicable) {?>
													    <option value="<?php  echo $ESIApplicable ?>"
                                                        <?php  if($tdsApp == $ESIApplicable) { echo ' selected'; } ?>
                                                        ><?php echo $ESIApplicable?> </option>
                                                    <?php } ?>
                                                </select>  	
                                            </div>

                                            <!-- <div class="form-group mb-3 col-lg-3">
                                                <label for="drpSalaryStructure">Salary Structure<span style="color:red">*</span></label>              			   
                                                <select  class="form-control drpSelect" name="drpSalaryStructure" id="drpSalaryStructure">
                                                    <option value=""> Select </option>
                                                <select>   
                                            </div> -->
                                            

                                        </div>
                                    </div>
                                </div>

                                <!-- EMPLOYEE BANK DETAILS -- NO -- -->

                                <h3 style="font-size: 16px;">Salary Details</h3>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                                        
                                            <div class="form-group mb-3 col-lg-3">
                                                <label for="drpSalaryStructure">Salary Structure<span style="color:red">*</span></label>              			   
                                                <select  class="form-control drpSelect" name="drpSalaryStructure" id="drpSalaryStructure">
                                                    <option value=""> Select </option>
                                                    <?php 
                                                    
                                                        foreach($resSalaryStructure as $item) {                                                             
                                                            if(isset($resGetEmp->SalaryStructure) && !empty($resGetEmp->SalaryStructure)) {
                                                                if($resGetEmp->SalaryStructure == $item->ID) {
                                                                    $select = "selected='selected'";
                                                                } else {
                                                                    $select = "";
                                                                }
                                                            } else {  $select = ""; }
                                                            echo '<option value="'.$item->ID.'" '.$select.'>'.$item->RoleName.'</option>';
                                                        }                                                        
                                                    ?>                                                        
                                                </select>   
                                            </div>                                            

                                        </div>
                                        
                                        <div class="row" id="divSlalaryMaster"></div>

                                        <div class="col-xs-12 mt-3 box-footer text-center">
                                            <a target="" href="employee-list" class="btn btn-danger"> Close</a>
                                            <button type="submit" name='submit' class="btn btn-success"> Submit </button>
                                        </div>	 

                                    </div>
                                </div>      
                                
                                

                            </form>

                                <div id="divDataList">
                                    <!-- -->
                                </div>


                            </div>
                        </div>

                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                <?php include_once ROOT . '/includes/footer.php' ?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        <?php include_once ROOT . '/includes/common-js.php' ?>

        <!-- Required datatable js -->
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        
        <?php include_once ROOT . 'includes/app-js.php' ?>
        

        <script>

            <?php if(isset($_GET["EmpID"]) && !empty($_GET["EmpID"])) { ?>
                $(document).ready(function () {
                    $.ajax({
                        url: 'Ajax/AjaxCreateEmployee.php',
                        type: 'POST',
                        data: {
                            methodType: "getsal",
                            salaryId: $("#drpSalaryStructure").val(),
                        },
                        success: function (data) {
                            $("#divSlalaryMaster").html("").html(data);
                            
                            var salStruct = <?php echo json_encode($resSalaryStructureObj); ?>;
                            $.each(salStruct, function(index, item) {
                                $(".element"+item.ElementID).val(item.Value);
                            });

                        }
                    });
                });
            <?php } ?>

            $(document).on("change", "#drpSalaryStructure", function() {

                var selectedId = $(this).val();
                
                $.ajax({
                    url: 'Ajax/AjaxCreateEmployee.php',
                    type: 'POST',
                    data: {
                        methodType: "getsal",
                        salaryId: $("#drpSalaryStructure").val(),
                    },
                    success: function (data) {
                        $("#divSlalaryMaster").html("").html(data);
                        
                        var salStID = '<?php echo (isset($resGetEmp->SalaryStructure) && !empty($resGetEmp->SalaryStructure)) ? $resGetEmp->SalaryStructure : NULL ?>';                        
                        
                        if(salStID != selectedId) {
                            $(".dynField").val("");
                        }else {
                            var salStructList = <?php echo json_encode($resSalaryStructureObj); ?>;
                            $.each(salStructList, function(index, item) {
                                $(".element"+item.ElementID).val(item.Value);
                            });
                        }

                    }
                });
                
                
            });


            // function fnChangeDepartment(DepartID, subDepartID) {

            //     var SubDepartments = '<?php // echo json_encode($resSubDepartments); ?>';
            //     var subDepartList = JSON.parse(SubDepartments);

            //     var selectedList = [];

            //     $.each(subDepartList, function(index, obj) {                
            //         if (obj.DeptID == DepartID) {
            //             selectedList.push(obj);
            //         }
            //     });

            //     var list = '<option value="">Select</option>';
            //     $.each(selectedList, function(index, item) {                
            //         list += '<option value="'+item.SubDeptID+'">'+item.SubDeptName+'</option>';
            //     });

            //     $("#drpSubDepartment").html(list);

            //     if(subDepartID != "") {
            //         $("#drpSubDepartment").val(subDepartID).trigger('change');
            //     }

            // }

            // $(document).on('change', '#drpDepartment', function () {
            //     var dID = $("#drpDepartment").val();
            //     fnChangeDepartment(dID, "");
            // }); 

            // function fnShowDataTable() {
            //     var table = $('#tblDataTable').DataTable({
            //         scrollY: "600px",
            //         scrollX: true,
            //         sScrollXInner: "100%",
            //         paging: false,
            //         destroy: true,
            //         fixedColumns: true,
            //         scrollCollapse: true,
            //         bInfo: false

            //     });
            // }
            
            // $(document).ready(function () {
            //     $.ajax({
            //         url: 'Ajax/AjaxSalaryElements.php',
            //         type: 'POST',
            //         data: {
            //             methodType: "get",
            //         },
            //         success: function (data) {
            //             $("#divDataList").html(data);
            //             fnShowDataTable();
            //         }
            //     });
            // });

            // jQuery.validator.addMethod('selectcheck', function (value) {
			// 	return (value != '0' || value != '');
			// }, "year required");
			// jQuery.validator.addMethod("noSpace", function(value, element) { 
			// 	return value == '' || value.trim().length != 0;  
			// }, "No space please and don't leave it empty");

            
			
            $("#frmCreateEmployee").validate({
                onkeyup: false,
                onfocusout: false,
                invalidHandler: function (form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        var firstInvalidElement = $(validator.errorList[0].element);
                        //$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
                        firstInvalidElement.focus();
                    }
                },
                ignore: "",
                rules: {
                    drpBranch: { required: true },
                    drpDepartment: { required: true },
                    // drpSubDepartment: { required: true },
                    txtBiometricId: { required: true, noSpace: true },
                    txtEmployeeName: { required: true, noSpace: true },
                    // drpContractorName: { required: true },
                    drpGender: { required: true },
                    drpCategory: { required: true },
                    // drpSubCategory: { required: true },
                    drpDesignation: { required: true },
                    drpShift: { required: true },
                    txtJoiningDate: { required: true, noSpace: true },

                    drpBloodGroup: { required: true },
                    // txtFatherName: { required: true, noSpace: true },
                    // txtSpouseName: { required: true, noSpace: true },
                    // txtGrade: { required: true, noSpace: true },
                    // txtDivision: { required: true, noSpace: true },
                   // drpSalaryStructure: { required: true },
                    txtSalaryCalculateFrom: { required: true },
                    // txtDateOfLeaving: { required: true, noSpace: true },
                    // txtReasonForLeaving: { required: true, noSpace: true },
                    // drpESIApplicable: { required: true },
                    // txtESIDispensary: { required: true, noSpace: true },
                    // drpPFApplicable: { required: true },
                    // txtPFNoForDeptFile: { required: true, noSpace: true },
                    // txtRestrictPF: { required: true, noSpace: true },
                    // txtZeroPension: { required: true, noSpace: true },
                    // txtZeroPT: { required: true, noSpace: true },
                    // txtUANNo: { required: true, noSpace: true },
                    // txtReJoinee: { required: true, noSpace: true },
                    // txtPrevEmployeeIDRefno: { required: true, noSpace: true },
                    // txtWashingAllowance: { required: true, noSpace: true },
                    // txtEmployeeCategory: { required: true, noSpace: true },
                    // txtPFDateOfJoining: { required: true, noSpace: true },
                    // txtESICRegistrationDate: { required: true, noSpace: true },
                    txtCTC: { required: true, noSpace: true },
                    txtGrossSalary: { required: true, noSpace: true },
                    // txtTDSApplicable: { required: true, noSpace: true },

                    drpMaritalStatus: { required: true },
                    txtPhoneNumber: { required: true, noSpace: true },
                    // txtAlternatePhoneNumber: { required: true, noSpace: true },
                    txtPresentAddress: { required: true, noSpace: true },
                    txtPermanentAddress: { required: true, noSpace: true },
                    txtDOB: { required: true, noSpace: true },
                    txtAadharNumber: { required: true, noSpace: true },
                    // txtPANNumber: { required: true, noSpace: true },
                    
                    //drpSalaryStructure: { required: true },

                    // txtElementName: { required: true, noSpace: true },
                    // drpElementType: { required: true, selectcheck: true },
                },
                errorPlacement: function () {
                    return false;  // suppresses error message text
                },
                submitHandler: function () {

                    var salaryStructureData = [];

                    $('.dynField').each(function(index) {
                        var elementID = $(this).attr('data-elementid');
                        var value = $(this).val();

                        salaryStructureData.push({
                            Value: value,
                            ElementID: elementID
                        });
                    });


                    // console.log(salaryStructureData);

                    var  frmData = {
                        methodType: "create",
                        empId: '<?php echo isset($_GET["EmpID"]) && !empty($_GET["EmpID"]) ? $_GET["EmpID"] : "" ?>',
                        drpBranch: $("#drpBranch").val(),
                        drpDepartment: $("#drpDepartment").val(),
                        txtBiometricId: $("#txtBiometricId").val(),
                        txtEmployeeName: $("#txtEmployeeName").val(),
                        drpGender: $("#drpGender").val(),
                        drpCategory: $("#drpCategory").val(),
                        drpSubCategory: $("#drpSubCategory").val(),
                        drpDesignation: $("#drpDesignation").val(),
                        drpShift: $("#drpShift").val(),
                        txtJoiningDate: $("#txtJoiningDate").val(),
                        txtReJoinee: $("#txtReJoinee").val(),
                        txtPrevEmployeeIDRefno: $("#txtPrevEmployeeIDRefno").val(),
                        // txtEmployeeCategory: $("#txtEmployeeCategory").val(),
                        drpContractorName: $("#drpContractorName").val(),

                        txtFatherName: $("#txtFatherName").val(),
                        txtSpouseName: $("#txtSpouseName").val(),
                        txtGrade: $("#txtGrade").val(),
                        txtDivision: $("#txtDivision").val(),
                        drpMaritalStatus: $("#drpMaritalStatus").val(),
                        txtPhoneNumber: $("#txtPhoneNumber").val(),
                        txtAlternatePhoneNumber: $("#txtAlternatePhoneNumber").val(),
                        txtPresentAddress: $("#txtPresentAddress").val(),
                        txtPermanentAddress: $("#txtPermanentAddress").val(),
                        txtDOB: $("#txtDOB").val(),
                        txtAadharNumber: $("#txtAadharNumber").val(),
                        txtPANNumber: $("#txtPANNumber").val(),
                        drpBloodGroup: $("#drpBloodGroup").val(),

                        txtSalaryCalculateFrom: $("#txtSalaryCalculateFrom").val(),
                        drpESIApplicable: $("#drpESIApplicable").val(),
                        txtESIDispensary: $("#txtESIDispensary").val(),
                        drpPFApplicable: $("#drpPFApplicable").val(),
                        txtPFNoForDeptFile: $("#txtPFNoForDeptFile").val(),
                        txtRestrictPF: $("#txtRestrictPF").val(),
                        txtZeroPension: $("#txtZeroPension").val(),
                        txtZeroPT: $("#txtZeroPT").val(),
                        txtUANNo: $("#txtUANNo").val(),
                        txtPFDateOfJoining: $("#txtPFDateOfJoining").val(),
                        txtESICRegistrationDate: $("#txtPFDateOfJoining").val(),
                        txtCTC: $("#txtCTC").val(),
                        txtGrossSalary: $("#txtGrossSalary").val(),
                        txtTDSApplicable: $("#txtTDSApplicable").val(),
                        drpSalaryStructure: $("#drpSalaryStructure").val(),

                        SalaryStructureObj:salaryStructureData
                    }

                    // console.log(frmData);

                    $.ajax({
                        url: 'Ajax/AjaxCreateEmployee.php',
                        type: 'POST',
                        data:  frmData,
                        success: function (data) {
                            // $("#divDataList").html(data);
                            // fnShowDataTable();
                            console.log(data);
                            if(data.Error == null) {
                                alert(data.Msg);
                            }else{
                                alert(data.Error);
                            }
                            return false;
                        }
                    });

                    return false;
                }

            });


        </script>

    </body>
</html>
