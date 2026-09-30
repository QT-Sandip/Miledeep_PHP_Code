<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$api = API_SUPPLIER_WISE_LEDGER_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $Plant = trim($_GET["Plant"]);
    $Center = trim($_GET["Center"]);
    $StartDate = trim($_GET["StartDate"]);
    $EndDate = trim($_GET["EndDate"]);
    $SupplierID = trim($_GET["Supplier"]);
    $AgentID = trim($_GET["AgentID"]);
    $BillerType = trim($_GET["BillerType"]);
    $border = "border='1'";
} else {
    $Plant = trim($_POST["Plant"]);
    $Center = trim($_POST["Center"]);
    $StartDate = trim($_POST["StartDate"]);
    $EndDate = trim($_POST["EndDate"]);
    $SupplierID = trim($_POST["Supplier"]);
    $AgentID = trim($_POST["AgentID"]);
    $BillerType = trim($_POST["BillerType"]);
    $border = "";
}

$bodyContent = array(
    'Plant' => $Plant,
    'Center' => $Center,
    'StartDate' => $StartDate,
    'EndDate' => $EndDate,
    'SupplierID' => $SupplierID,
    'AgentID' => $AgentID,
    'BillerType' => $BillerType

);

$resList = CallAPI($api, "GET", "", $bodyContent);
$error = $resList->Error;

if (!isset($error) || empty($error)) {

    $Totals = $resList->Data->Totals;
    $List = $resList->Data->PaymentCenterwiseReportData;

    $content = '<table id="tblDataTable" ' . $border . ' class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th style="min-width: 70px;">DATE</th>
                <th style="min-width: 130px;">Paid Through</th>
                <th style="min-width: 250px;">PARTICULARS </th>
                <th style="min-width: 100px;">DEBIT</th>
                <th style="min-width: 100px;">TDS Deduction</th>
                <th style="min-width: 130px;">Credit</th>
            </tr>
        </thead>
        <tbody>';
    $i = 1;
    foreach ($List as $item) {

        if ($item->SNO != "") {
            $content .= '
                        <tr>
                            <td>' . $item->Date . '</td>
                            <td>' . $item->PaidThrough . '</td>
                            <td>' . $item->Particulars . '</td>
                            <td>' . $item->Debit . '</td>
                            <td>' . $item->TDSDeduction . '</td>
                            <td>' . $item->Credit . '</td>
                        </tr>';
        } else {
            $content .= '
                        <tr>
                            <!--<td style="color: #6eaeff">' . $i . '</td>-->
                            <td style="color: #6eaeff"></td>
                            <td style="color: #6eaeff"></td>
                            <td style="color: #6eaeff">' . $item->Particulars . '</td>
                            <td style="color: #6eaeff">' . $item->SubTotal . '</td>
                            <td style="color: #6eaeff"></td>
                            <td style="color: #6eaeff"></td>
                        </tr>';
        }
        $i++;
    }
    $content .= '</tbody>
        <tfoot style="font-size: 16px;">
            <tr>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th style="text-align: right;">Totals </th>
                <th>' . $Totals->TotalDebitAmount . '</th>
                <th>' . $Totals->TotalTDSDeduction . '</th>
                <th>' . $Totals->TotalOSAmount . '</th>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th style="text-align: right;">Balance Amount</th>
                <th>' . $Totals->BalanceAmount . '</th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>' .  $Totals->EntryDescription . ' <br /></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </tfoot>
    </table>';
} else {
    echo "Note :" . $error;
}

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

    $filename = 'Supplier_Wise_Ledger_Report.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=" . $filename . "");
}

echo $content;
