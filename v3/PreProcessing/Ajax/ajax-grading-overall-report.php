<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$gradOverallReportViewChg = $ArrayClientConfigData['gradOverallReportViewChg'];
$mergeHonLoose = $ArrayClientConfigData['mergeHonLoose'];

$api = API_GRADING_OVERALL_REPORT;
if(!empty($_POST["type"]) && $_POST["type"] == 'getGradingMachines'){
    $resList = CallAPI($api, "GET", "", $_POST);
    echo json_encode($resList);die;
}

$bodyContent = array(
    // 'ArrivalDate' => trim($_POST["arrivalDate"]),
    'FromDate' => trim($_POST["fromDate"]),
    'ToDate' => trim($_POST["toDate"]),
    'ReceivingPlantID' => trim($_POST["receivingPlantId"]),
    'MachineID' => trim($_POST["machineID"]),
    'IsPlantFilter' => trim($_POST["IsPlantFilter"]),
    'TypeProduct' => trim($_POST["typeProduct"]),
    'Grade' => trim($_POST["grade"]),
    'MergeHonLoose' => $mergeHonLoose,
    'GradingDate' => trim($_POST["GradingDate"]),
);

$resList = CallAPI($api, "GET", "", $bodyContent);

// echo json_encode($resList->GrandTotals);exit;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->Data;
        $CombinationData = $resList->VarietyCombinationData;
        $GradeTotals = $resList->VarietyCombinationWeightTrackwise;
        $Totals = $resList->GrandTotals[0];

        if ($gradOverallReportViewChg == 1) {
            $comboCount = count($CombinationData);
?>


            <style>
                .table-sticky {
                    table-layout: fixed;
                    width: max-content;
                    border-collapse: collapse;
                }

                .table-sticky th,
                .table-sticky td {
                    white-space: nowrap;
                    min-width: 50px;
                }

                .table-sticky thead th {
                    position: sticky;
                    top: 0;
                    background: #fff;
                    z-index: 3;
                }

                .sticky {
                    position: sticky;
                    background: #fff;
                    z-index: 4;
                }

                .stickyCol1 {
                    left: 0px;
                }

                .stickyCol2 {
                    left: 50px;
                }

                .stickyCol3 {
                    left: 100px;
                }

                .stickyCol4 {
                    left: 150px;
                }

                .stickyCol5 {
                    left: 200px;
                }
            </style>

            <div style="overflow-x: auto;">
                <table id="tblDataTable" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <!-- Variety Header Row -->


                        <!-- Treatment Header Row -->


                        <!-- Column Titles -->
                        <tr>
                            <th>SNO</th>
                            <th>Track Code</th>
                            <th>Grading Date</th>
                            <th>Batch Code</th>
                            <th>Variety</th>
                            <th>Grade</th>
                            <th>Count</th>
                            <th>Treatment</th>
                            <th>Weight</th>
                            <th>Total</th>
                            <th>Yield</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $k = 0;
                        foreach ($List as $j => $item6) {
                            foreach ($item6->BillData as $i => $fbilldata) {
                                $firstRow = true;
                        ?>



                                <?php foreach ($CombinationData as $combo) {
                                    if ((isset($combo->HLWeightData[$k]->Weight) && $combo->HLWeightData[$k]->Weight > 0)) { ?>
                                        <tr>
                                            <td><?php echo $k + 1; ?></td>
                                            <td><?php echo isset($item6->TrackCode) ? $item6->TrackCode : ''; ?></td>
                                            <td><?php echo isset($item6->GradingDate) ? $item6->GradingDate : ''; ?></td>
                                            <td><?php echo isset($item6->GradingDate) ? $item6->GradingDate . "<br>" . isset($fbilldata->BillNumber) ? $fbilldata->BillNumber : '' : ''; ?></td>
                                            <? php ?>
                                            <td><?php echo isset($combo->ShortName) ? $combo->ShortName : "" ?></td>
                                            <td><?php echo isset($combo->PackingGrade) ? $combo->PackingGrade : "" ?></td>
                                            <td><?php echo isset($combo->HLCount) ? $combo->HLCount : "" ?></td>
                                            <td><?php echo isset($combo->Treatment) ? $combo->Treatment : "" ?></td>
                                            <td><?php echo isset($combo->HLWeightData[$k]->Weight) && $combo->HLWeightData[$k]->Weight > 0 ? $combo->HLWeightData[$k]->Weight : "" ?></td>
                                            <td><?php echo isset($GradeTotals[$k]->HlWeight) ? $GradeTotals[$k]->HlWeight : '0'; ?></td>
                                            <td><?php echo isset($GradeTotals[$k]->Yield) ? $GradeTotals[$k]->Yield . '%' : '0%'; ?></td>
                                        </tr>
                                <?php  }
                                } ?>






                        <?php
                                $k++;
                            }
                        } ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>
                                <?php echo 'HOn: ' . $Totals->HonWeight . '<br>HL: ' . $Totals->HLWeight; ?>
                            </td>
                            <td><?php echo 'Grand Yield: ' . $Totals->Yield . ' %'; ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-3 mb-3">Showing 1 to <?php echo count($CombinationData) ?> out of <?php echo count($CombinationData) ?> entries</div>

        <?php } else { ?>
            <style>
                .table-sticky {
                    table-layout: fixed;
                    width: max-content;
                    border-collapse: collapse;
                }

                .table-sticky th,
                .table-sticky td {
                    white-space: nowrap;
                    min-width: 50px;
                }

                .table-sticky thead th {
                    position: sticky;
                    top: 0;
                    background: #fff;
                    z-index: 3;
                }

                .sticky {
                    position: sticky;
                    background: #fff;
                    z-index: 4;
                }

                .stickyCol1 {
                    left: 0px;
                }

                .stickyCol2 {
                    left: 50px;
                }

                .stickyCol3 {
                    left: 100px;
                }

                .stickyCol4 {
                    left: 150px;
                }

                .stickyCol5 {
                    left: 200px;
                }


                .footer-bold {
                    font-family: 'Arial Black';
                    /* Bold font */
                    font-weight: 700;
                   
                    color: #000;
                    /* default black */
                    text-align: center;
                    vertical-align: middle;
                    font-size: 12px;

                }
            </style>

            <div style="overflow-x: auto;">
                <table id="tblDataTable" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <!-- <tr>
                        <th class=""></th>
                        <th class=""></th>
                        <th colspan="2" class="">Track Code</th>
                        <th class=""></th>
                        <?php foreach ($List as $item) { ?>
                            <th colspan="<?php echo count($item->BillData) ?>"><?php echo $item->TrackCode ?></th>
                        <?php } ?>
                        <th></th>
                    </tr>
                    <tr>
                        <th class=""></th>
                        <th class=""></th>
                        <th colspan="2" class="">Grading Machine</th>
                        <th class=""></th>
                        <?php foreach ($List as $item) { ?>
                            <th colspan="<?php echo count($item->BillData) ?>"><?php echo $item->MachineName ?></th>
                        <?php } ?>
                        <th></th>
                    </tr>
                    <tr>
                        <th class=""></th>
                        <th class=""></th>
                        <th colspan="2" class="">Grading Date</th>
                        <th class=""></th>
                        <?php foreach ($List as $item) { ?>
                            <th colspan="<?php echo count($item->BillData) ?>"><?php echo $item->GradingDate ?></th>
                        <?php } ?>
                        <th></th>
                    </tr> -->
                        <tr>
                            <th style="width: 50px; vertical-align: middle; text-align: center;" class="">SNO</th>
                            <th style="vertical-align: middle; text-align: center;" class="">Variety</th>
                            <th style="vertical-align: middle; text-align: center;" class="">Grade</th>
                            <th style="vertical-align: middle; text-align: center;" class="">Count</th>
                            <th style="vertical-align: middle; text-align: center;" class="">Treatment</th>
                            <?php foreach ($List as $item) { ?>
                                <?php foreach ($item->BillData as $billItem) { ?>
                                    <th style="vertical-align: middle; text-align: left; min-width: 250px; width: 250px;">
                                        <b><?php echo 'Track Code : ' . $item->TrackCode . '<br>' . str_replace(',', '<br>', $billItem->BillNumber); ?></b>

                                    </th>
                                <?php } ?>
                            <?php } ?>


                            <th style="vertical-align: middle; text-align: center; min-width: 100px;">Totals</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($CombinationData as $item) { ?>
                            <tr>
                                <td style="min-width: 50px" class=""><?php echo $item->SNO ?></td>
                                <td style="min-width: 100px" class=""><?php echo $item->ShortName ?></td>
                                <td style="min-width: 100px" class=""><?php echo $item->PackingGrade ?></td>
                                <td style="min-width: 100px" class=""><?php echo $item->HLCount ?></td>
                                <td style="min-width: 120px" class=""><?php echo $item->Treatment ?></td>
                                <?php foreach ($item->HLWeightData as $ItemWeight) { ?>
                                    <td><?php echo number_format($ItemWeight->Weight ? $ItemWeight->Weight : '0', 3); ?></td>
                                <?php } ?>
                                <td><?php echo number_format($item->TotalHLWeight, 3); ?></td>
                            </tr>
                        <?php } ?>
                        <!--  </tbody>
                <tfoot> -->
                        <tr class='footer-bold'>
                            <td style="width: 50px; vertical-align: middle; text-align: center;" class=""></td>
                            <td style="vertical-align: middle; text-align: center;" class="">Totals</td>
                            <td style="vertical-align: middle; text-align: center;" class=""></td>
                            <td style="vertical-align: middle; text-align: center;" class=""></td>
                            <td style="vertical-align: middle; text-align: center;" class=""></td>
                            <?php foreach ($GradeTotals as $total) { ?>
                                <td style="min-width: 100px;"><?php echo 'HOn : ' . $total->HonWeight ?> <br> <?php echo 'HL : ' . $total->HlWeight ?></td>
                            <?php } ?>
                            <td style="min-width: 100px;"><?php echo 'HOn : ' . $Totals->HonWeight ?> <br> <?php echo 'HL : ' . $Totals->HLWeight ?></td>
                        </tr>
                        <tr class='footer-bold'>
                            <td style="width: 50px; vertical-align: middle; text-align: center;"></td>
                            <td style="vertical-align: middle; text-align: center;">Yield</td>
                            <td style="vertical-align: middle; text-align: center;"></td>
                            <td style="vertical-align: middle; text-align: center;"></td>
                            <td style="vertical-align: middle; text-align: center;"></td>
                            <!-- <?php foreach ($GradeTotals as $total) { ?>
                                <td style="<?php echo ($total->Yield < 69 ? 'color:red;' : ''); ?>"><?php echo $total->Yield ? $total->Yield . '%' : '0' ?></td>
                            <?php } ?> -->

                             <?php foreach ($GradeTotals as $index => $total) { 
                            $species = '';

                            if (isset($total->Species)) {
                                $species = strtoupper(trim($total->Species));
                            } 

                            // Apply color rule
                            $colorStyle = '';
                            if (($species === 'VM' && $total->Yield < 69) || ($species === 'BT' && $total->Yield < 63)) {
                                $colorStyle = 'color:red; font-weight:700;';
                            }
                        ?>
                            <td style="<?php echo $colorStyle; ?>">
                                <?php echo $total->Yield ? $total->Yield . '%' : '0'; ?>
                            </td>
                        <?php } ?>

                        
                            <td style="min-width: 100px;"> <?php echo 'Grand Yield : ' . $Totals->Yield . ' %' ?> </td>
                        </tr>
                        <tr class='footer-bold'>
                            <td style="width: 50px; vertical-align: middle; text-align: center;"></td>
                            <td style="vertical-align: middle; text-align: center;">Type Product</td>
                            <td style="vertical-align: middle; text-align: center;"></td>
                            <td style="vertical-align: middle; text-align: center;"></td>
                            <td style="vertical-align: middle; text-align: center;"></td>
                            <?php foreach ($GradeTotals as $total) { ?>
                                <td></td>
                            <?php } ?>
                            <td style="min-width: 100px;"> <?php echo 'RAW: ' . $Totals->raw . '<br>COOKED: ' . $Totals->cooked . '<br>OTHERS: ' . $Totals->others; ?> </td>
                        </tr>
                        </tfoot>
                </table>
            </div>
        <?php } ?>
<?php
    } else {
        echo "Error: " . $error;
    }
} else {
    echo "<div class='alert alert-danger'>No data found. Please check your request parameters.</div>";
}
?>
