<?php

include_once '../Global/Util.php';
// include_once 'UtilController.php';

class RmHarvestingWorkingBoysReportController
{

    private $conn;
    private $utilController;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }
    // GET ALL
    public function getBoysReportDetails($RequestBody)
    {
        $CenterID = $RequestBody['CenterID'];
        $StartDate = $RequestBody['StartDate'];
        $EndDate = $RequestBody['EndDate'];
        $DailyWages = $RequestBody['DailyWages'];
        $BoysGroup = $RequestBody['BoysGroup'];

        $DateFilter = "BETWEEN '$StartDate' and '$EndDate'";

        $Query = '';

        if ($CenterID != '') {
            $Query .= " AND BookingStationID='$CenterID'";
        }
        if ($BoysGroup != '') {
            $Query .= " AND BoysGroupID='$BoysGroup'";
        }

        $SqlFields = "RmBookingID, RmBD.BookingNumber, BookingStationID, RmBD.BookingDate,FarmAddress,StationName,
        HarvestingID, MaterialTotalQuantity ,CheckingCount, BoysGroupID, WorkingBoys,MTCrateWeight,CheckingQty,GroupName,HarvestDate,
        (SELECT COUNT( `HarvestingID`) CNT FROM RmHarvestingDetails WHERE HarvestDate = RmHD.HarvestDate AND BoysGroupID = RmHD.BoysGroupID) AS TblMergeCnt,
        (SELECT SUM( `WorkingBoys`) CNT FROM RmHarvestingDetails WHERE HarvestDate = RmHD.HarvestDate AND BoysGroupID = RmHD.BoysGroupID) AS SumWorkingBoys";
        // RmBookingDetatils AS RmBD
        $sqlSelect = "SELECT $SqlFields FROM RmHarvestingDetails AS RmHD  
        LEFT JOIN RmBookingDetatils AS RmBD ON RmBD.RmBookingID = RmHD.BookingID 
        LEFT JOIN RmBoysGroupMasters AS RmBGD ON RmHD.BoysGroupID = RmBGD.GroupID
        LEFT JOIN RmPurchaseStations AS RmPS ON RmBD.BookingStationID = RmPS.PurchaseStationID
        WHERE RmBD.BookingDate $DateFilter $Query ";

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayBoysReportData = [];
        $TotalBoys = 0;
        $GrandTotalAmount = 0;
        $TotalBookingQty = 0;
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $HarvestingID = $dataRow['HarvestingID'];
            $WorkingBoys = $dataRow['WorkingBoys'];

            $ArrBoysReportList['SNO'] = ++$i;
            $ArrBoysReportList['RmBookingID'] = $dataRow['RmBookingID'];
            $ArrBoysReportList['BookingNumber'] = $dataRow['BookingNumber'];
            $ArrBoysReportList['FarmAddress'] = $dataRow['FarmAddress'];
            $ArrBoysReportList['BookingStationID'] = $dataRow['BookingStationID'];
            $ArrBoysReportList['StationName'] = $dataRow['StationName'];
            $ArrBoysReportList['BookingDate'] = $dataRow['BookingDate'];
            $ArrBoysReportList['HarvestingID'] = $dataRow['HarvestingID'];
            $ArrBoysReportList['MTCrateWeight'] = $dataRow['MTCrateWeight'];
            $ArrBoysReportList['CheckingQty'] = $dataRow['CheckingQty'];
            $ArrBoysReportList['CheckingCount'] = $dataRow['CheckingCount'];
            $ArrBoysReportList['BoysGroupID'] = $dataRow['BoysGroupID'];
            $ArrBoysReportList['BoysGroupName'] = $dataRow['GroupName'];
            $ArrBoysReportList['WorkingBoys'] = $dataRow['WorkingBoys'];
            $ArrBoysReportList['HarvestDate'] = $dataRow['HarvestDate'];
            $ArrBoysReportList['TblMergeCnt'] = $dataRow['TblMergeCnt'];
            $ArrBoysReportList['SumWorkingBoys'] = $dataRow['SumWorkingBoys'];

            $Totals = $this->getTotals($HarvestingID);

            $TotalNetWeight = $Totals['TotalWeight'] + $dataRow['CheckingQty'];
            $TotalCrateQty = $Totals['TotalCrates'] * $dataRow['MTCrateWeight'];

            $WithOutNetQty = $TotalNetWeight - $TotalCrateQty;
            $ArrBoysReportList['WithOutNetQty'] = $WithOutNetQty;

            $TotalAmount = $WorkingBoys * $DailyWages;
            $ArrBoysReportList['TotalAmount'] = $TotalAmount;

            $ArrayBoysReportData[] = $ArrBoysReportList;
            $TotalBoys += $WorkingBoys;
            $GrandTotalAmount += $TotalAmount;
            $TotalBookingQty += $WithOutNetQty;
        }
        $Data['Error'] = null;
        $Data['Msg'] = "success";
        $Data['Data'] = $ArrayBoysReportData;
        $Data['TotalBoys'] = $TotalBoys;
        $Data['GrandTotalAmount'] = $GrandTotalAmount;
        $Data['TotalBookingQty'] = $TotalBookingQty;

        return $Data;
    }


    function getTotals($HarvestingID)
    {
        $sqlTotals = "SELECT SUM(Weight) AS TotalWeight, SUM(NoofCrates) AS TotalCrates FROM RmHarvestingBreakupDetails 
        WHERE HarvestingID='$HarvestingID'";
        $stmtTotals = $this->conn->prepare($sqlTotals);
        $stmtTotals->execute();
        $dataTotals = $stmtTotals->fetch(PDO::FETCH_ASSOC);
        $AryTotals['TotalWeight'] = $dataTotals['TotalWeight'];
        $AryTotals['TotalCrates'] = $dataTotals['TotalCrates'];

        return $AryTotals;
    }
}
