<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_CONSOLIDATION_REPORT;

$bodyContent = array(
    'FromDate' => trim($_POST["fromDate"]),
    'ToDate' => trim($_POST["toDate"]),
    'ReceivingPlantID' => trim($_POST["receivingPlantId"])
);

$resList = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $ArrayCountGroupData = $resList->ArrayCountGroupData->CountGroupData;
        $TotalCountGroupWeight = $resList->ArrayCountGroupData->TotalCountGroupWeight;
        $ArrayPurchaseData = $resList->ArrayPurchaseData->PurchaseData;
        $GradewiseData = $resList->GradewiseData;
        $VarietyData = $resList->VarietyData;
        $TotalsData = $resList->TotalsData;
        $ResultData = $resList->ResultData;
        $GrandTotals = $resList->GrandTotals;
        //         if(!empty($VarietyData)){
//   $resList->Error = "data not found";
//         var_dump($resList->Error);
//         }

        ?>

        <!-- <?php //if (!isset($error) || empty($error)) { ?> -->
        <?php if (isset($VarietyData)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th rowspan="2">SNO</th>
                        <th rowspan="2">Count</th>
                        <th rowspan="2">Weight</th>
                        <!-- <th rowspan="2">Purchase Count</th> -->
                        <!-- <th rowspan="2">Purchase Weight</th> -->
                        <th rowspan="2" style="min-width: 100px;">Grade</th>
                        <th rowspan="2" style="min-width: 100px;">Weight</th>
                        <?php foreach ($VarietyData as $ItemVariety) { ?>
                            <!-- <th colspan="3"><?php //echo $ItemVariety->Species . ' ' . $ItemVariety->ShortName; ?></th> -->
                            <th colspan="3">
                                <?php echo $ItemVariety->ProductionSpecies . '-' . $ItemVariety->ProductionVarity . '( ' . $ItemVariety->ProductionTypeProduct . ' - ' . $ItemVariety->ProductionFreezingType . ' ) ' . $ItemVariety->ProductionTreatment . '<br> ' . $ItemVariety->PackingStyle; ?>
                            </th>
                        <?php } ?>
                    </tr>
                    <tr>
                        <?php foreach ($VarietyData as $ItemVariety) { ?>
                            <th>HL Graded Weight</th>
                            <th>Finished Production</th>
                            <th>Difference</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($TotalCountGroupWeight != 0) {
                        // $rowCount = max(count($ArrayCountGroupData), count($ArrayPurchaseData), count($GradewiseData));
                        $rowCount = max(count($ArrayCountGroupData), count($GradewiseData));

                        for ($i = 0; $i < $rowCount; $i++) {
                            $ItemCountGroupData = isset($ArrayCountGroupData[$i]) ? $ArrayCountGroupData[$i] : null;
                            // $ItemPurchaseData = isset($ArrayPurchaseData[$i]) ? $ArrayPurchaseData[$i] : null;
                            $ItemGradeData = isset($GradewiseData[$i]) ? $GradewiseData[$i] : null;
                            ?>
                            <tr>
                                <td><?php echo isset($ItemCountGroupData->SNO) ? $ItemCountGroupData->SNO : ''; ?></td>
                                <td><?php echo isset($ItemCountGroupData->BaseCount) ? $ItemCountGroupData->BaseCount : ''; ?></td>
                                <td><?php echo isset($ItemCountGroupData->Weight) ? $ItemCountGroupData->Weight : ''; ?></td>
                                <!-- <td><?php echo isset($ItemPurchaseData->Count) ? $ItemPurchaseData->Count : ''; ?></td> -->
                                <!-- <td><?php echo isset($ItemPurchaseData->Weight) ? $ItemPurchaseData->Weight : ''; ?></td> -->
                                <td style="min-width: 100px;">
                                    <?php echo isset($ItemGradeData->GradingGrade) ? $ItemGradeData->GradingGrade : ''; ?>
                                </td>
                                <td style="min-width: 100px;">
                                    <?php echo isset($ItemGradeData->GradingWeight) ? $ItemGradeData->GradingWeight : ''; ?>
                                </td>
                                <?php if (isset($ItemGradeData) && isset($ItemGradeData->ConsolidationData)) {
                                    foreach ($ItemGradeData->ConsolidationData as $ConsolidData) { ?>
                                        <td><?php echo number_format($ConsolidData->HLWeight, 3); ?></td>
                                        <!-- <td><?php echo number_format($ConsolidData->ProductionWeight, 3); ?></td> -->
                                        <td><?php echo $ConsolidData->ProductionWeight; ?></td>
                                        <td><?php echo number_format($ConsolidData->Difference, 3); ?></td>
                                    <?php }
                                } else {
                                    foreach ($VarietyData as $variety) { ?>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                    <?php }
                                } ?>
                            </tr>
                        <?php } ?>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>Rejection Weight</th>
                            <th></th>
                            <?php foreach ($VarietyData as $ItemVariety) { ?>
                            <th>0.000</th>
                            <th><?php echo $ItemVariety->RejectionWeight?></th>
                            <th><?php echo -$ItemVariety->RejectionWeight?></th>
                        <?php } ?>
                        </tr>
                        <tr>
                            <th></th>
                            <th>Totals</th>
                            <th><?php echo $TotalCountGroupWeight ?></th>
                            <th></th>
                            <th></th>
                            <?php foreach ($TotalsData as $ItemTotals) { ?>
                                <th><?php echo number_format($ItemTotals->HLWeight, 3) ?></th>
                                <th><?php echo number_format($ItemTotals->ProductionWeight, 3) ?></th>
                                <th><?php echo number_format($ItemTotals->Difference, 3) ?></th>
                            <?php } ?>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <?php foreach ($ResultData as $ItemData) { ?>
                                <td></td>
                                <td><?php echo $ItemData->DiffernceYield . '%'; ?></td>

                                <td></td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="min-width: 100px; text-align: left;"> HL Weight : </td>
                            <td style="min-width: 100px; text-align: right;"><?php echo number_format($GrandTotals->TotalHLWeight, 3) ?>
                            </td>
                            <?php foreach ($ResultData as $ItemData) { ?>
                                <td></td>
                                <td></td>
                                <td></td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="min-width: 100px; text-align: left;">Production Weight: </td>
                            <td style="min-width: 100px; text-align: right;">
                                <?php echo number_format($GrandTotals->TotalProductionWeight, 3) ?>
                            </td>
                            <?php foreach ($ResultData as $ItemData) { ?>
                                <td></td>
                                <td></td>
                                <td></td>
                            <?php } ?>
                        </tr>
                         <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="min-width: 100px; text-align: left;">Rejection Weight: </td>
                            <td style="min-width: 100px; text-align: right;">
                                <?php echo number_format($GrandTotals->TotalRejection, 3) ?>
                            </td>
                            <?php foreach ($ResultData as $ItemData) { ?>
                                <td></td>
                                <td></td>
                                <td></td>
                            <?php } ?>
                        </tr>
                         <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="min-width: 100px; text-align: left;">Finished Production: </td>
                            <td style="min-width: 100px; text-align: right;">
                                <?php echo number_format($GrandTotals->TotalWeight, 3) ?>
                            </td>
                            <?php foreach ($ResultData as $ItemData) { ?>
                                <td></td>
                                <td></td>
                                <td></td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="min-width: 100px; text-align: left;">Difference :</td>
                            <td style="min-width: 100px; text-align: right;">
                                <?php echo number_format($GrandTotals->TotalDifference, 3) ?>
                            </td>
                            <?php foreach ($ResultData as $ItemData) { ?>
                                <td></td>
                                <td></td>
                                <td></td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Purchase QTY</td>
                            <td><?php echo number_format($TotalCountGroupWeight, 3) ?></td>
                            <?php foreach ($ResultData as $ItemData) { ?>
                                <td></td>
                                <td></td>
                                <td></td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>HL Total QTY</td>
                            <td><?php echo number_format($GrandTotals->TotalHLWeight, 3) ?></td>
                            <?php foreach ($ResultData as $ItemData) { ?>
                                <td></td>
                                <td></td>
                                <td></td>
                            <?php } ?>
                        </tr>
                        <?php
                        $TotalHLWeight = $GrandTotals->TotalHLWeight;
                        $TotalYield = ($TotalHLWeight / $TotalCountGroupWeight) * 100;
                        ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Yield</td>
                            <td><?php echo round($TotalYield, 2) . '%' ?></td>
                            <?php foreach ($ResultData as $ItemData) { ?>
                                <td></td>
                                <td></td>
                                <td></td>
                            <?php }
                            ?>
                        </tr>
                    </tbody><?php } ?>
                </tbody>
            </table>
        <?php } else {
            $resList->Error = "data not found";

            echo "<div class='text-center mt-3 mb-3 text-bold'>Note : " . $resList->Error . "</div>";
        }
    } else {

        echo "<div class='text-center mt-3 mb-3 text-bold'>Note : " . $resList->Error . "</div>";
    }
}
?>