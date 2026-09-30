<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class FarmAuditReportController
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

    function getFarmAuditDetails($RequestBody)
    {
        $FarmNameID = $RequestBody['FarmNameID'];
        $PlantID = $RequestBody['PlantID'];
        $Year = $RequestBody['Year'];
        $Month = $RequestBody['Month'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $StartDate = $Year . '-04-01';
        $EndDate = ($Year + 1) . '-03-31';

        $Query = '';
        if ($FarmNameID != '') {
            $Query .= " AND FarmNameID = '$FarmNameID'";
        }
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
            $Query .= " and CONCAT_WS('', FarmAuditID, FarmNameID, FarmerNameID, QFAD.CertificationBodyID, RegistrationNo, AssociatedFactory, 
            IssuingAuthorityID, FarmSite, BAPNumber,ASCNo, TonnagePerCycle, WaterSpreadArea, Species, QFAD.Status, CerificationNo, AuditDate, 
            IssuedOn, ValidUpto, RenewalDate, CheckedBy, VerifiedBy,PostedDatetime,RPS.StationName, AuditStatus, QFAD.Remarks, QFAD.PlantID,
            QLT.LbuName, QLT2.LbuName, QACBM.CertificationBodyName, QADM.OptionName, FarmerName, 
            QADM2.OptionName, QADM3.OptionName) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(*) AS TotalCount FROM QcFarmAuditDetails AS QFAD 
        LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QFAD.CertificationBodyID = QACBM.CertificationBodyID
        LEFT JOIN QcAuditDropdownMasters AS QADM ON QFAD.FarmNameID = QADM.OptionID
        LEFT JOIN QcAuditFarmMasters AS QAFM ON QFAD.FarmerNameID = QAFM.FarmID
        LEFT JOIN QcAuditDropdownMasters AS QADM2 ON QFAD.IssuingAuthorityID = QADM2.OptionID
        LEFT JOIN QcAuditDropdownMasters AS QADM3 ON QFAD.FarmSite = QADM3.OptionID
        LEFT JOIN RmProcessingStations AS RPS ON QFAD.PlantID = RPS.ProcessingStationID
        LEFT JOIN QcLabTechnologist AS QLT ON QFAD.CheckedBy = QLT.LUID
        LEFT JOIN QcLabTechnologist AS QLT2 ON QFAD.VerifiedBy = QLT2.LUID
        WHERE AuditDate $DateFilters $Query ";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataCount['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "FarmAuditID, FarmNameID, FarmerNameID, QFAD.CertificationBodyID, RegistrationNo, AssociatedFactory, IssuingAuthorityID, 
        FarmSite, BAPNumber,ASCNo, TonnagePerCycle, WaterSpreadArea, Species, QFAD.Status, CerificationNo, AuditDate, IssuedOn, ValidUpto, 
        RenewalDate, CheckedBy, VerifiedBy,PostedDatetime,RPS.StationName, AuditStatus, QFAD.Remarks, QFAD.PlantID,
        QLT.LbuName AS CheckedByName, QLT2.LbuName AS VerifiedByName, QACBM.CertificationBodyName, QADM.OptionName AS FarmName, GROUP_CONCAT(QAFM.FarmerName SEPARATOR ',<br>') AS FarmerName, 
        QADM2.OptionName AS IssuingAuthority, QADM3.OptionName AS FarmSiteName";

        $sqlSelect = "SELECT $sqlFields FROM QcFarmAuditDetails AS QFAD 
        LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QFAD.CertificationBodyID = QACBM.CertificationBodyID
        LEFT JOIN QcAuditDropdownMasters AS QADM ON QFAD.FarmNameID = QADM.OptionID
        LEFT JOIN QcAuditFarmMasters AS QAFM ON FIND_IN_SET(QAFM.FarmID, QFAD.FarmerNameID) > 0
        LEFT JOIN QcAuditDropdownMasters AS QADM2 ON QFAD.IssuingAuthorityID = QADM2.OptionID
        LEFT JOIN QcAuditDropdownMasters AS QADM3 ON QFAD.FarmSite = QADM3.OptionID
        LEFT JOIN RmProcessingStations AS RPS ON QFAD.PlantID = RPS.ProcessingStationID
        LEFT JOIN QcLabTechnologist AS QLT ON QFAD.CheckedBy = QLT.LUID
        LEFT JOIN QcLabTechnologist AS QLT2 ON QFAD.VerifiedBy = QLT2.LUID
        WHERE AuditDate $DateFilters $Query GROUP BY FarmAuditID " . $Limit;

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);


        $ArrayAuditData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $CurrentDate = new DateTime(date('Y-m-d'));
            $PaymentData = $this->getFarmAuditPaymentData($dataRow['FarmAuditID']);

            $ArrayAuditList['SNO'] = ++$i;
            $ArrayAuditList['FarmAuditID'] = $dataRow['FarmAuditID'];
            $ArrayAuditList['PlantID'] = $dataRow['PlantID'];
            $ArrayAuditList['StationName'] = $dataRow['StationName'];
            $ArrayAuditList['FarmNameID'] = $dataRow['FarmNameID'];
            $ArrayAuditList['FarmName'] = $dataRow['FarmName'];
            $ArrayAuditList['FarmerNameID'] = $dataRow['FarmerNameID'];
            $ArrayAuditList['FarmerName'] = $dataRow['FarmerName'];
            $ArrayAuditList['CertificationBodyID'] = $dataRow['CertificationBodyID'];
            $ArrayAuditList['CertificationBodyName'] = $dataRow['CertificationBodyName'];
            $ArrayAuditList['RegistartionNo'] = $dataRow['RegistrationNo'];
            $ArrayAuditList['AssociatedFactory'] = $dataRow['AssociatedFactory'];
            $ArrayAuditList['IssuingAuthorityID'] = $dataRow['IssuingAuthorityID'];
            $ArrayAuditList['IssuingAuthority'] = $dataRow['IssuingAuthority'];
            $ArrayAuditList['FarmSite'] = $dataRow['FarmSite'];
            $ArrayAuditList['FarmSiteName'] = $dataRow['FarmSiteName'];
            $ArrayAuditList['BAPNumber'] = $dataRow['BAPNumber'];
            $ArrayAuditList['ASCNo'] = $dataRow['ASCNo'];
            $ArrayAuditList['TonnagePerCycle'] = $dataRow['TonnagePerCycle'];
            $ArrayAuditList['WaterSpreadArea'] = $dataRow['WaterSpreadArea'];
            $ArrayAuditList['Species'] = $dataRow['Species'];
            $ArrayAuditList['CerificationNo'] = $dataRow['CerificationNo'];
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


    function getFarmAuditPaymentData($FarmAuditID)
    {

        $sqlPaymentFields = "FarmAuditPaymentID, FarmAuditID, PurposeOfInvoice, InvoiceNo, InvoiceDate, InvoiceValue, 
        InvoiceDueDate, Others, PaymentType, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime";

        $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcFarmAuditPaymentDetails WHERE FarmAuditID = '$FarmAuditID'";
        $stmtPaymentSelect = $this->conn->prepare($sqlPaymentSelect);
        $stmtPaymentSelect->execute();
        // $errorInfo = $stmtPaymentSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayPaymentData = [];
        $i = 0;
        $PaymentTotalAmount = 0;
        while ($dataPaymentRow = $stmtPaymentSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayPaymentList['SNO'] = ++$i;
            $ArrayPaymentList['FarmAuditPaymentID'] = $dataPaymentRow['FarmAuditPaymentID'];
            $ArrayPaymentList['FarmAuditID'] = $dataPaymentRow['FarmAuditID'];
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
