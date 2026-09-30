<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$PageTitle = "Shipment Reports List";
$PageTitleFolder = "Reports";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");

$uid = $_SESSION['r_id'];
$ClientID = $_SESSION['ClientID'];

$CurrentMonth = date('m');
//echo $endYear;exit;

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




                                    <div class="row filter">

                                        <div class="col-lg-3 form-group">
                                            <label>From Date</label>
                                            <input type='date' value='<?php echo $year; ?>-04-01' class="form-control"
                                                name="fromDate" id="fromDate"></input>
                                        </div>
                                        <div class="col-lg-3 form-group">
                                            <label>To Date</label>
                                            <input type='date' value='<?php echo $EndDate; ?>' class="form-control"
                                                name="toDate" id="toDate"></input>
                                        </div>

                                        <div class=" col-lg-3">
                                            <label>Select Exporter: </label>
                                            <select class="form-control select2" name="drpExporter" id="drpExporter">
                                                <?php while ($row_add = $StmtExporter->fetch_array()) { ?>
                                                    <option Value="<?php echo $row_add['ExpID']; ?>">
                                                        <?php echo $row_add['ExpName']; ?>
                                                    </option>
                                                <?php } ?>
                                                <option value="">All</option>
                                                <select>
                                        </div>

                                        <div class=" col-lg-3">
                                            <label>Select Country: </label>
                                            <select class="form-control select2" name="drpCountry" id="drpCountry">
                                                <option value="">All</option>
                                                <?php while ($RowCountries = $StmtCountries->fetch_array()) { ?>
                                                    <option Value="<?php echo $RowCountries['id']; ?>">
                                                        <?php echo $RowCountries['name']; ?>
                                                    </option>
                                                <?php } ?>
                                                <select>
                                        </div>

                                        <div class="form-group col-lg-12 text-center">
                                            <button id="list" type="button"
                                                class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                        </div>

                                    </div>




                                </div>
                                <!-- end row -->
                            </div>
                        </div>
                    </div>

                    <!-- end col -->
                </div> <!-- end row -->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="POList">
                                    <!--<h4 class="card-title">Proforma Invoice List</h4>-->


                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div>
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
</body>

<script>
    function convertStringToDate(dateString) {
        // Create a new moment.js object from the string.
        var date = moment(dateString);

        // Format the moment.js object to the dd/mm/yyyy date format.
        var formattedDate = date.format('DD/MM/YYYY');

        // Return the formatted date.
        return formattedDate;
    }

    $('#list').click(function () {
        getList();
    });

    function formatDate(originalDate) {
        // Use Moment.js to parse the original date and format it to "DD/MM/YYYY"
        console.log(originalDate);
        // var parsedDate = moment(originalDate, "DD-MM-YYYY");
        return moment(originalDate, "DD-MM-YYYY").format("DD/MM/YYYY");
    }


    $(document).ready(function () {
        // $.fn.dataTable.moment('D/M/YYYY');

        // var originalDate = "12-06-2023";
        // var formattedDate = formatDate(originalDate);
        // console.log(formattedDate);
    });

    function isValidDate(value) {
        return moment(value, 'DD-MM-YYYY', true).isValid();
    }

    // Function to format the date as desired
    function formatDate(value) {
        return moment(value, 'DD-MM-YYYY').format('DD/MM/YYYY');
    }


    function getList() {
        var exporter = $('#drpExporter').val();
        var fromDate = $('#fromDate').val();
        var toDate = $('#toDate').val();
        var country = $('#drpCountry').val();
        var clientId = '<?php echo $ClientID; ?>';

        // const FROM_PATTERN = 'YYYY-MM-DD HH:mm:ss.SSS';
        // const TO_PATTERN   = 'DD/MM/YYYY HH:mm';

        $.ajax({
            url: 'Ajax/AjaxShipmntReportList',
            type: 'POST',
            // data:{'Exporter':Exporter,'YEAR':YEAR,'TYPE':'POLIST'},
            data: {
                'FromDate': fromDate,
                'ToDate': toDate,
                'Exporter': exporter,
                'Country': country
            },
            success: function (data) {
                $("#POList").html(data);
                // dateColumns = [2, 4, 5, 13, 15, 18];
                dateColumns = [2, 4, 5, 14, 16, 24];
                var table = $('#key-datatable').DataTable({
                    scrollY: "600px",
                    scrollX: true,
                    //sScrollX: "100%",
                    sScrollXInner: "150%",
                    // scrollCollapse:true,
                    paging: true,
                    destroy: true,
                    fixedColumns: true,
                    bSort: true,
                    dom: 'Blfrtip',
                    footer: true,
                    buttons: [{
                        extend: 'excel',
                        text: 'Download Excel',
                        className: 'btnExcelDataTable',
                        title: 'Shipment Report ',
                        footer: true,
                        exportOptions: {
                            columns: ':visible',
                            format: {
                                body: function (data, row, column, node) {

                                    const columnMap = {
                                        DFLV3: [2, 4, 5, 14, 16, 29],
                                        SMEV3: [2, 4, 5, 14, 16, 28],
                                        CAPLV3: [2, 4, 5, 17, 19, 21, 30, 39] //include prod info
                                    };
                                    const dateColumns = columnMap[clientId] || [2, 4, 5, 14, 16, 25];
                                    const ConsigneeColumns = [3, 6];
                                    const ProductColumns = [14, 15, 16];
                                    var cleanData = $('<div>').html(data).text().trim();
                                    if (dateColumns.includes(column)) {
                                        if (cleanData == "--") {
                                            return "--";
                                        } else {
                                            return moment(cleanData, "DD-MM-YYYY").format("YYYY-MM-DD");
                                        }
                                    } else if (ConsigneeColumns.includes(column)) {
                                        return cleanData.replace(/&amp;/g, '&');
                                    } else if(clientId == 'CAPLV3' && ProductColumns.includes(column)){
                                         return $('<div>')
                                                .html(data.replace(/<br\s*\/?>/gi, '\n'))
                                                .text();   // Removes HTML tags
                                    } else {
                                        return "" + cleanData;
                                    }

                                }
                            }
                        }
                    }],
                    order: [
                        [1, 'desc']
                    ],
                    "bStateSave": true,
                    "fnStateSave": function (oSettings, oData) {
                        localStorage.setItem('key-datatable', JSON.stringify(oData));
                    },
                    "fnStateLoad": function (oSettings) {
                        return JSON.parse(localStorage.getItem('key-datatable'));
                    },
                    drawCallback: function () {
                        $(document).trigger('afterSearch');
                    },
                    // "fnInitComplete": function () {
                    //     var i = 1;
                    //     $('invDate').each(function() {                        
                    //         var d = convertStringToDate($(this).text())
                    //         $(this).text(d);
                    //         i++;
                    //     });
                    // },
                });
            }
        });
    }

    function columnSum(classname, floatval) {
        var sum = 0;

        $(classname).each(function () {
            var str = $(this).text();
            val = str.replace(/\,|\$/g, "");
            // sum += parseFloat(val);
            if (val !== "") {
                var num = parseFloat(val);
                if (!isNaN(num)) {
                    sum += num;
                }
            }
        });
        return sum.toFixed(floatval);
    }

    $(document).on('afterSearch', function () {
        var countTotInv = $(".bodyTotInv").length;
        var sumTotAntiDumpingDuty = columnSum(".bodyTotAntiDumpingDuty", 2);
        var sumTotCountervailingDuty = columnSum(".bodyTotCountervailingDuty", 2);
        var sumTotMerchantDuty = columnSum(".bodyTotMerchantDuty", 2);
        var sumTotHarborDuty = columnSum(".bodyTotHarborDuty", 2);
        var sumTotReciprocalTariffsFee = columnSum(".bodyTotReciprocalTariffsFee", 2);

        var sumbodyTotalTariff = columnSum(".bodyTotalTariff", 2);
        var sumTotCustomFreight = columnSum(".bodyTotCustomFreight", 2);

        var sumTotNetWeightKgs = columnSum(".bodyTotNetWeightKgs", 3);
        var sumTotNetWeightLbs = columnSum(".bodyTotNetWeightLbs", 3);
        var sumTotInvoiceValueUSD = columnSum(".bodyTotInvoiceValueUSD", 2);
        var sumTotFOBValueUSD = columnSum(".bodyTotFOBValueUSD", 2);

        $(".footTotInv").text(countTotInv);
        $(".footTotAntiDumpingDuty").text(sumTotAntiDumpingDuty);
        $(".footTotCountervailingDuty").text(sumTotCountervailingDuty);
        $(".footTotMerchantDuty").text(sumTotMerchantDuty);
        $(".footTotHarborDuty").text(sumTotHarborDuty);
        $(".footTotReciprocalTariffsFee").text(sumTotReciprocalTariffsFee);
        $(".footTotTariff").text(sumbodyTotalTariff);

        $(".footTotCustomFreight").text(sumTotCustomFreight);

        $(".footTotNetWeightKgs").text(sumTotNetWeightKgs);
        $(".footTotNetWeightLbs").text(sumTotNetWeightLbs);
        $(".footTotInvoiceValueUSD").text(sumTotInvoiceValueUSD);
        $(".footTotFOBValueUSD").text(sumTotFOBValueUSD);
    });
$("#drpCountry").on("change", function () {
    $("#POList").html("");
});
</script>
