<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_DROPDOWNS;
$bodyContent = array(
    'PageType' => 'REPORTS',
);

$resDropdownsAPI = callAPI($api, "GET", "", $bodyContent);

// $resDropdownsAPI = CallAPI($api, "GET", "", "");

$resExporter = $resDropdownsAPI->Data->Exporter;
$resSpecies = $resDropdownsAPI->Data->Species;
$resProcessingStations = $resDropdownsAPI->Data->ProcessingStations;
$resPurchaseCenters = $resDropdownsAPI->Data->PurchaseCenters;
// $resOverAllBalWeight = $resDropdownsAPI->Data->OverAllBalWeight;


// var_dump($resDropdownsAPI);
// Species
// PurchaseCenters
// ProcessingStations
// PurchaseCategory
// Vehicles
// RmAgents
// Drivers
// RmGraders
// RmSuppliers
// RmVariety

$filterExporter = "T";
$filterConsignee = "T";
$filterCountry = "T";
$filterInvoiceStatus = "T";
$filterYear = "T";

$filterSideButton = "T";
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
                <?php // include_once ROOT . '/Sales/includes/page-filter.php' 
                ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <form role="form" id="frmRmOverAllArrival" method="post">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="txtFromDate">From Date</label>
                                            <input type="date" class="form-control" name="txtFromDate" id="txtFromDate"
                                                placeholder="Enter from date" value="<?php echo date('Y-m-d'); ?>" />
                                        </div>
                                        <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="txtDate">To Date</label>
                                            <input type="date" class="form-control" name="txtDate" id="txtDate"
                                                placeholder="Enter to Date" value="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="drpCompany">Company</label>
                                            <select class="form-control drpSelect" name="drpCompany" id="drpCompany">
                                                <option value="">Select</option>
                                                <!-- <option value="2">Devi Fisheries</option> -->
                                                <?php
                                                foreach ($resExporter as $Exporter) {
                                                    echo '<option value="' . $Exporter->ExpID . '">' . $Exporter->ExpName . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="drpPlant">Species</label>
                                            <select class="form-control drpSelect" name="drpSpieces" id="drpSpieces">
                                                <option value="">Select</option>
                                                <?php
                                                foreach ($resSpecies as $item)
                                                    echo '<option value="' . $item->ShortCode . '">' . $item->SpeciesName . '</option>';
                                                ?>
                                            </select>
                                        </div>



                                        <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="drpPlant">Plant</label>
                                            <select class="form-control drpSelect" name="drpPlant" id="drpPlant">
                                                <option value="">Select</option>
                                                <?php
                                                foreach ($resProcessingStations as $item)
                                                    echo '<option value="' . $item->ProcessingStationID . '">' . $item->StationName . '</option>';
                                                ?>
                                            </select>
                                        </div>



                                        <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="drpCenter">Center</label>
                                            <select class="form-control drpSelect" name="drpCenter" id="drpCenter">
                                                <option value="">Select</option>
                                                <?php
                                                foreach ($resPurchaseCenters as $item)
                                                    echo '<option value="' . $item->PurchaseStationID . '">' . $item->StationName . '</option>';
                                                ?>
                                            </select>
                                        </div>


                                        <div class="col col-md-3 mb-3">
                                            <label class="form-label" for="drpType">Status Type</label>
                                            <select class="form-control drpSelect" name="drpType" id="drpType">
                                                <option value="">Select</option>
                                                <option value="0">Pending</option>
                                                <option value="1">Completed</option>
                                                <option value="2">Exceed</option>
                                            </select>
                                        </div>



                                        <!-- <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="txtWeight">Weight</label>
                                            <input type="text" class="form-control" name="txtWeight" id="txtWeight"
                                                placeholder="Enter Weight" value="" />
                                            <div></div>
                                        </div>
                                        <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="txtAmount">Amount</label>
                                            <input type="text" class="form-control" name="txtAmount" id="txtAmount"
                                                placeholder="Enter Amount" value="" />
                                        </div> -->

                                        <!-- <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="txtNumber">Number</label>
                                            <input type="text" class="form-control" name="txtNumber" id="txtNumber"
                                                placeholder="Enter Number" value="" />
                                        </div> -->

                                        <!-- <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="txtAverageRate">Average Rate</label>
                                            <input type="text" class="form-control" name="txtAverageRate" readonly
                                                id="txtAverageRate" placeholder="Enter Average Rate" value="" />
                                        </div> -->
                                        <div class="col-lg-3 mb-3">
                                            <div><label class="form-label">&nbsp;</label></div>
                                            <div> <button type="button"
                                                    class="btn btn-primary waves-effect waves-light me-1 btnSearch">
                                                    Search </button></div>
                                        </div>





                                        <div class="mb-0">
                                            <div class="text-end">
                                                <input type="hidden" value="" id="hdnID" />
                                                <!-- <button type="reset" class="btn btn-danger waves-effect">
                                                    Cancel
                                                </button> -->

                                                <!-- <button type="button"
                                                    class="btn btn-success waves-effect waves-light me-1 btnNew">
                                                    New
                                                </button>
                                                <button type="submit"
                                                    class="btn btn-primary waves-effect waves-light me-1">
                                                    Submit
                                                </button> -->

                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </form>

                            <div class="my-3 mx-3">
                                <div id="divDataList">
                                    <!-- -->
                                </div>
                                <input type="hidden" id="hdnActionType" />

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

            // dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: 'RM Over All Arrival',
                footer: true,
                // exportOptions: {
                //     columns: ':visible'
                // }
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            }],
        });
        table.on('draw.dt', function () {

            let totalWeight = 0;
            let amount = 0;
            $('.tdWeight').each(function () {

                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    totalWeight += weight;
                }
            });
            $('.tdAmount').each(function () {

                const value = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(value)) {
                    amount += value;
                }
            });
            $('#TotalWeight').text(totalWeight.toLocaleString('en-US', {
                minimumFractionDigits: 3,
                maximumFractionDigits: 3
            }));
            $('#TotalAmount').text(amount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });

    }

    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        // $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
        //     "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>")
        //     .insertAfter("#tblDataTable_filter label");

        $("<button class='btn btn-secondary buttons-excel buttons-html5 btnExcelDataTable' id='btnExcelDownload' style='margin-left: 15px;' type='button'><span>Download Excel</span></button>")
            .insertAfter("#tblDataTable_filter label");


        // $('html, body').animate({
        //     scrollTop: $("#divDataList").offset().top - 50
        // }, 10);
    }




    function ShowList() {

        var ArrivalFromDate = $("#txtFromDate").val();
        var ArrivalDate = $("#txtDate").val();
        var Company = $("#drpCompany").val();
        var Plant = $("#drpPlant").val();
        var Center = $("#drpCenter").val();
        var Spieces = $("#drpSpieces").val();
        var statusType = $("#drpType").val();

        //  console.log(ArrivalFromDate);
        // console.log(ArrivalDate);

        var startDate = new Date(ArrivalFromDate);
        var endDate = new Date(ArrivalDate);

        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

        var days = daysDiff + 1;

        // console.log(days);

        if (days > 31) {
            exlRmOverallReport();
        } else {
            $.ajax({
                url: 'Ajax/AjaxRmOverAllArrival.php',
                type: 'POST',
                data: {
                    Report: "T",
                    ArrivalFromDate: $("#txtFromDate").val(),
                    ArrivalDate: $("#txtDate").val(),
                    Company: $("#drpCompany").val(),
                    Plant: $("#drpPlant").val(),
                    Center: $("#drpCenter").val(),
                    Species: $("#drpSpieces").val(),
                    StatusType: $("#drpType").val(),
                    MethodType: "Get"
                },
                success: function (data) {
                    console.log(data);
                    //$("#divDataList").html(data);
                    // fnShowDataTable();
                    fnSuccess(data);
                    return false;
                }
            });
        }
    }
    $(document).ready(function () {
        ShowList();

        $(document).on("click", "#btnExcelDownload", function () {
            exlRmOverallReport();
        });
    });

    $(document).on("click", ".btnSearch", function () {
        ShowList();
    })

    function exlRmOverallReport() {
        console.log($('#select2-drpCenter-container').text().split(' ').join('_'));
        var arrivalFromDate = $("#txtFromDate").val();
        var arrivalDate = $("#txtDate").val();
        var plant = $("#drpPlant").val();
        var plantName = $("#select2-drpPlant-container").text().split(' ').join('-');
        var center = $("#drpCenter").val();
        var centerName = $('#select2-drpCenter-container').text().split(' ').join('_');

        var company = $("#drpCompany").val();
        var companyName = $("#select2-drpCompany-container").text();
        var spieces = $("#drpSpieces").val();
        var statusType = $("#drpType").val();

        // console.log(arrivalFromDate,arrivalDate,plant);
        var queryString = "/v3/Purchase/Reports/DocExlRmOverAllArrivalReport?ArrivalFromDate=" + arrivalFromDate + "&ArrivalDate=" + arrivalDate + "&PlantID=" + plant + "&PlantName=" + plantName + "&CenterID=" + center + "&CenterName=" + centerName + "&CompanyID=" + company + "&CompanyName=" + companyName + "&Species=" + spieces + "&StatusType=" + statusType;
        window.open(queryString, '_blank');

    };

    $("#txtAmount, #txtWeight").on("keyup", function () {
        var amt = parseFloat($("#txtAmount").val());
        var weg = parseFloat($("#txtWeight").val());

        var avgRate = (amt / weg).toFixed(2);

        if ($.isNumeric(avgRate)) {

            $("#txtAverageRate").val(avgRate);
        } else {
            $("#txtAverageRate").val("");

        }
    });
</script>


</body>

</html>
