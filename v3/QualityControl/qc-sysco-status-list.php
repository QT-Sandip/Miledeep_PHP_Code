<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterProductionPlant = "T";
$filterYear = "T";
$filterConsigneeOne = "T";
//$filterButtomButton = "T";
$filterButtonOneForQc = "T";

$ProductionPlant = $_SESSION['UnitID'];
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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php'
                    ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">
                                    <!--  -->
                                </div>
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

<!-- jsPDF and jsPDF-AutoTable libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>


<script>
    $(document).ready(function () {
        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpArrivalPlant").prop('disabled', true);
            $("#drpArrivalPlant").val(productionPlant).trigger('change');
        } else {
            $("#drpArrivalPlant").prop('disabled', false);
            $("#drpArrivalPlant").val("").trigger('change');
        }
    });

    function fnPoStatusReportExcel(tableID, filename = '') {
        $("#tblDataTable").find(".thead-light  tr").height("auto");
        $("#tblDataTable").find(".thead-light  tr th").height("auto");
        $("#tblDataTable").find(".thead-light  tr th div").height("auto");

        $("#tblDataTable").find("tfoot tr").height("auto");
        $("#tblDataTable").find("tfoot tr td").height("auto");
        $("#tblDataTable").find("tfoot tr th div").height("auto").css("font-weight", "bold");

        var downloadLink;
        var dataType = 'data:application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);

        var clonedTable = tableSelect.cloneNode(true);

        var trs = clonedTable.getElementsByTagName('tr');
        for (var i = 0; i < trs.length; i++) {
            trs[i].style.border = '1px solid #eff2f7';
            var tds = trs[i].getElementsByTagName('td');
            for (var j = 0; j < tds.length; j++) {
                tds[j].style.border = '1px solid #eff2f7';
            }
        }

        var hiddenCells = clonedTable.querySelectorAll('td[style*="display: none"]');
        hiddenCells.forEach(function (cell) {
            cell.style.visibility = 'hidden';
        });

        var tableHTML = clonedTable.outerHTML;

        downloadLink = document.createElement('a');
        document.body.appendChild(downloadLink);

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

        $("#tblDataTable").find(".thead-light  tr").height("0px");
        $("#tblDataTable").find(".thead-light  tr th").height("0px");
        $("#tblDataTable").find(".thead-light  tr th div").height("0px");

        $("#tblDataTable").find("tfoot tr").height("0px");
        $("#tblDataTable").find("tfoot tr td").height("0px");
        $("#tblDataTable").find("tfoot tr th div").height("0px");
    }


    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            ordering: false,
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
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                action: function (e, dt, node, config) {
                    // Define your custom AJAX request for downloading Excel
                    $.ajax({
                        url: 'Ajax/ajax-sysco-excel-download.php', // Replace with your file
                        type: 'POST',
                        data: {
                            plant: $("#drpArrivalPlant").val(),
                            year: $("#drpYear").val(),
                            consigneeList: $("#drpConsigneeList").val(),
                        },
                        success: function (response) {
                            // Assuming the response contains the Excel file as a blob
                            const blob = new Blob([response], {
                                type: 'application/vnd.ms-excel'
                            });
                            const link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = 'sysco-psip-status list.xls';
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        },
                        error: function (xhr, status, error) {
                            console.error('Error downloading the file:', error);
                        }
                    });
                }
            }],
            // buttons: [{
            //         extend: 'excel',
            //         text: 'Download Excel',
            //         className: 'btnExcelDataTable btn-left-align',
            //         title: 'Sysco PSIP Status Report <?php echo date("d-m-Y") ?>',
            //         footer: true,
            //         exportOptions: {
            //             columns: ':visible'
            //         },
            //         action: function() {
            //             fnPoStatusReportExcel('tblDataTable', 'Sysco PSIP Status Report');
            //         }
            //     },
            // {
            //     extend: 'pdf',
            //     text: 'Download PDF',
            //     className: 'btnPdfDataTable btn-left-align',
            //     title: 'Sysco PSIP Status Report <?php echo date("d-m-Y") ?>',
            //     // action: function(e, dt, node, config) {
            //     //     fnPoStatusReportPDF('tblDataTable', 'Sysco PSIP Status Report');
            //     // }
            // }
            // ],
        });
    }


    function ShowList() {

        var consignee = $('#drpConsigneeList').val();
        var consignees = consignee.join(",");
        // alert(consignees);
        $.ajax({
            url: 'Ajax/ajax-qc-sysco-status-list.php',
            type: 'POST',
            data: {
                plant: $("#drpArrivalPlant").val(),
                year: $("#drpYear").val(),
                // consignee: $("#drpConsigneeList").val(),
                consigneeList: consignees,
            },
            success: function (data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }

    // $(document).ready(function() {

    //     // $("#drpConsigneeList").select2();
    // });

    $("#btnSearch").on("click", function () {
        ShowList();
    })

    $("#btnPrint").on("click", function () {
        var arrivalPlant = $("#drpArrivalPlant").val();
        if (arrivalPlant != '') {
            var arrivalPlantName = $("#drpArrivalPlant option:selected").text();
        } else {
            var arrivalPlantName = '';
        }
        var year = $("#drpYear").val();
        var consigneeList = $("#drpConsigneeList").val();

        var link = 'Reports/D1/sysco-psip-status?ProductionPlant=' + arrivalPlant + '&Year=' + year + '&BuyerID=' + consigneeList + '&ArrivalPlantName=' + arrivalPlantName;
        window.open(link, '_blank');
    });
</script>

</body>

</html>