<?php

include_once '../Global/Util.php';
class ExchangeRatesListDetailsController
{

    private $conn;
    private $db_table = "ScrmExchangeRates";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getExchangeRatesListDetails()
    {
        $SqlFields = "ExchangeRate,Fromdate";

        $sqlQuery = "SELECT $SqlFields FROM " . $this->db_table . " ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayExchangeRatesList['SNO'] = ++$i;
            $ArrayExchangeRatesList['ExchangeRate'] = $dataRow['ExchangeRate'];
            $date = date_create($dataRow['Fromdate']);
            $ArrayExchangeRatesList['ApplicableDate'] = date_format($date, "d-m-Y");
            $Data[] = $ArrayExchangeRatesList;


        }
        return $Data;
    }

}
?>