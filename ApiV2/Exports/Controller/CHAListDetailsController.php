<?php

include_once '../Global/Util.php';
class CHAListDetailsController
{

    private $conn;
    private $db_table = "ScrmLogisticsMaster";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getCHAListDetails()
    {
        $SqlFields = "ID,Name,Address";

        $sqlQuery = "SELECT $SqlFields FROM " . $this->db_table . " ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 1;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayCHAList['SNO'] = $i;
            $ArrayCHAList['ID'] = $dataRow['ID'];
            $ArrayCHAList['Name'] = $dataRow['Name'];
            $ArrayCHAList['Address'] = $dataRow['Address'];


            $Data[] = $ArrayCHAList;


            $i++;
        }
        return $Data;
    }

}
?>