<?php
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$filterfromDates = "T";
$filterToDates = "T";
$filterButtomButton = "T";

$ProductionPlant = $_SESSION['UnitID'];
?>

<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>

    <style>
        .highlight {
            border: 2px solid red;
        }
        #tblDataTable {
    width: max-content !important; /* prevents squeezing */
    border-collapse: separate;
}

#tblDataTable th,
#tblDataTable td {
    white-space: nowrap;   /* stop text wrapping */
    min-width: 120px;      /* adjust per column */
}

.dataTables_scrollHeadInner,
.dataTables_scrollBody table {
    width: max-content !important;
}
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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php'; ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
    type="text/javascript"></script>

<script>
    $(document).ready(function () {
        var productionPlant = <?php echo $ProductionPlant ?>;

        if (productionPlant != 0) {
            $("#drpReceivingPlantId").prop('disabled', true);
            $("#drpReceivingPlantId").val(productionPlant).trigger('change');
        } else {
            $("#drpReceivingPlantId").prop('disabled', false);
            $("#drpReceivingPlantId").val("").trigger('change');
        }
    });

    function fnPoStatusReportExcel(tableID, filename = '') {
        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();

        $("#tblDataTable").find(".thead-light  tr").height("auto");
        $("#tblDataTable").find(".thead-light  tr th").height("auto");
        $("#tblDataTable").find(".thead-light  tr th div").height("auto");

        $("#tblDataTable").find("tfoot tr").height("auto");
        $("#tblDataTable").find("tfoot tr td").height("auto");
        $("#tblDataTable").find("tfoot tr th div").height("auto").css("font-weight", "bold");

        var downloadLink;
        var dataType = 'data:application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);

        var clonedTable = tableSelect.cloneNode(true);

        var reportNameRow = document.createElement('tr');
            reportNameRow.innerHTML = `
            <td colspan="20" style="text-align: center; font-weight: bold;">
            Kits Summary report 
            </td>`;
            clonedTable.querySelector('thead').insertBefore(reportNameRow, clonedTable.querySelector('thead').firstChild);


        var trs = clonedTable.getElementsByTagName('tr');
        for (var i = 0; i < trs.length; i++) {
            trs[i].style.border = '1px solid #eff2f7';
            var tds = trs[i].getElementsByTagName('td');
            for (var j = 0; j < tds.length; j++) {
                if (/^\d+\/\d+$/.test(tds[j].innerText)) {
                    tds[j].innerText = "'" + tds[j].innerText;
                    // tds[j].innerHTML = '<span style="mso-number-format:@">' + tds[j].innerText + '</span>';
                }
                tds[j].style.border = '1px solid #eff2f7';
            }
        }

        var hiddenCells = clonedTable.querySelectorAll('td[style*="display: none"]');
        hiddenCells.forEach(function (cell) {
            cell.style.visibility = 'hidden';
        });

        var tableHTML = clonedTable.outerHTML;

        filename = filename ? filename + '_FromDate: ' + fromDate + '_ToDate: ' + toDate + '.xls' : 'excel_data.xls';

        downloadLink = document.createElement('a');
        document.body.appendChild(downloadLink);

        if (navigator.msSaveOrOpenBlob) {
            var blob = new Blob(['\ufeff', tableHTML], {
                type: dataType
            });
            navigator.msSaveOrOpenBlob(blob, filename);
        } else {
            downloadLink.href = dataType + ', ' + encodeURIComponent(tableHTML);
            downloadLink.download = filename;
            downloadLink.click();
        }

        $("#tblDataTable").find(".thead-light  tr").height("0px");
        $("#tblDataTable").find(".thead-light  tr th").height("0px");
        $("#tblDataTable").find(".thead-light  tr th div").height("0px");

        $("#tblDataTable").find("tfoot tr").height("0px");
        $("#tblDataTable").find("tfoot tr td").height("0px");
        $("#tblDataTable").find("tfoot tr th div").height("0px");
    }

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
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Kits Summary Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                action: function () {
                    fnPoStatusReportExcel('tblDataTable', 'Kits Summary Report');
                }
            }],
        });
    }

    function ShowList() {
        $.ajax({
            url: 'Ajax/ajax-kits-summary-report.php',
            type: 'POST',
            data: {
                fromDate: $("#txtFromDate").val(),
                toDate: $("#txtToDate").val(),
                // searchKey: searchKey,               // Search key to filter the data
                // pageNo: pageNo,                     // Page number for pagination
                // loadAll: loadAll   
            },
            success: function (data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }

    $("#btnSearch").on("click", function () {

        $("#txtFromDate, #txtToDate").removeClass("highlight");

        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();

        if (fromDate == "" || toDate == "") {
            if (fromDate == "") {
                $("#txtFromDate").addClass("highlight");
            }
            if (toDate == "") {
                $("#txtToDate").addClass("highlight");
            }
            return false;
        }

        // var fromDate = $("#txtFromDate").val();
        // var toDate = $("#txtToDate").val();
        // var receivingPlantId = $("#drpReceivingPlantId").val();
        // if (!fromDate && !toDate) {
        //     alert('Select From and To Dates');
        // } else if (!receivingPlantId) {
        //     alert('Select Plant');
        // } else {
        ShowList();
        // }
    });
</script>

</body>

</html>
