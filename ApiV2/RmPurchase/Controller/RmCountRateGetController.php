<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class RmCountRateGetController
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


    public function getRmCount($RequestBody)
    {
        $BillDate = $RequestBody['BillDate'];
        $CenterId = $RequestBody['CenterId'];
        $Species = $RequestBody['Species'];
        $ArrivalCount = $RequestBody['ArrivalCount'];

        $sqlRmDateID = "SELECT `ID` FROM `RmPurchaseRatesHistory` 
		WHERE `RmPriceDate` = '$BillDate' AND `SType` = '$Species' AND `CenterID` IN ($CenterId, 0)";
        $stmtRmDateID = $this->conn->prepare($sqlRmDateID);
        $stmtRmDateID->execute();

        // $errorInfo = $stmtRmDateID->errorInfo();
        // var_dump($errorInfo);

        $rmDataRow = $stmtRmDateID->fetch(PDO::FETCH_ASSOC);
        $dateId = $rmDataRow['ID'];

        $sqlRmRate = "SELECT `RmPrice` FROM `RmPurchaseRates` WHERE `RmCount` = '$ArrivalCount' AND `DateID` = '$dateId'";
        $stmtRmRate = $this->conn->prepare($sqlRmRate);
        $stmtRmRate->execute();
        $dataRowRate = $stmtRmRate->fetch(PDO::FETCH_ASSOC);
        $rmRate = $dataRowRate['RmPrice'];

        $data['Error'] = null;
        $data['Msg'] = "Success";
        $data['RmRate'] = $rmRate;

        return $data;
    }
}
