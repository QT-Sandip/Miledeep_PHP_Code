<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// phpinfo();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

// $filterArrivalPlant = "T";
// $filterFromDate2 = "T";
// $filterToDate2 = "T";
// $filterButtonSearch = "T";
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


                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <form id="frmGrading">
                                    <div class="row">
                                        <input type="hidden" id="hdnID" name="hdnID" value="" />

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Arrival Date</label>
                                            <input type="date" class="form-control" id="txtArrivalDate"
                                                name="txtArrivalDate" placeholder=""
                                                value="<?php echo date("Y-m-d"); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div>


                                        <!-- <div class="form-group col-sm-3 mb-3">
                                            <label for="txtBatchCode" class="form-label">Batch Code<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="txtBatchCode"
                                                id="txtBatchCode">
                                                <option value=""> Select</option>
                                            </select>
                                        </div> -->

                                        <!-- <div class="col-md-3 col-lg-3 mb-3 d-flex align-items-end">
                                            <button type="button" id="btnSearch" class="btn btn-primary">Search</button>
                                        </div> -->

                                    </div>



                                </form>

                            </div>
                        </div>
                        <div id="divDataList">
                            <!-- -->
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

    $(document).ready(function() {
        //  loadData();
        function loadData() {
            $.ajax({
                url: 'Ajax/AjaxGradingInspection.php',
                type: 'POST',
                data: {
                    methodType: "getReportDetails",
                    ArrivalDate: $("#txtArrivalDate").val(),
                    // BatchCode: $("#txtBatchCode").find(':selected').attr('data-batch'),
                },
                success: function(data) {
                    console.log("getReportDetails", data)
                    fnSuccess(data);
                }
            });
        }


        function fnSuccess(data) {
            $("#divDataList").html(data);
            fnShowDataTable();
            $("<button class='btn btn-secondary buttons-excel buttons-html5 btnExcelDataTable' id='btnExcelDownload' style='margin-left: 15px;' type='button'><span>Download Excel</span></button>")
                .insertAfter("#tblDataTable_filter label");

        }

        function fnShowDataTable() {

            var ArrivalDate = FormattedDate($("#txtArrivalDate").val());

            $('#tblDataTable').DataTable({
                scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                bInfo: false,
                ordering: false,
                title: ' COASTAL AQUA PRIVATE LIMITED ',
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
            buttons:[]
                // dom: 'Blfrtip',
                // buttons: [{
                //     extend: 'excel',
                //     text: 'Download Excel',
                //     className: 'btnExcelDataTable',
                //     title: DeheadingCheckList,
                //     footer: true,
                //     exportOptions: {
                //         columns: ':visible'
                //     }
                // }],

            });
        }



        $(document).on("click", "#btnExcelDownload", function() {
            var ArrivalDate = $("#txtArrivalDate").val();
            // var BatchCode = $("#txtBatchCode").val();
            // var Batch = $('#txtBatchCode').find(':selected').attr('data-batch');

            // var queryString = "Ajax/AjaxGradingInspection.php?ArrivalDate=" + ArrivalDate + "&BatchCode=" + BatchCode + "&Batch=" + Batch + "&excelDownload=T";
            var queryString = "Ajax/AjaxGradingInspection.php?ArrivalDate=" + ArrivalDate + "&excelDownload=T";

            window.open(queryString, '_blank');
        });



        // Submit handler for form
        $('#frmGrading').on('submit', function(e) {
            e.preventDefault();
            loadData();
        });

        $(document).on("change", "#txtArrivalDate", function() {
            loadData(); // Directly load data now
        });


        $(document).on("change", "#txtBatchCode", function() {
            loadData();
        });

        //  $(document).on("click", ".btnSearch", function () {
        //         loadData();
        //         })


        function getArrivals() {
            $.ajax({
                url: 'Ajax/AjaxGradingInspection.php',
                type: 'POST',
                data: {
                    ArrivalDate: $('#txtArrivalDate').val(),
                    methodType: "getReportLotDetails",
                },
                success: function(data) {
                    console.log(data);
                    var list = "<option value=''>Select</option>";
                    $.each(data.Data, function(index, value) {
                        list += "<option value='" + value.ArrivalID + "' data-batch='" + value.BatchCode + "'>" + value.BatchCode + "</option>";
                    });

                    $('#txtBatchCode').select2('destroy');
                    $('#txtBatchCode').html(list);
                    $('#txtBatchCode').select2();

                    return false;
                }
            });
        }




        // $(document).on("click", ".btnSearch", function () {
        $("#btnSearch").on("click", function() {
            var ArrivalDate = $("#txtArrivalDate").val();
            var BatchCode = $("#txtBatchCode").find(':selected').attr('data-batch');

            if (BatchCode !== '') {
                loadData();
            } else {
                alert('Select BatchCode ')
            }


        });




    });
</script>
