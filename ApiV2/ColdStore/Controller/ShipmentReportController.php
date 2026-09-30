<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class ShipmentReportController
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

    function getShipmentReportDetails($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $ShowBrandInCSReport = $RequestBody['ShowBrandInCSReport'];
         $ProductBatchCode = $RequestBody['ProductBatchCode']; 

        $Query = '';
        if ($ProductionPlant != "") {
            $Query .= " AND RPS.ProcessingStationID = '$ProductionPlant'";
        }

        $DataFilters = " BETWEEN '$FromDate' AND '$ToDate'";

        $sqlFields = "CPID.PoID, SPD.PoNo,SCI.CInvDate,SPGD.Grade, CPID.PlantID,SCI.StockOutDate,
        CPID.SockingType,CPID.PackingStyleID,SPVL.ShortName,APS.PackingStyle,CSOD.Remarks,SPVL.ProductDescription,
        SPGD.Mcs, (SPGD.MCS*TotalSlabs*SlabWeight*MFactor) as OrderKgs, (SPGD.Mcs*TotalSlabs) AS TotalSlabs,
        CPID.ProductionTypeID, CPID.PackingTypeID,SCI.CustomsInvNo, ContainerNo,SCI.VehicelNumber,StationName,DATE_FORMAT(RRD.ArrivalDate, '%d-%m-%Y') AS ArrivalDate,  RRD.TrackCode,
        CASE  
        WHEN RLD.LotNumber IS NOT NULL AND RLD.LotNumber != '' 
            THEN RLD.LotNumber
        WHEN CPID.BatchCode IS NOT NULL AND CPID.BatchCode != '' 
            THEN CONCAT('P-', CPID.BatchCode)
        WHEN CDPD.MixedBatch IS NOT NULL AND CDPD.MixedBatch != '' 
            THEN CONCAT('M-', CDPD.MixedBatch)
        ELSE ''
        END AS LotNumber
        ,CDPD.ProdBatchCode";
        if($ShowBrandInCSReport == 1){
            $sqlFields .=',SBM.`BrandName`'; 
        }
        $sqlQuery = "SELECT $sqlFields FROM ScrmCustomInvoices AS SCI
         LEFT JOIN ScrmPoData AS SPD ON SPD.PoID = SCI.PiID
         LEFT JOIN ScrmPoGradeData  AS SPGD ON SPGD.InvId=SPD.PoID
         LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID = SPGD.VarietyID 
         LEFT JOIN CsStockData AS CPID ON CPID.PoGradeId = SPGD.ID
         left join CsDailyProductionData AS CDPD ON CPID.ProductionID=CDPD.ProductionID 
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
         LEFT JOIN CsStockOutDetails AS CSOD  ON CSOD.StockID = CPID.StockID 
         LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = SPGD.PackingStyleID 
         left JOIN AdminExporterDetails as AED on AED.ExpID=SPD.ProcessorID 
         left join RmProcessingStations as RPS on RPS.ProdCode=AED.InvShortCode ";
         if($ShowBrandInCSReport == 1){
            $sqlQuery .= " LEFT JOIN `ScrmBrandsMaster` SBM ON SBM.`BrandID`= SPVL.`BrandName` ";
         }
         $sqlQuery .= " WHERE SCI.StockOutDate $DataFilters $Query AND (CSOD.OutDate IS NOT NULL AND CSOD.OutDate <> '') AND CSOD.OutType='Shipment Out' GROUP BY SPGD.ID ORDER BY SCI.StockOutDate DESC,SPGD.ID asc";
       
       
         $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayShipmentData = [];

        $i = 0;
        $customsInvNoCounts = [];
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            // var_dump($dataRow);
            $CustomsInvNo = $dataRow['CustomsInvNo'];
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

            // $CInvDate = $this->utilController->getFormatedDatelines($dataRow['CInvDate']);

            $ArrayShipmentList['SNO'] = ++$i;
            $ArrayShipmentList['PoID'] = $dataRow['PoID'];
            $ArrayShipmentList['PoNo'] = $dataRow['PoNo'];
            // $ArrayShipmentList['StockOutDate'] = $dataRow['OutDate'];
            $ArrayShipmentList['StockOutDate'] = $dataRow['StockOutDate'] ? date('d-m-Y', strtotime($dataRow['StockOutDate'])) : 'NA';
            $ArrayShipmentList['CInvDate'] = $dataRow['CInvDate'] ? date('d-m-Y', strtotime($dataRow['CInvDate'])) : 'NA';
            ;
            $ArrayShipmentList['Plant'] = $dataRow['StationName'];
            $ArrayShipmentList['CustomsInvNo'] = $dataRow['CustomsInvNo'];
            $ArrayShipmentList['CustomsInvNoTwo'] = $FormattedCustomsInvNo;
            $ArrayShipmentList['ProductionTypeID'] = $dataRow['ProductionTypeID'];
            // $ArrayShipmentList['ShortName'] = $dataRow['ShortName'];
            $ArrayShipmentList['ShortName'] = $dataRow['ProductDescription'];
            if($ShowBrandInCSReport == 1){
                $ArrayShipmentList['BrandName'] = $dataRow['BrandName'];
            }
            $ArrayShipmentList['Grade'] = $dataRow['Grade'];
            $ArrayShipmentList['PlantID'] = $dataRow['PlantID'];
            $ArrayShipmentList['SockingType'] = $dataRow['SockingType'];
            $ArrayShipmentList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayShipmentList['OrderedMcs'] = $dataRow['Mcs'];
            $ArrayShipmentList['OrderKgs'] = round($dataRow['OrderKgs'], 3);
            $ArrayShipmentList['OrderSlabs'] = round($dataRow['TotalSlabs']);
            $ArrayShipmentList['ContainerNo'] = $dataRow['ContainerNo'];
            $ArrayShipmentList['VehicelNumber'] = $dataRow['VehicelNumber'];
            $ArrayShipmentList['Remarks'] = $dataRow['Remarks'];

            $ArrayShipmentList['ArrivalDate'] = $dataRow['ArrivalDate'];
            $ArrayShipmentList['TrackCode'] = $dataRow['TrackCode'];
            $ArrayShipmentList['LotNumber'] = $dataRow['LotNumber'];
             $ArrayShipmentList['ProdBatchCode'] = $dataRow['ProdBatchCode'];
            

            $ArrayShipmentData[] = $ArrayShipmentList;


        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['ArrayShipmentData'] = $ArrayShipmentData;
        // $data['ShipmentTotals'] = $ShimentTotals;

        return $data;
    }
}
