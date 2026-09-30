<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ArrivalID = isset($_GET['ArrivalID']) && !empty($_GET['ArrivalID']) ? $_GET['ArrivalID'] : "";
$AgentComissionTwoPercent = $_SESSION['ClientConfigData']['AgentComissionTwoPercent'];

$agent_commision_mandatory = $_SESSION['ClientConfigData']['agent_commision_mandatory'];
$arrival_count_notallow_decimals = $_SESSION['ClientConfigData']['arrival_count_notallow_decimals'];
$api = API_RM_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", ["pr_page_name"=>"createarrival"]);

// var_dump($resDropdownsAPI);exit;

$resSpecies = $resDropdownsAPI->Data->Species;
$resPurchaseCenters = $resDropdownsAPI->Data->PurchaseCenters; // Purchase Stations / Purchase centers 
$resPurchaseCategory = $resDropdownsAPI->Data->PurchaseCategory;
$resVehicles = $resDropdownsAPI->Data->Vehicles;
$resRmAgents = $resDropdownsAPI->Data->RmAgents;
$resDrivers = $resDropdownsAPI->Data->Drivers;
$resRmGraders = $resDropdownsAPI->Data->RmGraders;
$resRmSupervisors = $resDropdownsAPI->Data->RmSupervisors;
$resRmSuppliers = $resDropdownsAPI->Data->RmSuppliers;
$resRmVariety = $resDropdownsAPI->Data->RmVariety;
$resProcessingStations = $resDropdownsAPI->Data->ProcessingStations;
$BillAndArrivalDiff = $resDropdownsAPI->Data->BillAndArrivalDiff;

$DefBillDate = date("Y-m-d", strtotime("-$BillAndArrivalDiff day"));
$DefArrivalDate = date("Y-m-d");

$api = API_RM_ARRIVAL;

$bodyContent = array(
    "ArrivalID" => $ArrivalID,

);
$result = CallAPI($api, "GET", "", $bodyContent);
$ShowAccess = $result->ShowAccess;

// echo json_encode($result);exit;


$api = API_RM_OVER_ALL_ARRIVAL;

$ArrivalID = isset($_GET['OverallArrivalID']) && !empty($_GET['OverallArrivalID']) ? $_GET['OverallArrivalID'] : "";  //trim($_GET['OverallArrivalID']);
$ArrivalDate = isset($_GET['ArrivalDate']) && !empty($_GET['ArrivalDate']) ? $_GET['ArrivalDate'] : ""; // trim($_GET['ArrivalDate']);
$bodyContent = array(
    "ArrivalID" => $ArrivalID,
    "ArrivalDate" => $ArrivalDate
);

// if ($ArrivalID != '') {
//     $result = CallAPI($api, "GET", "", $bodyContent);
//     $Data = $result->OverArrivalData;
// }


if ($ArrivalID != '') {

    $result = CallAPI($api, "GET", "", $bodyContent);

    $Data = $result->OverArrivalData;
    $OverallArrivalDate = $Data->ArrivalDate;
    $DefBillDate = date("Y-m-d", strtotime("-$BillAndArrivalDiff day", strtotime($OverallArrivalDate)));

    // echo json_encode($Data,JSON_PRETTY_PRINT);exit;

}

$PlantID = $_SESSION['UnitID'];
$AccessType = $_SESSION['AccessType'];
$ClientConfigData = (object) $_SESSION['ClientConfigData'];

$showSaveButton = $ClientConfigData->ShowNegativeValueforBalanceWeight;  // uncomment this line after auth-login.php converted to API modal
// $showSaveButton = $ClientConfigData['ShowNegativeValueforBalanceWeight']; // remove this line after auth-login.php converted to API modal

$ArrivalAutoGeneration  = $ClientConfigData->ArrivalAutoGeneration; // comment this file moved to live
// $ArrivalAutoGeneration  = $ClientConfigData['ArrivalAutoGeneration']; // uncomment this line, file moved to live

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
                                <form id="frmCreateArrivals">
                                    <div class="row">

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpSpecies" class="form-label">Species <span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpSpecies" id="drpSpecies">
                                                <option value="" <?php echo ($Data->Species == '') ? 'selected' : '';  ?>> Select</option>
                                                <?php foreach ($resSpecies as $Item) { ?>
                                                    <option value="<?php echo $Item->ShortCode; ?>" <?php echo ($Data->Species == $Item->ShortCode) ? 'selected' : ''; ?>>
                                                        <?php echo $Item->SpeciesName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpArrivalStationId" class="form-label">Center Name<span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpArrivalStationId" id="drpArrivalStationId">
                                                <option value="" <?php echo ($Data->Center == '') ? 'selected' : '';  ?>> Select</option>
                                                <?php foreach ($resPurchaseCenters as $Item) { ?>
                                                    <option value="<?php echo $Item->PurchaseStationID; ?>" <?php echo ($Data->CenterID == $Item->PurchaseStationID) ? 'selected' : ''; ?>>
                                                        <?php echo $Item->StationName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpProcessingStations" class="form-label">Arrival Plant<span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpProcessingStations" id="drpProcessingStations">
                                                <option value="" <?php echo ($Data->StationName == '') ? 'selected' : '';  ?>> Select</option>
                                                <?php foreach ($resProcessingStations as $Item) { ?>
                                                    <option value="<?php echo $Item->ProcessingStationID ?>" <?php echo ($Data->PlantID == $Item->ProcessingStationID) ? 'selected' : ''; ?>>
                                                        <?php echo $Item->StationName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBillDate" class="form-label">Bill Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtBillDate" name="txtBillDate" value="<?php echo $DefBillDate; ?>" max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Arrival Date <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtArrivalDate" name="txtArrivalDate" placeholder="Enter Arrival Date" <?php echo 'value="' . (!empty($Data->ArrivalDate) ? $Data->ArrivalDate : $DefArrivalDate) . '"'; ?> max="<?php echo date('Y-m-d',strtotime('+1 day')); ?>">
                                        </div>

                                        <?php if ($ArrivalAutoGeneration == 0) { ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtAutoArrivalRefNo" class="form-label">Bill Number - <b><span id="spanBillNumber" style=" text-transform: uppercase;"></span><span id="spanNumber" style=" text-transform: uppercase;"></span></b> <span style="color:red">*</span></label>
                                                <input type="text" maxlength="8" class="form-control" id="txtAutoArrivalRefNo" name="txtAutoArrivalRefNo" placeholder="Enter Arrival Number" style=" text-transform: uppercase;" />
                                                <input type="hidden" class="form-control" id="hdnArrivalNumber" name="hdnArrivalNumber" style=" text-transform: uppercase;" />
                                            </div>
                                        <?php } else { ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtAutoArrivalRefNo" class="form-label">Bill Number - <b><span id="spanBillNumber" style=" text-transform: uppercase;"></span><span id="spanNumber" style=" text-transform: uppercase;"></span></b> <span style="color:red">*</span></label>
                                                <input type="number" maxlength="4" class="form-control" id="txtAutoArrivalRefNo" name="txtAutoArrivalRefNo" placeholder="Enter Arrival Number" style=" text-transform: uppercase;" />
                                                <input type="hidden" class="form-control" id="hdnArrivalNumber" name="hdnArrivalNumber" style=" text-transform: uppercase;" />
                                            </div>
                                        <?php } ?>

                                        <div class="form-group col-sm-3">
                                            <label for="drpSuppliers" class="form-label">Supplier Name <span style="color:red">*</span> <button type="button" class="btn btn-success py-0" id="newSupplier" data-bs-toggle="modal" data-bs-target="#modalCreateSupplier">New </button></label>
                                            <select class="drpSelect" name="drpSuppliers" id="drpSuppliers">
                                                <option value=""> Select</option>
                                                <?php foreach ($resRmSuppliers as $Item) { ?>
                                                    <option value="<?php echo $Item->SupplierID; ?>">
                                                        <?php echo $Item->FinancierName . "-" . $Item->PanNO; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpAgentName" class="form-label">Agent Name <span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpAgentName" id="drpAgentName">
                                                <option value=""> Select</option>
                                                <option value="0"> Others</option>
                                                <?php foreach ($resRmAgents as $Item) { ?>
                                                    <option value="<?php echo $Item->UserID; ?>">
                                                        <?php echo $Item->UserName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpPurchaseType" class="form-label">Purchase Type <span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpPurchaseType" id="drpPurchaseType">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPurchaseCategory as $Item) { ?>
                                                    <option value="<?php echo $Item->RmCategoryID; ?>">
                                                        <?php echo $Item->RmCategoryName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtFarmLocation" class="form-label">Farm Location <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtFarmLocation" name="txtFarmLocation" placeholder="Enter Farm Location" />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpGraderName" class="form-label">Grader Name <span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpGraderName" id="drpGraderName">
                                                <option value=""> Select</option>
                                                <option value="0"> Others</option>
                                                <?php foreach ($resRmGraders as $Item) { ?>
                                                    <option value="<?php echo $Item->UserID; ?>">
                                                        <?php echo $Item->UserName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <!-- <div class="form-group col-sm-3">
                                            <label for="drpSupervisorName" class="form-label">Supervisor Name <span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpSupervisorName" id="drpSupervisorName">
                                                <option value=""> Select</option>
                                                <option value="0"> Others</option>
                                                <option value="0"></option>
                                                <?php foreach ($resRmSupervisors as $Item) { ?>
                                                    <option value="<?php echo $Item->UserID; ?>">
                                                        <?php echo $Item->UserName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div> -->
                                        <div class="form-group col-sm-3">
                                            <label for="drpSupervisorName" class="form-label">Supervisor Name <span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpSupervisorName" id="drpSupervisorName">
                                                <option value="">Select</option>
                                                <option value="0">Others</option>
                                                <?php foreach ($resRmSupervisors as $Item) { ?>
                                                    <option value="<?php echo $Item->UserID; ?>"><?php echo $Item->UserName; ?></option>
                                                <?php } ?>
                                            </select>

                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpDriverName" class="form-label">Driver Name <span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpDriverName" id="drpDriverName">
                                                <option value=""> Select</option>
                                                <option value="0"> Others</option>
                                                <?php foreach ($resDrivers as $Item) { ?>
                                                    <option value="<?php echo $Item->UserID; ?>">
                                                        <?php echo $Item->UserName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpVehicleNumber" class="form-label">Vehicle Number <span style="color:red">*</span></label>

                                            <select class="drpSelect" name="drpVehicleNumber" id="drpVehicleNumber">
                                                <option value=""> Select</option>
                                                <option value="0"> Others</option>
                                                <?php foreach ($resVehicles as $Item) { ?>
                                                    <option value="<?php echo $Item->VehicalID; ?>">
                                                        <?php echo $Item->VehicalNumber; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtHarvestCommission" class="form-label">Harvest Commission
                                                <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtHarvestCommission" name="txtHarvestCommission" placeholder="Enter Harvest Commission" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50" id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                        </div>
                                        <div>&nbsp;</div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTotalWeight" class="form-label">Total Weight</label>
                                            <input name="txtTotalWeight" class="form-control" rows="1" cols="50" id="txtTotalWeight" placeholder="Enter Total Weight" value="<?php echo $Data->TotalWeight ?>" readonly />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBalanceWeight" class="form-label">Balance Weight</label>
                                            <input name="txtBalanceWeight" class="form-control" rows="1" cols="50" id="txtBalanceWeight" placeholder="Enter Balance Weight" value='<?php echo $Data->BalanceWeight ?>' readonly />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTotalValue" class="form-label">Total Value</label>
                                            <input name="txtTotalValue" class="form-control" rows="1" cols="50" id="txtTotalValue" placeholder="Enter Total Value" value='<?php echo $Data->TotalAmount ?>' readonly />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBalanceValue" class="form-label">Balance Value</label>
                                            <input name="txtBalanceValue" class="form-control" rows="1" cols="50" id="txtBalanceValue" placeholder="Enter Balance Value" value='<?php echo $Data->AvgRate ?>' readonly />
                                        </div>

                                    </div>

                                    <hr>

                                    <div class="row">
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpVariety" class="form-label">Variety</label>
                                            <select class="drpSelect" name="drpVariety" id="drpVariety">
                                                <option value=""> Select</option>
                                                <?php foreach ($resRmVariety as $Item) { ?>
                                                    <option value="<?php echo $Item->VarietyName; ?>" data-value="<?php echo $Item->VarietyID; ?>">
                                                        <?php echo $Item->VarietyName; ?></option>
                                                <?php  } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalCount" class="form-label">Arrival Count </label>
                                            <?php if($arrival_count_notallow_decimals){ ?>
                                            <input type="number"
                                               step="0.5"
                                               min="0"
                                               class="form-control"
                                               id="txtArrivalCount"
                                               name="txtArrivalCount"
                                               placeholder="Enter Arrival Count"
                                               onkeydown="return event.key !== 'e' && event.key !== 'E' && event.key !== '+' && event.key !== '-'"
                                               oninput="validateCount(this)">
                                           <?php }else{ ?>
                                                <input type="number" step="any" class="form-control" id="txtArrivalCount" name="txtArrivalCount" placeholder="Enter Arrival Count">
                                            <?php } ?>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalWeight" class="form-label">Arrival Weight(Kgs)
                                            </label>
                                            <input type="text" class="form-control" id="txtArrivalWeight" name="txtArrivalWeight" placeholder=" Enter Arrival Weight(Kgs)">
                                            <input type="hidden" class="hdnArrivalWeight" id="hdnArrivalWeight" value="" />
                                        </div>
                                        <div hidden class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpMasterRates" class="form-label">Master Rates</label>
                                            <select class="drpSelect" name="drpMasterRates" id="drpMasterRates">
                                                <option value=""> Select</option>
                                                <?php
                                                $MasterRate = "270/260";
                                                $MasterList = explode("/", $MasterRate);

                                                foreach ($MasterList as $Item) { ?>
                                                    <option value="<?php echo $Item ?>"> <?php echo $Item ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalRate" class="form-label">Arrival Rate</label>
                                            <input type="text" class="form-control" id="txtArrivalRate" name="txtArrivalRate" placeholder="Enter Arrival Rate">
                                            <input type="hidden" id="hdnRmRate" name="hdnRmRate" />
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $ArrivalID ?>" />
                                        <input type="hidden" id="hdnNewArrival" name="hdnNewArrival" />
                                        <input type="hidden" id="hdnBrekUpId" name="hdnBrekUpId" />
                                        <a href="rm-arrival-list" class="btn btn-danger">Close</a>

                                        <?php if ($ShowAccess == "1") { ?>
                                            <a href="javascript:void(0)" class="newItem btn btn-success waves-effect waves-light">
                                                <i class="fa fa-plus"></i> New Arrival
                                            </a>
                                            <button class="btn btn-primary" name="submit" id="submit" type="submit">Save</button>
                                        <?php } ?>

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
    var AgentComissionTwoPercent = "<?php echo $AgentComissionTwoPercent?$AgentComissionTwoPercent:''; ?>";
    var agent_commision_mandatory = "<?php echo $agent_commision_mandatory?$agent_commision_mandatory:''; ?>";
    var arrivalID = "<?php echo isset($_GET["ArrivalID"]) && !empty($_GET["ArrivalID"]) ? $_GET["ArrivalID"] : "" ?>";
    var overallArrivalID = "<?php echo isset($_GET["OverallArrivalID"]) && !empty($_GET["OverallArrivalID"]) ? $_GET["OverallArrivalID"] : "" ?>";
    var table;
    var arrivalAutoGeneration = "<?php echo $ArrivalAutoGeneration ?>";

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
                title: 'Day-Arrival-Report-<?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }




    $(document).ready(function() {
        var plantId = <?php echo $PlantID ?>;
        if(plantId == ''){
            plantId = $("#drpProcessingStations").val();
        }
        var accessType = <?php echo $AccessType ?>;

        if (accessType == 3) {
            if (plantId != 0) {
                $("#drpProcessingStations").prop('disabled', true);
                $("#drpProcessingStations").val(plantId).trigger('change');
            } else {
                $("#drpProcessingStations").prop('disabled', false);
                $("#drpProcessingStations").val("").trigger('change');
            }
            $("#newSupplier").prop('disabled', true);
        } else {
            $("#drpProcessingStations").prop('disabled', false);
            $("#drpProcessingStations").val(plantId).trigger('change');
            $("#newSupplier").prop('disabled', false);
        }
        <?php 
        if($_SESSION['ClientID'] == 'DFLV3'){?>
            if($('#drpPurchaseType').val() == ''){
                 $("#drpPurchaseType option")
                    .filter((i, el) => $(el).text().trim() === "Site Weightment")
                    .prop("selected", true);

                $("#drpPurchaseType").trigger("change");
            }
            if($('#drpVariety').val() == ''){
                $('#drpVariety').val('HEAD ON').trigger('change');
            }
        <?php }?>

    });

    $(document).ready(function() {

        var showSaveButton = '<?php echo $showSaveButton ?>';

        if (showSaveButton == 0) {

            $("#txtArrivalWeight").on("keyup", function() {
                var brkUpID = $("#hdnBrekUpId").val();
                var arrivalWeight = parseFloat($(this).val());
                var balanceWeight = parseFloat($.trim($("#txtBalanceWeight").val()));
                var hdnarrivalWeight = parseFloat($.trim($("#hdnArrivalWeight").val()));
                totalWeight = hdnarrivalWeight + balanceWeight;

                if (brkUpID != '') {
                    if (totalWeight < arrivalWeight) {
                        $(this).val("");
                        alert("Arrival Weight Should Less than Balance Weight");
                    }
                } else {
                    if (balanceWeight < arrivalWeight) {
                        $(this).val("");
                        alert("Arrival Weight Should Less than Balance Weight");
                    }
                }
            });
        }

        $("#txtArrivalWeight").on("keyup", function() {
            var arrivalWeight = parseFloat($(this).val());

            if (arrivalWeight == 0 || arrivalWeight == 0.00) {
                $(this).val("");
                alert("Arrival Weight Should not be zero");
            }
        });
    });

    function funFormChange() {

        var arrivalStationId = $("#drpArrivalStationId").val();
        var processingStations = $("#drpProcessingStations").val();
        console.log(processingStations);

        var arrivalDate = $("#txtArrivalDate").val();
        var species = $("#drpSpecies").val();

        if (arrivalStationId != "" && processingStations != "" && arrivalDate != "" && species != "") {

            var frmData = {
                CenterID: arrivalStationId,
                PlantID: processingStations,
                ArrivalDate: arrivalDate,
                Species: species,
            }
            var showSaveButton = <?php echo $showSaveButton ?>;

            $.ajax({
                url: 'Ajax/AjaxArrivalWeightValue.php',
                type: 'POST',
                data: frmData,
                success: function(data) {

                    if (data.Error === null) {
                        $("#txtTotalWeight").val(data.ArrivalData.OverAllWeight);
                        $("#txtBalanceWeight").val(data.ArrivalData.OverAllBalWeight);
                        $("#txtTotalValue").val(data.ArrivalData.OverAllAmount);
                        $("#txtBalanceValue").val(data.ArrivalData.OverAllBalAmount);

                        if (data.ArrivalData.OverAllBalWeight <= 0) {} else {
                            $("#txtArrivalCount").attr("readonly", false);
                            $("#txtArrivalWeight").attr("readonly", false);
                            $("#txtArrivalRate").attr("readonly", false);
                        }

                        if (data.ArrivalData.OverAllBalWeight < 0) {
                            if (showSaveButton == 0) {
                                $('#submit').prop('disabled', true);
                            } else {
                                $('#submit').prop('disabled', false);
                            }
                        }

                    } else {
                        alert(error);
                        return false;
                    }
                    return false;
                }
            });
        }
    }

    function padNumber(number, desiredLength) {
        const stringNumber = number.toString();
        return stringNumber.padStart(desiredLength, "0");
    }

    function bindFormData(resJson) {
        console.log(resJson);

        $("#txtArrivalDate").val(resJson.ArrivalDate);
        $("#drpSpecies").val(resJson.Species).trigger('change');
        $("#drpArrivalStationId").val(resJson.ArrivalStationID).trigger('change');
        $("#drpPurchaseType").val(resJson.PurchaseCategoryID).trigger('change');
        $("#drpProcessingStations").val(resJson.ProcessingStationID).trigger('change');

        var n = padNumber(resJson.AutoArrivalReferenceNo, 4);
        var newArrNo = resJson.StationCode + n;

        if (arrivalAutoGeneration == 0) {
            $("#hdnArrivalNumber").val(resJson.ArrivalNumber);
            $("#txtAutoArrivalRefNo").val(resJson.ArrivalNumber);
            $("#spanBillNumber").val(resJson.ArrivalNumber);
            $("#spanNumber").text(resJson.ArrivalNumber);
        } else {
            $("#hdnArrivalNumber").val(newArrNo);
            $("#txtAutoArrivalRefNo").val(resJson.AutoArrivalReferenceNo);
            $("#spanBillNumber").text(resJson.StationCode);
            $("#spanNumber").text(n);
        }

        $("#drpVehicleNumber").val(resJson.VehicalID).trigger('change');
        $("#drpDriverName").val(resJson.DriverID).trigger('change');
        $("#drpGraderName").val(resJson.GraderID).trigger('change');
         $("#drpSupervisorName").val(resJson.SupervisorID).trigger('change');

        $("#txtBillDate").val(resJson.BillDate);

        $("#drpAgentName").val(resJson.AgentID).trigger('change');
        $("#txtFarmLocation").val($.trim(resJson.FarmAddress));
        $("#drpSuppliers").val(resJson.RmSupplierID).trigger('change');
        $("#txtRemarks").val(resJson.Remarks);
        $("#txtHarvestCommission").val(resJson.HarvestCommission);

        $("#txtTotalWeight").val(resJson.OverAllWeight);
        $("#txtBalanceWeight").val(resJson.OverAllBalWeight);
        $("#txtTotalValue").val(resJson.OverAllAmount);
        $("#txtBalanceValue").val(resJson.OverAllBalAmount);
    }


    $("#txtAutoArrivalRefNo").on("keyup change click", function() {
        var autoRefNo = $(this).val();
        var n = padNumber(autoRefNo, 4);
        var newArrNo = $("#spanBillNumber").text() + n;

        $("#hdnArrivalNumber").val(newArrNo);
        $("#spanNumber").text(n);

    })

    function fnGetAutoBillNo() {
        $.ajax({
            url: 'Ajax/AjaxArrivalWeightValue.php',
            type: 'POST',
            data: {
                methodType: "autoBillNo",
                centerID: $("#drpArrivalStationId").val(),
                arrivalDate: $("#txtArrivalDate").val(),
                billDate: $("#txtBillDate").val(),
                ArrivalID: arrivalID,
            },
            success: function(data) {
                if (arrivalAutoGeneration != 0) {
                    if (data.ArrivalNumberData) {
                        $("#txtAutoArrivalRefNo").val(data.ArrivalNumberData.AutoArrivalNumber);
                        var no = padNumber(data.ArrivalNumberData.AutoArrivalNumber, 4);
                        $("#hdnArrivalNumber").val(data.ArrivalNumberData.LotCode + data.ArrivalNumberData.YearSeparator + no);
                        $("#spanBillNumber").text(data.ArrivalNumberData.LotCode + data.ArrivalNumberData.YearSeparator);
                        $("#spanNumber").text(no);
                    }
                } else {
                    $("#hdnArrivalNumber").val("");
                    $("#spanBillNumber").text("");
                    $("#spanNumber").text("");
                    $("#txtAutoArrivalRefNo").val("");
                }

                return false;
            }
        });
    }

    function formatDateToYYYYMMDD(date) {
        var year = date.getFullYear();
        var month = date.getMonth() + 1;
        var day = date.getDate();

        if (month < 10) {
            month = "0" + month;
        }
        if (day < 10) {
            day = "0" + day;
        }

        var formattedDate = year + "-" + month + "-" + day;

        return formattedDate;
    }

    function getOneDayLessDate(date) {
        var BillAndArrivalDiff = '<?php echo $BillAndArrivalDiff ?>';
        var newDate = new Date(date);
        newDate.setDate(newDate.getDate() - BillAndArrivalDiff);

        newDate = formatDateToYYYYMMDD(newDate);
        return newDate;
    }

    function removeQueryParameters(url) {
        const urlObject = new URL(url);
        urlObject.search = '';
        return urlObject.toString();
    }

    if (arrivalAutoGeneration != 0) {
        $("#drpArrivalStationId, #drpProcessingStations, #txtArrivalDate, #drpSpecies").on("change", function() {
            funFormChange();
        });
    }

    $(document).on("change", "#txtArrivalDate", function() {
        var billdate = getOneDayLessDate($(this).val());
        $("#txtBillDate").val(billdate);
    })

    $(document).on("click", ".newItem", function() {

        $("#txtAutoArrivalRefNo").val("");
        $("#drpSuppliers").val("").trigger('change');
        $("#txtHarvestCommission").val("0");
        $("#txtRemarks").val("");
        $("#txtArrivalCount").val("");
        $("#txtArrivalWeight").val("");
        $("#txtArrivalRate").val("");
        arrivalID = "";
        table.clear().draw();
        $("#divDataList").remove();

        $("#hdnNewArrival").val("NEW");

        var url = window.location.href;
        var newUrl = removeQueryParameters(url);
        window.history.replaceState(null, null, newUrl);

        $("#txtBillDate, #txtArrivalDate").attr("readonly", false);
        $("#drpSpecies, #drpArrivalStationId, #drpProcessingStations").prop("disabled", false);

        funFormChange();

        fnGetAutoBillNo();

        return false;
    })

    $(document).ready(function() {

        if (arrivalID != "") {

            $("#txtBillDate, #txtArrivalDate").attr("readonly", true);
            $("#drpSpecies, #drpArrivalStationId, #drpProcessingStations").prop("disabled", true);

            var frmData = {
                methodType: "get",
                ArrivalID: arrivalID,
            }
            $.ajax({
                url: 'Ajax/AjaxArrival.php',
                type: 'POST',
                data: frmData,
                success: function(data) {
                    console.log(data);
                    var data = JSON.parse(data);

                    console.log(data);
                    var htmlData = data.html;
                    var resJson = data.json;
                    console.log(resJson)

                    $("#divDataList").html(htmlData);
                    fnShowDataTable();

                    if (arrivalID) {
                        bindFormData(resJson);
                    }
                    return false;
                }
            });
        }
        if (overallArrivalID != "") {
            $('#drpArrivalStationId').trigger('change');
            $(" #drpAgentName, #drpSuppliers, #drpGraderName,#drpSupervisorName, #drpDriverName, #drpVehicleNumber").val("0").trigger('change');
            // $(" #drpAgentName, #drpSuppliers, #drpGraderName, #drpSupervisorName, #drpDriverName, #drpVehicleNumber").val("0").trigger('change');
            $(" #drpSuppliers").val("").trigger('change');
            $("#txtAutoArrivalRefNo").val("");
            $("#txtHarvestCommission").val("0");
            $("#txtFarmLocation").val($.trim($('#drpArrivalStationId').select2('data')[0].text));

        }
    });

    $('#drpArrivalStationId').on('select2:select', function(e) {

        $(" #drpAgentName, #drpSuppliers, #drpGraderName, #drpDriverName,#drpSupervisorName, #drpVehicleNumber").val("0").trigger('change');
        // $(" #drpAgentName, #drpSuppliers, #drpGraderName, #drpSupervisorName, #drpDriverName, #drpVehicleNumber").val("0").trigger('change');
        $(" #drpSuppliers").val("").trigger('change');
        if (arrivalAutoGeneration != 0) {
            $("#txtAutoArrivalRefNo").val("");
        }
        $("#txtHarvestCommission").val("0");

        $("#txtFarmLocation").val($.trim($('#drpArrivalStationId').select2('data')[0].text));

        var currentUrl = window.location.href;
        var url = currentUrl.split('?')[0];

        window.history.pushState({}, '', url);

        if (table != null) {
            table.clear().draw();
            $("#divDataList").remove();
        }
        arrivalID = "";
    });

    $(document).on("click", ".deleteBrkUp", function() {
        var brkUpID = $(this).attr("data-arrivalbrkupid");

        var frmData = {
            methodType: "delete",
            ArrivalID: arrivalID,
            BreakUpID: brkUpID,
        }


        if (confirm("Are you sure you want to delete this item ?") == true) {
            $.ajax({
                url: 'Ajax/AjaxArrival.php',
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
                        if (resJson.AgentID) {
                            bindFormData(resJson);
                        } else {
                            window.location.href = "/v3/Purchase/rm-arrival-list";
                        }
                    }
                    return false;
                }
            });
        }
    });

    $(document).on("click", ".editBrkUp", function() {
        $("#submit").text("Update");
        var brkUpID = $(this).attr("data-arrivalbrkupid")
        var RecievalCount = parseInt($(this).attr("data-RecievalCount")) || 0

        $("#hdnBrekUpId").val(brkUpID);
        $("#drpVariety").val($(this).closest("tr").find(".tdVariety").text()).trigger("change");
        $("#txtArrivalCount").val($(this).closest("tr").find(".tdCount").text());
        $("#txtArrivalWeight").val($(this).closest("tr").find(".tdWeight").text());
        $("#hdnArrivalWeight").val($(this).closest("tr").find(".tdWeight").text());
        $("#txtArrivalRate").val($(this).closest("tr").find(".tdRate").text());
        console.log("RecievalCount ",RecievalCount)
        if (RecievalCount > 0) {
            $("#txtArrivalCount, #txtArrivalWeight").prop("readonly", true);
            $("#drpVariety").prop("disabled", true);
        } else {
            $("#txtArrivalCount, #txtArrivalWeight").prop("readonly", false);
            $("#drpVariety").prop("disabled", false);
        }

    });

    function updateHarvestCommissionReadonly() {
        var selectedBillDate = $("#txtBillDate").val();
        var selectedVal = $("#drpAgentName").val();

        if (selectedBillDate) {
            var billDate = new Date(selectedBillDate);
            var cutoffDate = new Date("2025-08-01");

            if (billDate >= cutoffDate) {
                if (selectedVal === "0") {
                    $("#txtHarvestCommission")
                        .val(0)
                        .attr("readonly", true);
                } else {
                    $("#txtHarvestCommission").attr("readonly", false);
                }
            } else {
                $("#txtHarvestCommission").attr("readonly", false);
            }
        }
    }

    if(AgentComissionTwoPercent){
        // Trigger when Agent dropdown changes
        $("#drpAgentName").on("change", function () {
            updateHarvestCommissionReadonly();
        });

        // Trigger when Bill Date changes
        $("#txtBillDate").on("change", function () {
            updateHarvestCommissionReadonly();
        });
    }

    if (arrivalAutoGeneration != 0) {
        $(document).on("change", "#drpArrivalStationId, #txtArrivalDate", function() {
            if (arrivalID == "") {
                fnGetAutoBillNo();
            }
        });
    }
    $("#frmCreateArrivals").validate({
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
            drpSpecies: {
                required: true,
                selectcheck: true
            },
            drpArrivalStationId: {
                required: true,
                selectcheck: true
            },
            drpPurchaseType: {
                required: true,
                selectcheck: true
            },
            drpProcessingStations: {
                required: true,
                selectcheck: true
            },
            txtAutoArrivalRefNo: {
                required: true,
                noSpace: true
            },
            txtHarvestCommission: {
                required: {
                    depends: function () {
                        return $("#drpAgentName").val() != "0" &&
                               $("#drpAgentName").val() != "" && agent_commision_mandatory ==1;
                    }
                },
                number: {
                    depends: function () {
                        return $("#drpAgentName").val() != "0" &&
                               $("#drpAgentName").val() != "" && agent_commision_mandatory ==1;
                    }
                },
                min: {
                    param: 0.01,
                    depends: function () {
                        return $("#drpAgentName").val() != "0" &&
                               $("#drpAgentName").val() != "" && agent_commision_mandatory ==1;
                    }
                }
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
            drpSupervisorName: {
                required: false,
                selectcheck: true
            },
            txtBillDate: {
                required: true,
                noSpace: true
            },
            txtArrivalDate: {
                required: true,
                noSpace: true
            },
            drpAgentName: {
                required: true,
                selectcheck: true
            },
            txtFarmLocation: {
                required: true,
                noSpace: true
            },
            drpSuppliers: {
                required: true,
                selectcheck: true
            },
            drpVariety: {
                required: true,
                selectcheck: true
            },
            txtArrivalCount: {
                required: true,
                noSpace: true
            },
            txtArrivalWeight: {
                required: true,
                noSpace: true
            },
            txtArrivalRate: {
                required: true,
                noSpace: true
            },
            drpVariety: {
                required: {
                    depends: function(element) {
                        return $("#txtArrivalCount").val().length > 0 || $("#txtArrivalWeight").val().length >
                            0 || $("#drpMasterRates").val().length > 0 || $("#txtArrivalRate").val().length > 0;
                    }
                },
                selectcheck: {
                    depends: function(element) {
                        return $("#txtArrivalCount").val().length > 0 || $("#txtArrivalWeight").val().length >
                            0 || $("#drpMasterRates").val().length > 0 || $("#txtArrivalRate").val().length > 0;
                    }
                }
            },
            txtArrivalCount: {
                required: {
                    depends: function(element) {
                        return $("#txtArrivalWeight").val().length > 0 || $("#drpMasterRates").val().length >
                            0 || $("#drpVariety").val().length > 0 || $("#txtArrivalRate").val().length > 0;
                    }
                }
            },
            txtArrivalWeight: {
                required: {
                    depends: function(element) {
                        return $("#txtArrivalCount").val().length > 0 || $("#drpMasterRates").val().length >
                            0 || $("#drpVariety").val().length > 0 || $("#txtArrivalRate").val().length > 0;
                    }
                }
            },
            txtArrivalRate: {
                required: {
                    depends: function(element) {
                        return $("#txtArrivalCount").val().length > 0 || $("#txtArrivalWeight").val().length >
                            0 || $("#drpVariety").val().length > 0 || $("#drpMasterRates").val().length > 0;
                    }
                },
                number: true
            }
        },
        errorPlacement: function() {
            return false; // suppresses error message text
        },
        submitHandler: function() {
            var arrivalBrkUpID;
            if ($("#hdnBrekUpId").val() != "") {
                arrivalBrkUpID = $("#hdnBrekUpId").val();
            } else {
                arrivalBrkUpID = "";
            }
            console.log("AjaxArrival ", {
                methodType: "post",
                ArrivalID: $("#hdnNewArrival").val() == "NEW" ? "" : arrivalID,
                ArrivalBrkUpID: arrivalBrkUpID,
                drpSpecies: $("#drpSpecies").val(),
                drpArrivalStationId: $("#drpArrivalStationId").val(),
                drpPurchaseType: $("#drpPurchaseType").val(),
                drpProcessingStations: $("#drpProcessingStations").val(),
                txtAutoArrivalRefNo: $("#txtAutoArrivalRefNo").val(),
                hdnArrivalNumber: $("#hdnArrivalNumber").val(),
                drpVehicleNumber: $("#drpVehicleNumber").val(),
                drpDriverName: $("#drpDriverName").val(),
                drpGraderName: $("#drpGraderName").val(),
                 drpSupervisorName: $("#drpSupervisorName").val(),
                txtBillDate: $("#txtBillDate").val(),
                txtArrivalDate: $("#txtArrivalDate").val(),
                drpAgentName: $("#drpAgentName").val(),
                txtFarmLocation: $("#txtFarmLocation").val(),
                drpSuppliers: $("#drpSuppliers").val(),
                txtRemarks: $("#txtRemarks").val(),
                txtHarvestCommission: $("#txtHarvestCommission").val(),
                drpVariety: $("#drpVariety").val(),
                txtArrivalCount: $("#txtArrivalCount").val(),
                txtArrivalWeight: $("#txtArrivalWeight").val(),
                drpMasterRates: $("#drpMasterRates").val(),
                txtArrivalRate: $("#txtArrivalRate").val(),
            })
            $.ajax({
                url: 'Ajax/AjaxArrival.php',
                type: 'POST',
                data: {
                    methodType: "post",
                    ArrivalID: $("#hdnNewArrival").val() == "NEW" ? "" : arrivalID,
                    ArrivalBrkUpID: arrivalBrkUpID,
                    drpSpecies: $("#drpSpecies").val(),
                    drpArrivalStationId: $("#drpArrivalStationId").val(),
                    drpPurchaseType: $("#drpPurchaseType").val(),
                    drpProcessingStations: $("#drpProcessingStations").val(),
                    txtAutoArrivalRefNo: $("#txtAutoArrivalRefNo").val(),
                    hdnArrivalNumber: $("#hdnArrivalNumber").val(),
                    drpVehicleNumber: $("#drpVehicleNumber").val(),
                    drpDriverName: $("#drpDriverName").val(),
                    drpGraderName: $("#drpGraderName").val(),
                    // drpSupervisorName: $("#drpSupervisorName").val(),
                    txtBillDate: $("#txtBillDate").val(),
                    txtArrivalDate: $("#txtArrivalDate").val(),
                    drpAgentName: $("#drpAgentName").val(),
                    txtFarmLocation: $("#txtFarmLocation").val(),
                    drpSuppliers: $("#drpSuppliers").val(),
                    txtRemarks: $("#txtRemarks").val(),
                    txtHarvestCommission: $("#txtHarvestCommission").val(),
                    drpVariety: $("#drpVariety").val(),
                    txtArrivalCount: $("#txtArrivalCount").val(),
                    txtArrivalWeight: $("#txtArrivalWeight").val(),
                    drpMasterRates: $("#drpMasterRates").val(),
                    txtArrivalRate: $("#txtArrivalRate").val(),
                },
                success: function(data) {
                    console.log("arr", data);
                    var data = JSON.parse(data);
                    var htmlData = data.html;
                    var resJson = data.json;
                    var error = data.Error;
                    var msg = data.Msg;

                    if (error === null) {
                        alert(msg);
                    } else {
                        alert(error);
                        return false;
                    }

                    if (arrivalID == "") {
                        if ($("#hdnNewArrival").val() == "NEW" || overallArrivalID != "") {
                            var newUrl = "/v3/Purchase/create-arrival" + "?ArrivalID=" + resJson.RmArrivalID;
                        } else {
                            var newUrl = window.location.href + "?ArrivalID=" + resJson.RmArrivalID;
                        }
                        window.location.href = newUrl;
                    }

                    $("#divDataList").html(htmlData);
                    fnShowDataTable();

                    if (arrivalID) {
                        bindFormData(resJson);
                    }
                    $("#drpVariety, #drpMasterRates").val("").trigger('change');
                    $("#txtArrivalCount, #txtArrivalWeight").val("");

                    $("#submit").text("Save");
                    $("#hdnBrekUpId").val("");

                    return false;
                }
            });
        }
    });
function validateCount(input) {
    const value = input.value;

    // Allow only whole numbers or numbers ending in .5
    if (value !== '' && !/^\d+(\.5)?$/.test(value)) {
        input.setCustomValidity("Only whole numbers or values ending in .5 are allowed.");
    } else {
        input.setCustomValidity("");
    }
}
</script>

</body>

</html>
