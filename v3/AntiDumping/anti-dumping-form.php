<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_COLDSTORE_DROPDOWNS;
$excludeList = array( "Blocks", "Floors", "StoreRacks", "Stock", "ProductGroup", "Exporters", "Consignee", "Processor", "OptType", "OutType", "Variety", "VarietyList", "StockAvailableVariety", "StockInVarietyList", "PoNumber", "GeneralCategories", "Status", "TenantType", "Brand", "BrandType", "Species", "Tranction", "TypeProduct", "Type", "ProductForm", "Preparation", "GlazeType", "ProductionPlant", "PoNumberNew", "TransactionTypesNew", "InwardGroups");
$bodyContent = array("exclude_list" => $excludeList);
$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resCsTransactionTypes = $resDropdownsAPI->Data->CsAllTransactionTypes;
$resProductGroup = $resDropdownsAPI->Data->ProductGroup;
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
                                            <label for="txtProductionDate" class="form-label">Date <span
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
                                            <label for="drpGroupType form-control" class="form-label">Group Type<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpGroupType"
                                                id="drpGroupType">
                                                <option value=""> Select Group</option>
                                                <option value="1"> Inward Group</option>
                                                <option value="2"> Outward Group</option>
                                            </select>
                                        </div>


                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpGroup form-control" class="form-label">Group<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpGroup" id="drpGroup">
                                                <option value=''>Select</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3" style="display: none;">
                                            <label for="drpStockBelongs" class="form-label">Stock Belongs to <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpStockBelongs"
                                                id="drpStockBelongs">
                                                <option value=""> Select</option>
                                                <?php foreach ($resColdStores as $item) { ?>
                                                    <option value="<?php echo $item->StoreID; ?>"
                                                        data-processorID="<?php echo $item->ProductionPlantID; ?>">
                                                        <?php echo $item->StoreName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>


                                        <!-- <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtProductionCode" class="form-label">Production Code<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtProductionCode"
                                                name="txtProductionCode" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBatchCode" class="form-label">Batch Code <span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtBatchCode"
                                                name="txtBatchCode" />
                                        </div> -->

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
                                            <label for="drpFreezingType" class="form-label">Freezing Type</label>
                                            <select class="drpSelect form-control" name="drpFreezingType"
                                                id="drpFreezingType">
                                                <option value=""> Select</option>
                                                <?php foreach ($resFreezingType as $item) { ?>
                                                    <option value="<?php echo $item->FreezingTypeName; ?>">
                                                        <?php echo $item->FreezingTypeName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtGlaze" class="form-label">Glaze(%)</label>
                                            <input type="text" class="form-control" id="txtGlaze" name="txtGlaze" />
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
                                                    <option value="<?php echo $item->Name; ?>" <?php echo ($item->Name === 'G1') ? 'selected' : ''; ?>>
                                                        <?php echo $item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3" style="display: none;">
                                            <label for="drpFromPlant" class="form-label">From Plant <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpFromPlant"
                                                id="drpFromPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPlant as $item) { ?>
                                                    <option value="<?php echo $item->ProcessingStationID; ?>">
                                                        <?php echo $item->StationName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3" style="display: none;">
                                            <label for="drpToPlant" class="form-label">To Plant<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpToPlant"
                                                id="drpToPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPlant as $item) { ?>
                                                    <option value="<?php echo $item->ProcessingStationID; ?>">
                                                        <?php echo $item->StationName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <!-- <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtTubNo" class="form-label">Tub No</label>
                                                <input type="text" class="form-control" id="txtTubNo" name="txtTubNo"  />
                                            </div>
 -->

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
                                            <label for="txtSlabs" class="form-label">Slabs <span
                                                    style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtSlabs" name="txtSlabs"
                                                min="0" />
                                        </div>

                                         <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50"
                                                id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div>
                                            <hr />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtOpeningStock" class="form-label">Opening Stock</label>
                                            <input type="number" class="form-control" id="txtOpeningStock"
                                                name="txtOpeningStock" min="0"/>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtClosingStock" class="form-label">Closing Stock</label>
                                            <input type="number" class="form-control" id="txtClosingStock"
                                                name="txtClosingStock" min="0"/>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTotalInSlabs" class="form-label">Total Inward Slabs </label>
                                            <input type="number" class="form-control" id="txtTotalInSlabs"
                                                name="txtTotalInSlabs" min="0" disabled/>
                                        </div>
                                         <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTotalOutSlabs" class="form-label">Total Outward Slabs </label>
                                            <input type="number" class="form-control" id="txtTotalOutSlabs"
                                                name="txtTotalOutSlabs" min="0" disabled/>
                                        </div>
                                        <input type="hidden" id="hdnSelected" name="hdnSelected" value="" />
                                        <!-- <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtLooseBags" class="form-label">Loose Bags <span
                                                    style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtLooseBags"
                                                name="txtLooseBags" />
                                        </div> -->
                                    </div>




                                    <div class="text-center">
                                        <!-- <input type="hidden" id="hdnStockID" name="hdnStockID" value="" /> -->
                                        <input type="hidden" id="hdnProductionID" name="hdnProductionID"
                                             />

                                        <a href="anti-dumping-form" class="btn btn-danger">Close</a>
                                        <button class="btn btn-primary" name="submit" id="submit"
                                            type="submit">Save</button>
                                        <button class="btn btn-primary" name="btnDeleteSelected" id="btnDeleteSelected" disabled
                                            type="button">Delete Selected</button>

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

                                <div class="row">
                                    <div class="col-12">
                                        <div id="varietyDataBrand"></div>
                                    </div>
                                </div>

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
                title: 'Anti Dumping Inward / Outward List',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
        table.on('draw.dt', function () {
            let noOfKgs = 0;
            let noOfSlabs = 0;
            $('.AvalSlabs').each(function () {
                // Parse the text content as a float, ignoring any commas
                const Slabs = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(Slabs)) {
                  noOfSlabs += Slabs;
                }
            });
            $('.Kgs').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                  noOfKgs += weight;
                }
            });
           
            $('#TotalSlabs').text(noOfSlabs.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#GrandTotalWeight').text(noOfKgs.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });
    }


    $("#txtVariety").on("click", function () {
        // if($("#drpPoNumber").val() != "") {
        getVarieties();

    });

    $(document).ready(function () {
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
        $("#drpPoNumber").prop('disabled', true);
        $("#txtTotalInSlabs").prop('disabled', true);
        $("#txtTotalOutSlabs").prop('disabled', true);
        $("#txtOpeningStock").prop('disabled',true);
        $("#txtClosingStock").prop('disabled',true);

        if (productionPlant != 0) {

            $("#drpStockBelongs").val($("[data-processorID='" + productionPlant + "']").val()).trigger('change');
            $("#drpStockBelongs").prop("disabled", true);
        } else {
            $("#drpStockBelongs").val("").trigger('change');
            $("#drpStockBelongs").prop("disabled", false);
        }
    });


    var remainingVarietyAlert = [];
    $(document).on("click", ".selectVariety", function () {
         remainingVarietyAlert = [];
        var selectedVariety = $(this).val();
        var selectedVarietyID = $(this).data("varietyid");
        $("#txtVariety").val(selectedVariety);

        var selectedVarietyID = $(this).attr("data-po-grade-id");
        $("#txtVariety").attr("data-po-grade-id", selectedVarietyID);

        var selectedVarietyID = $(this).attr("data-varietyid");
        $("#txtVariety").attr("data-varietyid", selectedVarietyID);

         $(".selectVariety").each(function () {

            var varietyId = $(this).data("varietyid");

            if (varietyId != selectedVarietyID) {

                var shortName = $(this).data("shortname");
                var pendingMcs = $(this).data("pending-mcs");

                if (pendingMcs && pendingMcs !== "") {
                    remainingVarietyAlert.push(shortName + " " + pendingMcs);
                }
            }
        });

        $('#modalVarietyList').modal('hide');

        // if($("#drpGroup").val()  === "Shipment Out"){
        //     getAllData(selectedVarietyID);
        // }
        
        getGrades();
        var PoID = $("#drpPoNumber").val();
        if(PoID == ''){
            getAllGrades();
        } else{
            getTotalByPoNumber();
        }
    });

    function getAllData(selectedVarietyID){
        $.ajax({
            type: 'POST',
            url: 'Ajax/ajax-anti-dumping-form.php',
            data: {
                'methodType': 'getDataWithoutBrand',
                'txtProductionDate' : $("#txtProductionDate").val(),
                'varietyID': selectedVarietyID,
            },
            success: function (data) {
                if (data) {
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        $("#varietyDataBrand").html(data);
                    }
                }
            }
        });
    }


    function getVarieties() {
        $.ajax({
            type: 'POST',
            url: 'Ajax/ajax-anti-dumping-form.php',
            data: {
                'methodType': 'poNumber',
                'pOID': $.trim($("#drpPoNumber").val()),
            },
            success: function (data) {
                if (data) {


                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        $('#modalVariety').html("").html(data.HtmlRes);
                        $('#modalVarietyList').modal('show');

                        if (data.ReadonlyDropdowns.PackingStyle == "Enable") {
                            $("#drpPackingStyle").prop("disabled", true);
                        } else {
                            $("#drpPackingStyle").prop("disabled", true);
                        }
                    }
                }
            }
        });
    }

    $(document).ready(function () {
        $("#hdnProductionID").val('');
        getProdDataByProdDate();
    });

    $(document).on("change", "#txtProductionDate", function () {
        var ProductionDate = $(this).val();
        if (ProductionDate != '') {
            getProdDataByProdDate(); // uncommented by durgaPrasad based on Maneeja requirement
        }
        if($("#drpGroup").val() != ''){
            if($("#drpGroup").val()  === "Shipment Out") {
                getAllPOs();
            }
        }
    })

    $("#drpGroup").on("change", function () {
        $("#drpFromPlant").val('');
        $("#drpToPlant").val('');
        var selectedGroup = $(this).val();

        if (selectedGroup === "IBT IN") { 
            // Replace with the actual group value to match
            $("#drpFromPlant").closest("div").show();
            $("#drpToPlant").closest("div").hide();
        } else if(selectedGroup === "IBT Out"){
            $("#drpFromPlant").closest("div").hide();
            $("#drpToPlant").closest("div").show();
        }  else{
             $("#drpFromPlant").closest("div").hide();
            $("#drpToPlant").closest("div").hide();
        }

        if(selectedGroup === "Shipment Out") {
            getAllPOs();
        } else{
             if($("#drpGroupType").val() != '' && $("#drpGroupType").val() == 2){
            var resPoNumber = <?php echo json_encode($resPoNumber) ?>;
            var list = "<option value=''>Select</option>";
                    if (resPoNumber) {
                        $.each(resPoNumber, function (index, value) {
                            list += "<option value=" + value.PoID + " data-plantid='" + value.ProductionPlantID + "'>" + value.PoNo + "</option>";
                        });
                    }

                    // $('#drpPoNumber').select2('destroy');
                    if ($.fn.select2 && $('#drpPoNumber').hasClass("select2-hidden-accessible")) {
                        $('#drpPoNumber').select2('destroy');
                    }
                    $('#drpPoNumber').html(list);
                    $('#drpPoNumber').select2();
                }
        }
    });

    $("#drpGroupType").on("change", function () {
        if ($("#drpGroupType").val() != '1') {
            $("#txtOpeningStock").prop('disabled', true);
            $("#txtClosingStock").prop('disabled', true);
        } else {
            if ($("#drpGrade").val() != '') {
                $("#drpGrade").trigger("change");
            }
        }
        var resCsTransactionTypes = <?php echo json_encode($resCsTransactionTypes); ?>;
        if ($(this).val() == 1) {
             $("#drpPoNumber").val("").trigger("change").prop('disabled', true);
            var list = "<option value=''>Select</option>";
            if (resCsTransactionTypes) {
                $.each(resCsTransactionTypes, function (index, value) {
                    if ((value.TransType == 1 || value.TransType == 3 || value.TransType == 4) && value.TransCategory != 'Opening Stock') {
                        list += "<option value='" + value.TransCategory + "' data-transtypeid='" + value.TransTypeID + "'>" + value.TransCategory + "</option>";
                    }
                });
            }

            $('#drpGroup').select2('destroy');
            $('#drpGroup').html(list);
            $('#drpGroup').select2();
        } else if($(this).val() == 2) {

            $("#drpPoNumber").prop('disabled', false);
            var list = "<option value=''>Select</option>";
            if (resCsTransactionTypes) {
                $.each(resCsTransactionTypes, function (index, value) {
                    if (value.TransType == 2 && value.TransCategory != 'Reglazing Out') {
                        list += "<option value='" + value.TransCategory + "' data-transtypeid='" + value.TransTypeID + "'>" + value.TransCategory + "</option>";
                    }
                });
            }

            $('#drpGroup').select2('destroy');
            $('#drpGroup').html(list);
            $('#drpGroup').select2();
        } else{
            $("#drpPoNumber").prop('disabled', true);
            var list = "<option value=''>Select</option>";

            $('#drpGroup').select2('destroy');
            $('#drpGroup').html(list);
            $('#drpGroup').select2();
        }


    });

    $("#drpGrade").on("change", function () {
        getTotalByPoNumber();
    });

    $("#drpPoNumber").on("change", function () {

        $("#txtVariety").val("").attr("data-po-grade-id", "").attr("data-varietyid", "");
        getTotalByPoNumber();

    });

    function getProdDataByProdDate() {

        $.ajax({
            url: 'Ajax/ajax-anti-dumping-form.php',
            type: 'POST',
            data: {
                methodType: "getList",
                txtProductionDate: $("#txtProductionDate").val(),
                ColdstoreID: $("#drpProductionPlant").val(),
            },
            success: function (data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                if($("#hdnProductionID").val() == ''){
                    $("#txtSlabs").val("");

                }
                $("#btnDeleteSelected").prop("disabled");

                getTotalByPoNumber();

                // $("#txtProductionCode").val($(document).find("#hdnProductionCode").val()).attr("readonly", "readonly");
                // $("#txtBatchCode").val($(document).find("#hdnBatchCode").val()).attr("readonly", "readonly");

                // var groupType = "<?php echo isset($_GET["type"]) && !empty($_GET["type"]) ? $_GET["type"] : "" ?>";
                // $("#drpGroup").val(groupType).trigger("change");

                return false;
            }
        });
    }

    function getTotalByPoNumber() {

        var varietyid = '';
        if ($.trim($("#drpPoNumber").val()) === '') {
            varietyid = $("#drpGrade option:selected").data("varietyid") || '';
            // varietyid = $("#txtVariety").attr("data-varietyid") || '';
        } else {
            varietyid = $("#txtVariety").attr("data-varietyid") || '';
        }
        $.ajax({
            url: 'Ajax/ajax-anti-dumping-form.php',
            type: 'POST',
            data: {
                methodType: "getPoTotal",
                txtProductionDate: $("#txtProductionDate").val(),
                ColdstoreID: $("#drpProductionPlant").val(),
                PoID: '',
                varietyID: varietyid,
            },
            success: function (data) {
                $("#txtTotalInSlabs").val(data.TotalSlabs?.TotalInwardSlabs ? data.TotalSlabs.TotalInwardSlabs : 0);
                $("#txtTotalOutSlabs").val(data.TotalSlabs?.TotalOutwardSlabs ? data.TotalSlabs?.TotalOutwardSlabs : 0);

               if (data?.OpeningStock && Object.keys(data.OpeningStock).length > 0) {
                    $("#txtOpeningStock")
                        .val(data.OpeningStock?.OpeningStock || '')
                        .prop('disabled', true);

                    $("#txtClosingStock")
                        .val(data.OpeningStock?.ClosingStock || '')
                        .prop('disabled', true);
                } else {
                    if ($("#drpGroupType").val() == '1') {
                        $("#txtOpeningStock").val('').prop('disabled', true); // enable if entry required
                        $("#txtClosingStock").val('').prop('disabled', true);
                    } else {
                        $("#txtOpeningStock").val('').prop('disabled', true);
                        $("#txtClosingStock").val('').prop('disabled', true);
                    }
                }

                return false;
            }
        });
    }

    function getGrades() {
        poGraidID = $.trim($("#txtVariety").attr("data-po-grade-id"));
        var PoID = $("#drpPoNumber").val();

        $.ajax({
            type: 'POST',
            url: 'Ajax/ajax-anti-dumping-form.php',
            data: {
                'methodType': 'varietyChange',
                'pOID': $("#drpPoNumber").val(),
                'varietyID': $("#txtVariety").attr("data-varietyid"),
                'poGradeID': ''
            },
            success: function (data) {
                //alert("Hi")
                // var obj = data.DependentDropdownsData.GradeData;
                // $("#drpGrade").val(obj.Grade).trigger("change").prop("disabled", false);
                var obj = data.DependentDropdownsData.GradeData;
                var grade = obj.Grade;
                $("#drpGrade").prop("disabled", false);
                // Check if option exists
                if ($("#drpGrade option[value='" + grade + "']").length === 0) {
                    // Add the missing option
                    $("#drpGrade").append(
                        $("<option>", {
                            value: grade,
                            text: grade,
                            "data-varietyid": obj.VarietyID || ""
                        })
                    );
                }

                // Select the grade
                $("#drpGrade").val(grade).trigger("change");
                $("#drpFreezingType").val(obj.FreezingTypeID).trigger("change").prop("disabled", true);
                $("#txtGlaze").val(obj.GlazePerc).attr("readonly", true);
                $("#drpSoaking").val(obj.SoakingType).trigger("change").prop("disabled", true);
                // $("#txtSalt").val(obj.Salt).attr("readonly", "true");

                $("#hdnPackingStyle").val(obj.PackingStyleID);
                if (obj.PackingStyle == "Enable") {
                    $("#drpPackingStyle").prop("disabled", true).val(obj.PackingStyleID).trigger("change");
                } else {
                    $("#drpPackingStyle").prop("disabled", true).val(obj.PackingStyleID).trigger("change");
                }
                // obj.PackingStyle
            }
        });
    }

    function getAllGrades() {
        var PoID = $("#drpPoNumber").val();

        $.ajax({
            type: 'POST',
            url: 'Ajax/ajax-anti-dumping-form.php',
            data: {
                'methodType': 'getVarietyAllGrades',
                'pOID': $("#drpPoNumber").val(),
                'varietyID': $("#txtVariety").attr("data-varietyid"), // $("#txtVariety").val(),
            },
            success: function (data) {
                var grade = $("#drpGrade").val();
                //alert("Hi")
                $("#drpGrade").prop("disabled", false);
                var list = "<option value=''>Select</option>";
                if (data) {
                    $.each(data, function (index, value) {
                        list += "<option value='" + value.Grade + "' data-varietyid='" + value.VarietyID + "'>" + value.Grade + "</option>";
                    });
                }

                $('#drpGrade').select2('destroy');
                $('#drpGrade').html(list);
                $('#drpGrade').select2();

                if (grade) {
                    $('#drpGrade').val(grade).trigger('change'); 
                }

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
            drpGroup: {
                required: true
            },
            drpStockBelongs: {
                required: true
            },
            drpProductionPlant: {
                required: true
            },
            txtVariety: {
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
            drpSoaking: {
                required: true
            },
            drpProductCategory: {
                required: true
            },
            txtSlabs: {
                required: true,
                noSpace: true
            },
            drpPoNumber: {
                required: function (element) {
                    return $("#drpGroup").val() === "Shipment Out";  
                }
            }
        },
        errorPlacement: function () {
            return false; // suppresses error message text
        },
        submitHandler: function () {

             if (remainingVarietyAlert.length > 0) {
                alert("Remaining Pending Mcs:\n" + remainingVarietyAlert.join(", "));
            }

            var poGId = $.trim($("#txtVariety").attr("data-po-grade-id"));

            if ($("#hdnProductionID").val() != "") {
                psid = $.trim($("#hdnProductionID").val());
            }
            else {
                psid = "";
            }

            var varietyid = '';
            if($.trim($("#drpPoNumber").val()) == ''){
                varietyid = $("#drpGrade option:selected").data("varietyid");
                // varietyid = $.trim($("#txtVariety").attr("data-varietyid"));
            }else{
                varietyid = $.trim($("#txtVariety").attr("data-varietyid"));
            }

            var stockOutID = "<?php echo isset($_GET["StockOutID"]) && !empty($_GET["StockOutID"]) ? $_GET["StockOutID"] : "" ?>";
            var frmData = {
                methodType: "create",
                productionID: psid,
                queryStockId: stockOutID,
                txtProductionDate: $.trim($("#txtProductionDate").val()),
                drpGroupType: $.trim($("#drpGroupType").val()),
                drpGroup: $.trim($("#drpGroup").val()),
                drpStockBelongs: $.trim($("#drpStockBelongs").val()),
                drpProductionPlant: $.trim($("#drpProductionPlant").val()),
                drpPoNumber: $.trim($("#drpPoNumber").val()),
                drpVariety: varietyid,
                drpPackingStyle: $.trim($("#drpPackingStyle").val()),
                drpGrade: $.trim($("#drpGrade").val()),
                drpFreezingType: $.trim($("#drpFreezingType").val()),
                txtGlaze: $.trim($("#txtGlaze").val()),
                drpSoaking: $.trim($("#drpSoaking").val()),
                drpProductCategory: $.trim($("#drpProductCategory").val()),
                txtRemarks: $.trim($("#txtRemarks").val()),
                txtSlabs: $.trim($("#txtSlabs").val()),
                txtOpeningStock: $.trim($("#txtOpeningStock").val()),
                txtClosingStock: $.trim($("#txtClosingStock").val()),
                drpFromPlant: $.trim($("#drpFromPlant").val()),
                drpToPlant: $.trim($("#drpToPlant").val()),
                poGradeID: poGId
            }


            $.ajax({
                url: 'Ajax/ajax-anti-dumping-form.php',
                type: 'POST',
                data: frmData,
                success: function (data) {
                    if(data.Error){
                        alert(data.Error);
                        return;
                    }
                    getProdDataByProdDate();
                    // $("#divDataList").html(data);
                    // fnShowDataTable();

                    // if(prodID == "") {
                    psid = $.trim($("#hdnProductionID").val());
                    $("#txtSlabs").val("");
                    $("#txtRemarks").val("");
                    // }
                    alert("Data Saved Successfully");
                    var newAddedProdId = $(document).find("#hdnNewAddedProdId").val();
                    var existingProdID = $(document).find("#hdnProductionID").val();
                    $("#hdnProductionID").val('');

                   if (psid !== '') {
                        window.location.href = "anti-dumping-form";
                    }

                    return false;
                }
            });
        }
    });

    $(document).on("click", ".btnEdit", function () {

        if ($(this).attr("data-prodid") != "") {
            pid = $(this).attr("data-prodid");
        } else {
            pid = "";
        }
        var barID = $("#hdnBarCodeID").val();

        // $('#drpPoNumber, #drpVariety').select2('destroy');
        $('#drpPoNumber').select2('destroy');
        $('#submit').prop("disabled", true);

        $.ajax({
            url: 'Ajax/ajax-anti-dumping-form.php',
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
                $("#drpProductionPlant").val(data.PlantID).trigger("change");
                // $("#txtTransactionDate").val(data.TransactionDate);
                $("#drpGroupType").val(data.GroupType).trigger("change");
                $("#drpGroup").val(data.Group).trigger("change");
                $("#drpStockBelongs").val(data.StoreID).trigger("change");
                $("#drpProductCategory").val(data.ProductCategory).trigger("change");
                $("#txtRemarks").val(data.Remarks);
                $("#txtSlabs").val(data.AvalSlabs);
                $('#drpPoNumber').select2();

                if (data.PoID && data.PoID != '' && data.PoID != 0) {
                    if ($("#drpPoNumber option[value='" + data.PoID + "']").length === 0) {
                        $("#drpPoNumber").append(
                            $("<option>", {
                                value: data.PoID,
                                text: data.PoNo,
                                "data-plantid": data.PlantID || ""
                            })
                        );
                    }

                    // wait a bit to ensure select2 has initialized
                    setTimeout(function () {
                        $("#drpPoNumber").val(data.PoID).trigger("change");

                        $("#drpPoNumber option").each(function () {
                            if ($(this).val() == data.PoID) {
                                $(this).prop("selected", true);
                            }
                        });
                    }, 200);
                }
                 setTimeout(function () {
                   
                    $("#txtVariety").attr("data-varietyid", data.VarietyID);
                    $("#txtVariety").attr("data-po-grade-id", data.PoGradeID);
                    $("#txtVariety").val(data.Variety + " - " + data.Grade + " - " + data.ActualPackingStyle);

                    $("#drpGrade option").each(function () {
                    if ($(this).val() === data.Grade) {
                        $(this).attr("data-varietyid", data.VarietyID);
                    }
                });

                $("#drpGrade").val(data.Grade).trigger("change");
                }, 400);
                
                $("#drpFreezingType").val(data.FreezingType).trigger("change").prop("disabled", true);
                $("#txtGlaze").val(data.GlazePerc).attr("readonly", true);
                $("#drpSoaking").val(data.Soaking).trigger("change").prop("disabled", true);

                $('#drpPackingStyle').select2('destroy');
                if (data.PackingType == "MASTER") {
                    $("#drpPackingStyle").prop("disabled", true).val(data.PackingStyleID);
                } else {
                    $("#drpPackingStyle").prop("disabled", true).val(data.PackingStyleID);
                }
                $('#drpPackingStyle').select2();

                $("#hdnPackingStyle").val(data.PackingStyleID);
               

                $('#submit').prop("disabled", false);

                getTotalByPoNumber();
                return false;
            }
        });

        $("html, body").animate({
            scrollTop: 0
        }, "slow");

    });

    $(document).on("click", ".btnDelete", function () {
        if (confirm('Are you sure want to delete?')) {

            $.ajax({
                url: 'Ajax/ajax-anti-dumping-form.php',
                type: 'POST',
                data: {
                    methodType: "delete",
                    ProductionID: $(this).attr("data-prodid"),
                    ProductionDate: $("#txtProductionDate").val()
                },
                success: function (data) {
                    getProdDataByProdDate();
                    // $("#divDataList").html(data);
                    // fnShowDataTable();

                    return false;
                }
            });
        }
    });

    $(document).on("click", ".chkSelectRecList", function() {
        fnSelectedIds();
    });

    $(document).on("click", "#chkSelectAll", function() {
        $(".chkSelectRecList").each(function() {
            $(this).prop("checked", $("#chkSelectAll").prop("checked"));
        });
        fnSelectedIds();
    });

    function fnSelectedIds(){
       var selectedBreakUpId = [];

        $(".chkSelectRecList:checked").each(function() {
            selectedBreakUpId.push($(this).val());
        });

        var anyChecked = selectedBreakUpId.length > 1;
        $("#btnDeleteSelected").prop("disabled", !anyChecked);
        $("#hdnSelected").val(selectedBreakUpId.join(","));
    }

    $(document).on("click", "#btnDeleteSelected", function () {
        if (confirm('Are you sure want to delete Selected Records?')) {

            $.ajax({
                url: 'Ajax/ajax-anti-dumping-form.php',
                type: 'POST',
                data: {
                    methodType: "deleteSelected",
                    ProductionID: $("#hdnSelected").val(),
                    ProductionDate: $("#txtProductionDate").val()
                },
                success: function (data) {
                    getProdDataByProdDate();
                    // $("#divDataList").html(data);
                    // fnShowDataTable();

                    return false;
                }
            });
        }
    });

    function getAllPOs() {
        $.ajax({
            type: 'POST',
            url: 'Ajax/ajax-anti-dumping-form.php',
            data: {
                'methodType': 'allPoNumbers',
                'ProductionPlant': $("#drpProductionPlant").val(),
                'ProductionDate': $("#txtProductionDate").val(),
            },
            success: function (data) {
                if (data) {
                    var list = "<option value=''>Select</option>";
                    if (data) {
                        $.each(data, function (index, value) {
                            list += "<option value=" + value.PoID + " data-plantid='" + value.ProductionPlantID + "'>" + value.PoNo + "</option>";
                        });
                    }

                    $('#drpPoNumber').select2('destroy');
                    $('#drpPoNumber').html(list);
                    $('#drpPoNumber').select2();

                }
            }
        });
    }

</script>

</body>

</html>