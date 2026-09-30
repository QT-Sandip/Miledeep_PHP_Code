<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
class PackingListDetailsController
{

    private $conn;
    private $db_table = "AdminPackingStyles";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getPackingListDetails()
    {
        $SqlFields = "PCKID ,PackingStyle,GrossWeight,Uom";

        $sqlQuery = "SELECT    $SqlFields FROM " . $this->db_table . " ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        $Controller = new UtilController($this->conn);

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayPackingList['SNO'] = ++$i;
            $ArrayPackingList['ID'] = $dataRow['PCKID'];
            $ArrayPackingList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayPackingList['GrossWeight'] = $dataRow['GrossWeight'];
            $ArrayPackingList['NetWeight'] = 'Doubt';
            $ArrayPackingList['UOM'] = $dataRow['Uom'];
            $Data[] = $ArrayPackingList;



        }
        return $Data;
    }

}
?>