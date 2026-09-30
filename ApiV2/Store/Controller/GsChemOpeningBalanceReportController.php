<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class GsChemOpeningBalanceReportController
{
    private $conn;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    function getChemicalOpeningDetails($RequestBody)
    {
        $PlantID = $RequestBody['PlantID'];
        $ChemicalID = $RequestBody['ChemicalID'];
        $BillDate = $RequestBody['BillDate'];
        $FromDate = $RequestBody['fromDate'];
        $ToDate = $RequestBody['toDate'];

        $CommonQuery = '';
        $GroupBy = '';
        $InnerQuery = '';
        $monthRange = [];
        if ($PlantID != '') {
            $CommonQuery .= " AND GCRQ.ProcessingPlantID = '$PlantID'";
            $GroupBy .= " ,ProcessingPlantID";
            $InnerQuery .= " AND PlantID = '$PlantID'";
        }

        if ($ChemicalID != '') {
            $CommonQuery .= " AND ChemicalID = '$ChemicalID'";
        }

        $Query = $CommonQuery;
        if ($BillDate != '') {
            $Query .= " AND BillDate <= '$BillDate' ";
        } else if ($FromDate != '' && $ToDate != '') {
            $Query .= " AND BillDate BETWEEN '$FromDate' AND '$ToDate' ";

        }


        $sqlFields = "SUM(QuantityReceived) AS TotalQuantityReceived, SUM(ReceivedBags) AS TotalReceivedBags,
            ChemicalID, ProcessingPlantID,BillDate, ChemicalName, ChemID, NoOfKgPerBag, RPS.StationName";
        $sqlSelect = "SELECT $sqlFields FROM GsChemReceivedQtys AS GCRQ
            LEFT JOIN GsChemicalMaster AS GCM ON GCRQ.ChemicalID = GCM.ChemID
            LEFT JOIN RmProcessingStations AS RPS ON GCRQ.ProcessingPlantID = RPS.ProcessingStationID
            WHERE 1=1 $Query 
            GROUP BY ChemicalID ORDER BY ChemicalID";

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayChemOrdersData = [];

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ChemicalID = $dataRow['ChemicalID'];
            $ProcessingPlantID = $dataRow['ProcessingPlantID'];

            $BillQuery = "";
            if ($BillDate != '') {
                $BillQuery .= " AND EntryDate <= '$BillDate' ";
            } else if ($FromDate != '' && $ToDate != '') {
                $BillQuery .= " AND EntryDate BETWEEN '$FromDate' AND '$ToDate' ";
            }

            $sqlConsumedFeilds = "SUM(BagsUsed) AS TotalBagsUsed, SUM(QuantityUsed) AS TotalQuantityUsed";
            $sqlConsumedData = "SELECT $sqlConsumedFeilds FROM GsPlantWiseConsumptionDetails
                WHERE ChemicalID = '$ChemicalID' $InnerQuery $BillQuery";
            $stmtConsumedData = $this->conn->prepare($sqlConsumedData);
            $stmtConsumedData->execute();
            $dataConsumedData = $stmtConsumedData->fetch(PDO::FETCH_ASSOC);
            $TotalBagsUsed = $dataConsumedData['TotalBagsUsed'];
            $TotalQuantityUsed = $dataConsumedData['TotalQuantityUsed'];

            $ArrayChemOrderList['SNO'] = ++$i;
            $ArrayChemOrderList['ChemicalID'] = $dataRow['ChemicalID'];
            $ArrayChemOrderList['ChemicalName'] = $dataRow['ChemicalName'];
            $ArrayChemOrderList['ProcessingPlantID'] = $dataRow['ProcessingPlantID'];
            // $ArrayChemOrderList['StationName'] = $dataRow['StationName'];
            $ArrayChemOrderList['NoOfKgPerBag'] = $dataRow['NoOfKgPerBag'];

            $TotalQuantityReceived = $dataRow['TotalQuantityReceived'];
            $TotalReceivedBags = $dataRow['TotalReceivedBags'];

            $RemainingBags = $TotalReceivedBags - $TotalBagsUsed;
            $RemainingKGs = $TotalQuantityReceived - $TotalQuantityUsed;

            $ArrayChemOrderList['TotalQuantityReceived'] = $TotalQuantityReceived;
            $ArrayChemOrderList['TotalReceivedBags'] = $TotalReceivedBags;
            $ArrayChemOrderList['TotalBagsUsed'] = $TotalBagsUsed;
            $ArrayChemOrderList['TotalQuantityUsed'] = $TotalQuantityUsed;

            $ArrayChemOrderList['RemainingBags'] = $RemainingBags;
            $ArrayChemOrderList['RemainingKGs'] = $RemainingKGs;

            $ArrayChemOrdersData[] = $ArrayChemOrderList;
        }


        $data['Error'] = null;
        $data['Msg'] = "Success";
        $data['Data'] = $ArrayChemOrdersData;

        return $data;
    }
}
