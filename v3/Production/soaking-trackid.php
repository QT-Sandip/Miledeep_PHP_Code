<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;  //sriram
$addExtraQty = $ClientConfigData->addExtraQty;  //sriram
$DoContinueTubNo = $ClientConfigData->DoContinueTubNo;  //sriram
$LoadingLinersBasedOnPlant = $ClientConfigData->LoadingLinersBasedOnPlant;

$ArrivalID = trim($_GET['ArrivalID']);

$ArrivalID = isset($_GET['ArrivalID']) && !empty($_GET['ArrivalID']) ? $_GET['ArrivalID'] : "";

$api = API_PRODUCTION_DROPDOWNS;

$bodyContent = array(
    'ArrivalID' => $ArrivalID,
    'LoadingLinersBasedOnPlant' => $LoadingLinersBasedOnPlant,
    'UnitId' => $_SESSION['UnitID']
);

$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);


$resProductionPlant = $resDropdownsAPI->Data->Plant;
$resTimePeriod = $resDropdownsAPI->Data->TimePeriod;
$resChemical = $resDropdownsAPI->Data->Chemical;
$resVariety = $resDropdownsAPI->Data->HlVaBillVariety;
$resSoakingTreatment = $resDropdownsAPI->Data->SoakingTreatment;
$resProductionGrades = $resDropdownsAPI->Data->ProductionGradesList;
$resProductionLines = $resDropdownsAPI->Data->ProductionLines;
$resShift = $resDropdownsAPI->Data->Shift;


$List = $resDropdownsAPI->Data;
$ProductionPlant = $_SESSION['UnitID'];

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

                                <form id="frmSoaking">
                                    <div class="row">

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpPlant" class="form-label">Production Plant<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpPlant" id="drpPlant">
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
                                                max="<?php echo date("Y-m-d"); ?>" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpTrackCode" class="form-label">Track Code<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpTrackCode"
                                                id="drpTrackCode">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtSoakingDate" class="form-label">Soaking Date<span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtSoakingDate"
                                                name="txtSoakingDate" value="<?php echo date("Y-m-d"); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtStartTime" class="form-label">Start Time <span
                                                    style="color:red">*</span></label>
                                            <?php date_default_timezone_set("Asia/Calcutta"); ?>
                                            <input type="time" class="form-control" id="txtStartTime"
                                                value="<?php echo date("H:i") ?>" name="txtStartTime" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpTimePeriod" class="form-label">Time Period <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpTimePeriod"
                                                id="drpTimePeriod">
                                                <option value=""> Select</option>
                                                <?php foreach ($resTimePeriod as $List) { ?>
                                                    <option value="<?php echo $List->OptID ?>"><?php echo $List->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpChemical" class="form-label">Chemical<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpChemical" id="drpChemical">
                                                <option value=""> Select</option>
                                                <?php foreach ($resChemical as $List) { ?>
                                                    <option value="<?php echo $List->ChemID ?>">
                                                        <?php echo $List->ChemicalName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtChemicalQuantity" class="form-label">Chemical
                                                Quantity(KGs)<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtChemicalQuantity"
                                                name="txtChemicalQuantity" placeholder="Enter Chemical Quantity" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtSaltKg" class="form-label">Salt(KGs) <span
                                                    style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="txtSaltKg" name="txtSaltKg"
                                                placeholder="Enter Salt(KGs)" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50"
                                                id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row">

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpInCount" class="form-label">Source Count / In Count<span
                                                    class="txtGrade"></span></label>
                                            <select class="drpSelect form-control" name="drpInCount" id="drpInCount">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>
                                        <?php if($addExtraQty == 1) { ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtExtraQty" class="form-label">Excess Quantity</label>
                                                <input type="number" class="form-control" id="txtExtraQty"
                                                    name="txtExtraQty" placeholder="Enter Excess Quantity" />
                                            </div>
                                        <?php } ?>

                                        
                                        <div class="form-group col-sm-3">
                                            <label for="drpVariety" class="form-label">Variety <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpVariety" id="drpVariety">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpGrade" class="form-label">Grade <span
                                                    class="txtGrade"></span></label>
                                            <select class="drpSelect form-control" name="drpGrade" id="drpGrade">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionGrades as $List) { ?>
                                                    <option value="<?php echo $List->Grade ?>"><?php echo $List->Grade ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpSoakingTreatment" class="form-label">SoakingTreatment<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpSoakingTreatment"
                                                id="drpSoakingTreatment">
                                                <option value=""> Select</option>
                                                <?php foreach ($resSoakingTreatment as $Treatment) { ?>
                                                    <option value="<?php echo $Treatment->ShortCode ?>">
                                                        <?php echo $Treatment->ShortCode ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpLineList" class="form-label">Line List</label>
                                            <select class="drpSelect form-control" name="drpLineList" id="drpLineList">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionLines as $List) { ?>
                                                    <option value="<?php echo $List->Name ?>"><?php echo $List->Name ?>
                                                    </option>
                                                <?php } ?>

                                              

                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTubNumber" class="form-label">Tub Number</label>
                                            <input type="number" class="form-control" id="txtTubNumber"
                                                name="txtTubNumber" placeholder="Enter Tub Number" />
                                        </div>

                                         <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtInCount" class="form-label">In Count</label>
                                            <input type="number" class="form-control" id="txtInCount"
                                                name="txtInCount" placeholder="Enter In Count" />
                                        </div>

                                        

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtInQuantity" class="form-label">In Quantity <span
                                                    id="balanceQtyMessage"></span></label>
                                            <input type="hidden" id="hdnbalanceQty" value="" />
                                            <input type="hidden" id="hdnEditQty" value="" />
                                             <input type="hidden" id="hdnbalanceQtyTot" value="" />
                                            <input type="number" class="form-control" id="txtInQuantity"
                                                name="txtInQuantity" placeholder=" Enter In Quantity">
                                        </div>

                                       

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtOutCount" class="form-label">Out Count</label>
                                            <input type="number" class="form-control" id="txtOutCount"
                                                name="txtOutCount" placeholder=" Enter Out Count">
                                        </div>

                                        <!-- <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtOutQty" class="form-label">Out Quantity</label>
                                            <input type="number" class="form-control" id="txtOutQty" name="txtOutQty"
                                                placeholder=" Enter Out Quantity">
                                        </div> -->
                                    </div>
                                    <hr>

                                    <div class="row">
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtIceQty" class="form-label">Ice Qty %</label>
                                            <input type="text" class="form-control" id="txtIceQty"
                                                name="txtIceQty" placeholder="Enter Ice Qty %" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtWaterQty" class="form-label">Water Qty %</label>
                                            <input type="text" class="form-control" id="txtWaterQty"
                                                name="txtWaterQty" placeholder="Enter Water Qty %" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpShift" class="form-label">Shift </label>
                                            <select class="drpSelect form-control" name="drpShift" id="drpShift">
                                                <option value=""> Select</option>
                                                <?php foreach ($resShift as $Item) { ?>
                                                    <option value="<?php echo $Item->OptID; ?>">
                                                        <?php echo $Item->Name; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtSoakingInTemp" class="form-label">Soaking In Temperature</label>
                                            <input type="text" class="form-control" id="txtSoakingInTemp"
                                                name="txtSoakingInTemp" placeholder="Enter Soaking In Temperature" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtSoakingOutTemp" class="form-label">Soaking Out Temperature</label>
                                            <input type="text" class="form-control" id="txtSoakingOutTemp"
                                                name="txtSoakingOutTemp" placeholder="Enter Soaking Out Temperature" />
                                        </div>
                                    </div>

                                    
                                    <div class="text-center">
                                        <input type="hidden" id="hdnSaveType" name="hdnSaveType"
                                            value="<?php echo $ArrivalID ?>" />
                                        <input type="hidden" id="hdnNewArrival" name="hdnNewArrival" />
                                        <input type="hidden" id="hdnBreakUpID" name="hdnBreakUpID" />
                                        <button class="btn btn-primary" name="submit" id="submit"
                                            type="submit">Save</button>
                                        <button class="btn btn-danger" type="button" disabled
                                            id="btnCancel">Cancel</button>
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

    function fnDateformat(dt) {
        var parts = dt.split("-");
        return parts[2] + "-" + parts[1] + "-" + parts[0];
    }

    function fnShowDataTable() {
        var excelHeader = "Soaking_ArrivalDate:" + fnDateformat($("#txtArrivalDate").val()) + "_" + $.trim($("#drpTrackCode option:selected").text()) + "_SoakingDate: " + fnDateformat($("#txtSoakingDate").val());

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
                title: excelHeader,
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
        table.on('draw.dt', function () {
            let soakInQty= 0;
            
            $('.tdSoakInQty').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    soakInQty += weight;
                }
            });
           
            $('#TotalSoakInQty').text(soakInQty.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });
    }
        

    $(document).on("change", "#txtArrivalDate", function () {
        var startTime = "<?php echo date("H:i") ?>";
        console.log(startTime);
        $("#txtStartTime").val(startTime);

        getArrivals();

        $("#drpTimePeriod, #drpChemical, #drpSoakingTreatment,  #drpVariety, #drpGrade").val("").trigger('change');

        $('#drpInCount').select2('destroy');
        $('#drpInCount').val("");
        $('#drpInCount').select2();

        // $("#txtChemicalQuantity, #txtSaltKg, #txtRemarks, #txtTubNumber, #txtInQuantity, #txtOutCount, #txtOutQty, #hdnEditQty, #hdnbalanceQty").val("");
        $("#txtChemicalQuantity, #txtSaltKg, #txtRemarks, #txtTubNumber, #txtInQuantity, #txtOutCount, #hdnEditQty, #hdnbalanceQty","hdnbalanceQtyTot").val("");
        $("#balanceQtyMessage").text("");
        $("#divDataList").html("");
    });

    function getAutoTubNumber(ArrivalDate){
         var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram
           $.ajax({
            url: 'Ajax/ajax-soaking-trackID.php',
            type: 'POST',
            data: {
                arrivalDate: ArrivalDate,
                trackId: $("#drpTrackCode").val(),
                methodType: "getAutoTub",
                plantID: $("#drpPlant").val(),
                IsPlantFilter: IsPlantFilter
            },
            success: function (data) {
                console.log("dataTub",data);
                if(data?.Data?.Total && data.Data.Total != null && data.Data.Total != ''){
                    var tubNo = parseInt(data.Data.Total) + 1;
                }else{
                    tubNo = parseInt(1);
                }
                $("#txtTubNumber").val(tubNo);
            }
        });
    }

    function getArrivals() {

        var productionPlant = <?php echo $ProductionPlant ?>;
        var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram
        if (productionPlant != 0 || IsPlantFilter == 0) {
            $("#drpPlant").prop('disabled', true);
            if (IsPlantFilter == 0) {
                $("#drpPlant").val("");
            } else {
                $("#drpPlant").val(productionPlant).trigger('change');
            }
        } else {
            $("#drpPlant").prop('disabled', false);
            // $("#drpPlant").val("").trigger('change');
        }

        $.ajax({
            url: 'Ajax/ajax-soaking-trackID.php',
            type: 'POST',
            data: {
                arrivalDate: $('#txtArrivalDate').val(),
                type: "Soaking",
                plantID: $("#drpPlant").val(),
            },
            success: function (data) {
                console.log(data.Data);
                if (data.Error) {
                    alert(data.Error);
                } else {
                    var list = "<option value=''>Select</option>";
                    if (data.Data.RmDeHeadingTrackIds) {
                        $.each(data.Data.RmDeHeadingTrackIds, function (index, value) { //  "' style='color: red;'>"
                            list += "<option value='" + value.TrackID + "' data-species='" + value.Species + "'  data-plantid='" + value.PlantID + "' style='color: red;'>" + value.TrackCode + "</option>";
                        });
                    }

                    $('#drpTrackCode').select2('destroy');
                    $('#drpTrackCode').html(list);
                    $('#drpTrackCode').select2();
                }
                return false;
            }
        });
    }

    $(document).ready(function () {
        getArrivals();
        $(document).on('change', '#drpPlant', function () {
            var productionPlant = <?php echo $ProductionPlant ?>;
            if (productionPlant == 0) {
                $("#txtArrivalDate").trigger('change');
            }
        });
    });


    $(document).on("change", "#drpTrackCode", function () {
        var productionPlant = <?php echo $ProductionPlant ?>;
        var IsPlantFilter = <?php echo $IsPlantFilter ?>; 
        var addExtraQty = <?php echo $addExtraQty ?>;
        var DoContinueTubNo = <?php echo $DoContinueTubNo ?>;
        if(DoContinueTubNo == 1){
            getAutoTubNumber($("#txtArrivalDate").val()); 
        }

        if ($(this).val().length > 0) {
            $.ajax({
                url: 'Ajax/ajax-soaking-trackID.php',
                type: 'POST',
                data: {
                    methodType: "get",
                    plantID: $('#drpPlant').val(),
                    trackID: $(this).val(),
                    arrivalDate: $("#txtArrivalDate").val(),
                    addExtraQty: addExtraQty
                },
                success: function (data) {
                    console.log(data);

                    var listInCount = "<option value=''>Select</option>";
                    if (data.vaData) {
                        
                        $.each(data.vaData, function (index, value) {
                            // if (!!value?.BalanceQty && value?.BalanceQty > 0) {
                            let VAOutQty = (value.VAOutQty) ? "(" + value.VAOutQty + ")" : '';
                            listInCount += "<option value='" + value.VACount + "' data-arrivalid='" + value.ArrivalID + "' data-vaqty='" + value.VAQty
                                + "' data-noofcrates='" + value.NoofCrates + "' data-varietyid='" + value.VarietyID + "' data-receivalbreakupid='"
                                + value.ReceivalBreakupID + "' data-vabreakUpid='" + value.VABreakUpID + "' data-materialtype='" + value.MaterialType + "' data-balanceqty='" + value.BalanceQty + "' data-vlid='" + (value?.VLID ? value.VLID : 0) + "' "
                                + (value?.BalanceQty <= 0.01 ? "disabled" : "") + ">"

                                // if (value.IsStoreMaterial == 0 && IsPlantFilter==0) {
                                if (IsPlantFilter==0) {
                                 listInCount += value.ArrivalNumber + " - " + value.PackingGrade + " - "; //uma
                                }
                                
                                listInCount += value.Variety + " " + value.VACount + " C  (" + value.VAQty + " KGs) " + VAOutQty + "</option>";
                                
                                // if (value.IsStoreMaterial == 0) {

                                    // listInCount += value.ArrivalNumber + " - " + value.PackingGrade + " - "; //uma
                                // }
                            // + "' " + (value?.BalanceQty < 0 ? "disabled" : "") +">" }
                        });
                    }

                    $('#drpInCount').select2('destroy');
                    $('#drpInCount').html(listInCount);
                    $('#drpInCount').select2();

                    if(addExtraQty == 1){
                        $("#txtExtraQty").val("");
                    }

                    if (data.soakingData) {

                        var soakDateParts = data.soakingData.TransactionDate.split('-');
                        var soakDate = soakDateParts[2] + '-' + soakDateParts[1] + '-' + soakDateParts[0];

                        $("#txtSoakingDate").val(soakDate);
                        $("#txtStartTime").val(data.soakingData.SoakInTime);
                        $("#drpTimePeriod").val(data.soakingData.TimePeriod).trigger("change");
                        $("#drpSoakingTreatment").val(data.soakingData.Treatment).trigger("change");
                        $("#drpChemical").val(data.soakingData.ChemicalID).trigger("change");
                        $("#txtChemicalQuantity").val(data.soakingData.ChemicalQty);
                        $("#txtSaltKg").val(data.soakingData.SaltQty);
                        $("#txtRemarks").val(data.soakingData.Remarks);

                        $("#txtHLWt, #txtCrates").val("");
                    } else {

                        var startTime = "<?php echo date("H:i") ?>";
                        $("#txtStartTime").val(startTime);

                        $("#drpTimePeriod").val("").trigger("change");
                        $("#drpSoakingTreatment").val("").trigger("change");
                        $("#drpChemical").val("").trigger("change");
                        $("#txtChemicalQuantity").val("");
                        $("#txtSaltKg").val('');
                        $("#txtRemarks").val("");

                        $("#drpVariety").val("").trigger("change");
                        $("#drpGrade").val("").trigger("change");
                        $("#drpLineList").val("").trigger("change");
                        if(DoContinueTubNo != 1){
                            $("#txtTubNumber").val("");
                        }

                        $('#drpInCount').select2('destroy');
                        $('#drpInCount').val("");
                        $('#drpInCount').select2();

                        $("#txtInQuantity").val("");
                        $("#txtInCount").val("");
                        $("#hdnEditQty").val("");
                        $("#txtOutCount").val("");
                        // $("#txtOutQty").val("");

                        $("#hdnbalanceQty").val("");
                        $("#hdnbalanceQtyTot").val("");
                        $("#balanceQtyMessage").text("");

                    }
                    if (data.htmlRes) {
                        $("#divDataList").html(data.htmlRes);
                        fnShowDataTable();
                    } else {
                        $("#divDataList").html("");
                    }

                    if (data.Grade) {
                        $(".txtGrade").text('(' + data.Grade + ')');
                    } else {
                        $(".txtGrade").text("");

                    }
                    return false;
                }
            });

            var trackSpecies = $("#drpTrackCode option:selected").data("species");

            (trackSpecies);
            var variety = <?php echo json_encode($resVariety, JSON_PRETTY_PRINT); ?>;

            var listVariety = "<option value=''>Select</option>";
            if (variety) {
                $.each(variety, function (index, value) {
                    // if (trackSpecies == value.Species) {
                    listVariety += "<option value='" + value.VarietyID + "'>" + value.Species + " " + value.ShortName + "</option>";
                    // }
                });
            }

            $('#drpVariety').select2('destroy');
            $('#drpVariety').html(listVariety);
            $('#drpVariety').select2();
        } else {

            var startTime = "<?php echo date("H:i") ?>";
            $("#txtStartTime").val(startTime);
            $("#drpTimePeriod").val("").trigger("change");
            $("#drpSoakingTreatment").val("").trigger("change");
            $("#drpChemical").val("").trigger("change");
            $("#txtChemicalQuantity").val("");
            $("#txtSaltKg").val('');
            $("#txtRemarks").val("");

            $("#drpVariety").val("").trigger("change");
            $("#drpGrade").val("").trigger("change");
            $("#drpLineList").val("").trigger("change");
            $("#txtTubNumber").val("");

            $('#drpInCount').select2('destroy');
            $('#drpInCount').val("");
            $('#drpInCount').select2();

            $("#txtInQuantity").val("");
            $("#hdnEditQty").val("");
            $("#txtOutCount").val("");
               $("#txtInCount").val("");

            // $("#txtOutQty").val("");
            
            $("#divDataList").html("");
            $("#hdnbalanceQty").val("");
              $("#hdnbalanceQtyTot").val("");
            $("#balanceQtyMessage").text("");
            if(addExtraQty == 1){
                $("#txtExtraQty").val("");
            }
        }
    });



    $(document).on("change", "#drpInCount", function () {
        var hdnBreakUpID = $("#hdnBreakUpID").val();
        var addExtraQty = <?php echo $addExtraQty ?>; 
        if(addExtraQty == 1){
            $("#txtExtraQty").val("");
        }
        changeSourceCount();

    });
    // $(document).on("change", "#drpSoakingTreatment", function () {
    //     var Treatment = $(this).val();
    //     var balanceQty = parseFloat($("#hdnbalanceQtyTot").val());
    //     if(Treatment === 'UNT'){
    //         balanceQty = Math.round(balanceQty / (125 * 0.01));
    //     }
    //      $("#balanceQtyMessage").text("(Balance Qty : " + (parseFloat(balanceQty) || 0).toFixed(3) + " KGs )").css("color", "green");
    //       $("#hdnbalanceQty").val(balanceQty);
    // });

    function changeSourceCount() {
        var vabreakUpid = $("#drpInCount").find(':selected').attr('data-vabreakUpid');
        var arrivalid = $("#drpInCount").find(':selected').attr('data-arrivalid');
        var materialType = $("#drpInCount").find(':selected').attr('data-materialtype');
        var hdnBreakUpID = $("#hdnBreakUpID").val();
        var balanceQty = $("#drpInCount").find(':selected').attr('data-balanceqty');
        var addExtraQty = <?php echo $addExtraQty ?>; 

        if ($("#drpInCount").val().length > 0) {
            $.ajax({
                url: 'Ajax/ajax-soaking-trackID.php',
                type: 'POST',
                data: {
                    methodType: "getSouCount",
                    arrivalid: arrivalid,
                    trackID: $("#drpTrackCode").val(),
                    vabreakUpid: vabreakUpid,
                    materialType: materialType,
                    plantID: $('#drpPlant').val(),
                    addExtraQty: addExtraQty
                },
                success: function (data) {
                    console.log(data);

                    if (data.soakingData) {
                        if (data.soakingData.PlantID) {

                        } else {

                        }
                        $("#txtStartTime").val(data.soakingData.SoakInTime);
                        $("#drpTimePeriod").val(data.soakingData.TimePeriod).trigger("change");
                        $("#drpChemical").val(data.soakingData.ChemicalID).trigger("change");
                        $("#txtChemicalQuantity").val(data.soakingData.ChemicalQty);
                        $("#txtSaltKg").val(data.soakingData.SaltQty);
                        $("#txtRemarks").val(data.soakingData.Remarks);
                        $("#txtHLWt, #txtCrates").val("");
                        if(addExtraQty == 1){
                            $("#txtExtraQty").val(data.soakingData.ExcessQuantity);
                        }
                    } else {

                        var startTime = "<?php echo date("H:i") ?>";
                        $("#txtStartTime").val(startTime);
                        // $("#txtTubNumber").val("");
                        $("#txtInQuantity").val("");
                        $("#hdnEditQty").val("");
                        $("#txtOutCount").val("");
                        

                        
                    }

                    if (balanceQty) {
                        var isUpdate = $("#submit").text().trim() === "Update";
                        if(isUpdate){
                            excessQty = (data?.soakingData?.ExcessQuantity != null && data.soakingData.ExcessQuantity !== '') ? parseFloat(data.soakingData.ExcessQuantity)  : 0;
                            SoakInQty = (data?.soakingData?.SoakInQty != null && data.soakingData.SoakInQty !== '') ? parseFloat(data.soakingData.SoakInQty)  : 0;
                            balanceQty = parseFloat(balanceQty) +  parseFloat(SoakInQty) + parseFloat(excessQty);
                        }
                        if (balanceQty > 0) {
                            $("#balanceQtyMessage").text("(Balance Qty : " + (parseFloat(balanceQty) || 0).toFixed(3) + " KGs )").css("color", "green");
                        } else {
                            $("#balanceQtyMessage").text("(Balance Qty : " + (parseFloat(balanceQty) || 0).toFixed(3) + " KGs )").css("color", "red");
                        }
                        $("#hdnbalanceQty").val(balanceQty);
                         $("#hdnbalanceQtyTot").val(balanceQty);
                    }
                    if (hdnBreakUpID == '') {
                        if (data.souCountData) {
                            $("#drpVariety").val(data.souCountData[0].VarietyID).trigger("change");
                            $("#drpGrade").val(data.souCountData[0].PackingGrade).trigger("change");
                            $("#drpSoakingTreatment").val(data.souCountData[0].Treatment).trigger("change");
                        }
                    }
                    if (data.htmlRes) {
                        $("#divDataList").html(data.htmlRes);
                        fnShowDataTable();
                    } else {
                        $("#divDataList").html("");
                    }

                    return false;

                }
            });
        } else {
            $("#drpVariety").val("").trigger("change");
            $("#drpGrade").val("").trigger("change");
            $("#drpSoakingTreatment").val("").trigger("change");
            $("#balanceQtyMessage").text("");
            $("#hdnbalanceQty").val("");
              $("#hdnbalanceQtyTot").val("");
        }
    };

    $(document).ready(function () {
        var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram
        var addExtraQty = <?php echo $addExtraQty ?>; // sriram

        var validationRules = {
            drpTrackCode: {
                required: true,
                selectcheck: true
            },
            txtSoakingDate: {
                required: true,
                selectcheck: true
            },
            txtStartTime: {
                required: true,
                noSpace: true
            },
            drpTimePeriod: {
                required: true,
                selectcheck: true
            },
            drpSoakingTreatment: {
                required: true,
                selectcheck: true
            },
            drpChemical: {
                required: true,
                selectcheck: true
            },
            txtChemicalQuantity: {
                required: true,
                noSpace: true
            },
            txtSaltKg: {
                required: true,
                selectcheck: true
            },
            drpVariety: {
                required: true,
                selectcheck: true
            },
            drpGrade: {
                required: true,
                selectcheck: true
            },
            txtTubNumber: {
                required: true,
                noSpace: true
            },
            drpInCount: {
                required: true,
                selectcheck: true
            },
            txtInQuantity: {
                required: true,
                noSpace: true
            },
            txtOutCount: {
                required: true,
                noSpace: true
            },
            txtInCount: {
                required: true,
                noSpace: true
            },
        };
        if (IsPlantFilter != 0) {
            validationRules.drpPlant = { required: true };
        }
        $("#frmSoaking").validate({
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
                return false;
            },
            submitHandler: function () {

                $.ajax({
                    url: 'Ajax/ajax-soaking-trackID.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        breakUpID: $("#submit").attr("data-breakupid"),
                        arrivalDate: $("#txtArrivalDate").val(),
                        trackID: $("#drpTrackCode").val(),
                        plant: $("#drpPlant").val(),
                        soakingDate: $("#txtSoakingDate").val(),
                        startTime: $("#txtStartTime").val(),
                        timePeriod: $("#drpTimePeriod").val(),
                        chemical: $("#drpChemical").val(),
                        soakingTreatment: $("#drpSoakingTreatment").val(),
                        chemicalQuantity: $("#txtChemicalQuantity").val(),
                        saltKg: $("#txtSaltKg").val(),
                        remarks: $("#txtRemarks").val(),
                        variety: $("#drpVariety").val(),
                        grade: $("#drpGrade").val(),
                        lineList: $("#drpLineList").val(),
                        tubNumber: $("#txtTubNumber").val(),
                        inCount: $("#drpInCount").val(),
                        inQuantity: $("#txtInQuantity").val(),
                        outCount: $("#txtOutCount").val(),

                        txtIceQty:$("#txtIceQty").val(),
                        txtWaterQty:$("#txtWaterQty").val(),
                        drpShift:$("#drpShift").val(),
                        txtSoakingInTemp:$("#txtSoakingInTemp").val(),
                        txtSoakingOutTemp:$("#txtSoakingOutTemp").val(),
                         inCounts:$("#txtInCount").val(),
                        

                        
                        // outQty: $("#txtOutQty").val(),
                        vabreakUpid: $("#drpInCount").find(':selected').attr('data-vabreakUpid'),
                        receivalbreakupid: $("#drpInCount").find(':selected').attr('data-receivalbreakupid'),
                        arrivalid: $("#drpInCount").find(':selected').attr('data-arrivalid'),
                        IsPlantFilter: IsPlantFilter,
                        vlid: $("#drpInCount").find(':selected').attr('data-vlid'),
                        addExtraQty: addExtraQty,
                        ExcessQuantity: ($("#txtExtraQty").length && $("#txtExtraQty").val().trim()) || 0,
                    },
                    success: function (data) {

                        console.log(data);

                        if (data.Error) {
                            alert(data.Error);
                            // $("#drpInCount").trigger("change");
                        } else {
                            alert(data.Msg);
                            $("#btnCancel").prop('disabled', true);
                            $("#submit").attr("data-breakupid", "").text("Submit");
                            $("#hdnBreakUpID").val("");

                            $("#drpVariety").val("").trigger("change");
                            $("#drpGrade").val("").trigger("change");
                            $("#drpLineList").val("").trigger("change");
                            $("#txtTubNumber").val("");

                            $('#drpInCount').select2('destroy');
                            $('#drpInCount').val("");
                            $('#drpInCount').select2();

                            $("#txtInQuantity").val("");
                            $("#hdnbalanceQty").val("");
                              $("#hdnbalanceQtyTot").val("");
                            $("#balanceQtyMessage").text("");
                            $("#hdnEditQty").val("");
                            $("#txtOutCount").val("");
                            $("#drpInCount").prop("disabled", false);
                            $("#drpTrackCode").trigger("change");
                            // $("#txtOutQty").val("");
                            $("#txtInCount").val("");

                            $("#txtIceQty, #txtWaterQty, #txtSoakingInTemp, #txtSoakingOutTemp").val("");
                            $('#drpShift').select2('destroy');
                            $('#drpShift').val("");
                            $('#drpShift').select2();
                        }

                        if (data.htmlRes) {
                            $("#divDataList").html(data.htmlRes);
                            fnShowDataTable();
                        } else {
                            $("#divDataList").html("");
                        }

                        return false;
                    }
                });
            }
        });
    });

    $(document).on("click", ".btnEdit", function () {

        var addExtraQty = <?php echo $addExtraQty ?>; // sriram

        $("#hdnBreakUpID").val("");
        var breakupid = $(this).attr("data-breakupid");
        $("#submit").attr("data-breakupid", breakupid).text("Update");

        var pId = $.trim($(this).closest("tr").find(".tdPlantID").attr("data-plantid"));
        var soakingDate = $.trim($(this).closest("tr").find(".tdTransactionDate").attr("data-soakdate"));
        var startTime = $.trim($(this).closest("tr").find(".tdSoakInTime").text());
        var timePeriod = $.trim($(this).closest("tr").find(".tdTimePeriod").text());
        var chemicalId = $.trim($(this).closest("tr").find(".tdChemical").attr("data-chemicalid"));
        var chemicalQuantity = $.trim($(this).closest("tr").find(".tdChemicalQty").text());
        var Treatment = $.trim($(this).closest("tr").find(".tdSoakingTreatment").text());
        var saltKGs = $.trim($(this).closest("tr").find(".tdSaltQty").text());
        var Remarks = $.trim($(this).closest("tr").find(".tdRemarks").text());
        var variety = $.trim($(this).closest("tr").find(".tdVarietyID").attr("data-varietyid"));
        var grade = $.trim($(this).closest("tr").find(".tdGrade").text());
        var tubNumber = $.trim($(this).closest("tr").find(".tdTubNumber").attr("data-tubnumber"));
        var inCount = $.trim($(this).closest("tr").find(".tdSoakInCount").text());
        var inQuantity = $.trim($(this).closest("tr").find(".tdSoakInQty").text());
        var outCount = $.trim($(this).closest("tr").find(".tdSoakInQty #hdnOutCount").val());
        // var outCount = $.trim($(this).closest("tr").find(".tdSoakOutCount").text());
        var outQuantity = $.trim($(this).closest("tr").find(".tdSoakOutQty").text());
        var lineList = $.trim($(this).closest("tr").find(".tdLineList").text());

        var iceQty = $.trim($(this).closest("tr").find(".tdIceQtyPercentage").text());
        var waterQty = $.trim($(this).closest("tr").find(".tdWaterQtyPercentage").text());
        var soakInTemp = $.trim($(this).closest("tr").find(".tdSoakInTemperature").text());
        var soakOutTemp = $.trim($(this).closest("tr").find(".tdSoakOutTemperature").text());
        var shiftid = $.trim($(this).closest("tr").find(".tdShift").attr("data-shiftid"));
        var excessQuantity = $.trim($(this).closest("tr").find(".tdExcessQuantity").text());
          var inCounts = $.trim($(this).closest("tr").find(".tdIncount").text());
        var breakupid = $.trim($(this).attr("data-breakupid"));

        if(addExtraQty == 1){
             $("#txtExtraQty").val(excessQuantity);
        }

        $("#hdnBreakUpID").val(breakupid);

        $("#drpLineList").val(lineList).trigger("change");
        
        $('#drpPlant').select2('destroy');
        $("#drpPlant").val(pId);
        $('#drpPlant').select2();
        $("#drpPlant").prop("disabled", true);

        $("#txtSoakingDate").val(soakingDate);
        $("#txtStartTime").val(startTime);
        $("#drpTimePeriod").val(timePeriod).trigger("change");
        $("#drpSoakingTreatment").val(Treatment).trigger("change");
        $("#drpChemical").val(chemicalId).trigger("change");
        $("#txtChemicalQuantity").val(chemicalQuantity);

        $("#txtSaltKg").val(saltKGs);
        $("#txtRemarks").val(Remarks);

        $("#txtTubNumber").val(tubNumber);

        var $option = $("#drpInCount option[value='" + inCount + "']");
        if ($option.prop("disabled")) {
            $option.prop("disabled", false);
            $("#drpInCount").prop("disabled", true);
        }
        $("#drpInCount").val(inCount).trigger("change");
        $('#txtInCount').val(inCounts);
        $("#drpVariety").val(variety).trigger("change");
        $("#drpGrade").val(grade).trigger("change");
        
        $("#txtInQuantity").val(inQuantity);
        $("#hdnEditQty").val(inQuantity);
        $("#txtOutCount").val(outCount);

        $("#txtIceQty").val(iceQty);
        $("#txtWaterQty").val(waterQty);
        $("#txtSoakingInTemp").val(soakInTemp);
        $("#txtSoakingOutTemp").val(soakOutTemp);
        $("#drpShift").val(shiftid).trigger("change");
        // $("#txtOutQty").val(outQuantity);

        $("#submit").attr("data-breakupid", breakupid).text("Update");
        $("#btnCancel").prop('disabled', false);
        // $("#hdnBreakUpID").val(breakupid);

        $("html, body").animate({
            scrollTop: 0
        });
    });

    $(document).on("click", "#btnCancel", function () {
        $("#submit").attr("data-breakupid", "").text("Save");
        $(this).prop('disabled', true);

        $("#drpVariety").val("").trigger("change");
        $("#drpGrade").val("").trigger("change");
        $("#txtTubNumber").val("");

        $('#drpInCount').select2('destroy');
        $('#drpInCount').val("");
        $('#drpInCount').select2();

        $("#txtInQuantity").val("");
        $("#hdnEditQty").val("");
        $("#txtOutCount").val("");
        $("#hdnbalanceQty").val("");
          $("#hdnbalanceQtyTot").val("");
        $("#balanceQtyMessage").text("");
        $("#drpInCount").prop("disabled", false);
        $("#drpTrackCode").trigger("change");
        $("#txtInCount").val("");

        $("#txtIceQty, #txtWaterQty, #txtSoakingInTemp, #txtSoakingOutTemp").val("");
        $('#drpShift').select2('destroy');
        $('#drpShift').val("");
        $('#drpShift').select2();
    });


    $(document).on("click", ".btnDelete", function () {
        var addExtraQty = <?php echo $addExtraQty ?>; // sriram
        if (confirm("Are you sure you want to delete this item ?") == true) {
            $.ajax({
                url: 'Ajax/ajax-soaking-trackID.php',
                type: 'POST',
                data: {
                    methodType: "delete",
                    arrivalid: $.trim($(this).attr("data-arrivalid")),
                    trackid: $("#drpTrackCode").val(),
                    breakupid: $.trim($(this).attr("data-breakupid")),
                    plantID: $('#drpPlant').val(),
                    addExtraQty: addExtraQty,
                },
                success: function (data) {
                    console.log(data);
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);

                        if (data.soakingData) {
                            // if (data.soakingData.PlantID) {
                            //     $("#drpPlant").val(data.soakingData.PlantID).trigger("change");
                            // } else {
                            //     var plantid = '<?php echo $ProductionPlant ?>';
                            //     $('#drpPlant').select2('destroy');
                            //     $("#drpPlant").val(plantid);
                            //     $('#drpPlant').select2();
                            // }

                            var soakDateParts = data.soakingData.TransactionDate.split('-');
                            var soakDate = soakDateParts[2] + '-' + soakDateParts[1] + '-' + soakDateParts[0];

                            $("#txtSoakingDate").val(soakDate);
                            $("#txtStartTime").val(data.soakingData.SoakInTime);
                            $("#drpTimePeriod").val(data.soakingData.TimePeriod).trigger("change");
                            $("#drpSoakingTreatment").val(data.soakingData.Treatment).trigger("change");
                            $("#drpChemical").val(data.soakingData.ChemicalID).trigger("change");
                            $("#txtChemicalQuantity").val(data.soakingData.ChemicalQty);
                            $("#txtSaltKg").val(data.soakingData.SaltQty);
                            $("#txtRemarks").val(data.soakingData.Remarks);

                            $("#txtHLWt, #txtCrates").val("");
                        } else {

                            // var plantid = '<?php echo $ProductionPlant ?>';
                            // $('#drpPlant').select2('destroy');
                            // $("#drpPlant").val(plantid);
                            // $('#drpPlant').select2();

                            var startTime = "<?php echo date("H:i") ?>";
                            console.log(startTime);
                            $("#txtStartTime").val(startTime);
                            $("#drpTimePeriod").val("").trigger("change");
                            $("#drpSoakingTreatment").val("").trigger("change");
                            $("#drpChemical").val("").trigger("change");
                            $("#txtChemicalQuantity").val("");
                            $("#txtChemicalQuantity").val("");
                            $("#txtSaltKg").val('');
                            $("#txtRemarks").val("");

                            $("#drpVariety").val("").trigger("change");
                            $("#drpGrade").val("").trigger("change");
                            $("#txtTubNumber").val("");

                            $('#drpInCount').select2('destroy');
                            $('#drpInCount').val("");
                            $('#drpInCount').select2();

                            $("#txtInQuantity").val("");
                             $("#txtInCount").val("");
                            $("#hdnEditQty").val("");
                            $("#txtOutCount").val("");
                            $("#hdnbalanceQty").val("");
                              $("#hdnbalanceQtyTot").val("");
                            $("#balanceQtyMessage").text("");

                            $("#txtIceQty, #txtWaterQty, #txtSoakingInTemp, #txtSoakingOutTemp").val("");
                            $('#drpShift').select2('destroy');
                            $('#drpShift').val("");
                            $('#drpShift').select2();
                            
                        }
                        if (data.htmlRes) {
                            $("#divDataList").html(data.htmlRes);
                            fnShowDataTable();
                        } else {
                            $("#divDataList").html("");
                        }
                        $("#submit").attr("data-breakupid", "").text("Save");

                        return false;
                    }
                }
            });
        }

    });

    $("#txtInQuantity").on("keyup", function () {
        var soakInQty = parseFloat($(this).val());
        var balanceQty = parseFloat($("#hdnbalanceQty").val());
        var breakUpID = $("#hdnBreakUpID").val();
        var editQty = parseFloat($("#hdnEditQty").val());
        var addExtraQty = <?php echo $addExtraQty ?>; // sriram
        var excessQty = 0;
        if(addExtraQty == 1){
            excessQty = ($("#txtExtraQty").length && $.trim($("#txtExtraQty").val()) !== "") ? parseFloat($("#txtExtraQty").val()) || 0 : 0;
        }

        if (breakUpID != '') {
            var totalQty = Number(balanceQty) + Number(excessQty);
            if (soakInQty > totalQty) {
                alert("SoakingIn Quantity not exceeds to Balance Quantity");
                $(this).val(editQty);
            }
        } else {
            var totalQty = Number(balanceQty) + Number(excessQty);
            if (soakInQty > totalQty) {
                alert("SoakingIn Quantity not exceeds to Balance Quantity");
                $(this).val("");
            }
        }

    });
    $("#txtExtraQty").on("keyup", function () {
        var balanceQty = parseFloat($("#hdnbalanceQty").val());
        var excessQty =  ($("#txtExtraQty").length && $.trim($("#txtExtraQty").val()) !== "") ? parseFloat($("#txtExtraQty").val()) || 0 : 0;
        var totalQty = Number(balanceQty) + Number(excessQty);
        
        if (totalQty > 0) {
            $("#balanceQtyMessage").text("(Balance Qty : " + (parseFloat(totalQty) || 0).toFixed(3) + " KGs )").css("color", "green");
        } else {
            $("#balanceQtyMessage").text("(Balance Qty : " + (parseFloat(totalQty) || 0).toFixed(3) + " KGs )").css("color", "red");
        }
        // $("#hdnbalanceQty").val(totalQty);
            

    });
    <?php 
    if(!empty($LoadingLinersBasedOnPlant)){?>
        $(document).on("change", "#drpPlant", function () {
            if($(this).val()){drpLineList
                $.ajax({
                    url: 'Ajax/ajax-soaking-report.php',
                    type: 'POST',
                    data: {
                        methodType: "getLinersByPlant",
                        plantID: $(this).val(),
                    },
                    dataType: 'json',
                    success: function (data) {
                        var list = "<option value=''>Select</option>";
                        if (data.Data) {
                            var selectedValue = $("#drpLineList").val();
                            $.each(data.Data, function (index, value) {
                                var selected = (selectedValue == value.Name) ? "selected" : "";
                                list += "<option value='" + value.Name + "' "+selected+">" + value.Name + "</option>";
                            });
                        }
                        $('#drpLineList').html(list)
                    }
                });
            }
        })
    <?php } ?>
</script>

</body>

</html>