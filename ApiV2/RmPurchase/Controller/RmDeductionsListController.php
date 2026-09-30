<?php
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
class RmDeductionsListController
{
    private $conn;  
    private $utilController;
    private $Error = null;
    private $Msg = '';

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    public function getRmDeductionsList($RequestBody)
    {
$Species=$RequestBody['Species'];
$Year=$RequestBody['Year'];
$Month=$RequestBody['Month'];

if($Month!=''){
$Query=" and month(EffectedDate)='$Month'";
}

        $SqlFieldsRmDeductionData = " DeductionRateID,`Species`, `EffectedDate`, PurchaseType as PurchaseCategoryName";

        $sqlRmDeductionList = "SELECT $SqlFieldsRmDeductionData FROM `RmDeductionRates` as RAD 
        left join RmPurchaseCategory as RPC on RPC.CatID= RAD.PurchaseCategoryID  WHERE Species='$Species' 
        and Year(EffectedDate)='$Year' $Query"; 
        $stmtRmDeductionList = $this->conn->prepare($sqlRmDeductionList);
        $stmtRmDeductionList->execute();
       
        while ($dataRowDeductionList = $stmtRmDeductionList->fetch(PDO::FETCH_ASSOC)) {
            $dataRowDeductionList['EffectedDate']=$this->utilController->getFormatedDate($dataRowDeductionList['EffectedDate']);          
         
            $ArrayData[] = $dataRowDeductionList;
        }
        $ArrayDeductionData['rmDeductionData'] = $ArrayData;
         $Message = 'Success';
        $data['status'] = $this->geterrorMessages($Message);
        $data['data'] = $ArrayDeductionData;
        return $data;
    }

    

    // function getRmDeductionDetails($RateID)
    // {
    //     $SqlFieldsRmDeductionData = " DeductionRateID,`PurchaseCategoryID` as PurchaseType, `Species`, `CenterID`, `EffectedDate`";

    //     $sqlRmDeductionData = "SELECT $SqlFieldsRmDeductionData FROM `RmDeductionRates` WHERE DeductionRateID='$RateID'";
    //     $stmtRmDeductionData = $this->conn->prepare($sqlRmDeductionData);
    //     $stmtRmDeductionData->execute();
    //     $dataRowDeductionData = $stmtRmDeductionData->fetch(PDO::FETCH_ASSOC);
    //     $dataRowDeductionData['EffectedRate']='';

    //     $SqlFieldsRmDeductionBreakupData = "DeductionBreakupID,`StartCount`, `EndCount`,  `StartWeight`, `EndWeight`, `Extra`";

    //     $sqlRmDeductionBreakupData = "SELECT $SqlFieldsRmDeductionBreakupData FROM  RmDeductionRatesBreakup  WHERE DeductionRateID='$RateID'";
    //     $stmtRmDeductionDataBreakupData = $this->conn->prepare($sqlRmDeductionBreakupData);
    //     $stmtRmDeductionDataBreakupData->execute();
    //     $SNo=0;
    //     while ($dataRowDeductionBreakupData = $stmtRmDeductionDataBreakupData->fetch(PDO::FETCH_ASSOC)) {
    //         $dataRowDeductionBreakupData['SNo']=++$SNo;            
    //         $ArrayData[] = $dataRowDeductionBreakupData;
    //     }
    //     $ArrayDeductionData['rmDeductionData'] = $dataRowDeductionData;
    //     $ArrayDeductionData['rmDeductionBreakUpData'] = $ArrayData;
    //     return $ArrayDeductionData;

    // }
    
  
    function geterrorMessages($Message)
    {
        $data['Error'] = null;
        $data['Msg'] = $Message;
        return $data;
    }
}
