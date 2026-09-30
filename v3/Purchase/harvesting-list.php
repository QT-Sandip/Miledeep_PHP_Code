<?php

// phpinfo();
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$HarvestFormNewDesignForSME = !empty($ClientConfigData->HarvestFormNewDesignForSME)?$ClientConfigData->HarvestFormNewDesignForSME:'';//SMEV3

$filterBookingDate = "T";
$buttonButtom = "T";
$filterPsId = "T";

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
            dom: 'Blfrtip',

            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: 'harvest-list',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }]
        });
        table.on('draw.dt', function () {
            let materialTotalQuantity = 0;
            let materialTotalCrates = 0;
            let withoutNetQty = 0;
            $('.material-total-quantity').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    materialTotalQuantity += weight;
                }
            });
            $('.material-total-crates').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    materialTotalCrates += weight;
                }
            });
            $('.without-netqty').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    withoutNetQty += weight;
                }
            });


            $('#TotalWithOutNetQty').text(withoutNetQty.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });
    }


    function ShowList() {

        $.ajax({
            url: 'Ajax/ajax-harvesting-list.php',
            type: 'POST',
            data: {

                // PsId: $("#drpPurchaseList").val(),
                bookingDate: $("#txtBookingDate").val(),
                purchaseCenters: $("#drpCenter").val(),
                'HarvestFormNewDesignForSME' :"<?php echo $HarvestFormNewDesignForSME;?>"
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