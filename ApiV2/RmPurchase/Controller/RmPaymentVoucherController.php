<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include_once '../Global/Util.php';
include_once 'UtilController.php';

class RmPaymentVoucherController
{

    private $conn;
    //private $db_table = "RmArrivalDetatils";
    private $utilController;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    // GET ALL
    public function GetRmPaymentVoucherReportDetails($RequestBody)
    {
        $PayDate = $RequestBody['PayDate'];
        $VoucherNo = $RequestBody['VoucherNo'];
        $SupplierID = $RequestBody['SupplierID'];
        $BillerType = $RequestBody['BillerType'];
        $AgentID = $RequestBody['AgentID'];

        if ($SupplierID != '') {
            $Query .= " and RmSupplierID='$SupplierID'";
        }

        if ($AgentID != '') {
            $Query .= " and RAD.AgentID='$AgentID'";
        }

       
           // $DateFilter = "  Date between '$StartDate' and '$EndDate'";
            $DateFilter = "  Date ='$PayDate'";
      
     

         $sqlData="SELECT GROUP_CONCAT(ArrivalID) as ArrivalID ,sum(RPB.Amount) as Amount,CheckNo FROM `RmPaymentDetails` as RDP
        left join  `RmPaymentBreakup`as RPB on RPB.PaymentID=RDP.PaymentID
        left join  `RmArrivalDetatils`as RAD on RAD.RmArrivalID=RPB.ArrivalID
        WHERE $DateFilter and PaymentRefNo='$VoucherNo' and RDP.BillerType='$BillerType' $Query";
        
        $stmtData = $this->conn->prepare($sqlData);
        $stmtData->execute();
        $ArrayReportData=array();
        $dataRow = $stmtData->fetch(PDO::FETCH_ASSOC);

       // while ($dataRow = $stmtData->fetch(PDO::FETCH_ASSOC)) {

$Amount=$dataRow['Amount'];
            $ArrayReportDataTemp=$this->getPaymentData($dataRow['ArrivalID'],$RequestBody);
            $ArrayBillerDetails=$this->getBillerDetails();


            $ArrayReportData['CompanyName']=ucwords(strtolower($ArrayBillerDetails['CompanyName']));
            $ArrayReportData['GSTNumber']=$ArrayBillerDetails['GSTNumber'];
            $ArrayReportData['LogoUrl']=$ArrayBillerDetails['LogoUrl'];
            $ArrayReportData['ChequeNO']=$dataRow['CheckNo'];
            $ArrayReportData['Address']=$ArrayBillerDetails['Address'];
            $ArrayReportData['Amount']=number_format($Amount,2);
            $ArrayReportData['AmountinRupees']=$this->utilController->AmountInWords($Amount);
           
           
          
           

            // foreach( $ArrayReportDataTemp as $Item){

            //     $ArrayReportData[]=$Item;
            //     // $GTotalOSAmount +=$Item['OSAmount'];
            //     //  $GTotalBillAmount +=$Item['BillAmount'];
            //     // $GTotalPaidAmount +=$Item['PaidAmount'];
            // }

    //    }
      
    

        // $ArrayTotals['TotalOSAmount'] = round($GTotalOSAmount, 2);
        // $ArrayTotals['TotalBillAmount'] = round($GTotalBillAmount,2);
        // $ArrayTotals['TotalPaidAmount'] = number_format($GTotalPaidAmount, 2);
			
            //$Data['PaymentVoucherReportData'] = $ArrayReportData;
            // $ArrayReportData['BillNumbers']=$ArrayReportDataTemp;
            $Data['PaymentVoucherReportData'] = $ArrayReportData;
            $Data['BillNumbers'] = $ArrayReportDataTemp;
        return $Data;
    }

    public function getPaymentData($ArrivalID,$RequestBody)
    {
        $AgentID = $RequestBody['AgentID'];
        $SupplierID = $RequestBody['SupplierID'];
        
        if($SupplierID!=''){
$Query=' and RmSupplierID='.$SupplierID;
        }
        
        if($AgentID!=''){
$Query.=' and AgentID='.$AgentID;
        }
   
        $SqlFields = "ArrivalNumber,BillDate,FinancierName,UserName";
       

        $sqlRmArrivalQuery = "SELECT $SqlFields FROM `RmArrivalDetatils` as RAD
        left join RmSuppliers as RS on RS.ID = RAD.RmSupplierID
        left join RmUsersList as RU on RU.ID = RAD.AgentID
        WHERE RmArrivalID in ($ArrivalID) $Query "; //limit 100 
     

        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();

        
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
           
            $ArrayBillNos[]=$dataRow['ArrivalNumber'].' Dt:-'.$this->utilController->getFormatedDateEmpty($dataRow['BillDate']);
            $ArrayBillData['BillNos']=implode(' , ',$ArrayBillNos);  
            $ArrayBillData['SupplierName']=$dataRow['FinancierName'];  
            $ArrayBillData['UserName']=$dataRow['UserName'];  
           
        }
      //echo $BillNos= $ArrayBillNos;exit;
        return $ArrayBillData;


       // $ArrayReportData[] = $ArrayBookingList;
     
       //return $ArrayBillNos;
    }
    public function getBillerDetails(){
        

        $SqlCheckupBrkUp = "select Name as CompanyName,LogoUrl,GSTNumber,Address  from AdminClientDetails ";
        $stmtCheck = $this->conn->prepare($SqlCheckupBrkUp);
        $stmtCheck->execute();
       return $BillerDetails= $stmtCheck->fetch(PDO::FETCH_ASSOC);


        

    }



    // public function getBillDetails($ArrivalID)
    // {

    //    $AryBillDetails= $this->utilController->getBillPriceDetails($ArrivalID);
    //   // var_dump( $AryBillDetails);
    //    $data['BillAmount']=sprintf('%0.2f', round($AryBillDetails['TotalBillAmount'],2));
    //    $data['TotalTDS']=sprintf('%0.2f', round($AryBillDetails['TotalTDS'],2));
    //    $data['TotalBillAmountExTDS']=sprintf('%0.2f', round($AryBillDetails['TotalBillAmountExTDS'],2));
     
    //     return $data;
    // }

    // public function getBillPaidAmountDetails($ArrivalID)
    // {
    //      $sqlQuery = "SELECT sum(Amount) as PaidAmount FROM `RmPaymentBreakup` WHERE ArrivalID='$ArrivalID'";
    //     $stmt = $this->conn->prepare($sqlQuery);
    //     $stmt->execute();
        
    //     if( $stmt->rowCount() ==0){
    //         $data['PaidAmount']='0';
      
    //     }else{
    //         $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
    //         $data['PaidAmount']=$dataRow['PaidAmount']? $dataRow['PaidAmount'] : 0;
    //    }	
    //     return $data;
    // }

   
}
?>