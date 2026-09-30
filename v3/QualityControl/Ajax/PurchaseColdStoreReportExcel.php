    <?php
    require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
    include_once ROOT . 'config/api-call.php';

    function fmtAbStatus($val) {
        switch (trim($val)) {
        case 'AB+':
        return 'POSITIVE';
        case 'AB-':
        return 'NEGATIVE';
        default:
        return $val;
        }
    }

        $fromDate = isset($_GET['fromDate']) ? $_GET['fromDate'] : '';
        $toDate   = isset($_GET['toDate']) ? $_GET['toDate'] : '';
        $lotIds   = isset($_GET['lotIds']) ? $_GET['lotIds'] : '';
        $LotIdsArray = !empty($lotIds) ? explode(',', $lotIds) : [];



        if (empty($fromDate) || empty($toDate)) {
            echo json_encode([
            "status" => "error",
            "error" => "From Date and To Date are required"
            ]);
            exit;
        }
        $api = API_PURCHASE_COLDSTORE_REPORT;


        // echo print_r($api);exit;

        $bodyContent = [
        'FromDate' => $fromDate,
        'ToDate'   => $toDate,
        // 'lotIds'   => $lotIds
        'LotIds'   => $LotIdsArray 
        ];


        $results = CallAPI($api, "POST", "", $bodyContent);

        //  echo print_r($bodyContent );exit;

        if (
            empty($results) ||
            (is_array($results) && empty($results['PurchaseColdStoreReportData'])) ||
            (is_object($results) && empty($results->PurchaseColdStoreReportData))
        ) {
            echo json_encode(array(
            "status" => "error",
            "error"  => (is_array($results) && isset($results['Error']))
                ? $results['Error']
                : ((is_object($results) && isset($results->Error))
                    ? $results->Error
                    : "No data found")
            ));
            exit;
        }

        function setExcelDate($sheet, $cell, $date)
        {
            if (empty($date)) {
                $sheet->setCellValue($cell, '');
                return;
            }

            // Force date only (NO TIME, NO TIMEZONE)
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
                $dt = DateTime::createFromFormat('d-m-Y', $date);
            } else {
                $dt = DateTime::createFromFormat('Y-m-d', substr($date, 0, 10));
            }

            if ($dt !== false) {
                $dt->setTime(0, 0, 0); 

                $excelDate = PHPExcel_Shared_Date::PHPToExcel($dt);

                $sheet->setCellValueExplicit(
                $cell,
                $excelDate,
                PHPExcel_Cell_DataType::TYPE_NUMERIC
                );

                $sheet->getStyle($cell)
                    ->getNumberFormat()
                    ->setFormatCode('dd-mm-yyyy');
            }
        }


        $List = $results->PurchaseColdStoreReportData;
        // echo '<pre>';print_r($results);die;
        //   echo print_r($List );exit;

        function setDecimalCell($sheet, $cell, $value, $decimals = 2)
        {
            if ($value === '' || $value === null || $value === '-') {
                $sheet->setCellValue($cell, '');
                return;
            }

            if (is_numeric($value)) {
                $sheet->setCellValueExplicit(
                $cell,
                (float)$value,
                PHPExcel_Cell_DataType::TYPE_NUMERIC
                );

                $format = $decimals === 2 ? '0.00' : '0.000';
                $sheet->getStyle($cell)
                    ->getNumberFormat()
                    ->setFormatCode($format);
            } else {
                $sheet->setCellValue($cell, $value);
            }
        }

        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();
        $timestamp = time();


     

        for ($col = 0; $col <= 45; $col++) {
        $sheet->getColumnDimensionByColumn($col)->setWidth(10);
        }

        $row = 1;
        foreach ($List as $item) {
            
        // echo '<pre>';
        // print_r($item->InwardMcs);
        // print_r($item->OutwardMcs);
        // echo '</pre>'; exit;

        $verticalMerge = $row+1;
        // ================= BATCH TITLE =================
        $sheet->mergeCells("A{$row}:B{$verticalMerge}");
        $sheet->setCellValue("A{$row}", "Batch No: " . $item->BatchCode);
        $sheet->getStyle("A$row")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A$row")->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $sheet->mergeCells("C{$row}:J{$verticalMerge}");

        $sheet->mergeCells("K{$row}:M{$row}");
        $sheet->setCellValue("K{$row}", "HO TO HL YIELD % : " . $item->HoToHlYield);
        $sheet->getStyle("K{$row}")->getFont()->setBold(true)->setSize(10);
        $row += 1;

        $sheet->mergeCells("K{$row}:M{$row}");
        $sheet->setCellValue("K{$row}", "HO TO FINISHED YIELD % : " . $item->HoToFinishedYield);
        $sheet->getStyle("K{$row}")->getFont()->setBold(true)->setSize(10);
        $row += 1;

        // Section Titles
        $sheet->setCellValue("A{$row}", "Purchase Details");
        $sheet->mergeCells("A{$row}:C{$row}");

        $sheet->setCellValue("D{$row}", "Harvest Details");
        $sheet->mergeCells("D{$row}:F{$row}");

        $sheet->setCellValue("G{$row}", "Receiving Details");
        $sheet->mergeCells("G{$row}:I{$row}");

        $sheet->setCellValue("J{$row}", "Antibiotic Test");
        $sheet->mergeCells("J{$row}:L{$row}");

        $sheet->getStyle("A{$row}:M{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:M{$row}")
        ->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $row++;

        // Purchase
        $sheet->setCellValue("A{$row}", "Date");
        $sheet->setCellValue("B{$row}", "Quantity");
        $sheet->setCellValue("C{$row}", "Count");

        // Harvest
        $sheet->setCellValue("D{$row}", "Date");
        $sheet->setCellValue("E{$row}", "Quantity ");
        $sheet->setCellValue("F{$row}", "Count");

        // Receiving
        $sheet->setCellValue("G{$row}", "Date");
        $sheet->setCellValue("H{$row}", "Quantity ");
        $sheet->setCellValue("I{$row}", "Plant Count");
        $sheet->setCellValue("J{$row}", "QuantityDiff");
        $sheet->setCellValue("K{$row}", "CountDiff");
        $sheet->setCellValue("L{$row}", "Antibiotic Test");



        $sheet->getStyle("A{$row}:M{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:M{$row}")
        ->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $row++;


    $abRowCount = 1; 

     for ($i = 0; $i < $abRowCount; $i++) {

    if ($i == 0) {

        $sheet->setCellValue("A{$row}", date('d-m-y', strtotime($item->BookingDate)));
        setDecimalCell($sheet,"B{$row}",$item->BookingWeight);
        setDecimalCell($sheet,"C{$row}",$item->BookingCount);

        $sheet->setCellValue("D{$row}", date('d-m-y', strtotime($item->HarvestDate)));
        setDecimalCell($sheet,"E{$row}",$item->HarvestWeight);
        setDecimalCell($sheet,"F{$row}",$item->HarvestCount);

        $sheet->setCellValue("G{$row}", date('d-m-y', strtotime($item->ArrivalDate)));
        setDecimalCell($sheet,"H{$row}",$item->ReceivingQty);
        setDecimalCell($sheet,"I{$row}",$item->ReceivingCount);
        setDecimalCell($sheet,"J{$row}",$item->QuantityDifference);
        setDecimalCell($sheet,"K{$row}",$item->CountDifference);

      
        $sheet->setCellValue( "L{$row}", !empty($item->AbStatus) ? $item->AbStatus : 'TEST PENDING' );
            }

            $row++;
        }

        $sheet->mergeCells("A{$row}:M{$row}");
        $sheet->setCellValue("A{$row}", "Pre-Processing");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $row++;

        // Section Titles
        $sheet->setCellValue("A{$row}", "Deheading Details");
        $sheet->mergeCells("A{$row}:D{$row}");

        $sheet->setCellValue("E{$row}", "Grading Details");
        $sheet->mergeCells("E{$row}:G{$row}");

        $sheet->setCellValue("H{$row}", "Value Addition Details");
        $sheet->mergeCells("H{$row}:N{$row}");

        $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:L{$row}")
        ->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $row++;



        $sheet->setCellValue("A{$row}", "Head On Qty");
        $sheet->setCellValue("B{$row}", "Head Less Qty");
        $sheet->setCellValue("C{$row}", "HL Count");
        $sheet->setCellValue("D{$row}", "Count LBS");
        $sheet->setCellValue("E{$row}", "Yield %");

        $sheet->setCellValue("F{$row}", "No Of Grade");
        $sheet->setCellValue("G{$row}", "Count");
         $sheet->setCellValue("H{$row}", "Variety");
        $sheet->setCellValue("I{$row}", "Quantity");

        $sheet->setCellValue("J{$row}", "VA Date");
        $sheet->setCellValue("K{$row}", "Variety");
        $sheet->setCellValue("L{$row}", "HL Qty");
        $sheet->setCellValue("M{$row}", "VA Qty");
        $sheet->setCellValue("N{$row}", "VA Yield %");
        $sheet->setCellValue("O{$row}", "Floor Balance");

        $sheet->getStyle("A{$row}:N{$row}")->getFont()->setBold(true);
        $row++;

        $grades = explode(',', $item->PackingGrade);
        $gCounts = explode(',', $item->HLCount);
        $gWeights = explode(',', $item->HLWeight);
        $var = explode(',', $item->Variety);
        $vaDates = explode(',', $item->ValueAdditionDate);
        $vaVar = explode(',', $item->VaShortName);
        $sentWt = explode(',', $item->SentWeight);
        $vaWt = explode(',', $item->WtAftrVA);
        $vaYield = explode(',', $item->Yields);
        $vaFloorBalance = explode(',', $item->FloorBalance);
        for ($g = 0; $g < count($grades); $g++) {

            $countArr   = isset($gCounts[$g]) ? explode('|', $gCounts[$g]) : [];
            $weightArr  = isset($gWeights[$g]) ? explode('|', $gWeights[$g]) : [];
            $varArr   = isset($var[$g]) ? explode('|', $var[$g]) : [];
            $vaDateArr  = isset($vaDates[$g]) ? explode('|', $vaDates[$g]) : [];
            $vaVarArr   = isset($vaVar[$g]) ? explode('|', $vaVar[$g]) : [];
            $sentArr    = isset($sentWt[$g]) ? explode('|', $sentWt[$g]) : [];
            $vaWtArr    = isset($vaWt[$g]) ? explode('|', $vaWt[$g]) : [];
            $yieldArr   = isset($vaYield[$g]) ? explode('|', $vaYield[$g]) : [];
            $floorArr   = isset($vaFloorBalance[$g]) ? explode('|', $vaFloorBalance[$g]) : [];

            $innerMax = max(
                count($countArr),
                count($vaDateArr)
            );

            for ($i = 0; $i < $innerMax; $i++) {
                if ($g == 0 && $i == 0) {
                    setDecimalCell($sheet,"A{$row}",$item->HonWeight);
                    setDecimalCell($sheet,"B{$row}",$item->WtAfterDH);
                    setDecimalCell($sheet,"C{$row}",$item->CountAfterDH);
                    setDecimalCell($sheet,"D{$row}",$item->ctHLCount);
                    //setDecimalCell($sheet,"D{$row}",$item->Yield);
                    $sheet->setCellValue("E{$row}", $item->Yield);
                }

                $sheet->setCellValue("F{$row}", $i == 0 ? (isset($grades[$g]) ? $grades[$g] : '') : '');
                setDecimalCell($sheet,"H{$row}", isset($countArr[$i]) ? $countArr[$i] : '');
                setDecimalCell($sheet,"H{$row}", isset($varArr[$i]) ? $varArr[$i] : '');
                setDecimalCell($sheet,"I{$row}", isset($weightArr[$i]) ? $weightArr[$i] : '');
                $sheet->setCellValue("J{$row}",  isset($vaDateArr[$i]) && $vaDateArr[$i] !='-' ? date('d-m-y', strtotime( $vaDateArr[$i])) : '-');
                // setExcelDate($sheet,"I{$row}", isset($vaDateArr[$i]) ? $vaDateArr[$i] : '');
                $sheet->setCellValue("K{$row}", isset($vaVarArr[$i]) ? $vaVarArr[$i] : '');
                setDecimalCell($sheet,"L{$row}", isset($sentArr[$i]) ? $sentArr[$i] : '');
                setDecimalCell($sheet,"M{$row}", isset($vaWtArr[$i]) ? $vaWtArr[$i] : '');
                //setDecimalCell($sheet,"M{$row}", isset($yieldArr[$i]) ? $yieldArr[$i] : '');
                $sheet->setCellValue("N{$row}", isset($yieldArr[$i]) ? $yieldArr[$i] : '');
                setDecimalCell($sheet,"O{$row}", isset($floorArr[$i]) ? $floorArr[$i] : '');

                $row++;
            }
        }

        $row++;
        $sheet->mergeCells("A{$row}:M{$row}");
        $sheet->setCellValue("A{$row}", "Processing");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $row++;

        $sheet->setCellValue("A{$row}", "Grades");
        $sheet->mergeCells("A{$row}:A{$row}");

        $sheet->setCellValue("B{$row}", "Soaking Details");
        $sheet->mergeCells("B{$row}:F{$row}");

        $sheet->setCellValue("G{$row}", "Freezing Details");
        $sheet->mergeCells("G{$row}:N{$row}");

        

        $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:H{$row}")
        ->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $row++;


        $sheet->setCellValue("A{$row}", "Grades");
        $sheet->setCellValue("B{$row}", "Soaking Date");
        $sheet->setCellValue("C{$row}", "In Qty");
       // $sheet->setCellValue("D{$row}", "Out Qty");
        $sheet->setCellValue("D{$row}", "In Count");
        $sheet->setCellValue("E{$row}", "Out Count");
        $sheet->setCellValue("F{$row}", "Soaking Yield %");

        $sheet->setCellValue("G{$row}", "Freezing In Qty");
        $sheet->setCellValue("H{$row}", "Freezing Date");
        $sheet->setCellValue("I{$row}", "Packing Style");
        $sheet->setCellValue("J{$row}", "No Of Cases");
        $sheet->setCellValue("K{$row}", "Final/Dummy");
        $sheet->setCellValue("L{$row}", "Production Qty");
        $sheet->setCellValue("M{$row}", "Freezing Yield %");

        $sheet->getStyle("A{$row}:M{$row}")->getFont()->setBold(true);
        $row++;

        $grades = explode(',', $item->PackingGrade);
        $soakDates   = explode(',', $item->SoakingDate);
        $soakInQty   = explode(',', $item->SoakInQty);
        $soakOutQty  = explode(',', $item->SoakOutQty);
        $soakInCnt   = explode(',', $item->SoakInCount);   
        $soakOutCnt  = explode(',', $item->SoakOutCount);
        $soakYield   = explode(',', $item->SoakingYield);

        $freezeDates = explode(',', $item->FreezingDate);
        $styles      = explode(',', $item->PackingStyle);
        $cases       = explode(',', $item->NoofCases);
        $types       = explode(',', $item->PackingTypeID);
        $prodWt      = explode(',', $item->ProductionWeight);
        $freezeYield = explode(',', $item->FreezingYield);
        $FreezingInArr = explode('|', $item->FreezingIn);
        
        

        $maxRows = max(
        count($grades),
        count($soakDates),
        count($freezeDates)
        );

        for ($g = 0; $g < count($grades); $g++) {

            $soakDateArr  = isset($soakDates[$g])   ? explode('|', $soakDates[$g])   : [];
            $soakInArr   = isset($soakInQty[$g])  ? explode('|', $soakInQty[$g])  : [];
            $soakOutArr  = isset($soakOutQty[$g]) ? explode('|', $soakOutQty[$g]) : [];
            $soakInCntArr = isset($soakInCnt[$g])   ? explode('|', $soakInCnt[$g])   : [];
            $soakOutCntArr= isset($soakOutCnt[$g])  ? explode('|', $soakOutCnt[$g])  : [];
            $soakYieldArr = isset($soakYield[$g])   ? explode('|', $soakYield[$g])   : [];

            $freezeDateArr = isset($freezeDates[$g]) ? explode('|', $freezeDates[$g]) : [];
            $styleArr      = isset($styles[$g])      ? explode('|', $styles[$g])      : [];
            $caseArr       = isset($cases[$g])       ? explode('|', $cases[$g])       : [];
            $typeArr       = isset($types[$g])       ? explode('|', $types[$g])       : [];
            $prodArr       = isset($prodWt[$g])      ? explode('|', $prodWt[$g])      : [];
            $freezeYieldArr= isset($freezeYield[$g]) ? explode('|', $freezeYield[$g]) : [];
            $FreezingInQty= isset($FreezingInArr[$g]) ? explode(',', $FreezingInArr[$g]) : [];

            $innerMax = max(
            count($soakDateArr),
            count($freezeDateArr)
            );

            for ($i = 0; $i < $innerMax; $i++) {
                $sheet->setCellValue("A{$row}", $i == 0 ? (isset($grades[$g]) ? $grades[$g] : '') : '');
                $sheet->setCellValue("B{$row}",  isset($soakDateArr[$i]) && $soakDateArr[$i] !='-' ? date('d-m-y', strtotime( $soakDateArr[$i])) : '-');
                //setExcelDate($sheet,"B{$row}", isset($soakDateArr[$i]) ? $soakDateArr[$i] : '');
                setDecimalCell($sheet,"C{$row}", isset($soakInArr[$i]) ? $soakInArr[$i] : '');
                //setDecimalCell($sheet,"D{$row}", isset($soakOutArr[$i]) ? $soakOutArr[$i] : '');
                setDecimalCell($sheet,"D{$row}", isset($soakInCntArr[$i]) ? $soakInCntArr[$i] : '');
                setDecimalCell($sheet,"E{$row}", isset($soakOutCntArr[$i]) ? $soakOutCntArr[$i] : '');
                //setDecimalCell($sheet,"F{$row}", isset($soakYieldArr[$i]) ? $soakYieldArr[$i] : '');
                $sheet->setCellValue("F{$row}", isset($soakYieldArr[$i]) ? $soakYieldArr[$i] : '');

                setDecimalCell($sheet,"G{$row}", isset($FreezingInQty[$i]) ? $FreezingInQty[$i] : '');
                $sheet->setCellValue("H{$row}",  isset($freezeDateArr[$i]) && $freezeDateArr[$i] !='-' ? date('d-m-y', strtotime( $freezeDateArr[$i])) : '-');
                //setExcelDate($sheet,"H{$row}", isset($freezeDateArr[$i]) ? $freezeDateArr[$i] : '');
                $sheet->setCellValue("I{$row}", isset($styleArr[$i]) ? $styleArr[$i] : '');
                setDecimalCell($sheet,"J{$row}", isset($caseArr[$i]) ? $caseArr[$i] : '');
                $sheet->setCellValue("K{$row}", isset($typeArr[$i]) ? $typeArr[$i] : '');
                setDecimalCell($sheet,"L{$row}", isset($prodArr[$i]) ? $prodArr[$i] : '');
                //setDecimalCell($sheet,"M{$row}", isset($freezeYieldArr[$i]) ? $freezeYieldArr[$i] : '');
                $sheet->setCellValue("M{$row}", isset($freezeYieldArr[$i]) ? $freezeYieldArr[$i] : '');

                $row++;
            }
        }


        $sheet->mergeCells("A{$row}:M{$row}");
        $sheet->setCellValue("A{$row}", "Store");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $row++;

        // Section Titles
        $sheet->setCellValue("A{$row}", "Grading");
        $sheet->setCellValue("B{$row}", "Cold Store Details");
        $sheet->mergeCells("B{$row}:E{$row}");

        $sheet->setCellValue("F{$row}", "Shipment Details");
        $sheet->mergeCells("F{$row}:I{$row}");

        $sheet->getStyle("A{$row}:I{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:I{$row}")
        ->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $row++;

        $sheet->setCellValue("A{$row}", "Grade");
        $sheet->setCellValue("B{$row}", "Out Date");
        $sheet->setCellValue("C{$row}", "Inward MCs");
        $sheet->setCellValue("D{$row}", "Outward MCs");
        $sheet->setCellValue("E{$row}", "Pallet No");

        $sheet->setCellValue("F{$row}", "Shipment Date");
        $sheet->setCellValue("G{$row}", "Shipped Out");
        $sheet->setCellValue("H{$row}", "Remaining Stock");

        $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
        $row++;
        $outDates = explode('|', $item->OutDate);

        $grades     = !empty($item->PackingGrade) ? explode(',', $item->PackingGrade) : [];
        //$outDates   = !empty($item->OutDate) ? explode('|', $item->OutDate) : [];
        /* $stkIn      = !empty($item->StockInKgs) ? explode('|', $item->StockInKgs) : [];
        $stkOut     = !empty($item->StockOutKgs) ? explode('|', $item->StockOutKgs) : []; */
        $stkIn      = !empty($item->StockInKgs) ? explode(',', $item->StockInKgs) : [];
        $stkOut     = !empty($item->StockOutKgs) ? explode(',', $item->StockOutKgs) : [];
        $palletNo = !empty($item->RackName) ? explode(',', $item->RackName) : [];
        $shipDates  = !empty($item->ShipmentDate) ? explode('|', $item->ShipmentDate) : [];
        $shipQty    = !empty($item->ShipmentOutKgs) ? explode(',', $item->ShipmentOutKgs) : [];
        //$balance    = !empty($item->BalanceKgs) ? explode('|', $item->BalanceKgs) : [];
        $balance    = !empty($item->BalanceKgs) ? explode(',', $item->BalanceKgs) : [];
        
       
        $maxRows = max(
        count($grades),
        count($outDates),
        count($stkIn),
        count($stkOut),
        count($palletNo), 
        count($shipDates)
        );

   

        for ($i = 0; $i < $maxRows; $i++) {
            $stkInArr = isset($stkIn[$i]) ? explode('|', $stkIn[$i]) : [''];
            $stkOutArr = isset($palletNo[$i]) ? explode('|', $stkOut[$i]) : [''];
            $palletArr = isset($palletNo[$i]) ? explode('|', $palletNo[$i]) : [''];
            $shipDatesArr = isset($shipDates[$i]) ? explode(',', $shipDates[$i]) : [''];
            $outDatesArr = isset($outDates[$i]) ? explode(',', $outDates[$i]) : [''];
            $innerRows = max(1, count($palletArr));

            for ($p = 0; $p < $innerRows; $p++) {
        
                $sheet->setCellValue("A{$row}", $p == 0 && isset($grades[$i]) ? $grades[$i] : '');
                //$sheet->setCellValue("B{$row}",  isset($outDates[$i]) && $outDates[$i] !='-' ? date('d-m-y', strtotime( $outDates[$i])) : '-');
                $sheet->setCellValue("B{$row}",$p == 0 ? (isset($outDatesArr[$p]) && trim($outDates[$p]) != '' && $outDates[$p] !='-' ? date('d-m-y', strtotime( $outDates[$p])) : '') : '');
                //setExcelDate($sheet,"B{$row}", $p == 0 ? (isset($outDates[$i]) ? $outDates[$i] : '') : '');
                // setDecimalCell($sheet,"C{$row}", $p == 0 ? (isset($stkIn[$i]) ? $stkIn[$i] : '') : '');
                // setDecimalCell($sheet,"D{$row}", $p == 0 ? (isset($stkOut[$i]) ? $stkOut[$i] : '') : '');
                $sheet->setCellValue("C{$row}", isset($stkInArr[$p]) ? $stkInArr[$p] : '');
                $sheet->setCellValue("D{$row}", isset($stkOutArr[$p]) ? $stkOutArr[$p] : '');

                $sheet->setCellValue("E{$row}", isset($palletArr[$p]) ? $palletArr[$p] : '');

                $sheet->setCellValue("F{$row}",  isset($shipDatesArr[$p]) && trim($shipDatesArr[$p]) != '' && $shipDatesArr[$p] !='-' ? date('d-m-y', strtotime( $shipDatesArr[$p])) : '-');
                //setExcelDate($sheet,"F{$row}", $p == 0 ? (isset($shipDates[$i]) ? $shipDates[$i] : '') : '');
                setDecimalCell($sheet,"G{$row}", $p == 0 ? (isset($shipQty[$i]) ? $shipQty[$i] : '') : '');
                setDecimalCell($sheet,"H{$row}", $p == 0 ? (isset($balance[$i]) ? $balance[$i] : '') : '');

                $row++;
            }
        }   
        $row++;
    }


    $filename = "BatchTrackingReport.xlsx";
    $filePath = $_SERVER['DOCUMENT_ROOT'] . "/$filename";

    if (file_exists($filePath)) {
    unlink($filePath);
    }

    $writer = new PHPExcel_Writer_Excel2007($excel);
    $writer->save($filePath);

    ob_end_clean();

    if (!file_exists($filePath)) {
        echo json_encode([
        "status" => "error",
        "error" => "Excel file not created"
        ]);
        exit;
    }

    echo json_encode([
        "status" => "Done",
        "filename" => "/download-file.php?file=$filename"
    ]);
    exit;
?>
