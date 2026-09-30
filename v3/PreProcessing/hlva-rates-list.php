<?php

// phpinfo();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterPPCStation = "T";
$filterPPCType = "T";
$filterMonth = "T";
$filterYear1 = "T";
$HLVABottom = "T";
$filterShift = "T";

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

                                <!-- for validation -->
                                <input type="hidden" id="hdnSelectedRow" name="hdnSelectedRow" />
                                <!-- for validation -->

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
    paging: false,
    destroy: true,
    scrollCollapse: true,
    info: false,
    ordering: false,
    autoWidth: false,
    fixedColumns: true, // just to be explicit
            dom: 'Blfrtip',
            buttons:[],
        });
    }
    // Validations
    // $("#btnSearch").on("click", function() {
    //     $("#hdnSelectedRow").val("");
    //     var StationID = $("#drpStation").val();
    //     var ContractorType = $("#drpSpType").val();
    //     var Month = $("#drpMonth").val();
    //     var Year = $("#drpYear1").val();

    //     if (ContractorType == "" || StationID == "" || Month == "" || Year == "") {
    //         $(this).next().find('.select2-selection').addClass('error')
            // if (StationID == "") {
            //     $("#drpStation").next().find('.select2-selection').addClass('error');
            // } else {
            //     $("#drpStation").next().find('.select2-selection').removeClass('error');
            // }

            // if (ContractorType == "") {
            //     $("#drpSpType").next().find('.select2-selection').addClass('error');
            // } else {
            //     $("#drpSpType").next().find('.select2-selection').removeClass('error');
            // }

            // if (Month == "") {
            //     $("#drpMonth").next().find('.select2-selection').addClass('error');
            // } else {
            //     $("#drpMonth").next().find('.select2-selection').removeClass('error');
            // }

            // if (Year == "") {
            //     $("#drpYear1").next().find('.select2-selection').addClass('error');
            // } else {
            //     $("#drpYear1").next().find('.select2-selection').removeClass('error');
            // }
        // } else {
            // if (StationID != "" || ContractorType != "" || Month != "" || Year != "") {

            //     $('#drpStation').next().find('.select2-selection').removeClass('error');
            //     $('#drpSpType').next().find('.select2-selection').removeClass('error');
            //     $('#drpMonth').next().find('.select2-selection').removeClass('error');
            //     $('#drpYear1').next().find('.select2-selection').removeClass('error');

            //     ShowList();
            // }

    //     }
    // });

    // Validations
    function ShowList() {

        $.ajax({
            url: 'Ajax/ajax-hlva-rates-list.php',
            type: 'POST',
            data: {
                Month: $("#drpMonth").val(),
                Year: $("#drpYear1").val(),
                ContractorType: $("#drpSpType").val(),
                StationID: $("#drpStation").val(),
                Shift : $('#drpShift').val(),

            },
            success: function(data) {

                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }
    
    $("#btnSearch").on("click", function() {
        ShowList()

    });
</script>

</body>

</html>
