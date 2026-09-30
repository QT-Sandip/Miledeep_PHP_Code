<?php 
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ERROR);
//echo ini_get('memory_limit');die;
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_DAILY_PRODUCTION_REPORT_FINAL_AND_DUMMY;


$bodyContent = array(
    "ProductionDate" => $_POST['productionDate'],
    "ColdstoreID" => $_POST['coldStores'],
    "PlantID" => $_POST['plantID'],
    "TypeProduct" => $_POST['typeProduct'],
    "Type" => $_POST['type'],
    "FreezingType" => $_POST['freezingType'],
    "Variety" => $_POST['typeVariety'],
    "Treatment" => $_POST['typeSoaking'],
     "PackingStyle" => $_POST['pakingStyle'],
    "Grade" => $_POST['typeGrade'],
    "Species" => $_POST['typeSpecies'],
    "StockBlgsTo" => $_POST['drpStockBlgsTo']
);
$resList = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($resList);die;
if (isset($resList) && !empty($resList)) {
    $error = $resList->Error;
    if (!isset($error) || empty($error)) {
        $timestamp = date("YmdHis");
        $List = $resList->Data;
        $ListCnt = count($resList->Data);
        $Slice = 2;//after mention no. of column data printing one by one
        $mergeRow = "I";
        $datasets = [
            $List
        ];
        $ExchangeRate = (float)$resList->ExchangeRate;
       // $GrandTotals = $resList->GrandTotals;

        $hasData = false;
        foreach ($datasets as $dataItem) {
            if (!empty($dataItem)) {
                $hasData = true;
                break;
            }
        }
        if($hasData){
            set_time_limit(0);
           // ini_set('memory_limit', '1024M');
            ini_set('memory_limit', '-1');
            require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed
            $spreadsheet = new PHPExcel();
            
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->getColumnDimension("A")->setWidth(15);
            $borderArr = [
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            ];
            $row = 1;
            $formattedDate = '';
            if(!empty($_POST['productionDate'])){ 
                $formattedDate = date('d.m.Y', strtotime($_POST['productionDate']));
            }
            $clientName = "STOCK DATA AS ON : $formattedDate";
            if(!empty($_SESSION['ClientID']) && $_SESSION['ClientID'] == 'CAPLV3'){ 
                $clientName = "COASTAL  AQUA PRIVATE LTD  STOCK DATA AS ON : $formattedDate";
            } 
            if(!empty($clientName)){                
                $sheet->mergeCells("B$row:$mergeRow$row")->setCellValue("B$row", $clientName);
                $sheet->getStyle("B$row")->getFont()->setBold(true)->setSize(12)
                    ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                $sheet->getStyle("B$row")->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension($row)->setRowHeight(20);
                $row++;
            }
            if (!empty($List)){ 
                $borderArr = [
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                ];               
                $ch = 0;
                $GrandTotalMCs = $GrandTotalKgs = $GrandTotalDollarVal = $GrandTotalInr = 0;
                foreach ($List as $Item) {
                    $VarietyCNT = count($Item->VarietyCombinationData);
                    
                    //echo '<pre>';print_r($Item->VarietyCombinationData);die;
                    $sheet->setCellValue("A$row", $Item->FreezingType .' '.$Item->PackingTypeID);
                    $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                    $sheet->getStyle("A$row")->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("A$row")->applyFromArray($borderArr);

                    $row++;
                    $startRow = $row;
                    $sheet->setCellValue("A$row", "Sum of NO .OF MC");

                    $GradeCnt = count($Item->GradesData) + 4;
                    /* $GradeIndex = 'E';
                    foreach($Item->GradesData as $GradesData){
                        $GradeIndex++;
                    } */
                    $GradeColumn = PHPExcel_Cell::stringFromColumnIndex($GradeCnt);
                   // echo "E$row:$GradeColumn$row";die;
                    $sheet->mergeCells("E$row:$GradeColumn$row")->setCellValue("E$row", "GRADE");
                    /* $sheet->getStyle("E$row")->getFont()->setBold(true)->setSize(12);
                    $sheet->getStyle("E$row")->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); */

                    $GradeRow = $row;//for PROD. WEIGHT vertical merging
                    $row++;
                    $sheet->setCellValue("A$row", "VARIETY");

                    $sheet->setCellValue("B$row", "TREAT");

                    $sheet->setCellValue("C$row", "PACKS");

                    $sheet->mergeCells("D$GradeRow:D$row");
                    $sheet->setCellValue("D$row", "PROD. WEIGHT");
                    if (!empty($Item->GradesData)) {
                        $GradeIndex = 'E';
                        foreach($Item->GradesData  as $gradeI => $itemGrades){
                            $sheet->setCellValue("$GradeIndex$row", $itemGrades->Grade);//21/25//
                                
                            $GradeIndex++;
                        }
                    }
                    $sheet->setCellValue("$GradeIndex$row", 'Total');//variety wise Total

                    $row++;
                    if (!empty($Item->VarietyCombinationData)) {
                        $VarietyRow = $row;
                        foreach ($Item->VarietyCombinationData as $ItemBreakUp) {                            
                            $VarietyIndex = "A";//A4
                            $sheet->setCellValue("$VarietyIndex$VarietyRow", "$ItemBreakUp->Species $ItemBreakUp->ShortName");//21/25//

                            $VarietyIndex++;//B4
                            $sheet->setCellValue("$VarietyIndex$VarietyRow", "$ItemBreakUp->SockingType");//21/25//

                            $VarietyIndex++;//C4
                            $sheet->setCellValue("$VarietyIndex$VarietyRow", "$ItemBreakUp->TotalSlabs");//21/25//

                            $VarietyIndex++;//D4
                            $sheet->setCellValue("$VarietyIndex$VarietyRow", "$ItemBreakUp->SlabWeight");//21/25//

                            $VarietyRow++;//D4
                        }
                    }

                    if (!empty($Item->GradesData)) {
                        $GradeIndex = 'E';
                        $GradeNumericStart =  $row;
                        foreach($Item->GradesData  as $gradeI => $itemGrades){
                            $GradeValueRow = $row;//5
                            $GradeWiseTotal = 0;
                            foreach($itemGrades->GradeValueData as $GradeValueData){
                                $sheet->setCellValue("$GradeIndex$GradeValueRow", "$GradeValueData");//E5
                               
                                $sheet->setCellValueExplicit(
                                    "$GradeIndex$GradeValueRow",
                                    $GradeValueData,
                                    PHPExcel_Cell_DataType::TYPE_NUMERIC
                                );
                                $GradeWiseTotal += $GradeValueData;
                                $GradeValueRow++;//6
                               
                            } 
                            $sheet->setCellValue("$GradeIndex$GradeValueRow", "$GradeWiseTotal");//E5

                            $sheet->setCellValueExplicit(
                                    "$GradeIndex$GradeValueRow",
                                    $GradeWiseTotal,
                                    PHPExcel_Cell_DataType::TYPE_NUMERIC
                                );
                                                                   
                            $GradeIndex++;//F                            
                        }
                        if (!empty($Item->GrandTotals)) {
                            $VarietyTotalRow = $row;
                            $varietyWiseTotal = 0;
                            foreach($Item->GrandTotals  as $GrandTotals){
                                $sheet->setCellValue("$GradeIndex$VarietyTotalRow", "$GrandTotals");//E5
                                $varietyWiseTotal += $GrandTotals;
                                $VarietyTotalRow++;
                            }
                            $sheet->setCellValue("$GradeIndex$VarietyTotalRow", "$varietyWiseTotal");//E5
                        }
                        $GradeRowEnd = $VarietyCNT + 1;
                        //echo "E$GradeNumericStart:$GradeIndex$GradeValueRow";die;
                        $sheet->getStyle("E$GradeNumericStart:$GradeIndex$GradeValueRow")
                                ->getNumberFormat()
                                //->setFormatCode('#,##0.00');
                                ->setFormatCode('[=0]"0";#,##0.00');

                    }
                    $sheet->setCellValue("A$VarietyRow", "Total");//Grade wise Total
                   

                    $row += $VarietyCNT + 2;///second Loop

                    $mergeRange = 'A' . $startRow . ':' . $GradeIndex . ($row - 2);
                    $sheet->getStyle("$mergeRange")->getFont()->setBold(true)->setSize(12);
                    $sheet->getStyle("$mergeRange")
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                            //->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                            ->setWrapText(true);
                    $sheet->getStyle("$mergeRange")->applyFromArray($borderArr);


                    //$row++;

                    
                    //$row++;
                    //echo '<pre>';print_r($row);die;
                }      
               
                $sheet->freezePane('B1');// freeze
                
                
            }
            ob_clean();
            // Write to Output
            $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
            $filePath = $_SERVER['DOCUMENT_ROOT'] . "/daily-production-report-final-and-dummy-" . $timestamp . ".xlsx";
            $writer->save($filePath);

            $data['filename'] =  "/download-file.php?file=" . "daily-production-report-final-and-dummy-" . $timestamp . ".xlsx";
            $data['status'] = 'Done';
            echo json_encode($data);die;
        }else {
            $data['msg'] = 'No Data Available';
            $data['status'] = 'Done';
             echo json_encode($data);die;
        }
    }else {
        $data['msg'] =  "ERROR : . $error . ";
        $data['status'] = 'Done';
        echo json_encode($data);die;
    }
}