<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class AddPackingDataController
{
    private $conn;
    private $db_table = "ScrmPIData";
    private $utilController;
    private $auditLogger;



    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Exports');

    }
    // GET ALL




    public function addPackingData($RequestBody)
    {
        $Error = null;
        $PIID = $RequestBody['OrderID'];
        //$POGradeId = $RequestBody['POGradeId'];
       // $LastUpdatedDate = $RequestBody['LastUpdatedDate'];
        $AvalMcs = $RequestBody['MasterCases'];       
        $ApprovalDate = $RequestBody['ApprovalDate'];
        $OrderedDate = $RequestBody['OrderedDate'];               
        $OrderRemarks = $RequestBody['OrderRemarks'];
        $InnerBags = $RequestBody['InnerBags'];
        $Priority = $RequestBody['Priority'];
        $Msg = "Data Saved Successfully";


        $SqlCount="SELECT * FROM `ScrmPackingData` WHERE PIID='$PIID'";
        $stmtCount = $this->conn->prepare($SqlCount);
        $stmtCount->execute();
        $rowCount = $stmtCount->rowCount(); 
        $existingPacking = $this->auditLogger->fetchRowsByConditions('ScrmPackingData', ['PIID' => $PIID]);
        $existingPackingRow = count($existingPacking) > 0 ? $existingPacking[0] : null;

if($rowCount==0){
       $sql = "INSERT INTO `ScrmPackingData`(`PIID`,`AvalMcs`,  `ApprovalDate`, `OrderedDate`, `OrderRemarks`, `InnerBags`, `Priority`)
       VALUES ('$PIID','$AvalMcs','$ApprovalDate','$OrderedDate','$OrderRemarks','$InnerBags','$Priority')";
      
       
}
else{
    $sql = "  UPDATE `ScrmPackingData` SET 
    `AvalMcs`='$AvalMcs', `ApprovalDate`='$ApprovalDate',`OrderedDate`='$OrderedDate',
     `OrderRemarks`='$OrderRemarks',`InnerBags`='$InnerBags',`Priority`='$Priority' WHERE `PIID`='$PIID'";

}
$stmt = $this->conn->prepare($sql);
$stmt->execute();
      
        if ($stmt) { 
             $savedPacking = $this->auditLogger->fetchRowsByConditions('ScrmPackingData', ['PIID' => $PIID]);
             $savedPackingRow = count($savedPacking) > 0 ? $savedPacking[0] : null;
             $recordId = $savedPackingRow && isset($savedPackingRow['ID']) ? $savedPackingRow['ID'] : $PIID;
             if($rowCount==0){
                $this->auditLogger->logInsert('ScrmPackingData', $recordId, $savedPackingRow, $RequestBody, [
                    'ReferenceNo' => $PIID,
                    'Remarks' => 'Export packing data created',
                ]);
             } else {
                $this->auditLogger->logUpdate('ScrmPackingData', $recordId, $existingPackingRow, $savedPackingRow, $RequestBody, [
                    'ReferenceNo' => $PIID,
                    'Remarks' => 'Export packing data updated',
                ]);
             }
             $Error = null;
        } else {
            $Error = null;
            $Msg = "Unable to Save";
        }

       


        $AryData['Error'] = $Error;
        $AryData['Msg'] = $Msg;
        return $AryData;
    }






}

?>
