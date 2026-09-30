<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = $_SESSION['ClientConfigData'];
// $IsStoreProductionLinked = $ClientConfigData->IsStoreProductionLinked;  //sriram
// $IsDeductGlaze = $ClientConfigData->IsDeductGlaze;  //sriram


$IsStoreProductionLinked = $ClientConfigData['IsStoreProductionLinked'];  //sriram
$IsDeductGlaze = $ClientConfigData['IsDeductGlaze'];  //sriram
$IsPlantFilter = $ClientConfigData['IsPlantFilter'];  //sriram
$StoreRackMaxMcs = $ClientConfigData['StoreRackMaxMcs'];//anil
$FlooreRackIds = !empty($ClientConfigData['FlooreRackIds']) ? explode(',',$ClientConfigData['FlooreRackIds']):[]; //anil;
$PackagingStoreIds = !empty($ClientConfigData['PackagingStoreIds']) ? trim($ClientConfigData['PackagingStoreIds']):[]; //anil
$DisabledABStatus = !empty($ClientConfigData['DisabledABStatus']) ? trim($ClientConfigData['DisabledABStatus']):0; //anil

$api = API_STORE_RACK_DETAILS;

$bodyContent = array(
    "BlockID" => ""
);

$resRackDetails = CallAPI($api, "GET", "", $bodyContent);

$resStoresData = $resRackDetails->StoresData;
$resBlockData = $resRackDetails->BlockData;

$api = API_COLDSTORE_DROPDOWNS;

$bodyContent = array(
    'page'=>'daily-production-list',
    'exclude_list'=>array("StockAvailableVariety","ShipoutPoNumber")
);
$resList = callAPI($api, "GET", "", $bodyContent);
$resPlant = $resList->Data->Plant;
$resColdStores = $resList->Data->ColdStores;
$resGroups = $resList->Data->InwardGroups;


$resStockBelongsTo = $resList->Data->StockBelongsTo;
$resPackingStyle = $resList->Data->PackingStyle;
$resGrade = $resList->Data->Grade;
$resVariety = $resList->Data->StockInVarietyList;
$resProductCategory = $resList->Data->ProductCategory;
$resSoaking = $resList->Data->Soaking;
$resFreezingType = $resList->Data->FreezingType;
$resPackingType = $resList->Data->PackingType;
$resAbstatus = $resList->Data->Abstatus;
$resShifts = $resList->Data->Shifts;
$resPoNumber = $resList->Data->PoNumber;

// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
$ProductionPlant = $_SESSION['UnitID'];
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$IsCsSelectionDisable = $ArrayClientConfigData['IsCsSelectionDisable'];
// date_default_timezone_set('Asia/Kolkata'); // Set to your preferred time zone
// echo date("Y-m-d");
// echo $IsCsSelectionDisable;
// exit;

if(empty($ProductionPlant) && count($resColdStores) == 1){
    $ProductionPlant = 1;
}
?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . 'includes/head.php' ?>

</head>

<?php include_once ROOT . 'includes/body-preloader.php' ?>
<style>
    .mcs-label {
        display: flex;
        align-items: center;
        font-size: 14px;
    }
    .mcs-label input[type="radio"] {
        vertical-align: middle;
        margin-right: 4px;
        margin-top: 0;
    }
</style>
<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . 'includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . 'includes/page-header.php' ?>

                <!-- <?php if (isset($_SESSION['Msg'])) { ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $Status ?>
                    </div>
                <?php $_SESSION['Error'] = null;
                } ?>

                <?php if (isset($_SESSION['Error'])) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $Status ?>
                    </div>
                <?php $_SESSION['Msg'] = null;
                } ?> -->

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <!-- <h4 class="card-title">Bootstrap Validation - Normal</h4> -->
                                <form id="frmInward">
                                    <div class="row div_inward">

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtProductionPlant" class="form-label">Production Plant<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="txtProductionPlant"
                                                id="txtProductionPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPlant as $item) { ?>
                                                    <option value="<?php echo $item->ProcessingStationID; ?>"
                                                        <?php if (count($resPlant) === 1) echo 'selected'; ?>>
                                                        <?php echo $item->StationName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtProductionDate" class="form-label">Production Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtProductionDate" name="txtProductionDate" <?php
                                            if (isset($_GET["ProductionDataID"]) && !empty($_GET["ProductionDataID"])) {
                                                echo "readonly='readonly'";
                                            }
                                            ?> value="<?php echo date("Y-m-d") ?>" max="<?php echo date("Y-m-d") ?>" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpBatchCodes" class="form-label">Batch Codes <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpBatchCodes" id="drpBatchCodes" <?php
                                            if (isset($_GET["ProductionDataID"]) && !empty($_GET["ProductionDataID"])) {
                                                echo "disabled";
                                            }
                                            ?>>
                                                <option value=""> Select</option>

                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTransactionDate" class="form-label">Transaction Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtTransactionDate"
                                                max="<?php echo date("Y-m-d") ?>" name="txtTransactionDate"
                                                value="<?php echo date("Y-m-d") ?>" />
                                        </div>

                                        <!-- <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtProductionPlant" class="form-label">Production Plant <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtProductionPlant" name="txtProductionPlant" value="<?php echo $resProdData->ProductionPlantName ?>" />
                                        </div> -->

                                        <?php if ($IsStoreProductionLinked == 1 && (!isset($_GET["ProductionDataID"]) || empty($_GET["ProductionDataID"]))): ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="drpGroup" class="form-label">Group<span style="color:red">*</span></label>
                                                <select class="drpSelect form-control group" name="drpGroup" id="drpGroup">
                                                    <option value=""> Select</option>
                                                    <?php foreach ($resGroups as $Item) { ?>
                                                    <option value="<?php echo $Item->Name ?>">
                                                        <?php echo $Item->Name ?>
                                                    </option>
                                                <?php } ?>
                                                </select>
                                            </div>
                                        <?php else: ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtGroup" class="form-label">Group<span
                                                    style="color:red">*</span></label>
                                                <input type="text" class="form-control group" id="txtGroup" name="txtGroup"
                                                    value="" />
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($IsStoreProductionLinked == 1 && (!isset($_GET["ProductionDataID"]) || empty($_GET["ProductionDataID"]))): ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpStockBlgsTo">Stock Belongs To<span
                                            style="color:red">*</span></label>
                                            <select class="drpSelect form-control StockBlgsTo" id="drpStockBlgsTo" name="drpStockBlgsTo">
                                                <option value="">Select</option>
                                                <?php foreach ($resStockBelongsTo as $item) { ?>
                                                    <option value="<?php echo $item->TenantID ?>"
                                                        data-processorID="<?php echo $item->ProcessorID; ?>" data-stockholderid="<?php echo $item->TenantID ?>"><?php echo $item->TenantName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            </div>
                                        <?php else: ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtStockBelongs" class="form-label">Stock Belongs to<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control StockBlgsTo" id="txtStockBelongs"
                                                name="txtStockBelongs" value="" />
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($IsStoreProductionLinked == 1 && (!isset($_GET["ProductionDataID"]) || empty($_GET["ProductionDataID"]))): ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpShift">Shift</label>
                                            <select class="drpSelect form-control shift" id="drpShift" name="drpShift">
                                                <option value="">Select</option>
                                                <?php foreach ($resShifts as $item) { ?>
                                                    <option value="<?php echo $item->OptID ?>" data-shiftid="<?php echo $item->OptID ?>"
                                                        ><?php echo $item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            </div>
                                        <?php else: ?>    
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtShift" class="form-label">Shift<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control shift" id="txtShift" name="txtShift"
                                                    value="" />
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($IsStoreProductionLinked == 1 && (!isset($_GET["ProductionDataID"]) || empty($_GET["ProductionDataID"]))): ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpPoNumber">Po No</label>
                                            <select class="drpSelect form-control poNo" id="drpPoNumber" name="drpPoNumber">
                                                <option value="">Select</option>
                                                <?php foreach ($resPoNumber as $item) { ?>
                                                    <option value="<?php echo $item->PoID ?>"
                                                        data-plantid="<?php echo $item->ProductionPlantID ?>"
                                                        data-grade="<?php echo $item->Grade ?>"
                                                        data-packingstyleid="<?php echo $item->PackingStyles ?>"><?php echo $item->PoNo ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            </div>
                                        <?php else: ?>       
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtPoNumber" class="form-label">Po Number<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control poNo" id="txtPoNumber" name="txtPoNumber"
                                                    value="" />
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($IsStoreProductionLinked == 1 && (!isset($_GET["ProductionDataID"]) || empty($_GET["ProductionDataID"]))): ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtVariety" class="form-label">Variety <span
                                                    style="color:red">*</span></label>
                                            <input type="input" class="form-control variety" id="txtVariety" name="txtVariety"
                                                placeholder="Click to Select Variety" readonly />
                                            </div>
                                        <?php else: ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtVariety" class="form-label">Variety<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control variety" id="txtVariety" name="txtVariety"
                                                value="" />
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($IsStoreProductionLinked == 1 && (!isset($_GET["ProductionDataID"]) || empty($_GET["ProductionDataID"]))): ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpPckgStyle">Packing Style</label>
                                            <select class="drpSelect form-control pckgStyle" id="drpPckgStyle" name="drpPckgStyle">
                                                <option value="">Select</option>
                                                <?php foreach ($resPackingStyle as $item) { ?>
                                                    <option value="<?php echo $item->PCKID ?>" data-packingstyleid="<?php echo $item->PCKID ?>"
                                                    data-slab_weight="<?php echo $item->SlabWeight ?>"
                                                    data-total_slabs="<?php echo $item->TotalSlabs ?>"><?php echo $item->PackingStyle ?></option>
                                                <?php } ?>
                                            </select>
                                            </div>
                                        <?php else: ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtPackingStyle" class="form-label">Packing Style<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control pckgStyle" id="txtPackingStyle"
                                                name="txtPackingStyle" value="" />
                                        </div>
                                        <?php endif; ?>
                                      
                                        <?php if ($IsStoreProductionLinked == 1 && (!isset($_GET["ProductionDataID"]) || empty($_GET["ProductionDataID"]))): ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpGrade">Grade</label>
                                            <select class="drpSelect form-control grade" id="drpGrade" name="drpGrade">
                                                <option value="">Select</option>
                                                <?php foreach ($resGrade as $item) { ?>
                                                    <option value="<?php echo $item->value ?>"><?php echo $item->value ?></option>
                                                <?php } ?>
                                            </select>
                                            </div>
                                        <?php else: ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtGrade" class="form-label">Grade<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control grade" id="txtGrade" name="txtGrade"
                                                value="" />
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($IsStoreProductionLinked == 1 && (!isset($_GET["ProductionDataID"]) || empty($_GET["ProductionDataID"]))): ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpPackingType">Packing Type</label>
                                            <select class="drpSelect form-control packingType" id="drpPackingType" name="drpPackingType">
                                                <option value="">Select</option>
                                                <?php foreach ($resPackingType as $item) { ?>
                                                    <option value="<?php echo $item->Name ?>"><?php echo $item->Name ?></option>
                                                <?php } ?>
                                            </select>
                                            </div>
                                        <?php else: ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtPackingType" class="form-label">Packing Type<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control packingType" id="txtPackingType"
                                                name="txtPackingType" value="" />
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($IsStoreProductionLinked == 1 && (!isset($_GET["ProductionDataID"]) || empty($_GET["ProductionDataID"]))): ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpFreezingType">Freezing Type</label>
                                            <select class="drpSelect form-control freezingType" id="drpFreezingType" name="drpFreezingType">
                                                <option value="">Select</option>
                                                <?php foreach ($resFreezingType as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode ?>"><?php echo $item->ShortCode ?></option>
                                                <?php } ?>
                                            </select>
                                            </div>
                                        <?php else: ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtFreezingType" class="form-label">Freezing Type<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control freezingType" id="txtFreezingType"
                                                name="txtFreezingType" value="" />
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($IsStoreProductionLinked == 1 && (!isset($_GET["ProductionDataID"]) || empty($_GET["ProductionDataID"]))): ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpSoaking">Soaking</label>
                                            <select class="drpSelect form-control soaking" id="drpSoaking" name="drpSoaking">
                                                <option value="">Select</option>
                                                <?php foreach ($resSoaking as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode ?>"><?php echo $item->ShortCode ?></option>
                                                <?php } ?>
                                            </select>
                                            </div>
                                        <?php else: ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtSoaking" class="form-label">Soaking<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control soaking" id="txtSoaking" name="txtSoaking"
                                                value="" />
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($IsStoreProductionLinked == 1 && (!isset($_GET["ProductionDataID"]) || empty($_GET["ProductionDataID"]))): ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpPDCategory">Product Category<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control pDCategory" id="drpPDCategory" name="drpPDCategory">
                                                <option value="">Select</option>
                                                <?php foreach ($resProductCategory as $item) { ?>
                                                    <option value="<?php echo $item->Name ?>">
                                                        <?php echo $item->Name ?>
                                                    </option>

                                                <?php } ?>
                                            </select>
                                            </div>
                                        <?php else: ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtProductCategory" class="form-label">Product Category<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control pDCategory" id="txtProductCategory"
                                                name="txtProductCategory" value="" />
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($IsStoreProductionLinked == 1 && (!isset($_GET["ProductionDataID"]) || empty($_GET["ProductionDataID"]))): ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpAbstatus">Abstatus</label>
                                            <select class="drpSelect form-control abstatus" id="drpAbstatus" name="drpAbstatus"
                                            <?php echo ($DisabledABStatus == 1) ? 'disabled' : '';?>>
                                                <option value="">Select</option>
                                                <?php
                                                if($DisabledABStatus == 0){
                                                     foreach ($resAbstatus as $item) { ?>
                                                        <option value="<?php echo $item->Name ?>"><?php echo $item->Name ?></option>
                                                    <?php } 
                                                }?>
                                            </select>
                                            </div>
                                        <?php else: ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtAbStatus" class="form-label">AbStatus<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control abstatus" id="txtAbStatus" name="txtAbStatus"
                                                value="" />
                                        </div>
                                        <?php endif; ?>
                                       


                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50"
                                                id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                        </div>

                                            <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTotalWeight" class="form-label">Total Weight (Mcs : <span
                                                    id="msgTotalMcs"></span>)</label>
                                            <input name="txtTotalWeight" class="form-control" rows="1" cols="50"
                                                id="txtTotalWeight" placeholder="Enter Total Weight" readonly="">
                                            </div>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtBalanceWeight" class="form-label">Balance Weight (Mcs : <span
                                                        id="msgBalanceMcs"></span>, LooseBags : <span
                                                        id="msgLooseBags"></span>)</label>
                                                <input name="txtBalanceWeight" class="form-control" rows="1" cols="50"
                                                    id="txtBalanceWeight" placeholder="Enter Balance Weight" readonly="">

                                            </div>

                                             <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBatchNo" class="form-label">BatchCode</label>
                                             <input name="txtBatchNo" class="form-control"
                                                id="txtBatchNo" placeholder="Enter Batch Code"></input>
                                        </div>

                                        

                                    </div>
                                    <div class="row">
                                        <div>
                                            <hr />
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpColdStores">Cold Stores<span
                                                    style="color:red">*</span></label>
                                            <!-- <select class="drpSelect form-control" name="drpColdStores" id="drpColdStores" disabled> -->
                                            <select class="drpSelect form-control" name="drpColdStores"
                                                id="drpColdStores">
                                                <option value=""> Select</option>
                                                <?php foreach ($resColdStores as $Item) { ?>
                                                    <option value="<?php echo $Item->StoreID ?>"
                                                        data-plant="<?php echo $Item->ProductionPlantID ?>">
                                                        <?php echo $Item->StoreName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtMcs" class="form-label mcs-label">Mcs<span
                                                style="color:red">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="txtMcs" name="txtMcs" />
                                            <input type="hidden" id="hdnMcsVal" name="hdnMcsVal" value="" />
                                            <input type="hidden" id="hdnLooseBags" name="hdnLooseBags" value="" />
                                        </div>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <label for="txtLooseBags" class="form-label mcs-label">Loose Bags<span
                                                    style="color:red">*</span>
                                                </label>
                                            <input type="number" class="form-control" id="txtLooseBags"
                                                name="txtLooseBags" style="width: 74%;"/>
                                        </div>


                                       
                                    </div>

                                    <div class="text-center">

                                        <button class="btn btn-primary" name="submit" id="submit"
                                            type="submit">Save</button>
                                        <button class="btn btn-danger" id="btnCancel" type="button">Clear</button>
                                        <a href="daily-production-list" class="btn btn-danger">Close</a>


                                        <input type="hidden" id="hdnProductionDataID"
                                            value="<?php echo isset($_GET["ProductionDataID"]) && !empty($_GET["ProductionDataID"]) ? $_GET["ProductionDataID"] : "" ?>" />
                                        <input type="hidden" id="hdnBarCodeID"
                                            value="<?php echo isset($_GET["BarCodeID"]) && !empty($_GET["BarCodeID"]) ? $_GET["BarCodeID"] : "" ?>" />
                                        <input type="hidden" id="hdnStockID"
                                            value="<?php echo isset($_GET["StockID"]) && !empty($_GET["StockID"]) ? $_GET["StockID"] : "" ?>" />

                                        <input type="hidden" id="hdnBalanceKgs" name="hdnBalanceKgs" value="" />

                                        <span style="float: right;">
                                            <a href="javascript:void(0)" id="aNewPalette"
                                                class="newItem btn btn-success waves-effect waves-light">
                                                <i class="fa fa-plus"></i> New Palette
                                            </a>
                                            <button type="button" id="aNewPlacePalette"
                                                class="newItem btn btn-success waves-effect waves-light">
                                                <i class="fa fa-plus"></i> Place Palette
                                            </button>
                                            <button <?php echo isset($_GET["BarCodeID"]) && !empty($_GET["BarCodeID"]) ? "" : "disabled" ?> id="aViewStore"
                                                class="newItem btn btn-success waves-effect waves-light">
                                                View Store
                                            </button>



                                        </span>

                                    </div>
                                </form>
                            </div>
                        </div>


                        <div id="divDataList"></div>

                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <?php include_once ROOT . 'includes/footer.php' ?>

    </div>
    <!-- end main content-->
    <div class="modal fade" id="modalVarietyList" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Variety List</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div id="modalVariety"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="modalNewPalette" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Place Palette</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="frmPopup">
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" id="hdnBlocks"
                                value="<?php echo htmlspecialchars(json_encode($resBlockData), ENT_QUOTES, 'UTF-8'); ?>" />
                            <div class="col-md-4 mb-4">
                                <label for="drpStoreID" class="form-label">Store ID <span
                                        style="color:red">*</span></label>
                                <select class="form-select drpSelectModal" name="drpStoreID" id="drpStoreID" disabled>
                                    <option value="">Select</option>
                                    <?php
                                    foreach ($resStoresData as $item) {
                                        echo '<option value="' . $item->StoreID . '" data-blocks="' . $item->StoreBlocks . '">' . $item->StoreName . '</option>';
                                    }
                                    ?>
                                    </select>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="drpBlockID" class="form-label">Block ID <span
                                        style="color:red">*</span></label>
                                <select class="form-select drpSelectModal" name="drpBlockID" id="drpBlockID">
                                    <option value="">Select</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="drpRackName" class="form-label">Rack Name<span
                                        style="color:red">*</span></label>
                                <select class="form-select drpSelectModal" name="drpRackName" id="drpRackName">
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">

                        <input type="hidden" id="hdnPopupStockId" value="" />
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="btnPlacePallet" class="btn btn-primary">Place Palette</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
<!-- END layout-wrapper -->


<?php include_once ROOT . 'includes/common-js.php' ?>

<?php include_once WORKING_MODULE . 'modalSupplier.php' ?>


<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>

<script>
    // var barCodeID = "<?php // echo isset($_GET["BarCodeID"]) && !empty($_GET["BarCodeID"]) ? $_GET["BarCodeID"] : ""  
    ?>";

    // var prodID = "<?php echo isset($_GET["ProductionDataID"]) && !empty($_GET["ProductionDataID"]) ? $_GET["ProductionDataID"] : "" ?>";
    // var barID = "<?php echo isset($_GET["BarCodeID"]) && !empty($_GET["BarCodeID"]) ? $_GET["BarCodeID"] : "" ?>";
    // var stockID = "<?php echo isset($_GET["StockID"]) && !empty($_GET["StockID"]) ? $_GET["StockID"] : "" ?>";



    var table;

    $(document).on('keydown', 'input[pattern]', function (e) {
        var input = $(this);
        var oldVal = input.val();
        var regex = new RegExp(input.attr('pattern'), 'g');

        setTimeout(function () {
            var newVal = input.val();
            if (!regex.test(newVal)) {
                input.val(oldVal);
            }
        }, 1);
    });

    function fnShowDataTable() {
        table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: 'InWard List',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }

    function fnGetReqParams(mType) {

        var IsStoreProductionLinked = <?php echo $IsStoreProductionLinked ?>;
        var IsPlantFilter = <?php echo $IsPlantFilter ?>;

        var prodID = $("#hdnProductionDataID").val();
        var barID = $("#hdnBarCodeID").val();
        var stockID = $("#hdnStockID").val();

        if (typeof $("#hdnBarCodeID").val() === "undefined") {
            var hdnBarCodeID = "";
        } else if ($("#hdnBarCodeID").val() === null) {
            var hdnBarCodeID = "";
        } else {
            var hdnBarCodeID = $.trim($("#hdnBarCodeID").val());
        }

        if (typeof $("#hdnStockID").val() === "undefined") {
            var sid = "";
        } else if ($("#hdnStockID").val() === null) {
            var sid = "";
        } else {
            var sid = $.trim($("#hdnStockID").val());
        }

        var pid = $("#drpBatchCodes").val();

        if(IsStoreProductionLinked == 1 && IsPlantFilter == 0){
             var params = {
                methodType: mType,
                prodDataId: prodID != "" ? prodID : pid,
                barCodeID: hdnBarCodeID != "" ? hdnBarCodeID : barID,
                stockId: sid != "" ? sid : stockID,
                isEdit: '1',
                prodDate: $("#txtProductionDate").val(),
                productionPlantID: 0,
            }
        } else {
            var params = {
                methodType: mType,
                prodDataId: prodID != "" ? prodID : pid,
                barCodeID: hdnBarCodeID != "" ? hdnBarCodeID : barID,
                stockId: sid != "" ? sid : stockID,
                isEdit: '1',
                prodDate: $("#txtProductionDate").val(),
                productionPlantID: $("#txtProductionPlant").val(),
            }
        }
        return params;
    }

    function fnDateYMD($dt) {
        var dtParts = $dt.split('-');
        var $date = dtParts[2] + '-' + dtParts[1] + '-' + dtParts[0];
        return $date;
    }

    $(document).ready(function () {
        <?php 
        if($_SESSION['ClientID'] == 'DFLV3'){?>
            if($('#drpShift').val() == ''){
                //$('#drpShift').val('113').trigger('change');
                $("#drpShift option")
                    .filter((i, el) => $(el).text().trim() === "A Shift")
                    .prop("selected", true);

                $("#drpShift").trigger("change");
            }
            if($('#drpPDCategory').val() == ''){
                $('#drpPDCategory').val('G1').trigger('change');
            }
            if($('#drpAbstatus').val() == ''){
                $('#drpAbstatus').val('AB-').trigger('change');
            }
       <?php } ?>

        $("#txtMcs").val("");
            $("#hdnMcsVal").val("");
            $("#hdnLooseBags").val("");
        var prdDate = $("#txtProductionDate").val();
        var isCsSelectionDisable = <?php echo $IsCsSelectionDisable ?>;
        var storeId = "<?php echo isset($_GET['StoreId']) ? $_GET['StoreId'] : ''; ?>";
        $("#txtTransactionDate").attr("min", prdDate);

        var productionPlant = "<?php echo isset($ProductionPlant) ? $ProductionPlant : ''; ?>";

        if (productionPlant != 0) {
            $("#txtProductionPlant").prop('disabled', true);
            $("#txtProductionPlant").val(productionPlant).trigger('change');
        } else {
            $("#txtProductionPlant").trigger('change');
            $("#txtProductionPlant").prop('disabled', false);
        }

        if (productionPlant != 0) {
            if (isCsSelectionDisable == 1) {
                $("#drpColdStores").prop('disabled', true);
            }
            $("#drpColdStores").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');

        } else {
            if(storeId != ''){
                $("#drpColdStores").prop('disabled', true);
                $("#drpColdStores").val(storeId).trigger('change');
            } else{
                $("#drpColdStores").prop('disabled', false);
                $("#drpColdStores").val("").trigger('change');
            }
        }

        function handleVarietyClick() {
            $('#modalVarietyList').modal('show');
        };

        $("#drpPoNumber").on("change", function() {
            if($("#drpGroup").val() == 'Physical In'){
                $("#txtTotalWeight").val("");
                $("#txtBalanceWeight").val("");

                $("#msgTotalMcs").text("");
                $("#msgBalanceMcs").text("");
                $("#msgLooseBags").text("");

                $("#txtLooseBags").val("0");
                var selectedValue = $(this).val();
                if (selectedValue != "") {
                    $("#txtVariety").off("click", handleVarietyClick);
                        return GetPoBalance(selectedValue, $("#drpGroup").val(), $.trim($("#hdnStockID").val()), '', isEdit = false);
                } else {
                    $("#txtVariety").on("click", handleVarietyClick);
                }
            }
        });
    });

    function fnGetList(prodID, isEdit = "F") {
        var IsStoreProductionLinked = <?php echo $IsStoreProductionLinked ?>;
        var ProductionId = <?php echo (isset($_GET['ProductionDataID']) && !empty($_GET['ProductionDataID']) ? 0 : 1) ?>;
        $.ajax({
            url: 'Ajax/AjaxInward.php',
            type: 'POST',
            data: fnGetReqParams("getList"),
            success: function (data) {


                if (data.Error) {
                    alert(data.Error);
                } else {

                    if (data.HtmlRes) {
                        $("#divDataList").html(data.HtmlRes);
                        fnShowDataTable();
                    } else {
                        $("#divDataList").html("");
                    }

                    if (data.DailyProductionList) {
                        var arylist = data.DailyProductionList;

                        var list = '<option value=""> Select</option>';
                        $.each(arylist, function (key, item) {
                            list += "<option value='" + item.ProductionID + "' >" + item.OurSupc + " - " + item.BatchCode + " - " + item.ProductDescription + "</option>";
                        });

                        var ProductionDataID = $("#hdnProductionDataID").val();
                        if(ProductionDataID == ''){
                            ProductionDataID = "<?php echo isset($_GET['ProductionDataID']) ? $_GET['ProductionDataID'] : ''; ?>";
                        }

                        $('#drpBatchCodes').select2('destroy');
                        $('#drpBatchCodes').html("").html(list); //.val(data.ProductionID);
                        $('#drpBatchCodes').val(ProductionDataID?ProductionDataID:prodID);
                        $('#drpBatchCodes').select2();

                    }

                    if (data.DailyProductionData) {

                        frmData = data.DailyProductionData;

                        // if(isEdit == "T") {
                        $("#txtProductionDate").val(frmData.ProductionDate);
                        // }

                        $('#drpBatchCodes').select2('destroy');
                        $('#drpBatchCodes').val(frmData.ProductionID);
                        $('#drpBatchCodes').select2();

                        if(IsStoreProductionLinked == 1 && ProductionId == 1){
                            $('#drpGroup').select2('destroy');
                            $('#drpGroup').val(frmData.Group);
                            $('#drpGroup').select2();

                            $('#drpStockBlgsTo').select2('destroy');
                            $('#drpStockBlgsTo').val(frmData.StockHolderName).attr("data-stockholderid",frmData.StockHolderID);
                            $('#drpStockBlgsTo').select2();

                            $('#drpShift').select2('destroy');
                            $('#drpShift').val(frmData.Shift).attr("data-shiftid",frmData.ShiftID);
                            $('#drpShift').select2();

                            $('#txtVariety').val(frmData.VarietyID).trigger("change");

                            $('#drpPackingType').select2('destroy');
                            $('#drpPackingType').val(frmData.PackingType);
                            $('#drpPackingType').select2();

                            $('#drpPckgStyle').select2('destroy');
                            $('#drpPckgStyle').val(frmData.PackingStyle).attr("data-packingstyleid",frmData.PackingStyleID);
                            $('#drpPckgStyle').select2();

                            $('#drpFreezingType').select2('destroy');
                            $('#drpFreezingType').val(frmData.FreezingType);
                            $('#drpFreezingType').select2();

                            $('#drpSoaking').select2('destroy');
                            $('#drpSoaking').val(frmData.Soaking);
                            $('#drpSoaking').select2();

                            $('#drpGrade').select2('destroy');
                            $('#drpGrade').val(frmData.Grade);
                            $('#drpGrade').select2();

                            $('#drpPDCategory').select2('destroy');
                            $('#drpPDCategory').val(frmData.ProductCategory);
                            $('#drpPDCategory').select2();

                            $('#drpAbstatus').select2('destroy');
                            $('#drpAbstatus').val(frmData.AbStatus);
                            $('#drpAbstatus').select2();

                            $('#drpPoNumber').select2('destroy');
                            $('#drpPoNumber').val(frmData.PoNo); 
                            $('#drpPoNumber').select2();
                        }else{
                            $("#txtGroup").val(frmData.Group);
                            $("#txtStockBelongs").val(frmData.StockHolderName).attr("data-stockholderid",frmData.StockHolderID);
                            // $("#txtProductionPlant").val(frmData.ProductionPlantName);
                            $("#txtShift").val(frmData.Shift).attr("data-shiftid",frmData.ShiftID);
                            $("#txtVariety").val(frmData.Variety).attr("data-varietyid", frmData.VarietyID);
                            $("#txtVariety").attr("data-po-grade-id", frmData.PoGradeId);
                            $("#txtBatchNo").val(frmData.BatchCode);

                            $("#txtPackingType").val(frmData.PackingType);
                            $("#txtPackingStyle").val(frmData.PackingStyle).attr("data-packingstyleid",frmData.PackingStyleID).attr("data-slab_weight",frmData.SlabWeight).attr("data-total_slabs",frmData.TotalSlabs);
                            $("#txtFreezingType").val(frmData.FreezingType);
                            $("#txtSoaking").val(frmData.Soaking);
                            $("#txtGrade").val(frmData.Grade);
                            $("#txtProductCategory").val(frmData.ProductCategory);
                            $("#txtAbStatus").val(frmData.AbStatus);
                            $("#txtPoNumber").val(frmData.PoNo).attr("data-poid",frmData.PoID);
                            $('#drpPoNumber').select2();
                       
                        }
                        
                        $("#txtRemarks").val(frmData.Remarks);
                        $("#txtStore").val(frmData.StoreName);
                        $("#txtTotalWeight").val(frmData.Kgs);
                        $("#txtBalanceWeight").val(frmData.BalanceKgs);
                        $("#txtProductionCode").val(frmData.ProductionCode);

                        $("#msgTotalMcs").text(frmData.TotalMcs);
                        $("#msgBalanceMcs").text(frmData.BalanceMcs);
                        $("#msgLooseBags").text(frmData.BalanceLoose);
                        
                         $("#txtBatchCode").val(frmData.BatchCode);

                        $("#txtLooseBags").val("0");

                        // var stocid = '<?php echo isset($_GET["StockID"]) && !empty($_GET["StockID"]) ? $_GET["StockID"] : ""; ?>';
                        // $("#hdnPopupStockId").val(stocid);


                    }

                    var stocid = '<?php echo isset($_GET["StockID"]) && !empty($_GET["StockID"]) ? $_GET["StockID"] : ""; ?>';
                    if (stocid != "") {
                        $("#hdnPopupStockId").val(stocid);
                    }

                    $("#hdnBarCodeID").val(data.BarCodeID);
                    var bId = data.BarCodeID;

                    if (bId == null) {
                        $("#aNewPlacePalette").prop("disabled", true);
                    } else {
                        $("#aNewPlacePalette").prop("disabled", false);
                    }

                    if (data.StockData) {
                        var StockData = data.StockData;
                        // $("#drpColdStores").val(stockData.UnitID).trigger("change");

                         $('#drpGroup').select2('destroy');
                            $('#drpGroup').val(StockData.Group);
                            $('#drpGroup').select2();

                            $('#drpStockBlgsTo').select2('destroy');
                            $('#drpStockBlgsTo').val(StockData.StockHolderID).attr("data-stockholderid",StockData.StockHolderID);
                            $('#drpStockBlgsTo').select2();

                            $('#drpShift').select2('destroy');
                            $('#drpShift').val(StockData.ShiftID).attr("data-shiftid",StockData.ShiftID);
                            $('#drpShift').select2();

                            $('#txtVariety').val(StockData.Variety).trigger("change").attr('data-varietyid',StockData.VarietyID);
                            $("#txtVariety").attr("data-po-grade-id", StockData.PoGradeId);

                            $('#drpPackingType').select2('destroy');
                            $('#drpPackingType').val(StockData.PackingType);
                            $('#drpPackingType').select2();

                            $('#drpPckgStyle').select2('destroy');
                            $('#drpPckgStyle').val(StockData.PackingStyleID).attr("data-packingstyleid",StockData.PackingStyleID);
                            $('#drpPckgStyle').select2();

                            $('#drpFreezingType').select2('destroy');
                            $('#drpFreezingType').val(StockData.FreezingType);
                            $('#drpFreezingType').select2();

                            $('#drpSoaking').select2('destroy');
                            $('#drpSoaking').val(StockData.Soaking);
                            $('#drpSoaking').select2();

                            $('#drpGrade').select2('destroy');
                            $('#drpGrade').val(StockData.Grade);
                            $('#drpGrade').select2();

                            $('#drpPDCategory').select2('destroy');
                            $('#drpPDCategory').val(StockData.ProductCategory);
                            $('#drpPDCategory').select2();

                            $('#drpAbstatus').select2('destroy');
                            $('#drpAbstatus').val(StockData.AbStatus);
                            $('#drpAbstatus').select2();

                            $('#drpPoNumber').select2('destroy');
                            $('#drpPoNumber').val(StockData.PoID);
                            $('#drpPoNumber').select2();

                            if($("#drpGroup").val() == 'Physical In'){
                                GetPoBalance(StockData.PoID, StockData.Group, $.trim($("#hdnStockID").val()), StockData.VarietyID, isEdit = true);
                            }

                        // $("#txtRemarks").val(frmData.Remarks);
                        $("#txtStore").val(StockData.StoreName);
                        $("#txtTotalWeight").val(StockData.Kgs);
                        $("#txtBalanceWeight").val(StockData.BalanceKgs);
                        $("#txtProductionCode").val(StockData.ProductionCode);

                        $("#msgTotalMcs").text(StockData.TotalMcs);
                        $("#msgBalanceMcs").text(StockData.BalanceMcs);
                        $("#msgLooseBags").text(StockData.BalanceLoose);

                        $("#txtLooseBags").val("0");


                        $("#txtMcs").val(StockData.Mcs);
                        $("#hdnMcsVal").val(StockData.Mcs);
                        $("#txtLooseBags").val(StockData.LooseBags);
                        $("#hdnLooseBags").val(StockData.LooseBags);
                        $("#txtRemarks").val(StockData.Remarks);
                         $("#txtBatchNo").val(StockData.InputBatchNo);
                        
                        if (StockData.TransactionDate) {
                            $("#txtTransactionDate").attr("min", StockData.TransactionDate);
                            $("#txtTransactionDate").attr("max", StockData.TransactionDate);
                            $("#txtTransactionDate").val(StockData.TransactionDate);
                        }                       


                        $("html, body").animate({
                            scrollTop: 0
                        }, "slow");

                    } else {

                        // $("#drpColdStores").val(data.UnitID).trigger("change");

                        $("#txtMcs").val("");
                        $("#txtRemarks").val("");
                        $("#txtTapeRolls,#txtStickers,#txtCountSlips").val(0)
                    }
                    var drpAbStatus = "<?php echo ($DisabledABStatus == 1) ? ',#drpAbstatus' : '';?>";
                    if(IsStoreProductionLinked == 1 && ProductionId == 1){
                        $(".div_inward select:not(#drpBatchCodes,#txtProductionPlant"+drpAbStatus+")").prop("disabled", false);
                        // $(".div_inward select:not(#drpBatchCodes,#txtProductionPlant,#drpGroup)").prop("disabled", true);
                        $(".div_inward select").filter("#drpBatchCodes, #txtProductionPlant").prop("disabled", true); // Disable only the selected ones
                    }else{
                        $(".div_inward input:not(#txtProductionDate, #txtTransactionDate)").attr('readonly', true);
                        $(".div_inward select:not(#drpBatchCodes,#txtProductionPlant)").prop("disabled", true);
                    }

                }
                return false;
            }
        });
    }

    $(document).ready(function () {

        fnGetList("");

    });

    $(document).on("change", "#drpPoNumber", function() {
        $("#txtTotalWeight").val("");
        $("#txtBalanceWeight").val("");

        $("#msgTotalMcs").text("");
        $("#msgBalanceMcs").text("");
        $("#msgLooseBags").text("");

        $("#txtLooseBags").val("0");
        $("#drpVariety").val("").trigger("change");
        $("#drpGrade").val("").trigger("change");
        $("#drpFreezingType").val("").trigger("change");
        $("#drpSoaking").val("").trigger("change");
        $("#drpSpecies").val("").trigger("change");
        $("#drpTypeProduct").val("").trigger("change");
        $("#drpPckgStyle").val("").trigger("change");
        $("#drpPackingType").val("").trigger("change");
         $("#txtVariety").val("").attr("data-po-grade-id", "").attr("data-varietyid", "");

        $("#drpAbstatus").val("");
        $("#drpPDCategory").val("");

        $("#txtRemarks").val("");
        $("#txtTotalWeight").val("");
        $("#txtBalanceWeight").val("");
        $("#msgTotalMcs").text("");
        $("#msgBalanceMcs").text("");
        $("#msgLooseBags").text("");
        $("#txtLooseBags").val("");
        $("#txtMcs").val("");
        $("#hdnMcsVal").val("");
        $("#hdnLooseBags").val("");
        $("#divDataList").html("");


        if ($(this).val().length > 0) {
            $("#drpVariety").prop("disabled", true);
            $("#drpGrade").prop("disabled", true);
            $("#drpFreezingType").prop("disabled", true);
            $("#drpSoaking").prop("disabled", true);
            $("#drpTypeProduct").prop("disabled", true);
            $("#drpSpecies").prop("disabled", true);
            $("#drpTypeProduct").prop("disabled", true);
            $("#drpPackingType").prop("disabled", true);
        } else {
            $("#drpVariety").prop("disabled", false);
            $("#drpPckgStyle").prop("disabled", false);
            $("#drpTypeProduct").prop("disabled", false);
            $("#drpGrade").prop("disabled", false);
            $("#drpFreezingType").prop("disabled", false);
            $("#drpSoaking").prop("disabled", false);
            $("#drpSpecies").prop("disabled", false);
            $("#drpTypeProduct").prop("disabled", false);
            $("#drpPackingType").prop("disabled", false);
        }
    });

    $("#txtVariety").on("click", function() {

        getVarieties();
    });
    
    $(document).on("click", ".selectVariety", function() {
        $("#txtTotalWeight").val("");
        $("#txtBalanceWeight").val("");

        $("#msgTotalMcs").text("");
        $("#msgBalanceMcs").text("");
        $("#msgLooseBags").text("");

        $("#txtLooseBags").val("0");
        var selectedVariety = $(this).val();
        $("#txtVariety").val(selectedVariety);

        var selectedVarietyID = $(this).attr("data-po-grade-id");
        $("#txtVariety").attr("data-po-grade-id", selectedVarietyID);

        var selectedVarietyID = $(this).attr("data-varietyid");
        $("#txtVariety").attr("data-varietyid", selectedVarietyID);

        $('#modalVarietyList').modal('hide');


        $("#drpVariety").prop("disabled", true);
        $("#drpGrade").prop("disabled", true);
        $("#drpFreezingType").prop("disabled", true);
        $("#drpSoaking").prop("disabled", true);
        $("#drpSpecies").prop("disabled", true);
        $("#drpTypeProduct").prop("disabled", true);
        $("#drpPackingType").prop("disabled", true);

        var $row = $(this).closest('tr');
        var varietyId = $row.data('varietyid');
        var shortName = $row.find('.tdShortName').text();
        var grade = $row.find('.tdGrade').text();
        var packingStyle = $row.find('.tdPackingStyle').text();
        var packingStyleId = $row.find('.tdPackingStyle').data('packingstyle');
        var freezingType = $row.find('.tdTypeProduct').data('freezingtype');
        var soaking = $row.find('.tdTypeProduct').data('soaking');
        var typeProduct = $row.find('.tdTypeProduct').text();
        var species = $row.find('.tdSpecies').text();

        $("#drpVariety").val(shortName).trigger("change");
        $("#drpGrade").val(grade).trigger("change");
        $("#drpPckgStyle").val(packingStyleId).attr("data-packingstyleid",packingStyleId).trigger("change");
        $("#drpFreezingType").val(freezingType).trigger("change");
        $("#drpSoaking").val(soaking).trigger("change");
        $("#drpSpecies").val(species).trigger("change");
        $("#drpTypeProduct").val(typeProduct).trigger("change");

        var poNumber = $("#drpPoNumber").val();
        if (poNumber != '') {
            $("#drpPackingType").val("MASTER").trigger("change");
        } else {
            $("#drpPackingType").val("DUMMY").trigger("change");
        }
        if (poNumber != '') {
            if($("#drpGroup").val() == 'Physical In'){
                return GetPoBalance(poNumber, $.trim($("#drpGroup").val()), $.trim($("#hdnStockID").val()), varietyId, isEdit = false);
            }
        }

    });
    function GetPoBalance(poNumber, group, hdnStockID = '',  varietyId = '', isEdit = ''){
        var frmData = {
            methodType: "getPoBalance",
            poNumber: poNumber,
            variety: varietyId,
            drpGroup: group,
            hdnStockID: hdnStockID,
        }

        $.ajax({
            url: 'Ajax/AjaxInward.php',
            type: 'POST',
            data: frmData,
            success: function (data) {

                if (data.Error) {
                    alert(data.Error);
                } else {
                    $("#txtTotalWeight").val(data.KGS);
                    $("#txtBalanceWeight").val(data.BalanceKgs);

                    $("#msgTotalMcs").text(data.MCS);
                    $("#msgBalanceMcs").text(data.BalanceMcs);
                    $("#msgLooseBags").text(data.BalanceLooseBags);
                    if(isEdit != true){                                           
                        $("#txtLooseBags").val("0");
                    }
                }
            }
        });
    }

    $(document).ready(function () {

    $("#txtBatchNo") .prop("disabled", true)
        .closest(".col-md-6, .col-lg-3").hide();

    $("#drpGroup").on("change", function () {

        var selectedGroup = $.trim($(this).val());

        if (selectedGroup === "Physical In") {
            $("#txtBatchNo").prop("disabled", false)
                .closest(".col-md-6, .col-lg-3").show();

        } else {

            $("#txtBatchNo").val("") .prop("disabled", true)
                .closest(".col-md-6, .col-lg-3").hide();
        }
    });

});



    $("#drpGroup").on("change", function () {
        $("#txtTotalWeight").val("");
        $("#txtBalanceWeight").val("");

        $("#msgTotalMcs").text("");
        $("#msgBalanceMcs").text("");
        $("#msgLooseBags").text("");

        $("#txtLooseBags").val("0");
        var group = $(this).val();
        $("#drpVariety").val("").trigger("change");
        $("#drpGrade").val("").trigger("change");
        $("#drpFreezingType").val("").trigger("change");
        $("#drpSoaking").val("").trigger("change");
        $("#drpSpecies").val("").trigger("change");
        $("#drpTypeProduct").val("").trigger("change");
        $("#drpPckgStyle").val("").trigger("change");
        $("#drpPackingType").val("").trigger("change");
        $("#txtVariety").val("");
        $("#txtVariety").attr("data-varietyid", "");
        $("#txtVariety").attr("data-po-grade-id", "");
        
        <?php 
        if($_SESSION['ClientID'] == 'DFLV3'){?>
            if($('#drpPDCategory').val() == ''){
                $('#drpPDCategory').val('G1').trigger('change');
            }
            if($('#drpAbstatus').val() == ''){
                $('#drpAbstatus').val('AB-').trigger('change');
            }
       <?php }else{ ?>
            $("#drpAbstatus").val("").trigger("change");;
            $("#drpPDCategory").val("").trigger("change");;
        <?php }?>
        $("#txtRemarks").val("");
        $("#txtTotalWeight").val("");
        $("#txtBalanceWeight").val("");
        $("#msgTotalMcs").text("");
        $("#msgBalanceMcs").text("");
        $("#msgLooseBags").text("");
        $("#txtLooseBags").val("");
        $("#txtMcs").val("");
        $("#hdnMcsVal").val("");
        $("#hdnLooseBags").val("");
       if(group == 'Return Container'){

        var frmData = {
                methodType: "getPoNumber",
                txtProductionPlant: $.trim($("#txtProductionPlant").val()),
            }

            $.ajax({
                url: 'Ajax/AjaxInward.php',
                type: 'POST',
                data: frmData,
                success: function (data) {

                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        var $drpPoNumber = $("#drpPoNumber");

                        // Remove all options except the first one ("Select")
                        $drpPoNumber.find("option:not(:first)").remove();
                        if(data && data.length > 0){
                        // Loop through the data and append new options
                        $.each(data, function (index, item) {
                            $drpPoNumber.append($('<option>', {
                                value: item.PoID,
                                text: item.PoNo,
                                'data-plantid': item.ProductionPlantID,
                                'data-grade': item.Grade,
                                'data-packingstyleid': item.PackingStyles
                            }));
                        });
                    }
                    }
                }
            });
       }else{

            var result = <?php echo json_encode($resPoNumber) ?>;
            var $drpPoNumber = $("#drpPoNumber");

            // Remove all options except the first one ("Select")
            $drpPoNumber.find("option:not(:first)").remove();
            $.each(result, function (index, item) {
                $drpPoNumber.append($('<option>', {
                    value: item.PoID,
                    text: item.PoNo,
                    'data-plantid': item.ProductionPlantID,
                    'data-grade': item.Grade,
                    'data-packingstyleid': item.PackingStyles
                }));
            });
       }
    });

    // completed
    $("#txtProductionDate").on("change", function () {
        $("#txtTotalWeight").val("");
        $("#txtBalanceWeight").val("");

        $("#msgTotalMcs").text("");
        $("#msgBalanceMcs").text("");
        $("#msgLooseBags").text("");

        $("#txtLooseBags").val("0");
        // $("#hdnProductionDataID").val("");
        $('#drpBatchCodes').select2('destroy');
        $('#drpBatchCodes').val("");
        $('#drpBatchCodes').select2();
        fnGetList("");

        var prdDate = $("#txtProductionDate").val();
        $("#txtTransactionDate").attr("min", prdDate);
    });

    $("#txtProductionPlant").on("change", function () {
        $("#txtTotalWeight").val("");
        $("#txtBalanceWeight").val("");

        $("#msgTotalMcs").text("");
        $("#msgBalanceMcs").text("");
        $("#msgLooseBags").text("");

        $("#txtLooseBags").val("0");
        fnGetList("");
    });

    $("#drpBatchCodes").on("change", function () {
        $("#txtTotalWeight").val("");
        $("#txtBalanceWeight").val("");

        $("#msgTotalMcs").text("");
        $("#msgBalanceMcs").text("");
        $("#msgLooseBags").text("");

        $("#txtLooseBags").val("0");
        var prodID = $(this).val();
        // var productionPlant = $("#txtProductionPlant").val();
        // console.log('Hello productionPlant ' + productionPlant);
        // $("#drpColdStores").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');
        if (prodID.length > 0) {
            fnGetList(prodID);
        }
    });

    $.validator.addMethod("notBothZero", function (value, element) {
        var txtMcs = $('#txtMcs').val();
        var txtLooseBags = $('#txtLooseBags').val();
        return !(txtMcs == 0 && txtLooseBags == 0);
    });

    $("#frmInward").validate({
        onkeyup: false,
        onfocusout: false,
        invalidHandler: function (form, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var firstInvalidElement = $(validator.errorList[0].element);
                //$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
                firstInvalidElement.focus();
            }
        },
        ignore: ":disabled",
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass(errorClass).addClass('valid');
            $(":disabled").removeClass(errorClass).addClass('valid');
        },
        rules: {
            txtProductionDate: {
                required: true,
                noSpace: true
            },
            txtTransactionDate: {
                required: true,
                noSpace: true
            },
            drpBatchCodes: {
                required: true
            },
            drpGroup: {
                required: true
            },
            drpStockBlgsTo: {
                required: true
            },
            drpStore: {
                required: true
            },
            drpProductionPlant: {
                required: true
            },
            drpShift: {
                required: true
            },
            txtProductionCode: {
                required: true,
                noSpace: true
            },
            txtBatchCode: {
                required: true,
                noSpace: true
            },
            txtVariety: {
                required: true
            },
            drpPackingType: {
                required: true
            },
            drpPackingStyle: {
                required: true
            },
            drpGrade: {
                required: true
            },
            txtFreezingType: {
                required: true
            },
            drpSoaking: {
                required: true
            },
            drpPDCategory: {
                required: true
            },
            drpAbStatus: {
                required: true
            },
            drpColdStores: {
                required: true
            },
            txtMcs: {
                required: true,
                noSpace: true,
                notBothZero: true
            },
            txtLooseBags: {
                required: true,
                noSpace: true,
                notBothZero: true
            }
        },
        errorPlacement: function () {
            return false; // suppresses error message text
        },
        submitHandler: function () {

            // console.log($("#hdnBarCodeID").val());
            var poGId = $.trim($("#drpVariety").find('option:selected').attr("data-po-grade-id"));
            var IsStoreProductionLinked = <?php echo $IsStoreProductionLinked ?>;
            var ProductionId = <?php echo (isset($_GET['ProductionDataID']) && !empty($_GET['ProductionDataID']) ? 0 : 1) ?>;

            if (typeof $("#hdnBarCodeID").val() === "undefined") {
                var hdnBarCodeID = "";
            } else if ($("#hdnBarCodeID").val() === null) {
                var hdnBarCodeID = "";
            } else {
                var hdnBarCodeID = $.trim($("#hdnBarCodeID").val());
            }

            var stockholderID;
            if ($("#drpStockBlgsTo").length > 0) {
                stockholderID = $.trim($("#drpStockBlgsTo option:selected").data("stockholderid"));
            } else {
                stockholderID = $.trim($("#txtStockBelongs").attr("data-stockholderid"));
            }

            var shiftId;
            if ($("#drpShift").length > 0) {
                shiftId = $.trim($("#drpShift option:selected").data("shiftid"));
            } else {
                shiftId = $.trim($("#txtShift").first().attr("data-shiftid"));
            }

            var pckgStyle;
            if ($("#drpPckgStyle").length > 0) {
                pckgStyle = $.trim($("#drpPckgStyle option:selected").data("packingstyleid"));
            } else {
                pckgStyle = $.trim($("#txtPackingStyle").first().attr("data-packingstyleid"));
            }
            var drpAbstatus;
            if ($("#drpAbstatus").length > 0) {
                drpAbstatus = $.trim($("#drpAbstatus option:selected").val());
            } else {
                drpAbstatus = $.trim($("#txtAbStatus").first().val());
            }

            var drpPoNo;
            if ($("#drpPoNumber").length > 0) {
                drpPoNo = $.trim($("#drpPoNumber option:selected").val());
            } else {
                drpPoNo = $.trim($("#txtPoNumber").first().attr("data-poid"));
            }
            var poGId = $.trim($("#txtVariety").attr("data-po-grade-id"));
            var IsDeductGlaze = "<?php echo $IsDeductGlaze ?>";
            var prodID = $("#hdnProductionDataID").val();
            var pid = $("#drpBatchCodes").val();
            var frmData = {
                methodType: "create",
                stockID: $.trim($("#hdnStockID").val()),
                barCodeID: hdnBarCodeID,
                txtProductionPlant: $.trim($("#txtProductionPlant").val()),
                txtProductionDate: $.trim($("#txtProductionDate").val()),
                txtTransactionDate: $.trim($("#txtTransactionDate").val()),
                ProductionID: prodID != '' ? $.trim(prodID) : $.trim(pid),  
                txtRemarks: $.trim($("#txtRemarks").val()),
                drpColdStores: $.trim($("#drpColdStores").val()),
                txtMcs: $.trim($("#txtMcs").val()),
                txtLooseBags: $.trim($("#txtLooseBags").val()),
                drpGroup: $.trim($(".group").first().val()),
                StockBlgsTo: stockholderID,
                shift: shiftId,
                poNo: drpPoNo,
                poGradeId: poGId,
                variety: $.trim($(".variety").first().attr("data-varietyid")),
                pckgStyle: pckgStyle,
                grade: $.trim($(".grade").first().val()),
                packingType: $.trim($(".packingType").first().val()),
                freezingType: $.trim($(".freezingType").first().val()),
                soaking: $.trim($(".soaking").first().val()),
                pDCategory: $.trim($(".pDCategory").first().val()),
                txtBatchNo: $.trim($("#txtBatchNo").val()),
                
                abstatus: drpAbstatus,
                IsDeductGlaze: IsDeductGlaze,
                IsStoreProductionLinked: IsStoreProductionLinked
            }

            $.ajax({
                url: 'Ajax/AjaxInward.php',
                type: 'POST',
                data: frmData,
                success: function (data) {

                    if (data.Error) {
                        alert(data.Error);
                    } else {

                        frmData = data.DailyProductionData;
                        frmStockData = data.StockData;

                        if (data.HtmlRes) {
                            $("#divDataList").html(data.HtmlRes);
                            fnShowDataTable();
                        } else {
                            $("#divDataList").html("");
                        }

                        if(IsStoreProductionLinked != 1 || ProductionId != 1){
                        $("#txtMcs, #txtLooseBags").val("");
                        $("#drpColdStores").prop("disabled", true);


                        $("#txtProductionDate").removeAttr("readonly");
                        $("#drpBatchCodes").prop("disabled", false);
                        $("#hdnBalanceKgs").val(data.BalanceKgs);
                        // var balanceKgs = data.BalanceKgs;
                        // $("#txtBalanceWeight").val(balanceKgs);
                        $("#txtBalanceWeight").val(frmData.BalanceKgs);
                        $("#msgBalanceMcs").text(frmData.BalanceMcs);
                        $("#msgLooseBags").text(frmData.BalanceLoose);
                        }

                        $("#hdnStockID").val("");
                        $("#submit").text("Save");
                        $("#txtRemarks").val("");
                         $("#txtBatchNo").val("");
                        
                        $("#hdnBarCodeID").val(data.BarCodeID);
                        $('.group').val(data.Group).trigger("change");;
                        var bidId = data.BarCodeID;

                        if (bidId == null) {
                            $("#aNewPlacePalette").prop("disabled", true);
                        } else {
                            $("#aNewPlacePalette").prop("disabled", false);
                        }

                        $("#hdnPopupStockId").val(frmStockData.StockID);
                        if (confirm('Data Saved Successfully, Are you want to Place Palette ?')) {
                            $("#aNewPlacePalette").trigger("click");
                        }

                    }



                    return false;
                }
            });
        }
    });

    $(document).on("click", ".btnEdit", function () {

        $("#txtProductionDate").attr("readonly", "readonly");
        $("#drpBatchCodes").prop("disabled", true);

        var sId = $(this).attr("data-stockid");
        var tdMcs = $(this).closest("tr").find(".tdMcs").text();
        var tdLooseBags = $(this).closest("tr").find(".tdLooseBags").text();
        $("#hdnStockID, #hdnPopupStockId").val(sId);

        fnGetList("");
        $("#submit").text("Update");
        $("html, body").animate({
            scrollTop: 0
        }, "slow");
    });


    $(document).on("click", ".btnDelete", function () {
        var stockID = $(this).attr("data-stockid");
        //var productionID = $("#drpBatchCodes").val();
        var barCodeID = $("#hdnBarCodeID").val();
        var prodID = $("#hdnProductionDataID").val();
        var pid = $("#drpBatchCodes").val();
        if (confirm("Are you sure you want to delete this item ?") == true) {

            $.ajax({
                url: 'Ajax/AjaxInward.php',
                type: 'POST',
                data: {
                    methodType: "deleteData",
                    stockID: stockID,
                    productionID: prodID != '' ?prodID : pid,
                    barCodeID: barCodeID,
                },
                success: function (data) {
                    // alert(data.Msg);


                    if (data.Error) {
                        alert(data.Error);
                    } else {

                        alert(data.Msg);

                        frmData = data.DailyProductionData;
                        frmStockData = data.StockData;

                        if (data.HtmlRes) {
                            $("#divDataList").html(data.HtmlRes);
                            fnShowDataTable();
                        } else {
                            $("#divDataList").html("");
                        }

                        $("#txtMcs, #txtLooseBags").val("");
                        $("#drpColdStores").prop("disabled", true);


                        $("#txtProductionDate").removeAttr("readonly");
                        $("#drpBatchCodes").prop("disabled", true);
                        // $("#drpGroup").val("").trigger("change");
                        // $("#drpStockBlgsTo").val("").trigger("change");
                        // $("#drpShift").val("").trigger("change");
                        // $("#drpPoNumber").val("").trigger("change");
                        // $("#txtVariety").val("").trigger("change");
                        $("#hdnBalanceKgs").val(data?.BalanceKgs ? data.BalanceKgs : "0");
                        $("#txtBalanceWeight").val(frmData?.BalanceKgs ? frmData.BalanceKgs : "0");
                        $("#msgBalanceMcs").text(frmData?.BalanceMcs ? frmData.BalanceMcs : "0");
                        $("#msgLooseBags").text(frmData?.BalanceLoose ? frmData.BalanceLoose : "0");
                        $("#hdnStockID").val("");
                        $("#submit").text("Save");
                        $("#txtRemarks").val("");
                        $("#txtBatchNo").val("");
                        
                        $("#hdnBarCodeID").val(data?.BarCodeID ? data.BarCodeID : "");
                        // window.location.reload(true);
                        var bidId = data.BarCodeID;

                        if (bidId == null) {
                            $("#aNewPlacePalette").prop("disabled", true);
                        } else {
                            $("#aNewPlacePalette").prop("disabled", false);
                        }

                        // $("#hdnPopupStockId").val(frmStockData.StockID);

                        // if (confirm('Data Saved Successfully, Are you want to Place Pallet ?')) {
                        //     $("#aNewPlacePalette").trigger("click");
                        // }

                    }



                    return false;
                }
            });
        }

    });

    $(document).on("click", "#btnCancel", function () {
        $("#hdnStockID").val("");
        $("#txtRemarks, #txtMcs, #txtLooseBags").val("");
        // $("#drpColdStores").val("").trigger("change").prop("disabled", false);

        $("#txtProductionDate").removeAttr("readonly");
        $("#drpBatchCodes").prop("disabled", false);
        $("#submit").text("Save");
    });
</script>

<script>
    $('.drpSelectModal').select2({
        dropdownParent: $('#modalNewPalette')
    });

    $(document).on("click", "#aNewPalette", function () {

        $("#txtMcs, #txtLooseBags").val("");
        $("#hdnBarCodeID, #hdnBalanceKgs").val("");
        $("#divDataList").html("");
        $("#aNewPlacePalette").prop("disabled", true);
    });
    $(document).on("click", "#aViewStore", function () {

        var Url = 'view-store-layout?BarCodeId=' + $("#hdnBarCodeID").val(); //uma
        window.open(Url, '_blank'); //uma
    });

    $(document).on("click", "#aNewPlacePalette", function () {
        var storeID = $("#drpColdStores").val();
        $("#drpStoreID").val(storeID).trigger('change');
        $('#modalNewPalette').modal('show');


    })

    $(document).on("change", "#drpStoreID", function () {
        var blocks = $("option:selected", this).attr("data-blocks");
        var blockArray = blocks.split(',');

        var blocksList = JSON.parse($("#hdnBlocks").val());

        var filteredArray = $.grep(blocksList, function (element) {
            return $.inArray(element.BlockID, blockArray) !== -1;
        });
        /*packing store starts*/ 
        var PackagingStoreIdsArr = <?php echo json_encode(!is_array($PackagingStoreIds) ? explode(',',$PackagingStoreIds) :[]);?>;
        /*packing store ENDS*/ 
        var listBlock = "<option value=''>Select</option>";
        $.each(filteredArray, function (index, val) {
            /*packing store starts*/ 
            if(PackagingStoreIdsArr.length !=0){
                if ($.inArray(val.BlockID, PackagingStoreIdsArr) === -1) {
                    listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
                }
                /*packing store Ends*/ 
            }else{
                listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
            }
        });

        $("#drpBlockID").html(listBlock);
        $("#drpRackName").html("<option value=''>select</option>");
    });

    $(document).on("change", "#drpBlockID", function () {
        var blockid = $(this).val();

        if (blockid != "") {
            $.ajax({
                url: 'Ajax/AjaxInward.php',
                type: 'POST',
                data: {
                    methodType: "getRackList",
                    blockID: $.trim($(this).val()),
                    storeID: $.trim($("#drpStoreID").val())
                },
                success: function (data) {
                    var listFloor = "<option value=''>select</option>";
                    $.each(data, function (index, val) {
                        if (val.IsPath != "1") {
                            if (val.Status == "1") {
                                listFloor += '<option value="' + val.RackID + '">' + val.RackName + " (Filled)" + '</option>';
                            } else {
                                listFloor += '<option value="' + val.RackID + '">' + val.RackName + '</option>';
                            }
                        }
                    });

                    $("#drpRackName").html(listFloor);

                    return false;
                }
            });
        } else {
            $("#drpRackName").html("<option value=''>select</option>");
        }

    })
        <?php 
    if(!empty($StoreRackMaxMcs)){?>
        var FlooreRackIdsArr = <?php echo json_encode($FlooreRackIds);?>;
        
         $(document).on("change", "#drpRackName", function () {
            let IsNotFlooreIds = true;
            if(FlooreRackIdsArr.length !=0){
                if($.inArray($(this).val(), FlooreRackIdsArr) === -1){//not in
                    IsNotFlooreIds = true;
                }else{//in
                    IsNotFlooreIds = false;   
                }
            }
            if($(this).val() && $('#drpBlockID').val() && IsNotFlooreIds){
                $.ajax({
                    url: 'Ajax/AjaxInward.php',
                    type: 'POST',
                    data: {
                        methodType: "validateRackId",
                        rackId: $.trim($(this).val()),
                        StoreRackMaxMcs : '<?php echo $StoreRackMaxMcs;?>',
                        BlockID: $.trim($('#drpBlockID').val()),
                        //storeID: $.trim($("#drpStoreID").val()),
                        StockId: $.trim($("#hdnPopupStockId").val())
                    },
                    success: function (res) {
                        if(res.Error == null){
                            $('#btnPlacePallet').prop('disabled', false);
                        }else{
                            alert(res.Error);
                            $('#btnPlacePallet').prop('disabled', true);
                            $('#drpRackName').val(null).trigger('change');
                        }
                    }
                })
            }else{
                $('#btnPlacePallet').prop('disabled', false);
            }
         })
   <?php } ?>

    $("#frmPopup").validate({
        onkeyup: false,
        onfocusout: false,
        invalidHandler: function (form, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var firstInvalidElement = $(validator.errorList[0].element);
                firstInvalidElement.focus();
            }
        },
        ignore: "",
        rules: {
            drpStoreID: {
                required: true
            },
            drpBlockID: {
                required: true
            },
            drpRackName: {
                required: true
            }
        },
        errorPlacement: function () {
            return false; // suppresses error message text
        },
        submitHandler: function () {

            $.ajax({
                url: 'Ajax/AjaxInward.php',
                type: 'POST',
                data: {
                    methodType: "placePalette",
                    // barCodeID: $.trim($("#hdnBarCodeID").val()),
                    stockID: $.trim($("#hdnPopupStockId").val()),
                    rackID: $.trim($("#drpRackName").val())
                },
                success: function (data) {
                    $('#modalNewPalette').modal('hide');

                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);
                        window.location.href = 'inward?ProductionDataID=' + $("#drpBatchCodes").val();
                    }

                    return false;
                }
            });
        }
    });


    $("#txtLooseBags").on("keyup", function() {
        var val = parseInt($(this).val()) || 0;
        var loose = parseInt($.trim($("#msgLooseBags").text()));
        var stockID = $("#hdnStockID").val();
        var editLoose = parseInt($("#hdnLooseBags").val()) || 0;
        var balMcs = parseInt($.trim($("#msgBalanceMcs").text())) || 0;
        var txtMcs = parseInt($('#txtMcs').val()) || 0;
        var drpGroup = $('#drpGroup').val();

        balMcs = balMcs - txtMcs;
       // alert(balMcs)

        if($('#drpPckgStyle').length != 0){
            var SlabWeight = parseInt($("#drpPckgStyle option:selected").attr("data-slab_weight")) || 0;
            var TotalSlabs = parseInt($("#drpPckgStyle option:selected").attr("data-total_slabs")) || 0;
        }else{
            var SlabWeight = parseInt($("#txtPackingStyle").attr("data-slab_weight")) || 0;
            var TotalSlabs = parseInt($("#txtPackingStyle").attr("data-total_slabs")) || 0;
        }
        if($('#drpPoNumber').length != 0){
            var PoId = $('#drpPoNumber').val();    
        }else{
            var PoId = $('#txtPoNumber').val();                      
        }
        // var balMcsSlabs = parseInt((balMcs * TotalSlabs) + parseInt(loose) || 0) || 0;
        // if(drpGroup == 'Physical In'){
        //     if(PoId){
        //         if (stockID != '') {
        //             if (val > balMcsSlabs + editLoose) {
        //                 $(this).val("");
        //                 alert("Loose Bags Should Less than Loose Bags");
        //             }
        //         } else {

        //             if (val > balMcsSlabs) {
        //                 $(this).val("");
        //                 alert("Loose Bags Should Less than Loose Bags");
        //             }
        //         }
        //     }
        // }else{
        //     if (stockID != '') {
        //         if (val > loose + editLoose) {
        //             $(this).val("");
        //             alert("Loose Bags Should Less than Loose Bags");
        //         }
        //     } else {

        //         if (val > loose) {
        //             $(this).val("");
        //             alert("Loose Bags Should Less than Loose Bags");
        //         }
        //     }
        // }
        
    });
    

    $("#txtMcs").on("keyup", function () {
        var val = parseInt($(this).val());
        var mcs = parseInt($.trim($("#msgBalanceMcs").text()));
        var stockID = $("#hdnStockID").val();
        var editVal = parseInt($("#hdnMcsVal").val());

        var drpGroup = $('#drpGroup').val();
        var loose = parseInt($.trim($("#msgLooseBags").text())) || 0;
        var balMcs = parseInt(mcs) || 0;
        var txtLooseBags = parseInt($('#txtLooseBags').val()) || 0;

        balMcs = balMcs;//94
        //alert(balMcs)

        if($('#drpPckgStyle').length != 0){
            var SlabWeight = parseInt($("#drpPckgStyle option:selected").attr("data-slab_weight")) || 0;
            var TotalSlabs = parseInt($("#drpPckgStyle option:selected").attr("data-total_slabs")) || 0;
        }else{
            var SlabWeight = parseInt($("#txtPackingStyle").attr("data-slab_weight")) || 0;
            var TotalSlabs = parseInt($("#txtPackingStyle").attr("data-total_slabs")) || 0;
        }
        if($('#drpPoNumber').length != 0){
            var PoId = $('#drpPoNumber').val();    
        }else{
            var PoId = $('#txtPoNumber').val();                      
        }
        // var balMcsSlabs = parseInt((balMcs * TotalSlabs) + parseInt(loose) || 0) || 0;//474
        // balMcsSlabs = parseInt(balMcsSlabs - txtLooseBags) || 0;
        // if(drpGroup == 'Physical In'){
        //     if(PoId){//val = txtmcs
        //         val = parseInt((val * TotalSlabs)) || 0;//
        //         //alert(val +'>'+ balMcsSlabs)
        //         if (stockID != '') {
        //             if (val > balMcsSlabs + editLoose) {
        //                 $(this).val("");
        //                 alert("Mcs Should Less than Aval Mcs");
        //             }
        //         } else {

        //             if (val > balMcsSlabs) {
        //                 $(this).val("");
        //                 alert("Mcs Should Less than Aval Mcs");
        //             }
        //         }
        //     }
        // }else{
        //     if (stockID != '') {

        //         if (val > mcs + editVal) {
        //             $(this).val("");
        //             alert("Mcs Should Less than Aval Mcs");
        //         }
        //     } else {
        //         // mcs = mcs;
        //         if (val > mcs) {
        //             $(this).val("");
        //             alert("Mcs Should Less than Aval Mcs");
        //         }
        //     }
        // }



        // if ()
        // if (val > mcs) {
        //     $(this).val("");
        //     alert("Mcs Should Less than Aval Mcs");
        // }
    });
    function getVarieties() {
        $.ajax({
            type: 'POST',
            url: 'Ajax/AjaxDailyProductionData.php',
            data: {
                'methodType': 'poNumber',
                'pOID': $("#drpPoNumber").val(),
                'packingType': $("#drpPackingType").val(),
            },
            success: function(data) {

                if (data) {
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        $('#modalVariety').html("").html(data.HtmlRes);
                        $('#modalVarietyList').modal('show');
                    }
                }
            }
        });
    }
</script>

</body>

</html>