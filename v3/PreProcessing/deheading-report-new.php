<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterFromDate = "T";
$filterToDate = "T";
// $filterReceivingPlant = "T";
$filterButButton = "T";

$ProductionPlant = $_SESSION['UnitID'];
$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;
if ($IsPlantFilter == 1) {
    $filterReceivingPlant = "T";
}
// echo $ProductionPlant;
// exit;
?>

<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>
    <style>
        div:not([class]):has(> ul.pagination) {
         float: none;
         margin-top: 0px;
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
        //     $("#drpReceivingPlantId").prop('disabled', true);
        //     $("#drpReceivingPlantId").val(productionPlant).trigger('change');
        // } else {
        //     $("#drpReceivingPlantId").prop('disabled', false);
        //     $("#drpReceivingPlantId").val("").trigger('change');
        // }
    });



    async function fnPoStatusReportExcel(tableID, filename = '') {
        const fromDate = $("#txtFromDate").val().split('-').reverse().join('/');
        const toDate = $("#txtToDate").val().split('-').reverse().join('/');
        const receivingPlant = $("#drpReceivingPlantId option:selected").text();
        const tableHeading = `Deheading Day wise report ( ${fromDate} - ${toDate} ) _Receiving Plant ${receivingPlant}`;

        // Initialize workbook and worksheet
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet("Report");

        // Add and style heading
        worksheet.addRow([tableHeading]);
        const headingRow = worksheet.getRow(1);
        headingRow.font = { bold: true, size: 12 };
        headingRow.alignment = { horizontal: "center", vertical: "middle" };

        // Clone and get all rows (thead, tbody, tfoot)
        const table = document.getElementById(tableID).cloneNode(true);
        const theadRows = table.tHead ? Array.from(table.tHead.rows) : [];
        const tbodyRows = table.tBodies.length ? Array.from(table.tBodies[0].rows) : [];
        const tfootRows = table.tFoot ? Array.from(table.tFoot.rows) : [];
        const rows = [...theadRows, ...tbodyRows, ...tfootRows];

        const numColumns = rows[0]?.children?.length ?? 15; // Adjust if needed
        worksheet.mergeCells(1, 1, 1, numColumns);

        const ws_data = [];
        var ClientID = "<?php echo $_SESSION['ClientID'];?>";
        let arrivalQtyPos = 0, hlQtyPos = 0, gradingHONPos = 0, SlicePos = 4, rowDataLength = 16, WsDataParentTablePos = ["","","",""];
        if(ClientID == 'SMEV3'){
            arrivalQtyPos = 3;
            hlQtyPos = arrivalQtyPos + 3;
            gradingHONPos = hlQtyPos + 1;//22 - 9

            SlicePos = 3;
            WsDataParentTablePos = ["","",""];
        }

        // Column indexes in Excel (1-based)
         let columnIndexes = {
            arrivalQty: 8 + arrivalQtyPos,       // 7th column (H)
            hlQty: 11 + hlQtyPos,            // 8th column (I)
            gradingHON: 15 + gradingHONPos,      // 14th column (N)
            gradingWeight: 16 + gradingHONPos,    // 15th column (O)
            gradingYeild: 17 + gradingHONPos    // 16th column (Q)
        };
        if(ClientID == 'CAPLV3'){
            columnIndexes = {
                arrivalQty: 8,       // 7th column (H)
                gradingHON: 10,      // 14th column (N)
                gradingWeight: 11,    // 15th column (O)
                gradingYeild: 12    // 16th column (Q)
            };

            rowDataLength = 10
        }

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
                    const nestedRows = nestedTable.getElementsByTagName("tr");

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
            //     rowData.splice(5, 13); // Adjust columns if needed
            //     ws_data.push(rowData);

            //     // Add remaining nested rows (if multiple exist)
            //     for (let x = 1; x < nestedRowsData.length; x++) {
            //         ws_data.push(["", "", "", "", ...nestedRowsData[x]]);
            //     }
            // } 
            if (hasNestedTable && nestedRowsData.length > 0) {

                // Parent columns (first 4)
                const parent = rowData.slice(0, SlicePos);

                // Remarks (always last)
                const remarks = rowData[rowData.length - 1];

                // Child columns (14 columns exact)
                const child = nestedRowsData[0];

                // Final row structure (parent + child + remarks)
                const finalRow = [
                    ...parent,
                    ...child,
                    remarks
                ];

                ws_data.push(finalRow);

                // remaining nested rows
                for (let x = 1; x < nestedRowsData.length; x++) {
                    ws_data.push([
                        ...WsDataParentTablePos,
                        ...nestedRowsData[x],
                        remarks
                    ]);
                }
            } else {
                // Exclude any existing rows containing "Grand Totals" anywhere (case-insensitive)
                if (
                    !rowData.some(cell => typeof cell === 'string' && cell.toLowerCase().includes("grand totals")) &&
                    rowData.length > rowDataLength &&
                    i !== 1
                ) {
                    ws_data.push(rowData);
                }
            }
        }

        // ---- Dynamic Grand Totals Calculation ----
        let totalArrivalQty = 0;
        let totalHLQty = 0;
        let totalGradingHON = 0;
        let totalGradingWeight = 0;
        let totalGradingYeild = 0;

        // Convert Excel 1-based to 0-based index for JS arrays
        const arrivalQtyCol = columnIndexes.arrivalQty - 1;
        const hlQtyCol = columnIndexes.hlQty - 1;
        const gradingHONCol = columnIndexes.gradingHON - 1;
        const gradingWeightCol = columnIndexes.gradingWeight - 1;
        const gradingYeildCol = columnIndexes.gradingYeild - 1;

        ws_data.forEach(row => {
            if (row.length > gradingWeightCol) {
                const arrivalVal = parseFloat(row[arrivalQtyCol]?.toString().replace(/,/g, '')) || 0;
                const hlVal = parseFloat(row[hlQtyCol]?.toString().replace(/,/g, '')) || 0;
                const gradingHONVal = parseFloat(row[gradingHONCol]?.toString().replace(/,/g, '')) || 0;
                const gradingWeightVal = parseFloat(row[gradingWeightCol]?.toString().replace(/,/g, '')) || 0;

                totalArrivalQty += arrivalVal;
                totalHLQty += hlVal;
                totalGradingHON += gradingHONVal;
                totalGradingWeight += gradingWeightVal;
            }
        });
         if(totalGradingHON && totalGradingWeight){
            totalGradingYeild = (totalGradingWeight/totalGradingHON) * 100;
        }

        // Create new Grand Totals row
        const grandTotalsRow = [];
        for (let i = 0; i < ws_data[0].length; i++) {
            if (i === 0) grandTotalsRow[i] = "Grand Totals";
            else if (i === arrivalQtyCol) grandTotalsRow[i] = totalArrivalQty.toFixed(2);
            else if (i === hlQtyCol) grandTotalsRow[i] = totalHLQty.toFixed(2);
            else if (i === gradingHONCol) grandTotalsRow[i] = totalGradingHON.toFixed(2);
            else if (i === gradingWeightCol) grandTotalsRow[i] = totalGradingWeight.toFixed(2);
            else if (i === gradingYeildCol) grandTotalsRow[i] = totalGradingYeild.toFixed(2);
            else grandTotalsRow[i] = "";
        }
        ws_data.push(grandTotalsRow);

        // Add data rows to worksheet
        ws_data.forEach((row, idx) => {
            const addedRow = worksheet.addRow(row);

            const isGrandTotalRow = row.some(cell =>
                typeof cell === 'string' && cell.toLowerCase().includes("grand totals")
            );

            if (isGrandTotalRow) {
                // Apply styles to each cell in the grand total row
                addedRow.eachCell((cell) => {
                    cell.font = { bold: true, size: 12 };

                });
            }

            // Format numeric columns from 7th column onwards, skip 16th
            if (idx >= 1) {
                for (let col = 6; col < numColumns; col++) {
                        const cell = addedRow.getCell(col + 1);
                        if (cell.value) {
                            const cleanedValue = parseFloat(cell.value.toString().replace(/,/g, ''));
                            if (!isNaN(cleanedValue)) {
                                cell.value = cleanedValue;
                                cell.numFmt = '#,##0.00';
                            }
                        }
                    
                }
            }
        });

        // Style header row 2 bold
        const headerRow = worksheet.getRow(2);
        headerRow.eachCell(cell => {
            cell.font = { bold: true };
        });

        // Adjust column widths
        worksheet.columns = new Array(numColumns).fill({ width: 15 });

        // Export workbook as Excel file
        filename = filename ? `${filename}_From_${fromDate}_To_${toDate}.xlsx` : 'Deheading_Day_wise_report.xlsx';
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
        const url = URL.createObjectURL(blob);

        // Trigger download
        const link = document.createElement("a");
        link.href = url;
        link.download = filename;
        link.click();
        URL.revokeObjectURL(url);

        // Optional: reset table header/footer row heights (your original code)
        $("#tblDataTable").find(".thead-light tr").height("0px");
        $("#tblDataTable").find(".thead-light tr th").height("0px");
        $("#tblDataTable").find(".thead-light tr th div").height("0px");

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
                title: 'Deheading Day Wise Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible',
                },
                action: function () {
                    fnPoStatusReportExcel('tblDataTable', 'Deheading Day Wise Report');
                }
            }],
        });
        table.on('draw.dt', function () {

            let arrivalQtyTotals = 0;
            let sampleHLQty = 0;
            let totalGradingHON = 0;
            let totalGradingWeight = 0;
            let totalGradingYeild = 0;


            $('.tdArrivalWeight').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    arrivalQtyTotals += weight;
                }
            });
            $('.tdHLQuantityKgs').each(function () {
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    sampleHLQty += weight;
                }
            });
            $('.tdGradingTotalHon').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    totalGradingHON += weight;
                }
            });
            $('.tdGradingTotalHl').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    totalGradingWeight += weight;
                }
            });
            $('#ArrivalQtyTotals').text(arrivalQtyTotals.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

            $('#TotalHLQuantityKgs').text(sampleHLQty.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#TotalGradingHON').text(totalGradingHON.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#TotalGradingWeight').text(totalGradingWeight.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            setTimeout(function () {
                if(totalGradingHON && totalGradingWeight){
                    totalGradingYeild = (totalGradingWeight/totalGradingHON) * 100;
                }
                $('#TotalGradingYeild').text(totalGradingYeild.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }, 200); // time in milliseconds (2 seconds)

            
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

        console.log('days ' + days);

        $.ajax({
            url: 'Ajax/ajax-deheading-report-new.php',
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
    $("#divDataList").html("");
});
</script>

</body>

</html>
