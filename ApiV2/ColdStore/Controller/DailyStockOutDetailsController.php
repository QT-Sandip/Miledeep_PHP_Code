<?php

include_once '../Global/Util.php';
//include_once 'Controller/UtilController.php';

class DailyStockOutDetailsController
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

    public function getStockOutDetails($RequestBody)
    {

        $StockOutDate = $RequestBody['StockOutDate'];
        $UnitID = $RequestBody['ColdStoreID'];
        $StockHolderID = $RequestBody['StockHolderID'];
        $ProductionTypeID = $RequestBody['Group'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $PackingTypeID = $RequestBody['PackingType'];
        $ProductionID = $RequestBody['ProductionID'];
        $Status = $RequestBody['Status'];
        $IsStoreProductionLinked = $RequestBody['IsStoreProductionLinked'];
        $TillDateStockOut = $RequestBody['TillDateStockOut'];
        $IsDeductGlazeFW = $RequestBody['IsDeductGlazeFW'];

        $Query = '';

        if ($ProductionID != '') {
            $PQuery = " AND ProductionID='$ProductionID'";
        }

        if ($ProductionID != '') {
            $DailyProductionData = $this->getProductionDetails($PQuery);
            // $ProductionDate=$DailyProductionData['ProductionDate'];
        }

        if ($StockOutDate != '') {
            if ($TillDateStockOut == 1) {
                $Query = "  AND StockOutDate<='$StockOutDate'";
            } else {
                $Query = "  AND StockOutDate='$StockOutDate'";
            }
        }

        // if ($StockOutDate != '' && $Status == 1) {
        //     $Query = " AND StockOutDate='$StockOutDate' and DailyStockOutStatus=1 "; //uma 13-09-2024
        // } else { //uma 13-09-2024
        //     $Query = "  AND  DailyStockOutStatus=0";
        // }

        if ($UnitID != '') {
            $Query .= " and UnitID='$UnitID'";
        } else {
            if ($ProductionPlant != '' && $ProductionPlant != 0) {
                $Query .= " and CSD.PlantID='$ProductionPlant'";
            }
        }


        if ($StockHolderID != '') {
            $Query .= " and CSD.StockHolderID='$StockHolderID'";
        }
        if ($ProductionTypeID != '') {
            // $Query .= " and ProductionTypeID='$ProductionTypeID'";
            $Query .= " and OutType='$ProductionTypeID'";
        }
        if ($PackingTypeID != '') {
            $Query .= " and PackingTypeID='$PackingTypeID'";
        }

        $DailyProductionList = $this->getProductionlist($Query, $IsStoreProductionLinked, $StockOutDate, $TillDateStockOut,$IsDeductGlazeFW);

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['AddAccess'] = true;
        $Data['AddLink'] = 'daily-production-data.php?StockOutID=';
        $Data['DailyStockOutDataList'] = $DailyProductionList;

        return $Data;
    }

    function getProductionlist($Query, $IsStoreProductionLinked, $StockOutDate, $TillDateStockOut,$IsDeductGlazeFW)
    {

        $sqlFields = "SPV.DeviSupc, GROUP_CONCAT(CSOD.CsStockOutID) AS CsStockOutID,ProductDescription,CSD.ProductionID, CSD.`PackingTypeID`, CSD.`Grade`, 
        CSD.`PoGradeId`, CSD.`FreezingType`, CSD.`VarietyID`, CSD.`Galze`, `OutType`,
        CSD.`SockingType`, CSD.`Salt`, CSD.`ProductionDate`, CSD.`PoID`, CSD.`RmLotID`,  CSD.`ProductionTypeID`, CSD.`ProductCategoryID`, CSD.`UnitID`, 
        CSD.`ShiftID`, CSD.`BarCodeID`, CSD.`PackingStyleID`, CSD.`StockHolderID`, CSD.`Remarks`, CSD.PlantID,
        CSD.`StockStatus`, CSD.`AbStatus`, CSD.`ProdDayCode`,CSD.`ProdBatchCode`, CSD.`SoakingDate`, CSD.`TubNo`, CSD.`LineNumber`,`PoNo`,AGO.Name as Shift,ShortName as Variety,
        APS.PackingStyle,TenantName,StoreName,PoNo,RPS.StationName,
        GROUP_CONCAT(CSOTD.StockOutID) as StockOutID,CSOD.Reason,
        sum(CSOTD.OutMcs) as OutMcs,
        sum(CSOTD.OutLooseBags) as OutLooseBags, 
        sum(CSOTD.ActualOutKgs) as Kgs,
        sum(CSD.ActualMcs) as ActualMcs,
        sum(CSD.ActualLooseBags) as  ActualLooseBags,SPV.Glaze AS GlazeType,MAX(StockOutDate) AS StockOutDate,DATE_FORMAT(RRD.ArrivalDate, '%d-%m-%Y') AS ArrivalDate,  RRD.TrackCode,RLD.LotNumber,CDPD.ProdBatchCode";
        $sqlQuery = "SELECT $sqlFields  FROM `CsDailyStockOutData` as CSOD 
        left join CsStockOutDetails as CSOTD on CSOTD.CsStockOutID=CSOD.CsStockOutID
        left join CsStockData as CSD on CSOTD.StockID=CSD.StockID
         LEFT JOIN CsDailyProductionData AS CDPD ON CDPD.ProductionID=CSD.ProductionID
        LEFT JOIN RmReceivingDetails AS RRD ON RRD.ReceivingID=CDPD.TrackID
        LEFT JOIN (
                SELECT TrackID, TubNo, LineNumber, MAX(ProductionID) AS MaxDataID
                FROM ProdFreezingData
                GROUP BY TrackID, TubNo, LineNumber
            ) PFDx
            ON CDPD.TrackID = PFDx.TrackID 
            AND CDPD.TubNo = PFDx.TubNo 
            AND CDPD.LineNumber = PFDx.LineNumber
            LEFT JOIN ProdFreezingData PFD
            ON PFD.ProductionID = PFDx.MaxDataID
            left join PpcHLGradingBreakup as PGB on PFD.GradingBreakupID=PGB.DataID 
            left join RmLotDetatils as RLD on PGB.LotID=RLD.RmLotID 
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        left join AdminGeneralOptions as AGO on AGO.OptID=CSD.ShiftID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        left join CsTenant as CST on CST.TenantID=CSD.StockHolderID        
        left join CsStores as CSS on CSS.StoreID=CSD.UnitID
        LEFT JOIN CsTransactionTypes AS CTT ON CSOTD.OutType=CTT.TransCategory
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID 
        WHERE CTT.IsReturnable=1 and CTT.TransType=2 and IsActive=1 $Query group by CSD.VarietyID,CSD.PackingStyleID,CSOTD.OutType ORDER BY StockOutDate DESC";

        // echo $sqlQuery;exit;
        // CTT.IsReturnable=1

        //                 $sqlFields = "
//     SPV.DeviSupc,
//     CSOD.CsStockOutID,
//     CSOTD.StockOutID,
//     CSOD.Reason,
//     CSOTD.OutMcs,
//     CSOTD.OutLooseBags,
//     CSOTD.ActualOutKgs AS Kgs,
//     CSD.ActualMcs,
//     CSD.ActualLooseBags,
//     ProductDescription,
//     CSD.ProductionID,
//     CSD.PackingTypeID,
//     CSD.Grade,
//     CSD.PoGradeId,
//     CSD.FreezingType,
//     CSD.VarietyID,
//     CSD.Galze,
//     CSOTD.OutType,
//     SockingType,
//     Salt,
//     CSD.ProductionDate,
//     CSD.PoID,
//     CSD.RmLotID,
//     CSD.ProductionTypeID,
//     CSD.ProductCategoryID,
//     CSD.UnitID,
//     CSD.ShiftID,
//     CSD.BarCodeID,
//     CSD.PackingStyleID,
//     CSD.StockHolderID,
//     CSD.Remarks,
//     CSD.PlantID,
//     CSD.StockStatus,
//     CSD.AbStatus,
//     CSD.ProdDayCode,
//     CSD.ProdBatchCode,
//     CSD.SoakingDate,
//     CSD.TubNo,
//     CSD.LineNumber,
//     SPD.PoNo,
//     AGO.Name AS Shift,
//     SPV.ShortName AS Variety,
//     APS.PackingStyle,
//     CST.TenantName,
//     CSS.StoreName,
//     RPS.StationName,
//     SPV.Glaze AS GlazeType,
//     CSOD.StockOutDate
// ";

        // $sqlQuery = "
//     SELECT $sqlFields
//     FROM CsDailyStockOutData AS CSOD
//     LEFT JOIN CsStockOutDetails AS CSOTD ON CSOTD.CsStockOutID = CSOD.CsStockOutID
//     LEFT JOIN CsStockData AS CSD ON CSOTD.StockID = CSD.StockID
//     LEFT JOIN ScrmPoData AS SPD ON SPD.PoID = CSD.PoID
//     LEFT JOIN AdminGeneralOptions AS AGO ON AGO.OptID = CSD.ShiftID
//     LEFT JOIN ScrmPIVarietyList AS SPV ON SPV.VarietyID = CSD.VarietyID
//     LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CSD.PackingStyleID
//     LEFT JOIN CsTenant AS CST ON CST.TenantID = CSD.StockHolderID
//     LEFT JOIN CsStores AS CSS ON CSS.StoreID = CSD.UnitID
//     LEFT JOIN CsTransactionTypes AS CTT ON CSOTD.OutType = CTT.TransCategory
//     LEFT JOIN RmProcessingStations AS RPS ON RPS.ProcessingStationID = CSD.PlantID
//     WHERE CTT.IsReturnable = 1 AND CTT.TransType = 2 AND IsActive = 1
//     $Query
//     ORDER BY CSOD.StockOutDate DESC
// ";

        // echo $sqlQuery;exit;
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $TotalWeight = round($dataRow['Kgs'], 2);
            $aryBalance = $this->getBalanceKgs($TotalWeight, $dataRow['StockOutID'], $dataRow, $IsStoreProductionLinked, $StockOutDate, $TillDateStockOut,$IsDeductGlazeFW);
            $BalanceKgs = $aryBalance['BalanceWeight'];
            if ($BalanceKgs <= 0) {
                $Status = 1;
                $BalanceKgs = 0;
            } else {
                $Status = 0;
            }

            if ($Status == 0 || ($Status == 1 && $StockOutDate == $dataRow['StockOutDate'])) {
                $ArrayStockData['SNo'] = ++$i;
                $ArrayStockData['StockOutID'] = $dataRow['StockOutID'];
                $ArrayStockData['OurSupc'] = $dataRow['DeviSupc'];
                $ArrayStockData['ProductDescription'] = $dataRow['ProductDescription'];
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
                $ArrayStockData['Type'] = $dataRow['OutType'];
                $ArrayStockData['StockOutDate'] = $dataRow['StockOutDate'];
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
                // $ArrayStockData['Salt'] = $dataRow['Salt'];
                $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
                $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
                $ArrayStockData['AbStatus'] = $dataRow['AbStatus'];
                // $ArrayStockData['TubNo'] = $dataRow['TubNo'];
                $ArrayStockData['LineNumber'] = $dataRow['LineNumber'];
                $ArrayStockData['Remarks'] = $dataRow['Remarks'];
                // $ArrayStockData['Mcs'] = $dataRow['AvalMcs'];
                $ArrayStockData['Mcs'] = $dataRow['OutMcs'];
                // $ArrayStockData['LooseBags'] = $dataRow['LooseBags'];
                $ArrayStockData['LooseBags'] = $dataRow['OutLooseBags'];
                // $ArrayStockData['BalanceMcs'] = $BalanceMcs;
                // $ArrayStockData['BalanceBags'] = $BalanceBags;
                $ArrayStockData['Kgs'] = sprintf('%0.2f', $TotalWeight);
                $ArrayStockData['BalanceKgs'] = sprintf('%0.2f', $BalanceKgs);
                $ArrayStockData['Status'] = $Status;
                $ArrayStockData['GlazeType'] = $dataRow['GlazeType'];

                $ArrayStockData['ArrivalDate'] = $dataRow['ArrivalDate'];
                $ArrayStockData['LotNumber'] = $dataRow['LotNumber'];
                $ArrayStockData['ProdBatchCode'] = $dataRow['ProdBatchCode'];


                $AryData[] = $ArrayStockData;
            }
        }
        return $AryData;
    }

    public function getBalanceKgs($TotalWeight, $CsStockOutID, $proddataRow, $IsStoreProductionLinked, $StockOutDate, $TillDateStockOut,$IsDeductGlazeFW)
    {

        $Type = $proddataRow['Reason'];
        $Query = '';
        if ($TillDateStockOut == 1) {
            $Query = " AND CSD.ProductionDate<='$StockOutDate'";
        }

        if ($Type != "Melting" || $IsStoreProductionLinked == 0) {
            $sqlFields = "sum(ActualKgs) as Kgs,SPV.Glaze,SPV.GlazePerc";

            $sqlQuery = "SELECT $sqlFields    FROM 
             CsStockOutDetails as CSOTD 
            left join CsDailyProductionData as CSD on CSOTD.StockOutID=CSD.StockOutID  
            left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
            left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID where CSD.StockOutID in ($CsStockOutID) $Query";
        } else {

            $sqlReceivingQuery = "SELECT GROUP_CONCAT(DISTINCT RB.ReceivingID) AS TrackIds,RD.OutStage FROM RmReceivingBreakup AS RB LEFT JOIN RmReceivingDetails AS RD ON RB.ReceivingID=RD.ReceivingID WHERE RB.CsStockOutID IN (" . $proddataRow['CsStockOutID'] . ")";
            $stmtReceivingQuery = $this->conn->prepare($sqlReceivingQuery);
            $stmtReceivingQuery->execute();
            $dataReceivingQuery = $stmtReceivingQuery->fetch(PDO::FETCH_ASSOC);

            if ($dataReceivingQuery['OutStage'] == 1) {
                $sqlQuery = "SELECT SUM(SentWeight) AS Kgs FROM PpcValueAddition WHERE TrackID IN (" . $dataReceivingQuery['TrackIds'] . ")";
            } else if ($dataReceivingQuery['OutStage'] == 2) {
                $sqlQuery = "SELECT SUM(SoakInQty) AS Kgs FROM ProdSoakingDetails WHERE TrackID IN (" . $dataReceivingQuery['TrackIds'] . ")";
            } else if ($dataReceivingQuery['OutStage'] == 3) {
                $sqlQuery = "SELECT SUM(ProductionWeight+RejectionWeight) AS Kgs FROM ProdFreezingData WHERE TrackID IN (" . $dataReceivingQuery['TrackIds'] . ")";
            } else {
                $sqlQuery = "SELECT SUM(ReceivingQty) AS Kgs FROM RmReceivingBreakup WHERE ReceivingID IN (" . $dataReceivingQuery['TrackIds'] . ")";
            }
        }

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();


        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalWeight;
        $Glaze = isset($dataRow['Glaze']) ? $dataRow['Glaze'] : "";
        $GlazePerc = isset($dataRow['GlazePerc']) ? $dataRow['GlazePerc'] : "";
        // if($IsDeductGlazeFW == 1 && strpos($Glaze, 'FW') === 0 && $GlazePerc > 0){
        //     $TotalWeight = ($TotalWeight / ((100 - $GlazePerc )/100));
        // }

        $TotalWeight = round(round($TotalWeight, 2) - round($dataRow['Kgs'], 2), 2);
        $ArrayBalance['BalanceWeight'] = $TotalWeight;

        return $ArrayBalance;
    }
}