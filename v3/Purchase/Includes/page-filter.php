<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_DROPDOWNS;
$bodyContent = array(
    'PageType' => 'REPORTS',
);

$resDropdownsAPI = callAPI($api, "GET", "", $bodyContent);

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
$resBoysGroup = $resDropdownsAPI->Data->BoysGroup; // 


// echo json_encode($resProcessingStations, JSON_PRETTY_PRINT);

// var_dump($resDropdownsAPI);

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
                    <?php
                    $Button = '<input type="button" id="btnSearch" name="btnSearch" class="btn btn-primary" value="Search" aria-label="Submit">';

                    if (isset($buttonPay) && !empty($buttonPay)) {
                        $PayButton = '<button id="btnPay" name="btnPay" type="button" 
                                            class="mx-3 btn btn-success waves-effect waves-light text-left" disabled>
                                            <i class="fa fa-plus"></i> Pay
                                    </button>';
                    }
                    ?>

                    <?php if (isset($filterPlant) && !empty($filterPlant)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect form-control" id="drpPlant" name="drpPlant">
                                <option value="">Select Plant</option>
                                <?php
                                foreach ($resRmSuppliers as $item)
                                    echo '<option value="' . $item->SupplierID . '">' . $item->FinancierName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterSpecies) && !empty($filterSpecies)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpSpecies">Species </label>
                            <select class="drpSelect form-control" id="drpSpecies" name="drpSpecies">
                                <option value="">Select</option>
                                <?php
                                foreach ($resSpecies as $item)
                                    echo '<option value="' . $item->ShortCode . '">' . $item->SpeciesName . '(' . $item->ShortCode . ')' . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterVariety) && !empty($filterVariety)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpVariety">Variety </label>
                            <select class="drpSelect form-control" id="drpVariety" name="drpVariety">
                                <option value="">Select Variety</option>
                                <?php
                                foreach ($resRmVariety as $item)
                                    echo '<option value="' . $item->VarietyID . '">' . $item->VarietyName . '</option>';
                                ?>

                            </select>
                        </div>
                    <?php } ?>



                    <?php if (isset($txtExchangeRate) && !empty($txtExchangeRate)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label for="txtExchangeRate">Exchange Value </label>
                            <input type="text" class="form-control" id="txtExchangeRate" name="txtExchangeRate"
                                placeholder="Enter Exchange Value" />
                        </div>
                    <?php } ?>

                    <?php if (isset($txtExpanseRate) && !empty($txtExpanseRate)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label for="txtExpanseRate">Expanse Rate/Kg </label>
                            <input type="text" class="form-control" id="txtExpanseRate" name="txtExpanseRate"
                                placeholder="Enter Expanse Value" />
                        </div>
                    <?php } ?>

                    <?php if (isset($txtMarginRatePerKg) && !empty($txtMarginRatePerKg)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label for="txtMarginRatePerKg">Margin Rate/Kg </label>
                            <input type="text" class="form-control" id="txtMarginRatePerKg" name="txtMarginRatePerKg"
                                placeholder="Enter Margin Rate Per Kg" />
                        </div>
                    <?php } ?>


                    <?php if (isset($filterFromDate) && !empty($filterFromDate)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="txtFromDate">From Date</label>
                            <input type="date" class="form-control" id="txtFromDate" name="txtFromDate"
                                value="<?php echo date('Y-m-d') ?>" />
                        </div>
                    <?php } ?>

                    <?php if (isset($filterToDateNew) && !empty($filterToDateNew)) { ?>
                        <div class="col col-md-3 mb-3 ">
                            <label for="txtToDate">To Date</label>
                            <input type="date" class="form-control" id="txtToDate" name="txtToDate"
                                value="<?php echo date('Y-m-d') ?>" />
                        </div>
                    <?php } ?>
                    <?php if (isset($filterToDate) && !empty($filterToDate)) { ?>
                        <div class="col col-md-3 mb-3 ">
                            <label for="txtToDate">To Date</label>
                            <input type="date" class="form-control" id="txtToDate" name="txtToDate" />
                        </div>
                    <?php } ?>

                    <?php if (isset($txtBillAmount) && !empty($txtBillAmount)) { ?>
                        <div class="col col-md-3 mb-3 ">
                            <label for="txtBillAmount">Bill Amount</label>
                            <input type="input" class="form-control" id="txtBillAmount" name="txtBillAmount"
                                readonly="readonly" />
                        </div>
                    <?php } ?>

                    <?php if (isset($filterToDate) && !empty($filterToDate)) { ?>
                        <div class="col col-md-3 mb-3 ">
                            <label for="txtOverAllExpenses">Over All Expenses</label>
                            <input type="input" class="form-control" id="txtOverAllExpenses" name="txtOverAllExpenses"
                                readonly="readonly" />
                        </div>
                    <?php } ?>

                    <?php if (isset($filterToDate) && !empty($filterToDate)) { ?>
                        <div class="col col-md-3 mb-3 ">
                            <label for="txtSaleValue">Sale Value</label>
                            <input type="input" class="form-control" id="txtSaleValue" name="txtSaleValue"
                                readonly="readonly" />
                        </div>
                    <?php } ?>

                    <?php if ((isset($filterCenter) && !empty($filterCenter)) || (isset($filterPsId) && !empty($filterPsId))) { ?>
                        <div class="col col-md-3 mb-3">
                            <?php
                            if (isset($filterCenter) && !empty($filterCenter)) {
                                echo '<select class="drpSelect form-control" id="drpCenter" name="drpCenter">
                                                <option value="">Select Center</option>';
                            }
                            if (isset($filterPsId) && !empty($filterPsId)) {
                                echo '<select class="drpSelect form-control" id="drpPurchaseList" name="drpPurchaseList">
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

                    <?php if (isset($filterCenterHarvest) && !empty($filterCenterHarvest)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpCenter">Center</label>
                            <select class="drpSelect form-control" id="drpCenter" name="drpCenter">
                                <option value="">Select</option>
                                <?php
                                foreach ($resPurchaseCenters as $item)
                                    echo '<option value="' . $item->PurchaseStationID . '">' . $item->StationName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterRptType) && !empty($filterRptType)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect form-control" id="drpRptType" name="drpRptType">
                                <option value="B">Select Report Type</option>
                                <option value="M">Month Wise Report</option>
                                <option value="B">Bill Wise Report</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterPurchaseBillType) && !empty($filterPurchaseBillType)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpBillType">Bill Type</label>
                            <select class="drpSelect form-control" id="drpBillType" name="drpBillType">
                                <option value="">Select</option>
                                <option value="1">Highest Bill Purchase</option>
                                <option value="2">Lowest Bill Purchase</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterBillerType) && !empty($filterBillerType)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect form-control" id="drpBillerType" name="drpBillerType">
                                <option value="">Select Type</option>
                                <option value="1">Agent</option>
                                <option value="2">Supplier</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterSupplier) && !empty($filterSupplier)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect form-control" id="drpSupplier" name="drpSupplier">
                                <option value="">Select Supplier</option>
                                <?php
                                foreach ($resRmSuppliers as $item)
                                    echo '<option value="' . $item->SupplierID . '">' . $item->FinancierName . "-" . $item->PanNO . '</option>';
                                ?>

                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterAgent) && !empty($filterAgent)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect form-control" id="drpAgent" name="drpAgent">
                                <option value="">Select Agent</option>
                                <?php
                                foreach ($resRmAgents as $item)
                                    echo '<option value="' . $item->UserID . '">' . $item->UserName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterCount) && !empty($filterCount)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect form-control" id="drpCount" name="drpCount">
                                <option value="">Select Count</option>
                                <?php
                                foreach ($resRmSuppliers as $item)
                                    echo '<option value="' . $item->SupplierID . '">' . $item->FinancierName . '</option>';
                                ?>

                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterArrivalPlant) && !empty($filterArrivalPlant)) { ?>
                        <div class="col col-md-3 mb-3">

                            <select class="drpSelect form-control" id="drpArrivalPlant" name="drpArrivalPlant">
                                <option value="">Select Arrival Plant</option>
                                <?php
                                foreach ($resProcessingStations as $item)
                                    echo '<option value="' . $item->ProcessingStationID . '">' . $item->StationName . '</option>';
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
                            <!-- <label></label> -->
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

                    <?php if (isset($filterCompanyType) && !empty($filterCompanyType)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect form-control" id="drpCompanyType" name="drpCompanyType">
                                <option value="">Select Company Type</option>
                                <option value="0">Non-Company</option>
                                <option value="1">Company</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterPaymentMode) && !empty($filterPaymentMode)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect form-control" id="drpPaymentMode" name="drpPaymentMode">
                                <option value="">Select Payment Mode</option>

                                <option value="B">Cheque</option>
                                <option value="C">Cash</option>
                                <option value="J">Journal</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterType) && !empty($filterType)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect form-control" id="drpType" name="drpType">
                                <option value="">Select Type</option>
                                <option value="0" selected>Pending</option>
                                <option value="1">Completed</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterHarComm) && !empty($filterHarComm)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect form-control" id="drpHarComm" name="drpHarComm">
                                <option value="">Select Harvest Commission</option>
                                <option value="T">Inc Harvest Commision</option>
                                <option value="F">Exc Harvest Commission</option>
                            </select>
                        </div>
                    <?php } ?>



                    <?php if (isset($filterStartDate) && !empty($filterStartDate)) { ?>
                        <div class="col col-md-3 mb-3">
                            <!-- <label></label> -->
                            <input type="date" class="form-control" id="txtStartDate" name="txtStartDate" />
                        </div>
                    <?php } ?>

                    <?php if (isset($filterEndDate) && !empty($filterEndDate)) { ?>
                        <div class="col col-md-3 mb-3 ">
                            <!-- <label></label> -->
                            <input type="date" class="form-control" id="txtEndDate" name="txtEndDate" />
                        </div>
                    <?php } ?>

                    <?php if (isset($filterYear) && !empty($filterYear)) { ?>
                        <div class="col col-md-3 mb-3">
                            <select class="drpSelect form-control" id="drpYear" name="drpYear">
                                <option value="<?php echo $PastFY ?>"><?php echo $PastFY . '-' . ($PastFY + 1) ?></option>
                                <option value="<?php echo $CurrentFY ?>" selected>
                                    <?php echo $CurrentFY . '-' . ($CurrentFY + 1) ?>
                                </option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterSingleYear) && !empty($filterSingleYear)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpYear">Year </label>
                            <select class="drpSelect form-control" id="drpYear" name="drpYear">
                                <option value="<?php echo $PastFY ?>"><?php echo $PastFY ?></option>
                                <option value="<?php echo $CurrentFY ?>" selected><?php echo $CurrentFY ?></option>
                                <option value="<?php echo $CurrentFY + 1 ?>"><?php echo $CurrentFY + 1 ?></option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterMonth) && !empty($filterMonth)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpMonth">Month </label>
                            <select class="form-control drpSelect " name="drpMonth" id="drpMonth">
                                <option value="">Select Month</option>
                                <?php
                                $start = strtotime('first day of this month');
                                for ($i = 0; $i < 12; $i++) {
                                    $time = strtotime(sprintf('%d months', $i), $start);

                                    $label = date('F', $time);
                                    $value = date('n', $time);
                                    echo "<option value='$value'";
                                    echo ">$label</option>";
                                }

                                ?>
                            </select>
                        </div>
                    <?php } ?>


                    <?php if (isset($filterDailyWages) && !empty($filterDailyWages)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label for="txtDailyWages">Daily Wages <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="txtDailyWages" name="txtDailyWages"
                                placeholder="Enter Daily Wages" />
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
                        echo '<div class="col col-md-3 mb-3">
                                <label class="col w-100 invisible">Label</label>';
                        echo $Button;
                        echo $PayButton;
                        echo '</div>';
                    }
                    ?>

                    <?php if (isset($buttonButtom) && !empty($buttonButtom)) { ?>
                        <div class="text-center mt-1">
                            <button id="btnSearch" type="button" class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"> </i>
                            </button>
                        </div>
                    <?php } ?>
                    <?php if (isset($addPurchaseRatesButtom) && !empty($addPurchaseRatesButtom)) { ?>
                        <div class="text-center mt-1">
                            <a href="PurchaseRatesHis" class="btn btn-danger">Close</a>
                            <button id="btnSearch" type="button" class="btn btn-primary waves-effect waves-light">
                                Search
                            </button>
                            <button id="btnSubmit" align="center" type="submit"
                                class="btn btn-success waves-effect waves-light">
                                Submit
                            </button>
                            <button id="btnFill" align="center" type="submit" class="btn btn-info waves-effect waves-light">
                                Fill Rates
                            </button>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterButtonRmDeductions) && !empty($filterButtonRmDeductions)) { ?>
                        <div class="text-center mt-3">
                            <button id="btnSearch" type="button" class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <a href="rm-deductions" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> Add Price
                            </a>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterButtonMasterCount) && !empty($filterButtonMasterCount)) { ?>
                        <div class="text-center mt-3">
                            <button id="btnSearch" type="button" class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <a href="master-add-counts" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> Add Count
                            </a>

                        </div>
                    <?php } ?>




                </div>
            </div>
        </div>
    </div>
</div>
