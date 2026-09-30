<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class SocialAuditController
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

    public function postSocialAuditDetails($RequestBody)
    {
        $SocialAuditID = $RequestBody['SocialAuditID'];
        $PlantID = $RequestBody['PlantID'];
        $AuditNameID = $RequestBody['AuditNameID'];
        $AuditTypeID = $RequestBody['AuditTypeID'];
        $CertificationBodyID = $RequestBody['CertificationBodyID'];
        $GradeNScore = $RequestBody['GradeNScore'];
        $WindowPeriod = $RequestBody['WindowPeriod']; //
        $SiteId = $RequestBody['SiteId']; // 
        $AuditStartDate = $RequestBody['AuditStartDate'];
        $AuditEndDate = $RequestBody['AuditEndDate'];
        $IssuedOn = $RequestBody['IssuedOn'];
        $ValidityUpto = $RequestBody['ValidityUpto'];
        $RenewalDate = $RequestBody['RenewalDate'];
        $CheckedBy = $RequestBody['CheckedBy'];
        $VerifiedBy = $RequestBody['VerifiedBy'];
        $UploadFile= $RequestBody['UploadFile'];
        $Status = $RequestBody['Status'];
        $AuditStatus = $RequestBody['AuditStatus'];
        $Remarks = $RequestBody['Remarks'];

        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");
        $existingAudit = null;
        if ($SocialAuditID != '') {
            $existingAudit = $this->auditLogger->fetchRowById('QcSocialAuditDetails', 'SocialAuditID', $SocialAuditID);
        }

        if ($SocialAuditID == '') {
            $sqlInsert = "INSERT INTO QcSocialAuditDetails(SocialAuditID, PlantID, AuditNameID, AuditTypeID, 
            CertificationBodyID, WindowPeriod, GradeNScore, SiteId, AuditStartDate, AuditEndDate, IssuedOn, 
            ValidityUpto, RenewalDate, Status, AuditStatus, CheckedBy, VerifiedBy,UploadFile, Remarks, PostedDatetime) VALUES ('$SocialAuditID', '$PlantID', '$AuditNameID', '$AuditTypeID', 
            '$CertificationBodyID', '$WindowPeriod', '$GradeNScore', '$SiteId', '$AuditStartDate', '$AuditEndDate', '$IssuedOn', 
            '$ValidityUpto', '$RenewalDate', '$Status', '$AuditStatus', '$CheckedBy', '$VerifiedBy','$UploadFile', '$Remarks', '$PostedDate')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                $this->Error = null;
                $this->Msg = 'Social Audit Posted Successfully';
                $LastInsertID = $this->conn->lastInsertId();
                $createdAudit = $this->auditLogger->fetchRowById('QcSocialAuditDetails', 'SocialAuditID', $LastInsertID);
                $this->auditLogger->logInsert('QcSocialAuditDetails', $LastInsertID, $createdAudit, $RequestBody, [
                    'ReferenceNo' => $LastInsertID,
                    'Remarks' => 'QC social audit created',
                ]);
            } else {
                $this->Error = "Unable to Post Social Audit Data";
                $this->Msg = "Unable to Post Social Audit Data";
            }
        } else {
            if ($UploadFile != '') {
                $UpdateQuery = ",UploadFile='$UploadFile'";
            } else {
                $UpdateQuery = "";
            }
            $sqlUpdate = "UPDATE QcSocialAuditDetails SET PlantID='$PlantID',AuditNameID='$AuditNameID',AuditTypeID='$AuditTypeID',
            CertificationBodyID='$CertificationBodyID',WindowPeriod='$WindowPeriod',GradeNScore='$GradeNScore',SiteId='$SiteId',
            AuditStartDate='$AuditStartDate',AuditEndDate='$AuditEndDate',IssuedOn='$IssuedOn',ValidityUpto='$ValidityUpto',
            RenewalDate='$RenewalDate',Status='$Status', AuditStatus='$AuditStatus',CheckedBy='$CheckedBy',VerifiedBy='$VerifiedBy',
            Remarks='$Remarks',PostedDatetime='$PostedDate' $UpdateQuery WHERE SocialAuditID='$SocialAuditID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            if ($stmtUpdate) {
                $this->Error = null;
                $this->Msg = 'Social Audit Updated Successfully';
                $LastInsertID = $SocialAuditID;
                $updatedAudit = $this->auditLogger->fetchRowById('QcSocialAuditDetails', 'SocialAuditID', $SocialAuditID);
                $this->auditLogger->logUpdate('QcSocialAuditDetails', $SocialAuditID, $existingAudit, $updatedAudit, $RequestBody, [
                    'ReferenceNo' => $SocialAuditID,
                    'Remarks' => 'QC social audit updated',
                ]);
            } else {
                $this->Error = "Unable to Update Social Audit Data";
                $this->Msg = "Unable to Update Social Audit Data";
            }
        }

        $PaymentData = $this->getSocialAuditPaymentData($SocialAuditID);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['LastInserted'] = $LastInsertID;
        $Data['PaymentData'] = $PaymentData['PaymentData'];

        return $Data;
       // echo json_encode($Data, JSON_PRETTY_PRINT);


    }

    function getSocialAuditDetails($RequestBody)
    {

        $SocialAuditID = $RequestBody['SocialAuditID'];
        $PlantID = $RequestBody['PlantID'];
        $Year = $RequestBody['Year'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $StartDate = $Year . '-04-01';
        $EndDate = ($Year + 1) . '-03-31';

        $Query = '';
        if ($PlantID != '') {
            $Query .= " AND QSAD.PlantID = '$PlantID'";
        }

        $DateFilters = " BETWEEN '$StartDate' AND '$EndDate'";

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('', SocialAuditID, QSAD.PlantID, AuditNameID, AuditTypeID, QSAD.CertificationBodyID, 
            WindowPeriod, GradeNScore,SiteId, AuditStartDate, AuditEndDate, IssuedOn, ValidityUpto, RenewalDate, QSAD.Status, 
            CheckedBy, VerifiedBy,UploadFile, RPS.StationName, QACM.CategoryName AS CategoryName, QACOM.OptionName, AuditStatus, QSAD.Remarks,
            QLT.LbuName, QLT2.LbuName, QADM.OptionName, CertificationBodyName) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(*) AS TotalCount FROM QcSocialAuditDetails AS QSAD 
        LEFT JOIN QcAuditCategoriesMaster AS QACM ON QSAD.AuditNameID = QACM.AuditID
        LEFT JOIN QcAuditCategoriesOptionsMaster AS QACOM ON QSAD.AuditTypeID = QACOM.AuditOptionID
        LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QSAD.CertificationBodyID = QACBM.CertificationBodyID
        LEFT JOIN QcAuditDropdownMasters AS QADM ON QSAD.SiteId = QADM.OptionID
        LEFT JOIN RmProcessingStations AS RPS ON QSAD.PlantID = RPS.ProcessingStationID
        LEFT JOIN QcLabTechnologist AS QLT ON QSAD.CheckedBy = QLT.LUID
        LEFT JOIN QcLabTechnologist AS QLT2 ON QSAD.VerifiedBy = QLT2.LUID
        WHERE IssuedOn $DateFilters $Query ";

        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataCount['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);


        $sqlFields = "SocialAuditID, QSAD.PlantID, QSAD.AuditNameID, AuditTypeID, QSAD.CertificationBodyID, 
        WindowPeriod, GradeNScore,SiteId, AuditStartDate, AuditEndDate, IssuedOn, ValidityUpto, RenewalDate, QSAD.Status, 
        CheckedBy, VerifiedBy,UploadFile, RPS.StationName, QACM.CategoryName AS CategoryName, QACOM.OptionName, AuditStatus, QSAD.Remarks,
        QLT.LbuName AS CheckedByName, QLT2.LbuName AS VerifiedByName, QADM.OptionName AS SiteName, CertificationBodyName";

        if ($SocialAuditID != '') {
            $sqlSelect = "SELECT $sqlFields FROM QcSocialAuditDetails AS QSAD 
            LEFT JOIN QcAuditCategoriesMaster AS QACM ON QSAD.AuditNameID = QACM.AuditID
            LEFT JOIN QcAuditCategoriesOptionsMaster AS QACOM ON QSAD.AuditTypeID = QACOM.AuditOptionID
            LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QSAD.CertificationBodyID = QACBM.CertificationBodyID
            LEFT JOIN QcAuditDropdownMasters AS QADM ON QSAD.SiteId = QADM.OptionID
            LEFT JOIN RmProcessingStations AS RPS ON QSAD.PlantID = RPS.ProcessingStationID
            LEFT JOIN QcLabTechnologist AS QLT ON QSAD.CheckedBy = QLT.LUID
            LEFT JOIN QcLabTechnologist AS QLT2 ON QSAD.VerifiedBy = QLT2.LUID
            WHERE SocialAuditID = '$SocialAuditID'";
        } else {
            $sqlSelect = "SELECT $sqlFields FROM QcSocialAuditDetails AS QSAD 
            LEFT JOIN QcAuditCategoriesMaster AS QACM ON QSAD.AuditNameID = QACM.AuditID
            LEFT JOIN QcAuditCategoriesOptionsMaster AS QACOM ON QSAD.AuditTypeID = QACOM.AuditOptionID
            LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QSAD.CertificationBodyID = QACBM.CertificationBodyID
            LEFT JOIN QcAuditDropdownMasters AS QADM ON QSAD.SiteId = QADM.OptionID
            LEFT JOIN RmProcessingStations AS RPS ON QSAD.PlantID = RPS.ProcessingStationID
            LEFT JOIN QcLabTechnologist AS QLT ON QSAD.CheckedBy = QLT.LUID
            LEFT JOIN QcLabTechnologist AS QLT2 ON QSAD.VerifiedBy = QLT2.LUID
            WHERE AuditStartDate $DateFilters $Query " . $Limit;
        }

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ArrayAuditData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $CurrentDate = new DateTime(date('Y-m-d'));
            $decodedUploadFile = json_decode($dataRow['UploadFile'], true);

            $baseURL = 'https://staging-server.miledeepworks.in/v3/QualityControl/';
            $DownloadableURLs = [];
            foreach ($decodedUploadFile as $filePath) {
                $cleanedFilePath = str_replace('../', '', $filePath);
                $DownloadableURLs[] = $baseURL . $cleanedFilePath;
            }

            $PaymentStatus = $this->getPaymentStatus($dataRow['SocialAuditID']);

            $ArrayAuditList['SNO'] = ++$i;
            $ArrayAuditList['SocialAuditID'] = $dataRow['SocialAuditID'];
            $ArrayAuditList['PlantID'] = $dataRow['PlantID'];
            $ArrayAuditList['StationName'] = $dataRow['StationName'];
            $ArrayAuditList['AuditNameID'] = $dataRow['AuditNameID'];
            $ArrayAuditList['CategoryName'] = $dataRow['CategoryName'];
            $ArrayAuditList['AuditTypeID'] = $dataRow['AuditTypeID'];
            $ArrayAuditList['OptionName'] = $dataRow['OptionName'];
            $ArrayAuditList['CertificationBodyID'] = $dataRow['CertificationBodyID'];
            $ArrayAuditList['CertificationBodyName'] = $dataRow['CertificationBodyName'];
            $ArrayAuditList['WindowPeriod'] = $dataRow['WindowPeriod'];
            $ArrayAuditList['GradeNScore'] = $dataRow['GradeNScore'];
            $ArrayAuditList['SiteId'] = $dataRow['SiteId'];
            $ArrayAuditList['SiteName'] = $dataRow['SiteName'];
            $ArrayAuditList['AuditStartDate'] = $dataRow['AuditStartDate'];
            $ArrayAuditList['AuditEndDate'] = $dataRow['AuditEndDate'];
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

            $ArrayAuditList['EditURL'] = 'social-audit-create?SocialAuditID=' . $dataRow['SocialAuditID'];
            $ArrayAuditList['ConfirmURL'] = 'social-audit-create?SocialAuditID=' . $dataRow['SocialAuditID'] . '&ConfirmPayment=1';

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

        $PaymentData = $this->getSocialAuditPaymentData($SocialAuditID);
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['PageCount'] = $PageCount;
        $data['Data'] = $ArrayAuditData;
        $data['PaymentData'] = $PaymentData['PaymentData'];

        return $data;

          //echo json_encode($data, JSON_PRETTY_PRINT);
        
    }


    function getSocialAuditPaymentData($SocialAuditID)
    {
        $sqlPaymentFields = "SocialAuditPaymentID, SocialAuditID, PurposeOfInvoice, PaymentType, InvoiceNo, InvoiceDate, 
        InvoiceValue, InvoiceDueDate,Others, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime,OptionName";

        $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcSocialAuditPaymentDetails  AS QSAP
        LEFT JOIN QcAuditDropdownMasters AS QADM ON QSAP.PaymentType = QADM.OptionID
        WHERE SocialAuditID = '$SocialAuditID'";
        $stmtPaymentSelect = $this->conn->prepare($sqlPaymentSelect);
        $stmtPaymentSelect->execute();

        $ArrayPaymentData = [];
        $i = 0;
        while ($dataPaymentRow = $stmtPaymentSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayPaymentList['SNO'] = ++$i;
            $ArrayPaymentList['SocialAuditPaymentID'] = $dataPaymentRow['SocialAuditPaymentID'];
            $ArrayPaymentList['SocialAuditID'] = $dataPaymentRow['SocialAuditID'];
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

    function getPaymentStatus($SocialAuditID)
    {
        $sqlPaymentIds = "SELECT COUNT(*) AS TotalPaymentIDs FROM QcSocialAuditPaymentDetails
        WHERE SocialAuditID = '$SocialAuditID'";
        $stmtPaymentIDs = $this->conn->prepare($sqlPaymentIds);
        $stmtPaymentIDs->execute();
        $rowPaymentIDs = $stmtPaymentIDs->fetch(PDO::FETCH_ASSOC);
        $TotalPaymentIDs = $rowPaymentIDs['TotalPaymentIDs'];

        $sqlPaymentStatus = "SELECT COUNT(*) AS TotalCount FROM QcSocialAuditPaymentDetails 
        WHERE SocialAuditID = '$SocialAuditID' AND PaymentDate = ''";
        $stmtPaymentStatus = $this->conn->prepare($sqlPaymentStatus);
        $stmtPaymentStatus->execute();
        $rowPaymentStatus = $stmtPaymentStatus->fetch(PDO::FETCH_ASSOC);
        $PaymentStatusCount = $rowPaymentStatus['TotalCount'];

        $Data['TotalPaymentIDs'] = $TotalPaymentIDs;
        $Data['PaymentStatusCount'] = $PaymentStatusCount;
        return $Data;
    }
}
