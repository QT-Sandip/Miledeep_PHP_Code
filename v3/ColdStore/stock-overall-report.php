<?php
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$excludeList = array("Blocks","Floors","StoreRacks","Stock","StockBelongsTo","ProductGroup","Exporters","Consignee","Processor","Shifts","OptType","OutType","Variety","StockAvailableVariety","StockInVarietyList","PoNumber","GeneralCategories","Status","TenantType","ProductionLines","Brand","BrandType","Tranction","Type","CsTransactionTypes","ProductForm","Preparation","GlazeType","ShipoutPoNumber","ProductionPlant","PoNumberNew","InwardGroups");
$filterfromDate = "T";
$filtertoDate = "T";
$filterColdStores = "T";
$filterPlant = "T";
$filterShortName = "T";
$filterGrade = "T";
$filterOverallGroup = "T";
$filterPckgStyle = "T";
$filterPDCategory = "T";

$filterFreezingType = "T";
$filterPackingType = "T";
$filterSpecies = "T";
$filterAbstatus = "T";
$filterTypeProduct = "T";
$filterSoaking = "T";

$buttomButton = "T";

$ProductionPlant = isset($_SESSION['UnitID']) ? $_SESSION['UnitID'] : 0;
?>

<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>
</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

<div id="layout-wrapper">

    <?php include_once ROOT . '/includes/menu.php' ?>

    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . '/includes/page-header.php' ?>
                <?php include_once WORKING_MODULE . '/includes/page-filter.php' ?>

                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">

                                <div id="divDataList">
                                    <table id="tblDataTable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Production Date</th>
                                                <th>Stock Out Date</th>
                                                <th>Variety</th>
                                                <th>Product Description</th>
                                                <th>Packing Style</th>
                                                <th>Grade</th>
                                                <th>Group</th>
                                                <th>Product Plant</th>
                                                <th>Cold Store</th>
                                                <th>Product Category</th>
                                                <th>Mcs</th>
                                                <th>Kgs</th>
                                                <th>Slabs</th>
                                                <th>HL Yield</th>
                                                <th>Finish HL Weight</th>
                                                <th>HON Yield</th>
                                                <th>Finish HON Weight</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th></th> <th></th> <th></th> <th></th> <th></th> <th></th> <th></th> <th></th> <th></th> <th></th> <th style="text-align:right">Total:</th> <th></th> <th></th> <th></th> <th></th> <th></th> <th></th> <th></th> </tr>
                                            </tfoot>
                                    </table>
                                </div>
                                <input type="hidden" id="hdnActionType" />
                            </div>

                        </div>

                    </div>
                </div>

            </div> </div>
        <?php include_once ROOT . '/includes/footer.php' ?>

    </div>
    </div>
<?php include_once ROOT . '/includes/common-js.php' ?>

<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>

<script>
$(document).ready(function() {

    // Track if user clicked search to stop auto-execution on window initialization
    var searchExecuted = false;

    // 1. Initialize the DataTable blueprint on the active DOM element
    var table = $('#tblDataTable').DataTable({
        processing: true,
        serverSide: true,
        scrollY: "600px",
        scrollX: true,
        sScrollXInner: "100%",
        paging: true,
        pageLength: 1000,
        lengthMenu: [
            [10, 25, 50, 100, 1000],
            [10, 25, 50, 100, 1000]
        ],
        scrollCollapse: true,
        bInfo: true,
        bSort: false,
        language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
        //dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 custom-filters">>rtip',
        dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex align-items-center justify-content-end custom-filters"B>>rtip',
        buttons: [{
        extend: 'excel',
        text: 'Download Excel',
        className: 'btnExcelDataTable btn-left-align',
        title: 'Stock Overall Report <?php echo date("d-m-Y") ?>',
        footer: true, // This ensures DataTables includes the <tfoot> values in the Excel output
        exportOptions: { columns: ':visible' },
        action: function (e, dt, node, config) {
            var self = this;
            var oldStart = dt.settings()[0]._iDisplayStart;
            var oldLength = dt.settings()[0]._iDisplayLength;

            dt.one('preXhr', function (e, s, data) {
                data.start = 0;
                data.length = -1;
            });

            dt.one('draw', function () {
                if ($.fn.dataTable.ext.buttons.excelHtml5.action) {
                    $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, node, config);
                } else if ($.fn.dataTable.ext.buttons.excelFlash.action) {
                    $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, node, config);
                }

                dt.one('preXhr', function (e, s, data) {
                    data.start = oldStart;
                    data.length = oldLength;
                });
                setTimeout(function() {
                    dt.ajax.reload();
                }, 0);
            });

            dt.ajax.reload();
        }
    }],

    footerCallback: function (row, data, start, end, display) {
        var api = this.api();

        // Helper function to process data strings into clean numbers
        var intVal = function (i) {
            if (i === null || i === undefined) {
                return 0;
            }
            if (typeof i === 'string') {
                // Strip out commas, spaces, currency symbols, and check if it's a valid number
                var cleaned = i.replace(/[\$,\s]/g, '');
                return isNaN(cleaned) || cleaned === '' ? 0 : parseFloat(cleaned);
            }
            return typeof i === 'number' ? i : 0;
        };

        // Columns to sum: Mcs (11), Kgs (12), Slabs (13), Finish HL Weight (15), Finish HON Weight (17)
        var columnsToSum = [11, 12, 13, 15, 17];

        columnsToSum.forEach(function (index) {
            // Calculate the total for the entire dataset across pages
            var total = api
                .column(index)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            // Force all totals to display with exactly 2 decimal places
            var formattedTotal = total.toFixed(2);

            // Update the corresponding footer cell
            $(api.column(index).footer()).html(formattedTotal);
        });
    },
        ajax: {
            url: 'Ajax/ajax-stock-overall-report.php',
            type: 'POST',
            data: function (d) {
                // If search wasn't triggered yet, pass a parameter to keep PHP script idle
                if (!searchExecuted) {
                    d.initialIdle = true;
                }

                // Gather reactive form variables dynamically on submission payload
                d.fromDate = $("#txtFromDate").val();
                d.toDate = $("#txtToDate").val();
                d.coldStore = $("#drpColdStore").val();
                d.plantID = $("#drpPlant").val();
                d.variety = $("#drpVariety").val();
                d.grade = $("#drpGrade").val();
                d.group = $("#drpGroup").val();
                d.isReturn = $("#drpGroup option:selected").data("isreturnable");
                d.transtype = $("#drpGroup option:selected").data("transtype");
                d.packingStyle = $("#drpPckgStyle").val();
                d.productCategory = $("#drpPDCategory").val();
                d.soaking = $("#drpSoaking").val();
                d.packingType = $("#drpPackingType").val();
                d.species = $.trim($("#drpSpecies").val());
                d.typeProduct = $("#drpTypeProduct").val();
                d.freezingType = $("#drpFreezingType").val();
                d.abstatus = $("#drpAbstatus").val();
            }
        }
    });

    // 2. Click Handler explicitly targeting your search process loop
    $("#btnSearch").on("click", function(e) {
        e.preventDefault(); // Prevents layout engine reloads if structured within forms

        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();

        if (fromDate != '' && toDate != '') {
            console.log("Search clicked, dates verified. Forcing reload...");

            searchExecuted = true; // Lift idle gate block
            table.ajax.reload();   // Command DataTables pipeline execution

        } else {
            alert('Select From and To Date');
        }
    });

    // --- Dropdown/Value DOM Mutators ---
    $("#drpGroup option").each(function () {
        if ($(this).text().trim() === "Repacking Out +") {
            $(this).text("Repacking In");
        } else if($(this).text().trim() === "Repacking Out -") {
            $(this).text("Repacking Out");
        } else if($(this).text().trim() === "Conversion Out +") {
            $(this).remove();
        }
    });

    var productionPlant = <?php echo (int)$ProductionPlant; ?>;
    if (productionPlant != 0) {
        $("#drpColdStore").prop('disabled', true).val($("[data-plant='" + productionPlant + "']").val()).trigger('change');
        $("#drpPlant").prop('disabled', true).val(productionPlant).trigger('change');
    } else {
        $("#drpColdStore").prop('disabled', false).val("").trigger('change');
        $("#drpPlant").prop('disabled', false).val("").trigger('change');
    }

});

$("#drpPlant").on("change", function () {

    $("#tblDataTable tbody").empty();

    table.ajax.reload(null, false);

});
</script>

</body>
</html>
