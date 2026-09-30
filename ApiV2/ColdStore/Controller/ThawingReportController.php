<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class ThawingReportController

{
    private $conn;
    //	private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }

    function getThawingDetails($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $Variety = $RequestBody['Variety'];
        $Grade = $RequestBody['Grade'];
        $Species = $RequestBody['Species'];
        $Treatment = $RequestBody['Treatment'];
        $ProductBatchCode = $RequestBody['ProductBatchCode']; 

        $Query = '';
        if ($ProductionPlant != "") {
            $Query .= " AND CSD.PlantID = '$ProductionPlant'";
        }
        if ($Variety != "") {
            $Query .= " AND SPVL.ShortName = '$Variety'";
        }
        if ($Grade != "") {
            $Query .= " AND CSD.Grade = '$Grade'";
        }
        if ($Species != "") {
            $Query .= " AND SPVL.Species = '$Species'";
        }
        if ($Treatment != "") {
            $Query .= " AND CSD.SockingType = '$Treatment'";
        }

        $DataFilters = " BETWEEN '$FromDate' AND '$ToDate'";

        $sqlFields = "GROUP_CONCAT(DISTINCT CSOD.CsStockOutID) AS CsStockOutIDs,GROUP_CONCAT(CSOD.StockOutID) AS StockOutIDs, SUM(OutMcs) AS OutMcs, SUM(OutLooseBags) AS OutLooseBags, SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) as KGs,
        SUM(OutLooseBags*SlabWeight*MFactor) as LKGs, ProductDescription, CSD.Grade, CSD.FreezingType, CSD.SockingType, CSD.PackingTypeID,sum(ActualOutKgs) as ActualOutKgs, 
        CSOD.OutDate, APS.PackingStyle, PoNo, SPVL.DeviSUPC, SPVL.ShortName,OutType,DeviSUPC, CSD.TransactionDate,CSD.ProductionDate,CSD.VarietyID,DATE_FORMAT(RRD.ArrivalDate, '%d-%m-%Y') AS ArrivalDate,  RRD.TrackCode,
        CASE 
        WHEN RLD.LotNumber IS NOT NULL AND RLD.LotNumber != '' 
            THEN RLD.LotNumber
        WHEN CSD.BatchCode IS NOT NULL AND CSD.BatchCode != '' 
            THEN CONCAT('P-', CSD.BatchCode)
        WHEN CDPD.MixedBatch IS NOT NULL AND CDPD.MixedBatch != '' 
            THEN CONCAT('M-', CDPD.MixedBatch)
        ELSE ''
        END AS LotNumber
        ,CDPD.ProdBatchCode";
        $sqlQuery = "SELECT $sqlFields FROM CsStockOutDetails AS CSOD 
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
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CSD.PoID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID=CSD.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD.PackingStyleID
        WHERE CSOD.OutDate $DataFilters $Query AND OutType IN ('Melting OUT', 'Melting IN','Melting') GROUP BY CSOD.CsStockOutID";
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayThawingData = [];
        $TotalMcs = 0;
        $TotalLooseBags = 0;
        $TotalOutKGs = 0;
        $TotalBalanceKGs = 0;
        $ProductionMcs = 0;
        $ProductionLoose = 0;
        $TotalProductionKGs = 0;
        $ThawingTotals = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $StockOutID = $dataRow['StockOutIDs'];
            $CsStockOutIDs = $dataRow['CsStockOutIDs'];
            $VarietyID = $dataRow['VarietyID'];
            $BreakUpData = $this->getThawingBreakUpData($CsStockOutIDs);
            $ArrayShipmentList['SNO'] = ++$i;
            $ArrayShipmentList['StockOutID'] = $dataRow['StockOutIDs'];
            $ArrayShipmentList['OutType'] = $dataRow['OutType'];
            $ArrayShipmentList['DeviSUPC'] = $dataRow['DeviSUPC'];
            $ArrayShipmentList['PoNo'] = $dataRow['PoNo'] ? $dataRow['PoNo'] : 'NA';
            $ArrayShipmentList['ShortName'] = $dataRow['ShortName'];
            $ArrayShipmentList['ProductDescription'] = $dataRow['ProductDescription'];
            $ArrayShipmentList['Grade'] = $dataRow['Grade'];
            $ArrayShipmentList['OutDate'] = $dataRow['OutDate'] ? date('d-m-Y', strtotime($dataRow['OutDate'])) : '-';
            $ArrayShipmentList['TransactionDate'] = $dataRow['TransactionDate'] ? date('d-m-Y', strtotime($dataRow['TransactionDate'])) : 'NA';
            $ArrayShipmentList['ProductionDate'] = $dataRow['ProductionDate'] ? date('d-m-Y', strtotime($dataRow['ProductionDate'])) : 'NA';
            $ArrayShipmentList['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayShipmentList['FreezingType'] = $dataRow['FreezingType'];
            $ArrayShipmentList['SockingType'] = $dataRow['SockingType'];
            $ArrayShipmentList['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayShipmentList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayShipmentList['OutMcs'] = $dataRow['OutMcs'];
            $ArrayShipmentList['OutLooseBags'] = $dataRow['OutLooseBags'];
            // $TotalKGs = $dataRow['KGs'] + $dataRow['LKGs'];
            $TotalKGs = $dataRow['ActualOutKgs'] ;

            $ArrayShipmentList['ArrivalDate'] = $dataRow['ArrivalDate'];
            $ArrayShipmentList['TrackCode'] = $dataRow['TrackCode'];
            $ArrayShipmentList['LotNumber'] = $dataRow['LotNumber'];
           $ArrayShipmentList['ProdBatchCode'] = $dataRow['ProdBatchCode']; 

            $ArrayShipmentList['TotalKGs'] =$TotalKGs;
            $ProductionKGs = $BreakUpData['TotalProductionKGs'];
            $BalanceKGs = $TotalKGs - $ProductionKGs;
            $ArrayShipmentList['BalanceKGs'] =$BalanceKGs;
            $ArrayShipmentList['BreakUpData'] = $BreakUpData['ArrayBreakUpData'];

            $TotalMcs += $dataRow['OutMcs'];
            $TotalLooseBags += $dataRow['OutLooseBags'];
            $TotalOutKGs += number_format($TotalKGs,2);
            $TotalBalanceKGs += $BalanceKGs;
            $ProductionMcs += $BreakUpData['TotalProductionMcs'];
            $ProductionLoose += $BreakUpData['TotalProductionLooseBags'];
            $TotalProductionKGs += $ProductionKGs;
            $ArrayThawingData[] =  $ArrayShipmentList;

            $ThawingTotals = [
                "TotalMcs" => $TotalMcs,
                "TotalLooseBags" => $TotalLooseBags,
                "TotalOutKGs" => $TotalOutKGs,
                "TotalBalanceKGs" => number_format($TotalBalanceKGs,2),
                "ProductionMcs" => $ProductionMcs,
                "ProductionLoose" => $ProductionLoose,
                "ProductionKGS" => number_format($TotalProductionKGs, 2),
            ];
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['ArrayThawingData'] = $ArrayThawingData;
        $data['ThawingTotals'] = $ThawingTotals;

        // var_dump($data);
        // exit;
        return $data;
    }

    public function getThawingBreakUpData($StockOutID)
    {

        $sqlTrackIds = "SELECT GROUP_CONCAT(DISTINCT RRD.ReceivingID) AS TrackIds FROM RmReceivingBreakup AS RRB left join RmReceivingDetails AS RRD ON RRB.ReceivingID=RRD.ReceivingID WHERE RRB.CsStockOutID IN ($StockOutID)";
        // echo $sqlTrackIds;exit;
        $stmtTrackIds = $this->conn->prepare($sqlTrackIds);
        $stmtTrackIds->execute();
        $rowTrackIds = $stmtTrackIds->fetch(PDO::FETCH_ASSOC);
        $TrackIds = $rowTrackIds['TrackIds'];
        
        $sqlBreakUpFields = "CPID.ActualMcs, CPID.ActualLooseBags,
        (CPID.ActualMcs*TotalSlabs*SlabWeight*MFactor) as KGs, (CPID.ActualLooseBags*SlabWeight*MFactor) as LKGs, CPID.ActualKgs,
        ProductDescription, CPID.Grade, CPID.FreezingType, CPID.SockingType, CPID.PackingTypeID, 
        APS.PackingStyle, PoNo, SPVL.DeviSUPC, SPVL.ShortName, CPID.ProductionDate, CPID.TransactionDate, CPID.ProductionTypeID, CPID.Remarks";
        $sqlBreakUpSelect = "SELECT $sqlBreakUpFields FROM CsStockData AS CPID
        LEFT JOIN CsDailyProductionData AS CDPD ON CDPD.ProductionID = CPID.ProductionID
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPID.PoID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID=CPID.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
        WHERE CDPD.TrackID IN ($TrackIds)";
        $stmtBreakUpSelect = $this->conn->prepare($sqlBreakUpSelect);
        $stmtBreakUpSelect->execute();

        $ArrayBreakUpData = [];
        $TotalProductionMcs = 0;
        $TotalProductionLooseBags = 0;
        $TotalProductionKGs = 0;
        while ($rowBreakUpData = $stmtBreakUpSelect->fetch(PDO::FETCH_ASSOC)) {
            $ProductionDate = $rowBreakUpData['ProductionDate'];
            $TransactionDate = $rowBreakUpData['TransactionDate'];
            $ArrayBreakUpList['ProductionDate'] = $ProductionDate ? date('d-m-Y', strtotime($ProductionDate)) : '-';
            $ArrayBreakUpList['TransactionDate'] = $TransactionDate ? date('d-m-Y', strtotime($TransactionDate)) : '-';
            $ArrayBreakUpList['ActualMcs'] = $rowBreakUpData['ActualMcs'];
            $ArrayBreakUpList['ActualLooseBags'] = $rowBreakUpData['ActualLooseBags'];
            // $ProductionKGs = $rowBreakUpData['KGs'] + $rowBreakUpData['LKGs'];
            $ProductionKGs = $rowBreakUpData['ActualKgs'] ;//uma

            $ArrayBreakUpList['TotalKGs'] = number_format($ProductionKGs, 2);
            $ArrayBreakUpList['ProductDescription'] = $rowBreakUpData['ProductDescription'];
            $ArrayBreakUpList['Grade'] = $rowBreakUpData['Grade'];
            $ArrayBreakUpList['FreezingType'] = $rowBreakUpData['FreezingType'];
            $ArrayBreakUpList['SockingType'] = $rowBreakUpData['SockingType'];
            $ArrayBreakUpList['PackingTypeID'] = $rowBreakUpData['PackingTypeID'];
            $ArrayBreakUpList['ProductionTypeID'] = $rowBreakUpData['ProductionTypeID'];
            $ArrayBreakUpList['PackingStyle'] = $rowBreakUpData['PackingStyle'];
            $ArrayBreakUpList['PoNo'] = $rowBreakUpData['PoNo'] ? $rowBreakUpData['PoNo'] : 'NA';
            $ArrayBreakUpList['DeviSUPC'] = $rowBreakUpData['DeviSUPC'];
            $ArrayBreakUpList['ShortName'] = $rowBreakUpData['ShortName'];
            $ArrayBreakUpList['Remarks'] = $rowBreakUpData['Remarks'];

            $TotalProductionMcs += $rowBreakUpData['ActualMcs'];
            $TotalProductionLooseBags += $rowBreakUpData['ActualLooseBags'];
            $TotalProductionKGs += $ProductionKGs;
            $ArrayBreakUpData[] = $ArrayBreakUpList;
        }
        $Data['ArrayBreakUpData'] = $ArrayBreakUpData;
        $Data['TotalProductionMcs'] = $TotalProductionMcs;
        $Data['TotalProductionLooseBags'] = $TotalProductionLooseBags;
        $Data['TotalProductionKGs'] = $TotalProductionKGs;

        return $Data;
    }
}
