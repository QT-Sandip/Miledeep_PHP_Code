<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$ShowBatchCode = $ClientConfigData->ShowBatchCode;
$ProductBatchCode = $ClientConfigData->ProductBatchCode;

$api = API_EXCESS_ORDER_REPORT;

$ColdstoreID = trim($_POST["coldStore"]);
$StockBlgsTo = trim($_POST["stockBlgsTo"]);
$PONO = trim($_POST["poNo"]);
$FromDate = trim($_POST["fromDate"]);
$ToDate = trim($_POST["toDate"]);
$Status = trim($_POST["status"]);
$VarietyID = trim($_POST["variety"]);
$Grade = trim($_POST["grade"]);
$PackingStyle = trim($_POST["pckgStyle"]);
// $PackingType = trim($_POST["pckgType"]);
// $ProdCategory = trim($_POST["prodCategory"]);
$Species = trim($_POST["fSpecies"]);
$ShortName = trim($_POST["fShortName"]);
$TypeProduct = trim($_POST["fTypeProduct"]);
$PageNo = trim($_POST["pageNo"]);
$SearchKey = trim($_POST["searchKey"]);
$LoadAll = trim($_POST["loadAll"]);


$bodyContent = array(

    'ColdstoreID' => $ColdstoreID,
    'TenantID' => $StockBlgsTo,
    'FromDate' => $FromDate,
    'ToDate' => $ToDate,
    'Status' => $Status,
    'PoNo' => $PONO,
    'VarietyID' => $VarietyID,
    'Grade' => $Grade,
    'PackingStyleID' => $PackingStyle,
    // 'PackingTypeID' => $PackingType,
    // 'ProductCategoryID' => $ProdCategory,
    'Species' => $Species,
    'ShortName' => $ShortName,
    'TypeProduct' => $TypeProduct,
    'PageNo' => $PageNo,
    'SearchKey' => $SearchKey,
    'LoadAll' => $LoadAll,
    "ProductBatchCode" => $ProductBatchCode
);

// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;

$resList = CallAPI($api, "GET", "", $bodyContent);

// var_dump($resList);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->Data;
        $Totals = $resList->Totals;
?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 20px">S.No</th>                      
                        <th>PO NO</th>
                         <!-- <?php if($ShowBatchCode == 1){?>
                                 <th style="min-width:80px;">Arrival Date</th>

                            <th style="min-width:80px;">Track Code</th>
                                <th style="min-width:80px;">Batch Code</th>
                           <?php }?> -->
                            <?php 
                            if($ProductBatchCode == 1){?>
                                <th>ProdBatchCode</th>
                            <?php }?>
                        <th>SUPC</th>
                        <th style="width: 80px;">Brand</th>
                        <th style="width: 100px;">Variety</th>
                        <th>Grade</th>
                        <th>Packing style</th>
                        <th>Freezing</th>
                        <th>Treatment</th>
                        <th>Glaze</th>
                        <th>Glaze Perc</th>
                        <th>Order Mcs</th> 
                        <th>Shipped Mcs</th>
                        <th>Excess Mcs</th>
                        <th>Excess Slabs </th>
                        <!-- <th>Order Kgs</th>
                        <th>Packed Kgs</th>
                        <th>Excess Kgs</th> -->
                        <th>Store</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo $item->PoNo ?></td>
                             <!-- <?php 
                                if($ShowBatchCode == 1){?>
                                  <td style="min-width:80px;"><?php echo $item->ArrivalDate ?></td>

                                <td style="min-width:80px;"><?php echo $item->TrackCode ?></td>

                                    <td><?php echo $item->LotNumber ?></td>
                                <?php }?> -->
                                <?php 
                                if($ProductBatchCode == 1){?>
                                    <td><?php echo $item->ProdBatchCode ?></td>
                                <?php }?>
                            <td><?php echo $item->SUPC ?></td>
                            <td><?php echo $item->Brand ?></td>
                            <td><?php echo $item->Variety ?></td>
                            <td><?php echo $item->Grade ?></td>
                            <td><?php echo $item->PackingStyle ?></td>
                            <td><?php echo $item->FreezingType ?></td>
                            <td><?php echo $item->Treatment ?></td>
                            <td><?php echo $item->Glaze ?></td>
                            <td><?php echo $item->GlazePerc ?></td>
                            <td><?php echo $item->OrderMcs ?></td>
                            <td><?php echo $item->ShipedMcs ?></td>
                            <td><?php echo $item->ExcessMcs ?></td>
                            <td><?php echo $item->ExcessSlabs ?></td>
                            <!-- <td><?php echo $item->OrderKgs ?></td>   
                            <td><?php echo $item->PackedKgs ?></td>
                            <td><?php echo $item->ExcessKgs ?></td> -->
                            <td><?php echo $item->Store ?></td>
                        </tr>
                    <?php  } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th></th>
                        <th>Total</th>
                         <!-- <?php 
                                if($ShowBatchCode == 1){?>
                                  <td style="min-width:80px;"></td>

                                <td style="min-width:80px;"></td>

                                    <td></td>
                                <?php }?> -->
                                  <?php 
                            if($ProductBatchCode == 1){?>
                                <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th> </th>
                        <th> </th>
                        <th></th>
                        <th></th>
                        <th><?php echo $Totals->TotalOrderMcs ?></th>
                        <th><?php echo $Totals->TotalShipedMcs ?></th>
                        <th><?php echo $Totals->TotalExcessMcs ?></th>
                        <th><?php echo $Totals->TotalExcessSlabs ?></th>
                        <!-- <th><?php echo $Totals->TotalOrderKgs ?></th>
                        <th><?php echo $Totals->TotalPackedKgs ?></th>
                        <th><?php echo $Totals->TotalExcessKgs ?></th> -->
                        <th></th>
                    </tr>
                </tfoot>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php  } ?>
        <div>
            <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
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
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
?>