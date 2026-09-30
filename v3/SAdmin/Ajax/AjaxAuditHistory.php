<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_PURCHASE_AUDIT_HISTORY;
$mode = trim((string) (isset($_POST['Mode']) ? $_POST['Mode'] : 'list'));
$clientId = trim((string) (isset($_POST['ClientID']) ? $_POST['ClientID'] : ''));

if ($clientId === '') {
    $clientId = isset($_SESSION['ClientID']) ? trim((string) $_SESSION['ClientID']) : '';
}

if ($mode === 'detail') {
    $bodyContent = array(
        'ClientID' => $clientId,
        'AuditID' => trim((string) (isset($_POST['AuditID']) ? $_POST['AuditID'] : '')),
    );
    $resList = CallAPI($api, "GET", "", $bodyContent);
    renderDetailResponse($resList);
    exit;
}

$startDate = trim((string) (isset($_POST['StartDate']) ? $_POST['StartDate'] : ''));
$endDate = trim((string) (isset($_POST['EndDate']) ? $_POST['EndDate'] : ''));

if ($startDate !== '' && $endDate !== '') {
    $startDt = date_create($startDate);
    $endDt = date_create($endDate);

    if ($startDt && $endDt) {
        if ($endDt < $startDt) {
            $response = array('error' => 'End date cannot be earlier than start date.', 'filters' => null, 'html' => '');
            echo json_encode($response);
            exit;
        }

        $diff = date_diff($startDt, $endDt);
        if ((int) $diff->days > 30) {
            $response = array('error' => 'Date range cannot exceed 30 days.', 'filters' => null, 'html' => '');
            echo json_encode($response);
            exit;
        }
    }
}

$bodyContent = array(
    'ClientID' => $clientId,
    'StartDate' => $startDate,
    'EndDate' => $endDate,
    'ActionBy' => trim((string) (isset($_POST['ActionBy']) ? $_POST['ActionBy'] : '')),
    'ModuleName' => trim((string) (isset($_POST['ModuleName']) ? $_POST['ModuleName'] : '')),
    'ActionType' => trim((string) (isset($_POST['ActionType']) ? $_POST['ActionType'] : '')),
    'SearchKey' => trim((string) (isset($_POST['SearchKey']) ? $_POST['SearchKey'] : '')),
    'PageNo' => trim((string) (isset($_POST['PageNo']) ? $_POST['PageNo'] : '1')),
    'LoadAll' => trim((string) (isset($_POST['LoadAll']) ? $_POST['LoadAll'] : '0')),
);

$resList = CallAPI($api, "GET", "", $bodyContent);
renderListResponse($resList, (int) $bodyContent['PageNo']);

function renderListResponse($resList, $pageNo)
{
    $response = array(
        'error' => null,
        'filters' => null,
        'html' => '',
    );

    if (!isset($resList) || empty($resList)) {
        $response['error'] = 'Unable to load audit history';
        echo json_encode($response);
        return;
    }

    if (!empty($resList->Error)) {
        $response['error'] = $resList->Error;
        echo json_encode($response);
        return;
    }

    $response['filters'] = isset($resList->Filters) ? $resList->Filters : null;
    $summary = isset($resList->ModuleSummary) ? $resList->ModuleSummary : array();
    $list = isset($resList->Data) ? $resList->Data : array();
    $totalCount = isset($resList->TotalCount) ? $resList->TotalCount : 0;
    $pageCount = isset($resList->PageCount) ? $resList->PageCount : 1;

    ob_start();

    function renderActionBadge($actionType) {
        $action = strtolower(trim($actionType));
        if (in_array($action, ['insert', 'create', 'add'])) {
            return 'audit-badge audit-badge-insert';
        } elseif (in_array($action, ['update', 'edit', 'modify'])) {
            return 'audit-badge audit-badge-update';
        } elseif (in_array($action, ['delete', 'remove'])) {
            return 'audit-badge audit-badge-delete';
        }
        return 'audit-badge audit-badge-default';
    }

    function formatSummaryMeta($item) {
        $i = (int) (isset($item->InsertCount) ? $item->InsertCount : 0);
        $u = (int) (isset($item->UpdateCount) ? $item->UpdateCount : 0);
        $d = (int) (isset($item->DeleteCount) ? $item->DeleteCount : 0);
        return '<span class="meta-insert"><i class="fas fa-plus-circle" aria-hidden="true"></i> I: ' . $i . '</span>' .
               '<span class="meta-update"><i class="fas fa-edit" aria-hidden="true"></i> U: ' . $u . '</span>' .
               '<span class="meta-delete"><i class="fas fa-trash-alt" aria-hidden="true"></i> D: ' . $d . '</span>';
    }
    ?>
    <div class="row g-3 mb-4" style="padding: 18px 18px 0;">
        <?php if (!empty($summary)) { ?>
            <?php foreach ($summary as $item) { ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="audit-summary-card" role="region" aria-label="Module summary">
                        <div class="summary-title"><?php echo htmlspecialchars(isset($item->TableName) ? $item->TableName : '', ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="summary-total"><?php echo (int) (isset($item->TotalActions) ? $item->TotalActions : 0); ?></div>
                        <div class="summary-meta">
                            <?php echo formatSummaryMeta($item); ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="col-12">
                <div class="alert audit-alert audit-alert-info mb-0" role="status">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    <span>No module summary available for the selected filters.</span>
                </div>
            </div>
        <?php } ?>
    </div>

    <div class="audit-table-wrapper">
        <table class="audit-table" role="grid" aria-label="Audit history records">
            <thead>
                <tr role="row">
                    <th scope="col" role="columnheader">S.No</th>
                    <th scope="col" role="columnheader">Date Time</th>
                    <th scope="col" role="columnheader">User</th>
                    <th scope="col" role="columnheader">Module</th>
                    <th scope="col" role="columnheader">Action</th>
                    <th scope="col" role="columnheader">Record ID</th>
                    <th scope="col" role="columnheader">Reference</th>
                    <th scope="col" role="columnheader">Changed Fields</th>
                    <th scope="col" role="columnheader">Source</th>
                    <th scope="col" role="columnheader">Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($list)) { ?>
                    <?php foreach ($list as $item) { ?>
                        <tr role="row">
                            <td><?php echo (int) (isset($item->SNO) ? $item->SNO : 0); ?></td>
                            <td style="white-space:nowrap;"><?php echo htmlspecialchars(isset($item->ActionDateTime) ? $item->ActionDateTime : '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(isset($item->ActionByName) ? $item->ActionByName : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <div style="font-weight:500;"><?php echo htmlspecialchars(isset($item->TableName) ? $item->TableName : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                <small style="color:#94a3b8;font-size:11px;"><?php echo htmlspecialchars(isset($item->ModuleName) ? $item->ModuleName : '', ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td><span class="<?php echo renderActionBadge(htmlspecialchars(isset($item->ActionType) ? $item->ActionType : '', ENT_QUOTES, 'UTF-8')); ?>"><?php echo htmlspecialchars(isset($item->ActionType) ? $item->ActionType : '', ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td style="font-family:'SF Mono',Menlo,Consolas,monospace;font-size:12px;"><?php echo htmlspecialchars(isset($item->RecordID) ? $item->RecordID : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(isset($item->ReferenceNo) ? $item->ReferenceNo : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="text-align:center;font-weight:600;color:#4f46e5;"><?php echo (int) (isset($item->ChangedFieldCount) ? $item->ChangedFieldCount : 0); ?></td>
                            <td>
                                <div style="font-weight:500;"><?php echo htmlspecialchars(isset($item->SourceType) ? $item->SourceType : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                <small style="color:#94a3b8;font-size:11px;word-break:break-all;"><?php echo htmlspecialchars(isset($item->SourcePath) ? $item->SourcePath : '-', ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td style="text-align:center;">
                                <button type="button" class="audit-view-btn btnAuditDetail" aria-label="View audit details for record" data-auditid="<?php echo (int) (isset($item->AuditID) ? $item->AuditID : 0); ?>">
                                    <i class="fas fa-eye" aria-hidden="true"></i> View
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="10">
                            <div class="audit-empty-state" role="status">
                                <div class="audit-empty-icon"><i class="fas fa-clipboard-list" aria-hidden="true"></i></div>
                                <h5>No Audit Records Found</h5>
                                <p>No records match your current filter criteria. Try adjusting the date range or removing some filters.</p>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="audit-pagination-bar" role="navigation" aria-label="Audit history pagination">
        <div class="audit-pagination-info">
            Showing <strong><?php echo count($list); ?></strong> of <strong><?php echo (int)$totalCount; ?></strong> entries
        </div>

        <ul class="audit-pagination" role="list">

            <!-- Previous -->
            <li class="page-item <?php echo ($pageNo <= 1) ? 'disabled' : ''; ?>">
                <button type="button"
                        class="audit-page-btn btnAuditPage"
                        data-pageno="<?php echo max(1, $pageNo - 1); ?>"
                        aria-label="Go to previous page"
                        <?php echo ($pageNo <= 1) ? 'disabled' : ''; ?>>
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </button>
            </li>

            <?php
            $totalPages = (int)$pageCount;
            $current = (int)$pageNo;

            $pages = [];

            // First 2 pages
            $pages[] = 1;
            if ($totalPages >= 2) $pages[] = 2;

            // Current -1, Current, Current +1
            for ($i = $current - 1; $i <= $current + 1; $i++) {
                if ($i > 2 && $i < $totalPages - 1) {
                    $pages[] = $i;
                }
            }

            // Last 2 pages
            if ($totalPages > 3) $pages[] = $totalPages - 1;
            if ($totalPages > 2) $pages[] = $totalPages;

            $pages = array_unique($pages);
            sort($pages);

            $last = 0;
            foreach ($pages as $page) {

                if ($last && ($page - $last) > 1) {
                    ?>
                    <li class="page-item" aria-hidden="true">
                        <span class="audit-page-ellipsis">...</span>
                    </li>
                    <?php
                }
                ?>

                <li class="page-item">
                    <button type="button"
                            class="audit-page-btn <?php echo ($page == $current) ? 'active' : ''; ?> btnAuditPage"
                            data-pageno="<?php echo $page; ?>"
                            aria-label="Go to page <?php echo $page; ?>"
                            <?php echo ($page == $current) ? 'aria-current="page"' : ''; ?>>
                        <?php echo $page; ?>
                    </button>
                </li>

                <?php
                $last = $page;
            }
            ?>

            <!-- Next -->
            <li class="page-item <?php echo ($pageNo >= $totalPages) ? 'disabled' : ''; ?>">
                <button type="button"
                        class="audit-page-btn btnAuditPage"
                        data-pageno="<?php echo min($totalPages, $pageNo + 1); ?>"
                        aria-label="Go to next page"
                        <?php echo ($pageNo >= $totalPages) ? 'disabled' : ''; ?>>
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </button>
            </li>

        </ul>
    </div>
    <?php

    $response['html'] = ob_get_clean();
    echo json_encode($response);
}

function renderDetailResponse($resList)
{
    $response = array(
        'error' => null,
        'html' => '',
    );

    if (!isset($resList) || empty($resList)) {
        $response['error'] = 'Unable to load audit details';
        echo json_encode($response);
        return;
    }

    if (!empty($resList->Error)) {
        $response['error'] = $resList->Error;
        echo json_encode($response);
        return;
    }

    $detail = isset($resList->Data) ? $resList->Data : null;
    if (!$detail) {
        $response['error'] = 'Audit detail not found';
        echo json_encode($response);
        return;
    }

    $changedFields = formatJsonBlock(isset($detail->ChangedFields) ? $detail->ChangedFields : null);
    $oldData = formatJsonBlock(isset($detail->OldData) ? $detail->OldData : null);
    $newData = formatJsonBlock(isset($detail->NewData) ? $detail->NewData : null);

    ob_start();
    ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="mb-2">
                <div class="audit-detail-label">Audit ID</div>
                <div class="audit-detail-value"><?php echo htmlspecialchars(isset($detail->AuditID) ? $detail->AuditID : '', ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="mb-2">
                <div class="audit-detail-label">Date Time</div>
                <div class="audit-detail-value"><?php echo htmlspecialchars(isset($detail->ActionDateTime) ? $detail->ActionDateTime : '', ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="mb-2">
                <div class="audit-detail-label">User</div>
                <div class="audit-detail-value"><?php echo htmlspecialchars(isset($detail->ActionByName) ? $detail->ActionByName : '-', ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="mb-2">
                <div class="audit-detail-label">Action</div>
                <div class="audit-detail-value"><?php echo htmlspecialchars(isset($detail->ActionType) ? $detail->ActionType : '', ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="mb-2">
                <div class="audit-detail-label">Client</div>
                <div class="audit-detail-value"><?php echo htmlspecialchars(isset($detail->ClientID) ? $detail->ClientID : '-', ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="mb-2">
                <div class="audit-detail-label">Transaction</div>
                <div class="audit-detail-value"><?php echo htmlspecialchars(isset($detail->TransactionID) ? $detail->TransactionID : '-', ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="mb-2">
                <div class="audit-detail-label">Module</div>
                <div class="audit-detail-value"><?php echo htmlspecialchars(isset($detail->ModuleName) ? $detail->ModuleName : '', ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="mb-2">
                <div class="audit-detail-label">Table</div>
                <div class="audit-detail-value"><?php echo htmlspecialchars(isset($detail->TableName) ? $detail->TableName : '', ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="mb-2">
                <div class="audit-detail-label">Record ID</div>
                <div class="audit-detail-value" style="font-family:'SF Mono',Menlo,Consolas,monospace;font-size:13px;"><?php echo htmlspecialchars(isset($detail->RecordID) ? $detail->RecordID : '-', ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="mb-2">
                <div class="audit-detail-label">Reference</div>
                <div class="audit-detail-value"><?php echo htmlspecialchars(isset($detail->ReferenceNo) ? $detail->ReferenceNo : '-', ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-2">
                <div class="audit-detail-label">Source</div>
                <div class="audit-detail-value"><?php echo htmlspecialchars((isset($detail->SourceType) ? $detail->SourceType : '-') . ' / ' . (isset($detail->SourcePath) ? $detail->SourcePath : '-'), ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-2">
                <div class="audit-detail-label">IP / Browser</div>
                <div class="audit-detail-value" style="font-size:12.5px;"><?php echo htmlspecialchars((isset($detail->IPAddress) ? $detail->IPAddress : '-') . ' / ' . (isset($detail->BrowserInfo) ? $detail->BrowserInfo : '-'), ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>
        <div class="col-12">
            <div class="mb-2">
                <div class="audit-detail-label">Remarks</div>
                <div class="audit-detail-value"><?php echo htmlspecialchars(isset($detail->Remarks) ? $detail->Remarks : '-', ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-12 audit-json-section">
            <h6><i class="fas fa-code" aria-hidden="true"></i> Changed Fields</h6>
            <div class="audit-json-box"><?php echo htmlspecialchars($changedFields, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <div class="col-md-6 audit-json-section">
            <h6><i class="fas fa-code" aria-hidden="true"></i> Old Data</h6>
            <div class="audit-json-box"><?php echo htmlspecialchars($oldData, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <div class="col-md-6 audit-json-section">
            <h6><i class="fas fa-code" aria-hidden="true"></i> New Data</h6>
            <div class="audit-json-box"><?php echo htmlspecialchars($newData, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    </div>
    <?php

    $response['html'] = ob_get_clean();
    echo json_encode($response);
}

function formatJsonBlock($value)
{
    if ($value === null || $value === '') {
        return 'No data';
    }

    if (is_string($value)) {
        return $value;
    }

    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
