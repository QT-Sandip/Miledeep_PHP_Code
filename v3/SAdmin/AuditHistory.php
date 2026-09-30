<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");

$PageTitle = "Audit History";
$PageTitleFolder = "Admin";
$AccessType = $_SESSION['AccessType'];
$SessionClientID = $_SESSION['ClientID'];

$Query = "";
if ($AccessType != 1) {
    $Query = " WHERE ClientAccountID='$SessionClientID' ";
}

$SqlClients = "SELECT ClientAccountID, ClientName FROM AdminRegisteredClients $Query ORDER BY ClientName ASC";
$StmtClients = $iconn->query($SqlClients);
?>
<!doctype html>
<html lang="en">
<head>
    <?php include($IncludesPath . "title-meta.php"); ?>
    <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/v3/assets/css/purchase-coldstore-theme.css" rel="stylesheet" type="text/css" />
    <?php include($IncludesPath . "head-css.php"); ?>
    <style>
        /* ============================================
           AUDIT HISTORY - UI/UX STYLES
           ============================================ */

        /* --- Page Header --- */
        .audit-page-header {
            margin-bottom: 24px;
        }
        .audit-page-header .audit-header-row {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .audit-page-header .audit-header-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
            flex-shrink: 0;
        }
        .audit-page-header .audit-header-text h4 {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            line-height: 1.2;
        }
        .audit-page-header .audit-header-text p {
            font-size: 13.5px;
            color: #64748b;
            margin: 3px 0 0 0;
            line-height: 1.4;
        }

        /* --- Filter Card --- */
        .audit-filter-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 1px 2px rgba(0,0,0,.06);
            margin-bottom: 24px;
        }
        .audit-filter-card .filter-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .audit-filter-card .filter-header i {
            color: #4f46e5;
            font-size: 16px;
        }
        .audit-filter-card .filter-header h6 {
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin: 0;
        }
        .audit-filter-bar label {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
            display: block;
        }
        .audit-filter-bar .form-control,
        .audit-filter-bar .select2-container--default .select2-selection--single {
            min-height: 38px;
            font-size: 13px;
        }
        .audit-filter-bar .filter-actions {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }
        .audit-filter-bar .filter-actions .btn {
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            padding: 7px 20px;
            transition: all .2s ease;
        }
        .audit-filter-bar .filter-actions .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            border: none;
            box-shadow: 0 2px 4px rgba(79,70,229,.3);
        }
        .audit-filter-bar .filter-actions .btn-primary:hover {
            box-shadow: 0 4px 8px rgba(79,70,229,.4);
            transform: translateY(-1px);
        }
        .audit-filter-bar .filter-actions .btn-outline-secondary {
            border: 1.5px solid #cbd5e1;
            color: #475569;
            background: #fff;
        }
        .audit-filter-bar .filter-actions .btn-outline-secondary:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #334155;
        }

        /* --- Date Validation --- */
        #dateValidationMsg .alert {
            border-radius: 8px;
            font-size: 12.5px;
            padding: 8px 14px;
            margin: 0;
        }

        /* --- Loading Overlay --- */
        .audit-loading-overlay {
            display: none;
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,.85);
            backdrop-filter: blur(2px);
            border-radius: 14px;
            z-index: 10;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 12px;
        }
        .audit-loading-overlay.active {
            display: flex;
        }
        .audit-loading-spinner {
            width: 36px;
            height: 36px;
            border: 3px solid #e2e8f0;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: auditSpin .8s linear infinite;
        }
        @keyframes auditSpin {
            to { transform: rotate(360deg); }
        }
        .audit-loading-text {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }

        /* --- Alert Styling --- */
        .audit-alert {
            border-radius: 10px;
            padding: 14px 18px;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: auditFadeIn .3s ease;
        }
        .audit-alert i {
            font-size: 18px;
            flex-shrink: 0;
        }
        .audit-alert-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }
        .audit-alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .audit-alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }
        @keyframes auditFadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Empty State --- */
        .audit-empty-state {
            text-align: center;
            padding: 48px 24px;
            animation: auditFadeIn .4s ease;
        }
        .audit-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .audit-empty-icon i {
            font-size: 32px;
            color: #94a3b8;
        }
        .audit-empty-state h5 {
            font-size: 16px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .audit-empty-state p {
            font-size: 13.5px;
            color: #64748b;
            margin: 0;
            max-width: 360px;
            margin-left: auto;
            margin-right: auto;
        }

        /* --- Summary Cards --- */
        .audit-summary-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 18px;
            background: #fff;
            height: 100%;
            transition: box-shadow .2s ease, transform .2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }
        .audit-summary-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,.07);
            transform: translateY(-2px);
        }
        .audit-summary-card .summary-title {
            font-size: 12.5px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        .audit-summary-card .summary-total {
            font-size: 26px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1;
            margin-bottom: 8px;
        }
        .audit-summary-card .summary-meta {
            font-size: 12px;
            color: #94a3b8;
            display: flex;
            gap: 6px;
        }
        .audit-summary-card .summary-meta span {
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
        .audit-summary-card .summary-meta .meta-insert { color: #059669; }
        .audit-summary-card .summary-meta .meta-update { color: #d97706; }
        .audit-summary-card .summary-meta .meta-delete { color: #dc2626; }

        /* --- Table Container --- */
        .audit-table-container {
            position: relative;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 1px 2px rgba(0,0,0,.06);
        }

        /* --- Table --- */
        .audit-table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .audit-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
        }
        .audit-table thead {
            position: sticky;
            top: 0;
            z-index: 5;
        }
        .audit-table thead th {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            color: #475569;
            font-weight: 600;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: 12px 14px;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
            user-select: none;
        }
        .audit-table tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
            transition: background .15s ease;
        }
        .audit-table tbody tr:nth-child(even) {
            background: #fafbfd;
        }
        .audit-table tbody tr:hover {
            background: #eef2ff !important;
        }
        .audit-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* --- Action Badges --- */
        .audit-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            letter-spacing: .02em;
            white-space: nowrap;
        }
        .audit-badge-insert {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .audit-badge-update {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .audit-badge-delete {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .audit-badge-default {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        /* --- View Button --- */
        .audit-view-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            color: #4f46e5;
            border: 1px solid #c7d2fe;
            background: #eef2ff;
            cursor: pointer;
            transition: all .2s ease;
        }
        .audit-view-btn:hover {
            background: #4f46e5;
            color: #fff;
            border-color: #4f46e5;
            box-shadow: 0 2px 4px rgba(79,70,229,.3);
        }
        .audit-view-btn:focus-visible {
            outline: 2px solid #4f46e5;
            outline-offset: 2px;
        }

        /* --- Pagination --- */
        .audit-pagination-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-top: 1px solid #f1f5f9;
            background: #fafbfd;
            flex-wrap: wrap;
            gap: 10px;
        }
        .audit-pagination-info {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }
        .audit-pagination-info strong {
            color: #334155;
        }
        .audit-pagination {
            display: flex;
            align-items: center;
            gap: 4px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .audit-page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 8px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
            background: #fff;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all .2s ease;
        }
        .audit-page-btn:hover:not(:disabled):not(.active) {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #334155;
        }
        .audit-page-btn.active {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 2px 4px rgba(79,70,229,.3);
        }
        .audit-page-btn:disabled {
            opacity: .45;
            cursor: not-allowed;
        }
        .audit-page-btn:focus-visible {
            outline: 2px solid #4f46e5;
            outline-offset: 2px;
        }
        .audit-page-ellipsis {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            font-size: 13px;
            color: #94a3b8;
        }

        /* --- Modal Enhancements --- */
        .modal-content {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.15);
        }
        .modal-header {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #fff;
            border: none;
            padding: 18px 24px;
        }
        .modal-header .modal-title {
            font-size: 16px;
            font-weight: 600;
        }
        .modal-header .close {
            color: #fff;
            opacity: .8;
            text-shadow: none;
        }
        .modal-header .close:hover {
            opacity: 1;
        }
        .modal-body {
            padding: 24px;
        }
        .audit-detail-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .03em;
            margin-bottom: 4px;
        }
        .audit-detail-value {
            font-size: 14px;
            color: #1e293b;
            font-weight: 500;
        }
        .audit-json-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px 16px;
            max-height: 380px;
            overflow: auto;
            white-space: pre-wrap;
            word-break: break-word;
            font-size: 12px;
            font-family: 'SF Mono', 'Fira Code', 'Fira Mono', Menlo, Consolas, monospace;
            color: #334155;
            line-height: 1.6;
        }
        .audit-json-section h6 {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .audit-json-section h6 i {
            color: #6366f1;
        }

        /* --- Responsive --- */
        @media (max-width: 991.98px) {
            .audit-filter-bar .col-lg-2,
            .audit-filter-bar .col-lg-4,
            .audit-filter-bar .col-lg-8 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }
        @media (max-width: 767.98px) {
            .audit-page-header .audit-header-text h4 {
                font-size: 18px;
            }
            .audit-filter-card {
                padding: 16px;
            }
            .audit-filter-bar .col-lg-2,
            .audit-filter-bar .col-lg-4,
            .audit-filter-bar .col-lg-8 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            .audit-filter-bar .filter-actions {
                flex-direction: column;
            }
            .audit-filter-bar .filter-actions .btn {
                width: 100%;
            }
            .audit-pagination-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            .audit-table thead th {
                padding: 10px;
                font-size: 10.5px;
            }
            .audit-table tbody td {
                padding: 8px 10px;
                font-size: 12px;
            }
        }
        @media (max-width: 575.98px) {
            .audit-page-header .audit-header-icon {
                width: 36px;
                height: 36px;
                font-size: 16px;
                border-radius: 10px;
            }
            .audit-page-header .audit-header-text h4 {
                font-size: 16px;
            }
            .audit-summary-card .summary-total {
                font-size: 22px;
            }
        }

        /* --- Accessibility --- */
        :focus-visible {
            outline: 2px solid #4f46e5;
            outline-offset: 2px;
        }
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0,0,0,0);
            white-space: nowrap;
            border: 0;
        }

        /* --- Print --- */
        @media print {
            .audit-filter-card,
            .audit-pagination-bar,
            .audit-loading-overlay,
            .audit-view-btn {
                display: none !important;
            }
            .audit-table-container {
                box-shadow: none;
                border: 1px solid #ccc;
            }
        }
    </style>
</head>
<body data-sidebar="dark">
<?php include($IncludesPath . "PreLoader.php"); ?>
<div id="layout-wrapper">
    <?php
    include($IncludesPath . "topbar.php");
    include($IncludesPath . "sidebar.php");
    ?>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <!-- Page Header -->
                <div class="audit-page-header">
                    <div class="audit-header-row">
                        <div class="audit-header-icon" aria-hidden="true">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="audit-header-text">
                            <h4>Audit History</h4>
                            <p>Track and review all system changes with detailed audit trails across modules and users.</p>
                        </div>
                    </div>
                </div>

                <!-- Filter Card -->
                <div class="audit-filter-card" role="search" aria-label="Audit filters">
                    <div class="filter-header">
                        <i class="fas fa-filter" aria-hidden="true"></i>
                        <h6>Filter Records</h6>
                    </div>
                    <div class="row audit-filter-bar g-3 align-items-end">
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <label for="drpClientID">Client</label>
                            <select class="form-control select2" id="drpClientID" aria-label="Select client" <?php echo $AccessType != 1 ? 'disabled="disabled"' : ''; ?>>
                                <?php if ($AccessType == 1) { ?>
                                    <option value="">Select Client</option>
                                <?php } ?>
                                <?php while ($RowClient = $StmtClients->fetch_array()) { ?>
                                    <option value="<?php echo $RowClient['ClientAccountID']; ?>" <?php echo $RowClient['ClientAccountID'] == $SessionClientID ? 'selected="selected"' : ''; ?>>
                                        <?php echo $RowClient['ClientName']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <label for="txtStartDate">Start Date</label>
                            <input type="date" id="txtStartDate" class="form-control" aria-label="Start date">
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <label for="txtEndDate">End Date</label>
                            <input type="date" id="txtEndDate" class="form-control" aria-label="End date">
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <label for="drpUser">User</label>
                            <select id="drpUser" class="form-control" aria-label="Select user">
                                <option value="">All Users</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <label for="drpModule">Module</label>
                            <select id="drpModule" class="form-control" aria-label="Select module">
                                <option value="">All Modules</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <label for="drpActionType">Action Type</label>
                            <select id="drpActionType" class="form-control" aria-label="Select action type">
                                <option value="">All Actions</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label for="txtSearchKey">Search</label>
                            <input type="text" id="txtSearchKey" class="form-control" placeholder="Reference, record, path..." aria-label="Search audit records" aria-describedby="searchHelpBlock">
                        </div>
                        <div class="col-lg-9 col-md-6">
                            <div class="filter-actions">
                                <button type="button" class="btn btn-primary" id="btnSearch" aria-label="Search records" style="margin-bottom: 0px;">
                                    <i class="fas fa-search mr-1" aria-hidden="true"></i> Search
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btnReset" aria-label="Reset filters">
                                    <i class="fas fa-redo mr-1" aria-hidden="true"></i> Reset
                                </button>
                            </div>
                        </div>
                        <div class="col-12" id="dateValidationMsg" style="display:none;" role="alert" aria-live="polite">
                            <div class="alert audit-alert audit-alert-danger">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                <span class="audit-validation-text"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data List Container -->
                <div id="divDataListWrapper" class="audit-table-container" style="position:relative;">
                    <div id="auditLoadingOverlay" class="audit-loading-overlay" role="status" aria-live="polite">
                        <div class="audit-loading-spinner" aria-hidden="true"></div>
                        <span class="audit-loading-text">Loading audit records...</span>
                    </div>
                    <div id="divDataList"></div>
                </div>

            </div>
        </div>

        <?php include($IncludesPath . "footer.php"); ?>
    </div>
</div>

<!-- Audit Detail Modal -->
<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-labelledby="auditDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="auditDetailModalLabel" style="color:white">
                    <i class="fas fa-file-alt mr-2" aria-hidden="true"></i> Audit Change Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="auditDetailBody"></div>
        </div>
    </div>
</div>

<?php include($IncludesPath . "vendor-scripts.php"); ?>
<script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>
<?php include($IncludesPath . "NewScripts.php"); ?>

<script>
    $(".select2").select2();

    /* ============================================
       AUDIT HISTORY - UI/UX ENHANCED LOGIC
       All original business logic preserved.
       ============================================ */

    function getTodayStr() {
        var now = new Date();
        var y = now.getFullYear();
        var m = String(now.getMonth() + 1).padStart(2, '0');
        var d = String(now.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }

    function showDateValidation(msg) {
        var el = $("#dateValidationMsg");
        el.find(".audit-validation-text").text(msg);
        el.show();
    }

    function clearDateValidation() {
        $("#dateValidationMsg").hide().find(".audit-validation-text").text("");
    }

    function validateDates() {
        var startVal = $("#txtStartDate").val();
        var endVal = $("#txtEndDate").val();

        if (!startVal || !endVal) {
            clearDateValidation();
            return true;
        }

        var startDate = new Date(startVal + "T00:00:00");
        var endDate = new Date(endVal + "T00:00:00");

        if (endDate < startDate) {
            showDateValidation("End date cannot be earlier than start date.");
            return false;
        }

        var diffMs = endDate.getTime() - startDate.getTime();
        var diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));

        if (diffDays > 30) {
            showDateValidation("Date range cannot exceed 30 days.");
            return false;
        }

        clearDateValidation();
        return true;
    }

    function getFilters() {
        return {
            ClientID: $("#drpClientID").val(),
            StartDate: $("#txtStartDate").val(),
            EndDate: $("#txtEndDate").val(),
            ActionBy: $("#drpUser").val(),
            ModuleName: $("#drpModule").val(),
            ActionType: $("#drpActionType").val(),
            SearchKey: $("#txtSearchKey").val()
        };
    }

    function escapeHtml(value) {
        return $("<div>").text(value === null || value === undefined ? "" : value).html();
    }

    function populateFilters(filters) {
        if (!filters) {
            return;
        }

        if (filters.Users) {
            var currentUser = $("#drpUser").val();
            var userOptions = '<option value="">All Users</option>';
            $.each(filters.Users, function(index, item) {
                userOptions += '<option value="' + escapeHtml(item.UserID) + '">' + escapeHtml(item.UserName) + '</option>';
            });
            $("#drpUser").html(userOptions).val(currentUser);
        }

        if (filters.Modules) {
            var currentModule = $("#drpModule").val();
            var moduleOptions = '<option value="">All Modules</option>';
            $.each(filters.Modules, function(index, item) {
                moduleOptions += '<option value="' + escapeHtml(item.ModuleName) + '">' + escapeHtml(item.ModuleName) + '</option>';
            });
            $("#drpModule").html(moduleOptions).val(currentModule);
        }

        if (filters.ActionTypes) {
            var currentAction = $("#drpActionType").val();
            var actionOptions = '<option value="">All Actions</option>';
            $.each(filters.ActionTypes, function(index, item) {
                actionOptions += '<option value="' + escapeHtml(item.ActionType) + '">' + escapeHtml(item.ActionType) + '</option>';
            });
            $("#drpActionType").html(actionOptions).val(currentAction);
        }
    }

    function showLoading() {
        $("#auditLoadingOverlay").addClass("active");
    }

    function hideLoading() {
        $("#auditLoadingOverlay").removeClass("active");
    }

    function showEmptyState(message) {
        return '<div class="audit-empty-state" role="status">' +
            '<div class="audit-empty-icon"><i class="fas fa-search" aria-hidden="true"></i></div>' +
            '<h5>No Audit Records Found</h5>' +
            '<p>' + escapeHtml(message || 'Try adjusting your filters or date range to find the records you\'re looking for.') + '</p>' +
            '</div>';
    }

    function showAlert(type, message) {
        var iconClass = type === 'warning' ? 'fas fa-exclamation-triangle' :
                        type === 'danger' ? 'fas fa-exclamation-circle' :
                        'fas fa-info-circle';
        var alertClass = 'audit-alert audit-alert-' + type;
        return '<div class="' + alertClass + '" role="alert">' +
            '<i class="' + iconClass + '" aria-hidden="true"></i>' +
            '<span>' + escapeHtml(message) + '</span>' +
            '</div>';
    }

    function loadHistory(pageNo, loadAll) {
        if (!validateDates()) {
            return;
        }

        var requestData = getFilters();
        requestData.Mode = "list";
        requestData.PageNo = pageNo || 1;
        requestData.LoadAll = loadAll || 0;

        if (!requestData.ClientID) {
            $("#divDataList").html(showAlert('warning', 'Select a client to load audit history.'));
            return;
        }

        showLoading();

        $.ajax({
            url: 'Ajax/AjaxAuditHistory.php',
            type: 'POST',
            dataType: 'json',
            data: requestData,
            success: function(response) {
                hideLoading();
                if (response.error) {
                    $("#divDataList").html(showAlert('danger', response.error));
                    return;
                }
                populateFilters(response.filters);

                var html = response.html;
                if (!html || html.trim() === '') {
                    $("#divDataList").html(showEmptyState());
                } else {
                    $("#divDataList").html(html);
                }

                applyActionBadges();
                applyPaginationClasses();
                enhanceSummaryCards();
            },
            error: function() {
                hideLoading();
                $("#divDataList").html(showAlert('danger', 'An error occurred while loading audit history. Please try again.'));
            }
        });
    }

    function loadDetail(auditId) {
        $.ajax({
            url: 'Ajax/AjaxAuditHistory.php',
            type: 'POST',
            dataType: 'json',
            data: {
                Mode: 'detail',
                AuditID: auditId,
                ClientID: $("#drpClientID").val()
            },
            success: function(response) {
                if (response.error) {
                    $("#auditDetailBody").html(showAlert('danger', response.error));
                } else {
                    $("#auditDetailBody").html(response.html);
                    enhanceDetailModal();
                }
                $("#auditDetailModal").modal('show');
            }
        });
    }

    function applyActionBadges() {
        $(".audit-badge").each(function() {
            var text = $(this).text().trim().toLowerCase();
            $(this).removeClass('badge badge-soft-primary');
            if (text === 'insert' || text === 'create' || text === 'add') {
                $(this).addClass('audit-badge audit-badge-insert');
            } else if (text === 'update' || text === 'edit' || text === 'modify') {
                $(this).addClass('audit-badge audit-badge-update');
            } else if (text === 'delete' || text === 'remove') {
                $(this).addClass('audit-badge audit-badge-delete');
            } else {
                $(this).addClass('audit-badge audit-badge-default');
            }
        });
    }

    function applyPaginationClasses() {
        $(".pagination").addClass("audit-pagination");
        $(".page-item .page-link").addClass("audit-page-btn");
        $(".page-item.disabled .page-link").attr("disabled", "disabled");
    }

    function enhanceSummaryCards() {
        $(".audit-summary-card .summary-meta").each(function() {
            var text = $(this).text();
            var parts = text.split('|');
            if (parts.length === 3) {
                var html = '<span class="meta-insert"><i class="fas fa-plus-circle" aria-hidden="true"></i> I: ' + $.trim(parts[0].replace('I:', '')) + '</span>';
                html += '<span class="meta-update"><i class="fas fa-edit" aria-hidden="true"></i> U: ' + $.trim(parts[1].replace('U:', '')) + '</span>';
                html += '<span class="meta-delete"><i class="fas fa-trash-alt" aria-hidden="true"></i> D: ' + $.trim(parts[2].replace('D:', '')) + '</span>';
                $(this).html(html);
            }
        });
    }

    function enhanceDetailModal() {
        var $body = $("#auditDetailBody");

        $body.find(".col-md-3, .col-md-6").each(function() {
            var $col = $(this);
            var $strong = $col.find("strong");
            if ($strong.length) {
                var label = $strong.text().replace(':', '').trim();
                var value = $col.text().replace($strong.text(), '').trim();
                $col.html('<div class="mb-2"><div class="audit-detail-label">' + escapeHtml(label) + '</div>' +
                    '<div class="audit-detail-value">' + escapeHtml(value) + '</div></div>');
            }
        });

        $body.find("h6").each(function() {
            var text = $(this).text().trim();
            $(this).html('<i class="fas fa-code" aria-hidden="true"></i> ' + escapeHtml(text));
            $(this).closest(".col-md-12, .col-md-6").addClass("audit-json-section");
        });
    }

    // --- Initialize ---
    $(document).ready(function() {
        var today = getTodayStr();
        $("#txtStartDate").val(today);
        $("#txtEndDate").val(today);
        loadHistory(1, 0);
    });

    // --- Event Handlers (all original logic preserved) ---
    $(document).on("change", "#txtStartDate, #txtEndDate", function() {
        validateDates();
    });

    $(document).on("click", "#btnSearch", function() {
        if (!validateDates()) {
            return;
        }
        loadHistory(1, 0);
    });

    $(document).on("click", "#btnReset", function() {
        var today = getTodayStr();
        $("#txtStartDate").val(today);
        $("#txtEndDate").val(today);
        $("#drpUser").val('');
        $("#drpModule").val('');
        $("#drpActionType").val('');
        $("#txtSearchKey").val('');
        clearDateValidation();
        loadHistory(1, 0);
    });

    $(document).on("click", "#btnLoadAll", function() {
        loadHistory(1, 1);
    });

    $(document).on("click", ".btnAuditPage", function() {
        loadHistory($(this).data("pageno"), 0);
    });

    $(document).on("click", ".btnAuditDetail", function() {
        loadDetail($(this).data("auditid"));
    });

    $(document).on("keypress", "#txtSearchKey", function(event) {
        if (event.which === 13) {
            if (!validateDates()) {
                return;
            }
            loadHistory(1, 0);
        }
    });
</script>
</body>
</html>
