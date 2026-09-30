<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterFromDate = "T";
$filterToDate = "T";
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
    type="text/javascript"></script>


<script>
    $(document).ready(function () {
        var productionPlant = <?php echo $ProductionPlant ?>;
        var isPlantFilter = <?php echo $IsPlantFilter ?>;

        if (productionPlant != 0 || isPlantFilter == 0) {
            $("#drpReceivingPlantId").prop('disabled', true);
            if (isPlantFilter == 0) {
                $("#drpReceivingPlantId").val("");
            } else {
                $("#drpReceivingPlantId").val(productionPlant).trigger('change');
            }
        } else {
            $("#drpReceivingPlantId").prop('disabled', false);
        }
    });

    function fnPoStatusReportExcel(tableID, filename = '') {
        var fromDate = $("#txtFromDate").val().split('-').reverse().join('/');
        var toDate = $("#txtToDate").val().split('-').reverse().join('/');
        var plantId = $("#drpReceivingPlantId option:selected").text();
        var lineNumber = $("#drpLineNumber option:selected").text();

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
        <td colspan="17" style="text-align: center; font-weight: bold;">
            Track Report_Date:( ${fromDate} - ${toDate} ) _Production Plant: ${plantId} 
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
        hiddenCells.forEach(function (cell) {
            cell.style.visibility = 'hidden';
        });

        var tableHTML = clonedTable.outerHTML;

        filename = filename ? filename + '_From: ' + fromDate + '_To' + toDate + '.xls' : 'excel_data.xls';

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
        var fromDate = $("#txtFromDate").val().split('-').reverse().join('/');
        var toDate = $("#txtToDate").val().split('-').reverse().join('/');

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
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Track Report Date: (' + fromDate + ' to ' + toDate + ')',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                // action: function() {
                //     fnPoStatusReportExcel('tblDataTable', 'Track Report');
                // }
            }],
        });
        table.on("draw.dt", function () {

            const fields = {
                arrivalWeight: "TotalArvalWeight",
                receivedWeight: "TotalReceivedWeight",
                deHeadingWeight: "TotalDeHeadingWeight",
                gradingWeight: "TotalGradingWeight",
                freezingQtyF1: "TotalFreezingQty",
                soakingInQtyS1: "TotalSoakingInQtyS1",
                vaGradingQty: "TotalVaGradingQty",
                valueAdditionInQty: "TotalValueAdditionInQty",
                vABalanceQty: "TotalVABalanceQty",
                vAOutQty: "TotalVAOutQty",
                freezingQtyF2: "TotalFreezingQtyF2",
                soakingInQtyS2: "TotalSoakingInQtyS2",
                soakingInQtyS12: "TotalSoakingInQtyS12",
                soakInQty: "TotalSoakInQty",
                soakingBalanceQty: "TotalSoakingBalanceQty",
                soakOutQty: "TotalSoakOutQty",
                soakingPickUpQty: "TotalSoakingPickUpQty",
                freezingQtyF2S3: "TotalFreezingQtyF2S3",
                freezingInQty: "TotalFreezingInQty",
                frzBalanceQty: "TotalFrzBalanceQty",
                storeInQty: "TotalStoreInQty",
                storeInwardQty: "TotalStoreInwardQty",
                sInwdBalanceQty: "TotalSInwdBalanceQty",
                storeOutwardQty: "TotalStoreOutwardQty",
                storeBalanceQty: "TotalStoreBalanceQty",
            };

            const totals = {};

            // Initialize totals to 0
            Object.keys(fields).forEach(key => totals[key] = 0);

            // Loop through each field and calculate totals
            Object.keys(fields).forEach(key => {
                $("." + key).each(function () {
                    const value = parseFloat($(this).text().replace(/,/g, ""));
                    if (!isNaN(value)) totals[key] += value;
                });
            });

            // Update UI elements
            Object.keys(fields).forEach(key => {
                $("#" + fields[key]).text(
                    totals[key].toLocaleString("en-US", {
                        minimumFractionDigits: 3,
                        maximumFractionDigits: 3
                    })
                );
            });
        });
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
            url: 'Ajax/ajax-track-report.php',
            type: 'POST',
            data: {
                fromDate: $("#txtFromDate").val(),
                toDate: $("#txtToDate").val(),
                plantId: $("#drpReceivingPlantId").val(),
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

    $("#btnSearch").on("click", function () {
        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();
        if (fromDate != '' && toDate != '') {
            ShowList("1", "", "0");
        } else {
            alert('Select From Date and To Date')
        }
    })

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
$("#drpReceivingPlantId").on("change", function () {
    $('#tblDataTable').DataTable().destroy();
    $("#divDataList").html("");
});
</script>

</body>

</html>
