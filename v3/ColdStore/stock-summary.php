<?php
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$excludeList = array("Blocks","Floors","StoreRacks","Stock","StockBelongsTo","ProductGroup","Exporters","Consignee","Processor","Shifts","PackingType","FreezingType","Soaking","Abstatus","OptType","OutType","Grade","Variety","VarietyList","StockAvailableVariety","StockInVarietyList","PoNumber","PackingStyle","GeneralCategories","Status","TenantType","ProductionLines","Brand","BrandType","Species","Tranction","TypeProduct","Type","CsTransactionTypes","ProductForm","Preparation","GlazeType","ShipoutPoNumber","ProductionPlant","PoNumberNew","TransactionTypesNew","InwardGroups");
$filterPlant = "T";
$filterColdStores = "T";
$filterfromDate = "T";
$filterClosingDate = "T";
$StockSummaryButton = "T";

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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php'; ?>

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
    <!-- Modal -->

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
        var plantName = $("#drpPlant option:selected").text();
        var storeName = $("#drpColdStore option:selected").text();
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bSort: false,
            bInfo: false,
            dom: 'Blfrtip',
            // dom: '<"top-left"Blfrtip>',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Stock Summary Report_ ' + plantName + ' - ' + storeName,
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],

        });
    }


    $(document).ready(function() {

        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpColdStore").prop('disabled', true);
            $("#drpPlant").prop('disabled', true);
            $("#drpColdStore").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');
            $("#drpPlant").val(productionPlant).trigger('change');

        } else {
            $("#drpColdStore").prop('disabled', false);
            $("#drpPlant").prop('disabled', false);
            $("#drpColdStore").val("").trigger('change');
            $("#drpPlant").val("").trigger('change');
        }
    });
    $("#btnSearch").on("click", function() {
        var fromDate = $("#txtFromDate").val();
        var closingDate = $("#txtClosingDate").val();

        if (fromDate == '' && closingDate == '') {
            alert("Select From and to Date");
        } else {
            ShowList();
        }
    });

    function ShowList() {
        $.ajax({
            url: 'Ajax/ajax-stock-summary.php',
            type: 'POST',
            data: {
                plant: $("#drpPlant").val(),
                coldStore: $("#drpColdStore").val(),
                fromDate: $("#txtFromDate").val(),
                drpPDCategory: $("#drpPDCategory").val(),
                closingDate: $("#txtClosingDate").val(),
                plantName: $("#drpPlant option:selected").text(),
                storeName: $("#drpColdStore option:selected").text(),
            },
            success: function(data) {
                // fnSuccess(data);
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }
$("#drpPlant").on("change", function () {
    $("#divDataList").html("");
});
</script>

</body>

</html>
