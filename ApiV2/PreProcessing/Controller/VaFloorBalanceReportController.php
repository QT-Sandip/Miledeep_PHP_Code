<?php

if (1) {

    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);
    ini_set('max_execution_time', 6000); // 10 min

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
            $StartDate = "2026-08-01"; // client requirement report start date should be May 20th
            if (!empty($RequestBody['ReceivingPlantID']) && $RequestBody['ReceivingPlantID'] == 7) {//unit 1
                $StartDate = "2026-08-01";
            }
            $EndDate = $RequestBody['EndDate'];
            $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
            $TypeOfMeterial = $RequestBody['TypeOfMeterial'];//regular/transfer
            $Treatment = $RequestBody['Treatment'];
            // $EndDate = $StartDate = "2026-05-18";

            $DateFilter = "RmRD.ArrivalDate BETWEEN '$StartDate' AND '$EndDate'";
            // $DateFilter = "RmRD.ArrivalDate <= '$EndDate'";
            $Query = '';

            if ($ReceivingPlantID != '') {
                $Query .= " AND RmRD.ReceivingPlantID='$ReceivingPlantID'";
            }
            $TreatmentQuery = '';
            if ($Treatment != '') {
                $TreatmentQuery .= " AND Treatment='$Treatment'";
            }

            $sqlFields = " distinct ReceivingID as TrackID,TrackCode,ReceivingPlantID,ArrivalDate,OutStage,0 AS IsTransfer";

            // $sqlQuery = "SELECT $sqlFields FROM PpcHLGradingDetails AS PHGD
            // LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
            // WHERE RmRD.ArrivalDate = '$ArrivalDate' $Query ";

           /*  $sqlQuery = "SELECT $sqlFields FROM `RmReceivingDetails` as RmRD WHERE $DateFilter
        and( ReceivingID in (SELECT TrackID FROM `PpcHLGradingDetails` ) or OutStage=1) $Query "; */

        $RegularTrackSql = "SELECT $sqlFields FROM `RmReceivingDetails` as RmRD WHERE $DateFilter
                            and( ReceivingID in (SELECT TrackID FROM `PpcHLGradingDetails` ) or OutStage=1) $Query ";

        $TransferTrackSql = "SELECT ReceivingID AS TrackID, CONCAT(TrackCode,' (Transferred)') AS TrackCode, ReceivingPlantID, ArrivalDate,
        OutStage, 1 AS IsTransfer
        FROM RmReceivingDetails AS RmRD
        LEFT JOIN  PpcHLGradingDetails AS PHGD ON PHGD.TrackID=RmRD.ReceivingID AND PHGD.CreatedFrom=1
        LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID = PHGD.LotID AND PHGB.CreatedFrom=1
        LEFT JOIN PpcValueAddition AS PVA ON PVA.TrackID = RmRD.ReceivingID  AND PVA.CreatedFrom=1 -- for transfer track ids
        WHERE  PHGB.IsBlock=0  $TreatmentQuery AND 
        $DateFilter 
        AND SoakingPlantID = '$ReceivingPlantID' AND ReceivingPlantID != '$ReceivingPlantID'
        AND IsStoreMaterial = 0
        AND OutStage = 0 ";

        if($TypeOfMeterial == 1){
            $sqlQuery = $RegularTrackSql;
        }elseif($TypeOfMeterial == 2){
            $sqlQuery = $TransferTrackSql;
        }else{
            $sqlQuery = "$RegularTrackSql 
                        UNION 
                        $TransferTrackSql ";
        }
        $sqlQuery .= " GROUP BY  ReceivingID 
                        ORDER BY ArrivalDate,TrackID ASC";
        //echo '<pre>';print_r($sqlQuery);die;
            $stmtSelect = $this->conn->prepare($sqlQuery);
            $stmtSelect->execute();
            
            /* $rows = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);
            $rowCount = count($rows);
            echo '<pre>';print_r($rowCount);die; */

            // $errorInfo = $stmtSelect->errorInfo();
            // var_dump($errorInfo);

            $i = 0;
            $ArrayReceivedData = [];
            $balQtyIdentity = [];
            $outStageArr = [];
            while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

                $TrackID = $dataRow['TrackID'];
                $OutStage = $dataRow['OutStage'];

                // $BalanceQty = $this->getCountWeight($TrackID);
                $BalanceQty = $this->getCountWeight($TrackID, $OutStage, $dataRow['IsTransfer'], $ReceivingPlantID);                
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
                    $balQtyIdentity[] = array("TrackID" => $dataRow['TrackID'], "OutStage" => $dataRow['OutStage'], "IsTransfer" => $dataRow['IsTransfer']);
                    $outStageArr[] = array("OutStage" => $dataRow['OutStage']);
                }
                // $BalanceData = $this->getBalanceReportData($RequestBody,$OutStage);

            }
            $BalanceData = $this->getBalanceReportData($RequestBody, $balQtyIdentity, $outStageArr);
            $Data['Error'] = null;
            $Data['Msg'] = 'Success';
            $Data['BalanceData'] = $BalanceData;
            $Data['Data'] = $ArrayReceivedData;

            return $Data;
        }

        public function getCountWeight($TrackID, $OutStage, $IsTransfer = 0, $ReceivingPlantID = '')
        {
            if($IsTransfer == 1){
                $sqlVAWeight = "SELECT `IsBlock`,`Treatment`,SUM(WtAftrVA) AS TotalQty,
                GROUP_CONCAT(DISTINCT VLID) AS VLIDs 
                FROM PpcValueAddition PVA 
                LEFT JOIN PpcHLGradingBreakup PGB ON PGB.DataID = PVA.GradingBreakupID
                WHERE TrackID in ($TrackID) AND `SoakingPlantID` = '$ReceivingPlantID' AND PackingGrade != 'DC'
                AND PGB.Treatment = 'UNT' ";
                $stmtVAWeight = $this->conn->prepare($sqlVAWeight);
                $stmtVAWeight->execute();
                $BalanceQty = 0;
                while ($rowVAWeight = $stmtVAWeight->fetch(PDO::FETCH_ASSOC)) {
                    $TotalQty = (float)$rowVAWeight['TotalQty'];
                    $VLIDs = $rowVAWeight['VLIDs'];
                    if($rowVAWeight['IsBlock'] == 0 && $rowVAWeight['Treatment'] != 'UNT'){
                        $sqlFreezing = "SELECT SUM(SoakInQty) AS TotalFreezQty FROM ProdSoakingDetails WHERE TrackID  in ($TrackID) and ValueAdditionID in ($VLIDs)";  
                    }else{
                        $sqlFreezing = "SELECT SUM(ProductionWeight + RejectionWeight) AS TotalFreezQty FROM ProdFreezingData WHERE TrackID  in ($TrackID) and TubNo in ($VLIDs)";  
                    }
                    $stmtFreezing = $this->conn->prepare($sqlFreezing);
                    $stmtFreezing->execute();
                    $rowFreezing = $stmtFreezing->fetch(PDO::FETCH_ASSOC);
                    $TotalFreezQty = (float)$rowFreezing['TotalFreezQty'];
                    $BalanceQty += ($TotalQty - $TotalFreezQty);
                }

            }else{
                if ($OutStage != 1) {
                    $sqlHLWeight = "SELECT SUM(HLWeight) AS TotalHLWeight FROM PpcHLGradingBreakup AS PHGB
                                LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID
                                WHERE PHGD.TrackID in ($TrackID) and IsBlock=0 AND PackingGrade != 'DC' ";
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
                
            }
            return $BalanceQty;
        }

        public function getBalanceReportData($RequestBody, $balQtyIdentity, $outStageArr)
        {
            // $StartDate        = $RequestBody['StartDate'];
            $EndDate = $RequestBody['EndDate'];
            $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
            $TypeOfMeterial = $RequestBody['TypeOfMeterial'];
            $TreatmentFilter = $RequestBody['Treatment'];

            //$StartDate = "2025-06-01"; // client requirement report start date should be May 20th
             $StartDate = "2026-08-01";
            if(!empty($RequestBody['ReceivingPlantID']) && $RequestBody['ReceivingPlantID'] == 7){// unit 1 - CAM-3216
                $StartDate = "2026-08-01";
            }
            // $EndDate = $StartDate = "2026-05-18";
            $Query = '';
            $SubQuery = '';

            $DateFilter = "RmRD.ArrivalDate BETWEEN '$StartDate' AND '$EndDate'";
            // $DateFilter = "RmRD.ArrivalDate <= '$EndDate'";

            if ($ReceivingPlantID != '') {
                $Query .= " AND RmRD.ReceivingPlantID = '$ReceivingPlantID'";
            }
            $TreatmentQuery = '';
            if ($TreatmentFilter != '') {
                $TreatmentQuery .= " AND PHGB.Treatment='$TreatmentFilter'";
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

                    $sqlHLFields = "PHGB.VarietyID, HLCount, PackingGrade,ShortName,PHGB.Treatment,
                                         SUM(HLWeight) AS TotalHLWeight,
                                        -- CASE WHEN PHGB.Treatment = 'UNT' AND PHGB.IsBlock=0
                                        -- THEN SUM(WtAftrVA)
                                        -- ELSE SUM(HLWeight)
                                        -- END AS TotalHLWeight,
                                        group_concat(DataID) as DataID,
                                        -- CASE WHEN PHGB.Treatment = 'UNT' AND PHGB.IsBlock=0
                                        -- THEN group_concat(VLID)
                                        -- ELSE group_concat(DataID)
                                        -- END AS DataID,
                                        PHVL.Species,SUM(PHGB.NoCrates) AS TotalCrates
                                        ,0 as IsTransfer";
                    $sqlHLFields = "PHGB.VarietyID, HLCount, PackingGrade,ShortName,PHGB.Treatment,SUM(HLWeight) AS TotalHLWeight,
                                group_concat(DISTINCT DataID) as DataID,PHVL.Species,SUM(PHGB.NoCrates) AS TotalCrates";

                    /* $sqlHLSelect = "SELECT $sqlHLFields FROM PpcHLGradingBreakup AS PHGB
                                    LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID
                                    LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID
                                    LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
                                    WHERE $DateFilter $Query AND PHGB.VarietyID != 0  and PHGB.IsBlock=0
                                    GROUP BY ShortName, HLCount, PackingGrade, PHGB.Treatment ORDER BY HLCount"; */

                    $regularHLSql = "SELECT $sqlHLFields,0 as IsTransfer FROM PpcHLGradingBreakup AS PHGB
                                    LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID
                                    LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID
                                    LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
                                    LEFT JOIN PpcValueAddition PVA ON GradingBreakupID = PHGB.DataID
                                    LEFT JOIN ProductionGrades AS PG ON PHGB.PackingGrade=PG.value
                                    WHERE $DateFilter $Query AND PHGB.VarietyID != 0  and PHGB.IsBlock=0 AND PG.GradeGroup NOT IN('All Mix','DC')
                                    AND (PVA.`SoakingPlantID` = '$ReceivingPlantID' OR PVA.`SoakingPlantID` = '' OR PVA.`SoakingPlantID` = 0 OR PVA.`SoakingPlantID` IS NULL)
                                    $TreatmentQuery
                                    GROUP BY ShortName, HLCount, PackingGrade, PHGB.Treatment ";

                    $TransferHLSql = " SELECT PVA.`VarietyID`,PVA.`SentCount` AS HLCount,PackingGrade,ShortName,PHGB.Treatment,
                                        SUM(`WtAftrVA`) AS TotalHLWeight,
                                    GROUP_CONCAT(DISTINCT VLID) AS DataID,PHVL.Species,SUM(PVA.`NoofCrates`) AS TotalCrates
                                    ,1 as IsTransfer
                                    FROM RmReceivingDetails AS RmRD
                                    LEFT JOIN  PpcHLGradingDetails AS PHGD ON PHGD.TrackID=RmRD.ReceivingID AND PHGD.CreatedFrom=1
                                    LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID = PHGD.LotID AND PHGB.CreatedFrom=1
                                    LEFT JOIN PpcValueAddition AS PVA ON PVA.`GradingBreakupID` = PHGB.DataID  AND PVA.CreatedFrom=1
                                    LEFT JOIN PpcHLVAVarietyList AS PHVL ON PVA.`VarietyID`=PHVL.VarietyID
                                    LEFT JOIN ProductionGrades AS PG ON PHGB.PackingGrade=PG.value
                                    WHERE $DateFilter  AND PVA.`SoakingPlantID` = '$ReceivingPlantID' AND RmRD.ReceivingPlantID != '$ReceivingPlantID' AND PHGB.VarietyID != 0 
                                     AND PG.GradeGroup NOT IN('All Mix','DC')
                                     AND PHGB.IsBlock=0 
                                     -- $TreatmentQuery
                                     AND PHGB.Treatment= 'UNT'
                                    GROUP BY ShortName, HLCount, PackingGrade, PHGB.Treatment ";

                    if($TypeOfMeterial == 1){
                        $sqlHLSelect = $regularHLSql;
                    }elseif($TypeOfMeterial == 2){
                        $sqlHLSelect = $TransferHLSql;
                    }else{
                        $sqlHLSelect = "$regularHLSql 
                                        UNION
                                        $TransferHLSql ";
                    }
                    $sqlHLSelect .=  " ORDER BY HLCount";         
                    // and PHGB.Treatment='UNT'
                    //echo '<pre>';print_r($sqlHLSelect);die;
                } else {
                    $sqlHLFields = "CSD.VarietyID,CSD.Grade as PackingGrade,Treatment, sum(ReceivingQty) AS TotalHLWeight,SPV.ShortName,
                                    RmRD.ReceivingID as DataID,SPV.Species";
                    $sqlHLSelect = "SELECT $sqlHLFields FROM `RmReceivingBreakup` as RRB
                                    LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = RRB.ReceivingID
                                    left join CsStockData as CSD on CSD.StockID=RRB.CsStockID
                                    left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID 
                                    LEFT JOIN ProductionGrades AS PG ON CSD.Grade=PG.value
                                    WHERE $DateFilter and OutStage=1 $Query AND CSD.VarietyID != 0
                                     AND PG.GradeGroup NOT IN('All Mix','DC')
                                    GROUP BY ShortName,  CSD.Grade, Treatment ORDER BY abs (CSD.Grade) ";
                    $SubQuery = " TrackID ";
                }
                //echo  $sqlHLSelect; exit;
                $stmtHLSelect = $this->conn->prepare($sqlHLSelect);
                $stmtHLSelect->execute();

                /* $rows = $stmtHLSelect->fetchAll(PDO::FETCH_ASSOC);
                $rowCount = count($rows);
                echo '<pre>';print_r($rowCount);die;  */


                $SNO = 0;
                // $GrandBalanceQty = 0;
                // $GrandBalanceCrates = 0;
                while ($dataHL = $stmtHLSelect->fetch(PDO::FETCH_ASSOC)) {
                    $DataID = $dataHL['DataID'];
                    $TotalHLWeight = $dataHL['TotalHLWeight'];
                    $TotalUtilisedCrates = (isset($dataHL['TotalCrates']) && !empty($dataHL['TotalCrates'])) ? $dataHL['TotalCrates'] : 0;

                    if($dataHL['IsTransfer'] != 1){
                        $sqlVASelect = "SELECT SUM(SentWeight) AS TotalVAQty,SUM(NoofCrates) AS TotalUsedCrates FROM PpcValueAddition WHERE  $SubQuery in ($DataID)";
                    }else{
                        if($dataHL['Treatment'] != 'UNT'){
                            $sqlVASelect = "SELECT SUM(SoakInQty) AS TotalVAQty, NoofCrates as TotalCrates 
                                                FROM ProdSoakingDetails WHERE  ValueAdditionID in ($DataID)"; 
                        }else{
                            $sqlVASelect = "SELECT SUM(ProductionWeight + RejectionWeight) AS TotalVAQty, 0 as TotalCrates 
                                            FROM ProdFreezingData PFD
                                            JOIN PpcValueAddition PVA ON PVA.VLID = PFD.TubNo and PFD.GradingBreakupID = PVA.GradingBreakupID
                                            WHERE TubNo in ($DataID)";  // DataId = VLID

                            $sqlVASelect = "SELECT SUM(ProductionWeight + RejectionWeight) AS TotalVAQty, 0 as TotalCrates 
                                            FROM ProdFreezingData PFD
                                            WHERE TubNo in ($DataID) AND TubNo != GradingBreakupID"; 
                        }
                    }
                    /* try {
                        $stmtVASelect = $this->conn->prepare($sqlVASelect);
                        $stmtVASelect->execute();

                        $rowVASelect = $stmtVASelect->fetch(PDO::FETCH_ASSOC);
                        echo '<pre>';print_r($rowVASelect);die;

                    } catch (PDOException $e) {
                        echo '<pre>';print_r("SQL Error: " . $e->getMessage());
                        echo '<pre>';print_r("<br>SQL Query: " . $sqlVASelect);die;
                    } */
                    $stmtVASelect = $this->conn->prepare($sqlVASelect);
                    $stmtVASelect->execute();
                    
                    $rowVASelect = $stmtVASelect->fetch(PDO::FETCH_ASSOC);
                    $TotalVAQty = $rowVASelect['TotalVAQty'] ? $rowVASelect['TotalVAQty'] : '0';
                    $TotalUsedCrates = (isset($rowVASelect['TotalUsedCrates']) && !empty($rowVASelect['TotalUsedCrates'])) ? $rowVASelect['TotalUsedCrates'] : '0';
                    // $BalanceQty = $TotalHLWeight - $TotalVAQty;
                    $BalanceQty = round($TotalHLWeight - $TotalVAQty, 3);
                    $BalanceCrates = round($TotalUtilisedCrates - $TotalUsedCrates, 3);
                    //echo '<pre>';print_r($rowVASelect);die; 
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
                                if($rowTrackID['IsTransfer'] == 1){
                                    $sqlBalanceSelect = "WITH Freezing AS (
                                                                SELECT SUM(ProductionWeight + RejectionWeight) AS FreezingWeight,
                                                                GROUP_CONCAT(DISTINCT TubNo) AS TubNo,
                                                                GROUP_CONCAT( PFD.SockingType) AS SockingType
                                                                FROM ProdFreezingData PFD
                                                                LEFT JOIN PpcValueAddition AS PVA ON PVA.VLID = PFD.TubNo
                                                                WHERE PFD.TrackID = '$TrackID' AND PVA.SoakingPlantID = '$ReceivingPlantID' 
                                                                GROUP BY PVA.VLID
                                                            ),
                                                            Soak AS (
                                                                SELECT SUM(SoakInQty) AS SoakInQty,SUM(PSD.NoofCrates)  AS NoofCrates,ValueAdditionID
                                                                FROM ProdSoakingDetails PSD
                                                                LEFT JOIN  PpcValueAddition PVA ON PSD.ValueAdditionID = PVA.VLID
                                                                WHERE PVA.TrackID = '$TrackID' AND PVA.SoakingPlantID = '$ReceivingPlantID'
                                                                GROUP BY PSD.ValueAdditionID
                                                            )
                                                        SELECT PHGB.IsBlock,PVA.VLID,SUM(WtAftrVA) AS HLWeight,SUM(PVA.NoofCrates) AS NoCrates,
                                                        CASE WHEN PHGB.Treatment != 'UNT' AND PHGB.IsBlock = 0
                                                            THEN SUM(PSD.SoakInQty)
                                                            ELSE SUM(PFD.FreezingWeight)
                                                            END AS TotalQty,
                                                        CASE WHEN PHGB.Treatment != 'UNT' AND PHGB.IsBlock = 0
                                                            THEN SUM(PSD.NoofCrates)
                                                            ELSE 0
                                                            END AS TotalCrates
                                                        FROM PpcHLGradingBreakup AS PHGB 
                                                        LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID 
                                                        LEFT JOIN PpcValueAddition AS PVA ON PVA.GradingBreakupID = PHGB.DataID
                                                        LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID
                                                        LEFT JOIN Freezing AS PFD ON PVA.VLID = PFD.TubNo
                                                        LEFT JOIN Soak AS PSD ON ValueAdditionID = PVA.VLID
                                                        WHERE PHGB.IsBlock = 0 AND PVA.SoakingPlantID = '$ReceivingPlantID' AND PHGD.TrackID ='$TrackID' AND PHVL.ShortName = '$ShortName' AND HLCount = '$HLCount' 
                                                        AND PackingGrade = '$PackingGrade' 
                                                        -- AND PHGB.Treatment = '$Treatment'
                                                        AND PHGB.Treatment = 'UNT'
                                                        ";          
                                                        
                                    /*  if($TrackID == 17797 && $dataHL['Treatment'] == 'UNT' && $dataHL['ShortName'] == 'RPD' && $dataHL['PackingGrade'] == '51/60'  && $dataHL['HLCount'] == '57.00'){
                                            
                                        echo '<pre>';print_r($sqlBalanceSelect);die;
                                        }   */                                           
                                }elseif ($OutStage != 1) {
                                    /* $sqlBalanceFields = "PHGB.IsBlock,
                                    -- PHGB.DataID,
                                    CASE WHEN PHGB.Treatment = 'UNT' AND PHGB.IsBlock=0
                                        THEN group_concat(VLID)
                                        ELSE group_concat(DataID)
                                        END AS DataID,
                                    SUM(SentWeight)  AS TotalQty,
                                     CASE WHEN PHGB.Treatment = 'UNT' AND PHGB.IsBlock=0
                                    THEN 0 -- FREEZING
                                    ELSE SUM(NoofCrates)
                                    END AS TotalCrates ,
                                    CASE WHEN PHGB.Treatment = 'UNT' AND PHGB.IsBlock=0
                                    THEN SUM(WtAftrVA)
                                    ELSE
                                        (select SUM(HLWeight) FROM PpcHLGradingBreakup AS PHGB LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID
                                        LEFT JOIN PpcHLVAVarietyList AS PHVL1 ON PHGB.VarietyID=PHVL.VarietyID
                                        WHERE PHGD.TrackID ='$TrackID' AND PHVL1.ShortName = '$ShortName' AND HLCount = '$HLCount' AND PackingGrade = '$PackingGrade' AND PHGB.Treatment = '$Treatment')
                                    END
                                    AS HLWeight,
                                    (select SUM(NoCrates) FROM PpcHLGradingBreakup AS PHGB LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID
                                    LEFT JOIN PpcHLVAVarietyList AS PHVL1 ON PHGB.VarietyID=PHVL.VarietyID
                                    WHERE PHGD.TrackID ='$TrackID' AND PHVL1.ShortName = '$ShortName' AND HLCount = '$HLCount' AND PackingGrade = '$PackingGrade' AND PHGB.Treatment = '$Treatment') as NoCrates";

                                        // $sqlBalanceFields = "SUM(HLWeight) AS HLWeight,SUM(NoCrates) AS NoCrates, SUM(SentWeight) AS TotalQty,SUM(NoofCrates) AS TotalCrates";
                                        // $sqlBalanceSelect = "SELECT $sqlBalanceFields FROM PpcHLGradingBreakup AS PHGB
                                        // LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID
                                        // LEFT JOIN PpcValueAddition AS PVA ON PVA.GradingBreakupID = PHGB.DataID
                                        // LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID
                                        // WHERE PHGD.TrackID ='$TrackID' AND ShortName = '$ShortName' AND HLCount = '$HLCount' AND PackingGrade = '$PackingGrade' AND PHGB.Treatment = '$Treatment'";

                                        $sqlBalanceSelect = "SELECT  $sqlBalanceFields FROM PpcHLGradingBreakup AS PHGB 
                                        LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID
                                        LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID

                                        LEFT JOIN PpcValueAddition AS PVA ON PVA.GradingBreakupID = PHGB.DataID -- AND (PVA.PlantID = PVA.SoakingPlantID OR PVA.SoakingPlantID = '')
                                        LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
                                        LEFT JOIN ProductionGrades AS PG ON PHGB.PackingGrade=PG.value
                                        WHERE PHGD.TrackID ='$TrackID' AND PHVL.ShortName = '$ShortName' AND HLCount = '$HLCount' 
                                        AND PackingGrade = '$PackingGrade' AND PHGB.Treatment = '$Treatment' AND PHGB.IsBlock=0  AND PG.GradeGroup NOT IN('All Mix','DC') 
                                        AND RmRD.ReceivingPlantID = '$ReceivingPlantID'  AND (PVA.SoakingPlantID = '$ReceivingPlantID' OR PVA.SoakingPlantID = '' OR PVA.SoakingPlantID IS NULL)
                                        "; */

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
                                                        LEFT JOIN ProductionGrades AS PG ON PHGB.PackingGrade=PG.value
                                                        WHERE PHGD.TrackID ='$TrackID' AND PHVL.ShortName = '$ShortName' AND HLCount = '$HLCount' AND PackingGrade = '$PackingGrade' AND PHGB.Treatment = '$Treatment'
                                                        AND PG.GradeGroup NOT IN('All Mix','DC') AND PHGB.DataID IS NOT NULL";
                                    
                                } else {
                                    $sqlBalanceFields = "sum(ReceivingQty) AS HLWeight,(select sum(SentWeight) from  PpcValueAddition where TrackID = RmRD.ReceivingID AND SPV.ShortName = '$ShortName'
                                AND CSD.Grade = '$PackingGrade' AND SPV.Treatment = '$Treatment' ) AS TotalQty";
                                    $sqlBalanceSelect = " SELECT $sqlBalanceFields FROM `RmReceivingBreakup` as RRB
                                LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = RRB.ReceivingID
                                left join CsStockData as CSD on CSD.StockID=RRB.CsStockID
                                LEFT JOIN ProductionGrades AS PG ON CSD.Grade=PG.value
                                left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID WHERE RmRD.ReceivingID ='$TrackID' AND SPV.ShortName = '$ShortName'  AND PG.GradeGroup NOT IN('All Mix','DC')
                                AND CSD.Grade = '$PackingGrade' AND SPV.Treatment = '$Treatment' ";
                                }
                                /*  
                                        if($TrackID == 23987 && $Treatment == 'BPT' && $HLCount == '14.00' && $ShortName == 'RPDTO'){
                                            echo '<pre>';print_r($rowBalance);die;
                                        } */
                               /*  if($Treatment == 'UNT' && $PackingGrade == '61/70'){
                                echo '<pre>';print_r($sqlHLSelect);die;
                                } */
                                $stmtBalanceSelect = $this->conn->prepare($sqlBalanceSelect);
                                $stmtBalanceSelect->execute();

                                while ($rowBalance = $stmtBalanceSelect->fetch(PDO::FETCH_ASSOC)) {
                                    $HLQty = $rowBalance['TotalQty'] ? $rowBalance['TotalQty'] : '0';
                                    //if($rowBalance['IsBlock'] == 1 && $Treatment == 'UNT' && !empty($rowBalance['DataID'])){
                                    if($dataHL['IsTransfer'] == 0 && $Treatment == 'UNT' && !empty($rowBalance['DataID'])){
                                        // data from freezing
                                        $FreezingBalWeightSql = "SELECT SUM(ProductionWeight + RejectionWeight) AS TotalQty 
                                        FROM ProdFreezingData WHERE TubNo IN(".$rowBalance['DataID'].") AND TrackID = $TrackID";
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
                                        $TotalBalanceCrates += $BalanceCrates;
                                    }
                                    // /$TotalBalanceCrates += $BalanceCrates;

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
           // $StartDate = "2025-06-01"; // client requirement report start date should be May 20th
            $StartDate = "2026-08-01";
            if (!empty($RequestBody['ReceivingPlantID']) && $RequestBody['ReceivingPlantID'] == 7) {//unit 1
                $StartDate = "2026-08-01";
            }
            $EndDate = $RequestBody['EndDate'];
            $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
            // $EndDate = $StartDate = "2026-05-18";

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
            //$StartDate = "2025-06-01"; // client requirement report start date should be May 20th
             $StartDate = "2026-08-01";
            if (!empty($RequestBody['ReceivingPlantID']) && $RequestBody['ReceivingPlantID'] == 7) {//unit 1
                $StartDate = "2026-08-01";
            }
            $EndDate = $RequestBody['EndDate'];
            $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
            // $EndDate = $StartDate = "2026-05-18";

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
                                    $sqlBalanceFields = "SUM(SentWeight)  AS TotalQty,SUM(NoofCrates) AS TotalCrates ,
                                (select SUM(HLWeight) FROM PpcHLGradingBreakup AS PHGB LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID
                               WHERE PHGD.TrackID ='$TrackID' AND ShortName = '$ShortName' AND HLCount = '$HLCount' AND PackingGrade = '$PackingGrade' AND PHGB.Treatment = '$Treatment')AS HLWeight,
                                (select SUM(NoCrates) FROM PpcHLGradingBreakup AS PHGB LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID
                                WHERE PHGD.TrackID ='$TrackID' AND ShortName = '$ShortName' AND HLCount = '$HLCount' AND PackingGrade = '$PackingGrade' AND PHGB.Treatment = '$Treatment') as NoCrates";

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
