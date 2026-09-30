<?php
include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");

$OnlyDetected = isset($_POST['OnlyDetected']) ? intval($_POST['OnlyDetected']) : 0;

// Pre-fetch lookup tables
function fetchLookupMap($iconn, $query, $keyField, $valueFields)
{
    $result = $iconn->query($query);
    $map = array();
    while ($row = $result->fetch_assoc()) {
        $key = $row[$keyField];
        $value = array();
        foreach ($valueFields as $field) {
            $value[$field] = $row[$field];
        }
        $map[$key] = $value;
    }
    return $map;
}


function setColor($field, $val)
{
    $val = trim(str_replace(',', '', $val));
    $color = "";

    if ($field === 'TpcResult') {
        // Remove CFU/SQCM if present
        $numericVal = floatval(preg_replace('/[^0-9.]/', '', $val));

        if (is_numeric($numericVal) && $numericVal > 80) {
            $color = "red";
        } elseif (strtoupper($val) === "TNTC") {
            $color = "red";
        }

    } elseif ($field === 'VcholeraeResult') {
        if (strtolower($val) === "positive") {
            $color = "red";
        }
    } elseif ($field === 'InstantSwab') {
        // New condition for instant swab
        if (strtolower($val) === "positive") {
            $color = "red";
        }
    }

    return $color;
}




// Fetch all reference data
$allPlants = fetchLookupMap($iconn, "SELECT ProcessingStationID, StationName FROM RmProcessingStations", 'ProcessingStationID', array('StationName'));
$allVarieties = fetchLookupMap($iconn, "SELECT ProductID, ShortName FROM ScrmProductList", 'ProductID', array('ShortName'));
$allParameters = fetchLookupMap($iconn, "SELECT TestID, TestName FROM QcTestList", 'TestID', array('TestName'));
$allMedia = fetchLookupMap($iconn, "SELECT MediaID, MediaName FROM usedmediamaster", 'MediaID', array('MediaName'));
$allInstruments = fetchLookupMap($iconn, "SELECT InstrumentId, InstrumentName FROM instrumentmaster", 'InstrumentId', array('InstrumentName'));

// Userlist names
$sqlQuery = "SELECT MIN(LUID) as USER_ID, LbuName as USER_NAME FROM `QcLabTechnologist` WHERE LbuStatus=1 GROUP BY LbuName";
$stmtUsers = $iconn->query($sqlQuery);
$RowUsersLists = array();
while ($RowUsers = $stmtUsers->fetch_array()) {
    $RowUsersLists[$RowUsers['USER_ID']] = $RowUsers['USER_NAME'];
}

// Fetch request variables safely
$request = $_REQUEST;
$searchValue = isset($request['search']['value']) ? $iconn->real_escape_string($request['search']['value']) : '';

$matchingUserIDs = array();
if (!empty($searchValue)) {
    foreach ($RowUsersLists as $userID => $userName) {
        if (stripos($userName, $searchValue) !== false) {
            $matchingUserIDs[] = $userID;
        }
    }
}

$fromPage = "";
if (isset($request['fromPage'])) {
    if ($request['fromPage'] === 'Create') {
        $fromPage = "Create";
    } else if ($request['fromPage'] === 'Listpage') {
        $fromPage = "Listpage";
    }
}

$columns = array(
    'mbwic_id',
    'sample_drawn_by',
    'sample_collection_date',
    'AnalysisDate',
    'Dateofcompletion',
    'Plant_id',
    'sample_collection_area',
    'sample_desc',
    'tpc_media',
    'tpc_Kitlotno',
    'tpc_UsedWells',
    'tpc_equipement',
    'tpc_result',
    'tpc_completion_date',
    'vcholerae_media',
    'vcholerae_Kitlotno',
    'vcholerae_UsedWells',
    'vcholerae_equipement',
    'vcholerae_result',
    'vcholerae_completion_date',
    'analysed_by',
    'checked_by',
    'verified_by',
    'remarks'
);

// Pagination and limits
$limit = isset($_POST['length']) ? intval($_POST['length']) : 20;
$limit = ($limit < 0) ? 20 : $limit;
$offset = isset($_POST['start']) ? max(0, intval($_POST['start'])) : 0;

$FromDate = isset($request['fromDate']) ? $iconn->real_escape_string($request['fromDate']) : '';
$ToDate = isset($request['toDate']) ? $iconn->real_escape_string($request['toDate']) : '';

$OrderBYVal = isset($request['order'][0]['column']) ? intval($request['order'][0]['column']) : 0;
$orderColumn = isset($columns[$OrderBYVal]) ? $columns[$OrderBYVal] : 'mbwic_id';

$searchQueryParts = array();

if (!empty($FromDate) && !empty($ToDate) && $fromPage != "Create") {
    $searchQueryParts[] = "Dateofcompletion BETWEEN '$FromDate' AND '$ToDate'";
}

if (!empty($request['report_type'])) {
    $reportType = $iconn->real_escape_string($request['report_type']);
    $searchQueryParts[] = "report_type = '$reportType'";
}

if (!empty($searchValue)) {
    $searchLikeParts = array();
    foreach ($columns as $col) {
        if (in_array($col, array('sample_drawn_by', 'analysed_by', 'checked_by', 'verified_by'))) {
            if (!empty($matchingUserIDs)) {
                $idList = implode(',', array_map('intval', $matchingUserIDs));
                $searchLikeParts[] = "$col IN ($idList)";
            }
        } else {
            $escapedVal = $iconn->real_escape_string($searchValue);
            $searchLikeParts[] = "$col LIKE '%$escapedVal%'";
        }
    }
    if (!empty($searchLikeParts)) {
        $searchQueryParts[] = '(' . implode(' OR ', $searchLikeParts) . ')';
    }
}

$searchQuery = (count($searchQueryParts) > 0) ? ' AND ' . implode(' AND ', $searchQueryParts) : '';

if ($OnlyDetected == 1) {
    $searchQuery .= " AND (
        (
            tpc_result REGEXP '[0-9]+(\\.[0-9]+)?'
            AND CAST(
                REGEXP_SUBSTR(REPLACE(tpc_result, ',', ''), '[0-9]+(\\.[0-9]+)?')
                AS DECIMAL(10,2)
            ) > 80
        )
        OR UPPER(TRIM(tpc_result)) = 'TNTC'
        OR LOWER(TRIM(vcholerae_result)) = 'positive'
        OR LOWER(TRIM(instant_swab)) = 'positive'
    ) ";
    $totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM micro_biology_ice_chutes_water_tanks Where 1=1 $searchQuery ")->fetch_assoc()['count'];

}

$totalRecordsQuery = "SELECT COUNT(*) as count FROM micro_biology_ice_chutes_water_tanks";
$totalRecordsResult = $iconn->query($totalRecordsQuery);
$totalRecordsRow = $totalRecordsResult->fetch_assoc();
$totalRecords = $totalRecordsRow['count'];

$totalFilteredQuery = "SELECT COUNT(*) as count FROM micro_biology_ice_chutes_water_tanks WHERE 1=1 $searchQuery";
$totalFilteredResult = $iconn->query($totalFilteredQuery);
$totalFilteredRow = $totalFilteredResult->fetch_assoc();
$totalFiltered = $totalFilteredRow['count'];

$sql = "SELECT * FROM micro_biology_ice_chutes_water_tanks WHERE 1=1 $searchQuery ORDER BY $orderColumn DESC LIMIT $offset, $limit";

$result = $iconn->query($sql);
$data = array();
if ($result && $result->num_rows > 0) {
    $i = $offset + 1;
    while ($row = $result->fetch_assoc()) {
        $singlerow = array();
        $singlerow['Sno'] = $i++;
        $singlerow['sample_collection_date'] = isset($row['sample_collection_date']) ? getFormatedDate($row['sample_collection_date']) : '';

        $singlerow['plant_name'] = isset($allPlants[$row['Plant_id']]['StationName']) ? $allPlants[$row['Plant_id']]['StationName'] : '';
        $singlerow['sample_analysis_date'] = isset($row['AnalysisDate']) ? getFormatedDate($row['AnalysisDate']) : '';
        $singlerow['sample_drawn_by'] = isset($RowUsersLists[$row['sample_drawn_by']]) ? $RowUsersLists[$row['sample_drawn_by']] : '';
        // $singlerow['instant_swab'] = $RowUsersLists['instant_swab']; 
        // $singlerow['instant_swab'] = isset($RowUsersLists[$row['instant_swab']]) ? $RowUsersLists[$row['instant_swab']]: $row['instant_swab'];  
        $InstantSwabColor = setColor('InstantSwab', isset($row['instant_swab']) ? $row['instant_swab'] : null);
        $singlerow['instant_swab'] = isset($row['instant_swab']) ? "<span style='color:$InstantSwabColor'>" . $row['instant_swab'] . "</span>" : '';
        $singlerow['tpc_completion_date'] = isset($row['tpc_completion_date']) ? getFormatedDate($row['tpc_completion_date']) : '';
        $singlerow['analysed_by'] = isset($RowUsersLists[$row['analysed_by']]) ? $RowUsersLists[$row['analysed_by']] : '';
        $singlerow['checked_by'] = isset($RowUsersLists[$row['checked_by']]) ? $RowUsersLists[$row['checked_by']] : '';
        $singlerow['verified_by'] = isset($RowUsersLists[$row['verified_by']]) ? $RowUsersLists[$row['verified_by']] : '';

        if ($fromPage === "") {
            $singlerow['sample_desc'] = isset($row['sample_desc']) ? $row['sample_desc'] : '';
            $singlerow['sample_collection_area'] = isset($row['sample_collection_area']) ? $row['sample_collection_area'] : '';
            $singlerow['Dateofcompletion'] = isset($row['Dateofcompletion']) ? getFormatedDate($row['Dateofcompletion']) : '';
            $singlerow['AnalysisDate'] = isset($row['AnalysisDate']) ? getFormatedDate($row['AnalysisDate']) : '';

            // Map tpc_media names
            $tpc_mediaIDs = explode(",", isset($row['tpc_media']) ? $row['tpc_media'] : '');
            $tpc_mediaNames = array();
            foreach ($tpc_mediaIDs as $id) {
                $id = trim($id);
                if ($id !== '' && isset($allMedia[$id]['MediaName'])) {
                    $tpc_mediaNames[] = $allMedia[$id]['MediaName'];
                }
            }
            $singlerow['tpc_media'] = implode(",", $tpc_mediaNames);

            // Map tpc_equipement names
            $tpc_equipementIDs = explode(",", isset($row['tpc_equipement']) ? $row['tpc_equipement'] : '');
            $tpc_equipementNames = array();
            foreach ($tpc_equipementIDs as $id) {
                $id = trim($id);
                if ($id !== '' && isset($allInstruments[$id]['InstrumentName'])) {
                    $tpc_equipementNames[] = $allInstruments[$id]['InstrumentName'];
                }
            }
            $singlerow['tpc_equipement'] = implode(",", $tpc_equipementNames);

            // Color logic for tpc_result
            $tpcColor = setColor('TpcResult', isset($row['tpc_result']) ? $row['tpc_result'] : null);
            $singlerow['tpc_result'] = isset($row['tpc_result']) ? "<span style='color:$tpcColor'>" . $row['tpc_result'] . "</span>" : '';

            // $tpcColor = setColor('TpcResult2', isset($row['tpc_result']) ? $row['tpc_result2'] : null);
            // $singlerow['tpc_result2'] = isset($row['tpc_result2']) ? "<span style='color:$tpcColor'>" . $row['tpc_result2'] . "</span>" : '';

            $singlerow['tpc_completion_date'] = isset($row['tpc_completion_date']) ? getFormatedDate($row['tpc_completion_date']) : '';



            // Map vcholerae_media names
            $vcholerae_mediaIDs = explode(",", isset($row['vcholerae_media']) ? $row['vcholerae_media'] : '');
            $vcholerae_mediaNames = array();
            foreach ($vcholerae_mediaIDs as $id) {
                $id = trim($id);
                if ($id !== '' && isset($allMedia[$id]['MediaName'])) {
                    $vcholerae_mediaNames[] = $allMedia[$id]['MediaName'];
                }
            }
            $singlerow['vcholerae_media'] = implode(",", $vcholerae_mediaNames);

            // Map vcholerae_equipement names
            $vcholerae_equipementIDs = explode(",", isset($row['vcholerae_equipement']) ? $row['vcholerae_equipement'] : '');
            $vcholerae_equipementNames = array();
            foreach ($vcholerae_equipementIDs as $id) {
                $id = trim($id);
                if ($id !== '' && isset($allInstruments[$id]['InstrumentName'])) {
                    $vcholerae_equipementNames[] = $allInstruments[$id]['InstrumentName'];
                }
            }
            $singlerow['vcholerae_equipement'] = implode(",", $vcholerae_equipementNames);
            $vcholeraeColor = setColor('VcholeraeResult', isset($row['vcholerae_result']) ? $row['vcholerae_result'] : null);
            $singlerow['vcholerae_result'] = isset($row['vcholerae_result']) ? "<span style='color:$vcholeraeColor'>" . $row['vcholerae_result'] . "</span>" : '';
            $singlerow['vcholerae_completion_date'] = isset($row['vcholerae_completion_date']) ? getFormatedDate($row['vcholerae_completion_date']) : '';
            $singlerow['remarks'] = isset($row['remarks']) ? $row['remarks'] : '';

        } else {
            if ($fromPage === "Create") {
                $singlerow['Action'] = "<a target='_blank' href='ihmbrficawt-create?FromDate=$FromDate&ToDate=$ToDate&id=" . $row['mbwic_id'] . "'><i class='mdi mdi-pencil font-size-16'></i></a>
                <button class='btn btn-link text-danger btnDelete' style='position: relative;top: -3px' data-id='" . $row['mbwic_id'] . "' data-toggle='tooltip' data-placement='top' title='Delete'>
                    <i class='mdi mdi-trash-can font-size-16'></i></button>";
            } else if ($fromPage === "Listpage") {
                $singlerow['Action'] = "<a href='ihmbrficawt-create?FromDate=" . $row['AnalysisDate'] . "&ToDate=" . $row['AnalysisDate'] . "'><i class='mdi mdi-plus font-size-16'></i></a>";
            }
        }
        $data[] = $singlerow;
    }
}

echo json_encode(array(
    "draw" => isset($request['draw']) ? intval($request['draw']) : 0,
    "recordsTotal" => intval($totalRecords),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
));
exit;
