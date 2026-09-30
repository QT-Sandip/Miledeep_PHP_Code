<?php
$PageTitle = "FDA Release Information";
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

$EndDate = date("Y-m-d");

// $StartDate = date("Y-m-d",strtotime("2023-04-01"));
// $EndDate = date("Y-m-d");

$uid = $_SESSION['r_id'];

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

$SqlProcessor = "SELECT  ExpID,ExpName,ExpApprovalNo FROM `AdminExporterDetails` where ExpType=2 ";
$StmtProcessor = $iconn->query($SqlProcessor);

?>

<!doctype html>
<html lang="en">

<link href="https://serp.silverlights.in/Clients/Jlsf/User.css" rel="stylesheet" type="text/css" />

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>

    <!-- DataTables -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css" rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />


    <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" />
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




                                    <div class="row">



                                        <div class="col-lg-3 mt-3">
                                            <label>Company</label>
                                            <select class="form-control select2" name="exporter" id="exporter">
                                                <option value="">All</option>
                                                <?php while ($RowExporter = $StmtExporter->fetch_array()) { ?>
                                                    <option Value="<?php echo $RowExporter['ExpID']; ?>"><?php echo $RowExporter['ExpName']; ?></option>
                                                <?php } ?>

                                                <select>
                                        </div>

                                        <div class="col-lg-3 mt-3">
                                            <label>Plant</label>
                                            <select class="form-control select2" name="processor" id="processor">
                                                <option value="">All</option>
                                                <?php while ($RowProcessor = $StmtProcessor->fetch_array()) { ?>
                                                    <option Value="<?php echo $RowProcessor['ExpID']; ?>"><?php echo $RowProcessor['ExpApprovalNo']; ?></option>
                                                <?php } ?>
                                                <select>
                                        </div>

                                        <div class="col-lg-3 mt-3">
                                            <label>Consignee</label>
                                            <select class="form-control select2" name="consignee" id="consignee">
                                                <option value="">All</option>
                                                <?php while ($RowConsignee = $StmtConsignee->fetch_array()) { ?>
                                                    <option Value="<?php echo $RowConsignee['ConID']; ?>"><?php echo $RowConsignee['ConsigneeName']; ?></option>
                                                <?php } ?>
                                                <select>
                                        </div>

                                        <div class="col-lg-3 mt-3">
                                            <label>Country</label>
                                            <select class="form-control select2" name="country" id="country">
                                                <option value=" ">All</option>
                                                <?php while ($RowCountries = $StmtCountries->fetch_array()) { ?>
                                                    <option Value="<?php echo $RowCountries['id']; ?>"><?php echo $RowCountries['name']; ?></option>
                                                <?php } ?>
                                                <select>
                                        </div>

                                        <div class="col-lg-3  mt-3 form-group">
                                            <label>From Date</label>
                                            <input type='date' value='<?php echo $year; ?>-04-01' class="form-control" name="fromDate" id="fromDate"></input>
                                        </div>
                                        <div class="col-lg-3  mt-3 form-group">
                                            <label>To Date</label>
                                            <input type='date' value='<?php echo $EndDate; ?>' class="form-control" name="toDate" id="toDate"></input>
                                        </div>

                                    </div>


                                    <div class="col-lg-12 text-center">
                                        <button id="list" type="button" class="btn btn-primary text-left waves-effect waves-light mt-3">
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
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/datatables-buttons-excel-styles@1.2.0/js/buttons.html5.styles.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables-buttons-excel-styles@1.2.0/js/buttons.html5.styles.templates.min.js"></script>


    <!-- Datatable init js -->
    <script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>

    <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <script>
        $('#list').click(function() {

            var exporter = $('#exporter').val();
            var processor = $('#processor').val();
            var consignee = $('#consignee').val();
            var country = $('#country').val();
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();

            $.ajax({
                url: 'Ajax/AjaxFdaReleaseInfoList',
                type: 'POST',
                data: {
                    "FromDate": fromDate,
                    "ToDate": toDate,
                    "Exporter": exporter,
                    "Processor": processor,
                    "Country": country,
                    "Consignee": consignee
                },
                success: function(data) {
                    $("#POList").html(data);
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
                        order: [
                            [1, 'asc']
                        ],
                        "bStateSave": true,
                        // dom: 'Blfrtip',
                        dom: 'Bfrtip',
                        // columnDefs: [
                        //     { width: '25px', targets: 0 },
                        //     { width: '90px', targets: 1 },
                        //     { width: '150px', targets: 2 },
                        //     { width: 'auto', targets: 3 },
                        // ],
                        buttons: [{
                                extend: 'excelHtml5',
                                text: 'Download Excel',
                                className: 'btnExcelDataTable',
                                title: 'FDA Release Information',
                                footer: true,
                                exportOptions: {
                                    columns: ':visible',
                                    format: {
                                        body: function(data, row, column, node) {

                                            const dateColumns = [10, 11, 12, 13, 14, 15];

                                            if (dateColumns.includes(column)) {
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
                            },
                            //    'colvis'
                        ],
                        "fnStateSave": function(oSettings, oData) {
                            localStorage.setItem('key-datatable', JSON.stringify(oData));
                        },
                        "fnStateLoad": function(oSettings) {
                            return JSON.parse(localStorage.getItem('key-datatable'));
                        },
                        drawCallback: function() {
                            $(document).trigger('afterSearch');
                        }
                    });
                }
            });

        });

        function columnSum(classname, floatval) {
            var sum = 0;

            $(classname).each(function() {
                var str = $(this).text();
                val = str.replace(/\,|\$/g, "");

                sum += parseFloat(val);
            });
            return sum.toFixed(floatval);
        }

        $(document).on('afterSearch', function() {
            var countTotInv = $(".bodyInvNo").length;
            var sumTotInvoiceValueUSD = columnSum(".bodyTotInvoiceValueUSD", 2);

            $(".footTotInv").text(countTotInv);
            $(".footTotInvoiceValueUSD").text(sumTotInvoiceValueUSD);
        });
    </script>
</body>

</html>