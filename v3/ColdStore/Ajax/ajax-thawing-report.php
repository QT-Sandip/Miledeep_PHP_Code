<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$ShowBatchCode = $ClientConfigData->ShowBatchCode;
$ProductBatchCode = $ClientConfigData->ProductBatchCode;

$api = API_THAWING_REPORT;

$bodyContent = array(
    "ProductionPlant" => trim($_POST["plant"]),
    "FromDate" => trim($_POST["fromDate"]),
    "ToDate" => trim($_POST["toDate"]),
    "Species" => trim($_POST["species"]),
    "Grade" => trim($_POST["grade"]),
    "Variety" => trim($_POST["variety"]),
    "Treatment" => trim($_POST["treatment"]),
    "ProductBatchCode" => $ProductBatchCode

    // "FromDate" => "2024-08-23",
    // "ToDate" => "2024-08-23",
    // "ProductionPlant" => "",
    // "Variety" => "",
    // "Grade" => "",
    // "Species" => "",
    // "Treatment" => ""
);

$resList = CallAPI($api, "GET", "", $bodyContent);

// echo print_r($resList);
// exit;

// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;


if (isset($resList) && !empty($resList)) {
    $error = $resList->Error;

    if (!isset($error) || empty($error)) {

        $ThawingData = $resList->ArrayThawingData;
        $ThawingTotals=$resList->ThawingTotals;

?>
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width:50px;">SNo</th>
                    <th style="min-width: 100px;width: 100px;">Type</th>
                    <th style="min-width: 120px;width: 120px;">Stock Out Date</th>
                    <th style="min-width: 120px;width: 120px;">Transaction Date</th>
                    <th style="min-width: 120px;width: 120px;">Production Date</th>
                    <?php if($ShowBatchCode == 1){?>
                    <th style="min-width:80px;">Arrival Date</th>
                    <th style="min-width:80px;">Track Code</th>
                        <th style="min-width:80px;">Batch Code</th>
                    <?php }?>
                     <?php if ($ProductBatchCode == 1) { ?>
                        <th>Production Batch Code</th>
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
                    <th style="min-width: 120px;width: 120px;">Production Date</th>
                    <th style="min-width: 120px;width: 120px;">Transction Date</th>
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
                <?php 
            $TotalOutMcs = 0;
            $TotalOutLooseBags = 0;
            $TotalKgs = 0;
            $TotalBalanceKGs = 0;
            foreach ($ThawingData as $item) {
                $TotalOutMcs += $item->OutMcs;
                $TotalOutLooseBags +=$item->OutLooseBags;
                $TotalKgs += floatval(str_replace(',', '', $item->TotalKGs));
               $TotalBalanceKGs +=floatval(str_replace(',', '', $item->BalanceKGs));
                
            //    var_dump($ThawingData);
            //    exit;

                ?>
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
                           <td><?php echo $item->ProdBatchCode?></td>
                        <?php } ?>
                        <td style="min-width: 120px;width: 100px;"><?php echo $item->DeviSUPC ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->ProductDescription ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->Grade ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->PackingTypeID ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->PackingStyle ?></td>
                        <td class="out-mcs" style="min-width: 100px;width: 100px;"><?php echo $item->OutMcs; ?></td>
                        <td class="out-loose-bags" style="min-width: 100px;width: 100px;"><?php echo $item->OutLooseBags; ?></td>
                        <td class="tdTotalKGs" style="min-width: 100px;width: 100px;"><?php echo $item->TotalKGs; ?></td>
                        <td class="tdBalanceKGs" style="min-width: 100px;width: 100px;"><?php echo number_format($item->BalanceKGs,2); ?></td>
                        <td style="padding: 0px !important;" colspan="12">
                            <table class="subTable table table-bordered mb-0" style="width: 100%">
                                <?php
                                foreach ($item->BreakUpData as $itemBreak) {  ?>
                                    <tr>
                                        <td style="min-width: 120px;width: 120px;"><?php echo $itemBreak->ProductionDate ?></td>
                                        <td style="min-width: 120px;width: 120px;"><?php echo $itemBreak->TransactionDate ?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->PoNo ?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->PackingStyle ?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->ProductDescription ?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->Grade ?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->PackingTypeID ?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->ProductionTypeID ?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->ActualMcs ?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->ActualLooseBags ?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->TotalKGs ?></td>
                                        <td style="min-width: 100px;width: 100px;"><?php echo $itemBreak->Remarks ?></td>
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
                        <!-- <td style="display: none"></td> -->
                    </tr>
                <?php  } ?>
            </tbody>
            <tr style="font-weight: bold;">
                <th style="min-width: 50px;width: 50px;">Grand Totals</th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
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
                <th style="min-width: 100px;width: 100px;"></th>
                <td id="TotalOutMcs"style="min-width: 100px;width: 100px;"><?php echo $TotalOutMcs ?></td>
                <td id="TotalOutLooseBags" style="min-width: 100px;width: 100px;"><?php echo $TotalOutLooseBags ?></td>
                <td id="TotalKgs" style="min-width: 100px;width: 100px;"><?php echo $TotalKgs ?></td>
                <td id="TotalBalanceKGs" style="min-width: 100px;width: 100px;"><?php echo number_format($TotalBalanceKGs, 2); ?></td>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"><?php echo $ThawingTotals->ProductionMcs?></th>
                <th style="min-width: 100px;width: 100px;"><?php echo $ThawingTotals->ProductionLoose?></th>
                <th style="min-width: 100px;width: 100px;"><?php echo $ThawingTotals->ProductionKGS?></th>
                <th style="min-width: 100px;width: 100px;"></th>
            </tr>
        </table>
<?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . htmlspecialchars($error) . "</div>";
    }
}
?>