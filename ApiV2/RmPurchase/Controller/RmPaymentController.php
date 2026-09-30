<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/AuditLogger.php';

class RmPaymentController
{
    private $conn;
    private $db_table = "ScrmApplicants";
    private $utilController;
    private $auditLogger;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new AuditLogger($this->conn, 'Purchase');
    }

    public function getRmBillDetails($RequestBody)
    {
        $PaymentMode = $RequestBody['PaymentMode'];
        $CenterID = $RequestBody['CenterID'];
        $PaymentDate = $RequestBody['PaymentDate'];
        $VoucherData = $this->utilController->getFormatedVoucherNumber($PaymentDate, $CenterID, $PaymentMode);



        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['Data'] = $VoucherData;
        // $Data['BreakUpData'] = $BreakUpData;
        // $Data['GrandTotal'] = $GrandTotal;

        return $Data;
    }

    public function postRmPaymentDetails($RequestBody)
    {

        $PaymentDate = $RequestBody['PaymentDate'];
        $PaymentMode = $RequestBody['PaymentMode'];
        $PaymentRefNo = $RequestBody['Vocher'];
        $CheckNo = $RequestBody['CheckNo'];
        $Remarks = $RequestBody['Remarks'];
        $Amount = $RequestBody['Amount'];
        $BillerType = $RequestBody['BillerType'];
        $AutoVoucher = $RequestBody['AutoVoucherNo'];
        $StationID = $RequestBody['CenterID'];


        //if ($ArrivalID == '') {




        //}


        $AryCurrentDate = getCurrentDateTime();

        $CreatedDateTime = $AryCurrentDate['TimeStamp'];


        $stmt = false;

        $PostedUserID = 1;

       $DateFilter= $this->utilController->getFinDateFilter($PaymentDate);

        $SqlCheckupBrkUp = "select PaymentID   from RmPaymentDetails where PaymentRefNo='$PaymentRefNo' and Date $DateFilter";
        $stmtCheck = $this->conn->prepare($SqlCheckupBrkUp);
        $stmtCheck->execute();
        $dataRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        $PaymentID = isset($dataRow['PaymentID']) ? $dataRow['PaymentID'] : null;


        if ($stmtCheck->rowCount() == 0 && $PaymentRefNo != '') {
            $sqlQuery = "INSERT INTO `RmPaymentDetails`
            (`BillerType`,`StationID`, `PaymentMode`, `PaymentRefNo`,`VoucherNo`,`CheckNo`, `Amount`,`Date`,`PostedDate`,`Remarks`,`PostedUserID`)
            VALUES ('$BillerType','$StationID','$PaymentMode','$PaymentRefNo','$AutoVoucher','$CheckNo','$Amount','$PaymentDate','$CreatedDateTime','$Remarks','$PostedUserID')";

            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();
            $PaymentID = $this->conn->lastInsertId();
            if ($stmt) {
                $newPaymentData = $this->auditLogger->fetchRowById('RmPaymentDetails', 'PaymentID', $PaymentID);
                $this->auditLogger->logInsert('RmPaymentDetails', $PaymentID, $newPaymentData, $RequestBody, [
                    'ReferenceNo' => $PaymentRefNo,
                    'Remarks' => 'Payment created',
                ]);
            }


        } else if ($stmtCheck->rowCount() > 0) {

         /*   $SqlRmBillDetails = "update `RmPaymentDetails` set	
           
            `BillerType`='$BillerType',  
            `StationID`='$StationID',
            `PaymentMode`='$PaymentMode',
            `CheckNo`='$CheckNo',
            `Amount`='$Amount',
            `Remarks`='$Remarks',
            `Date`='$PaymentDate' where `PaymentRefNo`='$PaymentRefNo'";
            $stmt = $this->conn->prepare($SqlRmBillDetails);
            $stmt->execute();
            */
            $this->Error="Vocher Number Already Exist";
        }else if($PaymentRefNo != ''){
            $this->Error="Vocher Number Not Found ";
        }
    // echo   $SqlRmBillDetails; exit;

        if ($stmt) {
            $RequestBody['PaymentID'] = $PaymentID;
            return $this->postPaymentBreakUp($PaymentID, $RequestBody);
        } else {
            return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
        }
    }

    function postPaymentBreakUp($PaymentID, $RequestBody)
    {
       // echo $PaymentID; exit;
        $AryError = NULL;
        $PaymentBreakUp = $RequestBody['PaymentBreakUp'];


        foreach ($PaymentBreakUp as $Item) {
            $BreakUpID = isset($Item['PaymentBreakUpID']) ? $Item['PaymentBreakUpID'] : null;

            $ArrivalID = $Item['ArrivalID']; 
            $Amount = $Item['Amount']; 
            $BillerType = $Item['BillerType'];

            if ($BreakUpID != null) {

                $SqlRmBillBreakUp = "update  `RmPaymentBreakup` set
	                         `ArrivalID`='$ArrivalID',
	                         `PaymentID`='$PaymentID',
	                         `Amount`='$Amount' where PaymentBreakUpID ='$BreakUpID' ";
            } else {

                $SqlRmBillBreakUp = "INSERT INTO `RmPaymentBreakup`(`BillerType`,`ArrivalID`,`PaymentID`, `Amount`) 
                VALUES ('$BillerType','$ArrivalID','$PaymentID','$Amount')";
            }

          //echo  $SqlRmBillBreakUp; exit;
            $BillAmount = $this->getBillDetails($ArrivalID); 
              $PaidAmount = $this->getBillPaidAmountDetails($ArrivalID);
               $BalanceValue=round(($BillAmount - $PaidAmount),2); 

           // if ($BillAmount - $PaidAmount >= $Amount) {
            if ($BalanceValue >= $Amount) {
               //echo $SqlRmBillBreakUp; exit;
                $StmtRmBillBreakUp = $this->conn->prepare($SqlRmBillBreakUp);
                $StmtRmBillBreakUp->execute();
                if ($StmtRmBillBreakUp) {
                    if ($BreakUpID != null) {
                        $updatedBreakup = $this->auditLogger->fetchRowById('RmPaymentBreakup', 'PaymentBreakUpID', $BreakUpID);
                        $this->auditLogger->logUpdate('RmPaymentBreakup', $BreakUpID, null, $updatedBreakup, $RequestBody, [
                            'ReferenceNo' => $PaymentID,
                            'Remarks' => 'Payment breakup updated',
                        ]);
                    } else {
                        $newBreakUpId = $this->conn->lastInsertId();
                        $newBreakup = $this->auditLogger->fetchRowById('RmPaymentBreakup', 'PaymentBreakUpID', $newBreakUpId);
                        $this->auditLogger->logInsert('RmPaymentBreakup', $newBreakUpId, $newBreakup, $RequestBody, [
                            'ReferenceNo' => $PaymentID,
                            'Remarks' => 'Payment breakup created',
                        ]);
                    }
                }
            } else {
                $BillNo = $this->getBillNo($ArrivalID);
                $ExcessAmount = ($PaidAmount + $Amount) - $BillAmount;
                $AryError[] = $ExcessAmount . '- Excess Amount for Bill No ' . $BillNo;
            }
        }

        if ($AryError != null && count($AryError) > 0) {

            $this->Error = implode('<br>', $AryError);
            $Data = array('Error' => $this->Error, "Msg" => "Some Bill Have Excess Amount");

        } else {
            $Data = array('Error' => null, "Msg" => "Payment Saved Successfully");
        }

        return $Data;
    }



    public function getBillDetails($ArrivalID)
    {

        $AryBillDetails = $this->utilController->getBillPriceDetails($ArrivalID);
        $data['BillAmount'] = sprintf('%0.2f', round($AryBillDetails['TotalBillAmount'], 2));
        $data['TotalTDS'] = sprintf('%0.2f', round($AryBillDetails['TotalTDS'], 2));
        $data['BillAmount'] = sprintf('%0.2f', round($AryBillDetails['TotalBillAmountExTDS'], 2));


        /*    $sqlQuery = "SELECT sum(Weight*Rate) as BillAmount FROM `RmBillBreakup` WHERE ArrivalID='$ArrivalID'";
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                $data['BillAmount'] = 'NA';

            } else {
                $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
                $data['BillAmount'] = $dataRow['BillAmount'];
            }*/
        return $data['BillAmount'];
    }


    public function getBillPaidAmountDetails($ArrivalID)
    {
        $sqlQuery = "SELECT sum(Amount) as PaidAmount FROM `RmPaymentBreakup` WHERE ArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $data['PaidAmount'] = '0';

        } else {
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['PaidAmount'] = $dataRow['PaidAmount'] ? $dataRow['PaidAmount'] : 0;
        }
        return $data['PaidAmount'];
    }


    public function getBillNo($ArrivalID)
    {
        $sqlQuery = "SELECT ArrivalNumber FROM `RmArrivalDetatils` WHERE RmArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $data['ArrivalNumber'] = 'NA';

        } else {
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['ArrivalNumber'] = $dataRow['ArrivalNumber'] ? $dataRow['ArrivalNumber'] : 'NA';
        }
        return $data['ArrivalNumber'];
    }


}
?>
