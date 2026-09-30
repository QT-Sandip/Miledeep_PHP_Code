<?php 
set_time_limit(300000); // 300 seconds = 5 minutes
ini_set('memory_limit', '512M');
//echo ini_get('memory_limit');die;
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_VARIETYWISE_STOCK_REPORT_NEW;

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
    "Species" => $_POST['typeSpecies']
);
$resList = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($resList);die;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $timestamp = date("YmdHis");
        $List = $resList->Data;
         $ReturnCorgosData = $resList->ReturnCorgosData;
        $AntiBioticDataList = $resList->AntiBioticData;
        $ShipmentDataList = $resList->ShipmentData;
        $IBTInData = $resList->IBTInData;
        $ConversionsData = $resList->ConversionsData;
        $IBTOutData = $resList->IBTOutData;
        $GrandTotals = $resList->GrandTotals;
        $Slice = 6;//after mention no. of column data printing one by one
        $mergeRow = "G";
        $datasets = [
            $List,
            $ReturnCorgosData,
            $AntiBioticDataList,
            $GrandTotals,
            $ShipmentDataList,
            $IBTInData,
            $IBTOutData,
            $ConversionsData
        ];

        $hasData = false;
        foreach ($datasets as $dataItem) {
            if (!empty($dataItem)) {
                $hasData = true;
                break;
            }
        }
        if ($hasData) {
            require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed
            $spreadsheet = new PHPExcel();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->getColumnDimension("A")->setWidth(12);
            $borderArr = [
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            ];
            $row = 1;
            if(!empty($_SESSION['UserName'])){
                $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row",$_SESSION['UserName']);
                $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(18)
                    ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                $sheet->getStyle("A$row")->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $row++;
            }//row = 2
            if(!empty($_POST['productionDate'])){                
                $productionDate = date('d.m.Y', strtotime($_POST['productionDate']));
                $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "DT : ".$productionDate);
                $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12)
                    ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                $sheet->getStyle("A$row")->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                //$sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "DT : ".$productionDate);
                $row++;
            }//row = 3

            if (!empty($List)){
                $ch = 0;
                foreach ($List as $Item) {
                    $headerArr = [];
                    if (!empty($Item->VarietyCombinationData)) {
                        foreach ($Item->VarietyCombinationData as $ItemBreakUp) {
                            $headerArr[] = [
                                'Species' => $ItemBreakUp->Species.' - '.$ItemBreakUp->ShortName,
                                'PackingStyle' => $ItemBreakUp->PackingStyle,
                                'Glaze' => $ItemBreakUp->Glaze,
                                'SockingType' => $ItemBreakUp->SockingType
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
                                    $sheet->setCellValue("$colIndex$headerRow",$HederItem)->getStyle("$colIndex$headerRow")->getFont()->setBold(true)->setSize(12);
                                    $sheet->getColumnDimension("$colIndex")->setWidth(12);                                        
                                    
                                    // Alignment + wrap
                                    $sheet->getStyle("$colIndex$headerRow")->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                        ->setWrapText(true);

                                    // Auto row height (IMPORTANT)
                                    $sheet->getRowDimension($headerRow)->setRowHeight(-1);
                                    $sheet->getStyle("$colIndex$headerRow")->applyFromArray($borderArr);

                                    $headerRow++;//83+4=87//
                                }
                                if(!in_array($headerRow, $HeaderRowArr)){
                                    $HeaderRowArr[] = $headerRow;
                                }
                            }else{
                                $sheet->setCellValue("$colIndex$headerRow",$HederItemArr)->getStyle("$colIndex$headerRow")->getFont()->setBold(true)->setSize(12);
                                $sheet->getColumnDimension("$colIndex")->setWidth(12);

                                // Alignment + wrap
                                $sheet->getStyle("$colIndex$headerRow")->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                    ->setWrapText(true);

                                // Auto row height (IMPORTANT)
                                $sheet->getRowDimension($headerRow)->setRowHeight(-1);
                                $sheet->getStyle("$colIndex$headerRow")->applyFromArray($borderArr);

                                //if($HeaderItemArrKey == 0){//if its 1st row
                                    $headerRow++;
                                //} 
                                $HeaderRowArr[] = $headerRow;                       
                            }
                            $colIndex++;
                        } 
                        $MergeCol = $headerRow - 1;//86
                        // 1️⃣ Merge cells (VERTICAL merge)
                        $sheet->mergeCells("A$row:A$MergeCol");
                        // 2️⃣ Set value
                        $sheet->setCellValue("A$row", "Grade");
                        // 3️⃣ Apply style
                        $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                        $sheet->getStyle("A$row")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("A$row")->getAlignment()
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        $sheet->getStyle("A$row:A$MergeCol")->applyFromArray($borderArr);
                        //$row = $headerRow;
                        if($HeaderRowKey == 0){
                            $originalRow = $MergeCol + 1;
                        }
                        $row = $headerRow + $gradeDataArrCnt + 2;//87+2+2
                    }
                    
                    if (!empty($Item->GradesData)) {//$MergeCol
                        $originalRowOld = $originalRow;//165
                        foreach ($gradeDataArr as $gradeI => $itemGrades) { //21 
                            foreach ($itemGrades['GradeValueData'] as $GradeValueDataI => $itemGradeValueDataArr) {
                                //if($GradeValueDataI == 0){
                                //    $GradeRow = $originalRowOld + $gradeI;//165
                                //}else{//190
                                    //$GradeRow = $headerRow;//189
                                    $GradeRow = $HeaderRowArr[$GradeValueDataI] + $gradeI;;
                                //}
                                $sheet->setCellValue("A$GradeRow", $itemGrades['Grade']);//21/25//
                                // 3️⃣ Apply style
                                $sheet->getStyle("A$GradeRow")->getFont()->setBold(true)->setSize(12);

                                // Alignment + wrap
                                $sheet->getStyle("A$GradeRow")->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                    ->setWrapText(true);

                                // Auto row height (IMPORTANT)
                                $sheet->getRowDimension($GradeRow)->setRowHeight(-1);
                                $sheet->getStyle("A$GradeRow")->applyFromArray($borderArr);

                                $colIndex = 'B';
                                foreach($itemGradeValueDataArr as $gradeValues){
                                    $sheet->setCellValue("$colIndex$GradeRow", $gradeValues->Value);
                                    $sheet->getStyle("$colIndex$GradeRow")->getFont()->setSize(12);
                                    $sheet->getStyle("$colIndex$GradeRow")->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                    $sheet->getStyle("$colIndex$GradeRow")->applyFromArray($borderArr);
                                    $colIndex++;
                                }
                                //continue;
                            /*  if(count($itemGrades['GradeValueData']) == 1 || $GradeValueDataI == 0){
                                    $originalRow++;//                                
                                }else{ 
                                    //if(count($itemGrades['GradeValueData']) > 2){echo '<pre>';print_r($HeaderRowArr[$GradeValueDataI]);die;}
                                // $originalRow = $gradeDataArrCnt * $gradeI;//10*1+22
                                    $headerRow++;//190                              
                                }   */                          
                                //
                            }
                        }                    
                    }
                    $gradeDataArr = [];
                    if(!empty($Item->GradesData)){
                        //echo $headerRow.' '.$originalRow;die;
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
                    $GrandTotalsArr = [];
                    if (!empty($Item->GrandTotals)) {
                        $GrandTotalCnt = ceil(count($Item->GrandTotals)/$Slice);
                        for($GrandTotalI = 0; $GrandTotalI < $GrandTotalCnt; $GrandTotalI++){
                            $GrandTotalOffset = $Slice * $GrandTotalI;  // start index
                            $GrandTotalsArr[$GrandTotalI] = array_slice($Item->GrandTotals,  $GrandTotalOffset, $Slice);     
                        }
                        foreach($GrandTotalsArr as $GrandTotalsK => $GrandTotalsItem){
                            /* if($GrandTotalsK == 0){
                                $GrandTotalRow = $originalRow;//28
                            }else{//190
                                $GrandTotalRow = $headerRow;//189
                            } */
                            $GrandTotalRow = $HeaderRowArr[$GrandTotalsK] + $gradeDataArrCnt;
                            $sheet->setCellValue("A$GrandTotalRow", "Totals");
                            // 3️⃣ Apply style
                            $sheet->getStyle("A$GrandTotalRow")->getFont()->setBold(true)->setSize(12);
                            $sheet->getStyle("A$GrandTotalRow")->getAlignment()
                                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            $sheet->getStyle("A$GrandTotalRow")->applyFromArray($borderArr);
                            $colIndex = 'B';
                            foreach($GrandTotalsItem as $ItemGrandTotals){
                                $sheet->setCellValue("$colIndex$GrandTotalRow", $ItemGrandTotals->TotalValue);
                                $sheet->getStyle("$colIndex$GrandTotalRow")->getFont()->setSize(12);
                                $sheet->getStyle("$colIndex$GrandTotalRow")->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                $sheet->getStyle("$colIndex$GrandTotalRow")->applyFromArray($borderArr);
                                $colIndex++;
                            }
                            /* if(count($GrandTotalsArr) == 1 || $GrandTotalsK == 0){
                                $originalRow++;//                                
                            }else{ 
                                $headerRow++;//190                              
                            }  */
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
                            /* if($ProductionK == 0){
                                $ProductionRow = $originalRow;//28
                            }else{//190
                                $ProductionRow = $headerRow;//189
                            } */
                            $ProductionRow = $HeaderRowArr[$ProductionK] + $gradeDataArrCnt + 1;
                            $sheet->setCellValue("A$ProductionRow", "Production");
                            // 3️⃣ Apply style
                            $sheet->getStyle("A$ProductionRow")->getFont()->setBold(true)->setSize(12);

                             // Alignment + wrap
                                $sheet->getStyle("A{$ProductionRow}")->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                    ->setWrapText(true);

                                // Auto row height (IMPORTANT)
                                $sheet->getRowDimension($ProductionRow)->setRowHeight(-1);
                                $sheet->getStyle("A$ProductionRow")->applyFromArray($borderArr);

                            $colIndex = 'B';
                            foreach($ProductionItem as $ItemFreezingData){
                                $sheet->setCellValue("$colIndex$ProductionRow", $ItemFreezingData->FreezingWeight);
                                $sheet->getStyle("$colIndex$ProductionRow")->getFont()->setSize(12);
                                $sheet->getStyle("$colIndex$ProductionRow")->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                $sheet->getStyle("$colIndex$ProductionRow")->applyFromArray($borderArr);
                                $colIndex++;
                            }
                            /* if(count($ProductionArr) == 1 || $ProductionK == 0){
                                $originalRow++;//                                
                            }else{ 
                                $headerRow++;//190                              
                            }  */
                        }
                    }
                    $row++;//$ch++;
                    //echo '<pre>';print_r($headerArr);die;
                }
            }
            if (!empty($ReturnCorgosData)){                
                $row++;
                foreach ($ReturnCorgosData as $Item) {
                    $headerArr = [];
                    if (!empty($Item->VarietyCombinationData)) {
                        foreach ($Item->VarietyCombinationData as $ItemBreakUp) {
                            $headerArr[] = [
                                'Species' => $ItemBreakUp->Species.' - '.$ItemBreakUp->ShortName,
                                'PackingStyle' => $ItemBreakUp->PackingStyle,
                                //'Glaze' => $ItemBreakUp->Glaze,
                                'SockingType' => $ItemBreakUp->SockingType,
                                'ProductionType' => 'RETURN CARGO'
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
                                    $sheet->setCellValue("$colIndex$headerRow",$HederItem)->getStyle("$colIndex$headerRow")->getFont()->setBold(true)->setSize(12);
                                    $sheet->getColumnDimension("$colIndex")->setWidth(12); 
                                        
                                    // Alignment + wrap
                                    $sheet->getStyle("$colIndex$headerRow")->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                        ->setWrapText(true);

                                    // Auto row height (IMPORTANT)
                                    $sheet->getRowDimension($headerRow)->setRowHeight(-1);
                                    $sheet->getStyle("$colIndex$headerRow")->applyFromArray($borderArr);
                                    $headerRow++;//83+4=87//
                                }
                                if(!in_array($headerRow, $HeaderRowArr)){
                                    $HeaderRowArr[] = $headerRow;
                                }
                            }else{
                                $sheet->setCellValue("$colIndex$headerRow",$HederItemArr)->getStyle("$colIndex$headerRow")->getFont()->setBold(true)->setSize(12);
                                $sheet->getColumnDimension("$colIndex")->setWidth(12);

                                // Alignment + wrap
                                    $sheet->getStyle("$colIndex$headerRow")->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                        ->setWrapText(true);

                                    // Auto row height (IMPORTANT)
                                    $sheet->getRowDimension($headerRow)->setRowHeight(-1);
                                    $sheet->getStyle("$colIndex$headerRow")->applyFromArray($borderArr);
                                //if($HeaderItemArrKey == 0){//if its 1st row
                                    $headerRow++;
                                //} 
                                $HeaderRowArr[] = $headerRow;                       
                            }
                            $colIndex++;
                        } 
                        $MergeCol = $headerRow - 1;//86
                        // 1️⃣ Merge cells (VERTICAL merge)
                        $sheet->mergeCells("A$row:A$MergeCol");
                        // 2️⃣ Set value
                        $sheet->setCellValue("A$row", "Grade");
                        // 3️⃣ Apply style
                        $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                        $sheet->getStyle("A$row")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("A$row")->getAlignment()
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        $sheet->getStyle("A$row:A$MergeCol")->applyFromArray($borderArr);
                        //$row = $headerRow;
                        if($HeaderRowKey == 0){
                            $originalRow = $MergeCol + 1;
                        }
                        $row = $headerRow + $gradeDataArrCnt + 2;//87+2+2
                    }
                    
                    if (!empty($Item->GradesData)) {//$MergeCol
                        $originalRowOld = $originalRow;//165
                        foreach ($gradeDataArr as $gradeI => $itemGrades) { //21 
                            foreach ($itemGrades['GradeValueData'] as $GradeValueDataI => $itemGradeValueDataArr) {
                                //if($GradeValueDataI == 0){
                                //    $GradeRow = $originalRowOld + $gradeI;//165
                                //}else{//190
                                    //$GradeRow = $headerRow;//189
                                    $GradeRow = $HeaderRowArr[$GradeValueDataI] + $gradeI;;
                                //}
                                $sheet->setCellValue("A$GradeRow", $itemGrades['Grade']);//21/25//
                                // 3️⃣ Apply style
                                $sheet->getStyle("A$GradeRow")->getFont()->setBold(true)->setSize(12);

                                // Alignment + wrap
                                $sheet->getStyle("A{$GradeRow}")->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                    ->setWrapText(true);

                                // Auto row height (IMPORTANT)
                                $sheet->getRowDimension($GradeRow)->setRowHeight(-1);
                                $sheet->getStyle("A$GradeRow")->applyFromArray($borderArr);


                                $colIndex = 'B';
                                foreach($itemGradeValueDataArr as $gradeValues){
                                    $sheet->setCellValue("$colIndex$GradeRow", $gradeValues->Value);
                                    $sheet->getStyle("$colIndex$GradeRow")->getFont()->setSize(12);
                                    $sheet->getStyle("$colIndex$GradeRow")->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                                    $sheet->getStyle("$colIndex$GradeRow")->applyFromArray($borderArr);
                                    $colIndex++;
                                }
                                //continue;
                            /*  if(count($itemGrades['GradeValueData']) == 1 || $GradeValueDataI == 0){
                                    $originalRow++;//                                
                                }else{ 
                                    //if(count($itemGrades['GradeValueData']) > 2){echo '<pre>';print_r($HeaderRowArr[$GradeValueDataI]);die;}
                                // $originalRow = $gradeDataArrCnt * $gradeI;//10*1+22
                                    $headerRow++;//190                              
                                }   */                          
                                //
                            }
                        }                    
                    }
                    $gradeDataArr = [];
                    if(!empty($Item->GradesData)){
                        //echo $headerRow.' '.$originalRow;die;
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
                    $GrandTotalsArr = [];
                    if (!empty($Item->GrandTotals)) {
                        $GrandTotalCnt = ceil(count($Item->GrandTotals)/$Slice);
                        for($GrandTotalI = 0; $GrandTotalI < $GrandTotalCnt; $GrandTotalI++){
                            $GrandTotalOffset = $Slice * $GrandTotalI;  // start index
                            $GrandTotalsArr[$GrandTotalI] = array_slice($Item->GrandTotals,  $GrandTotalOffset, $Slice);     
                        }
                        foreach($GrandTotalsArr as $GrandTotalsK => $GrandTotalsItem){
                            /* if($GrandTotalsK == 0){
                                $GrandTotalRow = $originalRow;//28
                            }else{//190
                                $GrandTotalRow = $headerRow;//189
                            } */
                            $GrandTotalRow = $HeaderRowArr[$GrandTotalsK] + $gradeDataArrCnt;
                            $sheet->setCellValue("A$GrandTotalRow", "Totals");
                            // 3️⃣ Apply style
                            $sheet->getStyle("A$GrandTotalRow")->getFont()->setBold(true)->setSize(12);
                            $sheet->getStyle("A$GrandTotalRow")->getAlignment()
                                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            
                            $sheet->getStyle("A$GrandTotalRow")->applyFromArray($borderArr);
                            $colIndex = 'B';
                            foreach($GrandTotalsItem as $ItemGrandTotals){
                                $sheet->setCellValue("$colIndex$GrandTotalRow", $ItemGrandTotals->TotalValue);
                                $sheet->getStyle("$colIndex$GrandTotalRow")->getFont()->setSize(12);
                                $sheet->getStyle("$colIndex$GrandTotalRow")->getAlignment()
                                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                $sheet->getStyle("$colIndex$GrandTotalRow")->applyFromArray($borderArr);
                                $colIndex++;
                            }
                            /* if(count($GrandTotalsArr) == 1 || $GrandTotalsK == 0){
                                $originalRow++;//                                
                            }else{ 
                                $headerRow++;//190                              
                            }  */
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
                            /* if($ProductionK == 0){
                                $ProductionRow = $originalRow;//28
                            }else{//190
                                $ProductionRow = $headerRow;//189
                            } */
                            $ProductionRow = $HeaderRowArr[$ProductionK] + $gradeDataArrCnt + 1;
                            $sheet->setCellValue("A$ProductionRow", "Production");                            
                            // 3️⃣ Apply style
                            $sheet->getStyle("A$ProductionRow")->getFont()->setBold(true)->setSize(12);

                             // Alignment + wrap
                                $sheet->getStyle("A{$ProductionRow}")->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                    ->setWrapText(true);

                                // Auto row height (IMPORTANT)
                                $sheet->getRowDimension($ProductionRow)->setRowHeight(-1);
                                $sheet->getStyle("A$ProductionRow")->applyFromArray($borderArr);

                            $colIndex = 'B';
                            foreach($ProductionItem as $ItemFreezingData){
                                $sheet->setCellValue("$colIndex$ProductionRow", $ItemFreezingData->FreezingWeight);
                                $sheet->getStyle("$colIndex$ProductionRow")->getFont()->setSize(12);
                                $sheet->getStyle("$colIndex$ProductionRow")->getAlignment()
                                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                $sheet->getStyle("$colIndex$ProductionRow")->applyFromArray($borderArr);

                                $colIndex++;
                            }
                            /* if(count($ProductionArr) == 1 || $ProductionK == 0){
                                $originalRow++;//                                
                            }else{ 
                                $headerRow++;//190                              
                            }  */
                        }
                    }
                    $row++;
                }
                $row++;
            }
            if (!empty($AntiBioticDataList)){
                $row++;
                $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row","ANTIBIOTIC");
                $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(16)
                    ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                $sheet->getStyle("A$row")->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $row++;
                foreach ($AntiBioticDataList as $Item) {
                    $headerArr = [];
                    if (!empty($Item->VarietyCombinationData)) {
                        foreach ($Item->VarietyCombinationData as $ItemBreakUp) {
                            $headerArr[] = [
                                'Species' => $ItemBreakUp->Species.' - '.$ItemBreakUp->ShortName,
                                'PackingStyle' => $ItemBreakUp->PackingStyle,
                                'Glaze' => $ItemBreakUp->Glaze,
                                'SockingType' => $ItemBreakUp->SockingType
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
                                    $sheet->setCellValue("$colIndex$headerRow",$HederItem)->getStyle("$colIndex$headerRow")->getFont()->setBold(true)->setSize(12);
                                    $sheet->getColumnDimension("$colIndex")->setWidth(12);

                                    // Alignment + wrap
                                    $sheet->getStyle("$colIndex$headerRow")->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                        ->setWrapText(true);

                                    // Auto row height (IMPORTANT)
                                    $sheet->getRowDimension($headerRow)->setRowHeight(-1);
                                    $sheet->getStyle("$colIndex$headerRow")->applyFromArray($borderArr);
                                    
                                    $headerRow++;//83+4=87//
                                }
                                if(!in_array($headerRow, $HeaderRowArr)){
                                    $HeaderRowArr[] = $headerRow;
                                }
                            }else{
                                $sheet->setCellValue("$colIndex$headerRow",$HederItemArr)->getStyle("$colIndex$headerRow")->getFont()->setBold(true)->setSize(12);
                                $sheet->getColumnDimension("$colIndex")->setWidth(12);

                                // Alignment + wrap
                                $sheet->getStyle("$colIndex$headerRow")->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                    ->setWrapText(true);

                                // Auto row height (IMPORTANT)
                                $sheet->getRowDimension($headerRow)->setRowHeight(-1);
                                $sheet->getStyle("$colIndex$headerRow")->applyFromArray($borderArr);
                                //if($HeaderItemArrKey == 0){//if its 1st row
                                    $headerRow++;
                                //} 
                                $HeaderRowArr[] = $headerRow;                       
                            }
                            $colIndex++;
                        } 
                        $MergeCol = $headerRow - 1;//86
                        // 1️⃣ Merge cells (VERTICAL merge)
                        $sheet->mergeCells("A$row:A$MergeCol");
                        // 2️⃣ Set value
                        $sheet->setCellValue("A$row", "Grade");
                        // 3️⃣ Apply style
                        $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);

                        $sheet->getStyle("A$row")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        $sheet->getStyle("A$row:A$MergeCol")->applyFromArray($borderArr);

                        //$row = $headerRow;
                        if($HeaderRowKey == 0){
                            $originalRow = $MergeCol + 1;
                        }
                        $row = $headerRow + $gradeDataArrCnt + 2;//87+2+2
                    }
                    
                    if (!empty($Item->GradesData)) {//$MergeCol
                        $originalRowOld = $originalRow;//165
                        foreach ($gradeDataArr as $gradeI => $itemGrades) { //21 
                            foreach ($itemGrades['GradeValueData'] as $GradeValueDataI => $itemGradeValueDataArr) {
                                //if($GradeValueDataI == 0){
                                //    $GradeRow = $originalRowOld + $gradeI;//165
                                //}else{//190
                                    //$GradeRow = $headerRow;//189
                                    $GradeRow = $HeaderRowArr[$GradeValueDataI] + $gradeI;;
                                //}
                                $sheet->setCellValue("A$GradeRow", $itemGrades['Grade']);//21/25//
                                // 3️⃣ Apply style
                                $sheet->getStyle("A$GradeRow")->getFont()->setBold(true)->setSize(12);

                                    
                            // Alignment + wrap
                                $sheet->getStyle("A$GradeRow")->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                    ->setWrapText(true);

                                // Auto row height (IMPORTANT)
                                $sheet->getRowDimension($GradeRow)->setRowHeight(-1);
                                $sheet->getStyle("A$GradeRow")->applyFromArray($borderArr);

                                $colIndex = 'B';
                                foreach($itemGradeValueDataArr as $gradeValues){
                                    $sheet->setCellValue("$colIndex$GradeRow", $gradeValues->Value);
                                    $sheet->getStyle("$colIndex$GradeRow")->getFont()->setSize(12);
                                    $sheet->getStyle("$colIndex$GradeRow")->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                    $sheet->getStyle("$colIndex$GradeRow")->applyFromArray($borderArr);
                                    $colIndex++;
                                }
                                //continue;
                            /*  if(count($itemGrades['GradeValueData']) == 1 || $GradeValueDataI == 0){
                                    $originalRow++;//                                
                                }else{ 
                                    //if(count($itemGrades['GradeValueData']) > 2){echo '<pre>';print_r($HeaderRowArr[$GradeValueDataI]);die;}
                                // $originalRow = $gradeDataArrCnt * $gradeI;//10*1+22
                                    $headerRow++;//190                              
                                }   */                          
                                //
                            }
                        }                    
                    }
                    $gradeDataArr = [];
                    if(!empty($Item->GradesData)){
                        //echo $headerRow.' '.$originalRow;die;
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
                    $GrandTotalsArr = [];
                    if (!empty($Item->GrandTotals)) {
                        $GrandTotalCnt = ceil(count($Item->GrandTotals)/$Slice);
                        for($GrandTotalI = 0; $GrandTotalI < $GrandTotalCnt; $GrandTotalI++){
                            $GrandTotalOffset = $Slice * $GrandTotalI;  // start index
                            $GrandTotalsArr[$GrandTotalI] = array_slice($Item->GrandTotals,  $GrandTotalOffset, $Slice);     
                        }
                        foreach($GrandTotalsArr as $GrandTotalsK => $GrandTotalsItem){
                            /* if($GrandTotalsK == 0){
                                $GrandTotalRow = $originalRow;//28
                            }else{//190
                                $GrandTotalRow = $headerRow;//189
                            } */
                            $GrandTotalRow = $HeaderRowArr[$GrandTotalsK] + $gradeDataArrCnt;
                            $sheet->setCellValue("A$GrandTotalRow", "Totals");
                            // 3️⃣ Apply style
                            $sheet->getStyle("A$GrandTotalRow")->getFont()->setBold(true)->setSize(12);
                            $sheet->getStyle("A$GrandTotalRow")->getAlignment()
                                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            $sheet->getStyle("A$GrandTotalRow")->applyFromArray($borderArr);
                            $colIndex = 'B';
                            foreach($GrandTotalsItem as $ItemGrandTotals){
                                $sheet->setCellValue("$colIndex$GrandTotalRow", $ItemGrandTotals->TotalValue);
                                $sheet->getStyle("$colIndex$GrandTotalRow")->getFont()->setSize(12);
                                $sheet->getStyle("$colIndex$GrandTotalRow")->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                $sheet->getStyle("$colIndex$GrandTotalRow")->applyFromArray($borderArr);
                                $colIndex++;
                            }
                            /* if(count($GrandTotalsArr) == 1 || $GrandTotalsK == 0){
                                $originalRow++;//                                
                            }else{ 
                                $headerRow++;//190                              
                            }  */
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
                            /* if($ProductionK == 0){
                                $ProductionRow = $originalRow;//28
                            }else{//190
                                $ProductionRow = $headerRow;//189
                            } */
                            $ProductionRow = $HeaderRowArr[$ProductionK] + $gradeDataArrCnt + 1;
                            $sheet->setCellValue("A$ProductionRow", "Production");
                            // 3️⃣ Apply style
                            $sheet->getStyle("A$ProductionRow")->getFont()->setBold(true)->setSize(12);

                            // Alignment + wrap
                            $sheet->getStyle("A{$ProductionRow}")->getAlignment()
                                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                ->setWrapText(true);

                            // Auto row height (IMPORTANT)
                            $sheet->getRowDimension($ProductionRow)->setRowHeight(-1);
                            $sheet->getStyle("A$ProductionRow")->applyFromArray($borderArr);

                            $colIndex = 'B';
                            foreach($ProductionItem as $ItemFreezingData){
                                $sheet->setCellValue("$colIndex$ProductionRow", $ItemFreezingData->FreezingWeight);
                                $sheet->getStyle("$colIndex$ProductionRow")->getFont()->setSize(12);
                                $sheet->getStyle("$colIndex$ProductionRow")->getAlignment()
                                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                $sheet->getStyle("$colIndex$ProductionRow")->applyFromArray($borderArr);
                                $colIndex++;
                            }
                            /* if(count($ProductionArr) == 1 || $ProductionK == 0){
                                $originalRow++;//                                
                            }else{ 
                                $headerRow++;//190                              
                            }  */
                        }
                    }
                    $row++;
                }
                $row++;
            }
            if(!empty($GrandTotals)){
                $row++;
                $totalValue = $totalSigleDayValue = 0;
                foreach($GrandTotals as $gTKey => $gTItem){
                   // $totalValue += (float) ($gTItem->Value ?? 0);
                   // $totalSigleDayValue += (float) ($gTItem->singleDayValue ?? 0);
                    $totalValue += (float) (($gTItem->Value)? $gTItem->Value : 0);
                    $totalSigleDayValue += (float) (($gTItem->singleDayValue)? $gTItem->singleDayValue : 0);

                    $sheet->mergeCells("A{$row}:C{$row}");
                    $sheet->setCellValue("A{$row}", "GRAND TOTAL - ".$gTKey);

                    $sheet->getStyle("A{$row}")->getFont()
                        ->setBold(true)
                        ->setSize(12);
                        //->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);


                    // Alignment + wrap
                    $sheet->getStyle("A{$row}")->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                        ->setWrapText(true);

                    // Auto row height (IMPORTANT)
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                    $sheet->getStyle("A{$row}:C{$row}")->applyFromArray($borderArr);


                    $sheet->setCellValue("D$row", $gTItem->Value);
                    $sheet->getStyle("D$row")->getFont()->setSize(12);
                    $sheet->getStyle("D$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle("D$row")->applyFromArray($borderArr);

                    $sheet->setCellValue("E$row", $_POST['type']);
                    $sheet->getStyle("E$row")->getFont()->setSize(12);
                    $sheet->getStyle("E$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle("E$row")->applyFromArray($borderArr);

                    $sheet->setCellValue("F$row", $gTItem->singleDayValue);
                    $sheet->getStyle("F$row")->getFont()->setSize(12);
                    $sheet->getStyle("F$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle("F$row")->applyFromArray($borderArr);

                    $sheet->setCellValue("G$row", $_POST['type']);
                    $sheet->getStyle("G$row")->getFont()->setSize(12);
                    $sheet->getStyle("G$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle("G$row")->applyFromArray($borderArr);

                    $row++;
                } 
                $sheet->mergeCells("A{$row}:C{$row}");
                $sheet->setCellValue("A{$row}", "TOTAL :");

                $sheet->getStyle("A{$row}")->getFont()
                    ->setBold(true)
                    ->setSize(12);
                   // ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);


                $sheet->getStyle("A$row")->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A{$row}:C{$row}")->applyFromArray($borderArr);

                $sheet->setCellValue("D$row", $totalValue);
                $sheet->getStyle("D$row")->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle("D$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("D$row")->applyFromArray($borderArr);

                $sheet->setCellValue("E$row", $_POST['type']);
                $sheet->getStyle("E$row")->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle("E$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("E$row")->applyFromArray($borderArr);

                $sheet->setCellValue("F$row", $totalSigleDayValue);
                $sheet->getStyle("F$row")->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle("F$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("F$row")->applyFromArray($borderArr);

                $sheet->setCellValue("G$row", $_POST['type']);
                $sheet->getStyle("G$row")->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle("G$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("G$row")->applyFromArray($borderArr);

               /*  $style = [
                    'borders' => [
                        'outline' => [
                            'borderStyle' => PHPExcel_Style_Border::BORDER_THICK,
                        ],
                        'inside' => [
                            'borderStyle' => PHPExcel_Style_Border::BORDER_THIN,
                        ],
                    ],
                ];

                $sheet->getStyle("B$row:I$row")->applyFromArray($style); */    
                $row++;           
                
            }
            if(!empty($ShipmentDataList)){
                $row++;
                foreach($ShipmentDataList as $Item){
                    $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", $Item->ShipmentData);
                    $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                      //  ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                    // Alignment + wrap
                    $sheet->getStyle("A{$row}")->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                        ->setWrapText(true);

                    // Auto row height (IMPORTANT)
                    $rowHeight = '-1';
                    if(strlen($Item->ShipmentData) > 73){
                        $rowHeight = '45';
                    }
                    $sheet->getRowDimension($row)->setRowHeight($rowHeight);
                    $sheet->getStyle("A$row:$mergeRow$row")->applyFromArray($borderArr);
                    
                    $row++;
                    $sheet->setCellValue("A$row", 'SNo');
                    $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                        //->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                    $sheet->getStyle("A$row")->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("A$row")->applyFromArray($borderArr);

                    $sheet->mergeCells("B$row:$mergeRow$row")->setCellValue("B$row", 'Description');
                    $sheet->getStyle("B$row")->getFont()->setBold(true)->setSize(12);
                       // ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
                    $sheet->getStyle("B$row:$mergeRow$row")->applyFromArray($borderArr);

                    $sheet->getStyle("B$row")->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    
                    $row++;
                    foreach($Item->ShipmentBreakUp as $BreakupKey => $ItemShipmentBreakUp){
                        $sheet->setCellValue("A$row", ++$BreakupKey);
                        $sheet->getStyle("A$row")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        $sheet->getStyle("A$row")->applyFromArray($borderArr);

                         /* $sheet->getStyle("A$row")->getAlignment()
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); */

                        $sheet->mergeCells("B$row:$mergeRow$row")->setCellValue("B$row", $ItemShipmentBreakUp);
                        $sheet->getStyle("B$row")->getFont()->setSize(12);
                          //  ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
                        

                        // Alignment + wrap
                        $sheet->getStyle("B{$row}")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                            ->setWrapText(true);

                        // Auto row height (IMPORTANT)
                        $rowHeight = '-1';
                        if(strlen($ItemShipmentBreakUp) > 65){
                            $rowHeight = '45';
                        }
                        $sheet->getRowDimension($row)->setRowHeight($rowHeight);
                        $sheet->getStyle("B$row:$mergeRow$row")->applyFromArray($borderArr);
                        $row++;
                    }
                    $row++;
                }
                

            }
            if(!empty($IBTInData)){
                $row++;
                foreach($IBTInData as $Item){
                    $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", $Item->FromStoreName);
                    $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                      //  ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                    $sheet->getStyle("A$row")->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("A$row:$mergeRow$row")->applyFromArray($borderArr);

                    $row++;
                    $sheet->setCellValue("A$row", 'SNo');
                    $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                       // ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                    $sheet->getStyle("A$row")->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("A$row:$mergeRow$row")->applyFromArray($borderArr);

                    $sheet->mergeCells("B$row:$mergeRow$row")->setCellValue("B$row", 'Description');
                    $sheet->getStyle("B$row")->getFont()->setBold(true)->setSize(12);
                       // ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                    $sheet->getStyle("B$row")->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("B$row:$mergeRow$row")->applyFromArray($borderArr);
                    $row++;
                    foreach($Item->Breakup as $BreakupKey => $ItemBreakUp){
                        $sheet->setCellValue("A$row", ++$BreakupKey);
                        $sheet->getStyle("A$row")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        $sheet->getStyle("A$row")->applyFromArray($borderArr);

                        $sheet->mergeCells("B$row:$mergeRow$row")->setCellValue("B$row", $ItemBreakUp);
                        $sheet->getStyle("B$row")->getFont()->setSize(12);
                           // ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
                        $sheet->getStyle("B$row:$mergeRow$row")->applyFromArray($borderArr);

                        // Alignment + wrap
                        $sheet->getStyle("B{$row}")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                            ->setWrapText(true);

                        // Auto row height (IMPORTANT)
                        $rowHeight = '-1';
                        if(strlen($ItemBreakUp) > 65){
                            $rowHeight = '45';
                        }
                        $sheet->getRowDimension($row)->setRowHeight($rowHeight);
                        $row++;
                    }
                    $row++;
                }
            }
            if(!empty($ConversionsData)){
                $row++;
                $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", 'CONVERSION :');
                $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12)
                    ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                $sheet->getStyle("A$row")->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                //$sheet->getStyle("A$row:$mergeRow$row")->applyFromArray($borderArr);

                $row++;
                foreach($ConversionsData as $ConKey => $Item){
                   // Merge cells
                    $sheet->mergeCells("A{$row}:{$mergeRow}{$row}")
                        ->setCellValue("A{$row}", ++$ConKey.'. '.$Item->Name);

                    // Font style
                    $sheet->getStyle("A{$row}")->getFont()
                        ->setSize(12);
                      //  ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                    // Alignment + wrap
                    $sheet->getStyle("A{$row}")->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                        ->setWrapText(true);

                    // Auto row height (IMPORTANT)
                    $rowHeight = '-1';
                    if(strlen($Item->Name) > 65){
                        $rowHeight = '65';
                    }
                    $sheet->getRowDimension($row)->setRowHeight($rowHeight);
                    //$sheet->getStyle("A$row:$mergeRow$row")->applyFromArray($borderArr);

                    // Set column width (main column)
                  //  $sheet->getColumnDimension('A')->setWidth(45);

                    $row++;
                }
            }
            
            if(!empty($IBTOutData)){
                $row++;
                foreach($IBTOutData as $Item){
                    $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", $Item->ToStoreName);
                    $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                       // ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                    // Alignment + wrap
                    $sheet->getStyle("A{$row}")->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                        ->setWrapText(true);

                    // Auto row height (IMPORTANT)
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                    $sheet->getStyle("A$row:$mergeRow$row")->applyFromArray($borderArr);

                    $row++;
                    $sheet->setCellValue("A$row", 'SNo');
                    $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
                        //->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                    $sheet->getStyle("A$row")->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("A$row")->applyFromArray($borderArr);

                    $sheet->mergeCells("B$row:$mergeRow$row")->setCellValue("B$row", 'Description');
                    $sheet->getStyle("B$row")->getFont()->setBold(true)->setSize(12);
                        //->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                    $sheet->getStyle("B$row")->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("B$row:$mergeRow$row")->applyFromArray($borderArr);
                    $row++;
                    foreach($Item->Breakup as $BreakupKey => $ItemBreakUp){
                        $sheet->setCellValue("A$row", ++$BreakupKey);
                        $sheet->getStyle("A$row")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        $sheet->getStyle("A$row")->applyFromArray($borderArr);

                        $sheet->mergeCells("B$row:$mergeRow$row")->setCellValue("B$row", $ItemBreakUp);
                        $sheet->getStyle("B$row")->getFont()->setSize(12);
                            //->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                        // Alignment + wrap
                        $sheet->getStyle("B{$row}")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                            ->setWrapText(true);

                        // Auto row height (IMPORTANT)
                        $rowHeight = '-1';
                        if(strlen($ItemBreakUp) > 65){
                            $rowHeight = '45';
                        }
                        $sheet->getRowDimension($row)->setRowHeight($rowHeight);
                        $sheet->getStyle("B$row:$mergeRow$row")->applyFromArray($borderArr);
                        $row++;
                    }
                    $row++;
                }
            }
            ob_clean();
            // Write to Output
            $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
            $filePath = $_SERVER['DOCUMENT_ROOT'] . "/daily-stock-report-plant-wise-" . $timestamp . ".xlsx";
            $writer->save($filePath);

            $data['filename'] =  "/download-file.php?file=" . "daily-stock-report-plant-wise-" . $timestamp . ".xlsx";
            $data['status'] = 'Done';
            echo json_encode($data);die;
        } else {
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