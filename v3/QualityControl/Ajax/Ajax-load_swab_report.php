<?php
include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");

// Helper: get user names
function getUserMap($iconn)
{
    $sql = "SELECT MIN(LUID) as USER_ID, LbuName as USER_NAME 
            FROM QcLabTechnologist 
            WHERE LbuStatus=1 
            GROUP BY LbuName";
    $result = $iconn->query($sql);
    $map = [];
    while ($row = $result->fetch_assoc()) {
        $map[$row['USER_ID']] = $row['USER_NAME'];
    }
    return $map;
}

$users = getUserMap($iconn);

// Get DataTables request
$request = $_REQUEST;
$report_type = isset($_POST['report_type']) ? $_POST['report_type'] : 'workhand';
$fromDate = !empty($_POST['fromDate']) ? $_POST['fromDate'] : '';
$toDate = !empty($_POST['toDate']) ? $_POST['toDate'] : '';
$limit = (int) (!empty($_POST['length']) ? $_POST['length'] : 10);
$offset = (int) (!empty($_POST['start']) ? $_POST['start'] : 0);
$searchValue = !empty($request['search']['value']) ? $request['search']['value'] : '';

$data = [];
$i = $offset;


if ($report_type === "workhand") {
    $sql = " SELECT 
    wh.SampleDate, 
    u.LbuName AS SampleDrawnByName, 
    wh.sample_collection_area, 
    wh.AnalysisDate, 
    wh.name_food_worker_hand AS WorkerName, 
    wh.emp_id, 
    wh.tpc_result AS TPC_Result_Hand, 
    wh.vcholerae_result AS VCholerae_Result_Hand, 
    wh.tpc_completion_date AS CompletionDate, 
    wh.analysed_by AS AnalysedBy, 
    wh.checked_by AS CheckedBy, 
    wh.verified_by AS VerifiedBy
FROM micro_biology_workers_hand wh
LEFT JOIN QcLabTechnologist u 
    ON wh.sample_drawn_by = u.LUID
WHERE wh.SampleDate BETWEEN '$fromDate' AND '$toDate' ";
} else { // foodcontact
    $sql = " SELECT 
            fs.sample_collection_date AS SampleDate,
            u.LbuName AS SampleDrawnByName, 
            fs.AnalysisDate,
            fs.name_food_contact_surface AS SurfaceName,
            fs.tpc_result AS TPC_Result_Surface,
            fs.sample_collection_area,
            fs.tpc_completion_date AS CompletionDate,
            fs.analysed_by AS AnalysedBy,
            fs.checked_by AS CheckedBy,
            fs.verified_by AS VerifiedBy
        FROM micro_biology_workers_food_surface fs
        LEFT JOIN QcLabTechnologist u ON fs.sample_drawn_by = u.LUID
        WHERE fs.sample_collection_date BETWEEN '$fromDate' AND '$toDate'
    ";
}
// echo $sql;  exit;

if (!empty($searchValue)) {
    $searchValue = $iconn->real_escape_string($searchValue);
    if ($report_type === "workhand") {
        $sql .= " AND (
            wh.name_food_worker_hand LIKE '%$searchValue%' OR
            wh.tpc_result LIKE '%$searchValue%' OR
            wh.vcholerae_result LIKE '%$searchValue%' OR
            wh.tpc_completion_date LIKE '%$searchValue%' OR
            wh.AnalysisDate LIKE '%$searchValue%'
        )";
    } else {
        $sql .= " AND (
            fs.name_food_contact_surface LIKE '%$searchValue%' OR
            fs.tpc_result LIKE '%$searchValue%' OR
            fs.vcholerae_result LIKE '%$searchValue%' OR
            fs.tpc_completion_date LIKE '%$searchValue%' OR
            fs.AnalysisDate LIKE '%$searchValue%'
        )";
    }
}

// Order by
$sql .= ($report_type === "workhand") ? " ORDER BY wh.AnalysisDate DESC" : " ORDER BY fs.AnalysisDate DESC";

// Execute
$result = $iconn->query($sql);

while ($row = $result->fetch_assoc()) {
    $i++;

    $row['SampleDate'] = getFormatedDate($row['SampleDate']);
    $row['AnalysisDate'] = getFormatedDate($row['AnalysisDate']);
    $row['CompletionDate'] = getFormatedDate($row['CompletionDate']);




    $tpc_hand_color = $row['TPC_Result_Hand'];
    $valTrim = trim($tpc_hand_color);

    // Check for numeric value
    if (is_numeric($valTrim) || preg_match('/\d+(\.\d+)?/', $valTrim, $matches)) {
        $num = is_numeric($valTrim) ? floatval($valTrim) : floatval($matches[0]);
        if ($num > 80) {
            $tpc_hand_color = "<span style='color:red;'>" . $valTrim . "</span>";
        }
    } elseif (strtoupper($valTrim) === "TNTC") {
        $tpc_hand_color = "<span style='color:red;'>" . $valTrim . "</span>";
    }

    $tpc_Vcholerae_color = isset($row['VCholerae_Result_Hand']) ? $row['VCholerae_Result_Hand'] : '';

    if (strtolower(trim($tpc_Vcholerae_color)) === "positive") {
        // Positive → red
        $tpc_Vcholerae_color = "<span style='color:red;'>" . $row['VCholerae_Result_Hand'] . "</span>";
    } else {
        // Normal → default color
        $tpc_Vcholerae_color = $row['VCholerae_Result_Hand'];
    }

    $tpc_surface_color = isset($row['TPC_Result_Surface']) ? $row['TPC_Result_Surface'] : '';
    $valTrim = trim($tpc_surface_color);

    // Check numeric > 80
    if (preg_match('/\d+(\.\d+)?/', $valTrim, $m)) {
        $num = (float) $m[0];
        if ($num > 80) {
            $tpc_surface_color = "<span style='color:red;'>" . $valTrim . "</span>";
        }
    }
    // Check TNTC (case-insensitive)
    elseif (strtoupper($valTrim) === "TNTC") {
        $tpc_surface_color = "<span style='color:red;'>" . $valTrim . "</span>";
    }


    if ($report_type === "workhand") {
        $data[] = [
            'sno' => $i,
            'sample_collection_date' => $row['SampleDate'],
            'sample_drawn_by' => $row['SampleDrawnByName'],
            'analysis_date' => $row['AnalysisDate'],
            'sample_collection_area' => $row['sample_collection_area'],
            'worker_name' => $row['WorkerName'],
            'emp_id' => $row['emp_id'],
            // 'tpc_result_hand'        => $row['TPC_Result_Hand'],
            'tpc_result_hand' => $tpc_hand_color,
            // 'vcholerae_hand'         => $row['VCholerae_Result_Hand'],
            'vcholerae_hand' => $tpc_Vcholerae_color,
            'completion_date' => $row['CompletionDate'],
            'analysed_by' => $users[$row['AnalysedBy']] ? $users[$row['AnalysedBy']] : '',
            'checked_by' => $users[$row['CheckedBy']] ? $users[$row['CheckedBy']] : '',
            'verified_by' => $users[$row['VerifiedBy']] ? $users[$row['VerifiedBy']] : '',
        ];
    } else {
        $data[] = [
            'sno' => $i,
            'sample_collection_date' => $row['SampleDate'],
            'sample_drawn_by' => $row['SampleDrawnByName'],
            'analysis_date' => $row['AnalysisDate'],
            'sample_collection_area' => $row['sample_collection_area'],
            'surface_name' => $row['SurfaceName'],
            // 'tpc_result_surface'     => $row['TPC_Result_Surface'],
            'tpc_result_surface' => $tpc_surface_color,
            'completion_date' => $row['CompletionDate'],
            'analysed_by' => $users[$row['AnalysedBy']] ? $users[$row['AnalysedBy']] : '',
            'checked_by' => $users[$row['CheckedBy']] ? $users[$row['CheckedBy']] : '',
            'verified_by' => $users[$row['VerifiedBy']] ? $users[$row['VerifiedBy']] : '',
        ];
    }
}

// Pagination
$paginated = array_slice($data, $offset, $limit);

// Return JSON
echo json_encode([
    "draw" => intval($request['draw'] ? intval($request['draw']) : 1),
    "recordsTotal" => count($data),
    "recordsFiltered" => count($data),
    "data" => $paginated
]);
exit;
