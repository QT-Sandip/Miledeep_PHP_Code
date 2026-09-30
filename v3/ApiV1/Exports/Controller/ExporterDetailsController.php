<?php

include_once '../Global/Util.php';
class ExporterDetailsController
{

    private $conn;
    private $db_table = "ScrmPoData";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getExporterDetails()
    {
        $sqlQuery = "SELECT * FROM " . $this->db_table . "";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;

        }
        return $Data;
    }
    
}
?>