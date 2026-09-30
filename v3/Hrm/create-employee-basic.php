<?php 
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

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


// echo json_encode($resDropdownsAPI, JSON_PRETTY_PRINT);
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

                        <?php include_once ROOT . '/includes/page-header.php' ?>   

                        <?php // include_once ROOT . 'Sales/includes/page-filter.php' ?>
                        <?php // include_once WORKING_MODULE . '/includes/page-filter.php' ?>

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">

                            <form role="form" method="post" id="frmCreateEmployeeBasic">
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
                                                        <option value="1"> Male </option>
													    <option value="2"> Female </option>
													    <option value="3"> Transgender </option>
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
                                            <div class="row">
                                                <div class="col-xs-12 mt-3 box-footer text-center">
                                                    <a target="" href="#" class="btn btn-danger"> Close</a>
                                                    <button type="submit" name='submit' class="btn btn-success"> Submit </button>
                                                </div>	 
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

            
			
            $("#frmCreateEmployeeBasic").validate({
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
                    txtJoiningDate: { required: true, noSpace: true }                  
                },
                errorPlacement: function () {
                    return false;  // suppresses error message text
                },
                submitHandler: function () {                    

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

                    }

                    console.log(frmData);

                    // $.ajax({
                    //     url: 'Ajax/AjaxSalaryElements.php',
                    //     type: 'POST',
                    //     data: frmData,
                    //     success: function (data) {
                    //         // $("#divDataList").html(data);
                    //         // fnShowDataTable();
                    //         return false;
                    //     }
                    // });

                    return false;
                }

            });


        </script>

    </body>
</html>
