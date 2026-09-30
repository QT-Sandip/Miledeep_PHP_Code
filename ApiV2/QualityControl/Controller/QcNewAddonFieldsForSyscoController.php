<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class QcNewAddonFieldsForSyscoController
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
    }

    public function postData($RequestBody)
    {
        $FactoryID = $RequestBody['FactoryID'];
        $ShipmentDate = $RequestBody['ShipmentDate'];
        $RequestSentDate = $RequestBody['RequestSentDate'];
        $SampleSendingDate = $RequestBody['SampleSendingDate'];
        $ReportExpectingDate = $RequestBody['ReportExpectingDate'];
        $SGSRequestDate = $RequestBody['SGSRequestDate'];
        $SimpSentDate = $RequestBody['SimpSentDate'];
        $PoID = $RequestBody['PoID'];
        $Status = $RequestBody['Status'];
        $LotCode = $RequestBody['LotCode'];

        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");
        $PostedUserID = 1;

        $sqlPoSelect = "SELECT COUNT(*) AS TotalCount FROM QcNewAddonFieldsData WHERE PoID = '$PoID'";
        $stmtPoSelect = $this->conn->prepare($sqlPoSelect);
        $stmtPoSelect->execute();
        $dataPoRow = $stmtPoSelect->fetch(PDO::FETCH_ASSOC);
        $RowCount = $dataPoRow['TotalCount'];

        if ($RowCount == "0") {
            $sqlInsert = "INSERT INTO QcNewAddonFieldsData(FactoryID, Status, ShipmentDate, RequestSentDate, 
            SampleSendingDate, ReportExpectingDate,SGSRequestDate, PoID, PostedDate, PostedUserID, LotCode, SimpSentDate) 
            VALUES ('$FactoryID', '$Status', '$ShipmentDate', '$RequestSentDate', 
            '$SampleSendingDate', '$ReportExpectingDate', '$SGSRequestDate','$PoID', '$PostedDate', '$PostedUserID', '$LotCode', '$SimpSentDate')";
            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                $this->Error = null;
                $this->Msg = 'Data Posted Successfully';
                $lastInsertId = $this->conn->lastInsertId();
                $createdRecord = $this->auditLogger->fetchRowById('QcNewAddonFieldsData', 'DataID', $lastInsertId);
                $this->auditLogger->logInsert('QcNewAddonFieldsData', $lastInsertId, $createdRecord, $RequestBody, [
                    'ReferenceNo' => $PoID,
                    'Remarks' => 'QC Sysco add-on fields created',
                ]);
            } else {
                $this->Error = "Unable to Post Data";
                $this->Msg = "Unable to Post Data";
            }
        } else {
            $existingRecord = $this->auditLogger->fetchOne("SELECT * FROM QcNewAddonFieldsData WHERE PoID = ?", [$PoID]);
            $sqlUpdate = "UPDATE QcNewAddonFieldsData SET FactoryID='$FactoryID',Status='$Status',
            ShipmentDate='$ShipmentDate',RequestSentDate='$RequestSentDate',SampleSendingDate='$SampleSendingDate',
            ReportExpectingDate='$ReportExpectingDate',SGSRequestDate='$SGSRequestDate',PostedDate='$PostedDate' ,PostedUserID='$PostedUserID' 
            ,LotCode='$LotCode',SimpSentDate='$SimpSentDate'  WHERE PoID='$PoID'";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            if ($stmtUpdate) {
                $this->Error = null;
                $this->Msg = 'Data Updated Successfully';
                $updatedRecord = $this->auditLogger->fetchOne("SELECT * FROM QcNewAddonFieldsData WHERE PoID = ?", [$PoID]);
                $entityId = isset($updatedRecord['DataID']) ? $updatedRecord['DataID'] : $PoID;
                $this->auditLogger->logUpdate('QcNewAddonFieldsData', $entityId, $existingRecord, $updatedRecord, $RequestBody, [
                    'ReferenceNo' => $PoID,
                    'Remarks' => 'QC Sysco add-on fields updated',
                ]);
            } else {
                $this->Error = "Unable to Update Data";
                $this->Msg = "Unable to Update Data";
            }
        }

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;

        return $Data;
    }

    function getDetails($RequestBody)
    {
        $PoID = $RequestBody['PoID'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('',DataID, FactoryID, Status, ShipmentDate, RequestSentDate, SampleSendingDate,SimpSentDate, 
            ReportExpectingDate, PoID, PostedDate) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(*) AS TotalCount FROM QcNewAddonFieldsData 
        WHERE 1=1 $Query ";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataCount['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "DataID, FactoryID, Status, ShipmentDate, RequestSentDate, SampleSendingDate, ReportExpectingDate, PoID, SGSRequestDate,LotCode,SimpSentDate";

        if ($PoID) {
            $sqlSelect = "SELECT $sqlFields FROM QcNewAddonFieldsData
            WHERE PoID = '$PoID'";
        } else {
            $sqlSelect = "SELECT $sqlFields FROM QcNewAddonFieldsData 
            WHERE 1=1 $Query " . $Limit;
        }
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        // $erroInfo = $stmtSelect->errorInfo();
        // var_dump($erroInfo);

        $ArrayData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            // $sqlFields = "DataID, FactoryID, Status, ShipmentDate, RequestSentDate, SampleSendingDate, ReportExpectingDate, PoID, SGSRequestDate";
            $ArrayList['SNO'] = ++$i;
            $ArrayList['DataID'] = $dataRow['DataID'];
            $ArrayList['PoID'] = $dataRow['PoID'];
            $ArrayList['FactoryID'] = $dataRow['FactoryID'];
            $ArrayList['ShipmentDate'] = $dataRow['ShipmentDate'];
            $ArrayList['Status'] = $dataRow['Status'];
            $ArrayList['RequestSentDate'] = $dataRow['RequestSentDate'];
            $ArrayList['SampleSendingDate'] = $dataRow['SampleSendingDate'];
            $ArrayList['ReportExpectingDate'] = $dataRow['ReportExpectingDate'];
            $ArrayList['SGSRequestDate'] = $dataRow['SGSRequestDate'];
            $ArrayList['SimpSentDate'] = $dataRow['SimpSentDate'];
            $ArrayList['LotCode'] = $dataRow['LotCode'];

            $ArrayData[] = $ArrayList;
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['TotalCount'] = $TotalCount;
        $Data['PageCount'] = $PageCount;
        $Data['OptionsData'] = $ArrayData;

        return $Data;
    }
}
