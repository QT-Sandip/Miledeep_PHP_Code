<?php
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$filterFromDate = "T";
$filterToDate = "T";
// $filterArrivalDateTwo = "T";
// $filterReceivingPlant = "T";
$filterMachineID = "T";
$filterButButton = "T";
$filterProductType1 = "T";
$filterGrade = "T";

$ProductionPlant = $_SESSION['UnitID'];
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;

if ($IsPlantFilter == 1) {
    $filterReceivingPlant = "T";
}

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$gradOverallReportViewChg = $ArrayClientConfigData['gradOverallReportViewChg'];
?>

<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>
    <style>
        .dataTables_wrapper .row > div:empty {
            display: none !important;
        }
        table.dataTable {
    width: 100% !important;
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
                                <div id="divDataList" class="table-responsive">
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



        var $drpMachineID = $("#drpMachineID");

        $drpMachineID.find('option').each(function () {
            var optionPlantId = $(this).data('plantid');

            if (productionPlant != 0) {
                var plantIdArray = String(optionPlantId).split(',');

                if (plantIdArray.includes(String(productionPlant)) || $(this).val() === "") {
                    $(this).show();
                } else {
                    $(this).remove();
                }
            } else {
                $(this).show();
            }
        });
        $('#drpReceivingPlantId').on('change',function(){
            if($("#drpReceivingPlantId").val()){
                $.ajax({
                    url: 'Ajax/ajax-grading-overall-report.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        ReceivingPlantId: $("#drpReceivingPlantId").val(),
                        type : 'getGradingMachines'
                    },
                    success: function (data) {
                        var list = `<option value="">Select</option>`;
                        if(data.Error == null && data.Data.length != 0){
                            $.each(data.Data, function(index, value){
                                list += `<option value="${value.MachineID}" data-plantid="${value.UnitID}">${value.MachineName}</option>`
                            })
                            $("#drpMachineID").html(list)
                        }else{
                            $("#drpMachineID").html(list)
                            alert(data.Error)
                        }
                    }
                })
            }
        })

        var newInput = `
            <div class="col col-md-3 mb-3">
                <label>Grading Date</label>
                <input type="date" value="-04-01" class="form-control" name="GradingDate" id="GradingDate">
            </div>
        `;

        $('#drpGrade').closest('div').after(newInput);

        $("#txtFromDate, #txtToDate").on("change", function () {
            const fromDate = $("#txtFromDate").val();
            const toDate = $("#txtToDate").val();
            if (fromDate && toDate) {
                $("#GradingDate").prop("readonly", true).val('');
            } else {
                $("#GradingDate").prop("readonly", false).val('');
            }
        });

        $("#GradingDate").on("change", function () {
            const GradingDate = $(this).val();
            if (GradingDate) {
                $("#txtFromDate, #txtToDate").prop("readonly", true).val('');
            } else {
                $("#txtFromDate, #txtToDate").prop("readonly", false).val('');
            }
        });
    });

async function fnPoStatusReportExcel(tableID, filename = '', claintStatus) {
    // const arrivalDate = $("#txtArrivalDate").val().split('-').reverse().join('/');
    const fromDate = $("#txtFromDate").val().split('-').reverse().join('/');
    const toDate = $("#txtToDate").val().split('-').reverse().join('/');
    const receivingPlantId = $("#drpReceivingPlantId option:selected").text();
    const machineID = $("#drpMachineID option:selected").text();
    const tableHeading = `Grading Report_FromDate: ${fromDate}_ToDate: ${toDate}_Receiving Plant:${receivingPlantId}${machineID != "Select" ? "_GradingMachine:" + machineID : ""}`;

    const table = document.getElementById(tableID);
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet("Grading Report");

    const numColumns = table.querySelector("thead tr").children.length;

    // Title row (row 1)
    worksheet.addRow([tableHeading]);
    worksheet.mergeCells(1, 1, 1, numColumns);
    worksheet.getRow(1).font = { bold: true, size: 12 };
    worksheet.getRow(1).alignment = { horizontal: 'bottom', vertical: 'middle' };



    // Prepare to add headers and data starting from row 3
    let allRows = [];

    const processRows = (rowElements, bold = false) => {
        rowElements.forEach(tr => {
            const rowData = Array.from(tr.cells).map(cell => {
                let text = cell.innerText.trim();
                if (bold) {
                    text = text.replace(/,\s*/g, ',\n');
                }
                return text;
            });
            allRows.push({ data: rowData, bold });
        });
    };

    // Collect data rows
    processRows(table.querySelectorAll("thead tr"), true);   // Headers
    processRows(table.querySelectorAll("tbody tr"));         // Body
    processRows(table.querySelectorAll("tfoot tr"), true);   // Footer (original footer from table)

    // Add collected rows starting from row 3
    allRows.forEach((rowObj, rowIndex) => {
        const raw = rowObj.data;
        const row = worksheet.addRow(raw);

        // Format certain columns as numbers depending on clientStatus
        let RowNumbers = [];
        if (claintStatus == 1) {
            RowNumbers = [6, 8, 9]; // Coastal
        } else {
            RowNumbers = [3];
            for (let i = 5; i < numColumns; i++) {
                RowNumbers.push(i); // Devi
            }
        }

        RowNumbers.forEach(colIdx => {
            const cell = row.getCell(colIdx + 1);
            const clean = parseFloat(cell.value?.toString().replace(/,/g, ''));
            if (!isNaN(clean)) {
                cell.value = clean;
                cell.numFmt = '#,##0.00';
            }
        });

        if (rowObj.bold) {
            row.eachCell(cell => {
                cell.font = { bold: true };
            });
        }

        row.eachCell(cell => {
            cell.alignment = { vertical: 'middle', wrapText: true };
        });

        row.tfoot = rowObj.bold;


        
    });



    

    // === Merge Columns except stacked heading and footer ===
    const mergeCols = [0, 1, 2, 3, 9, 10];  // zero-based indices
    const headerRowCount = table.querySelectorAll("thead tr").length;
    const footerRowCount = table.querySelectorAll("tfoot tr").length;

    const dataStart = 3 + headerRowCount - 2; // adjusted since we added stacked heading row at 2
    const summaryRowIndex = worksheet.rowCount;
    const mergeEndLimit = summaryRowIndex - 1; // exclude last row (footer if any)

    let mergeStart = dataStart;
    let prevSno = worksheet.getRow(mergeStart).getCell(1).value;

    for (let i = mergeStart + 1; i <= mergeEndLimit; i++) {
        const currentRow = worksheet.getRow(i);
        const currentSno = currentRow.getCell(1).value;
        const isSame = currentSno === prevSno;

        if (!isSame || i === mergeEndLimit) {
            const mergeEnd = isSame ? i : i - 1;
            if (mergeEnd > mergeStart) {
                mergeCols.forEach(colIdx => {
                    worksheet.mergeCells(mergeStart, colIdx + 1, mergeEnd, colIdx + 1);
                });
            }
            mergeStart = i;
            prevSno = currentSno;
        }
    }


    

    // === Auto column widths ===
    worksheet.columns.forEach(col => {
        let maxLength = 10;
        col.eachCell({ includeEmpty: true }, cell => {
            const val = cell.value ? cell.value.toString() : '';
            maxLength = Math.max(maxLength, val.length + 2);
        });
        col.width = Math.min(maxLength, 40);
    });



// === Make footer (Totals, Yield, Type Product) rows bold directly ===
worksheet.eachRow((row, rowNumber) => {
    const rowText = row.values
        .filter(v => v)
        .join(" ")
        .toLowerCase();

    // Match footer-related keywords anywhere in the row
    if (
        rowText.includes("total") ||
        rowText.includes("yield") ||
        rowText.includes("type product")
    ) {
        row.eachCell(cell => {
            cell.font = { bold: true };
        });
    }
});

// === Export workbook as Excel file ===
const buffer = await workbook.xlsx.writeBuffer();


    // === Export workbook as Excel file ===
    // const buffer = await workbook.xlsx.writeBuffer();


    
    const blob = new Blob([buffer], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = `${filename}.xlsx`;
    link.click();
    URL.revokeObjectURL(url);

    // === Optional: Hide original table footer/header visually ===
    $("#tblDataTable").find(".thead-light  tr").height("0px");
    $("#tblDataTable").find(".thead-light  tr th").height("0px");
    $("#tblDataTable").find(".thead-light  tr th div").height("0px");

    $("#tblDataTable").find("tfoot tr").height("0px");
    $("#tblDataTable").find("tfoot tr td").height("0px");
    $("#tblDataTable").find("tfoot tr th div").height("0px");
}




    function fnShowDataTable() {
        var claintStatus = 1;
        var tableId = '#tblDataTable';
        var table = $(tableId).DataTable({
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
                title: 'Granding Overall Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                
                action: function () {
                    fnPoStatusReportExcel('tblDataTable', 'Grading Overall Report',claintStatus);
                }
            }],
             drawCallback: function (settings) {
                 const rowSpanColumns =[0, 1, 2, 3, 9, 10];
                        resetRowspan(tableId, rowSpanColumns);
                        applyRowspan(tableId, rowSpanColumns);
                        const api = this.api();
                api.columns.adjust();
            }
            
        });
    }
    function fnShowDataTableForDevi(){
        var claintStatus = 0;
        var tableId = '#tblDataTable';
        var table = $(tableId).DataTable({
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
                title: 'Granding Overall Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                action: function () {
                    fnPoStatusReportExcel('tblDataTable', 'Grading Overall Report',claintStatus);
                }
            }],
            fixedColumns: {
                leftColumns: 5
            }
            
        });
    }
    function resetRowspan(tableId, columnIndexes) {
            const $rows = $(`${tableId} tbody tr`);
            $rows.each(function () {
                const $cells = $(this).children('td');
                columnIndexes.forEach(index => {
                    const $cell = $cells.eq(index);
                    if ($cell.length) {
                        $cell.removeAttr('rowspan').css('display', '');
                    }
                });
            });
        }
    function applyRowspan(tableId, columnIndexes) {
        const $rows = $(`${tableId} tbody tr`);
        const groupColIndex = columnIndexes[0];
        let prevGroupValue = null;
        let groupStartIndex = 0;
        let groupRowCount = 0;

        $rows.each(function (rowIndex) {
            const $cells = $(this).children('td');
            const currentGroupValue = $cells.eq(groupColIndex).text().trim();

            if (prevGroupValue === currentGroupValue) {
                groupRowCount++;
                columnIndexes.forEach(index => {
                    $cells.eq(index).css('display', 'none');
                });
            } else {
                if (groupRowCount > 1) {
                    const $groupStartRow = $rows.eq(groupStartIndex);
                    columnIndexes.forEach(index => {
                        $groupStartRow.children('td').eq(index).attr('rowspan', groupRowCount);
                    });
                }
                prevGroupValue = currentGroupValue;
                groupStartIndex = rowIndex;
                groupRowCount = 1;
            }
        });

        // Final group
        if (groupRowCount > 1) {
            const $groupStartRow = $rows.eq(groupStartIndex);
            columnIndexes.forEach(index => {
                $groupStartRow.children('td').eq(index).attr('rowspan', groupRowCount);
            });
        }
    }

    function ShowList() {
        var IsPlantFilter = <?php echo $IsPlantFilter ?>;
        var Grade = '';
        if($("#drpGrade option:selected").text() != 'All'){
            Grade = $("#drpGrade option:selected").text();
        }
        $.ajax({
            url: 'Ajax/ajax-grading-overall-report.php',
            type: 'POST',
            data: {
                // arrivalDate: $("#txtArrivalDate").val(),
                fromDate: $("#txtFromDate").val(),
                toDate: $("#txtToDate").val(),
                receivingPlantId: $("#drpReceivingPlantId").val(),
                machineID: $("#drpMachineID").val(),
                typeProduct: $("#drpProductType").val(),
                //grade: $("#drpGrade").val(),
                grade: Grade,
                IsPlantFilter: IsPlantFilter,
                GradingDate: $("#GradingDate").val(),
            },
            success: function (data) {

                $("#divDataList").html(data);
                <?php 
                if($gradOverallReportViewChg == 1){?>
                    fnShowDataTable();
               <?php }else{?>
                    fnShowDataTableForDevi();
                <?php }?>

                return false;
            }
        });
    }

    $("#btnSearch").on("click", function () {
        // var arrivalDate = $("#txtArrivalDate").val();
        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();
        var GradingDate = $("#GradingDate").val();
        var receivingPlantId = $("#drpReceivingPlantId").val();
        var IsPlantFilter = <?php echo $IsPlantFilter ?>;
        if (!fromDate && !toDate && !GradingDate) {
            alert('Please select either a Date Range or a Grading Date.');
        } else if (!receivingPlantId && IsPlantFilter == 1) {
            alert('Select Plant');
        } else {
            ShowList();
        }
    });
$("#drpReceivingPlantId").on("change", function () {
    $('#tblDataTable').DataTable().destroy();
    $("#divDataList").html("");
});
</script>

</body>

</html>
