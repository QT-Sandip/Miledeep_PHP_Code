<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class TraceabilityUtilizedReportController1
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }

    function getTraceabilityReportData($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $FarmerName = $RequestBody['FarmerName'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($FarmerName != '') {
            $Query .= " AND FarmerName = '$FarmerName'";
        }

        $DateFilters = " and  HarvestDt BETWEEN '$FromDate' AND '$ToDate'";

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('',TraceID, Tracebility, Tonnage, FarmerName) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(*) AS TotalCount FROM QcTracebilityList AS QTL
        WHERE 1=1 $Query ";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataCount['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "PondArea,PondNumber,TraceID, Tracebility, Tonnage, FarmerName";
        $sqlSelect = "SELECT $sqlFields FROM QcTracebilityList AS QTL
        WHERE 1=1 $Query GROUP BY Tracebility " . $Limit;
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ArrayData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $UsedQtyData = $this->getUsedQty($dataRow['TraceID'], $DateFilters);
            $InvData = $this->getInvoiceData($dataRow['TraceID']);            
            $customsInvString = implode('<br>', $InvData['CustomsInvNo']);
            $ArrayList['SNO'] = ++$i;
            $ArrayList['TraceID'] = $dataRow['TraceID'];
            $ArrayList['InvData'] = $InvData;
            $ArrayList['Tracebility'] = $dataRow['Tracebility'];
            $ArrayList['FarmerName'] = $dataRow['FarmerName'];
            $ArrayList['Tonnage'] = $dataRow['Tonnage'];
            $ArrayList['PondArea'] = $dataRow['PondArea'];
            $ArrayList['PondNumber'] = $dataRow['PondNumber'];
            $ArrayList['InvoiceNo'] = implode('<br>', $InvData['CustomsInvNo']);
            $ArrayList['UsedDate'] = implode('<br>', $InvData['DayCode']);
            $ArrayList['DueDate'] = $dataRow['DueDate'];
            //$ArrayList['UtilizedQty'] = $UsedQtyData['TotalUsedQty'];
            $Tonnage = $dataRow['Tonnage'];
            $TotalUsedQty = $UsedQtyData['TotalUsedQty'];
            $BalanceQty = $Tonnage - $TotalUsedQty;
            $ArrayList['BalanceQty'] = $Tonnage - $TotalUsedQty;
            $ArrayList['BreakUpData'] = $UsedQtyData['BreakUpData'];

            // if ($BalanceQty > 0) {
            $ArrayData[] = $ArrayList;
            // }
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['TotalCount'] = $TotalCount;
        $Data['PageCount'] = $PageCount;
        $Data['TraceabilityData'] = $ArrayData;

        return $Data;
    }
    function getInvoiceData($TracebilityID){        
        $sqlInvData = "SELECT qc.InvID, qc.DayCode, sc.CustomsInvNo FROM QcCodeList AS qc LEFT JOIN ScrmCustomInvoices AS sc ON sc.PiID = qc.InvID WHERE qc.TracebilityID ='".$TracebilityID."'";
        $stmtInvData = $this->conn->prepare($sqlInvData);
        $stmtInvData->execute();
        $invData = [];
        while ($dataInvData = $stmtInvData->fetch(PDO::FETCH_ASSOC)) {
            $invData['CustomsInvNo'][]=$dataInvData['CustomsInvNo'] ;
            $invData['DayCode'][]=$dataInvData['DayCode'] ;
        }
        return $invData;
    }
    function getUsedQty($TracebilityID, $DateFilters)
    {
        $sqlUsedQtyFields = "SUM(UsedQuantityInCodeList) AS UsedQty, ProcessingStationID, StationName";
        $sqlUsedQty = "SELECT $sqlUsedQtyFields FROM QcCodeList AS QCL
        LEFT JOIN ScrmPoData AS SPD ON QCL.InvID = SPD.PoID
        LEFT JOIN AdminExporterDetails AS AED ON SPD.ProcessorID = AED.ExpID
        LEFT JOIN RmProcessingStations AS RPS ON AED.InvShortCode = RPS.ProdCode
        WHERE TracebilityID = '$TracebilityID' $DateFilters GROUP BY ProcessingStationID";
        
        $stmtUsedQty = $this->conn->prepare($sqlUsedQty);
        $stmtUsedQty->execute();
        $BreakUpData = [];
        $TotalUsedQty = 0;
        while ($dataUsedQty = $stmtUsedQty->fetch(PDO::FETCH_ASSOC)) {

            $UsedQty = $dataUsedQty['UsedQty'];
            $BreakUpList['UsedQty'] = $dataUsedQty['UsedQty'];
            $BreakUpList['StationName'] = $dataUsedQty['StationName'] ? $dataUsedQty['StationName'] : '-';

            $TotalUsedQty += $UsedQty;
            $BreakUpData[] = $BreakUpList;
        }
        $Data['BreakUpData'] = $BreakUpData;
        $Data['TotalUsedQty'] = $TotalUsedQty;

        return $Data;
    }
}
