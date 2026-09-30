<?php 
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

set_time_limit(0);
ini_set('memory_limit', '-1');

//echo ini_get('memory_limit');die;
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STOCK_VALUE_REPORT_LANDSCAPE;


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
    "StockBlgsTo" => $_POST['drpStockBlgsTo'],
    "Request" => $_POST['Request'],
);
$resList = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($resList);die;
if (isset($resList) && !empty($resList)) {
    $error = $resList->Error;
    if (!isset($error) || empty($error)) {
        if($_POST['Request'] == 'SummeryData'){
           // echo '<pre>';print_r($resList);die;
            ?>
            <div style="overflow-x:auto; width:100%;">
                <table id="tblDataTable" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Store Name</th>
                            <th>Total Mcs</th>
                            <th>Total Kgs</th>
                            <th>Total USD Value</th>
                            <th>ExchangeRate</th>
                            <th>Total INR Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $ExchangeRate = !empty($resList->ExchangeRate) ? $resList->ExchangeRate : 0;
                        $TotalMcs = $TotalKgs = $TotalUSD = $TotalINR = 0;
                        foreach($resList->Stores as $StoresData){
                            $Mcs = $usd = $kgs = 0;
                            foreach($StoresData->Total as $key => $Totals){
                                if($key == 'mcs'){
                                    $Mcs = $Totals;
                                }else if($key == 'KGS'){
                                    $kgs = $Totals;
                                }else if($key == 'USD'){
                                    $usd = $Totals;
                                }
                            }
                            $INR = !empty($usd) && !empty($ExchangeRate) ? ($usd * $ExchangeRate) : 0;

                            $TotalMcs += $Mcs;
                            $TotalKgs += $kgs;
                            $TotalUSD += $usd;
                            $TotalINR += $INR;
                            ?>
                            <tr>
                                <td><?php echo $StoresData->StoreName;?></td>
                                <td><?php echo number_format($Mcs, 2);?></td>
                                <td><?php echo number_format($kgs, 2);?></td>
                                <td><?php echo number_format($usd, 2);?></td>
                                <td><?php echo $ExchangeRate;?></td>
                                <td>
                                    <?php echo  number_format($INR, 2);?>
                                </td>
                            </tr>
                        <?php } ?>                          
                    </tbody>
                    <?php 
                    if(empty($_POST['coldStores'])){?>
                        <tfoot>
                            <tr>
                                <td>Totals</td>
                                <td><?php echo number_format($TotalMcs, 2);?></td>
                                <td><?php echo number_format($TotalKgs, 2);?></td>
                                <td><?php echo number_format($TotalUSD, 2);?></td>
                                <td></td>
                                <td><?php echo number_format($TotalINR, 2);?></td>
                            </tr>
                        </tfoot>
                    <?php } ?>
                </table>
            </div>
        <?php
        }else{
            $timestamp = date("YmdHis");
            $List = $resList->Data;
            $ListCnt = count($resList->Data);
            $Slice = 2;//after mention no. of column data printing one by one
            $mergeRow = "I";
            $datasets = [
                $List
            ];
            $ExchangeRate = (float)$resList->ExchangeRate;
            $Stores = $resList->Stores;
            //echo '<pre>';print_r($resList->Stores);die;
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
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    //$sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "DT : ".$productionDate);
                    $row++;
                }
                $row++;//3
                if (!empty($List)){                
                    $ch = 0;
                    $GrandTotalMCs = $GrandTotalKgs = $GrandTotalDollarVal = $GrandTotalInr = 0;
                    foreach ($List as $Item) {
                        // caclualte USDTotal 
                        /* if (!empty($Item->GradesData)) {//$MergeCol
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
                        } */
                        $headerArr = [];
                        if (!empty($Item->VarietyCombinationData)) {
                            foreach ($Item->VarietyCombinationData as $ItemBreakUp) {
                                $Uom = $ItemBreakUp->Uom;
                                $headerArr[] = [
                                    'Species' => $ItemBreakUp->Species.'-'.$ItemBreakUp->ShortName.'-'.$ItemBreakUp->PreparationType.'-'.$ItemBreakUp->SockingType,
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
                        $gradeDataArrCnt = count($Item->GradesData);
                        $originalRow = $row;$HeaderRowArr = [];
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
                                        // $sheet->getStyle("$mergeRange")->applyFromArray($borderArr);
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
                                                //  $sheet->getStyle("$HeadeSubIndex$headerRow")->applyFromArray($borderArr);
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
                                // $sheet->getStyle("$colIndex$headerRow")->applyFromArray($borderArr);
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
                        //   $sheet->getStyle("A$row:A$MergeCol")->applyFromArray($borderArr);
                        }
                        //$row = $headerRow;
                        if($HeaderRowKey == 0){
                            $originalRow = $MergeCol + 1;
                        }
                        $row = $headerRow + $gradeDataArrCnt + 2;

                        if (!empty($Item->GradesData)) {//$MergeCol
                            $originalRowOld = $originalRow;
                            foreach ($Item->GradesData as $gradeI => $itemGrades) {
                                $GradeRow = $originalRow + $gradeI;

                                $sheet->setCellValue("A$GradeRow", $itemGrades->Grade);//21/25//
                                $sheet->getStyle("A$GradeRow")
                                        ->getAlignment()
                                        //->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                        //->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                        ->setWrapText(true);

                                $colIndex = $ItemSubIndexStyle = 'B';

                                foreach ($itemGrades->GradeValueData as $GradeItemKey => $gradeValuesItemArr) {
                                    $ItemSubIndex = $colIndex;
                                    foreach ($gradeValuesItemArr as $GradeValueKey => $gradeValuesItem) {

                                        $cell = $ItemSubIndex . $GradeRow;

                                        // Set value
                                        
                                        if ($GradeValueKey != 'MCS') {
                                        $gradeValuesItem = $gradeValuesItem != 0 ? round($gradeValuesItem, 3) : '-';
                                            $sheet->setCellValue($cell, $gradeValuesItem);
                                        } else {
                                        $gradeValuesItem = $gradeValuesItem != 0 ? $gradeValuesItem : '-';
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
                                                //  'borders' => !empty($borderArr['borders']) ? $borderArr['borders'] : []
                                                ]);
                                    }
                                    $colIndex = $ItemSubIndex;
                                }

                            }

                            $ranges = [
                                "B$originalRowOld:$ItemSubIndexStyle$GradeRow"
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
                                //  'borders' => !empty($borderArr['borders']) ? $borderArr['borders'] : []
                                ]);

                                
                                $sheet->getStyle("B$originalRowOld:$ItemSubIndexStyle$GradeRow")
                                                ->getAlignment()
                                                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                            // ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                                ->setWrapText(true);
                            }

                        }
                        $TotalMCs = $TotalKgs = $TotalDollarVal = $TotalPrice = 0;
                        if (!empty($Item->GrandTotals)) {
                            $originalRowOld = $originalRow;

                            $colIndex = $grandTotalEndIndex = 'B';
                            foreach($Item->GrandTotals as $GrandTotalsK => $GrandTotalsItem){
                                $GrandTotalRow = $originalRow + $gradeDataArrCnt;
                                $GtUnitID = $GrandTotalsItem->UnitID;

                                $sheet->setCellValue("A$GrandTotalRow", "Totals");
                                $ItemSubIndex = $colIndex;
                                foreach ($GrandTotalsItem as $key => $GrandTotals) {

                                    $cell = $ItemSubIndex . $GrandTotalRow;

                                    // Set value
                                    if($key != 'ConvertToKgs' && $key != 'UnitID'){
                                        $sheet->setCellValueExplicit(
                                                            $cell,
                                                            ( $key != 'MCS') ? round($GrandTotals, 3): $GrandTotals,
                                                            PHPExcel_Cell_DataType::TYPE_NUMERIC
                                                        );
                                    }
                                    $TotalMCsStore = $TotalKgsStore = $TotalDollarValStore = '';
                                    if($key == 'MCS'){ 
                                        $TotalMCsStore = $GrandTotals;
                                    }elseif($key == 'ConvertToKgs'){
                                        $TotalKgsStore = $GrandTotals;
                                    }elseif($key == 'INUSD'){
                                        $TotalDollarValStore = $GrandTotals; 
                                    }
                                    /* $foundKey = false;

                                    foreach ($Stores as $keyG => $store) {
                                        if ($store->StoreID == $GtUnitID) {
                                            $foundKey = $keyG;
                                            break;
                                        }
                                    }
                                    if ($foundKey !== false) {
                                        $Stores[$foundKey]->Total['mcs'] =
                                                (isset($Stores[$foundKey]->Total['mcs']) ? $Stores[$foundKey]->Total['mcs'] : 0)
                                                + $TotalMCsStore;

                                        $Stores[$foundKey]->Total['KGS'] =
                                            (isset($Stores[$foundKey]->Total['KGS']) ? $Stores[$foundKey]->Total['KGS'] : 0)
                                            + $TotalKgsStore;

                                        $Stores[$foundKey]->Total['USD'] =
                                            (isset($Stores[$foundKey]->Total['USD']) ? $Stores[$foundKey]->Total['USD'] : 0)
                                            + $TotalDollarValStore;
                                    } */

                                    if($key == 'MCS'){ 
                                        $TotalMCs += $GrandTotals;
                                    }elseif($key == 'ConvertToKgs'){
                                        $TotalKgs += $GrandTotals;
                                    }elseif($key == 'INUSD'){
                                        $TotalDollarVal += $GrandTotals; 
                                    }
                                    if($key != 'ConvertToKgs' && $key != 'UnitID'){
                                        $grandTotalEndIndex = $ItemSubIndex;
                                        $ItemSubIndex++;
                                    }
                                }     
                                $colIndex = $ItemSubIndex;
                                
                                $ranges = [
                                    "B$GrandTotalRow:$grandTotalEndIndex$GrandTotalRow"
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
                                    //  'borders' => !empty($borderArr['borders']) ? $borderArr['borders'] : []
                                    ]);

                                    $sheet->getStyle("B$GrandTotalRow:$grandTotalEndIndex$GrandTotalRow")->applyFromArray([
                                        'font' => [
                                            'bold' => false,
                                            'size' => 12
                                        ],
                                    //  'borders' => !empty($borderArr['borders']) ? $borderArr['borders'] : []
                                    ]);
                                    
                                    $sheet->getStyle("B$GrandTotalRow:$grandTotalEndIndex$GrandTotalRow")
                                            ->getAlignment()
                                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                            // ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)
                                            ->setWrapText(true);
                                }
                            }                        
                        }
                    // echo '<pre>';print_r($Stores);die;
                        if (!empty($Item->FreezingData)) {                        
                            $colIndex = $productioneEndIndex = 'B';
                            foreach($Item->FreezingData as $ProductionK => $ProductionItem){
                                $ProductionRow = $originalRow + $gradeDataArrCnt + 1;
                                $sheet->setCellValue("A$ProductionRow", "Production");
                                
                                $productionIdex = $colIndex;
                                foreach($ProductionItem as $ItemFreezingItemData){
                                    $sheet->setCellValue("$productionIdex$ProductionRow", number_format($ItemFreezingItemData, 3));
                                    
                                    $productioneEndIndex = $productionIdex;
                                    $productionIdex++;    
                                }
                                
                                $colIndex = $productionIdex;

                                $ranges = [
                                    "B$ProductionRow:$productioneEndIndex$ProductionRow"
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
                                    //   'borders' => !empty($borderArr['borders']) ? $borderArr['borders'] : []
                                    ]);
                                    
                                
                                
                                    /* $sheet->getStyle("B$ProductionRow:$productioneEndIndex$ProductionRow")->applyFromArray([
                                        'borders' => !empty($borderArr['borders']) ? $borderArr['borders'] : []
                                    ]); */
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
                        
                    // echo '<pre>';print_r($headerArr);die;
                        //$row++;
                        if(!empty($TotalMCs)){                    
                            $TotalInr = $ExchangeRate * $TotalDollarVal;
                        
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
                    
                    if(!empty($Stores)){
                        $ExchangeRowRange = "J$row:N$row";
                        $ExchgIndex = "J";
                    }else{
                        $ExchangeRowRange = "A$row:I$row";
                        $ExchgIndex = "A";
                    }
                    $sheet->mergeCells("$ExchangeRowRange")->setCellValue("$ExchgIndex$row", "Exchange Rate : ".(float)$ExchangeRate);
                    $sheet->getStyle("$ExchangeRowRange")->getFont()->setBold(true)->setSize(12)
                                        ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
                    if(!empty($Stores)){
                        foreach($Stores as $StoreItem){
                        // echo '<pre>';print_r($StoreItem->Total->mcs);die;
                            if(!empty($StoreItem->Total)){
                                // echo '<pre>';print_r("A$row:$mergeRow$row");die;
                                $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "STORE : ".$StoreItem->StoreName);
                                $sheet->getStyle("A$row:$mergeRow$row")->getFont()->setBold(true)->setSize(12)
                                        ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

                                        //  $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12)
                                        //         ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
                                $row++;
                                $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "TOTAL MASTER CARTONS : ".(int)$StoreItem->Total->mcs);
                                $row++;
                                $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "TOTAL KGS :  ".number_format($StoreItem->Total->KGS, 3));
                                $row++;
                                $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "TOTAL DOLLAR VALUE: ".number_format($StoreItem->Total->USD, 3));
                                $row++;
                                $TotalInr = $ExchangeRate * $StoreItem->Total->USD;
                                $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "TOTAL VALUE IN RUPEE : ".number_format($TotalInr, 3));
                                $row++;
                                $row++;
                            }
                        }

                    }

                /*  if(count($Stores) > 1){
                        $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "GRAND TOTALS :");
                                $sheet->getStyle("A$row:$mergeRow$row")->getFont()->setBold(true)->setSize(12)
                                        ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
                        
                        $row++;

                        $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "TOTAL MASTER CARTONS : ".(int)$GrandTotalMCs);
                        

                        $row++;
                        
                        $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "TOTAL KGS :  ".number_format($GrandTotalKgs, 3));
                        
                        $row++;
                        
                        $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "TOTAL DOLLAR VALUE: ".number_format($GrandTotalDollarVal, 3));
                    
                        $row++;
                        $TotalInr = $ExchangeRate * $TotalDollarVal;
                        $sheet->mergeCells("A$row:$mergeRow$row")->setCellValue("A$row", "TOTAL VALUE IN RUPEE : ".number_format($GrandTotalInr, 3));
                        // 3️⃣ Apply style
                        //$sheet->getStyle("A$totalRow:$mergeRow$row")->getFont()->setBold(true)->setSize(12);
                        
                    } */
                $sheet->getStyle("A$totalRow:$mergeRow$row")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    $sheet->freezePane('B1');// freeze
                    
                    
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

        }
    }else {
        $data['msg'] =  "ERROR : . $error . ";
        $data['status'] = 'Done';
        echo json_encode($data);die;
    }
}