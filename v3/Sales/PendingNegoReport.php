<?php
$PageTitle = "Pending Negotiations Report";
$PageTitleFolder = "Reports";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");


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



// $StartYear = date("Y",strtotime("yesterday"));
$EndDate = date("Y-m-d");

$uid = $_SESSION['r_id'];

$CurrentMonth = date('m');
//echo $endYear;exit;

if ($CurrentMonth > 3) {
    $FinClsYear = $Currentyear + 1;
} else {
    $FinClsYear = $Currentyear;
}
$StartDate = date("Y-m-d", strtotime("2023-04-01"));
$EndDate = date("Y-m-d");

$SqlConsignee = "SELECT * FROM `ScrmConsigneeDetails`";
$StmtConsignee = $iconn->query($SqlConsignee);

$SqlCountries = "SELECT * FROM `ScrmCountriesList`";
$StmtCountries = $iconn->query($SqlCountries);


$SqlExporter = "SELECT  ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1 ";
$StmtExporter = $iconn->query($SqlExporter);

$SqlProcessor = "SELECT  ExpID,ExpName,ExpApprovalNo FROM `AdminExporterDetails` where ExpType=2 ";
$StmtProcessor = $iconn->query($SqlProcessor);

$ClientConfigDataObj = (object) $_SESSION['ClientConfigData'];
$DefaultExporterID = $ClientConfigDataObj->DefaultExporterID;

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
        /* .sticky, .sticky1
        {
            position: sticky !important;
            z-index: 1;
            background: #ffffff
        }
        .sticky
        {
            left: 0;
        }
        .sticky1
        {
            left: 91px;
        }
        .table tr:hover td.sticky,
        .table tr:hover td.sticky1{
            color: #000000 !important;
        } */
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


                                        <div class="col-lg-3 mt-1">
                                            <label>Company</label>
                                            <select class="form-control select2" name="exporter" id="exporter">
                                                <option value="">All</option>
                                                <?php while ($RowExporter = $StmtExporter->fetch_array()) {
                                                    $selected = !empty($DefaultExporterID) && ($DefaultExporterID == $RowExporter['ExpID']) ? 'selected' : '' ;  ?>
                                                    <option Value="<?php echo $RowExporter['ExpID']; ?>" <?php echo $selected;?>>
                                                        <?php echo $RowExporter['ExpName']; ?>
                                                    </option>
                                                <?php } ?>

                                                <select>
                                        </div>

                                        <div class="col-lg-3 mt-1">
                                            <label>Plant</label>
                                            <select class="form-control select2" name="processor" id="processor">
                                                <option value="">All</option>
                                                <?php while ($RowProcessor = $StmtProcessor->fetch_array()) { ?>
                                                    <option Value="<?php echo $RowProcessor['ExpID']; ?>">
                                                        <?php echo $RowProcessor['ExpApprovalNo']; ?>
                                                    </option>
                                                <?php } ?>
                                                <select>
                                        </div>

                                        <div class="col-lg-3 mt-1">
                                            <label>Consignee</label>
                                            <select class="form-control select2" name="consignee" id="consignee">
                                                <option value="">All</option>
                                                <?php while ($RowConsignee = $StmtConsignee->fetch_array()) { ?>
                                                    <option Value="<?php echo $RowConsignee['ConID']; ?>">
                                                        <?php echo $RowConsignee['ConsigneeName']; ?>
                                                    </option>
                                                <?php } ?>
                                                <select>
                                        </div>

                                        <div class="col-lg-3 mt-1">
                                            <label>Country</label>
                                            <select class="form-control select2" name="country" id="country">
                                                <option value=" ">All</option>
                                                <?php while ($RowCountries = $StmtCountries->fetch_array()) { ?>
                                                    <option Value="<?php echo $RowCountries['id']; ?>">
                                                        <?php echo $RowCountries['name']; ?>
                                                    </option>
                                                <?php } ?>
                                                <select>
                                        </div>

                                        <div class="col-lg-3  mt-1 form-group">
                                            <label>From Date</label>
                                            <input type='date' value='<?php echo $year; ?>-04-01' class="form-control"
                                                name="fromDate" id="fromDate"></input>
                                        </div>
                                        <div class="col-lg-3  mt-1 form-group">
                                            <label>To Date</label>
                                            <input type='date' value='<?php echo $EndDate; ?>' class="form-control"
                                                name="toDate" id="toDate"></input>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 text-center">
                                        <button id="list" type="button"
                                            class="btn btn-primary text-left waves-effect waves-light mt-3">
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
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="POList">

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

    <script>
        $('#list').click(function () {

            var exporter = $('#exporter').val();
            var processor = $('#processor').val();
            var consignee = $('#consignee').val();
            var country = $('#country').val();
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();

            $.ajax({
                url: 'Ajax/AjaxPendingNegoReport',
                type: 'POST',
                data: {
                    "FromDate": fromDate,
                    "ToDate": toDate,
                    "Exporter": exporter,
                    "Processor": processor,
                    "Country": country,
                    "Consignee": consignee
                },
                success: function (data) {
                    $("#POList").html(data);
                    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                        var searchVal = $('#key-datatable_filter input').val().trim();
                        if (searchVal === "") return true;

                        for (var i = 0; i < data.length; i++) {
                            var cell = (data[i] || "").trim();
                            if (cell.toLowerCase() === searchVal.toLowerCase()) {
                                return true;
                            }
                        }
                        return false;
                    });
                    var table = $('#key-datatable').DataTable({
                        scrollY: "600px",
                        scrollX: true,
                        //sScrollX: "100%",
                        sScrollXInner: "150%",
                        // scrollCollapse:true,
                        paging: true,
                        destroy: true,
                        fixedColumns: true,
                        // Preserve the delivered/ETA/onboard priority returned by the API.
                        ordering: false,
                        order: [],
                        stateSave: false,
                        dom: 'Bfrtip',
                        buttons: [{
                            extend: 'excelHtml5',
                            text: 'Download Excel',
                            className: 'btnExcelDataTable',
                            title: 'Pending Negotiations Report',

                            exportOptions: {
                                columns: ':visible',
                                footer: false,
                                format: {
                                    body: function (data, row, column, node) {
                                       

                                        const dateColumns = [7, 8, 9];

                                        if (dateColumns.includes(column)) {
                                            if (data == "--") {
                                                return "--";
                                            } else {
                                                return moment(data, "DD-MM-YYYY").format("YYYY-MM-DD");
                                            }
                                        }

                                        if ($(node).find('table').length > 0) {
                                            return $(node).find('td').map(function () {
                                                return $(this).text().trim();
                                            }).get().join('\n');
                                        }
                                        return data;
                                    },
                                    footer: function (data, row, column, node) {
                                        let cleaned = data.replace(/<br\s*\/?>/gi, '\n');
                                        cleaned = cleaned.replace(/<[^>]*>/g, '');
                                        cleaned = cleaned.replace(/&nbsp;/g, ' ');
                                        return cleaned.trim();
                                    }
                                }
                            },

                            customize: function (xlsx) {

                                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                var sheetData = sheet.getElementsByTagName('sheetData')[0];

                                // Get last row number
                                var lastRow = sheetData.getElementsByTagName('row').length;
                                var nextRow = lastRow + 1;

                                // Read footer values
                                var TotalValueUSD = $(".footInvValUSD").first().text().trim();
                                var TotalValueINR = $(".footInvValINR").first().text().trim();
                                var TotalValuePOUND = $(".footInvValPOUND").first().text().trim();
                                var TotalValueEURO = $(".footInvValEURO").first().text().trim();
                                var TotalValueGBP = $(".footInvValGBP").first().text().trim();
                                var TotalValueCAD = $(".footInvValCAD").first().text().trim();

                                // Helper function to create bold Excel row
                                function createRow(label, value, rowNum) {
                                    return (
                                        '<row r="' + rowNum + '">' +
                                        '<c t="inlineStr" r="F' + rowNum + '">' +
                                        '<is><r><rPr><b/></rPr><t>' + label + '</t></r></is>' +
                                        '</c>' +
                                        '<c t="inlineStr" r="G' + rowNum + '">' +
                                        '<is><r><rPr><b/></rPr><t>' + value + '</t></r></is>' +
                                        '</c>' +
                                        '</row>'
                                    );
                                }

                                var rowsToAdd = "";

                                // ADD ROWS ONLY IF VALUE > 0
                                if (TotalValueUSD !== "" && TotalValueUSD !== "0") {
                                    rowsToAdd += createRow("Total Value USD", TotalValueUSD, nextRow++);
                                }

                                if (TotalValueINR !== "" && TotalValueINR !== "0") {
                                    rowsToAdd += createRow("Total Value INR", TotalValueINR, nextRow++);
                                }

                                if (TotalValuePOUND !== "" && TotalValuePOUND !== "0") {
                                    rowsToAdd += createRow("Total Value POUND", TotalValuePOUND, nextRow++);
                                }

                                if (TotalValueEURO !== "" && TotalValueEURO !== "0") {
                                    rowsToAdd += createRow("Total Value EURO", TotalValueEURO, nextRow++);
                                }

                                if (TotalValueGBP !== "" && TotalValueGBP !== "0") {
                                    rowsToAdd += createRow("Total Value GBP", TotalValueGBP, nextRow++);
                                }

                                if (TotalValueCAD !== "" && TotalValueCAD !== "0") {
                                    rowsToAdd += createRow("Total Value CAD", TotalValueCAD, nextRow++);
                                }

                                // Append all rows
                                sheetData.innerHTML += rowsToAdd;
                            }

                        }],

                        "fnStateSave": function (oSettings, oData) {
                            localStorage.setItem('key-datatable', JSON.stringify(oData));
                        },
                        "fnStateLoad": function (oSettings) {
                            return JSON.parse(localStorage.getItem('key-datatable'));
                        },
                        drawCallback: function () {
                            calculateSum();
                        }
                    });

                    table.on('draw.dt', function () {
                        calculateSum();
                    });
                }
            });
        });

        function calculateSum() {

            var sumInvValUSD = 0;
            var sumInvValINR = 0;
            var sumInvValPOUND = 0;
            var sumInvValEURO = 0;
            var sumInvValGBP = 0;
            var sumInvValCAD = 0;
            $(".bodyInvValUSD").each(function () {
                var valueText = $(this).text().trim();
                var currency = $(this).attr('uda-currency').trim();
                var totalvalue = $(this).attr('uda-totalvalue').trim();
                if (currency == 'USD') {
                    sumInvValUSD += parseFloat(totalvalue.replace(/,/g, ''));
                }

                if (currency == 'INR') {
                    sumInvValINR += parseFloat(totalvalue.replace(/,/g, ''));
                }

                 if (currency == 'POUND') {
                    sumInvValPOUND += parseFloat(totalvalue.replace(/,/g, ''));
                }

                 if (currency == 'EURO') {
                    sumInvValEURO += parseFloat(totalvalue.replace(/,/g, ''));
                }

                 if (currency == 'GBP') {
                    sumInvValGBP += parseFloat(totalvalue.replace(/,/g, ''));
                }
                 if (currency == 'CAD') {
                    sumInvValCAD += parseFloat(totalvalue.replace(/,/g, ''));
                }

                $('.footInvValUSD').text('$ ' + sumInvValUSD.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                $('.footInvValINR').text('₹ ' + sumInvValINR.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                 $('.footInvValPOUND').text('£ ' + sumInvValINR.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                 $('.footInvValEURO').text('€ ' + sumInvValINR.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                 $('.footInvValGBP').text('£ ' + sumInvValINR.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                 $('.footInvValCAD').text('C$ ' + sumInvValINR.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

            });

        }



$("#processor").on("change", function () {
    $("#POList").html("");
});
    </script>
</body>

</html>
