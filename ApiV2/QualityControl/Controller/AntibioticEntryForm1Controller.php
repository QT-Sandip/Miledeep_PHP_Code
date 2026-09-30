<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class AntibioticEntryForm1Controller
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
        $SampleID = $RequestBody['SampleID'];

        if ($SampleID == '') {
            $Data = $this->insertAntibioticDetails($RequestBody);
        } else {
            $Data = $this->updateAntibioticDetails($RequestBody);
        }

        return $Data;
    }

    public function insertAntibioticDetails($RequestBody)
    {
        $SampleID = $RequestBody['SampleID'];
        $TankNo = $RequestBody['TankNo'];
        $BatchNo = $RequestBody['BatchNo'];
        $SampleDetails = $RequestBody['SampleDetails'];
        $AnalyzedBy = $RequestBody['AnalyzedBy'];
        $CheckedBy = $RequestBody['CheckedBy'];
        $ExternalLabID = $RequestBody['ExternalLabID'];
        $SamplDt = $RequestBody['SamplDt'];
        $SampleResult = $RequestBody['SampleResult'];
        $VerifiedBy = $RequestBody['VerifiedBy'];

        $sqlInsert = "INSERT INTO QcABSampleResultData(SampleID, TankNo, BatchNo, SampleDetails, AnalyzedBy, CheckedBy, 
        ExternalLabID, SamplDt, SampleResult, VerifiedBy) 
        VALUES ('$SampleID','$TankNo','$BatchNo','$SampleDetails','$AnalyzedBy','$CheckedBy','$ExternalLabID','$SamplDt',
        '$SampleResult', '$VerifiedBy')";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['SampleID'] = $lastInsertedId;
            $createdRecord = $this->auditLogger->fetchRowById('QcABSampleResultData', 'SampleID', $lastInsertedId);
            $this->auditLogger->logInsert('QcABSampleResultData', $lastInsertedId, $createdRecord, $RequestBody, [
                'ReferenceNo' => $lastInsertedId,
                'Remarks' => 'QC antibiotic sample created',
            ]);

            $antibioticData = $this->getAntibioticDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Antibiotic Posted Successfully';
            $Data['Data'] = $antibioticData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert Antibiotic';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateAntibioticDetails($RequestBody)
    {
        $SampleID = $RequestBody['SampleID'];
        $TankNo = $RequestBody['TankNo'];
        $BatchNo = $RequestBody['BatchNo'];
        $SampleDetails = $RequestBody['SampleDetails'];
        $AnalyzedBy = $RequestBody['AnalyzedBy'];
        $CheckedBy = $RequestBody['CheckedBy'];
        $ExternalLabID = $RequestBody['ExternalLabID'];
        $SamplDt = $RequestBody['SamplDt'];
        $SampleResult = $RequestBody['SampleResult'];
        $VerifiedBy = $RequestBody['VerifiedBy'];
        $existingRecord = $this->auditLogger->fetchRowById('QcABSampleResultData', 'SampleID', $SampleID);

        $sqlUpdate = "UPDATE QcABSampleResultData SET TankNo='$TankNo', BatchNo='$BatchNo',SampleDetails='$SampleDetails', 
        AnalyzedBy='$AnalyzedBy', CheckedBy='$CheckedBy', ExternalLabID='$ExternalLabID',SamplDt='$SamplDt',
        SampleResult='$SampleResult', VerifiedBy='$VerifiedBy'
        WHERE SampleID='$SampleID'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        // $errorInfo = $stmtUpdate->errorInfo();
        // var_dump($errorInfo);

        if ($stmtUpdate) {
            $updatedRecord = $this->auditLogger->fetchRowById('QcABSampleResultData', 'SampleID', $SampleID);
            $this->auditLogger->logUpdate('QcABSampleResultData', $SampleID, $existingRecord, $updatedRecord, $RequestBody, [
                'ReferenceNo' => $SampleID,
                'Remarks' => 'QC antibiotic sample updated',
            ]);
            $antibioticData = $this->getAntibioticDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Antibiotic Updated Successfully';
            $Data['Data'] = $antibioticData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update Antibiotic';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    // function deleteFsAuditPaymentDetails($RequestBody)
    // {
    //     $AuditID = $RequestBody['AuditID'];

    //     $sqlDelete = "DELETE FROM `GsAuditFHFDetails` WHERE AuditID='$AuditID'";

    //     $stmtUpdate = $this->conn->prepare($sqlDelete);
    //     $stmtUpdate->execute();

    //     if ($stmtUpdate) {
    //         $fsAuditMillData = $this->getFsAuditMillDetails($RequestBody);
    //         $Data['Error'] = null;
    //         $Data['Msg'] = 'FS Mill Audit Deleted Successfully';
    //         $Data['Data'] = $fsAuditMillData['Data'];
    //         return $Data;
    //     } else {
    //         $Data['Error'] = 'Unable to Delete FS Mill Audit';
    //         $Data['Msg'] = $this->Error;
    //         return $Data;
    //     }
    // }

    function getAntibioticDetails($RequestBody)
    {
        $RecordsPerPage = 10;
        $SampleID = $RequestBody['SampleID'];
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($SampleID != "") {
            $Query .= " and SampleID = '$SampleID'";
        }

        if ($FromDate != "" && $ToDate != "") {
            $Query .= " AND QSRD.SamplDt BETWEEN '$FromDate' AND '$ToDate'";
        } else if ($FromDate != "") {
            $Query .= " AND QSRD.SamplDt = '$FromDate'";
        }

        $RecordsPerPage = 10;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', QSRD.TankNo, QSRD.BatchNo, QSRD.SampleDetails, QSRD.AnalyzedBy, QSRD.CheckedBy, QSRD.ExternalLabID, 
            QSRD.SamplDt,QSRD.SampleResult,QSRD.VerifiedBy, QLT.LbuName, QLT.LbuDesignation, QLT1.LbuName, QLT1.LbuDesignation,
            QLT2.LbuName, QLT2.LbuDesignation, QELN.LabName) LIKE '%$SearchKey%'";
        }

        // $DateFilter = "BETWEEN '$FromDate' and '$ToDate'";

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `QcABSampleResultData`";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "QSRD.SampleID,QSRD.TankNo,QSRD.BatchNo,QSRD.SampleDetails,QSRD.AnalyzedBy,QSRD.CheckedBy,QSRD.ExternalLabID,
        QSRD.SamplDt,QSRD.SampleResult,QSRD.VerifiedBy, QELN.LabName,
        CONCAT(QLT.LbuName, ' - ', QLT.LbuDesignation) AS AnalyzedByName,
        CONCAT(QLT1.LbuName, ' - ', QLT1.LbuDesignation) AS CheckedByName, 
        CONCAT(QLT2.LbuName, ' - ', QLT2.LbuDesignation) AS VerifiedByName";

        if ($SampleID != '') {

            $sqlQuery = "SELECT $sqlFields FROM QcABSampleResultData AS QSRD 
            LEFT JOIN QcLabTechnologist AS QLT ON QSRD.AnalyzedBy = QLT.LUID
            LEFT JOIN QcLabTechnologist AS QLT1 ON QSRD.CheckedBy = QLT1.LUID
            LEFT JOIN QcLabTechnologist AS QLT2 ON QSRD.VerifiedBy = QLT2.LUID
            LEFT JOIN QcEiaLabNames AS QELN ON QSRD.ExternalLabID = QELN.LabId
            WHERE SampleID='$SampleID'";
        } else {
  
            $sqlQuery = "SELECT $sqlFields FROM QcABSampleResultData AS QSRD 
            LEFT JOIN QcLabTechnologist AS QLT ON QSRD.AnalyzedBy = QLT.LUID
            LEFT JOIN QcLabTechnologist AS QLT1 ON QSRD.CheckedBy = QLT1.LUID
            LEFT JOIN QcLabTechnologist AS QLT2 ON QSRD.VerifiedBy = QLT2.LUID
            LEFT JOIN QcEiaLabNames AS QELN ON QSRD.ExternalLabID = QELN.LabId
            WHERE 1=1 $Query 
            GROUP BY SampleID
            ORDER BY SampleID " . $Limit;
        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayAntibioticData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayAntibioticList['SNO'] = ++$i;
            $ArrayAntibioticList['SampleID'] = $dataRow['SampleID'];
            $ArrayAntibioticList['TankNo'] = $dataRow['TankNo'];
            $ArrayAntibioticList['BatchNo'] = $dataRow['BatchNo'];
            $ArrayAntibioticList['SampleDetails'] = $dataRow['SampleDetails'];
            $ArrayAntibioticList['AnalyzedBy'] = $dataRow['AnalyzedBy'];
            $ArrayAntibioticList['AnalyzedByName'] = $dataRow['AnalyzedByName'];
            $ArrayAntibioticList['CheckedBy'] = $dataRow['CheckedBy'];
            $ArrayAntibioticList['CheckedByName'] = $dataRow['CheckedByName'];
            $ArrayAntibioticList['ExternalLabID'] = $dataRow['ExternalLabID'];
            $ArrayAntibioticList['LabName'] = $dataRow['LabName'];
            $ArrayAntibioticList['SamplDt'] = $dataRow['SamplDt'];
            $ArrayAntibioticList['SampleResult'] = $dataRow['SampleResult'];
            $ArrayAntibioticList['VerifiedBy'] = $dataRow['VerifiedBy'];
            $ArrayAntibioticList['VerifiedByName'] = $dataRow['VerifiedByName'];

            $ArrayAntibioticData[] =  $ArrayAntibioticList;
        }

        if (count($ArrayAntibioticData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "antibiotic-entry-form1?SampleID=";
        $data['AddLink'] = "antibiotic-entry-form2?SampleID=";
        $data['Data'] = $ArrayAntibioticData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
