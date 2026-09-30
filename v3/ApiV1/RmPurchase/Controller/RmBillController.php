<?php

include_once '../Global/Util.php';
include_once 'UtilController.php';

class RmBillController
{

    private $conn;
    private $db_table = "RmBillDetails";
    private $utilController;



    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
  
    }
    // GET ALL
    public function getRmBillDetails($RequestBody)
    {  
        $RmDate = $RequestBody['RmDate'];
        $PsId = $RequestBody['PsId'];

        $Query='';
        
        if($PsId!=''){
            $Query="and RLD.PurchaseStationID='$PsId'";
        }
        $Sqlfileds="RmReferenceID,LotNumber,PurchaseDate,FinancierName,GraderID, AgentID,PurchaseCount,PurchaseWeight";
        $sqlQuery = "SELECT  $Sqlfileds FROM RmBillDetails as RBD
         left join RmFinanciers as RMF on RMF.ID=RBD.FinancierID 
         left join RmLotDetatils as RLD on RLD.RmLotID =RBD.LotID  where PurchaseDate ='$RmDate' $Query order by PurchaseDate,LotNumber desc";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $i = 0;
        $Data=array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArrayLotList['sno'] = ++$i;
            $ArrayLotList['ID'] = $dataRow['RmReferenceID'];
            $ArrayLotList['LotNumber'] = $dataRow['LotNumber'];
            $ArrayLotList['PurchaseDate'] = $dataRow['PurchaseDate'];
            $ArrayLotList['RmDate'] = $dataRow['PurchaseDate'];
            $ArrayLotList['BillName'] = $dataRow['FinancierName'];
            $GraderID=$dataRow['GraderID'];
            $ArrayLotList['GraderID'] = $this->utilController->getUserName($GraderID);
            $AgentID=$dataRow['AgentID'];
            $ArrayLotList['AgentID'] =$this->utilController->getUserName($AgentID);
            $ArrayLotList['PurchaseCount'] = $dataRow['PurchaseCount'];
            $ArrayLotList['PurchaseWeight'] = $dataRow['PurchaseWeight'];
            $ArrayLotList['AbStatus'] = '';
            $Data[] = $ArrayLotList;
           
        }

        return $Data;
    }

}
?>
