<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class FsAuditPaymentController
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
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Store');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postFsAuditPaymentDetails($RequestBody)
    {
        $AuditPayID = $RequestBody['AuditPayID'];

        if ($AuditPayID == '') {
            $Data = $this->insertFsAuditPaymentDetails($RequestBody);
        } else {
            $Data = $this->updateFsAuditPaymentDetails($RequestBody);
        }

        return $Data;
    }

    public function insertFsAuditPaymentDetails($RequestBody)
    {
        $AuditPayID = $RequestBody['AuditPayID'];
        $AuditID = $RequestBody['AuditID'];
        $PaymentTypeID = $RequestBody['PaymentTypeID'];
        $InvoiceDate = $RequestBody['InvoiceDate'];
        $InvoiceNumber = $RequestBody['InvoiceNumber'];
        $InvoiceDue = $RequestBody['InvoiceDue'];
        $InvoiceValue = $RequestBody['InvoiceValue'];
        $PaymentDate = $RequestBody['PaymentDate'];
        $TotalAmount = $RequestBody['TotalAmount'];
        $Remarks = $RequestBody['Remarks'];
        $VerifiedBy = $RequestBody['VerifiedBy']; 
        $SecndVerifiedBy = $RequestBody['SecndVerifiedBy'];

        $sqlInsert = "INSERT INTO GsAuditPaymentDetails(AuditPayID, AuditID, PaymentTypeID, InvoiceDate, InvoiceNumber, InvoiceDue, 
        InvoiceValue, PaymentDate, TotalAmount, Remarks, VerifiedBy, SecndVerifiedBy) 
        VALUES ('$AuditPayID','$AuditID','$PaymentTypeID','$InvoiceDate','$InvoiceNumber','$InvoiceDue','$InvoiceValue','$PaymentDate',
        '$TotalAmount', '$Remarks', '$VerifiedBy', '$SecndVerifiedBy')";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['AuditPayID'] = $lastInsertedId;
            $createdPayment = $this->auditLogger->fetchRowById('GsAuditPaymentDetails', 'AuditPayID', $lastInsertedId);
            $this->auditLogger->logInsert('GsAuditPaymentDetails', $lastInsertedId, $createdPayment, $RequestBody, [
                'ReferenceNo' => $AuditID,
                'Remarks' => 'Store FS audit payment created',
            ]);

            $fsAuditPaymentData = $this->getFsAuditPaymentDetails($RequestBody);
            $TotalData = $this->getPaymtTotals($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'FS Payment Posted Successfully';
            $Data['Data'] = $fsAuditPaymentData['Data'];
            $Data['Total'] = $TotalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert FS Payment';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateFsAuditPaymentDetails($RequestBody)
    {
        $AuditPayID = $RequestBody['AuditPayID'];
        $AuditID = $RequestBody['AuditID'];
        $PaymentTypeID = $RequestBody['PaymentTypeID'];
        $InvoiceDate = $RequestBody['InvoiceDate'];
        $InvoiceNumber = $RequestBody['InvoiceNumber'];
        $InvoiceDue = $RequestBody['InvoiceDue'];
        $InvoiceValue = $RequestBody['InvoiceValue'];
        $PaymentDate = $RequestBody['PaymentDate'];
        $TotalAmount = $RequestBody['TotalAmount'];
        $Remarks = $RequestBody['Remarks'];
        $VerifiedBy = $RequestBody['VerifiedBy']; 
        $SecndVerifiedBy = $RequestBody['SecndVerifiedBy'];
        $existingPayment = $this->auditLogger->fetchRowById('GsAuditPaymentDetails', 'AuditPayID', $AuditPayID);
// echo 
        $sqlUpdate = "UPDATE GsAuditPaymentDetails SET AuditID='$AuditID', PaymentTypeID='$PaymentTypeID',
        InvoiceDate='$InvoiceDate', InvoiceNumber='$InvoiceNumber', InvoiceDue='$InvoiceDue', InvoiceValue='$InvoiceValue',
        PaymentDate='$PaymentDate', TotalAmount='$TotalAmount', Remarks='$Remarks', VerifiedBy='$VerifiedBy', SecndVerifiedBy='$SecndVerifiedBy'
        WHERE AuditPayID='$AuditPayID'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        // $errorInfo = $stmtUpdate->errorInfo();
        // var_dump($errorInfo);

        if ($stmtUpdate) {
            $updatedPayment = $this->auditLogger->fetchRowById('GsAuditPaymentDetails', 'AuditPayID', $AuditPayID);
            $this->auditLogger->logUpdate('GsAuditPaymentDetails', $AuditPayID, $existingPayment, $updatedPayment, $RequestBody, [
                'ReferenceNo' => $AuditID,
                'Remarks' => 'Store FS audit payment updated',
            ]);
            $fsAuditPaymentData = $this->getFsAuditPaymentDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'FS Payment Updated Successfully';
            $Data['Data'] = $fsAuditPaymentData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update FS Payment';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function deleteFsAuditPaymentDetails($RequestBody)
    {
        $AuditPayID = $RequestBody['AuditPayID'];
        $existingPayment = $this->auditLogger->fetchRowById('GsAuditPaymentDetails', 'AuditPayID', $AuditPayID);

        $sqlDelete = "DELETE FROM `GsAuditPaymentDetails` WHERE AuditPayID='$AuditPayID'";

        $stmtUpdate = $this->conn->prepare($sqlDelete);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $this->auditLogger->logDelete('GsAuditPaymentDetails', $AuditPayID, $existingPayment, $RequestBody, [
                'ReferenceNo' => isset($existingPayment['AuditID']) ? $existingPayment['AuditID'] : $AuditPayID,
                'Remarks' => 'Store FS audit payment deleted',
            ]);
            $fsAuditData = $this->getFsAuditPaymentDetails($RequestBody);
            $TotalData = $this->getPaymtTotals($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'FS Payment Deleted Successfully';
            $Data['Data'] = $fsAuditData['Data'];
            $Data['Total'] = $TotalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Delete FS Payment';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function getFsAuditPaymentDetails($RequestBody)
    {

        $AuditID = $RequestBody['AuditID'];

        $sqlFields = "GAPD.AuditPayID, GAPD.AuditID, GAPD.PaymentTypeID, GPNM.PaymentName,
        GAPD.InvoiceDate, GAPD.InvoiceNumber, GAPD.InvoiceDue, GAPD.InvoiceValue, GAPD.PaymentDate, GAPD.TotalAmount, GAPD.Remarks, 
        GAPD.VerifiedBy, CASE GAPD.VerifiedBy WHEN 1 THEN 'Verify A' WHEN 2 THEN 'Verify B' ELSE '' END AS Verify,
        GAPD.SecndVerifiedBy, CASE GAPD.SecndVerifiedBy WHEN 1 THEN 'Verify C' WHEN 2 THEN 'Verify D' ELSE '' END AS SecVerify";

        if ($AuditID != '') {

            $sqlQuery = "SELECT $sqlFields FROM GsAuditPaymentDetails AS GAPD
            LEFT JOIN GsPaymentNamesMaster AS GPNM ON GAPD.PaymentTypeID = GPNM.PaymentID
            WHERE AuditID='$AuditID'";

        } else {

            $sqlQuery = "SELECT $sqlFields FROM GsAuditPaymentDetails AS GAPD
            LEFT JOIN GsPaymentNamesMaster AS GPNM ON GAPD.PaymentTypeID = GPNM.PaymentID
            WHERE 1=1 ORDER BY AuditID ";

        }
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayFsPaymentData = [];

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayFsPaymentList['SNO'] = ++$i;
            $ArrayFsPaymentList['AuditPayID'] = $dataRow['AuditPayID'];
            $ArrayFsPaymentList['AuditID'] = $dataRow['AuditID'];
            $ArrayFsPaymentList['PaymentTypeID'] = $dataRow['PaymentTypeID'];
            $ArrayFsPaymentList['PaymentName'] = $dataRow['PaymentName'];
            $ArrayFsPaymentList['InvoiceDate'] = $dataRow['InvoiceDate'];
            $ArrayFsPaymentList['InvoiceNumber'] = $dataRow['InvoiceNumber'];
            $ArrayFsPaymentList['InvoiceDue'] = $dataRow['InvoiceDue'];
            $ArrayFsPaymentList['InvoiceValue'] = $dataRow['InvoiceValue'];
            $ArrayFsPaymentList['PaymentDate'] = $dataRow['PaymentDate'];
            $ArrayFsPaymentList['TotalAmount'] = $dataRow['TotalAmount'];
            $ArrayFsPaymentList['Remarks'] = $dataRow['Remarks'];
            $ArrayFsPaymentList['VerifiedBy'] = $dataRow['VerifiedBy'];
            $ArrayFsPaymentList['Verify'] = $dataRow['Verify'];
            $ArrayFsPaymentList['SecndVerifiedBy'] = $dataRow['SecndVerifiedBy'];
            $ArrayFsPaymentList['SecVerify'] = $dataRow['SecVerify'];

            $ArrayFsPaymentData[] =  $ArrayFsPaymentList;
        }

        $TotalData = $this->getPaymtTotals($RequestBody);
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['EditLink'] = "#";
        $data['Data'] = $ArrayFsPaymentData;
        $data['Total'] = $TotalData['Data'];
        
        return $data;
    }

    function getPaymtTotals($RequestBody) {

        $AuditID = $RequestBody['AuditID'];

        $sqlTotal = "SELECT sum(TotalAmount) AS TotalAmt FROM GsAuditPaymentDetails WHERE AuditID='$AuditID'";

        $stmtTotal = $this->conn->prepare($sqlTotal);
        $stmtTotal->execute();

        $ArrayTotal = [];

        while ($dataRow = $stmtTotal->fetch(PDO::FETCH_ASSOC)) {
            $ArrayTotalData['TotalAmt'] = $dataRow['TotalAmt'];
            
            $ArrayTotal[] = $ArrayTotalData;
            
        }

        $data['Data'] = $ArrayTotal;
        
        return $data;

    }

}
