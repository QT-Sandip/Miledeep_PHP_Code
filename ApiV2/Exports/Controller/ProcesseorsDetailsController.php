<?php

include_once '../Global/Util.php';
class ProcesseorsDetailsController
{

    private $conn;
    private $db_table = "AdminExporterDetails";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getProcesseorsDetails()
    {
        //Exptype=2 ->  Type Processors
        $SqlFields = "EiaExpName,EiaExpAddress,ExpUsfdaNo,ExpApprovalNo,ExpIeCode,InvShortCode";

        //Exptype=2 is AdminExporterDetails Table
        $sqlQuery = "SELECT  $SqlFields FROM " . $this->db_table . " where Exptype=2";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 1;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayProcesseorsList['SNO'] = $i;
            $ArrayProcesseorsList['Name'] = $dataRow['EiaExpName'];
            $ArrayProcesseorsList['Address'] = $dataRow['EiaExpAddress'];
            $ArrayProcesseorsList['USFDAREGNO'] = $dataRow['ExpUsfdaNo'];
            $ArrayProcesseorsList['ETANO'] = $dataRow['ExpApprovalNo'];
            $ArrayProcesseorsList['IECODE'] = $dataRow['ExpIeCode'];
            $ArrayProcesseorsList['InvioceCode'] = $dataRow['InvShortCode'];
            $Data[] = $ArrayProcesseorsList;


            $i++;

        }
        return $Data;
    }

}
?>