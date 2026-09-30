<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientID = $_SESSION['ClientID'];
$ConfigDetails['ClientConfigData'] = $_SESSION['ClientConfigData'];
$ConfigDetails['ClientInvoiceConfigData'] = $_SESSION['ClientInvoiceConfigData'];
$ConfigDetails['ClientID'] = $ClientID;

$api = API_GST_SALES_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $Exporter = trim($_GET["Exporter"]);
    $FromDate = trim($_GET["FromDate"]);
    $ToDate = trim($_GET["ToDate"]);

    $border = "border='1'";
} else {
    $FromDate = trim($_POST["FromDate"]);
    $ToDate = trim($_POST["ToDate"]);
    $ExporterID = trim($_POST["ExporterID"]);

    $border = "";
}

$bodyContent = array(
    'FromDate' => $FromDate,
    'ToDate' => $ToDate,
    'ExporterID' => $ExporterID,
    'ConfigDetails' => $ConfigDetails
);

$resList = CallAPI($api, "POST", "", $bodyContent);
// echo json_encode($resList, JSON_PRETTY_PRINT);exit;

$error = $resList->Error;


?>

<?php if (!isset($error) || empty($error)) {

    $List = $resList->Data;


    $content = '

    <div class="card">
        <div class="card-body">

            <table id="tblDataTable"' . $border . 'class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th class="sticky etaStatusCol1">S.No.</th>
                        <th  style="min-width: 90px;" class="sticky etaStatusCol1">Export Type</th>
                        <th  style="min-width: 150px;">Shipping Bill Number</th>
                        <th  style="min-width: 150px;">Shipping Bill Date</th>
                        <th  style="min-width: 50px;">PortCode</th>
                        <th  style="min-width: 120px;" class="sticky etaStatusCol1">Invoice Number</th>
                        <th  style="min-width: 120px;">Invoice Date</th>
                        <th  style="min-width: 120px;">Raw Shrimp USD</th>
                        <th  style="min-width: 120px;">Cooked Shrimp USD</th>
                        <th  style="min-width: 120px;">Sauce Shrimp USD</th>
                        <th  style="min-width: 120px;">Total FOB Value USD</th>
                        <th  style="min-width: 120px;">Total Invoice Value USD</th>
                        <th  style="min-width: 50px;">ExRate</th>
                        <th  style="min-width: 120px;">Invoice Value INR</th>

                        <th  style="min-width: 120px;">Raw Shrimp FOB INR</th>
                        <th  style="min-width: 120px;">Cooked Shrimp FOB INR</th>
                        <th  style="min-width: 120px;">Sauce Shrimp FOB INR</th>

                        <th  style="min-width: 150px;">Raw Shrimp Quantity</th>
                        <th  style="min-width: 150px;">Cooked Shrimp Quantity</th>
                        <th  style="min-width: 150px;">SAUCE Shrimp Quantity</th>

                        <th  style="min-width: 150px;">Total FOB VALUE</th>
                        <th  style="min-width: 150px;">FREIGHT VALUE</th>
                        <th  style="min-width: 150px;">ANTIDUMPING DUTY</th>
                        <th  style="min-width: 150px;">INSURANCE</th>

                        <th  style="min-width: 170px;">Total Invoice Value As Per Shipping Bill</th>
                        <th  style="min-width: 170px;">Raw Shrimp Invoice Value As Per Shipping Bill</th>
                        <th  style="min-width: 170px;">Cooked Shrimp Invoice Value As Per Shipping Bill</th>
                        <th  style="min-width: 170px;">Sauce Shrimp Invoice Value As Per Shipping Bill</th>
                        <th  style="min-width: 170px;">Total Invoice Value As Per Shipping Bill</th>
                        <th  style="min-width: 170px;">Raw Shrimp Taxable Value As Per Shipping Bill</th>
                        <th  style="min-width: 170px;">Cooked Shrimp Taxable Value As Per Shipping Bill</th>
                        <th  style="min-width: 170px;">Sauce Shrimp Taxable Value As Per Shipping Bill</th>
                        <th  style="min-width: 170px;">Total Taxable Value As Per Shipping Bill</th>

                        <th  style="min-width: 150px;">Applicable Percent Of Tax Rate</th>
                        <th  style="min-width: 150px;">IGST On Raw Shrimp</th>
                        <th  style="min-width: 150px;">IGST On Cooked Shrimp</th>
                        <th  style="min-width: 150px;">IGST On Sauce Shrimp</th>
                        <th  style="min-width: 150px;">Total IGST Value</th>
                    </tr>
                </thead>
                <tbody>';
    foreach ($List as $item) {
        $content .= '<tr>
                            <td class="sticky etaStatusCol1">
                                ' . $item->SNO . ' 
                            </td>
                            <td class="sticky etaStatusCol1">
                                ' . $item->ExportType . '
                            </td>
                            <td>
                                ' . $item->ShippingBillNumber . '
                            </td>
                            <td>
                                ' . $item->ShippingBillDate . '
                            </td>
                            <td>
                                ' . $item->PortCode . '
                            </td>
                            <td class="sticky etaStatusCol1">
                                ' . $item->InvoiceNumber . '
                            </td>
                            <td>
                                ' . $item->InvoiceDate . '
                            </td>
                            <td>
                                ' . $item->RawFOBValue . '
                            </td>
                            <td>
                                ' . $item->CookedFOBValue . '
                            </td>
                            <td>
                                ' . $item->SauceFOBValue . '
                            </td>
                            <td>
                                ' . $item->TotalFOBValueUSD . '
                            </td>
                            <td>
                                ' . $item->InvoiceValueUSD . '
                            </td>
                            <td>
                                ' . $item->ExRate . '
                            </td>
                            <td>
                                ' . $item->InvoiceValueINR . '
                            </td>
                            <td>
                                ' . $item->RawShrimpFOBINR . '
                            </td>
                            <td>
                                ' . $item->CookedShrimpFOBINR . '
                            </td>
                            <td>
                                ' . $item->SauceShrimpFOBINR . '
                            </td>
                            <td>
                                ' . $item->RawShrimpQuantity . '
                            </td>
                            <td>
                                ' . $item->CookedShrimpQuantity . '
                            </td>
                            <td>
                                ' . $item->SauceQuantity . '
                            </td>
                            <td>
                                ' . $item->TotalFOBValueINR  . '
                            </td>
                            <td>
                                ' . $item->FreightValue . '
                            </td>
                            <td>
                                ' . $item->AntiDumpingDuty . '
                            </td>
                            <td>
                                ' . $item->Insurance . '
                            </td>
                            <td>
                                ' . $item->TotalInvoiceValueAsPerShippingBill . '
                            </td>
                            <td>
                                ' . $item->RawShrimpInvoiceValueAsPerShippingBill . '
                            </td>
                            <td>
                                ' . $item->CookedShrimpInvoiceValueAsPerShippingBill . '
                            </td>
                            <td>
                                ' . $item->SauceShrimpInvoiceValueAsPerShippingBill . '
                            </td>
                            <td>
                                ' . $item->TotalInvoiceValueAsPerShippingBill . '
                            </td>
                            <td>
                                ' . $item->RawShrimpTaxableValueAsPerShippingBill . '
                            </td>
                            <td>
                                ' . $item->CookedShrimpTaxableValueAsPerShippingBill . '
                            </td>
                             <td>
                                ' . $item->SauceShrimpTaxableValueAsPerShippingBill . '
                            </td>
                            <td>
                                ' . $item->TotalTaxableValueAsPerShippingBill . '
                            </td>
                            <td>
                                ' . $item->ApplicableTaxRatePerc . '
                            </td>
                            <td>
                                ' . $item->IGSTOnRawShrimp . '
                            </td>
                            <td>
                                ' . $item->IGSTOnCookedShrimp . '
                            </td>
                             <td>
                                ' . $item->IGSTOnSauceShrimp . '
                            </td>
                            <td>
                                ' . $item->TotalIGSTValue . '
                            </td>
                        </tr>';
    }
    $content .= '
                </tbody>

            </table>
        ';
} else {
    echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
}
if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

    $filename = 'Gst-Sales-Report.xls';
    // header("Content-Type: application/xls");
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=" . $filename . "");
}

echo $content;
?> 

