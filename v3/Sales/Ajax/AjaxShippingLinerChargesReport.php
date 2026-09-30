<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include("../RestAPI.php");
// include_once $_SERVER['DOCUMENT_ROOT'] . '/config/api-call.php';
$Liner = trim($_GET['Liner']);
$FromDate = trim($_GET['FromDate']);
$ToDate = trim($_GET['ToDate']);

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
   $border = "border='1'";
} else {
   $border = "";
}

$api = API_SHIPPING_LINES_CHARGES_REPORT;

$bodyContent = array(
   "Liner" => $Liner,
   "FromDate" => $FromDate,
   "ToDate" => $ToDate
);
$SqlShippingLinesChargesCategory = "SELECT * FROM `ShippingLinesChargesCategory` ORDER BY sort ASC";
$StmtShippingLinesChargesCategory = $iconn->query($SqlShippingLinesChargesCategory);


$resGetClearingAgentReportDataAPI = CallAPI($api, "GET", "", $bodyContent);

$resTotals = $resGetClearingAgentReportDataAPI->Data;

// echo json_encode($resTotals, true);
// $resList = $resGetClearingAgentReportDataAPI->Data->getClearingAgentReportData;
// $ArrayClearingAgentTransactionsData = $resGetClearingAgentReportDataAPI->Data->ArrayClearingAgentTransactionsData;

$content = '<table id="key-datatable" ' . $border . ' data-paging="false" class="table">
<thead class="thead-light">
<tr style="height: 20px">
    <th style="min-width: 40px;" rowspan="2">S.No</th>
    <th style="min-width: 200px;" rowspan="2">Bill Number</th>
    <th style="min-width: 150px;" rowspan="2">Bill DATE</th>
    <th style="min-width: 140px;" rowspan="2">INV NO</th>';

// Fetch and print dynamic category headers
$categoryNames = [];
while ($row = $StmtShippingLinesChargesCategory->fetch_assoc()) {
   $categoryNames[] = $row['name'];
   $content .= '<th style="min-width: 200px;" colspan="3">' . htmlspecialchars($row['name']) . '</th>';
}

// Static headers
$content .= '
      <th style="min-width: 180px;" rowspan="2">Total Value</th>
    <th style="min-width: 180px;" rowspan="2">CGST</th>
     <th style="min-width: 180px;" rowspan="2">IGST</th>
    <th style="min-width: 100px;" rowspan="2">SGST</th>
    <th style="min-width: 150px;" rowspan="2">Total Bill Amount</th>
</tr>';

// Sub-headers for each category
$content .= '<tr>';
foreach ($categoryNames as $cat) {
   $content .= '
        <th style="text-align:center;">USD</th>
        <th style="text-align:center;">Ex Rate</th>
        <th style="text-align:center;">INR</th>';
}
$content .= '</tr></thead><tbody>';

// Row generation
$sno = 1;
foreach ($resTotals as $row) {
   $content .= '<tr>';
   $content .= '<td>' . $sno++ . '</td>';

   // Defaults
   $billNumber = '--';
   $billDate = '--';
   $igst = '0.00';
   $sgst = '0.00';
   $cgst = '0.00';


   if (!empty($row->ShippingLinesChargesTransactions)) {
      foreach ($row->ShippingLinesChargesTransactions as $tx) {
         if (!empty($tx->bill_number) || !empty($tx->bill_date)) {
            $billNumber = isset($tx->bill_number) ? $tx->bill_number : '--';
            $billDate = isset($tx->bill_date) ? $tx->bill_date : '--';
            $igst = isset($tx->igst) ? (float) $tx->igst : '0.00';
            $sgst = isset($tx->sgst) ? (float) $tx->sgst : '0.00';
            $cgst = isset($tx->cgst) ? (float) $tx->cgst : '0.00';
            break;
         }
      }
   }

   $content .= '<td>' . htmlspecialchars($billNumber) . '</td>';
   $content .= '<td>' . htmlspecialchars($billDate) . '</td>';
   $content .= '<td>' . htmlspecialchars(isset($row->InvNo) ? $row->InvNo : '-') . '</td>';

   $totalGrossValue = 0;
   $transactions = isset($row->ShippingLinesChargesTransactions) ? $row->ShippingLinesChargesTransactions : [];

   foreach ($categoryNames as $index => $categoryName) {
      if (isset($transactions[$index])) {
         $tx = $transactions[$index];
         $usd = isset($tx->amount_in_usd) ? (float) $tx->amount_in_usd : '0.00';
         $exRate = isset($tx->exchange_rate) ? (float) $tx->exchange_rate : '0.00';
         $inr = isset($tx->amount_in_inr) ? (float) $tx->amount_in_inr : '0.00';


         $lineTotal = $inr;
         $totalGrossValue += $lineTotal;

         $content .= "<td style='text-align:right;'>$usd</td>";
         $content .= "<td style='text-align:right;'>$exRate</td>";
         $content .= "<td style='text-align:right;'>" . number_format($inr, 2) . "</td>";
      } else {
         $content .= "<td></td><td></td><td></td>";
      }
   }

   $content .= '<td style="text-align:right;">' . ((float) $totalGrossValue) . '</td>';

   $totalGrossValue += $igst + $sgst + $cgst;
   $content .= '<td style="text-align:right;">' . $cgst . '</td>';
   $content .= '<td style="text-align:right;">' . $igst . '</td>';
   $content .= '<td style="text-align:right;">' . $sgst . '</td>';
   $content .= '<td style="text-align:right; font-weight:bold;">' . number_format($totalGrossValue, 2) . '</td>';
   $content .= '</tr>';
}

$content .= '</tbody></table>';





if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

   $filename = 'Shipping_line_charges_report.xls';
   header('Content-Type: application/vnd.ms-excel');
   header("Content-Disposition: attachment; filename=" . $filename . "");
}

echo $content;
