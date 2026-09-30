<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$PageTitle = "Purchase Rates";
$PageTitleFolder = "Purchase";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

include_once ROOT . 'config/api-call.php';


$api = API_RM_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");
$resSpecies = $resDropdownsAPI->Data->Species;

$SqlDeptList = "SELECT * FROM $RmPurchaseStations";
$StmtDept = $iconn->query($SqlDeptList);

// $Token = $_SESSION['Token'];
// echo $Token;
// exit;
?>

<!doctype html>
<html lang="en">


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
                                        <div class="col col-md-3 mb-3">

                                            <label for="accessType">Species <span style="color:red">*</span></label>
                                            <select class="select2 form-control" id="Type" name="Type">
                                                <option value="">Select Species</option>
                                                <?php
                                                foreach ($resSpecies as $item)
                                                    echo '<option value="' . $item->ShortCode . '">' . $item->SpeciesName . '(' . $item->ShortCode . ')'  . '</option>';
                                                ?>
                                            </select>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="accessType">Month <span style="color:red">*</span></label>

                                                <select class="form-control select2" name="Month" id="Month">
                                                    <?php
                                                    $start = strtotime('first day of this month');
                                                    for ($i = 0; $i < 12; $i++) {
                                                        $time = strtotime(sprintf('%d months', $i), $start);

                                                        $label = date('F', $time);
                                                        $value = date('m', $time);
                                                        echo "<option value='$value'>$label</option>";
                                                    }

                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="accessType">Year <span style="color:red">*</span></label>

                                                <select class="form-control select2" name="Year" id="Year">
                                                    <?php for ($year; $Startyear <= $Currentyear; $Startyear++) { ?>
                                                        <option value="<?php echo $Startyear ?>" <?php if ($Startyear == $Currentyear) {
                                                                                                        echo 'selected="selected"';
                                                                                                    } ?>>
                                                            <?php echo $Startyear ?></option>
                                                    <?php  } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div align="center" class="col-lg-12">
                                            <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                            <a href="add-purchase-rates-m1" class="btn btn-success waves-effect waves-light">
                                                <i class="fa fa-plus"></i> Create Rm Rates
                                            </a>
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
                                <div class="card-body" id="PurchaseRatesList">


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

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="copyModal" tabindex="-1" aria-labelledby="copyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="copyModalLabel">Select Date for Copy</h5>
                    <button type="button" class="btn btn-text" data-bs-dismiss="modal" aria-label="Close"><i class="mdi mdi-close font-size-18"></i></button>
                </div>
                <div class="modal-body">
                    <form id="copyForm">
                        <div class="mb-3">
                            <label for="copyDate" class="form-label">Select Date</label>
                            <input type="date" class="form-control" id="copyDate" required>
                        </div>
                        <input type="hidden" id="copyDateId">
                        <input type="hidden" id="copyStype">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="confirmCopy">Copy</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModel" tabindex="-1" aria-labelledby="deleteModelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModelLabel">Delete Rm Price</h5>
                    <button type="button" class="btn btn-text" data-bs-dismiss="modal" aria-label="Close"><i class="mdi mdi-close font-size-18"></i></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this Rm Price?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <?php include($IncludesPath . "vendor-scripts.php"); ?>

    <!-- Bootstrap & jQuery Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Required datatable js -->
    <script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/metisMenu/3.0.7/metisMenu.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/metisMenu/3.0.7/metisMenu.min.css">
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

    <!-- Datatable init js -->
    <script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>

    <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>


</body>

</html>

<script>
    $('document').ready(function() {

        $(document).on('click', '#list', function() {
            var Type = $('#Type').val();
            if (Type != '') {
                getData();
            } else {
                alert('Select Species');
            }
        });


        $(document).on('click', '#copy-btn', function() {

            let dateId = $(this).data('date-id');
            let stype = $(this).data('stype');

            $('#copyDateId').val(dateId);
            $('#copyStype').val(stype);

            $('#copyModal').modal('show');
        });

        $(document).on('click', '#confirmCopy', function() {

            let date = $('#copyDate').val();
            let dateId = $('#copyDateId').val();
            let stype = $('#copyStype').val();

            if (!date) {
                alert("Please select a date.");
                return;
            }
            $.ajax({
                url: 'Ajax/ajax-add-purchase-rates-m1.php',
                type: 'POST',
                data: {
                    'submissionDate': date,
                    'DateId': dateId,
                    'Stype': stype,
                    'methodType': 'copy'
                },
                success: function(data) {
                    console.log(data);
                    $('#copyModal').modal('hide');
                    setTimeout(() => {
                        getData()
                    }, 200);
                    setTimeout(() => {
                        alert(data?.Msg);
                    }, 500);

                }
            });
        });

        $(document).on('click', '#delete-btn', function() {

            let dateId = $(this).data('date-id');
            let stype = $(this).data('stype');

            $('#copyDateId').val(dateId);
            $('#copyStype').val(stype);

            $('#deleteModel').modal('show');
        });

        $(document).on('click', '#confirmDelete', function() {

            let dateId = $('#copyDateId').val();
            let stype = $('#copyStype').val();

            $.ajax({
                url: 'Ajax/ajax-add-purchase-rates-m1.php',
                type: 'POST',
                data: {
                    'DateId': dateId,
                    'Stype': stype,
                    'methodType': 'delete'
                },
                success: function(data) {
                    console.log(data);
                    $('#deleteModel').modal('hide');
                    setTimeout(() => {
                        getData()
                    }, 200);
                    setTimeout(() => {
                        alert(data?.Msg);
                    }, 500);

                }
            });
        });


    });

    function getData() {

        var MONTH = $('#MONTH').val();
        var PsId = $('#PurchaseStationID').val();
        var Year = $('#Year').val();
        var Month = $('#Month').val();
        var Type = $('#Type').val();

        $.ajax({
            url: 'Ajax/AjaxRmRatesHis.php',
            type: 'POST',
            data: {
                'PsId': PsId,
                'Month': Month,
                'Year': Year,
                'Type': Type
            },
            success: function(data) {
                showData(data);
            }
        });
    }

    function showData(data) {

        $("#PurchaseRatesList").html(data);

        var table = $('#key-datatable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "150%",
            paging: true,
            destroy: true,
            fixedColumns: true,
            bSort: false,
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
            buttons:[],
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
