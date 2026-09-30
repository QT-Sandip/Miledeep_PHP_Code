
<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

//$filterDailyWages = "T";
$filterToDateNew = "T";
$filterFromDate = "T";
$filterCenterHarvest = "T";
$buttonButtom = "T";

?>

<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . 'includes/head.php' ?>
</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

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
                <?php include_once WORKING_MODULE . 'includes/page-filter.php'; ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">
                                    <!-- -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <?php include_once ROOT . 'includes/footer.php' ?>

    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . 'includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . 'includes/app-js.php' ?>

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
                    title: 'Harvesting Boys Report - <?php echo date('Y-m-d') ?> ',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    },
                },
                {
                    extend: 'pdfHtml5',
                    text: 'Download PDF',
                    className: 'btnPdfDataTable btn-left-align',
                    title: 'Harvesting Boys Report - <?php echo date('Y-m-d') ?> ',
                    orientation: 'portrait',
                    // orientation: 'landscape',
                    exportOptions: {
                        columns: ':visible',
                        stripHtml: false
                    }
                }
            ],
        });
    }

    function ShowList() {
        $.ajax({
            url: 'Ajax/ajax-rm-booking-report.php',
            type: 'POST',
            data: {
                dailyWages: $("#txtDailyWages").val(),
                center: $("#drpCenter").val(),
                startDate: $("#txtFromDate").val(),
                endDate: $("#txtToDate").val()
            },
            success: function(data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }

    $("#btnSearch").on("click", function() 
    {

        var fromDate = $("#txtFromDate").val();

        if (fromDate) {
            ShowList();
        } else {
            // alert("Enter center");
        }
    })
</script>

</body>

</html>