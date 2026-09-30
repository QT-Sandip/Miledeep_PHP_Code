<?php

use PhpOffice\PhpSpreadsheet\Shared\OLE\PPS\Root;

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterPsId = "T";
// $filterBillsDate = "T";
$filterStartDateBills = "T";
$filterEndDateBills = "T";
$filterArrivalPlant = "T";
$filterSupplierBills = "T";
$filterAgentBills = "T";
$filterType = "T";
$buttonBottom = "T";
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$pancardduplicate = trim($ClientConfigData->pancardduplicate);
?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . 'includes/head.php' ?>

    <style>
        #chkSelectBill {
            cursor: pointer;
        }
    </style>

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
                                <!-- <div class="text-center">
                                            <a href="create-arrival" class="btn btn-success waves-effect waves-light">
                                                <i class="fa fa-plus"></i>  New Bills
                                            </a>
                                        </div> -->

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
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<?php include_once ROOT . 'includes/app-js.php' ?>
<style>
    /* Remove sort arrows from disabled columns */
    th.no-sort::before,
    th.no-sort::after {
        display: none !important;
    }
</style>
<script>
    function fnShowDataTable() {
        var type = $("#drpType").val();
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: true, // ✅ enable pagination
            lengthMenu: [[10, 30, 50, 100], [10, 30, 50, 100]],
            pageLength: 30,
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
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            columnDefs: [
                {
                    searchable: false,
                    orderable: false,
                    targets: type != 1 ? [] : [0]
                }
            ]

        });
        table.columns.adjust().draw();

        var buttons = new $.fn.dataTable.Buttons(table, {
            buttons: [{
                text: 'Download Commission Bills PDF',
                title: 'Print Bills',
                className: 'btnSaveBulkPdf  btn btn-success',
                extend: 'pdfHtml5',

                action: function () {
                    const arrivalIds = $("input[name='chkSelectBill']:checked").map(function () {
                        return $(this).val();
                    }).get();


                    if (arrivalIds.length === 0) {
                        alert("Please select at least one bill.");
                        return;
                    }
                    let url = "bulkDownloadBills.php?ArrivalIds=" + encodeURIComponent(arrivalIds.join(",")) + "&Type=2";
                    console.log("ArrivalIds ", url)
                    window.open(url, "_blank");
                },
            }]
        }).container().appendTo($('#tblDataTable_filter'));
        var buttons = new $.fn.dataTable.Buttons(table, {
            buttons: [{
                text: 'Download Bills PDF',
                title: 'Print Bills',
                className: 'btnSaveBulkPdf btn btn-success',
                extend: 'pdfHtml5',

                action: function () {
                    const arrivalIds = $("input[name='chkSelectBill']:checked").map(function () {
                        return $(this).val();
                    }).get();


                    if (arrivalIds.length === 0) {
                        alert("Please select at least one bill.");
                        return;
                    }
                    let url = "bulkDownloadBills.php?ArrivalIds=" + encodeURIComponent(arrivalIds.join(",")) + "&Type=1";
                    console.log("ArrivalIds ", url)
                    window.open(url, "_blank");
                },
            }]
        }).container().appendTo($('#tblDataTable_filter'));
        //alert($('#tblDataTable_filter').parent('div').prev('div').attr('class'))
        if (type == 1) {
            $('#tblDataTable_filter').parent('div').removeClass("col-md-6").addClass('col-md-8')
            $('#tblDataTable_filter').parent('div').prev('div').removeClass("col-md-6").addClass('col-md-4')
        } else {
            $('#tblDataTable_filter').parent('div').removeClass("col-md-8").addClass('col-md-6')
            $('#tblDataTable_filter').parent('div').prev('div').removeClass("col-md-4").addClass('col-md-6')
        }


    }
    function ShowList() {

        $.ajax({
            url: 'Ajax/AjaxBillsList.php',
            type: 'POST',
            data: {
                methodType: "get",
                PsId: $("#drpPurchaseList").val(),
                // BillDate: $("#txtBillsDate").val(),
                FromDate: $("#txtStartDate").val(),
                ToDate: $("#txtEndDate").val(),
                Supplier: $("#drpSupplier").val(),
                Agent: $("#drpAgent").val(),
                ArrivalPlant: $("#drpArrivalPlant").val(),
                Type: $("#drpType").val()
            },
            success: function (data) {

                $("#divDataList").html(data);
                fnShowDataTable();
                const Type = $("#drpType").val()
                if (Type == 0) {
                    $(".checkbox-class").hide();
                    $(".btnSaveBulkPdf").hide();
                } else {
                    $(".checkbox-class").show();
                    $(".btnSaveBulkPdf").show();

                }
                return false;
            }
        });

    }
    $(document).on("change", "#chkSelectAllBills", function () {
        var isChecked = $(this).is(":checked");
        $(".chkSelectBill").prop("checked", isChecked);
    });
    $(document).on("change", ".chkSelectBill", function () {
        if (!$(this).is(":checked")) {
            $("#chkSelectAllBills").prop("checked", false);
        } else if ($(".chkSelectBill:checked").length === $(".chkSelectBill").length) {
            $("#chkSelectAllBills").prop("checked", true);
        }
    });

    $(document).ready(function () {
        var today = new Date();
        $("#txtStartDate").val(today.toISOString().split("T")[0]);
        $("#txtEndDate").val(today.toISOString().split("T")[0]);
        ShowList();
    });

    $("#btnSearch").on("click", function () {
        ShowList();
    });


    $(document).on('click', '.btnDelete', function () {
        var breakUpID = $(this).data('id');
        var BillDate = $("#txtBillsDate").val();
        console.log(breakUpID);
        if (confirm('Are you sure you want to delete this record?')) {
            $.ajax({
                url: 'Ajax/AjaxBillsList.php',
                type: 'POST',
                data: {
                    methodType: "delete",
                    breakUpID: breakUpID
                },
                success: function (data) {
                    var formData = {
                        methodType: "POST",
                        ArrivalID: breakUpID,
                        BillDate: BillDate,
                    }
                    $.ajax({
                        url: 'Ajax/AjaxBillsBGTdsCal.php',
                        type: 'POST',
                        data: formData,
                        success: function (data) {
                            ShowList();
                        }
                    });

                },

            });

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
                        <option value="${item.PanNO}">
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
</script>

</body>

</html>
