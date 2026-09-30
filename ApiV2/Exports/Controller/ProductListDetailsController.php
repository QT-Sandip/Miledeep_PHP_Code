<?php

include_once '../Global/Util.php';
class ProductListDetailsController
{

    private $conn;
    private $db_table = "ScrmProductList";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getProductListDetails()
    {
        $SqlFields = "ShortName,FullName ";

        $sqlQuery = "SELECT  $SqlFields FROM " . $this->db_table . " ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayProductList['SNO'] = ++$i;
            $ArrayProductList['ShortName'] = $dataRow['ShortName'];
            $ArrayProductList['FullName'] = $dataRow['FullName'];



            $Data[] = $ArrayProductList;


            $i++;
        }
        return $Data;
    }

}
?>