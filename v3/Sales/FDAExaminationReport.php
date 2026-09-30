<?php
$PageTitle = "FD Examination Report";
$PageTitleFolder = "Sales";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");

$uid = $_SESSION['r_id'];


// Get the current month
$month = date('m');
// Check if the current month is greater than or equal to April
if ($month >= 4) {
    // Get the current year
    $year = date('Y');
} else {
    // Get the previous year
    $year = date('Y') - 1;
}
// Print the financial year
//echo $year . '-' . ($year + 1);

$EndDate = date("Y-m-d");



$CurrentMonth = date('m');
//echo $endYear;exit;

if ($CurrentMonth > 3) {
    $FinClsYear = $Currentyear + 1;
} else {
    $FinClsYear = $Currentyear;
}
//echo $endYear;exit;

$SqlConsignee = "SELECT * FROM `ScrmConsigneeDetails`";
$StmtConsignee = $iconn->query($SqlConsignee);

$SqlCountries = "SELECT * FROM `ScrmCountriesList`";
$StmtCountries = $iconn->query($SqlCountries);


$SqlExporter = "SELECT  ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1 ";
$StmtExporter = $iconn->query($SqlExporter);

?>

<!doctype html>
<html lang="en">

<link href="https://serp.silverlights.in/Clients/Jlsf/User.css" rel="stylesheet" type="text/css" />

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>

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
        .read-more {
            color: #0d6efd;
            cursor: pointer;
            font-size: 13px;
            display: inline-block;
            margin-top: 4px;
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
                                            <label>From Date</label>
                                            <input type='date' value='<?php echo $year; ?>-04-01' class="form-control"
                                                name="fromDate" id="fromDate"></input>
                                        </div>

                                        <div class="form-group col-lg-3">
                                            <label>To Date</label>
                                            <input type='date' value='<?php echo $EndDate; ?>' class="form-control"
                                                name="toDate" id="toDate"></input>
                                        </div>

                                        <div class="col-lg-3">
                                            <label>Select Exporter</label>
                                            <select class="form-control select2" name="Exporter" id="Exporter">
                                                <?php while ($row_add = $StmtExporter->fetch_array()) { ?>
                                                    <option Value="<?php echo $row_add['ExpID']; ?>">
                                                        <?php echo $row_add['ExpName']; ?>
                                                    </option>
                                                <?php } ?>
                                                <option value="">All</option>
                                                <select>
                                        </div>

                                        <div class="col-lg-3">
                                            <label>Status </label>
                                            <select class="form-control select2" name="drpFDAStatus" id="drpFDAStatus">
                                                <option value="">Select</option>
                                                <option value="SELECTED FOR FDA EXAMINATION">SELECTED FOR FDA
                                                    EXAMINATION</option>
                                                <option value="CUSTOMS X-RAY-MET-SCREENING EXAM">CUSTOMS
                                                    X-RAY-MET-SCREENING EXAM</option>
                                                <option value="AWAITING LOT DETAILS">AWAITING LOT DETAILS</option>
                                                <option value="LOT DETAILS SUBMITTED">LOT DETAILS SUBMITTED</option>
                                                <option value="LINE AVAILABILITY RECEIVED">LINE AVAILABILITY RECEIVED
                                                </option>
                                                <option value="SAMPLES COLLECTED">SAMPLES COLLECTED</option>
                                                <option value="LAB ANALYSIS IN PROCESS">LAB ANALYSIS IN PROCESS</option>
                                                <option value="LAB ANALYSIS COMPLETED">LAB ANALYSIS COMPLETED</option>
                                                <option value="PENDING REVIEW BY FDA COMPLIANCE STAFF">
                                                    PENDING REVIEW BY FDA COMPLIANCE STAFF
                                                </option>
                                                <option value="TESTED AND RELEASED">TESTED AND RELEASED</option>
                                                <option value="RELEASED WITHOUT TESTING">RELEASED WITHOUT TESTING
                                                </option>
                                                <option value="DETAINED">DETAINED</option>
                                                <option value="RELEASED AFTER WRONGFUL DETENTION">
                                                    RELEASED AFTER WRONGFUL DETENTION
                                                </option>

                                                <option value="Rejected">Rejected</option>
                                                <option value="Cancelled">Cancelled</option>
                                                <option value="Destroyed">Destroyed</option>
                                                <option value="Container ROB">Container ROB</option>
                                                <option value="Return Container">Return Container</option>
                                                <!-- <option value="Other Reasons">Other Reasons</option> -->
                                                <select>
                                        </div>

                                        <div class="col-lg-3">
                                            <label>FD Examination Status </label>
                                            <select class="form-control select2" name="drpFDAExmStatus" id="drpFDAExmStatus">
                                                <option value="">Select</option>
                                                <option value="Yes" selected>Yes</option>
                                                 <option value="Proceed">Proceed</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-12 text-center">
                                            <button id="list" type="button"
                                                class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                        </div>

                                    </div>
                                    <!-- end row -->
                                </div>
                            </div>
                        </div>

                        <!-- end col -->
                    </div> <!-- end row -->

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="POList">

                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div>
                    <!-- end row-->


                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
    <!-- end row-->

    </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    <?php include($IncludesPath . "footer.php"); ?>

    </div>
    <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>


    <script>
        function applyReadMore(limit = 15) {
            $('.remarks-text').each(function() {

                let fullText = $(this).text().trim();

                if (fullText.length <= limit) return;

                let visible = fullText.substring(0, limit);
                let hidden = fullText.substring(limit);

                $(this).html(
                    '<span class="remarks-visible">' + visible + '..</span>' +
                    '<span class="remarks-hidden" style="display:none;">' + hidden + '</span>' +
                    '<a href="#" class="read-more"> Read more</a>'
                );
            });
        }

        $(document).on('click', '.read-more', function(e) {
            e.preventDefault();

            let container = $(this).closest('.remarks-text');
            let hiddenText = container.find('.remarks-hidden');
            let visibleText = container.find('.remarks-visible');

            hiddenText.toggle();

            if (hiddenText.is(':visible')) {
                visibleText.text(visibleText.text().replace('..', ''));
                $(this).text(' Read less');
            } else {
                visibleText.text(visibleText.text().replace(/$/, '..'));
                $(this).text(' Read more');
            }
        });


        $('#list').click(function() {

            var exporter = $('#Exporter').val();
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            var status = $('#drpFDAStatus').val();
            var FDAExStatus = $('#drpFDAExmStatus').val();
            console.log($('#drpFDAExmStatus').val());

            $.ajax({
                url: 'Ajax/AjaxFDAExmRprtList',
                type: 'POST',
                data: {
                    'Exporter': exporter,
                    'FromDate': fromDate,
                    'ToDate': toDate,
                    'Status': status,
                    'FDAExmStatus': FDAExStatus,
                },
                success: function(data) {
                    $("#POList").html(data);
                    var table = $('#key-datatable').DataTable({
                        scrollY: "600px",
                        scrollX: true,
                        //sScrollX: "100%",
                        sScrollXInner: "150%",
                        scrollCollapse: true,
                        paging: true,
                        destroy: true,
                        fixedColumns: true,
                        bSort: false,
                        //order: [[1, 'desc']],
                        dom: 'Blfrtip',
                        buttons: [{
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'btnExcelDataTable',
                            title: 'FDA Examination Report',
                            footer: true,
                            exportOptions: {
                                columns: ':visible',
                                format: {
                                    body: function(data, row, column, node) {

                                        const dateColumns = [6, 10, 11];
                                        const BuyerColumn = [2];
                                        const removeBRTagColumn = [3, 9];
                                        const remarksColumn = [15];

                                        if (dateColumns.includes(column)) {
                                            if (data == "--") {
                                                return "--";
                                            } else {
                                                return moment(data, "DD-MM-YYYY").format("YYYY-MM-DD");
                                            }
                                        }
                                        // if (BuyerColumn.includes(column)) {
                                        //     return data.replace(/<br>/g, '');
                                        // }

                                        if (BuyerColumn.includes(column)) {
                                            return $('<div>').html(data).text().replace(/\s+/g, ' ').trim();
                                        }


                                        if (remarksColumn.includes(column)) {
                                            return $('<div>').html(data).text().trim();
                                        } else if (removeBRTagColumn.includes(column)) {
                                            return data.replace(/<br>/g, ' ');
                                        } else {
                                            return data;
                                        }

                                        // if (dateColumns.includes(column)) {
                                        //     if(data == "--") {
                                        //         return "--";    
                                        //     }else {
                                        //         return moment(data, "DD-MM-YYYY").format("YYYY-MM-DD");
                                        //     }
                                        // } else {
                                        //     return data;
                                        // }

                                    }
                                }

                            }
                        }],
                        "bStateSave": true,
                        "fnStateSave": function(oSettings, oData) {
                            localStorage.setItem('key-datatable', JSON.stringify(oData));
                        },
                        "fnStateLoad": function(oSettings) {
                            return JSON.parse(localStorage.getItem('key-datatable'));
                        }
                    });
                    applyReadMore(15);
                }
            });


        });
$("#drpFDAExmStatus").on("change", function () {
    $("#POList").html("");
});
    </script>


</body>

</html>
