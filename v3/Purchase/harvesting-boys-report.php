<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterDailyWages = "T";
$filterToDateNew = "T";
$filterFromDate = "T";
$filterCenterHarvest = "T";
$buttonButtom = "T";
$filterBoysGroup = "T";

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
                    customize: function (xlsx) {

                        var sheet = xlsx.xl.worksheets['sheet1.xml'];

                        // create mergeCells
                        if ($('mergeCells', sheet).length === 0) {
                            $('worksheet', sheet).append('<mergeCells count="0"></mergeCells>');
                        }

                        var mergeCells = $('mergeCells', sheet);

                        var data = table.rows().data().toArray();

                        var startExcelRow = 2; // header after excel row
                        var start = startExcelRow;

                        for (var i = 1; i <= data.length; i++) {

                            var current = data[i - 1] ? data[i - 1][4] : '';
                            var previous = data[i - 2] ? data[i - 2][4] : '';

                            if (i == 1) {
                                continue;
                            }

                            if (current != previous || i == data.length) {

                                var end = startExcelRow + i - 2;

                                // last row handle
                                if (i == data.length && current == previous) {
                                    end++;
                                }

                                if (end > start) {
                                    end = parseInt(end) + 1;
                                    mergeCells.append(
                                        '<mergeCell ref="E' + start + ':E' + end + '"/><mergeCell ref="J' + start + ':J' + end + '"/>'
                                    );
                                }

                                start = startExcelRow + i - 1;
                            }
                        }

                        mergeCells.attr('count', mergeCells.find('mergeCell').length);
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: 'Download PDF',
                    className: 'btnPdfDataTable',
                    title: 'Harvesting Boys Report - <?php echo date('Y-m-d') ?>',
                    
                    // orientation: 'landscape', // important
                    // pageSize: 'A4',

                    exportOptions: {
                        columns: ':visible',
                        stripHtml: false
                    },

                     customize: function (doc) {

                        doc.defaultStyle.fontSize = 8;
                        doc.styles.tableHeader.fontSize = 9;

                        doc.pageMargins = [5, 5, 5, 5];

                        // Get column count
                        var colCount = doc.content[1].table.body[0].length;

                        // Set equal widths
                        doc.content[1].table.widths = new Array(colCount).fill('auto');

                        // Table alignment
                        doc.styles.tableHeader.alignment = 'center';
                        doc.defaultStyle.alignment = 'center';
                    }
                }
            ],
        });
    }

    function ShowList() {
        if($("#drpBoysGroup").val() == ''){
            alert('Please select the Boys Group first.');
            return;
        }
        $.ajax({
            url: 'Ajax/ajax-harvesting-boys-report.php',
            type: 'POST',
            data: {
                dailyWages: $("#txtDailyWages").val(),
                center: $("#drpCenter").val(),
                startDate: $("#txtFromDate").val(),
                endDate: $("#txtToDate").val(),
                drpBoysGroup: $("#drpBoysGroup").val()
            },
            success: function(data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }

    $("#btnSearch").on("click", function() {

        var dailyWages = $("#txtDailyWages").val();

        if (dailyWages) {
            ShowList();
        } else {
            alert("Enter Daily Wages ");
        }
    })
</script>

</body>

</html>