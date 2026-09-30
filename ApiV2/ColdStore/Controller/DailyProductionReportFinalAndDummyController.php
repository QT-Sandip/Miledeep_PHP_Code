<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ERROR);
include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';
class DailyProductionReportFinalAndDummyController
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
           // $QueryTwo .= " AND TypeProduct = '$TypeProduct'";
            $Query .= " AND TypeProduct = '$TypeProduct'";
        }
        if ($Variety != '') {
            //$QueryTwo .= " AND ShortName = '$Variety'";
             $Query .= " AND PIVL.ShortName = '$Variety'";
        }
        if ($PackingStyle != '') {
           // $QueryTwo .= " AND PackingStyle = '$PackingStyle'";
            $Query .= " AND PCKID = '$PackingStyle'";
        }
        $QueryReplace =  $QueryTwo;
        if ($Treatment != '') {
           // $QueryTwo .= " AND SockingType = '$Treatment'";
           $Query .= " AND CPID.SockingType = '$Treatment'";
           
        }
       //
       // 
       //  echo '<pre>';print_r($RequestBody);die;
        // $sqlType = "SELECT DISTINCT TypeProduct FROM ScrmPIVarietyList WHERE 1=1 $QueryTwo ORDER BY TypeProduct ASC";
        $sqlType = "SELECT 
                        CASE WHEN PackingTypeID = 'MASTER'  THEN 'FINAL' ELSE PackingTypeID END AS CustomPackingTypeID,
                        `PackingTypeID`,
                        CASE WHEN PackingTypeID = 'MASTER'  THEN CPID.FreezingType ELSE '' END AS FreezingType
                    FROM CsStockData CPID
                    LEFT JOIN ScrmPIVarietyList PIVL ON CPID.VarietyID=PIVL.VarietyID
                    LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
                    WHERE 1 = 1 
                    $Query 
                    $QueryTwo
                    GROUP BY 
                    CASE 
                        WHEN PackingTypeID = 'MASTER' 
                        THEN CPID.FreezingType
                        ELSE PackingTypeID
                    END
                    ORDER BY 
                    CASE
                        WHEN PackingTypeID = 'MASTER' THEN 1
                        ELSE 2
                        END,
                    CASE
                        WHEN PackingTypeID = 'MASTER' AND CPID.FreezingType = 'BLOCK' THEN 1
                        ELSE 2
                        END";
        $stmtType = $this->conn->prepare($sqlType);
        $stmtType->execute();
       // echo '<pre>';print_r($sqlType);die;
        $ArrayTypeData = [];
        while ($rowType = $stmtType->fetch(PDO::FETCH_ASSOC)) {
           // echo '<pre>';print_r($rowType);die;
            $PackingTypeID = $rowType['PackingTypeID'];
            $FreezingType = $rowType['FreezingType'];
           // $SockingTypeRow = $rowType['Treatment']; ///
            $VarietyCombinationData = $this->getVarietyCombinationData($Query, $PackingTypeID, $ProductionDate, $FreezingType);
            $GradesData = $this->getGradeNames($Query, $PackingTypeID, $Type, $ProductionDate, $FreezingType, $VarietyCombinationData);
            $GrandTotals = $this->getVarietywiseTotals($Type, $PackingTypeID, $Query, $ProductionDate, $FreezingType, $VarietyCombinationData);
           // $FreezingData = $this->getVarietywiseFreezingData($Type, $PackingTypeID, $Query, $ProductionDate, $FreezingType, $ColdstoreID, '', $VarietyCombinationData);
            if ($GradesData && $VarietyCombinationData && $GrandTotals) {
               // $ArrayTypeList['TypeProduct'] = $TypeProductRow . ' ' . $ShortNameRow . ' ' . $SockingTypeRow;
                $ArrayTypeList['PackingTypeID'] = $rowType['CustomPackingTypeID'];
                $ArrayTypeList['FreezingType'] = $FreezingType;
                $ArrayTypeList['VarietyCombinationData'] = $VarietyCombinationData;
                $ArrayTypeList['GradesData'] = $GradesData;
                $ArrayTypeList['GrandTotals'] = $GrandTotals;
              //  $ArrayTypeList['FreezingData'] = $FreezingData;
                $ArrayTypeData[] = $ArrayTypeList;
            }
        }
      //  $GrandTotals = $this->getGrandTotals($RequestBody);
       // ECHO '<PRE>';print_r($ArrayTypeData);die;
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['Data'] = $ArrayTypeData;
        return $Data;
    }
    
    public function getGradeNames($Query, $PackingTypeID, $Type, $ProductionDate, $FreezingType, $Combinations = null) //
    {
        $sqlGrades = "SELECT DISTINCT CPID.Grade AS GradeName FROM `CsStockData` AS CPID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID=PIVL.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        -- LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CPID.ProductionDate <= '$ProductionDate' $Query 
        AND CPID.PackingTypeID = '$PackingTypeID' ";
        if($FreezingType){
        $sqlGrades .= "AND CPID.FreezingType = '$FreezingType' "; 
        }
        $sqlGrades .= "
        GROUP BY CPID.Grade
        HAVING SUM( CPID.AvalMcs + (CPID.LooseBags / NULLIF(APS.TotalSlabs, 0)) ) != 0
        ORDER BY abs(CPID.Grade) ASC";
        //ECHO '<pre>';print_r($sqlGrades);die;
        // WHERE CBS.BarCodeStatus=1 AND (AvalMcs!=0 OR LooseBags!=0) $Query AND PIVL.TypeProduct = '$TypeProduct'
        $stmtGrades = $this->conn->prepare($sqlGrades);
        $stmtGrades->execute();
        if ($Combinations === null) {
            $Combinations = $this->getVarietyCombinationData($Query, $PackingTypeID, $ProductionDate, $FreezingType);
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
             PIVL.Species = '$Species' AND CPID.SockingType = '$SockingType' ";
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
            $CombinationQuery AND CPID.PackingTypeID = '$PackingTypeID' $Query ";
            if($FreezingType){
                $sqlInSelect .= " AND CPID.FreezingType = '$FreezingType' ";
            }
            $sqlInSelect .= " 
            GROUP BY CPID.Grade
            HAVING SUM( CPID.AvalMcs + (CPID.LooseBags / NULLIF(APS.TotalSlabs, 0)) ) != 0";
            //echo '<pre>';print_r($sqlInSelect);die;
            $stmtInSelect = $this->conn->prepare($sqlInSelect);
            $stmtInSelect->execute();
            while ($rowInValue = $stmtInSelect->fetch(PDO::FETCH_ASSOC)) {                
                $inByGrade[$rowInValue['GradeName']] = [
                    'Mcs' => (($rowInValue['AvalMcs'] ? $rowInValue['AvalMcs'] : 0) + ($rowInValue['LooseMcs'] ? $rowInValue['LooseMcs'] : 0)),
                ];

            }
           
            foreach ($gradeOrder as $gradeName) {
                $Mcs = isset($inByGrade[$gradeName]['Mcs']) ? $inByGrade[$gradeName]['Mcs'] : 0;
                $inkgsR = !empty($Mcs) ? $Mcs * $TotalSlabs * $SlabWeight : 0;

                $ConvertToKgs = !empty($Mcs) ? $Mcs * $TotalSlabs * $SlabWeight * $MFactor : 0;


                
                $gradeRows[$gradeName]['GradeValueData'][] = $Mcs;
                /* $gradeRows[$gradeName]['GradeValueData'][] = [ 
                                                                'MCS' => $Mcs, 
                                                                'INKGS' => round($inkgsR, 3), 
                                                            ]; */
            }
        }
        $GradesData = [];
        foreach ($gradeOrder as $gradeName) {
            if (empty($gradeRows[$gradeName]['GradeValueData'])) {
                $gradeRows[$gradeName]['GradeValueData'][] = 0;
               // $gradeRows[$gradeName]['GradeValueData'][] = ['MCS' => '0','INKGS' => '0'];
            }
            $GradesData[] = $gradeRows[$gradeName];
        }
        return $GradesData;
    }
    public function getVarietyCombinationData($Query, $PackingTypeID, $ProductionDate, $FreezingType , $table = '') //
    {
        $join = '';
        if($table == ''){
            $table = 'CsStockData';
            //$join = '  LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID ';
        }
        $sqlVarietyFields = "CPID.PackingStyleID, APS.PackingStyle,APS.Uom, PIVL.ShortName, CPID.SockingType, PIVL.Species, PIVL.Glaze, PIVL.GlazePerc
                            ,APS.`TotalSlabs`,APS.`SlabWeight`,CPID.Grade,CPID.VarietyID,APS.MFactor,PIVL.PreparationType";
        $sqlVarietySelect = "SELECT $sqlVarietyFields FROM `$table` as CPID
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        $join
        WHERE CPID.ProductionDate <= '$ProductionDate' $Query  ";
        if($FreezingType){
            $sqlVarietySelect .= " AND CPID.FreezingType = '$FreezingType'  ";
        }
        
        $sqlVarietySelect .= " AND CPID.PackingTypeID = '$PackingTypeID'
        GROUP BY APS.PackingStyle, PIVL.Species, PIVL.ShortName,CPID.SockingType -- , PIVL.Glaze
        HAVING SUM( CPID.AvalMcs + (CPID.LooseBags / NULLIF(APS.TotalSlabs, 0)) ) != 0
        ORDER BY PIVL.ShortName";
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
            $ArrayVarietyData[] = $ArrayVarietyList;
        }
        return $ArrayVarietyData;
    }
    
        
    public function getVarietywiseTotals($Type, $PackingTypeID, $Query, $ProductionDate, $FreezingType, $Combinations = null) //
    {
        if ($Combinations === null) {
            $Combinations = $this->getVarietyCombinationData($Query, $PackingTypeID, $ProductionDate, $FreezingType);
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
             PIVL.Species = '$Species' AND CPID.SockingType = '$SockingType' ";
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
             $CombinationQuery AND CPID.PackingTypeID = '$PackingTypeID' $Query ";
             if($FreezingType){
                $sqlInSelect .= " AND CPID.FreezingType = '$FreezingType' ";
             }
             // echo '<pre>';print_r($sqlInSelect);die;
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

            
            $TotalValues[] = !empty($Mcs) ? $Mcs : 0;
            /* $TotalValues[] = [
                'MCS' => !empty($Mcs) ? $Mcs : 0,
                'INKGS' => !empty($inKgs1) ? round($inKgs1, 3) : 0,
            ]; */
            
        }
        return $TotalValues;
    }
    
}
