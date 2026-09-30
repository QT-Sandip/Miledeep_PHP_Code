<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';
class VarietywiseStockReportNewController
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
        $Query = '';
        $QueryTwo = '';
        if ($ColdstoreID != '') {
            $Query .= " AND CPID.UnitID = '$ColdstoreID'";
        }
        if ($PlantID != '') {
            $Query .= " AND CPID.PlantID = '$PlantID'";
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
        $GrandTotals = $this->getGrandTotalsNew($Type, $QueryTwo, $Query, $ProductionDate, $RequestBody);
        $ReturnCorgos = $this->GetReturnCargos($RequestBody);
        $AntiBioticData = $this->getAntiBioticData($RequestBody);
        $ShipmentData = $this->getShipmentData($ProductionDate, $RequestBody); 
        $IBTInData = $this->getIBTInDataByDate($ProductionDate, $Query);
         $IBTOutData = $this->getIBTOutDataByDate($ProductionDate, $Query);
        $ConversionsData = $this->getConversionsDataByDate($ProductionDate, $Query);
        //ECHO '<PRE>';print_r($AntiBioticData);
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['Data'] = $ArrayTypeData;
        $Data['ReturnCorgosData'] = $ReturnCorgos;
        $Data['AntiBioticData'] = $AntiBioticData;
        $Data['GrandTotals'] = $GrandTotals;
        $Data['ShipmentData'] = $ShipmentData;
        $Data['IBTInData'] = $IBTInData;
        $Data['ConversionsData'] = $ConversionsData;
        $Data['IBTOutData'] = $IBTOutData;
        return $Data;
    }
    public function getGradeNames($Query, $TypeProduct, $Type, $ProductionDate, $ShortNameRow, $SockingType, $Combinations = null) //
    {
        $sqlGrades = "SELECT DISTINCT CPID.Grade AS GradeName FROM `CsStockData` AS CPID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID=PIVL.VarietyID
        LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
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
            $CombinationQuery = " AND APS.PackingStyle = '$PackingStyle' AND PIVL.ShortName ='$ShortName' AND
            CPID.SockingType = '$SockingType' AND PIVL.Species = '$Species' AND PIVL.Glaze = '$Glaze'";
            $inByGrade = [];
            $outByGrade = [];
            $sqlInFields = "CPID.Grade AS GradeName, SUM(ActualMcs) AS AvalMcs,SUM(ActualLooseBags) AS LooseBags, SUM(ActualKgs) AS AvalKgs,
            FLOOR(SUM(ActualLooseBags)/TotalSlabs) AS LooseMcs";
            $sqlInSelect = "SELECT $sqlInFields FROM `CsStockData` as CPID
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
            WHERE CPID.ProductionDate <= '$ProductionDate' $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query
            GROUP BY CPID.Grade";
            $stmtInSelect = $this->conn->prepare($sqlInSelect);
            $stmtInSelect->execute();
            while ($rowInValue = $stmtInSelect->fetch(PDO::FETCH_ASSOC)) {
                $inByGrade[$rowInValue['GradeName']] = [
                    'Mcs' => (($rowInValue['AvalMcs'] ? $rowInValue['AvalMcs'] : 0) + ($rowInValue['LooseMcs'] ? $rowInValue['LooseMcs'] : 0)),
                    'Kgs' => ($rowInValue['AvalKgs'] ? $rowInValue['AvalKgs'] : 0)
                ];
            }
            $sqlOutFields = "CPID.Grade AS GradeName, SUM(OutMcs) as OutMcs, SUM(OutLooseBags) AS OutLooseBags,
            SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS OutKGs, SUM(OutLooseBags*SlabWeight*MFactor) AS OutLKGs,
            FLOOR(SUM(OutLooseBags)/TotalSlabs) AS OutLooseMcs";
            $sqlOutSelect = "SELECT $sqlOutFields FROM CsStockOutDetails AS CSOD
            LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
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
                if ($Type == 'MCS') {
                    $gradeRows[$gradeName]['GradeValueData'][] = ['Value' => intval($inMcs - $outMcs)];
                } else if ($Type == 'KGs') {
                    $gradeRows[$gradeName]['GradeValueData'][] = ['Value' => round($inKgs - $outKgs, 3)];
                } else {
                    $gradeRows[$gradeName]['GradeValueData'][] = ['Value' => '0'];
                }
            }
        }
        $GradesData = [];
        foreach ($gradeOrder as $gradeName) {
            if (empty($gradeRows[$gradeName]['GradeValueData'])) {
                $gradeRows[$gradeName]['GradeValueData'][] = ['Value' => '0'];
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
            $join = '  LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID ';
        }
        $sqlVarietyFields = "CPID.PackingStyleID, APS.PackingStyle, PIVL.ShortName, CPID.SockingType, PIVL.Species, PIVL.Glaze, PIVL.GlazePerc";
        $sqlVarietySelect = "SELECT $sqlVarietyFields FROM `$table` as CPID
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        $join
        WHERE CPID.ProductionDate <= '$ProductionDate' $Query  AND PIVL.TypeProduct = '$TypeProduct'
        AND PIVL.ShortName = '$ShortName' AND CPID.SockingType = '$SockingType'
        GROUP BY CPID.PackingStyleID, PIVL.Species, PIVL.Glaze
        ORDER BY PIVL.ShortName, -- CPID.PackingStyleID
        $this->PackingStyleOrderBy";
        // GROUP BY CPID.PackingStyleID, PIVL.ShortName, CPID.SockingType, PIVL.Species, PIVL.Glaze ORDER BY PIVL.ShortName, CPID.PackingStyleID";
        $stmtVarietySelect = $this->conn->prepare($sqlVarietySelect);
        $stmtVarietySelect->execute();
        $ArrayVarietyData = [];
        while ($rowVarietywise = $stmtVarietySelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayVarietyList['PackingStyle'] = $rowVarietywise['PackingStyle'];
            $ArrayVarietyList['PackingStyleID'] = $rowVarietywise['PackingStyleID'];
            $ArrayVarietyList['ShortName'] = $rowVarietywise['ShortName'];
            $ArrayVarietyList['Species'] = $rowVarietywise['Species'];
            $ArrayVarietyList['SockingType'] = $rowVarietywise['SockingType'];
            $ArrayVarietyList['Glaze'] = $rowVarietywise['Glaze'];
            $ArrayVarietyList['GlazePerc'] = $rowVarietywise['GlazePerc'];
            $ArrayVarietyData[] = $ArrayVarietyList;
        }
        return $ArrayVarietyData;
    }
    public function getGradewiseValues($CombinationQuery, $Grade, $Type, $TypeProduct, $Query, $ProductionDate) //
    {
        $sqlInFields = "SUM(ActualMcs) AS AvalMcs,SUM(ActualLooseBags) AS LooseBags, SUM(ActualKgs) AS AvalKgs,
        FLOOR(SUM(ActualLooseBags)/TotalSlabs) AS LooseMcs";
        $sqlInSelect = "SELECT $sqlInFields FROM `CsStockData` as CPID
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CPID.ProductionDate <= '$ProductionDate' AND CPID.Grade = '$Grade' $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query";
        $stmtInSelect = $this->conn->prepare($sqlInSelect);
        $stmtInSelect->execute();
        $sqlOutFields = "SUM(OutMcs) as OutMcs, SUM(OutLooseBags) AS OutLooseBags,
        SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS OutKGs, SUM(OutLooseBags*SlabWeight*MFactor) AS OutLKGs,
        FLOOR(SUM(OutLooseBags)/TotalSlabs) AS OutLooseMcs";
        $sqlOutSelect = "SELECT $sqlOutFields FROM CsStockOutDetails AS CSOD
        LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CSOD.OutDate <= '$ProductionDate' AND CPID.Grade = '$Grade' $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query";
        $stmtOutSelect = $this->conn->prepare($sqlOutSelect);
        $stmtOutSelect->execute();
        $ArrayInData = [];
        $ArrayOutData = [];
        $Value = ['Value' => '0'];
        while ($rowInValue = $stmtInSelect->fetch(PDO::FETCH_ASSOC)) {
            $InMcs = $rowInValue['AvalMcs'] + $rowInValue['LooseMcs'];
            $ArrayInList['InMcs'] = $InMcs ? $InMcs : '0';
            $ArrayInList['InLooseBags'] = $rowInValue['LooseBags'] ? $rowInValue['LooseBags'] : '0';
            $ArrayInList['InKgs'] = $rowInValue['AvalKgs'] ? $rowInValue['AvalKgs'] : '0';
            $ArrayInData[] = $ArrayInList;
        }
        while ($rowOutValue = $stmtOutSelect->fetch(PDO::FETCH_ASSOC)) {
            $OutMcs = $rowOutValue['OutMcs'] + $rowOutValue['OutLooseMcs'];
            $ArrayOutList['OutMcs'] = $OutMcs ? $OutMcs : '0';
            $ArrayOutList['OutLooseBags'] = $rowOutValue['OutLooseBags'] ? $rowOutValue['OutLooseBags'] : '0';
            $KGs = $rowOutValue['OutKGs'] + $rowOutValue['OutLKGs'];
            $ArrayOutList['OutLKGs'] = $KGs ? $KGs : '0';
            $ArrayOutData[] = $ArrayOutList;
        }
        foreach ($ArrayInData as $inValue) {
            foreach ($ArrayOutData as $outValue) {
                $Mcs = $inValue['InMcs'] - $outValue['OutMcs'];
                $LooseBags = $inValue['InLooseBags'] - $outValue['OutLooseBags'];
                $Kgs = $inValue['InKgs'] - $outValue['OutKGs'];
            }
        }
        if ($Type == 'MCS') {
            if ($Mcs != 0) {
                /* if($Mcs == -790){
                    echo $sqlInSelect.'<br>'. $sqlOutSelect;die;
                } */
                $Value['Value'] = intval($Mcs);
            } else {
                $Value['Value'] = 0;
            }
        } else if ($Type == 'KGs') {
            $Value['Value'] = $Kgs ? round($Kgs, 3) : '0';
        }
        return $Value;
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
            $CombinationQuery = " AND APS.PackingStyle = '$PackingStyle' AND PIVL.ShortName ='$ShortName' AND
            CPID.SockingType = '$SockingType' AND PIVL.Species = '$Species' AND PIVL.Glaze = '$Glaze'";
            $sqlInFields = "SUM(ActualMcs) AS AvalMcs, SUM(ActualLooseBags) AS LooseBags, SUM(ActualKgs) AS AvalKgs,
            FLOOR(SUM(ActualLooseBags)/TotalSlabs) AS LooseSlabs";
            $sqlInSelect = "SELECT $sqlInFields FROM `CsStockData` as CPID
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
            WHERE CPID.ProductionDate <= '$ProductionDate' $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query";
            $stmtInSelect = $this->conn->prepare($sqlInSelect);
            $stmtInSelect->execute();
            $sqlOutFields = "SUM(OutMcs) as OutMcs, SUM(OutLooseBags) AS OutLooseBags,
            SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS OutKGs, SUM(OutLooseBags*SlabWeight*MFactor) AS OutLKGs,
            FLOOR(SUM(OutLooseBags)/TotalSlabs) AS OutLooseMcs";
            $sqlOutSelect = "SELECT $sqlOutFields FROM CsStockOutDetails AS CSOD
            LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
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
            if ($Type == 'MCS') {
                if ($Mcs != 0) {
                    $TotalValue['TotalValue'] = intval($Mcs);
                } else {
                    $TotalValue['TotalValue'] = '0';
                }
            } else if ($Type == 'KGs') {
                $TotalValue['TotalValue'] = $Kgs ? round($Kgs, 2) : '0';
            }
            $TotalValues[] = [
                'TotalValue' => $TotalValue['TotalValue']
            ];
        }
        return $TotalValues;
    }
    function getGrandTotals($Type, $QueryTwo, $Query, $ProductionDate, $RequestBody) //
    {
        $Variety = $RequestBody['Variety'];
        $Treatment = $RequestBody['Treatment'];
        if ($Variety != '') {
            $Query .= " AND ShortName = '$Variety'";
        }
        if ($Treatment != '') {
            $Query .= " AND SockingType = '$Treatment'";
        }
        $sqlType = "SELECT TypeProduct, FreezingType FROM ScrmPIVarietyList WHERE 1=1 $QueryTwo GROUP BY TypeProduct, FreezingType
                    $this->VarietyOrderBy";
        $stmtType = $this->conn->prepare($sqlType);
        $stmtType->execute();
        $ArrayTypeData = [];
        $ArrayInData = [];
        $ArrayOutData = [];
        while ($rowType = $stmtType->fetch(PDO::FETCH_ASSOC)) {
            $TypeProduct = $rowType['TypeProduct'];
            $FreezingType = $rowType['FreezingType'];
            $TypeProductCombination = $rowType['TypeProduct'] . '<br>' . $rowType['FreezingType'];
            $sqlInFields = "SUM(ActualMcs) AS AvalMcs, SUM(ActualLooseBags) AS LooseBags, SUM(ActualKgs) AS AvalKgs,
            FLOOR(SUM(ActualLooseBags)/TotalSlabs) AS LooseMcs";
            $sqlInSelect = "SELECT $sqlInFields FROM `CsStockData` AS CPID
            LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID = PIVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
            WHERE CPID.ProductionDate <= '$ProductionDate' AND  PIVL.TypeProduct = '$TypeProduct' AND PIVL.FreezingType = '$FreezingType' $Query";
            $stmtInSelect = $this->conn->prepare($sqlInSelect);
            $stmtInSelect->execute();
            // $errorInfo = $stmtInSelect->errorInfo();
            // var_dump($errorInfo);
            $sqlOutFields = "SUM(OutMcs) as OutMcs, SUM(OutLooseBags) AS OutLooseBags,
            SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS OutKGs, SUM(OutLooseBags*SlabWeight*MFactor) AS OutLKGs,
            FLOOR(SUM(OutLooseBags)/TotalSlabs) AS OutLooseMcs";
            $sqlOutSelect = "SELECT $sqlOutFields FROM CsStockOutDetails AS CSOD
            LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
            LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID = PIVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
            WHERE CSOD.OutDate <= '$ProductionDate' AND PIVL.TypeProduct = '$TypeProduct' AND PIVL.FreezingType = '$FreezingType' $Query";
            $stmtOutSelect = $this->conn->prepare($sqlOutSelect);
            $stmtOutSelect->execute();
            $Value = 0;
            while ($rowInValue = $stmtInSelect->fetch(PDO::FETCH_ASSOC)) {
                $InMcs = $rowInValue['AvalMcs'] + $rowInValue['LooseMcs'];
                $ArrayInList['InMcs'] = $InMcs ? $InMcs : '0';
                $ArrayInList['InLooseBags'] = $rowInValue['LooseBags'] ? $rowInValue['LooseBags'] : '0';
                $ArrayInList['InKgs'] = $rowInValue['AvalKgs'] ? $rowInValue['AvalKgs'] : '0';
                $ArrayInData[] = $ArrayInList;
            }
            while ($rowOutValue = $stmtOutSelect->fetch(PDO::FETCH_ASSOC)) {
                $OutMcs = $rowOutValue['OutMcs'] + $rowOutValue['OutLooseMcs'];
                $ArrayOutList['OutMcs'] = $OutMcs ? $OutMcs : '0';
                $ArrayOutList['OutLooseBags'] = $rowOutValue['LooseBags'] ? $rowOutValue['OutLooseBags'] : '0';
                $KGs = $rowOutValue['OutKGs'] + $rowOutValue['OutLKGs'];
                $ArrayOutList['OutKGs'] = $KGs ? $KGs : '0';
                $ArrayOutData[] = $ArrayOutList;
            }
            foreach ($ArrayInData as $inValue) {
                foreach ($ArrayOutData as $outValue) {
                    $Mcs = $inValue['InMcs'] - $outValue['OutMcs'];
                    $LooseBags = $inValue['InLooseBags'] - $outValue['OutLooseBags'];
                    $Kgs = $inValue['InKgs'] - $outValue['OutKGs'];
                }
            }
            if ($Type == 'MCS') {
                if ($Mcs != 0) {
                    $Value = intval($Mcs);
                } else {
                    $Value = 0;
                }
            } else if ($Type == 'KGs') {
                $Value = $Kgs ? round($Kgs, 3) : '0';
            }
            if ($Value != 0) {
                $ArrayTypeData[$TypeProductCombination]['Value'] = $Value;
            }
        }
        return $ArrayTypeData;
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
               $CombinationQuery = " AND APS.PackingStyle='$PackingStyle' AND SPVL.ShortName='$ShortName' AND
            SPVL.Treatment='$SockingType' AND SPVL.Species='$Species' AND SPVL.Glaze='$Glaze'";
            // $CombinationQuery = " AND APS.PackingStyle='$PackingStyle' AND SPVL.Treatment='$SockingType'
            // AND SPVL.ShortName='$ShortName' AND SPVL.Species='$Species' AND SPVL.Glaze='$Glaze'";
            $sqlInSelect = "SELECT sum(ProductionWeight) AS ProductionWeight,APS.MFactor,APS.SlabWeight,APS.TotalSlabs FROM `ProdFreezingData` AS PFD
            LEFT JOIN ScrmPIVarietyList AS SPVL ON PFD.VarietyID=SPVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON PFD.PackingStyleID=APS.PCKID
            WHERE ProductionDate = '$ProductionDate' $CombinationQuery $Query";
            // echo $sqlInSelect;die;
            $stmtInSelect = $this->conn->prepare($sqlInSelect);
            $stmtInSelect->execute();
            $FreezingValue = $stmtInSelect->fetch(PDO::FETCH_ASSOC);
            $ProductionWeight = isset($FreezingValue['ProductionWeight']) ? $FreezingValue['ProductionWeight'] : 0;
            if ($Type == 'MCS') {
                if($ProductionWeight > 0){//6152.4
                    $slabs = ((($ProductionWeight / $FreezingValue['MFactor']) / $FreezingValue['SlabWeight']) / $FreezingValue['TotalSlabs']);
                    $TotalValues[] = [
                        'FreezingWeight' => round($slabs,2)
                    ];
                }else{
                    $TotalValues[] = [
                        'FreezingWeight' => 0
                    ];
                }
            }else{
                $TotalValues[] = [
                    'FreezingWeight' => round($ProductionWeight,2)
                ];
            }
        }
        return $TotalValues;
    }
    function getAntiBioticData($RequestBody){
        $ProductionDate = $RequestBody['ProductionDate'];
        $ColdstoreID = $RequestBody['ColdstoreID'];
        $PlantID = $RequestBody['PlantID'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $Type = $RequestBody['Type'];//mcs ot kgs
        $FreezingType = $RequestBody['FreezingType'];
        $Variety = $RequestBody['Variety'];
        $Treatment = $RequestBody['Treatment'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $Grade = $RequestBody['Grade'];
        $Species  = $RequestBody['Species'];
        $Query = '';
        $QueryTwo = '';
        if ($ColdstoreID != '') {
             $Query .= " AND CPID.UnitID = '$ColdstoreID'";
        }
        if ($PlantID != '') {
            $Query .= " AND CPID.PlantID = '$PlantID'";
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
        if ($Treatment != '') {
            $QueryTwo .= " AND SockingType = '$Treatment'";
            $QueryReplace = str_replace(" AND SockingType = '$Treatment'", '', $QueryTwo);
        }
        $sqlType = "SELECT TypeProduct, ShortName, Treatment FROM ScrmPIVarietyList SPVL
                    LEFT JOIN ProdFreezingData PFD ON PFD.`VarietyID`= SPVL.`VarietyID`
                    WHERE 1=1 AND PFD. AbStatus='AB+' $QueryReplace ";
        if ($Treatment != '') {
            $sqlType .= " AND Treatment = '$Treatment' ";
        }
        $sqlType .= " GROUP BY TypeProduct, ShortName, Treatment
                    $this->VarietyOrderBy";
        $stmtType = $this->conn->prepare($sqlType);
        $stmtType->execute();
        //echo '<pre>';print_r($sqlType);die;
        $table = 'ProdFreezingData';
        $Query .= " AND AbStatus='AB+' ";
        $ArrayTypeData = [];
        while ($rowType = $stmtType->fetch(PDO::FETCH_ASSOC)) {
            $TypeProductRow = $rowType['TypeProduct'];
            $ShortNameRow = $rowType['ShortName'];
            $SockingTypeRow = $rowType['Treatment'];
            $VarietyCombinationData = $this->getVarietyCombinationData($Query, $TypeProductRow, $ProductionDate, $ShortNameRow, $SockingTypeRow, $table);
            $GradesData = $this->AbGradeNames($Query, $TypeProductRow, $Type, $ProductionDate, $ShortNameRow, $SockingTypeRow, $table, $VarietyCombinationData);
            $GrandTotals = $this->getVarietywiseAbTotals($Type, $TypeProductRow, $Query, $ProductionDate, $ShortNameRow, $SockingTypeRow, $table, $VarietyCombinationData);
            $FreezingData = $this->getVarietywiseFreezingData($Type, $TypeProductRow, $Query, $ProductionDate, $ShortNameRow, $SockingTypeRow,$ColdstoreID, $table, $VarietyCombinationData);
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
        }  //echo '<pre>';print_r($ArrayTypeData);die;
        return $ArrayTypeData;
    }
    function AbGradeNames($Query, $TypeProduct, $Type, $ProductionDate, $ShortNameRow, $SockingType, $table, $Combinations = null){
        $sqlGrades = "SELECT DISTINCT CPID.Grade AS GradeName FROM `ProdFreezingData` AS CPID
                        LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID=PIVL.VarietyID
                        WHERE CPID.ProductionDate <= '$ProductionDate' $Query AND PIVL.TypeProduct = '$TypeProduct'
                        AND PIVL.ShortName = '$ShortNameRow' AND CPID.SockingType = '$SockingType'
                        ORDER BY abs(CPID.Grade) ASC";
                       // echo $sqlGrades;die;
                        $stmtGrades = $this->conn->prepare($sqlGrades);
                        $stmtGrades->execute();
        if ($Combinations === null) {
            $Combinations = $this->getVarietyCombinationData($Query, $TypeProduct, $ProductionDate, $ShortNameRow, $SockingType, $table);
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
            $CombinationQuery = " AND APS.PackingStyle = '$PackingStyle' AND PIVL.ShortName ='$ShortName' AND
            CPID.SockingType = '$SockingType' AND PIVL.Species = '$Species' AND PIVL.Glaze = '$Glaze' ";
            $sqlFields = "CPID.Grade AS GradeName, SUM(`ProductionWeight`) `ProductionWeight`, APS.MFactor, APS.SlabWeight, APS.TotalSlabs ";
            $sqlSelect = "SELECT $sqlFields FROM `ProdFreezingData` as CPID
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            WHERE CPID.ProductionDate <= '$ProductionDate' $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query
            GROUP BY CPID.Grade";
            $stmtSelect = $this->conn->prepare($sqlSelect);
            $stmtSelect->execute();
            $valsByGrade = [];
            while ($rowValue = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
                $productionWeight = isset($rowValue['ProductionWeight']) ? $rowValue['ProductionWeight'] : 0;
                if ($Type == 'MCS') {
                    $mFactor = (isset($rowValue['MFactor']) && (float)$rowValue['MFactor'] != 0) ? $rowValue['MFactor'] : 1;
                    $slabWeight = (isset($rowValue['SlabWeight']) && (float)$rowValue['SlabWeight'] != 0) ? $rowValue['SlabWeight'] : 1;
                    $totalSlabs = (isset($rowValue['TotalSlabs']) && (float)$rowValue['TotalSlabs'] != 0) ? $rowValue['TotalSlabs'] : 1;
                    $valsByGrade[$rowValue['GradeName']] = round((($productionWeight / $mFactor) / $slabWeight) / $totalSlabs);
                } else {
                    $valsByGrade[$rowValue['GradeName']] = (int)$productionWeight;
                }
            }
            foreach ($gradeOrder as $gradeName) {
                $gradeRows[$gradeName]['GradeValueData'][] = ['Value' => isset($valsByGrade[$gradeName]) ? $valsByGrade[$gradeName] : 0];
            }
        }
        $GradesData = [];
        foreach ($gradeOrder as $gradeName) {
            if (empty($gradeRows[$gradeName]['GradeValueData'])) {
                $gradeRows[$gradeName]['GradeValueData'][] = ['Value' => '0'];
            }
            $GradesData[] = $gradeRows[$gradeName];
        }
        return $GradesData;
    }
    function getGradewiseAbValues($CombinationQuery, $Grade, $Type, $TypeProduct, $Query, $ProductionDate){
        $sqlFields = "SUM(`ProductionWeight`) `ProductionWeight`,APS.MFactor,APS.SlabWeight,APS.TotalSlabs ";
        $sqlSelect = "SELECT $sqlFields FROM `ProdFreezingData` as CPID
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        WHERE CPID.ProductionDate <= '$ProductionDate' AND CPID.Grade = '$Grade' $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query";
        //echo $sqlSelect;die;
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        $ArrayData = [];
        $Value = ['Value' => '0'];
        while ($rowValue = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            //$InMcs = $rowInValue['AvalMcs'] + $rowInValue['LooseMcs'];
            //$ArrayInList['InMcs'] = $InMcs ? $InMcs : '0';
           // $ArrayInList['ProductionWeight'] = $rowValue['ProductionWeight'] ? $rowValue['ProductionWeight'] : '0';
            //$ArrayInList['InKgs'] = $rowInValue['AvalKgs'] ? $rowInValue['AvalKgs'] : '0';
             $ProductionWeight = isset($rowValue['ProductionWeight']) ? $rowValue['ProductionWeight'] : 0;
               if ($Type == 'MCS') {
                    $slabs = round((($ProductionWeight / $rowValue['MFactor']) / $rowValue['SlabWeight']) / $rowValue['TotalSlabs']);
               }else{
                    $slabs = (int)$ProductionWeight;
               }
            $Value['Value'] = $slabs;
        }
        return $Value;
    }
    public function getVarietywiseAbTotals($Type, $TypeProduct, $Query, $ProductionDate, $ShortNameRow, $SockingType, $table, $Combinations = null) //
    {
        if ($Combinations === null) {
            $Combinations = $this->getVarietyCombinationData($Query, $TypeProduct, $ProductionDate, $ShortNameRow, $SockingType, $table);
        }
        $TotalValues = [];
        foreach ($Combinations as $Item) {
            $PackingStyle = $Item['PackingStyle'];
            $ShortName = $Item['ShortName'];
            $Species = $Item['Species'];
            $SockingType = $Item['SockingType'];
            $Glaze = $Item['Glaze'];
            $CombinationQuery = " AND APS.PackingStyle = '$PackingStyle' AND PIVL.ShortName ='$ShortName' AND
            CPID.SockingType = '$SockingType' AND PIVL.Species = '$Species' AND PIVL.Glaze = '$Glaze'";
            $sqlFields = "SUM(`ProductionWeight`) `ProductionWeight`,APS.MFactor,APS.SlabWeight,APS.TotalSlabs ";
            $sqlSelect = "SELECT $sqlFields FROM `ProdFreezingData` as CPID
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            WHERE CPID.ProductionDate <= '$ProductionDate' $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query";
            $stmtSelect = $this->conn->prepare($sqlSelect);
            $stmtSelect->execute();
            $TotalValue = ['TotalValue' => '0'];
            $slabs = 0;
            while ($rowTotalValue = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
                $ProductionWeight = isset($rowTotalValue['ProductionWeight']) ? $rowTotalValue['ProductionWeight'] : 0;
               if ($Type == 'MCS') {
                    $slabs = round(((($ProductionWeight / $rowTotalValue['MFactor']) / $rowTotalValue['SlabWeight']) / $rowTotalValue['TotalSlabs']),2);
               }else{
                    $slabs = round($ProductionWeight,2);
               }
                $TotalValue['TotalValue'] += $slabs;
            }
            $TotalValues[] = [
                'TotalValue' => $TotalValue['TotalValue']
            ];
        }
        return $TotalValues;
    }
    function GetReturnCargos($RequestBody){
        $ProductionDate = $RequestBody['ProductionDate'];
        $ColdstoreID = $RequestBody['ColdstoreID'];
        $PlantID = $RequestBody['PlantID'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $Type = $RequestBody['Type'];//mcs ot kgs
        $FreezingType = $RequestBody['FreezingType'];
        $Variety = $RequestBody['Variety'];
        $Treatment = $RequestBody['Treatment'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $Grade = $RequestBody['Grade'];
        $Species  = $RequestBody['Species'];
        $Query = $QueryTwo = " AND `ProductionTypeID`='Return Container' ";
        if ($ColdstoreID != '') {
             $Query .= " AND CPID.UnitID = '$ColdstoreID'";
        }
        if ($PlantID != '') {
            $Query .= " AND CPID.PlantID = '$PlantID'";
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
        if ($Treatment != '') {
            $QueryTwo .= " AND SockingType = '$Treatment'";
            $QueryReplace = str_replace(" AND SockingType = '$Treatment'", '', $QueryTwo);
        }
        $sqlType = "SELECT  PIVL.TypeProduct, PIVL.ShortName, PIVL.Treatment FROM `CsStockData` AS CPID
                    LEFT JOIN ScrmPIVarietyList PIVL ON CPID.VarietyID=PIVL.VarietyID
                    WHERE CPID.ProductionDate <= '$ProductionDate' $Query $QueryReplace ";
        if ($Treatment != '') {
            $sqlType .= " AND PIVL.Treatment = '$Treatment' ";
        }
        $sqlType .= "
                    GROUP BY TypeProduct, ShortName, Treatment
                    $this->VarietyOrderBy";
        //echo '<pre>';print_r($sqlType);die;
        $stmtType = $this->conn->prepare($sqlType);
        $stmtType->execute();
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
        return $ArrayTypeData;
    }
    function getGrandTotalsNew($Type, $QueryTwo, $Query, $ProductionDate, $RequestBody) // 
    {
        $Variety = $RequestBody['Variety'];
        $Treatment = $RequestBody['Treatment'];
        if ($Variety != '') {
            $Query .= " AND ShortName = '$Variety'";
        }
        if ($Treatment != '') {
            $Query .= " AND SockingType = '$Treatment'";
        }
        $sqlType = "SELECT TypeProduct, FreezingType FROM ScrmPIVarietyList WHERE 1=1 $QueryTwo GROUP BY TypeProduct, FreezingType
                    $this->VarietyOrderBy";
        $stmtType = $this->conn->prepare($sqlType);
        $stmtType->execute();
        $ArrayTypeData = [];
        $ArrayInData = [];
        $ArrayOutData = [];
        while ($rowType = $stmtType->fetch(PDO::FETCH_ASSOC)) {
            $TypeProduct = $rowType['TypeProduct'];
            $FreezingType = $rowType['FreezingType'];
            $TypeProductCombination = $rowType['TypeProduct'] . ' - ' . $rowType['FreezingType'];
            $Condition = '<=';//for all day
            $value1 = $this->getTotalBasedOnFiltersNew($ProductionDate, $TypeProduct, $FreezingType, $Type, $Query, $Condition);
            $Condition = '=';//for single day
            $value2 = $this->getTotalBasedOnFiltersNew($ProductionDate, $TypeProduct, $FreezingType, $Type, $Query, $Condition);
            if ($value1 != 0) {
                $ArrayTypeData[$TypeProductCombination]['Value'] = $value1;                
            }
            if ($value2 != 0) {
                $ArrayTypeData[$TypeProductCombination]['singleDayValue'] = $value2;
            }
        }
        return $ArrayTypeData;
    }
    function getTotalBasedOnFilters($ProductionDate, $TypeProduct, $FreezingType, $Type, $Query, $Condition){
        $sqlInFields = "SUM(ActualMcs) AS AvalMcs, SUM(ActualLooseBags) AS LooseBags, SUM(ActualKgs) AS AvalKgs,
        FLOOR(SUM(ActualLooseBags)/TotalSlabs) AS LooseMcs,TotalSlabs";
        $sqlInSelect = "SELECT $sqlInFields FROM `CsStockData` AS CPID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID = PIVL.VarietyID  
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CPID.ProductionDate $Condition '$ProductionDate' AND  PIVL.TypeProduct = '$TypeProduct' AND PIVL.FreezingType = '$FreezingType' $Query";
        $stmtInSelect = $this->conn->prepare($sqlInSelect);
        $stmtInSelect->execute();
        // $errorInfo = $stmtInSelect->errorInfo();
        // var_dump($errorInfo);
        $sqlOutFields = "SUM(OutMcs) as OutMcs, SUM(OutLooseBags) AS OutLooseBags, 
        SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS OutKGs, SUM(OutLooseBags*SlabWeight*MFactor) AS OutLKGs,
        FLOOR(SUM(OutLooseBags)/TotalSlabs) AS OutLooseMcs,TotalSlabs";
        $sqlOutSelect = "SELECT $sqlOutFields FROM CsStockOutDetails AS CSOD
        LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID = PIVL.VarietyID  
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CSOD.OutDate $Condition '$ProductionDate' AND PIVL.TypeProduct = '$TypeProduct' AND PIVL.FreezingType = '$FreezingType' $Query";
        $stmtOutSelect = $this->conn->prepare($sqlOutSelect);
        $stmtOutSelect->execute();
        $Value = 0;
        while ($rowInValue = $stmtInSelect->fetch(PDO::FETCH_ASSOC)) {
            $TotalSlabs = $rowInValue['TotalSlabs'];
            //$InMcs = floor((($rowInValue['AvalMcs'] * $TotalSlabs) + $rowInValue['LooseBags']) / $TotalSlabs);
            $InMcs = $rowInValue['AvalMcs'] + $rowInValue['LooseMcs'];
            $ArrayInList['InMcs'] = $InMcs ? $InMcs : '0';
            $ArrayInList['InLooseBags'] = $rowInValue['LooseBags'] ? $rowInValue['LooseBags'] : '0';
            $ArrayInList['InKgs'] = $rowInValue['AvalKgs'] ? $rowInValue['AvalKgs'] : '0';
            $ArrayInData[] = $ArrayInList;
        }
        while ($rowOutValue = $stmtOutSelect->fetch(PDO::FETCH_ASSOC)) {
            $TotalSlabs = $rowOutValue['TotalSlabs'];
            //$OutMcs = floor((($rowOutValue['OutMcs'] * $TotalSlabs) + $rowOutValue['OutLooseBags']) / $TotalSlabs);
            $OutMcs = $rowOutValue['OutMcs'] + $rowOutValue['OutLooseMcs'];
            $ArrayOutList['OutMcs'] = $OutMcs ? $OutMcs : '0';
            $ArrayOutList['OutLooseBags'] = $rowOutValue['OutLooseBags'] ? $rowOutValue['OutLooseBags'] : '0';
            $KGs = $rowOutValue['OutKGs'] + $rowOutValue['OutLKGs'];
            $ArrayOutList['OutKGs'] = $KGs ? $KGs : '0';
            $ArrayOutData[] = $ArrayOutList;
        }
        foreach ($ArrayInData as $inValue) {
            foreach ($ArrayOutData as $outValue) {
                $Mcs = $inValue['InMcs'] - $outValue['OutMcs'];
                $LooseBags = $inValue['InLooseBags'] - $outValue['OutLooseBags'];
                $Kgs = $inValue['InKgs'] - $outValue['OutKGs'];
            }
        }
        if ($Type == 'MCS') {
            if ($Mcs != 0) {
                $Value = intval($Mcs);
            } else {
                $Value = 0;
            }
        } else if ($Type == 'KGs') {
            $Value = $Kgs ? round($Kgs, 3) : '0';
        }
        return $Value;
    }
    function getTotalBasedOnFiltersNew($ProductionDate, $TypeProduct, $FreezingType, $Type, $Query, $Condition){
       //openeng and closing report logic here
        $sqlInFields = "GROUP_CONCAT(DISTINCT StockID)as StockIDs,APS.TotalSlabs as PckTotalSlabs,
        SUM(ActualMcs) AS Mcs, SUM(ActualLooseBags) AS LooseBags,
        SUM(ActualKgs) AS Kgs,
        SUM(ActualMcs*TotalSlabs) AS McsSlabls";
        $sqlInSelect = "SELECT $sqlInFields FROM `CsStockData` AS CPID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID = PIVL.VarietyID  
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CPID.ProductionDate $Condition '$ProductionDate' AND `ProductionTypeID`='Production' AND  PIVL.TypeProduct = '$TypeProduct' AND PIVL.FreezingType = '$FreezingType' $Query
        GROUP BY PIVL.ShortName, CPID.PackingStyleID, PIVL.Treatment,PIVL.TypeProduct,CPID.Grade,PIVL.Glaze,Species";
        $stmtInSelect = $this->conn->prepare($sqlInSelect);
        $stmtInSelect->execute();
        // $errorInfo = $stmtInSelect->errorInfo();
        // var_dump($errorInfo);
        $Value = 0;$opnMcs = $outMcs = 0;
        while ($rowSlabs = $stmtInSelect->fetch(PDO::FETCH_ASSOC)) {
            $StockIDs = $rowSlabs['StockIDs'];
            $McsSlabls = $rowSlabs['McsSlabls'];//3944
            $LooseBags = $rowSlabs['LooseBags'];//0
            $TotalKgs = $rowSlabs['Kgs'];
            $PckTotalSlabs = $rowSlabs['PckTotalSlabs'];//1
            $TotalOpeningSlabs = $McsSlabls + $LooseBags;//16944 + 19 = 16963//3944
            $arrayStockOutData = $this->getOutStock($ProductionDate, $StockIDs, $Condition);
            //echo '<pre>';print_r( $arrayStockOutData);die;
            $StockOutMcs = isset($arrayStockOutData['OutMcs']) ? $arrayStockOutData['OutMcs'] : 0;// 4403
            $StockOutLooseBags = isset($arrayStockOutData['OutLooseBags']) ? $arrayStockOutData['OutLooseBags'] : 0;// 519
             $StockOutMcsSlabls = $StockOutMcs * $PckTotalSlabs;// 2379 * 6 = 14274//4403
            $TotalOutKgs = $arrayStockOutData['OutKgs'];
            $TotalOutSlabs = $StockOutMcsSlabls + $StockOutLooseBags;// 14274 + 6 = 14280//4922
            $BalanceKgs = round($TotalKgs, 2) - round($TotalOutKgs, 2);
            //$TotalOpeningMcs = floor($TotalOpeningSlabs / $PckTotalSlabs); //16963 / 6 = 2827//3944
            //$TotalOutMcs = floor($TotalOutSlabs / $PckTotalSlabs);// 14280/6 = 2380//4922
            $TotalOpeningMcs = round(($TotalOpeningSlabs / $PckTotalSlabs), 2); //16963 / 6 = 2827//3944
            $TotalOutMcs = round(($TotalOutSlabs / $PckTotalSlabs), 2);// 14280/6 = 2380//4922
            $openingBalMcs = $TotalOpeningMcs - $TotalOutMcs; // 2827 - 2380 = 447
            /* if($Condition != '='){
                $openingBalMcs = $TotalOpeningMcs - $TotalOutMcs; // 2827 - 2380 = 447
            }else{
                $openingBalMcs = $TotalOpeningMcs;
            } */
            
           // $openingBalSlabs = $TotalOpeningSlabs - $TotalOutSlabs; //16963 - 14280 = 268
            if ($Type == 'MCS') {
                $Value += $openingBalMcs;
            } else {
                $Value += round($BalanceKgs, 3);
            }
        }
         return $Value;
    }
    function getOutStock($ProductionDate, $StockIds, $Condition){
        if ($StockType == 'ClosingDate') {
            $DateFilter = "OutDate $Condition'$ProductionDate'";
        } else {
            $DateFilter = "OutDate $Condition'$ProductionDate'";
        }
        $sqlOutMcs = "SELECT sum(OutMcs) as  OutMcs,sum(OutLooseBags) as OutLooseBags,SUM(ActualOutKgs) AS OutKgs  from CsStockOutDetails WHERE StockID in($StockIds) 
        and $DateFilter";
        // echo '<pre>';print_r( $sqlOutMcs);die;
        $stmtOutMcs = $this->conn->prepare($sqlOutMcs);
        $stmtOutMcs->execute();
        $rowOutMcs = $stmtOutMcs->fetch(PDO::FETCH_ASSOC);
        return $rowOutMcs;
    }
    function getShipmentData($ProductionDate, $RequestBody = []){
        if(!empty($ProductionDate)){
            $PlantID  = $RequestBody['PlantID'];
            $ColdstoreID = $RequestBody['ColdstoreID'];
            $where = '';
            if($PlantID){
                $where .= " AND CSD.PlantID=$PlantID ";
            }
            if($ColdstoreID){
                $where .= " AND CSD.UnitID=$ColdstoreID ";
            }
            $shipmentSql = "SELECT `InvNo`,CSD.`PoID`,OutType,`PoNo`,`InvNumFull`,SCD.`ConsigneeName`,
                            (SELECT SUM(Mcs) FROM ScrmPoGradeData WHERE InvId=SPD.`PoID`) Mcs
                            FROM CsStockOutDetails CSOD
                            RIGHT JOIN CsStockData CSD ON CSD.`StockID`=CSOD.`StockID`
                            LEFT JOIN ScrmPoData SPD ON SPD.`PoID`=CSD.`PoID`
                            LEFT JOIN ScrmConsigneeDetails SCD ON SCD.`ConID`=SPD.`ConsigneeID`
                            WHERE OutType='Shipment Out' AND `OutDate`='$ProductionDate' $where
                            GROUP BY CSD.`PoID`";
            $stmtShipment = $this->conn->prepare($shipmentSql);
            $stmtShipment->execute();
            $Value = 0;
            $ShipmentArr = [];
            while ($rowShiment = $stmtShipment->fetch(PDO::FETCH_ASSOC)) {
                //SHIPMENT LESS : STANLEY PEARLMAN ENTERPRISES, PO.NO : 248562, DFL/JPT/2526/  = 1100 M/CTNS
                $ResArr['ShipmentData'] = 'SHIPMENT LESS : '.$rowShiment['ConsigneeName'].', PO.NO : '.$rowShiment['PoNo'].', '.$rowShiment['InvNumFull'].' = '.$rowShiment['Mcs'].'  M/CTNS';
                $invNo = $rowShiment['PoID'];
                $shimpmentBreackUpSql = "SELECT PGD.`InvId`,CONCAT(
                                            (
                                            SELECT InvShortCode FROM AdminExporterDetails AED 
                                            LEFT JOIN ScrmPoData SPD  ON AED.`ExpID`=SPD.`ExporterID`
                                            WHERE SPD.`PoID`=InvId GROUP BY AED.`ExpID`
                                            )
                                            ,' : ',Species,' ',ShortName,' - ',APS.`PackingStyle`,' (',PVL.Treatment,') - ',PGD.Grade,' = ',Mcs,'  M/CTNS') AS `Description` 
                                            FROM `ScrmPoGradeData` AS PGD
                                            LEFT JOIN ScrmPIVarietyList AS PVL ON PGD.VarietyID=PVL.VarietyID
                                            LEFT JOIN AdminPackingStyles AS  APS ON APS.PCKID =PVL.PackingStyle
                                            -- left JOIN ScrmPoData SPD ON SPD.`InvNo`=PGD.`InvId`
                                            -- left join AdminExporterDetails AED ON AED.`ExpID`=SPD.`ExporterID`
                                            WHERE InvId='$invNo'  ORDER BY ID ASC";
                $stmtShipmentBreackUp = $this->conn->prepare($shimpmentBreackUpSql);
                $stmtShipmentBreackUp->execute();
                $ResArr['ShipmentBreakUp'] = [];
                while ($rowShimentBreackUp = $stmtShipmentBreackUp->fetch(PDO::FETCH_ASSOC)) {
                    $ResArr['ShipmentBreakUp'][] = $rowShimentBreackUp['Description'];
                }
                $ShipmentArr[] = $ResArr;
            }
            return $ShipmentArr;
        }
    }
    function getIBTInDataByDate($StockOutDate, $Query){
        if(!empty($StockOutDate)){            
            $IBTInSql = "SELECT CIRD.FromStore,RPS.StationName AS FromStoreName
                            FROM CsStockData AS CPID
                            LEFT JOIN CsDailyProductionData AS CSDP ON CSDP.ProductionID=CPID.ProductionID
                            LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID=CPID.VarietyID
                            LEFT JOIN CsIBTRequestDetails AS CIRD ON CSDP.IBTOutID = CIRD.IBTID
                            LEFT JOIN RmProcessingStations AS RPS ON RPS.ProcessingStationID=CIRD.FromPlantID -- FromStore
                            WHERE CPID.ProductionDate = '$StockOutDate' $Query AND CPID.ProductionTypeID = 'IBT IN' GROUP BY CIRD.FromStore";
            $stmtIBTIn = $this->conn->prepare($IBTInSql);
            $stmtIBTIn->execute();
            $IBTInArr = [];
            while ($row = $stmtIBTIn->fetch(PDO::FETCH_ASSOC)) {
                $ResArr['FromStoreName'] = 'RECEIVED FROM '.$row['FromStoreName'];
                $fromStore = $row['FromStore'];
                $IBTInBreakupSql = "SELECT 
                                            SUM(CPID.ActualMcs) AS AvalMcs, SUM(CPID.ActualLooseBags) AS LooseBags, 
                                    Species,ShortName, CPID.Grade, CPID.FreezingType, CPID.SockingType, CPID.PackingTypeID, 
                                    APS.PackingStyle, PoNo, PIVL.DeviSUPC, PIVL.ShortName,DeviSUPC,FromStore,IBTOutID,
                                    CPID.ProductionID,RPS.StationName AS FromStoreName
                                            FROM CsStockData AS CPID 
                                    LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPID.PoID
                                    LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID=CPID.VarietyID
                                    LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
                                    LEFT JOIN CsDailyProductionData AS CSDP ON CSDP.ProductionID=CPID.ProductionID
                                    LEFT JOIN CsIBTRequestDetails AS CIRD ON CSDP.IBTOutID = CIRD.IBTID
                                    left join RmProcessingStations as RPS on RPS.ProcessingStationID=CIRD.FromStore 
                                    WHERE CIRD.FromStore = $fromStore AND CPID.ProductionDate = '$StockOutDate' $Query 
                                    -- AND CPID.ProductionTypeID = 'IBT IN' 
                                    GROUP BY CPID.VarietyID";
                $stmtIBTInBreakup = $this->conn->prepare($IBTInBreakupSql);
                $stmtIBTInBreakup->execute();
                $ResArr['Breakup'] = [];
                while ($rowBreakup = $stmtIBTInBreakup->fetch(PDO::FETCH_ASSOC)) {
                    $Breakup = $rowBreakup['Species'].' '.$rowBreakup['ShortName'].' - '.$rowBreakup['PackingStyle']. ' - '.$rowBreakup['Grade'].' = '.$rowBreakup['AvalMcs'].' M/CTNS';
                    if($rowBreakup['LooseBags'] != 0){
                        $Breakup .= ' '.$rowBreakup['LooseBags'].' LOOSE';
                    }
                    $ResArr['Breakup'][] = $Breakup; 
                }
                $IBTInArr[] = $ResArr;
            }
            return $IBTInArr;
        }
    }
    function getIBTOutDataByDate($StockOutDate, $Query){
        if(!empty($StockOutDate)){            
            /* $IBTOutSql = "SELECT ToStore,RPS2.StationName AS ToStoreName, CPID.UnitID 
                            FROM CsIBTRequestBreakUpDetails AS CITBD
                            LEFT JOIN CsIBTRequestDetails AS CIRD ON CITBD.IBTRequestID = CIRD.IBTID
                            LEFT JOIN CsStockData AS CPID ON CITBD.StockID = CPID.StockID
                            LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID=CPID.VarietyID
                            LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
                            LEFT JOIN RmProcessingStations AS RPS2 ON RPS2.ProcessingStationID=CIRD.ToStore
                            WHERE CIRD.OrderDate = '$StockOutDate'  $Query  GROUP BY ToStore"; */
            $IBTOutSql = "SELECT ToStore,RPS2.StationName AS ToStoreName, CPID.UnitID 
                            FROM CsIBTRequestBreakUpDetails AS CITBD
                            LEFT JOIN CsIBTRequestDetails AS CIRD ON CITBD.IBTRequestID = CIRD.IBTID
                            LEFT JOIN CsStockData AS CPID ON CITBD.StockID = CPID.StockID
                            LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID=CPID.VarietyID
                            LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
                            LEFT JOIN RmProcessingStations AS RPS2 ON RPS2.ProcessingStationID=CIRD.ToPlantID
                            WHERE CIRD.OrderDate = '$StockOutDate'  $Query  GROUP BY ToPlantID";
            $stmtIBTOut = $this->conn->prepare($IBTOutSql);
            $stmtIBTOut->execute();
            $IBTOutArr = [];
            while ($row = $stmtIBTOut->fetch(PDO::FETCH_ASSOC)) {
                $ResArr['ToStoreName'] = 'SENT TO '.$row['ToStoreName'];
                $ToStore = $row['ToStore'];
                $sqlIBTOutFields = "FromStore, OrderDate, ToStore, CITBD.VarietyID, SUM(NoOfMcs) AS MCS, SUM(NoOfLooseBags) AS LooseBags, 
                    CITBD.StockID,Species,ShortName, CPID.Grade, APS.PackingStyle, CPID.PackingTypeID, RPS.StationName AS FromStoreName, 
                    RPS2.StationName AS ToStoreName, CPID.UnitID";
                $sqlIBTOutSelect = "SELECT $sqlIBTOutFields FROM CsIBTRequestBreakUpDetails AS CITBD
                    LEFT JOIN CsIBTRequestDetails AS CIRD ON CITBD.IBTRequestID = CIRD.IBTID
                    LEFT JOIN CsStockData AS CPID ON CITBD.StockID = CPID.StockID
                    LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID=CPID.VarietyID
                    LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
                    left join RmProcessingStations as RPS on RPS.ProcessingStationID=CIRD.FromStore 
                    left join RmProcessingStations as RPS2 on RPS2.ProcessingStationID=CIRD.ToStore
                    WHERE ToStore=$ToStore AND CIRD.OrderDate = '$StockOutDate' $Query  GROUP BY CPID.VarietyID";
                $stmtIBTOutSelect = $this->conn->prepare($sqlIBTOutSelect);
                $stmtIBTOutSelect->execute();
                $ResArr['Breakup'] = [];
                while ($rowBreakup = $stmtIBTOutSelect->fetch(PDO::FETCH_ASSOC)) {
                    $Breakup = $rowBreakup['Species'].' '.$rowBreakup['ShortName'].' - '.$rowBreakup['PackingStyle']. ' - '.$rowBreakup['Grade'].' = '.$rowBreakup['MCS'].' M/CTNS';
                    if($rowBreakup['LooseBags'] != 0){
                        $Breakup .= ' '.$rowBreakup['LooseBags'].' LOOSE';
                    }
                    $ResArr['Breakup'][] = $Breakup; 
                }
                $IBToUTArr[] = $ResArr;
            }
            return $IBToUTArr;
        }
    }
    function getConversionsDataByDate($StockOutDate, $Query){
        if(!empty($StockOutDate)){
            $conversionSql = "SELECT OutType,GROUP_CONCAT(DISTINCT CSOD.`StockOutID`) StockOutIDs,
                                GROUP_CONCAT(DISTINCT CSOD.CsStockOutID) AS CsStockOutIDs,
                                SUM(OutMcs) AS OutMcs, SUM(OutLooseBags) AS OutLooseBags, 
                            Species,ShortName, CPID.Grade, 
                            APS.PackingStyle FROM CsStockOutDetails AS CSOD 
                            LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
                            LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPID.PoID
                            LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID=CPID.VarietyID
                            LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
                            -- RIGHT JOIN CsDailyProductionData CDPD ON CDPD.StockOutID = CSOD.StockOutID
                            left join CsDailyProductionData AS CDPD ON CPID.ProductionID=CDPD.ProductionID 
                            WHERE CSOD.OutDate = '$StockOutDate'  $Query
                            AND OutType IN ('Melting OUT','Melting','Melting IN','Repacking Out','Repacking') GROUP BY CPID.VarietyID";
            $stmtConversion = $this->conn->prepare($conversionSql);
            $stmtConversion->execute();
            $CoversionArr = [];
            while ($row = $stmtConversion->fetch(PDO::FETCH_ASSOC)) {
                $Name = $row['OutType'].': '.$row['Species'].' '.$row['ShortName'].' '.$row['PackingStyle'].' '.$row['Grade'].' = '.$row['OutMcs'].'  M/CTNS';
                if($row['OutLooseBags'] != 0){
                    $Name .= ' '. $row['OutLooseBags'].' LOOSE';
                }    
                if(in_array($row['OutType'],['Melting','Melting IN','Melting OUT'])){
                    $StockOutIDs = $row['CsStockOutIDs'];
                     $sqlTrackIds = "SELECT GROUP_CONCAT(DISTINCT RRD.ReceivingID) AS TrackIds 
                                        FROM RmReceivingBreakup AS RRB 
                                        left join RmReceivingDetails AS RRD ON RRB.ReceivingID=RRD.ReceivingID 
                                        WHERE RRB.CsStockOutID IN ($StockOutIDs)";
                    // echo $sqlTrackIds;exit;
                    $stmtTrackIds = $this->conn->prepare($sqlTrackIds);
                    $stmtTrackIds->execute();
                    $rowTrackIds = $stmtTrackIds->fetch(PDO::FETCH_ASSOC);
                    $TrackIds = $rowTrackIds['TrackIds'];

                   
                    $breakUpSql = "SELECT 
                                            CPID.`StockID` , Species,ShortName, APS.PackingStyle,CPID.Grade,
                                            SUM(CPID.ActualMcs) AS AvalMcs, SUM(CPID.ActualLooseBags) AS LooseBags
                                         FROM CsStockData AS CPID
                                        LEFT JOIN CsDailyProductionData AS CDPD ON CDPD.ProductionID = CPID.ProductionID
                                        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPID.PoID
                                        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID=CPID.VarietyID
                                        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
                                        WHERE CDPD.TrackID IN ($TrackIds)
                                        GROUP BY CPID.VarietyID";
                }else{                                    
                    $StockOutIDs = $row['StockOutIDs'];
                    $breakUpSql = "SELECT CPID.`StockID` , Species,ShortName, APS.PackingStyle,CPID.Grade,
                                    SUM(CPID.ActualMcs) AS AvalMcs, SUM(CPID.ActualLooseBags) AS LooseBags
                                    FROM CsDailyProductionData CDPD 
                                    LEFT JOIN CsStockData AS CPID ON CPID.`ProductionID` = CDPD.`ProductionID`
                                    LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID=CPID.VarietyID
                                    LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
                                    WHERE CDPD.`StockOutID` IN ($StockOutIDs)
                                    GROUP BY CPID.VarietyID";
                }
                $stmtBreakUp = $this->conn->prepare($breakUpSql);
                $stmtBreakUp->execute();
                $breakupArr = [];
                while ($rowB = $stmtBreakUp->fetch(PDO::FETCH_ASSOC)) {
                    $breakUpStr = $rowB['Species'].' '.$rowB['ShortName'].' '.$rowB['PackingStyle'].' '.$rowB['Grade'].' = '.$rowB['AvalMcs'].'  M/CTNS';
                    if($rowB['LooseBags'] != 0){
                        $breakUpStr .= ' '. $rowB['LooseBags'].' LOOSE';
                    } 
                    $breakupArr[] = $breakUpStr;
                }
                if(!empty($breakupArr)){
                    $Name .= ' CNVTD TO '.implode(' / ',$breakupArr);
                }
                $ResArr['Name'] = $Name;
                $CoversionArr[] = $ResArr;
            }
            return $CoversionArr;
        }
    }
}
