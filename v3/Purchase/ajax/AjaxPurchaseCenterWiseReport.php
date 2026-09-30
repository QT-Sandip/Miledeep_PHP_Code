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
                <tr style="height: 23px">
                    <th style="min-width: 50px;">S.No</th>
                    <th style="min-width: 110px;">Date	</th>
                    <th style="min-width: 110px;">Bill No</th>
                    <th style="min-width: 110px;">Center</th>
                    <th style="min-width: 120px;">Name of the supplier</th>
                    <th style="min-width: 100px;">Quantity</th>
                    <th style="min-width: 100px;">Basic-Value</th>
                    <th style="min-width: 100px;">Harvest-Expenses</th>
                    <th style="min-width: 100px;">Total </th>
                    
                    
                   
                </tr>
            </thead>
            <tbody>';

               
                  foreach ($List as $item) {
                    $content .= ' <tr style="height: 23px">
                            <td>'. $item->SNO .'</td>	
                            <td>'. $item->Date .'</td>	
                            <td>'. $item->BillNo .'</td>
                            <td>'. $item->Center .'</td>	
                            <td>'. $item->Supplier .'</td>
                            <td>'. $item->Qty .'</td>	
                            <td>'. $item->Amount .'</td>
                            <td>'. $item->HarvestAmount .'</td>
                            <td>'. $item->TotalAmount .'</td>
                        </tr>';
                 } 
                 $content .= ' </tbody>
            <tfoot>
            <tr style="height: 23px">
                    <td align="center">Total</td>
                    <td align="center"></td>
                    <td align="center"></td>
                    <td align="center"></td>
                    <td align="center"></td>
                    <td align="center">'. $Totals->TotalQuantity .'</td>
                    <td align="center">'. $Totals->TotalValue.'</td>
                    <td align="center"></td>
                    <td align="center">'. $Totals->TotalAmountt.'</td>
                </tr>';
                $content .= '
            </tfoot>
        </table>
            ';
        
}
else{
    echo "Note : ". $error;
}
if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

$filename = 'Purchase_Center_Wise_Report.xls';
header('Content-Type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=" . $filename . "");
}

echo $content;

