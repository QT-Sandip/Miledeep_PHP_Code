<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class RmFinanciersListController
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
    public function GetRmFinanciersListDetails($RequestBody)
    {

        $RecordsPerPage = 100;
        $ID = $RequestBody['ID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($ID != "") {
            $Query .= " and RmSuppliers.ID = '$ID'";
        }

        $RecordsPerPage = 100;

        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', FinancierName,GstNo,PanNumber,BankName) LIKE '%$SearchKey%'";
        }

        $sqlRmFinancierCount = "SELECT COUNT(*) as Total  FROM `RmSuppliers`";

        $stmt = $this->conn->prepare($sqlRmFinancierCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "ID,SupplierType,FinancierName,Address,PurchaseCenter,BankName,BankAccNo,GstNo,IFSC,PanNumber,EMailID,PhoneNumber,Status,IsCompany";

        if ($ID != '') {
            $SqlRmFinancierQuery = "SELECT  $sqlFields FROM `RmSuppliers` WHERE 1=1 $Query";
        } else {
            $SqlRmFinancierQuery = "SELECT  $sqlFields FROM `RmSuppliers` WHERE 1=1 $Query ORDER BY ID " . $Limit;
        }

        $stmt = $this->conn->prepare($SqlRmFinancierQuery);
        $stmt->execute();

        $AryFinancierData = [];
        $i = $LimitMax;

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArrayFinancierList['SNO'] = ++$i;
            $ArrayFinancierList['ID'] = $dataRow['ID'];
            $ArrayFinancierList['SupplierType'] = $dataRow['SupplierType'];
            $ArrayFinancierList['FinancierName'] = $dataRow['FinancierName'];
            $ArrayFinancierList['Address'] = $dataRow['Address'];
            $ArrayFinancierList['PurchaseCenter'] = $dataRow['PurchaseCenter'];
            $ArrayFinancierList['BankName'] = $dataRow['BankName'];
            $ArrayFinancierList['BankAccNo'] = $dataRow['BankAccNo'];
            $ArrayFinancierList['GstNo'] = $dataRow['GstNo'];
            $ArrayFinancierList['IFSC'] = $dataRow['IFSC'];
            $ArrayFinancierList['PanNumber'] = $dataRow['PanNumber'];
            $ArrayFinancierList['EMailID'] = $dataRow['EMailID'];
            $ArrayFinancierList['PhoneNumber'] = $dataRow['PhoneNumber'];
            $ArrayFinancierList['Status'] = $dataRow['Status'];
            $ArrayFinancierList['IsCompany'] = $dataRow['IsCompany'];

            $AryFinancierData[] = $ArrayFinancierList;
        }

        if (count($AryFinancierData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "create-financier.php?ID=";
        $data['Data'] = $AryFinancierData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
