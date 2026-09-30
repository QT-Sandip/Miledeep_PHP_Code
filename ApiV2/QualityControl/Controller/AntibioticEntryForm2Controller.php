<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class AntibioticEntryForm2Controller
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

    public function postAntibioticDetails($RequestBody)
    {
        $BreakUpID = $RequestBody['BreakUpID'];

        if ($BreakUpID == '') {
            $Data = $this->insertAntibioticDetails($RequestBody);
        } else {
            $Data = $this->updateAntibioticDetails($RequestBody);
        }

        return $Data;
    }

    public function insertAntibioticDetails($RequestBody)
    {
        $BreakUpID = $RequestBody['BreakUpID'];
        $SampleID = $RequestBody['SampleID'];
        $SampleType = $RequestBody['SampleType'];
        $SampleDate = $RequestBody['SampleDate'];
        $SampleTime = $RequestBody['SampleTime'];
        $SampleGPNumber = $RequestBody['SampleGPNumber'];
        $SamplerName = $RequestBody['SamplerName'];
        $ReceivedBy = $RequestBody['ReceivedBy'];
        $AnalysisDt = $RequestBody['AnalysisDt'];
        $CompletionDt = $RequestBody['CompletionDt'];
        $CAP = $RequestBody['CAP'];
        $AOZ = $RequestBody['AOZ'];
        $AMOZ = $RequestBody['AMOZ'];
        $AHD = $RequestBody['AHD'];
        $SEM = $RequestBody['SEM'];

        $sqlInsert = "INSERT INTO QcABSampleResultDataBreakUp(BreakUpID, SampleID, SampleType, SampleDate, SampleTime, 
        SampleGPNumber, SamplerName, ReceivedBy, AnalysisDt,CompletionDt, CAP, AOZ, AMOZ, AHD, SEM) 
        VALUES ('$BreakUpID','$SampleID','$SampleType','$SampleDate','$SampleTime','$SampleGPNumber','$SamplerName','$ReceivedBy',
        '$AnalysisDt', '$CompletionDt', '$CAP','$AOZ','$AMOZ','$AHD','$SEM')";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        // $errorInfo = $stmtInsert->errorInfo();
        // var_dump($errorInfo);

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['BreakUpID'] = $lastInsertedId;
            $createdRecord = $this->auditLogger->fetchRowById('QcABSampleResultDataBreakUp', 'BreakUpID', $lastInsertedId);
            $this->auditLogger->logInsert('QcABSampleResultDataBreakUp', $lastInsertedId, $createdRecord, $RequestBody, [
                'ReferenceNo' => $SampleID,
                'Remarks' => 'QC antibiotic sample breakup created',
            ]);

            $antibioticData = $this->getAntibioticDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Antibiotic BreakUp Posted Successfully';
            $Data['Data'] = $antibioticData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert Antibiotic BreakUp';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateAntibioticDetails($RequestBody)
    {
        $BreakUpID = $RequestBody['BreakUpID'];
        $SampleID = $RequestBody['SampleID'];
        $SampleType = $RequestBody['SampleType'];
        $SampleDate = $RequestBody['SampleDate'];
        $SampleTime = $RequestBody['SampleTime'];
        $SampleGPNumber = $RequestBody['SampleGPNumber'];
        $SamplerName = $RequestBody['SamplerName'];
        $ReceivedBy = $RequestBody['ReceivedBy'];
        $AnalysisDt = $RequestBody['AnalysisDt'];
        $CompletionDt = $RequestBody['CompletionDt'];
        $CAP = $RequestBody['CAP'];
        $AOZ = $RequestBody['AOZ'];
        $AMOZ = $RequestBody['AMOZ'];
        $AHD = $RequestBody['AHD'];
        $SEM = $RequestBody['SEM'];
        $existingRecord = $this->auditLogger->fetchRowById('QcABSampleResultDataBreakUp', 'BreakUpID', $BreakUpID);

        $sqlUpdate = "UPDATE QcABSampleResultDataBreakUp SET SampleID='$SampleID', SampleType='$SampleType',SampleDate='$SampleDate', 
        SampleTime='$SampleTime', SampleGPNumber='$SampleGPNumber', SamplerName='$SamplerName',ReceivedBy='$ReceivedBy',
        AnalysisDt='$AnalysisDt', CompletionDt='$CompletionDt', CAP='$CAP', AOZ='$AOZ', AMOZ='$AMOZ', AHD='$AHD', SEM='$SEM'
        WHERE BreakUpID='$BreakUpID'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        // $errorInfo = $stmtUpdate->errorInfo();
        // var_dump($errorInfo);

        if ($stmtUpdate) {
            $updatedRecord = $this->auditLogger->fetchRowById('QcABSampleResultDataBreakUp', 'BreakUpID', $BreakUpID);
            $this->auditLogger->logUpdate('QcABSampleResultDataBreakUp', $BreakUpID, $existingRecord, $updatedRecord, $RequestBody, [
                'ReferenceNo' => $SampleID,
                'Remarks' => 'QC antibiotic sample breakup updated',
            ]);
            $antibioticData = $this->getAntibioticDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Antibiotic BreakUp Updated Successfully';
            $Data['Data'] = $antibioticData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update Antibiotic BreakUp';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function deleteAntibioticDetails($RequestBody)
    {
        $BreakUpID = $RequestBody['BreakUpID'];
        $existingRecord = $this->auditLogger->fetchRowById('QcABSampleResultDataBreakUp', 'BreakUpID', $BreakUpID);

        $sqlDelete = "DELETE FROM `QcABSampleResultDataBreakUp` WHERE BreakUpID='$BreakUpID'";

        $stmtUpdate = $this->conn->prepare($sqlDelete);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $this->auditLogger->logDelete('QcABSampleResultDataBreakUp', $BreakUpID, $existingRecord, $RequestBody, [
                'ReferenceNo' => $BreakUpID,
                'Remarks' => 'QC antibiotic sample breakup deleted',
            ]);
            $antibioticData = $this->getAntibioticDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Antibiotic BreakUp Deleted Successfully';
            $Data['Data'] = $antibioticData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Delete Antibiotic BreakUp';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function getAntibioticDetails($RequestBody)
    {
        $SampleID = $RequestBody['SampleID'];

        $sqlCount = "SELECT COUNT(SampleID) as Total  FROM `QcABSampleResultDataBreakUp`";

        $stmt = $this->conn->prepare($sqlCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];

        $sqlFields = "QSRDB.BreakUpID, QSRDB.SampleID, QSRDB.SampleType,  QSRDB.SampleDate, QSRDB.SampleTime, QSRDB.SampleGPNumber, 
        QSRDB.SamplerName, QSRDB.ReceivedBy, QSRDB.AnalysisDt, QSRDB.CompletionDt, QSRDB.CAP, QSRDB.AOZ, QSRDB.AMOZ, QSRDB.AHD, 
        QSRDB.SEM, CONCAT(QLT.LbuName, ' - ' , QLT.LbuDesignation) AS LabName";

        $sqlQuery = "SELECT $sqlFields FROM QcABSampleResultDataBreakUp AS QSRDB 
        LEFT JOIN QcLabTechnologist AS QLT ON QSRDB.ReceivedBy = QLT.LUID
        WHERE SampleID='$SampleID' 
        ORDER BY QSRDB.SampleID ";

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayAntibioticData = [];

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayAntibioticList['SNO'] = ++$i;
            $ArrayAntibioticList['BreakUpID'] = $dataRow['BreakUpID'];
            $ArrayAntibioticList['SampleID'] = $dataRow['SampleID'];
            $ArrayAntibioticList['SampleType'] = $dataRow['SampleType'];
            $ArrayAntibioticList['SampleDate'] = $dataRow['SampleDate'];
            $ArrayAntibioticList['SampleTime'] = $dataRow['SampleTime'];
            $ArrayAntibioticList['SampleGPNumber'] = $dataRow['SampleGPNumber'];
            $ArrayAntibioticList['SamplerName'] = $dataRow['SamplerName'];
            $ArrayAntibioticList['ReceivedBy'] = $dataRow['ReceivedBy'];
            $ArrayAntibioticList['LabName'] = $dataRow['LabName'];
            $ArrayAntibioticList['AnalysisDt'] = $dataRow['AnalysisDt'];
            $ArrayAntibioticList['CompletionDt'] = $dataRow['CompletionDt'];
            $ArrayAntibioticList['CAP'] = $dataRow['CAP'];
            $ArrayAntibioticList['AOZ'] = $dataRow['AOZ'];
            $ArrayAntibioticList['AMOZ'] = $dataRow['AMOZ'];
            $ArrayAntibioticList['AHD'] = $dataRow['AHD'];
            $ArrayAntibioticList['SEM'] = $dataRow['SEM'];

            $ArrayAntibioticData[] =  $ArrayAntibioticList;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['Data'] = $ArrayAntibioticData;
        
        return $data;
    }
}
