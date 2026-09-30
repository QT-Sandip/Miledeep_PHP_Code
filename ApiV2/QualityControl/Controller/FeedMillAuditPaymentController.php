<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class FeedMillAuditPaymentController
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

    public function postFeedMillAuditPaymentDetails($RequestBody)
    {
        $FeedMillAuditPaymentID = $RequestBody['FeedMillAuditPaymentID'];
        $FeedMillAuditID = $RequestBody['FeedMillAuditID'];
        $PurposeOfInvoice = $RequestBody['PurposeOfInvoice'];
        $PaymentType = $RequestBody['PaymentType'];
        $InvoiceNo = $RequestBody['InvoiceNo'];
        $InvoiceDate = $RequestBody['InvoidDate'];
        $InvoiceValue = $RequestBody['InvoiceValue'];
        $InvoiceDueDate = $RequestBody['InvoiceDueDate'];
        $Others = $RequestBody['Others'];
        $TotalAmount = $RequestBody['TotalAmount'];
        $PaymentDate = $RequestBody['PaymentDate'];
        $PaymentRemarks = $RequestBody['PaymentRemarks'];
        $ConfirmPayment = $RequestBody['ConfirmPayment'];

        date_default_timezone_set("Asia/Kolkata");
        $PostedDatetime = date("Y-m-d H:i:s");
        $existingPayment = null;
        if ($FeedMillAuditPaymentID != '') {
            $existingPayment = $this->auditLogger->fetchRowById('QcFeedMillAuditPaymentDetails', 'FeedMillAuditPaymentID', $FeedMillAuditPaymentID);
        }

        if ($FeedMillAuditPaymentID == '') {

            $sqlInsert = "INSERT INTO QcFeedMillAuditPaymentDetails(FeedMillAuditID, PurposeOfInvoice, PaymentType, InvoiceNo, 
            InvoiceDate, InvoiceValue, InvoiceDueDate, Others, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime) 
            VALUES ('$FeedMillAuditID', '$PurposeOfInvoice', '$PaymentType', '$InvoiceNo', '$InvoiceDate', '$InvoiceValue', 
            '$InvoiceDueDate', '$Others', '$TotalAmount', '$PaymentDate', '$PaymentRemarks', '$PostedDatetime')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                $this->Error = null;
                $this->Msg = 'Payment Posted Successfully';
                $lastInsertedId = $this->conn->lastInsertId();
                $createdPayment = $this->auditLogger->fetchRowById('QcFeedMillAuditPaymentDetails', 'FeedMillAuditPaymentID', $lastInsertedId);
                $this->auditLogger->logInsert('QcFeedMillAuditPaymentDetails', $lastInsertedId, $createdPayment, $RequestBody, [
                    'ReferenceNo' => $FeedMillAuditID,
                    'Remarks' => 'QC feed mill audit payment created',
                ]);
            } else {
                $this->Error = "Unable to Post Paymnet";
                $this->Msg = "Unable to Post Paymnet";
            }
        } else {
            if ($ConfirmPayment == 'No') {
                $sqlUpdate = "UPDATE QcFeedMillAuditPaymentDetails SET FeedMillAuditID='$FeedMillAuditID',
                PurposeOfInvoice='$PurposeOfInvoice',PaymentType='$PaymentType',InvoiceNo='$InvoiceNo',InvoiceDate='$InvoiceDate',
                InvoiceValue='$InvoiceValue',InvoiceDueDate='$InvoiceDueDate', Others='$Others',TotalAmount='$TotalAmount',
                PostedDatetime='$PostedDatetime' 
                WHERE FeedMillAuditPaymentID='$FeedMillAuditPaymentID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $this->Error = null;
                    $this->Msg = 'Payment Updated Successfully';
                    $updatedPayment = $this->auditLogger->fetchRowById('QcFeedMillAuditPaymentDetails', 'FeedMillAuditPaymentID', $FeedMillAuditPaymentID);
                    $this->auditLogger->logUpdate('QcFeedMillAuditPaymentDetails', $FeedMillAuditPaymentID, $existingPayment, $updatedPayment, $RequestBody, [
                        'ReferenceNo' => $FeedMillAuditID,
                        'Remarks' => 'QC feed mill audit payment updated',
                    ]);
                } else {
                    $this->Error = "Unable to Update Paymnet";
                    $this->Msg = "Unable to Update Paymnet";
                }
            } else if ($ConfirmPayment == 'Yes') {
                $sqlUpdate = "UPDATE QcFeedMillAuditPaymentDetails SET PaymentDate='$PaymentDate',PaymentRemarks='$PaymentRemarks'
                WHERE FeedMillAuditPaymentID='$FeedMillAuditPaymentID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $this->Error = null;
                    $this->Msg = 'Payment Confirmed Successfully';
                    $updatedPayment = $this->auditLogger->fetchRowById('QcFeedMillAuditPaymentDetails', 'FeedMillAuditPaymentID', $FeedMillAuditPaymentID);
                    $this->auditLogger->logUpdate('QcFeedMillAuditPaymentDetails', $FeedMillAuditPaymentID, $existingPayment, $updatedPayment, $RequestBody, [
                        'ReferenceNo' => $FeedMillAuditID,
                        'Remarks' => 'QC feed mill audit payment confirmed',
                    ]);
                } else {
                    $this->Error = "Unable to Confirm Paymnet";
                    $this->Msg = "Unable to Confirm Paymnet";
                }
            }
        }

        $PaymentData = $this->getFeedMillAuditPaymentData($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['PaymentData'] = $PaymentData['PaymentData'];

        return $Data;
    }

    function getFeedMillAuditPaymentData($RequestBody)
    {
        $FeedMillAuditPaymentID = $RequestBody['FeedMillAuditPaymentID'];
        $FeedMillAuditID = $RequestBody['FeedMillAuditID'];

        $sqlPaymentFields = "FeedMillAuditPaymentID, FeedMillAuditID, PurposeOfInvoice, InvoiceNo, InvoiceDate, InvoiceValue, InvoiceDueDate, 
        Others, PaymentType, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime";

        if ($FeedMillAuditPaymentID) {
            $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcFeedMillAuditPaymentDetails WHERE FeedMillAuditPaymentID = '$FeedMillAuditPaymentID'";
        } else {
            $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcFeedMillAuditPaymentDetails WHERE FeedMillAuditID = '$FeedMillAuditID'";
        }
        $stmtPaymentSelect = $this->conn->prepare($sqlPaymentSelect);
        $stmtPaymentSelect->execute();

        $ArrayPaymentData = [];
        $i = 0;
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

    public function deletePaymentDetals($RequestBody)
    {
        $FeedMillAuditPaymentID = $RequestBody['FeedMillAuditPaymentID'];
        $FeedMillAuditID = $RequestBody['FeedMillAuditID'];
        $existingPayment = $this->auditLogger->fetchRowById('QcFeedMillAuditPaymentDetails', 'FeedMillAuditPaymentID', $FeedMillAuditPaymentID);

        $sqlDelete = "DELETE FROM QcFeedMillAuditPaymentDetails 
        WHERE FeedMillAuditPaymentID = '$FeedMillAuditPaymentID' AND FeedMillAuditID = '$FeedMillAuditID'";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();

        if ($stmtDelete) {
            $this->auditLogger->logDelete('QcFeedMillAuditPaymentDetails', $FeedMillAuditPaymentID, $existingPayment, $RequestBody, [
                'ReferenceNo' => $FeedMillAuditID,
                'Remarks' => 'QC feed mill audit payment deleted',
            ]);
            $this->Error = null;
            $this->Msg = 'Payment Delete Successfully';
        } else {
            $this->Error = "Unable to Delete Paymnet";
            $this->Msg = "Unable to Delete Paymnet";
        }

        $PaymentData = $this->getFeedMillAuditPaymentData($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['PaymentData'] = $PaymentData['PaymentData'];
        return  $Data;
    }
}
