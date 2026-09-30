<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class ShipmentStatusReportController
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

    function getShipmentStatusDetails($RequestBody)
    {

       

        $PoID = $RequestBody['PoID'];
        $Year = $RequestBody['Year'];
        $OrderStatus = $RequestBody['OrderStatus'];
        $ProductionPlant = $RequestBody['ProductionPlant'];

        // $FromDate = $Year . '-04-01';
        // $ToDate = ($Year + 1) . '-03-31';
        
       // $ProductionPlant = $RequestBody['ProductionPlant'];

        $Query = '';
        if ($ProductionPlant != '') {
            $Query .= " AND CPID.PlantID = '$ProductionPlant'";
        }

       // $DataFilters = " BETWEEN '$FromDate' AND '$ToDate'";

        $sqlFields = "CPID.PoID, SPD.PoNo, CPID.VarietyID, CPID.Grade, CPID.FreezingType,CPID.ProductionDate, CPID.PlantID,
        CPID.SockingType, CPID.PackingTypeID, CPID.PackingStyleID,SPVL.ShortName,APS.PackingStyle,
        SPGD.Mcs AS Mcs, SPGD.Mcs*TotalSlabs*SlabWeight*MFactor as OrderKgs, SPGD.Mcs*TotalSlabs AS TotalSlabs, SPVL.ProductDescription, 
        CPID.ProductionTypeID, CPID.PackingTypeID, CustomsInvNo, ContainerNo, VehicelNumber,
        SUM(CPID.ActualMcs) AS ActualMcs, SUM(CPID.ActualLooseBags) AS ActualLooseBags,ROUND(SUM(CPID.ActualLooseBags/TotalSlabs),2) AS ActualLooseBagsMcs, SUM(TotalSlabs*CPID.ActualMcs) AS McsSlabs,CPID.PoGradeId,SCI.StockOutDate,
        SUM(CPID.ActualMcs*TotalSlabs*SlabWeight*MFactor) as Kgs, SUM(CPID.ActualLooseBags*SlabWeight*MFactor) as LKgs, 
        RackName, CSBD.BlockID,CPID.ProductionTypeID, BlockName, StoreName, CPID.PlantID, RPS.StationName,CPID.TransactionDate, CPID.ProductionDate,DATE_FORMAT(RRD.ArrivalDate, '%d-%m-%Y') AS ArrivalDate,  RRD.TrackCode,
        CASE  
        WHEN RLD.LotNumber IS NOT NULL AND RLD.LotNumber != '' 
            THEN RLD.LotNumber
        WHEN CPID.BatchCode IS NOT NULL AND CPID.BatchCode != '' 
            THEN CONCAT('P-', CPID.BatchCode)
        WHEN CDPD.MixedBatch IS NOT NULL AND CDPD.MixedBatch != '' 
            THEN CONCAT('M-', CDPD.MixedBatch)
        ELSE ''
        END AS LotNumber";

        // $sqlQuery = "SELECT $sqlFields FROM CsStockData AS CPID
        // LEFT JOIN ScrmPoData AS SPD ON CPID.PoID = SPD.PoID
        // LEFT JOIN ScrmCustomInvoices AS SCI ON SPD.PoID = SCI.PiID
        // LEFT JOIN ScrmPIVarietyList AS SPVL ON CPID.VarietyID = SPVL.VarietyID
        // LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CPID.PackingStyleID
        // LEFT JOIN ScrmPoGradeData AS SPGD ON SPGD.InvId=SPD.PoID
        // LEFT JOIN CsStoresRackData AS CSRD ON CSRD.RowID = CPID.StoreRackID
        // LEFT JOIN CsStoresBlockData AS CSBD ON CSBD.BlockID = CSRD.BlockID
        // LEFT JOIN CsStores AS CS ON CS.StoreID = CPID.UnitID 
        // LEFT JOIN RmProcessingStations AS RPS ON CPID.PlantID = RPS.ProcessingStationID
        // WHERE CPID.ProductionDate $DataFilters $Query AND CPID.PoID != ''
        // GROUP BY CPID.VarietyID, CPID.PoID, CPID.StockID ORDER BY CPID.PoID";
        // $sqlQuery = "SELECT $sqlFields FROM CsStockData AS CPID
        // left join CsDailyProductionData AS CDPD ON CPID.ProductionID=CDPD.ProductionID 
        // left join RmReceivingDetails AS RRD ON CDPD.TrackID=RRD.ReceivingID
        //    LEFT JOIN (
        //         SELECT TrackID, TubNo, LineNumber, MAX(ProductionID) AS MaxDataID
        //         FROM ProdFreezingData
        //         GROUP BY TrackID, TubNo, LineNumber
        //     ) PFDx
        //     ON CDPD.TrackID = PFDx.TrackID 
        //     AND CDPD.TubNo = PFDx.TubNo 
        //     AND CDPD.LineNumber = PFDx.LineNumber
        //     LEFT JOIN ProdFreezingData PFD
        //     ON PFD.ProductionID = PFDx.MaxDataID
        //     left join PpcHLGradingBreakup as PGB on PFD.GradingBreakupID=PGB.DataID 
        //     left join RmLotDetatils as RLD on PGB.LotID=RLD.RmLotID 
        // LEFT JOIN ScrmPoData AS SPD ON CPID.PoID = SPD.PoID
        //  LEFT JOIN ScrmPoGradeData AS SPGD ON SPGD.InvId=SPD.PoID
        // LEFT JOIN ScrmCustomInvoices AS SCI ON SPD.PoID = SCI.PiID
        // LEFT JOIN ScrmPIVarietyList AS SPVL ON CPID.VarietyID = SPVL.VarietyID
        // LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CPID.PackingStyleID
        // LEFT JOIN CsStockOutDetails AS CSOD ON CPID.StockID = CSOD.StockID  -- sriram 19-02-25 only shipment out
        // LEFT JOIN CsStoresRackData AS CSRD ON CSRD.RowID = CPID.StoreRackID
        // LEFT JOIN CsStoresBlockData AS CSBD ON CSBD.BlockID = CSRD.BlockID
        // LEFT JOIN CsStores AS CS ON CS.StoreID = CPID.UnitID 
        // LEFT JOIN RmProcessingStations AS RPS ON CPID.PlantID = RPS.ProcessingStationID
        // WHERE     SPD.PoID = '$PoID' AND CSOD.OutType='Shipment Out' $Query -- sriram 19-02-25 only shipment out
        // GROUP BY CPID.VarietyID, SPD.PoID,CPID.Grade 
        // ORDER BY SCI.StockOutDate DESC";

        $ShipmentCondition = ($OrderStatus == 1) ? " AND CSOD.OutType = 'Shipment Out'" : "";

        $sqlQuery = "SELECT $sqlFields
        FROM CsStockData AS CPID
        LEFT JOIN CsDailyProductionData AS CDPD ON CPID.ProductionID = CDPD.ProductionID
        LEFT JOIN RmReceivingDetails AS RRD ON CDPD.TrackID = RRD.ReceivingID
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
        LEFT JOIN PpcHLGradingBreakup AS PGB ON PFD.GradingBreakupID = PGB.DataID
        LEFT JOIN RmLotDetatils AS RLD ON PGB.LotID = RLD.RmLotID
        LEFT JOIN ScrmPoData AS SPD ON CPID.PoID = SPD.PoID
        LEFT JOIN ScrmPoGradeData AS SPGD ON SPGD.InvId = SPD.PoID
        LEFT JOIN ScrmCustomInvoices AS SCI ON SPD.PoID = SCI.PiID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON CPID.VarietyID = SPVL.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CPID.PackingStyleID
        LEFT JOIN CsStockOutDetails AS CSOD ON CPID.StockID = CSOD.StockID
        LEFT JOIN CsStoresRackData AS CSRD ON CSRD.RowID = CPID.StoreRackID
        LEFT JOIN CsStoresBlockData AS CSBD ON CSBD.BlockID = CSRD.BlockID
        LEFT JOIN CsStores AS CS ON CS.StoreID = CPID.UnitID
        LEFT JOIN RmProcessingStations AS RPS ON CPID.PlantID = RPS.ProcessingStationID
        WHERE SPD.PoID = '$PoID'
            $ShipmentCondition
            $Query
        GROUP BY CPID.VarietyID, SPD.PoID, CPID.Grade
        ORDER BY SCI.StockOutDate DESC";

        // echo $sqlQuery;exit;
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayShipmentData = [];
        $ShimentTotals = [];
        $i = 0;
        $customsInvNoCounts = [];
        $currentPoNo = null;

        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            // echo $dataRow;

            $PoNo = $dataRow['PoNo'];
            $VarietyID = $dataRow['VarietyID'];
            $CustomsInvNo = $dataRow['CustomsInvNo'];

            if ($currentPoNo !== $PoNo) {
                $customsInvNoCounts = []; 
                $currentPoNo = $PoNo;
            }

            if (!empty($CustomsInvNo)) {
                if (!isset($customsInvNoCounts[$CustomsInvNo])) {
                    $customsInvNoCounts[$CustomsInvNo] = 1;
                } else {
                    $customsInvNoCounts[$CustomsInvNo]++;
                }
                $FormattedCustomsInvNo = $CustomsInvNo . '-' . $customsInvNoCounts[$CustomsInvNo];
            } else {
                $FormattedCustomsInvNo = '';
            }

            $ArrayShipmentList['SNO'] = ++$i;
            $ArrayShipmentList['PoID'] = $dataRow['PoID'];
            $ArrayShipmentList['PoNo'] = $dataRow['PoNo'];
            $ArrayShipmentList['PoGradeId'] = $dataRow['PoGradeId'];
            $ArrayShipmentList['CustomsInvNo'] = $dataRow['CustomsInvNo'];
            $ArrayShipmentList['CustomsInvNoTwo'] = $FormattedCustomsInvNo;
            $ArrayShipmentList['VarietyID'] = $dataRow['VarietyID'];
            $ArrayShipmentList['ShortName'] = $dataRow['ShortName'];
            $ArrayShipmentList['ProductDescription'] = $dataRow['ProductDescription'];
            $ArrayShipmentList['Grade'] = $dataRow['Grade'];
            $ArrayShipmentList['TransactionDate'] = $dataRow['TransactionDate'] ? date('d-m-Y', strtotime($dataRow['TransactionDate'])) : 'NA';
            $ArrayShipmentList['StockOutDate'] = $dataRow['StockOutDate'] ? date('d-m-Y', strtotime($dataRow['StockOutDate'])) : '-';
            $ArrayShipmentList['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayShipmentList['PlantID'] = $dataRow['PlantID'];
            $ArrayShipmentList['FreezingType'] = $dataRow['FreezingType'];
            $ArrayShipmentList['SockingType'] = $dataRow['SockingType'];
            $ArrayShipmentList['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayShipmentList['PackingStyleID'] = $dataRow['PackingStyleID'];
            $ArrayShipmentList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayShipmentList['OrderedMcs'] = $dataRow['Mcs'] ;
            $ArrayShipmentList['OrderKgs'] = round($dataRow['OrderKgs'], 3);
            $ArrayShipmentList['OrderSlabs'] = $dataRow['TotalSlabs'];
            $ArrayShipmentList['ContainerNo'] = $dataRow['ContainerNo'];
            $ArrayShipmentList['VehicelNumber'] = $dataRow['VehicelNumber'];
            $ArrayShipmentList['ProductionMcs'] = $dataRow['ActualMcs'] + $dataRow['ActualLooseBagsMcs'];
            $ArrayShipmentList['ProductionLooseBags'] = $dataRow['ActualLooseBags'];
            $ProductionSlabs = $dataRow['McsSlabs'] + $dataRow['ActualLooseBags'];
            $ArrayShipmentList['ProductionSlabs'] = $ProductionSlabs;
            $ProductionKGs = $dataRow['Kgs'] + $dataRow['LKgs'];
            $ArrayShipmentList['ProductionKGs'] = round($ProductionKGs, 3);
            $ArrayShipmentList['StationName'] = $dataRow['StationName'];
            $ArrayShipmentList['ProductionDate'] = $dataRow['ProductionDate'] ? date('d-m-Y', strtotime($dataRow['ProductionDate'])) : 'NA';;
            $ArrayShipmentList['StoreName'] = $dataRow['StoreName'];
            $ArrayShipmentList['ProductionTypeID'] = $dataRow['ProductionTypeID'];
            $ArrayShipmentList['BlockID'] = $dataRow['BlockID'];
            $ArrayShipmentList['Location'] = $dataRow['BlockName'] . '-' . $dataRow['RackName'];

            $ArrayShipmentList['ArrivalDate'] = $dataRow['ArrivalDate'];
            $ArrayShipmentList['TrackCode'] = $dataRow['TrackCode'];
            $ArrayShipmentList['LotNumber'] = $dataRow['LotNumber'];

            $ArrayShipmentData[] =  $ArrayShipmentList;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['ArrayShipmentData'] = $ArrayShipmentData;

        return $data;
    }
}
