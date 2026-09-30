<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($cp . "Includes/IncludePage.php");
include_once ROOT . 'config/api-call.php';

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$ShowBatchCode = $ClientConfigData->ShowBatchCode;
$ProductBatchCode = $ClientConfigData->ProductBatchCode;

$api = API_EXCESS_STOCK_REPORT;
// echo $_POST["plant"]; exit;
$PageNo = trim($_POST["pageNo"]);
$bodyContent = array(
    'ColdStore' => trim($_POST["coldStore"]),
    'PoID' => trim($_POST["ordernumber"]),
    'Variety' => trim($_POST["variety"]),
    'Species' => trim($_POST["species"]),
    'ProductType' => trim($_POST["productform"]),
    'FreezingType' => trim($_POST["freezingtype"]),
    'Grade' => trim($_POST["grade"]),
    'Plant' => trim($_POST["plant"]),
    'PageNo' => trim($_POST["pageNo"]),
    'SearchKey' => trim($_POST["searchKey"]),
    'LoadAll' => trim($_POST["loadAll"]),
    'ProductCategory' => trim($_POST["drpPDCategory"]),
    'Soaking' => trim($_POST["soaking"]),
    'AbStatus' => trim($_POST["abstatus"]),
    'PckgStyle' => trim($_POST["drpPckgStyle"]),
     "ProductBatchCode" => $ProductBatchCode
);
// echo json_encode($bodyContent, JSON_PRETTY_PRINT);

$resList = CallAPI($api, "GET", "", $bodyContent);
// var_dump($bodyContent);

// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->ExcessStockData;

        if (!isset($error) || empty($error)) {
?>
            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>SNO</th>
                        <th>Species</th>
                          <!-- <?php if($ShowBatchCode == 1){?>
                                 <th style="min-width:80px;">Arrival Date</th>

                            <th style="min-width:80px;">Track Code</th>
                                <th style="min-width:80px;">Batch Code</th>
                           <?php }?> -->
                             <?php 
                            if($ProductBatchCode == 1){?>
                                <th>ProdBatchCode</th>
                            <?php }?>
                        <th>Grade</th>
                        <th>Variety</th>
                        <th>Product Form</th>
                        <th>Freezing Type</th>
                        <th>Packing Style</th>
                        <th>Glaze Type</th>
                        <th>Glaze Perc</th>
                        <th>Soaked</th>
                        <th>Product Category</th>
                        <th>Order Quantity</th>
                        <th>Order Mcs</th>
                        <th>Order Slabs</th>
                        <th>Store Quantity Kgs</th>
                        <th>Store Mcs</th>
                        <th>Store Slabs</th>
                        <th>Excess Quantity Kgs</th>
                        <th>Excess Mcs</th>
                        <th>Excess Slabs</th>
                        <!-- <th>Hon Count</th>
                        <th>Qty in KGS</th>
                        <th>Qty in TONS</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo $item->SNO; ?></td>
                            <td><?php echo $item->Species; ?></td>
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
                            <td><?php echo $item->Grade; ?></td>
                            <td><?php echo $item->Species . '-' . $item->ShortName; ?></td>
                            <td><?php echo $item->TypeProduct; ?></td>
                            <td><?php echo $item->FreezingType; ?></td>
                            <td><?php echo $item->Style; ?></td>
                            <td><?php echo $item->Glaze; ?></td>
                            <td><?php echo $item->GlazePerc; ?></td>
                            <td><?php echo $item->Treatment; ?></td>
                            <td><?php echo $item->ProductCategory; ?></td>
                            <td data-value=<?php echo $item->OrderedKGs; ?>><?php echo number_format($item->OrderedKGs, 3); ?></td>
                            <td><?php echo $item->OrderedMcs; ?></td>
                            <td><?php echo $item->OrderedSlabs; ?></td>
                            <td data-value=<?php echo $item->StoreKGs; ?>><?php echo number_format($item->StoreKGs, 3) ?></td>
                            <td><?php echo $item->StoreMcs; ?></td>
                            <td><?php echo $item->StoreSlabs; ?></td>
                            <td style="color:<?php echo $item->DisplayKgColor; ?>" data-value=<?php echo $item->BalanceKGs; ?>><?php echo number_format($item->BalanceKGs, 3); ?></td>
                            <td style="color:<?php echo $item->DisplayMcsColor; ?>"><?php echo $item->BalanceMCS; ?></td>
                            <td><?php echo $item->BalanceSlabs; ?></td>
                            <!-- <td><?php echo $item->HeadonCount; ?></td>
                            <td><?php echo $item->HeadonQuantityinKgs; ?></td>
                            <td><?php echo $item->HeadonQuantityinTons; ?></td> -->
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo count($List) ?> entries</div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php } ?>
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
<?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>Note : " . $error . "</div>";
    }
}
?>