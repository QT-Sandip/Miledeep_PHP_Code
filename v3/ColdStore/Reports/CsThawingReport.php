<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed

$api = API_THAWING_REPORT;
$fromDate = $_GET["fromDate"];
$toDate = $_GET["toDate"];
$plant = $_GET["plant"];
$heading = "Thawing Report_Date (".$fromDate." - ".$toDate." ) _Production Plant ".$plant;
$FileName = "Thawing Report_Date (".$fromDate." - ".$toDate." )";
header("Content-type: application/vnd.ms-excel; charset=UTF-8");
header('Content-Disposition: attachment; filename=' . $FileName . '.xlsx');

$bodyContent = array(
    "ProductionPlant" => trim($_GET["plant"]),
    "FromDate" => trim($_GET["fromDate"]),
    "ToDate" => trim($_GET["toDate"]),
    "Species" => trim($_GET["species"]),
    "Grade" => trim($_GET["grade"]),
    "Variety" => trim($_GET["variety"]),
    "Treatment" => trim($_GET["treatment"]),

    // "FromDate" => "2024-08-23",
    // "ToDate" => "2024-08-23",
    // "ProductionPlant" => "",
    // "Variety" => "",
    // "Grade" => "",
    // "Species" => "",
    // "Treatment" => ""
);

$resList = CallAPI($api, "GET", "", $bodyContent);
$ThawingData = $resList->ArrayThawingData;
$ThawingTotals=$resList->ThawingTotals;

// Create PHPExcel object
$spreadsheet = new PHPExcel();
$sheet = $spreadsheet->getActiveSheet();

// Set Font Size
$spreadsheet->getDefaultStyle()->getFont()->setSize(10);

// Set Heading
$sheet->mergeCells("A1:Z1");
$sheet->setCellValue('A1', $heading);
$sheet->getStyle("A1:Z2")->getFont()->setBold(true);
$sheet->getStyle("A1:Z2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$headers = ['SNo', 'Type', 'Stock Out Date', 'Transaction Date', 'Production Date', 'SUPC', 'Product', 'Grade', 'Packing Type', 'Packing Style', 'Mcs', 'Loose Bags', 'KGs', 'Balance KGs', 'Production Date', 'Transaction Date', 'PoNo', 'Packing Style', 'Product', 'Grade', 'Packing Type', 'Group', 'Mcs', 'Loose Bags', 'KGs', 'Remarks'];

// Add Header Row to Excel
$colIndex = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($colIndex . '2', $header);
    $colIndex++;
}

// Column Widths
foreach (range('A', 'Z') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Fill Data
$row = 3;
$TotalOutMcs = 0;
$TotalOutLooseBags = 0;
$TotalKgs = 0;
$TotalBalanceKgs = 0;
foreach ($ThawingData as $item) {

    $TotalOutMcs += isset($item->OutMcs) ? $item->OutMcs : 0;
    $TotalOutLooseBags += isset($item->OutLooseBags) ? $item->OutLooseBags : 0;
    $TotalKgs += isset($item->TotalKGs) ? $item->TotalKGs : 0;
    $TotalBalanceKgs += isset($item->BalanceKGs) ? $item->BalanceKGs : 0;

    $col = 'A'; // Start with column A

    $sheet->setCellValue($col++ . $row, $item->SNO);
    $sheet->setCellValue($col++ . $row, $item->OutType);
    $sheet->setCellValue($col++ . $row, $item->OutDate);
    $sheet->setCellValue($col++ . $row, $item->TransactionDate);
    $sheet->setCellValue($col++ . $row, $item->ProductionDate);
    $sheet->setCellValue($col++ . $row, $item->DeviSUPC);
    $sheet->setCellValue($col++ . $row, $item->ProductDescription);
    $sheet->setCellValue($col++ . $row, $item->Grade);
    $sheet->setCellValue($col++ . $row, $item->PackingTypeID);
    $sheet->setCellValue($col++ . $row, $item->PackingStyle);
    $sheet->setCellValue($col . $row, cleanNumber($item->OutMcs));
    $sheet->getStyle($col++ . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    $sheet->setCellValue($col . $row, cleanNumber($item->OutLooseBags));
    $sheet->getStyle($col++ . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    $sheet->setCellValue($col . $row, cleanNumber($item->TotalKGs));
    $sheet->getStyle($col++ . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    $sheet->setCellValue($col . $row, cleanNumber($item->BalanceKGs));
    $sheet->getStyle($col++ . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

    foreach ($item->BreakUpData as $itemBreak) {
        $sheet->setCellValue($col++ . $row, $itemBreak->ProductionDate);
        $sheet->setCellValue($col++ . $row, $itemBreak->TransactionDate);
        $sheet->setCellValue($col++ . $row, $itemBreak->PoNo);
        $sheet->setCellValue($col++ . $row, $itemBreak->PackingStyle);
        $sheet->setCellValue($col++ . $row, $itemBreak->ProductDescription);
        $sheet->setCellValue($col++ . $row, $itemBreak->Grade);
        $sheet->setCellValue($col++ . $row, $itemBreak->PackingTypeID);
        $sheet->setCellValue($col++ . $row, $itemBreak->ProductionTypeID);
        $sheet->setCellValue($col . $row, cleanNumber($itemBreak->ActualMcs));
        $sheet->getStyle($col++ . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->setCellValue($col . $row, cleanNumber($itemBreak->ActualLooseBags));
        $sheet->getStyle($col++ . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->setCellValue($col . $row, cleanNumber($itemBreak->TotalKGs));
        $sheet->getStyle($col++ . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->setCellValue($col++ . $row, $itemBreak->Remarks);
        $col = 'O';
        $row++;
    }
    $BreakupLength = count($item->BreakUpData);
    if($BreakupLength == 0){$row++;}
}
$sheet->setCellValue("F" . $row, "Grand Totals");
$sheet->setCellValue("K" . $row, cleanNumber($TotalOutMcs));
$sheet->getStyle("K" . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
$sheet->setCellValue("L" . $row, cleanNumber($TotalOutLooseBags));
$sheet->getStyle("L" . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
$sheet->setCellValue("M" . $row, cleanNumber($TotalKgs));
$sheet->getStyle("M" . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
$sheet->setCellValue("N" . $row, cleanNumber($TotalBalanceKgs));
$sheet->getStyle("N" . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
$sheet->setCellValue("W" . $row, cleanNumber($ThawingTotals->ProductionMcs));
$sheet->getStyle("W" . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
$sheet->setCellValue("X" . $row, cleanNumber($ThawingTotals->ProductionLoose));
$sheet->getStyle("X" . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
$sheet->setCellValue("Y" . $row, cleanNumber($ThawingTotals->ProductionKGS));
$sheet->getStyle("Y" . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
$sheet->getStyle("A".$row.":Z".$row)->getFont()->setBold(true);
// Write to Output
$writer = new PHPExcel_Writer_Excel2007($spreadsheet);
$writer->save('php://output');
function cleanNumber($num) {
    return floatval(str_replace([',', "'"], '', $num)); // Remove comma & apostrophe, convert to number
}