<?php

include_once '../Global/Util.php';
// include_once 'UtilController.php';

class RmCountWisePurchaseReportController
{

    private $conn;
    private $db_table = "RmArrivalDetatils";
    private $utilController;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }
    // GET ALL
    public function GetRmCountWisePurchaseReportDetails($RequestBody)
    {
        $PlantID = $RequestBody['Plant'];
        $CenterID = $RequestBody['CenterID'];
        $Count = $RequestBody['Count'];
        $StartDate = $RequestBody['StartDate'];
        $EndDate = $RequestBody['EndDate'];
        $SupplierID = $RequestBody['SupplierID'];
        $IsHarestCommission = $RequestBody['HarestCommission'];

        $Query = '';

        if ($CenterID != '') {
            $Query .= " and ArrivalStationID='$CenterID'";
        }

        if ($PlantID != '') {
            $Query .= " and ProcessingStationID='$PlantID'";
        }

        if ($Count != '') {
            $Query .= " and Count='$Count'";
        }
        if ($SupplierID != '') {
            $Query .= " and RmSupplierID='$SupplierID'";
        }

        $SqlFields = " GROUP_CONCAT(RmArrivalID) as RmArrivalID,Species,Count,SUM(Weight) AS TotalWeight,SUM(Weight*Rate) as Amount,AVG(Rate) AS AvgRate,
        sum(Weight*HarvestCommission) as HarvestCommission,Variety";

        $sqlRmArrivalQuery = "SELECT $SqlFields FROM `RmArrivalDetatils`  RAD
        LEFT JOIN `RmArrivalBreakUpDetails` RABD ON RABD.ArrivalID=RAD.RmArrivalID
        WHERE BillDate BETWEEN '$StartDate' AND '$EndDate' $Query and RAD.LotStatus='1'
		 GROUP BY Variety,Species,Count ORDER BY `Count` ASC,Variety desc";  //uma

        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();

        $i = 0;
        $TotalAmount = 0;
        $TotalQuantity = 0;
        $Data = array();
        $DataVm = array();
        $DataBt = array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $HarvestExpanses = $this->getHarvestCommision($dataRow['RmArrivalID'], $dataRow['Variety'], $dataRow['Count']);
            $HarvestBillAmount = $HarvestExpanses;
            if ($IsHarestCommission == "T") {

                $Amount = $dataRow['Amount'] + $HarvestBillAmount;
            } else {
                $Amount = $dataRow['Amount'];
            }

            $ArrayBookingList['SNO'] = ++$i;
            $ArrayBookingList['HarvestExpanses'] = sprintf('%0.2f', isset($HarvestExpanses) ? $HarvestExpanses : 0);
            $ArrayBookingList['Species'] = $dataRow['Species'] . "-" . $dataRow['Variety'];
            $ArrayBookingList['Count'] = ROUND($dataRow['Count'], 2);
            $ArrayBookingList['TotalWeight'] = number_format($dataRow['TotalWeight'], 2);
            $ArrayBookingList['Amount'] = number_format($Amount, 2);
            $ArrayBookingList['AvgRate'] = number_format(($Amount / $dataRow['TotalWeight']), 2);

            $TotalQuantity += $dataRow['TotalWeight'];
            $TotalAmount += $Amount;

            if ($dataRow['Species'] == 'VM') {
                $DataVm[] = $ArrayBookingList;
            } else {
                $DataBt[] = $ArrayBookingList;
            }

        }
        $i = 0;
        foreach ($DataVm as $Item) {
            $Item['SNO'] = ++$i;
            $ArrayReportData[] = $Item;
        }

        foreach ($DataBt as $Item) {
            $Item['SNO'] = ++$i;
            $ArrayReportData[] = $Item;
        }

        $ArrayTotals['TotalQuantity'] = number_format($TotalQuantity, 2);
        $ArrayTotals['TotalAmount'] = number_format($TotalAmount, 2);
        $Data['CountwiseReportData'] = $ArrayReportData;
        $Data['Totals'] = $ArrayTotals;
        return $Data;
    }

    private function getHarvestCommision($RmArrivalID, $Variety, $Count)
    {
        $sqlQuery = "SELECT sum(HarvestCommission * Weight) as HarvestCommission  FROM `RmArrivalDetatils` as RAD 
        left Join RmArrivalBreakUpDetails as RABD on RAD.RmArrivalID=RABD.ArrivalID WHERE
        RAD.RmArrivalID in ($RmArrivalID) and Variety='$Variety' and Count='$Count' ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        return $HarvestExpKg = $dataRow['HarvestCommission'];

    }
}

