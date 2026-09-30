<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterArrivalDateTwo = "T";
// $filterReceivingPlant = "T";
$filterSpecies = "T";
//$filterPurchaseCenter = "T";
$filterTrackCode = "T";
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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php' ?>


                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">

                            <div class="card-body">

                                <div id="divDataList"></div>
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
    async function fnPoStatusReportExcel(tableID, filename = '') {
        var arrivalDate = $("#txtArrivalDate").val().split('-').reverse().join('/');
        var receivingPlant = $("#drpReceivingPlantId option:selected").text();
        var trackCode = $("#drpTrackCode option:selected").text();
        const tableHeading = `Assumed Report_Arrival Date: ${arrivalDate}_Receiving Plant: ${receivingPlant}${trackCode!= "Select" ?"_Track Code: "+trackCode:""}`;
        // Set heights for table elements to auto
        $("#tblDataTable").find(".thead-light tr").height("auto");
        $("#tblDataTable").find(".thead-light th").height("auto");
        $("#tblDataTable").find(".thead-light th div").height("auto");
        $("#tblDataTable").find("tfoot tr").height("auto");
        $("#tblDataTable").find("tfoot td").height("auto");
        $("#tblDataTable").find("tfoot th div").height("auto").css("font-weight", "bold");

        // Initialize workbook and worksheet
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet("Report");

        // Add and style heading
        worksheet.addRow([tableHeading]);
        const headingRow = worksheet.getRow(1);
        headingRow.font = { bold: true, size: 12 };
        headingRow.alignment = { horizontal: "center", vertical: "middle" };

        const numColumns = 16; // Adjust based on your table's number of columns
        worksheet.mergeCells(1, 1, 1, numColumns);

        // Process table rows
        // const table = document.getElementById(tableID).cloneNode(true);
        // const rows = table.getElementsByTagName("tr");
        // const ws_data = [];

        // for (let row = 0; row < rows.length; row++) {
        //     const cells = rows[row].children;
        //     const rowData = [];

        //     for (let col = 0; col < cells.length; col++) {
        //         const cell = cells[col];
        //         rowData.push(cell.innerText.trim());
        //     }
        //     ws_data.push(rowData);
        // }

        // // Add data rows to the worksheet
        // ws_data.forEach((row, idx) => {
        //     const addedRow = worksheet.addRow(row);
        //     // Format columns H to O as numbers, starting from the 4th row
        //     if (idx >= 1) {
        //         for (let col = 1; col <= 14; col++) {
        //             if(col != 10 && col != 11){
        //             const cell = addedRow.getCell(col + 1); // 1-based index
        //             if (cell.value) {
        //                 // Remove commas and convert to number
        //                 const cleanedValue = parseFloat(cell.value.toString().replace(/,/g, ''));
        //                 if (!isNaN(cleanedValue)) {
        //                     cell.value = cleanedValue;
        //                     cell.numFmt = '#,##0.00'; // Numeric format
        //                 }
        //             }
        //         }
        //         }
        //     }
        // });


        
        // Process table separately: <thead>, <tbody>, <tfoot>
const table = document.getElementById(tableID).cloneNode(true);

// --- 1. Headers ---
const theadRows = table.querySelectorAll("thead tr");
theadRows.forEach(row => {
    const excelRow = worksheet.addRow([...row.cells].map(c => c.innerText.trim()));
    excelRow.eachCell(cell => { cell.font = { bold: true }; });
});

// --- 2. Body ---

// const tbodyRows = table.querySelectorAll("tbody tr");
// tbodyRows.forEach((row, idx) => {
//     const excelRow = worksheet.addRow([...row.cells].map(c => c.innerText.trim()));

//     // Format numeric columns H to O (skip 10 and 11)
//     for (let col = 7; col <= 14; col++) { // zero-based index
//         if (col != 9 && col != 10) {
//             const cell = excelRow.getCell(col + 1);
//             if (cell.value) {
//                 const num = parseFloat(cell.value.toString().replace(/,/g, ''));
//                 if (!isNaN(num)) {
//                     cell.value = num;
//                     cell.numFmt = '#,##0.00';
//                 }
//             }
//         }
//     }

    
// });



const tbodyRows = table.querySelectorAll("tbody tr");
tbodyRows.forEach((row, idx) => {
    const excelRow = worksheet.addRow([...row.cells].map(c => c.innerText.trim()));

    // Loop through all numeric columns you want SUM/AVG
    // Example: columns 3 to 15 (adjust based on your table)
    for (let col = 2; col <= 14; col++) { // zero-based
        const cell = excelRow.getCell(col + 1);
        if (cell.value) {
            // Remove commas, %, and convert to number
            let num = parseFloat(cell.value.toString().replace(/,/g, '').replace('%',''));
            if (!isNaN(num)) {
                cell.value = num;
                cell.numFmt = '#,##0.00';
            }
        }
    }
});


// --- 3. Footer totals ---
// const tfootRows = table.querySelectorAll("tfoot tr");
// tfootRows.forEach(row => {
//     const excelRow = worksheet.addRow([...row.cells].map(c => c.innerText.trim()));
//     excelRow.eachCell(cell => { cell.font = { bold: true }; });
// });


// --- 3. Footer totals ---
const tfootRows = table.querySelectorAll("tfoot tr");
tfootRows.forEach(row => {
    const excelRow = worksheet.addRow([...row.cells].map(c => {
        let val = c.innerText.trim();
        let num = parseFloat(val.replace(/,/g,'').replace('%',''));
        return !isNaN(num) ? num : val;
    }));
    excelRow.eachCell(cell => {
        cell.font = { bold: true };
        cell.alignment = { horizontal: 'right' };
    });
});

        

        // Style headers in row 2 (bold text)
        const headerRow = worksheet.getRow(2);
        headerRow.eachCell((cell) => {
            cell.font = { bold: true };
        });

        // Adjust column widths
        worksheet.columns = new Array(numColumns).fill({ width: 15 });

        // Export the workbook
        filename = filename ? `${filename}_From_${arrivalDate}.xlsx` : 'AssumedReport.xlsx';
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
        const url = URL.createObjectURL(blob);

        // Trigger file download
        const link = document.createElement("a");
        link.href = url;
        link.download = filename;
        link.click();
        URL.revokeObjectURL(url);

        // Reset table heights after download
        $("#tblDataTable").find(".thead-light tr").height("0px");
        $("#tblDataTable").find(".thead-light th").height("0px");
        $("#tblDataTable").find(".thead-light th div").height("0px");
        $("#tblDataTable").find("tfoot tr").height("0px");
        $("#tblDataTable").find("tfoot td").height("0px");
        $("#tblDataTable").find("tfoot th div").height("0px");
    }
    function formatToFloat(value) {
        if (value == null || value == "") {
            return 0;
        }
        // Remove commas and parse to float
        return parseFloat(value.replace(/,/g, ''));
    }
    function updateFooterTotals(table) {
        var totalBaseWeight = 0, totalPurchaseWeight = 0, totalPurchaseAmount = 0, totalHLWeight = 0, totalYield = 0;

        // Use rows({ search: 'applied' }) to get only visible (filtered) rows
        table.rows({ search: 'applied' }).every(function () {
            var row = $(this.node());

            totalBaseWeight += formatToFloat(row.find(".tdBaseWeight").text()) || 0;
            totalPurchaseWeight += formatToFloat(row.find(".tdPurchaseWeight").text()) || 0;
            totalPurchaseAmount += formatToFloat(row.find(".tdPurchaseAmount").text()) || 0;
            totalHLWeight += formatToFloat(row.find(".tdHLWeight").text()) || 0;
        });
        // Calculate total yield
        totalYield = ((totalHLWeight/totalPurchaseWeight)*100) || 0;
        // Update the footer
        $("#totalBaseWeight").text(totalBaseWeight.toFixed(2));
        $("#totalPurchaseWeight").text(totalPurchaseWeight.toFixed(2));
        $("#totalPurchaseAmount").text(totalPurchaseAmount.toFixed(2));
        $("#totalHLWeight").text(totalHLWeight.toFixed(2));
        $("#totalYield").text(totalYield.toFixed(2)+" %");


         $("#totalBaseWeight, #totalPurchaseWeight, #totalPurchaseAmount, #totalHLWeight, #totalYield").css("font-weight", "bold");
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
                title: 'Assumed Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                action: function () {
                    fnPoStatusReportExcel('tblDataTable', 'Assumed Report');
                }
            }],
        });
        table.on('search.dt', function () {
            updateFooterTotals(table);
        });
    }

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
        // if (productionPlant != 0) {
        //     $("#drpReceivingPlantId").prop('disabled', true);
        //     $("#drpReceivingPlantId").val(productionPlant).trigger('change');
        // } else {
        //     $("#drpReceivingPlantId").prop('disabled', false);
        //     $("#drpReceivingPlantId").val("").trigger('change');
        // }
        if($("#drpReceivingPlantId").val() && $('#txtArrivalDate').val()){
            fetchTrackIDs()
        }

        $("#drpTrackCode").on("change", function () {
            var species = $(this).find('option:selected').data('species');
            if(species){
                $('#drpSpecies').val(species).trigger('change');
            }
        });
    });

    $("#txtArrivalDate,#drpReceivingPlantId").on("change", function () {
        $('#drpSpecies').val('').trigger('change');
        $("#divDataList").html('');
        fetchTrackIDs();
    });

    function fetchTrackIDs() {
        if(!$("#drpReceivingPlantId").val()){
            alert('Receiving PlantId not Found');
            return
        }
        $.ajax({
            url: 'Ajax/ajax-grading-trackid.php',
            type: 'POST',
            data: {
                arrivalDate: $('#txtArrivalDate').val(),
                plantID: $("#drpReceivingPlantId").val(),
                type: "grading",
            },
            success: function (data) {
                console.log(data);
                if (data.Error) {
                    alert(data.Error);
                } else {
                    var list = "<option value=''>Select</option>";
                    if (data.Data.RmDeHeadingTrackIds) {
                        $.each(data.Data.RmDeHeadingTrackIds, function (index, value) {
                            list += "<option value='" + value.TrackID + "'  data-plantid='" + value.PlantID + "' data-species='"+ value.Species +"'>" + value.TrackCode + "</option>";
                        });
                    }
                    $('#drpTrackCode').select2('destroy');
                    $('#drpTrackCode').html(list);
                    $('#drpTrackCode').select2();
                }
            }
        });
    }

    function ShowList() {
        $.ajax({
            url: 'Ajax/ajax-assumed-report.php',
            type: 'POST',
            data: {
                receivingPlantId: $("#drpReceivingPlantId").val(),
                purchaseCenter: $("#drpPurchaseCenter").val(),
                arrivalDate: $("#txtArrivalDate").val(),
                trackID: $("#drpTrackCode").val(),
                species: $("#drpSpecies").val(),

            },
            success: function (data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }

    $("#btnSearch").on("click", function () {
        var arrivalDate = $("#txtArrivalDate").val();
        var trackCode = $("#drpTrackCode").val();
        var receivingPlantId = $("#drpReceivingPlantId").val();

        if (receivingPlantId == '') {
            alert('Select Receival Plant');
        } else if (arrivalDate == '') {
            alert('Select Arrival Date');
        }
        //else if (trackCode == '') {
        //   alert('Select Track Code');
        // } 
        else {
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
