<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$request = $_REQUEST;
$columns = ['BioMetricID', 'EmpName', 'EmpCategory', 'PF', 'ESI', 'Advance','MessCharges','BusCharges','LateLoginFine','DessCodeDeductions','HelmetDeductions'];

$limit = intval($request['length']);
$offset = intval($request['start']);
$year = intval($request['year']);
$month = intval($request['month']);
$EmpCategory = $request['empcategory'];
$WorkerSubCategoryID=intval($request['workersubcategory']);
$DeptID = intval($request['dept']);
$SubDeptID = intval($request['subdept']);
$MonthSegment = intval($request['monthsegment']);

$searchValue = $iconn->real_escape_string($request['search']['value']);

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

// if (!empty($MonthSegment)) {
//     $searchQueryParts[] = "Deductions.MonthSegment = $MonthSegment";
// }

// Combine conditions with AND
$searchQuery = count($searchQueryParts) > 0 ? 'WHERE ' . implode(' AND ', $searchQueryParts) : '';

// $searchQuery = $searchValue ?
//     "WHERE $columns[0] LIKE '%$searchValue%' OR $columns[1] LIKE '%$searchValue%' OR $columns[2] LIKE '%$searchValue%'" 
//     : '';

// Total records
$totalRecords = $iconn->query("SELECT COUNT(*) as count FROM HrmEmployeeDetails")->fetch_assoc()['count'];
$totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM
            HrmEmployeeDetails HRD
        LEFT JOIN HrmEmpDeductionDetails Deductions ON
            HRD.EmpID = Deductions.EmpID 
            AND Deductions.year = $year 
            AND Deductions.month = $month 
            AND Deductions.MonthSegment=$MonthSegment
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
            IFNULL(Deductions.PF, 0) AS PF,
            IFNULL(Deductions.ESI, 0) AS ESI,
            IFNULL(Deductions.Advance, 0) AS Advance,
            IFNULL(Deductions.MessCharges, 0) AS MessCharges,
            IFNULL(Deductions.BusChargeDeductions, 0) AS BusCharges,
            IFNULL(Deductions.LateLoginFine, 0) AS LateLoginFine,
            IFNULL(Deductions.DessCodeDeductions, 0) AS DessCodeDeductions,
            IFNULL(Deductions.HelmetDeductions, 0) AS HelmetDeductions
        FROM
            HrmEmployeeDetails HRD
        LEFT JOIN HrmEmpDeductionDetails Deductions ON
            HRD.EmpID = Deductions.EmpID 
            AND Deductions.year = $year 
            AND Deductions.month = $month AND Deductions.MonthSegment=$MonthSegment
        LEFT JOIN EmpWorkerSubCategories HRWS ON
            HRWS.CategoryID = HRD.WorkerSubCategory $searchQuery
        ORDER BY
            BioMetricID
        ASC
        LIMIT $offset, $limit";
$result = $iconn->query($sql);

// Only proceed if query is successful and has rows
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Editable fields
        $row['BioMetricID'] = '<span data-pk="' . $row['EmpID'] . '" data-name="EmpName" data-url="update.php">' . $row['BioMetricID'] . '</span>';
        $row['EmpName'] = '<span data-pk="' . $row['EmpID'] . '" data-name="EmpName" data-url="update.php">' . $row['EmpName'] . '</span>';
        $row['EmpCategory'] = '<span data-pk="' . $row['EmpCategory'] . '" data-name="cost" data-url="update.php">' . $row['EmpCategory'] . '</span>';
        $row['PF'] = '<span data-pk="' . $row['EmpID'] . '" data-name="EmpName" data-url="update.php">' . $row['PF'] . '</span>';
        $row['ESI'] = '<span data-pk="' . $row['EmpID'] . '" data-name="EmpName" data-url="update.php">' . $row['ESI'] . '</span>';
        $row['MessCharges'] = '<span data-pk="' . $row['EmpID'] . '" data-name="EmpName" data-url="update.php">' . $row['MessCharges'] . '</span>';
        $row['BusChargeDeductions'] = '<span data-pk="' . $row['EmpID'] . '" data-name="EmpName" data-url="update.php">' . $row['BusCharges'] . '</span>';
        $row['Fine'] = '<span data-pk="' . $row['EmpID'] . '" data-name="EmpName" data-url="update.php">' . $row['LateLoginFine'] . '</span>';
        $row['DessCodeDeductions'] = '<span data-pk="' . $row['EmpID'] . '" data-name="EmpName" data-url="update.php">' . $row['DessCodeDeductions'] . '</span>';
        $row['HelmetDeductions'] = '<span data-pk="' . $row['EmpID'] . '" data-name="EmpName" data-url="update.php">' . $row['HelmetDeductions'] . '</span>';

        $row['DT_RowAttr'] = [
            'data-empid' => $row['EmpID'] // Rename 'id' to 'empid' if needed
        ];
        // Action button
        $row['edit_button'] = '<button class="btn btn-sm btn-primary edit-btn" data-id="' . $row['EmpID'] . '">Edit</button>';

        $data[] = $row;
    }
}
if($_SESSION['ClientConfigData']['pf_esi_auto_carry_frwd']){

    $currentMonth = (int)$month;
    $currentYear  = (int)$year;

    if ($currentMonth == 1) {
        $prevMonth = 12;
        $prevYear  = $currentYear - 1;
    } else {
        $prevMonth = $currentMonth - 1;
        $prevYear  = $currentYear;
    }


    $sqlCheck = "
        SELECT COUNT(*) AS count 
        FROM HrmEmpDeductionDetails 
        WHERE Month = '$currentMonth' 
          AND Year = '$currentYear'
    ";

    $totalCheckRecords = $iconn->query($sqlCheck)->fetch_assoc()['count'];


    if ($totalCheckRecords == 0) {

        $sqlInsert = "
            INSERT INTO HrmEmpDeductionDetails
            (
                EmpID,
                BioID,
                Year,
                Month,
                PF,
                ESI
            )
            SELECT
                EmpID,
                BioID,
                '$currentYear'  AS Year,
                '$currentMonth' AS Month,
                PF,
                ESI            
            FROM HrmEmpDeductionDetails
            WHERE Month = '$prevMonth'
              AND Year  = '$prevYear'
        ";
    //echo $sqlInsert; exit;
        $iconn->query($sqlInsert);
        if($iconn->affected_rows > 0) legacyAuditLogInsertById('HrmEmpDeductionDetails', 'EmpID', $iconn->insert_id, null, ['Remarks' => 'Auto carry forward PF/ESI from prev month', 'Year' => $currentYear, 'Month' => $currentMonth], 'Hrm');
    }

}
// Output JSON in DataTables format
echo json_encode([
    "draw" => intval($request['draw']),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalFiltered,
    "data" => $data // could be empty, and that's okay
]);
