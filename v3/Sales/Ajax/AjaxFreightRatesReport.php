<?php 
include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php";
require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed
$FreightFromDate = trim($_POST['fromDate']);
$FreightToDate = trim($_POST['toDate']);
$country = $_POST['country'];//
$CountryIds = $_POST['country'];
if(!empty($_GET['dashboard']) && !empty($_GET['fromDate']) && !empty($_GET['toDate'])){
    $FreightFromDate = trim($_GET['fromDate']);
    $FreightToDate = trim($_GET['toDate']);
    $where = '';
    if(!empty($_GET['liner_id'])){
        $where .= " AND Liner IN(".$_GET['liner_id'].")";
    }
    if(!empty($_GET['port_id'])){
        $where .= " AND (";
        $portQry = '';
        $port_idArr = explode(',',$_GET['port_id']);
        foreach($port_idArr as $id){
            $portQry .= "FIND_IN_SET($id,DestinationPort) > 0 OR ";
        }
        //$portQryTrim = rtrim($portQry,'OR');
        $portQryTrim = substr($portQry, 0, -4);
        $where .= "$portQryTrim)";
    }
    $countryIdsQuery = "WITH RECURSIVE 
                            split_Country AS (
                                SELECT
                                    id,
                                    TRIM(SUBSTRING_INDEX(Country, ',', 1)) AS `Country`,
                                    SUBSTRING(Country, LENGTH(SUBSTRING_INDEX(Country, ',', 1)) + 2) AS remaining
                                FROM ScrmFreightRates
                                WHERE `Country` != 0 
                                $where
                                -- AND `FromDate` <= '$FreightFromDate'  AND `ToDate` >= '$FreightToDate'
                                AND '$FreightToDate' BETWEEN `FromDate` AND `ToDate`
                                UNION ALL

                                SELECT
                                    id,
                                    TRIM(SUBSTRING_INDEX(remaining, ',', 1)),
                                    SUBSTRING(remaining, LENGTH(SUBSTRING_INDEX(remaining, ',', 1)) + 2)
                                FROM split_Country
                                WHERE remaining <> ''
                            )
                            SELECT `Country`
                            FROM split_Country 
                            GROUP BY `Country`";
    //echo '<pre>';echo $countryIdsQuery;die;
    $StmtCountryIdsHeader = $iconn->query($countryIdsQuery);

    $CountryIds = [];
    while ($row = $StmtCountryIdsHeader->fetch_assoc()) {
        $CountryIds[] = $row['Country'];
    }
}
    // echo '<pre>';print_r($CountryIds);die; 

$timestamp = date("YmdHis");
if(!empty($FreightFromDate) && !empty($FreightToDate) && !empty($CountryIds)){
    $strtime = strtotime($FreightFromDate);
    $headerDate = strtoupper(date("F-Y",$strtime));
    $spreadsheet = new PHPExcel();
    $sheet = $spreadsheet->getActiveSheet();
    $borderArr = [
                 'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                ];
    $borderNonArr = [
                 'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_NONE
                        )
                    )
                ];
    $borderArr1 = [
        'borders' => [
            'top' => [
                'style' => PHPExcel_Style_Border::BORDER_THIN
            ],
            'bottom' => [
                'style' => PHPExcel_Style_Border::BORDER_THIN
            ],
            'left' => [
                'style' => PHPExcel_Style_Border::BORDER_THIN
            ]
            // 'bottom' is intentionally excluded
        ]
    ];
    $borderArr2 = [
        'borders' => [
            'top' => [
                'style' => PHPExcel_Style_Border::BORDER_THIN
            ],
            'bottom' => [
                'style' => PHPExcel_Style_Border::BORDER_THIN
            ],
            'right' => [
                'style' => PHPExcel_Style_Border::BORDER_THIN
            ]
            // 'bottom' is intentionally excluded
        ]
    ];
    
    $row = 1;
    $sheet->mergeCells("A$row:L$row")->setCellValue("A$row", $headerDate)->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(25);
    $numRowsMain = 0;
    foreach($CountryIds as $country){
        $where = '';
        if($country){
            $where = " AND  FIND_IN_SET($country,SFR.Country)>0";
        }
        if(!empty($_GET['liner_id'])){
            $where .= " AND SFR.Liner IN(".$_GET['liner_id'].") ";
        }
        if(!empty($_GET['port_id'])){
            $where .= " AND sfrb.DestinationPortID IN(".$_GET['port_id'].") ";
        }
        $headerQuery = "SELECT TRIM(SLM.`LinerName`) LinerName,TRIM(ContractNumber) ContractNumber,
                    CASE WHEN SFR.`RateType`=1  THEN 'CONTRACT' ELSE 'SPOT' END AS `RateType`,
                    CASE WHEN SFR.`RateType`=1  THEN EndDate ELSE ToDate END AS 
                    ToDate
                    FROM `ScrmFreightRates` SFR 
                    LEFT JOIN ScrmLinerMaster SLM ON SFR.`Liner`=SLM.`LinerID`
                    LEFT JOIN LinerContractDetails LCD ON LCD.`LinerContractID`=SFR.`LinerContractId` AND LCD.`StartDate`<=SFR.`FromDate` AND LCD.`EndDate`>=SFR.`ToDate`
                    LEFT JOIN `ScrmFreightRatesBreakUp` sfrb ON sfrb.`ParentID`=SFR.ID
                    RIGHT JOIN ScrmPortCities spc ON sfrb.`DestinationPortID`=spc.`PortId`  AND spc.`Country`= $country
                    WHERE 
                    -- SFR.`FromDate`<='$FreightFromDate' AND SFR.`ToDate`>='$FreightToDate'
                     '$FreightToDate' BETWEEN SFR.`FromDate` AND SFR.`ToDate`
                     $where
                    GROUP BY SLM.`LinerName`,SFR.RateType";
        $stmtHeader = $iconn->query($headerQuery);
        //echo '<pre>';print_r($headerQuery);die;
        $row += 1;
        $hederStaticArr = ['DESTINATION','CONTRACT NO', strtoupper(date("d.m.Y",$strtime))];
        $contract = [];$spot = [];$parentIds = [];
        $numRows = $stmtHeader->num_rows;
        if(!empty($numRows)){                      
            
            $countryHeaderRow = 2;
            if($numRowsMain != 0){
                $countryHeaderRow = $row;
            }
            while($headerRes = $stmtHeader->fetch_assoc()){
                if($headerRes['RateType'] == 'SPOT'){
                    $spot[] = $headerRes;
                }else{
                    $contract[] = $headerRes;
                }
            }
            $linerArr = array_merge($spot,$contract);
            //echo '<pre>';print_r($linerArr);die;
            $hederArr['DESTINATION'] = [];
            $hederArr['CONTRACT NO'] = [];
            $hederArr[strtoupper(date("d.m.Y",$strtime))] = [];
            foreach($linerArr as $liner){
                $hederArr['DESTINATION'][] = trim($liner['LinerName']).' '.$liner['RateType'];///for colore identification purposes append rate type
                $hederArr['CONTRACT NO'][] = $liner['ContractNumber'].' '.$liner['RateType'];
                $ToDate = strtotime($liner['ToDate']);
                if($liner['RateType'] == 'CONTRACT'){
                    $RateType = 'UPTO';
                }else{
                    $RateType = 'SPOT';
                }
                $hederArr[strtoupper(date("d.m.Y",$strtime))][] = "$RateType \r\n".strtoupper(date("d.m.Y",$ToDate)).' '.$liner['RateType'];
            }
            // echo '<pre>';print_r($hederArr);die;
            $i = 1;
            foreach($hederArr as $key=>$valMain){
                $fontColor = 'FF0000';
                $fontSize = 20;
                if($row == 4){
                    $fontSize = 30;
                }
                $sheet->mergeCells("A$row:B$row")->setCellValue("A$row", $key)->getStyle("A$row")->getFont()->setBold(true)->setSize($fontSize);
                $sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->getColumnDimension('A')->setWidth(40);
                $sheet->getStyle("A$row")->applyFromArray($borderArr);
                $sheet->setCellValue("C$row",'');
                $sheet->getColumnDimension('C')->setWidth(5);
                $sheet->getStyle("C$row")->applyFromArray($borderArr);
                $colIndex = 'D';
                $contrctStatus = 0;$spotStatus = 0;
                foreach($valMain as $valChild){
                    if (strpos($valChild, 'SPOT') !== false) {
                        $spotStatus++;
                    }
                    if (strpos($valChild, 'CONTRACT') !== false) {
                        $fontColor = '008000';
                        $contrctStatus++;
                        
                    }
                    /* if( $contrctStatus==1 && $spotStatus>0){
                        $sheet->setCellValue("$colIndex$row",'');
                        $sheet->getColumnDimension($colIndex)->setWidth(2);
                        $colIndex++;
                    } */
                    $rowVal = rtrim(rtrim($valChild,'CONTRACT'),'SPOT');

                    $sheet->setCellValue("$colIndex$row",$rowVal)->getStyle("$colIndex$row")->getFont()->setBold(true)->setSize(18);
                    $sheet->getStyle("$colIndex$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $sheet->getColumnDimension($colIndex)->setWidth(25);
                    if($i != 3){                
                        $sheet->getStyle($colIndex . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => $fontColor], // Optional: black text
                            ],
                            'fill' => [
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'startcolor' => [
                                    'rgb' => 'FFFF00', // Yellow background
                                ],
                            ],
                            'alignment' => [
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            ],                   
                        ]);
                    // $sheet->freezePane($colIndex . $row);
                    }
                    $sheet->getStyle($colIndex . $row)->applyFromArray($borderArr);
                    
                    $colIndex++;
                    //$row++;            
                }
                $i++;
                $row++;
            }
            
            //$sheet->freezePane('A3');//for sticky perticuler column
            $portRateQuery = "SELECT SFR.ID,TRIM(slm.`LinerName`) LinerName,
                                CASE WHEN SFR.`RateType`=1  THEN 'CONTRACT' ELSE 'SPOT' END AS `RateType`,
                                sfrb.`Rate`,TRIM(spc.`Name`) Name,TRIM(`ContryCode`) ContryCode,TRIM(Via) Via
                                ,SFR.`DFD1`,SFR.`DFD2`
                                ,sfrb.`DFD1` BDFD1 ,sfrb.`DFD2` BDFD2,TRIM(Remarks) Remarks
                            FROM ScrmFreightRates SFR
                            LEFT JOIN ScrmLinerMaster slm ON slm.`LinerID`=SFR.`Liner`
                            LEFT JOIN `ScrmFreightRatesBreakUp` sfrb ON sfrb.`ParentID`=SFR.ID
                            RIGHT JOIN ScrmPortCities spc ON sfrb.`DestinationPortID`=spc.`PortId`  AND spc.`Country`=$country
                            WHERE 
                            -- SFR.`FromDate`<='$FreightFromDate' AND SFR.`ToDate`>='$FreightToDate'
                             '$FreightToDate' BETWEEN SFR.`FromDate` AND SFR.`ToDate`
                             $where";
                                // echo '<pre>';print_r($portRateQuery);die;

            $stmtPortRate = $iconn->query($portRateQuery);
            //$row += 1;
            $portRates = $OFD = $DFD1 = $DFD2 = $BDFD1 = $BDFD1 = $PlugInFD = $THCAndDC = $Genset = [];
            while($PortRatesRes = $stmtPortRate->fetch_assoc()){
                $PortName = $PortRatesRes['Name'];
                $PortCode = $PortRatesRes['ContryCode'];
                $LinerName = $PortRatesRes['LinerName'];
                $Rate = $PortRatesRes['Rate'];
                $RateType = $PortRatesRes['RateType'];
                $Via = $PortRatesRes['Via'];
                $portKeyName =  ltrim(rtrim($PortName)).' '.rtrim(ltrim($Via));
                $portKey = str_replace([" ",",", ".", "(", ")", "[", "]", "{", "}","-"], "_", $portKeyName);
                if(!empty($PortName)){
                    if (!isset($portRates[$portKey])){
                        $portRates[$portKey] = [
                            'PortCode'=>$PortCode,
                            'RateType'=>$RateType,
                            //'Via'=>$Via,
                            'port'=>$portKeyName,
                            /* 'DFD1'=>$PortRatesRes['DFD1'],
                            'DFD2'=>$PortRatesRes['DFD2'], */
                            //'BDFD1'=>[]
                            ];
                    }
                    $portRates[$portKey][trim($LinerName).' '.$RateType] = $Rate;
                    $portRates[$portKey]['DFD1'][trim($LinerName).' '.$RateType] = $PortRatesRes['DFD1'];
                    $portRates[$portKey]['DFD2'][trim($LinerName).' '.$RateType] = $PortRatesRes['DFD2'];
                    $portRates[$portKey]['BDFD1'][trim($LinerName).' '.$RateType] = $PortRatesRes['BDFD1'];
                    $portRates[$portKey]['BDFD2'][trim($LinerName).' '.$RateType] = $PortRatesRes['BDFD2'];
                    $portRates[$portKey]['Remarks'][trim($LinerName).' '.$RateType] = $PortRatesRes['Remarks'];
                }
            }
            $contrctStatus = 0;
            // echo '<pre>';print_r($portRates);die;
            /*Port break down starts*/
            if(!empty($portRates)){
                foreach($portRates as $k=>$linerRates){//echo '<pre>';print_r($linerRates['DFD1']);die;
                    $RateType = $linerRates['RateType'];
                    /* $DFD1 = $linerRates['DFD1'];
                    $BDFD1 = $linerRates['BDFD1']; */
                    $sheet->setCellValue("A$row",$linerRates['port'])->getStyle("A$row")->getFont()->setBold(true)->setSize(20);
                    $sheet->getStyle('A' . $row)->applyFromArray($borderArr);
                    $sheet->setCellValue("B$row",$linerRates['PortCode'])->getStyle("B$row")->getFont()->setBold(true)->setSize(20);
                    $sheet->getStyle('B' . $row)->applyFromArray($borderArr2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    // $colIndex = 'D';                        
                    foreach($linerRates as $rateKey=>$rates){                
                        /* if($linerRates['RateType'] == 'CONTRACT'){                   
                            $contrctStatus++;                
                        }
                        if( $contrctStatus==1){
                            $colIndex++;
                        } */
                        for($J='D';$J<$colIndex;$J++){ //echo $j;die;
                            $cell = $sheet->getCell($J.$countryHeaderRow);
                            // $cell = $sheet->getCell('J2');
                            $style = $sheet->getStyle($cell->getCoordinate());
                            $font = $style->getFont();
                            $fontColr = $font->getColor()->getRGB();
                            $linerKey = trim($sheet->getCell($J.$countryHeaderRow)->getValue());//key like 'MSC LINE CONTRACT'
                            //echo '<pre>';print_r($linerRates);die;
                            if (!empty($fontColr) && (string)$fontColr == '008000') {
                                $linerKey = $linerKey.' CONTRACT';
                            } else {
                                $linerKey = $linerKey.' SPOT';
                            }
                            /*color specification start based on dfd1 and dfd2 checking with breakup dfds*/ 
                            if(!empty($linerRates['DFD2'][$linerKey]) && !empty($linerRates['BDFD2'][ $linerKey])){
                                $DFDS = $linerRates['DFD1'][$linerKey].'+'.$linerRates['DFD2'][$linerKey];
                                $bDFDS = $linerRates['BDFD1'][ $linerKey].'+'.$linerRates['BDFD2'][ $linerKey];
                            }else{
                                $DFDS = $linerRates['DFD1'][$linerKey];
                                $bDFDS = $linerRates['BDFD1'][ $linerKey];
                            }
                            /*color specification ends based on dfd1 and dfd2 checking with breakup dfds*/ 
                            $fontColour = '000000';
                            if($DFDS == $bDFDS) {
                                $fontColour = 'FF0000';
                            }  
                            $sheet->setCellValue("$J$row",$linerRates[$linerKey])->getStyle("$J$row")->getFont()->setBold(true)->setSize(18)->getColor()->setRGB($fontColour);
                            $sheet->getStyle($J . $row)->applyFromArray($borderArr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        // $colIndex++;
                        }
                    }
                    $row++;
                }
            }
            //$row = $row-1;
            $freightRatesSql = "SELECT SFR.ID,TRIM(slm.`LinerName`) LinerName,SFR.Combined,
                                CASE WHEN SFR.`RateType`=1  THEN 'CONTRACT' ELSE 'SPOT' END AS `RateType`
                                ,SFR.`PlugInFD`,SFR.`DFD1`,SFR.`DFD2`,SFR.`THCAndDC`,SFR.`Genset`
                                ,GROUP_CONCAT(sfrb.`DFD1`) BDFD1 ,GROUP_CONCAT(sfrb.`DFD2`) BDFD2,OFD
                                FROM ScrmFreightRates SFR
                                LEFT JOIN ScrmLinerMaster slm ON slm.`LinerID`=SFR.`Liner`
                                LEFT JOIN `ScrmFreightRatesBreakUp` sfrb ON sfrb.`ParentID`=SFR.ID
                                RIGHT JOIN ScrmPortCities spc ON sfrb.`DestinationPortID`=spc.`PortId`  AND spc.`Country`=$country
                                WHERE 
                                -- SFR.`FromDate`<='$FreightFromDate' AND SFR.`ToDate`>='$FreightToDate' 
                                 '$FreightToDate' BETWEEN SFR.`FromDate` AND SFR.`ToDate`
                                $where
                                GROUP BY SFR.ID";
                                //echo '<pre>';print_r($freightRatesSql);die;
            $stmtFreightRates = $iconn->query($freightRatesSql);
            /* if (!$stmtFreightRates) {
                echo "SQL Error: " . $iconn->error;die;
            } */
            while($FreightRatesRes = $stmtFreightRates->fetch_assoc()){
                $LinerName = trim($FreightRatesRes['LinerName']);
                $RateType = $FreightRatesRes['RateType'];
                $Combined = $FreightRatesRes['Combined'];
                $CombinedArr['Combined'][$LinerName.' '.$RateType] = $Combined;
                $OFD['OFD'][$LinerName.' '.$RateType] = $FreightRatesRes['OFD'];
                $DFD1['DFD1'][$LinerName.' '.$RateType] = $FreightRatesRes['DFD1'];
                $DFD2['DFD2'][$LinerName.' '.$RateType] = ($Combined!=1)?$FreightRatesRes['DFD2']:'';
                $BDFD1['BDFD1'][$LinerName.' '.$RateType] = $FreightRatesRes['BDFD1'];
                $BDFD2['BDFD2'][$LinerName.' '.$RateType] = ($Combined!=1)?$FreightRatesRes['BDFD2']:'';
                $PlugInFD['PlugInFD'][$LinerName.' '.$RateType] = $FreightRatesRes['PlugInFD'];
                $THCAndDC['THCAndDC'][$LinerName.' '.$RateType] = $FreightRatesRes['THCAndDC'];
                $Genset['Genset'][$LinerName.' '.$RateType] = $FreightRatesRes['Genset'];
            }
            //echo '<pre>';print_r($THCAndDC);die;
            if(!empty($OFD)){
                $rowOfd = 0;
                foreach($OFD as $key=>$OFDValArr){ 
                    if(isset($OFDValArr[$linerKey])){
                        $sheet->mergeCells("A$row:B$row")->setCellValue("A$row",$key)->getStyle("A$row")->getFont()->setBold(true)->setSize(25);
                        $sheet->getStyle('A' . $row)->applyFromArray($borderArr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);;
                        foreach($OFDValArr as $OFDKey=>$OFDVal){
                            for($J='D';$J<$colIndex;$J++){
                                $cell = $sheet->getCell($J.$countryHeaderRow);
                                $style = $sheet->getStyle($cell->getCoordinate());
                                $font = $style->getFont();
                                $fontColr = $font->getColor()->getRGB();
                                $linerKey = trim($sheet->getCell($J.$countryHeaderRow)->getValue());//key like 'MSC LINE CONTRACT'
                                if (!empty($fontColr) && (string)$fontColr == '008000') {
                                    $linerKey = $linerKey.' CONTRACT';
                                } else {
                                    $linerKey = $linerKey.' SPOT';
                                }
                                $sheet->setCellValue("$J$row",$OFDValArr[$linerKey])->getStyle("$J$row")->getFont()->setBold(true)->setSize(18);
                                $sheet->getStyle($J . $row)->applyFromArray($borderArr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            }
                        }
                        $rowOfd = 1;
                    }                
                }
                $row+=$rowOfd;
            }
            if(!empty($DFD1)){
                foreach($DFD1 as $key=>$DFD1ValArr){ 
                    $sheet->mergeCells("A$row:B$row")->setCellValue("A$row",'DFD')->getStyle("A$row")->getFont()->setBold(true)->setSize(25);
                    $sheet->getStyle('A' . $row)->applyFromArray($borderArr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);;
                    foreach($DFD1ValArr as $DFD1Key=>$DFD1Val){
                        for($J='D';$J<$colIndex;$J++){
                            $cell = $sheet->getCell($J.$countryHeaderRow);
                            $style = $sheet->getStyle($cell->getCoordinate());
                            $font = $style->getFont();
                            $fontColr = $font->getColor()->getRGB();
                            $linerKey = trim($sheet->getCell($J.$countryHeaderRow)->getValue());//key like 'MSC LINE CONTRACT'
                            if (!empty($fontColr) && (string)$fontColr == '008000') {
                                $linerKey = $linerKey.' CONTRACT';
                            } else {
                                $linerKey = $linerKey.' SPOT';
                            }
                            if(trim($sheet->getCell($J.$countryHeaderRow)->getValue())){
                                $BDFD1Arr1 = $BDFD2Arr1 = [];
                                $DFD1S = $DFD1ValArr[$linerKey];
                                $DFD1A = [$DFD1S];
                                $BDFD1Arr1 = !empty($BDFD1['BDFD1'][$linerKey])?explode(',',$BDFD1['BDFD1'][$linerKey]):[];  
                                $isComibined = !empty($CombinedArr['Combined'][$linerKey])?'COM':'';
                                if(!$isComibined){
                                    if(!empty($DFD2['DFD2'][$linerKey])){
                                        $DFD1S = $DFD1ValArr[$linerKey].'+'.$DFD2['DFD2'][$linerKey];
                                        $DFD2A = [$DFD1S];
                                        $BDFD2Arr1 = !empty($BDFD2['BDFD2'][$linerKey])?explode(',',$BDFD2['BDFD2'][$linerKey]):[]; 
                                    }
                                    $isComibined = 'FREE'; 
                                }              
                                        
                                if(!empty($BDFD1Arr1) && !empty($BDFD2Arr1)){
                                    $df1df2 = [];
                                    for($df1=0;$df1<count($BDFD1Arr1);$df1++){
                                        $df1df2[] = $BDFD1Arr1[$df1].'+'.$BDFD2Arr1[$df1];// for generating '4+5 FREE' format
                                    }
                                    $DFDArr = array_diff(array_unique($df1df2),$DFD2A);
                                    $DFDS = implode('/',$DFDArr);
                                }else{
                                    $DFDArr = array_diff(array_unique($BDFD1Arr1),$DFD1A);     
                                    $DFDS = implode('/',$DFDArr); 
                                }  
                                if(!empty($DFDArr)){
                                    /*Color specification starts if dfd1 and breakup dfd1 is different*/
                                    $richText = new PHPExcel_RichText();     
                                                                
                                    // Red text: "10"
                                    $redText = $richText->createTextRun($DFD1S);
                                    $redText->getFont()->setBold(true)->setSize(20);
                                    $redText->getFont()->getColor()->setRGB('FF0000'); // Red

                                    // Add normal slash
                                    $richText->createText(' / ');
                                    // Black bold text: "7"
                                    $blackText = $richText->createTextRun($DFDS.' '.$isComibined);
                                    $blackText->getFont()->setBold(true)->setSize(20);
                                    $blackText->getFont()->getColor()->setRGB('000000'); // Black
                                    /* for($d=0;$d<count($DFDArr);$d++){

                                    } */
                                    /* $richText->createText(' / ');
                                    $greenText = $richText->createTextRun(222);
                                    $greenText->getFont()->setBold(true)->setSize(20);
                                    $greenText->getFont()->getColor()->setRGB('00FF00'); // green */
                                    // Set rich text into a cell
                                    $sheet->getCell("$J$row")->setValue($richText)->getStyle("$J$row")->getFont()->setBold(true)->setSize(20);
                                    /*Color specification ends if dfd1 and breakup dfd1 is different*/
                                }else{
                                    $dfdVal = '';
                                    if(!empty($DFD2['DFD2'][$linerKey])){
                                        $dfdVal = '+'.$DFD2['DFD2'][$linerKey];//if did1 and dfd2 is different then convert 4+5 FREE
                                    }
                                    $sheet->setCellValue("$J$row",$DFD1ValArr[$linerKey].$dfdVal.' '.$isComibined)->getStyle("$J$row")->getFont()->setBold(true)->setSize(18)->getColor()->setRGB('FF0000');
                                }  
                            }               
                            $sheet->getStyle($J . $row)->applyFromArray($borderArr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        }
                    }
                }
                $row++;
            }
            
            //echo '<pre>';print_r($PlugInFD);die;
            if(!empty($PlugInFD)){
                $rowPluginFd = 0;
                foreach($PlugInFD as $key=>$PlugInFDValArr){ 
                    if(isset($PlugInFDValArr[$linerKey])){
                        $sheet->mergeCells("A$row:B$row")->setCellValue("A$row",$key)->getStyle("A$row")->getFont()->setBold(true)->setSize(25);
                        $sheet->getStyle('A' . $row)->applyFromArray($borderArr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);;
                        foreach($PlugInFDValArr as $PlugInFDKey=>$PlugInFDVal){
                            for($J='D';$J<$colIndex;$J++){
                                $cell = $sheet->getCell($J.$countryHeaderRow);
                                $style = $sheet->getStyle($cell->getCoordinate());
                                $font = $style->getFont();
                                $fontColr = $font->getColor()->getRGB();
                                $linerKey = trim($sheet->getCell($J.$countryHeaderRow)->getValue());//key like 'MSC LINE CONTRACT'
                                if (!empty($fontColr) && (string)$fontColr == '008000') {
                                    $linerKey = $linerKey.' CONTRACT';
                                } else {
                                    $linerKey = $linerKey.' SPOT';
                                }
                                $sheet->setCellValue("$J$row",$PlugInFDValArr[$linerKey])->getStyle("$J$row")->getFont()->setBold(true)->setSize(18);
                                $sheet->getStyle($J . $row)->applyFromArray($borderArr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            }
                        }
                        $rowPluginFd = 1;
                    }                
                }
                $row += $rowPluginFd;
            }
            if(!empty($THCAndDC)){
                $rowTHCAndDC = 0;
                foreach($THCAndDC as $key=>$THCAndDCArr){ 
                    if(isset($THCAndDCArr[$linerKey])){
                        $sheet->mergeCells("A$row:B$row")->setCellValue("A$row",$key)->getStyle("A$row")->getFont()->setBold(true)->setSize(25);
                        $sheet->getStyle('A' . $row)->applyFromArray($borderArr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);;
                        foreach($THCAndDCArr as $THCAndDCKey=>$THCAndDCVal){
                            for($J='D';$J<$colIndex;$J++){
                                $cell = $sheet->getCell($J.$countryHeaderRow);
                                $style = $sheet->getStyle($cell->getCoordinate());
                                $font = $style->getFont();
                                $fontColr = $font->getColor()->getRGB();
                                $linerKey = trim($sheet->getCell($J.$countryHeaderRow)->getValue());//key like 'MSC LINE CONTRACT'
                                if (!empty($fontColr) && (string)$fontColr == '008000') {
                                    $linerKey = $linerKey.' CONTRACT';
                                } else {
                                    $linerKey = $linerKey.' SPOT';
                                }                            
                                $sheet->setCellValue("$J$row",$THCAndDCArr[$linerKey])->getStyle("$J$row")->getFont()->setBold(true)->setSize(18);
                                $sheet->getStyle($J . $row)->applyFromArray($borderArr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            }
                        }
                        $rowTHCAndDC = 1;
                    }                
                }
                $row+=$rowTHCAndDC;
            }
            if(!empty($Genset)){
                $rowGenset = 0;
                foreach($Genset as $key=>$GensetValArr){ 
                    if(isset($GensetValArr[$linerKey])){
                        $sheet->mergeCells("A$row:B$row")->setCellValue("A$row",$key)->getStyle("A$row")->getFont()->setBold(true)->setSize(25);
                        $sheet->getStyle('A' . $row)->applyFromArray($borderArr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);;
                        foreach($GensetValArr as $GensetKey=>$GensetVal){
                            for($J='D';$J<$colIndex;$J++){
                                $cell = $sheet->getCell($J.$countryHeaderRow);
                                $style = $sheet->getStyle($cell->getCoordinate());
                                $font = $style->getFont();
                                $fontColr = $font->getColor()->getRGB();
                                $linerKey = trim($sheet->getCell($J.$countryHeaderRow)->getValue());//key like 'MSC LINE CONTRACT'
                                if (!empty($fontColr) && (string)$fontColr == '008000') {
                                    $linerKey = $linerKey.' CONTRACT';
                                } else {
                                    $linerKey = $linerKey.' SPOT';
                                }
                                $sheet->setCellValue("$J$row",$GensetValArr[$linerKey])->getStyle("$J$row")->getFont()->setBold(true)->setSize(18);
                                $sheet->getStyle($J . $row)->applyFromArray($borderArr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            }
                        }
                        $rowGenset = 1;
                    }                
                }
                $row+=$rowGenset;
            }
            $maxColumn = $sheet->getHighestColumn();
            /*Remarks satrt*/
            if(!empty($portRates)){
                $RemarksRow = $row;
            // $sheet->mergeCells("A$RemarksRow:$maxColumn$RemarksRow")->setCellValue("A$RemarksRow",'Remarks')->getStyle("A$RemarksRow")->getFont()->setBold(true)->setSize(36);
            // $sheet->getStyle('A' . $RemarksRow)->applyFromArray($borderArr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);;
            /*Remarks heading display if remerks present any liner*/ 
                $row++;$remarksStatusMain = 0;
                foreach($portRates as $k=>$linerRates){
                    $remarksStatus = 0;
                    //echo '<pre>';print_r($linerRates);die;                
                    foreach($linerRates['Remarks'] as $RemarksKey=>$RemarksVal){  
                        for($J='D';$J<$colIndex;$J++){ //echo $j;die;
                            $cell = $sheet->getCell($J.$countryHeaderRow);
                            // $cell = $sheet->getCell('J2');
                            $style = $sheet->getStyle($cell->getCoordinate());
                            $font = $style->getFont();
                            $fontColr = $font->getColor()->getRGB();
                            $linerKey = trim($sheet->getCell($J.$countryHeaderRow)->getValue());//key like 'MSC LINE CONTRACT'
                            if (!empty($fontColr) && (string)$fontColr == '008000') {
                                $linerKey = $linerKey.' CONTRACT';
                            } else {
                                $linerKey = $linerKey.' SPOT';
                            }
                            if(!empty($linerRates['Remarks'][$linerKey])){
                                $remarksStatus = 1;$remarksStatusMain = 1;
                            }
                            $sheet->setCellValue("$J$row",$linerRates['Remarks'][$linerKey])->getStyle("$J$row")->getFont()->setBold(false)->setSize(12);
                            $sheet->getStyle($J . $row)->applyFromArray($borderArr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);                       
                        }
                    }
                    if(!empty($remarksStatus)){
                        $sheet->setCellValue("A$row",$linerRates['port'])->getStyle("A$row")->getFont()->setBold(true)->setSize(20);
                        $sheet->getStyle('A' . $row)->applyFromArray($borderArr);
                        $sheet->setCellValue("B$row",$linerRates['PortCode'])->getStyle("B$row")->getFont()->setBold(true)->setSize(20);
                        $sheet->getStyle('B' . $row)->applyFromArray($borderArr2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $row++;
                    }
                }
                if(!empty($remarksStatusMain)){
                    $sheet->mergeCells("A$RemarksRow:$maxColumn$RemarksRow")->setCellValue("A$RemarksRow",'Remarks')->getStyle("A$RemarksRow")->getFont()->setBold(true)->setSize(25);
                    $sheet->getStyle('A' . $RemarksRow)->applyFromArray($borderArr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);;
                }
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();
                $range = "A$row:" . $highestCol . $highestRow;
                //$sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle($borderNonArr);
                $sheet->getStyle($range)->applyFromArray($borderNonArr);
            } 
           // $row += 1;
            $numRowsMain++;
        }
    }
    

    if($numRowsMain != 0){
        ob_clean();
        // Write to Output
        $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
        $filePath = $_SERVER['DOCUMENT_ROOT'] . "/Freight_Rates_Report" . $timestamp . ".xlsx";
        // $filePath = $_SERVER['DOCUMENT_ROOT'] . "/Freight_Rates_Report.xlsx";
        $writer->save($filePath);
        //$data['filename'] = $host. "download-file.php?file="."Treatment_Register_Report" . $timestamp . ".xlsx";
        if(!empty($_GET['dashboard']) && file_exists($filePath)){
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            // Read the file and send it to the client
            readfile($filePath);
            // After sending the file, delete it
            unlink($filePath);
            exit;
        }else{            
            $data['filename'] =  "/download-file.php?file=" . "Freight_Rates_Report" . $timestamp . ".xlsx";
            $data['status'] = 'Done';
            echo json_encode($data);die;
        }
    }else{
        if(!empty($_GET['dashboard'])){
            echo 'Please choose a valid time period.';die;
        }else{
            $data['status'] = 'Filed';
            $data['msg'] = 'Please choose a valid time period.';
            echo json_encode($data);die;
        }
    }
}else{
     if(!empty($_GET['dashboard'])){
        echo 'Required Parametrs Missed, Please check Once.';die;
    }else{
        $data['status'] = 'Filed';
        $data['msg'] = 'Required Parametrs Missed, Please check Once.';
        echo json_encode($data);die;
    }    
}