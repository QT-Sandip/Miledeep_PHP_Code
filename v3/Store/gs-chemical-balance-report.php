<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterProPlants = "T";
$filterChemical = "T";
$filterYear = "T";
$filterMonth = "T";
$filterfromDateTwo = "T";
$filtertoDate = "T";

$ReportButton = "T";

$PlantID = $_SESSION['UnitID'];

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
    type="text/javascript"></script>
<!-- SheetJS Library -->
        <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
    var ChemPoID = "<?php echo isset($_GET["ChemPoID"]) && !empty($_GET["ChemPoID"]) ? $_GET["ChemPoID"] : "" ?>";

    function fnShowDataTable() {

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
                title: 'Chemical Report',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }

    function fnSuccess(data) {
        $("#divDataList").html(data);
        fnShowDataTable();
    }

    function ShowList() {
        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();
        var year = $("#drpYear").val();
        var drpMonth = $('#drpMonth').val();
        if(drpMonth){
            if(!year){
                alert('Plese Select Year First.')
                return false;
            }
        }
        if (!drpMonth && (!fromDate || !toDate)) {
            alert("Please Choose Date Range / Month");
            return false;
        }

        // if(year != ''){
        //     const startYear = parseInt(year.split('-')[0]);
        //     fromDate = `${startYear}-04-01`;
        //     toDate = `${startYear + 1}-03-31`;
        //     $("#txtFromDate").val(fromDate);
        //     $("#txtToDate").val(toDate);
        // }

        $.ajax({
            url: 'Ajax/ajax-gs-chemical-openig-report.php',
            type: 'POST',
            data: {
                proPlant: $("#drpProPlant").val(),
                fromDate: fromDate,
                toDate: toDate,
                chemical: $("#drpChemical").val(),
                Month: drpMonth,
                year: year,
            },
            success: function (data) {
                fnSuccess(data);
                return false;
            }
        });
    }

    // $(document).on("change", "#drpYear", function () {
    //     const yearVal = $(this).val();

    //     if (yearVal !== "") {
    //         $("#txtFromDate,#txtToDate").val("").trigger("change");
    //     } else {
    //         const today = new Date().toISOString().split('T')[0];

    //         if (!$("#txtFromDate").val()) {
    //             $("#txtFromDate").val(today).trigger("change");
    //         }

    //         if (!$("#txtToDate").val()) {
    //             $("#txtToDate").val(today).trigger("change");
    //         }
    //     }
    // });
    $(document).on("change", "#drpYear", function () {
    const yearVal = $(this).val();

    if (yearVal) {
        let startYear, endYear;

        if (yearVal.includes('-')) {
            const parts = yearVal.split('-');
            startYear = parseInt(parts[0]);
            endYear = parseInt('20' + parts[1]);
        } else {
            startYear = parseInt(yearVal);
            endYear = startYear + 1;
        }

        const fromDate = `${startYear}-04-01`;
        const toDate = `${endYear}-03-31`;

        $("#txtFromDate").val(fromDate);
        $("#txtToDate").val(toDate);
    }
});


    $(document).on("change", "#txtFromDate", function () {
        const fromVal = $(this).val();

        if (fromVal !== "") {
            $("#drpYear").val("").trigger("change").rules("remove");
        }
    });

    $(document).ready(function () {
        $('#drpMonth').on('change', function () {

            $("#txtFromDate,#txtToDate").prop('readonly', false);

            if ($(this).val()) {
                $("#txtFromDate,#txtToDate").prop('readonly', true);
            }
        });

        var productionPlant = <?php echo $PlantID ?>;
        if (productionPlant != 0) {
            $("#drpProPlant").prop('disabled', true);
            $("#drpProPlant").val(productionPlant).trigger('change');
        } else {
            $("#drpProPlant").prop('disabled', false);
            $("#drpProPlant").val("").trigger('change');
        }

        ShowList();

        $(document).on('click', '#btnExportExcel', function () {
            const tables = document.querySelectorAll('#chemicalReportWrapper table');
            const wb = XLSX.utils.book_new();

            tables.forEach((table, index) => {
                const ws = XLSX.utils.table_to_sheet(table);
                const monthHeading = table.querySelector('h5')?.innerText || `Sheet${index + 1}`;
                XLSX.utils.book_append_sheet(wb, ws, monthHeading.replace(/\W+/g, '_'));
            });

            XLSX.writeFile(wb, 'Chemical_Report.xlsx');
        });
    });

    $("#btnSearch").on("click", function () {
        ShowList();
    });
$("#drpProPlant").on("change", function () {
    $('#tblDataTable').DataTable().destroy();
    $("#divDataList").html("");
});
</script>

</body>

</html>
