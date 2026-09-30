<?php

include_once '../Global/Util.php';
include_once 'UtilController.php';
include_once __DIR__ . '/AuditLogger.php';

class RmAgentBillWiseOutStandingReportController
{

    private $conn;
    private $db_table = "RmArrivalDetatils";
    private $utilController;
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new AuditLogger($this->conn, 'Purchase');
    }
    // GET ALL
    public function GetRmAgentBillWiseOutStandingReportDetails($RequestBody)
    {
        $Plant = $RequestBody['Plant'];
        $CenterID = $RequestBody['CenterID'];
        $StartDate = $RequestBody['StartDate'];
        $AgentID = $RequestBody['AgentID'];
       
           // $DateFilter = "  Date between '$StartDate' and '$EndDate'";
            $DateFilter = "  BillDate <='$StartDate'";
      
        $Query = '';

        if ($CenterID != '') {
            $Query .= " and ArrivalStationID='$CenterID'";
        }

        if ($Plant != '') {
            $Query .= " and RAD.ProcessingStationID='$Plant'";
        }   
        
        if($AgentID!=''){
            $Query .= " and AgentID='$AgentID'";
           
        }


        // $sqlSuppliersID="SELECT ID as SupplierID FROM `RmSuppliers` WHERE ID in (SELECT FinancierID FROM `RmBillDetails` as RPD 
        // left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RPD.ArrivalID  WHERE $DateFilter $Query  and  RPD.PaymentStatus='0')  ";
        
        $sqlAgentID="SELECT  ID FROM `RmUsersList` WHERE UserType=3 AND  ID in (SELECT BillAgentID FROM `RmBillDetails` as RPD 
        left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RPD.ArrivalID  WHERE $DateFilter $Query  and  RPD.PaymentStatus='0')  ";
        
        $stmtAgentID = $this->conn->prepare($sqlAgentID);
        $stmtAgentID->execute();
        $ArrayReportData=array();
        // var_dump($ArrayReportData);
        // exit;

        while ($dataRow = $stmtAgentID->fetch(PDO::FETCH_ASSOC)) {


            $ArrayReportDataTemp=$this->getAgentWiseData($DateFilter,$Query,$dataRow['BillAgentID']);

            foreach( $ArrayReportDataTemp as $Item){

                $ArrayReportData[]=$Item;
                $GTotalOSAmount +=$Item['OSAmount'];
                $GTotalBillAmount +=$Item['BillAmount'];
                $GTotalPaidAmount +=$Item['PaidAmount'];
            }

        }
      
    

        $ArrayTotals['TotalOSAmount'] = round($GTotalOSAmount, 2);
        $ArrayTotals['TotalBillAmount'] = round($GTotalBillAmount,2);
        $ArrayTotals['TotalPaidAmount'] = number_format($GTotalPaidAmount, 2);
			
            $Data['AgentBillwiseReportData'] = $ArrayReportData;
            $Data['Totals'] = $ArrayTotals;
            return $Data;
    }

    public function getAgentWiseData($DateFilter,$Query,$AgentID)
    {

   
        $SqlFields = "ArrivalID as ArrivalIDs,UserName as AgentName,PanNumber,ArrivalNumber,BillDate";
       

        $sqlRmArrivalQuery = "SELECT $SqlFields  FROM `RmBillDetails` as RPD 
        left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RPD.ArrivalID 
        left join RmUsersList as RPS on RPS.ID=RAD.AgentID
       WHERE $DateFilter $Query   and  RPD.PaymentStatus='0' and  RAD.BillAgentID=$AgentID order by BillDate asc  ";   //limit 100
      

        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();

        $i = 0;
        $SubTotal = 0;
        $TotalPaidAmount = 0;
        $TotalBillAmount = 0;
        $Data = array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrivalIDs = $dataRow['ArrivalIDs'];
            

            $WeightDetails = $this->getBillDetails($ArrivalIDs);
            $BillPaidDetails=$this->getBillPaidAmountDetails($ArrivalIDs);
          
    

            $ArrayBookingList['SNO'] = ++$i;
            $AgentName = $dataRow['AgentName'];
            $ArrayBookingList['AgentName'] = $dataRow['AgentName'];
            $ArrayBookingList['PanNumber'] = $dataRow['PanNumber'];
            $ArrayBookingList['BillDate'] = $this->utilController->getFormatedDateEmpty($dataRow['BillDate']);
            $ArrayBookingList['BillNo'] = $dataRow['ArrivalNumber'];
            //$ArrayBookingList['BillAmount'] = round($WeightDetails['BillAmount'], 2);
            $ArrayBookingList['BillAmount'] = sprintf('%0.2f',round($WeightDetails['TotalBillAmountExTDS'], 2));
            $ArrayBookingList['PaidAmount'] = round($BillPaidDetails['PaidAmount'], 2);             
           // $ArrayBookingList['OSAmount'] = number_format($OsAMount,2);
            $ArrayBookingList['OSAmount'] = sprintf('%0.2f', round($WeightDetails['TotalBillAmountExTDS']-$BillPaidDetails['PaidAmount'],2));
            $OSAmount = round($WeightDetails['TotalBillAmountExTDS']-$BillPaidDetails['PaidAmount'],2);
        
            if( $OSAmount >0){             
                $ArrayReportData[] = $ArrayBookingList;
                $SubTotal +=$ArrayBookingList['OSAmount'];
              // $TotalBillAmount +=$WeightDetails['BillAmount'];
               $TotalBillAmount +=$WeightDetails['TotalBillAmountExTDS'];
                $TotalPaidAmount +=$BillPaidDetails['PaidAmount'];
            }else if($OSAmount <= 0){
                 $existingBillStatus = $this->auditLogger->fetchRowsByConditions('RmBillDetails', ['ArrivalID' => $ArrivalIDs]);
                 $existingBillStatus = !empty($existingBillStatus) ? $existingBillStatus[0] : null;
                 $sqlRmPayment = "UPDATE `RmBillDetails` set PaymentStatus=1 WHERE ArrivalID='$ArrivalIDs'";  
                $stmtRmPayment = $this->conn->prepare($sqlRmPayment);
                 $stmtRmPayment->execute();
                 if ($stmtRmPayment) {
                    $updatedBillStatus = $this->auditLogger->fetchRowsByConditions('RmBillDetails', ['ArrivalID' => $ArrivalIDs]);
                    $updatedBillStatus = !empty($updatedBillStatus) ? $updatedBillStatus[0] : null;
                    $this->auditLogger->logUpdate('RmBillDetails', $ArrivalIDs, $existingBillStatus, $updatedBillStatus, $RequestBody, [
                        'ReferenceNo' => $ArrivalIDs,
                        'Remarks' => 'Bill payment status auto-closed from outstanding report',
                    ]);
                 }
                
            }
            
        }
        $ArrayBookingList=array();

         $ArrayBookingList['SNO'] = '';
        $ArrayBookingList['AgentName'] = $AgentName;
              $ArrayBookingList['SubTotal'] =  sprintf('%0.2f', round($SubTotal,2));

        $ArrayReportData[] = $ArrayBookingList;
     
        return $ArrayReportData;
    }




    public function getBillDetails($ArrivalID)
    {

       $AryBillDetails= $this->utilController->getBillPriceDetails($ArrivalID);
      // var_dump( $AryBillDetails);
       $data['BillAmount']=sprintf('%0.2f', round($AryBillDetails['HarvestBillAmount'],2));
       $data['TotalTDS']=sprintf('%0.2f', round($AryBillDetails['HarvestTDS'],2));
       $data['TotalBillAmountExTDS']=sprintf('%0.2f', round($AryBillDetails['HarvestBillAmountExTDS'],2));
     
        return $data;
    }

    public function getBillPaidAmountDetails($ArrivalID)
    {
         $sqlQuery = "SELECT sum(Amount) as PaidAmount FROM `RmPaymentBreakup` WHERE ArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        
        if( $stmt->rowCount() ==0){
            $data['PaidAmount']='0';
      
        }else{
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['PaidAmount']=$dataRow['PaidAmount']? $dataRow['PaidAmount'] : 0;
       }	
        return $data;
    }

   

}
?>
