<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$ShowBrandInCSReport = $ClientConfigData->ShowBrandInCSReport;
$ShowBatchCode = $ClientConfigData->ShowBatchCode;
$ProductBatchCode = $ClientConfigData->ProductBatchCode;

$api = API_RACKWISE_STOCK_REPORT;

$ColdstoreID = trim($_POST["coldStore"]);
$StockBlgsTo = trim($_POST["stockBlgsTo"]);
$PONO = trim($_POST["poNo"]);
$VarietyID = trim($_POST["variety"]);
$Grade = trim($_POST["grade"]);
$PackingStyle = trim($_POST["pckgStyle"]);
$PackingType = trim($_POST["pckgType"]);
$ProdCategory = trim($_POST["prodCategory"]);
$Species = trim($_POST["fSpecies"]);
$ShortName = trim($_POST["fShortName"]);
$TypeProduct = trim($_POST["fTypeProduct"]);
$Blocks = trim($_POST["blocks"]);
$AntiBiotic = trim($_POST["abStatus"]);
$PageNo = trim($_POST["pageNo"]);
$SearchKey = trim($_POST["searchKey"]);
$LoadAll = trim($_POST["loadAll"]);
$Species = trim($_POST["species"]);
$FreezingType = trim($_POST["freezingType"]);
$Soaking = trim($_POST["fsoaking"]);
$TypeofProduct = trim($_POST["productType"]);
$StockType = trim($_POST["stockType"]);
$GlazePerc = trim($_POST["GlazePerc"]);
$Brand = trim($_POST["Brand"]);
$RequestFrom = trim($_POST["RequestFrom"]);


$bodyContent = array(

    'ColdstoreID' => $ColdstoreID,
    'TenantID' => $StockBlgsTo,
    'PoID' => $PONO,
    'VarietyID' => $VarietyID,
    'Grade' => $Grade,
    'PackingStyleID' => $PackingStyle,
    'PackingTypeID' => $PackingType,
    'ProductCategoryID' => $ProdCategory,
    'Species' => $Species,
    'ShortName' => $ShortName,
    'TypeProduct' => $TypeProduct,
    'Blocks' => $Blocks,
    'AbStatus' => $AntiBiotic,
    'PageNo' => $PageNo,
    'SearchKey' => $SearchKey,
    'LoadAll' => $LoadAll,
    'FreezingType' => $FreezingType,
    'Soaking' => $Soaking,
    'TypeofProduct' => $TypeofProduct,
    'StockType' => $StockType,
    'GlazePerc' => $GlazePerc,
    'Brand' => $Brand,
    'RequestFrom' => $RequestFrom,
    "ShowBrandInCSReport" => $ShowBrandInCSReport,
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
                        <th style="min-width:100px;">Production Date</th>
                        <th>PO NO</th>
                         <?php if($ShowBatchCode == 1){?>
                         <th style="min-width:80px;">Arrival Date</th>
                         <th style="min-width:80px;">Track Code</th>
                         <th style="min-width:80px;">Batch Code</th>
                        <?php }?>
                        <?php if($ProductBatchCode == 1){?>
                        <th style="min-width:100px;">Arrival Code</th>
                        <?php }?>
                        <th>SUPC</th>
                        <th style="width: 100px;">Variety</th>
                        <?php 
                        if($ShowBrandInCSReport == 1){?>
                            <th style="min-width:100px;">Brand</th>
                        <?php }?>
                        <th>Grade</th>
                        <td>Species</td>
                        <th>FreezingType</th>
                        <th>Soaking</th>
                        <th>Type of Product</th>
                        <th style="min-width:100px;">Packing style</th>
                        <th>Mcs</th>
                        <th>Loose</th>
                        <th>Slabs</th>
                        <th>Kgs</th>

                        <th>Packing Type</th>
                        <th>Group</th>
                        <th>Category</th>
                        <th>Ab Status</th>
                        <th style="min-width:100px;">Store</th>
                        <th style="min-width:100px;">Block</th>
                        <th style="min-width:100px;">Rack Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo date('d-m-Y', strtotime($item->ProductionDate)) ?></td>
                            <td><?php echo $item->PoNo ?></td>
                            <?php 
                                if($ShowBatchCode == 1){?>
                                  <td style="min-width:80px;"><?php echo $item->ArrivalDate ?></td>

                                <td style="min-width:80px;"><?php echo $item->TrackCode ?></td>

                                    <td><?php echo $item->LotNumber ?></td>
                                <?php }?>
                                 <?php 
                                if($ProductBatchCode == 1){?>
                                <td style="min-width:100px;"><?php echo $item->ProdBatchCode ?></td>
                            <?php }?>
                            <td><?php echo $item->SUPC ?></td>
                            <td><?php echo $item->Species . '-' . $item->Variety ?></td>
                            <?php 
                                if($ShowBrandInCSReport == 1){?>
                                <td><?php echo $item->BrandName ?></td>
                            <?php }?>
                            <td><?php echo $item->Grade ?></td>
                            <td><?php echo $item->Species ?></td>
                            <td><?php echo $item->FreezingType ?></td>
                            <td><?php echo $item->Soaking ?></td>
                            <td><?php echo $item->TypeofProduct ?></td>
                            <td><?php echo $item->PackingStyle ?></td>
                            <td><?php echo $item->Mcs ?></td>
                            <td><?php echo $item->Loose ?></td>
                            <td><?php echo $item->TotalSlabs ?></td>
                            <td><?php echo sprintf('%.2f', (intval($item->Kgs * 100) / 100)); ?></td>
                            <td><?php echo $item->PackingType ?></td>
                            <td><?php echo $item->Group ?></td>
                            <td><?php echo $item->Category ?></td>
                            <td><?php echo $item->AbStatus ?></td>
                            <td><?php echo $item->Store ?></td>
                            <td><?php echo $item->Block ?></td>
                            <td><?php echo $item->RackNumber ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td>Total</td>
                        <td>Mcs with LooseBags : <?php echo $Totals->TotalLooseMcs ?> </td>
                        <?php 
                        if($ShowBatchCode == 1){?>
                        <td style="min-width:80px;"></td>

                        <td style="min-width:80px;"></td>
                        <td></td>
                        <?php }?>
                        <?php 
                        if($ProductBatchCode == 1){?>
                            <td style="min-width:100px;"></td>
                        <?php }?>
                        <td></td>
                        <td></td>
                        <?php 
                        if($ShowBrandInCSReport == 1){?>
                            <td></td>
                        <?php }?>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?php echo $Totals->TotalMcs ?> </td>
                        <td><?php echo $Totals->TotalLoose ?> </td>
                        <td><?php echo $Totals->TotalSlabs ?></td>
                        <td><?php echo round($Totals->TotalKgs, 2) ?> </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php } ?>

        <?php if ($LoadAll == 0) { ?>
            <div style="display: flex;">
                <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
                <ul class="pagination pagination-rounded " style="overflow-x: auto;margin: 0px auto;">
                    <!-- justify-content-center -->
                    <?php
                    for ($i = 1; $i <= $PageCount; $i++) {
                        $pageActive = ($PageNo == $i ? "active" : "");
                        echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                    }
                    ?>
                </ul>
            </div>


            <!-- <div>
            <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
            <ul class="pagination pagination-rounded justify-content-center">
                <?php
                for ($i = 1; $i <= $PageCount; $i++) {
                    $pageActive = ($PageNo == $i ? "active" : "");
                    echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                }
                ?>
            </ul>
        </div> -->
        <?php } ?>
    <?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
?>