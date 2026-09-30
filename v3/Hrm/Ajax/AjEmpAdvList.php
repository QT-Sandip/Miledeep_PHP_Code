<?php
include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";

$DeptID    = isset($_POST['DeptID']) ? trim($_POST['DeptID']) : '';
$SubDeptID = isset($_POST['SubDeptID']) ? trim($_POST['SubDeptID']) : '';
$Month     = isset($_POST['Month']) ? trim($_POST['Month']) : '';
$Year      = isset($_POST['Year']) ? trim($_POST['Year']) : '';

if (empty($Month) || empty($Year)) {
    $Month = date('m');
    $Year  = date('Y');
}

$request = $_REQUEST;
$columns = ['BioMetricID', 'EmpName', 'DepartmentName'];

$limit  = isset($_POST['length']) ? (int) $_POST['length'] : 20;
$limit  = $limit < 0 ? 20 : $limit;
$offset = max(0, isset($_POST['start']) ? (int) $_POST['start'] : 0);

$firstdate = $Year . '-' . str_pad($Month, 2, '0', STR_PAD_LEFT) . '-01';

$orderColumnIndex = isset($request['order'][0]['column']) ? intval($request['order'][0]['column']) : 0;
$searchValue = isset($request['search']['value']) ? $iconn->real_escape_string($request['search']['value']) : '';

$searchQueryParts = [];

if (!empty($SubDeptID)) {
    $searchQueryParts[] = "ED.SubDeptID='$SubDeptID'";
} else if (!empty($DeptID)) {
    $searchQueryParts[] = "ED.DeptID='$DeptID'";
}

if (!empty($searchValue)) {
    $searchQueryParts[] = "(ED.BioMetricID LIKE '%" . $searchValue . "%' OR ED.EmpName LIKE '%" . $searchValue . "%' OR HD.DepartmentName LIKE '%" . $searchValue . "%')";
}

$searchQuery = count($searchQueryParts) > 0 ? ' AND ' . implode(' AND ', $searchQueryParts) : '';

$orderBy = "";
if (isset($columns[$orderColumnIndex])) {
    $orderCol = $columns[$orderColumnIndex];
    $orderDir = isset($request['order'][0]['dir']) ? $request['order'][0]['dir'] : 'ASC';
    $orderBy = " ORDER BY `" . ($orderCol === 'DepartmentName' ? 'HD' : 'ED') . "`.`" . $orderCol . "` " . $orderDir;
}

$totalRecords = $iconn->query("SELECT COUNT(*) as count FROM HrmEmployeeDetails")->fetch_assoc()['count'];

$totalFiltered = $iconn->query("
    SELECT COUNT(DISTINCT ED.EmpID) AS count
    FROM HrmEmployeeDetails AS ED
    LEFT JOIN HrmDepartments AS HD
        ON HD.DeptID = ED.DeptID
    LEFT JOIN HrmCashAdvance AS HCA
        ON HCA.EmpID = ED.EmpID
        AND HCA.AdvDate = '$firstdate'
    WHERE 1=1
    $searchQuery
")->fetch_assoc()['count'];

$data = [];
$sql  = "SELECT ED.EmpName,
    HD.DepartmentName,
    ED.BioMetricID,
    ED.EmpID,
    COALESCE(HCA.Remarks, '') AS Remarks
    FROM HrmEmployeeDetails AS ED
    LEFT JOIN HrmDepartments AS HD
        ON HD.DeptID = ED.DeptID
    LEFT JOIN HrmCashAdvance AS HCA
        ON HCA.EmpID = ED.EmpID
        AND HCA.AdvDate = '$firstdate'
    WHERE 1=1
    $searchQuery
    $orderBy
    LIMIT $offset, $limit";

$result = $iconn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($RowLeaveList = $result->fetch_assoc()) {
        $EmpID          = $RowLeaveList['EmpID'];
        $Deduction      = $RowLeaveList['Deduction'];
        $CurrentPending = $RowLeaveList['CurrentPending'];
        $DepartmentName = $RowLeaveList['DepartmentName'];
        $Remarks        = $RowLeaveList['Remarks'];

        $singlerow                   = array();
        $singlerow['BioMetricID']    = $RowLeaveList['BioMetricID'];
        $singlerow['EmpName']        = $RowLeaveList['EmpName'];
        $singlerow['DepartmentName'] = $DepartmentName;
        $singlerow['Remarks']        = '<input class="form-control" type="text" id="Remarks' . $EmpID . '" name="Remarks" placeholder="Enter Reason" value="' . htmlspecialchars($Remarks) . '" maxlength="20" required>';
        $singlerow['Tools']          = "<button id='BtnEditManualAttend' value='" . $EmpID . "' align='Center' class='btn btn-success btn-sm btn-flat'><i class='fa fa-plus'></i></button>";
        $data[] = $singlerow;
    }
}
echo json_encode([
    "draw"            => isset($request['draw']) ? intval($request['draw']) : 1,
    "recordsTotal"    => intval($totalRecords),
    "recordsFiltered" => intval($totalFiltered),
    "data"            => $data,
]);
exit;
?>
