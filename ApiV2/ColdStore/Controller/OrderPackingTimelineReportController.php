<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';
include_once '../App/Controller/AppUtilController.php';

class OrderPackingTimelineReportController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
    private $utilController;
    private $appController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
        $this->appController = new AppUtilController($this->conn);
    }


    function getOrderPackingDetails($RequestBody)
    {
        $PoID = $RequestBody['PoID'];
        $Year = $RequestBody['Year'];
        $MethodType = $RequestBody['MethodType'];
        // $OrderStatus = $RequestBody['OrderStatus'];
          $ProductBatchCode = $RequestBody['ProductBatchCode'];
        $FromDate = $Year . '-04-01';
        $ToDate = ($Year + 1) . '-03-31';

        $Query = '';
        if ($PoID != '') {
            $Query .= " AND CSD.PoID = '$PoID'";
        }

        $sqlInwardFields = "SUM(CSD.ActualKgs) as KGs, SUM(CSD.ActualMcs) AS Mcs, SUM(CSD.ActualLooseBags) AS LooseBags, 
        SUM(CSD.ActualMcs*TotalSlabs) AS McsSlabs, CSD.ProductionDate, SPVL.ProductDescription, APS.PackingStyle, CSD.ProductionTypeID,APS.TotalSlabs,
        SPD.PoNo, CSD.PoID, CSD.PlantID, RPS.StationName, CSD.PackingTypeID, CSD.UnitID, CSR.StoreName,CSD.Grade,DATE_FORMAT(RRD.ArrivalDate, '%d-%m-%Y') AS ArrivalDate,  RRD.TrackCode,
        CASE 
        WHEN RLD.LotNumber IS NOT NULL AND RLD.LotNumber != '' 
            THEN RLD.LotNumber
        WHEN CSD.BatchCode IS NOT NULL AND CSD.BatchCode != '' 
            THEN CONCAT('P-', CSD.BatchCode)
        ELSE ''
        END AS LotNumber
        ,CDPD.ProdBatchCode";

        $sqlInwardSelect = "SELECT $sqlInwardFields FROM CsStockData AS CSD 
        left join CsDailyProductionData AS CDPD ON CSD.ProductionID=CDPD.ProductionID 
        left join RmReceivingDetails AS RRD ON CDPD.TrackID=RRD.ReceivingID
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
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID = CSD.VarietyID 
        LEFT JOIN AdminPackingStyles AS APS ON CSD.PackingStyleID = APS.PCKID
        LEFT JOIN ScrmPoData AS SPD ON CSD.PoID = SPD.PoID
        LEFT JOIN RmProcessingStations AS RPS ON CSD.PlantID = RPS.ProcessingStationID
        LEFT JOIN CsStores AS CSR ON CSD.UnitID = CSR.StoreID
        WHERE 1=1 $Query
        GROUP BY ProductionDate,CSD.VarietyID, APS.PackingStyle, ProductionTypeID, PackingTypeID,UnitID ORDER BY ProductionDate ASC";
        $stmtInwardSelect = $this->conn->prepare($sqlInwardSelect);
        $stmtInwardSelect->execute();

        $ArrayInwardData = [];
        $serialNumber = 1;
        while ($dataInward = $stmtInwardSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayInwardList['SNO'] = $serialNumber++;
            $ArrayInwardList['OutDate'] = "";
            $ArrayInwardList['ProductionDate'] = $dataInward['ProductionDate'] ? date('d-m-Y', strtotime($dataInward['ProductionDate'])) : '';
            $ArrayInwardList['ProductDescription'] = $dataInward['ProductDescription'];
            $ArrayInwardList['PackingStyle'] = $dataInward['PackingStyle'];
            $ArrayInwardList['ProductionTypeID'] = $dataInward['ProductionTypeID'] . ' +';
            $ArrayInwardList['PackingTypeID'] = $dataInward['PackingTypeID'];
            $ArrayInwardList['StationName'] = $dataInward['StationName'];
            $ArrayInwardList['StoreName'] = $dataInward['StoreName'];
            $ArrayInwardList['PoNo'] = $dataInward['PoNo'];
            $ArrayInwardList['KGs'] = round($dataInward['KGs'], 3);
            $TotalSlabs = $dataInward['TotalSlabs'];
            $ArrayInwardList['Mcs'] = $dataInward['Mcs'] + round(($dataInward['LooseBags']/$TotalSlabs),2);

            $ArrayInwardList['McsSlabs'] = $dataInward['McsSlabs'] + $dataInward['LooseBags'];
            $ArrayInwardList['LooseBags'] = $dataInward['LooseBags'];
            $ArrayInwardList['Grade'] = $dataInward['Grade'];

            $ArrayInwardList['ArrivalDate'] = $dataInward['ArrivalDate'];
            $ArrayInwardList['TrackCode'] = $dataInward['TrackCode'];
            $ArrayInwardList['LotNumber'] = $dataInward['LotNumber'];
             $ArrayInwardList['ProdBatchCode'] = $dataInward['ProdBatchCode'];

            $ArrayInwardData[] = $ArrayInwardList;
        }


        $sqlFields = " SUM(CSOD.OutMcs) AS Mcs, SUM(CSOD.OutLooseBags) AS LooseBags, RPS.StationName,CSOD.OutDate,
        SUM(CSOD.OutMcs*TotalSlabs*SlabWeight*MFactor) AS KGs, SUM(CSOD.OutLooseBags*SlabWeight*MFactor) AS LKGs,sum(CSOD.ActualOutKgs) as ActualOutKgs,
        SUM(CSOD.OutMcs*TotalSlabs) AS McsSlabs, CSD.ProductionDate, SPVL.ProductDescription, APS.PackingStyle,CSOD.OutType,APS.TotalSlabs,
        CSD.PackingTypeID,CSD.UnitID, CSR.StoreName,CSD.Grade,DATE_FORMAT(RRD.ArrivalDate, '%d-%m-%Y') AS ArrivalDate,  RRD.TrackCode,RLD.LotNumber";

        $sqlSelect = "SELECT $sqlFields FROM CsStockOutDetails AS CSOD
        LEFT JOIN CsStockData AS CSD ON CSOD.StockID = CSD.StockID
        left join CsDailyProductionData AS CDPD ON CSD.ProductionID=CDPD.ProductionID 
        left join RmReceivingDetails AS RRD ON CDPD.TrackID=RRD.ReceivingID
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
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID = CSD.VarietyID 
        LEFT JOIN AdminPackingStyles AS APS ON CSD.PackingStyleID = APS.PCKID
        LEFT JOIN ScrmPoData AS SPD ON CSD.PoID = SPD.PoID
        LEFT JOIN RmProcessingStations AS RPS ON CSD.PlantID = RPS.ProcessingStationID
        LEFT JOIN CsStores AS CSR ON CSD.UnitID = CSR.StoreID
        WHERE 1=1 $Query
        GROUP BY OutDate, CSD.VarietyID, APS.PackingStyle, OutType, PackingTypeID,CSD.UnitID ORDER BY OutDate ASC";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ArrayOutwardData = [];
        while ($dataOutward = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayOutwardList['SNO'] = $serialNumber++;
            $ArrayOutwardList['ProductDescription'] = $dataOutward['ProductDescription'];
            $ArrayOutwardList['OutDate'] = $dataOutward['OutDate'] ? date('d-m-Y', strtotime($dataOutward['OutDate'])) : '';
            $ArrayOutwardList['ProductionDate'] = "";
            $ArrayOutwardList['PackingStyle'] = $dataOutward['PackingStyle'];
            $ArrayOutwardList['ProductionTypeID'] = $dataOutward['OutType'] . ' -';
            $ArrayOutwardList['PackingTypeID'] = $dataOutward['PackingTypeID'];
            $ArrayOutwardList['StationName'] = $dataOutward['StationName'];
            $ArrayOutwardList['StoreName'] = $dataOutward['StoreName'];
            $ArrayOutwardList['PoNo'] = $dataOutward['PoNo'];
            // $ArrayOutwardList['KGs'] = $dataOutward['KGs'] + $dataOutward['LKGs'];
            $ArrayOutwardList['KGs'] = round($dataOutward['ActualOutKgs'],3) ;

            $TotalSlabs = $dataOutward['TotalSlabs'];
            $ArrayOutwardList['Mcs'] = $dataOutward['Mcs'] + round(($dataOutward['LooseBags']/$TotalSlabs),2);
            $ArrayOutwardList['McsSlabs'] = $dataOutward['McsSlabs'] + $dataOutward['LooseBags'];
            $ArrayOutwardList['LooseBags'] = $dataOutward['LooseBags'];
            $ArrayOutwardList['Grade'] = $dataOutward['Grade'];

            $ArrayOutwardList['ArrivalDate'] = $dataOutward['ArrivalDate'];
            $ArrayOutwardList['TrackCode'] = $dataOutward['TrackCode'];
            $ArrayOutwardList['LotNumber'] = $dataOutward['LotNumber'];

            $ArrayOutwardData[] = $ArrayOutwardList;
        }

        $ArrayData = array_merge($ArrayInwardData, $ArrayOutwardData);

        if ($MethodType == 'getShipoutPoNumber') {
            $PoNumbers = $this->getShipoutPoNumber($RequestBody);
        } else {
            $PoNumbers = $this->getPoNumber($RequestBody);
        }
        $StoreData = $this->getStoreWiseSummary($PoID);

        $Data['Error'] = null;
        $Data['Msg'] = 'Msg';
        $Data['OrderPackingData'] = $ArrayData;
        $Data['PoNumbersData'] = $PoNumbers;
        $Data['StoreData'] = $StoreData;
        $Data['ShipmentPoNumbersData'] = $this->getShipmentOutPoNumber($RequestBody);

        return $Data;
    }

    function getStoreWiseSummary($PoNumber)
    {
        $sqlStoreFields = "SUM(AvalMcs) AS AvalMcs, SUM(LooseBags) AS LooseBags, SUM(AvalKgs) AS AvalKgs,
        SPVL.ProductDescription,StoreName,StationName, CSD.PlantID, CSD.UnitID, CSD.Grade,
        SUM(AvalMcs*TotalSlabs) AS McsSlabs, FLOOR(SUM(LooseBags)/TotalSlabs) AS LooseMcs,APS.TotalSlabs,
        MOD(SUM(LooseBags),TotalSlabs) AS RemainLoose,APS.PackingStyle";
        $sqlStoreSelect = "SELECT $sqlStoreFields FROM CsStockData AS CSD
        LEFT JOIN ScrmPIVarietyList AS SPVL ON CSD.VarietyID = SPVL.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON CSD.PackingStyleID = APS.PCKID
        LEFT JOIN RmProcessingStations AS RPS ON CSD.PlantID = RPS.ProcessingStationID
        LEFT JOIN CsStores AS CSR ON CSD.UnitID = CSR.StoreID
        WHERE (AvalMcs !=0 OR LooseBags !=0) AND CSD.PoID = '$PoNumber' GROUP BY CSD.UnitID, CSD.VarietyID,CSD.PlantID";
        $stmtStoreSelect = $this->conn->prepare($sqlStoreSelect);
        $stmtStoreSelect->execute();
        // $errorInfo = $stmtStoreSelect->errorInfo();
        // var_dump($errorInfo);
        $ArrayStoreData = [];
        $i = 0;
        while ($dataStore = $stmtStoreSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayStoreList['SNO'] = ++$i;
            $TotalSlabs = $dataStore['TotalSlabs'];
            $ArrayStoreList['AvalMcs'] = $dataStore['AvalMcs'] + round(($dataStore['LooseBags']/$TotalSlabs),2);
            // $ArrayStoreList['AvalMcs'] = $dataStore['AvalMcs'];
            $ArrayStoreList['LooseBags'] = $dataStore['LooseBags'];
            $ArrayStoreList['AvalKgs'] = round($dataStore['AvalKgs'], 3);
            $ArrayStoreList['ProductDescription'] = $dataStore['ProductDescription'];
            $ArrayStoreList['PlantID'] = $dataStore['PlantID'];
            $ArrayStoreList['StationName'] = $dataStore['StationName'];
            $ArrayStoreList['UnitID'] = $dataStore['UnitID'];
            $ArrayStoreList['StoreName'] = $dataStore['StoreName'];
            $ArrayStoreList['McsSlabs'] = $dataStore['McsSlabs'] + $dataStore['LooseBags'];
            $ArrayStoreList['StoreAvalMcs'] = $dataStore['AvalMcs'] + $dataStore['LooseMcs'];
            $ArrayStoreList['RemainLoose'] = $dataStore['RemainLoose'];
            $ArrayStoreList['Grade'] = $dataStore['Grade'];
            $ArrayStoreList['PackingStyle'] = $dataStore['PackingStyle'];
            $ArrayStoreData[] = $ArrayStoreList;
        }

        return $ArrayStoreData;
    }

    function getPoNumber($RequestBody)
    { 
        $Onlyponumber = false;
        if( isset($RequestBody['OnlyPoNumbers']) && $RequestBody['OnlyPoNumbers'] ){
            $Onlyponumber = true;    
        }    
        $Year = $RequestBody['Year'];
        $OrderStatus = $RequestBody['OrderStatus'];
        $Plant = $RequestBody['PlantID'];
        $FromDate = $Year . '-04-01';
        $ToDate = ($Year + 1) . '-03-31';

        $Query = '';
        if ($Plant != '') {
            if ($OrderStatus == 1) { 
                $ProcessorSql = "SELECT GROUP_CONCAT(DISTINCT ExpID) ExpID FROM AdminExporterDetails 
                                WHERE ExpType IN(2,3) AND ProcessingStationID = '$Plant'";
                $stmtProcessor = $this->conn->prepare($ProcessorSql);
                $stmtProcessor->execute();
                $ExpID = $stmtProcessor->fetch(PDO::FETCH_ASSOC)['ExpID'];
                if(!empty($ExpID)){
                    $Query .= " AND SPD.ProcessorID IN ($ExpID) ";
                }     
            }else{
             $Query = " AND (FIND_IN_SET($Plant,ProductionPlantID) > 0)";
           // $Query .= " AND ProductionPlantID = '$Plant'";
            }
        }

        if ($OrderStatus == 1) {  // completed
            $sqlPoNumber = "SELECT PoID,PoNo,ProductionPlantID FROM ScrmPoData as SPD left JOIN  ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID
            WHERE SCI.StockOutDate!='' and SCI.CInvDate between '$FromDate' and '$ToDate' and SPD.Status not in (2,3,4) $Query order by PoID ASC";
        } else if ($OrderStatus == 0) { // pending
            $sqlPoNumber = "SELECT PoID,PoNo,ProductionPlantID FROM ScrmPoData as SPD 
            WHERE PoID not in (SELECT PiID FROM ScrmCustomInvoices WHERE StockOutDate!=''  ) and SPD.Status not in (2,3,4)  and SPD.PoDate between '$FromDate' and '$ToDate'  $Query order by PoID ASC";
        }
       //echo '<pre>';print_r($sqlPoNumber);die;

        $stmtPoNumber = $this->conn->prepare($sqlPoNumber);
        $stmtPoNumber->execute();

        $ArrayData = [];
        while ($dataRow = $stmtPoNumber->fetch(PDO::FETCH_ASSOC)) {
            $ArrayList['PoID'] = $dataRow['PoID'];
            $ArrayList['PoNo'] = $dataRow['PoNo'];
            $ArrayList['ProductionPlantID'] = $dataRow['ProductionPlantID'];

            $ArrayData[] = $ArrayList;
        }

        if($Onlyponumber){
            $Data['Error'] = null;
            $Data['Msg'] = 'Msg';
            $Data['PoNumbersData'] = $ArrayData;
            return $Data;
        }

        return $ArrayData;
    }

    function getShipmentOutPoNumber($RequestBody)
    {
        $Onlyponumber = false;
        if( isset($RequestBody['OnlyPoNumbers']) && $RequestBody['OnlyPoNumbers'] ){
            $Onlyponumber = true;    
        } 

        $Year = $RequestBody['Year'];
        $OrderStatus = $RequestBody['OrderStatus'];
        $Plant = $RequestBody['PlantID'];
        $FromDate = $Year . '-04-01';
        $ToDate = ($Year + 1) . '-03-31';

        $Query = '';
        if ($Plant != '') {
            $Query .= " AND ProductionPlantID = '$Plant'";
        }

        $SqlPoIds = "SELECT GROUP_CONCAT(DISTINCT(CAST(CSD.PoID AS UNSIGNED))) AS PoID  FROM `CsStockData` as CSD
        LEFT JOIN CsStockOutDetails AS CSOD ON CSOD.StockID=CSD.StockID       
        WHERE StockStatus=2 and OutType='Shipment Out'";
        $stmtPoIds = $this->conn->prepare($SqlPoIds);
        $stmtPoIds->execute();
        $dataRowPoIds = $stmtPoIds->fetch(PDO::FETCH_ASSOC);
        $PoID = $dataRowPoIds['PoID'];
        if ($PoID != '') {
            $Query .= " and SPD.PoID not in  ($PoID)";
        }

        if ($OrderStatus == 1) {  // completed
            $sqlPoNumber = "SELECT PoID,PoNo,ProductionPlantID FROM ScrmPoData as SPD left JOIN  ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID
            WHERE SCI.StockOutDate!='' and SPD.PoDate between '$FromDate' and '$ToDate' and SPD.Status not in (2,3,4) $Query order by PoID ASC";
        } else if ($OrderStatus == 0) { // pending
            $sqlPoNumber = "SELECT PoID,PoNo,ProductionPlantID FROM ScrmPoData as SPD 
            WHERE PoID not in (SELECT PiID FROM ScrmCustomInvoices WHERE StockOutDate!=''  ) and Status not in (2,3,4) $Query order by PoID ASC";
        }
        $stmtPoNumber = $this->conn->prepare($sqlPoNumber);
        $stmtPoNumber->execute();

        $ArrayData = [];
        while ($dataRow = $stmtPoNumber->fetch(PDO::FETCH_ASSOC)) {
            $ArrayList['PoID'] = $dataRow['PoID'];
            $ArrayList['PoNo'] = $dataRow['PoNo'];
            $ArrayList['ProductionPlantID'] = $dataRow['ProductionPlantID'];

            $ArrayData[] = $ArrayList;
        }
        $ReturnContainerPosSql = "SELECT SPD.PoID,SPD.PoNo,ProductionPlantID,CSD.AvalMcs as Mcs FROM ScrmPoData AS SPD
        LEFT JOIN CsStockData AS CSD ON SPD.PoID = CSD.PoID WHERE SPD.Status IN (4)
        AND (CSD.AvalMcs > 0 OR CSD.LooseBags > 0) AND CSD.ProductionTypeID='Return Container' AND CSD.StockStatus=1";
        $stmtReturnContainerPosSql = $this->conn->prepare($ReturnContainerPosSql);
        $stmtReturnContainerPosSql->execute();
         while ($dataRowReturnContainerPosSql = $stmtReturnContainerPosSql->fetch(PDO::FETCH_ASSOC)) {
            $ArrayList['PoID'] = $dataRowReturnContainerPosSql['PoID'];
            $ArrayList['PoNo'] = $dataRowReturnContainerPosSql['PoNo'];
            $ArrayList['ProductionPlantID'] = $dataRowReturnContainerPosSql['ProductionPlantID'];

            $ArrayData[] = $ArrayList;
         }
          if($Onlyponumber){
            $Data['Error'] = null;
            $Data['Msg'] = 'Msg';
            $Data['ShipmentPoNumbersData'] = $ArrayData;
            return $Data;
        }
        return $ArrayData;
    }

    function getShipoutPoNumber($RequestBody)
    {
        // $Year = $RequestBody['Year'];
        // $OrderStatus = $RequestBody['OrderStatus'];
        // $Plant = $RequestBody['PlantID'];
        // $FromDate = $Year . '-04-01';
        // $ToDate = ($Year + 1) . '-03-31';

        // $Query = '';
        // if ($Plant != '') {
        //     $Query .= " AND ProductionPlantID = '$Plant'";
        // }

        // if ($OrderStatus == 1) {  // completed
        //     $sqlPoNumber = "SELECT PoID,PoNo,ProductionPlantID FROM ScrmPoData as SPD left JOIN  ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID
        //     WHERE SCI.StockOutDate!='' and SPD.PoDate between '$FromDate' and '$ToDate' and Status not in (2,3,4) $Query order by PoID ASC";
        // } else if ($OrderStatus == 0) { // pending
        //     $sqlPoNumber = "SELECT PoID,PoNo,ProductionPlantID FROM ScrmPoData as SPD 
        //     WHERE PoID not in (SELECT PiID FROM ScrmCustomInvoices WHERE StockOutDate!=''  ) and Status not in (2,3,4) $Query order by PoID ASC";
        // }
        // $stmtPoNumber = $this->conn->prepare($sqlPoNumber);
        // $stmtPoNumber->execute();

        // $ArrayData = [];
        // while ($dataRow = $stmtPoNumber->fetch(PDO::FETCH_ASSOC)) {
        //     $ArrayList['PoID'] = $dataRow['PoID'];
        //     $ArrayList['PoNo'] = $dataRow['PoNo'];
        //     $ArrayList['ProductionPlantID'] = $dataRow['ProductionPlantID'];

        //     $ArrayData[] = $ArrayList;
        // }

        // return $ArrayData;

        // $ClientID = $RequestBody['ClientID'];
        // $IsCheckStockOutDateAtCs =$this->appController->getClientConfig($ClientID)['IsStockOutDateAtCs'];

        $Year = $RequestBody['Year'];
        $OrderStatus = $RequestBody['OrderStatus'];
        $Plant = $RequestBody['PlantID'];
        $FromDate = $Year . '-04-01';
        $ToDate = ($Year + 1) . '-03-31';

        $ClientID = $RequestBody['ClientID'];
        $IsCheckStockOutDateAtCs = $this->appController->getClientConfig($ClientID)['IsStockOutDateAtCs'];

        $SqlPoIds = "SELECT GROUP_CONCAT(DISTINCT(CAST(CSD.PoID AS UNSIGNED))) AS PoID  FROM `CsStockData` as CSD
        LEFT JOIN CsStockOutDetails AS CSOD ON CSOD.StockID=CSD.StockID       
        WHERE StockStatus=2 and OutType='Shipment Out'";
        $stmtPoIds = $this->conn->prepare($SqlPoIds);
        $stmtPoIds->execute();
        $dataRowPoIds = $stmtPoIds->fetch(PDO::FETCH_ASSOC);
        $PoID = $dataRowPoIds['PoID'];
        if ($PoID != '') {
            $Query = " and SPD.PoID not in  ($PoID)";
        }
        if ($IsCheckStockOutDateAtCs) {
            if ($OrderStatus == 1) {  // completed
                $sqlQuery = "SELECT SPD.PoID,SPD.PoNo,ProductionPlantID,
                (select sum(Mcs) from ScrmPoGradeData as SPGD where SPGD.InvId=SPD.PoID ) as Mcs FROM  ScrmPoData AS SPD
                left JOIN  ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID
                WHERE (SPD.PoID not in (select PiID from ScrmCustomInvoices where StockOutDateAtCs!='' )) and Status  in(5) and SCI.CInvID IS NOT NULL and SPD.PoDate between '$FromDate' and '$ToDate' $Query";
            } else {
                $sqlQuery = "SELECT SPD.PoID,SPD.PoNo,ProductionPlantID,
                (select sum(Mcs) from ScrmPoGradeData as SPGD where SPGD.InvId=SPD.PoID ) as Mcs FROM  ScrmPoData AS SPD
                left JOIN  ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID
                WHERE (SPD.PoID not in (select PiID from ScrmCustomInvoices where StockOutDateAtCs!='' )) and Status  in(5) and SCI.CInvID IS NULL $Query";
            }
        } else {
            $sqlQuery = "SELECT SPD.PoID,SPD.PoNo,ProductionPlantID,
            (select sum(Mcs) from ScrmPoGradeData as SPGD where SPGD.InvId=SPD.PoID ) as Mcs FROM  ScrmPoData AS SPD
            LEFT JOIN ScrmShipmentDetailsBreif AS SDB ON SPD.PoID=SDB.PiID 
            WHERE (SPD.PoID not in (select PiID from ScrmCustomInvoices where StockOutDate!='')) and Status  in(5)
            and  (TransportedDate='' OR TransportedDate IS NULL) $Query";
        }
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
}
