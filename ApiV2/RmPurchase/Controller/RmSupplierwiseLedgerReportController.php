<?php
include_once '../Global/Util.php';
include_once 'UtilController.php';
class RmSupplierwiseLedgerReportController
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
    public function GetRmSupplierWiseledgerReportDetails($RequestBody)
    {
        $Plant = $RequestBody['Plant'];
        $CenterID = $RequestBody['Center'];
        $StartDate = $RequestBody['StartDate'];
        $BillerType = $RequestBody['BillerType'];
        $EndDate = $RequestBody['EndDate'];
        $SearchAgentID = $RequestBody['AgentID']; //uma
        $SearchSupplierID = $RequestBody['SupplierID']; //uma
        $DateFilter = "  BillDate between '$StartDate' and '$EndDate'";
        $StartDateFilter = "  RAD.BillDate < '$StartDate'"; //uma

        $Query = '';

        if ($CenterID != '' && $SearchSupplierID != '' ) {
            $Query .= " and RAD.ArrivalStationID='$CenterID'";
            if( $StartDate >='2026-04-01' ){
                $sqlSupplier = "SELECT GROUP_CONCAT(ID) as SupplierID FROM `RmSuppliers` WHERE PanNumber =(SELECT PanNumber FROM `RmSuppliers`
                WHERE ID=$SearchSupplierID)";
                $stmt = $this->conn->prepare($sqlSupplier);
                $stmt->execute();
                $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
                $SearchSupplierID = $dataRow['SupplierID'];
            }
            $Query .= " and RAD.RmSupplierID in ($SearchSupplierID)";

        }
        if ($Plant != '') {
            $Query .= " and RAD.ProcessingStationID='$Plant'";
        }
        if ($CenterID == '' && $SearchSupplierID != '') {

            $sqlSupplier = "SELECT GROUP_CONCAT(ID) as SupplierID FROM `RmSuppliers` WHERE PanNumber =(SELECT PanNumber FROM `RmSuppliers` 
            WHERE ID=$SearchSupplierID)";
            $stmt = $this->conn->prepare($sqlSupplier);
            $stmt->execute();
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $SearchSupplierID = $dataRow['SupplierID'];
            $Query .= " and RAD.RmSupplierID in ($SearchSupplierID)";
        }

        if ($SearchAgentID != '') {
            $Query .= " and RAD.AgentID='$SearchAgentID'";
        }
        $sqlRmArrivalQuery = "SELECT GROUP_CONCAT(RmArrivalID) as ArrivalIDs FROM `RmArrivalDetatils` as RAD 
                              left join RmBillDetails as RBD on RAD.RmArrivalID=RBD.ArrivalID  WHERE $StartDateFilter   $Query and RAD.LotStatus='1'";
        $CreditAmount = 0;
        if ($BillerType == 2) {
            $sqlRmArrivalQuery = "SELECT GROUP_CONCAT(RmArrivalID) as ArrivalIDs FROM `RmArrivalDetatils` as RAD 
                              left join RmBillDetails as RBD on RAD.RmArrivalID=RBD.ArrivalID  WHERE $StartDateFilter   $Query and RAD.LotStatus='1' and AgentID=0";
            $ArySupOpeningBalance = $this->getSupplierOpeningBal($sqlRmArrivalQuery, $BillerType, $StartDate, true);

            $sqlRmArrivalQuery = "SELECT GROUP_CONCAT(RmArrivalID) as ArrivalIDs FROM `RmArrivalDetatils` as RAD 
                          left join RmBillDetails as RBD on RAD.RmArrivalID=RBD.ArrivalID  WHERE $StartDateFilter   $Query and RAD.LotStatus='1' and AgentID!=0";
            $AryAgentOpeningBalance = $this->getSupplierOpeningBal($sqlRmArrivalQuery, $BillerType, $StartDate, false);
            $CreditAmount = $ArySupOpeningBalance['CreditAmount'] + $AryAgentOpeningBalance['CreditAmount'];
        } else {
            $ArySupOpeningBalance = $this->getSupplierOpeningBal($sqlRmArrivalQuery, $BillerType, $StartDate, false);
            $CreditAmount = $ArySupOpeningBalance['CreditAmount'];
        }

        $DebitAmount = 0;

        $OpeningBalanceDate = date('Y-m-d', strtotime('-1 day', strtotime($StartDate))); //uma
        $ArrayBookingList['SNO'] = "1";
        $ArrayBookingList['Date'] = $this->utilController->getFormatedDateEmpty($OpeningBalanceDate);
        $ArrayBookingList['PaidThrough'] = '';
        $ArrayBookingList['Particulars'] = 'Opening Balance As on ' . $this->utilController->getFormatedDateEmpty($OpeningBalanceDate);
        $ArrayBookingList['Debit'] = ''; //uma
        $ArrayBookingList['TDSDeduction'] = '';
        $ArrayBookingList['SubTotal'] = '';
        $ArrayBookingList['Credit'] = sprintf('%0.2f', ($CreditAmount), 2);
        $ArrayReportData[] = $ArrayBookingList;
        $TotalOSAmount = $CreditAmount; // for Grand Total

        $AryDates = $this->utilController->createDateRangeArray($StartDate, $EndDate);
        foreach ($AryDates as $Date) {
            /*****************************************For Credit***************************** */
            $sqlRmArrivalQuery = "SELECT RmArrivalID as ArrivalID,ArrivalNumber,RAD.AgentID,BillDate FROM `RmArrivalDetatils` as RAD 
            left join RmBillDetails as RBD on RAD.RmArrivalID=RBD.ArrivalID  WHERE BillDate='$Date'   $Query and RAD.LotStatus='1'";
            $stmt = $this->conn->prepare($sqlRmArrivalQuery);
            $stmt->execute();
            $CreditRowCount = $stmt->rowCount();
            while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $CreditAmount = 0;
                $HarvestCommAmount = 0;
                $OsAMount = 0;
                $ArrivalIDs = $dataRow['ArrivalID'];
                $ArrivalNumber = $dataRow['ArrivalNumber'];
                $AgentID = $dataRow['AgentID'];
                $WeightDetails = $this->getBillAmount($ArrivalIDs, $BillerType, $AgentID);
                $BillAmountExcCommession = $WeightDetails['BillAmount'];
                $TDSDeduction = $WeightDetails['TDSDeduction'];
                $OsAMount = $WeightDetails['BillAmount'] - $WeightDetails['PaidAmount'];
                $HarvestCommAmount = $WeightDetails['HarvestCommAmount'];
                if ($BillerType == 2) {
                    // COMMENT IF BILL AMOUNT INCLUDES HARVESTcOMMESSIN
                    $BillAmount = $BillAmountExcCommession + $HarvestCommAmount;
                    if ($AgentID == 0) {
                        $CreditAmount = $OsAMount + $HarvestCommAmount;
                    } else {
                        $CreditAmount = $OsAMount;
                    }
                } else {
                    $CreditAmount = $OsAMount;
                }
                $TotalCreditAmout += $CreditAmount;
                $DebitAmount = 0;
                $ArrayBookingList['SNO'] = "1";
                $ArrayBookingList['Date'] = $this->utilController->getFormatedDateEmpty($dataRow['BillDate']);
                $ArrayBookingList['PaidThrough'] = '';
                $ArrayBookingList['Particulars'] = 'As Per Bought Note Number As on ' . $ArrivalNumber;
                $ArrayBookingList['Debit'] = '';
                $ArrayBookingList['SubTotal'] = '';
                $ArrayBookingList['TDSDeduction'] = sprintf('%0.2f', ($TDSDeduction), 2);
                $ArrayBookingList['Credit'] = sprintf('%0.2f', ($CreditAmount), 2);

                $ArrayReportData[] = $ArrayBookingList;
                $TotalTDSDeduction += $TDSDeduction;
            }

            /*****************************************For Credit******************************/

            $sqlDebitData = "SELECT GROUP_CONCAT(PaymentID) as PaymentIDs,CheckNo  FROM `RmPaymentDetails` 
            WHERE Date ='$Date'  and BillerType='$BillerType' GROUP by CheckNo";
            $stmtDebitData = $this->conn->prepare($sqlDebitData);
            $stmtDebitData->execute();
            while ($RowDebitData = $stmtDebitData->fetch(PDO::FETCH_ASSOC)) {
                $PaymentID = $RowDebitData['PaymentIDs'];
                $sqlRmArrivalQuery = "SELECT ArrivalID,ArrivalNumber,CheckNo,PaymentMode,RPB.Amount,Date as PayDate FROM `RmPaymentDetails` as RPD 
            left join RmPaymentBreakup as RPB on RPD.PaymentID=RPB.PaymentID 
            left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RPB.ArrivalID
            WHERE RPD.Date ='$Date'   $Query and RPD.PaymentID in ($PaymentID) and RPD.BillerType='$BillerType' and RAD.LotStatus='1'";

                $stmt = $this->conn->prepare($sqlRmArrivalQuery);
                $stmt->execute();
                $DebitRowCount = $stmt->rowCount();
                $PaymentType = '';
                while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $ArrayArrivalID[] = $dataRow['ArrivalID'];
                    $ArrivalIds = implode(',', $ArrayArrivalID);
                    $ArrivalNumber = $dataRow['ArrivalNumber'];
                    $CheckNo = $dataRow['CheckNo'];
                    $PaymentType = $dataRow['PaymentMode'];
                    $Amount = $dataRow['Amount'];
                    $TotalDebitAmount += $Amount;
                    $ArrayPaymentMode = $this->getPaymentMode($PaymentType, $CheckNo);
                    $PaymentMode = $ArrayPaymentMode['PaymentMode'];
                    $BankDescription = $ArrayPaymentMode['BankDescription'];

                    $ArrayBookingList['SNO'] = "1";
                    $ArrayBookingList['Date'] = $this->utilController->getFormatedDateEmpty($Date);
                    $ArrayBookingList['PaidThrough'] = $PaymentMode;
                    $ArrayBookingList['Particulars'] = 'Amount Paid By  ' . $PaymentMode . ' ' . $BankDescription . ' Towards Bill No ' . $ArrivalNumber;
                    $ArrayBookingList['Debit'] = $Amount;
                    $ArrayBookingList['TDSDeduction'] = '';
                    $ArrayBookingList['Credit'] = '';

                    $ArrayReportData[] = $ArrayBookingList;
                }
                if ($PaymentType == 'B') {
                    $ArraySubtotals = $this->getSubTotal($CheckNo, $Date, $BillerType, $ArrivalIds);
                    $ArrayBookingList['SNO'] = "";
                    $ArrayBookingList['Particulars'] = $ArraySubtotals['Particulars'];
                    $ArrayBookingList['SubTotal'] = $ArraySubtotals['SubTotal'];
                    $ArrayReportData[] = $ArrayBookingList;
                }
            }
        }

        $TotalOSAmount += $TotalCreditAmout; //uma

        $ArrayNoofEntries = $this->getNoofEntries($StartDate, $EndDate, $BillerType, $Query);
        $ArrayTotals['TotalOSAmount'] = sprintf('%0.2f', ($TotalOSAmount), 2);
        $ArrayTotals['TotalTDSDeduction'] = sprintf('%0.2f', ($TotalTDSDeduction), 2);
        $ArrayTotals['TotalDebitAmount'] = sprintf('%0.2f', ($TotalDebitAmount), 2);
        $ArrayTotals['EntryDescription'] = $ArrayNoofEntries['EntryDescription'];
        $ArrayTotals['BalanceAmount'] = sprintf('%0.2f', ($TotalOSAmount - $TotalDebitAmount - $TotalTDSDeduction), 2);

        $Data['PaymentCenterwiseReportData'] = $ArrayReportData;
        $Data['Totals'] = $ArrayTotals;
        return $Data;
    }
    function getSupplierOpeningBal($sqlRmArrivalQuery, $BillerType, $StartDate, $IsSupplier)
    {
        if ($IsSupplier) {
            $AgentID = 0;
        } else {
            $AgentID = 1;
        }
        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $ArrivalIDs = $dataRow['ArrivalIDs'];
        $WeightDetails = $this->getOpeningBalance($ArrivalIDs, $BillerType, $StartDate); //uma
        $aryBillDetails = $this->getBillAmount($ArrivalIDs, $BillerType, $AgentID);
        $BillAmountExcCommession = $WeightDetails['BillAmount'];
        $PaidAmount = $WeightDetails['PaidAmount'];
        $OsAMount = $WeightDetails['BillAmount'] - $WeightDetails['PaidAmount'] - $aryBillDetails['TDSDeduction'];
        $HarvestCommAmount = $WeightDetails['HarvestCommAmount'];
        if (!$IsSupplier) {
            $HarvestCommAmount = 0; // COMMENT IF BILL AMOUNT INCLUDES HARVESTcOMMESSIN
        }
        $BillAmount = $BillAmountExcCommession + $HarvestCommAmount;
        $CreditAmount = $OsAMount + $HarvestCommAmount;
        $data['CreditAmount'] = $CreditAmount;
        return $data;
    }
    public
        function getOpeningBalance(
        $ArrivalIDs,
        $BillerType,
        $StartDate
    ) {
        $SqlPaidAmount = "SELECT sum(RPB.Amount) as PaidAmount FROM `RmPaymentBreakup` as RPB left JOIN RmPaymentDetails as RPD ON
            RPB.PaymentID=RPD.PaymentID where ArrivalID in ($ArrivalIDs) and RPB.BillerType='$BillerType' 
            and Date < '$StartDate' "; //and '2022-05-31'
        $stmtPaidAmount = $this->conn->prepare($SqlPaidAmount);
        $stmtPaidAmount->execute();
        $dataPaidRow = $stmtPaidAmount->fetch(PDO::FETCH_ASSOC);
        $sqlQuery = "select (SELECT sum(BillAmount) FROM `RmBillDetails`  where ArrivalID in ($ArrivalIDs) )as BillAmount,
         (SELECT Sum(RPB.Amount) FROM `RmPaymentBreakup` as RPB where ArrivalID in ($ArrivalIDs) and RPB.BillerType='$BillerType' )as PaidAmount, 
         (SELECT sum(HarvestAmount) FROM `RmBillDetails`  where ArrivalID in ($ArrivalIDs)) as HarvestCommAmount
          ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $AryBillDetails = $this->getBillPriceDetails($ArrivalIDs); //prasad
        if ($BillerType == 1) {
            $data['BillAmount'] = $AryBillDetails['HarvestBillAmount'];
        } else if ($BillerType == 2) {
            $data['BillAmount'] = $dataRow['BillAmount'];
        }
        $data['HarvestCommAmount'] = $dataRow['HarvestCommAmount'];
        $data['PaidAmount'] = $dataPaidRow['PaidAmount'] ? $dataPaidRow['PaidAmount'] : "0";
        return $data;
    }
    public function getBillPriceDetails($ArrivalIDs)
    {
        $sqlBillAmountQuery = "SELECT 
            sum(BillAmount) as BillAmount,  
            sum(HarvestAmount) as HarvestAmount,
            sum(BillTds) as BillTds,
            sum(HarvestTds) as HarvestTds,
            sum(HarvestCommision) as HarvestCommision
           from RmBillDetails as RBB  
            WHERE RBB.ArrivalID in ($ArrivalIDs)";
        $stmtBillAmount = $this->conn->prepare($sqlBillAmountQuery);
        $stmtBillAmount->execute();
        $dataRowBillAmount = $stmtBillAmount->fetch(PDO::FETCH_ASSOC);
        $data['HarvestTDS'] = $dataRowBillAmount['HarvestTds'];
        $data['HarvestBillAmount'] = $dataRowBillAmount['HarvestAmount'];
        $data['BillTDS'] = $dataRowBillAmount['BillTds'];
        $data['TotalTDS'] = $dataRowBillAmount['HarvestTds'] + $dataRowBillAmount['BillTds'];
        $data['HarvestCommision'] = $dataRowBillAmount['HarvestCommision'];
        return $data;
    }
    public function getBillAmount($ArrivalIDs, $BillerType, $AgentID)
    {
        $sqlQuery = "select (SELECT sum(BillAmount) FROM `RmBillDetails`  where ArrivalID in ($ArrivalIDs) )as BillAmount,
         (SELECT sum(HarvestAmount) FROM `RmBillDetails`  where ArrivalID in ($ArrivalIDs)) as HarvestCommAmount";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $AryBillDetails = $this->getBillPriceDetails($ArrivalIDs); //Prasad
        $data['HarvestCommAmount'] = $dataRow['HarvestCommAmount'];        
        if ($BillerType == 1) {
            $data['TDSDeduction'] = $AryBillDetails['HarvestTDS'] + $AryBillDetails['HarvestCommision'];
            $data['BillAmount'] = $AryBillDetails['HarvestBillAmount'];
            $data['HarvestCommision'] = $AryBillDetails['HarvestCommision'];
        } else if ($BillerType == 2) {
            if ($AgentID == 0) {
                $data['TDSDeduction'] = $AryBillDetails['TotalTDS'];
            } else {
                $data['TDSDeduction'] = $AryBillDetails['BillTDS'];
            }
            $data['BillAmount'] = $dataRow['BillAmount'];
        }
        return $data;
    }
    function getPaymentMode($PaymentMode, $CheckNo)
    {
        if ($PaymentMode == 'B') {
            $PaymentMode = 'Bank';
            $BankDescription = '( Cheque No- ' . $CheckNo . ' )';
        } else if ($PaymentMode == 'C') {
            $PaymentMode = 'Cash';
        } else if ($PaymentMode == 'J') {
            $PaymentMode = 'Journal';
        }
        $Data['PaymentMode'] = $PaymentMode;
        $Data['BankDescription'] = $BankDescription;
        return $Data;
    }

    function getNoofEntries($StartDate, $EndDate, $BillerType, $Query)
    {
        $SqlPaymentmode = " SELECT distinct PaymentMode FROM `RmPaymentDetails`";
        $StmtPaymentmode = $this->conn->prepare($SqlPaymentmode);
        $StmtPaymentmode->execute();
        while ($RowPaymentmode = $StmtPaymentmode->fetch(PDO::FETCH_ASSOC)) {
            $PaymentMode = $RowPaymentmode['PaymentMode'];
            $SqlEntries = "SELECT Count(*) as Count,sum(RPB.Amount) as Amount FROM `RmPaymentDetails` as RPD 
left join RmPaymentBreakup as RPB on RPB.PaymentID=RPD.PaymentID 
left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RPB.ArrivalID 
where RPD.BillerType='$BillerType' and Date   BETWEEN '$StartDate' and '$EndDate' and PaymentMode='$PaymentMode' $Query";
            $StmtEntries = $this->conn->prepare($SqlEntries);
            $StmtEntries->execute();
            $RowEntries = $StmtEntries->fetch(PDO::FETCH_ASSOC);
            $TotalDebitAmount += $RowEntries['Amount'];
            if ($PaymentMode == 'B') {
                $Description = 'No.of Bank Entries : ' . $RowEntries['Count'] . ' Total Bank Paid : ' . sprintf('%0.2f', ($RowEntries['Amount']), 2);
            } else if ($PaymentMode == 'C') {
                $Description = 'No.of Cash Entries : ' . $RowEntries['Count'] . ' Total Cash Paid : ' . sprintf('%0.2f', ($RowEntries['Amount']), 2);
            } else if ($PaymentMode == 'J') {
                $Description = 'No.of Other Entries : ' . $RowEntries['Count'] . ' Total Other Paid : ' . sprintf('%0.2f', ($RowEntries['Amount']), 2);
            }
            $ArrayEntries[] = $Description;
            $EntryDescription = implode('<br>', $ArrayEntries);
        }
        $ArrayEntryData['EntryDescription'] = $EntryDescription;
        $ArrayEntryData['TotalDebitAmount'] = $TotalDebitAmount;
        return $ArrayEntryData;
    }
    function getSubTotal($CheckNo, $PayDate, $BillerType, $ArrivalIds)
    {
        $sqlRmPaySubTotal = " SELECT sum(RPB.Amount) as Amount  FROM RmPaymentBreakup as RPB 
       left join  `RmPaymentDetails` as RPD on RPD.PaymentID=RPB.PaymentID
        WHERE CheckNo='$CheckNo' and PaymentMode='B' and Date='$PayDate' and RPB.BillerType='$BillerType' and RPB.ArrivalID in ($ArrivalIds) ";
        $stmtRmPaySubTotal = $this->conn->prepare($sqlRmPaySubTotal);
        $stmtRmPaySubTotal->execute();
        $dataRowRmPaySubTotal = $stmtRmPaySubTotal->fetch(PDO::FETCH_ASSOC);
        $subTotal = $dataRowRmPaySubTotal['Amount'];
        $ArrayBookingList = array();
        $ArrayBookingList['SNO'] = '';
        $ArrayBookingList['ChequeNo'] = $CheckNo;
        $ArrayBookingList['Particulars'] = 'Cheque Wise Total:';
        $ArrayBookingList['SubTotal'] = sprintf('%0.2f', ($subTotal), 2);
        return $ArrayBookingList;
    }

}
