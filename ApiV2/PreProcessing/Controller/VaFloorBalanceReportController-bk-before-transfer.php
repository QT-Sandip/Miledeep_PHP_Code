<?php

if (1) {

    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);

    include_once '../Global/Util.php';

    class VaFloorBalanceReportController
    {
        private $conn;
        private $Error = null;
        private $Msg = '';

        public function __construct($db)
        {
            $this->conn = $db;
        }
        public function getGradingTrackNumbers($RequestBody)
        {
            // $StartDate        = $RequestBody['StartDate'];
            $StartDate = "2026-01-01"; // client requirement report start date should be May 20th
            if (!empty($RequestBody['ReceivingPlantID']) && $RequestBody['ReceivingPlantID'] == 7) {
                $StartDate = "2026-08-01";
            }
            $EndDate = $RequestBody['EndDate'];
            $ReceivingPlantID = $RequestBody['ReceivingPlantID'];

            $DateFilter = "RmRD.ArrivalDate BETWEEN '$StartDate' AND '$EndDate'";
            // $DateFilter = "RmRD.ArrivalDate <= '$EndDate'";
            $Query = '';

            if ($ReceivingPlantID != '') {
                $Query .= " AND RmRD.ReceivingPlantID='$ReceivingPlantID'";
            }

            $sqlFields = " distinct ReceivingID as TrackID,TrackCode,ReceivingPlantID,ArrivalDate,OutStage";

            // $sqlQuery = "SELECT $sqlFields FROM PpcHLGradingDetails AS PHGD
            // LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
            // WHERE RmRD.ArrivalDate = '$ArrivalDate' $Query ";

            $sqlQuery = "SELECT $sqlFields FROM `RmReceivingDetails` as RmRD WHERE $DateFilter
        and( ReceivingID in (SELECT TrackID FROM `PpcHLGradingDetails` ) or OutStage=1) $Query group by  ReceivingID ";

            $stmtSelect = $this->conn->prepare($sqlQuery);
            $stmtSelect->execute();
            // $errorInfo = $stmtSelect->errorInfo();
            // var_dump($errorInfo);

            $i = 0;
            $ArrayReceivedData = [];
            $balQtyIdentity = [];
            $outStageArr = [];
            while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

                $TrackID = $dataRow['TrackID'];//23091
                $OutStage = $dataRow['OutStage'];

                // $BalanceQty = $this->getCountWeight($TrackID);
                $BalanceQty = $this->getCountWeight($TrackID, $OutStage);

                // $BalanceQty = $CountWeightData['BalanceQty'];
                $ArrivalDate = !empty($dataRow['ArrivalDate']) ? date('d-m-Y', strtotime($dataRow['ArrivalDate'])) : '';

                if ($BalanceQty > 1) {
                    $ArrayReceivedDetails['SNo'] = ++$i;
                    $ArrayReceivedDetails['ArrivalDate'] = $ArrivalDate;
                    $ArrayReceivedDetails['TrackID'] = $dataRow['TrackID'];
                    $ArrayReceivedDetails['TrackCode'] = $dataRow['TrackCode'];
                    $ArrayReceivedDetails['ReceivingPlantID'] = $dataRow['ReceivingPlantID'];
                    $ArrayReceivedDetails['CountWeightData'] = $BalanceQty;
                    // $ArrayReceivedDetails['CountWeightData'] = round($BalanceQty, 3);
                    $ArrayReceivedData[] = $ArrayReceivedDetails;
                    $balQtyIdentity[] = array("TrackID" => $dataRow['TrackID'], "OutStage" => $dataRow['OutStage']);
                    $outStageArr[] = array("OutStage" => $dataRow['OutStage']);
                }
                // $BalanceData = $this->getBalanceReportData($RequestBody,$OutStage);

            }

            $BalanceData = $this->getBalanceReportData($RequestBody, $balQtyIdentity, $outStageArr);
            //echo '<pre>';print_r($BalanceData);die;
            $Data['Error'] = null;
            $Data['Msg'] = 'Success';
            $Data['BalanceData'] = $BalanceData;
            $Data['Data'] = $ArrayReceivedData;

            return $Data;
        }

        public function getCountWeight($TrackID, $OutStage)
        {
            if ($OutStage != 1) {
                $sqlHLWeight = "SELECT SUM(HLWeight) AS TotalHLWeight FROM PpcHLGradingBreakup AS PHGB
        LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID
        WHERE PHGD.TrackID in ($TrackID) and IsBlock=0 ";
                // and Treatment='UNT'
            } else {
                $sqlHLWeight = "SELECT sum(ReceivingQty) AS TotalHLWeight  FROM `RmReceivingBreakup` WHERE ReceivingID in ($TrackID)";
            }

            $stmtHLWeight = $this->conn->prepare($sqlHLWeight);
            $stmtHLWeight->execute();

            $rowHLWeight = $stmtHLWeight->fetch(PDO::FETCH_ASSOC);
            $HLWeight = $rowHLWeight['TotalHLWeight'];

            $sqlVAWeight = "SELECT SUM(SentWeight) AS TotalQty FROM PpcValueAddition WHERE TrackID in ($TrackID)";
            // echo $sqlHLWeight;
            //   echo $sqlVAWeight;
            
            $stmtVAWeight = $this->conn->prepare($sqlVAWeight);
            $stmtVAWeight->execute();

            $rowVAWeight = $stmtVAWeight->fetch(PDO::FETCH_ASSOC);
            $VAWeight = $rowVAWeight['TotalQty'];

            $BalanceQty = $HLWeight - $VAWeight;
            // echo 'Bal:' . $BalanceQty . '<br> trackID: ' . $TrackID . '<br>';
            // $ArrayList['BalanceQty'] = $BalanceQty;
            // $ArrayData[] = $ArrayList;

            // $data['ArrayData'] = $ArrayData;
            return $BalanceQty;
        }

        public function getBalanceReportData($RequestBody, $balQtyIdentity, $outStageArr)
        {
            // $StartDate        = $RequestBody['StartDate'];
            $EndDate = $RequestBody['EndDate'];
            $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
            $StartDate = "2025-06-01"; // client requirement report start date should be May 20th
            if(!empty($RequestBody['ReceivingPlantID']) && $RequestBody['ReceivingPlantID'] == 7){// unit 1 - CAM-3216
                $StartDate = "2026-08-01";
            }
            $Query = '';
            $SubQuery = '';

            $DateFilter = "RmRD.ArrivalDate BETWEEN '$StartDate' AND '$EndDate'";
            // $DateFilter = "RmRD.ArrivalDate <= '$EndDate'";

            if ($ReceivingPlantID != '') {
                $Query .= " AND RmRD.ReceivingPlantID = '$ReceivingPlantID'";
            }
            // $sqlOutStage = "SELECT ReceivingID as TrackID,OutStage FROM `RmReceivingDetails` as RmRD WHERE ArrivalDate = '$ArrivalDate'
            $sqlOutStage = "SELECT distinct OutStage FROM `RmReceivingDetails` as RmRD WHERE $DateFilter
        and( ReceivingID in (SELECT TrackID FROM `PpcHLGradingDetails` ) or OutStage=1) $Query order by ReceivingID asc ";

            $stmtOutStage = $this->conn->prepare($sqlOutStage);
            $stmtOutStage->execute();

            while ($dataOutStage = $stmtOutStage->fetch(PDO::FETCH_ASSOC)) {
                //foreach($outStageArr as $dataOutStage){

                $OutStage = $dataOutStage['OutStage'];
                // /$TrackID = $dataOutStage['TrackID'];

                if ($OutStage != 1) {

                    $SubQuery = " GradingBreakupID ";

                    $sqlHLFields = "PHGB.VarietyID, HLCount, PackingGrade,ShortName,PHGB.Treatment,SUM(HLWeight) AS TotalHLWeight,
                 group_concat(DataID) as DataID,PHVL.Species,SUM(PHGB.NoCrates) AS TotalCrates";
                    $sqlHLSelect = "SELECT $sqlHLFields FROM PpcHLGradingBreakup AS PHGB
                LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID
                LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID
                LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
                WHERE $DateFilter $Query AND PHGB.VarietyID != 0  and PHGB.IsBlock=0
                GROUP BY ShortName, HLCount, PackingGrade, PHGB.Treatment ORDER BY HLCount";
                    // and PHGB.Treatment='UNT'
                } else {
                    $sqlHLFields = "CSD.VarietyID,CSD.Grade as PackingGrade,Treatment, sum(ReceivingQty) AS TotalHLWeight,SPV.ShortName,
                RmRD.ReceivingID as DataID,SPV.Species";
                    $sqlHLSelect = "SELECT $sqlHLFields FROM `RmReceivingBreakup` as RRB
                LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = RRB.ReceivingID
                left join CsStockData as CSD on CSD.StockID=RRB.CsStockID
                left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID WHERE $DateFilter and OutStage=1 $Query AND CSD.VarietyID != 0
                GROUP BY ShortName,  CSD.Grade, Treatment ORDER BY abs (CSD.Grade) ";
                    $SubQuery = " TrackID ";
                }
                //echo  $sqlHLSelect; exit;

                $stmtHLSelect = $this->conn->prepare($sqlHLSelect);
                $stmtHLSelect->execute();

                $SNO = 0;
                // $GrandBalanceQty = 0;
                // $GrandBalanceCrates = 0;
                while ($dataHL = $stmtHLSelect->fetch(PDO::FETCH_ASSOC)) {
                    $DataID = $dataHL['DataID'];
                    $TotalHLWeight = $dataHL['TotalHLWeight'];
                    $TotalUtilisedCrates = (isset($dataHL['TotalCrates']) && !empty($dataHL['TotalCrates'])) ? $dataHL['TotalCrates'] : 0;

                    $sqlVASelect = "SELECT SUM(SentWeight) AS TotalVAQty,SUM(NoofCrates) AS TotalUsedCrates FROM PpcValueAddition WHERE  $SubQuery in ($DataID)";
                    $stmtVASelect = $this->conn->prepare($sqlVASelect);
                    $stmtVASelect->execute();
                  
                    $rowVASelect = $stmtVASelect->fetch(PDO::FETCH_ASSOC);
                    $TotalVAQty = $rowVASelect['TotalVAQty'] ? $rowVASelect['TotalVAQty'] : '0';
                    $TotalUsedCrates = (isset($rowVASelect['TotalUsedCrates']) && !empty($rowVASelect['TotalUsedCrates'])) ? $rowVASelect['TotalUsedCrates'] : '0';
                    // $BalanceQty = $TotalHLWeight - $TotalVAQty;
                    $BalanceQty = round($TotalHLWeight - $TotalVAQty, 3);
                    $BalanceCrates = round($TotalUtilisedCrates - $TotalUsedCrates, 3);

                    // echo  $sqlVASelect;

                    if ($BalanceQty > 1 && $BalanceCrates > 0) {

                        $ArrayVarietyList['SNO'] = ++$SNO;
                        $ArrayVarietyList['DataID'] = $DataID;
                        $ArrayVarietyList['Treatment'] = $dataHL['Treatment'];
                        $ArrayVarietyList['HLCount'] = $dataHL['HLCount'];
                        $ArrayVarietyList['PackingGrade'] = $dataHL['PackingGrade'];
                        $ArrayVarietyList['ShortName'] = $dataHL['ShortName'] . "-" . $dataHL['Species'];
                        $ArrayVarietyList['BalanceQty'] = $BalanceQty;

                        $HLCount = $dataHL['HLCount'];
                        $PackingGrade = $dataHL['PackingGrade'];
                        $ShortName = $dataHL['ShortName'];
                        $Treatment = $dataHL['Treatment'];

                        $ArrayBalanceData = [];
                        $TotalBalanceQty = 0;
                        $TotalBalanceCrates = 0;

                        /*$sqlTrackID = "SELECT  ReceivingID as TrackID,OutStage FROM `RmReceivingDetails` as RmRD
                        WHERE $DateFilter and( ReceivingID in (SELECT TrackID FROM `PpcHLGradingDetails` ) or OutStage=1)
                        $Query order by ReceivingID asc ";
                        $stmtTrackID = $this->conn->prepare($sqlTrackID);
                        $stmtTrackID->execute();

                        while ($rowTrackID = $stmtTrackID->fetch(PDO::FETCH_ASSOC)) {*/
                        foreach ($balQtyIdentity as $rowTrackID) {
                            $TrackID = $rowTrackID['TrackID'];
                            $OutStage = $rowTrackID['OutStage'];

                            //$BalanceQty = $this->getCountWeight($TrackID, $OutStage);
                            $BalanceQty = 1;
                            //    echo  $BalanceQty = 'Balance : <br>'.$this->getCountWeight($TrackID, $OutStage);
                            if ($BalanceQty > 0) {

                                if ($OutStage != 1) {
                                    $sqlBalanceFields = "PHGB.IsBlock,PHGB.DataID,SUM(SentWeight)  AS TotalQty,SUM(NoofCrates) AS TotalCrates ,
                                PHGB.HLWeight AS HLWeight,PHGB.NoCrates as NoCrates";

                                    // $sqlBalanceFields = "SUM(HLWeight) AS HLWeight,SUM(NoCrates) AS NoCrates, SUM(SentWeight) AS TotalQty,SUM(NoofCrates) AS TotalCrates";
                                    // $sqlBalanceSelect = "SELECT $sqlBalanceFields FROM PpcHLGradingBreakup AS PHGB
                                    // LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID
                                    // LEFT JOIN PpcValueAddition AS PVA ON PVA.GradingBreakupID = PHGB.DataID
                                    // LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID
                                    // WHERE PHGD.TrackID ='$TrackID' AND ShortName = '$ShortName' AND HLCount = '$HLCount' AND PackingGrade = '$PackingGrade' AND PHGB.Treatment = '$Treatment'";

                                    $sqlBalanceSelect = "SELECT  $sqlBalanceFields FROM PpcHLGradingBreakup AS PHGB LEFT JOIN PpcHLGradingDetails AS PHGD
                            ON PHGD.LotID =PHGB.LotID LEFT JOIN PpcValueAddition AS PVA ON PVA.GradingBreakupID = PHGB.DataID
                            LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID
                            WHERE PHGD.TrackID ='$TrackID' AND PHVL.ShortName = '$ShortName' AND HLCount = '$HLCount' AND PackingGrade = '$PackingGrade' AND PHGB.Treatment = '$Treatment' AND PHGB.DataID IS NOT NULL";

                                } else {
                                    $sqlBalanceFields = "sum(ReceivingQty) AS HLWeight,(select sum(SentWeight) from  PpcValueAddition where TrackID = RmRD.ReceivingID AND SPV.ShortName = '$ShortName'
                                AND CSD.Grade = '$PackingGrade' AND SPV.Treatment = '$Treatment' ) AS TotalQty";
                                    $sqlBalanceSelect = " SELECT $sqlBalanceFields FROM `RmReceivingBreakup` as RRB
                                LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = RRB.ReceivingID
                                left join CsStockData as CSD on CSD.StockID=RRB.CsStockID
                                left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID WHERE RmRD.ReceivingID ='$TrackID' AND SPV.ShortName = '$ShortName'
                                AND CSD.Grade = '$PackingGrade' AND SPV.Treatment = '$Treatment' ";
                                }
                               /*  if($Treatment == 'UNT' && $PackingGrade == '100/150' && $ShortName == 'RHLSO' && $TrackID =='16668'){
echo '<pre>';print_r($sqlBalanceSelect);die;
                                } */
                                if($TrackID==16668){
                //echo '<pre>';print_r("$sqlBalanceSelect");die;
            }
                                $stmtBalanceSelect = $this->conn->prepare($sqlBalanceSelect);
                                $stmtBalanceSelect->execute();

                                while ($rowBalance = $stmtBalanceSelect->fetch(PDO::FETCH_ASSOC)) {

                                    $HLQty = $rowBalance['TotalQty'] ? $rowBalance['TotalQty'] : '0';
                                    if($rowBalance['IsBlock'] == 1 && $Treatment == 'UNT' && !empty($rowBalance['DataID'])){
                                        // data from freezing
                                        $FreezingBalWeightSql = "SELECT SUM(ProductionWeight + RejectionWeight) AS TotalQty FROM ProdFreezingData WHERE TubNo = ".$rowBalance['DataID']." AND TrackID = $TrackID";
                                        $stmtFreezingBalWeight = $this->conn->prepare($FreezingBalWeightSql);
                                        $stmtFreezingBalWeight->execute();
                                        $HLQty = $stmtFreezingBalWeight->fetch(PDO::FETCH_ASSOC)['TotalQty'];
                                    }
                                      if($rowBalance['IsBlock'] == 1 && $Treatment != 'UNT' && !empty($rowBalance['DataID'])){
                                        // data from freezing
                                        $SoakingBalWeightSql = "SELECT SUM(SoakInQty) AS TotalQty  FROM ProdSoakingDetails WHERE VABreakupID = ".$rowBalance['DataID']." AND TrackID = $TrackID";
                                        $stmtSoakingBalWeight = $this->conn->prepare($SoakingBalWeightSql);
                                        $stmtSoakingBalWeight->execute();
                                        $HLQty = $stmtSoakingBalWeight->fetch(PDO::FETCH_ASSOC)['TotalQty'];
                                    }

                                    $NoCrates = $rowBalance['NoCrates'] ? $rowBalance['NoCrates'] : '0';
                                    $TotalCrates = $rowBalance['TotalCrates'] ? $rowBalance['TotalCrates'] : '0';
                                    $BalanceCrates = $NoCrates - $TotalCrates;
                                    // $BalanceQty    = $rowBalance['HLWeight'] - $HLQty;
                                    $BalanceQty = round($rowBalance['HLWeight'] - $HLQty, 3);
                                   

                                    //   echo  $test = $TrackID.'  balance :'.$BalanceQty;

                                    $BalanceQty = ($BalanceQty >= 1 && $BalanceCrates > 0) ? $BalanceQty : '-';
                                    $BalanceCrates = $BalanceCrates ? $BalanceCrates : '0';

                                    $ArrayBalanceList['Balance'] = $BalanceQty;
                                    if ($BalanceQty >= 1 && $BalanceCrates > 0) {
                                        $TotalBalanceQty += $BalanceQty;
                                    }
                                    $TotalBalanceCrates += $BalanceCrates;

                                    $ArrayBalanceData[] = $ArrayBalanceList;
                                }
                            }
                        }

                        $ArrayVarietyList['TotalBalanceQty'] = $TotalBalanceQty;
                        $ArrayVarietyList['TotalBalanceCrates'] = $TotalBalanceCrates;
                        $ArrayVarietyList['BalanceData'] = $ArrayBalanceData;

                        $ArrayVarietyData[] = $ArrayVarietyList;
                        $GrandBalanceQty += $TotalBalanceQty;
                        $GrandBalanceCrates += $TotalBalanceCrates;

                    }
                }
            }
            if ($GrandBalanceQty != 0) {
                $Data['GrandBalanceQty'] = $GrandBalanceQty;
                $Data['GrandBalanceCrates'] = $GrandBalanceCrates;
                $Data['ArrayVarietyData'] = $ArrayVarietyData;
            }
            return $Data;
        }
    }

} else {
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);

    include_once '../Global/Util.php';

    class VaFloorBalanceReportController
    {
        private $conn;
        private $Error = null;
        private $Msg = '';

        public function __construct($db)
        {
            $this->conn = $db;
        }
        public function getGradingTrackNumbers($RequestBody)
        {
            // $StartDate        = $RequestBody['StartDate'];
            $StartDate = "2025-06-01"; // client requirement report start date should be May 20th
            if (!empty($RequestBody['ReceivingPlantID']) && $RequestBody['ReceivingPlantID'] == 7) {
                $StartDate = "2026-08-01";
            }
            $EndDate = $RequestBody['EndDate'];
            $ReceivingPlantID = $RequestBody['ReceivingPlantID'];

            $DateFilter = "RmRD.ArrivalDate BETWEEN '$StartDate' AND '$EndDate'";
            // $DateFilter = "RmRD.ArrivalDate <= '$EndDate'";

            $Query = '';

            if ($ReceivingPlantID != '') {
                $Query .= " AND RmRD.ReceivingPlantID='$ReceivingPlantID'";
            }

            $sqlFields = " distinct ReceivingID as TrackID,TrackCode,ReceivingPlantID,ArrivalDate,OutStage";

            // $sqlQuery = "SELECT $sqlFields FROM PpcHLGradingDetails AS PHGD
            // LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
            // WHERE RmRD.ArrivalDate = '$ArrivalDate' $Query ";

            $sqlQuery = "SELECT $sqlFields FROM `RmReceivingDetails` as RmRD WHERE $DateFilter
        and( ReceivingID in (SELECT TrackID FROM `PpcHLGradingDetails` ) or OutStage=1) $Query group by  ReceivingID ";

            $stmtSelect = $this->conn->prepare($sqlQuery);
            $stmtSelect->execute();
            // $errorInfo = $stmtSelect->errorInfo();
            // var_dump($errorInfo);

            $i = 0;
            $ArrayReceivedData = [];
            while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

                $TrackID = $dataRow['TrackID'];
                $OutStage = $dataRow['OutStage'];

                // $BalanceQty = $this->getCountWeight($TrackID);
                $BalanceQty = $this->getCountWeight($TrackID, $OutStage);

                // $BalanceQty = $CountWeightData['BalanceQty'];
                $ArrivalDate = !empty($dataRow['ArrivalDate']) ? date('d-m-Y', strtotime($dataRow['ArrivalDate'])) : '';

                if ($BalanceQty > 0) {
                    $ArrayReceivedDetails['SNo'] = ++$i;
                    $ArrayReceivedDetails['ArrivalDate'] = $ArrivalDate;
                    $ArrayReceivedDetails['TrackID'] = $dataRow['TrackID'];
                    $ArrayReceivedDetails['TrackCode'] = $dataRow['TrackCode'];
                    $ArrayReceivedDetails['ReceivingPlantID'] = $dataRow['ReceivingPlantID'];
                    $ArrayReceivedDetails['CountWeightData'] = $BalanceQty;
                    // $ArrayReceivedDetails['CountWeightData'] = round($BalanceQty, 3);
                    $ArrayReceivedData[] = $ArrayReceivedDetails;
                }
                // $BalanceData = $this->getBalanceReportData($RequestBody,$OutStage);

            }

            $BalanceData = $this->getBalanceReportData($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Success';
            $Data['BalanceData'] = $BalanceData;
            $Data['Data'] = $ArrayReceivedData;

            return $Data;
        }

        public function getCountWeight($TrackID, $OutStage)
        {
            if ($OutStage != 1) {
                $sqlHLWeight = "SELECT SUM(HLWeight) AS TotalHLWeight FROM PpcHLGradingBreakup AS PHGB
        LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID
        WHERE PHGD.TrackID in ($TrackID) and IsBlock=0 ";
                // and Treatment='UNT'
            } else {
                $sqlHLWeight = "SELECT sum(ReceivingQty) AS TotalHLWeight  FROM `RmReceivingBreakup` WHERE ReceivingID in ($TrackID)";
            }

            $stmtHLWeight = $this->conn->prepare($sqlHLWeight);
            $stmtHLWeight->execute();

            $rowHLWeight = $stmtHLWeight->fetch(PDO::FETCH_ASSOC);
            $HLWeight = $rowHLWeight['TotalHLWeight'];

            $sqlVAWeight = "SELECT SUM(SentWeight) AS TotalQty FROM PpcValueAddition WHERE TrackID in ($TrackID)";
            // echo $sqlHLWeight;
            //   echo $sqlVAWeight;
            $stmtVAWeight = $this->conn->prepare($sqlVAWeight);
            $stmtVAWeight->execute();

            $rowVAWeight = $stmtVAWeight->fetch(PDO::FETCH_ASSOC);
            $VAWeight = $rowVAWeight['TotalQty'];

            $BalanceQty = $HLWeight - $VAWeight;
            // echo 'Bal:' . $BalanceQty . '<br> trackID: ' . $TrackID . '<br>';
            // $ArrayList['BalanceQty'] = $BalanceQty;
            // $ArrayData[] = $ArrayList;

            // $data['ArrayData'] = $ArrayData;
            return $BalanceQty;
        }

        public function getBalanceReportData($RequestBody)
        {
            // $StartDate        = $RequestBody['StartDate'];
            $StartDate = "2025-06-01"; // client requirement report start date should be May 20th
            if (!empty($RequestBody['ReceivingPlantID']) && $RequestBody['ReceivingPlantID'] == 7) {
                $StartDate = "2026-08-01";
            }
            $EndDate = $RequestBody['EndDate'];
            $ReceivingPlantID = $RequestBody['ReceivingPlantID'];

            $Query = '';
            $SubQuery = '';

            $DateFilter = "RmRD.ArrivalDate BETWEEN '$StartDate' AND '$EndDate'";
            // $DateFilter = "RmRD.ArrivalDate <= '$EndDate'";

            if ($ReceivingPlantID != '') {
                $Query .= " AND RmRD.ReceivingPlantID = '$ReceivingPlantID'";
            }
            // $sqlOutStage = "SELECT ReceivingID as TrackID,OutStage FROM `RmReceivingDetails` as RmRD WHERE ArrivalDate = '$ArrivalDate'
            $sqlOutStage = "SELECT distinct OutStage FROM `RmReceivingDetails` as RmRD WHERE $DateFilter
        and( ReceivingID in (SELECT TrackID FROM `PpcHLGradingDetails` ) or OutStage=1) $Query order by ReceivingID asc ";

            $stmtOutStage = $this->conn->prepare($sqlOutStage);
            $stmtOutStage->execute();

            

            while ($dataOutStage = $stmtOutStage->fetch(PDO::FETCH_ASSOC)) {

                $OutStage = $dataOutStage['OutStage'];
                // /$TrackID = $dataOutStage['TrackID'];

                if ($OutStage != 1) {

                    $SubQuery = " GradingBreakupID ";

                    $sqlHLFields = "PHGB.VarietyID, HLCount, PackingGrade,ShortName,PHGB.Treatment,SUM(HLWeight) AS TotalHLWeight,
                 group_concat(DataID) as DataID,PHVL.Species,SUM(PHGB.NoCrates) AS TotalCrates";
                    $sqlHLSelect = "SELECT $sqlHLFields FROM PpcHLGradingBreakup AS PHGB
                LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID
                LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID
                LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
                WHERE $DateFilter $Query AND PHGB.VarietyID != 0  and PHGB.IsBlock=0
                GROUP BY ShortName, HLCount, PackingGrade, PHGB.Treatment ORDER BY HLCount";
                    // and PHGB.Treatment='UNT'
                } else {
                    $sqlHLFields = "CSD.VarietyID,CSD.Grade as PackingGrade,Treatment, sum(ReceivingQty) AS TotalHLWeight,SPV.ShortName,
                RmRD.ReceivingID as DataID,SPV.Species";
                    $sqlHLSelect = "SELECT $sqlHLFields FROM `RmReceivingBreakup` as RRB
                LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = RRB.ReceivingID
                left join CsStockData as CSD on CSD.StockID=RRB.CsStockID
                left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID WHERE $DateFilter and OutStage=1 $Query AND CSD.VarietyID != 0
                GROUP BY ShortName,  CSD.Grade, Treatment ORDER BY abs (CSD.Grade) ";
                    $SubQuery = " TrackID ";
                }
                // echo  $sqlHLSelect;

                $stmtHLSelect = $this->conn->prepare($sqlHLSelect);
                $stmtHLSelect->execute();

                $SNO = 0;
                // $GrandBalanceQty = 0;
                // $GrandBalanceCrates = 0;
                while ($dataHL = $stmtHLSelect->fetch(PDO::FETCH_ASSOC)) {
                    $DataID = $dataHL['DataID'];
                    $TotalHLWeight = $dataHL['TotalHLWeight'];
                    $TotalUtilisedCrates = (isset($dataHL['TotalCrates']) && !empty($dataHL['TotalCrates'])) ? $dataHL['TotalCrates'] : 0;

                    $sqlVASelect = "SELECT SUM(SentWeight) AS TotalVAQty,SUM(NoofCrates) AS TotalUsedCrates FROM PpcValueAddition WHERE  $SubQuery in ($DataID)";
                    $stmtVASelect = $this->conn->prepare($sqlVASelect);
                    $stmtVASelect->execute();

                    $rowVASelect = $stmtVASelect->fetch(PDO::FETCH_ASSOC);
                    $TotalVAQty = $rowVASelect['TotalVAQty'] ? $rowVASelect['TotalVAQty'] : '0';
                    $TotalUsedCrates = (isset($rowVASelect['TotalUsedCrates']) && !empty($rowVASelect['TotalUsedCrates'])) ? $rowVASelect['TotalUsedCrates'] : '0';

                    // $BalanceQty = $TotalHLWeight - $TotalVAQty;
                    $BalanceQty = round($TotalHLWeight - $TotalVAQty, 3);
                    $BalanceCrates = round($TotalUtilisedCrates - $TotalUsedCrates, 3);

                    // echo  $sqlVASelect;

                    if ($BalanceQty > 1 && $BalanceCrates > 0) {

                        $ArrayVarietyList['SNO'] = ++$SNO;
                        $ArrayVarietyList['DataID'] = $DataID;
                        $ArrayVarietyList['Treatment'] = $dataHL['Treatment'];
                        $ArrayVarietyList['HLCount'] = $dataHL['HLCount'];
                        $ArrayVarietyList['PackingGrade'] = $dataHL['PackingGrade'];
                        $ArrayVarietyList['ShortName'] = $dataHL['ShortName'] . "-" . $dataHL['Species'];
                        $ArrayVarietyList['BalanceQty'] = $BalanceQty;

                        $HLCount = $dataHL['HLCount'];
                        $PackingGrade = $dataHL['PackingGrade'];
                        $ShortName = $dataHL['ShortName'];
                        $Treatment = $dataHL['Treatment'];

                        $ArrayBalanceData = [];
                        $TotalBalanceQty = 0;
                        $TotalBalanceCrates = 0;

                        $sqlTrackID = "SELECT  ReceivingID as TrackID,OutStage FROM `RmReceivingDetails` as RmRD
                    WHERE $DateFilter and( ReceivingID in (SELECT TrackID FROM `PpcHLGradingDetails` ) or OutStage=1)
                    $Query order by ReceivingID asc ";
                        $stmtTrackID = $this->conn->prepare($sqlTrackID);
                        $stmtTrackID->execute();

                        while ($rowTrackID = $stmtTrackID->fetch(PDO::FETCH_ASSOC)) {
                            $TrackID = $rowTrackID['TrackID'];
                            $OutStage = $rowTrackID['OutStage'];

                            $BalanceQty = $this->getCountWeight($TrackID, $OutStage);
                            //    echo  $BalanceQty = 'Balance : <br>'.$this->getCountWeight($TrackID, $OutStage);
                            if ($BalanceQty > 0) {

                                if ($OutStage != 1) {
                                    $sqlBalanceFields = "SUM(SentWeight)  AS TotalQty,SUM(NoofCrates) AS TotalCrates ,PHGB.HLWeight AS HLWeight,
                                NoCrates as NoCrates";

                                    // $sqlBalanceFields = "SUM(HLWeight) AS HLWeight,SUM(NoCrates) AS NoCrates, SUM(SentWeight) AS TotalQty,SUM(NoofCrates) AS TotalCrates";
                                    // $sqlBalanceSelect = "SELECT $sqlBalanceFields FROM PpcHLGradingBreakup AS PHGB
                                    // LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID
                                    // LEFT JOIN PpcValueAddition AS PVA ON PVA.GradingBreakupID = PHGB.DataID
                                    // LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID
                                    // WHERE PHGD.TrackID ='$TrackID' AND ShortName = '$ShortName' AND HLCount = '$HLCount' AND PackingGrade = '$PackingGrade' AND PHGB.Treatment = '$Treatment'";

                                    $sqlBalanceSelect = "SELECT  $sqlBalanceFields FROM PpcHLGradingBreakup AS PHGB LEFT JOIN PpcHLGradingDetails AS PHGD
                            ON PHGD.LotID =PHGB.LotID LEFT JOIN PpcValueAddition AS PVA ON PVA.GradingBreakupID = PHGB.DataID
                            LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID
                            WHERE PHGD.TrackID ='$TrackID' AND ShortName = '$ShortName' AND HLCount = '$HLCount' AND PackingGrade = '$PackingGrade' AND PHGB.Treatment = '$Treatment'";

                                } else {
                                    $sqlBalanceFields = "sum(ReceivingQty) AS HLWeight,(select sum(SentWeight) from  PpcValueAddition where TrackID = RmRD.ReceivingID AND SPV.ShortName = '$ShortName'
                                AND CSD.Grade = '$PackingGrade' AND SPV.Treatment = '$Treatment' ) AS TotalQty";
                                    $sqlBalanceSelect = " SELECT $sqlBalanceFields FROM `RmReceivingBreakup` as RRB
                                LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = RRB.ReceivingID
                                left join CsStockData as CSD on CSD.StockID=RRB.CsStockID
                                left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID WHERE RmRD.ReceivingID ='$TrackID' AND SPV.ShortName = '$ShortName'
                                AND CSD.Grade = '$PackingGrade' AND SPV.Treatment = '$Treatment' ";
                                }

                                // echo $sqlBalanceSelect;
                                $stmtBalanceSelect = $this->conn->prepare($sqlBalanceSelect);
                                $stmtBalanceSelect->execute();

                                while ($rowBalance = $stmtBalanceSelect->fetch(PDO::FETCH_ASSOC)) {

                                    $HLQty = $rowBalance['TotalQty'] ? $rowBalance['TotalQty'] : '0';
                                    $NoCrates = $rowBalance['NoCrates'] ? $rowBalance['NoCrates'] : '0';
                                    $TotalCrates = $rowBalance['TotalCrates'] ? $rowBalance['TotalCrates'] : '0';
                                    $BalanceCrates = $NoCrates - $TotalCrates;
                                    $BalanceQty = $rowBalance['HLWeight'] - $HLQty;
                                    //   echo  $test = $TrackID.'  balance :'.$BalanceQty;

                                    $BalanceQty = ($BalanceQty >= 1 && $BalanceCrates > 0) ? $BalanceQty : '-';
                                    $BalanceCrates = $BalanceCrates ? $BalanceCrates : '0';

                                    $ArrayBalanceList['Balance'] = $BalanceQty;
                                    if ($BalanceQty >= 1 && $BalanceCrates > 0) {
                                        $TotalBalanceQty += $BalanceQty;
                                    }
                                    $TotalBalanceCrates += $BalanceCrates;

                                    $ArrayBalanceData[] = $ArrayBalanceList;
                                }
                            }
                        }

                        $ArrayVarietyList['TotalBalanceQty'] = $TotalBalanceQty;
                        $ArrayVarietyList['TotalBalanceCrates'] = $TotalBalanceCrates;
                        $ArrayVarietyList['BalanceData'] = $ArrayBalanceData;

                        $ArrayVarietyData[] = $ArrayVarietyList;
                        $GrandBalanceQty += $TotalBalanceQty;
                        $GrandBalanceCrates += $TotalBalanceCrates;

                    }
                }
            }
            if ($GrandBalanceQty != 0) {
                $Data['GrandBalanceQty'] = $GrandBalanceQty;
                $Data['GrandBalanceCrates'] = $GrandBalanceCrates;
                $Data['ArrayVarietyData'] = $ArrayVarietyData;
            }
            return $Data;
        }
    }
}
