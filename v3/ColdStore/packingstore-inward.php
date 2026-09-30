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
$PackagingStoreIds = !empty($ClientConfigData['PackagingStoreIds']) ? trim($ClientConfigData['PackagingStoreIds']):[]; //anil

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
    'exclude_list'=>array("StockAvailableVariety","ShipoutPoNumber","PoNumberNew",
    'StockBelongsTo','Shifts','FreezingType','Soaking','ProductCategory',
    'Abstatus','StockInVarietyList','InwardGroups')
);
$resList = callAPI($api, "GET", "", $bodyContent);
$resPlant = $resList->Data->Plant;
$resColdStores = $resList->Data->ColdStores;
// $resGroups = $resList->Data->Variety;
$resGroups = [
    (object) ['Name' => 'Mcs'],
    (object) ['Name' => 'Pouches'],
    (object) ['Name' => 'Stickers'],
    (object) ['Name' => 'Tape Rolls'],
    (object) ['Name' => 'Spacers'],
    (object) ['Name' => 'Count Slips'],
];
//echo '<pre>';print_r($resList->Data);die;
$resPackingStyle = $resList->Data->PackingStyle;
$resGrade = $resList->Data->Grade;

$resPoNumber = $resList->Data->PoNumber;
//echo '<pre>';print_r($resPoNumber);die;
$Brand = $resList->Data->Brand;
$resPackingType = $resList->Data->PackingType;
//echo '<pre>';print_r($resPackingType);die;
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
$ProductionPlant = $_SESSION['UnitID'];
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$IsCsSelectionDisable = $ArrayClientConfigData['IsCsSelectionDisable'];

$api = API_DAILY_PACKING_STORE_INWARD_DETAILS;

$bodyContent = array(
    'method'=>'getDrpDowns'
);
$resListD = callAPI($api, "GET", "", $bodyContent);
$resSizes = $resListD->Sizes;
$resColours = $resListD->Colours;
$resSlabWeight = $resListD->SlabWeight;
$resVariety = $resListD->Variety;
//echo '<pre>';print_r($resSlabWeight );die;
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
                                            <label for="txtTransactionDate" class="form-label">Inward Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtTransactionDate"
                                                max="<?php echo date("Y-m-d") ?>" name="txtTransactionDate"
                                                value="<?php echo date("Y-m-d") ?>" />
                                        </div>
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
                                        <?php //if ($IsStoreProductionLinked == 1 && (!isset($_GET["ProductionDataID"]) || empty($_GET["ProductionDataID"]))): ?>
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
                                        <?php /* else: ?>       
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtPoNumber" class="form-label">Po Number<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control poNo" id="txtPoNumber" name="txtPoNumber"
                                                    value="" />
                                            </div>
                                        <?php endif; */ ?>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBillNo" class="form-label">Bill Number<span
                                                    style="color:red"></span></label>
                                            <input type="text" class="form-control txtBillNo" id="txtBillNo" name="txtBillNo"
                                                value="" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpPackingType">Packing Type<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control packingType" id="drpPackingType" name="drpPackingType">
                                                <option value="">Select</option>
                                                <?php foreach ($resPackingType as $item) { ?>
                                                    <option value="<?php echo $item->Name ?>"><?php echo $item->Name ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpSlabWeight">SlabWeight</label>
                                            <select class="drpSelect form-control drpSlabWeight" id="drpSlabWeight" name="drpSlabWeight">
                                                <option value="">Select</option>
                                                <?php foreach ($resSlabWeight as $item) { ?>
                                                    <option value="<?php echo $item->Weight ?>" data-packingstyleid="<?php echo $item->Weight ?>"><?php echo $item->Weight ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpVariety">Variety</label>
                                            <select class="drpSelect form-control drpVariety" id="drpVariety" name="drpVariety">
                                                <option value="">Select</option>
                                                <?php foreach ($resVariety as $item) { ?>
                                                    <option value="<?php echo $item->ShortName ?>" ><?php echo $item->ShortName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        
                                        <?php //if ($IsStoreProductionLinked == 1 && (!isset($_GET["ProductionDataID"]) || empty($_GET["ProductionDataID"]))): ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="drpPckgStyle">Packing Style<span
                                                        style="color:red">*</span></label>
                                                <select class="drpSelect form-control pckgStyle" id="drpPckgStyle" name="drpPckgStyle">
                                                    <option value="">Select</option>
                                                    <?php foreach ($resPackingStyle as $item) { ?>
                                                        <option value="<?php echo $item->PCKID ?>" data-packingstyleid="<?php echo $item->PCKID ?>"><?php echo $item->PackingStyle ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        <?php /* else: ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtPackingStyle" class="form-label">Packing Style<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control pckgStyle" id="txtPackingStyle"
                                                    name="txtPackingStyle" value="" />
                                            </div>
                                        <?php endif; */ ?>
                                      
                                        <?php //if ($IsStoreProductionLinked == 1 && (!isset($_GET["ProductionDataID"]) || empty($_GET["ProductionDataID"]))): ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="drpGrade">Grade</label>
                                                <select class="drpSelect form-control grade" id="drpGrade" name="drpGrade">
                                                    <option value="">Select</option>
                                                    <?php foreach ($resGrade as $item) { ?>
                                                        <option value="<?php echo $item->value ?>"><?php echo $item->value ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        <?php /* else: ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtGrade" class="form-label">Grade<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control grade" id="txtGrade" name="txtGrade"
                                                    value="" />
                                            </div>
                                        <?php endif; */ ?>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpBrand">Brand<span style="color:red"></span></label>
                                            <select class="drpSelect form-control Brand" id="drpBrand" name="drpBrand">
                                                <option value="">Select</option>
                                                <?php foreach ($Brand as $item) { ?>
                                                    <option value="<?php echo $item->BrandID ?>">
                                                        <?php echo $item->BrandName ?>
                                                    </option>

                                                <?php } ?>
                                            </select>
                                        </div>
                                        <!-- <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpSizes" class="form-label">Sizes<span
                                                    style="color:red"></span></label>
                                            <select class="drpSelect form-control Sizes" id="drpSizes" name="drpSizes" disabled>
                                                <option value="">Select</option>
                                                <?php 
                                                foreach($resSizes as $val){?>
                                                     <option value="<?php echo trim($val->Value);?>"><?php echo $val->Value;?></option>      
                                               <?php }?>                                                                                        
                                            </select>
                                        </div> -->
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpSizes" class="form-label">Dimention<span
                                                    style="color:red"></span></label>
                                            <input type="text" class="form-control poNo" id="txtDimention" name="txtDimention" readonly
                                                    value="" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpColours" class="form-label">Colours<span
                                                    style="color:red"></span></label>
                                            <select class="drpSelect form-control Colours" id="drpColours" name="drpColours">
                                                <option value="">Select</option> 
                                                <?php 
                                                foreach($resColours as $val){?>
                                                     <option value="<?php echo trim($val->Value);?>"><?php echo $val->Value;?></option>      
                                               <?php }?>                                            
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtSpacers" class="form-label">Spacers<span
                                                    style="color:red"></span></label>
                                            <input type="number" class="form-control" id="txtSpacers"
                                            name="txtSpacers" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtCounts" class="form-label">Counts<span
                                                    style="color:red"></span></label>
                                            <input type="number" class="form-control" id="txtCounts"
                                            name="txtCounts" />
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div>
                                            <hr />
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpColdStores">Cold Stores<span
                                                    style="color:red">*</span></label>
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
                                            <label for="txtMcs" class="form-label">No. of Mcs<span
                                                    style="color:red"></span></label>
                                            <input type="number" class="form-control" id="txtMcs" name="txtMcs" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtLooseBags" class="form-label">No of Pouches<span
                                                    style="color:red"></span></label>
                                            <input type="number" class="form-control" id="txtLooseBags"
                                                name="txtLooseBags" />
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
                                            <!-- <button <?php echo isset($_GET["BarCodeID"]) && !empty($_GET["BarCodeID"]) ? "" : "disabled" ?> id="aViewStore"
                                                class="newItem btn btn-success waves-effect waves-light">
                                                View Store
                                            </button> -->



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
                                    foreach ($resStoresData as $item) {//$PackagingStoreIds
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

        $("#txtMcs").val("");
        var prdDate = $("#txtProductionDate").val();
        var isCsSelectionDisable = <?php echo $IsCsSelectionDisable ?>;
        var storeId = "<?php echo isset($_GET['StoreId']) ? $_GET['StoreId'] : ''; ?>";
       // $("#txtTransactionDate").attr("min", prdDate);

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

    });

    function fnGetList(prodID, isEdit = "F") {
        var IsStoreProductionLinked = <?php echo $IsStoreProductionLinked ?>;
        var ProductionId = <?php echo (isset($_GET['ProductionDataID']) && !empty($_GET['ProductionDataID']) ? 0 : 1) ?>;
        $.ajax({
            url: 'Ajax/AjaxPackingStoreInward.php',
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
                        //if(StockData.PoID == '' || StockData.PoID == null){
                           // $('#aNewPalette,#aNewPlacePalette,#aViewStore').hide()
                        //}
                         $('#drpGroup').select2('destroy');
                            $('#drpGroup').val(StockData.Group);
                            $('#drpGroup').select2();

                            $('#drpPckgStyle').select2('destroy');
                            $('#drpPckgStyle').val(StockData.PackingStyleID).attr("data-packingstyleid",StockData.PackingStyleID);
                            $('#drpPckgStyle').select2();

                            $('#drpGrade').select2('destroy');
                            $('#drpGrade').val(StockData.Grade);
                            $('#drpGrade').select2();

                            $('#drpPackingType').select2('destroy');
                            $('#drpPackingType').val(StockData.PackingTypeID);
                            $('#drpPackingType').select2();

                            $('#drpSlabWeight').select2('destroy');
                            $('#drpSlabWeight').val(StockData.SlabWeight);
                            $('#drpSlabWeight').select2();

                            $('#drpVariety').select2('destroy');
                            $('#drpVariety').val(StockData.Variety);
                            $('#drpVariety').select2();


                            $('#drpPoNumber').select2('destroy');
                            $('#drpPoNumber').val(StockData.PoID);
                            $('#drpPoNumber').select2();
                        // $("#txtRemarks").val(frmData.Remarks);
                        $("#txtStore").val(StockData.StoreName);
                        $("#txtProductionCode").val(StockData.ProductionCode);

                      /*new*/
                         $("#txtBillNo").val(StockData.BillNo);
                         $("#txtDimention").val(StockData.Dimention);

                         $('#drpBrand').select2('destroy');
                        $('#drpBrand').val(StockData.Brand);
                        $('#drpBrand').select2();

                        /*  $('#drpSizes').select2('destroy');
                        $('#drpSizes').val(StockData.Sizes);
                        $('#drpSizes').select2(); */

                         $('#drpColours').select2('destroy');
                        $('#drpColours').val(StockData.Colours);
                        $('#drpColours').select2();

                         $("#txtSpacers").val(StockData.Spacers);
                         $("#txtCounts").val(StockData.Count);
                        /*new*/

                        $("#txtMcs").val(StockData.Mcs);
                        $("#txtLooseBags").val(StockData.LooseBags);
                        if (StockData.TransactionDate) {
                          //  $("#txtTransactionDate").attr("min", StockData.TransactionDate);
                           // $("#txtTransactionDate").attr("max", StockData.TransactionDate);
                            $("#txtTransactionDate").val(StockData.TransactionDate);
                        }

                        $("html, body").animate({
                            scrollTop: 0
                        }, "slow");

                    } else {
                        $("#txtMcs").val("");
                    }
                    <?php 
                    if(isset($_GET['StockID']) || isset($_GET['BarCodeID'])){?>
                        $(".div_inward input:not(#txtProductionDate, #txtTransactionDate,#txtBillNo)").attr('readonly', true);
                        $(".div_inward select:not(#txtProductionPlant)").prop("disabled", true);
                        $('#drpGroup').trigger('change');
                    <?php } ?>
                    /* if(IsStoreProductionLinked == 1 && ProductionId == 1){
                        $(".div_inward select:not(#txtProductionPlant)").prop("disabled", false);
                        // $(".div_inward select:not(#drpBatchCodes,#txtProductionPlant,#drpGroup)").prop("disabled", true);
                        $(".div_inward select").filter("#txtProductionPlant").prop("disabled", true); // Disable only the selected ones
                        $('#drpGroup').trigger('change');
                    }else{
                        $(".div_inward input:not(#txtProductionDate, #txtTransactionDate,#txtBillNo)").attr('readonly', true);
                        $(".div_inward select:not(#txtProductionPlant)").prop("disabled", true);
                        $('#drpGroup').trigger('change');
                    } */

                }
                return false;
            }
        });
    }

    $(document).ready(function () {
        $('#drpPickStyle').attr('inert', true);
        fnGetList("");
    });

    $(document).on("change", "#drpPoNumber", function() {
        getBrandListByPoId($(this).val());
        if($(this).val()){
            $("#drpBrand").prop('required',true)           
        }else{
            $("#drpBrand").prop('required',false)            
        }
        /* if($(this).val() && $('#drpGroup').val() == 'Mcs'){
            $('#drpGrade').prop('disabled',false).prop('required',true).trigger("change");
        }else{
            $('#drpGrade').prop('disabled',true).prop('required',false).trigger("change");
        } */

        $("#txtLooseBags").val("0");
        $("#drpTypeProduct").val("").trigger("change");

        $("#txtMcs,#txtSpacers,#txtCounts,#txtBillNo,#txtDimention").val("");
        $("#drpGrade#drpPckgStyle,#drpSizes,#drpColours").val("").trigger("change");//15
        $("#divDataList").html("");    
    });

    
    $(document).on("change", "#drpGroup", function (e) {
        e.preventDefault()
        //$("#txtLooseBags").val("0");
        var group = $(this).val();
        var txtMcs = $("#txtMcs").val();
        var txtLooseBags = $("#txtLooseBags").val();
        var txtSpacers = $("#txtSpacers").val();
         var drpGrade = $("#drpGrade").val(); 
        var drpSizes = $.trim($("#drpSizes").val());
        var drpColours = $.trim($("#drpColours").val());
        var txtCounts = $("#txtCounts").val();
        var drpPckgStyle = $("#drpPckgStyle").val();
        var drpSlabWeight = $("#drpSlabWeight").val();
        var drpPoNumber = $("#drpPoNumber").val();
        var drpVariety = $("#drpVariety").val();
        var drpPackingType = $("#drpPackingType").val();
        var txtDimention = $.trim($("#txtDimention").val());
        //drpPoNo = $.trim($("#drpPoNumber").attr("data-poid"));
        $("#drpGrade,#drpSizes,#drpColours,#drpPckgStyle,#drpSlabWeight").val("").trigger("change");
        $("#txtLooseBags,#txtMcs,#txtSpacers,#txtCounts, #txtDimention").val("");
        if(group == 'Stickers'){
            //$('#drpSizes').prop('disabled',false).prop('required',true).val(drpSizes).trigger("change");
            $('#drpSizes,#drpColours,#drpSlabWeight,#drpVariety').prop('disabled',true).prop('required',false);
            $('#drpGrade').prop('disabled',false).prop('required',true).val(drpGrade).trigger("change");
            $('#txtMcs,#txtSpacers,#txtLooseBags,#txtDimention').prop('readonly',true).prop('required',false);
            $('#txtCounts').prop('readonly',false).prop('required',true).val(txtCounts);
            $('#drpPckgStyle').prop('disabled',false).prop('required',true).val(drpPckgStyle).trigger("change");
            $('#drpPackingType').prop('disabled',false).prop('required',true).val(drpPackingType).trigger("change");
        }else if(group == 'Count Slips'){
            $('#drpGrade').prop('disabled',false).prop('required',true).val(drpGrade).trigger("change");
            $('#drpSizes,#drpColours,#drpPckgStyle,#drpSlabWeight,#drpVariety').prop('disabled',true).prop('required',false);
            $('#txtMcs,#txtSpacers,#txtLooseBags,#txtDimention').prop('readonly',true).prop('required',false);
            $('#txtCounts').prop('readonly',false).prop('required',true).val(txtCounts);
            $('#drpPackingType').prop('disabled',false).prop('required',true).val(drpPackingType).trigger("change");
        }else if(group == 'Tape Rolls'){
            $('#drpColours').prop('disabled',false).prop('required',true).val(drpColours).trigger("change");
            // $('#drpGrade').prop('disabled',false).prop('required',true).val(drpGrade).trigger("change");
            $('#drpSizes,#drpGrade,#drpSlabWeight,#drpVariety,#drpPckgStyle,#drpPackingType').prop('disabled',true).prop('required',false).val('').trigger("change");
            $('#txtMcs,#txtLooseBags,#txtSpacers,#txtDimention').prop('readonly',true).prop('required',false);
            $('#txtCounts').prop('readonly',false).prop('required',true).val(txtCounts);
            //$('#drpPckgStyle').prop('disabled',false).prop('required',true).val(drpPckgStyle).trigger("change");
        }else if(group == 'Pouches'){
            $('#txtLooseBags').prop('readonly',false).prop('required',true).val(txtLooseBags);
            $('#drpColours,#drpGrade,#drpSizes,#drpPckgStyle').prop('disabled',true).prop('required',false);
            //$('#drpSizes').prop('disabled',false).prop('required',true).val(drpSizes).trigger("change");
            $('#drpSlabWeight').prop('disabled',false).prop('required',true).val(drpSlabWeight).trigger("change");
            $('#txtMcs,#txtSpacers,#txtCounts').prop('readonly',true).prop('required',false);
            $('#txtDimention').prop('readonly',false).prop('required',false).val(txtDimention);
            if(drpPackingType != 'DUMMY'){
                $('#drpVariety').prop('disabled',false).prop('required',true).val(drpVariety).trigger("change");
            }else{
                $('#drpVariety').prop('disabled',true).prop('required',false).val('').trigger("change");
            }
            $('#drpPackingType').prop('disabled',false).prop('required',true).val(drpPackingType).trigger("change");
        }else if(group == 'Mcs'){
            $('#txtMcs').prop('readonly',false).prop('required',true).val(txtMcs);
            $('#drpColours,#drpGrade,#drpSizes,#drpSlabWeight').prop('disabled',true).prop('required',false);
            //$('#drpSizes').prop('disabled',false).prop('required',true).val(drpSizes).trigger("change");
            $('#drpPckgStyle').prop('disabled',false).prop('required',true).val(drpPckgStyle).trigger("change");
            $('#txtSpacers,#txtLooseBags,#txtCounts').prop('readonly',true).prop('required',false);
            $('#txtDimention').prop('readonly',false).prop('required',false).val(txtDimention);
            if(drpPackingType != 'DUMMY'){
                $('#drpVariety').prop('disabled',false).prop('required',true).val(drpVariety).trigger("change");
            }else{
                $('#drpVariety').prop('disabled',true).prop('required',false).val('').trigger("change");
            }
            $('#drpPackingType').prop('disabled',false).prop('required',true).val(drpPackingType).trigger("change");
            /* if(drpPoNumber){
                $('#drpGrade').prop('disabled',false).prop('required',true).val(drpGrade).trigger("change");
            } */
        }else if(group == 'Spacers'){
            $('#txtSpacers').prop('readonly',false).prop('required',true).val(txtSpacers);
            $('#drpSizes,#drpColours,#drpGrade,#drpPckgStyle,#drpSlabWeight,#drpVariety').prop('disabled',true).prop('required',false);
            $('#txtMcs,#txtLooseBags,#txtCounts,#txtDimention').prop('readonly',true).prop('required',false);
            $('#drpPackingType').prop('disabled',false).prop('required',true).val(drpPackingType).trigger("change");
        }
      
    });
    $(document).on("change", "#drpPackingType", function (e) {
        var val = $(this).val();
        var drpGroup = $('#drpGroup').val();
        $('#drpVariety').prop('disabled',true).prop('required',false).val('').trigger("change");
        if (val !== 'DUMMY' && $.inArray(drpGroup, ['Mcs', 'Pouches']) !== -1){
            $('#drpVariety').prop('disabled',false).prop('required',true).trigger("change");
        }
    })
    // completed


    $("#txtProductionPlant").on("change", function () {

        $("#txtLooseBags").val("0");
        fnGetList("");
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
           
            txtTransactionDate: {
                required: true,
                noSpace: true
            },
            
            drpGroup: {
                required: true
            },
           
            drpStore: {
                required: true
            },
            drpProductionPlant: {
                required: true
            },
            drpPackingStyle: {
                required: true
            },
            drpPackingType: {
                required: true
            },
            /* drpGrade: {
                required: true
            }, */
            drpPckgStyle: {
                required: true
            },
            drpColdStores: {
                required: true
            },
            /* txtMcs: {
                required: true,
                noSpace: true,
                notBothZero: true
            },
            txtLooseBags: {
                required: true,
                noSpace: true,
                notBothZero: true
            } */
        },
        errorPlacement: function () {
            return false; // suppresses error message text
        },
        submitHandler: function () {
            if($("#drpGroup").val() == ''){
                alert('Please select a group first.');
                return;
            }
            // console.log($("#hdnBarCodeID").val());
            var IsStoreProductionLinked = <?php echo $IsStoreProductionLinked ?>;
            var ProductionId = <?php echo (isset($_GET['ProductionDataID']) && !empty($_GET['ProductionDataID']) ? 0 : 1) ?>;

            if (typeof $("#hdnBarCodeID").val() === "undefined") {
                var hdnBarCodeID = "";
            } else if ($("#hdnBarCodeID").val() === null) {
                var hdnBarCodeID = "";
            } else {
                var hdnBarCodeID = $.trim($("#hdnBarCodeID").val());
            }

           

            var pckgStyle;
            if ($("#drpPckgStyle").length > 0) {
                pckgStyle = $.trim($("#drpPckgStyle option:selected").data("packingstyleid"));
            } else {
                pckgStyle = $.trim($("#txtPackingStyle").first().attr("data-packingstyleid"));
            }
            

            var drpPoNo;
            if ($("#drpPoNumber").length > 0) {
                drpPoNo = $.trim($("#drpPoNumber option:selected").val());
            } else {
                drpPoNo = $.trim($("#txtPoNumber").first().attr("data-poid"));
            }
            var IsDeductGlaze = "<?php echo $IsDeductGlaze ?>";
            var prodID = $("#hdnProductionDataID").val();
            var frmData = {
                methodType: "create",
                stockID: $.trim($("#hdnStockID").val()),
                barCodeID: hdnBarCodeID,
                txtProductionPlant: $.trim($("#txtProductionPlant").val()),
                txtTransactionDate: $.trim($("#txtTransactionDate").val()),
                ProductionID: prodID != '' ? $.trim(prodID) : '',  
                drpColdStores: $.trim($("#drpColdStores").val()),
                txtMcs: $.trim($("#txtMcs").val()),
                txtLooseBags: $.trim($("#txtLooseBags").val()),
                drpGroup: $.trim($("#drpGroup").val()),
                poNo: drpPoNo,
                pckgStyle: pckgStyle,
                grade: $.trim($("#drpGrade").val()),
               /*  IsDeductGlaze: IsDeductGlaze,
                IsStoreProductionLinked: IsStoreProductionLinked, */

               // drpSizes: $.trim($("#drpSizes").val()),
                drpColours: $.trim($("#drpColours").val()),
                txtSpacers: $.trim($("#txtSpacers").val()),
                drpBrand: $.trim($("#drpBrand").val()),
                txtBillNo: $.trim($("#txtBillNo").val()),
                txtCounts : $.trim($("#txtCounts").val()),
                PostedUserId : "<?php echo !empty($_SESSION['UserID'])?$_SESSION['UserID']:'';?>",

                drpPackingType: $.trim($("#drpPackingType").val()),
                drpSlabWeight : $.trim($("#drpSlabWeight").val()),
                drpVariety : $.trim($("#drpVariety").val()),
                txtDimention : $.trim($("#txtDimention").val()),

            }

            $.ajax({
                url: 'Ajax/AjaxPackingStoreInward.php',
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


                           // $("#txtProductionDate").removeAttr("readonly");
                        }

                        $("#hdnStockID").val("");
                        $("#submit").text("Save");
                       
                        alert('Data Saved Successfully.');
                        //if (confirm('Data Saved Successfully, Are you want to Place Palette ?')) {
                            //if(drpPoNo){
                                $("#hdnBarCodeID").val(data.BarCodeID);
                                $('.group').val(data.Group);
                                var bidId = data.BarCodeID;

                                if (bidId == null) {
                                    $("#aNewPlacePalette").prop("disabled", true);
                                } else {
                                    $("#aNewPlacePalette").prop("disabled", false);
                                }

                                $("#hdnPopupStockId").val(frmStockData.StockID);
                                $("#aNewPlacePalette").trigger("click");
                            //}                           
                        //}

                    }



                    return false;
                }
            });
        }
    });

    $(document).on("click", ".btnEdit", function () {

       // $("#txtProductionDate").attr("readonly", "readonly");

        var sId = $(this).attr("data-stockid");
        var tdMcs = $(this).closest("tr").find(".tdMcs").text();
        var tdLooseBags = $(this).closest("tr").find(".tdLooseBags").text();
        $("#hdnStockID, #hdnPopupStockId").val(sId);
        var tdBrand = $(this).closest("tr").find(".tdBrand").data('bid');
        $('#drpBrand').val(tdBrand).trigger("change");
        var group = $(this).closest("tr").find(".tdGroup").text();
        //$("#drpGroup").val(group);        
        var tdSizes = $(this).closest("tr").find(".tdSizes").text();
        var tdCount = $(this).closest("tr").find(".tdCount").text();
        var tdPackingStyleID = $(this).closest("tr").find(".tdPackingStyleID").data('packing_style_id');
        var tdGrade = $(this).closest("tr").find(".tdGrade").text();
        var tdSlabWeight = $(this).closest("tr").find(".tdSlabWeight").data('slab_weight_id');
        var tdLooseBags = $(this).closest("tr").find(".tdLooseBags").text();
        var tdDimention = $(this).closest("tr").find(".tdDimention").text();
        //var txtCounts = $("#txtCounts").val();
        if(group == 'Stickers'){            
            $("#txtCounts").val(tdCount);
            $('#drpPckgStyle').val(tdPackingStyleID).trigger("change");
            $("#drpGroup").val(group).trigger("change");
            $('#drpGrade').val(tdGrade).trigger("change");
        }else if(group == 'Count Slips'){
            $('#drpGrade').val(tdGrade).trigger("change");
            $("#txtCounts").val(tdCount);
            $("#drpGroup").val(group).trigger("change");
        }else if(group == 'Tape Rolls'){
            var tdColours = $(this).closest("tr").find(".tdColours").text();
            $('#drpColours').val(tdColours).trigger("change");
            $("#txtCounts").val(tdCount);
            //$('#drpGrade').val(tdGrade).trigger("change");
            $('#drpPckgStyle').val(tdPackingStyleID).trigger("change");
            $("#drpGroup").val(group).trigger("change");
        }else if(group == 'Pouches'){
            $('#txtLooseBags').val(tdLooseBags)
            //$('#drpSizes').val(tdSizes).trigger("change");
            $('#drpSlabWeight').val(tdSlabWeight).trigger("change");
            $("#drpGroup").val(group).trigger("change");
            $('#txtDimention').val(tdDimention)
            if(tdPackingStyleID != 'DUMMY'){
                $("#drpVariety").val(tdVariety).trigger("change");
            }
        }else if(group == 'Mcs'){
            $('#txtMcs').val(tdMcs)
            //$('#drpSizes').val(tdSizes).trigger("change");
            $('#drpPckgStyle').val(tdPackingStyleID).trigger("change");
            $("#drpGroup").val(group).trigger("change");
             $('#txtDimention').val(tdDimention)
            if(tdPackingStyleID != 'DUMMY'){
                $("#drpVariety").val(tdVariety).trigger("change");
            }
        }else if(group == 'Spacers'){
            var tdSpacers = $(this).closest("tr").find(".tdSpacers").text();
            $('#txtSpacers').val(tdSpacers)
            $("#drpGroup").val(group).trigger("change");
        }
        
        fnGetList("");
        $("#submit").text("Update");
        $("html, body").animate({
            scrollTop: 0
        }, "slow");
    });


    $(document).on("click", ".btnDelete", function () {
        var stockID = $(this).attr("data-stockid");
        var barCodeID = $("#hdnBarCodeID").val();
        var prodID = $("#hdnProductionDataID").val();
        var pid = $("#drpBatchCodes").val();
        if (confirm("Are you sure you want to delete this item ?") == true) {

            $.ajax({
                url: 'Ajax/AjaxPackingStoreInward.php',
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

                        $("#txtMcs, #txtLooseBags,#txtSpacers,#txtDimention").val("");
                        $("#drpColdStores").prop("disabled", true);
                        $("#drpGrade#drpPckgStyle,#drpSizes,#drpColours").val("").trigger("change");


                        // $("#drpGroup").val("").trigger("change");
                        
                        $("#hdnStockID").val("");
                        $("#submit").text("Save");
                        $("#hdnBarCodeID").val(data?.BarCodeID ? data.BarCodeID : "");
                        // window.location.reload(true);
                        var bidId = data.BarCodeID;

                        if (bidId == null) {
                            $("#aNewPlacePalette").prop("disabled", true);
                        } else {
                            $("#aNewPlacePalette").prop("disabled", false);
                        }
                        window.location.href = "/v3/ColdStore/packingstore-inward?";


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
        $("#txtRemarks, #txtMcs, #txtLooseBags,#txtSpacers,#txtDimention").val("");
        $("#drpGrade#drpPckgStyle,#drpSizes,#drpColours,#drpSlabWeight").val("").trigger("change");
        // $("#drpColdStores").val("").trigger("change").prop("disabled", false);

       // $("#txtProductionDate").removeAttr("readonly");
        $("#submit").text("Save");
    });
</script>

<script>
    $('.drpSelectModal').select2({
        dropdownParent: $('#modalNewPalette')
    });

    $(document).on("click", "#aNewPalette", function () {

        $("#txtMcs, #txtLooseBags,#Spacers,#txtDimention").val("");
         $("#drpGrade,#drpSizes,#drpColours,#drpSlabWeight,#drpPckgStyle").val("").trigger("change");
        $("#divDataList").html("");
        $("#aNewPlacePalette").prop("disabled", true);
    });
    $(document).on("click", "#aViewStore", function () {

        var Url = 'packing-store-view-store-layout?BarCodeId=' + $("#hdnBarCodeID").val(); //uma
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
        var PackagingStoreIdsArr = <?php echo json_encode(!is_array($PackagingStoreIds) ? explode(',',$PackagingStoreIds) :[]);?>;
        var listBlock = "<option value=''>Select</option>";
        $.each(filteredArray, function (index, val) {
            if(PackagingStoreIdsArr.length !=0){
                if ($.inArray(val.BlockID, PackagingStoreIdsArr) !== -1) {
                    listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
                }
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
                url: 'Ajax/AjaxPackingStoreInward.php',
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
                url: 'Ajax/AjaxPackingStoreInward.php',
                type: 'POST',
                data: {
                    methodType: "placePalette",
                    // barCodeID: $.trim($("#hdnBarCodeID").val()),
                    stockID: $.trim($("#hdnPopupStockId").val()),
                    rackID: $.trim($("#drpRackName").val()),
                    PostedUserId : "<?php echo !empty($_SESSION['UserID'])?$_SESSION['UserID']:'';?>"
                },
                success: function (data) {
                    $('#modalNewPalette').modal('hide');

                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);
                        window.location.href = 'packingstore-inward';
                        //window.location.href = 'packingstore-inward?ProductionDataID=' + $("#hdnProductionDataID").val();
                    }

                    return false;
                }
            });
        }
    });
    function getBrandListByPoId(Poid){
        var list = "<option value=''>Select</option>";
        if(Poid){
            $.ajax({
                url: 'Ajax/AjaxPackingStoreInward.php',
                type: 'POST',
                data: {
                    methodType: "getBrandListByPoId",
                    Poid: $.trim(Poid),
                },
                success: function (data) {
                    var selected = '';
                    if(data.Brands.length == 1){
                        selected = "selected='selected'";
                    }
                    $.each(data.Brands, function (index, val) {                        
                        list += '<option value="' + val.BrandID + '" '+selected+'>' + val.BrandName + '</option>';
                    });
                    $("#drpBrand").html(list);
                    return false;
                }
            });
        }else{
            var Brands = <?php echo json_encode($Brand);?>;
            $.each(Brands, function (index, val) {
                 list += '<option value="' + val.BrandID + '">' + val.BrandName + '</option>';
            });
            $("#drpBrand").html(list);
        }
    }

</script>

</body>

</html>