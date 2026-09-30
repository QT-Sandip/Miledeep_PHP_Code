<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
//include_once 'Controller/UtilController.php';

class DailyOutwardListController
{
    private $conn;
    //private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }

    public function getDailyOutwardDetails($RequestBody)
    {
        $TransactionDate = $RequestBody['TransactionDate'];
        $UnitID = $RequestBody['ColdStoreID'];
        $StockHolderID = $RequestBody['StockHolderID'];
        $ProductionTypeID = $RequestBody['Group'];
        $PackingTypeID = $RequestBody['PackingType'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $VarietyID = $RequestBody['Variety'];
        $PoNumber = $RequestBody['PoNumber'];
        
        $Query = '';
        if ($UnitID != '') {
            $Query .= " and UnitID='$UnitID'";
        }
        if ($StockHolderID != '') {
            $Query .= " and StockHolderID='$StockHolderID'";
        }
        if ($ProductionTypeID != '') {
            $Query .= " and ProductionTypeID='$ProductionTypeID'";
        }
        if ($PackingTypeID != '') {
            $Query .= " and PackingTypeID='$PackingTypeID'";
        }

        if ($VarietyID != '') {
            $Query .= " and ShortName='$VarietyID'";
        }

        if ($PoNumber  != '') {
            $Query .= " and CSD.PoID='$PoNumber'";
        }

        $dataRow = null;
        $sqlFields = " Sum(OutMcs) as Mcs,sum(OutLooseBags) as Bags,ShortName,CSD.Grade,PackingTypeID,APS.PackingStyle,
        CSD.ProductionDate,CSD.TransactionDate,sum(OutMcs*TotalSlabs*SlabWeight*MFactor) as Kgs, sum(OutLooseBags*SlabWeight*MFactor) as LKgs, CSD.VarietyID,PoNo,CSOD.Remarks,
        OutType, CSOD.PostedDate";
        // ProductionTypeID, CSOD.PostedDate";
        // ProductionTypeID, CSD.FreezingType, SPV.Species, SPV.TypeProduct";

        $sqlQuery = "SELECT $sqlFields FROM `CsStockOutDetails` as CSOD
        left join CsStockData as CSD on CSOD.StockID=CSD.StockID
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        WHERE OutDate='$TransactionDate' $Query
        group by CSD.VarietyID,CSD.Grade,PackingTypeID,PackingStyleID";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        // $errorInfo = $stmt->errorInfo();
        // var_dump($errorInfo);

        $i = 0;
        $Totals = [];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $KGS = sprintf('%0.2f', round($dataRow['Kgs'], 2));
            $LKGS = sprintf('%0.2f', round($dataRow['LKgs'], 2));
            $Mcs = $dataRow['Mcs'];
            $LooseBags = $dataRow['Bags'];

            $TotalKGs = $KGS + $LKGS;

            $ArrayDailyOutwardData['SNo'] = ++$i;
            $ArrayDailyOutwardData['ProductionDate'] = $dataRow['ProductionDate'];
            $ArrayDailyOutwardData['TransactionDate'] = $dataRow['TransactionDate'];
            $ArrayDailyOutwardData['PoNo'] = $dataRow['PoNo'];
            $ArrayDailyOutwardData['Variety'] = $dataRow['ShortName'];
            $ArrayDailyOutwardData['Grade'] = $dataRow['Grade'];
            $ArrayDailyOutwardData['PackingType'] = $dataRow['PackingTypeID'];
            $ArrayDailyOutwardData['Mcs'] = $Mcs;
            $ArrayDailyOutwardData['LooseBags'] = $LooseBags;
            $ArrayDailyOutwardData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayDailyOutwardData['Remarks'] = $dataRow['Remarks'];
            $ArrayDailyOutwardData['ProductionTypeID'] = $dataRow['OutType'];
            $ArrayDailyOutwardData['PostedDate'] = $dataRow['PostedDate'];
            $ArrayDailyOutwardData['Kgs'] = $TotalKGs;

            $AryData[] = $ArrayDailyOutwardData;
            $Totals['TotalKGs'] +=  $TotalKGs;
            $Totals['TotalMcs'] +=  $Mcs;
            $Totals['TotalLooseBags'] +=  $LooseBags;
        }
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['DailyOutwardData'] = $AryData;
        $Data['Totals'] = $Totals;
        return $Data;
    }
}
