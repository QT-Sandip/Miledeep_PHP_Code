<?php

include_once '../Global/Util.php';
include_once 'UtilController.php';

class RmPaymentCenterwiseWiseReportController
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
    public function GetRmPaymentCenterwiseWiseReportDetails($RequestBody)
    {
        $Plant = $RequestBody['Plant'];
        $CenterID = $RequestBody['CenterID'];
        $StartDate = $RequestBody['StartDate'];
        $SupplierID = $RequestBody['SupplierID'];
        $ReqAgentID = $RequestBody['AgentID'];
        $Type = $RequestBody['BillerType'];

        $DateFilter = "  RAD.BillDate  <= '$StartDate'";
        $Query = '';

        if ($CenterID != '') {
            $Query .= " and ArrivalStationID='$CenterID'";
        }

        if ($Plant != '') {
            $Query .= " and RAD.ProcessingStationID='$Plant'";
        }

        if ($SupplierID != '') {
            $Query .= " and RmSupplierID='$SupplierID'";
        }

        if ($ReqAgentID != '') {
            $Query .= " and AgentID='$ReqAgentID'";
        }

        $Groupby = '';
        if ($Type == '1') {
            $Groupby .= " group by RAD.AgentID";

        } else if ($Type == '2') {
            $Groupby .= " group by RAD.RmSupplierID";
        } else {
            $Groupby .= " group by RAD.RmSupplierID,RAD.AgentID";
        }

        $sqlRmArrivalQuery = "SELECT   AgentID,RAD.RmSupplierID,GROUP_CONCAT(ArrivalID) as ArrivalIDs FROM `RmBillDetails`  as RBD
        left join RmArrivalDetatils as RAD on  RBD.ArrivalID=RAD.RmArrivalID  
        WHERE $DateFilter and RAD.LotStatus='1'  $Query and  PaymentStatus=0 $Groupby ";
        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();
        $Data = array();
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $i++;
            $ArrayReportDataTemp = $this->getCenterwiseData($RequestBody, $DateFilter, $Query, $dataRow, $StartDate, $Type, $Groupby,$i);

            foreach ($ArrayReportDataTemp as $Item) {

                $ArrayReportData[] = $Item;
                $GTotalOSAmount += $Item['OSAmount'];
                $GTotalBillAmount += $Item['BillAmount'];
                $GTotalPaidAmount += $Item['PaidAmount'];
            }
        }

        $ArrayTotals['TotalOSAmount'] =sprintf('%0.2f', ($GTotalOSAmount), 2) ;
        $ArrayTotals['TotalBillAmount'] = sprintf('%0.2f', ($GTotalBillAmount), 2);
        $ArrayTotals['TotalPaidAmount'] = sprintf('%0.2f', ($GTotalPaidAmount), 2);
        $Data['PaymentCenterwiseReportData'] = $ArrayReportData;
        $Data['Totals'] = $ArrayTotals;
        return $Data;
    }
    function getCenterwiseData($RequestBody, $DateFilter, $Query, $suppllierRow, $StartDate, $Type, $Groupby,$i)
    {

        $ArrivalIDs = $suppllierRow['ArrivalIDs'];
        $RmSupplierID = $suppllierRow['RmSupplierID'];
        $AgentID = $suppllierRow['AgentID'];
        $ReqAgentID = $RequestBody['AgentID'];

        $sqlRmArrivalQuery = $this->getQuery($DateFilter, $Query, $suppllierRow, $StartDate, $Type, $Groupby);
        $stmtRmArrivalQuery = $this->conn->prepare($sqlRmArrivalQuery);
        $stmtRmArrivalQuery->execute();

        $TotalOSAmount = 0;
        $TotalBillAmount = 0;
        $TotalPaidAmount = 0;
        
        $j = 0;
        while ($dataRow = $stmtRmArrivalQuery->fetch(PDO::FETCH_ASSOC)) {
            $supplierPaidAmount = $dataRow['SPaidAmount'];
            $agentPaidAmount = $dataRow['APaidAmount'];

            $ArySupplierHarvestAmount = $this->getSupplierHarvestAmount($ArrivalIDs);
            //$AryAgentHarvestAmount = $this->getAgentHarvestAmount($ArrivalIDs, $AgentID);
            $TotalBillAmountExTDS = $dataRow['BillAmount'] - $dataRow['BillTds'];
            $TotalHarvestAmountExTDS = $ArySupplierHarvestAmount['HarvestAmount'] - $ArySupplierHarvestAmount['HarvestTds'];

            if ($ReqAgentID == '' && $dataRow['AgentID'] != 0) {

                $AryAgentDeatils = $this->utilController->getAgentName($dataRow['AgentID']);
                $AgentName = $AryAgentDeatils['UserName'];
                $SupplierTotalBillAmountExTDS = $TotalBillAmountExTDS;

            } else {

                if ($TotalHarvestAmountExTDS > 0) {
                    $SupplierTotalBillAmountExTDS = $TotalBillAmountExTDS + $TotalHarvestAmountExTDS ;
                } else {
                    $SupplierTotalBillAmountExTDS = $TotalBillAmountExTDS;
                }

            }
            if ($Type == '') {

                $ArrayBookingList['SNO'] = $i;
                $ArrayBookingList['Type'] = $this->utilController->getRMSuppliers($RmSupplierID)['SupplierType'];
                $ArrayBookingList['Center'] = $this->utilController->getRMSuppliers($RmSupplierID)['SupplierName'];
                $ArrayBookingList['BillAmount'] = sprintf('%0.2f', ($SupplierTotalBillAmountExTDS), 2); // By Baburao 
                $ArrayBookingList['PaidAmount'] = sprintf('%0.2f', ($supplierPaidAmount), 2);
                $ArrayBookingList['OSAmount'] = sprintf('%0.2f', ($ArrayBookingList['BillAmount'] - $ArrayBookingList['PaidAmount']), 2); //uma
                $ArrayBookingList['TotalHarvestAmountExTDS'] = sprintf('%0.2f', ($TotalHarvestAmountExTDS), 2); //uma

                $TotalOSAmount += $ArrayBookingList['OSAmount'];
                $TotalPaidAmount += $ArrayBookingList['PaidAmount'];
                $TotalBillAmount += $ArrayBookingList['BillAmount']; // By Baburao 

                $ArrayReportData[] = $ArrayBookingList;
            } else if ($Type != 1) {

                $ArySupplierHarvestAmount = $this->getSupplierHarvestAmount($ArrivalIDs);
                $SupplierBillAmountExdTDS = $TotalBillAmountExTDS;
                $SupplierHarvestAmountExTDS = $ArySupplierHarvestAmount['HarvestAmount'] - $ArySupplierHarvestAmount['HarvestTds'];
                if ($ReqAgentID == '' && $dataRow['AgentID'] != 0) {
                    $TotalBillAmountExTDS = $SupplierBillAmountExdTDS;
                } else {
                    if ($TotalHarvestAmountExTDS > 0) {
                        $TotalBillAmountExTDS = $SupplierBillAmountExdTDS + $SupplierHarvestAmountExTDS;
                    } else {
                        $TotalBillAmountExTDS = $SupplierBillAmountExdTDS;
                    }
                }
                $ArrayBookingList['SNO'] = $i;
                $ArrayBookingList['Type'] = $this->utilController->getRMSuppliers($RmSupplierID)['SupplierType'];
                $ArrayBookingList['Center'] = $this->utilController->getRMSuppliers($RmSupplierID)['SupplierName']; //$dataRow['StationName'];
                $ArrayBookingList['BillAmount'] = sprintf('%0.2f', ($TotalBillAmountExTDS), 2); // By Baburao 
                $ArrayBookingList['PaidAmount'] = sprintf('%0.2f', ($supplierPaidAmount), 2);
                $ArrayBookingList['OSAmount'] = sprintf('%0.2f', ($TotalBillAmountExTDS - $supplierPaidAmount), 2); //uma

                $TotalOSAmount += $ArrayBookingList['OSAmount'];
                $TotalPaidAmount += $supplierPaidAmount;
                $TotalBillAmount += $ArrayBookingList['BillAmount']; // By Baburao 

                $ArrayReportData[] = $ArrayBookingList;
            }


            if ($Type != 2) {
                if ($dataRow['AgentID'] != '' && $dataRow['AgentID'] != 0) {
                    $AryAgentHarvestAmount = $this->getAgentHarvestAmount($ArrivalIDs, $AgentID);
                    $BillAmount = $AryAgentHarvestAmount['HarvestAmount'];
                    $TotalBillAmountExTDS = $AryAgentHarvestAmount['HarvestAmount'] - $AryAgentHarvestAmount['HarvestTds'] - $AryAgentHarvestAmount['HarvestCommision'];
                    $AryAgentDeatils = $this->utilController->getAgentName($AgentID);
                    $AgentName = $AryAgentDeatils['UserName'];
                    if ($Type != '') {

                        $SNo = ++$j;
                    }
                    if ($BillAmount > 0) {
                        $ArrayBookingList['SNO'] = $SNo;
                        $ArrayBookingList['Center'] = $AgentName;
                        $ArrayBookingList['Type'] = 'Agent';
                        $ArrayBookingList['BillAmount'] = sprintf('%0.2f', ($TotalBillAmountExTDS), 2); // By Baburao 
                        $ArrayBookingList['PaidAmount'] = sprintf('%0.2f', ($agentPaidAmount), 2);
                        $ArrayBookingList['OSAmount'] = sprintf('%0.2f', ($TotalBillAmountExTDS - $agentPaidAmount), 2); //uma
                        $TotalOSAmount += $ArrayBookingList['OSAmount'];
                        $TotalPaidAmount += $ArrayBookingList['PaidAmount'];
                        $TotalBillAmount += $ArrayBookingList['BillAmount']; // By Baburao 

                        $ArrayReportData[] = $ArrayBookingList;
                    }
                }
            }
        }
        return $ArrayReportData;

    }

    function getAgentHarvestAmount($ArrivalID, $AgentID)
    {
        $SqlHar = "SELECT sum(HarvestAmount) as  HarvestAmount,sum(HarvestTds) as HarvestTds, sum(HarvestCommision) as HarvestCommision FROM `RmBillDetails` WHERE  RmBillDetails.ArrivalID 
      in ($ArrivalID) and BillAgentID in ($AgentID) group by BillAgentID ";
        $stmt = $this->conn->prepare($SqlHar);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dataRow;
    }


    function getSupplierHarvestAmount($ArrivalID)
    {
         $SqlHar = "SELECT 
           sum(BillAmount) as BillAmount,sum(BillTds) as BillTds, sum(HarvestAmount) as  HarvestAmount ,
            sum(HarvestTds) as HarvestTds 
            FROM `RmBillDetails` WHERE  RmBillDetails.ArrivalID in ($ArrivalID) and BillAgentID=0  ";
        $stmt = $this->conn->prepare($SqlHar);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dataRow;
    }


    function getQuery($DateFilter, $Query, $suppllierRow, $StartDate, $Type, $Groupby)
    {

        $SupplierID = $suppllierRow['RmSupplierID'];
        $AgentID = $suppllierRow['AgentID'];
        $ArrivalIDs = $suppllierRow['ArrivalIDs'];

        if ($Type == '1') {
            $intQry = " and  RAD.AgentID in ($AgentID)";
        } else {
            $intQry = " and RAD.RmSupplierID in ($SupplierID)";
        }
        $sqlRmArrivalQuery = "SELECT 
            RAD.RmArrivalID,RAD.ArrivalStationID,RAD.BillDate,RAD.RmSupplierID, RAD.AgentID,
            RAD.ArrivalNumber,RSD.FinancierName,RSD.PanNumber,
            RAD.ArrivalNumber,sum(RBD.BillAmount) as BillAmount,sum(RBD.BillTds) as BillTds,
            max((RBD.BillAmount+RBD.HarvestAmount)-(RBD.BillTds+ RBD.HarvestTds)) as BExtd,
            (SELECT COALESCE(sum(RPBD.Amount),0) FROM RmPaymentBreakup as RPBD 
             left join RmPaymentDetails as RPD on RPD.PaymentID=RPBD.PaymentID where 
             ArrivalID in ($ArrivalIDs) and RPD.Date   <= '$StartDate') as PaidAmount,
             (SELECT COALESCE(sum(RPBD.Amount),0) FROM RmPaymentBreakup as RPBD 
             left join RmPaymentDetails as RPD on RPD.PaymentID=RPBD.PaymentID where RPBD.BillerType=2 
             and ArrivalID in ($ArrivalIDs) and RPD.Date   <= '$StartDate') as SPaidAmount,
             (SELECT COALESCE(sum(RPBD.Amount),0) FROM RmPaymentBreakup as RPBD 
             left join RmPaymentDetails as RPD on RPD.PaymentID=RPBD.PaymentID where RPBD.BillerType=1 
             and ArrivalID in ($ArrivalIDs) and RPD.Date   <= '$StartDate') as APaidAmount 
            FROM RmArrivalDetatils as RAD 
            left join RmSuppliers as RSD on RAD.RmSupplierID=RSD.ID 
            left join RmBillDetails as RBD on RAD.RmArrivalID=RBD.ArrivalID 
            
            WHERE RAD.BillDate <= '$StartDate' and RAD.RmArrivalID in ($ArrivalIDs) and PaymentStatus=0 $intQry  $Query   
            "; 
            // group by  RAD.RmArrivalID in ($ArrivalIDs) HAVING PaidAmount < BExtd order by ArrivalStationID,ArrivalNumber,BillDate  asc
    
                      
        return $sqlRmArrivalQuery;

    }

}
