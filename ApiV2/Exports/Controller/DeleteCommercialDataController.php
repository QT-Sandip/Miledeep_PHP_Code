<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class DeleteCommercialDataController
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




    public function deleteCommercialData($RequestBody)
    {
        $Error = null;
        $OrderID = $RequestBody['OrderID'];
        $Msg = "Deleted Successfully";
        $existingCommercialRows = $this->auditLogger->fetchRowsByConditions('ScrmCommercialData', ['PoID' => $OrderID]);
        $existingBreakupRows = $this->auditLogger->fetchRowsByConditions('ScrmCIGradeData', ['InvId' => $OrderID]);


      $sql = "DELETE FROM `ScrmCommercialData` WHERE PoID='$OrderID'"; 
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();


      
        if ($stmt) {
            $sqlCommercialBreakupDetails = "DELETE FROM `ScrmCIGradeData` WHERE InvId='$OrderID'";
            $stmtCommercialBreakupDetails = $this->conn->prepare($sqlCommercialBreakupDetails);
            $stmtCommercialBreakupDetails->execute();
        if ($stmtCommercialBreakupDetails) {
        foreach ($existingCommercialRows as $row) {
            $recordId = isset($row['PoID']) ? $row['PoID'] : $OrderID;
            $this->auditLogger->logDelete('ScrmCommercialData', $recordId, $row, $RequestBody, [
                'ReferenceNo' => $OrderID,
                'Remarks' => 'Export commercial data deleted',
            ]);
        }
        foreach ($existingBreakupRows as $row) {
            $recordId = isset($row['ID']) ? $row['ID'] : $OrderID;
            $this->auditLogger->logDelete('ScrmCIGradeData', $recordId, $row, $RequestBody, [
                'ReferenceNo' => $OrderID,
                'Remarks' => 'Export commercial breakup deleted',
            ]);
        }
        $Error = null;
        } else {
        $Error = null;
        $Msg = "Unable to delete breakup details";
        }

            $Error = null;
        } else {
            $Error = null;
            $Msg = "Unable to delete";
        }

       


        $AryData['Error'] = $Error;
        $AryData['Msg'] = $Msg;
        return $AryData;
    }






}

?>
