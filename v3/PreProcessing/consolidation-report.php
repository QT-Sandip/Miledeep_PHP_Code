<?php
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

//$filterArrivalDateTwo = "T";
$filterFromDate = "T";
$filterToDate = "T";
// $filterReceivingPlant = "T";
// $filterMachineID = "T";
$filterButButton = "T";
// $filterProductType1 = "T";
// $filterGrade = "T";

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
        .btn-group-vertical>.btn, .btn-group>.btn {
            width: 200px;
        }
        div.dataTables_wrapper div.dataTables_filter input, .dt-search-input {
            font-size: 14px !important;
            width: 220px !important;
            min-height: 45px !important;
            height: 45px !important;
            border-radius: 10px !important;
            border: 1.5px solid #4f46e58f !important;
            padding: 10px 14px 10px 44px !important;
            transition: all .25s ease;
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
        $("#txtToDate").parent().after(`
            <div class="col col-md-3 mb-3">
                <label for="drpType">Type</label>
                <select id="drpType" class="form-control select2">
                    <option value="1">Kgs</option>
                    <option value="2">Slabs</option>
                </select>
            </div>
        `);
        $("#drpType").select2();

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

    });

    function fnShowDataTableForDeviBk(){
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
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"l f B><"col-md-6">>rtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Consolidation Report-'+ $("#txtArrivalDate").val(),
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                /* action: function () {
                    fnPoStatusReportExcel('tblDataTable', 'Grading Overall Report',claintStatus);
                } */
            }],
            /* fixedColumns: {
                leftColumns: 3
            } */
            
        });
    }
    function fnShowDataTableForDevi(){
        var claintStatus = 0;
        var tableId = '#table2';
        $('#table1').DataTable({
            scrollY: "600px",
            scrollX: true, 
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            scrollCollapse: true,
            bInfo: false,   
            bSort: false,
            searching: false,
            fixedColumns: {
                leftColumns: 3
            }
        })
        // $('#table2').DataTable()
        // return;
        var table = $(tableId).DataTable({
            scrollY: "600px",
            scrollX: true, 
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            scrollCollapse: true,
            bInfo: false,   
            bSort: false,
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"l f B><"col-md-6">>rtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Consolidation Report-'+ $("#txtArrivalDate").val(),
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                action: async function () {

                    let workbook = new ExcelJS.Workbook();
                    let worksheet = workbook.addWorksheet("Report");

                    // Export first table from column A

                    let from = new Date($('#txtFromDate').val());

                    let fromday = String(from.getDate()).padStart(2, '0');
                    let frommonth = String(from.getMonth() + 1).padStart(2, '0');
                    let fromyear = from.getFullYear();

                    let formattedfromDate = `${fromday}-${frommonth}-${fromyear}`;

                    let to = new Date($('#txtToDate').val());

                    let today = String(to.getDate()).padStart(2, '0');
                    let tomonth = String(to.getMonth() + 1).padStart(2, '0');
                    let toyear = to.getFullYear();

                    let formattedtoDate = `${today}-${tomonth}-${toyear}`;


                    var table1Heading = 'Grading Report_FromDate:'+formattedfromDate+'_ToDate:'+formattedtoDate+'_Receiving Plant: '+$("#drpReceivingPlantId option:selected").text();
                   // table1Heading = '';
                    exportTable("#table1", worksheet, 1, table1Heading);

                    // Export second table from column H (change if needed)
                    var table2Heading = 'Consolidation Report-'+formattedtoDate;
                    exportTable("#table2", worksheet, 7, table2Heading);

                    // Auto width
                    worksheet.columns.forEach(column => {
                        let maxLength = 10;
                        column.eachCell({ includeEmpty: true }, cell => {
                            let len = cell.value ? cell.value.toString().length : 0;
                            if (len > maxLength) maxLength = len;
                        });
                       // column.width = maxLength + 2;
                    });

                    let buffer = await workbook.xlsx.writeBuffer();

                    saveAs(
                        new Blob([buffer], {
                            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                        }),
                        "Consolidation Report.xlsx"
                    );
                }
            }],
            fixedColumns: {
                leftColumns: 3
            }
            
        });
    }
    function exportTable(tableId, worksheet, startCol, tableHeading) {

        let table = $(tableId).DataTable();

        // Title
        worksheet.getCell(1, startCol).value = tableHeading;//1st row
        worksheet.mergeCells(1, startCol, 1, startCol + 5);
        worksheet.getCell(1, startCol).font = {
            bold: true,
            size: 14
        };

        // Headers
        $(tableId + " thead th").each(function (i) {

            let cell = worksheet.getCell(2, startCol + i);

            cell.value = $(this).text().trim();
            cell.font = {
                bold: true
            };
        });

        // Data
        let row = 3;

        table.rows({ search: 'applied' }).every(function () {

            let col = startCol;

            $(this.node()).find("td").each(function () {

                let value = $(this).attr("data-value") ?? $(this).text().trim().replace(/,/g, "");

                worksheet.getCell(row, col).value =
                    $.isNumeric(value) ? Number(value) : value;

                col++;
            });

            row++;
        });
    }

    function ShowList() {
        var IsPlantFilter = <?php echo $IsPlantFilter ?>;
        var Grade = '';
        if($("#drpGrade option:selected").text() != 'All'){
            Grade = $("#drpGrade option:selected").text();
        }
        $.ajax({
            url: 'Ajax/ajax-consolidation-report.php',
            type: 'POST',
            data: {
                //arrivalDate: $("#txtArrivalDate").val(),
                receivingPlantId: $("#drpReceivingPlantId").val(),
                typeProduct: $("#drpProductType").val(),
                grade: Grade,
                IsPlantFilter: IsPlantFilter,
                FromDate : $('#txtFromDate').val(),
                ToDate : $('#txtToDate').val(),
                drpType : $('#drpType').val(),
            },
            success: function (data) {
                if(data.Error){
                    alert(data.Error);
                    return;
                }
                $("#divDataList").html(data);
                fnShowDataTableForDevi();

                return false;
            }
        });
    }

    $("#btnSearch").on("click", function () {
        var arrivalDate = $("#txtArrivalDate").val();
        var receivingPlantId = $("#drpReceivingPlantId").val();
        var IsPlantFilter = <?php echo $IsPlantFilter ?>;

        var txtFromDate = $("#txtFromDate").val();
        var txtToDate = $("#txtToDate").val();
        /* if (!arrivalDate) {
            alert('Select Arrival Date');
        } */        
        if (!txtFromDate && !txtToDate) {
            alert('From Date and To Date are required.');
        } else if (!receivingPlantId && IsPlantFilter == 1) {
            alert('Select Plant');
        } else {

            let from = new Date(txtFromDate);
            let to = new Date(txtToDate);

            let diff = (to - from) / (1000 * 60 * 60 * 24); // Days difference

            if (diff < 0) {
                alert("To Date should be greater than or equal to From Date.");
                $("#txtToDate").val("");
                return;
            }

            if (diff > 30) {
                alert("The date range cannot exceed 30 days.");
                $("#txtToDate").val("");
                return;
            }
            ShowList();
        }
    });
</script>

</body>

</html>
