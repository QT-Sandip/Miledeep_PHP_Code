<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");


$resSpecies = $resDropdownsAPI->Data->Species;
$resPurchaseCenters = $resDropdownsAPI->Data->PurchaseCenters; // Purchase Stations / Purchase centers
$resRmSuppliers = $resDropdownsAPI->Data->RmSuppliers; // Purchase Stations / Purchase centers

$resProcessingStations = $resDropdownsAPI->Data->ProcessingStations; // 
$resPurchaseCategory = $resDropdownsAPI->Data->PurchaseCategory; // 
$resVehicles = $resDropdownsAPI->Data->Vehicles; // 
$resRmAgents = $resDropdownsAPI->Data->RmAgents; // 
$resDrivers = $resDropdownsAPI->Data->Drivers; // 
$resRmGraders = $resDropdownsAPI->Data->RmGraders; // 
$resRmVariety = $resDropdownsAPI->Data->RmVariety; // 
$resProductionPlant = $resDropdownsAPI->Data->Plant; //Soaking Report / Production Plant
// $resProductionLines = $resDropdownsAPI->Data->ProductionLines; //Soaking Report/LineNumber

// echo json_encode($resDropdownsAPI, JSON_PRETTY_PRINT);
// var_dump($resDropdownsAPI);

$api = API_PREPROCESSING_DROPDOWNS;

$resPPcDropdownsAPI = CallAPI($api, "GET", "", "");

$resCenter = $resPPcDropdownsAPI->Data->Center; // 

$resProductionLine = $resPPcDropdownsAPI->Data->ProductionLine;
// print_r($resProductionLine); exit; //Soaking Report/LineNumber
$resHlVaBillVariety = $resPPcDropdownsAPI->Data->HlVaBillVariety;
$resShift = $resPPcDropdownsAPI->Data->Shift;
$resProductionShifts = $resPPcDropdownsAPI->Data->ProductionShift;
$resFreezingType = $resPPcDropdownsAPI->Data->FreezingType;
$resProductType = $resPPcDropdownsAPI->Data->ProductType;
$resPreparation = $resPPcDropdownsAPI->Data->Preparation;
$resTypeGlaze = $resPPcDropdownsAPI->Data->TypeGlaze;
$resTreatment = $resPPcDropdownsAPI->Data->Treatment;
$resBrand = $resPPcDropdownsAPI->Data->Brand;
$resGrade = $resPPcDropdownsAPI->Data->Grade;
$resPackingStyle = $resPPcDropdownsAPI->Data->PackingStyle;
$resGroupCompany = $resPPcDropdownsAPI->Data->GroupCompany;
$resGradingmachine = $resPPcDropdownsAPI->Data->Gradingmachine;
$resPlant = $resPPcDropdownsAPI->Data->Plant;

// echo json_encode($resGradingmachine, JSON_PRETTY_PRINT);


// hlva-rates-list
$api = API_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resultSpecies = $resDropdownsAPI->Data->Species; // Species


$CurrentMonth = date('m');
$CurrentYear = date('Y');

// Determine the past FY and current FY based on the current month
if ($CurrentMonth > 3) {
    $PastFY = $CurrentYear - 1;
    $CurrentFY = $CurrentYear;
} else {
    $PastFY = $CurrentYear - 2;
    $CurrentFY = $CurrentYear - 1;
}

?>

<div class="row rowFilter">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <!-- <div class="col">
                        <input type="text" class="form-control" aria-label="From Date" placeholder="From Date" onfocus="(this.type='date')" onblur="(this.type='text')">
                    </div>
                    <div class="col">
                        <input type="text" class="form-control" aria-label="To Date"  placeholder="To Date" onfocus="(this.type='date')" onblur="(this.type='text')">
                    </div> -->

                    <?php
                    $Button = '<input type="button" id="btnSearch" name="btnSearch" class="btn btn-primary"  value="Search" aria-label="Submit">';

                    if (isset($buttonPay) && !empty($buttonPay)) {
                        $PayButton = '<button id="btnPay" name="btnPay" type="button" data-bs-toggle="modal" data-bs-target="#ModalRmPayment" 
                                             class="mx-3 btn btn-success waves-effect waves-light text-left" disabled>
                                            <i class="fa fa-plus"></i> Pay
                                    </button>';
                    }

                    ?>

                    <?php if (isset($filterReceivingPlant) && !empty($filterReceivingPlant)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpReceivingPlantId">Receiving Plant </label>
                            <select class="drpSelect" id="drpReceivingPlantId" name="drpReceivingPlantId">
                                <option value="">Select</option>
                                <?php
                                foreach ($resPlant as $item)
                                    echo '<option value="' . $item->ProcessingStationID . '">' . $item->StationName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if ((isset($filterCenter) && !empty($filterCenter)) || (isset($filterPsId) && !empty($filterPsId))) { ?>
                        <div class="col col-md-3 mb-3">
                            <?php
                            if (isset($filterCenter) && !empty($filterCenter)) {
                                echo '<select class="drpSelect" id="drpCenter" name="drpCenter">
                                                <option value="">Select Center</option>';
                            }
                            if (isset($filterPsId) && !empty($filterPsId)) {
                                echo '<select class="drpSelect" id="drpPurchaseList" name="drpPurchaseList">
                                                <option value="">Select Purchase</option>';
                            }
                            ?>
                            <?php
                            foreach ($resPurchaseCenters as $item)
                                echo '<option value="' . $item->PurchaseStationID . '">' . $item->StationName . '</option>';
                            ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterPlant) && !empty($filterPlant)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect" id="drpPlant" name="drpPlant">
                                <option value="">Select Plant</option>
                                <?php
                                foreach ($resRmSuppliers as $item)
                                    echo '<option value="' . $item->SupplierID . '">' . $item->FinancierName . '</option>';
                                ?>

                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterPlantTwo) && !empty($filterPlantTwo)) { ?>
                        <div class="col-md-3 mb-3">
                            <label for="drpPlant">Production Plant</label>
                            <select class="drpSelect form-control" id="drpPlant" name="drpPlant">
                                <option value="">Select</option>
                                <?php foreach ($resPlant as $item) { ?>
                                    <option value="<?php echo $item->ProcessingStationID ?>"><?php echo $item->StationName ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterPlantThree) && !empty($filterPlantThree)) { ?>
                        <div class="col-md-3 mb-3">
                            <label for="drpPlant">Production Plant</label>
                            <select class="drpSelect form-control" id="drpPlant" name="drpPlant">
                                <option value="">Select</option>
                                <?php foreach ($resPlant as $item) { ?>
                                    <option value="<?php echo $item->ProcessingStationID ?>"><?php echo $item->StationName ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>



                    <?php if (isset($filterSupplier) && !empty($filterSupplier)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect" id="drpSupplier" name="drpSupplier">
                                <option value="">Select Supplier</option>
                                <?php
                                foreach ($resRmSuppliers as $item)
                                    echo '<option value="' . $item->SupplierID . '">' . $item->FinancierName . '</option>';
                                ?>

                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterSupplierID) && !empty($filterSupplierID)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpSupplier">Supplier Id <span style="color:red">* </span></label>
                            <select class="drpSelect" id="drpSupplier" name="drpSupplier">
                                <option value="">Select Supplier</option>
                                <?php
                                foreach ($resRmSuppliers as $item)
                                    echo '<option value="' . $item->SupplierID . '">' . $item->FinancierName . '</option>';
                                ?>

                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterProductionPlant) && !empty($filterProductionPlant)) { ?>
                        <div class="form-group col-sm-3 mb-3">
                            <label for="drpProductionPlant" class="form-label">Production Plant<span
                                    style="color:red">*</span></label>
                            <select class="drpSelect" name="drpProductionPlant" id="drpProductionPlant">
                                <option value=""> Select</option>
                                <?php foreach ($resProductionPlant as $Item) { ?>
                                    <option value="<?php echo $Item->ProcessingStationID; ?>">
                                        <?php echo $Item->FullName . '-' . $Item->StationName; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>



                    <?php if (isset($filterCount) && !empty($filterCount)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect" id="drpCount" name="drpCount">
                                <option value="">Select Count</option>
                                <?php
                                foreach ($resRmSuppliers as $item)
                                    echo '<option value="' . $item->SupplierID . '">' . $item->FinancierName . '</option>';
                                ?>

                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterVehicleNumber) && !empty($filterVehicleNumber)) { ?>
                        <div class="form-group col-sm-3">
                            <label for="drpVehicleNumber" class="form-label">Vehicle Number <span
                                    style="color:red">*</span></label>

                            <select class="drpSelect" name="drpVehicleNumber" id="drpVehicleNumber">
                                <option value=""> Select</option>
                                <option value="0"> Others</option>
                                <?php foreach ($resVehicles as $Item) { ?>
                                    <option value="<?php echo $Item->VehicalID; ?>">
                                        <?php echo $Item->VehicalNumber; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterArrivalPlant) && !empty($filterArrivalPlant)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect" id="drpArrivalPlant" name="drpArrivalPlant">
                                <!-- <label for="">Arrival Plant <span>*</span></label> -->
                                <option value="">Select</option>
                                <?php
                                foreach ($resProcessingStations as $item)
                                    echo '<option value="' . $item->ProcessingStationID . '">' . $item->StationName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterArrivalPlantTwo) && !empty($filterArrivalPlantTwo)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpArrivalPlant">Arrival Plant <span style="color:red">*</span></label>
                            <select class="drpSelect" id="drpArrivalPlant" name="drpArrivalPlant">
                                <option value="">Select</option>
                                <?php
                                foreach ($resProcessingStations as $item)
                                    echo '<option value="' . $item->ProcessingStationID . '">' . $item->StationName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterPurchaseCenter) && !empty($filterPurchaseCenter)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpPurchaseCenter">Purchase Center</label>
                            <select class="drpSelect" id="drpPurchaseCenter" name="drpPurchaseCenter">
                                <option value="">Select</option>
                                <?php
                                foreach ($resPurchaseCenters as $item)
                                    echo '<option value="' . $item->PurchaseStationID . '">' . $item->StationName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>


                    <?php if (isset($filterArrivalDateTwo) && !empty($filterArrivalDateTwo)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="txtArrivalDate">Arrival Date <span style="color:red">*</span></label>
                            <input type="date" class="form-control" value="<?php echo date('Y-m-d') ?>"
                                max="<?php echo date('Y-m-d') ?>" id="txtArrivalDate" name="txtArrivalDate" />
                        </div>
                    <?php } ?>

                    <?php if (isset($filterTrackCode) && !empty($filterTrackCode)) { ?>
                        <div class="form-group col-sm-3 mb-3">
                            <label for="drpTrackCode" class="form-label">Track ID<span style="color:red">*</span></label>
                            <select class="drpSelect form-control" name="drpTrackCode" id="drpTrackCode">
                                <option value=""> Select</option>
                            </select>
                        </div>
                    <?php } ?>


                    <?php if (isset($filterMachineID) && !empty($filterMachineID)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpMachineID">Grading Machine </label>
                            <select class="drpSelect" id="drpMachineID" name="drpMachineID">
                                <option value="">Select</option>
                                <?php
                                foreach ($resGradingmachine as $item)
                                    echo '<option value="' . $item->MachineID . '" data-plantid="' . $item->UnitID . '">' . $item->MachineName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterBookingDate) && !empty($filterBookingDate)) { ?>
                        <div class="col col-md-3 mb-3">
                            <!-- <label></label> -->
                            <input type="date" class="form-control" value="<?php echo date('Y-m-d') ?>"
                                max="<?php echo date('Y-m-d') ?>" id="txtBookingDate" name="txtBookingDate" />
                        </div>
                    <?php } ?>

                    <?php if (isset($filterArrivalDate) && !empty($filterArrivalDate)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label>Arrival Date <span style="color:red">*</span></label>
                            <input type="date" class="form-control" value="<?php echo date('Y-m-d') ?>"
                                max="<?php echo date('Y-m-d') ?>" id="txtArrivalDate" name="txtArrivalDate" />
                        </div>
                    <?php } ?>

                    <?php if (isset($filterBillsDate) && !empty($filterBillsDate)) { ?>
                        <div class="col col-md-3 mb-3">
                            <!-- <label></label> -->
                            <input type="date" class="form-control" value="<?php echo date('Y-m-d') ?>"
                                max="<?php echo date('Y-m-d') ?>" id="txtBillsDate" name="txtBillsDate" />
                        </div>
                    <?php } ?>

                    <?php if (isset($filterPayDate) && !empty($filterPayDate)) { ?>
                        <div class="col col-md-3 mb-3">
                            <!-- <label></label> -->
                            <input type="date" class="form-control" value="<?php echo date('Y-m-d') ?>"
                                max="<?php echo date('Y-m-d') ?>" id="txtBillsDate" name="txtBillsDate" />
                        </div>
                    <?php } ?>

                    <!-- <?php //if (isset($filterPaymentMode) && !empty($filterPaymentMode)) { 
                    ?>
                    <div class="col col-md-3 mb-3">
                        <select class="drpSelect" id="drpPaymentMode" name="drpPaymentMode">
                            <option value="">Select Payment Mode</option>
                            <option value="Cash" selected>Cash</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                    <?php //} 
                    ?>  -->

                    <?php if (isset($filterPaymentMode) && !empty($filterPaymentMode)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect" id="drpPaymentMode" name="drpPaymentMode">
                                <option value="">Select Payment Mode</option>

                                <option value="B">Cheque</option>
                                <option value="C">Cash</option>
                                <option value="J">Journal</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterType) && !empty($filterType)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect" id="drpType" name="drpType">
                                <option value="">Select Type</option>
                                <option value="0" selected>Pending</option>
                                <option value="1">Completed</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterFinancialYear) && !empty($filterFinancialYear)) { ?>
                        <div class="col col-md-2 mb-3">
                            <label for="drpYear">Year <span style="color:red">*</span></label>
                            <select class="drpSelect" id="drpYear" name="drpYear">
                                <option value="">Select</option>
                                <option value="<?php echo $PastFY ?>"><?php echo $PastFY . '-' . substr($PastFY + 1, -2) ?>
                                </option>
                                <option value="<?php echo $CurrentFY ?>">
                                    <?php echo $CurrentFY . '-' . substr($CurrentFY + 1, -2) ?>
                                </option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterDateFilter) && !empty($filterDateFilter)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpDateType">Date Filter <span style="color:red">*</span></label>
                            <select class="drpSelect" id="drpDateType" name="drpDateType">
                                <option value="0" selected>Arrival Date</option>
                                <option value="1">Bill Date</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterStartDate) && !empty($filterStartDate)) { ?>
                        <div class="col col-md-3 mb-3">
                            <!-- <label></label> -->
                            <input type="date" class="form-control" id="txtStartDate" name="txtStartDate" />
                        </div>
                    <?php } ?>

                    <?php if (isset($filterFromDate) && !empty($filterFromDate)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="txtFromDate">From Date <span style="color:red">*</span></label>
                            <input type="date" class="form-control" id="txtFromDate" name="txtFromDate" />
                        </div>
                    <?php } ?>



                    <?php if (isset($filterEndDate) && !empty($filterEndDate)) { ?>
                        <div class="col col-md-3 mb-3 ">
                            <!-- <label></label> -->
                            <input type="date" class="form-control" id="txtEndDate" name="txtEndDate" />
                        </div>
                    <?php } ?>



                    <?php if (isset($filterToDate) && !empty($filterToDate)) { ?>
                        <div class="col col-md-3 mb-3 ">
                            <label for="txtToDate">To Date <span style="color:red">*</span></label>
                            <input type="date" class="form-control" id="txtToDate" name="txtToDate" />
                        </div>
                    <?php } ?>

                    <?php if (isset($filterBatchNo) && !empty($filterBatchNo)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpYear">Batch No</label>
                            <select class="form-control select2" name="PurchaseStationID" id="PurchaseStationID">
                                <option value="">Select</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterYear) && !empty($filterYear)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect" id="drpYear" name="drpYear">
                                <option value="<?php echo $PastFY ?>"><?php echo $PastFY . '-' . ($PastFY + 1) ?></option>
                                <option value="<?php echo $CurrentFY ?>" selected>
                                    <?php echo $CurrentFY . '-' . ($CurrentFY + 1) ?>
                                </option>
                            </select>
                        </div>
                    <?php } ?>

                    <!-- PreProcessing @Durga -->

                   
                    <?php if (isset($filterPPCStation) && !empty($filterPPCStation)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpStation">Station<span style="color:red">*</span> </label>
                            <select class="drpSelect" name="drpStation" id="drpStation">
                                <option value="">All</option>
                                <?php foreach ($resCenter as $Center) { ?>
                                    <option value="<?php echo $Center->StationID ?>"><?php echo $Center->UnitName ?></option>
                                <?php } ?>
                                </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterVariety) && !empty($filterVariety)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpVariety">Variety</label>
                            <select class="drpSelect" name="drpVariety" id="drpVariety">
                                <option value="">All</option>
                                <?php foreach ($resHlVaBillVariety as $HlVaBillVariety) { ?>
                                    <option value="<?php echo $HlVaBillVariety->VarietyID ?>">
                                        <?php echo $HlVaBillVariety->ShortName ?>
                                    </option>
                                <?php } ?>
                                </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterPPCType) && !empty($filterPPCType)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpSpType">Supplier Type<span style="color:red">*</span></label>
                            <select class="form-control drpSelect" name="drpSpType" id="drpSpType">
                                <option value="">Select</option>
                                <option value="1">Contractor</option>
                                <option value="2">Mestri</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterShift) && !empty($filterShift)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpShift">Shift</label>
                            <select class="drpSelect" name="drpShift" id="drpShift">
                                <option value="">All</option>
                                <option value="1">Day</option>
                                <option value="2">Night</option>
                                <option value="3">Both</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterProductionShifts) && !empty($filterProductionShifts)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpProductionShifts">Shifts</label>
                            <select class="drpSelect" name="drpProductionShifts" id="drpProductionShifts">
                                <option value="">Select</option>
                                <?php foreach ($resProductionShifts as $Shifts) { ?>
                                    <option value="<?php echo $Shifts->OptID ?>"><?php echo $Shifts->Name ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if (isset($filterProductionLine) && !empty($filterProductionLine)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpLineNumber">Line Number</label>
                            <select class="drpSelect" name="drpLineNumber" id="drpLineNumber">
                                <option value="">Select</option>
                                <?php foreach ($resProductionLine as $ProductionLine) { ?>
                                    <option value="<?php echo $ProductionLine->Value ?>"><?php echo $ProductionLine->Name ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>





                    <?php if (isset($filterSpecies) && !empty($filterSpecies)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpSpecies">Species</label>
                            <select class="drpSelect" name="drpSpecies" id="drpSpecies">
                                <option value="">Select</option>
                                <?php foreach ($resSpecies as $item) { ?>>
                                    <option value="<?php echo $item->ShortCode ?>"><?php echo $item->ShortCode ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <!-- created-hlva-variety -->
                    <?php if (isset($filterHLVAVariety) && !empty($filterHLVAVariety)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpVariety">Variety<span style="color:red">*</span> </label>
                            <select class="drpSelect" name="drpVariety" id="drpVariety">
                                <option value="">All</option>
                                <?php foreach ($resRmVariety as $RmVariety) { ?>>
                                    <option value="<?php echo $RmVariety->VarietyID ?>"><?php echo $RmVariety->VarietyName ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterFreezingType) && !empty($filterFreezingType)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpFreezingType">Freezing Type<span style="color:red">*</span> </label>
                            <select class="drpSelect" name="drpFreezingType" id="drpFreezingType">
                                <option value="">All</option>
                                <?php foreach ($resFreezingType as $FreezingType) { ?>>
                                    <option value="<?php echo $FreezingType->FreezingTypeName ?>">
                                        <?php echo $FreezingType->FreezingTypeName ?>
                                    </option>
                                <?php } ?>
                                </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterProductType) && !empty($filterProductType)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpProductType">Type of Product<span style="color:red">*</span> </label>
                            <select class="drpSelect" name="drpProductType" id="drpProductType">
                                <option value="">All</option>
                                <?php foreach ($resProductType as $ProductType) { ?>>
                                    <option value="<?php echo $ProductType ?>"><?php echo $ProductType ?></option>
                                <?php } ?>
                                </select>
                        </div>
                    <?php } ?>
                    <?php if (isset($filterProductType1) && !empty($filterProductType1)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpProductType">Type of Product</label>
                            <select class="drpSelect" name="drpProductType" id="drpProductType">
                                <option value="">All</option>
                                <?php foreach ($resProductType as $ProductType) { ?>>
                                    <option value="<?php echo $ProductType ?>"><?php echo $ProductType ?></option>
                                <?php } ?>
                                </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterRptType) && !empty($filterRptType)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpRptType">Report Type <span style="color:red">*</span></label>
                            <select class="drpSelect form-control" id="drpRptType" name="drpRptType">
                                <option value="B">Select Report Type</option>
                                <option value="M">Month Wise Report</option>
                                <option value="B">Bill Wise Report</option>
                            </select>
                        </div>
                    <?php } ?>


                    <?php if (isset($filterPreparation) && !empty($filterPreparation)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpPreparation">Preparation<span style="color:red">*</span> </label>
                            <select class="drpSelect" name="drpPreparation" id="drpPreparation">
                                <option value="">All</option>
                                <?php foreach ($resPreparation as $Preparation) { ?>>
                                    <option value="<?php echo $Preparation->PreparationName ?>">
                                        <?php echo $Preparation->PreparationName ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterTypeGlaze) && !empty($filterTypeGlaze)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpTypeGlaze">Type Glaze<span style="color:red">*</span> </label>
                            <select class="drpSelect" name="drpTypeGlaze" id="drpTypeGlaze">
                                <option value="">All</option>
                                <?php foreach ($resTypeGlaze as $TypeGlaze) { ?>>
                                    <option value="<?php echo $TypeGlaze->ID ?>"><?php echo $TypeGlaze->GlazeType ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterTreatment) && !empty($filterTreatment)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpTreatment">Treatment<span style="color:red">*</span> </label>
                            <select class="drpSelect" name="drpTreatment" id="drpTreatment">
                                <option value="">All</option>
                                <?php foreach ($resTreatment as $Treatment) { ?>>
                                    <option value="<?php echo $Treatment->ID ?>"><?php echo $Treatment->TreatmentName ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterBrand) && !empty($filterBrand)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpBrand">Brand<span style="color:red">*</span> </label>
                            <select class="drpSelect" name="drpBrand" id="drpBrand">
                                <option value="">All</option>
                                <?php foreach ($resBrand as $Brand) { ?>>
                                    <option value="<?php echo $Brand->BrandID ?>"><?php echo $Brand->BrandName ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterGrade) && !empty($filterGrade)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpGrade">Grade<span style="color:red">*</span> </label>
                            <select class="drpSelect" name="drpGrade" id="drpGrade">
                                <option value="">All</option>
                                <?php foreach ($resGrade as $Grade) { ?>>
                                    <option value="<?php echo $Grade->ID ?>"><?php echo $Grade->GradeGroup ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterPackingStyle) && !empty($filterPackingStyle)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpPackingStyle">Packing Style<span style="color:red">*</span> </label>
                            <select class="drpSelect" name="drpPackingStyle" id="drpPackingStyle">
                                <option value="">All</option>
                                <?php foreach ($resPackingStyle as $PackingStyle) { ?>>
                                    <option value="<?php echo $PackingStyle->PCKID ?>"><?php echo $PackingStyle->PackingStyle ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterGroupCompany) && !empty($filterGroupCompany)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpGroupCompany">Group Company<span style="color:red">*</span> </label>
                            <select class="drpSelect" name="drpGroupCompany" id="drpGroupCompany">
                                <option value="">All</option>
                                <?php foreach ($resGroupCompany as $GroupCompany) { ?>>
                                    <option value="<?php echo $GroupCompany->ConID ?>">
                                        <?php echo $GroupCompany->ConsigneeName ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterCompanyType) && !empty($filterCompanyType)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpCompanyType">Company Type <span style="color:red">*</span></label>
                            <select class="drpSelect form-control" id="drpCompanyType" name="drpCompanyType">
                                <option value="">Select Company Type</option>
                                <option value="0">Non-Company</option>
                                <option value="1">Company</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterAgent) && !empty($filterAgent)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpAgent">Agent <span style="color:red">*</span></label>
                            <select class="drpSelect form-control" id="drpAgent" name="drpAgent">
                                <option value="">Select Agent</option>
                                <?php
                                foreach ($resRmAgents as $item)
                                    echo '<option value="' . $item->UserID . '">' . $item->UserName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterMonth) && !empty($filterMonth)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Select Month<span style="color:red">*</span></label>
                            <select class="drpSelect" name="drpMonth" id="drpMonth">
                                <?php
                                $start = strtotime('first day of this month');
                                for ($i = 0; $i < 12; $i++) {
                                    $time = strtotime(sprintf('%d months', $i), $start);
                                    $label = date('F', $time);
                                    $value = date('m', $time);
                                    echo "<option value='$value'>$label</option>";
                                }
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterYearOne) && !empty($filterYearOne)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Select Year<span style="color:red">*</span></label>
                            <select class="drpSelect" name="drpYearOne" id="drpYearOne">
                                <?php for ($year; $Startyear <= $Currentyear; $Startyear++) { ?>
                                    <option value="<?php echo $Startyear ?>" <?php if ($Startyear == $Currentyear) {
                                           echo 'selected="selected"';
                                       } ?>>
                                 <?php echo $Startyear ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if (isset($filterYearTwo) && !empty($filterYearTwo)) { ?>
                    <div class="col-md-3 mb-3">
                                            <label for="drpYear">Year</label>
                                            <select class="drpSelect form-control" id="drpYear" name="drpYear">
                                                <option value="2019">2019</option>
                                                <option value="2020">2020</option>
                                                <option value="2021">2021</option>
                                                <option value="2022">2022</option>
                                                <option value="2023">2023</option>
                                                <option value="2024" selected>2024</option>
                                                <option value="2025">2025</option>
                                            </select>
                                        </div>
                                        <?php } ?>

                    <?php if (isset($filterLineNumber) && !empty($filterLineNumber)) { ?>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <label for="drpLineNumber" class="form-label">Line Number</label>
                            <select class="drpSelect form-control" name="drpLineNumber" id="drpLineNumber">
                                <option value=""> Select</option>
                                <?php foreach ($resProductionLines as $item) { ?>
                                    <option value="<?php echo $item->Value; ?>">
                                        <?php echo $item->Name ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                    <?php } ?>
                    
                   

                    <?php if (isset($filterFromStation) && !empty($filterFromStation)) { ?>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <label for="drpFromStation" class="form-label">From Station</label>
                            <select class="drpSelect form-control" name="drpFromStation" id="drpFromStation">
                                <option value=""> Select</option>
                                <?php foreach ($resCenter as $Center) { ?>
                                    <option value="<?php echo $Center->StationID ?>"><?php echo $Center->UnitName ?></option>
                                <?php } ?>
                            </select>
                        </div>

                    <?php } ?>


                    <?php if (isset($filterToStation) && !empty($filterToStation)) { ?>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <label for="drpToStation" class="form-label">To Station</label>
                            <select class="drpSelect form-control" name="drpToStation" id="drpToStation">
                                <option value=""> Select</option>
                                <?php foreach ($resCenter as $Center) { ?>
                                    <option value="<?php echo $Center->StationID ?>"><?php echo $Center->UnitName ?></option>
                                <?php } ?>
                            </select>
                        </div>

                    <?php } ?>

                    <?php if (isset($filterTransactionType) && !empty($filterTransactionType)) { ?>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <label for="drpTransactionType" class="form-label">Transaction Type</label>
                            <select class="drpSelect form-control" name="drpTransactionType" id="drpTransactionType">
                               <!-- <option value=""> Select</option>-->
                                <option value="Sender"> Sender</option>
                                <option value="Receiver"> Receiver</option>                                
                            </select>
                        </div>

                    <?php } ?>

                    <?php if (isset($filterYear1) && !empty($filterYear1)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label>Select Year<span style="color:red">*</span></label>
                            <select class="drpSelect" id="drpYear1" name="drpYear1">
                                <option value="<?php echo $PastFY ?>"><?php echo $PastFY ?></option>
                                <option value="<?php echo $CurrentFY ?>" selected>
                                    <?php echo $CurrentFY ?>
                                </option>
                            </select>
                        </div>
                    <?php } ?>

                    <!-- create-hlva-variety -->
                    <?php if (isset($filterPPCDate) && !empty($filterPPCDate)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label></label>
                            <input type="date" class="form-control" id="txtPPCDate" name="txtPPCDate" />
                        </div>
                    <?php } ?>

                    <?php if (isset($filterButButton) && !empty($filterButButton)) { ?>
                        <div class="text-center mt-3 col-12">
                            <button id="btnSearch" align="left" type="button"
                                class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                        </div>
                    <?php } ?>


                    <?php if (isset($HLVABottom) && !empty($HLVABottom)) { ?>
                        <div class="text-center mt-1">

                            <button id="btnSearch" align="left" type="button"
                                class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <?php if ($_SESSION["AccessType"] == '1') { ?>
                                <a href="AddHlVaRates" class="btn btn-success waves-effect waves-light">
                                    <i class="fa fa-plus"></i> Add Rates
                                </a>
                            <?php } ?>

                        </div>
                    <?php } ?>

                    <?php if (isset($PpcStationBottom) && !empty($PpcStationBottom)) { ?>
                        <div class="text-center mt-1">

                            <!-- <button id="btnSearch" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                            </button>-->
                            <a href="create-ppc-station" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> Add Station
                            </a>

                        </div>
                    <?php } ?>                  

                    <?php if (isset($HLVAVarietyButton) && !empty($HLVAVarietyButton)) { ?>
                        <div class="text-center mt-1">

                            <button id="btnSearch" align="left" type="button"
                                class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <a href="create-hlva-variety" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> Add Variety
                            </a>

                        </div>
                    <?php } ?>


                    <?php if (isset($buttonBottom) && !empty($buttonBottom)) { ?>
                        <div class="text-center mt-1">
                            <?php
                            echo $Button;
                            echo $PayButton;
                            ?>
                        </div>
                    <?php } ?>


                    <?php
                    if (isset($buttonRight) && !empty($buttonRight)) {
                        echo $Button;
                        echo $PayButton;
                    }
                    ?>
                </div>

            </div>
        </div>
    </div>
</div>
