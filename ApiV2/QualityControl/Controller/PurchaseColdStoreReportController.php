<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class PurchaseColdStoreReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);

    }

    public function getPurchaseColdStoreDetails($RequestBody)
    {

        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $LotIds = isset($RequestBody['LotIds']) ? $RequestBody['LotIds'] : '';
        $DateFilter = "'$FromDate' AND '$ToDate'";
        $Type = isset($RequestBody['Type']) ? $RequestBody['Type'] : '';
        // $LotIds = isset($RequestBody['LotIds']) ? $RequestBody['LotIds'] : '';
        $PageNo = !empty($RequestBody['PageNo']) ? $RequestBody['PageNo'] : 1;
        $SearchKey = isset($RequestBody['SearchKey']) ? $RequestBody['SearchKey'] : '';
        $RecordsPerPage = 50;
        $Query = '';
        $defaultArrivalDate = '2026-02-01'; // From this date onwards, Total Deheading column will be considered
        $subQuery = '';
        if (!empty($LotIds)) {

            $BookingCode = "'" . implode("','", $LotIds) . "'";
            $Query = " And RBD.BookingNumber IN ($BookingCode) ";
            $subQuery = " WHERE RAD.ArrivalNumber IN($BookingCode) ";
            //   echo print_r($Query);exit;
        }


        if ($Type == 'GET_BATCH') {
            $sqlBatchNumber = " SELECT DISTINCT RBD.RmBookingID, RBD.BookingNumber  FROM RmBookingDetatils AS RBD
                    WHERE RBD.BookingDate BETWEEN   $DateFilter
                    ORDER BY RBD.BookingDate DESC, RBD.BookingNumber ASC";

            //   print_r($sqlBatchNumber);exit;

            $stmtLots = $this->conn->prepare($sqlBatchNumber);
            $stmtLots->execute();
            $BatchList = $stmtLots->fetchAll(PDO::FETCH_ASSOC);

            $response = [
                'Error' => null,
                'Msg' => 'Success',
                'PurchaseLotsData' => $BatchList
            ];

            echo json_encode($response);
            exit;
        }

        $RecordsPerPage = 50;
        $PageNo = max(1, (int) $PageNo);
        $offset = ($PageNo - 1) * $RecordsPerPage;

        /* -------- TOTAL ROW COUNT QUERY (WITHOUT LIMIT) -------- */
        $countSql = "
            SELECT COUNT(DISTINCT RAD.RmArrivalID) AS totalRows
            FROM RmBookingBreakUpDetails RBB
            LEFT JOIN RmBookingDetatils RBD ON RBB.BookingID = RBD.RmBookingID
            LEFT JOIN RmArrivalDetatils RAD ON RAD.ArrivalNumber = RBD.BookingNumber
            WHERE RBD.BookingDate BETWEEN $DateFilter $Query
        ";

        $stmtCount = $this->conn->prepare($countSql);
        $stmtCount->execute();
        $totalRows = (int) $stmtCount->fetchColumn();

        $fields = "RBD.BookingNumber,RAD.RmArrivalID,
                    RHD.HarvestDate,
                    RBB.Count AS BookingCount,
                    RBB.Weight AS BookingWeight,
                    RHBD.HarvestCount,
                    RHBD.HarvestWeight,
                    RAD.ArrivalDate,

                    RX.ReceivingQty,
                    RX.ReceivingCount,
                    RX.QuantityDifference,
                    RX.CountDifference,

                    RX.HonWeight,
                    RX.WtAfterDH,
                    RX.DeheadingWt,
                    RX.CountAfterDH,
                    RX.DeheadingCnt,
                    RX.deheadingYield,

                    RX.PackingGrade,
                    RX.HLCount,
                    RX.HLWeight,
                    RX.Variety,
                    RX.VaShortName,

                    RX.WtAftrVA,
                    RX.SentWeight,

                    RX.SoakInCount,
                    RX.SoakInQty,
                    RX.SoakOutQty,
                    RX.SoakOutCount,

                    RX.PackingTypeID,
                    RX.ProductionWeight,
                    RX.FreezingIn,
                    RX.PackingStyle,
                    RX.FreezingMcs,

                    RX.TotalFinished,

                
                    RX.TrackID,
                    RX.PackingStyleID,
                    RX.TubNo,
                    RX.LineNumber,
                    RX.VarietyID,

                    RBD.BookingDate,
                    RX.ValueAdditionDate,
                    RX.SoakingDate,
                    RX.FreezingDate";

        $SqlPurchaseColdstore = "SELECT
                                    $fields

                                FROM RmBookingBreakUpDetails RBB

                                LEFT JOIN RmBookingDetatils RBD ON RBB.BookingID = RBD.RmBookingID
                                LEFT JOIN (
                                    SELECT
                                        RHD.BookingID,
                                        -- GROUP_CONCAT(DISTINCT HB.Count ORDER BY HB.Count) AS HarvestCount,
                                        GROUP_CONCAT(DISTINCT RHD.CheckingCount ORDER BY RHD.CheckingCount) AS HarvestCount,
                                        -- GROUP_CONCAT(HB.TotalWeight ORDER BY HB.Count) AS HarvestWeight
                                        ((HB.TotalWeight + RHD.CheckingQty) - (HB.TotalCrates * RHD.MTCrateWeight))  AS HarvestWeight
                                    FROM RmHarvestingDetails RHD
                                    LEFT JOIN (
                                        SELECT HarvestingID, Count, SUM(Weight) AS TotalWeight, SUM(NoofCrates) AS TotalCrates
                                        FROM RmHarvestingBreakupDetails
                                        GROUP BY HarvestingID -- , Count
                                    ) HB ON HB.HarvestingID = RHD.HarvestingID
                                    GROUP BY RHD.BookingID
                                ) RHBD ON RHBD.BookingID = RBB.BookingID

                                LEFT JOIN RmHarvestingDetails RHD ON RHD.BookingID = RBB.BookingID
                                LEFT JOIN RmArrivalDetatils RAD ON RAD.ArrivalNumber = RBD.BookingNumber

                                LEFT JOIN (
                                    SELECT
                                        RAD1.RmArrivalID AS ArrivalID,

                                        RRB.ReceivingQty,
                                        RRB.ReceivingCount,
                                        (RRB.ReceivingQty - IFNULL(RAB.Weight,0)) AS QuantityDifference,
                                        (RRB.ReceivingCount - IFNULL(RAB.Count,0)) AS CountDifference,

                                        -- PD.HonWeight,
                                        -- CASE WHEN RAD1.ArrivalDate IS NULL OR RAD1.ArrivalDate <'$defaultArrivalDate' THEN PD.HonWeight ELSE RAB.Weight END AS HonWeight,
                                         PD.WtAfterDH AS DeheadingWt,
                                        -- CASE WHEN RAD1.ArrivalDate IS NULL OR  RAD1.ArrivalDate <'$defaultArrivalDate' THEN PD.WtAfterDH ELSE PD.TotalDeheadingWeight END AS WtAfterDH,
                                         PD.CountAfterDH AS DeheadingCnt,
                                        CASE WHEN RAD1.ArrivalDate IS NULL OR  RAD1.ArrivalDate <'$defaultArrivalDate' THEN PD.CountAfterDH ELSE RAB.Count END as CountAfterDH,
                                        -- CASE WHEN RAD1.ArrivalDate IS NULL OR  RAD1.ArrivalDate <'$defaultArrivalDate' THEN (PD.WtAfterDH/PD.HonWeight) ELSE (PD.TotalDeheadingWeight/RAB.Weight) END AS deheadingYield,
                                        -- (PD.WtAfterDH/PD.HonWeight) AS deheadingYield,
                                        /*
                                        CASE 
                                            WHEN PHGD.MixIDs IS NOT NULL THEN
                                                (
                                                    SELECT SUM(RABD.Weight)
                                                    FROM RmLotDetatils AS RLD
                                                    LEFT JOIN RmArrivalBreakUpDetails AS RABD 
                                                        ON RABD.ArrivalBrkUpID = RLD.BreakUpID
                                                    WHERE FIND_IN_SET(RLD.RmLotID, PHGD.MixIDs)
                                                )
                                            ELSE 
                                                CASE 
                                                    WHEN RAD1.ArrivalDate IS NULL 
                                                        OR RAD1.ArrivalDate < '$defaultArrivalDate' 
                                                    THEN PD.HonWeight 
                                                    ELSE RAB.Weight 
                                                END
                                        END AS HonWeight,*/
                                        PD.HonWeight ,
                                        
                                        CASE 
                                            WHEN PHGD.MixIDs IS NOT NULL THEN
                                                (
                                                    SELECT SUM(HLWeight)
                                                    FROM PpcHLGradingBreakup
                                                    WHERE FIND_IN_SET(LotID, PHGD.MixIDs)
                                                    AND CreatedFrom = 1
                                                ) 
                                            ELSE 
                                                CASE 
                                                    WHEN RAD1.ArrivalDate IS NULL 
                                                        OR RAD1.ArrivalDate < '$defaultArrivalDate' 
                                                    THEN PD.WtAfterDH 
                                                    ELSE PD.TotalDeheadingWeight 
                                                END
                                        END AS WtAfterDH,

                                        CASE 
                                            WHEN PHGD.MixIDs IS NOT NULL THEN
                                            (
                                                (
                                                    SELECT SUM(HLWeight)
                                                        FROM PpcHLGradingBreakup
                                                        WHERE FIND_IN_SET(LotID, PHGD.MixIDs)
                                                        AND CreatedFrom = 1                
                                                )
                                                /
                                                NULLIF(
                                                    /*
                                                    (
                                                    SELECT SUM(RABD.Weight)
                                                        FROM RmLotDetatils AS RLD
                                                        LEFT JOIN RmArrivalBreakUpDetails AS RABD 
                                                            ON RABD.ArrivalBrkUpID = RLD.BreakUpID
                                                        WHERE FIND_IN_SET(RLD.RmLotID, PHGD.MixIDs)
                                                    ),
                                                    */
                                                    PD.HonWeight,
                                                     0
                                                )
                                            )
                                            ELSE
                                            (
                                                /*
                                                CASE 
                                                    WHEN RAD1.ArrivalDate IS NULL 
                                                        OR RAD1.ArrivalDate < '$defaultArrivalDate'
                                                        THEN (PD.WtAfterDH/PD.HonWeight) 
                                                    ELSE 
                                                        (PD.TotalDeheadingWeight/RAB.Weight) 
                                                END */
                                                (PD.WtAfterDH/PD.HonWeight) 
                                            )
                                        END AS deheadingYield,

                                    
                                        GROUP_CONCAT(PHGB.PackingGrade ORDER BY PHGB.DataID) AS PackingGrade,
                                        GROUP_CONCAT(PHGB.HLCount ORDER BY PHGB.DataID) AS HLCount,
                                        GROUP_CONCAT(PHGB.HLWeight ORDER BY PHGB.DataID) AS HLWeight,
                                        GROUP_CONCAT(PVL.ShortName ORDER BY PHGB.DataID) AS Variety,

                                        GROUP_CONCAT(IFNULL(PVA.WtAftrVA,'-') ORDER BY PHGB.DataID) AS WtAftrVA,
                                        GROUP_CONCAT(IFNULL(PVA.SentWeight,'-') ORDER BY PHGB.DataID) AS SentWeight,
                                        GROUP_CONCAT(IFNULL(PVA.ProcessingDate,'-') ORDER BY PHGB.DataID) AS ValueAdditionDate,
                                        GROUP_CONCAT(IFNULL(PVA.VaShortName,'-') ORDER BY PHGB.DataID) AS VaShortName,

                                        GROUP_CONCAT(IFNULL(PSD.SoakInCount,'-') ORDER BY PHGB.DataID) AS SoakInCount,
                                        GROUP_CONCAT(IFNULL(PSD.SoakInQty,'-') ORDER BY PHGB.DataID) AS SoakInQty,
                                        GROUP_CONCAT(IFNULL(PSD.SoakOutQty,'-') ORDER BY PHGB.DataID) AS SoakOutQty,
                                        GROUP_CONCAT(IFNULL(PSD.SoakOutCount,'-') ORDER BY PHGB.DataID) AS SoakOutCount,
                                        GROUP_CONCAT(IFNULL(PSD.SoakingDate,'-') ORDER BY PHGB.DataID) AS SoakingDate,

                                        GROUP_CONCAT(IFNULL(PFD.PackingTypeID,'-') ORDER BY PHGB.DataID) AS PackingTypeID,
                                        GROUP_CONCAT(IFNULL(PFD.ProductionWeight,'-') ORDER BY PHGB.DataID) AS ProductionWeight,
                                        GROUP_CONCAT(IFNULL(PFD.FreezingIn,'-') ORDER BY PHGB.DataID) AS FreezingIn,
                                        -- GROUP_CONCAT(IFNULL(PFD.AbStatus,'-') ORDER BY PHGB.DataID) AS AbStatus,
                                        GROUP_CONCAT(IFNULL(PFD.ProductionDate,'-') ORDER BY PHGB.DataID) AS FreezingDate,
                                        GROUP_CONCAT(IFNULL(PFD.PackingStyleName,'-') ORDER BY PHGB.DataID) AS PackingStyle,
                                        GROUP_CONCAT(IFNULL(PFD.FreezingMcs,'-') ORDER BY PHGB.DataID) AS FreezingMcs,

                                        GROUP_CONCAT(IFNULL(CSD.TotalStockInKgs,'-') ORDER BY PHGB.DataID) AS StockInKgs,
                                        GROUP_CONCAT(IFNULL(CSD.TotalBalanceKgs,'-') ORDER BY PHGB.DataID) AS BalanceKgs,
                                        GROUP_CONCAT(IFNULL(CSO.TotalStockOutKgs,'-') ORDER BY PHGB.DataID) AS StockOutKgs,
                                        GROUP_CONCAT(IFNULL(CSSO.TotalShipmentOutKgs,'-') ORDER BY PHGB.DataID) AS ShipmentOutKgs,

                                        MAX(CSO.OutDate) AS OutDate,
                                        MAX(CSSO.ShipmentDate) AS ShipmentDate,

                                        GROUP_CONCAT(DISTINCT PFD.TrackID ORDER BY PHGB.DataID) AS TrackID,
                                        GROUP_CONCAT( PFD.PackingStyleID ORDER BY PHGB.DataID) AS PackingStyleID,
                                        GROUP_CONCAT( PFD.TubNo ORDER BY PHGB.DataID) AS TubNo,
                                        GROUP_CONCAT( PFD.LineNumber ORDER BY PHGB.DataID) AS LineNumber,
                                        GROUP_CONCAT( PFD.VarietyID ORDER BY PHGB.DataID) AS VarietyID,
                                        GROUP_CONCAT(DISTINCT CSRD.RackName ORDER BY PHGB.DataID) AS RackName,
                                        GROUP_CONCAT(DISTINCT CDPD.ProductionID ORDER BY PHGB.DataID) AS ProductionID,
                                        
                                        GROUP_CONCAT(IFNULL(PFD.TotalFinished,'-') ORDER BY PHGB.DataID) AS TotalFinished

                                    FROM RmArrivalDetatils RAD1

                                    -- LEFT JOIN RmArrivalBreakUpDetails RAB ON RAB.ArrivalID = RAD1.RmArrivalID
                                   LEFT JOIN ( -- In Miexed batch case we getting dublicate rows
                                        SELECT RAB.* 
                                        FROM RmArrivalBreakUpDetails RAB
                                        LEFT JOIN RmArrivalDetatils RAD ON RAB.ArrivalID = RAD.RmArrivalID
                                        $subQuery
                                        GROUP BY ArrivalID
                                    ) RAB ON RAB.ArrivalID = RAD1.RmArrivalID
                                    
                                    LEFT JOIN RmReceivingBreakup RRB ON RRB.ArrivalBreakupID = RAB.ArrivalBrkUpID

                                    --  LEFT JOIN PpcDeheading PD ON PD.TrackBreakUpID = RRB.ReceivalBreakupID AND PD.CreatedFrom=1
                                    LEFT JOIN ( -- In De Heading Breackup case we getting dublicate rows
                                        SELECT SUM(WtAfterDH) AS WtAfterDH,
                                        -- SUM(HonWeight) AS HonWeight
                                        RRB.ReceivingQty AS HonWeight
                                        ,SUM(TotalDeheadingWeight) AS TotalDeheadingWeight,SUM(CountAfterDH) AS CountAfterDH,TrackBreakUpID  
                                            FROM RmArrivalDetatils RAD 
                                            LEFT JOIN RmArrivalBreakUpDetails RAB ON RAB.ArrivalID = RAD.RmArrivalID
                                            LEFT JOIN RmReceivingBreakup RRB ON RRB.ArrivalBreakupID = RAB.ArrivalBrkUpID
                                            LEFT JOIN PpcDeheading PD1 ON PD1.TrackBreakUpID = RRB.ReceivalBreakupID AND PD1.CreatedFrom=1
                                            $subQuery
                                            GROUP BY PD1.TrackBreakUpID
                                    ) PD ON  PD.TrackBreakUpID = RRB.ReceivalBreakupID

                                    LEFT JOIN PpcHLGradingDetails PHGD ON PHGD.TrackBreakUpID = PD.TrackBreakUpID AND PHGD.CreatedFrom=1
                                    LEFT JOIN PpcHLGradingBreakup PHGB ON PHGB.MixedID = PHGD.MixIDs AND PHGB.CreatedFrom=1
                                    LEFT JOIN PpcHLVAVarietyList PVL ON PVL.VarietyID = PHGB.VarietyID
                                    LEFT JOIN (
                                        SELECT
                                            GROUP_CONCAT(PVA1.GradingBreakupID) AS GradingBreakupID,
                                            GROUP_CONCAT(IFNULL(PVA1.WtAftrVA,'-') SEPARATOR '|') AS WtAftrVA,
                                            GROUP_CONCAT(IFNULL(PVA1.SentWeight,'-') SEPARATOR '|') AS SentWeight,
                                            GROUP_CONCAT(IFNULL(PVA1.ProcessingDate,'-') SEPARATOR '|') AS ProcessingDate,
                                            GROUP_CONCAT(IFNULL(PVL1.ShortName,'-') SEPARATOR '|') AS VaShortName
                                        FROM PpcValueAddition PVA1
                                        LEFT JOIN PpcHLVAVarietyList PVL1 ON PVL1.VarietyID = PVA1.VarietyID
                                        WHERE PVA1.CreatedFrom=1
                                        GROUP BY PVA1.GradingBreakupID
                                    ) PVA
                                    ON FIND_IN_SET(PHGB.DataID, PVA.GradingBreakupID)

                                    LEFT JOIN (
                                        SELECT
                                            VABreakupID,
                                            GROUP_CONCAT(SckID) AS SckID,
                                            GROUP_CONCAT(SoakInQty SEPARATOR '|') AS SoakInQty,
                                            GROUP_CONCAT(SoakOutQty SEPARATOR '|') AS SoakOutQty,
                                            GROUP_CONCAT(SoakInCount SEPARATOR '|') AS SoakInCount,
                                            GROUP_CONCAT(SoakOutCount SEPARATOR '|') AS SoakOutCount,
                                            GROUP_CONCAT(SoakingDate SEPARATOR '|') AS SoakingDate
                                        FROM ProdSoakingDetails
                                        WHERE CreatedFrom=1
                                        GROUP BY VABreakupID
                                    ) PSD ON FIND_IN_SET(PHGB.DataID, PSD.VABreakupID)

                                    LEFT JOIN (
                                        SELECT
                                            COALESCE(FS.ProductionID, FG.ProductionID) AS ProductionID,
                                            COALESCE(FS.GradingBreakupID, FG.GradingBreakupID) AS GradingBreakupID,
                                            GROUP_CONCAT( COALESCE(FS.PackingTypeID, FG.PackingTypeID) SEPARATOR '|') AS PackingTypeID,
                                            GROUP_CONCAT( COALESCE(FS.ProductionWeight, FG.ProductionWeight) SEPARATOR '|') AS ProductionWeight,
                                            GROUP_CONCAT(
                                                COALESCE(
                                                    CONCAT(FS.ProductionWeight,'-',FS.TubNo),
                                                    CONCAT(FG.ProductionWeight,'-',FG.TubNo)
                                                ) SEPARATOR '|'
                                            ) AS FreezingIn,
                                            GROUP_CONCAT(
                                                CONCAT(
                                                    FLOOR(COALESCE(FS.ProductionSlabs, FG.ProductionSlabs) / NULLIF(APS.TotalSlabs,0)),
                                                    '/' ,
                                                    CEIL(COALESCE(FS.ProductionSlabs, FG.ProductionSlabs) % NULLIF(APS.TotalSlabs,0)) 
                                                    
                                                )
                                                SEPARATOR '|'
                                            ) AS FreezingMcs,

                                            GROUP_CONCAT(DISTINCT COALESCE(FS.AbStatus, FG.AbStatus) SEPARATOR '|') AS AbStatus,
                                            GROUP_CONCAT( COALESCE(FS.ProductionDate, FG.ProductionDate) SEPARATOR '|') AS ProductionDate,
                                            GROUP_CONCAT( APS.PackingStyle ,' (',FS.Grade,') ',' (',ShortName,') ',' (',FS.ProductCategoryID,') ' SEPARATOR '|') AS PackingStyleName,
                                            GROUP_CONCAT(DISTINCT FS.SoakingID SEPARATOR '|') AS SoakingID,

                                            COALESCE(FS.TrackID, FG.TrackID) AS TrackID,
                                            GROUP_CONCAT( FS.PackingStyleID SEPARATOR '|') AS PackingStyleID,
                                            GROUP_CONCAT( COALESCE(FS.TubNo, FG.TubNo) SEPARATOR '|') AS TubNo,
                                            GROUP_CONCAT(
                                                CONCAT('''', COALESCE(FS.LineNumber, FG.LineNumber), '''') 
                                                ORDER BY COALESCE(FS.LineNumber, FG.LineNumber)
                                                SEPARATOR '|'
                                            ) AS LineNumber,
                                            GROUP_CONCAT( COALESCE(FS.VarietyID, FG.VarietyID) SEPARATOR '|') AS VarietyID,

                                            GROUP_CONCAT(
                                            COALESCE(
                                                (FS.ProductionWeight + FS.RejectionWeight),
                                                (FG.ProductionWeight + FG.RejectionWeight)
                                            )
                                            SEPARATOR '|'
                                            ) AS TotalFinished
                                        FROM ProdFreezingData FS
                                        LEFT JOIN ProdFreezingData FG
                                            ON FG.GradingBreakupID = FS.GradingBreakupID
                                            AND FS.SoakingID IS NULL
                                        LEFT JOIN AdminPackingStyles APS
                                            ON APS.PCKID = COALESCE(FS.PackingStyleID, FG.PackingStyleID)
                                        left join ScrmPIVarietyList as SPV on SPV.VarietyID= COALESCE(FS.VarietyID, FG.VarietyID)
                                        GROUP BY COALESCE(FS.GradingBreakupID, FG.GradingBreakupID)
                                    ) PFD
                                        ON (
                                            FIND_IN_SET(PSD.SckID, REPLACE(PFD.SoakingID,'|',','))
                                            OR PFD.GradingBreakupID = PHGB.DataID
                                        )

                                    LEFT JOIN (
                                        SELECT TrackID, TubNo, LineNumber, VarietyID, GROUP_CONCAT(DISTINCT ProductionID) AS ProductionID
                                        FROM CsDailyProductionData
                                    ) CDPD ON CDPD.TrackID = PFD.TrackID AND FIND_IN_SET(CDPD.TubNo, PFD.TubNo) AND FIND_IN_SET(CDPD.LineNumber, PFD.LineNumber) AND FIND_IN_SET(CDPD.VarietyID, PFD.VarietyID)

                                    LEFT JOIN (
                                        SELECT ProductionID, GROUP_CONCAT(DISTINCT StockID) AS StockID, StoreRackID,
                                            GROUP_CONCAT((ActualMcs) SEPARATOR '|') AS TotalStockInKgs,
                                            GROUP_CONCAT((AvalMcs) SEPARATOR '|') AS TotalBalanceKgs
                                        FROM CsStockData
                                    ) CSD ON FIND_IN_SET(CSD.ProductionID, CDPD.ProductionID)

                                    LEFT JOIN (
                                        SELECT StockID, GROUP_CONCAT((OutMcs) SEPARATOR '|') AS TotalStockOutKgs, GROUP_CONCAT(OutDate SEPARATOR '|') AS OutDate
                                        FROM CsStockOutDetails
                                        WHERE OutType <> 'Shipment Out'
                                    ) CSO ON FIND_IN_SET(CSO.StockID, CSD.StockID)

                                    LEFT JOIN (
                                        SELECT StockID, GROUP_CONCAT((OutMcs) SEPARATOR '|') AS TotalShipmentOutKgs, GROUP_CONCAT(OutDate SEPARATOR '|') AS ShipmentDate
                                        FROM CsStockOutDetails
                                        WHERE OutType = 'Shipment Out'
                                    ) CSSO ON FIND_IN_SET(CSSO.StockID, CSD.StockID)
                                    
                                    LEFT JOIN CsStoresRackData CSRD ON CSRD.RowID = CSD.StoreRackID


                                    GROUP BY RAD1.RmArrivalID
                                ) RX ON RX.ArrivalID = RAD.RmArrivalID
                                WHERE RBD.BookingDate BETWEEN $DateFilter $Query order by RBD.RmBookingID ASC";

        $offset = ($PageNo - 1) * $RecordsPerPage;
        $SqlPurchaseColdstore .= " LIMIT $offset, $RecordsPerPage";


        // echo print_r($SqlPurchaseColdstore);
        // exit;



        $stmt = $this->conn->prepare($SqlPurchaseColdstore);
        $stmt->execute();
        // $errorInfo = $stmt->errorInfo();
        // var_dump($errorInfo);die;
        $i = 0;
        $PurchaseColdStoreList = [];
        $TotalYield = 0;
        $RowCount = 0;
        $TotalYields = 0;
        $FloorBalance = 0;


        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            //echo '<pre>';print_r($dataRow);die;
            $PurchaseColdStoreData = [];
            $deheadingYield = !empty($dataRow['deheadingYield']) ? round(($dataRow['deheadingYield'] * 100),2) : 0;
            $TotalYield += round(($dataRow['deheadingYield'] * 100), 2);
            $RowCount++;

            $GradingWeightArr = !empty($dataRow['HLWeight']) ? explode(',', $dataRow['HLWeight']) : [];
            $HLWeightArr = !empty($dataRow['WtAftrVA']) ? explode(',', $dataRow['WtAftrVA']) : [];
            $SentWeightArr = !empty($dataRow['SentWeight']) ? explode(',', $dataRow['SentWeight']) : [];
            $SoakInQtyArr = !empty($dataRow['SoakInQty']) ? explode(',', $dataRow['SoakInQty']) : [];
            $SoakOutQtyArr = !empty($dataRow['SoakOutQty']) ? explode(',', $dataRow['SoakOutQty']) : [];
            $ProductionWeightArr = !empty($dataRow['ProductionWeight']) ? explode(',', $dataRow['ProductionWeight']) : [];
            $FreezingInArr = !empty($dataRow['FreezingIn']) ? explode(',', $dataRow['FreezingIn']) : [];

            $PackingStyleIDArr = !empty($dataRow['PackingStyleID']) ? explode(',', $dataRow['PackingStyleID']) : [];
            $TubIdArr = !empty($dataRow['TubNo']) ? explode(',', $dataRow['TubNo']) : [];
            $VarietyIDArr = !empty($dataRow['VarietyID']) ? explode(',', $dataRow['VarietyID']) : [];
            $LineNumberArr = !empty($dataRow['LineNumber']) ? explode(',', $dataRow['LineNumber']) : [];

            $TotalFinishedArr = !empty($dataRow['TotalFinished']) ? explode(',', $dataRow['TotalFinished']) : [];
            //echo '<pre>';print_r($TotalFinishedArr);//die;

            $Yields = '';
            $FloorBalance = '';
            $TotalYields = 0;
            $FreezingYield = '';
            $SoakingYield = '';

            /* SAFEST length */
            $len = max(count($HLWeightArr), count($SentWeightArr), count($SoakInQtyArr), count($ProductionWeightArr), count($GradingWeightArr)
            , count($PackingStyleIDArr), count($TubIdArr), count($VarietyIDArr), count($LineNumberArr));
            $pack = $tub = $var = $lineNo = $HLWeightSumArr = $honFinishedSumArr = [];
            $FreezingInResults = [];
            for ($idx = 0; $idx < $len; $idx++) {

                $hlStr = isset($HLWeightArr[$idx]) ? trim($HLWeightArr[$idx]) : '';
                $gradeStr = isset($GradingWeightArr[$idx]) ? trim($GradingWeightArr[$idx]) : '';
                $sentStr = isset($SentWeightArr[$idx]) ? trim($SentWeightArr[$idx]) : '';
                $soakOutStr = isset($SoakOutQtyArr[$idx]) ? trim($SoakOutQtyArr[$idx]) : '';
                $prodStr = isset($ProductionWeightArr[$idx]) ? trim($ProductionWeightArr[$idx]) : '';
                $soakStr = isset($SoakInQtyArr[$idx]) ? trim($SoakInQtyArr[$idx]) : '';      
                $FreezingInStr = isset($FreezingInArr[$idx]) ? trim($FreezingInArr[$idx]) : ''; 
                
                $TotalFinishedStr = isset($TotalFinishedArr[$idx]) ? trim($TotalFinishedArr[$idx]) : ''; 
                // echo '<pre>';print_r($honFinishedSumArr);die;
                
                /* Soak In Qty */
                $SoakInQtySum = 0;
                if ($soakStr !== '' && $soakStr !== '-') {
                    foreach (explode('|', $soakStr) as $v) {
                        if (is_numeric($v)) {
                            $SoakInQtySum += (float) $v;
                        }
                    }
                }
                
                $SoakOutQtySum = 0;
                foreach (explode('|', $soakOutStr) as $v) {
                    if ($v !== '' && is_numeric($v)) {
                        $SoakOutQtySum += (float) $v;
                    }
                }

                $Syield = ($SoakInQtySum > 0 && $SoakOutQtySum > 0)
                    ? round(($SoakOutQtySum / $SoakInQtySum) * 100) : '-';
                $SoakingYield = ($SoakingYield === '') ? $Syield : $SoakingYield . ',' . $Syield;

                $ProdQtySum = 0;
                if ($prodStr !== '' && $prodStr !== '-') {
                    foreach (explode('|', $prodStr) as $v) {
                        if (is_numeric($v)) {
                            $ProdQtySum += (float) $v;
                        }
                    }
                }
                if ($FreezingInStr !== '' && $FreezingInStr !== '-') { 
                    $tubData = [];               
                    foreach (explode('|', $FreezingInStr) as $v) {

                        if (strpos($v, '-') !== false) {
                            list($weight, $tubNo) = explode('-', $v);

                            // Step 4: sum if same tubNo
                            if (!isset($tubData[$tubNo])) {
                                $tubData[$tubNo] = 0;
                            }
                            $tubData[$tubNo] += (float)$weight;
                        }
                    }

                    $temp = [];
                    foreach ($tubData as $tubNo => $weight) {
                        $temp[] = $weight;
                    }
                    $FreezingInResults[] = implode(',', $temp);
                }else{
                    $FreezingInResults[] = '-';
                }

             
                // freezing yield
                $fyield = ($SoakInQtySum > 0 && $ProdQtySum)
                    ? round(($ProdQtySum / $SoakInQtySum) * 100)
                    : '-';


                if ($fyield === '-') {
                    if ($gradeStr !== '' && $gradeStr !== '-') {

                        $HLWeightSum = 0;
                        if ($gradeStr !== '' && $gradeStr !== '-') {
                            foreach (explode('|', $gradeStr) as $v) {
                                if (is_numeric($v)) {
                                    $HLWeightSum += (float) $v;
                                }
                            }
                        }

                        /* Sent Weight */
                        $SentWeightSum = 0;
                        foreach (explode('|', $hlStr) as $v) {
                            if ($v !== '' && is_numeric($v)) {
                                $SentWeightSum += (float) $v;
                            }
                        }

                        $fyield = ($SentWeightSum > 0 && $ProdQtySum)
                            ? round(($ProdQtySum / $SentWeightSum) * 100)
                            : (($HLWeightSum > 0 && $ProdQtySum)
                                ? round(($ProdQtySum / $HLWeightSum) * 100)
                                : '-');
                        $FreezingYield = ($FreezingYield === '')
                            ? $fyield
                            : $FreezingYield . ',' . $fyield;
                    }
                } else {

                    $FreezingYield = ($FreezingYield === '')
                        ? $fyield
                        : $FreezingYield . ',' . $fyield;
                }

                if ($hlStr !== '' && $hlStr !== '-') {

                    /* HL Weight */
                    $HLWeightSum = 0;
                    foreach (explode('|', $hlStr) as $v) {
                        if ($v !== '' && is_numeric($v)) {
                            $HLWeightSum += (float) $v;
                        }
                    }

                    /* Sent Weight */
                    $SentWeightSum = 0;
                    foreach (explode('|', $sentStr) as $v) {
                        if ($v !== '' && is_numeric($v)) {
                            $SentWeightSum += (float) $v;
                        }
                    }

                    /* Yield */
                    $gyield = ($SentWeightSum > 0)
                        ? round(($HLWeightSum / $SentWeightSum) * 100)
                        : 0;



                    $gradingSum = 0;
                    if ($gradeStr !== '' && $gradeStr !== '-') {
                        foreach (explode('|', $gradeStr) as $v) {
                            if (is_numeric($v)) {
                                $gradingSum += (float) $v;
                            }
                        }
                    }

                    // $Sfloor = ( $HLWeightSum > 0 && $gradingSum > 0)
                    //     ? round($gradingSum - $HLWeightSum , 2) : 0;


                     //  floorbalance
                    $Sfloor = ( $SentWeightSum > 0 && $gradingSum > 0)
                        ? round($gradingSum - $SentWeightSum , 2) : 0;




                    $Yields = ($Yields === '') ? $gyield : $Yields . ',' . $gyield;
                    $FloorBalance = ($FloorBalance === '') ? $Sfloor : $FloorBalance . ',' . $Sfloor;

                    $TotalYields += $gyield;

                } else {
                    $Yields = ($Yields === '') ? '-' : $Yields . ',-';
                    $FloorBalance = ($FloorBalance === '') ? '-' : $FloorBalance . ',-';
                }
                if (!empty($PackingStyleIDArr[$idx])) {
                    $PackingStyleIDArrs = explode('|', $PackingStyleIDArr[$idx]);
                    $pack[] = implode(',',$PackingStyleIDArrs);
                    
                }
                if (!empty($TubIdArr[$idx])) {
                    $TubIdArrArr2 = explode('|', $TubIdArr[$idx]);
                    $tub[] = implode(',',$TubIdArrArr2);
                }
                if (!empty($VarietyIDArr[$idx])) {
                    $VarietyIDArr2 = explode('|', $VarietyIDArr[$idx]);
                    $var[] = implode(',',$VarietyIDArr2);
                }
                if (!empty($LineNumberArr[$idx])) {
                    $LineNumberArr2 = explode('|', $LineNumberArr[$idx]);
                    $lineNo[] = implode(',',$LineNumberArr2);
                }

                if ($gradeStr !== '' && $gradeStr !== '-') {
                    foreach (explode('|', $gradeStr) as $v) {
                        if (is_numeric($v)) {
                            $HLWeightSumArr[] = (float) $v;
                        }
                    }
                }
                if ($TotalFinishedStr !== '' && $TotalFinishedStr !== '-') {
                    foreach (explode('|', $TotalFinishedStr) as $v) {
                        if (is_numeric($v)) {
                            $honFinishedSumArr[] = (float) $v;
                        }
                    }
                }
                
            }

            $FreezingInQtyStr = implode('|', $FreezingInResults);
            $PurchaseColdStoreData['SNo'] = ++$i;
            $PurchaseColdStoreData['BatchCode'] = $dataRow['BookingNumber'];
            $PurchaseColdStoreData['BookingDate'] = $dataRow['BookingDate'];
            $PurchaseColdStoreData['ArrivalDate'] = $dataRow['ArrivalDate'];
            $PurchaseColdStoreData['ValueAdditionDate'] = $dataRow['ValueAdditionDate'];
            $PurchaseColdStoreData['SoakingDate'] = $dataRow['SoakingDate'];
            $PurchaseColdStoreData['FreezingDate'] = $dataRow['FreezingDate'];
            $PurchaseColdStoreData['OutDate'] = $dataRow['OutDate'];
            $PurchaseColdStoreData['ShipmentDate'] = $dataRow['ShipmentDate'];

            $PurchaseColdStoreData['BookingCount'] = $dataRow['BookingCount'];
            $PurchaseColdStoreData['BookingWeight'] = $dataRow['BookingWeight'];
            $PurchaseColdStoreData['HarvestDate'] = $dataRow['HarvestDate'];
            $PurchaseColdStoreData['HarvestCount'] = $dataRow['HarvestCount'];
            $PurchaseColdStoreData['HarvestWeight'] = $dataRow['HarvestWeight'];

            $PurchaseColdStoreData['ArrivalDate'] = $dataRow['ArrivalDate'];
            $PurchaseColdStoreData['ReceivingCount'] = $dataRow['ReceivingCount'];
            $PurchaseColdStoreData['ReceivingQty'] = $dataRow['ReceivingQty'];
            $PurchaseColdStoreData['QuantityDifference'] = $dataRow['QuantityDifference'];
            $PurchaseColdStoreData['CountDifference'] = $dataRow['CountDifference'];



            $bookingCount = !empty($dataRow['BookingCount']) ? (float) $dataRow['BookingCount'] : 0;
            $receivingCount = !empty($dataRow['ReceivingCount']) ? (float) $dataRow['ReceivingCount'] : 0;

            $bookingWeight = !empty($dataRow['BookingWeight']) ? (float) $dataRow['BookingWeight'] : 0;
            $receivingQty = !empty($dataRow['ReceivingQty']) ? (float) $dataRow['ReceivingQty'] : 0;



            // $PurchaseColdStoreData['CountDifference'] = round($receivingCount - $bookingCount, 2);
            // $PurchaseColdStoreData['QuantityDifference'] = round($receivingQty - $bookingWeight, 2);
            $PurchaseColdStoreData['HonWeight'] = $dataRow['HonWeight'];
            $PurchaseColdStoreData['WtAfterDH'] = $dataRow['WtAfterDH'];
            //$PurchaseColdStoreData['CountAfterDH'] = $dataRow['CountAfterDH'];

            $NoofPcs =$dataRow['DeheadingCnt'];
            $HLSampleQty =number_format( $dataRow['DeheadingWt'],3);
            
            $HLCount = round(($NoofPcs / $HLSampleQty) * 0.454, 2);
            $PurchaseColdStoreData['ctHLCount'] =number_format($HLCount,2); 

            $PurchaseColdStoreData['CountAfterDH'] = round((($dataRow['CountAfterDH'] / $deheadingYield) * 100), 2);
            $PurchaseColdStoreData['Yield'] = round($deheadingYield, 2);
            $PurchaseColdStoreData['PackingGrade'] = $dataRow['PackingGrade'];
            $PurchaseColdStoreData['HLCount'] = $dataRow['HLCount'];
            $PurchaseColdStoreData['HLWeight'] = $dataRow['HLWeight'];
            $PurchaseColdStoreData['Variety'] = $dataRow['Variety'];
            $PurchaseColdStoreData['VaShortName'] = $dataRow['VaShortName'];
            $PurchaseColdStoreData['WtAftrVA'] = $dataRow['WtAftrVA'];
            $PurchaseColdStoreData['Yields'] = $Yields;
            $PurchaseColdStoreData['SentWeight'] = $dataRow['SentWeight'];

            $PurchaseColdStoreData['SoakInQty'] = $dataRow['SoakInQty'];
            $PurchaseColdStoreData['SoakOutQty'] = $dataRow['SoakOutQty'];
            $PurchaseColdStoreData['SoakInCount'] = $dataRow['SoakInCount'];
            $PurchaseColdStoreData['SoakOutCount'] = $dataRow['SoakOutCount'];
            $PurchaseColdStoreData['SoakingYield'] = $SoakingYield;

            $PurchaseColdStoreData['Variety'] = $dataRow['Variety'];
            $PurchaseColdStoreData['PackingStyle'] = $dataRow['PackingStyle'];
            $PurchaseColdStoreData['SoakOutQty'] = $dataRow['SoakOutQty'];
            $PurchaseColdStoreData['PackingTypeID'] = $dataRow['PackingTypeID'];
            $PurchaseColdStoreData['ProductionWeight'] = $dataRow['ProductionWeight'];
            $PurchaseColdStoreData['FreezingIn'] = $FreezingInQtyStr;
            $PurchaseColdStoreData['FreezingYield'] = $FreezingYield;
            $PurchaseColdStoreData['NoofCases'] = $dataRow['FreezingMcs'];
            $PurchaseColdStoreData['Variety'] = $dataRow['Variety'];

            
          //  $OutWardRes = $this->getShpmentData($dataRow);
            $StoreBalanceQty = $StoreInwardQtyArr = $OutMcsArr = $RackNameArr = $ShipQtyArr = $OutDateArr = $ShipDateArr = [];
            for($i = 0;$i < count($pack); $i++){
                $reqCon['TrackID'] = $dataRow['TrackID'];
                $reqCon['PackingStyleID'] = $pack[$i];
                $reqCon['TubNo'] = $tub[$i];
                $reqCon['VarietyID'] = $var[$i];
                $reqCon['LineNumber'] = $lineNo[$i];
                //$StoreInwardQty = $this->getStoreInwardQty($reqCon);
                $StoreInwardRes = $this->getStoreInwardQty($reqCon); //
                $StoreOutwardRes = $this->getStoreOutwardQty($reqCon); 
                $StoreBalanceQty[] = $StoreInwardRes['StoreInwardQty'] - $StoreOutwardRes['StoreOutwardQty'];
                $StoreInwardQtyArr[] = $StoreInwardRes['InMcs'];
                $RackNameArr[] = $StoreInwardRes['RackName'];
                $OutMcsArr[] = $StoreOutwardRes['OutMcs'];

                $shipmentRes = $this->getShpmentData($reqCon);
                $OutDateArr[] = $shipmentRes['OutDate'];
                $ShipDateArr[] = $shipmentRes['ShipmentDate'];
                $ShipQtyArr[] = $shipmentRes['ShipmentOutKgs'];
            }
            //return $OutDateArr;

            //$PurchaseColdStoreData['StockOutKgs'] = $OutWardRes['StockOutKgs'];
            $PurchaseColdStoreData['StockOutKgs'] = implode(',',$OutMcsArr);
           // $PurchaseColdStoreData['RackName'] = $OutWardRes['RackName'];
           $PurchaseColdStoreData['RackName'] = implode(',',$RackNameArr);
           // $PurchaseColdStoreData['ShipmentOutKgs'] = $OutWardRes['ShipmentOutKgs'];
           $PurchaseColdStoreData['ShipmentOutKgs'] = implode(',',$ShipQtyArr);
            // $PurchaseColdStoreData['StockInKgs'] = $OutWardRes['StockInKgs'];
            // $PurchaseColdStoreData['BalanceKgs'] = $OutWardRes['BalanceKgs'];
            $PurchaseColdStoreData['StockInKgs'] = implode(',',$StoreInwardQtyArr);
            $PurchaseColdStoreData['BalanceKgs'] = implode(',',$StoreBalanceQty);
           // $PurchaseColdStoreData['OutDate'] = $OutWardRes['OutDate'];
           // $PurchaseColdStoreData['ShipmentDate'] = $OutWardRes['ShipmentDate'];
           $PurchaseColdStoreData['OutDate'] = implode('|',$OutDateArr);
           $PurchaseColdStoreData['ShipmentDate'] = implode('|',$ShipDateArr);


            // $PurchaseColdStoreData['AbStatus'] = $dataRow['AbStatus'];
             $ArrivalID = $dataRow['RmArrivalID'];
            $ab = $this->getAbStatus($ArrivalID);
            $PurchaseColdStoreData['AbStatus'] = $ab['Status'];

            $PurchaseColdStoreData['FloorBalance'] = $FloorBalance;

            $HlQuantity = array_sum($HLWeightSumArr);
            $FinishedQuantity = array_sum($honFinishedSumArr);//            
            $PurchaseColdStoreData['HoToHlYield'] = number_format((($HlQuantity / $dataRow['HonWeight']) * 100), 2);
            $PurchaseColdStoreData['HoToFinishedYield'] = number_format((($FinishedQuantity / $dataRow['HonWeight']) * 100), 2);

          



            $PurchaseColdStoreList[] = $PurchaseColdStoreData;
        }
        $response = [
            'Error' => null,
            'Msg' => 'Success',
            'TotalCount' => $totalRows,
            'PageCount' => 1,
            'limit' => 50,
            'totalRows' => $totalRows,
            'PurchaseColdStoreReportData' => $PurchaseColdStoreList
        ];



        return $response;
    }
    function getShpmentData($dataRow){
        $TrackID = $dataRow['TrackID'];
            $TubNo = $dataRow['TubNo'];
            $LineNumber = $dataRow['LineNumber'];
            $VarietyID = $dataRow['VarietyID'];
            $PackingStyleID = $dataRow['PackingStyleID'];

            $sqlStock = "SELECT GROUP_CONCAT(CSD.ActualMcs) AS StockInKgs,
                                GROUP_CONCAT(CSD.AvalKgs) AS BalanceKgs,
                                GROUP_CONCAT(CSO.TotalStockOutKgs) AS StockOutKgs,
                                GROUP_CONCAT(CSSO.TotalShipmentOutKgs) AS ShipmentOutKgs,
                                GROUP_CONCAT(CSO.OutDate) AS OutDate,
                                GROUP_CONCAT(CSSO.ShipmentDate) AS ShipmentDate,
                                GROUP_CONCAT(DISTINCT CSRD.RackName SEPARATOR '|') AS RackName
                                FROM CsDailyProductionData AS CDPD
                                LEFT JOIN CsStockData AS CSD ON CDPD.ProductionID=CSD.ProductionID
                                LEFT JOIN (
                                    SELECT StockID, SUM((OutMcs)) AS TotalStockOutKgs, GROUP_CONCAT(DATE_FORMAT(OutDate, '%d-%m-%y') SEPARATOR '|') AS OutDate
                                    FROM CsStockOutDetails
                                    WHERE OutType <> 'Shipment Out'
                                    GROUP BY StockID
                                ) CSO ON CSO.StockID=CSD.StockID
                                LEFT JOIN (
                                    SELECT StockID, SUM((ActualOutKgs)) AS TotalShipmentOutKgs, GROUP_CONCAT(OutDate SEPARATOR '|') AS ShipmentDate
                                    FROM CsStockOutDetails
                                    WHERE OutType = 'Shipment Out'
                                    GROUP BY StockID
                                ) CSSO ON CSSO.StockID=CSD.StockID
                                
                                LEFT JOIN CsStoresRackData CSRD ON CSRD.RowID = CSD.StoreRackID WHERE CDPD.TrackID IN ($TrackID) AND CDPD.TubNo IN ($TubNo) AND CDPD.LineNumber IN ($LineNumber) AND CDPD.VarietyID IN ($VarietyID)";

        // echo $sqlStock;exit;

        $stmtStock = $this->conn->prepare($sqlStock);
        $stmtStock->execute();
       return $rowStock = $stmtStock->fetch(PDO::FETCH_ASSOC);
    }
    public function getStoreInwardQty($dataRow)
    {
        $TrackID = $dataRow['TrackID'];
        $TubNo = $dataRow['TubNo'];
        $LineNumber = $dataRow['LineNumber'];
        $VarietyID = $dataRow['VarietyID'];
        $PackingStyleID = $dataRow['PackingStyleID'];

        
        $sqlFields = "SUM(CPID.ActualKgs) AS StoreInwardQty,GROUP_CONCAT(CPID.ActualMcs + CEIL(CPID.ActualLooseBags/TotalSlabs) SEPARATOR '|') AS InMcs,GROUP_CONCAT(CSRD.RackName SEPARATOR '|') AS RackName";
        $sqlStoreInwardQty = "SELECT $sqlFields FROM CsStockData AS CPID
        LEFT JOIN CsDailyProductionData AS CDPD ON CPID.ProductionID = CDPD.ProductionID 
        LEFT JOIN CsStoresRackData CSRD ON CSRD.RowID = CPID.StoreRackID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CPID.PackingStyleID 
        WHERE TrackID='$TrackID' AND CDPD.PackingStyleID IN($PackingStyleID)
        AND CDPD.TubNo IN ($TubNo)
        AND CDPD.LineNumber IN ($LineNumber) 
        AND CDPD.VarietyID IN ($VarietyID)";
        $stmtStoreInwardQty = $this->conn->prepare($sqlStoreInwardQty);
        $stmtStoreInwardQty->execute();
        $dataRowStoreInwardQty = $stmtStoreInwardQty->fetch(PDO::FETCH_ASSOC);
        return $dataRowStoreInwardQty;
    }

    public function getStoreOutwardQty($dataRow)
    {
        $TrackID = $dataRow['TrackID'];
        $TubNo = $dataRow['TubNo'];
        $LineNumber = $dataRow['LineNumber'];
        $VarietyID = $dataRow['VarietyID'];
        $PackingStyleID = $dataRow['PackingStyleID'];

        $sqlFields = "SUM((OutMcs*TotalSlabs*SlabWeight*MFactor)+(OutLooseBags*SlabWeight*MFactor)) AS StoreOutwardQty,
        GROUP_CONCAT(CSOD.OutMcs + CEIL(CSOD.OutLooseBags/TotalSlabs) SEPARATOR '|') AS OutMcs";
        $sqlStoreOutwardQty = "SELECT $sqlFields FROM CsStockOutDetails AS CSOD
        LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
        LEFT JOIN CsDailyProductionData AS CDPD ON CPID.ProductionID = CDPD.ProductionID 
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CPID.PackingStyleID 
        WHERE TrackID='$TrackID'  AND CDPD.PackingStyleID IN($PackingStyleID)
        AND CDPD.TubNo IN ($TubNo)
        AND CDPD.LineNumber IN ($LineNumber) 
        AND CDPD.VarietyID IN ($VarietyID)";
        $stmtStoreOutwardQty = $this->conn->prepare($sqlStoreOutwardQty);
        $stmtStoreOutwardQty->execute();
        $dataRowStoreOutwardQty = $stmtStoreOutwardQty->fetch(PDO::FETCH_ASSOC);
        return $dataRowStoreOutwardQty;
    }

    public function getAbStatus($ArrivalID)
{ 
    $sql = "SELECT 
                RLD.RmLotID,
                QCAB.SampleDetails,
                ABO.Name
            FROM RmArrivalDetatils RAD
            JOIN RmLotDetatils RLD ON RLD.RmReferenceID = RAD.RmArrivalID
            JOIN QcRmAbReport QCAB ON  FIND_IN_SET(RLD.LotNumber,QCAB.LotNO)
            LEFT JOIN QcABOptions ABO ON ABO.OptID = QCAB.AbStatus
            WHERE RAD.RmArrivalID = $ArrivalID
            ORDER BY QCAB.AbRepID";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        return [
            'LotID'  => null,
            'Status' => 'TEST PENDING'
        ];
    }

    $statuStr = 'TEST PENDING';
    $statusArr = [];
    foreach ($rows as $row) {
        if (!empty($row['SampleDetails']) && !empty($row['Name'])) {

            if (stripos($row['SampleDetails'], 'Raw Material Sample') !== false) {
                $sample = 'R';
            } elseif (stripos($row['SampleDetails'], 'Grading Sample') !== false) {
                $sample = 'G';
            } elseif (stripos($row['SampleDetails'], 'Farm Sample') !== false) {
                $sample = 'F';
            }  elseif (stripos($row['SampleDetails'], 'Finished Product') !== false) {
                $sample = 'FP';
            }  else {
                continue;
            }

            $statusArr[] = $sample . '-' . strtoupper($row['Name']);
        }
    }
    if(empty($statusArr)){
        $statusArr[] = $statuStr;
    }

    return [
        'LotID'  => $rows[0]['RmLotID'],
        'Status' => implode(', ',array_unique($statusArr))
    ];
}


}