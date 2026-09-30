<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class GsChemOrdersListController
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

    function getChemReportDetails($RequestBody)
    { 
        $RecordsPerPage = 10;
        $ChemPoID = $RequestBody['ChemPoID'];
        $ProcessingPlantID = $RequestBody['ProcessingPlantID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($ProcessingPlantID != "") {
            $Query .= " and GCRQ.ProcessingPlantID = '$ProcessingPlantID'";
        }

        $RecordsPerPage = 10;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', ReceivedID,ContainerNo,BLNo,ChemPoNo,QuantityReceived, BillOfEntryNo,StationName) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(DISTINCT ChemPoID) as Total  FROM `GsChemReceivedQtys`";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "GCRQ.ChemPoID, GCPs.ChemPoNo, 
        MAX(GCRQ.BillDate) AS BlDate, MAX(GCRQ.ClearedDate) AS ClrDate,
        GROUP_CONCAT(DISTINCT GCRQ.ReceivedID SEPARATOR ', ') AS ReceivedIDs,
        GROUP_CONCAT(DISTINCT GCRQ.BLNo SEPARATOR ', ') AS BLNOs,
        GROUP_CONCAT(DISTINCT GCRQ.ContainerNo SEPARATOR ', ') AS ContainerNos,
        (SELECT SUM(QuantityReceived) FROM GsChemReceivedQtys WHERE ChemPoID = GCRQ.ChemPoID ) AS TotalQtyRcvd,
        GROUP_CONCAT(DISTINCT GCRQ.BillOfEntryNo SEPARATOR ', ') AS BillOfEntryNos,
        GROUP_CONCAT(DISTINCT GCRQ.ProcessingPlantID SEPARATOR ', ') AS ProcessingPlantIDs,
        GROUP_CONCAT(DISTINCT RPS.StationName SEPARATOR ', ') AS StationNames,
        CASE WHEN ( SELECT SUM(QuantityInKgs) FROM GsChemicalPoBreakUp WHERE ChemPoID = GCRQ.ChemPoID
        ) = ( SELECT SUM(QuantityReceived) FROM GsChemReceivedQtys WHERE ChemPoID = GCRQ.ChemPoID) THEN 1 ELSE 0 END AS QtyStatus";

        if ($ChemPoID != '') {

            $sqlQuery = "SELECT $sqlFields FROM GsChemReceivedQtys AS GCRQ
            LEFT JOIN GsChemicalPos AS GCPs ON GCRQ.ChemPoID = GCPs.ChemPoID
            LEFT JOIN RmProcessingStations AS RPS ON GCRQ.ProcessingPlantID = RPS.ProcessingStationID
            LEFT JOIN GsChemicalPoBreakUp AS GCPB ON GCRQ.ChemPoID = GCPB.ChemPoID
            WHERE GCRQ.ChemPoID='$ChemPoID'
            GROUP BY GCRQ.ChemPoID, GCPs.ChemPoNo";
        } else {
            // echo
            $sqlQuery = "SELECT $sqlFields FROM GsChemReceivedQtys AS GCRQ
            LEFT JOIN GsChemicalPos AS GCPs ON GCRQ.ChemPoID = GCPs.ChemPoID
            LEFT JOIN RmProcessingStations AS RPS ON GCRQ.ProcessingPlantID = RPS.ProcessingStationID
            LEFT JOIN GsChemicalPoBreakUp AS GCPB ON GCRQ.ChemPoID = GCPB.ChemPoID
            WHERE 1=1 $Query
            GROUP BY GCRQ.ChemPoID, GCPs.ChemPoNo
            ORDER BY ChemPoID " . $Limit;
        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();
        

        $ArrayChemOrdersData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayChemOrderList['SNO'] = ++$i;
            $ArrayChemOrderList['ChemPoID'] = $dataRow['ChemPoID'];
            $ArrayChemOrderList['ReceivedID'] = $dataRow['ReceivedIDs'];
            $ArrayChemOrderList['ChemPoNo'] = $dataRow['ChemPoNo'];
            $ArrayChemOrderList['BLNo'] = $dataRow['BLNOs'];
            $ArrayChemOrderList['ContainerNo'] = $dataRow['ContainerNos'];
            $ArrayChemOrderList['QuantityReceived'] = $dataRow['TotalQtyRcvd'];
            $ArrayChemOrderList['BillOfEntryNo'] = $dataRow['BillOfEntryNos'];
            $ArrayChemOrderList['BillDate'] = $dataRow['BlDate'];
            $ArrayChemOrderList['ClearedDate'] = $dataRow['ClrDate'];
            $ArrayChemOrderList['ProcessingPlantID'] = $dataRow['ProcessingPlantIDs'];
            $ArrayChemOrderList['StationName'] = $dataRow['StationNames'];
            $ArrayChemOrderList['QtyStatus'] = $dataRow['QtyStatus'];


            $ArrayChemOrdersData[] =  $ArrayChemOrderList;
        }

        if (count($ArrayChemOrdersData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "GsChemOrdersCreate.php?ChemPoID=";
        $data['AddLink'] = "";
        $data['Data'] = $ArrayChemOrdersData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
