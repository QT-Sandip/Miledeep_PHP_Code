<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class ReglazingReportController
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

    function getProductionYields($RequestBody)
    {
        // echo "<pre>"; print_r($RequestBody); exit;
        
        $LotId = $RequestBody['LotId'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        $RecordsPerPage = 20;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', FreezingType, Shift, Grade, PackingStyle, ProductionDate,  Brand, Shift, Mcs) LIKE '%$SearchKey%'";
        }

        $SqlIqfData = "SELECT COUNT(*) as Total FROM ProdReglazingData WHERE LotId='$LotId' $Query";
        //echo $SqlIqfData; exit;
        $stmt = $this->conn->prepare($SqlIqfData);
        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        /*$sqlFields = "FreezingType, Shift, Grade, PackingStyle, ProductionDate,  Brand, Shift, 
         Mcs";*/

        if ($LotId != '') {
            $sqlQuery = "SELECT * FROM `ProdReglazingData` 
            WHERE LotId='$LotId' $Query  " . $Limit;
        } else {
            $sqlQuery = "SELECT * FROM `ProdReglazingData`
            WHERE 1=1 $Query  " . $Limit;
        }
        $sqlQuery = "SELECT PoID,Grade,FreezingType,PackingStyle,ProductionDate,Brand,Shift,Mcs,ProdID FROM `ProdReglazingData` 
            WHERE LotId='$LotId' $Query  order by ProductionDate desc " . $Limit;
         
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayStock = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {            
            $PIID = $dataRow['PoID'];
            $SqlPoList = "SELECT PoNo FROM `ScrmPoData` WHERE PoID='$PIID'";            
            $StmtPoList = $this->conn->prepare($SqlPoList);
            $StmtPoList->execute();
            $RowPoList = $StmtPoList->fetch(PDO::FETCH_ASSOC);            
            $dataRow['PoNo'] = $RowPoList['PoNo']?$RowPoList['PoNo']:'';
            $dataRow['ProductionDate'] =date('d-m-Y', strtotime($dataRow['ProductionDate']));
            $ArrayStock[] = $dataRow;
        }

        if (count($ArrayStock) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        $data['Data'] = $ArrayStock;

        return $data;
    }
}
