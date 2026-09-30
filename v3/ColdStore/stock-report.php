<?php
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$ClientConfigData = $_SESSION['ClientConfigData'];
$BlockFieldsOtherThanPO = $ClientConfigData['BlockFieldsOtherThanPO'];  //sriram

$excludeList = array("Floors", "StoreRacks", "Stock", "ProductGroup", "Exporters", "Consignee", "Processor", "Shifts", "OptType", "OutType", "VarietyList", "StockInVarietyList", "Plant", "GeneralCategories", "Status", "TenantType", "ProductionLines", "BrandType", "Tranction", "ProductForm", "Preparation", "GlazeType", "ShipoutPoNumber", "ProductionPlant", "PoNumberNew", "TransactionTypesNew", "InwardGroups");
// $filterVarietyList = "T";
$filterMultipleStockAvailableVariety = "T";
$filterProductForm = "T";
$filterMultipleColdStores = "T";
$filterBlocksTwo = "T";
$filterMultipleStockBlgsTo = "T";
$filterMultiplePackingType = "T";
$filterMultiplePDCategory = "T";
$filterMultipleGrade = "T";
$filterMultiplePackingStyle = "T";
$filterInwardGroup = "T";
$filterMultipleFreezingType = "T";
$filterSpecies = "T";
$filterMultipleTypeProduct = "T";
$filterMultipleSoaking = "T";
$filterAbstatus = "T";
$filterPoNumber = "T";
$filterType = "T";
// $filterClosingDate = "T"; //
$filterBrand = "T";
$buttomButton = "T";

$ProductionPlant = $_SESSION['UnitID'];

?>

<!doctype html>
<html lang="en">


<head>
    <style>
        .modal {
            z-index: 1050;
            /* Bootstrap default value */
        }
    </style>
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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php'; ?>


                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <input type="hidden" id="hdnBlocks"
                            value="<?php echo htmlspecialchars(json_encode($resBlockData), ENT_QUOTES, 'UTF-8'); ?>" />
                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">
                                </div>
                                <input type="hidden" id="hdnActionType" />
                            </div>
                        </div>
                    </div>
                </div>
                <div id="myModal" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-fullscreen">

                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Rackwise data</h4>
                            </div>

                            <div class="modal-body">

                                <div class="row">

                                    <div class="col-md-12 table-responsive">
                                        <table id="PopUpTable" class="table table-bordered ">
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"
                                    aria-label="Close">Close</button>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
            <!-- End Page-content -->
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
    <!-- included for fixed column -->
    <script
        src="https://cdn.jsdelivr.net/npm/datatables.net-fixedcolumns@4.3.0/js/dataTables.fixedColumns.min.js"></script>
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/datatables.net-fixedcolumns-dt@4.3.0/css/fixedColumns.dataTables.min.css" />
    <!-- included for fixed column -->
    <?php include_once ROOT . '/includes/app-js.php' ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>


    <script>
        function fnShowDataTable(tableId) {
            tableId = tableId ? tableId : '#tblDataTable';
            /* if ($.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable().clear().destroy();
            } */
            var table = $(tableId).DataTable({
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
                    title: 'Stock Report <?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
                footerCallback: function (row, data, start, end, display) {
                    let api = this.api();
                    let mcs = 0, loose = 0, slabs = 0, kgs = 0;
                    api.rows({ search: 'applied' }).every(function () {
                        let row = $(this.node());
                        mcs    += parseFloat(row.find('.mcs').text().replace(/,/g, '')) || 0;
                        loose  += parseFloat(row.find('.looseBags').text().replace(/,/g, '')) || 0;
                        slabs  += parseFloat(row.find('.slabs').text().replace(/,/g, '')) || 0;
                        kgs    += parseFloat(row.find('.kgs').text().replace(/,/g, '')) || 0;
                    });
                    let thGradesLegth = $(api.table().header()).find('.thGrades').length;
                    let gradeTotals = [];
                    for (let i = 0; i < thGradesLegth; i++) {
                        gradeTotals[i] = 0;
                        let GradeMcs = 0, GradeLoose = 0;
                        api.rows({ search: 'applied' }).nodes().to$()
                        .find('.Grade' + i)
                        .each(function () {
                            GradeMcs += Number($(this).attr('data-grade_mcs') || 0);
                            GradeLoose += Number($(this).attr('data-grade_looseBags') || 0);
                            gradeTotals[i] = GradeMcs + '/' + GradeLoose;
                        });
                    }

                    $(api.column($('.mcs').index()).footer()).html(mcs);
                    $(api.column($('.looseBags').index()).footer()).html(loose);
                    $(api.column($('.slabs').index()).footer()).html(slabs);
                    $(api.column($('.kgs').index()).footer()).html(kgs.toFixed(2));
                    // $.each(gradeTotals, function(i, value) {
                    //     $(api.column($('.GradeTotal'+i).index()).footer()).html(value);
                    // });
                }
                //   fixedColumns: {
                // leftColumns: 13 // Freeze until Soaking
                // }, 
            });
        }

        $(document).ready(function () {
            $("#drpPDCategory").val(null).trigger('change')
            var productionPlant = <?php echo $ProductionPlant ?>;
            if (productionPlant != 0) {
                $("#drpColdStore").prop('disabled', true);
                var matchedColdStoreIds = $("[data-plant='" + productionPlant + "']").map(function () {
                    return $(this).val();
                }).get();
                $("#drpColdStore").val(matchedColdStoreIds).trigger('change');

            } else {
                $("#drpColdStore").prop('disabled', false);
                $("#drpColdStore").val([]).trigger('change');
            }

            var $drpPoNumber = $("#drpPoNumber");

            if (productionPlant) {
                $drpPoNumber.find('option').each(function () {
                    var optionPlantId = $(this).data('plantid');

                    var plantIdArray = String(optionPlantId).split(',');

                    if (plantIdArray.includes(String(productionPlant)) || $(this).val() === "") {
                        $(this).show();
                    } else {
                        $(this).remove();
                    }
                });
            }
            storeBlocks()

            if (productionPlant != 0) {
                var matchedTenantIds = $("[data-processorID='" + productionPlant + "']").map(function () {
                    return $(this).val();
                }).get();
                $("#drpStockBlgsTo").val(matchedTenantIds).trigger('change');
            } else {
                $("#drpStockBlgsTo").val([]).trigger('change');
            }

            // Initialize Select2 on the multi-selects
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

            $("#drpGrade").select2({
                placeholder: "Select Grade(s)",
                width: '100%'
            });

            $("#drpPckgStyle").select2({
                placeholder: "Select Packing Style(s)",
                width: '100%'
            });

            // Variety is now a multi-select; aggregate the shortname of every
            // selected option into the hidden field the AJAX call reads from.
            $("#drpVariety").on("change", function () {
                var shortNameArr = [];
                $(this).find(':selected').each(function () {
                    var shortName = $.trim($(this).data('shortname'));
                    if (shortName) shortNameArr.push(shortName);
                });
                shortNameArr = [...new Set(shortNameArr)];
                $("#hdnFilterShortName").val(shortNameArr.join(","));
            });

            if ($("#drpVariety").hasClass("select2-hidden-accessible")) {
                $("#drpVariety").select2('destroy');
            }

            $("#drpVariety").select2({
                placeholder: "Select Variety(ies)",
                width: '100%',
                closeOnSelect: false,
                dropdownParent: $('body')
            });

            $(document).off("click", ".show-rack-popup");
            $(document).on("click", ".show-rack-popup", function () {
                const jsonData = JSON.parse(this.getAttribute('data-info'));
                jsonData.pageNo = 1;
                jsonData.searchKey = '';
                jsonData.loadAll = 1;

                ShowRackDataList(jsonData)
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    const modal = document.querySelector('.modal.show');
                    if (modal) {
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        modalInstance?.hide(); // Manually hide the modal
                    }
                }
            });

        });

        function ShowRackDataList(jsonData) {
            $.ajax({
                url: 'Ajax/ajax-rackwise-stock-report.php',
                type: 'POST',
                data: jsonData,
                success: function (data) {
                    $("#PopUpTable").html(data);
                    $('#myModal').modal('show');
                    // fnShowDataTable("#tblDataTable");

                    return false;
                }
            });
        }
        function fnSuccess(data) {

            $("#divDataList").html(data);
            fnShowDataTable();
            var pdCategoryVal = $('#drpPDCategory').val();
            if (!pdCategoryVal || pdCategoryVal.length === 0) {
                $('#tblDataTable2').DataTable({
                    scrollY: "600px",
                    scrollX: true,
                    sScrollXInner: "100%",
                    paging: false,
                    destroy: true,
                    scrollCollapse: true,
                    //bInfo: false,
                    info: false,
                    searching: false,
                    // fixedColumns: {
                    //     leftColumns: 2 // Freeze until Soaking
                    // },
                })
            }
            $('html, body').animate({
                scrollTop: $("#divDataList").offset().top - 50
            }, 10);
        }

        $(document).on("change", "#drpPoNumber", function () {
            var BlockFieldsOtherThanPO = <?php echo $BlockFieldsOtherThanPO ?>;
            var poVal = $("#drpPoNumber").val();

            // If PO has no value (empty, null, undefined) → enable
            if (BlockFieldsOtherThanPO == 1 && poVal) {
                // Disable when PO selected
                $("#drpBlocks, #drpSpecies, #drpTypeProduct, #drpFreezingType, #drpSoaking, #drpBrand, #drpVariety, #drpGrade, #drpGroup, #drpPckgStyle, #drpPackingType, #drpPDCategory, #drpAbstatus")
                    .val(null).trigger('change').prop("disabled", true);

                $("#drpStockBlgsTo").trigger('change').prop("disabled", true);

            } else {
                // Enable when no PO selected
                $("#drpBlocks, #drpSpecies, #drpTypeProduct, #drpFreezingType, #drpSoaking, #drpBrand, #drpVariety, #drpGrade, #drpGroup, #drpPckgStyle, #drpPackingType, #drpPDCategory, #drpAbstatus")
                    .prop("disabled", false);
                $("#drpStockBlgsTo").trigger('change').prop("disabled", false);
            }
        });


        $(document).on("change", "#drpColdStore", function () {
            storeBlocks();
        });


        function storeBlocks() {
            var blocksList = JSON.parse($("#hdnBlocks").val());
            var $coldStore = $("#drpColdStore");

            // Aggregate the "blocks" data attribute across every selected cold store
            // (drpColdStore is now a multi-select).
            var selectedBlockIds = [];
            $coldStore.find(':selected').each(function () {
                var selectBlocks = $(this).data('blocks');
                if (selectBlocks) {
                    var ids = String(selectBlocks).split(',').map(function (id) {
                        return id.trim();
                    });
                    selectedBlockIds = selectedBlockIds.concat(ids);
                }
            });
            selectedBlockIds = [...new Set(selectedBlockIds)];

            if (selectedBlockIds.length > 0) {
                var listBlock = "<option value=''>Select</option>";
                $.each(blocksList, function (index, val) {
                    if (selectedBlockIds.indexOf(String(val.BlockID)) !== -1) {
                        listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
                    }
                });
                $("#drpBlocks").html("").html(listBlock);
            } else {
                $("#drpBlocks").html("<option value=''>Select</option>");
            }
        }

        // Helper: turn a jQuery multi-select .val() (array or null) into a comma-separated string
        function joinMultiVal(selector) {
            var v = $(selector).val();
            return v && v.length ? v.join(",") : "";
        }

        function ShowList(pageNo, searchkey, loadAll) {

            $.ajax({
                url: 'Ajax/ajax-stock-report.php',
                type: 'POST',
                data: {
                    coldStore: joinMultiVal("#drpColdStore"),
                    blocksTwo: $.trim($("#drpBlocks").val()),
                    stockBlgsTo: joinMultiVal("#drpStockBlgsTo"),
                    variety: $("#hdnFilterShortName").val() || "",
                    packingType: joinMultiVal("#drpPackingType"),
                    pdCategory: joinMultiVal("#drpPDCategory"),
                    group: $.trim($("#drpGroup").val()),
                    pckgStyle: joinMultiVal("#drpPckgStyle"),
                    packingstyleName: $("#drpPckgStyle option:selected").map(function () {
                        return $(this).data('packingstyle');
                    }).get().join(","),
                    grade: joinMultiVal("#drpGrade"),
                    freezingType: joinMultiVal("#drpFreezingType"),
                    productType: joinMultiVal("#drpTypeProduct"),
                    soaking: joinMultiVal("#drpSoaking"),
                    abStatus: $.trim($("#drpAbstatus").val()),
                    poNumber: $.trim($("#drpPoNumber").val()),
                    species: $.trim($("#drpSpecies").val()),
                    Type: $.trim($("#drpType").val()),
                    // closingDate: $.trim($("#txtClosingDate").val()), //
                    brand: $.trim($("#drpBrand").val()),
                    brandname: $("#drpBrand option:selected").attr('data-brandname'),
                    pageNo: pageNo,
                    searchKey: searchkey,
                    loadAll: loadAll,
                },
                success: function (data) {
                    fnSuccess(data);
                    return false;
                }
            });

        }

        $(document).on("click", "#btnMoreSearch", function () {
            var searchkey = $("#tblDataTable_filter input").val();
            if (searchkey.length > 0) {
                ShowList("1", searchkey, "0");
            }
        });

        $(document).on("click", "#btnLoadAll", function () {
            ShowList("1", "", "1");
        });

        $(document).on("click", ".btnPageno", function () {
            var pageno = $(this).attr("data-pageno");
            ShowList(pageno, "", "0");
        });

        $("#btnSearch").on("click", function () {
            ShowList("1", "", "0");
        });
    </script>

    </body>

</html>