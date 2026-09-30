<?php

// phpinfo();
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterPsId = "T";
$filterBookingDate = "T";
$filterArrivalPlant = "T";
$buttonButtom = "T";
// $buttonRight = "T";

$PlantId = $_SESSION['UnitID'];
// echo $PlantId;
// exit;
?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . 'includes/head.php' ?>

</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . 'includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . 'includes/page-header.php' ?>
                <?php include_once WORKING_MODULE . 'includes/page-filter.php'; ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <div class="text-center">
                                    <a href="create-booking" class="btn btn-success waves-effect waves-light">
                                        <i class="fa fa-plus"></i> New Bookings
                                    </a>
                                </div>

                                <div id="divDataList">
                                    <!-- -->
                                </div>

                            </div>
                        </div>


                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <?php include_once ROOT . 'includes/footer.php' ?>

    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . 'includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . 'includes/app-js.php' ?>

<script>
    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
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
        });

        table.on('draw.dt', function () {
            let totalWeight = 0;
            $('.booking-weight').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    totalWeight += weight;
                }
            });

            $('#total-booking-weight').text(totalWeight.toLocaleString('en-US', {
                minimumFractionDigits: 3,
                maximumFractionDigits: 3
            }));
        });
    }

    // $(document).ready(function() {
    //     var plantId = <?php echo $PlantId ?>;

    //     if (plantId != 0) {
    //         $("#drpArrivalPlant").prop('disabled', true);
    //         $("#drpArrivalPlant").val(plantId).trigger('change');
    //     } else {
    //         $("#drpArrivalPlant").prop('disabled', false);
    //         $("#drpArrivalPlant").val("").trigger('change');
    //     }
    // });

    function ShowList() {

        $.ajax({
            url: 'Ajax/AjaxBookingList.php',
            type: 'POST',
            data: {

                PsId: $("#drpPurchaseList").val(),
                BookingDate: $("#txtBookingDate").val(),
                ArrivalPlant: $("#drpArrivalPlant").val(),
            },
            success: function (data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }
    $(document).ready(function () {
        ShowList();
    });

    $("#btnSearch").on("click", function () {
        ShowList();
    })
</script>

</body>

</html>
