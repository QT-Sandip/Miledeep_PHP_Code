<?php
$PageTitle = "Pending Contracts";
$PageTitleFolder = "Sales";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include ($cp."Includes/IncludePage.php");


$uid = $_SESSION['r_id'];

$year=getCurrentFinancialYear();
$EndDate = date("Y-m-d");


$SqlExporter = "SELECT  ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1 ";
$StmtExporter = $iconn->query($SqlExporter);

$SqlProcessor = "SELECT  ExpID,ExpApprovalNo FROM `AdminExporterDetails` where ExpType=2 ";
$StmtProcessor = $iconn->query($SqlProcessor);

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
                                        
                                        <div class="col-lg-3">
                                            <label>Company</label>

                                            <select class="form-control select2" name="drpExporter"
                                                id="drpExporter">

                                                <option value=" ">All</option>
                                                <?php while ($RowExporter = $StmtExporter->fetch_array()) {

                                                    ?>

                                                    <option Value="<?php echo $RowExporter['ExpID']; ?>"><?php echo $RowExporter['ExpName']; ?></OPTION>

                                                <?php } ?>
                                               
                                                </select>

                                               
                                        </div> 
                                        
                                        <div class="col-lg-3">
                                            <label>Processor </label>

                                            <select class="form-control select2" name="drpProcessor"
                                                id="drpProcessor">

                                                <option value=" ">All</option>
                                                <?php while ($RowProcessor = $StmtProcessor->fetch_array()) {

                                                    ?>

                                                    <option Value="<?php echo $RowProcessor['ExpID']; ?>"><?php echo $RowProcessor['ExpApprovalNo']; ?></OPTION>

                                                <?php } ?>
                                               
                                                </select>


                                        </div>





                                    </div>


                                    <div align="center" class="col-lg-12">

                                        <button id="list" align="left" type="button"
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

</body>









<script>



    $('#list').click(function () {

        getList();



    });




    function getList() {

        var Processor = $('#drpProcessor').val();
        var Exporter = $('#drpExporter').val();
        var FromDate = $('#fromDate').val();
        var ToDate = $('#toDate').val();
       

        $.ajax({
            url: 'Ajax/AjaxQCertificateReport',
            type: 'POST',
             data: { 'Processor': Processor, 'Exporter': Exporter, 'FromDate': FromDate, 'ToDate': ToDate},
            success: function (data) {
                showData(data);

            }
        });
        //});
    }
</script>

<script>

    function showData(data) {

        $("#POList").html(data);
        var table = $('#key-datatable').DataTable({
            scrollY: "600px",
            scrollX: true,
            //sScrollX: "100%",
            sScrollXInner: "100%",
            // scrollCollapse:true,
            paging: true,
            destroy: true,
            fixedColumns: true,
            bSort: true,
            order: [[1, 'desc']],
             dom: 'Blfrtip',
            buttons: [{extend: 'excel', text: 'Download Excel', className: 'btnExcelDataTable',title: 'Q-Certificate Report', footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
            "bStateSave": true,
            "fnStateSave": function (oSettings, oData) {
                localStorage.setItem('key-datatable', JSON.stringify(oData));
            },
            "fnStateLoad": function (oSettings) {
                return JSON.parse(localStorage.getItem('key-datatable'));
            }
        });

    }

</script>