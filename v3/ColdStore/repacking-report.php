<?php
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$excludeList = array("Floors","StoreRacks","Stock","ProductGroup","Exporters","Consignee","Processor","Shifts","OptType","OutType","StockAvailableVariety","StockInVarietyList","GeneralCategories","Status","TenantType","ProductionLines","BrandType","Tranction","ProductForm","Preparation","GlazeType","ShipoutPoNumber","ProductionPlant","PoNumberNew","TransactionTypesNew","InwardGroups");
$filterPlant = "T"; 
$filterfromDate = "T"; 
$filtertoDate = "T"; 
$filterSpecies = "T"; 
$filterGrade = "T"; 
$filterShortName = "T";
$filterTreatment = "T";  
$filterPckgStyle = "T";  
$filterSideButton = "T";
$filterGroup = "T";

$ProductionPlant = $_SESSION['UnitID'];
// echo $ProductionPlant;
// exit;
?>

<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>
    <style>
        .table tbody td, table.table-bordered.dataTable tbody th, table.table-bordered.dataTable tbody td
 {
    border-color: #edf0f4;
    vertical-align: middle;
    color: #000000;
    font-size: 14px;
    font-weight: 400;
    border-bottom: 1px solid #ccc;
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

<?php include_once ROOT . '/includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>


<script>
    $(document).ready(function() {
        var selectedValues = ["Repacking","Repacking Out", "Conversion Out"];
        $("#drpGroup option").each(function () {
            // if ($(this).val() == '') {
            //     return true;
            // }
            if ($.inArray($(this).val(), selectedValues) === -1) {
                $(this).remove();
            }
        });
        var productionPlant = <?php echo $ProductionPlant ?>;

        if (productionPlant != 0) {
            $("#drpPlant").prop('disabled', true);
            $("#drpPlant").val(productionPlant).trigger('change');
        } else {
            $("#drpPlant").prop('disabled', false);
            $("#drpPlant").val("").trigger('change');
        }
    });


    function fnPoStatusReportExcel(tableID, filename = '') {
        var fromDate = $("#txtFromDate").val().split('-').reverse().join('/');
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
            Thawing Report_Date ( ${fromDate} - ${toDate} ) _Production Plant ${plant}
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
                title: 'Thawing Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                action: function() {
                    // fnPoStatusReportExcel('tblDataTable', 'Thawing Report');
                    DownloadExcel ();
                }
            }],
        });

        table.on('draw.dt', function () {
    let outMcs = 0;
    let outLooseBags = 0;
    let kgs = 0;
    let balanceKgs = 0; 

    let mcs = 0;
    let looseBags = 0;
    let totalKgs = 0;

    $('.out-mcs').each(function () {
        const value = $(this).text().replace(/,/g, '').trim();
        const weight = parseFloat(value) || 0;
        outMcs += weight;
    });

    $('.out-loose-bags').each(function () {
        const value = $(this).text().replace(/,/g, '').trim();
        const weight = parseFloat(value) || 0;
        outLooseBags += weight;
    });

    $('.total-kgs').each(function () {
        const value = $(this).text().replace(/,/g, '').trim();
        const weight = parseFloat(value) || 0;

        console.log("KGs Value:", value, "Parsed:", weight); // Debugging
        kgs += weight;
    });

    $('.balance-kgs').each(function () {
        const value = $(this).text().replace(/,/g, '').trim();
        const weight = parseFloat(value) || 0;
        balanceKgs += weight;
    });

    $('.actual-mcs').each(function () {
        const value = $(this).text().replace(/,/g, '').trim();
        const weight = parseFloat(value) || 0;
        mcs += weight;
    });

    $('.actual-loosebags').each(function () {
        const value = $(this).text().replace(/,/g, '').trim();
        const weight = parseFloat(value) || 0;
        looseBags += weight;
    });

    $('.total-kgs').each(function () {
        const value = $(this).text().replace(/,/g, '').trim();
        const weight = parseFloat(value) || 0;
        totalKgs += weight;
    });

    // Update the footer with correctly formatted totals
    $('#TotalMcs').text(outMcs.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }));

    $('#TotalLooseBags').text(outLooseBags.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }));

    $('#TotalOutKGs').text(kgs.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }));

    $('#TotalBalanceKGs').text(balanceKgs.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }));
    $('#ProductionMcs').text(mcs.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }));
    $('#ProductionLoose').text(looseBags.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }));
    $('#ProductionKGS').text(totalKgs.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }));
});


    }
    function DownloadExcel (){
        var plan = $("#drpPlant").val();
        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();
        var species = $("#drpSpecies").val();
        var grade = $("#drpGrade").val();
        var variety = $("#drpVariety").val();
        var treatment = $("#drpSoakingTreatment").val();
        var packingStyle = $("#drpPckgStyle").val();
        var url = `Reports/CsRepackingReport.php?plant=${plan}&fromDate=${fromDate}&toDate=${toDate}&species=${species}&grade=${grade}&variety=${variety}&treatment=${treatment}&packingStyle=${packingStyle}`;
        window.open(url, '_blank');
    }
    function ShowList() {
        $.ajax({
            url: 'Ajax/ajax-repacking-report.php',
            type: 'POST',
            data: {
                plant: $("#drpPlant").val(),
                fromDate: $("#txtFromDate").val(),
                toDate: $("#txtToDate").val(),
                species: $("#drpSpecies").val(),
                grade: $("#drpGrade").val(),
                variety: $("#drpVariety").val(),
                treatment: $("#drpSoakingTreatment").val(),
                packingStyle: $("#drpPckgStyle").val(),
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
    });
$("#drpPlant").on("change",function(){
    $("#divDataList").html("");
});
</script>

</body>

</html>
