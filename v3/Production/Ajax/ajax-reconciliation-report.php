<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API__RECONCILIATION_REPORT;

$bodyContent = array(
    "FromDate" => trim($_POST["fromDate"]),
    "ToDate" => trim($_POST["toDate"]),
);

$resList = CallAPI($api, "GET", "", $bodyContent);
//echo ',pre>';print_r($resList);die;
if (isset($resList) && !empty($resList)) {
    $error = $resList->Error;

    if (!isset($error) || empty($error)) {

        $ReconsolationData = $resList->ReconsolationData;
        ?>
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width: 100px;width: 100px;">Plant</th>
                    <th style="min-width: 100px;width: 100px;">Species</th>
                    <th style="min-width: 100px;width: 100px;">Head On Raw Material Qty Kgs</th>
                    <th style="min-width: 100px;width: 100px;">Rm Procured HL Kg</th>
                    <th style="min-width: 100px;width: 100px;">Head On Farm Raw Material Qty Kgs</th>
                    <th style="min-width: 100px;width: 100px;">Rm Procured Farm HL Kg  </th>
                    <th style="min-width: 100px;width: 100px;">Total Rm Procured HL Kg (Raw)</th>
                    <th style="min-width: 100px;width: 100px;">Rm Consumed HL Kg ( Prod)</th>
                    
                    <th style="min-width: 100px;width: 100px;">Qty Variance Kg</th>
                    <th style="min-width: 100px;width: 100px;">Variance %</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ReconsolationData as $item) { ?>
                    <!-- Display Plant Data -->
                    <tr>
                        <td style="min-width: 100px;"><?php echo $item->Plant ?></td>
                        <td style="padding: 0px !important;" colspan="9">
                            <table class="subTable table table-bordered mb-0" style="width: 100%">
                                <!-- Display BreakUp Data -->
                                <?php foreach ($item->BreakUpData as $itemBreak) { 
                                    $VariancePercentage = $itemBreak->VariancePercentage * 100;
                                    $fontColor = 'green';
                                    if($VariancePercentage > 2){
                                        $fontColor = 'red';
                                    }
                                    ?>
                                    <tr>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->Species ?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->HeadOnRawMeterialQty ?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->RMProcuredHLKg ?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->HeadOnFarmRawMeterialQty ?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->FarmHLkg ?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->totalRmProcured;?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->RMConsumedHLkg ?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->Variancekg ?></td>
                                        <td style="min-width: 100px;width: 100px;color:<?php echo $fontColor;?>"><?php echo $VariancePercentage.'%' ?></td>
                                    </tr>
                                <?php } ?>
                                <!-- Display Plant Wise Totals -->
                                <tr style="font-weight: bold;">
                                    <td colspan="1">Totals</td>
                                    <td style="min-width: 100px;width: 100px;">
                                         <?php echo $item->PlantWiseWeightTotals->TotalHeadOnRawMeterialQty ?>
                                    </td>
                                    <td style="min-width: 100px;width: 100px;">
                                        <?php echo $item->PlantWiseWeightTotals->TotalRMProcuredHLKg ?>
                                    </td>
                                    <td style="min-width: 100px;width: 100px;">
                                        <?php echo $item->PlantWiseWeightTotals->TotalHeadOnFarmRawMeterialQty ?>
                                    </td>
                                    <td style="min-width: 100px;width: 100px;">
                                        <?php echo $item->PlantWiseWeightTotals->TotalFarmHLkg ?>
                                    </td>
                                    <td style="min-width: 100px;width: 100px;">
                                        <?php echo $item->PlantWiseWeightTotals->GrandTotalRmProcured; ?>
                                    </td>
                                    <td style="min-width: 100px;width: 100px;">
                                        <?php echo $item->PlantWiseWeightTotals->TotalRMConsumedHLkg ?>
                                    </td>
                                    <td style="min-width: 100px;width: 100px;">
                                        <?php echo $item->PlantWiseWeightTotals->TotalVariancekg ?>
                                    </td>
                                    <?php 
                                    $TotalVariancePercentage = $item->PlantWiseWeightTotals->TotalVariancePercentage * 100;
                                    $fontColor = 'green';
                                    if($TotalVariancePercentage > 1){
                                        $fontColor = 'red';
                                    }?>
                                    <td style="min-width: 100px;width: 100px;color:<?php echo $fontColor;?>">
                                        <?php echo $TotalVariancePercentage.'%' ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . htmlspecialchars($error) . "</div>";
    }
}
?>