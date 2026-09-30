<?php
header('Content-Type: application/json; charset=utf-8');
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_EMPLOYEE_BANK_DETAILS_CREATE;

$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

    if ($methodType == "post") {
        $breakupId = trim($_POST["breakupId"]);
        $empID = trim($_POST["empID"]);
        $bank = trim($_POST["bank"]);
        $accountNumber = trim($_POST["accountNumber"]);
        $accountType = trim($_POST["accountType"]);
        $ifsc = trim($_POST["ifsc"]);
        $doc = trim($_POST["doc"]);

        $checkApi = API_EMPLOYEE_BANK_DETAILS_CREATE;
        $checkBody = array( "EmployeeID"=>$empID, "accountNumber" => $accountNumber, 'breakupId' => $breakupId);
        $checkResult = CallAPI($checkApi, "GET", "", $checkBody);

        if (isset($checkResult->Data) && $checkResult->Data ==1) {
            echo json_encode(array("status" => "error", "message" => "Account number already exists. Please enter a unique account number."));
            exit;
        }

        $bodyContent = array(
            "BreakUpId" => $breakupId,
            "EmployeeID" => $empID,
            "BankID" => $bank,
            "AccountNumber" => $accountNumber,
            "AccountType" => $accountType,
            "Ifsc" => $ifsc,
            "DateOfCreation" => $doc
        );

        $result = CallAPI($api, "POST", array("Content-Type: application/json"), $bodyContent);

    } else if ($methodType == "get") {

        $empID = trim($_POST["empID"]);

        $bodyContent = array(
            "EmployeeID" => $empID
        );

        $result = CallAPI($api, "GET", "", $bodyContent);
    }
}

$error = isset($result) ? (isset($result->Error) ? $result->Error : null) : "Request failed";

if (!isset($error) || empty($error)) {

    $resList = isset($result->Data->EmployeeBankDetails) ? $result->Data->EmployeeBankDetails : null;
    if (isset($resList) && !empty($resList)) {

        $tableHtml = '<div class="card"><div class="card-body">';
        $tableHtml .= '<table id="tblDataTable" class="table table-hover table-bordered">';
        $tableHtml .= '<thead class="thead-light"><tr>';
        $tableHtml .= '<th>Employee Name</th><th>Bank Name</th><th>Account Number</th><th>IFSC</th><th>AccountType</th><th>Date of Creation</th><th>Actions</th>';
        $tableHtml .= '</tr></thead><tbody>';

        foreach ($resList as $item) {
            $tableHtml .= '<tr>';
            $tableHtml .= '<td>' . htmlspecialchars($item->EmployeeName) . '</td>';
            $tableHtml .= '<td class="tdBankName" data-bankid="' . htmlspecialchars($item->BankID) . '">' . htmlspecialchars($item->BankName) . '</td>';
            $tableHtml .= '<td class="tdAccountNumber">' . htmlspecialchars($item->AccountNumber) . '</td>';
            $tableHtml .= '<td class="tdIfsc">' . htmlspecialchars($item->Ifsc) . '</td>';
            $tableHtml .= '<td class="tdAccountType" data-actype="savings">' . htmlspecialchars($item->AccountType) . '</td>';
            $tableHtml .= '<td class="tdDateOfCreation" data-doc-date="' . date("Y-m-d", strtotime($item->DateOfCreation)) . '">' . htmlspecialchars($item->DateOfCreation) . '</td>';
            $tableHtml .= '<td class="text-center"><button class="mr-3 text-primary btnActions btnEdit" data-breakup-id="' . htmlspecialchars($item->BreakupID) . '" title="Edit"><i class="mdi mdi-pencil font-size-18"></i></button></td>';
            $tableHtml .= '</tr>';
        }

        $tableHtml .= '</tbody></table></div></div>';

        echo json_encode(array("status" => "success", "table" => $tableHtml));
    } else {
        echo json_encode(array("status" => "success", "table" => ""));
    }
} else {
    echo json_encode(array("status" => "error", "message" => $error));
}
?>
