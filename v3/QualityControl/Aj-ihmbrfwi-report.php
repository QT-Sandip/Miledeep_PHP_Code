<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
// error_reporting(0);
// ini_set('display_errors', 0);
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/Libraries/PHPExcel/Classes/PHPExcel.php';
include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";

if (isset($_GET['FromDate']) && $_GET['FromDate'] && isset($_GET['ToDate']) && $_GET['ToDate'] && isset($_GET['report_type']) && $_GET['report_type']) {
    $StartDate = $_GET['FromDate'];
    $EndDate = $_GET['ToDate'];
    $report_type = $_GET['report_type'];
} 

else {
    echo "Bad request";
    exit;
}
// $request = $_REQUEST;
// $report_type = isset($_REQUEST['report_type']) ? $_REQUEST['report_type'] : 'workhand';
$fromDate = $_GET['FromDate'] ? $_GET['FromDate'] : '';
$toDate   = $_GET['ToDate'] ? $_GET['ToDate'] : '';

$offset   = 0;
$searchValue = $request['search']['value'] ?$request['search']['value']: '';
//ob_start();


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
$data = [];

$i = $offset;


$mixedrow = [];
$mixedrows = [];

// 1. Workers Hand data
$sqlWorker = "SELECT 
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
LEFT JOIN QcLabTechnologist u ON wh.sample_drawn_by = u.LUID
WHERE wh.SampleDate BETWEEN '$fromDate' AND '$toDate'
ORDER BY wh.AnalysisDate DESC";

$resWorker = $iconn->query($sqlWorker);
$i = 0;
while ($row = $resWorker->fetch_assoc()) {
    $i++;
    $mixedrow[] = [
        'sno' => $i,
        'sample_collection_date' => $row['SampleDate'],
        'sample_drawn_by' => $row['SampleDrawnByName'],
        'analysis_date' => $row['AnalysisDate'],
        'sample_collection_area' => $row['sample_collection_area'],
        'worker_name' => $row['WorkerName'],
        'emp_id' => $row['emp_id'],
        'tpc_result_hand' => $row['TPC_Result_Hand'],
        'vcholerae_hand' => $row['VCholerae_Result_Hand'],
        'completion_date' => $row['CompletionDate'],
        'analysed_by' => $users[$row['AnalysedBy']] ? $users[$row['AnalysedBy']]: '',
        'checked_by' => $users[$row['CheckedBy']] ? $users[$row['CheckedBy']]: '',
        'verified_by' => $users[$row['VerifiedBy']] ? $users[$row['VerifiedBy']]: '',
    ];
}

// 2. Food Contact Surface data
$sqlSurface = "SELECT 
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
ORDER BY fs.AnalysisDate DESC";

$resSurface = $iconn->query($sqlSurface);
$j = 0;
while ($row = $resSurface->fetch_assoc()) {
    $j++;
    $mixedrows[] = [
        'sno' => $j,
        'sample_collection_date' => $row['SampleDate'],
        'sample_drawn_by' => $row['SampleDrawnByName'],
        'analysis_date' => $row['AnalysisDate'],
        'sample_collection_area' => $row['sample_collection_area'],
        'surface_name' => $row['SurfaceName'],
        'tpc_result_surface' => $row['TPC_Result_Surface'],
        'completion_date' => $row['CompletionDate'],
        'analysed_by' => $users[$row['AnalysedBy']] ? $users[$row['AnalysedBy']]:'',
        'checked_by' => $users[$row['CheckedBy']] ?  $users[$row['CheckedBy']] : '',
        'verified_by' => $users[$row['VerifiedBy']] ?  $users[$row['VerifiedBy']]: '',
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

// Header Information
$sheet->mergeCells("A$row:P$row")->setCellValue("A$row", "COASTAL AQUA PRIVATE LIMITED");
$sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(20);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension($row)->setRowHeight(30); // Adjust as needed

$row++;
$sheet->mergeCells("A$row:P$row")->setCellValue("A$row", "PATHARLAGADDA, KAKINDA");
$sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(14);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension($row)->setRowHeight(30); // Adjust as needed

$row++;
$custom_heding = "";
$newcol = 0;

if( $_REQUEST['report_type'] == "Water - Daily Based" ){
    $newcol = 0;
    $custom_heding = "WATER AND ICE { DAILY BASED }";
}else if( $_REQUEST['report_type'] == "Water - Once In 4 Days" ){
    $newcol = 1;
    $custom_heding = "WATER AND ICE { ONCE IN FOUR DAYS }";
}else if( $_REQUEST['report_type'] == "Water And Ice Weekly Once" ){
    $newcol = 1;
    $custom_heding = "WATER AND ICE { WEEKLY ONCE }";
}
$sheet->mergeCells("A$row:N$row")->setCellValue("A$row", "IN HOUSE MICROBIOLOGY REPORT FOR ".$custom_heding);
$sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(18);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension($row)->setRowHeight(30); // Adjust as needed

$row++;
$sheet->mergeCells("A$row:N$row")->setCellValue("A$row", "APPROVAL NO :1866");
$sheet->getStyle("A$row")->getFont()->setBold(true);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$sheet->getRowDimension($row)->setRowHeight(20);

$row++;
$sheet->mergeCells("A$row:I$row")->setCellValue("A$row", "Date of Analysis Starting: ".setDate($StartDate));
$sheet->mergeCells("J$row:N$row")->setCellValue("J$row", "Date of Completion: ".setDate($EndDate));


$sheet->getStyle("A$row")->getFont()->setBold(true);
$sheet->getStyle("J$row")->getFont()->setBold(true);

$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$sheet->getStyle("J$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$sheet->getRowDimension($row)->setRowHeight(20);

$row++;

// Column Headers
if( $newcol == 0 ){
    $headers = [
        "Sno", "Sample Drawn By", "Sample Collection","Analysis StartDate", "Plant Name", "Sample Description", "Location", "Tap Number Only For Water", "Itype Of Ice", "Tpc 37 °C", "Tpc 22 °C", "Completed Date", "V. Cholerae", "Completed Date"];
}else{
      $headers = [
        "Sno", "Sample Drawn By", "Sample Collection","Analysis StartDate", "Plant Name", "Sample Description", "Location", "Tap Number Only For Water", "Itype Of Ice", "Tpc 37 °C", "Tpc 22 °C", "Completed Date", "V. Cholerae", "Completed Date", "Coliform", "Completed Date"];  
}

$columnWidths = [
    5,   // SNO
    18,  // Sample Drawn By
    23,  // Sample Collection DATE
    20,   // Analysis StartDate    
    20,  // Plant Name
    18,  // Sample Description
    18,  // Location
    26,  // Tap Number Only For    
    15,  // Itype Of Ice
    18,  // PARAMETER (TPC)
    15,  // TPC RESULT1
    15,  // TPC RESULT2
    20,  // COMPLETED DATE (TPC)    
    18,  // PARAMETER (V.CHOLERAE)
    22,  // V.CHOLERAE RESULT1
    20,  // COMPLETED DATE (V.CHOLERAE)    
];

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

if($newcol == 1){
// Data Rows
    foreach ($rows as $data) {
        $values = [
            $data['Sno'], 
            $data['sample_drawn_by'], 
            setDate($data['sample_collection']), 
            setDate($StartDate),
            $data['plant_id'],
            $data['sample_desc'], 
            $data['location'], 
            $data['tap_no_for_water']?$data['tap_no_for_water']:'-',
            $data['itype_of_ice']?$data['itype_of_ice']:'-', 
            
            // $data['tpc_media'], 
            // $data['tpc_equipement'], 
            $data['tpc_result1'],
            $data['tpc_result2'], 
            setDate($data['tpc_completion_date']),             
            
            // $data['vcholerae_media'], 
            // $data['vcholerae_equipement'],
            $data['vcholerae_result1'], 
            setDate($data['vcholerae_completion_date']), 
            
            // $data['cholerae_media'], 
            // $data['cholerae_equipement'],
            $data['cholerae_result1'], 
            setDate($data['cholerae_completion_date']), 

           
        ];

      
$col = 'A';
foreach ($values as $index => $value) {
    $sheet->setCellValue($col.$row, $value);

    $valTrim = trim($value);
    $res = strtoupper($col);

    // Map columns
    if($res === 'J') $resName = 'TPC 35C';
    elseif($res === 'K') $resName = 'TPC 22C';
    elseif($res === 'M') $resName = 'VCholeraeResult';
    elseif($res === 'O') $resName = 'Coliform';
    else $resName = '';

    if($resName){
        $color = "";

        // TPC columns: check numeric threshold
        if(is_numeric($valTrim) || preg_match('/[\d\.]+/', $valTrim, $matches)){
            $numVal = is_numeric($valTrim) ? floatval($valTrim) : floatval($matches[0]);

            if(strtolower($resName) == "tpc 35c" && $numVal > 15){
                $color = "FF0000"; // red
            }
            elseif(strtolower($resName) == "tpc 22c" && $numVal > 50){
                $color = "FF0000"; // red
            }
            else {
            $color = "";  
        }
        }

        // VCholerae check (case-insensitive)
        if(strtolower($resName) == "vcholeraeresult" && strtolower($valTrim) === "positive"){
            $color = "FF0000"; // red
        }
    // Coliform check
if(strtolower($resName) == "coliform"){
    if(is_numeric($valTrim)){
        $numVal = floatval($valTrim);
        if($numVal > 1){   // threshold
            $color = "FF0000"; // red
        }
    }
}

        // Apply color
        if($color){
            $sheet->getStyle($col.$row)->getFont()->getColor()->setRGB($color);
        }
    }

    $col++;
}


    $sheet->getStyle("A$row:AP$row")->getAlignment()->setWrapText(true);
    $sheet->getRowDimension($row)->setRowHeight(-1);
    
    $row++; 
    }
}

else{
    foreach ($rows as $data) {
         $values = [
            $data['Sno'], 
            $data['sample_drawn_by'],
            setDate($data['sample_collection']), 
           setDate($StartDate),
            $data['plant_id'],
            $data['sample_desc'], 
            $data['location'], 
            $data['tap_no_for_water']?$data['tap_no_for_water']:'-',
            $data['itype_of_ice']?$data['itype_of_ice']:'-', 
            
            // $data['tpc_media'], 
            // $data['tpc_equipement'], 
            $data['tpc_result1'],
            $data['tpc_result2'], 
            setDate($data['tpc_completion_date']),             
            
            // $data['vcholerae_media'], 
            // $data['vcholerae_equipement'],
            $data['vcholerae_result1'], 
            setDate($data['vcholerae_completion_date']), 

            
        ];
    
  
$col = 'A';
foreach ($values as $index => $value) {
    $sheet->setCellValue($col.$row, $value);

    $valTrim = trim($value);
    $res = strtoupper($col);

    // Map columns
    if($res === 'J') $resName = 'TPC 35C';
    elseif($res === 'K') $resName = 'TPC 22C';
    elseif($res === 'M') $resName = 'VCholeraeResult';
    elseif($res === 'O') $resName = 'Coliform';

    else $resName = '';


if ($resName) {
    $color = "";

    $resNameLower = strtolower($resName);
    $valTrimUpper = strtoupper($valTrim);

    // TPC 35C
    if ($resNameLower == "tpc 35c") {
        if ($valTrimUpper === "TNTC") {
            $color = "FF0000"; // red for TNTC
        } elseif (is_numeric($valTrim) || preg_match('/[\d\.]+/', $valTrim, $matches)) {
            $numVal = is_numeric($valTrim) ? floatval($valTrim) : floatval($matches[0]);
            if ($numVal > 15) {
                $color = "FF0000"; 
            }
        }
    }
    // TPC 22C
    elseif ($resNameLower == "tpc 22c") {
          if ($valTrimUpper === "TNTC") {
            $color = "FF0000"; // red for TNTC
        }
       elseif (is_numeric($valTrim) || preg_match('/[\d\.]+/', $valTrim, $matches)) {
            $numVal = is_numeric($valTrim) ? floatval($valTrim) : floatval($matches[0]);
            if ($numVal > 50) {
                $color = "FF0000"; // red
            }
        }
    }
    // VCholerae
    elseif ($resNameLower == "vcholeraeresult" && $valTrimUpper === "POSITIVE") {
        $color = "FF0000";
    }
    // Coliform
    elseif ($resNameLower == "coliform" && is_numeric($valTrim)) {
        $numVal = floatval($valTrim);
        if ($numVal > 1) {
            $color = "FF0000";
        }
    }


        // Apply color
        if($color){
            $sheet->getStyle($col.$row)->getFont()->getColor()->setRGB($color);
        }
    }

    $col++;
}


    $sheet->getStyle("A$row:AP$row")->getAlignment()->setWrapText(true);
    $sheet->getRowDimension($row)->setRowHeight(-1);
    
    $row++;
    }
}


function addFooter($sheet, &$row, $dataset, $showRemarks = true) {
    if (empty($dataset)) {
        return;
    }

    // Collect unique values from all rows
    $analysedArr = [];
    $checkedArr  = [];
    $verifiedArr = [];
    $remarksArr  = [];

    foreach ($dataset as $r) {
        if (!empty($r['analysed_by'])) $analysedArr[] = trim($r['analysed_by']);
        if (!empty($r['checked_by']))  $checkedArr[]  = trim($r['checked_by']);
        if (!empty($r['verified_by'])) $verifiedArr[] = trim($r['verified_by']);
        if ($showRemarks && !empty($r['remarks'])) $remarksArr[] = trim($r['remarks']);
    }

    // Remove duplicates
    $analysedArr = array_unique($analysedArr);
    $checkedArr  = array_unique($checkedArr);
    $verifiedArr = array_unique($verifiedArr);
    $remarksArr  = array_unique($remarksArr);

    // Convert arrays to comma-separated strings
    $analysedBy = !empty($analysedArr) ? implode(", ", $analysedArr) : "N/A";
    $checkedBy  = !empty($checkedArr)  ? implode(", ", $checkedArr)  : "N/A";
    $verifiedBy = !empty($verifiedArr) ? implode(", ", $verifiedArr) : "N/A";
    $remarks    = !empty($remarksArr)  ? implode(", ", $remarksArr)  : "N/A";

    // Add each footer on a separate row
    $sheet->setCellValue("A$row", "Analysed By:");
    $sheet->setCellValue("B$row", $analysedBy);
    $sheet->getStyle("A$row")->getFont()->setBold(true);
    $sheet->getStyle("B$row")->getFont()->setBold(true);
    $row++;

    $sheet->setCellValue("A$row", "Checked By:");
    $sheet->setCellValue("B$row", $checkedBy);
    $sheet->getStyle("A$row")->getFont()->setBold(true);
    $sheet->getStyle("B$row")->getFont()->setBold(true);
    $row++;

    $sheet->setCellValue("A$row", "Verified By:");
    $sheet->setCellValue("B$row", $verifiedBy);
    $sheet->getStyle("A$row")->getFont()->setBold(true);
    $sheet->getStyle("B$row")->getFont()->setBold(true);
    $row++;

    if ($showRemarks) {
        $sheet->setCellValue("A$row", "Remarks:");
        $sheet->setCellValue("B$row", $remarks);
        $sheet->getStyle("A$row")->getFont()->setBold(true);
        $sheet->getStyle("B$row")->getFont()->setBold(true);
        $row++;
    }

    $row++; // extra space after footer
    // Optional: auto-size columns
    $sheet->getColumnDimension('A')->setAutoSize(true);
    // $sheet->getColumnDimension('B')->setAutoSize(true);
}

$row++;
// For Water table (show remarks)
addFooter($sheet, $row, $rows, true);


if (!empty($mixedrows)) {
    $row++;
    $row++;
    $row++;

$sheet->mergeCells("A$row:J$row")->setCellValue("A$row", "SWAB FOR WORKERS HAND");
$sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A$row")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

// Row height
$sheet->getRowDimension($row)->setRowHeight(25);

// Border around header
$sheet->getStyle("A$row:J$row")->applyFromArray([
    'borders' => [
        'allborders' => [
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
]);

$row++;



//// mixed report ///


$sheet->setCellValue("A$row", "Sno");
$sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(10);

$sheet->setCellValue("B$row", "Sample Collection Date");
$sheet->getStyle("B$row")->getFont()->setBold(true)->setSize(10);

$sheet->setCellValue("C$row", "Sample Drawn By");
$sheet->getStyle("C$row")->getFont()->setBold(true)->setSize(10);

$sheet->setCellValue("D$row", "Sample Analysis Date");
$sheet->getStyle("D$row")->getFont()->setBold(true)->setSize(10);

$sheet->setCellValue("E$row", "Sample Collection Area");
$sheet->getStyle("E$row")->getFont()->setBold(true)->setSize(10);

$sheet->setCellValue("F$row", "Name of the Food Worker Hand");
$sheet->getStyle("F$row")->getFont()->setBold(true)->setSize(10);

$sheet->setCellValue("G$row", "Employee Card No");
$sheet->getStyle("G$row")->getFont()->setBold(true)->setSize(10);

$sheet->setCellValue("H$row", "TPC at 37°C");
$sheet->getStyle("H$row")->getFont()->setBold(true)->setSize(10);

$sheet->setCellValue("I$row", "V. cholerae");
$sheet->getStyle("I$row")->getFont()->setBold(true)->setSize(10);

$sheet->setCellValue("J$row", "Date of Completion");
$sheet->getStyle("J$row")->getFont()->setBold(true)->setSize(10);


$sheet->getStyle("A$row:J$row")->applyFromArray([
    'borders' => [
        'allborders' => [
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'], // Black border
        ],
    ],
]);


$columnWidths = [
    5,   // Sno
    20,  // Sample Collection Date
    30,  // Sample Drawn By
    20,  // Sample Analysis Date
    25,  // Sample Collection Area
    35,  // Name of the Food Worker Hand
    20,  // Employee Card No
    20,  // TPC at 35°C
    20,  // V.cholerae
    20,  // Date of Completion
    25,  // Analysed By
    25,  // Checked By
    25,  // Verified By
];


$colIndex = 'A';
foreach ($columnWidths as $width) {
    $sheet->getColumnDimension($colIndex)->setWidth($width);
    $colIndex++;
}

$row++;
foreach ($mixedrow as $data) {
    
    $values = [
        $data['sno'],                       // Sno
        $data['sample_collection_date'],    // Sample Collection Date
        $data['sample_drawn_by'],           // Sample Drawn By
        $data['analysis_date'],             // Sample Analysis Date
        $data['sample_collection_area'],    // Sample Collection Area
        $data['worker_name'],               // Name of the Food Worker Hand
        $data['emp_id'],                    // Employee Card No
        $data['tpc_result_hand'],           // TPC at 35°C
        $data['vcholerae_hand'],            // V.cholerae
        $data['completion_date'],           // Date of Completion
        // $data['analysed_by'],               // Analysed By
        // $data['checked_by'],                // Checked By
        // $data['verified_by'],               // Verified By
    ];


$col = 'A';
foreach ($values as $value) {
    $sheet->setCellValue($col . $row, $value);

    $valTrim = trim($value);

  
    if ($col == 'H') { 
    if (strtoupper($valTrim) === "TNTC") {
       
        $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB('FF0000'); 
    } elseif (preg_match('/\d+(\.\d+)?/', $valTrim, $m)) {
        $num = (float)$m[0];
        if ($num > 80) {
            $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB('FF0000'); 
        }
    }
}
    if ($col == 'I') { // V. cholerae
        if (strtolower($valTrim) === "positive") {
            $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB('FF0000'); // red
        }
    }
    

    $col++;
}

    

    $sheet->getStyle("A$row:AP$row")->getAlignment()->setWrapText(true);
    $sheet->getRowDimension($row)->setRowHeight(-1);
    
    $sheet->getStyle("A$row:J$row")->applyFromArray([
        'borders' => [
            'allborders' => [
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'], // Black border
            ],
        ],
    ]);    

    $row++;
    }


$row++;
addFooter($sheet, $row, $mixedrow, false);

// === SWAB FOR FOOD CONTACT SURFACE ===
$row++;
$row++;

$sheet->mergeCells("A$row:H$row")->setCellValue("A$row", "SWAB FOR FOOD CONTACT SURFACE");
$sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A$row")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$sheet->getRowDimension($row)->setRowHeight(25);

// Border around header
$sheet->getStyle("A$row:H$row")->applyFromArray([
    'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN,'color' => ['argb' => 'FF000000']]],
]);

$row++;

// Table Header
$surfaceHeaders = ["Sno","Sample Collection Date","Sample Drawn By","Sample Analysis Date","Sample Collection Area","Name of the Food Contact Surface","TPC at 37°C","Date of Completion"];
$col = 'A';
foreach($surfaceHeaders as $header){
    $sheet->setCellValue($col.$row, $header);
    $sheet->getStyle($col.$row)->getFont()->setBold(true)->setSize(10);
    $col++;
}
$sheet->getRowDimension($row)->setRowHeight(20);



// Table Data
$row++;
foreach($mixedrows as $data){
    $values = [
        $data['sno'],
        $data['sample_collection_date'],
        $data['sample_drawn_by'],
        $data['analysis_date'],
        $data['sample_collection_area'],
        $data['surface_name'],
        $data['tpc_result_surface'],
        $data['completion_date'],
        // $data['analysed_by'],
        // $data['checked_by'],
        // $data['verified_by']
    ];

$col = 'A';
foreach ($values as $value) {
    $sheet->setCellValue($col . $row, $value);

    $valTrim = trim($value);

  
  if ($col == 'G') { 
        if (strtoupper($valTrim) === "TNTC") {
        $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB('FF0000'); 
    }
        elseif (preg_match('/\d+(\.\d+)?/', $valTrim, $m)) {
            $num = (float)$m[0];
            if ($num > 80) {
                $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB('FF0000'); // red
            }
        }
    }

    $col++;
}


    $col = 'A';
    foreach($values as $value){
        $sheet->setCellValue($col.$row, $value);
        $col++;
    }
    $sheet->getRowDimension($row)->setRowHeight(-1);
    $row++;
}

// Footer for Food Contact Surface
$row++;
addFooter($sheet, $row, $mixedrows, false);


}
// Set headers for download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="WaterandIce.xls"');
header('Cache-Control: max-age=0');
ob_end_clean();
// Write file to output
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>