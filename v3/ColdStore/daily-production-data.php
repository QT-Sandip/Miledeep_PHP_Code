<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_COLDSTORE_DROPDOWNS;
$excludeList = array("ColdStores", "Blocks", "Floors", "StoreRacks", "Stock", "ProductGroup", "Exporters", "Consignee", "Processor", "OptType", "OutType", "Variety", "VarietyList", "StockAvailableVariety", "StockInVarietyList", "PoNumber", "GeneralCategories", "Status", "TenantType", "Brand", "BrandType", "Species", "Tranction", "TypeProduct", "Type", "ProductForm", "Preparation", "GlazeType", "ProductionPlant", "PoNumberNew", "TransactionTypesNew", "InwardGroups");
$bodyContent = array("exclude_list" => $excludeList);
$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resCsTransactionTypes = $resDropdownsAPI->Data->CsTransactionTypes;
$resProductGroup = $resDropdownsAPI->Data->ProductGroup;
$resStockBelongsTo = $resDropdownsAPI->Data->StockBelongsTo;
$resColdStores = $resDropdownsAPI->Data->ColdStores;
$resPlant = $resDropdownsAPI->Data->Plant;
$resShifts = $resDropdownsAPI->Data->Shifts;
$resPoNumber = $resDropdownsAPI->Data->ShipoutPoNumber;//$resDropdownsAPI->Data->PoNumber;
$resVariety = $resDropdownsAPI->Data->Variety;
$resPackingType = $resDropdownsAPI->Data->PackingType;
$resPackingStyle = $resDropdownsAPI->Data->PackingStyle;
$resGrade = $resDropdownsAPI->Data->Grade;
$resFreezingType = $resDropdownsAPI->Data->FreezingType;
$resSoaking = $resDropdownsAPI->Data->Soaking;
$resProductCategory = $resDropdownsAPI->Data->ProductCategory;
$resAbstatus = $resDropdownsAPI->Data->Abstatus;

$resBlocks = $resDropdownsAPI->Data->Blocks;
$resFloors = $resDropdownsAPI->Data->Floors;
$resProductionLines = $resDropdownsAPI->Data->ProductionLines;

$StockOutID = isset($_GET["StockOutID"]) && !empty($_GET["StockOutID"]) ? $_GET["StockOutID"] : "";
$ProductionPlant = $_SESSION['UnitID'];
$PackingStyleData = "";

$ClientConfigData = $_SESSION['ClientConfigData'];
$ClientID = $_SESSION['ClientID'];
// $IsDeductGlaze = $ClientConfigData->IsDeductGlaze;  //sriram

$IsDeductGlaze = $ClientConfigData['IsDeductGlaze'];  //sriram
$IsDeductGlazeFW = $ClientConfigData['IsDeductGlazeFW'];  //sriram
$IsDeductFWFCGlaze = $ClientConfigData['IsDeductFWFCGlaze'];  //sriram
$ProductBatchCode = $ClientConfigData['ProductBatchCode'];
$ArrivalDisplayName = $ClientID != 'JLSFV3' ? 'Batch Code' : 'Arrival Code';


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

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <!-- <h4 class="card-title">Bootstrap Validation - Normal</h4> -->
                                <form id="frmInward">
                                    <div class="row div_inward">


                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtProductionDate" class="form-label">Production Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtProductionDate"
                                                name="txtProductionDate" value="<?php echo date("Y-m-d") ?>"
                                                max="<?php echo date("Y-m-d") ?>" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpProductionPlant" class="form-label">Production Plant<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpProductionPlant"
                                                id="drpProductionPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPlant as $item) { ?>
                                                    <option value="<?php echo $item->ProcessingStationID; ?>">
                                                        <?php echo $item->StationName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpGroup form-control" class="form-label">Group<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpGroup" id="drpGroup">
                                                <option value=""> Select</option>
                                                <?php
                                                foreach ($resCsTransactionTypes as $item) {

                                                    if (!empty($StockOutID)) {
                                                        if ($item->TransType == "2") {
                                                            echo "<option value='" . $item->TransCategory . "' data-transtypeid=" . $item->TransTypeID . ">" . $item->TransCategory . "</option>";
                                                        }
                                                    } else {
                                                        if ($item->TransType == "1") {
                                                            echo "<option value='" . $item->TransCategory . "' data-transtypeid=" . $item->TransTypeID . ">" . $item->TransCategory . "</option>";
                                                        }
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpStockBelongs" class="form-label">Stock Belongs to <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpStockBelongs"
                                                id="drpStockBelongs">
                                                <option value=""> Select</option>
                                                <?php foreach ($resStockBelongsTo as $item) { ?>
                                                    <option value="<?php echo $item->TenantID; ?>"
                                                        data-processorID="<?php echo $item->ProcessorID; ?>">
                                                        <?php echo $item->TenantName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpShift" class="form-label">Shift<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpShift" id="drpShift">
                                                <option value=""> Select</option>
                                                <?php foreach ($resShifts as $item) { ?>
                                                    <option value="<?php echo $item->OptID; ?>">
                                                        <?php echo $item->Name; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtProductionCode" class="form-label">Production Code<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtProductionCode"
                                                name="txtProductionCode" />
                                        </div>


                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBatchCode"
                                                class="form-label"><?php echo $ArrivalDisplayName; ?>
                                                <span style="color:red">*</span></label>
                                            <!-- <input type="text" class="form-control" id="txtBatchCode"
                                                name="txtBatchCode" /> -->

                                            <input type="text" class="form-control" id="txtBatchCode"
                                                name="txtBatchCode" <?= ($ProductBatchCode == 1) ? '' : 'readonly' ?> />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpPoNumber" class="form-label">Po Number</label>
                                            <select class="drpSelect form-select" name="drpPoNumber" id="drpPoNumber">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPoNumber as $item) { ?>
                                                    <option value="<?php echo $item->PoID; ?>"
                                                        data-plantid="<?php echo $item->ProductionPlantID ?>">
                                                        <?php echo $item->PoNo ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtVariety" class="form-label">Variety <span
                                                    style="color:red">*</span></label>
                                            <input type="input" class="form-control" id="txtVariety" name="txtVariety"
                                                placeholder="Click to Select Variety" readonly />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpPackingType" class="form-label">Packing Type <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpPackingType"
                                                id="drpPackingType">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPackingType as $item) { ?>
                                                    <option value="<?php echo $item->Name; ?>">
                                                        <?php echo $item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpPackingStyle" class="form-label">Packing Style <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpPackingStyle"
                                                id="drpPackingStyle">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPackingStyle as $item) { ?>
                                                    <option value="<?php echo $item->PCKID; ?>">
                                                        <?php echo $item->PackingStyle ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <input type="hidden" id="hdnPackingStyle" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpGrade" class="form-label">Grade</label>
                                            <select class="drpSelect form-control" name="drpGrade" id="drpGrade">
                                                <option value=""> Select</option>
                                                <?php foreach ($resGrade as $item) { ?>
                                                    <option value="<?php echo $item->value; ?>">
                                                        <?php echo $item->Options ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpFreezingType" class="form-label">Freezing Type</label>
                                            <select class="drpSelect form-control" name="drpFreezingType"
                                                id="drpFreezingType">
                                                <option value=""> Select</option>
                                                <?php foreach ($resFreezingType as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode; ?>">
                                                        <?php echo $item->ShortCode ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtGlaze" class="form-label">Glaze(%)</label>
                                            <input type="text" class="form-control" id="txtGlaze" name="txtGlaze" />
                                            <input type="hidden" class="form-control" id="hdnGlazeType"
                                                name="hdnGlazeType" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpSoaking" class="form-label">Soaking-Treatment</label>
                                            <select class="drpSelect form-control" name="drpSoaking" id="drpSoaking">
                                                <option value=""> Select</option>
                                                <?php foreach ($resSoaking as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode; ?>">
                                                        <?php echo $item->TreatmentName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpProductCategory" class="form-label">Product Category <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpProductCategory"
                                                id="drpProductCategory">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductCategory as $item) { ?>
                                                    <option value="<?php echo $item->Name; ?>">
                                                        <?php echo $item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpAbStatus" class="form-label">Ab Status <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpAbStatus" id="drpAbStatus">
                                                <!-- <option value=""> Select</option> -->
                                                <?php foreach ($resAbstatus as $item) { ?>
                                                    <option value="<?php echo $item->Name; ?>">
                                                        <?php echo $item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <!-- <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtTubNo" class="form-label">Tub No</label>
                                                <input type="text" class="form-control" id="txtTubNo" name="txtTubNo"  />
                                            </div>
 -->
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpLineNumber" class="form-label">Line Number <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpLineNumber"
                                                id="drpLineNumber">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionLines as $item) { ?>
                                                    <option value="<?php echo $item->Value; ?>">
                                                        <?php echo $item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50"
                                                id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                        </div>


                                        <!-- <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtTotalWeight" class="form-label">Total Weight</label>
                                                <input name="txtTotalWeight" class="form-control" rows="1" cols="50" id="txtTotalWeight" placeholder="Enter Total Weight" readonly="">
                                            </div>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtBalanceWeight" class="form-label">Balance Weight</label>
                                                <input name="txtBalanceWeight" class="form-control" rows="1" cols="50" id="txtBalanceWeight" placeholder="Enter Balance Weight" readonly="">
                                            </div> -->



                                    </div>
                                    <div class="row">
                                        <div>
                                            <hr />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtMcs" class="form-label">Mcs <span
                                                    style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtMcs" name="txtMcs"
                                                min="0" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtLooseBags" class="form-label">Loose Bags <span
                                                    style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtLooseBags"
                                                name="txtLooseBags" />
                                        </div>
                                    </div>




                                    <div class="text-center">
                                        <!-- <input type="hidden" id="hdnStockID" name="hdnStockID" value="" /> -->
                                        <input type="hidden" id="hdnProductionID" name="hdnProductionID"
                                            value="<?php echo (isset($_GET['ProductionDataID']) && !empty("ProductionDataID")) ? $_GET['ProductionDataID'] : "" ?>" />

                                        <a href="daily-production-list" class="btn btn-danger">Close</a>
                                        <button class="btn btn-primary" name="submit" id="submit"
                                            type="submit">Save</button>

                                        <!-- <span style="float: right;">
                                            <a href="javascript:void(0)" id="aNewPalette" class="newItem btn btn-success waves-effect waves-light" >
                                                <i class="fa fa-plus"></i> New Palette
                                            </a>
                                            <button type="button" id="aNewPlacePalette" class="newItem btn btn-success waves-effect waves-light">
                                                <i class="fa fa-plus"></i> Place Palette
                                            </button>
                                            <a href="javascript:void(0)" id="aViewStore" class="newItem btn btn-success waves-effect waves-light">
                                                View Store
                                            </a>
                                        </span> -->

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

    <div class="modal fade" id="modalVarietyList" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Variety List</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!--<form role="form" id="frmTransitType" onsubmit="return validateForm()" method="post">-->
                <div class="modal-body">
                    <div class="row">
                        <div id="modalVariety"></div>
                    </div>
                </div>
                <!-- <div class="modal-footer">                            
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            <button type="button" id="btnPopStockOut" name='btnPopStockOut' class="btn btn-primary">Stock Out</button>
                        </div> -->
            </div>
        </div>
    </div>

    <div class="modal" id="modalNewPalette" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">PO Status Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="frmPopup">
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" id="hdnBlocks"
                                value="<?php echo htmlspecialchars(json_encode($resBlocks), ENT_QUOTES, 'UTF-8'); ?>" />
                            <input type="hidden" id="hdnFloors"
                                value="<?php echo htmlspecialchars(json_encode($resFloors), ENT_QUOTES, 'UTF-8'); ?>" />
                            <div class="col-md-4 mb-4">
                                <label for="drpStoreID" class="form-label">Store ID <span
                                        style="color:red">*</span></label>
                                <select class="form-select drpSelectModal" name="drpStoreID" id="drpStoreID">
                                    <option value="">Select</option>
                                    <?php
                                    foreach ($resColdStores as $coldStore) {
                                        echo '<option value="' . $coldStore->StoreID . '">' . $coldStore->StoreName . '</option>';
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
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="btnPlacePallet" class="btn btn-primary">Save changes</button>
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

    var poptable;

    $(document).on('shown.bs.modal', '#modalVarietyList', function () {
        if ($.fn.dataTable.isDataTable('.popVarietyTable')) {
            poptable.destroy();
        }
        // poptable.columns.adjust().draw();
        poptable = $('.popVarietyTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bInfo: false,
            ordering: false
        });

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


    $("#txtVariety").on("click", function () {
        // if($("#drpPoNumber").val() != "") {
        getVarieties();

    });

    $(document).ready(function () {
        <?php 
        if($_SESSION['ClientID'] == 'DFLV3' || $_SESSION['ClientID'] == 'CAMAROV3'){?>
            if($('#drpShift').val() == ''){
                //$('#drpShift').val('113').trigger('change');
                $("#drpShift option")
                    .filter((i, el) => $(el).text().trim() === "A Shift")
                    .prop("selected", true);

                $("#drpShift").trigger("change");
            }
            if($('#drpProductCategory').val() == ''){
                $('#drpProductCategory').val('G1').trigger('change');
            }
            if($('#drpLineNumber').val() == ''){
                $('#drpLineNumber').val('NA').trigger('change');
            }
       <?php } ?>
        var productionPlant = <?php echo $ProductionPlant ?>;

        if (productionPlant != 0) {
            $("#drpProductionPlant").prop('disabled', true);
            $("#drpProductionPlant").val(productionPlant).trigger('change');
        } else {
            $("#drpProductionPlant").prop('disabled', false);
            $("#drpProductionPlant").val("").trigger('change');
        }

        var plantId = $("#drpProductionPlant").val();

        var $drpPoNumber = $("#drpPoNumber");

        $drpPoNumber.find('option').each(function () {
            var optionPlantId = $(this).data('plantid');

            var plantIdArray = String(optionPlantId).split(',').map(function (id) {
                return id.trim();
            });

            if (productionPlant != 0) {
                if (plantIdArray.includes(plantId) || $(this).val() === "") {
                    $(this).show();
                } else {
                    $(this).remove();
                }
            } else {
                $(this).show();
            }
        });

        if (productionPlant != 0) {

            $("#drpStockBelongs").val($("[data-processorID='" + productionPlant + "']").val()).trigger('change');
            $("#drpStockBelongs").prop("disabled", true);
        } else {
            $("#drpStockBelongs").val("").trigger('change');
            $("#drpStockBelongs").prop("disabled", false);
        }
    });



    $(document).on("click", ".selectVariety", function () {
        var selectedVariety = $(this).val();
        $("#txtVariety").val(selectedVariety);

        var selectedVarietyID = $(this).attr("data-po-grade-id");
        $("#txtVariety").attr("data-po-grade-id", selectedVarietyID);

        var selectedVarietyID = $(this).attr("data-varietyid");
        $("#txtVariety").attr("data-varietyid", selectedVarietyID);

        $('#modalVarietyList').modal('hide');
        getGrades();
    })


    function getVarieties() {
        $.ajax({
            type: 'POST',
            url: 'Ajax/AjaxDailyProductionData.php',
            data: {
                'methodType': 'poNumber',
                'pOID': $("#drpPoNumber").val(),
                'packingType': $("#drpPackingType").val(),
            },
            success: function (data) {
                if (data) {


                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        $('#modalVariety').html("").html(data.HtmlRes);
                        $('#modalVarietyList').modal('show');

                        if (data.ReadonlyDropdowns.PackingStyle == "Enable") {
                            $("#drpPackingStyle").prop("disabled", false);
                        } else {
                            $("#drpPackingStyle").prop("disabled", true);
                        }
                        if (data.ReadonlyDropdowns.DisablePackingType == "Enable") {
                            $("#drpPackingType").prop("disabled", false);
                        } else {
                            $('#drpPackingType').select2('destroy');
                            $("#drpPackingType").val("DUMMY").prop("disabled", true);
                            $('#drpPackingType').select2();
                            // $("#drpPackingType").val("DUMMY").trigger("change").prop("disabled", true);
                        }
                    }



                }
            }
        });
    }

    var prodID = "<?php echo isset($_GET["ProductionDataID"]) && !empty($_GET["ProductionDataID"]) ? $_GET["ProductionDataID"] : "" ?>";

    function fnGetProdData() {
        if (prodID != "") {
            $.ajax({
                url: 'Ajax/AjaxDailyProductionData.php',
                type: 'POST',
                data: {
                    methodType: "getProdData",
                    prodDataId: prodID,
                    barCodeID: "",
                },
                success: function (data) {
                    if (data) {
                        $("#txtProductionDate").val(data.ProductionDate);
                        // $("#txtTransactionDate").val(data.TransactionDate);
                        $("#drpGroup").val(data.Group).trigger("change");
                        $("#drpStockBelongs").val(data.StockHolderID).trigger("change");
                        // $("#drpStore").val(data.StoreID).trigger("change");
                        $("#drpProductionPlant").val(data.PlantID).trigger("change");
                        $("#drpShift").val(data.ShiftID).trigger("change");
                        $("#txtProductionCode").val(data.ProductionCode).attr("readonly", "readonly");
                        $("#txtBatchCode").val(data.BatchCode).attr("readonly", "readonly");

                        $('#drpPackingType').select2('destroy');
                        $("#drpPackingType").val(data.PackingType);
                        $('#drpPackingType').select2();

                        // $("#txtSalt").val(data.);
                        $("#drpProductCategory").val(data.ProductCategory).trigger("change");
                        $("#drpAbStatus").val(data.AbStatus).trigger("change");
                        $("#drpLineNumber").val(data.LineNumber).trigger("change");
                        $("#txtRemarks").val(data.Remarks);
                        $("#txtMcs").val(data.Mcs);
                        $("#txtLooseBags").val(data.LooseBags);

                        $('#drpPoNumber').select2('destroy');
                        $("#drpPoNumber").val(data.PoID);
                        $('#drpPoNumber').select2();

                        $("#txtVariety").attr("data-varietyid", data.VarietyID);
                        $("#txtVariety").attr("data-po-grade-id", data.PoGradeId);
                        $("#txtVariety").val(data.Variety + " - " + data.Grade + " - " + data.ActualPackingStyle + " (" + data.Mcs + " Mcs)");


                        $("#drpGrade").val(data.Grade).trigger("change").prop("disabled", true);
                        $("#drpFreezingType").val(data.FreezingType).trigger("change").prop("disabled", true);
                        $("#txtGlaze").val(data.GlazePerc).attr("readonly", true);
                        $("#drpSoaking").val(data.Soaking).trigger("change").prop("disabled", true);
                        // $("#txtSalt").val(obj.Salt).attr("readonly", "true");

                        $('#drpPackingStyle').select2('destroy');
                        if (data.PackingType == "MASTER") {
                            $("#drpPackingStyle").prop("disabled", true).val(data.PackingStyleID);
                        } else {
                            $("#drpPackingStyle").prop("disabled", false).val(data.PackingStyleID);
                        }
                        $('#drpPackingStyle').select2();

                        $("#hdnPackingStyle").val(data.PackingStyleID);


                    } else {
                        console.log("no data");
                    }

                    return false;
                }
            });


        } else {
            console.log("Else");
        }
    }

    $(document).ready(function () {

        var ProductionDate = $("#txtProductionDate").val();
        var ProductionDataID = "<?php echo isset($_GET["ProductionDataID"]) && !empty($_GET["ProductionDataID"]) ? $_GET["ProductionDataID"] : "" ?>";

        if (ProductionDataID != '') {
            fnGetProdData();
        } else if (ProductionDate != '') {
            getProdDataByProdDate();
            // getVarieties();
        } else {
            // getVarieties();
        }
    });

    $(document).on("change", "#txtProductionDate", function () {
        var ProductionDate = $(this).val();
        if (ProductionDate != '') {
            getProdDataByProdDate(); // uncommented by durgaPrasad based on Maneeja requirement
        }
    })

    $("#drpPoNumber").on("change", function () {

        $("#txtVariety").val("").attr("data-po-grade-id", "").attr("data-varietyid", "");

    });

    $("#drpPackingType").on("change", function () {


        if ($(this).val() == "MASTER") {
            $("#drpPackingStyle").prop("disabled", true).val($("#hdnPackingStyle").val()).trigger("change");
        } else {
            $("#drpPackingStyle").prop("disabled", false);
            // $("#drpPackingStyle").prop("disabled", false).val("").trigger("change");
        }

    });


    // changes for Kgs
    $("#drpPackingStyle").on("change", function () {

        if ($(this).val() != "") {
            $.ajax({
                url: 'Ajax/AjaxDailyProductionData.php',
                type: 'POST',
                data: {
                    methodType: "getPackingStyle",
                    packingStyle: $(this).val(),

                },
                success: function (data) {
                    $PackingStyleData = data;
                    var IsDeductGlaze = <?php echo $IsDeductGlaze ?>;
                    var IsDeductGlazeFW = <?php echo $IsDeductGlazeFW ?>;
                    var IsDeductFWFCGlaze = <?php echo $IsDeductFWFCGlaze?$IsDeductFWFCGlaze:0 ?>;
                    if (($("#txtMcs").val() != "" && $("#txtMcs").val() >= 0) || ($("#txtLooseBags").val() != "" && $("#txtLooseBags").val() >= 0)) {
                        var Kgs = parseFloat("<?php echo isset($_GET["kgs"]) && !empty($_GET["kgs"]) ? $_GET["kgs"] : '' ?>");
                        var Glaze = $("#txtGlaze").val();
                        var mcsKgs = ($("#txtMcs").val() * ($PackingStyleData['TotalSlabs']) * ($PackingStyleData['SlabWeight']) * ($PackingStyleData['MFactor'])) + ($(this).val()) * ($PackingStyleData['SlabWeight']) * ($PackingStyleData['MFactor']);
                        if (IsDeductGlaze == 1 && Glaze > 0) {
                            GlazeWeight = (mcsKgs * Glaze) / 100;
                            mcsKgs = (mcsKgs - GlazeWeight);
                        }
                        var groupType = $("#drpGroup").val();
                        if(groupType != "Repacking" && groupType != "Reglazing"){
                        if (IsDeductFWFCGlaze == 1 ) {
                            var GlazeType = $.trim($("#hdnGlazeType").val());
                            if (GlazeType.startsWith('FW') && Glaze > 0) {
                                GlazeWeight = (mcsKgs * Glaze) / 100;
                                mcsKgs = (mcsKgs - GlazeWeight);
                            }
                        }
                        }
                        if (IsDeductGlazeFW == 1) {
                            var GlazeType = $.trim($("#hdnGlazeType").val());
                              if (GlazeType.startsWith('FW') && Glaze > 0) {
                                 Kgs = Kgs / ((100-Glaze)/100);
                                // GlazeWeight = (Kgs * Glaze) / 100;
                                // Kgs = (Kgs + GlazeWeight);
                            //    GlazeWeight = (mcsKgs * Glaze) / 100;
                            //      mcsKgs = (mcsKgs - GlazeWeight);
                            }
                         }
                        if (Kgs != '' && Kgs < (Math.floor((mcsKgs) * 100) / 100)) {
                            alert("Your MCS exceeds the KGS , Please change packing Style");
                            $("#txtMcs").val("");
                            $("#txtLooseBags").val("");
                        }
                    }
                    return false;
                }
            });
        }

    });
    $("#txtMcs").on("keyup", function () {
        var IsDeductGlaze = <?php echo $IsDeductGlaze ?>;
        var IsDeductGlazeFW = <?php echo $IsDeductGlazeFW ?>;
        var IsDeductFWFCGlaze = <?php echo $IsDeductFWFCGlaze?$IsDeductFWFCGlaze:0 ?>;
        var Kgs = parseFloat("<?php echo isset($_GET["kgs"]) && !empty($_GET["kgs"]) ? $_GET["kgs"] : '' ?>");
        var Glaze = $("#txtGlaze").val();
        var mcsKgs = $(this).val() * ($PackingStyleData['TotalSlabs']) * ($PackingStyleData['SlabWeight']) * ($PackingStyleData['MFactor']);
        if (IsDeductGlaze == 1 && Glaze > 0) {
            GlazeWeight = (mcsKgs * Glaze) / 100;
            mcsKgs = (mcsKgs - GlazeWeight);
        }
         var groupType = $("#drpGroup").val();
                        if(groupType != "Repacking" && groupType != "Reglazing"){
        if (IsDeductFWFCGlaze == 1 ) {
            var GlazeType = $.trim($("#hdnGlazeType").val());
            if (GlazeType.startsWith('FW') && Glaze > 0) {
                GlazeWeight = (mcsKgs * Glaze) / 100;
                mcsKgs = (mcsKgs - GlazeWeight);
            }
        }
}
        if (IsDeductGlazeFW == 1) {
            var GlazeType = $.trim($("#hdnGlazeType").val());
              if (GlazeType.startsWith('FW') && Glaze > 0) {
                 Kgs = Kgs / ((100-Glaze)/100);
                // GlazeWeight = (Kgs * Glaze) / 100;
                // Kgs = (Kgs + GlazeWeight);
                // GlazeWeight = (mcsKgs * Glaze) / 100;
                // mcsKgs = (mcsKgs - GlazeWeight);
            }
        }
        if ($(this).val() != "" && $PackingStyleData != null && $(this).val() >= 0) {
            if (Kgs != '' && Kgs < (Math.floor((mcsKgs) * 100) / 100)) {
                alert("Your MCS exceeds the KGS , Please change packing Style");
                $("#txtMcs").val("");
                $("#txtLooseBags").val("");
            }
        } else {
            alert("Please enter valid Mcs");
        }

    });
    $("#txtLooseBags").on("keyup", function () {
        var IsDeductGlaze = <?php echo $IsDeductGlaze ?>;
        var IsDeductGlazeFW = <?php echo $IsDeductGlazeFW ?>;
        var IsDeductFWFCGlaze = <?php echo $IsDeductFWFCGlaze?$IsDeductFWFCGlaze:0 ?>;
        var Kgs = parseFloat("<?php echo isset($_GET["kgs"]) && !empty($_GET["kgs"]) ? $_GET["kgs"] : '' ?>");
        var Glaze = $("#txtGlaze").val();
        var mcsKgs = ($("#txtMcs").val() * ($PackingStyleData['TotalSlabs']) * ($PackingStyleData['SlabWeight']) * ($PackingStyleData['MFactor'])) + ($(this).val()) * ($PackingStyleData['SlabWeight']) * ($PackingStyleData['MFactor']);
        if (IsDeductGlaze == 1 && Glaze > 0) {
            GlazeWeight = (mcsKgs * Glaze) / 100;
            mcsKgs = (mcsKgs - GlazeWeight);
        }
         var groupType = $("#drpGroup").val();
                        if(groupType != "Repacking" && groupType != "Reglazing"){
        if (IsDeductFWFCGlaze == 1 ) {
            var GlazeType = $.trim($("#hdnGlazeType").val());
            if (GlazeType.startsWith('FW') && Glaze > 0) {
                GlazeWeight = (mcsKgs * Glaze) / 100;
                mcsKgs = (mcsKgs - GlazeWeight);
            }
        }
}
        if (IsDeductGlazeFW == 1) {
            var GlazeType = $.trim($("#hdnGlazeType").val());
            if (GlazeType.startsWith('FW') && Glaze > 0) {
                Kgs = Kgs / ((100-Glaze)/100);
                // Kgs = (Kgs + GlazeWeight);
                // GlazeWeight = (mcsKgs * Glaze) / 100;
                // mcsKgs = (mcsKgs - GlazeWeight);
            }
        }
        if ($(this).val() != "" && $PackingStyleData != null && (($("#txtMcs").val()) >= 0 && $(this).val() >= 0)) {
            if (Kgs != '' && Kgs < (Math.floor((mcsKgs) * 100) / 100)) {
                alert("Your MCS exceeds the KGS , Please change packing Style");
                $("#txtMcs").val("");
                $("#txtLooseBags").val("");
            }
        } else {
            alert("Please enter valid Mcs");
        }

    });


    function getProdDataByProdDate() {
        var ProductBatchCode = <?php echo $ProductBatchCode ?>;

        $.ajax({
            url: 'Ajax/AjaxDailyProductionData.php',
            type: 'POST',
            data: {
                methodType: "getList",
                txtProductionDate: $("#txtProductionDate").val(),
                ColdstoreID: $("#drpProductionPlant").val(),

            },
            success: function (data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                $("#txtMcs, #txtLooseBags").val("");

                $("#txtProductionCode").val($(document).find("#hdnProductionCode").val()).attr("readonly", "readonly");
                if (ProductBatchCode != 1) {
                    $("#txtBatchCode").val($(document).find("#hdnBatchCode").val()).attr("readonly", "readonly");
                }

                var groupType = "<?php echo isset($_GET["type"]) && !empty($_GET["type"]) ? $_GET["type"] : "" ?>";
                $("#drpGroup").val(groupType).trigger("change");

                return false;
            }
        });
    }

    function getGrades() {
        poGraidID = $.trim($("#txtVariety").attr("data-po-grade-id"));
        var PoID = $("#drpPoNumber").val();

        $.ajax({
            type: 'POST',
            url: 'Ajax/AjaxDailyProductionData.php',
            data: {
                'methodType': 'varietyChange',
                'pOID': $("#drpPoNumber").val(),
                'varietyID': $("#txtVariety").attr("data-varietyid"), // $("#txtVariety").val(),
                'packingType': $("#drpPackingType").val(),
                'poGradeID': poGraidID
            },
            success: function (data) {
                //alert("Hi")
                var obj = data.DependentDropdownsData.GradeData;
                $("#drpGrade").val(obj.Grade).trigger("change").prop("disabled", true);
                $("#drpFreezingType").val(obj.FreezingTypeID).trigger("change").prop("disabled", true);
                $("#txtGlaze").val(obj.GlazePerc).attr("readonly", true);
                $("#hdnGlazeType").val(obj.Glaze);
                $("#drpSoaking").val(obj.SoakingType).trigger("change").prop("disabled", true);
                // $("#txtSalt").val(obj.Salt).attr("readonly", "true");

                $("#hdnPackingStyle").val(obj.PackingStyleID);
                if (PoID != '') {
                    $("#drpPackingType").val("MASTER").trigger("change");
                }
                if (obj.PackingStyle == "Enable") {
                    $("#drpPackingStyle").prop("disabled", false).val(obj.PackingStyleID).trigger("change");
                } else {
                    $("#drpPackingStyle").prop("disabled", true).val(obj.PackingStyleID).trigger("change");
                }
                // obj.PackingStyle
            }
        });
    }



    // $.validator.setDefaults({
    //     ignore: ':hidden, [readonly=readonly], :disabled'
    // });

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
            // txtTransactionDate: {
            //     required: true,
            //     noSpace: true
            // },
            drpGroup: {
                required: true
            },
            drpStockBelongs: {
                required: true
            },
            // drpStore: {
            //     required: true
            // },
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
            // drpPoNumber: {
            //     required: true
            // },
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
            drpFreezingType: {
                required: true
            },
            // txtGlaze : {
            //     required: true,
            //     noSpace: true
            // },
            drpSoaking: {
                required: true
            },
            drpProductCategory: {
                required: true
            },
            drpAbStatus: {
                required: true
            },
            drpLineNumber: {
                required: true
            },
            // txtRemarks : {
            //     required: true,
            //     noSpace: true
            // },
            txtMcs: {
                required: true,
                noSpace: true
            },
            txtLooseBags: {
                required: true,
                noSpace: true
            }
        },
        errorPlacement: function () {
            return false; // suppresses error message text
        },
        submitHandler: function () {
            $('#submit').prop('disabled', true);
            // console.log($("#hdnBarCodeID").val());
            // var poGId = $.trim($("#drpVariety").find('option:selected').attr("data-po-grade-id"));
            var poGId = $.trim($("#txtVariety").attr("data-po-grade-id"));
            // if(prodID == "") {
            //     psid = $.trim($("#hdnProductionID").val());
            // }else{
            //     psid = prodID;
            // }

            if ($("#hdnProductionID").val() != "") {
                psid = $.trim($("#hdnProductionID").val());
            }
            // else if(prodID != "") {
            //     psid = prodID;
            // }
            else {
                psid = "";
            }
            var stockOutID = "<?php echo isset($_GET["StockOutID"]) && !empty($_GET["StockOutID"]) ? $_GET["StockOutID"] : "" ?>";
            var IsDeductGlaze = <?php echo $IsDeductGlaze ?>;
            var IsDeductGlazeFW = <?php echo $IsDeductGlazeFW ?>;
            var IsDeductFWFCGlaze = <?php echo $IsDeductFWFCGlaze?$IsDeductFWFCGlaze:0 ?>;
            var frmData = {
                methodType: "create",
                productionID: psid,
                queryStockId: stockOutID,
                // barCodeID: $.trim($("#hdnBarCodeID").val()),
                txtProductionDate: $.trim($("#txtProductionDate").val()),
                // txtTransactionDate : $.trim($("#txtTransactionDate").val()),
                drpGroup: $.trim($("#drpGroup").val()),
                drpStockBelongs: $.trim($("#drpStockBelongs").val()),
                // drpStore : $.trim($("#drpStore").val()),
                drpProductionPlant: $.trim($("#drpProductionPlant").val()),
                drpShift: $.trim($("#drpShift").val()),
                txtProductionCode: $.trim($("#txtProductionCode").val()),
                txtBatchCode: $.trim($("#txtBatchCode").val()),
                drpPoNumber: $.trim($("#drpPoNumber").val()),
                drpVariety: $.trim($("#txtVariety").attr("data-varietyid")),
                drpPackingType: $.trim($("#drpPackingType").val()),
                drpPackingStyle: $.trim($("#drpPackingStyle").val()),
                drpGrade: $.trim($("#drpGrade").val()),
                drpFreezingType: $.trim($("#drpFreezingType").val()),
                txtGlaze: $.trim($("#txtGlaze").val()),
                drpSoaking: $.trim($("#drpSoaking").val()),
                // txtSalt : $.trim($("#txtSalt").val()),
                drpProductCategory: $.trim($("#drpProductCategory").val()),
                drpAbStatus: $.trim($("#drpAbStatus").val()),
                drpLineNumber: $.trim($("#drpLineNumber").val()),
                txtRemarks: $.trim($("#txtRemarks").val()),
                txtMcs: $.trim($("#txtMcs").val()),
                txtLooseBags: $.trim($("#txtLooseBags").val()),
                poGradeID: poGId,
                IsDeductGlaze: IsDeductGlaze,
                IsDeductGlazeFW: IsDeductGlazeFW,
                IsDeductFWFCGlaze: IsDeductFWFCGlaze
            }



            $.ajax({
                url: 'Ajax/AjaxDailyProductionData.php',
                type: 'POST',
                data: frmData,
                success: function (data) {
                    if(data.Error){
                        alert(data.Error);
                        $('#submit').prop('disabled', false);
                        return;
                    }
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    $('#submit').prop('disabled', false);
                    // if(prodID == "") {
                    $("#txtMcs, #txtLooseBags").val("");
                    // }                    
                    //alert("Data saved Successfully");
                    var newAddedProdId = $(document).find("#hdnNewAddedProdId").val();
                    var existingProdID = $(document).find("#hdnProductionID").val();
                    // console.log("durga " + existingProdID);

                    if (existingProdID != '') {
                        window.location.href = 'inward?ProductionDataID=' + existingProdID;
                    } else {
                        window.location.href = 'inward?ProductionDataID=' + newAddedProdId;
                    }


                    // var bidId = $("#hdnBarCodeID").val();

                    // if(bidId == null) {
                    //     $("#aNewPlacePalette").prop("disabled", true);
                    // } else {
                    //     $("#aNewPlacePalette").prop("disabled", false);        
                    // }

                    return false;
                }
            });
        }
    });

    $(document).on("click", ".btnEdit", function () {
        $('#submit').prop('disabled', false);
        // if(prodID == "") {
        //     pid = $(this).attr("data-prodid");
        // }else{
        //     pid = prodID;
        // }

        if ($(this).attr("data-prodid") != "") {
            pid = $(this).attr("data-prodid");
        } else if (prodID != "") {
            pid = prodID;
        } else {
            pid = "";
        }

        // var sId = $(this).attr("data-stockid");
        var barID = $("#hdnBarCodeID").val();

        // $('#drpPoNumber, #drpVariety').select2('destroy');
        $('#drpPoNumber').select2('destroy');
        $('#submit').prop("disabled", true);

        // console.log(stockId);
        // console.log($("#hdnBarCodeID").val());

        $.ajax({
            url: 'Ajax/AjaxDailyProductionData.php',
            type: 'POST',
            data: {
                methodType: "getSingleStock",
                productionId: pid,
                productionDate: $("#txtProductionDate").val(),
                // barCodeID: barID,  // Not manditaory
            },
            success: function (data) {

                // $("#hdnBarCodeID").val();
                $("#hdnProductionID").val(data.ProductionID);
                $("#txtProductionDate").val(data.ProductionDate);
                // $("#txtTransactionDate").val(data.TransactionDate);
                $("#drpGroup").val(data.Group).trigger("change");
                $("#drpStockBelongs").val(data.StockHolderID).trigger("change");
                // $("#drpStore").val(data.StoreID).trigger("change");
                $("#drpProductionPlant").val(data.PlantID).trigger("change");
                $("#drpShift").val(data.ShiftID).trigger("change");
                $("#txtProductionCode").val(data.ProductionCode);
                $("#txtBatchCode").val(data.BatchCode);

                $("#drpPackingType").val(data.PackingType).trigger("change");

                // $("#txtSalt").val(data.);
                $("#drpProductCategory").val(data.ProductCategory).trigger("change");
                $("#drpAbStatus").val(data.AbStatus).trigger("change");
                $("#drpLineNumber").val(data.LineNumber).trigger("change");
                $("#txtRemarks").val(data.Remarks);
                $("#txtMcs").val(data.Mcs);
                $("#txtLooseBags").val(data.LooseBags);

                $("#drpPoNumber").val(data.PoID);
                $('#drpPoNumber').select2();

                // $("#txtVariety").attr("data-varietyid", data.VarietyID);


                $("#txtVariety").attr("data-varietyid", data.VarietyID);
                $("#txtVariety").attr("data-po-grade-id", data.PoGradeId);
                $("#txtVariety").val(data.Variety + " - " + data.Grade + " - " + data.ActualPackingStyle + " (" + data.Mcs + " Mcs)");

                $("#drpGrade").val(data.Grade).trigger("change").prop("disabled", true);
                $("#drpFreezingType").val(data.FreezingType).trigger("change").prop("disabled", true);
                $("#txtGlaze").val(data.GlazePerc).attr("readonly", true);
                $("#drpSoaking").val(data.Soaking).trigger("change").prop("disabled", true);
                // $("#txtSalt").val(obj.Salt).attr("readonly", "true");

                $('#drpPackingStyle').select2('destroy');
                if (data.PackingType == "MASTER") {
                    $("#drpPackingStyle").prop("disabled", true).val(data.PackingStyleID);
                } else {
                    $("#drpPackingStyle").prop("disabled", false).val(data.PackingStyleID);
                }
                $('#drpPackingStyle').select2();

                $("#hdnPackingStyle").val(data.PackingStyleID);


                $('#submit').prop("disabled", false);


                return false;
            }
        });

        $("html, body").animate({
            scrollTop: 0
        }, "slow");

    });
</script>

<script>
    $('.drpSelectModal').select2({
        dropdownParent: $('#modalNewPalette')
    });


    $(document).on("change", "#drpStoreID", function () {
        var storeid = $(this).val();
        var blocksList = JSON.parse($("#hdnBlocks").val());

        var listBlock = "<option value=''>Select</option>";
        $.each(blocksList, function (index, val) {
            if (val.StoreID == storeid) {
                listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
            }
        });

        $("#drpBlockID").html(listBlock);
    })




    $("#frmPopup").validate({
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
        ignore: "",
        // unhighlight: function (element, errorClass, validClass) {
        //     $(element).removeClass(errorClass).addClass('valid');
        //     $(":disabled").removeClass(errorClass).addClass('valid');
        // },
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
                url: 'Ajax/AjaxDailyProductionData.php',
                type: 'POST',
                data: {
                    methodType: "placePalette",
                    barCodeID: $.trim($("#hdnBarCodeID").val()),
                    rackID: $.trim($("#drpRackName").val())
                },
                success: function (data) {
                    $('#modalNewPalette').modal('hide');

                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);
                    }


                    return false;
                }
            });
        }
    });

    $(document).ready(function () {
        $("#drpGroup option").filter(function () {
            return $(this).text() === "Conversion Out";
        })
        .text("Conversion In")
        .val("Conversion In"); 
        <?php 
        if(isset($_GET["type"]) && ($_GET["type"] == "Conversion Out")){?>
            var groupType = "<?php echo isset($_GET["type"]) && ($_GET["type"] == "Conversion Out") ? "Conversion In" : "" ?>";
           $("#drpGroup").trigger("change.select2");
           $("#drpGroup").val(groupType).trigger("change");
        <?php }?>
    })


    var val = parseInt($(this).val());
    var mcs = parseInt($.trim($("#msgBalanceMcs").text()));
    // var stockID = $("#hdnStockID").val();
    var StockOutID = '<?php echo $StockOutID; ?>';
    var editVal = parseInt($("#hdnMcsVal").val());
    // alert(StockOutID);
    if (StockOutID != '') {

        if (val > mcs + editVal) {
            $(this).val("");
            alert("Mcs Should Less than Aval Mcs");
        }
    } else {
        // mcs = mcs;
        if (val > mcs) {
            $(this).val("");
            alert("Mcs Should Less than Aval Mcs");
        }
    }
    // if ()
    // if (val > mcs) {
    //     $(this).val("");
    //     alert("Mcs Should Less than Aval Mcs");
    // }
</script>

</body>

</html>
