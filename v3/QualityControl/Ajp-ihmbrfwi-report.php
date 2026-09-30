<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
// error_reporting(0);
// ini_set('display_errors', 0);
if (isset($_GET['FromDate']) && $_GET['FromDate'] && isset($_GET['ToDate']) && $_GET['ToDate'] && isset($_GET['report_type']) && $_GET['report_type']) {
    $StartDate = $_GET['FromDate'];
    $EndDate = $_GET['ToDate'];
    $report_type = $_GET['report_type'];
} 

else {
    echo "Bad request";
    exit;
}

$request = $_REQUEST;
$fromDate = $_GET['FromDate'] ? $_GET['FromDate'] : '';
$toDate   = $_GET['ToDate'] ? $_GET['ToDate'] : '';

$offset   = 0;
$searchValue = $request['search']['value'] ?$request['search']['value']: '';
//ob_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/Libraries/PHPExcel/Classes/PHPExcel.php';
include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";

$sql = "
SELECT 
    COALESCE(wh.AnalysisDate, fs.AnalysisDate) AS AnalysisDate,
    wh.name_food_worker_hand AS WorkerName,
    fs.name_food_contact_surface AS SurfaceName,
    wh.tpc_result AS TPC_Result_Hand,
    fs.tpc_result AS TPC_Result_Surface,
    wh.vcholerae_result AS VCholerae_Result_Hand,
    fs.vcholerae_result AS VCholerae_Result_Surface,
    wh.tpc_completion_date AS CompletionDate,
    wh.analysed_by AS AnalysedBy,
    wh.checked_by AS CheckedBy,
    wh.verified_by AS VerifiedBy
FROM 
    micro_biology_workers_hand wh
LEFT JOIN 
    micro_biology_workers_food_surface fs 
    ON wh.AnalysisDate = fs.AnalysisDate
WHERE 
    wh.AnalysisDate BETWEEN '$fromDate' AND '$toDate'
";

//echo $sql;  exit;

// Search filter
if (!empty($searchValue)) {
    $searchValue = $iconn->real_escape_string($searchValue);
    $sql .= " AND (
        wh.name_food_worker_hand LIKE '%$searchValue%' OR
        fs.name_food_contact_surface LIKE '%$searchValue%' OR
        wh.tpc_result LIKE '%$searchValue%' OR
        fs.tpc_result LIKE '%$searchValue%' OR
        wh.vcholerae_result LIKE '%$searchValue%' OR
        fs.vcholerae_result LIKE '%$searchValue%' OR
        wh.tpc_completion_date LIKE '%$searchValue%' OR
        wh.AnalysisDate LIKE '%$searchValue%'
    )";
}

function getUserMap($iconn) {
    $sql = "SELECT MIN(LUID) as USER_ID, LbuName as USER_NAME FROM QcLabTechnologist WHERE LbuStatus=1 GROUP BY LbuName";
    $result = $iconn->query($sql);
    $map = [];
    while ($row = $result->fetch_assoc()) {
        $map[$row['USER_ID']] = $row['USER_NAME'];
    }
    return $map;
}

$users = getUserMap($iconn);
// Order and execute
$sql .= " ORDER BY wh.AnalysisDate DESC";

$result = $iconn->query($sql);
$data = [];
$i = $offset;

while ($rowdata = $result->fetch_assoc()) {
    $i++;
    $mixedrows[] = [
        'sno'                  => $i,
        'analysis_date'        => $rowdata['AnalysisDate'],
        'worker_name'          => $rowdata['WorkerName'],
        'surface_name'         => $rowdata['SurfaceName'],
        'tpc_result_hand'      => $rowdata['TPC_Result_Hand'],
        'tpc_result_surface'   => $rowdata['TPC_Result_Surface'],
        'vcholerae_hand'       => $rowdata['VCholerae_Result_Hand'],
        'vcholerae_surface'    => $rowdata['VCholerae_Result_Surface'],
        'completion_date'      => $rowdata['CompletionDate'],
        'analysed_by'          => $users[$rowdata['AnalysedBy']] ? $users[$rowdata['AnalysedBy']]: '',
        'checked_by'           => $users[$rowdata['CheckedBy']] ? $users[$rowdata['CheckedBy']]: '',
        'verified_by'          => $users[$rowdata['VerifiedBy']] ? $users[$rowdata['VerifiedBy']]: '',
    ];
}

//echo "<pre>"; print_r($mixedrows); exit;

$api = API_MICRO_BIOLOGY_REPORT_WATER_ICE;

if (isset($_GET['FromDate']) && $_GET['FromDate'] && isset($_GET['ToDate']) && $_GET['ToDate'] && isset($_GET['report_type']) && $_GET['report_type']) {
    $StartDate = $_GET['FromDate'];
    $EndDate = $_GET['ToDate'];
    $report_type = $_GET['report_type'];
} else {
    echo "Bad request";
    exit;
}

$bodyContent = array(
    "StartDate" => $StartDate,
    "EndDate" => $EndDate,
    "report_type" => $report_type
);

$resList = CallAPI($api, "GET", "", $bodyContent);
$dataList = $resList->Data;
$rows = [];
if(isset($dataList) && is_array($dataList) && count($dataList)>0){
    foreach ($dataList as $entry) {
        $rows[] = (array) $entry;
    }
}
//echo "<pre>"; print_r($resList); exit;
function setDate($date) {
    if (isset($date) && $date) {
        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return date("d-m-Y", $timestamp);
        } else {
            // strtotime failed, return original or some default
            return $date;
        }
    } else {
        return $date;
    }
}
// Create PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);
$sheet = $objPHPExcel->getActiveSheet();
$sheet->setTitle(str_replace($report_type," ","")." Report");

$row = 1;
$custom_heding = "";
$newcol = 0;

if ($_REQUEST['report_type'] == "Water - Daily Based") {
    $newcol = 0;
    $custom_heding = "WATER AND ICE { DAILY BASED }";
} else if ($_REQUEST['report_type'] == "Water - Once In 4 Days") {
    $newcol = 1;
    $custom_heding = "WATER AND ICE { ONCE IN FOUR DAYS }";
} else if ($_REQUEST['report_type'] == "Water And Ice Weekly Once") {
    $newcol = 1;
    $custom_heding = "WATER AND ICE { WEEKLY ONCE }";
} 

$sheet->mergeCells("A$row:M$row")->setCellValue("A$row", "MEDIA & EQUIPMENT");
$sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(14);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension($row)->setRowHeight(30);

$row++;


if ($newcol == 0) {
    $headers = [
        "Sno", 
        "Analysis Date", 
        "Sample Description", 
        "Location", 
        "Parameter", 
        "Name Of The Media Used", 
        "Equipment Used", 
        "Parameter", 
        "Name Of The Media Used", 
        "Equipment Used",
    ];
    $columnWidths = [5, 18, 25, 25, 20, 30, 25, 20, 30, 25];
} else {
    $headers = [
        "Sno", 
        "Analysis Date", 
        "Sample Description", 
        "Location", 
        "Parameter", 
        "Name Of The Media Used", 
        "Equipment Used", 
        "Parameter", 
        "Name Of The Media Used", 
        "Equipment Used",
        "Parameter",
        "Name Of The Media Used", 
        "Equipment Used",
    ];
    $columnWidths = [5, 18, 25, 25, 20, 30, 25, 20, 30, 25, 20, 30, 25];
}


$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . $row, $header);
    $sheet->getStyle($col . $row)->getFont()->setBold(true)->setSize(10);
    $col++;
}
$sheet->getRowDimension($row)->setRowHeight(20);

$colIndex = 'A';
foreach ($columnWidths as $width) {
    $sheet->getColumnDimension($colIndex)->setWidth($width);
    $colIndex++;
}

$row++;

foreach ($rows as $data) {
   $values = [
    $data['Sno'],
    setDate($data['sample_collection']), 
    $data['sample_desc'],
    $data['location'],
    "TPC",
    $data['tpc_media'],
    $data['tpc_equipement'],
    "V. Cholerae",
    $data['vcholerae_media'],
    $data['vcholerae_equipement'],
];

if ($newcol == 1) {
    $values[] = "Coliform";
    $values[] = $data['cholerae_media'];
    $values[] = $data['cholerae_equipement'];
}



    $col = 'A';
    foreach ($values as $value) {
        $sheet->setCellValue($col . $row, $value);
        $col++;
    }

    $sheet->getRowDimension($row)->setRowHeight(20);
    $row++;
}




    // After data rows written
foreach ($rows as $data) {
    // write your row here
    $row++;
}





// Set headers for download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="MediaAndEquipement.xls"');
header('Cache-Control: max-age=0');
ob_end_clean();
// Write file to output
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>