<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filtermonthwisefromDate = "T";
$filtertoDate = "T";
$filterExporter = "T";

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

                <?php // include_once ROOT . 'Sales/includes/page-filter.php' 
                ?>
                <?php include_once WORKING_MODULE . '/includes/page-filter.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <!-- <div class="card">
                            <div class="card-body"> -->

                        <div>
                            <div class="card">
                                <div class="card-body" id="divDataList">
                                </div>
                            </div>
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
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<script>
    $(document).on('click', '.read-more', function(e) {
        e.preventDefault();
        var $td = $(this).closest('td');
        $td.find('.short-text, .read-more').hide();
        $td.find('.full-text').removeClass('d-none');
        $td.append('<a href="#" class="read-less">Read less</a>');
    });

    $(document).on('click', '.read-less', function(e) {
        e.preventDefault();
        var $td = $(this).closest('td');
        $td.find('.full-text').addClass('d-none');
        $td.find('.short-text, .read-more').show();
        $(this).remove();
    });


    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            // sScrollXInner: "100%",
            scrollCollapse: true,
            paging: false,
            destroy: true,
            autoWidth: false,
            // fixedColumns: true,
            dom: 'Blfrtip',
            columnDefs: [{
                targets: 15,
                width: '200px'
            }],
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: "Shipment Sales Line Itemwise Report",
                footer: true,
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: function(data, row, column, node) {

                            if (column == 4 || column == 14 || column == 15) {
                                var fullText = $(node).find('.full-text').text();
                                if (fullText) return fullText;
                                // Fallback: strip HTML tags from anything else
                                return $(node).text().trim();
                            }

                            const dateColumns = [3, 8];
                            const TermsColumns = [14];

                            if (TermsColumns.includes(column)) {
                                return data.replace(/&amp;/g, '&');
                            } else if (dateColumns.includes(column)) {
                                if (data == "--") {
                                    return "--";
                                } else {
                                    return moment(data, "DD-MM-YYYY").format("YYYY-MM-DD");
                                }
                            } else {
                                return data;
                            }
                        }
                    }
                }
            }, ],
        });
    }
    $("#btnSearch").on("click", function() {

        var exporter = $("#drpExporterList").val();
        var txtStartDate = $("#fromDate").val();
        var txtEndDate = $("#toDate").val();

        var startDate = new Date(txtStartDate);
        var endDate = new Date(txtEndDate);

        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

        var days = daysDiff + 1;

        console.log(days);

        if (days > 31) {
            var url = 'Ajax/AjaxShipmentSalesLineItemwiseReport.php?Exporter=' + exporter + '&FromDate=' + txtStartDate + '&ToDate=' + txtEndDate + '&excelDownload=T';
            window.open(url, '_blank');
        } else {
            $.ajax({
                url: 'Ajax/AjaxShipmentSalesLineItemwiseReport.php',
                type: 'POST',
                data: {
                    Exporter: $("#drpExporterList").val(),
                    FromDate: $("#fromDate").val(),
                    ToDate: $("#toDate").val(),
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    return false;
                }
            });
        }

    });
$("#fromDate").on("change", function () {
    $("#divDataList").html("");
});
</script>

</body>

</html>
