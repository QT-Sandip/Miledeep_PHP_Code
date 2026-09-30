<?php

$PageTitle = "In House Microbiology Report For Product List";
$PageTitleFolder = "Quality Control";
$Currentyear = date('Y');
$today_date = date("Y-m-d");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
?>

<!doctype html>
<html lang="en">

<head>

    <!-- DataTables -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />


    <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css"
        rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css"
        rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css"
        rel="stylesheet" />
    <?php include($IncludesPath . "head-css.php"); ?>
    <style>
        .dataTables_wrapper .dataTables_paginate {
    float: right !important;
    text-align: right;
}
    </style>
</head>

<body data-sidebar="dark">
    <?php include($IncludesPath . "PreLoader.php"); ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <?php include($IncludesPath . "page-title.php"); ?>
                    <?php include "PopUpTable.php"; ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">

                                        <div class="form-group col-sm-3">
                                            <label for="exampleInputEmail1">From Date</label>
                                            <input required type="date" value="<?php echo $today_date ?>" class="form-control"
                                                name='SampleDate' id="fromDate"
                                                placeholder="Enter Date of Sampling">
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="exampleInputEmail1">To Date </label>
                                            <input required type="date" value="<?php echo $today_date ?>" class="form-control"
                                                name='SampleDate' id="toDate"
                                                placeholder="Enter Date of Sampling">
                                        </div>

                                        <div class="form-group col-sm-3 mt-4">
                                            <div class="d-flex gap-2">
                                                <button id="list" type="button" class="btn btn-primary mr-3">
                                                    Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                                </button>
                                                <a id="createreport" href="javascript:void(0)" target="_blank" class="btn btn-success">
                                                    Create Report
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="ATT_DATA" style="display:none">
                                    <table id="mytable-data" class="table dt-responsive nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Sno </th>
                                                <th>Batch Number </th>
                                                <th>Sample Collection Date </th>
                                                <th>Sample Drawn By </th>
                                                <th>Analysis Date </th>
                                                <th>Report Date </th>
                                                <th>Variety </th>
                                                <th>Action </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div>
                </div>
            </div>
            <?php include($IncludesPath . "footer.php"); ?>
            <?php include($IncludesPath . "leave_register.php"); ?>
        </div>
    </div>
    <?php include($IncludesPath . "vendor-scripts.php"); ?>


    <!-- Required datatable js -->
    <script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>

    <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/js/pages/form-advanced.init.js"></script>


    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <!-- Buttons examples -->
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/jszip/jszip.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/pdfmake.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/vfs_fonts.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-select/js/dataTables.select.min.js"></script>

    <!-- Responsive examples -->
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script
        src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

    <!-- Datatable init js -->
    <script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>

    <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>
    <!-- <script src="https://debug.datatables.net/debug.js"></script> -->

     <script>
    $(document).ready(function () {
        $("#createreport").click(function () {
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            if (!fromDate || !toDate) {
                alert("Please Select Dates");
                return false;
            }
            //window.location.href = "in-house-microbiology-report-for-product-create?FromDate=" + fromDate + "&ToDate=" + toDate;
            var url = "in-house-microbiology-report-for-product-create?FromDate=" + fromDate + "&ToDate=" + toDate;
            $(this).attr('href', url);
        });

        $("#list").click(function () {
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            if (!fromDate || !toDate) {
                alert("Please Select Dates");
                return false;
            }
            loadAdvances(false); // normal paginated load
        });

        // Handle click on Action button after search
        $('#mytable-data').on('click', '.btnEdit', function () {
            var id = $(this).data('id');
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();

            if (!fromDate || !toDate) {
                alert("Please select From and To dates.");
                return;
            }

            window.location.href = `in-house-microbiology-report-for-product-create?FromDate=${fromDate}&ToDate=${toDate}&id=${id}`;
        });

        // Add More Search and Load All buttons next to DataTable search box after first draw
        $(document).on('draw.dt', function () {
    if (!$('#btnMoreSearch').length) {
        const btns = `
            <div id="customBtns" style="margin-left:10px; display:flex; gap:8px;">
                <button class="btn btn-primary" id="btnMoreSearch">More Search</button>
                <button class="btn btn-success" id="btnLoadAll">Load All</button>
            </div>
        `;

        $("#mytable-data_filter").append(btns);
    }
});

        // Click handlers for new buttons
        $(document).on("click", "#btnMoreSearch", function () {
            loadAdvances(false); // normal pagination
        });

        $(document).on("click", "#btnLoadAll", function () {
            $('#fromDate').val('');
            $('#toDate').val('');
            $('#drpLotId').val(null).trigger("change");
            loadAdvances(true); // Load all rows, disable pagination
        });
    });

    function loadAdvances(loadAll = false) {
        $('#ATT_DATA').show();
        var fromDate = $('#fromDate').val();
        var toDate = $('#toDate').val();

        if ($.fn.DataTable.isDataTable('#mytable-data')) {
            $('#mytable-data').DataTable().destroy();
        }

        $('#mytable-data').DataTable({
            serverSide: true,
            ajax: {
                url: 'Ajax/AjSwabProdList',
                type: 'POST',
                data: function (d) {
                    d.fromDate = fromDate;
                    d.toDate = toDate;
                    d.fromPage = "Listpage";
                    d.loadAll = loadAll; // send info to backend if needed
                }
            },
            columns: [
                { data: 'Sno' },
                { data: 'BatchNumber' },
                { data: 'SampleDate' },
                { data: 'DrawnBy' },
                { data: 'AnalysisDate' },
                { data: 'ReportDate' },
                { data: 'Variety' },
                { data: 'Action' }
            ],
            paging: !loadAll,               // disable pagination when loadAll = true
            pageLength: loadAll ? -1 : 100, // -1 shows all rows
            lengthMenu: loadAll ? [[-1], ["All"]] : [[10, 50, 100], [10, 50, 100]],
            scrollX: true,
            scrollY: loadAll ? '' : '500px',
            scrollCollapse: true,
            autoWidth: false,
            responsive: false,
            searching: true,
            ordering: false,
            language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',
            buttons:[]

        });
    }
</script>

</body>

</html>
