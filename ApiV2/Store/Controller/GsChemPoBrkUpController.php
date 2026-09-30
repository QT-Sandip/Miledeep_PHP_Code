<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class GsChemPoBrkUpController
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

    public function postChemPoBrkUpDetails($RequestBody)
    {
        $ChemPoBreakUpID = $RequestBody['ChemPoBreakUpID'];

        if ($ChemPoBreakUpID == '') {
            $Data = $this->insertChemPoBrkUpDetails($RequestBody);
        } else {
            $Data = $this->updateChemPoBrkUpDetails($RequestBody);
        }

        return $Data;
    }

    public function insertChemPoBrkUpDetails($RequestBody)
    {
        $ChemPoBreakUpID = $RequestBody['ChemPoBreakUpID'];
        $ChemPoID = $RequestBody['ChemPoID'];
        $ChemicalID = $RequestBody['ChemicalID'];
        $QuantityInKgs = $RequestBody['QuantityInKgs'];
        $QuantityInBags = $RequestBody['QuantityInBags'];
        $CurrencyType = $RequestBody['CurrencyType'];
        $RatePerKgInRupees = $RequestBody['RatePerKgInRupees'];

        $sqlInsert = "INSERT INTO GsChemicalPoBreakUp( ChemPoID, ChemicalID, QuantityInKgs, QuantityInBags, CurrencyType, RatePerKgInRupees) 
            VALUES ('$ChemPoID','$ChemicalID','$QuantityInKgs', '$QuantityInBags','$CurrencyType','$RatePerKgInRupees')";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['ChemPoBreakUpID'] = $lastInsertedId;
            $createdBreakup = $this->auditLogger->fetchRowById('GsChemicalPoBreakUp', 'ChemPoBreakUpID', $lastInsertedId);
            $this->auditLogger->logInsert('GsChemicalPoBreakUp', $lastInsertedId, $createdBreakup, $RequestBody, [
                'ReferenceNo' => $ChemPoID,
                'Remarks' => 'Store chemical PO breakup created',
            ]);

            $chemPoBrkUpData = $this->getChemPoBrkUpDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'PO BreakUp Posted Successfully';
            $Data['EditLink'] = $chemPoBrkUpData['EditLink'];
            $Data['Data'] = $chemPoBrkUpData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert PO BreakUp';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateChemPoBrkUpDetails($RequestBody)
    {
        $ChemPoBreakUpID = $RequestBody['ChemPoBreakUpID'];
        $ChemPoID = $RequestBody['ChemPoID'];
        $ChemicalID = $RequestBody['ChemicalID'];
        $QuantityInKgs = $RequestBody['QuantityInKgs'];
        $QuantityInBags = $RequestBody['QuantityInBags'];
        $CurrencyType = $RequestBody['CurrencyType'];
        $RatePerKgInRupees = $RequestBody['RatePerKgInRupees'];
        $existingBreakup = $this->auditLogger->fetchRowById('GsChemicalPoBreakUp', 'ChemPoBreakUpID', $ChemPoBreakUpID);

        $sqlUpdate = "UPDATE GsChemicalPoBreakUp SET ChemPoID='$ChemPoID',ChemicalID='$ChemicalID',QuantityInKgs='$QuantityInKgs',
        QuantityInBags='$QuantityInBags',RatePerKgInRupees='$RatePerKgInRupees',CurrencyType='$CurrencyType'
        WHERE ChemPoBreakUpID='$ChemPoBreakUpID'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $updatedBreakup = $this->auditLogger->fetchRowById('GsChemicalPoBreakUp', 'ChemPoBreakUpID', $ChemPoBreakUpID);
            $this->auditLogger->logUpdate('GsChemicalPoBreakUp', $ChemPoBreakUpID, $existingBreakup, $updatedBreakup, $RequestBody, [
                'ReferenceNo' => $ChemPoID,
                'Remarks' => 'Store chemical PO breakup updated',
            ]);
            $chemPoBrkUpData = $this->getChemPoBrkUpDetails($RequestBody);
            $TotalData = $this->getPoBrkUpTotals($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'PO BreakUp Updated Successfully';
            $Data['Data'] = $chemPoBrkUpData['Data'];
            $Data['Total'] = $TotalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update PO BreakUp';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function deleteChemPoBrkUpDetails($RequestBody)
    {
        $ChemPoBreakUpID = $RequestBody['ChemPoBreakUpID'];
        $existingBreakup = $this->auditLogger->fetchRowById('GsChemicalPoBreakUp', 'ChemPoBreakUpID', $ChemPoBreakUpID);

        $sqlDelete = "DELETE FROM `GsChemicalPoBreakUp` WHERE ChemPoBreakUpID='$ChemPoBreakUpID'";

        $stmtUpdate = $this->conn->prepare($sqlDelete);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $this->auditLogger->logDelete('GsChemicalPoBreakUp', $ChemPoBreakUpID, $existingBreakup, $RequestBody, [
                'ReferenceNo' => isset($existingBreakup['ChemPoID']) ? $existingBreakup['ChemPoID'] : $ChemPoBreakUpID,
                'Remarks' => 'Store chemical PO breakup deleted',
            ]);
            $chemPoBrkUpData = $this->getChemPoBrkUpDetails($RequestBody);
            $TotalData = $this->getPoBrkUpTotals($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'PO BreakUp Deleted Successfully';
            $Data['Data'] = $chemPoBrkUpData['Data'];
            $Data['Total'] = $TotalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Delete PO BreakUp';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function getChemPoBrkUpDetails($RequestBody)
    {

        $ChemPoID = $RequestBody['ChemPoID'];

        $sqlFields = "GCPB.ChemPoBreakUpID, GCPB.ChemPoID, GCPB.ChemicalID, GCPB.QuantityInKgs,GCPB.QuantityInBags, 
        GCPB.CurrencyType,GCPB.RatePerKgInRupees, GCM.ChemicalName,CurrencyName,CurrenySymbol,
        (GCPB.QuantityInKgs * GCPB.RatePerKgInRupees) AS Amout";

        // SELECT ChemPoBreakUpID, ChemPoID, ChemicalID, QuantityInKgs, CurrencyType, RatePerKgInRupees, 
        // TotalAmount FROM GsChemicalPoBreakUp WHERE 1

        if ($ChemPoID != '') {
            $sqlQuery = "SELECT $sqlFields FROM GsChemicalPoBreakUp AS GCPB
            LEFT JOIN GsChemicalMaster AS GCM ON GCPB.ChemicalID = GCM.ChemID
            LEFT JOIN ScrmCurrencyMaster AS SCM ON GCPB.CurrencyType = SCM.CurrencyID
            WHERE ChemPoID='$ChemPoID'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM GsChemicalPoBreakUp  AS GCPB
            LEFT JOIN GsChemicalMaster AS GCM ON GCPB.ChemicalID = GCM.ChemID
            LEFT JOIN ScrmCurrencyMaster AS SCM ON GCPB.CurrencyType = SCM.CurrencyID
            WHERE 1=1 ORDER BY ChemPoID ";
        }
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayChemPoBrkUpData = [];

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ChemPoID = $dataRow['ChemPoID'];
            $ChemicalID = $dataRow['ChemicalID'];

            $sqlReceivedQty = "SELECT SUM(ReceivedBags) AS TotalReceivedBags FROM GsChemReceivedQtys 
            WHERE ChemPoID = '$ChemPoID' AND ChemicalID = '$ChemicalID'";
            $stmtReceivedQty = $this->conn->prepare($sqlReceivedQty);
            $stmtReceivedQty->execute();
            $dataReceived = $stmtReceivedQty->fetch(PDO::FETCH_ASSOC);
            $TotalReceivedBags = $dataReceived['TotalReceivedBags'];

            $ArrayChemPoBrkUpList['SNO'] = ++$i;
            $ArrayChemPoBrkUpList['ChemPoBreakUpID'] = $dataRow['ChemPoBreakUpID'];
            $ArrayChemPoBrkUpList['ChemPoID'] = $dataRow['ChemPoID'];
            $ArrayChemPoBrkUpList['ChemicalID'] = $dataRow['ChemicalID'];
            $ArrayChemPoBrkUpList['ChemicalName'] = $dataRow['ChemicalName'];
            $ArrayChemPoBrkUpList['QuantityInKgs'] = $dataRow['QuantityInKgs'];
            $ArrayChemPoBrkUpList['QuantityInBags'] = $dataRow['QuantityInBags'];
            $ArrayChemPoBrkUpList['RatePerKgInRupees'] = $dataRow['RatePerKgInRupees'];
            $ArrayChemPoBrkUpList['CurrencyType'] = $dataRow['CurrencyType'];
            $ArrayChemPoBrkUpList['CurrencyName'] = $dataRow['CurrencyName'];
            $ArrayChemPoBrkUpList['Amout'] = $dataRow['Amout'];
            if ($TotalReceivedBags > 0) {
                $DeleteRow = 'No';
            } else {
                $DeleteRow = 'Yes';
            }
            $ArrayChemPoBrkUpList['DeleteRow'] = $DeleteRow;

            $ArrayChemPoBrkUpData[] =  $ArrayChemPoBrkUpList;
        }

        $TotalData = $this->getPoBrkUpTotals($RequestBody);
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['EditLink'] = "GsChemPoBrkUpCreate.php?ChemPoBreakUpID=";
        $data['Data'] = $ArrayChemPoBrkUpData;
        $data['Total'] = $TotalData['Data'];

        return $data;
    }

    function getPoBrkUpTotals($RequestBody)
    {

        $ChemPoID = $RequestBody['ChemPoID'];

        $sqlTotal = "SELECT sum(QuantityInKgs) AS TotalQty, sum(QuantityInKgs*RatePerKgInRupees) AS TotalAmount FROM GsChemicalPoBreakUp WHERE ChemPoID='$ChemPoID'";

        $stmtTotal = $this->conn->prepare($sqlTotal);
        $stmtTotal->execute();

        $ArrayTotal = [];

        while ($dataRow = $stmtTotal->fetch(PDO::FETCH_ASSOC)) {
            $ArrayTotalData['TotalQty'] = $dataRow['TotalQty'];
            $ArrayTotalData['TotalAmount'] = $dataRow['TotalAmount'];

            $ArrayTotal[] = $ArrayTotalData;
        }

        $data['Data'] = $ArrayTotal;

        return $data;
    }
}
