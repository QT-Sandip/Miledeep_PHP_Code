<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class DurgaRoughController
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

    public function getIndexDetails()
    {

        $sqlSelect = "SELECT SUM(CPID.ActualMcs) AS AvalMcs, SUM(CPID.ActualLooseBags) AS LooseBags, 
        ProductDescription, CPID.Grade, CPID.FreezingType, CPID.SockingType, CPID.PackingTypeID, FromStore,IBTOutID,CPID.ProductionID,
        APS.PackingStyle, PoNo, PIVL.DeviSUPC, PIVL.ShortName,DeviSUPC FROM CsStockData AS CPID 
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPID.PoID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID=CPID.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
        LEFT JOIN CsDailyProductionData AS CSDP ON CSDP.ProductionID=CPID.ProductionID
        LEFT JOIN CsIBTRequestDetails AS CIRD ON CSDP.IBTOutID = CIRD.IBTID
        WHERE CPID.ProductionDate = '2024-08-31'  AND CPID.UnitID = '3' AND CPID.ProductionTypeID = 'IBT IN' GROUP BY CPID.VarietyID";

        // $sqlSelect = "SELECT  RPS.StationName,CDP.ProductionDate,CRBD.TransferDate,
        // SPV.ProductDescription, CSD.Grade,  APS.PackingStyle FROM CsStockData AS CSD 
        // LEFT JOIN ScrmPIVarietyList AS SPV ON SPV.VarietyID=CSD.VarietyID
        // LEFT JOIN CsIBTRequestBreakUpDetails  AS CRBD ON CRBD.IBTBreakupID = CSD.StockID
        // LEFT JOIN CsIBTRequestDetails   AS CRD ON CRD.IBTID =  CRBD.IBTBreakupID
        // LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD.PackingStyleID
        // LEFT JOIN RmProcessingStations AS RPS ON CSD.PlantID=RPS.ProcessingStationID
        // LEFT JOIN CsDailyProductionData  AS CDP ON CDP.PlantID=RPS.ProcessingStationID
        // WHERE  CRBD.TransferDate BETWEEN '2024-08-05' AND '2024-08-30'";
        // $sqlSelect = "SELECT * FROM CsDailyProductionData as CSDP  
        // LEFT JOIN  CsStockData as CSD on CSDP.ProductionID=CSD.ProductionID 
        // where IBTOutID!=0";
        // $sqlSelect = "SELECT * FROM CsStockData WHERE ProductionTypeID = 'IBT IN'";
        // $sqlSelect = "SELECT * FROM CsDailyProductionData WHERE IBTOutID != 0"; 
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
}
