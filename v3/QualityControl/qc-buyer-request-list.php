<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterProductionPlant = "T";
$filterConsigneeOne = "T";
$filterYear = "T";
$filterButtonTwoForQc = "T";

$ProductionPlant = $_SESSION['UnitID'];
?>
<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <style>
        /* .select2-container--open {
                z-index: 9999999
            }             */
    </style>

</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . '/includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . '/includes/page-header.php' ?>
                <?php include_once WORKING_MODULE . '/includes/page-filter.php'
                    ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">
                                    <!--  -->
                                </div>
                                <input type="hidden" id="hdnActionType" />
                            </div>

                        </div>

                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <?php include_once ROOT . '/includes/footer.php' ?>

    </div>
    <!-- end main content-->
    <!-- Modal -->

</div>
<!-- END layout-wrapper -->




<?php include_once ROOT . '/includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
    type="text/javascript"></script>


<script>
    $(document).ready(function () {

        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpArrivalPlant").prop('disabled', true);
            $("#drpArrivalPlant").val(productionPlant).trigger('change');
        } else {
            $("#drpArrivalPlant").prop('disabled', false);
            $("#drpArrivalPlant").val("").trigger('change');
        }
    });

    $("#btnSearch").on("click", function () {
        ShowList();
    });
    $("#btnPrint").on("click", function () {
        var arrivalPlant = $("#drpArrivalPlant").val();
        if (arrivalPlant != '') {
            var arrivalPlantName = $("#drpArrivalPlant option:selected").text();
        } else {
            var arrivalPlantName = '';
        }
        var year = $("#drpYear").val();
        var consigneeList = $("#drpConsigneeList").val();

        var link = 'Reports/D1/buyer-request?ProductionPlant=' + arrivalPlant + '&Year=' + year + '&BuyerID=' + consigneeList + '&ArrivalPlantName=' + arrivalPlantName;
        window.open(link, '_blank');
    });

    function ShowList() {
        // alert($("#drpConsigneeList").val());
        $.ajax({
            url: 'Ajax/ajax-qc-buyer-request-list.php',
            type: 'POST',
            data: {
                arrivalPlant: $("#drpArrivalPlant").val(),
                year: $("#drpYear").val(),
                consigneeList: $("#drpConsigneeList").val(),
            },
            success: function (data) {
                $("#divDataList").html(data);
                fnShowDataTable();
            }
        });
    }

    function fnShowDataTable() {
        // Destroy the existing DataTable instance if it exists
        if ($.fn.dataTable.isDataTable('#tblDataTable')) {
            $('#tblDataTable').DataTable().clear().destroy();
        }

        // Initialize the DataTable
        $('#tblDataTable').DataTable({
            ordering: false,
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            fixedColumns: true,
            scrollCollapse: true,
            bInfo: false,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Qc Buyer Request List <?php echo date("d-m-Y"); ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }]
        });
    }

</script>

</body>

</html>