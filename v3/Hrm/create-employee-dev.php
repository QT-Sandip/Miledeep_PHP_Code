<?php 
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

$PageTitle = "Create Employee";
$PageTitleFolder = "Employee";

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT.'config/api-call.php';

$api = API_HRM_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resSalaryStructure = $resDropdownsAPI->Data->SalaryStructure;
$resDepartment  = $resDropdownsAPI->Data->Department;
$resSubDepartments  = $resDropdownsAPI->Data->SubDepartments;
$resDesignations  = $resDropdownsAPI->Data->Designations;
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
$resGender  = $resDropdownsAPI->Data->Gender;

// echo json_encode($resSalaryStructure, JSON_PRETTY_PRINT);
// exit;
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

                    <?php include($IncludesPath . "page-title.php"); ?>
                        <?php //include_once ROOT . '/includes/page-header.php' ?>   

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
                                                        <?php foreach($resBranches as $Branches) {?>
													    <option value="<?php echo $Branches->BranchID?>"><?php echo $Branches->BranchName?> </option>
                                                        <?php } ?>
													<select>   
												</div>
                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpDepartment">Department<span style="color:red">*</span></label>              			   
													<select  class="form-control drpSelect" name="drpDepartment" id="drpDepartment">
													    <option value=""> Select </option>
                                                        <?php foreach($resDepartment as $Department) {?>
													    <option value="<?php echo $Department->DeptID?>"><?php echo $Department->DepartmentName?> </option>
                                                        <?php } ?>
													<select>   
												</div>
                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpSubDepartment">Sub Department<span style="color:red">*</span></label>              			   
													<select  class="form-control drpSelect" name="drpSubDepartment" id="drpSubDepartment">
													    <option value=""> Select </option>
                                                        <?php foreach($resSubDepartments as $SubDepartments) {?>
													    <option value="<?php echo $SubDepartments->SubDeptID?>"><?php echo $SubDepartments->SubDeptName?> </option>
                                                        <?php } ?>
													<select>   
												</div>
												
												<div class="form-group mb-3 col-sm-3" >
													<label for="txtBiometricId">Biometric ID <span style="color:red">*</span></label>
													<input  type="text"  class="form-control" name='txtBiometricId' id="txtBiometricId" 
														placeholder="Enter Biometric ID"  />		
												</div>

												 

                                                <div class="form-group mb-3 col-sm-3" >
													<label for="txtEmployeeName">Employee Name <span style="color:red">*</span></label>
													<input  type="text"  class="form-control" name='txtEmployeeName' id="txtEmployeeName" 
														placeholder="Enter Employee Name"  />		
												</div>
                                                

                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpContractorName">Contractor Name<span style="color:red">*</span></label>              			   
													<select  class="form-control drpSelect" name="drpContractorName" id="drpContractorName">
													    <option value=""> Select </option>
                                                        <?php foreach($resContractors as $Contractors) {?>
													    <option value="<?php echo $Contractors->ContractorID?>"><?php echo $Contractors->ContractorName?> </option>
                                                        <?php } ?>
													<select>   
												</div>                                                 

                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpGender">Gender<span style="color:red">*</span></label>              			   
													<select  class="form-control drpSelect " name="drpGender" id="drpGender">
													    <option value=""> Select </option>
													    <?php foreach($resGender as $Gender) {?>
													    <option value="<?php //echo $Category->ID?>"><?php echo $Gender?> </option>
                                                        <?php } ?>
                                                    <select>   
												</div> 
                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpCategory">Category<span style="color:red">*</span></label>              			   
													<select  class="form-control drpSelect " name="drpCategory" id="drpCategory">
													    <option value=""> Select </option>
                                                        <?php foreach($resCategory as $Category) {?>
													    <option value="<?php //echo $Category->ID?>"><?php echo $Category?> </option>
                                                        <?php } ?>
													<select>   
												</div> 
                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpSubCategory">Sub Category<span style="color:red">*</span></label>              			   
													<select  class="form-control drpSelect " name="drpSubCategory" id="drpSubCategory">
													    <option value=""> Select </option>
                                                        <?php foreach($resSubCategory as $SubCategory) {?>
													    <option value="<?php // echo $Designations->DesigID?>"><?php echo $SubCategory?> </option>
                                                        <?php } ?>
													<select>   
												</div> 
                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpDesignation">Designation<span style="color:red">*</span></label>              			   
													<select  class="form-control drpSelect " name="drpDesignation" id="drpDesignation">
													    <option value=""> Select </option>
                                                        <?php foreach($resDesignations as $Designations) {?>
													    <option value="<?php echo $Designations->DesigID?>"><?php echo $Designations->Designation?> </option>
                                                        <?php } ?>
													<select>   
												</div> 
                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpShiftCategory">Shift Category<span style="color:red">*</span></label> 
													<select  class="form-control drpSelect " name="drpShiftCategory" id="drpShiftCategory">
													    <option value=""> Select </option>
													    <?php // foreach($resShift as $Shift) { ?>
													    <option value="<?php echo $resShift->ShiftID?>"><?php echo $resShift->ShiftName?> </option>
                                                        <?php // } ?>
													<select>   
												</div> 
                                                <div class="form-group mb-3 col-sm-3" >
													<label for="txtJoiningDate">Joining Date <span style="color:red">*</span></label>
													<input  type="date"  class="form-control" name='txtJoiningDate' id="txtJoiningDate" 
														placeholder="Enter Joining Date"  />		
												</div>

                                            </div>

                                                                                

                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="form-group mb-3 col-lg-3">
                                                <label for="drpBloodGroup">Blood Group<span style="color:red">*</span></label>              			   
                                                <select  class="form-control drpSelect" name="drpBloodGroup" id="drpBloodGroup">
                                                    <option value=""> Select </option>
                                                    <?php foreach($resBloodGroup as $BloodGroup) {?>
													    <option value="<?php //echo $Designations->DesigID?>"><?php echo $BloodGroup?> </option>
                                                        <?php } ?>
                                                <select>   
                                            </div>
                                            

                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtFatherName">Father Name <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtFatherName' id="txtFatherName" 
                                                    placeholder="Enter Father Name"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtSpouseName">Spouse Name <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtSpouseName' id="txtSpouseName" 
                                                    placeholder="Enter Spouse Name"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtGrade">Grade <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtGrade' id="txtGrade" 
                                                    placeholder="Enter Grade"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtDivision">Division <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtDivision' id="txtDivision" 
                                                    placeholder="Enter Division"  />		
                                            </div>
                                            

                                            <div class="form-group mb-3 col-lg-3">
                                                <label for="drpSalaryStructure">Salary Structure<span style="color:red">*</span></label>              			   
                                                <select  class="form-control drpSelect" name="drpSalaryStructure" id="drpSalaryStructure">
                                                    <option value=""> Select </option>
                                                <select>   
                                            </div>
                                            <div class="form-group mb-3 col-lg-3">
                                                <label for="drpSalaryCalculate">Salary Calculate From<span style="color:red">*</span></label>              			   
                                                <select  class="form-control drpSelect" name="drpSalaryCalculate" id="drpSalaryCalculate">
                                                    <option value=""> Select </option>
                                                <select>   
                                            </div>

                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtDateOfLeaving">Date of Leaving <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtDateOfLeaving' id="txtDateOfLeaving" 
                                                    placeholder="Enter Date of Leaving"  />		
                                            </div>

                                            

                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtReasonForLeaving">Reason for leaving <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtReasonForLeaving' id="txtReasonForLeaving" 
                                                    placeholder="Enter Reason for leaving"  />		
                                            </div>

                                            <div class="form-group mb-3 col-lg-3">
                                                <label for="drpESIApplicable">ESI Applicable<span style="color:red">*</span></label>              			   
                                                <select  class="form-control drpSelect" name="drpESIApplicable" id="drpESIApplicable">
                                                    <option value=""> Select </option>
                                                    <?php foreach($resESIApplicable as $ESIApplicable) {?>
													    <option value="<?php // echo $Designations->DesigID?>"><?php echo $ESIApplicable?> </option>
                                                        <?php } ?>
                                                <select>   
                                            </div>
                                            

                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtESIDispensary">ESI Dispensary <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtESIDispensary' id="txtESIDispensary" 
                                                    placeholder="Enter ESI Dispensary"  />		
                                            </div>

                                            
                                            <div class="form-group mb-3 col-lg-3">
                                                <label for="drpPFApplicable">PF Applicable<span style="color:red">*</span></label>              			   
                                                <select  class="form-control drpSelect" name="drpPFApplicable" id="drpPFApplicable">
                                                    <option value=""> Select </option>
                                                    <?php foreach($resPFApplicable as $PFApplicable) {?>
													    <option value="<?php // echo $Designations->DesigID?>"><?php echo $PFApplicable?> </option>
                                                        <?php } ?>
                                                <select>   
                                            </div>

                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtPFNoForDeptFile">PF No for Dept File <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtPFNoForDeptFile' id="txtPFNoForDeptFile" 
                                                    placeholder="Enter PF No for Dept File"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtRestrictPF">Restrict PF <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtRestrictPF' id="txtRestrictPF" 
                                                    placeholder="Enter Restrict PF"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtZeroPension">Zero Pension <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtZeroPension' id="txtZeroPension" 
                                                    placeholder="Enter Zero Pension"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtZeroPT">Zero PT <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtZeroPT' id="txtZeroPT" 
                                                    placeholder="Enter Zero PT"  />		
                                            </div>

                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtUANNo">UAN NO <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtUANNo' id="txtUANNo" 
                                                    placeholder="Enter UAN NO"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtReJoinee">Re-Joinee <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtReJoinee' id="txtReJoinee" 
                                                    placeholder="Enter Re-Joinee"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtPrevEmployeIDRefno">Prev Employe ID Refno <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtPrevEmployeIDRefno' id="txtPrevEmployeIDRefno" 
                                                    placeholder="Enter Prev Employe ID Refno"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtWashingAllowance">Washing Allowance <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtWashingAllowance' id="txtWashingAllowance" 
                                                    placeholder="Enter Washing Allowance"  />		
                                            </div>

                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtEmployeCategory">Employe Category <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtEmployeCategory' id="txtEmployeCategory" 
                                                    placeholder="Enter Employe Category"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtPFDateOfJoining">PF Date of Joining <span style="color:red">*</span></label>
                                                <input  type="date"  class="form-control" name='txtPFDateOfJoining' id="txtPFDateOfJoining" 
                                                    placeholder="Enter PF Date of Joining"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtESICRegistrationDate">ESIC Registration Date <span style="color:red">*</span></label>
                                                <input  type="date"  class="form-control" name='txtESICRegistrationDate' id="txtESICRegistrationDate" 
                                                    placeholder="Enter ESIC Registration Date"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtCTC">CTC <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtCTC' id="txtCTC" 
                                                    placeholder="Enter CTC"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtGrossSalary">Gross Salary <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtGrossSalary' id="txtGrossSalary" 
                                                    placeholder="Enter Gross Salary"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtTDSApplicable">TDS Applicable <span style="color:red">*</span></label>
                                                <select  class="form-control drpSelect" name="txtTDSApplicable" id="txtTDSApplicable">
                                                    <option value=""> Select </option>
                                                    <?php foreach($resESIApplicable as $ESIApplicable) {?>
													    <option value="<?php // echo $Designations->DesigID?>"><?php echo $ESIApplicable?> </option>
                                                        <?php } ?>
                                                <select>  	
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="form-group mb-3 col-lg-3">
                                                <label for="drpMaritalStatus">Marital Status<span style="color:red">*</span></label>              			   
                                                <select  class="form-control drpSelect " name="drpMaritalStatus" id="drpMaritalStatus">
                                                    <option value=""> Select </option>
                                                    <?php foreach($resMaritalStatus as $MaritalStatus) {?>
													    <option value="<?php // echo $Designations->DesigID?>"><?php echo $MaritalStatus?> </option>
                                                        <?php } ?>
                                                <select>   
                                            </div> 
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtPhoneNumber">Phone Number <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtPhoneNumber' id="txtPhoneNumber" 
                                                    placeholder="Enter Element Value"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtAlternatePhoneNumber">Alternate Phone Number <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtAlternatePhoneNumber' id="txtAlternatePhoneNumber" 
                                                    placeholder="Enter Element Value"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtPresentAddress">Present Address <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtPresentAddress' id="txtPresentAddress" 
                                                    placeholder="Enter Element Value"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtPermanentAddress">Permanent Address <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtPermanentAddress' id="txtPermanentAddress" 
                                                    placeholder="Enter Element Value"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtDOB">DOB <span style="color:red">*</span></label>
                                                <input  type="date"  class="form-control" name='txtDOB' id="txtDOB" 
                                                    placeholder="Enter Element Value"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtAadharNumber">Aadhar Number <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtAadharNumber' id="txtAadharNumber" 
                                                    placeholder="Enter Element Value"  />		
                                            </div>
                                            <div class="form-group mb-3 col-sm-3" >
                                                <label for="txtPANNumber">PAN Number <span style="color:red">*</span></label>
                                                <input  type="text"  class="form-control" name='txtPANNumber' id="txtPANNumber" 
                                                    placeholder="Enter Element Value"  />		
                                            </div>

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
                                                <?php //var_dump($resSalaryStructure)?>            			   
                                                <select  class="form-control drpSelect" name="drpSalaryStructure" id="drpSalaryStructure">
                                                    <option value=""> Select </option>
                                                    <?php foreach($resSalaryStructure as $SalaryStructure) {?>
                                                        <option value="<?php echo $SalaryStructure->ID?>"> <?php echo $SalaryStructure->RoleName?> </option>
                                                        <?php  } ?>                                                     
                                                <select>   
                                            </div>                                            

                                        </div>
                                        
                                        <div class="row" id="divSlalaryMaster"></div>

                                        <div class="col-xs-12 mt-3 box-footer text-center">
                                            <a target="" href="#" class="btn btn-danger"> Close</a>
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

            $(document).on("change", "#drpSalaryStructure", function() {
                
                $.ajax({
                    url: 'Ajax/AjaxCreateEmployee.php',
                    type: 'POST',
                    data: {
                        methodType: "getsal",
                        salaryId: $(this).val(),
                    },
                    success: function (data) {
                        $("#divSlalaryMaster").html("").html(data);
                        // fnShowDataTable();

                    }
                });
            })

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
                    drpShiftCategory: { required: true },
                    txtJoiningDate: { required: true, noSpace: true },

                    drpBloodGroup: { required: true },
                    // txtFatherName: { required: true, noSpace: true },
                    // txtSpouseName: { required: true, noSpace: true },
                    // txtGrade: { required: true, noSpace: true },
                    // txtDivision: { required: true, noSpace: true },
                    drpSalaryStructure: { required: true },
                    drpSalaryCalculate: { required: true },
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
                    // txtPrevEmployeIDRefno: { required: true, noSpace: true },
                    // txtWashingAllowance: { required: true, noSpace: true },
                    // txtEmployeCategory: { required: true, noSpace: true },
                    // txtPFDateOfJoining: { required: true, noSpace: true },
                    // txtESICRegistrationDate: { required: true, noSpace: true },
                    txtCTC: { required: true, noSpace: true },
                    txtGrossSalary: { required: true, noSpace: true },
                    // txtTDSApplicable: { required: true, noSpace: true },

                    drpMaritalStatus: { required: true },
                    txtPhoneNumber: { required: true, noSpace: true },
                    txtAlternatePhoneNumber: { required: true, noSpace: true },
                    txtPresentAddress: { required: true, noSpace: true },
                    txtPermanentAddress: { required: true, noSpace: true },
                    txtDOB: { required: true, noSpace: true },
                    txtAadharNumber: { required: true, noSpace: true },
                    txtPANNumber: { required: true, noSpace: true },
                    
                    drpSalaryStructure: { required: true },

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

                    var jsonData = {
                        SalaryElementsData: salaryStructureData
                    };

                    console.log(jsonData);

                    var  frmData = {
                        methodType: "create",
                        drpBranch: $("#drpBranch").val(),
                        drpDepartment: $("#drpDepartment").val(),
                        drpSubDepartment: $("#drpSubDepartment").val(),
                        txtBiometricId: $("#txtBiometricId").val(),
                        txtEmployeeName: $("#txtEmployeeName").val(),
                        drpContractorName: $("#drpContractorName").val(),
                        drpGender: $("#drpGender").val(),
                        drpCategory: $("#drpCategory").val(),
                        drpSubCategory: $("#drpSubCategory").val(),
                        drpDesignation: $("#drpDesignation").val(),
                        drpShiftCategory: $("#drpShiftCategory").val(),
                        txtJoiningDate: $("#txtJoiningDate").val(),

                        drpBloodGroup: $("#drpBloodGroup").val(),
                        txtFatherName: $("#txtFatherName").val(),
                        txtSpouseName: $("#txtSpouseName").val(),
                        txtGrade: $("#txtGrade").val(),
                        txtDivision: $("#txtDivision").val(),
                        drpSalaryStructure: $("#drpSalaryStructure").val(),
                        drpSalaryCalculate: $("#drpSalaryCalculate").val(),
                        txtDateOfLeaving: $("#txtDateOfLeaving").val(),
                        txtReasonForLeaving: $("#txtReasonForLeaving").val(),
                        drpESIApplicable: $("#drpESIApplicable").val(),
                        txtESIDispensary: $("#txtESIDispensary").val(),
                        drpPFApplicable: $("#drpPFApplicable").val(),
                        txtPFNoForDeptFile: $("#txtPFNoForDeptFile").val(),
                        txtRestrictPF: $("#txtRestrictPF").val(),
                        txtZeroPension: $("#txtZeroPension").val(),
                        txtZeroPT: $("#txtZeroPT").val(),
                        txtUANNo: $("#txtUANNo").val(),
                        txtReJoinee: $("#txtReJoinee").val(),
                        txtPrevEmployeIDRefno: $("#txtPrevEmployeIDRefno").val(),
                        txtWashingAllowance: $("#txtWashingAllowance").val(),
                        txtEmployeCategory: $("#txtEmployeCategory").val(),
                        txtPFDateOfJoining: $("#txtPFDateOfJoining").val(),
                        txtESICRegistrationDate: $("#txtESICRegistrationDate").val(),
                        txtCTC: $("#txtCTC").val(),
                        txtGrossSalary: $("#txtGrossSalary").val(),
                        txtTDSApplicable: $("#txtTDSApplicable").val(),

                        drpMaritalStatus: $("#drpMaritalStatus").val(),
                        txtPhoneNumber: $("#txtPhoneNumber").val(),
                        txtAlternatePhoneNumber: $("#txtAlternatePhoneNumber").val(),
                        txtPresentAddress: $("#txtPresentAddress").val(),
                        txtPermanentAddress: $("#txtPermanentAddress").val(),
                        txtDOB: $("#txtDOB").val(),
                        txtAadharNumber: $("#txtAadharNumber").val(),
                        txtPANNumber: $("#txtPANNumber").val(),
                        SalaryStructureObj:jsonData
                    }

                    console.log(frmData);

                    $.ajax({
                        url: 'Ajax/AjaxSalaryElements.php',
                        type: 'POST',
                        data: frmData,
                        success: function (data) {
                            // $("#divDataList").html(data);
                            // fnShowDataTable();
                            return false;
                        }
                    });

                    return false;
                }

            });


        </script>

    </body>
</html>
