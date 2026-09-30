<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

header('Content-Type: application/json');

$frozenBy = isset($_SESSION['UserID']) ? (int)$_SESSION['UserID'] : 0;

$deptId = isset($_POST['DeptID'])
    ? (int)$_POST['DeptID']
    : (isset($_SESSION['DeptID']) ? (int)$_SESSION['DeptID'] : 0);

$subDeptId = !empty($_POST['SubDeptID'])
    ? (int)$_POST['SubDeptID']
    : null;

$month = isset($_POST['Month'])
    ? (int)$_POST['Month']
    : (isset($_SESSION['MONTH']) ? (int)$_SESSION['MONTH'] : 0);

$year = isset($_POST['Year'])
    ? (int)$_POST['Year']
    : (isset($_SESSION['YEAR']) ? (int)$_SESSION['YEAR'] : 0);

$empCategory = isset($_POST['EmpCategory'])
    ? trim($_POST['EmpCategory'])
    : '';

$empSubCategory = !empty($_POST['EmpSubCategory'])
    ? trim($_POST['EmpSubCategory'])
    : null;

$monthSegment = !empty($_POST['MonthSegment'])
    ? trim($_POST['MonthSegment'])
    : null;

$startDate = !empty($_POST['StartDate'])
    ? $_POST['StartDate']
    : null;

$endDate = !empty($_POST['EndDate'])
    ? $_POST['EndDate']
    : null;


// -------------------------
// Validation
// -------------------------

$errors = [];

if ($deptId <= 0) {
    //$errors[] = 'Department is required.';
}

if ($month < 1 || $month > 12) {
    $errors[] = 'Invalid month.';
}

if ($year <= 0) {
    $errors[] = 'Invalid year.';
}

if ($empCategory === '') {
    //$errors[] = 'Employee category is required.';
}

if ($frozenBy <= 0) {
    $errors[] = 'User session is invalid.';
}

if (!empty($errors)) {

    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid salary sheet details.',
        'errors'  => $errors
    ]);

    exit;
}


// -------------------------
// Insert
// -------------------------

$sql = "
    INSERT INTO salary_sheet_freeze
    (
        dept_id,
        sub_dept_id,
        salary_month,
        salary_year,
        emp_category,
        emp_sub_category,
        month_segment,
        start_date,
        end_date,
        frozen_by,
        frozen_at
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
";


$stmt = $iconn->prepare($sql);

if ($stmt === false) {

    error_log(
        'Salary Freeze Prepare Error: ' . $iconn->error
    );

    echo json_encode([
        'status'  => 'error',
        'message' => 'Unable to prepare salary freeze query.',
        'error'   => $iconn->error
    ]);

    exit;
}


// -------------------------
// Bind parameters
// -------------------------

$stmt->bind_param(
    "iiiisssssi",
    $deptId,
    $subDeptId,
    $month,
    $year,
    $empCategory,
    $empSubCategory,
    $monthSegment,
    $startDate,
    $endDate,
    $frozenBy
);


// -------------------------
// Execute
// -------------------------

if ($stmt->execute()) {

    echo json_encode([
        'status'  => 'success',
        'message' => 'Salary sheet frozen successfully.'
    ]);

} else {

    error_log(
        'Salary Freeze Execute Error: ' . $stmt->error
    );

    // Duplicate entry
    if ($stmt->errno == 1062) {

        echo json_encode([
            'status'  => 'already_frozen',
            'message' => 'Salary sheet is already frozen.'
        ]);

    } else {

        echo json_encode([
            'status'  => 'error',
            'message' => 'Unable to freeze salary sheet.',
            'error'   => $stmt->error
        ]);
    }
}

$stmt->close();

?>
