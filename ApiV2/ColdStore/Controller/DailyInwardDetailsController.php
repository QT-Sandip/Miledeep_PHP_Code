<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
//include_once 'Controller/UtilController.php';
include_once '../ColdStore/Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class DailyInwardDetailsController
{
    private $conn;
    private $utilController;
    private $BalanceKgs;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;



    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'ColdStore');
    }

    public function getStockDetails($RequestBody)
    {

        if (isset($RequestBody['StockID']) && $RequestBody['StockID'] != '') {
            $StockData = $this->getStockData($RequestBody);
        } else {
            $StockData = null;
        }

        if (isset($RequestBody['ProductionID']) && $RequestBody['ProductionID'] != '') {
            $ProductionData = $this->getProductionDetails($RequestBody);
            $RequestBody['ProductionDate'] = $ProductionData['ProductionDate'];
        } else {
            $ProductionData = null;
        }

        if (isset($RequestBody['StockID']) && $RequestBody['StockID'] != '') {
            $StockList = $this->getStockList($RequestBody);
        } else {
            $StockList = null;
        }


        $DailyProductionList = $this->getProductionList($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['StockData'] = $StockData;
        $Data['StockList'] = $StockList;
        $Data['DailyProductionData'] = $ProductionData;
        $Data['DailyProductionList'] = $DailyProductionList;
        return $Data;
    }
    function getStockData($RequestBody)
    {

        $StockID = $RequestBody['StockID'];
        $BarCodeID = $RequestBody['BarCodeID'];


        if ($StockID != '') {
            //  $Query = " and StockID='$StockID'";
        }

        $ProductionID = $RequestBody['ProductionID'];
        $ProductDetails['TotalWeight'] = $this->getProductionWeight($ProductionID);
        $this->BalanceKgs = $this->getBalanceKgs($ProductDetails['TotalWeight'], $ProductionID,'');

        $dataRow = null;
        $sqlFields = " CSD.UnitID,StockID,`AvalMcs`, `LooseBags`, `PackingTypeID`, CSD.`Grade`,   `PoGradeId`, CSD.`FreezingType`, CSD.`VarietyID`, `Galze`,
        `SockingType`, `Salt`, `ProductionDate`, CSD.`PoID`, `RmLotID`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`,
        `ShiftID`, `BarCodeID`, CSD.`PackingStyleID`, `StockHolderID`, CSD.`Remarks`, CSD.PlantID,
        `StockStatus`,TransactionDate, `AbStatus`, `ProdDayCode`,`ProdBatchCode`, `SoakingDate`, `TubNo`, `LineNumber`,`PoNo`,AGO.Name as Shift,ShortName as Variety,
        APS.PackingStyle,TenantName,StoreName,PoNo,RPS.StationName,SPV.DeviSupc,SPV.ProductDescription,CSD.BatchCode";

        $sqlQuery = "SELECT $sqlFields  FROM `CsStockData` as CSD
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        left join AdminGeneralOptions as AGO on AGO.OptID=CSD.ShiftID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        left join CsTenant as CST on CST.TenantID=CSD.StockHolderID
        left join CsStores as CSS on CSS.StoreID=CSD.UnitID
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID

        WHERE  StockID='$StockID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $Data['BarCodeID'] = $BarCodeID;
        $Data['BalanceKgs'] = $this->BalanceKgs;
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

        // print_r($dataRow);exit;

       // $ArrayStockData['StockID'] = $dataRow['StockID'];
        // $ArrayStockData['TransactionDate'] = $dataRow['TransactionDate'];

        // $ArrayStockData['Remarks'] = $dataRow['Remarks'];
        // $ArrayStockData['Mcs'] = $dataRow['AvalMcs'];
        // $ArrayStockData['LooseBags'] = $dataRow['LooseBags'];
        // $ArrayStockData['UnitID'] = $dataRow['UnitID'];

         $ArrayStockData['StockID'] = $dataRow['StockID'];
            $ArrayStockData['TransactionDate'] = date('d-m-Y', strtotime($dataRow['TransactionDate']));
            $ArrayStockData['ProductionDate'] = date('d-m-Y', strtotime($dataRow['ProductionDate']));
            $ArrayStockData['Group'] = $dataRow['ProductionTypeID'];
            $ArrayStockData['StockHolderID'] = $dataRow['StockHolderID'];
            $ArrayStockData['StockHolderName'] = $dataRow['TenantName'];
            $ArrayStockData['TransactionDate'] = $dataRow['TransactionDate'];
            $ArrayStockData['PoNo'] = $dataRow['PoNo'];
            $ArrayStockData['PoID'] = $dataRow['PoID'];
            $ArrayStockData['StoreID'] = $dataRow['UnitID'];
            $ArrayStockData['StoreName'] = $dataRow['StoreName'];
            $ArrayStockData['PlantID'] = $dataRow['PlantID'];
            $ArrayStockData['ProductionPlantName'] = $dataRow['StationName'];
            $ArrayStockData['Coldstore'] = $dataRow['UnitID'];
            $ArrayStockData['ShiftID'] = $dataRow['ShiftID'];
            $ArrayStockData['Shift'] = $dataRow['Shift'];
            $ArrayStockData['ProductionCode'] = $dataRow['ProdDayCode'];
            $ArrayStockData['RmLotID'] = $dataRow['RmLotID'];
            $ArrayStockData['BatchCode'] = $dataRow['ProdBatchCode'];
            $ArrayStockData['PoNumber'] = $dataRow['PoNo'];
            $ArrayStockData['Variety'] = $dataRow['Variety'];
            $ArrayStockData['VarietyID'] = $dataRow['VarietyID'];
            $ArrayStockData['PoGradeId'] = $dataRow['PoGradeId'];
            $ArrayStockData['PackingType'] = $dataRow['PackingTypeID'];
            $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStockData['PackingStyleID'] = $dataRow['PackingStyleID'];
            $ArrayStockData['Grade'] = $dataRow['Grade'];
            $ArrayStockData['FreezingType'] = $dataRow['FreezingType'];
            $ArrayStockData['Galze'] = $dataRow['Galze'];
            $ArrayStockData['Soaking'] = $dataRow['SockingType'];
            $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
            $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
            $ArrayStockData['AbStatus'] = $dataRow['AbStatus'];
            $ArrayStockData['Remarks'] = $dataRow['Remarks'];
            $ArrayStockData['InputBatchNo'] = $dataRow['BatchCode'];
            $ArrayStockData['Mcs'] = $dataRow['AvalMcs'];
            $ArrayStockData['LooseBags'] = $dataRow['LooseBags'];
            $ArrayStockData['UnitID'] = $dataRow['UnitID'];
            $ArrayStockData['OurSupc'] = $dataRow['DeviSupc'];
            $ArrayStockData['ProductDescription'] = $dataRow['ProductDescription'];
        return $ArrayStockData;
    }

    function getStockList($RequestBody)
    {

        $StockID = $RequestBody['StockID'];
        $BarCodeID = $RequestBody['BarCodeID'];


        if ($StockID != '') {
            //  $Query = " and StockID='$StockID'";
        }

        $ProductionID = $RequestBody['ProductionID'];
        $ProductDetails['TotalWeight'] = $this->getProductionWeight($ProductionID);
        $this->BalanceKgs = $this->getBalanceKgs($ProductDetails['TotalWeight'], $ProductionID,'');

        $dataRow = null;
        $sqlFields = " CSD.UnitID,StockID,CSD.`AvalMcs`, CSD.`LooseBags`, CSD.`PackingTypeID`, CSD.`Grade`,  CSD.`PoGradeId`, CSD.`FreezingType`, CSD.`VarietyID`, CSD.`Galze`,
        CSD.`SockingType`, CSD.`Salt`, CSD.`ProductionDate`, CSD.`PoID`, CSD.`RmLotID`,  CSD.`ProductionTypeID`, CSD.`ProductCategoryID`, CSD.`UnitID`,
        CSD.`ShiftID`, CSD.`BarCodeID`, CSD.`PackingStyleID`, CSD.`StockHolderID`, CSD.`Remarks`, CSD.PlantID,
        CSD.`StockStatus`,CSD.TransactionDate, CSD.`AbStatus`, CSD.`ProdDayCode`,CSD.`ProdBatchCode`, CSD.`SoakingDate`, CSD.`TubNo`, CSD.`LineNumber`,`PoNo`,AGO.Name AS Shift,ShortName AS Variety,
        APS.PackingStyle,TenantName,StoreName,PoNo,RPS.StationName,SPV.DeviSupc,SPV.ProductDescription
        ,RRD.TrackCode,RRD.ArrivalDate,
        CASE
        WHEN RLD.LotNumber IS NOT NULL AND RLD.LotNumber != ''
            THEN RLD.LotNumber
        WHEN CSD.BatchCode IS NOT NULL AND CSD.BatchCode != ''
            THEN CONCAT('P-', CSD.BatchCode)
        WHEN CDPD.MixedBatch IS NOT NULL AND CDPD.MixedBatch != ''
            THEN CONCAT('M-', CDPD.MixedBatch)
        ELSE ''
        END AS LotNumber ";

        $sqlQuery = "SELECT $sqlFields  FROM `CsStockData` as CSD
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        left join AdminGeneralOptions as AGO on AGO.OptID=CSD.ShiftID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        left join CsTenant as CST on CST.TenantID=CSD.StockHolderID
        left join CsStores as CSS on CSS.StoreID=CSD.UnitID
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID

        LEFT JOIN `CsDailyProductionData` CDPD ON CDPD.`ProductionID`=CSD.`ProductionID`
        LEFT JOIN RmReceivingDetails AS RRD ON CDPD.TrackID=RRD.ReceivingID
           LEFT JOIN (
                SELECT TrackID, TubNo, LineNumber, MAX(ProductionID) AS MaxDataID
                FROM ProdFreezingData
                GROUP BY TrackID, TubNo, LineNumber
            ) PFDx
            ON CDPD.TrackID = PFDx.TrackID
            AND CDPD.TubNo = PFDx.TubNo
            AND CDPD.LineNumber = PFDx.LineNumber
        LEFT JOIN ProdFreezingData PFD ON PFD.ProductionID = PFDx.MaxDataID
        LEFT JOIN PpcHLGradingBreakup AS PGB ON PFD.GradingBreakupID=PGB.DataID
        LEFT JOIN RmLotDetatils AS RLD ON PGB.LotID=RLD.RmLotID

        WHERE  StockID='$StockID' and (CSD.AvalMcs !=0  or CSD.LooseBags !=0) ";
        //echo '<pre>';print_r($sqlQuery);die;
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $Data['BarCodeID'] = $BarCodeID;
        $Data['BalanceKgs'] = $this->BalanceKgs;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArrayStockData['StockID'] = $dataRow['StockID'];
            $ArrayStockData['TransactionDate'] = date('d-m-Y', strtotime($dataRow['TransactionDate']));
            $ArrayStockData['ProductionDate'] = date('d-m-Y', strtotime($dataRow['ProductionDate']));
            $ArrayStockData['Group'] = $dataRow['ProductionTypeID'];
            $ArrayStockData['StockHolderID'] = $dataRow['StockHolderID'];
            $ArrayStockData['StockHolderName'] = $dataRow['TenantName'];
            $ArrayStockData['PoNo'] = $dataRow['PoNo'];
            $ArrayStockData['PoID'] = $dataRow['PoID'];
            $ArrayStockData['StoreID'] = $dataRow['UnitID'];
            $ArrayStockData['StoreName'] = $dataRow['StoreName'];
            $ArrayStockData['PlantID'] = $dataRow['PlantID'];
            $ArrayStockData['ProductionPlantName'] = $dataRow['StationName'];
            $ArrayStockData['Coldstore'] = $dataRow['UnitID'];
            $ArrayStockData['ShiftID'] = $dataRow['ShiftID'];
            $ArrayStockData['Shift'] = $dataRow['Shift'];
            $ArrayStockData['ProductionCode'] = $dataRow['ProdDayCode'];
            $ArrayStockData['RmLotID'] = $dataRow['RmLotID'];
            $ArrayStockData['BatchCode'] = $dataRow['ProdBatchCode'];
            $ArrayStockData['PoNumber'] = $dataRow['PoNo'];
            $ArrayStockData['Variety'] = $dataRow['Variety'];
            $ArrayStockData['VarietyID'] = $dataRow['VarietyID'];
            $ArrayStockData['PoGradeId'] = $dataRow['PoGradeId'];
            $ArrayStockData['PackingType'] = $dataRow['PackingTypeID'];
            $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStockData['PackingStyleID'] = $dataRow['PackingStyleID'];
            $ArrayStockData['Grade'] = $dataRow['Grade'];
            $ArrayStockData['FreezingType'] = $dataRow['FreezingType'];
            $ArrayStockData['Galze'] = $dataRow['Galze'];
            $ArrayStockData['Soaking'] = $dataRow['SockingType'];
            $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
            $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
            $ArrayStockData['AbStatus'] = $dataRow['AbStatus'];
            $ArrayStockData['Remarks'] = $dataRow['Remarks'];
            $ArrayStockData['Mcs'] = $dataRow['AvalMcs'];
            $ArrayStockData['LooseBags'] = $dataRow['LooseBags'];
            $ArrayStockData['UnitID'] = $dataRow['UnitID'];
            $ArrayStockData['OurSupc'] = $dataRow['DeviSupc'];
            $ArrayStockData['ProductDescription'] = $dataRow['ProductDescription'];
             $ArrayStockData['InputBatchNo'] = $dataRow['BatchCode'];
             $ArrayStockData['LotNumber'] = $dataRow['LotNumber'];

            $AryStockData[] = $ArrayStockData;
        }

        $Data['Data'] = $AryStockData;
        //  $AryStockData['BarCodeID'] = $BarCodeID;
        return $Data;
    }




    function getProductionDetails($RequestBody)
    {

        $ProductionID = $RequestBody['ProductionID'];
        $StockID = $RequestBody['StockID'];
        $IsEdit = $RequestBody['IsEdit'];
        if($IsEdit != '1'){
            $StockID = '';
        }

        $dataRow = null;
        $sqlFields = " ProductionID,`AvalMcs`, `LooseBags`, `PackingTypeID`, CSD.`Grade`,   CSD.`PoGradeId`, CSD.`FreezingType`, CSD.`VarietyID`, `Galze`,
        `SockingType`, `Salt`, `ProductionDate`, CSD.`PoID`, `RmLotID`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`,
        `ShiftID`, `BarCodeID`, CSD.`PackingStyleID`, `StockHolderID`, CSD.`Remarks`, CSD.PlantID,
        `StockStatus`, `AbStatus`, `ProdDayCode`,`ProdBatchCode`, `SoakingDate`, `TubNo`, `LineNumber`,`PoNo`,AGO.Name as Shift,ShortName as Variety,
        APS.PackingStyle,TenantName,StoreName,PoNo,RPS.StationName,APS.TotalSlabs,APS.SlabWeight,
        CSD.AvalKgs as Kgs,ActualMcs,ActualLooseBags,SPV.Glaze,SPV.ProductDescription";

        $sqlQuery = "SELECT $sqlFields  FROM `CsDailyProductionData` as CSD
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        left join AdminGeneralOptions as AGO on AGO.OptID=CSD.ShiftID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        left join CsTenant as CST on CST.TenantID=CSD.StockHolderID
        left join CsStores as CSS on CSS.StoreID=CSD.UnitID
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID where ProductionID='$ProductionID'";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $ArrayStockData = array();

        $AvalMcs = $dataRow['AvalMcs'];
        $LooseBags = $dataRow['LooseBags'];
        $ActualMcs = $dataRow['ActualMcs'];
        $ActualLooseBags = $dataRow['ActualLooseBags'];

        $TotalWeight = round($dataRow['Kgs'], 2);
        $BalanceKgs = $this->getBalanceKgs($TotalWeight, $dataRow['ProductionID'], $StockID);
        $TotalMcs = $this->getTotalMcs($dataRow['ProductionID']);
        $BalanceMcs = $this->getBalanceMcs($TotalMcs, $dataRow['ProductionID'],$StockID);

        $TotalLoose = $this->getTotalLoose($dataRow['ProductionID']);
        $LooseBags = $this->getLooseBags($TotalLoose, $dataRow['ProductionID'],$StockID);

        $ProductionID = $dataRow['ProductionID'];
        // $this->postDailyproductionStatus($BalanceKgs,$ProductionID);
        if ($BalanceKgs == 0) {
            $Status = 1;

        } else if ($BalanceKgs < 0) {
            $Status = 2;
        } else {
            $Status = 0;
        }


        $ArrayStockData['ProductionID'] = $dataRow['ProductionID'];
        $ArrayStockData['TransactionDate'] = $dataRow['TransactionDate'];
        $ArrayStockData['ProductionDate'] = $dataRow['ProductionDate'];
        $ArrayStockData['Group'] = $dataRow['ProductionTypeID'];
        $ArrayStockData['StockHolderID'] = $dataRow['StockHolderID'];
        $ArrayStockData['StockHolderName'] = $dataRow['TenantName'];
        $ArrayStockData['PoNo'] = $dataRow['PoNo'];
        $ArrayStockData['PoID'] = $dataRow['PoID'];
        $ArrayStockData['StoreID'] = $dataRow['UnitID'];
        $ArrayStockData['StoreName'] = $dataRow['StoreName'];
        $ArrayStockData['PlantID'] = $dataRow['PlantID'];
        $ArrayStockData['ProductionPlantName'] = $dataRow['StationName'];
        $ArrayStockData['Coldstore'] = $dataRow['UnitID'];
        $ArrayStockData['ShiftID'] = $dataRow['ShiftID'];
        $ArrayStockData['Shift'] = $dataRow['Shift'];
        if($LooseBags < 0){
            $TotalSlabs = $dataRow['TotalSlabs'];
            $looseMcs = ceil(abs($LooseBags) / $TotalSlabs);
            $BalanceMcs = $BalanceMcs - $looseMcs;
            // $LooseBags = $LooseBags - (abs($looseMcs) * $TotalSlabs);
            $LooseBags = (abs($looseMcs) * $TotalSlabs) - abs($LooseBags);

        }
        $ArrayStockData['BalanceMcs'] = $BalanceMcs;
        $ArrayStockData['BalanceLoose'] = $LooseBags;

        $ArrayStockData['BalanceBags'] = $BalanceBags;

        $ArrayStockData['ProductionCode'] = $dataRow['ProdDayCode'];
        $ArrayStockData['RmLotID'] = $dataRow['RmLotID'];
        $ArrayStockData['BatchCode'] = $dataRow['ProdBatchCode'];
        $ArrayStockData['PoNumber'] = $dataRow['PoNo'];
        $ArrayStockData['Variety'] = $dataRow['Variety'];
        $ArrayStockData['VarietyName'] = $dataRow['ProductDescription'];
        $ArrayStockData['VarietyID'] = $dataRow['VarietyID'];
        $ArrayStockData['PoGradeId'] = $dataRow['PoGradeId'];
        $ArrayStockData['PackingType'] = $dataRow['PackingTypeID'];
        $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
        $ArrayStockData['TotalSlabs'] = $dataRow['TotalSlabs'];
        $ArrayStockData['SlabWeight'] = $dataRow['SlabWeight'];
        $ArrayStockData['PackingStyleID'] = $dataRow['PackingStyleID'];
        $ArrayStockData['Grade'] = $dataRow['Grade'];
        $ArrayStockData['FreezingType'] = $dataRow['FreezingType'];
        $ArrayStockData['Galze'] = $dataRow['Galze'];
        $ArrayStockData['GalzeType'] = $dataRow['Glaze'];
        $ArrayStockData['Soaking'] = $dataRow['SockingType'];
        // $ArrayStockData['Salt'] = $dataRow['Salt'];
        $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
        $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
        $ArrayStockData['AbStatus'] = $dataRow['AbStatus'];
        // $ArrayStockData['TubNo'] = $dataRow['TubNo'];
        $ArrayStockData['LineNumber'] = $dataRow['LineNumber'];
        $ArrayStockData['Remarks'] = $dataRow['Remarks'];
        $ArrayStockData['Mcs'] = $dataRow['AvalMcs'];
        $ArrayStockData['LooseBags'] = $dataRow['LooseBags'];
        $ArrayStockData['TotalMcs'] = $TotalMcs;
        $ArrayStockData['BalanceMcs'] = $BalanceMcs;
        $dataRow = $ArrayStockData['Kgs'] = sprintf('%0.2f', $TotalWeight);
        $dataRow = $ArrayStockData['BalanceKgs'] = sprintf('%0.2f', $BalanceKgs);
        $dataRow = $ArrayStockData['Status'] = $Status;

        return $ArrayStockData;
    }

    function getProductionlist($RequestBody)
    {

        $ProductionDate = $RequestBody['ProductionDate'];
        $ProductionID = $RequestBody['ProductionID'];
        $StockID = $RequestBody['StockID'];
        $PlantID = $RequestBody['ProductionPlantID'];

        if ($StockID != '') {

            $Qry = " and ProductionID=$ProductionID ";
        }



        $sqlFields = "SPV.DeviSupc,ProductDescription,ProductionID,`StockStatus`,`ProdBatchCode`,CSD.`Galze`,CSD.ActualKgs";

        $sqlQuery = "SELECT $sqlFields  FROM `CsDailyProductionData` as CSD
                     left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID where CSD.ProductionDate='$ProductionDate'
                     and CSD.PlantID='$PlantID' $Qry";
// echo $sqlQuery;exit;

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $AvalMcs = $dataRow['AvalMcs'];
            $LooseBags = $dataRow['LooseBags'];
            $ActualMcs = $dataRow['ActualMcs'];
            $ActualLooseBags = $dataRow['ActualLooseBags'];
            $BalanceMcs = $ActualMcs - $AvalMcs;
            $BalanceBags = $ActualLooseBags - $LooseBags;

            $TotalWeight = round($dataRow['ActualKgs'], 2);
            // if($dataRow['Galze'] > 0){
            //     $GlazeWeight = ($TotalWeight * $dataRow['Galze']) / 100;
            //     $TotalWeight = round($TotalWeight - $GlazeWeight,2);
            // }
            $BalanceKgs = $this->getBalanceKgs($TotalWeight, $dataRow['ProductionID'],'');

            if ($BalanceKgs <= 0) {
                $Status = 1;
            } else {
                $Status = 0;
            }

            $ArrayStockData['SNo'] = ++$i;
            $ArrayStockData['OurSupc'] = $dataRow['DeviSupc'];
            $ArrayStockData['ProductDescription'] = $dataRow['ProductDescription'];
            $ArrayStockData['ProductionID'] = $dataRow['ProductionID'];
            $ArrayStockData['BatchCode'] = $dataRow['ProdBatchCode'];
            $ArrayStockData['Status'] = $Status;
            $AryData[] = $ArrayStockData;
        }

        return $AryData;
    }


    function getProductionWeight($ProductID)
    {
        // $sqlFields = "  (ActualMcs*TotalSlabs*SlabWeight*MFactor) as Kgs, (ActualLooseBags*SlabWeight*MFactor) as LKgs";
        $sqlFields = "sum(ActualKgs) as ActualKgs";

        $sqlQuery = "SELECT $sqlFields  FROM `CsDailyProductionData` as CSD
         left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
         WHERE ProductionID=$ProductID";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        // $TotalWeight = round($dataRow['Kgs'] + $dataRow['LKgs'], 2);
        $TotalWeight = round($dataRow['ActualKgs'], 2);

        return $TotalWeight;
    }
    function getTotalMcs($ProductID)
    {
        // $sqlFields = "  (ActualMcs*TotalSlabs*SlabWeight*MFactor) as Kgs, (ActualLooseBags*SlabWeight*MFactor) as LKgs";
        $sqlFields = "sum(ActualMcs) as ActualMcs";

        $sqlQuery = "SELECT $sqlFields  FROM `CsDailyProductionData` as CSD
         left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
         WHERE ProductionID=$ProductID";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        // $TotalWeight = round($dataRow['Kgs'] + $dataRow['LKgs'], 2);
        $TotalMcs = $dataRow['ActualMcs'];

        return $TotalMcs;
    }

    function getTotalLoose($ProductID)
    {
        $sqlFields = "sum(ActualLooseBags) as ActualLooseBags"; // ActualLooseBags, LooseBags

        $sqlQuery = "SELECT $sqlFields  FROM `CsDailyProductionData` as CSD
         left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
         WHERE ProductionID=$ProductID";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        // $TotalWeight = round($dataRow['Kgs'] + $dataRow['LKgs'], 2);
        $TotalLoose = $dataRow['ActualLooseBags'];

        return $TotalLoose;
    }


    public function getBalanceKgs($TotalWeight, $ProductionID,$StockID)
    {
        // $sqlFields = "sum((ActualMcs*TotalSlabs*SlabWeight*MFactor)) as Kgs,sum((ActualLooseBags*SlabWeight*MFactor)) as LKgs";


        $StockQuery = '';
        if($StockID != '' && $StockID != 0){
            $StockQuery = " AND CSD.StockID NOT IN ($StockID)";
        } else{
            $StockQuery = "";
        }

        //$sqlFields = "sum(ActualKgs) as ActualKgs";
        $sqlFields = " SUM(
                            CASE
                                WHEN APS.UOM IN ('kgs', 'grams')
                                    THEN (ActualMcs * TotalSlabs * SlabWeight * MFactor)
                                ELSE (ActualMcs * TotalSlabs * SlabWeight * lbsfactor * 0.454 )
                            END
                        ) AS Kgs,

                        SUM(
                            CASE
                                WHEN APS.UOM IN ('kgs', 'grams')
                                    THEN (ActualLooseBags * SlabWeight * MFactor)
                                ELSE (ActualLooseBags * SlabWeight * lbsfactor * 0.454)
                            END
                        ) AS LKgs,SUM(CSD.ActualKgs) AS ActualKgs";

        $sqlQuery = "SELECT $sqlFields FROM `CsStockData` as CSD
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        where ProductionID='$ProductionID' $StockQuery";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        //$TotalWeight;
        // $TotalWeight = round($TotalWeight - ($dataRow['Kgs'] + $dataRow['LKgs']), 2);
       // $TotalWeight = round(round($TotalWeight, 2) - round($dataRow['ActualKgs'], 2), 2);
        $ProductionTotalWeight = ($dataRow['Kgs'] + $dataRow['LKgs']);
        $ProductionTotalWeight = $dataRow['ActualKgs'];
        $TotalWeight = round(round($TotalWeight, 2) - round($ProductionTotalWeight, 2), 2);

        return $TotalWeight;
    }

    public function getBalanceMcs($TotalMcs, $ProductionID,$StockID)
    {
        // $sqlFields = "sum((ActualMcs*TotalSlabs*SlabWeight*MFactor)) as Kgs,sum((ActualLooseBags*SlabWeight*MFactor)) as LKgs";
        $StockQuery = '';
        if($StockID != '' && $StockID != 0){
            $StockQuery = " AND CSD.StockID NOT IN ($StockID)";
        } else{
            $StockQuery = "";
        }

        $sqlFields = "sum(ActualMcs) as ActualMcs";

        $sqlQuery = "SELECT $sqlFields FROM `CsStockData` as CSD
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        where ProductionID='$ProductionID' $StockQuery";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();


        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalMcs;
        // $TotalWeight = round($TotalWeight - ($dataRow['Kgs'] + $dataRow['LKgs']), 2);
        $BalanceMcs = $TotalMcs - $dataRow['ActualMcs'];

        return $BalanceMcs;
    }

    public function getLooseBags($TotalLoose, $ProductionID,$StockID)
    {
        // $sqlFields = "sum((ActualMcs*TotalSlabs*SlabWeight*MFactor)) as Kgs,sum((ActualLooseBags*SlabWeight*MFactor)) as LKgs";

        $StockQuery = '';
        if($StockID != '' && $StockID != 0){
            $StockQuery = " AND CSD.StockID NOT IN ($StockID)";
        } else{
            $StockQuery = "";
        }

        $sqlFields = "sum(ActualLooseBags) as ActualLooseBags";

        $sqlQuery = "SELECT $sqlFields FROM `CsStockData` as CSD
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        where ProductionID='$ProductionID' $StockQuery";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();


        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        // $TotalLoose;
        // $TotalWeight = round($TotalWeight - ($dataRow['Kgs'] + $dataRow['LKgs']), 2);
        $BalanceLooseBags = $TotalLoose - $dataRow['ActualLooseBags'];

        return $BalanceLooseBags;
    }


    public function postStockDetails($RequestBody)
    {

        $PostedUserID = 1;
        $StockID = $RequestBody['StockID'];
        $ProductionID = $RequestBody['ProductionID'];

        $TransactionDate = $RequestBody['TransactionDate'];
        $BarCodeID = $RequestBody['BarCodeID'];
        $Remarks = $RequestBody['Remarks'];
        $UnitID = $RequestBody['UnitID'];
        $AvalMcs = $RequestBody['Mcs'];
        $LooseBags = $RequestBody['LooseBags'];
        $ProductionTypeID = $RequestBody['Group'];
        $AbStatus = $RequestBody['Abstatus'];
        $ProductCategoryID = $RequestBody['PDCategory'];
        $SockingType = $RequestBody['Soaking'];
        $FreezingType = $RequestBody['FreezingType'];
        $Grade = $RequestBody['Grade'];
        $PackingStyleID = $RequestBody['PckgStyle'];
        $PackingTypeID = $RequestBody['PackingType'];
        $VarietyID = $RequestBody['Variety'];
        $ShiftID = $RequestBody['Shift'];
        $PoID = $RequestBody['PoNo'];
        $StockHolderID = $RequestBody['StockBlgsTo'];
        $IsDeductGlaze = $RequestBody['IsDeductGlaze'];
        $IsDeductGlazeFW = $RequestBody['IsDeductGlazeFW'];
        $IsDeductFWFCGlaze = $RequestBody['IsDeductFWFCGlaze'];
        $IsStoreProductionLinked = $RequestBody['IsStoreProductionLinked'];
        $MCSRestriction = $RequestBody['MCSRestriction'];
        $BatchCode = $RequestBody['txtBatchNo'];


        if ($BarCodeID == '') {
            $BarCodeID = $this->getNewBarcode($RequestBody);
        }

        $ProductDetails = [];

        if ($ProductionID == '') {
            $ProductionDate = $RequestBody['ProductionDate'];
            $PlantID = $RequestBody['TxtProductionPlant'];
            $PoGradeID = $RequestBody['PoGradeId'];
        } else {
            $ProductDetails = $this->getProductionDetails($RequestBody);
            // $UnitID = $ProductDetails['UnitID'];
            $ProductionDate = $ProductDetails['ProductionDate'];
            $PlantID = $ProductDetails['PlantID'];

            $ProdDayCode = $ProductDetails['ProductionCode'];
            $RmLotID = $ProductDetails['RmLotID'];
            $ProdBatchCode = $ProductDetails['BatchCode'];

            $PoGradeID = $ProductDetails['PoGradeId'];
            $LineNumber = $ProductDetails['LineNumber'];
        }
        if ($ProductionTypeID == '') {
            $ProductionTypeID = $ProductDetails['Group'];
        }
        if ($StockHolderID == '') {
            $StockHolderID = $ProductDetails['StockHolderID'];
        }
        if ($PoID == '') {
            $PoID = $ProductDetails['PoID'];
        }
        if ($ShiftID == '') {
            $ShiftID = $ProductDetails['ShiftID'];
        }
        if ($VarietyID == '') {
            $VarietyID = $ProductDetails['VarietyID'];
            $Galze = $ProductDetails['Galze'];
            $GlazeType = $ProductDetails['GlazeType'];
        } else {
            $sqlVariety = "SELECT Glaze,GlazePerc FROM ScrmPIVarietyList WHERE VarietyID=$VarietyID";
            $stmt = $this->conn->prepare($sqlVariety);
            $stmt->execute();
            if ($stmt) {
                $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
                $Galze = $dataRow['GlazePerc'];
                $GlazeType = $dataRow['Glaze'];
            }
        }

        if ($PackingTypeID == '') {
            $PackingTypeID = $ProductDetails['PackingType'];
        }
        if ($PackingStyleID == '') {
            $PackingStyleID = $ProductDetails['PackingStyleID'];
        }
        if ($Grade == '') {
            $Grade = $ProductDetails['Grade'];
        }
        if ($FreezingType == '') {
            $FreezingType = $ProductDetails['FreezingType'];
        }
        if ($SockingType == '') {
            $SockingType = $ProductDetails['Soaking'];
        }
        if ($ProductCategoryID == '') {
            $ProductCategoryID = $ProductDetails['ProductCategory'];
        }
        if ($AbStatus == '') {
            $AbStatus = $ProductDetails['AbStatus'];
        }


        $AryCurrentDate = getCurrentDateTime();
        $CreatedDateTime = $AryCurrentDate['TimeStamp'];
        $Year = $AryCurrentDate['Year'];

        //sriram
        // $ActualMcs = $ProductDetails['AvalMcs'];
        // $ActualLooseBags = $ProductDetails['LooseBags'];

        // $sqlPacking = "SELECT `TotalSlabs`,`SlabWeight`,`MFactor` FROM `AdminPackingStyles` WHERE `PCKID`='$PackingStyleID'";
        // $smtPacking = $this->conn->prepare($sqlPacking);
        // $smtPacking->execute();
        // $dataRowPacking = $smtPacking->fetch(PDO::FETCH_ASSOC);

        //  // sriram
        //  if ($ActualMcs >= $AvalMcs && $ActualLooseBags >= $LooseBags) {

        //  } else {
        //      if ($ActualMcs > $AvalMcs && $ActualLooseBags < $LooseBags) {
        //          $unPackMcs = ceil(($LooseBags - $ActualLooseBags) / $dataRowPacking['TotalSlabs']);
        //          if ($ActualMcs > ($AvalMcs + $unPackMcs)) {
        //              $BalanceMcs = $availabeMcs - ($AvalMcs + $unPackMcs);

        //              $BalanceLooseBags = ($availableLooseBags + ($unPackMcs * $RowStockData['TotalSlabs'])) - $OutLooseBags;
        //          } else {
        //              $Data['Error'] = "MCS and Loose bags exceeds available stock";
        //              $Data['Msg'] = "";
        //              return $Data;
        //          }
        //      } else {
        //          $Data['Error'] = "MCS and Loose bags exceeds available stock";
        //          $Data['Msg'] = "";
        //          return $Data;
        //      }
        //  }

        $ActualKgs = $this->utilController->getCsStockAvalWeightinKgs($PackingStyleID, $AvalMcs, $LooseBags);
        if (($IsDeductGlaze == 1 && $Galze > 0) ) {
            $GlazeWeight = ($ActualKgs * $Galze) / 100;
            $ActualKgs = $ActualKgs - $GlazeWeight;
        }
         if (strtolower($ProductionTypeID) == 'production') {
            if ($IsDeductGlaze == 0 && $IsDeductFWFCGlaze == 1 && (strpos($GlazeType, 'FW') === 0) && $Galze > 0){
                $GlazeWeight = ($ActualKgs * $Galze) / 100;
                $ActualKgs = $ActualKgs - $GlazeWeight;
                //  $ActualKgs = ($ActualKgs / ((100 - $Galze )/100));
            }
        }
        // if($IsStoreProductionLinked == 1){
        //     $GlazeWeight = ($ActualKgs * $Galze) / 100;
        //     $ActualKgs = $ActualKgs - $GlazeWeight;
        // }
        if($MCSRestriction == 1 && !empty($PoID) && !empty($VarietyID) && $PackingTypeID == 'MASTER'
        ){
            $slabRes = $this->getSlabData($PoID, $VarietyID, $AvalMcs, $LooseBags, $StockID, $UnitID, $StockHolderID);
            if(isset($slabRes)){
                if($slabRes < 0){//if mcs exceeds
                    $slabRes = 0;
                }
                $Data['Error'] = "Weight exceeds the allowed limit. Balance slabs: $slabRes";
                $Data['Msg'] = "Weight exceeds the allowed limit. Balance slabs: $slabRes ";

                return $Data;
            }
        }
        if ($ProductionID != '') {
            $sqlPorductionGet = "SELECT * FROM `CsDailyProductionData` WHERE ProductionID='$ProductionID'";
            $stmtPorductionGet = $this->conn->prepare($sqlPorductionGet);
            $stmtPorductionGet->execute();

            if ($stmtPorductionGet->rowCount() == 0) {
                $Data['Error'] = "Unable to place the Stock In, Please Try Again by Refreshing and Clicking Add in Production List ";
                $Data['Msg'] = "Unable to place the Stock In, Please Try Again by Refreshing and Clicking Add in Production List ";
                return $Data;
            }


            if($StockID != ''){
                $StockDetails = $this->getStockDataWithStockID($StockID);
                $AvailableProductionData = $this->getProductionAvailable($ProductionID);
                // Ensure all values are numeric
                $AvailableProductionData['BalanceMcs'] = ((float) $AvailableProductionData['BalanceMcs'] + (float)$StockDetails['Mcs']);
                $AvalMcs = (float) $AvalMcs;
                $AvailableProductionData['BalanceBags'] = ((float) $AvailableProductionData['BalanceBags'] + (float)$StockDetails['Bags']);
                $LooseBags = (float) $LooseBags;
                $AvailableProductionData['BalanceKgs'] = ((float) $this->truncateDecimal($AvailableProductionData['BalanceKgs'] + (float)$StockDetails['Kgs'],2));
                $ActualKgs = (float) $this->truncateDecimal($ActualKgs,2);
                // echo  $AvailableProductionData['BalanceMcs'] ."--" . $AvalMcs . " == " . $AvailableProductionData['BalanceBags'] . " -- " . $LooseBags. " == " .  $AvailableProductionData['BalanceKgs'] . " -- ".  $ActualKgs;exit;
            }else{
                $AvailableProductionData = $this->getProductionAvailable($ProductionID);

                // Ensure all values are numeric
                $AvailableProductionData['BalanceMcs'] = (float) $AvailableProductionData['BalanceMcs'];
                $AvalMcs = (float) $AvalMcs;
                $AvailableProductionData['BalanceBags'] = (float) $AvailableProductionData['BalanceBags'];
                $LooseBags = (float) $LooseBags;
                $AvailableProductionData['BalanceKgs'] = (float) $this->truncateDecimal($AvailableProductionData['BalanceKgs'],2);
                $ActualKgs = (float) $this->truncateDecimal($ActualKgs,2);
            }
            $BalanceMcs = $AvailableProductionData['BalanceMcs'];
            $BalanceLooseBags = $AvailableProductionData['BalanceBags'];
            if(($AvalMcs < $BalanceMcs) && ($BalanceLooseBags < $LooseBags)){

                $TotalSlabs = $AvailableProductionData['TotalSlabs'];
                $DummyLooseBags = $LooseBags - $BalanceLooseBags;
                $looseMcs = ceil(abs($DummyLooseBags) / $TotalSlabs);

            //  echo $BalanceMcs . " -- " . $AvalMcs . " -- " . $BalanceLooseBags . " -- " . $LooseBags . " -- " . $looseMcs . "-- " . $TotalSlabs;exit;
                $BalanceMcs = $BalanceMcs - $looseMcs;
                $BalanceLooseBags = ($looseMcs*$TotalSlabs) + (abs($BalanceLooseBags));
            }
            // echo $BalanceMcs . " -- " . $AvalMcs . " -- " . $BalanceLooseBags . " -- " . $LooseBags . " -- " .
            // $AvailableProductionData['BalanceKgs'] . " -- " . $ActualKgs;exit;
            if (
                bccomp($BalanceMcs, $AvalMcs, 2) === -1 ||
                bccomp($BalanceLooseBags, $LooseBags, 2) === -1 ||
                bccomp(round($AvailableProductionData['BalanceKgs'], 3), round($ActualKgs, 3), 2) === -1
               // if value is 4 X 2.5 LBS 1 bag = 1.135 some times round is converting to 1.13 and 1.14 then condition going to failed
                /* bccomp(
                    number_format($AvailableProductionData['BalanceKgs'], 3, '.', ''),
                    number_format($ActualKgs, 3, '.', ''),
                    2
                ) === -1 */
            ) {
                $Data['Error'] = "Weight Exceeds";
                $Data['Msg'] = "Weight Exceeds ";
                return $Data;
            }
        }
        if (strtolower($ProductionTypeID) == 'production') {
            if ($IsDeductGlaze == 0 && $IsDeductGlazeFW == 1 && (strpos($GlazeType, 'FW') === 0) && $Galze > 0){
                // $GlazeWeight = ($ActualKgs * $Galze) / 100;
                // $ActualKgs = $ActualKgs + $GlazeWeight;
                 $ActualKgs = ($ActualKgs / ((100 - $Galze )/100));
            }
        }
        if ($this->Error == null && $StockID == '') {
            if ($AvalMcs > 0 || $LooseBags > 0) {
                $sqlQuery = "INSERT INTO `CsStockData`(`ProductionID`,`ActualMcs`, `ActualLooseBags`, `AvalMcs`, `LooseBags`,
                `ActualKgs`, `AvalKgs`,  `PackingTypeID`, `Grade`, `PoGradeId`, `FreezingType`, `VarietyID`, `Galze`, `SockingType`,
             `ProductionDate`,`TransactionDate`, `PostedDate`,
              `PoID`, `RmLotID`, `ProductionTypeID`, `ProductCategoryID`, `UnitID`,`PlantID`, `ShiftID`, `BarCodeID`,
              `PackingStyleID`, `StockHolderID`, `Remarks`, `AbStatus`, `ProdDayCode`, `ProdBatchCode`,`LineNumber`,`BatchCode`)

              values('$ProductionID','$AvalMcs','$LooseBags','$AvalMcs','$LooseBags','$ActualKgs','$ActualKgs','$PackingTypeID','$Grade','$PoGradeID',
              '$FreezingType','$VarietyID','$Galze','$SockingType','$ProductionDate','$TransactionDate','$CreatedDateTime','$PoID','$RmLotID','$ProductionTypeID',
              '$ProductCategoryID','$UnitID','$PlantID','$ShiftID','$BarCodeID','$PackingStyleID',
              '$StockHolderID','$Remarks','$AbStatus', '$ProdDayCode', '$ProdBatchCode','$LineNumber','$BatchCode')";
                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();

                // echo $sqlQuery;exit;
                $StockID = $this->conn->lastInsertId();
                $this->auditLogger->logInsert('CsStockData', $StockID, $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID), $RequestBody);
                $RequestBody['StockID'] = $StockID;
                $RequestBody['BarCodeID'] = $BarCodeID;
                //$this->postDailyproductionStatus($ProductionID, $AvalMcs, $LooseBags, ((($IsDeductGlaze == 1) || ($IsDeductGlaze == 0 && $IsDeductGlazeFW == 1 && (strpos($GlazeType, 'FW') === 0))) ? $Galze : 0));

                $sqlAvlMcsQuery = "SELECT AvalMcs,LooseBags FROM CsDailyProductionData WHERE ProductionID='$ProductionID'";
                $stmtAvlMcs = $this->conn->prepare($sqlAvlMcsQuery);
                $stmtAvlMcs->execute();
                $AvlMcsRow = $stmtAvlMcs->fetch(PDO::FETCH_ASSOC);
                $this->postDailyproductionStatus($ProductionID, $AvlMcsRow['AvalMcs'], $AvlMcsRow['LooseBags'],'');

                $this->Error = null;
                $this->Msg = 'Posted Successfully';
                return $this->getStockDetails($RequestBody);
            } else {
                $Data['Error'] = null;
                $Data['Msg'] = 'Please Enter Valid Mcs';
                return $Data;
            }
        } else if ($this->Error == null && $StockID != '' || $RequestBody['Type'] == 'ProductUpdate') {
            $SqlStockData = "SELECT * from CsStockData where StockID ='$StockID'";
            $stmtStockData = $this->conn->prepare($SqlStockData);
            $stmtStockData->execute();
            $RowStockData = $stmtStockData->fetch(PDO::FETCH_ASSOC);

            if ($RowStockData['ActualMcs'] == $RowStockData['AvalMcs'] && $StockID != $RequestBody['Type']) {

                // `ArrivalNumber`='$ArrivalNumber',  because Auto Number
                $sqlQuery = "UPDATE `CsStockData` SET  `ActualMcs`='$AvalMcs',
                `ActualLooseBags`='$LooseBags',`AvalMcs`='$AvalMcs',`LooseBags`='$LooseBags',`ActualKgs`='$ActualKgs',`AvalKgs`='$ActualKgs',`PackingTypeID`='$PackingTypeID',
                `Grade`='$Grade',`PoGradeId`='$PoGradeID',`FreezingType`='$FreezingType',`VarietyID`='$VarietyID',`Galze`='$Galze',`SockingType`='$SockingType',
                `ProductionDate`='$ProductionDate',`TransactionDate`='$TransactionDate',`PostedDate`='$CreatedDateTime',`PoID`='$PoID',`RmLotID`='$RmLotID',
                `ProductionTypeID`='$ProductionTypeID',`ProductCategoryID`='$ProductCategoryID',`PlantID`='$PlantID',`ShiftID`='$ShiftID',
                `PackingStyleID`='$PackingStyleID',`StockHolderID`='$StockHolderID',`Remarks`='$Remarks',`AbStatus`='$AbStatus',
                `ProdDayCode`='$ProdDayCode',`ProdBatchCode`='$ProdBatchCode',`BatchCode`='$BatchCode' where `StockID`='$StockID'";
                // `UnitID`='$UnitID',
                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
                $this->auditLogger->logUpdate('CsStockData', $StockID, $RowStockData, $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID), $RequestBody);

                if ($stmt) {
                    //$this->postDailyproductionStatus($ProductionID, $AvalMcs, $LooseBags, ((($IsDeductGlaze == 1) || ($IsDeductGlaze == 0 && $IsDeductGlazeFW == 1 && (strpos($GlazeType, 'FW') === 0))) ? $Galze : 0));
                   $sqlAvlMcsQuery = "SELECT AvalMcs,LooseBags FROM CsDailyProductionData WHERE ProductionID='$ProductionID'";
                    $stmtAvlMcs = $this->conn->prepare($sqlAvlMcsQuery);
                    $stmtAvlMcs->execute();
                    $AvlMcsRow = $stmtAvlMcs->fetch(PDO::FETCH_ASSOC);
                    $this->postDailyproductionStatus($ProductionID, $AvlMcsRow['AvalMcs'], $AvlMcsRow['LooseBags'], ((($IsDeductGlaze == 1) || ($IsDeductGlaze == 0 && $IsDeductGlazeFW == 1 && (strpos($GlazeType, 'FW') === 0)) || ($IsDeductGlaze == 0 && $IsDeductFWFCGlaze == 1 && (strpos($GlazeType, 'FW') === 0))) ? $Galze : 0));
                    $RequestBody['BarCodeID'] = $BarCodeID;

                    $this->Error = null;
                    $this->Msg = 'Updated Successfully';
                    return $this->getStockDetails($RequestBody);
                }
            } else if ($RequestBody['Type'] == 'ProductUpdate') {

                $sqlQuery = "UPDATE `CsStockData` SET `PackingTypeID`='$PackingTypeID',
                `Grade`='$Grade',`PoGradeId`='$PoGradeID',`FreezingType`='$FreezingType',`VarietyID`='$VarietyID',`Galze`='$Galze',`SockingType`='$SockingType',
                `ProductionDate`='$ProductionDate',`TransactionDate`='$TransactionDate',`PostedDate`='$CreatedDateTime',`PoID`='$PoID',`RmLotID`='$RmLotID',
                `ProductionTypeID`='$ProductionTypeID',`ProductCategoryID`='$ProductCategoryID',`PlantID`='$PlantID',`ShiftID`='$ShiftID',
                `PackingStyleID`='$PackingStyleID',`StockHolderID`='$StockHolderID',`Remarks`='$Remarks',`AbStatus`='$AbStatus',
                `ProdDayCode`='$ProdDayCode',`ProdBatchCode`='$ProdBatchCode' $updateFields WHERE `ProductionID`='$ProductionID'";
                // `UnitID`='$UnitID',`ActualKgs`='$ActualKgs',`AvalKgs`='$ActualKgs', //uma 04-10-2024
                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
                $this->auditLogger->logUpdate('CsStockData', $ProductionID, null, $this->auditLogger->fetchRowById('CsStockData', 'ProductionID', $ProductionID), $RequestBody);
                if ($stmt) {
                    //$this->postDailyproductionStatus($ProductionID, $AvalMcs, $LooseBags, ((($IsDeductGlaze == 1) || ($IsDeductGlaze == 0 && $IsDeductGlazeFW == 1 && (strpos($GlazeType, 'FW') === 0))) ? $Galze : 0));
                    $sqlAvlMcsQuery = "SELECT AvalMcs,LooseBags FROM CsDailyProductionData WHERE ProductionID='$ProductionID'";
                    $stmtAvlMcs = $this->conn->prepare($sqlAvlMcsQuery);
                    $stmtAvlMcs->execute();
                    $AvlMcsRow = $stmtAvlMcs->fetch(PDO::FETCH_ASSOC);
                    $this->postDailyproductionStatus($ProductionID, $AvlMcsRow['AvalMcs'], $AvlMcsRow['LooseBags'], ((($IsDeductGlaze == 1) || ($IsDeductGlaze == 0 && $IsDeductGlazeFW == 1 && (strpos($GlazeType, 'FW') === 0)) || ($IsDeductGlaze == 0 && $IsDeductFWFCGlaze == 1 && (strpos($GlazeType, 'FW') === 0))) ? $Galze : 0));
                    $RequestBody['BarCodeID'] = $BarCodeID;
                    $this->Error = null;
                    $this->Msg = 'Updated Successfully';
                    return $this->getStockDetails($RequestBody);
                }
            } else {
                $this->Error = null;
                $this->Msg = 'No Access To Edit';
                // return $Data;
                return $this->getStockDetails($RequestBody);
            }
        }



        // else {
        //     return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
        // }
    }
    function truncateDecimal($number, $decimals = 2) {
        $factor = pow(10, $decimals);
        return ($number * $factor) / $factor;
    }

    function getNewBarcode($RequestBody = array() )
    {



        $SqlBarCodes = "SELECT BarCodeID FROM CsBarCodes ORDER by BarCodeID DESC LIMIT 1";
        $StmtBarCodes = $this->conn->prepare($SqlBarCodes);
        $StmtBarCodes->execute();
        $RecentBarCode = $StmtBarCodes->fetch(PDO::FETCH_ASSOC);
        $LastBarCodeID = $RecentBarCode['BarCodeID'];

        if ($LastBarCodeID == '') {
            $LastBarCodeID = 1;
        } else {
            $LastBarCodeID = $LastBarCodeID + 1;
        }


        date_default_timezone_set("Asia/Kolkata");
        $BarCodeDate = date("Y-m-d");
        $BarCodeValue = str_replace("-", "", $BarCodeDate);
        $BarCodeValue = $BarCodeValue . $LastBarCodeID;

        $sqlInsertBarCodeData = "INSERT INTO `CsBarCodes`(`BarCodeDate`, `BarCodeValue`, `BarCodeStatus`, `OnFloor`, `PostedUserID`)
        VALUES ('$BarCodeDate','$BarCodeValue','1','0','1')";

        $stmtInsertBarCodeData = $this->conn->prepare($sqlInsertBarCodeData);
        $stmtInsertBarCodeData->execute();
        $BarCodeID = $this->conn->lastInsertId();
        $this->auditLogger->logInsert('CsBarCodes', $BarCodeID, $this->auditLogger->fetchRowById('CsBarCodes', 'BarCodeID', $BarCodeID), $RequestBody);
        return $BarCodeID;
    }

    public function deleteStockData($RequestBody)
    {

        $StockID = $RequestBody['StockID'];
        $ProductionID = $RequestBody['ProductionID'];
        $BarCodeID = $RequestBody['BarCodeID'];

        $oldStockRow = $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID);
        $sqlDelete = "DELETE FROM CsStockData WHERE StockID=$StockID";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();
        $this->auditLogger->logDelete('CsStockData', $StockID, $oldStockRow, $RequestBody);
        //$stmtDelete = true;
        if ($stmtDelete) {
              
            $sqlAvlMcsQuery = "SELECT AvalMcs,LooseBags FROM CsDailyProductionData WHERE ProductionID='$ProductionID'";
            $stmtAvlMcs = $this->conn->prepare($sqlAvlMcsQuery);
            $stmtAvlMcs->execute();
            $AvlMcsRow = $stmtAvlMcs->fetch(PDO::FETCH_ASSOC);
            $this->postDailyproductionStatus($ProductionID, $AvlMcsRow['AvalMcs'], $AvlMcsRow['LooseBags'],'');
            
           

            $this->Error = null;
            $this->Msg = 'Deleted Successfully';
            return $this->getStockDetails($RequestBody);
        } else {
            $this->Error = 'Unable to Delete';
            $this->Msg = 'Unable to Delete';
            return $this->getStockDetails($RequestBody);
        }
    }



    function postDailyproductionStatus($ProductionID, $AvalMcs, $LooseBags, $Galze)
    {
        $sqlPackingStyle = "SELECT TotalSlabs,SlabWeight,MFactor,Uom,LbsFactor FROM `CsDailyProductionData` as CSD
                left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
                WHERE ProductionID='$ProductionID'";
        $stmtPackingStyle = $this->conn->prepare($sqlPackingStyle);
        $stmtPackingStyle->execute();
        $dataRowPackingStyle = $stmtPackingStyle->fetch(PDO::FETCH_ASSOC);

        // $Kgs = $AvalMcs * $dataRowPackingStyle['TotalSlabs'] * $dataRowPackingStyle['SlabWeight'] * $dataRowPackingStyle['MFactor'];
        // $LKgs = $LooseBags * $dataRowPackingStyle['SlabWeight'] * $dataRowPackingStyle['MFactor'];
        // $TotalWeight = $Kgs + $LKgs;

        if ($dataRowPackingStyle['Uom'] == 'KGS' || $dataRowPackingStyle['Uom'] == 'GRAMS') {
            $TotalWeight = ($AvalMcs * $dataRowPackingStyle['TotalSlabs'] + $LooseBags) * $dataRowPackingStyle['SlabWeight'] * $dataRowPackingStyle['MFactor'];
        } else {
            $TotalWeight = ($AvalMcs * $dataRowPackingStyle['TotalSlabs'] + $LooseBags) * $dataRowPackingStyle['SlabWeight'] * $dataRowPackingStyle['LbsFactor'] * 0.454;
        }

        if ($Galze > 0) {
            $GlazeWeight = ($TotalWeight * $Galze) / 100;
            $TotalWeight = round($TotalWeight - $GlazeWeight, 2);
        }

        $dataRowAvalMcs['ActualLooseBags'] = $LooseBags;
        $dataRowAvalMcs['ActualMcs'] = $AvalMcs;
        $BalanceKgs = $this->getBalanceKgs($TotalWeight, $ProductionID,'');

        if ($BalanceKgs <= 0) {
            $Status = 1;
        } else {
            $Status = 0;
        }

        $SqlUpdateDailyProductionStatus = "UPDATE `CsDailyProductionData` SET `StockStatus`='$Status' WHERE `ProductionID`='$ProductionID'";
        $stmtUpdateDailyProductionStatus = $this->conn->prepare($SqlUpdateDailyProductionStatus);
        $stmtUpdateDailyProductionStatus->execute();
        // $ArrayStatus['Status'] = $Status;


        return 'Success';
    }

    public function getPoNumbers($RequestBody)
    {
        $Plant = $RequestBody['ProductionPlantID'];
        $Query = '';
        if ($Plant != '') {
            $Query = "AND ProductionPlantID=$Plant";
        }
        $sqlQuery = "SELECT PoID,PoNo,ProductionPlantID FROM ScrmPoData as SPD 
        left join ScrmISFDetails  ISF on ISF.PIID=SPD.PoID
        WHERE SPD.Status in (4) AND ISF.FDAContainerStatus REGEXP 'Return Container' $Query order by PoID ASC";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $Data = [];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    public function getPoWeightBalance($RequestBody)
    {
        $PoNumber = $RequestBody['PoNumber'];
        $Variety = $RequestBody['Variety'];
        $Group = $RequestBody['Group'];
        $Query = $csVaraityQry = '';
        if ($Variety != '') {
            $Query = "AND SPGD.VarietyID=$Variety";
            $csVaraityQry = " AND VarietyID = $Variety ";
        }
        $groupWhere = " AND ProductionTypeID='Return Container' ";
        if($Group == 'Physical In'){
            //$groupWhere = " AND ProductionTypeID='Physical In' ";
            $groupWhere = '';
        }
        $stockIdWhere = '';
        if($RequestBody['StockID']){
            $stockIdWhere = ' AND StockID != '.$RequestBody['StockID'];
        }

        $sqlQuery = "SELECT SPD.PoID,SPD.PoNo,SUM(COALESCE(SPGD.Mcs,0)) AS MCS,SUM(COALESCE(SPGD.Mcs*APS.TotalSlabs*APS.MFactor*APS.SlabWeight,0)) AS KGS,
        COALESCE(CSD.TotalMcs,0) AS TotalMcs,COALESCE(CSD.LooseBags,0) AS LooseBags,COALESCE(CSD.TotalKgs,0) AS TotalKgs,
        APS.TotalSlabs,APS.MFactor,APS.SlabWeight,SCRD.ReturnContainerNetWeightKgs AS ReturnKgs,(SCRD.ReturnContainerNetWeightKgs/TotalSlabs/SlabWeight/Mfactor) as ReturnMcs
        FROM ScrmPoData as SPD LEFT JOIN ScrmPoGradeData AS SPGD ON SPD.PoID=SPGD.InvId 
        LEFT JOIN AdminPackingStyles AS APS ON SPGD.PackingStyleID=APS.PCKID 
        LEFT JOIN (
                    SELECT DISTINCT PoID,SUM(ActualMcs) AS TotalMcs,SUM(ActualLooseBags) AS LooseBags,SUM(ActualKgs) AS TotalKgs 
                    FROM CsStockData 
                    WHERE PoID=$PoNumber $csVaraityQry  $stockIdWhere
                ) AS CSD ON SPD.PoID =CSD.PoID
        left join ScrmCotainerReturnDetails as SCRD on SCRD.PoID=SPD.PoID
        WHERE SPD.PoId=$PoNumber $Query GROUP BY SPD.PoID";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $Data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($Group == 'Return Container') {
            $Data['KGS'] = $Data['ReturnKgs'];
            $Data['MCS'] = $Data['ReturnMcs'];
        }
        $Data['BalanceKgs'] = $Data['KGS'] - $Data['TotalKgs'];
        $LooseMcs = 0;
        if ($Data['LooseBags'] > 0) {
            //if($Group == 'Physical In'){
                $LooseSlabs = floor($Data['LooseBags'] / $Data['TotalSlabs']);//11/5=2.4
                $LooseBags = $Data['LooseBags'] - ($LooseSlabs * $Data['TotalSlabs']);//12-(2*5)=2
                $Data['BalanceLooseBags'] = $Data['TotalSlabs'] - $LooseBags;
                $LooseMcs = ceil($Data['LooseBags'] / $Data['TotalSlabs']);//11/5=2.2=3
            // }
            // $LooseMcs = $Data['LooseBags'] / $Data['TotalSlabs'];//lb=12 = 2.4
            // $Data['BalanceLooseBags'] = $Data['LooseBags'] - ($LooseMcs * $Data['TotalSlabs']);
        } else {
            $Data['BalanceLooseBags'] = 0;
        }
        $Data['BalanceMcs'] = $Data['MCS'] - $Data['TotalMcs'] - $LooseMcs;
        return $Data;
    }

    private function getProductionAvailable($ProductionId)
    {

        $sqlProductionData = "SELECT CSD.*,APS.TotalSlabs AS TotalSlabs FROM CsDailyProductionData AS CSD left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID WHERE ProductionID='$ProductionId'";
        $stmtProductionData = $this->conn->prepare($sqlProductionData);
        $stmtProductionData->execute();
        $ProductionDataRow = $stmtProductionData->fetch(PDO::FETCH_ASSOC);

        $ActualMcs = ($ProductionDataRow['ActualMcs'] > 0) ? $ProductionDataRow['ActualMcs'] : 0;
        $ActualLooseBags = ($ProductionDataRow['ActualLooseBags'] > 0) ? $ProductionDataRow['ActualLooseBags'] : 0;
        $ActualKgs = ($ProductionDataRow['ActualKgs'] > 0) ? $ProductionDataRow['ActualKgs'] : 0;

        $sqlStockData = "SELECT SUM(ActualMcs) AS Mcs,SUM(ActualLooseBags) AS Bags,SUM(ActualKgs) AS Kgs FROM CsStockData AS CSD WHERE ProductionID='$ProductionId'";
        $stmtStockData = $this->conn->prepare($sqlStockData);
        $stmtStockData->execute();
        $StockDataRow = $stmtStockData->fetch(PDO::FETCH_ASSOC);

        $Mcs = ($StockDataRow['Mcs'] > 0) ? $StockDataRow['Mcs'] : 0;
        $LooseBags = ($StockDataRow['Bags'] > 0) ? $StockDataRow['Bags'] : 0;
        $Kgs = ($StockDataRow['Kgs'] > 0) ? $StockDataRow['Kgs'] : 0;

        $BalanceMcs = $ActualMcs - $Mcs;
        $BalanceLooseBags = $ActualLooseBags - $LooseBags;
        $TotalSlabs = $ProductionDataRow['TotalSlabs'];
        if($BalanceLooseBags < 0){
            $looseMcs = ceil(abs($BalanceLooseBags) / $TotalSlabs);
            $BalanceMcs = $BalanceMcs - $looseMcs;
             $BalanceLooseBags = $TotalSlabs - (abs($BalanceLooseBags)%$TotalSlabs);
        }
        $resultArr['BalanceMcs'] = $BalanceMcs;
        $resultArr['BalanceBags'] = $BalanceLooseBags;
        $resultArr['TotalSlabs'] = $TotalSlabs;
        $resultArr['BalanceKgs'] = $ActualKgs - $Kgs;

        return $resultArr;

    }

    private function getStockDataWithStockID($StockID){

        $sqlStockData = "SELECT * FROM CsStockData WHERE StockID='$StockID'";
        $stmtStockData = $this->conn->prepare($sqlStockData);
        $stmtStockData->execute();
        $rowStockData = $stmtStockData->fetch(PDO::FETCH_ASSOC);

        $resultArr['Mcs'] = $rowStockData['ActualMcs'];
        $resultArr['Bags'] = $rowStockData['ActualLooseBags'];
        $resultArr['Kgs'] = $rowStockData['ActualKgs'];

        return $resultArr;

    }
    /*check slab data starts*/ 
    function getSlabData($PoNumber, $VarietyId, $AvalMcs, $LooseBags, $StockID = '', $UnitID = '', $StockHolderID = ''){
        if($PoNumber && $VarietyId){
             $sqlSlabQuery = "SELECT SUM(PGD.Mcs) AS Mcs,CPS.`PackingStyle`,CPS.`TotalSlabs` ,CPS.`SlabWeight`,CPS.MFactor,PGD.VarietyID
                                FROM ScrmPoGradeData PGD
                            LEFT JOIN AdminPackingStyles AS CPS ON PGD.PackingStyleID=CPS.PckID            
                            WHERE PGD.InvId='$PoNumber' AND VarietyID=$VarietyId";
            $stmtSlab = $this->conn->prepare($sqlSlabQuery);
            $stmtSlab->execute();
            $RowSlab = $stmtSlab->fetch(PDO::FETCH_ASSOC);
            $Mcs = !empty($RowSlab['Mcs']) ? $RowSlab['Mcs'] : 0;
            $Slabs = !empty($RowSlab['TotalSlabs']) ? $RowSlab['TotalSlabs'] : 0;//20
            $VarietyID = !empty($RowSlab['VarietyID']) ? $RowSlab['VarietyID'] : '';//
           // $SlabWeight = !empty($RowSlab['SlabWeight']) ? $RowSlab['SlabWeight'] : '';//
           // $MFactor = !empty($RowSlab['MFactor']) ? $RowSlab['MFactor'] : '';//
            $EnteredSlabs = ($AvalMcs * $Slabs) + $LooseBags;//16
            $where = '';
            if($StockID){// edit case
                $where .= " AND StockID != $StockID";            
            }
            if($UnitID){
                $where .= " AND UnitID = $UnitID";   
            }
            if($StockHolderID){
                $where .= " AND StockHolderID = $StockHolderID";   
            }

            $csStockSql = "SELECT SUM(`AvalMcs`) AS TotalMcs,SUM(LooseBags) AS TotalBags FROM `CsStockData` WHERE `VarietyID`=$VarietyId
             AND PackingTypeID = 'MASTER'
            AND ProductionTypeID NOT IN ('Update_Prod')
             AND `PoID`=$PoNumber $where";
            $stmtCsStock = $this->conn->prepare($csStockSql);
            $stmtCsStock->execute();
            $TotalRes = $stmtCsStock->fetch(PDO::FETCH_ASSOC);
            $TotalMcsStock = (int)$TotalRes['TotalMcs'];//802
            $TotalBags = (int)$TotalRes['TotalBags'];

            if(!empty($TotalMcsStock) && !empty($Slabs)){
                $TotalSlab = ((int)$TotalMcsStock * (int)$Slabs) + (int)$TotalBags;//14,400
            }else{
                $TotalSlab = (int)$TotalBags;//2
            }
            $avlTotalSlabs = $TotalSlab;//
            $ActPoSlabs = $Mcs * $Slabs;//14400
            $TotalSlab += $EnteredSlabs;//14,400+16=14,416
            $RemainSlabs = $ActPoSlabs - $TotalSlab;//14400- 14,416 = -16
            if($RemainSlabs < 0){
                $reqSlabs = $ActPoSlabs - $avlTotalSlabs;//144000-14,416=-16
                return (int)$reqSlabs;
            }
            return null;
        }
    }
    /*check slab data ends*/
}