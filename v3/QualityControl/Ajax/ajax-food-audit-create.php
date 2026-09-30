<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$FilePath = $ROOT_URL . $workingDir;


$MethodType = trim($_POST['methodType']);
if ($MethodType == 'formPost') {
    // echo "hi";
    // exit;
    // upload files//
    $uploadedFiles = [];
    if (!empty($_FILES['files']['name'][0])) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
            $fileName = basename($_FILES['files']['name'][$key]);
            $targetFilePath = "../Uploads/" . $fileName;
            // $targetFilePath = $FilePath . "Uploads/" . $fileName;

            // Create the uploads directory if it doesn't exist
            if (!file_exists("Uploads")) {
                mkdir("Uploads", 0777, true);
            }

            if (move_uploaded_file($tmpName, $targetFilePath)) {
                $uploadedFiles[] = $targetFilePath;
                $uploadedFilesJson = json_encode($uploadedFiles);


            } else {
                $response['message'] = "Failed to upload file: $fileName";
                // echo json_encode($response);
                // exit;
            }
        }
    }
    // upload files//

    $api = API_FOOD_AUDIT;
    $bodyContentPost = array(
        "FoodAuditID" => trim($_POST["foodAuditID"]),
        "PlantID" => trim($_POST["productionPlant"]),
        "AuditNameID" => trim($_POST["auditName"]),
        "AuditTypeID" => trim($_POST["auditType"]),
        "ProductCategoryID" => trim($_POST["productCategory"]),
        "CertificationBodyID" => trim($_POST["certificationBody"]),
        "RegistrationNo" => trim($_POST["registartionNo"]),
        "GradeAndScore" => trim($_POST["gradeNScore"]),
        "AuditStartDate" => trim($_POST["auditStartDate"]),
        "AuditEndDate" => trim($_POST["auditEndDate"]),
        "IssuedOn" => trim($_POST["issuedOn"]),
        "ValidUpto" => trim($_POST["validUpto"]),
        "RenewalDate" => trim($_POST["renewalDate"]),
        "AuditStatus" => trim($_POST["auditStatus"]),
        "CheckedBy" => trim($_POST["checkedBy"]),
        "VerifiedBy" => trim($_POST["verifiedBy"]),
        "Remarks" => trim($_POST["remarks"]),
        // "UploadFile" => trim($_POST["fileUpload"]),
        "UploadFile" => trim($uploadedFilesJson),
        "Status" => trim($_POST["status"]),
    );
    // echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
    // exit;
    $result = callAPI($api, "POST", "", $bodyContentPost);
    header('Content-Type: application/json');
    echo json_encode($result);
} else if ($MethodType == 'paymentPost') {
    $api = API_FOOD_AUDIT_PAYMENT;
    $bodyContentPost = array(
        "FoodAuditPaymentID" => trim($_POST["foodAuditPaymentID"]),
        "FoodAuditID" => trim($_POST["foodAuditID"]),
        "PurposeOfInvoice" => trim($_POST["purposeOfInvoice"]),
        "PaymentType" => trim($_POST["paymentType"]),
        "InvoiceNo" => trim($_POST["invoiceNo"]),
        "InvoidDate" => trim($_POST["invoidDate"]),
        "InvoiceValue" => trim($_POST["invoiceValue"]),
        "Others" => trim($_POST["others"]),
        // "PaymentRemarks" => trim($_POST["paymentRemarks"]),
        "InvoiceDueDate" => trim($_POST["invoiceDueDate"]),
        // "PaymentDate" => trim($_POST["paymentDate"]),
        "TotalAmount" => trim($_POST["totalAmount"]),
        "ConfirmPayment" => "No",
    );
    // echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
    // exit;

    $result = callAPI($api, "POST", "", $bodyContentPost);
    header('Content-Type: application/json');
    echo json_encode($result);
} else if ($MethodType == 'confirmPayment') {
    $api = API_FOOD_AUDIT_PAYMENT;
    $bodyContent = array(
        "FoodAuditID" => trim($_POST["foodAuditID"]),
        "FoodAuditPaymentID" => trim($_POST["foodAuditPaymentID"]),
        "PaymentRemarks" => trim($_POST["paymentRemarks"]),
        "PaymentDate" => trim($_POST["paymentDate"]),
        "ConfirmPayment" => "Yes",
    );
    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // exit;
    $result = callAPI($api, "POST", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($result);
} else if ($MethodType == 'paymentDelete') {
    $api = API_FOOD_AUDIT_PAYMENT;
    $bodyContent = array(
        "FoodAuditID" => trim($_POST["foodAuditID"]),
        "FoodAuditPaymentID" => trim($_POST["foodAuditPaymentID"]),
    );
    $result = callAPI($api, "DELETE", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($result);
} else if ($MethodType == 'getList') {
    $ConfirmPayment = trim($_POST['confirmPayment']);
    $api = API_FOOD_AUDIT;
    $bodyContent = array(
        "FoodAuditID" => trim($_POST["foodAuditID"]),
    );
    $result = callAPI($api, "GET", "", $bodyContent);
    // echo json_encode($result, JSON_PRETTY_PRINT);
    // exit;

    if (isset($result) && !empty($result)) {

        $error = $result->Error;

        if (!isset($error) || empty($error)) {
            $Data = $result->PaymentData;
            ?>
            <?php if (!isset($error) || empty($error)) { ?>
                                <table id="tblDataTable" class="table table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 80px">S.No</th>
                                            <th>Purpose of Invoice</th>
                                            <th>Type of Payment</th>
                                            <th>Invoice No</th>
                                            <th>Invoice Date</th>
                                            <th>Invoice Value</th>
                                            <th>Invoice Due Date</th>
                                            <th>Others</th>
                                            <th>Total Amount</th>
                                            <th>Payment Date</th>
                                            <th>Remarks</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        <?php foreach ($Data as $item) { ?>
                                            <tr>
                                                <td><?php echo $item->SNO ?></td>
                                                <td class="tdPurposeOfInvoice"><?php echo $item->PurposeOfInvoice ?></td>
                                                <td class="tdPaymentType" data-paymenttypeid="<?php echo $item->PaymentType ?>">
                                    <?php echo $item->OptionName ?>
                                                </td>
                                                <td class="tdInvoiceNumber"><?php echo $item->InvoiceNumber ?></td>
                                                <td class="tdInvoiceDate" data-invoicedate="<?php echo $item->InvoiceDate ?>">
                                    <?php echo date('d-m-Y', strtotime($item->InvoiceDate)) ?>
                                                </td>
                                                <td class="tdInvoiceValue"><?php echo $item->InvoiceValue ?></td>
                                                <td class="tdInvoiceDueDate" data-invoiceduedate="<?php echo $item->InvoiceDueDate ?>">
                                    <?php echo date('d-m-Y', strtotime($item->InvoiceDueDate)) ?>
                                                </td>
                                                <td class="tdOthers"><?php echo $item->Others ?></td>
                                                <td class="tdTotalAmount"><?php echo $item->TotalAmount ?></td>
                                                <td><input type="date" class="form-control txtPaymentDate" name="txtPaymentDate"
                                                        value="<?php echo $item->PaymentDate ?>" /></td>
                                                <td><input type="text" class="form-control txtPaymentRemarks" name="txtPaymentRemarks"
                                                        placeholder="Enter Payment Remarks" value="<?php echo $item->PaymentRemarks ?>" /></td>
                                                <td class="text-center">
                                    <?php if ($ConfirmPayment == 1) { ?>
                                                        <button type="button" data-paymentid="<?php echo $item->FoodAuditPaymentID ?>"
                                                            class="btn btn-link p-0 confirmPayment" title="Confirm Payment">
                                                            <i class="btn-primary fa fa-save font-size-16"></i>
                                                        </button>
                                    <?php } else if ($item->PaymentDate == '' && $item->PaymentRemarks == '') { ?>
                                                            <button type="button" data-paymentid="<?php echo $item->FoodAuditPaymentID ?>"
                                                                class="btn btn-link p-0 editBrkUp" title="Edit">
                                                                <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                                            </button>
                                                            <button type="button" data-paymentid="<?php echo $item->FoodAuditPaymentID ?>"
                                                                class="btn btn-link p-0 deleteBrkUp" title="Delete">
                                                                <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                                            </button>
                                    <?php } ?>
                                                </td>
                                            </tr>
                        <?php } ?>
                                    </tbody>
                                </table>
                                <div class="mt-3 mb-3">Showing 1 to <?php echo count($Data) ?> out of <?php echo count($Data) ?> entries</div>
            <?php } ?>
            <?php
            } else {
                echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
            }
    }
}
?>