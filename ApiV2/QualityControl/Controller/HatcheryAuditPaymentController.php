<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class HatcheryAuditPaymentController
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

    public function postHatcheryAuditPaymentDetails($RequestBody)
    {
        $HatcheryPaymentID = $RequestBody['HatcheryPaymentID'];
        $HatcheryID = $RequestBody['HatcheryID'];
        $PurposeOfInvoice = $RequestBody['PurposeOfInvoice'];
        $PaymentType = $RequestBody['PaymentType'];
        $InvoiceNumber = $RequestBody['InvoiceNumber'];
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
        if ($HatcheryPaymentID != '') {
            $existingPayment = $this->auditLogger->fetchRowById('QcHatcheryAuditPaymentDetails', 'HatcheryPaymentID', $HatcheryPaymentID);
        }

        if ($HatcheryPaymentID == '') {

            $sqlInsert = "INSERT INTO QcHatcheryAuditPaymentDetails(HatcheryID, PurposeOfInvoice, PaymentType, InvoiceNumber, 
            InvoiceDate, InvoiceValue, InvoiceDueDate, Others, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime) 
            VALUES ('$HatcheryID', '$PurposeOfInvoice', '$PaymentType', '$InvoiceNumber', '$InvoiceDate', '$InvoiceValue', 
            '$InvoiceDueDate', '$Others', '$TotalAmount', '$PaymentDate', '$PaymentRemarks', '$PostedDatetime')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                $this->Error = null;
                $this->Msg = 'Payment Posted Successfully';
                $lastInsertedId = $this->conn->lastInsertId();
                $createdPayment = $this->auditLogger->fetchRowById('QcHatcheryAuditPaymentDetails', 'HatcheryPaymentID', $lastInsertedId);
                $this->auditLogger->logInsert('QcHatcheryAuditPaymentDetails', $lastInsertedId, $createdPayment, $RequestBody, [
                    'ReferenceNo' => $HatcheryID,
                    'Remarks' => 'QC hatchery audit payment created',
                ]);
            } else {
                $this->Error = "Unable to Post Paymnet";
                $this->Msg = "Unable to Post Paymnet";
            }
        } else {
            if ($ConfirmPayment == 'No') {
                $sqlUpdate = "UPDATE QcHatcheryAuditPaymentDetails SET HatcheryID='$HatcheryID',
                PurposeOfInvoice='$PurposeOfInvoice',PaymentType='$PaymentType',InvoiceNumber='$InvoiceNumber',InvoiceDate='$InvoiceDate',
                InvoiceValue='$InvoiceValue',InvoiceDueDate='$InvoiceDueDate', Others='$Others',TotalAmount='$TotalAmount',
                PostedDatetime='$PostedDatetime' 
                WHERE HatcheryPaymentID='$HatcheryPaymentID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $this->Error = null;
                    $this->Msg = 'Payment Updated Successfully';
                    $updatedPayment = $this->auditLogger->fetchRowById('QcHatcheryAuditPaymentDetails', 'HatcheryPaymentID', $HatcheryPaymentID);
                    $this->auditLogger->logUpdate('QcHatcheryAuditPaymentDetails', $HatcheryPaymentID, $existingPayment, $updatedPayment, $RequestBody, [
                        'ReferenceNo' => $HatcheryID,
                        'Remarks' => 'QC hatchery audit payment updated',
                    ]);
                } else {
                    $this->Error = "Unable to Update Paymnet";
                    $this->Msg = "Unable to Update Paymnet";
                }
            } else if ($ConfirmPayment == 'Yes') {
                $sqlUpdate = "UPDATE QcHatcheryAuditPaymentDetails SET PaymentDate='$PaymentDate',PaymentRemarks='$PaymentRemarks'
                WHERE HatcheryPaymentID='$HatcheryPaymentID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $this->Error = null;
                    $this->Msg = 'Payment Confirmed Successfully';
                    $updatedPayment = $this->auditLogger->fetchRowById('QcHatcheryAuditPaymentDetails', 'HatcheryPaymentID', $HatcheryPaymentID);
                    $this->auditLogger->logUpdate('QcHatcheryAuditPaymentDetails', $HatcheryPaymentID, $existingPayment, $updatedPayment, $RequestBody, [
                        'ReferenceNo' => $HatcheryID,
                        'Remarks' => 'QC hatchery audit payment confirmed',
                    ]);
                } else {
                    $this->Error = "Unable to Confirm Paymnet";
                    $this->Msg = "Unable to Confirm Paymnet";
                }
            }
        }

        $PaymentData = $this->getHatcheryAuditPaymentData($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['PaymentData'] = $PaymentData['PaymentData'];

        return $Data;
    }

    function getHatcheryAuditPaymentData($RequestBody)
    {
        $HatcheryPaymentID = $RequestBody['HatcheryPaymentID'];
        $HatcheryID = $RequestBody['HatcheryID'];

        $sqlPaymentFields = "HatcheryPaymentID, HatcheryID, PurposeOfInvoice, InvoiceNumber, InvoiceDate, InvoiceValue, InvoiceDueDate, 
        Others, PaymentType, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime";

        if ($HatcheryPaymentID) {
            $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcHatcheryAuditPaymentDetails WHERE HatcheryPaymentID = '$HatcheryPaymentID'";
        } else {
            $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcHatcheryAuditPaymentDetails WHERE HatcheryID = '$HatcheryID'";
        }
        $stmtPaymentSelect = $this->conn->prepare($sqlPaymentSelect);
        $stmtPaymentSelect->execute();

        $ArrayPaymentData = [];
        $i = 0;
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
        $HatcheryPaymentID = $RequestBody['HatcheryPaymentID'];
        $HatcheryID = $RequestBody['HatcheryID'];
        $existingPayment = $this->auditLogger->fetchRowById('QcHatcheryAuditPaymentDetails', 'HatcheryPaymentID', $HatcheryPaymentID);

        $sqlDelete = "DELETE FROM QcHatcheryAuditPaymentDetails 
        WHERE HatcheryPaymentID = '$HatcheryPaymentID' AND HatcheryID = '$HatcheryID'";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();

        if ($stmtDelete) {
            $this->auditLogger->logDelete('QcHatcheryAuditPaymentDetails', $HatcheryPaymentID, $existingPayment, $RequestBody, [
                'ReferenceNo' => $HatcheryID,
                'Remarks' => 'QC hatchery audit payment deleted',
            ]);
            $this->Error = null;
            $this->Msg = 'Payment Delete Successfully';
        } else {
            $this->Error = "Unable to Delete Paymnet";
            $this->Msg = "Unable to Delete Paymnet";
        }

        $PaymentData = $this->getHatcheryAuditPaymentData($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['PaymentData'] = $PaymentData['PaymentData'];
        return  $Data;
    }
}
