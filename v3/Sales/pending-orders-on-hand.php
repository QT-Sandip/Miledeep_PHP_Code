<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterYear = "T";  // drpYear
$filterExporter = "T"; // drpExporterList
$filterConsignee = "T"; // drpConsigneeList
$filterCountry = "T"; // drpCountryList
$inrExchange = "T"; // txtINRExchange
$filterButButton = "T"; // btnSearch

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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <!-- <div class="card">
                            <div class="card-body">
                                <div class="text-center">
                                    <a href="currency-create" class="btn btn-success waves-effect waves-light">
                                        <i class="fa fa-plus"></i> Add Currency
                                    </a>
                                </div>
                            </div>
                        </div> -->

                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>


<script>
    $(document).ready(function() {
        
        function fnShowDataTable() {
            var table = $('#tblDataTable').DataTable({
                scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                bInfo: false,
                dom: 'Blfrtip',
                bSort: false,
                // dom: '<"top-left"Blfrtip>',
                buttons: [{
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'btnExcelDataTable btn-left-align',
                    title: 'Pending Order On Hand <?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        function ShowList() {
            $.ajax({
                url: 'Ajax/ajax-pending-order-on-hand.php',
                type: 'POST',
                data: {
                    year: $("#drpYear").val(),
                    exporterList: $("#drpExporterList").val(),
                    consigneeList: $("#drpConsigneeList").val(),
                    countryList: $("#drpCountryList").val(),
                    inrExchange: $("#txtINRExchange").val()
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    return false;
                }
            });
        }

        $("#btnSearch").on("click", function() {
            ShowList();
        });
    });
</script>

</body>

</html>s