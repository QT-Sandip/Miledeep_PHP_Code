<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$request = $_REQUEST;
$columns = ['BioMetricID', 'EmpName', 'EmpCategory', 'ArrearAmount', 'ShipmentCharges','Commission','SundayIncentive','OThours'];

$limit = intval($request['length']);
$offset = intval($request['start']);
$year = intval($request['year']);
$month = intval($request['month']);
$EmpCategory = $request['empcategory'];
$WorkerSubCategoryID=intval($request['workersubcategory']);
$DeptID = intval($request['dept']);
$SubDeptID = intval($request['subdept']);
$searchValue = $iconn->real_escape_string($request['search']['value']);
$MonthSegment = intval($request['monthsegment']);

$searchQueryParts = [];

if (!empty($searchValue)) {
    $searchQueryParts[] = "($columns[0] LIKE '%$searchValue%' OR $columns[1] LIKE '%$searchValue%' OR $columns[2] LIKE '%$searchValue%')";
}

if (!empty($EmpCategory)) {
    $searchQueryParts[] = "HRD.EmpCategory = '$EmpCategory'";
}

if (!empty($WorkerSubCategoryID)) {
    $searchQueryParts[] = "HRD.WorkerSubCategory = $WorkerSubCategoryID";
}

if (!empty($DeptID)) {
    $searchQueryParts[] = "HRD.DeptID = $DeptID";
}

if (!empty($SubDeptID)) {
    $searchQueryParts[] = "HRD.SubDeptID = $SubDeptID";
}

// Combine conditions with AND
$searchQuery = count($searchQueryParts) > 0 ? 'WHERE ' . implode(' AND ', $searchQueryParts) : '';

// $searchQuery = $searchValue ?
//     "WHERE $columns[0] LIKE '%$searchValue%' OR $columns[1] LIKE '%$searchValue%' OR $columns[2] LIKE '%$searchValue%'" 
//     : '';

// Total records
$totalRecords = $iconn->query("SELECT COUNT(*) as count FROM HrmEmployeeDetails")->fetch_assoc()['count'];
$totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM
            HrmEmployeeDetails HRD
        LEFT JOIN HrmEmpAdditionDetails Additions ON
            HRD.EmpID = Additions.EmpID 
            AND Additions.year = $year 
            AND Additions.month = $month
            AND Additions.MonthSegment=$MonthSegment
        LEFT JOIN EmpWorkerSubCategories HRWS ON
            HRWS.CategoryID = HRD.WorkerSubCategory $searchQuery
        ORDER BY
            BioMetricID")->fetch_assoc()['count'];

// Fetch data
$data = [];
$sql = "SELECT
            HRD.EmpID,
            HRD.BioMetricID,
            HRD.EmpName,
            HRD.EmpCategory,
            IFNULL(Additions.ArrearAmount, 0) AS ArrearAmount,
            IFNULL(Additions.ShipmentCharges, 0) AS ShipmentCharges,
            IFNULL(Additions.Commission, 0) AS Commission,
            IFNULL(Additions.SundayIncentive, 0) AS SundayIncentive,
            IFNULL(Additions.OThours, 0) AS OThours
        FROM
            HrmEmployeeDetails HRD
        LEFT JOIN HrmEmpAdditionDetails Additions ON
            HRD.EmpID = Additions.EmpID 
            AND Additions.year = $year 
            AND Additions.month = $month
            AND Additions.MonthSegment=$MonthSegment
        LEFT JOIN EmpWorkerSubCategories HRWS ON
            HRWS.CategoryID = HRD.WorkerSubCategory $searchQuery
        ORDER BY
            BioMetricID
        ASC
        LIMIT $offset, $limit";
        error_log($sql);
$result = $iconn->query($sql);

// Only proceed if query is successful and has rows
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Editable fields
        $row['BioMetricID'] = '<span data-pk="' . $row['EmpID'] . '" data-name="EmpName" data-url="update.php">' . $row['BioMetricID'] . '</span>';
        $row['EmpName'] = '<span data-pk="' . $row['EmpID'] . '" data-name="EmpName" data-url="update.php">' . $row['EmpName'] . '</span>';
        $row['EmpCategory'] = '<span data-pk="' . $row['EmpCategory'] . '" data-name="cost" data-url="update.php">' . $row['EmpCategory'] . '</span>';
        $row['ArrearAmount'] = '<span data-pk="' . $row['EmpID'] . '" data-name="EmpName" data-url="update.php">' . $row['ArrearAmount'] . '</span>';
        $row['ShipmentCharges'] = '<span data-pk="' . $row['EmpID'] . '" data-name="EmpName" data-url="update.php">' . $row['ShipmentCharges'] . '</span>';
        $row['Commission'] = '<span data-pk="' . $row['EmpID'] . '" data-name="EmpName" data-url="update.php">' . $row['Commission'] . '</span>';
        $row['SundayIncentive'] = '<span data-pk="' . $row['EmpID'] . '" data-name="EmpName" data-url="update.php">' . $row['SundayIncentive'] . '</span>';
        $row['OThours'] = '<span data-pk="' . $row['EmpID'] . '" data-name="EmpName" data-url="update.php">' . $row['OThours'] . '</span>';

        $row['DT_RowAttr'] = [
            'data-empid' => $row['EmpID'] // Rename 'id' to 'empid' if needed
        ];
        // Action button
        $row['edit_button'] = '<button class="btn btn-sm btn-primary edit-btn" data-id="' . $row['EmpID'] . '">Edit</button>';

        $data[] = $row;
    }
}
// Output JSON in DataTables format
echo json_encode([
    "draw" => intval($request['draw']),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalFiltered,
    "data" => $data // could be empty, and that's okay
]);
