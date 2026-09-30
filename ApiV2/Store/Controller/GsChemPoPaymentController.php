<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class GsChemPoPaymentController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
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

    public function postChemPoPaymentDetails($RequestBody)
    {
        $ChemPaymentID = $RequestBody['ChemPaymentID'];

        if ($ChemPaymentID == '') {
            $Data = $this->insertChemPoPaymentDetails($RequestBody);
        } else {
            $Data = $this->updateChemPoPaymentDetails($RequestBody);
        }

        return $Data;
    }

    public function insertChemPoPaymentDetails($RequestBody)
    {
        $ChemPoID = $RequestBody['ChemPoID'];
        $ChemPaymentID = $RequestBody['ChemPaymentID'];
        $InvoiceNo = $RequestBody['InvoiceNo'];
        $InvoiceDt = $RequestBody['InvoiceDt'];
        //$InvoiceAmount = $RequestBody['InvoiceAmount'];
        $PaymentDtls = $RequestBody['PaymentDtls'];
        $Remarks = $RequestBody['Remarks'];

        $InvoiceAmount = $RequestBody['Amount'];

        $TotalPayment = $this->getPoPaymentTotals($RequestBody);
        $Amount = $this->getPoAmountTotals($RequestBody);

        $TotalInvAmt = $TotalPayment['Data'][0]['TotalInvAmt'];
        $TotalAmt = $Amount['Data'][0]['TotalAmount'];

        if ($TotalInvAmt + $InvoiceAmount <= $TotalAmt) {

            $sqlInsert = "INSERT INTO GsChemPOPaymentDetails(ChemPoID, ChemPaymentID, InvoiceNo, InvoiceDt, InvoiceAmount, PaymentDtls, Remarks) 
            VALUES ('$ChemPoID','$ChemPaymentID','$InvoiceNo','$InvoiceDt','$InvoiceAmount','$PaymentDtls','$Remarks')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {

                $lastInsertedId = $this->conn->lastInsertId();
                $RequestBody['ChemPaymentID'] = $lastInsertedId;
                $createdPayment = $this->auditLogger->fetchRowById('GsChemPOPaymentDetails', 'ChemPaymentID', $lastInsertedId);
                $this->auditLogger->logInsert('GsChemPOPaymentDetails', $lastInsertedId, $createdPayment, $RequestBody, [
                    'ReferenceNo' => $ChemPoID,
                    'Remarks' => 'Store chemical PO payment created',
                ]);

                $chemPoPaymentData = $this->getChemPoPaymentDetails($RequestBody);
                $TotalData = $this->getPoPaymentTotals($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'Payment Posted Successfully';
                $Data['Data'] = $chemPoPaymentData['Data'];
                $Data['Total'] = $TotalData['Data'];

                return $Data;
            } else {
                $Data['Error'] = 'Unable to Insert Payment';
                $Data['Msg'] = $this->Error;
                return $Data;
            }
        } else {
            $Data['Error'] = 'Total received weight exceeds the total quantity specified in the purchase order';
            $Data['Msg'] = 'Invoice Payment (' . ($TotalInvAmt + $InvoiceAmount) . ') EXCEEDED PO Total Amount (' . ($TotalAmt) . ')';
            return $Data;
        }
    }

    public function updateChemPoPaymentDetails($RequestBody)
    {

        $ChemPoID = $RequestBody['ChemPoID'];
        $ChemPaymentID = $RequestBody['ChemPaymentID'];
        $InvoiceNo = $RequestBody['InvoiceNo'];
        $InvoiceDt = $RequestBody['InvoiceDt'];
       // $InvoiceAmount = $RequestBody['InvoiceAmount'];
        $PaymentDtls = $RequestBody['PaymentDtls'];
        $Remarks = $RequestBody['Remarks'];
        $existingPayment = $this->auditLogger->fetchRowById('GsChemPOPaymentDetails', 'ChemPaymentID', $ChemPaymentID);

        $InvoiceAmount = $RequestBody['Amount'];

        $TotalPayment = $this->getPoPaymentTotals($RequestBody);
        $Amount = $this->getPoAmountTotals($RequestBody);

        $TotalInvAmt = $TotalPayment['Data'][0]['TotalInvAmt'];
        $TotalAmt = $Amount['Data'][0]['TotalAmount'];

        if ($TotalInvAmt + $InvoiceAmount <= $TotalAmt) {

            $sqlUpdate = "UPDATE GsChemPOPaymentDetails SET ChemPoID='$ChemPoID',InvoiceNo='$InvoiceNo',InvoiceDt='$InvoiceDt',
            InvoiceAmount='$InvoiceAmount',PaymentDtls='$PaymentDtls',Remarks='$Remarks' WHERE ChemPaymentID='$ChemPaymentID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();


            if ($stmtUpdate) {
                $updatedPayment = $this->auditLogger->fetchRowById('GsChemPOPaymentDetails', 'ChemPaymentID', $ChemPaymentID);
                $this->auditLogger->logUpdate('GsChemPOPaymentDetails', $ChemPaymentID, $existingPayment, $updatedPayment, $RequestBody, [
                    'ReferenceNo' => $ChemPoID,
                    'Remarks' => 'Store chemical PO payment updated',
                ]);
                $chemPoPaymentData = $this->getChemPoPaymentDetails($RequestBody);
                $TotalData = $this->getPoPaymentTotals($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'Payment Updated Successfully';
                $Data['Data'] = $chemPoPaymentData['Data'];
                $Data['Total'] = $TotalData['Data'];

                return $Data;
            } else {
                $Data['Error'] = 'Unable to Updated Payment';
                $Data['Msg'] = $this->Error;
                return $Data;
            }
        } else {
            $Data['Error'] = 'Total received weight exceeds the total quantity specified in the purchase order';
            $Data['Msg'] = 'Invoice Payment (' . ($TotalInvAmt + $InvoiceAmount) . ') EXCEEDED PO Total Amount (' . ($TotalAmt) . ')';
            return $Data;
        }
    }

    function deleteChemPaymentDetails($RequestBody)
    {
        $ChemPaymentID = $RequestBody['ChemPaymentID'];
        $existingPayment = $this->auditLogger->fetchRowById('GsChemPOPaymentDetails', 'ChemPaymentID', $ChemPaymentID);

        $sqlDelete = "DELETE FROM `GsChemPOPaymentDetails` WHERE ChemPaymentID='$ChemPaymentID'";

        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();

        if ($stmtDelete) {
            $this->auditLogger->logDelete('GsChemPOPaymentDetails', $ChemPaymentID, $existingPayment, $RequestBody, [
                'ReferenceNo' => isset($existingPayment['ChemPoID']) ? $existingPayment['ChemPoID'] : $ChemPaymentID,
                'Remarks' => 'Store chemical PO payment deleted',
            ]);
            $chemPoPaymentData = $this->getChemPoPaymentDetails($RequestBody);
            $TotalData = $this->getPoPaymentTotals($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Payment Deleted Successfully';
            $Data['Data'] = $chemPoPaymentData['Data'];
            $Data['Total'] = $TotalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Delete Payment';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }
    function getChemPoPaymentDetails($RequestBody)
    {
        $ChemPoID = $RequestBody['ChemPoID'];
        $where = '';
        if(!empty($RequestBody['ChemPaymentID'])){
            $where = ' AND ChemPaymentID='.$RequestBody['ChemPaymentID'];
        }
        $sqlFields = "GCPD.ChemPoID, GCPD.ChemPaymentID, GCPs.ChemPoNo, GCM.ChemicalName, GCPs.BuyerPartryID, GCBD.BuyerName, 
        sum(GCPB.QuantityInKgs) AS TotalQty, sum(GCPB.QuantityInKgs * GCPB.RatePerKgInRupees) AS Amount, GCPD.InvoiceNo, GCPD.InvoiceDt, 
        GCPD.InvoiceAmount, GCPD.PaymentDtls, GCPD.Remarks,
        GCSD.SupplierName AS BuyerName,(SELECT SUM(QuantityReceived) FROM GsChemReceivedQtys WHERE ChemPoID =GCPD.ChemPoID) AS TotalQty ";

        if ($ChemPoID != '') {

            $sqlQuery = "SELECT $sqlFields FROM GsChemPOPaymentDetails AS  GCPD
            LEFT JOIN GsChemicalPos  AS GCPs ON GCPD.ChemPoID = GCPs.ChemPoID
            LEFT JOIN GsChemBuyersData AS GCBD ON GCPs.BuyerPartryID = GCBD.ChemBuyerID
            LEFT JOIN GsChemSuppliersData AS GCSD ON GCPs.SupplierPartyID = GCSD.ChemSupplierID
            LEFT JOIN GsChemicalPoBreakUp AS GCPB ON GCPD.ChemPoID = GCPB.ChemPoID
            LEFT JOIN GsChemicalMaster AS GCM ON GCPB.ChemicalID = GCM.ChemID
            WHERE GCPD.ChemPoID='$ChemPoID' $where
            GROUP BY GCPD.ChemPoID, GCPD.ChemPaymentID, GCPs.ChemPoNo, GCM.ChemicalName, GCPs.BuyerPartryID, GCBD.BuyerName, 
            GCPD.InvoiceNo, GCPD.InvoiceDt, GCPD.InvoiceAmount, GCPD.PaymentDtls, GCPD.Remarks";
        } else {

            $sqlQuery = "SELECT $sqlFields FROM GsChemPOPaymentDetails AS  GCPD
            LEFT JOIN GsChemicalPos  AS GCPs ON GCPD.ChemPoID = GCPs.ChemPoID
            LEFT JOIN GsChemBuyersData AS GCBD ON GCPs.BuyerPartryID = GCBD.ChemBuyerID
            LEFT JOIN GsChemSuppliersData AS GCSD ON GCPs.SupplierPartyID = GCSD.ChemSupplierID
            LEFT JOIN GsChemSuppliersData AS GCSD ON GCPs.SupplierPartyID = GCSD.ChemSupplierID
            LEFT JOIN GsChemicalPoBreakUp AS GCPB ON GCPD.ChemPoID = GCPB.ChemPoID
            LEFT JOIN GsChemicalMaster AS GCM ON GCPB.ChemicalID = GCM.ChemID
            WHERE 1=1 ORDER BY ChemPoID";
        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayChemPoPaymentData = [];

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayChemPoPaymentList['SNO'] = ++$i;
            $ArrayChemPoPaymentList['ChemPoID'] = $dataRow['ChemPoID'];
            $ArrayChemPoPaymentList['ChemPaymentID'] = $dataRow['ChemPaymentID'];
            $ArrayChemPoPaymentList['ChemPoNo'] = $dataRow['ChemPoNo'];
            $ArrayChemPoPaymentList['ChemicalName'] = $dataRow['ChemicalName'];
            $ArrayChemPoPaymentList['BuyerPartryID'] = $dataRow['BuyerPartryID'];
            $ArrayChemPoPaymentList['BuyerName'] = $dataRow['BuyerName'];
            $ArrayChemPoPaymentList['TotalQty'] = $dataRow['TotalQty'];
            $ArrayChemPoPaymentList['Amount'] = $dataRow['Amount'];
            $ArrayChemPoPaymentList['InvoiceNo'] = $dataRow['InvoiceNo'];
            $ArrayChemPoPaymentList['InvoiceDt'] = $dataRow['InvoiceDt'];
            $ArrayChemPoPaymentList['InvoiceAmount'] = $dataRow['InvoiceAmount'];
            $ArrayChemPoPaymentList['PaymentDtls'] = $dataRow['PaymentDtls'];
            $ArrayChemPoPaymentList['Remarks'] = $dataRow['Remarks'];

            $ArrayChemPoPaymentData[] =  $ArrayChemPoPaymentList;
        }

        $TotalData = $this->getPoPaymentTotals($RequestBody);
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['EditLink'] = "GsChemPoPaymentCreate.php?ChemPoID=";
        $data['Data'] = $ArrayChemPoPaymentData;
        $data['Total'] = $TotalData['Data'];

        return $data;
    }

    function getPoPaymentTotals($RequestBody)
    {

        $ChemPoID = $RequestBody['ChemPoID'];

        $sqlTotal = "SELECT sum(InvoiceAmount) AS TotalInvAmt FROM GsChemPOPaymentDetails WHERE ChemPoID='$ChemPoID'";

        $stmtTotal = $this->conn->prepare($sqlTotal);
        $stmtTotal->execute();

        $ArrayTotal = [];

        while ($dataRow = $stmtTotal->fetch(PDO::FETCH_ASSOC)) {
            $ArrayTotalData['TotalInvAmt'] = $dataRow['TotalInvAmt'];

            $ArrayTotal[] = $ArrayTotalData;
        }

        $data['Data'] = $ArrayTotal;

        return $data;
    }
    function getPoAmountTotals($RequestBody)
    {

        $ChemPoID = $RequestBody['ChemPoID'];

        $sqlTotal = "SELECT SUM(QuantityInKgs * RatePerKgInRupees) AS TotalAmount FROM GsChemicalPoBreakUp WHERE ChemPoID='$ChemPoID'";

        $stmtTotal = $this->conn->prepare($sqlTotal);
        $stmtTotal->execute();

        $ArrayTotal = [];

        while ($dataRow = $stmtTotal->fetch(PDO::FETCH_ASSOC)) {
            $ArrayTotalData['TotalAmount'] = $dataRow['TotalAmount'];

            $ArrayTotal[] = $ArrayTotalData;
        }

        $data['Data'] = $ArrayTotal;

        return $data;
    }
}
