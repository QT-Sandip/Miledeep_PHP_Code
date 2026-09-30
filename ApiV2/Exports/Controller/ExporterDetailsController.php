<?php

include_once '../Global/Util.php';
class ExporterDetailsController
{

    private $conn;
    private $db_table = "AdminExporterDetails";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getExporterDetails()
    {
        //Exptype=1 ->  Type Exporter
        $SqlFields = "ExpName,ExpAddress,ExpTax,ExpGstNo,ExpCinno,InvShortCode";

        //Exptype=1 is AdminExporterDetails Table
        $sqlQuery = "SELECT $SqlFields  FROM " . $this->db_table . " where Exptype=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayExporterList['SNO'] = ++$i;
            $ArrayExporterList['Name'] = $dataRow['ExpName'];
            $ArrayExporterList['Address'] = $dataRow['ExpAddress'];
            $ArrayExporterList['Tax'] = $dataRow['ExpTax'];
            $ArrayExporterList['GSTIN'] = $dataRow['ExpGstNo'];
            $ArrayExporterList['CINNO'] = $dataRow['ExpCinno'];
            $ArrayExporterList['InvioceCode'] = $dataRow['InvShortCode'];
            $Data[] = $ArrayExporterList;

        }

        return $Data;
    }

}
?>