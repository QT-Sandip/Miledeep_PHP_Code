<?php
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// $filterFromDate = "T";
$filterToDate = "T";
// $filterReceivingPlant = "T";
$filterChillRoomPreparation = "T";
// $filterProductType = "T";
$filterButButton = "T";

$ProductionPlant = $_SESSION['UnitID'];
$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;

if ($IsPlantFilter == 1) {
    $filterReceivingPlant = "T";
}
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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php'; ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <button id="btnDownloadExcel" class="btn btn-success" align="right"
                                    style="display: none;">Download
                                    Excel</button>
                                <div id="divDataList">
                                </div>

                                <input type="hidden" id="hdnActionType" />
                                <?php
                                if ($IsPlantFilter == 0) {
                                    echo '<input type="hidden" id="drpReceivingPlantId" />';
                                }
                                ?>
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
<script src="https://cdn.jsdelivr.net/npm/datatables.net-fixedcolumns@4.3.0/js/dataTables.fixedColumns.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-fixedcolumns-dt@4.3.0/css/fixedColumns.dataTables.min.css" />

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
    type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>



<script>
    $(document).ready(function() {
        $('label[for="txtToDate"]').html('Till Date Pending: <span style="color:red">*</span>');

        var toDate = new Date().toISOString().split('T')[0];
        $("#txtToDate").val(toDate);
        $("#txtToDate").prop("readonly", true);
        $("#txtToDate").css("pointer-events", "none");

        var productionPlant = <?php echo $ProductionPlant ?>;
        var IsPlantFilter = <?php echo $IsPlantFilter ?>;
        console.log("productionPlant ", productionPlant, " IsPlantFilter ", IsPlantFilter)
        if (productionPlant != 0 || IsPlantFilter == 0) {
            $("#drpReceivingPlantId").prop('disabled', true);
            if (IsPlantFilter == 0) {
                $("#drpReceivingPlantId").val("");
            } else {
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

        $("#btnDownloadExcel").on("click", function() {
            // downloadAllTablesAsExcel();
            DownloadExcel()

        });
    });


    // function downloadAllTablesAsExcel() {
    //     var tables = $("table"); // Get all tables
    //     var fromDate = $("#txtFromDate").val().split('-').reverse().join('/');
    //     var toDate = $("#txtToDate").val().split('-').reverse().join('/');
    //     var receivingPlant = $("#drpReceivingPlantId option:selected").text();

    //     // Create header row
    //     var headerRow = `
    //     <tr>
    //         <td colspan="10" style="text-align: center; font-weight: bold; border: 1px solid black;">
    //             Chill Room Balance Report ( ${fromDate} - ${toDate} ) _Receiving Plant ${receivingPlant}
    //         </td>
    //     </tr>
    // `;

    //     // Wrap header row in a table for consistent formatting
    //     var headerTable = `
    //     <table style="border-collapse: collapse; width: 100%;">
    //         ${headerRow}
    //     </table>
    //     <br>
    // `;

    //     // Start combined HTML with the header table
    //     var combinedHTML = `
    //     <html>
    //         <head>
    //             <style>
    //                 table { 
    //                     border-collapse: collapse; 
    //                     width: 100%; 
    //                 }
    //                 td, th { 
    //                     padding: 4px; /* Adjust padding */
    //                     text-align: left; 
    //                     border: 1px solid black; /* Add borders */
    //                 }
    //             </style>
    //         </head>
    //         <body>
    //             ${headerTable}
    // `;

    //     // Add all tables to the combined HTML
    //     tables.each(function () {
    //         var tableHTML = $(this)[0].outerHTML;
    //         combinedHTML += tableHTML + '<br><br>';
    //     });

    //     combinedHTML += '</body></html>';

    //     // Prepare and download the Excel file
    //     var downloadLink;
    //     var dataType = 'data:application/vnd.ms-excel';
    //     var filename = 'Chill_room_balance_report' + ' _From: ' + fromDate + ' _To: ' + toDate + '.xls';

    //     var blob = new Blob(['\ufeff', combinedHTML], { type: dataType });

    //     downloadLink = document.createElement('a');
    //     document.body.appendChild(downloadLink);

    //     if (navigator.msSaveOrOpenBlob) {
    //         navigator.msSaveOrOpenBlob(blob, filename);
    //     } else {
    //         downloadLink.href = URL.createObjectURL(blob);
    //         downloadLink.download = filename;
    //         downloadLink.click();
    //     }
    // }
    // async function downloadAllTablesAsExcel() {
    //     const tables = $("table"); // Get all tables
    //     const fromDate = $("#txtFromDate").val().split('-').reverse().join('/');
    //     const toDate = $("#txtToDate").val().split('-').reverse().join('/');
    //     const receivingPlant = $("#drpReceivingPlantId option:selected").text();

    //     // Create a new Excel workbook and worksheet
    //     const workbook = new ExcelJS.Workbook();
    //     const worksheet = workbook.addWorksheet("Chill Room Balance Report");

    //     // Add header row
    //     const headerRow = `Chill Room Balance Report ( ${fromDate} - ${toDate} ) _Receiving Plant ${receivingPlant}`;
    //     worksheet.addRow([headerRow]);
    //     worksheet.mergeCells(1, 1, 1, 10); // Merge A1 to J1 (adjust column count as needed)
    //     const headerCell = worksheet.getCell('A1');
    //     headerCell.alignment = { horizontal: 'center', vertical: 'middle' };
    //     headerCell.font = { bold: true, size: 14 };
    //     headerCell.border = { bottom: { style: 'thin' } };

    //     // Add a blank row after the header
    //     worksheet.addRow([]);

    //     // Loop through each table and add its content to the Excel sheet
    //     tables.each(function (tableIndex, tableElement) {
    //         const table = $(tableElement);

    //         // Add table rows to the Excel worksheet
    //         table.find("tr").each(function (rowIndex, rowElement) {
    //             const row = [];
    //             $(rowElement).find("td, th").each(function (cellIndex, cellElement) {
    //                 row.push($(cellElement).text().trim());
    //             });
    //             worksheet.addRow(row);
    //         });

    //         // Add a blank row after each table
    //         if (tableIndex < tables.length - 1) {
    //             worksheet.addRow([]);
    //         }
    //     });

    //     // Adjust column widths for better readability
    //     worksheet.columns.forEach((column) => {
    //         column.width = 15; // Set a default column width
    //     });

    //     // Export the Excel file
    //     const filename = `Chill_room_balance_report_From_${fromDate}_To_${toDate}.xlsx`;
    //     const buffer = await workbook.xlsx.writeBuffer();
    //     const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    //     const link = document.createElement("a");

    //     link.href = URL.createObjectURL(blob);
    //     link.download = filename;
    //     document.body.appendChild(link);
    //     link.click();
    //     document.body.removeChild(link);
    // }





    // function fnPoStatusReportExcel(tableID, filename = '') {
    //     var fromDate = $("#txtFromDate").val().split('-').reverse().join('/');
    //     var toDate = $("#txtToDate").val().split('-').reverse().join('/');
    //     var receivingPlant = $("#drpReceivingPlantId option:selected").text();
    //     var receivingPlant = $("#drpReceivingPlantId option:selected").text();

    //     var tableSelector = $("#" + tableID);//uma

    //     tableSelector.find(".thead-light  tr").height("auto");
    //     tableSelector.find(".thead-light  tr th").height("auto");
    //     tableSelector.find(".thead-light  tr th div").height("auto");

    //     tableSelector.find("tfoot tr").height("auto");
    //     tableSelector.find("tfoot tr td").height("auto");
    //     tableSelector.find("tfoot tr th div").height("auto").css("font-weight", "bold");

    //     var downloadLink;
    //     var dataType = 'data:application/vnd.ms-excel';
    //     var tableSelect = document.getElementById(tableID);

    //     var clonedTable = tableSelect.cloneNode(true);

    //     var reportNameRow = document.createElement('tr');
    //     reportNameRow.innerHTML = `
    //         <td colspan="10" style="text-align: center; font-weight: bold;">
    //         Chill Room Balance Report ( ${fromDate} - ${toDate} ) _Receiving Plant ${receivingPlant}
    //         </td>`;
    //     clonedTable.querySelector('thead').insertBefore(reportNameRow, clonedTable.querySelector('thead').firstChild);


    //     var trs = clonedTable.getElementsByTagName('tr');
    //     for (var i = 0; i < trs.length; i++) {
    //         trs[i].style.border = '1px solid #eff2f7';
    //         var tds = trs[i].getElementsByTagName('td');
    //         for (var j = 0; j < tds.length; j++) {
    //             tds[j].style.border = '1px solid #eff2f7';
    //         }
    //     }

    //     var hiddenCells = clonedTable.querySelectorAll('td[style*="display: none"]');
    //     hiddenCells.forEach(function (cell) {
    //         cell.style.visibility = 'hidden';
    //     });

    //     var tableHTML = clonedTable.outerHTML;

    //     filename = filename ? filename + '_From: ' + fromDate + ' _To: ' + toDate + '.xls' : 'excel_data.xls';

    //     downloadLink = document.createElement('a');
    //     document.body.appendChild(downloadLink);

    //     if (navigator.msSaveOrOpenBlob) {
    //         var blob = new Blob(['\ufeff', tableHTML], {
    //             type: dataType
    //         });
    //         navigator.msSaveOrOpenBlob(blob, filename);
    //     } else {
    //         downloadLink.href = dataType + ', ' + encodeURIComponent(tableHTML);
    //         downloadLink.download = filename;
    //         downloadLink.click();
    //     }

    //     tableSelector.find(".thead-light  tr").height("0px");
    //     tableSelector.find(".thead-light  tr th").height("0px");
    //     tableSelector.find(".thead-light  tr th div").height("0px");

    //     tableSelector.find("tfoot tr").height("0px");
    //     tableSelector.find("tfoot tr td").height("0px");
    //     tableSelector.find("tfoot tr th div").height("0px");

    // }

    function fnShowDataTable(tableID) {

        var table = $('#' + tableID).DataTable({ //uma

            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            scrollCollapse: true,
            bInfo: false,
            bSort: false,
            // dom: 'Blfrtip',
            dom: 'lrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-right-align',
                title: 'Chill Room Balance Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                action: function() {
                    // fnPoStatusReportExcel(tableID, 'Chill Room Balance Report');//uma
                    DownloadExcel()
                }
            }],
            fixedColumns: {
                 leftColumns: 3
            }
        });
    }

    /*function DownloadExcel() {
        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();
        var receivingPlantID = $("#drpReceivingPlantId").val();
        var ChillRoomPreparationType = $("#drpChillRoomPreparation").val();
        var plantName = $("#drpReceivingPlantId option:selected").text();

        var startDate = new Date(fromDate);
        var endDate = new Date(toDate);
        // var url = 'Reports/SoakingFloorBalanceReport.php?receivingPlantID=' + receivingPlantID + '&fromDate=' + fromDate + '&ChillRoomPreparationType=' + ChillRoomPreparationType + '&toDate=' + toDate + '&plantName=' + plantName + '&excelDownload=T';
        var url = 'Reports/SoakingFloorBalanceReport.php?receivingPlantID=' + receivingPlantID + '&ChillRoomPreparationType=' + ChillRoomPreparationType + '&toDate=' + toDate + '&plantName=' + plantName + '&excelDownload=T';
        window.open(url, '_blank');
    }*/

async function DownloadExcel() {
    var toDate = $("#txtToDate").val().split('-').reverse().join('/');
    var plantName = $("#drpReceivingPlantId option:selected").text();
    var ChillRoomPreparationType = $("#drpChillRoomPreparation").val();

    var heading = 'Chill Room Balance Report (Till: ' + toDate + ') _Receiving Plant: ' + plantName;
    if (ChillRoomPreparationType) {
        heading += ' _Type: ' + ChillRoomPreparationType;
    }

    var workbook = new ExcelJS.Workbook();
    var worksheet = workbook.addWorksheet("Report");
    worksheet.views = [{ state: 'frozen', xSplit: 3 }];

    // Add and style heading row
    worksheet.addRow([heading]);
    var headingRow = worksheet.getRow(1);
    headingRow.font = { bold: true, size: 12 };
    headingRow.alignment = { horizontal: "center", vertical: "middle" };

    //var tables = document.querySelectorAll('#divDataList table');

    var tables = document.querySelectorAll('#divDataList .dataTables_scrollBody table');
    var numColumns = 0;

    tables.forEach(function(table, tIdx) {
        // Add a blank separator row between tables (except before first)
        if (tIdx > 0) worksheet.addRow([]);

        var rows = table.getElementsByTagName("tr");

        for (var r = 0; r < rows.length; r++) {
            var cells = rows[r].children;
            var rowData = [];
            for (var c = 0; c < cells.length; c++) {
                rowData.push(cells[c].innerText.trim());
            }

            // Skip empty rows
            if (rowData.length === 0 || (rowData.length === 1 && rowData[0] === '')) continue;

            // Capture max columns from first real data row
            if (numColumns === 0 && rowData.length > 3) {
                numColumns = rowData.length;
                worksheet.mergeCells(1, 1, 1, numColumns);
            }

            worksheet.addRow(rowData);
        }
    });

    // Format numeric cells starting from row 4 (after heading + header rows)
    // Convert text numbers to actual numbers
worksheet.eachRow(function (row) {
    row.eachCell(function (cell) {
        if (typeof cell.value === 'string') {
            let value = cell.value.trim();

            if (value === '' || value === '-') {
                return;
            }

            // Handle values like 45.00-
            if (value.endsWith('-')) {
                value = '-' + value.slice(0, -1);
            }

            value = value.replace(/,/g, '');

            const num = parseFloat(value);

            if (!isNaN(num)) {
                cell.value = num;
                cell.numFmt = '0.000';
            }
        }
    });
});

var lastRow = worksheet.rowCount;

    // Make last two rows bold (Grand Totals)
    if (lastRow >= 2) {
        worksheet.getRow(lastRow).eachCell(function(cell) {
            cell.font = { bold: true };
        });
    }

    // Style header row (row 2)
    if (worksheet.rowCount >= 2) {
        worksheet.getRow(2).eachCell(function(cell) {
            cell.font = { bold: true };
            cell.alignment = { horizontal: "center", vertical: "middle", wrapText: true };
        });
    }

    // Auto-fit column widths with max cutoff
    if (numColumns > 0) {
        worksheet.columns = new Array(numColumns).fill({ width: 10 });
        worksheet.columns.forEach(function(column) {
            var maxWidth = 15;
            column.eachCell({ includeEmpty: true }, function(cell) {
                var cellValue = cell.value ? cell.value.toString() : '';
                maxWidth = Math.min(Math.max(maxWidth, cellValue.length + 2), 20);
            });
            column.width = maxWidth;
        });
    }

    // Generate and download
    var filename = 'Chill_Room_Balance_Report_Till_' + toDate + '.xlsx';
    var buffer = await workbook.xlsx.writeBuffer();
    var blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    var url = URL.createObjectURL(blob);
    var link = document.createElement("a");
    link.href = url;
    link.download = filename;
    link.click();
    URL.revokeObjectURL(url);
}

    function ShowList() {

        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();
        var receivingPlantID = $("#drpReceivingPlantId").val();
        var ChillRoomPreparationType = $("#drpChillRoomPreparation").val();
        var plantName = $("#drpReceivingPlantId option:selected").text();

        var startDate = new Date(fromDate);
        var endDate = new Date(toDate);

        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

        var days = daysDiff + 1;

        console.log('days ' + days);

        if (days > 15 && 0) {
            DownloadExcel()
        } else {
            $.ajax({
                url: 'Ajax/ajax-soaking-floor-balance-variety-report.php',
                type: 'POST',
                data: {
                    // fromDate: $("#txtFromDate").val(),
                    toDate: $("#txtToDate").val(),
                    receivingPlantID: $("#drpReceivingPlantId").val(),
                    ChillRoomPreparationType: ChillRoomPreparationType,
                },

                success: function(data) {
                    $('#btnDownloadExcel').show();
                    $("#divDataList").html(data);

                    $("#divDataList").find("table").each(function() { //uma
                        var tableID = $(this).attr("id"); // Get unique table ID

                        fnShowDataTable(tableID);
                    });

                    return false;

                }
            });
        }
    }

    $("#btnSearch").on("click", function() {
        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();
        if (fromDate != '' && toDate != '') {
            ShowList();
            //DownloadExcel();
        } else {
            alert('Select From Date and To Date')
        }
    });
$("#drpReceivingPlantId").on("change", function () {

    $("#divDataList").html("");
});
</script>

</body>

</html>
