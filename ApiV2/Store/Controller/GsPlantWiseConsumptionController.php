<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class GsPlantWiseConsumptionController
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

    public function postChemConsumptionDetails($RequestBody)
    {
        $EntryID = $RequestBody['EntryID'];

        if ($EntryID == '') {
            $Data = $this->insertChemConsumptionDetails($RequestBody);
        } else {
            $Data = $this->updateChemConsumptionDetails($RequestBody);
        }

        return $Data;
    }

    public function insertChemConsumptionDetails($RequestBody)
    {

        $ChemicalID = $RequestBody['ChemicalID'];
        $PlantID = $RequestBody['PlantID'];
        $SendToPlantID = $RequestBody['SendToPlantID'];
        $BagsUsed = $RequestBody['BagsUsed'];
        $QuantityUsed = $RequestBody['QuantityUsed'];
        $EntryDate = $RequestBody['EntryDate'];
        $UsageType = $RequestBody['UsageType'];
        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");

        $QtyUsed = $this->getQtyUsedTotals($RequestBody);
        $PoQty = $this->getPoQtyTotals($RequestBody);

        $TotalQtyUsed = $QtyUsed['Data'][0]['TotalQtyUsed'];
        $TotalPoQty = $PoQty['Data'][0]['TotalPoQty'];

        if ($TotalQtyUsed + $BagsUsed <= $TotalPoQty) {

            $sqlInsert = "INSERT INTO GsPlantWiseConsumptionDetails(ChemicalID, PlantID,SendToPlantID, BagsUsed,QuantityUsed, 
            EntryDate,UsageType, PostedDateNTime) 
            VALUES ('$ChemicalID','$PlantID','$SendToPlantID','$BagsUsed','$QuantityUsed','$EntryDate', '$UsageType', '$PostedDate')";
            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();
            // $errorInfo = $stmtInsert->errorInfo();
            // var_dump($errorInfo);

            if ($stmtInsert) {
                $lastInsertedId = $this->conn->lastInsertId();
                $RequestBody['EntryID'] = $lastInsertedId;


                if ($UsageType == 2) {
                    $sqlInsertTransfer = "INSERT INTO GsChemReceivedQtys(QuantityReceived,BillDate,PostedDateNTime,
                    ProcessingPlantID,ChemicalID,ReceivedBags,ReceivingPlantID,ReceivingStatus,ConsumptionID) 
                    VALUES ('$QuantityUsed','$EntryDate','$PostedDate','$SendToPlantID','$ChemicalID','$BagsUsed','$PlantID',1,'$lastInsertedId')";
                    $stmtInsertTransfer = $this->conn->prepare($sqlInsertTransfer);
                    $stmtInsertTransfer->execute();
                    $transferId = $this->conn->lastInsertId();
                    $transferRow = $this->auditLogger->fetchRowById('GsChemReceivedQtys', 'ReceivedID', $transferId);
                    $this->auditLogger->logInsert('GsChemReceivedQtys', $transferId, $transferRow, $RequestBody, [
                        'ReferenceNo' => $lastInsertedId,
                        'Remarks' => 'Store chemical transfer receipt created from consumption',
                    ]);
                }
                $createdConsumption = $this->auditLogger->fetchRowById('GsPlantWiseConsumptionDetails', 'EntryID', $lastInsertedId);
                $this->auditLogger->logInsert('GsPlantWiseConsumptionDetails', $lastInsertedId, $createdConsumption, $RequestBody, [
                    'ReferenceNo' => $ChemicalID,
                    'Remarks' => 'Store plant-wise consumption created',
                ]);

                $chemConsumptionData = $this->getPlantConsumptionDetails($RequestBody);
                $TotalData = $this->getQtyUsedTotals($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'Consumption Posted Successfully';
                $Data['Data'] = $chemConsumptionData['Data'];
                $Data['Total'] = $TotalData['Data'];

                return $Data;
            } else {
                $Data['Error'] = 'Unable to Insert Consumption';
                $Data['Msg'] = $this->Error;
                return $Data;
            }
        } else {
            // $Data['Error'] = '';
            $Data['Error'] = 'Consumption Bags (' . ($TotalQtyUsed + $BagsUsed) . ') Exceeds PO Total Bags (' . ($TotalPoQty) . ')';
            $Data['Msg'] = 'Consumption Bags (' . ($TotalQtyUsed + $BagsUsed) . ') Exceeds PO Total Bags (' . ($TotalPoQty) . ')';
            return $Data;
        }
    }

    public function updateChemConsumptionDetails($RequestBody)
    {

        $EntryID = $RequestBody['EntryID'];
        $ChemicalID = $RequestBody['ChemicalID'];
        $PlantID = $RequestBody['PlantID'];
        $SendToPlantID = $RequestBody['SendToPlantID'];
        $BagsUsed = $RequestBody['BagsUsed'];
        $QuantityUsed = $RequestBody['QuantityUsed'];
        $EntryDate = $RequestBody['EntryDate'];
        $UsageType = $RequestBody['UsageType'];
        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");
        $existingConsumption = $this->auditLogger->fetchRowById('GsPlantWiseConsumptionDetails', 'EntryID', $EntryID);


        $QtyUsed = $this->getQtyUsedTotals($RequestBody);
        $PoQty = $this->getPoQtyTotals($RequestBody);

        $TotalQtyUsed = $QtyUsed['Data'][0]['TotalQtyUsed'];
        $TotalPoQty = $PoQty['Data'][0]['TotalPoQty'];

        if ($TotalQtyUsed + $QuantityUsed <= $TotalPoQty) {

            $sqlUpdate = "UPDATE GsPlantWiseConsumptionDetails SET ChemicalID='$ChemicalID',PlantID='$PlantID',
            SendToPlantID='$SendToPlantID',BagsUsed='$BagsUsed',QuantityUsed='$QuantityUsed',EntryDate='$EntryDate',
            UsageType='$UsageType',PostedDateNTime='$PostedDate' 
            WHERE EntryID='$EntryID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            if ($stmtUpdate) {
                $updatedConsumption = $this->auditLogger->fetchRowById('GsPlantWiseConsumptionDetails', 'EntryID', $EntryID);
                $this->auditLogger->logUpdate('GsPlantWiseConsumptionDetails', $EntryID, $existingConsumption, $updatedConsumption, $RequestBody, [
                    'ReferenceNo' => $ChemicalID,
                    'Remarks' => 'Store plant-wise consumption updated',
                ]);
                $chemConsumptionData = $this->getPlantConsumptionDetails($RequestBody);
                $TotalData = $this->getQtyUsedTotals($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'Consumption Updated Successfully';
                $Data['Data'] = $chemConsumptionData['Data'];
                $Data['Total'] = $TotalData['Data'];

                return $Data;
            } else {
                $Data['Error'] = 'Unable to Updated Consumption';
                $Data['Msg'] = $this->Error;
                return $Data;
            }
        } else {
            $Data['Error'] = 'Total  weight exceeds the total quantity';
            $Data['Msg'] = 'Consumption Bags (' . ($TotalQtyUsed + $QuantityUsed) . ') Exceeds PO Total Bags (' . ($TotalPoQty) . ')';
            return $Data;
        }
    }

    function deleteChemConsumptionDetails($RequestBody)
    {
        $EntryID = $RequestBody['EntryID'];
        $existingConsumption = $this->auditLogger->fetchRowById('GsPlantWiseConsumptionDetails', 'EntryID', $EntryID);
        $transferRows = $this->auditLogger->fetchRowsByConditions('GsChemReceivedQtys', [
            'ConsumptionID' => $EntryID,
        ]);

        $sqlDelete = "DELETE FROM `GsPlantWiseConsumptionDetails` WHERE EntryID='$EntryID'";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();

        if ($stmtDelete) {
            $sqlDeleteReceive = "DELETE FROM GsChemReceivedQtys WHERE ConsumptionID = '$EntryID'";
            $stmtDeleteReceive = $this->conn->prepare($sqlDeleteReceive);
            $stmtDeleteReceive->execute();
            foreach ($transferRows as $transferRow) {
                $receivedId = isset($transferRow['ReceivedID']) ? $transferRow['ReceivedID'] : null;
                $this->auditLogger->logDelete('GsChemReceivedQtys', $receivedId, $transferRow, $RequestBody, [
                    'ReferenceNo' => $EntryID,
                    'Remarks' => 'Store chemical transfer receipt deleted with consumption',
                ]);
            }
            $this->auditLogger->logDelete('GsPlantWiseConsumptionDetails', $EntryID, $existingConsumption, $RequestBody, [
                'ReferenceNo' => isset($existingConsumption['ChemicalID']) ? $existingConsumption['ChemicalID'] : $EntryID,
                'Remarks' => 'Store plant-wise consumption deleted',
            ]);
            
            $chemConsumptionData = $this->getPlantConsumptionDetails($RequestBody);
            $TotalData = $this->getQtyUsedTotals($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Consumption Deleted Successfully';
            $Data['Data'] = $chemConsumptionData['Data'];
            $Data['Total'] = $TotalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Delete Consumption';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function getPlantConsumptionDetails($RequestBody)
    {
        $ChemicalID = $RequestBody['ChemicalID'];
        $PlantID = $RequestBody['PlantID'];

        $Query = '';
        if ($PlantID != '') {
            $Query .= " AND PlantID = '$PlantID'";
        }
        $sqlFields = "ChemicalID, PlantID,SendToPlantID, BagsUsed,QuantityUsed, EntryID, EntryDate, 
        RPS.StationName AS ProductionPlant, RPS2.StationName AS TransferPlant,ChemicalName, ChemID, NoOfKgPerBag";

        $sqlQuery = "SELECT $sqlFields FROM GsPlantWiseConsumptionDetails AS GPCD
        LEFT JOIN GsChemicalMaster AS GCM ON GPCD.ChemicalID = GCM.ChemID
        LEFT JOIN RmProcessingStations AS RPS ON GPCD.PlantID = RPS.ProcessingStationID
        LEFT JOIN RmProcessingStations AS RPS2 ON GPCD.SendToPlantID = RPS2.ProcessingStationID
        WHERE GPCD.ChemicalID='$ChemicalID' $Query";

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();
        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayConsumptionData = [];
        $TotalUsedBags = 0;
        $TotalUsedKGs = 0;
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayConsumptionList['SNO'] = ++$i;
            $ArrayConsumptionList['EntryID'] = $dataRow['EntryID'];
            $ArrayConsumptionList['ChemicalID'] = $dataRow['ChemicalID'];
            $ArrayConsumptionList['ChemicalName'] = $dataRow['ChemicalName'];
            $ArrayConsumptionList['PlantID'] = $dataRow['PlantID'];
            $ArrayConsumptionList['ProductionPlant'] = $dataRow['ProductionPlant'];
            $ArrayConsumptionList['SendToPlantID'] = $dataRow['SendToPlantID'];
            $ArrayConsumptionList['TransferPlant'] = $dataRow['TransferPlant'];
            $ArrayConsumptionList['EntryDate'] = $dataRow['EntryDate'];
            $ArrayConsumptionList['ChemID'] = $dataRow['ChemID'];
            $ArrayConsumptionList['BagsUsed'] = $dataRow['BagsUsed'];
            $ArrayConsumptionList['QuantityUsed'] = $dataRow['QuantityUsed'];
            $ArrayConsumptionList['NoOfKgPerBag'] = $dataRow['NoOfKgPerBag'];

            $TotalUsedBags += $dataRow['BagsUsed'];
            $TotalUsedKGs += $dataRow['QuantityUsed'];
            $ArrayConsumptionData[] =  $ArrayConsumptionList;
        }

        $TotalData = $this->getQtyUsedTotals($RequestBody);
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['EditLink'] = "GsChemConsumptionCreate.php?ChemPoID=";
        $data['Data'] = $ArrayConsumptionData;
        $data['Total'] = [
            'TotalUsedBags' => $TotalUsedBags,
            'TotalUsedKGs' => $TotalUsedKGs,
        ];

        return $data;
    }

    function getQtyUsedTotals($RequestBody)
    {

        $ChemicalID = $RequestBody['ChemicalID'];
        $PlantID = $RequestBody['PlantID'];

        $sqlTotal = "SELECT SUM(BagsUsed) AS TotalQtyUsed FROM GsPlantWiseConsumptionDetails 
        WHERE ChemicalID='$ChemicalID' AND PlantID = '$PlantID'";

        $stmtTotal = $this->conn->prepare($sqlTotal);
        $stmtTotal->execute();

        $ArrayTotal = [];

        while ($dataRow = $stmtTotal->fetch(PDO::FETCH_ASSOC)) {
            $ArrayTotalData['TotalQtyUsed'] = $dataRow['TotalQtyUsed'];

            $ArrayTotal[] = $ArrayTotalData;
        }

        $data['Data'] = $ArrayTotal;

        return $data;
    }
    function getPoQtyTotals($RequestBody)
    {

        $ChemicalID = $RequestBody['ChemicalID'];
        $PlantID = $RequestBody['PlantID'];

        $sqlTotal = "SELECT SUM(ReceivedBags) AS TotalPoQty FROM GsChemReceivedQtys 
        WHERE ChemicalID='$ChemicalID' AND ProcessingPlantID = '$PlantID'";

        $stmtTotal = $this->conn->prepare($sqlTotal);
        $stmtTotal->execute();

        $ArrayTotal = [];

        while ($dataRow = $stmtTotal->fetch(PDO::FETCH_ASSOC)) {
            $ArrayTotalData['TotalPoQty'] = $dataRow['TotalPoQty'];

            $ArrayTotal[] = $ArrayTotalData;
        }

        $data['Data'] = $ArrayTotal;

        return $data;
    }
}
