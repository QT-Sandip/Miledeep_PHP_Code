<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$filterFromDate = "T";
$filterToDate = "T";
$filterButButton = "T";

$ClientID = $_SESSION['ClientID'];
$NoOfTableCols = isset($ClientID) && $ClientID !== 'DFLV3' ? 15 : 14;

$ProductionPlant = $_SESSION['UnitID'];
$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;
if ($IsPlantFilter == 1) {
    $filterReceivingPlant = "T";
}

// echo $IsPlantFilter;
// exit;
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

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
    type="text/javascript"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>

<script>
    $(document).ready(function () {
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
        // $("#drpReceivingPlantId").prop('disabled', true);
        // $("#drpReceivingPlantId").val(productionPlant).trigger('change');
        // } else {
        // $("#drpReceivingPlantId").prop('disabled', false);
        // $("#drpReceivingPlantId").val("").trigger('change');
        // }
    });

    async function fnPoStatusReportExcel(tableID, filename = '') {

        const fromDate = $("#txtFromDate").val().split('-').reverse().join('/');
        const toDate = $("#txtToDate").val().split('-').reverse().join('/');
        const receivingPlant = $("#drpReceivingPlantId option:selected").text();
        const tableHeading = `Rm Received Day wise report ( ${fromDate} - ${toDate} ) _Receiving Plant ${receivingPlant}`;

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
        const rows = table.querySelectorAll("tr");
        // const numColumns = rows[0]?.children?.length ?? <?php echo $NoOfTableCols; ?>;
        const numColumns = (rows[0] && rows[0].children && rows[0].children.length) ? rows[0].children.length : <?php echo $NoOfTableCols; ?>;
        // Adjust based on your table's number of columns
        worksheet.mergeCells(1, 1, 1, numColumns);
        const ws_data = [];
        let grandTotalsRow = null;

        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].children;
            const rowData = [];
            const nestedRowsData = [];
            let hasNestedTable = false;

            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                const nestedTable = cell.querySelector("table");

                if (nestedTable) {
                    hasNestedTable = true;
                    const nestedRows = nestedTable.querySelectorAll("tr");

                    for (let k = 0; k < nestedRows.length; k++) {
                        const nestedRowData = [];
                        const nestedCells = nestedRows[k].children;

                        for (let m = 0; m < nestedCells.length; m++) {
                            nestedRowData.push(nestedCells[m].innerText.trim());
                        }
                        nestedRowsData.push(nestedRowData);
                    }
                } else {
                    rowData.push(cell.innerText.trim());
                }
            }

            // if (hasNestedTable && nestedRowsData.length > 0) {
            //     // Merge first row of the nested table into the main row
            //     rowData.push(...nestedRowsData[0]);
            //     rowData.splice(5, 9); // Adjust columns if needed
            //     ws_data.push(rowData);

            //     // Add remaining nested rows (if multiple exist)
            //     for (let x = 1; x < nestedRowsData.length; x++) {
            //         ws_data.push(["", "", "", "", "", ...nestedRowsData[x]]);
            //     }
            // } 
            if (hasNestedTable && nestedRowsData.length > 0) {
                for (let x = 0; x < nestedRowsData.length; x++) {
                    const remarks = rowData[rowData.length - 1] || "";
                    const mainData = [
                        rowData[0] || "", // SNo
                        rowData[1] || "", // Arrival Date
                        rowData[2] || "", // Vehicle No
                        rowData[3] || "", // Track Number
                        rowData[4] || "", // Seal No
                        //  rowData[5] || "",  // Remarks
                    ];
                    const nestedRow = nestedRowsData[x];
                    const fullRow = [...mainData, ...nestedRow, remarks];
                    ws_data.push(fullRow);
                }
            }

            else {
                // if (rowData[1]?.includes("Grand Totals")) {
                //     grandTotalsRow = rowData;
                // } else
                if (rowData.length > 10 && i !== 1) {
                    ws_data.push(rowData);
                }
            }
        }

        // if (grandTotalsRow) ws_data.push(grandTotalsRow);

        let addedGrandTotalRow = null;
        // Add data rows to the worksheet
        ws_data.forEach((row, idx) => {
            const addedRow = worksheet.addRow(row);
            // Format columns H to O as numbers, starting from the 4th row
            if (idx >= 1) {
                for (let col = 6; col < numColumns; col++) {
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
            if (JSON.stringify(row) === JSON.stringify(grandTotalsRow)) {
                addedGrandTotalRow = addedRow;
            }
        });

        // Style headers in row 2 (bold text)
        const headerRow = worksheet.getRow(2);
        headerRow.font = { bold: true, size: 12 };
        // Read totals updated by DataTables draw.dt calculation
        const arrivalTotal = parseFloat($("#ArrivalQTY").text().replace(/,/g, '')) || 0;
        const receivedTotal = parseFloat($("#ReceivingQTY").text().replace(/,/g, '')) || 0;

        // Build your grand total row (adjust columns to match your sheet)
        const grandTotalRowData = [
            "",                // SNO
            "Grand Totals",    // Label
            "", "", "", "", "",   // Empty columns until numeric columns
            arrivalTotal, "", "", "",
            receivedTotal
        ];

        // Add to sheet
        const grandTotalExcelRow = worksheet.addRow(grandTotalRowData);

        // Style it bold
        grandTotalExcelRow.eachCell(c => {
            c.font = { bold: true, size: 12 };
        });


        //         if (addedGrandTotalRow) {
        //     addedGrandTotalRow.eachCell((cell) => {
        //         cell.font = { bold: true, size: 12 };
        //     });
        // }
        // Adjust column widths
        worksheet.columns = new Array(numColumns).fill({ width: <?php echo $NoOfTableCols; ?> });

        worksheet.columns = [
            { width: 8 },
            { width: 15 },
            { width: 17 },
            { width: 25 },
            { width: 15 },
            { width: 20 },
            { width: 10 },
            { width: 15 },
            { width: 15 },
            { width: 15 },
            { width: 18 },
            { width: 18 },
            { width: 15 },
            { width: 15 },
            { width: 40 }

        ];

        // Export the workbook
        filename = filename ? `${filename}_From_${fromDate}_To_${toDate}.xlsx` : 'Rm_Received_Day_wise_report.xlsx';
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
        const url = URL.createObjectURL(blob);

        // Trigger file download
        const link = document.createElement("a");
        link.href = url;
        link.download = filename;
        link.click();
        URL.revokeObjectURL(url);
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
                title: 'Receiving Overall Report <?php echo date("d-m-Y") ?>',
                footer: true,
                // exportOptions: {
                //     columns: ':visible'
                // },
                action: function () {
                    fnPoStatusReportExcel('tblDataTable', 'Receiving Overall Report');
                }
            }],
        });
        table.on('draw.dt', function () {
            let arrivalWeight = 0;
            let receivingQty = 0;

            $('.tdWeight').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    arrivalWeight += weight;
                }
            });
            $('.tdReceived_Weight').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    receivingQty += weight;
                }
            });
            $('#ArrivalQTY').text(arrivalWeight.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#ReceivingQTY').text(receivingQty.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });
    }

    function ShowList() {

        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();
        var receivingPlantID = $("#drpReceivingPlantId").val();
        var plantName = $("#drpReceivingPlantId option:selected").text();

        var startDate = new Date(fromDate);
        var endDate = new Date(toDate);

        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

        var days = daysDiff + 1;

        $.ajax({
            url: 'Ajax/ajax-receiving-trackid-overall-report.php',
            type: 'POST',
            data: {
                fromDate: $("#txtFromDate").val(),
                toDate: $("#txtToDate").val(),
                receivingPlantID: $("#drpReceivingPlantId").val(),
            },

            success: function (data) {
                if (days > 31) {
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

    $("#btnSearch").on("click", function () {
        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();
        if (fromDate != '' && toDate != '') {
            ShowList();
        } else {
            alert('Select From Date and To Date')
        }
    });
$("#drpReceivingPlantId").on("change", function () {
    $('#tblDataTable').DataTable().destroy();
    $("#divDataList").html("");
});
</script>

</body>

</html>
