<?php
/* treatment-register-details-report */
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$filterfromDate = "T";
$filtertoDate = "T";
//$filterProductionLine = "T";
$filterButtonSearch = "T";

$ProductionPlant = $_SESSION['UnitID'];
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;
if ($IsPlantFilter == 1) {
    $filterReceivingPlant = "T";
}
// echo WORKING_MODULE;
// exit;
$bodyContent = [];
$api = API_QC_TREATMENT_REGISTER_DROPDOWN;
$dropDownData = CallAPI($api, "GET", "", $bodyContent);
$shiftData = !empty($dropDownData->Data->shiftData) ? $dropDownData->Data->shiftData : [];
//echo '<pre>';print_r($dropDownData);die;

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
                <?php //include_once WORKING_MODULE . '/includes/page-filter.php' 
                ?>
                <div class="row">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label>Soaking Date<span style="color:red">*</span></label>
                                    <input type="date" value="" class="form-control" name="SoakDate" id="SoakDate">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label for="Shift">Shift</label>
                                    <select class="form-control" name="Shift" id="Shift">
                                        <option value="">Select</option>
                                        <?php
                                        if (!empty($shiftData)) {
                                            foreach ($shiftData as $siftRow) { ?>
                                                <option value="<?php echo $siftRow->ShiftID; ?>"><?php echo $siftRow->ShiftName; ?></option>
                                        <?php }
                                        } ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <button id="btnSearch" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                                        Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">

                            <div class="card-body">


                                <div id="divDataList">

                                </div>
                                <input type="hidden" id="hdnActionType" />
                                <?php
                                if ($IsPlantFilter == 0) {
                                    echo '<input type="hidden" id="drpReceivingPlantId" />';
                                }
                                ?>
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
    <!-- Modal -->

</div>
<!-- END layout-wrapper -->




<?php include_once ROOT . '/includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
    type="text/javascript"></script> -->
<!--  <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script> -->



<script>
    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            scrollCollapse: true,
            bInfo: false,
            bSort: false,
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
            buttons: [{
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
            }],
        });
    }

    function ShowList() {
        var txtSoakDate = $("#SoakDate").val();
        var Shift = $("#Shift").val();
        var SoakDate = new Date(txtSoakDate);

        var drpReceivingPlantId = $("#drpReceivingPlantId").val();
        var txtStartDate = $("#fromDate").val();
        var txtEndDate = $("#toDate").val();
        var startDate = new Date(txtStartDate);
        var endDate = new Date(txtEndDate);
        var drpLineNumber = $("#drpLineNumber").val();

        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

        var days = daysDiff + 1;

        if (days > 31) {
            var url = 'Ajax/ajax-treatment-register-report-qc.php?Shift=' + Shift + '&txtSoakDate=' + txtSoakDate + '&excelDownload=T';
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    window.location.href = data.filename;
                }
            });

        } else {
            $.ajax({
                url: 'Ajax/ajax-treatment-register-report-qc.php',
                type: 'POST',
                data: {
                    SoakDate: txtSoakDate,
                    Shift: Shift
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();

                    return false;
                }
            });
        }


    }

    function fnPoStatusReportExcelNew() {
        var txtSoakDate = $("#SoakDate").val();
        var Shift = $("#Shift").val();
        var SoakDate = new Date(txtSoakDate);
        var url = 'Ajax/ajax-treatment-register-report-qc.php?Shift=' + Shift + '&txtSoakDate=' + txtSoakDate + '&excelDownload=T';
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                window.location.href = data.filename;
            }
        });
    }

    $("#btnSearch").on("click", function() {
        var SoakDate = $("#SoakDate").val();
        if (SoakDate) {
            ShowList();
        } else {
            alert('Select Soaking Date')
        }
    })
</script>

</body>

</html>
