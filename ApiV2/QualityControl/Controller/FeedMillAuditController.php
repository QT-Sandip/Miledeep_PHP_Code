<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class FeedMillAuditController
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

    public function postFeedMillAuditDetails($RequestBody)
    {
        $FeedMillAuditID = $RequestBody['FeedMillAuditID'];
        $PlantID = $RequestBody['PlantID'];
        $AuditNameID = $RequestBody['AuditNameID'];
        $FarmerNameID = $RequestBody['FarmerNameID'];
        $CertificationBodyID = $RequestBody['CertificationBodyID'];
        $RegistrationNo = $RequestBody['RegistrationNo'];
        $CertificationNumber = $RequestBody['CertificationNumber'];
        $BAPNumber = $RequestBody['BAPNumber'];
        $AuditDate = $RequestBody['AuditDate'];
        $IssuedOn = $RequestBody['IssuedOn'];
        $ValidityUpto = $RequestBody['ValidityUpto'];
        $RenewalDate = $RequestBody['RenewalDate'];
        $CheckedBy = $RequestBody['CheckedBy'];
        $VerifiedBy = $RequestBody['VerifiedBy'];
        $UploadFile = $RequestBody['UploadFile'];
        $Status = $RequestBody['Status'];
        $AuditStatus = $RequestBody['AuditStatus'];
        $Remarks = $RequestBody['Remarks'];
        date_default_timezone_set("Asia/Kolkata");
        $PostedDatetime = date("Y-m-d H:i:s");
        $existingAudit = null;
        if ($FeedMillAuditID != '') {
            $existingAudit = $this->auditLogger->fetchRowById('QcFeedMillAuditDetails', 'FeedMillAuditID', $FeedMillAuditID);
        }

        if ($FeedMillAuditID == '') {

            $sqlInsert = "INSERT INTO QcFeedMillAuditDetails(PlantID, AuditNameID, FarmerNameID,  
            CertificationBodyID, RegistrationNo, CertificationNumber, BAPNumber, AuditDate, IssuedOn, ValidityUpto, 
            RenewalDate, Status, CheckedBy, VerifiedBy,UploadFile, AuditStatus,Remarks, PostedDatetime) 
            VALUES ('$PlantID', '$AuditNameID', '$FarmerNameID',  '$CertificationBodyID', '$RegistrationNo', 
            '$CertificationNumber', '$BAPNumber', '$AuditDate', '$IssuedOn', '$ValidityUpto', '$RenewalDate', '$Status', 
            '$CheckedBy', '$VerifiedBy','$UploadFile', '$AuditStatus','$Remarks', '$PostedDatetime')";
            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                $this->Error = null;
                $this->Msg = 'Feed Mill Audit Posted Successfully';
                $LastInsertID = $this->conn->lastInsertId();
                $createdAudit = $this->auditLogger->fetchRowById('QcFeedMillAuditDetails', 'FeedMillAuditID', $LastInsertID);
                $this->auditLogger->logInsert('QcFeedMillAuditDetails', $LastInsertID, $createdAudit, $RequestBody, [
                    'ReferenceNo' => $LastInsertID,
                    'Remarks' => 'QC feed mill audit created',
                ]);
            } else {
                $this->Error = "Unable to Post Feed Mill Audit Data";
                $this->Msg = "Unable to Post Feed Mill Audit Data";
            }
        } else {
            if ($UploadFile != '') {
                $UpdateQuery = ",UploadFile='$UploadFile'";
            } else {
                $UpdateQuery = "";
            }
            $sqlUpdate = "UPDATE QcFeedMillAuditDetails SET PlantID='$PlantID',AuditNameID='$AuditNameID',FarmerNameID='$FarmerNameID',
            CertificationBodyID='$CertificationBodyID',RegistrationNo='$RegistrationNo',CertificationNumber='$CertificationNumber',
            BAPNumber='$BAPNumber',AuditDate='$AuditDate',IssuedOn='$IssuedOn', ValidityUpto='$ValidityUpto',RenewalDate='$RenewalDate',
            Status='$Status',CheckedBy='$CheckedBy',VerifiedBy='$VerifiedBy',AuditStatus='$AuditStatus',Remarks='$Remarks',
            PostedDatetime='$PostedDatetime' $UpdateQuery WHERE FeedMillAuditID='$FeedMillAuditID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            if ($stmtUpdate) {
                $this->Error = null;
                $this->Msg = 'Feed Mill Audit Updated Successfully';
                $LastInsertID = $FeedMillAuditID;
                $updatedAudit = $this->auditLogger->fetchRowById('QcFeedMillAuditDetails', 'FeedMillAuditID', $FeedMillAuditID);
                $this->auditLogger->logUpdate('QcFeedMillAuditDetails', $FeedMillAuditID, $existingAudit, $updatedAudit, $RequestBody, [
                    'ReferenceNo' => $FeedMillAuditID,
                    'Remarks' => 'QC feed mill audit updated',
                ]);
            } else {
                $this->Error = "Unable to Update Feed Mill Audit Data";
                $this->Msg = "Unable to Update Feed Mill Audit Data";
            }
        }

        $PaymentData = $this->getFeedMillAuditPaymentData($FeedMillAuditID);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['LastInserted'] = $LastInsertID;
        $Data['PaymentData'] = $PaymentData['PaymentData'];

        return $Data;
    }

    function getFeedMillAuditDetails($RequestBody)
    {
        $FeedMillAuditID = $RequestBody['FeedMillAuditID'];
        $PlantID = $RequestBody['PlantID'];
        $Year = $RequestBody['Year'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $StartDate = $Year . '-04-01';
        $EndDate = ($Year + 1) . '-03-31';

        $Query = '';
        if ($PlantID != '') {
            $Query .= " AND QFAD.PlantID = '$PlantID'";
        }

        $DateFilters = " BETWEEN '$StartDate' AND '$EndDate'";

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('', FeedMillAuditID, QFAD.PlantID, AuditNameID, FarmerNameID, CertificationBodyID, 
            RegistrationNo, CertificationNumber, BAPNumber, AuditDate, IssuedOn, ValidityUpto, RenewalDate, QFAD.Status, 
            CheckedBy, VerifiedBy,UploadFile, RPS.StationName, QACM.CategoryName, QACOM.OptionName, AuditStatus, QFAD.Remarks,
            QLT.LbuName, QLT2.LbuName, QACM2.CategoryName) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(*) AS TotalCount FROM QcFeedMillAuditDetails AS QFAD 
        LEFT JOIN QcAuditCategoriesMaster AS QACM ON QFAD.AuditNameID = QACM.AuditID
        LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QFAD.CertificationBodyID = QACBM.CertificationBodyID
        LEFT JOIN QcAuditFarmMasters AS QAFM ON QFAD.FarmerNameID = QAFM.FarmID
        LEFT JOIN RmProcessingStations AS RPS ON QFAD.PlantID = RPS.ProcessingStationID
        LEFT JOIN QcLabTechnologist AS QLT ON QFAD.CheckedBy = QLT.LUID
        LEFT JOIN QcLabTechnologist AS QLT2 ON QFAD.VerifiedBy = QLT2.LUID
        WHERE AuditDate $DateFilters $Query";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataCount['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);


        $sqlFields = "FeedMillAuditID, QFAD.PlantID, QFAD.AuditNameID, FarmerNameID, QFAD.CertificationBodyID, 
        RegistrationNo, CertificationNumber, BAPNumber, AuditDate, IssuedOn, ValidityUpto, RenewalDate, QFAD.Status, 
        CheckedBy, VerifiedBy,UploadFile, RPS.StationName, QACM.CategoryName AS CategoryName, AuditStatus, QFAD.Remarks,
        QLT.LbuName AS CheckedByName, QLT2.LbuName AS VerifiedByName, QACBM.CertificationBodyName, QAFM.FarmerName";

        if ($FeedMillAuditID != '') {
            $sqlSelect = "SELECT $sqlFields FROM QcFeedMillAuditDetails AS QFAD 
            LEFT JOIN QcAuditCategoriesMaster AS QACM ON QFAD.AuditNameID = QACM.AuditID
            LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QFAD.CertificationBodyID = QACBM.CertificationBodyID
            LEFT JOIN QcAuditFarmMasters AS QAFM ON QFAD.FarmerNameID = QAFM.FarmID
            LEFT JOIN RmProcessingStations AS RPS ON QFAD.PlantID = RPS.ProcessingStationID
            LEFT JOIN QcLabTechnologist AS QLT ON QFAD.CheckedBy = QLT.LUID
            LEFT JOIN QcLabTechnologist AS QLT2 ON QFAD.VerifiedBy = QLT2.LUID
            WHERE FeedMillAuditID = '$FeedMillAuditID'";
        } else {
            $sqlSelect = "SELECT $sqlFields FROM QcFeedMillAuditDetails AS QFAD 
            LEFT JOIN QcAuditCategoriesMaster AS QACM ON QFAD.AuditNameID = QACM.AuditID
            LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QFAD.CertificationBodyID = QACBM.CertificationBodyID
            LEFT JOIN QcAuditFarmMasters AS QAFM ON QFAD.FarmerNameID = QAFM.FarmID
            LEFT JOIN RmProcessingStations AS RPS ON QFAD.PlantID = RPS.ProcessingStationID
            LEFT JOIN QcLabTechnologist AS QLT ON QFAD.CheckedBy = QLT.LUID
            LEFT JOIN QcLabTechnologist AS QLT2 ON QFAD.VerifiedBy = QLT2.LUID
            WHERE AuditDate $DateFilters $Query " . $Limit;
        }

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ArrayAuditData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $CurrentDate = new DateTime(date('Y-m-d'));
            $PaymentStatus = $this->getPaymentStatus($dataRow['FeedMillAuditID']);
            $decodedUploadFile = json_decode($dataRow['UploadFile'], true);

            $baseURL = 'https://staging-server.miledeepworks.in/v3/QualityControl/';
            $DownloadableURLs = [];
            foreach ($decodedUploadFile as $filePath) {
                $cleanedFilePath = str_replace('../', '', $filePath);
                $DownloadableURLs[] = $baseURL . $cleanedFilePath;
            }

            $ArrayAuditList['SNO'] = ++$i;
            $ArrayAuditList['FeedMillAuditID'] = $dataRow['FeedMillAuditID'];
            $ArrayAuditList['PlantID'] = $dataRow['PlantID'];
            $ArrayAuditList['StationName'] = $dataRow['StationName'];
            $ArrayAuditList['AuditNameID'] = $dataRow['AuditNameID'];
            $ArrayAuditList['CategoryName'] = $dataRow['CategoryName'];
            $ArrayAuditList['FarmerNameID'] = $dataRow['FarmerNameID'];
            $ArrayAuditList['FarmerName'] = $dataRow['FarmerName'];
            $ArrayAuditList['CertificationBodyID'] = $dataRow['CertificationBodyID'];
            $ArrayAuditList['CertificationBodyName'] = $dataRow['CertificationBodyName'];
            $ArrayAuditList['RegistrationNo'] = $dataRow['RegistrationNo'];
            $ArrayAuditList['CertificationNumber'] = $dataRow['CertificationNumber'];
            $ArrayAuditList['BAPNumber'] = $dataRow['BAPNumber'];
            $ArrayAuditList['AuditDate'] = $dataRow['AuditDate'];
            $ArrayAuditList['IssuedOn'] = $dataRow['IssuedOn'];
            $ArrayAuditList['ValidityUpto'] = $dataRow['ValidityUpto'];
            $ArrayAuditList['RenewalDate'] = $dataRow['RenewalDate'];
            $ArrayAuditList['Status'] = $dataRow['Status'];
            $ArrayAuditList['CheckedBy'] = $dataRow['CheckedBy'];
            $ArrayAuditList['CheckedByName'] = $dataRow['CheckedByName'];
            $ArrayAuditList['VerifiedBy'] = $dataRow['VerifiedBy'];
            $ArrayAuditList['VerifiedByName'] = $dataRow['VerifiedByName'];
            $ArrayAuditList['UploadFile'] = $DownloadableURLs;
            $ArrayAuditList['Remarks'] = $dataRow['Remarks'];
            $ArrayAuditList['AuditStatus'] = $dataRow['AuditStatus'];

            $ArrayAuditList['EditURL'] = 'feed-mill-audit-create?FeedMillAuditID=' . $dataRow['FeedMillAuditID'];
            $ArrayAuditList['ConfirmURL'] = 'feed-mill-audit-create?FeedMillAuditID=' . $dataRow['FeedMillAuditID'] . '&ConfirmPayment=1';

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

        $PaymentData = $this->getFeedMillAuditPaymentData($FeedMillAuditID);
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['PageCount'] = $PageCount;
        $data['Data'] = $ArrayAuditData;
        $data['PaymentData'] = $PaymentData['PaymentData'];

        return $data;
    }


    function getFeedMillAuditPaymentData($FeedMillAuditID)
    {

        $sqlPaymentFields = "FeedMillAuditPaymentID, FeedMillAuditID, PurposeOfInvoice, PaymentType, InvoiceNo, InvoiceDate, 
        InvoiceValue, InvoiceDueDate,Others, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime, OptionName";

        $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcFeedMillAuditPaymentDetails AS QFAPD
        LEFT JOIN QcAuditDropdownMasters AS QADM ON QFAPD.PaymentType = QADM.OptionID
        WHERE FeedMillAuditID = '$FeedMillAuditID'";
        $stmtPaymentSelect = $this->conn->prepare($sqlPaymentSelect);
        $stmtPaymentSelect->execute();

        $ArrayPaymentData = [];
        $i = 0;
        while ($dataPaymentRow = $stmtPaymentSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayPaymentList['SNO'] = ++$i;
            $ArrayPaymentList['FeedMillAuditPaymentID'] = $dataPaymentRow['FeedMillAuditPaymentID'];
            $ArrayPaymentList['FeedMillAuditID'] = $dataPaymentRow['FeedMillAuditID'];
            $ArrayPaymentList['PurposeOfInvoice'] = $dataPaymentRow['PurposeOfInvoice'];
            $ArrayPaymentList['PaymentType'] = $dataPaymentRow['PaymentType'];
            $ArrayPaymentList['OptionName'] = $dataPaymentRow['OptionName'];
            $ArrayPaymentList['InvoiceNo'] = $dataPaymentRow['InvoiceNo'];
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

    function getPaymentStatus($FeedMillAuditID)
    {
        $sqlPaymentIds = "SELECT COUNT(*) AS TotalPaymentIDs FROM QcFeedMillAuditPaymentDetails
        WHERE FeedMillAuditID = '$FeedMillAuditID'";
        $stmtPaymentIDs = $this->conn->prepare($sqlPaymentIds);
        $stmtPaymentIDs->execute();
        $rowPaymentIDs = $stmtPaymentIDs->fetch(PDO::FETCH_ASSOC);
        $TotalPaymentIDs = $rowPaymentIDs['TotalPaymentIDs'];

        $sqlPaymentStatus = "SELECT COUNT(*) AS TotalCount FROM QcFeedMillAuditPaymentDetails 
        WHERE FeedMillAuditID = '$FeedMillAuditID' AND PaymentDate = ''";
        $stmtPaymentStatus = $this->conn->prepare($sqlPaymentStatus);
        $stmtPaymentStatus->execute();
        $rowPaymentStatus = $stmtPaymentStatus->fetch(PDO::FETCH_ASSOC);
        $PaymentStatusCount = $rowPaymentStatus['TotalCount'];

        $Data['TotalPaymentIDs'] = $TotalPaymentIDs;
        $Data['PaymentStatusCount'] = $PaymentStatusCount;
        return $Data;
    }
}
