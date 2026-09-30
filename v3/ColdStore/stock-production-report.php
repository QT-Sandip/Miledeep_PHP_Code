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
$filterStockAvailableVariety = "T";
$filterProductForm = "T";
$filterColdStores = "T";
$filterBlocksTwo = "T";
$filterStockBlgsTo = "T";
$filterPackingType = "T";
$filterPDCategory = "T";
$filterGrade = "T";
$filterPckgStyle = "T";
$filterInwardGroup = "T";
$filterFreezingType = "T";
$filterSpecies = "T";
$filterTypeProduct = "T";
$filterSoaking = "T";
$filterAbstatus = "T";
$filterPoNumber = "T";
$filterType = "T";
$filtertoDate = "T";
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
                            <h6>Note : Production Quantity is displayed in brackets AS (P-)</h6>
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
    <style>
        
    .fixed-col{
        min-width: 70px !important;
        max-width: 70px !important;
        width: 70px !important;
    }
    </style>

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
                dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"l f B><"col-md-6">>rtip',
                // dom: '<"top-left"Blfrtip>',

                buttons: [{
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'btnExcelDataTable btn-left-align',
                    title: 'Stock Production Report <?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
                footerCallback: function (row, data, start, end, display) {
                    let api = this.api();
                    let mcs = 0, slabs = 0, kgs = 0;
                    api.rows({ search: 'applied' }).every(function () {
                        let row = $(this.node());
                        mcs    += parseFloat(row.find('.mcs').text().replace(/,/g, '')) || 0;
                        slabs  += parseFloat(row.find('.slabs').text().replace(/,/g, '')) || 0;
                        kgs    += parseFloat(row.find('.kgs').text().replace(/,/g, '')) || 0;
                    });

                    let thGradesLegth = $(api.table().header()).find('.thGrades').length;
                    let gradeTotals = [];
                    for (let i = 0; i < thGradesLegth; i++) {
                        gradeTotals[i] = 0;
                        let GradeMcs = 0;
                        api.rows({ search: 'applied' }).nodes().to$()
                        .find('.Grade' + i)
                        .each(function () {
                            GradeMcs += Number($(this).attr('data-grade_mcs') || 0);
                            gradeTotals[i] = GradeMcs ;
                        });
                    }

                    $(api.column($('.mcs').index()).footer()).html(mcs.toFixed(2));
                    $(api.column($('.slabs').index()).footer()).html(slabs.toFixed(2));
                    $(api.column($('.kgs').index()).footer()).html(kgs.toFixed(2));
                    $.each(gradeTotals, function(i, value) {
                        $(api.column($('.GradeTotal'+i).index()).footer()).html(value.toFixed(2));
                    });
                },
                fixedColumns: {
                    leftColumns: 7 // Freeze
                },

                columnDefs: [
                    {
                        targets: [0,1,2,3,4,5,6],
                        className: 'fixed-col'
                    }
                ]
            });
        }

        $(document).ready(function () {
            $("#drpPDCategory").val(null).trigger('change')
            var productionPlant = <?php echo $ProductionPlant ?>;
            if (productionPlant != 0) {
                $("#drpColdStore").prop('disabled', true);
                $("#drpColdStore").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');

            } else {
                $("#drpColdStore").prop('disabled', false);
                $("#drpColdStore").val("").trigger('change');
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
                $("#drpStockBlgsTo").val($("[data-processorID='" + productionPlant + "']").val()).trigger('change');
            } else {
                $("#drpStockBlgsTo").val("").trigger('change');
            }

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
            if ($('#drpPDCategory').val() == '') {
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
                    .val('').trigger('change').prop("disabled", true);

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
            var coldStoreId = $("#drpColdStore");

            var selectBlocks = $(coldStoreId).find(':selected').data("blocks");

            if (selectBlocks) {
                var selectBlocksAry = selectBlocks.split(",");

                var listBlock = "<option value=''>Select</option>";
                $.each(blocksList, function (index, val) {
                    if (selectBlocksAry.indexOf(val.BlockID) !== -1) {
                        listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
                    }
                });
                $("#drpBlocks").html("").html(listBlock);
            } else {
                $("#drpBlocks").html("<option value=''>Select</option>");
            }
        }


        function ShowList(pageNo, searchkey, loadAll) {

            $.ajax({
                url: 'Ajax/ajax-stock-production-report.php',
                type: 'POST',
                data: {
                    coldStore: $.trim($("#drpColdStore").val()),
                    blocksTwo: $.trim($("#drpBlocks").val()),
                    stockBlgsTo: $.trim($("#drpStockBlgsTo").val()),
                    variety: $("#drpVariety option:selected").attr('data-shortname'),
                    packingType: $.trim($("#drpPackingType").val()),
                    pdCategory: $.trim($("#drpPDCategory").val()),
                    group: $.trim($("#drpGroup").val()),
                    pckgStyle: $("#drpPckgStyle").val(),
                    packingstyleName: $("#drpPckgStyle option:selected").attr('data-packingstyle'),
                    grade: $("#drpGrade").val(),
                    freezingType: $.trim($("#drpFreezingType").val()),
                    productType: $.trim($("#drpTypeProduct").val()),
                    soaking: $.trim($("#drpSoaking").val()),
                    abStatus: $.trim($("#drpAbstatus").val()),
                    poNumber: $.trim($("#drpPoNumber").val()),
                    species: $.trim($("#drpSpecies").val()),
                    Type: $.trim($("#drpType").val()),
                    StockDate: $.trim($("#txtToDate").val()),
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

        $("#btnSearch").on("click", function() {
            var typePrduct = $("#drpTypeProduct").val();
            var freezingType = $("#drpFreezingType").val();
            var drpColdStore = $("#drpColdStore").val();
            if (drpColdStore == '') {
                $("#drpColdStore").focus().addClass('red-focus');
                alert("Select Cold Store");
            /*}else if (typePrduct == '') {
                $("#drpTypeProduct").focus().addClass('red-focus');
                alert("Select Type Product");
            } else if (typePrduct == "RAW" && freezingType == '') {
                $("#drpFreezingType").focus().addClass('red-focus');
                alert("Select Freezing Type");
            */} else {
                ShowList("1", "", "0");
            }
        });

$("#drpColdStore, #drpPoNumber").on("change", function () {
    $("#divDataList").html("");
});
    </script>

    </body>

</html>
