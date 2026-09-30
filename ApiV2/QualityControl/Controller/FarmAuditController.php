<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class FarmAuditController
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

    public function postFarmAuditDetails($RequestBody)
    {

        $FarmAuditID = $RequestBody['FarmAuditID'];
        $PlantID = $RequestBody['PlantID'];
        $FarmNameID = $RequestBody['FarmNameID'];
        $FarmerNameID = $RequestBody['FarmerNameID'];
        $CertificationBodyID = $RequestBody['CertificationBodyID'];
        $RegistartionNo = $RequestBody['RegistartionNo'];
        $AssociatedFactory = $RequestBody['AssociatedFactory'];
        $IssuingAuthorityID = $RequestBody['IssuingAuthorityID'];
        $FarmSite = $RequestBody['FarmSite'];
        $BAPNumber = $RequestBody['BAPNumber'];
        $ASCNo = $RequestBody['ASCNo'];
        $TonnagePerCycle = $RequestBody['TonnagePerCycle'];
        $WaterSpreadArea = $RequestBody['WaterSpreadArea'];
        $Species = $RequestBody['Species'];
        $CerificationNo = $RequestBody['CerificationNo'];
        $AuditDate = $RequestBody['AuditDate'];
        $IssuedOn = $RequestBody['IssuedOn'];
        $ValidUpto = $RequestBody['ValidUpto'];
        $RenewalDate = $RequestBody['RenewalDate'];
        $CheckedBy = $RequestBody['CheckedBy'];
        $VerifiedBy = $RequestBody['VerifiedBy'];
        $UploadFile = $RequestBody['UploadFile'];
        $Status = $RequestBody['Status'];
        $AuditStatus = $RequestBody['AuditStatus'];
        $Remarks = $RequestBody['Remarks'];
        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");
        $existingAudit = null;
        if ($FarmAuditID != '') {
            $existingAudit = $this->auditLogger->fetchRowById('QcFarmAuditDetails', 'FarmAuditID', $FarmAuditID);
        }

        if ($FarmAuditID == '') {

            $sqlInsert = "INSERT INTO QcFarmAuditDetails(PlantID, FarmNameID, FarmerNameID, CertificationBodyID, RegistrationNo, 
            AssociatedFactory, IssuingAuthorityID, FarmSite, BAPNumber, ASCNo, TonnagePerCycle, WaterSpreadArea, Species, 
            CerificationNo, AuditDate, IssuedOn, ValidUpto, RenewalDate, Status, CheckedBy, VerifiedBy,UploadFile, AuditStatus,Remarks,PostedDatetime) 
            VALUES ('$PlantID', '$FarmNameID', '$FarmerNameID', '$CertificationBodyID', '$RegistartionNo', '$AssociatedFactory', 
            '$IssuingAuthorityID', '$FarmSite', '$BAPNumber', '$ASCNo', '$TonnagePerCycle', '$WaterSpreadArea', '$Species', '$CerificationNo', 
            '$AuditDate', '$IssuedOn', '$ValidUpto', '$RenewalDate', '$Status', '$CheckedBy', '$VerifiedBy','$UploadFile', '$AuditStatus', '$Remarks','$PostedDate')";
            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                $this->Error = null;
                $this->Msg = 'Farm Audit Posted Successfully';
                $LastInsertID = $this->conn->lastInsertId();
                $createdAudit = $this->auditLogger->fetchRowById('QcFarmAuditDetails', 'FarmAuditID', $LastInsertID);
                $this->auditLogger->logInsert('QcFarmAuditDetails', $LastInsertID, $createdAudit, $RequestBody, [
                    'ReferenceNo' => $LastInsertID,
                    'Remarks' => 'QC farm audit created',
                ]);
            } else {
                $this->Error = "Unable to Post Farm Audit Data";
                $this->Msg = "Unable to Post Farm Audit Data";
            }
        } else {
            if ($UploadFile != '') {
                $UpdateQuery = ",UploadFile='$UploadFile'";
            } else {
                $UpdateQuery = "";
            }
            $sqlUpdate = "UPDATE QcFarmAuditDetails SET PlantID='$PlantID',FarmNameID='$FarmNameID',FarmerNameID='$FarmerNameID',
            CertificationBodyID='$CertificationBodyID',RegistrationNo='$RegistartionNo',AssociatedFactory='$AssociatedFactory',
            IssuingAuthorityID='$IssuingAuthorityID',FarmSite='$FarmSite',BAPNumber='$BAPNumber',ASCNo='$ASCNo',
            TonnagePerCycle='$TonnagePerCycle',WaterSpreadArea='$WaterSpreadArea',Species='$Species',CerificationNo='$CerificationNo',
            AuditDate='$AuditDate', IssuedOn='$IssuedOn',ValidUpto='$ValidUpto',RenewalDate='$RenewalDate',Status='$Status',
            CheckedBy='$CheckedBy',VerifiedBy='$VerifiedBy',AuditStatus='$AuditStatus',Remarks='$Remarks', PostedDatetime='$PostedDate' $UpdateQuery WHERE FarmAuditID='$FarmAuditID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            if ($stmtUpdate) {
                $this->Error = null;
                $this->Msg = 'Farm Audit Updated Successfully';
                $LastInsertID = $FarmAuditID;
                $updatedAudit = $this->auditLogger->fetchRowById('QcFarmAuditDetails', 'FarmAuditID', $FarmAuditID);
                $this->auditLogger->logUpdate('QcFarmAuditDetails', $FarmAuditID, $existingAudit, $updatedAudit, $RequestBody, [
                    'ReferenceNo' => $FarmAuditID,
                    'Remarks' => 'QC farm audit updated',
                ]);
            } else {
                $this->Error = "Unable to Update Farm Audit Data";
                $this->Msg = "Unable to Update Farm Audit Data";
            }
        }

        $PaymentData = $this->getFarmAuditPaymentData($FarmAuditID);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['LastInserted'] = $LastInsertID;
        $Data['PaymentData'] = $PaymentData['PaymentData'];

        return $Data;
    }

    function getFarmAuditDetails($RequestBody)
    {

        $FarmAuditID = $RequestBody['FarmAuditID'];
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
            $Query .= " and CONCAT_WS('', FarmAuditID, FarmNameID, FarmerNameID, QFAD.CertificationBodyID, RegistrationNo, AssociatedFactory, 
            IssuingAuthorityID, FarmSite, BAPNumber,ASCNo, TonnagePerCycle, WaterSpreadArea, Species, QFAD.Status, CerificationNo, AuditDate, 
            IssuedOn, ValidUpto, RenewalDate, CheckedBy, VerifiedBy,UploadFile,PostedDatetime,RPS.StationName, AuditStatus, QFAD.Remarks, QFAD.PlantID,
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
        WHERE IssuedOn $DateFilters $Query ";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataCount['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "FarmAuditID, FarmNameID, FarmerNameID, QFAD.CertificationBodyID, RegistrationNo, AssociatedFactory, IssuingAuthorityID, 
        FarmSite, BAPNumber,ASCNo, TonnagePerCycle, WaterSpreadArea, Species, QFAD.Status, CerificationNo, AuditDate, IssuedOn, ValidUpto, 
        RenewalDate, CheckedBy,UploadFile, VerifiedBy,PostedDatetime,RPS.StationName, AuditStatus, QFAD.Remarks, QFAD.PlantID,
        QLT.LbuName AS CheckedByName, QLT2.LbuName AS VerifiedByName, QACBM.CertificationBodyName, QADM.OptionName AS FarmName, GROUP_CONCAT(QAFM.FarmerName SEPARATOR ',<br>') AS FarmerNames, 
        QADM2.OptionName AS IssuingAuthority, QADM3.OptionName AS FarmSiteName";

        if ($FarmAuditID != '') {
            $sqlSelect = "SELECT $sqlFields FROM QcFarmAuditDetails AS QFAD 
            LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QFAD.CertificationBodyID = QACBM.CertificationBodyID
            LEFT JOIN QcAuditDropdownMasters AS QADM ON QFAD.FarmNameID = QADM.OptionID
            LEFT JOIN QcAuditFarmMasters AS QAFM ON QFAD.FarmerNameID = QAFM.FarmID
            LEFT JOIN QcAuditDropdownMasters AS QADM2 ON QFAD.IssuingAuthorityID = QADM2.OptionID
            LEFT JOIN QcAuditDropdownMasters AS QADM3 ON QFAD.FarmSite = QADM3.OptionID
            LEFT JOIN RmProcessingStations AS RPS ON QFAD.PlantID = RPS.ProcessingStationID
            LEFT JOIN QcLabTechnologist AS QLT ON QFAD.CheckedBy = QLT.LUID
            LEFT JOIN QcLabTechnologist AS QLT2 ON QFAD.VerifiedBy = QLT2.LUID
            WHERE FarmAuditID = '$FarmAuditID'";
        } else {
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
        }

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ArrayAuditData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $CurrentDate = new DateTime(date('Y-m-d'));
            $PaymentStatus = $this->getPaymentStatus($dataRow['FarmAuditID']);
            $decodedUploadFile = json_decode($dataRow['UploadFile'], true);

            $baseURL = 'https://staging-server.miledeepworks.in/v3/QualityControl/';
            $DownloadableURLs = [];
            foreach ($decodedUploadFile as $filePath) {
                $cleanedFilePath = str_replace('../', '', $filePath);
                $DownloadableURLs[] = $baseURL . $cleanedFilePath;
            }
            
            $ArrayAuditList['SNO'] = ++$i;
            $ArrayAuditList['FarmAuditID'] = $dataRow['FarmAuditID'];
            $ArrayAuditList['PlantID'] = $dataRow['PlantID'];
            $ArrayAuditList['StationName'] = $dataRow['StationName'];
            $ArrayAuditList['FarmNameID'] = $dataRow['FarmNameID'];
            $ArrayAuditList['FarmName'] = $dataRow['FarmName'];
            $ArrayAuditList['FarmerNameID'] = $dataRow['FarmerNameID'];
            $ArrayAuditList['FarmerName'] = $dataRow['FarmerNames'];
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
            $ArrayAuditList['UploadFile'] = $DownloadableURLs;
            $ArrayAuditList['VerifiedByName'] = $dataRow['VerifiedByName'];
            $ArrayAuditList['Remarks'] = $dataRow['Remarks'];
            $ArrayAuditList['AuditStatus'] = $dataRow['AuditStatus'];

            $ArrayAuditList['EditURL'] = 'audit-farm-create?FarmAuditID=' . $dataRow['FarmAuditID'];
            $ArrayAuditList['ConfirmURL'] = 'audit-farm-create?FarmAuditID=' . $dataRow['FarmAuditID'] . '&ConfirmPayment=1';

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

        $PaymentData = $this->getFarmAuditPaymentData($FarmAuditID);
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['PageCount'] = $PageCount;
        $data['Data'] = $ArrayAuditData;
        $data['PaymentData'] = $PaymentData['PaymentData'];

        return $data;
    }


    function getFarmAuditPaymentData($FarmAuditID)
    {

        $sqlPaymentFields = "FarmAuditPaymentID, FarmAuditID, PurposeOfInvoice, InvoiceNo, InvoiceDate, InvoiceValue, 
        InvoiceDueDate, Others, PaymentType, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime, OptionName";

        $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcFarmAuditPaymentDetails AS QFPD
        LEFT JOIN QcAuditDropdownMasters AS QADM ON QFPD.PaymentType = QADM.OptionID
        WHERE FarmAuditID = '$FarmAuditID'";
        $stmtPaymentSelect = $this->conn->prepare($sqlPaymentSelect);
        $stmtPaymentSelect->execute();
        
        $ArrayPaymentData = [];
        $i = 0;
        while ($dataPaymentRow = $stmtPaymentSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayPaymentList['SNO'] = ++$i;
            $ArrayPaymentList['FarmAuditPaymentID'] = $dataPaymentRow['FarmAuditPaymentID'];
            $ArrayPaymentList['FarmAuditID'] = $dataPaymentRow['FarmAuditID'];
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

    function getPaymentStatus($FarmAuditID)
    {
        $sqlPaymentIds = "SELECT COUNT(*) AS TotalPaymentIDs FROM QcFarmAuditPaymentDetails
        WHERE FarmAuditID = '$FarmAuditID'";
        $stmtPaymentIDs = $this->conn->prepare($sqlPaymentIds);
        $stmtPaymentIDs->execute();
        $rowPaymentIDs = $stmtPaymentIDs->fetch(PDO::FETCH_ASSOC);
        $TotalPaymentIDs = $rowPaymentIDs['TotalPaymentIDs'];

        $sqlPaymentStatus = "SELECT COUNT(*) AS TotalCount FROM QcFarmAuditPaymentDetails 
        WHERE FarmAuditID = '$FarmAuditID' AND PaymentDate = ''";
        $stmtPaymentStatus = $this->conn->prepare($sqlPaymentStatus);
        $stmtPaymentStatus->execute();
        $rowPaymentStatus = $stmtPaymentStatus->fetch(PDO::FETCH_ASSOC);
        $PaymentStatusCount = $rowPaymentStatus['TotalCount'];

        // return $PaymentStatusCount;
        $Data['TotalPaymentIDs'] = $TotalPaymentIDs;
        $Data['PaymentStatusCount'] = $PaymentStatusCount;
        return $Data;
    }
}
