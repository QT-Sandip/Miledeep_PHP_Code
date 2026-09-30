<?php
ini_set('memory_limit', '512M'); 

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/Libraries/PHPExcel/Classes/PHPExcel.php';

// $api = API_MICRO_BIOLOGY_REPORT;

// if (isset($_GET['FromDate']) && $_GET['FromDate'] && isset($_GET['ToDate']) && $_GET['ToDate']) {
//     $StartDate = $_GET['FromDate'];
//     $EndDate = $_GET['ToDate'];
// } else {
//     echo "Bad request";
//     exit;
// }


$api = API_MICRO_BIOLOGY_REPORT;

$StartDate = !empty($_GET['FromDate']) ? $_GET['FromDate'] : null;
$EndDate   = !empty($_GET['ToDate']) ? $_GET['ToDate'] : null;
$LotIds    = !empty($_GET['LotIds']) ? $_GET['LotIds'] : '';
$fyYear    = !empty($_GET['fyYear']) ? $_GET['fyYear'] : '';
$loadAll   = isset($_GET['loadAll']) ? true : false;

$typeofreport = isset($_GET['Type']) ? $_GET['Type'] : "";

$typeofreport = "";
if( isset($_GET['Type']) && $_GET['Type'] ){
   $typeofreport = $_GET['Type'];
}

$loadAll = isset($_GET['loadAll']) && $_GET['loadAll'] == 1 ? true : false;

$bodyContent = array(
    "StartDate" => $StartDate,
    "EndDate"   => $EndDate,
    "LotIds"    => $LotIds, 
    "fyYear"    => $fyYear,
    "loadAll"   => $loadAll
);



$resList = CallAPI($api, "GET", "", $bodyContent);
$dataList = $resList->Data;
$rows = [];
foreach ($dataList as $entry) {
    $rows[] = (array) $entry;
}
//echo "<pre>"; print_r($resList); exit;

// Create PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);
$sheet = $objPHPExcel->getActiveSheet();
$sheet->setTitle("MicroBiology Report");

function setDate($date){
    if( isset($date) && $date ){
        return date("d-m-Y", strtotime($date));
    }else{
        return $date;
    }
}
function setColor($field, $val) {
    $val = trim(str_replace(',', '', $val));
    $raw = strtolower($val);
    $color = "";

    // Positive / Suspect
    if ($raw === "positive") {
        $color = "FF0000"; // Red
    } elseif ($raw === "suspect") {
        $color = "FFA500"; // Orange
    }

    elseif ($field === 'TpcResult') {
    if (is_numeric($val) && $val > 70000) {
        $color = "FF0000"; // Red
    } elseif (strtoupper(trim($val)) === "TNTC") {
                $color = "FF0000";   
            }
    }
    elseif ($field === 'EcoliResult') {
        // if (preg_match('/<([\d\.]+)mpn/i', $raw, $matches)) {
        //     $value = floatval($matches[1]);
        //     if ($value > 3) $color = "FF0000";
        // } 
        if (preg_match('/<([\d\.]+)cfu/i', $raw, $matches)) {
            $value = floatval($matches[1]);
            if ($value > 20) $color = "FF0000";
        }
        //  else {
        //     $color = "FF0000";
        // }
    }
    // S.AureusResult threshold (<10 CFU)
    elseif ($field === 'SaureusResult') {
        if (preg_match('/<([\d\.]+)cfu/i', $raw, $matches)) {
            $value = floatval($matches[1]);
            if ($value > 10) $color = "FF0000";
        } elseif (is_numeric($val) && $val > 10) {
            $color = "FF0000";
        } else {
            $color = "FF0000"; 
        }
    }      
        elseif (in_array($field, ['VcholeraeResult','VparahaemolyticusResult','Salmonella'])) {
            $rawTrim = trim($raw);

            // Only set red if value is positive
            if (preg_match('/^positive$/i', $rawTrim)) {
                $color = "FF0000";
            } else {
                $color = "";  
            }
        }

            return $color;
        }

$row = 1;
    if( $typeofreport == "media" ){
        $cc = 'O';
        $ccm = 'H';
        $ccmn = 'I';
    }else if( $typeofreport == "parameter" ){
        $cc = 'U';
        $ccm = 'M';
        $ccmn = 'N';
    }else{
        $cc = 'AP';
        $ccm = 'V';
        $ccmn = 'W';
    }
// Header Information
$sheet->mergeCells("A$row:$cc$row")->setCellValue("A$row", "COASTAL AQUA PRIVATE LIMITED");
$sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(20);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension($row)->setRowHeight(30); // Adjust as needed

$row++;
$sheet->mergeCells("A$row:$cc$row")->setCellValue("A$row", "PATHARLAGADDA, KAKINADA");
$sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(14);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension($row)->setRowHeight(30); // Adjust as needed

$row++;
$sheet->mergeCells("A$row:$cc$row")->setCellValue("A$row", "IN HOUSE MICROBIOLOGY REPORT FOR PRODUCT");
$sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(18);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension($row)->setRowHeight(30); // Adjust as needed

$row++;
$sheet->mergeCells("A$row:$cc$row")->setCellValue("A$row", "APPROVAL NO :1866");
$sheet->getStyle("A$row")->getFont()->setBold(true);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$sheet->getRowDimension($row)->setRowHeight(20);

$row++;
$sheet->mergeCells("A$row:$ccm$row")->setCellValue("A$row", "Date of Analysis Starting: ".setDate($StartDate));
$sheet->mergeCells("$ccmn$row:$cc$row")->setCellValue("$ccmn$row", "Date of Completion: ".setDate($EndDate));

$sheet->getStyle("A$row")->getFont()->setBold(true);
$sheet->getStyle("$ccmn$row")->getFont()->setBold(true);

$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$sheet->getStyle("$ccmn$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$sheet->getRowDimension($row)->setRowHeight(20);

$row++;

// Column Headers

if( $typeofreport == "media" ){
    $headers = [
        "Sno", 
        "Batch Number", 
        "Sample Collection Date",        
        
        "Name Of The Media Used", 
        "Equipment Used", 
        
        "Name Of The Media Used", 
        "Equipment Used",         
        
        "Name Of The Media Used", 
        "Equipment Used", 
        
        "Name Of The Media Used", 
        "Equipment Used", 
        
        "Name Of The Media Used", 
        "Equipment Used", 
        
        "Name Of The Media Used", 
        "Equipment Used"
        
    ];

    $columnWidths = [
        5,   // SNO
        18,  // BATCH NUMBER        
        28,  // SAMPLE COLLECTION DATE        
        
        28,  // NAME OF THE MEDIA USED (TPC)
        20,  // EQUIPMENT USED (TPC)
        
        28,  // NAME OF THE MEDIA USED (E.COLI)
        20,  // EQUIPMENT USED (E.COLI)
        
        28,  // NAME OF THE MEDIA USED (S.AUREUS)
        20,  // EQUIPMENT USED (S.AUREUS)
        
        28,  // NAME OF THE MEDIA USED (V.CHOLERAE)
        20,  // EQUIPMENT USED (V.CHOLERAE)
        
        28,  // NAME OF THE MEDIA USED (V.PARAHAEMOLYTICUS)
        20,  // EQUIPMENT USED (V.PARAHAEMOLYTICUS)
        
        28,  // NAME OF THE MEDIA USED (SALMONELLA)
        20,  // EQUIPMENT USED (SALMONELLA)

    ];
} else if( $typeofreport == "parameter" ){
    $headers = [
        "Sno", 
        "Batch Number", 
        "Plant Name", 
        "Sample Collection Date", 
        "Sample Drawn By", 
        "Analysis Date", 
        "Report Date", 
        "Variety",
        "Sample Collection",        
        
        "TPC 35 °C", 
        "Completed Date",
        
        "E.Coli Result", 
        "Completed Date",
        
        "S.Aureus Result", 
        "Completed Date",
        
        "V.Cholerae Result", 
        "Completed Date",
        
        "V.Parahaemolyticus Result", 
        "Completed Date",
        
        "Salmonella", 
        "Completed Date",

       
    ];

    $columnWidths = [
        5,   // SNO        
        20,  // PLANT NAME        
        20,  // SAMPLE DRAWN BY
        18,  // Analysis Date
        18,  // Report Date
        15,  // VARIETY
        30, // Sample collection
        
        15,  // TPC 35 °C
        20,  // COMPLETED DATE (TPC)

        18,  // E.COLI RESULT
        20,  // COMPLETED DATE (E.COLI)

        20,  // S.AUREUS RESULT
        20,  // COMPLETED DATE (S.AUREUS)

        22,  // V.CHOLERAE RESULT
        20,  // COMPLETED DATE (V.CHOLERAE)

        32,  // V.PARAHAEMOLYTICUS RESULT
        20,  // COMPLETED DATE (V.PARAHAEMOLYTICUS)

        20,  // SALMONELLA
        20,  // COMPLETED DATE (SALMONELLA)

        
    ];
}else{
    $headers = [
        "Sno", "Batch Number", "Plant Name", "Sample Collection Date", "Sample Drawn By", "Analysis Date", "Report Date", "Variety", "Sample Collection",

         "Name Of The Media Used", "Equipment Used", "TPC 35 °C", "Completed Date",

         "Name Of The Media Used", "Equipment Used", "E.Coli Result", "Completed Date",

         "Name Of The Media Used", "Equipment Used", "S.Aureus Result", "Completed Date",

         "Name Of The Media Used", "Equipment Used", "V.Cholerae Result", "Completed Date",

         "Name Of The Media Used", "Equipment Used", "V.Parahaemolyticus Result", "Completed Date",

         "Name Of The Media Used", "Equipment Used", "Salmonella", "Completed Date",

        // "Analysed By", "Checked By", "Verified By", 
        // "Remarks"
    ];

    $columnWidths = [
        5,   // SNO        
        20,  // PLANT NAME
        28,  // SAMPLE COLLECTION DATE
        20,  // SAMPLE DRAWN BY
        18,  // Analysis Date
        18,  // Report Date
        15,  // VARIETY
        30,  // Sample collection
        
        28,  // NAME OF THE MEDIA USED (TPC)
        20,  // EQUIPMENT USED (TPC)
        15,  // TPC 35 °C
        20,  // COMPLETED DATE (TPC)

        28,  // NAME OF THE MEDIA USED (E.COLI)
        20,  // EQUIPMENT USED (E.COLI)
        18,  // E.COLI RESULT
        20,  // COMPLETED DATE (E.COLI)

        28,  // NAME OF THE MEDIA USED (S.AUREUS)
        20,  // EQUIPMENT USED (S.AUREUS)
        20,  // S.AUREUS RESULT
        20,  // COMPLETED DATE (S.AUREUS)

        28,  // NAME OF THE MEDIA USED (V.CHOLERAE)
        20,  // EQUIPMENT USED (V.CHOLERAE)
        22,  // V.CHOLERAE RESULT
        20,  // COMPLETED DATE (V.CHOLERAE)

        28,  // NAME OF THE MEDIA USED (V.PARAHAEMOLYTICUS)
        20,  // EQUIPMENT USED (V.PARAHAEMOLYTICUS)
        28,  // V.PARAHAEMOLYTICUS RESULT
        20,  // COMPLETED DATE (V.PARAHAEMOLYTICUS)

        28,  // NAME OF THE MEDIA USED (SALMONELLA)
        20,  // EQUIPMENT USED (SALMONELLA)
        20,  // SALMONELLA
        20,  // COMPLETED DATE (SALMONELLA)

        
    ];
}

// Map Excel columns to data fields for coloring
if ($typeofreport == "parameter") {
    $colToField = [
        'J' => 'TpcResult', 
        'L' => 'EcoliResult',
        'N' => 'SaureusResult',
        'P' => 'VcholeraeResult',
        'R' => 'VparahaemolyticusResult',
        'T' => 'Salmonella'
    ];
} 



$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . $row, $header);

     $sheet->getStyle($col . $row)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'], // Optional: black text
        ],
        'fill' => [
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => [
                'rgb' => '000000', // Yellow background
            ],
        ],
        'alignment' => [
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ],
    ]);

    $col++;
}
$sheet->getRowDimension($row)->setRowHeight(20);

$colIndex = 'A';
foreach ($columnWidths as $width) {
    $sheet->getColumnDimension($colIndex)->setWidth($width);
    $colIndex++;
}

$row++;

if( $typeofreport == "media" ){
    foreach ($rows as $data) {
        $batches = explode(',', $data['BatchNumber']);
        foreach( $batches as $batchno){
        $values = [
            $data['Sno'], 
            $batchno, 
            setDate($data['SampleDate']), 

            $data['NameMediaUsed'], 
            $data['EquipmentUsed'], 

            $data['ec_nameMediaUsed'], 
            $data['ec_equipmentUsed'], 

            $data['sa_nameMediaUsed'], 
            $data['sa_equipmentUsed'], 

            $data['vc_nameMediaUsed'], 
            $data['vc_equipmentUsed'], 

            $data['vp_nameMediaUsed'], 
            $data['vp_equipmentUsed'],

            $data['sal_nameMediaUsed'], 
            $data['sal_equipmentUsed']
            
        ];
        $col = 'A';
            foreach ($values as $value) {
                $sheet->setCellValue($col . $row, $value);
                $sheet->getStyle($col . $row)->getAlignment()->setWrapText(true);
                $col++;
            }

            $sheet->getRowDimension($row)->setRowHeight(-1);
            $row++;


    }
    }
} else if( $typeofreport == "parameter" ){
    foreach ($rows as $data) {
        $batches = explode(',', $data['BatchNumber']);
        foreach( $batches as $batchno){
        $values = [
            $data['Sno'], 
            $batchno,
            $data['Plant'], 
            setDate($data['SampleDate']), 
            $data['DrawnBy'],
            setDate($data['AnalysisDate']), 
            setDate($data['ReportDate']), 
            $data['Variety'],
            $data['sample_collection'],

            $data['TpcResult'], 
            setDate($data['tp_completed_date']),

            $data['EcoliResult'], 
            setDate($data['ec_completed_date']),

            $data['SaureusResult'], 
            setDate($data['sa_completed_date']),

            $data['VcholeraeResult'], 
            setDate($data['vc_completed_date']),

            $data['VparahaemolyticusResult'], 
            setDate($data['vp_completed_date']),

            $data['Salmonella'], 
            setDate($data['sal_completed_date']),

            // $data['analysed_by'], $data['CheckedBy'], $data['VerfiedBy'], $data['Remarks']
        ];

$col = 'A';
foreach ($values as $index => $value) {
    $sheet->setCellValue($col . $row, $value);

    // Apply text color based on field
    if (isset($colToField[$col])) {
        $field = $colToField[$col];
        $color = setColor($field, $value);
        if ($color) {
            $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB($color);
        }
    }

    $sheet->getStyle($col . $row)->getAlignment()->setWrapText(true);
    $col++;
}
        $sheet->getRowDimension($row)->setRowHeight(-1);
        
        $row++;
    }
    }



// Footer Section (only)
if (!empty($rows)) {
    // Collect unique values
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

    // Convert arrays to comma-separated strings
    $analysedBy = implode(", ", $analysedArr);
    $checkedBy  = implode(", ", $checkedArr);
    $verifiedBy = implode(", ", $verifiedArr);
    $remarks    = implode(", ", $remarksArr);

    // Start footer rows
    $row += 2;

    $sheet->mergeCells("A$row:H$row");
    $sheet->setCellValue("A$row", "Analysed By: $analysedBy");
    $sheet->getStyle("A$row")->getFont()->setBold(true);
    $row++;

  
    $sheet->mergeCells("A$row:H$row");
    $sheet->setCellValue("A$row", "Checked By: $checkedBy");
    $sheet->getStyle("A$row")->getFont()->setBold(true);
    $row++;

   
    $sheet->mergeCells("A$row:H$row");
    $sheet->setCellValue("A$row", "Verified By: $verifiedBy");
    $sheet->getStyle("A$row")->getFont()->setBold(true);
    $row++;

    
    $sheet->mergeCells("A$row:H$row");
    $sheet->setCellValue("A$row", "Remarks: $remarks");
    $sheet->getStyle("A$row")->getFont()->setBold(true);
}

//  print_r($rows);
// exit;


}else{
    foreach ($rows as $data) {
        $values = [
            $data['Sno'], $data['BatchNumber'], $data['Plant'], setDate($data['SampleDate']), $data['DrawnBy'],
            setDate($data['AnalysisDate']), setDate($data['ReportDate']), $data['Variety'], $data['sample_collection'],

             $data['NameMediaUsed'], $data['EquipmentUsed'], $data['TpcResult'], setDate($data['tp_completed_date']),

             $data['ec_nameMediaUsed'], $data['ec_equipmentUsed'], $data['EcoliResult'], setDate($data['ec_completed_date']),

             $data['sa_nameMediaUsed'], $data['sa_equipmentUsed'], $data['SaureusResult'], setDate($data['sa_completed_date']),

             $data['vc_nameMediaUsed'], $data['vc_equipmentUsed'], $data['VcholeraeResult'], setDate($data['vc_completed_date']),

             $data['vp_nameMediaUsed'], $data['vp_equipmentUsed'], $data['VparahaemolyticusResult'], setDate($data['vp_completed_date']),

             $data['sal_nameMediaUsed'], $data['sal_equipmentUsed'], $data['Salmonella'], setDate($data['sal_completed_date']),

            // $data['analysed_by'], $data['CheckedBy'], $data['VerfiedBy'], $data['Remarks']
        ];

        $col = 'A';
        foreach ($values as $value) {
            $sheet->setCellValue($col . $row, $value);
            if($col == 'L' || $col == 'Q' || $col == 'V' || $col == 'AA' || $col == 'AF' || $col == 'AK'){
                $color="";
                if( is_numeric($value) && $value>40000 ){
                    $color = "FF0000";
                }else if( strtolower($value) == "positive" ) {
                    $color = "FF0000";
                }else if( strtolower($value) == "suspect" ) {
                    $color = "FFA500";
                } 
                if($color !=""){
                    $sheet->getStyle($col . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
          ->getStartColor()->setRGB($color);
                }
            }
            $sheet->getStyle($col . $row)
                  ->getAlignment()
                  ->setWrapText(true);
            $col++;
        }

        
        $sheet->getRowDimension($row)->setRowHeight(-1);
        
        $row++;
    }
}


// Set headers for download (XLSX)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="MicroBiologyReport.xlsx"');
header('Cache-Control: max-age=0');
ob_end_clean();

// Write file to output
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>