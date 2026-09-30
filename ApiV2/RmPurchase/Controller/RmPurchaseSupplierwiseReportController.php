<?php
include_once '../Global/Util.php';
include_once 'UtilController.php';

class RmPurchaseSupplierwiseReportController
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
    public function GetRmPurchaseSupplierWiseReportDetails($RequestBody)
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

        $SqlFields = "  ArrivalDate,GROUP_CONCAT(RmArrivalID) as ArrivalIDs";
        $sqlRmArrivalQuery = "SELECT FinancierName,$SqlFields FROM `RmArrivalDetatils` as RAD 
        left join RmSuppliers as RS on RAD.RmSupplierID=RS.ID
        WHERE  $DateFilter and RAD.LotStatus='1'
        $Query  GROUP by RmSupplierID   asc";
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
            $ArrayBookingList['Date'] = $this->utilController->getFormatedDateEmpty($dataRow['ArrivalDate']);
            $ArrayBookingList['FinancierName'] = $dataRow['FinancierName'];
            $ArrivalIDs = $dataRow['ArrivalIDs'];
            $WeightDetails = $this->getBreakUpDetails($ArrivalIDs);
            $ArrayBookingList['HONVMQTY'] = (float)$WeightDetails['HONVMQTY'];
            $ArrayBookingList['HONVMValue'] = (float)$WeightDetails['HONVMValue'];
            $ArrayBookingList['HONBTQTY'] = (float)$WeightDetails['HONBTQTY'];
            $ArrayBookingList['HONBTValue'] = (float)$WeightDetails['HONBTValue'];
            $ArrayBookingList['TotalQty'] = (float)$WeightDetails['TotalQty'];
            $ArrayBookingList['TotalValue'] = (float)$WeightDetails['TotalValue'];

            $ArrayReportData[] = $ArrayBookingList;

            $TotalHonVmQty += (float)$WeightDetails['HONVMQTY'];
            $TotalHonBTQty += (float)$WeightDetails['HONBTQTY'];
            $TotalHonVmValue += (float)$WeightDetails['HONVMValue'];
            $TotalHonBTValue += (float)$WeightDetails['HONBTValue'];
            $TotalQuantity += (float)$WeightDetails['TotalQty'];
            $TotalAmount += (float)$WeightDetails['TotalValue'];
        }

        $ArrayTotals['TotalHonVmQty'] = $TotalHonVmQty;
        $ArrayTotals['TotalHonBTQty'] = $TotalHonBTQty;
        $ArrayTotals['TotalQuantity'] = $TotalQuantity;
        $ArrayTotals['TotalHonVmValue'] = $TotalHonVmValue;
        $ArrayTotals['TotalHonBTValue'] = $TotalHonBTValue;
        $ArrayTotals['TotalAmount'] = $TotalAmount;
        $Data['DateWiseReportData'] = $ArrayReportData;
        $Data['Totals'] = $ArrayTotals;
        return $Data;
    }
    public function getBreakUpDetails($ArrivalIDs)
    {
        $sqlQuery = "select (SELECT sum(Weight) FROM `RmArrivalBreakUpDetails` as RABD left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RABD.ArrivalID WHERE RAD.Species='VM' and ArrivalID in ($ArrivalIDs) )as HONVMQTY,
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
