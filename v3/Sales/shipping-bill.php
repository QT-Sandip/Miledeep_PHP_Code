<?php

include_once $_SERVER['DOCUMENT_ROOT'].'/config/global.php';


$filterExporter = "T";
$filterConsignee = "T";
$filterCountry = "T";
$filterInvoiceStatus = "T";
$filterYear = "T";
$filterPlant = "T";

$filterButButton = "T";

?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>

</head>

<?php  include_once ROOT . '/includes/body-preloader.php' ?>

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
                                <!--  <div class="text-center">
                                        <button id="btnSearch" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                                              Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                                        </button>     
                                        </div>  -->

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
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
    type="text/javascript"></script>

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
            bInfo: false,
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
            buttons: [],

        });
    }
    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
            "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>")
            .insertAfter("#tblDataTable_filter label");


        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }
    function ShowList(pageNo, searchkey, loadAll) {
        $.ajax({
            url: 'Ajax/AjaxShippingBill.php',
            type: 'POST',
            data: {
                ExporterID: $("#drpExporterList").val(),
                ConsigneeID: $("#drpConsigneeList").val(),
                CountryID: $("#drpCountryList").val(),
                Year: $("#drpYear").val(),
                ProcessorID: $("#drpProcessorList").val(),
                BillsType: $("#drpStatus").val(),
                PageNo: pageNo,
                SearchKey: searchkey,
                LoadAll: loadAll,
            },
            success: function (data) {
                fnSuccess(data);
            }
        });
    }
    $(document).ready(function () {
        ShowList("1", "", "0");
    });
    $("#btnSearch").on("click", function () {
        ShowList("1", "", "0");
    });
    $(document).on("click", "#btnMoreSearch", function () {
        var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", searchkey, "0");
        }
    });
    $(document).on("click", ".btnPageno", function () {
        var pageno = $(this).attr("data-pageno");
        ShowList(pageno, "", "0");
    });

    $(document).on("click", "#btnLoadAll", function () {
        ShowList("1", "", "1");
    });
</script>

</body>

</html>
