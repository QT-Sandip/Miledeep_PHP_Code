<?php

include_once '../Global/Util.php';
class BrandsListDetailsController
{

    private $conn;
    private $db_table = "ScrmBrandsMaster";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getBrandsListDetails()
    {
        $SqlFields = "BrandName,Remarks";

        $sqlQuery = "SELECT $SqlFields  FROM " . $this->db_table . " ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayBrandsList['SNO'] = ++$i;
            $ArrayBrandsList['BrandName'] = $dataRow['BrandName'];
            $ArrayBrandsList['Remarks'] = $dataRow['Remarks'];
            $Data[] = $ArrayBrandsList;



        }
        return $Data;
    }

}
?>