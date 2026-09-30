<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientID = $_SESSION['ClientID'];
$DisplayINRValue = isset($ClientID) && $ClientID === 'SMEV3' ? true : false;

$api = API_SHIPMENT_SALES_LINE_PRICE_BOOK_REPORT;

$MethodType = trim($_POST["MethodType"]);

if (isset($_POST["MethodType"]) && $_POST["MethodType"] == "UpdateDDPPriceRemarks") {
    $id = trim($_POST["ItemID"]);
    $price = trim($_POST["DDPPrice"]);
    $remarks = trim($_POST["Remarks"]);
    $table = trim($_POST["Table"]);

    $bodyContent = array(
        'ItemID' => $id,
        'DDPPrice' => $price,
        'Remarks' => $remarks,
        'Table' => $table,
    );

    $resList = CallAPI($api, "PUT", "", $bodyContent);

    echo json_encode($resList, JSON_PRETTY_PRINT);
} else {

    if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
        $Year = trim($_GET["Year"]);
        $Consignee = trim($_GET["Consignee"]);
        $Country = trim($_GET["Country"]);
        $PortDestination = trim($_GET["PortDestination"]);
        $Product = trim($_GET["Product"]);
        $Variety = trim($_GET["Variety"]);
        $FromDate = trim($_GET["FromDate"]);
        $ToDate = trim($_GET["ToDate"]);

        $Status = trim($_POST["Status"]);
        $Grade = trim($_POST["Grade"]);
        $TypeProduct = trim($_POST["TypeProduct"]);
        $border = "border='1'";
    } else {
        $Year = trim($_POST["Year"]);
        $Consignee = trim($_POST["Consignee"]);
        $Country = trim($_POST["Country"]);
        $PortDestination = trim($_POST["PortDestination"]);
        $Product = trim($_POST["Product"]);
        $Variety = trim($_POST["Variety"]);
        $FromDate = trim($_POST["FromDate"]);
        $ToDate = trim($_POST["ToDate"]);

        $Status = trim($_POST["Status"]);
        $Grade = trim($_POST["Grade"]);
        $TypeProduct = trim($_POST["TypeProduct"]);

        $border = "";
    }

    $bodyContent = array(
        'ConsigneeId' => $Consignee,
        'CountryId' => $Country,
        'DestinationId' => $PortDestination,
        'ProductCode' => $Product,
        'Variety' => $Variety,
        'FromDate' => $FromDate,
        'ToDate' => $ToDate,
        'Year' => $Year,
        'Status' => $Status,
        'Grade' => $Grade,
        'TypeProduct' => $TypeProduct,
    );

    $resList = CallAPI($api, "POST", "", $bodyContent);
    // echo json_encode($resList, JSON_PRETTY_PRINT);
    // exit;

    $error = $resList->Error;

    //$ListTotal = $resList->Data->Totals;
    ?>
    <?php if (!isset($error) || empty($error)) {
        $List = $resList->Data;

        $content = '
        <div id="columnSelector" class="mb-2"></div>
        <table id="tblDataTable"  ' . $border . ' class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr style="height: 20px">
                    <th style="min-width:45px; max-width:60px;" class="sticky etaStatusCol1">S.No</th>
                    <th style="min-width:250px; max-width:300px;" class="sticky etaStatusCol1">Buyer</th>
                    <th style="min-width:200px; max-width:250px;" class="sticky etaStatusCol1">Po NO</th>
                    <th style="min-width:90px;">PO Date</th>
                    <th style="min-width:250px; max-width:250px;">Product</th>
                    <th style="min-width:90px;">Grade</th>
                    <th style="min-width:120px;">Packing</th>
                    <th style="min-width:150px; max-width:150px;">Packing Brand</th>
                    <th style="min-width:70px;">MCS</th>
                    <th style="min-width:150px; max-width:150px;">Invoice Price (DDP)</th>
                    <th style="min-width:130px;">Sale Invoice Price</th>
                    <th style="min-width:130px;">Reciprocal Tariff</th>

                    <th class="sticky1 etaStatusCol1" style="min-width:150px; max-width:150px;">Invoice Number</th>
                    <th style="min-width:200px; max-width:250px;" class="sticky1 etaStatusCol1">Invoice Line Number</th>
                    
                    
                    <th style="min-width:100px;">Container No</th>
                    <th style="min-width:150px; max-width:200px;">Final Destination</th>
                    <th style="min-width:85px;">Quantity</th>
                    <th style="min-width:65px;">UOM</th>
                     <th style="min-width:100px;">Value</th>

                    <th style="min-width:90px;">Shipment Date</th>
                    
                    <th style="min-width:100px;">Status</th>
                    <th style="min-width:200px; max-width:250px;">Remarks</th>';
        $content .= '</tr>
            </thead>
        <tbody>';
        foreach ($List as $item) {
            $content .= '
                <tr style="height: 23px">
                    <td style="min-width:45px; max-width:60px;" class="sticky etaStatusCol1">' . $item->SNO . '</td>
                    <td style="min-width:250px; max-width:300px;"  class="sticky etaStatusCol1">' . $item->CustomerName . '</td>
                     <td class="sticky etaStatusCol1" style="min-width:200px; max-width:250px;">' . $item->PurchaseOrderNumber . '</td>
                    <td style="min-width:90px;">' . $item->PurchaseOrderDate . '</td>
                    <td style="min-width:250px; max-width:250px;">' . $item->ProductDescription . '</td>
                    <td style="min-width:90px;">' . "" . $item->Grade . '</td>
                    <td style="min-width:120px;">' . $item->PackingStyle . '</td>
                    <td style="min-width:150px; max-width:150px;">' . $item->PackingBrand . '</td>
                    <td style="min-width:70px;">' . $item->NoofMasterCartons . '</td>';
                    if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

                        $content .= '<td style="min-width:150px; max-width:150px;">' . $item->DDPPrice . '</td>';
                    } else {

                       $content .= '<td style="min-width:150px; max-width:150px;">' . $item->DDPPrice . '</td>';

                        // $content .= '<td style="min-width:150px; max-width:150px;">
                        //         <input type="number" id="ddpPrice" name="DDPPrice[]" data-id="' . $item->PoGradeTableId . '" data-field="' . $item->PoGradeTableField . '" data-table="' . $item->PoGradeTable . '" value="' . $item->DDPPrice . '" class="form-control ddpPrice">
                        //     </td>';
                    }

                    $content .= '
                    <td style="min-width:130px;">' . $item->GrossUnitPrice . '</td>
                    <td style="min-width:130px;">' . $item->ReciprocalTariffs . '</td>
                    <td style="min-width:150px; max-width:150px;" class="sticky1 etaStatusCol1">' . $item->SaleInvoiceNumber . '</td>
                    <td style="min-width:200px; max-width:250px;"class="sticky1 etaStatusCol1">' . $item->SaleInvoiceLineNumber . '</td>
                    ';


            $content .= '
                    <td style="min-width:100px;">' . $item->ContainerNumber . '</td>
                    <td style="min-width:150px; max-width:200px;">' . $item->FinalDestination . '</td>
                    <td style="min-width:85px;">' . $item->Quantity . '</td> 
                     <td style="min-width:65px;">' . $item->QuantityUnitofMeasure . '</td>
                     <td style="min-width:100px;">' . $item->ValueUSD . '</td>

                    <td style="min-width:90px;">' . $item->StockOutDate . '</td>';
         
            

            $content .= '<td style="min-width:100px;">' . $item->ItemStatus . '</td>';

            if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
                $content .= '<td style="min-width:200px; max-width:250px;">' . $item->Remarks . '</td>';
            } else {
                $content .= '
                    <td style="min-width:200px; max-width:250px;">
                        <input type="text" data-id="' . $item->PoGradeTableId . '" data-field="' . $item->PoGradeTableField . '" data-table="' . $item->PoGradeTable . '"  id="remarks" name="Remarks[]" value="' . $item->Remarks . '" class="form-control remarks">
                    </td>';
            }
            $content .= '</tr>';
        }
        $content .= '
            </tbody>
        </table>';
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }

    if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

        $filename = 'Shipment_Line_Item_Wise_Price_Report.xls';
        // header("Content-Type: application/xls");
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment; filename=" . $filename . "");
    }

    $response = [
        "html" => $content,
        "filteredCountries" => $resList->FilteredCountries,
        "filteredPorts" => $resList->FilteredPorts
    ];

    echo json_encode($response);
}
?>
