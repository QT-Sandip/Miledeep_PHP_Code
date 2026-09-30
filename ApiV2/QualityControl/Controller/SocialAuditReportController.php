<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class SocialAuditReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }

    function getSocialAuditDetails($RequestBody)
    {
        $AuditNameID = $RequestBody['AuditNameID'];
        $PlantID = $RequestBody['PlantID'];
        $Year = $RequestBody['Year'];
        $Month = $RequestBody['Month'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $StartDate = $Year . '-04-01';
        $EndDate = ($Year + 1) . '-03-31';

        $Query = '';
        if ($AuditNameID != '') {
            $Query .= " AND QSAD.AuditNameID = '$AuditNameID'";
        }
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
            $Query .= " and CONCAT_WS('', SocialAuditID, QSAD.PlantID, QSAD.AuditNameID, AuditTypeID, QSAD.CertificationBodyID, 
            WindowPeriod, GradeNScore,SiteId, AuditStartDate, AuditEndDate, IssuedOn, ValidityUpto, RenewalDate, QSAD.Status, 
            CheckedBy, VerifiedBy, RPS.StationName, QACM.CategoryName, QACOM.OptionName, AuditStatus, QSAD.Remarks,
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
        WHERE AuditStartDate $DateFilters $Query";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataCount['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "SocialAuditID, QSAD.PlantID, QSAD.AuditNameID, AuditTypeID, QSAD.CertificationBodyID, 
        WindowPeriod, GradeNScore,SiteId, AuditStartDate, AuditEndDate, IssuedOn, ValidityUpto, RenewalDate, QSAD.Status, 
        CheckedBy, VerifiedBy, RPS.StationName, QACM.CategoryName AS CategoryName, QACOM.OptionName, AuditStatus, QSAD.Remarks,
        QLT.LbuName AS CheckedByName, QLT2.LbuName AS VerifiedByName, QADM.OptionName AS SiteName, CertificationBodyName";

        $sqlSelect = "SELECT $sqlFields FROM QcSocialAuditDetails AS QSAD 
        LEFT JOIN QcAuditCategoriesMaster AS QACM ON QSAD.AuditNameID = QACM.AuditID
        LEFT JOIN QcAuditCategoriesOptionsMaster AS QACOM ON QSAD.AuditTypeID = QACOM.AuditOptionID
        LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QSAD.CertificationBodyID = QACBM.CertificationBodyID
        LEFT JOIN QcAuditDropdownMasters AS QADM ON QSAD.SiteId = QADM.OptionID
        LEFT JOIN RmProcessingStations AS RPS ON QSAD.PlantID = RPS.ProcessingStationID
        LEFT JOIN QcLabTechnologist AS QLT ON QSAD.CheckedBy = QLT.LUID
        LEFT JOIN QcLabTechnologist AS QLT2 ON QSAD.VerifiedBy = QLT2.LUID
        WHERE AuditStartDate $DateFilters $Query " . $Limit;


        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayAuditData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $CurrentDate = new DateTime(date('Y-m-d'));

            $PaymentData = $this->getSocialAuditPaymentData($dataRow['SocialAuditID']);

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
            $ArrayAuditList['Remarks'] = $dataRow['Remarks'];
            $ArrayAuditList['AuditStatus'] = $dataRow['AuditStatus'];

            $ArrayAuditList['EditURL'] = 'social-audit-create?SocialAuditID=' . $dataRow['SocialAuditID'];

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
                $AuditStatusName = "Fresh Audit";
            } else if ($AuditStatus == 2) {
                $AuditStatusName = "Renewal Audit";
            } else if ($AuditStatus == 3) {
                $AuditStatusName = "Re Certification";
            } else {
                $AuditStatusName = "";
            }

            $ArrayAuditList['DisplayColor'] = $DisplayColor;
            $ArrayAuditList['TextColor'] = $TextColor;
            $ArrayAuditList['StatusMessage'] = $StatusMessage;
            $ArrayAuditList['AuditStatusName'] = $AuditStatusName;
            $ArrayAuditList['PaymentTotalAmount'] = $PaymentData['PaymentTotalAmount'];
            $ArrayAuditList['PaymentData'] = $PaymentData['PaymentData'];

            $ArrayAuditData[] = $ArrayAuditList;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['PageCount'] = $PageCount;
        $data['Data'] = $ArrayAuditData;

        return $data;
    }


    function getSocialAuditPaymentData($SocialAuditID)
    {

        $sqlPaymentFields = "SocialAuditPaymentID, SocialAuditID, PurposeOfInvoice, PaymentType, InvoiceNo, InvoiceDate, 
        InvoiceValue, InvoiceDueDate,Others, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime";

        $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcSocialAuditPaymentDetails WHERE SocialAuditID = '$SocialAuditID'";
        $stmtPaymentSelect = $this->conn->prepare($sqlPaymentSelect);
        $stmtPaymentSelect->execute();
        // $errorInfo = $stmtPaymentSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayPaymentData = [];
        $i = 0;
        $PaymentTotalAmount = 0;
        while ($dataPaymentRow = $stmtPaymentSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayPaymentList['SNO'] = ++$i;
            $ArrayPaymentList['SocialAuditPaymentID'] = $dataPaymentRow['SocialAuditPaymentID'];
            $ArrayPaymentList['SocialAuditID'] = $dataPaymentRow['SocialAuditID'];
            $ArrayPaymentList['PurposeOfInvoice'] = $dataPaymentRow['PurposeOfInvoice'];
            $ArrayPaymentList['PaymentType'] = $dataPaymentRow['PaymentType'];
            $ArrayPaymentList['InvoiceNo'] = $dataPaymentRow['InvoiceNo'];
            $ArrayPaymentList['InvoiceDate'] = $dataPaymentRow['InvoiceDate'];
            $ArrayPaymentList['InvoiceValue'] = $dataPaymentRow['InvoiceValue'];
            $ArrayPaymentList['InvoiceDueDate'] = $dataPaymentRow['InvoiceDueDate'];
            $ArrayPaymentList['Others'] = $dataPaymentRow['Others'];
            $ArrayPaymentList['TotalAmount'] = $dataPaymentRow['TotalAmount'];
            $TotalAmount = $dataPaymentRow['TotalAmount'];
            $ArrayPaymentList['PaymentDate'] = $dataPaymentRow['PaymentDate'];
            $ArrayPaymentList['PaymentRemarks'] = $dataPaymentRow['PaymentRemarks'];
            $ArrayPaymentList['PostedDatetime'] = $dataPaymentRow['PostedDatetime'];

            $PaymentTotalAmount += $TotalAmount;
            $ArrayPaymentData[] = $ArrayPaymentList;
        }

        $Data['PaymentData'] = $ArrayPaymentData;
        $Data['PaymentTotalAmount'] = $PaymentTotalAmount;

        return $Data;
    }
}
