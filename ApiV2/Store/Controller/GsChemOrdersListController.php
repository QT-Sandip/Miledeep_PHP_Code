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

    function getChemOrdersDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $ChemPoID = $RequestBody['ChemPoID'];
        $ProcessingPlantID = $RequestBody['ProcessingPlantID'];
        $Year = $RequestBody['BillDate'];
        $Status = $RequestBody['Status'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';
        $InnerQuery = '';
        $GroupBy = '';

        if ($ChemPoID != '') {
            $Query .= " AND GCRQ.ChemPoID = '$ChemPoID'";
        }
        if ($ProcessingPlantID != '') {
            $Query .= " AND GCRQ.ProcessingPlantID = '$ProcessingPlantID'";
            $InnerQuery .= " AND ProcessingPlantID = '$ProcessingPlantID'";
            $GroupBy .= " ,GCRQ.ProcessingPlantID";
        }

        $StartDate = $Year . '-04-01';
        $EndDate = ($Year + 1) . '-03-31';
        $DateFilter = "BETWEEN '$StartDate' AND '$EndDate'";

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('', ReceivedID,ContainerNo,BLNo,ChemPoNo,QuantityReceived, BillOfEntryNo,StationName) LIKE '%$SearchKey%'";
        }

        /* $sqlVarietyCount = "SELECT COUNT(DISTINCT GCRQ.ChemPoID) as Total  FROM GsChemReceivedQtys AS GCRQ
        LEFT JOIN GsChemicalPos AS GCPs ON GCRQ.ChemPoID = GCPs.ChemPoID
        LEFT JOIN RmProcessingStations AS RPS ON GCRQ.ProcessingPlantID = RPS.ProcessingStationID
        LEFT JOIN GsChemicalPoBreakUp AS GCPB ON GCRQ.ChemPoID = GCPB.ChemPoID
        WHERE BillDate $DateFilter $Query"; */
        $TotalCount = 0;
        if ($LoadAll == 0) {
            $sqlVarietyCount = "SELECT COUNT(Total) AS Total FROM (
                                        SELECT DISTINCT GCRQ.ChemPoID as Total,ReceivingStatus,
                                        CASE WHEN 
                                        ( 
                                            SELECT SUM(QuantityInBags) FROM GsChemicalPoBreakUp WHERE ChemPoID = GCRQ.ChemPoID
                                        ) = 
                                        ( 
                                            SELECT SUM(ReceivedBags) FROM GsChemReceivedQtys WHERE ChemPoID = GCRQ.ChemPoID
                                        ) THEN 1 ELSE 0 END AS QtyStatus   
                                        FROM GsChemReceivedQtys AS GCRQ
                                        LEFT JOIN GsChemicalPos AS GCPs ON GCRQ.ChemPoID = GCPs.ChemPoID
                                        LEFT JOIN RmProcessingStations AS RPS ON GCRQ.ProcessingPlantID = RPS.ProcessingStationID
                                        LEFT JOIN GsChemicalPoBreakUp AS GCPB ON GCRQ.ChemPoID = GCPB.ChemPoID
                                        WHERE BillDate $DateFilter $Query
                                ) A ";
            if(isset($Status)){
                if($Status == 2){
                    $sqlVarietyCount .= " WHERE ReceivingStatus=1";
                }elseif($Status == 0 || $Status == 1){
                    $sqlVarietyCount .= " WHERE QtyStatus=$Status AND ReceivingStatus=0";
                }                
            }
            $stmt = $this->conn->prepare($sqlVarietyCount);
            $stmt->execute();
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $TotalCount = $dataRow['Total'];
        }
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "GCRQ.ChemPoID, GCPs.ChemPoNo, 
                        MAX(GCRQ.BillDate) AS BlDate, MAX(GCRQ.ClearedDate) AS ClrDate,
                        GROUP_CONCAT(DISTINCT GCRQ.ReceivedID SEPARATOR ', ') AS ReceivedIDs,
                        GROUP_CONCAT(DISTINCT GCRQ.BLNo SEPARATOR ', ') AS BLNOs,
                        GROUP_CONCAT(DISTINCT GCRQ.ContainerNo SEPARATOR ', ') AS ContainerNos,
                        SUM(ReceivedBags) AS TotalReceivedBags, SUM(QuantityReceived) AS TotalQtyReceived,
                        GROUP_CONCAT(DISTINCT GCRQ.BillOfEntryNo SEPARATOR ', ') AS BillOfEntryNos,
                        GROUP_CONCAT(DISTINCT GCRQ.ProcessingPlantID SEPARATOR ', ') AS ProcessingPlantIDs,
                        GROUP_CONCAT(DISTINCT RPS.StationName SEPARATOR ', ') AS StationNames,
                        GROUP_CONCAT(DISTINCT GCM.ChemicalName SEPARATOR ', ') AS ChemicalNames,
                        ReceivingStatus,
                        SUM(QuantityInBags) AS PoBags, (select SUM(ReceivedBags) FROM GsChemReceivedQtys WHERE ChemPoID = GCRQ.ChemPoID) AS ReceivedBags, 
                        (select SUM(QuantityReceived) FROM GsChemReceivedQtys WHERE ChemPoID = GCRQ.ChemPoID) AS QuantityReceived,
                        CASE WHEN ( SELECT SUM(QuantityInBags) FROM GsChemicalPoBreakUp WHERE ChemPoID = GCRQ.ChemPoID
                        ) = ( SELECT SUM(ReceivedBags) FROM GsChemReceivedQtys WHERE ChemPoID = GCRQ.ChemPoID) THEN 1 ELSE 0 END AS QtyStatus ";
        // SUM(ReceivedBags) AS ReceivedBags, SUM(QuantityReceived) AS QuantityReceived,

        /* $sqlQuery = "SELECT $sqlFields FROM GsChemReceivedQtys AS GCRQ
        LEFT JOIN GsChemicalPos AS GCPs ON GCRQ.ChemPoID = GCPs.ChemPoID
        LEFT JOIN RmProcessingStations AS RPS ON GCRQ.ProcessingPlantID = RPS.ProcessingStationID
        LEFT JOIN GsChemicalPoBreakUp AS GCPB ON GCRQ.ChemPoID = GCPB.ChemPoID
        LEFT JOIN GsChemicalMaster AS GCM ON GCM.ChemID = GCRQ.ChemicalID
        WHERE BillDate $DateFilter $Query
        GROUP BY GCRQ.ChemPoID,ReceivingStatus $GroupBy ORDER BY GCRQ.BillDate DESC " . $Limit; */
        // GROUP BY GCRQ.ChemPoID,ReceivingStatus ORDER BY ChemPoID " . $Limit;

        $sqlQuery = "SELECT * FROM (
                            SELECT $sqlFields FROM GsChemReceivedQtys AS GCRQ
                            LEFT JOIN GsChemicalPos AS GCPs ON GCRQ.ChemPoID = GCPs.ChemPoID
                            LEFT JOIN RmProcessingStations AS RPS ON GCRQ.ProcessingPlantID = RPS.ProcessingStationID
                            LEFT JOIN GsChemicalPoBreakUp AS GCPB ON GCRQ.ChemPoID = GCPB.ChemPoID
                            LEFT JOIN GsChemicalMaster AS GCM ON GCM.ChemID = GCRQ.ChemicalID
                            WHERE BillDate $DateFilter $Query
                            GROUP BY GCRQ.ChemPoID,ReceivingStatus $GroupBy ORDER BY GCRQ.BillDate DESC 
                        ) A ";
        if(isset($Status)){
            if($Status == 2){
                $sqlQuery .= " WHERE ReceivingStatus=1 ";
            }elseif($Status == 0 || $Status == 1){
                $sqlQuery .= " WHERE QtyStatus=$Status AND ReceivingStatus=0 ";
            }
        }
        $sqlQuery .= $Limit;
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayChemOrdersData = [];

        $i = $LimitMax;
        $RequiredBags = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ChemPoID = $dataRow['ChemPoID'];
            $sqlPoBags = "SELECT SUM(QuantityInBags) AS PoBags,SUM(QuantityInKgs) AS PoQty FROM GsChemicalPoBreakUp WHERE ChemPoID = '$ChemPoID'";
            $stmtPoBags = $this->conn->prepare($sqlPoBags);
            $stmtPoBags->execute();
            $dataPoBags = $stmtPoBags->fetch(PDO::FETCH_ASSOC);
            $PoBags = $dataPoBags['PoBags'];
            $PoQty = $dataPoBags['PoQty'];

            $sqlRecievedBags = "SELECT SUM(ReceivedBags) AS RecievedBags,SUM(QuantityReceived) AS RecievedQty FROM GsChemReceivedQtys WHERE ChemPoID = '$ChemPoID'";
            $stmtRecievedBags = $this->conn->prepare($sqlRecievedBags);
            $stmtRecievedBags->execute();
            $dataRecievedBags = $stmtRecievedBags->fetch(PDO::FETCH_ASSOC);
            $RecievedBags = $dataRecievedBags['RecievedBags'];
            $RecievedQty = $dataRecievedBags['RecievedQty'];

            $sqlTotalRecievedBags = "SELECT SUM(ReceivedBags) AS TotalRecievedBags, SUM(QuantityReceived) AS TotalQtyReceived 
            FROM GsChemReceivedQtys WHERE ChemPoID = '$ChemPoID' $InnerQuery ";
            $stmtTotalRecievedBags = $this->conn->prepare($sqlTotalRecievedBags);
            $stmtTotalRecievedBags->execute();
            $dataTotalRecievedBags = $stmtTotalRecievedBags->fetch(PDO::FETCH_ASSOC);
            $TotalRecievedBags = $dataTotalRecievedBags['TotalRecievedBags'];
            $TotalQtyReceived = $dataTotalRecievedBags['TotalQtyReceived'];

            $ArrayChemOrderList['SNO'] = ++$i;
            $ArrayChemOrderList['ChemPoID'] = $dataRow['ChemPoID'];
            $ArrayChemOrderList['ReceivedID'] = $dataRow['ReceivedIDs'];
            $ArrayChemOrderList['ChemPoNo'] = $dataRow['ChemPoNo'];
            $ArrayChemOrderList['ChemicalNames'] = $dataRow['ChemicalNames'];
            $ArrayChemOrderList['BLNo'] = $dataRow['BLNOs'];
            $ArrayChemOrderList['ContainerNo'] = $dataRow['ContainerNos'];
            $ArrayChemOrderList['ReceivedBags'] = $TotalRecievedBags;
            // $ArrayChemOrderList['ReceivedBags'] = $RecievedBags;
            $ArrayChemOrderList['QuantityReceived'] = $TotalQtyReceived;
            $ArrayChemOrderList['BillOfEntryNo'] = $dataRow['BillOfEntryNos'];
            $ArrayChemOrderList['BillDate'] = $dataRow['BlDate'];
            $ArrayChemOrderList['ClearedDate'] = $dataRow['ClrDate'];
            // $ArrayChemOrderList['ProcessingPlantID'] = $dataRow['ProcessingPlantIDs'];
            $ArrayChemOrderList['PoBags'] = $dataRow['PoBags'];
            $ReceivingStatus = $dataRow['ReceivingStatus'];
            $ArrayChemOrderList['ReceivingStatus'] = $ReceivingStatus;
            if ($ReceivingStatus == "0") {
                $RequiredBags = $PoBags - $RecievedBags;
                $BalanceQty = $PoQty - $RecievedQty;
            } else if ($ReceivingStatus == "1") {
                $RequiredBags = 0;
                $BalanceQty = 0;
            }
            $ArrayChemOrderList['RequiredBags'] = $RequiredBags;
            // $ArrayChemOrderList['StationName'] = $dataRow['StationNames'];
            if ($ReceivingStatus == 0) {
                $ArrayChemOrderList['QtyStatus'] = $dataRow['QtyStatus'];
                $ArrayChemOrderList['ClearedDate'] = $dataRow['ClrDate'];
            } else if ($ReceivingStatus == 1) {
                $ArrayChemOrderList['QtyStatus'] = 2;
                $ArrayChemOrderList['ClearedDate'] = null;
            }
            $ArrayChemOrderList['BalanceQty'] = $BalanceQty;

            $ArrayChemOrdersData[] = $ArrayChemOrderList;
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
