<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class FoodAuditReportController
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

    function getFoodAuditDetails($RequestBody)
    {
        $AuditNameID = $RequestBody['AuditNameID'];
        $PlantID = $RequestBody['PlantID'];
        $Year = $RequestBody['Year'];
        // $Month = $RequestBody['Month'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $StartDate = $Year . '-04-01';
        $EndDate = ($Year + 1) . '-03-31';

        $Query = '';
        if ($AuditNameID != '') {
            $Query .= " AND QFAD.AuditNameID = '$AuditNameID'";
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
            $Query .= " and CONCAT_WS('', FoodAuditID, QFAD.PlantID, QFAD.AuditNameID, AuditTypeID, ProductCategoryID, QFAD.CertificationBodyID, 
            RegistrationNo, GradeAndScore, AuditStartDate, AuditEndDate, IssuedOn, ValidUpto, RenewalDate, QFAD.Status, 
            CheckedBy, VerifiedBy, UploadFile, RPS.StationName, QACM.CategoryName, QACOM.OptionName, AuditStatus, QFAD.Remarks,QACBM.CertificationBodyName) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(*) AS TotalCount FROM QcFoodAuditDetails AS QFAD 
        LEFT JOIN QcAuditCategoriesMaster AS QACM ON QFAD.AuditNameID = QACM.AuditID
        LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QFAD.CertificationBodyID = QACBM.CertificationBodyID
        LEFT JOIN QcAuditCategoriesOptionsMaster AS QACOM ON QFAD.AuditTypeID = QACOM.AuditOptionID
        LEFT JOIN RmProcessingStations AS RPS ON QFAD.PlantID = RPS.ProcessingStationID
        LEFT JOIN QcLabTechnologist AS QLT ON QFAD.CheckedBy = QLT.LUID
        LEFT JOIN QcLabTechnologist AS QLT2 ON QFAD.VerifiedBy = QLT2.LUID
        WHERE IssuedOn $DateFilters $Query ";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataCount['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);


        $sqlFields = "FoodAuditID, QFAD.PlantID, QFAD.AuditNameID, AuditTypeID, ProductCategoryID, QFAD.CertificationBodyID, 
        RegistrationNo, GradeAndScore, AuditStartDate, AuditEndDate, IssuedOn, ValidUpto, RenewalDate, QFAD.Status, 
        CheckedBy, VerifiedBy, UploadFile, RPS.StationName, QACM.CategoryName AS CategoryName, QACOM.OptionName, AuditStatus, QFAD.Remarks,
        QLT.LbuName AS CheckedByName, QLT2.LbuName AS VerifiedByName, QACBM.CertificationBodyName";

        $sqlSelect = "SELECT $sqlFields FROM QcFoodAuditDetails AS QFAD 
        LEFT JOIN QcAuditCategoriesMaster AS QACM ON QFAD.AuditNameID = QACM.AuditID
        LEFT JOIN QcAuditCertificationBodyMaster AS QACBM ON QFAD.CertificationBodyID = QACBM.CertificationBodyID
        LEFT JOIN QcAuditCategoriesOptionsMaster AS QACOM ON QFAD.AuditTypeID = QACOM.AuditOptionID
        LEFT JOIN RmProcessingStations AS RPS ON QFAD.PlantID = RPS.ProcessingStationID
        LEFT JOIN QcLabTechnologist AS QLT ON QFAD.CheckedBy = QLT.LUID
        LEFT JOIN QcLabTechnologist AS QLT2 ON QFAD.VerifiedBy = QLT2.LUID
        WHERE IssuedOn $DateFilters $Query " . $Limit;

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayAuditData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $CurrentDate = new DateTime(date('Y-m-d'));

            $PaymentData = $this->getFoodAuditPaymentData($dataRow['FoodAuditID']);

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





            // $ArrayAuditList['UploadFile'] = $dataRow['UploadFile'];

            $ArrayAuditList['UploadFile'] = $DownloadableURLs;

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


    // function getFoodAuditPaymentData($FoodAuditID)
    // {

    //     $sqlPaymentFields = "FoodAuditPaymentID, FoodAuditID, PurposeOfInvoice, PaymentType, InvoiceNumber, InvoiceDate, 
    //     InvoiceValue, InvoiceDueDate,Remarks, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime, OptionName";

    //     $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcFoodAuditPaymentDetails AS QFAPD
    //     LEFT JOIN QcAuditDropdownMasters AS QADM ON QADM.OptionID = QFAPD.PaymentType
    //     WHERE FoodAuditID = '$FoodAuditID'";
    //     $stmtPaymentSelect = $this->conn->prepare($sqlPaymentSelect);
    //     $stmtPaymentSelect->execute();
    //     // $errorInfo = $stmtPaymentSelect->errorInfo();
    //     // var_dump($errorInfo);

    //     $ArrayPaymentData = [];
    //     $i = 0;
    //     $PaymentTotalAmount = 0;
    //     while ($dataPaymentRow = $stmtPaymentSelect->fetch(PDO::FETCH_ASSOC)) {
    //         $ArrayPaymentList['SNO'] = ++$i;
    //         $ArrayPaymentList['FoodAuditPaymentID'] = $dataPaymentRow['FoodAuditPaymentID'];
    //         $ArrayPaymentList['FoodAuditID'] = $dataPaymentRow['FoodAuditID'];
    //         $ArrayPaymentList['PurposeOfInvoice'] = $dataPaymentRow['PurposeOfInvoice'];
    //         $ArrayPaymentList['PaymentType'] = $dataPaymentRow['PaymentType'];
    //         $ArrayPaymentList['OptionName'] = $dataPaymentRow['OptionName'];
    //         $ArrayPaymentList['InvoiceNumber'] = $dataPaymentRow['InvoiceNumber'];
    //         $ArrayPaymentList['InvoiceDate'] = $dataPaymentRow['InvoiceDate'];
    //         $ArrayPaymentList['InvoiceValue'] = $dataPaymentRow['InvoiceValue'];
    //         $ArrayPaymentList['InvoiceDueDate'] = $dataPaymentRow['InvoiceDueDate'];
    //         $ArrayPaymentList['Remarks'] = $dataPaymentRow['Remarks'];
    //         $ArrayPaymentList['TotalAmount'] = $dataPaymentRow['TotalAmount'];
    //         $TotalAmount = $dataPaymentRow['TotalAmount'];
    //         $ArrayPaymentList['PaymentDate'] = $dataPaymentRow['PaymentDate'];
    //         $ArrayPaymentList['PaymentRemarks'] = $dataPaymentRow['PaymentRemarks'];
    //         $ArrayPaymentList['PostedDatetime'] = $dataPaymentRow['PostedDatetime'];

    //         $PaymentTotalAmount += $TotalAmount;
    //         $ArrayPaymentData[] = $ArrayPaymentList;
    //     }

    //     $Data['PaymentData'] = $ArrayPaymentData;
    //     $Data['PaymentTotalAmount'] = $PaymentTotalAmount;

    //     return $Data;
    // }


        function getFoodAuditPaymentData($FoodAuditID)
{
    $sqlPaymentFields = "FoodAuditPaymentID, FoodAuditID, PurposeOfInvoice, PaymentType, InvoiceNumber, InvoiceDate, 
        InvoiceValue, InvoiceDueDate, Others, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime, OptionName";

    $sqlPaymentSelect = "SELECT $sqlPaymentFields 
                         FROM QcFoodAuditPaymentDetails AS QFAPD
                         LEFT JOIN QcAuditDropdownMasters AS QADM ON QFAPD.PaymentType = QADM.OptionID
                         WHERE FoodAuditID = '$FoodAuditID'";

    $stmtPaymentSelect = $this->conn->prepare($sqlPaymentSelect);
    $stmtPaymentSelect->execute();

    $ArrayPaymentData = [];
    $i = 0;
    $PaymentTotalAmount = 0;

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

        // Remove commas and convert to float for correct addition
        $TotalAmount = floatval(str_replace(',', '', $dataPaymentRow['TotalAmount']));
        $ArrayPaymentList['TotalAmount'] = $TotalAmount;

        $ArrayPaymentList['PaymentDate'] = $dataPaymentRow['PaymentDate'];
        $ArrayPaymentList['PaymentRemarks'] = $dataPaymentRow['PaymentRemarks'];
        $ArrayPaymentList['PostedDatetime'] = $dataPaymentRow['PostedDatetime'];

        $PaymentTotalAmount += $TotalAmount;

        $ArrayPaymentData[] = $ArrayPaymentList;
    }

    $Data['Error'] = null;
    $Data['Msg'] = 'Success';
    $Data['PaymentData'] = $ArrayPaymentData;
    $Data['PaymentTotalAmount'] = $PaymentTotalAmount;

    return $Data;
}
}
