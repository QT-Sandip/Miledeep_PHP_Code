<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class CoastingRatesHistoryController
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
    public function GetCoastingRates($RequestBody)
    {
        $Species = $RequestBody['Species'];
        $Month = $RequestBody['Month'];
        $Year = $RequestBody['Year'];

        $SqlFields = "FromDate,SType,ID";

        $SqlCoastingQuery = "SELECT $SqlFields FROM SalesCostingRatesHistory WHERE Month(FromDate)='$Month' and Year(FromDate)='$Year' and SType='$Species' ORDER BY ID DESC";
        $stmt = $this->conn->prepare($SqlCoastingQuery);
        $stmt->execute();

        $AryCoastingRate = [];
        $i = 0;

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayCoastingRates['SNO'] = ++$i;
            $ArrayCoastingRates['ID'] = $dataRow['ID'];
            $ArrayCoastingRates['FromDate'] = 'Rates From - ' . $this->utilController->getFormatedDate($dataRow['FromDate']);
            $ArrayCoastingRates['SType'] = $dataRow['SType'];

            $AryCoastingRate[] = $ArrayCoastingRates;
        }

        if ($ArrayCoastingRates == 0) {
            $SqlMaxDate = "SELECT MAX(FromDate) AS FromDate  FROM SalesCostingRatesHistory  WHERE SType='$Species'";
            $stmtMaxDate = $this->conn->prepare($SqlMaxDate);
            $stmtMaxDate->execute();

            ($dataRow = $stmtMaxDate->fetch(PDO::FETCH_ASSOC));
                $LastUpdatedDate = $dataRow['FromDate'];
        }

        $data['PrintLink'] = "Reports/coasting-rates-print.php?id=";
        $data['Data'] = $AryCoastingRate;
        $data['LastUpdatedDate'] = $LastUpdatedDate;

        return $data;
    }
}
