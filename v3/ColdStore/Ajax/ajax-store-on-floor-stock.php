<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
//  ECHO $_SESSION['UnitID']; EXIT;
$ClientConfigData = $_SESSION['ClientConfigData'];
$IsStoreProductionLinked = $ClientConfigData['IsStoreProductionLinked']; 
$ShowBatchCode = $ClientConfigData['ShowBatchCode'];
$ProductBatchCode = $ClientConfigData['ProductBatchCode'];
$shiftToFilledPallet = $ClientConfigData['shiftToFilledPallet'];
$IsMultipleStockAssignToRack = !empty($ClientConfigData['IsMultipleStockAssignToRack']) ? $ClientConfigData['IsMultipleStockAssignToRack'] : 0; //anil;
$IsDeductGlazeFW = $ClientConfigData['IsDeductGlazeFW'];  
$IsDeductGlaze = $ClientConfigData['IsDeductGlaze'];
$displayBrandColumn = ($_SESSION['ClientID'] == 'DFLV3') ? 1 : 0;

if (isset($_POST["methodType"]) && !empty($_POST["methodType"])) {
    if ($_POST["methodType"] == "popupAdd") {
        $api = API_ATTACH_STOCK_TOALREADY_FILLED_PALLET;

        // $drpAddBlockID = trim($_POST["drpAddBlockID"]);
        $drpAddRackName = trim($_POST["drpAddRackName"]);
        $stockID = trim($_POST["stockID"]);
        $existingRackID = trim($_POST["existingRackID"]);

        $bodyContent = array(
            'RackID' => $drpAddRackName,
            'StockID' => $stockID,
            'existingRackID' => $existingRackID,
            'shiftToFilledPallet' => $shiftToFilledPallet
        );

        // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
        // exit;
        $res = CallAPI($api, "POST", "", $bodyContent);

        header('Content-Type: application/json');
        echo json_encode($res);

    }else if ($_POST["methodType"] == "getAnalytics") {

        $api = API_STORE_ON_FLOOR_STOCK;
        $ColdstoreID = trim($_POST["coldStore"]);
        $TenantID = trim($_POST["stockBlgsTo"]);
        $bodyContent = array('methodType'=>'getAnalytics','ColdstoreID' => $ColdstoreID,'TenantID' => $TenantID);
        $data = CallAPI($api, "GET", "", $bodyContent);
        //echo "<pre>"; print_r($data); exit;

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    } else if ($_POST["methodType"] == "MiltipleShiftPalette") {
        $api = API_STOCK_SHIFTING_TO_ANOTHER_RACK;

        // $drpAddBlockID = trim($_POST["drpAddBlockID"]);
        $drpAddRackName = trim($_POST["drpAddRackName"]);
        $StockData = json_decode($_POST["StockData"], true);
        $existingRackID = trim($_POST["existingRackID"]);

        $bodyContent = array(
            'RackID' => $drpAddRackName,
            'StockData' => $StockData,
           // 'existingRackID' => $existingRackID,
            'shiftToFilledPallet' => $shiftToFilledPallet,
            'IsDeductGlazeFW' => $IsDeductGlazeFW,
            'IsDeductGlaze' => $IsDeductGlaze,
        );

        // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
        // exit;
        $res = CallAPI($api, "POST", "", $bodyContent);
        //echo '<pre>';print_r($res);die;


        header('Content-Type: application/json');
        echo json_encode($res);

    }
} else {

    $api = API_STORE_ON_FLOOR_STOCK;

    $ColdstoreID = trim($_POST["coldStore"]);
    $StockBlgsTo = trim($_POST["stockBlgsTo"]);
    $VarietyID = trim($_POST["variety"]);
    $Grade = trim($_POST["grade"]);
    $PackingStyle = trim($_POST["pckgStyle"]);
    $PackingType = trim($_POST["pckgType"]);
    $StockType = trim($_POST["stockType"]);
    $ShortName = trim($_POST["fShortName"]);
    $PoNumber = trim($_POST["PoNumber"]);
    $Species = trim($_POST["species"]);
    $TypeProduct = trim($_POST["typeProduct"]);
    $freezingType = trim($_POST["freezingType"]);
    $blocks = trim($_POST["blocks"]);
    $groups = trim($_POST["group"]);

    $PageNo = trim($_POST["pageNo"]);
    $SearchKey = trim($_POST["searchKey"]);
    $LoadAll = trim($_POST["loadAll"]);


    $bodyContent = array(
        'ColdstoreID' => $ColdstoreID,
        'TenantID' => $StockBlgsTo,
        'VarietyID' => $VarietyID,
        'PoGradeId' => $Grade,
        'PackingStyleID' => $PackingStyle,
        'PackingType' => $PackingType,
        'StockType' => $StockType,
        'Species' => $Species,
        'ShortName' => $ShortName,
        'TypeProduct' => $TypeProduct,
        'PoNumber' => $PoNumber,
        'FreezingType' => $freezingType,
        'Blocks' => $blocks,
        'Group' => $groups,
        'PageNo' => $PageNo,
        'SearchKey' => $SearchKey,
        'LoadAll' => $LoadAll,
        "ProductBatchCode" => $ProductBatchCode,
        'ShowBatchCode' => $ShowBatchCode,
        'Brand' => !empty($_POST['drpBrand']) ? $_POST['drpBrand'] : '',
    );


    $resList = CallAPI($api, "GET", "", $bodyContent);

    // echo json_encode($resList, JSON_PRETTY_PRINT);
    // exit;
    // var_dump($resList);
    // echo json_encode($resList, JSON_PRETTY_PRINT);
    // exit;
    if (isset($resList) && !empty($resList)) {

        $error = $resList->Error;

        if (!isset($error) || empty($error)) {
            $PageCount = $resList->PageCount;
            $resTotal = $resList->TotalCount;
            $List = $resList->Data;


            ?>
            <?php if (!isset($error) || empty($error)) { ?>
<style>
/* Table header */
table.dataTable thead th {
    background: #f8f9fa;
    font-size: 0.75rem;
    text-transform: uppercase;
    color: #6c757d;
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 10;
}

/* Table cells */
table.dataTable td {
    font-size: 0.85rem;
    vertical-align: middle;
    white-space: nowrap;
}

/* Light row separators */
table.dataTable tbody tr {
    border-bottom: 1px solid #f1f1f1;
}

/* Hover effect */
table.dataTable tbody tr:hover {
    background-color: #f8f9fa;
}

/* Store name highlight */
.tdStoreName {
    font-weight: 600;
    color: #343a40;
}

/* Actions column */
.tdActionName {
    text-align: center;
    white-space: nowrap;
}

.tdActionName a,
.tdActionName button {
    padding: 4px 6px;
    border-radius: 4px;
}

.tdActionName a:hover,
.tdActionName button:hover {
    background: #f1f3f5;
}

/* Sticky header */


table.dataTable thead .sorting,
table.dataTable thead .sorting_asc,
table.dataTable thead .sorting_desc {
    background-position: right 8px center !important;
    background-repeat: no-repeat !important;
}
thead th {
    background-color: #f1f3f5;   /* light grey */
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}
.select2-container--open .select2-dropdown {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
  max-height: 200px;
  overflow-x: hidden;
}
.td-bid {
    font-weight: 600;
    color: #212529;
}

.td-mcs {
    font-weight: 700;
    color: #2f9e44; /* green */
}

.td-loose {
    font-weight: 600;
}

.td-loose.has-loose {
    color: #d6336c; /* red */
}
.tdActionName a,
.tdActionName button {
    opacity: 0.6;
    transition: opacity 0.2s ease;
}

table.dataTable tbody tr:hover .tdActionName a,
table.dataTable tbody tr:hover .tdActionName button {
    opacity: 1;
}
table.dataTable th:first-child,
table.dataTable td:first-child {
    position: sticky;
    left: 0;
    background: #fff;
    z-index: 5;
}


table.dataTable tbody tr {
    cursor: pointer;
}

table.dataTable tbody tr.active-row {
    background-color: #e7f1ff;
}


</style>
                <table id="tblDataTable" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <?php
                            if(!empty($IsMultipleStockAssignToRack) && $StockType != 0){?>
                                <th class="noExport cellCheckbox text-center" style="min-width: 60px;">
                                    <input type="checkbox" class="big-checkbox" id="checkall" />
                                </th>
                            <?php } ?>
                            <th style="width: 40px">S.No</th>
                            <th style="width: 40px">BID</th>
                            <th style="min-width: 100px">Date</th>
                            <?Php if($IsStoreProductionLinked==1){ ?>
                                <th>Track Code</th>
                            <?php  }?>
                            <?php if($ShowBatchCode == 1){?>
                                 <th style="min-width:80px;">Arrival Date</th>
                                <th style="min-width:80px;">Batch Code</th>
                            <?php }?>
                            <?php
                            if($ProductBatchCode == 1){?>
                                <th>Arrival Code</th>
                            <?php }?>
                            <th>Group</th>
                            <th>Variety</th>
                            <th>Grade</th>
                            <th>Soaking</th>
                            <th>Species</th>
                            <th>Packing Type</th>
                            <?php 
                            if($displayBrandColumn){?>
                                <th>Brand Name</th>
                            <?php }?>
                            <th>Packing style</th>
                            <th>Category</th>
                            <th>AB Status</th>
                            <th>Po Number</th>
                            <!-- <th>Lot Number</th> -->
                            <th>MCS</th>
                            <th>Loose</th>
                            <th style="min-width: 100px">Store</th>
                            <?php if ($StockType != 0) { ?>
                                <th style="min-width: 100px">Location</th>
                            <?php } ?>
                            <th class="noExport" style="min-width: 110px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $TotalMcs = $TotalBags = 0;
                        foreach ($List as $item) { 
                            $TotalMcs += $item->AvalMcs;
                            $TotalBags += $item->LooseBags;?>
                            <tr>
                                <?php
                                if(!empty($IsMultipleStockAssignToRack) && $StockType != 0){?>
                                    <td class="tdCheckList text-center" style="min-width: 60px;">
                                        <input type="checkbox" class="rowCheck big-checkbox" data-store_rack_id="<?php echo $item->StoreRackID ;?>" data-sno="<?php echo $item->SNO;?>" data-bid="<?php echo $item->BarCodeID;?>" value="<?php echo $item->StockID;?>" />
                                    </td>
                                <?php }?>
                                <td><?php echo $item->SNO ?></td>
                                <td><?php echo $item->BarCodeID ?></td>
                                <td><?php echo date('d-m-Y', strtotime($item->ProductionDate)) ?></td>
                                <?php if($IsStoreProductionLinked==1){ ?>
                                    <td><?php echo $item->TrackCode ?></td>
                                    <?php  }?>
                                      <?php
                                if($ShowBatchCode == 1){?>
                                  <td style="min-width:80px;"><?php echo $item->ArrivalDate ?></td>
                                    <td><?php echo $item->LotNumber ?></td>
                                <?php }?>
                                 <?php
                                if($ProductBatchCode == 1){?>
                                    <td><?php echo $item->ProdBatchCode ?></td>
                                <?php }?>
                                <td><?php echo $item->ProductionTypeID ?></td>
                                <td><?php echo $item->Species . '-' . $item->VarietyName ?></td>
                                <td><?php echo $item->Grade ?></td>
                                <td><?php echo $item->SockingType ?></td>
                                <td><?php echo $item->Species ?></td>
                                <td><?php echo $item->PackingTypeID ?></td>
                                <?php 
                                if($displayBrandColumn){?>
                                    <td><?php echo $item->BrandName ?></td>
                                <?php }?>
                                <td><?php echo $item->PackingStyle ?></td>
                                <td><?php echo $item->ProductCategoryID ?></td>
                                <td><?php echo $item->AbStatus ?></td>
                                <td><?php echo $item->PoNo . $item->LotNo ?></td>
                                <!-- <td><?php // echo $item->LotNo ?></td> -->
                                <?php
                                if(!empty($IsMultipleStockAssignToRack) && $StockType != 0){?>
                                    <td>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <input type="text" style="width: 150px;" class="form-control txtMCS" placeholder="Enter MCS" id="<?php echo $item->SNO ?>-txtMCS" name="txtMCS" value="<?php echo $item->AvalMcs ?>" data-mcs="<?php echo $item->AvalMcs ?>" />
                                        </div>
                                    </td>
                                    <td>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <input id="<?php echo $item->SNO ?>-txtLooseBags" type="text" style="width: 150px;" class="form-control txtLooseBags" data-totalslabs="<?php echo $item->TotalSlabs ?>" data-bags="<?php echo $item->LooseBags ?>" placeholder="Enter Loose Bags"
                                            value="<?php echo $item->LooseBags ?>" />
                                        </div>
                                    </td>
                                <?php }else{?>
                                    <td class="AvalMcs"><?php echo $item->AvalMcs ?></td>
                                    <td class="LooseBags"><?php echo $item->LooseBags ?></td>
                                <?php }?>
                                <td style="width: 150px" class="tdStoreName" data-storeid="<?php echo $item->StoreID ?>">
                                    <?php echo $item->StoreName ?></td>
                                <?php if ($StockType != 0) { ?>
                                    <td><?php echo $item->Location ?></td>
                                <?php } ?>

                                <td class="tdActionName">

                                    <?php if ($StockType != '') { ?>
                                        <?php if ($StockType == 0) { ?>

                                            <a class="mr-3 text-primary aListAdd" href="javascript:void(0)" data-storeRackId="0"
                                                data-stockid="<?php echo $item->StockID ?>">
                                                <i class="mdi mdi-plus font-size-18"></i>
                                            </a>&nbsp;&nbsp;

                                            <a target="_blank" class="mr-3 text-primary"
                                                href="view-store-layout?BarCodeId=<?php echo $item->BarCodeID ?>&StockID=<?php echo $item->StockID ?>">
                                                <i class="mdi mdi-open-in-app font-size-18"></i>
                                            </a>&nbsp;&nbsp;

                                            <a target="_blank" class="mr-3 text-primary"
                                                href="inward?ProductionDataID=<?php echo $item->ProductionID ?>&BarCodeID=<?php echo $item->BarCodeID ?>&StockID=<?php echo $item->StockID ?>">
                                                <i class="mdi mdi-pencil font-size-18"></i>&nbsp;&nbsp;
                                            </a>
                                        <?php } ?>
                                        <?php if ($StockType == 1) { ?>
                                            <button class="btn text-primary unplacePallate" data-id="<?php echo $item->BarCodeID ?>"
                                                data-stockid="<?php echo $item->StockID ?>" data-storeRackId="<?php echo $item->StoreRackID ?>"
                                                title="Unplace Palette" data-original-title="Unplace Pallate">
                                                <i class="mdi mdi-login font-size-18  text-danger"></i>
                                            </button>

                                            <button class="btn text-primary aListAdd" data-id="<?php echo $item->BarCodeID ?>"
                                                data-stockid="<?php echo $item->StockID ?>" data-storeRackId="<?php echo $item->StoreRackID ?>"
                                                title="Shift Palette" data-original-title="Shift Pallate">
                                                <i class="mdi mdi-vector-intersection font-size-18  text-danger"></i>
                                            </button>
                                        <?php } ?>
                                    <?php } ?>

                                    <a target="_blank" class="mr-3 text-primary"
                                        href="CsBarCodeLabelPrint2.php?BID=<?php echo $item->BarCodeID ?>&StockID=<?php echo $item->StockID ?>">
                                        <i class="mdi mdi-printer font-size-18"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                     <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>Totals</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <?php 
                            if($displayBrandColumn){?>
                                <td></td>
                            <?php }?>

                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td id="TotalMcs"><?php echo $TotalMcs;?></td>
                            <td id="TotalBags"><?php echo $TotalBags;?></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
                <?php $activepagestyle = "border:2px solid #74788d" ?>
            <?php } ?>
            <div style="display: flex;">
                <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
                <ul class="pagination pagination-rounded" style="overflow-x:auto; margin:0 auto;">

    <?php
    // Current page
    $currentPage = $PageNo;

    // Total pages
    $totalPages = $PageCount;

    // Left Arrow
    if ($currentPage > 1) {
        echo '<li class="page-item">
                <button class="page-link btnPageno" data-pageno="' . ($currentPage - 1) . '">&laquo;</button>
              </li>';
    }

    for ($i = 1; $i <= $totalPages; $i++) {

        // Show:
        // first 2 pages
        // last 2 pages
        // current page ±1

        if (
            $i <= 2 ||
            $i > $totalPages - 2 ||
            abs($i - $currentPage) <= 1
        ) {

            $pageActive = ($currentPage == $i) ? "active" : "";

            echo '<li class="page-item">
                    <button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button>
                  </li>';

        } else {

            // Show dots only once
            if (
                $i == 3 && $currentPage > 4 ||
                $i == $totalPages - 2 && $currentPage < $totalPages - 3
            ) {
                echo '<li class="page-item disabled">
                        <span class="page-link">..</span>
                      </li>';
            }
        }
    }

    // Right Arrow
    if ($currentPage < $totalPages) {
        echo '<li class="page-item">
                <button class="page-link btnPageno" data-pageno="' . ($currentPage + 1) . '">&raquo;</button>
              </li>';
    }
    ?>

</ul>
            </div>
            <?php
        } else {
            echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
        }
    }
}
?>
