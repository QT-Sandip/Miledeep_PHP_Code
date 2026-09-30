<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'UtilController.php';

class RmPaymentListController
{

    private $conn;
    private $db_table = "RmArrivalDetatils";
    private $utilController;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    // GET ALL

    public function getPendingPayments($RequestBody)
    {
        $ClientID = $RequestBody['ClientID'];
        // var_dump($RequestBody);

        $Type = $RequestBody['Type'];
        $Query = '';

        $SqlFields = " BillStatus,RmArrivalID ,GraderID,AgentID,
        PurchaseType,ArrivalNumber,LotStatus,FarmAddress
        ,ArrivalDate,BillDate,RmSupplierID";


        if ($Type == 0) {
            $BillerType = $RequestBody['BillerType'];
            $BillDate = $RequestBody['BillDate'];
            $PsId = $RequestBody['PsId'];
            // $SupplierID = $RequestBody['SupplierID'];
            // $AgentID = $RequestBody['AgentID'];
            $SearchAgentID = $RequestBody['AgentID']; //uma
            $SearchSupplierID = $RequestBody['SupplierID']; //uma


            if ($PsId != '') {
                $Query = " and RLD.ArrivalStationID='$PsId'";
            }

            if ($SearchSupplierID != '') {
                if( $BillDate >='2026-04-01' ){
                    /*$sqlSupplier = "SELECT GROUP_CONCAT(ID) as SupplierID FROM `RmSuppliers` WHERE PanNumber =(SELECT PanNumber FROM `RmSuppliers`
                    WHERE ID=$SearchSupplierID)";
                    $stmt = $this->conn->prepare($sqlSupplier);
                    $stmt->execute();
                    $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
                    $SearchSupplierID = $dataRow['SupplierID'];*/
                }
                $Query .= " and RLD.RmSupplierID in ($SearchSupplierID)";
            }

            if ($SearchAgentID != '') {
                $Query .= " and RLD.AgentID='$SearchAgentID'";
            }

            $sqlRmArrivalQuery = "SELECT  $SqlFields FROM  RmBillDetails as RBD  
            left join RmArrivalDetatils as RLD  on RBD.ArrivalID=RLD.RmArrivalID
            left join RmPurchaseStations as RPS on RLD.ArrivalStationID=RPS.PurchaseStationID 
            left join RmPurchaseCategory as RPC on RLD.PurchaseCategoryID=RPC.CatID
              where BillDate  <= '$BillDate' and RBD.PaymentStatus='0' and RLD.LotStatus='1' $Query 
              order by ArrivalDate,ArrivalNumber desc";
        } else if ($Type = 1) {
            $ArrivalID = $RequestBody['ArrivalID'];
            $SearchAgentID = $RequestBody['AgentID']; //uma
            $SearchSupplierID = $RequestBody['SupplierID']; //uma
            $BillerType = $RequestBody['BillerType']; //uma

            $Query = '';
            $sqlRmArrivalQuery = "SELECT  $SqlFields FROM  RmBillDetails as RBD  
            left join RmArrivalDetatils as RLD  on RBD.ArrivalID=RLD.RmArrivalID
            left join RmPurchaseStations as RPS on RLD.ArrivalStationID=RPS.PurchaseStationID 
            left join RmPurchaseCategory as RPC on RLD.PurchaseCategoryID=RPC.CatID
            where RBD.ArrivalID in ($ArrivalID) and RBD.PaymentStatus='0' $Query 
            order by ArrivalDate,ArrivalNumber desc";
          
        }
        // echo  $sqlRmArrivalQuery;exit;

        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();

        //Will execute only for payment popup only 
        $checkTDSFlag = $RequestBody['IsCalcTDS'];
        if($checkTDSFlag!='' && $checkTDSFlag=='true'){            
            $ArrivalID = $RequestBody['ArrivalID'];
            $ArrivalIDArray = explode(",", $ArrivalID);
            foreach($ArrivalIDArray as $ArrivalID){

                    $TdsCalEndDate = new DateTime('2024-04-01');
                    $tdsLimit = 5000000;
                    if ($Billdate < $TdsCalEndDate) {
                        $tdsLimit = 0;
                    }
                    $AryupdateBillDetails = $this->utilController->updateBillTable($ArrivalID, $tdsLimit,$ClientID);
                    $AryupdateArrivalBillAmountandTds = $this->utilController->updateArrivalBillAmountandTds($ArrivalID);                
            }
        }

        $i = 0;
        $Data = array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayBookingList['SNO'] = ++$i;
            $ArrayBookingList['RmArrivalID'] = $dataRow['RmArrivalID'];
            $ArrayBookingList['ArrivalNumber'] = $dataRow['ArrivalNumber'];
            $ArrayBookingList['ArrivalDate'] = $dataRow['ArrivalDate'];
            $ArrayBookingList['BillDate'] = $this->utilController->getFormatedDateEmpty($dataRow['BillDate']);
            $ArrayBookingList['PurchaseType'] = $dataRow['PurchaseType'];
            $GraderID = $dataRow['GraderID'];
            $ArrayBookingList['Grader'] = $this->utilController->getUserName($GraderID);
            $AgentID = $dataRow['AgentID'];
            $ArrayBookingList['Agent'] = $this->utilController->getUserName($AgentID);
            $ArrivalID = $dataRow['RmArrivalID'];
            $ArrayBookingList['BillStatus'] = $dataRow['BillStatus'];

            $BillDetails = $this->getBillDetails($ArrivalID,false);
            //var_dump( $BillDetails);
            $BillPaidDetails = $this->getBillPaidAmountDetails($ArrivalID,$BillerType);
            $ArrayBookingList['BillAmount'] = $BillDetails['BillAmount'];
           // $ArrayBookingList['TDS'] = $BillDetails['TotalTDS'];
           // $ArrayBookingList['BillAmt'] = $BillDetails['TotalBillAmountExTDS'];
            $ArrayBookingList['PaidAmount'] = $BillPaidDetails['PaidAmount'];
            // $ArrayBookingList['BalanceAmount'] =sprintf('%0.2f', round($BillDetails['TotalBillAmountExTDS']-$BillPaidDetails['PaidAmount'],2));
           /****uma*** */
            if ($SearchAgentID != 0 || $SearchAgentID != '') {
                $ArrayBookingList['BalanceAmount'] = sprintf('%0.2f', round($BillDetails['HarvestBillAmountExTDS'] - ($BillDetails['HarvestCommision'] + $BillPaidDetails['PaidAmount']), 2));
                $ArrayBookingList['TDS'] = $BillDetails['HarvestCommision'];
                $ArrayBookingList['HarvestCommision'] = $BillDetails['HarvestCommision'];
                $ArrayBookingList['BillAmt'] = $BillDetails['HarvestBillAmountExTDS']-$ArrayBookingList['HarvestCommision'];
                 $ArrayBookingList['HarvestBillAmount'] = $BillDetails['HarvestBillAmount'];
            } else if ($SearchSupplierID != 0 || $SearchSupplierID != '') {
               
                if ($AgentID == 0 || $AgentID == '') { 
                    $TotalBillAmountExTDS = $BillDetails['TotalBillAmountExTDS']; 
                }else{
                    $TotalBillAmountExTDS=$BillDetails['TotalBillAmountExTDS']-$BillDetails['HarvestBillAmountExTDS'];
                   
                }

                $ArrayBookingList['BillAmt'] = $TotalBillAmountExTDS ;

                $ArrayBookingList['BalanceAmount'] = sprintf('%0.2f', round($TotalBillAmountExTDS  - $BillPaidDetails['PaidAmount'], 2));
                $ArrayBookingList['TDS'] = $BillDetails['TotalTDS'];
               
            }
            $ArrayBookingList['BillAmountInTDS'] = $BillDetails['BillAmountInTDS'];
            /****uma*** */
            $ArySupplier = $this->utilController->getRMSuppliers($dataRow['RmSupplierID']);
            $ArrayBookingList['SupplierName'] = isset($ArySupplier['SupplierName']) ? $ArySupplier['SupplierName'] : null;
            if ($ArrayBookingList['BalanceAmount'] > 0) {

                $Data[] = $ArrayBookingList;
            }

        }
        return $Data;

    }



    public function getBillDetails($ArrivalID,$IsCalcTds)
    {

        $AryBillDetails = $this->utilController->getBillPriceDetails($ArrivalID,$IsCalcTds);

     //  echo $AryBillDetails['TotalBillAmount'];
        // var_dump( $AryBillDetails);
        $data['BillAmount'] = sprintf('%0.2f', round($AryBillDetails['TotalBillAmount'], 2));
        $data['TotalTDS'] = sprintf('%0.2f', round($AryBillDetails['TotalTDS'], 2));
        
        $data['TotalBillAmountExTDS'] = sprintf('%0.2f', round($AryBillDetails['TotalBillAmountExTDS'], 2));
        $data['HarvestBillAmount'] = $AryBillDetails['HarvestBillAmount']; //uma
        $data['HarvestBillAmountExTDS'] = $AryBillDetails['HarvestBillAmountExTDS']; //uma
        $data['HarvestTDS'] = sprintf('%0.2f', round($AryBillDetails['HarvestTDS'], 2));//uma
        $data['HarvestCommision'] = sprintf('%0.2f', round($AryBillDetails['HarvestCommision'], 2));
        $data['BillAmountInTDS'] = sprintf('%0.2f', round($AryBillDetails['BillAmount'], 2));
        // $data['BillAmount']='NA';

        /*
         $sqlQuery = "SELECT HarvestCommission FROM `RmArrivalDetatils` WHERE RmArrivalID='$ArrivalID'";
         $stmt = $this->conn->prepare($sqlQuery);
         $stmt->execute();
         $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
         $HarvestCommission=$dataRow['HarvestCommission'];


         $sqlQuery = "SELECT sum(Weight) as Weight,sum(Weight*Rate) as BillAmount FROM `RmBillBreakup` WHERE ArrivalID='$ArrivalID'";
         $stmt = $this->conn->prepare($sqlQuery);
         $stmt->execute();
         
         if( $stmt->rowCount() ==0){
             $data['BillAmount']='NA';
       
         }else{
             $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
             $HarvestCommission=($dataRow['Weight']*$HarvestCommission);
             $data['BillAmount']=sprintf('%0.2f', round($dataRow['BillAmount']+ $HarvestCommission,2));
        }	

        */
        return $data;
    }


    public function getBillPaidAmountDetails($ArrivalID,$BillerType)
    {
        $sqlQuery = "SELECT sum(Amount) as PaidAmount FROM `RmPaymentBreakup` WHERE ArrivalID='$ArrivalID' and BillerType='$BillerType'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $data['PaidAmount'] = '0';

        } else {
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['PaidAmount'] = $dataRow['PaidAmount'] ? $dataRow['PaidAmount'] : 0;
        }
        return $data;
    }

}
