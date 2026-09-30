<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class DatabaseAPIController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Exports');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postDatabaseDetails($RequestBody)
    {
        $Permission = $RequestBody['Permission'];

        if ($Permission == 'Granted') {

            $sqlPiData = "SELECT PoID,ProcessorID FROM ScrmPIData WHERE ProductionPlantID='' OR ProductionPlantID=0";
            // echo $sqlPiData = "SELECT PoID,ProcessorID,ProductionPlantID FROM ScrmPIData";
            $stmtPiData = $this->conn->prepare($sqlPiData);
            $stmtPiData->execute();

            $data = [];
            while ($dataRow = $stmtPiData->fetch(PDO::FETCH_ASSOC)) {

                $PoID = $dataRow['PoID'];
                $ProcessorID = $dataRow['ProcessorID'];
                $ProductionPlantID = $dataRow['ProductionPlantID'];

                $sqlAED = "SELECT InvShortCode FROM AdminExporterDetails WHERE ExpID='$ProcessorID'";
                $stmtAED = $this->conn->prepare($sqlAED);
                $stmtAED->execute();
                $dataAED = $stmtAED->fetch(PDO::FETCH_ASSOC);
                $invShortCode = $dataAED['InvShortCode'];

                $sqlRmPS = "SELECT ProcessingStationID, ProdCode  FROM  RmProcessingStations WHERE ProdCode='$invShortCode'";
                $stmtRmPS = $this->conn->prepare($sqlRmPS);
                $stmtRmPS->execute();
                $dataRmPS = $stmtRmPS->fetch(PDO::FETCH_ASSOC);
                $ProcessingStationID = $dataRmPS['ProcessingStationID'];

                // $errorInfo = $stmtRmPS->errorInfo();
                // var_dump($errorInfo);

                $existingPi = $this->auditLogger->fetchRowById('ScrmPIData', 'PoID', $PoID);
                $sqlInsertPlant = "UPDATE ScrmPIData SET ProductionPlantID='$ProcessingStationID' WHERE PoID='$PoID'";
                $stmtInsertPlant = $this->conn->prepare($sqlInsertPlant);
                $stmtInsertPlant->execute();

                if ($stmtInsertPlant) {
                    $updatedPi = $this->auditLogger->fetchRowById('ScrmPIData', 'PoID', $PoID);
                    $this->auditLogger->logUpdate('ScrmPIData', $PoID, $existingPi, $updatedPi, $RequestBody, [
                        'ReferenceNo' => $PoID,
                        'Remarks' => 'Exports production plant backfilled',
                        'Async' => 1,
                    ]);
                    $list['PoID'] = $PoID;
                    $list['ProcessorID'] = $ProcessorID;
                    $list['InvShortCode'] = $invShortCode;
                    $list['ProcessingStationID'] = $ProcessingStationID;
                    $list['ProductionPlantID'] = $ProductionPlantID;

                    $data[] = $list;
                } else {
                    $data = 'check your code there are some mistakes';
                }
            }
        }
        $Data['Error'] = null;
        $Data['Msg'] = 'success';
        $Data['Data'] = $data;

        return $Data;
    }
}
