<?php
include_once '../Global/Util.php';
include_once 'UtilController.php';
class RmPurchaseDateWiseReportController
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
    public function GetRmPurchaseDateWiseReportDetails($RequestBody)
    {
        $CenterID = $RequestBody['CenterID'];
        $SupplierID = $RequestBody['SupplierID'];
        $Year = $RequestBody['Year'];
        $StartDate = $RequestBody['StartDate'];
        $EndDate = $RequestBody['EndDate'];
        $Plant = $RequestBody['Plant'];

        if ($Year != '') {
            $DateFilter = "  Year(BillDate)='$Year'";

        } else {
            $DateFilter = "  BillDate between '$StartDate' and '$EndDate'";
        }
        $Query = '';
        if ($Plant != '') {
            $Query .= " and ProcessingStationID='$Plant'";
        }
        if ($CenterID != '') {
            $Query .= " and ArrivalStationID='$CenterID'";
        }
        if ($SupplierID != '') {
            $Query .= " and RmSupplierID='$SupplierID'";
        }

        $SqlFields = "  BillDate,GROUP_CONCAT(RmArrivalID) as ArrivalIDs";
        $sqlRmArrivalQuery = "SELECT $SqlFields FROM `RmArrivalDetatils` WHERE $DateFilter and LotStatus='1'
         $Query  GROUP by BillDate order by BillDate asc";

        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();

        $i = 0;
        $TotalHonVmQty = 0;
        $TotalHonBTQty = 0;
        $TotalHonVmValue = 0;
        $TotalHonBTValue = 0;
        $TotalQuantity = 0;
        $TotalAmount = 0;
        $Data = array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayBookingList['SNO'] = ++$i;
            $ArrayBookingList['Date'] = $this->utilController->getFormatedDateEmpty($dataRow['BillDate']);
            $ArrayBookingList['FinancierName'] = $dataRow['FinancierName'];
            //Calculated Valus
            $ArrivalIDs = $dataRow['ArrivalIDs'];
            $WeightDetails = $this->getBreakUpDetails($ArrivalIDs);
            $ArrayBookingList['HONVMQTY'] = number_format($WeightDetails['HONVMQTY'], 2);
            $ArrayBookingList['HONVMValue'] = number_format($WeightDetails['HONVMValue'], 2);
            $ArrayBookingList['HONBTQTY'] = number_format($WeightDetails['HONBTQTY'], 2);
            $ArrayBookingList['HONBTValue'] = number_format($WeightDetails['HONBTValue'], 2);
            $ArrayBookingList['TotalQty'] = number_format($WeightDetails['TotalQty'], 2);
            $ArrayBookingList['TotalValue'] = number_format($WeightDetails['TotalValue'], 2);

            $ArrayReportData[] = $ArrayBookingList;

            $TotalHonVmQty += $WeightDetails['HONVMQTY'];
            $TotalHonBTQty += $WeightDetails['HONBTQTY'];
            $TotalHonVmValue += $WeightDetails['HONVMValue'];
            $TotalHonBTValue += $WeightDetails['HONBTValue'];
            $TotalQuantity += $WeightDetails['TotalQty'];
            $TotalAmount += $WeightDetails['TotalValue'];

        }

        $ArrayTotals['TotalHonVmQty'] = number_format($TotalHonVmQty, 2);
        $ArrayTotals['TotalHonBTQty'] = number_format($TotalHonBTQty, 2);
        $ArrayTotals['TotalQuantity'] = number_format($TotalQuantity, 2);
        $ArrayTotals['TotalHonVmValue'] = number_format($TotalHonVmValue, 2);
        $ArrayTotals['TotalHonBTValue'] = number_format($TotalHonBTValue, 2);
        $ArrayTotals['TotalAmount'] = number_format($TotalAmount, 2);
        $Data['DateWiseReportData'] = $ArrayReportData;
        $Data['Totals'] = $ArrayTotals;
        return $Data;
    }
    public function getBreakUpDetails($ArrivalIDs)
    {
        $sqlQuery = "select 
        (SELECT sum(Weight) FROM `RmArrivalBreakUpDetails` as RABD left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RABD.ArrivalID WHERE RAD.Species='VM' and ArrivalID in ($ArrivalIDs) )as HONVMQTY,
        (SELECT sum(Weight*HarvestCommission)  FROM `RmArrivalBreakUpDetails` as RABD left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RABD.ArrivalID WHERE RAD.Species='VM' and ArrivalID in ($ArrivalIDs) )as HONVMHarvestAmount,
         (SELECT sum(Weight*Rate) FROM `RmArrivalBreakUpDetails` as RABD left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RABD.ArrivalID WHERE RAD.Species='VM' and ArrivalID in ($ArrivalIDs) )as HONVMValue,
          (SELECT sum(Weight) FROM `RmArrivalBreakUpDetails` as RABD left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RABD.ArrivalID WHERE RAD.Species='BT' and ArrivalID in ($ArrivalIDs) ) as HONBTQTY, 
          (SELECT sum(Weight*HarvestCommission) FROM `RmArrivalBreakUpDetails` as RABD left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RABD.ArrivalID WHERE RAD.Species='BT' and ArrivalID in ($ArrivalIDs) ) as HONBTHarvestAmount, 
          (SELECT sum(Weight*Rate) FROM `RmArrivalBreakUpDetails` as RABD left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RABD.ArrivalID WHERE RAD.Species='BT' and ArrivalID in ($ArrivalIDs) ) as HONBTValue";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $data['HONVMQTY'] = 'NA';
            $data['HONVMValue'] = 'NA';
            $data['HONBTQTY'] = 'NA';
            $data['HONBTValue'] = 'NA';
            $data['TotalValue'] = 'NA';
            $data['TotalQty'] = 'NA';

        } else {
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $HONVMValue = $dataRow['HONVMValue'] + $dataRow['HONVMHarvestAmount'];
            $HONBTValue = $dataRow['HONBTValue'] + $dataRow['HONBTHarvestAmount'];
            $data['HONVMQTY'] = $dataRow['HONVMQTY'];
            $data['HONVMValue'] = $HONVMValue;
            $data['HONBTQTY'] = $dataRow['HONBTQTY'];
            $data['HONBTValue'] = $HONBTValue;
            $data['TotalQty'] = $dataRow['HONVMQTY'] + $dataRow['HONBTQTY'];
            $data['TotalValue'] = $HONVMValue + $HONBTValue;
        }
        return $data;
    }

}
?>