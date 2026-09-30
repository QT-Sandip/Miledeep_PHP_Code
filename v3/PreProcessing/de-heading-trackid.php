<?php

session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = $_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData['IsPlantFilter'];  //sriram
$IsHONPacking = $ClientConfigData['IsHONPacking'];  //sriram
$DeHeadingBreakup = $ClientConfigData['DeHeadingBreakup'];


$ArrivalID = trim($_GET['ArrivalID']);

$ArrivalID = isset($_GET['ArrivalID']) && !empty($_GET['ArrivalID']) ? $_GET['ArrivalID'] : "";

// var_dump($ArrivalID);
// exit;
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

// SESSION VALUES 
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$ShowDeheadingStationID = $ArrayClientConfigData['ShowDeheadingStationID'];
$DeheadingPlantID = $_SESSION['UnitID'];
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
                                <form id="frmDeHeading">
                                    <div class="row">

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpProductionPlant" class="form-label">Production Plant<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpProductionPlant"
                                                id="drpProductionPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionPlant as $Item) { ?>
                                                    <option value="<?php echo $Item->ProcessingStationID; ?>">
                                                        <?php echo $Item->FullName . '-' . $Item->StationName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Arrival Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtArrivalDate"
                                                name="txtArrivalDate" placeholder=""
                                                value="<?php echo date("Y-m-d"); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />

                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpTrackCode" class="form-label">Track ID<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpTrackCode"
                                                id="drpTrackCode">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <?php if ($ShowDeheadingStationID == 1) { ?>
                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="drpCenter" class="form-label">De Heading Station<span
                                                        style="color:red">*</span></label>

                                                <select class="drpSelect form-control" name="drpCenter" id="drpCenter"
                                                <?php echo !empty($DeHeadingBreakup) ? 'disabled' : '';?>>
                                                    <option value=""> Select</option>
                                                    <?php foreach ($resCenter as $Item) { ?>
                                                        <option value="<?php echo $Item->StationID; ?>">
                                                            <?php echo $Item->UnitName; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        <?php } ?>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTransactionDate" class="form-label">De Heading Date<span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtTransactionDate"
                                                name="txtTransactionDate" value="<?php echo date('Y-m-d'); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpShift" class="form-label">Shift <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpShift" id="drpShift">
                                                <option value=""> Select</option>
                                                <?php foreach ($resShift as $Item) { ?>
                                                    <option value="<?php echo $Item->OptID; ?>">
                                                        <?php echo $Item->Name; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpSuperVisor" class="form-label">SuperVisor <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpSuperVisor"
                                                id="drpSuperVisor" 
                                                <?php echo !empty($DeHeadingBreakup) ? 'disabled' : '';?>>
                                                <option value=""> Select</option>
                                                <?php foreach ($resSupervisor as $Item) { ?>
                                                    <option value="<?php echo $Item->ID; ?>">
                                                        <?php echo $Item->UserName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50"
                                                id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <span id="gradingStationMessage" style="color: red"><b></b></span>
                                        <input type="hidden" id="hdnGradingPlant" name="hdnGradingPlant" />

                                    </div><br>

                                    <div class="text-center">
                                        <input type="hidden" id="hdnSaveType" name="hdnSaveType"
                                            value="<?php echo $ArrivalID ?>" />
                                        <input type="hidden" id="hdnNewArrival" name="hdnNewArrival" />
                                        <input type="hidden" id="hdnNewArrival" name="hdnNewArrival" />
                                        <!-- <a href="#" class="btn btn-danger">Close</a> -->
                                        <button class="btn btn-primary" name="submit" id="submit"
                                            type="submit">Save</button>
                                        <!-- <button class="btn btn-success" name="btnTransfer" id="btnTransfer" type="button">Transfer</button> -->

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


<div class="modal fade" id="modalTransfetPlant" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title modheading"> Transfer to Another Plant <span class="spanInvNo"></span></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="">
                    <div class="container-fluid">
                        <div class="row">
                            <div style="padding: 15px;">
                                <div class="">
                                    <form role="form" method="post" id="frmTransferPlant">
                                        <div class="row">

                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="drpProductionPlant2" class="form-label">Production
                                                    Plant<span style="color:red">*</span></label>
                                                <select class="drpSelect form-control" name="drpProductionPlant2"
                                                    id="drpProductionPlant2">
                                                    <option value=""> Select</option>
                                                    <?php foreach ($resProductionPlant as $Item) { ?>
                                                        <option value="<?php echo $Item->ProcessingStationID; ?>">
                                                            <?php echo $Item->FullName . '-' . $Item->StationName; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <span id="errorMessage" style="color:red"> </span>

                                            <div class="text-center w-100">
                                                <button type="button" class="btn btn-danger close"
                                                    data-dismiss="modal">Close</button>
                                                <button class="btn btn-success btnSave" name="submit"
                                                    id="submit">Update</button>

                                            </div>

                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include_once ROOT . 'includes/common-js.php' ?>

<?php include_once WORKING_MODULE . 'modalSupplier.php' ?>


<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>

<script>
    var regex1 = /^\d{0,5}(\.\d{0,3})?$/; // txthonQty  999.999
    var regex2 = /^\d{0,4}(\.\d{0,4})?$/; // txthlCount  999.999
    var regex3 = /^\d{0,5}(\.\d{0,3})?$/; // txthlQty  99999.999 

    // var regex2 = /^\d{0,4}?$/; // txtNoOfPcs  9999
    // var regex3 = /^\d{1,3}(\.\d{0,3})?$/; //  txtRecvCount  999.999


    var arrivalID = "<?php echo isset($_GET["ArrivalID"]) && !empty($_GET["ArrivalID"]) ? $_GET["ArrivalID"] : "" ?>";
    var table;

    function fnShowDataTable() {

        var arrivalDate = $("#txtArrivalDate").val();
        var parts = arrivalDate.split("-");
        var formattedArrivalDate = parts[2] + "-" + parts[1] + "-" + parts[0];

        var trasDate = $("#txtTransactionDate").val();
        var partsTrasDate = arrivalDate.split("-");
        var formattedTrasDate = parts[2] + "-" + parts[1] + "-" + parts[0];

        var excelHeader = "De-Heading_" + $.trim($("#drpTrackCode option:selected").text()) + "_Arrival Date: " + formattedArrivalDate + "_De Heading Date: " + formattedTrasDate;

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
                title: excelHeader, //'De-Heading-<?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    //columns: ':visible'
                    columns: [1, 2, 3, 4, 5, 6, 7, 8],
                    format: {
                        body: function (inner, rowidx, colidx, node) {
                            if ($(node).children("input").length > 0) {
                                return $(node).children("input").first().val();
                            } else {
                                return inner;
                            }
                        }
                    }
                }
            }],
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

    function fnValidateBreakup() {
        // Validate input fields on page load
        validateInputs('#tblDataTable .txthonQty', regex1);
        validateInputs('#tblDataTable .txthlCount', regex2);
        validateInputs('#tblDataTable .txthlQty', regex3);
    }


    $(document).ready(function () {

        getArrivals();

        // Validate input fields on page load
        fnValidateBreakup(); // Breakup Validation

        // Attach keyup event listeners to input fields
        $(document).on('keyup', '#tblDataTable .txthonQty', function () {
            validateInputs(this, regex1);
        });

        $(document).on('keyup', '#tblDataTable .txthlCount', function () {
            validateInputs(this, regex2);
        });

        $(document).on('keyup', '#tblDataTable .txthlQty', function () {
            validateInputs(this, regex3);
        });

        $(document).on('change', '#drpProductionPlant', function () {
            var productionPlant = <?php echo $DeheadingPlantID ?>;
            if (productionPlant == 0) {
                $("#txtArrivalDate").trigger('change');
            }

        });

    });

    $(document).on("change", "#txtArrivalDate", function () {
        getArrivals();

        // console.log("ko");

        $("#drpCenter").val("").trigger("change");
        $("#drpShift").val("").trigger("change");
        $("#drpSuperVisor").val("").trigger("change");
        $("#txtRemarks").val("");
        $("#divDataList").html("");
    });

    function getArrivals() {

        var deheadingPlant = <?php echo $DeheadingPlantID ?>;

        var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram
        if (deheadingPlant != 0 || IsPlantFilter == 0) {
            $("#drpProductionPlant").prop('disabled', true);
            if(IsPlantFilter == 0){
                $("#drpProductionPlant").val("");
            }else{
            $("#drpProductionPlant").val(deheadingPlant).trigger('change');
            }
        } else {
            $("#drpProductionPlant").prop('disabled', false);
            // $("#drpProductionPlant").val("").trigger('change');
        }

        $.ajax({
            url: 'Ajax/ajax-de-heading-trackid.php',
            type: 'POST',
            data: {
                arrivalDate: $('#txtArrivalDate').val(),
                plantID: $("#drpProductionPlant").val(),
                type: "Deheading",
            },
            success: function (data) {

                console.log(data);
                var list = "<option>Select</option>";
                $.each(data.Data.RmDeHeadingTrackIds, function (index, value) {
                    list += "<option value='" + value.TrackID + "' data-plantid='" + value.PlantID + "'>" + value.TrackCode + "</option>";
                });

                $('#drpTrackCode').select2('destroy');
                $('#drpTrackCode').html(list);
                $('#drpTrackCode').select2();

                $("#drpCenter", "#drpShift", "#drpSuperVisor").val("").trigger("change");
                $("#txtRemarks").val("");
                $("#divDataList").html("");
                return false;
            }
        });
    }

    // $(document).ready(function() {

    //     getArrivals();

    //     $.ajax({
    //         url: 'Ajax/ajax-de-heading-trackid.php',
    //         type: 'POST',
    //         data: {
    //             methodType: "get",
    //             ArrivalID: arrivalID,
    //             plantID: $("#drpProductionPlant").val(),
    //         },
    //         success: function(data) {

    //             $("#divDataList").html(data);
    //             fnShowDataTable();

    //             return false;
    //         }
    //     });
    // });


    $(document).on("change", "#drpTrackCode", function () {

        if ($(this).val().length > 0) {
            getFullData();
        }
    });

    function getFullData() {
        
        var IsHONPacking = <?php echo $IsHONPacking ?>; // sriram
        $.ajax({
            url: 'Ajax/ajax-de-heading-trackid.php',
            type: 'POST',
            data: {
                methodType: "getBreakUp",
                arrivalDate: $('#txtArrivalDate').val(),
                trackID: $("#drpTrackCode").val(),
                plantID: $("#drpProductionPlant").val(),
                IsHONPacking: IsHONPacking,
                
            },
            success: function (data) {
                var currentDate = '<?php echo date('Y-m-d') ?>';

                console.log(data);
                if (data.FormData) {

                    $("#drpCenter").val(data.FormData.CenterID).trigger("change");
                    if (data.FormData.TransactionDate) {
                        $("#txtTransactionDate").val(data.FormData.TransactionDate);
                    } else {
                        $("#txtTransactionDate").val(currentDate);
                    }
                    $("#drpShift").val(data.FormData.ShiftID).trigger("change");
                    $("#drpSuperVisor").val(data.FormData.SupervisorID).trigger("change");
                    $("#txtRemarks").val(data.FormData.Remarks);

                    if (data.FormData.GradingPlantID != '<?php echo $DeheadingPlantID; ?>') {

                        if (data.FormData.GradingPlantName.length > 0) {
                            $("#gradingStationMessage").text('Grading Plant -> ' + data.FormData.GradingPlantName);
                        } else {
                            $("#gradingStationMessage").text('');
                        }
                        $("#hdnGradingPlant").val(data.FormData.GradingPlantID);
                    } else {
                        // $("#gradingStationMessage").text('No Grading Plant Selected');
                        $("#gradingStationMessage").text("");
                        $("#hdnGradingPlant").val("");
                    }
                    // console.log(data.FormData.CenterID + " - CenterID");
                    if (data.FormData.CenterID) {
                        $("#btnTransfer").prop("disabled", false);
                    } else {
                        $("#btnTransfer").prop("disabled", true);
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
    }
    // });

    $(document).ready(function () {
        var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram

        var validationRules ={
            txtArrivalDate: {
                required: true
            },
            drpTrackCode: {
                required: true
            },
            txtDeHeadingDate: {
                required: true
            },
            drpShift: {
                required: true
            },
            drpSuperVisor: {
                required: true
            },
            // txtRemarks: {
            //     required: true
            // },
        };

    if (IsPlantFilter != 0) {
        validationRules.drpProductionPlant = { required: true };
        validationRules.drpCenter = { required: true };
    }
    $("#frmDeHeading").validate({
        onkeyup: false,
        onfocusout: false,
        invalidHandler: function (form, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var firstInvalidElement = $(validator.errorList[0].element);
                firstInvalidElement.focus();
            }
        },
        ignore: "",
        rules: validationRules,
        errorPlacement: function () {
            return false; // suppresses error message text
        },
        submitHandler: function () {
            $.ajax({
                url: 'Ajax/ajax-de-heading-trackid.php',
                type: 'POST',
                data: {
                    methodType: "formPost",
                    trackCodeID: $("#drpTrackCode").val(),
                    productionPlant: $("#drpProductionPlant").val(),
                    center: $("#drpCenter").val(),
                    transactionDate: $("#txtTransactionDate").val(),
                    shift: $("#drpShift").val(),
                    superVisor: $("#drpSuperVisor").val(),
                    remarks: $("#txtRemarks").val(),
                    gradingPlantID: $("#hdnGradingPlant").val(),
                    IsPlantFilter: IsPlantFilter
                },
                success: function (data) {
                    console.log(data);
                    if (data.Error) {
                        alert(data.Error);
                        // console.log(data.Error);
                    } else {
                        alert(data.Msg);
                        if (data.DeheadingDetails) {
                            $(".btnUpdateRow, .btnSaveRow, .btnDelete").show();
                            // $("#hdnTackId").val(data.DeheadingDetails.TrackID);
                            $("#btnTransfer").prop('disabled', false);

                            fnValidateBreakup(); // Breakup Validation
                            getFullData();
                        }
                    }
                    return false;
                }
            });
        }
    });

    $("#btnTransfer").on("click", function () {

        var productionPlant = $("#hdnGradingPlant").val();
        $('#modalTransfetPlant').modal('show');
        $("#drpProductionPlant2").val(productionPlant).trigger("change");

    });


    $("#frmTransferPlant").validate({

        onkeyup: false,
        onfocusout: false,
        invalidHandler: function (form, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var firstInvalidElement = $(validator.errorList[0].element);
                firstInvalidElement.focus();
            }
        },
        ignore: "",
        rules: {
            drpProductionPlant2: {
                required: true,
            },
        },
        errorPlacement: function () {
            return false;
        },
        submitHandler: function () {

            $.ajax({
                url: 'Ajax/ajax-de-heading-trackid.php',
                type: 'POST',
                data: {
                    methodType: "Transfer",
                    trackCodeID: $("#drpTrackCode").val(),
                    productionPlant: $("#drpProductionPlant").val(),
                    gradingPlant: $("#drpProductionPlant2").val(),
                    center: $("#drpCenter").val(),
                    transactionDate: $("#txtTransactionDate").val(),
                    shift: $("#drpShift").val(),
                    superVisor: $("#drpSuperVisor").val(),
                    remarks: $("#txtRemarks").val()
                },
                success: function (data) {
                    getFullData();
                    // $('#modalTransfetPlant').modal('hide');

                    console.log(data);
                    if (data.Error) {
                        $("#errorMessage").text(data.Error);
                        // alert(data.Error);
                    } else {
                        alert(data.Msg);
                        $('#modalTransfetPlant').modal('hide');

                        if (data.DeheadingDetails) {
                            $(".btnUpdateRow, .btnSaveRow, .btnDelete").show();

                            fnValidateBreakup(); // Breakup Validation
                        }
                        $("#errorMessage").text("");
                    }
                    return false;
                }
            });

            // return false;
        }
    });

    });
    $(document).on("click", ".btnDelete", function () {

        var brkUpID = $(this).attr("data-breakup-id");
        console.log(brkUpID);

        if (confirm("Are you sure you want to delete this item ?") == true) {
            $.ajax({
                url: 'Ajax/ajax-de-heading-trackid.php',
                type: 'POST',
                data: {
                    methodType: "deleteRow",
                    breakUpID: brkUpID,
                    arrivalDate: $("#txtArrivalDate").val(),
                    trackID: $("#drpTrackCode").val()
                },
                success: function (data) {
                    console.log(data);
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);

                        if (data.FormData) {
                            // $("#drpProductionPlant").val(data.FormData.PlantID).trigger("change");
                            $("#drpCenter").val(data.FormData.CenterID).trigger("change");
                            $("#txtDeHeadingDate").val(data.FormData.TransactionDate);
                            $("#drpShift").val(data.FormData.ShiftID).trigger("change");
                            $("#drpSuperVisor").val(data.FormData.SupervisorID).trigger("change");
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
        var recvBrkupID = $(this).attr("data-recv-breakup-id");
        var deheadingBrkupid = $(this).attr("data-deheading-breakupid");

        $.ajax({
            url: 'Ajax/ajax-de-heading-trackid.php',
            type: 'POST',
            data: {
                methodType: "TableSave",
                arrivalID: arrID,
                recvBreakupID: recvBrkupID,
                deheadingBreakupID: deheadingBrkupid,
                honQty: $(this).closest("tr").find(".txthonQty").val(),
                hlCount: $(this).closest("tr").find(".txthlCount").val(),
                hlQty: $(this).closest("tr").find(".txthlQty").val(),
                transactionDate: $("#txtDeHeadingDate").val(),
                trackID: $("#drpTrackCode").val(),
                productionPlant: $("#drpProductionPlant").val(),
                center: $("#drpCenter").val(),
                transactionDate: $("#txtTransactionDate").val(),
                shift: $("#drpShift").val(),
                superVisor: $("#drpSuperVisor").val(),
                remarks: $("#txtRemarks").val(),
                totalDeheadingWeight: $(this).closest("tr").find(".txtTotalDeheadingWeight").val(),
                txtCheckingCount2: $(this).closest("tr").find(".txtCheckingCount2").val(),
                txtHONQuantity: $(this).closest("tr").find(".txtHONQuantity").val(),
            },
            success: function (data) {
                // console.log(data);
                if (data.Error) {
                    alert(data.Error);
                } else {
                    alert(data.Msg);
                    if (data.FormData) {
                        // $("#drpProductionPlant").val(data.FormData.PlantID).trigger("change");
                        $("#drpCenter").val(data.FormData.CenterID).trigger("change");
                        $("#txtTransactionDate").val(data.FormData.TransactionDate);
                        $("#drpShift").val(data.FormData.ShiftID).trigger("change");
                        $("#drpSuperVisor").val(data.FormData.SupervisorID).trigger("change");
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

    $(document).on("click", ".btnUpdateRow", function () {

        var arrID = $(this).attr("data-arrival-id");
        var recvBrkupID = $(this).attr("data-recv-breakup-id");
        var deheadingBrkupid = $(this).attr("data-deheading-breakupid");

        console.log(arrID);
        console.log(recvBrkupID);
        console.log(deheadingBrkupid);

        $.ajax({
            url: 'Ajax/ajax-de-heading-trackid.php',
            type: 'POST',
            data: {
                methodType: "TableUpdate",
                arrivalID: arrID,
                recvBreakupID: recvBrkupID,
                deheadingBreakupID: deheadingBrkupid,
                honQty: $(this).closest("tr").find(".txthonQty").val(),
                hlCount: $(this).closest("tr").find(".txthlCount").val(),
                hlQty: $(this).closest("tr").find(".txthlQty").val(),
                transactionDate: $("#txtDeHeadingDate").val(),
                trackID: $("#drpTrackCode").val(),
                totalDeheadingWeight: $(this).closest("tr").find(".txtTotalDeheadingWeight").val(),
                txtCheckingCount2: $(this).closest("tr").find(".txtCheckingCount2").val(),
                txtHONQuantity: $(this).closest("tr").find(".txtHONQuantity").val(),
            },
            success: function (data) {
                console.log(data);
                if (data.Error) {
                    alert(data.Error);
                } else {
                    if (data.htmlRes) {
                        $("#divDataList").html(data.htmlRes);
                        fnShowDataTable();

                        fnValidateBreakup(); // Breakup Validation
                    }
                    alert(data.Msg);
                }
                return false;
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        $("#drpProductionPlant2").select2({
            dropdownParent: $("#modalTransfetPlant"),
        });

        $('.close').on('click', function () {
            $('#modalTransfetPlant').modal('hide');
        });
    });
</script>

</body>

</html>