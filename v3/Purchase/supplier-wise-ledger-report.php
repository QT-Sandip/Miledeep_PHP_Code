<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterArrivalPlant = "T";
$filterCenter = "T";
$filterStartDate = "T";
$filterEndDate = "T";
$buttonBottom = "T";
$filterSupplier = "T";
$filterBillerType = "T";
$filterAgent = "T";
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
    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bSort: false,
            language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',
            buttons:[],

        });
    }

    function ShowList() {

        $.ajax({
            url: 'Ajax/AjaxSupplierWiseLedgerReport.php',
            type: 'POST',
            data: {
                Plant: $("#drpArrivalPlant").val(),
                Supplier: $("#drpSupplier").val(),
                Center: $("#drpCenter").val(),
                StartDate: $("#txtStartDate").val(),
                EndDate: $("#txtEndDate").val(),
                AgentID: $("#drpAgent").val(),
                BillerType: $("#drpBillerType").val()

            },
            success: function (data) {
                $("#divDataList").html(data);
                fnShowDataTable();

                $("<button class='btn btn-secondary buttons-excel buttons-html5 btnExcelDataTable' id='btnExcelDownload' style='margin-left: 15px;' type='button'><span>Download Excel</span></button>")
                    .insertAfter("#tblDataTable_filter label");

                return false;
            }
        });
    }

    $(document).on("change", "#drpBillerType", function () {
        var agentType = $(this).val();
        $('#drpSupplier, #drpAgent').val("").trigger("change");
        if (agentType == "1") {
            $('#drpSupplier').prop("disabled", true);
            $('#drpAgent').prop("disabled", false);
        } else {
            $('#drpSupplier').prop("disabled", false);
            $('#drpAgent').prop("disabled", true);
        }
    });

    $(document).ready(function () {
        $('#drpSupplier, #drpAgent').prop("disabled", true);
    });

    // Dugra 
    function searchRequired(val, selector) {
        if (val == "") {
            $(selector).next().find('.select2-selection').addClass('error');
        } else {
            $(selector).next().find('.select2-selection').removeClass('error');
        }
    }

    $("#btnSearch").on("click", function () {
        var drpCenter = $("#drpCenter").val();
        var drpBillerType = $("#drpBillerType").val();
        var drpAgent = $("#drpAgent").val();
        var drpSupplier = $("#drpSupplier").val();
        var drpArrivalPlant = $("#drpArrivalPlant").val();
        var txtStartDate = $("#txtStartDate").val();
        var txtEndDate = $("#txtEndDate").val();
        var drpCenter = "1";

        if (drpCenter == "" || drpBillerType == "") {

            if (drpCenter == "") {
                $("#drpCenter").next().find('.select2-selection').addClass('error');
            } else {
                $("#drpCenter").next().find('.select2-selection').removeClass('error');
            }

            if (drpBillerType == "") {
                $("#drpBillerType").next().find('.select2-selection').addClass('error');
                $("#drpSupplier").next().find('.select2-selection').removeClass('error');
                $("#drpAgent").next().find('.select2-selection').removeClass('error');
                $('#drpSupplier, #drpAgent').prop("disabled", true);
            }

        } else {
            if (drpBillerType == "1") {
                if (drpAgent == "") {
                    $("#drpAgent").next().find('.select2-selection').addClass('error');
                } else {
                    $("#drpAgent").next().find('.select2-selection').removeClass('error');
                }
                $("#drpBillerType, #drpSupplier").next().find('.select2-selection').removeClass('error');
            }
            if (drpBillerType == "2") {
                if (drpSupplier == "") {
                    $("#drpSupplier").next().find('.select2-selection').addClass('error');
                } else {
                    $("#drpSupplier").next().find('.select2-selection').removeClass('error');
                }
                $("#drpBillerType, #drpAgent").next().find('.select2-selection').removeClass('error');
            }
            if (drpCenter != "" && drpBillerType != "") {
                if (drpSupplier != "" || drpAgent != "") {
                    $('#drpCenter').next().find('.select2-selection').removeClass('error');
                    $('#drpBillerType').next().find('.select2-selection').removeClass('error');
                    $('#drpSupplier').next().find('.select2-selection').removeClass('error');
                    $('#drpAgent').next().find('.select2-selection').removeClass('error');
                    ShowList();
                }
            }
        }
    });


    $(document).on("click", "#btnExcelDownload", function () {
        console.log($('#select2-drpCenter-container').text().split(' ').join('_'));

        var drpCenter = $("#drpCenter").val();
        var centerName = $("#select2-drpCenter-container").text().split(' ').join('-');//durga prasad 30-01-2024
        var drpArrivalPlant = $("#drpArrivalPlant").val();
        var plantName = $("#select2-drpArrivalPlant-container").text().split(' ').join('-');//durga prasad 30-01-2024
        var drpSupplier = $("#drpSupplier").val();
        var suplierName = $("#select2-drpSupplier-container").text().split(' ').join(' ');//durga prasad 30-01-2024
        var txtStartDate = $("#txtStartDate").val();
        var txtEndDate = $("#txtEndDate").val();
        var drpAgent = $("#drpAgent").val();
        var agentName = $("#select2-drpAgent-container").text().split(' ').join('-');//durga prasad 30-01-2024
        var drpBillerType = $("#drpBillerType").val();

        // var queryString = "/v3/Purchase/Reports/DocExlSupplierWiseLedgerReport?Plant=" + drpArrivalPlant + "&Center=" + drpCenter + "&StartDate=" + txtStartDate + "&EndDate=" + txtEndDate + "&Supplier=" + drpSupplier + "&AgentID=" + drpAgent + "&BillerType=" + drpBillerType; //uma
        var queryString = "/v3/Purchase/Reports/DocExlSupplierWiseLedgerReport?Plant=" + drpArrivalPlant + "&PlantName=" + plantName +
            "&Center=" + drpCenter + "&CenterName=" + encodeURIComponent(centerName) + "&StartDate=" + txtStartDate + "&EndDate=" + txtEndDate + "&Supplier=" + drpSupplier +
            "&SupplierName=" + encodeURIComponent(suplierName) + "&AgentID=" + drpAgent + "&AgentName=" + agentName + "&BillerType=" + drpBillerType; //durga prasad 30-01-2024

        window.open(queryString, '_blank');

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
    checkDatesAndSubmit();
});
}
$("#drpCenter").on("change", function () {
    $('#tblDataTable').DataTable().destroy();
    $("#divDataList").html("");
});
</script>

</body>

</html>
