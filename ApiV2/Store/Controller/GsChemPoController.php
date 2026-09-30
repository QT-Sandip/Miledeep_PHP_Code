<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class GsChemPoController
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

    public function postChemPoDetails($RequestBody)
    {
        $ChemPoID = $RequestBody['ChemPoID'];
        if ($ChemPoID == '') {
            $Data = $this->insertChemPoDetails($RequestBody);
        } else {
            $Data = $this->updateChemPoDetails($RequestBody);
        }

        return $Data;
    }

    public function insertChemPoDetails($RequestBody)
    {

        $SupplierPartyID = $RequestBody['SupplierPartyID'];
        $ChemPoNo = $RequestBody['ChemPoNo'];
        $ChemPoDate = $RequestBody['ChemPoDate'];
        $ChemETADate = $RequestBody['ChemETADate'];
        $ChemETDDate = $RequestBody['ChemETDDate'];
        $PortID = $RequestBody['PortID'];
        $ModeOfImport = $RequestBody['ModeOfImport'];
        $Remarks = $RequestBody['Remarks'];
        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");
        $FinancialYear = $this->getFinancialYearFromPostedDate($PostedDate);

        $sqlInsertCheck = "SELECT COUNT(*) as Total FROM GsChemicalPos WHERE ChemPoNo = '$ChemPoNo'";
        $stmtInsertCheck = $this->conn->prepare($sqlInsertCheck);
        $stmtInsertCheck->execute();
        $dataRowCheck = $stmtInsertCheck->fetch(PDO::FETCH_ASSOC);
        $RowCount = $dataRowCheck['Total'];

        if ($RowCount == 0) {
            $sqlInsert = "INSERT INTO GsChemicalPos(SupplierPartyID, ChemPoNo, ChemPoDate,PortID, 
            ModeOfImport, Remarks, PostedDateTime,ChemicalETADate, ChemicalETDDate, FinancialYear)
            VALUES ('$SupplierPartyID','$ChemPoNo','$ChemPoDate','$PortID', '$ModeOfImport', 
            '$Remarks', '$PostedDate','$ChemETADate', '$ChemETDDate', '$FinancialYear')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {

                $lastInsertedId = $this->conn->lastInsertId();
                $RequestBody['ChemPoID'] = $lastInsertedId;
                $createdPo = $this->auditLogger->fetchRowById('GsChemicalPos', 'ChemPoID', $lastInsertedId);
                $this->auditLogger->logInsert('GsChemicalPos', $lastInsertedId, $createdPo, $RequestBody, [
                    'ReferenceNo' => $ChemPoNo,
                    'Remarks' => 'Store chemical PO created',
                ]);

                $chemPoData = $this->getChemPoDetails($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'PO Posted Successfully';
                $Data['Data'] = $chemPoData['Data'];
            } else {
                $Data['Error'] = 'Unable to Insert PO';
                $Data['Msg'] = $this->Error;
            }
        } else {
            $Data['Error'] = 'PoNo already exist';
            $Data['Msg'] = $this->Error;
        }
        return $Data;
    }

    public function updateChemPoDetails($RequestBody)
    {
        $ChemPoID = $RequestBody['ChemPoID'];
        $SupplierPartyID = $RequestBody['SupplierPartyID'];
        $ChemPoNo = $RequestBody['ChemPoNo'];
        $ChemPoDate = $RequestBody['ChemPoDate'];
        $ChemETADate = $RequestBody['ChemETADate'];
        $ChemETDDate = $RequestBody['ChemETDDate'];
        $PortID = $RequestBody['PortID'];
        $ModeOfImport = $RequestBody['ModeOfImport'];
        $Remarks = $RequestBody['Remarks'];
        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");
        $existingPo = $this->auditLogger->fetchRowById('GsChemicalPos', 'ChemPoID', $ChemPoID);

        $sqlUpdateCheck = "SELECT COUNT(*) as Total FROM GsChemicalPos WHERE ChemPoNo = '$ChemPoNo' AND ChemPoID != '$ChemPoID'";
        $stmtUpdateCheck = $this->conn->prepare($sqlUpdateCheck);
        $stmtUpdateCheck->execute();
        $dataRowCheck = $stmtUpdateCheck->fetch(PDO::FETCH_ASSOC);
        $RowCount = $dataRowCheck['Total'];
        if ($RowCount == 0) {
            $sqlUpdate = "UPDATE GsChemicalPos SET SupplierPartyID='$SupplierPartyID',
            ChemPoNo='$ChemPoNo',PostedDateTime='$PostedDate',ChemPoDate='$ChemPoDate',
            ChemicalETADate='$ChemETADate',ChemicalETDDate='$ChemETDDate',
            PortID='$PortID', ModeOfImport='$ModeOfImport', Remarks='$Remarks'
            WHERE ChemPoID='$ChemPoID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            if ($stmtUpdate) {
                $updatedPo = $this->auditLogger->fetchRowById('GsChemicalPos', 'ChemPoID', $ChemPoID);
                $this->auditLogger->logUpdate('GsChemicalPos', $ChemPoID, $existingPo, $updatedPo, $RequestBody, [
                    'ReferenceNo' => $ChemPoNo,
                    'Remarks' => 'Store chemical PO updated',
                ]);
                $chemPoData = $this->getChemPoDetails($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'PO Updated Successfully';
                $Data['Data'] = $chemPoData['Data'];
            } else {
                $Data['Error'] = 'Unable to update PO';
                $Data['Msg'] = $this->Error;
                // return $Data;
            }
        } else {
            $Data['Error'] = 'PoNo already Exist';
            $Data['Msg'] = $this->Error;
        }
        return $Data;
    }

    function getChemPoDetails($RequestBody)
    {
        $ChemPoID = $RequestBody['ChemPoID'];
        $Year = $RequestBody['Year'];
        $Supplier = $RequestBody['Supplier'];
        $PoNo = $RequestBody['PoNo'];
        $Chemical = $RequestBody['Chemical'];
        $Port = $RequestBody['Port'];

        $Query = '';

        $StartDate = $Year . '-04-01';
        $EndDate = ($Year + 1) . '-03-31';
        $DateFilter = "BETWEEN '$StartDate' AND '$EndDate'";

        $Query = '';
        if ($Supplier != '') {
            $Query .= " AND GCPs.SupplierPartyID = '$Supplier'";
        }
        if ($PoNo != "") {
            $Query .= " AND GCPs.ChemPoID = '$PoNo'";
        }
        if ($Chemical != "") {
            $Query .= " AND GCPB.ChemicalID = '$Chemical'";
        }
        if ($Port != "") {
            $Query .= " AND GCPs.PortID = '$Port'";
        }

        $sqlFields = "GCPs.ChemPoID, GCPs.BuyerPartryID, GCPs.SupplierPartyID, GCPs.ChemPoNo, GCPs.ChemPoDate, 
        GCPs.PostedDateTime, GCPs.PostedUserID,ChemicalETDDate, ChemicalETADate,
        GCPs.PortID, GCPs.ModeOfImport, GCPs.Remarks, GCBD.BuyerName, GCSD.SupplierName,
        GROUP_CONCAT(DISTINCT CONCAT(SPC.PortCode, ' - ', SPC.Name) ORDER BY SPC.Name SEPARATOR ', ') AS PortName,
        GROUP_CONCAT(DISTINCT GCM.ChemicalName SEPARATOR ', ') AS ChemicalNames,
        sum(GCPB.QuantityInKgs) AS TotalQty,sum(GCPB.QuantityInBags) AS TotalBags,
         sum(GCPB.QuantityInKgs * GCPB.RatePerKgInRupees) AS TotalAmount,
         (SELECT SUM(QuantityReceived) FROM GsChemReceivedQtys WHERE ChemPoID = GCPs.ChemPoID) AS QuantityReceived";
        // 80 * sum(GCPB.QuantityInKgs * GCPB.RatePerKgInRupees) AS `TotalAmountUSD`";
        //  ChemETADate, ChemETDDate,

        if ($ChemPoID != '') {
            $sqlQuery = "SELECT $sqlFields FROM GsChemicalPos AS GCPs 
            LEFT JOIN GsChemBuyersData AS GCBD ON GCPs.BuyerPartryID = GCBD.ChemBuyerID
            LEFT JOIN GsChemSuppliersData AS GCSD ON GCPs.SupplierPartyID = GCSD.ChemSupplierID
            LEFT JOIN GsChemicalPoBreakUp AS GCPB ON GCPs.ChemPoID = GCPB.ChemPoID
            LEFT JOIN GsChemicalMaster AS GCM ON GCPB.ChemicalID = GCM.ChemID
            LEFT JOIN ScrmPortCities AS SPC ON GCPs.PortID = SPC.PortId
            WHERE GCPs.ChemPoID='$ChemPoID'
            GROUP BY GCPs.ChemPoID";
        } else {

            $sqlQuery = "SELECT $sqlFields FROM GsChemicalPos AS GCPs
            LEFT JOIN GsChemBuyersData AS GCBD ON GCPs.BuyerPartryID = GCBD.ChemBuyerID
            LEFT JOIN GsChemSuppliersData AS GCSD ON GCPs.SupplierPartyID = GCSD.ChemSupplierID
            LEFT JOIN GsChemicalPoBreakUp AS GCPB ON GCPs.ChemPoID = GCPB.ChemPoID
            LEFT JOIN GsChemicalMaster AS GCM ON GCPB.ChemicalID = GCM.ChemID
            LEFT JOIN ScrmPortCities AS SPC ON GCPs.PortID = SPC.PortId
            WHERE ChemPoDate $DateFilter $Query
            GROUP BY GCPs.ChemPoID, GCPs.BuyerPartryID,  GCPs.ChemPoNo, GCPs.ChemPoDate, GCPs.PostedDateTime, 
            GCPs.PostedUserID, GCBD.BuyerName, SPC.PortId
            ORDER BY GCPs.ChemPoID DESC";
        }
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayChemPoData = [];

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayChemPoList['SNO'] = ++$i;
            $ArrayChemPoList['ChemPoID'] = $dataRow['ChemPoID'];
            $ArrayChemPoList['BuyerPartryID'] = $dataRow['BuyerPartryID'];
            $ArrayChemPoList['BuyerName'] = $dataRow['BuyerName'];
            $ArrayChemPoList['SupplierPartyID'] = $dataRow['SupplierPartyID'];
            $ArrayChemPoList['SupplierName'] = $dataRow['SupplierName'];
            $ArrayChemPoList['ChemPoNo'] = $dataRow['ChemPoNo'];
            $ArrayChemPoList['ChemicalNames'] = $dataRow['ChemicalNames'];
            $ArrayChemPoList['ChemPoDate'] = $dataRow['ChemPoDate'];
            $ArrayChemPoList['ChemETDDate'] = $dataRow['ChemicalETDDate'];
            $ArrayChemPoList['ChemETADate'] = $dataRow['ChemicalETADate'];
            $ArrayChemPoList['PortID'] = $dataRow['PortID'];
            $ArrayChemPoList['PortName'] = $dataRow['PortName'];
            $ArrayChemPoList['ModeOfImport'] = $dataRow['ModeOfImport'];
            $ArrayChemPoList['Mode'] = $dataRow['ModeOfImport'];
            $ArrayChemPoList['Remarks'] = $dataRow['Remarks'];
            $ArrayChemPoList['PostedDateTime'] = $dataRow['PostedDateTime'];
            $ArrayChemPoList['TotalBags'] = $dataRow['TotalBags'];
            $ArrayChemPoList['TotalQty'] = $dataRow['TotalQty'];
            $ArrayChemPoList['TotalAmount'] = $dataRow['TotalAmount'];
            $ArrayChemPoList['TotalAmountUSD'] = $dataRow['TotalAmountUSD'];
            $ArrayChemPoList['QuantityReceived'] = $dataRow['QuantityReceived'];

            $ArrayChemPoData[] =  $ArrayChemPoList;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['RemainingCount'] = "";
        $data['EditLink'] = "GsChemPoCreate.php?ChemPoID=";
        $data['AddLink'] = "GsChemPoBrkUpCreate.php?ChemPoID=";
        $data['PrintLink'] = "Reports/ChemicalPoPrint.php?ChemPoID=";
        $data['Data'] = $ArrayChemPoData;

        return $data;
    }

    function generateChemPoNo($RequestBody)
    {
        $ClientID = $RequestBody['ClientID'];
        $ClientPrefix = substr($ClientID, 0, 3);
        $SupplierID = $RequestBody['SupplierID'];

        $sqlQuery = "SELECT SupplierCode FROM GsChemSuppliersData WHERE ChemSupplierID = '$SupplierID'";
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();
        $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        $supplierCode = $dataRow['SupplierCode'];
        $financialYear = getFinancialYear();
        $lastPoNo = '';

        $sql = "SELECT ChemPoNo FROM GsChemicalPos
            WHERE SupplierPartyID = ? AND FinancialYear = ?
            ORDER BY ChemPoID DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$SupplierID, $financialYear]);
        $lastPoNo = $stmt->fetchColumn();

        if ($lastPoNo) {
            $parts = explode('/', $lastPoNo);
            $lastNumber = (int)end($parts);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        $NewPoNo = "$ClientPrefix/$supplierCode/$financialYear/$formattedNumber";

        $data['Error'] = null;
        $data['AutoGeneratedPoNo'] = $NewPoNo;

        return $data;
    }

    function getFinancialYearFromPostedDate($dateString)
    {
        $timestamp = strtotime($dateString);

        if ($timestamp === false) {
            return null;
        }

        $year = date('Y', $timestamp);
        $month = date('n', $timestamp);

        if ($month >= 4) {
            $startYear = $year;
            $endYear = $year + 1;
        } else {
            $startYear = $year - 1;
            $endYear = $year;
        }

        return substr($startYear, 2, 2) . substr($endYear, 2, 2);
    }
}
