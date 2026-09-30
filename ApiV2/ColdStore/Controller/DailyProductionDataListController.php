<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
//include_once 'Controller/UtilController.php';

class DailyProductionDataListController
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

    public function getDailyProductionDetails($RequestBody)
    {
        $TransactionDate = $RequestBody['TransactionDate'];
        $UnitID = $RequestBody['ColdStoreID'];
        $StockHolderID = $RequestBody['StockHolderID'];
        $ProductionTypeID = $RequestBody['Group'];
        $PackingTypeID = $RequestBody['PackingType'];

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


        $dataRow = null;
        $sqlFields = " StockID ,AvalMcs as Mcs,SPV.DeviSupc,ProductDescription,ActualLooseBags as Bags,ShortName,CSD.Grade,PackingTypeID,APS.PackingStyle,
                        (AvalMcs*TotalSlabs*SlabWeight*MFactor) as Kgs";

          $sqlQuery = "SELECT $sqlFields FROM `CsDailyProductionData` as CSD
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        WHERE TransactionDate='$TransactionDate' $Query order by  ShortName asc ,abs(Grade) asc ";  
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {


            $ArrayDailyProductionData['SNo'] = ++$i;
            $ArrayDailyProductionData['ProductionDataID'] = $dataRow['StockID'];
            $ArrayDailyProductionData['Variety'] = $dataRow['ShortName'];
            $ArrayDailyProductionData['Grade'] = $dataRow['Grade'];
            $ArrayDailyProductionData['PackingType'] = $dataRow['PackingTypeID'];
            $ArrayDailyProductionData['Mcs'] = $dataRow['Mcs'];
            $ArrayDailyProductionData['Bags'] = $dataRow['Bags'];
            $ArrayDailyProductionData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayDailyProductionData['ProductDescription'] = $dataRow['ProductDescription'];
            $ArrayDailyProductionData['OurSupc'] = $dataRow['DeviSupc'];
            $ArrayDailyProductionData['Kgs'] = sprintf('%0.2f', round($dataRow['Kgs'], 2));

            $AryData[] = $ArrayDailyProductionData;
        }
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['DailyProductionData'] = $AryData;
        return $Data;


    }



}
?>