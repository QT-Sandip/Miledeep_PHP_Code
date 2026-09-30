<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class VarietywiseStockReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }


    public function getStockReportData($RequestBody)
    {
        $ProductionDate = $RequestBody['ProductionDate'];
        $ColdstoreID = $RequestBody['ColdstoreID'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $Type = $RequestBody['Type'];
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
        if ($Treatment != '') {
            $QueryTwo .= " AND SockingType = '$Treatment'";
        }


        // $sqlType = "SELECT DISTINCT TypeProduct FROM ScrmPIVarietyList WHERE 1=1 $QueryTwo ORDER BY TypeProduct ASC";
        $sqlType = "SELECT TypeProduct, ShortName, Treatment FROM ScrmPIVarietyList WHERE 1=1 $QueryTwo 
        GROUP BY TypeProduct, ShortName, Treatment ORDER BY TypeProduct ASC ";
        $stmtType = $this->conn->prepare($sqlType);
        $stmtType->execute();

        $ArrayTypeData = [];
        while ($rowType = $stmtType->fetch(PDO::FETCH_ASSOC)) {
            $TypeProductRow = $rowType['TypeProduct'];
            $ShortNameRow = $rowType['ShortName'];
            $SockingTypeRow = $rowType['Treatment'];

            $GradesData = $this->getGradeNames($Query, $TypeProductRow, $Type, $ProductionDate, $ShortNameRow, $SockingTypeRow,$ColdstoreID);
            $VarietyCombinationData = $this->getVarietyCombinationData($Query, $TypeProductRow, $ProductionDate, $ShortNameRow, $SockingTypeRow,$ColdstoreID);
            $GrandTotals = $this->getVarietywiseTotals($Type, $TypeProductRow, $Query, $ProductionDate, $ShortNameRow, $SockingTypeRow,$ColdstoreID);
            $FreezingData = $this->getVarietywiseFreezingData($Type, $TypeProductRow, $Query, $ProductionDate, $ShortNameRow, $SockingTypeRow,$ColdstoreID);

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

        $GrandTotals = $this->getGrandTotals($Type, $QueryTwo, $Query, $ProductionDate, $RequestBody);
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['Data'] = $ArrayTypeData;
        $Data['GrandTotals'] = $GrandTotals;

        return $Data;
    }

    public function getGradeNames($Query, $TypeProduct, $Type, $ProductionDate, $ShortNameRow, $SockingType,$ColdstoreID) // 
    {
        $sqlGrades = "SELECT DISTINCT CPID.Grade AS GradeName FROM `CsStockData` AS CPID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CPID.ProductionDate BETWEEN '2025-04-01' AND '$ProductionDate' $Query AND PIVL.TypeProduct = '$TypeProduct' 
        AND PIVL.ShortName = '$ShortNameRow' AND CPID.SockingType = '$SockingType'
        ORDER BY abs(CPID.Grade) ASC";
        // WHERE CBS.BarCodeStatus=1 AND (AvalMcs!=0 OR LooseBags!=0) $Query AND PIVL.TypeProduct = '$TypeProduct' 
        $stmtGrades = $this->conn->prepare($sqlGrades);
        $stmtGrades->execute();

        $Combinations = $this->getVarietyCombinationData($Query, $TypeProduct, $ProductionDate, $ShortNameRow, $SockingType,$ColdstoreID);

        $GradesData = [];

        while ($dataGrade = $stmtGrades->fetch(PDO::FETCH_ASSOC)) {
            $GradeName = $dataGrade['GradeName'];

            $GradeEntry = [
                'Grade' => $GradeName,
                'GradeValueData' => []
            ];

            foreach ($Combinations as $Item) {
                $PackingStyle = $Item['PackingStyle'];
                $ShortName = $Item['ShortName'];
                $Species = $Item['Species'];
                $SockingType = $Item['SockingType'];
                $Glaze = $Item['Glaze'];

                $CombinationQuery = " AND APS.PackingStyle = '$PackingStyle' AND PIVL.ShortName ='$ShortName' AND 
                CPID.SockingType = '$SockingType' AND PIVL.Species = '$Species' AND PIVL.Glaze = '$Glaze'";

                $ValueData = $this->getGradewiseValues($CombinationQuery, $GradeName, $Type, $TypeProduct, $Query, $ProductionDate,$ColdstoreID);

                $GradeEntry['GradeValueData'][] = $ValueData;
            }
            if (empty($GradeEntry['GradeValueData'])) {
                $GradeEntry['GradeValueData'][] = ['Value' => '0'];
            }

            $GradesData[] = $GradeEntry;
        }

        return $GradesData;
    }

    public function getVarietyCombinationData($Query, $TypeProduct, $ProductionDate, $ShortName, $SockingType,$ColdstoreID) //
    {

        $sqlVarietyFields = "CPID.PackingStyleID, APS.PackingStyle, PIVL.VarietyID, PIVL.ShortName, CPID.SockingType, PIVL.Species, PIVL.Glaze, PIVL.GlazePerc";
        $sqlVarietySelect = "SELECT $sqlVarietyFields FROM `CsStockData` as CPID
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CPID.ProductionDate BETWEEN '2025-04-01' AND '$ProductionDate' $Query  AND PIVL.TypeProduct = '$TypeProduct' 
        AND PIVL.ShortName = '$ShortName' AND CPID.SockingType = '$SockingType'
        GROUP BY CPID.PackingStyleID, PIVL.Species, PIVL.Glaze ORDER BY PIVL.ShortName, CPID.PackingStyleID";
        // GROUP BY CPID.PackingStyleID, PIVL.ShortName, CPID.SockingType, PIVL.Species, PIVL.Glaze ORDER BY PIVL.ShortName, CPID.PackingStyleID"; 
        $stmtVarietySelect = $this->conn->prepare($sqlVarietySelect);
        $stmtVarietySelect->execute();



        $ArrayVarietyData = [];
        while ($rowVarietywise = $stmtVarietySelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayVarietyList['VarietyID'] = $rowVarietywise['VarietyID'];
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

    public function getGradewiseValues($CombinationQuery, $Grade, $Type, $TypeProduct, $Query, $ProductionDate,$ColdstoreID) //
    {

        $sqlInFields = "SUM(ActualMcs) AS AvalMcs,SUM(ActualLooseBags) AS LooseBags, SUM(ActualKgs) AS AvalKgs, 
        FLOOR(SUM(ActualLooseBags)/TotalSlabs) AS LooseMcs";
        $sqlInSelect = "SELECT $sqlInFields FROM `CsStockData` as CPID
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CPID.ProductionDate BETWEEN '2025-04-01' AND '$ProductionDate' AND CPID.Grade = '$Grade' $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query";
        $stmtInSelect = $this->conn->prepare($sqlInSelect);
        $stmtInSelect->execute();

        $sqlOutFields = "SUM(OutMcs) as OutMcs, SUM(OutLooseBags) AS OutLooseBags, 
        SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS OutKGs, SUM(OutLooseBags*SlabWeight*MFactor) AS OutLKGs,
        FLOOR(SUM(OutLooseBags)/TotalSlabs) AS OutLooseMcs,sum(ActualOutKgs) as ActualOutKgs";
        $sqlOutSelect = "SELECT $sqlOutFields FROM CsStockOutDetails AS CSOD
        LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CSOD.OutDate <= '$ProductionDate' AND CPID.ProductionDate BETWEEN '2025-04-01' AND '$ProductionDate' AND CPID.Grade = '$Grade' $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query";
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
            // $KGs = $rowOutValue['OutKGs'] + $rowOutValue['OutLKGs'];
            $KGs = $rowOutValue['ActualOutKgs']; //uma

            $ArrayOutList['OutLKGs'] = $KGs ? $KGs : '0';

            $ArrayOutData[] = $ArrayOutList;
        }

        foreach ($ArrayInData as $inValue) {
            foreach ($ArrayOutData as $outValue) {
                $Mcs = $inValue['InMcs'] - $outValue['OutMcs'];
                $LooseBags = $inValue['InLooseBags'] - $outValue['OutLooseBags'];
                // $Kgs = $inValue['InKgs'] - $outValue['OutKGs'];
                $Kgs = $inValue['InKgs'] - $outValue['OutLKGs'];
            }
        }

        if ($Type == 'MCS') {
            if ($Mcs != 0) {
                $Value['Value'] = intval($Mcs);
            } else {
                $Value['Value'] = 0;
            }
        } else if ($Type == 'KGs') {
            $Value['Value'] = $Kgs ? round($Kgs, 3) : '0';
        }

        return $Value;
    }

    public function getVarietywiseTotals($Type, $TypeProduct, $Query, $ProductionDate, $ShortNameRow, $SockingType,$ColdstoreID) // 
    {
        $Combinations = $this->getVarietyCombinationData($Query, $TypeProduct, $ProductionDate, $ShortNameRow, $SockingType,$ColdstoreID);

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
            WHERE CPID.ProductionDate BETWEEN '2025-04-01' AND '$ProductionDate' $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query";
            $stmtInSelect = $this->conn->prepare($sqlInSelect);
            $stmtInSelect->execute();


            $sqlOutFields = "SUM(OutMcs) as OutMcs, SUM(OutLooseBags) AS OutLooseBags, 
            SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS OutKGs, SUM(OutLooseBags*SlabWeight*MFactor) AS OutLKGs,
            FLOOR(SUM(OutLooseBags)/TotalSlabs) AS OutLooseMcs,sum(ActualOutKgs) as ActualOutKgs";
            $sqlOutSelect = "SELECT $sqlOutFields FROM CsStockOutDetails AS CSOD
            LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
            WHERE CSOD.OutDate <= '$ProductionDate' AND CPID.ProductionDate BETWEEN '2025-04-01' AND '$ProductionDate' $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query";
            $stmtOutSelect = $this->conn->prepare($sqlOutSelect);
            $stmtOutSelect->execute();

            $TotalValue = ['TotalValue' => '0'];
            $InTotalMcs = 0;
            $InTotalLooseBags = 0;
            $InTotalKgs = 0;

            $OutTotalMcs = 0;
            $OutTotalLooseBags = 0;
            $OutTotalKGS = 0;

            while ($rowInTotalValue = $stmtInSelect->fetch(PDO::FETCH_ASSOC)) {
                $AvalMcs = $rowInTotalValue['AvalMcs'] + $rowInTotalValue['LooseSlabs'];
                $InTotalMcs += $AvalMcs ? $AvalMcs : '0';
                $InTotalLooseBags += $rowInTotalValue['LooseBags'] ? $rowInTotalValue['LooseBags'] : '0';
                $InTotalKgs += $rowInTotalValue['AvalKgs'] ? $rowInTotalValue['AvalKgs'] : '0';

                $ArrayInList['InTotalMcs'] = $InTotalMcs;
                $ArrayInList['InTotalLooseBags'] = $InTotalLooseBags;
                $ArrayInList['InTotalKgs'] = $InTotalKgs;

                $ArrayInData[] = $ArrayInList;
            }

            while ($rowOutTotalValue = $stmtOutSelect->fetch(PDO::FETCH_ASSOC)) {
                $OutMcs = $rowOutTotalValue['OutMcs'] + $rowOutTotalValue['OutLooseMcs'];
                $OutTotalMcs += $OutMcs ? $OutMcs : '0';
                $OutTotalLooseBags += $rowOutTotalValue['OutLooseBags'] ? $rowOutTotalValue['OutLooseBags'] : '0';
                // $OutKGs = $rowOutTotalValue['OutKGs'] + $rowOutTotalValue['OutLKGs'];
                $OutKGs = $rowOutTotalValue['ActualOutKgs'];

                $OutTotalKGS += $OutKGs ? $OutKGs : '0';

                $ArrayOutList['OutTotalOutMcs'] = $OutTotalMcs;
                $ArrayOutList['OutTotalOutLooseBags'] = $OutTotalLooseBags;
                $ArrayOutList['OutTotalOutKGs'] = $OutTotalKGS;

                $ArrayOutData[] = $ArrayOutList;
            }


            foreach ($ArrayInData as $inValue) {
                foreach ($ArrayOutData as $outValue) {
                    $Mcs = $inValue['InTotalMcs'] - $outValue['OutTotalOutMcs'];
                    $LooseBags = $inValue['InTotalLooseBags'] - $outValue['OutTotalOutLooseBags'];
                    $Kgs = $inValue['InTotalKgs'] - $outValue['OutTotalOutKGs'];
                }
            }


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
        $ColdstoreID = $RequestBody['ColdstoreID'];

        if ($Variety != '') {
            $Query .= " AND ShortName = '$Variety'";
        }
        if ($Treatment != '') {
            $Query .= " AND SockingType = '$Treatment'";
        }
        $sqlType = "SELECT TypeProduct, FreezingType FROM ScrmPIVarietyList WHERE 1=1 $QueryTwo GROUP BY TypeProduct, FreezingType";
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
            WHERE CPID.ProductionDate BETWEEN '2025-04-01' AND '$ProductionDate' AND  PIVL.TypeProduct = '$TypeProduct' AND PIVL.FreezingType = '$FreezingType' $Query";
            $stmtInSelect = $this->conn->prepare($sqlInSelect);
            $stmtInSelect->execute();

            // $errorInfo = $stmtInSelect->errorInfo();
            // var_dump($errorInfo);

            $sqlOutFields = "SUM(OutMcs) as OutMcs, SUM(OutLooseBags) AS OutLooseBags, 
            SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS OutKGs, SUM(OutLooseBags*SlabWeight*MFactor) AS OutLKGs,
            FLOOR(SUM(OutLooseBags)/TotalSlabs) AS OutLooseMcs,sum(ActualOutKgs) as ActualOutKgs";
            $sqlOutSelect = "SELECT $sqlOutFields FROM CsStockOutDetails AS CSOD
            LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
            LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID = PIVL.VarietyID  
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
            WHERE CSOD.OutDate <= '$ProductionDate' AND CPID.ProductionDate BETWEEN '2025-04-01' AND '$ProductionDate' AND PIVL.TypeProduct = '$TypeProduct' AND PIVL.FreezingType = '$FreezingType' $Query";
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
                //    $KGs = $rowOutValue['OutKGs'] + $rowOutValue['OutLKGs'];
                $KGs = $rowOutValue['ActualOutKgs'];
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

    public function getVarietywiseFreezingData($Type, $TypeProduct, $Query, $ProductionDate, $ShortNameRow, $SockingType,$ColdstoreID) // 
    {
        $Combinations = $this->getVarietyCombinationData($Query, $TypeProduct, $ProductionDate, $ShortNameRow, $SockingType,$ColdstoreID);

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
            WHERE ProductionDate = '$ProductionDate' $CombinationQuery";
            // echo $sqlInSelect;
            $stmtInSelect = $this->conn->prepare($sqlInSelect);
            $stmtInSelect->execute();
            $FreezingValue = $stmtInSelect->fetch(PDO::FETCH_ASSOC);
            $ProductionWeight = isset($FreezingValue['ProductionWeight']) ? $FreezingValue['ProductionWeight'] : 0;
            if ($Type == 'MCS') {
                if($ProductionWeight > 0){
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
}
