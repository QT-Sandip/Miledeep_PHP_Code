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
        if ($ProductionDate != '') {
            $Query .= " AND CPID.ProductionDate <= '$ProductionDate'";
        }

        if ($FreezingType != '') {
            $Query .= " AND PIVL.FreezingType = '$FreezingType'";
        }

        if ($Variety != '') {
            $Query .= " AND PIVL.ShortName = '$Variety'";
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
            $QueryTwo .= " AND TypeProduct = '$TypeProduct'";
        }

        $sqlType = "SELECT DISTINCT TypeProduct FROM ScrmPIVarietyList WHERE 1=1 $QueryTwo ORDER BY TypeProduct ASC";
        // $sqlType = "SELECT DISTINCT TypeProduct FROM ScrmPIVarietyList WHERE 1=1 $QueryTwo ";
        $stmtType = $this->conn->prepare($sqlType);
        $stmtType->execute();

        $ArrayTypeData = [];
        while ($rowType = $stmtType->fetch(PDO::FETCH_ASSOC)) {
            $TypeProductRow = $rowType['TypeProduct'];

            $GradesData = $this->getGradeNames($Query, $TypeProductRow, $Type);
            $VarietyCombinationData = $this->getVarietyCombinationData($Query, $TypeProductRow);
            $GrandTotals = $this->getVarietywiseTotals($Type, $TypeProductRow, $Query);

            if ($GradesData && $VarietyCombinationData && $GrandTotals) {
                $ArrayTypeList['TypeProduct'] = $TypeProductRow;
                $ArrayTypeList['VarietyCombinationData'] = $VarietyCombinationData;
                $ArrayTypeList['GradesData'] = $GradesData;
                $ArrayTypeList['GrandTotals'] = $GrandTotals;
                
                $ArrayTypeData[] = $ArrayTypeList;
            }
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['Data'] = $ArrayTypeData;

        return $Data;
    }

    public function getGradeNames($Query, $TypeProduct, $Type)
    {
        $sqlGrades = "SELECT DISTINCT CPID.Grade AS GradeName FROM `CsStockData` AS CPID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CBS.BarCodeStatus=1 AND (AvalMcs!=0 OR LooseBags!=0) $Query AND PIVL.TypeProduct = '$TypeProduct' 
        ORDER BY abs(CPID.Grade) ASC";
        $stmtGrades = $this->conn->prepare($sqlGrades);
        $stmtGrades->execute();

        $Combinations = $this->getVarietyCombinationData($Query, $TypeProduct);

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

                $ValueData = $this->getGradewiseValues($CombinationQuery, $GradeName, $Type, $TypeProduct, $Query);

                $GradeEntry['GradeValueData'][] = $ValueData;
            }
            if (empty($GradeEntry['GradeValueData'])) {
                $GradeEntry['GradeValueData'][] = ['Value' => '0'];
            }

            $GradesData[] = $GradeEntry;
        }

        return $GradesData;
    }

    public function getVarietyCombinationData($Query, $TypeProduct)
    {

        $sqlVarietyFields = "CPID.PackingStyleID, APS.PackingStyle, PIVL.ShortName, CPID.SockingType, PIVL.Species, PIVL.Glaze, PIVL.GlazePerc";
        $sqlVarietySelect = "SELECT $sqlVarietyFields FROM `CsStockData` as CPID
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CBS.BarCodeStatus=1 AND (AvalMcs!=0 or LooseBags!=0) $Query  AND PIVL.TypeProduct = '$TypeProduct'
        GROUP BY CPID.PackingStyleID, PIVL.ShortName, CPID.SockingType, PIVL.Species, PIVL.Glaze ";
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

    public function getGradewiseValues($CombinationQuery, $Grade, $Type, $TypeProduct, $Query)
    {

        $sqlFields = "SUM(AvalMcs) AS AvalMcs,SUM(LooseBags) AS LooseBags, SUM(AvalKgs) AS AvalKgs";
        $sqlSelect = "SELECT $sqlFields FROM `CsStockData` as CPID
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
        LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CBS.BarCodeStatus=1 AND CPID.Grade = '$Grade' $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $Value = ['Value' => '0'];
        while ($rowValue = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $AvalMcs = $rowValue['AvalMcs'] ? $rowValue['AvalMcs'] : '0';
            $LooseBags = $rowValue['LooseBags'] ? $rowValue['LooseBags'] : '0';
            $AvalKgs = $rowValue['AvalKgs'] ? $rowValue['AvalKgs'] : '0';

            if ($Type == 'MCS') {
                if ($AvalMcs != 0 || $LooseBags != 0) {
                    $Value['Value'] = $AvalMcs . '/' . $LooseBags;
                } else {
                    $Value['Value'] = 0;
                }
            } else if ($Type == 'KGs') {
                $Value['Value'] = $AvalKgs ? round($AvalKgs, 2) : '0';
            }
        }

        return $Value;
    }

    public function getVarietywiseTotals($Type, $TypeProduct, $Query)
    {
        $Combinations = $this->getVarietyCombinationData($Query, $TypeProduct);

        $TotalValues = [];
        foreach ($Combinations as $Item) {
            $PackingStyle = $Item['PackingStyle'];
            $ShortName = $Item['ShortName'];
            $Species = $Item['Species'];
            $SockingType = $Item['SockingType'];
            $Glaze = $Item['Glaze'];

            $CombinationQuery = " AND APS.PackingStyle = '$PackingStyle' AND PIVL.ShortName ='$ShortName' AND 
            CPID.SockingType = '$SockingType' AND PIVL.Species = '$Species' AND PIVL.Glaze = '$Glaze'";

            $sqlFields = "SUM(AvalMcs) AS AvalMcs, SUM(LooseBags) AS LooseBags, SUM(AvalKgs) AS AvalKgs";
            $sqlSelect = "SELECT $sqlFields FROM `CsStockData` as CPID
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
            LEFT JOIN `CsBarCodes` AS CBS ON CBS.BarCodeID = CPID.BarCodeID
            WHERE CBS.BarCodeStatus=1 $CombinationQuery AND PIVL.TypeProduct = '$TypeProduct' $Query";

            $stmtSelect = $this->conn->prepare($sqlSelect);
            $stmtSelect->execute();

            $TotalValue = ['TotalValue' => '0'];
            $totalAvalMcs = 0;
            $totalLooseBags = 0;
            $totalAvalKgs = 0;

            while ($rowTotalValue = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
                $totalAvalMcs += $rowTotalValue['AvalMcs'] ? $rowTotalValue['AvalMcs'] : '0';
                $totalLooseBags += $rowTotalValue['LooseBags'] ? $rowTotalValue['LooseBags'] : '0';
                $totalAvalKgs += $rowTotalValue['AvalKgs'] ? $rowTotalValue['AvalKgs'] : '0';
            }

            if ($Type == 'MCS') {
                if ($totalAvalMcs != 0 || $totalLooseBags != 0) {
                    $TotalValue['TotalValue'] = $totalAvalMcs . '/' . $totalLooseBags;
                } else {
                    $TotalValue['TotalValue'] = '0';
                }
            } else if ($Type == 'KGs') {
                $TotalValue['TotalValue'] = $totalAvalKgs ? round($totalAvalKgs, 2) : '0';
            }

            $TotalValues[] = [
                'TotalValue' => $TotalValue['TotalValue']
            ];
        }

        return $TotalValues;
    }
}
