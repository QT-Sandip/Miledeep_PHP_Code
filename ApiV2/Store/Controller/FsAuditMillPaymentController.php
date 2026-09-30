<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class FsAuditMillPaymentController
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

    public function postFsAuditMillPaymentDetails($RequestBody)
    {
        $PaymentID = $RequestBody['PaymentID'];

        if ($PaymentID == '') {
            $Data = $this->insertFsAuditMillPaymentDetails($RequestBody);
        } else {
            $Data = $this->updateFsAuditMillPaymentDetails($RequestBody);
        }

        return $Data;
    }

    public function insertFsAuditMillPaymentDetails($RequestBody)
    {
        $PaymentID = $RequestBody['PaymentID'];
        $FHFAuditID = $RequestBody['FHFAuditID'];
        $PaymentTypeID = $RequestBody['PaymentTypeID'];
        $InvoiceDate = $RequestBody['InvoiceDate'];
        $InvoiceNumber = $RequestBody['InvoiceNumber'];
        $InvoiceValue = $RequestBody['InvoiceValue'];
        $PaymentDt = $RequestBody['PaymentDt'];
        $TotalAmount = $RequestBody['TotalAmount'];
        $Remarks = $RequestBody['Remarks'];

        $sqlInsert = "INSERT INTO GsAuditFHFPaymentDetails(PaymentID, FHFAuditID, PaymentTypeID, InvoiceDate, InvoiceNumber, InvoiceValue, 
        PaymentDt, TotalAmount, Remarks) 
        VALUES ('$PaymentID','$FHFAuditID','$PaymentTypeID','$InvoiceDate','$InvoiceNumber','$InvoiceValue','$PaymentDt',
        '$TotalAmount', '$Remarks')";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        // $errorInfo = $stmtInsert->errorInfo();
        // var_dump($errorInfo);

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['PaymentID'] = $lastInsertedId;
            $createdPayment = $this->auditLogger->fetchRowById('GsAuditFHFPaymentDetails', 'PaymentID', $lastInsertedId);
            $this->auditLogger->logInsert('GsAuditFHFPaymentDetails', $lastInsertedId, $createdPayment, $RequestBody, [
                'ReferenceNo' => $FHFAuditID,
                'Remarks' => 'Store FHF audit payment created',
            ]);

            $fsAuditMillPmtData = $this->getFsAuditMillPmtDetails($RequestBody);
            $TotalData = $this->getPaymtTotals($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'FS Mill Payment Posted Successfully';
            $Data['Data'] = $fsAuditMillPmtData['Data'];
            $Data['Total'] = $TotalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert FS Mill Payment';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateFsAuditMillPaymentDetails($RequestBody)
    {
        $PaymentID = $RequestBody['PaymentID'];
        $FHFAuditID = $RequestBody['FHFAuditID'];
        $PaymentTypeID = $RequestBody['PaymentTypeID'];
        $InvoiceDate = $RequestBody['InvoiceDate'];
        $InvoiceNumber = $RequestBody['InvoiceNumber'];
        $InvoiceValue = $RequestBody['InvoiceValue'];
        $PaymentDt = $RequestBody['PaymentDt'];
        $TotalAmount = $RequestBody['TotalAmount'];
        $Remarks = $RequestBody['Remarks'];
        $existingPayment = $this->auditLogger->fetchRowById('GsAuditFHFPaymentDetails', 'PaymentID', $PaymentID);

        $sqlUpdate = "UPDATE GsAuditFHFPaymentDetails SET FHFAuditID='$FHFAuditID', PaymentTypeID='$PaymentTypeID',
        InvoiceDate='$InvoiceDate', InvoiceNumber='$InvoiceNumber', InvoiceValue='$InvoiceValue', PaymentDt='$PaymentDt',
        TotalAmount='$TotalAmount', Remarks='$Remarks'
        WHERE PaymentID='$PaymentID'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        // $errorInfo = $stmtUpdate->errorInfo();
        // var_dump($errorInfo);

        if ($stmtUpdate) {
            $updatedPayment = $this->auditLogger->fetchRowById('GsAuditFHFPaymentDetails', 'PaymentID', $PaymentID);
            $this->auditLogger->logUpdate('GsAuditFHFPaymentDetails', $PaymentID, $existingPayment, $updatedPayment, $RequestBody, [
                'ReferenceNo' => $FHFAuditID,
                'Remarks' => 'Store FHF audit payment updated',
            ]);
            $fsAuditMillPmtData = $this->getFsAuditMillPmtDetails($RequestBody);
            $TotalData = $this->getPaymtTotals($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'FS Mill Payment Updated Successfully';
            $Data['Data'] = $fsAuditMillPmtData['Data'];
            $Data['Total'] = $TotalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update FS Mill Payment';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function deleteFsAuditMillPmtDetails($RequestBody)
    {
        $PaymentID = $RequestBody['PaymentID'];
        $existingPayment = $this->auditLogger->fetchRowById('GsAuditFHFPaymentDetails', 'PaymentID', $PaymentID);

        $sqlDelete = "DELETE FROM `GsAuditFHFPaymentDetails` WHERE PaymentID='$PaymentID'";

        $stmtUpdate = $this->conn->prepare($sqlDelete);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $this->auditLogger->logDelete('GsAuditFHFPaymentDetails', $PaymentID, $existingPayment, $RequestBody, [
                'ReferenceNo' => isset($existingPayment['FHFAuditID']) ? $existingPayment['FHFAuditID'] : $PaymentID,
                'Remarks' => 'Store FHF audit payment deleted',
            ]);
            $fsAuditMillPmtData = $this->getFsAuditMillPmtDetails($RequestBody);
            $TotalData = $this->getPaymtTotals($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'FS Mill Payment Deleted Successfully';
            $Data['Data'] = $fsAuditMillPmtData['Data'];
            $Data['Total'] = $TotalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Delete FS Mill Payment';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function getFsAuditMillPmtDetails($RequestBody)
    {

        $FHFAuditID = $RequestBody['FHFAuditID'];

        $sqlFields = "GAHPD.PaymentID, GAHPD.FHFAuditID, GAHPD.PaymentTypeID, GAHPD.InvoiceDate, GAHPD.InvoiceNumber, GAHPD.InvoiceValue, GAHPD.PaymentDt, GAHPD.Others, 
        GAHPD.TotalAmount, GAHPD.Remarks, GPNM.PaymentName";

        if ($FHFAuditID != '') {

            $sqlQuery = "SELECT $sqlFields FROM GsAuditFHFPaymentDetails AS GAHPD
            LEFT JOIN GsPaymentNamesMaster AS GPNM ON GAHPD.PaymentTypeID = GPNM.PaymentID
            WHERE GAHPD.FHFAuditID='$FHFAuditID'";
        } else {

            $sqlQuery = "SELECT $sqlFields FROM GsAuditFHFPaymentDetails AS GAHPD
            LEFT JOIN GsPaymentNamesMaster AS GPNM ON GAHPD.PaymentTypeID = GPNM.PaymentID
            WHERE 1=1 ORDER BY FHFAuditID ";
        }
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayFsAuditMillPaymentData = [];

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayFsAuditMillList['SNO'] = ++$i;
            $ArrayFsAuditMillList['PaymentID'] = $dataRow['PaymentID'];
            $ArrayFsAuditMillList['FHFAuditID'] = $dataRow['FHFAuditID'];
            $ArrayFsAuditMillList['PaymentTypeID'] = $dataRow['PaymentTypeID'];
            $ArrayFsAuditMillList['PaymentName'] = $dataRow['PaymentName'];
            $ArrayFsAuditMillList['InvoiceDate'] = $dataRow['InvoiceDate'];
            $ArrayFsAuditMillList['InvoiceNumber'] = $dataRow['InvoiceNumber'];
            $ArrayFsAuditMillList['InvoiceValue'] = $dataRow['InvoiceValue'];
            $ArrayFsAuditMillList['PaymentDt'] = $dataRow['PaymentDt'];
            // $ArrayFsAuditMillList['Others'] = $dataRow['Others'];
            $ArrayFsAuditMillList['TotalAmount'] = $dataRow['TotalAmount'];
            $ArrayFsAuditMillList['Remarks'] = $dataRow['Remarks'];

            $ArrayFsAuditMillPaymentData[] =  $ArrayFsAuditMillList;
        }

        $TotalData = $this->getPaymtTotals($RequestBody);
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['EditLink'] = "";
        $data['Data'] = $ArrayFsAuditMillPaymentData;
        $data['Total'] = $TotalData['Data'];

        return $data;
    }
    function getPaymtTotals($RequestBody)
    {

        $FHFAuditID = $RequestBody['FHFAuditID'];

        $sqlTotal = "SELECT sum(TotalAmount) AS TotalAmt FROM GsAuditFHFPaymentDetails WHERE FHFAuditID='$FHFAuditID'";

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
