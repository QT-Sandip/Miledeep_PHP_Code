<?php

include_once '../Global/Util.php';
class BrandListController
{

    private $conn;
    private $db_table = "ScrmBrandsMaster";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getBrandDetails()
    {
        $sqlQuery = "SELECT BrandID,BrandName,Remarks FROM ScrmBrandsMaster where Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $i = 1;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArrayBrandList['sno'] = $i;
            $ArrayBrandList['Name'] = $dataRow['BrandName'];
            $ArrayBrandList['ID'] = $dataRow['BrandID'];
            $ArrayBrandList['Remamrks'] = $dataRow['Remarks'];
            $Data[] = $dataRow;
            $i++;
        }

        return $Data;
    }

}
?>