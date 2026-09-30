<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_CONTAINER_RETURN_REPORT;

$bodyContent = array(

    'ExporterID' => trim($_POST["exporterID"]),
    'CountryID' => trim($_POST["countryID"]),
    'Year' => trim($_POST["year"]),
    'ConsigneeID' => trim($_POST["consigneeID"]),
    'PageNo' => trim($_POST["pageNo"]),
    'SearchKey' => trim($_POST["searchKey"]),
    'LoadAll' => trim($_POST["loadAll"])
);
$resCustom = CallAPI($api, "GET", "", $bodyContent);

// echo json_encode($resCustom, JSON_PRETTY_PRINT);
// exit;


if (isset($resCustom) && !empty($resCustom)) {

    $error = $resCustom->Error;

    if (!isset($error) || empty($error)) {

        $PageCount = $resCustom->PageCount;
        $resList = $resCustom->ContainerReturnData;
        $resTotals = $resCustom->Totals;
        // var_dump($resTotals);
        // exit;

?>

        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width:50px;">SNO</th>
                    <th>Invoice No</th>
                    <th>Invoice date</th>
                    <th>Po No</th>
                    <th>Po Date</th>
                    <th>Stock Out Date</th>
                    <th>Consignee</th>
                    <th>CY/SD</th>
                    <th>Terms of Delivery</th>
                    <th>Port of loading</th>
                    <th>Country</th>
                    <th>Port of Discharge</th>
                    <th>Final Destination</th>
                    <th>Shipping Bill No</th>
                    <th>Shipping Bill Date</th>
                    <th>BL.NO</th>
                    <th>BL.Date</th>
                    <th>Container No</th>
                    <th>Quantity Net weight KGs</th>
                    <th>Quantity Net weight LBs</th>
                    <th>Invoice Value $</th>
                    <th>FOB Value</th>
                    <th>Freight</th>

                    <th>Return Container stock Received Date</th>
                    <th>Return Bill No</th>
                    <th>Return Container No</th>
                    <th>Return Container Net Wt KGs</th>
                    <th>Container Return Port</th>
                    <th>Bill of Entry Number</th>
                    <th>Bill of Entry Date</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resList as $item) { ?>
                    <tr>
                        <td><?php echo $item->sno ?></td>
                        <td><?php echo $item->InvNo ?></td>
                        <td style="min-width:100px ;"><?php echo $item->InvoiceDate ?></td>
                        <td><?php echo $item->PoNo ?></td>
                        <td style="min-width:100px ;"><?php echo $item->PoDate ?></td>
                        <td style="min-width:100px ;"><?php echo $item->StockOutDate ?></td>
                        <td><?php echo $item->Consignee ?></td>
                        <td><?php echo $item->CYSD ?></td>
                        <td><?php echo $item->TermsofDelivery ?></td>
                        <td><?php echo $item->PortofLoading ?></td>
                        <td><?php echo $item->Country ?></td>
                        <td><?php echo $item->PortOfDischarge ?></td>
                        <td><?php echo $item->FinalDestination ?></td>
                        <td><?php echo $item->ShippingBillNo ?></td>
                        <td style="min-width:100px ;"><?php echo $item->ShippingBillDate ?></td>
                        <td><?php echo $item->BLNo ?></td>
                        <td style="min-width:100px ;"><?php echo $item->BLDate ?></td>
                        <td><?php echo $item->Containerno ?></td>
                        <td><?php echo $item->QuantityNetWeightKgs ?></td>
                        <td><?php echo $item->QuantityNetWeightLbs ?></td>
                        <td><?php echo $item->InvoiceValueUSD ?></td>
                        <td><?php echo $item->FOBValue ?></td>
                        <td><?php echo $item->Freight ?></td>

                        <td style="min-width:100px ;"><?php echo $item->ReturnContainerStockReceivedDate ?></td>
                        <td><?php echo $item->ReturnBLNo ?></td>
                        <td><?php echo $item->ReturnContainerNo ?></td>
                        <td><?php echo $item->ReturnContainerNetWeightKgs ?></td>
                        <td><?php echo $item->ContainerReturnPort ?></td>
                        <td><?php echo $item->BillofEntryNo ?></td>
                        <td style="min-width:100px ;"><?php echo $item->BillofEntryDate ?></td>
                        <td style="min-width: 120px;"><?php echo $item->FDAContainerStatus ?></td>
                        <td style="min-width: 400px;"><?php echo $item->ReturnRemarks ?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <?php // foreach ($resTotals as $item) { 
                    ?>
                    <td></td>
                    <td>Grand Totals</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $resTotals->TotalNetWeightKgs ?></td>
                    <td><?php echo $resTotals->TotalNetWeightLbs ?></td>
                    <td><?php echo $resTotals->TotalInvoiceValueUSD ?></td>
                    <td><?php echo $resTotals->TotalFOBValueUSD ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <?php // } 
                    ?>
                </tr>
            </tfoot>
        </table>
        <div class="mt-3 mb-3">Showing 1 to
            <?php echo count($resList) ?> out of
            <!-- <?php echo $resCustom->TotalCount ?> entries -->
            <?php echo count($resList) ?> entries
        </div>
        <div>
            <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resCustom->TotalCount ?>' /> -->
            <ul class="pagination pagination-rounded justify-content-center">
                <?php
                for ($i = 1; $i <= $PageCount; $i++) {
                    $pageActive = ($PageNo == $i ? "active" : "");
                    echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                }
                ?>
            </ul>
        </div>
<?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
?>