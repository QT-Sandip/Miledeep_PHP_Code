<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// $api = API_PREPARATION_MASTARS;

$FarmName = trim($_POST["farmName"]);
$FarmerName = trim($_POST["farmerName"]);
$CertifyingBody = trim($_POST["certifyingBody"]);
$AssciatedFactory = trim($_POST["assciatedFactory"]);
$GovtIssuingAuthority = trim($_POST["govtIssuingAuthority"]);
$GovtRegisterNo = trim($_POST["govtRegisterNo"]);
$BapNumber = trim($_POST["bapNumber"]);
$TonnagePerAnnum = trim($_POST["tonnagePerAnnum"]);
$WaterSpreadArea = trim($_POST["WaterSpreadArea"]);
$Status = trim($_POST["status"]);
$AuditDate = trim($_POST["auditDate"]);
$IssuedOn = trim($_POST["issuedOn"]);
$InvoiceDavalidityUpto= trim($_POST["validityUpto"]);
$Verifiedby2 = trim($_POST["verifiedby2"]);

// $bodyContent = array(
// "HatcheryName" => trim($_POST["hatcheryName"]),
// "Location" => trim($_POST["location"]),
// "CertifyBody" => trim($_POST["certifyBody"]),
// "RegisterNo" => trim($_POST["registerNo"]),
// "BAPNumber" => trim($_POST["bapNumber"]),
// "ProductPerCycle" => trim($_POST["productPerCycle"]),
// "AuditDate" => trim($_POST["auditDate"]),
// "ExpiryOn" => trim($_POST["expiryOn"]),
// "RenewalDate" => trim($_POST["renewalDate"]),
// "VerifiedBy" => trim($_POST["verifiedBy"]),
// "Status" => trim($_POST["status"]),
// "InvoiceNo" => trim($_POST["invoiceNo"]),
// "InvoiceDate" => trim($_POST["invoiceDate"]),
// "InvoiceValue" => trim($_POST["invoiceValue"]),
// "InvoiceDueDate" => trim($_POST["invoiceDueDate"]),
// "Others" => trim($_POST["others"]),
// "PaymentType" => trim($_POST["paymentType"]),
// "Remarks" => trim($_POST["remarks"]),
// "TotalAmount" => trim($_POST["totalAmount"]),
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
            <th style="width: 80px">S.No</th>
            <th>Type of Payment</th>
            <th>Invoice Date</th>
            <th>Invoice No</th>
            <th>Invoice value</th>
            <th>Due Date</th>
            <th>Other Value</th>
            <th>Total Amount</th>
            <th>Remarks</th>
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
            <td><?php echo "08-07-2024" ?></td>
            <td><?php echo "TEST Other" ?></td>
            <td><?php echo "TEST Amount" ?></td>
            <td><?php echo "TEST Remarks" ?></td>
            <td class="text-center">
                <button type="button" id="btnEdit" name="btnEdit" data-FoodBrkUpID="<?php echo $item->FoodBrkUpID ?>" class="btn btn-link p-0 editBrkUp" title="Edit">
                    <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                </button>
                <button type="button" id="btnDelete" name="btnDelete" data-FoodBrkUpID="<?php echo $item->FoodBrkUpID ?>" class="btn btn-link p-0 deleteBrkUp" title="Delete">
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