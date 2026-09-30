<?php


include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";

// Pre-fetch lookup tables
function fetchLookupMap($iconn, $query, $keyField, $valueFields)
{
    $result = $iconn->query($query);
    $map    = [];
    while ($row = $result->fetch_assoc()) {
        $key   = $row[$keyField];
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
    $color = "";
    $rawVal = trim($val);

    // TPC thresholds
    // if (strtolower($res) == "tcp 35c" && is_numeric($val) && $val > 15) {
    //     $color = "red";
    // } elseif (strtolower($res) == "tcp 22c" && is_numeric($val) && $val > 50) {
    //     $color = "red";
    // }

    if (strtolower($res) == "tcp 35c" && is_numeric($val) && $val > 15) {
        $color = "red";
    }elseif (strtoupper(trim($res)) === "TNTC") {
                $color = "red"; 
    }
     elseif (strtolower($res) == "tcp 22c" && is_numeric($val) && $val > 50) {
        $color = "red";
    }
    elseif (strtoupper(trim($val)) === "TNTC") {
                $color = "red"; 
    }
    // VCholerae check (case-insensitive)
    elseif (strtolower($res) == "vcholeraeresult") {
        if (preg_match('/^positive$/i', $rawVal)) {
            $color = "red";
        } else {
            $color = "";
        }
    } elseif (strtolower($res) == "coliform") {
        if (preg_match('/<([\d\.]+)MPN/i', $rawVal, $matches)) {
            $value = floatval($matches[1]);
            if ($value > 1) {
                $color = "red";
            }
        }
    }


    return $color;
}




function setDate($date)
{
    if (isset($date) && $date) {
        return date("d-m-Y", strtotime($date));
    } else {
        return $date;
    }
}

// Fetch all reference data
$allPlants      = fetchLookupMap($iconn, "SELECT ProcessingStationID, StationName FROM RmProcessingStations", 'ProcessingStationID', ['StationName']);
$allVarieties   = fetchLookupMap($iconn, "SELECT ProductID, ShortName FROM ScrmProductList", 'ProductID', ['ShortName']);
$allParameters  = fetchLookupMap($iconn, "SELECT TestID, TestName FROM QcTestList", 'TestID', ['TestName']);
$allMedia       = fetchLookupMap($iconn, "SELECT MediaID, MediaName FROM usedmediamaster", 'MediaID', ['MediaName']);
$allInstruments = fetchLookupMap($iconn, "SELECT InstrumentId, InstrumentName FROM instrumentmaster", 'InstrumentId', ['InstrumentName']);

//  Userlist names
$sqlQuery      = "SELECT MIN(LUID) as USER_ID, LbuName as USER_NAME FROM `QcLabTechnologist` WHERE LbuStatus=1 Group by LbuName ";
$stmtUsers     = $iconn->query($sqlQuery);
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
    $limit  = isset($_POST['length']) ? (int) $_POST['length'] : 20;
    $offset = isset($_POST['start']) ? max(0, (int) $_POST['start']) : 0;
}

$fromPage = "";
if (isset($request['fromPage']) && $request['fromPage'] == 'Create') {
    $fromPage = "Create";
} else if (isset($request['fromPage']) && $request['fromPage'] == 'Listpage') {
    $fromPage = "Listpage";
}

// $columns = [`mbwi_id`, `report_type`, `sample_drawn_by`, `sample_collection`, `plant_id`, `sample_desc`, `location`, `tap_no_for_water`, `itype_of_ice`,`tpc_paramater`, `tpc_media`, `tpc_equipement`, `tpc_result1`, `tpc_result2`, `tpc_completion_date`, `vcholerae_paramater`, `vcholerae_media`, `vcholerae_equipement`, `vcholerae_result1`, `vcholerae_result2`, `vcholerae_completion_date`,`cholerae_paramater`, `cholerae_media`, `cholerae_equipement`, `cholerae_result1`, `cholerae_completion_date`, `analysed_by`, `checked_by`, `verified_by`, `remarks`];
$columns = ['mbwi_id', 'report_type', 'sample_drawn_by', 'sample_collection', 'plant_id', 'sample_desc', 'location', 'tap_no_for_water', 'itype_of_ice', 'tpc_paramater', 'tpc_media', 'tpc_equipement', 'tpc_result1', 'tpc_result2', 'tpc_completion_date', 'vcholerae_paramater', 'vcholerae_media', 'vcholerae_equipement', 'vcholerae_result1', 'vcholerae_result2', 'vcholerae_completion_date', 'cholerae_paramater', 'cholerae_media', 'cholerae_equipement', 'cholerae_result1', 'cholerae_completion_date', 'analysed_by', 'checked_by', 'verified_by', 'remarks'];

$limit  = (int) $_POST['length'];
$limit  = $limit < 0 ? 20 : $limit;
$offset = max(0, (int) $_POST['start']);

// $FromDate = $_POST['fromDate'];
// $ToDate   = $_POST['toDate'];

$FromDate = isset($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : date('Y-m-d');
$ToDate   = isset($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : date('Y-m-d');



$onlyDetected = !empty($_POST['OnlyDetected']) && $_POST['OnlyDetected'] == 1;






$OrderBYVal  = intval($request['order'][0]['column']);
$searchValue = $iconn->real_escape_string($request['search']['value']);

$searchQueryParts = [];

if (!empty($FromDate) && !empty($ToDate)) {
    // $searchQueryParts[] = " sample_collection BETWEEN '$FromDate' and '$ToDate' ";
    $searchQueryParts[] = " cholerae_completion_date >= '$FromDate 00:00:00' AND cholerae_completion_date <= '$ToDate 23:59:59' ";
}

if (!empty($request['report_type'])) {
    $searchQueryParts[] = " report_type = '" . $request['report_type'] . "' ";
}

if (!empty($searchValue)) {
    $searchQueryParts[] = "(" . $columns[0] . " LIKE '%" . $searchValue . "%' OR " . $columns[1] . " LIKE '%" . $searchValue . "%' OR " . $columns[2] . " LIKE '%" . $searchValue . "%'  OR " . $columns[3] . " LIKE '%" . $searchValue . "%' OR " . $columns[4] . " LIKE '%" . $searchValue . "%' OR " . $columns[5] . " LIKE '%" . $searchValue . "%' OR " . $columns[6] . " LIKE '%" . $searchValue . "%' OR " . $columns[7] . " LIKE '%" . $searchValue . "%' OR " . $columns[8] . " LIKE '%" . $searchValue . "%')";
}

$searchQuery = count($searchQueryParts) > 0 ? ' AND ' . implode(' AND ', $searchQueryParts) : '';



//dedcution code:-


// Check for detected-only filter
// $onlyDetected = true; 
// if ($onlyDetected) {
// $detectionFilter = "(
//     (tpc_result1 IS NOT NULL AND (tpc_result1 > 15 OR tpc_result1 > 50))
//     OR (tpc_result2 IS NOT NULL AND (tpc_result2 > 15 OR tpc_result2 > 50))
//     OR (vcholerae_result1 IS NOT NULL AND TRIM(UPPER(vcholerae_result1)) <> 'ABSENT' AND TRIM(vcholerae_result1) <> '')
//     OR (cholerae_result1 IS NOT NULL AND TRIM(UPPER(cholerae_result1)) <> '<1MPN/ML' AND TRIM(cholerae_result1) <> '')
// )";


// }


if (!empty($_POST['OnlyDetected']) && 
    ($_POST['OnlyDetected'] == 1 || $_POST['OnlyDetected'] === 'true' || $_POST['OnlyDetected'] === 'on')) {

    $onlyDetected = true;

    $detectionFilter = "(
        (tpc_result1 IS NOT NULL AND (tpc_result1 > 15 OR UPPER(TRIM(tpc_result1)) = 'TNTC'))
        OR (tpc_result2 IS NOT NULL AND (tpc_result2 > 50 OR UPPER(TRIM(tpc_result2)) = 'TNTC'))
        OR (vcholerae_result1 IS NOT NULL AND TRIM(UPPER(vcholerae_result1)) = 'POSITIVE')
        OR (cholerae_result1 IS NOT NULL AND TRIM(UPPER(cholerae_result1)) NOT IN ('<1MPN/ML', 'ABSENT', 'NEGATIVE') AND TRIM(cholerae_result1) <> '')
    )";
}










$totalRecords  = $iconn->query("SELECT COUNT(*) as count FROM micro_biology_water_ice")->fetch_assoc()['count'];
// $totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM micro_biology_water_ice Where 1=1 $searchQuery ")->fetch_assoc()['count'];
// $data          = [];


$totalFilteredQuery = "SELECT COUNT(*) as count FROM micro_biology_water_ice WHERE 1=1 $searchQuery";
//$totalFilteredQuery = "SELECT COUNT(*) as count FROM micro_biology_water_ice WHERE 1=1 $searchQuery";
//$totalFilteredQuery = "SELECT COUNT(*) as count FROM micro_biology_water_ice WHERE 1=1 $searchQuery";
if ($onlyDetected) {
    $totalFilteredQuery .= " AND " . $detectionFilter;
}
$totalFiltered = $iconn->query($totalFilteredQuery)->fetch_assoc()['count'];
$data          = [];




// $sql           = "SELECT * from micro_biology_water_ice where 1=1 $searchQuery
//              LIMIT $offset, $limit";

// Main query
$sql = "SELECT * FROM micro_biology_water_ice WHERE 1=1 $searchQuery";

if ($onlyDetected) {
    $sql .= " AND " . $detectionFilter;
}

if ($limit > 0) {
    $sql .= " LIMIT $offset, $limit";
}


//echo $sql; exit;
$result = $iconn->query($sql);



//Dection code:-



if ($result && $result->num_rows > 0) {
    $i = 0;
    while ($RowLeaveList = $result->fetch_assoc()) {
        $i++;
        $singlerow = array();

        $singlerow['Sno'] = $i;

        $singlerow['report_type'] = $RowLeaveList['report_type'];
        $singlerow['sample_collection']  = setDate($RowLeaveList['sample_collection']);
        $singlerow['sample_drawn_by']     = $RowUsersLists[$RowLeaveList['sample_drawn_by']] ? $RowUsersLists[$RowLeaveList['sample_drawn_by']] : '';
        $singlerow['analysis_startdate'] = setDate($RowLeaveList['analysis_startdate']);
        $singlerow['analysed_by'] = $RowUsersLists[$RowLeaveList['analysed_by']] ? $RowUsersLists[$RowLeaveList['analysed_by']] : '';
        $singlerow['checked_by']   = $RowUsersLists[$RowLeaveList['checked_by']] ? $RowUsersLists[$RowLeaveList['checked_by']] : '';
        $singlerow['verified_by']      = $RowUsersLists[$RowLeaveList['verified_by']] ? $RowUsersLists[$RowLeaveList['verified_by']] : '';


        $singlerow['tpc_result1'] = $RowLeaveList['tpc_result1'];
        $singlerow['tpc_result2'] = $RowLeaveList['tpc_result2'];
        $singlerow['vcholerae_result1'] = $RowLeaveList['vcholerae_result1'];
        $singlerow['cholerae_result1'] = $RowLeaveList['cholerae_result1'];

        if ($fromPage == "") {
            $singlerow['plant_id'] = $allPlants[$RowLeaveList['plant_id']]['StationName'] ? $allPlants[$RowLeaveList['plant_id']]['StationName'] : '';
            $singlerow['sample_desc'] = $RowLeaveList['sample_desc'];
            $singlerow['location'] = $RowLeaveList['location'];
            $singlerow['tap_no_for_water'] = $RowLeaveList['tap_no_for_water'] ? $RowLeaveList['tap_no_for_water'] : '-';
            $singlerow['itype_of_ice'] = $RowLeaveList['itype_of_ice'] ? $RowLeaveList['itype_of_ice'] : '-';

            $singlerow['tpc_paramater']     = $allParameters[$RowLeaveList['tpc_paramater']]['TestName'] ? $allParameters[$RowLeaveList['tpc_paramater']]['TestName'] : '';

            $tpc_mediaIDs   = explode(",", $RowLeaveList['tpc_media']);
            $tpc_mediaNames = array_map(function ($id) use ($allMedia) {
                return $allMedia[$id]['MediaName'] ? $allMedia[$id]['MediaName'] : '';
            }, $tpc_mediaIDs);

            $singlerow['tpc_media'] = implode(",", array_filter($tpc_mediaNames));

            //equipmentused
            $tpc_equipementIDs   = explode(",", $RowLeaveList['tpc_equipement']);
            $tpc_equipementNames = array_map(function ($id) use ($allInstruments) {
                return $allInstruments[$id]['InstrumentName'] ? $allInstruments[$id]['InstrumentName'] : '';
            }, $tpc_equipementIDs);

            $singlerow['tpc_equipement'] = implode(",", array_filter($tpc_equipementNames));

            // $tpc_bgcolor = setColor('result1',$RowLeaveList['tpc_result1']);
            // $tpc_class ="";    
            // if($tpc_bgcolor){
            //     $tpc_class="redbg";
            // }


            // $singlerow['tpc_result1']  = "<span class='".$tpc_class."' style='background-color:".$tpc_bgcolor."'>".$RowLeaveList['tpc_result1']."<span>";
            // $tpc_bgcolor = setColor('result2',$RowLeaveList['tpc_result2']);
            // $tpc_class ="";    
            // $tpc_class="";
            // if($tpc_bgcolor){
            //     $tpc_class="redbg";
            // }
            // $singlerow['tpc_result2']     = "<span class='".$tpc_class."' style='background-color:".$tpc_bgcolor."'>".$RowLeaveList['tpc_result2']."<span>";

            $tpc_color = setColor('Tcp 35C', $RowLeaveList['tpc_result1']);
            $singlerow['tpc_result1'] = "<span style='color:" . $tpc_color . "'>" . $RowLeaveList['tpc_result1'] . "</span>";

            $tpc_color = setColor('Tcp 22C', $RowLeaveList['tpc_result2']);
            $singlerow['tpc_result2'] = "<span style='color:" . $tpc_color . "'>" . $RowLeaveList['tpc_result2'] . "</span>";
            $cholerae_color = setColor('coliform', $RowLeaveList['cholerae_result1']);
            $singlerow['cholerae_result1'] = "<span style='color:" . $cholerae_color . "'>" . $RowLeaveList['cholerae_result1'] . "</span>";

            $singlerow['tpc_completion_date']     = setDate($RowLeaveList['tpc_completion_date']);



            //equipmentused


            $singlerow['vcholerae_paramater']     = $allParameters[$RowLeaveList['vcholerae_paramater']]['TestName'] ? $allParameters[$RowLeaveList['vcholerae_paramater']]['TestName'] : '';

            $vcholerae_mediaIDs   = explode(",", $RowLeaveList['vcholerae_media']);
            $vcholerae_mediaNames = array_map(function ($id) use ($allMedia) {
                return $allMedia[$id]['MediaName'] ? $allMedia[$id]['MediaName'] : '';
            }, $vcholerae_mediaIDs);

            $singlerow['vcholerae_media'] = implode(",", array_filter($vcholerae_mediaNames));

            $vcholerae_equipementIDs   = explode(",", $RowLeaveList['vcholerae_equipement']);
            $vcholerae_equipementNames = array_map(function ($id) use ($allInstruments) {
                return $allInstruments[$id]['InstrumentName'] ? $allInstruments[$id]['InstrumentName'] : '';
            }, $vcholerae_equipementIDs);

            $singlerow['vcholerae_equipement'] = implode(",", array_filter($vcholerae_equipementNames));

            $tpc_bgcolor = setColor('result1', $RowLeaveList['vcholerae_result1']);
            $tpc_class = "";
            if ($tpc_bgcolor) {
                $tpc_class = "redbg";
            }
            $singlerow['vcholerae_result1']     = $RowLeaveList['vcholerae_result1'];


            $singlerow['vcholerae_completion_date']     = setDate($RowLeaveList['vcholerae_completion_date']);


            $singlerow['cholerae_paramater']     = $allParameters[$RowLeaveList['cholerae_paramater']]['TestName'] ? $allParameters[$RowLeaveList['cholerae_paramater']]['TestName'] : '';

            $cholerae_mediaIDs   = explode(",", $RowLeaveList['cholerae_media']);
            $cholerae_mediaNames = array_map(function ($id) use ($allMedia) {
                return $allMedia[$id]['MediaName'] ? $allMedia[$id]['MediaName'] : '';
            }, $cholerae_mediaIDs);

            $singlerow['cholerae_media'] = implode(",", array_filter($cholerae_mediaNames));

            $cholerae_equipementIDs   = explode(",", $RowLeaveList['cholerae_equipement']);
            $cholerae_equipementNames = array_map(function ($id) use ($allInstruments) {
                return $allInstruments[$id]['InstrumentName'] ? $allInstruments[$id]['InstrumentName'] : '';
            }, $cholerae_equipementIDs);

            $singlerow['cholerae_equipement'] = implode(",", array_filter($cholerae_equipementNames));

            // $tpc_bgcolor = setColor('result1',$RowLeaveList['cholerae_result1']);
            // $tpc_class ="";    
            // if($tpc_bgcolor){
            //     $tpc_class="redbg";
            // }
            // $singlerow['cholerae_result1']     = $RowLeaveList['cholerae_result1'];

            $vcholerae_color = setColor('vcholeraeresult', $RowLeaveList['vcholerae_result1']);
            $singlerow['vcholerae_result1'] = "<span style='color:" . $vcholerae_color . "'>" . $RowLeaveList['vcholerae_result1'] . "</span>";


            $singlerow['cholerae_completion_date']     = setDate($RowLeaveList['cholerae_completion_date']);


            $singlerow['analysed_by'] = $RowUsersLists[$RowLeaveList['analysed_by']] ? $RowUsersLists[$RowLeaveList['analysed_by']] : '';
            $singlerow['checked_by']   = $RowUsersLists[$RowLeaveList['checked_by']] ? $RowUsersLists[$RowLeaveList['checked_by']] : '';
            $singlerow['verified_by']   = $RowUsersLists[$RowLeaveList['verified_by']] ? $RowUsersLists[$RowLeaveList['verified_by']] : '';
            $singlerow['remarks']     = $RowLeaveList['remarks'];
        } else {
            $singlerow['Action'] = "";

            if ($fromPage == "Create") {
                $singlerow['Action'] = "<a target='_blank' href='ihmbrfwi-create?FromDate=" . $FromDate . "&ToDate=" . $ToDate . "&id=" . $RowLeaveList['mbwi_id'] . "'><i class='mdi mdi-pencil font-size-16'></i></a>
                    <button class='btn btn-link text-danger  btnDelete' style='position: relative;top: -3px' data-id='" . $RowLeaveList['mbwi_id'] . "' data-toggle='tooltip' data-placement='top' title='' value='' data-original-title='Delete'>
                        <i class='mdi mdi-trash-can font-size-16'></i></button>
                ";
            } else if ($fromPage == "Listpage") {
                $singlerow['Action'] = "<a href='ihmbrfwi-create?FromDate=" . $RowLeaveList['sample_collection'] . "&ToDate=" . $RowLeaveList['sample_collection'] . "'><i class='mdi mdi-plus font-size-16'></i></a>";
            }
        }
        $data[] = $singlerow;
    }
}
echo json_encode([
    "draw"            => intval($request['draw']),
    "recordsTotal"    => intval($totalRecords),
    "recordsFiltered" => intval($totalFiltered),
    "data"            => $data, // could be empty, and that's okay
]);
exit;
