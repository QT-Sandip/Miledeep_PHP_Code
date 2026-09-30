<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class RmAgentListController
{
    private $conn;
    private $db_table = "ScrmPIData";
    private $utilController;



    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    // GET ALL
    public function GetAgentDetails($RequestBody)
    {

        $RecordsPerPage = 100;
        $AgentID = $RequestBody['ID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($AgentID != "") {
            $Query .= " and RmUsersList.ID = '$AgentID'";
        }

        $RecordsPerPage = 100;

        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', UserName,PanNumber,BankName,GstNo) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `RmUsersList` WHERE UserType=3 and Status=1";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

		$sqlFields = "ID,UserName,BankName,PanNumber,BankAccNo,IFSC,GstNo,IsCompany";
        // echo 
        if($AgentID) {
        $SqlAgentQuery = "SELECT $sqlFields FROM `RmUsersList` WHERE UserType=3 and Status=1 $Query";
        } else {
        $SqlAgentQuery = "SELECT $sqlFields FROM `RmUsersList` WHERE UserType=3 and Status=1 $Query ORDER BY ID ". $Limit;
        }

        $stmt = $this->conn->prepare($SqlAgentQuery);
        $stmt->execute();

        $AryAgentData = [];
        $i = $LimitMax;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArrayAgentList['SNO'] = ++$i;
            $ArrayAgentList['ID'] = $dataRow['ID'];
            $ArrayAgentList['UserName'] = $dataRow['UserName'];
            $ArrayAgentList['BankName'] = $dataRow['BankName'];
            $ArrayAgentList['PanNumber'] = $dataRow['PanNumber'];
            $ArrayAgentList['BankAccNo'] = $dataRow['BankAccNo'];
            $ArrayAgentList['IFSC'] = $dataRow['IFSC'];
            $ArrayAgentList['GstNo'] = $dataRow['GstNo'];
            $ArrayAgentList['IsCompany'] = $dataRow['IsCompany'];

            $AryAgentData[] = $ArrayAgentList;
        }

        if (count($AryAgentData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }
        
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "create-agent.php?ID=";
        $data['Data'] = $AryAgentData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
