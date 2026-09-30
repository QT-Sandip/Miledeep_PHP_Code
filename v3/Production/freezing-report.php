<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$ClientID = $_SESSION["ClientID"];

if ($ClientID === "CAPLV3") {
    $filterFinancialYear = 'T';
    $filterBatchNo = 'T';
}

$filterFromDate = "T";
$filterToDate = "T";
// $filterReceivingPlant = "T";
$filterProductionLine = "T";
$filterFreezingType = "T";
$filterProductType = "T";
$filterProductionShifts = "T";
$filterButButton = "T";

$ProductionPlant = $_SESSION['UnitID'];
$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;
$LoadingLinersBasedOnPlant = $ClientConfigData->LoadingLinersBasedOnPlant;

// $IsPlantFilter = $ClientConfigData['IsPlantFilter'];
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
<style>
    .circular {
        height: 100px;
        width: 100px;
        position: relative;
    }

    .circular .inner,
    .circular .outer,
    .circular .circle {
        position: absolute;
        z-index: 6;
        height: 100%;
        width: 100%;
        border-radius: 100%;
        box-shadow: inset 0 1px 0 rgba(0, 0, 0, 0.2);
    }

    .circular .inner {
        top: 50%;
        left: 50%;
        height: 80px;
        width: 80px;
        margin: -40px 0 0 -40px;
        background-color: #dde6f0;
        border-radius: 100%;
        box-shadow: 0 1px 0 rgba(0, 0, 0, 0.2);
    }

    .circular .circle {
        z-index: 1;
        box-shadow: none;
    }

    .circular .numb {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 10;
        font-size: 18px;
        font-weight: 500;
        color: #4158d0;
    }

    .circular .bar {
        position: absolute;
        height: 100%;
        width: 100%;
        background: #fff;
        -webkit-border-radius: 100%;
        clip: rect(0px, 100px, 100px, 50px);
    }

    .circle .bar .progress {
        position: absolute;
        height: 100%;
        width: 100%;
        -webkit-border-radius: 100%;
        clip: rect(0px, 50px, 100px, 0px);
    }

    .circle .bar .progress,
    .dot span {
        background: #4158d0;
    }

    .circle .left .progress {
        z-index: 1;
        animation: left 4s linear both;
    }

    @keyframes left {
        100% {
            transform: rotate(180deg);
        }
    }

    .circle .right {
        z-index: 3;
        transform: rotate(180deg);
    }

    .circle .right .progress {
        animation: right 4s linear both;
        animation-delay: 4s;
    }

    @keyframes right {
        100% {
            transform: rotate(180deg);
        }
    }

    .circle .dot {
        z-index: 2;
        position: absolute;
        left: 50%;
        top: 50%;
        width: 50%;
        height: 10px;
        margin-top: -5px;
        animation: dot 8s linear both;
        transform-origin: 0% 50%;
    }

    .circle .dot span {
        position: absolute;
        right: 0;
        width: 10px;
        height: 10px;
        border-radius: 100%;
    }

    @keyframes dot {
        0% {
            transform: rotate(-90deg);
        }

        50% {
            transform: rotate(90deg);
            z-index: 4;
        }

        100% {
            transform: rotate(270deg);
            z-index: 4;
        }
    }
</style>
<style type="text/css">
    /*#preloader {
  background-color: rgb(0 0 0 / 65%);
}    */
</style>
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

        if (productionPlant != 0) {
            $("#drpReceivingPlantId").prop('disabled', true);
            $("#drpReceivingPlantId").val(productionPlant).trigger('change');
        } else {
            $("#drpReceivingPlantId").prop('disabled', false);
            $("#drpReceivingPlantId").val("").trigger('change');
        }
    });

    async function fnPoStatusReportExcel(tableID, filename = '') {
        var fromDate = $("#txtFromDate").val().split('-').reverse().join('/');
        var toDate = $("#txtToDate").val().split('-').reverse().join('/');
        var plantId = $("#drpReceivingPlantId option:selected").text();
        var lineNumber = $("#drpLineNumber option:selected").text();

        $("#tblDataTable").find(".thead-light  tr").height("auto");
        $("#tblDataTable").find(".thead-light  tr th").height("auto");
        $("#tblDataTable").find(".thead-light  tr th div").height("auto");

        $("#tblDataTable").find("tfoot tr").height("auto");
        $("#tblDataTable").find("tfoot tr td").height("auto");
        $("#tblDataTable").find("tfoot tr th div").height("auto").css("font-weight", "bold");

        const tableHeading = `Freezing Report_Date:( ${fromDate} - ${toDate} ) _Production Plant ${plantId}${!lineNumber?.includes("Select") ? " _Line Number" + lineNumber : ""}`;

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
        const numColumns = rows[0]?.children?.length ?? 20;; // Adjust based on your table's number of columns
        worksheet.mergeCells(1, 1, 1, numColumns);
        const ws_data = [];
        let grandTotalsRow = null;

        for (let row = 0; row < rows.length; row++) {
            const cells = rows[row].children;
            const rowData = [];

            for (let col = 0; col < cells.length; col++) {
                const cell = cells[col];
                rowData.push(cell.innerText.trim());
            }

            if (rowData[1]?.includes("Totals")) {
                grandTotalsRow = rowData;
            } else {
                ws_data.push(rowData);
            }

        }
        let totalCases = 0;
        let totalSlabs = 0;
        let totalProductionWeight = 0;
        let totalRejectionWeight = 0;

        // Loop through visible tbody rows only
        $("#tblDataTable tbody tr:visible").each(function () {
            const cells = $(this).find("td");

            // You can adjust the column indexes if your table changes
            const cases = parseFloat(cells.eq(11).text().replace(/,/g, '')) || 0;
            const slabs = parseFloat(cells.eq(12).text().replace(/,/g, '')) || 0;
            const prodWeight = parseFloat(cells.eq(13).text().replace(/,/g, '')) || 0;
            const rejWeight = parseFloat(cells.eq(14).text().replace(/,/g, '')) || 0;

            totalCases += cases;
            totalSlabs += slabs;
            totalProductionWeight += prodWeight;
            totalRejectionWeight += rejWeight;
        });

        // Create a totals row matching column count
        const totalsRow = new Array(numColumns).fill("");
        totalsRow[1] = "Totals";
        totalsRow[11] = totalCases.toLocaleString();          // No. of Cases
        totalsRow[12] = totalSlabs.toLocaleString();          // No. of Slabs
        totalsRow[13] = totalProductionWeight.toLocaleString(); // Prod. Weight
        totalsRow[14] = totalRejectionWeight.toLocaleString(); // Rej. Weight

        ws_data.push(totalsRow);
        // Add data rows to the worksheet
        ws_data.forEach((row, idx) => {
            const addedRow = worksheet.addRow(row);
            // Enable word wrapping
            addedRow.eachCell((cell) => {
                cell.alignment = { wrapText: true, vertical: 'middle' };
            });

            // Format columns H to O as numbers, starting from the 4th row
            if (idx >= 1) {
                for (let col = 10; col < numColumns - 1; col++) {
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
        });

        // Style headers in row 2 (bold text)
        const headerRow = worksheet.getRow(2);
        headerRow.eachCell((cell) => {
            cell.font = { bold: true };
        });

        // Adjust column widths with a maximum cutoff
        worksheet.columns = new Array(numColumns).fill({ width: 10 });
        worksheet.columns.forEach((column) => {
            let maxWidth = 15; // Maximum width cutoff
            column.eachCell({ includeEmpty: true }, (cell) => {
                const cellValue = cell.value ? cell.value.toString() : '';
                maxWidth = Math.min(Math.max(maxWidth, cellValue.length + 2), 20); // Adjust width dynamically with cutoff at 20
            });
            column.width = maxWidth;
        });

        // Export the workbook
        filename = filename ? `${filename}_From_${fromDate}_To_${toDate}.xlsx` : 'Freezing_Report.xlsx';
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
            /* search: {
                smart: false   // Disables smart/partial matching
            }, */
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Freezing Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                action: function () {
                    fnPoStatusReportExcel('tblDataTable', 'Freezing Report');
                }
            }],
        });
        table.on('draw.dt', function () {
            let noOfCases = 0;
            let noOfSlabs = 0;
            let productionWeight = 0;
            let rejectionWeight = 0;
            $('.tdNoofCases').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    noOfCases += weight;
                }
            });
            $('.tdNoofSlabs').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    noOfSlabs += weight;
                }
            });
            $('.tdProductionWeight').each(function () {
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    productionWeight += weight;
                }
            });

            $('.tdRejectionWeight').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    rejectionWeight += weight;
                }
            });

            $('#TotalNoofCases').text(noOfCases.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#TotalNoofSlabs').text(noOfSlabs.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#TotalProductionWeight').text(productionWeight.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

            $('#TotalRejectionWeight').text(rejectionWeight.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });


    }


    function ShowList() {
        var drpReceivingPlantId = $("#drpReceivingPlantId").val();
        var txtStartDate = $("#txtFromDate").val();
        var txtEndDate = $("#txtToDate").val();
        var startDate = new Date(txtStartDate);
        var endDate = new Date(txtEndDate);
        var drpLineNumber = $("#drpLineNumber").val();
        var drpFreezingType = $("#drpFreezingType").val();
        var drpProductionShifts = $('#drpProductionShifts').val();
        var drpProductType = $("#drpProductType").val();
        var arrivalID = $("#PurchaseStationID").val();

        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

        var days = daysDiff + 1;


        // if(days > 31){
        //     var url = 'Ajax/ajax-freezing-report.php?plantId='+drpReceivingPlantId+'&lineNumber='+drpLineNumber+'&freezingType='+drpFreezingType+'&shifts='+drpProductionShifts+'&productType='+drpProductType+'&fromDate='+txtStartDate+'&toDate='+txtEndDate+'&excelDownload=T';
        //     $.ajax({
        //         url: url,
        //         type: 'GET',
        //         dataType: 'json',
        //         success: function(data) {
        //             //console.log(data);
        //             //window.test=data;
        //             window.location.href=data.filename;
        //             // $("#divDataList").html(data);
        //             // fnShowDataTable();

        //             // return false;
        //         }
        //     });     

        // } else {
        // }
        $.ajax({
            url: 'Ajax/ajax-freezing-report.php',
            type: 'POST',
            data: {
                fromDate: txtStartDate,
                toDate: txtEndDate,
                plantId: drpReceivingPlantId,
                lineNumber: drpLineNumber,
                freezingType: drpFreezingType, // drpFreezingType
                shifts: drpProductionShifts,
                productType: drpProductType,
                ArrivalID: arrivalID,
            },
            success: function (data) {
                if (days > 31 && !arrivalID) {
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
    })

    $(document).on('change', '#drpYear', function () {
        if ($(this).val() !== "") {
            $("#txtFromDate, #txtToDate").val("").prop("readonly", true);
        } else {
            $("#txtFromDate, #txtToDate").prop("readonly", false);
        }
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

        $.ajax({
            url: 'Ajax/ajax-freezing-report.php',
            type: 'POST',
            data: {
                'methodType': 'yearChange',
                'DataType': "getArrivalsByFY",
                FYYear: yearVal,
                'PlantID': $("#drpArrivalPlant").val(),
            },
            success: function (data) {
                console.log(data);
                let list = "<option value=''>Select</option>";
                $.each(data.ArrivalsList, function (index, value) {
                    list += "<option value='" + value.RmArrivalID + "'>" + value.ArrivalNumber + "</option>";
                });
                $('#PurchaseStationID').html(list);
                $('#PurchaseStationID').select2();
            }
        });
    });

    $("#txtFromDate, #txtToDate").on("change", function () {
        if ($("#txtFromDate").val() || $("#txtToDate").val()) {
            $("#drpYear").val("").prop("disabled", true);
        } else {
            $("#drpYear").prop("disabled", false);
        }
        loadBatchesByDate();
    });

    function loadBatchesByDate() {
        const fromDate = $("#txtFromDate").val();
        const toDate = $("#txtToDate").val();
        const plantId = $("#drpArrivalPlant").val();

        if (!fromDate || !toDate) return;

        $.ajax({
            url: 'Ajax/ajax-freezing-report.php',
            type: 'POST',
            data: {
                'DataType': 'getArrivalsByDate',
                fromDate: fromDate,
                toDate: toDate,
                PlantID: plantId
            },
            success: function (data) {
                let list = "<option value=''>Select</option>";
                if (data && data.ArrivalsList && data.ArrivalsList.length > 0) {
                    $.each(data.ArrivalsList, function (index, value) {
                        list += `<option value="${value.RmArrivalID}">${value.ArrivalNumber}</option>`;
                    });
                } else {
                    list += "<option value=''>No data available</option>";
                }
                $('#PurchaseStationID').html(list).select2();
            }
        });
    }
    <?php 
    if(!empty($LoadingLinersBasedOnPlant)){?>
        $(document).on("change", "#drpReceivingPlantId", function () {
            if($(this).val()){
                $.ajax({
                    url: 'Ajax/ajax-soaking-report.php',
                    type: 'POST',
                    data: {
                        methodType: "getLinersByPlant",
                        plantID: $(this).val(),
                    },
                    dataType: 'json',
                    success: function (data) {
                        var list = "<option value=''>Select</option>";
                        if (data.Data) {
                            $.each(data.Data, function (index, value) {
                                list += "<option value='" + value.Name + "'>" + value.Name + "</option>";
                            });
                        }
                        $('#drpLineNumber').html(list)
                    }
                });
            }
        })
    <?php } ?>
$("#drpReceivingPlantId").on("change", function () {
    $("#divDataList").html("");
});
</script>
</body>

</html>
