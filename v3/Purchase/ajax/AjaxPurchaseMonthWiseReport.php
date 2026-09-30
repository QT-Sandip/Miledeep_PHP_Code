<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_PURCHASE_MONTH_WISE_REPORT;
//$api = API_RM_PURCHASE_DATE_WISE_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $Plant = trim($_GET["Plant"]);
    $Center = trim($_GET["Center"]);
    $StartDate = trim($_GET["StartDate"]);
    $EndDate = trim($_GET["EndDate"]);
    $border = "border='1'";
} else {
    //$Year = trim($_POST["Year"]);
    $Center = trim($_POST["Center"]);
    $Plant = trim($_POST["Plant"]);
    $StartDate = trim($_POST["StartDate"]);
    $EndDate = trim($_POST["EndDate"]);
    $border = "";
}


$bodyContent = array(
    'StartDate' => $StartDate,
    'EndDate' => $EndDate,
    'CenterID' => $Center,
    'Plant' => $Plant
);

$resList = CallAPI($api, "GET", "", $bodyContent);

$error = $resList->Error;
$List = $resList->Data->DateWiseReportData;
$Totals = $resList->Data->Totals;

 if (!isset($error) || empty($error)) { 
    $content = '
    <table id="tblDataTable"' . $border . '  class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th style="min-width: 50px;">S.No</th>
                <th style="min-width: 40px;">Month</th>
                <th style="min-width: 100px;">HONV Quantity</th>
                <th style="min-width: 90px;">HONV Value</th>
                <th style="min-width: 100px;">HONBT Quantity</th>
                <th style="min-width: 120px;">HONBT Value</th>
                <th style="min-width: 100px;">Total Quantity</th>
                <th style="min-width: 100px;">Total Value</th>
            </tr>
        </thead>
        <tbody>';

            foreach ($List as $item) { 
                $content .= ' <tr>
                    <td>
                        '. $item->SNO .'
                    </td>
                    <td>
                        '. $item->Month .'
                    </td>
                    <td>
                        '. $item->HONVMQTY .'
                    </td>
                    <td>
                        '. $item->HONVMValue .'
                    </td>
                    <td>
                        '. $item->HONBTQTY .'
                    </td>
                    <td>
                        '. $item->HONBTValue .'
                    </td>
                    <td>
                        '. $item->TotalQty .'
                    </td>
                    <td>
                        '. $item->TotalValue .'
                    </td>
                </tr>';
             } 
             $content .= '</tbody>

        <tfoot>
            <tr>
                <th align="center">Total</th>
                <th align="center"></th>
                <th align="center">
                    '. $Totals->TotalHonVmQty .'
                </th>
                <th align="center">
                    '. $Totals->TotalHonVmValue .'
                </th>
                <th align="center">
                    '. $Totals->TotalHonBTQty .'
                </th>
                <th align="center">
                    '. $Totals->TotalHonBTValue .'
                </th>
                <th align="center">
                    '. $Totals->TotalQuantity .'
                </th>
                <th align="center">
                    '. $Totals->TotalAmount .'
                </th>


            </tr>';
            $content .= '
        </tfoot>
    </table>
    ';
 } else {
    echo "Note : " . $error;
}
if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

    $filename = 'Purchase_Month_Wise_Report.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=" . $filename . "");
    }
    echo $content;