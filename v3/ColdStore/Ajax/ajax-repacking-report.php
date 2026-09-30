<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$ShowBatchCode = $ClientConfigData->ShowBatchCode;
$ProductBatchCode = $ClientConfigData->ProductBatchCode;

$api = API_REPACKING_REPORT;

$bodyContent = array(
    "ProductionPlant" => trim($_POST["plant"]),
    "FromDate" => trim($_POST["fromDate"]),
    "ToDate" => trim($_POST["toDate"]),
    "Species" => trim($_POST["species"]),
    "Grade" => trim($_POST["grade"]),
    "Variety" => trim($_POST["variety"]),
    "Treatment" => trim($_POST["treatment"]),
    "PackingStyle" => trim($_POST["packingStyle"]),
    "ProductBatchCode" => $ProductBatchCode,
    "Group" => trim($_POST["drpGroup"]),
);

$resList = CallAPI($api, "GET", "", $bodyContent);

// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;


if (isset($resList) && !empty($resList)) {
    $thLabelNameOut = 'Repacking - Date';
    $thLabelNameIn = 'Repacking + Date';
    if(!empty($_POST["drpGroup"]) && $_POST["drpGroup"] == 'Conversion Out'){
        $thLabelNameOut = 'Conversion Out Date';
        $thLabelNameIn = 'Conversion In Date';
    }
    $error = $resList->Error;

    if (!isset($error) || empty($error)) {

        $RepackingData = $resList->ArrayRepackingData;
        $RepackingTotals = $resList->RepackingTotals;

?>
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width:50px;">SNo</th>
                    <th style="min-width: 100px;width: 100px;">Type</th>
                    <th style="min-width: 120px;width: 120px;"><?php echo $thLabelNameOut;?></th>
                    <th style="min-width: 120px;width: 120px;">Transction Date</th>
                    <th style="min-width: 120px;width: 120px;">Actual Production Date</th>
                       <?php if($ShowBatchCode == 1){?>
                                 <th style="min-width:80px;">Arrival Date</th>

                            <th style="min-width:80px;">Track Code</th>
                                <th style="min-width:80px;">Batch Code</th>
                           <?php }?>
                              <?php if ($ProductBatchCode == 1) { ?>
                <th>ProdBatchCode</th>
            <?php } ?>
                    <th style="min-width: 100px;width: 100px;">SUPC</th>
                    <th style="min-width: 100px;width: 100px;">Product</th>
                    <th style="min-width: 100px;width: 100px;">Grade</th>
                    <th style="min-width: 100px;width: 100px;">Packing Type</th>
                    <th style="min-width: 100px;width: 100px;">Packing Style</th>
                    <th style="min-width: 100px;width: 100px;">Mcs</th>
                    <th style="min-width: 100px;width: 100px;">Loose Bags</th>
                    <th style="min-width: 100px;width: 100px;">KGs</th>
                    <th style="min-width: 100px;width: 100px;">Balance KGs</th>
                    <!-- breakUp -->
                    <th style="min-width: 120px;width: 120px;"><?php echo $thLabelNameIn;?></th>
                    <th style="min-width: 120px;width: 120px;">Transction Date</th>
                    <th style="min-width: 120px;width: 120px;">Production Plant</th>
                    <th style="min-width: 100px;width: 100px;">PoNo</th>
                    <th style="min-width: 100px;width: 100px;">Packing Style</th>
                    <th style="min-width: 100px;width: 100px;">Product</th>
                    <th style="min-width: 100px;width: 100px;">Grade</th>
                    <th style="min-width: 100px;width: 100px;">Packing Type</th>
                    <th style="min-width: 100px;width: 100px;">Group</th>
                    <th style="min-width: 100px;width: 100px;">Mcs</th>
                    <th style="min-width: 100px;width: 100px;">Loose Bags</th>
                    <th style="min-width: 100px;width: 100px;">KGs</th>
                    <th style="min-width: 100px;width: 100px;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($RepackingData as $item) { ?>
                    <tr>
                        <td style="min-width:50px;"><?php echo $item->SNO ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OutType ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OutDate?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->TransactionDate?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->ProductionDate?></td>
                          <?php 
                                if($ShowBatchCode == 1){?>
                                  <td style="min-width:80px;"><?php echo $item->ArrivalDate ?></td>

                                <td style="min-width:80px;"><?php echo $item->TrackCode ?></td>

                                    <td><?php echo $item->LotNumber ?></td>
                                <?php }?>
                                 <?php if ($ProductBatchCode == 1) { ?>
                    <td><?= $item->ProdBatchCode ?></td>
                <?php } ?>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->DeviSUPC ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->ProductDescription ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->Grade ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->PackingTypeID ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->PackingStyle ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OutMcs ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OutLooseBags ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->TotalKGs ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->BalanceKGs ?></td>
                        <td style="padding: 0px !important;" colspan="13">
                            <table class="subTable table table-bordered mb-0" style="width: 100%">
                                <?php
                                foreach ($item->BreakUpData as $itemBreak) {  ?>
                                    <tr>
                                        <th style="min-width: 120px;width: 120px;"><?php echo $itemBreak->ProductionDate ?></th>
                                        <th style="min-width: 120px;width: 120px;"><?php echo $itemBreak->TransactionDate ?></th>
                                        <th style="min-width: 120px;width: 120px;"><?php echo $itemBreak->StationName ?></th>
                                        <th style="min-width: 100px;width: 100px;"><?php echo $itemBreak->PoNo ?></th>
                                        <th style="min-width: 100px;width: 100px;"><?php echo $itemBreak->PackingStyle ?></th>
                                        <th style="min-width: 100px;width: 100px;"><?php echo $itemBreak->ProductDescription ?></th>
                                        <th style="min-width: 100px;width: 100px;"><?php echo $itemBreak->Grade ?></th>
                                        <th style="min-width: 100px;width: 100px;"><?php echo $itemBreak->PackingTypeID ?></th>
                                        <th style="min-width: 100px;width: 100px;"><?php echo $itemBreak->ProductionTypeID ?></th>
                                        <th style="min-width: 100px;width: 100px;"><?php echo $itemBreak->ActualMcs ?></th>
                                        <th style="min-width: 100px;width: 100px;"><?php echo $itemBreak->ActualLooseBags ?></th>
                                        <th style="min-width: 100px;width: 100px;"><?php echo $itemBreak->TotalKGs ?></th>
                                        <th style="min-width: 100px;width: 100px;"><?php echo $itemBreak->Remarks ?></th>
                                    </tr>
                                <?php } ?>
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
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                    </tr>
                <?php  } ?>
            </tbody>
            <tr style="font-weight: bold;">
                <th style="min-width:50px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;">Grand Totals</th>
                    <?php 
                                if($ShowBatchCode == 1){?>
                                  <td style="min-width:80px;"></td>

                                <td style="min-width:80px;"></td>

                                    <td></td>
                                <?php }?>
                                 <?php if ($ProductBatchCode == 1) { ?>
                    <th></th>
                <?php } ?>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"><?php echo $RepackingTotals->TotalMcs ?></th>
                <th style="min-width: 100px;width: 100px;"><?php echo $RepackingTotals->TotalLooseBags ?></th>
                <th style="min-width: 100px;width: 100px;"><?php echo $RepackingTotals->TotalOutKGs ?></th>
                <th style="min-width: 100px;width: 100px;"><?php echo $RepackingTotals->TotalBalanceKGs ?></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"><?php echo $RepackingTotals->ProductionMcs ?></th>
                <th style="min-width: 100px;width: 100px;"><?php echo $RepackingTotals->ProductionLoose ?></th>
                <th style="min-width: 100px;width: 100px;"><?php echo $RepackingTotals->ProductionKGS ?></th>
                <th style="min-width: 100px;width: 100px;"></th>
            </tr>
        </table>
<?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
?>