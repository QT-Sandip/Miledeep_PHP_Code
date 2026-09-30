<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class RackWiseDummyFinishedDetailsController
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

    function getRackWiseStockDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $Plant = $RequestBody['Plant'];
        $Variety = $RequestBody['Variety'];
        $Group = $RequestBody['Group'];
        $Grade = $RequestBody['Grade'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $ColdStore = $RequestBody['ColdStore'];
        $PoNo = $RequestBody['PoNo'];
        $Brand = $RequestBody['Brand'];
        $ClosingDate = $RequestBody['ClosingDate'];
        $ProductCombination = $RequestBody['ProductCombination'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        // {"Plant":"","Variety":"","Group":"","Grade":"","PackingStyle":"","ColdStore":"","Brand":"","PoNo":"","ClosingDate":"","PageNo":"","SearchKey":"","LoadAll":""}

        $Query = '';

        if ($Plant != "") {
            $Query .= " and  = '$Plant'";
        }
        if ($Variety != "") {
            $Query .= " and  = '$Variety'";
        }
        if ($Group != "") {
            $Query .= " and  = '$Group'";
        }
        if ($Grade != "") {
            $Query .= " and  = '$Grade'";
        }
        if ($PackingStyle != "") {
            $Query .= " and  = '$PackingStyle'";
        }
        if ($ColdStore != "") {
            $Query .= " and  = '$ColdStore'";
        }
        if ($Brand != "") {
            $Query .= " and  = '$Brand'";
        }
        if ($ClosingDate != "") {
            $Query .= " and  = '$ClosingDate'";
        }
        if ($PoNo != "") {
            $Query .= " and  = '$PoNo'";
        }
        if ($ProductCombination != "") {
            $Query .= " and  = '$ProductCombination'";
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
            $ArrayOptionsList['ClickforStockminus'] = $dataRow['ClickforStockminus'];
            $ArrayOptionsList['Plant'] = $dataRow['Plant'];
            $ArrayOptionsList['OrderNumber'] = $dataRow['OrderNumber'];
            $ArrayOptionsList['ColdStore'] = $dataRow['ColdStore'];
            $ArrayOptionsList['Rack'] = $dataRow['Rack'];
            $ArrayOptionsList['LastInwardDate'] = $dataRow['LastInwardDate'];
            $ArrayOptionsList['Combination'] = $dataRow['Combination'];
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
