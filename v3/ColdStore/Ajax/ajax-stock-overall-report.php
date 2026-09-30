<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// 1. Capture DataTables standard routing integers (PHP 5.6 safe)
$draw   = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
$start  = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 10;

// 2. Prevent background executions before user triggers search
if (isset($_POST['initialIdle']) && $_POST['initialIdle'] === 'true') {
    header('Content-Type: application/json');
    echo json_encode(array(
        "draw"            => $draw,
        "recordsTotal"    => 0,
        "recordsFiltered" => 0,
        "data"            => array()
    ));
    exit;
}

// 3. Map DataTables pagination into the API payload structure
$bodyContent = array(
    "Start"           => $start,
    "Length"          => $length,
    "FromDate"        => isset($_POST['fromDate']) ? trim($_POST['fromDate']) : '',
    "ToDate"          => isset($_POST['toDate']) ? trim($_POST['toDate']) : '',
    "PlantID"         => isset($_POST['plantID']) ? trim($_POST['plantID']) : '',
    "ColdStore"       => isset($_POST['coldStore']) ? trim($_POST['coldStore']) : '',
    "Variety"         => isset($_POST['variety']) ? trim($_POST['variety']) : '',
    "Grade"           => isset($_POST['grade']) ? trim($_POST['grade']) : '',
    "Group"           => isset($_POST['group']) ? trim($_POST['group']) : '',
    "IsReturn"        => isset($_POST['isReturn']) ? trim($_POST['isReturn']) : '',
    "Transtype"       => isset($_POST['transtype']) ? trim($_POST['transtype']) : '',
    "PackingStyle"    => isset($_POST['packingStyle']) ? trim($_POST['packingStyle']) : '',
    "ProductCategory" => isset($_POST['productCategory']) ? trim($_POST['productCategory']) : '',
    "Soaking"         => isset($_POST['soaking']) ? trim($_POST['soaking']) : '',
    "TypeProduct"     => isset($_POST['typeProduct']) ? trim($_POST['typeProduct']) : '',
    "PackingType"     => isset($_POST['packingType']) ? trim($_POST['packingType']) : '',
    "FreezingType"    => isset($_POST['freezingType']) ? trim($_POST['freezingType']) : '',
    'Species'         => isset($_POST['species']) ? trim($_POST['species']) : '',
    'AbStatus'        => isset($_POST['abstatus']) ? trim($_POST['abstatus']) : '',
);

$api = API_STOCK_OVERALL_REPORT;

// Call your updated controller via the framework API layer
$resList = CallAPI($api, "GET", "", $bodyContent);

$data = array();
$recordsTotal = 0;

if (isset($resList) && !empty($resList)) {
    $error = isset($resList->Error) ? $resList->Error : '';

    if (empty($error) && isset($resList->StockOverallReport)) {

        // Grab the total un-sliced count sent back from our controller modification
        $recordsTotal = isset($resList->TotalRecords) ? (int)$resList->TotalRecords : count($resList->StockOverallReport);

        // Iterate over the already sliced elements
        foreach ($resList->StockOverallReport as $item) {
            $data[] = array(
                isset($item->SNO) ? $item->SNO : '',
                isset($item->ProductionDate) ? $item->ProductionDate : '',
                isset($item->OutDate) ? $item->OutDate : '',
                isset($item->Variety) ? $item->Variety : '',
                isset($item->ProductDescription) ? $item->ProductDescription : '',
                isset($item->PackingStyle) ? $item->PackingStyle : '',
                isset($item->Grade) ? $item->Grade : '',
                isset($item->ProductionTypeID) ? $item->ProductionTypeID : '',
                isset($item->StationName) ? $item->StationName : '',
                isset($item->StoreName) ? $item->StoreName : '',
                isset($item->ProductCategory) ? $item->ProductCategory : '',
                isset($item->Mcs) ? $item->Mcs : '',
                isset($item->KGs) ? $item->KGs : '',
                isset($item->McsSlabs) ? $item->McsSlabs : '',
                isset($item->HLYield) ? $item->HLYield : '',
                isset($item->HLTotal) ? $item->HLTotal : '',
                isset($item->HONYield) ? $item->HONYield : '',
                isset($item->HONTotal) ? $item->HONTotal : ''
            );
        }
    }
}

// 4. Return clean JSON to client UI
header('Content-Type: application/json');
echo json_encode(array(
    "draw"            => intval($draw),
    "recordsTotal"    => intval($recordsTotal),
    "recordsFiltered" => intval($recordsTotal),
    "data"            => $data
));
exit;
