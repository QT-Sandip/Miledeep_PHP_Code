<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

include_once ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/OrderStatusHelper.php';
$AccessType = $_SESSION['AccessType']?$_SESSION['AccessType']:1;
$UnitID     = $_SESSION['UnitID']?$_SESSION['UnitID']:$_GET['PID'];

$Qry = "";
if ($AccessType != 1 && $UnitID != 0) {
    $Qry = " AND PlantID = $UnitID ";
}else if( isset($_GET['PID']) && $_GET['PID']>0 ){
    $pid = (int)$_GET['PID']; // Safely convert to a number
    $Qry = " AND PlantID = $pid "; // No quotes needed around $pid now
}

$PoQry = "";
if ($AccessType != 1 && $UnitID != 0) {
    $PoQry = " AND FIND_IN_SET('$UnitID', SPD.ProductionPlantID) > 0 ";
}

$StoreQry = "";
if ($AccessType != 1 && $UnitID != 0) {
    $StoreQry = " AND CSD.PlantID = $UnitID ";
}

$SelectedSpecies = isset($_GET['Species']) ? trim($_GET['Species']) : (isset($_GET['species']) ? trim($_GET['species']) : '');
if ($SelectedSpecies !== '') {
    $safeSpecies = $iconn->real_escape_string($SelectedSpecies);
    $speciesFilter     = " AND PIVL.Species = '$safeSpecies'";
    $speciesFilterVL   = " AND VL.Species = '$safeSpecies'";
    $speciesFilterV    = " AND V.Species = '$safeSpecies'";
    $speciesFilterSPVL = " AND SPVL.Species = '$safeSpecies'";
} else {
    $speciesFilter     = '';
    $speciesFilterVL   = '';
    $speciesFilterV    = '';
    $speciesFilterSPVL = '';
}

$action = (isset($_GET['action']) && $_GET['action']) ? $_GET['action'] : '';
$SelectedUom = isset($_GET['UOM']) ? strtoupper(trim($_GET['UOM'])) : 'KGS';
if (!in_array($SelectedUom, ['KGS', 'MCS'], true)) {
    $SelectedUom = 'KGS';
}
$SelectedUomLabel = ($SelectedUom === 'MCS') ? 'Mcs' : 'Kgs';

$fromDateInput = isset($_GET['FromDate']) ? trim($_GET['FromDate']) : '';
$toDateInput = isset($_GET['ToDate']) ? trim($_GET['ToDate']) : '';
$yearInput = isset($_GET['Year']) ? intval($_GET['Year']) : 0;
$monthInput = isset($_GET['Month']) ? intval($_GET['Month']) : 0;
$fromDateObj = DateTime::createFromFormat('Y-m-d', $fromDateInput);
$toDateObj = DateTime::createFromFormat('Y-m-d', $toDateInput);
if ($fromDateObj && $fromDateObj->format('Y-m-d') === $fromDateInput && $toDateObj && $toDateObj->format('Y-m-d') === $toDateInput) {
    $FromDate = $fromDateInput;
    $ToDate = $toDateInput;
} else if ($yearInput >= 2000 && $yearInput <= 2100 && $monthInput >= 1 && $monthInput <= 12) {
    $FromDate = sprintf('%04d-%02d-01', $yearInput, $monthInput);
    $ToDate = date('Y-m-t', strtotime($FromDate));
} else {
    $FromDate = date('Y-m-01');
    $ToDate = date('Y-m-t');
}

function getShipmentStatusWrappedQuery($fromDate, $toDate, $poQry)
{
    global $UnitID;
    global $speciesFilterSPVL;
    $unitcsfilter = "";
    if($UnitID){
        $unitcsfilter = " AND RPS.ProcessingStationID = ". $UnitID;
    }
    return "
        SELECT
            COALESCE(SPD.PoNo, 'NA') AS PoNo,
            SPVL.ProductDescription,
            SPGD.Grade,
            SPGD.Mcs AS ActualMcs,
            DATE_FORMAT(SPD.PoDate, '%d-%m-%Y') AS PoDate,
            DATE_FORMAT(SPD.ShipByDate, '%d-%m-%Y') AS ShipByDate,
            DATE_FORMAT(SCI.StockOutDate, '%d-%m-%Y') AS OutDate,
            'Shipments' AS DerivedStatus
        FROM ScrmCustomInvoices AS SCI
        LEFT JOIN ScrmPoData AS SPD
            ON SPD.PoID = SCI.PiID
        LEFT JOIN ScrmPoGradeData AS SPGD
            ON SPGD.InvId = SPD.PoID
        LEFT JOIN ScrmPIVarietyList AS SPVL
            ON SPVL.VarietyID = SPGD.VarietyID
        LEFT JOIN CsStockData AS CPID
            ON CPID.PoGradeId = SPGD.ID
        LEFT JOIN CsStockOutDetails AS CSOD
            ON CSOD.StockID = CPID.StockID
        LEFT JOIN AdminExporterDetails AS AED
            ON AED.ExpID = SPD.ProcessorID
        LEFT JOIN RmProcessingStations AS RPS
            ON RPS.ProdCode = AED.InvShortCode
        WHERE
            SCI.StockOutDate BETWEEN '$fromDate' AND '$toDate'
            $unitcsfilter
            $speciesFilterSPVL
            AND (CSOD.OutDate IS NOT NULL AND CSOD.OutDate <> '')
            AND CSOD.OutType = 'Shipment Out'
        GROUP BY SPGD.ID
    ";
}

/* ================= SIMPLE HELPERS ================= */
function getShipmentProcessorID($iconn) {
    global $UnitID;

    $sqlGetProid = " SELECT * FROM `AdminExporterDetails` where ProcessingStationID = '".$UnitID."' ";
    $restult = $iconn->query($sqlGetProid);
    $row = $restult->fetch_assoc();
    $proccessorId = 0 ;
    if( isset($row['ExpID']) && $row['ExpID']>0 ){
        $proccessorId = $row['ExpID'];
    }
    return $proccessorId;
}
function getShipmentStockOutIDs($iconn, $FromDate, $ToDate, $PoQry) {
    global $UnitID;
    global $speciesFilterSPVL;
    $proccessorId = getShipmentProcessorID($iconn);
    if($UnitID){
    $sql = "
        SELECT DISTINCT
            SPD.PoID
        FROM ScrmPoData SPD
        INNER JOIN ScrmCustomInvoices SCI
            ON SCI.PiID = SPD.PoID
        LEFT JOIN ScrmPoGradeData SPGD
            ON SPGD.InvId = SPD.PoID
        LEFT JOIN ScrmPIVarietyList SPVL
            ON SPVL.VarietyID = SPGD.VarietyID
        WHERE SCI.StockOutDate BETWEEN '$FromDate' AND '$ToDate'
          AND SCI.StockOutDate IS NOT NULL
          AND SCI.StockOutDate <> ''
          AND SPD.ProcessorID = $proccessorId
          $speciesFilterSPVL
    ";
    }else{
      $sql = "
        SELECT DISTINCT
            SPD.PoID
        FROM ScrmPoData SPD
        INNER JOIN ScrmCustomInvoices SCI
            ON SCI.PiID = SPD.PoID
        LEFT JOIN ScrmPoGradeData SPGD
            ON SPGD.InvId = SPD.PoID
        LEFT JOIN ScrmPIVarietyList SPVL
            ON SPVL.VarietyID = SPGD.VarietyID
        WHERE SCI.StockOutDate BETWEEN '$FromDate' AND '$ToDate'
          AND SCI.StockOutDate IS NOT NULL
          AND SCI.StockOutDate <> ''
          $speciesFilterSPVL
    ";
    }
    $res = $iconn->query($sql);
    $ids = [];
    while ($r = $res->fetch_assoc()) {
        $ids[] = (int)$r['PoID'];
    }
    return $ids;
}

function getShipmentOutDoneIDs($iconn, $FromDate, $ToDate, $PoQry) {
    global $UnitID;
    global $speciesFilterSPVL;
    if($UnitID){
        $sql = "
            SELECT DISTINCT SPD.PoID
            FROM ScrmPoData AS SPD
            INNER JOIN ScrmCustomInvoices AS SCI
                ON SCI.PiID = SPD.PoID
            INNER JOIN AdminExporterDetails AS AED
                ON AED.ExpID = SPD.ProcessorID
            INNER JOIN RmProcessingStations AS RPS
                ON RPS.ProdCode = AED.InvShortCode
            LEFT JOIN ScrmPoGradeData AS SPGD
                ON SPGD.InvId = SPD.PoID
            LEFT JOIN ScrmPIVarietyList AS SPVL
                ON SPVL.VarietyID = SPGD.VarietyID
            LEFT JOIN CsStockData AS CPID
                ON CPID.PoGradeId = SPGD.ID
            LEFT JOIN CsStockOutDetails AS CSOD
                ON CSOD.StockID = CPID.StockID
            WHERE SCI.StockOutDate BETWEEN '$FromDate' AND '$ToDate'
              AND RPS.ProcessingStationID = '$UnitID'
              AND CSOD.OutType = 'Shipment Out'
              AND CSOD.OutDate IS NOT NULL
              AND CSOD.OutDate <> ''
              $speciesFilterSPVL
        ";
    }else{
        $sql = "
        SELECT DISTINCT SPD.PoID
        FROM ScrmPoData AS SPD
        INNER JOIN ScrmCustomInvoices AS SCI
            ON SCI.PiID = SPD.PoID
        INNER JOIN AdminExporterDetails AS AED
            ON AED.ExpID = SPD.ProcessorID
        INNER JOIN RmProcessingStations AS RPS
            ON RPS.ProdCode = AED.InvShortCode
        LEFT JOIN ScrmPoGradeData AS SPGD
            ON SPGD.InvId = SPD.PoID
        LEFT JOIN ScrmPIVarietyList AS SPVL
            ON SPVL.VarietyID = SPGD.VarietyID
        LEFT JOIN CsStockData AS CPID
            ON CPID.PoGradeId = SPGD.ID
        LEFT JOIN CsStockOutDetails AS CSOD
            ON CSOD.StockID = CPID.StockID
        WHERE SCI.StockOutDate BETWEEN '$FromDate' AND '$ToDate'

          AND CSOD.OutType = 'Shipment Out'
          AND CSOD.OutDate IS NOT NULL
          AND CSOD.OutDate <> ''
          $speciesFilterSPVL
    ";
    }
    $res = $iconn->query($sql);
    $ids = [];
    while ($r = $res->fetch_assoc()) {
        $ids[] = (int)$r['PoID'];
    }
    return array_values(array_unique($ids));
}

// Pending Shipment
function getShipmentPendingIDs($iconn, $FromDate, $ToDate, $PoQry) {
    global $UnitID;
    global $speciesFilterSPVL;
    $sqlGetProid = " SELECT * FROM `AdminExporterDetails` where ProcessingStationID = '".$UnitID."' ";
    $restult = $iconn->query($sqlGetProid);
    $row = $restult->fetch_assoc();
    $proccessorId = 0 ;
    if( isset($row['ExpID']) && $row['ExpID']>0 ){
        $proccessorId = $row['ExpID'];
    }
    if($UnitID){
        $sql = "
            SELECT DISTINCT
          SPD.PoID,
          SPD.PoNo,
          DATE_FORMAT(SPD.PoDate, '%d-%m-%Y') AS PoDate,
    SCI.StockOutDate
      FROM ScrmPoData SPD
      LEFT JOIN ScrmCustomInvoices SCI
          ON SCI.PiID = SPD.PoID
      LEFT JOIN ScrmPoGradeData SPGD
          ON SPGD.InvId = SPD.PoID
      LEFT JOIN ScrmPIVarietyList SPVL
          ON SPVL.VarietyID = SPGD.VarietyID
      WHERE  SPD.ShipByDate BETWEEN '$FromDate' AND '$ToDate'
        AND SPD.ShipByDate IS NOT NULL
        AND SPD.ShipByDate <> ''
        AND (SCI.StockOutDate IS NULL OR SCI.StockOutDate = ''

        )
           and SPD.ProcessorID = $proccessorId
           $speciesFilterSPVL
        ";
    }else{
        $sql = "
        SELECT DISTINCT
      SPD.PoID,
      SPD.PoNo,
      DATE_FORMAT(SPD.PoDate, '%d-%m-%Y') AS PoDate,
SCI.StockOutDate
  FROM ScrmPoData SPD
  LEFT JOIN ScrmCustomInvoices SCI
      ON SCI.PiID = SPD.PoID
  LEFT JOIN ScrmPoGradeData SPGD
      ON SPGD.InvId = SPD.PoID
  LEFT JOIN ScrmPIVarietyList SPVL
      ON SPVL.VarietyID = SPGD.VarietyID
  WHERE  SPD.ShipByDate BETWEEN '$FromDate' AND '$ToDate'
    AND SPD.ShipByDate IS NOT NULL
    AND SPD.ShipByDate <> ''
    AND (SCI.StockOutDate IS NULL OR SCI.StockOutDate = ''

    )
    $speciesFilterSPVL

    ";
    }
    $res = $iconn->query($sql);
    $ids = [];
    while ($r = $res->fetch_assoc()) {
        $ids[] = (int)$r['PoID'];
    }
    return $ids;
}

// Pending Shipment = Stock Out / Shipped - Shipments
function getStockoutPendingIDs($iconn, $FromDate, $ToDate, $PoQry) {
    $stockOutIDs = getShipmentStockOutIDs($iconn, $FromDate, $ToDate, $PoQry);
    $shipmentOutDoneIDs = getShipmentOutDoneIDs($iconn, $FromDate, $ToDate, $PoQry);

    return array_values(array_diff($stockOutIDs, $shipmentOutDoneIDs));
}

// Stock Out Done
function getStockOutDoneIDs($iconn) {
    global $UnitID;
    global $speciesFilterV;
    if($UnitID){
        $sql = "
            SELECT DISTINCT CSD.PoID
            FROM CsStockData CSD
            JOIN CsStockOutDetails CSOD
                ON CSOD.StockID = CSD.StockID
            LEFT JOIN ScrmPIVarietyList V
                ON V.VarietyID = CSD.VarietyID
            WHERE
                CSD.StockStatus = 2
                AND CSOD.OutType = 'Shipment Out'
                AND CSD.UnitID = $UnitID
                $speciesFilterV
        ";
    }else{
        $sql = "
            SELECT DISTINCT CSD.PoID
            FROM CsStockData CSD
            JOIN CsStockOutDetails CSOD
                ON CSOD.StockID = CSD.StockID
            LEFT JOIN ScrmPIVarietyList V
                ON V.VarietyID = CSD.VarietyID
            WHERE
                CSD.StockStatus = 2
                AND CSOD.OutType = 'Shipment Out'
                $speciesFilterV
        ";
    }
    $res = $iconn->query($sql);
    $ids = [];
    while ($r = $res->fetch_assoc()) {
        $ids[] = (int)$r['PoID'];
    }
    return $ids;
}

if( $action == "store_counts" ){
    $ColdstoreID = '';
    $Blocks      = '';

    $AccessType = $_SESSION['AccessType'];
    $UnitID = $_SESSION['UnitID'];
    $storeIDs = $_SESSION["StoreIDs"];
    if ($AccessType != 1) {
        if ( $UnitID != 0) {
            $ColdstoreID = $UnitID;
        }
        if ($storeIDs  != 0) {
            $Blocks = $storeIDs;
        }
    }else {
        if (!empty($_GET['PID'])) {
            $ColdstoreID = $_GET['PID'];
        }
    }
    $SelectedUom = isset($_GET['UOM']) ? strtoupper(trim($_GET['UOM'])) : 'KGS';
    $bodyContent = array(
        'ColdstoreID' => $ColdstoreID,   // ✅ from session / GET logic
        'Blocks'      => $Blocks,        // ✅ from StoreIDs
        'TenantID' => $_POST["stockBlgsTo"],
        'VarietyID' => $_POST["variety"],
        'PackData' => $_POST["packingType"],
        'Category' => $_POST["pdCategory"],
        'Group' => $_POST["group"],
        'Grade' => $_POST["grade"],
        'PackingStyle' => $_POST["pckgStyle"],
        'FreezingType' => $_POST["freezingType"],
        'TypeProduct' => $_POST["productType"],
        'Soaking' => $_POST["soaking"],
        'AntiBiotic' => $_POST["abStatus"],
        'PONumber' => $_POST["poNumber"],
        'Species' => $SelectedSpecies,
        'Brand' => $_POST["brand"],
        'Type' => $_POST["Type"],
        'ConvertLooseToMcs' => $convertLooseToMcs,
        'PageNo' => 1,
        'SearchKey' => '',
        'LoadAll' => 1   // ✅ important for dashboard
    );

    $api = API_STOCK_REPORT;
    $response = callAPI($api, $bodyContent);

    $totalMcs        = $response['ProductTotal']['TotalMcs'];
    $totalLoose      = $response['ProductTotal']['TotalLooseBags'];
    $totalKgs        = $response['ProductTotal']['TotalWeight'];
    if ($SelectedUom === 'MCS') {

        if ($convertLooseToMcs == 1) {
            // Already handled inside API totals
            $CurrentStockValue = $totalMcs;
        } else {
            $CurrentStockValue = $totalMcs;
        }

    } elseif ($SelectedUom === 'KGS') {

        $CurrentStockValue = $totalKgs;
    }

    $countTodayCurrentStocks = $CurrentStockValue;
    echo $countTodayCurrentStocks; exit;
} elseif( $action == "stockagepopup" ){

    $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
    $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
    $length = isset($_GET['length']) ? intval($_GET['length']) : 10;
    $searchValue = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';
    $ageGroup = isset($_GET['agegroup'])
    ? trim(str_replace(
        [' Days', '–', '—'], // remove text + normalize dashes
        ['', '-', '-'],
        $_GET['agegroup']
    ))
    : '';
    $selectedPid = isset($_GET['PID']) ? intval($_GET['PID']) : 0;



    $allowedAgeGroups = ['0-30', '31-90', '91-180', '181-365', '>365'];
    if (!in_array($ageGroup, $allowedAgeGroups, true)) {
        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'notinarray'=>'yes'
        ]);
        exit;
    }

    $stockFilter = " AND S.StockStatus = 1 AND (S.AvalMcs > 0 OR S.LooseBags > 0) ";
    if ($AccessType != 1 && $UnitID != 0) {
        $stockFilter .= " AND S.PlantID = " . (int)$UnitID . " ";
    } else if ($selectedPid > 0) {
        $stockFilter .= " AND S.UnitID = " . $selectedPid . " ";
    }

    $bucketCondition = " 1 ";
    if ($ageGroup === '0-30') {
        $bucketCondition = "  DATEDIFF(CURDATE(), S.ProductionDate) <= 30";
    } else if ($ageGroup === '31-90') {
        $bucketCondition = "  DATEDIFF(CURDATE(), S.ProductionDate) BETWEEN 31 AND 90";
    } else if ($ageGroup === '91-180') {
        $bucketCondition = "  DATEDIFF(CURDATE(), S.ProductionDate) BETWEEN 91 AND 180";
    } else if ($ageGroup === '181-365') {
        $bucketCondition = "  DATEDIFF(CURDATE(), S.ProductionDate) BETWEEN 181 AND 365";
    } else if ($ageGroup === '>365') {
        $bucketCondition = "  DATEDIFF(CURDATE(), S.ProductionDate) > 365";
    }

    $searchCondition = "";
    if ($searchValue !== '') {
        $safeSearch = $iconn->real_escape_string($searchValue);
        $searchCondition = "
            AND (
                COALESCE(V.ShortName, '') LIKE '%$safeSearch%'
                OR COALESCE(APS.PackingStyle, '') LIKE '%$safeSearch%'
                OR COALESCE(S.PackingTypeID, '') LIKE '%$safeSearch%'
                OR COALESCE(S.Grade, '') LIKE '%$safeSearch%'
                OR COALESCE(S.BatchCode, '') LIKE '%$safeSearch%'
                OR COALESCE(CST.StoreName, '') LIKE '%$safeSearch%'
                OR COALESCE(B.BlockName, '') LIKE '%$safeSearch%'
                OR COALESCE(F.FloorName, '') LIKE '%$safeSearch%'
                OR COALESCE(R.RackName, '') LIKE '%$safeSearch%'
            )
        ";
    }

    $baseFrom = "
        FROM CsStockData S
        LEFT JOIN ScrmPIVarietyList V
            ON V.VarietyID = S.VarietyID
        LEFT JOIN AdminPackingStyles APS
            ON APS.PCKID = S.PackingStyleID
        LEFT JOIN CsStoresRackData R
            ON R.RowID = S.StoreRackID
        LEFT JOIN CsStoresBlockData B
            ON B.BlockID = R.BlockID
        LEFT JOIN CsStoresFloorData F
            ON F.FloorID = R.FloorID
        LEFT JOIN CsStores CST
            ON CST.StoreID = S.UnitID
        WHERE $bucketCondition
        $stockFilter
        $speciesFilterV
    ";

    $totalQuery = "SELECT COUNT(*) AS total $baseFrom";

    $recordsTotal = (int)$iconn->query($totalQuery)->fetch_assoc()['total'];

    $filteredQuery = "SELECT COUNT(*) AS total $baseFrom $searchCondition";
    $recordsFiltered = (int)$iconn->query($filteredQuery)->fetch_assoc()['total'];

    $dataQuery = "
        SELECT
        S.ProductionDate,
            COALESCE(V.ShortName, '-') AS Variety,
            COALESCE(APS.PackingStyle, '-') AS PackingStyle,
            COALESCE(S.PackingTypeID, '-') AS PackingType,
            COALESCE(S.Grade, '-') AS Grade,
            DATEDIFF(CURDATE(), S.ProductionDate) AS StockAgeDays,
            ROUND(COALESCE(S.AvalMcs, 0), 2) AS AvalMcs,
            ROUND(COALESCE(S.LooseBags, 0), 2) AS LooseBags,
            ROUND(
                (COALESCE(S.AvalMcs, 0) * COALESCE(APS.TotalSlabs, 0) * COALESCE(APS.SlabWeight, 0) * COALESCE(APS.MFactor, 0)) +
                (COALESCE(S.LooseBags, 0) * COALESCE(APS.SlabWeight, 0) * COALESCE(APS.MFactor, 0)),
                2
            ) AS TotalKgs,
            COALESCE(S.BatchCode, '-') AS BatchCode,
            COALESCE(CST.StoreName, '-') AS StoreName,
            COALESCE(B.BlockName, '-') AS BlockName,
            COALESCE(F.FloorName, '-') AS FloorName,
            COALESCE(R.RackName, '-') AS RackName
        $baseFrom
        $searchCondition
        ORDER BY DATEDIFF(CURDATE(), S.ProductionDate) DESC, V.ShortName ASC
        LIMIT $start, $length
    ";

    $result = $iconn->query($dataQuery);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $data,
        'query' => $dataQuery,
    ], JSON_NUMERIC_CHECK);
    exit;
} elseif( $action == "stockagepopup_export" ){
    /* Export all matching stock age records (no pagination) as JSON for client-side Excel/CSV generation */

    $ageGroup = isset($_GET['agegroup'])
    ? trim(str_replace(
        [' Days', '–', '—'],
        ['', '-', '-'],
        $_GET['agegroup']
    ))
    : '';
    $selectedPid = isset($_GET['PID']) ? intval($_GET['PID']) : 0;
    $searchValue = isset($_GET['search']) ? trim($_GET['search']) : '';

    $allowedAgeGroups = ['0-30', '31-90', '91-180', '181-365', '>365'];
    if (!in_array($ageGroup, $allowedAgeGroups, true)) {
        echo json_encode(['data' => []]);
        exit;
    }

    $stockFilter = " AND S.StockStatus = 1 AND (S.AvalMcs > 0 OR S.LooseBags > 0) ";
    if ($AccessType != 1 && $UnitID != 0) {
        $stockFilter .= " AND S.PlantID = " . (int)$UnitID . " ";
    } else if ($selectedPid > 0) {
        $stockFilter .= " AND S.UnitID = " . $selectedPid . " ";
    }

    $bucketCondition = " 1 ";
    if ($ageGroup === '0-30') {
        $bucketCondition = "  DATEDIFF(CURDATE(), S.ProductionDate) <= 30";
    } else if ($ageGroup === '31-90') {
        $bucketCondition = "  DATEDIFF(CURDATE(), S.ProductionDate) BETWEEN 31 AND 90";
    } else if ($ageGroup === '91-180') {
        $bucketCondition = "  DATEDIFF(CURDATE(), S.ProductionDate) BETWEEN 91 AND 180";
    } else if ($ageGroup === '181-365') {
        $bucketCondition = "  DATEDIFF(CURDATE(), S.ProductionDate) BETWEEN 181 AND 365";
    } else if ($ageGroup === '>365') {
        $bucketCondition = "  DATEDIFF(CURDATE(), S.ProductionDate) > 365";
    }

    $searchCondition = "";
    if ($searchValue !== '') {
        $safeSearch = $iconn->real_escape_string($searchValue);
        $searchCondition = "
            AND (
                COALESCE(V.ShortName, '') LIKE '%$safeSearch%'
                OR COALESCE(APS.PackingStyle, '') LIKE '%$safeSearch%'
                OR COALESCE(S.PackingTypeID, '') LIKE '%$safeSearch%'
                OR COALESCE(S.Grade, '') LIKE '%$safeSearch%'
                OR COALESCE(S.BatchCode, '') LIKE '%$safeSearch%'
                OR COALESCE(CST.StoreName, '') LIKE '%$safeSearch%'
                OR COALESCE(B.BlockName, '') LIKE '%$safeSearch%'
                OR COALESCE(F.FloorName, '') LIKE '%$safeSearch%'
                OR COALESCE(R.RackName, '') LIKE '%$safeSearch%'
            )
        ";
    }

    /* Fetch ALL matching records without LIMIT/OFFSET */
    $dataQuery = "
        SELECT
            DATE_FORMAT(S.ProductionDate, '%d-%m-%Y') AS ProductionDate,
            COALESCE(V.ShortName, '-') AS Variety,
            COALESCE(APS.PackingStyle, '-') AS PackingStyle,
            COALESCE(S.PackingTypeID, '-') AS PackingType,
            COALESCE(S.Grade, '-') AS Grade,
            DATEDIFF(CURDATE(), S.ProductionDate) AS StockAgeDays,
            ROUND(COALESCE(S.AvalMcs, 0), 2) AS AvalMcs,
            ROUND(COALESCE(S.LooseBags, 0), 2) AS LooseBags,
            ROUND(
                (COALESCE(S.AvalMcs, 0) * COALESCE(APS.TotalSlabs, 0) * COALESCE(APS.SlabWeight, 0) * COALESCE(APS.MFactor, 0)) +
                (COALESCE(S.LooseBags, 0) * COALESCE(APS.SlabWeight, 0) * COALESCE(APS.MFactor, 0)),
                2
            ) AS TotalKgs,
            COALESCE(S.BatchCode, '-') AS BatchCode,
            COALESCE(CST.StoreName, '-') AS StoreName,
            COALESCE(B.BlockName, '-') AS BlockName,
            COALESCE(F.FloorName, '-') AS FloorName,
            COALESCE(R.RackName, '-') AS RackName
        FROM CsStockData S
        LEFT JOIN ScrmPIVarietyList V ON V.VarietyID = S.VarietyID
        LEFT JOIN AdminPackingStyles APS ON APS.PCKID = S.PackingStyleID
        LEFT JOIN CsStoresRackData R ON R.RowID = S.StoreRackID
        LEFT JOIN CsStoresBlockData B ON B.BlockID = R.BlockID
        LEFT JOIN CsStoresFloorData F ON F.FloorID = R.FloorID
        LEFT JOIN CsStores CST ON CST.StoreID = S.UnitID
        WHERE $bucketCondition
        $stockFilter
        $speciesFilterV
        $searchCondition
        ORDER BY DATEDIFF(CURDATE(), S.ProductionDate) DESC, V.ShortName ASC
    ";

    $result = $iconn->query($dataQuery);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode(['data' => $data], JSON_NUMERIC_CHECK);
    exit;
} else if( $action == "packed_vs_balance" ){
    $where = "";
    $poWhere = "";
    if ($AccessType != 1 && $UnitID != 0) {
        $where = " AND S.PlantID = $UnitID ";
        $poWhere = " AND SPD.ProductionPlantID = $UnitID ";
    }

        $sql = "
          SELECT
    SPD.PoID,
    SPD.PoNo,
    SPD.PoDate,

    RPS.StationName AS PlantName,

    /* STATUS (FIXED - NO DUPLICATION) */
    CASE
        WHEN SCI.IsCompleted = 1 THEN 'COMPLETED'
        ELSE 'PENDING'
    END AS STATUS,

    /* ORDERED */
    ROUND(COALESCE(OK.OrderedKgs, 0), 3) AS OrderedKgs,
    ROUND(COALESCE(OK.OrderedMcs, 0), 4) AS OrderedMcs,

    /* PACKED */
    ROUND(COALESCE(PK.PackedKgs, 0), 3) AS PackedKgs,
    ROUND(COALESCE(PK.PackedMcs, 0), 4) AS PackedMcs,

    /* BALANCE */
    ROUND(
        GREATEST(
            COALESCE(OK.OrderedKgs, 0) - COALESCE(PK.PackedKgs, 0),
            0
        ),
        3
    ) AS BalanceKgs,

    ROUND(
        GREATEST(
            COALESCE(OK.OrderedMcs, 0) - COALESCE(PK.PackedMcs, 0),
            0
        ),
        4
    ) AS BalanceMcs

FROM ScrmPoData SPD


/* ================= STATUS FIX ================= */
LEFT JOIN (
    SELECT
        PiID,
        MAX(
            CASE
                WHEN TRIM(COALESCE(StockOutDate, '')) <> '' THEN 1
                ELSE 0
            END
        ) AS IsCompleted
    FROM ScrmCustomInvoices
    GROUP BY PiID
) SCI ON SCI.PiID = SPD.PoID


/* ================= PACKED ================= */
LEFT JOIN (
    SELECT
        S.PoID,

        SUM(
            (S.AvalMcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor) +
            (S.LooseBags * APS.SlabWeight * APS.MFactor)
        ) AS PackedKgs,

        SUM(
            S.AvalMcs + (S.LooseBags / APS.TotalSlabs)
        ) AS PackedMcs,

        SUM(
            CASE
                WHEN S.PackingTypeID = 'DUMMY' THEN
                    (S.AvalMcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor) +
                    (S.LooseBags * APS.SlabWeight * APS.MFactor)
                ELSE 0
            END
        ) AS DummyKgs,

        SUM(
            CASE
                WHEN S.PackingTypeID = 'DUMMY' THEN
                    S.AvalMcs + (S.LooseBags / APS.TotalSlabs)
                ELSE 0
            END
        ) AS DummyMcs,

        SUM(
            CASE
                WHEN S.PackingTypeID = 'MASTER' THEN
                    (S.AvalMcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor) +
                    (S.LooseBags * APS.SlabWeight * APS.MFactor)
                ELSE 0
            END
        ) AS MasterKgs,

        SUM(
            CASE
                WHEN S.PackingTypeID = 'MASTER' THEN
                    S.AvalMcs + (S.LooseBags / APS.TotalSlabs)
                ELSE 0
            END
        ) AS MasterMcs

    FROM CsStockData S
    JOIN AdminPackingStyles APS
        ON APS.PCKID = S.PackingStyleID
    LEFT JOIN ScrmPIVarietyList PIVL
        ON PIVL.VarietyID = S.VarietyID

    WHERE
        S.PoID IS NOT NULL
        AND S.PlantID = $UnitID
        $speciesFilter

    GROUP BY S.PoID
) PK ON PK.PoID = SPD.PoID


/* ================= ORDERED ================= */
LEFT JOIN (
    SELECT
        SPGD.InvId AS PoID,

        SUM(SPGD.Mcs) AS OrderedMcs,

        SUM(
            SPGD.Mcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor
        ) AS OrderedKgs

    FROM ScrmPoGradeData SPGD
    JOIN AdminPackingStyles APS
        ON APS.PCKID = SPGD.PackingStyleID
    JOIN ScrmPoData SPD2
        ON SPD2.PoID = SPGD.InvId
    LEFT JOIN ScrmPIVarietyList PIVL
        ON PIVL.VarietyID = SPGD.VarietyID

    WHERE SPD2.ProductionPlantID = $UnitID
        $speciesFilter

    GROUP BY SPGD.InvId
) OK ON OK.PoID = SPD.PoID


/* ================= PLANT ================= */
LEFT JOIN RmProcessingStations RPS
    ON RPS.ProcessingStationID = SPD.ProductionPlantID


/* ================= MAIN FILTER ================= */
WHERE 1=1
    $poWhere
    AND SPD.ProductionPlantID = $UnitID
    AND DATE(SPD.PoDate) BETWEEN '$FromDate' AND '$ToDate'


/* ================= ORDER ================= */
ORDER BY PackedKgs DESC;
;


        ";
        //echo $sql; exit;
        $result = $iconn->query($sql);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $packedValue = ($SelectedUom === 'MCS') ? (float)$row['PackedMcs'] : (float)$row['PackedKgs'];
            $balanceValue = ($SelectedUom === 'MCS') ? (float)$row['BalanceMcs'] : (float)$row['BalanceKgs'];
            $dummyValue = ($SelectedUom === 'MCS') ? (float)$row['DummyMcs'] : (float)$row['DummyKgs'];
            $masterValue = ($SelectedUom === 'MCS') ? (float)$row['MasterMcs'] : (float)$row['MasterKgs'];
            $dummyBalValue = ($SelectedUom === 'MCS') ? (float)$row['DummyBalanceMcs'] : (float)$row['DummyBalanceKgs'];
            $masterBalValue = ($SelectedUom === 'MCS') ? (float)$row['MasterBalanceMcs'] : (float)$row['MasterBalanceKgs'];
            $data[] = [
                'PoNo'       => $row['PoNo'],
                'PackedKgs'  => round($packedValue, 2),
                'BalanceKgs' => round($balanceValue, 2),
                'DummyKgs'   => round($dummyValue, 2),
                'MasterKgs'  => round($masterValue, 2),
                'DummyBalanceKgs'  => round($dummyBalValue, 2),
                'MasterBalanceKgs' => round($masterBalValue, 2),
                'UOM' => $SelectedUom,
                'UOMLabel' => $SelectedUomLabel,
                'PackedValue' => round($packedValue, 2),
                'BalanceValue' => round($balanceValue, 2)
            ];
        }

        echo json_encode($data);
        exit;


}else if( $action == "IBTRPENDING" ){
    $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
    $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
    $length = isset($_GET['length']) ? intval($_GET['length']) : 10;
    $searchValue = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';
    $safeSearch = $iconn->real_escape_string($searchValue);

    $baseFrom = "
        FROM CsIBTRequestDetails CIRD
        LEFT JOIN (
            SELECT ProductionPlantID, MAX(StoreLocation) AS StoreLocation
            FROM CsStores
            GROUP BY ProductionPlantID
        ) CS ON CS.ProductionPlantID = CIRD.FromPlantID
    ";

    $where = " WHERE (CIRD.RequestStatus = 1 OR CIRD.RequestStatus = 2) and CIRD.PostedDateTime>'2026-04-01' ";
    if ($AccessType != 1 && $UnitID != 0) {
        $where .= " AND CIRD.ToPlantID = $UnitID ";
    }else if( isset($_GET['PID']) && $_GET['PID']>0 ){
        $pid = (int)$_GET['PID']; // Safely convert to a number
        //$Qry = " AND PlantID = $pid "; // No quotes needed around $pid now
        $where .= " AND CIRD.ToPlantID = $pid ";
    }

    $filteredWhere = $where;
    if ($searchValue !== '') {
        $filteredWhere .= "
            AND (
                CIRD.IBTOrderNumber LIKE '%$safeSearch%'
                OR DATE_FORMAT(CIRD.OrderDate, '%d-%m-%Y') LIKE '%$safeSearch%'
                OR COALESCE(CS.StoreLocation, '') LIKE '%$safeSearch%'
                OR (CASE WHEN CIRD.RequestStatus = 1 THEN 'Active' WHEN CIRD.RequestStatus = 2 THEN 'Transit' ELSE '' END) LIKE '%$safeSearch%'
            )
        ";
    }

    $totalQuery = "SELECT COUNT(*) AS total $baseFrom $where";
    $totalResult = $iconn->query($totalQuery)->fetch_assoc();
    $totalRecords = intval($totalResult['total']);

    $filteredQuery = "SELECT COUNT(*) AS total $baseFrom $filteredWhere";
    $filteredResult = $iconn->query($filteredQuery)->fetch_assoc();
    $recordsFiltered = intval($filteredResult['total']);

    $dataQuery = "
        SELECT
            CIRD.IBTOrderNumber,
            CIRD.OrderDate,
            COALESCE(CS.StoreLocation, CIRD.FromPlantID) AS FromPlantID,
            CASE
                WHEN CIRD.RequestStatus = 1 THEN 'Active'
                WHEN CIRD.RequestStatus = 2 THEN 'Transit'
                ELSE ''
            END AS RequestStatus
        $baseFrom
        $filteredWhere
        ORDER BY CIRD.OrderDate DESC
        LIMIT $start, $length
    ";
    $result = $iconn->query($dataQuery);

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $recordsFiltered,
        "data" => $data
    ]);

    exit;
}else if( $action == "rmreq" ){
    $query = "
        SELECT
    O.TypeProduct,
    O.Species,
    O.Grade,
    O.ShortName,
    GROUP_CONCAT(DISTINCT O.PoNo ORDER BY O.PoNo SEPARATOR ', ') AS PoNo,
    ROUND(O.OrderedMcs, 2) AS OrderedMcs,
    ROUND(O.OrderedKgs, 2) AS OrderedKgs,

    ROUND(COALESCE(S.StoreMcs, 0), 2) AS StoreMcs,
    ROUND(COALESCE(S.StoreKgs, 0), 2) AS StoreKgs,

    ROUND(O.OrderedMcs - COALESCE(S.StoreMcs, 0), 2) AS RequiredMcs,
    ROUND(O.OrderedKgs - COALESCE(S.StoreKgs, 0), 2) AS RequiredKgs,

    'PURCHASE / PRODUCE' AS PlanningAction

FROM (
    -- ORDERED
    SELECT
        VL.TypeProduct,
        VL.Species,
        VL.Grade,
        VL.ShortName,
        GROUP_CONCAT(DISTINCT SPD.PoNo ORDER BY SPD.PoNo SEPARATOR ', ') AS PoNo,
        SUM(SPGD.Mcs) AS OrderedMcs,

        SUM(
            SPGD.Mcs
            * CSP.TotalSlabs
            * CSP.SlabWeight
            * CSP.MFactor
        ) AS OrderedKgs

    FROM ScrmPoData SPD
    JOIN ScrmPoGradeData SPGD
        ON SPGD.InvId = SPD.PoID
    JOIN AdminPackingStyles CSP
        ON CSP.PCKID = SPGD.PackingStyleID
    JOIN ScrmPIVarietyList VL
        ON VL.VarietyID = SPGD.VarietyID

    WHERE 1 = 1
        $PoQry
        $speciesFilterVL

    GROUP BY
        VL.TypeProduct,
        VL.Species,
        VL.Grade,
        VL.ShortName
) O

LEFT JOIN (
    -- STOCK
    SELECT
        VL.TypeProduct,
        VL.Species,
        VL.Grade,
        VL.ShortName,

        -- ✅ Only cartons
        SUM(CSD.AvalMcs) AS StoreMcs,

        -- ✅ KGS includes loose bags
        SUM(
            (CSD.AvalMcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor) +
            (CSD.LooseBags * APS.SlabWeight * APS.MFactor)
        ) AS StoreKgs

    FROM CsStockData CSD
    JOIN ScrmPIVarietyList VL
        ON VL.VarietyID = CSD.VarietyID
    JOIN AdminPackingStyles APS
        ON APS.PCKID = CSD.PackingStyleID

    WHERE
        CSD.PackingTypeID = 'DUMMY'
        AND (CSD.AvalMcs > 0 OR CSD.LooseBags > 0)
        $StoreQry
        $speciesFilterVL

    GROUP BY
        VL.TypeProduct,
        VL.Species,
        VL.Grade,
        VL.ShortName
) S
ON  S.TypeProduct = O.TypeProduct
AND S.Species     = O.Species
AND S.Grade       = O.Grade
AND S.ShortName   = O.ShortName

WHERE (
    ('$SelectedUom' = 'MCS'
        AND (O.OrderedMcs - COALESCE(S.StoreMcs, 0)) > 0)
    OR
    ('$SelectedUom' = 'KGS'
        AND (O.OrderedKgs - COALESCE(S.StoreKgs, 0)) > 0)
)

ORDER BY
    CASE
        WHEN '$SelectedUom' = 'MCS'
            THEN (O.OrderedMcs - COALESCE(S.StoreMcs, 0))
        ELSE
            (O.OrderedKgs - COALESCE(S.StoreKgs, 0))
    END DESC

LIMIT 10;
    ";
    $result = $iconn->query($query);

    $GDPData = [];
    if ($result) {
        while ($rowGDP = $result->fetch_assoc()) {
            $orderedValue = ($SelectedUom === 'MCS') ? (float)$rowGDP['OrderedMcs'] : (float)$rowGDP['OrderedKgs'];
            $storeValue = ($SelectedUom === 'MCS') ? (float)$rowGDP['StoreMcs'] : (float)$rowGDP['StoreKgs'];
            $requiredValue = ($SelectedUom === 'MCS') ? (float)$rowGDP['RequiredMcs'] : (float)$rowGDP['RequiredKgs'];
            $rowGDP['OrderedKgs'] = round($orderedValue, 2);
            $rowGDP['StoreKgs'] = round($storeValue, 2);
            $rowGDP['RequiredKgs'] = round($requiredValue, 2);
            $rowGDP['UOM'] = $SelectedUom;
            $rowGDP['UOMLabel'] = $SelectedUomLabel;
            $GDPData[] = $rowGDP;
        }
    }

    echo json_encode($GDPData, JSON_NUMERIC_CHECK);
    exit;
}else if( $action == "shipmentpopup_export" ){

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="shipment_data.csv"');

    $output = fopen("php://output", "w");

    fputcsv($output, ['PoNo', 'PoDate', 'ShipByDate', 'Grade', 'ProductDescription', 'StockOutDate', 'ActualMcs', 'Status']);

    $status = isset($_GET['status']) ? trim($_GET['status']) : '';

    /* ================= GET IDS ================= */
    $pendingIDs = getShipmentPendingIDs($iconn, $FromDate, $ToDate, $PoQry);
    $stockOutIDs = getStockOutDoneIDs($iconn);
    $stockOutPendingIDs = getStockoutPendingIDs($iconn, $FromDate, $ToDate, $PoQry);

    /* ================= PENDING / STOCK OUT ================= */
    if ($status === 'Pending Shipments' || $status === 'Stock Out Done' || $status === 'Stock out pending') {

        if ($status === 'Pending Shipments') {
            $idList = $pendingIDs;
        } else if ($status === 'Stock out pending') {
            $idList = $stockOutPendingIDs;
        } else {
            $idList = $stockOutIDs;
        }

        if (!empty($idList)) {

            $ids = implode(',', array_map('intval', $idList));

            $query = "
                SELECT
                    SPD.PoNo,
                    DATE_FORMAT(SPD.PoDate, '%d-%m-%Y') AS PoDate,

                    DATE_FORMAT(SPD.ShipByDate, '%d-%m-%Y') AS ShipByDate,

                    SPGD.Grade,
      SPVL.ProductDescription,
      SPGD.Mcs AS ActualMcs,
      '$status' AS Status
                FROM ScrmPoData SPD
                 LEFT JOIN ScrmPoGradeData SPGD
      ON SPGD.InvId = SPD.PoID
                LEFT JOIN ScrmPIVarietyList SPVL
      ON SPVL.VarietyID = SPGD.VarietyID
                WHERE SPD.PoID IN ($ids)
                ORDER BY SPD.PoNo ASC
            ";

            $result = $iconn->query($query);

            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    $row['PoNo'],
                    $row['PoDate'],
                    $row['ShipByDate'],
                    $row['Grade'],
                    $row['ProductDescription'],
                    $row['StockOutDate'],
                    $row['ActualMcs'],
                    $row['Status']
                ]);
            }
        }

        fclose($output);
        exit;
    }

    /* ================= Shipments (KEEP EXISTING) ================= */

    $wrappedQuery = getShipmentStatusWrappedQuery($FromDate, $ToDate, $PoQry);

$result = $iconn->query($wrappedQuery);

if (!$result) {
    die("SQL Error: " . $iconn->error);
}

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['PoNo'],
        $row['PoDate'],
        $row['ShipByDate'],
        !empty($row['Grade']) ? $row['Grade'] : '-',
        !empty($row['ProductDescription']) ? $row['ProductDescription'] : '-',
        $row['OutDate'],
        $row['ActualMcs'],
        $row['DerivedStatus'] // ⚠️ not 'Status'
    ]);
}

fclose($output);
    exit;
}else if( $action == "shipmentpopup" ){

    $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
    $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
    $length = isset($_GET['length']) ? intval($_GET['length']) : 10;
    $searchValue = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';

    $safeSearch = $iconn->real_escape_string($searchValue);

    /* ================= GET IDS ================= */
    $pendingIDs = getShipmentPendingIDs($iconn, $FromDate, $ToDate, $PoQry);
    $stockOutIDs = getStockOutDoneIDs($iconn);
    $stockOutPendingIDs = getStockoutPendingIDs($iconn, $FromDate, $ToDate, $PoQry);

    /* ================= HANDLE PENDING / STOCK OUT ================= */
    if ($status === 'Pending Shipments' || $status === 'Stock Out Done' || $status === 'Stock out pending') {

        if ($status === 'Pending Shipments') {
            $idList = $pendingIDs;
        } else if ($status === 'Stock out pending') {
            $idList = $stockOutPendingIDs;
        } else {
            $idList = $stockOutIDs;
        }

        if (empty($idList)) {
            echo json_encode([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
            exit;
        }

        $ids = implode(',', array_map('intval', $idList));

        $whereSearch = "";
        if ($searchValue !== '') {
            $whereSearch = " AND (SPD.PoNo LIKE '%$safeSearch%') ";
        }

        /* TOTAL COUNT */
        $totalQuery = "
            SELECT COUNT(*) as total
            FROM ScrmPoData SPD
            LEFT JOIN ScrmPoGradeData SPGD
      ON SPGD.InvId = SPD.PoID
                LEFT JOIN ScrmPIVarietyList SPVL
      ON SPVL.VarietyID = SPGD.VarietyID
            WHERE SPD.PoID IN ($ids)
        ";
        $recordsTotal = (int)$iconn->query($totalQuery)->fetch_assoc()['total'];

        /* FILTERED COUNT */
        $filteredQuery = "
            SELECT COUNT(*) as total
            FROM ScrmPoData SPD
            LEFT JOIN ScrmPoGradeData SPGD
      ON SPGD.InvId = SPD.PoID
                LEFT JOIN ScrmPIVarietyList SPVL
      ON SPVL.VarietyID = SPGD.VarietyID
            WHERE SPD.PoID IN ($ids)
            $whereSearch
        ";
        $recordsFiltered = (int)$iconn->query($filteredQuery)->fetch_assoc()['total'];

        /* DATA QUERY */
        $dataQuery = "
            SELECT
                SPD.PoNo,
                DATE_FORMAT(SPD.PoDate, '%d-%m-%Y') AS PoDate,

                DATE_FORMAT(SPD.ShipByDate, '%d-%m-%Y') AS ShipByDate,

                SPGD.Grade,
      SPVL.ProductDescription,
      SPGD.Mcs AS ActualMcs,
      COALESCE(DATE_FORMAT(SCI.StockOutDate, '%d-%m-%Y'), '-') AS StockOutDate,
      '$status' AS Status
            FROM ScrmPoData SPD
            LEFT JOIN ScrmPoGradeData SPGD
      ON SPGD.InvId = SPD.PoID
                LEFT JOIN ScrmPIVarietyList SPVL
      ON SPVL.VarietyID = SPGD.VarietyID
      LEFT JOIN (
                SELECT PiID, MAX(StockOutDate) AS StockOutDate
                FROM ScrmCustomInvoices
                GROUP BY PiID
            ) SCI
                ON SCI.PiID = SPD.PoID
            WHERE SPD.PoID IN ($ids)
            $whereSearch
            ORDER BY SPD.PoNo ASC
            LIMIT $start, $length
        ";

        $result = $iconn->query($dataQuery);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ], JSON_NUMERIC_CHECK);
        exit;
    }

    /* ================= Shipments (KEEP EXISTING) ================= */

    $orderColumnIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
    $orderDirRaw = isset($_GET['order'][0]['dir']) ? strtolower(trim($_GET['order'][0]['dir'])) : 'asc';
    $orderDir = ($orderDirRaw === 'desc') ? 'DESC' : 'ASC';

    $columns = ['PoNo','Grade','ProductDescription','ActualMcs','DerivedStatus'];
    $orderColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'PoNo';

    $wrappedQuery = getShipmentStatusWrappedQuery($FromDate, $ToDate, $PoQry);



    $orderColumn = "X." . $orderColumn;

$where = "WHERE 1";

if ($searchValue !== '') {
    $where .= " AND X.PoNo LIKE '%$safeSearch%'";
}

$totalQuery = "
    SELECT COUNT(*) AS total
    FROM ($wrappedQuery) X
";

$recordsTotal = (int)$iconn->query($totalQuery)->fetch_assoc()['total'];

$filteredQuery = "
    SELECT COUNT(*) AS total
    FROM ($wrappedQuery) X
    $where
";

$recordsFiltered = (int)$iconn->query($filteredQuery)->fetch_assoc()['total'];

$dataQuery = "
    SELECT
        X.PoNo,
        X.PoDate,
        X.ShipByDate,
        X.OutDate,
        X.ProductDescription,
        X.Grade,
        X.ActualMcs,
        X.DerivedStatus AS Status
    FROM ($wrappedQuery) X
    $where
    ORDER BY $orderColumn $orderDir
    LIMIT $start,$length
";

    $result = $iconn->query($dataQuery);

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'PoNo' => $row['PoNo'],
            'PoDate' => $row['PoDate'],
            'ShipByDate' => $row['ShipByDate'],
            'Grade' => !empty($row['Grade']) ? $row['Grade'] : '-',
            'ProductDescription' => !empty($row['ProductDescription']) ? $row['ProductDescription'] : '-',
            'StockOutDate' => $row['OutDate'],
            'ActualMcs' => $row['ActualMcs'],
            'Status' => $row['Status']
        ];
    }

    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $data
    ], JSON_NUMERIC_CHECK);
    exit;
}else if( $action == "shipment" ){
    $pendingIDs = getShipmentPendingIDs($iconn, $FromDate, $ToDate, $PoQry);
    $stockOutPendingIDs = getStockoutPendingIDs($iconn, $FromDate, $ToDate, $PoQry);

/* KEEP EXISTING Shipment Done logic */
$wrappedQuery = getShipmentStatusWrappedQuery($FromDate, $ToDate, $PoQry);

$dataQuery = "
    SELECT
        X.PoNo,

        X.ProductDescription,

        X.DerivedStatus AS Status
    FROM ($wrappedQuery) X
";

    $result = $iconn->query($dataQuery);

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row['PoNo']] = $row['PoNo'];
    }

$totalCount = count($data);

if( $UnitID ){
    $query = "SELECT AED.ExpDisplayName FROM AdminExporterDetails AED WHERE AED.ProcessingStationID = ".$UnitID." AND AED.Status = 1 AND AED.ExpType IN (2,3);";
    $res = $iconn->query($query);
    $row_r = $res->fetch_assoc();
    $stockoutpeningcnt =count($stockOutPendingIDs). " (".$row_r['ExpDisplayName'].")";
}else{
    $stockoutpeningcnt =count($stockOutPendingIDs);
}

$GDPData = [
    ['Status' => 'Shipments', 'PoCount' => $totalCount],
    ['Status' => 'Pending Shipments', 'PoCount' => count($pendingIDs)],
    ['Status' => 'Stock out pending', 'PoCount' => $stockoutpeningcnt]
];

echo json_encode($GDPData, JSON_NUMERIC_CHECK);
exit;
}if( $action == "shipment_plantwise_orders" ){

    $Year = isset($_GET['Year']) ? trim($_GET['Year']) -1 : '';
    $Month = isset($_GET['Month']) ? trim($_GET['Month']) : '';
    $CurrentMonth = date('m');
    $selectedShipmentPlantId = 0;

    if ($AccessType != 1 && $UnitID != 0) {
        $selectedShipmentPlantId = (int)$UnitID;
    } else if (!empty($_GET['PID'])) {
        $selectedShipmentPlantId = (int)$_GET['PID'];
    }

    if ($Year == '') {
        $Currentyear = date('Y');
    } else {
        $Currentyear = $Year;
    }

    if ($CurrentMonth > 3 && $Year == '') {
        $FinClsYear = $Currentyear;
    } else if ($Year != '') {
        $FinClsYear = $Year;
    } else {
        $FinClsYear = $Currentyear - 1;
    }

    if ($CurrentMonth > 3) {
        $RunningFinStartYear = date('Y');
    } else {
        $RunningFinStartYear = (date('Y') - 1);
    }

    $RunningFinStartYear = $Currentyear = 1;

    if ($Month !== '' && ctype_digit((string)$Month) && (int)$Month >= 1 && (int)$Month <= 12) {
        $fromDt = sprintf('%04d-%02d-01', (int)$FinClsYear, (int)$Month);
        $toDt = date('Y-m-t', strtotime($fromDt));
        $DateFilter = " BETWEEN '$fromDt' AND '$toDt' ";
    } else {
        $fyStart = $FinClsYear . '-04-01';
        $fyEnd = ($FinClsYear + 1) . '-03-31';
        $DateFilter = " BETWEEN '$fyStart' AND '$fyEnd' ";
    }
    //$_GET['FromDate'] = '2025-04-01';
    //$_GET['ToDate'] = '2026-03-31';
    $fyStart = $_GET['FromDate'];
    $fyEnd = $_GET['ToDate'];
    $DateFilter = " BETWEEN '$fyStart' AND '$fyEnd' ";
    $shipmentPlantFilter = '';
    if ($selectedShipmentPlantId > 0) {
        $shipmentPlantFilter = " AND AED.ProcessingStationID = " . $selectedShipmentPlantId . " ";
    }

    $speciesFilterSubquery = '';
    if ($SelectedSpecies !== '') {
        $safeSpeciesQ = $iconn->real_escape_string($SelectedSpecies);
        $speciesFilterSubquery = " AND SPD.PoID IN (SELECT DISTINCT SPGD.InvId FROM ScrmPoGradeData SPGD LEFT JOIN ScrmPIVarietyList SPVL ON SPVL.VarietyID = SPGD.VarietyID WHERE SPVL.Species = '$safeSpeciesQ')";
    }

    $SqlPlantWiseOrdersQuery = "SELECT
        AED.ExpApprovalNo AS StationName,
        AED.ExpDisplayName,
        AED.ExpID,
        (SELECT COUNT(*)
         FROM ScrmPoData SPD
         INNER JOIN ScrmCustomInvoices SCI ON SPD.PoID = SCI.PiID
         WHERE SPD.ProcessorID = AED.ExpID
           AND SCI.CInvDate $DateFilter
           AND SPD.Status NOT IN (2, 3, 4)
           $speciesFilterSubquery
        ) AS Total,
        (SELECT COUNT(*)
         FROM ScrmPoData SPD
         INNER JOIN ScrmCustomInvoices SCI ON SPD.PoID = SCI.PiID
         WHERE SPD.ProcessorID = AED.ExpID
           AND (SCI.StockOutDate IS NOT NULL AND SCI.StockOutDate != '')
           AND SPD.Status NOT IN (2, 3, 4)
           AND SCI.CInvDate $DateFilter
           $speciesFilterSubquery
        ) AS Shipped,
        (SELECT COUNT(*) FROM ScrmPoData SPD
         WHERE SPD.PoID NOT IN
            (SELECT PiID FROM ScrmCustomInvoices WHERE StockOutDate IS NOT NULL AND StockOutDate != '')
            AND SPD.Status NOT IN (2, 3, 4)
            AND SPD.ProcessorID = AED.ExpID
            $speciesFilterSubquery
        ) AS Pending
    FROM AdminExporterDetails AED
    WHERE AED.ExpType IN (2, 3)
      AND AED.Status = 1
      $shipmentPlantFilter
    GROUP BY AED.ExpApprovalNo, AED.ExpDisplayName, AED.ExpID
    ORDER BY AED.ExpDisplayName ASC";

    $stationNamesArray = [];
    $ordersReceivedArray = [];
    $shippedArray = [];
    $PendingArray = [];
    $ExporterDetails = [];
    $AllTotal = 0;
    $AllShipped = 0;
    $AllPending = 0;
//echo $SqlPlantWiseOrdersQuery; exit;

    $SqlPlantWiseOrdersDetails = $iconn->query($SqlPlantWiseOrdersQuery);
    while ($SqlPlantWiseOrdersRows = $SqlPlantWiseOrdersDetails->fetch_array(MYSQLI_ASSOC)) {
        $pendingValue = ($RunningFinStartYear == $Currentyear) ? $SqlPlantWiseOrdersRows['Pending'] : 0;
        $shippedValue = $SqlPlantWiseOrdersRows['Shipped'];
        if ($pendingValue > 0 || $shippedValue > 0) {
            $stationNamesArray[] = $SqlPlantWiseOrdersRows['ExpDisplayName'] ? $SqlPlantWiseOrdersRows['ExpDisplayName'] . '(' . $SqlPlantWiseOrdersRows['StationName'] . ')' : $SqlPlantWiseOrdersRows['StationName'];
            $ordersReceivedArray[] = floatval($SqlPlantWiseOrdersRows['Shipped']) + floatval($pendingValue);
            $shippedArray[] = $SqlPlantWiseOrdersRows['Shipped'];
            $PendingArray[] = $pendingValue;
            $ExporterDetails[] = $SqlPlantWiseOrdersRows['ExpID'];
            $test[] = $SqlPlantWiseOrdersRows;
            /*$AllTotal += floatval($SqlPlantWiseOrdersRows['Shipped']) + floatval($pendingValue);
            $AllShipped += floatval($SqlPlantWiseOrdersRows['Shipped']);
            $AllPending += floatval($pendingValue);*/
        }
    }
//echo "<pre>"; print_r($test); exit;
    /*$stationNamesArray[] = 'All';
    $ExporterDetails[] = 'ALL';
    $shippedArray[] = $AllShipped;*/
    $series = [];
    if ($RunningFinStartYear == $Currentyear) {
        $ordersReceivedArray[] = $AllTotal;
        $PendingArray[] = $AllPending;
        $series[] = ['name' => 'Total', 'data' => $ordersReceivedArray];
        $series[] = ['name' => 'Shipped', 'data' => $shippedArray];
        $series[] = ['name' => 'Pending', 'data' => $PendingArray];
    } else {
        $series[] = ['name' => 'Shipped', 'data' => $shippedArray];
    }

    $AryData = [];
    $AryData['stationNames'] = $stationNamesArray;
    $AryData['series'] = $series;
    $AryData['exporterDetails'] = $ExporterDetails;

    echo json_encode(['AryPlantwiseGraphDtls' => $AryData], JSON_NUMERIC_CHECK);
    exit;



}else if ($action === 'order_status_filters') {

    $selectedPid = isset($_GET['PlantID']) ? intval($_GET['PlantID']) : 0;
    if (!$selectedPid && isset($_GET['PID'])) {
        $selectedPid = intval($_GET['PID']);
    }

    $csdPlantSubFilter = "";
    if ($AccessType != 1 && $UnitID != 0) {
        $csdPlantSubFilter = "AND CSD.PlantID = " . (int)$UnitID;
    } else if ($selectedPid > 0) {
        $csdPlantSubFilter = "AND CSD.PlantID = " . $selectedPid;
    }

    $podPlantFilter = "";
    if ($AccessType != 1 && $UnitID != 0) {
        $podPlantFilter = "AND FIND_IN_SET('" . (int)$UnitID . "', POD.ProductionPlantID) > 0";
    } else if ($selectedPid > 0) {
        $podPlantFilter = "AND FIND_IN_SET('$selectedPid', POD.ProductionPlantID) > 0";
    }

    $baseWhere = "
        WHERE NOT EXISTS (
            SELECT 1 FROM ScrmCustomInvoices AS SCI
            WHERE SCI.PiID = POD.PoID AND SCI.StockOutDate != ''
        )
        AND POD.Status NOT IN (2,3,4)
        $podPlantFilter
    ";

    $poQuery = "SELECT DISTINCT POD.PoNo
        FROM ScrmPoData POD
        INNER JOIN ScrmPoGradeData AS SPGD ON SPGD.InvId = POD.PoID
        INNER JOIN CsStockData AS CSD ON CSD.PoGradeId = SPGD.ID
        LEFT JOIN ScrmPIVarietyList PIVL ON PIVL.VarietyID = CSD.VarietyID
        $baseWhere $csdPlantSubFilter $speciesFilter
        ORDER BY POD.PoNo";
    $poResult = $iconn->query($poQuery);
    $poList = [];
    if ($poResult) { while ($r = $poResult->fetch_assoc()) { $poList[] = $r['PoNo']; } }

    $ptQuery = "SELECT DISTINCT COALESCE(CSD.PackingTypeID, '-') AS PackingType
        FROM ScrmPoData POD
        INNER JOIN ScrmPoGradeData AS SPGD ON SPGD.InvId = POD.PoID
        INNER JOIN CsStockData AS CSD ON CSD.PoGradeId = SPGD.ID
        LEFT JOIN ScrmPIVarietyList PIVL ON PIVL.VarietyID = CSD.VarietyID
        $baseWhere $csdPlantSubFilter $speciesFilter
        ORDER BY PackingType";
    $ptResult = $iconn->query($ptQuery);
    $ptList = [];
    if ($ptResult) { while ($r = $ptResult->fetch_assoc()) { $ptList[] = $r['PackingType']; } }

    $cnQuery = "SELECT DISTINCT COALESCE(SCD.ConsigneeName, '') AS ConsigneeName
        FROM ScrmPoData POD
        INNER JOIN ScrmPoGradeData AS SPGD ON SPGD.InvId = POD.PoID
        INNER JOIN CsStockData AS CSD ON CSD.PoGradeId = SPGD.ID
        LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID = POD.ConsigneeID
        LEFT JOIN ScrmPIVarietyList PIVL ON PIVL.VarietyID = CSD.VarietyID
        $baseWhere $csdPlantSubFilter $speciesFilter
        ORDER BY ConsigneeName";
    $cnResult = $iconn->query($cnQuery);
    $cnList = [];
    if ($cnResult) { while ($r = $cnResult->fetch_assoc()) { $cnList[] = $r['ConsigneeName']; } }

    echo json_encode([
        'poNo' => $poList,
        'packingType' => $ptList,
        'consigneeName' => $cnList,
        'status' => array('Pending', 'Completed', 'Partial')
    ]);
    exit;


}else if ($action === 'order_status_detail') {

    header('Content-Type: application/json');

    $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
    $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
    $length = isset($_GET['length']) ? intval($_GET['length']) : 10;
    $searchValue = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';
    $selectedPid = isset($_GET['PlantID']) ? intval($_GET['PlantID']) : 0;
    if (!$selectedPid && isset($_GET['PID'])) {
        $selectedPid = intval($_GET['PID']);
    }
    $uomFilter = isset($_GET['UOM']) ? strtoupper(trim($_GET['UOM'])) : 'MCS';
    if (!in_array($uomFilter, ['KGS', 'MCS'], true)) {
        $uomFilter = 'MCS';
    }

    /* Status filter: Completed, Pending, Partial, or empty for All */
    $statusFilter = isset($_GET['statusFilter']) ? trim($_GET['statusFilter']) : '';
    $allowedStatuses = ['Completed', 'Pending', 'Partial'];
    if ($statusFilter !== '' && !in_array($statusFilter, $allowedStatuses, true)) {
        $statusFilter = '';
    }

    $colSearch = [];
    for ($i = 0; $i < 6; $i++) {
        $colSearch[$i] = isset($_GET['columns'][$i]['search']['value']) ? trim($_GET['columns'][$i]['search']['value']) : '';
    }

    $csdPlantSubFilter = "";
    if ($AccessType != 1 && $UnitID != 0) {
        $csdPlantSubFilter = "AND CSD.PlantID = " . (int)$UnitID;
    } else if ($selectedPid > 0) {
        $csdPlantSubFilter = "AND CSD.PlantID = " . $selectedPid;
    }

    $searchCondition = "";
    if ($searchValue !== '') {
        $safeSearch = $iconn->real_escape_string($searchValue);
        $searchCondition = "
            AND (
                COALESCE(POD.PoNo, '') LIKE '%$safeSearch%'
                OR COALESCE(STK.PackingTypeID, '') LIKE '%$safeSearch%'
                OR COALESCE(SCD.ConsigneeName, '') LIKE '%$safeSearch%'
            )
        ";
    }

    $colConditions = "";
    if (!empty($colSearch[0])) {
        $safe = $iconn->real_escape_string($colSearch[0]);
        $colConditions .= " AND COALESCE(POD.PoNo, '') LIKE '%$safe%'";
    }
    if (!empty($colSearch[1])) {
        $safe = $iconn->real_escape_string($colSearch[1]);
        $colConditions .= " AND COALESCE(STK.PackingTypeID, '') LIKE '%$safe%'";
    }
    if (!empty($colSearch[2])) {
        $safe = $iconn->real_escape_string($colSearch[2]);
        $colConditions .= " AND COALESCE(SCD.ConsigneeName, '') LIKE '%$safe%'";
    }

    $podPlantFilter = "";
    if ($AccessType != 1 && $UnitID != 0) {
        $podPlantFilter = "AND FIND_IN_SET('" . (int)$UnitID . "', POD.ProductionPlantID) > 0";
    } else if ($selectedPid > 0) {
        $podPlantFilter = "AND FIND_IN_SET('$selectedPid', POD.ProductionPlantID) > 0";
    }
    $proccessorId = getShipmentProcessorID($iconn);
    if($proccessorId != 1){
        $podPlantFilter = "AND POD.ProcessorID =". $proccessorId;
    }
    $baseFrom = "
        FROM ScrmPoData POD
        LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID = POD.ConsigneeID
        LEFT JOIN (
            SELECT
                SPGD.InvId AS PoID,
                SUM(
                    CAST(COALESCE(SPGD.Mcs, 0) AS DECIMAL(15,3))
                ) AS OrderedMcs,
                SUM(
                    CAST(COALESCE(SPGD.Mcs, 0) AS DECIMAL(15,3))
                    * COALESCE(APS.TotalSlabs, 0)
                    * CAST(COALESCE(APS.SlabWeight, 0) AS DECIMAL(15,3))
                    * CAST(COALESCE(APS.MFactor, 0) AS DECIMAL(15,3))
                ) AS OrderedKgs
            FROM ScrmPoGradeData AS SPGD
            LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = SPGD.PackingStyleID
            GROUP BY SPGD.InvId
        ) AS POG ON POG.PoID = POD.PoID
        LEFT JOIN (
            SELECT
                SPGD.InvId AS PoID,
                MIN(CSD.PackingTypeID) AS PackingTypeID,
                SUM(
                    CAST(COALESCE((CSD.AvalMcs+ (CSD.ActualLooseBags / APS.TotalSlabs)), 0) AS DECIMAL(15,3))
                ) AS FinishedMcs,
                SUM(COALESCE(CSD.LooseBags, 0)) AS LooseBags,
                SUM(
                    CAST(COALESCE(CSD.AvalMcs, 0) AS DECIMAL(15,3))
                    * COALESCE(APS.TotalSlabs, 0)
                    * CAST(COALESCE(APS.SlabWeight, 0) AS DECIMAL(15,3))
                    * CAST(COALESCE(APS.MFactor, 0) AS DECIMAL(15,3))
                )
                +
                SUM(
                    CAST(COALESCE(CSD.LooseBags, 0) AS DECIMAL(15,3))
                    * CAST(COALESCE(APS.SlabWeight, 0) AS DECIMAL(15,3))
                    * CAST(COALESCE(APS.MFactor, 0) AS DECIMAL(15,3))
                ) AS FinishedKgs
            FROM ScrmPoGradeData AS SPGD
            INNER JOIN CsStockData AS CSD ON CSD.PoGradeId = SPGD.ID
            LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CSD.PackingStyleID
            WHERE 1=1 $speciesFilter
            GROUP BY SPGD.InvId
        ) AS STK ON STK.PoID = POD.PoID
        WHERE NOT EXISTS (
            SELECT 1 FROM ScrmCustomInvoices AS SCI
            WHERE SCI.PiID = POD.PoID AND SCI.StockOutDate != ''
        )
        AND POD.Status NOT IN (2,3,4)
        $podPlantFilter
        $colConditions
    ";

    $groupCols = "POD.PoID, POD.PoNo";

    $orderExpr = "COALESCE(POG.OrderedKgs, 0)";
    $avalExpr = "COALESCE(STK.FinishedKgs, 0)";
    $orderMcsExpr = "COALESCE(POG.OrderedMcs, 0)";
    $avalMcsExpr = "COALESCE(STK.FinishedMcs, 0)";


    $statusCase = getOrderStatusSqlCase($avalExpr, $orderExpr);

    /* --- Summary counts (before pagination/search) --- */
    $statusCaseAlias = getOrderStatusSqlCase('packedQty', 'orderQty');
    $summaryQuery = "SELECT
        COUNT(*) AS TotalOrders,
        SUM(CASE WHEN ($statusCaseAlias) = 'Completed' THEN 1 ELSE 0 END) AS CompletedOrders,
        SUM(CASE WHEN ($statusCaseAlias) = 'Pending' THEN 1 ELSE 0 END) AS PendingOrders,
        SUM(CASE WHEN ($statusCaseAlias) = 'Partial' THEN 1 ELSE 0 END) AS PartialOrders,
        SUM(orderMcs) AS TotalOrderedMcs,
        SUM(packedMcs) AS TotalFinishedMcs,
        SUM(orderMcs - packedMcs) AS TotalBalanceMcs
    FROM (
        SELECT
            ROUND($orderExpr, 2) AS orderQty,
            ROUND($avalExpr, 2) AS packedQty,
            ROUND($orderMcsExpr, 3) AS orderMcs,
            ROUND($avalMcsExpr, 3) AS packedMcs
        $baseFrom
        GROUP BY $groupCols
    ) AS summary_data";
    //echo $summaryQuery; exit;
    $summaryResult = $iconn->query($summaryQuery);
    $summaryRow = $summaryResult ? $summaryResult->fetch_assoc() : null;
    $totalOrders = $summaryRow ? (int)$summaryRow['TotalOrders'] : 0;
    $completedOrders = $summaryRow ? (int)$summaryRow['CompletedOrders'] : 0;
    $pendingOrders = $summaryRow ? (int)$summaryRow['PendingOrders'] : 0;
    $partialOrders = $summaryRow ? (int)$summaryRow['PartialOrders'] : 0;

    /* Compute percentages rounded to 2 decimal places */
    $completedPct = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 2) : 0;
    $pendingPct = $totalOrders > 0 ? round(($pendingOrders / $totalOrders) * 100, 2) : 0;
    $partialPct = $totalOrders > 0 ? round(($partialOrders / $totalOrders) * 100, 2) : 0;

    /* --- Status filter condition for data query (HAVING works here because GROUP BY is present) --- */
    $statusCondition = "";
    if ($statusFilter !== '') {
        $safeStatus = $iconn->real_escape_string($statusFilter);
        $statusCondition = " HAVING OrderStatus = '$safeStatus'";
    }

    $totalQuery = "SELECT COUNT(*) AS total FROM (SELECT $groupCols $baseFrom GROUP BY $groupCols) AS grouped";
    $totalResult = $iconn->query($totalQuery);
    $recordsTotal = $totalResult ? (int)$totalResult->fetch_assoc()['total'] : 0;
    if (!$totalResult) {
        error_log('order_status_detail totalQuery error: ' . $iconn->error . ' | Query: ' . substr($totalQuery, 0, 500));
    }

    /* Filtered count: use WHERE on the outer subquery (not HAVING, which requires GROUP BY) */
    $statusFilterWhere = "";
    if ($statusFilter !== '') {
        $safeStatus = $iconn->real_escape_string($statusFilter);
        $statusFilterWhere = " WHERE OrderStatus = '$safeStatus'";
    }

    $filteredCountQuery = "SELECT COUNT(*) AS total FROM (
        SELECT $groupCols, ($statusCase) AS OrderStatus $baseFrom $searchCondition GROUP BY $groupCols
    ) AS grouped_filtered $statusFilterWhere";

    $filteredResult = $iconn->query($filteredCountQuery);
    $recordsFiltered = $filteredResult ? (int)$filteredResult->fetch_assoc()['total'] : 0;
    if (!$filteredResult) {
        error_log('order_status_detail filteredCount error: ' . $iconn->error . ' | Query: ' . substr($filteredCountQuery, 0, 500));
    }

    $dataQuery = "
        SELECT
            POD.PoNo,
            COALESCE(STK.PackingTypeID, '') AS PackingType,
            COALESCE(SCD.ConsigneeName, '') AS ConsigneeName,
            ROUND($orderExpr, 2) AS OrderedKgs,
            ROUND($orderMcsExpr, 2) AS OrderedMcs,
            ROUND($avalExpr, 2) AS FinishedKgs,
            ROUND($avalMcsExpr, 2) AS FinishedMcs,
            ROUND(GREATEST($orderExpr - $avalExpr, 0), 2) AS BalanceKgs,
            ROUND(GREATEST($orderMcsExpr - $avalMcsExpr, 0), 2) AS BalanceMcs,
            ROUND(CASE WHEN $orderExpr > 0 THEN ($avalExpr / $orderExpr) * 100 ELSE 0 END, 2) AS CompletedPercent,
            ROUND(CASE WHEN $orderExpr > 0 THEN (($orderExpr - $avalExpr) / $orderExpr) * 100 ELSE 100 END, 2) AS PendingPercent,
            0 AS PartialPercent,
            ($statusCase) AS OrderStatus,
            ROUND(COALESCE(STK.LooseBags, 0), 2) AS LooseBags
        $baseFrom
        $searchCondition
        GROUP BY $groupCols
        $statusCondition
        ORDER BY BalanceKgs ASC
    ";

    if ($length > 0) {
        $dataQuery .= " LIMIT $start, $length";
    }

    $result = $iconn->query($dataQuery);

    $data = [];
    if (!$result) {
        error_log('order_status_detail query error: ' . $iconn->error . ' | Query: ' . substr($dataQuery, 0, 500));
    }
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'PoNo'              => $row['PoNo'],
                'PackingType'       => $row['PackingType'],
                'ConsigneeName'     => $row['ConsigneeName'] ?: '',
                'OrderedKgs'        => (float)$row['OrderedKgs'],
                'OrderedMcs'        => (float)$row['OrderedMcs'],
                'FinishedKgs'       => (float)$row['FinishedKgs'],
                'FinishedMcs'       => (float)$row['FinishedMcs'],
                'BalanceKgs'        => (float)$row['BalanceKgs'],
                'BalanceMcs'        => (float)$row['BalanceMcs'],
                'LooseBags'         => (float)$row['LooseBags'],
                'CompletedPercent'  => round($row['CompletedPercent']),
                'PendingPercent'    => round($row['PendingPercent']),
                'PartialPercent'    => round($row['PartialPercent']),
                'OrderStatus'       => $row['OrderStatus'],
            ];
        }
    }

    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data'            => $data,
        'summary' => [
            'TotalOrders'      => $totalOrders,
            'CompletedOrders'  => $completedOrders,
            'PendingOrders'    => $pendingOrders,
            'PartialOrders'    => $partialOrders,
            'CompletedPct'     => $completedPct,
            'PendingPct'       => $pendingPct,
            'PartialPct'       => $partialPct,
        ]
    ], JSON_NUMERIC_CHECK);
    exit;

}else{

    $queryGetDailyProduction = "
SELECT
    DATE(CSD.ProductionDate) AS ProductionDate,

    -- Completed Mcs
    SUM(
        IF(
            CSD.StockStatus = 1,
            CSD.ActualMcs +
            (CSD.ActualLooseBags / NULLIF(APS.TotalSlabs, 0)),
            0
        )
    ) AS Completed_Mcs,

    -- Pending Mcs
    SUM(
        IF(
            CSD.StockStatus = 0,
            CSD.ActualMcs +
            (CSD.ActualLooseBags / NULLIF(APS.TotalSlabs, 0)),
            0
        )
    ) AS Pending_Mcs,

    -- Completed Kgs
    SUM(
        IF(CSD.StockStatus = 1, CSD.ActualKgs, 0)
    ) AS Completed_Kgs,

    -- Pending Kgs
    SUM(
        IF(CSD.StockStatus = 0, CSD.ActualKgs, 0)
    ) AS Pending_Kgs

FROM CsDailyProductionData CSD

LEFT JOIN AdminPackingStyles APS
    ON APS.PCKID = CSD.PackingStyleID
LEFT JOIN ScrmPIVarietyList PIVL
    ON PIVL.VarietyID = CSD.VarietyID

WHERE
    CSD.ProductionDate >= CURDATE() - INTERVAL 7 DAY
    AND CSD.ProductionDate < CURDATE() + INTERVAL 1 DAY
    and CSD.ProductionTypeID!='Update_Prod'
    $Qry
    $speciesFilter

GROUP BY DATE(CSD.ProductionDate)

ORDER BY DATE(CSD.ProductionDate);
";
//echo $queryGetDailyProduction;
    $smtGDP = $iconn->query($queryGetDailyProduction);

    $GDPData = [];

    if ($smtGDP) {
        while ($rowGDP = $smtGDP->fetch_assoc()) {
            $completedValue = ($SelectedUom === 'MCS') ? (float)$rowGDP['Completed_Mcs'] : (float)$rowGDP['Completed_Kgs'];
            $pendingValue = ($SelectedUom === 'MCS') ? (float)$rowGDP['Pending_Mcs'] : (float)$rowGDP['Pending_Kgs'];
            $rowGDP['Completed_Kgs'] = round($completedValue, 2);
            $rowGDP['Pending_Kgs'] = round($pendingValue, 2);
            $rowGDP['UOM'] = $SelectedUom;
            $rowGDP['UOMLabel'] = $SelectedUomLabel;
            $GDPData[] = $rowGDP;
        }
    }

    echo json_encode($GDPData, JSON_NUMERIC_CHECK);
    exit;

}
