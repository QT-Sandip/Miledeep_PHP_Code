<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class CoastingRatesPrintController
{
    private $conn;
    private $db_table = "ScrmPIData";
    private $utilController;



    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    
    }
    // GET ALL
    public function GetCoastingRatesPrint($RequestBody)
    {
        $ID = $RequestBody['ID'];
        
        $SqlFields = "SCR.Grade, SCR.USD, SCRH.FromDate,SType";

        $SqlCoastingQuery = "SELECT $SqlFields FROM SalesCostingRates AS SCR 
        LEFT JOIN SalesCostingRatesHistory AS SCRH ON SCR.TrackID = SCRH.ID 
        WHERE SCR.TrackID='$ID' ORDER BY SCR.CostID ASC";

        $stmt = $this->conn->prepare($SqlCoastingQuery);
        $stmt->execute();

        $AryCoastingRate = [];

        // $AryCoastingRate['FromDate'];
        // $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayCoastingRates['Grade'] = $dataRow['Grade'];
            $ArrayCoastingRates['USD'] = $dataRow['USD'];
            $ArrayCoastingRates['FromDate'] = $dataRow['FromDate'];
            $ArrayCoastingRates['SType'] = $dataRow['SType'];
            
            $AryCoastingRate[] = $ArrayCoastingRates;

        }
        $data['Data'] = $AryCoastingRate;
        
        return $data;
    }

}
?>