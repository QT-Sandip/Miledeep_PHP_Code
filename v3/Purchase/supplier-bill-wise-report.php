<?php

// phpinfo();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

//$filterPlant="T";
$filterArrivalPlant = "T";
$filterCenter = "T";
$filterBillerType = "T";
$filterSupplier = "T";
$filterAgent = "T";
$filterStartDate = "T";
//$filterEndDate = "T";
// $buttonRight = "T";
 $buttonBottom = "T";
$filterRptType = "T";
$PageTitle = "Supplier & Bill  Wise Outstanding Amount";


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
        var CenterName = $("#select2-drpCenter-container").text();
        var StartDate = FormattedDate($("#txtStartDate").val());
        //   var EndDate= FormattedDate($("#txtEndDate").val());

        var DatePeriod = StartDate;

        if (CenterName == "Select Center") {
            CenterName = ' ';
        }
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bSort: false,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: CenterName + '  Details Of Supplier Wise Out Standing Amount on Date : - ' + DatePeriod,
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }

    function ShowList() {

        $.ajax({
            url: 'Ajax/AjaxSupplierBillWiseReport.php',
            type: 'POST',
            data: {
                // plant: $("#drpPlant").val(),
                Plant: $("#drpArrivalPlant").val(),
                Center: $("#drpCenter").val(),
                Type: $("#drpBillerType").val(),
                Supplier: $("#drpSupplier").val(),
                Agent: $("#drpAgent").val(),
                // count: $("#drpCount").val(),
                startDate: $("#txtStartDate").val(),
                //endDate: $("#txtEndDate").val()
                RptType: $("#drpRptType").val()
            },
            success: function(data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }
    $(document).ready(function() {
        //  ShowList();
    });

    // $("#btnSearch").on("click", function () {
    //     // ShowList();
    //     $StartDate = $("#txtStartDate").val();
    //     $EndDate = $("#txtEndDate").val();
    //     if ($StartDate != '') {
    //         ShowList();
    //     }
    //     else {
    //         alert("Please select Date");
    //     }
    // })

    $(document).on("change", "#drpBillerType", function() {
        var billerType = $(this).val();
        $('#drpSupplier, #drpAgent').val("").trigger("change");
        if (billerType == "1") {
            $('#drpSupplier').prop("disabled", true);
            $('#drpAgent').prop("disabled", false);
        } else {
            $('#drpSupplier').prop("disabled", false);
            $('#drpAgent').prop("disabled", true);
        }
    });

    $("#btnSearch").on("click", function() {

        var startDate = $("#txtStartDate").val();

        if (startDate == '') {
            if (startDate == "") {
                $("#txtStartDate").addClass('error');
            } else {
                $("#txtStartDate").removeClass('error');
            }
        } else {
            $("#txtStartDate").removeClass('error');
            ShowList();
        }
    })
    // $("#btnSearch").on("click", function() {
    //     var drpCenter = $("#drpCenter").val();

    //     var startDate = $("#txtStartDate").val();
    //     // $EndDate = $("#txtEndDate").val();

    //     console.log(startDate, drpCenter);

    //     // console.log("1");
    //     if (drpCenter == "" || startDate == '') {
    //         if (drpCenter == "") {
    //             $("#drpCenter").next().find('.select2-selection').addClass('error');
    //         } else {
    //             $("#drpCenter").next().find('.select2-selection').removeClass('error');
    //         }
    //         if (startDate == "") {
    //             $("#txtStartDate").addClass('error');
    //         } else {
    //             $("#txtStartDate").removeClass('error');
    //         }
    //     } else {
    //         $("#drpCenter").next().find('.select2-selection').removeClass('error');
    //         $("#txtStartDate").removeClass('error');
    //         ShowList();
    //     }
    // })

    function FormattedDate(oldformatDate) {
        var dateObject = new Date(oldformatDate);
        var FormattedDate = dateObject.getDate() + "/" + (dateObject.getMonth() + 1) + "/" + dateObject.getFullYear();
        return FormattedDate;
    }

    const LIMIT_DATE = "2026-03-31";

function checkDatesAndSubmit() {

    let fromDate = document.getElementById("txtStartDate").value;



    // convert to Date objects
    let from = new Date(fromDate);

    let limit = new Date(LIMIT_DATE);

    // invalid range check

    // CASE 1: one side below limit and other above limit (NOT allowed)

    // CASE 2: valid cases → trigger AJAX

        triggerAjax(fromDate);

}


// Example AJAX function
function triggerAjax(fromDate, toDate) {
    $.ajax({
        url: "Ajax/AjaxGetSupplierList.php",
        type: "GET",
        dataType: "json",
        data: {
            fromDate: fromDate

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
$("#txtStartDate").on("change", function () {
    checkDatesAndSubmit();
});
$("#drpCenter").on("change", function () {
    $('#tblDataTable').DataTable().destroy();
    $("#divDataList").html("");
});
</script>

</body>

</html>
