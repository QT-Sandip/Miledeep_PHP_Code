<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';

class GsChemConsumptiontListController
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    function getChemConsumptionDetails($RequestBody)
    {
        $ChemicalID = $RequestBody['ChemicalID'];
        $PlantID = $RequestBody['PlantID'];
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];

        $EntryDates = $this->getDates($FromDate, $ToDate);
        $ArrayConsumptionData = [];
        $i = 0;
        // while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
        foreach ($EntryDates as $EntryDate) {

            $EntryDate = date('Y-m-d', strtotime($EntryDate));
            $sqlChemicalName = "SELECT ChemicalName FROM GsChemicalMaster WHERE ChemID = '$ChemicalID'";
            $stmtChemicalName = $this->conn->prepare($sqlChemicalName);
            $stmtChemicalName->execute();
            $dataChemical = $stmtChemicalName->fetch(PDO::FETCH_ASSOC);

            $ArrayConsumptionList['EntryDate'] = $EntryDate;
            $ArrayConsumptionList['ChemicalName'] = $dataChemical['ChemicalName'];

            $ReceivingData = $this->getReceivingDetails($ChemicalID, $EntryDate, $PlantID);
            $ArrayConsumptionList['QuantityReceived'] = $ReceivingData['QuantityReceived'];
            $ArrayConsumptionList['ReceivedBags'] = $ReceivingData['ReceivedBags'];
            $ArrayConsumptionList['StationName'] = $ReceivingData['StationName'];

            $ConsumptionDetails = $this->getConsumptionDetails($ChemicalID, $EntryDate, $PlantID);
            $ArrayConsumptionList['BagsUsed'] = $ConsumptionDetails['BagsUsed'] ? $ConsumptionDetails['BagsUsed'] : '';
            $ArrayConsumptionList['QuantityUsed'] = $ConsumptionDetails['QuantityUsed'] ? $ConsumptionDetails['QuantityUsed'] : '';

            $TransferDetails = $this->getTransferDetails($ChemicalID, $EntryDate, $PlantID);
            $ArrayConsumptionList['SendBags'] = $TransferDetails['BagsUsed'] ? $TransferDetails['BagsUsed'] : '';
            $ArrayConsumptionList['SendQty'] = $TransferDetails['QuantityUsed'] ? $TransferDetails['QuantityUsed'] : '';
            $ArrayConsumptionList['TransferPlant'] = $TransferDetails['TransferPlant'];

            $OpeningDetails = $this->getOpeningDetails($ChemicalID, $EntryDate, $PlantID);
            $OpeningBags = $OpeningDetails['OpeningBags'];
            $ArrayConsumptionList['OpeningBags'] = $OpeningDetails['OpeningBags'];
            $ArrayConsumptionList['OpeningKGs'] = $OpeningDetails['OpeningKGs'];

            $ClosingDetails = $this->getClosingDetails($ChemicalID, $EntryDate, $PlantID);
            $ClosingBags = $ClosingDetails['ClosingBags'];
            $ArrayConsumptionList['ClosingBags'] = $ClosingDetails['ClosingBags'];
            $ArrayConsumptionList['ClosingKGs'] = $ClosingDetails['ClosingKGs'];

            $ArrayConsumptionList['SNO'] = ++$i;
            $ArrayConsumptionData[] =  $ArrayConsumptionList;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['Data'] = $ArrayConsumptionData;

        return $data;
    }

    function getReceivingDetails($ChemicalID, $EntryDate, $PlantID)
    {
        $Query = '';
        if ($PlantID != '') {
            $Query .= " AND ProcessingPlantID = '$PlantID'";
        }
        $sqlFields = "SUM(QuantityReceived) AS QuantityReceived,SUM(ReceivedBags) AS ReceivedBags, StationName";
        $sqlSelect = "SELECT $sqlFields FROM GsChemReceivedQtys AS GCRQ
        LEFT JOIN RmProcessingStations AS RPS ON GCRQ.ReceivingPlantID = RPS.ProcessingStationID
        WHERE ChemicalID = '$ChemicalID' AND BillDate = '$EntryDate' $Query";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);
        $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        $Data['QuantityReceived'] = $dataRow['QuantityReceived'];
        $Data['ReceivedBags'] = $dataRow['ReceivedBags'];
        $Data['StationName'] = $dataRow['StationName'];

        return $Data;
    }

    function getConsumptionDetails($ChemicalID, $EntryDate, $PlantID)
    {
        $Query = '';
        if ($PlantID != '') {
            $Query .= " AND PlantID = '$PlantID'";
        }
        $sqlFields = "SUM(BagsUsed) AS BagsUsed,SUM(QuantityUsed) AS QuantityUsed";
        $sqlSelect = "SELECT $sqlFields FROM GsPlantWiseConsumptionDetails 
        WHERE ChemicalID = '$ChemicalID' AND EntryDate = '$EntryDate' AND UsageType = 1 $Query";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        $Data['BagsUsed'] = $dataRow['BagsUsed'] ? $dataRow['BagsUsed'] : 0;
        $Data['QuantityUsed'] = $dataRow['QuantityUsed'] ? $dataRow['QuantityUsed'] : 0;

        return $Data;
    }
    function getTransferDetails($ChemicalID, $EntryDate, $PlantID)
    {

        $Query = '';
        if ($PlantID != '') {
            $Query .= " AND PlantID = '$PlantID'";
        }
        $sqlFields = "SUM(BagsUsed) AS BagsUsed,SUM(QuantityUsed) AS QuantityUsed, GROUP_CONCAT(DISTINCT RPS.StationName) AS TransferPlant";
        $sqlSelect = "SELECT $sqlFields FROM GsPlantWiseConsumptionDetails AS GPWC
        LEFT JOIN RmProcessingStations AS RPS ON GPWC.SendToPlantID = RPS.ProcessingStationID
        WHERE ChemicalID = '$ChemicalID' AND EntryDate = '$EntryDate'  AND UsageType = 2 $Query";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        $Data['BagsUsed'] = $dataRow['BagsUsed'];
        $Data['QuantityUsed'] = $dataRow['QuantityUsed'];
        $Data['TransferPlant'] = $dataRow['TransferPlant'];

        return $Data;
    }

    function getClosingDetails($ChemicalID, $EntryDate, $PlantID)
    {
        $Query = '';
        $QueryTwo = '';
        if ($PlantID != '') {
            $Query .= " AND PlantID = '$PlantID'";
            $QueryTwo .= " AND ProcessingPlantID = '$PlantID'";
        }

        $sqlUsedFields = "SUM(BagsUsed) AS BagsUsed,SUM(QuantityUsed) AS QuantityUsed";
        $sqlUsedSelect = "SELECT $sqlUsedFields FROM GsPlantWiseConsumptionDetails AS GPCD
        WHERE GPCD.ChemicalID = '$ChemicalID' AND EntryDate <= '$EntryDate' $Query";
        $stmtUsedSelect = $this->conn->prepare($sqlUsedSelect);
        $stmtUsedSelect->execute();
        $dataUsedRow = $stmtUsedSelect->fetch(PDO::FETCH_ASSOC);
        $BagsUsed = $dataUsedRow['BagsUsed'];
        $QuantityUsed = $dataUsedRow['QuantityUsed'];

        $sqlReceivedFields = "SUM(QuantityReceived) AS QuantityReceived,SUM(ReceivedBags) AS ReceivedBags";
        $sqlReceivedSelect = "SELECT $sqlReceivedFields FROM GsChemReceivedQtys 
        WHERE ChemicalID = '$ChemicalID' AND BillDate <= '$EntryDate' $QueryTwo";
        $stmtReceivedSelect = $this->conn->prepare($sqlReceivedSelect);
        $stmtReceivedSelect->execute();
        $dataReceivedRow = $stmtReceivedSelect->fetch(PDO::FETCH_ASSOC);
        $QuantityReceived = $dataReceivedRow['QuantityReceived'];
        $ReceivedBags = $dataReceivedRow['ReceivedBags'];

        // echo $BagsUsed . '/';
        $Data['ClosingBags'] = $ReceivedBags - $BagsUsed;
        $Data['ClosingKGs'] = $QuantityReceived - $QuantityUsed;

        return $Data;
    }
    function getOpeningDetails($ChemicalID, $EntryDate, $PlantID)
    {
        $Query = '';
        $QueryTwo = '';
        if ($PlantID != '') {
            $Query .= " AND PlantID = '$PlantID'";
            $QueryTwo .= " AND ProcessingPlantID = '$PlantID'";
        }

        $sqlUsedFields = "SUM(BagsUsed) AS BagsUsed,SUM(QuantityUsed) AS QuantityUsed";
        $sqlUsedSelect = "SELECT $sqlUsedFields FROM GsPlantWiseConsumptionDetails AS GPCD
        WHERE GPCD.ChemicalID = '$ChemicalID' AND EntryDate < '$EntryDate' $Query";
        $stmtUsedSelect = $this->conn->prepare($sqlUsedSelect);
        $stmtUsedSelect->execute();
        $dataUsedRow = $stmtUsedSelect->fetch(PDO::FETCH_ASSOC);
        $BagsUsed = $dataUsedRow['BagsUsed'];
        $QuantityUsed = $dataUsedRow['QuantityUsed'];

        $sqlReceivedFields = "SUM(QuantityReceived) AS QuantityReceived,SUM(ReceivedBags) AS ReceivedBags";
        $sqlReceivedSelect = "SELECT $sqlReceivedFields FROM GsChemReceivedQtys 
        WHERE ChemicalID = '$ChemicalID' AND BillDate < '$EntryDate' $QueryTwo";
        $stmtReceivedSelect = $this->conn->prepare($sqlReceivedSelect);
        $stmtReceivedSelect->execute();
        $dataReceivedRow = $stmtReceivedSelect->fetch(PDO::FETCH_ASSOC);
        $QuantityReceived = $dataReceivedRow['QuantityReceived'];
        $ReceivedBags = $dataReceivedRow['ReceivedBags'];

        // echo $BagsUsed . '/';
        $Data['OpeningBags'] = $ReceivedBags - $BagsUsed;
        $Data['OpeningKGs'] = $QuantityReceived - $QuantityUsed;

        return $Data;
    }

    public function getDates($FromDate, $ToDate)
    {
        $startDate = new DateTime($FromDate);
        $endDate = new DateTime($ToDate);

        $dateArray = [];

        while ($startDate <= $endDate) {
            $dateArray[] = $startDate->format('d-m-Y');
            $startDate->modify('+1 day');
        }

        return $dateArray;
    }
}
