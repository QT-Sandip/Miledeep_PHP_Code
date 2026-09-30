<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STORE_STOCK_SEARCH;

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
$PageNo = trim($_POST["pageNo"]);
$SearchKey = trim($_POST["searchKey"]);
$LoadAll = trim($_POST["loadAll"]);

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
    'PageNo' => $PageNo,
    'SearchKey' => $SearchKey,
    'LoadAll' => $LoadAll
);

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
                        <th style="width: 80px;">Prod Date</th>
                        <th>PO NO</th>
                        <th>SUPC</th>
                        <!-- <th>BarCode</th> -->
                        <th style="width: 100px;">Variety</th>
                        <th>Grade</th>
                        <th>Mcs</th>
                        <th>Loose</th>
                        <th>Kgs</th>
                        <th>Packing style</th>
                        <th>Packing Type</th>
                        <th>Freezing</th>
                        <th>Group</th>
                        <th>Category</th>
                        <th>Store</th>
                        <th>Block</th>
                        <th>Rack Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo date('d-m-Y', strtotime($item->ProductionDate)) ?></td>
                            <td><?php echo $item->PoNo ?></td>
                            <td><?php echo $item->SUPC ?></td>
                            <!-- <td><?php // echo $item->BarCodeID ?></td> -->
                            <td><?php echo $item->Variety ?></td>
                            <td><?php echo $item->Grade ?></td>
                            <td><?php echo $item->Mcs ?></td>
                            <td><?php echo $item->Loose ?></td>
                            <td><?php echo $item->Kgs ?></td>
                            <td><?php echo $item->PackingStyle ?></td>
                            <td><?php echo $item->PackingType ?></td>
                            <td><?php echo $item->FreezingType ?></td>
                            <td><?php echo $item->Group ?></td>
                            <td><?php echo $item->Category ?></td>
                            <td><?php echo $item->Store ?></td>
                            <td><?php echo $item->Block ?></td>
                            <td><?php echo $item->RackNumber ?></td>
                        </tr>
                    <?php  } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th></th>
                        <th>Total</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th><?php echo $Totals->TotalMcs ?> </th>
                        <th><?php echo $Totals->TotalLoose ?> </th>
                        <th><?php echo $Totals->TotalKgs ?> </th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
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