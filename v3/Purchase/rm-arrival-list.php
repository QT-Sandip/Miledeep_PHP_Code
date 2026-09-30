<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterCenter = "T";
$filterArrivalDate = "T";
$filterArrivalPlant = "T";
$filterSupplier = "T";
$buttonBottom = "T";

$PlantID = $_SESSION['UnitID'];
$AccessType = $_SESSION['AccessType'];

// echo 'PlantId' . $PlantID;
// echo '\AccessType' . $AccessType;
// exit;
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
                                <div class="text-left">
                                    <a href="create-arrival" class="btn btn-success waves-effect waves-light">
                                        <i class="fa fa-plus"></i> New Arrival
                                    </a>
                                </div>

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

        var drpPurchaseName = $("#select2-drpPurchaseList-container").text();
        if (drpPurchaseName == "Select Purchase") {
            drpPurchaseName = "";
        }
        var fileName = 'Purchase-Arrival-List-' + drpPurchaseName + '-<?php echo date("d-m-Y") ?>';
        console.log(fileName);

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
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: fileName,
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
                table.on('draw.dt', function () {
            let arrivalWeight = 0;
            let totalAmount = 0;
            $('.arrival-weight').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    arrivalWeight += weight;
                }
            });
            $('.total-amt').each(function () {
                // Parse the text content as a float, ignoring any commas
                const amount = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(amount)) {
                    totalAmount += amount;
                }
            });
            $('#TotalArrivalWeight').text(arrivalWeight.toLocaleString('en-US', {
                minimumFractionDigits: 3,
                maximumFractionDigits: 3
            }));
            $('#TotalAmt').text(totalAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

        });
    }

    $(document).ready(function() {
        var plantId = <?php echo $PlantID ?>;
        var accessType = <?php echo $AccessType ?>;
        console.log('accessType' + accessType);

        if (accessType == 3) {
            if (plantId != 0) {
                $("#drpArrivalPlant").prop('disabled', true);
                $("#drpArrivalPlant").val(plantId).trigger('change');
            } else {
                $("#drpArrivalPlant").prop('disabled', false);
                $("#drpArrivalPlant").val("").trigger('change');
            }
        } else {
            $("#drpArrivalPlant").prop('disabled', false);
            $("#drpArrivalPlant").val("").trigger('change');
        }
    });

    function ShowList() {
        $.ajax({
            url: 'Ajax/AjaxArrivalList.php',
            type: 'POST',
            data: {
                PsId: $("#drpCenter").val(),
                ArrivalDate: $("#txtArrivalDate").val(),
                ArrivalPlant: $("#drpArrivalPlant").val(),
                Supplier: $("#drpSupplier").val(),
            },
            success: function(data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }
    $(document).ready(function() {
        ShowList();
    });

    $("#btnSearch").on("click", function() {
        ShowList();
    })


    $(document).on("click", ".btnDelete", function() {
        var selectedID = $(this).attr("data-id");
        // var tdDate = $.trim($(this).closest("tr").find(".tdDate").attr("data-date"));
        console.log(selectedID);

        if (confirm("Are you sure do you want to delete !") == true) {
            $.ajax({
                url: 'Ajax/AjaxArrivalList.php',
                type: 'POST',
                data: {
                    ArrivalID: selectedID,
                    ArrivalDate: $("#txtArrivalDate").val(),
                    MethodType: "Delete"
                },
                success: function(data) {

                    if (data.Error == null) {
                        alert(data.Msg);
                        ShowList();
                        // location.reload();
                    }
                    return false;
                }
            });
        } else {

        }
    });

const LIMIT_DATE = "2026-03-31";

function checkDatesAndSubmit() {

    let fromDate = document.getElementById("txtArrivalDate").value;


    // convert to Date objects
    let from = new Date(fromDate);
    let limit = new Date(LIMIT_DATE);

    // invalid range check


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
$("#txtArrivalDate").on("change", function () {
    checkDatesAndSubmit();
});
</script>

</body>

</html>
