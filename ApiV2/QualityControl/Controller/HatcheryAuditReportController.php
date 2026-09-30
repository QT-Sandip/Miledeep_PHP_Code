<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class HatcheryAuditReportController
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

    function getHatcheryAuditDetails($RequestBody)
    {

        $HatcheryName = $RequestBody['HatcheryName'];
        $Year = $RequestBody['Year'];
        // $Month = $RequestBody['Month'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $StartDate = $Year . '-04-01';
        $EndDate = ($Year + 1) . '-03-31';

        $Query = '';
        if ($HatcheryName != '') {
            $Query .= " AND HatcheryName = '$HatcheryName'";
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
            CheckedBy, VerifiedBy, RPS.StationName, QACM.CategoryName, QACOM.OptionName, AuditStatus, QHAD.Remarks,
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
        CheckedBy, VerifiedBy, RPS.StationName, QADM.OptionName AS Hatchery, QAHM.HatcheryLocation as HatcheryLocationName, AuditStatus, QHAD.Remarks,
        QLT.LbuName AS CheckedByName, QLT2.LbuName AS VerifiedByName, QACBM.CertificationBodyName";

        $sqlSelect = "SELECT $sqlFields FROM QcHatcheryAuditDetails AS QHAD 
        LEFT JOIN QcAuditDropdownMasters AS QADM ON QHAD.HatcheryName = QADM.OptionID
        LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QHAD.CertificationBody = QACBM.CertificationBodyID
        LEFT JOIN QcAuditHatcheryMasters AS QAHM ON QHAD.HatcheryLocation = QAHM.HatcheryNameID
        LEFT JOIN RmProcessingStations AS RPS ON QHAD.PlantID = RPS.ProcessingStationID
        LEFT JOIN QcLabTechnologist AS QLT ON QHAD.CheckedBy = QLT.LUID
        LEFT JOIN QcLabTechnologist AS QLT2 ON QHAD.VerifiedBy = QLT2.LUID
        WHERE AuditDate $DateFilters $Query " . $Limit;

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayAuditData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $CurrentDate = new DateTime(date('Y-m-d'));

            $PaymentData = $this->getHatcheryAuditPaymentData($dataRow['HatcheryID']);

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


    function getHatcheryAuditPaymentData($HatcheryID)
    {

        $sqlPaymentFields = "HatcheryPaymentID, HatcheryID, PurposeOfInvoice, PaymentType, InvoiceNumber, InvoiceDate, 
        InvoiceValue, InvoiceDueDate,Others, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime, PostedUserID";

        $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcHatcheryAuditPaymentDetails WHERE HatcheryID = '$HatcheryID'";
        $stmtPaymentSelect = $this->conn->prepare($sqlPaymentSelect);
        $stmtPaymentSelect->execute();
        // $errorInfo = $stmtPaymentSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayPaymentData = [];
        $i = 0;
        $PaymentTotalAmount = 0;
        while ($dataPaymentRow = $stmtPaymentSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayPaymentList['SNO'] = ++$i;
            $ArrayPaymentList['HatcheryPaymentID'] = $dataPaymentRow['HatcheryPaymentID'];
            $ArrayPaymentList['HatcheryID'] = $dataPaymentRow['HatcheryID'];
            $ArrayPaymentList['PurposeOfInvoice'] = $dataPaymentRow['PurposeOfInvoice'];
            $ArrayPaymentList['PaymentType'] = $dataPaymentRow['PaymentType'];
            $ArrayPaymentList['InvoiceNumber'] = $dataPaymentRow['InvoiceNumber'];
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
