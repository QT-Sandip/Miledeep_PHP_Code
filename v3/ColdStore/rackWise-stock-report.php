<?php

session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$excludeList = array("Floors","StoreRacks","Stock","ProductGroup","Exporters","Consignee","Processor","Shifts","OptType","OutType","Variety","VarietyList","StockInVarietyList","PoNumber","Plant","GeneralCategories","Status","TenantType","ProductionLines","Brand","BrandType","Tranction","Type","CsTransactionTypes","ProductForm","Preparation","GlazeType","ShipoutPoNumber","ProductionPlant","TransactionTypesNew","InwardGroups");
$filterMultipleColdStores = "T";
// $filterBlocks = "T";
$filterBlocksTwo = "T";
$filterMultipleStockBlgsTo = "T";
// $filterPoNumber = "T";
$filterPoNumberNew = "T";
// $filterVarietyList = "T";
$filterMultipleStockAvailableVariety = "T";
$filterMultipleGrade = "T";
$filterMultiplePackingStyle = "T";
$filterMultiplePackingType = "T";
$filterMultiplePDCategory = "T";
$filterAbstatus = "T";
$buttomButton = "T";
$filterSpecies = "T";
$filterMultipleFreezingType = "T";
$filterMultipleTypeProduct = "T";
$filterMultipleSoaking = "T";
$filterStockType = "T";


$ProductionPlant = $_SESSION['UnitID'];

?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <style>
        /* .select2-container--open {
                z-index: 9999999
            }             */
    </style>

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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <div id="divDataList">

                                </div>
                                <input type="hidden" id="hdnActionType" />
                            </div>

                        </div>

                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <?php include_once ROOT . '/includes/footer.php' ?>

    </div>
    <!-- end main content-->
</div>
<!-- END layout-wrapper -->


<?php include_once ROOT . '/includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>


<script>
    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                //fixedColumns: true,
                scrollCollapse: true,
                bInfo: false,
                dom: 'Blfrtip',
                // dom: '<"top-left"Blfrtip>',
                searching: true,
                search: {
                    smart: false
                },
                buttons: [{
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'btnExcelDataTable btn-left-align',
                    title: 'Rackwise Stock Report <?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
        });
    }

        $(document).ready(function() {

            var productionPlant = <?php echo $ProductionPlant ?>;
            if (productionPlant) {
                $("#drpColdStore").prop('disabled', true);
                var matchedColdStoreIds = $("[data-plant='" + productionPlant + "']").map(function() {
                    return $(this).val();
                }).get();
                $("#drpColdStore").val(matchedColdStoreIds).trigger('change');

            } else {
                $("#drpColdStore").prop('disabled', false);
                $("#drpColdStore").val([]).trigger('change');
            }

            storeBlocks();

            if (productionPlant) {
                var $drpPoNumber = $("#drpPoNumber");
                $drpPoNumber.find('option').each(function() {
                    var optionPlantId = $(this).data('plantid');

                    var plantIdArray = String(optionPlantId).split(',');

                    if (plantIdArray.includes(String(productionPlant)) || $(this).val() === "") {
                        $(this).show();
                    } else {
                        $(this).remove();
                    }
                });
            }

        $("#drpPoNumber").on("change", function() {

        var hdnGradeList = JSON.parse($("#hdnGradeList").val());
        var hdnPackingStyleList = JSON.parse($("#hdnPackingStyle").val());

        // preserve currently selected values before rebuilding
        var currentSelectedGrades = $("#drpGrade").val() || [];
        var currentSelectedPackingStyles = $("#drpPckgStyle").val() || [];

        if ($("#drpPoNumber").val() !== '') {

            // --- Grade ---
            var gradeId = $("#drpPoNumber option:selected").data('grade');
            var allowedGradeIds = gradeId.split(",");

            var listGrade = "";
            $.each(hdnGradeList, function(index, val) {
                if (allowedGradeIds.indexOf(val.value.toString()) !== -1) {
                    var isSelected = currentSelectedGrades.indexOf(val.value.toString()) !== -1 ? 'selected' : '';
                    listGrade += '<option value="' + val.value + '" ' + isSelected + '>' + val.value + '</option>';
                }
            });
            $("#drpGrade").html(listGrade);

            // --- Packing Style ---
            var packingstyleId = $("#drpPoNumber option:selected").data('packingstyleid').toString();
            var allowedPackingStyleId = packingstyleId.split(",");

            var listPackingStyle = "";
            $.each(hdnPackingStyleList, function(index, val) {
                if (allowedPackingStyleId.indexOf(val.PCKID.toString()) !== -1) {
                    var isSelected = currentSelectedPackingStyles.indexOf(val.PCKID.toString()) !== -1 ? 'selected' : '';
                    listPackingStyle += '<option value="' + val.PCKID + '" ' + isSelected + '>' + val.PackingStyle + '</option>';
                }
            });
            $("#drpPckgStyle").html(listPackingStyle);

        } else {

            // --- Grade (no PO selected, show all) ---
            var listGrade = "";
            $.each(hdnGradeList, function(index, val) {
                listGrade += '<option value="' + val.value + '">' + val.value + '</option>';
            });
            $("#drpGrade").html(listGrade);

            // --- Packing Style (no PO selected, show all) ---
            var listPackingStyle = "";
            $.each(hdnPackingStyleList, function(index, val) {
                listPackingStyle += '<option value="' + val.PCKID + '">' + val.PackingStyle + '</option>';
            });
            $("#drpPckgStyle").html(listPackingStyle);
        }

        // refresh Select2 UI to reflect the rebuilt options
        $("#drpGrade").trigger('change');
        $("#drpPckgStyle").trigger('change');
    });

    // Initialize Select2 on all multi-selects
    $("#drpGrade").select2({
        placeholder: "Select Grade(s)",
        width: '100%'
    });

    $("#drpPckgStyle").select2({
        placeholder: "Select Packing Style(s)",
        width: '100%'
    });

    $("#drpColdStore").select2({
        placeholder: "Select Cold Store(s)",
        width: '100%'
    });

    $("#drpStockBlgsTo").select2({
        placeholder: "Select Stock Belongs To",
        width: '100%'
    });

    $("#drpTypeProduct").select2({
        placeholder: "Select Type Product(s)",
        width: '100%'
    });

    $("#drpFreezingType").select2({
        placeholder: "Select Freezing Type(s)",
        width: '100%'
    });

    $("#drpSoaking").select2({
        placeholder: "Select Soaking(s)",
        width: '100%'
    });

    $("#drpPackingType").select2({
        placeholder: "Select Packing Type(s)",
        width: '100%'
    });

    $("#drpPDCategory").select2({
        placeholder: "Select Product Category(ies)",
        width: '100%'
    });


        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            console.log('productionPlant' + productionPlant);
            var matchedTenantIds = $("[data-processorID='" + productionPlant + "']").map(function() {
                return $(this).val();
            }).get();
            $("#drpStockBlgsTo").val(matchedTenantIds).trigger('change');
        } else {
            $("#drpStockBlgsTo").val([]).trigger('change');
        }
    });

    $(document).on('change', '#drpColdStore', function() {
        storeBlocks();
    });

    function storeBlocks() {
        var blocksList = JSON.parse($("#hdnBlocks").val());
        var $coldStore = $("#drpColdStore");

        // Aggregate the "blocks" data attribute across every selected cold store
        // (drpColdStore is now a multi-select).
        var selectedBlockIds = [];
        $coldStore.find(':selected').each(function() {
            var selectBlocks = $(this).data('blocks');
            if (selectBlocks) {
                var ids = String(selectBlocks).split(',').map(function(id) {
                    return id.trim();
                });
                selectedBlockIds = selectedBlockIds.concat(ids);
            }
        });
        selectedBlockIds = [...new Set(selectedBlockIds)];

        if (selectedBlockIds.length > 0) {
            var listBlock = "<option value=''>Select</option>";
            $.each(blocksList, function(index, val) {
                if (selectedBlockIds.indexOf(String(val.BlockID)) !== -1) {
                    listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
                }
            });
            $("#drpBlocks").html("").html(listBlock);
        } else {
            $("#drpBlocks").html("<option value=''>Select</option>");
        }
    }

    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
                "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>"
            )
            .insertAfter("#tblDataTable_filter label");

        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }

$("#drpVariety").on("change", function() {
    var shortNameArr = [];

    $(this).find(':selected').each(function() {
        var shortName = $.trim($(this).data('shortname'));
        if (shortName) shortNameArr.push(shortName);
    });

    shortNameArr = [...new Set(shortNameArr)];
    $("#hdnFilterShortName").val(shortNameArr.join(","));
});

// destroy any existing instance before initializing, in case something else already bound select2 to this element
if ($("#drpVariety").hasClass("select2-hidden-accessible")) {
    $("#drpVariety").select2('destroy');
}

$("#drpVariety").select2({
    placeholder: "Select Variety(ies)",
    width: '100%',
    closeOnSelect: false,
    dropdownParent: $('body')
});

    // Helper: turn a jQuery multi-select .val() (array or null) into a comma-separated string
    function joinMultiVal(selector) {
        var v = $(selector).val();
        return v && v.length ? v.join(",") : "";
    }

    function ShowList(pageNo, searchkey, loadAll) {
        // convert multi-select arrays to comma-separated strings
        var varietyVal = joinMultiVal("#drpVariety");
        var gradeVal = joinMultiVal("#drpGrade");
        var pckgStyleVal = joinMultiVal("#drpPckgStyle");
        var coldStoreVal = joinMultiVal("#drpColdStore");
        var stockBlgsToVal = joinMultiVal("#drpStockBlgsTo");
        var typeProductVal = joinMultiVal("#drpTypeProduct");
        var freezingTypeVal = joinMultiVal("#drpFreezingType");
        var soakingVal = joinMultiVal("#drpSoaking");
        var packingTypeVal = joinMultiVal("#drpPackingType");
        var prodCategoryVal = joinMultiVal("#drpPDCategory");

        var fSpeciesVal = $("#drpSpecies").val() || "";
        var fShortNameVal = $("#hdnFilterShortName").val() || "";

        $.ajax({
            url: 'Ajax/ajax-rackwise-stock-report.php',
            type: 'POST',
            data: {
                coldStore: coldStoreVal,
                stockBlgsTo: stockBlgsToVal,
                poNo: $("#drpPoNumber").val(),
                variety: varietyVal,
                grade: gradeVal,
                pckgStyle: pckgStyleVal,
                pckgType: packingTypeVal,
                prodCategory: prodCategoryVal,
                fSpecies: fSpeciesVal,
                fShortName: fShortNameVal,
                fTypeProduct: typeProductVal,
                blocks: $("#drpBlocks").val(),
                abStatus: $.trim($("#drpAbstatus").val()),
                pageNo: pageNo,
                searchKey: searchkey,
                loadAll: loadAll,
                species: $("#drpSpecies").val(),
                freezingType: freezingTypeVal,
                fsoaking: soakingVal,
                productType: typeProductVal,
                stockType: $("#drpStockType").val(),
            },
            success: function(data) {
                fnSuccess(data);
                return false;
            }
        });
    }
    // $(document).ready(function() {
    //     ShowList("1", "", "0");
    // });

    $(document).on("click", "#btnMoreSearch", function() {
        var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", searchkey, "0");
        }
    });

    $(document).on("click", "#btnLoadAll", function() {
        ShowList("1", "", "1");
    });

    $(document).on("click", ".btnPageno", function() {
        var pageno = $(this).attr("data-pageno");
        ShowList(pageno, "", "0");
    });

    $("#btnSearch").on("click", function() {
        ShowList("1", "", "0");
    });
$("#drpColdStore, #drpPoNumber").on("change", function () {
    $("#divDataList").html("");
});
</script>

</body>

</html>
