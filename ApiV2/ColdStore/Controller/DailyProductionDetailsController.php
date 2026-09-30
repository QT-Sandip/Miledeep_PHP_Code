<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
//include_once 'Controller/UtilController.php';
include_once '../ColdStore/Controller/UtilController.php';
// include_once 'Controller/DailyInwardDetailsController.php';
include_once '../ColdStore/Controller/DailyInwardDetailsController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class DailyProductionDetailsController
{
    private $conn;
    //private $db_table = "ScrmApplicants";
    private $utilController;
    private $dailyInwardDetailsController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->dailyInwardDetailsController = new DailyInwardDetailsController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'ColdStore');
    }

    public function getDailyProductionDetails($RequestBody, $IsDeductGlaze)
    {
        $ProductionDate = $RequestBody['ProductionDate'];
        $ProductionEndDate = $RequestBody['ProductionEndDate'];
        $UnitID = $RequestBody['ColdstoreID'];
        $StockHolderID = $RequestBody['StockHolderID'];
        $ProductionTypeID = $RequestBody['Group'];
        $PackingTypeID = $RequestBody['PackingType'];
        $ProductionID = $RequestBody['ProductionID'];
        $Supc = $RequestBody['Supc'];
        $Status = $RequestBody['Status'];
        $DateType = $RequestBody['DateType'];
        $DateCondition = '=';
        if ($DateType == 2 && $Status == 0) {
            $DateCondition = " BETWEEN '2026-01-01' AND ";
        }


        // print_r("Status". $Status);
        // print_r("StProductionDateatus". $ProductionDate);
        $Query = '';

        if ($Supc != '') {
            $Query = " WHERE SPV.DeviSUPC='$Supc' ";
        }

        if ($ProductionID != '') {
            $PQuery = " where ProductionID='$ProductionID'";
        }

        if ($ProductionID != '') {
            $DailyProductionData = $this->getProductionDetails($PQuery, $IsDeductGlaze);
            $ProductionDate = $DailyProductionData['ProductionDate'];
        } else {
            $DailyProductionData['ProductionCode'] = str_replace("-", "", $ProductionDate);
            $DailyProductionData['BatchCode'] = $DailyProductionData['ProductionCode'] . "-" . rand(10, 100);
        }

        $ProductionDateFilter = '';

        if ($ProductionEndDate != '') {
            $ProductionDateFilter = " WHERE CSD.ProductionDate BETWEEN '$ProductionDate' AND '$ProductionEndDate' ";
        } else {
            $ProductionDateFilter = " WHERE CSD.ProductionDate $DateCondition '$ProductionDate'";
        }

        if ($ProductionDate != '' && $Status == '') {
            $Query = $ProductionDateFilter;
        } else if ($ProductionDate != '' && $Status == 1) {

            $Query = "  $ProductionDateFilter and CSD.StockStatus=1 "; //uma 13-09-2024
        } else { //uma 13-09-2024
            if ($ProductionDate != '') {
                $Query = "  $ProductionDateFilter and CSD.StockStatus=0";
            } else {
                $Query = "  WHERE  CSD.StockStatus=0";
            }
        }

        if ($UnitID != '') {
            $Query .= " and CSD.PlantID='$UnitID'";
        }
        if ($StockHolderID != '') {
            $Query .= " and CSD.StockHolderID='$StockHolderID'";
        }
        if ($ProductionTypeID != '') {
            $Query .= " and CSD.ProductionTypeID='$ProductionTypeID'";
        } else{
            $Query .= " and CSD.ProductionTypeID!='Update_Prod'";
        }
        if ($PackingTypeID != '') {
            $Query .= " and CSD.PackingTypeID='$PackingTypeID'";
        }


        $DailyProductionList = $this->getProductionlist($Query, $RequestBody);


        // $sqlLastest = "SELECT LAST_INSERT_ID() AS LastID FROM CsDailyProductionData";
        $sqlLastest = "SELECT MAX(ProductionID) AS LastID FROM CsDailyProductionData";
        $stmtLastestID = $this->conn->prepare($sqlLastest);
        $stmtLastestID->execute();
        $dataLastID = $stmtLastestID->fetch(PDO::FETCH_ASSOC);

        $LastID = $dataLastID['LastID'];


        // $ProdID = $this->postDailyProductionDetails($RequestBody)['NewAddedProdId'];

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['NewAddedProdId'] = $LastID;
        $Data['DailyProductionList'] = $DailyProductionList['ArrayData'];
        $Data['DailyProductionData'] = $DailyProductionData;
        $PageNo = $RequestBody['PageNo'];
        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Data['PageStart'] = $LimitMax + 1;
        $Data['TotalCount'] = $DailyProductionList['TotalCount'];
        $Data['PageCount'] = $DailyProductionList['PageCount'];
        $Data['totalNoOfCases'] = $DailyProductionList['totalNoOfCases'];
        //ar_dump($Data);
        return $Data;
    }

    function getProductionlist($Query, $RequestBody)
    {

        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $ShowBatchCode = $RequestBody['ShowBatchCode'];

        $Status = $RequestBody['Status'];
        $IsDeductGlaze = $RequestBody['IsDeductGlaze'];


        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $SearchQuery = " and CONCAT_WS('', SPV.DeviSupc, ProductDescription,ProductionID,`AvalMcs`, `LooseBags`, `PackingTypeID`, CSD.`Grade`,
            `PoGradeId`, CSD.`FreezingType`, CSD.`VarietyID`, `Galze`,`SockingType`, `Salt`, `ProductionDate`, CSD.`PoID`, `RmLotID`,
            `ProductionTypeID`, `ProductCategoryID`, `UnitID`, `ShiftID`, `BarCodeID`, CSD.`PackingStyleID`, `StockHolderID`, CSD.`Remarks`, CSD.PlantID,
            `StockStatus`, `AbStatus`, `ProdDayCode`,`ProdBatchCode`, `SoakingDate`,`TubNo`, `LineNumber`,`PoNo`,AGO.Name,ShortName,
            APS.PackingStyle,TenantName,StoreName,PoNo,RPS.StationName,TrackCode) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(*) as Total  FROM   `CsDailyProductionData` as CSD
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        left join AdminGeneralOptions as AGO on AGO.OptID=CSD.ShiftID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        left join CsTenant as CST on CST.TenantID=CSD.StockHolderID
        left join CsStores as CSS on CSS.StoreID=CSD.UnitID
        left join RmReceivingDetails as RRD on RRD.ReceivingID=CSD.TrackID
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID $Query order by ProductionDate Desc";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();

        $rowCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $rowCount['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $BatchQuery = '';
        $BatchFields = '';
        if ($ShowBatchCode == 1) {
            $BatchFields = ",RLD.LotNumber";
            $BatchQuery = "LEFT JOIN (
                                SELECT p1.*
                                FROM ProdFreezingData p1
                                INNER JOIN (
                                    SELECT TrackID, TubNo, MAX(ProductionID) AS MaxProdID
                                    FROM ProdFreezingData
                                    GROUP BY TrackID, TubNo
                                ) latest ON latest.TrackID = p1.TrackID
                                        AND latest.TubNo = p1.TubNo
                                        AND latest.MaxProdID = p1.ProductionID
                            ) AS PFD ON CSD.TrackID = PFD.TrackID AND CSD.TubNo = PFD.TubNo
                            LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.DataID = PFD.GradingBreakupID
                            LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID = PHGB.LotID
                            LEFT JOIN RmLotDetatils AS RLD ON RLD.RmLotID = PHGD.LotID";
        }

        $sqlFields = "SPV.DeviSupc, ProductDescription,CSD.ProductionID,`AvalMcs`, CSD.`LooseBags`, CSD.`PackingTypeID`,
        CSD.`Grade`, CSD.`PoGradeId`, CSD.`FreezingType`, CSD.`VarietyID`, CSD.`Galze`, CSD.`SockingType`, CSD.`Salt`,
        CSD.`ProductionDate`, CSD.`PoID`, CSD.`RmLotID`, CSD.`ProductionTypeID`, CSD.`ProductCategoryID`, CSD.`UnitID`,
        CSD.`ShiftID`, CSD.`BarCodeID`, CSD.`PackingStyleID`, CSD.`StockHolderID`, CSD.`Remarks`, CSD.PlantID, CSD.`StockStatus`,
        CSD.`AbStatus`, CSD.`ProdDayCode`,CSD.`ProdBatchCode`, CSD.`SoakingDate`, CSD.`TubNo`, CSD.`LineNumber`,`PoNo`,
        AGO.Name as Shift,ShortName as Variety, APS.PackingStyle,TenantName,StoreName,PoNo,RPS.StationName,
        AvalKgs as Kgs,ActualMcs,ActualLooseBags,TrackCode,FreezingProductionWeight,APS.`TotalSlabs`,CSD.MixedBatch";

        $sqlQuery = "SELECT $sqlFields $BatchFields  FROM `CsDailyProductionData` as CSD
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        left join AdminGeneralOptions as AGO on AGO.OptID=CSD.ShiftID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        left join CsTenant as CST on CST.TenantID=CSD.StockHolderID
        left join CsStores as CSS on CSS.StoreID=CSD.UnitID
        left join RmReceivingDetails as RRD on RRD.ReceivingID=CSD.TrackID $BatchQuery
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID $Query $SearchQuery
        order by CSD.ProductionDate Desc " . $Limit;


        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $i = $LimitMax;
        $totalNoOfCases = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {


            $AvalMcs = $dataRow['AvalMcs'];
            $LooseBags = $dataRow['LooseBags'];
            $ActualMcs = $dataRow['ActualMcs'];
            $ActualLooseBags = $dataRow['ActualLooseBags'];
            $BalanceMcs = $ActualMcs - $AvalMcs;
            $BalanceBags = $ActualLooseBags - $LooseBags;

            $TotalWeight = round($dataRow['Kgs'], 3);
            $aryBalance = $this->getBalanceKgs($TotalWeight, $dataRow['ProductionID'], $dataRow, $IsDeductGlaze);

            $BalanceMcs = $aryBalance['BalanceMcs'];
            $BalanceBags = $aryBalance['BalanceLooseBags'];
            $BalanceKgs = $aryBalance['BalanceWeight'];
             if ($BalanceKgs <= 0 && $BalanceMcs <= 0 && $BalanceBags <= 0) {
                $StatusCheck = 1;
            } else {
                if ($BalanceMcs <= 0 && $BalanceBags <= 0) {
                    $BalanceKgs = 0;
                    $StatusCheck = 1;
                } else {
                    $StatusCheck = 0;
                }
            }


            $this->dailyInwardDetailsController->postDailyproductionStatus($dataRow['ProductionID'], $AvalMcs, $LooseBags, $dataRow['Galze']);

            if ($Status == $StatusCheck) {
                $ArrayStockData['SNo'] = ++$i;
                $ArrayStockData['OurSupc'] = $dataRow['DeviSupc'];
                $ArrayStockData['ProductDescription'] = $dataRow['ProductDescription'];
                $ArrayStockData['ProductionID'] = $dataRow['ProductionID'];
                $ArrayStockData['TransactionDate'] = $dataRow['TransactionDate'];
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
                $ArrayStockData['BalanceMcs'] = $BalanceMcs;
                $ArrayStockData['BalanceBags'] = $BalanceBags;

                $ArrayStockData['LotNumber'] = !empty($dataRow['LotNumber']) ? $dataRow['LotNumber'] : (!empty($dataRow['MixedBatch']) ? 'M-' . $dataRow['MixedBatch'] : '');

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
                $ArrayStockData['GlazePerc'] = $dataRow['GlazePerc'];
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
                $ArrayStockData['BalanceMcs'] = $BalanceMcs;
                $ArrayStockData['BalanceBags'] = $BalanceBags;
                // $ArrayStockData['Kgs'] = round($TotalWeight,3);
                // $ArrayStockData['BalanceKgs'] = round( $BalanceKgs ,3);
                $ArrayStockData['Kgs'] = sprintf('%.3f', $TotalWeight);
                $ArrayStockData['BalanceKgs'] = sprintf('%.3f', $BalanceKgs);
                $ArrayStockData['Status'] = $StatusCheck;
                $ArrayStockData['TrackCode'] = $dataRow['TrackCode'];
                $ArrayStockData['ProductionWeight'] = $dataRow['FreezingProductionWeight'];

                $TotalSlabs = $dataRow['TotalSlabs'];
                $looseInMcs = round(($dataRow['LooseBags'] / $TotalSlabs), 2);
                $NoOfCases = round($AvalMcs + $looseInMcs, 3);
                $ArrayStockData['NoOfCases'] = $NoOfCases;
                $totalNoOfCases += $NoOfCases;

                $AryData[] = $ArrayStockData;
            }
        }
        if (count($AryData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['ArrayData'] = $AryData;
        $Data['TotalCount'] = $TotalCount;
        $Data['PageCount'] = $PageCount;
        $Data['RecordsAval'] = $RecordsAval;
        $Data['totalNoOfCases'] = $totalNoOfCases;
        return $Data;
    }

    function getProductionDetails($Query, $IsDeductGlaze)
    {

        $dataRow = null;
        $sqlFields = " ProductionID,`AvalMcs`, `LooseBags`, `PackingTypeID`, CSD.`Grade`,   `PoGradeId`, CSD.`FreezingType`, CSD.`VarietyID`, `Galze`,
        `SockingType`, `Salt`, `ProductionDate`, CSD.`PoID`, `RmLotID`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`,
        `ShiftID`, `BarCodeID`, CSD.`PackingStyleID`, `StockHolderID`, CSD.`Remarks`, CSD.PlantID,
        `StockStatus`, `AbStatus`, `ProdDayCode`,`ProdBatchCode`, `SoakingDate`, `TubNo`, `LineNumber`,`PoNo`,AGO.Name as Shift,ShortName as Variety,
        APS.PackingStyle, APS1.PackingStyle as ActualPackingStyle,TenantName,StoreName,PoNo,RPS.StationName,
        (ActualMcs*APS.TotalSlabs*APS.SlabWeight*APS.MFactor) as Kgs, (ActualLooseBags*APS.SlabWeight*APS.MFactor) as LKgs,ActualMcs,ActualLooseBags";

        $sqlQuery = "SELECT $sqlFields  FROM `CsDailyProductionData` as CSD
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        left join AdminGeneralOptions as AGO on AGO.OptID=CSD.ShiftID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        left join AdminPackingStyles as APS1 on APS1.PCKID=SPV.PackingStyle
        left join CsTenant as CST on CST.TenantID=CSD.StockHolderID
        left join CsStores as CSS on CSS.StoreID=CSD.UnitID
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID $Query";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $ArrayStockData = array();

        // $AvalMcs = $dataRow['AvalMcs'];
        // $LooseBags = $dataRow['LooseBags'];
        // $ActualMcs = $dataRow['ActualMcs'];
        // $ActualLooseBags = $dataRow['ActualLooseBags'];

        $TotalWeight = round($dataRow['Kgs'] + $dataRow['LKgs'], 3);
        $aryBalance = $this->getBalanceKgs($TotalWeight, $dataRow['ProductionID'], $dataRow, $IsDeductGlaze);
        $BalanceMcs = $aryBalance['BalanceMcs'];
        $BalanceBags = $aryBalance['BalanceLooseBags'];
        $BalanceKgs = $aryBalance['BalanceWeight'];

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
        $ArrayStockData['BalanceMcs'] = $BalanceMcs;
        $ArrayStockData['BalanceBags'] = $BalanceBags;

        $ArrayStockData['ProductionCode'] = $dataRow['ProdDayCode'];
        $ArrayStockData['RmLotID'] = $dataRow['RmLotID'];
        $ArrayStockData['BatchCode'] = $dataRow['ProdBatchCode'];
        $ArrayStockData['PoNumber'] = $dataRow['PoNo'];
        $ArrayStockData['Variety'] = $dataRow['Variety'];
        $ArrayStockData['VarietyID'] = $dataRow['VarietyID'];
        $ArrayStockData['PoGradeId'] = $dataRow['PoGradeId'];
        $ArrayStockData['PackingType'] = $dataRow['PackingTypeID'];
        $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
        $ArrayStockData['ActualPackingStyle'] = $dataRow['ActualPackingStyle'];
        $ArrayStockData['PackingStyleID'] = $dataRow['PackingStyleID'];
        $ArrayStockData['Grade'] = $dataRow['Grade'];
        $ArrayStockData['FreezingType'] = $dataRow['FreezingType'];
        $ArrayStockData['Galze'] = $dataRow['Galze'];
        $ArrayStockData['GlazePerc'] = $dataRow['Galze'];
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
        $ArrayStockData['Kgs'] = sprintf('%.3f', $TotalWeight);
        $ArrayStockData['BalanceKgs'] = sprintf('%.3f', $BalanceKgs);
        $ArrayStockData['Status'] = $Status;

        return $ArrayStockData;
    }


    public function postDailyProductionDetails($RequestBody)
    {

        $PostedUserID = 1;

        $ProductionID = $RequestBody['ProductionID'];
        $TransactionDate = $RequestBody['TransactionDate'];
        $ProductionDate = $RequestBody['ProductionDate'];
        $ProductionTypeID = $RequestBody['Group'];
        $StockHolderID = $RequestBody['StockHolderID'];
        $UnitID = $RequestBody['ColdstoreID'];
        $PlantID = $RequestBody['PlantID'];
        $ShiftID = $RequestBody['Shift'];
        $ProdDayCode = $RequestBody['ProductionCode'];
        $RmLotID = $RequestBody['RmLotID'];
        $ProdBatchCode = $RequestBody['BatchCode'];
        $PoID = $RequestBody['PoID'];
        $VarietyID = $RequestBody['Variety'];
        $PoGradeID = $RequestBody['PoGradeID'];
        $PackingTypeID = $RequestBody['PackingType'];
        $PackingStyleID = $RequestBody['PackingStyle'];
        $Grade = $RequestBody['Grade'];
        $FreezingType = $RequestBody['FreezingType'];
        $Galze = $RequestBody['Glaze'];
        $SockingType = $RequestBody['Soaking'];
        $ProductCategoryID = $RequestBody['ProductCategory'];
        $AbStatus = $RequestBody['AbStatus'];
        $LineNumber = $RequestBody['LineNumber'];
        $Remarks = $RequestBody['Remarks'];
        $StockOutID = $RequestBody['StockOutID'];
        $TrackID = $RequestBody['TrackID'];
        $AvalMcs = $RequestBody['Mcs'];
        $LooseBags = $RequestBody['LooseBags'];
        $FreezingProductionWeight = (isset($RequestBody['FreezingProductionWeight']) && $RequestBody['FreezingProductionWeight'] != '') ? $RequestBody['FreezingProductionWeight'] : 0;
        $AryCurrentDate = getCurrentDateTime();
        $CreatedDateTime = $AryCurrentDate['TimeStamp'];
        $Year = $AryCurrentDate['Year'];
        $TubNo = $RequestBody['TubNumberValue'];
        $IsDeductGlaze = $RequestBody['IsDeductGlaze'];
        $IsDeductGlazeFW = $RequestBody['IsDeductGlazeFW'];
        $IsDeductFWFCGlaze = $RequestBody['IsDeductFWFCGlaze'];
        $MixedBatchCode = $RequestBody['MixedBatchCode'];

        if ($PoGradeID != 0) {
            $SqlVarietyId = "SELECT VarietyID FROM `ScrmPoGradeData` WHERE ID='$PoGradeID'";
            $stmtVarietyId = $this->conn->prepare($SqlVarietyId);
            $stmtVarietyId->execute();
            $RowVarietyId = $stmtVarietyId->fetch(PDO::FETCH_ASSOC);
            $VarietyID = $RowVarietyId['VarietyID'];
        }

        $GlazeType = '';
        if ($VarietyID != '' && $VarietyID != '0' && $VarietyID != 0) {
            $SqlVariety = "SELECT Glaze FROM `ScrmPIVarietyList` WHERE VarietyID='$VarietyID'";
            $stmtVariety = $this->conn->prepare($SqlVariety);
            $stmtVariety->execute();
            $RowVariety = $stmtVariety->fetch(PDO::FETCH_ASSOC);
            $GlazeType = $RowVariety['Glaze'];
        }

        if ($FreezingProductionWeight && $FreezingProductionWeight > 0) {
            $ActualKgs = $FreezingProductionWeight;
        } else {
            $ActualKgs = $this->utilController->getCsStockAvalWeightinKgs($PackingStyleID, $AvalMcs, $LooseBags);
            // if ($StockOutID == '') {
            //     if(($IsDeductGlaze == 1 && $Galze > 0) || ($IsDeductGlaze == 0 && $IsDeductGlazeFW == 1 && (strpos($GlazeType, 'FW') === 0)  && $Galze > 0)){

            //         $GlazeWeight = ($ActualKgs * $Galze) / 100;
            //         $ActualKgs = $ActualKgs - $GlazeWeight;
            //     }
            // }
        }

        $stmt = false;

        if ((float) $FreezingProductionWeight != 0 && ((float) $ActualKgs != (float) $FreezingProductionWeight)) {
            $ActualKgs = $FreezingProductionWeight;
            $dataMcs = $this->getMcsandLoosebags($PackingStyleID, $FreezingProductionWeight);
            $AvalMcs = $dataMcs['Mcs'];
            $LooseBags = $dataMcs['Loose'];
        }

        if (strtolower($ProductionTypeID) == 'production') {
            if ($IsDeductGlazeFW == 1 && strpos($GlazeType, 'FW') === 0 && $Galze > 0) {
                $ActualKgs = ($ActualKgs / ((100 - $Galze) / 100));
                // $ActualKgs = $ActualKgs + $GlazeWeight;
            }
            if ($IsDeductFWFCGlaze == 1 && strpos($GlazeType, 'FW') === 0 && $Galze > 0) {
                $GlazeWeight = ($ActualKgs * $Galze) / 100;
                $ActualKgs = $ActualKgs - $GlazeWeight;
            }
        }

        if ($ProductionID == '') {
            // check doublicate entry
            $ProductionIDSql = "SELECT `ProductionID` FROM CsDailyProductionData
                                WHERE `ActualMcs`='" . (int) $AvalMcs . "' AND `ActualLooseBags`='" . $LooseBags . "' AND `PackingTypeID`='$PackingTypeID' AND
                                `Grade`='$Grade' AND `PoGradeId`='$PoGradeID' AND `FreezingType`='$FreezingType' AND `VarietyID`='$VarietyID' AND `Galze`='" . (int) $Galze . "' AND `SockingType`='$SockingType' AND
                                `ProductionDate`='$ProductionDate' AND `PoID`='$PoID' AND `RmLotID`='$RmLotID' AND
                                `ProductionTypeID`='$ProductionTypeID' AND `ProductCategoryID`='$ProductCategoryID' AND `UnitID`='$UnitID' AND `PlantID`='$PlantID' AND `ShiftID`='$ShiftID' AND
                                `PackingStyleID`='$PackingStyleID' AND `StockHolderID`='$StockHolderID' AND `Remarks`='$Remarks' AND `AbStatus`='$AbStatus' AND
                                `ProdDayCode`='$ProdDayCode' AND `ProdBatchCode`='$ProdBatchCode' AND `LineNumber` ='$LineNumber' AND `FreezingProductionWeight` ='$FreezingProductionWeight' AND MixedBatch='$MixedBatchCode' ";
            $stmtProductionID = $this->conn->prepare($ProductionIDSql);
            $stmtProductionID->execute();
            $ProductionID = $stmtProductionID->fetch(PDO::FETCH_ASSOC)['ProductionID'];
            if (!empty($ProductionID)) {
                $CntSql = "SELECT COUNT(`StockID`) AS CNT FROM CsStockData WHERE ProductionID=$ProductionID";
                $stmtCnt = $this->conn->prepare($CntSql);
                $stmtCnt->execute();
                $CNT = $stmtCnt->fetch(PDO::FETCH_ASSOC)['CNT'];
                if ($CNT > 0) {
                    $ProductionID = '';
                }
            }
        }
        /* if(!empty($RequestBody['MCSRestriction']) && $PoID && $PackingStyleID && $VarietyID
           // && $PackingTypeID == 'MASTER'
            ){
            $slabArr['PoID'] = $PoID;
            $slabArr['PackingStyleID'] = $PackingStyleID;
            $slabArr['VarietyID'] = $VarietyID;
            $slabArr['ProductionID'] = $ProductionID;
            $slabArr['PackingTypeID'] = $PackingTypeID;
            $slabArr['TxtMcs'] = $AvalMcs;
            $slabArr['TxtLooseBags'] = $LooseBags;
            $slabArr['UnitID'] = $UnitID;
            $slabArr['StockHolderID'] = $StockHolderID;
            $slabRes = $this->GetSlabData($slabArr);
            if($slabRes){
                // if($slabRes < 0){
                //     $slabRes = 0;
                // }
                $Data['mcsStatus'] = 1;
                $Data['Error'] = "Weight limit exceeded for the selected PO $slabRes";
                $Data['Msg'] = "Weight limit exceeded for the selected PO $slabRes ";
                return $Data;
            }
        } */

        if ($ProductionID == '') {
            if ($AvalMcs > 0 || $LooseBags > 0) {
                $sqlQuery = "INSERT INTO `CsDailyProductionData`(`ActualMcs`, `ActualLooseBags`, `AvalMcs`, `LooseBags`,
                `ActualKgs`,`AvalKgs`,
             `PackingTypeID`, `Grade`, `PoGradeId`, `FreezingType`, `VarietyID`, `Galze`, `SockingType`,
             `ProductionDate`, `PostedDate`,
              `PoID`, `RmLotID`, `ProductionTypeID`, `ProductCategoryID`, `UnitID`,`PlantID`, `ShiftID`, `BarCodeID`,
              `PackingStyleID`, `StockHolderID`, `Remarks`, `AbStatus`, `ProdDayCode`, `ProdBatchCode`,`LineNumber`,`StockOutID`,`TrackID`,`FreezingProductionWeight`,`TubNo`,MixedBatch)

              values('$AvalMcs','$LooseBags','$AvalMcs','$LooseBags','$ActualKgs','$ActualKgs','$PackingTypeID','$Grade','$PoGradeID',
              '$FreezingType','$VarietyID','$Galze','$SockingType','$ProductionDate','$CreatedDateTime','$PoID','$RmLotID','$ProductionTypeID',
              '$ProductCategoryID','$UnitID','$PlantID','$ShiftID','0','$PackingStyleID',
              '$StockHolderID','$Remarks','$AbStatus', '$ProdDayCode', '$ProdBatchCode','$LineNumber','$StockOutID','$TrackID','$FreezingProductionWeight','$TubNo','$MixedBatchCode')";

                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
                $ProductionID = $this->conn->lastInsertId();
                $this->auditLogger->logInsert('CsDailyProductionData', $ProductionID, $this->auditLogger->fetchRowById('CsDailyProductionData', 'ProductionID', $ProductionID), $RequestBody);
                $RequestBody['ProductionID'] = $ProductionID;
                $Data['Error'] = null;
                $Data['Msg'] = 'Posted Successfully';
                $Data['NewAddedProdId'] = $ProductionID;
                if ($FreezingProductionWeight > 0) {
                    $this->updateProductionData($TrackID, $IsDeductGlazeFW, $IsDeductFWFCGlaze);
                }
                $Data = $this->getDailyProductionDetails($RequestBody, $IsDeductGlaze);
                return $Data;
            } else {
                $Data['Error'] = 'Please Enter Valid Mcs';
                $Data['Msg'] = 'Please Enter Valid Mcs';
                return $Data;
            }
        } else if ($ProductionID != '') {


            $SqlStockData = "SELECT * from CsDailyProductionData where ProductionID ='$ProductionID'";
            $stmtStockData = $this->conn->prepare($SqlStockData);
            $stmtStockData->execute();
            $RowStockData = $stmtStockData->fetch(PDO::FETCH_ASSOC);

            // if ($RowStockData['ActualMcs'] == $RowStockData['AvalMcs']) {

            if ($RequestBody['Type'] == "ProductUpdate") {

                $sqlQuery = "UPDATE `CsDailyProductionData` SET  `ActualMcs`='$AvalMcs',
                `ActualLooseBags`='$LooseBags',`AvalMcs`='$AvalMcs',`LooseBags`='$LooseBags',`ActualKgs`='$ActualKgs',`AvalKgs`='$ActualKgs',
                `PackingTypeID`='$PackingTypeID',
                `Grade`='$Grade',`PoGradeId`='$PoGradeID',`FreezingType`='$FreezingType',`VarietyID`='$VarietyID',`Galze`='$Galze',`SockingType`='$SockingType',
                `ProductionDate`='$ProductionDate',`PostedDate`='$CreatedDateTime',`PoID`='$PoID',`RmLotID`='$RmLotID',
                `ProductionTypeID`='$ProductionTypeID',`ProductCategoryID`='$ProductCategoryID',`UnitID`='$UnitID',`PlantID`='$PlantID',`ShiftID`='$ShiftID',
                `PackingStyleID`='$PackingStyleID',`StockHolderID`='$StockHolderID',`Remarks`='$Remarks',`AbStatus`='$AbStatus',
                `ProdDayCode`='$ProdDayCode',`ProdBatchCode`='$ProdBatchCode',`LineNumber` ='$LineNumber',`StockOutID` ='$StockOutID',`TrackID`='$TrackID',`TubNo`='$TubNo'
                ,`FreezingProductionWeight` ='$FreezingProductionWeight',MixedBatch='$MixedBatchCode' where `ProductionID`='$ProductionID'";
                // `TrackID` ='$TrackID',
                // echo $sqlQuery;exit;
                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
                $this->auditLogger->logUpdate('CsDailyProductionData', $ProductionID, $RowStockData, $this->auditLogger->fetchRowById('CsDailyProductionData', 'ProductionID', $ProductionID), $RequestBody);
                // $fetchRequest['ProductionDate'] = $RequestBody['ProductionDate'];
                $fetchRequest['ProductionID'] = $ProductionID;
                if ($stmt) {
                    $RequestBody['StockID'] = $RequestBody['Type'];
                    $RequestBody['ProductionID'] = $ProductionID; //Newly added for duplicate entry cases.
                    $this->dailyInwardDetailsController->postStockDetails($RequestBody);
                    $BalanceKgs = $this->getStatus($ProductionID, $AvalMcs, $LooseBags, $IsDeductGlaze)['BalanceKgs'];
                    // $this->dailyInwardDetailsController->postDailyproductionStatus($BalanceKgs, $ProductionID);
                    $this->dailyInwardDetailsController->postDailyproductionStatus($ProductionID, $AvalMcs, $LooseBags, $Galze);

                    $Data['Error'] = null;
                    $Data['Msg'] = 'Updated Successfully';
                    if ($FreezingProductionWeight > 0) {
                        $this->updateProductionData($TrackID, $IsDeductGlazeFW, $IsDeductFWFCGlaze);
                    }
                    return $this->getDailyProductionDetails($fetchRequest, $IsDeductGlaze);
                }
            } else if ($RequestBody['Type'] == 'McsUpdate') {
                // $Query = '';
                // $sqlPackingStyle = "SELECT TotalSlabs,SlabWeight,MFactor FROM `CsDailyProductionData` as CSD
                // left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
                // WHERE ProductionID='$ProductionID'";
                // $stmtPackingStyle = $this->conn->prepare($sqlPackingStyle);
                // $stmtPackingStyle->execute();
                // $dataRowPackingStyle = $stmtPackingStyle->fetch(PDO::FETCH_ASSOC);

                // $Kgs = $AvalMcs * $dataRowPackingStyle['TotalSlabs'] * $dataRowPackingStyle['SlabWeight'] * $dataRowPackingStyle['MFactor'];
                // $LKgs = $LooseBags * $dataRowPackingStyle['SlabWeight'] * $dataRowPackingStyle['MFactor'];
                // $ActualKgs = $TotalWeight = round($Kgs + $LKgs, 2);

                // $dataRowAvalMcs['ActualLooseBags'] = $LooseBags;
                // $dataRowAvalMcs['ActualMcs'] = $AvalMcs;
                // $aryBalance = $this->getBalanceKgs($TotalWeight, $ProductionID, $dataRowAvalMcs);

                // $BalanceKgs = $aryBalance['BalanceWeight'];
                // if ($BalanceKgs <= 0) {
                //     $Status = 1;
                // } else {
                //     $Status = 0;
                // }

                $ArrayStatus = $this->getStatus($ProductionID, $AvalMcs, $LooseBags, $IsDeductGlaze);
                $Status = $ArrayStatus['Status'];
                $ActualKgs = $ArrayStatus['ActualKgs'];

                $oldProdRow = $this->auditLogger->fetchRowById('CsDailyProductionData', 'ProductionID', $ProductionID);
                $sqlQuery = "UPDATE `CsDailyProductionData` SET
                 `ActualMcs`='$AvalMcs',
                `ActualLooseBags`='$LooseBags',
                `AvalMcs`='$AvalMcs',
                `LooseBags`='$LooseBags',
                `ActualKgs`='$ActualKgs',
                `AvalKgs`='$ActualKgs' , `StockStatus`='$Status'
                  where `ProductionID`='$ProductionID'";


                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
                $this->auditLogger->logUpdate('CsDailyProductionData', $ProductionID, $oldProdRow, $this->auditLogger->fetchRowById('CsDailyProductionData', 'ProductionID', $ProductionID),$RequestBody);
                // $fetchRequest['ProductionDate'] = $RequestBody['ProductionDate'];
                $fetchRequest['ProductionID'] = $ProductionID;
                if ($stmt) {
                    if ($FreezingProductionWeight > 0) {
                        $this->updateProductionData($TrackID, $IsDeductGlazeFW, $IsDeductFWFCGlaze);
                    }
                    $Data['Error'] = null;
                    $Data['Msg'] = 'Updated Successfully';
                    return $this->getDailyProductionDetails($fetchRequest, $IsDeductGlaze);
                }
            } else {
                // $fetchRequest['ProductionDate'] = $RequestBody['ProductionDate'];
                $fetchRequest['ProductionID'] = $ProductionID;
                $Data['Error'] = null;
                $Data['Msg'] = 'No Access To Edit';
                return $this->getDailyProductionDetails($fetchRequest, $IsDeductGlaze);
            }
        }
    }

    public function getBalanceKgs($TotalWeight, $ProductionID, $proddataRow, $IsDeductGlaze)
    {
        // $sqlFields = "sum((ActualMcs*TotalSlabs*SlabWeight*MFactor)) as Kgs,
        // sum((ActualLooseBags*SlabWeight*MFactor)) as LKgs,
        // sum(ActualMcs) as ActualMcs,
        // sum(ActualLooseBags) as ActualLooseBags";

        $sqlFields = " APS.UOM,APS.TotalSlabs,
    SUM(
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
    ) AS LKgs,

    SUM(ActualMcs) AS ActualMcs,
    SUM(ActualLooseBags) AS ActualLooseBags
";

        $sqlQuery = "SELECT $sqlFields
         FROM `CsStockData` as CSD
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID where ProductionID='$ProductionID' ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $ProductionTotalWeight = ($dataRow['Kgs'] + $dataRow['LKgs']);//51200
        if ($dataRow['UOM'] == 'GRAMS') {
            //$TotalWeight = $TotalWeight * 1000;//174000
        }
        //if ($proddataRow['Galze'] > 0 && $IsDeductGlaze == 1) {
        if ($proddataRow['Galze'] > 0) {
            $GlazeWeight = ($ProductionTotalWeight * $proddataRow['Galze']) / 100;
            $ProductionTotalWeight = $ProductionTotalWeight - $GlazeWeight;
        }
        $TotalWeight = round($TotalWeight - $ProductionTotalWeight, 3);
        $BalanceMcs = $proddataRow['ActualMcs'] - $dataRow['ActualMcs'];
        $BalanceLooseBags = $proddataRow['ActualLooseBags'] - $dataRow['ActualLooseBags'];
        if ($BalanceLooseBags < 0) {
            $TotalSlabs = $dataRow['TotalSlabs'];
            $looseMcs = ceil(abs($BalanceLooseBags) / $TotalSlabs);
            $BalanceMcs = $BalanceMcs - $looseMcs;
            $BalanceLooseBags = $TotalSlabs - (abs($BalanceLooseBags%$TotalSlabs));
        }
        $ArrayBalance['BalanceWeight'] = $TotalWeight;
        $ArrayBalance['BalanceLooseBags'] = $BalanceLooseBags;
        $ArrayBalance['BalanceMcs'] = $BalanceMcs;

        return $ArrayBalance;
    }

    public function deleteDailyProduction($RequestBody)
    {
        $ProdID = $RequestBody['ProductionID'];
        $ProductionDate = $RequestBody['ProductionDate'];
        $IsDeductGlaze = $RequestBody['IsDeductGlaze'];

        $sqlDelete = "SELECT count(*) StockCount FROM CsStockData  WHERE ProductionID ='$ProdID'";

        $stmtStockCount = $this->conn->prepare($sqlDelete);
        $stmtStockCount->execute();

        $Count = $stmtStockCount->fetch(PDO::FETCH_ASSOC);
        $StockCount = $Count['StockCount'];

        if ($StockCount == 0) {

            $sqlDelete = "DELETE FROM CsDailyProductionData WHERE ProductionID='$ProdID'";
        } else {
            $this->Error = 'Stock already available';
            $this->Msg = 'Stock already available';
        }

        $oldRow = $this->auditLogger->fetchRowById('CsDailyProductionData', 'ProductionID', $ProdID);
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();
        if ($ProdID != '') {
            $this->auditLogger->logDelete('CsDailyProductionData', $ProdID, $oldRow,$RequestBody);
        }
        $RequestBody['ProductionID'] = '';

        if ($stmtDelete) {
            $this->Error = null;
            $this->Msg = 'Deleted Successfully';
        }

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        return $this->getDailyProductionDetails($RequestBody, $IsDeductGlaze);
    }

    function getStatus($ProductionID, $AvalMcs, $LooseBags, $IsDeductGlaze)
    {
        $sqlPackingStyle = "SELECT TotalSlabs,SlabWeight,MFactor,CSD.AvalKgs FROM `CsDailyProductionData` as CSD
                left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
                WHERE ProductionID='$ProductionID'";
        $stmtPackingStyle = $this->conn->prepare($sqlPackingStyle);
        $stmtPackingStyle->execute();
        $dataRowPackingStyle = $stmtPackingStyle->fetch(PDO::FETCH_ASSOC);

        $Kgs = $AvalMcs * $dataRowPackingStyle['TotalSlabs'] * $dataRowPackingStyle['SlabWeight'] * $dataRowPackingStyle['MFactor'];
        $LKgs = $LooseBags * $dataRowPackingStyle['SlabWeight'] * $dataRowPackingStyle['MFactor'];
        $ActualKgs = $TotalWeight = round($Kgs + $LKgs, 3);
        $ActualKgs = $dataRowPackingStyle['AvalKgs'];

        $dataRowAvalMcs['ActualLooseBags'] = $LooseBags;
        $dataRowAvalMcs['ActualMcs'] = $AvalMcs;
        $aryBalance = $this->getBalanceKgs($TotalWeight, $ProductionID, $dataRowAvalMcs, $IsDeductGlaze);

        $BalanceKgs = $aryBalance['BalanceWeight'];
        if ($BalanceKgs <= 0) {
            $Status = 1;
        } else {
            $Status = 0;
        }
        $ArrayStatus['Status'] = $Status;
        $ArrayStatus['ActualKgs'] = $ActualKgs;
        $ArrayStatus['BalanceKgs'] = $BalanceKgs;

        return $ArrayStatus;
    }
    public function getPackingStyleDetails($RequestBody)
    {

        $PackingStyle = $RequestBody['PackingStyle'];


        $Query = '';

        if ($PackingStyle != '') {
            $Query .= "WHERE PCKID=" . $PackingStyle;
        }


        $sqlPacking = "SELECT TotalSlabs,SlabWeight,MFactor,PackingStyle FROM AdminPackingStyles $Query";
        $stmtPacking = $this->conn->prepare($sqlPacking);
        $stmtPacking->execute();
        $PackingStyleData = $stmtPacking->fetch(PDO::FETCH_ASSOC);




        // $ProdID = $this->postDailyProductionDetails($RequestBody)['NewAddedProdId'];

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['Data'] = $PackingStyleData;
        //ar_dump($Data);
        return $Data;
    }
    function getMcsandLoosebags($PackingStyleID, $ProductionWeight)
    {
        $ArrayMcsandLoosebags[] = '';
        $SqlgetMcsandLooseQuery = "SELECT TotalSlabs,SlabWeight,MFactor FROM `AdminPackingStyles` where PCKID='$PackingStyleID'";
        $StmtgetMcsandLooseQuery = $this->conn->prepare($SqlgetMcsandLooseQuery);
        $StmtgetMcsandLooseQuery->execute();
        $dataRowgetMcsandLooseQuery = $StmtgetMcsandLooseQuery->fetch(PDO::FETCH_ASSOC);

        $SlabWeight = $dataRowgetMcsandLooseQuery['SlabWeight'];
        $TotalSlabs = $dataRowgetMcsandLooseQuery['TotalSlabs'];
        $MFactor = $dataRowgetMcsandLooseQuery['MFactor'];
        $ProductionSlabs = floor($ProductionWeight / ($SlabWeight * $MFactor));
        $Mcs = floor($ProductionSlabs / $TotalSlabs);
        // $Loose = $ProductionSlabs % $TotalSlabs;

        $Loose = $ProductionSlabs - ($Mcs * $TotalSlabs);
        $ArrayMcsandLoosebags['Mcs'] = $Mcs;
        $ArrayMcsandLoosebags['Loose'] = $Loose;
        $ArrayMcsandLoosebags['TotalSlabs'] = $dataRowgetMcsandLooseQuery['TotalSlabs'];
        $ArrayMcsandLoosebags['SlabWeight'] = $dataRowgetMcsandLooseQuery['SlabWeight'];
        $ArrayMcsandLoosebags['MFactor'] = $dataRowgetMcsandLooseQuery['MFactor'];
        return $ArrayMcsandLoosebags;
    }

    private function updateProductionData($TrackID, $IsDeductGlazeFW, $IsDeductFWFCGlaze)
    {
        $sqlFreezing = "SELECT TrackID,ProductionDate,PackingTypeID,ProductCategoryID,PackingStyleID,AbStatus,LineNumber,Galze,UnitID,PlantID,StockHolderID,ProdDayCode,ProdBatchCode,

PoID,PoGradeID,Grade,SockingType,FreezingType,TubNo,VarietyID,SUM(ProductionWeight) AS TotalWeight,SUM(ProductionSlabs) AS TotalSlabs,ProductionTypeID,ShiftID,RmLotID

 FROM `ProdFreezingData` WHERE TrackID=$TrackID GROUP BY TrackID,ProductionDate,PackingTypeID,ProductCategoryID,PackingStyleID,AbStatus,LineNumber,

PoID,PoGradeID,Grade,SockingType,FreezingType,TubNo,VarietyID ORDER BY `ProductionID` ASC";

        // $stmtFreezing = $this->conn->query($sqlFreezing);
        // while ($dataFreezing = $stmtFreezing->fetch_array()) {

        $ProductionIdsList = [];

        $UpdateProductionIdsList = [];
        $InsertProductionIdsList = [];

        $stmtFreezing = $this->conn->prepare($sqlFreezing);
        $stmtFreezing->execute();
        while ($dataFreezing = $stmtFreezing->fetch(PDO::FETCH_ASSOC)) {

            $TrackID = $dataFreezing['TrackID'];

            $ProductionDate = $dataFreezing['ProductionDate'];

            $PackingTypeID = $dataFreezing['PackingTypeID'];

            $ProductCategoryID = $dataFreezing['ProductCategoryID'];

            $PackingStyleID = $dataFreezing['PackingStyleID'];

            $AbStatus = $dataFreezing['AbStatus'];

            $LineNumber = $dataFreezing['LineNumber'];

            $PoID = $dataFreezing['PoID'];

            $PoGradeID = $dataFreezing['PoGradeID'];

            $Grade = $dataFreezing['Grade'];

            $SockingType = $dataFreezing['SockingType'];

            $FreezingType = $dataFreezing['FreezingType'];

            $TubNo = $dataFreezing['TubNo'];

            $TotalSlabs = $dataFreezing['TotalSlabs'];

            $TotalWeight = $dataFreezing['TotalWeight'];

            $VarietyID = $dataFreezing['VarietyID'];

            $Galze = $dataFreezing['Galze'];

            $UnitID = $dataFreezing['UnitID'];

            $PlantID = $dataFreezing['PlantID'];

            $StockHolderID = $dataFreezing['StockHolderID'];

            $ProdDayCode = $dataFreezing['ProdDayCode'];

            $ProdBatchCode = $dataFreezing['ProdBatchCode'];

            $ProductionTypeID = $dataFreezing['ProductionTypeID'];

            $RmLotID = $dataFreezing['RmLotID'];

            $ShiftID = $dataFreezing['ShiftID'];





            $sqlProductionDataCount = "SELECT *  FROM `CsDailyProductionData` AS CSD

            WHERE TrackID='$TrackID'and ProductionDate='$ProductionDate'  and CSD.PackingTypeID='$PackingTypeID' and 

                CSD.ProductCategoryID='$ProductCategoryID' and CSD.PackingStyleID='$PackingStyleID' and AbStatus='$AbStatus'

                and LineNumber='$LineNumber' and PoID='$PoID' and PoGradeId='$PoGradeID' and CSD.Grade='$Grade'and CSD.SockingType='$SockingType'

                and CSD.FreezingType='$FreezingType' and CSD.VarietyID='$VarietyID' and TubNo=$TubNo";

            // $stmtProductionDataCount = $this->conn->query($sqlProductionDataCount);

            // $dataRowProductionDataCount = $stmtProductionDataCount->fetch_array();

            $stmtProductionDataCount = $this->conn->prepare($sqlProductionDataCount);
            $stmtProductionDataCount->execute();
            $dataRowProductionDataCount = $stmtProductionDataCount->fetch(PDO::FETCH_ASSOC);

            $sqlVariety = "SELECT Glaze,GlazePerc FROM ScrmPIVarietyList WHERE VarietyID=$VarietyID";
            $stmtVariety = $this->conn->prepare($sqlVariety);
            $stmtVariety->execute();
            $dataVariety = $stmtVariety->fetch(PDO::FETCH_ASSOC);


            $GlazeType = !empty($dataVariety['Glaze']) ? $dataVariety['Glaze'] : '';
            $GlazePerc = !empty($dataVariety['GlazePerc']) ? $dataVariety['GlazePerc'] : 0;


            if ($dataRowProductionDataCount['ProductionID'] != NULL) {

                $ProductionID = $dataRowProductionDataCount['ProductionID'];
                $ProductionIdsList[] = $ProductionID;
                $UpdateProductionIdsList[] = $ProductionID;
                $StoreActualKgs = $dataRowProductionDataCount['ActualKgs'];
                $StoreAvalKgs = $dataRowProductionDataCount['AvalKgs'];
                $StoreAvalMcs = $dataRowProductionDataCount['AvalMcs'];
                $StoreActualMcs = $dataRowProductionDataCount['ActualMcs'];
                $StoreLooseBags = $dataRowProductionDataCount['LooseBags'];
                $StoreActualLooseBags = $dataRowProductionDataCount['ActualLooseBags'];

                if ($TotalWeight != $StoreActualKgs) {

                    $sqlPacking = "SELECT * FROM AdminPackingStyles WHERE PCKID=$PackingStyleID";

                    // $stmtPacking = $this->conn->query($sqlPacking);

                    // $dataPacking = $stmtPacking->fetch_array();
                    $stmtPacking = $this->conn->prepare($sqlPacking);
                    $stmtPacking->execute();
                    $dataPacking = $stmtPacking->fetch(PDO::FETCH_ASSOC);

                    $TotalPackingSlabs = $dataPacking['TotalSlabs'];
                    $TotalSlabWeight = $dataPacking['SlabWeight'];
                    $Mfactor = $dataPacking['MFactor'];
                    $ActualMcs = floor($TotalSlabs / $TotalPackingSlabs);

                    // $ActualLooseBags = $TotalSlabs % $TotalPackingSlabs;
                    $ActualLooseBags = $TotalSlabs - ($ActualMcs * $TotalPackingSlabs);

                    $ActualKgs = $TotalWeight;
                    if (strtolower($ProductionTypeID) == 'production') {
                        if ($IsDeductGlazeFW == 1 && strpos($GlazeType, 'FW') === 0 && $GlazePerc > 0) {
                            $ActualKgs = ($ActualKgs / ((100 - $Galze) / 100));
                            // $ActualKgs = $ActualKgs + $GlazeWeight;
                        }
                        if ($IsDeductFWFCGlaze == 1 && strpos($GlazeType, 'FW') === 0 && $GlazePerc > 0) {
                            $GlazeWeight = ($ActualKgs * $Galze) / 100;
                            $ActualKgs = $ActualKgs - $GlazeWeight;
                        }
                    }

                    $AvalMcs = $ActualMcs - ($StoreActualMcs - $StoreAvalMcs);

                    $LooseBags = $ActualLooseBags - ($StoreActualLooseBags - $StoreLooseBags);

                    $AvalKgs = $ActualKgs - ($StoreActualKgs - $StoreAvalKgs);

                    $sqlQuery = "UPDATE `CsDailyProductionData` SET  `ActualMcs`='$ActualMcs',

                `ActualLooseBags`='$ActualLooseBags',`AvalMcs`='$AvalMcs',`LooseBags`='$LooseBags',`ActualKgs`='$ActualKgs',`AvalKgs`='$AvalKgs',

                `PackingTypeID`='$PackingTypeID',

                `Grade`='$Grade',`PoGradeId`='$PoGradeID',`FreezingType`='$FreezingType',`VarietyID`='$VarietyID',`Galze`='$Galze',`SockingType`='$SockingType',

                `ProductionDate`='$ProductionDate',`PoID`='$PoID',`RmLotID`='$RmLotID',

                `ProductionTypeID`='Production',`ProductCategoryID`='$ProductCategoryID',`UnitID`='$UnitID',`PlantID`='$PlantID',`ShiftID`='$ShiftID',

                `PackingStyleID`='$PackingStyleID',`StockHolderID`='$StockHolderID',`Remarks`='',`AbStatus`='$AbStatus',

                `ProdDayCode`='$ProdDayCode',`ProdBatchCode`='$ProdBatchCode',`LineNumber` ='$LineNumber',`StockOutID` ='0',`TrackID`='$TrackID',`TubNo`='$TubNo'

                ,`FreezingProductionWeight` ='$ActualKgs' where `ProductionID`='$ProductionID'";

                    // $stmtQuery = $this->conn->query($sqlQuery);
                    $stmtQuery = $this->conn->prepare($sqlQuery);
                    $stmtQuery->execute();

                    // echo print_r($sqlQuery);

                    // echo '<hr></hr>';
                }


            } else {



                $sqlProductionDataCount = "SELECT *  FROM `CsDailyProductionData` AS CSD

            WHERE TrackID='$TrackID'and ProductionDate='$ProductionDate' and CSD.VarietyID='$VarietyID' and CSD.PackingTypeID='$PackingTypeID' and TubNo=$TubNo and LineNumber='$LineNumber'";

                // $stmtProductionDataCount = $this->conn->query($sqlProductionDataCount);

                // $dataRowProductionDataCount = $stmtProductionDataCount->fetch_array();

                $stmtProductionDataCount = $this->conn->prepare($sqlProductionDataCount);
                $stmtProductionDataCount->execute();
                $dataRowProductionDataCount = $stmtProductionDataCount->fetch(PDO::FETCH_ASSOC);

                $sqlVariety = "SELECT Glaze,GlazePerc FROM ScrmPIVarietyList WHERE VarietyID=$VarietyID";
                $stmtVariety = $this->conn->prepare($sqlVariety);
                $stmtVariety->execute();
                $dataVariety = $stmtVariety->fetch(PDO::FETCH_ASSOC);


                $GlazeType = !empty($dataVariety['Glaze']) ? $dataVariety['Glaze'] : '';
                $GlazePerc = !empty($dataVariety['GlazePerc']) ? $dataVariety['GlazePerc'] : 0;


                if ($dataRowProductionDataCount['ProductionID'] != NULL) {

                    $ProductionID = $dataRowProductionDataCount['ProductionID'];
                    $ProductionIdsList[] = $ProductionID;
                    $UpdateProductionIdsList[] = $ProductionID;
                    $StoreActualKgs = $dataRowProductionDataCount['ActualKgs'];
                    $StoreAvalKgs = $dataRowProductionDataCount['AvalKgs'];
                    $StoreAvalMcs = $dataRowProductionDataCount['AvalMcs'];
                    $StoreActualMcs = $dataRowProductionDataCount['ActualMcs'];
                    $StoreLooseBags = $dataRowProductionDataCount['LooseBags'];
                    $StoreActualLooseBags = $dataRowProductionDataCount['ActualLooseBags'];

                    if ($TotalWeight != $StoreActualKgs) {

                        $sqlPacking = "SELECT * FROM AdminPackingStyles WHERE PCKID=$PackingStyleID";

                        // $stmtPacking = $this->conn->query($sqlPacking);

                        // $dataPacking = $stmtPacking->fetch_array();
                        $stmtPacking = $this->conn->prepare($sqlPacking);
                        $stmtPacking->execute();
                        $dataPacking = $stmtPacking->fetch(PDO::FETCH_ASSOC);

                        $TotalPackingSlabs = $dataPacking['TotalSlabs'];
                        $TotalSlabWeight = $dataPacking['SlabWeight'];
                        $Mfactor = $dataPacking['MFactor'];
                        $ActualMcs = floor($TotalSlabs / $TotalPackingSlabs);

                        // $ActualLooseBags = $TotalSlabs % $TotalPackingSlabs;
                        $ActualLooseBags = $TotalSlabs - ($ActualMcs * $TotalPackingSlabs);

                        $ActualKgs = $TotalWeight;
                        if (strtolower($ProductionTypeID) == 'production') {
                            if ($IsDeductGlazeFW == 1 && strpos($GlazeType, 'FW') === 0 && $GlazePerc > 0) {
                                $ActualKgs = ($ActualKgs / ((100 - $Galze) / 100));
                                // $ActualKgs = $ActualKgs + $GlazeWeight;
                            }
                            if ($IsDeductFWFCGlaze == 1 && strpos($GlazeType, 'FW') === 0 && $GlazePerc > 0) {
                                $GlazeWeight = ($ActualKgs * $Galze) / 100;
                                $ActualKgs = $ActualKgs - $GlazeWeight;
                            }
                        }

                        $AvalMcs = $ActualMcs - ($StoreActualMcs - $StoreAvalMcs);

                        $LooseBags = $ActualLooseBags - ($StoreActualLooseBags - $StoreLooseBags);

                        $AvalKgs = $ActualKgs - ($StoreActualKgs - $StoreAvalKgs);

                        $sqlQuery = "UPDATE `CsDailyProductionData` SET  `ActualMcs`='$ActualMcs',

                `ActualLooseBags`='$ActualLooseBags',`AvalMcs`='$AvalMcs',`LooseBags`='$LooseBags',`ActualKgs`='$ActualKgs',`AvalKgs`='$AvalKgs',

                `PackingTypeID`='$PackingTypeID',

                `Grade`='$Grade',`PoGradeId`='$PoGradeID',`FreezingType`='$FreezingType',`VarietyID`='$VarietyID',`Galze`='$Galze',`SockingType`='$SockingType',

                `ProductionDate`='$ProductionDate',`PoID`='$PoID',`RmLotID`='$RmLotID',

                `ProductionTypeID`='Production',`ProductCategoryID`='$ProductCategoryID',`UnitID`='$UnitID',`PlantID`='$PlantID',`ShiftID`='$ShiftID',

                `PackingStyleID`='$PackingStyleID',`StockHolderID`='$StockHolderID',`Remarks`='',`AbStatus`='$AbStatus',

                `ProdDayCode`='$ProdDayCode',`ProdBatchCode`='$ProdBatchCode',`LineNumber` ='$LineNumber',`StockOutID` ='0',`TrackID`='$TrackID',`TubNo`='$TubNo'

                ,`FreezingProductionWeight` ='$ActualKgs' where `ProductionID`='$ProductionID'";

                        // $stmtQuery = $this->conn->query($sqlQuery);
                        $stmtQuery = $this->conn->prepare($sqlQuery);
                        $stmtQuery->execute();

                        // echo print_r($sqlQuery);

                        // echo '<hr></hr>';
                    }


                } else {



                    $sqlPacking = "SELECT * FROM AdminPackingStyles WHERE PCKID=$PackingStyleID";

                    // $stmtPacking = $this->conn->query($sqlPacking);

                    // $dataPacking = $stmtPacking->fetch_array();
                    $stmtPacking = $this->conn->prepare($sqlPacking);
                    $stmtPacking->execute();
                    $dataPacking = $stmtPacking->fetch(PDO::FETCH_ASSOC);

                    $TotalPackingSlabs = $dataPacking['TotalSlabs'];

                    $TotalSlabWeight = $dataPacking['SlabWeight'];

                    $Mfactor = $dataPacking['MFactor'];

                    $AvalMcs = floor($TotalSlabs / $TotalPackingSlabs);

                    // $LooseBags = $TotalSlabs % $TotalPackingSlabs;

                    $LooseBags = $TotalSlabs - ($AvalMcs * $TotalPackingSlabs);

                    $ActualKgs = $TotalWeight;

                    if (strtolower($ProductionTypeID) == 'production') {
                        if ($IsDeductGlazeFW == 1 && strpos($GlazeType, 'FW') === 0 && $GlazePerc > 0) {
                            $ActualKgs = ($ActualKgs / ((100 - $Galze) / 100));
                            // $ActualKgs = $ActualKgs + $GlazeWeight;
                        }
                        if ($IsDeductFWFCGlaze == 1 && strpos($GlazeType, 'FW') === 0 && $GlazePerc > 0) {
                            $GlazeWeight = ($ActualKgs * $Galze) / 100;
                            $ActualKgs = $ActualKgs - $GlazeWeight;
                        }
                    }


                    $sqlQuery = "INSERT INTO `CsDailyProductionData`(`PackingCount`,`ActualMcs`, `ActualLooseBags`, `AvalMcs`, `LooseBags`, 

                `ActualKgs`,`AvalKgs`,

             `PackingTypeID`, `Grade`, `PoGradeId`, `FreezingType`, `VarietyID`, `Galze`, `SockingType`,  `Salt`,

             `ProductionDate`, `PostedDate`,

              `PoID`, `RmLotID`, `ProductionTypeID`, `ProductCategoryID`, `UnitID`,`PlantID`, `ShiftID`, `BarCodeID`,

              `PackingStyleID`, `StockHolderID`, `Remarks`, `StockStatus`,`AbStatus`, `ProdDayCode`, `ProdBatchCode`,`SoakingDate`,`LineNumber`,`StockOutID`,`IBTOutID`,`TrackID`,`FreezingProductionWeight`,`TubNo`,`ProductID`,`TransactionDate`)

              

              values('','$AvalMcs','$LooseBags','$AvalMcs','$LooseBags','$ActualKgs','$ActualKgs','$PackingTypeID','$Grade','$PoGradeID',

              '$FreezingType','$VarietyID','$Galze','$SockingType',0,'$ProductionDate','$ProductionDate','$PoID','$RmLotID','$ProductionTypeID',

              '$ProductCategoryID','$UnitID','$PlantID','$ShiftID','0','$PackingStyleID',

              '$StockHolderID','',0,'$AbStatus', '$ProdDayCode', '$ProdBatchCode','','$LineNumber','0',0,'$TrackID','$ActualKgs','$TubNo',0,'')";

                    // $stmtQuery = $this->conn->query($sqlQuery);

                    $stmtQuery = $this->conn->prepare($sqlQuery);
                    $stmtQuery->execute();

                    $ProductionIdsList[] = $this->conn->lastInsertId();
                    $InsertProductionIdsList[] = $this->conn->lastInsertId();
                    // echo $sqlQuery;

                }
            }

        }
        if (!empty($TrackID) && $TrackID != 0 && $TrackID != null) {
            $DeleteQuery = "";
            if (!empty($ProductionIdsList)) {
                $ProductionIds = implode(',', array_map('intval', $ProductionIdsList));
                $DeleteQuery = "AND ProductionID NOT IN ($ProductionIds)";
            }
            error_log("Error in Daily Productuion - Update List: " . $UpdateProductionIdsList);
            error_log("Error in Daily Productuion - Insert List: " . $InsertProductionIdsList);
            error_log("Error in Daily Productuion - List: " . $ProductionIdsList);
            $sqlDelete = "DELETE FROM CsDailyProductionData WHERE TrackID = $TrackID AND ProductionTypeID='Production' $DeleteQuery";

            $sqlUpdateDelete = "UPDATE `CsDailyProductionData` SET

                `ProductionTypeID`='Update_Prod',`StockStatus`=3 WHERE TrackID = $TrackID AND ProductionTypeID='Production' $DeleteQuery";

            $stmtDelete = $this->conn->prepare($sqlUpdateDelete);
            $stmtDelete->execute();
        }
    }
    function GetSlabData($slabArr = []){
        $Mcs = 0;
        $POSlabs = 0;
        $TotalMcs = 0;
        $TotalMcsStock = 0; //from repacking
        $ProductionSlabQty = $CurrentProductionSlabQty = $TotalSlab = $CurrentTotalSlab = 0;
        $mcsArr = [];
        if($slabArr['PoID'] && $slabArr['VarietyID']){
            $sqlSlabQuery = "SELECT PGD.Mcs,CPS.`PackingStyle`,CPS.`TotalSlabs` ,CPS.`SlabWeight`,CPS.MFactor,PGD.VarietyID
                            FROM ScrmPoGradeData PGD
                        LEFT JOIN AdminPackingStyles AS CPS ON PGD.PackingStyleID=CPS.PckID            
                        WHERE PGD.InvId='".$slabArr['PoID']."' AND VarietyID=".$slabArr['VarietyID'];
            $stmtSlab = $this->conn->prepare($sqlSlabQuery);
            $stmtSlab->execute();
            $RowSlab = $stmtSlab->fetch(PDO::FETCH_ASSOC);
            $Mcs = !empty($RowSlab['Mcs']) ? $RowSlab['Mcs'] : 0;
            $PackingStyleTotalSlabs = $POSlabs = !empty($RowSlab['TotalSlabs']) ? $RowSlab['TotalSlabs'] : 0;//20
            $VarietyID = !empty($RowSlab['VarietyID']) ? $RowSlab['VarietyID'] : '';//
            $PackingStyleSlabWeight = $POSlabWeight = !empty($RowSlab['SlabWeight']) ? $RowSlab['SlabWeight'] : 0;//
            $PackingStyleMFactor = $POMFactor = !empty($RowSlab['MFactor']) ? $RowSlab['MFactor'] : 0;//
            $PackingStyleID = !empty($RowSlab['PackingStyleID']) ? $RowSlab['PackingStyleID'] : '';//
            if(
                $slabArr['PackingTypeID'] === 'DUMMY' &&
                (!empty($slabArr['TxtMcs']) || !empty($slabArr['TxtLooseBags'])) &&
                !empty($slabArr['PackingStyleID']) &&
                $slabArr['PackingStyleID'] !== $PackingStyleID
            ){//6 X 1.8 KGS
                $PackingStyleSql = "SELECT TotalSlabs,SlabWeight,MFactor FROM AdminPackingStyles WHERE PckID=".$slabArr['PackingStyleID'];
                $stmtPackingStyle = $this->conn->prepare($PackingStyleSql);
                $stmtPackingStyle->execute();
                $RowPackingStyle = $stmtPackingStyle->fetch(PDO::FETCH_ASSOC);
                $PackingStyleTotalSlabs = $RowPackingStyle['TotalSlabs'];
                $PackingStyleSlabWeight = $RowPackingStyle['SlabWeight'];
                $PackingStyleMFactor = $RowPackingStyle['MFactor'];
            }
            //pomcs = 7200
            $POQty = $Mcs * $POSlabs * $POSlabWeight * $POMFactor;//16,344
            $EnteredSlabs = ((int)$slabArr['TxtMcs'] * $PackingStyleTotalSlabs) + (int)$slabArr['TxtLooseBags'];//40
            $EnteredSlabsQty = $EnteredSlabs * $PackingStyleSlabWeight * $PackingStyleMFactor;//431.3
            if (!empty($slabArr['VarietyID'])) {
                $where = '';
                if (!empty($RequestBody['ProductionID'])) {//edit case
                    $where .= ' AND ProductionID !=' . $slabArr['ProductionID'];
                }
                if($slabArr['UnitID']){
                    $where .= " AND UnitID = ".$slabArr['UnitID'];   
                }
                if($slabArr['StockHolderID']){
                    $where .= " AND StockHolderID = ".$slabArr['StockHolderID'];   
                }
                $csStockSql = "SELECT SUM(
                                            IFNULL(CDPD.AvalMcs, 0) 
                                            * IFNULL(CPS.TotalSlabs, 0)
                                            * IFNULL(CPS.SlabWeight, 0)
                                            * IFNULL(CPS.MFactor, 0)
                                        ) AS TotalMcsQty,

                                        SUM(
                                            IFNULL(CDPD.LooseBags, 0)
                                            * IFNULL(CPS.SlabWeight, 0)
                                            * IFNULL(CPS.MFactor, 0)
                                        ) AS TotalBagsQty
                                FROM `CsDailyProductionData` CDPD
                                LEFT JOIN AdminPackingStyles AS CPS ON CDPD.`PackingStyleID`=CPS.PckID 
                                WHERE  `VarietyID`=". $slabArr['VarietyID'] ."
                                -- AND PackingTypeID = 'MASTER' 
                                AND ProductionTypeID NOT IN ('Update_Prod')
                                 AND `PoID`=".$slabArr['PoID']." $where";
                $stmtCsStock = $this->conn->prepare($csStockSql);
                $stmtCsStock->execute();
                $TotalRes = $stmtCsStock->fetch(PDO::FETCH_ASSOC);
                $actualProdQty = (float)$TotalRes['TotalMcsQty'] + (float)$TotalRes['TotalBagsQty'];//
                
                $SqlStockOut = "SELECT SUM(`ActualOutKgs`) `ActualOutKgs` 
                                    FROM CsStockOutDetails 
                                    WHERE `StockID` IN ( SELECT `StockID` FROM CsStockData WHERE `VarietyID`=". $slabArr['VarietyID'] ." AND `PoID`=".$slabArr['PoID']." $where )";
                $stmtStockOut = $this->conn->prepare($SqlStockOut);
                $stmtStockOut->execute();
                $StockOutQty = $stmtStockOut->fetch(PDO::FETCH_ASSOC)['ActualOutKgs'];
                if(!empty($StockOutQty)){
                    $actualProdQty -= $StockOutQty;//300-300=0
                    if($actualProdQty < 0){
                        $actualProdQty = 0;
                    }
                }
                
                $CurrentProductionSlabQty = $ProductionSlabQty = $actualProdQty;//3734.15
                if(!empty($EnteredSlabsQty)){
                    $ProductionSlabQty += (float) ($EnteredSlabsQty);//+431.3=4,165.45
                } //2.27
                $TotalSlab = round(($ProductionSlabQty / ($POSlabWeight * $POMFactor)), 3);//include entered value//7,001
                $CurrentTotalSlab = round(($CurrentProductionSlabQty / ($POSlabWeight * $POMFactor)), 3);//not include etered value//6,969
                //mcs=1200
                $RemainSlabs = (($Mcs * $POSlabs) - $TotalSlab);//-1
                $RemainSlabsOld = ($Mcs * $POSlabs) - $CurrentTotalSlab;//not consider entered value//31
                $TotalMcs = ceil($TotalSlab / $POSlabs);//1751
                $remainMcs = $Mcs - $TotalMcs;//1750 - 1751 = -1  
                /* printf("%.20f\n", ($Mcs * $POSlabs));
                printf("%.20f\n", $TotalSlab);
                        echo '<pre>';var_dump($RemainSlabs);die;  */         
                if($RemainSlabs < 0){//16,328.11
                    if($RemainSlabsOld > $POSlabs){
                        $ReqMcs = floor((int)$RemainSlabsOld / $POSlabs);//7.75
                        $ReqKgs = ($ReqMcs * $POSlabs * $POSlabWeight * $POMFactor) + $CurrentProductionSlabQty;//16,341.73
                        $lbQty = $POQty - $ReqKgs;//loose qty 4,179.07
                        $ReqLooseBags = round(($lbQty / ($POSlabWeight * $POMFactor)), 3);
                        return "MCS-$ReqMcs, Loose Bags-$ReqLooseBags";
                    }elseif($RemainSlabsOld > 0){
                        return "MCS-0, Loose Bags-$RemainSlabsOld";
                    }
                    return "MCS-0, Loose Bags-0";
                   // return (int)$RemainSlabsOld;
                }
                return false;                
            }
        }
    }
}