<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class TracebilityDropDownLoadMoreController
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

    function getTracebilityList($RequestBody)
    {
        $PageNo = (isset($RequestBody['PageNo']) && $RequestBody['PageNo'] > 0) ? $RequestBody['PageNo'] : 0;  
        $Length = ($PageNo) * 100;
        $ShowTracebilityIds = $RequestBody['ShowTracebilityIds'];   

        $where = $sqlTot = $sqlRec = "";

        $where = ' WHERE TraceID != ""';
        if(!empty($ShowTracebilityIds)){
            $where .= ' AND Certification=1 AND L2Status=1';
        }
    

        // $SqlTrace = "SELECT TraceID,Tracebility,Tonnage,ExpiryDate,(SELECT sum(UsedQuantityInCodeList) FROM `QcCodeList` as QCL WHERE QTL.TraceID=QCL.TracebilityID 
        // and HarvestDt>=QTL.StartDate) as UsedQuantity ,(SELECT DATEDIFF(CURDATE(),min(HarvestDt) )  FROM `QcCodeList` as QCL  WHERE QTL.TraceID=QCL.TracebilityID 
        // and HarvestDt>=QTL.StartDate) as UsedDays FROM QcTracebilityList as QTL";


        $SqlTrace = "SELECT
            TraceID,
            Tracebility,
            Tonnage,
            ExpiryDate,
            QTL.Bap,
            
            QCL.UsedQuantity,
            QCL.UsedDays
        FROM
            QcTracebilityList AS QTL
        LEFT JOIN(
            SELECT
                SUM(UsedQuantityInCodeList) AS UsedQuantity,
                DATEDIFF(CURDATE(), MIN(HarvestDt)) AS UsedDays,
                TracebilityID,
                HarvestDt
            FROM
                `QcCodeList`
            GROUP BY
                TracebilityID
        ) AS QCL
        ON
            QTL.TraceID = QCL.TracebilityID AND QCL.HarvestDt >= QTL.StartDate";



        $SqlTraceTot = "SELECT COUNT(TraceID) as total FROM QcTracebilityList";


        if( !empty($RequestBody['SearchKey']) ) {   
            $where .=" AND ( Tracebility LIKE '".$RequestBody['SearchKey']."%' )";
        }
        $sqlTot .= $SqlTraceTot;
        $sqlRec .= $SqlTrace;
        //concatenate search sql if value exist
        if(isset($where) && $where != '') {
            $sqlTot .= $where;
            $sqlRec .= $where;
        }
        $sqlRec .=  " ORDER by ABS(Tracebility) LIMIT ".$Length.",100";
        $StmtTraceTot = $this->conn->prepare($sqlTot);
        $StmtTraceTot->execute();        
        $TotalRows = $StmtTraceTot->fetch(PDO::FETCH_ASSOC); 
        $TotalRows = $TotalRows['total'];
        //echo $sqlRec; exit;
        $StmtTrace = $this->conn->prepare($sqlRec);
        $StmtTrace->execute();



        $ArrayData = [];
        $i = 0;
        while ($dataRow = $StmtTrace->fetch(PDO::FETCH_ASSOC)) {

            $BalanceQunatity = $dataRow['Tonnage'] - $dataRow['UsedQuantity'];
            $UsedDays = !empty($dataRow['UsedDays']) ? $dataRow['UsedDays'] : '0';
            $ExpiryDate = !empty($dataRow['ExpiryDate']) ? date('d-m-Y', strtotime($dataRow['ExpiryDate'])) : '--';
            $ArrayList['text'] = $dataRow['Tracebility'].'-'.$dataRow['Bap'] . '-Balance(' . $BalanceQunatity . ')- Days(' . $UsedDays . ')-Expiry on(' . $ExpiryDate . ')';
            $ArrayList['id'] = $dataRow['TraceID'];
            $ArrayList['Bap'] =$dataRow['Bap'];
            $ArrayList['BalanceQunatity'] = $BalanceQunatity;
            $ArrayList['UsedDays'] = $UsedDays;
            $ArrayList['ExpiryDate'] = $ExpiryDate;
            $ArrayData[] = $ArrayList;
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['TotalCount'] = $TotalCount;
        $Data['PageCount'] = $PageCount;
        $Data['TraceabilityData'] = $ArrayData;
        $Data['TotalRows']=$TotalRows;

        return $Data;

        exit;
    }
    
}
