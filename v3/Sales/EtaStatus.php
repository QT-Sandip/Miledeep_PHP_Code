<?php
$PageTitle = "OUT of ETD/ETA Report";
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


if ($CurrentMonth > 3) {
    $FinClsYear = $Currentyear + 1;
} else {
    $FinClsYear = $Currentyear;
}

if ($SelectedYear != '') {

    if ($CurrentMonth > 3) {
        $FinClsYear = $SelectedYear + 1;
    } else {
        $FinClsYear = $SelectedYear;
    }
}



$SqlExporter = "SELECT ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1";
$StmtExporter = $iconn->query($SqlExporter);

$SqlProcessor = "SELECT ExpID,ExpName FROM `AdminExporterDetails` where ExpType=2";
$StmtProcessor = $iconn->query($SqlProcessor);

$SqlConsignee = "SELECT * FROM `ScrmConsigneeDetails` LEFT JOIN `ScrmCountriesList` ON ScrmConsigneeDetails.ConsigneeCountry = ScrmCountriesList.id";
$StmtConsignee = $iconn->query($SqlConsignee);

$ClientConfigDataObj = (object) $_SESSION['ClientConfigData'];
$DefaultExporterID = $ClientConfigDataObj->DefaultExporterID;

?>

<!doctype html>
<html lang="en">


<link href="<?php echo  $href ?>" rel="stylesheet" type="text/css" />

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

<!--<body data-sidebar="dark" class="sidebar-enable vertical-collpsed">-->

<body data-sidebar="dark" class="sidebar-enable">

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
                                        <div class="col-lg-3">
                                            <label>Select Exporter: </label>
                                            <select class="form-control select2" name="ExporterID" id="ExporterID">
                                                <option value="">All</option>
                                                <?php while ($RowExporter = $StmtExporter->fetch_array()) {
                                                    $selected = !empty($DefaultExporterID) && ($DefaultExporterID == $RowExporter['ExpID']) ? 'selected' : '' ; ?>
                                                    <option Value="<?php echo $RowExporter['ExpID']; ?>" <?php echo $selected;?>><?php echo $RowExporter['ExpName']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <!-- <div class="row"> -->
                                        <div class="col-lg-3">
                                            <label>Select Processor: </label>
                                            <select class="form-control select2" name="ProcessorID" id="ProcessorID">
                                                <option value="">All</option>
                                                <?php while ($RowProcessor = $StmtProcessor->fetch_array()) { ?>
                                                    <option Value="<?php echo $RowProcessor['ExpID']; ?>"><?php echo $RowProcessor['ExpName']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-lg-3">
                                            <label>Consignee</label>
                                            <select class="form-control select2" name="ConsigneeID" id="ConsigneeID">
                                                <option value="">ALL</option>
                                                <?php while ($RowConsignee = $StmtConsignee->fetch_array()) { ?>
                                                    <option Value="<?php echo $RowConsignee['ConID']; ?>"><?php echo $RowConsignee['ConsigneeName'] . ' - ' . $RowConsignee['name']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-lg-3">
                                            <label>ETD/ETA </label>
                                            <select class="form-control select2" name="ETD_ETA" id="ETD_ETA">
                                                <option value="">All</option>
                                                <option value="ETD">ETD</option>
                                                <option value="ETA">ETA</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-lg-3">
                                            <label>From Date(Discharge Date)</label>
                                            <input type='date' value='<?php echo $year; ?>-04-01' class="form-control" name="FromDate" id="FromDate"></input>
                                        </div>

                                        <div class="form-group col-lg-3">
                                            <label>To Date(Discharge Date) </label>
                                            <input type='date' value='<?php echo $EndDate; ?>' class="form-control" name="ToDate" id="ToDate"></input>
                                        </div>

                                        <div class="col-lg-12 text-center">
                                            <button id="list" type="button" class="btn btn-primary waves-effect waves-light"> Search <i class="ri-arrow-right-line align-middle ml-2"></i></button>

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
                                <div class="card-body" id="ShowDataTable">
                                    <!--<h4 class="card-title">Proforma Invoice List</h4>-->


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

    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php // include ($IncludesPath."ShowDataTable.php");
    ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
</body>

<script>
    // $(document).on('click', '#Download', function(e) {
    //     var Exporter = $('#ExporterID').val();
    //     var Processor = $('#ProcessorID').val();
    //     var ETD_ETA = $('#ETD_ETA').val();
    //     var ToDate = $('#ToDate').val();
    //     var FromDate = $('#FromDate').val();
    //     var Link = $(this).val();

    //     var dynamic_link = Link + '?Exporter=' + Exporter + "&TYPE=POLIST" + "&Processor=" + Processor ";
    //     window.open(dynamic_link);
    // });

    $('document').ready(function() {
        // getPiList();
        $('#elisa').on('click', function() {
            alert("test");
            $('form').submit();
        });
    });

    $(function() {
        $('#example1').dataTable({
            "order": [
                [0, 'desc']
            ]
        });

        $('#list').click(function() {
            getPiList();
        });
    });

    function getPiList() {
        var ExporterID = $('#ExporterID').val();
        var ProcessorID = $('#ProcessorID').val();
        var ToDate = $('#ToDate').val();
        var FromDate = $('#FromDate').val();
        var ETD_ETA = $('#ETD_ETA').val();
        var ConsigneeID = $('#ConsigneeID').val();

        var startDate = new Date(FromDate);
        var endDate = new Date(ToDate);

        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

        var days = daysDiff + 1;

        console.log(days);

        if (days > 31) {
            var url = 'Ajax/AjaxEtaPoList.php?Exporter=' + ExporterID + '&Processor=' + ProcessorID + '&ConsigneeID=' + ConsigneeID + '&ETDETA=' + ETD_ETA + '&FromDate=' + FromDate + '&ToDate=' + ToDate + '&excelDownload=T';
            window.open(url, '_blank');
        } else {
            $.ajax({
                url: 'Ajax/AjaxEtaPoList.php',
                type: 'POST',
                data: {
                    'Exporter': ExporterID,
                    'Processor': ProcessorID,
                    'ETDETA': ETD_ETA,
                    'FromDate': FromDate,
                    'ConsigneeID': ConsigneeID,
                    'ToDate': ToDate
                },
                success: function(data) {
                    // showData(data);
                    $("#ShowDataTable").html(data);
                    var table = $('#key-datatable').DataTable({
                        scrollY: "600px",
                        scrollX: true,
                        sScrollXInner: "150%",
                        paging: true,
                        destroy: true,
                        fixedColumns: true,
                        dom: 'Blfrtip',
                        buttons: [{
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'btnExcelDataTable',
                            title: "OUT_of_ETD_ETA_Report",
                            exportOptions: {
                                columns: ':visible',
                                format: {
                                    body: function(data, row, column, node) {

                                        const dateColumns = [9, 10, 11, 12];
                                        const productColumn = [5, 8];

                                        if (dateColumns.includes(column)) {
                                            if (data == "--") {
                                                return "--";
                                            } else {
                                                return moment(data, "DD-MM-YYYY").format("YYYY-MM-DD");
                                            }
                                        }
                                        if (productColumn.includes(column)) {
                                            return data.replace(/<br>/g, '');
                                        } else {
                                            return data;
                                        }

                                    }
                                }
                            }

                        }],
                        bSort: true,
                        "bStateSave": true,
                        "fnStateSave": function(oSettings, oData) {
                            localStorage.setItem('key-datatable', JSON.stringify(oData));
                        },
                        "fnStateLoad": function(oSettings) {
                            return JSON.parse(localStorage.getItem('key-datatable'));
                        }
                    });
                }
            });
        }
    }
$("#ConsigneeID, #ProcessorID").on("change", function () {
    $("#ShowDataTable").html("");
});
</script>
