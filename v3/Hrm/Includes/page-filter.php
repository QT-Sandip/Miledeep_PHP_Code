<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_HRM_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

// echo json_encode($resDropdownsAPI, JSON_PRETTY_PRINT);

$resDepartment = $resDropdownsAPI->Data->Department;
$resSubDepartments = $resDropdownsAPI->Data->SubDepartments;
$resDesignations = $resDropdownsAPI->Data->Designations;
$resBankDetails = $resDropdownsAPI->Data->BankDetails;

$CurrentMonth = date('m');
$CurrentYear = date('Y');

// Determine the past FY and current FY based on the current month
if ($CurrentMonth > 3) {
    $PastFY = $CurrentYear - 1;
    $CurrentFY = $CurrentYear;
} else {
    $PastFY = $CurrentYear - 2;
    $CurrentFY = $CurrentYear - 1;
}

?>

<div class="row rowFilter">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">

                    <?php
                    $Button = '<input type="button" id="btnSearch" name="btnSearch" class="btn btn-primary" style="max-width:150px;min-width: 150px;max-height: 35px;
                        margin-left: 15px;" value="Search" aria-label="Submit">';

                    if (isset($buttonPay) && !empty($buttonPay)) {
                        $PayButton = '<button id="btnPay" name="btnPay" type="button" data-bs-toggle="modal" data-bs-target="#ModalRmPayment" 
                                            style="max-width:150px; min-width: 150px;max-height: 35px;" class="mx-3 btn btn-success waves-effect waves-light text-left" disabled> 
                                            <i class="fa fa-plus"></i> Pay
                                    </button>';
                    }

                    ?>

                    <?php if (isset($filterDepartment) && !empty($filterDepartment)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect" id="drpDepartment" name="drpDepartment">
                                <option value="">Select Department</option>
                                <?php
                                foreach ($resDepartment as $item)
                                    echo '<option value="' . $item->DeptID . '">' . $item->DepartmentName . '</option>';
                                ?>

                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterSubDepartments) && !empty($filterSubDepartments)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect" id="drpSubDepartments" name="drpSubDepartments">
                                <option value="">Select Designations</option>
                                <?php
                                foreach ($resDesignations as $item)
                                    echo '<option value="' . $item->DesigID . '">' . $item->Designation . '</option>';
                                ?>

                            </select>
                        </div>
                    <?php } ?>


                    <?php if (isset($filterDesignations) && !empty($filterDesignations)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect" id="drpDesignations" name="drpDesignations">
                                <option value="">Select Designations</option>
                                <?php
                                foreach ($resDesignations as $item)
                                    echo '<option value="' . $item->SubDeptID . '">' . $item->SubDeptName . '</option>';
                                ?>

                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterEmployeeStatus) && !empty($filterEmployeeStatus)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect" id="drpEmployeeStatus" name="drpEmployeeStatus">
                                <option value="">Employee Status</option>
                                <option value="1">Active</option>
                                <option value="0">In Active</option>
                            </select>
                        </div>
                    <?php } ?>

                    <!-- Durga -->
                    <?php if (isset($filterBankDetails) && !empty($filterBankDetails)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpBank">Bank Name</label>
                            <select class="form-control drpSelect" name="drpBank" id="drpBank">
                                <option value=""> Select Bank</option>
                                <?php foreach ($resBankDetails as $BankDetails) { ?>
                                    <option value="<?php echo $BankDetails->BankNameID ?>"><?php echo $BankDetails->BankName ?> </option>
                                <?php } ?>
                                <select>
                        </div>
                    <?php } ?>


                    <?php if (isset($buttonBottom) && !empty($buttonBottom)) { ?>
                        <div class="text-center mt-1">
                            <?php
                            echo $Button;
                            echo $PayButton;
                            ?>
                        </div>
                    <?php } ?>

                    <?php
                    if (isset($buttonRight) && !empty($buttonRight)) {
                        echo $Button;
                        echo $PayButton;
                    }
                    ?>

                </div>
            </div>
        </div>
    </div>
</div>