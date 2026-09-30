<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/Libraries/PHPExcel/Classes/PHPExcel.php';
include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";

if (isset($_GET['FromDate']) && $_GET['FromDate'] && isset($_GET['ToDate']) && $_GET['ToDate'] ) {
    $StartDate = $_GET['FromDate'];
    $EndDate = $_GET['ToDate'];
    
} else {
    echo "Bad request";
    exit;
}

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




function setDate($date){
    if( isset($date) && $date ){
        return date("d-m-Y", strtotime($date));
    }else{
        return $date;
    }
}

$allPlants      = fetchLookupMap($iconn, "SELECT ProcessingStationID, StationName FROM RmProcessingStations", 'ProcessingStationID', ['StationName']);
$allMedia       = fetchLookupMap($iconn, "SELECT MediaID, MediaName FROM usedmediamaster", 'MediaID', ['MediaName']);
$allInstruments = fetchLookupMap($iconn, "SELECT InstrumentId, InstrumentName FROM instrumentmaster", 'InstrumentId', ['InstrumentName']);

//  Userlist names
$sqlQuery      = "SELECT MIN(LUID) as USER_ID, LbuName as USER_NAME FROM `QcLabTechnologist` WHERE LbuStatus=1 Group by LbuName ";
$stmtUsers     = $iconn->query($sqlQuery);
$RowUsersLists = [];
$i = 0;
while ($RowUsers = $stmtUsers->fetch_array()) {
    $RowUsersLists[$RowUsers['USER_ID']] = $RowUsers['USER_NAME'];
}
//end here

$FromDate = $_GET['FromDate'];
$ToDate   = $_GET['ToDate'];

$sql           = "SELECT * from micro_biology_ice_chutes_water_tanks where 1=1 AND AnalysisDate BETWEEN '$FromDate' and '$ToDate' order by mbwic_id desc";
//echo $sql; exit;
$result = $iconn->query($sql);
while ($RowLeaveList = $result->fetch_assoc()) { 
    $i++;
    $singlerow = array();
    
    $singlerow['Sno'] = $i;       

    $singlerow['sample_drawn_by']     = $RowUsersLists[$RowLeaveList['sample_drawn_by']] ? $RowUsersLists[$RowLeaveList['sample_drawn_by']] : '';
    $singlerow['instant_swab'] = isset($RowLeaveList['instant_swab']) ? $RowLeaveList['instant_swab'] : '';
    $singlerow['sample_collection_date']  = setDate($RowLeaveList['sample_collection_date']);
    $singlerow['plant_name'] = $allPlants[$RowLeaveList['Plant_id']]['StationName'] ? $allPlants[$RowLeaveList['Plant_id']]['StationName'] : '';                    
    $singlerow['sample_desc'] = $RowLeaveList['sample_desc'];
    $singlerow['sample_collection_area'] = $RowLeaveList['sample_collection_area'];
    $singlerow['AnalysisDate'] = setDate($RowLeaveList['AnalysisDate']);
    $singlerow['Dateofcompletion'] = setDate($RowLeaveList['Dateofcompletion']);
            
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
    $singlerow['tpc_result']     = $RowLeaveList['tpc_result']; 
    //  $singlerow['tpc_result2']     = $RowLeaveList['tpc_result2'];           
    $singlerow['tpc_completion_date']     = setDate($RowLeaveList['tpc_completion_date']);

    

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

   
    $singlerow['vcholerae_result']     = $RowLeaveList['vcholerae_result'];
    $singlerow['vcholerae_completion_date']     = setDate($RowLeaveList['vcholserae_completion_date']);
    $singlerow['vcholerae_completion_date'] = setDate($RowLeaveList['vcholerae_completion_date']);

    
    $singlerow['analysed_by'] = $RowUsersLists[$RowLeaveList['analysed_by']] ? $RowUsersLists[$RowLeaveList['analysed_by']] : '';
    $singlerow['checked_by']   = $RowUsersLists[$RowLeaveList['checked_by']] ? $RowUsersLists[$RowLeaveList['checked_by']] : '';
    $singlerow['verified_by']      = $RowUsersLists[$RowLeaveList['verified_by']] ? $RowUsersLists[$RowLeaveList['verified_by']] : '';
    $singlerow['remarks']     = $RowLeaveList['remarks'];
        
    $rows[] = $singlerow;   
}
//echo "<pre>"; print_r($rows); exit;



if(1){
    /////// media wise

    // Create PHPExcel object
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle(str_replace($report_type," ","")." Report");

    $row = 1;

    // Header Information
    $sheet->mergeCells("A$row:N$row")->setCellValue("A$row", "COASTAL AQUA PRIVATE LIMITED");
    $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(20);
    $sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($row)->setRowHeight(30); // Adjust as needed

    $row++;
    $sheet->mergeCells("A$row:M$row")->setCellValue("A$row", "PATHARLAGADDA, KAKINDA");
    $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($row)->setRowHeight(30); // Adjust as needed

    $row++;
    $custom_heding = "";
    $newcol = 0;

    
    $sheet->mergeCells("A$row:N$row")->setCellValue("A$row", "IN HOUSE MICROBIOLOGY REPORT FOR ICE CHUTES AND WATER TANKS ");
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
        // $headers = [
        //     "Sno", "Sample Drawn By","Instant Swab","Sample Collection", "Plant Name", "Sample Description", "Location", "Analysis Date", "Date Of Completion", "Tpc 35 °C", "TPC AT 22 °C", "Completed Date", "V. Cholerae", "Completed Date"
        //     //  "Analysed By", "Checked By", "Verified By", "Remarks"
        // ];

        $headers = [
    "Sno", "Sample Drawn By","Instant Swab","Sample Collection",
    "Plant Name", "Sample Description", "Location", "Analysis Date", 
    "Date Of Completion", 
    "TPC 37 °C", "Completed Date",
    "V. Cholerae", "Completed Date"
];

    }else{
        //   $headers = [
        //     "Sno", "Sample Drawn By","Instant Swab", "Sample Collection", "Plant Name", "Sample Description", "Location", "Analysis Date", "Date Of Completion",  "Tpc 35 °C","TPC AT 22 °C", "Completed Date", "V. Cholerae", "Completed Date"
        //     // "Analysed By", "Checked By", "Verified By", "Remarks"
        // ];  
        $headers = [
    "Sno", "Sample Drawn By","Instant Swab","Sample Collection",
    "Plant Name", "Sample Description", "Location", "Analysis Date", 
    "Date Of Completion", 
    "TPC 37 °C", "Completed Date",
    "V. Cholerae", "Completed Date"
];

    }
  

    $columnWidths = [
        5,   // SNO
        18,  // Sample Drawn By
        20,  // Instant Swab
        23,  // Sample Collection DATE    
        20,  // Plant Name
        18,  // Sample Description
        18,  // Location
        26,  // Tap Number Only For    
        15,  // Itype Of Ice

        18,  // PARAMETER (TPC)
        // 28,  // NAME OF THE MEDIA USED (TPC)
        // 20,  // EQUIPMENT USED (TPC)
        // 15,  // TPC RESULT1
        15,  // TPC RESULT2
        20,  // COMPLETED DATE (TPC)    

        18,  // PARAMETER (V.CHOLERAE)
        // 28,  // NAME OF THE MEDIA USED (V.CHOLERAE)
        // 20,  // EQUIPMENT USED (V.CHOLERAE)
        22,  // V.CHOLERAE RESULT1
        
        20,  // COMPLETED DATE (V.CHOLERAE)    

        // 18,  // ANALYSED BY
        // 18,  // CHECKED BY
        // 18,  // Verified BY
        // 25   // Remarks
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
                $data['instant_swab'],
                $data['sample_collection_date'], 
                $data['plant_name'],
                $data['sample_desc'], 
                $data['sample_collection_area'], 
                $data['AnalysisDate'],
                $data['Dateofcompletion'], 
                
                // $data['tpc_media'], 
                // $data['tpc_equipement'], 
                $data['tpc_result'],        // TPC 35 °C
                // $data['tpc_result2'],       // TPC 37 °C
                $data['tpc_completion_date'], 
                // $data['vcholerae_media'], 
                // $data['vcholerae_equipement'],
                $data['vcholerae_result'], 
                $data['vcholerae_completion_date'], 

                // $data['analysed_by'], 
                // $data['checked_by'],
                // $data['verified_by'], 
                // $data['remarks']
            ];
        
      
  $col = 'A';
    foreach ($values as $index => $value) {
        $sheet->setCellValue($col . $row, $value);

        // Inline condition for colors (NO function call)
        $val = trim(str_replace(',', '', $value));
        $color = "";

        if ($col == 'C') {
            if (strtolower($val) === "positive") {
                $color = "FF0000";
            }
        }

       if ($col == 'J') { // TPC 37 °C
    // Extract numeric value from string
    preg_match('/\d+/', $value, $matches);
    $num = isset($matches[0]) ? (float)$matches[0] : null;

    if ($num !== null && $num > 80) {
        $color = "FF0000"; // Red
    } elseif (strtoupper(trim($value)) === "TNTC") {
        $color = "FF0000"; // Red
    }
}


        //  if ($col == 'K') {
        //     if (is_numeric($val) && (float)$val > 50) {
        //         $color = "FF0000"; // Red
        //     } elseif (strtoupper($val) === "TNTC") {
        //         $color = "FF0000"; // Red
        //     }
        // }

     
        if ($col == 'L') {
            if (strtolower($val) === "positive") {
                $color = "FF0000"; 
            }
        }

        if ($color != "") {
            $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB($color);
        }

        $col++;
    }

           $sheet->getStyle("A$row:AP$row")->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($row)->setRowHeight(-1);
        
        $row++;
        }
    }else{
        foreach ($rows as $data) {
             $values = [
    $data['Sno'], 
    $data['sample_drawn_by'], 
    $data['instant_swab'],
    $data['sample_collection_date'], 
    $data['plant_name'],
    $data['sample_desc'], 
    $data['sample_collection_area'], 
    $data['AnalysisDate'],
    $data['Dateofcompletion'], 

    $data['tpc_result'],        // TPC 35 °C
    // $data['tpc_result2'],       // TPC 37 °C
    $data['tpc_completion_date'], // Completed Date

    $data['vcholerae_result'], 
    $data['vcholerae_completion_date'],
];

        
  $col = 'A';
    foreach ($values as $index => $value) {
        $sheet->setCellValue($col . $row, $value);

        // Inline condition for colors (NO function call)
        $val = trim(str_replace(',', '', $value));
        $color = "";

        if ($col == 'C') {
            if (strtolower($val) === "positive") {
                $color = "FF0000";
            }
        }

     if ($col == 'J') { // TPC 37 °C

    preg_match('/\d+/', $value, $matches);
    $num = isset($matches[0]) ? (float)$matches[0] : null;

    if ($num !== null && $num > 80) {
        $color = "FF0000"; // Red
    } elseif (strtoupper(trim($value)) === "TNTC") {
        $color = "FF0000"; // Red
    }
}


        //  if ($col == 'K') {
        //     if (is_numeric($val) && (float)$val > 50) {
        //         $color = "FF0000"; // Red
        //     } elseif (strtoupper($val) === "TNTC") {
        //         $color = "FF0000"; // Red
        //     }
        // }

     
        if ($col == 'L') {
            if (strtolower($val) === "positive") {
                $color = "FF0000"; 
            }
        }

        if ($color != "") {
            $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB($color);
        }

        $col++;
    }
        $sheet->getStyle("A$row:AP$row")->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($row)->setRowHeight(-1);
        
        $row++;
        }
    }
    // After data rows written
foreach ($rows as $data) {
    // write your row here
    $row++;
}

// Footer Section (only)
if (!empty($rows)) {
    // Collect unique values from all rows
    $analysedArr = [];
    $checkedArr  = [];
    $verifiedArr = [];
    $remarksArr  = [];

    foreach ($rows as $r) {
        if (!empty($r['analysed_by'])) $analysedArr[] = trim($r['analysed_by']);
        if (!empty($r['checked_by']))  $checkedArr[]  = trim($r['checked_by']);
        if (!empty($r['CheckedBy']))   $checkedArr[]  = trim($r['CheckedBy']);
        if (!empty($r['verified_by'])) $verifiedArr[] = trim($r['verified_by']);
        if (!empty($r['VerfiedBy']))   $verifiedArr[] = trim($r['VerfiedBy']);
        if (!empty($r['remarks']))     $remarksArr[]  = trim($r['remarks']);
        if (!empty($r['Remarks']))     $remarksArr[]  = trim($r['Remarks']);
    }

    // Remove duplicates
    $analysedArr = array_unique($analysedArr);
    $checkedArr  = array_unique($checkedArr);
    $verifiedArr = array_unique($verifiedArr);
    $remarksArr  = array_unique($remarksArr);

    // Convert arrays to strings
    $analysedBy = !empty($analysedArr) ? implode(", ", $analysedArr) : "N/A";
    $checkedBy  = !empty($checkedArr)  ? implode(", ", $checkedArr)  : "N/A";
    $verifiedBy = !empty($verifiedArr) ? implode(", ", $verifiedArr) : "N/A";
    $remarks    = !empty($remarksArr)  ? implode(", ", $remarksArr)  : "N/A";

    // Leave exactly ONE empty row after table
    $row++;

    // Footer rows
    $footerItems = [
        "Analysed By: $analysedBy",
        "Checked By: $checkedBy",
        "Verified By: $verifiedBy",
        "Remarks: $remarks"
    ];

    foreach ($footerItems as $text) {
        $sheet->mergeCells("A$row:H$row");
        $sheet->setCellValue("A$row", $text);
        $sheet->getStyle("A$row")->getFont()->setBold(true);
        $sheet->getStyle("A$row")->getAlignment()
              ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $row++;
    }
}
}
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="ICE-CHUTES-AND-WATER-TANKS.xls"');
header('Cache-Control: max-age=0');
ob_end_clean();

// Write file to output
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>