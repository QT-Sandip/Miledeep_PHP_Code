<?php
include("../RestAPI.php");

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

   $Exporter = trim($_GET['Exporter']);
   // $Country = trim($_GET['Country']);
   $Consignee = trim($_GET['Consignee']);
   $ClearingAgent = trim($_GET['ClearingAgent']);
   $DateSelection = trim($_GET['DateSelection']);
   $FromDate = trim($_GET['FromDate']);
   $ToDate = trim($_GET['ToDate']);
   $border = "border='1'";
} else {
   $Exporter = trim($_POST['Exporter']);
   // $Country = trim($_POST['Country']);
   $Consignee = trim($_POST['Consignee']);
   $ClearingAgent = trim($_POST['ClearingAgent']);
   $DateSelection = trim($_POST['DateSelection']);
   $FromDate = trim($_POST['FromDate']);
   $ToDate = trim($_POST['ToDate']);
   $border = "";
}

$api = GetClearingAgentReportDataAPI;

$bodyContent = array(
   "Exporter" => $Exporter,
   // "Country" => $Country,
   "Consignee" => $Consignee,
   "ClearingAgent" => $ClearingAgent,
   "DateSelection" => $DateSelection,
   "FromDate" => $FromDate,
   "ToDate" => $ToDate
);

$resGetClearingAgentReportDataAPI = CallAPI($api, "POST", "", $bodyContent);

// echo json_encode($resGetClearingAgentReportDataAPI, JSON_PRETTY_PRINT);
// exit;

$resTotals = $resGetClearingAgentReportDataAPI->Data->Totals;
$resList = $resGetClearingAgentReportDataAPI->Data->getClearingAgentReportData;
$ArrayClearingAgentTransactionsData = $resGetClearingAgentReportDataAPI->Data->ArrayClearingAgentTransactionsData;

$content = '<table id="key-datatable" ' . $border . 'data-paging="false" class="table ">
   <thead class="thead-light">
      <tr style="height: 20px">
         <th style="min-width: 40px;" class="sticky etaStatusCol1">S.No</th>
         <th style="min-width: 150px;" class="sticky etaStatusCol1">INVOICE DATE</th>
         <th style="min-width: 140px;" class="sticky etaStatusCol1">INV NO</th>
         <th style="min-width: 200px;">Terms of Delivery</th>
         <th style="min-width: 150px;">Country</th>
         <th style="min-width: 150px;">U.S. ENTRY NO.</th>
         <th style="min-width: 180px;">Customs entry date</th>
         <th style="min-width: 180px;">Quantity (in LBS)</th>
         <th style="min-width: 220px;">Quantity (in conv KGs) Gross</th>
         <th style="min-width: 220px;">U.S Anti Dumping duty ($)</th>
         <th style="min-width: 220px;">CVD Duty</th>
         <th style="min-width: 180px;">Reciprocal Tariffs Fee</th>
         <th style="min-width: 100px;">HMF</th>
         <th style="min-width: 100px;">MPF </th>
         <th style="min-width: 100px;">ADD%</th>
         <th style="min-width: 250px;">Reciprocal Tariffs %</th>
         <th style="min-width: 100px;">Tariff %</th>
         <th style="min-width: 100px;">Tariff Fee</th>
         <th style="min-width: 200px;">Entered Value FOB VALUE</th>
         <th style="min-width: 150px;">Invoice Value $</th>
         <th style="min-width: 150px;">Entered Invoice Value</th>
         <th style="min-width: 150px;">Agent Name</th>';
foreach ($ArrayClearingAgentTransactionsData as $item) {

   $content .= '<th style="min-width: 150px;">' . $item . ' </th>';
}

$content .= '</tr>
   </thead>
   <tbody>';
foreach ($resList as $item) {
   $content .= '<tr style="height: 23px">
            <td class="sticky etaStatusCol1">' . $item->sno . '</td>
            <td class="sticky etaStatusCol1">' . $item->CInvDate . '</td>
            <td class="sticky etaStatusCol1 bodyTotInv">' . $item->InvoiceNo . '</td>
            <td>' . $item->TermsofDelivery . '</td>
            <td>' . $item->Country . '</td>
            <td>' . trim($item->USEntryNumber) . '</td>
            <td>' . $item->CustomsEntryDate . '</td>
            <td class="bodyTotalNetWgtLbs">' . $item->QuantityinLbs . '</td>
            <td class="bodyTotalGrossWeightKgs">' . $item->QuantityinconvKgsGross . '</td>
            <td>' . $item->USAntiDumpingDuty . '</td>
            <td>' . $item->CountervailingDuty . '</td>
            <td>' . $item->ReciprocalTariffsFee . '</td>
            <td>' . $item->HMF . '</td>
            <td>' . $item->MPF . '</td>
            <td>' . $item->ADD . '</td>
            <td>' . $item->RTP . '</td>
            <td>' . $item->TariffPerc . '</td>
            <td>' . $item->TariffFee . '</td>
            <td class="bodyEnteredvalueFobValue">' . $item->EnteredvalueFobValue . '</td>
            <td class="bodyInvoiceValue">' . $item->InvoiceValue . '</td>
            <td class="bodyEnteredInvoiceValue">' . $item->EnteredInvValue . '</td>
            <td>' . $item->AgentName . '</td>';
   foreach ($ArrayClearingAgentTransactionsData as $ArrayClearingAgentTransaction) {
      $content .= '<th style="min-width: 150px;">' . $item->$ArrayClearingAgentTransaction . ' </th>';
   }

   $content .= '</tr>';
}
$content .= '</tbody>
   <tfoot>
      <tr style="height: 23px">
         <th style="min-width: 60px;">Total Invoices</th>
         <th style="min-width: 150px;" class="footTotInv">' . $resTotals->TotalInvoices . '</th>
         <th style="min-width: 140px;"></th>
         <th style="min-width: 200px;"></th>
         <th style="min-width: 150px;"></th>
         <th style="min-width: 150px;"></th>
         <th style="min-width: 180px;"></th>
         <th style="min-width: 180px;" class="footTotalNetWgtLbs">' . $resTotals->TotalNetWgtLbs . '</th>
         <th style="min-width: 220px;" class="footTotalGrossWeightKgs">' . $resTotals->TotalGrossWeightKgs . '</th>
         <th style="min-width: 220px;"></th>
         <th style="min-width: 100px;"></th>
         <th style="min-width: 250px;"></th>
         <th style="min-width: 100px;"></th>
         <th style="min-width: 100px;"></th>
         <th style="min-width: 250px;"></th>
         <th style="min-width: 200px;"></th>
         <th style="min-width: 200px;"></th>
         <th style="min-width: 200px;"></th>
         <th style="min-width: 200px;" class="footTotalEnteredvalueFobValue">' . $resTotals->TotalEnteredvalueFobValue . '</th>
         <th style="min-width: 200px;" class="footTotalInvoiceValueUSD" >' . $resTotals->TotalInvoiceValueUSD . '</th>
         <th style="min-width: 150px;"></th>
         <th style="min-width: 150px;"></th>
         <th style="min-width: 180px;"></th>
         <th style="min-width: 200px;"></th>
         <th style="min-width: 250px;"></th>
         <th style="min-width: 250px;"></th> 
      </tr>
   </tfoot>
</table>';


if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

   $filename = 'Clearing_Agent_Report.xls';
   header('Content-Type: application/vnd.ms-excel');
   header("Content-Disposition: attachment; filename=" . $filename . "");
}

echo $content;
