<?php 
require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed
$api = API_QC_QUALITY_ASSURANCE_CHECK_LIST_GET_FOR_LISTING;
$_GET['BatchCode'] = !empty($_GET['BatchCode'])?explode(',',$_GET['BatchCode']):[];
$bodyContentPost = $_GET;
$arrivalDate = !empty($_GET['ArrivalDate'])?$_GET['ArrivalDate']:'';
$results = callAPI($api, "POST", "", $bodyContentPost);
//echo '<pre>';print_r($results);die;
if(!empty($results->Data)){
    $resList = $results->Data;
    $error = $resList->Error;
    if (!isset($error) || empty($error)) {
        $List = $resList->Data->ResultsData;
        $for_report = $resList->Data->for_report;

        $spreadsheet = new PHPExcel();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $headerArr = ['COASTAL AQUA PRIVATE LIMITED', 'PATHARLAGADDA, KAKINADA', 'QUALITY ASSURANCE DEPARTMENT','ONLINE ORGANOLEPTIC INSPECTION REPORT - BLOCK / IQF'];
        $h = 1;$row = 1;
        foreach ($headerArr as $headerVal) {
            $fontSize = 15;$rowHeight = 18;
            $bold = true;
            if($h == 1){
                $fontSize  = 20;
                $rowHeight = 23;
            }
            if($h == 3){
                $fontSize = 18;
                $rowHeight = 21;
                $bold = false;
            }
            $sheet->mergeCells("A$h:T$h")->setCellValue("A$h", $headerVal);
            $sheet->getStyle("A$h")->getFont()->setBold($bold)->setSize($fontSize);
            $sheet->getRowDimension($h)->setRowHeight($rowHeight); // Adjust as needed
            $h++;
            $row++;
        }
         $headerRightArr = ['APPROVAL NO :1866', 'FMT/SOP/27', 'Date:'.$arrivalDate];
        $hl = 1;
        foreach ($headerRightArr as $headerRightVal) {
            $sheet->mergeCells("U$hl:AI$hl")->setCellValue("U$hl", $headerRightVal)->getStyle('U' . $hl)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("U$hl")->getFont()->setBold(true)->setSize(10);
            $hl++;
        }
        $sheet->getRowDimension($row)->setRowHeight(25);

            // Set table heading bold text
        $sheet->getStyle($row)->getFont()->setBold(true);
        // Header Row
        $headers = [
            'S.No',
            'Invoice No ',
            'Production Date',
            'Batch Number ',
            'Production Code',
            'FreezingType',
            'Brand',
            'Variety',
            'Type of Packing',
            'Diclared Size Grade',
            'Colour',
            'General Appearance',
            'Freshness',
            'Odour',
            'Texture',
            'Temperature',
            'Filling Weight in Gms',
            'Deglazed wt',
            'Glaze %',
            'Count',
            'Big Pieces (gms)',
            'Small Pieces (gms)',
            'Uniformity',
            'Total Pieces / Slab',
            'Dehydration',
            'Discolouration',
            'Black Spots / Tails',
            'BKN Damaged Pieces',
            'Legs, Veins, Shell Bits',
            'Hanging Meat',
            'Soft Shell',
            'Foreign Matter',
            'Tail BKN ',
            'Total % of Defect',
            'Sailing Intact',
            'Remarks',
        ];
        $colIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($colIndex . $row, $header);
            $sheet->getColumnDimension($colIndex)->setAutoSize(true);
            $colIndex++;
        }
         $columnsToAlign = ['AI'];
        foreach ($columnsToAlign as $col) {
            $sheet->getStyle($col)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }
        $row += 1;
        foreach ($List as $item) {
            $sheet->setCellValue("A$row",$item->SNO);
            $sheet->setCellValue("B$row",$item->InvoiceNo);
            $sheet->setCellValue("C$row",$item->ArrivalDate);
            $sheet->setCellValue("D$row",$item->ArrivalNumber)->getStyle("D$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
           // $sheet->setCellValue("E$row","$item->ProductionCode")->getStyle("E$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);;
            /* if (is_numeric($item->ProductionCode)) {
                $sheet->setCellValue("E$row", $item->ProductionCode)
                    ->getStyle("E$row")
                    ->getNumberFormat()
                    ->setFormatCode('0.##E+00');
            } else {
                $sheet->setCellValueExplicit("E$row", $item->ProductionCode, PHPExcel_Cell_DataType::TYPE_STRING);
            } */
            $sheet->setCellValueExplicit("E$row", $item->ProductionCode, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue("F$row",$item->FreezingType);
            $sheet->setCellValue("G$row",$item->BrandName);
            $sheet->setCellValue("H$row",$item->Variety);
            $sheet->setCellValue("I$row",$item->Packing);
            $sheet->setCellValue("J$row",$item->DiclaredSizeGrade);
            $sheet->setCellValue("K$row",$item->Colour);
            $sheet->setCellValue("L$row",$item->GeneralAppearance);
            $sheet->setCellValue("M$row",$item->Freshness);
            $sheet->setCellValue("N$row",$item->Odour);
            $sheet->setCellValue("O$row",$item->Texture);
            $sheet->setCellValue("P$row",$item->Temperature);
            $sheet->setCellValue("Q$row",$item->FillingWeightInGms);
            $sheet->setCellValue("R$row",$item->DeglazedWt);
            $sheet->setCellValue("S$row",$item->Glaze);
            $sheet->setCellValue("T$row",$item->Count);
            $sheet->setCellValue("U$row",$item->BigPieces);                            
            $sheet->setCellValue("V$row",$item->SmallPieces);
            $sheet->setCellValue("W$row",$item->Uniformity);
            $sheet->setCellValue("X$row",$item->TotalPiecesPerSlab);
            $sheet->setCellValue("Y$row",$item->Dehydration);
            $sheet->setCellValue("Z$row",$item->Discolouration);
            $sheet->setCellValue("AA$row",$item->BlackSpotsPerTails);
            $sheet->setCellValue("AB$row",$item->BKNDamagedPieces);
            $sheet->setCellValue("AC$row",$item->LegsVeinsShellBits);
            $sheet->setCellValue("AD$row",$item->HangingMeat);
            $sheet->setCellValue("AE$row",$item->SoftShell);
            $sheet->setCellValue("AF$row",$item->ForeignMatter);
            $sheet->setCellValue("AG$row",$item->TailBKN);
            $sheet->setCellValue("AH$row",$item->TotalPercentageOfDefect);
            $sheet->setCellValue("AI$row",$item->SailingIntact);
            $sheet->setCellValue("AJ$row",$item->Remarks);
            $row++;
        }
        // $row += 1;

         $sheet->mergeCells("A$row:G$row")->setCellValue("A$row", "Inspected By: $for_report->CheckedBy")->getStyle("A$row")->getFont(11)->setBold(true);
        $sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("H$row:N$row")->setCellValue("H$row", "Verifyed By : $for_report->VerifiedBy")->getStyle("H$row")->getFont(11)->setBold(true);
        $sheet->getStyle("H$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension($row)->setRowHeight(35);
      
         // Write to Output
        $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
        
        $filePath = $_SERVER['DOCUMENT_ROOT'] . "/online_organoleptic_inspection_report" . $timestamp . ".xlsx";
        $writer->save($filePath);
        $filename = "online_organoleptic_inspection_report" . $timestamp . ".xlsx";
        //$validator->removeWhitespace($filename);
        $data['filename'] =  "/download-file.php?file=$filename";
        $data['status'] = 'Done';
        echo json_encode($data);
    }else{
        $data['status'] = 'Done';
        $data['error'] = $error;;
        echo json_encode($data);
    }
}else{
        $data['status'] = 'Done';
        $data['error'] = 'No data found.';
        echo json_encode($data);
}
?>