<?php 
//echo ini_get('memory_limit');die;
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STOCK_VALUE_REPORT;

$apiSummary = API_STOCK_SUMMARY_REPORT;

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
            $sheet->getColumnDimension("A")->setWidth(8);
            $borderArr = [
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            ];
            $row = 1;
            if(!empty($_POST['productionDate'])){                
                $productionDate = date('d-M-Y', strtotime($_POST['productionDate']));
                $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "".$productionDate);
                $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12)
                    ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                $sheet->getStyle("A$row")->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                //$sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "DT : ".$productionDate);
                $row++;
            }
            $row++;//3
            if (!empty($List)){                
                $ch = 0;
                $GrandTotalMCs = $GrandTotalKgs = $GrandTotalDollarVal = $GrandTotalInr = 0;
                foreach ($List as $Item) {
                    // caclualte USDTotal 
                    if (!empty($Item->GradesData)) {//$MergeCol
                        foreach ($Item->GradesData as $gradeI => $itemGrades) { //21
                            foreach ($itemGrades->GradeValueData as $GradeItemKey => $itemGradeValueDataArr) {                       
                                foreach($itemGradeValueDataArr as $GradeValueKey => $gradeValuesItem){ 
                                    if($GradeValueKey == 'ValUSD'){                                         
                                        $Item->GrandTotals[$GradeItemKey]->INUSD += $gradeValuesItem;
                                    }
                                    if($GradeValueKey == 'RATE_PER_KGS'){                                         
                                        $Item->GrandTotals[$GradeItemKey]->RATE_PER_KGS += $gradeValuesItem;
                                    }
                                    if($GradeValueKey == 'ConvertToKgs'){                                         
                                        $Item->GrandTotals[$GradeItemKey]->ConvertToKgs += $gradeValuesItem;
                                    }
                                }
                            }
                        }
                    }
                    $headerArr = [];
                    if (!empty($Item->VarietyCombinationData)) {
                        foreach ($Item->VarietyCombinationData as $ItemBreakUp) {
                            $Uom = $ItemBreakUp->Uom;
                            $headerArr[] = [
                                'Species' => $ItemBreakUp->Species.' - '.$ItemBreakUp->ShortName,
                                'PackingStyle' => $ItemBreakUp->PackingStyle,
                                'Glaze' => $ItemBreakUp->Glaze,
                                //'SockingType' => $ItemBreakUp->SockingType,
                                'SubHeader' => [
                                    'M/Cs','IN'.$Uom,$Uom,'VAL US $'
                                ]
                            ];
                        }
                    }else{
                        $headerArr[] = 'No Variety Combination Data Available'; 
                    }
                    $cntHeader = ceil(count($headerArr)/$Slice);
                    $HeaderK = 1;
                    $HeaderRes = [];
                    for($HeaderI = 0; $HeaderI < $cntHeader; $HeaderI++){
                        $HeaderOffset = $Slice * $HeaderI;  // start index
                        $HeaderRes[$HeaderI] = array_slice($headerArr, $HeaderOffset, $Slice);                    
                    }
                    
                    $gradeDataArr = [];
                    if(!empty($Item->GradesData)){
                        foreach ($Item->GradesData as $g=> $itemGrades) {
                            $gradeDataArr[$g]['Grade'] = $itemGrades->Grade;
                            $gradeValCnt = ceil(count($itemGrades->GradeValueData)/$Slice);
                            $gradeValK=1;
                            //$gradeDataArr = [];
                            for($gradeValI=0;$gradeValI<$gradeValCnt;$gradeValI++){
                                $gradeOffset = $Slice * $gradeValI;  // start index
                                $gradeDataArr[$g]['GradeValueData'][$gradeValI] = array_slice($itemGrades->GradeValueData,  $gradeOffset, $Slice);     
                            }
                        }
                    }
                    $gradeDataArrCnt = count($gradeDataArr);
                    $originalRow = $row;$HeaderRowArr = [];
                    
                    foreach($HeaderRes as $HeaderRowKey => $headerArr){
                        $colIndex = 'B';
                        foreach($headerArr as $HeaderItemArrKey => $HederItemArr){
                            $headerRow = $row;//83
                            
                            //$headerRow = $row + ($gradeDataArrCnt * $HeaderRowKey);//2+3*1
                            if(is_array($HederItemArr)){
                                $H = 0;
                                foreach($HederItemArr as $HederItem){   
                                    
                                    if(!is_array($HederItem)){
                                        $startCol = $colIndex;

                                        // Calculate end column (4 columns block)
                                        $startIndex = PHPExcel_Cell::columnIndexFromString($startCol) - 1;//need to try vaiable of variable
                                        $endCol = PHPExcel_Cell::stringFromColumnIndex($startIndex + 3);
                                       
                                       // Merge
                                       $mergeRange = $startCol . $headerRow . ':' . $endCol . $headerRow;
                                        // Remove existing merge if needed
                                        foreach ($sheet->getMergeCells() as $mergedCells) {
                                            if ($mergedCells == $mergeRange) {
                                                $sheet->unmergeCells($mergedCells);
                                            }
                                        }
                                        $sheet->mergeCells($mergeRange);
                                        // Set header
                                        $sheet->setCellValue($startCol.$headerRow, $HederItem);

                                        // Alignment + wrap
                                        $sheet->getStyle("$mergeRange")->getAlignment()
                                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                           // ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                            ->setWrapText(true);

                                        // Auto row height (IMPORTANT)
                                        $sheet->getRowDimension($headerRow)->setRowHeight(-1);
                                        if($ListCnt < 80){
                                            $sheet->getStyle("$mergeRange")->applyFromArray($borderArr);
                                        }
                                       
                                    }else{
                                        //SubHeader section
                                        // echo '<pre>';print_r($colIndex);die; 
                                        if(!empty($headerArr[$HeaderItemArrKey]['Species'])){
                                            $HeadeSubIndex = $colIndex;
                                            foreach($HederItem as $HeadrSubVal){
                                                $sheet->setCellValue("$HeadeSubIndex$headerRow",$HeadrSubVal);
                                                $sheet->getColumnDimension("$HeadeSubIndex")->setWidth(10);                                        
                                        
                                                // Alignment + wrap
                                                $sheet->getStyle("$HeadeSubIndex$headerRow")->getAlignment()
                                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                                    //->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                                    ->setWrapText(true);

                                                // Auto row height (IMPORTANT)
                                                $sheet->getRowDimension($headerRow)->setRowHeight(-1);
                                                if($ListCnt < 80){
                                                    $sheet->getStyle("$HeadeSubIndex$headerRow")->applyFromArray($borderArr);
                                                }
                                                $HeadeSubIndex++;
                                            }
                                        }
                                    }        
                                    $headerRow++;//83+4=87//
                                }                            
                             
                                if(!in_array($headerRow, $HeaderRowArr)){
                                    $HeaderRowArr[] = $headerRow;
                                }
                            }else{
                                $sheet->setCellValue("$colIndex$headerRow",$HederItemArr);
                                $sheet->getColumnDimension("$colIndex")->setWidth(12);

                                // Alignment + wrap
                                $sheet->getStyle("$colIndex$headerRow")->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                    //->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                    ->setWrapText(true);

                                // Auto row height (IMPORTANT)
                                $sheet->getRowDimension($headerRow)->setRowHeight(-1);
                                if($ListCnt < 80){
                                    $sheet->getStyle("$colIndex$headerRow")->applyFromArray($borderArr);
                                }

                                //if($HeaderItemArrKey == 0){//if its 1st row
                                    $headerRow++;
                                //} 
                                $HeaderRowArr[] = $headerRow;                       
                            }
                            $nextIndex = $startIndex + 4;

                            $colIndex = PHPExcel_Cell::stringFromColumnIndex($nextIndex);

                        } 
                        
                        $MergeCol = $headerRow - 1;//86
                        // 1️⃣ Merge cells (VERTICAL merge)
                        $sheet->mergeCells("A$row:A$MergeCol");
                        // 2️⃣ Set value
                        $sheet->setCellValue("A$row", "Grade");
                        // 3️⃣ Apply style
                        //$sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                        $sheet->getStyle("A$row")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("A$row")->getAlignment()
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        if($ListCnt < 80){
                            $sheet->getStyle("A$row:A$MergeCol")->applyFromArray($borderArr);
                        }
                        //$row = $headerRow;
                        if($HeaderRowKey == 0){
                            $originalRow = $MergeCol + 1;
                        }
                        $row = $headerRow + $gradeDataArrCnt + 2;//87+2+2
                    }
                    if (!empty($Item->GradesData)) {//$MergeCol
                        $originalRowOld = $originalRow;//165       
                        foreach ($gradeDataArr as $gradeI => $itemGrades) { //$gradeI=0,1 
                            foreach ($itemGrades['GradeValueData'] as $GradeValueDataI => $itemGradeValueDataArr) {
                                $GradeRow = $HeaderRowArr[$GradeValueDataI] + $gradeI;
                                
                                $sheet->setCellValue("A$GradeRow", $itemGrades['Grade']);//21/25//
                                $sheet->getStyle("A$GradeRow")
                                        ->getAlignment()
                                        //->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                        //->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                        ->setWrapText(true);
                               
                                
                              //  echo '<pre>';print_r($itemGradeValueDataArr);die;
                                $colIndex = $ItemSubIndexStyle = 'B';
                                /* foreach($itemGradeValueDataArr as $GradeItemKey => $gradeValuesItemArr){ 
                                   // if(is_array($gradeValuesItemArr)){
                                        $ItemSubIndex = $colIndex;
                                        foreach($gradeValuesItemArr as  $GradeValueKey => $gradeValuesItem){   
                                            

                                            //$sheet->setCellValue("$ItemSubIndex$GradeRow", $gradeValuesItem);
                                            $sheet->setCellValueExplicit(
                                                        "$ItemSubIndex$GradeRow",
                                                       ( $GradeValueKey != 'MCS') ? round($gradeValuesItem, 3): $gradeValuesItem,
                                                        PHPExcel_Cell_DataType::TYPE_NUMERIC
                                                    );
                                            $ItemSubIndexStyle = $ItemSubIndex;
                                            $ItemSubIndex++;    
                                        }
                                    //}

                                    $sheet->getStyle("$colIndex$originalRowOld:$ItemSubIndexStyle$GradeRow")->applyFromArray([
                                                'font' => [
                                                    'bold' => false,
                                                    'size' => 12
                                                ],
                                                'borders' => !empty($borderArr['borders']) ? $borderArr['borders'] : []
                                            ]);
                                    $sheet->getStyle("$colIndex$originalRowOld:$ItemSubIndexStyle$GradeRow")
                                            ->getAlignment()
                                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                            ->setWrapText(true);
                                    
                                    $colIndex = $ItemSubIndex;
                                } */

                                foreach ($itemGradeValueDataArr as $GradeItemKey => $gradeValuesItemArr) {

                                    $ItemSubIndex = $colIndex;

                                    foreach ($gradeValuesItemArr as $GradeValueKey => $gradeValuesItem) {

                                        $cell = $ItemSubIndex . $GradeRow;

                                        // Set value
                                        if ($GradeValueKey != 'MCS') {
                                            $sheet->setCellValue($cell, round($gradeValuesItem, 3));
                                        } else {
                                            $sheet->setCellValue($cell, $gradeValuesItem);
                                        }

                                       

                                       
                                        $ItemSubIndexStyle = $ItemSubIndex;
                                        $ItemSubIndex++;
                                    }
                                    if($ListCnt < 80){
                                        // Prevent page crash when record count exceeds 80
                                        $sheet->getStyle("$colIndex$originalRowOld:$ItemSubIndexStyle$GradeRow")->applyFromArray([
                                                    'font' => [
                                                        'bold' => false,
                                                        'size' => 12
                                                    ],
                                                    'borders' => !empty($borderArr['borders']) ? $borderArr['borders'] : []
                                                ]);
                                    }
                                    $colIndex = $ItemSubIndex;
                                }
                            }

                        }

                        
                        $ranges = [
                            "C$originalRowOld:E$GradeRow",
                            "G$originalRowOld:I$GradeRow"
                        ];

                        foreach ($ranges as $range) {
                            $sheet->getStyle($range)
                                ->getNumberFormat()
                                //->setFormatCode('#,##0.00');
                                ->setFormatCode('[=0]"0";#,##0.00');
                        }
                        if($ListCnt < 80){
                            $sheet->getStyle("A$originalRowOld:A$GradeRow")->applyFromArray([
                                /* 'font' => [
                                    'bold' => true,
                                    'size' => 12
                                ],  */                           
                                'borders' => !empty($borderArr['borders']) ? $borderArr['borders'] : []
                            ]);

                            
                            $sheet->getStyle("B$originalRowOld:I$GradeRow")
                                            ->getAlignment()
                                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                           // ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                            ->setWrapText(true);
                        }


                    }
                    
                    $TotalMCs = $TotalKgs = $TotalDollarVal = $TotalPrice = 0;
                    $GrandTotalsArr = [];
                   // echo '<pre>';print_r($Item->GrandTotals);die;
                    if (!empty($Item->GrandTotals)) {
                        $GrandTotalCnt = ceil(count($Item->GrandTotals)/$Slice);
                        for($GrandTotalI = 0; $GrandTotalI < $GrandTotalCnt; $GrandTotalI++){
                            $GrandTotalOffset = $Slice * $GrandTotalI;  // start index
                            $GrandTotalsArr[$GrandTotalI] = array_slice($Item->GrandTotals,  $GrandTotalOffset, $Slice);     
                        }
                        //echo '<pre>';print_r($GrandTotalsArr);die;
                        foreach($GrandTotalsArr as $GrandTotalsK => $GrandTotalsItem){
                           
                            $GrandTotalRow = $HeaderRowArr[$GrandTotalsK] + $gradeDataArrCnt;
                            $sheet->setCellValue("A$GrandTotalRow", "Totals");
                            
                            $colIndex = $grandTotalEndIndex = 'B';
                            foreach($GrandTotalsItem as $k => $ItemGrandTotalsItemArr){
                                $grandTotalIndex = $colIndex;
                                foreach( $ItemGrandTotalsItemArr as $key => $GrandTotals){                                     
                                    //$sheet->setCellValue("$grandTotalIndex$GrandTotalRow", number_format($GrandTotals, 3));
                                    if($key != 'ConvertToKgs'){
                                        $sheet->setCellValueExplicit(
                                                            "$grandTotalIndex$GrandTotalRow",
                                                            ( $key != 'MCS') ? round($GrandTotals, 3): $GrandTotals,
                                                            PHPExcel_Cell_DataType::TYPE_NUMERIC
                                                        );
                                    }
                                    
                                    if($key == 'MCS'){ 
                                        $TotalMCs += $GrandTotals;
                                    }elseif($key == 'ConvertToKgs'){
                                        $TotalKgs += $GrandTotals;
                                    }elseif($key == 'INUSD'){
                                        $TotalDollarVal += $GrandTotals; 
                                    }
                                    if($key != 'ConvertToKgs'){        //extra row adding                                
                                        $grandTotalEndIndex = $grandTotalIndex;
                                        $grandTotalIndex++;    
                                    }
                                }
                               
                                $colIndex = $grandTotalIndex;
                            }

                            $ranges = [
                                "C$GrandTotalRow:E$GrandTotalRow",
                                "G$GrandTotalRow:I$GrandTotalRow"
                            ];

                            foreach ($ranges as $range) {
                                $sheet->getStyle($range)
                                    ->getNumberFormat()
                                    ->setFormatCode('[=0]"0";#,##0.00');
                            }
                            if($ListCnt < 80){
                                $sheet->getStyle("A$GrandTotalRow:A$GrandTotalRow")->applyFromArray([
                                    /* 'font' => [
                                        'bold' => true,
                                        'size' => 12
                                    ], */
                                    'alignment' => [
                                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                       // 'vertical'   => PHPExcel_Style_Alignment::VERTICAL_TOP,
                                        'wrapText'   => true,
                                    ],
                                    'borders' => !empty($borderArr['borders']) ? $borderArr['borders'] : []
                                ]);

                                $sheet->getStyle("B$GrandTotalRow:$grandTotalEndIndex$GrandTotalRow")->applyFromArray([
                                    'font' => [
                                        'bold' => false,
                                        'size' => 12
                                    ],
                                    'borders' => !empty($borderArr['borders']) ? $borderArr['borders'] : []
                                ]);
                                
                                $sheet->getStyle("B$GrandTotalRow:$grandTotalEndIndex$GrandTotalRow")
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                        // ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                        ->setWrapText(true);
                            }
                        }
                    }
                    
                    $ProductionArr = [];
                    if (!empty($Item->FreezingData)) {
                        $ProductionCnt = ceil(count($Item->FreezingData)/$Slice);
                        for($ProductionI = 0; $ProductionI < $ProductionCnt; $ProductionI++){
                            $ProductionOffset = $Slice * $ProductionI;  // start index
                            $ProductionArr[$ProductionI] = array_slice($Item->FreezingData,  $ProductionOffset, $Slice);     
                        }
                        foreach($ProductionArr as $ProductionK => $ProductionItem){
                            
                            $ProductionRow = $HeaderRowArr[$ProductionK] + $gradeDataArrCnt + 1;
                            $sheet->setCellValue("A$ProductionRow", "Production");                            

                            $colIndex = $productioneEndIndex = 'B';
                            foreach($ProductionItem as $ItemFreezingItemData){
                                $productionIdex = $colIndex;
                                foreach($ItemFreezingItemData as  $productionData){
                                    $sheet->setCellValue("$productionIdex$ProductionRow", number_format($productionData, 3));
                                   
                                    $productioneEndIndex = $productionIdex;
                                    $productionIdex++;    
                                }
                                
                                $colIndex = $productionIdex;
                            }

                            $ranges = [
                                "C$ProductionRow:E$ProductionRow",
                                "G$ProductionRow:I$ProductionRow"
                            ];

                            foreach ($ranges as $range) {
                                $sheet->getStyle($range)
                                    ->getNumberFormat()
                                    ->setFormatCode('[=0]"0";#,##0.00');
                            }
                            if($ListCnt < 80){
                                $sheet->getStyle("A$ProductionRow:A$ProductionRow")->applyFromArray([
                                    /* 'font' => [
                                        'bold' => true,
                                        'size' => 12
                                    ], */
                                    'borders' => !empty($borderArr['borders']) ? $borderArr['borders'] : []
                                ]);
                                
                               
                            
                                $sheet->getStyle("B$ProductionRow:$productioneEndIndex$ProductionRow")->applyFromArray([
                                    'borders' => !empty($borderArr['borders']) ? $borderArr['borders'] : []
                                ]);
                                
                            }
                            $sheet->getStyle("A$ProductionRow:A$ProductionRow")
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                    //  ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                    ->setWrapText(true);

                            $sheet->getStyle("B$ProductionRow:$productioneEndIndex$ProductionRow")
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                        //  ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                        ->setWrapText(true);
                           
                        }
                    }
                    //$row++;
                    if(!empty($TotalMCs)){                    
                       /*  $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "TOTAL MASTER CARTONS : ".number_format($TotalMCs));
                        // 3️⃣ Apply style
                        $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                        $sheet->getStyle("A$row")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        $row++;
                        
                        $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "TOTAL KGS :  ".number_format($TotalKgs, 3));
                        // 3️⃣ Apply style
                        $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                        $sheet->getStyle("A$row")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        $row++;
                        
                        $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "TOTAL DOLLAR VALUE: ".number_format($TotalDollarVal, 3));
                        // 3️⃣ Apply style
                        $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                        $sheet->getStyle("A$row")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        $row++;*/
                        $TotalInr = $ExchangeRate * $TotalDollarVal;
                        /*
                        $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "TOTAL VALUE IN RUPEE : ".number_format($TotalInr, 3));
                        // 3️⃣ Apply style
                        $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                        $sheet->getStyle("A$row")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); */

                        //$GrandTotalMCs = $GrandTotalKgs = $GrandTotalDollarVal = $GrandTotalPrice = 0;
                        $GrandTotalMCs += $TotalMCs; 
                        $GrandTotalKgs += $TotalKgs; 
                        $GrandTotalDollarVal += $TotalDollarVal; 
                        $GrandTotalInr += $TotalInr; 

                        $row++;
                    }
                    $row++;
                    //echo '<pre>';print_r($row);die;
                }      
                $totalRow = $row;
                $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "TOTAL MASTER CARTONS : ".(int)$GrandTotalMCs);
                // 3️⃣ Apply style
                // $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                // $sheet->getStyle("A$row")->getAlignment()
                //     ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $row++;
                
                $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "TOTAL KGS :  ".number_format($GrandTotalKgs, 3));
                // 3️⃣ Apply style
                // $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                // $sheet->getStyle("A$row")->getAlignment()
                //     ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $row++;
                
                $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "TOTAL DOLLAR VALUE: ".number_format($GrandTotalDollarVal, 3));
                // 3️⃣ Apply style
                // $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                // $sheet->getStyle("A$row")->getAlignment()
                //     ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $row++;
                $TotalInr = $ExchangeRate * $TotalDollarVal;
                $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "TOTAL VALUE IN RUPEE : ".number_format($GrandTotalInr, 3));
                // 3️⃣ Apply style
                //$sheet->getStyle("A$totalRow:$mergeRow$row")->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle("A$totalRow:$mergeRow$row")->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                
                
            }
            ob_clean();
            // Write to Output
            $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
            $filePath = $_SERVER['DOCUMENT_ROOT'] . "/stock-value-report-plant-wise-" . $timestamp . ".xlsx";
            $writer->save($filePath);

            $data['filename'] =  "/download-file.php?file=" . "stock-value-report-plant-wise-" . $timestamp . ".xlsx";
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