<?php

session_start();
$PageTitle = "VA Quality Check";
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once ROOT . 'config/api-call.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';




$api = API_QC_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");
$resVariety = $resDropdownsAPI->Data->HlVaBillVariety;
//echo '<pre>';print_r($resDropdownsAPI->Data->Grade);die;
$resSupervisor = $resDropdownsAPI->Data->Supervisor;
// $resImproperPeeling = $resDropdownsAPI->Data->ImproperPeeling;
// $resImproperCutting = $resDropdownsAPI->Data->ImproperCutting;
$resVein = $resDropdownsAPI->Data->Vein;
$resBroken = $resDropdownsAPI->Data->Broken;
$resOtherObjectionDefects = $resDropdownsAPI->Data->OtherObjectionDefects;
$resCorrectiveAction = $resDropdownsAPI->Data->CorrectiveAction;
$resGrade = $resDropdownsAPI->Data->Grade;
$resTypeOfCut = $resDropdownsAPI->Data->TypeOfCut;
$resCenter = $resDropdownsAPI->Data->Center;

$List = $resDropdownsAPI->Data;


$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$ShowDeheadingStationID = $ArrayClientConfigData['ShowDeheadingStationID'];

$sql_LAB_USERS = "SELECT * FROM QcLabTechnologist where LbuStatus=1 ";
$stmtVerifiedBy = $iconn->query($sql_LAB_USERS);
$stmtInspectedBy = $iconn->query($sql_LAB_USERS);
$stmtCheckedBy = $iconn->query($sql_LAB_USERS);

// echo print_r($stmtVerifiedBy );
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
                                <form id="frmQuantiryCheck">
                                    <div class="row">

                                        <input type="hidden" id="hdnID" name="hdnID" value="" />
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Value Addition Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtArrivalDate"
                                                name="txtArrivalDate" placeholder=""
                                                value="<?php echo date("Y-m-d"); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
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
                                                            <option value="<?php echo $Item->UnitName; ?>" <?php echo $selectedDropdown; ?>>
                                                                <?php echo $Item->UnitName; ?>
                                                            </option>
                                                        <?php

                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                            <?php endif; ?>
                                        <?php } ?>

                                        
                                         <!-- <input type="hidden" id="hdnID" name="hdnID" value="" />
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Value Addition Date<span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtArrivalDate"
                                                name="txtArrivalDate" placeholder=""
                                                value="<?php echo date("Y-m-d"); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div> -->
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="BatchNoType" class="form-label">Type Of Batch</label>
                                            <select class="drpSelect form-control select2 BatchNoType" name="BatchNoType" id="BatchNoType">
                                                <option value="Arrival">Arrival</option>
                                                <option value="Reglaze">Reglaze</option>
                                                <option value="Melting">Melting</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="ReglazeBatchNo" class="form-label">Batch No<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="ReglazeBatchNo" name="ReglazeBatchNo"
                                                placeholder="Enter Reglaze Batch No" value="" style="display:none">
                                            <select class="drpSelect form-control  BatchCode form-control " name="drpBatchCode"
                                                id="drpBatchCode" requireds  multiple="multiple" required> 
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                    

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtRoomTemperature" class="form-label">Room Temperature
                                                <span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtRoomTemperature"
                                                name="txtRoomTemperature">

                                        </div>


                                        <div class="form-group col-sm-3">
                                            <label for="txtTableNo" class="form-label">Table No<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtTableNo" name="txtTableNo" />
                                        </div>

                                        <!-- <div class="form-group col-sm-3">
                                            <label for="drpVariety" class="form-label">Variety <span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="drpVariety" name="drpVariety"
                                                disabled />
                                        </div> -->

                                        <div class="form-group col-sm-3">
                                            <label for="drpVariety" class="form-label">Variety <span style="color:red">*</span></label>
                                            <select class="form-control drpSelect select2" id="drpVariety" name="drpVariety" >
                                                <option value="">Select</option>
                                                <!-- Options will be added dynamically -->
                                            </select>
                                        </div>

                                       <!-- <div class="form-group col-sm-3 mb-3">
                                            <label for="txtCount" class="form-label">HL Count</label>
                                            <input type="text" class="form-control" id="txtCount" name="txtCount"
                                                placeholder="Enter Count" >
                                        </div> -->

                                     

                                          <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="drpGrade" class="form-label">Grade
                                        <!-- <span style="color:red">*</span> -->
                                    </label>
                                    <select class="drpSelect form-control select2" name="drpGrade" id="drpGrade">
                                        <option value="">Select</option>
                                    </select>
                                </div>

                                      

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtTypeOfCut" class="form-label">Type Of Cut<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="txtTypeOfCut"
                                                id="txtTypeOfCut">
                                                <option value=""> Select</option>
                                                <?php if (!empty($resTypeOfCut)) { ?>
                                                <?php foreach ($resTypeOfCut as $Item) { ?>
                                                    <option value="<?php echo $Item->ID ?>">
                                                      <?php echo $Item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            <?php } ?>
                                            </select>
                                        </div>


                                        <div class="form-group col-sm-3 mb-3">
                                        <label for="txtImproperPeeling" class="form-label">Improper Peeling</label>
                                        <input type="text" class="form-control" id="txtImproperPeeling" name="txtImproperPeeling" 
                                        placeholder="Enter Improper Peeling" />
                                    </div>

                                    <div class="form-group col-sm-3 mb-3">
                                        <label for="txtImproperCutting" class="form-label">Improper Cutting</label>
                                        <input type="text" class="form-control" id="txtImproperCutting" name="txtImproperCutting"
                                         placeholder="Enter Improper Cutting" />
                                    </div>



                                          <div class="form-group col-sm-3 mb-3">
                                        <label for="drpVein" class="form-label">Vein</label>
                                        <input type="text" class="form-control" id="drpVein" name="drpVein"
                                         placeholder="Enter Vein " />
                                    </div>

                                    

                                          <div class="form-group col-sm-3 mb-3">
                                        <label for="txtBroken" class="form-label">Broken</label>
                                        <input type="text" class="form-control" id="txtBroken" name="txtBroken"
                                         placeholder="Enter Broken " />
                                    </div>

                                     

                                          <div class="form-group col-sm-3 mb-3">
                                        <label for="txtOtherObjectionDefects" class="form-label">Other Objection Defects</label>
                                        <input type="text" class="form-control" id="drpOtherObjectionDefects" name="drpOtherObjectionDefects"
                                         placeholder="Enter OtherObjectionDefects " />
                                    </div>


                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtTotalPeelingGirls" class="form-label">Total Peeling
                                                Girls</label>
                                            <input type="text" class="form-control" id="txtTotalPeelingGirls"
                                                name="txtTotalPeelingGirls">
                                        </div>


                                        <!-- <div class="form-group col-sm-3 mb-3">
                                            <label for="txtHeadLessQuantity" class="form-label">Head Less
                                                Quantity</label>
                                            <input type="text" class="form-control" id="txtHeadLessQuantity"
                                                name="txtHeadLessQuantity">
                                        </div> -->

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtCount" class="form-label">Head Less Quantity</label>
                                            <input type="text" class="form-control" id="txtCount" name="txtCount"
                                                placeholder="Enter Count" >
                                        </div>


                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtQuantity" class="form-label">Quantity</label>
                                            <input type="text" class="form-control" id="txtQuantity" name="txtQuantity">
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtYield" class="form-label">Yield(%)</label>
                                            <input type="text" class="form-control" id="txtYield" name="txtYield">
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtCheckingGirls" class="form-label">Checking Girls</label>
                                            <input type="text" class="form-control" id="txtCheckingGirls"
                                                name="txtCheckingGirls">

                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtSuperVisor" class="form-label">SuperVisor</label>
                                            <input type="text" class="form-control" id="txtSuperVisor"
                                                name="txtSuperVisor">
                                            <!-- value="Nill -->
                                            <!-- <input type="text" placeholder="Enter Verified By" class="form-control" name='SuperVisor' id="SuperVisor"
                                                    value="K.L. Ganesh"> -->
                                        </div>


                                         <div class="form-group col-sm-3">
                                                <label for="drpInspectedBy">Inspected By</label>
                                                <select class="drpSelect form-control select2" name="drpInspectedBy" id="drpInspectedBy">
                                                    
                                                    <?php while ($RowVerifiedBy = $stmtVerifiedBy->fetch_array()) { ?>
                                                        <option value="<?php echo $RowVerifiedBy['LbuName'] ?>">
                                                            <?php echo $RowVerifiedBy['LbuName'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            
                                        <div class="form-group col-lg-3">
                                            <label for="txtVerifiedBy">Verified By<span
                                                    style="color:red">*</span></label>
                                            <input type="text" placeholder="Enter Verified By" class="form-control"
                                                name='txtVerifiedBy' id="txtVerifiedBy" value="K.L.Ganesh">
                                        </div>


                                          

                                        


                                  <!-- <div class="form-group col-sm-3">
                                                <label for="drpCheckedBy">Checked By</label>
                                                <select class="drpSelect form-control select2" name="drpCheckedBy" id="drpCheckedBy">
                                                    <option value="" disabled selected>Select </option>
                                                    <?php while ($RowCheckedBy = $stmtCheckedBy->fetch_array()) { 
                                                        $name = $RowCheckedBy['LbuName'];
                                                    ?>
                                                        <option value="<?php echo $name; ?>">
                                                            <?php echo $name; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>   -->

                                       



                                          <div class="form-group col-sm-3 mb-3">
                                            <label for="drpCorrectiveAction" class="form-label">Corrective Action</label>
                                            <select class="drpSelect form-control" name="drpCorrectiveAction"
                                                id="drpCorrectiveAction">
                                                <option value=""> Select</option>
                                                <?php if (!empty($resCorrectiveAction)) { ?>
                                                <?php foreach ($resCorrectiveAction as $Item) { ?>
                                                    <option value="<?php echo $Item->ID ?>">
                                                      <?php echo $Item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            <?php } ?>
                                            </select>
                                        </div>


                                             <div class="form-group col-sm-3 mb-3">
                                            <label for="txtTableTemperature" class="form-label">Table Temperature</label>
                                            <input type="text" class="form-control" id="txtTableTemperature"
                                                name="txtTableTemperature">

                                        </div>

                                                    <div class="form-group col-sm-3 mb-3">
                                            <label for="txtCrateTemperature" class="form-label">Crate Temperature</label>
                                            <input type="text" class="form-control" id="txtCrateTemperature"
                                                name="txtCrateTemperature">

                                        </div>

                                                 <div class="form-group col-sm-3 mb-3">
                                            <label for="txtNumberSamplesChecked" class="form-label">Number Of Samples Checked</label>
                                            <input type="text" class="form-control" id="txtNumberSamplesChecked"
                                                name="txtNumberSamplesChecked">

                                        </div>

                                         <div class="form-group col-sm-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks<span
                                                    style="color:red"></span></label>
                                            <input type="text" class="form-control" id="txtRemarks"
                                                name="txtRemarks">
                                        </div>
                                        
                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType"
                                                value="<?php echo $ArrivalID ?>" />

                                            <input type="hidden" id="hdnSelected" name="hdnSelected" value="" />
                                            <input type="hidden" id="hdnBreakUpID" name="hdnBreakUpID" value="" />
                                            <button class="btn btn-primary" name="submit" id="submit" type="submit"
                                                data-breakupid="">Save</button>
                                            <!-- <button class="btn btn-danger" id="btnCancel" disabled
                                                type="button">Cancel</button> -->


                                        </div>
                                </form>
                            </div>
                        </div>

                        <div id="divDataList">
                            <!-- -->
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
    $(document).on("change", "#txtArrivalDate", function () {
        getArrivals();
        loadData();
    });
    // $(document).on("change", "#drpBatchCode", function () {
    //     if ($("#drpBatchCode").val() != []) {
    //         var variety = $("#drpBatchCode option:selected").data("variety");
    //         $("#drpVariety").val(variety);
    //         loadData();
    //     }
    // });


    $(document).on("change", "#drpBatchCode", function () {
        if ($("#drpBatchCode").val() != "") {
            let selectedBatches = $("#drpBatchCode option:selected");
            let addedVarieties = new Set();
            // let addedCounts = new Set();

            let varietyList = [];

            // Collect varieties and counts
            selectedBatches.each(function () {
                let varieties = $(this).data("variety");
                // let hlCounts = $(this).data("hlcount");

                if (varieties) {
                    varieties.toString().split(",").forEach(variety => {
                        variety = variety.trim();
                        if (variety && !addedVarieties.has(variety)) {
                            varietyList.push(variety);
                            addedVarieties.add(variety);
                        }
                    });
                }

                // if (hlCounts) {
                //     hlCounts.toString().split(",").forEach(count => {
                //         count = count.trim();
                //         if (count && !addedCounts.has(count)) {
                //             addedCounts.add(count);
                //         }
                //     });
                // }
            });

            // Empty and conditionally add "Select Variety" option
            $("#drpVariety").empty();
            if (varietyList.length > 1) {
                $("#drpVariety").append('<option value="">Select </option>');
            }

            varietyList.forEach(variety => {
                $("#drpVariety").append(
                    $("<option>", {
                        value: variety,
                        text: variety
                    })
                );
            });

            // Auto-select only if exactly one variety
            if (varietyList.length === 1) {
                $("#drpVariety").val(varietyList[0]).trigger("change");
            }

            // Auto-fill hlCount if only one value exists
            // if (addedCounts.size === 1) {
            //    $("#txtCount").val([...addedCounts][0]);
            // } else {
            //    $("#txtCount").val(""); // Clear if multiple or none
            // }

            loadData();
        }

    });
    $('#ReglazeBatchNo').on('input', function () {
        this.value = this.value.toUpperCase();
    });
    $(document).on("change", "#BatchNoType", function(e) {
        $('#ReglazeBatchNo').val('');
        if($(this).val() == 'Arrival'){
            getArrivals();
            $('#drpBatchCode').css('display','block').show().attr('required',true);
            $('#drpBatchCode').select2('destroy').select2();
            $('#drpBatchCode').next('.select2-container').css('display','block').show();
            $('#ReglazeBatchNo').css('display','none').hide().attr('required',false);
            $('#drpGrade,#drpVariety').html("<option value=''>Select</option>");
        }else{
            $('#drpBatchCode').css('display','block').show().attr('required',false).select2('destroy').val(null).select2().next('.select2-container').css('display','none').hide();
            $('#ReglazeBatchNo').css('display','block').show().attr('required',true);
            getGradeForReglze()
            getVaraityForReglze()
            if($(this).val() == 'Reglaze'){
                $('#ReglazeBatchNo').attr('placeholder','Enter Reglaze Batch No').show();
            }else{
                $('#ReglazeBatchNo').attr('placeholder','Enter Melting Batch No').show();
            }
        }
    })
    /* $(document).on("change", "#ReglazeBatchNo", function(e) {
        var ReglazeBatchNo = ($(this).val())?$(this).val():'';
        $('#drpGrade,#drpVariety').html("<option value=''>Select</option>");
        if(ReglazeBatchNo){
            getGradeForReglze()
            getVaraityForReglze()
            $('#drpBatchCode').prop("disabled", true).select2('destroy').val(null).select2();
        }else{
            $('#drpBatchCode').prop("disabled", false);
            getArrivals();
        }
    })
    $(document).on("change", "#drpBatchCode", function(e) {
        var BatchCode = ($(this).val())?$(this).val():'';console.log(BatchCode)
        if(BatchCode.length != 0){
            $('#ReglazeBatchNo').prop("readonly", true).val('');
        }else{
            $('#ReglazeBatchNo').prop("readonly", false);
        }
    }) */
   $(document).on("change", "#drpVariety", function () {
        if($('#BatchNoType').val() == 'Arrival' &&  $('#drpVariety').val() && $('#drpBatchCode').val()){
            $.ajax({
                url: 'Ajax/ajax-quantity-check-value.php',
                type: 'POST',
                data: {
                    arrivalDate: $('#txtArrivalDate').val(),
                    batchCode: $('#drpBatchCode').val() ? $('#drpBatchCode').val().join(',') : '',
                    variety: $('#drpVariety').val(),
                    methodType: "getCounts",
                },
                success: function (data) {
                    var list = "<option value=''>Select</option>";
                    if (data?.Data && data.Data.length > 0) {
                        $.each(data.Data, function (index, value) {
                            var count = value.SentCount;
                            var weights = value.WeightWithGrade.split(',');
                            $.each(weights, function (index, value) {
                                var datas = value.split('@');
                                // list += "<option value='" + datas[1] + "' data-weight='" + datas[0] + "'>" + datas[1] + "(" + count + ")</option>";
                            list += "<option value='" + datas[1] + "' data-weight='" + datas[0] + "' data-quantity='" + datas[2] + "'>" + datas[1] + "(" + count + ")</option>";

                            });
                            
                        });
                    }

                    $('#drpGrade').select2('destroy');
                    $('#drpGrade').html(list);
                    $('#drpGrade').select2();
                    return false;
                }
            });
        }
    });

    $(document).on("change", "#drpGrade", function () {
        var selectedWeight = $(this).find('option:selected').data('weight');
          var selectedQuantity = $(this).find('option:selected').data('quantity');
        $('#txtCount').val(selectedWeight).trigger('input');
          $('#txtQuantity').val(selectedQuantity).trigger('input');
        
    });


$(document).on("input", "#txtCount, #txtQuantity", function () {
    const quantity = parseFloat($("#txtCount").val()) || 0;         
    const headLessQty = parseFloat($("#txtQuantity").val()) || 0; 

    let yieldValue = 0;
    if (headLessQty > 0 && quantity > 0) {
        yieldValue = (headLessQty / quantity) * 100;
    }

    $("#txtYield").val(yieldValue.toFixed(2));
});


$('document').ready(function () {
        getArrivals();
        // getting Track Codes
        $('#txtVerifiedBy').on('change', function () {
            if ($(this).val() == 'Others') {
                $('#VerifiedByNew').show().css('display', 'block').attr('required', true);
            } else {
                $('#VerifiedByNew').hide().css('display', 'none').attr('required', false);
            }
        })
        $("#frmQuantiryCheck").validate({
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

                /* drpBatchCode: {
                    required: true
                }, */
                txtRoomTemperature: {
                    required: true
                },
                ArrivalDate: {
                    required: true
                },
                txtTableNo: {
                    required: true
                },
                drpVariety: {
                    required: true
                },
                // drpGrade: {
                //     required: true
                // },
                txtTypeOfCut: {
                    required: true
                }
            },
            messages: {
                /* drpBatchCode: {
                    required: "Please select a Batch."
                }, */

                txtRoomTemperature: {
                    required: "Please enter RoomTemperature"
                },
                OutTemperature: {
                    required: "Please enter OutTemperature."
                },
                ArrivalDate: {
                    required: "Please Coose ArrivalDate."
                },
                txtTableNo: {
                    required: "Please Select TableNo."
                },
                drpVariety: {
                    required: "Please Enter Variety ."
                },
                // drpGrade: {
                //     required: "Please Select Grade."
                // }

            },
            errorPlacement: function () {
                return false; // suppresses error message text
            },
            submitHandler: function () {
                $.ajax({
                    url: 'Ajax/ajax-quantity-check-value.php',
                    type: 'POST',
                    data: {
                        methodType: "TableSave",
                        QuantityCheckid: $("#hdnID").val(),
                        ArrivalDate: $("#txtArrivalDate").val(),
                        BatchCode: $('#drpBatchCode').val() ? $('#drpBatchCode').val().join(',') : '',
                        RoomTemperature: $("#txtRoomTemperature").val(),
                        HLCount: $("#txtCount").val(),
                        TableNo: $("#txtTableNo").val(),
                        Variety: $("#drpVariety").val(),
                        Grade: $("#drpGrade").val(),
                        TypeOfCut: $("#txtTypeOfCut").val(),
                        ImproperPeeling: $("#txtImproperPeeling").val(),
                        ImproperCutting: $("#txtImproperCutting").val(),
                        Vein: $("#drpVein").val(),
                        Broken: $("#txtBroken").val(),
                        OtherObjectionDefects: $("#drpOtherObjectionDefects").val(),
                        TotalPeelingGirls: $("#txtTotalPeelingGirls").val(),
                        HeadLessQuantity: $("#txtCount").val(),
                        Quantity: $("#txtQuantity").val(),
                        Yield: $("#txtYield").val(),
                        CheckingGirls: $("#txtCheckingGirls").val(),
                        SuperVisor: $("#txtSuperVisor").val(),
                        VerifiedBy: $("#txtVerifiedBy").val(),
                        InspectedBy: $("#drpInspectedBy").val(),
                        // CheckedBy: $("#drpCheckedBy").val(),
                        Remarks: $("#txtRemarks").val(),
                        CorrectiveAction: $("#drpCorrectiveAction").val(),
                        ReglazeBatchNo: $("#ReglazeBatchNo").val(),
                        TableTemperature: $("#txtTableTemperature").val(),
                        CrateTemperature: $("#txtCrateTemperature").val(),
                        NumberSamplesChecked: $("#txtNumberSamplesChecked").val(),
                        BatchNoType: $("#BatchNoType").val(),
                          VAStation: $("#drpVAStation").val(),

                    },
                    success: function (data) {
            if (data.Error) {
                alert(data.Error);
            } else {
                alert(data.Msg);
                var txtArrivalDate = $("#txtArrivalDate").val()
                $("#hdnID").val('');
                $('#frmQuantiryCheck')[0].reset();
                $("#txtArrivalDate").val(txtArrivalDate)
                $('#drpBatchCode').val(null).trigger('change');
                /* $('#drpVariety').val('').trigger('change');
                $('#drpGrade').val('').trigger('change'); */
                $("#drpVariety,#drpGrade").html('<option value="">Select </option>');
                 $('#drpCorrectiveAction').val('').trigger('change');
                $('#drpCheckedBy').val('').trigger('change');
                  $('#drpInspectedBy').val('').trigger('change');
                $("#txtCount").val('');
                $("#txtYield").val('');
                 $("#txtTypeOfCut").val('').trigger('change');
                  $("#drpVAStation").val('').trigger("change");
                //  $("#txtTypeOfCut").val('');
                //$('#drpBatchCode').prop('disabled',false).select2('destroy').val(null).select2();
                //$('#ReglazeBatchNo').val('').prop('readonly',false)                
                
                if($('#BatchNoType').val() != 'Arrival'){
                    $('#BatchNoType').val('Arrival').trigger('change');
                    $('#ReglazeBatchNo').css('display','none').hide().attr('required',false);
                     $('#drpBatchCode').css('display','block').show().attr('required',true).select2('destroy').val(null).select2().next('.select2-container').css('display','block').show();
                     getArrivals()
                }
                
                $("button[name='submit']").text("Save").removeClass("btn-warning").addClass("btn-primary");
            
                loadData();
                        }


                    }
                });
            }
        });
    });

    function loadData() {
        $.ajax({
            url: 'Ajax/ajax-quantity-check-value.php',
            type: 'POST',
            data: {
                arrivalDate: $('#txtArrivalDate').val(),
                batchCode: $('#drpBatchCode').val() ? $('#drpBatchCode').val().join(',') : '',
                methodType: "GET",
            },
            success: function (data) {
                if (data?.Error != '') {
                    alert(data.Error);
                } else {
                    fnSuccess(data.htmlRes);
                    return false;
                }
            }
        });
    }


      function loadData() {
        $.ajax({
            url: 'Ajax/ajax-quantity-check-value.php',
            type: 'POST',
            data: {
                arrivalDate: $('#txtArrivalDate').val(),
                batchCode: $('#drpBatchCode').val() ? $('#drpBatchCode').val().join(',') : '',
                methodType: "GET",
            },
            success: function (data) {
                if (data?.Error != '') {
                    alert(data.Error);
                } else {
                    fnSuccess(data.htmlRes);
                    return false;
                }
            }
        });
    }




           
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
                title: 'Quantity Value Added List',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                customize: function (xlsx) {
                var sheet = xlsx.xl.worksheets['sheet1.xml'];

                // Find the column(s) to fix (e.g., column A = A1, A2, ...)
                $('c[r^="A"]', sheet).attr('s', '55'); // 55 = text format
                }
            }],
        });
    }

    function getArrivals() {
        if($('#BatchNoType').val()=='Arrival'){
            $.ajax({
                url: 'Ajax/ajax-quantity-check-value.php',
                type: 'POST',
                data: {
                    arrivalDate: $('#txtArrivalDate').val(),
                    methodType: "QualityCheck",
                },
                success: function (data) {
                    var list = "<option value=''>Select</option>";
                    if (data?.Data && data.Data.length > 0) {
                        $.each(data.Data, function (index, value) {
                            list += "<option value='" + value.ArrivalId + "'  data-plantid='" + value.PlantID + "' data-variety='" + value.Variety + "' data-hlcount='" + value.SentCount + "'>" + value.ArrivalNumber + "</option>";
                        });
                    }

                    $('#drpBatchCode').select2('destroy');
                    $('#drpBatchCode').html(list);
                    $('#drpBatchCode').select2();
                    return false;
                }
            });
        }
    }

    function fnSuccess(data) {
        $("#divDataList").html(data);
        fnShowDataTable();
    }

    $(document).on("click", ".btnEdit", function () {

        if (confirm('Are you sure do you want to edit ?')) {
            
            var row = $(this).closest("tr");
            console.log("row",row);
             $("#hdnID").val($(this).data("id"));
             var QuantityCheckID = $(this).attr("data-id");
             console.log("QuantityCheckID",QuantityCheckID);
            // /var tdBatchCode = row.find(".tdBatchCode").text();
            //  var tdBatchCodeRaw = row.find(".tdBatchCode").data("value");
            //  var tdBatchCode = String(tdBatchCodeRaw || "");
            // var tdBatchCode = row.find(".tdBatchCode").data("value").split(',');
            var tdArrivalDate = row.find(".tdArrivalDate").text();
            var tdRoomTemperature = row.find(".tdRoomTemperature").text();
            var tdTableNo = row.find(".tdTableNo").text();
            var tdVariety = row.find(".tdVariety").text();
            var tdHLCount = row.find(".tdHLCount").text();
            var tdGrade = row.find(".tdGrade").data("id");
            var tdTypeOfCut = row.find(".tdTypeOfCut").data("id");
            var tdImproperPeeling = row.find(".tdImproperPeeling").text();
            var tdImproperCutting = row.find(".tdImproperCutting").text();
            var tdVein = row.find(".tdVein").text();
            var tdBroken = row.find(".tdBroken").text();
            var tdOtherObjectionDefects = row.find(".tdOtherObjectionDefects").text();
            var tdTotalPeelingGirls = row.find(".tdTotalPeelingGirls").text();
            var tdHeadLessQuantity = row.find(".tdHeadLessQuantity").text();
            var tdQuantity = row.find(".tdQuantity").text();
            var tdYield = row.find(".tdYield").text();
            var tdCheckingGirls = row.find(".tdCheckingGirls").text();
            var tdSuperVisor = row.find(".tdSuperVisor").text();
            var tdVerifiedBy = row.find(".tdVerifiedBy").text();
            var tdRemarks = row.find(".tdRemarks").text();
            var tdCorrectiveAction = row.find(".tdCorrectiveAction").data("id");
            //  var tdCheckedBy = row.find(".tdCheckedBy").data("id");
             var ReglazeBatchNo = row.find(".tdBatchCode").data("reglaze_batch_no");
             var BatchNo = row.find(".tdBatchCode").data("batch_no");
             var BatchNoType = row.find(".tdBatchCode").data("batch_no_type");
             var ArrivalDate = row.find(".tdArrivalDate").data("arrival_date");
              var tdTableTemperature = row.find(".tdTableTemperature").text();
              var tdCrateTemperature = row.find(".tdCrateTemperature").text();
              var tdNumberSamplesChecked = row.find(".tdNumberSamplesChecked").text();
               var tdVAStation = row.find(".tdVAStation").text();
              
              

            
            $('#txtArrivalDate').val(ArrivalDate)
            $("#hdnID").val(QuantityCheckID);
            $("#hdnID").val(QuantityCheckID);
            // $("#drpBatchCode").val(tdBatchCode).trigger("change");
            // $("#drpBatchCode").val(tdBatchCode).trigger("change");
            $("#txtRoomTemperature").val(tdRoomTemperature).trigger("change");
            $("#txtTableNo").val(tdTableNo).trigger("change");
           
            $("#txtCount").val(tdHLCount).trigger("change");
           
            $("#txtTypeOfCut").val(tdTypeOfCut).trigger("change");
            $("#txtImproperPeeling").val(tdImproperPeeling).trigger("change");
            $("#txtImproperCutting").val(tdImproperCutting).trigger("change");
            $("#drpVein").val(tdVein).trigger("change");
            $("#txtBroken").val(tdBroken).trigger("change");
            $("#drpOtherObjectionDefects").val(tdOtherObjectionDefects).trigger("change");
            $("#txtTotalPeelingGirls").val(tdTotalPeelingGirls).trigger("change");
            $("#txtHeadLessQuantity").val(tdHeadLessQuantity).trigger("change");
            $("#txtQuantity").val(tdQuantity).trigger("change");
            $("#txtYield").val(tdYield).trigger("change");
            $("#txtCheckingGirls").val(tdCheckingGirls).trigger("change");
            $("#txtSuperVisor").val(tdSuperVisor).trigger("change");
            $("#txtRemarks").val(tdRemarks).trigger("change");
            $("#drpCorrectiveAction").val(tdCorrectiveAction).trigger("change");
            $("#txtTableTemperature").val(tdTableTemperature).trigger("change");
             $("#txtCrateTemperature").val(tdCrateTemperature).trigger("change");
            $("#txtNumberSamplesChecked").val(tdNumberSamplesChecked).trigger("change");
           // $('#drpBatchCode').prop('disabled',false).select2('destroy').val(null).select2();
            $('#ReglazeBatchNo').val('').prop('readonly',false)
            $("#txtNumberSamplesChecked").val(tdNumberSamplesChecked).trigger("change");
            $('#BatchNoType').select2('destroy').val(BatchNoType).select2();
              $("#drpVAStation").val(tdVAStation).trigger("change");
            if(BatchNoType != 'Arrival'){
                getGradeForReglze(tdGrade)
                getVaraityForReglze(tdVariety)
                $('#drpBatchCode').css('display','none').hide().attr('required',false).select2('destroy').val(null).select2().next('.select2-container').css('display','none').hide();
                $('#ReglazeBatchNo').css('display','block').show().attr('required',true).val(ReglazeBatchNo);
            }
            else{
               // $('#ReglazeBatchNo').prop('readonly',true)

                $('#drpBatchCode').css('display','block').show().attr('required',true);
                $('#drpBatchCode').select2('destroy').val(BatchNo).select2().trigger("change");
                $('#drpBatchCode').next('.select2-container').css('display','block').show();
                $('#ReglazeBatchNo').css('display','none').hide().attr('required',false);

                 $("#drpVariety").val(tdVariety).trigger("change");
                setTimeout(function() {                   
                    $('#drpGrade').select2('destroy').val(tdGrade).select2();
                   // getArrivals(BatchNo)
                }, 1000);
            }
            $("button[name='submit']").text("Update").removeClass("btn-primary").addClass("btn-primary");
            $(window).scrollTop(0);
        }
    });

$(document).on("click", ".btnDelete", function () {
    if (confirm("Are you sure you want to delete this item?")) {
        var button = $(this); // Save reference to clicked button
        var QuantityCheckid = button.attr("data-id");

        $.ajax({
            url: 'Ajax/ajax-quantity-check-value.php',
            type: 'POST',
            data: {
                methodType: "deleteRow",
                QuantityCheckid: QuantityCheckid
            },
            success: function (data) {
                if (data.Error) {
                    alert(data.Error);
                } else {
                    alert(data.Msg);
                    loadData();

                  
                    button.closest("tr").remove();

                    // Reset the form if open
                    $('#frmQuantiryCheck')[0].reset();
                    $("button[name='submit']").text("Save").removeClass("btn-primary").addClass("btn-primary");
                }
            }
        });
    }
});
    
function getVaraityForReglze( Variety=''){
    var Veraities = <?php echo json_encode($resVariety);?>;
    //console.log(Veraities)
    var list = "<option value=''>Select</option>";
    $.each(Veraities, function(index, value) {
        list += "<option value='" + $.trim(value.ShortName) + "'>" + value.ShortName + "</option>";
    });
    $('#drpVariety').select2('destroy').html(list).select2();
    if (Variety) {
        setTimeout(function() {
            $('#drpVariety').select2('destroy').val(Variety).select2();
        }, 500);
    }
}
function getGradeForReglze(Grade = '') {
    var Grades = <?php echo json_encode($resGrade);?>;
    //console.log(Grades)
    var list = "<option value=''>Select</option>";
    $.each(Grades, function(index, val) {
        list += "<option value='" + $.trim(val.value) + "'>" + val.value + "</option>";
    });
    $('#drpGrade').select2('destroy').html(list).select2();
    if (Grade) {
        setTimeout(function() {
            $('#drpGrade').select2('destroy').val(Grade).select2();
        }, 500);
    }
}



</script>


</body>

</html>