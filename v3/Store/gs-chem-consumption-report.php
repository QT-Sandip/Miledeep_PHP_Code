<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterProPlants = "T";
$filterChemical = "T";
$filterfromDateTwo = "T";
$filtertoDate = "T";
$chemConsumptionButton = "T";

$PlantID = $_SESSION['UnitID'];

?>

<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>
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

                        <div class="card">
                            <div class="card-body">


                                <div id="divDataList">
                                    <!-- -->
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
    function fnShowDataTable() {
        var chemical = $("#drpChemical option:selected").text();

        table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bInfo: false,
            bSort: false,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: 'Chemical Consumption Report_' + chemical,
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
        let ReceivedBagsTotals = 0,QuantityReceivedTotals = 0,SendBagsTotals = 0,SendQtyTotals = 0,BagsUsedTotals = 0;
        let QuantityUsedTotals = 0,OpeningBagsTotals = 0,OpeningKGsTotals = 0,ClosingBagsTotals = 0,ClosingKGsTotals = 0;
        //parseFloat
        table.on('draw.dt', function () {
            getTotals()
        })
        
    }
    function getTotals() {
        var totalClassArr = [
            'ReceivedBagsTotals',
            'QuantityReceivedTotals',
            'SendBagsTotals',
            'SendQtyTotals',
            'BagsUsedTotals',
            'QuantityUsedTotals',
            'OpeningBagsTotals',
            'OpeningKGsTotals',
            'ClosingBagsTotals',
            'ClosingKGsTotals'
        ];

        $.each(totalClassArr, function (index, className) {
            let newName = className.replace('Totals', '');
            let totals = 0;

            $('.' + newName).each(function () {
                let value = parseFloat($(this).text().replace(/,/g, ''));

                if (!isNaN(value)) {
                    totals += value;
                }
            });
            $('#' + className).text(
                totals.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })
            );
        });
    }


    function productionPlant() {
        var productionPlant = <?php echo $PlantID ?>;
        if (productionPlant != 0) {
            $("#drpProPlant").prop('disabled', true);
            $("#drpProPlant").val(productionPlant).trigger('change');
        } else {
            $("#drpProPlant").prop('disabled', false);
            $("#drpProPlant").val("").trigger('change');
        }
    }

    function fnSuccess(data) {
        $("#divDataList").html(data);
        fnShowDataTable();
    }

    function ShowList() {
        $.ajax({
            url: 'Ajax/ajax-gs-chemConsumption-report.php',
            type: 'POST',
            data: {
                proPlant: $("#drpProPlant").val(),
                chemical: $("#drpChemical").val(),
                fromDate: $("#txtFromDate").val(),
                toDate: $("#txtToDate").val(),
            },
            success: function(data) {
                fnSuccess(data);
                return false;
            }
        });
    }

    $(document).ready(function() {
        productionPlant();
    });

    $("#btnSearch").on("click", function() {
        var chemical = $("#drpChemical").val()
        if (chemical == "") {
            alert('Select Chemical');
        } else {
            ShowList();
        }
    });
$("#drpProPlant").on("change", function () {
    $('#tblDataTable').DataTable().destroy();
    $("#divDataList").html("");
});
</script>

</body>

</html>
