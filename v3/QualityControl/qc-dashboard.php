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

$api = API_RM_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resSpecies = $resDropdownsAPI->Data->Species;
$resPurchaseCenters = $resDropdownsAPI->Data->PurchaseCenters; // Purchase Stations / Purchase centers 
$resRmSuppliers = $resDropdownsAPI->Data->RmSuppliers;
$resRmVariety = $resDropdownsAPI->Data->RmVariety;
$resProcessingStations = $resDropdownsAPI->Data->ProcessingStations;
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
                       
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <div style="display: flex; justify-content: space-between;">
                                        <h4 class="card-title mb-4" style="margin: 0px !important;">Code Status By Plant</h4>
                                        <span id="plant-filter-container"></span>
                                    </div>
                                    <div id="sum-of-qty-by-plant" style="height: 380px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <div style="display: flex; justify-content: space-between;">
                                        <h4 class="card-title mb-4" style="margin: 0px !important;">Sample Status By Labs</h4>
                                        <span id="species-filter-container"></span>
                                    </div>
                                    <div id="sum-of-qty-by-species" style="height: 380px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <div style="display: flex; justify-content: space-between;">
                                        <h4 class="card-title mb-4" style="margin: 0px !important;">Simp Doc By Plant</h4>
                                        <span id="center-filter-container"></span>
                                    </div>
                                    <div id="sum-of-qty-by-center" style="height: 350px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>

                    </div>


                </div>
            </div>
        </div>
    </div>
    <?php
    include($IncludesPath . "footer.php");
    ?>
    <!-- Modal -->
    <div id="myModal" style="display: none;">
        <div id="modalContent"
            style="background: white; padding: 20px; border-radius: 8px; max-width: 500px; margin: auto;">
            <!-- Content will be loaded here -->
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
        let chartInstances = {};
        let appliedFilters = {
            species: [],
            centers: [],
            plants: [],
        };

        let selectedCounts = [];

        $(document).ready(function () {
            $('#drpArrivalStationId, #drpProcessingStations, #drpSuppliers, #drpSpecies').select2({
                placeholder: "Select", // Your placeholder text
            });

            $(document).off('click', '#search');
            $(document).on('click', '#search', function (e) {
                loadData();
            });

            $(document).off('click', '#rm-search');
            $(document).on('click', '#rm-search', function (e) {
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
            filterContainer = document.getElementById("center-filter-container");
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
                    }

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
            }
            filterContainer.innerHTML = "";
            loadData();
        }

        function loadData() {

          //  var centers = appliedFilters.centers.length > 0 ? appliedFilters.centers.join(',') : $("#drpArrivalStationId").val().join(',');
            var plants = appliedFilters.plants.length > 0 ? appliedFilters.plants.join(',') : $("#drpProcessingStations").val().join(',');
          //  var species = appliedFilters.species.length > 0 ? appliedFilters.species.join(',') : $("#drpSpecies").val().join(',');
          //  var counts = selectedCounts.length > 0 ? selectedCounts.join(',') : '';


            $.ajax({
                url: 'Ajax/ajax-qc-dashboard.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    
                    'Plant': plants,
                   
                },
                success: function (data) {
                    var error = data.Error;
                    var msg = data.Msg;
                    if (error === null) {
                       // jQuery('.custom-button').parent('a').attr('href', 'https://camaroo.in/v3/Purchase/RM_RatesPrnt.php?id=' + data.RmRateID)
                        renderFilters(data);
                        renderLabGraph(data?.LabData);
                         renderSumOfQtyByPlantGraph(data?.PlantData);
                        // renderSumOfQtyByCenterGraph(data?.CenterData);
                        // renderSumOfQtyByGroupGraph(data?.GroupQtyData);
                        // renderSumOfQtyByVarietyGraph(data?.VarietyData);
                        //renderPlantHlGradingQty(data?.GradingData);
                        // renderPlantVAQty(data?.VAData);
                        // renderPlantHlGroupGradingQty(data?.ProductionData);
                        // renderPendingProductionDetails(data?.ProductionData);
                        // renderTimeVsPriceGrpah(data?.RmRates, data?.CountRange);
                    } else {
                        alert(error);
                        return false;
                    }
                }
            });
        }

        function renderLabGraph(data) {
            const labels = data?.map(item => item.TestingLab);
            const TotalSamples = data?.map(item => parseFloat(item.TotalSamples));
            const PendingReports = data?.map(item => parseFloat(item.PendingReports));
            let series = [];
            series.push({
                'name': 'Samples Sent',
                'data': TotalSamples
            });
            series.push({
                'name': 'Reports Pending',
                'data': PendingReports
            });
            renderGraph({
                series: series || [],
                labels: labels || [],
                columnWidth: '15%'
            }, 'sum-of-qty-by-species', "bar", function (event, chartContext, config) {
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
            const PlantQty = data?.map(item => parseFloat(item.PlantQty));
            const PendinCodeQty = data?.map(item => parseFloat(item.PendinCodeQty));
            let series = [];
            series.push({
                'name': 'Orders',
                'data': PlantQty
            });

            series.push({
                'name': 'Code Pending',
                'data': PendinCodeQty
            });

            renderGraph({
                series: series || [],
                labels: labels || [],
                columnWidth: '15%'
            }, 'sum-of-qty-by-plant', "bar", function (event, chartContext, config) {
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

        function renderSumOfQtyByCenterGraph(data) {
            const labels = data?.map(item => item.CenterName);
            const seriesData = data?.map(item => parseFloat(item.CenterQty));
            let series = [];
            series.push({
                'name': 'Quantity',
                'data': seriesData
            });
            renderGraph({
                series: series || [],
                labels: labels || [],
                columnWidth: '20%'
            }, 'sum-of-qty-by-center', "bar", function (event, chartContext, config) {
                var seriesIndex = config.seriesIndex;
                var dataPointIndex = config.dataPointIndex;
                const selectValue = data[dataPointIndex].CenterID;

                if (appliedFilters.centers.indexOf(selectValue) != -1) {
                    appliedFilters.centers = appliedFilters.centers.filter(centers => centers !== selectValue);
                } else {
                    appliedFilters.centers.push(selectValue);
                }
                loadData();
            });
        }

        function renderSumOfQtyByVarietyGraph(data) {
            const labels = data?.map(item => item.VarietyName);
            const seriesData = data?.map(item => parseFloat(item.CenterQty));
            renderGraph({
                series: seriesData || [],
                labels: labels || []
            }, 'sum-of-qty-by-variety', 'pie', function (event, chartContext, config) {

            });
        }

        function renderSumOfQtyByGroupGraph(data) {
            const labels = data?.map(item => item.GroupName);
            const seriesData = data?.map(item => parseFloat(item.GroupQty));
            let series = [];
            series.push({
                'name': 'Quantity',
                'data': seriesData
            });
            renderGraph({
                series: series || [],
                labels: labels || []
            }, 'sum-of-qty-by-group', "bar", function (event, chartContext, config) {

            });
        }


        function renderPlantHlGradingQty(data) {
            const labels = data?.map(item => item.PlantName);
            const seriesData = data?.map(item => parseFloat(item.PlantHLQty));
            let series = [];
            series.push({
                'name': 'Quantity',
                'data': seriesData
            });
            renderGraph({
                series: series || [],
                labels: labels || []
            }, 'hl-grading-qty-by-plant', "bar", function (event, chartContext, config) {

            });
        }


        function renderPlantVAQty(data) {
            const labels = data?.map(item => item.PlantName);
            const seriesData = data?.map(item => parseFloat(item.PlantVAQty));
            let series = [];
            series.push({
                'name': 'Quantity',
                'data': seriesData
            });
            renderGraph({
                series: series || [],
                labels: labels || []
            }, 'hl-va-qty-by-plant', "bar", function (event, chartContext, config) {

            });
        }

        function renderPlantHlGroupGradingQty(data) {
            const labels = data?.map(item => item.PlantNameWithYield);
            const FinalYieldData = data?.map(item => parseFloat(item.FinalYield));
            const FreezingQty = data?.map(item => parseFloat(item.FreezingQty));
            const HeadOnQtyData = data?.map(item => parseFloat(item.HeadOnQty));
            const HlYieldData = data?.map(item => parseFloat(item.HlYield));
            const GradingQty = data?.map(item => parseFloat(item.GradingQty));
            const SoakOutQty = data?.map(item => parseFloat(item.SoakOutQty));
            const VaOutQty = data?.map(item => parseFloat(item.VaOutQty));
            let series = [];
            // series.push({
            //     'name': 'FinalYield',
            //     'data': FinalYieldData
            // });

            series.push({
                'name': 'HeadOn Qty',
                'data': HeadOnQtyData
            });
            series.push({
                'name': 'Grading Qty',
                'data': GradingQty
            });

            series.push({
                'name': 'VaOut Qty',
                'data': VaOutQty
            });

            series.push({
                'name': 'SoakOut Qty',
                'data': SoakOutQty
            });

            series.push({
                'name': 'Freezing Qty',
                'data': FreezingQty
            });


            // series.push({
            //     'name': 'HlYield',
            //     'data': HlYieldData
            // });

            renderGraph({
                series: series || [],
                labels: labels || [],
            }, 'hl-grading-qty-by-plant-group', 'bar', function (event, chartContext, config) {

            });
        }

        function renderPendingProductionDetails(data) {
            const labels = data?.map(item => item.PlantName);
            const FinalYieldData = data?.map(item => parseFloat(item.FinalYield));
            const FreezingBalQty = data?.map(item => parseFloat(item.FreezingBalQty));
            const GradingQty = data?.map(item => parseFloat(item.GradingQty));
            const HlYieldData = data?.map(item => parseFloat(item.HlYield));
            const SoakBalQty = data?.map(item => parseFloat(item.SoakBalQty));
            const VaBalQty = data?.map(item => parseFloat(item.VaBalQty));
            let series = [];
            // series.push({
            //     'name': 'FinalYield',
            //     'data': FinalYieldData
            // });

            // series.push({
            //     'name': 'Grading Qty',
            //     'data': GradingQty
            // });

            series.push({
                'name': 'VA Balance',
                'data': VaBalQty
            });

            series.push({
                'name': 'Soaking Balance',
                'data': SoakBalQty
            });

            series.push({
                'name': 'Freezing Balance',
                'data': FreezingBalQty
            });


            // series.push({
            //     'name': 'HlYield',
            //     'data': HlYieldData
            // });

            renderGraph({
                series: series || [],
                labels: labels || [],
            }, 'hl-grading-qty-by-plant-pending-group', 'bar', function (event, chartContext, config) {

            });
        }

        function renderTimeVsPriceGrpah(ratesData, CountRange) {
            // Your data
            const data = ratesData;

            // Extracting data for each series
            const dates = data?.map(item => item.Date);
            const seriesData = {};

            if(dates !=null){
            // Dynamically extract keys and populate series data
            data.forEach(item => {
                Object.keys(item).forEach(key => {
                    if (key !== "Date") { // Skip the "Date" key
                        if (!seriesData[key]) {
                            seriesData[key] = []; // Initialize the array if not exists
                        }
                        seriesData[key].push(item[key]); // Add the value to the corresponding series
                    }
                });
            });

        }
            // Convert the seriesData object into the desired series array
            const series = Object.keys(seriesData).map(key => ({
                name: key,
                data: seriesData[key]
            }));


            const counts = CountRange.split(',');
            // To keep track of selected counts

            // Render the counts above the graph
            const countSelector = document.getElementById("count-selector");
            countSelector.innerHTML = '';
            counts.forEach(count => {
                const countItem = document.createElement("div");
                countItem.className = "count-item";
                countItem.textContent = count;

                // Click event for selection
                countItem.addEventListener("click", () => {
                    if (selectedCounts.includes(count)) {
                        // Deselect count
                        selectedCounts = selectedCounts.filter(c => c !== count);
                        countItem.classList.remove("selected");
                    } else {
                        // Select count
                        selectedCounts.push(count);
                        countItem.classList.add("selected");
                    }

                    // Update the graph based on selectedCounts
                    loadData();
                });

                if (selectedCounts.includes(count)) {
                    countItem.classList.add("selected");
                }

                countSelector.appendChild(countItem);
            });

            renderGraph({
                series: series || [],
                labels: [],
                xaxis: {
                    categories: dates,
                    title: {
                        text: 'Date'
                    }
                },
                yaxis: {
                    title: {
                        text: 'Rates'
                    }
                },
                dataLabels: true
            }, 'time-vs-price-grpah', "line", function (event, chartContext, config) {

            });
        }

        function renderGraph(data, divId, chartType, callback) {
            const customColors = ['#3444c1', '#FF33F3']; // Example colors

            var Options = {
                series: data.series,
                stroke: {
                    width: [2, 2, 2, 2],
                    curve: "smooth"
                },
                chart: {
                    height: 400,
                    type: chartType,
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
                        size: '30%'
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
                xaxis: data.xaxis,
                yaxis: data.yaxis
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