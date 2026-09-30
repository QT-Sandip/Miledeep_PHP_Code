<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$CurrentMonth = date('m');
$Currentyear = date('Y');
$Startyear = isset($_SESSION["StartYear"]) ? $_SESSION["StartYear"] : ($Currentyear - 3);
$FinClsYear = ($CurrentMonth > 3) ? $Currentyear : ($Currentyear - 1);
$ProductionPlant = isset($_SESSION['UnitID']) ? (int) $_SESSION['UnitID'] : 0;
function qcPreviewOptions($iconn, $sql, $valueKey, $labelKey)
{
    $options = array();
    $result = mysqli_query($iconn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $options[] = array("value" => $row[$valueKey], "label" => $row[$labelKey]);
        }
    }
    return $options;
}

$plantOptions = qcPreviewOptions($iconn, "SELECT ProcessingStationID, StationName FROM RmProcessingStations WHERE StationStatus = 1 ORDER BY StationName", "ProcessingStationID", "StationName");
$poOptions = qcPreviewOptions($iconn, "SELECT PoID, PoNo FROM ScrmPoData WHERE Status NOT IN (2,3,4) ORDER BY PoID DESC LIMIT 500", "PoID", "PoNo");
$buyerOptions = qcPreviewOptions($iconn, "SELECT ConID, CASE WHEN IFNULL(ConsigneeCode, '') <> '' THEN ConsigneeCode ELSE ConsigneeName END AS BuyerName FROM ScrmConsigneeDetails ORDER BY ConsigneeName", "ConID", "BuyerName");
$countryOptions = qcPreviewOptions($iconn, "SELECT id, name FROM ScrmCountriesList ORDER BY name", "id", "name");
$labOptions = qcPreviewOptions($iconn, "SELECT DISTINCT TestingLab FROM QcSamplingLettersExt WHERE IFNULL(TestingLab, '') <> '' ORDER BY TestingLab", "TestingLab", "TestingLab");
$statusOptions = qcPreviewOptions($iconn, "SELECT DISTINCT Status FROM QcSamplingLettersExt WHERE IFNULL(Status, '') <> '' ORDER BY Status", "Status", "Status");
/*
 * ScrmPoData.ProcessorID stores AdminExporterDetails.ExpID. The
 * RmProcessingStations values are a different ID domain.
 */
$plantOptions1 = qcPreviewOptions(
    $iconn,
    'SELECT DISTINCT AED.ExpID, AED.ExpName AS PlantName
     FROM ScrmPoData AS SPD
     INNER JOIN AdminExporterDetails AS AED ON AED.ExpID = SPD.ProcessorID
     WHERE SPD.Status NOT IN (2,3,4)
     ORDER BY PlantName',
    'ExpID',
    'PlantName'
);
?>
<!doctype html>
<html lang="en">

<head>
    <?php include($IncludesPath . "title-meta.php"); ?>
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <?php include($IncludesPath . "head-css.php"); ?>
    <style>
        label {
            font-size: 13px;
            color: #111827;
            margin-bottom: 4px;
        }

        .metric-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 16px;
            background: #fff;
            min-height: 92px;
        }

        .metric-label {
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .metric-value {
            color: #111827;
            font-size: 26px;
            font-weight: 700;
            line-height: 1.2;
        }

        .metric-note {
            color: #6b7280;
            font-size: 12px;
        }

        .table td,
        .table th {
            font-size: 12px;
            padding: 7px !important;
            vertical-align: top;
        }

        .lab-name-cell {
            min-width: 260px;
            white-space: normal !important;
        }

        .product-cell {
            min-width: 520px;
            max-width: 720px;
            white-space: normal !important;
            word-break: break-word;
        }

        #lab-availability-table tbody td {
            color: #111827 !important;
            background-color: #ffffff !important;
        }

        #lab-availability-table tbody tr.lab-row-pass td {
            background-color: #dcfce7 !important;
        }

        #lab-availability-table tbody tr.lab-row-fail td {
            background-color: #fee2e2 !important;
        }

        #lab-availability-table.table-hover tbody tr:hover td,
        #lab-availability-table.table-hover tbody tr:hover td.sorting_1,
        #lab-availability-table.table-hover tbody tr:hover td.sorting_2,
        #lab-availability-table.table-hover tbody tr:hover td.sorting_3,
        div.dataTables_scrollBody #lab-availability-table.table-hover tbody tr:hover td {
            color: #111827 !important;
            background-color: #eef2ff !important;
            box-shadow: none !important;
        }

        #lab-availability-table.table-hover tbody tr.lab-row-pass:hover td {
            background-color: #bbf7d0 !important;
        }

        #lab-availability-table.table-hover tbody tr.lab-row-fail:hover td {
            background-color: #fecaca !important;
        }

        #lab-availability-table.table-hover tbody tr:hover td *,
        div.dataTables_scrollBody #lab-availability-table.table-hover tbody tr:hover td * {
            color: inherit !important;
        }

        #lab-availability-table .status-ok,
        #lab-availability-table.table-hover tbody tr:hover .status-ok {
            background-color: #dcfce7 !important;
            color: #166534 !important;
        }

        #lab-availability-table .status-warn,
        #lab-availability-table.table-hover tbody tr:hover .status-warn {
            background-color: #fef3c7 !important;
            color: #92400e !important;
        }

        #lab-availability-table .status-risk,
        #lab-availability-table.table-hover tbody tr:hover .status-risk {
            background-color: #fee2e2 !important;
            color: #991b1b !important;
        }

        #lab-availability-table thead th {
            color: #111827 !important;
            background-color: #f8fafc !important;
            border-bottom: 1px solid #cbd5e1 !important;
            white-space: nowrap;
        }

        #lab-availability-table_wrapper .qc-table-toolbar {
            gap: 12px;
        }

        #lab-availability-table_wrapper .qc-table-left {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        #lab-availability-table_wrapper .dataTables_filter,
        #lab-availability-table_wrapper .dataTables_length {
            margin-bottom: 0;
        }

        #lab-availability-table_wrapper .dataTables_filter label,
        #lab-availability-table_wrapper .dataTables_length label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0;
        }

        #lab-availability-table_wrapper .dataTables_filter input {
            width: 280px;
            max-width: 100%;
            height: 36px;
        }

        #lab-availability-table_wrapper .dt-buttons .btn {
            margin-bottom: 0;
            white-space: nowrap;
        }

        .multi-line-cell {
            min-width: 120px;
            white-space: pre-line !important;
        }

        .status-pill {
            display: inline-block;
            min-width: 72px;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            text-align: center;
        }

        .status-ok {
            background: #dcfce7;
            color: #166534;
        }

        .status-warn {
            background: #fef3c7;
            color: #92400e;
        }

        .status-risk {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>

<body data-sidebar="dark">
    <?php include($IncludesPath . "PreLoader.php"); ?>
    <div id="layout-wrapper">
        <?php include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php"); ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">QC Management Preview</h4>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2"><label>Financial Year</label><select
                                        id="drpFinancialYear" class="form-control">
                                        <?php for ($year = $Startyear; $year <= ($Currentyear + 1); $year++): ?>
                                            <option value="<?php echo $year; ?>" <?php echo ($year == $FinClsYear) ? "selected" : ""; ?>><?php echo $year . "-" . substr(($year + 1), 2); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select></div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2"><label>Production Plant</label><select
                                        id="drpPlant" class="form-control select2"
                                        multiple><?php foreach ($plantOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option["value"]); ?>">
                                                <?php echo htmlspecialchars($option["label"]); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select></div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2"><label>PO</label><select id="drpPo"
                                        class="form-control select2" multiple><?php foreach ($poOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option["value"]); ?>">
                                                <?php echo htmlspecialchars($option["label"]); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select></div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2"><label>Buyer</label><select id="drpBuyer"
                                        class="form-control select2"
                                        multiple><?php foreach ($buyerOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option["value"]); ?>">
                                                <?php echo htmlspecialchars($option["label"]); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select></div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2"><label>Destination Country</label><select
                                        id="drpDestinationCountry" class="form-control select2"
                                        multiple><?php foreach ($countryOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option["value"]); ?>">
                                                <?php echo htmlspecialchars($option["label"]); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select></div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2"><label>Lab</label><select id="drpLab"
                                        class="form-control select2" multiple><?php foreach ($labOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option["value"]); ?>">
                                                <?php echo htmlspecialchars($option["label"]); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select></div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2"><label>Status</label><select id="drpStatus"
                                        class="form-control select2">
                                        <option value="">All</option>
                                        <option value="qc_completed">QC Code Completed</option>
                                        <option value="qc_pending">QC Code Pending</option>
                                        <option value="lab_pending">Lab Pending</option>
                                        <?php foreach ($statusOptions as $option): ?>
                                            <option value="lab_status:<?php echo htmlspecialchars($option["value"]); ?>">Lab
                                                Status: <?php echo htmlspecialchars($option["label"]); ?></option>
                                        <?php endforeach; ?>
                                    </select></div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2 d-flex align-items-end"><button
                                        id="btnSearch" type="button" class="btn btn-primary btn-block">Search</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="kpiRow">
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="metric-card">
                                <div class="metric-label">Total POs</div>
                                <div class="metric-value" id="kpiTotalPo">0</div>
                                <div class="metric-note">Completed + pending POs</div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="metric-card">
                                <div class="metric-label">QC Completed</div>
                                <div class="metric-value" id="kpiQcCompleted">0</div>
                                <div class="metric-note">From monthly QC status</div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="metric-card">
                                <div class="metric-label">QC Pending</div>
                                <div class="metric-value" id="kpiQcPending">0</div>
                                <div class="metric-note">From monthly QC status</div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="metric-card">
                                <div class="metric-label">Avg Availability</div>
                                <div class="metric-value" id="kpiAvgAvailability">0%</div>
                                <div class="metric-note">Packed / ordered Mcs</div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="metric-card">
                                <div class="metric-label">Internal Samples</div>
                                <div class="metric-value" id="kpiInternalSamples">0</div>
                                <div class="metric-note">Unique POs assigned</div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="metric-card">
                                <div class="metric-label">External Samples</div>
                                <div class="metric-value" id="kpiExternalSamples">0</div>
                                <div class="metric-note">Unique POs assigned</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">QC Code Status</h4>
                                    <div id="qc-status-chart" style="height: 320px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Lab Coverage</h4>
                                    <div id="lab-coverage-chart" style="height: 320px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Production Readiness</h4>
                                    <div id="production-readiness-chart" style="height: 320px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Buyer Pending Risk</h4>
                                    <div id="buyer-risk-chart" style="height: 360px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Destination Pending Risk</h4>
                                    <div id="country-risk-chart" style="height: 360px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Qc Completed Count and Pending Count</h4>
                                    <div id="monthly-qc-count-chart" style="height: 360px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Center Wise Quantity</h4>
                                    <div id="center-wise-quantity-chart" style="height: 360px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Supplier Wise Quantity</h4>
                                    <div id="supplier-wise-quantity-chart" style="height: 360px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-end">
                                        <div class="col-lg-3 col-md-5 col-sm-8 mb-2"><label>Lots</label><select
                                                id="drpLots" class="form-control select2" multiple></select></div>
                                        <div class="col-lg-2 col-md-3 col-sm-4 mb-2"><button id="btnLotSearch"
                                                type="button" class="btn btn-primary btn-block">Search Lots</button>
                                        </div>
                                    </div>
                                    <div class="table-responsive mt-3">
                                        <table id="lots-details-table" class="table table-bordered table-striped w-100">
                                            <thead>
                                                <tr>
                                                    <th>Lot No</th>
                                                    <th>Supplier</th>
                                                    <th>Center</th>
                                                    <th>Arrival Date</th>
                                                    <th>Total Weight</th>
                                                    <th>Result</th>
                                                    <th>Positive</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">PO Grade Lab Availability</h4>
                                    <div class="table-responsive">
                                        <table id="lab-availability-table"
                                            class="table table-bordered table-hover nowrap w-100">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>S.No</th>
                                                    <th>Po No</th>
                                                    <th>Buyer</th>
                                                    <th>Brand</th>
                                                    <th>Ship By Date</th>
                                                    <th>Country</th>
                                                    <th>Product</th>
                                                    <th>Packing Style</th>
                                                    <th>Ordered Mcs</th>
                                                    <th>Assigned Mcs</th>
                                                    <th>Production Packed</th>
                                                    <th>Availability %</th>
                                                    <th>Day Code</th>
                                                    <th>Psi ID</th>
                                                    <th>Regular Lab Name</th>
                                                    <th>Regular Sampling Date</th>
                                                    <th>Regular Sampling Status</th>
                                                    <th>Buyer Lab Name</th>
                                                    <th>Buyer Sampling Date</th>
                                                    <th>Buyer Sampling Status</th>
                                                    <th>Expected Sampling Date</th>
                                                    <th>Shipment Status</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include($IncludesPath . "footer.php"); ?>
        </div>
    </div>

    <div class="modal fade" id="graphDrilldownModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="graph-drilldown-title">Graph Details</h5><button type="button"
                        class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="graph-drilldown-table" class="table table-bordered table-striped w-100">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include($IncludesPath . "vendor-scripts.php");
    include($IncludesPath . "NewScripts.php"); ?>
    <script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/jszip/jszip.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/apexcharts/apexcharts.min.js"></script>
    <script>
        let chartInstances = {};
        $(document).ready(function () {
            const productionPlant = <?php echo json_encode($ProductionPlant); ?>;
            if (productionPlant !== 0) {
                $('#drpPlant').removeAttr('multiple');
            }

            $('.select2').select2({ placeholder: 'Select', allowClear: true, width: '100%' });

            if (productionPlant !== 0) {
                $('#drpPlant').val(String(productionPlant)).trigger('change');
                $('#drpPlant').prop('disabled', true);
            } else {
                $('#drpPlant').prop('disabled', false).val(null).trigger('change');
            }

            $('#btnSearch').on('click', loadManagementPreview);
            $('#btnLotSearch').on('click', function () { renderLotsDetails(window.managementPreviewLotsDetails || []); });
            loadManagementPreview();
        });
        function selectedValues(selector) { const value = $(selector).val(); return Array.isArray(value) ? value.join(',') : (value || ''); }
        function escapeValue(value) { if (value === null || value === undefined || value === '') return '--'; return $('<div/>').text(value).html(); }
        function escapeMultiLineValue(value) { return escapeValue(value).replace(/\r?\n/g, '<br>'); }
        function formatProductList(value) {
            if (value === null || value === undefined || value === '') return '--';
            const products = String(value).split(',').map(function (item) { return item.trim(); }).filter(function (item) { return item !== ''; });
            if (products.length === 0) return '--';
            return products.map(function (item) { return escapeValue(item); }).join('<br>');
        }
        function numericValue(value) { const numberValue = parseFloat(value); return Number.isFinite(numberValue) ? numberValue : 0; }
        function formatDisplayDate(value) {
            if (value === null || value === undefined || value === '' || value === '0000-00-00') return '--';
            const cleanValue = String(value).split(' ')[0];
            const parts = cleanValue.split('-');
            if (parts.length !== 3) return escapeValue(value);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const year = parts[0];
            const monthIndex = parseInt(parts[1], 10) - 1;
            const day = parts[2].padStart(2, '0');
            if (monthIndex < 0 || monthIndex > 11 || !year || !day) return escapeValue(value);
            return day + '-' + months[monthIndex] + '-' + year;
        }
        function dateSortValue(value) {
            if (value === null || value === undefined || value === '' || value === '0000-00-00') return '';
            const cleanValue = String(value).split(' ')[0];
            const parts = cleanValue.split('-');
            if (parts.length === 3 && parts[0].length === 4) return cleanValue;
            return '';
        }
        function displayCellValue(column, value) {
            return /date/i.test(column) ? formatDisplayDate(value) : escapeValue(value);
        }
        function displayTableCell(column, value) {
            if (/date/i.test(column)) {
                return '<td data-order="' + dateSortValue(value) + '">' + formatDisplayDate(value) + '</td>';
            }
            if (column === 'ProductType') {
                return '<td class="product-cell">' + formatProductList(value) + '</td>';
            }
            if (column === 'DayCode' || column === 'PsiID') {
                return '<td class="multi-line-cell">' + escapeMultiLineValue(value) + '</td>';
            }
            return '<td>' + escapeValue(value) + '</td>';
        }
        function isQcCompleted(row) {
            const SamplingType = parseInt(row.SamplingType || 0);
            if (SamplingType === 1 && row.InternalStatus == 'Pass') {
                return true;
            } else if ((SamplingType === 0 || SamplingType === 2) && row.ExternalStatus == 'Pass') {
                return true;
            } else if (SamplingType === 3 && row.InternalStatus == 'Pass' && row.ExternalStatus == 'Pass') {
                return true;
            }
            return false;
        }
        function readinessLabel(row) { const availability = numericValue(row.AvailabilityPercentage); const hasQc = isQcCompleted(row); const hasLab = !!(row.InternalLabName || row.ExternalLabName); if (availability >= 100 && hasQc && hasLab) return 'Ready'; if (availability >= 80 && hasLab) return 'Watch'; return 'Risk'; }
        function normalizeLabStatus(value) { return String(value || '').trim().toLowerCase(); }
        function isPassStatus(value) { return normalizeLabStatus(value) === 'pass'; }
        function isFailStatus(value) { return normalizeLabStatus(value) === 'fail'; }
        function hasInternalSample(row) { return !!(row.InternalLabName || row.InternalDate || row.InternalStatus); }
        function hasExternalSample(row) { return !!(row.ExternalLabName || row.ExternalDate || row.ExternalStatus); }
        function shipmentStatus(row) {
            const samplingType = parseInt(row.SamplingType || 0);
            const orderedMcs = numericValue(row.OrderedMcs);
            const packedMcs = numericValue(row.PackedMcs !== undefined ? row.PackedMcs : row.AvalMcs);
            const productionCompleted = orderedMcs > 0 && Math.abs(packedMcs - orderedMcs) <= 0.01;
            const internalPass = isPassStatus(row.InternalStatus);
            const externalPass = isPassStatus(row.ExternalStatus);
            const internalFail = isFailStatus(row.InternalStatus);
            const externalFail = isFailStatus(row.ExternalStatus);
            const requiresInternal = samplingType === 1 || samplingType === 3;
            const requiresExternal = samplingType === 0 || samplingType === 2 || samplingType === 3;

            if (internalFail || externalFail) {
                return { text: 'Failed', kind: 'status-risk' };
            }

            if (isQcCompleted(row)) {
                return { text: 'Ready to shipment', kind: 'status-ok' };
            }
            if (samplingType === 3) {
                if (internalPass && !externalPass) {
                    return { text: 'Buyer Result Pending', kind: 'status-warn' };
                }
                if (externalPass && !internalPass) {
                    return { text: 'Regular Result Pending', kind: 'status-warn' };
                }
            }

            if (hasInternalSample(row) || hasExternalSample(row)) {
                if (requiresInternal && !hasInternalSample(row)) {
                    return { text: 'Lab Testing Pending (Regular)', kind: 'status-warn' };
                }
                if (requiresExternal && !hasExternalSample(row)) {
                    return { text: 'Lab Testing Pending (Buyer)', kind: 'status-warn' };
                }
            }

            if (!productionCompleted) {
                return { text: 'Production Pending', kind: 'status-warn' };
            }

            return { text: 'Lab Testing Pending (Regular/Buyer)', kind: 'status-warn' };
        }
        function labRowClass(row) { const status = shipmentStatus(row); if (status.text === 'Failed') return 'lab-row-fail'; if (status.text === 'Ready to shipment') return 'lab-row-pass'; return ''; }
        function statusPill(text, kind) { return '<span class="status-pill ' + kind + '">' + escapeValue(text) + '</span>'; }
        function loadManagementPreview() {
            $.ajax({ url: 'Ajax/ajax-qc-dashboard-management-preview.php', type: 'POST', dataType: 'json', data: { FinancialYear: $('#drpFinancialYear').val(), PlantID: selectedValues('#drpPlant'), PoID: selectedValues('#drpPo'), BuyerID: selectedValues('#drpBuyer'), DestinationCountryID: selectedValues('#drpDestinationCountry'), LabName: selectedValues('#drpLab'), Status: $('#drpStatus').val() }, success: function (response) { if (response.Error) { alert(response.Error); return; } const rows = response.Data || []; window.managementPreviewResponse = response; window.managementPreviewRows = rows; window.managementPreviewLotsDetails = response.LotsDetailsData || []; renderKpis(rows, response); renderCharts(rows, response); renderExistingDashboardGraphs(response); bindLots(response.LotsData || []); renderLotsDetails(window.managementPreviewLotsDetails); renderLabAvailabilityTable(rows); }, error: function () { alert('Unable to load QC management preview data.'); } });
        }
        function renderKpis(rows, response) {
            const internalPoIds = {};
            const externalPoIds = {};
            const totalPoIds = {};
            const completedPoIds = {};
            let orderedMcs = 0, packedMcs = 0;
            rows.forEach(function (row) {
                const poId = row.PoID || '';
                orderedMcs += numericValue(row.OrderedMcs);
                packedMcs += numericValue(row.AssignedMcs !== undefined ? row.AssignedMcs : (row.PackedMcs !== undefined ? row.PackedMcs : row.AvalMcs));
                if (poId) totalPoIds[poId] = true;
                if (poId && (row.DayCode || row.PsiID)) completedPoIds[poId] = true;
                if (poId && row.InternalLabName) internalPoIds[poId] = true;
                if (poId && row.ExternalLabName) externalPoIds[poId] = true;
            });
            const totalPoCount = Object.keys(totalPoIds).length;
            const completed = Object.keys(completedPoIds).length;
            const pending = Math.max(totalPoCount - completed, 0);
            const availabilityPercentage = orderedMcs > 0 ? (packedMcs * 100 / orderedMcs) : 0;
            $('#kpiTotalPo').text(totalPoCount); $('#kpiQcCompleted').text(completed); $('#kpiQcPending').text(pending); $('#kpiAvgAvailability').text(availabilityPercentage.toFixed(1) + '%'); $('#kpiInternalSamples').text(Object.keys(internalPoIds).length); $('#kpiExternalSamples').text(Object.keys(externalPoIds).length);
        }
        function groupedCounts(rows, keyName, pendingOnly) { const counts = {}; rows.forEach(function (row) { if (pendingOnly && isQcCompleted(row)) return; const key = row[keyName] || 'Not Available'; counts[key] = (counts[key] || 0) + 1; }); return Object.entries(counts).sort(function (a, b) { return b[1] - a[1]; }).slice(0, 10); }
        function renderCharts(rows, response) { const totalPoIds = {}; const completedPoIds = {}; rows.forEach(function (row) { const poId = row.PoID || ''; if (poId) totalPoIds[poId] = true; if (poId && (row.DayCode || row.PsiID)) completedPoIds[poId] = true; }); const completed = Object.keys(completedPoIds).length; const pending = Math.max(Object.keys(totalPoIds).length - completed, 0); renderDonut('qc-status-chart', ['Completed', 'Pending'], [completed, pending], ['#16a34a', '#f59e0b']); const internalSamples = rows.filter(function (row) { return !!row.InternalLabName; }).length; const externalSamples = rows.filter(function (row) { return !!row.ExternalLabName; }).length; const noLab = rows.filter(function (row) { return !row.InternalLabName && !row.ExternalLabName; }).length; renderDonut('lab-coverage-chart', ['Internal', 'External', 'No Lab'], [internalSamples, externalSamples, noLab], ['#2563eb', '#7c3aed', '#ef4444']); const readinessBuckets = { '0-49%': 0, '50-79%': 0, '80-99%': 0, '100%+': 0 }; rows.forEach(function (row) { const value = numericValue(row.AvailabilityPercentage); if (value >= 100) readinessBuckets['100%+']++; else if (value >= 80) readinessBuckets['80-99%']++; else if (value >= 50) readinessBuckets['50-79%']++; else readinessBuckets['0-49%']++; }); renderBar('production-readiness-chart', Object.keys(readinessBuckets), Object.values(readinessBuckets), 'PO Grades', '#0f766e', false); const buyerRisk = groupedCounts(rows, 'BuyerName', true); renderBar('buyer-risk-chart', buyerRisk.map(function (item) { return item[0]; }), buyerRisk.map(function (item) { return item[1]; }), 'Pending Rows', '#dc2626', true); const countryRisk = groupedCounts(rows, 'FinalDestinationCountry', true); renderBar('country-risk-chart', countryRisk.map(function (item) { return item[0]; }), countryRisk.map(function (item) { return item[1]; }), 'Pending Rows', '#ea580c', true); }
        function renderExistingDashboardGraphs(response) { const monthlyData = response.MonthlyData || []; renderMultiBar('monthly-qc-count-chart', monthlyData.map(function (row) { return row.MonthYear; }), [{ name: 'QC Completed', data: monthlyData.map(function (row) { return parseInt(row.CompletedCount || 0); }) }, { name: 'QC Pending', data: monthlyData.map(function (row) { return parseInt(row.PendingCount || 0); }) }], ['#16a34a', '#f59e0b']); const centerData = response.CenterWiseData || []; renderMultiBar('center-wise-quantity-chart', centerData.map(function (row) { return row.CenterName || 'Not Available'; }), [{ name: 'Positive', data: centerData.map(function (row) { return parseInt(row.PositiveCount || 0); }) }, { name: 'Negative', data: centerData.map(function (row) { return parseInt(row.NegativeCount || 0); }) }], ['#dc2626', '#16a34a']); const supplierData = response.SupplierData || []; renderMultiBar('supplier-wise-quantity-chart', supplierData.map(function (row) { return row.SupplierName || 'Not Available'; }), [{ name: 'Positive', data: supplierData.map(function (row) { return parseInt(row.PositiveCount || 0); }) }, { name: 'Negative', data: supplierData.map(function (row) { return parseInt(row.NegativeCount || 0); }) }], ['#dc2626', '#16a34a']); }
        function resetChart(divId) { if (chartInstances[divId]) chartInstances[divId].destroy(); }
        function renderDonut(divId, labels, series, colors) { resetChart(divId); chartInstances[divId] = new ApexCharts(document.querySelector('#' + divId), { chart: { type: 'donut', height: 310, events: { dataPointSelection: function (event, chartContext, config) { showGraphDrilldown(divId, labels[config.dataPointIndex], config.seriesIndex, config.dataPointIndex); } } }, labels: labels, series: series, colors: colors, legend: { position: 'bottom' }, dataLabels: { enabled: true } }); chartInstances[divId].render(); }
        function renderBar(divId, categories, series, seriesName, color, horizontal) { resetChart(divId); chartInstances[divId] = new ApexCharts(document.querySelector('#' + divId), { chart: { type: 'bar', height: 330, toolbar: { show: false }, events: { dataPointSelection: function (event, chartContext, config) { showGraphDrilldown(divId, categories[config.dataPointIndex], config.seriesIndex, config.dataPointIndex); } } }, plotOptions: { bar: { horizontal: horizontal, borderRadius: 3 } }, series: [{ name: seriesName, data: series }], xaxis: { categories: categories, labels: { trim: true } }, colors: [color], dataLabels: { enabled: false } }); chartInstances[divId].render(); }
        function renderMultiBar(divId, categories, series, colors) { resetChart(divId); chartInstances[divId] = new ApexCharts(document.querySelector('#' + divId), { chart: { type: 'bar', height: 340, toolbar: { show: false }, events: { dataPointSelection: function (event, chartContext, config) { showGraphDrilldown(divId, categories[config.dataPointIndex], config.seriesIndex, config.dataPointIndex); } } }, plotOptions: { bar: { horizontal: false, borderRadius: 3, columnWidth: '35%' } }, series: series, xaxis: { categories: categories, labels: { trim: true, rotate: -35 } }, colors: colors, dataLabels: { enabled: false }, legend: { position: 'bottom' }, noData: { text: 'No data available' } }); chartInstances[divId].render(); }
        function poDrilldownColumns() { return ['PoNo', 'BuyerName', 'FinalDestinationCountry', 'ProductType', 'Brand', 'OrderedMcs', 'AssignedMcs', 'PackedMcs', 'AvailabilityPercentage', 'InternalLabName', 'ExternalLabName', 'DayCode', 'PsiID']; }
        function showGraphDrilldown(divId, label, seriesIndex, dataPointIndex) { const response = window.managementPreviewResponse || {}; const rows = window.managementPreviewRows || []; let title = label || 'Graph Details', detailRows = [], columns = []; if (divId === 'qc-status-chart') { detailRows = rows.filter(function (row) { const done = !!(row.DayCode || row.PsiID); return label === 'Completed' ? done : !done; }); title = 'QC Code Status - ' + label; columns = poDrilldownColumns(); } else if (divId === 'lab-coverage-chart') { detailRows = rows.filter(function (row) { if (label === 'Internal') return !!row.InternalLabName; if (label === 'External') return !!row.ExternalLabName; return !row.InternalLabName && !row.ExternalLabName; }); title = 'Lab Coverage - ' + label; columns = poDrilldownColumns(); } else if (divId === 'production-readiness-chart') { detailRows = rows.filter(function (row) { const value = numericValue(row.AvailabilityPercentage); if (label === '100%+') return value >= 100; if (label === '80-99%') return value >= 80 && value < 100; if (label === '50-79%') return value >= 50 && value < 80; return value < 50; }); title = 'Production Readiness - ' + label; columns = poDrilldownColumns(); } else if (divId === 'buyer-risk-chart') { detailRows = rows.filter(function (row) { return !isQcCompleted(row) && (row.BuyerName || 'Not Available') === label; }); title = 'Buyer Pending Risk - ' + label; columns = poDrilldownColumns(); } else if (divId === 'country-risk-chart') { detailRows = rows.filter(function (row) { return !isQcCompleted(row) && (row.FinalDestinationCountry || 'Not Available') === label; }); title = 'Destination Pending Risk - ' + label; columns = poDrilldownColumns(); } else if (divId === 'monthly-qc-count-chart') { detailRows = [response.MonthlyData[dataPointIndex] || {}]; title = 'Month Wise QC - ' + label; columns = ['MonthYear', 'CompletedCount', 'PendingCount']; } else if (divId === 'center-wise-quantity-chart') { detailRows = [response.CenterWiseData[dataPointIndex] || {}]; title = 'Center Wise Quantity - ' + label; columns = ['CenterName', 'PositiveCount', 'NegativeCount', 'TotalBreakupWeight']; } else if (divId === 'supplier-wise-quantity-chart') { detailRows = [response.SupplierData[dataPointIndex] || {}]; title = 'Supplier Wise Quantity - ' + label; columns = ['SupplierName', 'PositiveCount', 'NegativeCount', 'PositiveWeight', 'NegativeWeight', 'TotalBreakupWeight']; } renderDrilldownTable(title, detailRows, columns); }
        function renderDrilldownTable(title, rows, columns) { const $modal = $('#graphDrilldownModal'); const $table = $('#graph-drilldown-table'); const $thead = $table.find('thead'); const $tbody = $table.find('tbody'); if ($.fn.DataTable.isDataTable($table)) $table.DataTable().destroy(); $('#graph-drilldown-title').text(title + ' (' + rows.length + ')'); $thead.html('<tr>' + columns.map(function (column) { return '<th>' + escapeValue(column) + '</th>'; }).join('') + '</tr>'); $tbody.empty(); rows.forEach(function (row) { $tbody.append('<tr>' + columns.map(function (column) { return displayTableCell(column, row[column]); }).join('') + '</tr>'); }); $modal.off('shown.bs.modal.drilldown').on('shown.bs.modal.drilldown', function () { $table.DataTable({ pageLength: 10, scrollX: true, destroy: true, order: [] }); $table.DataTable().columns.adjust(); }); $modal.modal('show'); }
        function bindLots(lotsData) { const selectedLots = $('#drpLots').val() || []; const $lotDropdown = $('#drpLots'); $lotDropdown.empty(); (lotsData || []).forEach(function (item) { const label = item.LotNumber || item.RmLotID; const option = new Option(label, item.RmLotID, false, selectedLots.indexOf(String(item.RmLotID)) !== -1); $lotDropdown.append(option); }); $lotDropdown.trigger('change.select2'); }
        function renderLotsDetails(data) { const selectedLots = $('#drpLots').val() || []; const rows = selectedLots.length > 0 ? (data || []).filter(function (item) { return selectedLots.indexOf(String(item.RmLotID)) !== -1; }) : (data || []); const $table = $('#lots-details-table'); const $tbody = $table.find('tbody'); if ($.fn.DataTable.isDataTable($table)) $table.DataTable().destroy(); $tbody.empty(); rows.forEach(function (item) { $tbody.append(`<tr><td>${escapeValue(item.LotNumber)}</td><td>${escapeValue(item.SupplierName)}</td><td>${escapeValue(item.PurchaseStation)}</td><td data-order="${dateSortValue(item.ArrivalDate)}">${formatDisplayDate(item.ArrivalDate)}</td><td class="text-right">${numericValue(item.TotalBreakupWeight).toLocaleString()}</td><td>${escapeValue(item.ab_result)}</td><td>${escapeValue(item.ab_positive)}</td></tr>`); }); $table.DataTable({ pageLength: 10, responsive: true, destroy: true, order: [] }); }
        function renderLabAvailabilityTable(rows) {
            const $table = $('#lab-availability-table');
            const $tbody = $table.find('tbody');

            if ($.fn.DataTable.isDataTable($table)) {
                $table.DataTable().destroy();
            }

            $tbody.empty();
            rows.forEach(function (row, index) {
                const shipment = shipmentStatus(row);
                const qcStatus = statusPill(shipment.text, shipment.kind);
                const rowClass = labRowClass(row);

                $tbody.append(`
                    <tr class="${rowClass}">
                        <td>${index + 1}</td>
                        <td>${escapeValue(row.PoNo)}</td>
                        <td>${escapeValue(row.BuyerName)}</td>
                        <td>${escapeValue(row.Brand)}</td>
                        <td data-order="${dateSortValue(row.ShipByDate)}">${formatDisplayDate(row.ShipByDate)}</td>
                        <td>${escapeValue(row.FinalDestinationCountry)}</td>
                        <td class="product-cell">${formatProductList(row.ProductType)}</td>
                        <td>${escapeValue(row.PackingStyle)}</td>
                        <td class="text-right">${escapeValue(row.OrderedMcs)}</td>
                        <td class="text-right">${escapeValue(row.AssignedMcs)}</td>
                        <td class="text-right">${escapeValue(row.PackedMcs !== undefined ? row.PackedMcs : row.AvalMcs)}</td>
                        <td class="text-right">${escapeValue(row.AvailabilityPercentage)}</td>
                        <td class="multi-line-cell">${escapeMultiLineValue(row.DayCode)}</td>
                        <td class="multi-line-cell">${escapeMultiLineValue(row.PsiID)}</td>
                        <td class="lab-name-cell">${escapeValue(row.InternalLabName)}</td>
                        <td data-order="${dateSortValue(row.InternalDate)}">${formatDisplayDate(row.InternalDate)}</td>
                        <td>${escapeValue(row.InternalStatus)}</td>
                        <td class="lab-name-cell">${escapeValue(row.ExternalLabName)}</td>
                        <td data-order="${dateSortValue(row.ExternalDate)}">${formatDisplayDate(row.ExternalDate)}</td>
                        <td>${escapeValue(row.ExternalStatus)}</td>
                        <td data-order="${dateSortValue(row.ExpectedSamplingDate)}">${formatDisplayDate(row.ExpectedSamplingDate)}</td>
                        <td>${qcStatus}</td>
                    </tr>
                `);
            });
            // $table.DataTable({ pageLength: 10, responsive: true, destroy: true, order: [] });
            $table.DataTable({
                scrollY: '500px',
                scrollCollapse: true,
                paging: false,

                scrollX: true,
                searching: true,
                ordering: true,
                info: true,
                //pageLength: 25,
                //lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                destroy: true,
                //pagingType: 'simple_numbers',
                dom: '<"qc-table-toolbar d-flex justify-content-between align-items-center flex-wrap mb-3"<"qc-table-left"lf><"qc-table-actions"B>>rtip',
                buttons: [{
                    extend: 'excelHtml5',
                    text: 'Download Excel',
                    title: 'PO Grade Lab Availability Management Preview',
                    filename: 'po-grade-lab-availability',
                    className: 'btn btn-success btn-sm',
                    exportOptions: {
                        columns: ':visible',
                        stripHtml: false,
                        format: {
                            body: function (data, row, column, node) {
                                let source = data;
                                if (node && ($(node).hasClass('multi-line-cell') || $(node).hasClass('product-cell'))) {
                                    source = $(node).html();
                                }
                                const html = String(source || '').replace(/<br\s*\/?\s*>/gi, '\r\n');
                                return $('<div/>').html(html).text();
                            }
                        }
                    },
                    customize: function (xlsx) {
                        const sheet = xlsx.xl.worksheets['sheet1.xml'];
                        $('row c[r^="G"], row c[r^="M"], row c[r^="N"]', sheet).attr('s', '55');
                    }
                }],
                columnDefs: [
                    { targets: [6], width: '560px' },
                    { targets: [14, 17], width: '260px' },
                    { targets: [15, 16, 18, 19, 20], width: '130px' }
                ],
                order: []
            });
        }
    </script>
</body>

</html>
