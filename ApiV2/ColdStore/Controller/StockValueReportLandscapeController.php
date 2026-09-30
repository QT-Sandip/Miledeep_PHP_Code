<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';
class StockValueReportLandScapeController
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
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
        $this->PackingStyleOrderBy = " CASE
                                        WHEN APS.Uom = 'KGS' THEN 1
                                        WHEN APS.Uom = 'OZ' THEN 2
                                        ELSE  3 END ";
        $this->VarietyOrderBy = " ORDER BY 
                                CASE
                                    WHEN PIVL.TypeProduct = 'RAW' THEN 1
                                    WHEN PIVL.TypeProduct = 'COOKED' THEN 2
                                    ELSE 3 END,
                                CASE
                                    WHEN  PreparationType = 'RI' THEN 1
                                    WHEN  PreparationType = 'RB' THEN 2
                                    WHEN PreparationType = 'RI-BFY' THEN 3
                                    WHEN PreparationType = 'RI-SKW' THEN 4
                                    WHEN PreparationType = 'CI' THEN 5
                                    WHEN PreparationType = 'CI V EZPL' THEN 6
                                    WHEN PreparationType = 'CIS' THEN 7
                                    WHEN PreparationType = 'BI' THEN 8
                                    ELSE 9 END,
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
                                    ELSE 5 END,
                                CASE
                                    WHEN APS.Uom = 'KGS' THEN 1
                                    WHEN APS.Uom = 'OZ' THEN 2
                                    ELSE  3 END ";
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
        
        if ($Variety != '') {
            $Query .= " AND PIVL.ShortName = '$Variety'";
        }
        if ($Treatment != '') {
            $Query .= " AND CPID.SockingType = '$Treatment'";
        }
        if ($TypeProduct != '') {
            $Query .= " AND PIVL.TypeProduct = '$TypeProduct'";
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
        $StockOutDate = date('Y-m-d', strtotime($ProductionDate . '-1 day'));
       
        $ArrayTypeData = [];
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';

        $VarietyCombinationData = $this->getVarietyCombinationData($Query,  $ProductionDate);        
        $Data['ExchangeRate'] = $this->getLatestExchangeRate();
        $Data['Stores'] = $this->getStores($ColdstoreID, $Type,  $Query, $ProductionDate,   $VarietyCombinationData);
        if($RequestBody['Request'] == 'SummeryData'){
            return $Data;
        }
        $GradesData = $this->getGradeNames($Query,  $Type, $ProductionDate,   $VarietyCombinationData);
        $GrandTotals = $this->getVarietywiseTotals($Type,  $Query, $ProductionDate,   $VarietyCombinationData);
        $FreezingData = $this->getVarietywiseFreezingData($Type,  $Query, $ProductionDate,  $ColdstoreID, '', $VarietyCombinationData);
        if ($GradesData && $VarietyCombinationData && $GrandTotals) {
            // $ArrayTypeList['TypeProduct'] = $TypeProductRow . ' ' . $ShortNameRow . ' ' . $SockingTypeRow;
            // $ArrayTypeList['ShortName'] = $ShortNameRow;
            // $ArrayTypeList['Treatment'] = $SockingTypeRow;
            $ArrayTypeList['VarietyCombinationData'] = $VarietyCombinationData;
            $ArrayTypeList['GradesData'] = $GradesData;
            $ArrayTypeList['GrandTotals'] = $GrandTotals;
            $ArrayTypeList['FreezingData'] = $FreezingData;
            $ArrayTypeData[] = $ArrayTypeList;
        }
        //  $GrandTotals = $this->getGrandTotals($RequestBody);
        // ECHO '<PRE>';print_r($ArrayTypeData);die;        
        $Data['Data'] = $ArrayTypeData;
        // $Data['GrandTotals'] = $GrandTotals;
        //$Data['Stores'] = $this->getStores($ColdstoreID, $Type,  $Query, $ProductionDate,   $VarietyCombinationData);
        return $Data;
    } //$Query,  $Type, $ProductionDate,   $VarietyCombinationData
    public function getGradeNames($Query, $Type, $ProductionDate, $Combinations = null) //
    {
        $sqlGrades = "SELECT DISTINCT CPID.Grade AS GradeName FROM `CsStockData` AS CPID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID=PIVL.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        -- LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CPID.ProductionDate <= '$ProductionDate' $Query  
        GROUP BY CPID.Grade
        HAVING SUM( CPID.AvalMcs + (CPID.LooseBags / NULLIF(APS.TotalSlabs, 0)) ) != 0
        ORDER BY abs(CPID.Grade) ASC";
       // ECHO '<pre>';print_r($sqlGrades);die;
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
            $PreparationType = $Item['PreparationType'];
            $TypeProduct = $Item['TypeProduct'];

            $TotalSlabs = $Item['TotalSlabs'];
            $SlabWeight = $Item['SlabWeight'];
            $MFactor = $Item['MFactor'];           

            $CombinationQuery = " AND APS.PackingStyle = '$PackingStyle' AND PIVL.ShortName ='$ShortName' AND
            CPID.SockingType = '$SockingType' AND PIVL.Species = '$Species' AND PIVL.Glaze = '$Glaze' AND PreparationType = '$PreparationType' ";
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
            GROUP BY CPID.Grade
            HAVING SUM( CPID.AvalMcs + (CPID.LooseBags / NULLIF(APS.TotalSlabs, 0)) ) != 0";
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
    public function getVarietyCombinationData($Query, $ProductionDate , $table = '') //
    {
        $join = '';
        if($table == ''){
            $table = 'CsStockData';
            //$join = '  LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID ';
        }
        $sqlVarietyFields = " PIVL.TypeProduct,PreparationType,PIVL.Species,PIVL.ShortName, 
                                CPID.SockingType,  PIVL.Glaze,CPID.PackingStyleID, APS.PackingStyle,
                                APS.Uom, PIVL.GlazePerc,APS.`TotalSlabs`,APS.`SlabWeight`,CPID.Grade,
                                CPID.VarietyID,APS.MFactor ";
        $sqlVarietySelect = "SELECT $sqlVarietyFields FROM `$table` as CPID
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        $join
        WHERE CPID.ProductionDate <= '$ProductionDate' $Query  
        GROUP BY PIVL.TypeProduct,PreparationType,PIVL.ShortName,APS.PackingStyle, PIVL.Species, PIVL.Glaze,CPID.SockingType
        HAVING SUM( CPID.AvalMcs + (CPID.LooseBags / NULLIF(APS.TotalSlabs, 0)) ) != 0
        $this->VarietyOrderBy";
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
            $ArrayVarietyList['PreparationType'] = $rowVarietywise['PreparationType'];
            $ArrayVarietyList['TypeProduct'] = $rowVarietywise['TypeProduct'];
            $ArrayVarietyData[] = $ArrayVarietyList;
        }
        return $ArrayVarietyData;
    }
    
       
    public function getVarietywiseTotalsOld($Type, $Query, $ProductionDate, $Combinations = null) //
    {
        if ($Combinations === null) {
            $Combinations = $this->getVarietyCombinationData($Query, $TypeProduct, $ProductionDate);
        }
        $TotalValues = [];
        foreach ($Combinations as $Item) {
            $PackingStyle = $Item['PackingStyle'];
            $ShortName = $Item['ShortName'];
            $Species = $Item['Species'];
            $SockingType = $Item['SockingType'];
            $Glaze = $Item['Glaze'];
            $PreparationType = $Item['PreparationType'];
            $TypeProduct = $Item['TypeProduct'];

            $TotalSlabs = $Item['TotalSlabs'];
            $SlabWeight = $Item['SlabWeight'];
            $MFactor = $Item['MFactor'];

            //$unitPrice = $this->GetLatestPriceByVariety($Item);
            //echo '<pre>';print_r($unitPrice);die;
            $CombinationQuery = " AND APS.PackingStyle = '$PackingStyle' AND PIVL.ShortName ='$ShortName' AND
            CPID.SockingType = '$SockingType' AND PIVL.Species = '$Species' AND PIVL.Glaze = '$Glaze' AND PreparationType = '$PreparationType' ";
            $sqlInFields = "-- SUM(AvalMcs) AS AvalMcs, 
                            CPID.UnitID,SUM(AvalKgs) AS AvalKgs,
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

            $unitPrice = $this->GetLatestPriceByVariety($Item);
            

            $ConvertToKgs = !empty($Mcs) ? $Mcs * $TotalSlabs * $SlabWeight * $MFactor : 0;

            $inUsd = $unitPrice * round($ConvertToKgs, 3);
                
            
            $TotalValues[] = [
                'UnitID' => !empty($rowInTotalValue['UnitID']) ? $rowInTotalValue['UnitID'] : '',
                'MCS' => !empty($Mcs) ? $Mcs : 0,
                'INKGS' => !empty($inKgs1) ? round($inKgs1, 3) : 0,
                'RATE_PER_KGS' =>  $unitPrice,
                'INUSD' => $inUsd,
                'ConvertToKgs' => !empty($ConvertToKgs) ? round($ConvertToKgs, 3) : 0,
            ];
            
        }
        return $TotalValues;
    }
    public function getVarietywiseTotals($Type, $Query, $ProductionDate, $Combinations = null) //
    {
        if ($Combinations === null) {
            $Combinations = $this->getVarietyCombinationData($Query, $TypeProduct, $ProductionDate);
        }
        $TotalValues = [];
        foreach ($Combinations as $Item) {
            $PackingStyle = $Item['PackingStyle'];
            $ShortName = $Item['ShortName'];
            $Species = $Item['Species'];
            $SockingType = $Item['SockingType'];
            $Glaze = $Item['Glaze'];
            $PreparationType = $Item['PreparationType'];
            $TypeProduct = $Item['TypeProduct'];
            $Grade = $Item['Grade'];

            $TotalSlabs = $Item['TotalSlabs'];
            $SlabWeight = $Item['SlabWeight'];
            $MFactor = $Item['MFactor'];

            //$unitPrice = $this->GetLatestPriceByVariety($Item);
            //echo '<pre>';print_r($unitPrice);die;
            $CombinationQuery = " AND APS.PackingStyle = '$PackingStyle' AND PIVL.ShortName ='$ShortName' AND
            CPID.SockingType = '$SockingType' AND PIVL.Species = '$Species' AND PIVL.Glaze = '$Glaze' AND PreparationType = '$PreparationType' ";
            /* $sqlInFields = "-- SUM(AvalMcs) AS AvalMcs, 
                            group_concat( CPID.VarietyId) VarietyIds,
                            group_concat(CPID.AvalMcs + (CPID.LooseBags / NULLIF(APS.TotalSlabs, 0))) IndividualMcs,
                            CPID.UnitID,SUM(AvalKgs) AS AvalKgs,
                            -- FLOOR(SUM(LooseBags)/TotalSlabs) AS LooseSlabs,
                           -- CASE WHEN SUM(LooseBags) != 0 THEN (SUM(LooseBags)/TotalSlabs) ELSE 0 END AS LooseSlabs,
                            SUM( CPID.AvalMcs + (CPID.LooseBags / NULLIF(APS.TotalSlabs, 0)) ) AS McsCalc ";*/
                $sqlInSelect = "SELECT
                                GROUP_CONCAT(t.VarietyID ORDER BY t.VarietyID) AS VarietyIds,
                                GROUP_CONCAT(t.MCSS ORDER BY t.VarietyID) AS IndividualMcs,
                                GROUP_CONCAT(t.AvalKgs ORDER BY t.VarietyID) AS IndividualAvalKgs,
                                t.UnitID,
                                SUM(t.AvalKgs) AS AvalKgs,
                                SUM(t.MCSS) AS McsCalc

                            FROM (

                                SELECT
                                    CPID.VarietyID,
                                    CPID.UnitID,

                                    SUM(
                                        CPID.AvalMcs +
                                        (CPID.LooseBags / NULLIF(APS.TotalSlabs,0))
                                    ) AS MCSS,

                                    SUM(CPID.AvalKgs) AS AvalKgs

                                FROM CsStockData CPID

                                LEFT JOIN ScrmPIVarietyList PIVL
                                    ON CPID.VarietyID=PIVL.VarietyID

                                LEFT JOIN AdminPackingStyles APS
                                    ON CPID.PackingStyleID=APS.PCKID

                                WHERE  CPID.ProductionDate <= '$ProductionDate'
                                        AND (CPID.AvalMcs != 0 OR CPID.LooseBags != 0)
                                        $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query
                                GROUP BY
                                    CPID.UnitID,
                                    CPID.VarietyID

                            ) t";
             /* if($PackingStyle == '16 X 2 LBS' && $Grade == '21/25'){
                                      echo '<pre>';print_r($sqlInSelect);die;
             } */

            $stmtInSelect = $this->conn->prepare($sqlInSelect);
            $stmtInSelect->execute();
            
            $TotalValue = ['TotalValue' => '0'];
            
            $McsTotal = $INKGS = $USD = $ConvertToKgsTotal =  $UnitID = 0;

           // $rowInTotalValue = $stmtInSelect->fetch(PDO::FETCH_ASSOC); 
            while ($rowInTotalValue = $stmtInSelect->fetch(PDO::FETCH_ASSOC)) {
                //echo '<pre>';print_r($rowInTotalValue);//die;
                $varietyIdsArr = explode(',', $rowInTotalValue['VarietyIds']);
                $IndividualMcsArr = explode(',', $rowInTotalValue['IndividualMcs']);
                $IndividualAvalKgsArr = explode(',', $rowInTotalValue['IndividualAvalKgs']);
                $AvalKgs =  $rowInTotalValue['AvalKgs'];

                $result = [];
                $resultKgs = [];

                foreach ($varietyIdsArr as $key => $varietyId) {

                    if (!isset($result[$varietyId])) {
                        $result[$varietyId] = 0;
                    }
                    if (!isset($resultKgs[$varietyId])) {
                        $resultKgs[$varietyId] = 0;
                    }

                    $result[$varietyId] += (float)$IndividualMcsArr[$key];
                    $resultKgs[$varietyId] += (float)$IndividualAvalKgsArr[$key];
                }
                /* if($PackingStyle == '1 X 10 KGS' && $Grade == '36/40' && $ShortName == 'PVPD' && $SockingType == 'NPT' && $PreparationType == 'RI'){
                echo '<pre>';print_r($rowInTotalValue);//die;
                echo '<pre>';print_r($result);die;
                } */
                
                $UnitID = $rowInTotalValue['UnitID'];
                foreach ($result as $varietyIdKey => $McsVal) {
                    //echo '<pre>';print_r($result);die;
                    $unitPrice = $this->GetLatestPriceByVariety(['VarietyID' => $varietyIdKey]);

                    $inTotalMcs = ($McsVal) ? $McsVal : 0;
                    $Mcs = $inTotalMcs;

                    $inKgs1 = !empty($Mcs) ? $TotalSlabs * $SlabWeight * $Mcs : 0;//AvalKgs
                    //$ConvertToKgs = !empty($Mcs) ? $TotalSlabs * $SlabWeight * $MFactor * $Mcs : 0;
                    //$ConvertToKgs = $rowInTotalValue['AvalKgs'];

                    //$ConvertToKgs = !empty($Mcs) ?$inKgs1 * $MFactor : 0;

                    $ConvertToKgs = $resultKgs[$varietyIdKey];

                    $inUsd = $unitPrice * round($ConvertToKgs, 3);

                    $McsTotal += $Mcs;
                    $INKGS += $inKgs1;
                    $USD += $inUsd;
                    $ConvertToKgsTotal += $ConvertToKgs;
                }
            }
                //    echo '<pre>';print_r($McsTotal);die;
            if (!$rowInTotalValue) { $rowInTotalValue = []; }

           // $ConvertToKgs = $rowInTotalValue['AvalKgs'];

                
            $TotalValues[] = [
                'UnitID' => $UnitID,
                'MCS' => !empty($McsTotal) ? $McsTotal : 0,
                'INKGS' => !empty($INKGS) ? round($INKGS, 3) : 0,
                'RATE_PER_KGS' => 0,
                'INUSD' => $USD,
                'ConvertToKgs' => !empty($ConvertToKgsTotal) ? round($ConvertToKgsTotal, 3) : 0,
            ];
            /* if($PackingStyle == '16 X 2 LBS' && $Grade == '21/25'){
            echo '<pre>';print_r([
                'UnitID' => !empty($rowInTotalValue['UnitID']) ? $rowInTotalValue['UnitID'] : '',
                'MCS' => !empty($McsTotal) ? $McsTotal : 0,
                'INKGS' => !empty($INKGS) ? round($INKGS, 3) : 0,
                'RATE_PER_KGS' => 0,
                'INUSD' => $USD,
                'ConvertToKgs' => !empty($ConvertToKgsTotal) ? round($ConvertToKgsTotal, 3) : 0,
            ]);die;
            } */ 
        }
        return $TotalValues;
    }
    public function getVarietywiseFreezingData($Type, $Query, $ProductionDate,$ColdstoreID, $table = '', $Combinations = null) //
    {
        if ($Combinations === null) {
            $Combinations = $this->getVarietyCombinationData($Query, $ProductionDate, $table);
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
            $PreparationType = $Item['PreparationType'];
            $TypeProduct = $Item['TypeProduct'];

            $TotalSlabs = $Item['TotalSlabs'];
            $SlabWeight = $Item['SlabWeight'];
            $MFactor = $Item['MFactor'];
            
            
               $CombinationQuery = " AND APS.PackingStyle='$PackingStyle' AND SPVL.ShortName='$ShortName' AND
            SPVL.Treatment='$SockingType' AND SPVL.Species='$Species' AND SPVL.Glaze='$Glaze' AND PreparationType = '$PreparationType' AND SPVL.TypeProduct = '$TypeProduct' ";
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
            /* $sql = "SELECT Price FROM ScrmPoGradeData WHERE `PackingStyleID` = '$PackingStyleID' AND `VarietyID` = '$VarietyID' ";
            if($Grade){
                $sql .= " AND  `Grade` = '$Grade' ";
            } */
            $sql = "SELECT Price FROM ScrmPoGradeData WHERE `VarietyID` = '$VarietyID' ";
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
    function getStores($UnitId, $Type,  $Query, $ProductionDate,   $VarietyCombinationData)
    {
        $where = '';
        if($UnitId){
            $where = " AND StoreID=$UnitId";
        }
        $sqlQuery = "SELECT StoreID ,StoreName  FROM `CsStores` WHERE StoreStatus=1 $where ORDER BY StoreName ASC ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Query .= " AND CPID.UnitID = '".$dataRow['StoreID']."'";
            /* if($dataRow['StoreID'] == 2){
                echo '<pre>';print_r($Query);die;
            } */
            $StoreWiseTotal = $this->getVarietywiseTotals($Type, $Query, $ProductionDate, $VarietyCombinationData);
           // echo '<pre>';print_r($StoreWiseTotal);die;
            $mcs = $KGS = $USD = 0;
            foreach($StoreWiseTotal as $item){
                if($item['UnitID'] == $dataRow['StoreID']){
                    $mcs += $item['MCS'];
                    $KGS += $item['ConvertToKgs'];
                    $USD += $item['INUSD'];
                }
            }
            $dataRow['Total'] = [
                'mcs' => $mcs,
                'KGS' => $KGS,
                'USD' => $USD,
            ];
            $Data[] = $dataRow;
            $Query = str_replace(" AND CPID.UnitID = '".$dataRow['StoreID']."'", "", $Query);
        }
        return $Data;
    }
}
