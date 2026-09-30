<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
require ROOT . 'Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed

        $api = API_GRADING_INSPECTION_DETAILS;
        $StartDate = trim($_GET["startDate"]);
        $EndDate = trim($_GET["endDate"]);
        
        $bodyContent = array(
            'StartDate' => $StartDate,
            'EndDate' => $EndDate,
            'Type' => 'getReportDetails',
        );
        $resList = CallAPI($api, "GET", "", $bodyContent);
        // var_dump($resList); exit;

if (isset($resList->Error) && $resList->Error == false) {
                ob_start();
                $dataList = $resList->Data;
                $spreadsheet = new PHPExcel();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->getDefaultStyle()->getFont()->setSize(14);

                // === Header Rows ===
                $sheet->setCellValue('A1', 'COASTAL AQUA PRIVATE LIMITED');
                $sheet->setCellValue('A2', 'PATHARLAGADDA, KAKINADA');
                $sheet->setCellValue('A3', 'ONLINE GRADING INSPECTION REPORT');

                // Calculate the last column letter based on data
                $lastColLetter = $sheet->getHighestColumn();

                // Merge all cells in row 4 across data columns
                $sheet->mergeCells("A4:{$lastColLetter}4");

                // Set today's date in merged cell
                $today = date("d/m/Y");
                $sheet->setCellValue("A4", "Date: $today");

                // Style: align left, normal font
                $sheet->getStyle("A4")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("A4")->getFont()->setSize(12);

                // Determine column span based on record count
                $colCount = count($dataList) + 1;
                $lastCol = PHPExcel_Cell::stringFromColumnIndex($colCount - 1);

                // Merge headers across all columns
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->mergeCells("A3:{$lastCol}3");

                // Style Row 1: Big Bold
                $sheet->getStyle("A1")->getFont()->setSize(22)->setBold(true);
                $sheet->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                // Style Row 2: Small
                $sheet->getStyle("A2")->getFont()->setSize(16);
                $sheet->getStyle("A2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                // Style Row 3: Medium + Underline
                $sheet->getStyle("A3")->getFont()->setSize(18)->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
                $sheet->getStyle("A3")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                // === Data Section ===
                $fields = [
                    'Arrival Date' => 'ArrivalDate',
                    'Batch Code' => 'BatchCode',
                    'Variety' => 'Variety',
                    'Grade' => 'Grade',
                    'General Appearance' => 'GeneralAppearance',
                    'Color' => 'Color',
                    'Freshness' => 'Freshness',
                    'Odour' => 'Odour',
                    'Texture' => 'Texture',
                    'Temperature' => 'Temperature',
                    'Count Per Lb' => 'CountPerLb',
                    'Big Piece per Gram' => 'BigPieceperGram',
                    'Small Piece per Gram' => 'SmallPieceperGram',
                    'Uniformity' => 'Uniformity',
                    'Total Pieces per Gram' => 'TotalPiecesperGram',
                    'Deterioration' => 'Deterioration',
                    'Discolouration' => 'Discolouration',
                    'Black Spots' => 'BlackSpots',
                    'Soft Shell' => 'SoftShell',
                    'HON Quantity' => 'HONQuantity',
                    'HL Quantity' => 'HLQuantity',
                    'Yield %' => 'Yield',
                    'VerifiedBy' => 'VerifiedBy',
                    'InspectedBy' => 'InspectedBy'
                ];

                // Data starts from Row 5
                $sheet->setCellValue('A5', 'No.');
                $col = 'B';
                $recordIndex = 1;
                foreach ($dataList as $row) {
                    $sheet->setCellValue($col . '5', $recordIndex++);
                    $col++;
                }

                // Field Rows
                $rowIndex = 6;
                foreach ($fields as $fieldLabel => $property) {
                    $sheet->setCellValue('A' . $rowIndex, $fieldLabel);
                    $col = 'B';
                    foreach ($dataList as $record) {
                        $value = isset($record->$property) ? $record->$property : '';
                        $sheet->setCellValue($col . $rowIndex, $value);
                        $col++;
                    }
                    $rowIndex++;
                }

                // Bold headers
                $sheet->getStyle("A5:{$lastCol}5")->getFont()->setBold(true);
                $sheet->getStyle("A5:A{$sheet->getHighestRow()}")->getFont()->setBold(true);

                // Auto width
                foreach (range('A', $lastCol) as $colID) {
                    $sheet->getColumnDimension($colID)->setAutoSize(true);
                }

                // Get the full data range
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn(); // e.g., 'G'
                $range = "B5:{$highestCol}{$highestRow}";

                // Apply horizontal and vertical center alignment
                $sheet->getStyle($range)
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                // Start after last data row + spacing
                $footerStartRow = $sheet->getHighestRow() + 2;
                

                // Get the last data column letter and index
                $lastColLetter = $sheet->getHighestColumn();
                $lastColIndex = PHPExcel_Cell::columnIndexFromString($lastColLetter); // 1-based

                // Divide into 3 equal parts (approximate)
                $chunkSize = floor($lastColIndex / 3);

                // Compute merged column ranges
                $startCol1 = 0;
                $endCol1 = $chunkSize - 1;

                $startCol2 = $chunkSize;
                $endCol2 = $chunkSize * 2 - 1;

                $startCol3 = $chunkSize * 2;
                $endCol3 = $lastColIndex - 1;

                // Convert indexes to column letters
                $col1Start = PHPExcel_Cell::stringFromColumnIndex($startCol1);
                $col1End   = PHPExcel_Cell::stringFromColumnIndex($endCol1);

                $col2Start = PHPExcel_Cell::stringFromColumnIndex($startCol2);
                $col2End   = PHPExcel_Cell::stringFromColumnIndex($endCol2);

                $col3Start = PHPExcel_Cell::stringFromColumnIndex($startCol3);
                $col3End   = PHPExcel_Cell::stringFromColumnIndex($endCol3);

                // Signature row number (after data + spacing)
                $footerRow = $sheet->getHighestRow() + 4;
                $sheet->setCellValue('A30', 'REMARKS');
                
                // Merge cells for 3 columns
                $sheet->mergeCells("{$col1Start}{$footerRow}:{$col1End}{$footerRow}");
                $sheet->mergeCells("{$col2Start}{$footerRow}:{$col2End}{$footerRow}");
                $sheet->mergeCells("{$col3Start}{$footerRow}:{$col3End}{$footerRow}");

                // Set labels
                $sheet->setCellValue("{$col1Start}{$footerRow}", "INSPECTED BY");
                $sheet->setCellValue("{$col2Start}{$footerRow}", "Q.A AUTHORISED");
                $sheet->setCellValue("{$col3Start}{$footerRow}", "VERIFIED BY");

                // Style: center align + bold
                $sheet->getStyle("{$col1Start}{$footerRow}:{$col3End}{$footerRow}")
                    ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("{$col1Start}{$footerRow}:{$col3End}{$footerRow}")
                    ->getFont()->setBold(true)->setSize(12);
                
                // Get full data range: from A5 to bottom-right cell
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn(); // e.g. 'I'

                $dataRange = "A1:{$highestCol}{$highestRow}";

                // Define thin border style
                $borderStyle = [
                    'borders' => [
                        'allborders' => [
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'] // black
                        ]
                    ]
                ];

                // Apply to data range
                $sheet->getStyle($dataRange)->applyFromArray($borderStyle);
                
                // Output the Excel file
                $filename = "Grading_Inspection_Report_" . date("d-m-Y") . ".xlsx";
                header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                header("Content-Disposition: attachment; filename=\"$filename\"");

                if (ob_get_length()) {
                    ob_clean();
                }
                $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
                $writer->save('php://output');
                exit;
            } else {
                echo "Note: " . $resList->Error;
            }