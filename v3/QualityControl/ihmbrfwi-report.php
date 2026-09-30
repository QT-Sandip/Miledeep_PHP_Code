<?php

$PageTitle = "In House Microbiology Report For Water And Ice Report";
$PageTitleFolder = "Quality Control";
$Currentyear = date('Y');
$today_date = date("Y-m-d");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$reporttypearr = array(
    "Water - Daily Based" => "Water - Daily Based",
    "Water - Once in 4days" => "Water - Once In 4 Days",
    "Water And Ice Weekly Once" => "Water And Ice Weekly Once"
);
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

        td:has(> span.redbg) {
            background-color: red;
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

                                        <div class="form-group col-lg-3">
                                            <label for="report_type">Type Of Report </label>
                                            <select class="form-control select2" name="report_type" id="report_type" required>
                                                <option Value=""> Select</option>
                                                <?php foreach ($reporttypearr as $RowUsersList) {
                                                ?>
                                                    <option Value="<?php echo $RowUsersList; ?>" <?php echo ($ROWSWAB['report_type'] == $RowUsersList) ? "selected='selected'" : ''; ?>>
                                                        <?php echo $RowUsersList; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="exampleInputEmail1">From Date</label>
                                            <input required type="date" value="<?php echo $today_date ?>" class="form-control"
                                                name='SampleDate' id="fromDate"
                                                placeholder="Enter Date of Sampling">
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="exampleInputEmail1">To Date</label>
                                            <input required type="date" value="<?php echo $today_date ?>" class="form-control"
                                                name='SampleDate' id="toDate"
                                                placeholder="Enter Date of Sampling">
                                        </div>

                                        <div class="form-group col-sm-3 mt-4">
                                            <div class="d-flex gap-2">
                                                <button id="list" type="button" class="btn btn-primary mr-3">
                                                    Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                                </button>
                                                <a id="createreport" href="javascript:void(0)" class="btn btn-success" style="display:none">
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
                                                <th>Type of report </th>
                                                <th>Sample Drawn By </th>
                                                 <!-- <th>Instant Swab </th>  -->
                                                <th>Sample Collection Date </th>
                                                 <!-- <th>AnalysisDate </th>  -->
                                                <th>Plant Name </th>
                                                <th>Sample Description</th>
                                                <th>Location</th>
                                                <th>Tap Number Only For Water </th>
                                                <th>Type Of Ice </th>

                                                <!-- <th>Name Of The Media Used </th> -->
                                                <!-- <th>Equipment Used </th> -->
                                                <th>Tpc 37 °C </th>
                                                <th>Tpc 22 °C </th>
                                                <th>Completed Date </th>

                                                <!-- <th>Name Of The Media Used </th> -->
                                                <!-- <th>Equipment Used </th> -->
                                                <th>V. Cholerae </th>
                                                <th>Completed Date </th>

                                                <!-- <th class="hide">Name Of The Media Used </th> -->
                                                <!-- <th class="hide">Equipment Used </th> -->
                                                <th class="hide">Coliform </th>
                                                <th class="hide">Completed Date </th>

                                                <th>Analysed By</th>
                                                <th>Checked By</th>
                                                <th>Verified By</th>
                                                <th>Remarks </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="ATT_DATA2" style="display:none">


                                    <table id="microReport" class="table dt-responsive nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th colspan="13">SWAB FOR WORKERS HAND</th>
                                               
                                            </tr>
                                            <tr>
                                                <th>Sno</th>
                                                <th>Sample Collection date</th>
                                                 <th>Sample Drawn By</th>
                                                <th>Sample Analysis Date</th>
                                                <th>Sample Collection Area</th>
                                                <th>Name of the Food Worker Hand</th>
                                                <th>Employee Card No</th>
                                                <th>TPC at 37°C</th>
                                                <th>V.cholerae</th>
                                                 <th>Date of Completion</th>
                                                <th >Analysed By</th>
                                                <th>Checked By</th>
                                                <th>Verified By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>

                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->

                    </div>
                     <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="ATT_DATA3" style="display:none">
                                    <table id="microReport1" class="table dt-responsive nowrap w-100">
                                        <thead>
                                            <tr>
                                                <!-- <th colspan="12">SWAB FOR WORKERS HAND</th> -->
                                                <th colspan="11">SWAB FOR FOOD CONTACT SURFACES</th>
                                                
                                            </tr>
                                            <tr>
                                               <th>Sno</th>
                                                <th>Sample Collection date</th>
                                                 <th>Sample Drawn By</th>
                                                <th>Sample Analysis Date</th>
                                                <th>Sample Collection Area</th>
                                                <th>Name of the Food Contact Surface</th>
                                                <th>TPC at 37°C</th>
                                                 <th>Date of Completion</th>
                                                <th >Analysed By</th>
                                                <th>Checked By</th>
                                                <th>Verified By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
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

            $("#exportToexcel").click(function() {

                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();
                var report_type = $('#report_type').val();
                if (report_type == "" || report_type == undefined) {
                    return false;
                } else if (fromDate == "" || toDate == "" || fromDate == undefined || toDate == undefined) {
                    return false;
                }
                // window.location.href = "Aj-ihmbrfwi-report.php?FromDate=2025-07-01&ToDate=2025-08-22&report_type=workhand"
              window.location.href = "Aj-ihmbrfwi-report?FromDate=" + fromDate + "&ToDate=" + toDate + "&report_type=" + report_type;
                return false;
            });

             $("#parameterexportToexcel").click(function() {

                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();
                var report_type = $('#report_type').val();
                if (report_type == "" || report_type == undefined) {
                    return false;
                } else if (fromDate == "" || toDate == "" || fromDate == undefined || toDate == undefined) {
                    return false;
                }
                // window.location.href = "Aj-ihmbrfwi-report?FromDate=" + fromDate + "&ToDate=" + toDate + "&report_type=report_type";
                // window.location.href = "Aj-ihmbrfwi-report.php?FromDate=2025-07-01&ToDate=2025-08-22&report_type=foodcontact"
                window.location.href = "Aj-ihmbrfwi-report?FromDate=" + fromDate + "&ToDate=" + toDate + "&report_type=" + report_type;
                return false;
            });

            $("#createreport").click(function() {
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();
                if (fromDate == "" || toDate == "" || fromDate == undefined || toDate == undefined) {
                    alert("Please Select Dates");
                    return false;
                }
                window.location.href = "in-house-microbiology-report-for-product-create?FromDate=" + fromDate + "&ToDate=" + toDate;
            });

            $("#list").click(function() {
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();
                var report_type = $('#report_type').val();
                if (report_type == "" || report_type == undefined) {
                    alert("Please select type of report");
                    return false;
                } else if (fromDate == "" || toDate == "" || fromDate == undefined || toDate == undefined) {
                    alert("Please Select Dates");
                    return false;
                }
                loadAdvances();
            });

        });

        function loadAdvances() {
            $('#ATT_DATA').show();
            $('#ATT_DATA2').show();
             $('#ATT_DATA3').show();
            var report_type = $('#report_type').val();
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();

            if ($.fn.DataTable.isDataTable('#mytable-data')) {
                $('#mytable-data').DataTable().destroy();
            }

            $("#exportToexcel").show();
             $("#parameterexportToexcel").show();
            table = "";
            table = $('#mytable-data').DataTable({
                serverSide: true,
                ajax: {
                    url: 'Ajax/Ajihmbrfwilist.php',
                    type: 'POST',
                    data: function(d) {
                        d.fromDate = fromDate;
                        d.toDate = toDate;
                        d.report_type = report_type;
                    }
                },

                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Download',
                        className: 'btn btn-success mr-2',
                        exportOptions: {
                            columns: ':visible' // Export visible columns
                        },
                        action: function(e, dt, node, config) {
                            var fromDate = $('#fromDate').val();
                            var toDate = $('#toDate').val();
                            var report_type = $('#report_type').val();
                            if (report_type == "" || report_type == undefined) {
                                return false;
                            } else if (fromDate == "" || toDate == "" || fromDate == undefined || toDate == undefined) {
                                return false;
                            }
                            // window.location.href = "Aj-ihmbrfwi-report?FromDate=" + fromDate + "&ToDate=" + toDate + "&report_type=" + report_type;
                            window.location.href = "Aj-ihmbrfwi-report?FromDate=" + fromDate + "&ToDate=" + toDate + "&report_type=" + report_type;
                            return false;
                        }
                    },

                    {
                        extend: 'excelHtml5',
                        text: 'Media Equipment wise',
                        className: 'btn btn-success mr-2',
                        exportOptions: {
                            columns: ':visible' 
                        },
                        action: function (e, dt, node, config) {
                            var fromDate = $('#fromDate').val();
                            var toDate = $('#toDate').val();
                            var report_type = $('#report_type').val();
                            //  var report_type = 'Parameter Wise';
                            if( report_type == "" || report_type == undefined ){
                                return false;      
                            }else if( fromDate == "" || toDate == "" || fromDate == undefined || toDate == undefined ){                                         
                                return false;      
                            }
                            window.location.href="Ajp-ihmbrfwi-report?FromDate="+fromDate+"&ToDate="+toDate+"&report_type="+report_type;
                            return false;
                        }
                    }
                
                ],
               columns: [{
                        data: 'Sno'
                    },
                    {
                        data: 'report_type'
                    },
                    {
                        data: 'sample_drawn_by'
                    }, 
                    {
                        data: 'sample_collection'
                    },
                    

                    {
                        data: 'plant_id'
                    },
                    {
                        data: 'sample_desc'
                    },
                    {
                        data: 'location'
                    },
                    {
                        data: 'tap_no_for_water'
                    },
                    {
                        data: 'itype_of_ice'
                    },

                    {
                        data: 'tpc_result1'
                    },
                    {
                        data: 'tpc_result2'
                    },
                    {
                        data: 'tpc_completion_date'
                    },

                    {
                        data: 'vcholerae_result1'
                    },
                    {
                        data: 'vcholerae_completion_date'
                    },

                   
                    {
                        data: 'cholerae_result1'
                    },
                    {
                        data: 'cholerae_completion_date'
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
                    [10, 50, 100],
                    [10, 50, 100]
                ],
                scrollX: true,
                scrollY: true,
                // scrollY: true,
                scrollCollapse: false,
                autoWidth: false,
                responsive: false,
                searching: true,
                ordering: false,
                 initComplete: function(settings, json) {
        // Hide Coliform cols if report_type is "Water - Daily Based"
        if (report_type === "Water - Daily Based") {
            table.column(14).visible(false); // Coliform
            table.column(15).visible(false); // Completed Date
        } else {
            table.column(14).visible(true);
            table.column(15).visible(true);
        }
    }
            

            });


            // if ($.fn.DataTable.isDataTable('#mytable-data')) {
            //     $('#microReport').DataTable().destroy();
            // }
            if ($.fn.DataTable.isDataTable('#microReport')) {
                    $('#microReport').DataTable().destroy();
                }


            $('#microReport').DataTable({

                serverSide: true,
                ajax: {
                    url: 'Ajax/Ajax-load_swab_report.php',
                    type: 'POST',
                    data: function(d) {
                        d.fromDate = fromDate;
                        d.toDate = toDate;
                        // d.report_type = report_type;
                         d.report_type = 'workhand'; 
                    }
                },
                dom: 'Bfrtip',
                buttons: [
                   
                ],
                columns: [
                     { data: 'sno' 

                     },
                    {
                        data: 'sample_collection_date'
                    },
                    {
                        data: 'sample_drawn_by'
                    },  
                    {
                        data: 'analysis_date'
                    },
                    {
                        data: 'sample_collection_area'
                    },
                    {
                        data: 'worker_name'
                    },
                    {
                        data: 'emp_id'
                    },
                    {
                        data: 'tpc_result_hand'
                    },
                    {
                        data: 'vcholerae_hand'
                    },
                    
                    {
                        data: 'completion_date'
                    },
                    {
                        data: 'analysed_by'
                    },
                    {
                        data: 'checked_by'
                    },
                    {
                        data: 'verified_by'
                    }
                ]
            });

            //  if ($.fn.DataTable.isDataTable('#mytable-data')) {
            //     $('#microReport1').DataTable().destroy();
            // }
            if ($.fn.DataTable.isDataTable('#microReport1')) {
                $('#microReport1').DataTable().destroy();
            }


            $('#microReport1').DataTable({

                serverSide: true,
                ajax: {
                    url: 'Ajax/Ajax-load_swab_report.php',
                    type: 'POST',
                    data: function(d) {
                        d.fromDate = fromDate;
                        d.toDate = toDate;
                        // d.report_type = report_type;
                        d.report_type = 'foodcontact';
                        
                    }
                },
                dom: 'Bfrtip',
                buttons: [
                   
                ],
            columns: [
        { data: 'sno' },

        { data: 'sample_collection_date' },

        { data: 'sample_drawn_by' },

        { data: 'analysis_date' },

         { data: 'sample_collection_area' },

        { data: 'surface_name' },  

        { data: 'tpc_result_surface' }, 
   
        { data: 'completion_date' },

        { data: 'analysed_by' },

        { data: 'checked_by' },

        { data: 'verified_by' }
        ]
            });

        }
    </Script>

</body>

</html>