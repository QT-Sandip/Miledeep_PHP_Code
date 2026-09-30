<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
class BuyerListDetailsController
{

    private $conn;
    private $db_table = "ScrmBuyerDetails";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getBuyerListDetails()
    {
        $SqlFields = "BuyerName,BuyerAddress,BuyerCountry,BuyerEmailAddress";

        $sqlQuery = "SELECT $SqlFields FROM " . $this->db_table . " ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        $Controller = new UtilController($this->conn);

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayBuyerList['SNO'] = ++$i;
            $ArrayBuyerList['BuyerName'] = $dataRow['BuyerName'];
            $ArrayBuyerList['BuyerAddress'] = $dataRow['BuyerAddress'];

            $Country = $dataRow['BuyerCountry'];
            $CountryDetails = $Controller->getCountryDetails($Country);
            $ArrayBuyerList['Country'] = $CountryDetails['CountryName'];
            
            $ArrayBuyerList['BuyerEmailAddress'] = $dataRow['BuyerEmailAddress'];
            $Data[] = $ArrayBuyerList;



        }
        return $Data;
    }

}
?>