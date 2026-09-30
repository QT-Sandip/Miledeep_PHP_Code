<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include_once '../Global/Util.php';
include_once 'UtilController.php';
include_once __DIR__ . '/AuditLogger.php';

class RmPaymentSummaryReportController
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
    public function GetRmPaymentSummaryReportDetails($RequestBody)
    {

        //  $ArrivalID = $RequestBody['ArrivalID'];
        $PaymentSummaryData = $this->getPaymentSummaryReportData($RequestBody);



        $Data['Error'] = null;
        $Data['Msg'] = ' Success';

        $Data['Data'] = $PaymentSummaryData;

        return $Data;
    }


    public function postDeletePayment($RequestBody)
    {
        $PaymentID = $RequestBody['PaymentID'];
        $BillerType = $RequestBody['BillerType'];
        $Plant = $RequestBody['Plant'];
        $PaymentMode = $RequestBody['PaymentMode'];
        $CenterID = $RequestBody['CenterID'];

        $StartDate = $RequestBody['StartDate'];
        $EndDate = $RequestBody['EndDate'];
        $BillerType = $RequestBody['BillerType'];
        $SearchAgentID = $RequestBody['AgentID']; //uma
        $SearchSupplierID = $RequestBody['SupplierID']; //uma

        $existingPaymentData = $this->auditLogger->fetchRowById('RmPaymentDetails', 'PaymentID', $PaymentID);
        $existingPaymentBreakups = $this->auditLogger->fetchRowsByConditions('RmPaymentBreakup', ['PaymentID' => $PaymentID]);
        $sqlPaymentDelete = "DELETE FROM `RmPaymentDetails`  WHERE PaymentID='$PaymentID' and BillerType='$BillerType'";
        $stmtPaymentDelete = $this->conn->prepare($sqlPaymentDelete);
        $stmtPaymentDelete->execute();
        if ($stmtPaymentDelete && $existingPaymentData != null) {
            $this->auditLogger->logDelete('RmPaymentDetails', $PaymentID, $existingPaymentData, $RequestBody, [
                'ReferenceNo' => isset($existingPaymentData['PaymentRefNo']) ? $existingPaymentData['PaymentRefNo'] : (string) $PaymentID,
                'Remarks' => 'Payment deleted from summary report',
            ]);
        }



        if ($stmtPaymentDelete) {

            $sqlArrivalId = " SELECT GROUP_CONCAT(ArrivalID) as ArrivalIDs FROM `RmPaymentBreakup` WHERE PaymentID='$PaymentID'";
            $stmtArrivalId = $this->conn->prepare($sqlArrivalId);
            $stmtArrivalId->execute();
            $dataArrivalId = $stmtArrivalId->fetch(PDO::FETCH_ASSOC);
            $ArrivalIDs = $dataArrivalId['ArrivalIDs'];

            $sqlQuery = "DELETE FROM `RmPaymentBreakup` WHERE  PaymentID='$PaymentID' and BillerType='$BillerType'";
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();
            if ($stmt) {
                foreach ($existingPaymentBreakups as $paymentBreakup) {
                    $this->auditLogger->logDelete('RmPaymentBreakup', $paymentBreakup['PaymentBreakUpID'], $paymentBreakup, $RequestBody, [
                        'ReferenceNo' => $PaymentID,
                        'Remarks' => 'Payment breakup deleted from summary report',
                    ]);
                }
            }

            if ($stmt) {
                $sqlPaymentStatus = "UPDATE `RmBillDetails` SET `PaymentStatus`='0' WHERE `ArrivalID` in ($ArrivalIDs)";
                $stmtPaymentStatus = $this->conn->prepare($sqlPaymentStatus);
                $stmtPaymentStatus->execute();
            }
            $PaymentSummaryData = $this->getPaymentSummaryReportData($RequestBody);

            $Data = array('Error' => null, "Msg" => "Deleted Successfully");
        } else {
            $Data = array('Error' => null, "Msg" => "Unable to Delete");
        }

        $Data['Data'] = $PaymentSummaryData;
        return $Data;
    }

    function getPaymentSummaryReportData($RequestBody)
    {
        $Plant = $RequestBody['Plant'];
        $PaymentMode = $RequestBody['PaymentMode'];
        $CenterID = $RequestBody['CenterID'];

        $StartDate = $RequestBody['StartDate'];
        $EndDate = $RequestBody['EndDate'];
        $BillerType = $RequestBody['BillerType'];
        $SearchAgentID = $RequestBody['AgentID']; //uma
        $SearchSupplierID = $RequestBody['SupplierID']; //uma



        $DateFilter = "  Date between '$StartDate' and '$EndDate'";

        $Query = '';



        if ($Plant != '') {
            $Query .= " and ProcessingStationID='$Plant'";
        }

        if ($PaymentMode != '') {
            $Query .= " and PaymentMode='$PaymentMode'";
        }
        if ($CenterID != '') {
            $Query .= " and ArrivalStationID='$CenterID'";
        }
        if ($SearchSupplierID != '') {
            $Query .= " and RmSupplierID='$SearchSupplierID'";
        }

        if ($SearchAgentID != '') {
            $Query .= " and RAD.AgentID='$SearchAgentID'";
        }


        $SqlFields = "group_concat(ArrivalID) as ArrivalID,RPD.PaymentID,ArrivalNumber,BillDate,Date as PayDate,FinancierName,
        PaymentRefNo,PaymentMode,RPD.BillerType,
        CheckNo,RmSupplierID,RAD.AgentID";

        $sqlRmArrivalQuery = "SELECT  group_concat(ArrivalID) as ArrivalIDs,PaymentRefNo FROM `RmPaymentDetails` as RPD 
            left join RmPaymentBreakup as RPB on RPB.PaymentID=RPD.PaymentID left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RPB.ArrivalID 
            left join RmSuppliers as RS on RS.ID=RAD.RmSupplierID 
            WHERE $DateFilter and RPD.BillerType ='$BillerType' $Query and LotStatus='1'   GROUP BY 	PaymentRefNo order by RPB.PaymentID,BillDate asc";
        $stmtPaymentRef = $this->conn->prepare($sqlRmArrivalQuery);
        $stmtPaymentRef->execute();
        $TotalAmount = 0;
        $i = 0;
        while ($dataPaymentRefNoRow = $stmtPaymentRef->fetch(PDO::FETCH_ASSOC)) {
            $PaymentRefNo = $dataPaymentRefNoRow['PaymentRefNo'];
            $ArrivalIDs = $dataPaymentRefNoRow['ArrivalIDs'];

            $sqlRmArrivalQuery = "SELECT  $SqlFields FROM `RmPaymentDetails` as RPD 
               left join RmPaymentBreakup as RPB on RPB.PaymentID=RPD.PaymentID 
               left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RPB.ArrivalID
                left join RmSuppliers as RS on RS.ID=RAD.RmSupplierID 
               WHERE $DateFilter $Query  and PaymentRefNo='$PaymentRefNo' and ArrivalID in ($ArrivalIDs) and RPD.BillerType ='$BillerType'
                group by Date,PaymentRefNo order by RPB.PaymentID,BillDate asc";

            $stmt = $this->conn->prepare($sqlRmArrivalQuery);
            $stmt->execute();



            $Data = array();
            while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ArrivalID = $dataRow['ArrivalID'];
                $PaymentMode = $dataRow['PaymentMode'];
                $PaymentID = $dataRow['PaymentID'];
                $BillerTypee = $dataRow['BillerType'];
                $AgentID = $dataRow['AgentID'];
                $CheckNo = $dataRow['CheckNo'];
                $PayDate = $dataRow['PayDate'];
                $PaymentMode = $dataRow['PaymentMode'];

                $PrintLink = $this->getActionUrls($PaymentMode);

                //  $sqlBreakup = "SELECT Sum(Amount) as Amount FROM `RmPaymentBreakup` WHERE ArrivalID=$ArrivalID and PaymentID='$PaymentID'"; 
                // $sqlBreakup = "SELECT Sum(Amount) as Amount FROM `RmPaymentBreakup` WHERE ArrivalID in ($ArrivalID) and BillerType ='$BillerType' ";//commented on 13-12-2023
                $sqlBreakup = "SELECT sum(RPB.Amount) as Amount, RPD.Amount as AmountData  FROM RmPaymentBreakup as RPB 
                left join  `RmPaymentDetails` as RPD on RPD.PaymentID=RPB.PaymentID
                 WHERE CheckNo='$CheckNo'  and PaymentRefNo='$PaymentRefNo'  and PaymentMode='$PaymentMode' and Date='$PayDate' and RPB.BillerType='$BillerTypee' and RPB.ArrivalID in ($ArrivalID) ";
                $stmtBreakup = $this->conn->prepare($sqlBreakup);
                $stmtBreakup->execute();
                $dataBreakup = $stmtBreakup->fetch(PDO::FETCH_ASSOC);

                $PayDate = $dataRow['PayDate'];
                $PaymentRefNo = $dataRow['PaymentRefNo'];
                $ArrayBookingList['SNO'] = ++$i;
                //$ArrayBookingList['BillDate'] = $this->utilController->getFormatedDateEmpty($dataRow['BillDate']);
                $ArrayBookingList['PayDate'] = $this->utilController->getFormatedDateEmpty($dataRow['PayDate']);
                //$ArrayBookingList['BillNumber'] = $dataRow['ArrivalNumber'];
                $ArrayBookingList['Supplier'] = $dataRow['FinancierName'];
                // $ArrayBookingList['AgentName'] = $this->utilController->getUserName($AgentID);
                $ArrayBookingList['AgentName'] = $this->utilController->getAgentName($AgentID)['UserName'];
                $ArrayBookingList['SupplierID'] = $dataRow['RmSupplierID'];
                $ArrayBookingList['Voucher'] = $dataRow['PaymentRefNo'];
                $ArrayBookingList['ChequeNo'] = $dataRow['CheckNo'];
                $ArrayBookingList['Amount'] = number_format($dataBreakup['Amount'], 2);
                $ArrayBookingList['PrintLink'] = $PrintLink;
                $ArrayBookingList['BillerType'] = $BillerTypee;
                $ArrayBookingList['PaymentID'] = $PaymentID;

                $TotalAmount += $dataBreakup['Amount'];
                // $Data[] = $ArrayBookingList;
                $ArrayReportData[] = $ArrayBookingList;
            }
        }







        $ArrayTotals['TotalAmount'] = number_format($TotalAmount, 2);


        $Data['PaymentDateWiseReportData'] = $ArrayReportData;
        $Data['Totals'] = $ArrayTotals;
        return $Data;
    }

    function getActionUrls($PaymentMode)
    {

        // if ($PaymentMode == 'C') {
        //     $PrintLink = 'https://camaroo.in/v3/Purchase/Reports/T1/cash-payment-voucher';

        // } else if ($PaymentMode == 'J') {
        //     $PrintLink = 'https://camaroo.in/v3/Purchase/Reports/T1/journal-payment-voucher';
        // } else if ($PaymentMode == 'B') {
        //     $PrintLink = 'https://camaroo.in/v3/Purchase/Reports/T1/bank-payment-voucher';
        // } 

        if ($PaymentMode == 'C') {
            $PrintLink = 'Reports/T1/cash-payment-voucher';
        } else if ($PaymentMode == 'J') {
            $PrintLink = 'Reports/T1/journal-payment-voucher';
        } else if ($PaymentMode == 'B') {
            $PrintLink = 'Reports/T1/bank-payment-voucher';
        }

        return $PrintLink;
    }
}
