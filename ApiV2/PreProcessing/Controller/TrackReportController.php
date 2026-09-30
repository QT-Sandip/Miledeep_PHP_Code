<?php
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class TrackReportController
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
    public function getTrackReportDetails($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
        $Status = $RequestBody['Status'];
        $RecordsPerPage = 100;
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $MergeHonLoose = $RequestBody['mergeHonLoose'];

        $Query = '';
        if ($ReceivingPlantID != '') {
            $Query .= " AND RmRD.ReceivingPlantID = '$ReceivingPlantID'";
        }

        $DateFilter = "RmRD.ArrivalDate BETWEEN '$FromDate' AND '$ToDate'";

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            //$SearchQuery = " and CONCAT_WS('', TrackNumber) LIKE '%$SearchKey%'";
             $SearchQuery = " and CONCAT_WS('', TrackCode) LIKE '%$SearchKey%'
            OR DATE_FORMAT(RmRD.ArrivalDate, '%d-%m-%Y') LIKE '%$SearchKey%'
            OR EXISTS (
                SELECT 1 
                FROM RmArrivalBreakUpDetails RABD
                LEFT JOIN RmArrivalDetatils RAD ON RAD.RmArrivalID = RABD.ArrivalID
                LEFT JOIN RmReceivingBreakup RRB ON RRB.ArrivalBreakupID = RABD.ArrivalBrkUpID
                WHERE RRB.ReceivingID = RmRD.ReceivingID
                AND RAD.ArrivalNumber LIKE '%$SearchKey%'
            )";
        }

        $sqlFields = "RmRD.ReceivingID, RmRD.ArrivalDate, RmRD.VehicleID, RmRD.VehicleNumber, RmRD.ReceivingPlantID, 
		RmPS.FullName, RmPS.StationName, RmRD.TrackCode, RmRD.TrackNumber,RmRD.SealNo, RmRD.SupervisorID, RmUL.UserName, RmRD.Remarks, 
		RmPS.ProdCode as PlantCode";

        $sqlReceivedQuery = "SELECT  $sqlFields FROM `RmReceivingDetails` AS RmRD
		LEFT JOIN RmProcessingStations AS RmPS ON RmRD.ReceivingPlantID=RmPS.ProcessingStationID
		LEFT JOIN RmUsersList AS RmUL ON RmRD.SupervisorID=RmUL.ID 
		WHERE $DateFilter $Query AND IsStoreMaterial=0 $SearchQuery
        ORDER BY RmRD.ArrivalDate ,RmRD.TrackNumber ASC " . $Limit;


        $sqlCountQuery = "SELECT COUNT(*) as TotalCount 
FROM `RmReceivingDetails` AS RmRD
LEFT JOIN RmProcessingStations AS RmPS ON RmRD.ReceivingPlantID=RmPS.ProcessingStationID
LEFT JOIN RmUsersList AS RmUL ON RmRD.SupervisorID=RmUL.ID 
WHERE $DateFilter $Query AND IsStoreMaterial=0 $SearchQuery";

        $stmtCount = $this->conn->prepare($sqlCountQuery);
        $stmtCount->execute();
        $countResult = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $countResult['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $stmtSelectReceived = $this->conn->prepare($sqlReceivedQuery);
        $stmtSelectReceived->execute();

        // $TotalCount = $stmtSelectReceived->rowCount();
        // $PageCount = ceil($TotalCount / $RecordsPerPage);

        $ArrayReceivedData = [];
        $i = 0;

        $TotalArrivalWeight = 0;
        $TotalReceivingWeight = 0;
        $TotalDeheadingWeight = 0;
        $TotalGradingWeight = 0;
        $TotalFreezingQtyF1 = 0;
        $TotalSoakingInQtyS1 = 0;
        $TotalVaGradingQty = 0;
        $TotalValueAdditionInQty = 0;
        $TotalValueAdditionBalanceQty = 0;
        $TotalValueAdditionOutQty = 0;
        $TotalFreezingQtyF2 = 0;
        $TotalSoakingInQtyS2 = 0;
        $TotalSoakingInQtyS12 = 0;
        $TotalSoakInQty = 0;
        $TotalSoakingBalanceQty = 0;
        $TotalSoakOutQty = 0;
        $TotalFreezingQtyF2S3 = 0;
        $TotalFreezingInQty = 0;
        $TotalFreezingBalanceQty = 0;
        $TotalStoreInQty = 0;
        $TotalStoreInwardQty = 0;
        $TotalStoreInwardBalanceQty = 0;
        $TotalStoreOutwardQty = 0;

        $TotalBalanceQuantity = 0;
        while ($dataRow = $stmtSelectReceived->fetch(PDO::FETCH_ASSOC)) {

            $ReceivingID = $dataRow['ReceivingID'];
            $ReceivingPlantID = $dataRow['ReceivingPlantID'];

            $BreakUpData = $this->getRmReceivedBreakupDetails($ReceivingID, $ReceivingPlantID, $MergeHonLoose);
            $ArrivalWeight = $BreakUpData['ArrivalWeight'];
            $DeheadingWeight = $this->getDeheadingWeight($ReceivingID, $ReceivingPlantID)['DeheadingWeight'];
            $GradingWeight = $this->getGradingWeight($ReceivingID, $ReceivingPlantID)['GradingWeight'];

            $GradingYield = ($GradingWeight / $BreakUpData['ArrivalWeight']) * 100; //
            $FreezingQtyF1 = $this->getFreezingQtyF1($ReceivingID, $ReceivingPlantID)['FreezingQty']; //
            $SoakingInQtyS1 = $this->getSoakingInQtyS1($ReceivingID, $ReceivingPlantID)['SoakingInQty']; //
            $VaGradingQty = $this->getVaGradingQty($ReceivingID, $ReceivingPlantID)['VaGradingQty']; //
            $ValueAdditionInQty = $this->getValueAdditionInQty($ReceivingID, $ReceivingPlantID)['ValueAdditionInQty']; // 
            $ValueAdditionBalanceQty = $ValueAdditionInQty - $VaGradingQty; //
            $ValueAdditionOutQty = $this->getValueAdditionOutQty($ReceivingID, $ReceivingPlantID)['ValueAdditionOutQty']; // 
            $ValueAdditionYield = ($ValueAdditionOutQty / $ValueAdditionInQty) * 100; //
            $FreezingQtyF2 = $this->getFreezingQtyF2($ReceivingID, $ReceivingPlantID)['FreezingVAQty']; // 
            $SoakingInQtyS2 = $this->getSoakingQtyS2($ReceivingID, $ReceivingPlantID)['SoakingVAQty']; //
            $SoakingInQtyS12 = $SoakingInQtyS1 + $SoakingInQtyS2; //
            $SoakInQty = $this->getSoakingInQty($ReceivingID, $ReceivingPlantID)['SoakInQty']; //
            $SoakingBalanceQty = $SoakInQty - $SoakingInQtyS12; //
            $SoakOutQty = $this->getSoakingOutQty($ReceivingID, $ReceivingPlantID)['SoakOutQty']; //
            $SoakingPickUpQty = $SoakOutQty - $SoakInQty; //
            $SoakingPickUpYield = ($SoakOutQty / $SoakInQty) * 100;
            $FreezingQtyF2S3 = $SoakOutQty + $FreezingQtyF1 + $FreezingQtyF2; //
            $FreezingInQty = $this->getFreezingInQty($ReceivingID, $ReceivingPlantID)['FreezingInQty']; //
            $FreezingRejectionWeight = $this->getFreezingInQty($ReceivingID, $ReceivingPlantID)['RejectionWeight']; //
            $FreezingYield = (($FreezingInQty + $FreezingRejectionWeight) / $ArrivalWeight) * 100;
            $StoreInQty = $this->getStoreInQty($ReceivingID, $ReceivingPlantID)['StoreInQty']; //
            $StoreInwardQty = $this->getStoreInwardQty($ReceivingID, $ReceivingPlantID)['StoreInwardQty']; //
            $StoreInwardBalanceQty = $StoreInwardQty - $StoreInQty;
            $StoreOutwardQty = $this->getStoreOutwardQty($ReceivingID, $ReceivingPlantID)['StoreOutwardQty']; //
            $StoreBalanceQty = $StoreInwardQty - $StoreOutwardQty;

            $ArrivalDate = !empty($dataRow['ArrivalDate']) ? date('d-m-Y', strtotime($dataRow['ArrivalDate'])) : '';

            $ValueAdditionBalanceQtyColor = '';
            if ($ValueAdditionBalanceQty > 0) {
                $ValueAdditionBalanceQtyColor = 'green';
            } else if ($ValueAdditionBalanceQty < 0) {
                $ValueAdditionBalanceQtyColor = 'red';
            }

            $SoakingBalanceQtyColor = '';
            if ($SoakingBalanceQty > 0) {
                $SoakingBalanceQtyColor = 'green';
            } else if ($SoakingBalanceQty < 0) {
                $SoakingBalanceQtyColor = 'red';
            }

            $StoreInwardBalanceQtyColor = '';
            if ($StoreInwardBalanceQty > 0) {
                $StoreInwardBalanceQtyColor = 'green';
            } else if ($StoreInwardBalanceQty < 0) {
                $StoreInwardBalanceQtyColor = 'red';
            }

            $FreezingBalanceQty = $FreezingQtyF2S3 - $FreezingInQty;

            $ArrayReceivedDetails['SNo'] = ++$i;
            $ArrayReceivedDetails['TrackCode'] = $dataRow['TrackCode'];
            $ArrayReceivedDetails['ArrivalDate'] = $ArrivalDate;
            $ArrayReceivedDetails['BillNumber'] = $BreakUpData['ArrivalNumber'];
            $ArrayReceivedDetails['Count'] = $BreakUpData['ArrivalCount'];
            $ArrayReceivedDetails['ArrivalWeight'] = number_format($BreakUpData['ArrivalWeight'], 3);
            $ArrayReceivedDetails['ReceivingWeight'] = number_format($BreakUpData['ReceivingWeight'], 3);
            $ArrayReceivedDetails['DeheadingWeight'] = number_format($DeheadingWeight, 3);
            $ArrayReceivedDetails['GradingWeight'] = number_format($GradingWeight, 3);
            $ArrayReceivedDetails['GradingYield'] = number_format($GradingYield, 2); //
            $ArrayReceivedDetails['FreezingQtyF1'] = number_format($FreezingQtyF1, 3); //
            $ArrayReceivedDetails['SoakingInQtyS1'] = number_format($SoakingInQtyS1, 3); //
            $ArrayReceivedDetails['VaGradingQty'] = number_format($VaGradingQty, 3); //
            $ArrayReceivedDetails['ValueAdditionInQty'] = number_format($ValueAdditionInQty, 3); //
            $ArrayReceivedDetails['ValueAdditionBalanceQty'] = number_format($ValueAdditionBalanceQty, 3); //
            $ArrayReceivedDetails['ValueAdditionBalanceQtyColor'] = $ValueAdditionBalanceQtyColor; //
            $ArrayReceivedDetails['ValueAdditionOutQty'] = number_format($ValueAdditionOutQty, 3); //
            $ArrayReceivedDetails['ValueAdditionYield'] = number_format($ValueAdditionYield, 2); //
            $ArrayReceivedDetails['FreezingQtyF2'] = number_format($FreezingQtyF2, 3); //
            $ArrayReceivedDetails['SoakingInQtyS2'] = number_format($SoakingInQtyS2, 3); //
            $ArrayReceivedDetails['SoakingInQtyS12'] = number_format($SoakingInQtyS12, 3); //
            $ArrayReceivedDetails['SoakInQty'] = number_format($SoakInQty, 3);
            $ArrayReceivedDetails['SoakingBalanceQty'] = number_format($SoakingBalanceQty, 3);
            $ArrayReceivedDetails['SoakingBalanceQtyColor'] = $SoakingBalanceQtyColor; //
            $ArrayReceivedDetails['SoakOutQty'] = number_format($SoakOutQty, 3); //
            $ArrayReceivedDetails['SoakingPickUpQty'] = number_format($SoakingPickUpQty, 3); //
            $ArrayReceivedDetails['SoakingPickUpYield'] = number_format($SoakingPickUpYield, 2); //
            $ArrayReceivedDetails['FreezingQtyF2S3'] = number_format($FreezingQtyF2S3, 3); //
            $ArrayReceivedDetails['FreezingInQty'] = number_format($FreezingInQty, 3); //
            $ArrayReceivedDetails['RejectionWeight'] = number_format($FreezingRejectionWeight, 3); //
            $ArrayReceivedDetails['FreezingBalanceQty'] = number_format(($FreezingBalanceQty), 3);
            $ArrayReceivedDetails['FreezingYield'] = number_format(($FreezingYield), 2);
            $ArrayReceivedDetails['StoreInQty'] = number_format($StoreInQty, 3); //
            $ArrayReceivedDetails['StoreInwardQty'] = number_format($StoreInwardQty, 3); //
            $ArrayReceivedDetails['StoreInwardBalanceQty'] = number_format($StoreInwardBalanceQty, 3); //
            $ArrayReceivedDetails['StoreInwardBalanceQtyColor'] = $StoreInwardBalanceQtyColor; //
            $ArrayReceivedDetails['StoreOutwardQty'] = number_format($StoreOutwardQty, 3); //
            $ArrayReceivedDetails['StoreBalanceQty'] = number_format($StoreBalanceQty, 3); //

            $ArrayReceivedDetails['Yield'] = 'hold';
            $ArrayReceivedDetails['Status'] = 'hold';


            $TotalArrivalWeight += $BreakUpData['ArrivalWeight'];
            $TotalReceivingWeight += $BreakUpData['ReceivingWeight'];
            $TotalDeheadingWeight += $DeheadingWeight;
            $TotalGradingWeight += $GradingWeight;
            $TotalFreezingQtyF1 += $FreezingQtyF1;
            $TotalSoakingInQtyS1 += $SoakingInQtyS1;
            $TotalVaGradingQty += $VaGradingQty;
            $TotalValueAdditionInQty += $ValueAdditionInQty;
            $TotalValueAdditionBalanceQty += $ValueAdditionBalanceQty;
            $TotalValueAdditionOutQty += $ValueAdditionOutQty;
            $TotalFreezingQtyF2 += $FreezingQtyF2;
            $TotalSoakingInQtyS2 += $SoakingInQtyS2;
            $TotalSoakingInQtyS12 += $SoakingInQtyS12;
            $TotalSoakInQty += $SoakInQty;
            $TotalSoakingBalanceQty += $SoakingBalanceQty;
            $TotalSoakOutQty += $SoakOutQty;
            $TotalSoakingPickUpQty += $SoakingPickUpQty;
            $TotalFreezingQtyF2S3 += $FreezingQtyF2S3;
            $TotalFreezingInQty += $FreezingInQty;
            $TotalFreezingBalanceQty += $FreezingBalanceQty;
            $TotalStoreInQty += $StoreInQty;
            $TotalStoreInwardQty += $StoreInwardQty;
            $TotalStoreInwardBalanceQty += $StoreInwardBalanceQty;
            $TotalStoreOutwardQty += $StoreOutwardQty;
            $TotalStoreBalanceQty += $StoreBalanceQty;


            $ArrayReceivedData[] = $ArrayReceivedDetails;
        }
        $ArrayTotals['TotalArrivalWeight'] = number_format($TotalArrivalWeight, 3);
        $ArrayTotals['TotalReceivingWeight'] = number_format($TotalReceivingWeight, 3);
        $ArrayTotals['TotalDeheadingWeight'] = number_format($TotalDeheadingWeight, 3);
        $ArrayTotals['TotalGradingWeight'] = number_format($TotalGradingWeight, 3);
        $ArrayTotals['TotalFreezingQtyF1'] = number_format($TotalFreezingQtyF1, 3);
        $ArrayTotals['TotalSoakingInQtyS1'] = number_format($TotalSoakingInQtyS1, 3);
        $ArrayTotals['TotalVaGradingQty'] = number_format($TotalVaGradingQty, 3);
        $ArrayTotals['TotalValueAdditionInQty'] = number_format($TotalValueAdditionInQty, 3);
        $ArrayTotals['TotalValueAdditionBalanceQty'] = number_format($TotalValueAdditionBalanceQty, 3);
        $ArrayTotals['TotalValueAdditionOutQty'] = number_format($TotalValueAdditionOutQty, 3);
        $ArrayTotals['TotalFreezingQtyF2'] = number_format($TotalFreezingQtyF2, 3);
        $ArrayTotals['TotalSoakingInQtyS2'] = number_format($TotalSoakingInQtyS2, 3);
        $ArrayTotals['TotalSoakingInQtyS12'] = number_format($TotalSoakingInQtyS12, 3);
        $ArrayTotals['TotalSoakInQty'] = number_format($TotalSoakInQty, 3);
        $ArrayTotals['TotalSoakingBalanceQty'] = number_format($TotalSoakingBalanceQty, 3);
        $ArrayTotals['TotalSoakOutQty'] = number_format($TotalSoakOutQty, 3);
        $ArrayTotals['TotalSoakingPickUpQty'] = number_format($TotalSoakingPickUpQty, 3);
        $ArrayTotals['TotalFreezingQtyF2S3'] = number_format($TotalFreezingQtyF2S3, 3);
        $ArrayTotals['TotalFreezingInQty'] = number_format($TotalFreezingInQty, 3);
        $ArrayTotals['TotalFreezingBalanceQty'] = number_format($TotalFreezingBalanceQty, 3);
        $ArrayTotals['TotalStoreInQty'] = number_format($TotalStoreInQty, 3);
        $ArrayTotals['TotalStoreInwardQty'] = number_format($TotalStoreInwardQty, 3);
        $ArrayTotals['TotalStoreInwardBalanceQty'] = number_format($TotalStoreInwardBalanceQty, 3);
        $ArrayTotals['TotalStoreOutwardQty'] = number_format($TotalStoreOutwardQty, 3);
        $ArrayTotals['TotalStoreBalanceQty'] = number_format($TotalStoreBalanceQty, 3);


        if (count($ArrayReceivedData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['TotalCount'] = $TotalCount;
        $Data['RemainingCount'] = "";
        $Data['EditLink'] = "";
        $Data['PageCount'] = $PageCount;
        $Data['RecordsAval'] = $RecordsAval;
        $Data['Data'] = $ArrayReceivedData;
        $Data['Totals'] = $ArrayTotals;


        return $Data;
    }

    public function getRmReceivedBreakupDetails($ReceivingID, $ReceivingPlantID, $MergeHonLoose)
    {
        $Query = "";
        if ($ReceivingPlantID != '' && $ReceivingPlantID != '0') {
            $Query = " and ProcessingStationID='$ReceivingPlantID'";
        }

        if ($MergeHonLoose == 1) {
            $sqlFields = "GROUP_CONCAT(distinct RAD.ArrivalNumber SEPARATOR '<br>') as ArrivalNumber,
            GROUP_CONCAT(distinct FORMAT(RABD.Count,2) SEPARATOR ',') as ArrivalCount,
            sum(RABD.Weight) as ArrivalWeight, sum(ReceivingQty) as ReceivingWeight";

            $sqlBreakupData = "SELECT  $sqlFields FROM RmArrivalBreakUpDetails as RABD 
            left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RABD.ArrivalID 
            left join RmReceivingBreakup as RRB on RRB.ArrivalID=RABD.ArrivalID
            left join RmReceivingDetails as RRD on RRD.ReceivingID=RRB.ReceivingID
            WHERE RRD.ReceivingID='$ReceivingID' $Query GROUP BY RAD.ArrivalNumber;
            ORDER BY  ArrivalBrkUpID ASC ";
        } else {
            $sqlFields = "GROUP_CONCAT(distinct RAD.ArrivalNumber SEPARATOR '<br>') as ArrivalNumber,
            GROUP_CONCAT(distinct FORMAT(RABD.Count,2) SEPARATOR '<br>') as ArrivalCount,
            sum(RABD.Weight) as ArrivalWeight, sum(ReceivingQty) as ReceivingWeight";

            $sqlBreakupData = "SELECT  $sqlFields FROM RmArrivalBreakUpDetails as RABD 
            left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RABD.ArrivalID 
            left join RmReceivingBreakup as RRB on RRB.ArrivalBreakupID=RABD.ArrivalBrkUpID
            left join RmReceivingDetails as RRD on RRD.ReceivingID=RRB.ReceivingID
            WHERE RRD.ReceivingID='$ReceivingID' $Query;
            ORDER BY  ArrivalBrkUpID ASC ";
        }

        $stmtSelectBreakUpData = $this->conn->prepare($sqlBreakupData);
        $stmtSelectBreakUpData->execute();

        while ($dataRowBreakUpData = $stmtSelectBreakUpData->fetch(PDO::FETCH_ASSOC)) {
            $ArrayReceivedBreakupDetails['ArrivalNumber'] = $dataRowBreakUpData['ArrivalNumber'];
            $ArrayReceivedBreakupDetails['ArrivalCount'] = $dataRowBreakUpData['ArrivalCount'];
            $ArrayReceivedBreakupDetails['ArrivalWeight'] = $dataRowBreakUpData['ArrivalWeight'];
            $ArrayReceivedBreakupDetails['ReceivingWeight'] = $dataRowBreakUpData['ReceivingWeight'];
        }

        return $ArrayReceivedBreakupDetails;
    }

    public function getDeheadingWeight($ReceivingID, $ReceivingPlantID)
    {
        $Query = "";
        if ($ReceivingPlantID != '' && $ReceivingPlantID != '0') {
            $Query = " and PlantID='$ReceivingPlantID'";
        }
        $sqlFields = "sum(WtAfterDH) as DeheadingWeight";
        $sqlDeheadingWeight = "SELECT $sqlFields FROM `PpcDeheading` WHERE TrackID='$ReceivingID' $Query";
        $stmtDeheadingWeight = $this->conn->prepare($sqlDeheadingWeight);
        $stmtDeheadingWeight->execute();
        $dataRowDeheadingWeight = $stmtDeheadingWeight->fetch(PDO::FETCH_ASSOC);
        return $dataRowDeheadingWeight;
    }

    public function getGradingWeight($ReceivingID, $ReceivingPlantID)
    {
        $Query = "";
        if ($ReceivingPlantID != '' && $ReceivingPlantID != '0') {
            $Query = " and PlantID='$ReceivingPlantID'";
        }
        $sqlFields = "sum(HLWeight)as GradingWeight";
        $sqlGradingWeight = "SELECT $sqlFields FROM `PpcHLGradingDetails` as PHGD 
        left join PpcHLGradingBreakup as PHGB on PHGB.LotID=PHGD.LotID where TrackID='$ReceivingID' $Query";
        $stmtGradingWeight = $this->conn->prepare($sqlGradingWeight);
        $stmtGradingWeight->execute();
        $dataRowGradingWeight = $stmtGradingWeight->fetch(PDO::FETCH_ASSOC);
        return $dataRowGradingWeight;
    }

    // Query getting Soaking 'No(UNT)', IsBlock 'Yes' which is the direct Grading value to Freezing, (VA, Soaking Skip)
    public function getFreezingQtyF1($ReceivingID, $ReceivingPlantID)
    {
        $Query = "";
        if ($ReceivingPlantID != '' && $ReceivingPlantID != '0') {
            $Query = " and PlantID='$ReceivingPlantID' ";
        }
        $sqlFields = "sum(HLWeight)as FreezingQty";
        $sqlFreezingQtyF1 = "SELECT $sqlFields FROM `PpcHLGradingDetails` AS PHGD 
        LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID=PHGD.LotID 
        WHERE TrackID='$ReceivingID' $Query AND Treatment = 'UNT' AND IsBlock = 1 ";
        $stmtFreezingQtyF1 = $this->conn->prepare($sqlFreezingQtyF1);
        $stmtFreezingQtyF1->execute();
        $dataRowFreezingQtyF1 = $stmtFreezingQtyF1->fetch(PDO::FETCH_ASSOC);
        return $dataRowFreezingQtyF1;
    }

    // Query for getting Soaking 'Yes(Treated)', IsBlock 'Yes' which is the direct Grading value to Soaking, (VA Skip)
    public function getSoakingInQtyS1($ReceivingID, $ReceivingPlantID)
    {
        $Query = "";
        if ($ReceivingPlantID != '' && $ReceivingPlantID != '0') {
            $Query = " and PlantID='$ReceivingPlantID' ";
        }
        $sqlFields = "sum(HLWeight) AS SoakingInQty";
        $sqlSoakingInQtyS1 = "SELECT $sqlFields FROM `PpcHLGradingDetails` AS PHGD 
        LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID=PHGD.LotID 
        WHERE TrackID='$ReceivingID' $Query AND Treatment != 'UNT' AND IsBlock = 1 ";
        $stmtSoakingInQtyS1 = $this->conn->prepare($sqlSoakingInQtyS1);
        $stmtSoakingInQtyS1->execute();
        $dataRowSoakingInQtyS1 = $stmtSoakingInQtyS1->fetch(PDO::FETCH_ASSOC);
        return $dataRowSoakingInQtyS1;
    }

    // Query for getting Soaking 'Yes/No(Treated/UNT)', IsBlock 'No' which is the Grading value to Value Addition, (General Flow)
    public function getVaGradingQty($ReceivingID, $ReceivingPlantID)
    {
        $Query = "";
        if ($ReceivingPlantID != '' && $ReceivingPlantID != '0') {
            $Query = " and PlantID='$ReceivingPlantID' ";
        }
        $sqlFields = "sum(HLWeight) AS VaGradingQty";
        $sqlVaGradingQty = "SELECT $sqlFields FROM `PpcHLGradingDetails` AS PHGD 
        LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID=PHGD.LotID 
        WHERE TrackID='$ReceivingID' $Query AND IsBlock = 0 ";
        $stmtVaGradingQty = $this->conn->prepare($sqlVaGradingQty);
        $stmtVaGradingQty->execute();
        $dataRowVaGradingQty = $stmtVaGradingQty->fetch(PDO::FETCH_ASSOC);
        return $dataRowVaGradingQty;
    }

    // Query for getting Value Addition Added Qty
    public function getValueAdditionInQty($ReceivingID, $ReceivingPlantID)
    {
        $Query = "";
        if ($ReceivingPlantID != '' && $ReceivingPlantID != '0') {
            $Query = " and PlantID='$ReceivingPlantID'";
        }
        $sqlFields = "sum(SentWeight) as ValueAdditionInQty";
        $sqlValueAdditionInQty = "SELECT $sqlFields FROM `PpcValueAddition` WHERE TrackID='$ReceivingID' $Query";
        $stmtValueAdditionInQty = $this->conn->prepare($sqlValueAdditionInQty);
        $stmtValueAdditionInQty->execute();
        $dataRowValueAdditionInQty = $stmtValueAdditionInQty->fetch(PDO::FETCH_ASSOC);
        return $dataRowValueAdditionInQty;
    }

    // Query for getting Value Addition Out Qty
    public function getValueAdditionOutQty($ReceivingID, $ReceivingPlantID)
    {
        $Query = "";
        if ($ReceivingPlantID != '' && $ReceivingPlantID != '0') {
            $Query = " and PlantID='$ReceivingPlantID'";
        }
        $sqlFields = "sum(WtAftrVA) as ValueAdditionOutQty";
        $sqlValueAdditionOutQty = "SELECT $sqlFields FROM `PpcValueAddition` WHERE TrackID='$ReceivingID' $Query";
        $stmtValueAdditionOutQty = $this->conn->prepare($sqlValueAdditionOutQty);
        $stmtValueAdditionOutQty->execute();
        $dataRowValueAdditionOutQty = $stmtValueAdditionOutQty->fetch(PDO::FETCH_ASSOC);
        return $dataRowValueAdditionOutQty;
    }

    // Query for getting Soaking 'No(UNT)', IsBlock 'No' which is the Value Addition value to Freezing, (Soaking Skip)
    public function getFreezingQtyF2($ReceivingID, $ReceivingPlantID)
    {
        $Query = "";
        if ($ReceivingPlantID != '' && $ReceivingPlantID != '0') {
            $Query = " and PlantID='$ReceivingPlantID' ";
        }
        $sqlFields = "sum(WtAftrVA) as FreezingVAQty";
        $sqlFreezingQty2 = "SELECT $sqlFields FROM `PpcValueAddition` AS PVA
        LEFT JOIN PpcHLGradingBreakup AS PHGB ON PVA.GradingBreakupID = PHGB.DataID
        WHERE TrackID='$ReceivingID' $Query AND Treatment = 'UNT' AND IsBlock = 0";
        $stmtFreezingQty2 = $this->conn->prepare($sqlFreezingQty2);
        $stmtFreezingQty2->execute();
        $dataRowFreezingQty2 = $stmtFreezingQty2->fetch(PDO::FETCH_ASSOC);
        return $dataRowFreezingQty2;
    }

    // Query for getting Soaking 'Yes(Treated)', IsBlock 'No' which is the Value Addition value to Soaking (General Flow)
    public function getSoakingQtyS2($ReceivingID, $ReceivingPlantID)
    {
        $Query = "";
        if ($ReceivingPlantID != '' && $ReceivingPlantID != '0') {
            $Query = " and PlantID='$ReceivingPlantID' ";
        }
        $sqlFields = "sum(WtAftrVA) as SoakingVAQty";
        $sqlSoakingQty2 = "SELECT $sqlFields FROM `PpcValueAddition` AS PVA
        LEFT JOIN PpcHLGradingBreakup AS PHGB ON PVA.GradingBreakupID = PHGB.DataID
        WHERE TrackID='$ReceivingID' $Query AND Treatment != 'UNT' AND IsBlock = 0";
        $stmtSoakingQty2 = $this->conn->prepare($sqlSoakingQty2);
        $stmtSoakingQty2->execute();
        $dataRowSoakingQty2 = $stmtSoakingQty2->fetch(PDO::FETCH_ASSOC);
        return $dataRowSoakingQty2;
    }

    // Query for getting Total Soaking In Qty
    public function getSoakingInQty($ReceivingID, $ReceivingPlantID)
    {
        $Query = "";
        if ($ReceivingPlantID != '' && $ReceivingPlantID != '0') {
            $Query = " and ProductionPlantID='$ReceivingPlantID' ";
        }
        $sqlFields = "SUM(SoakInQty) as SoakInQty";
        $sqlSoakingInQty = "SELECT $sqlFields FROM `ProdSoakingDetails` 
        WHERE TrackID='$ReceivingID' $Query";
        $stmtSoakingInQty = $this->conn->prepare($sqlSoakingInQty);
        $stmtSoakingInQty->execute();
        $dataRowSoakingQty = $stmtSoakingInQty->fetch(PDO::FETCH_ASSOC);
        return $dataRowSoakingQty;
    }

    // Query for getting Total Soaking Out Qty
    public function getSoakingOutQty($ReceivingID, $ReceivingPlantID)
    {
        $Query = "";
        if ($ReceivingPlantID != '' && $ReceivingPlantID != '0') {
            $Query = " and ProductionPlantID='$ReceivingPlantID' ";
        }
        $sqlFields = "SUM(SoakOutQty) as SoakOutQty";
        $sqlSoakingInQty = "SELECT $sqlFields FROM `ProdSoakingDetails` 
        WHERE TrackID='$ReceivingID' $Query";
        $stmtSoakingInQty = $this->conn->prepare($sqlSoakingInQty);
        $stmtSoakingInQty->execute();
        $dataRowSoakingQty = $stmtSoakingInQty->fetch(PDO::FETCH_ASSOC);
        return $dataRowSoakingQty;
    }

    // Query for getting Freezing Added (In Qty)
    public function getFreezingInQty($ReceivingID, $ReceivingPlantID)
    {
        $Query = "";
        if ($ReceivingPlantID != '' && $ReceivingPlantID != '0') {
            $Query = " and PlantID='$ReceivingPlantID' ";
        }
        $sqlFields = "sum(ProductionWeight) as FreezingInQty,sum(RejectionWeight) as RejectionWeight";
        $sqlFreezingInQty = "SELECT $sqlFields FROM `ProdFreezingData` 
        WHERE TrackID='$ReceivingID' $Query";
        $stmtFreezingInQty = $this->conn->prepare($sqlFreezingInQty);
        $stmtFreezingInQty->execute();
        $dataRowFreezingInQty = $stmtFreezingInQty->fetch(PDO::FETCH_ASSOC);
        return $dataRowFreezingInQty;
    }

    // Query for getting Daily Production Added Qty (From Freezing)
    public function getStoreInQty($ReceivingID, $ReceivingPlantID)
    {
        $Query = "";
        if ($ReceivingPlantID != '' && $ReceivingPlantID != '0') {
            $Query = " and PlantID='$ReceivingPlantID' ";
        }
        $sqlFields = "sum(ActualKgs) AS StoreInQty";
        $sqlStoreInQty = "SELECT $sqlFields FROM `CsDailyProductionData` 
        WHERE TrackID='$ReceivingID' $Query";
        $stmtStoreInQty = $this->conn->prepare($sqlStoreInQty);
        $stmtStoreInQty->execute();
        $dataRowStoreInQty = $stmtStoreInQty->fetch(PDO::FETCH_ASSOC);
        return $dataRowStoreInQty;
    }

    // Query for getting Store Inward Added Qty
    public function getStoreInwardQty($ReceivingID, $ReceivingPlantID)
    {
        $Query = "";
        if ($ReceivingPlantID != '' && $ReceivingPlantID != '0') {
            $Query = " and CDPD.PlantID='$ReceivingPlantID' ";
        }
        $sqlFields = "SUM(CPID.ActualKgs) AS StoreInwardQty";
        $sqlStoreInwardQty = "SELECT $sqlFields FROM CsStockData AS CPID
        LEFT JOIN CsDailyProductionData AS CDPD ON CPID.ProductionID = CDPD.ProductionID 
        WHERE TrackID='$ReceivingID' $Query";
        $stmtStoreInwardQty = $this->conn->prepare($sqlStoreInwardQty);
        $stmtStoreInwardQty->execute();
        $dataRowStoreInwardQty = $stmtStoreInwardQty->fetch(PDO::FETCH_ASSOC);
        return $dataRowStoreInwardQty;
    }

    public function getStoreOutwardQty($ReceivingID, $ReceivingPlantID)
    {
        $Query = "";
        if ($ReceivingPlantID != '' && $ReceivingPlantID != '0') {
            $Query = " and CDPD.PlantID='$ReceivingPlantID' ";
        }
        $sqlFields = "SUM((OutMcs*TotalSlabs*SlabWeight*MFactor)+(OutLooseBags*SlabWeight*MFactor)) AS StoreOutwardQty";
        $sqlStoreOutwardQty = "SELECT $sqlFields FROM CsStockOutDetails AS CSOD
        LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
        LEFT JOIN CsDailyProductionData AS CDPD ON CPID.ProductionID = CDPD.ProductionID 
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CPID.PackingStyleID 
        WHERE TrackID='$ReceivingID' $Query";
        $stmtStoreOutwardQty = $this->conn->prepare($sqlStoreOutwardQty);
        $stmtStoreOutwardQty->execute();
        $dataRowStoreOutwardQty = $stmtStoreOutwardQty->fetch(PDO::FETCH_ASSOC);
        return $dataRowStoreOutwardQty;
    }
}


// Soaking PickUp % calculation
// for KGs -> (SoakOutQty/SoakInQty) * 100 = 
// for Count -> 454 standard value for converting count into KGs 
// 454/SoakInCount -> InCountKGs 
// 454/SoakOutCount -> OutCountKGs 
// (OutCountKGs/InCountKGs) * 100 = 