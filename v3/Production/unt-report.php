
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once __DIR__.'/../config/BaseUrl.php';
$BaseUrlConfig = new BaseUrl;
$BaseUrlConfig = $BaseUrlConfig->getBaseUrl(true);
$folderArr = explode('/',$BaseUrlConfig->dir);
//echo '<pre>';print_r(explode('/',$BaseUrlConfig->dir));die;
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$PageTitleFolder = !empty($folderArr[2])?$folderArr[2]:'';
$PageTitleLink = $BaseUrlConfig->dir;
$PageTitle ="Untreated Report";
$filterFromDate = "T";
$filterToDate = "T";
$filterReceivingPlant = "T";
$filterProductType = "T";
$filterButButton = "T";

 $ProductionPlant = $_SESSION['UnitID'];
// $ClientConfigData = $_SESSION['ClientConfigData'];
// $IsPlantFilter = $ClientConfigData->IsPlantFilter;
// if ($IsPlantFilter == 1) {
//     $filterReceivingPlant = "T";
// }
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

                <?php //include_once ROOT . '/includes/page-header.php' ?>
                <?php include($IncludesPath . "page-title.php"); ?>
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
                                // if ($IsPlantFilter == 0) {
                                //     echo '<input type="hidden" id="drpReceivingPlantId" />';
                                // }
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
    
    $(document).ready(function() {
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
        var plant = $("#drpReceivingPlantId option:selected").text();
       

        $("#tblDataTable").find(".thead-light  tr").height("auto");
        $("#tblDataTable").find(".thead-light  tr th").height("auto");
        $("#tblDataTable").find(".thead-light  tr th div").height("auto");

        $("#tblDataTable").find("tfoot tr").height("auto");
        $("#tblDataTable").find("tfoot tr td").height("auto");
        $("#tblDataTable").find("tfoot tr th div").height("auto").css("font-weight", "bold");

        //const tableHeading = `Untreated Report_Date:( ${fromDate} - ${toDate} ) _Production Plant ${plant}${!lineNumber?.includes("Select")?" _Line Number"+lineNumber:""}`;
        const tableHeading = `Untreated Report_Date:( ${fromDate} - ${toDate} ) _Production Plant ${plant}`;

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
        const numColumns = rows[0]?.children?.length ?? 21;; // Adjust based on your table's number of columns
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
            } else{
                ws_data.push(rowData);
            }
           
        }
        if (grandTotalsRow) ws_data.push(grandTotalsRow);
        // Add data rows to the worksheet
        ws_data.forEach((row, idx) => {
            const addedRow = worksheet.addRow(row);
            // Enable word wrapping
            addedRow.eachCell((cell) => {
                cell.alignment = { wrapText: true, vertical: 'middle' }; 
            });

            // Format columns H to O as numbers, starting from the 4th row
            if (idx >= 1) {
                for (let col = 11; col < numColumns-1; col++) {
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
                maxWidth = Math.min(Math.max(maxWidth, cellValue.length + 2), 20); // Adjust width dynamically with cutoff at 30
            });
            column.width = maxWidth;
        });

        // Export the workbook
        filename = filename ? `${filename}_From_${fromDate}_To_${toDate}.xlsx` : 'UNT_Report.xlsx';
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
             iDisplayLength: 20,
            buttons: [
            //     {
            //     extend: 'excel',
            //     text: 'Download Excel',
            //     className: 'btnExcelDataTable btn-left-align',
            //     title: 'Untreated Report <?php echo date("d-m-Y") ?>',
            //     footer: true,
            //     exportOptions: {
            //         columns: ':visible'
            //     },
            //     action: function () {
            //         fnPoStatusReportExcel('tblDataTable', 'Untreated Report');
            //     }
            // }
            {
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: 'Untreated Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }
        ],
        });
        table.on('draw.dt', function () {
            let inQuantity = 0;
            let outQuantity = 0;
            let yieldv = 0;
            let count = 0;
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
             $('.tdYield').each(function () {
                // Parse the text content as a float, ignoring any commas
                const value = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(value)) {
                    yieldv += value;
                    count++;
                }
            });
            const averageYield = count > 0 ? (yieldv / count) : 0;
            
           
            $('#TotalInQuantity').text(inQuantity.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#TotalOutQuantity').text(outQuantity.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#TotalYield').text(averageYield.toLocaleString('en-US', {
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
        var productType = $("#drpProductType").val();

        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
        
        var days = daysDiff + 1;

        $.ajax({
            url: 'Ajax/ajax-unt-report.php',
            type: 'POST',
            data: {
                fromDate: txtStartDate,
                toDate: txtEndDate,
                plantId: drpReceivingPlantId,
                productType: productType,

            },
            success: function(data) {
                if(days > 31){
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    if(!$("#divDataList").text().includes("No data available in table")){
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
