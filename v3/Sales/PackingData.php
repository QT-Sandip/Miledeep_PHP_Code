<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

// $api = API_PACKING_REPORT_DATA;

//$filterfromDate = "T";
//$filtertoDate = "T";

$filterExporter = "T";
$filterProcessor = "T";
$filterBuyer = "T";
// $filterBrand = "T";    
$filterPrority = "T";

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

                <?php // include_once ROOT . 'Sales/includes/page-filter.php' 
                ?>
                <?php include_once ROOT . 'v3/Sales/includes/page-filter.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <!-- <div class="card">
                                    <div class="card-body"> -->
                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">
                                    <!-- -->
                                </div>

                                <!-- </div>
                                </div> -->

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

</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . '/includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>


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
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: 'PACKING REPORT',
                footer: true,
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: function (data, row, column, node) {

                            const dateColumns = [4, 13, 14];
                            const selectColumns = [18];
                            const inputColumns = [13, 14, 15, 16, 17];
                            const buttonColumns = [19];

                            var decodedData = $('<div>').html(data).text();
                            if (dateColumns.includes(column)) {
                                const dateInput = $(node).find('input[type="date"]');
                                const dateValue = dateInput.length > 0 ? dateInput.val() : data;

                                if (dateValue == null || String(dateValue).trim() === "") {
                                    return "";
                                }
                                if (dateValue === "--") {
                                    return "--";
                                }

                                const parsedDate = moment(dateValue, ["DD-MM-YYYY", "YYYY-MM-DD"], true);
                                return parsedDate.isValid() ? parsedDate.format("DD-MM-YYYY") : "";
                            } else if (inputColumns.includes(column)) {
                                if ($(node).find('input').length > 0) {
                                    return $(node).find('input').val();
                                } else {
                                    return decodedData;
                                }
                            } else if (selectColumns.includes(column)) {
                                if ($(node).children("select").length > 0) {
                                    const selectedOption = $(node).children("select").first().find(":selected").text();
                                    return selectedOption !== "Select Priority" ? selectedOption : "--";
                                } else {
                                    return decodedData;
                                }
                            } else if (buttonColumns.includes(column)) {
                                return "";

                            } else {
                                return $(node).text().trim();
                            }

                        }
                    }

                }
            }],
        });
    }
    $("#btnSearch").on("click", function () {
        $.ajax({
            url: 'Ajax/AjaxPackingData.php',
            type: 'POST',
            data: {
                exporterID: $("#drpExporterList").val(),
                consigneeID: $("#drpConsigneeList").val(),
                processorID: $("#drpProcessorList").val(),
                brandID: $("#drpBrandList").val(),
                prority: $("#drpPrority").val(),
            },
            success: function (data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    });

    $(document).on("click", ".btnSavePackOrder", function () {
        var orderId = $(this).attr("data-orderid");
        //var poGradId = $(this).attr("data-pogradid");

        var approvaldDate = $(this).closest("tr").find(".txtApprovaldDate").val();
        var orderedDate = $(this).closest("tr").find(".txtOrderedDate").val();
        var orderRemarks = $(this).closest("tr").find(".txtOrderRemarks").val();
        var masterCases = $(this).closest("tr").find(".txtMasterCases").val();
        var innerBags = $(this).closest("tr").find(".txtInnerBags").val();
        var listPrority = $(this).closest("tr").find(".drpListPrority").val();

        // alert(listPrority);

        $.ajax({
            url: 'Ajax/AjaxPackingData.php',
            type: 'POST',
            data: {
                type: "update",
                OrderId: orderId,
                //PoGradId: poGradId,
                ApprovaldDate: approvaldDate,
                OrderedDate: orderedDate,
                OrderRemarks: orderRemarks,
                MasterCases: masterCases,
                InnerBags: innerBags,
                ListPrority: listPrority
            },
            success: function (data) {
                console.log(data);
                if (!data.Error) {
                    alert(data.Msg);
                } else {
                    alert(data.Error);
                }
                return false;
            }
        });

        // console.log(orderId, poGradId, approvaldDate, orderedDate, masterCases, innerBags, listPrority);

    });
$("#drpConsigneeList, #drpProcessorList").on("change", function () {
    $("#divDataList").html("");
});
</script>

</body>

</html>
