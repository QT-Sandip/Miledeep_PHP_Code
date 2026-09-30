<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
class PoStatusDetailsController
{

    private $conn;
    private $db_table = "ScrmPIData";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getPoStatusDetails($POID)
    {

        $SqlFields = "PoNo,PoDate,Status,Remarks,ProductionPlantID";

        $SqlPoStatusQuery = "SELECT  $SqlFields FROM " . $this->db_table . " where POID='$POID'";

        $stmt = $this->conn->prepare($SqlPoStatusQuery);
        $stmt->execute();

        $i = 0;
        $Controller = new UtilController($this->conn);
        $Data = array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayPoStatusList['SNO'] = ++$i;
            $ArrayPoStatusList['Status'] = $dataRow['Status']; 
            $ArrayPoStatusList['PoNo'] = $dataRow['PoNo'];
            if($dataRow['PoDate']!=''){
            $date = date_create($dataRow['PoDate']);
            $PoDate=date_format($date, "d-m-Y");
            }else{
                $PoDate='';  
            }
            $ArrayPoStatusList['PoDate'] = $PoDate;
            $ArrayPoStatusList['Remarks'] = $dataRow['Remarks'];
            $ArrayPoStatusList['ProductionPlantID'] = $dataRow['ProductionPlantID'];
            $Data[] = $ArrayPoStatusList;

        }
        return $Data;
    }

}
?>