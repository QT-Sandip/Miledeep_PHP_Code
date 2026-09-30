<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class GsChemPoPaymtListController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }

    function getChemPoDetails($RequestBody)
    {
        $RecordsPerPage = 10;
        $ChemPoID = $RequestBody['ChemPoID'];
        // $ChemPoDate = $RequestBody['ChemPoDate'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $ChemPoDate = $RequestBody['ChemPoDate'];
        $Supplier = $RequestBody['Supplier'];

        $Query = '';


        if ($ChemPoID != "") {
            $Query .= " and GsChemPOPaymentDetails.ChemPoID = '$ChemPoID'";
        }

        // if ($ChemPoDate != "") {
        //     $yearFilter = date('Y', strtotime($ChemPoDate));
        //     $Query .= " AND YEAR(GCPs.ChemPoDate) = '$yearFilter'";
        // }

        $RecordsPerPage = 10;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', ChemicalName,BuyerName,ChemPoNo,InvoiceNo, PaymentDtls) LIKE '%$SearchKey%'";
        }
        if ($ChemPoDate != "") {
            $Query .= " AND YEAR(`ChemPoDate`)= '$ChemPoDate'";
        }
        if ($Supplier != "") {
            $Query .= " AND `SupplierPartyID` = '$Supplier'";
        }

        $sqlVarietyCount = "SELECT COUNT(DISTINCT ChemPoID) as Total  FROM `GsChemPOPaymentDetails`";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "GCPs.ChemPoID, GCPs.ChemPoNo, GCBD.BuyerName, DerivedTable.TotalQty, DerivedTable.Amount,
        GROUP_CONCAT(DISTINCT GCM.ChemicalName SEPARATOR ', ') AS ChemicalNames, 
        GROUP_CONCAT(DISTINCT GCPD.InvoiceNo SEPARATOR ', ') AS InvoiceNos, 
        MAX(GCPD.InvoiceDt) AS LatestInvDt, SUM(GCPD.InvoiceAmount) AS TotalInvAmount, 
        MAX(GCPD.PaymentDtls) AS LatestPaymentDtls, MAX(GCPD.Remarks) AS LatestRemarks, 
        MAX(GCPD.ChemPaymentID) AS LatestChemPaymentID,
        CASE WHEN Amount = sum(GCPD.InvoiceAmount) THEN 1 ELSE 0 END AS Status";


        if ($ChemPoID != '') {

            $sqlQuery = "SELECT $sqlFields FROM  GsChemPOPaymentDetails AS  GCPD
            LEFT JOIN GsChemicalPos  AS GCPs ON GCPD.ChemPoID = GCPs.ChemPoID
            LEFT JOIN GsChemBuyersData AS GCBD ON GCPs.BuyerPartryID = GCBD.ChemBuyerID
            LEFT JOIN GsChemSuppliersData AS GCSD ON GCPs.SupplierPartyID = GCSD.ChemSupplierID
            LEFT JOIN GsChemicalPoBreakUp AS GCPB ON GCPD.ChemPoID = GCPB.ChemPoID
            LEFT JOIN GsChemicalMaster AS GCM ON GCPB.ChemicalID = GCM.ChemID
            WHERE GCPD.ChemPoID='$ChemPoID' 
            GROUP BY GCPD.ChemPoID, GCPs.ChemPoNo, GCBD.BuyerName";
        } else {

            $sqlQuery = "SELECT ChemPoID,ChemPoNo,BuyerName,ChemicalNames,TotalQty,Amount,PaidAmount,InvoiceNos,LatestInvDt,TotalInvAmount,
            LatestPaymentDtls,LatestRemarks,LatestChemPaymentID,
            CASE WHEN Amount = TotalInvAmount THEN 1 ELSE 0 END AS Status,SupplierName AS BuyerName,QuantityReceived AS TotalQty
            FROM ( SELECT GCPs.ChemPoID, GCPs.ChemPoNo, GCBD.BuyerName,
                GROUP_CONCAT(DISTINCT GCM.ChemicalName SEPARATOR ', ') AS ChemicalNames,
                (SELECT SUM(GCPB.QuantityInKgs) FROM GsChemicalPoBreakUp GCPB WHERE GCPB.ChemPoID = GCPs.ChemPoID ) AS TotalQty,
                (SELECT SUM(GCPB.QuantityInKgs * GCPB.RatePerKgInRupees) FROM GsChemicalPoBreakUp GCPB 
                WHERE GCPB.ChemPoID = GCPs.ChemPoID ) AS Amount,
                (SELECT SUM(InvoiceAmount) AS TotalInvAmt FROM GsChemPOPaymentDetails WHERE ChemPoID=GCPD.ChemPoID ) AS PaidAmount,
                GROUP_CONCAT(DISTINCT GCPD.InvoiceNo SEPARATOR ', ') AS InvoiceNos,
                MAX(GCPD.InvoiceDt) AS LatestInvDt,
                (SELECT SUM(GCPDInner.InvoiceAmount) FROM GsChemPOPaymentDetails GCPDInner
                WHERE GCPDInner.ChemPoID = GCPs.ChemPoID ) AS TotalInvAmount,
                MAX(GCPD.PaymentDtls) AS LatestPaymentDtls,
                MAX(GCPD.Remarks) AS LatestRemarks,
                MAX(GCPD.ChemPaymentID) AS LatestChemPaymentID,
                 GCSD.SupplierName,(SELECT SUM(QuantityReceived) FROM GsChemReceivedQtys WHERE ChemPoID =GCPD.ChemPoID) AS QuantityReceived 
            FROM GsChemPOPaymentDetails AS GCPD
            LEFT JOIN GsChemicalPos AS GCPs ON GCPD.ChemPoID = GCPs.ChemPoID
            LEFT JOIN GsChemBuyersData AS GCBD ON GCPs.BuyerPartryID = GCBD.ChemBuyerID
            LEFT JOIN GsChemSuppliersData AS GCSD ON GCPs.SupplierPartyID = GCSD.ChemSupplierID
            LEFT JOIN GsChemicalPoBreakUp AS GCPB ON GCPD.ChemPoID = GCPB.ChemPoID
            LEFT JOIN GsChemicalMaster AS GCM ON GCPB.ChemicalID = GCM.ChemID
            WHERE 1=1 $Query GROUP BY GCPD.ChemPoID ) AS DerivedTable
            ORDER BY ChemPoID $Limit";
        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayChemPoPaymentData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayChemPoPaymentList['SNO'] = ++$i;
            $ArrayChemPoPaymentList['ChemPoID'] = $dataRow['ChemPoID'];
            $ArrayChemPoPaymentList['LstPmtID'] = $dataRow['LatestChemPaymentID'];
            $ArrayChemPoPaymentList['ChemPoNo'] = $dataRow['ChemPoNo'];
            $ArrayChemPoPaymentList['ChemicalNames'] = $dataRow['ChemicalNames'];
            $ArrayChemPoPaymentList['BuyerName'] = $dataRow['BuyerName'];
            $ArrayChemPoPaymentList['TotalQty'] = $dataRow['TotalQty'];
            $ArrayChemPoPaymentList['Amount'] = $dataRow['Amount'];
            $ArrayChemPoPaymentList['PaidAmount'] = $dataRow['PaidAmount'];
            $ArrayChemPoPaymentList['InvoiceNos'] = $dataRow['InvoiceNos'];
            $ArrayChemPoPaymentList['LstInvDt'] = $dataRow['LatestInvDt'];
            $ArrayChemPoPaymentList['TotalInvAmount'] = $dataRow['TotalInvAmount'];
            $ArrayChemPoPaymentList['LstPmtDtls'] = $dataRow['LatestPaymentDtls'];
            $ArrayChemPoPaymentList['Remarks'] = $dataRow['LatestRemarks'];
            $ArrayChemPoPaymentList['Status'] = $dataRow['Status'];
            $ArrayChemPoPaymentList['LatestChemPaymentID'] = $dataRow['LatestChemPaymentID'];

            $ArrayChemPoPaymentData[] =  $ArrayChemPoPaymentList;
        }

        if (count($ArrayChemPoPaymentData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
       // $data['EditLink'] = "GsChemPoPaymentCreate.php?ChemPoID=";
        $data['EditLink'] = "GsChemPoPaymentCreate.php?ChemPaymentID=";
        $data['AddLink'] = "GsChemPoPaymentCreate.php?ChemPoID=";
        $data['Data'] = $ArrayChemPoPaymentData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
