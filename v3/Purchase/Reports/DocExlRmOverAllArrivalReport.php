<?php
//session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed

if (isset($_GET["ArrivalFromDate"]) && !empty($_GET["ArrivalFromDate"])) {
    $ArrivalFromDate = trim($_GET["ArrivalFromDate"]);
} else {
    $ArrivalFromDate = "";
}

$ArrivalDate = trim($_GET["ArrivalDate"]);
$PlantID = trim($_GET["PlantID"]);
$PlantName = trim($_GET["PlantName"]);
if ($PlantName == "Select") {
    $PlantName = "";
}
$CenterID = trim($_GET["CenterID"]);
$CenterName = trim($_GET["CenterName"]);
if ($CenterName == "Select") {
    $CenterName = "";
} else {
    $CenterName = "-" . $CenterName;
}

$CompanyID = trim($_GET["CompanyID"]);

$Species = trim($_GET["Species"]);

$api = API_RM_ARRIVAL_REPORT;

$bodyContent = array(
    'ArrivalFromDate' => $ArrivalFromDate,
    'ArrivalDate' => $ArrivalDate,
    'PlantID' => $PlantID,
    'CenterID' => $CenterID,
    'CompanyID' => $CompanyID,
    'Species' => $Species
);

// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;

$resRmArrivalReport = CallAPI($api, "GET", "", $bodyContent);

// echo json_encode($resRmArrivalReport, JSON_PRETTY_PRINT);
// exit;

$CompanyName = trim($_GET["CompanyName"]);
if ($CompanyName == "Select") {
    $CompanyName = $resRmArrivalReport->CompanyName;
}

$resHeader = $resRmArrivalReport->Headers;
$headerCount = count($resHeader);

$resArrivalData = $resRmArrivalReport->ArrivalData;
$resSubTotal = $resRmArrivalReport->SubTotal;
$resGrandTotal = $resRmArrivalReport->GrandTotal;
$resSummary = $resRmArrivalReport->Summary;

// var_dump($resRmArrivalReport);

// echo json_encode($resRmArrivalReport);

// header("Content-Type: application/xls");
// header("Content-Disposition: attachment; filename=" . $FileName . ".xls");




$topRow = $headerCount * 3 + 1;
// echo $topRow;
// exit;
// $content = '
// <table class="table  wrap w-100" border="1">
    
//     <thead>
//         <tr>
//             <th colspan="' . $topRow . '">' . $CompanyName . '</th>
//         </tr>    
//         <tr>';
// if ($ArrivalFromDate != "") {
//     $content .= '<th colspan="' . $topRow . '">RAW MATERIAL ARRIVAL ON ' . $PlantName . " - " . date('d/m/Y', strtotime($ArrivalFromDate)) . " - " . date('d/m/Y', strtotime($ArrivalDate)) . ' </th>';
// } else {
//     $content .= '<th colspan="' . $topRow . '">RAW MATERIAL ARRIVAL ON ' . $PlantName . " - " . date('d/m/Y', strtotime($ArrivalDate)) . ' </th>';
// }
// $content .= '
//         </tr>    
//         <tr>
//             <th>Center</th>';
// foreach ($resHeader as $headers) {
//     $content .= '<th colspan="3">' . $headers . '</th>';
// }

// $content .= '
//             <th>Total Qty</th>
//             <th>Total Value</th>
//         </tr>        
//     </thead>
    
//     <tbody>
//         <tr>
//             <td>&nbsp;</td>';
// for ($a = 1; $a <= $headerCount; $a++) {
//     $content .= '
//                     <td  style="width: 90px;text-align:right">KGS</td>
//                     <td style="width: 90px;text-align:right">Avg. Rate</td>
//                     <td style="width: 90px;text-align:right">Values</td>';
// }
// $content .= '
//             <td  style="width: 90px;text-align:right">&nbsp;</td>
//             <td  style="width: 90px;text-align:right">&nbsp;</td>
//         </tr>';

// foreach ($resArrivalData as $arrivalData) {
//     $content .= '
//                 <tr>
//                     <td>' . $arrivalData->Center . '</td>';
//     foreach ($arrivalData->item as $subItem) {  // "\t'0.123456789'";
//         $content .= "
//                             <!--<td>\xA0" . $subItem->Kgs . "</td>-->
//                             <!--<td style='text-align: right'>$subItem->Kgs</td>-->
//                             <td style='text-align: right'>$subItem->Kgs</td>
//                             <td style='text-align: right'>$subItem->AvgRate</td>
//                             <td style='text-align: right'>$subItem->Value</td>";
//     }
//     $content .= "
//                     <td style='text-align: right'>$arrivalData->Qty</td>
//                     <td style='text-align: right'>$arrivalData->Value</td>
//                 </tr>";
// }
// // foreach($resArrivalData as $arrivalData) {
// $content .= '        
//             <tr>
//                 <td>Total</td>';
// // for($i = 1; $i <= $headerCount; $i++) {
// foreach ($resSubTotal as $SubTotal) {
//     $content .= "
//                             <td style='text-align: right'>$SubTotal->Kgs</td> 
//                             <td style='text-align: right'>$SubTotal->AvgRate</td>
//                             <td style='text-align: right'>$SubTotal->Value</td>
//                             ";
// }

// $content .= "
//                 <td style='text-align: right'>$lastElement->WeightTotal</td>
//                 <td style='text-align: right'>$lastElement->ValueTotal</td>
//             </tr>";
// // }
// $content .= '
//     </tbody>
    
//     <tfoot>
//         <tr>
//             <th colspan="7">Summary of Report</th>
//         </tr>
        
//         <tr>
//             <th>&nbsp;</th>
//             <th colspan="2">Qty</th>
//             <th colspan="2">A.Rate</th>
//             <th colspan="2">Value</th>
//         </tr>';
// foreach ($resSummary as $summary) {
//     $content .= "
//             <tr>
//                 <th>$summary->Species</th>
//                 <th style='text-align: right' colspan='2'>$summary->WeightTotal</th>
//                 <th style='text-align: right' colspan='2'>$summary->AvgRate</th>
//                 <th style='text-align: right' colspan='2'>$summary->ValueTotal</th>
//             </tr>";
// }
// $content .= '
//         <tr>
//             <th colspan="7"></th>
//             <th colspan="3" style="text-align:center;border:0px">Signature</th>
//         </tr>
//     </tfoot> 
        
// </table>
// ';

// echo $content;

// Create PHPExcel object
$spreadsheet = new PHPExcel();
$sheet = $spreadsheet->getActiveSheet();

// // Set Font Size
$spreadsheet->getDefaultStyle()->getFont()->setSize(10);

$sheet->setCellValue("A1", $CompanyName);
if ($ArrivalFromDate != "") {
    $sheet->setCellValue("A2", 'RAW MATERIAL ARRIVAL ON ' . $PlantName . " - " . date('d/m/Y', strtotime($ArrivalFromDate)) . " - " . date('d/m/Y', strtotime($ArrivalDate)) );
} else {
    $sheet->setCellValue("A2", 'RAW MATERIAL ARRIVAL ON ' . $PlantName . " - " . date('d/m/Y', strtotime($ArrivalDate)));
}
$endCellIndex = PHPExcel_Cell::columnIndexFromString("A");
$endCell = PHPExcel_Cell::stringFromColumnIndex($endCellIndex + $topRow); // Adjusted to include the total columns
$sheet->mergeCells("A1:".$endCell."1");
$sheet->mergeCells("A2:".$endCell."2");
$sheet->getStyle("A1:".$endCell."3")->getFont()->setBold(true);
$sheet->getStyle("A1:".$endCell."3")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//Add Header Row to Excel
$sheet->setCellValue('A3', 'Center');
$startColumn = 'B';
$startRow = 3;
$endColumn = $endCell;

// Dynamically convert column letters to indices
$startColIndex = PHPExcel_Cell::columnIndexFromString($startColumn);
$endColIndex = PHPExcel_Cell::columnIndexFromString($endColumn);

$colIndex = $startColIndex; // Start with the starting column index
foreach ($resHeader as $header) {
    if ($colIndex + 3 > $endColIndex) break;
    $startMergeCol = PHPExcel_Cell::stringFromColumnIndex($colIndex - 1);
    $endMergeCol = PHPExcel_Cell::stringFromColumnIndex($colIndex + 1);
    $sheet->mergeCells("$startMergeCol$startRow:$endMergeCol$startRow");
    $sheet->setCellValue("$startMergeCol$startRow", $header);
    $colIndex+=3;
}
$sheet->setCellValue($endCell . '3', 'Total Value');
$sendLastCell = PHPExcel_Cell::stringFromColumnIndex($endColIndex - 2);
$sheet->setCellValue($sendLastCell . '3', 'Total Qty');

// Column Widths
// foreach (range('A', $endCell) as $columnID) {
//         $sheet->getColumnDimension($columnID)->setAutoSize(true);
// }
$totalCols = 1 + ($headerCount * 3) + 2; // A + (3 columns per header) + 2 totals

for ($col = 0; $col < $totalCols; $col++) {
    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
}

// Add Sub Heading Row to Excel
$startColumn = 'B';
$startRow = 4;
$endColumn = $endCell;
$startColIndex = PHPExcel_Cell::columnIndexFromString($startColumn);
$endColIndex = PHPExcel_Cell::columnIndexFromString($endColumn);
$values = ['KGS', 'Avg rate', 'Values'];
$currentColIndex = $startColIndex; 
while ($currentColIndex <= $endColIndex-2) {
    foreach ($values as $value) {
        if ($currentColIndex> $endColIndex-2) break; // Ensure we don't go out of bounds
        $startCol = PHPExcel_Cell::stringFromColumnIndex($currentColIndex - 1);
        $sheet->setCellValue("$startCol$startRow", $value);
        $currentColIndex ++;
    }
}

// Fill Data
$row = 5;
// $Col = 'A';
foreach ($resArrivalData as $arrivalData) {
    $Col = 'A';
    $sheet->setCellValue("$Col$row",$arrivalData->Center);
    ++$Col;
    foreach ($arrivalData->item as $subItem) {  
        $sheet->setCellValue("$Col$row",$subItem->Kgs);
        $sheet->getStyle("$Col$row")->getNumberFormat()->setFormatCode('#,##0.000');
        ++$Col;
        $sheet->setCellValue("$Col$row",$subItem->AvgRate);
        ++$Col;
        $sheet->setCellValue("$Col$row",$subItem->Value);
        ++$Col;
    }
    $sheet->setCellValue("$Col$row",$arrivalData->Qty);
    $sheet->getStyle("$Col$row")->getNumberFormat()->setFormatCode('#,##0.000');
    ++$Col;
    $sheet->setCellValue("$Col$row",$arrivalData->Value);
    $row++;
}

$TotalCol = 'A';
$sheet->setCellValue("$TotalCol$row","Totals");
++$TotalCol;
foreach ($resSubTotal as $SubTotal) {
    $sheet->setCellValue("$TotalCol$row",$SubTotal->Kgs);
    ++$TotalCol;
    $sheet->setCellValue("$TotalCol$row",$SubTotal->AvgRate);
    ++$TotalCol;
    $sheet->setCellValue("$TotalCol$row",$SubTotal->Value);
    ++$TotalCol;
}
$lastElement = array_slice($resSummary, -1)[0];
$sheet->setCellValue("$TotalCol$row",$lastElement->WeightTotal);
$sheet->getStyle("$TotalCol$row")->getNumberFormat()->setFormatCode('#,##0.000');
++$TotalCol;
$sheet->setCellValue("$TotalCol$row",$lastElement->ValueTotal);
++$row;
$SummaryCol = 'A';
$EndMergeCell = chr(ord($SummaryCol) +6);
$sheet->mergeCells("$SummaryCol$row:$EndMergeCell$row");
$sheet->setCellValue("$SummaryCol$row", 'Summary of Report');
$sheet->getStyle("$SummaryCol$row:$EndMergeCell$row")->getFont()->setBold(true);
$sheet->getStyle("$SummaryCol$row:$EndMergeCell$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
++$row;

$SummaryCol = 'B';
$EndMergeCell2 = chr(ord($SummaryCol) +1);
$sheet->mergeCells("$SummaryCol$row:$EndMergeCell2$row");
$sheet->setCellValue("$SummaryCol$row", 'Qty');
++$SummaryCol;
++$SummaryCol;
$EndMergeCell2 = chr(ord($SummaryCol) +1);
$sheet->mergeCells("$SummaryCol$row:$EndMergeCell2$row");
$sheet->setCellValue("$SummaryCol$row", 'A.Rate');
++$SummaryCol;
++$SummaryCol;
$EndMergeCell2 = chr(ord($SummaryCol) +1);
$sheet->mergeCells("$SummaryCol$row:$EndMergeCell2$row");
$sheet->setCellValue("$SummaryCol$row", 'Value');

$sheet->getStyle("B$row:$EndMergeCell$row")->getFont()->setBold(true);
++$row;
$SummaryCol = 'A';
foreach ($resSummary as $summary) {
    $sheet->setCellValue("$SummaryCol$row", $summary->Species);
    ++$SummaryCol;
    $EndMergeCell2 = chr(ord($SummaryCol) +1);
    $sheet->mergeCells("$SummaryCol$row:$EndMergeCell2$row");
    $sheet->setCellValue("$SummaryCol$row", cleanNumber($summary->WeightTotal));
     $sheet->getStyle("$SummaryCol$row")->getNumberFormat()->setFormatCode('#,##0.000');
   // $sheet->getStyle("$SummaryCol$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    ++$SummaryCol;
    ++$SummaryCol;
    $EndMergeCell2 = chr(ord($SummaryCol) +1);
    $sheet->mergeCells("$SummaryCol$row:$EndMergeCell2$row");
    $sheet->setCellValue("$SummaryCol$row", cleanNumber($summary->AvgRate));
    $sheet->getStyle("$SummaryCol$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    ++$SummaryCol;
    ++$SummaryCol;
    $EndMergeCell2 = chr(ord($SummaryCol) +1);
    $sheet->mergeCells("$SummaryCol$row:$EndMergeCell2$row");
    $sheet->setCellValue("$SummaryCol$row", cleanNumber($summary->ValueTotal));
    $sheet->getStyle("$SummaryCol$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    $row++;
    $SummaryCol = 'A'; // Reset column index for next row
}
++$EndMergeCell;
$sheet->mergeCells("$EndMergeCell$row:$endCell$row");
$sheet->setCellValue("$EndMergeCell$row", 'Signature');
$sheet->getStyle("$EndMergeCell$row:$endCell$row")->getFont()->setBold(true);
$styleArray = array(
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
        ),
    ),
);

$sheet->getStyle('A1:' . $endCell . $row)->applyFromArray($styleArray);
ob_clean();

$FileName = "Rm-Over-All-Arrival-Report";
header("Content-type: application/vnd.ms-excel");
header('Content-Disposition: attachment; filename=' . $FileName . $CenterName . '-' . date("d-m-Y") . '.xlsx');

    // Write to Output
     $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
     $writer->save('php://output');

function cleanNumber($num) {
    return floatval(str_replace([',', "'"], '', $num)); // Remove comma & apostrophe, convert to number
}