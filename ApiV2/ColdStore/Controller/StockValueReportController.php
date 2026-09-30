<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';
class StockValueReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $VarietyOrderBy;
    private $PackingStyleOrderBy;
    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
        $this->PackingStyleOrderBy = " CASE
                WHEN APS.PackingStyle LIKE  '%KGS%' THEN 1
                WHEN APS.PackingStyle LIKE  '%OZ%' THEN 2
                ELSE  3 END ";
        $this->VarietyOrderBy = " ORDER BY
                                    CASE
                                    WHEN TypeProduct = 'RAW' THEN 1
                                    WHEN TypeProduct = 'COOKED' THEN 2
                                    ELSE 3 END,
                                    CASE
                                    WHEN  ShortName = 'HLSO' THEN 1
                                    WHEN  ShortName = 'PVPD' THEN 2
                                    WHEN ShortName = 'PDTO' THEN 3
                                    WHEN ShortName = 'PD' THEN 4
                                    WHEN  ShortName = 'EZPL' THEN 5
                                    ELSE 6 END,
                                    CASE
                                    WHEN  Treatment = 'UNT' THEN 1
                                    WHEN  Treatment = 'NPT' THEN 2
                                    WHEN Treatment = 'SALT' THEN 3
                                    WHEN Treatment = 'STPP' THEN 4
                                    ELSE 5 END ";
    }
    public function getStockReportData($RequestBody)
    {
        $ProductionDate = $RequestBody['ProductionDate'];
        $ColdstoreID = $RequestBody['ColdstoreID'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $Type = $RequestBody['Type'];//mcs ot kgs
        $FreezingType = $RequestBody['FreezingType'];
        $Variety = $RequestBody['Variety'];
        $Treatment = $RequestBody['Treatment'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $Grade = $RequestBody['Grade'];
        $Species  = $RequestBody['Species'];
        $PlantID  = $RequestBody['PlantID'];
        $StockBlgsTo  = $RequestBody['StockBlgsTo'];
        $Query = '';
        $QueryTwo = '';
        if ($ColdstoreID != '') {
            $Query .= " AND CPID.UnitID = '$ColdstoreID'";
        }
        if ($PlantID != '') {
            $Query .= " AND CPID.PlantID = '$PlantID'";
        }
        if ($StockBlgsTo != '') {
            $Query .= " AND CPID.StockHolderID = '$StockBlgsTo'";
        }
        // if ($ProductionDate != '') {
        //     $Query .= " AND CPID.ProductionDate <= '$ProductionDate'";
        //     // $Query .= " AND CPID.ProductionDate = '$ProductionDate'";
        // }
        if ($FreezingType != '') {
            $Query .= " AND PIVL.FreezingType = '$FreezingType'";
        }
        // if ($Variety != '') {
        //     $Query .= " AND PIVL.ShortName = '$Variety'";
        // }
        // if ($Treatment != '') {
        //     $Query .= " AND CPID.SockingType = '$Treatment'";
        // }
        if ($PackingStyle != '') {
            $Query .= " AND CPID.PackingStyleID = '$PackingStyle'";
        }
        if ($Grade != '') {
            $Query .= " AND CPID.Grade = '$Grade'";
        }
        if ($Species != '') {
            $Query .= " AND PIVL.Species = '$Species'";
        }
        if ($TypeProduct != '') {
            $QueryTwo .= " AND TypeProduct = '$TypeProduct'";
        }
        if ($Variety != '') {
            $QueryTwo .= " AND ShortName = '$Variety'";
        }
        if ($PackingStyle != '') {
            $QueryTwo .= " AND PackingStyle = '$PackingStyle'";
        }
        $QueryReplace =  $QueryTwo;
        $StockOutDate = date('Y-m-d', strtotime($ProductionDate . '-1 day'));
        if ($Treatment != '') {
            $QueryTwo .= " AND SockingType = '$Treatment'";
            $QueryReplace = str_replace(" AND SockingType = '$Treatment'", '', $QueryTwo);
        }
        //echo '<pre>';print_r($CoversionsData);die;
        // $sqlType = "SELECT DISTINCT TypeProduct FROM ScrmPIVarietyList WHERE 1=1 $QueryTwo ORDER BY TypeProduct ASC";
        $sqlType = "SELECT TypeProduct, ShortName, Treatment FROM ScrmPIVarietyList WHERE 1=1 $QueryReplace ";
        if ($Treatment != '') {
            $sqlType .= " AND Treatment = '$Treatment' ";
        }
        $sqlType .= "
                    GROUP BY TypeProduct, ShortName, Treatment
                    -- ORDER BY TypeProduct ASC
                    $this->VarietyOrderBy";
        $stmtType = $this->conn->prepare($sqlType);
        $stmtType->execute();
        //echo '<pre>';print_r($sqlType);die;
        $ArrayTypeData = [];
        while ($rowType = $stmtType->fetch(PDO::FETCH_ASSOC)) {
            $TypeProductRow = $rowType['TypeProduct'];
            $ShortNameRow = $rowType['ShortName'];
            $SockingTypeRow = $rowType['Treatment'];
            $VarietyCombinationData = $this->getVarietyCombinationData($Query, $TypeProductRow, $ProductionDate, $ShortNameRow, $SockingTypeRow);
            $GradesData = $this->getGradeNames($Query, $TypeProductRow, $Type, $ProductionDate, $ShortNameRow, $SockingTypeRow, $VarietyCombinationData);
            $GrandTotals = $this->getVarietywiseTotals($Type, $TypeProductRow, $Query, $ProductionDate, $ShortNameRow, $SockingTypeRow, $VarietyCombinationData);
            $FreezingData = $this->getVarietywiseFreezingData($Type, $TypeProductRow, $Query, $ProductionDate, $ShortNameRow, $SockingTypeRow,$ColdstoreID, '', $VarietyCombinationData);
            if ($GradesData && $VarietyCombinationData && $GrandTotals) {
                $ArrayTypeList['TypeProduct'] = $TypeProductRow . ' ' . $ShortNameRow . ' ' . $SockingTypeRow;
                $ArrayTypeList['ShortName'] = $ShortNameRow;
                $ArrayTypeList['Treatment'] = $SockingTypeRow;
                $ArrayTypeList['VarietyCombinationData'] = $VarietyCombinationData;
                $ArrayTypeList['GradesData'] = $GradesData;
                $ArrayTypeList['GrandTotals'] = $GrandTotals;
                $ArrayTypeList['FreezingData'] = $FreezingData;
                $ArrayTypeData[] = $ArrayTypeList;
            }
        }
      //  $GrandTotals = $this->getGrandTotals($RequestBody);
       // ECHO '<PRE>';print_r($GrandTotals);die;
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['Data'] = $ArrayTypeData;
       // $Data['GrandTotals'] = $GrandTotals;
        $Data['ExchangeRate'] = $this->getLatestExchangeRate();
        return $Data;
    }
    public function getGradeNamesBk($Query, $TypeProduct, $Type, $ProductionDate, $ShortNameRow, $SockingType, $Combinations = null) //
    {
        $sqlGrades = "SELECT DISTINCT CPID.Grade AS GradeName FROM `CsStockData` AS CPID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID=PIVL.VarietyID
        -- LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CPID.ProductionDate <= '$ProductionDate' $Query AND PIVL.TypeProduct = '$TypeProduct'
        AND PIVL.ShortName = '$ShortNameRow' AND CPID.SockingType = '$SockingType'
        ORDER BY abs(CPID.Grade) ASC";
        // WHERE CBS.BarCodeStatus=1 AND (AvalMcs!=0 OR LooseBags!=0) $Query AND PIVL.TypeProduct = '$TypeProduct'
        $stmtGrades = $this->conn->prepare($sqlGrades);
        $stmtGrades->execute();
        if ($Combinations === null) {
            $Combinations = $this->getVarietyCombinationData($Query, $TypeProduct, $ProductionDate, $ShortNameRow, $SockingType);
        }
        $gradeOrder = [];
        $gradeRows = [];
        while ($dataGrade = $stmtGrades->fetch(PDO::FETCH_ASSOC)) {
            $gradeName = $dataGrade['GradeName'];
            $gradeOrder[] = $gradeName;
            $gradeRows[$gradeName] = [
                'Grade' => $gradeName,
                'GradeValueData' => []
            ];
        }
        foreach ($Combinations as $Item) {
            $PackingStyle = $Item['PackingStyle'];
            $ShortName = $Item['ShortName'];
            $Species = $Item['Species'];
            $SockingType = $Item['SockingType'];
            $Glaze = $Item['Glaze'];

            $TotalSlabs = $Item['TotalSlabs'];
            $SlabWeight = $Item['SlabWeight'];           

            $CombinationQuery = " AND APS.PackingStyle = '$PackingStyle' AND PIVL.ShortName ='$ShortName' AND
            CPID.SockingType = '$SockingType' AND PIVL.Species = '$Species' AND PIVL.Glaze = '$Glaze'";
            $inByGrade = [];
            $outByGrade = [];
            $sqlInFields = "CPID.Grade AS GradeName, SUM(ActualMcs) AS AvalMcs,
            -- SUM(ActualLooseBags) AS LooseBags, SUM(ActualKgs) AS AvalKgs,
            FLOOR(SUM(ActualLooseBags)/TotalSlabs) AS LooseMcs,CPID.Grade,PIVL.VarietyID,CPID.PackingStyleID";
            $sqlInSelect = "SELECT $sqlInFields FROM `CsStockData` as CPID
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            -- LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
            WHERE CPID.ProductionDate <= '$ProductionDate' $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query
            GROUP BY CPID.Grade";
            //echo '<pre>';print_r($sqlInSelect);die;
            $stmtInSelect = $this->conn->prepare($sqlInSelect);
            $stmtInSelect->execute();
            while ($rowInValue = $stmtInSelect->fetch(PDO::FETCH_ASSOC)) {                
                $unitPrice = $this->GetLatestPriceByVariety($rowInValue);

                $inByGrade[$rowInValue['GradeName']] = [
                    'Mcs' => (($rowInValue['AvalMcs'] ? $rowInValue['AvalMcs'] : 0) + ($rowInValue['LooseMcs'] ? $rowInValue['LooseMcs'] : 0)),
                    'Kgs' => ($rowInValue['AvalKgs'] ? $rowInValue['AvalKgs'] : 0),
                    'unitPrice' => ($unitPrice ? $unitPrice : 0)
                ];
            }
            $sqlOutFields = "CPID.Grade AS GradeName, SUM(OutMcs) as OutMcs, 
            -- SUM(OutLooseBags) AS OutLooseBags,SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS OutKGs, SUM(OutLooseBags*SlabWeight*MFactor) AS OutLKGs,
            FLOOR(SUM(OutLooseBags)/TotalSlabs) AS OutLooseMcs";
            $sqlOutSelect = "SELECT $sqlOutFields FROM CsStockOutDetails AS CSOD
            LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            -- LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
            WHERE CSOD.OutDate <= '$ProductionDate' $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query
            GROUP BY CPID.Grade";
            $stmtOutSelect = $this->conn->prepare($sqlOutSelect);
            $stmtOutSelect->execute();
            while ($rowOutValue = $stmtOutSelect->fetch(PDO::FETCH_ASSOC)) {
                $outByGrade[$rowOutValue['GradeName']] = [
                    'Mcs' => (($rowOutValue['OutMcs'] ? $rowOutValue['OutMcs'] : 0) + ($rowOutValue['OutLooseMcs'] ? $rowOutValue['OutLooseMcs'] : 0)),
                    'Kgs' => (($rowOutValue['OutKGs'] ? $rowOutValue['OutKGs'] : 0) + ($rowOutValue['OutLKGs'] ? $rowOutValue['OutLKGs'] : 0))
                ];
            }
            foreach ($gradeOrder as $gradeName) {
                $inMcs = isset($inByGrade[$gradeName]['Mcs']) ? $inByGrade[$gradeName]['Mcs'] : 0;
                $outMcs = isset($outByGrade[$gradeName]['Mcs']) ? $outByGrade[$gradeName]['Mcs'] : 0;
                $inKgs = isset($inByGrade[$gradeName]['Kgs']) ? $inByGrade[$gradeName]['Kgs'] : 0;
                $outKgs = isset($outByGrade[$gradeName]['Kgs']) ? $outByGrade[$gradeName]['Kgs'] : 0;
                $Mcs = intval($inMcs - $outMcs);
                $inkgsR = !empty($Mcs) ? $Mcs * $TotalSlabs * $SlabWeight : 0;

                $unitPrice = isset($inByGrade[$gradeName]['unitPrice']) ? $inByGrade[$gradeName]['unitPrice'] : 0;

                $inUsd = $unitPrice * $inkgsR;
                $kgs = 0;
                if(($inMcs - $outMcs) != 0){
                   // $kgs = ($inKgs - $outKgs);
                }
                $kgs = ($inKgs - $outKgs);

                $gradeRows[$gradeName]['GradeValueData'][] = [ 
                                                                'MCS' => intval($inMcs - $outMcs), 
                                                                'INKGS' => round($inkgsR, 3), 
                                                                'RATE_PER_KGS' => $unitPrice, 
                                                                'ValUSD' => round($inUsd, 3)
                                                            ];
            }
        }
        $GradesData = [];
        foreach ($gradeOrder as $gradeName) {
            if (empty($gradeRows[$gradeName]['GradeValueData'])) {
                $gradeRows[$gradeName]['GradeValueData'][] = ['MCS' => '0','INKGS' => '0','RATE_PER_KGS' => '0','ValUSD' => '0'];
            }
            $GradesData[] = $gradeRows[$gradeName];
        }
        return $GradesData;
    }
    public function getGradeNames($Query, $TypeProduct, $Type, $ProductionDate, $ShortNameRow, $SockingType, $Combinations = null) //
    {
        $sqlGrades = "SELECT DISTINCT CPID.Grade AS GradeName FROM `CsStockData` AS CPID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID=PIVL.VarietyID
        -- LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CPID.ProductionDate <= '$ProductionDate' $Query AND PIVL.TypeProduct = '$TypeProduct'
        AND PIVL.ShortName = '$ShortNameRow' AND CPID.SockingType = '$SockingType'
        ORDER BY abs(CPID.Grade) ASC";
        // WHERE CBS.BarCodeStatus=1 AND (AvalMcs!=0 OR LooseBags!=0) $Query AND PIVL.TypeProduct = '$TypeProduct'
        $stmtGrades = $this->conn->prepare($sqlGrades);
        $stmtGrades->execute();
        if ($Combinations === null) {
            $Combinations = $this->getVarietyCombinationData($Query, $TypeProduct, $ProductionDate, $ShortNameRow, $SockingType);
        }
        $gradeOrder = [];
        $gradeRows = [];
        while ($dataGrade = $stmtGrades->fetch(PDO::FETCH_ASSOC)) {
            $gradeName = $dataGrade['GradeName'];
            $gradeOrder[] = $gradeName;
            $gradeRows[$gradeName] = [
                'Grade' => $gradeName,
                'GradeValueData' => []
            ];
        }
        foreach ($Combinations as $Item) {
            $PackingStyle = $Item['PackingStyle'];
            $ShortName = $Item['ShortName'];
            $Species = $Item['Species'];
            $SockingType = $Item['SockingType'];
            $Glaze = $Item['Glaze'];

            $TotalSlabs = $Item['TotalSlabs'];
            $SlabWeight = $Item['SlabWeight'];
            $MFactor = $Item['MFactor'];           

            $CombinationQuery = " AND APS.PackingStyle = '$PackingStyle' AND PIVL.ShortName ='$ShortName' AND
            CPID.SockingType = '$SockingType' AND PIVL.Species = '$Species' AND PIVL.Glaze = '$Glaze'";
            $inByGrade = [];
            $outByGrade = [];
            $sqlInFields = "CPID.Grade AS GradeName, SUM(AvalMcs) AS AvalMcs,
            -- FLOOR(SUM(LooseBags)/TotalSlabs) AS LooseMcs,
            CASE WHEN SUM(LooseBags) != 0 THEN (SUM(LooseBags)/TotalSlabs) ELSE 0 END AS LooseMcs,
            CPID.Grade,PIVL.VarietyID,CPID.PackingStyleID";
            $sqlInSelect = "SELECT $sqlInFields FROM `CsStockData` as CPID
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            -- LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
            WHERE CPID.ProductionDate <= '$ProductionDate'
            AND (CPID.AvalMcs != 0 OR CPID.LooseBags != 0)
             $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query
            GROUP BY CPID.Grade";
            //echo '<pre>';print_r($sqlInSelect);die;
            $stmtInSelect = $this->conn->prepare($sqlInSelect);
            $stmtInSelect->execute();
            while ($rowInValue = $stmtInSelect->fetch(PDO::FETCH_ASSOC)) {                
                $unitPrice = $this->GetLatestPriceByVariety($rowInValue);
                $inByGrade[$rowInValue['GradeName']] = [
                    'Mcs' => (($rowInValue['AvalMcs'] ? $rowInValue['AvalMcs'] : 0) + ($rowInValue['LooseMcs'] ? $rowInValue['LooseMcs'] : 0)),
                    'unitPrice' => ($unitPrice ? $unitPrice : 0)
                ];

            }
           
            foreach ($gradeOrder as $gradeName) {
                $Mcs = isset($inByGrade[$gradeName]['Mcs']) ? $inByGrade[$gradeName]['Mcs'] : 0;
                $inkgsR = !empty($Mcs) ? $Mcs * $TotalSlabs * $SlabWeight : 0;

                $ConvertToKgs = !empty($Mcs) ? $Mcs * $TotalSlabs * $SlabWeight * $MFactor : 0;

                $unitPrice = isset($inByGrade[$gradeName]['unitPrice']) ? $inByGrade[$gradeName]['unitPrice'] : 0;

                $inUsd = $unitPrice * $ConvertToKgs;
                

                $gradeRows[$gradeName]['GradeValueData'][] = [ 
                                                                'MCS' => $Mcs, 
                                                                'INKGS' => round($inkgsR, 3), 
                                                                'RATE_PER_KGS' => $unitPrice, 
                                                                'ValUSD' => round($inUsd, 3)
                                                            ];
            }
        }
        $GradesData = [];
        foreach ($gradeOrder as $gradeName) {
            if (empty($gradeRows[$gradeName]['GradeValueData'])) {
                $gradeRows[$gradeName]['GradeValueData'][] = ['MCS' => '0','INKGS' => '0','RATE_PER_KGS' => '0','ValUSD' => '0'];
            }
            $GradesData[] = $gradeRows[$gradeName];
        }
        return $GradesData;
    }
    public function getVarietyCombinationData($Query, $TypeProduct, $ProductionDate, $ShortName, $SockingType , $table = '') //
    {
        $join = '';
        if($table == ''){
            $table = 'CsStockData';
            //$join = '  LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID ';
        }
        $sqlVarietyFields = "CPID.PackingStyleID, APS.PackingStyle,APS.Uom, PIVL.ShortName, CPID.SockingType, PIVL.Species, PIVL.Glaze, PIVL.GlazePerc
                            ,APS.`TotalSlabs`,APS.`SlabWeight`,CPID.Grade,CPID.VarietyID,APS.MFactor";
        $sqlVarietySelect = "SELECT $sqlVarietyFields FROM `$table` as CPID
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        $join
        WHERE CPID.ProductionDate <= '$ProductionDate' $Query  AND PIVL.TypeProduct = '$TypeProduct'
        AND PIVL.ShortName = '$ShortName' AND CPID.SockingType = '$SockingType'
        GROUP BY APS.PackingStyle, PIVL.Species, PIVL.Glaze
        ORDER BY PIVL.ShortName, -- CPID.PackingStyleID
        $this->PackingStyleOrderBy";
        //echo '<pre>';print_r($sqlVarietySelect);die;
        // GROUP BY CPID.PackingStyleID, PIVL.ShortName, CPID.SockingType, PIVL.Species, PIVL.Glaze ORDER BY PIVL.ShortName, CPID.PackingStyleID";
        $stmtVarietySelect = $this->conn->prepare($sqlVarietySelect);
        $stmtVarietySelect->execute();
        $ArrayVarietyData = [];
        while ($rowVarietywise = $stmtVarietySelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayVarietyList['PackingStyle'] = $rowVarietywise['PackingStyle'];
            $ArrayVarietyList['Uom'] = $rowVarietywise['Uom'];
            $ArrayVarietyList['TotalSlabs'] = $rowVarietywise['TotalSlabs'];
            $ArrayVarietyList['SlabWeight'] = $rowVarietywise['SlabWeight'];
            $ArrayVarietyList['PackingStyleID'] = $rowVarietywise['PackingStyleID'];
            $ArrayVarietyList['ShortName'] = $rowVarietywise['ShortName'];
            $ArrayVarietyList['Species'] = $rowVarietywise['Species'];
            $ArrayVarietyList['SockingType'] = $rowVarietywise['SockingType'];
            $ArrayVarietyList['Glaze'] = $rowVarietywise['Glaze'];
            $ArrayVarietyList['GlazePerc'] = $rowVarietywise['GlazePerc'];
            $ArrayVarietyList['Grade'] = $rowVarietywise['Grade'];
            $ArrayVarietyList['VarietyID'] = $rowVarietywise['VarietyID'];
            $ArrayVarietyList['MFactor'] = $rowVarietywise['MFactor'];
            $ArrayVarietyData[] = $ArrayVarietyList;
        }
        return $ArrayVarietyData;
    }
    
    public function getVarietywiseTotalsBk($Type, $TypeProduct, $Query, $ProductionDate, $ShortNameRow, $SockingType, $Combinations = null) //
    {
        if ($Combinations === null) {
            $Combinations = $this->getVarietyCombinationData($Query, $TypeProduct, $ProductionDate, $ShortNameRow, $SockingType);
        }
        $TotalValues = [];
        foreach ($Combinations as $Item) {
            $PackingStyle = $Item['PackingStyle'];
            $ShortName = $Item['ShortName'];
            $Species = $Item['Species'];
            $SockingType = $Item['SockingType'];
            $Glaze = $Item['Glaze'];

            $TotalSlabs = $Item['TotalSlabs'];
            $SlabWeight = $Item['SlabWeight'];

            //$unitPrice = $this->GetLatestPriceByVariety($Item);
            //echo '<pre>';print_r($unitPrice);die;
            $CombinationQuery = " AND APS.PackingStyle = '$PackingStyle' AND PIVL.ShortName ='$ShortName' AND
            CPID.SockingType = '$SockingType' AND PIVL.Species = '$Species' AND PIVL.Glaze = '$Glaze'";
            $sqlInFields = "SUM(ActualMcs) AS AvalMcs, 
            -- SUM(ActualLooseBags) AS LooseBags, SUM(ActualKgs) AS AvalKgs,
            FLOOR(SUM(ActualLooseBags)/TotalSlabs) AS LooseSlabs";
            $sqlInSelect = "SELECT $sqlInFields FROM `CsStockData` as CPID
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            -- LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
            WHERE CPID.ProductionDate <= '$ProductionDate' $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query";
            $stmtInSelect = $this->conn->prepare($sqlInSelect);
            $stmtInSelect->execute();
            $sqlOutFields = "SUM(OutMcs) as OutMcs, 
            -- SUM(OutLooseBags) AS OutLooseBags,SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS OutKGs, SUM(OutLooseBags*SlabWeight*MFactor) AS OutLKGs,
            FLOOR(SUM(OutLooseBags)/TotalSlabs) AS OutLooseMcs";
            $sqlOutSelect = "SELECT $sqlOutFields FROM CsStockOutDetails AS CSOD
            LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            -- LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
            WHERE CSOD.OutDate <= '$ProductionDate' $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query";
            $stmtOutSelect = $this->conn->prepare($sqlOutSelect);
            $stmtOutSelect->execute();
            $TotalValue = ['TotalValue' => '0'];
            $rowInTotalValue = $stmtInSelect->fetch(PDO::FETCH_ASSOC);
            $rowOutTotalValue = $stmtOutSelect->fetch(PDO::FETCH_ASSOC);
            if (!$rowInTotalValue) { $rowInTotalValue = []; }
            if (!$rowOutTotalValue) { $rowOutTotalValue = []; }
            $inTotalMcs = (($rowInTotalValue['AvalMcs']) ? $rowInTotalValue['AvalMcs'] : 0) + (($rowInTotalValue['LooseSlabs']) ? $rowInTotalValue['LooseSlabs'] : 0);
            $inTotalKgs = (($rowInTotalValue['AvalKgs']) ? $rowInTotalValue['AvalKgs'] : 0);
            $outTotalMcs = (($rowOutTotalValue['OutMcs']) ? $rowOutTotalValue['OutMcs'] : 0) + (($rowOutTotalValue['OutLooseMcs']) ? $rowOutTotalValue['OutLooseMcs'] : 0);
            $outTotalKgs = (($rowOutTotalValue['OutKGs']) ? $rowOutTotalValue['OutKGs'] : 0) + (($rowOutTotalValue['OutLKGs']) ? $rowOutTotalValue['OutLKGs'] : 0);
            $Mcs = $inTotalMcs - $outTotalMcs;
            $Kgs = $inTotalKgs - $outTotalKgs;

            $inKgs1 = !empty($Mcs) ? $TotalSlabs * $SlabWeight * $Mcs : 0;

            //$inUsd = $unitPrice * $inKgs1;
            if($Mcs == 0){
              //  $Kgs = 0;
            }
            
            $TotalValues[] = [
                'MCS' => !empty($Mcs) ? $Mcs : 0,
                'INKGS' => !empty($inKgs1) ? round($inKgs1, 3) : 0,
                'RATE_PER_KGS' => 0,
                'INUSD' => 0,
            ];
        }
        return $TotalValues;
    }    
    public function getVarietywiseTotals($Type, $TypeProduct, $Query, $ProductionDate, $ShortNameRow, $SockingType, $Combinations = null) //
    {
        if ($Combinations === null) {
            $Combinations = $this->getVarietyCombinationData($Query, $TypeProduct, $ProductionDate, $ShortNameRow, $SockingType);
        }
        $TotalValues = [];
        foreach ($Combinations as $Item) {
            $PackingStyle = $Item['PackingStyle'];
            $ShortName = $Item['ShortName'];
            $Species = $Item['Species'];
            $SockingType = $Item['SockingType'];
            $Glaze = $Item['Glaze'];

            $TotalSlabs = $Item['TotalSlabs'];
            $SlabWeight = $Item['SlabWeight'];
            $MFactor = $Item['MFactor'];

            //$unitPrice = $this->GetLatestPriceByVariety($Item);
            //echo '<pre>';print_r($unitPrice);die;
            $CombinationQuery = " AND APS.PackingStyle = '$PackingStyle' AND PIVL.ShortName ='$ShortName' AND
            CPID.SockingType = '$SockingType' AND PIVL.Species = '$Species' AND PIVL.Glaze = '$Glaze'";
            $sqlInFields = "-- SUM(AvalMcs) AS AvalMcs, 
                            SUM(AvalKgs) AS AvalKgs,
                            -- FLOOR(SUM(LooseBags)/TotalSlabs) AS LooseSlabs,
                           -- CASE WHEN SUM(LooseBags) != 0 THEN (SUM(LooseBags)/TotalSlabs) ELSE 0 END AS LooseSlabs,
                            SUM( CPID.AvalMcs + (CPID.LooseBags / NULLIF(APS.TotalSlabs, 0)) ) AS McsCalc ";
            $sqlInSelect = "SELECT $sqlInFields FROM `CsStockData` as CPID
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            -- LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
            WHERE CPID.ProductionDate <= '$ProductionDate'
            AND (CPID.AvalMcs != 0 OR CPID.LooseBags != 0)
             $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query";
             /* if($PackingStyle == '10 X 1 KGS'){
                                      echo '<pre>';print_r($sqlInSelect);die;
             } */

            $stmtInSelect = $this->conn->prepare($sqlInSelect);
            $stmtInSelect->execute();
            
            $TotalValue = ['TotalValue' => '0'];
            $rowInTotalValue = $stmtInSelect->fetch(PDO::FETCH_ASSOC); 
                    
            if (!$rowInTotalValue) { $rowInTotalValue = []; }
            //$inTotalMcs = (($rowInTotalValue['AvalMcs']) ? $rowInTotalValue['AvalMcs'] : 0) + (($rowInTotalValue['LooseSlabs']) ? $rowInTotalValue['LooseSlabs'] : 0);
            $inTotalMcs = ($rowInTotalValue['McsCalc']) ? $rowInTotalValue['McsCalc'] : 0;
            $Mcs = $inTotalMcs;

            $inKgs1 = !empty($Mcs) ? $TotalSlabs * $SlabWeight * $Mcs : 0;
            //$ConvertToKgs = !empty($Mcs) ? $TotalSlabs * $SlabWeight * $MFactor * $Mcs : 0;
            $ConvertToKgs = $rowInTotalValue['AvalKgs'];

            
            
            $TotalValues[] = [
                'MCS' => !empty($Mcs) ? $Mcs : 0,
                'INKGS' => !empty($inKgs1) ? round($inKgs1, 3) : 0,
                'RATE_PER_KGS' => 0,
                'INUSD' => 0,
                'ConvertToKgs' => !empty($ConvertToKgs) ? round($ConvertToKgs, 3) : 0,
            ];
            
        }
        return $TotalValues;
    }
    public function getVarietywiseFreezingData($Type, $TypeProduct, $Query, $ProductionDate, $ShortNameRow, $SockingType,$ColdstoreID, $table = '', $Combinations = null) //
    {
        if ($Combinations === null) {
            $Combinations = $this->getVarietyCombinationData($Query, $TypeProduct, $ProductionDate, $ShortNameRow, $SockingType, $table);
        }
        $Query = str_replace("CPID", "PFD", $Query);//replace alias name
        $TotalValues = [];
        foreach ($Combinations as $Item) { 
            $PackingStyle = $Item['PackingStyle'];
            $PackingStyleID = $Item['PackingStyleID'];
            $ShortName = $Item['ShortName'];
            $Species = $Item['Species'];
            $SockingType = $Item['SockingType'];
            $Glaze = $Item['Glaze'];
            $VarietyID = $Item['VarietyID'];

            $TotalSlabs = $Item['TotalSlabs'];
            $SlabWeight = $Item['SlabWeight'];
            $MFactor = $Item['MFactor'];
            
            
               $CombinationQuery = " AND APS.PackingStyle='$PackingStyle' AND SPVL.ShortName='$ShortName' AND
            SPVL.Treatment='$SockingType' AND SPVL.Species='$Species' AND SPVL.Glaze='$Glaze'";
            // $CombinationQuery = " AND APS.PackingStyle='$PackingStyle' AND SPVL.Treatment='$SockingType'
            // AND SPVL.ShortName='$ShortName' AND SPVL.Species='$Species' AND SPVL.Glaze='$Glaze'";
            $sqlInSelect = "SELECT sum(ProductionWeight) AS ProductionWeight,APS.MFactor,APS.SlabWeight,APS.TotalSlabs
            ,PFD.VarietyID,PFD.PackingStyleID
             FROM `ProdFreezingData` AS PFD
            LEFT JOIN ScrmPIVarietyList AS SPVL ON PFD.VarietyID=SPVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON PFD.PackingStyleID=APS.PCKID
            WHERE ProductionDate = '$ProductionDate' $CombinationQuery $Query";
            // echo $sqlInSelect;die;
            $stmtInSelect = $this->conn->prepare($sqlInSelect);
            $stmtInSelect->execute();
            $FreezingValue = $stmtInSelect->fetch(PDO::FETCH_ASSOC);
            $ProductionWeight = isset($FreezingValue['ProductionWeight']) ? $FreezingValue['ProductionWeight'] : 0;
            
            if($ProductionWeight > 0){//6152.4
                $slabs = ((($ProductionWeight / $FreezingValue['MFactor']) / $FreezingValue['SlabWeight']) / $FreezingValue['TotalSlabs']);
               
            }else{
                $slabs = 0;
            }
            $unitPrice = $this->GetLatestPriceByVariety($FreezingValue);

            $inKgs1 = !empty($slabs) ? $slabs * $SlabWeight : 0;
            $ConvertToKgs = !empty($slabs) ? $slabs * $SlabWeight * $MFactor : 0;

             $inUsd = $unitPrice * $ConvertToKgs;
            
            $TotalValues[] = [
                'MCS' => !empty($slabs) ? round($slabs,2) : 0,
                'INKGS' => !empty($ProductionWeight) ? round($inKgs1,2) : 0,
                'RATE_PER_KGS' => !empty($unitPrice) ? $unitPrice : 0,
                'INUSD' => !empty($inUsd) ? round($inUsd,2) : 0,
            ];
        }
        return $TotalValues;
    }
    function GetLatestPriceByVariety($ItemData = []){
        if(!empty($ItemData)){
            $PackingStyleID = $ItemData['PackingStyleID'];
            $Grade = $ItemData['Grade'];
            $VarietyID = $ItemData['VarietyID'];
            $sql = "SELECT Price FROM ScrmPoGradeData WHERE `PackingStyleID` = '$PackingStyleID' AND `VarietyID` = '$VarietyID' ";
            if($Grade){
                $sql .= " AND  `Grade` = '$Grade' ";
            }
            $sql .= "    ORDER BY ID DESC LIMIT 1";                    
            $Stmt = $this->conn->prepare($sql);
            $Stmt->execute();
            $Price = $Stmt->fetch(PDO::FETCH_ASSOC)['Price'];
            return (float)$Price;
        }
    }
    function getLatestExchangeRate(){
        $sql = "SELECT `ExchangeRate` FROM `ScrmExchangeRates` ORDER BY `FromDate` DESC LIMIT 1";
        $Stmt = $this->conn->prepare($sql);
        $Stmt->execute();
        $ExchangeRate = $Stmt->fetch(PDO::FETCH_ASSOC)['ExchangeRate'];
        return (float)$ExchangeRate;
    }
    function getGrandTotals($RequestBody){
        $ProductionDate = $RequestBody['ProductionDate'];
        $ColdstoreID = $RequestBody['ColdstoreID'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $Type = $RequestBody['Type'];//mcs ot kgs
        $FreezingType = $RequestBody['FreezingType'];
        $Variety = $RequestBody['Variety'];
        $Treatment = $RequestBody['Treatment'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $Grade = $RequestBody['Grade'];
        $Species  = $RequestBody['Species'];
        $PlantID  = $RequestBody['PlantID'];
        $StockBlgsTo  = $RequestBody['StockBlgsTo'];
        if(empty($ProductionDate)){
            return false;
        }
        $Query = '';
        if ($ColdstoreID != '') {
            $Query .= " AND CPID.UnitID = '$ColdstoreID'";
        }
        if ($PlantID != '') {
            $Query .= " AND CPID.PlantID = '$PlantID'";
        }
        if ($StockBlgsTo != '') {
            $Query .= " AND CPID.StockHolderID = '$StockBlgsTo'";
        }
        if ($FreezingType != '') {
            $Query .= " AND PIVL.FreezingType = '$FreezingType'";
        }
        if ($Treatment != '') {
            $Query .= " AND CPID.SockingType = '$Treatment'";
        }
        if ($PackingStyle != '') {
            $Query .= " AND CPID.PackingStyleID = '$PackingStyle'";
        }
        if ($Grade != '') {
            $Query .= " AND CPID.Grade = '$Grade'";
        }
        if ($Species != '') {
            $Query .= " AND PIVL.Species = '$Species'";
        }
        if ($TypeProduct != '') {
            $Query .= " AND TypeProduct = '$TypeProduct'";
        }
        if ($Variety != '') {
            $Query .= " AND ShortName = '$Variety'";
        }

        $sql = "SELECT SUM(McsCalc) AS TotalStoreMcs, SUM(StoreKgs) AS TotalStoreQuantity 
                    , SUM(PriceKgs) AS TotalPriceKgs
                    FROM ( 
                    SELECT sum( CPID.AvalMcs + (CPID.LooseBags / NULLIF(APS.TotalSlabs, 0)) ) AS McsCalc, sum(CPID.AvalKgs) AS StoreKgs,
                    IFNULL(
                        NULLIF(
                            (
                                SELECT Price 
                                FROM ScrmPoGradeData 
                                WHERE PackingStyleID = CPID.PackingStyleID 
                                AND VarietyID = CPID.VarietyID 
                                ORDER BY ID DESC 
                                LIMIT 1
                            ),
                        0),
                    0
                    ) AS Price,
                    (
                        sum(CPID.AvalKgs) * (
                                            IFNULL(
                                                NULLIF(
                                                    (
                                                        SELECT Price 
                                                        FROM ScrmPoGradeData 
                                                        WHERE PackingStyleID = CPID.PackingStyleID 
                                                        AND VarietyID = CPID.VarietyID 
                                                        ORDER BY ID DESC 
                                                        LIMIT 1
                                                    ),
                                                0),
                                            0
                                            ) 
                                        )
                    )AS PriceKgs
                    FROM CsStockData CPID 
                    LEFT JOIN AdminPackingStyles APS ON APS.PCKID = CPID.PackingStyleID 
                    LEFT JOIN ScrmPIVarietyList PIVL ON PIVL.VarietyID = CPID.VarietyID 
                    WHERE (CPID.AvalMcs != 0 OR CPID.LooseBags != 0) and CPID.ProductionDate <= '$ProductionDate'
                    $Query
                    group by CPID.VarietyID,CPID.PackingStyleID 
                    ) AS FineSummary";
        $Stmt = $this->conn->prepare($sql);
        $Stmt->execute();
        $results = $Stmt->fetch(PDO::FETCH_ASSOC);
        return $results;

    }
}
