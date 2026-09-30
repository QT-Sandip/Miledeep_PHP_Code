<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$AccessType = $_SESSION["AccessType"];

function checkEmpty($Value)
{
    if ($Value == '') {
        $Value = '--';
    }
    return $Value;
}

if ($_GET['chbkExp'] == '') {
    $chbkExp = 'true';
} else {
    $chbkExp = $_GET['chbkExp'];
}

if ($_GET['chbkShip'] == '') {
    $chbkShip = 'true';
} else {
    $chbkShip = $_GET['chbkShip'];
}

$Year = $_GET['Year'];
$CurrentMonth = date('m');

if ($Year == '') {
    $Currentyear = date('Y');
} else {
    $Currentyear = $Year;
}

$Startyear = $_SESSION["StartYear"]; //uma 10-04-2024
$endYear = $Currentyear + 1; //uma 10-04-2024


if ($CurrentMonth > 3 && $Year == '') {
    // $FinClsYear = $Currentyear;
    $FinClsYear = $Currentyear + 1; //uma
} else if ($Year != '') {
    $FinClsYear = $Year;
} else {
    // $FinClsYear = $Currentyear - 1;
    $FinClsYear = $Currentyear; //uma
}


if ($CurrentMonth > 3) {
    $RunningFinStartYear = date('Y');
} else {
    $RunningFinStartYear = (date('Y') - 1);
}


$DateStart = $FinClsYear . '-04-01';
$DateEnd = ($FinClsYear + 1) . '-03-31';

$PastYearStart = ($FinClsYear - 1) . '-04-01';
$PastYearEnd = ($FinClsYear) . '-03-31';
$PastNextYear = $FinClsYear . "-03-31";

$CurrentFincialYear = $DateStart;

// $api = API_RM_DROPDOWNS;
$api = API_COLDSTORE_DROPDOWNS;


$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resSpecies = $resDropdownsAPI->Data->Species;
// $resPurchaseCenters = $resDropdownsAPI->Data->PurchaseCenters; // Purchase Stations / Purchase centers 
// $resRmSuppliers = $resDropdownsAPI->Data->RmSuppliers;
// $resRmVariety = $resDropdownsAPI->Data->RmVariety;
$resProcessingStations = $resDropdownsAPI->Data->Plant;
$resConsigneeDetails = $resDropdownsAPI->Data->Consignee;
$resPoNumber = $resDropdownsAPI->Data->ShipoutPoNumber;
// echo $resPoNumber;exit;

echo '<script>';
echo 'const consigneeData = ' . json_encode($resConsigneeDetails) . ';';
echo 'const poData = ' . json_encode($resPoNumber) . ';';
echo '</script>';
// print_r($resPurchaseCenters);
// exit;
?>

<!doctype html>
<html lang="en">

<head>
    <?php include($IncludesPath . "title-meta.php"); ?>

    <style>
        label {
            font-size: 15px;
            color: black;
        }

        th {
            font-size: 12px;
        }

        .table tr:hover {
            background-color: #343a40 !important;
            color: #fff !important;
        }

        .table td,
        .table th {
            font-size: 12px;
            padding: 7px !important;
            vertical-align: top;
            border-top: 1px solid #eff2f7;
        }

        .customWrap {
            white-space: nowrap !important;
        }

        .warning-row {
            background-color: #f7ff84 !important;
            color: #111;
            font-weight: 500 !important;
        }

        .success-row {
            background-color: #fff !important;
        }

        .deadline-row {
            background-color: #df411d !important;
            color: #ffffff !important;
            font-weight: 500 !important;
        }

        .subTable {
            width: 100%;
            height: 100%
        }

        .subTable tr td {
            padding-left: 3px !important;
            padding-right: 3px !important;
        }

        #species-filter-container,
        #plant-filter-container,
        #center-filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .filter-item {
            padding: 5px 10px;
            background-color: #03624b;
            border: 1px solid #ccc;
            border-radius: 5px;
            display: flex;
            align-items: center;
            color: #eff2f7;
        }

        .close-button {
            margin-left: 5px;
            color: red;
            font-weight: bold;
            cursor: pointer;
        }

        .count-item {
            padding: 5px 10px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            user-select: none;
        }

        .count-item.selected {
            background-color: #007bff;
            color: white;
            border-color: #0056b3;
        }
    </style>


    <!-- jquery.vectormap css -->
    <link href="<?php echo $BaseDist ?>assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css"
        rel="stylesheet" type="text/css" />

    <!-- DataTables -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />

    <?php include($IncludesPath . "head-css.php"); ?>

    <style>
        .pending-contract {
            border: none;
            background: none;
            color: #343a40 !important;
            font-size: 1.35rem;
            font-family: Inter, sans-serif;
            font-weight: 500;
        }

        .pending-contract:hover {
            color: #5664d2 !important;
            text-decoration: underline;
        }

        @media (min-width: 992px) {

            .modal-lg,
            .modal-xl {
                max-width: 1230px;
                max-height: 210px;
            }
        }

        .modal .modal-body div#key-datatable_wrapper .row:nth-child(2) {
            height: 70vh;
        }
    </style>
</head>

<body data-sidebar="dark">
    <?php include "PopUpTable.php"; ?>
    <?php include($IncludesPath . "PreLoader.php"); ?>
    <div id="layout-wrapper">
        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>

        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
                        <!-- Global Search  -->
                        <div class="col-12">
                            <div class="card filterCnt">
                                <div class="card-body">

                                    <div class="row">
                                        <div class="col-lg-2 col-sm-6">
                                            <label for="drpSpecies">Species: </label>
                                            <select class="form-control select2" name="drpSpecies" id="drpSpecies"
                                                multiple="multiple">
                                                <option value=" ">All</option>
                                                <?php foreach ($resSpecies as $Item): ?>
                                                    <option value="<?php echo htmlspecialchars($Item->ShortCode); ?>">
                                                        <?php echo htmlspecialchars($Item->SpeciesName); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>



                                        <div class="col-lg-2 col-sm-6">
                                            <label for="drpProcessingStations">Plant: </label>
                                            <select class="form-control select2" id="drpProcessingStations"
                                                name="drpProcessingStations" multiple="multiple">
                                                <option value="">Select</option>
                                                <?php foreach ($resProcessingStations as $option): ?>
                                                    <option
                                                        value="<?php echo htmlspecialchars($option->ProcessingStationID); ?>">
                                                        <?php echo htmlspecialchars($option->StationName); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-lg-2 col-sm-6">
                                            <label for="drpConsignee">Consignee: </label>
                                            <select class="form-control select2" id="drpConsignee" name="drpConsignee">
                                                <option value="">Select</option>
                                                <?php foreach ($resConsigneeDetails as $option): ?>
                                                    <option value="<?php echo htmlspecialchars($option->ConID); ?>">
                                                        <?php echo htmlspecialchars($option->ConsigneeName); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-lg-2 col-sm-6">
                                            <label for="drpPoNo">Po Number: </label>
                                            <select class="form-control select2" id="drpPoNo" name="drpPoNo" disabled>
                                                <option value="">Select</option>
                                                <?php foreach ($resPoNumber as $option): ?>
                                                    <option value="<?php echo htmlspecialchars($option->PoID); ?>">
                                                        <?php echo htmlspecialchars($option->PoNo); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>


                                        <div class="col col-sm-12 text-center">
                                            <label style="display: block;" class="d-sm-none">&nbsp;</label>
                                            <button id="search" type="button"
                                                class="btn-filter btn btn-primary mb-2 waves-effect waves-light  text-left">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div style="display: flex; justify-content: space-between;">
                                        <h4 class="card-title mb-4">Quantity by SPECIES</h4>
                                        <span id="species-filter-container"></span>
                                    </div>
                                    <div id="sum-of-qty-by-species" style="height: 380px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-9">
                            <div class="card">
                                <div class="card-body">
                                    <div style="display: flex; justify-content: space-between;">
                                        <h4 class="card-title mb-4">Quantity by PLANT </h4>
                                        <span id="plant-filter-container"></span>
                                    </div>
                                    <div id="sum-of-qty-by-plant" style="height: 380px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <div style="display: flex; justify-content: space-between;">
                                        <h4 class="card-title mb-4">Quantity by Category</h4>
                                        <span id="category-filter-container"></span>
                                    </div>
                                    <div id="sum-of-qty-by-Category" style="height: 200px; width: 100%;"> </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <div style="display: flex; justify-content: space-between;">
                                        <h4 class="card-title mb-4">Quantity by Variety</h4>
                                        <span id="variety-filter-container"></span>
                                    </div>

                                    <div id="sum-of-qty-by-variety" style="height: 200px; width: 100%;"> </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <div style="display: flex; justify-content: space-between;">
                                        <h4 class="card-title mb-4">Quantity by PackingType</h4>
                                        <span id="packingtype-filter-container"></span>
                                    </div>
                                    <div id="sum-of-qty-by-PackingType" style="height: 200px; width: 100%;"> </div>
                                </div>
                            </div>
                        </div>


                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Quantity by Group</h4>
                                    <div id="sum-of-qty-by-group" style="height: 200px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Store Availability</h4>
                                    <div id="sum-of-qty-by-block" style="height: 350px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Order Status</h4>
                                    <div id="order-status-by-plant" style="height: 200px; width: 100%;"> </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="row">
                        <div class="col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Sum of Grading Quantity</h4>
                                    <div id="hl-grading-qty-by-plant" style="height: 200px; width: 100%;"> </div>
                                </div>
                            </div>
                        </div>
                        
                   
                        <div class="col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Sum of VA Quantity</h4>
                                    <div id="hl-va-qty-by-plant" style="height: 200px; width: 100%;"> </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Sum of Soaking Quantity</h4>
                                    <div id="hl-soaking-qty-by-plant" style="height: 200px; width: 100%;"> </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Sum of Freezing Quantity</h4>
                                    <div id="hl-freezing-qty-by-plant" style="height: 200px; width: 100%;"> </div>
                                </div>
                            </div>
                        </div>
                        
                    </div> -->
                </div>
            </div>
        </div>
    </div>
    <?php
    include($IncludesPath . "footer.php");
    ?>
    <div id="myModalFullScreen" class="modal fade" role="dialog" tabindex="-1">
        <div class="modal-dialog  modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header" style="padding: 5px 15px">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="PopUpTableFullScreen" class="col-md-12 scrollPopup"> </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    include($IncludesPath . "vendor-scripts.php");
    include($IncludesPath . "NewScripts.php");
    ?>

    <link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.js"></script>
    <script src="<?php echo $BaseDist ?>assets/js/canvasjs.min.js"></script>
    <script>
        var chartInstances = {};
        let appliedFilters = {
            species: [],
            centers: [],
            plants: [],
            category: [],
            freezing: [],
            variety: [],
            packingType: [],
        };
        $(document).ready(function () {
            $('#drpArrivalStationId, #drpProcessingStations, #drpSuppliers, #drpSpecies, #drpConsignee, #drpPoNo').select2({
                // $(' #drpProcessingStations,  #drpSpecies', '#drpConsignee').select2({
                placeholder: "Select", // Your placeholder text
            });

            $(document).off('click', '#search');
            $(document).on('click', '#search', function (e) {
                loadData();
            });

            loadData();
        });

        function renderFilters(data) {
            // Clear existing filters
            filterContainer = document.getElementById("plant-filter-container");
            filterContainer.innerHTML = "";
            filterContainer = document.getElementById("species-filter-container");
            filterContainer.innerHTML = "";
            filterContainer = document.getElementById("category-filter-container");
            filterContainer.innerHTML = "";
            filterContainer = document.getElementById("variety-filter-container");
            filterContainer.innerHTML = "";
            filterContainer = document.getElementById("packingtype-filter-container");
            filterContainer.innerHTML = "";


            Object.keys(appliedFilters).forEach((key) => {
                appliedFilters[key].forEach((value, index) => {
                    let filterContainer = '';
                    let valueName = value;

                    if (key == 'plants') {
                        filterContainer = document.getElementById("plant-filter-container");
                        valueName = data?.PlantData[0].PlantName;
                    } else if (key == 'species') {
                        filterContainer = document.getElementById("species-filter-container");
                    } else if (key == 'centers') {
                        filterContainer = document.getElementById("center-filter-container");
                        valueName = data?.CenterData[0].CenterName;
                    } else if (key == 'category' || key == 'freezing') {
                        filterContainer = document.getElementById("category-filter-container");
                        valueName = data?.ProductCategoryData?.[0].ProductTypeName;
                    } else if (key == 'variety') {
                        filterContainer = document.getElementById("variety-filter-container");
                        valueName = data?.VarietyData?.[0].VarietyName;
                    } else if (key == 'packingType') {
                        filterContainer = document.getElementById("packingtype-filter-container");
                        valueName = data?.PackingTypeData?.[0].PackingTypeName;
                    }

                    valueName = valueName ? valueName : (appliedFilters.category.length > 0 ? appliedFilters.category[0] : '');
                    filterContainer.innerHTML = "";
                    // Create filter item
                    const filterItem = document.createElement("div");
                    filterItem.className = "filter-item";
                    filterItem.textContent = `${valueName} `;

                    // Create close button
                    const closeButton = document.createElement("span");
                    closeButton.className = "close-button";
                    closeButton.textContent = "×";
                    // closeButton.style.cursor = "pointer";

                    // Add click event to remove the filter
                    closeButton.addEventListener("click", () => {
                        removeFilter(key, index);
                    });

                    filterItem.appendChild(closeButton);
                    filterContainer.appendChild(filterItem);
                });
            });
        }

        function removeFilter(category, index) {
            // Remove the selected filter
            appliedFilters[category].splice(index, 1);
            let filterContainer = '';

            if (category == 'plants') {
                filterContainer = document.getElementById("plant-filter-container");
            } else if (category == 'species') {
                filterContainer = document.getElementById("species-filter-container");
            } else if (category == 'centers') {
                filterContainer = document.getElementById("center-filter-container");
            } else if (category == 'category' || category == 'freezing') {
                filterContainer = document.getElementById("category-filter-container");
            } else if (category == 'variety') {
                filterContainer = document.getElementById("variety-filter-container");
            } else if (category == 'packingType') {
                filterContainer = document.getElementById("packingtype-filter-container");
            }
            filterContainer.innerHTML = "";
            loadData();
        }

        // document.addEventListener('DOMContentLoaded', function () {
        $(document).on('change', '#drpConsignee', function (e) {

            const Consigneedropdown = document.getElementById('drpConsignee');
            const PoNumberDropdown = document.getElementById('drpPoNo');

            // Populate Co1 Dropdown
            consigneeData.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                Consigneedropdown.appendChild(option);
            });

            // Event Listener for Co1 Dropdown Change
            // Consigneedropdown.addEventListener('change', function () {
            const selectedConsigneeId = this.value;

            // Clear and disable Po2 Dropdown initially
            PoNumberDropdown.innerHTML = '<option value="">Select PO2</option>';
            PoNumberDropdown.disabled = true;

            if (!selectedConsigneeId) {
                return; // Exit if no selection
            }

            // Filter Po2 based on selected Co1
            const filteredPoList = poData.filter(item => item.ConsigneeID === selectedConsigneeId);

            // Populate Po2 Dropdown
            filteredPoList.forEach(item => {
                const option = document.createElement('option');
                option.value = item.PoID;
                option.textContent = item.PoNo;
                PoNumberDropdown.appendChild(option);
            });

            PoNumberDropdown.disabled = false; // Enable Po2 Dropdown
            // });
        });


        function loadData() {
            // var ArrivalStationId = $("#drpArrivalStationId").val().join(',');
            // var ProcessingStations = $("#drpProcessingStations").val().join(',');
            var plants = appliedFilters.plants.length > 0 ? appliedFilters.plants.join(',') : $("#drpProcessingStations").val().join(',');
            var species = appliedFilters.species.length > 0 ? appliedFilters.species.join(',') : ($("#drpSpecies").val() != '') ? $("#drpSpecies").val().join(',') : ['BT', 'VM'].join(',');
            var ProductCategory = appliedFilters.category.length > 0 ? appliedFilters.category.join(',') : '';
            var FreezingType = appliedFilters.freezing.length > 0 ? appliedFilters.freezing.join(',') : '';
            var PackingTypeData = appliedFilters.packingType.length > 0 ? appliedFilters.packingType.join(',') : '';
            var Variety = appliedFilters.variety.length > 0 ? appliedFilters.variety.join(',') : '';
            var ConsigneeID = $("#drpConsignee").val();
            var PoID = $("#drpPoNo").val();
            // alert(PoID);
            $.ajax({
                url: 'Ajax/ajax-cs-dashboard.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    'Species': species,
                    // 'Center': ArrivalStationId,
                    'Plant': plants,
                    'Consignee': ConsigneeID,
                    'PoID': PoID,
                    'ProductCategory': ProductCategory,
                    'FreezingType': FreezingType,
                    'PackingTypeData': PackingTypeData,
                    'Variety': Variety,
                    // 'FromDate': $('#txtFromDate').val(),
                    // 'ToDate': $('#txtToDate').val(),
                },
                success: function (data) {
                    var error = data.Error;
                    var msg = data.Msg;
                    if (error === null) {
                        renderFilters(data);
                        renderSumOfQtyBySpeciesGraph(data?.SpeciesData);
                        renderSumOfQtyByPlantGraph(data?.PlantData);
                        renderSumOfQtyByCenterGraph(data?.StoreData);
                        renderSumOfQtyByGroupGraph(data?.GroupQtyData);
                        renderSumOfQtyByCategoryGraph(data?.ProductCategoryData);
                        renderSumOfQtyByPackingTypeGraph(data?.PackingTypeData);
                        renderSumOfQtyByVarietyGraph(data?.VarietyData);
                        renderOrderStatus(data?.OrderData);
                        //  renderPlantVAQty(data?.VAData);
                    } else {
                        alert(error);
                        return false;
                    }
                }
            });
        }

        function renderSumOfQtyBySpeciesGraph(data) {
            const labels = data?.map(item => item.SpeciesName);
            const seriesData = data?.map(item => parseFloat(item.SpeciesQty));

            let series = [];
            series.push({
                'name': 'Quantity',
                'data': seriesData
            });
            renderGraph(data, {
                series: series || [],
                labels: labels || [],
                columnWidth: '15%'
            }, 'sum-of-qty-by-species', '', "SHOW", function (event, chartContext, config) {
                var dataPointIndex = config.dataPointIndex;
                const selectValue = labels[dataPointIndex];

                if (appliedFilters.species.indexOf(selectValue) != -1) {
                    appliedFilters.species = appliedFilters.species.filter(species => species !== selectValue);
                } else {
                    appliedFilters.species.push(selectValue);
                }
                loadData();
            });
        }

        function renderSumOfQtyByPlantGraph(data) {
            const labels = data?.map(item => item.PlantName);
            const seriesData = data?.map(item => parseFloat(item.PlantQty));

            let series = [];
            series.push({
                'name': 'Quantity',
                'data': seriesData
            });


            renderGraph(data, {
                series: series || [],
                labels: labels || [],
                columnWidth: '15%'
            }, 'sum-of-qty-by-plant', '', "SHOW", function (event, chartContext, config) {
                var seriesIndex = config.seriesIndex;
                var dataPointIndex = config.dataPointIndex;
                const selectValue = data[dataPointIndex].PlantID;

                if (appliedFilters.plants.indexOf(selectValue) != -1) {
                    appliedFilters.plants = appliedFilters.plants.filter(plants => plants !== selectValue);
                } else {
                    appliedFilters.plants.push(selectValue);
                }
                loadData();
            });
        }
        function renderOrderStatus(data) {
            const labels = data?.map(item => item.StationName);
            const OrderMcs = data?.map(item => parseFloat(item.OrderMcs));
            const AvalMcs = data?.map(item => parseFloat(item.AvalMcs));

            // let series = [];

            // series.push({
            //     'name': 'Order Mcs',
            //     'data': OrderMcs
            // });
            // series.push({
            //     'name': 'Packed Mcs',
            //     'data': AvalMcs
            // });


            // renderGraph({
            //     series: series || [],
            //     labels: labels || []

            // const labels = data?.map(item => item.ProductCategoryName);
            const seriesData = data?.map(item => parseFloat(item.AvalMcs));
            renderGraph(data, {
                series: seriesData || [],
                labels: labels || []
            }, 'order-status-by-plant', 'PIE', "SHOW", function (event, chartContext, config) {

            });
        }

        function renderSumOfQtyByCenterGraph(data) {
            const labels = data?.map(item => item.BlockName);
            const TotalRacks = data?.map(item => parseFloat(item.TotalRacks));
            const FilledRacks = data?.map(item => parseFloat(item.Filled));

            let series = [];

            series.push({
                'name': 'Total Racks',
                'data': TotalRacks
            });
            series.push({
                'name': 'Filled Racks',
                'data': FilledRacks
            });


            renderGraph(data, {
                series: series || [],
                labels: labels || []
            }, 'sum-of-qty-by-block', '', null, function (event, chartContext, config) {

            });
        }





        function renderSumOfQtyByCategoryGraph(data) {
            const labels = data?.map(item => item.ProductTypeName);
            const labels1 = data?.map(item => item.ProductCategoryName);
            const labels2 = data?.map(item => item.FreezingType);
            const seriesData = data?.map(item => parseFloat(item.ProductCategoryQty));
            renderGraph(data, {
                series: seriesData || [],
                labels: labels || []
            }, 'sum-of-qty-by-Category', 'PIE', "SHOW", function (event, chartContext, config) {
                var dataPointIndex = config.dataPointIndex;
                const selectValue = labels1[dataPointIndex];
                const freezingValue = labels2[dataPointIndex];

                if (appliedFilters.category.indexOf(selectValue) != -1) {
                    appliedFilters.category = appliedFilters.category.filter(category => category !== selectValue);
                } else {
                    appliedFilters.category.push(selectValue);
                }

                if (appliedFilters.freezing.indexOf(freezingValue) != -1) {
                    appliedFilters.freezing = appliedFilters.freezing.filter(freezing => freezing !== freezingValue);
                } else {
                    appliedFilters.freezing.push(freezingValue);
                }
                loadData();
            });
        }
        function renderSumOfQtyByVarietyGraph(data) {
            const labels = data?.map(item => item.VarietyName);
            const seriesData = data?.map(item => parseFloat(item.VarietyQty));
            renderGraph(data, {
                series: seriesData || [],
                labels: labels || []
            }, 'sum-of-qty-by-variety', 'PIE', "SHOW", function (event, chartContext, config) {

                var dataPointIndex = config.dataPointIndex;
                const selectValue = labels[dataPointIndex];

                if (appliedFilters.variety.indexOf(selectValue) != -1) {
                    appliedFilters.variety = appliedFilters.variety.filter(variety => variety !== selectValue);
                } else {
                    appliedFilters.variety.push(selectValue);
                }
                loadData();
            });
        }
        function renderSumOfQtyByPackingTypeGraph(data) {
            const labels = data?.map(item => item.PackingTypeName);
            const seriesData = data?.map(item => parseFloat(item.PackingTypeQty));
            renderGraph(data, {
                series: seriesData || [],
                labels: labels || []
            }, 'sum-of-qty-by-PackingType', 'PIE', "SHOW", function (event, chartContext, config) {
                var dataPointIndex = config.dataPointIndex;
                const selectValue = labels[dataPointIndex];

                if (appliedFilters.packingType.indexOf(selectValue) != -1) {
                    appliedFilters.packingType = appliedFilters.packingType.filter(packingType => packingType !== selectValue);
                } else {
                    appliedFilters.packingType.push(selectValue);
                }
                loadData();
            });
        }

        function renderSumOfQtyByGroupGraph(data) {
            const labels = data?.map(item => item.Grade);
            const seriesData = data?.map(item => parseFloat(item.GradeQty));

            let series = [];
            series.push({
                'name': 'Quantity',
                'data': seriesData
            });


            renderGraph(data, {
                series: series || [],
                labels: labels || []
            }, 'sum-of-qty-by-group', '', "SHOW", function (event, chartContext, config) {

            });
        }


        function renderPlantHlGradingQty(data) {
            const labels = data?.map(item => item.PlantName);
            const seriesData = data?.map(item => parseFloat(item.PlantHLQty));
            renderGraph(data, {
                series: seriesData || [],
                labels: labels || []
            }, 'hl-grading-qty-by-plant', '', "SHOW", function (event, chartContext, config) {

            });
        }


        function renderPlantVAQty(data) {
            const labels = data?.map(item => item.PlantName);
            const seriesData = data?.map(item => parseFloat(item.PlantVAQty));
            renderGraph(data, {
                series: seriesData || [],
                labels: labels || []
            }, 'hl-va-qty-by-plant', '', "SHOW", function (event, chartContext, config) {

            });
        }

        function renderGraph(mainData, data, divId, chartType, showCustomTooltip, callback) {
            const customColors = ['#3444c1', '#FF33F3']; // Example colors


            let customTooltip = chartType !== 'PIE'
                ? function ({ seriesIndex, dataPointIndex, w }) {
                    // For non-pie charts (e.g., bar charts)
                    var value = w.config.series[seriesIndex]?.data[dataPointIndex]; // Access data for bar charts
                    var additionalValue = mainData[dataPointIndex]?.AvalMcs || 'N/A'; // Fallback for missing data

                    return `
            <div style="background: #333; color: #fff; padding: 8px; border-radius: 4px;">
                <strong>Kgs: </strong>${value}<br>
                <strong>Mcs: </strong>${additionalValue}
            </div>
        `;
                }
                : function ({ seriesIndex, w }) {
                    // For pie charts
                    var value = w.config.series[seriesIndex]; // Access series for pie charts
                    var dataPointIndex = seriesIndex; // SeriesIndex is the dataPointIndex for pie charts
                    var additionalValue = mainData[dataPointIndex]?.AvalMcs || 'N/A'; // Fallback for missing data

                    return `
            <div style="background: #333; color: #fff; padding: 8px; border-radius: 4px;">
                <strong>Kgs: </strong>${value}<br>
                <strong>Mcs: </strong>${additionalValue}
            </div>
        `;
                };

            if (showCustomTooltip == null) {
                customTooltip = undefined;
            }
            var Options = {
                series: data.series,
                stroke: {
                    width: [2, 2, 2, 2],
                    curve: "smooth"
                },
                chart: {
                    height: 400,
                    type: chartType == 'PIE' ? "pie" : "bar",
                    toolbar: {
                        show: !1
                    },
                    events: {
                        dataPointSelection: function (event, chartContext, config) {
                            callback(event, chartContext, config);
                        }
                    }
                },

                plotOptions: {
                    bar: {
                        horizontal: !1,
                        columnWidth: data?.columnWidth || "30%"
                    },

                    pie: {
                        size: chartType === 'PIE' ? '60%' : undefined
                    }
                },
                dataLabels: {
                    enabled: data.dataLabels ? data.dataLabels : false
                },
                legend: {
                    show: 1
                },
                noData: {
                    text: 'No data available', // Custom message
                    align: 'center', // Options: 'left', 'center', 'right'
                    verticalAlign: 'middle', // Options: 'top', 'middle', 'bottom'
                    offsetX: 0, // Horizontal offset
                    offsetY: 0, // Vertical offset
                    style: {
                        color: '#FF0000', // Text color
                        fontSize: '16px', // Font size
                        fontFamily: 'Arial' // Font family
                    }
                },
                labels: data.labels,
                tooltip: {
                    enabled: true,  // Enable the tooltip
                    y: {
                        formatter: function (val, opts) {
                            var dataPointIndex = opts?.dataPointIndex;
                            // var value = `${val} <br> Mcs : ${mainData[dataPointIndex].AvalMcs}`;
                            return val;
                        },
                    },
                    custom: customTooltip,

                }
            };

            if (chartInstances[divId]) {
                chartInstances[divId].destroy();
            }
            chart = new ApexCharts(document.querySelector("#" + divId), Options);
            chart.render();
            chartInstances[divId] = chart;
        }
    </script>
</body>

</html>