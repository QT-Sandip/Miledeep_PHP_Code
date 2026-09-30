<?php
$PageTitle = "Clearing Agent Report";
$PageTitleFolder = "Reports";

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

// echo print_r($_SESSION);exit;
$ClientId = $_SESSION['ClientID'];
$AccessType = $_SESSION['AccessType'];
$DeptID = $_SESSION['DeptID'];
$IsAccess = 0;
if ($ClientId == 'SMEV3' && $AccessType == 2 && ($DeptID == 8 || $DeptID == 4)) {
    $IsAccess = 1;
} else {
    $IsAccess = 0;
}

//  $CurrentMonth=date('m');	
//echo $endYear;exit;

// if($CurrentMonth >3){	
// $FinClsYear=$Currentyear+1;	
// }else{
// $FinClsYear=$Currentyear;
// }
//echo $endYear;exit;

$SqlConsignee = "SELECT * FROM `ScrmConsigneeDetails`";
$StmtConsignee = $iconn->query($SqlConsignee);

$SqlCountries = "SELECT * FROM `ScrmCountriesList`";
$StmtCountries = $iconn->query($SqlCountries);

$SqlExporterDetails = "SELECT * FROM `AdminExporterDetails` WHERE ExpType=1;";
$StmtExporterDetails = $iconn->query($SqlExporterDetails);


$SqlNotifyparty = "SELECT * FROM `ScrmNotifyparty` WHERE Status=1 ";
$StmtNotifyparty = $iconn->query($SqlNotifyparty);

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

                                    <div class="row">

                                        <div class="form-group col-lg-3">
                                            <label>Company: </label>
                                            <select class="form-control select2" name="drpCompany" id="drpCompany">
                                                <option value="">All</option>
                                                <?php while ($RowExporterDetails = $StmtExporterDetails->fetch_array()) { ?>
                                                    <option value="<?php echo $RowExporterDetails['ExpID']; ?>">
                                                        <?php echo $RowExporterDetails['ExpName']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <!-- <div class="form-group col-lg-3">
                                            <label>Country: </label>
                                            <select class="form-control select2" name="drpCountry" id="drpCountry">
                                                <option value="">All</option>
                                                <?php while ($RowCountries = $StmtCountries->fetch_array()) { ?>
                                                    <option value="<?php echo $RowCountries['id']; ?>"><?php echo $RowCountries['name']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div> -->

                                        <div class="form-group col-lg-3">
                                            <label>Consignee: </label>
                                            <select class="form-control select2" name="drpConsignee" id="drpConsignee">
                                                <option value="">All</option>
                                                <?php while ($RowConsignee = $StmtConsignee->fetch_array()) { ?>
                                                    <option value="<?php echo $RowConsignee['ConID'] ?>">
                                                        <?php echo $RowConsignee['ConsigneeName'] ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-lg-3">
                                            <label>Clearing Agent Name: </label>
                                            <select class="form-control select2" name="drpClearingAgentName"
                                                id="drpClearingAgentName">
                                                <option value="">All</option>
                                                <?php while ($RowNotifyparty = $StmtNotifyparty->fetch_array()) { ?>
                                                    <option value="<?php echo $RowNotifyparty['NotifyPartyID'] ?>">
                                                        <?php echo $RowNotifyparty['NotifyPartyName'] ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-lg-3">
                                            <label>Date Selection</label>
                                            <select class="form-control select2" name="drpDateSelection"
                                                id="drpDateSelection">

                                                <option value="EntryDate">Entry Date</option>
                                                <option value="InvoiceDate">Invoice Date</option>
                                            </select>
                                        </div>

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

                                        <?php if($IsAccess == 1){ ?>
                                            <div class="form-group col-lg-3">
                                                <label>Approval Selection</label>
                                                <select class="form-control select2" name="drpApprovalSelection"
                                                    id="drpApprovalSelection">
                                                    <option value="" selected>Select</option>
                                                    <option value="2">Pending</option>
                                                    <option value="1">Approved</option>
                                                    <option value="0">Approval Pending</option>
                                                </select>
                                            </div>
                                        <?php } ?>

                                        <div class="col-lg-12 text-center">
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
    <script>


        $(document).ready(function () {
            $('#drpDateSelection').val('InvoiceDate').trigger('change');;
        });

        $('#list').click(function () {

            var Company = $('#drpCompany').val();
            var Country = $('#drpCountry').val();
            var Consignee = $('#drpConsignee').val();
            var ClearingAgentName = $('#drpClearingAgentName').val();
            var DateSelection = $('#drpDateSelection').val();
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            var Approval = $('#drpApprovalSelection').val();

            var startDate = new Date(fromDate);
            var endDate = new Date(toDate);

            var timeDiff = endDate.getTime() - startDate.getTime();
            var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

            var days = daysDiff + 1;


            if (days > 31) {
                var url = 'Ajax/AjaxClearingRprtNew.php?Exporter=' + Company + '&Country=' + Country + '&Consignee=' + Consignee + '&ClearingAgent=' + ClearingAgentName + '&DateSelection=' + DateSelection + '&FromDate=' + fromDate + '&ToDate=' + toDate + '&Approval' + Approval + '&excelDownload=T';
                window.open(url, '_blank');
            } else {
                $.ajax({
                    url: 'Ajax/AjaxClearingRprtNew',
                    type: 'POST',
                    data: {
                        'Exporter': Company,
                        // 'Country': Country,
                        'Consignee': Consignee,
                        'ClearingAgent': ClearingAgentName,
                        'DateSelection': DateSelection,
                        'FromDate': fromDate,
                        'ToDate': toDate,
                        'Approval': Approval
                    },
                    success: function (data) {
                        data = JSON.parse(data);
                        $("#POList").html(data.content);
                        var table = $('#key-datatable').DataTable({
                            scrollY: "600px",
                            scrollX: true,
                            //sScrollX: "100%",
                            sScrollXInner: "150%",
                            scrollCollapse: true,
                            paging: true,
                            destroy: true,
                            fixedColumns: true,
                            bSort: true,
                            order: [
                                [1, 'desc']
                            ],
                            "bStateSave": true,
                            dom: 'Blfrtip',
                            buttons: [
    {
        text: 'Download Excel',
        className: 'btnExcelDataTable',
        action: function (e, dt, node, config) {
            var url = 'Ajax/AjaxClearingRprtNew.php?Exporter=' + Company + '&Country=' + Country + '&Consignee=' + Consignee + '&ClearingAgent=' + ClearingAgentName + '&DateSelection=' + DateSelection + '&FromDate=' + fromDate + '&ToDate=' + toDate + '&Approval' + Approval + '&excelDownload=T';
                window.open(url, '_blank');
        }
    }
],
                            "fnStateSave": function (oSettings, oData) {
                                localStorage.setItem('key-datatable', JSON.stringify(oData));
                            },
                            "fnStateLoad": function (oSettings) {
                                return JSON.parse(localStorage.getItem('key-datatable'));
                            },
                            drawCallback: function () {
                                $(document).trigger('afterSearch');
                            },
                        });
                    }
                });

            }
        });
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

        function approveRecord($Poid){
            var Company = $('#drpCompany').val();
            var Country = $('#drpCountry').val();
            var Consignee = $('#drpConsignee').val();
            var ClearingAgentName = $('#drpClearingAgentName').val();
            var DateSelection = $('#drpDateSelection').val();
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            var Approval = $('#drpApprovalSelection').val();
            $.ajax({
                    url: 'Ajax/AjaxClearingRprtNew',
                    type: 'POST',
                    data: {
                        'PoId': $Poid,
                        'Exporter': Company,
                        // 'Country': Country,
                        'Consignee': Consignee,
                        'ClearingAgent': ClearingAgentName,
                        'DateSelection': DateSelection,
                        'FromDate': fromDate,
                        'ToDate': toDate,
                        'methodType': 'Approve',
                        'Approval': Approval
                    },
                    success: function (data) {
                        data = JSON.parse(data);
                        alert(data.Data.Data.Msg);
                        $("#POList").html(data.content);
                        var table = $('#key-datatable').DataTable({
                            scrollY: "600px",
                            scrollX: true,
                            //sScrollX: "100%",
                            sScrollXInner: "150%",
                            scrollCollapse: true,
                            paging: true,
                            destroy: true,
                            fixedColumns: true,
                            bSort: true,
                            order: [
                                [1, 'desc']
                            ],
                            "bStateSave": true,
                            dom: 'Blfrtip',
                            buttons: [
                                {
                                    text: 'Download Excel',
                                    className: 'btnExcelDataTable',
                                    action: function (e, dt, node, config) {
                                        var url = 'Ajax/AjaxClearingRprtNew.php?Exporter=' + Company + '&Country=' + Country + '&Consignee=' + Consignee + '&ClearingAgent=' + ClearingAgentName + '&DateSelection=' + DateSelection + '&FromDate=' + fromDate + '&ToDate=' + toDate + '&excelDownload=T';
                                            window.open(url, '_blank');
                                    }
                                }
                            ],
                            "fnStateSave": function (oSettings, oData) {
                                localStorage.setItem('key-datatable', JSON.stringify(oData));
                            },
                            "fnStateLoad": function (oSettings) {
                                return JSON.parse(localStorage.getItem('key-datatable'));
                            },
                            drawCallback: function () {
                                $(document).trigger('afterSearch');
                            },
                        });
                    }
                });
        }

    </script>

</body>

</html>