<?php
error_reporting(0);
ini_set('display_errors', 0);
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
ob_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/Libraries/PHPExcel/Classes/PHPExcel.php';
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

if (isset($_GET['id']) && $_GET['id']>0) {
    $id = $_GET['id'];    
} else {
    echo "Bad request";
    exit;
}

$master = $iconn->query("SELECT * FROM loading_register_master WHERE id=$id")->fetch_assoc();
if(!$master){
    echo "Bad request";
    exit;
}

$details = $iconn->query("SELECT * FROM loading_register_details WHERE master_id=$id order by CAST(lot_no AS UNSIGNED) asc");
$detailslist = array();
while( $row = $details->fetch_assoc() ){
    //$detailslist[$row['rec_id']][] = $row;
    $detailslist[] = $row;
}
$customInvData = $iconn->query("SELECT VehicelNumber,LinerSealNo,CustomSealNo,TotalNetWt FROM `ScrmCustomInvoices` WHERE PiID='".$master['invoice_no']."'")->fetch_assoc();

//get invoice id
$getInvQry = "SELECT PoID, InvNumFull FROM `ScrmPIData` where status=5 and PoID='".$master['invoice_no']."'";
$smtInv = $iconn->query( $getInvQry );
$invData = array();
$rowInvData = $smtInv->fetch_assoc();

//$SqlGetVlist = "SELECT ID,PackingStyleID,Grade,Variety,Brand FROM ScrmPoGradeData WHERE InvId = '".$id."'";
$SqlGetVlist = "SELECT 
    spgd.ID,
    aps.PackingStyle,
    spgd.Grade,
    spgd.Variety,
    spgd.GlazeType,
    spgd.Brand,
    SVTL.ShortName
FROM ScrmPoGradeData spgd
LEFT JOIN AdminPackingStyles aps ON aps.PCKID = spgd.PackingStyleID
LEFT JOIN ScrmPIVarietyList SVTL ON SVTL.VarietyID = spgd.VarietyID
WHERE spgd.InvId = '".$master['invoice_no']."'";

$StmtVlist = $iconn->query($SqlGetVlist);
$vllist = array();

while( $RowVlistData = $StmtVlist->fetch_array() ){
    $vllist[$RowVlistData['ID']] = $RowVlistData;   
}

$cusquery = "SELECT * FROM `ScrmCustomInvoices` where PiID='".$master['invoice_no']."' limit 1";
$Stmtcus = $iconn->query($cusquery);

$CustomInvoicesData = [];
while( $RowCusData = $Stmtcus->fetch_array() ){
    $CustomInvoicesData = $RowCusData;  
}

$sqlQuery = "SELECT MIN(LUID) as USER_ID, LbuName as USER_NAME FROM `QcLabTechnologist` WHERE LbuStatus=1 Group by LbuName ORDER BY LbuName ASC";
$stmtUsers = $iconn->query($sqlQuery);
$RowUsersLists = [];
while ($RowUsers = $stmtUsers->fetch_array()) {
    $RowUsersLists[$RowUsers['USER_ID']] = $RowUsers['USER_NAME'];
}

// Create PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);
$sheet = $objPHPExcel->getActiveSheet();
$sheet->setTitle("MicroBiology Report");

$row = 1;

// Header Information
$sheet->mergeCells("A$row:H$row")->setCellValue("A$row", "COASTAL AQUA PRIVATE LIMITED");
$sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(16);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension($row)->setRowHeight(30); // Adjust as needed

$row++;

$sheet->mergeCells("A$row:H$row")->setCellValue("A$row", "LOADING REGISTER");
$sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(14);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension($row)->setRowHeight(30); // Adjust as needed

$row++;

$row++;
$sheet->mergeCells("A$row:D$row")->setCellValue("A$row", "Container No: ". $CustomInvoicesData['ContainerNo']);
$sheet->mergeCells("E$row:H$row")->setCellValue("E$row", "Seal No: ".$CustomInvoicesData['LinerSealNo'].",".$CustomInvoicesData['CustomSealNo']);

$sheet->getStyle("A$row")->getFont()->setBold(true);
$sheet->getStyle("E$row")->getFont()->setBold(true);

$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$sheet->getStyle("E$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$sheet->getRowDimension($row)->setRowHeight(20);

$row++;
$sheet->mergeCells("A$row:D$row")->setCellValue("A$row", "Vehicle No: ". $CustomInvoicesData['VehicelNumber']);
$sheet->mergeCells("E$row:H$row")->setCellValue("E$row", "PO No: ".$master['po_no']);

$sheet->getStyle("A$row")->getFont()->setBold(true);
$sheet->getStyle("E$row")->getFont()->setBold(true);

$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$sheet->getStyle("E$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$sheet->getRowDimension($row)->setRowHeight(20);

$row++;
$sheet->mergeCells("A$row:D$row")->setCellValue("A$row", "Invoice No: ". $rowInvData['InvNumFull']);
$sheet->mergeCells("E$row:H$row")->setCellValue("E$row", "Date: ".$master['date']);

$sheet->getStyle("A$row")->getFont()->setBold(true);
$sheet->getStyle("E$row")->getFont()->setBold(true);

$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$sheet->getStyle("E$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$sheet->getRowDimension($row)->setRowHeight(20);

$row++;


// Column Headers
$headers = [
    "Sno",
    "Packing Size",
    "Grade",
    "Variety",
    "Brand",    
    "Lot No",
    "No. of Cases",
    "Batch No"
];

$columnWidths = [
    7,   // SNO
    17,  // BATCH NUMBER
    17,  // PLANT NAME
    17,  // SAMPLE COLLECTION DATE
    20,  // SAMPLE DRAWN BY
    10,  // Analysis Date
    18,  // Report Date
    15,  // VARIETY
    25,
];
$row++;
$borderStyle = [
    'borders' => [
        'allborders' => [
            'style' => PHPExcel_Style_Border::BORDER_THIN, // Use BORDER_THIN or BORDER_MEDIUM if you prefer
            'color' => ['argb' => '000000'], // Black color
        ],
    ],
];
$sheet->freezePane('A' . ($row + 1));

$col = 'A';
foreach ($headers as $header) {
    $cell = $col . $row;
    $sheet->setCellValue($cell, $header);

    // Style for header
    $sheet->getStyle($cell)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'], // White font
        ],
        'fill' => [
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => ['rgb' => '000000'], // Black background
        ],
        'borders' => [
            'allborders' => [
                'style' => PHPExcel_Style_Border::BORDER_THICK,
                'color' => ['rgb' => '000000'],
            ],
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

// Data Rows
$total= 0;
$i=0; 
//foreach( $vllist as $id => $vlistdata){ $i++; $j=0;
    //foreach ($detailslist[$vlistdata['ID']] as $detailRow) { 
    foreach ($detailslist as $detailRow){
         $j=1; $i++;
        $vlistdata = $vllist[$detailRow['rec_id']];
        $total += $detailRow['no_of_cases'];
        
        if($j == 1){
            $values = [
                $i, 
                $vlistdata['PackingStyle'], $vlistdata['Grade'], $vlistdata['ShortName'], $vlistdata['Brand'],
                $detailRow['lot_no'], $detailRow['no_of_cases'], $detailRow['batch_no']
            ];
        }else{
            $values = [
                "", 
                "", "", "", "",
                $detailRow['lot_no'], $detailRow['no_of_cases'], $detailRow['batch_no']
            ];
        }
    $col = 'A';
    foreach ($values as $value) {
        $sheet->setCellValue($col . $row, $value);
        $sheet->getStyle($col . $row)->applyFromArray($borderStyle);
        $sheet->getStyle($col . $row)->getAlignment()->setWrapText(true);
        $col++;
    }
    $sheet->getRowDimension($row)->setRowHeight(-1);
    $row++;
    }


$row++;
$sheet->setCellValue('F' . $row, 'Total');
$sheet->getStyle('F' . $row)->getFont()->setBold(true);
$sheet->setCellValue('G' . $row, $total);
$sheet->getStyle('G' . $row)->getFont()->setBold(true);
$sheet->getStyle('F' . $row)->applyFromArray($borderStyle);
$sheet->getStyle('G' . $row)->applyFromArray($borderStyle);

$row++;
$sheet->mergeCells("A$row:D$row")->setCellValue("A$row", "Inspected By: ". $RowUsersLists[$master['inspected']]);
$sheet->mergeCells("E$row:H$row")->setCellValue("E$row", "Verified By: ".$RowUsersLists[$master['verified']]);

$sheet->getStyle("A$row")->getFont()->setBold(true);
$sheet->getStyle("E$row")->getFont()->setBold(true);

$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$sheet->getStyle("E$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$sheet->getRowDimension($row)->setRowHeight(20);


$row++;
$sheet->mergeCells("A$row:H$row")->setCellValue("A$row", "Remarks: ".$master['remarks']);


// Set headers for download (XLSX)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="LoadingRegisterReport.xlsx"');
header('Cache-Control: max-age=0');
ob_end_clean();
// Write file to output
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');

exit;
?>
