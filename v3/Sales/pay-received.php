<?php 

include_once $_SERVER['DOCUMENT_ROOT'].'/config/global.php';

//$filterfromDate = "T";
//$filtertoDate = "T";
$filterExporter = "T";
$filterBillType = "T";
$filterYear = "T";
$filterSideButton = "T";
$filterCountry = "T";
$PageTitle="Realization Done";
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

                <?php // include_once ROOT . 'Sales/includes/page-filter.php' ?>
                <?php include_once ROOT . 'v3/Sales/includes/page-filter.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <!-- <div class="card">
                                    <div class="card-body"> -->
                                    <div class="card">
    <div class="card-body">
                        <div id="divDataList">
                            <!-- -->
                        </div>

                        <!-- </div>
                                </div> -->

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
        dom: 'Blfrtip',
        buttons: [{
            extend: 'excel',
            text: 'Download Excel',
            className: 'btnExcelDataTable',
            title: 'Realization Done Report',
            footer: true,
            exportOptions: {
                columns: ':visible'
            }
        }],
    });
}
$("#btnSearch").on("click", function() {
    $.ajax({
        url: 'Ajax/AjaxPayReceived.php',
        type: 'POST',
        data: {

            Year: $("#drpYear").val(),
            ExporterID: $("#drpExporterList").val(),
            Type: $("#drpBillType").val(),
            Country: $("#drpCountryList").val()
        },
        success: function(data) {
            $("#divDataList").html(data);
            fnShowDataTable();
            return false;
        }
    });
})
</script>

</body>

</html>
