<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

if(isset($_POST["methodType"]) && !empty($_POST["methodType"])) {
    $methodType = trim($_POST["methodType"]);
} else {
    $methodType = "";
}

if($methodType == "getsal") {

    $api = API_HRM_SALARY_STRUCTURE_MASTER;
    $bodyContent = array(        
        "StructureID" => trim($_POST["salaryId"]));
    $resSalaryMaster = CallAPI($api, "GET", "", $bodyContent);
    
    $List = $resSalaryMaster->Data->AllValues;

    // echo json_encode($resSalaryMaster, JSON_PRETTY_PRINT);exit;

    $i = 1;
    foreach($List as $item) {
        foreach($item->Options as $element) {
            $IdName = preg_replace('/[^a-zA-Z0-9-_\.]/','', $element->ElementName);
            echo '
            <div class="form-group mb-3 col-sm-3">
                <label for="'.$IdName.'">'.$element->ElementName.'<span style="color:red">*</span></label>
                <input required type="'.$element->ElementType.'" data-elementid="'.$element->ElementID.'"  class="form-control dynField element'.$element->ElementID.'" name="'.$IdName.'" id="'.$IdName.'" />
            </div>';
        }

        if($i != count($List)) {
            echo "<hr />";
        }
        $i++;
    }
    

} else if($methodType == "create") {
    
    // echo json_encode($_POST, JSON_PRETTY_PRINT); exit;

    $apiCreateEmp = API_HRM_EMPLOYEE_CREATION;

    // echo json_encode($_POST["SalaryStructureObj"]);exit;
    
    $bodyContent = array(        
            "EmployeeID" => trim($_POST["empId"]),  // ?

            "BranchID" => trim($_POST["drpBranch"]),
            "DepartmentID" => trim($_POST["drpDepartment"]),
            "BiometricId" => trim($_POST["txtBiometricId"]),
            "EmployeeName" => trim($_POST["txtEmployeeName"]),
            "GenderID" => trim($_POST["drpGender"]),
            "CategoryID" => trim($_POST["drpCategory"]),
            "SubCategoryID" => trim($_POST["drpSubCategory"]),
            "DesignationID" => trim($_POST["drpDesignation"]),
            "ShiftCategoryID" => trim($_POST["drpShift"]),
            "JoiningDate" => trim($_POST["txtJoiningDate"]),
            "ReJoinee" => trim($_POST["txtReJoinee"]),
            "PrevEmployeIDRefno" => trim($_POST["txtPrevEmployeeIDRefno"]),
            // "EmployeeCategory" => trim($_POST["txtEmployeeCategory"]),  
            "ContractorNameID" => trim($_POST["drpContractorName"]),

            "FatherName" => trim($_POST["txtFatherName"]),
            "SpouseName" => trim($_POST["txtSpouseName"]),
            "Grade" => trim($_POST["txtGrade"]),
            "Division" => trim($_POST["txtDivision"]),
            "MaritalStatusID" => trim($_POST["drpMaritalStatus"]),
            "PhoneNumber" => trim($_POST["txtPhoneNumber"]),
            "AlternatePhoneNumber" => trim($_POST["txtAlternatePhoneNumber"]),
            "PresentAddress" => trim($_POST["txtPresentAddress"]),
            "PermanentAddress" => trim($_POST["txtPermanentAddress"]),
            "DOB" => trim($_POST["txtDOB"]),
            "AadharNumber" => trim($_POST["txtAadharNumber"]),
            "PANNumber" => trim($_POST["txtPANNumber"]),
            "BloodGroupID" => trim($_POST["drpBloodGroup"]),

            "SalaryCalculateID" => trim($_POST["txtSalaryCalculateFrom"]),
            "ESIApplicableID" => trim($_POST["drpESIApplicable"]),
            "ESIDispensary" => trim($_POST["txtESIDispensary"]),
            "PFApplicableID" => trim($_POST["drpPFApplicable"]),
            "PFNoForDeptFile" => trim($_POST["txtPFNoForDeptFile"]),
            "RestrictPF" => trim($_POST["txtRestrictPF"]),
            "ZeroPension" => trim($_POST["txtZeroPension"]),
            "ZeroPT" => trim($_POST["txtZeroPT"]),
            "UANNo" => trim($_POST["txtUANNo"]),
            "PFDateOfJoining" => trim($_POST["txtPFDateOfJoining"]),
            "ESICRegistrationDate" => trim($_POST["txtESICRegistrationDate"]),
            "CTC" => trim($_POST["txtCTC"]),
            "GrossSalary" => trim($_POST["txtGrossSalary"]),
            "TDSApplicable" => trim($_POST["txtTDSApplicable"]),
            "SalaryStructureID" => trim($_POST["drpSalaryStructure"]),
            "SalaryStructureObj" => $_POST["SalaryStructureObj"]

            // "SubDepartmentID" => "",
            // "WashingAllowance" => "",
    
    );


    $resCreateEmp = CallAPI($apiCreateEmp, "POST", "", $bodyContent);

    header('Content-Type: application/json');

    echo json_encode($resCreateEmp);
    
    // if (isset($resCreateEmp) && !empty($resCreateEmp)) {

    //     $error = $resCreateEmp->Error;
    
    //     if (!isset($error) || empty($error)) {
            
    //         // $resList = $resCreateEmp->Data;

    //     }
    // }

}
    ?>