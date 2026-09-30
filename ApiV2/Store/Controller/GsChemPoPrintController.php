<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class GsChemPoPrintController
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
        $ChemPoID = $RequestBody['ChemPoID'];

        // CurrencyID,CurrencyName,CurrenySymbol
        $sqlFields = "GCPB.ChemPoID, GCP.ChemPoNo, GCP.Remarks, GCP.ModeOfImport,  GCP.ChemPoDate, GCPB.ChemicalID,
         GCM.ChemicalName, GCPB.QuantityInKgs, GCPB.RatePerKgInRupees,SupplierPartyID,SupplierName,SupplierAddress,
        (GCPB.QuantityInKgs * GCPB.RatePerKgInRupees) AS Amount, GCPB.CurrencyType,CurrencyName,CurrenySymbol,CurrencyCode,CurrenySymbol";
        // echo 
        $sqlQuery = "SELECT $sqlFields FROM  GsChemicalPoBreakUp AS GCPB
        LEFT JOIN GsChemicalPos AS GCP ON GCPB.ChemPoID=GCP.ChemPoID
        LEFT JOIN GsChemicalMaster AS GCM ON GCPB.ChemicalID=GCM.ChemID
        LEFT JOIN ScrmCurrencyMaster AS SCM ON GCPB.CurrencyType = SCM.CurrencyID
        LEFT JOIN GsChemSuppliersData AS GCSD ON GCP.SupplierPartyID = GCSD.ChemSupplierID
        WHERE GCPB.ChemPoID='$ChemPoID' ORDER BY ChemPoID";

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayChemPoPaymentData = [];

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $dataRow['CurrenySymbol'] = html_entity_decode($dataRow['CurrenySymbol'], ENT_QUOTES, 'UTF-8');

            $ArrayChemPoPaymentList['SNO'] = ++$i;
            $ArrayChemPoPaymentList['ChemPoID'] = $dataRow['ChemPoID'];
            $ArrayChemPoPaymentList['ChemPoNo'] = $dataRow['ChemPoNo'];
            $ArrayChemPoPaymentList['SupplierName'] = $dataRow['SupplierName'];
            $ArrayChemPoPaymentList['SupplierAddress'] = $dataRow['SupplierAddress'];
            $ArrayChemPoPaymentList['ChemPoDate'] = $dataRow['ChemPoDate'];
            $ArrayChemPoPaymentList['ChemicalID'] = $dataRow['ChemicalID'];
            $ArrayChemPoPaymentList['Remarks'] = $dataRow['Remarks'];
            $ArrayChemPoPaymentList['ModeOfImport'] = $dataRow['ModeOfImport'];
            $ArrayChemPoPaymentList['ChemicalName'] = $dataRow['ChemicalName'];
            $ArrayChemPoPaymentList['CurrenySymbol'] = $dataRow['CurrenySymbol'];
            $ArrayChemPoPaymentList['QuantityInKgs'] = $dataRow['QuantityInKgs'];
            $ArrayChemPoPaymentList['RatePerKgInRupees'] = $dataRow['RatePerKgInRupees'];
            $ArrayChemPoPaymentList['Amount'] = $dataRow['Amount'];
            $ArrayChemPoPaymentList['CurrencyCode'] = $dataRow['CurrencyCode'];

            $ArrayChemPoPaymentData[] =  $ArrayChemPoPaymentList;
            
        }

        $TotalData = $this->getTotals($RequestBody);
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['Data'] = $ArrayChemPoPaymentData;
        $data['Total'] = $TotalData['Data'];

        return $data;
    }

    function getTotals($RequestBody)
    {

        $ChemPoID = $RequestBody['ChemPoID'];

        $sqlTotal = "SELECT SUM(QuantityInKgs * RatePerKgInRupees) AS TotalAmount FROM GsChemicalPoBreakUp WHERE ChemPoID='$ChemPoID'";

        $stmtTotal = $this->conn->prepare($sqlTotal);
        $stmtTotal->execute();

        $ArrayTotalData = [];

        while ($dataRow = $stmtTotal->fetch(PDO::FETCH_ASSOC)) {

            $ArrayTotalList['TotalAmount'] = $dataRow['TotalAmount'];

            $ArrayTotalData[] =  $ArrayTotalList;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['Data'] = $ArrayTotalData;

        return $data;
    }
}
