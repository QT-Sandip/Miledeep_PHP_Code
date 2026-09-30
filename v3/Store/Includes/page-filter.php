<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STORE_DROPDOWN;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resBuyer = $resDropdownsAPI->Data->Buyer;
$resProPlants = $resDropdownsAPI->Data->ProcessingPlants;
$resChemicalName = $resDropdownsAPI->Data->ChemicalName;
$resCountry = $resDropdownsAPI->Data->Country;
$resSupplier = $resDropdownsAPI->Data->Supplier;
$resPoNo = $resDropdownsAPI->Data->PoNo;
$resPort = $resDropdownsAPI->Data->Port;

// $CurrentMonth = date('m');
// $CurrentYear = date('Y');
// $PastYear = $CurrentYear - 1;
// $PastFY = $PastYear . '-' . substr($CurrentYear, -2);
// $CurrentFY = $CurrentYear . '-' . substr($CurrentYear + 1, -2);
// $Fromdate = $CurrentFY . '-04-01';
// $ToDate = date("Y-m-d");
// Determine the past FY and current FY based on the current month
// if ($CurrentMonth > 3) {
//     $PastFY = $CurrentYear - 1;
//     $CurrentFY = $CurrentYear;
// } else {
//     $PastFY = $CurrentYear - 2;
//     $CurrentFY = $CurrentYear - 1;
// }
$CurrentMonth = date('m');
$CurrentYear = date('Y');

if ($CurrentMonth >= 4) {
    $FYStartYear = $CurrentYear;
    $FYEndYear = $CurrentYear + 1;
} else {
    $FYStartYear = $CurrentYear - 1;
    $FYEndYear = $CurrentYear;
}
$CurrentFY = $FYStartYear . '-' . substr($FYEndYear, -2);
$PastFY = ($FYStartYear - 1) . '-' . substr($FYStartYear, -2);

$Fromdate = $FYStartYear . '-04-01';
$ToDate = $FYEndYear . '-03-31';

?>

<div class="row rowFilter">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">

                    <?php if (isset($filterBuyer) && !empty($filterBuyer)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpPlant">Buyer</label>
                            <select class="drpSelect" id="drpBuyer" name="drpBuyer">
                                <option value="">Select</option>
                                <?php
                                foreach ($resBuyer as $item)
                                    echo '<option value="' . $item->ChemBuyerID . '">' . $item->BuyerName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterProPlants) && !empty($filterProPlants)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpPlant">Plant</label>
                            <select class="drpSelect" id="drpProPlant" name="drpProPlant">
                                <option value="">Select</option>
                                <?php
                                foreach ($resProPlants as $item)
                                    echo '<option value="' . $item->ProcessingStationID . '">' . $item->StationName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterSupplier) && !empty($filterSupplier)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpSupplier">Supplier</label>
                            <select class="drpSelect" name="drpSupplier" id="drpSupplier">
                                <option value="">All</option>
                                <?php
                                foreach ($resSupplier as $item)
                                    echo '<option value="' . $item->ChemSupplierID . '">' . $item->SupplierName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterPoNo) && !empty($filterPoNo)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpPoNo">Po No</label>
                            <select class="drpSelect" name="drpPoNo" id="drpPoNo">
                                <option value="">All</option>
                                <?php
                                foreach ($resPoNo as $item)
                                    echo '<option value="' . $item->ChemPoID . '">' . $item->ChemPoNo . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterChemical) && !empty($filterChemical)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpChemical">Chemical</label>
                            <select class="drpSelect" name="drpChemical" id="drpChemical">
                                <option value="">All</option>
                                <?php
                                foreach ($resChemicalName as $item)
                                    echo '<option value="' . $item->ChemID . '">' . $item->ChemicalName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterCountry) && !empty($filterCountry)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpCountry">Country</label>
                            <select class="drpSelect" name="drpCountry" id="drpCountry">
                                <option value="">All</option>
                                <?php
                                foreach ($resCountry as $item)
                                    echo '<option value="' . $item->id . '">' . $item->name . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterPort) && !empty($filterPort)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpPort">Port </label>
                            <select class="drpSelect" name="drpPort" id="drpPort">
                                <option value="">All</option>
                                <?php
                                foreach ($resPort as $item)
                                    echo '<option value="' . $item->PortId . '">' . $item->PortName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                   


                    <?php if (isset($filterAuditType) && !empty($filterAuditType)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpAuditType">Audit Type</label>
                            <select class="drpSelect" id="drpAuditType" name="drpAuditType">
                                <option value="">All</option>
                                <option value="1">Farm</option>
                                <option value="2">Hatchery</option>
                                <option value="3">Feed Mill</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterStatus) && !empty($filterStatus)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpStatus">Status</label>
                            <select class="drpSelect" id="drpStatus" name="drpStatus">
                                <option value="1">Completed</option>
                                <option value="0">Pending</option>
                                <option value="2">Received</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterfromDate) && !empty($filterfromDate)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label for="txtFromDate">From Date</label>
                            <input type="date" value="<?php echo $Fromdate; ?>" class="form-control" name="txtFromDate"
                                id="txtFromDate">
                        </div>
                    <?php } ?>

                    <?php if (isset($filterfromDateTwo) && !empty($filterfromDateTwo)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label for="txtFromDate">From Date</label>
                            <input type="date" value="<?php echo $Fromdate; ?>" class="form-control" name="txtFromDate"
                                id="txtFromDate">
                        </div>
                    <?php } ?>

                    <?php if (isset($filtertoDate) && !empty($filtertoDate)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label for="txtToDate">To Date</label>
                            <input type="date" value="<?php echo $ToDate; ?>" class="form-control" name="txtToDate"
                                id="txtToDate">
                        </div>
                    <?php } ?>

                     <?php if (isset($filterYear) && !empty($filterYear)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpYear">Year</label>
                            <select class="drpSelect" id="drpYear" name="drpYear">
                                <option value="<?php echo $FYStartYear - 1; ?>">
                                    <?php echo ($FYStartYear - 1) . '-' . substr($FYStartYear, -2); ?>
                                </option>
                                <option value="<?php echo $FYStartYear; ?>" selected>
                                    <?php echo $CurrentFY; ?>
                                </option>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if (isset($filterMonth) && !empty($filterMonth)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Select Month</label>
                            <select class="form-control drpSelect" name="drpMonth" id="drpMonth">
                                <option value="">Select</option>
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

                    <?php if (isset($filterBillDate) && !empty($filterBillDate)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label for="txtBillDate">Bill Date</label>
                            <input type="date" value="<?php echo date('Y-m-d') ?>" class="form-control" name="txtBillDate"
                                id="txtBillDate">
                        </div>
                    <?php } ?>

                    <?php if (isset($filterYearNew) && !empty($filterYearNew)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="drpYear">Year</label>
                            <select class="drpSelect" id="drpYear" name="drpYear">
                                <option value="">Select Year</option>
                                <option value="<?php echo $PastYear ?>" <?php echo ($CurrentYear == $PastYear) ? 'selected' : ''; ?>><?php echo $PastFY ?> </option>
                                <option value="<?php echo $CurrentYear ?>" selected> <?php echo $CurrentFY ?> </option>
                            </select>
                        </div>
                    <?php } ?>


                    <?php if (isset($chemPoButton) && !empty($chemPoButton)) { ?>
                        <div class="text-center mt-1">
                            <button id="btnSearch" align="left" type="button"
                                class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <a href="GsChemPoCreate" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> Add PO
                            </a>
                        </div>
                    <?php } ?>

                    <?php if (isset($chemOrderButton) && !empty($chemOrderButton)) { ?>
                        <div class="text-center mt-1">
                            <button id="btnSearch" align="left" type="button"
                                class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <a href="GsChemOrdersCreate" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> Add Order
                            </a>
                        </div>
                    <?php } ?>

                    <?php if (isset($chemPaymentButton) && !empty($chemPaymentButton)) { ?>
                        <div class="text-center mt-1">
                            <button id="btnSearch" align="left" type="button"
                                class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <a href="GsChemPoPaymentCreate" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> Add Paymet
                            </a>
                        </div>
                    <?php } ?>

                    <?php if (isset($chemConsumptionButton) && !empty($chemConsumptionButton)) { ?>
                        <div class="text-center mt-1">
                            <button id="btnSearch" align="left" type="button"
                                class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <a href="GsChemConsumptionCreate" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> Add Consumption
                            </a>
                        </div>
                    <?php } ?>

                    <!-- Audit Screens -->
                    <?php if (isset($fsAuditButton) && !empty($fsAuditButton)) { ?>
                        <div class="text-center mt-1">
                            <button id="btnSearch" align="left" type="button"
                                class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <a href="FsAuditCreate" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> Add Audit
                            </a>
                        </div>
                    <?php } ?>

                    <?php if (isset($fsMillButton) && !empty($fsMillButton)) { ?>
                        <div class="text-center mt-1">
                            <button id="btnSearch" align="left" type="button"
                                class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <a href="FsAuditMillCreate" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> Add Mill
                            </a>
                        </div>
                    <?php } ?>

                    <?php if (isset($buyerButton) && !empty($buyerButton)) { ?>
                        <div class="text-center mt-1">
                            <button id="btnSearch" align="left" type="button"
                                class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <a href="GsChemBuyerCreate" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> Add Buyer
                            </a>
                        </div>
                    <?php } ?>

                    <?php if (isset($supplierButton) && !empty($supplierButton)) { ?>
                        <div class="text-center mt-1">
                            <button id="btnSearch" align="left" type="button"
                                class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <a href="GsChemSupplierCreate" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> Add Supplier
                            </a>
                        </div>
                    <?php } ?>

                    <?php if (isset($ChemicalButton) && !empty($ChemicalButton)) { ?>
                        <div class="text-center mt-1">
                            <button id="btnSearch" align="left" type="button"
                                class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <a href="GsChemicalCreate" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> Add Chemical
                            </a>
                        </div>
                    <?php } ?>

                    <?php if (isset($ReportButton) && !empty($ReportButton)) { ?>
                        <div class="text-center mt-1">
                            <button id="btnSearch" align="left" type="button"
                                class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>