<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$ClientID = $_SESSION["ClientID"];
if ($ClientID === "CAPLV3") {
    $filterFinancialYear = 'T';
    $filterBatchNo = "T";
}
$filterFromDate = "T";
$filterToDate = "T";
$filterProductionLine = "T";
// $filterReceivingPlant = "T";
$filterButButton = "T";
$filterProductType = "T";
$filterSoakingDate = "T";


$ProductionPlant = $_SESSION['UnitID'];
$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;
$LoadingLinersBasedOnPlant = $ClientConfigData->LoadingLinersBasedOnPlant;

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
<script src="https://cdn.jsdelivr.net/npm/datatables.net-fixedcolumns@4.3.0/js/dataTables.fixedColumns.min.js"></script>
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/datatables.net-fixedcolumns-dt@4.3.0/css/fixedColumns.dataTables.min.css" />

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
    type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>


<script>
    $(document).ready(function () {
        $("#txtFromDate, #txtToDate").on("change", function () {
            const fromDate = $("#txtFromDate").val();
            const toDate = $("#txtToDate").val();
            if (fromDate && toDate) {
                $("#soaking").prop("readonly", true).val('');
               // $("#soaking").css("pointer-events", "none");
               $('#drpYear').prop('disabled',true)
            } else {
                $("#soaking").prop("readonly", false).val('');
               // $("#soaking").css("pointer-events", "auto");
               $('#drpYear').prop('disabled',false)
            }
        });

        $("#soaking").on("change", function () {
            const soakingDate = $(this).val();
            if (soakingDate) {
                $("#txtFromDate, #txtToDate").prop("readonly", true).val('');
               // $("#txtFromDate, #txtToDate").css("pointer-events", "none");
               $('#drpYear').prop('disabled',true)
            } else {
                $("#txtFromDate, #txtToDate").prop("readonly", false).val('');
              //  $("#txtFromDate, #txtToDate").css("pointer-events", "auto");
              $('#drpYear').prop('disabled',false)
            }
        });

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


    //     async function fnPoStatusReportExcel(tableID, filename = '') {
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

    //     const tableHeading = `Soaking Report_Date:( ${fromDate} - ${toDate} ) _Production Plant ${plant}${!lineNumber?.includes("Select") ? " _Line Number" + lineNumber : ""}`;

    //     // Initialize workbook and worksheet
    //     const workbook = new ExcelJS.Workbook();
    //     const worksheet = workbook.addWorksheet("Report");

    //     // Add and style heading
    //     worksheet.addRow([tableHeading]);
    //     const headingRow = worksheet.getRow(1);
    //     headingRow.font = {
    //         bold: true,
    //         size: 12
    //     };
    //     headingRow.alignment = {
    //         horizontal: "center",
    //         vertical: "middle"
    //     };

    //     // Process table rows
    //     const table = document.getElementById(tableID).cloneNode(true);
    //     const rows = table.getElementsByTagName("tr");
    //     const numColumns = rows[0]?.children?.length ?? 21;
    //     worksheet.mergeCells(1, 1, 1, numColumns);

    //     const ws_data = [];
    //     let grandTotalsRow = null;

    //     for (let row = 0; row < rows.length; row++) {
    //         const cells = rows[row].children;
    //         const rowData = [];

    //         for (let col = 0; col < cells.length; col++) {
    //             const cell = cells[col];
    //             rowData.push(cell.innerText.trim());
    //         }

    //         if (rowData[1]?.includes("Totals")) {
    //             grandTotalsRow = rowData;
    //         } else {
    //             ws_data.push(rowData);
    //         }
    //     }
    //     if (grandTotalsRow) ws_data.push(grandTotalsRow);


    //     ws_data.forEach((row, idx) => {
    //     const addedRow = worksheet.addRow(row);
    //     addedRow.eachCell((cell) => {
    //         cell.alignment = {
    //             wrapText: true,
    //             vertical: 'middle'
    //         };
    //     });
    //     // Define columns you want to format as numbers
    //     // Adjust indexes as per your columns
    //    if (idx >= 1) {
    //     // Find index of the 'Grade' column in header row
    //     const gradeColumnIndex = ws_data[0].findIndex(
    //         col => col?.toLowerCase()?.trim() === 'grade'
    //     );

    //     for (let col = 0; col < row.length; col++) {
    //         const cell = addedRow.getCell(col + 1);

    //         // Skip formatting 'Grade' column (preserve values like '45/34')
    //         if (col === gradeColumnIndex) continue;

    //         if (cell.value && typeof cell.value === 'string') {
    //             const cleanedValue = parseFloat(
    //                 cell.value.replace(/,/g, '').replace(/₹/g, '')
    //             );
    //             if (!isNaN(cleanedValue)) {
    //                 cell.value = cleanedValue;
    //                 cell.numFmt = '#,##0.00'; // Ensure numeric format with commas
    //             }
    //         }
    //     }
    // }

    // });

    //     // Style headers in row 2 (bold text)
    //     const headerRow = worksheet.getRow(2);
    //     headerRow.eachCell((cell) => {
    //         cell.font = {
    //             bold: true
    //         };
    //     });

    //     worksheet.columns = [
    //         { width: 10 }, 
    //         { width: 15 },  
    //         { width: 15 },  
    //         { width: 15 }, 
    //         { width: 10 },  
    //         { width: 30 },  
    //         { width: 15 },  
    //         { width: 15 },  
    //         { width: 12 },  
    //         { width: 12 },  
    //         { width: 15 },  
    //         { width: 15 },  
    //         { width: 15 },  
    //         { width: 15 },  
    //         { width: 25 },  
    //         { width: 25 },  
    //         { width: 15 },  
    //         { width: 30 },  
    //         { width: 15 },  
    //         { width: 15 },  
    //         { width: 15 },  
    //     ];

    //     // // Freeze first 8 columns
    //     // worksheet.views = [{
    //     //     state: "frozen",
    //     //     xSplit: 8
    //     // }];


    //     // Export the workbook
    //     filename = filename ? `${filename}_From_${fromDate}_To_${toDate}.xlsx` : 'Soaking_Report.xlsx';
    //     const buffer = await workbook.xlsx.writeBuffer();
    //     const blob = new Blob([buffer], {
    //         type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    //     });
    //     const url = URL.createObjectURL(blob);

    //     // Trigger file download
    //     const link = document.createElement("a");
    //     link.href = url;
    //     link.download = filename;
    //     link.click();
    //     URL.revokeObjectURL(url);

    //     $("#tblDataTable").find(".thead-light  tr").height("0px");
    //     $("#tblDataTable").find(".thead-light  tr th").height("0px");
    //     $("#tblDataTable").find(".thead-light  tr th div").height("0px");

    //     $("#tblDataTable").find("tfoot tr").height("0px");
    //     $("#tblDataTable").find("tfoot tr td").height("0px");
    //     $("#tblDataTable").find("tfoot tr th div").height("0px");
    // }

    function fnPoStatusReportExcel(tableID, filename = '') {
        var fromDate = $("#txtFromDate").val().split('-').reverse().join('/');
        var toDate = $("#txtToDate").val().split('-').reverse().join('/');
        var plant = $("#drpReceivingPlantId option:selected").text();
        var lineNumber = $("#drpLineNumber").text();

        $("#tblDataTable").find(".thead-light  tr").height("auto");
        $("#tblDataTable").find(".thead-light  tr th").height("auto");
        $("#tblDataTable").find(".thead-light  tr th div").height("auto");
        $("#tblDataTable").find("tfoot tr").height("auto");
        $("#tblDataTable").find("tfoot tr td").height("auto");
        $("#tblDataTable").find("tfoot tr th div").height("auto").css("font-weight", "bold");
        var tableSelect = document.getElementById(tableID);
        var clonedTable = tableSelect.cloneNode(true);

        // Move footer rows to the end of tbody
        var tfoot = clonedTable.querySelector('tfoot');
        if (tfoot) {
            var tbody = clonedTable.querySelector('tbody') || clonedTable;
            Array.from(tfoot.rows).forEach(function (row) {
                row.style.fontWeight = 'bold'; // Keep footer bold
                tbody.appendChild(row.cloneNode(true));
            });
            tfoot.remove(); // Remove original tfoot
        }
        var trs = clonedTable.getElementsByTagName('tr');
        for (var i = 0; i < trs.length; i++) {
            trs[i].style.border = '1px solid #EFF2F7';
            var tds = trs[i].getElementsByTagName('td');
            for (var j = 0; j < tds.length; j++) {
                var text = tds[j].innerText.trim();


                if (/^\d+\/\d+$/.test(text)) {
                    var parts = text.split('/').map(Number);
                    if (parts[0] <= 12 && parts[1] <= 31) {
                        tds[j].innerText = "'" + text;
                    }
                }

                tds[j].style.border = '1px solid #EFF2F7';
            }
        }


        // Hide cells with display:none
        var hiddenCells = clonedTable.querySelectorAll('td[style*="display: none"]');
        hiddenCells.forEach(function (cell) {
            cell.style.visibility = 'hidden';
        });

        // Prepare Excel download
        var tableHTML = clonedTable.outerHTML;
        filename = filename ? filename + '_FromDate: ' + fromDate + '_ToDate: ' + toDate + '.xls' : 'excel_data.xls';
        var downloadLink = document.createElement('a');
        document.body.appendChild(downloadLink);

        var dataType = 'data:application/vnd.ms-excel';
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

        // Reset table heights back to 0px
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
                title: 'Soaking Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                action: function () {
                    fnPoStatusReportExcel('tblDataTable', 'Soaking Report');
                }
            }],
            fixedColumns: {
                leftColumns: 8
            }
        });
        table.on('draw.dt', function () {
            let inQuantity = 0;
            let outQuantity = 0;
            let totalSoakingYield = 0;
            let soakingYieldCount = 0;
            $('.tdInQuantity').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    inQuantity += weight;
                }
            });
            $('.tdOutQuantity').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    outQuantity += weight;
                }
            });
            $('.tdSoakingYield').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    totalSoakingYield += weight;
                }
                if (weight > 0) {
                    soakingYieldCount++;
                }
            });

            $('#TotalInQuantity').text(inQuantity.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#TotalOutQuantity').text(outQuantity.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#AverageSoakingYield').text((soakingYieldCount > 0 ? totalSoakingYield / soakingYieldCount : totalSoakingYield).toLocaleString('en-US', {
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
        var soakingDate = $("#soaking").val();
        var productType = $("#drpProductType").val();
        var arrivalID = $("#PurchaseStationID").val();

        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

        var days = daysDiff + 1;

        $.ajax({
            url: 'Ajax/ajax-soaking-report.php',
            type: 'POST',
            data: {
                fromDate: txtStartDate,
                toDate: txtEndDate,
                plantId: drpReceivingPlantId,
                lineNumber: drpLineNumber,
                productType: productType,
                soakingDate: soakingDate,
                arrivalID: arrivalID
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

        // $.ajax({
        //     url: 'Ajax/ajax-soaking-report.php',
        //     type: 'POST',
        //     data: {
        //         fromDate: $txtStartDate,
        //         toDate: txtEndDate,
        //         plantId: drpReceivingPlantId,
        //         lineNumber: drpLineNumber,
        //     },
        //     success: function (data) {
        //         $("#divDataList").html(data);
        //         fnShowDataTable();

        //         return false;
        //     }
        // });
    }

    $("#btnSearch").on("click", function () {
        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();
        var soakingDate = $("#soaking").val();

        if ((fromDate != '' && toDate != '') || soakingDate != '') {
            ShowList();
        } else {
            alert('Select From Date and To Date or Soaking Date')
        }
    })

    $(document).on('change', '#drpYear', function () {
        if ($(this).val() !== "") {
            $("#txtFromDate, #txtToDate,#soaking").val("").prop("readonly", true);
        } else {
            $("#txtFromDate, #txtToDate,#soaking").prop("readonly", false).val('');
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
        if(!yearVal){
            return false;
        }

        $.ajax({
            url: 'Ajax/ajax-soaking-report.php',
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

    $("#txtFromDate, #txtToDate,#soaking").on("change", function () {
        if ($("#txtFromDate").val() || $("#txtToDate").val()) {
            $("#drpYear").val("").prop("disabled", true);
        } else {
            $("#drpYear").prop("disabled", false);
        }
        loadBatchesByDate();
    });

    $('#txtFromDate,#txtToDate,#soaking,#drpYear').on("change", function () {
        let list = "<option value=''>Select</option>";
        list += "<option value=''>No data available</option>";
        $('#PurchaseStationID').html(list).select2();
    })
    function loadBatchesByDate() {
        const fromDate = $("#txtFromDate").val();
        const toDate = $("#txtToDate").val();
        const plantId = $("#drpArrivalPlant").val();
        const soakingDate = $("#soaking").val();

        //if ((!fromDate || !toDate ) || !soaking) return;
        if (!(soakingDate || (fromDate && toDate))) {
            return;
        }

        $.ajax({
            url: 'Ajax/ajax-soaking-report.php',
            type: 'POST',
            data: {
                'DataType': 'getArrivalsByDate',
                fromDate: fromDate,
                toDate: toDate,
                PlantID: plantId,
                soakingDate : soakingDate
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
