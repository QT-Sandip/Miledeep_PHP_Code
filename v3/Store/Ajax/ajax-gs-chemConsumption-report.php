<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_GS_CHEM_CONSUMPSTION_LIST;

$Chemical = trim($_POST["chemical"]);
$PlantID = trim($_POST["proPlant"]);
$FromDate = trim($_POST["fromDate"]);
$ToDate = trim($_POST["toDate"]);

$bodyContent = array(
    'FromDate' => $FromDate,
    'ToDate' => $ToDate,
    'ChemicalID' => $Chemical,
    'PlantID' => $PlantID,

);

$resList = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($resList);die;
// echo json_encode($resList, JSON_PRETTY_PRINT);

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->Data;
?>
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="width: 25px;">S.NO</th>
                    <th>Date</th>
                    <th>Chemical</th>
                    <th>Received From</th>
                    <th>Received Qty In bags</th>
                    <th>Received Qty In Kgs</th>
                    <th style="width: 100px;">Sended To</th>
                    <th>Sended In Bags</th>
                    <th>Sended In Kgs</th>
                    <th>Used In Bags</th>
                    <th>Used In KGs</th>
                    <th>Opening In Bags</th>
                    <th>Opening In Kgs</th>
                    <th>Closing in Bags</th>
                    <th>Closing in KGs</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $ReceivedBagsTotals = $QuantityReceivedTotals = $SendBagsTotals = $SendQtyTotals = $BagsUsedTotals = 0;
                    $QuantityUsedTotals = $OpeningBagsTotals = $OpeningKGsTotals = $ClosingBagsTotals = $ClosingKGsTotals = 0;
                    foreach ($List as $item) { 
                        $ReceivedBagsTotals += $item->ReceivedBags;
                        $QuantityReceivedTotals += $item->QuantityReceived;
                        $SendBagsTotals += $item->SendBags;
                        $SendQtyTotals += $item->SendQty;
                        $BagsUsedTotals += $item->BagsUsed;
                        $QuantityUsedTotals += $item->QuantityUsed;
                        $OpeningBagsTotals += $item->OpeningBags;
                        $OpeningKGsTotals += $item->OpeningKGs;
                        $ClosingBagsTotals += $item->ClosingBags;
                        $ClosingKGsTotals += $item->ClosingKGs;
                        ?>
                    <tr>
                        <td><?php echo $item->SNO ?></td>
                        <td style="width: 80px"><?php echo date('d-m-Y', strtotime($item->EntryDate)) ?></td>
                        <td><?php echo $item->ChemicalName ?></td>
                        <td><?php echo $item->StationName ?></td>
                        <td class="ReceivedBags"><?php echo $item->ReceivedBags ?></td>
                        <td class="QuantityReceived"><?php echo $item->QuantityReceived ?></td>
                        <td><?php echo $item->TransferPlant ?></td>
                        <td class="SendBags"><?php echo $item->SendBags ?></td>
                        <td class="SendQty"><?php echo $item->SendQty ?></td>
                        <td class="BagsUsed"><?php echo $item->BagsUsed ?></td>
                        <td class="QuantityUsed"><?php echo $item->QuantityUsed ?></td>
                        <td class="OpeningBags"><?php echo $item->OpeningBags ?></td>
                        <td class="OpeningKGs"><?php echo $item->OpeningKGs ?></td>
                        <td class="ClosingBags"><?php echo $item->ClosingBags ?></td>
                        <td class="ClosingKGs"><?php echo $item->ClosingKGs ?></td>
                    </tr>
                <?php } ?>                        
            </tbody>
            <tfoot>
                <tr>
                    <td>Grand Totals</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td id="ReceivedBagsTotals"><?php echo $ReceivedBagsTotals;?></td>
                    <td id="QuantityReceivedTotals"><?php echo $QuantityReceivedTotals;?></td>
                    <td></td>
                    <td id="SendBagsTotals"><?php echo $SendBagsTotals;?></td>
                    <td id="SendQtyTotals"><?php echo $SendQtyTotals;?></td>
                    <td id="BagsUsedTotals"><?php echo $BagsUsedTotals;?></td>
                    <td id="QuantityUsedTotals"><?php echo $QuantityUsedTotals;?></td>
                    <td id="OpeningBagsTotals"><?php echo $OpeningBagsTotals;?></td>
                    <td id="OpeningKGsTotals"><?php echo $OpeningKGsTotals;?></td>
                    <td id="ClosingBagsTotals"><?php echo $ClosingBagsTotals;?></td>
                    <td id="ClosingKGsTotals"><?php echo $ClosingKGsTotals;?></td>
                </tr>
            </tfoot>
        </table>
        <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo count($List) ?> entries</div>
        <?php $activepagestyle = "border:2px solid #74788d" ?>
        <div>
            <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resTotal ?>' /> -->
            <ul class="pagination pagination-rounded justify-content-center">
                <?php
                for ($i = 1; $i <= $PageCount; $i++) {
                    $pageActive = ($PageNo == $i ? "active" : "");
                    echo '<li class="page-item"><button class="page-link ' . $pageActive . 'btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                }
                ?>
            </ul>
        </div>
<?php }
} ?>