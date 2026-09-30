<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_TDS_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $Plant = trim($_GET["Plant"]);
    $Center = trim($_GET["Center"]);
    $Supplier = trim($_GET["Supplier"]);
    $Agent = trim($_GET["Agent"]);
    $CompanyType = trim($_GET["CompanyType"]);
    $Type = trim($_GET["Type"]);
    $RptType = trim($_GET["RptType"]);
    $StartDate = trim($_GET["StartDate"]);
    $EndDate = trim($_GET["EndDate"]);
    $border = "border='1'";
} else {
    $Plant = trim($_POST["Plant"]);
    $Supplier = trim($_POST["Supplier"]);
    $Agent = trim($_POST["Agent"]);
    $Center = trim($_POST["Center"]);
    $StartDate = trim($_POST["StartDate"]);
    $EndDate = trim($_POST["EndDate"]);
    $CompanyType = trim($_POST["CompanyType"]);
    $Type = trim($_POST["Type"]);
    $RptType = trim($_POST["RptType"]);
    $border = "";
}

$bodyContent = array(
    'Plant' => $Plant,
    'CenterID' => $Center,
    'StartDate' => $StartDate,
    'EndDate' => $EndDate,
    'SupplierID' => $Supplier,
    'AgentID' => $Agent,
    'CompanyType' => $CompanyType,
    'Type' => $Type,
    'ReportType' => $RptType
);

$resList = CallAPI($api, "GET", "", $bodyContent);
$error = $resList->Error;
$List = $resList->Data->PurchaseReportData;
$Totals = $resList->Data->Totals;

if (!isset($error) || empty($error)) {
    $content = ' 
    <table id="tblDataTable"' . $border . ' class="table table-hover table-bordered">
        <thead class="thead-light">

            <tr>
                <th style="min-width: 50px;">S.No </th>
                <th style="min-width: 120px;">Center </th>';
    if ($RptType != 'M') {
        $content .= '<th style="min-width: 80px;">Bill.No </th>
                <th style="min-width: 110px;">Bill Date </th>';
    }
    $content .= '<th style="min-width: 130px;">Farmer Name </th> 
                <th style="min-width: 110px;">PAN Number </th>
                <th style="min-width: 80px;">Type </th>
                <th style="min-width: 100px;">Purchase Value </th>
                <th style="min-width: 130px;">Purchase Value (TDS Deducted) </th>
                <th style="min-width: 120px;">Harvest Value </th> 
                <th style="min-width: 100px;">Purchase TDS Deducted </th>
                <th style="min-width: 110px;">Harvest TDS Deducted </th>
                <th style="min-width: 130px;">Total Value </th>
                <th style="min-width: 130px;">Total TDS </th>

            </tr>';
    $content .= ' </thead>
        <tbody>';

    foreach ($List as $item) {
        $content .= '<tr>
                    <td>
                        ' . $item->SNO . '
                    </td>
                    <td>
                        ' . $item->Center . '
                    </td>';
        if ($RptType != 'M') {
            $content .= '<td>
                        ' . $item->BillNo . '
                    </td>
                    <td>
                        ' . $item->Date . '
                    </td>';
        }
        $content .= '<td>
                        ' . $item->FarmerName . '
                    </td>
                    <td>
                        ' . $item->PanNumber . '
                    </td>
                    
                    <td>
                        ' . $item->Type . '
                    </td>
                    
                    <td>
                        ' . $item->Amount . '
                    </td>
                    <td>
                        ' . $item->TdsDeductedValue . '
                    </td>
                    <td>
                        ' . $item->HarvestAmount . '
                    </td>
                    <td>
                        ' . $item->PurchaseTDSdeducted . '
                    </td>
                    
                    <td>
                        ' . $item->HarvestTDSDeducted . '
                    </td>
                    <td>
                        ' . $item->TotalValue . '
                    </td>
                    <td>
                        ' . $item->TotalTDS . '
                    </td>
                </tr>';
    }
    $content .= '</tbody>

        <tfoot>
            <tr>
                <th style="text-align: center;">Total</th>
                <th style="text-align: center;"></th>';
    if ($RptType != 'M') {
        $content .= '<th style="text-align: center;"> </th>
                <th style="text-align: center;"> </th>';
    }
    $content .= '<th style="text-align: center;"> </th>
               <th style="text-align: center;"> </th>
             <th style="text-align: center;"> </th>

                <th style="text-align: center;">
                    ' . $Totals->TotalValue . '
                </th>
                <th style="text-align: center;">
                    ' . $Totals->TotalPurchaseValueTDSDeducted . '
                </th>
                <th style="text-align: center;">
                    ' . $Totals->TotalHarvestAmount . '
                </th>
               
                <th style="text-align: center;"> </th>
                <th style="text-align: center;"></th>
                
                <th style="text-align: center;">
                    ' . $Totals->TotalAmountt . '
                </th>
                <th style="text-align: center;">
                    ' . $Totals->TotalTDSValue . '
                </th>
   </tr>';
    $content .= '
        </tfoot>
    </table>
    ';
} else {
    echo "Note:" . $error;
}

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

    $filename = 'Tds_Report.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=" . $filename . "");
}
echo $content;

