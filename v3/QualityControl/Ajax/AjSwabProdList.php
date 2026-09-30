<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// ini_set('log_errors', 1);
// ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/swabproducts_error.log');
// error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";

/* -----------------------------------------------------------
 *  Helper‑functions and lookup tables
 * ----------------------------------------------------------- */
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



// function setColor($val)

// {
//     $val = str_replace(',', '', $val);
//     $color = "";

//     if (is_numeric($val) && $val > 70000) {

//         $color = "Red";
//     } elseif (strtolower($val) == "positive") {

//         $color = "Red";
//     } elseif (strtolower($val) == "suspect") {

//         $color = "Orange";
//     }

//     return $color;
// }


function setColor($field, $val) {
    $val = trim(str_replace(',', '', $val));
    $raw = strtolower($val);
    $color = "";

   
    if ($raw === "positive") {
        $color = "red";
    } elseif ($raw === "suspect") {
        $color = "orange";
    }

        elseif ($field === 'TpcResult') {
            if (is_numeric($val) && $val > 70000) {
                $color = "red";   // numeric > 70,000
            } elseif (strtoupper(trim($val)) === "TNTC") {
                $color = "red";   // only TNTC, case-insensitive
            }
        }

        if (in_array($field, ['EcoliResult'])) {
            $color = "";
            
            // if (preg_match('/<([\d\.]+)MPN/i', $raw, $matches)) {
            //     $value = floatval($matches[1]);
            //     if ($value > 3) {
            //         $color = "red";
            //     }
            // }

        if (preg_match('/<([\d\.]+)CFU/i', $raw, $matches)) {
            $value = floatval($matches[1]);
            if ($value > 20) {
                $color = "red";
            }
        }
            // else {
            //     $color = "red";
            // }
        }

        elseif (in_array($field, ['SaureusResult'])) {
            $threshold = 10;
            if (preg_match('/<([\d\.]+)CFU/i', $raw, $matches)) {
                $value = floatval($matches[1]);
                if ($value > $threshold) {
                    $color = "red";
                }
            } else {
                $color = "red";
            }
        }

        elseif (in_array($field, ['VcholeraeResult','VparahaemolyticusResult','Salmonella'])) {
            $rawTrim = trim($raw);

            // Only set red if value is positive
            if (preg_match('/^positive$/i', $rawTrim)) {
                $color = "red";
            } else {
                $color = "";  
            }
        }

        return $color;
    }



function setDate($date) {
    if (isset($date) && $date) {
        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return date("d-m-Y", $timestamp);
        } else {
            
            return $date;
        }
    } else {
        return $date;
    }
}

/* -----------------------------------------------------------
 *  Reference data
 * ----------------------------------------------------------- */
$allLots        = fetchLookupMap($iconn, "SELECT RmLotID, LotNumber FROM RmLotDetatils", 'RmLotID', ['LotNumber']);

$allPlants      = fetchLookupMap($iconn, "SELECT ProcessingStationID, StationName FROM RmProcessingStations", 'ProcessingStationID', ['StationName']);

$allVarieties   = fetchLookupMap($iconn, "SELECT ProductID, ShortName FROM ScrmProductList", 'ProductID', ['ShortName']);

$allParameters  = fetchLookupMap($iconn, "SELECT TestID, TestName FROM QcTestList", 'TestID', ['TestName']);

$allMedia       = fetchLookupMap($iconn, "SELECT MediaID, MediaName FROM usedmediamaster", 'MediaID', ['MediaName']);

$allInstruments = fetchLookupMap($iconn, "SELECT InstrumentId, InstrumentName FROM instrumentmaster", 'InstrumentId', ['InstrumentName']);

// Technologist names
$sqlQuery      = "SELECT MIN(LUID) AS USER_ID, LbuName AS USER_NAME
                  FROM QcLabTechnologist
                  WHERE LbuStatus = 1
                  GROUP BY LbuName";
$stmtUsers     = $iconn->query($sqlQuery);

$RowUsersLists = [];

while ($RowUsers = $stmtUsers->fetch_array()) {

    $RowUsersLists[$RowUsers['USER_ID']] = $RowUsers['USER_NAME'];
}

/* -----------------------------------------------------------
 *  Incoming DataTables request
 * ----------------------------------------------------------- */
$request   = $_REQUEST;
$fromPage  = $request['fromPage'] ? $request['fromPage'] : '';
if ($fromPage === 'Create') {
    $fromPage = 'Create';
} else if ($fromPage === 'Listpage') {
    $fromPage = 'Listpage';
}



$columns = [

    'BatchNumber',

    'Plant',

    'SampleDate',

    'sample_collection',

    'DrawnBy',

    'GatePassNo',

    'AnalysisDate',

    'ReportDate',

    'Variety',

    'Parameter',

    'NameMediaUsed',

    'EquipmentUsed',

    'type_of_method',

    'TpcResult',

    'EcoliResult',

    'SaureusResult',

    'VcholeraeResult',

    'VparahaemolyticusResult',

    'Salmonella',

    'CheckedBy',

    'VerfiedBy',

    'Remarks'

];



$limit      = max(0, (int) $_POST['length']);

$offset     = max(0, (int) $_POST['start']);

$FromDate   = $_POST['fromDate'] ? $_POST['fromDate'] : '';

$ToDate     = $_POST['toDate']   ? $_POST['toDate']   : '';

$LotIds     = $_POST['lotIds']   ? $_POST['lotIds']   : [];

//dedcutation

$onlyDetected = isset($_POST['OnlyDetected']) && $_POST['OnlyDetected'] == '1';

$fyYear     = isset($_POST['fyYear']) ? intval($_POST['fyYear']) : 0;

$searchValue     = $iconn->real_escape_string($request['search']['value'] ? $request['search']['value'] : '');

$searchQueryParts = [];

/* -----------------------------------------------------------
 *  Lot‑number filter (checkboxes)
 * ----------------------------------------------------------- */
if (!empty($LotIds) && is_array($LotIds)) {

    $escapedLotIds = array_map('intval', $LotIds);

    $likeParts     = [];

    foreach ($escapedLotIds as $lotId) {

        $likeParts[] = "FIND_IN_SET('$lotId', BatchNumber) > 0";
    }

    if ($likeParts) {

        $searchQueryParts[] = '(' . implode(' OR ', $likeParts) . ')';
    }
}

/* -----------------------------------------------------------
 *  Global search box
 * ----------------------------------------------------------- */
if ($searchValue !== '') {

    $likeParts = [];

    // basic column search
    foreach ($columns as $col) {

        $likeParts[] = "$col LIKE '%$searchValue%'";
    }

    // lot numbers
    foreach ($allLots as $id => $lot) {

        if (stripos($lot['LotNumber'], $searchValue) !== false) {

            $likeParts[] = "FIND_IN_SET('$id', BatchNumber) > 0";
        }
    }

    // user names
    foreach ($RowUsersLists as $id => $name) {

        if (stripos($name, $searchValue) !== false) {

            $likeParts[] = "DrawnBy = $id OR CheckedBy = $id OR VerfiedBy = $id OR analysed_by = $id";
        }
    }

    // variety short names
    foreach ($allVarieties as $id => $variety) {

        if (stripos($variety['ShortName'], $searchValue) !== false) {

            $likeParts[] = "Variety = $id";
        }
    }

    $searchQueryParts[] = '(' . implode(' OR ', $likeParts) . ')';
}

/* -----------------------------------------------------------
 *  NEW  ▸ Date range filter
 * ----------------------------------------------------------- */
// $dateFilter = '';

// if ($FromDate !== '' && $ToDate !== '') {

//     $dateFilter = " AND AnalysisDate BETWEEN '$FromDate' AND '$ToDate'";
// }


$dateFilter = '';
if ($FromDate !== '' && $ToDate !== '') {
    // $dateFilter = " AND (SampleDate BETWEEN '$FromDate' AND '$ToDate' OR AnalysisDate BETWEEN '$FromDate' AND '$ToDate')";
   $dateFilter = " AND ReportDate BETWEEN '$FromDate' AND '$ToDate'";
}




if ($onlyDetected) {
    $searchQueryParts[] = "(
        (TpcResult REGEXP '^[0-9]+$' AND CAST(TpcResult AS UNSIGNED) > 70000)
        OR UPPER(TpcResult) = 'TNTC'
        OR EcoliResult REGEXP '<([0-9]+)CFU' AND CAST(REGEXP_SUBSTR(EcoliResult, '[0-9]+') AS UNSIGNED) > 20
        OR SaureusResult REGEXP '<([0-9]+)CFU' AND CAST(REGEXP_SUBSTR(SaureusResult, '[0-9]+') AS UNSIGNED) > 10
        OR LOWER(VcholeraeResult) = 'positive'
        OR LOWER(VparahaemolyticusResult) = 'positive'
        OR LOWER(Salmonella) = 'positive'
    )";
}



/* -----------------------------------------------------------
 *  Final WHERE clause
 * ----------------------------------------------------------- */
$searchQuery = $searchQueryParts ? ' AND ' . implode(' AND ', $searchQueryParts) : '';



$totalRecords  = $iconn->query("SELECT COUNT(*) AS count FROM swabproducts")->fetch_assoc()['count'];



$totalFiltered = $iconn->query("

        SELECT COUNT(*) AS count

        FROM swabproducts

        WHERE 1 = 1

              $dateFilter

              $searchQuery

    ")->fetch_assoc()['count'];



$sql = "

    SELECT *

    FROM swabproducts

    WHERE 1 = 1

          $dateFilter

          $searchQuery

" . (($limit > 0 || $offset > 0) ? " LIMIT $offset, $limit" : '');



$sql = "

    SELECT * FROM swabproducts WHERE 1 = 1 
    $dateFilter $searchQuery " . (($limit > 0 || $offset > 0) ? " LIMIT $offset, $limit" : '');




    
// echo print_r($sql);
// exit;




$result = $iconn->query($sql);

$data   = [];



if ($result && $result->num_rows > 0) {

    // $i = 0;
    $i = $offset; // adding this for correct numbering

    while ($RowLeaveList = $result->fetch_assoc()) {

        $i++;

        $singlerow = [];

        $singlerow['Sno'] = $i;



        $batchIDs   = explode(",", $RowLeaveList['BatchNumber']);

        $batchNames = array_map(function ($id) use ($allLots) {

            return $allLots[$id]['LotNumber'] ? $allLots[$id]['LotNumber'] : '';
        }, $batchIDs);

        $singlerow['BatchNumber'] = implode(",", array_filter($batchNames));

        /* Basic columns */
        $singlerow['SampleDate']  = setDate($RowLeaveList['SampleDate']);
        $singlerow['SampleCollection']  = setDate($RowLeaveList['sample_collection']);
        $singlerow['DrawnBy']     = $RowUsersLists[$RowLeaveList['DrawnBy']] ? $RowUsersLists[$RowLeaveList['DrawnBy']] : '';
        $singlerow['AnalysisDate'] = setDate($RowLeaveList['AnalysisDate']);
        $singlerow['ReportDate']   = setDate($RowLeaveList['ReportDate']);
        $singlerow['Variety']      = $allVarieties[$RowLeaveList['Variety']]['ShortName'] ? $allVarieties[$RowLeaveList['Variety']]['ShortName'] : '';
         
//         $singlerow['TpcResultValue'] = $RowLeaveList['TpcResult']; // plain value
// $singlerow['TpcResult'] = "<span style='background-color:" . setColor($RowLeaveList['TpcResult']) . "'>" . $RowLeaveList['TpcResult'] . "</span>"; // HTML

        $singlerow['TpcResultValue'] = $RowLeaveList['TpcResult'];
        $singlerow['EcoliResultValue'] = $RowLeaveList['EcoliResult'];
        $singlerow['SaureusResultValue'] = $RowLeaveList['SaureusResult'];
        $singlerow['VcholeraeResultValue'] = $RowLeaveList['VcholeraeResult'];
        $singlerow['VparahaemolyticusResultValue'] = $RowLeaveList['VparahaemolyticusResult'];
        $singlerow['SalmonellaValue'] = $RowLeaveList['Salmonella'];



        if ($fromPage === '') {

            $singlerow['Plant']         = $allPlants[$RowLeaveList['Plant']]['StationName'] ? $allPlants[$RowLeaveList['Plant']]['StationName'] : '';

            $singlerow['Parameter']     = $allParameters[$RowLeaveList['Parameter']]['TestName'] ? $allParameters[$RowLeaveList['Parameter']]['TestName'] : '';

            $singlerow['NameMediaUsed'] = $allMedia[$RowLeaveList['NameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['NameMediaUsed']]['MediaName'] : '';

            $singlerow['EquipmentUsed'] = $allInstruments[$RowLeaveList['ec_equipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['ec_equipmentUsed']]['InstrumentName'] : '';

            $singlerow['type_of_method'] = $RowLeaveList['type_of_method'] ? $RowLeaveList['type_of_method'] : '';



            // $singlerow['TpcResult']     = "<span style='background-color:" . setColor($RowLeaveList['TpcResult']) . "'>" . $RowLeaveList['TpcResult'] . "</span>";
    
            $singlerow['TpcResult'] =  "<span style='color:" . setColor('TpcResult', $RowLeaveList['TpcResult']) . "'>"  . $RowLeaveList['TpcResult'] . "</span>";

            $singlerow['tp_completed_date'] = setDate($RowLeaveList['tp_completed_date']);

            $singlerow['ec_parameter']     = $allParameters[$RowLeaveList['ec_parameter']]['TestName'] ? $allParameters[$RowLeaveList['ec_parameter']]['TestName'] : '';

            $singlerow['ec_nameMediaUsed'] = $allMedia[$RowLeaveList['ec_nameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['ec_nameMediaUsed']]['MediaName'] : '';

            $singlerow['ec_equipmentUsed'] = $allInstruments[$RowLeaveList['ec_equipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['ec_equipmentUsed']]['InstrumentName'] : '';

            
           $singlerow['EcoliResult'] = "<span style='color:" . setColor('EcoliResult', $RowLeaveList['EcoliResult']) . "'>"  . $RowLeaveList['EcoliResult'] . "</span>";
           
           $singlerow['ec_completed_date'] = setDate($RowLeaveList['ec_completed_date']);

            $singlerow['sa_parameter']     = $allParameters[$RowLeaveList['sa_parameter']]['TestName'] ? $allParameters[$RowLeaveList['sa_parameter']]['TestName'] : '';

            $singlerow['sa_nameMediaUsed'] = $allMedia[$RowLeaveList['sa_nameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['sa_nameMediaUsed']]['MediaName'] : '';

            $singlerow['sa_equipmentUsed'] = $allInstruments[$RowLeaveList['sa_equipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['sa_equipmentUsed']]['InstrumentName'] : '';

            // $singlerow['SaureusResult']    = "<span style='background-color:" . setColor($RowLeaveList['SaureusResult']) . "'>" . $RowLeaveList['SaureusResult'] . "</span>";
            
           $singlerow['SaureusResult'] ="<span style='color:" . setColor('SaureusResult', $RowLeaveList['SaureusResult']) . "'>". $RowLeaveList['SaureusResult'] . "</span>";
          
           $singlerow['sa_completed_date'] = setDate($RowLeaveList['sa_completed_date']);

            $singlerow['vc_parameter']     = $allParameters[$RowLeaveList['vc_parameter']]['TestName'] ? $allParameters[$RowLeaveList['vc_parameter']]['TestName'] : '';

            $singlerow['vc_nameMediaUsed'] = $allMedia[$RowLeaveList['vc_nameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['vc_nameMediaUsed']]['MediaName'] : '';

            $singlerow['vc_equipmentUsed'] = $allInstruments[$RowLeaveList['vc_equipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['vc_equipmentUsed']]['InstrumentName'] : '';

            // $singlerow['VcholeraeResult']  = "<span style='background-color:" . setColor($RowLeaveList['VcholeraeResult']) . "'>" . $RowLeaveList['VcholeraeResult'] . "</span>";
           $singlerow['VcholeraeResult'] = "<span style='color:" . setColor('VcholeraeResult', $RowLeaveList['VcholeraeResult']) . "'>"  . $RowLeaveList['VcholeraeResult'] . "</span>";

            $singlerow['vc_completed_date'] = setDate($RowLeaveList['vc_completed_date']);

            $singlerow['vp_parameter']     = $allParameters[$RowLeaveList['vp_parameter']]['TestName'] ? $allParameters[$RowLeaveList['vp_parameter']]['TestName'] : '';

            $singlerow['vp_nameMediaUsed'] = $allMedia[$RowLeaveList['vp_nameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['vp_nameMediaUsed']]['MediaName'] : '';

            $singlerow['vp_equipmentUsed'] = $allInstruments[$RowLeaveList['vp_equipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['vp_equipmentUsed']]['InstrumentName'] : '';

            // $singlerow['VparahaemolyticusResult'] = "<span style='background-color:" . setColor($RowLeaveList['VparahaemolyticusResult']) . "'>" . $RowLeaveList['VparahaemolyticusResult'] . "</span>";
           $singlerow['VparahaemolyticusResult'] = "<span style='color:" . setColor('VparahaemolyticusResult', $RowLeaveList['VparahaemolyticusResult']) . "'>"  . $RowLeaveList['VparahaemolyticusResult'] . "</span>";
           
           $singlerow['vp_completed_date'] = setDate($RowLeaveList['vp_completed_date']);

            $singlerow['sal_parameter']     = $allParameters[$RowLeaveList['sal_parameter']]['TestName'] ? $allParameters[$RowLeaveList['sal_parameter']]['TestName'] : '';

            $singlerow['sal_nameMediaUsed'] = $allMedia[$RowLeaveList['sal_nameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['sal_nameMediaUsed']]['MediaName'] : '';

            $singlerow['sal_equipmentUsed'] = $allInstruments[$RowLeaveList['sal_equipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['sal_equipmentUsed']]['InstrumentName'] : '';

            // $singlerow['Salmonella']        = "<span style='background-color:" . setColor($RowLeaveList['Salmonella']) . "'>" . $RowLeaveList['Salmonella'] . "</span>";
           $singlerow['Salmonella'] ="<span style='color:" . setColor('Salmonella', $RowLeaveList['Salmonella']) . "'>" . $RowLeaveList['Salmonella'] . "</span>";
           
           $singlerow['sal_completed_date'] = setDate($RowLeaveList['sal_completed_date']);

            $singlerow['analysed_by'] = $RowUsersLists[$RowLeaveList['analysed_by']] ? $RowUsersLists[$RowLeaveList['analysed_by']] : '';

            $singlerow['CheckedBy']   = $RowUsersLists[$RowLeaveList['CheckedBy']] ? $RowUsersLists[$RowLeaveList['CheckedBy']] : '';

            $singlerow['VerfiedBy']   = $RowUsersLists[$RowLeaveList['VerfiedBy']] ? $RowUsersLists[$RowLeaveList['VerfiedBy']] : '';

            $singlerow['Remarks']     = $RowLeaveList['Remarks'];
        } else {
            /* Action buttons for Create/Listpage */
            if ($fromPage === 'Create') {
                $singlerow['Action'] =
                    "<a target='_blank' href='in-house-microbiology-report-for-product-create?FromDate=$FromDate&ToDate=$ToDate&id={$RowLeaveList['id']}'>
                        <i class='mdi mdi-pencil font-size-16'></i></a>
                     <button class='btn btn-link text-danger btnDelete' style='position:relative;top:-3px'
                             data-id='{$RowLeaveList['id']}' title='Delete'>
                        <i class='mdi mdi-trash-can font-size-16'></i></button>";

            } elseif ($fromPage === 'Listpage') {
                $singlerow['Action'] =
                    "<a href='in-house-microbiology-report-for-product-create?FromDate={$RowLeaveList['AnalysisDate']}&ToDate={$RowLeaveList['AnalysisDate']}'>
                    <i class='mdi mdi-plus font-size-16'></i></a>";
            }
        }

        $data[] = $singlerow;
    }
}


header('Content-Type: application/json');
echo json_encode([
    "draw"            => isset($request['draw']) ? intval($request['draw']) : 0,
    "recordsTotal"    => intval($totalRecords),
    "recordsFiltered" => intval($totalFiltered),
    "data"            => $data
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

exit;
