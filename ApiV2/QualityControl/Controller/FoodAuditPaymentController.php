<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class FoodAuditPaymentController
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

    public function postFoodAuditPaymentDetails($RequestBody)
    {
        $FoodAuditPaymentID = $RequestBody['FoodAuditPaymentID'];
        $FoodAuditID = $RequestBody['FoodAuditID'];
        $PurposeOfInvoice = $RequestBody['PurposeOfInvoice'];
        $PaymentType = $RequestBody['PaymentType'];
        $InvoiceNumber = $RequestBody['InvoiceNo'];
        $InvoiceDate = $RequestBody['InvoidDate'];
        $InvoiceValue = $RequestBody['InvoiceValue'];
        $InvoiceDueDate = $RequestBody['InvoiceDueDate'];
        $TotalAmount = $RequestBody['TotalAmount'];
        $Others = $RequestBody['Others'];
        $PaymentDate = $RequestBody['PaymentDate'];
        $PaymentRemarks = $RequestBody['PaymentRemarks'];
        $ConfirmPayment = $RequestBody['ConfirmPayment'];

        date_default_timezone_set("Asia/Kolkata");
        $PostedDatetime = date("Y-m-d H:i:s");
        $existingPayment = null;
        if ($FoodAuditPaymentID != '') {
            $existingPayment = $this->auditLogger->fetchRowById('QcFoodAuditPaymentDetails', 'FoodAuditPaymentID', $FoodAuditPaymentID);
        }

        if ($FoodAuditPaymentID == '') {
            $sqlInsert = "INSERT INTO QcFoodAuditPaymentDetails(FoodAuditID, PurposeOfInvoice, PaymentType, InvoiceNumber, 
            InvoiceDate, InvoiceValue, InvoiceDueDate, TotalAmount, Others, PostedDatetime) 
            VALUES ('$FoodAuditID', '$PurposeOfInvoice', '$PaymentType', '$InvoiceNumber', '$InvoiceDate', '$InvoiceValue', 
            '$InvoiceDueDate', '$TotalAmount', '$Others', '$PostedDatetime')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();
            // $errorInfo = $stmtInsert->errorInfo();
            // var_dump($errorInfo);

            if ($stmtInsert) {
                $this->Error = null;
                $this->Msg = 'Payment Posted Successfully';
                $lastInsertedId = $this->conn->lastInsertId();
                $createdPayment = $this->auditLogger->fetchRowById('QcFoodAuditPaymentDetails', 'FoodAuditPaymentID', $lastInsertedId);
                $this->auditLogger->logInsert('QcFoodAuditPaymentDetails', $lastInsertedId, $createdPayment, $RequestBody, [
                    'ReferenceNo' => $FoodAuditID,
                    'Remarks' => 'QC food audit payment created',
                ]);
                // $FoodAuditID = $this->conn->lastInsertId();
            } else {
                $this->Error = "Unable to Post Paymnet";
                $this->Msg = "Unable to Post Paymnet";
            }
        } else {
            if ($ConfirmPayment == 'No') {
                $sqlUpdate = "UPDATE QcFoodAuditPaymentDetails SET FoodAuditID='$FoodAuditID',
                PurposeOfInvoice='$PurposeOfInvoice',PaymentType='$PaymentType',InvoiceNumber='$InvoiceNumber',InvoiceDate='$InvoiceDate',
                InvoiceValue='$InvoiceValue',InvoiceDueDate='$InvoiceDueDate', TotalAmount='$TotalAmount',
                Others='$Others', PostedDatetime='$PostedDatetime' 
                WHERE FoodAuditPaymentID='$FoodAuditPaymentID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $this->Error = null;
                    $this->Msg = 'Payment Updated Successfully';
                    $updatedPayment = $this->auditLogger->fetchRowById('QcFoodAuditPaymentDetails', 'FoodAuditPaymentID', $FoodAuditPaymentID);
                    $this->auditLogger->logUpdate('QcFoodAuditPaymentDetails', $FoodAuditPaymentID, $existingPayment, $updatedPayment, $RequestBody, [
                        'ReferenceNo' => $FoodAuditID,
                        'Remarks' => 'QC food audit payment updated',
                    ]);
                } else {
                    $this->Error = "Unable to Update Paymnet";
                    $this->Msg = "Unable to Update Paymnet";
                }
            } else if ($ConfirmPayment == 'Yes') {
                $sqlUpdate = "UPDATE QcFoodAuditPaymentDetails SET PaymentDate='$PaymentDate',PaymentRemarks='$PaymentRemarks' 
                WHERE FoodAuditPaymentID='$FoodAuditPaymentID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $this->Error = null;
                    $this->Msg = 'Payment Confirmed Successfully';
                    $updatedPayment = $this->auditLogger->fetchRowById('QcFoodAuditPaymentDetails', 'FoodAuditPaymentID', $FoodAuditPaymentID);
                    $this->auditLogger->logUpdate('QcFoodAuditPaymentDetails', $FoodAuditPaymentID, $existingPayment, $updatedPayment, $RequestBody, [
                        'ReferenceNo' => $FoodAuditID,
                        'Remarks' => 'QC food audit payment confirmed',
                    ]);
                } else {
                    $this->Error = "Unable to Confirm Paymnet";
                    $this->Msg = "Unable to Confirm Paymnet";
                }
            }
        }

        $PaymentData = $this->getFoodAuditPaymentData($FoodAuditID);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['PaymentData'] = $PaymentData['PaymentData'];

        return $Data;
    }

    function getFoodAuditPaymentData($RequestBody)
    {
        $FoodAuditPaymentID = $RequestBody['FoodAuditPaymentID'];
        $FoodAuditID = $RequestBody['FoodAuditID'];

        $sqlPaymentFields = "FoodAuditPaymentID, FoodAuditID, PurposeOfInvoice, PaymentType, InvoiceNumber, InvoiceDate, 
        InvoiceValue, InvoiceDueDate,Others, TotalAmount, PaymentDate, PaymentRemarks, PostedDatetime, PostedUserID";

        if ($FoodAuditPaymentID) {
            $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcFoodAuditPaymentDetails WHERE FoodAuditPaymentID = '$FoodAuditPaymentID'";
        } else {
            $sqlPaymentSelect = "SELECT $sqlPaymentFields FROM QcFoodAuditPaymentDetails WHERE FoodAuditID = '$FoodAuditID'";
        }
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
        $FoodAuditPaymentID = $RequestBody['FoodAuditPaymentID'];
        $FoodAuditID = $RequestBody['FoodAuditID'];
        $existingPayment = $this->auditLogger->fetchRowById('QcFoodAuditPaymentDetails', 'FoodAuditPaymentID', $FoodAuditPaymentID);

        $sqlDelete = "DELETE FROM QcFoodAuditPaymentDetails 
        WHERE FoodAuditPaymentID = '$FoodAuditPaymentID' AND FoodAuditID = '$FoodAuditID'";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();

        if ($stmtDelete) {
            $this->auditLogger->logDelete('QcFoodAuditPaymentDetails', $FoodAuditPaymentID, $existingPayment, $RequestBody, [
                'ReferenceNo' => $FoodAuditID,
                'Remarks' => 'QC food audit payment deleted',
            ]);
            $this->Error = null;
            $this->Msg = 'Payment Delete Successfully';
        } else {
            $this->Error = "Unable to Delete Paymnet";
            $this->Msg = "Unable to Delete Paymnet";
        }

        $PaymentData = $this->getFoodAuditPaymentData($FoodAuditID);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['PaymentData'] = $PaymentData['PaymentData'];
        return  $Data;
    }
}
