<?php


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed

$ClientConfigData = $_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;

$api = API_QC_TREATMENT_REGISTER_QC;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $Shift = trim($_GET["Shift"]);
    $SoakDate = trim($_GET["txtSoakDate"]);
    $border = "border='1'";
} else {
    $Shift = trim($_POST["Shift"]);
    $SoakDate = trim($_POST["SoakDate"]);
    $border = "";
}

$bodyContent = array(
    "SoakDate" => $SoakDate,
    "Shift" => $Shift
);

$resList = CallAPI($api, "POST", "", $bodyContent);
//echo '<pre>';print_r($resList);die;
$Totals = $resList->Data->Totals;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Data->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->Data->SoakingData;
        $resTotals = $resList->Data->Totals;
        $PageNo = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
        $PageCount = ceil($resList->Data->TotalCount / 10); // Adjust based on pagination logic

        $content = '
       <!-- <div id="nonTableData" style="font-weight: bold; margin-bottom: 10px;">
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width:50px;">S.No</th>
                    <th style="min-width: 120px;max-width: 120px;">Soaking Date</th>
                    </tr>
                     </thead>
            <tbody>
                    <tr>
                    <th style="min-width:50px;">S.No</th>
                    <th style="min-width: 120px;max-width: 120px;">Soaking Date</th>
                    </tr>
                                </tbody>
                                </table>

        </div>-->

    <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
            
        <tr>
            <th style="min-width:100px;"colspan="2">Approval No : 1866</th>
            </tr>
              <tr>
            <th style="min-width:100px; max-width:120px;">FMT/SOP/27</th>
        </tr>
                <tr>
                    <th style="min-width:50px;">S.No</th>
                    <th style="min-width: 120px;max-width: 120px;">Soaking Date</th>
                    <th style="min-width: 100px;width: 100px;">Batch No</th>
                   <!--  <th style="min-width: 300px;width: 300px;">Plant</th> -->
                    <th style="min-width: 100px;max-width: 100px;">Variety</th>
                    <th style="min-width: 100px;max-width: 100px;">Grade</th>
                    <th style="min-width: 100px;max-width: 100px;">Soak In Qty(Kgs)</th>
                    <th style="min-width: 100px;max-width: 100px;">Ice % Qty</th>
                     <th style="min-width: 100px;max-width: 100px;">Water % Qty</th>
                     <th style="min-width: 100px;max-width: 100px;">Phosphate /Non Phosphate Qty(Kgs)</th>
                    <th style="min-width: 100px;max-width: 100px;">Salt(KGs)</th>
                    <th style="min-width: 100px;max-width: 100px;">Start Time</th>
                    <th style="min-width: 100px;max-width: 100px;">Start Count</th>
                     <th style="min-width: 100px;max-width: 100px;">End Time</th>
                     <th style="min-width: 100px;max-width: 100px;">End Count</th>
                     <th style="min-width: 100px;max-width: 100px;">In Temperature(C)</th>
                    <th style="min-width: 100px;max-width: 100px;">Out Temperature(C)</th>
                    <th style="min-width: 100px;max-width: 100px;">Chemical</th>
                    <th style="min-width: 100px;max-width: 100px;">Chlorine dioxide (ML)</th>
                   <th style="min-width: 100px;max-width: 100px;">Pick Up</th>
                  <th style="min-width: 100px;max-width: 100px;">Soaking Time</th>
                   <th style="min-width: 100px;max-width: 100px;">Line</th>
                    <th style="min-width: 100px;max-width: 100px;">Shift</th>
                   <th style="min-width: 100px;max-width: 100px;">Remarks</th>

                </tr>
            </thead>
            <tbody>';

        foreach ($List as $itemBreak) {
            $content .= '<tr>
                        <td style="min-width:50px;">' . $itemBreak->SNo . '</td>
                        <td style="min-width: 120px;max-width: 120px;">' . $itemBreak->SoakingDate . '</td>                      
                         <td style="min-width: 100px;width: 100px;">' . $itemBreak->LotNo . '</td>
                        <!--<td style="min-width: 300px;width: 300px;">' . $itemBreak->Plant . '</td>-->
                        <td style="min-width: 100px;max-width: 100px;"> ' . $itemBreak->Species . '-' . $itemBreak->Variety . '  </td>
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->Grade . '</td>
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->InQuantity . '</td>
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->IceQtyPercentage . '</td>  
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->WaterQtyPercentage . '</td>
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->ChemicalQty . '</td>
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->Salt . '</td>
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->StartTime . '</td>
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->SourceCount . '</td>
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->EndTime . '</td>
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->OutCount . '</td>
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->SoakInTemperature . '</td>  
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->SoakOutTemperature . '</td> 
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->ChemicalName . '</td>
                        <td style="min-width: 100px;max-width: 100px;">';
                         $content .=  !empty($itemBreak->ChlorineDioxide)?$itemBreak->ChlorineDioxide.' ML':'';
                         $content .=  '</td>
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->PickUp . '</td>  
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->TimePeriod . '</td>
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->LineNumber . '</td>
                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->ShiftName . '</td>

                        <td style="min-width: 100px;max-width: 100px;">' . $itemBreak->Remarks . '</td>


                    </tr>';
        }

        $content .= '</tbody>
            <!-- <tfoot> -->
            <!--<tr style="font-weight: bold;">
                <td style="min-width:50px;"></td>
                <td style="min-width: 100px;width: 100px;">Grand Totals</td>
                <td style="min-width: 100px;width: 100px;"></td>
                <td style="min-width: 100px;width: 100px;"></td>
                <td style="min-width: 100px;width: 100px;"></td>

                <td style="min-width: 120px;max-width: 120px;"></td>
                <td style="min-width: 100px;max-width: 100px;">' . $resTotals->TotalSoakInQty . '</td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>

                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>

                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                 <td style="min-width: 100px;max-width: 100px;"></td>
                  <td style="min-width: 120px;max-width: 120px;"></td>

            </tr>-->            
            <!-- </tfoot> -->
        </table>';

        $content .= '
        <div class="mt-3 mb-3">Showing 1 to ' . count($List) . ' out of ' . count($List) . ' entries</div>
        <input type="hidden" id="hdnTotalRecords" value="' . $resList->TotalCount . '" />
        <div>
            <ul class="pagination pagination-rounded justify-content-center">';

        for ($i = 1; $i <= $PageCount; $i++) {
            $pageActive = ($PageNo == $i) ? "active" : "";
            $content .= '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
        }

        $content .= '</ul>
        </div>';
    } else {
        $content .=    "<div class='text-center mt-3 mb-3 text-bold'>Note: " . $error . "</div>";
    }
}

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

    $timestamp = date("YmdHis");
    $List = $resList->Data->SoakingData;
    $for_report = $resList->Data->for_report;
    // Create PHPExcel object
    $spreadsheet = new PHPExcel();
    $sheet = $spreadsheet->getActiveSheet();

    // Set Font Size
    $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
    $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    //$Totals->TotalSoakInQty
    $phospateArray = ['Phospate Batch No:', 'Non Phospate Batch No:', 'Salt Batch No:', 'Soak OutQty:', 'Soak InQty:', 'Pickup:'];
    $row = 1;
    foreach ($phospateArray as $phospateVal) {
        $sheet->mergeCells("A$row:C$row")->setCellValue("A$row", $phospateVal)->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $SoakVal = '';
        if ($phospateVal == 'Phospate Batch No:') {
            $SoakVal = !empty($for_report->PhosphateBatchNumber) ? $for_report->PhosphateBatchNumber : '';
        }
        if ($phospateVal == 'Non Phospate Batch No:') {
            $SoakVal = !empty($for_report->NonPhosphateBatchNumber) ? $for_report->NonPhosphateBatchNumber : '';
        }
        if ($phospateVal == 'Salt Batch No:') {
            $SoakVal = !empty($for_report->SaltBatchNumber) ? $for_report->SaltBatchNumber : '';
        }
        if ($phospateVal == 'Soak InQty:') {
            $SoakVal = !empty($Totals->TotalSoakInQty) ? $Totals->TotalSoakInQty : '';
        }
        $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(10);
        $sheet->mergeCells("D$row:F$row")->setCellValue("D$row", $SoakVal)->getStyle('D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);;

        $row++;
    }
    $headerArr = ['COASTAL AQUA PRIVATE LIMITED', 'PATHARLAGADDA, KAKINADA', 'TREATMENT - REGISTER'];
    $h = 1;
    foreach ($headerArr as $headerVal) {
        $fontSize = 15;
        $rowHeight = 18;
        if ($h == 1) {
            $fontSize  = 20;
            $rowHeight = 23;
        }
        if ($h == 3) {
            $fontSize = 18;
            $rowHeight = 21;
        }
        $sheet->mergeCells("G$h:K$h")->setCellValue("G$h", $headerVal);
        $sheet->getStyle("G$h")->getFont()->setBold(true)->setSize($fontSize);
        //$sheet->getStyle("G$h")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($h)->setRowHeight($rowHeight); // Adjust as needed
        $h++;
    }
    $sheet->mergeCells("G$h:K$h")->setCellValue("G$h", $for_report->ShiftName . '  -   ' . $for_report->lineNumber);
    $sheet->getStyle("G$h")->getFont()->setBold(true)->setSize(10);

    $headerRightArr = ['APPROVAL NO :1866', 'FMT/SOP/27', 'Brand:', 'Date:' . date("d-m-Y", strtotime($SoakDate))];
    $hl = 1;
    foreach ($headerRightArr as $headerRightVal) {
        $sheet->mergeCells("L$hl:T$hl")->setCellValue("L$hl", $headerRightVal)->getStyle('L' . $hl)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("L$hl")->getFont()->setBold(true)->setSize(10);
        $hl++;
    }
    $sheet->getRowDimension($row)->setRowHeight(20);

    // Set table heading bold text
    $sheet->getStyle($row)->getFont()->setBold(true);


// $lastRow = $row - 1; // Last row filled
// $lastColumn = 'T';   // Last column with data

// $styleArray = [
//     'borders' => [
//         'allborders' => [
//             'style' => PHPExcel_Style_Border::BORDER_THIN,
//             'color' => ['rgb' => '000000'],
//         ],
//     ],
// ];

// Apply border to entire data table
// $sheet->getStyle("A1:$lastColumn$lastRow")->applyFromArray($styleArray);

// Thin border for inner cells
$thinBorder = [
    'borders' => [
        'allborders' => [
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
];

$thickBorder = [
    'borders' => [
        'outline' => [
            'style' => PHPExcel_Style_Border::BORDER_THICK,
            'color' => ['rgb' => '000000'],
        ],
    ],
];

$startRow = $row; 
$endRow = $row + count($List); 
$lastColumn = 'T'; 
// Apply thin borders to all header + data cells
$sheet->getStyle("A$startRow:$lastColumn$endRow")->applyFromArray($thinBorder);


    // Header Row
    $headers = [
        'S.No',
        'Batch No',
        'Variety',
        'Grade',
        'Soak In Qty(Kgs)',
        'Ice % Qty',
        'Water % Qty',
        'Phosphate /Non Phosphate Qty(Kgs)',
        'Salt(KGs)',
        'Start Time',
        'Start Count',
        'End Time',
        'End Count',
        'In Temperature(C)',
        'Out Temperature(C)',
        'Chemical',
        'Chlorine dioxide (ML)',
        'Pick Up',
        'Soaking Time',
        'Remarks'
    ];

    $colIndex = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($colIndex . $row, $header);

        $sheet->getStyle($colIndex . $row)->applyFromArray([
            'font' => [
                'bold' => true,
                // 'color' => ['rgb' => 'FFFFFF'], // Optional: black text
            ],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => [
                    // 'rgb' => '000000', // Yellow background
                ],
            ],
            /* 'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ], */
        ]);

        $colIndex++;
    }

    // Column Widths
    foreach (range('A', 'T') as $columnID) {
        if ($columnID === 'B' || $columnID === 'C') {
            $sheet->getColumnDimension($columnID)->setWidth(15);
        } else if ($columnID === 'F' || $columnID === 'K'  || $columnID === 'H') {
            $sheet->getColumnDimension($columnID)->setWidth(10);
        } else if ($columnID === 'I') {
            $sheet->getColumnDimension($columnID)->setWidth(12);
        } else if ($columnID === 'J') {
            $sheet->getColumnDimension($columnID)->setWidth(15);
        } else if ($columnID === 'M') {
            $sheet->getColumnDimension($columnID)->setWidth(12);
        } else if ($columnID === 'E' || $columnID === 'N' || $columnID === 'O' || $columnID === 'P') {
            $sheet->getColumnDimension($columnID)->setWidth(15);
        } else if ($columnID === 'Q' || $columnID === 'R') {
            $sheet->getColumnDimension($columnID)->setWidth(15);
        } else if ($columnID === 'T') {
            $sheet->getColumnDimension($columnID)->setWidth(30);
        } else {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }

    // Align Specific Columns to Right
    $columnsToAlign = ['T'];
    foreach ($columnsToAlign as $col) {
        $sheet->getStyle($col)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    }
    // $sheet->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    // $sheet->getStyle('E')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    // $sheet->getStyle('J')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

    // Fill Data
    $row += 1;
    foreach ($List as $item) {
        $sheet->setCellValue("A$row", $item->SNo);
        $sheet->setCellValue("B$row", $item->LotNo)->getStyle("B$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->setCellValue("C$row", $item->Species . '-' . $item->Variety);
        $sheet->setCellValue("D$row", $item->Grade);
        $sheet->setCellValue("E$row", $item->InQuantity);
        $sheet->setCellValue("F$row", $item->IceQtyPercentage);
        $sheet->setCellValue("G$row", $item->WaterQtyPercentage);
        $sheet->setCellValue("H$row", $item->ChemicalQty);
        $sheet->setCellValue("I$row", $item->Salt);
        $sheet->setCellValue("J$row", $item->StartTime);
        $sheet->setCellValue("K$row", $item->SourceCount);
        $sheet->setCellValue("L$row", $item->EndTime);
        $sheet->setCellValue("M$row", $item->OutCount);
        $sheet->setCellValue("N$row", $item->SoakInTemperature);
        $sheet->setCellValue("O$row", $item->SoakOutTemperature);
        $sheet->setCellValue("P$row", $item->ChemicalName);
        $sheet->setCellValue("Q$row", !empty($item->ChlorineDioxide)?$item->ChlorineDioxide.' ML':'');
        $sheet->setCellValue("R$row", $item->PickUp);
        $sheet->setCellValue("S$row", $item->TimePeriod);
        $sheet->setCellValue("T$row", $item->Remarks);
        $row++;
    }
    if ($Totals) {
        $sheet->setCellValue("A$row", "Total");
        $sheet->setCellValue("E$row", $Totals->TotalSoakInQty);
        //$sheet->setCellValue("J$row", $Totals->TotalSoakOutQty);


        // // Set footer cells to bold
        $sheet->getStyle("A$row")->getFont()->setBold(true);
        $sheet->getStyle("I$row")->getFont()->setBold(true);
        $sheet->getStyle("J$row")->getFont()->setBold(true);
        $row++;
    }
    $sheet->getStyle("A$startRow:$lastColumn$endRow")->applyFromArray($thinBorder);

// Apply thick outer border
$sheet->getStyle("A$startRow:$lastColumn$endRow")->applyFromArray($thickBorder);

//     

// Collect multiple names
$verifiedByArr = [];
$inspectedByArr = [];
foreach ($List as $rowItem) {
    if (!empty($rowItem->VerifiedBy)) {
        $verifiedByArr[] = $rowItem->VerifiedBy;
    }
    
    if (!empty($rowItem->InspectedBy)) {
        $inspectedByArr[] = $rowItem->InspectedBy;
    }
    if (!empty($rowItem->InspectionBy)) {
        $inspectedByArr[] = $rowItem->InspectionBy;
    }
   
}

// If nothing collected from List, fall back to $for_report
if (empty($inspectedByArr) && !empty($for_report->InspectedBy)) {
    $inspectedByArr[] = $for_report->InspectedBy;
}
if (empty($verifiedByArr) && !empty($for_report->VerifiedBy)) {
    $verifiedByArr[] = $for_report->VerifiedBy;
}

// Remove duplicates & join
$verifiedByStr  = implode(", ", array_unique($verifiedByArr));
$inspectedByStr = implode(", ", array_unique($inspectedByArr));


$row += 1;
$sheet->mergeCells("A$row:H$row");
$sheet->setCellValue("A$row", "S - Satisfactory");
$sheet->getStyle("A$row")->getFont()->setSize(11)->setBold(true);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$sheet->getRowDimension($row)->setRowHeight(20);

$row += 1;
$sheet->mergeCells("A$row:H$row");
$sheet->setCellValue("A$row", "Inspected By : $inspectedByStr");
$sheet->getStyle("A$row")->getFont()->setSize(11)->setBold(true);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$sheet->getRowDimension($row)->setRowHeight(20);

$row += 1;
$sheet->mergeCells("A$row:H$row");
$sheet->setCellValue("A$row", "Verified By : $verifiedByStr");
$sheet->getStyle("A$row")->getFont()->setSize(11)->setBold(true);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$sheet->getRowDimension($row)->setRowHeight(20);




    // Write to Output
    $writer = new PHPExcel_Writer_Excel2007($spreadsheet);

    //$filePath = $_SERVER['DOCUMENT_ROOT'] . "/treatment-register-report.php" . $timestamp . ".xlsx";
    $filePath = $_SERVER['DOCUMENT_ROOT'] . "/Treatment_Register_Report" . $timestamp . ".xlsx";
    $writer->save($filePath);
    //$data['filename'] = $host. "download-file.php?file="."Treatment_Register_Report" . $timestamp . ".xlsx";
    $filename = "Treatment_Register_Report" . $timestamp . ".xlsx";
    //$validator->removeWhitespace($filename);
    $data['filename'] =  "/download-file.php?file=$filename";
    $data['status'] = 'Done';
    echo json_encode($data);
} else {
    echo $content;
}
