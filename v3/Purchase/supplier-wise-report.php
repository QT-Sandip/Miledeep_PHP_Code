<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterArrivalPlant = "T";
$filterCenter = "T";
$filterSupplier = "T";
$filterStartDate = "T";
$filterEndDate = "T";
// $buttonRight = "T";
$buttonBottom = "T";
$PageTitle = "Supplier Bill  Summary";
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
        var SupplierName = $("#select2-drpSupplier-container").text();
        var StartDate = FormattedDate($("#txtStartDate").val());
        var EndDate = FormattedDate($("#txtEndDate").val());

        var DatePeriod = 'From ' + ' ' + StartDate + ' To ' + EndDate;

        if (SupplierName == "Select Supplier") {
            SupplierName = 'Supplierwise Purchase Summary for the period:' + DatePeriod;
        }
        else {
            SupplierName = 'Name: ' + SupplierName + '- Supplierwise Purchase Summary for the period:' + DatePeriod;
        }
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel', text: 'Download Excel', className: 'btnExcelDataTable', title: SupplierName, footer: true,
                exportOptions: {
                    columns: ':visible',
                    format: {
                                    body: function(data, row, column, node) {

                                        const dateColumns = [2];

                                        if (dateColumns.includes(column)) {
                                            if (data == "--") {
                                                return "--";
                                            } else {
                                                return moment(data, "DD-MM-YYYY").format("YYYY-MM-DD");
                                            }
                                        } else {
                                            return data;
                                        }

                                    }
                                }

                }
            }],
        });
        table.on('draw.dt', function () { 
            let totalQuantity = 0;
            let totalValue = 0;
            let totalHarvestAmount = 0;
            let totalAmount = 0;
          
          $('.qty').each(function () {
              // Parse the text content as a float, ignoring any commas
              const weight = parseFloat($(this).text().replace(/,/g, ''));
              if (!isNaN(weight)) {
                totalQuantity += weight;
              }
          });
          $('.amount').each(function () {
              // Parse the text content as a float, ignoring any commas
              const value = parseFloat($(this).text().replace(/,/g, ''));
              if (!isNaN(value)) {
                totalValue += value;
              }
          });

          $('.harvestAmount').each(function () {
              // Parse the text content as a float, ignoring any commas
              const value = parseFloat($(this).text().replace(/,/g, ''));
              if (!isNaN(value)) {
                totalHarvestAmount += value;
              }
          });

          $('.total-amount').each(function () {
              // Parse the text content as a float, ignoring any commas
              const value = parseFloat($(this).text().replace(/,/g, ''));
              if (!isNaN(value)) {
                totalAmount += value;
              }
          });
          $('#TotalQuantity').text(totalQuantity.toLocaleString('en-US', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
          }));
          $('#TotalValue').text(totalValue.toLocaleString('en-US', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
          }));
           $('#TotalHarvestAmount').text(totalHarvestAmount.toLocaleString('en-US', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
          }));
          $('#TotalAmount').text(totalAmount.toLocaleString('en-US', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
          }));
      });
    }
    function ShowList() {
    var plant = $("#drpArrivalPlant").val();
    var center = $("#drpCenter").val();
    var supplier = $("#drpSupplier").val();
    var txtStartDate = $("#txtStartDate").val();
    var txtEndDate = $("#txtEndDate").val();

    var startDate = new Date(txtStartDate);
    var endDate = new Date(txtEndDate);
    
    var timeDiff = endDate.getTime() - startDate.getTime();
    var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
    
    var days = daysDiff + 1;

    console.log(days);

    if(days > 31) {
        var url = 'Ajax/AjaxSupplierWiseReport.php?Plant='+plant+'&Center='+center+'&Supplier='+supplier+'&StartDate='+txtStartDate+'&EndDate='+txtEndDate+'&excelDownload=T';
        window.open(url, '_blank');
    } else {
        $.ajax({
            url: 'Ajax/AjaxSupplierWiseReport.php',
            type: 'POST',
            data: {
                Plant: $("#drpArrivalPlant").val(),
                Center: $("#drpCenter").val(),
                Supplier: $("#drpSupplier").val(),
                StartDate: $("#txtStartDate").val(),
                EndDate: $("#txtEndDate").val(),
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
        ShowList();
    });

    $("#btnSearch").on("click", function () {
        ShowList();
    })

    function FormattedDate(oldformatDate) {
        var dateObject = new Date(oldformatDate);
        var FormattedDate = dateObject.getDate() + "/" + (dateObject.getMonth() + 1) + "/" + dateObject.getFullYear();
        return FormattedDate;
    }

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


        
