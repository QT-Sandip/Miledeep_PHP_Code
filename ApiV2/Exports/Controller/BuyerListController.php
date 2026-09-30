<?php

include_once '../Global/Util.php';

class BuyerListController{
    private $conn;
    private $db_table = "ScrmBuyerDetails";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getBuyerDetails()
    {
        $sqlQuery = "SELECT BuyerID,BuyerName,BuyerCode,BuyerAddress,BuyerCountry,BuyerEmailAddress FROM " . $this->db_table . " where Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
}
?>


