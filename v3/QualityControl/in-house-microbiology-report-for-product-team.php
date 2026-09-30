<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$PageTitle = "In House Microbiology Report For Product";
$PageTitleFolder = "Quality Control";
$Currentyear = date('Y');
$today_date = date("Y-m-d");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
$CurrentYear = date('Y');
$CurrentMonth = date('m');
if ($CurrentMonth > 3) {
    $PastFY = $CurrentYear - 1;
    $CurrentFY = $CurrentYear;
} else {
    $PastFY = $CurrentYear - 2;
    $CurrentFY = $CurrentYear - 1;
}
// echo $PastFY . " " . $CurrentFY;exit;
$NextFY = $CurrentFY + 1;
// Fetch batch codes
// $LotQuery = "SELECT RmLotID, LotNumber FROM RmLotDetatils ORDER BY LotNumber DESC";
// $LotResult = $iconn->query($LotQuery);
// $LotOptions = [];
// while ($row = $LotResult->fetch_assoc()) {
//     $LotOptions[] = $row;
// }
$fromDate = $_POST['fromDate']; // e.g., "2025-07-01"
$toDate   = $_POST['toDate'];   // e.g., "2025-07-31"
$year     = $_POST['Year'];     // e.g., 2025
$LotList = [];

$LotQuery = "
    SELECT DISTINCT r.RmLotID, r.LotNumber
    FROM swabproducts s
    JOIN RmLotDetatils r 
      ON FIND_IN_SET(r.RmLotID, s.BatchNumber) > 0
    WHERE r.LotNumber != ''
      AND s.AnalysisDate BETWEEN '2025-04-01' AND '2026-03-31'
    ORDER BY s.AnalysisDate DESC
";

$result = $iconn->query($LotQuery);
while ($row = $result->fetch_assoc()) {
    $LotList[] = $row;
}
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

        .select2-results__options {
            max-height: 200px !important;
            overflow-y: auto !important;
        }
        /* Selected item in Select2 dropdown - green background and white text */
        .select2-selection__choice {
            background-color: #28a745 !important;
            /* Bootstrap green */
            color: #ffffff !important;
            border: none !important;
        }
        /* Ensure the 'x' icon remains visible and white */
        .select2-selection_choice_remove {
            color: #ffffff !important;
            margin-right: 4px;
        }
        .select2-selection_choice_remove:hover {
            color: #000000 !important;
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
                                            <label>Select Year</label>
                                            <select class="drpSelect form-control" id="drpYear" name="drpYear">
                                                <option value="" selected>Select Year</option>
                                                <?php
                                                for ($startFY = $PastFY - 1; $startFY <= $CurrentFY; $startFY++) {
                                                    // Skip 2023-2024 and 2024-2025
                                                    if ($startFY == 2023 || $startFY == 2024) {
                                                        continue;
                                                    }
                                                    $endFY = $startFY + 1;
                                                    echo "<option value=\"$startFY\">{$startFY}-{$endFY}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-3">
                                            <label for="exampleInputEmail1">From Date</label>
                                            <input required type="date" value="<?php echo $fromDate ?>" class="form-control"
                                                name='SampleDate' id="fromDate"
                                                placeholder="Enter Date of Sampling">
                                        </div>
                                        <div class="form-group col-sm-3">
                                            <label for="exampleInputEmail1">To Date</label>
                                            <input required type="date" value="<?php echo $toDate ?>" class="form-control"
                                                name='SampleDate' id="toDate"
                                                placeholder="Enter Date of Sampling">
                                        </div>
                                        <!-- <div class="form-group col-sm-3">
                                            <label for="LotId">Lot No / Batch No</label>
                                            <select class="form-control" id="drpLotId" name="LotId">
                                                <option value="">Select Batch Code</option>
                                                <?php foreach ($LotOptions as $lot) { ?>
                                                    <option value="<?php echo $lot['RmLotID']; ?>">
                                                        <?php echo $lot['LotNumber']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div> -->
                                       <div class="form-group col-sm-3">
                                            <label for="LotId">Lot No / Batch No</label>
                                            <select class="form-control" id="drpLotId" name="LotId[]" multiple>
                                                <option value="">Select Batch Code</option>
                                                <?php foreach ($LotList as $lot) { ?>
                                                    <option value="<?php echo $lot['RmLotID']; ?>">
                                                        <?php echo $lot['LotNumber']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-3 mt-4">
                                            <div class="d-flex gap-2">
                                                <button id="list" type="button" class="btn btn-primary mr-3 mt-1">
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
                            <button class="btn btn-success mr-3" id="pexportToexcel" style="display:none">Parameter Wise Download</button>
                            <button class="btn btn-success mr-3" id="mexportToexcel" style="display:none">Media Wise Download</button>
                        </div>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="ATT_DATA" style="display:none">
                                    <table id="mytable-data" class="table dt-responsive nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Sno </th>
                                                <th>Batch Number </th>
                                                <th>Plant Name </th>
                                                <th>Sample Collection Date </th>
                                                <th>Sample Collection</th>
                                                <th>Sample Drawn By </th>
                                                <th>Analysis Date </th>
                                                <th>Report Date </th>
                                                <th>Variety </th>
                                                <!-- <th>Name Of The Media Used </th> -->
                                                <!-- <th>Equipment Used </th> -->
                                                <th>Type of Method</th>
                                                <th>Tpc 35 °C </th>
                                                <th>Completed Date </th>
                                                <!-- <th>Name Of The Media Used </th> -->
                                                <!-- <th>Equipment Used </th> -->
                                                <th>E.Coli Result </th>
                                                <th>Completed Date </th>
                                                <!-- <th>Name Of The Media Used </th> -->
                                                <!-- <th>Equipment Used </th> -->
                                                <th>S.Aureus Result </th>
                                                <th>Completed Date </th>
                                                <!-- <th>Name Of The Media Used </th> -->
                                                <!-- <th>Equipment Used </th> -->
                                                <th>V.Cholerae Result </th>
                                                <th>Completed Date </th>
                                                <!-- <th>Name Of The Media Used </th> -->
                                                <!-- <th>Equipment Used </th> -->
                                                <th>V.Parahaemolyticus Result </th>
                                                <th>Completed Date </th>
                                                <!-- <th>Name Of The Media Used </th>
                                                <th>Equipment Used </th> -->
                                                <th>Salmonella </th>
                                                <th>Completed Date </th>
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
                </div>
            </div>
            <?php include($IncludesPath . "footer.php"); ?>
            <?php //include($IncludesPath . "leave_register.php"); 
            ?>
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
        $(document).ready(function() {
            let batchCodes = <?php echo json_encode($LotList); ?>;
            let html = '';
            batchCodes.forEach(function(lot) {
                html += `<option value="${lot.RmLotID}">${lot.LotNumber}</option>`;
            });
            $('#drpLotId').html(html);
            $('#drpLotId').select2({
                width: '100%',
                placeholder: "Select Batch Code",
                allowClear: true
            });
            // $("#pexportToexcel").click(function() {
            //     var fromDate = $('#fromDate').val();
            //     var toDate = $('#toDate').val();
            //     if (fromDate === "" || toDate === "") return false;
            //     window.location.href = "Ajmicrobiology-report-for-product-team?Type=parameter&FromDate=" + fromDate + "&ToDate=" + toDate;
            //     return false;
            // });
            // $("#mexportToexcel").click(function() {
            //     var fromDate = $('#fromDate').val();
            //     var toDate = $('#toDate').val();
            //     if (fromDate === "" || toDate === "") return false;
            //     window.location.href = "Ajmicrobiology-report-for-product-team?Type=media&FromDate=" + fromDate + "&ToDate=" + toDate;
            //     return false;
            // });


           


        //     $("#pexportToexcel").click(function() {
        //     var fromDate = $('#fromDate').val() || '';
        //     var toDate   = $('#toDate').val() || '';
        //     var lotIds   = $('#drpLotId').val() || '';
        //     var loadAll  = $('#btnLoadAll').data('loadall') ? 1 : 0; 

        //     window.location.href = "Ajmicrobiology-report-for-product-team?Type=parameter"
        //         + "&FromDate=" + fromDate
        //         + "&ToDate=" + toDate
        //         + "&lotIds=" + lotIds 
        //         + "&loadAll=" + loadAll; 
        //     return false;
        // });

        // $("#mexportToexcel").click(function() {
        //     var fromDate = $('#fromDate').val() || '';
        //     var toDate   = $('#toDate').val() || '';
        //     var lotIds   = $('#drpLotId').val() || '';
        //     var loadAll  = $('#btnLoadAll').data('loadall') ? 1 : 0;

        //     window.location.href = "Ajmicrobiology-report-for-product-team?Type=media"
        //         + "&FromDate=" + fromDate
        //         + "&ToDate=" + toDate
        //         + "&lotIds=" + lotIds 
        //         + "&loadAll=" + loadAll;
        //     return false;
        // });


        $("#pexportToexcel").click(function() {
    var fromDate = $('#fromDate').val() || '';
    var toDate   = $('#toDate').val() || '';
    var lotIds   = $('#drpLotId').val();

    if (Array.isArray(lotIds)) {
        lotIds = lotIds.join(",");
    } else {
        lotIds = lotIds || '';
    }

    var loadAll  = $('#btnLoadAll').data('loadall') ? 1 : 0; 

    window.location.href = "Ajmicrobiology-report-for-product-team?Type=parameter"
        + "&FromDate=" + fromDate
        + "&ToDate=" + toDate
        + "&LotIds=" + lotIds   // 🔥 fixed
        + "&loadAll=" + loadAll;
    return false;
});

$("#mexportToexcel").click(function() {
    var fromDate = $('#fromDate').val() || '';
    var toDate   = $('#toDate').val() || '';
    var lotIds   = $('#drpLotId').val();

    if (Array.isArray(lotIds)) {
        lotIds = lotIds.join(",");
    } else {
        lotIds = lotIds || '';
    }

    var loadAll  = $('#btnLoadAll').data('loadall') ? 1 : 0;

    window.location.href = "Ajmicrobiology-report-for-product-team?Type=media"
        + "&FromDate=" + fromDate
        + "&ToDate=" + toDate
        + "&LotIds=" + lotIds   // 🔥 fixed
        + "&loadAll=" + loadAll;
    return false;
});


// When Load All button is clicked, set a flag
$(document).on("click", "#btnLoadAll", function() {
    $('#btnLoadAll').data('loadall', true);
});


$(document).on("click", "#pexportToexcel, #mexportToexcel", function() {
    $('#btnLoadAll').data('loadall', false);
});


            $("#createreport").click(function() {
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();
                if (!fromDate || !toDate) {
                    alert("Please Select Dates");
                    return false;
                }
                window.location.href = "in-house-microbiology-report-for-product-create?FromDate=" + fromDate + "&ToDate=" + toDate;
            });
            $("#list").click(function() {
                loadAdvances();
            });
            $('#drpYear').on('change', function() {
                var fy = $(this).val();
                var startYear = parseInt(fy, 10);
                var endYear = startYear + 1;
                var fromDate = startYear + '-04-01';
                var toDate = endYear + '-03-31';
                $('#fromDate').val(fromDate);
                $('#toDate').val(toDate);
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
            $(document).on("click", "#btnMoreSearch", function() {
                loadAdvances(); // Uses current filters
            });
            $(document).on("click", "#btnLoadAll", function() {
                $('#fromDate').val('');
                $('#toDate').val('');
                $('#drpLotId').val(null).trigger("change");
                loadAdvances(true); // Loads all by clearing filters
            });
        });
        function loadAdvances(loadAll = false) {
            $('#ATT_DATA').show();

            const lotIds = $('#drpLotId').val();
            const fromDate = $('#fromDate').val();
            const toDate = $('#toDate').val();
            const fyYear = $('#drpYear').val();

            if ($.fn.DataTable.isDataTable('#mytable-data')) {
                $('#mytable-data').DataTable().destroy();
            }

            $("#pexportToexcel").show();
            $("#mexportToexcel").show();

            $('#mytable-data').DataTable({
                serverSide: true,
                ajax: {
                    url: 'Ajax/AjSwabProdList',
                    type: 'POST',
                    data: {
                        lotIds: lotIds,
                        fromDate: fromDate,
                        toDate: toDate,
                        fyYear: fyYear,
                        loadAll: loadAll,
                    },
                    dataSrc: function(json) {
                        console.log("Data Received:", json);
                        return json.data;
                    }
                },
                columns: [{
                        data: 'Sno'
                    },
                    {
                        data: 'BatchNumber'
                    },
                    {
                        data: 'Plant'
                    },
                    {
                        data: 'SampleDate'
                    },
                    {
                        data: 'SampleCollection'
                    },
                    {
                        data: 'DrawnBy'
                    },
                    {
                        data: 'AnalysisDate'
                    },
                    {
                        data: 'ReportDate'
                    },
                    {
                        data: 'Variety'
                    },
                    // {
                    //     data: 'NameMediaUsed'
                    // },

                    // {
                    //     data: 'EquipmentUsed'
                    // },
                    {
                        data: 'type_of_method'
                    },
                    {
                        data: 'TpcResult'
                    },
                    {
                        data: 'tp_completed_date'
                    },
                    // {
                    //     data: 'ec_nameMediaUsed'
                    // },

                    // {
                    //     data: 'ec_equipmentUsed'
                    // },

                    {
                        data: 'EcoliResult'
                    },
                    {
                        data: 'ec_completed_date'
                    },
                    // {
                    //     data: 'sa_nameMediaUsed'
                    // },
                    // {
                    //     data: 'sa_equipmentUsed'
                    // },
                    {
                        data: 'SaureusResult'
                    },

                    {
                        data: 'sa_completed_date'
                    },

                    // {
                    //     data: 'vc_nameMediaUsed'
                    // },
                    // {
                    //     data: 'vc_equipmentUsed'
                    // },
                    {
                        data: 'VcholeraeResult'
                    },
                    {
                        data: 'vc_completed_date'
                    },
                    // {
                    //     data: 'vp_nameMediaUsed'
                    // },
                    // {
                    //     data: 'vp_equipmentUsed'
                    // },
                    {
                        data: 'VparahaemolyticusResult'
                    },
                    {
                        data: 'vp_completed_date'
                    },
                    {
                        data: 'Salmonella'
                    },
                    {
                        data: 'sal_completed_date'
                    },
                    {
                        data: 'analysed_by'
                    },
                    {
                        data: 'CheckedBy'
                    },
                    {
                        data: 'VerfiedBy'
                    },
                    {
                        data: 'Remarks'
                    }
                ],
                paging: !loadAll, // Disable pagination when loadAll is true
                pageLength: loadAll ? -1 : 100, // -1 means show all rows
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
    <!-- <script>
        $(document).ready(function () {
            $('#drpLotId').select2({
                width: '100%',
                placeholder: "Select Batch Code",
                allowClear: true
            });
            $("#pexportToexcel").click(function () {
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();
                if (fromDate === "" || toDate === "") return false;
                window.location.href = "Ajmicrobiology-report-for-product-team?Type=parameter&FromDate=" + fromDate + "&ToDate=" + toDate;
                return false;
            });
            $("#mexportToexcel").click(function () {
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();
                if (fromDate === "" || toDate === "") return false;
                window.location.href = "Ajmicrobiology-report-for-product-team?Type=media&FromDate=" + fromDate + "&ToDate=" + toDate;
                return false;
            });
            $("#createreport").click(function () {
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();
                if (!fromDate || !toDate) {
                    alert("Please Select Dates");
                    return false;
                }
                window.location.href = "in-house-microbiology-report-for-product-create?FromDate=" + fromDate + "&ToDate=" + toDate;
            });
            $("#list").click(function () {
                loadAdvances();
            });
            // Year Dropdown Change Handler
            $('#drpYear').on('change', function () {
                var fy = $(this).val();
                var startYear = parseInt(fy, 10);
                var endYear = startYear + 1;
                var fromDate = startYear + '-04-01';
                var toDate = endYear + '-03-31';
                $('#fromDate').val(fromDate);
                $('#toDate').val(toDate);
                // 🆕 Fetch Batch Codes based on selected year (date range)
                $.ajax({
                    url: 'Ajax/AjSwabProdList.php', // ✅ Replace with correct file name if different
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        methodType: 'GET_BATCH',
                        fromDate: fromDate,
                        toDate: toDate
                    },
                    success: function (response) {
                        if (response && response.AntibioticLotsData) {
                            var options = '<option value="">Select Batch Code</option>';
                            response.AntibioticLotsData.forEach(function (item) {
                                options += `<option value="${item.RmLotID}">${item.LotNumber}</option>`;
                            });
                            $('#drpLotId').html(options).val(null).trigger('change');
                        } else {
                            $('#drpLotId').html('<option value="">No Batch Codes Found</option>');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Failed to load batch codes:", error);
                    }
                });
            });
            // Add More Search and Load All buttons next to DataTable search box after first draw
            $(document).on('draw.dt', function () {
                if (!$('#btnMoreSearch').length) {
                    $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px; margin-left: 10px;'>More Search</button>" +
                    "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px; margin-left: 10px;'>Load All</button>")
                        .insertAfter("#mytable-data_filter label");
                }
            });
            // Click handlers for new buttons
            $(document).on("click", "#btnMoreSearch", function () {
                loadAdvances(); // Uses current filters
            });
            $(document).on("click", "#btnLoadAll", function () {
                $('#fromDate').val('');
                $('#toDate').val('');
                $('#drpLotId').val(null).trigger("change");
                loadAdvances(); // Loads all by clearing filters
            });
        });
        function loadAdvances() {
            $('#ATT_DATA').show();
            const lotIds = $('#drpLotId').val();
            const fromDate = $('#fromDate').val();
            const toDate = $('#toDate').val();
            const fyYear = $('#drpYear').val();
            if (!fyYear) {
                alert("Please select a valid year.");
                return false;
            }
            if ($.fn.DataTable.isDataTable('#mytable-data')) {
                $('#mytable-data').DataTable().destroy();
            }
            $("#pexportToexcel").show();
            $("#mexportToexcel").show();
            $('#mytable-data').DataTable({
                serverSide: true,
                ajax: {
                    url: 'Ajax/AjSwabProdList.php', // ✅ Ensure this matches your backend
                    type: 'POST',
                    data: {
                        lotIds: lotIds,
                        fromDate: fromDate,
                        toDate: toDate,
                        fyYear: fyYear
                    },
                    dataSrc: function (json) {
                        console.log("Data Received:", json);
                        return json.data;
                    }
                },
                columns: [
                    { data: 'Sno' }, { data: 'BatchNumber' }, { data: 'Plant' }, { data: 'SampleDate' },
                    { data: 'SampleCollection' }, { data: 'DrawnBy' }, { data: 'AnalysisDate' }, { data: 'ReportDate' },
                    { data: 'Variety' }, { data: 'NameMediaUsed' }, { data: 'EquipmentUsed' }, { data: 'type_of_method' },
                    { data: 'TpcResult' }, { data: 'tp_completed_date' },
                    { data: 'ec_nameMediaUsed' }, { data: 'ec_equipmentUsed' }, { data: 'EcoliResult' }, { data: 'ec_completed_date' },
                    { data: 'sa_nameMediaUsed' }, { data: 'sa_equipmentUsed' }, { data: 'SaureusResult' }, { data: 'sa_completed_date' },
                    { data: 'vc_nameMediaUsed' }, { data: 'vc_equipmentUsed' }, { data: 'VcholeraeResult' }, { data: 'vc_completed_date' },
                    { data: 'vp_nameMediaUsed' }, { data: 'vp_equipmentUsed' }, { data: 'VparahaemolyticusResult' }, { data: 'vp_completed_date' },
                    { data: 'sal_nameMediaUsed' }, { data: 'sal_equipmentUsed' }, { data: 'Salmonella' }, { data: 'sal_completed_date' },
                    { data: 'analysed_by' }, { data: 'CheckedBy' }, { data: 'VerfiedBy' }, { data: 'Remarks' }
                ],
                paging: true,
                pageLength: 10,
                scrollX: true,
                scrollY: '500px',
                scrollCollapse: true,
                autoWidth: false,
                responsive: false,
                searching: true,
                ordering: false,
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'></i>",
                        next: "<i class='mdi mdi-chevron-right'></i>"
                    }
                },
                drawCallback: function () {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                }
            });
        }
    </script> -->
</body>
</html>
