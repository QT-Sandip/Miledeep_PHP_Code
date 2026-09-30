<?php

$PageTitle = "IN HOUSE MICROBIOLOGY REPORT FOR ICE CHUTES AND WATER TANKS";
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
        .dataTables_scrollBody {
            overflow-y: auto !important;
        }

        .dataTables_wrapper {
            overflow-x: auto;
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
                                                <th>Sample Drawn By </th>
                                                 <th>Instant Swab </th>
                                                <th>Sample Collection Date </th>
                                                <th>Plant Name </th>
                                                <th>Location </th>
                                                <th>Sample Description </th>
                                                <th>Analysis Date </th>
                                                <th>Date Of Completion </th>

                                                <!-- <th>Name Of The Media Used </th>
                                                <th>Equipment Used </th> -->
                                                <th>Tpc 37 °C </th>
                                                <!-- <th>Tpc 22 °C </th> -->
                                                <th>Completed Date </th>

                                                <!-- <th>Name Of The Media Used </th>
                                                <th>Equipment Used </th> -->
                                                <th>V. Cholerae </th>
                                                <th>Completed Date </th>

                                                <th>Analysed By </th>
                                                <th>Checked By </th>
                                                <th>Verified By </th>
                                                <th>Remark </th>
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

    <Script>
        $('document').ready(function() {
            $("#createreport").click(function() {
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();
                if (fromDate == "" || toDate == "" || fromDate == undefined || toDate == undefined) {
                    alert("Please Select Dates");
                    return false;
                }
                window.location.href = "ihmbrficawt-create.php?FromDate=" + fromDate + "&ToDate=" + toDate;
            });

            $("#list").click(function() {
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();
                if (fromDate == "" || toDate == "" || fromDate == undefined || toDate == undefined) {
                    alert("Please Select Dates");
                    return false;
                }
                loadAdvances();
            });

        });

        function loadAdvances() {
            $('#ATT_DATA').show();
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();

            if ($.fn.DataTable.isDataTable('#mytable-data')) {
                $('#mytable-data').DataTable().destroy();
            }

            table = "";
            table = $('#mytable-data').DataTable({
                serverSide: true,
                ajax: {
                    url: 'Ajax/Ajihmbrfcawflist',
                    type: 'POST',
                    data: function(d) {
                        d.fromDate = fromDate;
                        d.toDate = toDate;
                        d.fromPage = "";
                    }
                },
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'excelHtml5',
                    text: 'Excel Download',
                    className: 'btn btn-success mr-2',
                    exportOptions: {
                        columns: ':visible' // Export visible columns
                    },
                    action: function(e, dt, node, config) {
                        var fromDate = $('#fromDate').val();
                        var toDate = $('#toDate').val();
                        if (fromDate == "" || toDate == "" || fromDate == undefined || toDate == undefined) {
                            return false;
                        }
                        window.location.href = "Aj-ihmbrfcawt-report?FromDate=" + fromDate + "&ToDate=" + toDate;
                        return false;
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Media Equipment wise',
                    className: 'btn btn-success mr-2',
                    exportOptions: {
                        columns: ':visible' // Export visible columns
                    },
                    action: function(e, dt, node, config) {
                        var fromDate = $('#fromDate').val();
                        var toDate = $('#toDate').val();
                        if (fromDate == "" || toDate == "" || fromDate == undefined || toDate == undefined) {
                            return false;
                        }
                        window.location.href = "Ajp-ihmbrfcawt-report?FromDate=" + fromDate + "&ToDate=" + toDate;
                        return false;
                    }
                }],
                columns: [{
                        data: 'Sno'
                    },
                    {
                        data: 'sample_drawn_by'
                    },
                    { data: 'instant_swab'

                     }, 
                    {
                        data: 'sample_collection_date'
                    },
                    {
                        data: 'plant_name'
                    },
                    {
                        data: 'sample_collection_area'
                    },
                    {
                        data: 'sample_desc'
                    },
                    {
                        data: 'AnalysisDate'
                    },
                    {
                        data: 'Dateofcompletion'
                    },

                    // {
                    //     data: 'tpc_media'
                    // },
                    // {
                    //     data: 'tpc_equipement'
                    // },
                    {
                        data: 'tpc_result'
                    },
                    // {
                    //     data: 'tpc_result2'
                    // },
                    {
                        data: 'tpc_completion_date'
                    },

                    // {
                    //     data: 'vcholerae_media'
                    // },
                    // {
                    //     data: 'vcholerae_equipement'
                    // },
                    {
                        data: 'vcholerae_result'
                    },
                    {
                        data: 'vcholerae_completion_date'
                    },

                    {
                        data: 'analysed_by'
                    },
                    {
                        data: 'checked_by'
                    },
                    {
                        data: 'verified_by'
                    },
                    {
                        data: 'remarks'
                    }
                ],
                columnDefs: [
                    /*{ targets: [2], orderable: false, searchable: false },
                    { targets: [3], orderable: false, searchable: false },
                    { targets: [4], orderable: false, searchable: false },                */

                ],
                paging: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 50, 100],
                    [10, 50, 100]
                ],
                scrollX: true,
                scrollY: '500px',
                scrollCollapse: true,
                autoWidth: false,
                responsive: false,
                searching: true,
                ordering: false,

            });
        }
    </Script>

</body>

</html>