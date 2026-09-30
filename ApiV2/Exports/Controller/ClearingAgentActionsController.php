<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class ClearingAgentActionsController
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




    public function deleteClearingAgent($RequestBody)
    {
        $Error = null;
        $OrderID = $RequestBody['OrderID'];
        $Msg = "Deleted Successfully";
        $existingAgentRows = $this->auditLogger->fetchRowsByConditions('ScrmClearingAgentDetails', ['PoID' => $OrderID]);
        $existingBreakupRows = $this->auditLogger->fetchRowsByConditions('ScrmClearingAgentBreakupDetails', ['PoID' => $OrderID]);


     $sql = "DELETE FROM `ScrmClearingAgentDetails` WHERE PoID='$OrderID'"; 
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();


      
        if ($stmt) {
            $sqlClearingAgentBreakupDetails = "DELETE FROM `ScrmClearingAgentBreakupDetails` WHERE PoID='$OrderID'";
            $stmtClearingAgentBreakupDetails = $this->conn->prepare($sqlClearingAgentBreakupDetails);
            $stmtClearingAgentBreakupDetails->execute();
            foreach ($existingAgentRows as $row) {
                $recordId = isset($row['ClearingAgentDetailsID']) ? $row['ClearingAgentDetailsID'] : $OrderID;
                $this->auditLogger->logDelete('ScrmClearingAgentDetails', $recordId, $row, $RequestBody, [
                    'ReferenceNo' => $OrderID,
                    'Remarks' => 'Export clearing agent header deleted',
                    'Async' => 1,
                ]);
            }
            foreach ($existingBreakupRows as $row) {
                $recordId = isset($row['ClearingAgentDetailsBreakupID']) ? $row['ClearingAgentDetailsBreakupID'] : $OrderID;
                $this->auditLogger->logDelete('ScrmClearingAgentBreakupDetails', $recordId, $row, $RequestBody, [
                    'ReferenceNo' => $OrderID,
                    'Remarks' => 'Export clearing agent breakup deleted',
                    'Async' => 1,
                ]);
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
