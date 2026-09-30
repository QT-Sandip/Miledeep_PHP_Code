<?php
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$filterFromDate = "T";
$filterToDate = "T";
$filterReceivingPlant = "T";
$filterButButton = "T";

$ProductionPlant = $_SESSION['UnitID'];
$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;
?>

<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>

    <style>
        .highlight {
            border: 2px solid red;
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
<script>
    $(document).ready(function () {
        var productionPlant = <?php echo $ProductionPlant ?>;
        var IsPlantFilter = <?php echo $IsPlantFilter ?>;
        console.log("productionPlant ",productionPlant ," IsPlantFilter ", IsPlantFilter)
        if (productionPlant != 0 || IsPlantFilter == 0) {
            $("#drpReceivingPlantId").prop('disabled', true);
            if(IsPlantFilter == 0){
                $("#drpReceivingPlantId").val(productionPlant);
            }else{
            $("#drpReceivingPlantId").val(productionPlant).trigger('change');
            }
        } else {
            $("#drpReceivingPlantId").prop('disabled', false);
        }
        // if (productionPlant != 0) {
        //     $("#drpReceivingPlantId").prop('disabled', true);
        //     $("#drpReceivingPlantId").val(productionPlant).trigger('change');
        // } else {
        //     $("#drpReceivingPlantId").prop('disabled', false);
        //     $("#drpReceivingPlantId").val("").trigger('change');
        // }
    });

    async function fnPoStatusReportExcel(tableID, filename = '') {
        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();

        $("#tblDataTable").find(".thead-light  tr").height("auto");
        $("#tblDataTable").find(".thead-light  tr th").height("auto");
        $("#tblDataTable").find(".thead-light  tr th div").height("auto");

        $("#tblDataTable").find("tfoot tr").height("auto");
        $("#tblDataTable").find("tfoot tr td").height("auto");
        $("#tblDataTable").find("tfoot tr th div").height("auto").css("font-weight", "bold");

        const tableHeading = `Consolidation Daily and Monthly Report ( ${fromDate} - ${toDate} )`;
        
        // Initialize workbook and worksheet
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet("Report");

        // Add and style heading
        worksheet.addRow([tableHeading]);
        const headingRow = worksheet.getRow(1);
        headingRow.font = { bold: true, size: 12 };
        headingRow.alignment = { horizontal: "center", vertical: "middle" };

        // Process table rows
        const table = document.getElementById(tableID).cloneNode(true);
        const rows = table.getElementsByTagName("tr");
        const numColumns = rows[2]?.children?.length ?? 11;; // Adjust based on your table's number of columns
        worksheet.mergeCells(1, 1, 1, numColumns);
        const ws_data = [];
        // let grandTotalsRow = null;

        for (let row = 0; row < rows.length; row++) {
            const cells = rows[row].children;
            const rowData = [];

            for (let col = 0; col < cells.length; col++) {
                const cell = cells[col];
                rowData.push(cell.innerText.trim());
                
                if(row == 0 && col > 4)rowData.push("", "");
            }
            
            if (row == 1) {
                ws_data.push(["","","","","",...rowData]);
            } else{
                ws_data.push(rowData);
            }
           
        }
        // if (grandTotalsRow) ws_data.push(grandTotalsRow);
        // Add data rows to the worksheet
        ws_data.forEach((row, idx) => {
            const addedRow = worksheet.addRow(row);
            // Enable word wrapping
            addedRow.eachCell((cell) => {
                cell.alignment = { wrapText: true, vertical: 'middle' }; 
            });

            // Format columns H to O as numbers, starting from the 4th row
            if (idx >= 1) {
                for (let col = 2; col < numColumns; col++) {
                    if(col != 3 ){
                        const cell = addedRow.getCell(col + 1); // 1-based index
                        if (cell.value) {
                            // Remove commas and convert to number
                            const cleanedValue = parseFloat(cell.value.toString().replace(/,/g, ''));
                            if (!isNaN(cleanedValue)) {
                                cell.value = cleanedValue;
                                cell.numFmt = '#,##0.00'; // Numeric format
                            }
                        }
                    }
                }
            }
        });

         // Make rows up to row 3 bold
         for (let i = 2; i <= 3; i++) {
            const row = worksheet.getRow(i);
            row.eachCell((cell) => {
                cell.font = { bold: true };
            });
        }

       // Merging rows 1-2 for columns 1-5 (row span)
        for (let startColumn = 1; startColumn <= 5; startColumn++) {
            // Check if the cells are already merged before attempting to merge
            if (!worksheet.getCell(2, startColumn).isMerged) {
                worksheet.mergeCells(2, startColumn, 3, startColumn); // Merge row 1 to row 2 for the current column

                // Access the top cell of the merged region
                const cell = worksheet.getCell(1, startColumn);

                // Style the merged cell
                cell.alignment = { horizontal: 'center', vertical: 'middle' }; // Center alignment
                cell.font = { bold: true }; // Bold text
            }
        }

        // Merging columns 6 onward with a span of 3 (on row 2)
        let startColumn = 6;
        while (startColumn <= numColumns) {
            const endColumn = startColumn + 2; // Merge 3 columns
            if (endColumn <= numColumns) {
                // Check if the cells are already merged before attempting to merge
                if (!worksheet.getCell(2, startColumn).isMerged) {
                    worksheet.mergeCells(2, startColumn, 2, endColumn);
                    const cell = worksheet.getCell(2, startColumn);

                    // Style the merged cell
                    cell.alignment = { horizontal: 'center', vertical: 'middle' }; // Center alignment
                    cell.font = { bold: true }; // Bold text
                }
            }
            startColumn += 3; // Move to the next set of 3 columns
        }


        // Adjust column widths with a maximum cutoff
        worksheet.columns = new Array(numColumns).fill({ width: 10 });
        worksheet.columns.forEach((column) => {
            let maxWidth = 15; // Maximum width cutoff
            column.eachCell({ includeEmpty: true }, (cell) => {
                const cellValue = cell.value ? cell.value.toString() : '';
                maxWidth = Math.min(Math.max(maxWidth, cellValue.length + 2), 20); // Adjust width dynamically with cutoff at 30
            });
            column.width = maxWidth;
        });

        // Export the workbook
        filename = filename ? `${filename}_From_${fromDate}_To_${toDate}.xlsx` : 'Consolidation_Daily_and_Monthly_Report.xlsx';
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
        const url = URL.createObjectURL(blob);

        // Trigger file download
        const link = document.createElement("a");
        link.href = url;
        link.download = filename;
        link.click();
        URL.revokeObjectURL(url);

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
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            scrollCollapse: true,
            bInfo: false,
            bSort: false,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Consolidation Daily and Monthly Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                action: function () {
                    fnPoStatusReportExcel('tblDataTable', 'Consolidation Daily and Monthly Report');
                }
            }],
        });
    }

    function ShowList() {
        $.ajax({
            url: 'Ajax/ajax-consolidation-report-daily-and-monthly.php',
            type: 'POST',
            data: {
                fromDate: $("#txtFromDate").val(),
                toDate: $("#txtToDate").val(),
                receivingPlantId: $("#drpReceivingPlantId").val(),
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
$("#drpReceivingPlantId").on("change", function () {
    $('#tblDataTable').DataTable().destroy();
    $("#divDataList").html("");
});
</script>

</body>

</html>
