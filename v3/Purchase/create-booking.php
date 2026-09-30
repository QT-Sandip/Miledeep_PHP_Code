<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$HarvestFormNewDesignForSME = !empty($ClientConfigData->HarvestFormNewDesignForSME)?$ClientConfigData->HarvestFormNewDesignForSME:'';//SMEV3

$BookingID = isset($_GET['BookingID']) && !empty($_GET['BookingID']) ? $_GET['BookingID'] : "";

$api = API_RM_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resSpecies = $resDropdownsAPI->Data->Species;
$resPurchaseCenters = $resDropdownsAPI->Data->PurchaseCenters; // Purchase Stations / Purchase centers 
$resPurchaseCategory = $resDropdownsAPI->Data->PurchaseCategory;
$resVehicles = $resDropdownsAPI->Data->Vehicles;
$resRmAgents = $resDropdownsAPI->Data->RmAgents;
$resDrivers = $resDropdownsAPI->Data->Drivers;
$resRmGraders = $resDropdownsAPI->Data->RmGraders;
$resRmSuppliers = $resDropdownsAPI->Data->RmSuppliers;

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

                <?php if (isset($_SESSION['Msg'])) { ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $Status ?>
                    </div>
                    <?php $_SESSION['Error'] = null;
                } ?>

                <?php if (isset($_SESSION['Error'])) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $Status ?>
                    </div>
                    <?php $_SESSION['Msg'] = null;
                } ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <form id="frmCreateBookings">
                                    <div class="row">

                                        <div class="form-group col-sm-3">
                                            <label for="drpBookingStationId" class="form-label">Booking Station<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpBookingStationId"
                                                id="drpBookingStationId">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPurchaseCenters as $Item) { ?>
                                                    <option value="<?php echo $Item->PurchaseStationID; ?>">
                                                        <?php echo $Item->StationName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpSpecies" class="form-label">Species <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpSpecies" id="drpSpecies">
                                                <option value=""> Select</option>
                                                <?php foreach ($resSpecies as $Item) { ?>
                                                    <option value="<?php echo $Item->ShortCode; ?>">
                                                        <?php echo $Item->SpeciesName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpPurchaseType" class="form-label">Purchase Type <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpPurchaseType" id="drpPurchaseType">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPurchaseCategory as $Item) { ?>
                                                    <option value="<?php echo $Item->RmCategoryID; ?>">
                                                        <?php echo $Item->RmCategoryName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBookingNumber" class="form-label">Booking Number <span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtBookingNumber"
                                                name="txtBookingNumber" placeholder="Enter Booking Number" />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpVehicleNumber" class="form-label">Vehicle Number <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpVehicleNumber" id="drpVehicleNumber" multiple="multiple">
                                                <option value=""> Select</option>
                                                <?php foreach ($resVehicles as $Item) { ?>
                                                    <option value="<?php echo $Item->VehicalID; ?>">
                                                        <?php echo $Item->VehicalNumber; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpDriverName" class="form-label">Driver Name <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpDriverName" id="drpDriverName" multiple="multiple">
                                                <option value=""> Select</option>
                                                <?php foreach ($resDrivers as $Item) { ?>
                                                    <option value="<?php echo $Item->UserID; ?>">
                                                        <?php echo $Item->UserName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpGraderName" class="form-label">Grader Name<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpGraderName"
                                                id="drpGraderName" multiple="multiple">

                                                <option value="">Select</option>
                                                <?php foreach ($resRmGraders as $Item) { ?>
                                                    <option value="<?= htmlspecialchars($Item->UserID); ?>">
                                                        <?= htmlspecialchars($Item->UserName); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBookingDate" class="form-label">Booking Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtBookingDate"
                                                name="txtBookingDate" placeholder=""
                                                value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpAgentName" class="form-label">Agent Name </label>
                                            <select class="drpSelect" name="drpAgentName" id="drpAgentName">
                                                <option value=""> Select</option>
                                                <?php foreach ($resRmAgents as $Item) { ?>
                                                    <option value="<?php echo $Item->UserID; ?>">
                                                        <?php echo $Item->UserName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtFarmLocation" class="form-label">Farm Location <span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtFarmLocation"
                                                name="txtFarmLocation" placeholder="Enter Farm Location" />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpSuppliers" class="form-label">Suppliers <span
                                                    style="color:red">*</span> <button type="button"
                                                    class="btn btn-success py-0" data-bs-toggle="modal"
                                                    data-bs-target="#modalCreateSupplier">New </button></label>
                                            <select class="drpSelect" name="drpSuppliers" id="drpSuppliers">
                                                <option value=""> Select</option>
                                                <?php foreach ($resRmSuppliers as $Item) { ?>
                                                    <option value="<?php echo $Item->SupplierID; ?>">
                                                        <?php echo $Item->FinancierName . "-" . $Item->PanNO; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <!-- <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtExtra" class="form-label">Addition/Deduction Per Kg <span style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtExtra" name="txtExtra" placeholder="Enter Addition Deduction" />
                                        </div> -->

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50"
                                                id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                        </div>
                                    </div>

                                    <hr>


                                    <div class="row">
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBookingCount" class="form-label">Booking Count </label>
                                            <input type="number" class="form-control" id="txtBookingCount"
                                                name="txtBookingCount" placeholder="Enter Booking Count">
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBookingWeight" class="form-label">Booking Weight(Kgs)
                                            </label>
                                            <input type="text" class="form-control" id="txtBookingWeight"
                                                name="txtBookingWeight" placeholder=" Enter Booking Weight(Kgs)">
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBookingRate" class="form-label">Booking Rate</label>
                                            <input type="text" class="form-control" id="txtBookingRate"
                                                name="txtBookingRate" placeholder="Enter Booking Rate">
                                        </div>

                                    </div>
                                    <?php 
                                        if($HarvestFormNewDesignForSME == 1){?>
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-6 col-lg-3 mb-3">
                                                    <label for="SampledBy" class="form-label">Sampled by </label>
                                                    <input type="text" class="form-control" id="SampledBy"
                                                        name="SampledBy" placeholder="Enter Sampled by">
                                                </div>
                                                <div class="col-md-6 col-lg-3 mb-3">
                                                    <label for="TagId" class="form-label">Tag id
                                                    </label>
                                                    <input type="text" class="form-control" id="TagId"
                                                        name="TagId" placeholder=" Enter Tag id">
                                                </div>
                                                <div class="col-md-6 col-lg-3 mb-3">
                                                    <label for="SampleStatus" class="form-label">Sample Status <!-- <span
                                                    style="color:red">*</span> --></label>
                                                    <select class="drpSelect" name="SampleStatus"
                                                        id="SampleStatus">
                                                        <option value=""> Select</option>
                                                        <option value="1"> Accepted</option>
                                                        <option value="0"> Rejected</option>
                                                        <option value="2"> Not Applicable</option>                                                        
                                                    </select>
                                                </div>                                     
                                            </div>
                                    <?php } ?>
                                    <div class="text-center">
                                        <input type="hidden" id="hdnSaveType" name="hdnSaveType"
                                            value="<?php echo $BookingID ?>" />
                                        <a href="rm-booking-list" class="btn btn-danger">Close</a>
                                        <a href="create-booking" class="btn btn-success waves-effect waves-light">
                                            <i class="fa fa-plus"></i> New Bookings
                                        </a>
                                        <button class="btn btn-primary" name="submit" id="submit"
                                            type="submit">Save</button>
                                    </div>

                                </form>
                            </div>
                        </div>

                        <div id="divDataList">

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
    var bookingID = "<?php echo isset($_GET["BookingID"]) && !empty($_GET["BookingID"]) ? $_GET["BookingID"] : "" ?>";

    var table;

    function fnShowDataTable() {
        table = $('#tblDataTable').DataTable({
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
            buttons: [],
        });
    }

    function bindFormData(resJson) {

        $("#drpSpecies").val(resJson.Species).trigger('change');
        $("#drpBookingStationId").val(resJson.BookingStationID).trigger('change');
        $("#drpPurchaseType").val(resJson.PurchaseCategoryID).trigger('change');
        $("#txtBookingNumber").val(resJson.BookingNumber);
        $("#drpVehicleNumber").val(resJson.VehicalID.split(',')).trigger('change');
        $("#drpDriverName").val(resJson.DriverID.split(',')).trigger('change');
        $("#drpGraderName").val(resJson.GraderID.split(',')).trigger('change');
        $("#txtBookingDate").val(resJson.BookingDate);
        $("#drpAgentName").val(resJson.AgentID).trigger('change');
        $("#txtFarmLocation").val(resJson.FarmAddress);
        $("#drpSuppliers").val(resJson.RmSupplierID).trigger('change');
        // $("#txtExtra").val(resJson.Extra);
        $("#txtRemarks").val(resJson.Remarks);

        if($("#SampledBy").length !=0){
             $("#SampledBy").val(resJson.SampledBy);
        }
        if($("#TagId").length !=0){
             $("#TagId").val(resJson.TagId);
        }
        if($("#SampleStatus").length !=0){
            $("#SampleStatus").val(resJson.SampleStatus).trigger('change');
        }

        // $("#drpVariety").val(resJson.VarietyID).trigger('change');

    }



    $(document).ready(function () {
        var frmData = {
            methodType: "get",
            BookingID: bookingID,
            HarvestFormNewDesignForSME:"<?php echo $HarvestFormNewDesignForSME;?>"
        }
        $.ajax({
            url: 'Ajax/AjaxBooking.php',
            type: 'POST',
            data: frmData,
            success: function (data) {
                var data = JSON.parse(data);
                var htmlData = data.html;
                var resJson = data.json;

                $("#divDataList").html(htmlData);
                fnShowDataTable();

                if (bookingID) {
                    bindFormData(resJson);
                }
                return false;
            }
        });
        $('#drpGraderName').select2();
        $('#drpVehicleNumber').select2();
        $('#drpDriverName').select2();
    });

    $('#drpBookingStationId').on('select2:select', function (e) {

        $("#drpSpecies, #drpPurchaseType, #drpVehicleNumber, #drpDriverName, #drpAgentName, #drpSuppliers").val("").trigger('change');
        $("#txtBookingNumber, #txtRemarks").val("");
        // $("#txtBookingNumber, #txtBookingDate, #txtExtra, #txtRemarks").val("");

        $("#txtFarmLocation").val($('#drpBookingStationId').select2('data')[0].text.trim());

        var currentUrl = window.location.href;
        var url = currentUrl.split('?')[0];

        window.history.pushState({}, '', url);

        table.clear().draw();
        $("#divDataList").remove();

        bookingID = "";
    });

    $(document).on("click", ".deleteBrkUp", function () {
        var brkUpID = $(this).attr("data-BookingBrkUpID");
        console.log(brkUpID);

        var frmData = {
            methodType: "delete",
            BookingID: bookingID,
            BreakUpID: brkUpID,
            HarvestFormNewDesignForSME:"<?php echo $HarvestFormNewDesignForSME;?>"
        }
        if (confirm("Are you sure you want to delete this item ?") == true) {

            $.ajax({
                url: 'Ajax/AjaxBooking.php',
                type: 'POST',
                data: frmData,
                success: function (data) {
                    var data = JSON.parse(data);
                    var htmlData = data.html;
                    var resJson = data.json;

                    $("#divDataList").html(htmlData);
                    fnShowDataTable();

                    if (bookingID) {
                        bindFormData(resJson);
                    }
                    return false;
                }
            });
        }
    });


    $("#frmCreateBookings").validate({
        onkeyup: false,
        onfocusout: false,
        invalidHandler: function (form, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var firstInvalidElement = $(validator.errorList[0].element);
                //$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
                firstInvalidElement.focus();
            }
        },
        ignore: "",
        rules: {
            drpSpecies: {
                required: true,
                selectcheck: true
            },
            drpBookingStationId: {
                required: true,
                selectcheck: true
            },
            drpPurchaseType: {
                required: true,
                selectcheck: true
            },
            txtBookingNumber: {
                required: true,
                noSpace: true
            },
            drpVehicleNumber: {
                required: true,
                selectcheck: true
            },
            drpDriverName: {
                required: true,
                selectcheck: true
            },
            drpGraderName: {
                required: true,
                selectcheck: true
            },
            txtBookingDate: {
                required: true,
                noSpace: true
            },
            // drpAgentName: { required: true, selectcheck: true },
            txtFarmLocation: {
                required: true,
                noSpace: true
            },
            drpSuppliers: {
                required: true,
                selectcheck: true
            },
            // txtExtra: {
            //     required: true,
            //     number: true
            // },
            //txtRemarks: { required: true, noSpace: true },
            // drpVariety: { required: true, selectcheck: true },

            txtBookingCount: {
                required: {
                    depends: function (element) {
                        return $("#txtBookingWeight").val().length > 0 || $("#txtBookingRate").val().length > 0;
                    }
                }
            },
            txtBookingWeight: {
                required: {
                    depends: function (element) {
                        return $("#txtBookingCount").val().length > 0 || $("#txtBookingRate").val().length > 0;
                    }
                }
            },
            txtBookingRate: {
                required: {
                    depends: function (element) {
                        return $("#txtBookingCount").val().length > 0 || $("#txtBookingWeight").val().length > 0;
                    }
                },
                number: true
            }
        },
        errorPlacement: function () {
            return false; // suppresses error message text
        },
        submitHandler: function () {

            var isInsert = '<?php echo isset($BookingID) ?>';
            var drpGraderNames = $("#drpGraderName").val().join(',');
            var drpVehicleNumber = $("#drpVehicleNumber").val().join(',');
            var drpDriverName = $("#drpDriverName").val().join(',');
            // if(isInsert.length > 0) {
            $.ajax({
                url: 'Ajax/AjaxBooking.php',
                type: 'POST',
                data: {
                    methodType: "post",
                    BookingID: bookingID,
                    // hdnSaveType: $("#hdnSaveType").val(),                                    
                    drpSpecies: $("#drpSpecies").val(),
                    drpBookingStationId: $("#drpBookingStationId").val(),
                    drpPurchaseType: $("#drpPurchaseType").val(),
                    txtBookingNumber: $("#txtBookingNumber").val(),
                    drpVehicleNumber: drpVehicleNumber,
                    drpDriverName: drpDriverName,
                    drpGraderName: drpGraderNames,
                    txtBookingDate: $("#txtBookingDate").val(),
                    drpAgentName: $("#drpAgentName").val(),
                    txtFarmLocation: $("#txtFarmLocation").val(),
                    drpSuppliers: $("#drpSuppliers").val(),
                    // txtExtra: $("#txtExtra").val(),
                    txtRemarks: $("#txtRemarks").val(),
                    // drpVariety: $("#drpVariety").val(),
                    txtBookingCount: $("#txtBookingCount").val(),
                    txtBookingWeight: $("#txtBookingWeight").val(),
                    txtBookingRate: $("#txtBookingRate").val(),

                    SampledBy: ($("#SampledBy").length!=0)?$("#SampledBy").val():'',
                    TagId: ($("#TagId").length!=0)?$("#TagId").val():'',
                    SampleStatus: ($("#SampleStatus").length!=0)?$("#SampleStatus").val():'',
                    HarvestFormNewDesignForSME:"<?php echo $HarvestFormNewDesignForSME;?>"

                },
                success: function (data) {
                    var data = JSON.parse(data);
                    var htmlData = data.html;
                    var resJson = data.json;
                    var error = data.Error;
                    var msg = data.Msg;

                    console.log(data);

                    if (error === null) {
                        alert(msg);
                    } else {
                        alert(error);
                        return false;
                    }

                    if (bookingID == "") {
                        // console.log('Hello');
                        // console.log(resJson.RmBookingID);
                        var newUrl = window.location.href + "?BookingID=" + resJson.RmBookingID;
                        window.location.href = newUrl;
                    }

                    $("#divDataList").html(htmlData);
                    fnShowDataTable();

                    if (bookingID) {
                        bindFormData(resJson);
                    }
                    $("#txtBookingCount").val("");
                    $("#txtBookingWeight").val("");
                    $("#txtBookingRate").val("");
                    return false;
                }
            });
            // } else {
            //     return true;
            // }                        
        }
    });
</script>

</body>

</html>
