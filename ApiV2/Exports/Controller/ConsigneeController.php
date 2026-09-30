<?php

include_once '../Global/Util.php';

class ConsigneeListController{
    private $conn;
    private $db_table = "consigneelist";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getConsigneeDetails()
    {
        $sqlQuery = "SELECT ID,ConsigneeName,ConsigneeAddress,ConsigneeCountry,ConsigneeEmailAddress FROM " . $this->db_table . " where 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
}
?>


