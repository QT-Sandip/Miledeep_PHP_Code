<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class AnalysisRequiredMastersController
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

    public function postAnalysisRequiredDetails($RequestBody)
    {
        $LabId = $RequestBody['LabId'];

        if ($LabId == '') {
            $Data = $this->insertAnalysisRequiredDetails($RequestBody);
        } else {
            $Data = $this->updateAnalysisRequiredDetails($RequestBody);
        }

        return $Data;
    }

    public function insertAnalysisRequiredDetails($RequestBody)
    {
        $TestID = $RequestBody['TestID'];
        $TestCategory = $RequestBody['TestCategory'];
        $TestName = $RequestBody['TestName'];

        $sqlInsert = "INSERT INTO QcTestList(TestID, TestCategory, TestName) 
        VALUES ('$TestID', '$TestCategory', '$TestName')";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['TestID'] = $lastInsertedId;
            $createdTest = $this->auditLogger->fetchRowById('QcTestList', 'TestID', $lastInsertedId);
            $this->auditLogger->logInsert('QcTestList', $lastInsertedId, $createdTest, $RequestBody, [
                'ReferenceNo' => $lastInsertedId,
                'Remarks' => 'QC analysis required master created',
            ]);

            $LabData = $this->getAnalysisRequiredDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Analysis Required Posted Successfully';
            $Data['Data'] = $LabData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert Analysis Required';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateAnalysisRequiredDetails($RequestBody)
    {
        $TestID = $RequestBody['TestID'];
        $TestCategory = $RequestBody['TestCategory'];
        $TestName = $RequestBody['TestName'];
        $existingTest = $this->auditLogger->fetchRowById('QcTestList', 'TestID', $TestID);

        $sqlUpdate = "UPDATE QcTestList SET TestCategory='$TestCategory', TestName='$TestName'
        WHERE TestID='$TestID'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $updatedTest = $this->auditLogger->fetchRowById('QcTestList', 'TestID', $TestID);
            $this->auditLogger->logUpdate('QcTestList', $TestID, $existingTest, $updatedTest, $RequestBody, [
                'ReferenceNo' => $TestID,
                'Remarks' => 'QC analysis required master updated',
            ]);
            $LabData = $this->getAnalysisRequiredDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Analysis Required Updated Successfully';
            $Data['Data'] = $LabData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update Analysis Required';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function getAnalysisRequiredDetails($RequestBody)
    {

        $RecordsPerPage = 100;
        $TestID = $RequestBody['TestID'];
        // $TestCategory = $RequestBody['TestCategory'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        // if ($TestCategory != "") {
        //     $Query .= " and TestCategory='$TestCategory'";
        // }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', TestID,TestCategory,TestName) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total FROM `QcTestList`";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "TestID, TestCategory, TestName";

        if ($TestID != "") {
            $sqlQuery = "SELECT $sqlFields FROM QcTestList WHERE TestID='$TestID'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM QcTestList 
            WHERE 1=1 $Query " . $Limit;
        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayAnalysisData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayAnalysisList['SNO'] = ++$i;
            $ArrayAnalysisList['TestID'] = $dataRow['TestID'];
            $ArrayAnalysisList['TestCategory'] = $dataRow['TestCategory'];
            $ArrayAnalysisList['TestName'] = $dataRow['TestName'];

            $ArrayAnalysisData[] =  $ArrayAnalysisList;
        }
        if (count($ArrayAnalysisData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['EditLink'] = "analysis-required-create.php?TestID=";
        $data['Data'] = $ArrayAnalysisData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
