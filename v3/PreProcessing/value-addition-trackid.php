<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter; //sriram
$hasVACrates = $ClientConfigData->hasVACrates;
$EnabelGradeField = $ClientConfigData->EnabelGradeField;

$ArrivalID = trim($_GET['ArrivalID']);

$ArrivalID = isset($_GET['ArrivalID']) && !empty($_GET['ArrivalID']) ? $_GET['ArrivalID'] : "";

// var_dump($ArrivalID);
// exit;
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
$Grade = $resDropdownsAPI->Data->Grade;

// echo json_encode($resVariety, JSON_PRETTY_PRINT);
// exit;
$List = $resDropdownsAPI->Data;

// SESSION VALUES 
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$ShowDeheadingStationID = $ArrayClientConfigData['ShowDeheadingStationID'];
$DefaultCrate = $ArrayClientConfigData['DefaultCrate'];
$ProductionPlant = $_SESSION['UnitID'];
$IsVAStationLinked = $ArrayClientConfigData['IsVAStationLinked'];
$IsRejInVA = $ArrayClientConfigData['IsRejInVA'];

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
                                                id="drpArrivalPlant" disabled>
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
                                            <label for="drpTrackID" class="form-label">Track ID<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpTrackID" id="drpTrackID">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <?php if ($ShowDeheadingStationID == 1) { ?>

                                            <?php if ($IsVAStationLinked == 1): ?>
                                                <div class="form-group col-sm-3 mb-3">
                                                    <label for="drpVAStation" class="form-label">VA Station<span
                                                            style="color:red">*</span></label>
                                                    <?php
                                                    $selectedStations = isset($_SESSION["VAStationIDs"]) ? explode(',', $_SESSION["VAStationIDs"]) : [];

                                                    $disableDropdown = count($selectedStations) === 1 ? 'disabled' : '';
                                                    $selectedDropdown = count($selectedStations) === 1 ? 'selected' : '';
                                                    ?>

                                                    <select class="drpSelect form-control" name="drpVAStation" id="drpVAStation"
                                                        <?php echo $disableDropdown; ?>>
                                                        <option value="">Select</option>
                                                        <?php
                                                        foreach ($resCenter as $Item) {
                                                            if (in_array($Item->StationID, $selectedStations)) {
                                                        ?>
                                                                <option value="<?php echo $Item->StationID; ?>" <?php echo $selectedDropdown; ?>>
                                                                    <?php echo $Item->UnitName; ?>
                                                                </option>
                                                        <?php
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            <?php else: ?>
                                                <div class="form-group col-sm-3 mb-3">
                                                    <label for="drpVAStation" class="form-label">VA Station<span
                                                            style="color:red">*</span></label>


                                                    <select class="drpSelect form-control" name="drpVAStation" id="drpVAStation"
                                                        <?php echo $disableDropdown; ?>>
                                                        <option value="">Select</option>
                                                        <?php
                                                        foreach ($resCenter as $Item) {

                                                        ?>
                                                            <option value="<?php echo $Item->StationID; ?>" <?php echo $selectedDropdown; ?>>
                                                                <?php echo $Item->UnitName; ?>
                                                            </option>
                                                        <?php

                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                            <?php endif; ?>
                                        <?php } ?>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtValueAdditionDate" class="form-label">Value Addition
                                                Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtValueAdditionDate"
                                                name="txtValueAdditionDate" value="<?php echo date("Y-m-d"); ?>"
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
                                                id="drpSuperVisor">
                                                <option value=""> Select</option>
                                                <?php foreach ($resSupervisor as $Item) { ?>
                                                    <option value="<?php echo $Item->ID; ?>">
                                                        <?php echo $Item->UserName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpHLCount" class="form-label">HL Count <span
                                                    id="txtGrade"></span><span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpHLCount" id="drpHLCount">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtTotalCrates" class="form-label">Totals (<span
                                                    id="totals"></span>)<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtTotalCrates"
                                                name="txtTotalCrates" placeholder="Balance" value="" readonly />
                                            <input type="hidden" id="hdnGradingBreakUpID" name="hdnGradingBreakUpID" />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpVariety" class="form-label">Variety <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpVariety" id="drpVariety">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>
                                        <?php 
                                        if(!empty($EnabelGradeField)){?>
                                            <div class="form-group col-sm-3">
                                                <label for="drpVariety" class="form-label">Grade <span
                                                        style="color:red">*</span></label>
                                                <select class="drpSelect form-control" name="drpGrade" id="drpGrade" required>
                                                    <option value=""> Select</option>
                                                    <?php 
                                                    if(!empty($Grade)){
                                                        foreach($Grade as $row){?>
                                                            <option value="<?php echo $row->value;?>"><?php echo $row->value;?></option>
                                                        <?php }
                                                    }?>
                                                </select>
                                            </div>
                                        <?php }?>

                                        <div class="form-group col-sm-3">
                                            <label for="txtHLWt" class="form-label">HL Wt <span id="txtInQty"></span>
                                                (Kgs) <span style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtHLWt" name="txtHLWt"
                                                placeholder="Enter HL Weight" value="<?php echo $DefaultCrate ?>" />
                                            <input type="hidden" id="hdnBalanceWeight" name="hdnBalanceWeight"
                                                value="" />
                                        </div>
                                        <?php if ($hasVACrates == 1) { ?>
                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="txtCrates" class="form-label">Crates<span
                                                        style="color:red">*</span></label>
                                                <input type="number" class="form-control" id="txtCrates" name="txtCrates"
                                                    placeholder="Enter Crates" />
                                                <input type="hidden" id="hdnBalanceCrates" name="hdnBalanceCrates"
                                                    value="" />
                                                <input type="hidden" id="hdnEditCrates" name="hdnEditCrates" value="" />
                                            </div>
                                        <?php } ?>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtVAOutQuantity" class="form-label">VAOutQuantity</label>
                                            <input type="number" class="form-control" id="txtVAOutQuantity"
                                                name="txtVAOutQuantity" placeholder="Enter VAOutQuantity" />
                                        </div>
                                        <?php if ($IsRejInVA == 1) { ?>
                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="txtReturnQuantity" class="form-label">Rejection Weight</label>
                                                <input type="number" class="form-control" id="txtReturnQuantity"
                                                    name="txtReturnQuantity" placeholder="Enter Rejection Weight" />
                                            </div>
                                        <?php } ?>
                                    </div>

                                    <div class="text-center">
                                        <input type="hidden" id="hdnSaveType" name="hdnSaveType"
                                            value="<?php echo $ArrivalID ?>" />
                                        <input type="hidden" id="hdnNewArrival" name="hdnNewArrival" />

                                        <input type="hidden" id="hdnSelected" name="hdnSelected" value="" />
                                        <input type="hidden" id="hdnBreakUpID" name="hdnBreakUpID" value="" />
                                        <button class="btn btn-primary" name="submit" id="submit" type="submit"
                                            data-breakupid="">Save</button>
                                        <button class="btn btn-danger" id="btnCancel" disabled
                                            type="button">Cancel</button>
                                        <button class="btn btn-success" name="btnTransfer" id="btnTransfer"
                                            data-transferid="" type="button" disabled>Transfer to Another Plant</button>

                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="divDataList">
                            <!-- -->
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalTransfetPlant" role="dialog">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title modheading"> Transfer to Another Plant <span
                                        class="spanInvNo"></span></h5>
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
                                                                <label for="drpProductionPlant2"
                                                                    class="form-label">Production Plant<span
                                                                        style="color:red">*</span></label>
                                                                <select class="drpSelect2 form-control"
                                                                    name="drpProductionPlant2" id="drpProductionPlant2">
                                                                    <option value=""> Select</option>
                                                                    <?php foreach ($resProductionPlant as $Item) { ?>
                                                                        <?php if ($Item->ProcessingStationID != $ProductionPlant) { ?>
                                                                            <option
                                                                                value="<?php echo $Item->ProcessingStationID; ?>">
                                                                                <?php echo $Item->FullName . '-' . $Item->StationName; ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>

                                                            <div class="col-md-6 col-lg-3 mb-3">
                                                                <label for="txtTranscationDate"
                                                                    class="form-label">Transcation Date <span
                                                                        style="color:red">*</span></label>
                                                                <input type="date" class="form-control"
                                                                    id="txtTranscationDate" name="txtTranscationDate"
                                                                    placeholder="Enter Transcation Date" <?php echo 'value="' . (!empty($Data->ArrivalDate) ? $Data->ArrivalDate : $DefArrivalDate) . '"'; ?>
                                                                    max="<?php echo date('Y-m-d'); ?>">
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
    var isEditMode = false;
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

    function fnDateformat(dt) {
        var parts = dt.split("-");
        return parts[2] + "-" + parts[1] + "-" + parts[0];
    }

    function fnShowDataTable() {

        var excelHeader = "ValueAddition_ArrivalDate:" + fnDateformat($("#txtArrivalDate").val()) + "_" + $.trim($("#drpTrackID option:selected").text()) + "_ValueAdditionDate: " + fnDateformat($("#txtValueAdditionDate").val());

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
                title: excelHeader,
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });

        table.on('draw.dt', function() {
            let hlQuantityKgs = 0;
            let vtOutQuantity = 0;
            let totalYield = 0;
            $('.tdHLWtKgs').each(function() {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    hlQuantityKgs += weight;
                }
            });
            $('.tdVAOutQuantity').each(function() {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    vtOutQuantity += weight;
                }
            });
            var yieldCount = 0;
            $('.tdYield').each(function() {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    totalYield += weight;
                    if (weight > 0) {
                        yieldCount++;
                    }
                }
            });

            console.log("yieldCount ", yieldCount)

            $('#TotalHLQuantityKgs').text(hlQuantityKgs.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#TotalWtAftrVA').text(vtOutQuantity.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            if (yieldCount > 0) {

                $('#TotalYield').text((totalYield / yieldCount).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }
        });
    }

    // getting Track Codes
    $(document).on("change", "#txtArrivalDate", function() {
        getArrivals();
        var productionPlant = <?php echo $ProductionPlant ?>;
        console.log('PlantID on Date change : ' + productionPlant);

        $("#drpTrackID").val("").trigger("change");
        // $("#drpArrivalPlant").val(productionPlant).trigger("change");

        // $("#drpVAStation").val("").trigger("change");
        $("#drpShift").val("").trigger("change");
        $("#drpSuperVisor").val("").trigger("change");
        $("#drpHLCount").val("").trigger("change");
        $("#drpVariety, #drpGrade").val("").trigger("change");
        $("#txtTotalCrates, #txtCrates, #hdnBalanceWeight, #hdnBalanceCrates, #hdnBreakUpID, #hdnEditCrates").val("");
        $("#txtCrates").prop("readonly", false)
        $("#txtHLWt").prop("readonly", false)

        $("#divDataList").html("");
    });

    function getArrivals() {

        var productionPlant = <?php echo $ProductionPlant ?>;

        var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram
        var IsRejInVA = <?php echo $IsRejInVA ?>; // sriram

        if (productionPlant != 0 || IsPlantFilter == 0) {
            $("#drpArrivalPlant").prop('disabled', true);
            if (IsPlantFilter == 0) {
                $("#drpArrivalPlant").val("");
            } else {
                $("#drpArrivalPlant").val(productionPlant).trigger('change');
            }
        } else {
            $("#drpArrivalPlant").prop('disabled', false);
            // $("#drpArrivalPlant").val("").trigger('change');
        }

        $.ajax({
            url: 'Ajax/ajax-value-addition-trackID.php',
            type: 'POST',
            data: {
                arrivalDate: $('#txtArrivalDate').val(),
                plantID: $("#drpArrivalPlant").val(),
                isRejInVA: IsRejInVA,
                vaStation: $("#drpVAStation").val(),
                type: "ValueAddition",
            },
            success: function(data) {

                // var list = "<option value=''>Select</option>";
                // $.each(data.Data.RmDeHeadingTrackIds, function(index, value) {
                //     list += "<option value='" + value.TrackID + "' data-plantid='" + value.PlantID + "' data-species='" + value.Species + "'>" + value.TrackCode + "</option>";
                // });

                var deheadingData = data.Data.RmDeHeadingTrackIds;
                var list = "<option value=''>Select</option>";
                if (deheadingData && deheadingData.length > 0) {
                    if (deheadingData.length == 1) {
                        var value = deheadingData[0];
                        list = "<option value='" + value.TrackID + "' data-plantid='" + value.PlantID + "' data-species='" + value.Species + "' selected>" + value.TrackCode + "</option>";

                    } else {
                        $.each(deheadingData, function(index, value) {
                            list += "<option value='" + value.TrackID + "' data-plantid='" + value.PlantID + "' data-species='" + value.Species + "'>" + value.TrackCode + "</option>";
                        });
                    }

                    $('#drpTrackID').select2('destroy');
                    $('#drpTrackID').html(list);
                    $('#drpTrackID').select2();

                    if (deheadingData.length == 1) {
                        $("#drpTrackID").prop("disabled", false)
                        $("#drpTrackID").trigger("change");
                    }
                }else {
                    // Optional: Clear and disable dropdown if no data
                    $('#drpTrackID').select2('destroy');
                    $('#drpTrackID').html("<option value=''>No Track IDs Found</option>");
                    $('#drpTrackID').select2();
                    $('#drpTrackID').prop("disabled", false);
                }

                return false;
            }
        });
    }

    $(document).ready(function() {
        getArrivals();
        $(document).on('change', '#drpArrivalPlant', function() {
            var productionPlant = <?php echo $ProductionPlant ?>;
            if (productionPlant == 0) {
                $("#txtArrivalDate").trigger('change');
            }
        });
    });

    // getting form data
    $(document).on("change", "#drpTrackID", function() {
        var productionPlant = <?php echo $ProductionPlant ?>;
        var IsPlantFilter = <?php echo $IsPlantFilter ?>;
        var IsRejInVA = <?php echo $IsRejInVA ?>;
        var gradeBreakUpID = $("#hdnGradingBreakUpID").val();
        if (($.trim($(this).val())).length > 0) {
            $.ajax({
                url: 'Ajax/ajax-value-addition-trackID.php',
                type: 'POST',
                data: {
                    methodType: "getData",
                    plantid: $("#drpArrivalPlant").val(),
                    trackID: $(this).val(),
                    isRejInVA: IsRejInVA,
                    vaStation: $("#drpVAStation").val(),
                },
                success: function(data) {
                    console.log("ffff", data);

                    var listHLCount = "<option value=''>Select</option>";
                    if (data.GradingDataList) {
                        $.each(data.GradingDataList, function(index, value) {
                            //sriram
                            // listHLCount += "<option value='" + value.HLCount + "' data-arrivalid='" + value.ArrivalID + "' data-gradingqty='" + value.GradingQty + "' data-balQty='" + value.BalanceQty + "' data-noofcrates='" + value.NoofCrates + "' data-balCrates='" + value.BalanceCrates + "' data-varietyid='" + value.VarietyID + "' data-receivalbreakupid='" + value.ReceivalBreakupID + "' data-gradingbreakUpid='" + value.GradingBreakUpID + "'" + (value.disable == 0 ? "disabled" : "") + ">" ((IsPlantFilter == 0) ? (+ "" + value.ArrivalNumber + " - " + value.Grade + " - " + ) : (+ "")) + value.Variety + " " + value.HLCount + " C (" + value.GradingQty + " KGs) </option>"; //+ value.ArrivalNumber + " - " + value.Grade + " - " 
                            listHLCount += "<option value='" + value.HLCount +
                                "' data-arrivalid='" + value.ArrivalID +
                                "' data-gradingqty='" + value.GradingQty +
                                "' data-balQty='" + value.BalanceQty +
                                "' data-noofcrates='" + value.NoofCrates +
                                "' data-balCrates='" + value.BalanceCrates +
                                "' data-varietyid='" + value.VarietyID +
                                "' data-receivalbreakupid='" + value.ReceivalBreakupID +
                                "' data-gradingbreakUpid='" + value.GradingBreakUpID +
                                  "' data-grade='" + value.Grade +
                                "'" + (value.disable == 0 ? " disabled" : "") + ">";

                            if (IsPlantFilter == 0) {
                                // if (value.IsStoreMaterial == 0) {

                                listHLCount += value.ArrivalNumber + " - " + value.Grade + " - ";
                            }

                            listHLCount += value.Variety + " " + value.HLCount + " C (" + value.GradingQty + " KGs) </option>";
                        });
                    }

                    $('#drpHLCount').select2('destroy');
                    $('#drpHLCount').html(listHLCount);
                    $('#drpHLCount').select2();

                    if (gradeBreakUpID != '') {
                        $("#drpHLCount").val($("[data-gradingbreakUpid='" + gradeBreakUpID + "']").val()).trigger('change');
                    } else {
                        $("#drpHLCount").val("").trigger("change");
                    }

                    if (data.ValueAdditionData) {

                        $("#btnTransfer").attr("data-transferid", data.ValueAdditionData.BreakUpID);
                        // $("#drpVAStation").val(data.ValueAdditionData.StationID).trigger("change");
                        $("#txtValueAdditionDate").val(data.ValueAdditionData.TransactionDate);
                        $("#drpShift").val(data.ValueAdditionData.ShiftID).trigger("change");
                        $("#drpSuperVisor").val(data.ValueAdditionData.Supervisor).trigger("change");
                    } else {
                        $("#btnTransfer").attr("data-transferid", "");
                        // $("#drpVAStation").val("").trigger("change");
                        $("#drpShift").val("").trigger("change");
                        $("#drpSuperVisor").val("").trigger("change");
                        $("#drpVariety").val("").trigger("change");
                        $("#drpHLCount").val("").trigger("change");
                        $("#txtTotalCrates, #txtCrates, #hdnEditCrates").val("");
                    }
                    $("#drpHLCount").val("").trigger("change");

                    if (data.htmlRes) {
                        $("#divDataList").html(data.htmlRes);
                        fnShowDataTable();
                    } else {
                        $("#divDataList").html("");
                    }
                    if (data.Grade) {
                        $("#txtGrade").text('(' + data.Grade + ')');
                    } else {
                        $("#txtGrade").text("");

                    }
                    return false;
                }
            });

            var trackSpecies = $("#drpTrackID option:selected").data("species");
            var variety = <?php echo json_encode($resVariety, JSON_PRETTY_PRINT); ?>;

            var listVariety = "<option value=''>Select</option>";
            if (variety) {
                $.each(variety, function(index, value) {
                    // if (trackSpecies == value.Species) {
                    listVariety += "<option value='" + value.VarietyID + "'>" + value.Species + " " + value.ShortName + "</option>";
                    // }
                });
            }
            $('#drpVariety').select2('destroy');
            $('#drpVariety').html(listVariety);
            $('#drpVariety').select2();

            // $("#drpHLCount").trigger("Change");

        } else {
            // $("#drpVAStation").val("").trigger("change");
            $("#drpShift").val("").trigger("change");
            $("#drpSuperVisor").val("").trigger("change");
            $("#drpHLCount").val("").trigger("change");
            $("#txtTotalCrates, #txtCrates, #hdnBalanceWeight, #hdnBalanceCrates, #hdnBreakUpID, #hdnEditCrates").val("");
            $("#divDataList").html("");

            var listVariety = "<option value=''>Select</option>";
            $('#drpVariety').select2('destroy');
            $('#drpVariety').html(listVariety);
            $('#drpVariety').select2();
        }
    });

//     $(document).ready(function () {
//     $("#txtHLWt").val("");
//    });

    $(document).on("change", "#drpHLCount", function() {
        if (($.trim($(this).val())).length > 0) {
            var hlCount = $(this).val();
            var varietyid = $(this).find(':selected').attr('data-varietyid');
            var noofcrates = $(this).find(':selected').attr('data-noofcrates');
            var gradingqty = $(this).find(':selected').attr('data-gradingqty');
            var balanceQty = $(this).find(':selected').attr('data-balQty');
            var balanceCrates = $(this).find(':selected').attr('data-balCrates');
            var gradingBreakUpID = $("#drpHLCount").find(':selected').attr('data-gradingbreakUpid');
            var Grade = $(this).find(':selected').attr('data-grade');
            <?php if (!empty($DefaultCrate)) : ?>
                var DefaultCrate = <?php echo json_encode($DefaultCrate); ?>;
            <?php else : ?>
                var DefaultCrate = '';
            <?php endif; ?>

            $.ajax({
                url: 'Ajax/ajax-value-addition-trackID.php',
                type: 'POST',
                data: {
                    methodType: "getBalance",
                    gradingBreakUpID: gradingBreakUpID,
                    trackID: $("#drpTrackID").val(),
                    hlCount: hlCount,
                    vaStation: $("#drpVAStation").val(),
                },
                success: function(data) {
                    if (data.Error) {
                        console.error("Server Error: " + data.Msg);
                        alert("Error: " + data.Msg);  // Optional: show user
                        return;
                    }
                    balanceData = data.BalanceDetails;

                    console.log(balanceData);
                    var roundedBalanceQty = parseFloat(balanceData.BalanceQty).toFixed(3);

                    var hasVACrates = <?php echo $hasVACrates ?>; // Harinath

                    var totalbalanceTextsText = "Balance ( " + roundedBalanceQty + " Kgs";
                    if (hasVACrates == 1) {
                        totalbalanceTextsText += " - " + balanceData.BalanceCrates + " Crates";
                    }

                    totalbalanceTextsText += ")";


                    $("#txtTotalCrates").val(totalbalanceTextsText);

                    $("#hdnBalanceWeight").val(roundedBalanceQty);
                    $("#hdnBalanceCrates").val(balanceData.BalanceCrates);

                    // if (balanceData.BalanceCrates <= 1 && roundedBalanceQty > 0) {
                    //     // $("#txtHLWt").val(roundedBalanceQty);
                    //     $("#txtHLWt").val("");
                    //     $("#txtCrates").val(balanceData.BalanceCrates);
                    // } else {
                    //     // $("#txtHLWt").val(<?php echo $DefaultCrate ?>);
                    //      $("#txtHLWt").val("");
                    //     $("#txtCrates").val("");
                    // }

                    if (balanceData.BalanceCrates <= 1 && roundedBalanceQty > 0) {
                        if (!isEditMode) {
                            $("#txtHLWt").val(roundedBalanceQty);
                        }
                        $("#txtCrates").val(balanceData.BalanceCrates);
                    } else {
                        if (!isEditMode) {
                            $("#txtHLWt").val(DefaultCrate);
                        }
                        $("#txtCrates").val("");
                    }

                }
            });

            if (balanceQty <= 0) {
                balanceCrates = 0;
            }

            var hasVACrates = <?php echo $hasVACrates ?>; // Harinath
            var totalsText = " " + gradingqty + " Kgs";
            if (hasVACrates == 1) {
                totalsText += " - " + noofcrates + " Crates ";
            }
            $('#totals').text(totalsText);
            $("#drpVariety").val(varietyid).trigger("change");
            $("#drpGrade").val(Grade).trigger("change");

            $("#hdnEditCrates").val("");
        } else {
            $("#drpVariety, #drpGrade").val("").trigger("change");
            $("#txtTotalCrates, #hdnBalanceWeight, #hdnBalanceCrates, #hdnBreakUpID, #hdnEditCrates").val("");
            $('#totals').text("");
            $("#hdnGradingBreakUpID").val("");
        }
        $("#txtVAOutQuantity").val("");
    });

    // Transfer Plant Checkbox
    $(document).on("click", ".chkSelectRecList", function() {
        fnSelectedIds();
    });

    // $(document).on("click", "#chkSelectAll", function () {
    // $('.chkSelectRecList').prop('checked', $(this).prop('checked'));
    // fnSelectedIds();
    // });

    // function fnSelectedIds() {

    // var selectedBreakUpId = [];
    // $('.chkSelectRecList:checked').each(function () {
    // selectedBreakUpId.push($(this).val());
    // });
    // var anyChecked = selectedBreakUpId.length > 0;
    // $("#btnTransfer").prop('disabled', !anyChecked);

    // var selectedBreakUpIdString = selectedBreakUpId.join(",");
    // $("#hdnSelected").val(selectedBreakUpId);

    // }

    //sriram
    $(document).on("click", "#chkSelectAll", function() {
        $(".chkSelectRecList").each(function() {
            if (!$(this).prop("disabled")) {
                $(this).prop("checked", $("#chkSelectAll").prop("checked"));
            }
        });
        fnSelectedIds();
    });

    function fnSelectedIds() {
        var selectedBreakUpId = [];

        $(".chkSelectRecList:checked").each(function() {
            if (!$(this).prop("disabled")) {
                selectedBreakUpId.push($(this).val());
            } else {
                $(this).prop("checked", false);
            }
        });

        var anyChecked = selectedBreakUpId.length > 0;
        $("#btnTransfer").prop("disabled", !anyChecked);

        $("#hdnSelected").val(selectedBreakUpId.join(","));
    }

    // Transfer Plant Checkbox

    // posting form data
    $(document).ready(function() {
        var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram
        var IsRejInVA = <?php echo $IsRejInVA ?>; // sriram

        var validationRules = {
            txtArrivalDate: {
                required: true
            },
            drpTrackID: {
                required: true
            },
            txtValueAdditionDate: {
                required: true
            },
            drpShift: {
                required: true
            },
            drpSuperVisor: {
                required: true
            },
            drpVariety: {
                required: true
            },
            drpHLCount: {
                required: true
            },
            txtTotalCrates: {
                required: true
            },
            txtHLWt: {
                required: true
            },
            txtCrates: {
                required: true
            },
            txtVAOutQuantity: {
                required: true
            },

        };

        if (IsPlantFilter != 0) {
            validationRules.drpArrivalPlant = {
                required: true
            };
            validationRules.drpVAStation = {
                required: true
            };
        }

        $("#frmValAdd").validate({
            onkeyup: false,
            onfocusout: false,
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    firstInvalidElement.focus();
                }
            },
            ignore: "",
            rules: validationRules,
            errorPlacement: function() {
                return false;
            },
            submitHandler: function() {
                // let HlQty=$("#txtHLWt").val();            
                // let VaOutQuantity = $("#txtVAOutQuantity").val();
                // if(HlQty <= VaOutQuantity) {
                //     alert("VA Out Quantity should be less than HL Wt");
                //     return false;
                // }
                var noofCrates = $("#txtCrates").val();
                var gradeBreakUpId = $("#drpHLCount").find(':selected').attr('data-gradingbreakUpid');
                console.log('gradeBreakUpId ' + gradeBreakUpId);
                var hasVACrates = <?php echo $hasVACrates ?>; // Harinath
                var IsRejInVA = <?php echo $IsRejInVA ?>; // Sriram
                var IsVAStationLinked = <?php echo $IsVAStationLinked ?>; // sriram

                if (hasVACrates == 0 || noofCrates > 0) {
                    $.ajax({
                        url: 'Ajax/ajax-value-addition-trackID.php',
                        type: 'POST',
                        data: {
                            methodType: "postForm",
                            breakUpID: $("#submit").attr("data-breakupid"),
                            arrivalDate: $("#txtArrivalDate").val(),
                            trackID: $("#drpTrackID").val(),
                            arrivalPlant: $("#drpArrivalPlant").val(),
                            vaStation: $("#drpVAStation").val(),
                            transactionDate: $("#txtValueAdditionDate").val(),
                            shift: $("#drpShift").val(),
                            superVisor: $("#drpSuperVisor").val(),
                            hlCount: $("#drpHLCount").val(),
                            varietyId: $("#drpVariety").val(),
                            hlQty: $("#txtHLWt").val(),
                            crates: $("#txtCrates").val(),
                            vaOutQuantity: $("#txtVAOutQuantity").val(),
                            returnQuantity: ($("#txtReturnQuantity").val() || ''),
                            gradingbreakUpid: $("#drpHLCount").find(':selected').attr('data-gradingbreakUpid'),
                            receivalbreakupid: $("#drpHLCount").find(':selected').attr('data-receivalbreakupid'),
                            arrivalid: $("#drpHLCount").find(':selected').attr('data-arrivalid'),
                            IsPlantFilter: IsPlantFilter,
                            hasVACrates: hasVACrates,
                            IsRejInVA: IsRejInVA,
                            IsVAStationLinked: IsVAStationLinked,
                            Grade: $("#drpGrade").val(),
                        },
                        success: function(data) {
                            console.log(data);

                            if (data.Error) {
                                alert(data.Error);
                            } else {
                                alert(data.Msg);
                                $("#submit").attr("data-breakupid", "").text("Save");

                                if (data.htmlRes) {
                                    $("#divDataList").html(data.htmlRes);
                                    fnShowDataTable();
                                }
                                $("#drpHLCount").trigger("change");

                                // var selectedOptionValue = $("#drpHLCount option[data-gradingbreakUpid='" + gradeBreakUpId + "']").val();
                                // $("#drpHLCount").val(selectedOptionValue).trigger('change');

                            }
                            return false;
                        }
                    });
                } else {
                    alert("Crates not available");
                }
            }
        });
    });

    $("#btnTransfer").on("click", function() {

        var productionPlant = $("#drpArrivalPlant").val();
        $('#modalTransfetPlant').modal('show');
        $("#errorMessage").text("");

    });

    $("#frmTransferPlant").validate({

        onkeyup: false,
        onfocusout: false,
        invalidHandler: function(form, validator) {
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
        errorPlacement: function() {
            return false;
        },
        submitHandler: function() {

            $.ajax({
                url: 'Ajax/ajax-value-addition-trackID.php',
                type: 'POST',
                data: {
                    methodType: "Transfer",
                    breakUpID: $("#hdnSelected").val(),
                    arrivalDate: $("#txtArrivalDate").val(),
                    trackID: $("#drpTrackID").val(),
                    arrivalPlant: $("#drpArrivalPlant").val(),
                    soakingPlantID: $("#drpProductionPlant2").val(),
                    vaStation: $("#drpVAStation").val(),
                    transactionDate: $("#txtValueAdditionDate").val(),
                    shift: $("#drpShift").val(),
                    superVisor: $("#drpSuperVisor").val(),
                    hlCount: $("#drpHLCount").val(),
                    varietyId: $("#drpVariety").val(),
                    hlQty: $("#txtHLWt").val(),
                    crates: $("#txtCrates").val(),
                    vaOutQuantity: $("#txtVAOutQuantity").val(),
                    gradingbreakUpid: $("#drpHLCount").find(':selected').attr('data-gradingbreakUpid'),
                    receivalbreakupid: $("#drpHLCount").find(':selected').attr('data-receivalbreakupid'),
                    arrivalid: $("#drpHLCount").find(':selected').attr('data-arrivalid'),
                    Grade: $("#drpGrade").val(),
                },
                success: function(data) {

                    console.log(data);

                    if (data.Error) {
                        $("#errorMessage").text(data.Error);
                        // alert(data.Error);
                    } else {
                        alert(data.Msg);
                        $("#errorMessage").text("");
                        $("#submit").attr("data-breakupid", "").text("Save");

                        if (data.htmlRes) {
                            $("#divDataList").html(data.htmlRes);
                            fnShowDataTable();
                        }
                        if (data.GradingDataList) {
                            var varietyId = $("#drpVariety").val();

                            var filteredData = data.GradingDataList.filter(function(item) {
                                return item.VarietyID === varietyId;
                            });

                            if (filteredData.length > 0) {
                                var balanceQty = filteredData[0].BalanceQty;
                                var balanceCrates = filteredData[0].BalanceCrates;

                                $("#txtTotalCrates").val("Balance ( " + balanceQty + " Kgs - " + balanceCrates + " Crates )");
                                if (balanceCrates <= 1) {
                                    $("#txtHLWt").val(balanceQty);
                                    $("#txtCrates").val(balanceCrates);
                                } else {
                                    $("#txtVAOutQuantity").val("");
                                    $("#txtCrates").val("");
                                }
                            }
                        }
                        if (data.ValueAdditionData) {

                            $("#btnTransfer").attr("data-transferid", data.ValueAdditionData.BreakUpID);
                            // $("#drpVAStation").val(data.ValueAdditionData.StationID).trigger("change");
                            $("#txtValueAdditionDate").val(data.ValueAdditionData.TransactionDate);
                            $("#drpShift").val(data.ValueAdditionData.ShiftID).trigger("change");
                            $("#drpSuperVisor").val(data.ValueAdditionData.Supervisor).trigger("change");
                            $('#drpHLCount option[data-gradingbreakupid="' + data.ValueAdditionData.GradingBreakupID + '"]').prop('selected', true).trigger("change");
                        } else {
                            $("#btnTransfer").attr("data-transferid", "");
                            // $("#drpVAStation").val("").trigger("change");
                            $("#drpShift").val("").trigger("change");
                            $("#drpSuperVisor").val("").trigger("change");
                            $("#drpVariety, #drpGrade").val("").trigger("change");
                            $("#drpHLCount").val("").trigger("change");
                            $("#txtTotalCrates, #txtCrates").val("");
                        }
                        $("#btnCancel").prop('disabled', true);
                        $("#submit").attr("data-breakupid", "").text("Save");
                        $("#errorMessage").text("");

                        var tid = $("#drpTrackID").val();
                        $("#drpTrackID").val(tid).trigger("change");

                        $('#modalTransfetPlant').modal('hide');
                    }
                    return false;
                }
            });
        }
    });

    $(document).on("click", ".btnEdit", function() {
        isEditMode = true;
        var breakupid = $(this).attr("data-breakupid");
        $("#submit").attr("data-breakupid", breakupid).text("Update");

        var plantId = $(this).closest("tr").find(".tdPlantId").attr("data-plantid");
        var stationId = $(this).attr("data-stationid");
        var valueAddDate = $(this).closest("tr").find(".tdTransactionDate").attr("data-date");
        var shiftid = $(this).closest("tr").find(".tdShiftId").attr("data-shiftid");
        var supervisor = $(this).attr("data-supervisor");
        var hlCount = $.trim($(this).closest("tr").find(".tdHLCount").text());
        var varietyid = $(this).closest("tr").find(".tdVariety").attr("data-varietyid");
        var hlWtKgs = $.trim($(this).closest("tr").find(".tdHLWtKgs").text());
        var vaOutQty = $.trim($(this).closest("tr").find(".tdVAOutQuantity").text());
        var noofcrates = $(this).attr("data-noofcrates");
        var Grade = $.trim($(this).closest("tr").find(".tdGrade").text());

        console.log('noofcrates ' + noofcrates);
        $("#drpVAStation").val(stationId).trigger("change");
        $("#txtValueAdditionDate").val(valueAddDate);
        $("#drpShift").val(shiftid).trigger("change");
        $("#drpSuperVisor").val(supervisor).trigger("change");
        $("#drpHLCount option[value='" + hlCount + "']").prop("disabled", false);
        $("#drpHLCount").val(hlCount).trigger("change");
        $("#drpVariety").val(varietyid).trigger("change");
        $("#drpGrade").val(Grade).trigger("change");
        crateHlWtKgs = Number(hlWtKgs) / Number(noofcrates);
        // setTimeout(() => {
            $("#txtHLWt").val(crateHlWtKgs).attr("value", crateHlWtKgs).trigger("change");
            $("#txtInQty").html('<strong>' + hlWtKgs + '</strong>');
            $("#txtCrates").val(noofcrates).attr("value", noofcrates).trigger("change");
            $("#hdnEditCrates").val(noofcrates).attr("value", noofcrates).trigger("change");
        // }, 500);
        $("#txtVAOutQuantity").val(vaOutQty);
        $("#hdnBreakUpID").val(breakupid);
        $("#btnCancel").prop('disabled', false);
    });

    $(document).on("click", "#btnCancel", function() {
        $("#submit").attr("data-breakupid", "").text("Save");
        $("#txtCrates").val("");
        $(this).prop('disabled', true);
    });

    $(document).on("click", ".btnDelete", function() {
        var plantid = $("#drpArrivalPlant").val();

        if (confirm("Are you sure you want to delete this item ?") == true) {
            $.ajax({
                url: 'Ajax/ajax-value-addition-trackID.php',
                type: 'POST',
                data: {
                    methodType: "deleteRow",
                    breakUpID: $(this).attr("data-breakupid"),
                    trackID: $("#drpTrackID").val(),
                    plantid: plantid,
                    vaStation: $("#drpVAStation").val(),
                },
                success: function(data) {
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);

                        if (data.ValueAdditionData) {
                            // $("#drpVAStation").val(data.ValueAdditionData.StationID).trigger("change");
                            $("#txtValueAdditionDate").val(data.ValueAdditionData.TransactionDate);
                            $("#drpShift").val(data.ValueAdditionData.ShiftID).trigger("change");
                            $("#drpSuperVisor").val(data.ValueAdditionData.Supervisor).trigger("change");
                            // $('#drpHLCount option[data-gradingbreakupid="' + data.ValueAdditionData.GradingBreakupID + '"]').prop('selected', true).trigger("change");
                        } else {
                            // $("#drpVAStation").val("").trigger("change");
                            $("#drpShift").val("").trigger("change");
                            $("#drpSuperVisor").val("").trigger("change");
                            $("#drpVariety, #drpGrdae").val("").trigger("change");
                            $("#drpHLCount").val("").trigger("change");
                            $("#txtTotalCrates, #txtCrates").val("");
                        }

                        if (data.htmlRes) {
                            $("#divDataList").html(data.htmlRes);
                            fnShowDataTable();
                        }
                        if (data.GradingDataList) {
                            var varietyId = $("#drpVariety").val();
                            var filteredData = data.GradingDataList.filter(function(item) {
                                return item.VarietyID === varietyId;
                            });

                            if (filteredData.length > 0) {
                                var balanceQty = filteredData[0].BalanceQty;
                                var balanceCrates = filteredData[0].BalanceCrates;
                                $("#txtTotalCrates").val("Balance ( " + balanceQty + " Kgs - " + balanceCrates + " Crates )");
                                if (balanceCrates <= 1) {
                                    $("#txtHLWt").val(balanceQty);
                                    $("#txtCrates").val(balanceCrates);
                                    // $("#txtHLWt").prop("readonly", true).val(balanceQty);
                                    // $("#txtCrates").prop("readonly", true).val(balanceCrates);
                                } else {
                                    $("#txtCrates").val("");
                                    $("#txtVAOutQuantity").val(""); // 
                                }
                            }
                        }

                        $("#drpTrackID").trigger("change");

                    }
                    return false;
                }
            });
        }
    });

    $("#txtCrates").on("keyup", function() {
        var noofCrates = Number($(this).val());
        var balanceCrates = Number($("#hdnBalanceCrates").val());
        var breakUpID = $("#hdnBreakUpID").val();
        var editCrates = Number($("#hdnEditCrates").val());

        console.log('noofCrates ' + noofCrates);
        console.log('balanceCrates ' + balanceCrates);
        console.log('editCrates ' + editCrates);
        if (breakUpID != '') {
            // var totalCrates = Number(balanceCrates) + Number(editCrates);
            var totalCrates = balanceCrates + editCrates;

            if (noofCrates > totalCrates) {
                alert("Crates exceeds");
                $(this).val(editCrates);
            }
        } else {

            if (noofCrates > balanceCrates) {
                alert("Crates exceeds");
                $(this).val("");
            }
        }

    });
</script>

<script>
    $(document).ready(function() {
        $("#drpProductionPlant2").select2({
            dropdownParent: $("#modalTransfetPlant"),
        });

        $('.close').on('click', function() {
            $('#modalTransfetPlant').modal('hide');
        });
    });
</script>

</body>

</html>