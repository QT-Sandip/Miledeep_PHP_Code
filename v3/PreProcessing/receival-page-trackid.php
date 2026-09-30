<?php

session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

//print_r($_SESSION); 

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$PageTitle = "Rm Received trackid";

$ClientConfigData = (object) $_SESSION['ClientConfigData'];

error_log(print_r($ClientConfigData, true));
$IsVehicleTrack = $ClientConfigData->IsVehicleTrack;
$IsPlantFilter = $ClientConfigData->IsPlantFilter;  //sriram
$IsHONPacking = $ClientConfigData->IsHONPacking;  //sriram
$removePlantCode = $ClientConfigData->removePlantCode;  //sriram
$CratePageAccess = $ClientConfigData->CratePageAccess; 


$ArrivalID = trim($_GET['ArrivalID']);

$ArrivalID = isset($_GET['ArrivalID']) && !empty($_GET['ArrivalID']) ? $_GET['ArrivalID'] : "";

$api = API_PREPROCESSING_DROPDOWNS;

$bodyContent = array(
    'ArrivalID' => $ArrivalID
);

$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resCenter = $resDropdownsAPI->Data->Center;
$resProductionPlant = $resDropdownsAPI->Data->Plant;
$resShift = $resDropdownsAPI->Data->Shift;
$resSupervisor = $resDropdownsAPI->Data->Supervisor;

$List = $resDropdownsAPI->Data;

$PlantID = $_SESSION['UnitID'];
$ClientID = $_SESSION['ClientID'];
// echo $PlantID;
// exit;

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
                                <!-- <h4 class="card-title">Bootstrap Validation - Normal</h4> -->
                                <form id="frmReceival">
                                    <div class="row">

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpArrivalPlant" class="form-label">Production Plant<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpArrivalPlant"
                                                id="drpArrivalPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionPlant as $Item) { ?>
                                                    <option value="<?php echo $Item->ProcessingStationID; ?>">
                                                        <?php echo $Item->FullName . ' - ' . $Item->StationName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Arrival Date</label>
                                            <input type="date" class="form-control" id="txtArrivalDate"
                                                name="txtArrivalDate" placeholder=""
                                                value="<?php echo date("Y-m-d"); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpVehicleNumber" class="form-label">Vehicle Number<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpVehicleNumber"
                                                id="drpVehicleNumber">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtTrackCode" class="form-label">Track Code Format<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtTrackCode"
                                                name="txtTrackCode" placeholder="Enter Track Code Format" readonly>
                                            <input type="hidden" id="hdnTackId" />
                                        </div>
                                        <?php if ($removePlantCode == 1) { ?>
                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="txtSealNumber" class="form-label">Seal Number
                                                    <?php if ($ClientID != 'SMEV3') { ?>
                                                        <span style="color:red">*</span>
                                                    <?php } ?></label>
                                                <input type="text" class="form-control" id="txtSealNumber"
                                                    name="txtSealNumber" maxlength="15" placeholder="Enter Seal Number"
                                                    value="NA" disabled readonly>
                                            </div>
                                        <?php } else { ?>
                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="txtSealNumber" class="form-label">Seal Number
                                                    <?php if ($ClientID != 'SMEV3') { ?>
                                                        <span style="color:red">*</span>
                                                    <?php } ?></label>
                                                <input type="text" class="form-control" id="txtSealNumber"
                                                    name="txtSealNumber" maxlength="15" placeholder="Enter Seal Number">
                                            </div>
                                        <?php } ?>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpSupervisor" class="form-label">Supervisor
                                                <?php if ($ClientID != 'SMEV3') { ?>
                                                    <span style="color:red">*</span>
                                                <?php } ?></label>
                                            <select class="drpSelect form-control" name="drpSupervisor"
                                                id="drpSupervisor"
                                                <?php echo !empty($CratePageAccess) ? 'disabled' : '';?>>
                                                <option value=""> Select</option>
                                                <?php foreach ($resSupervisor as $Item) { ?>
                                                    <option value="<?php echo $Item->ID; ?>">
                                                        <?php echo $Item->UserName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea class="form-control" id="txtRemarks" maxlength="50"
                                                name="txtRemarks" placeholder="Enter Remarks" style="height: 38px;"><?php // echo $Data->ExporterAddress 
                                                ?></textarea>
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <input type="hidden" id="hdnSaveType" name="hdnSaveType"
                                            value="<?php echo $ArrivalID ?>" />
                                        <input type="hidden" id="hdnNewArrival" name="hdnNewArrival" />
                                        <!-- <a href="#" class="btn btn-danger">Close</a> -->
                                        <!-- <a href="javascript:void(0)" class="newItem btn btn-success waves-effect waves-light">
                                            <i class="fa fa-plus"></i> New
                                        </a> -->
                                        <button class="btn btn-primary" name="submit" id="submit"
                                            type="submit">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="divDataList">
                            <!--  -->
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
    var regex1 = /^\d{0,3}(\.\d{0,3})?$/; // txtSampleQty  999.999
    var regex2 = /^\d{0,4}?$/; // txtNoOfPcs  9999
    var regex3 = /^\d{1,3}(\.\d{0,3})?$/; //  txtRecvCount  999.999
    var regex4 = /^\d{1,5}(\.\d{0,3})?$/; // txtRecvQty 



    var arrivalID = "<?php echo isset($_GET["ArrivalID"]) && !empty($_GET["ArrivalID"]) ? $_GET["ArrivalID"] : "" ?>";
    var table;

    // $(document).on('keydown', 'input[pattern]', function(e) {
    //     var input = $(this);
    //     var oldVal = input.val();
    //     var regex = new RegExp(input.attr('pattern'), 'g');

    //     setTimeout(function() {
    //         var newVal = input.val();
    //         if (!regex.test(newVal)) {
    //             input.val(oldVal);
    //         }
    //     }, 1);
    // });

    function fnDateformat(dt) {
        // var arrivalDate = $("#txtArrivalDate").val();
        var parts = dt.split("-");
        return parts[2] + "-" + parts[1] + "-" + parts[0];
    }

    function fnShowDataTable() {

        var excelHeader = "Recevied__" + fnDateformat($("#txtArrivalDate").val()) + "_" + $.trim($("#drpVehicleNumber option:selected").text()) + "_" + $("#txtTrackCode").val() + "_" + $("#txtSealNumber").val();
        // var excelHeader = "Recevied__<?php echo date("d-m-Y") ?>_" +$.trim($("#drpVehicleNumber option:selected").text()) +"_"+ $("#txtTrackCode").val() +"_"+ $("#txtSealNumber").val();


        table = $('#tblDataTable').DataTable({
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
                title: excelHeader, //'Recevied  -<?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    // columns: ':visible',
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                    format: {
                        body: function (inner, rowidx, colidx, node) {
                            if ($(node).find("select").length > 0) {
                                return $(node).find("select option:selected").text();
                            }
                            // Check if cell contains an input field
                            else if ($(node).find("input").length > 0) {
                                return $(node).find("input").val();
                            }
                            // Default return for normal text
                            return inner;
                        }
                    }
                }
            }],
        });

        table.on('draw.dt', function () {
            let arrivalWeight = 0;
            let sampQty = 0;
            let recvQty = 0;
            let qtyDiff = 0;

            $('.txtWeight').each(function () {
                const weight = parseFloat($(this).val().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    arrivalWeight += weight;
                }
            });

            $('.txtRecvCount').each(function () {
                const weight = parseFloat($(this).val().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    sampQty += weight;
                }
            });

            $('.txtRecvQty').each(function () {
                const weight = parseFloat($(this).val().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    recvQty += weight;
                }
            });
            $('.tdQuantityDifference').each(function () {
                const weight = parseFloat($(this).val().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    qtyDiff += weight;
                }
            });

            $('#TotalWeight').text(arrivalWeight.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#TotalSampQty').text(sampQty.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#TotalRecvQty').text(recvQty.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#QtyDiff').text(qtyDiff.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });

    }

    $(document).on("change", "#txtArrivalDate", function () {
        getArrivals();
        $("#divDataList").html("");
        $("#drpSupervisor").val("").trigger("change");
        $("#txtTrackCode, #txtSealNumber, #txtRemarks").val("");
        var removePlantCode = <?php echo $removePlantCode ?>; // sriram
        if (removePlantCode == 1) {
            $("#txtSealNumber").val("NA");
        }

    });

    function getArrivals() {

        var productionPlant = <?php echo $PlantID ?>;
        var IsVehicleTrack = <?php echo $IsVehicleTrack ?>;
        var IsHONPacking = <?php echo $IsHONPacking ?>;

        // if(IsVehicleTrack==0){
        //     getTrackCodes();
        //     return;
        // }


        var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram
        var removePlantCode = <?php echo $removePlantCode ?>; // sriram
        if (productionPlant != 0 || IsPlantFilter == 0) {
            $("#drpArrivalPlant").prop('disabled', true);
            if (IsPlantFilter == 0) {
                $("#drpArrivalPlant").val();
            } else {
                $("#drpArrivalPlant").val(productionPlant).trigger('change');

            }
        } else {
            $("#drpArrivalPlant").prop('disabled', false);
        }


        $.ajax({
            url: 'Ajax/ajax-receival-page-trackid.php',
            type: 'POST',
            data: {
                ArrivalDate: $('#txtArrivalDate').val(),
                plantid: $("#drpArrivalPlant").val(),
                Type: "Vehicle",
                IsVehicleTrack: IsVehicleTrack,
                IsHONPacking: IsHONPacking,
                removePlantCode: removePlantCode
            },
            success: function (data) {
                console.log(data);

                var list = "<option value=''>Select</option>";
                $.each(data.Data.VehicleData, function (index, value) {
                    list += "<option value='" + value.VehicleID + "' data-centerid='" + value.CenterID + "' data-plantid='" + value.PlantID + "' data-receivingID='" + value.ReceivingID + "'>" + value.VehicalNumber + "" + value.TrackCode + "</option>";
                });

                $('#drpVehicleNumber').select2('destroy');
                $('#drpVehicleNumber').html(list);
                $('#drpVehicleNumber').select2();

                return false;
            }
        });
    }

    // Function to validate input fields against regex pattern
    function validateInputs(selector, regex) {
        $(selector).each(function () {
            var inputValue = $(this).val();

            if (regex.test(inputValue)) {
                $(this).removeClass('error').addClass('valid');

                if ($(this).closest("tr").find("input").hasClass("error")) {
                    $(this).closest("tr").find(".btnUpdateRow, .btnSaveRow").prop("disabled", true);
                } else {
                    $(this).closest("tr").find(".btnUpdateRow, .btnSaveRow").prop("disabled", false);
                }

                //$(this).closest("tr").find(".btnUpdateRow").prop("disabled", false);
            } else {
                $(this).removeClass('valid').addClass('error');
                $(this).closest("tr").find(".btnUpdateRow, .btnSaveRow").prop("disabled", true);
            }
        });
    }

    $(document).ready(function () {
        // fnShowDataTable();

        getArrivals();
        var removePlantCode = <?php echo $removePlantCode ?>; // sriram

        $.ajax({
            url: 'Ajax/ajax-receival-page-trackid.php',
            type: 'POST',
            data: {
                methodType: "get",
                ArrivalID: arrivalID,
                removePlantCode: removePlantCode
            },
            success: function (data) {

                $("#divDataList").html(data);
                fnShowDataTable();

                return false;
            }
        });

        // Validate input fields on page load
        fnValidateBreakup(); // Breakup Validation

        // Attach keyup event listeners to input fields
        $(document).on('keyup', '#tblDataTable .txtSampleQty', function () {
            validateInputs(this, regex1);
        });

        $(document).on('keyup', '#tblDataTable .txtNoOfPcs', function () {
            validateInputs(this, regex2);
        });

        $(document).on('keyup', '#tblDataTable .txtRecvCount', function () {
            validateInputs(this, regex3);
        });

        $(document).on('keyup', '#tblDataTable .txtRecvQty', function () {
            validateInputs(this, regex4);
        });

        $(document).on('change', '#drpArrivalPlant', function () {
            var productionPlant = <?php echo $PlantID ?>;
            if (productionPlant == 0) {
                $("#txtArrivalDate").trigger('change');
            }

        });

    });



    function fnValidateBreakup() {
        // Validate input fields on page load
        validateInputs('#tblDataTable .txtSampleQty', regex1);
        validateInputs('#tblDataTable .txtNoOfPcs', regex2);
        validateInputs('#tblDataTable .txtRecvCount', regex3);
        validateInputs('#tblDataTable .txtRecvQty', regex4);
    }

    $(document).on("change", "#drpVehicleNumber", function () {
        getTrackCodes();
    });

    function getTrackCodes() {
        if ($('#drpVehicleNumber').val().length > 0) {

            var IsVehicleTrack = <?php echo $IsVehicleTrack ?>;
            var centerid = $('#drpVehicleNumber').find(':selected').attr('data-centerid');
            var plantid = $('#drpVehicleNumber').find(':selected').attr('data-plantid');
            var receivingID = $('#drpVehicleNumber').find(':selected').attr('data-receivingID');
            var vehicleID = $('#drpVehicleNumber').val();

            var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram
            var IsHONPacking = <?php echo $IsHONPacking ?>; // sriram
            var removePlantCode = <?php echo $removePlantCode ?>; // sriram


            // $("#drpArrivalPlant").val(plantid).trigger("change");
            $.ajax({
                url: 'Ajax/ajax-receival-page-trackid.php',
                type: 'POST',
                data: {
                    methodType: "getFormData",
                    arrivalDate: $('#txtArrivalDate').val(),
                    vehicleID: $('#drpVehicleNumber').val(),
                    receivalPlant: $("#drpArrivalPlant").val(),
                    receivingID: receivingID,
                    IsVehicleTrack: IsVehicleTrack,
                    IsPlantFilter: IsPlantFilter,
                    IsHONPacking: IsHONPacking,
                    removePlantCode: removePlantCode
                },
                success: function (data) {
                    console.log(data);
                    if (data.FormData) {
                        // $("#drpArrivalPlant").val(data.FormData.ReceivingPlantID).trigger("change");
                        $("#txtTrackCode").val(data.FormData.TrackCode);
                        $("#txtSealNumber").val(data.FormData.SealNo);
                        $("#drpSupervisor").val(data.FormData.SupervisorID).trigger("change");
                        $("#txtRemarks").val(data.FormData.Remarks);
                        $("#hdnTackId").val(data.FormData.ReceivingID);
                        if (removePlantCode == 1) {
                            $("#txtSealNumber").val("NA");
                        }
                    }
                    if (data.htmlRes) {
                        $("#divDataList").html(data.htmlRes);
                        fnShowDataTable();

                        fnValidateBreakup(); // Breakup Validation
                    }

                    return false;
                }
            });

        } else {
            var removePlantCode = <?php echo $removePlantCode ?>; // sriram
            $("#divDataList").html("");
            $("#drpSupervisor").val("").trigger("change");
            $("#txtTrackCode, #txtSealNumber, #txtRemarks").val("");
            if (removePlantCode == 1) {
                $("#txtSealNumber").val("NA");
            }
        }
    }




    $(document).on("click", ".btnDelete", function () {

        var brkUpID = $(this).attr("data-breakup-id");
        console.log(brkUpID);
        var removePlantCode = <?php echo $removePlantCode ?>; // sriram
        var IsHONPacking = <?php echo $IsHONPacking ?>; // sriram

        if (confirm("Are you sure you want to delete this item ?") == true) {
            $.ajax({
                url: 'Ajax/ajax-receival-page-trackid.php',
                type: 'POST',
                data: {
                    methodType: "deleteRow",
                    breakUpID: brkUpID,
                    arrivalDate: $("#txtArrivalDate").val(),
                    vehicleID: $("#drpVehicleNumber").val(),
                    arrivalPlantId: $("#drpArrivalPlant").val(),
                    removePlantCode: removePlantCode,
                    IsHONPacking: IsHONPacking
                },
                success: function (data) {
                    console.log(data);
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);

                        if (data.FormData) {
                            // $("#drpArrivalPlant").val(data.FormData.ReceivingPlantID).trigger("change");
                            $("#txtTrackCode").val(data.FormData.TrackCode);
                            $("#txtSealNumber").val(data.FormData.SealNo);
                            $("#drpSupervisor").val(data.FormData.SupervisorID).trigger("change");
                            $("#txtRemarks").val(data.FormData.Remarks);
                        }
                        if (data.htmlRes) {
                            $("#divDataList").html(data.htmlRes);
                            fnShowDataTable();

                            fnValidateBreakup(); // Breakup Validation
                        }
                    }
                    return false;
                }
            });
        }

    });

    $(document).on("click", ".btnSaveRow", function () {

        var arrID = $(this).attr("data-arrival-id");
        var arrivalBrkupID = $(this).attr("data-arr-breakup-id");
        var removePlantCode = <?php echo $removePlantCode ?>; // sriram
        var IsHONPacking = <?php echo $IsHONPacking ?>; // sriram
        $.ajax({
            url: 'Ajax/ajax-receival-page-trackid.php',
            type: 'POST',
            data: {
                methodType: "TableSave",
                arrivalID: arrID,
                arrivalBreakupID: arrivalBrkupID,
                sampleQuantity: $(this).closest("tr").find(".txtSampleQty").val(),
                noofPieces: $(this).closest("tr").find(".txtNoOfPcs").val(),
                receivingCount: $(this).closest("tr").find(".txtRecvCount").val(),
                receivingQty: $(this).closest("tr").find(".txtRecvQty").val(),
                trackId: $("#hdnTackId").val(),
                arrivalDate: $("#txtArrivalDate").val(),
                vehicleID: $("#drpVehicleNumber").val(),
                arrivalPlantId: $("#drpArrivalPlant").val(),
                HONPacking: $(this).closest("tr").find(".drpHONPacking").val() ?? "0",
                removePlantCode: removePlantCode,
                IsHONPacking: IsHONPacking
            },
            success: function (data) {
                // console.log(data);
                if (data.Error) {
                    alert(data.Error);
                } else {
                    alert(data.Msg);

                    if (data.FormData) {
                        // $("#drpArrivalPlant").val(data.FormData.ReceivingPlantID).trigger("change");
                        $("#txtTrackCode").val(data.FormData.TrackCode);
                        $("#txtSealNumber").val(data.FormData.SealNo);
                        $("#drpSupervisor").val(data.FormData.SupervisorID).trigger("change");
                        $("#txtRemarks").val(data.FormData.Remarks);
                    }
                    if (data.htmlRes) {
                        $("#divDataList").html(data.htmlRes);
                        fnShowDataTable();

                        fnValidateBreakup(); // Breakup Validation
                    }
                    if (removePlantCode == 1) {
                        if (data?.LotId != '' && data?.ArrivalID != '') {
                            var url = 'crates-creation?LotID=' + data.LotID + '&ArrivalID=' + data.ArrivalID + '&saveType="save"';
                            window.open(url, '_blank');
                        }
                    }
                }
                return false;
            }
        });

    });

    $(document).on("click", ".btnUpdateRow", function () {

        var arrID = $(this).attr("data-arrival-id");
        var arrivalBrkupID = $(this).attr("data-arr-breakup-id");
        var receivalBrkupid = $(this).attr("data-receival-breakupid");
        var receivingid = $(this).attr("data-receivingid");
        var removePlantCode = <?php echo $removePlantCode ?>; // sriram
        var IsHONPacking = <?php echo $IsHONPacking ?>; // sriram
        $.ajax({
            url: 'Ajax/ajax-receival-page-trackid.php',
            type: 'POST',
            data: {
                methodType: "TableUpdate",
                trackId: $("#hdnTackId").val(),
                arrivalID: arrID,
                arrivalBreakupID: arrivalBrkupID,
                receivalBreakupID: receivalBrkupid,
                sampleQuantity: $(this).closest("tr").find(".txtSampleQty").val(),
                noofPieces: $(this).closest("tr").find(".txtNoOfPcs").val(),
                receivingCount: $(this).closest("tr").find(".txtRecvCount").val(),
                receivingQty: $(this).closest("tr").find(".txtRecvQty").val(),
                arrivalDate: $("#txtArrivalDate").val(),
                vehicleID: $("#drpVehicleNumber").val(),
                arrivalPlantId: $("#drpArrivalPlant").val(),
                HONPacking: $(this).closest("tr").find(".drpHONPacking").val() ?? "0",
                removePlantCode: removePlantCode,
                IsHONPacking: IsHONPacking
            },
            success: function (data) {
                console.log(data);
                if (data.Error) {
                    alert(data.Error);
                } else {
                    alert(data.Msg);
                    if (data.FormData) {
                        // $("#drpArrivalPlant").val(data.FormData.ReceivingPlantID).trigger("change");
                        $("#txtTrackCode").val(data.FormData.TrackCode);
                        $("#txtSealNumber").val(data.FormData.SealNo);
                        $("#drpSupervisor").val(data.FormData.SupervisorID).trigger("change");
                        $("#txtRemarks").val(data.FormData.Remarks);
                    }
                    if (data.htmlRes) {
                        $("#divDataList").html(data.htmlRes);
                        fnShowDataTable();

                        fnValidateBreakup(); // Breakup Validation
                    }
                }
                return false;
            }
        });

    });



    $(document).ready(function () {
        var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram
        var clientID = '<?php echo $ClientID ?>';

        var validationRules = {
            txtArrivalDate: {
                required: true
            },
            drpVehicleNumber: {
                required: true
            },
            txtTrackCode: {
                required: true
            },
            txtSealNumber: {
                required: true
            },
            drpSupervisor: {
                required: true
            }
        };

        if (clientID == 'SMEV3') {
            validationRules = {
                txtArrivalDate: {
                    required: true
                },
                drpVehicleNumber: {
                    required: true
                },
                txtTrackCode: {
                    required: true
                },
            };
        }

        if (IsPlantFilter != 0) {
            validationRules.drpArrivalPlant = {
                required: true
            };
        }

        $("#frmReceival").validate({
            onkeyup: false,
            onfocusout: false,
            invalidHandler: function (form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    firstInvalidElement.focus();
                }
            },
            ignore: "readonly",
            rules: validationRules,

            errorPlacement: function () {
                return false; // suppresses error message text
            },
            submitHandler: function () {

                debugger;
                var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram
                var removePlantCode = <?php echo $removePlantCode ?>; // sriram
                $.ajax({
                    url: 'Ajax/ajax-receival-page-trackid.php',
                    type: 'POST',
                    data: {
                        methodType: "FormPost",
                        arrivalDate: $("#txtArrivalDate").val(),
                        vehicleId: $("#drpVehicleNumber").val(),
                        vehicleNumber: $("#drpVehicleNumber option:selected").text(),
                        arrivalPlantId: $("#drpArrivalPlant").val(),
                        // trackCode: $("#txtTrackCode").val(),
                        // trackNumber: $("#txtTrackCode").attr("data-trackid"),
                        sealNumber: $("#txtSealNumber").val(),
                        supervisorId: $("#drpSupervisor").val(),
                        remarks: $("#txtRemarks").val(),
                        IsPlantFilter: IsPlantFilter, //sriram
                        removePlantCode: removePlantCode
                    },
                    success: function (data) {
                        console.log(data);
                        if (data.Error) {
                            alert(data.Error);
                        } else {
                            alert(data.Msg);
                            if (data.Data.RmReceivingDetails) {
                                $(".btnUpdateRow, .btnSaveRow, .btnDelete").show();
                                $("#hdnTackId").val(data.Data.RmReceivingDetails.ReceivingID);

                                fnValidateBreakup(); // Breakup Validation
                            }
                        }

                        return false;
                    }
                });
            }
        });


    });


    // // Attach keyup event listener to the input field
    // $(document).on('keyup','#tblDataTable .txtSampleQty, #tblDataTable .txtRecvCount', function() {
    //     // Get the input value
    //     var inputValue = $(this).val();

    //     // Test the input value against the regular expression pattern
    //     if (regex1.test(inputValue)) {
    //         // Input is valid
    //         $(this).removeClass('error').addClass('valid');
    //         $(this).closest("tr").find(".btnUpdateRow").prop("disabled", false);
    //     } else {
    //         // Input is not valid
    //         $(this).removeClass('valid').addClass('error');
    //         $(this).closest("tr").find(".btnUpdateRow").prop("disabled", true);
    //     }
    // });

    // // Attach keyup event listener to the input field
    // $(document).on('keyup','#tblDataTable .txtNoOfPcs', function() {

    //     // Get the input value
    //     var inputValue = $(this).val();

    //     // Test the input value against the regular expression pattern
    //     if (regex2.test(inputValue)) {
    //         // Input is valid
    //         $(this).removeClass('error').addClass('valid');
    //         $(this).closest("tr").find(".btnUpdateRow").prop("disabled", false);
    //     } else {
    //         // Input is not valid
    //         $(this).removeClass('valid').addClass('error');
    //         $(this).closest("tr").find(".btnUpdateRow").prop("disabled", true);
    //     }
    // });

    // // Attach keyup event listener to the input field
    // $(document).on('keyup','#tblDataTable .txtRecvQty', function() {

    //     // Get the input value
    //     var inputValue = $(this).val();

    //     // Test the input value against the regular expression pattern
    //     if (regex3.test(inputValue)) {
    //         // Input is valid
    //         $(this).removeClass('error').addClass('valid');
    //         $(this).closest("tr").find(".btnUpdateRow").prop("disabled", false);
    //     } else {
    //         // Input is not valid
    //         $(this).removeClass('valid').addClass('error');
    //         $(this).closest("tr").find(".btnUpdateRow").prop("disabled", true);
    //     }
    // });



    // });
</script>

</body>

</html>
