<?php

include_once '../Global/Util.php';
class PreparationListDetailsController
{

    private $conn;

    private $db_table = "ScrmPreparationMaster";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getPreparationListDetails()
    {
        $SqlFields = "PreparationName, PreparationDesc,ShortCode";

        $sqlQuery = "SELECT  $SqlFields FROM " . $this->db_table . " ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayProductList['SNO'] = ++$i;
            $ArrayProductList['PreparationName'] = $dataRow['PreparationName'];
            $ArrayProductList['Description'] = $dataRow['PreparationDesc'];
            $ArrayProductList['ShortCode'] = $dataRow['ShortCode'];
            $Data[] = $ArrayProductList;



        }
        return $Data;
    }

}
?>