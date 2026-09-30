 <?php

$PageTitle = "List";
$PageTitleFolder = "Quality Assurance Department";
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");



//echo '<pre>';print_r($dropDownData);die;
?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . 'includes/head.php' ?>
    

</head>

<?php include_once ROOT . 'includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . 'includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php //include_once ROOT . 'includes/page-header.php' ?>
                <?php include($IncludesPath . "page-title.php"); ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">                             
                                <form id="frmDeHeading">
                                    <div class="row">
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="ArrivalDate" class="form-label">Arrival Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="ArrivalDate"
                                                name="ArrivalDate" placeholder=""
                                                value="<?php echo date("Y-m-d"); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="BatchCode" class="form-label">Batch Code<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control  BatchCode form-control " name="BatchCode[]"
                                                id="BatchCode" required
                                                    multiple="multiple"> 
                                                <option value=""> Select</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-3 mt-4">
                                            <a class="btn btn-primary search""> Search</a>
                                            <a href="quality-assurance-check-list-form.php" class="btn btn-success waves-effect waves-light">
                                                <i class="fa fa-plus"></i> Add</a>
                                        </div>                                    
                                </form>
                            </div>

                        </div>
                        <div id="divDataList" class="p-3"></div>

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

<?php include_once WORKING_MODULE . 'modalSupplier.php' ?>


<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>

<script>

    $(document).ready(function () {
        loadData();
        $(document).on("click", ".search", function () {
            loadData();
        })
        $(document).on("click", ".btnPageno", function(e) {
            loadData( $(this).attr('data-pageno'));
        })
        function loadData(pageNo = '',excelDownload='') {
            // debugger
           // console.log($("#txtBatchCode").val());
            $.ajax({
                url: 'Ajax/ajax-quality-assurance-check-list.php',
                type: 'POST',

                data: {
                    methodType: "GET",
                    ArrivalDate: $("#ArrivalDate").val(),
                    BatchCode: $("#BatchCode").val(),
                    pageNo:pageNo,
                    excelDownload:excelDownload
                    
                },
                success: function (data) {
                    fnSuccess(data);
                }
            });
        }


        function fnSuccess(data) {
            $("#divDataList").html(data);
            fnShowDataTable();
        }

        function fnShowDataTable() {
            $('#tblDataTable').DataTable({
                scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                bInfo: false,
                bSort: false,
                dom: 'Blfrtip',
                buttons: [
                            {
                                //extend: 'excel',
                                text: 'Load All',
                                className: 'loadAll btn-left-align btn btn-warning pr-2',
                                //title: 'Soaking Report <?php echo date("d-m-Y") ?>',
                                footer: true,
                                /* exportOptions: {
                                    columns: ':visible'
                                }, */
                                action: function() {
                                    var excelDownload = 'T';
                                    loadData('',excelDownload);
                                }
                            },
                            {
                                extend: 'excel',
                                text: 'Download Excel',
                                className: 'btnExcelDataTable btn-left-align',
                                title: 'Soaking Report <?php echo date("d-m-Y") ?>',
                                footer: true,
                                exportOptions: {
                                    columns: ':visible'
                                },
                                action: function() {
                                    //fnPoStatusReportExcel('tblDataTable', 'Treatment_Register_Report');
                                    fnPoStatusReportExcelNew();
                                }
                            },
                        ],
            });
        }
        function fnPoStatusReportExcelNew() {
            var ArrivalDate = $("#ArrivalDate").val();
            var BatchCode = $("#BatchCode").val();
            var url = 'Ajax/ajax-quality-assurance-check-list.php?BatchCode=' + BatchCode.join(',') + '&ArrivalDate=' + ArrivalDate+ '&excelDownload=T';
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    window.location.href = data.filename;
                }
            });
        }

        $(document).ready(function () {
            let isFirstSaveDone = false;
            getArrivals();   
            $(document).on("change", "#ArrivalDate", function () {
                getArrivals();
            });
           

            function getArrivals() {
                $.ajax({
                    url: 'Ajax/ajax-quality-assurance-check-list.php',
                    type: 'POST',
                    data: {
                        ArrivalDate: $('#ArrivalDate').val(),
                        methodType: "GetBatchNumbers",
                        pageType:'listing'
                        // removePlantCode: removePlantCode

                    },
                    success: function (data) {
                        //console.log(data);
                        var list = "<option value=''>Select</option>";
                        if (data?.Data?.BatchData) {
                            $.each(data.Data.BatchData, function (index, value) {
                                list += "<option ";
                                list +="value='" + value.ArrivalId + "' >" + value.ArrivalNumber + "</option>";
                            });
                        }
                        // console.log(list)
                        $('#BatchCode').select2('destroy');
                        $('#BatchCode').html(list);
                        $('#BatchCode').select2();

                        return false;
                    }
                });
            }


            $(document).on("click", ".delete", function () {


                if (confirm("Are you sure you want to delete this item ?") == true) {

                    var row = $(this).closest("tr");
                    //  debugger
                    var AssuranceId = $(this).attr("data-assurance_id");
                    $.ajax({
                        url: 'Ajax/ajax-quality-assurance-check-list.php',
                        type: 'POST',
                        data: {
                            methodType: "deleteRow",
                            AssuranceId: AssuranceId,


                        },
                        success: function (data) {
                           // console.log(data);
                            loadData();
                            
                            if (data.Error) {
                                alert(data.Error);
                            } else {
                                alert(data.Msg);

                            }

                            return false;
                        }
                    });
                }

            });
        });
    });
    
</script>


</script>

</body>

</html> 