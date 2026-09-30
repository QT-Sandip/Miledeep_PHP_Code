<?php
include_once '../Global/Util.php';
include_once 'UtilController.php';
class RmPaymentAgentCommisionReportController
{
    private $conn;
    private $db_table = "RmArrivalDetatils";
    private $utilController;
    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    // GET ALL
    public function GetRmPaymentAgentCommisionReportDetails($RequestBody)
{
    $Plant = $RequestBody['Plant'];
    $CenterID = $RequestBody['CenterID'];
    $StartDate = $RequestBody['StartDate'];
    $EndDate = $RequestBody['EndDate'];
    $SupplierID = $RequestBody['SupplierID'];
    $ReqAgentID = $RequestBody['AgentID'];
    $ReportType = $RequestBody['ReportType'];

    // Pagination parameters
    // $page = isset($RequestBody['page']) && is_numeric($RequestBody['page']) ? (int)$RequestBody['page'] : 1;
    // $pageSize = isset($RequestBody['pageSize']) && is_numeric($RequestBody['pageSize']) ? (int)$RequestBody['pageSize'] : 20;

    // $offset = ($page - 1) * $pageSize;

    // Base Query
    $Query = " rb.HarvestCommision != 0 ";

    if (!empty($StartDate) && !empty($EndDate)) {
        $Query .= " AND ra.BillDate BETWEEN '$StartDate' AND '$EndDate' ";
    }

    if (!empty($CenterID)) {
        $Query .= " AND ra.ArrivalStationID = '$CenterID' ";
    }

    if (!empty($Plant)) {
        $Query .= " AND ra.ProcessingStationID = '$Plant' ";
    }

    if (!empty($ReqAgentID)) {
        $Query .= " AND ra.AgentID = '$ReqAgentID' ";
    }
    $Groupby = "";
    $sqlFields = "ra.AgentID,

            al.UserName AS AgentName,

            al.PanNumber AS AgentPAN,

            a.StationName AS PurchaseStation,

            ps.StationName AS ProcessingStation,

            ra.ArrivalDate,

            ra.ArrivalNumber,

            ra.RmArrivalID,

            rb.RmBillID,

            ra.BillDate,

            ra.AgentID,

            rb.CreatedDateTime,

            rb.BillAmount,

           ROUND(rb.HarvestAmount, 2) AS HarvestAmount,

           ROUND(rb.HarvestCommision, 2) AS BillHarvestCommission,

            ra.HarvestCommission AS ArrivalHarvestCommission";
    if ($ReportType == 'M') {
        $sqlFields = "ra.AgentID,

            al.UserName AS AgentName,

            al.PanNumber AS AgentPAN,

            a.StationName AS PurchaseStation,

            ps.StationName AS ProcessingStation,

            ra.ArrivalDate,

            ra.ArrivalNumber,

            ra.RmArrivalID,

            rb.RmBillID,

            ra.BillDate,

            ra.AgentID,

            rb.CreatedDateTime,

            SUM(rb.BillAmount) as BillAmount,

           ROUND(SUM(rb.HarvestAmount), 2) AS HarvestAmount,

           ROUND(SUM(rb.HarvestCommision), 2) AS BillHarvestCommission,

            SUM(ra.HarvestCommission) AS ArrivalHarvestCommission";

        $Groupby = " group by ra.ArrivalStationID,ra.AgentID";
        
    } else if ($ReportType == 'B') {
        $Groupby = " and rb.HarvestAmount > 0 ";
    } 

    // Count total matching records without LIMIT
//     $countQuery = "
// SELECT COUNT(*) AS Total FROM (
//     SELECT rb.ArrivalID
//     FROM RmBillDetails rb
//     LEFT JOIN RmArrivalDetatils ra ON rb.ArrivalID = ra.RmArrivalID
//     LEFT JOIN RmPurchaseStations a ON ra.ArrivalStationID = a.PurchaseStationID
//     LEFT JOIN RmProcessingStations ps ON ra.ProcessingStationID = ps.ProcessingStationID
//     LEFT JOIN RmUsersList al ON ra.AgentID = al.ID
//     WHERE $Query
//     $Groupby
// ) AS grouped
// ";

    //echo $countQuery; exit;
    // $stmtCount = $this->conn->prepare($countQuery);
    // $stmtCount->execute();
    // $totalRows = $stmtCount->fetch(PDO::FETCH_ASSOC)['Total'];
    //echo $totalRows; exit;
    // Main SQL with LIMIT and OFFSET for pagination
    $sqlSuppliersID = "
        SELECT 
            $sqlFields
        FROM 
            RmBillDetails rb
        LEFT JOIN 
            RmArrivalDetatils ra ON rb.ArrivalID = ra.RmArrivalID
        LEFT JOIN 
            RmPurchaseStations a ON ra.ArrivalStationID = a.PurchaseStationID
        LEFT JOIN 
            RmProcessingStations ps ON ra.ProcessingStationID = ps.ProcessingStationID
        LEFT JOIN 
            RmUsersList al ON ra.AgentID = al.ID
        WHERE 
            $Query $Groupby
        ORDER BY 
            ra.ArrivalNumber ASC
         
    ";
    //LIMIT $offset, $pageSize
//return $sqlSuppliersID;
// echo $sqlSuppliersID; exit;
   // error_log($sqlSuppliersID);
    $stmtSuppliersID = $this->conn->prepare($sqlSuppliersID);
    $stmtSuppliersID->execute();

    $Data = [];
    $sno = 1;
     $TotalHarvestAmount = 0;
     $TotalHarvestCommision = 0;
    while ($dataRow = $stmtSuppliersID->fetch(PDO::FETCH_ASSOC)) {
        // echo print_r($dataRow);
        $dataRow['SNO'] = $sno++;
        $Data[] = $dataRow;
        $TotalHarvestAmount += $dataRow['HarvestAmount'];
        $TotalHarvestCommision += $dataRow['BillHarvestCommission'];
       
    }
   $ArrayTotals = [
    'TotalHarvestAmount' => number_format($TotalHarvestAmount, 2, '.', ''),
    'TotalHarvestCommision' => number_format($TotalHarvestCommision, 2, '.', ''),
];


    //return $Data;
    return [
        'data' => $Data,
        'Totals' => $ArrayTotals,
    ];
}

}
?>