<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ProductionPlant = $_SESSION['UnitID'];
$api = API_STOCK_OUT_DROPDOWNS;
$bodyContent = array(
    'page' => 'stock-out',
    'UnitId' => $ProductionPlant,
);
$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resPackingType = $resDropdownsAPI->Data->PackingType;
$resCsTransactionTypes = $resDropdownsAPI->Data->CsTransactionTypes;

// echo json_encode($resCsTransactionTypes, JSON_PRETTY_PRINT);
// exit;
$resColdStores = $resDropdownsAPI->Data->ColdStores;
$resPoNumber = $resDropdownsAPI->Data->OutPoNumbers;
$resVariety = $resDropdownsAPI->Data->Variety;
$resGrade = $resDropdownsAPI->Data->Grade;
$resBlocks = $resDropdownsAPI->Data->Blocks;
$resPackingStyle = $resDropdownsAPI->Data->PackingStyle;
$resVarietyList = $resDropdownsAPI->Data->VarietyList;
$resTypeProduct = $resDropdownsAPI->Data->TypeProduct;
$resSpecies = $resDropdownsAPI->Data->Species;
$resFreezingType = $resDropdownsAPI->Data->FreezingType;
$resSoaking = $resDropdownsAPI->Data->Soaking;
$resProductCategory = $resDropdownsAPI->Data->ProductCategory;
$resAbstatus = $resDropdownsAPI->Data->Abstatus;

$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$IsDeductGlaze = $ClientConfigData->IsDeductGlaze;  //sriram
$IsDeductGlazeFW = $ClientConfigData->IsDeductGlazeFW;  //sriram
$IsDeductFWFCGlaze = $ClientConfigData->IsDeductFWFCGlaze;  //sriram
$convertLooseToMcs = $ClientConfigData->convertLooseToMcs;  //sriram

?>

<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>
</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . '/includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . '/includes/page-header.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <form id="frmStockOutSearch" role="form" method="post">
                                    <div class="row">
                                        <input type="hidden" id="hdnBlocks"
                                            value="<?php echo htmlspecialchars(json_encode($resBlocks), ENT_QUOTES, 'UTF-8'); ?>" />

                                        <div class="col-md-3 mb-3">
                                            <label for="drpColdStore">Cold Stores</label>
                                            <select class="drpSelect form-control" id="drpColdStore"
                                                name="drpColdStore">
                                                <option value="">Select</option>
                                                <?php foreach ($resColdStores as $item) { ?>
                                                    <option value="<?php echo $item->StoreID ?>"
                                                        data-plant="<?php echo $item->ProductionPlantID ?>"
                                                        data-blocks="<?php echo $item->StoreBlocks ?>">
                                                        <?php echo $item->StoreName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="drpBlocks" class="form-label">Blocks</label>
                                            <select class="form-control drpSelect" name="drpBlocks" id="drpBlocks">
                                                <option value="">Select</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="drpPoNumber">PO Number</label>
                                            <select class="drpSelect form-control" id="drpPoNumber" name="drpPoNumber">
                                                <option value="">Select</option>
                                                <?php foreach ($resPoNumber as $item) { ?>
                                                    <option value="<?php echo $item->PoID ?>"
                                                        data-plantid="<?php echo $item->ProductionPlantID ?>">
                                                        <?php echo $item->PoNo ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtVariety" class="form-label">Product <span
                                                    style="color:red">*</span></label>
                                            <input type="input" class="form-control" id="txtVariety" name="txtVariety"
                                                placeholder="Click to Select Variety" readonly />
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
                                            <input type="hidden" id="hdnGradeList"
                                                value='<?php echo json_encode($resGrade) ?>' />
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="drpVariety">Variety</label>
                                            <select class="drpSelect form-control" id="drpVariety" name="drpVariety">
                                                <option value="">Select (Stock Available Variety Only)</option>
                                                <?php foreach ($resVarietyList as $item) { ?>
                                                    <option value="<?php echo $item->DisplayName ?>">
                                                        <?php echo $item->DisplayName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="drpPckgStyle">Packing Style</label>
                                            <select class="drpSelect form-control" id="drpPckgStyle"
                                                name="drpPckgStyle">
                                                <option value="">Select</option>
                                                <?php foreach ($resPackingStyle as $item) { ?>
                                                    <option value="<?php echo $item->PCKID ?>" data-totalslabs="<?php echo $item->TotalSlabs ?>">
                                                        <?php echo $item->PackingStyle ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <input type="hidden" id="hdnPackingStyle"
                                                value='<?php echo json_encode($resPackingStyle) ?>' />
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="drpPackingType">Packing Type</label>
                                            <select class="drpSelect form-control" name="drpPackingType"
                                                id="drpPackingType">
                                                <option value=""> Select</option>
                                                <option value="DUMMY"> DUMMY</option>
                                                <option value="MASTER"> MASTER</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="drpSpecies">Species</label>
                                            <select class="drpSelect form-control" id="drpSpecies" name="drpSpecies">
                                                <option value="">Select</option>
                                                <?php foreach ($resSpecies as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode ?>">
                                                        <?php echo $item->SpeciesName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpSoaking" class="form-label">Soaking</label>
                                            <select class="drpSelect form-control" name="drpSoaking" id="drpSoaking">
                                                <option value=""> Select</option>
                                                <?php foreach ($resSoaking as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode; ?>">
                                                        <?php echo $item->ShortCode ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpType" class="form-label">Freezing Type</label>
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

                                        <div class="col col-md-3 mb-2">
                                            <label>Type of Product </label>
                                            <select class="form-control drpSelect" name="drpTypeProduct"
                                                id="drpTypeProduct">
                                                <option value="">All</option>
                                                <?php foreach ($resTypeProduct as $TypeProduct) { ?>
                                                    <option value="<?php echo $TypeProduct ?>"><?php echo $TypeProduct ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="drpAbstatus">Abstatus</label>
                                            <select class="drpSelect form-control" id="drpAbstatus" name="drpAbstatus">
                                                <option value="">Select</option>
                                                <?php foreach ($resAbstatus as $item) { ?>
                                                    <option value="<?php echo $item->Name ?>"><?php echo $item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="drpPDCategory">Product Category</label>
                                            <select class="drpSelect form-control" id="drpPDCategory" name="drpPDCategory">
                                                <option value="">Select</option>
                                                <?php foreach ($resProductCategory as $item) { ?>
                                                    <option value="<?php echo $item->Name ?>" <?php echo ($item->Name == "G1") ? 'selected' : ''; ?>>
                                                        <?php echo $item->Name ?>
                                                    </option>

                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <label for="btnSearch" style="display: block;">&nbsp;</label>
                                            <button id="btnSearch" style="text-align:left" type="submit"
                                                class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                            <button id="btnStockOut" disabled style="text-align:left" type="button"
                                                class="btn btn-success  waves-effect waves-light" data-bs-toggle="modal"
                                                data-bs-target="#ModalStockOut">
                                                Stock Out <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                            <input type="hidden" id="hdnTotalMcs" />
                                        </div>
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

        <div class="modal fade" id="ModalStockOut" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Stock Out Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form role="form" id="frmStockOut" method="post">
                        <div class="modal-body">
                            <div class="row">
                                <table class="popTable table table-hover table-bordered dataTable no-footer">
                                    <thead>
                                        <th>MCS</th>
                                        <th>Loose</th>
                                        <th style="width: 250px">Stock Out Date</th>
                                        <th style="width: 250px">Reason</th>
                                        <th style="width: 250px">Stage</th>
                                        <th style="width: 250px">Remarks</th>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="hdnSelectedRow" name="hdnSelectedRow" />
                            <input type="hidden" id="hSumMcs" name="hSumMcs" value="0" />
                            <input type="hidden" id="hSumLoosBags" name="hSumLoosBags" value="0" />
                            <input type="hidden" id="hdnObjRowsSelected" name="hdnObjRowsSelected" />
                            <input type="hidden" id="hdnSumMCS" name="hdnSumMCS" />
                            <input type="hidden" id="hdnSumLooseBags" name="hdnSumLooseBags" />
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            <button type="button" id="btnPopStockOut" name='btnPopStockOut'
                                class="btn btn-primary">Stock Out</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php include_once ROOT . '/includes/footer.php' ?>

    </div>
    <!-- end main content-->
    <!-- Modal -->

</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . '/includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
    type="text/javascript"></script>
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->



<script>
    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bInfo: false,
            searching: true,
            // ordering: true,
            order: [[1, 'asc']],
            columnDefs: [{
                targets: [0],
                orderable: false
            }]
        });
    }

    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        $("<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>").insertAfter("#tblDataTable_filter label");
        $(`<div id="selectedSummaryContainer" class="d-flex align-items-center me-auto" style="gap: 10px;">
        <span class="selectedSum" style="display:none;">Total Mcs: </span>
        <span class="selectedSum" style="display:none;" id="totalSelectedMcsValue">0</span>
        <span class="selectedSum" style="display:none;"> Total Bags: </span>
        <span class="selectedSum" style="display:none;" id="totalSelectedBagsValue">0</span>
   </div>`).insertBefore("#tblDataTable_filter");

        $("#tblDataTable_filter").parent().addClass("d-flex justify-content-between align-items-center w-100");
        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }

    function poNumber(productionPlant) {
        var $drpPoNumber = $("#drpPoNumber");

        if (productionPlant) {
            $drpPoNumber.find('option').each(function() {
                var optionPlantId = $(this).data('plantid');
                var plantIdArray = String(optionPlantId).split(',');

                if (productionPlant != 0) {
                    if (productionPlant == 0 || plantIdArray.includes(String(productionPlant)) || $(this).val() === "") {
                        $(this).show();
                    } else {
                        $(this).remove();
                    }
                } else {
                    $(this).show();
                }
            });
        }
    }

    function plantAutoFill() {
        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpColdStore").prop('disabled', true);
            $("#drpColdStore").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');
        } else {
            $("#drpColdStore").prop('disabled', false);
            $("#drpColdStore").val("").trigger('change');
        }

        poNumber(productionPlant);
    }

    $(document).on("change", "#drpPoNumber", function() {
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

        $("#drpAbstatus").val("");
        $("#drpPDCategory").val("");

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
        var selectedVariety = $(this).val();
        $("#txtVariety").val(selectedVariety);

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
        $("#drpPckgStyle").val(packingStyleId).trigger("change");
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

    $(document).ready(function() {
        plantAutoFill();
        storeBlocks();
    });

    $(document).on("change", "#drpColdStore", function() {
        storeBlocks();
        var productionPlant = <?php echo $ProductionPlant ?>;
        poNumber(productionPlant);

    });

    function storeBlocks() {
        var blocksList = JSON.parse($("#hdnBlocks").val());

        var coldStoreId = $("#drpColdStore");

        var selectBlocks = $(coldStoreId).find(':selected').data("blocks");

        if (selectBlocks) {
            var selectBlocksAry = selectBlocks.split(",");

            var listBlock = "<option value=''>Select</option>";
            $.each(blocksList, function(index, val) {
                if (selectBlocksAry.indexOf(val.BlockID) !== -1) {
                    listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
                }
            });
            $("#drpBlocks").html("").html(listBlock);
        }
    }

    var poptable;

    $(document).on('shown.bs.modal', '#modalVarietyList', function() {
        if ($.fn.dataTable.isDataTable('.popVarietyTable')) {
            poptable.destroy();
        }
        poptable = $('.popVarietyTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            order: [],
            fixedColumns: true,
            scrollCollapse: true,
            bInfo: false,
            ordering: false
        });
    });

    $(document).ready(function() {

        function handleVarietyClick() {
            $('#modalVarietyList').modal('show');
        };

        $("#drpPoNumber").on("change", function() {
            var selectedValue = $(this).val();
            if (selectedValue != "") {
                $("#txtVariety").off("click", handleVarietyClick);
            } else {
                $("#txtVariety").on("click", handleVarietyClick);
            }
        });

    });

    function ShowList(pageNo, searchKey, loadAll) {
        $.ajax({
            url: 'Ajax/AjaxStockOut.php',
            type: 'POST',
            data: {
                "methodType": "getList",
                "productionID": "",
                "coldStore": $("#drpColdStore").val(),
                "packingType": $("#drpPackingType").val(),
                "shortName": $("#drpVariety").val(),
                "poNumber": $("#drpPoNumber").val(),
                "blocks": $("#drpBlocks").val(),
                "pckgStyle": $("#drpPckgStyle").val(),
                "grade": $("#drpGrade").val(),
                "pageNo": pageNo,
                "loadAll": loadAll,
                "searchKey": searchKey,
                "species": $("#drpSpecies").val(),
                "freezingType": $("#drpFreezingType").val(),
                "soaking": $("#drpSoaking").val(),
                "productType": $("#drpTypeProduct").val(),
                "abStatus": $("#drpAbstatus").val(),
                "productCategory": $("#drpPDCategory").val(),

            },
            success: function(data) {
                fnSuccess(data);
            }
        });



    }

    // $(document).ready(function() {
    // var poNumber = $("#drpPoNumber").val();
    $("#frmStockOutSearch").validate({

        onkeyup: false,
        onfocusout: false,
        invalidHandler: function(form, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var firstInvalidElement = $(validator.errorList[0].element);
                firstInvalidElement.focus();
            }
        },
        rules: {
            txtVariety: {
                required: function() {
                    var poNumber = $("#drpPoNumber").val();
                    return poNumber != '';
                }
            },
            drpVariety: {
                required: function() {
                    var poNumber = $("#drpPoNumber").val();
                    return poNumber === '';
                }
            },
            drpPckgStyle: {
                required: function() {
                    var poNumber = $("#drpPoNumber").val();
                    return poNumber === '';
                }
            },
            drpTypeProduct: {
                required: function() {
                    var poNumber = $("#drpPoNumber").val();
                    return poNumber === '';
                }
            }
        },
        errorPlacement: function() {
            return false; // suppresses error message text
        },
        submitHandler: function(form) {
            ShowList("1", "", "0");
            return false;
        }
    });
    // });

    $("#drpPoNumber").change(function() {

        $("#frmStockOutSearch").validate().element("#txtVariety");
        $("#frmStockOutSearch").validate().element("#drpVariety");
        $("#frmStockOutSearch").validate().element("#drpPckgStyle");
        $("#frmStockOutSearch").validate().element("#drpTypeProduct");


    });

    $(document).on("click", "#btnMoreSearch", function() {
        var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", searchkey, "0");
        }
    });

    $(document).on("click", "#btnLoadAll", function() {
        if (confirm("All the selected rows will be lost!, do you want to continue ?")) {
            $("#hSumMcs, #hSumLoosBags, #hdnSelectedRow").val("0");
            ShowList("1", "", "1");
        }
    });

    $(document).on("click", ".btnPageno", function() {
        if (confirm("All the selected rows will be lost!, do you want to continue ?")) {
            var pageno = $(this).attr("data-pageno");
            ShowList(pageno, "", "0");
        }
    });

    function checkOptions(unCheckVal) {
        
        var convertLooseToMcs = <?php echo $convertLooseToMcs ?>;
        var selectedOptions = [];
        $("input.rowCheck:checked").each(function() {
            selectedOptions.push(this.value);
        });
        const selectedSumMcsValue = selectedOptions.reduce((sum, number) => {
            let value = 0;
            if ($("#" + number + "-txtMCS").val()) {
                if (typeof $("#" + number + "-txtMCS").val() == 'string') {
                    value = parseFloat($("#" + number + "-txtMCS").val());
                } else if (typeof $("#" + number + "-txtMCS").val() == 'number') {
                    value = $("#" + number + "-txtMCS").val();
                }
            }
            return sum += value;
        }, 0)
        const selectedSumBagsValue = selectedOptions.reduce((sum, number) => {
            let value = 0;
            if ($("#" + number + "-txtLooseBags").val()) {
                if (typeof $("#" + number + "-txtLooseBags").val() == 'string') {
                    value = parseFloat($("#" + number + "-txtLooseBags").val());
                } else if (typeof $("#" + number + "-txtLooseBags").val() == 'number') {
                    value = $("#" + number + "-txtLooseBags").val();
                }
            }
            return sum += value;
        }, 0)

        var hdnSelectedVal = $('#hdnSelectedRow').val();
        if (hdnSelectedVal != "") {
            var sArray = hdnSelectedVal.split(",");
            var combinedArray = sArray.concat(selectedOptions);
            var uniqueArray = [];
            $.each(combinedArray, function(index, value) {
                if ($.inArray(value, uniqueArray) === -1) {
                    uniqueArray.push(value);
                }
            });
            if (unCheckVal) {
                uniqueArray.splice(uniqueArray.indexOf(unCheckVal), 1);
            }

            var joinedArray = uniqueArray.join(",");

            $('#hdnSelectedRow').val(joinedArray);
        } else {
            $('#hdnSelectedRow').val(selectedOptions.join(","));
        }

        if ($("#hdnSelectedRow").val().length > 0) {
            $("#btnStockOut").prop("disabled", false);

            if(convertLooseToMcs == 1){
                var totalSlabs = $("#drpPckgStyle").find(':selected').data("totalslabs");
                looseMcs = Math.floor(selectedSumBagsValue/totalSlabs);
                var FinalSelectedSumMcsValue = selectedSumMcsValue + looseMcs;
                var FinalSelectedSumBagsValue = selectedSumBagsValue - (looseMcs * totalSlabs);
                //   $LooseBags = $LooseBags - (abs($looseMcs) * $TotalSlabs);
                $("#totalSelectedMcsValue").text(FinalSelectedSumMcsValue);
                $("#totalSelectedBagsValue").text(FinalSelectedSumBagsValue);
            } else{
                $("#totalSelectedMcsValue").text(selectedSumMcsValue);
                $("#totalSelectedBagsValue").text(selectedSumBagsValue);
            }

            $(".selectedSum").show();
        } else {
            $("#btnStockOut").prop("disabled", true);
            $("#totalSelectedMcsValue").text(0);
            $("#totalSelectedBagsValue").text(0);
            $(".selectedSum").hide();
        }

    }

    function sumMcsLoosBags(msc, looseBags, isChecked) {
        if (isChecked == "checked") {
            $("#hSumMcs").val(parseFloat($("#hSumMcs").val()) + msc);
            $("#hSumLoosBags").val(parseFloat($("#hSumLoosBags").val()) + looseBags);
        } else {
            $("#hSumMcs").val(parseFloat($("#hSumMcs").val()) - msc);
            $("#hSumLoosBags").val(parseFloat($("#hSumLoosBags").val()) - looseBags);
        }
    }

    $(document).on("click", "input.rowCheck", function() {

        var isChecked = $(this).prop('checked');
        var msc = parseFloat($(this).closest("tr").find(".txtMCS").val());
        var looseBags = parseFloat($(this).closest("tr").find(".txtLooseBags").val());


        if (isChecked) {
            checkOptions();
            sumMcsLoosBags(msc, looseBags, "checked");
            $(this).closest("tr").find(".txtMCS").prop("disabled", true);
            $(this).closest("tr").find(".txtLooseBags").prop("disabled", true);
        } else {
            checkOptions($(this).val());
            sumMcsLoosBags(msc, looseBags, "unChecked");
            $(this).closest("tr").find(".txtMCS").prop("disabled", false);
            $(this).closest("tr").find(".txtLooseBags").prop("disabled", false);
        }
        $("#checkall").prop("checked", false);

    });

    $(document).on("click", "#checkall", function() {
        if (this.checked) {
            $(".rowCheck").prop("checked", true);
            checkOptions();
        } else {
            $(".rowCheck").prop("checked", false);
            checkOptions();
        }
    });

    function fnToday() {
        var today = new Date();
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var yyyy = today.getFullYear();
        var todayDate = yyyy + '-' + mm + '-' + dd;

        return todayDate;
    }

    function checkedRows() {

        var jsonObj = [];
        var sumMCS = 0;
        var sumLooseBags = 0;

        $("#tblDataTable tbody tr").each(function() {
            var checkbox = $(this).find(".rowCheck");

            if (checkbox.is(":checked")) {
                var hdnStockId = $(this).find(".hdnStockId").val();
                var hdnBarCodeID = $(this).find(".hdnBarCodeID").val();
                var hdnStoreID = $(this).find(".hdnStoreID").val();
                var hdnRackID = $(this).find(".hdnRackID").val();
                var hdnBlockID = $(this).find(".hdnBlockID").val();
                var txtMCS = $(this).find(".txtMCS").val();
                var txtLooseBags = $(this).find(".txtLooseBags").val();
                var prodid = $(this).find(".rowCheck").attr("data-prodid");

                // sumMCS = parseFloat(sumMCS) + parseFloat(txtMCS);
                // sumLooseBags = parseFloat(sumLooseBags) + parseFloat(txtLooseBags);
                sumMCS = parseFloat(sumMCS) + parseFloat(txtMCS);
                sumLooseBags = parseFloat(sumLooseBags) + parseFloat(txtLooseBags);

                jsonObj.push({
                    "StockID": hdnStockId,
                    "BarCodeID": hdnBarCodeID,
                    "StoreID": hdnStoreID,
                    "RackID": hdnRackID,
                    "OutMcs": txtMCS,
                    "OutLooseBags": txtLooseBags,
                    "BlockID": hdnBlockID,
                    "ProductionID": prodid
                });
            }
        });

        $("#hdnSumMCS").val(sumMCS);
        $("#hdnSumLooseBags").val(sumLooseBags);
        $("#hdnObjRowsSelected").val(JSON.stringify(jsonObj));

        var tdVariety = $(".tdVariety").text();
        var tdGrade = $(".tdGrade").text();
        var tdPackingStyle = $(".tdPackingStyle").text();

        var today = fnToday();

        $("#hSumMcs").val($("#totalSelectedMcsValue").text());
        $("#hSumLoosBags").val($("#totalSelectedBagsValue").text());

        var hSumMcs = $("#hSumMcs").val();
        var hSumLoosBags = $("#hSumLoosBags").val();

        var popRow = '<tr>' +
            '<td style="padding-top: 15px;">' + hSumMcs + '</td>' +
            '<td style="padding-top: 15px;">' + hSumLoosBags + '</td>' +
            '<td><div class="col-md-6 col-lg-3 mb-3" style="width: 100%"><input type="date" class="form-control txtStockOutDate" id="txtStockOutDate" name="txtStockOutDate" max="' + today + '" value="' + today + '"></div></td>' +
            '<td><div class="col-md-6 col-lg-3 mb-3" style="width:100%"><select class="form-control drpSelect" id="drpReason" name="drpReason"><option value="">Select</option><?php foreach ($resCsTransactionTypes as $resCs) {
                                                                                                                                                                                    if ($resCs->TransType == 2 or $resCs->TransType == 3) {
                                                                                                                                                                                        echo '<option value="' . $resCs->TransCategory . '" data-transtypeid="' . $resCs->TransTypeID . '" data-isreturnable="' . $resCs->IsReturnable . '" data-isstageselection ="' . $resCs->IsStageSelection . '">' . $resCs->TransCategory . '</option>';
                                                                                                                                                                                    }
                                                                                                                                                                                } ?> </select></div></td>' +
            '<td><div class="col-md-6 col-lg-3 mb-3" style="width:100%"><select class="form-control drpSelect drpReStockinStage" id="drpReStockinStage" name="drpReStockinStage"><option value="" data-stageid="">Select</option><option value="1" data-stageid="">Value Addition</option><option value="2" data-stageid="">Soaking</option><option value="3" data-stageid="">Freezing</option> </select></div></td>' +
            '<td><div class="col-md-6 col-lg-3 mb-3" style="width: 100%"><input type="text" class="form-control" id="txtRemarks" name="txtRemarks" value="" placeholder="Enter Remarks" /></div></td>' +
            '</tr>';
        $(".popTable tbody").html("").html(popRow);

        $('#drpReason').select2({
            dropdownParent: $('#ModalStockOut')
        });

        $(".popTable tbody").html("").html(popRow);
    }

    $("#btnStockOut").on("click", function() {
        // $("#tblDataTable_filter input").val("").focus();
        var oTable = $('#tblDataTable').dataTable();
        oTable.fnFilter('');
        checkedRows();
    });


    $(document).on("change", "#drpReason", function() {
        $("#drpReStockinStage").val("").trigger("change");
        // var reason = $("#drpReason option:selected").data("isreturnable");
        var reason = $("#drpReason option:selected").data("isstageselection");
        if (reason == 1) {
            $("#drpReStockinStage").prop("disabled", false);
        } else {
            $("#drpReStockinStage").prop("disabled", true);
        }
    });

    var jsonObj = [];


    var todayDate = "<?php echo date('Y-m-d') ?>";

    $("#btnPopStockOut").on("click", function() {


        var stockOutDate = $("#txtStockOutDate").val();
        var remarks = $("#txtRemarks").val();
        var totMsc = $("#hSumMcs").val(); // $("#hdnSumMCS").val();
        var totLooseBags = $("#hSumLoosBags").val(); //$("#hdnSumLooseBags").val();
        var reason = $("#drpReason").val();
        // var isReturnable = $("#drpReason option:selected").data("isreturnable");
        var isStageSelection = $("#drpReason option:selected").data("isstageselection");
        var restockinstage = $("#drpReStockinStage").val();
        var jsonRowsSelected = JSON.parse($("#hdnObjRowsSelected").val());
        var selectedTransTypeId = $(document).find('#drpReason option:selected').data('transtypeid');
        var productionPlant = '<?php echo $ProductionPlant ?>';
        var IsDeductGlaze = <?php echo $IsDeductGlaze ?>;
        var IsDeductGlazeFW = <?php echo $IsDeductGlazeFW ?>;
        var IsDeductFWFCGlaze = <?php echo $IsDeductFWFCGlaze ?>;

        var stockOutDatePrint = stockOutDate.split('-').reverse().join('/');

        if (reason == '') {
            $("#drpReason").addClass("error").focus();
        } else if (isStageSelection == 1 && restockinstage == '') {
            $("#drpReStockinStage").addClass("error").focus();
        } else if (stockOutDate > todayDate) {
            alert(stockOutDatePrint + " This Stock Out Date is not allowed.");
        } else {
            $("#drpReStockinStage").removeClass("error").focus();
            $.ajax({
                url: 'Ajax/AjaxStockOut.php',
                type: 'POST',
                data: {
                    "methodType": "stockOut",
                    "stockOutDate": stockOutDate,
                    "stockOutReason": reason,
                    "reStockinstage": restockinstage, //uma
                    "productionPlant": productionPlant, //uma
                    "remarks": remarks,
                    "totalMsc": totMsc,
                    "totalLooseBags": totLooseBags,
                    "stockItems": JSON.stringify(jsonRowsSelected),
                    "selectedTransTypeId": selectedTransTypeId,
                    "IsDeductGlaze": IsDeductGlaze,
                    "IsDeductGlazeFW": IsDeductGlazeFW,
                    "IsDeductFWFCGlaze" : IsDeductFWFCGlaze,
                },
                success: function(data) {
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        $("#btnPopStockOut").prop('disabled', true);
                        alert(data.Msg);
                        window.location.href = "stock-out";
                    }
                }
            });
        }
    });


    // $(document).on("keyup", ".txtMCS", function () {
    //     var actualMsc = parseFloat($(this).attr("data-mcs"));
    //     var enteredMsc = parseFloat($(this).val());
    //     if (enteredMsc > actualMsc) {
    //         alert("MCS exceeds actual value.");
    //         $(this).val(actualMsc);
    //     }
    // });

    // $(document).on("keyup", ".txtLooseBags", function () {
    //     var actualBags = parseFloat($(this).attr("data-bags"));
    //     var enteredBags = parseFloat($(this).val());
    //     if (enteredBags > actualBags) {
    //         alert("Loose Bags exceeds actual value.");
    //         $(this).val(actualBags);
    //     }
    // })


    $(document).on("keyup", ".txtMCS", function() {
        var actualMsc = parseFloat($(this).attr("data-mcs"));
        var enteredMsc = parseFloat($(this).val());
        if(enteredMsc < 0){
            alert("Minimum value allowed is " + actualMsc)
            $(this).val(actualMsc);
            return;
        }
        if (enteredMsc > actualMsc) {
            alert("MCS exceeds actual value.");
            $(this).val(actualMsc);
        } else {
            if (actualMsc == enteredMsc) {
                var $looseBagsField = $(this).closest("tr").find(".txtLooseBags");
                var actualBags = parseFloat($looseBagsField.attr("data-bags"));
                var enteredBags = parseFloat($looseBagsField.val());
                if (enteredBags > actualBags) {
                    alert("Loose Bags exceeds actual value.");
                    $(this).val(actualMsc);
                    $looseBagsField.val(actualBags);
                }
            }
        }
    });

    $(document).on("keyup", ".txtLooseBags", function() {
        var actualBags = parseFloat($(this).attr("data-bags"));
        var enteredBags = parseFloat($(this).val());
        if(enteredBags < 0){
            alert("Minimum value allowed is " + actualBags)
            $(this).val(actualBags);
            return;
        }

        var $mcsField = $(this).closest("tr").find(".txtMCS");
        var actualMsc = parseFloat($mcsField.attr("data-mcs"));
        var enteredMsc = parseFloat($mcsField.val());

        if (actualMsc == enteredMsc && enteredBags > actualBags) {
            alert("Loose Bags and MCS exceeds actual value.");
            $mcsField.val(actualMsc);
            $(this).val(actualBags);
        }
    })
</script>

</body>

</html>