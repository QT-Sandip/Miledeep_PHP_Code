<?php
//session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_PURCHASE_SUPPLIER_WISE_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $Plant = trim($_GET["Plant"]);
    $Center = trim($_GET["Center"]);
    $Supplier = trim($_GET["Supplier"]);
    $StartDate = trim($_GET["StartDate"]);
    $EndDate = trim($_GET["EndDate"]);
} else {
    $Plant = trim($_POST["Plant"]);
    $Center = trim($_POST["Center"]);
    $Supplier = trim($_POST["Supplier"]);
    $StartDate = trim($_POST["StartDate"]);
    $EndDate = trim($_POST["EndDate"]);
}

$bodyContent = [
    'Plant' => $Plant,
    'CenterID' => $Center,
    'SupplierID' => $Supplier,
    'StartDate' => $StartDate,
    'EndDate' => $EndDate
];

$resList = CallAPI($api, "GET", "", $bodyContent);
$error = $resList->Error;
$List = $resList->Data->DateWiseReportData;
$Totals = $resList->Data->Totals;

if (isset($error) && !empty($error)) {
    die("Error: " . $error);
}

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    echo "<html><head>
            <script src='https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js'></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    exportToExcel();
                });
                
                function exportToExcel() {
                    var wb = XLSX.utils.book_new();
                    var ws_data = [['Purchase Supplier Wise Report from $StartDate to $EndDate'],
                        ['S.No', 'Supplier', 'HONV Quantity', 'HONV Value', 'HONBT Quantity', 'HONBT Value', 'Total Quantity', 'Total Value']
                    ];
            ";

    foreach ($List as $item) {
        echo "ws_data.push([
                    '{$item->SNO}',
                    '{$item->FinancierName}',
                    parseFloat('{$item->HONVMQTY}'),
                    parseFloat('{$item->HONVMValue}'),
                    parseFloat('{$item->HONBTQTY}'),
                    parseFloat('{$item->HONBTValue}'),
                    parseFloat('{$item->TotalQty}'),
                    parseFloat('{$item->TotalValue}')
                ]);
                ";
    }

    echo "ws_data.push([
                    'Totals', '',
                    parseFloat('{$Totals->TotalHonVmQty}'), 
                    parseFloat('{$Totals->TotalHonVmValue}'),
                    parseFloat('{$Totals->TotalHonBTQty}'), 
                    parseFloat('{$Totals->TotalHonBTValue}'),
                    parseFloat('{$Totals->TotalQuantity}'), 
                    parseFloat('{$Totals->TotalAmount}')
                ]);

        var ws = XLSX.utils.aoa_to_sheet(ws_data);
        // Merge cells A1 to H1 
        ws['!merges'] = [
            { s: { r: 0, c: 0 }, e: { r: 0, c: 7 } }, // Merging row 1 (A1:H1)
        ];

        // Set column widths
        ws['!cols'] = [
            { wch: 10 },
            { wch: 20 },
            { wch: 15 },
            { wch: 15 },
            { wch: 15 },
            { wch: 15 },
            { wch: 15 },
            { wch: 15 }
        ];
        XLSX.utils.book_append_sheet(wb, ws, 'Report');
        XLSX.writeFile(wb, 'Purchase_Supplier_Wise_Report.xlsx');
    }
    </script>
    </head><body></body></html>";
    exit;
} else {
    echo "<table border='1' id='tblDataTable' class='table table-hover table-bordered'> 
            <thead>
                <tr>
                    <th>S.No</th><th>Supplier</th><th>HONV Quantity</th><th>HONV Value</th>
                    <th>HONBT Quantity</th><th>HONBT Value</th><th>Total Quantity</th><th>Total Value</th>
                </tr>
            </thead>
            <tbody>";
    foreach ($List as $item) {
        echo "<tr>
                <td>{$item->SNO}</td>
                <td>{$item->FinancierName}</td>
                <td>" . (float)$item->HONVMQTY . "</td>
                <td>" . number_format($item->HONVMValue, 2) . "</td>
                <td>" . (float)$item->HONBTQTY . "</td>
                <td>" . number_format($item->HONBTValue, 2) . "</td>
                <td>" . (float)$item->TotalQty . "</td>
                <td>" . number_format($item->TotalValue, 2) . "</td>
              </tr>";
    }
    echo "</tbody>
            <tfoot>
                <tr>
                    <td>Totals</td><td></td>
                   <td>" . number_format($Totals->TotalHonVmQty, 3) . "</td>
                    <td>" . number_format($Totals->TotalHonVmValue, 2) . "</td>
                    <td>" . number_format($Totals->TotalHonBTQty, 3) . "</td>
                    <td>" . number_format($Totals->TotalHonBTValue, 2) . "</td>
                    <td>" . number_format($Totals->TotalQuantity, 3) . "</td>
                    <td>" . number_format($Totals->TotalAmount, 2) . "</td>
                </tr>
            </tfoot>
          </table>";
}
