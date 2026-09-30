<?php

include_once '../Global/Util.php';
include_once 'UtilController.php';

class RmPaymentDateWiseReportController
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
    public function GetRmPaymentDateWiseReportDetails($RequestBody)
    {
        $Plant = $RequestBody['Plant'];
        $PaymentMode = $RequestBody['PaymentMode'];
        $CenterID = $RequestBody['CenterID'];
        $SupplierID = $RequestBody['SupplierID'];
        $ReqAgentID = $RequestBody['AgentID'];
        $StartDate = $RequestBody['StartDate'];
        $EndDate = $RequestBody['EndDate'];
        $BillerType = $RequestBody['BillerType'];
        $DateFilter = "  Date between '$StartDate' and '$EndDate'";
        $Query = '';
        $PaymentQuery = '';

        $AgentJoin = '';
        $SqlFields = "ArrivalID,RPD.PaymentID,ArrivalNumber,BillDate,Date as PayDate,RS.FinancierName,PaymentRefNo,PaymentMode,CheckNo,RmSupplierID";

        if ($Plant != '') {
            $Query .= " and ProcessingStationID='$Plant'";
        }

        if ($PaymentMode != '') {
            $Query .= " and PaymentMode='$PaymentMode'";
        }
        if ($CenterID != '') {
            $Query .= " and ArrivalStationID='$CenterID'";
        }
        if ($BillerType == '2') {
            $PaymentQuery = " AND RPB.BillerType='2'";
        }

        if ($SupplierID != '') {
            $Query .= " and RAD.RmSupplierID='$SupplierID'";
        }
        // Common values used in both places
        $NeedAgentFields = false;

        if ($BillerType == '1') {
            $PaymentQuery = " AND RPB.BillerType='1'";
            $NeedAgentFields = true;
        }

        if ($ReqAgentID != '') {
            $Query .= " AND RAD.AgentID='$ReqAgentID'";
            $NeedAgentFields = true;
        }

        // Add only once
        if ($NeedAgentFields) {
            $AgentJoin = 'LEFT JOIN RmUsersList AS RA ON RA.ID = RAD.AgentID';
            $SqlFields .= ", RA.UserName AS AgentName";
        }

        $sqlRmArrivalQuery = "SELECT  ArrivalID,ArrivalNumber,BillDate,Date as PayDate,RS.FinancierName,PaymentRefNo,PaymentMode FROM `RmPaymentDetails` as RPD 
            left join RmPaymentBreakup as RPB on RPB.PaymentID=RPD.PaymentID 
            left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RPB.ArrivalID 
            left join RmSuppliers as RS on RS.ID=RAD.RmSupplierID 
             WHERE $DateFilter and RAD.LotStatus='1' $Query  $PaymentQuery GROUP BY 	PaymentRefNo order by RPB.PaymentID,BillDate,ArrivalNumber   asc";
        $stmtPaymentRef = $this->conn->prepare($sqlRmArrivalQuery);
        $stmtPaymentRef->execute();
        $TotalAmount = 0;
        while ($dataPaymentRefNoRow = $stmtPaymentRef->fetch(PDO::FETCH_ASSOC)) {
            $PaymentRefNo = $dataPaymentRefNoRow['PaymentRefNo'];
            $sqlRmArrivalQuery = "SELECT  $SqlFields FROM `RmPaymentDetails` as RPD 
               left join RmPaymentBreakup as RPB on RPB.PaymentID=RPD.PaymentID 
               left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RPB.ArrivalID
                left join RmSuppliers as RS on RS.ID=RAD.RmSupplierID 
                $AgentJoin
               WHERE $DateFilter and RAD.LotStatus='1' $Query  and PaymentRefNo='$PaymentRefNo' group by ArrivalID,ArrivalNumber,BillDate,Date,FinancierName,PaymentRefNo,PaymentMode order by RPB.PaymentID,BillDate asc";
            $stmt = $this->conn->prepare($sqlRmArrivalQuery);
            $stmt->execute();

            $i = 0;

            $Data = array();
            while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ArrivalID = $dataRow['ArrivalID'];
                $PaymentMode = $dataRow['PaymentMode'];
                $PaymentID = $dataRow['PaymentID'];
                $PrintLink = $this->getActionUrls($PaymentMode);

                $sqlBreakup = "SELECT Sum(Amount) as Amount FROM `RmPaymentBreakup` AS RPB WHERE ArrivalID=$ArrivalID and PaymentID='$PaymentID' $PaymentQuery";
                $stmtBreakup = $this->conn->prepare($sqlBreakup);
                $stmtBreakup->execute();
                $dataBreakup = $stmtBreakup->fetch(PDO::FETCH_ASSOC);

                $PayDate = $dataRow['PayDate'];
                $PaymentRefNo = $dataRow['PaymentRefNo'];
                $ArrayBookingList['SNO'] = ++$i;
                $ArrayBookingList['BillDate'] = $this->utilController->getFormatedDateEmpty($dataRow['BillDate']);
                $ArrayBookingList['PayDate'] = $this->utilController->getFormatedDateEmpty($dataRow['PayDate']);
                $ArrayBookingList['BillNumber'] = $dataRow['ArrivalNumber'];
                $ArrayBookingList['Supplier'] = $dataRow['FinancierName'];
                $ArrayBookingList['Agent'] = $dataRow['AgentName'];
                $ArrayBookingList['SupplierID'] = $dataRow['RmSupplierID'];
                $ArrayBookingList['Voucher'] = $dataRow['PaymentRefNo'];
                $ArrayBookingList['ChequeNo'] = $dataRow['CheckNo'];
                $ArrayBookingList['Amount'] = number_format($dataBreakup['Amount'], 2);
                $ArrayBookingList['PrintLink'] = $PrintLink;

                $TotalAmount += $dataBreakup['Amount'];
                $ArrayReportData[] = $ArrayBookingList;


            }
            $sqlRmPaySubTotal = " SELECT * FROM `RmPaymentDetails` WHERE PaymentRefNo='$PaymentRefNo' and Date='$PayDate'";
            $stmtRmPaySubTotal = $this->conn->prepare($sqlRmPaySubTotal);
            $stmtRmPaySubTotal->execute();
            $dataRowRmPaySubTotal = $stmtRmPaySubTotal->fetch(PDO::FETCH_ASSOC);

            $subTotal = $dataRowRmPaySubTotal['Amount'];
            $ArrayBookingList = array();
            $ArrayBookingList['SNO'] = '';
            $ArrayBookingList['SubTotal'] = number_format($subTotal, 2);
            $ArrayBookingList['Voucher'] = $PaymentRefNo;
            $ArrayReportData[] = $ArrayBookingList;

        }
        $ArrayTotals['TotalAmount'] = number_format($TotalAmount, 2);
        $Data['PaymentDateWiseReportData'] = $ArrayReportData;
        $Data['Totals'] = $ArrayTotals;
        return $Data;
    }

    function getActionUrls($PaymentMode)
    {
        if ($PaymentMode == 'C') {
            $PrintLink = 'https://camaroo.in/v3/Purchase/Reports/T1/cash-payment-voucher';

        } else if ($PaymentMode == 'J') {
            $PrintLink = 'https://camaroo.in/v3/Purchase/Reports/T1/journal-payment-voucher';
        } else if ($PaymentMode == 'B') {
            $PrintLink = 'https://camaroo.in/v3/Purchase/Reports/T1/bank-payment-voucher';
        }

        return $PrintLink;

    }

}
?>