<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_PREPROCESSING_DROPDOWNS;

$bodyContent = array(
    'ArrivalID' => $ArrivalID
);
// var_dump($bodyContent);

$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resProductionPlant = $resDropdownsAPI->Data->Plant;
$resCenter = $resDropdownsAPI->Data->Center;
$resShift = $resDropdownsAPI->Data->Shift;
$resVariety = $resDropdownsAPI->Data->HlVaBillVariety;
$resSupervisor = $resDropdownsAPI->Data->Supervisor;

$List = $resDropdownsAPI->Data;

// SESSION VALUES 
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$ShowDeheadingStationID = $ArrayClientConfigData['ShowDeheadingStationID'];

$ProductionPlant = $_SESSION['UnitID'];


// echo $ProductionPlant;
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
                                <form id="frmValAdd">
                                    <div class="row">

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpArrivalPlant" class="form-label">Production Plant<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpArrivalPlant"
                                                id="drpArrivalPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionPlant as $Item) { ?>
                                                    <option value="<?php echo $Item->ProcessingStationID ?>">
                                                        <?php echo $Item->FullName . '-' . $Item->StationName ?>
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
                                            <label for="drpTrackID" class="form-label">Track ID <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpTrackID" id="drpTrackID">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpArrivalCode" class="form-label">Arrival Code <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpArrivalCode"
                                                id="drpArrivalCode">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpHonCount" class="form-label">Hon Count <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpHonCount" id="drpHonCount">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtHonWeight" class="form-label">Hon Weight <span
                                                    id="txtTotalWeight"></span> <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtHonWeight"
                                                name="txtHonWeight" placeholder="Enter Weight" value="" readonly />
                                            <input type="hidden" id="hdnBalanceWeight" name="hdnBalanceWeight" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtWeight" class="form-label">Weight<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtWeight" name="txtWeight"
                                                placeholder="Enter Weight" value="" />
                                            <input type="hidden" id="hdnWeight" name="hdnWeight" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpTransferPlant" class="form-label">Transfer Plant <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpTransferPlant"
                                                id="drpTransferPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionPlant as $Item) { ?>
                                                    <?php if ($Item->ProcessingStationID != $ProductionPlant) { ?>
                                                        <option value="<?php echo $Item->ProcessingStationID ?>">
                                                            <?php echo $Item->FullName . '-' . $Item->StationName ?>
                                                        </option>
                                                    <?php } ?>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTransferDate" class="form-label">Transfer Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtTransferDate"
                                                name="txtTransferDate" value="<?php echo date("Y-m-d"); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <div class="col-md-12 col-lg-12 mb-3 text-center">
                                            <input type="hidden" id="hdnVaTransferID" value="" />
                                            <a href="#" class="btn btn-danger">Close</a>
                                            <button class="btn btn-primary" type="submit" id="btnSubmit">Submit
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">
                                    <!--  -->
                                </div>
                            </div>
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
    // getting Track Codes
    $(document).on("change", "#txtArrivalDate", function () {
        getArrivals();
        var productionPlant = <?php echo $ProductionPlant ?>;

        // $("#drpArrivalPlant").val(productionPlant).trigger("change");
        $("#drpTrackID").val("").trigger("change");
        $("#drpArrivalCode").val("").trigger("change");
        $("#drpHonCount").val("").trigger("change");
        // $("#drpTransferPlant").val(productionPlant).trigger("change");
        $("#txtWeight").val("");
        $("#txtHonWeight").val("");
    });

    function fnShowDataTable() {
        var excelHeader = "Value Addition Transfer Report";

        table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: excelHeader, // 'Value-Addition-Trackid-<?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }

    function getArrivals() {

        var productionPlant = <?php echo $ProductionPlant ?>;

        if (productionPlant != 0) {
            $("#drpArrivalPlant").prop('disabled', true);
            $("#drpArrivalPlant").val(productionPlant).trigger('change');
            $("#drpTransferPlant").val(productionPlant).trigger('change');
        } else {
            $("#drpArrivalPlant").prop('disabled', false);
            // $("#drpArrivalPlant").val("").trigger('change');
            // $("#drpTransferPlant").val("").trigger('change');
        }

        $.ajax({
            url: 'Ajax/ajax-value-addition-hon-transfer.php',
            type: 'POST',
            data: {
                arrivalDate: $('#txtArrivalDate').val(),
                plantID: $("#drpArrivalPlant").val(),
                type: "ValueAdditionTransfer",
            },
            success: function (data) {

                var list = "<option value=''>Select</option>";
                $.each(data.Data.RmDeHeadingTrackIds, function (index, value) {
                    list += "<option value='" + value.TrackID + "' data-plantid='" + value.PlantID + "'>" + value.TrackCode + "</option>";
                });

                $('#drpTrackID').select2('destroy');
                $('#drpTrackID').html(list);
                $('#drpTrackID').select2();

                return false;
            }
        });
    }

    $(document).ready(function () {
        getArrivals();

        $(document).on('change', '#drpArrivalPlant', function () {
            var productionPlant = <?php echo $ProductionPlant ?>;
            if (productionPlant == 0) {
                $("#txtArrivalDate").trigger('change');
            }
        });
    });

    // getBillNumbers
    $(document).on("change", "#drpTrackID", function () {

        if ($(this).val().length > 0) {
            $.ajax({
                url: 'Ajax/ajax-value-addition-hon-transfer.php',
                type: 'POST',
                data: {
                    methodType: "getBillsData",
                    trackID: $(this).val(),
                },
                success: function (data) {
                    // console.log(data);

                    var listBillNumbers = "<option value=''>Select</option>";
                    if (data) {
                        $.each(data, function (index, value) {
                            listBillNumbers += "<option value='" + value.LotIDs + "'>" + value.BillNumber + "</option>";
                        });
                    }
                    $('#drpArrivalCode').select2('destroy');
                    $('#drpArrivalCode').html(listBillNumbers);
                    $('#drpArrivalCode').select2();

                }
            });
        } else {
            var listBillNumbers = "<option value=''>Select</option>";
            $('#drpArrivalCode').html(listBillNumbers);
        }
        $("#drpHonCount").val("").trigger("change");
        $("#txtWeight,#hdnBalanceWeight, #txtHonWeight").val("");
        $("#txtTotalWeight").text("");
        $("#drpTransferPlant").val("").trigger("change");
        $("#divDataList").html("");
    });

    // get Arrival Counts
    $(document).on("change", "#drpArrivalCode", function () {

        if ($(this).val().length > 0) {
            $.ajax({
                url: 'Ajax/ajax-value-addition-hon-transfer.php',
                type: 'POST',
                data: {
                    methodType: "getCountsData",
                    arrivalID: $(this).val(),
                    trackID: $("#drpTrackID").val(),
                },
                success: function (data) {
                    console.log(data);

                    var listHonCount = "<option value=''>Select</option>";
                    if (data.CountsData) {
                        $.each(data.CountsData, function (index, value) {
                            listHonCount += "<option value='" + value.HonCount + "' data-honWeight='" + value.HonWeight + "' data-honRate='" + value.HonRate + "' data-honLotId='" + value.RmLotID + "'>" + value.HonCount + "C - " + value.HonWeight + "KGs - " + value.LotNumber + "</option>";
                        });
                    }
                    $('#drpHonCount').select2('destroy');
                    $('#drpHonCount').html(listHonCount);
                    $('#drpHonCount').select2();

                    if (data.HtmlTable) {
                        $("#divDataList").html(data.HtmlTable);
                        fnShowDataTable();
                    } else {
                        $("#divDataList").html("");
                    }
                }
            });
        } else {
            var listHonCount = "<option value=''>Select</option>";

            $('#drpHonCount').html(listHonCount);
            $("#divDataList").html("");
            $("#txtWeight").val("");
            $("#txtTotalWeight").text("");
            $("#txtHonWeight").val("");
        }
        $("#txtWeight,#hdnBalanceWeight, #txtHonWeight").val("");
        $("#txtTotalWeight").text("");
        $("#drpTransferPlant").val("").trigger("change");
    });

    $(document).on("change", "#drpHonCount", function () {

        if ($(this).val().length > 0) {
            $.ajax({
                url: 'Ajax/ajax-value-addition-hon-transfer.php',
                type: 'POST',
                data: {
                    methodType: "getVaWeight",
                    count: $(this).val(),
                    trackID: $("#drpTrackID").val(),
                    arrivalID: $("#drpArrivalCode").val(),
                    lotId: $("#drpHonCount").find(':selected').attr('data-honLotId')
                },
                success: function (data) {
                    showBalanceQty(data);
                }
            });
        }
        $("#txtWeight,#hdnBalanceWeight, #txtHonWeight").val("");
        $("#txtTotalWeight").text("");
        $("#drpTransferPlant").val("").trigger("change");
    });

    function showBalanceQty(data) {
        var vaWeight = parseFloat(data.VaWeight);
        var honWeight = parseFloat($("#drpHonCount").find(':selected').attr('data-honweight'));
        var balanceQty = honWeight - vaWeight;
        $("#txtHonWeight").val('Balance : ' + balanceQty + ' KGs');
        $("#hdnBalanceWeight").val(balanceQty);
        $("#txtTotalWeight").text('(Total : ' + honWeight + ' KGs)');

    }

    $("#txtWeight").on("keyup", function () {
        var val = $(this).val();
        var balanceQty = parseFloat($("#hdnBalanceWeight").val());
        var vaTransferID = $("#hdnVaTransferID").val();
        var hdnWeight = parseFloat($("#hdnWeight").val());

        if (vaTransferID != '') {
            if (val > balanceQty + hdnWeight) {
                $(this).val("");
                alert("Weight Should Less than Hon Weight");
            }
        } else {
            if (val > balanceQty) {
                $(this).val("");
                alert("Weight Should Less than Hon Weight");
            }
        }
    });

    // posting form data
    $("#frmValAdd").validate({
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
            txtArrivalDate: {
                required: true
            },
            drpTrackID: {
                required: true
            },
            drpArrivalPlant: {
                required: true
            },
            drpArrivalCode: {
                required: true
            },
            drpHonCount: {
                required: true
            },
            txtWeight: {
                required: true
            },
            drpTransferPlant: {
                required: true
            },
            txtTransferDate: {
                required: true
            },
        },
        errorPlacement: function () {
            return false;
        },
        submitHandler: function () {
            $.ajax({
                url: 'Ajax/ajax-value-addition-hon-transfer.php',
                type: 'POST',
                data: {
                    methodType: "postForm",
                    vaTransferID: $("#hdnVaTransferID").val(),
                    arrivalDate: $("#txtArrivalDate").val(),
                    trackID: $("#drpTrackID").val(),
                    arrivalPlant: $("#drpArrivalPlant").val(),
                    arrivalCode: $("#drpArrivalCode").val(),
                    honCount: $("#drpHonCount").val(),
                    // honWeight: $("#txtHonWeight").val(),
                    weight: $("#txtWeight").val(),
                    transferPlant: $("#drpTransferPlant").val(),
                    transferDate: $("#txtTransferDate").val(),
                    honWeight: $("#drpHonCount").find(':selected').attr('data-honWeight'),
                    HonRate: $("#drpHonCount").find(':selected').attr('data-honRate'),
                    honLotID: $("#drpHonCount").find(':selected').attr('data-honLotId'),
                },
                success: function (data) {
                    console.log(data);
                    showBalanceQty(data);
                    alert(data.Msg);

                    if (data.HtmlTable) {
                        $("#divDataList").html(data.HtmlTable);
                        fnShowDataTable();
                    } else {
                        $("#divDataList").html("");
                    }

                    $("#txtWeight").val("");
                    $("#drpTransferPlant").val("").trigger("change");

                    return false;

                }
            });
        }
    });

    $(document).on("click", ".btnEdit", function () {
        var vaTransferID = $(this).attr("data-vaTransferID");
        $("#btnSubmit").text("Update");

        var transferDate = $(this).attr("data-transferDate");
        var transferPlant = $(this).closest("tr").find(".tdTransferPlant").attr("data-TransferPlantId");
        var honCount = $(this).closest("tr").find(".tdHonCount").text();
        var honWeight = $(this).closest("tr").find(".tdHonWeight").text();
        var honRate = $(this).closest("tr").find(".tdHonRate").text();
        var vaWeight = $(this).closest("tr").find(".tdVaWeight").text();

        console.log('honCount' + honCount);

        $("#hdnVaTransferID").val(vaTransferID);
        $("#txtTransferDate").val(transferDate);
        $("#drpTransferPlant").val(transferPlant).trigger("change");
        $("#drpHonCount").val(honCount).trigger("change");
        $("#txtWeight").val(vaWeight);
        $("#hdnWeight").val(vaWeight);

    });

    $(document).on("click", ".btnDelete", function () {
        var vaTransferID = $(this).attr("data-vaTransferID");
        var trackID = $(this).attr("data-trackID");
        var arrivalID = $(this).attr("data-arrivalID");
        var honCount = $(this).attr("data-honcount");
        var honLotID = $(this).attr("data-honlotid");
        var drpCount = $("#drpHonCount").val();

        $.ajax({
            url: 'Ajax/ajax-value-addition-hon-transfer.php',
            type: 'POST',
            data: {
                methodType: "delete",
                vaTransferID: vaTransferID,
                trackID: trackID,
                arrivalCode: arrivalID,
                honLotID: honLotID,
                honCount: honCount
            },
            success: function (data) {

                // console.log(data);
                if (drpCount != '') {
                    showBalanceQty(data);
                } else {

                }
                alert(data.Msg);
                if (data.HtmlTable) {
                    $("#divDataList").html(data.HtmlTable);
                    fnShowDataTable();
                } else {
                    $("#divDataList").html("");
                }

                $("#txtWeight").val("");
                $("#drpTransferPlant").val("").trigger("change");
                return false;
            }
        });
    });
</script>

</body>

</html>