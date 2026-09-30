<?php
include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");



$OnlyDetected = isset($_POST['OnlyDetected']) ? intval($_POST['OnlyDetected']) : 0;
// Pre-fetch lookup tables
function fetchLookupMap($iconn, $query, $keyField, $valueFields)
{
    $result = $iconn->query($query);
    $map = [];
    while ($row = $result->fetch_assoc()) {
        $key = $row[$keyField];
        $value = [];
        foreach ($valueFields as $field) {
            $value[$field] = $row[$field];
        }
        $map[$key] = $value;
    }
    return $map;
}

function setColor($res, $val)
{
    $chkval = 20;
    if ($res == "result1") {
        $chkval = 35;
    }
    $color = "";
    if (is_numeric($val) && $val > $chkval) {
        $color = "Red";
    }
    return $color;
}

// Fetch all reference data
$allPlants = fetchLookupMap($iconn, "SELECT ProcessingStationID, StationName FROM RmProcessingStations", 'ProcessingStationID', ['StationName']);
$allVarieties = fetchLookupMap($iconn, "SELECT ProductID, ShortName FROM ScrmProductList", 'ProductID', ['ShortName']);
$allParameters = fetchLookupMap($iconn, "SELECT TestID, TestName FROM QcTestList", 'TestID', ['TestName']);
$allMedia = fetchLookupMap($iconn, "SELECT MediaID, MediaName FROM usedmediamaster", 'MediaID', ['MediaName']);
$allInstruments = fetchLookupMap($iconn, "SELECT InstrumentId, InstrumentName FROM instrumentmaster", 'InstrumentId', ['InstrumentName']);

//  Userlist names
$sqlQuery = "SELECT MIN(LUID) as USER_ID, LbuName as USER_NAME FROM `QcLabTechnologist` WHERE LbuStatus=1 Group by LbuName ";
$stmtUsers = $iconn->query($sqlQuery);
$RowUsersLists = [];
while ($RowUsers = $stmtUsers->fetch_array()) {
    $RowUsersLists[$RowUsers['USER_ID']] = $RowUsers['USER_NAME'];
}
//end here

$request = $_REQUEST;


$loadAll = isset($request['loadAll']) ? intval($request['loadAll']) : 0;

if ($loadAll) {
    $limit = -1;   // no limit
    $offset = 0;
} else {
    $limit = isset($_POST['length']) ? (int) $_POST['length'] : 20;
    $offset = isset($_POST['start']) ? max(0, (int) $_POST['start']) : 0;
}

$fromPage = "";
if (isset($request['fromPage']) && $request['fromPage'] == 'Create') {
    $fromPage = "Create";
} else if (isset($request['fromPage']) && $request['fromPage'] == 'Listpage') {
    $fromPage = "Listpage";
}

// $columns = [`mbwh_id`, `report_type`, `sample_drawn_by`, `sample_collection`, `plant_id`, `sample_desc`, `location`, `tap_no_for_water`, `itype_of_ice`, `tpc_paramater`, `tpc_media`, `tpc_equipement`, `tpc_result1`, `tpc_result2`, `tpc_completion_date`, `vcholerae_paramater`, `vcholerae_media`, `vcholerae_equipement`, `vcholerae_result1`, `vcholerae_result2`, `vcholerae_completion_date`, `analysed_by`, `checked_by`, `verified_by`, `remarks`];

$columns = [
    'mbwh_id',
    'report_type',
    'sample_drawn_by',
    'sample_collection',
    'plant_id',
    'sample_desc',
    'location',
    'tap_no_for_water',
    'itype_of_ice',
    'tpc_paramater',
    'tpc_media',
    'tpc_equipement',
    'tpc_result1',
    'tpc_result2',
    'tpc_completion_date',
    'vcholerae_paramater',
    'vcholerae_media',
    'vcholerae_equipement',
    'vcholerae_result1',
    'vcholerae_result2',
    'vcholerae_completion_date',
    'analysed_by',
    'checked_by',
    'verified_by',
    'remarks'
];


$limit = (int) $_POST['length'];
$limit = $limit < 0 ? 20 : $limit;
$offset = max(0, (int) $_POST['start']);

$FromDate = $_POST['fromDate'];
$ToDate = $_POST['toDate'];

$OrderBYVal = intval($request['order'][0]['column']);
$searchValue = $iconn->real_escape_string($request['search']['value']);

$searchQueryParts = [];

if (!empty($FromDate) && !empty($ToDate)) {
    $FromDateFull = $FromDate . ' 00:00:00';
    $ToDateFull = $ToDate . ' 23:59:59';
    $searchQueryParts[] = " vcholerae_completion_date BETWEEN '$FromDateFull' AND '$ToDateFull' ";
}


if (!empty($request['report_type'])) {
    $searchQueryParts[] = " report_type = '" . $request['report_type'] . "' ";
}

if (!empty($searchValue)) {
    $searchQueryParts[] = "(" . $columns[0] . " LIKE '%" . $searchValue . "%' OR " . $columns[1] . " LIKE '%" . $searchValue . "%' OR " . $columns[2] . " LIKE '%" . $searchValue . "%'  OR " . $columns[3] . " LIKE '%" . $searchValue . "%' OR " . $columns[4] . " LIKE '%" . $searchValue . "%' OR " . $columns[5] . " LIKE '%" . $searchValue . "%' OR " . $columns[6] . " LIKE '%" . $searchValue . "%' OR " . $columns[7] . " LIKE '%" . $searchValue . "%' OR " . $columns[8] . " LIKE '%" . $searchValue . "%')";
}

$searchQuery = count($searchQueryParts) > 0 ? ' AND ' . implode(' AND ', $searchQueryParts) : '';

$totalRecords = $iconn->query("SELECT COUNT(*) as count FROM micro_biology_workers_hand")->fetch_assoc()['count'];
//echo "SELECT COUNT(*) as count FROM micro_biology_workers_hand Where 1=1 $searchQuery"; exit;
$totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM micro_biology_workers_hand Where 1=1 $searchQuery ")->fetch_assoc()['count'];
$data = [];

if ($OnlyDetected == 1) {
    $searchQuery .= " AND (
        (
            tpc_result REGEXP '[0-9]+(\\.[0-9]+)?'
            AND CAST(REGEXP_SUBSTR(tpc_result, '[0-9]+(\\.[0-9]+)?') AS DECIMAL(10,2)) > 80
        )
        OR UPPER(TRIM(tpc_result)) = 'TNTC'
        OR UPPER(TRIM(vcholerae_result)) = 'POSITIVE'
    ) ";
    $totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM micro_biology_workers_hand Where 1=1 $searchQuery ")->fetch_assoc()['count'];

}
$sql = "SELECT * from micro_biology_workers_hand where 1=1 $searchQuery";
if ($limit > 0) {
    $sql .= " LIMIT $offset, $limit";
}
//echo $sql; exit;
$result = $iconn->query($sql);
if ($result && $result->num_rows > 0) {
    $i = 0;
    while ($RowLeaveList = $result->fetch_assoc()) {
        $i++;
        $singlerow = array();

        $singlerow['Sno'] = $i;
        $singlerow['sample_date'] = getFormatedDate($RowLeaveList['SampleDate']);

        $singlerow['name_food_worker_hand'] = $RowLeaveList['name_food_worker_hand'];
        $singlerow['analysis_date'] = getFormatedDate($RowLeaveList['AnalysisDate']);
        $singlerow['sample_collection_area'] = $RowLeaveList['sample_collection_area'];
        $singlerow['sample_drawn_by'] = $RowUsersLists[$RowLeaveList['sample_drawn_by']] ? $RowUsersLists[$RowLeaveList['sample_drawn_by']] : '';

        $tpc_hand_color = $RowLeaveList['tpc_result'];
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
        $singlerow['tpc_result'] = $tpc_hand_color;
        $singlerow['tpc_completion_date'] = getFormatedDate($RowLeaveList['tpc_completion_date']);
        $singlerow['vcholerae_result'] = $RowLeaveList['vcholerae_result'];
        $singlerow['analysed_by'] = $RowUsersLists[$RowLeaveList['analysed_by']] ? $RowUsersLists[$RowLeaveList['analysed_by']] : '';
        $singlerow['checked_by'] = $RowUsersLists[$RowLeaveList['checked_by']] ? $RowUsersLists[$RowLeaveList['checked_by']] : '';
        $singlerow['verified_by'] = $RowUsersLists[$RowLeaveList['verified_by']] ? $RowUsersLists[$RowLeaveList['verified_by']] : '';
        if ($fromPage == "") {
            $singlerow['plant_id'] = $allPlants[$RowLeaveList['plant_id']]['StationName'] ? $allPlants[$RowLeaveList['plant_id']]['StationName'] : '';
            $singlerow['sample_desc'] = $RowLeaveList['sample_desc'];
            $singlerow['location'] = $RowLeaveList['location'];
            $singlerow['tap_no_for_water'] = $RowLeaveList['tap_no_for_water'];
            $singlerow['itype_of_ice'] = $RowLeaveList['itype_of_ice'];

            $singlerow['tpc_paramater'] = $allParameters[$RowLeaveList['tpc_paramater']]['TestName'] ? $allParameters[$RowLeaveList['tpc_paramater']]['TestName'] : '';

            $tpc_mediaIDs = explode(",", $RowLeaveList['tpc_media']);
            $tpc_mediaNames = array_map(function ($id) use ($allMedia) {
                return $allMedia[$id]['MediaName'] ? $allMedia[$id]['MediaName'] : '';
            }, $tpc_mediaIDs);

            $singlerow['tpc_media'] = implode(",", array_filter($tpc_mediaNames));

            //equipmentused
            $tpc_equipementIDs = explode(",", $RowLeaveList['tpc_equipement']);
            $tpc_equipementNames = array_map(function ($id) use ($allInstruments) {
                return $allInstruments[$id]['InstrumentName'] ? $allInstruments[$id]['InstrumentName'] : '';
            }, $tpc_equipementIDs);

            $singlerow['tpc_equipement'] = implode(",", array_filter($tpc_equipementNames));

            $tpc_bgcolor = setColor('result1', $RowLeaveList['tpc_result1']);
            $tpc_class = "";
            if ($tpc_bgcolor) {
                $tpc_class = "redbg";
            }
            $singlerow['tpc_result1'] = "<span class='" . $tpc_class . "' style='background-color:" . $tpc_bgcolor . "'>" . $RowLeaveList['tpc_result1'] . "<span>";
            $tpc_bgcolor = setColor('result2', $RowLeaveList['tpc_result2']);
            $tpc_class = "";
            if ($tpc_bgcolor) {
                $tpc_class = "redbg";
            }
            $singlerow['tpc_result2'] = "<span class='" . $tpc_class . "' style='background-color:" . $tpc_bgcolor . "'>" . $RowLeaveList['tpc_result2'] . "<span>";
            $singlerow['tpc_completion_date'] = getFormatedDate($RowLeaveList['tpc_completion_date']);



            //equipmentused


            $singlerow['vcholerae_paramater'] = $allParameters[$RowLeaveList['vcholerae_paramater']]['TestName'] ? $allParameters[$RowLeaveList['vcholerae_paramater']]['TestName'] : '';

            $vcholerae_mediaIDs = explode(",", $RowLeaveList['vcholerae_media']);
            $vcholerae_mediaNames = array_map(function ($id) use ($allMedia) {
                return $allMedia[$id]['MediaName'] ? $allMedia[$id]['MediaName'] : '';
            }, $vcholerae_mediaIDs);

            $singlerow['vcholerae_media'] = implode(",", array_filter($vcholerae_mediaNames));

            $vcholerae_equipementIDs = explode(",", $RowLeaveList['vcholerae_equipement']);
            $vcholerae_equipementNames = array_map(function ($id) use ($allInstruments) {
                return $allInstruments[$id]['InstrumentName'] ? $allInstruments[$id]['InstrumentName'] : '';
            }, $vcholerae_equipementIDs);

            $singlerow['vcholerae_equipement'] = implode(",", array_filter($vcholerae_equipementNames));

            $tpc_bgcolor = setColor('result1', $RowLeaveList['vcholerae_result1']);
            $tpc_class = "";
            if ($tpc_bgcolor) {
                $tpc_class = "redbg";
            }
            $singlerow['vcholerae_result1'] = $RowLeaveList['vcholerae_result1'];


            $singlerow['vcholerae_completion_date'] = $RowLeaveList['vcholerae_completion_date'];

            $singlerow['analysed_by'] = $RowUsersLists[$RowLeaveList['analysed_by']] ? $RowUsersLists[$RowLeaveList['analysed_by']] : '';
            $singlerow['checked_by'] = $RowUsersLists[$RowLeaveList['checked_by']] ? $RowUsersLists[$RowLeaveList['checked_by']] : '';
            $singlerow['verified_by'] = $RowUsersLists[$RowLeaveList['verified_by']] ? $RowUsersLists[$RowLeaveList['verified_by']] : '';
            $singlerow['remarks'] = $RowLeaveList['remarks'];
        } else {
            if ($fromPage == "Create") {
                $singlerow['Action'] = "<a target='_blank' href='ihmbrfwh-create?FromDate=" . $FromDate . "&ToDate=" . $ToDate . "&id=" . $RowLeaveList['mbwh_id'] . "'><i class='mdi mdi-pencil font-size-16'></i></a>
                    <button class='btn btn-link text-danger  btnDelete' style='position: relative;top: -3px' data-id='" . $RowLeaveList['mbwh_id'] . "' data-toggle='tooltip' data-placement='top' title='' value='' data-original-title='Delete'>
                        <i class='mdi mdi-trash-can font-size-16'></i></button>
                ";
            } else if ($fromPage == "Listpage") {
                $dateOnly = date('Y-m-d', strtotime($RowLeaveList['vcholerae_completion_date']));
                $singlerow['Action'] = "<a target='_blank' href='ihmbrfwh-create?FromDate=" . $dateOnly . "&ToDate=" . $dateOnly . "'><i class='mdi mdi-plus font-size-16'></i></a>";
            }
        }
        $data[] = $singlerow;
    }

}



echo json_encode([
    "draw" => intval($request['draw']),
    "recordsTotal" => intval($totalRecords),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data, // could be empty, and that's okay
]);
exit;