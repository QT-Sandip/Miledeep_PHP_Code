<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ArrivalID = trim($_GET['ArrivalID']);

$ArrivalID = isset($_GET['ArrivalID']) && !empty($_GET['ArrivalID']) ? $_GET['ArrivalID'] : "";

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$hasWageSelector = $ArrayClientConfigData['hasWageSelector'];
// var_dump($ArrivalID);
// exit;

$api = API_PREPROCESSING_DROPDOWNS;

$bodyContent = array(
    'ArrivalID' => $ArrivalID
);
// var_dump($bodyContent);

$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

// var_dump($resDropdownsAPI);

$resProductionPlant = $resDropdownsAPI->Data->Plant;
$resCenter = $resDropdownsAPI->Data->Center;
$resShift = $resDropdownsAPI->Data->Shift;
//$resVariety = $resDropdownsAPI->Data->Variety;
$resVariety = $resDropdownsAPI->Data->HlVaBillVariety;
$resWages = $resDropdownsAPI->Data->Wages;

$List = $resDropdownsAPI->Data;
// echo json_encode($List, JSON_PRETTY_PRINT);
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
                                <!-- <h4 class="card-title">Bootstrap Validation - Normal</h4> -->
                                <form id="frmValAdd">
                                    <div class="row">
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Arrival Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtArrivalDate"
                                                name="txtArrivalDate" placeholder="" />
                                        </div>

                                        <!-- 02-02-2024 Durga Prasad -->
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpLotCode" class="form-label">Arrival/Lot Code<span
                                                    style="color:red">*</span></label>

                                            <select class="drpSelect form-control" name="drpLotCode" id="drpLotCode">
                                                <option value=""> Select</option>
                                                <option value="0"> Melting</option>
                                                <option value="1"> Reglazing</option>
                                            </select>
                                        </div>
                                        <!-- 02-02-2024 Durga Prasad -->

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpProductionPlant" class="form-label">Production Plant<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpProductionPlant"
                                                id="drpProductionPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionPlant as $Item) { ?>
                                                    <option value="<?php echo $Item->ProcessingStationID ?>">
                                                        <?php echo $Item->StationName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpCenter" class="form-label">Center<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpCenter" id="drpCenter">
                                                <option value=""> Select</option>
                                                <?php foreach ($resCenter as $Item) { ?>
                                                    <option value="<?php echo $Item->StationID; ?>">
                                                        <?php echo $Item->UnitName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTransactionDate" class="form-label">Transaction Date<span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtTransactionDate"
                                                name="txtTransactionDate" value="<?php echo date("Y-m-d"); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpShift" class="form-label">Select Shift <span
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
                                            <label for="txtIceConsumption" class="form-label">Ice Consumption </label>
                                            <input type="number" class="form-control" id="txtIceConsumption"
                                                pattern="^\d*(\.\d{0,2})?$" name="txtIceConsumption" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50"
                                                id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">

                                        <div class="form-group col-sm-3">
                                            <label for="drpVariety" class="form-label">Select Variety <span
                                                    style="color:red">*</span>
                                                <!-- <button type="button" class="btn btn-success py-0" data-bs-toggle="modal" data-bs-target="#modalCreateSupplier">New </button> -->
                                            </label>
                                            <select class="drpSelect  form-control" name="drpVariety" id="drpVariety">
                                                <option value=""> Select</option>
                                                <?php foreach ($resVariety as $Item) { ?>
                                                    <option value="<?php echo $Item->VarietyID; ?>">
                                                        <?php echo $Item->ShortName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpHLCount" class="form-label">HL Count <span
                                                    style="color:red">*</span></label>
                                            <!-- <input type="number" class="form-control" id="txtHLCount" pattern="^\d*(\.\d{0,2})?$" name="txtHLCount" /> -->
                                            <select class="drpSelect form-control" name="drpHLCount" id="drpHLCount">
                                                <option value="" data-hlbreakup=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="txtHLWt" class="form-label">HL Wt(Kgs) <span
                                                    style="color:red">*</span></label>

                                            <input type="number" class="form-control" id="txtHLWt"
                                                pattern="^\d*(\.\d{0,3})?$" name="txtHLWt" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtReceivedHLWt" class="form-label">Received HL Wt(Kgs) <span
                                                    style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtReceivedHLWt"
                                                pattern="^\d*(\.\d{0,3})?$" name="txtReceivedHLWt" />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="txtWeightAfterVA" class="form-label">Weight After VA(Kgs) <span
                                                    style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtWeightAfterVA"
                                                pattern="^\d*(\.\d{0,3})?$" name="txtWeightAfterVA" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtCountAfterVA" class="form-label">Count After VA <span
                                                    style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtCountAfterVA"
                                                pattern="^\d*(\.\d{0,2})?$" name="txtCountAfterVA" />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="txtReturnWt" class="form-label">Return Wt(Kgs) <span
                                                    style="color:red">*</span></label>

                                            <input type="number" class="form-control" id="txtReturnWt"
                                                pattern="^\d*(\.\d{0,3})?$" name="txtReturnWt" />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="txtBrokenWt" class="form-label">Broken Wt(Kgs) 
                                                <!-- <span style="color:red">*</span> -->
                                            </label>

                                            <input type="number" class="form-control" id="txtBrokenWt"
                                                pattern="^\d*(\.\d{0,3})?$" name="txtBrokenWt" />
                                        </div>

                                        <div class="form-group col-sm-3" <?php echo $hasWageSelector == 1 ? "" : "hidden" ?>>
                                            <label for="drpWages" class="form-label">Select Wage <span
                                                    style="color:red">*</span>
                                                <!-- <button type="button" class="btn btn-success py-0" data-bs-toggle="modal" data-bs-target="#modalCreateSupplier">New </button> -->
                                            </label>
                                            <select class="drpSelect  form-control" name="drpWages" id="drpWages">
                                                <option value=""> Select</option>
                                                <?php foreach ($resWages as $Item) { ?>
                                                    <option value="<?php echo $Item->OptID; ?>">
                                                        <?php echo $Item->Name; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                    </div>

                                    <div class="text-center">
                                        <input type="hidden" id="hdnSaveType" name="hdnSaveType"
                                            value="<?php echo $ArrivalID ?>" />
                                        <input type="hidden" id="hdnNewArrival" name="hdnNewArrival" />
                                        <!-- <a href="rm-arrival-list" class="btn btn-danger">Close</a> -->

                                        <a href="javascript:void(0)"
                                            class="newItem btn btn-success waves-effect waves-light">
                                            <i class="fa fa-plus"></i> New
                                        </a>
                                        <button class="btn btn-primary" name="submit" id="submit"
                                            type="submit">Save</button>
                                        <input type="hidden" id="hdnIsUpdate" value="" />
                                        <!-- <button class="btn btn-danger" id="btnCancel" type="button">Cancel</button> -->
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
    var arrivalID = "<?php echo isset($_GET["ArrivalID"]) && !empty($_GET["ArrivalID"]) ? $_GET["ArrivalID"] : "" ?>";
    var table;

    $(document).on('keydown', 'input[pattern]', function (e) {
        var input = $(this);
        var oldVal = input.val();
        var regex = new RegExp(input.attr('pattern'), 'g');

        setTimeout(function () {
            var newVal = input.val();
            if (!regex.test(newVal)) {
                input.val(oldVal);
            }
        }, 1);
    });


    function fnShowDataTable() {
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
                title: 'value-addition-<?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }

    $(document).ready(function () {
        // fnShowDataTable();

        var frmData = {
            methodType: "get",
            ArrivalID: arrivalID,
        }
        $.ajax({
            url: 'Ajax/ajax-value-addition.php',
            type: 'POST',
            data: frmData,
            success: function (data) {
                var data = JSON.parse(data);
                console.log(data);
                var htmlData = data.html;
                var resJson = data.json;
                console.log(resJson);

                $("#divDataList").html(htmlData);
                fnShowDataTable();

                if (arrivalID) {
                    bindFormData(resJson);
                }
                // $("#drpHLCount").prop('disabled', false);
                return false;
            }
        });
    });

    $(document).on("change", "#txtArrivalDate", function () {
        var type = "ValueAddition";
        $.ajax({
            url: 'Ajax/ajax-value-addition.php',
            type: 'POST',
            data: {
                ArrivalDate: $(this).val(),
                Type: type,
            },
            success: function (data) {
                console.log(data.Data);

                var list = '<option value="">Select</option>';
                list += '<option value="0"> Melting</option>';
                list += '<option value="1"> Reglazing</option>';
                $.each(data.Data, function (index, value) {
                    list += "<option value='" + value.RmArrivalID + "'>" + value.ArrivalNumber + " - " + value.ArrivalCount + " - " + value.ArrivalWeight + "</option>";
                });
                $('#drpLotCode').select2('destroy');
                $('#drpLotCode').html(list);
                $('#drpLotCode').select2();
                // $("#drpHLCount").prop('disabled', false);

                return false;
            }
        });
    });

    function bindFormData(resJson) {
        if (resJson) {
            // $("#txtArrivalDate").val("");
            // $("#drpLotCode").val(resJson.ArrivalID).trigger('change');
            $("#drpProductionPlant").val(resJson.PlantID).trigger('change');
            $("#drpCenter").val(resJson.CenterID).trigger('change');
            $("#txtTransactionDate").val(resJson.TransactionDate);
            $("#drpShift").val(resJson.ShiftID).trigger('change');
            $("#txtIceConsumption").val(resJson.IceConsumption);
            $("#txtRemarks").val(resJson.Remarks);
            // $("#drpVariety").val(resJson.VarietyID).trigger('change');
            // $("#txtHLCount").val(resJson.HLCount);
            // $("#txtHLWt").val(resJson.HLQuantityKgs);
            // $("#txtReceivedHLWt").val(resJson.ReceivedWeight);
            // $("#txtWeightAfterVA").val(resJson.WtAftrVA);
            // $("#txtCountAfterVA").val(resJson.CountAftrVA);
            // $("#txtReturnWt").val(resJson.ReturnWeight);
        } else {
            $("#drpProductionPlant").val("").trigger('change');
            $("#drpCenter").val("").trigger('change');
            // $("#txtTransactionDate").val("");
            $("#drpShift").val("").trigger('change');
            $("#txtIceConsumption").val("");
            $("#txtRemarks").val("");
        }

    }

    function removeQueryParameters(url) {
        const urlObject = new URL(url);
        urlObject.search = '';
        return urlObject.toString();
    }

    $(document).on("click", ".newItem", function () {

        $("#txtTransactionDate, #txtIceConsumption, #txtRemarks, #txtHLWt, #txtReceivedHLWt, #txtReturnWt, #txtBrokenWt, #txtWeightAfterVA, #txtCountAfterVA").val("");
        $("#drpLotCode, #drpProductionPlant, #drpCenter, #drpShift, #drpVariety, #drpHLCount").val("").trigger('change');
        // $("#drpHLCount").prop('disabled', false);

        arrivalID = "";

        table.clear().draw();
        $("#divDataList").html("");
        $("#hdnNewArrival").val("NEW");

        var url = window.location.href;
        var newUrl = removeQueryParameters(url);
        window.history.replaceState(null, null, newUrl);

        return false;
    });

    $(document).on("change", "#drpLotCode", function () {
        // $("#txtTransactionDate, #txtIceConsumption, #txtRemarks, #txtHLCount, #txtHLWt, #txtReceivedHLWt, #txtReturnWt, #txtWeightAfterVA, #txtCountAfterVA").val("");
        // $("#drpProductionPlant, #drpCenter, #drpShift, #drpVariety").val("").trigger('change');    

        var aid = $(this).val();
        // console.log(aid);
        if (aid != "Select" || aid != 0 || aid != 1) {
            var frmData = {
                methodType: "get",
                ArrivalID: aid,
            }
            // var frmData = {
            //     methodType: "get",
            //     // ArrivalID: aid,
            // }
            // if (aid != 0) {
            //     frmData.ArrivalID = aid;
            // } else {
            //     frmData.TransactionDate = $("#txtTransactionDate").val();
            // }
            $.ajax({
                url: 'Ajax/ajax-value-addition.php',
                type: 'POST',
                data: frmData,
                success: function (data) {
                    var data = JSON.parse(data);
                    console.log(data);
                    var htmlData = data.html;
                    var resJson = data.json;
                    var hlCount = data.hlCounts;

                    var listHlCount = "<option value='' data-hlbreakup=''>Select</option>";
                    $.each(hlCount, function (index, val) {
                        listHlCount += '<option value="' + val.HLCount + '" data-hlbreakup="' + val.HLBreakupID + '">' + val.Title + '</option>';
                    });
                    // console.log(listBlock);
                    $("#drpHLCount").html("").html(listHlCount);

                    // console.log(resJson);

                    $("#divDataList").html(htmlData);
                    fnShowDataTable();

                    // if (arrivalID) {
                    bindFormData(resJson);
                    // }

                }
            });
        }

        $("#txtTransactionDate, #txtHLWt, #txtReceivedHLWt, #txtReturnWt, #txtBrokenWt, #txtWeightAfterVA, #txtCountAfterVA").val("");
        $("#drpVariety, #drpHLCount").val("").trigger('change');
        $("#drpHLCount").prop('disabled', false);

        return false;
    });


    $(document).on("change", "#txtTransactionDate", function () {

        var arrivalID = $("#drpLotCode").val();
        // console.log('arrivalID ' + arrivalID);
        if (arrivalID == 0 || arrivalID == 1) {
            var transactionDate = $(this).val();
            // console.log(aid);
            if (transactionDate != "") {
                var frmData = {
                    methodType: "get",
                    TransactionDate: transactionDate,
                }
                $.ajax({
                    url: 'Ajax/ajax-value-addition.php',
                    type: 'POST',
                    data: frmData,
                    success: function (data) {
                        var data = JSON.parse(data);
                        console.log(data);
                        var htmlData = data.html;
                        var resJson = data.json;

                        $("#divDataList").html(htmlData);
                        fnShowDataTable();

                        if (resJson) {
                            bindFormData(resJson);
                        }
                    }
                });
            }
        }

        $("#txtHLWt, #txtReceivedHLWt, #txtReturnWt, #txtBrokenWt, #txtWeightAfterVA, #txtCountAfterVA").val("");
        $("#drpVariety, #drpHLCount").val("").trigger('change');
        // $("#drpHLCount").prop('disabled', false);


        return false;
    });



    $("#frmValAdd").validate({

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
            txtArrivalDate: {
                required: true,
                selectcheck: true
            },
            drpLotCode: {
                required: true,
                selectcheck: true
            },
            drpProductionPlant: {
                required: true,
                selectcheck: true
            },
            drpCenter: {
                required: true,
                selectcheck: true
            },
            txtTransactionDate: {
                required: true,
                noSpace: true
            },
            drpShift: {
                required: true,
                noSpace: true
            },
            // txtIceConsumption: {
            //     required: true,
            // },
            drpVariety: {
                required: true,
                selectcheck: true
            },
            drpHLCount: {
                required: true,
                selectcheck: true
            },
            txtHLWt: {
                required: true,
                noSpace: true
            },
            txtReceivedHLWt: {
                required: true,
                noSpace: true
            },
            txtWeightAfterVA: {
                required: true,
                selectcheck: true
            },
            txtCountAfterVA: {
                required: true,
                noSpace: true
            },
            txtReturnWt: {
                required: true,
                selectcheck: true
            },
        },
        errorPlacement: function () {
            return false;
        },
        submitHandler: function () {

            var selectHlBreakUpId = $("#drpHLCount").find(':selected').attr('data-hlbreakup');
            var brkUpID = $("#hdnIsUpdate").attr("data-brkUpID");

            $.ajax({
                url: 'Ajax/ajax-value-addition.php',
                type: 'POST',
                data: {
                    methodType: $("#hdnIsUpdate").val() == "T" ? "update" : "post",
                    BreakUpID: $("#hdnIsUpdate").val() == "T" ? brkUpID : "",
                    ArrivalDate: $("#txtArrivalDate").val(),
                    ArrivalID: $("#drpLotCode").val(),
                    drpProductionPlant: $("#drpProductionPlant").val(),
                    drpCenter: $("#drpCenter").val(),
                    txtTransactionDate: $("#txtTransactionDate").val(),
                    drpShift: $("#drpShift").val(),
                    txtIceConsumption: $("#txtIceConsumption").val(),
                    txtRemarks: $("#txtRemarks").val(),
                    drpVariety: $("#drpVariety").val(),
                    drpHLCount: $("#drpHLCount").val(),
                    txtHLWt: $("#txtHLWt").val(),
                    txtReceivedHLWt: $("#txtReceivedHLWt").val(),
                    txtWeightAfterVA: $("#txtWeightAfterVA").val(),
                    txtCountAfterVA: $("#txtCountAfterVA").val(),
                    txtReturnWt: $("#txtReturnWt").val(),
                    txtBrokenWt: $("#txtBrokenWt").val(),
                    hlBreakUpId: selectHlBreakUpId, // Grading BreakUpID
                    drpWages: $("#drpWages").val()
                },
                success: function (data) {
                    var data = JSON.parse(data);
                    var htmlData = data.html;
                    var resJson = data.json;
                    var error = data.Error;
                    var msg = data.Msg;

                    console.log(data);
                    console.log(resJson);

                    if (error === null) {
                        alert(msg);
                        $("#submit").text("Save");
                        $("#hdnIsUpdate").val("");
                        $("#hdnIsUpdate").attr("data-brkUpID", "");
                    } else {
                        alert(error);
                        return false;
                    }

                    // if (arrivalID == "") {
                    //     if($("#hdnNewArrival").val() == "NEW") {
                    //         var newUrl = "/v3/PreProcessing/de-heading-dev"+ "?ArrivalID=" + resJson.RmArrivalID;
                    //     }else {
                    //         var newUrl = window.location.href + "?ArrivalID=" + resJson.RmArrivalID;
                    //     }
                    //     // window.history.pushState({}, '', currentUrl);
                    //     window.location.href = newUrl;
                    // }

                    $("#divDataList").html(htmlData);
                    fnShowDataTable();

                    $("#txtHLWt, #txtReceivedHLWt, #txtWeightAfterVA, #txtCountAfterVA, #txtReturnWt, #txtBrokenWt").val("");
                    $("#drpVariety, #drpHLCount").val("").trigger('change');

                    return false;
                }
            });
        }
    });

    $(document).on("click", ".deleteBrkUp", function () {
        var arrId = $(this).attr("data-ArrivalId");
        var brkUpID = $(this).attr("data-ArrivalBrkUpID");
        var transactionDate = $("#txtArrivalDate").val();
        console.log(brkUpID);

        var frmData = {
            methodType: "delete",
            ArrivalID: arrId,
            BreakUpID: brkUpID,
            transactionDate: transactionDate,
        }
        if (confirm("Are you sure you want to delete this item ?") == true) {
            $.ajax({
                url: 'Ajax/ajax-value-addition.php',
                type: 'POST',
                data: frmData,
                success: function (data) {
                    var data = JSON.parse(data);
                    console.log(data);
                    var htmlData = data.html;
                    var resJson = data.json;

                    $("#divDataList").html(htmlData);
                    fnShowDataTable();

                    if (arrivalID) {
                        bindFormData(resJson);
                    }
                    return false;
                }
            });
        }
    });


    $(document).on("click", ".btnEdit", function () {

        $("#hdnIsUpdate").val("T");

        var arrId = $(this).attr("data-ArrivalId");
        var brkUpID = $(this).attr("data-ArrivalBrkUpID");
        $("#hdnIsUpdate").attr("data-brkUpID", brkUpID);
        // $("#txtArrivalDate").val();
        // $("#drpLotCode").val();
        // console.log($(this).closest("tr").find(".tdPlant").attr("data-plantid"));
        $("#drpProductionPlant").val($(this).closest("tr").find(".tdPlant").attr("data-plantid")).trigger("change");
        $("#drpCenter").val($(this).closest("tr").find(".tdCenter").attr("data-centerid")).trigger("change");
        //$("#txtTransactionDate").val();
        $("#drpShift").val($(this).closest("tr").find(".tdShift").attr("data-shiftid")).trigger("change");
        $("#txtIceConsumption").val($(this).closest("tr").find(".tdIceConsumption").text());
        $("#txtRemarks").val($(this).closest("tr").find(".tdRemarks").text());
        $("#drpVariety").val($(this).closest("tr").find(".tdVariety").attr("data-varietyid")).trigger("change");
        $("#drpHLCount").val($(this).closest("tr").find(".tdHLCount").text()).trigger("change");
        $("#txtHLWt").val($(this).closest("tr").find(".tdHLWt").text());
        $("#txtReceivedHLWt").val($(this).closest("tr").find(".tdReceivedWeight").text());
        $("#txtWeightAfterVA").val($(this).closest("tr").find(".tdWtAftrVA").text());
        $("#txtCountAfterVA").val($(this).closest("tr").find(".tdCountAftrVA").text());
        $("#txtReturnWt").val($(this).closest("tr").find(".tdReturnWeight").text());
        $("#txtBrokenWt").val($(this).closest("tr").find(".tdBrokenWeight").text());

        $("#submit").text("Update");

        $("html, body").animate({
            scrollTop: 0
        });
    });

    $(document).on("click", "#btnCancel", function () {
        $("#submit").text("Save");
        $("#hdnIsUpdate").val("");
        $("#hdnIsUpdate").attr("data-brkUpID", "");

        $("#txtHLWt, #txtReceivedHLWt, #txtReturnWt, #txtBrokenWt, #txtWeightAfterVA, #txtCountAfterVA").val("");
        $("#drpVariety, #drpHLCount").val("").trigger('change');
    });
</script>

</body>

</html>
