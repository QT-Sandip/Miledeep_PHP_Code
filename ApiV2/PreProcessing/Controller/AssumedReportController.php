<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class AssumedReportController
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
    public function getAssumedDetails($RequestBody)
    {

        $ArrayArrivalData = $this->getAssumedArrivalWiseData($RequestBody);
        $RequestBody['AryArrivalID'] = $ArrayArrivalData['AryArrivalID'];
        $ArrayGradingData = $this->getAssumedGradingData($RequestBody);
        $ArrayCountGroupData = $this->getCountGroupWiseData($RequestBody);

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['TotalPurchaseWeight'] = number_format($ArrayArrivalData['TotalPurchaseWeight'], 3);
        $Data['TotalPurchaseAmount'] = number_format($ArrayArrivalData['TotalAmount'], 3);
        $Data['CountGroupWeight'] = number_format($ArrayCountGroupData['TotalCountGroupWeight'], 3);
        $Data['TotalGradingWeight'] = number_format($ArrayGradingData['TotalGradingWeight'], 2);

        $Data['TotalYield'] = number_format(($ArrayGradingData['TotalGradingWeight'] / $ArrayArrivalData['TotalPurchaseWeight']) * 100, 2);

        $Data['ArrivalData'] = $ArrayArrivalData['List'];
        $Data['Grading'] = $ArrayGradingData['List'];

        return $Data;
    }


    public function getAssumedArrivalWiseData($RequestBody)
    {
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $ArrivalPlant = $RequestBody['ArrivalPlant'];
        $Species = $RequestBody['Species'];
        $TrackID = $RequestBody['TrackID'];

        $Query = '';

        if ($ArrivalPlant != '') {
            $Query .= " and RAD.ProcessingStationID='$ArrivalPlant'";
        }
        if ($Species != '') {
            $Query .= " and RAD.Species='$Species' ";
        }

        if ($TrackID != '') {
            $Query .= " and  VehicalID in (SELECT VehicleID FROM `RmReceivingDetails` WHERE ReceivingID='$TrackID') ";
        }

        $sqlFields = "Count,sum(Weight) as Weight, sum(Weight*Rate) as TotalAmount,GROUP_CONCAT(RmArrivalID) as RmArrivalID";
        $sqlQuery = "SELECT $sqlFields FROM RmArrivalDetatils as RAD 
        left join RmArrivalBreakUpDetails as RAB on RAB.ArrivalID=RAD.RmArrivalID
        LEFT JOIN RmOverAllArrival AS OAL ON OAL.CenterID=RAB.ArrivalBrkUpID 
        WHERE RAD.ArrivalDate='$ArrivalDate' $Query GROUP by Count order by Count asc";

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();
        $rowCount = $stmtSelect->rowCount();

        $ArrayData = [];
        $AryArrivalID = array();
        $TotalWeight = 0;
        $TotalAmount = 0;
        $i = 0;

        $CountGroupData = $this->getCountGroupWiseData($RequestBody);
        $ArrayCountGroupData = $CountGroupData['List'];

        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayAssumedList['SNO'] = ++$i;
            $ArrayAssumedList['Count'] = $dataRow['Count'];
            $ArrayAssumedList['Weight'] = $dataRow['Weight'];
            $PurchaseRate = $dataRow['TotalAmount'] / $dataRow['Weight'];
            $ArrayAssumedList['Rate'] = number_format($PurchaseRate, 3);
            $ArrayAssumedList['Amount'] = $dataRow['TotalAmount'];

            $TotalWeight += $dataRow['Weight'];
            $TotalAmount += $dataRow['TotalAmount'];
            $AryArrivalID[] = $dataRow['RmArrivalID'];

            $ArrayData[] = $ArrayAssumedList;
        }

        $j = $rowCount;
        foreach ($ArrayCountGroupData as $countGroup) {

            $ArrayCountGroupList['SNO'] = ++$$j;
            $ArrayCountGroupList['BaseCount'] = $countGroup['BaseCount'];
            $ArrayCountGroupList['BaseCountWeight'] = $countGroup['Weight'];

            $ArrayData[] = $ArrayCountGroupList;
        }

        $Data['TotalPurchaseWeight'] = $TotalWeight;
        $Data['TotalAmount'] = $TotalAmount;
        $Data['AryArrivalID'] = $AryArrivalID;
        $Data['List'] = $ArrayData;

        return $Data;
    }

    public function getCountGroupWiseData($RequestBody)
    {
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $ArrivalPlant = $RequestBody['ArrivalPlant'];
        $Species = $RequestBody['Species'];
        $TrackID = $RequestBody['TrackID'];

        $Query = '';

        if ($ArrivalPlant != '') {
            $Query .= " and RAD.ProcessingStationID='$ArrivalPlant'";
        }
        if ($Species != '') {
            $Query .= " and RAD.Species='$Species' ";
        }

        if ($TrackID != '') {
            $Query .= " and VehicalID in (SELECT VehicleID FROM `RmReceivingDetails` WHERE ReceivingID='$TrackID') ";
        }

        $sqlFields = "Count, sum(Weight) as Weight, sum(Weight*Rate) as TotalAmount";
        $sqlQuery = "SELECT $sqlFields FROM RmArrivalDetatils as RAD 
        LEFT JOIN RmArrivalBreakUpDetails as RAB ON RAB.ArrivalID=RAD.RmArrivalID
        LEFT JOIN RmOverAllArrival AS OAL ON OAL.CenterID=RAB.ArrivalBrkUpID 
        WHERE RAD.ArrivalDate='$ArrivalDate' $Query GROUP BY Count ORDER BY Count ASC";


        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayData = [];
        $AryArrivalID = array();
        $TotalWeight = 0;
        $dataByBaseCount = [];

        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $Count = (float) $dataRow['Count'];
            $Weight = (float) $dataRow['Weight'];

            $BaseCount1 = $this->largestMultipleOfTenLessThan($Count);
            $BaseCount2 = $this->smallestMultipleOfTenGreaterThan($Count);

            if (!isset($dataByBaseCount[$BaseCount1])) {
                $dataByBaseCount[$BaseCount1] = [
                    'BaseCount' => $BaseCount1,
                    'Weight' => 0,
                ];
            }
            if (!isset($dataByBaseCount[$BaseCount2])) {
                $dataByBaseCount[$BaseCount2] = [
                    'BaseCount' => $BaseCount2,
                    'Weight' => 0,
                ];
            }

            $weightForBaseCount1 = $Weight * ($BaseCount2 - $Count) / 10;
            $weightForBaseCount2 = $Weight - $weightForBaseCount1;

            $dataByBaseCount[$BaseCount1]['Weight'] += $weightForBaseCount1;
            $dataByBaseCount[$BaseCount2]['Weight'] += $weightForBaseCount2;

            $TotalWeight += $Weight;
            $AryArrivalID[] = $dataRow['RmArrivalID'];
        }

        $i = 0;
        foreach ($dataByBaseCount as $item) {
            if ($item['Weight'] > 0) {
                $ArrayAssumedList['SNO'] = ++$i;
                $ArrayAssumedList['BaseCount'] = $item['BaseCount'];
                $ArrayAssumedList['Weight'] = number_format($item['Weight'], 3);
                $ArrayData[] = $ArrayAssumedList;
            }
        }

        $Data['TotalCountGroupWeight'] = $TotalWeight;
        $Data['List'] = $ArrayData;

        return $Data;
    }

    function largestMultipleOfTenLessThan($number)
    {
        return floor($number - ($number % 10));
    }

    function smallestMultipleOfTenGreaterThan($number)
    {
        return floor($number + (10 - ($number % 10)));
    }

    function getAssumedGradingData($RequestBody)
    {
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
        $TrackID = $RequestBody['TrackID'];
        $AryArrivalID = implode(',', $RequestBody['AryArrivalID']);


        $Query = '';
        if ($ReceivingPlantID != '') {
            $Query .= " AND RmRD.ReceivingPlantID='$ReceivingPlantID'";
        }
        if ($TrackID != '') {
            $Query .= " AND PHGD.TrackID='$TrackID'";
        }

        $sqlFields = "PHGB.VarietyID, HLCount,HLWeight,PackingGrade,ShortName,PHGD.TrackID,PHGB.Treatment, SUM(HLWeight) AS Weight,PHVL.Species";
        $sqlQuery = "SELECT $sqlFields FROM PpcHLGradingBreakup AS PHGB
        LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID
        LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID = PHGB.LotID
        LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
        LEFt JOIN RmLotDetatils AS RLD ON RLD.RmLotID=PHGD.LotID
        WHERE RmRD.ArrivalDate='$ArrivalDate' and RmReferenceID in ($AryArrivalID) $Query GROUP BY PHGB.VarietyID, HLCount, PackingGrade, PHGB.Treatment";
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayAssumedData = [];
        $TotalGradingWeight = 0;
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayList['SNO'] = ++$i;
            $ArrayList['VarietyID'] = $dataRow['VarietyID'];
            $ArrayList['HLCount'] = $dataRow['HLCount'];
            // $ArrayList['TrackID'] = $dataRow['TrackID'];
            $ArrayList['HLWeight'] = $dataRow['HLWeight'];
            $ArrayList['PackingGrade'] = $dataRow['PackingGrade'];
            $ArrayList['ShortName'] = $dataRow['ShortName'] . "-" . $dataRow['Species'];
            $ArrayList['Treatment'] = $dataRow['Treatment'];
            $ArrayList['Weight'] = $dataRow['Weight'];
            $TotalGradingWeight += $dataRow['Weight'];

            //  $Totals['TotalArrivalWeight'] += $dataRow['ArrivalWeight'];


            $ArrayAssumedData[] = $ArrayList;
        }

        $Data['TotalGradingWeight'] = $TotalGradingWeight;
        $Data['List'] = $ArrayAssumedData;

        return $Data;
    }
    // public function getTotals($ArrivalID)
    // {

    //     $sqlHONQuery = "SELECT sum(Weight) as HonWeight FROM `RmLotDetatils` as RLD
    // 	LEFT JOIN RmArrivalBreakUpDetails as RABD on RABD.ArrivalBrkUpID=RLD.BreakUpID WHERE RmLotID in ($ArrivalID)";
    //     $stmtHON = $this->conn->prepare($sqlHONQuery);
    //     $stmtHON->execute();

    //     $dataRowHON = $stmtHON->fetch(PDO::FETCH_ASSOC);
    //     $TotalHON = $dataRowHON['HonWeight'];

    //     $sqlHLQuery = "SELECT SUM(HLWeight) AS TotalQty FROM PpcHLGradingBreakup WHERE LotID in ($ArrivalID)";
    //     $stmtHL = $this->conn->prepare($sqlHLQuery);
    //     $stmtHL->execute();
    //     $dataRowHL = $stmtHL->fetch(PDO::FETCH_ASSOC);
    //     $TotalHL = $dataRowHL['TotalQty'];

    //     $Yield = $TotalHL / $TotalHON * 100;

    //     $ArrayTotals['TotalHON'] = $TotalHON;
    //     $ArrayTotals['TotalHL'] = number_format($TotalHL, 3);
    //     $ArrayTotals['Yield'] = round($Yield, 2);

    //     return $ArrayTotals;
    // }

}
