<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterYear = "T";
$filterExporter = "T";
$filterCountry = "T";
$filterConsignee = "T";

$filterButButton = "T";

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

                        <!-- <div class="card">
                                    <div class="card-body"> -->

                        <div id="divDataList">
                            <!-- -->
                        </div>

                        <!-- </div>
                                </div> -->


                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <?php include_once ROOT . '/includes/footer.php' ?>

    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . '/includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="../assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<script>
$(document).ready(function(){
   $('#drpYear').prev('label').text('Select Year (Based on Custom Inv Date)')
})
function fnShowDataTable() {
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
            title: "M-Transit-Ins-Payments",
            footer: true,
            exportOptions: {
                columns: ':visible',
                        format: {
                            body: function (data, row, column, node) {

                                const dateColumns = [13];

                                if (dateColumns.includes(column)) {
                                    return moment(data, "DD-MM-YYYY").format("YYYY-MM-DD");
                                } else {
                                    return data;
                                }
                                
                            }
                        }
            }
        }, ],
        drawCallback: function() {
            $(document).trigger('afterSearch');
        }

    });
}

$(document).on('afterSearch', function() {
    var countInv = $(".bodyInvNo").length;
    var sumInvVal = columnSum(".bodyInvVal", 2);
    var sumInvPerc = columnSum(".bodyInvPerc", 2);
    var sumPremiums = columnSum(".bodyPremiums", 2);

    $(".footInvNo").text(countInv);
    $(".footInvVal").text(sumInvVal);
    $(".footInvPerc").text(sumInvPerc);
    $(".footPremiums").text(sumPremiums);
});

$("#btnSearch").on("click", function() {
    $.ajax({
        url: 'Ajax/AjaxMTransitInsAndPayments.php',
        type: 'POST',
        data: {
            Year: $("#drpYear").val(),
            ExporterID: $("#drpExporterList").val(),
            ConsigneeID: $("#drpConsigneeList").val(),
            CountryID: $("#drpCountryList").val(),

        },
        success: function(data) {
            $("#divDataList").html(data);
            fnShowDataTable();
            return false;
        }
    });
});
$("#drpConsigneeList").on("change", function () {
    $("#divDataList").html("");
});
</script>

</body>

</html>
