<?php


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$filterFromDate2 = "T";
$filterToDate2 = "T";
$filterSideButton = "T";


$api = API_EXPORTS_DROPDOWNS;

$resDropDown = CallAPI($api, "GET", "", "");

$statusList = $resDropDown->Data->CustomsCancelStatus;
// echo json_encode($resDropDown, JSON_PRETTY_PRINT);
// exit;
$antiBoiTicReportValues = [
    [
        'parameters' => 'CAP',
        'RPAµgPerKg' => 0.15,
        'CCβµgPerKg' => 0.075,
        'CutOffValueµgPerKg' => 0.04
    ],
    [
        'parameters' => 'AMOZ',
        'RPAµgPerKg' => 0.5,
        'CCβµgPerKg' => 0.25,
        'CutOffValueµgPerKg' => '0.200'
    ],
    [
        'parameters' => 'AOZ',
        'RPAµgPerKg' => 0.5,
        'CCβµgPerKg' => 0.25,
        'CutOffValueµgPerKg' => '0.100'
    ],
    [
        'parameters' => 'AHD',
        'RPAµgPerKg' => 0.5,
        'CCβµgPerKg' => 0.25,
        'CutOffValueµgPerKg' => '0.200'
    ],
    [
        'parameters' => 'SEM',
        'RPAµgPerKg' => 0.5,
        'CCβµgPerKg' => 0.25,
        'CutOffValueµgPerKg' => '0.500'
    ],
];



//Micro part
$microStaticValues = [
    [
        'parameters' => 'TPC',
        'ExternalLimit' => 'BELOW 5,00,000',
        'InternalLimit' => 'ABOVE 70,000'
    ],
    [
        'parameters' => 'SALMONELLA',
        'ExternalLimit' => 'ABSENT/25G',
        'InternalLimit' => 'ABSENT/25G'
    ],
    [
        'parameters' => 'STAPHYLOCOCCUS',
        'ExternalLimit' => '<100CFU/G',
        'InternalLimit' => '<10CFU/G'
    ],
    [
        'parameters' => 'E.COLI',
        'ExternalLimit' => '<20CFU/G',
        'InternalLimit' => '<20CFU/G'
    ],
    [
        'parameters' => 'COLIFORMS',
        'ExternalLimit' => '<3MPN/G',
        'InternalLimit' => '<3MPN/G'
    ],
    [
        'parameters' => 'VIBRIO CHOLERAE',
        'ExternalLimit' => 'ABSENT/25G',
        'InternalLimit' => 'ABSENT/25G'
    ],
    [
        'parameters' => 'VIBRIO PARAHAEMOLYTICUS',
        'ExternalLimit' => 'ABSENT/25G',
        'InternalLimit' => 'ABSENT/25G'
    ],
];



// Swab Static Values
$swabStaticValues = [
    ['parameters' => 'WORKERS SWAB', 'ExternalLimit' => '100CFU/SQCM', 'InternalLimit' => '80CFU/SQCM', 'Vibrio Cholerae' => 'ABSENT'],
    ['parameters' => 'FOOD CONTACT SURFACES SWAB', 'ExternalLimit' => '100CFU/SQCM', 'InternalLimit' => '80CFU/SQCM', 'Vibrio Cholerae' => '-'],
    ['parameters' => 'FLAKE ICE & PLATE ICE CHUTE', 'ExternalLimit' => '100CFU/SQCM', 'InternalLimit' => '80CFU/SQCM', 'Vibrio Cholerae' => 'ABSENT'],
];

// Water & Ice Static Values
$waterIceStaticValues = [
    ['parameters' => '22°C', 'ExternalLimit' => '100ML', 'InternalLimit' => '50/ML', 'Vibrio Cholerae' => 'ABSENT', 'Coliforms' => '<1MPN/ML'],
    ['parameters' => '37°C', 'ExternalLimit' => '20/ML', 'InternalLimit' => '15/ML', 'Vibrio Cholerae' => 'ABSENT', 'Coliforms' => '<1MPN/ML'],
    //['parameters' => 'E.COLI', 'ExternalLimit' => 'ABSENT/100ml', 'InternalLimit' => 'ABSENT/100ml'],
    //['parameters' => 'Pseudomonas', 'ExternalLimit' => '<10CFU/ml', 'InternalLimit' => '<10CFU/ml'],
];



?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>

    <style>
        .modal-fullscreen {
            max-width: 100%;
            width: 100%;
            margin: 0;
        }

        #PopUpTableFullScreen table {
            width: 100% !important;
        }

        .dataTables_scrollHeadInner {
            width: 100% !important;
        }


        /* Apply to rows containing the cards */
        .row-equal>.col-xl-6 {
            display: flex;
            /* Make columns flex */
        }

        .row-equal>.col-xl-6>.card {
            flex: 1;
            /* Make cards take equal height */
            display: flex;
            flex-direction: column;
            /* Keep card-body stacking content vertically */
        }

        .bottom-cards-row>.col-xl-6 {
            margin-bottom: 25px;
            /* space from footer */
            margin-top: 20px;
        }
    </style>

</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . '/includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php
                if(!isset($_SESSION['default_dashboard'])) 
                include_once ROOT . '/includes/page-header.php' 
                ?>
                <?php include_once ROOT . 'v3/QualityControl/includes/page-filter.php' ?>

                <!-- start page title -->

                <div class="row row-equal">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body">
                                <?php
                                if (!empty($antiBoiTicReportValues)) { ?>
                                    <h4 class="card-title mb-4" style="font-size: 20px; font-weight: bold;">Antibiotic</h4>

                                    <h4 class="card-title mb-4">Antibiotic Maximum Limits</h4>
                                    <table class="table table-bordered table-striped text-center">

                                        <thead class="thead-light">
                                            <tr>
                                                <th>Parameters </th>
                                                <th>RPA<br> (µg/Kg)</th>
                                                <th>CCβ <br>(µg/Kg)</th>
                                                <th>CutOffValue <br>(µg/Kg)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($antiBoiTicReportValues as $val) { ?>
                                                <tr>
                                                    <td><?php echo $val['parameters']; ?></td>
                                                    <td><?php echo $val['RPAµgPerKg']; ?></td>
                                                    <td><?php echo $val['CCβµgPerKg']; ?></td>
                                                    <td><?php echo $val['CutOffValueµgPerKg']; ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>

                                <?php } ?>
                                <h4 class="card-title mb-4">Antibiotic Report</h4>
                                <div id="antibioticGraph" style="height: 200px; width: 100%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body">
                                <?php if (!empty($microStaticValues)) { ?>
                                    <h4 class="card-title mb-4" style="font-size: 20px; font-weight: bold;">Micro</h4>
                                    <h4 class="card-title mb-4">Micro Maximum Limits</h4>
                                    <table class="table table-bordered table-striped text-center">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Parameters</th>
                                                <th>External Limit<br>(Finished Product)</th>
                                                <th>Internal Limit<br>(Finished Product)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($microStaticValues as $val) { ?>
                                                <tr>
                                                    <td><?php echo $val['parameters']; ?></td>
                                                    <td><?php echo $val['ExternalLimit']; ?></td>
                                                    <td><?php echo $val['InternalLimit']; ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                <?php } ?>

                                <h4 class="card-title mb-4">Micro Report</h4>
                                <div id="microGraph" style="height: 200px; width: 100%;"></div>
                            </div>
                        </div>
                    </div>

                </div>



                <div class="row bottom-cards-row row-equal">
                    <!-- Swab Card -->
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body">
                                <?php if (!empty($swabStaticValues)) { ?>
                                    <h4 class="card-title mb-4" style="font-size: 20px; font-weight: bold;">Swab</h4>

                                    <h4 class="card-title mb-4">Swab Maximum Limits</h4>
                                    <table class="table table-bordered table-striped text-center">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Parameters</th>
                                                <th>External Limit</th>
                                                <th>Internal Limit</th>
                                                <th>Vibrio Cholerae</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($swabStaticValues as $val) { ?>
                                                <tr>
                                                    <td><?php echo $val['parameters']; ?></td>
                                                    <td><?php echo $val['ExternalLimit']; ?></td>
                                                    <td><?php echo $val['InternalLimit']; ?></td>
                                                    <td><?php echo $val['Vibrio Cholerae']; ?></td>

                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                <?php } ?>
                                <h4 class="card-title mb-4">Swab Report</h4>
                                <div id="swabGraph" style="height:200px;width:100%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Water & Ice Card -->
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body">
                                <?php if (!empty($waterIceStaticValues)) { ?>
                                    <h4 class="card-title mb-4" style="font-size: 20px; font-weight: bold;">Water & Ice</h4>

                                    <h4 class="card-title mb-4">Water & Ice Maximum Limits</h4>
                                    <table class="table table-bordered table-striped text-center">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Parameters</th>
                                                <th>External Limit</th>
                                                <th>Internal Limit</th>
                                                <th>Vibrio Cholerae</th>
                                                <th>Coliforms</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($waterIceStaticValues as $val) { ?>
                                                <tr>
                                                    <td><?php echo $val['parameters']; ?></td>
                                                    <td><?php echo $val['ExternalLimit']; ?></td>
                                                    <td><?php echo $val['InternalLimit']; ?></td>
                                                    <td><?php echo $val['Vibrio Cholerae']; ?></td>
                                                    <td><?php echo $val['Coliforms']; ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                <?php } ?>
                                <h4 class="card-title mb-4">Water & Ice Report</h4>
                                <div id="waterIceGraph" style="height:200px;width:100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->
    <?php include_once ROOT . '/includes/footer.php' ?>

</div>
<!-- end main content-->

<div id="myModalFullScreen" class="modal fade" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header" style="padding: 10px 15px;">
                <h4 class="modal-title" id="modalTitle">Reports</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <!-- <span aria-hidden="true">&times;</span> -->
                </button>
            </div>
            <div class="modal-body">
                <div id="PopUpTableFullScreen" class="col-md-12"> </div>
            </div>
        </div>
    </div>
</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . '/includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
    type="text/javascript"></script>
<!-- <script src="<?php echo $BaseDist ?>assets/js/canvasjs.min.js"></script> -->

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    var table;
    var chartInstances = {};

    $(document).ready(function () {
        $('#txtFromDate').val(new Date().toISOString().slice(0, 10));
        $('#txtToDate').val(new Date().toISOString().slice(0, 10));
        getAntibioticGraphData();
        getMicroGraphData();
        getSwabGraphData();
        getWaterIceGraphData();
        // getFoodContactSurfaceGraphData();
        // similarly, if you have getFlakeIceGraphData():
        getFlakeIceGraphData();


        $(document).on('click', '#btnSearch', function () {
            getAntibioticGraphData();
            getMicroGraphData();
            getSwabGraphData();
            getWaterIceGraphData();
            // getFoodContactSurfaceGraphData();
            // similarly, if you have getFlakeIceGraphData():
            getFlakeIceGraphData();
        });
        // getAntibioticGraphData();
        // getMicroGraphData();

        $('#myModalFullScreen, #myModal').on('shown.bs.modal', function (e) {
            $("html").addClass("overflow-hidden");
            $(this).css({
                "padding": "0"
            });
        });

        $('#myModalFullScreen, #myModal').on('hidden.bs.modal', function (e) {
            $("html").removeClass("overflow-hidden");
        });


        // Export function
        function exportTableToExcel(tableId, filename) {
            var table = document.getElementById(tableId);
            var wb = XLSX.utils.table_to_book(table, {
                sheet: "Sheet JS"
            });
            XLSX.writeFile(wb, filename + '.xlsx');
        }

        function getAntibioticGraphData() {
            var fromDate = $('#txtFromDate').val();
            var toDate = $('#txtToDate').val();
            $.ajax({
                url: 'Ajax/AjaxDashboardGraphs.php',
                type: 'POST',
                data: {
                    'fromDate': fromDate,
                    'toDate': toDate,
                    'methodType': 'antibioticGraphData'
                },
                success: function (data) {
                    console.log("getAntibioticGraphData ", data);
                    var AryData = JSON.parse(JSON.stringify(data));
                    renderGraphData(AryData.Data, 'antibioticGraph', "antibioticData", "Antibiotic");
                }
            });
        }

        function getMicroGraphData() {
            var fromDate = $('#txtFromDate').val();
            var toDate = $('#txtToDate').val();
            $.ajax({
                url: 'Ajax/AjaxDashboardGraphs.php',
                type: 'POST',
                data: {
                    'fromDate': fromDate,
                    'toDate': toDate,
                    'methodType': 'microGraphData',

                },
                success: function (data) {
                    console.log("microGraphData ", data);
                    var AryData = JSON.parse(JSON.stringify(data));
                    renderGraphData(AryData.Data, 'microGraph', "microData", "Micro");
                }
            });
        }






        function getFlakeIceGraphData() {
            var fromDate = $('#txtFromDate').val();
            var toDate = $('#txtToDate').val();
            $.ajax({
                url: 'Ajax/AjaxDashboardGraphs.php',
                type: 'POST',
                data: {
                    'fromDate': fromDate,
                    'toDate': toDate,
                    'methodType': 'flakeIceGraphData'
                },
                success: function (data) {
                    var AryData = data && (typeof data === 'string') ? JSON.parse(data) : data;
                    if (!AryData.Data || !AryData.Data.series || !AryData.Data.months) {
                        $("#flakeIceGraph").html("<p class='text-center'>No Flake Ice Data Available</p>");
                        return;
                    }
                    renderGraphData(AryData.Data, 'flakeIceGraph', 'flakeIceData', 'Flake Ice');
                }
            });
        }







        function getSwabGraphData() {
            var fromDate = $('#txtFromDate').val();
            var toDate = $('#txtToDate').val();

            // $("#swabGraph").html("<p class='text-center'>Loading...</p>");

            $.ajax({
                url: 'Ajax/AjaxDashboardGraphs.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    fromDate,
                    toDate,
                    methodType: 'swabGraphData'
                },
                success: function (response) {
                    let data = response;

                    if (typeof data === 'string') {
                        try {
                            data = JSON.parse(data);
                        } catch (e) {
                            console.error("Error parsing swab graph data:", e);
                            $("#swabGraph").html("<p class='text-center'>Error parsing Swab Data</p>");
                            return;
                        }
                    }

                    if (!data || !data.Data ||
                        !Array.isArray(data.Data.series) ||
                        !Array.isArray(data.Data.months) ||
                        data.Data.series.length === 0 ||
                        data.Data.months.length === 0) {
                        $("#swabGraph").html("<p class='text-center'>No Swab Data Available</p>");
                        return;
                    }

                    const categories = data.Data.months;
                    const seriesArr = data.Data.series;


                    const workersSwabData = [];
                    const foodContactData = [];
                    const flakeIceData = [];
                    const detectedSamples = [];

                    categories.forEach(month => {
                        const monthData = seriesArr.find(item => item.month === month) || {};
                        workersSwabData.push(monthData['Workers Swab'] || 0);
                        foodContactData.push(monthData['FOOD CONTACT SURFACES SWAB'] || 0);
                        flakeIceData.push(monthData['FLATE ICE & PLATE ICE CHUTE'] || 0);
                        detectedSamples.push(monthData['Detected Samples'] || 0);

                    });


                    const formattedData = {
                        series: [{
                            name: 'Workers Swab',
                            data: workersSwabData,
                            color: '#008FFB'
                        },
                        {
                            name: 'Food Contact Surfaces',
                            data: foodContactData,
                            color: '#CFD026'
                        },
                        {
                            name: 'Flake Ice & Plate Ice Chute',
                            data: flakeIceData,
                            color: '#108b62ff'
                        },
                        {
                            name: 'Detected Samples',
                            data: detectedSamples,
                            color: '#FF0000'
                        }
                        ],
                        months: categories
                    };

                    renderGraphData(formattedData, 'swabGraph', 'swabData', 'Swab');
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error for swabGraphData:", status, error, xhr.responseText);
                    $("#swabGraph").html("<p class='text-center'>Error fetching Swab data.</p>");
                }
            });
        }



        // function getWaterIceGraphData() {
        //     var fromDate = $('#txtFromDate').val();
        //     var toDate = $('#txtToDate').val();

        //     // Include the last day
        //     // if (toDate) {
        //     //     var endDate = new Date(toDate);
        //     //     endDate.setDate(endDate.getDate() + 1);
        //     //     toDate = endDate.toISOString().slice(0, 10);
        //     // }

        //     $.ajax({
        //         url: 'Ajax/AjaxDashboardGraphs.php',
        //         type: 'POST',
        //         data: {
        //             'fromDate': fromDate,
        //             'toDate': toDate,
        //             'methodType': 'waterIceGraphData'
        //         },
        //         success: function(data) {
        //             var AryData = (typeof data === 'string') ? JSON.parse(data) : data;

        //             if (!AryData.Data || !AryData.Data.series || !AryData.Data.months) {
        //                 $("#waterIceGraph").html("<p class='text-center'>No Water & Ice Data Available</p>");
        //                 return;
        //             }

        //             renderGraphData(AryData.Data, 'waterIceGraph', 'waterIceData', 'Water & Ice');
        //         }
        //     });
        // }


        function getWaterIceGraphData() {
            var fromDate = $('#txtFromDate').val();
            var toDate = $('#txtToDate').val();
            $.ajax({
                url: 'Ajax/AjaxDashboardGraphs.php',
                type: 'POST',
                data: {
                    'fromDate': fromDate,
                    'toDate': toDate,
                    'methodType': 'waterIceGraphData'
                },
                success: function (data) {
                    var AryData = (typeof data === 'string') ? JSON.parse(data) : data;

                    if (!AryData.Data || !AryData.Data.series || !AryData.Data.months) {
                        $("#waterIceGraph").html("<p class='text-center'>No Water & Ice Data Available</p>");
                        return;
                    }


                    //  Add custom color only for TPC Result 2
                    if (AryData.Data.series.length > 1) {
                        AryData.Data.series[1].color = "#a8e65cff"; // Any color you want
                    }


                    // This will now draw two bars per month (TPC 22°C & TPC 37°C)
                    renderGraphData(AryData.Data, 'waterIceGraph', 'waterIceData', 'Water & Ice');
                }
            });
        }




        // function renderGraphData(data, tableId, methodType, filename) {

        //     // Check if data is empty
        //     if (!data || !data.months || !data.series || data.months.length === 0 || data.series.length === 0) {
        //         data = {
        //             months: ["No Data"],
        //             series: [{
        //                 name: "Samples",
        //                 data: [0]
        //             }]
        //         };
        //     }

        //     var options = {
        //         chart: {
        //             type: 'bar',
        //             height: 300,
        //             toolbar: {
        //                 show: false
        //             },
        //             events: {
        //                 dataPointSelection: function(event, chartContext, config) {
        //                     var seriesIndex = config.seriesIndex;
        //                     var dataPointIndex = config.dataPointIndex;
        //                     var name = config.w.config.series[seriesIndex]['name'];
        //                     var plantName = config.w.config.xaxis.categories[dataPointIndex];

        //                     if (data.months[dataPointIndex] !== "No Data") {
        //                         getPopupTableData(data.months[dataPointIndex], name, methodType, filename);
        //                     }
        //                 }
        //             }
        //         },
        //         series: data.series,
        //         xaxis: {
        //             categories: data.months,
        //             labels: {
        //                 rotate: -45,
        //                 rotateAlways: true,
        //                 hideOverlappingLabels: true,
        //                 showDuplicates: false,
        //                 trim: false,
        //                 minHeight: 50,
        //                 maxHeight: 80,
        //                 style: {
        //                     fontSize: '11px',
        //                     fontWeight: 400,
        //                     colors: ['#304758']
        //                 },
        //                 offsetY: 0
        //             }
        //         },
        //         yaxis: {
        //             title: {
        //                 text: 'Samples'
        //             }
        //         },
        //         plotOptions: {
        //             bar: {
        //                 horizontal: false,
        //                 dataLabels: {
        //                     position: 'top'
        //                 },
        //                 columnWidth: '50%'
        //             }
        //         },
        //         dataLabels: {
        //             enabled: false,
        //             formatter: function(val) {
        //                 return (val > 0) ? val : '';
        //             },
        //             offsetY: -10,
        //             style: {
        //                 fontSize: '12px',
        //                 colors: ["#304758"],
        //                 rotate: -45
        //             }
        //         },
        //         legend: {
        //             show: true
        //         },
        //         tooltip: {
        //             enabled: true,
        //             y: {
        //                 formatter: function(val) {
        //                     return val;
        //                 }
        //             },
        //             style: {
        //                 fontSize: '12px'
        //             }
        //         }
        //     };

        //     if (chartInstances[tableId]) {
        //         chartInstances[tableId].destroy();
        //     }

        //     var chart = new ApexCharts(document.querySelector("#" + tableId), options);
        //     chart.render().then(() => {
        //         const dataLabels = document.querySelectorAll('.apexcharts-text.apexcharts-datalabel');
        //         dataLabels.forEach(label => {
        //             label.style.transform = 'rotate(-45deg)';
        //             label.style.transformOrigin = 'center';
        //         });
        //     });

        //     chartInstances[tableId] = chart;
        // }


        // function renderGraphData(data, tableId, methodType, filename) {
        //     // Check if data is empty
        //     if (!data || !data.months || !data.series || data.months.length === 0 || data.series.length === 0) {
        //         data = {
        //             months: ["No Data"],
        //             series: [{
        //                 name: "Samples",
        //                 data: [0]
        //             }]
        //         };
        //     }

        //     // Map series names to correct popup methodType
        //     const seriesToMethodMap = {
        //         'Workers Swab': 'swabData',
        //         'Food Contact Surfaces': 'foodContactSurfaceData',
        //         'Flake Ice & Plate Ice Chute': 'flakeIceData',
        //         'Water & Ice': 'waterIceData',
        //         'Micro': 'microData',
        //         'Antibiotic': 'antibioticData'
        //         // Add more mappings if needed
        //     };

        //     var options = {
        //         chart: {
        //             type: 'bar',
        //             height: 300,
        //             toolbar: {
        //                 show: false
        //             },
        //             events: {
        //                 dataPointSelection: function(event, chartContext, config) {
        //                     const seriesIndex = config.seriesIndex;
        //                     const dataPointIndex = config.dataPointIndex;
        //                     const seriesName = config.w.config.series[seriesIndex].name.trim(); // remove extra spaces
        //                     const month = config.w.config.xaxis.categories[dataPointIndex];

        //                     // Determine correct popupMethodType: first check map, fallback to methodType
        //                     let popupMethodType = seriesToMethodMap[seriesName] || methodType;

        //                     console.log('Click:', seriesName, month, popupMethodType); // Debug

        //                     if (month !== "No Data" && popupMethodType) {
        //                         getPopupTableData(month, seriesName, popupMethodType, seriesName);
        //                     }
        //                 }
        //             }
        //         },

        //         colors: data.series.map(s =>
        //             s.name.trim() === 'Detected Samples' ? '#FF0000' : '#26A0FC'
        //         ),





        //         series: data.series,
        //         xaxis: {
        //             categories: data.months,
        //             labels: {
        //                 rotate: -45,
        //                 rotateAlways: true,
        //                 hideOverlappingLabels: true,
        //                 showDuplicates: false,
        //                 trim: false,
        //                 minHeight: 50,
        //                 maxHeight: 80,
        //                 style: {
        //                     fontSize: '11px',
        //                     fontWeight: 400,
        //                     colors: ['#304758']
        //                 },
        //                 offsetY: 0
        //             }
        //         },
        //         yaxis: {
        //             title: {
        //                 text: 'Samples'
        //             }
        //         },
        //         plotOptions: {
        //             bar: {
        //                 horizontal: false,
        //                 dataLabels: {
        //                     position: 'top'
        //                 },
        //                 columnWidth: '50%'
        //             }
        //         },
        //         dataLabels: {
        //             enabled: false,
        //             formatter: function(val) {
        //                 return (val > 0) ? val : '';
        //             },
        //             offsetY: -10,
        //             style: {
        //                 fontSize: '12px',
        //                 colors: ["#304758"],
        //                 rotate: -45
        //             }
        //         },
        //         legend: {
        //             show: true
        //         },
        //         tooltip: {
        //             enabled: true,
        //             y: {
        //                 formatter: function(val) {
        //                     return val;
        //                 }
        //             },
        //             style: {
        //                 fontSize: '12px'
        //             }
        //         }
        //     };

        //     // Destroy existing chart if exists
        //     if (chartInstances[tableId]) chartInstances[tableId].destroy();

        //     var chart = new ApexCharts(document.querySelector("#" + tableId), options);
        //     chart.render().then(() => {
        //         // Rotate data labels properly
        //         const dataLabels = document.querySelectorAll('.apexcharts-text.apexcharts-datalabel');
        //         dataLabels.forEach(label => {
        //             label.style.transform = 'rotate(-45deg)';
        //             label.style.transformOrigin = 'center';
        //         });
        //     });

        //     chartInstances[tableId] = chart;
        // }


        function renderGraphData(data, tableId, methodType, filename) {
            // Check if data is empty
            if (!data || !data.months || !data.series || data.months.length === 0 || data.series.length === 0) {
                data = {
                    months: ["No Data"],
                    series: [{
                        name: "Samples",
                        data: [0]
                    }]
                };
            }

            // Map series names to correct popup methodType
            const seriesToMethodMap = {
                'Workers Swab': 'swabData',
                'Food Contact Surfaces': 'foodContactSurfaceData',
                'Flake Ice & Plate Ice Chute': 'flakeIceData',
                'Water & Ice': 'waterIceData',
                'Micro': 'microData',
                'Antibiotic': 'antibioticData'
            };

            var options = {
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: {
                        show: false
                    },
                    events: {
                        dataPointSelection: function (event, chartContext, config) {
                            const seriesIndex = config.seriesIndex;
                            const dataPointIndex = config.dataPointIndex;
                            const seriesName = config.w.config.series[seriesIndex].name.trim();
                            const month = config.w.config.xaxis.categories[dataPointIndex];

                            // Determine correct popupMethodType
                            let popupMethodType = seriesToMethodMap[seriesName] || methodType;

                            // Determine TPC type for Water & Ice
                            let ReqType = seriesName;
                            // if (seriesName === 'Water & Ice') {
                            //     // Decide TPC type based on series or dataPoint
                            //     // If you have multiple TPC series in the chart, you can differentiate here
                            //     // Example: using the series name or some data mapping
                            //     // For simplicity, if value > 1000 use TPC2, else TPC1 (replace logic as per your data)
                            //     const value = config.w.config.series[seriesIndex].data[dataPointIndex];
                            //     ReqType = (value && value > 1000) ? 'TPC Result 2' : 'TPC Result 1';
                            // }



                            // if (seriesName === 'Water & Ice') {
                            //     if (seriesName.includes('Detected') || seriesName === 'Detected Samples') {
                            //         ReqType = 'Detected Samples';
                            //     } else {
                            //         const value = config.w.config.series[seriesIndex].data[dataPointIndex];
                            //         ReqType = (value && value > 1000) ? 'TPC Result 2' : 'TPC Result 1';
                            //     }
                            // }


                            // ✅ Water & Ice specific handling
                            if (popupMethodType === 'waterIceData') {
                                if (seriesName.toLowerCase().includes('detected')) {
                                    ReqType = 'Detected Samples'; // This will trigger OnlyDetected=1
                                } else if (seriesName.toLowerCase().includes('tpc 2')) {
                                    ReqType = 'TPC Result 2';
                                } else {
                                    ReqType = 'TPC Result 1';
                                }
                            }


                            console.log('Click:', seriesName, month, popupMethodType, ReqType);

                            if (month !== "No Data" && popupMethodType) {
                                getPopupTableData(month, ReqType, popupMethodType, seriesName);
                            }
                        }
                    }
                },

                colors: data.series.map(s =>
                    s.name.trim() === 'Detected Samples' ? '#FF0000' : '#26A0FC'
                ),

                series: data.series,
                xaxis: {
                    categories: data.months,
                    labels: {
                        rotate: -45,
                        rotateAlways: true,
                        hideOverlappingLabels: true,
                        showDuplicates: false,
                        trim: false,
                        minHeight: 50,
                        maxHeight: 80,
                        style: {
                            fontSize: '11px',
                            fontWeight: 400,
                            colors: ['#304758']
                        },
                        offsetY: 0
                    }
                },
                yaxis: {
                    title: {
                        text: 'Samples'
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        dataLabels: {
                            position: 'top'
                        },
                        columnWidth: '50%'
                    }
                },
                dataLabels: {
                    enabled: false,
                    formatter: function (val) {
                        return (val > 0) ? val : '';
                    },
                    offsetY: -10,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"],
                        rotate: -45
                    }
                },
                legend: {
                    show: true
                },
                tooltip: {
                    enabled: true,
                    y: {
                        formatter: function (val) {
                            return val;
                        }
                    },
                    style: {
                        fontSize: '12px'
                    }
                }
            };

            // Destroy existing chart if exists
            if (chartInstances[tableId]) chartInstances[tableId].destroy();

            var chart = new ApexCharts(document.querySelector("#" + tableId), options);
            chart.render().then(() => {
                const dataLabels = document.querySelectorAll('.apexcharts-text.apexcharts-datalabel');
                dataLabels.forEach(label => {
                    label.style.transform = 'rotate(-45deg)';
                    label.style.transformOrigin = 'center';
                });
            });

            chartInstances[tableId] = chart;
        }




        // function getPopupTableData(Processor, ReqType, methodType, filename) {
        //     console.log("Processor:", Processor, "ReqType:", ReqType);

        //     if (ReqType === 'Total Samples') return; // No popup for total samples

        //     var today = new Date();
        //     var fromDate, toDate;

        //     var userFrom = $('#txtFromDate').val();
        //     var userTo = $('#txtToDate').val();

        //     // Helper: Get last day of a given month
        //     function getLastDayOfMonth(year, month) {
        //         return new Date(year, month + 1, 0).getDate();
        //     }

        //     // Handle date selection logic
        //     if (userFrom && userTo) {
        //         fromDate = new Date(userFrom);
        //         toDate = new Date(userTo);

        //         var diffDays = (toDate - fromDate) / (1000 * 3600 * 24);

        //         if (diffDays > 30) {
        //             // reset to Processor month
        //             let [year, month] = Processor.split('-').map(Number);
        //             fromDate = new Date(year, month - 1, 1);
        //             toDate = new Date(year, month - 1, getLastDayOfMonth(year, month - 1));
        //         }
        //     } else {
        //         // No dates selected → fallback to Processor month
        //         let [year, month] = Processor.split('-').map(Number);
        //         fromDate = new Date(year, month - 1, 1);
        //         toDate = new Date(year, month - 1, getLastDayOfMonth(year, month - 1));
        //     }

        //     // Limit to today's date if in current month
        //     if (toDate > today) toDate = today;

        //     // Format dates as YYYY-MM-DD
        //     function formatDate(date) {
        //         let mm = String(date.getMonth() + 1).padStart(2, '0');
        //         let dd = String(date.getDate()).padStart(2, '0');
        //         return `${date.getFullYear()}-${mm}-${dd}`;
        //     }

        //     fromDate = formatDate(fromDate);
        //     toDate = formatDate(toDate);

        //     console.log("Using dates:", fromDate, "to", toDate);

        //     // Determine AJAX URL & table setup
        //     let ajaxUrl = '';
        //     let isHtmlResponse = false;
        //     let tableId = '';

        //     switch (methodType) {
        //         case 'antibioticData':
        //             ajaxUrl = 'Ajax/AjaxDashboardGraphs.php';
        //             isHtmlResponse = true;
        //             tableId = 'key-datatableFullscreen';
        //             break;
        //         case 'microData':
        //             ajaxUrl = 'Ajax/AjSwabProdList';
        //             tableId = 'microTable';
        //             break;
        //         case 'swabData':
        //             ajaxUrl = 'Ajax/Ajihmbrfwhlist';
        //             tableId = 'swabTable';
        //             break;
        //         case 'waterIceData':
        //             ajaxUrl = 'Ajax/Ajihmbrfwilist';
        //             tableId = 'waterIceTable';
        //             break;
        //         case 'foodContactSurfaceData':
        //             ajaxUrl = 'Ajax/Ajihmbrfwfslist.php';
        //             isHtmlResponse = false; // <-- must be JSON
        //             tableId = 'foodSurfaceTable';
        //             break;

        //         case 'flakeIceData':
        //             ajaxUrl = 'Ajax/Ajihmbrfcawflist.php';
        //             isHtmlResponse = false;
        //             tableId = 'flakeIceTable';
        //             break;
        //         default:
        //             console.error('Unknown chart type:', methodType);
        //             return;
        //     }

        //     $.ajax({
        //         url: ajaxUrl,
        //         type: 'POST',
        //         data: {
        //             fromDate: fromDate,
        //             toDate: toDate,
        //             methodType: methodType,
        //             reqType: ReqType,
        //             OnlyDetected: (methodType === 'microData' && ReqType === 'Detected Samples') ? 1 : 0
        //         },
        //         dataType: isHtmlResponse ? 'html' : 'json',
        //         success: function(response) {
        //             let html = '';

        //             if (isHtmlResponse) {
        //                 html = response; // Direct HTML response from backend
        //             } else {
        //                 // Build dynamic tables based on methodType
        //                 switch (methodType) {
        //                     case 'microData':
        //                         html = buildMicroTableHtml(response.data, tableId);
        //                         break;
        //                     case 'swabData':
        //                         html = buildSwabTableHtml(response.data, tableId);
        //                         break;
        //                     case 'waterIceData':
        //                         // html = buildWaterIceTableHtml(response.data, tableId);
        //                         // break;

        //                         if (ReqType === 'TPC Result 2') {
        //                             html = buildWaterIceTPC2TableHtml(response.data, tableId); // TPC 37°C
        //                         } else {
        //                             html = buildWaterIceTableHtml(response.data, tableId); // TPC 22°C
        //                         }
        //                         break;

        //                     case 'foodContactSurfaceData':
        //                         html = buildFoodSurfaceTableHtml(response.data, tableId); // <-- pass response.data, not response
        //                         break;

        //                     case 'flakeIceData':
        //                         html = buildFlakeIceTableHtml(response.data, tableId); // <-- pass response.data, not response
        //                         break;
        //                     default:
        //                         console.warn("No builder defined for", methodType);
        //                         html = "<p class='text-center'>No data available</p>";
        //                 }
        //             }

        //             $("#PopUpTableFullScreen").html(html);

        //             // Initialize DataTable safely
        //             if ($.fn.dataTable.isDataTable('#' + tableId)) {
        //                 $('#' + tableId).DataTable().destroy();
        //             }

        //             if ($('#' + tableId).length) {
        //                 $('#' + tableId).DataTable({
        //                     dom: 'rtip',
        //                     responsive: true,
        //                     scrollX: true,
        //                     scrollY: $('#PopUpTableFullScreen').height() - 20 + "px",
        //                     scrollCollapse: true,
        //                     paging: true,
        //                     info: true,
        //                     fixedHeader: true
        //                 });
        //             }

        //             $('#modalTitle').text(`${filename} ${ReqType || ''} ${fromDate} to ${toDate}`);
        //             $('#myModalFullScreen').modal('show');
        //         },
        //         error: function(xhr, status, error) {
        //             console.error('AJAX Error:', error);
        //             alert('Error loading data: ' + error);
        //         }
        //     });
        // }

        function getPopupTableData(Processor, ReqType, methodType, filename) {
            console.log("Processor:", Processor, "ReqType:", ReqType);

            if (ReqType === 'Total Samples') return; // No popup for total samples

            var today = new Date();
            var fromDate, toDate;

            var userFrom = $('#txtFromDate').val();
            var userTo = $('#txtToDate').val();

            // Helper: Get last day of a given month
            function getLastDayOfMonth(year, month) {
                return new Date(year, month + 1, 0).getDate();
            }

            // Handle date selection logic
            if (userFrom && userTo) {
                fromDate = new Date(userFrom);
                toDate = new Date(userTo);

                var diffDays = (toDate - fromDate) / (1000 * 3600 * 24);

                if (diffDays > 30) {
                    let [year, month] = Processor.split('-').map(Number);
                    fromDate = new Date(year, month - 1, 1);
                    toDate = new Date(year, month - 1, getLastDayOfMonth(year, month - 1));
                }
            } else {
                let [year, month] = Processor.split('-').map(Number);
                fromDate = new Date(year, month - 1, 1);
                toDate = new Date(year, month - 1, getLastDayOfMonth(year, month - 1));
            }

            if (toDate > today) toDate = today;

            function formatDate(date) {
                let mm = String(date.getMonth() + 1).padStart(2, '0');
                let dd = String(date.getDate()).padStart(2, '0');
                return `${date.getFullYear()}-${mm}-${dd}`;
            }

            fromDate = formatDate(fromDate);
            toDate = formatDate(toDate);

            console.log("Using dates:", fromDate, "to", toDate);

            // Determine AJAX URL & table ID
            let ajaxUrl = '';
            let isHtmlResponse = false;
            let tableId = '';
            const OnlyDetected = (ReqType && ReqType.toLowerCase() === 'detected samples') ? 1 : 0;


            if (methodType === 'swabData' && OnlyDetected) {
                if (OnlyDetected === 1) {

                    prepareDetectedPopup();

                    $('#myModalFullScreen').modal('show');

                    loadDetectedTable({
                        ajaxUrl: 'Ajax/Ajihmbrfwhlist.php',
                        tableId: 'workersSwabTable',
                        sectionId: 'workersSwabSection',
                        methodType: methodType,
                        fromDate: fromDate,
                        toDate: toDate,
                    });

                    loadDetectedTable({
                        ajaxUrl: 'Ajax/Ajihmbrfwfslist.php',
                        tableId: 'foodContactSurfaceTable',
                        sectionId: 'foodContactSurfaceSection',
                        methodType: 'foodContactSurface',
                        fromDate: fromDate,
                        toDate: toDate,
                    });

                    loadDetectedTable({
                        ajaxUrl: 'Ajax/Ajihmbrfcawflist.php',
                        tableId: 'flakeIceTable',
                        sectionId: 'flakeIceSection',
                        methodType: 'flakeIce',
                        fromDate: fromDate,
                        toDate: toDate,
                    });

                    return; // prevent old single-table ajax
                }


            }
            switch (methodType) {
                case 'antibioticData':
                    ajaxUrl = 'Ajax/AjaxDashboardGraphs.php';
                    isHtmlResponse = true;
                    tableId = 'key-datatableFullscreen';
                    break;
                case 'microData':
                    ajaxUrl = 'Ajax/AjSwabProdList';
                    tableId = 'microTable';
                    break;
                case 'swabData':
                    ajaxUrl = 'Ajax/Ajihmbrfwhlist';
                    tableId = 'swabTable';
                    break;
                case 'waterIceData':
                    ajaxUrl = 'Ajax/Ajihmbrfwilist';
                    tableId = 'waterIceTable';
                    break;
                case 'foodContactSurfaceData':
                    ajaxUrl = 'Ajax/Ajihmbrfwfslist.php';
                    tableId = 'foodSurfaceTable';
                    break;
                case 'flakeIceData':
                    ajaxUrl = 'Ajax/Ajihmbrfcawflist.php';
                    tableId = 'flakeIceTable';
                    break;
                default:
                    console.error('Unknown chart type:', methodType);
                    return;
            }

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    fromDate: fromDate,
                    toDate: toDate,
                    methodType: methodType,
                    reqType: ReqType,

                    // OnlyDetected: (methodType === 'microData' && ReqType === 'Detected Samples') ? 1 : 0,
                    // onlyDetected: (methodType === 'waterIceData' && ReqType === 'Detected Samples') ? 1 : 0
                    // OnlyDetected: ((methodType === 'microData' && ReqType === 'Detected Samples') ||
                    //     (methodType === 'waterIceData' && ReqType === 'Detected Samples')) ? 1 : 0

                    OnlyDetected: OnlyDetected


                },
                dataType: isHtmlResponse ? 'html' : 'json',
                success: function (response) {
                    let html = '';
                    const switchMethodType = (methodType == 'swabData' && OnlyDetected == 1) ? methodType + "_detected" : methodType;
                    if (isHtmlResponse) {
                        html = response;
                    } else {
                        switch (switchMethodType) {
                            case 'microData':
                                html = buildMicroTableHtml(response.data, tableId);
                                break;
                            case 'swabData':
                                html = buildSwabTableHtml(response.data, tableId);
                                break;
                            case 'waterIceData':

                                // let tpcType = (ReqType === 'TPC Result 2') ? 'TPC2' : 'TPC1';
                                // html = buildWaterIceTableHtml(response.data, tpcType, tableId);
                                // break;

                                let tpcType = 'TPC1';
                                if (ReqType === 'TPC Result 2') tpcType = 'TPC2';
                                if (ReqType === 'Detected Samples') tpcType = 'ALL';

                                html = buildWaterIceTableHtml(response.data, tpcType, tableId);
                                break;
                            case 'foodContactSurfaceData':
                                html = buildFoodSurfaceTableHtml(response.data, tableId);
                                break;
                            case 'flakeIceData':
                                html = buildFlakeIceTableHtml(response.data, tableId);
                                break;
                            case 'swabData_detected':
                                html = buildSwabDetectedSampleTableHtml(response.data, tableId);
                                break;
                            default:
                                html = "<p class='text-center'>No data available</p>";
                        }
                    }

                    $("#PopUpTableFullScreen").html(html);

                    if ($.fn.dataTable.isDataTable('#' + tableId)) {
                        $('#' + tableId).DataTable().destroy();
                    }

                    if ($('#' + tableId).length) {
                        $('#' + tableId).DataTable({
                            dom: 'rtip',
                            responsive: true,
                            scrollX: true,
                            scrollY: $('#PopUpTableFullScreen').height() - 20 + "px",
                            scrollCollapse: true,
                            paging: true,
                            info: true,
                            fixedHeader: true
                        });
                    }

                    //$('#modalTitle').text(`${filename} ${ReqType || ''} ${fromDate} to ${toDate}`);

                    //  Avoid duplicate titles like "Food Contact Surfaces Food Contact Surfaces ..."
                    let titleText = (filename && filename !== ReqType) ?
                        `${filename} ${ReqType || ''} ${fromDate} to ${toDate}` :
                        `${ReqType || filename || ''} ${fromDate} to ${toDate}`;

                    $('#modalTitle').text(titleText.trim());

                    $('#myModalFullScreen').modal('show');
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', error);
                    alert('Error loading data: ' + error);
                }
            });
        }






        // ===== Swab Table Builder =====
        function buildSwabTableHtml(data, tableId) {
            let html = `
        <table id="${tableId}" class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>Sno</th>
                    <th>Sample Collection Date</th>
                    <th>Food Worker Name</th>
                     <th>Sample Analysis Date</th>
                    <th>Collection Area</th>
                    <th>Drawn By</th>
                    <th>TPC 37°C Result</th>
                     <th> Date of Completion</th>
                    <th>V. Cholerae Result</th>
                    <th>Analysed By</th>
                    <th>Checked By</th>
                    <th>Verified By</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
    `;

            if (data && data.length > 0) {
                data.forEach(item => {
                    html += `
                <tr>
                    <td>${item.Sno}</td>
                     <td>${item.sample_date}</td>
                    <td>${item.name_food_worker_hand}</td>
                    <td>${item.analysis_date}</td>
                    <td>${item.sample_collection_area}</td>
                    <td>${item.sample_drawn_by}</td>
                    <td>${item.tpc_result}</td>
                     <td>${item.tpc_completion_date}</td>
                    <td>${item.vcholerae_result}</td>
                    <td>${item.analysed_by}</td>
                    <td>${item.checked_by}</td>
                    <td>${item.verified_by}</td>
                    <td>${item.remarks}</td>
                </tr>
            `;
                });
            } else {
                html += `<tr><td colspan="10" class="text-center">No data found</td></tr>`;
            }
            html += `</tbody></table>`;

            // Ensure table is attached to DOM before initializing DataTable
            setTimeout(() => {
                // Destroy if already exists
                if ($.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().destroy();
                }

                // Initialize DataTable
                $('#' + tableId).DataTable({
                    dom: 'lrtip', // show filter, table, pagination
                    responsive: true,
                    scrollX: true,
                    scrollY: ($('#PopUpTableFullScreen').height() - 20) + "px",
                    scrollCollapse: true,
                    paging: true,
                    pageLength: 10, // default rows per page
                    info: true,
                    fixedHeader: true
                });
            }, 100); // increase timeout to 100ms to ensure DOM is ready

            return html;
        }

        function buildSwabDetectedSampleTableHtml(data, tableId) {
            let html = `
        <table id="${tableId}" class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>Sno</th>
                    <th>Sample Collection Date</th>
                    <th>Food Worker Name</th>
                     <th>Sample Analysis Date</th>
                    <th>Collection Area</th>
                    <th>Drawn By</th>
                    <th>TPC 37°C Result</th>
                     <th> Date of Completion</th>
                    <th>V. Cholerae Result</th>
                    <th>Analysed By</th>
                    <th>Checked By</th>
                    <th>Verified By</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
    `;

            if (data && data.length > 0) {
                data.forEach(item => {
                    html += `
                <tr>
                    <td>${item.Sno}</td>
                     <td>${item.sample_date}</td>
                    <td>${item.name_food_worker_hand}</td>
                    <td>${item.analysis_date}</td>
                    <td>${item.sample_collection_area}</td>
                    <td>${item.sample_drawn_by}</td>
                    <td>${item.tpc_result}</td>
                     <td>${item.tpc_completion_date}</td>
                    <td>${item.vcholerae_result}</td>
                    <td>${item.analysed_by}</td>
                    <td>${item.checked_by}</td>
                    <td>${item.verified_by}</td>
                    <td>${item.remarks}</td>
                </tr>
            `;
                });
            } else {
                html += `<tr><td colspan="10" class="text-center">No data found</td></tr>`;
            }
            html += `</tbody></table>`;

            // Ensure table is attached to DOM before initializing DataTable
            setTimeout(() => {
                // Destroy if already exists
                if ($.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().destroy();
                }

                // Initialize DataTable
                $('#' + tableId).DataTable({
                    dom: 'lrtip', // show filter, table, pagination
                    responsive: true,
                    scrollX: true,
                    scrollY: ($('#PopUpTableFullScreen').height() - 20) + "px",
                    scrollCollapse: true,
                    paging: true,
                    pageLength: 10, // default rows per page
                    info: true,
                    fixedHeader: true
                });
            }, 100); // increase timeout to 100ms to ensure DOM is ready

            return html;
        }


        // ===== Micro Table Builder (your existing Micro structure) =====
        function buildMicroTableHtml(data, tableId) {
            let html = `
        <table id="${tableId}" class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>Sno</th>
                    <th>Batch Number</th>
                    <th>Plant</th>
                    <th>Sample Date</th>
                    <th>Drawn By</th>
                    <th>Analysis Date</th>
                    <th>Report Date</th>
                    <th>Variety</th>
                    <th>Name Media Used</th>
                    <th>Equipment Used</th>
                    <th>Tpc Result</th>
                    <th>Tpc Completed Date</th>
                    <th>EC Name Media Used</th>
                    <th>EC Equipment Used</th>
                    <th>Ecoli Result</th>
                    <th>EC Completed Date</th>
                    <th>SA Name Media Used</th>
                    <th>SA Equipment Used</th>
                    <th>Saureus Result</th>
                    <th>SA Completed Date</th>
                    <th>VC Name Media Used</th>
                    <th>VC Equipment Used</th>
                    <th>Vcholerae Result</th>
                    <th>VC Completed Date</th>
                    <th>VP Name Media Used</th>
                    <th>VP Equipment Used</th>
                    <th>Vparahaemolyticus Result</th>
                    <th>VP Completed Date</th>
                    <th>Salmonella Name Media Used</th>
                    <th>Salmonella Equipment Used</th>
                    <th>Salmonella</th>
                    <th>Salmonella Completed Date</th>
                    <th>Analysed By</th>
                    <th>Checked By</th>
                    <th>Verified By</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    `;

            setTimeout(() => {
                if ($.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().destroy();
                }

                $('#' + tableId).DataTable({
                    dom: 'lrtip',
                    responsive: true,
                    scrollX: true,
                    scrollY: ($('#PopUpTableFullScreen').height() - 20) + "px",
                    scrollCollapse: true,
                    paging: true,
                    pageLength: 10,
                    info: true,
                    fixedHeader: true,
                    data: data, // <-- pass the data array directly
                    columns: [{
                        data: 'Sno'
                    },
                    {
                        data: 'BatchNumber'
                    },
                    {
                        data: 'Plant'
                    },
                    {
                        data: 'SampleDate'
                    },
                    {
                        data: 'DrawnBy'
                    },
                    {
                        data: 'AnalysisDate'
                    },
                    {
                        data: 'ReportDate'
                    },
                    {
                        data: 'Variety'
                    },
                    {
                        data: 'NameMediaUsed'
                    },
                    {
                        data: 'EquipmentUsed'
                    },
                    {
                        data: 'TpcResult'
                    },
                    {
                        data: 'tp_completed_date'
                    },
                    {
                        data: 'ec_nameMediaUsed'
                    },
                    {
                        data: 'ec_equipmentUsed'
                    },
                    {
                        data: 'EcoliResult'
                    },
                    {
                        data: 'ec_completed_date'
                    },
                    {
                        data: 'sa_nameMediaUsed'
                    },
                    {
                        data: 'sa_equipmentUsed'
                    },
                    {
                        data: 'SaureusResult'
                    },
                    {
                        data: 'sa_completed_date'
                    },
                    {
                        data: 'vc_nameMediaUsed'
                    },
                    {
                        data: 'vc_equipmentUsed'
                    },
                    {
                        data: 'VcholeraeResult'
                    },
                    {
                        data: 'vc_completed_date'
                    },
                    {
                        data: 'vp_nameMediaUsed'
                    },
                    {
                        data: 'vp_equipmentUsed'
                    },
                    {
                        data: 'VparahaemolyticusResult'
                    },
                    {
                        data: 'vp_completed_date'
                    },
                    {
                        data: 'sal_nameMediaUsed'
                    },
                    {
                        data: 'sal_equipmentUsed'
                    },
                    {
                        data: 'Salmonella'
                    },
                    {
                        data: 'sal_completed_date'
                    },
                    {
                        data: 'analysed_by'
                    },
                    {
                        data: 'CheckedBy'
                    },
                    {
                        data: 'VerfiedBy'
                    },
                    {
                        data: 'Remarks'
                    }
                    ]
                });
            }, 100);

            return html;
        }


        function loadDetectedTable({ ajaxUrl, tableId, sectionId, methodType, fromDate, toDate }) {
            return $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    fromDate,
                    toDate,
                    methodType,
                    OnlyDetected: 1
                },
                success: function (res) {

                    const $section = $('#' + sectionId);
                    $section.find('.loader').hide();

                    if (res && res.recordsFiltered > 0) {

                        if (methodType === 'foodContactSurface') {
                            const foodContactSurfaceColumns = [
                                {
                                    data: null,
                                    title: 'S.No',
                                    render: function (data, type, row, meta) {
                                        return meta.row + 1;
                                    }
                                },
                                { data: 'sample_date', title: 'Sample Collection Date' },
                                { data: 'name_food_contact_surface', title: 'Name of the Food Contact Surface' },
                                { data: 'analysis_date', title: 'Sample Analysis Date' },
                                { data: 'sample_collection_area', title: 'Sample Collection Area' },
                                { data: 'sample_drawn_by', title: 'Sample Drawn By' },
                                {
                                    data: 'tpc_result', title: 'TPC 37°C Result', createdCell: function (td, cellData, rowData, row, col) {
                                        if (cellData && cellData !== '') {
                                            $(td).css({
                                                'color': 'red',
                                            });
                                        }
                                    }
                                },
                                { data: 'tpc_completion_date', title: 'Date of Completion' },
                                { data: 'analysed_by', title: 'Analysed By' },
                                { data: 'checked_by', title: 'Checked By' },
                                { data: 'verified_by', title: 'Verified By' },
                                { data: 'remarks', title: 'Remarks' }
                            ];
                            $('#foodContactSurfaceTable').removeClass('d-none').DataTable({
                                data: res.data,
                                columns: foodContactSurfaceColumns, // your column config
                                destroy: true,
                                autoWidth: false
                            });
                        } else if (methodType === 'flakeIce') {
                            const waterIceColumns = [
                                {
                                    data: null,
                                    title: 'S.No',
                                    render: function (data, type, row, meta) {
                                        return meta.row + 1;
                                    }
                                },
                                { data: 'sample_collection_date', title: 'Sample Collection Date' },
                                { data: 'plant_name', title: 'Plant Name' },
                                { data: 'sample_analysis_date', title: 'Sample Analysis Date' },
                                { data: 'sample_drawn_by', title: 'Sample Drawn By' },
                                { data: 'instant_swab', title: 'Instant Swab' },
                                { data: 'tpc_result', title: 'TPC 37°C Result', },
                                { data: 'tpc_completion_date', title: 'Date of Completion' },
                                { data: 'analysed_by', title: 'Analysed By' },
                                { data: 'checked_by', title: 'Checked By' },
                                { data: 'verified_by', title: 'Verified By' },
                                { data: 'remarks', title: 'Remarks' }
                            ];
                            $('#flakeIceTable').removeClass('d-none').DataTable({
                                data: res.data,
                                columns: waterIceColumns, // your column config
                                destroy: true,
                                autoWidth: false
                            });
                        } else {

                            const workersSwabColumns = [
                                { data: 'Sno', title: 'Sno' },
                                { data: 'sample_date', title: 'Sample Collection Date' },
                                { data: 'name_food_worker_hand', title: 'Food Worker Name' },
                                { data: 'analysis_date', title: 'Sample Analysis Date' },
                                { data: 'sample_collection_area', title: 'Collection Area' },
                                { data: 'sample_drawn_by', title: 'Drawn By' },
                                { data: 'tpc_result', title: 'TPC 37°C Result' },
                                { data: 'tpc_completion_date', title: 'Date of Completion' },
                                { data: 'vcholerae_result', title: 'V. Cholerae Result' },
                                { data: 'analysed_by', title: 'Analysed By' },
                                { data: 'checked_by', title: 'Checked By' },
                                { data: 'verified_by', title: 'Verified By' },
                                { data: 'remarks', title: 'Remarks' }
                            ];


                            $('#workersSwabTable').removeClass('d-none').DataTable({
                                data: res.data,
                                columns: workersSwabColumns, // your column config
                                destroy: true,
                                autoWidth: false
                            });

                        }

                        $('#' + tableId).removeClass('d-none');
                    } else {
                        $section.append('<div class="text-muted">No detected samples</div>');
                    }
                },
                error: function () {
                    $('#' + sectionId + ' .loader').text('Error loading data');
                }
            });
        }

        function prepareSwabTableHtml(data, tableId) {
            let html = `
        <table  class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>Sno</th>
                    <th>Sample Collection Date</th>
                    <th>Food Worker Name</th>
                     <th>Sample Analysis Date</th>
                    <th>Collection Area</th>
                    <th>Drawn By</th>
                    <th>TPC 37°C Result</th>
                     <th> Date of Completion</th>
                    <th>V. Cholerae Result</th>
                    <th>Analysed By</th>
                    <th>Checked By</th>
                    <th>Verified By</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
    `;

            if (data && data.length > 0) {
                data.forEach(item => {
                    html += `
                <tr>
                    <td>${item.Sno}</td>
                     <td>${item.sample_date}</td>
                    <td>${item.name_food_worker_hand}</td>
                    <td>${item.analysis_date}</td>
                    <td>${item.sample_collection_area}</td>
                    <td>${item.sample_drawn_by}</td>
                    <td>${item.tpc_result}</td>
                     <td>${item.tpc_completion_date}</td>
                    <td>${item.vcholerae_result}</td>
                    <td>${item.analysed_by}</td>
                    <td>${item.checked_by}</td>
                    <td>${item.verified_by}</td>
                    <td>${item.remarks}</td>
                </tr>
            `;
                });
            } else {
                html += `<tr><td colspan="10" class="text-center">No data found</td></tr>`;
            }
            html += `</tbody></table>`;

            return html;
        }

        function renderTable(tableId, data) {

            if ($.fn.DataTable.isDataTable('#' + tableId)) {
                $('#' + tableId).DataTable().clear().rows.add(data).draw();
            } else {
                $('#' + tableId).DataTable({
                    data: data,
                    paging: true,
                    searching: true,
                    destroy: true,
                    autoWidth: false
                });
            }
        }


        function prepareDetectedPopup() {

            $('#modalTitle').text('Detected Samples');

            const html = `
        <div id="workersSwabSection" class="mb-4">
            <h5>Workers Swab</h5>
            <div class="loader text-muted">Loading...</div>
            <table id="workersSwabTable" class="table table-hover table-bordered table-sm d-none"></table>
        </div>

        <hr>

        <div id="foodContactSurfaceSection" class="mb-4">
            <h5>Food Contact Surface</h5>
            <div class="loader text-muted">Loading...</div>
            <table id="foodContactSurfaceTable" class="table table-bordered table-sm d-none"></table>
        </div>

        <hr>

        <div id="flakeIceSection" class="mb-4">
            <h5>FLAKE ICE & PLATE ICE CHUTE</h5>
            <div class="loader text-muted">Loading...</div>
            <table id="flakeIceTable" class="table table-bordered table-sm d-none"></table>
        </div>
    `;

            $('#PopUpTableFullScreen').html(html);
        }





        // ===== Water/Ice Table Builder =====
        //     function buildWaterIceTableHtml(data, tableId) {
        //         let html = `
        // <table id="${tableId}" class="table table-hover table-bordered">
        //     <thead class="thead-light">
        //         <tr>
        //             <th>S.No</th>
        //             <th>Sample Collection Date</th>
        //             <th>Sample Description</th>
        //             <th>Tap No / Ice Type</th>
        //             <th>Drawn By</th>
        //             <th>TPC Result</th>
        //             <th>V. Cholerae Result</th>
        //             <th>Cholerae Result</th>
        //             <th>Analysed By</th>
        //             <th>Checked By</th>
        //             <th>Verified By</th>
        //         </tr>
        //     </thead>
        //     <tbody>
        // `;

        //         if (data && data.length > 0) {
        //             data.forEach(item => {
        //                 html += `
        //         <tr>
        //             <td>${item.Sno || '-'}</td>
        //             <td>${item.sample_collection || '-'}</td>
        //             <td>${item.sample_desc || '-'}</td>
        //             <td>${item.tap_no_for_water || item.itype_of_ice || '-'}</td>
        //             <td>${item.sample_drawn_by || '-'}</td>
        //             <td>${item.tpc_result1 || item.tpc_result2 || '-'}</td>
        //             <td>${item.vcholerae_result1 || item.vcholerae_result2 || '-'}</td>
        //             <td>${item.cholerae_result1 || '-'}</td>
        //             <td>${item.analysed_by || '-'}</td>
        //             <td>${item.checked_by || '-'}</td>
        //             <td>${item.verified_by || '-'}</td>
        //         </tr>
        //     `;
        //             });
        //         } else {
        //             html += `<tr><td colspan="11" class="text-center">No data found</td></tr>`;
        //         }
        //         html += `</tbody></table>`;

        //         // Initialize DataTable with pagination using dynamic tableId
        //         setTimeout(() => {
        //             if ($.fn.dataTable.isDataTable('#' + tableId)) {
        //                 $('#' + tableId).DataTable().destroy();
        //             }

        //             $('#' + tableId).DataTable({
        //                 dom: 'rtip',
        //                 responsive: true,
        //                 scrollX: true,
        //                 scrollY: $('#PopUpTableFullScreen').height() - 20 + "px",
        //                 scrollCollapse: true,
        //                 paging: true,
        //                 info: true,
        //                 fixedHeader: true
        //             });
        //         }, 50);

        //         return html;
        //     }


    });




    // function buildFoodSurfaceTableHtml(data, tableId) {
    //     if (!data || data.length === 0)
    //         return "<p class='text-center'>No data available</p>";

    //     let html = `<table id="${tableId}" class="table table-hover table-bordered">
    // <thead>
    //     <tr>
    //         <th>S.No</th>
    //         <th>Name of the food worker hand</th>
    //         <th>Sample Collection Area</th>
    //         <th>Sample Drawn By</th>
    //         <th>TPC 35°C  Result</th>
    //         <th>Analysed By</th>
    //         <th>Checked By</th>
    //         <th>Verified By</th>
    //         <th>Remarks</th>
    //     </tr>
    // </thead>
    // <tbody>`;

    //     data.forEach((row, i) => {
    //         html += `<tr>
    //     <td>${i + 1}</td>
    //     <td>${row.name_food_contact_surface || ''}</td>
    //     <td>${row.sample_collection_area || ''}</td>
    //     <td>${row.sample_drawn_by || ''}</td>
    //     <td>${row.tpc_result || ''}</td>
    //     <td>${row.analysed_by || ''}</td>
    //     <td>${row.checked_by || ''}</td>
    //     <td>${row.verified_by || ''}</td>
    //     <td>${row.remarks || ''}</td>
    // </tr>`;
    //     });

    //     html += `</tbody></table>`;

    //     // Initialize DataTable
    //     setTimeout(() => {
    //         if ($.fn.DataTable.isDataTable('#' + tableId)) {
    //             $('#' + tableId).DataTable().destroy();
    //         }

    //         $('#' + tableId).DataTable({
    //             dom: 'lrtip',
    //             responsive: true,
    //             scrollX: true,
    //             scrollY: ($('#PopUpTableFullScreen').height() - 20) + "px",
    //             scrollCollapse: true,
    //             paging: true,
    //             pageLength: 10,
    //             info: true,
    //             fixedHeader: true
    //         });
    //     }, 100);

    //     return html;
    // }





    // function buildFoodSurfaceTableHtml(data, tableId) {
    //     if (!data || data.length === 0)
    //         return "<p class='text-center'>No data available</p>";

    //     let html = `<table id="${tableId}" class="table table-hover table-bordered">
    //     <thead>
    //         <tr>
    //             <th>S.No</th>
    //             <th>Name of the Food Contact Surface</th>
    //             <th>Sample Collection Area</th>
    //             <th>Sample Drawn By</th>
    //             <th>TPC 37°C Result</th>
    //             <th>Analysed By</th>
    //             <th>Checked By</th>
    //             <th>Verified By</th>
    //             <th>Remarks</th>
    //         </tr>
    //     </thead>
    //     <tbody>`;

    //     data.forEach((row, i) => {
    //         let tpcVal = row.tpc_result || '';
    //         let displayVal = tpcVal;

    //         //  Convert value to number (if numeric)
    //         let num = parseFloat(tpcVal);

    //         //  Highlight if value is numeric and above limit
    //         // Change threshold here if needed (35 is typical for Food Contact Surface)
    //         if (!isNaN(num) && num > 35) {
    //             displayVal = `<span style="color:red;font-weight:bold;">${tpcVal}</span>`;
    //         }

    //         //  Optional: If it's text like ">50" or "<2MPN" — treat special cases
    //         else if (typeof tpcVal === 'string' && tpcVal.trim().startsWith('>')) {
    //             displayVal = `<span style="color:red;font-weight:bold;">${tpcVal}</span>`;
    //         }

    //         html += `<tr>
    //         <td>${i + 1}</td>
    //         <td>${row.name_food_contact_surface || ''}</td>
    //         <td>${row.sample_collection_area || ''}</td>
    //         <td>${row.sample_drawn_by || ''}</td>
    //         <td>${displayVal}</td>
    //         <td>${row.analysed_by || ''}</td>
    //         <td>${row.checked_by || ''}</td>
    //         <td>${row.verified_by || ''}</td>
    //         <td>${row.remarks || ''}</td>
    //     </tr>`;
    //     });

    //     html += `</tbody></table>`;

    //     // Initialize DataTable
    //     setTimeout(() => {
    //         if ($.fn.DataTable.isDataTable('#' + tableId)) {
    //             $('#' + tableId).DataTable().destroy();
    //         }

    //         $('#' + tableId).DataTable({
    //             dom: 'lrtip',
    //             responsive: true,
    //             scrollX: true,
    //             scrollY: ($('#PopUpTableFullScreen').height() - 20) + "px",
    //             scrollCollapse: true,
    //             paging: true,
    //             pageLength: 10,
    //             info: true,
    //             fixedHeader: true
    //         });
    //     }, 100);

    //     return html;
    // }



    function buildFoodSurfaceTableHtml(data, tableId) {
        if (!data || data.length === 0)
            return "<p class='text-center'>No data available</p>";

        let html = `<table id="${tableId}" class="table table-hover table-bordered">
    <thead>
        <tr>
            <th>S.No</th>
             <th>Sample Collection Date</th>
            <th>Name of the Food Contact Surface</th>
             <th>Sample Analysis Date</th>
            <th>Sample Collection Area</th>
            <th>Sample Drawn By</th>
            <th>TPC 37°C Result</th>
             <th> Date of Completion</th>
            <th>Analysed By</th>
            <th>Checked By</th>
            <th>Verified By</th>
            <th>Remarks</th>
        </tr>
    </thead>
    <tbody>`;

        data.forEach((row, i) => {
            html += `<tr>
        <td>${i + 1}</td>
         <td>${row.sample_date || ''}</td>
        <td>${row.name_food_contact_surface || ''}</td>
        <td>${row.analysis_date || ''}</td>
        <td>${row.sample_collection_area || ''}</td>
        <td>${row.sample_drawn_by || ''}</td>
        <td>${row.tpc_result || ''}</td>
        <td>${row.tpc_completion_date || ''}</td>
        <td>${row.analysed_by || ''}</td>
        <td>${row.checked_by || ''}</td>
        <td>${row.verified_by || ''}</td>
        <td>${row.remarks || ''}</td>
    </tr>`;
        });

        html += `</tbody></table>`;

        // Initialize DataTable
        setTimeout(() => {
            if ($.fn.DataTable.isDataTable('#' + tableId)) {
                $('#' + tableId).DataTable().destroy();
            }

            $('#' + tableId).DataTable({
                dom: 'lrtip',
                responsive: true,
                scrollX: true,
                scrollY: ($('#PopUpTableFullScreen').height() - 20) + "px",
                scrollCollapse: true,
                paging: true,
                pageLength: 10,
                info: true,
                fixedHeader: true
            });
        }, 100);

        return html;
    }



    function buildFlakeIceTableHtml(data, tableId) {
        if (!data || data.length === 0)
            return "<p class='text-center'>No data available</p>";

        let html = `
        <table id="${tableId}" class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>S.No</th>
                     <th>Sample Collection Date</th>
                    <th>Plant Name</th>
                    <th>Sample Analysis Date</th>
                    <th>Sample Drawn By</th>
                    <th>Instant Swab</th>
                    
                    <th>TPC 37°C Result</th>
                     <th> Date of Completion</th>
                    <th>Analysed By</th>
                    <th>Checked By</th>
                    <th>Verified By</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
    `;

        data.forEach((row, index) => {
            html += `
            <tr>
                <td>${index + 1}</td>
                 <td>${row.sample_collection_date || ''}</td>
                <td>${row.plant_name || ''}</td>
                <td>${row.sample_analysis_date || ''}</td>
                <td>${row.sample_drawn_by || ''}</td>
                <td>${row.instant_swab || ''}</td>
                <td>${row.tpc_result || ''}</td>
                <td>${row.tpc_completion_date || ''}</td>
             
                <td>${row.analysed_by || ''}</td>
                <td>${row.checked_by || ''}</td>
                <td>${row.verified_by || ''}</td>
                <td>${row.remarks || ''}</td>
            </tr>
        `;
        });

        html += `</tbody></table>`;

        // Initialize DataTable with pagination
        setTimeout(() => {
            if ($.fn.DataTable.isDataTable('#' + tableId)) {
                $('#' + tableId).DataTable().destroy();
            }

            $('#' + tableId).DataTable({
                dom: 'lrtip',
                responsive: true,
                scrollX: true,
                scrollY: ($('#PopUpTableFullScreen').height() - 20) + "px",
                scrollCollapse: true,
                paging: true,
                pageLength: 10,
                info: true,
                fixedHeader: true
            });
        }, 100);

        return html;
    }



    function buildWaterIceTPC2TableHtml(data) {
        let html = `
        <table id="tblWaterIceTPC2" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>S.No</th>
                    <th>Sample Collection Date</th>
                    <th>Sample Description</th>
                    <th>Tap No / Ice Type</th>
                    <th>Drawn By</th>
                    <th>TPC 37°C</th>
                    <th>Analysed By</th>
                    <th>Checked By</th>
                    <th>Verified By</th>
                </tr>
            </thead>
            <tbody>
    `;

        if (data && data.length > 0) {
            data.forEach(item => {
                html += `
                <tr>
                    <td>${item.Sno}</td>
                    <td>${item.sample_collection}</td>
                    <td>${item.sample_desc}</td>
                    <td>${item.tap_no_for_water || item.itype_of_ice}</td>
                    <td>${item.sample_drawn_by}</td>
                    <td>${item.tpc_result2 || '-'}</td>
                    <td>${item.analysed_by}</td>
                    <td>${item.checked_by}</td>
                    <td>${item.verified_by}</td>
                </tr>
            `;
            });
        } else {
            html += `<tr><td colspan="9" class="text-center">No data found</td></tr>`;
        }

        html += `</tbody></table>`;

        setTimeout(() => {
            if ($.fn.dataTable.isDataTable('#tblWaterIceTPC2')) {
                $('#tblWaterIceTPC2').DataTable().destroy();
            }
            $('#tblWaterIceTPC2').DataTable({
                dom: 'rtip',
                responsive: true,
                scrollX: true,
                scrollY: $('#PopUpTableFullScreen').height() - 20 + "px",
                scrollCollapse: true,
                paging: true,
                info: true,
                fixedHeader: true
            });
        }, 50);

        return html;
    }


    // function buildWaterIceTableHtml(data, tpcType = 'TPC22', tableId = 'tblWaterIce') {
    //      let tpcHeader = tpcType === 'TPC22' ? 'TPC 22°C' : 'TPC 37°C';
    //     let html = `
    //     <table id="${tableId}" class="table table-hover table-bordered">
    //         <thead class="thead-light">
    //             <tr>
    //                 <th>S.No</th>
    //                 <th>Sample Collection Date</th>
    //                 <th>Sample Description</th>
    //                 <th>Tap No / Ice Type</th>
    //                 <th>Drawn By</th>
    //                 <th>${tpcType === 'TPC1' ? 'TPC 22°C' : 'TPC 37°C'}</th>
    //                 <th>V. Cholerae Result</th>
    //                 <th>Cholerae Result</th>
    //                 <th>Analysed By</th>
    //                 <th>Checked By</th>
    //                 <th>Verified By</th>
    //             </tr>
    //         </thead>
    //         <tbody>
    //     `;

    //     if (data && data.length > 0) {
    //         data.forEach((item, i) => {
    //             let tpcValue = (tpcType === 'TPC1') ? item.tpc_result1 : item.tpc_result2;
    //     //         let tpcValue = '-';
    //     // if (item.tpc_paramater === '22°C') tpcValue = item.tpc_result1 || '-';
    //     // else if (item.tpc_paramater === '37°C') tpcValue = item.tpc_result2 || '-';
    //             html += `
    //             <tr>
    //                 <td>${i + 1}</td>
    //                 <td>${item.sample_collection}</td>
    //                 <td>${item.sample_desc}</td>
    //                 <td>${item.tap_no_for_water || item.itype_of_ice}</td>
    //                 <td>${item.sample_drawn_by}</td>
    //                 <td>${tpcValue || '-'}</td>
    //                 <td>${item.vcholerae_result1 || item.vcholerae_result2 || '-'}</td>
    //                 <td>${item.cholerae_result1 || '-'}</td>
    //                 <td>${item.analysed_by}</td>
    //                 <td>${item.checked_by}</td>
    //                 <td>${item.verified_by}</td>
    //             </tr>
    //             `;
    //         });
    //     } else {
    //         html += `<tr><td colspan="11" class="text-center">No data found</td></tr>`;
    //     }

    //     html += `</tbody></table>`;

    //     setTimeout(() => {
    //         if ($.fn.dataTable.isDataTable('#' + tableId)) {
    //             $('#' + tableId).DataTable().destroy();
    //         }
    //         $('#' + tableId).DataTable({
    //             dom: 'rtip',
    //             responsive: true,
    //             scrollX: true,
    //             scrollY: $('#PopUpTableFullScreen').height() - 20 + "px",
    //             scrollCollapse: true,
    //             paging: true,
    //             info: true,
    //             fixedHeader: true
    //         });
    //     }, 50);

    //     return html;
    // }


    // function buildWaterIceTableHtml(data, tpcType = 'TPC1', tableId = 'tblWaterIce') {
    //     // Set headings: first graph = TPC 37°C, second graph = TPC 22°C (just headings, not values)
    //     let tpcHeader = '';
    //     if (tableId === 'tblWaterIce' || tpcType === 'TPC1') {
    //         tpcHeader = 'TPC 37°C';
    //     } else {
    //         tpcHeader = 'TPC 22°C';
    //     }

    //     let html = `
    // <table id="${tableId}" class="table table-hover table-bordered">
    //     <thead class="thead-light">
    //         <tr>
    //             <th>S.No</th>
    //             <th>Sample Collection Date</th>
    //             <th>Sample Description</th>
    //             <th>Tap No / Ice Type</th>
    //             <th>Drawn By</th>
    //             <th>${tpcHeader}</th>
    //             <th>V. Cholerae Result</th>
    //             <th>Coliform Result</th>
    //             <th>Analysed By</th>
    //             <th>Checked By</th>
    //             <th>Verified By</th>
    //         </tr>
    //     </thead>
    //     <tbody>
    // `;

    //     if (data && data.length > 0) {
    //         data.forEach((item, i) => {
    //             let tpcValue = tpcType === 'TPC1' ? item.tpc_result1 : item.tpc_result2;

    //             html += `
    //         <tr>
    //             <td>${i + 1}</td>
    //             <td>${item.sample_collection}</td>
    //             <td>${item.sample_desc}</td>
    //             <td>${item.tap_no_for_water || item.itype_of_ice}</td>
    //             <td>${item.sample_drawn_by}</td>
    //             <td>${tpcValue || '-'}</td>
    //             <td>${item.vcholerae_result1 || item.vcholerae_result2 || '-'}</td>
    //             <td>${item.cholerae_result1 || '-'}</td>
    //             <td>${item.analysed_by}</td>
    //             <td>${item.checked_by}</td>
    //             <td>${item.verified_by}</td>
    //         </tr>
    //         `;
    //         });
    //     } else {
    //         html += `<tr><td colspan="11" class="text-center">No data found</td></tr>`;
    //     }

    //     html += `</tbody></table>`;

    //     setTimeout(() => {
    //         if ($.fn.dataTable.isDataTable('#' + tableId)) {
    //             $('#' + tableId).DataTable().destroy();
    //         }
    //         $('#' + tableId).DataTable({
    //             dom: 'rtip',
    //             responsive: true,
    //             scrollX: true,
    //             scrollY: $('#PopUpTableFullScreen').height() - 20 + "px",
    //             scrollCollapse: true,
    //             paging: true,
    //             info: true,
    //             fixedHeader: true
    //         });
    //     }, 50);

    //     return html;
    // }


    // function buildWaterIceTableHtml(data, tableId = 'tblWaterIce') {
    //     let html = `
    // <table id="${tableId}" class="table table-hover table-bordered">
    //     <thead class="thead-light">
    //         <tr>
    //             <th>S.No</th>
    //             <th>Sample Collection Date</th>
    //             <th>Sample Description</th>
    //             <th>Tap No / Ice Type</th>
    //             <th>Drawn By</th>
    //             <th>TPC 37°C</th>
    //             <th>TPC 22°C</th>
    //             <th>V. Cholerae Result</th>
    //             <th>Coliform Result</th>
    //             <th>Analysed By</th>
    //             <th>Checked By</th>
    //             <th>Verified By</th>
    //         </tr>
    //     </thead>
    //     <tbody>
    // `;

    //     if (data && data.length > 0) {
    //         data.forEach((item, i) => {
    //             html += `
    //         <tr>
    //             <td>${i + 1}</td>
    //             <td>${item.sample_collection}</td>
    //             <td>${item.sample_desc}</td>
    //             <td>${item.tap_no_for_water || item.itype_of_ice}</td>
    //             <td>${item.sample_drawn_by}</td>
    //             <td>${item.tpc_result1 || '-'}</td>
    //             <td>${item.tpc_result2 || '-'}</td>
    //             <td>${item.vcholerae_result1 || item.vcholerae_result2 || '-'}</td>
    //             <td>${item.cholerae_result1 || '-'}</td>
    //             <td>${item.analysed_by}</td>
    //             <td>${item.checked_by}</td>
    //             <td>${item.verified_by}</td>
    //         </tr>
    //         `;
    //         });
    //     } else {
    //         html += `<tr><td colspan="12" class="text-center">No data found</td></tr>`;
    //     }

    //     html += `</tbody></table>`;

    //     setTimeout(() => {
    //         if ($.fn.dataTable.isDataTable('#' + tableId)) {
    //             $('#' + tableId).DataTable().destroy();
    //         }
    //         $('#' + tableId).DataTable({
    //             dom: 'rtip',
    //             responsive: true,
    //             scrollX: true,
    //             scrollY: $('#PopUpTableFullScreen').height() - 20 + "px",
    //             scrollCollapse: true,
    //             paging: true,
    //             info: true,
    //             fixedHeader: true
    //         });
    //     }, 50);

    //     return html;
    // }




    // function buildWaterIceTableHtml(data, tableId = 'tblWaterIce') {
    //     let html = `
    // <table id="${tableId}" class="table table-hover table-bordered">
    //     <thead class="thead-light">
    //         <tr>
    //             <th>S.No</th>
    //             <th>Sample Collection Date</th>
    //             <th>Sample Description</th>
    //             <th>Tap No / Ice Type</th>
    //             <th>Drawn By</th>
    //             <th>TPC 37°C</th>
    //             <th>TPC 22°C</th>
    //             <th>V. Cholerae Result</th>
    //             <th>Coliform Result</th>
    //             <th>Analysed By</th>
    //             <th>Checked By</th>
    //             <th>Verified By</th>

    //         </tr>
    //     </thead>
    //     <tbody>
    // `;

    //     if (data && data.length > 0) {
    //         data.forEach(item => {
    //             html += `<tr>
    //             <td>${item.Sno}</td>
    //             <td>${item.sample_collection}</td>
    //             <td>${item.sample_desc}</td>
    //             <td>${item.tap_no_for_water}</td>
    //             <td>${item.sample_drawn_by}</td>
    //             <td>${item.tpc_result1}</td>
    //             <td>${item.tpc_result2}</td>
    //             <td>${item.vcholerae_result1}</td>
    //             <td>${item.cholerae_result1}</td>
    //             <td>${item.analysed_by}</td>
    //             <td>${item.checked_by}</td>
    //             <td>${item.verified_by}</td>

    //         </tr>`;
    //         });
    //     } else {
    //         html += `<tr><td colspan="13" class="text-center">No data found</td></tr>`;
    //     }

    //     html += `</tbody></table>`;

    //     setTimeout(() => {
    //         if ($.fn.dataTable.isDataTable('#' + tableId)) {
    //             $('#' + tableId).DataTable().destroy();
    //         }

    //         $('#' + tableId).DataTable({
    //             dom: "<'row'<'col-md-12 text-center'<'#customPagination.paging_simple_numbers'>>>" + // center pagination
    //                 "rt" +
    //                 "<'row'<'col-md-12 text-center'p>>",
    //             responsive: true,
    //             scrollX: true,
    //             scrollY: $('#PopUpTableFullScreen').height() - 20 + "px",
    //             scrollCollapse: true,
    //             paging: true,
    //             info: true,
    //             fixedHeader: true,
    //             columns: [{
    //                     data: 'Sno'
    //                 }, {
    //                     data: 'sample_collection'
    //                 }, {
    //                     data: 'sample_desc'
    //                 }, {
    //                     data: 'tap_no_for_water'
    //                 },
    //                 {
    //                     data: 'sample_drawn_by'
    //                 }, {
    //                     data: 'tpc_result1'
    //                 }, {
    //                     data: 'tpc_result2'
    //                 },
    //                 {
    //                     data: 'vcholerae_result1'
    //                 }, {
    //                     data: 'cholerae_result1'
    //                 }, {
    //                     data: 'analysed_by'
    //                 },
    //                 {
    //                     data: 'checked_by'
    //                 }, {
    //                     data: 'verified_by'
    //                 }
    //             ]
    //         });

    //         // Move pagination controls to center
    //         let pagination = $('#' + tableId + '_paginate');
    //         $('#customPagination').html(pagination);
    //     }, 50);

    //     return html;
    // }



    function buildWaterIceTableHtml(data, tableId = 'tblWaterIce') {
        // Destroy existing table if exists
        if ($.fn.dataTable.isDataTable('#' + tableId)) {
            $('#' + tableId).DataTable().destroy();
            $('#' + tableId).empty(); // clear old table
        }

        // Build table structure (thead only)
        let html = `
        <table id="${tableId}" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>S.No</th>
                    <th>Sample Collection Date</th>
                    <th>Sample Description</th>
                    <th>Tap No / Ice Type</th>
                    <th>Drawn By</th>
                    <th>TPC 37°C</th>
                    <th>TPC 22°C</th>
                    <th>V. Cholerae Result</th>
                    <th>Coliform Result</th>
                    <th>Analysed By</th>
                    <th>Checked By</th>
                    <th>Verified By</th>
                </tr>
            </thead>
        </table>
    `;

        // Append table HTML to container
        $('#PopUpTableFullScreen').html(html);

        // Initialize DataTable with data directly
        $('#' + tableId).DataTable({
            data: data,
            columns: [{
                data: 'Sno'
            },
            {
                data: 'sample_collection'
            },
            {
                data: 'sample_desc'
            },
            {
                data: 'tap_no_for_water'
            },
            {
                data: 'sample_drawn_by'
            },
            {
                data: 'tpc_result1'
            },
            {
                data: 'tpc_result2'
            },
            {
                data: 'vcholerae_result1'
            },
            {
                data: 'cholerae_result1'
            },
            {
                data: 'analysed_by'
            },
            {
                data: 'checked_by'
            },
            {
                data: 'verified_by'
            }
            ],
            scrollX: true,
            scrollY: $('#PopUpTableFullScreen').height() - 20 + "px",
            scrollCollapse: true,
            paging: true,
            info: true,
            fixedHeader: true,
            responsive: true,
            // dom: "<'row'<'col-md-12 text-center'<'#customPagination.paging_simple_numbers'>>>" + "rt" + "<'row'<'col-md-12 text-center'p>>"

            dom: "rt" + "<'row'<'col-md-12 text-center'p>>"

        });

        // Center pagination controls
        let pagination = $('#' + tableId + '_paginate');
        $('#customPagination').html(pagination);
    }
</script>

</body>

</html>