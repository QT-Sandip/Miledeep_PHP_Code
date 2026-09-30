<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterfromDateOne = "T";
$filtertoDateOne = "T";
$filterButtomButton = "T";
$filterProductionPlant = "T";
$ProductionPlant = $_SESSION['UnitID'];

$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;

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
                                    <!-- -->
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
<!-- Include ExcelJS & FileSaver -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<script>

    $(document).ready(function () {
        var productionPlantId = <?php echo $ProductionPlant ?>;
        var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram
        if (productionPlantId != 0 || IsPlantFilter == 0) {
            $("#drpArrivalPlant").prop('disabled', true);
            if (IsPlantFilter == 0) {
                $("#drpArrivalPlant").val(productionPlantId);
            } else {
                $("#drpArrivalPlant").val(productionPlantId).trigger('change');
            }
        } else {
            $("#drpArrivalPlant").prop('disabled', false);
        }

    });
    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bInfo: false,
            bSort: false,
            dom: 'Blfrtip',
            // dom: '<"top-left"Blfrtip>',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Bap Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: function (data, row, column, node) {
                            return $(node).attr('data-value') ? $(node).attr('data-value') : data.replace(/,/g, '');
                        }
                    }
                },
                action: function () {
                    exportToExcel();
                }
            }],
        });
    }

    async function exportToExcel() {
        let table = $('#tblDataTable').DataTable();
        let workbook = new ExcelJS.Workbook();
        let worksheet = workbook.addWorksheet("Report");

        // Get current date
        let currentDate = new Date().toLocaleDateString('en-GB').replace(/\//g, '-'); // Format: DD-MM-YYYY
        let title = `Bap Report ${currentDate}`;

        // Get table headers
        let headers = [];
        $('#tblDataTable thead th').each(function () {
            headers.push($(this).text().trim());
        });

        let totalCols = headers.length; // Dynamic column count
        let lastCol = String.fromCharCode(64 + totalCols); // Example: If totalCols = 22, lastCol = 'V'

        // **(1) Add Title in Row 1 and Merge Cells**
        let titleCell = worksheet.getCell(`A1`);
        titleCell.value = title;
        titleCell.alignment = {
            horizontal: "center",
            vertical: "middle"
        };
        titleCell.font = {
            bold: true,
            size: 14
        };
        worksheet.mergeCells(`A1:${lastCol}1`);

        // **(2) Add Headers in Row 2 (Bold & Centered)**
        worksheet.addRow(headers);
        let headerRow = worksheet.getRow(2);
        headerRow.font = {
            bold: true
        };
        headerRow.alignment = {
            horizontal: "center",
            vertical: "middle"
        };

        // **(3) Add Table Data**
        table.rows().every(function () {
            let rowData = [];
            $(this.node()).find('td').each(function () {
                let rawValue = $(this).attr('data-value') ? $(this).attr('data-value') : $(this).text().replace(/,/g, '');

                if ($.isNumeric(rawValue)) {
                    rowData.push(Number(rawValue)); // Store as number
                } else {
                    rowData.push(rawValue);
                }
            });
            worksheet.addRow(rowData);
        });

        // **(4) Apply Number Formatting (Comma Separator)**
        worksheet.columns.forEach((col, index) => {
           // col.numFmt = "#,##0.000"; // Apply comma formatting to numbers
            if (index === 0 || index === 11) {
                // Integer format
                col.numFmt = "#,##0";
            } else if (index === 16) {
                // Text format
                col.numFmt = "@";
            } else {
                col.numFmt = "#,##0.000";
            }
            col.width = Math.max(headers[index]?.length + 5, 10); // Adjust column width
        });
       

        // **(5) Generate & Download File**
        let buffer = await workbook.xlsx.writeBuffer();
        let blob = new Blob([buffer], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        });
        saveAs(blob, title + ".xlsx");
    }

    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
            "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>"
        )
            .insertAfter("#tblDataTable_filter label");

        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }

    function ShowList(pageNo, searchkey, loadAll) {
        $.ajax({
            url: 'Ajax/ajax-bap-report.php',
            type: 'POST',
            data: {
                fromDate: $("#txtFromDate").val(),
                toDate: $("#txtToDate").val(),
                plantID: $("#drpArrivalPlant").val(),
                pageNo: pageNo,
                searchKey: searchkey,
                loadAll: loadAll,
            },
            success: function (data) {
                fnSuccess(data);
                return false;
            }
        });
    }

    $(document).on("click", "#btnMoreSearch", function () {
        var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", searchkey, "0");
        }
    });

    $(document).on("click", "#btnLoadAll", function () {
        ShowList("1", "", "1");
    });

    $(document).on("click", ".btnPageno", function () {
        var pageno = $(this).attr("data-pageno");
        ShowList(pageno, "", "0");
    });

    $("#btnSearch").on("click", function () {
        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();
        if (fromDate == '') {
            alert('Select From Date');
        } else if (toDate == '') {
            alert('Select To Date');
        } else {
            ShowList("1", "", "0");
        }
    })
</script>

</body>

</html>