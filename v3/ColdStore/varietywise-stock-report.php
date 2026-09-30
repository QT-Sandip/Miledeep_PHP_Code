<?php
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$filterColdStores = "T";
$filterProductionDate = "T";
$filterTypeProduct = "T";
$filterSpecies = "T";
$filterFreezingType = "T";
//$filterVarietyOnlyTwo = "T";
$filterShortName = "T";
$filterSoaking = "T";
$filterPckgStyle = "T";
$filterGrade = "T";
$filterType = "T";
$buttomButton = "T";

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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php'; ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <!-- <input type="hidden" id="hdnBlocks" value="<?php echo htmlspecialchars(json_encode($resBlockData), ENT_QUOTES, 'UTF-8'); ?>" /> -->
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>


<script>
    function fnPoStatusReportExcel(tableID, filename = '') {
        var productionDate = $("#txtProductionDate").val().split('-').reverse().join('/');
        var coldStore = $("#drpColdStore option:selected").text();
        var type = $("#drpType option:selected").text();

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
        var reportNameRow = document.createElement('tr');
        reportNameRow.innerHTML = `
        <td colspan="${clonedTable.querySelectorAll('tr:first-child th').length}" style="text-align: left; font-weight: bold;">
            Daily Stock Report_${type} wise_ProductionDate: ${productionDate} _Cold Store: ${coldStore}
        </td>`;
        clonedTable.querySelector('thead').insertBefore(reportNameRow, clonedTable.querySelector('thead').firstChild);

        var trs = clonedTable.getElementsByTagName('tr');
        for (var i = 0; i < trs.length; i++) {
            trs[i].style.border = '1px solid #eff2f7';
            var tds = trs[i].getElementsByTagName('td');
            for (var j = 0; j < tds.length; j++) {
                if (/^\d+\/\d+$/.test(tds[j].innerText)) {
                    tds[j].innerText = "'" + tds[j].innerText;
                }
                tds[j].style.border = '1px solid #eff2f7';
            }
        }

        var hiddenCells = clonedTable.querySelectorAll('td[style*="display: none"]');
        hiddenCells.forEach(function(cell) {
            cell.style.visibility = 'hidden';
        });

        var tableHTML = clonedTable.outerHTML;

        filename = filename ? filename + '_ProductionDate' + productionDate + '.xls' : 'excel_data.xls';

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
                title: 'Receiving Overall Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                action: function() {
                    fnPoStatusReportExcel('tblDataTable', 'Daily Stock Report');
                }
            }],
        });
    }

    $(document).ready(function() {

        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpColdStore").prop('disabled', true);
            $("#drpColdStore").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');
        } else {
            $("#drpColdStore").prop('disabled', false);
            $("#drpColdStore").val("").trigger('change');
        }

    });

    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }

    function ShowList() {

        $.ajax({
            url: 'Ajax/ajax-varietywise-stock-report.php',
            type: 'POST',
            data: {
                coldStores: $.trim($("#drpColdStore").val()),
                productionDate: $.trim($("#txtProductionDate").val()),
                typeProduct: $.trim($("#drpTypeProduct").val()),
                typeSpecies: $.trim($("#drpSpecies").val()),
                freezingType: $.trim($("#drpFreezingType").val()),
                typeVariety: $.trim($("#drpVariety").val()),
                typeSoaking: $.trim($("#drpSoaking").val()),
                pakingStyle: $.trim($("#drpPckgStyle").val()),
                typeGrade: $.trim($("#drpGrade").val()),
                type: $.trim($("#drpType").val()),
            },
            success: function(data) {
                fnSuccess(data);
                return false;
            }
        });
    }
    $("#btnSearch").on("click", function() {
        ShowList();
    });
</script>

</body>

</html>