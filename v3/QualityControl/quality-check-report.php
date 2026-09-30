<?php

session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$PageTitle = "VA Quality Check Report";
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

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

                <?php include_once ROOT . 'includes/page-header.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <form id="frmQuantiryCheck">
                                    <div class="row">

                                        <input type="hidden" id="hdnID" name="hdnID" value="" />
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Value Addition Date<span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtArrivalDate"
                                                name="txtArrivalDate" placeholder=""
                                                value="<?php echo date("Y-m-d"); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <!-- <div class="form-group col-sm-3 mb-3">
                                            <label for="drpBatchCode" class="form-label">Batch Number<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpBatchCode"
                                                id="drpBatchCode">
                                                <option value=""> Select</option>
                                            </select>
                                        </div> -->



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

<?php include_once WORKING_MODULE . 'modalSupplier.php' ?>


<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<?php include_once ROOT . '/includes/app-js.php' ?>

<script>

     function FormattedDate(oldformatDate) {
               var dateObject = new Date(oldformatDate);
               var FormattedDate = dateObject.getDate()+"/"+(dateObject.getMonth()+1)+"/"+dateObject.getFullYear();
               return FormattedDate;
            }

            
    $(document).on("change", "#txtArrivalDate", function () {
        getArrivals();
    });
    $(document).on("change", "#drpBatchCode", function () {
        if ($("#drpBatchCode").val() != '') {
            var variety = $("#drpBatchCode option:selected").data("variety");
            $("#drpVariety").val(variety);
            loadData();
        }
    });
    $('document').ready(function () {
        getArrivals();
        // getting Track Codes
       
       
        $("#frmQuantiryCheck").validate({
            onkeyup: false,
            onfocusout: false,
            invalidHandler: function (form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    firstInvalidElement.focus();
                }
            },
            errorPlacement: function () {
                return false; // suppresses error message text
            },
            submitHandler: function () {
                $.ajax({
                    url: 'Ajax/ajax-quality-check-report.php',
                    type: 'POST',
                    data: {
                        methodType: "TableSave",
                        QuantityCheckid: $("#hdnID").val(),
                        ArrivalDate: $("#txtArrivalDate").val(),
                        BatchCode: $("#drpBatchCode").val(),
                        RoomTemperature: $("#txtRoomTemperature").val(),
                        TableNo: $("#txtTableNo").val(),
                        Variety: $("#drpVariety").val(),
                        Grade: $("#drpGrade").val(),
                        TypeOfCut: $("#txtTypeOfCut").val(),
                        ImproperPeeling: $("#drpImproperPeeling").val(),
                        ImproperCutting: $("#drpImproperCutting").val(),
                        Vein: $("#drpVein").val(),
                        Broken: $("#drpBroken").val(),
                        OtherObjectionDefects: $("#drpOtherObjectionDefects").val(),
                        TotalPeelingGirls: $("#txtTotalPeelingGirls").val(),
                        HeadLessQuantity: $("#txtHeadLesQuantity").val(),
                        Quantity: $("#txtQuantity").val(),
                        Yield: $("#txtYield").val(),
                        CheckingGirls: $("#txtCheckingGirls").val(),
                        SuperVisor: $("#txtSuperVisor").val(),
                        VerifiedBy: $("#txtVerifiedBy").val(),
                         VAStation: $("#drpVAStation").val(),

                    },
                    success: function (data) {

                        if (data.Error) {
                            alert(data.Error);
                        } else {
                            alert(data.Msg);
                            loadData();
                            if($("#hdnID").val() != ''){
                                $("#hdnID").val('');
                                $('#frmQuantiryCheck')[0].reset();
                                var row = $(this).closest("tr");
                                $("button[name='submit']").text("Save").removeClass("btn-primary").addClass("btn-primary");
                            }
                        }


                    }
                });
            }
        });
    });

 

    $(document).ready(function () {
    getArrivals();

    $('#txtArrivalDate').on('change', function () {
        loadData();
    });

    loadData();  // initial load on page ready
});

function loadData() {
    $.ajax({
        url: 'Ajax/ajax-quality-check-report.php',
        type: 'POST',
        data: {
            arrivalDate: $('#txtArrivalDate').val(),
            methodType: 'GET'
        },
        success: function (data) {
            fnSuccess(data);
        },
        error: function (xhr, status, error) {
            alert('Error fetching data: ' + error);
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

        table = $('#tblDataTable').DataTable({
           scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                bInfo: false,
                 ordering: false,
            // dom: 'Blfrtip',
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
            buttons:[],
           initComplete: function () {
            // Insert the button once DataTable finishes initialization
            if (!$('#btnExcelDownload').length) {
                $("<button class='btn btn-secondary buttons-excel buttons-html5 btnExcelDataTable' id='btnExcelDownload' style='margin-left: 15px;' type='button'><span>Download Excel</span></button>")
                    .insertAfter("#tblDataTable_filter label");
            }
        }
       
    });
}
    


      $(document).on("click", "#btnExcelDownload", function () {
    var arrivalDate = $("#txtArrivalDate").val();
    var batchCode = $("#drpBatchCode").val();
    var queryString = "Ajax/ajax-quality-check-report.php?ArrivalDate=" + arrivalDate + "&BatchCode=" + batchCode + "&excelDownload=T";
    window.open(queryString, '_blank');
});



    function getArrivals() {

        $.ajax({
            url: 'Ajax/ajax-quality-check-report.php',
            type: 'POST',
            data: {
                arrivalDate: $('#txtArrivalDate').val(),
                methodType: "QualityCheck",
            },
            success: function (data) {
                var list = "<option value=''>Select</option>";
                if (data?.Data && data.Data.length > 0) {
                    $.each(data.Data, function (index, value) {
                        list += "<option value='" + value.ArrivalId + "'  data-plantid='" + value.PlantID + "' data-variety='" + value.Variety + "'>" + value.ArrivalNumber + "</option>";
                    });
                }

                $('#drpBatchCode').select2('destroy');
                $('#drpBatchCode').html(list);
                $('#drpBatchCode').select2();
                return false;
            }
        });
    }

    function fnSuccess(data) {
        $("#divDataList").html(data);
        fnShowDataTable();
    }



</script>


</body>

</html>
