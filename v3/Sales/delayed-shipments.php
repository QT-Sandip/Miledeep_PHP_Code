<?php
// ini_set('display_errorsF

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterExporter = "T";
$filterConsignee = "T";
$filterMonthSelect = "T";
$filterYear = "T";
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

<script>
   function fnShowDataTable() {
    var consigneeVal = $("#drpConsigneeList").val();
    var consigneeName = $.trim($("#drpConsigneeList option:selected").text());
    if (consigneeVal) {
        excelTitle = '' + consigneeName;
    } else {
        excelTitle = 'Delayed_Shipment_Report';
    }
// var consigneeName = $.trim($("#drpConsigneeList option:selected").text());
// if (consigneeName) {
//     excelTitle = '' + consigneeName;
// } else {
//     excelTitle = consigneeName + 'Report';
// }

var table = $('#tblDataTable').DataTable({
    scrollY: "600px",
    scrollX: true,
    sScrollXInner: "100%",
    paging: false,
    destroy: true,
    info: false,
    fixedColumns: true,
    scrollCollapse: true,
    dom: 'Blfrtip',
    buttons: [{
        extend: 'excel',
        text: 'Download Excel',
        className: 'btnExcelDataTable btn-left-align',
        title: excelTitle,
        footer: true,
        exportOptions: {
            columns: ':visible'
        }
    }],
 });

}

    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
                "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>"
            )
            .insertAfter("#tblDataTable_filter label");

        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }

    function ShowList(pageNo, searchkey, loadAll) {
        $.ajax({
            url: 'Ajax/ajax-delayed-shipments.php',
            type: 'POST',
            data: {
                Exporter: $("#drpExporterList").val(), //drpExporterList
                Consignee: $("#drpConsigneeList").val(), // drpConsigneeList
                Month: $("#drpMonth").val(), // drpMonth
                Year: $("#drpYear").val(), // drpYearOne
                PageNo: pageNo,
                SearchKey: searchkey,
                LoadAll: loadAll,
            },
            success: function(data) {
                fnSuccess(data);
                return false;
            }
        });
    }
    $(document).ready(function() {
        ShowList("1", "", "0");

        // $(document).on("click", ".delayedShipID", function() {
        //     alert('Hello');
        // });
    });

    $(document).on("click", "#btnMoreSearch", function() {
        var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", searchkey, "0");
        }
    });

    $(document).on("click", "#btnLoadAll", function() {
        ShowList("1", "", "1");
    });

    $(document).on("click", ".btnPageno", function() {
        var pageno = $(this).attr("data-pageno");
        ShowList(pageno, "", "0");
    });

    $("#btnSearch").on("click", function() {
        ShowList("1", "", "0");
    });
</script>

</body>

</html>