<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ArrivalID = trim($_GET['ArrivalID']);

$ArrivalID = isset($_GET['ArrivalID']) && !empty($_GET['ArrivalID']) ? $_GET['ArrivalID'] : "";

$api = API_PRODUCTION_DROPDOWNS;

$bodyContent = array(
    'ArrivalID' => $ArrivalID

);
// var_dump($bodyContent);
// exit;

$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resCenter = $resDropdownsAPI->Data->Center;
$resProductionPlant = $resDropdownsAPI->Data->Plant;
$resShift = $resDropdownsAPI->Data->Shift;
$resTimePeriod = $resDropdownsAPI->Data->TimePeriod;
$resVariety = $resDropdownsAPI->Data->Variety;

$List = $resDropdownsAPI->Data;

// var_dump($List);
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
                                <form id="frmSoaking">
                                    <div class="row">

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Arrival Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtArrivalDate" name="txtArrivalDate" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpLotCode" class="form-label">Arrival/Lot Code<span
                                                    style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpLotCode" id="drpLotCode">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpProductionPlant" class="form-label">Production Plant<span
                                                    style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpProductionPlant"
                                                id="drpProductionPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionPlant as $Item) { ?>
                                                    <option value="<?php echo $Item->ProcessingStationID; ?>">
                                                        <?php echo $Item->StationName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpSourceStation" class="form-label">Source Station <span
                                                    style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpSourceStation"
                                                id="drpSourceStation">
                                                <option value=""> Select</option>
                                                <?php foreach ($resCenter as $Item) { ?>
                                                    <option value="<?php echo $Item->StationID; ?>">
                                                        <?php echo $Item->UnitName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTransactionDate" class="form-label">Transaction Date<span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtTransactionDate" name="txtTransactionDate"
                                                value="<?php echo date("Y-m-d"); ?>" max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpShift" class="form-label">Select Shift <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpShift"
                                                id="drpShift">
                                                <option value=""> Select</option>
                                                <?php foreach ($resShift as $Item) { ?>
                                                    <option value="<?php echo $Item->OptID; ?>">
                                                        <?php echo $Item->Name; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtStartTime" class="form-label">Start Time <span style="color:red">*</span></label>
                                            <input type="time" class="form-control" id="txtStartTime" name="txtStartTime" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpTimePeriod" class="form-label">Time Period <span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpTimePeriod"
                                                id="drpTimePeriod">
                                                <option value=""> Select</option>
                                                <?php foreach ($resTimePeriod as $Item) { ?>
                                                    <option value="<?php echo $Item->OptID; ?>">
                                                        <?php echo $Item->Name; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtSaltKg" class="form-label">Salt/Kg <span style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtSaltKg" pattern="^\d*(\.\d{0,3})?$" name="txtSaltKg" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtChemicalQuantity" class="form-label">Chemical Quantity <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtChemicalQuantity" name="txtChemicalQuantity" />
                                        </div>


                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50"
                                                id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                        </div>

                                    </div>

                                    <hr>

                                    <!-- <h4>Soaking Details</h4> -->
                                    <div class="row">

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpVariety" class="form-label">Variety</label>
                                            <select class="drpSelect" name="drpVariety" id="drpVariety">
                                                <option value=""> Select</option>
                                                <?php foreach ($resVariety as $Item) { ?>
                                                    <option value="<?php echo $Item->OptID; ?>">
                                                        <?php echo $Item->Name; ?></option>
                                                <?php  } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTubNumber" class="form-label">Tub Number </label>
                                            <input type="text" class="form-control" id="txtTubNumber" name="txtTubNumber" placeholder="Enter Tub Number">
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <!-- <label for="drpInCount" class="form-label">In Count </label> -->
                                            <label for="drpInCount" class="form-label">Source Count </label>
                                            <!-- <input type="number" class="form-control" id="txtInCount" name="txtInCount" placeholder="Enter In Count"> -->
                                            <select class="drpSelect" name="drpInCount" id="drpInCount">
                                                <option value="" data-hlbreakup=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtInCount" class="form-label">In Count</label>
                                            <input type="number" class="form-control" id="txtInCount" pattern="^\d*(\.\d{0,3})?$" name="txtInCount" placeholder=" Enter In Count">
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtInQuantity" class="form-label">In Quantity</label>
                                            <input type="number" class="form-control" id="txtInQuantity" pattern="^\d*(\.\d{0,3})?$" name="txtInQuantity" placeholder=" Enter In Quantity">
                                        </div>
                                        
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtOutCount" class="form-label">Out Count</label>
                                            <input type="number" class="form-control" id="txtOutCount" pattern="^\d*(\.\d{0,3})?$" name="txtOutCount" placeholder=" Enter Out Count">
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtOutQuantity" class="form-label">Out Quantity</label>
                                            <input type="text" class="form-control" id="txtOutQuantity" name="txtOutQuantity" placeholder="Enter Out Quantity">
                                        </div>

                                    </div>

                                    <div class="text-center">
                                        <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $ArrivalID ?>" />
                                        <input type="hidden" id="hdnNewArrival" name="hdnNewArrival" />
                                        <!--<a href="rm-arrival-list" class="btn btn-danger">Close</a>-->

                                        <a href="javascript:void(0)" class="newItem btn btn-success waves-effect waves-light">
                                            <i class="fa fa-plus"></i> New
                                        </a>
                                        <button class="btn btn-primary" name="submit" id="submit" type="submit">Save</button>
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

    $(document).on('keydown', 'input[pattern]', function(e) {
        var input = $(this);
        var oldVal = input.val();
        var regex = new RegExp(input.attr('pattern'), 'g');

        setTimeout(function() {
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
                title: 'Soaking-<?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }

    $(document).ready(function() {
        // fnShowDataTable();

        var frmData = {
            methodType: "get",
            ArrivalID: arrivalID,
        }
        $.ajax({
            url: 'Ajax/ajax-soaking.php',
            type: 'POST',
            data: frmData,
            success: function(data) {
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
                return false;
            }
        });
    });


    $(document).on("change", "#txtArrivalDate", function() {
        $.ajax({
            url: 'Ajax/ajax-soaking.php',
            type: 'POST',
            data: {
                ArrivalDate: $(this).val(),
                Type: "Soaking"
            },
            success: function(data) {
                console.log(data.Data);

                var list = "<option>Select</option>";
                $.each(data.Data, function(index, value) {
                    list += "<option value='" + value.RmArrivalID + "'>" + value.ArrivalNumber + " - " + value.ArrivalCount + " - " + value.ArrivalWeight + "</option>";
                });
                $('#drpLotCode').html(list);
                $('#drpLotCode').trigger("change");
                return false;
            }
        });
    });

    function bindFormData(resJson) {

        $("#txtArrivalDate").val("");
        $("#drpLotCode").val(resJson.ArrivalID).trigger('change');
        $("#drpProductionPlant").val(resJson.PlantID).trigger('change');
        $("#drpSourceStation").val(resJson.SourceStationID).trigger('change');
        $("#txtTransactionDate").val(resJson.TransactionDate);
        $("#drpShift").val(resJson.Shift).trigger('change');
        $("#txtStartTime").val(resJson.SoakInTime);
        $("#drpTimePeriod").val(resJson.TimePeriod).trigger('change');
        $("#txtSaltKg").val(resJson.SaltQty);
        $("#txtChemicalQuantity").val(resJson.ChemicalQty);
        $("#txtRemarks").val(resJson.Remarks);
        $("#drpVariety").val(resJson.Variety).trigger('change');
        $("#txtTubNumber").val(resJson.TubNumber);
        $("#drpInCount").val(resJson.SoakInCount);
        $("#txtInQuantity").val(resJson.SoakInQty);
        $("#txtOutCount").val(resJson.SoakOutCount);
        $("#txtOutQuantity").val(resJson.SoakOutQty);

    }

    function removeQueryParameters(url) {
        const urlObject = new URL(url);
        urlObject.search = '';
        return urlObject.toString();
    }

    $(document).on("click", ".newItem", function() {

        $("#txtTransactionDate, #txtSaltKg, #txtChemicalQuantity, #txtRemarks, #txtTubNumber, #txtInQuantity, #txtOutCount, #txtOutQuantity").val("");
        $("#drpLotCode, #drpProductionPlant, #drpSourceStation, #drpShift, #drpTimePeriod, #drpVariety, #drpInCount").val("").trigger('change');
        arrivalID = "";

        table.clear().draw();
        $("#divDataList").remove();
        $("#hdnNewArrival").val("NEW");

        var url = window.location.href;
        var newUrl = removeQueryParameters(url);
        window.history.replaceState(null, null, newUrl);

        return false;
    });

    $(document).on("change", "#drpLotCode", function() {
        $("#txtTransactionDate, #txtStartTime, #txtSaltKg, #txtChemicalQuantity, #txtRemarks, #txtTubNumber, #txtInQuantity, #txtOutCount, #txtOutQuantity").val("");
        $("#drpProductionPlant, #drpSourceStation, #drpShift, #drpTimePeriod, #drpVariety,#drpInCount").val("").trigger('change');

        var arrid = $(this).val();

        if (arrid != "Select") {
            var frmData = {
                methodType: "get",
                ArrivalID: arrid,
            }
            $.ajax({
                url: 'Ajax/ajax-soaking.php',
                type: 'POST',
                data: frmData,
                success: function(data) {
                    var data = JSON.parse(data);
                    console.log(data);
                    var htmlData = data.html;
                    var resJson = data.json;
                    var hlBreakId = data.hlBreakId;
                    console.log(resJson);

                    var listHlBreakId = "<option value='' data-hlbreakup=''>Select</option>";
                    $.each(hlBreakId, function(index, val) {
                        listHlBreakId += '<option value="' + val.HLCount + '" data-hlbreakup="' + val.HLBreakupID + '">' + val.Title + '</option>';
                    });
                    // console.log(listHlBreakId);
                    $("#drpInCount").html("").html(listHlBreakId);

                    $("#divDataList").html(htmlData);
                    fnShowDataTable();
                    return false;
                }
            });

        }


    });

    $("#frmSoaking").validate({
        onkeyup: false,
        onfocusout: false,
        invalidHandler: function(form, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var firstInvalidElement = $(validator.errorList[0].element);
                //$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
                firstInvalidElement.focus();
            }
        },
        ignore: "",
        rules: {
            drpLotCode: {
                required: true,
                selectcheck: true
            },
            drpProductionPlant: {
                required: true,
                selectcheck: true
            },
            drpSourceStation: {
                required: true,
                selectcheck: true
            },
            txtTransactionDate: {
                required: true,
                selectcheck: true
            },
            drpShift: {
                required: true,
                noSpace: true
            },
            txtStartTime: {
                required: true,
                noSpace: true
            },
            drpTimePeriod: {
                required: true,
                selectcheck: true
            },
            txtSaltKg: {
                required: true,
                selectcheck: true
            },
            txtChemicalQuantity: {
                required: true,
                selectcheck: true
            },
            drpVariety: {
                required: true,
                noSpace: true
            },
            txtTubNumber: {
                required: true,
                selectcheck: true
            },
            drpInCount: {
                required: true
            },
            txtInCount: {
                required: true
            },
            txtInQuantity: {
                required: true,
                selectcheck: true
            },
            txtOutCount: {
                required: true,
                selectcheck: true
            },
            txtOutQuantity: {
                required: true,
                selectcheck: true
            },
        },
        errorPlacement: function() {
            return false; // suppresses error message text
        },
        submitHandler: function() {

            var selectHlBreakUpId = $("#drpInCount").find(':selected').attr('data-hlbreakup');

            //var isInsert = '<?php // echo isset($ArrivalID) 
                                ?>';
            // if(isInsert.length > 0) {
            $.ajax({
                url: 'Ajax/ajax-soaking.php',
                type: 'POST',
                data: {
                    methodType: "post",
                    ArrivalDate: $("#txtArrivalDate").val(),
                    ArrivalID: $("#drpLotCode").val(),
                    drpProductionPlant: $("#drpProductionPlant").val(),
                    drpSourceStation: $("#drpSourceStation").val(),
                    txtTransactionDate: $("#txtTransactionDate").val(),
                    drpShift: $("#drpShift").val(),
                    txtStartTime: $("#txtStartTime").val(),
                    drpTimePeriod: $("#drpTimePeriod").val(),
                    txtSaltKg: $("#txtSaltKg").val(),
                    txtChemicalQuantity: $("#txtChemicalQuantity").val(),
                    txtRemarks: $("#txtRemarks").val(),
                    drpVariety: $("#drpVariety").val(),
                    txtTubNumber: $("#txtTubNumber").val(),
                    drpInCount: $("#drpInCount").val(),
                    txtInCount: $("#txtInCount").val(),
                    txtInQuantity: $("#txtInQuantity").val(),
                    txtOutCount: $("#txtOutCount").val(),
                    txtOutQuantity: $("#txtOutQuantity").val(),
                    hlBreakUpId: selectHlBreakUpId
                },
                success: function(data) {
                    var data = JSON.parse(data);
                    var htmlData = data.html;
                    var resJson = data.json;
                    var error = data.Error;
                    var msg = data.Msg;

                    console.log(data);
                    console.log(resJson);

                    if (error === null) {
                        alert(msg);
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
                    //     console.log(newUrl);
                    //     // window.history.pushState({}, '', currentUrl);
                    //     // window.location.href = newUrl;
                    // }

                    $("#divDataList").html(htmlData);
                    fnShowDataTable();

                    $("#drpVariety,#drpInCount").val("").trigger('change');
                    $("#txtTubNumber, #txtInQuantity, #txtOutCount, #txtOutQuantity").val("");

                    return false;
                }
            });
        }
    });

    $(document).on("click", ".deleteBrkUp", function() {
        var arrId = $(this).attr("data-arrivalid");
        var brkUpID = $(this).attr("data-arrivalbrkupid");
        // console.log(brkUpID);

        var frmData = {
            methodType: "delete",
            ArrivalID: arrId,
            BreakUpID: brkUpID,
        }
        if (confirm("Are you sure you want to delete this item ?") == true) {
            $.ajax({
                url: 'Ajax/ajax-soaking.php',
                type: 'POST',
                data: frmData,
                success: function(data) {
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
</script>

</body>

</html>