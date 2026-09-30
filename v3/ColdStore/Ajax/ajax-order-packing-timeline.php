<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$ShowBatchCode = $ClientConfigData->ShowBatchCode;
$ProductBatchCode = $ClientConfigData->ProductBatchCode;

$methodType = trim($_POST["methodType"]);

$api = API_ORDER_PACKING_TIMELINE_REPORT;
//header('Content-Type: application/json');
//echo json_encode($ClientConfigData);
if ($methodType == 'getPoNumbers') {

    $bodyContent = array(
        "Year" => trim($_POST["year"]),
        "OrderStatus" => trim($_POST["orderStatus"]),
        "PlantID" => trim($_POST["PlantID"]),
        "MethodType" => trim($methodType),
        "ProductBatchCode" => $ProductBatchCode
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);
    //echo '<pre>';print_r($resList);die;
    $PoNumbersData = $resList->PoNumbersData;
    // echo json_encode($PoNumbersData, JSON_PRETTY_PRINT);
    // exit;
    header('Content-Type: application/json');
    echo json_encode($PoNumbersData);
} else if ($methodType == 'getShipoutPoNumber') {

    $bodyContent = array(
        "Year" => trim($_POST["year"]),
        "OrderStatus" => trim($_POST["orderStatus"]),
        "MethodType" => trim($methodType)
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);

    $PoNumbersData = $resList->PoNumbersData;
    // echo json_encode($PoNumbersData, JSON_PRETTY_PRINT);
    // exit;
    header('Content-Type: application/json');
    echo json_encode($PoNumbersData);
} else if ($methodType == 'getShipmentoutPoNumber') {

    $bodyContent = array(
        "Year" => trim($_POST["year"]),
        "OrderStatus" => trim($_POST["orderStatus"]),
        "MethodType" => trim($methodType)
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);
    //echo '<pre>';print_r($resList);die;
    $PoNumbersData = $resList->ShipmentPoNumbersData;
    // echo json_encode($PoNumbersData, JSON_PRETTY_PRINT);
    // exit;
    header('Content-Type: application/json');
    echo json_encode($PoNumbersData);
} else if ($methodType == 'getList') {

    $bodyContent = array(
        "Year" => trim($_POST["year"]),
        "OrderStatus" => trim($_POST["orderStatus"]),
        "PoID" => trim($_POST["purchaseOrder"]),
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);

    // echo json_encode($resList, JSON_PRETTY_PRINT);
    // exit;
    if (isset($resList) && !empty($resList)) {
        $error = $resList->Error;

        if (!isset($error) || empty($error)) {

            $OrderPackingData = $resList->OrderPackingData;
            $StoreData = $resList->StoreData;
            $noDataColSpan = 13;
            if ($ShowBatchCode == 1) {
                 $noDataColSpan += 3;
            }
            if ($ProductBatchCode == 1) {
                $noDataColSpan += 1;
            }
            ?>
                        <table id="tblDataTable" class="table table-hover table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th style="min-width:50px;">S.No</th>
                                    <th style="min-width: 120px;width: 120px;">Production Date</th>
                                    <th style="min-width: 120px;width: 120px;">Stock Out Date</th>

                        <?php if ($ShowBatchCode == 1) { ?>
                                        <th style="min-width:80px;">Arrival Date</th>

                                        <th style="min-width:80px;">Track Code</th>
                                        <th style="min-width:80px;">Batch Code</th>
                        <?php } ?>
                        <?php if ($ProductBatchCode == 1) { ?>
                                        <th>ProdBatchCode</th>
                        <?php } ?>

                                    <th style="min-width: 150px;width: 150px;">Product Description</th>
                                    <th style="min-width: 150px;width: 150px;">Grade</th>
                                    <th style="min-width: 120px;width: 120px;">Packing Style</th>
                                    <th style="min-width: 120px;width: 120px;">Group</th>
                                    <th style="min-width: 120px;width: 120px;">PackingType</th>
                                    <th style="min-width: 100px;width: 100px;">Production Plant</th>
                                    <th style="min-width: 150px;width: 150px;">Cold Store</th>
                                    <th style="min-width: 100px;width: 100px;">Mcs</th>
                                    <th style="min-width: 100px;width: 100px;">Kgs</th>
                                    <th style="min-width: 100px;width: 100px;">Slabs</th>
                                </tr>
                            </thead>
                            <tbody>

                    <?php if (!empty($OrderPackingData)) { ?>
                        <?php foreach ($OrderPackingData as $item) { ?>
                                        <tr>
                                            <td style="min-width:50px;"><?php echo $item->SNO ?></td>
                                            <td style="min-width: 120px;width: 120px;"><?php echo $item->ProductionDate ?></td>
                                            <td style="min-width: 120px;width: 120px;"><?php echo $item->OutDate ?></td>

                                    <?php
                                    if ($ShowBatchCode == 1) { ?>
                                                <td style="min-width:80px;"><?php echo $item->ArrivalDate ?></td>

                                                <td style="min-width:80px;"><?php echo $item->TrackCode ?></td>

                                                <td><?php echo $item->LotNumber ?></td>
                                <?php } ?>
                                <?php if ($ProductBatchCode == 1) { ?>
                                                <td><?php echo $item->ProdBatchCode ?></td>
                                <?php } ?>

                                            <td style="min-width: 150px;width: 150px;"><?php echo $item->ProductDescription ?></td>
                                            <td style="min-width: 150px;width: 150px;"><?php echo $item->Grade ?></td>
                                            <td style="min-width: 120px;width: 120px;"><?php echo $item->PackingStyle ?></td>
                                            <td style="min-width: 120px;width: 100px;"><?php echo $item->ProductionTypeID ?></td>
                                            <td style="min-width: 120px;width: 100px;"><?php echo $item->PackingTypeID ?></td>
                                            <td style="min-width: 100px;width: 100px;"><?php echo $item->StationName ?></td>
                                            <td style="min-width: 150px;width: 150px;"><?php echo $item->StoreName ?></td>
                                            <td style="min-width: 100px;width: 100px;"><?php echo $item->Mcs ?></td>
                                            <td style="min-width: 100px;width: 100px;"><?php echo $item->KGs ?></td>
                                            <td style="min-width: 100px;width: 100px;"><?php echo $item->McsSlabs ?></td>
                                        </tr>
                        <?php } ?>

                    <?php } else { ?>
                            <tr>
                                <td style='display: none'>&nbsp;</td>
                                <td style='display: none'>&nbsp;</td>
                                <td style='display: none'>&nbsp;</td>
                                <td style='display: none'>&nbsp;</td>
                                <td style='display: none'>&nbsp;</td>
                                <td style='display: none'>&nbsp;</td>
                                <td style='display: none'>&nbsp;</td>
                                <td colspan='<?php echo $noDataColSpan;?>'>
                                    <div class='text-center'> No data available </div>
                                </td>
                                
                                
                                <td style='display: none'>&nbsp;</td>
                                <td style='display: none'>&nbsp;</td>
                                <td style='display: none'>&nbsp;</td>
                                <td style='display: none'>&nbsp;</td>
                                <td style='display: none'>&nbsp;</td>
                                <?php if ($ProductBatchCode == 1) { ?>
                                    <td style='display: none'>&nbsp;</td>
                                <?php } ?>
                                <?php
                                if ($ShowBatchCode == 1) { ?>
                                    <td style='display: none'>&nbsp;</td>
                                    <td style='display: none'>&nbsp;</td>
                                    <td style='display: none'>&nbsp;</td>
                            <?php } ?>
                            </tr>
                    <?php } ?>
                            </tbody>
                            <!-- <tfoot> -->
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                 <?php
                                    if ($ShowBatchCode == 1) { ?>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                <?php }?>
                                <?php if ($ProductBatchCode == 1) { ?>
                                    <th></th>
                                <?php }?>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                            <tr>
                                <th style="min-width:50px;">SNO</th>
                                <th style="min-width: 12px;width: 120px;">Production Plant</th>
                                <th style="min-width: 12px;width: 120px;">Cold Store</th>
                                <th style="min-width: 120px;width: 150px;">Product Description</th>
                                <th style="min-width: 120px;width: 120px;">Store KGs</th>
                                <th style="min-width: 120px;width: 120px;">Store MCS</th>
                                <th style="min-width: 120px;width: 120px;">Store Loose Bags</th>
                                <th style="min-width: 120px;width: 120px;">Grade</th>
                                <th style="min-width: 120px;width: 120px;">PackingStyle</th>

                            </tr>

                <?php if (!empty($StoreData)) { ?>
                    <?php foreach ($StoreData as $storeItem) { ?>
                                    <tr>
                                        <th style="min-width:50px;"><?php echo $storeItem->SNO ?></th>
                                        <th style="min-width: 12px;width: 120px;"><?php echo $storeItem->StationName ?></th>
                                        <th style="min-width: 12px;width: 120px;"><?php echo $storeItem->StoreName ?></th>
                                        <th style="min-width: 120px;width: 150px;"><?php echo $storeItem->ProductDescription ?></th>
                                        <th style="min-width: 120px;width: 120px;"><?php echo $storeItem->AvalKgs ?></th>
                                        <th style="min-width: 120px;width: 120px;"><?php echo $storeItem->AvalMcs ?></th>
                                        <th style="min-width: 120px;width: 120px;"><?php echo $storeItem->LooseBags ?></th>
                                        <th style="min-width: 120px;width: 120px;"><?php echo $storeItem->Grade ?></th>
                                         <th style="min-width: 120px;width: 120px;"><?php echo $storeItem->PackingStyle ?></th>

                                    </tr>
                    <?php } ?>
                <?php } else { ?>
                                <tr>
                                    <th style="display: none"></th>
                                    <th style="display: none"></th>
                                    <th style="display: none"></th>
                                    <th style="display: none"></th>
                                    <th style="display: none"></th>
                                    <th style="display: none"></th>

                                    <th colspan='<?php echo $noDataColSpan;?>'>
                                        <div class='text-center'> No data available </div>
                                    </th>

                                </tr>
                <?php } ?>
                        </table>
            <?php


        }
    }
}
?>