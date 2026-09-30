<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$ShowBatchCode = $ClientConfigData->ShowBatchCode;
$ProductBatchCode = $ClientConfigData->ProductBatchCode;

$api = API_SHIPMENT_STATUS_REPORT;

$bodyContent = array(
    "PoID" => trim($_POST["PoID"]),
    "Year" => trim($_POST["year"]),
    "OrderStatus" => trim($_POST["orderStatus"]),
    "ProductionPlant" => trim($_POST["productionPlant"]),
    

);
// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;
$resList = CallAPI($api, "GET", "", $bodyContent);

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->ArrayShipmentData;
        $ShipmentTotals = $resList->ShipmentTotals;

        // echo json_encode($List, JSON_PRETTY_PRINT);
        // exit;

?>
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <!-- <th>SNo</th> -->
                    <th style="min-width: 120px;width: 120px;">StockOut Date</th>
                    <th style="min-width: 100px;width: 100px;">PoNo</th>
                    <th style="min-width: 100px;width: 100px;">Invoice Number</th>

                
                    <?php if($ShowBatchCode == 1){?>
                                <th style="min-width:80px;">Arrival Date</th>
                        <th style="min-width:80px;">Track Code</th>
                            <th style="min-width:80px;">Batch Code</th>
                        <?php }?>
                    <?php if($ProductBatchCode == 1){?>
                        <th style="min-width: 80px;">Production Batch Code</th>
                       <?php }?>

                    <!-- <th style="min-width: 130px;width: 130px;">Invoice Number(#)</th> -->
                    <th style="min-width: 200px;width: 200px;">Variety</th>
                    <th style="min-width: 100px;width: 100px;">Grade</th>
                    <th style="min-width: 100px;width: 100px;">Packing Type</th>
                    <th style="min-width: 100px;width: 100px;">Container No</th>
                    <th style="min-width: 120px;width: 120px;">Vehicel Number</th>
                    <th style="min-width: 100px;width: 100px;">Packing Style</th>
                    <th style="min-width: 100px;width: 100px;">Ordered Mcs</th>
                    <th style="min-width: 100px;width: 100px;">Ordered Slabs</th>
                    <th style="min-width: 100px;width: 100px;">Ordered KGs</th>
                    <!-- breakUp -->
                    <th style="min-width: 120px;width: 120px;">Production Plant</th>
                    <!-- <th style="min-width: 120px;width: 120px;">Production Date</th> -->
                    <th style="min-width: 120px;width: 120px;">Production Group</th>
                    <th style="min-width: 100px;width: 100px;">Shipment Plant</th>
                    <th style="min-width: 100px;width: 100px;">Production Mcs</th>
                    <th style="min-width: 120px;width: 120px;">Production Slabs</th>
                    <th style="min-width: 100px;width: 100px;">Production KGs</th>
                    <th style="min-width: 100px;width: 100px;">Store Name</th>
                    <th style="min-width: 100px;width: 100px;">Location</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($List as $item) { ?>
                    <tr>
                        <!-- <td><?php echo $item->SNO ?></td> -->
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->StockOutDate ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->PoNo ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->CustomsInvNo ?></td>

                        <?php 
                                if($ShowBatchCode == 1){?>
                                  <td style="min-width:80px;"><?php echo $item->ArrivalDate ?></td>

                                <td style="min-width:80px;"><?php echo $item->TrackCode ?></td>

                                    <td><?php echo $item->LotNumber ?></td>
                                <?php }?>
                                  <?php 
                        if($ProductBatchCode == 1){?>
                             <td><?php echo $item->ProdBatchCode; ?></td>
                        <?php }?>

                        <!-- <td style="min-width: 150px;width: 150px;"><?php echo $item->CustomsInvNoTwo ?></td> -->
                        <td style="min-width: 200px;width: 200px;"><?php echo $item->ProductDescription ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->Grade ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->PackingTypeID ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->ContainerNo ?></td>
                        <td style="min-width: 120px;width: 120px;"><?php echo $item->VehicelNumber ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->PackingStyle ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OrderedMcs ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OrderSlabs ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->OrderKgs ?></td>

                        <!-- breakUpData -->
                        <td style="min-width: 120px;width: 120px;"><?php echo $item->StationName ?></td>
                        <!-- <td style="min-width: 120px;width: 120px;"><?php echo $item->ProductionDate ?></td> -->
                        <td style="min-width: 120px;width: 120px;"><?php echo $item->ProductionTypeID ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->StationName ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->ProductionMcs ?></td>
                        <td style="min-width: 120px;width: 120px;"><?php echo $item->ProductionSlabs ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->ProductionKGs ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->StoreName ?></td>
                        <td style="min-width: 100px;width: 100px;"><?php echo $item->Location ?></td>
                        <!-- <td style="padding: 0px !important;" colspan="9">
                            <table class="subTable table table-bordered mb-0" style="width: 100%">
                                <?php
                                foreach ($item->ArrayBreakUpData as $itemBreak) {  ?>
                                    <tr>
                                        <th style="min-width: 120px;width: 120px;"><?php echo $itemBreak->StationName ?></th>
                                        <th style="min-width: 120px;width: 120px;"><?php echo $itemBreak->ProductionDate ?></th>
                                        <th style="min-width: 120px;width: 120px;"><?php echo $itemBreak->ProductionTypeID ?></th>
                                        <th style="min-width: 100px;width: 100px;"><?php echo $itemBreak->StationName ?></th>
                                        <th style="min-width: 100px;width: 100px;"><?php echo $itemBreak->ProductionMcs ?></th>
                                        <th style="min-width: 120px;width: 120px;"><?php echo $itemBreak->ProductionSlabs ?></th>
                                        <th style="min-width: 100px;width: 100px;"><?php echo $itemBreak->ProductionKGs ?></th>
                                        <th style="min-width: 100px;width: 100px;"><?php echo $itemBreak->StoreName ?></th>
                                        <th style="min-width: 100px;width: 100px;"><?php echo $itemBreak->Location ?></th>
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
                        <td style="display: none"></td> -->
                    </tr>
                <?php  } ?>
            </tbody>
            <!-- <tr style="font-weight: bold;">
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;">Grand Totals</th>
                <th style="min-width: 120px;width: 120px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 120px;width: 120px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"><?php echo $ShipmentTotals->TotalOrderedMcs ?></th>
                <th style="min-width: 100px;width: 100px;"><?php echo $ShipmentTotals->TotalOrderedSlabs ?></th>
                <th style="min-width: 100px;width: 100px;"><?php echo $ShipmentTotals->TotalOrderedKGs ?></th>

                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 100px;width: 100px;"></th>
                <th style="min-width: 120px;width: 120px;"><?php echo $ShipmentTotals->TotalProductionMcs ?></th>
                <th style="min-width: 100px;width: 100px;"><?php echo $ShipmentTotals->TotalProductionSlabs ?></th>
                <th style="min-width: 100px;width: 100px;"><?php echo $ShipmentTotals->TotalProductionKGs ?></th>
                <th style="min-width: 100px;width: 100px;"></th>
            </tr> -->
        </table>
        <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo count($List) ?> entries</div>
        <?php $activepagestyle = "border:2px solid #74788d" ?>
<?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>Note: " . $error . "</div>";
    }
}
?>