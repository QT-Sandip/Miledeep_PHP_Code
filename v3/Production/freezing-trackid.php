<?php
// session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;  //sriram
$IsHONPacking = $ClientConfigData->IsHONPacking;  //sriram
$IsDeductGlaze = $ClientConfigData->IsDeductGlaze;  //sriram
$IsDeductGlazeFW = $ClientConfigData->IsDeductGlazeFW;  //sriram
$allowExcessFreezing = $ClientConfigData->allowExcessFreezing;  //sriram
$MCSRestriction = $ClientConfigData->MCSRestriction;  //anil
$MixedBatchCode = $ClientConfigData->MixedBatchCode;
$EnableCodeListPrint = $ClientConfigData->EnableCodeListPrint;
$EnableMcsAndLooseAtFreezingTrackPage = $ClientConfigData->EnableMcsAndLooseAtFreezingTrackPage;
$LoadingLinersBasedOnPlant = $ClientConfigData->LoadingLinersBasedOnPlant;
$DisabledABStatus = $ClientConfigData->DisabledABStatus;
$DisabledPackingStyle = 0;
if($_SESSION['ClientID'] == 'DFLV3'){
    $DisabledPackingStyle = 1;
}




// $IsPlantFilter = $ClientConfigData['IsPlantFilter'];  //sriram
// $IsHONPacking = $ClientConfigData['IsHONPacking'];  //sriram
// $IsDeductGlaze = $ClientConfigData['IsDeductGlaze'];  //sriram
// $IsDeductGlazeFW = $ClientConfigData['IsDeductGlazeFW'];  //sriram
// $allowExcessFreezing = $ClientConfigData['allowExcessFreezing'];  //sriram

$Plant = $_SESSION['UnitID'];
$bodyContent = array(
    'page' => 'daily-production-list',
    'exclude_list' => array("StockAvailableVariety", "ShipoutPoNumber"),
     'UnitId' => $Plant,
    'LoadingLinersBasedOnPlant' => $LoadingLinersBasedOnPlant,
);
$api = API_COLDSTORE_DROPDOWNS;
$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resDropdownsAPI, JSON_PRETTY_PRINT);
// exit;

$resProductGroup = $resDropdownsAPI->Data->ProductGroup;
$resStockBelongsTo = $resDropdownsAPI->Data->StockBelongsTo;
$resColdStores = $resDropdownsAPI->Data->ColdStores;
$resPlant = $resDropdownsAPI->Data->Plant;
$resShifts = $resDropdownsAPI->Data->Shifts;
$resPoNumber = $resDropdownsAPI->Data->PoNumberFreezing;
$resVariety = $resDropdownsAPI->Data->Variety;
$resPackingType = $resDropdownsAPI->Data->PackingType;
$resPackingStyle = $resDropdownsAPI->Data->PackingStyle;
$resGrade = $resDropdownsAPI->Data->Grade;
$resFreezingType = $resDropdownsAPI->Data->FreezingType;
$resSoaking = $resDropdownsAPI->Data->Soaking;
$resProductCategory = $resDropdownsAPI->Data->ProductCategory;
$resAbstatus = $resDropdownsAPI->Data->Abstatus;

$resBlocks = $resDropdownsAPI->Data->Blocks;
$resFloors = $resDropdownsAPI->Data->Floors;
$resProductionLines = $resDropdownsAPI->Data->ProductionLines;
// $error = $resDropdownsAPI->Error;
$Plant = $_SESSION['UnitID'];

$StockOutID = isset($_GET["StockOutID"]) && !empty($_GET["StockOutID"]) ? $_GET["StockOutID"] : "";

// SESSION values
$ProductionPlant = $_SESSION['UnitID'];
$ClientID = $_SESSION['ClientID'];
?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . 'includes/head.php' ?>

    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"> -->

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
                                <form id="frmInward">
                                    <div class="row div_inward">

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpProductionPlant" class="form-label">Production Plant<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpProductionPlant"
                                                id="drpProductionPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPlant as $item) { ?>
                                                    <option value="<?php echo $item->ProcessingStationID; ?>">
                                                        <?php echo $item->StationName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Arrival Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtArrivalDate"
                                                name="txtArrivalDate" value="<?php echo date("Y-m-d") ?>"
                                                max="<?php echo date("Y-m-d") ?>" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpTrackCode" class="form-label">Track Code<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpTrackCode"
                                                id="drpTrackCode">
                                                <option value=""> Select</option>
                                            </select>
                                       
                                        </div>
                                 <?php if ($MixedBatchCode == 1) { ?>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtMixedBatchCode" class="form-label">Mixed Batch</label>
                                            <input name="txtMixedBatchCode" class="form-control"
                                                id="txtMixedBatchCode" placeholder="Enter Mixed Bacth" required></input>
                                        </div>
                                         <?php } ?>


                                          <?php if ($MixedBatchCode == 1) { ?>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtWeight" class="form-label">Weight</label>
                                            <input name="txtWeight" class="form-control"
                                                id="txtWeight" placeholder="Enter Weight"></input>
                                        </div> <?php } ?>
                                         
                                      
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtProductionDate" class="form-label">Production Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtProductionDate"
                                                name="txtProductionDate" value="<?php echo date("Y-m-d") ?>"
                                                max="<?php echo date("Y-m-d") ?>" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpTubNumber" class="form-label">Tub Number<span
                                                    style="color:red">*</span></label>
                                            <input type="hidden" id="hdnFreezingQty" name="hdnFreezingQty" value="" />
                                            <select class="drpSelect form-control dropOptColor" name="drpTubNumber"
                                                id="drpTubNumber">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpGroup form-control" class="form-label">Group<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpGroup" id="drpGroup"
                                                disabled>
                                                <option value=""> Select</option>
                                                <?php
                                                foreach ($resProductGroup as $item) {
                                                    if (!empty($StockOutID)) {
                                                        if ($item->Name == "REPACK")
                                                            echo '<option value="' . $item->Name . '" selected>' . $item->Name . '</option>';
                                                    } else {
                                                        if ($item->Name == "PRODUCTION")
                                                            echo '<option value="' . $item->Name . '" selected>' . $item->Name . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpShift" class="form-label">Shift<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpShift" id="drpShift">
                                                <option value=""> Select</option>
                                                <?php foreach ($resShifts as $item) { ?>
                                                    <option value="<?php echo $item->OptID; ?>">
                                                        <?php echo $item->Name; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtStartTimer" class="form-label">Start Timer </label>
                                            <input type="time" class="form-control" id="txtStartTimer"
                                                name="txtStartTimer" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtEndTimer" class="form-label">End Timer </label>
                                            <input type="time" class="form-control" id="txtEndTimer"
                                                name="txtEndTimer" />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpPoNumber" class="form-label">Po Number</label>
                                            <select class="drpSelect form-select" name="drpPoNumber" id="drpPoNumber">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPoNumber as $item) { ?>
                                                    <option value="<?php echo $item->PoID; ?>">
                                                        <?php echo $item->PoNo . " - " . $item->LotNo; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtVariety" class="form-label">Variety <span
                                                    style="color:red">*</span></label>
                                            <input type="input" class="form-control" id="txtVariety" name="txtVariety"
                                                placeholder="Click to Select Variety" readonly />
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpPackingType" class="form-label">Packing Type <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpPackingType"
                                                id="drpPackingType">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPackingType as $item) { ?>
                                                    <option value="<?php echo $item->Name; ?>">
                                                        <?php echo $item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpPackingStyle" class="form-label">Packing Style <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpPackingStyle"
                                                id="drpPackingStyle"
                                                <?php echo ($DisabledPackingStyle == 1)? "disabled" : "";?>>
                                                <option value=""> Select</option>
                                                <?php foreach ($resPackingStyle as $item) { ?>
                                                    <option value="<?php echo $item->PCKID; ?>" data-slabweight="<?php echo $item->SlabWeight; ?>" data-mfactor="<?php echo $item->MFactor; ?>"  data-totalslabs="<?php echo $item->TotalSlabs; ?>">
                                                        <?php echo $item->PackingStyle ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <input type="hidden" id="hdnPackingStyle" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpGrade" class="form-label">Grade</label>
                                            <select class="drpSelect form-control" name="drpGrade" id="drpGrade">
                                                <option value=""> Select</option>
                                                <?php foreach ($resGrade as $item) { ?>
                                                    <option value="<?php echo $item->value; ?>">
                                                        <?php echo $item->Options ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpFreezingType" class="form-label">Freezing Type</label>
                                            <select class="drpSelect form-control" name="drpFreezingType"
                                                id="drpFreezingType">
                                                <option value=""> Select</option>
                                                <?php foreach ($resFreezingType as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode; ?>">
                                                        <?php echo $item->FreezingTypeName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtGlaze" class="form-label">Glaze(%)</label>
                                            <input type="text" class="form-control" id="txtGlaze" name="txtGlaze" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpSoaking" class="form-label">Treatment</label>
                                            <select class="drpSelect form-control" name="drpSoaking" id="drpSoaking">
                                                <option value=""> Select</option>
                                                <?php foreach ($resSoaking as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode; ?>">
                                                        <?php echo $item->TreatmentName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpProductCategory" class="form-label">Product Category <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpProductCategory"
                                                id="drpProductCategory">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductCategory as $item) { ?>
                                                    <option value="<?php echo $item->Name; ?>" <?php echo ($item->Name === 'G1') ? 'selected' : ''; ?>>
                                                        <?php echo $item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpAbStatus" class="form-label">Ab Status <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpAbStatus" id="drpAbStatus"
                                            <?php echo ($DisabledABStatus == 1) ? 'disabled' : '';?>>
                                                <option value=""> Select</option>
                                                <?php
                                                if($DisabledABStatus == 0){
                                                    foreach ($resAbstatus as $item) { ?>
                                                        <option value="<?php echo $item->Name; ?>" <?php echo ($item->Name === 'AB-') ? 'selected' : ''; ?>>
                                                            <?php echo $item->Name ?>
                                                        </option>
                                                    <?php } 
                                                }?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpLineNumber" class="form-label">Line Number <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpLineNumber"
                                                id="drpLineNumber">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionLines as $item) { ?>
                                                    <option value="<?php echo $item->Value; ?>">
                                                        <?php echo $item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        
                                        <?php 
                                        if($EnableMcsAndLooseAtFreezingTrackPage == 1){?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtMcs" class="form-label">MCS 
                                                    <span id="txtMcsMsg"></span>                                                                               
                                                    <span style="color:red">*</span>
                                                </label>
                                                <input type="number" class="form-control" id="txtMcs"
                                                    name="txtMcs"  required  value="0"/>
                                            </div>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtLooseBags" class="form-label">LooseBags   
                                                    <span id="txtLooseBagsMsg"></span>                                                   
                                                    <span style="color:red">*</span>
                                                </label>
                                                <input type="number" class="form-control" id="txtLooseBags"
                                                    name="txtLooseBags" required value="0"/>
                                            </div>
                                        <?php } ?>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtProductionSlabs" class="form-label">Production Slabs <span
                                                    id="txtBalanceMessage"></span>                                                    
                                                    <span
                                                    style="color:red">*</span><br>
                                                <span id="slabInfo"></span></label>
                                            <input type="number" class="form-control" id="txtProductionSlabs"
                                                name="txtProductionSlabs" 
                                                <?php echo ($EnableMcsAndLooseAtFreezingTrackPage == 1) ? 'disabled' : '';?>/>
                                            <input type="hidden" id="hdnBalanceQty" value="" />
                                            <input type="hidden" id="hdnProdcutionQty" value="" />
                                        </div>



                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRejectionWeight" class="form-label">Rejection Weight</label>
                                            <input name="txtRejectionWeight" class="form-control"
                                                id="txtRejectionWeight" placeholder="Enter Rejection Weight"></input>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50"
                                                id="txtRemarks" placeholder="Enter Remarks(Optional)"></textarea>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnClientID" name="hdnClientID"
                                                value="<?php echo $ClientID ?>" />
                                            <input type="hidden" id="hdnProductionID" name="hdnProductionID"
                                                value="<?php echo (isset($_GET['ProductionDataID']) && !empty("ProductionDataID")) ? $_GET['ProductionDataID'] : "" ?>" />

                                            <!-- <a href="daily-production-list" class="btn btn-danger">Close</a> -->
                                            <button class="btn btn-primary" name="submit" id="submit">Save</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="divDataList"></div>

                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <?php include_once ROOT . 'includes/footer.php' ?>

    </div>
    <!-- end main content-->

    <div class="modal fade" id="modalVarietyList" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Variety List</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div id="modalVariety"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END layout-wrapper -->


<?php include_once ROOT . 'includes/common-js.php' ?>

<?php include_once WORKING_MODULE . 'modalSupplier.php' ?>


<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

<style>
    /* Apply background color to the select element */
    .dropOptColor {
        color: black;
        font-weight: bold;
        padding: 5px;
        border-radius: 5px;
    }

    .dropOptColor option[data-col="red"] {
        background-color: red;
        color: white;
    }

    .dropOptColor option[data-col="blue"] {
        background-color: blue;
        color: white;
    }

    .dropOptColor option[data-col="green"] {
        background-color: green;
        color: white;
    }
    .disabled-input {
    background-color: #e9ecef !important;
    cursor: not-allowed;
    pointer-events: none;
}
</style>

<script>
    $(document).ready(function () {
        // Button click event to add data-col attributes
        $("#drpTubNumber").click(function () {
            $("#colorSelect option").each(function () {
                $(this).attr("data-col", $(this).val()); // Assign value to data-col attribute
            });
            alert("Data attributes added to options!");
        });

        // Change background color based on selected option
        $("#colorSelect").change(function () {
            let selectedOption = $(this).find(":selected");
            let color = selectedOption.data("col"); // Get data-col attribute value
            if (color) {
                $(this).css("background-color", color);
            }
        });
    });


$(document).on("change", "#drpTrackCode, #drpTubNumber", function () {
    let trackCode = $("#drpTrackCode").val();
    let tubNumber = $("#drpTubNumber").val();

    // if (trackCode !== "" || tubNumber !== "") {
    //     $("#txtMixedBatchCode").val("") .prop("disabled", true) .addClass("disabled-input");
    // } else {
    //     $("#txtMixedBatchCode") .prop("disabled", false) .removeClass("disabled-input");
    // }
});


// Initialize validation
// var validator = $("#frmInward").validate({
//     ignore: ":hidden",
//     rules: validationRules,
//     errorPlacement: function () {
//         return false;
//     }
// });


</script>
<script>
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

    var poptable;
    $(document).on('shown.bs.modal', '#modalVarietyList', function () {
        if ($.fn.dataTable.isDataTable('.popVarietyTable')) {
            poptable.destroy();
        }
        // poptable.columns.adjust().draw();
        poptable = $('.popVarietyTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bInfo: false,
            ordering: false
        });

    });

    function fnDateformat(dt) {
        // var arrivalDate = $("#txtArrivalDate").val();
        var parts = dt.split("-");
        return parts[2] + "-" + parts[1] + "-" + parts[0];
    }

    function fnShowDataTable() {

        var excelHeader = "Freezing_ArrivalDate:" + fnDateformat($("#txtArrivalDate").val()) + "_" + $.trim($("#drpTrackCode option:selected").text()) + "_ProductionDate: " + fnDateformat($("#txtProductionDate").val());


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
                title: excelHeader, //'InWard List',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
        table.on('draw.dt', function () {

            let productionWeight = 0;
            let ProductionSlabs = 0;
            let rejectionWeight = 0;
            $('.production-weight').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    productionWeight += weight;
                }
            });
            $('.production-slabs').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    ProductionSlabs += weight;
                }
            });
            $('.rejection-weight').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    rejectionWeight += weight;
                }
            });


            $('#TotalProductionWeight').text(productionWeight.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#TotalProductionSlabs').text(ProductionSlabs.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#TotalRejectionWeight').text(rejectionWeight.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });
    }

    function getTrackCodes() {

        var productionPlant = <?php echo $ProductionPlant ?>;
        var IsHONPacking = <?php echo $IsHONPacking ?>;
        var IsPlantFilter = <?php echo $IsPlantFilter ?>;
        if (IsPlantFilter == 1) {
            if (productionPlant != 0) {
                $("#drpProductionPlant").prop('disabled', true);
                $("#drpProductionPlant").val(productionPlant).trigger('change');
            } else {
                $("#drpProductionPlant").prop('disabled', false);
                $("#drpPlant").val("").trigger('change');
            }
        }

        $.ajax({
            url: 'Ajax/ajax-freezing-trackid.php',
            type: 'POST',
            data: {
                methodType: "getTrackCodes",
                arrivalDate: $('#txtArrivalDate').val(),
                plantID: $("#drpProductionPlant").val(),
                isHONPacking: IsHONPacking,
                type: "Freezing",
            },
            success: function (data) {
                if (data.Error) {
                    alert(data.Error);
                } else {
                    var list = "<option value=''>Select</option>";
                    if (data.Data.RmDeHeadingTrackIds) {
                        $.each(data.Data.RmDeHeadingTrackIds, function (index, value) {
                            list += "<option value='" + value.TrackID + "'  data-plantid='" + value.PlantID + "' data-species='"+ value.Species +"'>" + value.TrackCode + "</option>";
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

    function getTimerData() {
        <?php 
        if($MixedBatchCode){?>
            if($('#txtMixedBatchCode').val()){
                return true;
            }
        <?php } ?>
        var productionPlant = <?php echo $ProductionPlant ?>;
        var IsPlantFilter = <?php echo $IsPlantFilter ?>;
        if (IsPlantFilter == 1) {
            if (productionPlant != 0) {
                $("#drpProductionPlant").prop('disabled', true);
                $("#drpProductionPlant").val(productionPlant).trigger('change');
            } else {
                $("#drpProductionPlant").prop('disabled', false);
                $("#drpPlant").val("").trigger('change');
            }
        }

        $.ajax({
            url: 'Ajax/ajax-freezing-trackid.php',
            type: 'POST',
            data: {
                methodType: "getTimerData",
                productionDate: $('#txtProductionDate').val(),
                plantID: $("#drpProductionPlant").val(),
                shift: $("#drpShift").val(),
                trackID: $('#drpTrackCode').val(),

            },
            success: function (data) {

                if (data.TimerData.length > 0) {
                    let timer = data.TimerData[0]; // Access the first object in the array

                    // $("#txtStartTimer").val(timer.StartTimer);
                    // $("#txtEndTimer").val(timer.EndTimer);
                } else {
                    console.log("No Timer Data Found");
                }

                return false;
            }
        });
    }

    $(document).on("change", "#txtArrivalDate", function () {
        if(!$('#hdnProductionID').val()){
            $('#txtMixedBatchCode').val('');
            $('#drpTrackCode, #drpTubNumber, #txtProductionSlabs, #txtMcs, #txtLooseBags').prop({ disabled: false, required: true}).val('').trigger("change") 
        }
        <?php 
        if($EnableMcsAndLooseAtFreezingTrackPage == 1){?>
            $('#txtProductionSlabs').prop({ disabled: true, required: false}).val('').trigger("change") 
        <?php } ?>
        $('#txtWeight').prop('required',false)
        $('#txtBalanceMessage').text('')
        getTrackCodes();
    });
    $(document).on("change", "#drpShift", function () {
    // if( !empty($('#drpTrackCode').val())){
        getTimerData();
// }
    });
    $(document).on("change", "#txtProductionDate", function () {
        // if( !empty($('#drpTrackCode').val())){
        getTimerData();
// }
    });


    function getTubs() {

        var trackID = $('#drpTrackCode').val();
        var plantID = $("#drpProductionPlant").val();
        var arrivaldate = $('#txtArrivalDate').val();
        var IsHONPacking = <?php echo $IsHONPacking ?>;
        var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram

        if ($('#drpTrackCode').val().length > 0) {
            $.ajax({
                url: 'Ajax/ajax-freezing-trackid.php',
                type: 'POST',
                data: {
                    methodType: "getTubNumbers",
                    trackID: trackID,
                    plantID: plantID,
                    arrivalDate: arrivaldate,
                    isHONPacking: IsHONPacking
                },
                success: function (data) {
                    $('#submit').attr('disabled',false)
                    var listTubNo = "<option value=''>Select</option>";
                    if (data.TubNumbesData) {

                        if (data.TubNumbesData.TubNumbers) {
                            $.each(data.TubNumbesData.TubNumbers, function (index, value) {
                                // console.log(value?.BalanceQty); allowedFreezingWeight

                                listTubNo += "<option data-col='red' value='" + value.TubNumberValue + "' " +
                                    "data-tubnumbervalues='" + value.TubNumber + "' " +
                                    "data-soakingQty='" + value.SoakInQty + "' " +
                                    "data-species='" + value.Species + "' " +
                                    "data-balanceqty='" + value.BalanceQty + "' " +
                                    "data-allowedfreezingweight='" + value.allowedFreezingWeight + "' " +
                                    "data-shortname='" + value.ShortName + "' " +
                                    "data-typeproduct='" + value.TypeProduct + "' " +
                                    "data-grade='" + value.GradeNumber + "' " +
                                    "data-treatment='" + value.Treatment + "' " +
                                    "data-gradingbreakupid='" + value.GradingBreakupID + "' " +
                                    "data-variety='" + value.Variety + "' data-productionlist='" + value.ProductionList + "'>"

                                //if (value.IsStoreMaterial == 0 && value.ArrivalNumber != '' && IsPlantFilter == 0) {
                                if (IsPlantFilter == 0 && value.ArrivalNumber != ''){
                                    listTubNo += value.ArrivalNumber + " - "; //uma

                                }

                                listTubNo += value.TubCode + "(" + value.BalanceQty + " KGs / " + value.SoakInQty + " KGs) " + value.Grade +
                                    "</option>";

                            });
                        }

                        $('#drpTubNumber').select2('destroy');
                        $('#drpTubNumber').html(listTubNo);
                        $('#drpTubNumber').select2();
                    } else {
                        $('#drpTubNumber').select2('destroy');
                        $('#drpTubNumber').html(listTubNo);
                        $('#drpTubNumber').select2();
                    }
                    return false;
                }
            });
            getProdDataByProdDate();

        } else {
            if(!$('#txtMixedBatchCode').val()){
                $("#divDataList").html("");
            }
            // $("#txtVariety").val("").trigger("change");
        }



    }
    $(document).on("change", "#drpTrackCode", function () {

        getTubs();
    });

    $("#txtVariety").on("click", function () {
        // var tubNumber = $("#drpTubNumber").val();
        // if (tubNumber) {
        getVarieties();
        // } else {
        //     alert("Select Tub Number");
        // }

    });

    $(document).on("click", ".selectVariety", function () {        
        var selectedVariety = $(this).val();
        $("#txtVariety").val(selectedVariety);

        var selectedVarietyID = $(this).attr("data-po-grade-id");
        $("#txtVariety").attr("data-po-grade-id", selectedVarietyID);

        var selectedVarietyID = $(this).attr("data-varietyid");
        $("#txtVariety").attr("data-varietyid", selectedVarietyID);        

        var typeproduct = $(this).attr("data-type");
        if (typeproduct === 'COOKED') {
            var soakingqty = $("#drpTubNumber option:selected").data('soakingqty');
            var allowedfreezingweight = $("#drpTubNumber option:selected").data('allowedfreezingweight');
            var balanceqty = $("#drpTubNumber option:selected").data('balanceqty');
            allowedfreezingweight = parseFloat(soakingqty);
            var balanceqty = parseFloat(balanceqty) - (parseFloat(allowedfreezingweight) - parseFloat(soakingqty));
            $("#drpTubNumber option:selected").attr('data-allowedfreezingweight', allowedfreezingweight);
            if (balanceqty > 0) {
                $("#txtBalanceMessage").text("(Balance Qty : " + (parseFloat(balanceqty) || 0).toFixed(3) + " KGs )").css("color", "green");
            } else {
                $("#txtBalanceMessage").text("(Balance Qty : " + (parseFloat(balanceqty) || 0).toFixed(3) + " KGs )").css("color", "red");
            }

            // $("#hdnBalanceQty").val(balanceqty);
        } else {
            var soakingqty = $("#drpTubNumber option:selected").data('soakingqty');
            var allowedfreezingweight = $("#drpTubNumber option:selected").data('allowedfreezingweight');
            var balanceqty = $("#drpTubNumber option:selected").data('balanceqty');
            $("#drpTubNumber option:selected").attr('data-allowedfreezingweight', allowedfreezingweight);
            if (balanceqty > 0) {
                $("#txtBalanceMessage").text("(Balance Qty : " + (parseFloat(balanceqty) || 0).toFixed(3) + " KGs )").css("color", "green");
            } else {
                $("#txtBalanceMessage").text("(Balance Qty : " + (parseFloat(balanceqty) || 0).toFixed(3) + " KGs )").css("color", "red");
            }
        }

        $('#modalVarietyList').modal('hide');
        getGrades();
        <?php 
        if($MixedBatchCode){?>
            if($('#txtMixedBatchCode').val()){
                $("#txtBalanceMessage").text("");
            }
        <?php } ?>
    })

    function getVarieties() {
        var species = '';
        if(!$("#drpPoNumber").val()){
            var selectedOption = $("#drpTrackCode").find(":selected");
            species = selectedOption.data("species");
        }
        $.ajax({
            type: 'POST',
            url: 'Ajax/ajax-freezing-trackid.php',
            data: {
                'methodType': 'poNumber',
                'pOID': $("#drpPoNumber").val(),
                'packingType': $("#drpPackingType").val(),
                'species': species,
                // 'variety': $("#drpTubNumber option:selected").data('variety'),
                // 'type': $("#drpTubNumber option:selected").data('tubnumbervalues'),

                // 'species': $("#drpTubNumber option:selected").data("species"),
                // 'shortname': $("#drpTubNumber option:selected").data("shortname"),
                // 'typeproduct': $("#drpTubNumber option:selected").data("typeproduct"),
                // 'grade': $("#drpTubNumber option:selected").data("grade"),
                // 'treatment': $("#drpTubNumber option:selected").data("treatment"),
            },
            success: function (data) {
                if (data) {
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        $('#modalVariety').html("").html(data.HtmlRes);
                        $('#modalVarietyList').modal('show');
                        <?php 
                        if($DisabledPackingStyle == 0){?>
                            if (data.ReadonlyDropdowns.PackingStyle == "Enable") {
                                $("#drpPackingStyle").prop("disabled", false);
                            } else {
                                $("#drpPackingStyle").prop("disabled", true);
                            }
                        <?php }?>

                        if (data.ReadonlyDropdowns.DisablePackingType == "Enable") {
                            $("#drpPackingType").prop("disabled", false);
                        } else {
                            $('#drpPackingType').select2('destroy');
                            $("#drpPackingType").val("DUMMY").prop("disabled", true);
                            $('#drpPackingType').select2();
                        }
                    }
                }
            }
        });
    }

    var prodID = "<?php echo isset($_GET["ProductionDataID"]) && !empty($_GET["ProductionDataID"]) ? $_GET["ProductionDataID"] : "" ?>";
    var IsPlantFilter = <?php echo $IsPlantFilter ?>;

    function fnGetProdData() {
        if (prodID != "") {
            $.ajax({
                url: 'Ajax/ajax-freezing-trackid.php',
                type: 'POST',
                data: {
                    methodType: "getProdData",
                    prodDataId: prodID,
                    barCodeID: "",
                },
                success: function (data) {
                    if (data) {
                        $("#txtProductionDate").val(data.ProductionDate);
                        $("#drpGroup").val(data.Group).trigger("change");
                        $("#drpStockBelongs").val(data.StockHolderID).trigger("change");
                        if (IsPlantFilter == 1) {
                            $("#drpProductionPlant").val(data.PlantID).trigger("change");
                        } else {
                            $("#drpProductionPlant").val("");
                        }
                        $("#drpShift").val(data.ShiftID).trigger("change");

                        $('#drpPackingType').select2('destroy');
                        $("#drpPackingType").val(data.PackingType);
                        $('#drpPackingType').select2();

                        $("#drpProductCategory").val(data.ProductCategory).trigger("change");
                        $("#drpAbStatus").val(data.AbStatus).trigger("change");
                        $("#drpLineNumber").val(data.LineNumber).trigger("change");
                        $("#txtRemarks").val(data.Remarks);
                        $("#txtRejectionWeight").val(data.RejectionWeight);
                        $("#txtProductionSlabs").val(data.ProductionSlabs);
                        $("#txtStartTimer").val(data.StartTimer);
                        $("#txtEndTimer").val(data.EndTimer);


                        $('#drpPoNumber').select2('destroy');
                        $("#drpPoNumber").val(data.PoID);
                        $('#drpPoNumber').select2();
                        <?php if($EnableCodeListPrint){?>
                                var Poid = data.PoID;
                                $('#drpPoNumber').prev("label").text('Po Number');
                                if(Poid){
                                    var urlParam = btoa('PoID='+Poid+'&ReportName=Internal code list');
                                    var printLink = `
                                                    <a target="_blank"
                                                    href="/v3/QualityControl/Reports/D1/DocMpedaCodeList?Q=${urlParam}&sourceUrl=Freezing"
                                                    class="mr-3 text-primary"
                                                    title="Code List Print">
                                                    <i class="mdi mdi-printer font-size-18"></i>
                                                    </a>
                                                `; 
                                    $('#drpPoNumber').prev('label').append(printLink);
                                }
                        <?php } ?>

                        $("#txtVariety").attr("data-varietyid", data.VarietyID);
                        $("#txtVariety").attr("data-po-grade-id", data.PoGradeId);
                        $("#txtVariety").val(data.Variety + " - " + data.Grade + " - " + data.ActualPackingStyle + " (" + data.Mcs + " Mcs)");

                        $("#drpGrade").val(data.Grade).trigger("change").prop("disabled", true);
                        $("#drpFreezingType").val(data.FreezingType).trigger("change").prop("disabled", true);
                        $("#txtGlaze").val(data.GlazePerc).attr("readonly", true);
                        $("#drpSoaking").val(data.Soaking).trigger("change").prop("disabled", true);

                        $('#drpPackingStyle').select2('destroy');
                        if (data.PackingType == "MASTER") {
                            $("#drpPackingStyle").prop("disabled", true).val(data.PackingStyleID);
                        } else {
                            $("#drpPackingStyle").prop("disabled", false).val(data.PackingStyleID);
                        }
                        $('#drpPackingStyle').select2();

                        $("#hdnPackingStyle").val(data.PackingStyleID);


                    } else {
                        console.log("no data");
                    }
                    $("#hdnProdcutionQty").val("");

                    return false;
                }
            });
        } else {
            console.log("Else");
        }
    }

    $(document).ready(function () {

        var ProductionDate = $("#txtProductionDate").val();
        var ProductionDataID = "<?php echo isset($_GET["ProductionDataID"]) && !empty($_GET["ProductionDataID"]) ? $_GET["ProductionDataID"] : "" ?>";


        if (ProductionDataID != '') {
            fnGetProdData();
        } else {
            // 
        }

        getTrackCodes();
        var productionPlant = <?php echo $ProductionPlant ?>;
        var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram
        if (productionPlant != 0 || IsPlantFilter == 0) {
            $("#drpProductionPlant").prop('disabled', true);
            if (IsPlantFilter == 0) {
                $("#drpProductionPlant").val("");
            } else {
                $("#drpProductionPlant").val(productionPlant).trigger('change');
            }
        } else {
            $("#drpProductionPlant").prop('disabled', false);
            // $("#drpProductionPlant").val("").trigger('change');
        }


        $(document).on('change', '#drpProductionPlant', function () {
            var productionPlant = <?php echo $ProductionPlant ?>;
            if (productionPlant == 0) {
                $("#txtArrivalDate").trigger('change');
            }
        });
    });

    $("#drpPoNumber").on("change", function () {
        $("#txtVariety").val("").attr("data-po-grade-id", "").attr("data-varietyid", "");
        <?php if($EnableCodeListPrint){?>
            var Poid = $(this).val();
           $(this).prev("label").text('Po Number');
            if(Poid){
                var urlParam = btoa('PoID='+Poid+'&ReportName=Internal code list');
                var printLink = `
                                <a target="_blank"
                                href="/v3/QualityControl/Reports/D1/DocMpedaCodeList?Q=${urlParam}&sourceUrl=Freezing"
                                class="mr-3 text-primary"
                                title="Code List Print">
                                <i class="mdi mdi-printer font-size-18"></i>
                                </a>
                            `; 
                $(this).prev('label').append(printLink);
            }
        <?php } ?>
    });

    $("#drpPackingType").on("change", function () {
        <?php if ($DisabledPackingStyle == 0) { ?>
            if ($(this).val() == "MASTER") {
                $("#drpPackingStyle").prop("disabled", true).val($("#hdnPackingStyle").val()).trigger("change");
            } else {
                $("#drpPackingStyle").prop("disabled", false).val("").trigger("change");
            }
        <?php }else{?>
                $("#drpPackingStyle").val($("#hdnPackingStyle").val()).trigger("change");
        <?php } ?>
    });
    $("#drpFreezingType").on("change", function () {
        var allowExcessFreezing = <?php echo $allowExcessFreezing ?>; // sriram 
        if (allowExcessFreezing == 1) {
            var freezing = $(this).val();
            if (freezing == 'BLOCK') {
                var tubNumber = $("#drpTubNumber").val();
                var typeofMaterial = $("#drpTubNumber option:selected").data('tubnumbervalues');
                var soakingqty = $("#drpTubNumber option:selected").data('soakingqty');
                var allowedfreezingweight = $("#drpTubNumber option:selected").data('allowedfreezingweight');
                allowedfreezingweight = parseFloat(soakingqty) + parseFloat(Math.round(soakingqty * 0.15 * 100) / 100);
                $("#drpTubNumber option:selected").attr('data-allowedfreezingweight', allowedfreezingweight);
            } else {
                var drpSoaking = $("#drpSoaking").val();
                if (freezing == 'IQF' && drpSoaking == 'UNT') {
                    var tubNumber = $("#drpTubNumber").val();
                    var typeofMaterial = $("#drpTubNumber option:selected").data('tubnumbervalues');
                    var soakingqty = $("#drpTubNumber option:selected").data('soakingqty');
                    var allowedfreezingweight = $("#drpTubNumber option:selected").data('allowedfreezingweight');
                    allowedfreezingweight = parseFloat(soakingqty) + parseFloat(Math.round(soakingqty * 0.15 * 100) / 100);
                    $("#drpTubNumber option:selected").attr('data-allowedfreezingweight', allowedfreezingweight);
                }
            }
        }
    });
    $("#drpFreezingType").on("change", function () {
        var allowExcessFreezing = <?php echo $allowExcessFreezing ?>; // sriram
        if (allowExcessFreezing == 1) {
            var drpSoaking = $(this).val();
            var freezing = $("#drpFreezingType").val();
            if (freezing == 'IQF' && drpSoaking == 'UNT') {
                var tubNumber = $("#drpTubNumber").val();
                var typeofMaterial = $("#drpTubNumber option:selected").data('tubnumbervalues');
                var soakingqty = $("#drpTubNumber option:selected").data('soakingqty');
                var allowedfreezingweight = $("#drpTubNumber option:selected").data('allowedfreezingweight');
                allowedfreezingweight = parseFloat(soakingqty) + parseFloat(Math.round(soakingqty * 0.15 * 100) / 100);
                $("#drpTubNumber option:selected").attr('data-allowedfreezingweight', allowedfreezingweight);
            }
        }
    });

    function getProdDataByProdDate() {
        var productionPlant = <?php echo $ProductionPlant ?>;
        $.ajax({
            url: 'Ajax/ajax-freezing-trackid.php',
            type: 'POST',
            data: {
                methodType: "getList",
                trackID: $("#drpTrackCode").val(),
                plantID: $("#drpProductionPlant").val(),
                arrivalDate: $("#txtArrivalDate").val(),
                txtMixedBatchCode: $("#txtMixedBatchCode").val(),
            },
            success: function (data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                $("#txtProductionSlabs, #txtMcs, #txtLooseBags").val("");
                $("#hdnProdcutionQty").val("");


                return false;
            }
        });
    }

    // $(document).on("change", "#drpTubNumber", function () {
    //     var tubNumber = $("#drpTubNumber").val();
    //     var typeofMaterial = $("#drpTubNumber option:selected").data('tubnumbervalues');

    //     if (tubNumber != '') {
    //         $.ajax({
    //             type: 'POST',
    //             url: 'Ajax/ajax-freezing-trackid.php',
    //             data: {
    //                 methodType: 'getBalanceQty',
    //                 tubNumber: tubNumber,
    //                 typeofMaterial: typeofMaterial,
    //                 drpTrackCode: $("#drpTrackCode").val(),
    //             },
    //             success: function (data) {
    //                 console.log("getBalanceQty ", data);

    //                 $("#txtBalanceMessage").text("(Balance Qty : " + data + " KGs )");
    //                 $("#hdnBalanceQty").val(data);
    //             }
    //         });
    //     }
    // });


    $(document).on("change", "#drpTubNumber", function () {
        if(!$("#txtMixedBatchCode").val()){
            $("#txtVariety").val("");
        
            $("#drpPackingType").prop("disabled", false);
            $("#drpPackingType").val("").trigger("change");
        

            $("#drpPackingStyle").val("").trigger("change");
            $("#drpGrade").prop("disabled", false).val("").trigger("change");
            $("#drpFreezingType").prop("disabled", false).val("").trigger("change");
            $("#txtGlaze").val("");
            $("#drpSoaking").prop("disabled", false).val("").trigger("change");

            var tubNumber = $("#drpTubNumber").val();
            var typeofMaterial = $("#drpTubNumber option:selected").data('tubnumbervalues');
            var balanceqty = $("#drpTubNumber option:selected").data('balanceqty');

            if (tubNumber != '') {
                if (balanceqty > 0) {
                    $("#txtBalanceMessage").text("(Balance Qty : " + (parseFloat(balanceqty) || 0).toFixed(3) + " KGs )").css("color", "green");
                } else {
                    $("#txtBalanceMessage").text("(Balance Qty : " + (parseFloat(balanceqty) || 0).toFixed(3) + " KGs )").css("color", "red");
                }

                // $("#hdnBalanceQty").val(balanceqty);
            }
        }
    });

    function getGrades() {
        poGraidID = $.trim($("#txtVariety").attr("data-po-grade-id"));

        $.ajax({
            type: 'POST',
            url: 'Ajax/ajax-freezing-trackid.php',
            data: {
                'methodType': 'varietyChange',
                'pOID': $("#drpPoNumber").val(),
                'varietyID': $("#txtVariety").attr("data-varietyid"), // $("#txtVariety").val(),
                'packingType': $("#drpPackingType").val(),
                'poGradeID': poGraidID
            },
            success: function (data) {
                var obj = data.DependentDropdownsData.GradeData;
                $("#drpGrade").val(obj.Grade).trigger("change").prop("disabled", true);
                $("#drpFreezingType").val(obj.FreezingTypeID).trigger("change").prop("disabled", true);
                $("#txtGlaze").val(obj.GlazePerc).attr("readonly", true);
                $("#drpSoaking").val(obj.SoakingType).trigger("change").prop("disabled", true);
                // $("#txtSalt").val(obj.Salt).attr("readonly", "true");

                $("#hdnPackingStyle").val(obj.PackingStyleID);
                <?php 
                    if($DisabledPackingStyle == 1){?>
                        $("#drpPackingStyle").val(obj.PackingStyleID).trigger("change");
                <?php }else{?>
                    if (obj.PackingStyle == "Enable") {
                        $("#drpPackingStyle").prop("disabled", false).val(obj.PackingStyleID).trigger("change");
                    } else {
                        $("#drpPackingStyle").prop("disabled", true).val(obj.PackingStyleID).trigger("change");
                    }
                <?php }?>
                // obj.PackingStyle
            }
        });
    }

    $(document).ready(function () {
        var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram

        var validationRules = {
            txtProductionDate: {
                required: true,
                noSpace: true
            },

            drpGroup: {
                required: true
            },

            drpTrackCode: {
                required: true
            },
            drpTubNumber: {
                required: true
            },
            drpShift: {
                required: true
            },

            txtVariety: {
                required: true
            },
            drpPackingType: {
                required: true
            },
            drpPackingStyle: {
                required: true
            },
            drpGrade: {
                required: true
            },
            drpFreezingType: {
                required: true
            },
            drpSoaking: {
                required: true
            },
            drpProductCategory: {
                required: true
            },
            <?php 
            if($DisabledABStatus == 0){?>
                drpAbStatus: {
                    required: true
                },
            <?php }?>
            drpLineNumber: {
                required: true
            },
            txtProductionSlabs: {
                required: true,
                noSpace: true
            },

        };
        if (IsPlantFilter != 0) {
            validationRules.drpProductionPlant = { required: true };
        }
        $("#frmInward").validate({
            ignore: ":hidden",
            onkeyup: false,
            onfocusout: false,
            invalidHandler: function (form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    firstInvalidElement.focus();
                }
            },
            ignore: ":disabled",
            rules: validationRules,
            errorPlacement: function () {
                return false; // suppresses error message text
            },
            submitHandler: function () {
                var poGId = $.trim($("#txtVariety").attr("data-po-grade-id"));
                var tubNumberValue = $("#drpTubNumber option:selected").data("tubnumbervalues");
                var gradingbreakupid = $("#drpTubNumber option:selected").data("gradingbreakupid"); //uma
                // alert(gradingbreakupid);
                if ($("#hdnProductionID").val() != "") {
                    psid = $.trim($("#hdnProductionID").val());
                } else {
                    psid = "";
                }

                var allowedfreezingweight = $("#drpTubNumber option:selected").data('allowedfreezingweight');
                var balanceqty = $("#drpTubNumber option:selected").data('balanceqty');
                var soakingQty = $("#drpTubNumber option:selected").data('soakingqty');
                var allowExcessFreezing = <?php echo $allowExcessFreezing ?>; // sriram
                if (allowExcessFreezing == 1) {
                    var freezing = $("#drpFreezingType").val();
                    var drpSoaking = $("#drpSoaking").val();
                    if (drpSoaking == 'UNT' && ((freezing == 'BLOCK') || (freezing == 'IQF'))) {
                        allowedfreezingweight = parseFloat(soakingQty) + parseFloat(Math.round(soakingQty * 0.15 * 100) / 100);
                    }
                }
                let PoStoreQty = parseFloat(0);
                <?php 
                if($MCSRestriction == 1 ){?>
                    PoStoreQty = parseFloat($('#slabInfo').data('qty'));
                <?php }?>

                var FinalBalanceqty = parseFloat(balanceqty);
                if (FinalBalanceqty < 0) {
                    var FinalBalanceqty = Math.abs(FinalBalanceqty);
                    var currentFreezingQty = parseFloat(soakingQty) + parseFloat(FinalBalanceqty) ;
                } else {
                    var currentFreezingQty = parseFloat(soakingQty) - (parseFloat(FinalBalanceqty) );
                }


                // alert(soakingQty);

                currentFreezingQty = currentFreezingQty.toFixed(2);

                // var soakOutQty=SoakInQty+balanceqty;  // 600+102 > 702
                // var allowdLimit=round((SoakInQty * 25)/100);  // 600+102 > 702


                // if (tubNumber != '') {
                //     if (balanceqty > 0) {
                //         $("#txtBalanceMessage").text("(Balance Qty : " + balanceqty + " KGs )").css("color", "green");
                //     } else {
                //         $("#txtBalanceMessage").text("(Balance Qty : " + balanceqty + " KGs )").css("color", "red");
                //     }


                var stockOutID = "<?php echo isset($_GET["StockOutID"]) && !empty($_GET["StockOutID"]) ? $_GET["StockOutID"] : "" ?>";
                var IsDeductGlaze = <?php echo $IsDeductGlaze ?>; // sriram
                var IsDeductGlazeFW = <?php echo $IsDeductGlazeFW ?>; // sriram
                var frmData = {
                    methodType: "create",
                    productionID: psid,
                    queryStockId: stockOutID,

                    allowedfreezingweight: allowedfreezingweight,
                    currentFreezingQty: currentFreezingQty,
                    txtProductionDate: $.trim($("#txtProductionDate").val()),
                    drpTrackCode: $.trim($("#drpTrackCode").val()),
                    drpTubNumber: $("#drpTubNumber").val(),
                    tubNumberValue: tubNumberValue,
                    gradingbreakupID: gradingbreakupid, //uma
                    clientID: $("#hdnClientID").val(),
                    drpGroup: $.trim($("#drpGroup").val()),
                    drpStockBelongs: $.trim($("#drpStockBelongs").val()),
                    drpProductionPlant: $.trim($("#drpProductionPlant").val()),
                    drpShift: $.trim($("#drpShift").val()),
                    txtProductionCode: "",
                    txtBatchCode: "",
                    drpPoNumber: $.trim($("#drpPoNumber").val()),
                    drpVariety: $.trim($("#txtVariety").attr("data-varietyid")),
                    drpPackingType: $.trim($("#drpPackingType").val()),
                    drpPackingStyle: $.trim($("#drpPackingStyle").val()),
                    drpGrade: $.trim($("#drpGrade").val()),
                    drpFreezingType: $.trim($("#drpFreezingType").val()),
                    txtGlaze: $.trim($("#txtGlaze").val()),
                    drpSoaking: $.trim($("#drpSoaking").val()),
                    drpProductCategory: $.trim($("#drpProductCategory").val()),
                    drpAbStatus: $.trim($("#drpAbStatus").val()),
                    drpLineNumber: $.trim($("#drpLineNumber").val()),
                    txtRemarks: $.trim($("#txtRemarks").val()),
                    txtRejectionWeight: $.trim($("#txtRejectionWeight").val()),
                    txtProductionSlabs: $.trim($("#txtProductionSlabs").val()),
                    txtArrivalDate: $.trim($("#txtArrivalDate").val()),
                    txtStartTimer: $.trim($("#txtStartTimer").val()),
                    txtEndTimer: $.trim($("#txtEndTimer").val()),
                    <?php 
                    if($MixedBatchCode){?>
                        txtMixedBatchCode: $.trim($("#txtMixedBatchCode").val()),
                        txtWeight: $.trim($("#txtWeight").val()),
                    <?php }?>
                    <?php 
                    if($EnableMcsAndLooseAtFreezingTrackPage == 1){?>
                        txtMcs: $.trim($("#txtMcs").val()),
                        txtLooseBags: $.trim($("#txtLooseBags").val()),
                    <?php }?>
                     
                    
                    IsDeductGlaze: IsDeductGlaze,
                    IsDeductGlazeFW: IsDeductGlazeFW,
                    poGradeID: poGId,
                    productionList: $("#drpTubNumber option:selected").data("productionlist"),//sriram
                }

                $.ajax({
                    url: 'Ajax/ajax-freezing-trackid.php',
                    type: 'POST',
                    data: frmData,
                    success: function (data) {

                        if (data.includes("Error")) {
                            alert(data);
                            
                        } else {
                            // alert("Data Saved Successfully");
                            $("#divDataList").html(data);
                            fnShowDataTable();
                            
                            alertMsg = $("#hdnMsg").val();
                            alert(alertMsg);
                            $("#hdnProductionID").val("");
                            $("#txtProductionSlabs, #txtMcs, #txtLooseBags").val("");
                            $('#txtMcs').removeAttr('data-avl_mcs')
                            $('#txtLooseBags').removeAttr('data-avl_loose')
                            $("#hdnProdcutionQty").val("");
                            $("#drpTubNumber").trigger("change");

                            $("#hdnProductionID").val("");

                            $("#txtVariety").val("");
                            $("#drpPackingType").val("").trigger("change").prop("disabled", false);
                            $("#drpPackingStyle").val("").trigger("change");
                            $("#drpGrade").val("").trigger("change").prop("disabled", false);
                            $("#drpFreezingType").val("").trigger("change").prop("disabled", false);
                            $("#txtGlaze").val("").attr("readonly", false);
                            $("#drpSoaking").val("").trigger("change").prop("disabled", false);
                            //$("#drpLineNumber").val("").trigger("change");
                            $("#txtRemarks").val("");
                            $("#txtRejectionWeight").val("");
                            $("#drpShift").val("");                            
                            $("#txtBalanceMessage,#slabInfo").text("");

                            if($("#txtMixedBatchCode").length == 0 || !$("#txtMixedBatchCode").val()){
                                getTubs();
                            }
                            //  $("#txtMixedBatchCode").val("");
                            //  $("#txtWeight").val("");
                        }


                        return false;
                    }
                });
            }
        });
    });



    $(document).on("click", ".btnEdit", function () {
        var IsPlantFilter = <?php echo $IsPlantFilter ?>;

        if ($(this).attr("data-prodid") != "") {
            pid = $(this).attr("data-prodid");
        } else if (prodID != "") {
            pid = prodID;
        } else {
            pid = "";
        }

        $('#drpPoNumber').select2('destroy');
        $('#submit').prop("disabled", true);
        //   var trackId=$("#drpTrackCode").val();
        // alert(trackId);
        $.ajax({             
            url: 'Ajax/ajax-freezing-trackid.php',
            type: 'POST',
            data: {
                methodType: "getSingleStock",
                productionId: pid,
                drpTrackCode: $("#drpTrackCode").val(),
                productionDate: $("#txtProductionDate").val(),
                txtMixedBatchCode: $("#txtMixedBatchCode").val(),
            },
            success: function (data) {
                $('#submit').attr('disabled',false)
                $("#hdnProductionID").val(data.ProductionID);
                $("#txtProductionDate").val(data.ProductionDate);
                
                $("#drpTubNumber").val(data.TubNo).trigger("change");

                $('#drpTubNumber option').each(function () {

                    if ($(this).data('tubnumbervalues') == data.TubCode && $(this).val() == data.TubNo) {
                        $(this).prop('selected', true);
                    }
                });

                $('#drpTubNumber').trigger("change");

                $("#hdnFreezingQty").val(data.ProductionWeight);
                $("#drpGroup").val(data.Group).trigger("change");
                $("#drpStockBelongs").val(data.StockHolderID).trigger("change");
                $("#drpLineNumber").val(data.LineNumber).trigger("change");
                if (IsPlantFilter == 1) {
                    $("#drpProductionPlant").val(data.PlantID).trigger("change");
                } else {
                    $("#drpProductionPlant").val("");
                }

                $("#drpShift").val(data.ShiftID).trigger("change");

                $("#txtStartTimer").val(data.StartTimer);
                $("#txtEndTimer").val(data.EndTimer);

                $("#drpPackingType").val(data.PackingType).trigger("change");

                $("#drpProductCategory").val(data.ProductCategory).trigger("change");
                $("#drpAbStatus").val(data.AbStatus).trigger("change");
                $("#txtRemarks").val(data.Remarks);
                $("#txtRejectionWeight").val(data.RejectionWeight);
                if(!$("#txtMixedBatchCode").val()){ 
                    $("#txtProductionSlabs").val(data.ProductionSlabs).attr('data-edit_click',1);
                    $("#hdnProdcutionQty").val(data.ProductionWeight);
                    <?php 
                    if($EnableMcsAndLooseAtFreezingTrackPage == 1){?>
                        $("#txtMcs").val(data.ProductionMcs).attr('data-edit_click',1);
                        $("#txtLooseBags").val(data.ProductionLooseBags);
                    <?php }?>
                }else{
                    $("#txtWeight").val(data.ProductionWeight);
                }
                if(data.MixedBatch){
                    $('#drpTrackCode, #drpTubNumber, #txtProductionSlabs').prop({ disabled: true, required: false}).val('').trigger("change") 
                    $('#txtWeight').prop('required',true)
                    $('#txtBalanceMessage').text('')
                }
                //  $("#txtMixedBatchCode").val(data.MixedBacthCode);
                //     $("#txtWeight").val(data.Weight);
                

                $("#drpPoNumber").val(data.PoID);
                $('#drpPoNumber').select2();
                <?php if($EnableCodeListPrint){?>
                        var Poid = data.PoID;
                        $('#drpPoNumber').prev("label").text('Po Number');
                        if(Poid){
                            var urlParam = btoa('PoID='+Poid+'&ReportName=Internal code list');
                            var printLink = `
                                            <a target="_blank"
                                            href="/v3/QualityControl/Reports/D1/DocMpedaCodeList?Q=${urlParam}&sourceUrl=Freezing"
                                            class="mr-3 text-primary"
                                            title="Code List Print">
                                            <i class="mdi mdi-printer font-size-18"></i>
                                            </a>
                                        `; 
                            $('#drpPoNumber').prev('label').append(printLink);
                        }
                <?php } ?>

                $("#txtVariety").attr("data-varietyid", data.VarietyID);
                $("#txtVariety").attr("data-po-grade-id", data.PoGradeId);
                $("#txtVariety").val(data.Variety + " - " + data.Grade + " - " + data.ActualPackingStyle + " (" + data.Mcs + " Mcs)");

                $("#drpGrade").val(data.Grade).trigger("change").prop("disabled", true);
                $("#drpFreezingType").val(data.FreezingType).trigger("change").prop("disabled", true);
                $("#txtGlaze").val(data.GlazePerc).attr("readonly", true);
                $("#drpSoaking").val(data.Soaking).trigger("change").prop("disabled", true);

                $('#drpPackingStyle').select2('destroy');
                <?php 
                    if($DisabledPackingStyle == 1){ ?>
                         $("#drpPackingStyle").val(data.PackingStyleID).trigger("change");
                <?php 
                }else{ ?>
                        if (data.PackingType == "MASTER") {
                            $("#drpPackingStyle").prop("disabled", true).val(data.PackingStyleID).trigger("change");
                        } else {
                            $("#drpPackingStyle").prop("disabled", false).val(data.PackingStyleID).trigger("change");
                        }
                <?php } ?>
                
                $('#drpPackingStyle').select2();

                $("#hdnPackingStyle").val(data.PackingStyleID);

                $('#submit').prop("disabled", false);
                <?php
                    if($MCSRestriction == 1 ){?>
                        checkAvailableMcs();
                <?php }?>

                return false;
            }
        });

        $('#drpTubNumber').trigger("change");
        

        $("html, body").animate({
            scrollTop: 0
        }, "slow");

    });

    $(document).on("click", ".btnDelete", function () {
        
        var IsDeductGlazeFW = <?php echo $IsDeductGlazeFW ?>; // sriram
        if ($(this).attr("data-prodid") != "") {
            pid = $(this).attr("data-prodid");
        } else if (prodID != "") {
            pid = prodID;
        } else {
            pid = "";
        }

        $.ajax({
            url: 'Ajax/ajax-freezing-trackid.php',
            type: 'POST',
            data: {
                methodType: "deleteBreakup",
                productionId: pid,
                productionDate: $("#txtProductionDate").val(),
                drpTrackCode: $("#drpTrackCode").val(),
                IsDeductGlazeFW: IsDeductGlazeFW,
                txtMixedBatchCode: $("#txtMixedBatchCode").val(),
            },
            success: function (data) {
                $('#submit').attr('disabled',false)
                $("#divDataList").html(data);
                fnShowDataTable();

                $("#txtProductionSlabs, #txtMcs, #txtLooseBags").val("");
                $('#txtMcs').removeAttr('data-avl_mcs')
                $('#txtLooseBags').removeAttr('data-avl_loose')
                $("#hdnProdcutionQty").val("");
                $("#drpTubNumber").trigger("change");

                // alert("Data deleted successfully");
                alertMsg = $("#hdnMsg").val();
                $("#hdnProductionID").val("");
                alert(alertMsg);
                getTubs()
                return false;
            }
        });
    });

    //  $("#txtProductionWeight").on("keyup", function() {
    // var freezeQty = parseFloat($(this).val());
    // var balanceQty = parseFloat($("#hdnBalanceQty").val());
    // var hdnProductionID = $("#hdnProductionID").val();
    // var hdnProdcutionQty = parseFloat($("#hdnProdcutionQty").val());

    // var freezeQty = $(this).val();
    // var balanceQty = $("#hdnBalanceQty").val();
    // var hdnProductionID = $("#hdnProductionID").val();
    // var hdnProdcutionQty = $("#hdnProdcutionQty").val();
    // var totalBalanceQty = Number(balanceQty) + Number(hdnProdcutionQty);

    // if (freezeQty > totalBalanceQty) {
    //     $(this).val("");
    //     alert("Freezing Quantity not exceeds to Soaking Quantity");
    // }
    //});
</script>

<script>
    $('.drpSelectModal').select2({
        dropdownParent: $('#modalNewPalette')
    });


    $(document).on("change", "#drpStoreID", function () {
        var storeid = $(this).val();
        var blocksList = JSON.parse($("#hdnBlocks").val());

        var listBlock = "<option value=''>Select</option>";
        $.each(blocksList, function (index, val) {
            if (val.StoreID == storeid) {
                listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
            }
        });

        $("#drpBlockID").html(listBlock);
    })

    $("#frmPopup").validate({
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
            drpStoreID: {
                required: true
            },
            drpBlockID: {
                required: true
            },
            drpRackName: {
                required: true
            }
        },
        errorPlacement: function () {
            return false; // suppresses error message text
        },
        submitHandler: function () {

            $.ajax({
                url: 'Ajax/ajax-freezing-trackid.php',
                type: 'POST',
                data: {
                    methodType: "placePalette",
                    barCodeID: $.trim($("#hdnBarCodeID").val()),
                    rackID: $.trim($("#drpRackName").val())
                },
                success: function (data) {
                    $('#modalNewPalette').modal('hide');

                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);
                    }


                    return false;
                }
            });
        }
    });
    <?php
    $ClientID = !empty($_SESSION["ClientID"])?$_SESSION["ClientID"]:'';
    if($MCSRestriction == 1 ){?>
        $(document).on('change','#drpPackingType,#drpPoNumber,#drpTubNumber',function(e){
             e.preventDefault();
            checkAvailableMcs();
        })
        $(document).on('click','.selectVariety',function(e){
            e.preventDefault();
            checkAvailableMcs();
        })
        /* $("#txtProductionSlabs").on("keyup change keydown", function(e) {
            // e.preventDefault();
            checkAvailableMcs();
        }) */
       let typingTimer;
        $("#txtProductionSlabs").on("keyup", function () {
            // e.preventDefault();
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function () {
                checkAvailableMcs();
            }, 50);
        })
        /* $(document).on('click','#submit',function(e){
            e.preventDefault();
            checkAvailableMcs();
        }) */
    
        function checkAvailableMcs(){
            var drpPackingType = $('#drpPackingType').val();
            var TrackCode = $('#drpTrackCode').val();
            var PoNumber = $('#drpPoNumber').val();
            var Variety = $('#txtVariety').val();//alert(Variety);return;
            var drpPoNumberTxt = $("#drpPoNumber option:selected").text();
            var txtSlab = $('#txtProductionSlabs').val();
            var prodId = $('#hdnProductionID').val();
            var VarietyId = $('#txtVariety').attr('data-varietyid');
            var BalQty = $('#drpTubNumber option:selected').attr('data-balanceqty');
            var soakingqty = $('#drpTubNumber option:selected').attr('data-soakingqty');

            if(drpPackingType=='MASTER' && PoNumber!='' && Variety!='' && TrackCode!=''){
                $.ajax({
                    url: 'Ajax/ajax-freezing-trackid.php',
                    type: 'POST',
                    data: {
                        methodType: "getTotalSlabs",
                        PoNumber: $.trim(PoNumber),
                        Variety: $.trim(Variety),
                        TrackID: $.trim(TrackCode),
                        prodId: $.trim(prodId),
                        VarietyId:VarietyId,
                    },
                    dataType:'json',
                    success: function(data) {
                        var remain_mcs =parseInt(data.slabData.RemainMcs);
                        var slabs =parseInt(data.slabData.Slabs);
                        var remain_slabs =parseInt(data.slabData.RemainSlabs);
                        var remain_slabs1 = parseInt(remain_slabs)-parseInt(txtSlab);
                        $('#slabInfo').text('(Balance Slabs :'+data.slabData.RemainSlabs+', Store Qty :'+data.slabData.BalQty+')').css('color','green').attr('data-qty',data.slabData.BalQty)


                        if(remain_slabs1 <0){
                            $('#txtProductionSlabs').val('')
                            alert('Weight exceeds the allowed limit,Balance slabs ('+data.slabData.RemainSlabs+')')
                            $('#submit').attr('disabled',true)
                        }else{
                            $('#submit').attr('disabled',false)
                        }
                    }
                });
            }else{
                 $('#slabInfo').text('').attr('data-qty',0)
                $('#submit').attr('disabled',false)
            }
        }
        function checkAvailableMcsNew(){
            var drpPackingType = $('#drpPackingType').val();
            var TrackCode = $('#drpTrackCode').val();
            var PoNumber = $('#drpPoNumber').val();
            var Variety = $('#txtVariety').val();//alert(Variety);return;
            var drpPoNumberTxt = $("#drpPoNumber option:selected").text();
            var txtSlab = $('#txtProductionSlabs').val();
            var prodId = $('#hdnProductionID').val();
            var VarietyId = $('#txtVariety').attr('data-varietyid');
            var PoGradeId = $('#txtVariety').attr('data-po-grade-id');
            var BalQty = $('#drpTubNumber option:selected').attr('data-balanceqty');
            var soakingqty = $('#drpTubNumber option:selected').attr('data-soakingqty');
            var drpPackingStyle = $('#drpPackingStyle').val();

            if(drpPackingType == 'MASTER'  && PoNumber!='' && Variety!='' && TrackCode!=''){
                $.ajax({
                    url: 'Ajax/ajax-freezing-trackid.php',
                    type: 'POST',
                    data: {
                        methodType: "getTotalSlabs",
                        PoNumber: $.trim(PoNumber),
                        Variety: $.trim(Variety),
                        TrackID: $.trim(TrackCode),
                        prodId: $.trim(prodId),
                        VarietyId:VarietyId,
                        PackingType:drpPackingType,
                        txtSlab:txtSlab,
                        drpPackingStyle:drpPackingStyle,
                        PoGradeId:PoGradeId
                    },
                    dataType:'json',
                    success: function(data) {
                        var remain_mcs =parseInt(data.slabData.RemainMcs);
                        var slabs =parseInt(data.slabData.Slabs);
                        var remain_slabs =parseInt(data.slabData.RemainSlabs);
                        var remain_slabs1 = parseInt(remain_slabs)-parseInt(txtSlab);
                        //if(txtSlab == 0){
                            $('#slabInfo').text('(Bal Slabs :'+data.slabData.RemainSlabsOld+', Bal PO Qty :'+data.slabData.BalQty+', Store Qty :'+data.slabData.StoreQty+')').css('color','green').attr('data-qty',data.slabData.StoreQty)
                        //}
                        
                        //if(remain_slabs1 < 0){
                        if(remain_slabs < 0){
                            $('#txtProductionSlabs').val('')
                            alert('Weight exceeds the allowed limit,Balance Weight ('+data.slabData.BalQty+')')
                            $('#submit').attr('disabled',true)
                        }else{
                            $('#submit').attr('disabled',false)
                        }
                    }
                });
            }else{
                 $('#slabInfo').text('').attr('data-qty',0)
                $('#submit').attr('disabled',false)
            }
        }
    <?php }?>
    <?php 
    if($MixedBatchCode){?>
        $(document).on("change", "#drpTrackCode", function () {

            let track = $('#drpTrackCode').val();
           // let tub   = $('#drpTubNumber').val();

            if (track) {
                $('#txtMixedBatchCode, #txtWeight')
                    .prop({ disabled: true, required: false }).val('');
            } else {
                $('#txtMixedBatchCode, #txtWeight')
                    .prop({ disabled: false, required: true });
            }

        });
        //$(document).on("keyup", "#txtMixedBatchCode", function () {
        $('#txtMixedBatchCode').focusout(function() {
            $('#txtMcs').removeAttr('data-avl_mcs')
            $('#txtLooseBags').removeAttr('data-avl_loose')
            if($(this).val()){
                $('#drpTrackCode, #drpTubNumber, #txtProductionSlabs, #txtMcs, #txtLooseBags').prop({ disabled: true, required: false}).val('').trigger("change") 
                $('#txtWeight').prop('required',true)
                $('#txtBalanceMessage').text('')
                if(!$("#hdnProductionID").val()){
                    getProdDataByProdDate();
                }
            }else{
                var confrm = false;
                //if(confirm('Are you sure you want to clear the form data?')){
                    confrm = true;
                //}
                $('#drpTrackCode, #drpTubNumber, #txtProductionSlabs, #txtMcs, #txtLooseBags').prop({ disabled: false, required: true})
               <?php 
                if($EnableMcsAndLooseAtFreezingTrackPage == 1){?>
                    $('#txtProductionSlabs').prop({ disabled: true, required: false}).val('').trigger("change") 
                <?php } ?>
                $('#txtWeight').prop('required',false)
                if(!$("#hdnProductionID").val() || confrm){
                    $("#drpAbStatus, #drpProductCategory, #drpPackingType,#drpGrade,#drpFreezingType,#drpSoaking").val('').trigger("change");
                    //  $("#drpShift").val('');
                    $('#drpShift').prop('selectedIndex', 0);
                    $("#hdnProductionID, #txtRejectionWeight,#txtWeight,#txtRemarks,#txtStartTimer,#txtEndTimer,#txtVariety").val('');
                    $('#txtVariety').removeAttr('varietyid po-grade-id');
                    $('#divDataList').html('')
                }
            }
        })
       /*  $('#txtMixedBatchCode').focusout(function() {
            if($(this).val()){
                getProdDataByProdDate();
            }
        }); */
    <?php } ?>
    <?php 
    if($EnableMcsAndLooseAtFreezingTrackPage == 1){?>
        $(document).on("change", "#drpPackingStyle", function () {
            $('#txtMcsMsg, #txtLooseBagsMsg').text('');
            if($('#txtMcs').attr('data-edit_click') == 0){               
                $('#txtProductionSlabs, #txtMcs, #txtLooseBags').val(0);
            }
            $('#txtMcs').removeAttr('data-avl_mcs data-edit_click')
            $('#txtLooseBags').removeAttr('data-avl_loose')
            var soakingqty = parseFloat($('#drpTubNumber').find(':selected').attr('data-soakingqty')) || 0;
            var BalanceQty = parseFloat($('#drpTubNumber').find(':selected').attr('data-balanceqty')) || 0;
            var allowedfreezingweight = parseFloat($('#drpTubNumber').find(':selected').attr('data-allowedfreezingweight')) || 0;

            // if(BalanceQty < 0){
            //     var weight = soakingqty + Math.abs(BalanceQty);//150+(-2) = 152
            // }else{
            //      var weight = soakingqty - BalanceQty;//150-2=148
            // }
            // BalanceQty -= parseFloat(weight) || 0;//180-152/180-148

            var productionQty = parseFloat($('#hdnProdcutionQty').val()) || 0;

            BalanceQty = BalanceQty < 0 
                ? BalanceQty - productionQty 
                : BalanceQty + productionQty;
            if(productionQty){
               if (BalanceQty > 0) {
                    $("#txtBalanceMessage").text("(Balance Qty : " + (parseFloat(BalanceQty) || 0).toFixed(3) + " KGs )").css("color", "green");
                } else {
                    $("#txtBalanceMessage").text("(Balance Qty : " + (parseFloat(BalanceQty) || 0).toFixed(3) + " KGs )").css("color", "red");
                }
            }
            if($(this).val() && BalanceQty){
                if(BalanceQty > 0){
                    $('#txtMcsMsg, #txtLooseBagsMsg').css("color", "green")
                }else{
                    $('#txtMcsMsg, #txtLooseBagsMsg').css("color", "red")
                    var BalanceQty = Math.abs(BalanceQty);
                } 
                var SlabWeight = $(this).find(':selected').attr('data-slabweight');
                var MFactor = $(this).find(':selected').attr('data-mfactor');
                var TotalSlabs = $(this).find(':selected').attr('data-totalslabs');
                var Slabs = parseFloat(
                                        (BalanceQty / (SlabWeight * MFactor)).toFixed(3)
                                        );
                var Mcs = Math.floor(Slabs / TotalSlabs);
                // var Loose = Math.ceil(Slabs - (Mcs * TotalSlabs));
                var Loose = (Slabs - (Mcs * TotalSlabs)).toFixed(3);
                
                               
                $('#txtMcsMsg').text(' ( Avl Mcs :'+Mcs+' ) ');
                $('#txtLooseBagsMsg').text(' ( Avl Loose Bags:'+Loose+' ) ');
               // $('#txtProductionSlabs').val(Slabs).trigger('input').trigger('change');
                $('#txtMcs').attr('data-avl_mcs',Mcs);
                $('#txtLooseBags').attr('data-avl_loose',Loose);

                // console.log('BalanceQty:'+BalanceQty+',MFactor:'+MFactor+',TotalSlabs:'+TotalSlabs)
                // console.log('Slabs:'+Slabs+',Mcs:'+Mcs+',Loose:'+Loose)
            }
        })
        $(document).on("keyup", "#txtMcs", function () {
            var avlMcs = parseInt($(this).attr('data-avl_mcs')) || 0

            var SlabWeight = $('#drpPackingStyle').find(':selected').attr('data-slabweight');
            var MFactor = $('#drpPackingStyle').find(':selected').attr('data-mfactor');
            var TotalSlabs = $('#drpPackingStyle').find(':selected').attr('data-totalslabs');
            var LooseBags =parseFloat( $('#txtLooseBags').val()) || 0;
            //console.log(SlabWeight+' '+MFactor+' '+TotalSlabs+' '+LooseBags);
            if($(this).val() > avlMcs){
                alert('MCS limit exceeded. Available MCS: '+avlMcs+'.')
                $(this).val(0)
            }
            var Slabs = ((parseFloat($(this).val()) * parseFloat(TotalSlabs)) + parseFloat(LooseBags));
            $('#txtProductionSlabs').val(Slabs);
            
        })
        $(document).on("keyup", "#txtLooseBags", function () {
            var avlLoose = parseFloat($(this).attr('data-avl_loose')) || 0

            var SlabWeight = $('#drpPackingStyle').find(':selected').attr('data-slabweight');
            var MFactor = $('#drpPackingStyle').find(':selected').attr('data-mfactor');
            var TotalSlabs = $('#drpPackingStyle').find(':selected').attr('data-totalslabs');
            var txtMcs = parseInt($('#txtMcs').val()) || 0;

            if($(this).val() > avlLoose){
                alert('Loose bags exceeded. Available: '+avlLoose+'.')
                $(this).val(0)
            }
            //var Slabs = ((txtMcs * TotalSlabs) + $(this).val());
            var Slabs = (parseFloat(txtMcs) * parseFloat(TotalSlabs)) + parseFloat($(this).val());
            // var entrLoose = $(this).val();
            // $(this).val(parseFloat(entrLoose))
            $('#txtProductionSlabs').val(Slabs);
        })
   <?php } ?>
   <?php 
    if(!empty($LoadingLinersBasedOnPlant)){?>
        $(document).on("change", "#drpProductionPlant", function () {
            if($(this).val()){
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
                            var selectedValue = $("#drpLineNumber").val();
                            $.each(data.Data, function (index, value) {
                                var selected = (selectedValue == value.Name) ? "selected" : "";
                                list += "<option value='" + value.Name + "' "+selected+">" + value.Name + "</option>";
                            });
                        }
                        $('#drpLineNumber').html(list)
                    }
                });
            }
        })
    <?php } ?>
</script>

</body>

</html>