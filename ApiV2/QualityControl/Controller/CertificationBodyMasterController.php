<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class CertificationBodyMasterController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'QualityControl');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postCertificationBodyMasterDetails($RequestBody)
    {

        $CertificationBodyID = $RequestBody['CertificationBodyID'];
        $AuditNameID = $RequestBody['AuditNameID'];
        $PlantID = $RequestBody['PlantID'];
        $CertificationBodyName = $RequestBody['CertificationBodyName'];
        $Status = $RequestBody['Status'];

        if ($CertificationBodyID == '') {

            $sqlInsertCheck = "SELECT COUNT(*) AS InsertRowCount FROM QcAuditCertificationBodyMaster WHERE CertificationBodyName = '$CertificationBodyName'";
            $stmtInsertCheck = $this->conn->prepare($sqlInsertCheck);
            $stmtInsertCheck->execute();
            $dataInsertCheck = $stmtInsertCheck->fetch(PDO::FETCH_ASSOC);
            $InsertRowCount = $dataInsertCheck['InsertRowCount'];

            if ($InsertRowCount == 0) {
                $sqlInsert = "INSERT INTO QcAuditCertificationBodyMaster(AuditNameID, PlantID, CertificationBodyName, Status) 
                VALUES ('$AuditNameID', '$PlantID', '$CertificationBodyName', '$Status')";

                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->execute();
                // $errorInfo = $stmtInsert->errorInfo();
                // var_dump($errorInfo);

                if ($stmtInsert) {
                    $lastInsertedId = $this->conn->lastInsertId();
                    $createdBody = $this->auditLogger->fetchRowById('QcAuditCertificationBodyMaster', 'CertificationBodyID', $lastInsertedId);
                    $this->auditLogger->logInsert('QcAuditCertificationBodyMaster', $lastInsertedId, $createdBody, $RequestBody, [
                        'ReferenceNo' => $lastInsertedId,
                        'Remarks' => 'QC certification body master created',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'CertificationBody Posted Successfully';
                } else {
                    $this->Error = "Unable to Post CertificationBody";
                    $this->Msg = "Unable to Post CertificationBody";
                }
            } else {
                $this->Error = "Certification Body Name already exist";
                $this->Msg = "Certification Body Name already exist";
            }
        } else {
            $sqlUpdateCheck = "SELECT COUNT(*) AS UpdateRowCount FROM QcAuditCertificationBodyMaster 
            WHERE CertificationBodyName = '$CertificationBodyName' AND CertificationBodyID != '$CertificationBodyID'";
            $stmtUpdateCheck = $this->conn->prepare($sqlUpdateCheck);
            $stmtUpdateCheck->execute();
            $dataUpdateCheck = $stmtUpdateCheck->fetch(PDO::FETCH_ASSOC);
            $UpdateRowCount = $dataUpdateCheck['UpdateRowCount'];

            if ($UpdateRowCount == 0) {
                $existingBody = $this->auditLogger->fetchRowById('QcAuditCertificationBodyMaster', 'CertificationBodyID', $CertificationBodyID);
                $sqlUpdate = "UPDATE QcAuditCertificationBodyMaster SET AuditNameID='$AuditNameID',
                PlantID='$PlantID',CertificationBodyName='$CertificationBodyName',Status='$Status' 
                WHERE CertificationBodyID='$CertificationBodyID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $updatedBody = $this->auditLogger->fetchRowById('QcAuditCertificationBodyMaster', 'CertificationBodyID', $CertificationBodyID);
                    $this->auditLogger->logUpdate('QcAuditCertificationBodyMaster', $CertificationBodyID, $existingBody, $updatedBody, $RequestBody, [
                        'ReferenceNo' => $CertificationBodyID,
                        'Remarks' => 'QC certification body master updated',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'CertificationBody Updated Successfully';
                } else {
                    $this->Error = "Unable to Update CertificationBody";
                    $this->Msg = "Unable to Update CertificationBody";
                }
            } else {
                $this->Error = "Certification Body Name already exist";
                $this->Msg = "Certification Body Name already exist";
            }
        }

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;

        return $Data;
    }

    function getCertificationBodyMasterData($RequestBody)
    {
        $CertificationBodyID = $RequestBody['CertificationBodyID'];
        $AuditNameID = $RequestBody['AuditNameID'];
        $PlantID = $RequestBody['PlantID'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';
        if ($AuditNameID != '') {
            $Query .= " AND AuditNameID = '$AuditNameID'";
        }

        if ($PlantID != '') {
            $Query .= " AND QAC.PlantID = '$PlantID'";
        }
        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('',CertificationBodyID, AuditNameID, QAC.PlantID, CertificationBodyName, QAC.Status, 
            StationName,QACM.CategoryName) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(*) AS TotalCount FROM QcAuditCertificationBodyMaster AS QAC
        LEFT JOIN RmProcessingStations AS RPS ON QAC.PlantID = RPS.ProcessingStationID
        LEFT JOIN QcAuditCategoriesMaster AS QACM ON QAC.AuditNameID = QACM.AuditID
        WHERE 1=1 $Query ";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataCount['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);


        $sqlFields = "CertificationBodyID, AuditNameID, QAC.PlantID, CertificationBodyName, QAC.Status, StationName,QACM.CategoryName";

        if ($CertificationBodyID) {
            $sqlSelect = "SELECT $sqlFields FROM QcAuditCertificationBodyMaster AS QAC
            LEFT JOIN RmProcessingStations AS RPS ON QAC.PlantID = RPS.ProcessingStationID
            LEFT JOIN QcAuditCategoriesMaster AS QACM ON QAC.AuditNameID = QACM.AuditID
            WHERE CertificationBodyID = '$CertificationBodyID'";
        } else {
            $sqlSelect = "SELECT $sqlFields FROM QcAuditCertificationBodyMaster AS QAC
            LEFT JOIN RmProcessingStations AS RPS ON QAC.PlantID = RPS.ProcessingStationID
            LEFT JOIN QcAuditCategoriesMaster AS QACM ON QAC.AuditNameID = QACM.AuditID
            WHERE 1=1 $Query " . $Limit;
        }
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        // $erroInfo = $stmtSelect->errorInfo();
        // var_dump($erroInfo);

        $ArrayData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayList['SNO'] = ++$i;
            $ArrayList['CertificationBodyID'] = $dataRow['CertificationBodyID'];
            $ArrayList['AuditNameID'] = $dataRow['AuditNameID'];
            $ArrayList['CategoryName'] = $dataRow['CategoryName'];
            $ArrayList['PlantID'] = $dataRow['PlantID'];
            $ArrayList['StationName'] = $dataRow['StationName'];
            $ArrayList['CertificationBodyName'] = $dataRow['CertificationBodyName'];
            $ArrayList['Status'] = $dataRow['Status'];
            $ArrayList['EditLink'] = 'certification-body-master-create?CertificationBodyID=' . $dataRow['CertificationBodyID'];

            $ArrayData[] = $ArrayList;
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['PageCount'] = $PageCount;
        $Data['TotalCount'] = $TotalCount;
        $Data['CertificationBodyData'] = $ArrayData;

        return $Data;
    }

    public function deleteCertificationBodyMasterDetals($RequestBody)
    {
        $CertificationBodyID = $RequestBody['CertificationBodyID'];
        $existingBody = $this->auditLogger->fetchRowById('QcAuditCertificationBodyMaster', 'CertificationBodyID', $CertificationBodyID);

        $sqlDelete = "DELETE FROM QcAuditCertificationBodyMaster 
        WHERE CertificationBodyID = '$CertificationBodyID'";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();
        // $errorInfo = $stmtDelete->errorInfo();
        // var_dump($errorInfo);

        if ($stmtDelete) {
            $this->auditLogger->logDelete('QcAuditCertificationBodyMaster', $CertificationBodyID, $existingBody, $RequestBody, [
                'ReferenceNo' => $CertificationBodyID,
                'Remarks' => 'QC certification body master deleted',
            ]);
            $this->Error = null;
            $this->Msg = 'CertificationBody Delete Successfully';
        } else {
            $this->Error = "Unable to Delete CertificationBody";
            $this->Msg = "Unable to Delete CertificationBody";
        }

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        return  $Data;
    }
}
