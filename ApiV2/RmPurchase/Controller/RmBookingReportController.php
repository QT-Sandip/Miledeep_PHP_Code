<?php

include_once '../Global/Util.php';
// include_once 'UtilController.php';

class  RmBookingReportController
{

    private $conn;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getBoysReportDetails($RequestBody)
    {
        $CenterID = $RequestBody['CenterID'];
        $StartDate = $RequestBody['StartDate'];
        $EndDate = $RequestBody['EndDate'];

        $DateFilter = "BETWEEN '$StartDate' and '$EndDate'";

        $Query = '';

        if ($CenterID != '') {
            $Query .= " AND BookingStationID='$CenterID'";
        }

        $SqlFields = "RmBookingID, BookingNumber, VehicalID, BookingDate,Species, VehicalNumber";

        $sqlSelect = "SELECT $SqlFields FROM RmBookingDetatils AS RmBD
        LEFT JOIN RmVehicalDetails AS RmVD ON RmBD.VehicalID = RmVD.ID
        WHERE RmBD.BookingDate $DateFilter $Query ";

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ArrayBoysReportData = [];
        $TotalWeight = 0;
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $RmBookingID = $dataRow['RmBookingID'];

            $ArrBoysReportList['SNO'] = ++$i;
            $ArrBoysReportList['RmBookingID'] = $dataRow['RmBookingID'];
            $ArrBoysReportList['BookingNumber'] = $dataRow['BookingNumber'];
            $ArrBoysReportList['VehicalID'] = $dataRow['VehicalID'];
            $ArrBoysReportList['BookingDate'] = $dataRow['BookingDate'];
            $ArrBoysReportList['VehicalNumber'] = $dataRow['VehicalNumber'];
            $ArrBoysReportList['Species'] = $dataRow['Species'];

            $WeightDetails = $this->getBreakUpDetails($RmBookingID);
            $ArrBoysReportList['BookingCount'] = $WeightDetails['BookingCount'];
            $ArrBoysReportList['BookingWeight'] = $WeightDetails['BookingWeight'];
            $ArrBoysReportList['BookingRate'] = $WeightDetails['BookingRate'];

            $ArrayBoysReportData[] = $ArrBoysReportList;
            $TotalWeight += $WeightDetails['BookingWeight'];
        }
        $Data['Error'] = null;
        $Data['Msg'] = "success";
        $Data['Data'] = $ArrayBoysReportData;
        $Data['TotalWeight'] = $TotalWeight;

        return $Data;
    }

    public function getBreakUpDetails($BookingID)
    {

        $sqlQuery = "SELECT GROUP_CONCAT(Count SEPARATOR  '/') as BookingCount,SUM(Weight) as BookingWeight,GROUP_CONCAT(Rate) as BookingRate 
        FROM `RmBookingBreakUpDetails` WHERE BookingID='$BookingID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $data['BookingCount'] = 'NA';
            $data['BookingWeight'] = 'NA';
            $data['BookingRate'] = 'NA';
        } else {
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['BookingCount'] = $dataRow['BookingCount'];
            $data['BookingWeight'] = $dataRow['BookingWeight'];
            $data['BookingRate'] = $dataRow['BookingRate'];
        }
        return $data;
    }
}
