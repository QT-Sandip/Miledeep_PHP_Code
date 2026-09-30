<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class FarmAuditPaymentController
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

    public function postFarmAuditPaymentDetails($RequestBody)
    {
        $FarmAuditPaymentID = $RequestBody['FarmAuditPaymentID'];
        $FarmAuditID = $RequestBody['FarmAuditID'];
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
        if ($FarmAuditPaymentID != '') {
            $existingPayment = $this->auditLogger->fetchRowById('QcFarmAuditPaymentDetails', 'FarmAuditPaymentID', $FarmAuditPaymentID);
        }

        if ($FarmAuditPaymentID == '') {

            $sqlInsert = "INSERT INTO QcFarmAuditPaymentDetails(FarmAuditID, PurposeOfInvoice, PaymentType, InvoiceNo, 
            InvoiceDate, InvoiceValue, InvoiceDueDate, Others, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime) 
            VALUES ('$FarmAuditID', '$PurposeOfInvoice', '$PaymentType', '$InvoiceNo', '$InvoiceDate', '$InvoiceValue', 
            '$InvoiceDueDate', '$Others', '$TotalAmount', '$PaymentDate', '$PaymentRemarks', '$PostedDatetime')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();
            // $errorInfo = $stmtInsert->errorInfo();
            // var_dump($errorInfo);

            if ($stmtInsert) {
                $this->Error = null;
                $this->Msg = 'Payment Posted Successfully';
                $lastInsertedId = $this->conn->lastInsertId();
                $createdPayment = $this->auditLogger->fetchRowById('QcFarmAuditPaymentDetails', 'FarmAuditPaymentID', $lastInsertedId);
                $this->auditLogger->logInsert('QcFarmAuditPaymentDetails', $lastInsertedId, $createdPayment, $RequestBody, [
                    'ReferenceNo' => $FarmAuditID,
                    'Remarks' => 'QC farm audit payment created',
                ]);
                // $FoodAuditID = $this->conn->lastInsertId();
            } else {
                $this->Error = "Unable to Post Paymnet";
                $this->Msg = "Unable to Post Paymnet";
            }
        } else {
            if ($ConfirmPayment == 'No') {

                $sqlUpdate = "UPDATE QcFarmAuditPaymentDetails SET FarmAuditID='$FarmAuditID',
                PurposeOfInvoice='$PurposeOfInvoice',PaymentType='$PaymentType',InvoiceNo='$InvoiceNo',InvoiceDate='$InvoiceDate',
                InvoiceValue='$InvoiceValue',InvoiceDueDate='$InvoiceDueDate', Others='$Others',TotalAmount='$TotalAmount',
                PostedDatetime='$PostedDatetime' 
                WHERE FarmAuditPaymentID='$FarmAuditPaymentID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $this->Error = null;
                    $this->Msg = 'Payment Updated Successfully';
                    $updatedPayment = $this->auditLogger->fetchRowById('QcFarmAuditPaymentDetails', 'FarmAuditPaymentID', $FarmAuditPaymentID);
                    $this->auditLogger->logUpdate('QcFarmAuditPaymentDetails', $FarmAuditPaymentID, $existingPayment, $updatedPayment, $RequestBody, [
                        'ReferenceNo' => $FarmAuditID,
                        'Remarks' => 'QC farm audit payment updated',
                    ]);
                } else {
                    $this->Error = "Unable to Update Paymnet";
                    $this->Msg = "Unable to Update Paymnet";
                }
            } else if ($ConfirmPayment == 'Yes') {
                $sqlUpdate = "UPDATE QcFarmAuditPaymentDetails SET PaymentDate='$PaymentDate',PaymentRemarks='$PaymentRemarks'
                WHERE FarmAuditPaymentID='$FarmAuditPaymentID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $this->Error = null;
                    $this->Msg = 'Payment Confirmed Successfully';
                    $updatedPayment = $this->auditLogger->fetchRowById('QcFarmAuditPaymentDetails', 'FarmAuditPaymentID', $FarmAuditPaymentID);
                    $this->auditLogger->logUpdate('QcFarmAuditPaymentDetails', $FarmAuditPaymentID, $existingPayment, $updatedPayment, $RequestBody, [
                        'ReferenceNo' => $FarmAuditID,
                        'Remarks' => 'QC farm audit payment confirmed',
                    ]);
                } else {
                    $this->Error = "Unable to Confirm Paymnet";
                    $this->Msg = "Unable to Confirm Paymnet";
                }
            }
        }

        $PaymentData = $this->getFarmAuditPaymentData($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['PaymentData'] = $PaymentData['PaymentData'];

        return $Data;
    }

    function getFarmAuditPaymentData($RequestBody)
    {
        $FarmAuditPaymentID = $RequestBody['FarmAuditPaymentID'];
        $FarmAuditID = $RequestBody['FarmAuditID'];

        $sqlPaymentFields = "FarmAuditPaymentID, FarmAuditID, PurposeOfInvoice, InvoiceNo, InvoiceDate, InvoiceValue, InvoiceDueDate, 
        Others, PaymentType, Remarks, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime, PostedUserID";

        if ($FarmAuditPaymentID) {
            $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcFarmAuditPaymentDetails WHERE FarmAuditPaymentID = '$FarmAuditPaymentID'";
        } else {
            $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcFarmAuditPaymentDetails WHERE FarmAuditID = '$FarmAuditID'";
        }
        $stmtPaymentSelect = $this->conn->prepare($sqlPaymentSelect);
        $stmtPaymentSelect->execute();
        // $erroInfo = $stmtPaymentSelect->errorInfo();
        // var_dump($erroInfo);

        $ArrayPaymentData = [];
        $i = 0;
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
        $FarmAuditPaymentID = $RequestBody['FarmAuditPaymentID'];
        $FarmAuditID = $RequestBody['FarmAuditID'];
        $existingPayment = $this->auditLogger->fetchRowById('QcFarmAuditPaymentDetails', 'FarmAuditPaymentID', $FarmAuditPaymentID);

        $sqlDelete = "DELETE FROM QcFarmAuditPaymentDetails 
        WHERE FarmAuditPaymentID = '$FarmAuditPaymentID' AND FarmAuditID = '$FarmAuditID'";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();
        // $errorInfo = $stmtDelete->errorInfo();
        // var_dump($errorInfo);

        if ($stmtDelete) {
            $this->auditLogger->logDelete('QcFarmAuditPaymentDetails', $FarmAuditPaymentID, $existingPayment, $RequestBody, [
                'ReferenceNo' => $FarmAuditID,
                'Remarks' => 'QC farm audit payment deleted',
            ]);
            $this->Error = null;
            $this->Msg = 'Payment Delete Successfully';
        } else {
            $this->Error = "Unable to Delete Paymnet";
            $this->Msg = "Unable to Delete Paymnet";
        }

        $PaymentData = $this->getFarmAuditPaymentData($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['PaymentData'] = $PaymentData['PaymentData'];
        return  $Data;
    }
}
