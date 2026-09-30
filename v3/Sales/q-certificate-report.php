<?php


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterfromDate = "T";
$filtertoDate = "T";
$filterCompany = "T";
$filterProcessor = "T";

// $filterButtomButton = "T";
$filterButButton = "T";

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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <!-- <div class="card">
                                    <div class="card-body"> -->

                        <div id="divDataList">
                            <!-- -->
                        </div>

                        <!-- </div>
                                </div> -->


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
<script src="../assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

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
            buttons: [{extend: 'excel', text: 'Download Excel', className: 'btnExcelDataTable',footer: true,title:"Q-CERTIFICATE-REPORT"}],
            drawCallback: function() {
                $(document).trigger('afterSearch');
            }
        });
    }
    $("#btnSearch").on("click", function () {
        $.ajax({
            url: 'Ajax/AjaxQCertificateReport.php',
            type: 'POST',
            data: {
                Exporter: $("#drpExporterList").val(),
                Processor: $("#drpProcessorList").val(),
                FromDate: $("#fromDate").val(),
                ToDate: $("#toDate").val(),
            },
            success: function (data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    })
    $(document).on('afterSearch', function() {
        var countTotInv = $(".bodyInvNo").length;
        var sumTotNetWeightLbs = columnSum(".bodyNetWeightLbs", 3);
        var sumTotNetWeightKgs = columnSum(".bodyNetWeightKgs",3);
        var sumTotFOBValueUSD = columnSum(".bodyFOBValueUSD", 2);
        var sumTotFobValueINR = columnSum(".bodyFobValueINR", 2);

        $(".footInvNo").text(countTotInv);
        $(".footTotNetWeightLbs").text(sumTotNetWeightLbs);
        $(".footTotNetWeightKgs").text(sumTotNetWeightKgs);
        $(".footTotFOBValueUSD").text(sumTotFOBValueUSD);
        $(".footTotFobValueINR").text(sumTotFobValueINR);
    });
$("#drpProcessorList").on("change", function () {
    $("#divDataList").html("");
});
</script>

</body>

</html>
