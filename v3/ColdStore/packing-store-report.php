
<?php
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$filterPlant = "T";
$filterColdStores = "T";
//$filterfromDate = "T";  // txtFromDate
$filtertoDate = "T"; // txtToDate

$filterSideButton = "T";

$ProductionPlant = $_SESSION['UnitID'];
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$IsCsSelectionDisable = $ArrayClientConfigData['IsCsSelectionDisable'];
// echo $ProductionPlant;
// exit;
$resGroups = [
    (object) ['Name' => 'Mcs'],
    (object) ['Name' => 'Pouches'],
    (object) ['Name' => 'Stickers'],
    (object) ['Name' => 'Tape Rolls'],
    (object) ['Name' => 'Spacers'],
    (object) ['Name' => 'Count Slips'],
];
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
<?php 
if(empty($ProductionPlant) && count($resColdStores) == 1){
    $ProductionPlant = 1;
}
?>
<?php include_once ROOT . '/includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>


<script>
    $(document).ready(function() {
        $('label[for="txtToDate"]').text('Inward Date');
        $('label[for="drpColdStore"]').text('Packing Store');
        var productionPlant = "<?php echo isset($ProductionPlant) ? $ProductionPlant : ''; ?>";
        var isCsSelectionDisable = <?php echo $IsCsSelectionDisable ?>;
        if (productionPlant != 0) {
            $("#drpPlant").prop('disabled', true);
            $("#drpPlant").val(productionPlant).trigger('change');
        } else {
            $("#drpPlant").trigger('change');
            $("#drpPlant").prop('disabled', false);
        }

        if (productionPlant != 0) {
            if (isCsSelectionDisable == 1) {
                $("#drpColdStore").prop('disabled', true);
            }
            $("#drpColdStore").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');

        } else {
            $("#drpColdStore").prop('disabled', false);
            $("#drpColdStore").val("").trigger('change');
        }
        $('#drpPDCategory').parent('div').css('display','none')

        $('#txtToDate').parent().after(`
                            <div class="col-md-6 col-lg-3 mb-3">
                                <label for="drpGroup" class="form-label">Group<span style="color:red"></span></label>
                                <select class="drpSelect form-control group" name="drpGroup" id="drpGroup">
                                    <option value=""> Select</option>
                                    <?php foreach ($resGroups as $Item) { ?>
                                    <option value="<?php echo $Item->Name ?>">
                                        <?php echo $Item->Name ?>
                                    </option>
                                <?php } ?>
                                </select>
                            </div>
                            `);
        $('#drpGroup').select2();
    });


    function fnPoStatusReportExcel(tableID, filename = '') {
         var fromDate = '';
       // var fromDate = $("#txtFromDate").val().split('-').reverse().join('/');
        var toDate = $("#txtToDate").val().split('-').reverse().join('/');
        var plant = $("#drpPlant option:selected").text();

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
        <td colspan="23" style="text-align: center; font-weight: bold;">
            Packing Store Report_Date ( ${fromDate} - ${toDate} ) _Production Plant ${plant}
        </td>`;
        clonedTable.querySelector('thead').insertBefore(reportNameRow, clonedTable.querySelector('thead').firstChild);

        var trs = clonedTable.getElementsByTagName('tr');
        for (var i = 0; i < trs.length; i++) {
            trs[i].style.border = '1px solid #eff2f7';
            var tds = trs[i].getElementsByTagName('td');
            for (var j = 0; j < tds.length; j++) {
                tds[j].style.border = '1px solid #eff2f7';
            }
        }

        var hiddenCells = clonedTable.querySelectorAll('td[style*="display: none"]');
        hiddenCells.forEach(function(cell) {
            cell.style.visibility = 'hidden';
        });

        var tableHTML = clonedTable.outerHTML;

        filename = filename ? filename + '_Date (' + fromDate + ' - ' + toDate + '.xls' : 'excel_data.xls';

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
                title: 'Packing Store  Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                /* action: function() {
                    fnPoStatusReportExcel('tblDataTable', 'Packing Store Report');
                } */
            }],
        });
    }

    function ShowList() {
        $.ajax({
            url: 'Ajax/ajax-packing-store-report.php',
            type: 'POST',
            data: {
                plant: $("#drpPlant").val(),
                fromDate: $("#txtFromDate").val(),
                toDate: $("#txtToDate").val(),
                ColdStore: $("#drpColdStore").val(),
                drpGroup: $("#drpGroup").val(),
            },
            success: function(data) {
                $("#divDataList").html(data);
                fnShowDataTable();

                return false;
            }
        });
    }

    $("#btnSearch").on("click", function() {
        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();

        if (fromDate != '' && toDate != '') {
            ShowList();
        } else {
            alert('Select From Date and To Date');
        }
    })
</script>

</body>

</html>