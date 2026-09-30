<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class FeedMillAuditReportController
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

    function getFeedMillAuditDetails($RequestBody)
    {
        $AuditNameID = $RequestBody['AuditNameID'];
        $Year = $RequestBody['Year'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $StartDate = $Year . '-04-01';
        $EndDate = ($Year + 1) . '-03-31';

        $Query = '';
        if ($AuditNameID != '') {
            $Query .= " AND AuditNameID = '$AuditNameID'";
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
            CheckedBy, VerifiedBy, RPS.StationName, QACM.CategoryName, QACOM.OptionName, AuditStatus, QFAD.Remarks,
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
        CheckedBy, VerifiedBy, RPS.StationName, QACM.CategoryName AS CategoryName, AuditStatus, QFAD.Remarks,
        QLT.LbuName AS CheckedByName, QLT2.LbuName AS VerifiedByName, QACBM.CertificationBodyName, QAFM.FarmerName";

        $sqlSelect = "SELECT $sqlFields FROM QcFeedMillAuditDetails AS QFAD 
        LEFT JOIN QcAuditCategoriesMaster AS QACM ON QFAD.AuditNameID = QACM.AuditID
        LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QFAD.CertificationBodyID = QACBM.CertificationBodyID
        LEFT JOIN QcAuditFarmMasters AS QAFM ON QFAD.FarmerNameID = QAFM.FarmID
        LEFT JOIN RmProcessingStations AS RPS ON QFAD.PlantID = RPS.ProcessingStationID
        LEFT JOIN QcLabTechnologist AS QLT ON QFAD.CheckedBy = QLT.LUID
        LEFT JOIN QcLabTechnologist AS QLT2 ON QFAD.VerifiedBy = QLT2.LUID
        WHERE AuditDate $DateFilters $Query " . $Limit;

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);


        $ArrayAuditData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $CurrentDate = new DateTime(date('Y-m-d'));
            $PaymentData = $this->getFeedMillAuditPaymentData($dataRow['FeedMillAuditID']);

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
            $ArrayAuditList['Remarks'] = $dataRow['Remarks'];
            $ArrayAuditList['AuditStatus'] = $dataRow['AuditStatus'];

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


    function getFeedMillAuditPaymentData($FeedMillAuditID)
    {

        $sqlPaymentFields = "FeedMillAuditPaymentID, FeedMillAuditID, PurposeOfInvoice, InvoiceNo, InvoiceDate, InvoiceValue, InvoiceDueDate, 
        Others, PaymentType, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime";

        $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcFeedMillAuditPaymentDetails WHERE FeedMillAuditID = '$FeedMillAuditID'";
        $stmtPaymentSelect = $this->conn->prepare($sqlPaymentSelect);
        $stmtPaymentSelect->execute();
        // $errorInfo = $stmtPaymentSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayPaymentData = [];
        $i = 0;
        $PaymentTotalAmount = 0;
        while ($dataPaymentRow = $stmtPaymentSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayPaymentList['SNO'] = ++$i;
            $ArrayPaymentList['FeedMillAuditPaymentID'] = $dataPaymentRow['FeedMillAuditPaymentID'];
            $ArrayPaymentList['FeedMillAuditID'] = $dataPaymentRow['FeedMillAuditID'];
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
