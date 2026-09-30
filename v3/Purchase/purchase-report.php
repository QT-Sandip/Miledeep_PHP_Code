<?php

// phpinfo();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterArrivalPlant = "T";
//$filterPlant="T";
$filterCenter = "T";
$filterSupplier = "T";
//$filterCount="T";
$filterStartDate = "T";
$filterEndDate = "T";
$buttonBottom = "T";

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$pancardduplicate = trim($ClientConfigData->pancardduplicate);

?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . 'includes/head.php' ?>

</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . 'includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . 'includes/page-header.php' ?>
                <?php include_once WORKING_MODULE . 'includes/page-filter.php'; ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">

                                <div id="divDataList">
                                    <!-- -->
                                </div>

                            </div>
                        </div>


                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <?php include_once ROOT . 'includes/footer.php' ?>

    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . 'includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . 'includes/app-js.php' ?>

<script>
    function FormattedDate(oldformatDate) {
        var dateObject = new Date(oldformatDate);
        var FormattedDate = dateObject.getDate() + "/" + (dateObject.getMonth() + 1) + "/" + dateObject.getFullYear();
        return FormattedDate;
    }

    function fnShowDataTable() {

        var CenterName = $("#select2-drpCenter-container").text();

        var StartDate = FormattedDate($("#txtStartDate").val());
        var EndDate = FormattedDate($("#txtEndDate").val());

        var DatePeriod = '(From ' + ' ' + StartDate + ' To ' + EndDate + ')';

        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            dom: 'Blfrtip',
            // bSortCellsTop: true,
            // header: true, // Enable complex headers
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: 'Purchase Report - ' + CenterName + " " + DatePeriod,
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                // exportOptions: {
                //     columns: ':visible',
                //     // rows: function (indx, rowData, domElement) {
                //     //     return $(domElement).css("display") != "none";
                //     // }
                // },
                // customize: function (xlsx) {
                //     var sheet = xlsx.xl.worksheets['sheet1.xml'];
                //     $('row c[r^="C"]', sheet).attr('s', '2');
                // }

            }],
        });
        table.on('draw.dt', function() {

            let totalFinalQty = 0, totalAmount = 0, totalHarvestAmount = 0, totalFinalAmount = 0;
            $('.tdFinalQty').each(function() {
                const quantity = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(quantity)) {
                    totalFinalQty += quantity;
                }
            });
            $('.tdAmount').each(function() {
                const value = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(value)) {
                    totalAmount += value;
                }
            });
            $('.tdHarvestAmount').each(function() {
                const value = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(value)) {
                    totalHarvestAmount += value;
                }
            });
            $('.tdFinalAmount').each(function() {
                const value = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(value)) {
                    totalFinalAmount += value;
                }
            });
            $('#TotalQuantity').text(totalFinalQty.toLocaleString('en-US', {
                minimumFractionDigits: 3,
                maximumFractionDigits: 3
            }));
            $('#TotalValue').text(totalAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#TotalHarvestAmount').text(totalHarvestAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#TotalAmount').text(totalFinalAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });
        // var data = table.buttons.exportData( {
        //     customizeData: function(dataForExport) {
        //         dataForExport.header.push("Header line 1");
        //         dataForExport.header.push("Header line 2");
        //         dataForExport.header.push("Header line 3");
        //     }
        // } );
    }

    function ShowList() {

        var drpArrivalPlant = $("#drpArrivalPlant").val();
        var drpCenter = $("#drpCenter").val();
        var drpSupplier = $("#drpSupplier").val();
        var drpCount = $("#drpCount").val();
        var txtStartDate = $("#txtStartDate").val();
        var txtEndDate = $("#txtEndDate").val();

        var startDate = new Date(txtStartDate);
        var endDate = new Date(txtEndDate);

        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

        var days = daysDiff + 1;

        console.log(days);

        if (days > 31) {
            var params = new URLSearchParams();

            params.append("Plant", drpArrivalPlant);
            params.append("Center", drpCenter);
            params.append("Supplier", drpSupplier);
            params.append("Count", drpCount);
            params.append("StartDate", txtStartDate);
            params.append("EndDate", txtEndDate);
            params.append("excelDownload", "T");

            var url =
                "Ajax/AjaxPurchaseitemWisecsvdownload.php?" +
                params.toString();

            // Direct download
            window.location.href = url;

            return false;
        } else {
            $.ajax({
                url: 'Ajax/AjaxPurchaseItemWiseReport.php',
                type: 'POST',
                data: {
                    // Plant: $("#drpPlant").val(),
                    Plant: drpArrivalPlant,
                    Center: drpCenter,
                    Supplier: drpSupplier,
                    //  Count: drpCount,
                    StartDate: txtStartDate,
                    EndDate: txtEndDate
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    return false;
                }
            });

        }
    }
    $(document).ready(function() {
        // ShowList();
        triggerAjax('2024-01-01', '2024-02-01');
    });
    $("#drpCenter").on("change", function () {
        $('#tblDataTable').DataTable().destroy();
        $("#divDataList").html("");
    });

    $("#btnSearch").on("click", function() {

        var txtStartDate = $("#txtStartDate").val();
        var txtEndDate = $("#txtEndDate").val();

        if (txtStartDate && txtEndDate) {
            ShowList();
        } else {
            alert("Please select the start date and end date");
        }
    });

var pancardduplicate = '<?php echo $pancardduplicate?$pancardduplicate:0; ?>';
if(pancardduplicate){
const LIMIT_DATE = "2026-03-31";

function checkDatesAndSubmit() {

    let fromDate = document.getElementById("txtStartDate").value;
    let toDate   = document.getElementById("txtEndDate").value;

    if (!fromDate || !toDate) {
        alert("Please select both dates");
        return;
    }

    // convert to Date objects
    let from = new Date(fromDate);
    let to   = new Date(toDate);
    let limit = new Date(LIMIT_DATE);

    // invalid range check
    if (from > to) {
        alert("From Date cannot be greater than To Date");
        return;
    }

    // CASE 1: one side below limit and other above limit (NOT allowed)
    if ((from <= limit && to > limit) || (from >= limit && to < limit)) {
        document.getElementById("txtStartDate").value ="";
        document.getElementById("txtEndDate").value = "";
        alert("Cannot select both financial year and date range. Please choose one");
        return;
    }

    // CASE 2: valid cases → trigger AJAX

        triggerAjax(fromDate, toDate);

}


// Example AJAX function
function triggerAjax(fromDate, toDate) {
    $.ajax({
        url: "Ajax/AjaxGetSupplierList.php",
        type: "GET",
        dataType: "json",
        data: {
            fromDate: fromDate,
            toDate: toDate
        },
        success: function (res) {

            if (res.status && res.data.length > 0) {

                let options = '<option value="">Select Supplier</option>';

                $.each(res.data, function (i, item) {

                    options += `
                        <option value="${item.SupplierID}">
                            ${item.FinancierName} - ${item.PanNO}
                        </option>
                    `;
                });
                $("#drpSupplier").html("");
                $("#drpSupplier").html(options);

                // refresh select2 if used
                if ($.fn.select2) {
                    $("#drpSupplier").select2();
                }
            }
        },
        error: function (err) {
            console.log("Error loading suppliers", err);
        }
    });
}


// optional auto trigger button
$("#txtStartDate, #txtEndDate").on("change", function () {
    //checkDatesAndSubmit();
});
}
</script>

</body>

</html>
