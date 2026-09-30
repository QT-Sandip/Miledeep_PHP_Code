<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class HatcheryAuditController
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

    public function postHatcheryAuditDetails($RequestBody)
    {
        $HatcheryID = $RequestBody['HatcheryID'];
        $PlantID = $RequestBody['PlantID'];
        $HatcheryName = $RequestBody['HatcheryName'];
        $HatcheryLocation = $RequestBody['HatcheryLocation'];
        $CertificationBody = $RequestBody['CertificationBody'];
        $RegistrationNumber = $RequestBody['RegistrationNumber'];
        $BAPNumber = $RequestBody['BAPNumber'];
        $ProductionPerCycle = $RequestBody['ProductionPerCycle'];
        $AuditDate = $RequestBody['AuditDate'];
        $IssuedOn = $RequestBody['IssuedOn'];
        $ValidUpto = $RequestBody['ValidUpto'];
        $RenewalDate = $RequestBody['RenewalDate'];
        $CheckedBy = $RequestBody['CheckedBy'];
        $VerifiedBy = $RequestBody['VerifiedBy'];
        $Status = $RequestBody['Status'];
        $AuditStatus = $RequestBody['AuditStatus'];
        $Remarks = $RequestBody['Remarks'];
        $UploadFile = $RequestBody['UploadFile'];
        date_default_timezone_set("Asia/Kolkata");
        $PostedDatetime = date("Y-m-d H:i:s");
        $existingAudit = null;
        if ($HatcheryID != '') {
            $existingAudit = $this->auditLogger->fetchRowById('QcHatcheryAuditDetails', 'HatcheryID', $HatcheryID);
        }

        if ($HatcheryID == '') {

            $sqlInsert = "INSERT INTO QcHatcheryAuditDetails(PlantID, HatcheryName, HatcheryLocation,  
            CertificationBody, RegistrationNumber, BAPNumber, ProductionPerCycle, AuditDate, IssuedOn, ValidUpto, 
            RenewalDate, Status, CheckedBy, VerifiedBy,UploadFile,AuditStatus,Remarks, PostedDatetime) 
            VALUES ('$PlantID', '$HatcheryName', '$HatcheryLocation',  
            '$CertificationBody', '$RegistrationNumber', '$BAPNumber', '$ProductionPerCycle', '$AuditDate', '$IssuedOn', '$ValidUpto', 
            '$RenewalDate', '$Status', '$CheckedBy', '$VerifiedBy','$UploadFile', '$AuditStatus','$Remarks', '$PostedDatetime')";
            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                $this->Error = null;
                $this->Msg = 'Hatchery Audit Posted Successfully';
                $LastInsertID = $this->conn->lastInsertId();
                $createdAudit = $this->auditLogger->fetchRowById('QcHatcheryAuditDetails', 'HatcheryID', $LastInsertID);
                $this->auditLogger->logInsert('QcHatcheryAuditDetails', $LastInsertID, $createdAudit, $RequestBody, [
                    'ReferenceNo' => $LastInsertID,
                    'Remarks' => 'QC hatchery audit created',
                ]);
            } else {
                $this->Error = "Unable to Post Hatchery Audit Data";
                $this->Msg = "Unable to Post Hatchery Audit Data";
            }
        } else {
            if ($UploadFile != '') {
                $UpdateQuery = ",UploadFile='$UploadFile'";
            } else {
                $UpdateQuery = "";
            }
            $sqlUpdate = "UPDATE QcHatcheryAuditDetails SET PlantID='$PlantID',HatcheryName='$HatcheryName',HatcheryLocation='$HatcheryLocation',
            CertificationBody='$CertificationBody',RegistrationNumber='$RegistrationNumber',
            BAPNumber='$BAPNumber',ProductionPerCycle='$ProductionPerCycle',AuditDate='$AuditDate',IssuedOn='$IssuedOn',
            ValidUpto='$ValidUpto',RenewalDate='$RenewalDate',Status='$Status',CheckedBy='$CheckedBy',
            VerifiedBy='$VerifiedBy', AuditStatus='$AuditStatus',Remarks='$Remarks',PostedDatetime='$PostedDatetime' $UpdateQuery WHERE HatcheryID='$HatcheryID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            if ($stmtUpdate) {
                $this->Error = null;
                $this->Msg = 'Hatchery Audit Updated Successfully';
                $LastInsertID = $HatcheryID;
                $updatedAudit = $this->auditLogger->fetchRowById('QcHatcheryAuditDetails', 'HatcheryID', $HatcheryID);
                $this->auditLogger->logUpdate('QcHatcheryAuditDetails', $HatcheryID, $existingAudit, $updatedAudit, $RequestBody, [
                    'ReferenceNo' => $HatcheryID,
                    'Remarks' => 'QC hatchery audit updated',
                ]);
            } else {
                $this->Error = "Unable to Update Hatchery Audit Data";
                $this->Msg = "Unable to Update Hatchery Audit Data";
            }
        }

        $PaymentData = $this->getHatcheryAuditPaymentData($HatcheryID);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['LastInserted'] = $LastInsertID;
        $Data['PaymentData'] = $PaymentData['PaymentData'];

        return $Data;
    }

    function getHatcheryAuditDetails($RequestBody)
    {

        $HatcheryID = $RequestBody['HatcheryID'];
        $PlantID = $RequestBody['PlantID'];
        $Year = $RequestBody['Year'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $StartDate = $Year . '-04-01';
        $EndDate = ($Year + 1) . '-03-31';

        $Query = '';
        if ($PlantID != '') {
            $Query .= " AND QHAD.PlantID = '$PlantID'";
        }

        $DateFilters = " BETWEEN '$StartDate' AND '$EndDate'";

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('', HatcheryID, QHAD.PlantID, HatcheryName, HatcheryLocation, CertificationBody, 
            RegistrationNumber, BAPNumber, ProductionPerCycle, AuditDate, IssuedOn, ValidUpto, RenewalDate, QHAD.Status, 
            CheckedBy, VerifiedBy,UploadFile,RPS.StationName, QACM.CategoryName, QACOM.OptionName, AuditStatus, QHAD.Remarks,
            QLT.LbuName, QLT2.LbuName, QACM2.CategoryName) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(*) AS TotalCount FROM QcHatcheryAuditDetails AS QHAD 
        LEFT JOIN QcAuditDropdownMasters AS QADM ON QHAD.HatcheryName = QADM.OptionID
        LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QHAD.CertificationBody = QACBM.CertificationBodyID
        LEFT JOIN QcAuditHatcheryMasters AS QAHM ON QHAD.HatcheryLocation = QAHM.HatcheryNameID
        LEFT JOIN RmProcessingStations AS RPS ON QHAD.PlantID = RPS.ProcessingStationID
        LEFT JOIN QcLabTechnologist AS QLT ON QHAD.CheckedBy = QLT.LUID
        LEFT JOIN QcLabTechnologist AS QLT2 ON QHAD.VerifiedBy = QLT2.LUID
        WHERE IssuedOn $DateFilters $Query ";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataCount['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);


        $sqlFields = "HatcheryID, QHAD.PlantID, QHAD.HatcheryName, QHAD.HatcheryLocation, CertificationBody, 
        RegistrationNumber, BAPNumber, ProductionPerCycle, AuditDate, IssuedOn, ValidUpto, RenewalDate, QHAD.Status, 
        CheckedBy, VerifiedBy,UploadFile,RPS.StationName, QADM.OptionName AS Hatchery, QAHM.HatcheryLocation as HatcheryLocationName, AuditStatus, QHAD.Remarks,
        QLT.LbuName AS CheckedByName, QLT2.LbuName AS VerifiedByName, QACBM.CertificationBodyName";

        if ($HatcheryID != '') {
            $sqlSelect = "SELECT $sqlFields FROM QcHatcheryAuditDetails AS QHAD 
            LEFT JOIN QcAuditDropdownMasters AS QADM ON QHAD.HatcheryName = QADM.OptionID
            LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QHAD.CertificationBody = QACBM.CertificationBodyID
            LEFT JOIN QcAuditHatcheryMasters AS QAHM ON QHAD.HatcheryLocation = QAHM.HatcheryNameID
            LEFT JOIN RmProcessingStations AS RPS ON QHAD.PlantID = RPS.ProcessingStationID
            LEFT JOIN QcLabTechnologist AS QLT ON QHAD.CheckedBy = QLT.LUID
            LEFT JOIN QcLabTechnologist AS QLT2 ON QHAD.VerifiedBy = QLT2.LUID
            WHERE HatcheryID = '$HatcheryID'";
        } else {
            $sqlSelect = "SELECT $sqlFields FROM QcHatcheryAuditDetails AS QHAD 
            LEFT JOIN QcAuditDropdownMasters AS QADM ON QHAD.HatcheryName = QADM.OptionID
            LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QHAD.CertificationBody = QACBM.CertificationBodyID
            LEFT JOIN QcAuditHatcheryMasters AS QAHM ON QHAD.HatcheryLocation = QAHM.HatcheryNameID
            LEFT JOIN RmProcessingStations AS RPS ON QHAD.PlantID = RPS.ProcessingStationID
            LEFT JOIN QcLabTechnologist AS QLT ON QHAD.CheckedBy = QLT.LUID
            LEFT JOIN QcLabTechnologist AS QLT2 ON QHAD.VerifiedBy = QLT2.LUID
            WHERE AuditDate $DateFilters $Query " . $Limit;
        }

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayAuditData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $CurrentDate = new DateTime(date('Y-m-d'));
            $PaymentStatus = $this->getPaymentStatus($dataRow['HatcheryID']);
            $decodedUploadFile = json_decode($dataRow['UploadFile'], true);
            
            $baseURL = 'https://staging-server.miledeepworks.in/v3/QualityControl/';
            $DownloadableURLs = [];
            foreach ($decodedUploadFile as $filePath) {
                $cleanedFilePath = str_replace('../', '', $filePath);
                $DownloadableURLs[] = $baseURL . $cleanedFilePath;
            }

            $ArrayAuditList['SNO'] = ++$i;
            $ArrayAuditList['HatcheryID'] = $dataRow['HatcheryID'];
            $ArrayAuditList['PlantID'] = $dataRow['PlantID'];
            $ArrayAuditList['StationName'] = $dataRow['StationName'];
            $ArrayAuditList['HatcheryName'] = $dataRow['HatcheryName'];
            $ArrayAuditList['Hatchery'] = $dataRow['Hatchery'];
            $ArrayAuditList['HatcheryLocation'] = $dataRow['HatcheryLocation'];
            $ArrayAuditList['HatcheryLocationName'] = $dataRow['HatcheryLocationName'];
            $ArrayAuditList['CertificationBody'] = $dataRow['CertificationBody'];
            $ArrayAuditList['CertificationBodyName'] = $dataRow['CertificationBodyName'];
            $ArrayAuditList['RegistrationNumber'] = $dataRow['RegistrationNumber'];
            $ArrayAuditList['BAPNumber'] = $dataRow['BAPNumber'];
            $ArrayAuditList['ProductionPerCycle'] = $dataRow['ProductionPerCycle'];
            $ArrayAuditList['AuditDate'] = $dataRow['AuditDate'];
            $ArrayAuditList['IssuedOn'] = $dataRow['IssuedOn'];
            $ArrayAuditList['ValidUpto'] = $dataRow['ValidUpto'];
            $ArrayAuditList['RenewalDate'] = $dataRow['RenewalDate'];
            $ArrayAuditList['Status'] = $dataRow['Status'];
            $ArrayAuditList['CheckedBy'] = $dataRow['CheckedBy'];
            $ArrayAuditList['CheckedByName'] = $dataRow['CheckedByName'];
            $ArrayAuditList['VerifiedBy'] = $dataRow['VerifiedBy'];
            $ArrayAuditList['VerifiedByName'] = $dataRow['VerifiedByName'];
            $ArrayAuditList['UploadFile'] = $DownloadableURLs;
            $ArrayAuditList['Remarks'] = $dataRow['Remarks'];
            $ArrayAuditList['AuditStatus'] = $dataRow['AuditStatus'];

            $ArrayAuditList['EditURL'] = 'hatchery-audit-create?HatcheryID=' . $dataRow['HatcheryID'];
            $ArrayAuditList['ConfirmURL'] = 'hatchery-audit-create?HatcheryID=' . $dataRow['HatcheryID'] . '&ConfirmPayment=1';

            $Status = $dataRow['Status'];
            $AuditStatus = $dataRow['AuditStatus'];
            $RenewalDate = new DateTime($dataRow['RenewalDate']);
            $interval = $CurrentDate->diff($RenewalDate);

            $daysDifference = $interval->days;

            if ($RenewalDate <= $CurrentDate && $Status == "1") {
                $DisplayColor = 'red';
                $TextColor = 'white';
                $StatusMessage = 'Active';
            } else if ($daysDifference <= 90 && $Status == "1") {
                $DisplayColor = 'yellow';
                $StatusMessage = 'Active';
                $TextColor = 'black';
            } else if ($daysDifference > 90 && $Status == "1") {
                $DisplayColor = 'green';
                $StatusMessage = 'Active';
                $TextColor = 'white';
            } else if ($Status == "0") {
                $DisplayColor = 'gray';
                $StatusMessage = 'In-Active';
                $TextColor = 'black';
            }

            if ($AuditStatus == 1) {
                $AuditStatusName = "Fressh Audit";
            } else if ($AuditStatus == 2) {
                $AuditStatusName = "Renewal Audit";
            } else if ($AuditStatus == 3) {
                $AuditStatusName = "Re Certification";
            } else {
                $AuditStatusName = "";
            }

            $TotalPaymentIDs = $PaymentStatus['TotalPaymentIDs'];
            $PaymentStatusCount = $PaymentStatus['PaymentStatusCount'];

            if ($TotalPaymentIDs == 0) {
                $PaymentStatusMessage = "-";
                $PaymentStatusColor = "";
                $PaymentStatusText = "black";
            } else if ($PaymentStatusCount == 0) {
                $PaymentStatusMessage = "Done";
                $PaymentStatusColor = "green";
                $PaymentStatusText = "white";
            } else if ($PaymentStatusCount != 0) {
                $PaymentStatusMessage = "Pending";
                $PaymentStatusColor = "red";
                $PaymentStatusText = "white";
            }

            $ArrayAuditList['DisplayColor'] = $DisplayColor;
            $ArrayAuditList['TextColor'] = $TextColor;
            $ArrayAuditList['StatusMessage'] = $StatusMessage;
            $ArrayAuditList['AuditStatusName'] = $AuditStatusName;
            $ArrayAuditList['PaymentStatusMessage'] = $PaymentStatusMessage;
            $ArrayAuditList['PaymentStatusColor'] = $PaymentStatusColor;
            $ArrayAuditList['PaymentStatusText'] = $PaymentStatusText;

            $ArrayAuditData[] = $ArrayAuditList;
        }

        $PaymentData = $this->getHatcheryAuditPaymentData($HatcheryID);
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['PageCount'] = $PageCount;
        $data['Data'] = $ArrayAuditData;
        $data['PaymentData'] = $PaymentData['PaymentData'];

        return $data;
    }


    function getHatcheryAuditPaymentData($HatcheryID)
    {

        $sqlPaymentFields = "HatcheryPaymentID, HatcheryID, PurposeOfInvoice, PaymentType, InvoiceNumber, InvoiceDate, 
        InvoiceValue, InvoiceDueDate,Others, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime, OptionName";

        $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcHatcheryAuditPaymentDetails AS QHAPD
        LEFT JOIN QcAuditDropdownMasters AS QADM ON QHAPD.PaymentType = QADM.OptionID
        WHERE HatcheryID = '$HatcheryID'";
        $stmtPaymentSelect = $this->conn->prepare($sqlPaymentSelect);
        $stmtPaymentSelect->execute();
        // $errorInfo = $stmtPaymentSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayPaymentData = [];
        $i = 0;
        while ($dataPaymentRow = $stmtPaymentSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayPaymentList['SNO'] = ++$i;
            $ArrayPaymentList['HatcheryPaymentID'] = $dataPaymentRow['HatcheryPaymentID'];
            $ArrayPaymentList['HatcheryID'] = $dataPaymentRow['HatcheryID'];
            $ArrayPaymentList['PurposeOfInvoice'] = $dataPaymentRow['PurposeOfInvoice'];
            $ArrayPaymentList['PaymentType'] = $dataPaymentRow['PaymentType'];
            $ArrayPaymentList['OptionName'] = $dataPaymentRow['OptionName'];
            $ArrayPaymentList['InvoiceNumber'] = $dataPaymentRow['InvoiceNumber'];
            $ArrayPaymentList['InvoiceDate'] = $dataPaymentRow['InvoiceDate'];
            $ArrayPaymentList['InvoiceValue'] = $dataPaymentRow['InvoiceValue'];
            $ArrayPaymentList['InvoiceDueDate'] = $dataPaymentRow['InvoiceDueDate'];
            $ArrayPaymentList['Others'] = $dataPaymentRow['Others'];
            $ArrayPaymentList['TotalAmount'] = $dataPaymentRow['TotalAmount'];
            $ArrayPaymentList['PaymentDate'] = $dataPaymentRow['PaymentDate'];
            $ArrayPaymentList['PaymentRemarks'] = $dataPaymentRow['PaymentRemarks'];
            $ArrayPaymentList['PostedDatetime'] = $dataPaymentRow['PostedDatetime'];

            $ArrayPaymentData[] = $ArrayPaymentList;
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['PaymentData'] = $ArrayPaymentData;

        return $Data;
    }

    function getPaymentStatus($HatcheryID)
    {
        $sqlPaymentIds = "SELECT COUNT(*) AS TotalPaymentIDs FROM QcHatcheryAuditPaymentDetails
        WHERE HatcheryID = '$HatcheryID'";
        $stmtPaymentIDs = $this->conn->prepare($sqlPaymentIds);
        $stmtPaymentIDs->execute();
        $rowPaymentIDs = $stmtPaymentIDs->fetch(PDO::FETCH_ASSOC);
        $TotalPaymentIDs = $rowPaymentIDs['TotalPaymentIDs'];

        $sqlPaymentStatus = "SELECT COUNT(*) AS TotalCount FROM QcHatcheryAuditPaymentDetails 
        WHERE HatcheryID = '$HatcheryID' AND PaymentDate = ''";
        $stmtPaymentStatus = $this->conn->prepare($sqlPaymentStatus);
        $stmtPaymentStatus->execute();
        $rowPaymentStatus = $stmtPaymentStatus->fetch(PDO::FETCH_ASSOC);
        $PaymentStatusCount = $rowPaymentStatus['TotalCount'];
        
        $Data['TotalPaymentIDs'] = $TotalPaymentIDs;
        $Data['PaymentStatusCount'] = $PaymentStatusCount;
        return $Data;
    }
}
