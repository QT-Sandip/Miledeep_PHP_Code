<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class ShipmentPackedBalanceReportController
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

    function getShipmentPackedBalanceDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $Variety = $RequestBody['Variety'];
        $Species = $RequestBody['Species'];
        $ProductForm = $RequestBody['ProductForm'];
        $FreezingType = $RequestBody['FreezingType'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $Grade = $RequestBody['Grade'];
        $Brand = $RequestBody['Brand'];
        $Plant = $RequestBody['Plant'];
        $Orders = $RequestBody['Orders'];
        $Status = $RequestBody['Status'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        // {"FromDate":"","ToDate":"","Variety":"","Species":"","ProductForm":"","FreezingType":"","PackingStyle":"","Grade":"","Brand":"",
        //     "Plant":"","Orders":"","Status":"","PageNo":"","SearchKey":"","LoadAll":""}
        
        $Query = '';

        $DateFilters = " AND '$FromDate' BETWEEN '$ToDate'";

        if ($Variety != "") {
            $Query .= " and  = '$Variety'";
        }
        if ($Species != "") {
            $Query .= " and  = '$Species'";
        }
        if ($ProductForm != "") {
            $Query .= " and  = '$ProductForm'";
        }
        if ($FreezingType != "") {
            $Query .= " and  = '$FreezingType'";
        }
        if ($PackingStyle != "") {
            $Query .= " and  = '$PackingStyle'";
        }
        if ($Grade != "") {
            $Query .= " and  = '$Grade'";
        }
        if ($Brand != "") {
            $Query .= " and  = '$Brand'";
        }
        if ($Plant != "") {
            $Query .= " and  = '$Plant'";
        }
        if ($Orders != "") {
            $Query .= " and  = '$Orders'";
        }
        if ($Status != "") {
            $Query .= " and  = '$Status'";
        }
        
        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', OptID,DeptID,OptionType,Value, Name) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `AdminGeneralOptions` WHERE Status=1 $Query";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlQuery = "SELECT OptID, DeptID, OptionType, Value, Name, Status FROM AdminGeneralOptions 
        WHERE 1=1 AND Status=1 $Query ORDER BY OptID " . $Limit;

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayOptionsData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayOptionsList['SNO'] = ++$i;
            $ArrayOptionsList['OrderNumber'] = $dataRow['OrderNumber'];
            $ArrayOptionsList['Brand'] = $dataRow['Brand'];
            $ArrayOptionsList['Status'] = $dataRow['Status'];
            $ArrayOptionsList['Vareity'] = $dataRow['Vareity'];
            $ArrayOptionsList['Soaked'] = $dataRow['Soaked'];
            $ArrayOptionsList['Type'] = $dataRow['Type'];
            $ArrayOptionsList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayOptionsList['Quantity'] = $dataRow['Quantity'];

            $ArrayOptionsData[] =  $ArrayOptionsList;
        }

        if (count($ArrayOptionsData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['Data'] = $ArrayOptionsData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
