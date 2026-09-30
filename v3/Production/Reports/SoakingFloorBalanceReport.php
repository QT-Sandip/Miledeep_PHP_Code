<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed

// $StartDate = trim($_GET["fromDate"]);
$EndDate = trim($_GET["toDate"]);
$ReceivingPlantID = trim($_GET["receivingPlantID"]);
$PlantName = trim($_GET["plantName"]);
$ChillRoomPreparationType = $_GET["ChillRoomPreparationType"];

// $FromDate = date('d/m/Y', strtotime($StartDate));
$ToDate = date('d/m/Y', strtotime($EndDate));
// $heading = 'Chill Room Balance Report_Date:(' . date('d/m/Y', strtotime($StartDate)) . ' - ' . date('d/m/Y', strtotime($EndDate)) . ')_ReceivingPlant: ' . $PlantName;
$heading = 'Chill Room Balance Report_TillDate:(' . date('d/m/Y', strtotime($EndDate)) . ')_ReceivingPlant: ' . $PlantName;

header('Content-Type: application//vnd.openxmlformats-officedocument.spreadsheetml.sheet');
// header('Content-Disposition: attachment; filename="Chill Room Balance Report_FromDate:' . $FromDate . '_ToDate:' . $ToDate . '.xlsx"');
header('Content-Disposition: attachment; filename="Chill Room Balance Report_TillDate:'. $ToDate . '.xls"');
$bodyContent = array(
    // "StartDate" => $StartDate,
    "EndDate" => $EndDate,
    "ReceivingPlantID" => $ReceivingPlantID,
    "ChillRoomPreparationType" => $ChillRoomPreparationType,
);
$api = API__SOAKING_FLOOR_BALANCE_REPORT;
$resList = CallAPI($api, "GET", "", $bodyContent);

$RawHLList = $resList->RawHLTrackData;
$RawHLCombinationData = $resList->RawHLBalanceData->ArrayVarietyData;
$RawHLGrandTotals = $resList->RawHLBalanceData;

$CookedHLList = $resList->CookedHLTrackData;
$CookedHLCombinationData = $resList->CookedHLBalanceData->ArrayVarietyData;
$CookedHLGrandTotals = $resList->CookedHLBalanceData;
// $CookedHLTableID = "tblDataTableCookedHL";
$CookedHLCount = count($CookedHLList)+4;

$RawMeatLList = $resList->RawMeatTrackData;
$RawMeatCombinationData = $resList->RawMeatBalanceData->ArrayVarietyData;
$RawMeatGrandTotals = $resList->RawMeatBalanceData;
$RawMeatCount = count($RawMeatLList)+4;

$CookedMeatList = $resList->CookedMeatTrackData;
$CookedMeatCombinationData = $resList->CookedMeatBalanceData->ArrayVarietyData;
$CookedMeatGrandTotals = $resList->CookedMeatBalanceData;
$CookedMeatCount = count($CookedMeatList)+4;

$BlockMeatList = $resList->BlockMeatTrackData;
$BlockMeatCombinationData = $resList->BlockMeatBalanceData->ArrayVarietyData;
$BlockMeatGrandTotals = $resList->BlockMeatBalanceData;
$BlockMeatCount = count($BlockMeatList)+4;

$ThawingMeatList = $resList->ThawingMeatTrackData;
$ThawingMeatCombinationData = $resList->ThawingMeatBalanceData->ArrayVarietyData;
$ThawingMeatGrandTotals = $resList->ThawingMeatBalanceData;
$ThawingMeatCount = count($ThawingMeatList)+4;

        // $CookedHLList = $resList->CookedHLTrackData;
        // $CookedHLCombinationData = $resList->CookedHLBalanceData->ArrayVarietyData;
        // $CookedHLGrandTotals = $resList->CookedHLBalanceData;
        // // $CookedHLTableID = "tblDataTableCookedHL";
        // $CookedHLCount = count($CookedHLList)+4;

// Create PHPExcel object
$spreadsheet = new PHPExcel();
$sheet = $spreadsheet->getActiveSheet();
// freezes the first 3 columns from row 1
$sheet->freezePane('D1');
// Set Font Size
$spreadsheet->getDefaultStyle()->getFont()->setSize(10);

$row = 1;
function generateExcelTable ($List, $CombinationData, $GrandTotals, $heading, $HeaderName){
    global $row,$sheet;
    $sheet->setCellValue('A' . $row,$heading);
    $HeaderRow = $row++;
    $sheet->setCellValue('A' . $row++, $HeaderName);
    $sheet->setCellValue('A' . $row, "SNO");
    $sheet->setCellValue('B' . $row, "Variety");
    $sheet->setCellValue('C' . $row, "Grade");
    // Column Widths

    $headerCount = 0;
    $col = 'D';
    foreach ($List as $item) {
        $sheet->setCellValue($col++ . $row, $item->TrackCode);
        $headerCount++;
    }
    $sheet->setCellValue($col . $row, "Total KGs");
    $headerCount+=3;    
    $HeaderEndCol = PHPExcel_Cell::stringFromColumnIndex($headerCount);
    $sheet->mergeCells('A'.$HeaderRow.':'.$HeaderEndCol .$HeaderRow);
    foreach (range(0, $headerCount-1) as $columnID) {
        $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($columnID))->setAutoSize(true);
    }
    $row++;
    $col = 'D';
    foreach ($List as $item) {
        $sheet->setCellValue( $col++ . $row, $item->ArrivalDate);
    }
    $sheet->getStyle("A".$HeaderRow.":".$HeaderEndCol.$row)->getFont()->setBold(true);
    $sheet->getStyle("A".$HeaderRow.":".$HeaderEndCol.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $row++;
    $i = 0;
    foreach ($CombinationData as $item) {
        $col = 'A'; // Start with column A
        $sheet->setCellValue($col++ . $row, ++$i);
        $sheet->setCellValue($col++ . $row, $item->ShortName);
        $sheet->setCellValue($col++ . $row, $item->PackingGrade);
        foreach ($item->BalanceData as $itemList) {
            if($itemList->Balance != '-'){
                $sheet->setCellValue($col . $row, cleanNumber($itemList->Balance));
                $sheet->getStyle($col++ . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            }else{
                $sheet->setCellValue($col++ . $row, $itemList->Balance);
            }
        }
        $sheet->setCellValue($col . $row, cleanNumber($item->TotalBalanceQty));
        $sheet->getStyle($col++ . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $row++;
    }
    
    if ($GrandTotals->GrandBalanceQty != 0) {
        $sheet->setCellValue('B' . $row, "Grand Totals");
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    }
    $sheet->setCellValue($HeaderEndCol . $row, cleanNumber($GrandTotals->GrandBalanceQty));
    $sheet->getStyle($HeaderEndCol . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    $sheet->getStyle($HeaderEndCol . $row)->getFont()->setBold(true);
}
generateExcelTable($RawHLList, $RawHLCombinationData, $RawHLGrandTotals, $heading, 'Raw HL');
$row++;
generateExcelTable($CookedHLList, $CookedHLCombinationData, $CookedHLGrandTotals, $heading, 'Cooked HL');
$row++;
generateExcelTable($RawMeatLList, $RawMeatCombinationData, $RawMeatGrandTotals, $heading, 'Raw Meat');
$row++;
generateExcelTable($CookedMeatList, $CookedMeatCombinationData, $CookedMeatGrandTotals, $heading, 'Cooked Meat');
$row++;
generateExcelTable($BlockMeatList, $BlockMeatCombinationData, $BlockMeatGrandTotals, $heading, 'Block Meat');
$row++;
generateExcelTable($ThawingMeatList, $ThawingMeatCombinationData, $ThawingMeatGrandTotals, $heading, 'Thawing Meat');


//Set Cell Borders
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();
$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // Convert 'D' to 4

$borderStyle = [
    'borders' => [
        'allborders' => [
            'style' => PHPExcel_Style_Border::BORDER_THIN,
        ],
    ]
];

for ($row = 1; $row <= $highestRow; $row++) {
    for ($col = 0; $col < $highestColumnIndex; $col++) {
        $cell = PHPExcel_Cell::stringFromColumnIndex($col) . $row; // Convert index to cell name (e.g., 'A1')
        if ($sheet->getCell($cell)->getValue() !== null && $sheet->getCell($cell)->getValue() !== '') {
            $sheet->getStyle($cell)->applyFromArray($borderStyle);
        }
    }
}


ob_clean();
// Write to Output
$writer = new PHPExcel_Writer_Excel2007($spreadsheet);
$writer->save('php://output');
function cleanNumber($num) {
    return floatval(str_replace([',', "'"], '', $num)); // Remove comma & apostrophe, convert to number
}