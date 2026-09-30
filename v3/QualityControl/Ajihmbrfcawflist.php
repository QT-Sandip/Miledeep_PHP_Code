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

function setColor($res, $val){
    $chkval = 20;
    if( $res == "result1" ){
        $chkval = 35;    
    }
    $color="";
    if( is_numeric($val) && $val>$chkval ){
        $color = "Red";
    }    
    return $color;
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

$fromPage = "";
if (isset($request['fromPage']) && $request['fromPage'] == 'Create') {
    $fromPage = "Create";
} else if (isset($request['fromPage']) && $request['fromPage'] == 'Listpage') {
    $fromPage = "Listpage";
}

$columns = [`mbwi_id`, `report_type`, `sample_drawn_by`, `sample_collection`, `plant_id`, `sample_desc`, `location`, `tap_no_for_water`, `itype_of_ice`, `tpc_paramater`, `tpc_media`, `tpc_equipement`, `tpc_result1`, `tpc_result2`, `tpc_completion_date`, `vcholerae_paramater`, `vcholerae_media`, `vcholerae_equipement`, `vcholerae_result1`, `vcholerae_result2`, `vcholerae_completion_date`, `analysed_by`, `checked_by`, `verified_by`, `remarks`];

$limit  = (int) $_POST['length'];
$limit  = $limit < 0 ? 20 : $limit;
$offset = max(0, (int) $_POST['start']);

$FromDate = $_POST['fromDate'];
$ToDate   = $_POST['toDate'];

$OrderBYVal  = intval($request['order'][0]['column']);
$searchValue = $iconn->real_escape_string($request['search']['value']);

$searchQueryParts = [];

if (!empty($FromDate) && !empty($ToDate) && $fromPage != "Create") {
    $searchQueryParts[] = " AnalysisDate BETWEEN '$FromDate' and '$ToDate' ";
}

if( !empty($request['report_type']) ){
    $searchQueryParts[] = " report_type = '".$request['report_type']."' ";
}

if (!empty($searchValue)) {
    $searchQueryParts[] = "(" . $columns[0] . " LIKE '%" . $searchValue . "%' OR " . $columns[1] . " LIKE '%" . $searchValue . "%' OR " . $columns[2] . " LIKE '%" . $searchValue . "%'  OR " . $columns[3] . " LIKE '%" . $searchValue . "%' OR " . $columns[4] . " LIKE '%" . $searchValue . "%' OR " . $columns[5] . " LIKE '%" . $searchValue . "%' OR " . $columns[6] . " LIKE '%" . $searchValue . "%' OR " . $columns[7] . " LIKE '%" . $searchValue . "%' OR " . $columns[8] . " LIKE '%" . $searchValue . "%')";
}

$searchQuery = count($searchQueryParts) > 0 ? ' AND ' . implode(' AND ', $searchQueryParts) : '';

$totalRecords  = $iconn->query("SELECT COUNT(*) as count FROM micro_biology_ice_chutes_water_tanks")->fetch_assoc()['count'];
//echo "SELECT COUNT(*) as count FROM micro_biology_ice_chutes_water_tanks Where 1=1 $searchQuery"; exit;
$totalFiltered = $iconn->query("SELECT COUNT(*) as count FROM micro_biology_ice_chutes_water_tanks Where 1=1 $searchQuery ")->fetch_assoc()['count'];
$data          = [];
$sql           = "SELECT * from micro_biology_ice_chutes_water_tanks where 1=1 $searchQuery order by mbwic_id desc
             LIMIT $offset, $limit";
//echo $sql; exit;
$result = $iconn->query($sql);
if ($result && $result->num_rows > 0) {
    $i = 0;
    while ($RowLeaveList = $result->fetch_assoc()) {
        $i++;
        $singlerow = array();

        $singlerow['Sno'] = $i;
        
        $singlerow['plant_name'] = $allPlants[$RowLeaveList['Plant_id']]['StationName'] ? $allPlants[$RowLeaveList['Plant_id']]['StationName'] : '';                
        $singlerow['sample_collection_date']  = $RowLeaveList['sample_collection_date'];
        $singlerow['sample_drawn_by']     = $RowUsersLists[$RowLeaveList['sample_drawn_by']] ? $RowUsersLists[$RowLeaveList['sample_drawn_by']] : '';

        $singlerow['analysed_by'] = $RowUsersLists[$RowLeaveList['analysed_by']] ? $RowUsersLists[$RowLeaveList['analysed_by']] : '';
        $singlerow['checked_by']   = $RowUsersLists[$RowLeaveList['checked_by']] ? $RowUsersLists[$RowLeaveList['checked_by']] : '';
        $singlerow['verified_by']      = $RowUsersLists[$RowLeaveList['verified_by']] ? $RowUsersLists[$RowLeaveList['verified_by']] : '';
        if ($fromPage == "") {
            $singlerow['plant_name'] = $allPlants[$RowLeaveList['Plant_id']]['StationName'] ? $allPlants[$RowLeaveList['Plant_id']]['StationName'] : '';
            $singlerow['sample_desc'] = $RowLeaveList['sample_desc'];
            $singlerow['sample_collection_area'] = $RowLeaveList['sample_collection_area'];
            $singlerow['Dateofcompletion'] = $RowLeaveList['Dateofcompletion'];
            $singlerow['AnalysisDate'] = $RowLeaveList['AnalysisDate'];

            
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
            
            $tpc_bgcolor = setColor('result1',$RowLeaveList['tpc_result1']);
            $tpc_class ="";    
            if($tpc_bgcolor){
                $tpc_class="redbg";
            }
            $singlerow['tpc_result']     = $RowLeaveList['tpc_result'];            
            $singlerow['tpc_completion_date']     = $RowLeaveList['tpc_completion_date'];

            //equipmentused

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

            $tpc_bgcolor = setColor('result1',$RowLeaveList['vcholerae_result1']);
            $tpc_class ="";    
            if($tpc_bgcolor){
                $tpc_class="redbg";
            }
            $singlerow['vcholerae_result']     = $RowLeaveList['vcholerae_result'];
            $singlerow['vcholerae_completion_date']     = $RowLeaveList['vcholerae_completion_date'];
           
                    
            $singlerow['vcholerae_completion_date']     = $RowLeaveList['vcholerae_completion_date'];

            $singlerow['analysed_by'] = $RowUsersLists[$RowLeaveList['analysed_by']] ? $RowUsersLists[$RowLeaveList['analysed_by']] : '';
            $singlerow['checked_by']   = $RowUsersLists[$RowLeaveList['checked_by']] ? $RowUsersLists[$RowLeaveList['checked_by']] : '';
            $singlerow['verified_by']   = $RowUsersLists[$RowLeaveList['verified_by']] ? $RowUsersLists[$RowLeaveList['verified_by']] : '';
            $singlerow['remarks']     = $RowLeaveList['remarks'];
        } else {
            if ($fromPage == "Create") {
                $singlerow['Action'] = "<a href='ihmbrficawt-create?FromDate=" . $FromDate . "&ToDate=" . $ToDate . "&id=" . $RowLeaveList['mbwic_id'] . "'><i class='mdi mdi-pencil font-size-16'></i></a>
                    <button class='btn btn-link text-danger  btnDelete' style='position: relative;top: -3px' data-id='" . $RowLeaveList['mbwic_id'] . "' data-toggle='tooltip' data-placement='top' title='' value='' data-original-title='Delete'>
                        <i class='mdi mdi-trash-can font-size-16'></i></button>
                ";
            } else if ($fromPage == "Listpage") {
                $singlerow['Action'] = "<a href='ihmbrficawt-create?FromDate=" . $RowLeaveList['AnalysisDate'] . "&ToDate=" . $RowLeaveList['AnalysisDate'] . "'><i class='mdi mdi-plus font-size-16'></i></a>";
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