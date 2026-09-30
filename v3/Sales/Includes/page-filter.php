<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// $api = API_DROPDOWNS;
$api = API_EXPORTS_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resExporters = $resDropdownsAPI->Data->AdminExporters; // Admin Exporters

$resConsignee = $resDropdownsAPI->Data->Consignee; // Consignee 
$resProcessor = $resDropdownsAPI->Data->Processor; // Processor 

$resCountry = $resDropdownsAPI->Data->Country; // Countries

$resShipmentStatusCodes = $resDropdownsAPI->Data->ShipmentStatusCodes; // ShipmentStatus
$resBrand = $resDropdownsAPI->Data->Brand; // Brand
$resSpecies = $resDropdownsAPI->Data->Species; // Species
$resTypeProduct = $resDropdownsAPI->Data->TypeProduct; // TypeProduct
$resShortName = $resDropdownsAPI->Data->ShortName; // ShortName
$resExporterPacker = $resDropdownsAPI->Data->ExporterPacker;
$resLinerList = $resDropdownsAPI->Data->LinerList;

//var_dump($resDropdownsAPI);



$CurrentMonth = date('m');
$CurrentYear = date('Y');

$Startyear = $_SESSION["StartYear"]; //uma 10-04-2024
$endYear = $CurrentYear + 1; //uma 10-04-2024

// Determine the past FY and current FY based on the current month

if ($CurrentMonth > 3) {
    $FinClsYear = $CurrentYear + 1;
    $PastFY = $CurrentYear - 1;
    $CurrentFY = $CurrentYear;
} else {
    $FinClsYear = $CurrentYear;
    $PastFY = $CurrentYear - 2;
    $CurrentFY = $CurrentYear - 1;
}

// if ($CurrentMonth > 3) {
//     $PastFY = $CurrentYear - 1;
//     $CurrentFY = $CurrentYear;
// } else {
//     $PastFY = $CurrentYear - 2;
//     $CurrentFY = $CurrentYear - 1;
// }



$Fromdate = $CurrentFY . '-04-01';
$ToDate = date("Y-m-d");
$MonthwiseFromDate = date('Y-m-d', strtotime('-30 days'));
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

                    <?php if (isset($filterYear) && !empty($filterYear)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Select Year </label>
                            <select class="form-control drpSelect" name="drpYear" id="drpYear">
                                <?php
                                for ($Startyear; $Startyear <= $endYear; $Startyear++) {
                                    $year = ($Startyear - 1) . '-' . ($Startyear);
                                ?>
                                    <option <?php if ($Startyear == $FinClsYear) {
                                                echo 'selected="selected"';
                                            } ?> value="<?php echo $Startyear - 1 ?>"> <?php echo $year ?></option>
                                <?php } ?>
                            </select>
                        </div>

                    <?php } ?>



                    <!-- Durga -->

                    <?php if (isset($filterBrand) && !empty($filterBrand)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Select Brand:</label>
                            <select class="form-control drpSelect" id="drpBrandList" name="drpBrandList">
                                <option value="">All</option>
                                <?php
                                foreach ($resBrand as $item)
                                    echo '<option value="' . $item->ID . '">' . $item->Name . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterSpecies) && !empty($filterSpecies)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Species : </label>
                            <select class="form-control drpSelect" name="drpSpecies" id="drpSpecies">
                                <?php foreach ($resSpecies as $Species) { ?>
                                    <option value="<?php echo $Species->ShortCode ?>"><?php echo $Species->Description ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterSpecies2) && !empty($filterSpecies2)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Species : </label>
                            <select class="form-control drpSelect" name="drpSpecies2" id="drpSpecies2">
                                <option value="">All</option>
                                <?php foreach ($resSpecies as $Species) { ?>
                                    <option value="<?php echo $Species->ShortCode ?>"><?php echo $Species->SpeciesName ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterExportPacker) && !empty($filterExportPacker)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label for="drpType">Exporter/Processor</label>
                            <select class="form-control drpSelect" name="drpType" id="drpType">
                                <option value="">All</option>
                                <?php foreach ($resExporterPacker as $List) { ?>
                                    <option value="<?php echo $List->ExpID ?>"><?php echo $List->ExporterType ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterTypeProduct) && !empty($filterTypeProduct)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Type Product : </label>
                            <select class="form-control drpSelect" name="drpTypeProduct" id="drpTypeProduct">
                                <option value="">All</option>
                                <?php foreach ($resTypeProduct as $TypeProduct) { ?>
                                    <option value="<?php echo $TypeProduct ?>"><?php echo $TypeProduct ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterShortName) && !empty($filterShortName)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Product Form : </label>
                            <select class="form-control drpSelect" name="drpShortName" id="drpShortName">
                                <option value="">All</option>
                                <?php foreach ($resShortName as $ShortName) { ?>
                                    <option value="<?php echo $ShortName->ShortName ?>"><?php echo $ShortName->ShortName ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <!-- Durga -->

                    <?php if (isset($filterMonth) && !empty($filterMonth)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Select Month</label>
                            <select class="form-control drpSelect" name="drpMonth" id="drpMonth">
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

                    <?php if (isset($filterMonthSelect) && !empty($filterMonthSelect)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Month</label>
                            <select class="form-control drpSelect" name="drpMonth" id="drpMonth">
                                <?php
                                print '<option value="" selected>Select</option>';
                                for ($i = 1; $i <= 12; $i++) {
                                    print '<option value="' . $i . '">' . date('F', strtotime("$i/12/10")) . '</option>';
                                }
                                ?>
                                <select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterYearOne) && !empty($filterYearOne)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Select Year</label>
                            <select class="form-control drpSelect" name="drpYearOne" id="drpYearOne">
                                <option value="2020">2020</option>
                                <option value="2021">2021</option>
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                                <option value="2024" selected>2024</option>
                                <option value="2023">2025</option>
                            </select>
                        </div>
                    <?php } ?>

                    <!-- Durga -->

                    <?php if (isset($filterfromDate) && !empty($filterfromDate)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>From Date</label>
                            <input type="date" value="<?php echo $Fromdate; ?>" class="form-control" name="fromDate" id="fromDate">
                        </div>
                    <?php } ?>

                    <?php if (isset($filtermonthwisefromDate) && !empty($filtermonthwisefromDate)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>From Date</label>
                            <input type="date" value="<?php echo $MonthwiseFromDate; ?>" class="form-control" name="fromDate" id="fromDate">
                        </div>
                    <?php } ?>

                    <?php if (isset($filtertoDate) && !empty($filtertoDate)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>To Date</label>
                            <input type="date" value="<?php echo $ToDate; ?>" class="form-control" name="toDate" id="toDate">
                        </div>
                    <?php } ?>

                    <?php if (
                        (isset($filterExporter) && !empty($filterExporter)) ||
                        (isset($filterCompany) && !empty($filterCompany))
                    ) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Select
                                <?php
                                if (isset($filterExporter))
                                    echo "Exporter";
                                else if (isset($filterCompany))
                                    echo "Company";
                                ?> :
                            </label>
                            <select class="form-control drpSelect" id="drpExporterList" name="drpExporterList">
                                <option value="">Select</option>
                                <?php
                                foreach ($resExporters as $item)
                                    echo '<option value="' . $item->ExpID . '">' . $item->ExpName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (
                        (isset($filterConsignee) && !empty($filterConsignee)) ||
                        (isset($filterBuyer) && !empty($filterBuyer))
                    ) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Select
                                <?php
                                if (isset($filterConsignee))
                                    echo "Consignee";
                                else if (isset($filterBuyer))
                                    echo "Buyer";
                                ?> :
                            </label>
                            <select class="form-control drpSelect" id="drpConsigneeList" name="drpConsigneeList">
                                <option value="">All</option>
                                <?php
                                foreach ($resConsignee as $item)
                                    echo '<option value="' . $item->ConID . '">' . $item->ConsigneeName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (
                        (isset($filterProcessor) && !empty($filterProcessor)) ||
                        (isset($filterPlant) && !empty($filterPlant))
                    ) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Select
                                <?php
                                if (isset($filterProcessor))
                                    echo "Processor";
                                else if (isset($filterPlant))
                                    echo "Plant";
                                ?> :
                            </label>
                            <select class="form-control drpSelect" id="drpProcessorList" name="drpProcessorList">
                                <option value="">All</option>
                                <?php
                                foreach ($resProcessor as $item)
                                    echo '<option value="' . $item->ExpID . '">' . $item->ExpName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterCountry) && !empty($filterCountry)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Select Country:</label>
                            <select class="form-control drpSelect" id="drpCountryList" name="drpCountryList">
                                <option value="">All</option>
                                <?php
                                foreach ($resCountry as $item)
                                    echo '<option value="' . $item->ID . '">' . $item->name . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>


                    <?php if (isset($filterStatus) && !empty($filterStatus)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Status:</label>
                            <select class="form-control drpSelect" id="drpShimpmentStatuscodelist" name="drpShimpmentStatuscodelist">
                                <option value="">All</option>
                                <?php
                                foreach ($resShipmentStatusCodes as $item)
                                    echo '<option value="' . $item->ID . '">' . $item->Name . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (
                        (isset($filterInvoiceStatus) && !empty($filterInvoiceStatus)) ||
                        (isset($filterClearingAgentStatus) && !empty($filterClearingAgentStatus))
                    ) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>
                                <?php
                                if (isset($filterInvoiceStatus))
                                    echo "Invoice Status";
                                else if (isset($filterClearingAgentStatus))
                                    echo "Status";
                                ?> :
                            </label>
                            <select class="form-control drpSelect" name="drpStatus" id="drpStatus">
                                <option value="0">Pending</option>
                                <option value="1">Completed</option>
                                <!-- <option value="4">Rejected</option> -->

                            </select>
                        </div>
                    <?php } ?>

                    
                    <?php if (isset($filterLiner) && !empty($filterLiner)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Liner:</label>
                            <select class="form-control drpSelect" id="drpLinerList" name="drpLinerList">
                                <option value="">All</option>
                                <?php
                                foreach ($resLinerList as $item)
                                    echo '<option value="' . $item->LinerID . '">' . $item->LinerName . '</option>';
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (
                        (isset($filterCInvoiceStatus) && !empty($filterCInvoiceStatus)) ||
                        (isset($filterFdaStatus) && !empty($filterFdaStatus))
                    ) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>
                                <?php
                                if (isset($filterCInvoiceStatus))
                                    echo "Invoice Status";
                                else if (isset($filterFdaStatus))
                                    echo "Status";
                                ?> :
                            </label>
                            <select class="form-control drpSelect" name="drpStatus" id="drpStatus">
                                <option value="0">Pending</option>
                                <option value="1">Completed</option>
                                <option value="4">Rejected</option>

                            </select>
                        </div>
                    <?php } ?>


                    <?php if (isset($filterCustomsStatus) && !empty($filterCustomsStatus)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Bills Type: </label>
                            <select class="form-control drpSelect" name="drpStatus" id="drpStatus">
                                <option value="0">Pending</option>
                                <option value="1">Completed</option>
                                <option value="4">Rejected</option>
                                <option value="7">Customs Cancelled</option>
                                <option value="8">Customs Deleted</option>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if (isset($filterBillType) && !empty($filterBillType)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Bills Type: </label>
                            <select class="form-control drpSelect" name="drpBillType" id="drpBillType">
                                <option value="">All</option>
                                <option value="1">Collections Bills</option>
                                <option value="2">Negotiation Bills</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($inrExchange) && !empty($inrExchange)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label for="txtINRExchange">INR Exchange Value </label>
                            <input type="text" class="form-control" id="txtINRExchange" name="txtINRExchange" placeholder="Enter INR Exchange Value" />
                        </div>
                    <?php } ?>

                    <?php if (isset($filterPrority) && !empty($filterPrority)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Priority: </label>
                            <select class="form-control drpSelect" name="drpPrority" id="drpPrority">
                                <option value="">All</option>
                                <option value="High">High Priority</option>
                                <option value="Low">Low Priority</option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterContainerStatus) && !empty($filterContainerStatus)) { ?>
                        <div class="col col-md-3 mb-2">
                            <label>Status </label>
                            <select class="form-control drpSelect" name="drpReturnStatus" id="drpReturnStatus">
                                <option value="">Select</option>
                                <option value="Rejected">Rejected</option>
                                <option value="Cancelled">Cancelled [FDA/Custom]</option>
                                <option value="Destroyed">Destoryed</option>
                                <option value="Container ROB">Container ROB</option>
                                <option value="Return Container">Return Container
                                </option>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterSideButton) && !empty($filterSideButton)) { ?>
                        <!-- <div class="text-center"> -->
                        <div class="col col-md-3 mb-2">
                            <label class="invisible" style="display: block;">Status: </label> <!-- invisible -->
                            <input type="button" class="btn btn-primary " id="btnSearch" value="Search" aria-label="Search">
                        </div>
                        <!-- </div> -->
                    <?php } ?>


                    <?php if (isset($filterButtomButton) && !empty($filterButtomButton)) { ?>
                        <div class="text-center mt-3">
                            <button id="btnSearch" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <button id="newCoastingRate" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> New
                            </button>

                        </div>
                    <?php } ?>

                    <?php if (isset($filterButButton) && !empty($filterButButton)) { ?>
                        <div class="text-center mt-3">
                            <button id="btnSearch" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                        </div>
                    <?php } ?>

                    <?php if (isset($filterButtomProformaList) && !empty($filterButtomProformaList)) { ?>
                        <div class="text-center mt-3">
                            <button id="btnSearch" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <a href="PIGeneration" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> New PI
                            </a>
                            <button class="btn btn-warning waves-effect waves-light" data-bs-target="#modalBulkApproval" data-bs-toggle="modal" id="BtnPopUpBulk">
                                Bulk Approval
                            </button>
                        </div>
                    <?php } ?>

                    <!-- Durga -->
                    <?php if (isset($filterBottomVarietyList) && !empty($filterBottomVarietyList)) { ?>

                        <div class="text-center mt-3">
                            <button id="btnSearch" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <a href="AddVariety" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> New Variety
                            </a>

                        </div>

                    <?php } ?>

                    <!-- Durga -->
                    <?php if (isset($filterTaxChargeBottom) && !empty($filterTaxChargeBottom)) { ?>

                        <div class="text-center mt-3">
                            <button id="btnSearch" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <a href="create-tax-charges" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> Add Tax Charge
                            </a>
                        </div>
                    <?php } ?>

                    <!-- Durga -->
                    <?php if (isset($filterCountryBottom) && !empty($filterCountryBottom)) { ?>

                        <div class="text-center mt-3">
                            <button id="btnSearch" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <a href="create-countrywise-addresses" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> Add Address
                            </a>
                        </div>
                    <?php } ?>

                    <!-- Durga -->
                    <?php if (isset($filterReturnContainerBottom) && !empty($filterReturnContainerBottom)) { ?>

                        <div class="text-center mt-3">
                            <button id="btnSearch" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                            </button>
                            <!-- <a href="add-container-return-details" class="btn btn-success waves-effect waves-light">
                                <i class="fa fa-plus"></i> Add Container
                            </a> -->
                        </div>
                    <?php } ?>

                </div>
            </div>
        </div>
    </div>
</div>