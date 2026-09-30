<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class IbtReportController
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
        $this->utilController = new UtilController($this->conn);
    }

    function getIbtReportDetails($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $Plant = $RequestBody['Plant'];
        $ProductBatchCode = $RequestBody['ProductBatchCode']; 

        $DataFilters = "CRBD.TransferDate BETWEEN '$FromDate' AND '$ToDate'";

        $sqlFields = " IBTID as IBTRequestID, CRBD.TransferDate,
        sum(NoOfLooseBags) as OutLooseBags,
        sum(NoOfMcs) as OutMcs,
        SPVO.ProductDescription as OutProductDescription, CSD.Grade,RPSF.ProdCode as FromPlant,
        APSO.PackingStyle as OutPackingStyle ,CRD.Remarks,RPSF.ProcessingStationID,IBTOrderNumber,DATE_FORMAT(RRD.ArrivalDate, '%d-%m-%Y') AS ArrivalDate,  RRD.TrackCode,
        CASE 
        WHEN RLD.LotNumber IS NOT NULL AND RLD.LotNumber != '' 
            THEN RLD.LotNumber
        WHEN CSD.BatchCode IS NOT NULL AND CSD.BatchCode != '' 
            THEN CONCAT('P-', CSD.BatchCode)
        WHEN CDPD.MixedBatch IS NOT NULL AND CDPD.MixedBatch != '' 
            THEN CONCAT('M-', CDPD.MixedBatch)
        ELSE ''
        END AS LotNumber
        ,CDPD.ProdBatchCode,APSO.TotalSlabs AS TotalSlabs";

        $sqlQuery = "SELECT   $sqlFields FROM CsIBTRequestBreakUpDetails  AS CRBD 
        LEFT JOIN CsIBTRequestDetails   AS CRD ON CRD.IBTID =  CRBD.IBTRequestID   
        LEFT JOIN CsStockData AS CSD ON CRBD.StockID = CSD.StockID 
        left join CsDailyProductionData AS CDPD ON CSD.ProductionID=CDPD.ProductionID 
        left join RmReceivingDetails AS RRD ON CDPD.TrackID=RRD.ReceivingID
        LEFT JOIN (SELECT TrackID, TubNo, LineNumber, MAX(ProductionID) AS MaxDataID FROM ProdFreezingData
        GROUP BY TrackID, TubNo, LineNumber ) PFDx ON CDPD.TrackID = PFDx.TrackID 
        AND CDPD.TubNo = PFDx.TubNo  AND CDPD.LineNumber = PFDx.LineNumber
        LEFT JOIN ProdFreezingData PFD ON PFD.ProductionID = PFDx.MaxDataID
        left join PpcHLGradingBreakup as PGB on PFD.GradingBreakupID=PGB.DataID 
        left join RmLotDetatils as RLD on PGB.LotID=RLD.RmLotID 
        LEFT JOIN ScrmPIVarietyList AS SPVO ON SPVO.VarietyID=CSD.VarietyID
        LEFT JOIN AdminPackingStyles AS APSO ON APSO.PCKID=CSD.PackingStyleID        
        LEFT JOIN RmProcessingStations AS RPSF ON CRD.FromPlantID=RPSF.ProcessingStationID
        WHERE  $DataFilters and CRD.FromPlantID='$Plant' GROUP by CRD.IBTID";
        
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayIBTReportData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $IBTRequestID = $dataRow['IBTRequestID'];

            $ArrayIBTList['SNO'] = ++$i;

            // $ArrayIBTList['OutCompany'] = '';
            $BreakUpData = $this->getIBTBreakUpData($IBTRequestID);
            $ArrayIBTList['IBTRequestID'] = $IBTRequestID;
            $ArrayIBTList['OrderNumber'] = $dataRow['IBTOrderNumber'];
            $ArrayIBTList['OutFromPlant'] = $dataRow['FromPlant'];
            $ArrayIBTList['OutTransactionDate'] = $this->utilController->getFormatedDateEmpty($dataRow['TransferDate']);
            $ArrayIBTList['OutVariety'] = $dataRow['OutProductDescription'];
            $ArrayIBTList['OutPackingStyle'] = $dataRow['OutPackingStyle'];
            $ArrayIBTList['OutGrade'] = $dataRow['Grade'];
            $ArrayIBTList['OutMcs'] = $dataRow['OutMcs'];
            $ArrayIBTList['OutLooseBags'] = $dataRow['OutLooseBags'];
            $ArrayIBTList['Remarks'] = $dataRow['Remarks'];
            $ArrayIBTList['BreakUpData'] = $BreakUpData['ArrayBreakUpData'];

            $balanceMcs = $dataRow['OutMcs'] - $BreakUpData['TotalMcs'];
            $balanceBags = $dataRow['OutLooseBags'] - $BreakUpData['TotalLoose'];
            $TotalSlabs = $dataRow['TotalSlabs'];

            if($balanceBags < 0 && $balanceMcs < 0){
                $balanceMcs = 0;
                $balanceBags = 0;
            } 
            else{
                if($balanceMcs < 0){
                    $mcsBags = abs($balanceMcs) * $TotalSlabs;
                    $balanceMcs = 0;
                    $balanceBags = $balanceBags - $mcsBags;
                }
                if($balanceBags < 0){
                    $bagsMcs = ceil(abs($balanceBags) / $TotalSlabs);
                    $balanceMcs = $balanceMcs - $bagsMcs;
                    $balanceBags = $TotalSlabs - (abs($balanceBags) % $TotalSlabs);
                }
            }

            $ArrayIBTList['BalanceMcs'] = $balanceMcs;
            $ArrayIBTList['BalanceLooseBags'] = $balanceBags;

            $ArrayIBTList['ArrivalDate'] = $dataRow['ArrivalDate'];
            $ArrayIBTList['TrackCode'] = $dataRow['TrackCode'];
            $ArrayIBTList['LotNumber'] = $dataRow['LotNumber'];
            $ArrayIBTList['ProdBatchCode'] = $dataRow['ProdBatchCode'];


            $ArrayIBTReportData[] = $ArrayIBTList;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['ArrayIBTReportData'] = $ArrayIBTReportData;

        return $data;
    }

    public function getIBTBreakUpData($IBTRequestID)
    {
        $sqlBreakUpFields = " CSDI.Grade as InGrade, SPVI.ProductDescription as InProductDescription,APSI.PackingStyle as InPackingStyle,
        RPS.ProdCode as ToPlant,CSDI.TransactionDate,CSDI.ProductionDate,SUM(CSDI.ActualMcs) as InMCs,SUM(CSDI.ActualLooseBags) as InLooseBags ";

        $sqlBreakUpSelect = "SELECT $sqlBreakUpFields FROM  CsIBTRequestDetails   AS CRD
        LEFT JOIN CsDailyProductionData  AS CDPI ON CDPI.IBTOutID=CRD.IBTID  
        LEFT JOIN CsStockData AS CSDI ON CDPI.ProductionID =  CSDI.ProductionID 
        LEFT JOIN RmProcessingStations AS RPS ON RPS.ProcessingStationID=CRD.ToPlantID
        LEFT JOIN ScrmPIVarietyList AS SPVI ON SPVI.VarietyID=CSDI.VarietyID
        LEFT JOIN AdminPackingStyles AS APSI ON APSI.PCKID=CSDI.PackingStyleID
        WHERE CDPI.IBTOutID=$IBTRequestID GROUP BY CDPI.VarietyID, CSDI.PackingStyleID, CSDI.Grade ";

        // print_r($sqlBreakUpSelect);exit;
        $stmtBreakUpSelect = $this->conn->prepare($sqlBreakUpSelect);
        $stmtBreakUpSelect->execute();

        $ArrayBreakUpData = [];

        $TotalMcs = 0;
        $TotalLoose = 0;
        while ($rowBreakUpData = $stmtBreakUpSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayBreakUpList['InToPlant'] = $rowBreakUpData['ToPlant'];
            $ArrayBreakUpList['InTransactionDate'] = $this->utilController->getFormatedDateEmpty($rowBreakUpData['TransactionDate']);
            $ArrayBreakUpList['InVariety'] = $rowBreakUpData['InProductDescription'];
            $ArrayBreakUpList['InPackingStyle'] = $rowBreakUpData['InPackingStyle'];
            $ArrayBreakUpList['InGrade'] = $rowBreakUpData['InGrade'];
            $ArrayBreakUpList['InMcs'] = $rowBreakUpData['InMCs'];
            $ArrayBreakUpList['InLooseBags'] = $rowBreakUpData['InLooseBags'];
            $ArrayBreakUpList['InProductionDate'] = $this->utilController->getFormatedDateEmpty($rowBreakUpData['ProductionDate']);

            $TotalMcs += $rowBreakUpData['InMCs'];
            $TotalLoose += $rowBreakUpData['InLooseBags'];

            $ArrayBreakUpData[] = $ArrayBreakUpList;
        }
        $Data['ArrayBreakUpData'] = $ArrayBreakUpData;
        $Data['TotalMcs'] = $TotalMcs;
        $Data['TotalLoose'] = $TotalLoose;

        return $Data;
    }
}
