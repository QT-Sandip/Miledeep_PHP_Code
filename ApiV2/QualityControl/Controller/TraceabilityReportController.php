<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class TraceabilityReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }

    function getTraceabilityReportData($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $PageNo = $RequestBody['PageNo'];
        // $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        // $Query = '';
        $FromDayCode = $this->utilController->getDaycodeofDate($FromDate);
        $ToDayCode = $this->utilController->getDaycodeofDate($ToDate);
        $DateFilters = " BETWEEN '$FromDayCode' AND '$ToDayCode'";

        // $DateFilters = "BETWEEN '$FromDate' AND '$ToDate'";

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = "LIMIT $RecordsPerPage OFFSET $LimitMax";

        if ($LoadAll == 1) {
            $Limit = '';
        }

        $sql = " QTL.FarmerName, QTL.AddressContinued, QTL.Tracebility,QTL.StartDate,QTL.ExpiryDate,CL.DayCode, QLT.TraceID,CL.VarietyID,
        SUM(McsTotal) AS McsTotal,Sum(PackingStyle) AS PackingStyle,Sum(Grades) AS Grades,";
        //  SUM(McsTotal*PackingStyle*Grades) AS Tonnage,



        $sqlExt = "(McsTotal*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs";





        $sql = "SELECT *,$sqlExt  FROM QcCodeList AS CL 
            LEFT JOIN QcTracebilityList AS QLT ON CL.TracebilityID = QLT.TraceID 
            left join AdminPackingStyles as APS on CL.PackingStyleID=APS.PCKID
            WHERE CL.DayCode $DateFilters  ORDER BY CL.DayCode ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $dataCount = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataCount['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

//         var_dump($sql);
// exit;

        $i = 0;
        $TraceabilityReportData = [];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $TraceabilityList['SNO'] = ++$i;
            $TraceabilityList['FarmerName'] = $dataRow['FarmerName'];
            $TraceabilityList['AddressContinued'] = $dataRow['AddressContinued'];
            $TraceabilityList['Tracebility'] = $dataRow['Tracebility'];
            $TraceabilityList['McsTotal'] = $dataRow['McsTotal'];
            $TraceabilityList['Tonnage'] = number_format($dataRow['NetWgtKgs'] / 1000, 4);
            //  $TraceabilityList['Tonnage'] =$dataRow['McsTotal'] + $dataRow['PackingStyle'] + $dataRow['Grades']; 
            // $TraceabilityList['Tonnage'] = $dataRow['Tonnage'].'(' . $dataRow['PackingStyle'] . '-' . $dataRow['McsTotal'].')';
            $TraceabilityList['StartDate'] = $dataRow['StartDate'];
            $TraceabilityList['ExpiryDate'] = $dataRow['ExpiryDate'];
            $TraceabilityReportData[] = $TraceabilityList;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TraceabilityReportData'] = $TraceabilityReportData;

        // echo ($data);
// exit;
        return $data;
    }
}



