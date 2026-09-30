<?php

// phpinfo();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

//$filterPlant="T";
$filterArrivalPlant = "T";
$filterCenter = "T";
$filterStartDate = "T";
//$filterEndDate = "T";
$filterSupplier = "T";
$filterBillerType = "T";
$filterAgent = "T";
$buttonRight = "T";
$PageTitle = "Supplier Wise Outstanding Report";

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
        // var EndDate= FormattedDate($("#txtEndDate").val());

        var DatePeriod = 'on  Date:-' + ' ' + StartDate;

        //alert(SupplierName);
        if (CenterName == "Select Center") {
            CenterName = '  Details of Supplier Wise Out Standing Amount ' + DatePeriod;
        } else {
            CenterName = CenterName + '  Details of Supplier Wise Out Standing Amount ' + DatePeriod;
        }


        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            ordering: false,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: CenterName,
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }

    function ShowList() {

        $.ajax({
            url: 'Ajax/AjaxSupplierCenterBillWiseReport.php',
            type: 'POST',
            data: {
                //Plant: $("#drpPlant").val(),
                Plant: $("#drpArrivalPlant").val(),
                Supplier: $("#drpSupplier").val(),
                Center: $("#drpCenter").val(),
                AgentID: $("#drpAgent").val(),
                BillerType: $("#drpBillerType").val(),
                StartDate: $("#txtStartDate").val(),
                // EndDate: $("#txtEndDate").val()

            },
            success: function(data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }
    $(document).ready(function() {
        $('#drpSupplier, #drpAgent').prop("disabled", false);
    });

    $(document).on("change", "#drpBillerType", function() {
        var agentType = $(this).val();
        $('#drpSupplier, #drpAgent').val("").trigger("change");
        if (agentType == "1") {
            $('#drpSupplier').prop("disabled", true);
            $('#drpAgent').prop("disabled", false);
        } else if (agentType == "2") {
            $('#drpSupplier').prop("disabled", false);
            $('#drpAgent').prop("disabled", true);
        } else {
            $('#drpSupplier').prop("disabled", false);
            $('#drpAgent').prop("disabled", false);
        }
    });


    $("#btnSearch").on("click", function() {
        var drpCenter = $("#drpCenter").val();
        var drpBillerType = $("#drpBillerType").val();
        var drpAgent = $("#drpAgent").val();
        var drpSupplier = $("#drpSupplier").val();
        var drpArrivalPlant = $("#drpArrivalPlant").val();
        var txtStartDate = $("#txtStartDate").val();

        console.log(drpCenter);

        // if (drpCenter == "" || txtStartDate == "") {
        if (txtStartDate == "") {

            // if (drpCenter == "") {
            //     $("#drpCenter").next().find('.select2-selection').addClass('error');
            // } else {
            //     $("#drpCenter").next().find('.select2-selection').removeClass('error');
            // }

            if (txtStartDate == '') {
                $("#txtStartDate").addClass('error');
            } else {
                $('#txtStartDate').removeClass('error');
            }

        } else {
            // $('#drpCenter').next().find('.select2-selection').removeClass('error');
            $('#txtStartDate').removeClass('error');
            ShowList();
        }
    });

    const LIMIT_DATE = "2026-03-31";

function checkDatesAndSubmit() {

    let fromDate = document.getElementById("txtStartDate").value;

    if (!fromDate) {
        alert("Please select date");
        return;
    }

    // convert to Date objects
    let from = new Date(fromDate);
    let limit = new Date(LIMIT_DATE);

    // CASE 2: valid cases → trigger AJAX

        triggerAjax(fromDate);

}


// Example AJAX function
function triggerAjax(fromDate) {
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
