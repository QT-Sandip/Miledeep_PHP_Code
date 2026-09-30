<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class HLVAVarietyListController
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
    public function GetHLVAVarietyDetails($RequestBody)
    {

        $RecordsPerPage = 100;
        $VarietyID = $RequestBody['VarietyID'];
        $ShortName = $RequestBody['ShortName'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($ShortName != "") {
            $Query .= " and PHVL.ShortName = '$ShortName'";
        }

        if ($VarietyID != "") {
            $Query .= " and PHVL.VarietyID = '$VarietyID'";
        }

        $RecordsPerPage = 100;

        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', TypeProduct,ShortName,FullName,Category) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `PpcHLVAVarietyList`";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "Species, VarietyID, TypeProduct, ShortName, FullName, Category, Status, ProductID, ProductDescription";

        if ($VarietyID != '') {
            $SqlHLVAVarietyQuery = "SELECT $sqlFields FROM PpcHLVAVarietyList AS PHVL WHERE 1=1 $Query";
        } else {
            $SqlHLVAVarietyQuery = "SELECT $sqlFields FROM PpcHLVAVarietyList AS PHVL WHERE 1=1 $Query ORDER BY VarietyID " . $Limit;
        }

        $stmt = $this->conn->prepare($SqlHLVAVarietyQuery);
        $stmt->execute();

        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayVarietyList['SNO'] = ++$i;
            $ArrayVarietyList['VarietyID'] = $dataRow['VarietyID'];
            $ArrayVarietyList['Species'] = $dataRow['Species'];
            $ArrayVarietyList['TypeProduct'] = $dataRow['TypeProduct'];
            $ArrayVarietyList['ShortName'] = $dataRow['ShortName'];
            $ArrayVarietyList['FullName'] = $dataRow['FullName'];
            $ArrayVarietyList['Category'] = $dataRow['Category'];
            $ArrayVarietyList['Status'] = $dataRow['Status'];
            $ArrayVarietyList['Product'] = $dataRow['ProductID'];
            $ArrayVarietyList['ProductDescription'] = $dataRow['ProductDescription'];

            $AryVarietyData[] = $ArrayVarietyList;
        }
        if (count($AryVarietyData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "create-hlva-variety?VarietyID=";
        $data['Data'] = $AryVarietyData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
