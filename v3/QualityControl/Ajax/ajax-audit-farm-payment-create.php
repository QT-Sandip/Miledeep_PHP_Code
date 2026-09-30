<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);


// $api = API_PREPARATION_MASTARS;
if ($methodType == "getList") {
    $PaymentID = trim($_POST["paymentID"]);
} else if ($methodType == "formPost") {

    $NameOfFarm = trim($_POST["nameOfFarm"]);
    $FarmerName = trim($_POST["farmerName"]);
    $CertifyBody = trim($_POST["certifyBody"]);
    $AssociatedFactory = trim($_POST["associatedFactory"]);
    $GovtIssuingAuthority = trim($_POST["govtIssuingAuthority"]);
    $GovtRegistrationNo = trim($_POST["govtRegistrationNo"]);
    $BAPNumber = trim($_POST["bapNumber"]);
    $TonnagePerCycle = trim($_POST["tonnagePerCycle"]);
    $WaterSpreadArea = trim($_POST["waterSpreaArea"]);
    $Status = trim($_POST["status"]);
    $AuditDate = trim($_POST["auditDate"]);
    $IssuedOn = trim($_POST["issuedOn"]);
    $ValidityUpto = trim($_POST["validityUpto"]);
    $VerifiedBy2 = trim($_POST["VerifiedBy2"]);
}
// $bodyContent = array(
// "FarmName" => trim($_POST["farmName"]);
// "Farmer" => trim($_POST["farmer"]);
// "CertifyBody" => trim($_POST["certifyBody"]);
// "RegisterNo" => trim($_POST["registerNo"]);
// "BAPNumber" => trim($_POST["bapNumber"]);
// "ProductPerAnnum" => trim($_POST["productPerAnnum"]);
// "AuditDate" => trim($_POST["auditDate"]);
// "ExpiryOn" => trim($_POST["expiryOn"]);
// "RenewalDate" => trim($_POST["renewalDate"]);
// "VerifiedBy" => trim($_POST["verifiedBy"]);
// "Status" => trim($_POST["status"]);
// );

// $resList = CallAPI($api, "GET", "", $bodyContent);

// if (isset($resList) && !empty($resList)) {

//     $error = $resList->Error;

//     if (!isset($error) || empty($error)) {
//         $PageCount = $resList->PageCount;
//         $resTotal = $resList->TotalCount;
//         $List = $resList->Data;
?>
<?php // if (!isset($error) || empty($error)) { 
?>

<table id="tblDataTable" class="table table-hover table-bordered">
    <thead class="thead-light">
        <tr>
            <th style="width: 50px">S.No</th>
            <th style="min-width: 120px;">Type of Payment</th>
            <th style="min-width: 100px;">Invoice Date</th>
            <th style="min-width: 100px;">Invoice No</th>
            <th style="min-width: 120px;">Invoice value</th>
            <th style="min-width: 100px;">Other Value</th>
            <th style="min-width: 100px;">Total Amount</th>
            <th style="min-width: 100px;">Remarks</th>
            <th style="min-width: 150px;">Payment Date</th>
            <th style="min-width: 150px;">Payment Remarks</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php // foreach ($List as $item) { 
        ?>
        <tr>
            <td><?php echo "1" ?></td>
            <td><?php echo "TEST Payment" ?></td>
            <td><?php echo "08-07-2024" ?></td>
            <td><?php echo "TEST Invoice No" ?></td>
            <td><?php echo "TEST Invoice Value" ?></td>
            <td><?php echo "TEST Other" ?></td>
            <td><?php echo "TEST Amount" ?></td>
            <td><?php echo "TEST Remarks" ?></td>
            <td><input type="date" class="form-control txtPaymentDate" id="txtPaymentDate" name="txtPaymentDate" value="" /></td>
            <td><input type="text" class="form-control txtPaymentRemarks" id="txtPaymentRemarks" name="txtPaymentRemarks" placeholder="Enter Payment Remarks" value="" /></td>
            <td class="text-center">
                <button type="button" id="btnEdit" name="btnEdit" data-AuditBrkUpID="<?php echo $item->AuditBrkUpID ?>" class="btn btn-link p-0 btnSavePayment" title="Save Payment">
                    <i class="btn-primary fa fa-save font-size-16"></i>
                </button>
                <button type="button" id="btnDelete" name="btnDelete" data-AuditBrkUpID="<?php echo $item->AuditBrkUpID ?>" class="btn btn-link p-0 deleteBrkUp" title="Delete Payment">
                    <i class="mdi mdi-delete font-size-16 text-danger"></i>
                </button>
            </td>
        </tr>
        <?php // } 
        ?>
    </tbody>
</table>
<div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
<?php $activepagestyle = "border:2px solid #74788d" ?>
<?php // } 
?>
<div>
    <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
    <ul class="pagination pagination-rounded justify-content-center">
        <?php
        for ($i = 1; $i <= $PageCount; $i++) {
            $pageActive = ($PageNo == $i ? "active" : "");
            echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
        }
        ?>
    </ul>
</div>
<?php
//     } else {
//         echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
//     }
// }
?>