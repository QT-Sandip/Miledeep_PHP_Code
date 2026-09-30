<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = $_SESSION['ClientConfigData'];
$convertLooseToMcs = !empty($ClientConfigData['convertLooseToMcs']) ? trim($ClientConfigData['convertLooseToMcs']):0; //sriram

$api = API_SHIPMENT_OUT;

$ColdstoreID = trim($_POST["coldStore"]);
$StockBlgsTo = trim($_POST["stockBlgsTo"]);
$PoNo = trim($_POST["poNo"]);
$PageNo = trim($_POST["pageNo"]);
$SearchKey = trim($_POST["searchKey"]);
$LoadAll = trim($_POST["loadAll"]);
$pdCategory = trim($_POST["pdCategory"]);

$bodyContent = array(
    'ColdstoreID' => $ColdstoreID,
    'TenantID' => $StockBlgsTo,
    'PoNo' => $PoNo,
    'PageNo' => $PageNo,
    'SearchKey' => $SearchKey,
    'LoadAll' => $LoadAll,
    'pdCategory' => $pdCategory,
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


?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center">
                            <input type="checkbox" class="big-checkbox" id="checkall" />
                        </th>
                        <th>SNO</th>
                        <th>ID</th>
                        <th>Bar Code</th>
                        <th>Po Number</th>
                        <th>Production Plant</th>
                        <th>Group</th>
                        <th>Variety</th>
                        <th>Grade</th>
                        <th>Mcs</th>
                        <?php if($convertLooseToMcs == 1) { ?>
                            <th>Bags</th>
                        <?php } ?>
                        <th>Packing Style</th>
                        <th>Packing Type</th>
                        <th>Store</th>
                        <th>Location</th>
                        <th>Slabs</th>
                        <th>KGS</th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="rowCheck big-checkbox" data-stockid="<?php echo $item->StockID ?>" data-prodid="<?php echo $item->ProductionID ?>" value="<?php echo $item->StockID ?>" />
                                <input type="hidden" class="hdnStockId" value="<?php echo $item->StockID ?>" />
                                <input type="hidden" class="hdnRowId" value="<?php echo $item->RowID ?>" />
                                <input type="hidden" class="hdnBarCodeID" value="<?php echo $item->BarCodeID ?>" />
                                <input type="hidden" class="txtLooseBags" value="<?php echo $item->LooseSlabs ?>" />
                            </td>
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo $item->BarCodeID ?></td>
                            <td><?php echo $item->BarCodeValue ?></td>
                            <td><?php echo $item->PoNo ?></td>
                            <td><?php echo $item->ProductionPlantName ?></td>
                            <td><?php echo $item->Group ?></td>
                            <td><?php echo $item->ShortName ?></td>
                            <td><?php echo $item->Grade ?></td>
                            <!-- <td><?php echo $item->AvalMcs ?></td> -->
                            <!-- <td data-Mcs="">
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <input type="text" style="width: 150px;" class="form-control txtMCS" placeholder="Enter MCS" id=""
                                        name="txtMCS" value="<?php echo $item->AvalMcs ?>" data-mcs="<?php echo $item->AvalMcs ?>"
                                        data-varietyid="<?php echo $item->VarietyID ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');" />
                                </div>
                            </td> -->
                            <td class="mcsCell" data-Mcs="">
                                <span class="exportMcs"><?php echo $item->AvalMcs ?></span>

                                <input type="text"
                                    style="width:150px;"
                                    class="form-control txtMCS"
                                    placeholder="Enter MCS"
                                    name="txtMCS"
                                    value="<?php echo $item->AvalMcs ?>"
                                    data-mcs="<?php echo $item->AvalMcs ?>"
                                    data-varietyid="<?php echo $item->VarietyID ?>"
                                    oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                            </td>
                            <?php if($convertLooseToMcs == 1) { ?>
                                <td class="LooseBags"><?php echo $item->LooseSlabs ?></td>
                            <?php } ?>
                            <td><?php echo $item->PackingStyle ?></td>
                            <td><?php echo $item->PackingTypeID ?></td>
                            <td><?php echo $item->StoreName ?></td>
                            <td><?php echo $item->Location ?></td>
                            <td><?php echo $item->TotalSlabs . '
                                <input type="hidden" class="hdnStockId" value="' . $item->StockID . '" />
                                <input type="hidden" class="hdnBarCodeID" value="' . $item->BarCodeID . '" />
                                <input type="hidden" class="hdnStoreID" value="' . $item->StoreID . '" />
                                <input type="hidden" class="hdnRackID" value="' . $item->RowID . '" />
                                <input type="hidden" class="hdnVarietyID" value="' . $item->VarietyID . '" />
                                <input type="hidden" class="hdnBlockID" value="' . $item->BlockID . '" /> '
                                ?></td>
                            <td><?php echo $item->AvalKgs ?></td>

                        </tr>
                    <?php  } ?>
                </tbody>
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