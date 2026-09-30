<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
//echo '<pre>';print_r($_SESSION['ClientConfigData']);die;
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$ShowBrandInCSReport = $ClientConfigData->ShowBrandInCSReport;
$ShowBatchCode = $ClientConfigData->ShowBatchCode;
$ProductBatchCode = $ClientConfigData->ProductBatchCode;

$api = API_SHIPMENT_REPORT;

$bodyContent = array(

    "ProductionPlant" => trim($_POST["productionPlant"]),
    "FromDate" => trim($_POST["fromDate"]),
    "ToDate" => trim($_POST["toDate"]),
    "ShowBrandInCSReport" => $ShowBrandInCSReport,
     "ProductBatchCode" => $ProductBatchCode

);
// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;

$resList = CallAPI($api, "GET", "", $bodyContent); 
//echo '<pre>';print_r($resList);die;
// var_dump($resList);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;

if (isset($resList) && !empty($resList)) {
    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->ArrayShipmentData;
        $ShipmentData = $resList->ArrayShipmentData;
        
        ?>
        <table id="tblDataTable" data-paging="false" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>S.No</th>
                    <th style="min-width: 100px;">Plant</th>
                    <th style="min-width: 100px;">Stock Out Date</th>
                    <th style="min-width: 100px;">Invoice Date</th>
                    <th style="min-width: 100px;">Vehicel Number </th>
                    <?php if($ShowBatchCode == 1){?>
                                 <th style="min-width:80px;">Arrival Date</th>

                            <th style="min-width:80px;">Track Code</th>
                                <th style="min-width:80px;">Batch Code</th>
                           <?php }?>
                            <?php 
                    if($ProductBatchCode == 1){?>
                        <th style="min-width: 80px;">ProdBatchCode</th>
                    <?php }?>
                    <th style="min-width: 160px;">Container No </th>
                    <th style="min-width: 100px;">Grade </th>
                    <!-- <th style="min-width: 100px;">Salbs </th> -->
                    <th style="min-width: 120px;">PO No</th>
                    <th style="min-width: 150px;">Invoice Number</th>
                    <th style="min-width: 150px;">Invoice Number(#)</th>
                    <th style="min-width: 160px;">Variety</th>
                    <th style="min-width: 160px;">Packing Style</th>
                    <?php 
                    if($ShowBrandInCSReport == 1){?>
                        <th style="min-width: 160px;">Brand</th>
                    <?php }?>
                    <!-- <th style="min-width: 100px;">Treatment</th> -->
                    <th style="min-width: 100px;">Ordered Mcs</th>
                    <th style="min-width: 100px;">Ordered Salbs </th>
                    <th style="min-width: 160px;">Remarks</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ShipmentData as $item) { ?>
                    <tr>
                        <td><?php echo $item->SNO; ?></td>
                        <td><?php echo $item->Plant; ?></td>
                        <td><?php echo $item->StockOutDate; ?></td>
                        <td><?php echo $item->CInvDate; ?></td>
                        <td><?php echo $item->VehicelNumber; ?></td>

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

                        <td><?php echo $item->ContainerNo; ?></td>
                        <td><?php echo $item->Grade; ?></td>
                        <!-- <td><?php echo $item->OrderKgs; ?></td> -->
                        <!-- <td><?php echo $item->OrderSlabs; ?></td> -->
                        <td><?php echo $item->PoNo; ?></td>
                        <td><?php echo $item->CustomsInvNo; ?></td>
                        <td><?php echo $item->CustomsInvNoTwo; ?></td>
                        <td><?php echo $item->ShortName; ?></td>
                        <td><?php echo $item->PackingStyle; ?></td>
                        <?php 
                        if($ShowBrandInCSReport == 1){?>
                             <td><?php echo $item->BrandName; ?></td>
                        <?php }?>
                        <!-- <td><?php echo $item->SockingType; ?></td> -->
                        <td><?php echo $item->OrderedMcs; ?></td>
                        <td><?php echo $item->OrderSlabs; ?></td>
                        <td><?php echo $item->Remarks; ?></td>

                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo count($List) ?> entries</div>
        <?php $activepagestyle = "border:2px solid #74788d" ?>


        <?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>Note: " . htmlspecialchars($error) . "</div>";
    }
}
?>
