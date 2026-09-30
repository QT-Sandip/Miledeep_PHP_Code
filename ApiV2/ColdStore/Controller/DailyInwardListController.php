<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
//include_once 'Controller/UtilController.php';

class DailyInwardListController
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

    public function getDailyInwardDetails($RequestBody)
    {
        $TransactionDate = $RequestBody['TransactionDate'];
        $UnitID = $RequestBody['ColdStoreID'];
        $StockHolderID = $RequestBody['StockHolderID'];
        $ProductionTypeID = $RequestBody['Group'];
        $PackingTypeID = $RequestBody['PackingType'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $VarietyID = $RequestBody['Variety'];
        $PoNumber  = $RequestBody['PoNumber'];

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
        // if ($VarietyID != '') {
        //     $Query .= " and VarietyID='$VarietyID'";
        // }
        if ($VarietyID != '') {
            $Query .= " and ShortName='$VarietyID'";
        }
        if ($PoNumber  != '') {
            $Query .= " and CSD.PoID='$PoNumber'";
        }
        // if ($PoNo != "") {
        //     // $Query .= " and CPID.PoID='$PoID'";
        //     $PoNoQuery .= " and PoID='$PoNo'";
        // }


        $dataRow = null;
        $sqlFields = " Sum(ActualMcs) as Mcs,sum(ActualLooseBags) as Bags,ShortName,CSD.Grade,PackingTypeID,APS.PackingStyle,
        sum(ActualMcs*TotalSlabs*SlabWeight*MFactor) as Kgs,
        sum(ActualLooseBags*SlabWeight*MFactor) as LKgs ,UnitID,CSD.VarietyID,PoNo,CSD.Remarks,ProductionTypeID,CSRD.BlockID,
        CSD.StoreRackID,CSD.ProductionDate,CSD.TransactionDate";

        $sqlQuery = "SELECT $sqlFields FROM `CsStockData` as CSD
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        LEFT JOIN CsStoresRackData as CSRD on CSRD.RowID=CSD.StoreRackID
        WHERE TransactionDate='$TransactionDate' $Query
        group by CSD.StockID ";//group by CSD.VarietyID,CSD.Grade,PackingTypeID,PackingStyleID,UnitID
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $i = 0;
        $Totals = [];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            // var_dump($dataRow);
            // exit;
            $KGS = sprintf('%0.2f', round($dataRow['Kgs']+$dataRow['LKgs'], 2));
            $Mcs = $dataRow['Mcs'];
            $LooseBags = $dataRow['Bags'];

            $ArrayDailyInwardData['SNo'] = ++$i;
            $ArrayDailyInwardData['Group'] = $dataRow['ProductionTypeID'];
            $ArrayDailyInwardData['PoNo'] = $dataRow['PoNo'];
            $ArrayDailyInwardData['ProductionDate'] = $dataRow['ProductionDate'];
            $ArrayDailyInwardData['TransactionDate'] = $dataRow['TransactionDate'];
            $ArrayDailyInwardData['Variety'] = $dataRow['ShortName'];
            $ArrayDailyInwardData['Grade'] = $dataRow['Grade'];
            $ArrayDailyInwardData['PackingType'] = $dataRow['PackingTypeID'];
            $ArrayDailyInwardData['Mcs'] = $Mcs;
            $ArrayDailyInwardData['LooseBags'] = $LooseBags;
            $ArrayDailyInwardData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayDailyInwardData['UnitID'] = $dataRow['UnitID'];
            $ArrayDailyInwardData['VarietyID'] = $dataRow['VarietyID'];
            $ArrayDailyInwardData['Remarks'] = $dataRow['Remarks'];
            $ArrayDailyInwardData['StoreRackID'] = $dataRow['StoreRackID'];
            $ArrayDailyInwardData['BlockID'] = $dataRow['BlockID'];
            $ArrayDailyInwardData['Kgs'] =  $KGS;

            $AryData[] = $ArrayDailyInwardData;
            $Totals['TotalKGs'] +=  $KGS;
            $Totals['TotalMcs'] +=  $Mcs;
            $Totals['TotalLooseBags'] +=  $LooseBags;
        }
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['DailyInwardData'] = $AryData;
        $Data['Totals'] = $Totals;
        return $Data;
    }
}
