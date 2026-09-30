<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class HatcheryMasterController
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

    public function postHatcheryMasterDetails($RequestBody)
    {
        $HatcheryNameID = $RequestBody['HatcheryNameID'];
        $HatcheryName = $RequestBody['HatcheryName'];
        $HatcheryLocation = $RequestBody['HatcheryLocation'];
        $PlantID = $RequestBody['PlantID'];
        $Status = $RequestBody['Status'];

        if ($HatcheryNameID == '') {
            // $sqlInsertCheck = "SELECT COUNT(*) AS InsertRowCount FROM QcAuditHatcheryMasters WHERE HatcheryName = '$HatcheryName'";
            // $stmtInsertCheck = $this->conn->prepare($sqlInsertCheck);
            // $stmtInsertCheck->execute();
            // $rowInsertCheck =  $stmtInsertCheck->fetch(PDO::FETCH_ASSOC);
            // $InsertRowCount = $rowInsertCheck['InsertRowCount'];
            // if ($InsertRowCount == 0) {

            $sqlInsert = "INSERT INTO QcAuditHatcheryMasters(HatcheryName, HatcheryLocation, PlantID, Status) 
                VALUES ('$HatcheryName', '$HatcheryLocation', '$PlantID', '$Status')";
            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();
            // $errorInfo = $stmtInsert->errorInfo();
            // var_dump($errorInfo);

            if ($stmtInsert) {
                $lastInsertedId = $this->conn->lastInsertId();
                $createdHatchery = $this->auditLogger->fetchRowById('QcAuditHatcheryMasters', 'HatcheryNameID', $lastInsertedId);
                $this->auditLogger->logInsert('QcAuditHatcheryMasters', $lastInsertedId, $createdHatchery, $RequestBody, [
                    'ReferenceNo' => $lastInsertedId,
                    'Remarks' => 'QC hatchery master created',
                ]);
                $this->Error = null;
                $this->Msg = 'Hatchery Posted Successfully';
            } else {
                $this->Error = "Unable to Post Hatchery";
                $this->Msg = "Unable to Post Hatchery";
            }
            // } else {
            //     $this->Error = "Hatchery Name already exist";
            //     $this->Msg = "Hatchery Name already exist";
            // }
        } else {
            // $sqlUpdateCheck = "SELECT COUNT(*) AS UpdateRowCount FROM QcAuditHatcheryMasters 
            // WHERE HatcheryName = '$HatcheryName' AND HatcheryNameID != '$HatcheryNameID'";
            // $stmtUpdateCheck = $this->conn->prepare($sqlUpdateCheck);
            // $stmtUpdateCheck->execute();
            // $rowUpdateCheck =  $stmtUpdateCheck->fetch(PDO::FETCH_ASSOC);
            // $UpdateRowCount = $rowUpdateCheck['UpdateRowCount'];
            // if ($UpdateRowCount == 0) {
            $existingHatchery = $this->auditLogger->fetchRowById('QcAuditHatcheryMasters', 'HatcheryNameID', $HatcheryNameID);
            $sqlUpdate = "UPDATE QcAuditHatcheryMasters SET HatcheryName='$HatcheryName',
                HatcheryLocation='$HatcheryLocation',PlantID='$PlantID',Status='$Status'
                WHERE HatcheryNameID='$HatcheryNameID'";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();
            // $errorInfo = $stmtUpdate->errorInfo();
            // var_dump($errorInfo);

            if ($stmtUpdate) {
                $updatedHatchery = $this->auditLogger->fetchRowById('QcAuditHatcheryMasters', 'HatcheryNameID', $HatcheryNameID);
                $this->auditLogger->logUpdate('QcAuditHatcheryMasters', $HatcheryNameID, $existingHatchery, $updatedHatchery, $RequestBody, [
                    'ReferenceNo' => $HatcheryNameID,
                    'Remarks' => 'QC hatchery master updated',
                ]);
                $this->Error = null;
                $this->Msg = 'Hatchery Updated Successfully';
            } else {
                $this->Error = "Unable to Update Hatchery";
                $this->Msg = "Unable to Update Hatchery";
            }
            // } else {
            //     $this->Error = "Hatchery Name already exist";
            //     $this->Msg = "Hatchery Name already exist";
            // }
        }

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;

        return $Data;
    }

    function getHatcheryMasterData($RequestBody)
    {
        $PlantID = $RequestBody['PlantID'];
        $HatcheryNameID = $RequestBody['HatcheryNameID'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($PlantID != '') {
            $Query .= " AND QAHM.PlantID = '$PlantID'";
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('',HatcheryNameID, HatcheryName, HatcheryLocation, PlantID, Status) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(*) AS TotalCount FROM QcAuditHatcheryMasters 
        WHERE 1=1 $Query ";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataCount['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "HatcheryNameID, HatcheryName, HatcheryLocation, QAHM.PlantID, QAHM.Status,StationName,OptionName";

        if ($HatcheryNameID) {
            $sqlSelect = "SELECT $sqlFields FROM QcAuditHatcheryMasters AS QAHM
            LEFT JOIN QcAuditDropdownMasters AS QADM ON QAHM.HatcheryName = QADM.OptionID
            LEFT JOIN RmProcessingStations AS RPS ON RPS.ProcessingStationID = QAHM.PlantID
            WHERE HatcheryNameID = '$HatcheryNameID'";
        } else {
            $sqlSelect = "SELECT $sqlFields FROM QcAuditHatcheryMasters AS QAHM
            LEFT JOIN QcAuditDropdownMasters AS QADM ON QAHM.HatcheryName = QADM.OptionID
            LEFT JOIN RmProcessingStations AS RPS ON RPS.ProcessingStationID = QAHM.PlantID
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
            $ArrayList['HatcheryNameID'] = $dataRow['HatcheryNameID'];
            $ArrayList['HatcheryName'] = $dataRow['HatcheryName'];
            $ArrayList['OptionName'] = $dataRow['OptionName'];
            $ArrayList['HatcheryLocation'] = $dataRow['HatcheryLocation'];
            $ArrayList['PlantID'] = $dataRow['PlantID'];
            $ArrayList['StationName'] = $dataRow['StationName'];
            $ArrayList['Status'] = $dataRow['Status'];
            $ArrayList['EditLink'] = 'hatchery-master-create?HatcheryNameID=' . $dataRow['HatcheryNameID'];

            $ArrayData[] = $ArrayList;
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['TotalCount'] = $TotalCount;
        $Data['PageCount'] = $PageCount;
        $Data['HatcheryData'] = $ArrayData;

        return $Data;
    }

    public function deleteHatcheryMasterDetals($RequestBody)
    {
        $HatcheryNameID = $RequestBody['HatcheryNameID'];
        $existingHatchery = $this->auditLogger->fetchRowById('QcAuditHatcheryMasters', 'HatcheryNameID', $HatcheryNameID);

        $sqlDelete = "DELETE FROM QcAuditHatcheryMasters WHERE HatcheryNameID = '$HatcheryNameID'";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();
        // $errorInfo = $stmtDelete->errorInfo();
        // var_dump($errorInfo);

        if ($stmtDelete) {
            $this->auditLogger->logDelete('QcAuditHatcheryMasters', $HatcheryNameID, $existingHatchery, $RequestBody, [
                'ReferenceNo' => $HatcheryNameID,
                'Remarks' => 'QC hatchery master deleted',
            ]);
            $this->Error = null;
            $this->Msg = 'Hatchery Delete Successfully';
        } else {
            $this->Error = "Unable to Delete Hatchery";
            $this->Msg = "Unable to Delete Hatchery";
        }

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        return  $Data;
    }
}
