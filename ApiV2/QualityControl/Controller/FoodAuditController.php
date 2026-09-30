<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class FoodAuditController
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

    public function postFoodAuditDetails($RequestBody)
    {
        $FoodAuditID = $RequestBody['FoodAuditID'];
        $PlantID = $RequestBody['PlantID'];
        $AuditNameID = $RequestBody['AuditNameID'];
        $AuditTypeID = $RequestBody['AuditTypeID'];
        $ProductCategoryID = $RequestBody['ProductCategoryID'];
        $CertificationBodyID = $RequestBody['CertificationBodyID'];
        $RegistrationNo = $RequestBody['RegistrationNo'];
        $GradeAndScore = $RequestBody['GradeAndScore'];
        $AuditStartDate = $RequestBody['AuditStartDate'];
        $AuditEndDate = $RequestBody['AuditEndDate'];
        $IssuedOn = $RequestBody['IssuedOn'];
        $ValidUpto = $RequestBody['ValidUpto'];
        $RenewalDate = $RequestBody['RenewalDate'];
        $CheckedBy = $RequestBody['CheckedBy'];
        $VerifiedBy = $RequestBody['VerifiedBy'];
        $UploadFile = $RequestBody['UploadFile'];
        $Status = $RequestBody['Status'];
        $AuditStatus = $RequestBody['AuditStatus'];
        $Remarks = $RequestBody['Remarks'];
        $existingAudit = null;
        if ($FoodAuditID != '') {
            $existingAudit = $this->auditLogger->fetchRowById('QcFoodAuditDetails', 'FoodAuditID', $FoodAuditID);
        }

        if ($FoodAuditID == '') {
            $sqlInsert = "INSERT INTO QcFoodAuditDetails(PlantID, AuditNameID, AuditTypeID, ProductCategoryID, 
            CertificationBodyID, RegistrationNo, GradeAndScore, AuditStartDate, AuditEndDate, IssuedOn, ValidUpto, 
            RenewalDate, Status, CheckedBy, VerifiedBy, UploadFile,AuditStatus,Remarks) 
            VALUES ('$PlantID', '$AuditNameID', '$AuditTypeID', '$ProductCategoryID', 
            '$CertificationBodyID', '$RegistrationNo', '$GradeAndScore', '$AuditStartDate', '$AuditEndDate', '$IssuedOn', 
            '$ValidUpto', '$RenewalDate', '$Status', '$CheckedBy', '$VerifiedBy', '$UploadFile','$AuditStatus','$Remarks')";
            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                $this->Error = null;
                $this->Msg = 'Food Audit Posted Successfully';
                $LastInsertID = $this->conn->lastInsertId();
                $createdAudit = $this->auditLogger->fetchRowById('QcFoodAuditDetails', 'FoodAuditID', $LastInsertID);
                $this->auditLogger->logInsert('QcFoodAuditDetails', $LastInsertID, $createdAudit, $RequestBody, [
                    'ReferenceNo' => $LastInsertID,
                    'Remarks' => 'QC food audit created',
                ]);
            } else {
                $this->Error = "Unable to Post Food Audit Data";
                $this->Msg = "Unable to Post Food Audit Data";
            }
        } else {
            if ($UploadFile != '') {
                $UpdateQuery = ",UploadFile='$UploadFile'";
            } else {
                $UpdateQuery = "";
            }
            $sqlUpdate = "UPDATE QcFoodAuditDetails SET PlantID='$PlantID',AuditNameID='$AuditNameID',AuditTypeID='$AuditTypeID',
            ProductCategoryID='$ProductCategoryID',CertificationBodyID='$CertificationBodyID',RegistrationNo='$RegistrationNo',
            GradeAndScore='$GradeAndScore',AuditStartDate='$AuditStartDate',AuditEndDate='$AuditEndDate',IssuedOn='$IssuedOn',
            ValidUpto='$ValidUpto',RenewalDate='$RenewalDate',Status='$Status',CheckedBy='$CheckedBy',
            VerifiedBy='$VerifiedBy', AuditStatus='$AuditStatus',Remarks='$Remarks' $UpdateQuery WHERE FoodAuditID='$FoodAuditID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            if ($stmtUpdate) {
                $this->Error = null;
                $this->Msg = 'Food Audit Updated Successfully';
                $LastInsertID = $FoodAuditID;
                $updatedAudit = $this->auditLogger->fetchRowById('QcFoodAuditDetails', 'FoodAuditID', $FoodAuditID);
                $this->auditLogger->logUpdate('QcFoodAuditDetails', $FoodAuditID, $existingAudit, $updatedAudit, $RequestBody, [
                    'ReferenceNo' => $FoodAuditID,
                    'Remarks' => 'QC food audit updated',
                ]);
            } else {
                $this->Error = "Unable to Update Food Audit Data";
                $this->Msg = "Unable to Update Food Audit Data";
            }
        }

        $PaymentData = $this->getFoodAuditPaymentData($FoodAuditID);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['LastInserted'] = $LastInsertID;
        $Data['PaymentData'] = $PaymentData['PaymentData'];

        return $Data;
    }

    function getFoodAuditDetails($RequestBody)
    {

        $FoodAuditID = $RequestBody['FoodAuditID'];
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
            $Query .= " and CONCAT_WS('', FoodAuditID, QFAD.PlantID, QFAD.AuditNameID, AuditTypeID, ProductCategoryID, QFAD.CertificationBodyID, 
        RegistrationNo, GradeAndScore, AuditStartDate, AuditEndDate, IssuedOn, ValidUpto, RenewalDate, QFAD.Status, 
        CheckedBy, VerifiedBy, UploadFile, RPS.StationName, QACM.CategoryName, QACOM.OptionName, AuditStatus, QFAD.Remarks) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(*) AS TotalCount FROM QcFoodAuditDetails AS QFAD 
        LEFT JOIN QcAuditCategoriesMaster AS QACM ON QFAD.AuditNameID = QACM.AuditID
        LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QFAD.CertificationBodyID = QACBM.CertificationBodyID
        LEFT JOIN QcAuditCategoriesOptionsMaster AS QACOM ON QFAD.AuditTypeID = QACOM.AuditOptionID
        LEFT JOIN RmProcessingStations AS RPS ON QFAD.PlantID = RPS.ProcessingStationID
        LEFT JOIN QcLabTechnologist AS QLT ON QFAD.CheckedBy = QLT.LUID
        LEFT JOIN QcLabTechnologist AS QLT2 ON QFAD.VerifiedBy = QLT2.LUID
        WHERE AuditStartDate $DateFilters $Query ";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataCount['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);


        $sqlFields = "FoodAuditID, QFAD.PlantID, QFAD.AuditNameID, AuditTypeID, ProductCategoryID, QFAD.CertificationBodyID, 
        RegistrationNo, GradeAndScore, AuditStartDate, AuditEndDate, IssuedOn, ValidUpto, RenewalDate, QFAD.Status, 
        CheckedBy, VerifiedBy, UploadFile, RPS.StationName, QACM.CategoryName AS CategoryName, QACOM.OptionName, AuditStatus, QFAD.Remarks,
        QLT.LbuName AS CheckedByName, QLT2.LbuName AS VerifiedByName, QACBM.CertificationBodyName";

        if ($FoodAuditID != '') {
            $sqlSelect = "SELECT $sqlFields FROM QcFoodAuditDetails AS QFAD 
            LEFT JOIN QcAuditCategoriesMaster AS QACM ON QFAD.AuditNameID = QACM.AuditID
            LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QFAD.CertificationBodyID = QACBM.CertificationBodyID
            LEFT JOIN QcAuditCategoriesOptionsMaster AS QACOM ON QFAD.AuditTypeID = QACOM.AuditOptionID
            LEFT JOIN RmProcessingStations AS RPS ON QFAD.PlantID = RPS.ProcessingStationID
            LEFT JOIN QcLabTechnologist AS QLT ON QFAD.CheckedBy = QLT.LUID
            LEFT JOIN QcLabTechnologist AS QLT2 ON QFAD.VerifiedBy = QLT2.LUID
            WHERE FoodAuditID = '$FoodAuditID'";
        } else {
            $sqlSelect = "SELECT $sqlFields FROM QcFoodAuditDetails AS QFAD 
            LEFT JOIN QcAuditCategoriesMaster AS QACM ON QFAD.AuditNameID = QACM.AuditID
            LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QFAD.CertificationBodyID = QACBM.CertificationBodyID
            LEFT JOIN QcAuditCategoriesOptionsMaster AS QACOM ON QFAD.AuditTypeID = QACOM.AuditOptionID
            LEFT JOIN RmProcessingStations AS RPS ON QFAD.PlantID = RPS.ProcessingStationID
            LEFT JOIN QcLabTechnologist AS QLT ON QFAD.CheckedBy = QLT.LUID
            LEFT JOIN QcLabTechnologist AS QLT2 ON QFAD.VerifiedBy = QLT2.LUID
            WHERE AuditStartDate $DateFilters $Query " . $Limit;
        }

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ArrayAuditData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $CurrentDate = new DateTime(date('Y-m-d'));
            // $decodedUploadFile = json_decode($dataRow['UploadFile'], true);

            // // $baseURL = 'https://staging-server.miledeepworks.in/v3/QualityControl/';
            // $path = '/v3/QualityControl/';
            // $baseURL = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $path;
            // $DownloadableURLs = [];
            // foreach ($decodedUploadFile as $filePath) {
            //     $cleanedFilePath = str_replace('../', '', $filePath);
            //     $DownloadableURLs[] = $baseURL . $cleanedFilePath;
            // }


            
            $decodedUploadFile = json_decode($dataRow['UploadFile'], true);

            // Use the correct public URL for uploads
            $baseURL = 'https://camaroo.in/v3/QualityControl/Uploads/';

            $DownloadableURLs = [];

            if (is_array($decodedUploadFile)) {
                foreach ($decodedUploadFile as $filePath) {
                    // Only the file name, remove any folder in DB path
                    $fileName = basename($filePath);

                    // Encode spaces and special characters correctly for file URLs
                    $DownloadableURLs[] = $baseURL . rawurlencode($fileName);
                }
            }

            $PaymentStatus = $this->getPaymentStatus($dataRow['FoodAuditID']);
            $ArrayAuditList['SNO'] = ++$i;
            $ArrayAuditList['FoodAuditID'] = $dataRow['FoodAuditID'];
            $ArrayAuditList['PlantID'] = $dataRow['PlantID'];
            $ArrayAuditList['StationName'] = $dataRow['StationName'];
            $ArrayAuditList['AuditNameID'] = $dataRow['AuditNameID'];
            $ArrayAuditList['CategoryName'] = $dataRow['CategoryName'];
            $ArrayAuditList['AuditTypeID'] = $dataRow['AuditTypeID'];
            $ArrayAuditList['OptionName'] = $dataRow['OptionName'];
            $ArrayAuditList['ProductCategoryID'] = $dataRow['ProductCategoryID'];
            $ArrayAuditList['CertificationBodyID'] = $dataRow['CertificationBodyID'];
            $ArrayAuditList['CertificationBodyName'] = $dataRow['CertificationBodyName'];
            $ArrayAuditList['RegistrationNo'] = $dataRow['RegistrationNo'];
            $ArrayAuditList['GradeAndScore'] = $dataRow['GradeAndScore'];
            $ArrayAuditList['AuditStartDate'] = $dataRow['AuditStartDate'];
            $ArrayAuditList['AuditEndDate'] = $dataRow['AuditEndDate'];
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

            $ArrayAuditList['EditURL'] = 'food-audit-create?FoodAuditID=' . $dataRow['FoodAuditID'];
            $ArrayAuditList['ConfirmURL'] = 'food-audit-create?FoodAuditID=' . $dataRow['FoodAuditID'] . '&ConfirmPayment=1';

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
            $ArrayAuditList['PaymentStatusCount'] = $PaymentStatusCount;
            $ArrayAuditList['TotalPaymentIDs'] = $TotalPaymentIDs;

            $ArrayAuditData[] = $ArrayAuditList;
        }

        $PaymentData = $this->getFoodAuditPaymentData($FoodAuditID);
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['PageCount'] = $PageCount;
        $data['Data'] = $ArrayAuditData;
        $data['PaymentData'] = $PaymentData['PaymentData'];

        return $data;
    }

    function getFoodAuditPaymentData($FoodAuditID)
    {
        $sqlPaymentFields = "FoodAuditPaymentID, FoodAuditID, PurposeOfInvoice, PaymentType, InvoiceNumber, InvoiceDate, 
        InvoiceValue, InvoiceDueDate,Others, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime, OptionName";

        $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcFoodAuditPaymentDetails AS QFAPD
        LEFT JOIN QcAuditDropdownMasters AS QADM ON QFAPD.PaymentType = QADM.OptionID
        WHERE FoodAuditID = '$FoodAuditID'";
        $stmtPaymentSelect = $this->conn->prepare($sqlPaymentSelect);
        $stmtPaymentSelect->execute();

        $ArrayPaymentData = [];
        $i = 0;
        while ($dataPaymentRow = $stmtPaymentSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayPaymentList['SNO'] = ++$i;
            $ArrayPaymentList['FoodAuditPaymentID'] = $dataPaymentRow['FoodAuditPaymentID'];
            $ArrayPaymentList['FoodAuditID'] = $dataPaymentRow['FoodAuditID'];
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

    function getPaymentStatus($FoodAuditID)
    {
        $sqlPaymentIds = "SELECT COUNT(*) AS TotalPaymentIDs FROM QcFoodAuditPaymentDetails
        WHERE FoodAuditID = '$FoodAuditID'";
        $stmtPaymentIDs = $this->conn->prepare($sqlPaymentIds);
        $stmtPaymentIDs->execute();
        $rowPaymentIDs = $stmtPaymentIDs->fetch(PDO::FETCH_ASSOC);
        $TotalPaymentIDs = $rowPaymentIDs['TotalPaymentIDs'];

        $sqlPaymentStatus = "SELECT COUNT(*) AS TotalCount FROM QcFoodAuditPaymentDetails 
        WHERE FoodAuditID = '$FoodAuditID' AND PaymentDate = ''";
        $stmtPaymentStatus = $this->conn->prepare($sqlPaymentStatus);
        $stmtPaymentStatus->execute();
        $rowPaymentStatus = $stmtPaymentStatus->fetch(PDO::FETCH_ASSOC);
        $PaymentStatusCount = $rowPaymentStatus['TotalCount'];

        $Data['TotalPaymentIDs'] = $TotalPaymentIDs;
        $Data['PaymentStatusCount'] = $PaymentStatusCount;
        return $Data;
    }
}
