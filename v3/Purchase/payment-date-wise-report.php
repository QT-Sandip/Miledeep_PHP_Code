<?php

// phpinfo();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

//$filterPlant="T";
//$filterArrivalPlant="T";
$filterCenter = "T";
$filterSupplier = "T";
$filterBillerType = "T";
$filterStartDate = "T";
$filterEndDate = "T";
$filterPaymentMode = "T";
$filterAgent = "T";
//$buttonRight="T";
$buttonBottom = "T";

$PageTitle = "Bill & Date Wise Payment Summary";
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<script>
    function fnShowDataTable() {

        var CenterName = $("#select2-drpCenter-container").text();
        var StartDate = FormattedDate($("#txtStartDate").val());
        var EndDate = FormattedDate($("#txtEndDate").val());

        var DatePeriod = ' From ' + ' ' + StartDate + ' To ' + EndDate;


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
                title: CenterName + '  Details of Bill Wise & Date Wise Bank Statement For The Period  ' + DatePeriod,
                footer: true,
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: function (data, row, column, node) {
                            const dateColumns = [1, 2];

                            function decodeHtml(html) {
                                const txt = document.createElement("textarea");
                                txt.innerHTML = html;
                                return txt.value;
                            }

                            if (dateColumns.includes(column)) {
                                if (data == "--") {
                                    return "--";
                                } else if (data == "") {
                                    return "";
                                } else {
                                    return moment(data, "DD-MM-YYYY").format("YYYY-MM-DD");
                                }
                            } else {
                                // return data.replace(/^\s+|\s+$|\s+/g, '').replace(/<[^>]+>/g, '');
                                return decodeHtml(data)
                                    .replace(/^\s+|\s+$/g, '')
                                    .replace(/\s+/g, ' ')
                                    .replace(/<[^>]+>/g, '');
                            }

                        }
                    }
                }
            }],
        });
        table.on('draw.dt', function () {
            let paidAmount = 0;
            $('.PaidAmount').each(function () {
                // Parse the text content as a float, ignoring any commas
                const amount = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(amount)) {
                    paidAmount += amount;
                }
            });
            $('#TotalPaidAmount').text(paidAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });


    }

    function ShowList() {
        var paymentMode = $("#drpPaymentMode").val();
        var type = $("#drpBillerType").val();
        var supplier = $("#drpSupplier").val();
        var agent = $("#drpAgent").val();
        var center = $("#drpCenter").val();
        var txtStartDate = $("#txtStartDate").val();
        var txtEndDate = $("#txtEndDate").val();

        var startDate = new Date(txtStartDate);
        var endDate = new Date(txtEndDate);

        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

        var days = daysDiff + 1;

        console.log(days);

        if (days > 31) {
            var url = 'Ajax/AjaxPaymentDateWiseReport.php?PaymentMode=' + paymentMode + '&Center=' + center + '&BillerType=' + type + '&Agent=' + agent + '&Supplier=' + supplier + '&StartDate=' + txtStartDate + '&EndDate=' + txtEndDate + '&excelDownload=T';
            window.open(url, '_blank');
        } else {
            $.ajax({
                url: 'Ajax/AjaxPaymentDateWiseReport.php',
                type: 'POST',
                data: {
                    //Plant: $("#drpPlant").val(), 
                    //Plant: $("#drpArrivalPlant").val(),                       
                    PaymentMode: $("#drpPaymentMode").val(),
                    Center: $("#drpCenter").val(),
                    BillerType: $("#drpBillerType").val(),
                    //Supplier: $("#drpCenter").val(),
                    StartDate: $("#txtStartDate").val(),
                    EndDate: $("#txtEndDate").val(),
                    Supplier: $("#drpSupplier").val(),
                    Agent: $("#drpAgent").val(),

                },
                success: function (data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    return false;
                }
            });
        }
    }
    $(document).ready(function () {
        //  ShowList();
    });

    $("#btnSearch").on("click", function () {
        // ShowList();
        // ShowList();
        $StartDate = $("#txtStartDate").val();
        $EndDate = $("#txtEndDate").val();
        $BillerType = $("#drpBillerType").val();
        if ($BillerType == '' || $BillerType == null) {
            alert("Please select type");
            return false;
        }
        if ($StartDate == '' || $EndDate == '') {
            alert("Please select Date");
            return false;
        }
        ShowList();
    })

    function FormattedDate(oldformatDate) {
        var dateObject = new Date(oldformatDate);
        var FormattedDate = dateObject.getDate() + "/" + (dateObject.getMonth() + 1) + "/" + dateObject.getFullYear();
        return FormattedDate;
    }

    $(document).on("change", "#drpBillerType", function () {
        var billerType = $(this).val();
        $('#drpSupplier, #drpAgent').val("").trigger("change");
        if (billerType == "1") {
            $('#drpSupplier').prop("disabled", true);
            $('#drpAgent').prop("disabled", false);
        } else if (billerType == "2") {
            $('#drpSupplier').prop("disabled", false);
            $('#drpAgent').prop("disabled", true);
        } else {
            $('#drpSupplier').prop("disabled", false);
            $('#drpAgent').prop("disabled", false);
        }
    });

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
$("#drpCenter").on("change", function () {
    $('#tblDataTable').DataTable().destroy();
    $("#divDataList").html("");
});
</script>

</body>

</html>
