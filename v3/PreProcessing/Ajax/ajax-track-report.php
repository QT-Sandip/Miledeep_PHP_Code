<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$mergeHonLoose = $ArrayClientConfigData['mergeHonLoose'];

$api = API_TRACK_REPORT;

$PageNo = trim($_POST["pageNo"]);

$bodyContent = array(
    "FromDate" => trim($_POST["fromDate"]),
    "ToDate" => trim($_POST["toDate"]),
    "ReceivingPlantID" => trim($_POST["plantId"]),
    //"Status" => "", 
    "PageNo" => trim($_POST["pageNo"]),
    "LoadAll" => trim($_POST["loadAll"]),
    "SearchKey" => trim($_POST["searchKey"]),
    "mergeHonLoose" => $mergeHonLoose
);

$resList = CallAPI($api, "GET", "", $bodyContent);

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $Data = $resList->Data;
        $Totals = $resList->Totals;
        ?>
        <table id="tblDataTable" data-paging="false" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width:50px;">S.No</th>
                    <th>Track Code</th>
                    <th>Arrival Date</th>
                    <th>Bill No</th>
                    <th>Count</th>
                    <th>Arrival Weight</th>
                    <th>Received Weight</th>
                    <th>Deheading Weight</th>
                    <th>Grading</th>
                    <th>Grading Yield</th>
                    <th>Freezing Qty F1 (Grading Out Qty)</th>
                    <th>Soaking Qty S1 (Grading Out Qty)</th>
                    <th>Value Addition Qty (Grading Out)</th>
                    <th>Value Addition In Qty</th>
                    <th>Value Addition Balance Qty</th>
                    <th>Value Addition Out Qty</th>
                    <th>Value Addition Yield(%)</th>
                    <th>Freezing Qty F2 (VA Out Qty)</th>
                    <th>Soaking Qty S2 (VA Out Qty)</th>
                    <th>Soaking In Qty (S1 + S2) </th>
                    <th>Soak IN (At Soaking In)</th>
                    <th>Soak In Balance Qty</th>
                    <th>Soak Out Qty S3</th>
                    <th>Pick Up Kgs</th>
                    <th>Pick Up Yield(%)</th>
                    <th>Freezing Qty (F1 + F2 + S3)</th>
                    <th>Freezing In Qty</th>
                    <th>Rejection Weight</th>
                    <th>Freezing Balance Qty</th>
                    <th>Freezing Yield(%)</th>
                    <th>Store In Qty</th>
                    <th>Store Inward Qty</th>
                    <th>Store Inward Balance Qty</th>
                    <th>Store Outward Quantity</th>
                    <th>Store Balance Quantity</th>

                    <!-- <th>Yield</th>
                    <th>Status</th> -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($Data as $item) { ?>
                    <tr>
                        <td style="min-width:50px;"><?php echo $item->SNo ?></td>
                        <td style="min-width: 130px;width: 130px;"><?php echo $item->TrackCode ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->ArrivalDate ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->BillNumber ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->Count ?></td>
                        <td class="arrivalWeight" style="min-width: 100px;width: 100px;"><?php echo str_replace(',','',$item->ArrivalWeight); ?></td>
                        <td class="receivedWeight" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->ReceivingWeight); ?></td>
                        <td class="deHeadingWeight" style="min-width: 100px;width: 100px;"><?php echo  str_replace(',', '', $item->DeheadingWeight) ?></td>
                        <td class="gradingWeight" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->GradingWeight); ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->GradingYield . ' %' ?></td>
                        <td class="freezingQtyF1" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->FreezingQtyF1); ?></td>
                        <td class="soakingInQtyS1" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->SoakingInQtyS1); ?></td>
                        <td class="vaGradingQty" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->VaGradingQty); ?></td>
                        <td class="valueAdditionInQty" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->ValueAdditionInQty); ?></td>
                        <td class="vABalanceQty" style="min-width: 100px;width: 100px; color:<?php echo str_replace(',', '', $item->ValueAdditionBalanceQtyColor); ?>;">
                            <?php echo $item->ValueAdditionBalanceQty ?>
                        </td>
                        <td class="vAOutQty" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->ValueAdditionOutQty); ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->ValueAdditionYield ?></td>
                        <td class="freezingQtyF2" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->FreezingQtyF2); ?></td>
                        <td class="soakingInQtyS2" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->SoakingInQtyS2); ?></td>
                        <td class="soakingInQtyS12" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->SoakingInQtyS12); ?></td>
                        <td class="soakInQty" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->SoakInQty); ?></td>
                        <td class="soakingBalanceQty" style="min-width: 100px;width: 100px; color:<?php echo str_replace(',', '', $item->SoakingBalanceQtyColor); ?>;">
                            <?php echo $item->SoakingBalanceQty ?>
                        </td>
                        <td class="soakOutQty" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->SoakOutQty); ?></td>
                        <td class="soakingPickUpQty" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->SoakingPickUpQty); ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->SoakingPickUpYield ?></td>
                        <td class="freezingQtyF2S3" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->FreezingQtyF2S3); ?></td>
                        <td class="freezingInQty" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->FreezingInQty); ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->RejectionWeight); ?></td>
                        <td class="frzBalanceQty" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->FreezingBalanceQty); ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->FreezingYield ?></td>
                        <td class="storeInQty" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->StoreInQty); ?></td>
                        <td class="storeInwardQty" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->StoreInwardQty); ?></td>
                        <td class="sInwdBalanceQty" style="min-width: 100px;width: 100px; color:<?php echo str_replace(',', '', $item->StoreInwardBalanceQtyColor); ?>;">
                            <?php echo str_replace(',', '', $item->StoreInwardBalanceQty); ?>
                        </td>
                        <td class="storeOutwardQty" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->StoreOutwardQty); ?></td>
                        <td class="storeBalanceQty" style="min-width: 100px;width: 100px;"><?php echo str_replace(',', '', $item->StoreBalanceQty); ?></td>

                        <!-- <td style="min-width: 100px;width: 100px;"><?php echo $item->Yield ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->Status ?></td> -->
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr style="font-weight: bold;">
                    <td style="min-width:50px;"></td>
                    <td style="min-width: 100px;width: 100px;">Totals</td>
                    <td style="min-width: 100px;width: 100px;"></td>
                    <td style="min-width: 120px;max-width: 120px;"></td>
                    <td style="min-width: 100px;max-width: 100px;"></td>
                    <td id= "TotalArvalWeight" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalArrivalWeight); ?></td>
                    <td id="TotalReceivedWeight" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalReceivingWeight); ?></td>
                    <td id="TotalDeHeadingWeight" style="min-width: 100px;max-width: 100px;"><?php echo $Totals->TotalDeheadingWeight ?></td>
                    <td id="TotalGradingWeight" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalGradingWeight); ?></td>
                    <td style="min-width: 100px;max-width: 100px;"></td>
                    <td id="TotalFreezingQty" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalFreezingQtyF1); ?></td>
                    <td id="TotalSoakingInQtyS1" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalSoakingInQtyS1); ?></td>
                    <td id="TotalVaGradingQty" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalVaGradingQty); ?></td>
                    <td id="TotalValueAdditionInQty" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalValueAdditionInQty); ?></td>
                    <td id="TotalVABalanceQty" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalValueAdditionBalanceQty); ?></td>
                    <td id="TotalVAOutQty" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalValueAdditionOutQty); ?></td>
                    <td style="min-width: 100px;max-width: 100px;"></td>
                    <td id="TotalFreezingQtyF2" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalFreezingQtyF2); ?></td>
                    <td id="TotalSoakingInQtyS2" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalSoakingInQtyS2); ?></td>
                    <td id="TotalSoakingInQtyS12" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalSoakingInQtyS12); ?></td>
                    <td id="TotalSoakInQty" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalSoakInQty); ?></td>
                    <td id="TotalSoakingBalanceQty" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalSoakingBalanceQty); ?></td>
                    <td id="TotalSoakOutQty" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalSoakOutQty); ?></td>
                    <td id="TotalSoakingPickUpQty" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalSoakingPickUpQty); ?></td>
                    <td style="min-width: 100px;max-width: 100px;"></td>
                    <td id="TotalFreezingQtyF2S3" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalFreezingQtyF2S3); ?></td>
                    <td id="TotalFreezingInQty" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalFreezingInQty); ?></td>
                    <td style="min-width: 100px;max-width: 100px;"></td>
                    <td id="TotalFrzBalanceQty" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalFreezingBalanceQty); ?></td>
                    <td style="min-width: 100px;max-width: 100px;"></td>
                    <td id="TotalStoreInQty" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalStoreInQty); ?></td>
                    <td id="TotalStoreInwardQty" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalStoreInwardQty); ?></td>
                    <td id="TotalSInwdBalanceQty" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalStoreInwardBalanceQty) ?></td>
                    <td id="TotalStoreOutwardQty" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalStoreOutwardQty); ?></td>
                    <td id="TotalStoreBalanceQty" style="min-width: 100px;max-width: 100px;"><?php echo str_replace(',', '', $Totals->TotalStoreBalanceQty); ?></td>

                    <!-- <td style="min-width: 100px;max-width: 100px;"></td>
                    <td style="min-width: 100px;max-width: 100px;"></td> -->
                </tr>
            </tfoot>
        </table>
        <div class="mt-3 mb-3">Showing 1 to <?php echo count($Data) ?> out of <?php echo count($Data) ?> entries</div>
        <?php $activepagestyle = "border:2px solid #74788d" ?>
        <div>
            <input type='hidden' id='hdnTotalRecords' value='<?php echo $Data->TotalCount ?>' />
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
        echo "<div class='text-center mt-3 mb-3 text-bold'>Note: " . $error . "</div>";
    }
}
?>