<?php


include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once 'Controller/GradingOverallReportController.php';

class DeheadingOverallReportController
{
    private $conn;
    private $utilController;
    private $gradingOverallController;
    private $Error = null;
    private $Msg = '';

    public function __construct($db)
    {
        $this->conn = $db;
        $this->gradingOverallController = new GradingOverallReportController($this->conn);
    }

    public function getDeheadingDetails($RequestBody)
    {
        $StartDate = $RequestBody['StartDate'];
        $EndDate = $RequestBody['EndDate'];
        $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
        $MergeHonLoose = $RequestBody['MergeHonLoose'];
        $V2reportFormat = $RequestBody['V2reportFormat'];

        $DateFilter = "RmRD.ArrivalDate BETWEEN '$StartDate' AND '$EndDate'";

        $Query = '';
        if ($ReceivingPlantID != '') {
            $Query .= " AND RmRD.ReceivingPlantID='$ReceivingPlantID'";
        }

        $sqlFields = "RmRD.ReceivingID, RmRD.ArrivalDate, RmRD.ReceivingPlantID,PDH.GradingPlantID,RPS2.StationName as GradingPlant,
		RmPS.FullName, RmPS.StationName, RmRD.TrackCode, RmRD.TrackNumber,RmRD.Remarks";

        $sqlReceivedQuery = "SELECT  $sqlFields FROM `RmReceivingDetails` AS RmRD
       LEFT JOIN RmReceivingBreakup AS RRCB ON RRCB.ReceivingID=RmRD.ReceivingID 
       LEFT JOIN PpcDeheading AS PDH ON PDH.TrackBreakUpID=RRCB.ReceivalBreakupID 
		LEFT JOIN RmProcessingStations AS RmPS ON PDH.PlantID=RmPS.ProcessingStationID
		LEFT JOIN RmUsersList AS RmUL ON RmRD.SupervisorID=RmUL.ID 
        LEFT JOIN RmProcessingStations AS RPS2 ON RPS2.ProcessingStationID=PDH.GradingPlantID
       
      
		 WHERE $DateFilter $Query and IsStoreMaterial=0 GROUP BY RmRD.ReceivingID ORDER BY RmRD.ArrivalDate,RmRD.TrackNumber ASC ";

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

        $ArrayDeheadingData = [];
        $GrandTotals = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ReceivingID = $dataRow['ReceivingID'];
            $BreakUpData = $this->getDeheadingBreakUp($ReceivingID, $MergeHonLoose, $V2reportFormat);
            $ArrivalDate = !empty($dataRow['ArrivalDate']) ? date('d-m-Y', strtotime($dataRow['ArrivalDate'])) : '';

            $ArrayDeheadingDetails['SNo'] = ++$i;
            $ArrayDeheadingDetails['ArrivalDate'] = $ArrivalDate;
            $ArrayDeheadingDetails['TrackCode'] = $dataRow['TrackCode'];
            $ArrayDeheadingDetails['TrackNumber'] = $dataRow['TrackNumber'];
            $ArrayDeheadingDetails['Remarks'] = $dataRow['Remarks'];
            $ArrayDeheadingDetails['GradingLocation'] = $dataRow['GradingPlant'];
            $ArrayDeheadingDetails['FullName'] = $dataRow['FullName'];
            $ArrayDeheadingDetails['StationName'] = $dataRow['StationName'];
            $ArrayDeheadingDetails['BreakUpData'] = $BreakUpData['BreakUpData'];

            // Get totals from BreakUpData
            $ArrivalGrandTotals = $BreakUpData['Totals']['TotalArrivalWeight'];
            $TotalGradingWeight = $BreakUpData['Totals']['TotalGradingWeight'];
            $TotalGradingHON = $BreakUpData['Totals']['TotalGradingHON'];
            $TotalSampleHLQty = $BreakUpData['Totals']['TotalSampleHLQty'];

            // Accumulate grand totals with formatted output
            $GrandTotals['ArrivalQtyTotals'] += $ArrivalGrandTotals;
            $GrandTotals['TotalGradingWeight'] += $TotalGradingWeight;
            $GrandTotals['TotalGradingHON'] += $TotalGradingHON;
            $GrandTotals['TotalSampleHLQty'] += $TotalSampleHLQty;

            $ArrayDeheadingData[] = $ArrayDeheadingDetails;
        }

        // Format grand totals to display three decimal places
        $GrandTotals['ArrivalQtyTotals'] = number_format($GrandTotals['ArrivalQtyTotals'], 3, '.', '');
        $GrandTotals['TotalGradingWeight'] = number_format($GrandTotals['TotalGradingWeight'], 3, '.', '');
        $GrandTotals['TotalGradingHON'] = number_format($GrandTotals['TotalGradingHON'], 3, '.', '');
        $GrandTotals['TotalSampleHLQty'] = number_format($GrandTotals['TotalSampleHLQty'], 3, '.', '');

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['Data'] = $ArrayDeheadingData;
        $Data['GrandTotals'] = $GrandTotals;

        return $Data;

    }


    function getDeheadingBreakUp($ReceivingID, $MergeHonLoose, $V2reportFormat = 0)
    {
        $v2ReportFields = '';
        if($V2reportFormat == 1){
            $v2ReportFields = ",PVS.UnitName,DATE_FORMAT(PDH.ProcessingDate, '%d-%m-%Y') AS DeheadingDate,PDH.CheckingCount,PDH.CheckingCount2,PDH.TotalDeheadingWeight,RRB.ReceivingQty,PDH.HonQty";
        }
        if ($MergeHonLoose == 1) {
            $sqlFields = "DHID,ProcessingDate,ArrivalNumber,CheckingCount,CountAfterDH,HonWeight, PHGB.MixIDs,
                WtAfterDH,NetCount,IceConsumption,PDH.StationID,PDH.PlantID,PDH.LotID,PDH.ShiftID,PDH.SupervisorID,GROUP_CONCAT(RABD.Count) as ArrivalCount,
                SUM(RABD.Weight) as ArrivalWeight,RABD.Variety as ArrivalVariety,RAD.RmArrivalID as ArrivalID,ReceivalBreakupID,
                PDH.Remarks,RAD.Species,GROUP_CONCAT(CONCAT(RAD.Species, '-', RABD.Variety)) as OverallVariety
                $v2ReportFields";

            $sqlQuery = "SELECT  $sqlFields FROM RmArrivalDetatils AS RAD
				LEFT JOIN RmArrivalBreakUpDetails AS RABD ON RAD.RmArrivalID = RABD.ArrivalID 
				LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalID = RABD.ArrivalID
                LEFT JOIN RmReceivingDetails as RRD on RRD.ReceivingID=RRB.ReceivingID
                LEFT JOIN PpcDeheading AS PDH ON PDH.TrackBreakUpID =RRB.ReceivalBreakupID 
                LEFT JOIN PpcHLGradingDetails AS PHGB ON PHGB.LotID = PDH.LotID AND PDH.TrackID = PHGB.TrackID
                LEFT JOIN PpcVAStations AS PVS ON PVS.StationID=PDH.StationID 
                LEFT JOIN RmUsersList AS RUL ON RUL.ID=PDH.SupervisorID 
                LEFT JOIN PpcOptions AS PPO ON PPO.OptID=PDH.ShiftID 
                LEFT JOIN RmProcessingStations AS RPS ON RPS.ProcessingStationID=PDH.PlantID
                
                WHERE RRD.ReceivingID='$ReceivingID' AND PDH.LotID != 0 AND PDH.CreatedFrom = 1 GROUP BY RAD.ArrivalNumber order by ArrivalBrkUpID asc";

        } else {
            $sqlFields = "DHID,ProcessingDate,ArrivalNumber,CheckingCount,CountAfterDH,HonWeight, PHGB.MixIDs,
                WtAfterDH,NetCount,IceConsumption,PDH.StationID,PDH.PlantID,PDH.LotID,PDH.ShiftID,PDH.SupervisorID,RABD.Count as ArrivalCount,
                RABD.Weight as ArrivalWeight,RABD.Variety as ArrivalVariety,RAD.RmArrivalID as ArrivalID,ReceivalBreakupID,
                PDH.Remarks,RAD.Species,CONCAT(RAD.Species, '-', RABD.Variety) as OverallVariety
                $v2ReportFields";

            $sqlQuery = "SELECT  $sqlFields FROM RmArrivalBreakUpDetails AS RABD 
                LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID=RABD.ArrivalID 
                LEFT JOIN RmReceivingBreakup  AS RRB ON RRB.ArrivalBreakupID=RABD.ArrivalBrkUpID
                LEFT JOIN RmReceivingDetails as RRD on RRD.ReceivingID=RRB.ReceivingID
                LEFT JOIN PpcDeheading AS PDH ON PDH.TrackBreakUpID =RRB.ReceivalBreakupID 
                LEFT JOIN PpcHLGradingDetails AS PHGB ON PHGB.LotID = PDH.LotID AND PDH.TrackID = PHGB.TrackID
                LEFT JOIN PpcVAStations AS PVS ON PVS.StationID=PDH.StationID 
                LEFT JOIN RmUsersList AS RUL ON RUL.ID=PDH.SupervisorID 
                LEFT JOIN PpcOptions AS PPO ON PPO.OptID=PDH.ShiftID 
                LEFT JOIN RmProcessingStations AS RPS ON RPS.ProcessingStationID=PDH.PlantID
                
                WHERE RRD.ReceivingID='$ReceivingID' AND PDH.CreatedFrom = 1  order by ArrivalBrkUpID asc";


        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $i = 0;
        $Totals = [];
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $Yield = ($dataRow['WtAfterDH'] / $dataRow['HonWeight']) * 100;
            $MixedID = $dataRow['MixIDs'];
            $LotID = $dataRow['LotID'];

            $DeheadingData['SNo'] = ++$i;
            $DeheadingData['ReceivalBreakUpID'] = $dataRow['ReceivalBreakupID'];
            $DeheadingData['DeheadingBreakUpID'] = $dataRow['DHID'];
            $DeheadingData['LotID'] = $dataRow['LotID'];
            $DeheadingData['MixedID'] = $dataRow['MixIDs'];
            $DeheadingData['ArrivalID'] = $dataRow['ArrivalID'];
            $DeheadingData['BillNumber'] = $dataRow['ArrivalNumber'];
            $DeheadingData['HLCount'] = $dataRow['CountAfterDH'];
            $DeheadingData['HLQuantityKgs'] = number_format($dataRow['WtAfterDH'], 2);
            $DeheadingData['HonQuantity'] = number_format($dataRow['HonWeight'], 3);
            $DeheadingData['ArrivalWeight'] = number_format($dataRow['ArrivalWeight'], 3);
            $DeheadingData['ArrivalCount'] = number_format($dataRow['ArrivalCount'], 2);
            $DeheadingData['ArrivalVariety'] = $dataRow['ArrivalVariety'] . "-" . $dataRow['Species'];
            $DeheadingData['OverallVariety'] = $dataRow['OverallVariety'];
            $DeheadingData['Yield'] = round($Yield, 2);
            $Yield = round($Yield, 2);

            $NoofPcs = $dataRow['CountAfterDH'];
            $HLSampleQty = number_format($dataRow['WtAfterDH'], 3);
            $HonSampleQty = number_format($dataRow['HonWeight'], 3);

            $HonCount = round($NoofPcs / $HonSampleQty, 2);
            $HLCount = round(($NoofPcs / $HLSampleQty) * 0.454, 2);

            $DeheadingData['ctHonCount'] = number_format($HonCount, 2);
            $DeheadingData['ctHLCount'] = number_format($HLCount, 2);

            $GradingData = $this->getTotals($MixedID, $LotID, $MergeHonLoose);

            if (strpos($MixedID, ',') !== false) {
                $DeheadingData['GradingTotalHON'] = round($GradingData['TotalHON'], 2);
                $DeheadingData['GradingTotalHL'] = round($GradingData['TotalHL'], 2);
                $DeheadingData['GradingTotalSampleHL'] = round($GradingData['TotalSampleHL'], 2);
            } else {
                $DeheadingData['GradingTotalHON'] = $GradingData['TotalHON'];
                $DeheadingData['GradingTotalHL'] = $GradingData['TotalHL'];
                $DeheadingData['GradingTotalSampleHL'] = $GradingData['TotalSampleHL'];
            }
            $DeheadingData['GradingYield'] = $GradingData['Yield'];
            $GradingYield = $GradingData['Yield'];
            $IsMixRef = $GradingData['IsMixRef'];
            $DeheadingData['RefArrival'] = $IsMixRef;


            if ($GradingYield != 'Mix') {
                $DeheadingData['YieldDiff'] = round($GradingYield - $Yield, 2);
            } else {
                $DeheadingData['YieldDiff'] = 'Mix';
            }

            $Totals['TotalArrivalWeight'] += $dataRow['ArrivalWeight'];
            $Totals['TotalGradingWeight'] += $GradingData['TotalHL'];
            $Totals['TotalGradingHON'] += $GradingData['TotalHON'];
            $Totals['TotalSampleHLQty'] += $dataRow['WtAfterDH'];

            if($V2reportFormat == 1){                
                $HLYield = 0;
                if($dataRow['TotalDeheadingWeight'] != 0.00 && !empty($dataRow['ReceivingQty'])){
                    $HLYield = ($dataRow['TotalDeheadingWeight'] / $dataRow['ReceivingQty']) * 100;
                }
                
                $DeheadingData['StationName'] = $dataRow['UnitName'];
                $DeheadingData['DeheadingDate'] = $dataRow['DeheadingDate'];
                $DeheadingData['CheckingCount'] = $dataRow['CheckingCount'];
                $DeheadingData['CheckingCount2'] = $dataRow['CheckingCount2'];
                $DeheadingData['HLYield'] = round($HLYield, 2);
                $HonReturn = 0;
                if(!empty((float)$dataRow['HonQty'])){
                    $HonReturn = (float)$dataRow['ReceivingQty'] - (float)$dataRow['HonQty'];
                }
                $DeheadingData['HonQty'] = (float)$dataRow['HonQty'];
                $DeheadingData['HonReturn'] = (float)$HonReturn;
                if(!empty($dataRow['LotID'])){
                    $LotSql = "SELECT RMG.UserName AS Grader,RMA.UserName AS Agent
                                FROM RmLotDetatils RLD
                                LEFT JOIN RmUsersList AS RMG ON RMG.ID = RLD.GraderID
                                LEFT JOIN RmUsersList AS RMA ON RMA.ID = RLD.AgentID
                                WHERE RmLotID = ".$dataRow['LotID'];
                    $stmtLot = $this->conn->prepare($LotSql);
                    $stmtLot->execute();
                    $LotRes = $stmtLot->fetch(PDO::FETCH_ASSOC);
                    $DeheadingData['Grader'] = $LotRes['Grader'];
                    $DeheadingData['Agent'] = $LotRes['Agent']; 
                }
            }
            $ArrayData[] = $DeheadingData;
        }

        $data['BreakUpData'] = $ArrayData;
        $data['Totals'] = $Totals;
        return $data;
    }

    public function getTotals($ArrivalID, $LotID, $MergeHonLoose)
    {

        $sqlHLQuery = "SELECT SUM(HLWeight) AS TotalQty FROM PpcHLGradingBreakup WHERE LotID ='$LotID'";
        $stmtHL = $this->conn->prepare($sqlHLQuery);
        $stmtHL->execute();
        $dataRowHL = $stmtHL->fetch(PDO::FETCH_ASSOC);
        $TotalHLRef = $dataRowHL['TotalQty'];

        if ($MergeHonLoose == 1) {
            $sqlHONQuery = "SELECT sum(Weight) as HonWeight ,GROUP_CONCAT(RLD.LotNumber) as LotNumber FROM `RmLotDetatils` as RLD
            LEFT JOIN RmArrivalBreakUpDetails as RABD on RABD.ArrivalID=RLD.RmReferenceID WHERE RmLotID in ($ArrivalID)";

        } else {

            $sqlHONQuery = "SELECT sum(Weight) as HonWeight ,GROUP_CONCAT(RLD.LotNumber) as LotNumber FROM `RmLotDetatils` as RLD
            LEFT JOIN RmArrivalBreakUpDetails as RABD on RABD.ArrivalBrkUpID=RLD.BreakUpID WHERE RmLotID in ($ArrivalID)";

        }
        $stmtHON = $this->conn->prepare($sqlHONQuery);
        $stmtHON->execute();

        $dataRowHON = $stmtHON->fetch(PDO::FETCH_ASSOC);
        $TotalHON = $dataRowHON['HonWeight'];
        $LotNumber = $dataRowHON['LotNumber'];

        $sqlHLQuery = "SELECT SUM(HLWeight) AS TotalQty FROM PpcHLGradingBreakup WHERE LotID in ($ArrivalID) AND CreatedFrom=1";
        $stmtHL = $this->conn->prepare($sqlHLQuery);
        $stmtHL->execute();
        $dataRowHL = $stmtHL->fetch(PDO::FETCH_ASSOC);
        $TotalHL = $dataRowHL['TotalQty'];

        $Yield = $TotalHL / $TotalHON * 100;
        if ($TotalHLRef > 0) {
            $ArrayTotals['TotalHON'] = $TotalHON;
            $ArrayTotals['TotalHL'] = round($TotalHL, 2);
            $ArrayTotals['Yield'] = round($Yield, 2);

        } else {
            $ArrayTotals['TotalHON'] = '0';
            $ArrayTotals['TotalHL'] = '0';
            $ArrayTotals['Yield'] = round($Yield, 2);
            $ArrayTotals['IsMixRef'] = false;
        }

        // Fetch total sample HL weight for the specified ArrivalIDs
        $sqlSampleHLQuery = "SELECT SUM(HLWeight) AS TotalSampleQty FROM PpcSampleHLBreakup WHERE LotID IN ($ArrivalID)";
        $stmtSampleHL = $this->conn->prepare($sqlSampleHLQuery);
        $stmtSampleHL->execute();
        $dataRowSampleHL = $stmtSampleHL->fetch(PDO::FETCH_ASSOC);
        $TotalSampleHLQty = $dataRowSampleHL['TotalSampleQty'];
        //$TotalSampleHLQty = !empty($dataRowSampleHL['TotalSampleQty']) ? $dataRowSampleHL['TotalSampleQty'] : 0;

        $ArrayTotals['TotalSampleHLQty'] = round($TotalSampleHLQty, 2);

        if ($ArrivalID != $LotID) {
            $ArrayTotals['IsMixRef'] = str_replace(',', '<br>', $LotNumber);
        } else {
            $ArrayTotals['IsMixRef'] = '';
        }
        return $ArrayTotals;
    }
}
