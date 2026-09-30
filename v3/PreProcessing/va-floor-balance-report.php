<?php
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// $filterArrivalDateTwo = "T";
// $filterFromDate = "T";
$filterToDate = "T";
// $filterReceivingPlant = "T";
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
        $("#txtToDate").parent().after(`
            <div class="col col-md-3 mb-3 ">
                <label for="drpTreatment">Treatment <span style="color:red"></span></label>
                <select id="drpTreatment" class="form-control">
                    <option value="">All</option>
                    <option value="UNT">UNT</option>
                    <option value="BPT">BPT</option>
                    <option value="SALT">SALT</option>
                </select>
            </div>
            <div class="col col-md-3 mb-3 ">
                <label for="drpTypeOfMeterial">Type of Material <span style="color:red"></span></label>
                <select id="drpTypeOfMeterial" class="form-control">
                    <option value="">All</option>
                    <option value="1" selected>Regular</option>
                    <option value="2">Transfered</option>
                </select>
            </div>
        `);
        $("#drpTypeOfMeterial,#drpTreatment").select2()
    });


    async function fnPoStatusReportExcel(tableID, filename = '') {
        // var fromDate = $("#txtFromDate").val().split('-').reverse().join('/');
        var toDate = $("#txtToDate").val().split('-').reverse().join('/');
        var receivingPlant = $("#drpReceivingPlantId option:selected").text();

        $("#tblDataTable").find(".thead-light  tr").height("auto");
        $("#tblDataTable").find(".thead-light  tr th").height("auto");
        $("#tblDataTable").find(".thead-light  tr th div").height("auto");

        $("#tblDataTable").find("tfoot tr").height("auto");
        $("#tblDataTable").find("tfoot tr td").height("auto");
        $("#tblDataTable").find("tfoot tr th div").height("auto").css("font-weight", "bold");

        // const tableHeading = `Floor Balance Report ( ${fromDate} - ${toDate} ) _Receiving Plant ${receivingPlant}`;
        const tableHeading = `Floor Balance Report ( ${toDate} ) _Receiving Plant ${receivingPlant}`;

        // Initialize workbook and worksheet
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet("Report");
        worksheet.views = [{
                state: 'frozen',
                xSplit: 3
            }
        ];

        // Add and style heading
        worksheet.addRow([tableHeading]);
        const headingRow = worksheet.getRow(1);
        headingRow.font = {
            bold: true,
            size: 12
        };
        headingRow.alignment = {
            horizontal: "center",
            vertical: "middle"
        };

        // Process table rows
        const table = document.getElementById(tableID).cloneNode(true);
        const rows = table.getElementsByTagName("tr");
        const ws_data = [];
        const numColumns = rows[0]?.children?.length ?? 8; // Adjust based on your table's number of columns
        worksheet.mergeCells(1, 1, 1, numColumns);
        let grandTotalsRow = null;
        let yeildsRow = null;

        for (let row = 0; row < rows.length; row++) {
            const cells = rows[row].children;
            const rowData = [];

            for (let col = 0; col < cells.length; col++) {
                const cell = cells[col];
                rowData.push(cell.innerText.trim());
            }

            if (rowData[1]?.includes("Totals") || rowData[1]?.includes("Yield")) {
                if (rowData[1]?.includes("Totals")) {
                    rowData.splice(3, 0, "");
                    grandTotalsRow = rowData;
                }
                if (rowData[1]?.includes("Yield")) {
                    yeildsRow = rowData;
                }
            } else {
                ws_data.push(rowData);
            }

        }
        // Add grand totals row if it exists
        if (grandTotalsRow) ws_data.push(grandTotalsRow);
        if (yeildsRow) ws_data.push(yeildsRow);
        // Add data rows to the worksheet
        ws_data.forEach((row, idx) => {
            const addedRow = worksheet.addRow(row);
            // Enable word wrapping
            addedRow.eachCell((cell) => {
                cell.alignment = {
                    wrapText: true,
                    vertical: 'middle'
                };
            });

            // Format columns H to O as numbers, starting from the 4th row
            if (idx >= 1) {
                for (let col = 3; col < numColumns; col++) {
                    if (col != 4) {
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


        // === Make Grand Totals and Yield rows bold ===
const totalRows = worksheet.rowCount; // total number of rows added
const lastTwoRows = [totalRows]; // assuming totals and yield are last two rows

lastTwoRows.forEach(rowIndex => {
    const row = worksheet.getRow(rowIndex);
    row.eachCell((cell) => {
        cell.font = { bold: true }; // make the entire row bold
    });
});


        // Style headers in row 2 (bold text)
        const headerRow = worksheet.getRow(2);
        headerRow.eachCell((cell) => {
            cell.font = {
                bold: true
            };
        });

        // Adjust column widths with a maximum cutoff
        worksheet.columns = new Array(numColumns).fill({
            width: 10
        });
        worksheet.columns.forEach((column) => {
            let maxWidth = 15; // Maximum width cutoff
            column.eachCell({
                includeEmpty: true
            }, (cell) => {
                const cellValue = cell.value ? cell.value.toString() : '';
                maxWidth = Math.min(Math.max(maxWidth, cellValue.length + 2), 20); // Adjust width dynamically with cutoff at 30
            });
            column.width = maxWidth;
        });

        // Export the workbook
        // filename = filename ? `${filename}_From_${fromDate}_To_${toDate}.xlsx` : 'Floor_Balance_Report.xlsx';
        filename = filename ? `${filename}_Till_${toDate}.xlsx` : 'Floor_Balance_Report.xlsx';
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        });
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

            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Floor Balance Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                action: function() {
                    fnPoStatusReportExcel('tblDataTable', 'Floor Balance Report');
                }
            }],
            fixedColumns: {
                leftColumns: 3
            }
        });
    }

    function ShowList() {

        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();
        var receivingPlantID = $("#drpReceivingPlantId").val();
        var plantName = $("#drpReceivingPlantId option:selected").text();
        if(!receivingPlantID){
            alert('Please Select Plant.');
            return;
        }

        var startDate = new Date(fromDate);
        var endDate = new Date(toDate);

        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

        var days = daysDiff + 1;

        console.log('days ' + days);

        $.ajax({
            url: 'Ajax/ajax-va-floor-balance-report.php',
            type: 'POST',
            data: {
                // fromDate: $("#txtFromDate").val(),
                toDate: $("#txtToDate").val(),
                receivingPlantID: $("#drpReceivingPlantId").val(),
                drpTypeOfMeterial : $('#drpTypeOfMeterial').val(),
                drpTreatment : $('#drpTreatment').val()
            },

            success: function(data) {
                if (days > 15) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    if (!$("#divDataList").text().includes("No data available in table")) {
                        $(".btnExcelDataTable").trigger('click');
                        $("#divDataList").html("");
                    }
                } else {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                }

                return false;
            }
        });
    }

    $("#btnSearch").on("click", function() {
        // var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();
        // if (fromDate != '' && toDate != '') {
        if (toDate != '') {
            ShowList();
        } else {
            alert('Select To Date')
        }
    })
$("#drpReceivingPlantId").on("change", function () {
    $('#tblDataTable').DataTable().destroy();
    $("#divDataList").html("");
});
</script>

</body>

</html>
