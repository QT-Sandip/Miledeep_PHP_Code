<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_PURCHASE_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $Plant = trim($_GET["Plant"]);
    $Center = trim($_GET["Center"]);
    $Supplier = trim($_GET["Supplier"]);
    $StartDate = trim($_GET["StartDate"]);
    $EndDate = trim($_GET["EndDate"]);
    $border = "border='1'";
} else {
    $Plant = trim($_POST["Plant"]);
    $Center = trim($_POST["Center"]);
    $Supplier = trim($_POST["Supplier"]);
    $StartDate = trim($_POST["StartDate"]);
    $EndDate = trim($_POST["EndDate"]);
    //$SupplierName = trim($_POST["SupplierName"]);
    $border = "";
}


 $bodyContent = array(
    'Plant' => $Plant,
    'CenterID' => $Center,
    'SupplierID' => $Supplier,
    'StartDate' => $StartDate,
    'EndDate' => $EndDate

);
// echo json_encode($bodyContent , JSON_PRETTY_PRINT);
// exit;
$resList = CallAPI($api, "GET", "", $bodyContent);
$Totals = $resList->Data->Totals;


$error = $resList->Error;
$List = $resList->Data->PurchaseReportData;



 if (!isset($error) || empty($error)) { 
    $content = '
    <table id="tblDataTable"' . $border . ' class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th style="min-width: 100px;">S.No</th>
                <th style="min-width: 160px;">Bill.No</th>
                <th style="min-width: 130px;">Date </th>
                <th style="min-width: 120px;">Quantity</th>
                <th style="min-width: 100px;">Value</th>
                <th style="min-width: 100px;">Harvest.Exps</th>
                <th style="min-width: 160px;">Total Amount</th>
            </tr>
        </thead>
        <tbody>';

             foreach ($List as $item) {
                $content .= '<tr>
                    <td>
                        '. $item->SNO .'
                    </td>
                    <td>
                        '. $item->BillNo .'
                    </td>
                    <td>
                        '. $item->Date .'
                    </td>
                    <td>
                        '. $item->Qty .'
                    </td>
                    <td>
                        '. $item->Amount .'
                    </td>
                    <td>
                        '. $item->HarvestAmount .'
                    </td>
                    <td>
                        '. $item->TotalAmount .'
                    </td>
                </tr>';
             } 
             $content .= '</tbody>
        <tfoot>
            <tr>
                <td align="center">Total</td>
                <td align="center"></td>
                <td align="center"></td>
                <td align="center">'. $Totals->TotalQuantity.'</td>
                <td align="center">'. $Totals->TotalValue.'</td>
                <td align="center"></td>
                <td align="center">'. $Totals->TotalAmountt.'</td>
            </tr>';
            $content .= '
        </tfoot>
    </table>
    ';

} 
else {
    echo "Note : " . $error;
}
if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

$filename = 'Supplier_Wise_Report.xls';
header('Content-Type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=" . $filename . "");
}
echo $content;
