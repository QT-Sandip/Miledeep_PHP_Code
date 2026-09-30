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
                                <form id="frmCreateHarvest">
                                    <div class="row">

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBookingDate" class="form-label">Booking Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtBookingDate"
                                                name="txtBookingDate"
                                                value="<?php echo isset($HarvestData->BookingDate) ? $HarvestData->BookingDate : '' ?>"
                                                max="<?php echo date('Y-m-d'); ?>" readonly>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBookingNumber" class="form-label">Booking Number<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtBookingNumber"
                                                name="txtBookingNumber" placeholder="Enter Booking Number"
                                                value="<?php echo strtoupper($HarvestData->BookingID) ?>" readonly style=" text-transform: uppercase;" />
                                            <input type="hidden" id="hdnBookingID" name="hdnBookingID"
                                                value="<?php echo strtoupper($BookingID) ?>" style=" text-transform: uppercase;" />
                                        </div>

                                        <?php if ($hasHarvestDate == 1) { ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtHarvestDate" class="form-label">Harvest Date<span
                                                        style="color:red">*</span> </label>
                                                <input type="date" class="form-control" id="txtHarvestDate"
                                                    name="txtHarvestDate"
                                                    value="<?php echo isset($HarvestData->HarvestDate) ? $HarvestData->HarvestDate : '' ?>"
                                                    max="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        <?php } ?>

                                        <div class="form-group col-sm-3">
                                            <label for="drpGraderName" class="form-label">Supervisor<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpGraderName"
                                                id="drpGraderName" multiple="multiple">
                                                <?php
                                                $selectedGraders = explode(',', $HarvestData->SupervisorORGrader); // Convert string to array
                                                ?>
                                                <option value="">Select</option>
                                                <option value="0" <?php echo (strpos($HarvestData->SupervisorORGrader, '0') !== false) ? 'selected' : ''; ?>>Others</option>
                                                <?php foreach ($resRmGraders as $Item) { ?>
                                                    <option value="<?= htmlspecialchars($Item->UserID); ?>"
                                                        <?= in_array($Item->UserID, $selectedGraders) ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($Item->UserName); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtFarmerName" class="form-label">Farmer Name<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtFarmerName"
                                                name="txtFarmerName" placeholder="Enter Farmer Name"
                                                value="<?php echo $HarvestData->FarmerName ?>" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtPhoneNumber" class="form-label">Farmer Mobile No<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtPhoneNumber"
                                                name="txtPhoneNumber" placeholder="Enter Phone Number"
                                                value="<?php echo $HarvestData->PhoneNumber ?>" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtSealNumber" class="form-label">Seal Number <span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtSealNumber"
                                                name="txtSealNumber" placeholder="Enter Seal Number"
                                                value="<?php echo $HarvestData->SealNumber ?>" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpBoysGroup" class="form-label">Boys Group<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpBoysGroup" id="drpBoysGroup">
                                                <option value="" <?php echo ($HarvestData->BoysGroupID == '') ? 'selected' : ''; ?>> Select</option>
                                                <?php foreach ($resBoysGroup as $Item) { ?>
                                                    <option value="<?php echo $Item->GroupID; ?>" <?php echo ($HarvestData->BoysGroupID == $Item->GroupID) ? 'selected' : ''; ?>>
                                                        <?php echo $Item->GroupName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtWorkingBoys" class="form-label">Working Boys<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtWorkingBoys"
                                                name="txtWorkingBoys" placeholder="Enter Working Boys"
                                                value="<?php echo $HarvestData->WorkingBoys ?>" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtMTCrates" class="form-label">Empty Crates<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtMTCrates" name="txtMTCrates"
                                                placeholder="Enter MT crates"
                                                value="<?php echo $HarvestData->MTCrates ?>" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtNets" class="form-label">Nets<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtNets" name="txtNets"
                                                placeholder="Enter Nets" value="<?php echo $HarvestData->Nets ?>" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtSavels" class="form-label">Savels<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtSavels" name="txtSavels"
                                                placeholder="Enter Savels" value="<?php echo $HarvestData->Savels ?>" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtWeightStones" class="form-label">Weight Stone's<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtWeightStones"
                                                name="txtWeightStones" placeholder="Enter Weight Stones"
                                                value="<?php echo $HarvestData->WeightStones ?>" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtWeightMC" class="form-label">Weight M/C<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtWeightMC" name="txtWeightMC"
                                                placeholder="Enter Weight M/C"
                                                value="<?php echo $HarvestData->WeightMc ?>" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtWatertin" class="form-label">Water Tin <span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtWaterTin" name="txtWatertin"
                                                placeholder="Enter Water Tin"
                                                value="<?php echo $HarvestData->WaterTin ?>" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <textarea name="txtRemarks" class="form-control" rows="1" cols="50"
                                                id="txtRemarks"
                                                placeholder="Enter Remarks(Optional)"><?php echo $HarvestData->Remarks ?></textarea>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtDefectRemarks" class="form-label">Defect Remarks</label>
                                            <textarea name="txtDefectRemarks" class="form-control" rows="1" cols="50"
                                                id="txtDefectRemarks"
                                                placeholder="Enter Defect Remarks(Optional)"><?php echo $HarvestData->DefectRemarks ?></textarea>
                                        </div>

                                        <div>&nbsp;</div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtMTCrateWeight" class="form-label">Empty Net Weight/Each <span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtMTCrateWeight"
                                                name="txtMTCrateWeight" placeholder="Enter MT Crate Weight" value=<?php echo $HarvestData->MTCrateWeight ? $HarvestData->MTCrateWeight : '1.8' ?> />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtCheckingCount" class="form-label">Checking Count<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtCheckingCount"
                                                name="txtCheckingCount" placeholder="Enter Checking Count"
                                                value="<?php echo $HarvestData->CheckingCount ?>" />
                                        </div>
                                        <?php if ($hasHarvestDate == 1) { ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtCheckingCrate" class="form-label">Checking Crate<span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtCheckingCrate"
                                                    name="txtCheckingCrate" placeholder="Enter Checking Crate"
                                                    value="<?php echo isset($HarvestData->CheckingCrate) ? $HarvestData->CheckingCrate : 1 ?>" />
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtCheckingQty" class="form-label">Checking Qty/Kgs<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtCheckingQty"
                                                name="txtCheckingQty" placeholder="Enter Checking Qty/Kgs"
                                                value="<?php echo $HarvestData->CheckingQty ?>" />
                                        </div>
                                        <?php if ($hasHarvestDate == 1) { ?>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtNumberOfPieces" class="form-label">Number Of Pieces</label>
                                                <input type="text" class="form-control" id="txtNumberOfPieces"
                                                    name="txtNumberOfPieces" placeholder="Enter No Of Pieces"
                                                    value="<?php echo isset($HarvestData->NumberOfPieces) ? $HarvestData->NumberOfPieces : '' ?>" />
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtMaterialtotalQty" class="form-label">(+) NW Total Material
                                                Qty</label>
                                            <input name="txtMaterialtotalQty" class="form-control" rows="1" cols="50"
                                                id="txtMaterialtotalQty" placeholder="Enter Material total Qty"
                                                value="<?php echo $HarvestData->MaterialTotalQuantity ?>" readonly />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtMaterialtotalCrates" class="form-label">Total Crate Net
                                                Weight</label>
                                            <input name="txtMaterialtotalCrates" class="form-control" rows="1" cols="50"
                                                id="txtMaterialtotalCrates" placeholder="Enter Material total Crates"
                                                value="<?php echo $HarvestData->MaterialTotalCrates ?>" readonly />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtQtyWON" class="form-label">(-) NW Total Material Qty</label>
                                            <input name="txtQtyWON" class="form-control" rows="1" cols="50"
                                                id="txtQtyWON" placeholder=""
                                                value="<?php echo $HarvestData->WithOutNetQty ?>" readonly />
                                        </div>

                                        <hr>

                                        <div class="row">
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="drpVariety" class="form-label">Variety</label>
                                                <select class="drpSelect" name="drpVariety" id="drpVariety" <?php echo empty($HarvestID) ? 'disabled' : ''; ?>>
                                                    <option value=""> Select</option>
                                                    <?php foreach ($resRmVariety as $Item) { ?>
                                                        <option value="<?php echo $Item->VarietyID; ?>">
                                                            <?php echo $Item->VarietyName; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtWeight" class="form-label"> Weight(Kgs)</label>
                                                <input type="text" class="form-control" id="txtWeight" name="txtWeight"
                                                    placeholder=" Enter Weight(Kgs)" <?php echo empty($HarvestID) ? 'readonly' : ''; ?>>
                                            </div>

                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtNoOfCrates" class="form-label">No Of Crates</label>
                                                <input type="text" class="form-control" id="txtNoOfCrates"
                                                    name="txtNoOfCrates" placeholder="Enter No Of Crates" <?php echo empty($HarvestID) ? 'readonly' : ''; ?>>
                                            </div>

                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <label for="txtCount" class="form-label">Count</label>
                                                <input type="text" class="form-control" id="txtCount" name="txtCount"
                                                    placeholder="Enter Count" <?php echo empty($HarvestID) ? 'readonly' : ''; ?>>
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnHarvestID" name="hdnHarvestID"
                                                value="<?php echo $HarvestID ?>" />
                                            <input type="hidden" id="hdnHarvestBreakUpID" name="hdnHarvestBreakUpID"
                                                value="" />
                                            <input type="hidden" id="hdnTxtWeight" name="hdnTxtWeight"
                                                value="" />
                                            <input type="hidden" id="hdnDrpVariety" name="hdnDrpVariety"
                                                value="" />
                                            <input type="hidden" id="hdnTxtNoOfCrates" name="hdnTxtNoOfCrates"
                                                value="" />
                                            <input type="hidden" id="hdnTxtCount" name="hdnTxtCount"
                                                value="" />
                                            <a href="harvesting-list" class="btn btn-danger">Close</a>
                                            <button class="btn btn-primary" name="submit" id="submit">Save</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">
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
    var table;
    var hasHarvestDate = <?php echo json_encode($hasHarvestDate == 1); ?>;

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
                title: 'Day-Harvesting-Report-<?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }

    $(document).ready(function() {
        getBookingNumber();
        var harvestID = $("#hdnHarvestID").val();
        if (harvestID) {
            showList();
        }

        $('#drpGraderName').select2();

    });

    function getBookingNumber() {
        $.ajax({
            url: 'Ajax/ajax-harvesting-create.php',
            type: 'POST',
            data: {
                methodType: 'getBookingData',
                bookingID: $("#hdnBookingID").val()
            },
            success: function(data) {
                $("#txtBookingNumber").val(data[0].BookingNumber);
                return false;
            }
        });
    }

    function showList() {
        $.ajax({
            url: 'Ajax/ajax-harvesting-create.php',
            type: 'POST',
            data: {
                methodType: 'getList',
                harvestID: $("#hdnHarvestID").val(),
            },
            success: function(data) {
                console.log(data);
                $("#divDataList").html(data.Content);
                return false;
            }
        });
    }

    $(document).on("click", ".btnEdit", function() {
        var brkUpID = $(this).attr("data-brkUpID");
        var varietyID = $(this).closest("tr").find(".tdVariety").data("varietyid");
        $("#hdnHarvestBreakUpID").val(brkUpID);

        $("#drpVariety").val(varietyID).trigger("change");
        $("#txtWeight").val($(this).closest("tr").find(".tdWeight").text());
        $("#txtNoOfCrates").val($(this).closest("tr").find(".tdNoofCrates").text());
        $("#txtCount").val($(this).closest("tr").find(".tdCount").text());
    });

    $("#frmCreateHarvest").validate({
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
            txtBookingDate: {
                required: true
            },
            txtBookingNumber: {
                required: true
            },
            drpGraderName: {
                required: true
            },
            txtFarmerName: {
                required: true
            },
            txtPhoneNumber: {
                required: true
            },
            txtSealNumber: {
                required: true
            },
            drpBoysGroup: {
                required: true
            },
            txtWorkingBoys: {
                required: true
            },
            txtMTCrates: {
                required: true
            },
            txtNets: {
                required: true
            },
            txtSavels: {
                required: true
            },
            txtWeightStones: {
                required: true
            },
            txtWeightMC: {
                required: true
            },
            txtWaterTin: {
                required: true
            },
            // txtRemarks: {
            //     required: true
            // },
            txtMTCrateWeight: {
                required: true
            },
            txtCheckingCount: {
                required: true
            },
            txtCheckingQty: {
                required: true
            },
            txtHarvestDate: {
                required: hasHarvestDate
            },
            txtCheckingCount: {
                required: hasHarvestDate
            }
        },
        errorPlacement: function() {
            return false; // suppresses error message text
        },
        submitHandler: function() {
            var supervisors = $("#drpGraderName").val().join(',');

            $.ajax({
                url: 'Ajax/ajax-harvesting-create.php',
                type: 'POST',
                data: {
                    methodType: "formPost",
                    harvestID: $("#hdnHarvestID").val(),
                    bookingDate: $("#txtBookingDate").val(),
                    bookingID: $("#hdnBookingID").val(),
                    supervisor: supervisors,
                    farmerName: $("#txtFarmerName").val(),
                    phoneNumber: $("#txtPhoneNumber").val(),
                    sealNumber: $("#txtSealNumber").val(),
                    boysGroup: $("#drpBoysGroup").val(),
                    workingBoys: $("#txtWorkingBoys").val(),
                    mtCrates: $("#txtMTCrates").val(),
                    nets: $("#txtNets").val(),
                    savels: $("#txtSavels").val(),
                    weightStones: $("#txtWeightStones").val(),
                    weightMC: $("#txtWeightMC").val(),
                    waterTin: $("#txtWaterTin").val(),
                    remarks: $("#txtRemarks").val(),
                    defectremarks: $("#txtDefectRemarks").val(),   //uma                 
                    mtCrateWeight: $("#txtMTCrateWeight").val(),
                    checkingCount: $("#txtCheckingCount").val(),
                    checkingQty: $("#txtCheckingQty").val(),
                    materialTotalQty: $("#txtMaterialtotalQty").val(),
                    materialTotalCrates: $("#txtMaterialtotalCrates").val(),
                    harvestBreakUpID: $("#hdnHarvestBreakUpID").val(),
                    variety: $("#drpVariety").val(),
                    weight: $("#txtWeight").val(),
                    noofCrates: $("#txtNoOfCrates").val(),
                    count: $("#txtCount").val(),
                    harvestDate: (hasHarvestDate) ? $("#txtHarvestDate").val() : '',
                    checkingCrate: (hasHarvestDate) ? $("#txtCheckingCrate").val() : '',
                    numberOfPieces: (hasHarvestDate) ? $("#txtNumberOfPieces").val() : '',

                },
                success: function(data) {
                    var bookingID = $("#hdnBookingID").val();

                    console.log(data);
                    if (data.Error != '') {
                        alert(data.Msg);
                        $("#divDataList").html(data.Content);
                        window.location.href = 'create-harvesting?BookingID=' + bookingID + '&HarvestingID=' + data.HarvestingID;
                    } else {
                        alert(data.Error);
                    }
                    $("#txtCount, #txtWeight, #txtNoOfCrates").val("");
                    $("#drpVariety").val("").trigger("change");
                    return false;
                }
            });
        }
    });
    $(document).on("click", "#btnDuplicate", function() {
        var varietyID = $(this).closest("tr").find(".tdVariety").data("varietyid");
        $("#hdnDrpVariety").val(varietyID);
        $("#hdnTxtWeight").val($(this).closest("tr").find(".tdWeight").text());
        $("#hdnTxtNoOfCrates").val($(this).closest("tr").find(".tdNoofCrates").text());
        $("#hdnTxtCount").val($(this).closest("tr").find(".tdCount").text());
        fnShowModalConfirmation("Confirmation", "Are you sure you want to copy the item ?", "Cancel", "Continue",
            "", "T");
    });
    $(document).on("click", "#btnModalContinue", function() {
        var supervisors = $("#drpGraderName").val().join(',');
        $.ajax({
            url: 'Ajax/ajax-harvesting-create.php',
            type: 'POST',
            data: {
                methodType: "formPost",
                harvestID: $("#hdnHarvestID").val(),
                bookingDate: $("#txtBookingDate").val(),
                harvestDate: (hasHarvestDate) ? $("#txtHarvestDate").val() : '',
                bookingID: $("#hdnBookingID").val(),
                supervisor: supervisors,
                farmerName: $("#txtFarmerName").val(),
                phoneNumber: $("#txtPhoneNumber").val(),
                sealNumber: $("#txtSealNumber").val(),
                boysGroup: $("#drpBoysGroup").val(),
                workingBoys: $("#txtWorkingBoys").val(),
                mtCrates: $("#txtMTCrates").val(),
                nets: $("#txtNets").val(),
                savels: $("#txtSavels").val(),
                weightStones: $("#txtWeightStones").val(),
                weightMC: $("#txtWeightMC").val(),
                waterTin: $("#txtWaterTin").val(),
                remarks: $("#txtRemarks").val(),
                mtCrateWeight: $("#txtMTCrateWeight").val(),
                checkingCount: $("#txtCheckingCount").val(),
                checkingCrate: (hasHarvestDate) ? $("#txtCheckingCrate").val() : '',
                checkingQty: $("#txtCheckingQty").val(),
                materialTotalQty: $("#txtMaterialtotalQty").val(),
                materialTotalCrates: $("#txtMaterialtotalCrates").val(),
                harvestBreakUpID: "",
                variety: $("#hdnDrpVariety").val(),
                weight: $("#hdnTxtWeight").val(),
                noofCrates: $("#hdnTxtNoOfCrates").val(),
                count: $("#hdnTxtCount").val(),
                numberOfPieces: (hasHarvestDate) ? $("#txtNumberOfPieces").val() : '',
            },
            success: function(data) {
                var bookingID = $("#hdnBookingID").val();
                if (data.Error != '') {
                    // fnShowModalConfirmation("Success", data.Msg, "OK", "", "", "F");
                    // alert(data.Msg);
                    $("#divDataList").html(data.Content);
                    window.location.href = 'create-harvesting?BookingID=' + bookingID + '&HarvestingID=' + data.HarvestingID;
                } else {
                    alert(data.Error);
                }
                return false;
            }
        });
    });
</script>

</body>

</html>