<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientID = $_SESSION['ClientID'];
$DisplayINRValue = isset($ClientID) && $ClientID === 'SMEV3' ? true : false;
$ClientConfigData = $_SESSION['ClientConfigData'];
$HasSauce = $ClientConfigData['HasSauce'];
$DisplayProductDescriptionFromVarietyList = isset($ClientID) && $ClientID === 'DFLV3' ? 1 : 0;

$api = API_SHIPMENT_SALES_LINEWISE_REPORT;


if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $Exporter = trim($_GET["Exporter"]);
    $FromDate = trim($_GET["FromDate"]);
    $ToDate = trim($_GET["ToDate"]);

    $border = "border='1'";
} else {
    $FromDate = trim($_POST["FromDate"]);
    $ToDate = trim($_POST["ToDate"]);
    $Exporter = trim($_POST["Exporter"]);

    $border = "";
}

$bodyContent = array(
    'FromDate' => $FromDate,
    'ToDate' => $ToDate,
    'Exporter' => $Exporter,
    "DisplayProductDescriptionFromVarietyList" => $DisplayProductDescriptionFromVarietyList
);

$resList = CallAPI($api, "POST", "", $bodyContent);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
//echo '<pre>';print_r($resList);die;

$error = $resList->Error;

//$ListTotal = $resList->Data->Totals;
?>
<?php if (!isset($error) || empty($error)) {
    $List = $resList->Data;

    $content = '


        <table id="tblDataTable"  ' . $border . ' class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr style="height: 20px">
                    <th>S.No</th>
                    <th>Sale Invoice Number</th>
                    <th style="min-width:150px; max-width:150px;">Sale Invoice Line Number</th>
                    <th style="min-width:90px; max-width:90px;">Sale Invoice Date</th>
                    <th style="min-width:200px; max-width:200px;">Customer Name</th>
                    <th>Customer Country</th>
                    <th>Customer Zip Code</th>
                    <th style="min-width:200px; max-width:200px;">Purchase Order Number</th>
                    <th>Purchase Order Date</th>
                    <th style="min-width:150px; max-width:150px;">Destination State</th>
                    <th>Destination State Code</th>
                    <th>Country of Final Destination</th>
                    <th>Terms of Delivery</th>
                    <th>Certification</th>
                    <th>Terms of Payment</th>
                    <th style="min-width:150px; max-width:150px;">Product Description as in Invoice</th>
                    <th>Grade</th>
                    <th>Cooked Form</th>
                    <th>Head Status</th>
                    <th>Shell Status</th>
                    <th>Vein Status</th>
                    <th>Tail Status</th>
                    <th>Other Shrimp Preparation</th>
                    <th>Frozen Form</th>
                    <th>Flavoring</th>
                    <th>Presentation</th>
                    <th>Species</th>
                    <th>Preservative</th>
                    <th>Other Quality Grade</th>
                    <th>Glaze Percentage</th>
                    <th>NWFW</th>
                    <th>NCFC</th>
                    <th>Slabs Bags Per Carton</th>
                    <th>Weight of Each Slab bag pouch</th>
                    <th>Weight Measurement</th>
                    <th>Weight Denomination of Each Slab bag pouch</th>
                    <th style="min-width:150px; max-width:150px;">Packing Brand</th>
                    <th>No of Master Cartons</th>
                    <th>Quantity</th>
                    <th>Quantity Unit of Measure</th>
                    <th>Gross Unit Price</th>
                    <th>Value USD</th>';
    if ($DisplayINRValue) {
        $content .= '<th>Value INR</th>';
    }
    $content .= '<th style="min-width:200px; max-width:200px;">Processed and Packed at</th>
                    <th style="min-width:150px; max-width:150px;">Importer on Record</th>
                    <th style="min-width:150px; max-width:150px;">Exporter</th>
                    <th>Container Number</th>
                    <th>Bill of Lading Number</th>
                    <th>Final Destinationasin BL</th>
                    <th>State Code of Final Destinationas in BL</th>
                    <th style="min-width:90px; max-width:90px;">Form 7501 Entry Date</th>
                    <th style="min-width:350px; max-width:350px;">Product Description as in Production</th>
                    <th>Parts of Body</th>
                    <th>Type Product</th>
                    <th>Qty Converted to Kgs</th>
                    <th>GEX Qty</th>';
                    if($HasSauce == 1){
                        $content .= '
                        <th>Shrimp Packing Style</th>
                        <th>Shrimp Qty</th>
                        <th>Sauce Packing Style</th>
                        <th>Sauce Qty</th>
                        <th>Shrimp Price</th>
                        <th>Sauce Price</th>';
                    }
                    $content .= '
                    <th>Qty Percentage</th>
                    <th>Value Percentage</th>
                    <th>GIN Packed Count</th>
                    <th>GIN Grade</th>
                    <th>GEX Packed Count</th>
                    <th>GEX Grade</th>
                    <th>HS Code</th>
                    <th>Shipping Bill Exchange Rate</th>
                </tr>
            </thead>
            <tbody>';
    foreach ($List as $item) {
        $content .= '
                <tr style="height: 23px">
                    <td>' . $item->SNO . '</td>
                    <td>' . $item->SaleInvoiceNumber . '</td>
                    <td>' . $item->SaleInvoiceLineNumber . '</td>
                    <td>' . $item->SaleInvoiceDate . '</td>';
        if ($_GET["excelDownload"] == "T") {
            $content .= '<td>' . $item->CustomerName . '</td>';
        } else {
            $content .= '<td style="min-width:200px; max-width:200px;" class="truncate-text" data-fulltext="' . htmlspecialchars($item->CustomerName, ENT_QUOTES) . '">
                <span class="short-text">' . substr($item->CustomerName, 0, 15) . '...</span>
                <span class="full-text d-none">' . htmlspecialchars($item->CustomerName, ENT_QUOTES) . '</span></td>';
        }

        $content .= ' <td>' . $item->CustomerCountry . '</td>
                    <td>' . $item->CustomerZipCode . '</td>
                    <td>' . $item->PurchaseOrderNumber . '</td>
                    <td>' . $item->PurchaseOrderDate . '</td>
                    <td>' . $item->DestinationState . '</td>
                    <td>' . $item->DestinationStateCode . '</td>
                    <td>' . $item->CountryofFinalDestination . '</td>
                    <td>' . $item->TermsofDelivery . '</td>
                    <td>' . $item->Cerification . '</td>';
        if ($_GET["excelDownload"] == "T") {
            $content .= '<td>' . $item->TermsofPayment . '</td>';
            $content .= '<td>' . $item->ProductDescriptionasinInvoice . '</td>';

        } else {
            $content .= '<td style="min-width:150px; max-width:150px;" class="truncate-text" data-fulltext="' . htmlspecialchars($item->TermsofPayment, ENT_QUOTES) . '">';
            $content .= ' <span class="short-text">' . substr($item->TermsofPayment, 0, 15) . '...</span>
                    <span class="full-text d-none">' . htmlspecialchars($item->TermsofPayment, ENT_QUOTES) . '</span></td>';
            $content .= '<td style="min-width:150px; max-width:150px;" class="truncate-text" data-fulltext="' . htmlspecialchars($item->ProductDescriptionasinInvoice, ENT_QUOTES) . '">
                    <span class="short-text">' . substr($item->ProductDescriptionasinInvoice, 0, 15) . '...</span>';
            $content .= '<span class="full-text d-none">' . htmlspecialchars($item->ProductDescriptionasinInvoice, ENT_QUOTES) . '</span>
        </td>';
        }


        // <a href="#" class="read-more">Read more</a>

        $content .= '<td>' . "" . $item->Grade . '</td> 
                    <td>' . $item->CookedForm . '</td>
                    <td>' . $item->HeadStatus . '</td>
                    <td>' . $item->ShellStatus . '</td>
                    <td>' . $item->VeinStatus . '</td>
                    <td>' . $item->TailStatus . '</td>
                    <td>' . $item->OtherShrimpPreparation . '</td>
                    <td>' . $item->FrozenForm . '</td>
                    <td>' . $item->Flavoring . '</td>
                    <td>' . $item->Presentation . '</td>
                    <td>' . $item->Species . '</td>
                    <td>' . $item->Preservative . '</td>
                    <td>' . $item->OtherQualityGrade . '</td>
                    <td>' . $item->GlazePercentage . '</td>
                    <td>' . $item->NWFW . '</td>
                    <td>' . $item->NCFC . '</td>
                    <td>' . $item->SlabsBagsPerCarton . '</td>
                    <td>' . $item->WeightofEachSlabbagpouch . '</td>
                    <td>' . $item->WeightMeasurement . '</td>
                    <td>' . $item->WeightDenominationofEachSlabbagpouch . '</td>
                    <td>' . $item->PackingBrand . '</td>
                    <td>' . $item->NoofMasterCartons . '</td>
                    <td>' . $item->Quantity . '</td>
                    <td>' . $item->QuantityUnitofMeasure . '</td>
                    <td>' . $item->GrossUnitPrice . '</td>';
        if ($ClientID === 'SMEV3') {
            $content .= '<td>' . $item->SMEInvValueUsd . '</td>';
        } else {
            $content .= '<td>' . $item->ValueUSD . '</td>';
        }
        if ($DisplayINRValue) {
            $content .= '<td>' . $item->SMEInvValueInr . '</td>';
        }
        $content .= '<td style="min-width:150px; max-width:150px;">' . $item->ProcessedandPackedat . '</td>
                    <td style="min-width:150px; max-width:150px;">' . $item->ImporteronRecord . '</td>
                    <td style="min-width:150px; max-width:150px;">' . $item->Exporter . '</td>
                    <td>' . $item->ContainerNumber . '</td>
                    <td>' . $item->BillofLadingNumber . '</td>
                    <td>' . /* $item->FinalDestinationasinBL */
                     $item->FinalDestinationPortName. '</td>
                    <td>' . /* $item->StateCodeofFinalDestinationasinBL */ 
                    $item->ConsigneeZipCode. '</td>
                    <td>' . $item->Form7501EntryDate . '</td>
                    <td>' . $item->ProductDescriptionasinProduction . '</td>
                    <td>' . $item->PartsofBody . '</td>
                    <td>' . $item->TypeProduct . '</td>
                    <td>' . $item->QtyConvertedtoKgs . '</td>
                    <td>' . $item->GEXQty . '</td>';
                    if($HasSauce == 1){
                        $content .= '
                        <td>' . $item->ShrimpPackingStyle . '</td>
                        <td>' . number_format($item->ShrimpQty, 2) . '</td>
                        <td>' . $item->SaucePackingStyle . '</td>
                        <td>' . number_format($item->SauceQty, 2) . '</td>
                        <td>' . $item->ShrimpUnitPrice . '</td>
                        <td>' . $item->SauceUnitPrice . '</td>';
                    }
                    $content .= '
                    <td>' . number_format($item->QtyPercentage, 2) . '</td>
                    <td>' . $item->ValuePercentage . '</td>
                    <td>' . $item->GINPackedCount . '</td>
                    <td>' . $item->GINGrade . '</td>
                    <td>' . $item->GEXPackedCount . '</td>
                    <td>' . $item->GEXGrade . '</td>
                    <td>' . $item->HSCode . '</td>
                    <td>' . $item->SBExchangeRate . '</td>
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

    $filename = 'Shipment_Sales_Line_Itemwise_Report.xls';
    // header("Content-Type: application/xls");
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=" . $filename . "");
}

echo $content;
?>