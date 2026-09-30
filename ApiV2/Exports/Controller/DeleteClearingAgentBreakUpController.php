<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class DeleteClearingAgentBreakUpController
{
    private $conn;
    //private $db_table = "ScrmPIData";
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




    public function deleteClearingAgentBreakup($RequestBody)
    {
        $Error = null;
        $BreakUpID = $RequestBody['BreakUpID'];
        $Msg = "Deleted Successfully";
        $existingBreakup = $this->auditLogger->fetchRowById('ScrmClearingAgentBreakupDetails', 'ClearingAgentDetailsBreakupID', $BreakUpID);

            $sql = "DELETE FROM `ScrmClearingAgentBreakupDetails` WHERE ClearingAgentDetailsBreakupID='$BreakUpID'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            if ($stmt) {
            $this->auditLogger->logDelete('ScrmClearingAgentBreakupDetails', $BreakUpID, $existingBreakup, $RequestBody, [
                'ReferenceNo' => isset($existingBreakup['PoID']) ? $existingBreakup['PoID'] : null,
                'Remarks' => 'Export clearing agent breakup deleted',
            ]);
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
