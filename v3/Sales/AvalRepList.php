<?php
$PageTitle = "Available Reports List";
$PageTitleFolder = "Sales";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");

$uid = $_SESSION['r_id'];

$PoID = $_GET['PoID'];

$StartDate = date("Y-m-d", strtotime("yesterday"));
$EndDate = date("Y-m-d");

$AccessType = $_SESSION["ACCESS_TYPE"];
$DepartId = $_SESSION["D_ID"];


if ($AccessType == '1') {
    $SqlAvalRep = "SELECT * FROM `ScrmDateWiseReports` where ACCESS_TYPE >= 1 and AdminAccess=1 and RepStatus=2 ORDER BY SortOrder,REPORT_NAME ASC";
} else if ($AccessType == '2') {
    $SqlAvalRep = "SELECT * FROM `ScrmDateWiseReports` where DEPARTMENT_ID='$DepartId' and ACCESS_TYPE >= 2 and AdminAccess=1 and RepStatus=2 ORDER BY SortOrder,REPORT_NAME ASC";
} else {
    $SqlAvalRep = "SELECT * FROM `ScrmDateWiseReports` where DEPARTMENT_ID='$DepartId' and ACCESS_TYPE = 3 and AdminAccess=1 and RepStatus=2 ORDER BY SortOrder,REPORT_NAME ASC";
}


$SqlAvalRep = "SELECT * FROM `ScrmDateWiseReports` where  RepStatus=1 ORDER BY SortOrder asc ";
$StmtRep = $iconn->query($SqlAvalRep);

$SqlSelect = "SELECT * FROM `AdminExporterDetails`  Where ExpType=1";
//$SqlSelect="SELECT DISTINCT ExpName FROM `$TblAdminExporterDetails`";
$StmtSelect = $iconn->query($SqlSelect);

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


                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">




                                    <div class="row">




                                        <div class="col-lg-3">
                                            <label>Select Exporter: </label>

                                            <select class="form-control select2" name="Exporter" id="Exporter">
                                                <option value=" ">All</option>

                                                <?php while ($RowSelect = $StmtSelect->fetch_array()) {

                                                ?>

                                                    <option Value="<?php echo $RowSelect['ExpID']; ?>"><?php echo $RowSelect['ExpName']; ?></OPTION>

                                                <?php } ?>
                                                <select>


                                        </div>



                                        <div class="col-lg-3">
                                            <label>Select From Date: </label>
                                            <input type='date' value='<?php echo $StartDate; ?>' class="form-control" name="Fromdate" id="Fromdate"></input>
                                        </div>
                                        <div class="col-lg-3">
                                            <label>Select To Date: </label>
                                            <input type='date' value='<?php echo $EndDate; ?>' class="form-control" name="ToDate" id="ToDate"></input>
                                        </div>

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

                        <!--  <section class="content">-->

                        <div class="modal fade" id="myModal" role="dialog">
                            <div class="modal-dialog">
                                <!-- Modal content-->

                            </div>
                        </div>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body" id="ShowDataTable">

                                            <table id="key-datatable" data-paging="false" class="table table-hover table-bordered">
                                                <thead class="thead-light">
                                                    <th>Report Name</th>
                                                    <th>Actions</th>


                                                </thead>
                                                <tbody>

                                                    <?php

                                                    while ($Row = $StmtRep->fetch_array()) {


                                                    ?>

                                                        <tr>
                                                            <td><?php echo $Row['ReportName'] ?></td>

                                                            <td><button id="LotList" value="<?php echo  "/v3/Sales/" . $Row['ReportLink'] ?>" class="btn btn-link text-primary" title="View"><i class="mdi mdi-eye font-size-16">
                                                                    </i></button>

                                                            <button id="Download" value="<?php echo  "/v3/Sales/" . $Row['ReportExcel'] ?>" class="btn btn-link text-success" title="Download"><i class="mdi mdi-download font-size-16">
                                                                    </i></button>



                                                            </td>



                                                        </tr>

                                                    <?php $c++;
                                                    } ?>

                                                </tbody>

                                            </table>



                                        </div> <!-- end card body-->
                                    </div> <!-- end card -->
                                </div>
                            </div>
                        </div>
                    </div><!-- end col-->
                </div>
                <!-- </section>-->
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
    <?php // include($IncludesPath . "ShowDataTable.php"); 
    ?>

    <script>       

            function showData(data, sort) {
                $("#ShowDataTable").html(data);
                var table = $('#key-datatable').DataTable({
                    scrollY: "600px",
                    scrollX: true,
                    sScrollXInner: "100%",
                    paging: true,
                    destroy: true,
                    fixedColumns: true,
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
    </script>



<script type="text/javascript">
    $('document').ready(function() {
        showData();
        $(document).on('click', '#LotList', function(e) {


            var from_date = $('#Fromdate').val();
            //	var to_date=$('#TO_DATE').val();
            var to_date = $('#ToDate').val();
            var Link = $(this).val();
            var Exporter = $('#Exporter').val();
            //	alert(Link);


            var dynamic_link = Link + '?from_date=' + from_date + '&' + 'to_date=' + to_date + '&' + 'Exporter=' + Exporter;
            window.open(dynamic_link);

        });

        $(document).on('click', '#Download', function(e) {


            var from_date = $('#Fromdate').val();
            //	var to_date=$('#TO_DATE').val();
            var to_date = $('#ToDate').val();
            var Link = $(this).val();
            var Exporter = $('#Exporter').val();
            //	alert(Link);


            var dynamic_link = Link + '?from_date=' + from_date + '&' + 'to_date=' + to_date + '&' + 'Exporter=' + Exporter;
            window.open(dynamic_link);

        });






    });
</script>





<script>
    if ($('[type="date"]').prop('type') != 'date') {
        $('[type="date"]').datepicker();
    }
</script>


</body>


</html>