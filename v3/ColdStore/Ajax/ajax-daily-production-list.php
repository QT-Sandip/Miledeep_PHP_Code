<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = isset($_POST["methodType"]) && !empty($_POST["methodType"]) ? $_POST["methodType"] : "";
$ClientConfigData = $_SESSION['ClientConfigData'];
$IsStoreProductionLinked = $ClientConfigData['IsStoreProductionLinked'];
$ShowBatchCode = $ClientConfigData['ShowBatchCode'];
$IsDeductGlaze = $ClientConfigData['IsDeductGlaze'];
$MixedBatchCode = $ClientConfigData['MixedBatchCode'];
$EnableCodeListPrint = !empty($ClientConfigData['EnableCodeListPrint']) ? $ClientConfigData['EnableCodeListPrint'] : 0;




// if($methodType != "McsUpdate") {

$api = API_DAILY_PRODUCTION_DETAILS;

if ($methodType == "McsUpdate") {

    // $api = API_DAILY_PRODUCTION_DETAILS;

    $bodyContent = array(
        "StockOutID" => "",
        "ProductionID" => $_POST["productionID"],
        // "BarCodeID" => "",
        "ProductionDate" => "",
        "TransactionDate" => "",
        "Group" => "",
        "StockHolderID" => "",
        "ColdstoreID" => "",
        "PlantID" => "",
        "Shift" => "",
        "ProductionCode" => "",
        "BatchCode" => "",
        "PoID" => "",
        "Variety" => "",
        "PackingType" => "",
        "PackingStyle" => "",
        "Grade" => "",
        "FreezingType" => "",
        "Glaze" => "",
        "Soaking" => "",
        //"Salt" => "",
        "ProductCategory" => "",
        "AbStatus" => "",
        // "TubNo" =>"",
        "LineNumber" => "",
        "Remarks" => "",
        "Mcs" => $_POST["txtMcs"],
        "LooseBags" => $_POST["txtLooseBags"],
        "PoGradeID" => "",
        "Type" => "McsUpdate",
        "ShowBatchCode" => $ShowBatchCode,
        'IsDeductGlaze' => $IsDeductGlaze,
    );

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // exit;

    $resList = CallAPI($api, "POST", "", $bodyContent);
    //echo '<pre>';print_r( $resList);die;
    // echo json_encode($res, JSON_PRETTY_PRINT);
    // exit;

} else if ($methodType == "delete") {
    $bodyContent = array(
        'ProductionID' => trim($_POST["ProductionID"]),
        'ProductionDate' => trim($_POST["ProductionDate"]),
        'ShowBatchCode' => $ShowBatchCode,
        'IsDeductGlaze' => $IsDeductGlaze,
    );

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // exit;

    $resList = CallAPI($api, "DELETE", "", $bodyContent);
} else {

    $Date = trim($_POST["date"]);
    $Date1 = trim($_POST["date1"]);
    $ColdStores = trim($_POST["coldStore"]);
    $StockBlnTo = trim($_POST["stockBlnTO"]);
    $PackingType = trim($_POST["packingType"]);
    $Group = trim($_POST["group"]);
    $Status = trim($_POST["status"]);
    $PageNo = trim($_POST["PageNo"]);
    $SearchKey = trim($_POST["SearchKey"]);
    $LoadAll = trim($_POST["LoadAll"]);
    $DateType = trim($_POST["DateType"]);

    $bodyContent = array(
        'ProductionDate' => $Date,
        'ProductionEndDate' => $Date1,
        'ColdStoreID' => $ColdStores,
        'StockHolderID' => $StockBlnTo,
        'PackingType' => $PackingType,
        'Group' => $Group,
        'Status' => $Status,
        'SearchKey' => $SearchKey,
        'PageNo' => $PageNo,
        'LoadAll' => $LoadAll,
        'DateType' => $DateType,
        'ShowBatchCode' => $ShowBatchCode,
        'IsDeductGlaze' => $IsDeductGlaze,
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);
    //echo '<pre>';print_r($resList);die;
}

// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;

// var_dump($resList);
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->DailyProductionList;
        $PageStart = !empty($resList->PageStart)?$resList->PageStart:1;
        $Count = count($List);
        $totalNoOfCases = $resList->totalNoOfCases;

        //Totals
        $totalMCS = 0;
        $totalBalanceMCS = 0;
        $totalBags = 0;
        $totalBalanceBags = 0;
        $totalKgs = 0;
        $totalBalanceKgs = 0;
        // echo $PageCount;
        ?>
        <div class="card">
            <input type="hidden" id="hdnAPIRes" data-restype="success" value="<?php echo $resList->Msg ?>" />
            <div class="card-body">
                <table id="tblDataTable" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>S.NO</th>
                            <!-- <th>Production ID</th> -->
                            <th style="min-width:80px;">Production Date </th>
                            <?Php if ($ShowBatchCode == 1 || $MixedBatchCode == 1) { ?>
                                <th>Batch Code</th>
                            <?php } ?>
                            <?Php if ($IsStoreProductionLinked == 1) { ?>
                                <th style="min-width:100px;">Track Code</th>
                            <?php } ?>
                            <th>Group</th>
                            <th style="min-width:150px;">PO Number</th>
                            <th>SUPC</th>
                            <th style="min-width:150px;">Product </th>
                            <th>Grade</th>
                            <th>Packing Type</th>
                            <th>Packing Style</th>
                            <th>Mcs</th>
                            <th>Balance Mcs</th>
                            <th>Bags</th>
                            <th>Balance Bags</th>
                            <th>Kgs</th>
                            <th>Balance Kgs</th>
                            <!-- <?Php //if($IsStoreProductionLinked==1){ ?>
                                <th>Production Weight</th>
                          <?php  //} ?> -->
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($List as $item) { 
                            $totalMCS += $item->Mcs;
                            $totalBalanceMCS += $item->BalanceMcs;
                            $totalBags += $item->LooseBags;
                            $totalBalanceBags += $item->BalanceBags;
                            $totalKgs += $item->Kgs;
                            $totalBalanceKgs += $item->BalanceKgs;
                            ?>
                            <tr>
                                <td><?php echo $item->SNo ?></td>
                                <!-- <td><?php echo $item->ProductionID ?></td> -->
                                <td style="min-width:80px;"><?php echo $item->ProductionDate ?></td>
                                <?Php if ($ShowBatchCode == 1 || $MixedBatchCode == 1) { ?>
                                    <td><?php echo $item->LotNumber ?></td>
                                <?php } ?>
                                <?php if ($IsStoreProductionLinked == 1) { ?>
                                    <td style="min-width:100px;"><?php echo $item->TrackCode ?></td>
                                <?php } ?>
                                <td><?php echo $item->Group ?></td>
                                <td style="min-width:150px;"><?php echo $item->PoNumber ?>
                                    <?php 
                                    if(!empty($EnableCodeListPrint) && !empty($item->PoNumber)){                                    
                                        $urlParam = base64_encode('PoID='.$item->PoID.'&ReportName=Internal code list');
                                        ?>
                                        <a target="_blank"
                                            href="/v3/QualityControl/Reports/D1/DocMpedaCodeList?Q=<?php echo $urlParam;?>&sourceUrl=Freezing"
                                            class="mr-3 text-primary"
                                            title="Code List Print">
                                            <i class="mdi mdi-printer font-size-18"></i>
                                        </a> 
                                    <?php } ?>
                                </td>
                                <td><?php echo $item->OurSupc ?></td>
                                <td style="min-width:150px;"><?php echo $item->ProductDescription ?></td>
                                <td><?php echo $item->Grade ?></td>
                                <td><?php echo $item->PackingType ?></td>
                                <td><?php echo $item->PackingStyle ?></td>
                                <td class="tdMsc" data-noof_cases="<?php echo $item->NoOfCases ?>"><?php echo $item->Mcs ?></td>
                                <td class="tdBalanceMsc"><?php echo $item->BalanceMcs ?></td>
                                <td class="tdBags"><?php echo $item->LooseBags ?></td>
                                <td class="tdBalanceBags"><?php echo $item->BalanceBags ?></td>
                                <td class="tdKgs"><?php echo $item->Kgs ?></td>
                                <td class="tdBalanceKgs"><?php echo $item->BalanceKgs ?></td>
                                <!-- <?php //if($IsStoreProductionLinked==1){ ?>
                                    <td><?php //echo $item->ProductionWeight ?></td>
                                    <?php  //} ?> -->
                                <td>
                                    <?php if ($item->Status == "1") { ?>
                                        <button class="btn btn-link badge badge-success font-size-10"
                                            >
                                            Completed
                                        </button>
                                    <?php } else { ?>
                                        <button class="btn btn-link badge badge-danger font-size-10"
                                            >
                                            Pending
                                        </button>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if ($item->Status == "0") { ?>
                                        <a href="inward?ProductionDataID=<?php echo $item->ProductionID; ?>" class="mr-3 text-primary"
                                            data-toggle="tooltip" data-placement="top" title="Add Stock" data-original-title="Add">
                                            <i class="mdi mdi-plus font-size-18"></i>
                                        </a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="text-right font-weight-bold">Totals:</td>
                            <td></td>
                            <?php if ($ShowBatchCode == 1 || $MixedBatchCode ==1) { ?>
                                    <td></td>
                            <?php } ?>
                            <?php if ($IsStoreProductionLinked == 1) { ?>
                                    <td></td>
                            <?php } ?>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="min-width:150px;"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="font-weight-bold" id="totalMCS"><?php echo $totalMCS; ?></td>
                            <td class="font-weight-bold" id="totalBalanceMCS"><?php echo $totalBalanceMCS; ?></td>
                            <td class="font-weight-bold" id="totalBags"><?php echo $totalBags; ?></td>
                            <td class="font-weight-bold" id="totalBalanceBags"><?php echo $totalBalanceBags; ?></td>
                            <td class="font-weight-bold" id="totalKgs"><?php echo $totalKgs; ?></td>
                            <td class="font-weight-bold" id="totalBalanceKgs"><?php echo $totalBalanceKgs; ?></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?php 
                        if(!empty($totalNoOfCases)){?>
                            <tr>
                                <td class="text-right font-weight-bold" colspan="2">Total Cases:</td>
                            <!-- <td></td> -->
                            <?php if ($ShowBatchCode == 1 || $MixedBatchCode==1) { ?>
                                    <td></td>
                            <?php } ?>
                            <?php if ($IsStoreProductionLinked == 1) { ?>
                                    <td></td>
                            <?php } ?>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="min-width:150px;"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="font-weight-bold" id="totalCases"  style="min-width:80px;"><?php echo $totalNoOfCases; ?></td>
                            <td class="font-weight-bold"></td>
                            <td class="font-weight-bold"></td>
                            <td class="font-weight-bold"></td>
                            <td class="font-weight-bold"></td>
                            <td class="font-weight-bold"></td>
                            <td></td>
                            <td></td>
                            </tr>
                        <?php }?>
                    </tfoot>
                </table>
                <div class="mt-3 mb-3">Showing <?php echo $PageStart;?> to <?php echo $item->SNo ?> out of <?php echo $resTotal ?> entries </div>
                <?php $activepagestyle = "border:2px solid #74788d" ?>
                <div>
                    <input type='hidden' id='hdnTotalRecords' value='<?php echo $resTotal ?>' />
                    <ul class="pagination pagination-rounded  <?php echo $PageCount<=30?'justify-content-center':'';?>" style="overflow-x: auto;margin: 0px auto;">
                        <?php
                        for ($i = 1; $i <= $PageCount; $i++) {
                            $pageActive = ($PageNo == $i ? "active" : "");
                            echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                        }
                        ?>
                    </ul>
                </div>
            <?php } else {
        echo "
        <input type='hidden' id='hdnAPIRes' data-restype='error' value='.$resList->Error .' />
        <div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";
    }
} ?>

    </div>
</div>
<?php ?>