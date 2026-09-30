<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterFromDate = "T";
$filterToDate = "T";
$filterProductionLine = "T";
$filterButButton = "T";

$ProductionPlant = $_SESSION['UnitID'];
$ClientConfigData = $_SESSION['ClientConfigData'];
// $IsPlantFilter = $ClientConfigData->IsPlantFilter;
$IsPlantFilter = $ClientConfigData['IsPlantFilter'];
if ($IsPlantFilter == 1) {
    $filterReceivingPlant = "T";
}
// echo WORKING_MODULE;
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
                $("#drpReceivingPlantId").val(productionPlant);
            } else {
                $("#drpReceivingPlantId").val(productionPlant).trigger('change');
            }
        } else {
            $("#drpReceivingPlantId").prop('disabled', false);
        }
       
    });

    // function fnPoStatusReportExcel(tableID, filename = '') {
    //     var fromDate = $("#txtFromDate").val().split('-').reverse().join('/');
    //     var toDate = $("#txtToDate").val().split('-').reverse().join('/');
    //     var plant = $("#drpReceivingPlantId option:selected").text();
    //     var lineNumber = $("#drpLineNumber").text();

    //     $("#tblDataTable").find(".thead-light  tr").height("auto");
    //     $("#tblDataTable").find(".thead-light  tr th").height("auto");
    //     $("#tblDataTable").find(".thead-light  tr th div").height("auto");

    //     $("#tblDataTable").find("tfoot tr").height("auto");
    //     $("#tblDataTable").find("tfoot tr td").height("auto");
    //     $("#tblDataTable").find("tfoot tr th div").height("auto").css("font-weight", "bold");

    //     var downloadLink;
    //     var dataType = 'data:application/vnd.ms-excel';
    //     var tableSelect = document.getElementById(tableID);

    //     // var colCount = tableSelect.querySelector('thead tr').children.length;
    //     var clonedTable = tableSelect.cloneNode(true);
    //     // clonedTable.setAttribute("style", "text-align:center; border-collapse:collapse;");
        


    //     // var reportNameRow = document.createElement('tr');
    //     // reportNameRow.innerHTML = `
    //     // <td colspan="19" style="text-align: center; font-weight: bold;">
    //     //     Soaking Report_Date:( ${fromDate} - ${toDate} ) _Production Plant ${plant} _Line Number ${lineNumber}
    //     // </td>`;
    //     // clonedTable.querySelector('thead').insertBefore(reportNameRow, clonedTable.querySelector('thead').firstChild);
    //     var reportNameRow1 = document.createElement('tr');
    //     reportNameRow1.innerHTML = `
    //         <td colspan="19" style="text-align: center; font-weight: bold;">
    //             COASTAL AQUA PRIVATE LIMITED
    //         </td>`;

    //     var reportNameRow2 = document.createElement('tr');
    //     reportNameRow2.innerHTML = `
    //         <td colspan="19" style="text-align: center; font-weight: bold;">
    //             PATHARLAGADDA, KAKINADA
    //         </td>`;

    //     var reportNameRow3 = document.createElement('tr');
    //     reportNameRow3.innerHTML = `
    //         <td colspan="19" style="text-align: center; font-weight: bold;">
    //             TREATMENT - REGISTER
    //         </td>`;

    //     // Insert these rows at the beginning of the <thead>
    //     var thead = clonedTable.querySelector('thead');
    //     thead.insertBefore(reportNameRow3, thead.firstChild);
    //     thead.insertBefore(reportNameRow2, thead.firstChild);
    //     thead.insertBefore(reportNameRow1, thead.firstChild);      

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

    //     filename = filename ? filename + '_From: ' + fromDate + '_To' + toDate + '.xls' : 'excel_data.xls';

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

    //     $("#tblDataTable").find(".thead-light  tr").height("0px");
    //     $("#tblDataTable").find(".thead-light  tr th").height("0px");
    //     $("#tblDataTable").find(".thead-light  tr th div").height("0px");

    //     $("#tblDataTable").find("tfoot tr").height("0px");
    //     $("#tblDataTable").find("tfoot tr td").height("0px");
    //     $("#tblDataTable").find("tfoot tr th div").height("0px");
    // }

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

    const tableHeading = `${filename}_Date:( ${fromDate} - ${toDate} )`;

    // Initialize workbook and worksheet
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet("Report");

    // Clone table and determine column count
    const table = document.getElementById(tableID).cloneNode(true);
    const rows = table.getElementsByTagName("tr");
    const numColumns = 23; //rows[0]?.children?.length ?? 20;

    // Add Header Row 1: Company Name
    worksheet.addRow(["COASTAL AQUA PRIVATE LIMITED"]);
    worksheet.getRow(1).font = { bold: true, size: 14 };
    worksheet.getRow(1).alignment = { horizontal: "center", vertical: "middle" };
    worksheet.mergeCells(1, 1, 1, numColumns);

    // Add Header Row 2: Location
    worksheet.addRow(["PATHARLAGADDA, KAKINADA"]);
    worksheet.getRow(2).font = { bold: true, size: 12 };
    worksheet.getRow(2).alignment = { horizontal: "center", vertical: "middle" };
    worksheet.mergeCells(2, 1, 2, numColumns);

    // Add Header Row 3: Register Title
    worksheet.addRow(["TREATMENT - REGISTER"]);
    worksheet.getRow(3).font = { bold: true, size: 12 };
    worksheet.getRow(3).alignment = { horizontal: "center", vertical: "middle" };
    worksheet.mergeCells(3, 1, 3, numColumns);

    // Add Header Row 4: Metadata
    const metadataRowText = `Date: ${fromDate} - ${toDate}`; // | Plant: ${plantId} | Line: ${lineNumber}`;
    worksheet.addRow([metadataRowText]);
    worksheet.getRow(4).font = { bold: true, size: 11 };
    worksheet.getRow(4).alignment = { horizontal: "center", vertical: "middle" };
    worksheet.mergeCells(4, 1, 4, numColumns);

    const ws_data = [];
    let grandTotalsRow = null;

    // Extract table rows
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

    if (grandTotalsRow) ws_data.push(grandTotalsRow);

    // Add table data starting from row 5
    ws_data.forEach((row, idx) => {
        const addedRow = worksheet.addRow(row);

        // Word wrapping and alignment
        addedRow.eachCell((cell) => {
            cell.alignment = { wrapText: true, vertical: 'middle' };
        });

        // Format columns H to O as numbers, starting from 2nd data row
        if (idx >= 1) {
            for (let col = 11; col < numColumns - 1; col++) {
                const cell = addedRow.getCell(col + 1); // 1-based index
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

    // Auto column width adjustment
    worksheet.columns = new Array(numColumns).fill({ width: 10 });
    worksheet.columns.forEach((column) => {
        let maxWidth = 15;
        column.eachCell({ includeEmpty: true }, (cell) => {
            const cellValue = cell.value ? cell.value.toString() : '';
            maxWidth = Math.min(Math.max(maxWidth, cellValue.length + 2), 20);
        });
        column.width = maxWidth;
    });

    // Final filename and export
    filename = filename ? `${filename}_From_${fromDate}_To_${toDate}.xlsx` : 'Freezing_Report.xlsx';
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);

    const link = document.createElement("a");
    link.href = url;
    link.download = filename;
    link.click();
    URL.revokeObjectURL(url);

    // Reset height changes (optional cleanup)
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
                title: 'Soaking Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                action: function () {
                    fnPoStatusReportExcel('tblDataTable', 'Treatment_Register_Report');
                }
            }],
        });
    }

    function ShowList() {
        var drpReceivingPlantId = $("#drpReceivingPlantId").val();
        var txtStartDate = $("#txtFromDate").val();
        var txtEndDate = $("#txtToDate").val();
        var startDate = new Date(txtStartDate);
        var endDate = new Date(txtEndDate);
        var drpLineNumber = $("#drpLineNumber").val();

        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
        
        var days = daysDiff + 1;

        if(days > 31){
            var url = 'Ajax/ajax-treatment-register-report.php?plantId='+drpReceivingPlantId+'&lineNumber='+drpLineNumber+'&fromDate='+txtStartDate+'&toDate='+txtEndDate+'&excelDownload=T';
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    window.location.href=data.filename;
                }
            });     

        } else {
            $.ajax({
                url: 'Ajax/ajax-treatment-register-report.php',
                type: 'POST',
                data: {
                    fromDate: txtStartDate,
                    toDate: txtEndDate,
                    plantId: drpReceivingPlantId,
                    lineNumber: drpLineNumber,
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();

                    return false;
                }
            });            
        }

      
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
</script>

</body>

</html>