<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class RmReceivedDetailsController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }
    public function getRmReceivedDetails($RequestBody)
    {
        $StartDate = $RequestBody['StartDate'];
        $EndDate = $RequestBody['EndDate'];
        $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
        $MergeHonLoose = $RequestBody['MergeHonLoose'];

        $Query = '';
        if ($ReceivingPlantID != '') {
            $Query .= " AND RmRD.ReceivingPlantID = '$ReceivingPlantID'";
        }

        $DateFilter = "RmRD.ArrivalDate BETWEEN '$StartDate' AND '$EndDate'";


        $sqlFields = "RmRD.ReceivingID, RmRD.ArrivalDate, RmRD.VehicleID, RmRD.VehicleNumber, RmRD.ReceivingPlantID, 
		RmPS.FullName, RmPS.StationName, RmRD.TrackCode, RmRD.TrackNumber,RmRD.SealNo, RmRD.SupervisorID, RmUL.UserName, RmRD.Remarks, 
		RmPS.ProdCode as PlantCode";

        $sqlReceivedQuery = "SELECT  $sqlFields FROM `RmReceivingDetails` AS RmRD
		LEFT JOIN RmProcessingStations AS RmPS ON RmRD.ReceivingPlantID=RmPS.ProcessingStationID
		LEFT JOIN RmUsersList AS RmUL ON RmRD.SupervisorID=RmUL.ID 
		WHERE $DateFilter $Query and IsStoreMaterial=0 AND RmRD.`TrackCode` NOT REGEXP 'REJ'
        ORDER BY RmRD.ArrivalDate ,RmRD.TrackNumber ASC ";

        $stmtSelect = $this->conn->prepare($sqlReceivedQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayReceivedData = [];
        $i = 0;
        $GrandArrivalQTY = 0;
        $GrandReceivingQTY = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ReceivingID = $dataRow['ReceivingID'];
            $BreakUpData = $this->getRmReceivedBreakupDetails($ReceivingID,$MergeHonLoose);
            $ArrivalDate = !empty($dataRow['ArrivalDate']) ? date('d-m-Y', strtotime($dataRow['ArrivalDate'])) : '';


            $ArrayReceivedDetails['SNo'] = ++$i;
            $ArrayReceivedDetails['ArrivalDate'] = $ArrivalDate;
            $ArrayReceivedDetails['ReceivingID'] = $dataRow['ReceivingID'];
            $ArrayReceivedDetails['VehicleID'] = $dataRow['VehicleID'];
            $ArrayReceivedDetails['VehicleNumber'] = $dataRow['VehicleNumber'];
            $ArrayReceivedDetails['ReceivingPlantID'] = $dataRow['ReceivingPlantID'];
            $ArrayReceivedDetails['FullName'] = $dataRow['FullName'];
            $ArrayReceivedDetails['StationName'] = $dataRow['StationName'];
            $ArrayReceivedDetails['TrackCode'] = $dataRow['TrackCode'];
            $ArrayReceivedDetails['TrackNumber'] = $dataRow['TrackNumber'];
            $ArrayReceivedDetails['SealNo'] = $dataRow['SealNo'];
            $ArrayReceivedDetails['SupervisorID'] = $dataRow['SupervisorID'];
            $ArrayReceivedDetails['UserName'] = $dataRow['UserName'];
            $ArrayReceivedDetails['Remarks'] = $dataRow['Remarks'];
            $ArrayReceivedDetails['PlantCode'] = $dataRow['PlantCode'];
            $ArrayReceivedDetails['BreakUpData'] = $BreakUpData['BreakUpData'];

            $GrandArrivalQTY += $BreakUpData['TotalArrivalQTY'];
            $GrandReceivingQTY += $BreakUpData['TotalReceivingQTY'];

            $ArrayReceivedData[] = $ArrayReceivedDetails;
        }
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['Data'] = $ArrayReceivedData;
        $Data['ArrivalQTY'] =number_format( $GrandArrivalQTY,3);
        $Data['ReceivingQTY'] =number_format( $GrandReceivingQTY,3);

        return $Data;
    }

    public function getRmReceivedBreakupDetails($ReceivingID,$MergeHonLoose)
    {
        if($MergeHonLoose == 1){
            $sqlFields = "RAD.ArrivalNumber,GROUP_CONCAT(RABD.Count) as ArrivalCount,SUM(RABD.Weight) as ArrivalWeight,RABD.Variety as ArrivalVariety,
            RRB.SampleQuantity,RRB.NoofPieces,RRB.ReceivingCount,RRB.ReceivingQty,RRB.CountDifference,RRB.QuantityDifference,RRD.Remarks,
            RmArrivalID as ArrivalID, RRB.ArrivalBreakupID, RRB.ReceivalBreakupID, RABD.ArrivalBrkUpID,RAD.Species,RPS.LotCode,RMS.ProdCode,GROUP_CONCAT(CONCAT(RAD.Species, '-', RABD.Variety)) as OverallVariety";

            $sqlBreakupDetails = "SELECT  $sqlFields FROM RmArrivalDetatils AS RAD
				LEFT JOIN RmArrivalBreakUpDetails AS RABD ON RAD.RmArrivalID = RABD.ArrivalID 
				LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalID = RABD.ArrivalID 
                left join RmReceivingDetails as RRD on RRD.ReceivingID=RRB.ReceivingID
                LEFT JOIN RmPurchaseStations as RPS ON RAD.ArrivalStationID=RPS.PurchaseStationID
                LEFT JOIN (
                    SELECT * FROM PpcPlantCheckData AS PPCD1
                    WHERE PPCD1.CrateDataID = (
                        SELECT MAX(PPCD2.CrateDataID)
                        FROM PpcPlantCheckData AS PPCD2
                        WHERE PPCD2.ArrivalID = PPCD1.ArrivalID
                    )
                ) AS PPCD ON RABD.ArrivalID = PPCD.ArrivalID
                LEFT JOIN RmProcessingStations AS RMS ON RMS.ProcessingStationID=COALESCE(PPCD.PlantID)
                WHERE RRD.ReceivingID='$ReceivingID' GROUP BY RAD.ArrivalNumber order by ArrivalBrkUpID asc ";
        }else{
            $sqlFields = "RAD.ArrivalNumber,RABD.Count as ArrivalCount,RABD.Weight as ArrivalWeight,RABD.Variety as ArrivalVariety,
            RRB.SampleQuantity,RRB.NoofPieces,RRB.ReceivingCount,RRB.ReceivingQty,RRB.CountDifference,RRB.QuantityDifference,RRD.Remarks,
            RmArrivalID as ArrivalID, RRB.ArrivalBreakupID, RRB.ReceivalBreakupID, RABD.ArrivalBrkUpID,RAD.Species,RPS.LotCode,RMS.ProdCode,CONCAT(RAD.Species, '-', RABD.Variety) as OverallVariety";

            $sqlBreakupDetails = "SELECT  $sqlFields FROM RmArrivalBreakUpDetails as RABD 
            left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RABD.ArrivalID 
            left join RmReceivingBreakup as RRB on RRB.ArrivalBreakupID=RABD.ArrivalBrkUpID
            left join RmReceivingDetails as RRD on RRD.ReceivingID=RRB.ReceivingID
            LEFT JOIN RmPurchaseStations as RPS ON RAD.ArrivalStationID=RPS.PurchaseStationID
            LEFT JOIN (
                SELECT * FROM PpcPlantCheckData AS PPCD1
                WHERE PPCD1.CrateDataID = (
                    SELECT MAX(PPCD2.CrateDataID)
                    FROM PpcPlantCheckData AS PPCD2
                    WHERE PPCD2.ArrivalID = PPCD1.ArrivalID
                )
            ) AS PPCD ON RABD.ArrivalID = PPCD.ArrivalID
            LEFT JOIN RmProcessingStations AS RMS ON RMS.ProcessingStationID=COALESCE(PPCD.PlantID)
            WHERE RRD.ReceivingID='$ReceivingID' order by ArrivalBrkUpID asc ";
        }


        $stmtSelect = $this->conn->prepare($sqlBreakupDetails);
        $stmtSelect->execute();

        $ArrayBreakUpData = [];
        $i = 0;
        $TotalArrivalQTY = 0;
        $TotalReceivingQTY = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ReceivingCount = $dataRow['ReceivingCount'] == 0 ? ' ' : $dataRow['ReceivingCount'];
            $ReceivingQty = $dataRow['ReceivingQty'] == 0 ? ' ' : $dataRow['ReceivingQty'];
            $ArrivalCount = $dataRow['ArrivalCount'];
            $ArrivalWeight = $dataRow['ArrivalWeight'];

            $ArrayReceivedBreakupDetails['SNo'] = ++$i;
            $ArrayReceivedBreakupDetails['ArrivalID'] = $dataRow['ArrivalID'];
            $ArrayReceivedBreakupDetails['ArrivalBreakupID'] = $dataRow['ArrivalBreakupID'];
            $ArrayReceivedBreakupDetails['ReceivalBreakupID'] = $dataRow['ReceivalBreakupID'];
            $ArrayReceivedBreakupDetails['ArrivalNumber'] = $dataRow['ArrivalNumber'];
            $ArrayReceivedBreakupDetails['LotCode'] = $dataRow['ProdCode'];
            $ArrayReceivedBreakupDetails['ArrivalCount'] = $ArrivalCount;
            $ArrayReceivedBreakupDetails['ArrivalWeight'] = number_format($ArrivalWeight, 3);
            $ArrayReceivedBreakupDetails['ArrivalVariety'] = $dataRow['ArrivalVariety']. "-" . $dataRow['Species'];
            $ArrayReceivedBreakupDetails['Species'] = $dataRow['Species'];
            $ArrayReceivedBreakupDetails['OverallVariety'] = $dataRow['OverallVariety'];
            $ArrayReceivedBreakupDetails['SampleQuantity'] =number_format($dataRow['SampleQuantity'] == 0 ? ' ' : $dataRow['SampleQuantity'],3); 
            $ArrayReceivedBreakupDetails['NoofPieces'] = round($dataRow['NoofPieces'] == 0 ? ' ' : $dataRow['NoofPieces']);
            $ArrayReceivedBreakupDetails['ReceivingCount'] =number_format($ReceivingCount,2); 
            $ArrayReceivedBreakupDetails['ReceivingQty'] =number_format($ReceivingQty,3); 
            $ArrayReceivedBreakupDetails['CountDifference'] =number_format($ReceivingCount - $ArrivalCount,2); 
            $ArrayReceivedBreakupDetails['QuantityDifference'] =number_format($ReceivingQty - $ArrivalWeight,3); 

            $ArrayReceivedBreakupDetails['RemarksTwo'] = $dataRow['Remarks'];

            $TotalArrivalQTY += $ArrivalWeight;
            $TotalReceivingQTY += $ReceivingQty;

            $ArrayBreakUpData[] = $ArrayReceivedBreakupDetails;
        }

        $Data['BreakUpData'] = $ArrayBreakUpData;
        $Data['TotalArrivalQTY'] = $TotalArrivalQTY;
        $Data['TotalReceivingQTY'] = $TotalReceivingQTY;

        return $Data;
    }
}
