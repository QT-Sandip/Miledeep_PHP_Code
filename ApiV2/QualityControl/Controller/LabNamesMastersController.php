<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class LabNamesMastersController
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

    public function postLabDetails($RequestBody)
    {
        $LabId = $RequestBody['LabId'];

        if ($LabId == '') {
            $Data = $this->insertLabDetails($RequestBody);
        } else {
            $Data = $this->updateLabDetails($RequestBody);
        }

        return $Data;
    }

    public function insertLabDetails($RequestBody)
    {
        $LabId = $RequestBody['LabId'];
        $LabName = $RequestBody['LabName'];
        $Address = $RequestBody['Address'];
        $Description = $RequestBody['Description'];
        $Status = $RequestBody['Status'];

        // SELECT LabId, LabName, Address, RepresentationDescription, Status, Color FROM 

        $sqlInsert = "INSERT INTO QcEiaLabNames(LabId, LabName, Address, RepresentationDescription, Status) 
        VALUES ('$LabId', '$LabName', '$Address', '$Description', 1)";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['LabId'] = $lastInsertedId;
            $createdLab = $this->auditLogger->fetchRowById('QcEiaLabNames', 'LabId', $lastInsertedId);
            $this->auditLogger->logInsert('QcEiaLabNames', $lastInsertedId, $createdLab, $RequestBody, [
                'ReferenceNo' => $lastInsertedId,
                'Remarks' => 'QC lab name master created',
            ]);

            $LabData = $this->getLabDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Lab Posted Successfully';
            $Data['Data'] = $LabData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert Lab';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateLabDetails($RequestBody)
    {
        $LabId = $RequestBody['LabId'];
        $LabName = $RequestBody['LabName'];
        $Address = $RequestBody['Address'];
        $Description = $RequestBody['Description'];
        $Status = $RequestBody['Status'];
        $existingLab = $this->auditLogger->fetchRowById('QcEiaLabNames', 'LabId', $LabId);

        $sqlUpdate = "UPDATE QcEiaLabNames SET LabName='$LabName', Address='$Address', RepresentationDescription='$Description', Status=1
        WHERE LabId='$LabId'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $updatedLab = $this->auditLogger->fetchRowById('QcEiaLabNames', 'LabId', $LabId);
            $this->auditLogger->logUpdate('QcEiaLabNames', $LabId, $existingLab, $updatedLab, $RequestBody, [
                'ReferenceNo' => $LabId,
                'Remarks' => 'QC lab name master updated',
            ]);
            $LabData = $this->getLabDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Lab Updated Successfully';
            $Data['Data'] = $LabData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update Lab';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function getLabDetails($RequestBody)
    {

        $RecordsPerPage = 10;
        $LabId = $RequestBody['LabId'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        $RecordsPerPage = 10;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', LabName,LabId,Address,Status) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total FROM `QcEiaLabNames`";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "LabId, LabName, Address, RepresentationDescription, Status";

        if ($LabId != "") {
            $sqlQuery = "SELECT $sqlFields FROM QcEiaLabNames WHERE LabId='$LabId'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM QcEiaLabNames 
            WHERE 1=1 $Query " . $Limit;
        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayLabData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayLabList['SNO'] = ++$i;
            $ArrayLabList['LabId'] = $dataRow['LabId'];
            $ArrayLabList['LabName'] = $dataRow['LabName'];
            $ArrayLabList['Address'] = $dataRow['Address'];
            $ArrayLabList['Description'] = $dataRow['RepresentationDescription'];
            $ArrayLabList['Status'] = $dataRow['Status'];

            $ArrayLabData[] =  $ArrayLabList;
        }
        if (count($ArrayLabData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['EditLink'] = "lab-names-create.php?LabId=";
        $data['Data'] = $ArrayLabData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
