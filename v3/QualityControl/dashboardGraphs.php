<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include $_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php";
$BaseDist = "/assetsv2/";
?>
<!doctype html>
<html lang="en">
<head>
    
    
    <style>
        .chart-skeleton-loader {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #f9f9f9;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10;
            padding: 20px;
        }

        .skeleton-line {
            width: 80%;
            height: 12px;
            background: linear-gradient(90deg, #eee, #ddd, #eee);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            margin: 6px 0;
            border-radius: 6px;
        }

        .skeleton-line.short {
            width: 40%;
        }

        .skeleton-text {
            margin-top: 20px;
            font-size: 14px;
            color: #999;
            font-style: italic;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }

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

        /* Fixed modal sizing */
        .modal-fullscreen {
            width: 100vw;
            max-width: none;
            height: 100vh;
            margin: 0;
        }

        .modal-fullscreen .modal-content {
            height: 100vh;
            border: 0;
            border-radius: 0;
        }

        .modal-fullscreen .modal-body {
            overflow-y: auto;
            padding: 15px;
        }

        /* DataTable wrapper height */
        .modal .modal-body #PopUpTableFullScreen {
            height: calc(100vh - 100px);
            overflow: auto;
        }

        /* Ensure proper table display */
        #key-datatableFullscreen_wrapper {
            width: 100%;
        }

        .scrollPopup {
            max-height: 80vh;
            overflow-y: auto;
        }
    </style>
</head>

<body data-sidebar="dark">
    <?php include "PopUpTable.php"; ?>
    
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Antibiotic Report</h4>
                                <div id="antibioticGraph" style="height: 200px; width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Micro Report</h4>
                                <div id="microGraph" style="height: 200px; width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fixed Modal Structure -->
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
    
    <!-- DataTables Buttons JS -->
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
      <script src="<?php echo $BaseDist ?>assets/js/canvasjs.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        var table;
        var chartInstances = {};

        $(document).ready(function () {
            getAntibioticGraphData();
            getMicroGraphData();
            
            $('#myModalFullScreen, #myModal').on('shown.bs.modal', function (e) {
                $("html").addClass("overflow-hidden");
                $(this).css({"padding": "0"});
            });
            
            $('#myModalFullScreen, #myModal').on('hidden.bs.modal', function (e) {
                $("html").removeClass("overflow-hidden");
            });
        

        // Export function
            function exportTableToExcel(tableId, filename) {
                var table = document.getElementById(tableId);
                var wb = XLSX.utils.table_to_book(table, {sheet: "Sheet JS"});
                XLSX.writeFile(wb, filename + '.xlsx');
            }

            function getAntibioticGraphData() {
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();
                $.ajax({
                    url: 'Ajax/AjaxDashboardGraphs.php',
                    type: 'POST',
                    data: {
                        'fromDate': '2024-06-20',
                        'toDate': '2025-06-20',
                        'methodType': 'antibioticGraphData'
                    },
                    success: function (data) {
                        console.log("getAntibioticGraphData ", data);
                        var AryData = JSON.parse(JSON.stringify(data));
                        renderGraphData(AryData.Data,'antibioticGraph',"antibioticData","Antibiotic");
                    }
                });
            }
            function getMicroGraphData() {
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();
                $.ajax({
                    url: 'Ajax/AjaxDashboardGraphs.php',
                    type: 'POST',
                    data: {
                        'fromDate': '2024-06-20',
                        'toDate': '2025-06-20',
                        'methodType': 'microGraphData'
                    },
                    success: function (data) {
                        console.log("microGraphData ", data);
                        var AryData = JSON.parse(JSON.stringify(data));
                        renderGraphData(AryData.Data,'microGraph',"microData","Micro");
                    }
                });
            }


            function renderGraphData(data,tableId, methodType, filename) {
                
                var options = {
                    chart: {
                        type: 'bar',
                        height: 300,
                        toolbar: {
                            show: false
                        },
                        events: {
                            dataPointSelection: function (event, chartContext, config) {
                                var seriesIndex = config.seriesIndex;
                                var dataPointIndex = config.dataPointIndex;
                                var name = config.w.config.series[seriesIndex]['name'];
                                var plantName = config.w.config.xaxis.categories[dataPointIndex];

                                getPopupTableData(data['months'][dataPointIndex], name,methodType, filename);
                            }
                        }
                    },
                    series: data['series'],
                    xaxis: {
                        categories: data['months'],
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
                            offsetY: 0,
                        },
                    },
                    yaxis: {
                        title: {
                            text: 'Count'
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
                        enabled: true,
                        formatter: function (val) {
                            if (parseFloat(val) < 30 && parseFloat(val) > 0) {
                                return parseInt(val);
                            }
                            return '';
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
                }

                if (chartInstances[tableId]) {
                    chartInstances[tableId].destroy();
                }
                
                var chart = new ApexCharts(document.querySelector("#"+tableId), options);
                
                chart.render().then(() => {
                    const dataLabels = document.querySelectorAll('.apexcharts-text.apexcharts-datalabel');
                    dataLabels.forEach(label => {
                        label.style.transform = 'rotate(-45deg)';
                        label.style.transformOrigin = 'center';
                    });
                });

                chartInstances[tableId] = chart;
            }

            function getPopupTableData(Processor, ReqType, methodType, filename) {
                console.log(Processor, ReqType);
                if(ReqType != 'Total Samples'){

                
                var fromDate = Processor+"-01";
                var toDate = Processor+"-30";
                
                $.ajax({
                    url: 'Ajax/AjaxDashboardGraphs.php',
                    type: 'POST',
                    data: {
                        'fromDate': fromDate,
                        'toDate': toDate,
                        'methodType': methodType,
                        'reqType' : ReqType
                    },
                    success: function (data) {
                        $("#PopUpTableFullScreen").html(data);
                        
                        // Destroy existing DataTable if it exists
                        if ($.fn.dataTable.isDataTable('#key-datatableFullscreen')) {
                            $('#key-datatableFullscreen').DataTable().destroy();
                        }
                        
                        // Initialize new DataTable with proper configuration
                        table = $('#key-datatableFullscreen').DataTable({
                            dom: 'Bfrtip',
                            buttons: [
                                {
                                    extend: 'excel',
                                    text: 'Download Excel',
                                    className: 'btn btn-success',
                                    filename: function() {
                                        return (filename + " " + ReqType || filename) + new Date().toISOString().slice(0,10);
                                    }
                                }
                            ],
                            responsive: true,
                            scrollX: true,
                            scrollY: '60vh',
                            scrollCollapse: true,
                            paging: true,
                            searching: true,
                            ordering: true,
                            info: true
                        });
                        
                        // Update modal title
                        $('#modalTitle').text((filename + " " + ReqType || filename)+ ' '+ fromDate + ' to ' + toDate);
                        
                        // Show modal
                        $('#myModalFullScreen').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        alert('Error loading data: ' + error);
                    }
                });
                }
            }

        });
    </script>
</body>
</html>