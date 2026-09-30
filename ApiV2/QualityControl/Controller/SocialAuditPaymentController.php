<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class SocialAuditPaymentController
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

    public function postSocialAuditPaymentDetails($RequestBody)
    {
        $SocialAuditPaymentID = $RequestBody['SocialAuditPaymentID'];
        $SocialAuditID = $RequestBody['SocialAuditID'];
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
        if ($SocialAuditPaymentID != '') {
            $existingPayment = $this->auditLogger->fetchRowById('QcSocialAuditPaymentDetails', 'SocialAuditPaymentID', $SocialAuditPaymentID);
        }

        if ($SocialAuditPaymentID == '') {

            $sqlInsert = "INSERT INTO QcSocialAuditPaymentDetails(SocialAuditID, PurposeOfInvoice, PaymentType, InvoiceNo, 
            InvoiceDate, InvoiceValue, InvoiceDueDate, Others, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime) 
            VALUES ('$SocialAuditID', '$PurposeOfInvoice', '$PaymentType', '$InvoiceNo', '$InvoiceDate', '$InvoiceValue', 
            '$InvoiceDueDate', '$Others', '$TotalAmount', '$PaymentDate', '$PaymentRemarks', '$PostedDatetime')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                $this->Error = null;
                $this->Msg = 'Payment Posted Successfully';
                $lastInsertedId = $this->conn->lastInsertId();
                $createdPayment = $this->auditLogger->fetchRowById('QcSocialAuditPaymentDetails', 'SocialAuditPaymentID', $lastInsertedId);
                $this->auditLogger->logInsert('QcSocialAuditPaymentDetails', $lastInsertedId, $createdPayment, $RequestBody, [
                    'ReferenceNo' => $SocialAuditID,
                    'Remarks' => 'QC social audit payment created',
                ]);
            } else {
                $this->Error = "Unable to Post Paymnet";
                $this->Msg = "Unable to Post Paymnet";
            }
        } else {

            if ($ConfirmPayment == 'No') {

                $sqlUpdate = "UPDATE QcSocialAuditPaymentDetails SET SocialAuditID='$SocialAuditID',
                PurposeOfInvoice='$PurposeOfInvoice',PaymentType='$PaymentType',InvoiceNo='$InvoiceNo',InvoiceDate='$InvoiceDate',
                InvoiceValue='$InvoiceValue',InvoiceDueDate='$InvoiceDueDate', Others='$Others', TotalAmount='$TotalAmount',
                PostedDatetime='$PostedDatetime' 
                WHERE SocialAuditPaymentID='$SocialAuditPaymentID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $this->Error = null;
                    $this->Msg = 'Payment Updated Successfully';
                    $updatedPayment = $this->auditLogger->fetchRowById('QcSocialAuditPaymentDetails', 'SocialAuditPaymentID', $SocialAuditPaymentID);
                    $this->auditLogger->logUpdate('QcSocialAuditPaymentDetails', $SocialAuditPaymentID, $existingPayment, $updatedPayment, $RequestBody, [
                        'ReferenceNo' => $SocialAuditID,
                        'Remarks' => 'QC social audit payment updated',
                    ]);
                } else {
                    $this->Error = "Unable to Update Paymnet";
                    $this->Msg = "Unable to Update Paymnet";
                }
            } else if ($ConfirmPayment == 'Yes') {
                $sqlUpdate = "UPDATE QcSocialAuditPaymentDetails SET PaymentDate='$PaymentDate',PaymentRemarks='$PaymentRemarks' 
                WHERE SocialAuditPaymentID='$SocialAuditPaymentID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $this->Error = null;
                    $this->Msg = 'Payment Confirmed Successfully';
                    $updatedPayment = $this->auditLogger->fetchRowById('QcSocialAuditPaymentDetails', 'SocialAuditPaymentID', $SocialAuditPaymentID);
                    $this->auditLogger->logUpdate('QcSocialAuditPaymentDetails', $SocialAuditPaymentID, $existingPayment, $updatedPayment, $RequestBody, [
                        'ReferenceNo' => $SocialAuditID,
                        'Remarks' => 'QC social audit payment confirmed',
                    ]);
                } else {
                    $this->Error = "Unable to Confirm Paymnet";
                    $this->Msg = "Unable to Confirm Paymnet";
                }
            }
        }

        $PaymentData = $this->getSocialAuditPaymentData($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['PaymentData'] = $PaymentData['PaymentData'];

        return $Data;
    }

    function getSocialAuditPaymentData($RequestBody)
    {
        $SocialAuditPaymentID = $RequestBody['SocialAuditPaymentID'];
        $SocialAuditID = $RequestBody['SocialAuditID'];

        $sqlPaymentFields = "SocialAuditPaymentID, SocialAuditID, PurposeOfInvoice, PaymentType, InvoiceNo, InvoiceDate, 
        InvoiceValue, InvoiceDueDate,Others, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime";

        if ($SocialAuditPaymentID) {
            $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcSocialAuditPaymentDetails WHERE SocialAuditPaymentID = '$SocialAuditPaymentID'";
        } else {
            $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcSocialAuditPaymentDetails WHERE SocialAuditID = '$SocialAuditID'";
        }
        $stmtPaymentSelect = $this->conn->prepare($sqlPaymentSelect);
        $stmtPaymentSelect->execute();

        $ArrayPaymentData = [];
        $i = 0;
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
        $SocialAuditPaymentID = $RequestBody['SocialAuditPaymentID'];
        $SocialAuditID = $RequestBody['SocialAuditID'];
        $existingPayment = $this->auditLogger->fetchRowById('QcSocialAuditPaymentDetails', 'SocialAuditPaymentID', $SocialAuditPaymentID);

        $sqlDelete = "DELETE FROM QcSocialAuditPaymentDetails 
        WHERE SocialAuditPaymentID = '$SocialAuditPaymentID' AND SocialAuditID = '$SocialAuditID'";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();

        if ($stmtDelete) {
            $this->auditLogger->logDelete('QcSocialAuditPaymentDetails', $SocialAuditPaymentID, $existingPayment, $RequestBody, [
                'ReferenceNo' => $SocialAuditID,
                'Remarks' => 'QC social audit payment deleted',
            ]);
            $this->Error = null;
            $this->Msg = 'Payment Delete Successfully';
        } else {
            $this->Error = "Unable to Delete Paymnet";
            $this->Msg = "Unable to Delete Paymnet";
        }

        $PaymentData = $this->getSocialAuditPaymentData($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['PaymentData'] = $PaymentData['PaymentData'];
        return  $Data;
    }
}
