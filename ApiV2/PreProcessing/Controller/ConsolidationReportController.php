<?php

    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);

    include_once '../Global/Util.php';
    class ConsolidationReportController
    {
        private $conn;
        private $Error = null;
        private $Msg   = '';

        public function __construct($db)
        {
            $this->conn = $db;
        }
        function getData($RequestBody){
            $ArrivalDate = $RequestBody['ArrivalDate'];
            $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
            $IsPlantFilter = $RequestBody['IsPlantFilter'];
            $TypeProduct = $RequestBody['TypeProduct'];
            $Grade = $RequestBody['Grade'];

            $FromDate = $RequestBody['FromDate'];
            $ToDate = $RequestBody['ToDate'];
            $drpType = $RequestBody['drpType'];//1= kgs, 2=slabs
            
            if(!$FromDate && !$ToDate){
                $Data['Error'] = 'From Date and To Date are required.';
                return $Data;
            }elseif(!$FromDate){
                $Data['Error'] = 'From Date is required.';
                return $Data;
            }elseif(!$ToDate){
                $Data['Error'] = 'To Date is required.';
                return $Data;
            }

            /* if(!$ArrivalDate){
                $Data['Error'] = 'ArrivalDate is required.';
                return $Data;
            } */
           $TrackIDQuery = " BETWEEN '$FromDate' AND '$ToDate' ";
            if ($ReceivingPlantID != '') {
                $TrackIDQuery .= " and RmRD.ReceivingPlantID = '$ReceivingPlantID'";
            }

            $TrackIDSql = "SELECT GROUP_CONCAT(DISTINCT ReceivingID) as TrackID 
                            FROM `RmReceivingDetails` as RmRD 
                        WHERE RmRD.IsStoreMaterial=0 and RmRD.ArrivalDate $TrackIDQuery ";
            
            $stmtTrackID = $this->conn->prepare($TrackIDSql);
            $stmtTrackID->execute();

            $TrackID = $stmtTrackID->fetch(PDO::FETCH_ASSOC)['TrackID'];

            $sqlRejTracks = "SELECT GROUP_CONCAT(DISTINCT ReceivingID) AS TrackIds 
                            FROM RmReceivingDetails AS RmRD WHERE RmRD.ArrivalDate  
                            $TrackIDQuery 
                            AND VehicleID='0' AND SupervisorID='0' AND `TrackCode` REGEXP 'REJ' AND OutStage=3 AND IsStoreMaterial=0";

            $stmtRejTrackID = $this->conn->prepare($sqlRejTracks);
            $stmtRejTrackID->execute();

            $RejTrackID = $stmtRejTrackID->fetch(PDO::FETCH_ASSOC)['TrackIds'];

            //echo '<pre>';print_r($sqlRejTracks);die;
            if(!$TrackID){
                $Data['Error'] = 'No Data Found.';
                return $Data;
            }
            $Query = $GradingQuery = " TrackID in ($TrackID) ";
            /* $Query = " ProductionDate = '$ProductionDate' ";
            if ($ReceivingPlantID != '') {
                $Query .= " AND PFD.PlantID = '$ReceivingPlantID'";
            } */
            if ($TypeProduct != '') {
                $Query .= " AND SPVL.TypeProduct = '$TypeProduct'";
                $GradingQuery .= " AND HLVA.TypeProduct = '$TypeProduct'";
            }
            if ($Grade != '') {
                $Query .= " AND PFD.Grade = '$Grade'";
                $GradingQuery .= " AND HLGB.PackingGrade = '$Grade'";
            }
            $VarietyCombinationData = $this->getVarietyCombinationData($Query);
            $PackingStyle = $this->getVarietyPackingStyleData($Query, $VarietyCombinationData, $RejTrackID, $drpType );
            $FreezingData['VarietyCombinationData'] = $VarietyCombinationData;
            $FreezingData['PackingStyle'] = $PackingStyle;

            $GradingData = $this->GetGradingData($GradingQuery);
            $Weights  = $this->getHOnHLWeightsByTrackIds($TrackID);
            $ProductTypeWeights  = $this->GetConsolidatedWeightsByProductType($TrackID);
            $resultData['FreezingData'] = $FreezingData;
            $resultData['GradingData']['List'] = $GradingData;
            $resultData['GradingData']['Weights'] = $Weights;
            $resultData['GradingData']['ProductTypeWeights'] = $ProductTypeWeights;

            $Data['Error'] = null;
            $Data['Msg'] = 'Success';
            $Data['Data'] = $resultData;
            return $Data;

        }
        function getVarietyCombinationData($Query){
            $PackingSql = "SELECT 
                            GROUP_CONCAT(DISTINCT SPVL.`VarietyID`) VarietyID,
                            CONCAT(LEFT(`TypeProduct`, 1),SPVL.`ShortName`,'-',PFD.`SockingType`) AS Variety,
                            CONCAT(`SlabWeight`,' ',`Uom`) AS `PackingStyle`
                            FROM ProdFreezingData PFD
                            LEFT JOIN AdminPackingStyles APS ON APS.PCKID = PFD.`PackingStyleID`
                            LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.`VarietyID` = PFD.`VarietyID`
                            WHERE  $Query
                            GROUP BY SPVL.`ShortName`, PFD.`SockingType`,TypeProduct
                            -- VarietyID, PFD.`SockingType`,PFD.`FreezingType`,SlabWeight,Uom
                            -- SPVL.`ShortName`,PFD.`SockingType`,PFD.`FreezingType,SlabWeight,Uom,PFD.Grade
                            HAVING SUM(PFD.ProductionWeight) != 0";
                           // echo '<pre>';print_r($PackingSql);die;
            $PackingStmt = $this->conn->prepare($PackingSql);
            $PackingStmt->execute();
            while ($dataRow = $PackingStmt->fetch(PDO::FETCH_ASSOC)) {
                $results [] = $dataRow;
            }
            $results [] = [
                            'VarietyID' => 'GRADE_TOTAL',
                            'Variety' => 'Total'
                        ];
            return $results;
        }
        function getVarietyPackingStyleData($Query, $VarietyCombinationData = null, $RejTrackID = '', $drpType ){
            $results = [];
            if($VarietyCombinationData === null){
                $VarietyCombinationData = $this->getVarietyCombinationData($Query);
            }
            $Sql = "SELECT PFD.Grade,
                            CONCAT(`SlabWeight`,' ',`Uom`) AS `PackingStyle`,
                            PFD.`FreezingType`,PFD.`SockingType`
                            FROM ProdFreezingData PFD
                            LEFT JOIN AdminPackingStyles APS ON APS.PCKID = PFD.`PackingStyleID`
                            LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.`VarietyID` = PFD.`VarietyID`
                            WHERE  $Query
                            GROUP BY -- SPVL.`ShortName`,PFD.`SockingType`,
                            PFD.Grade,SlabWeight,Uom
                            -- ,PFD.Grade
                            HAVING SUM(PFD.ProductionWeight) != 0
                            ORDER BY ABS(PFD.Grade),CONCAT(`SlabWeight`,' ',`Uom`) ASC";
            $Stmt = $this->conn->prepare($Sql);
            $Stmt->execute();
            $gradeOrder = [];
            $gradeRows = [];
            while ($PackingData = $Stmt->fetch(PDO::FETCH_ASSOC)) {
                $PackingStyle = $PackingData['PackingStyle'];
                $gradeName = $PackingData['Grade'];
                $gradeOrder[] = $gradeName.' '.$PackingStyle;
                $gradeRows[$gradeName.' '.$PackingStyle] = [
                    'Grade' => $gradeName,
                    'PackingStyle' => $PackingStyle,
                    'GradeValueData' => []
                ];
            } 
            //  echo '<pre>';print_r($VarietyCombinationData);die;
            $RejSql = '';
            if($RejTrackID){
                 $RejSql = "  (
                                    SELECT SUM(PFD2.ProductionWeight)
                                    FROM ProdFreezingData PFD2
                                    WHERE PFD2.TrackID IN ($RejTrackID)
                                    AND PFD2.Grade = PFD.Grade
                                    AND PFD2.VarietyID = PFD.VarietyID
                                )  ";
            }
            foreach($VarietyCombinationData as $Item){ 
                //if($Item['VarietyID'] != 'GRADE_TOTAL'){
                    $CheckVarietyPackStyleSql = "SELECT CONCAT(`SlabWeight`,' ',`Uom`) AS `PackingStyle`,
                                            GROUP_CONCAT(DISTINCT PFD.`VarietyID`) AS VarietyID
                                            FROM ProdFreezingData PFD
                                            LEFT JOIN AdminPackingStyles APS ON APS.PCKID = PFD.`PackingStyleID`
                                            -- LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.`VarietyID` = PFD.`VarietyID`
                                            WHERE $Query AND PFD.`VarietyID` IN(".$Item['VarietyID'].")
                                            GROUP BY `SlabWeight`,`Uom`";
                                           // echo '<pre>';print_r($CheckVarietyPackStyleSql);die;
                    $stmtCheckVarietyPackStyle = $this->conn->prepare($CheckVarietyPackStyleSql);
                    $stmtCheckVarietyPackStyle->execute();
                    //$VarietyIDArr = explode(',',$Item['VarietyID']);
                    $WeightByGrade = [];
                    while ($rowCheckVarietyPackStyle = $stmtCheckVarietyPackStyle->fetch(PDO::FETCH_ASSOC)) {
                        $VarietyID = $rowCheckVarietyPackStyle['VarietyID'];
                    //foreach($VarietyIDArr as $VarietyID){
                        if($drpType  == 1){//kgs 
                            $CalculatedField = " SUM(ProductionWeight) AS ProductionWeight,$RejSql AS RejectionWeight,";
                        }else{//slabs
                            $CalculatedField = " ROUND(SUM(ProductionWeight) /  (SlabWeight * MFactor), 2) AS ProductionWeight,ROUND($RejSql/(SlabWeight * MFactor), 2) AS RejectionWeight,";
                        }
                        $WeightSql = "SELECT PFD.Grade,$CalculatedField
                                PFD.`FreezingType`,PFD.`SockingType`,
                                CONCAT(`SlabWeight`,' ',`Uom`) AS `PackingStyle`
                                FROM ProdFreezingData PFD
                                LEFT JOIN AdminPackingStyles APS ON APS.PCKID = PFD.`PackingStyleID`
                                LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.`VarietyID` = PFD.`VarietyID`
                                WHERE $Query AND PFD.`VarietyID` IN($VarietyID)  ";
                        $WeightSql .= "GROUP BY Grade";
                       // echo '<pre>';print_r($WeightSql);die;
                        $stmtWeight = $this->conn->prepare($WeightSql);
                        $stmtWeight->execute();
                        while ($rowWeight = $stmtWeight->fetch(PDO::FETCH_ASSOC)) {
                            $RejectionWeight = ''; 
                            if(!empty($rowWeight['RejectionWeight'])){
                                $RejectionWeight = "/R-(".$rowWeight['RejectionWeight'].')';
                            }
                            $WeightByGrade[$rowWeight['Grade'].' '.$rowWeight['PackingStyle']] =  $rowWeight['ProductionWeight'].$RejectionWeight;
                        }
                    }
                   //echo '<pre>';print_r($WeightByGrade);die;
                    /* if(count($VarietyIDArr) > 1){
                    echo '<pre>';print_r($WeightByGrade);die;
                    } */
                //}
                foreach ($gradeOrder as $gradeName) {                                    
                    if($Item['VarietyID'] === 'GRADE_TOTAL'){
                        $gradeRows[$gradeName]['GradeValueData'][] = $this->getGradeWiseTotal($gradeName, $Query, $drpType);
                    }else{
                        $Weight = isset($WeightByGrade[$gradeName]) ? $WeightByGrade[$gradeName] : 0;
                        $gradeRows[$gradeName]['GradeValueData'][] = $Weight;
                    }
                }
            }
            $GradesData = [];
            foreach ($gradeOrder as $gradeName) {
                if (empty($gradeRows[$gradeName]['GradeValueData'])) {
                    $gradeRows[$gradeName]['GradeValueData'][] = 0;
                }
                $GradesData[] = $gradeRows[$gradeName];
            }
            return $GradesData;
        }
        
        function getGradeWiseTotal($gradeName, $Query, $drpType){            
            $gradeNameArr = explode(' ',$gradeName);
            $grade = $gradeNameArr[0];
            $where = '';
            if(!empty($gradeNameArr[1])){
                $SlabWeight = $gradeNameArr[1];
                $Uom = $gradeNameArr[2];
                $where = " and CONCAT(`SlabWeight`,'',`Uom`) = '$SlabWeight$Uom'";
            }
            if($drpType == 1){//kgs
                $CalculatedField = "SUM(ProductionWeight) ProductionWeight,";
            }else{//slabs
                $CalculatedField = "ROUND(SUM(ProductionWeight)/(SlabWeight * MFactor), 2) ProductionWeight,";
            }
            $WeightSql = "SELECT PFD.Grade,$CalculatedField
                            PFD.`FreezingType`,PFD.`SockingType`
                            FROM ProdFreezingData PFD
                            LEFT JOIN AdminPackingStyles APS ON APS.PCKID = PFD.`PackingStyleID`
                            LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.`VarietyID` = PFD.`VarietyID`
                            WHERE $Query AND PFD.`Grade` = '$grade' $where ";
                           // echo '<pre>';print_r($WeightSql);die;
            $stmtWeight = $this->conn->prepare($WeightSql);
            $stmtWeight->execute();
           return $rowWeight = $stmtWeight->fetch(PDO::FETCH_ASSOC)['ProductionWeight'];
        }
        //grading section start
        function GetGradingData($Query){
            $Sql = "SELECT 
                        CONCAT(HLVA.ShortName,'-',HLVA.Species) AS Variety,-- PFD.Grade,
                        HLGB.PackingGrade,HLGB.Treatment,`HLCount`, SUM(HLGB.HLWeight) AS `HLWeight`
                    FROM `PpcHLGradingDetails` AS HLGD
                    LEFT JOIN PpcHLGradingBreakup AS HLGB  ON HLGD.LotID = HLGB.LotID
                    LEFT JOIN PpcHLVAVarietyList AS HLVA ON HLGB.VarietyID=HLVA.VarietyID
                    WHERE $Query
                     AND HLGB.DataID IS NOT NULL AND HLGB.HLWeight !=0 
                    GROUP BY HLVA.Species,
                    HLGB.PackingGrade,HLGB.Treatment,
                    HLVA.ShortName,`HLCount`
                    ORDER BY HLVA.Species DESC,ABS(HLGB.PackingGrade), HLVA.ShortName ASC";
            $Stmt = $this->conn->prepare($Sql);
            $Stmt->execute();
            $results = [];
            $i = 0;
            while ($dataRow = $Stmt->fetch(PDO::FETCH_ASSOC)) {
                $dataRow['SNo'] = ++$i;
                $results [] = $dataRow;
            }
            return $results;
        }
        function getHOnHLWeightsByTrackIds($TrackIds){
            $HonWeight = $HLWeight = 0;
            if(!empty($TrackIds)){
                $HOnSql = "SELECT SUM(Weight) AS HonWeight FROM `RmLotDetatils` AS RLD
                            LEFT JOIN RmArrivalBreakUpDetails AS RABD ON RABD.ArrivalBrkUpID=RLD.BreakUpID 
                            LEFT JOIN RmReceivingBreakup AS RRB ON RRB.`ArrivalBreakupID` = RABD.`ArrivalBrkUpID`
                            WHERE RRB.`ReceivingID` IN ($TrackIds)";
                $StmtHOn = $this->conn->prepare($HOnSql);
                $StmtHOn->execute();
                $HonWeight = $StmtHOn->fetch(PDO::FETCH_ASSOC)['HonWeight'];

                $HOnSql = "SELECT SUM(HLGB.HLWeight) AS `HLWeight`
                            FROM PpcHLGradingBreakup  HLGB 
                            LEFT JOIN PpcHLGradingDetails HLGD ON HLGD.LotID = HLGB.LotID
                            WHERE `TrackID` IN ($TrackIds)";
                $StmtHOn = $this->conn->prepare($HOnSql);
                $StmtHOn->execute();
                $HLWeight = $StmtHOn->fetch(PDO::FETCH_ASSOC)['HLWeight'];
            }
            $Data['HonWeight'] = $HonWeight;
            $Data['HLWeight'] = $HLWeight;
            return $Data;
        }
        function GetConsolidatedWeightsByProductType($TrackIds){
            if(!empty($TrackIds)){
                $sql = "SELECT
                           CONCAT(HLVA.TypeProduct,' : ',SUM(HLGB.HLWeight)) AS `WeightsByType`
                            FROM `PpcHLGradingDetails` AS HLGD
                            LEFT JOIN PpcHLGradingBreakup AS HLGB  ON HLGD.LotID = HLGB.LotID
                            LEFT JOIN PpcHLVAVarietyList AS HLVA ON HLGB.VarietyID=HLVA.VarietyID
                            WHERE HLGD.TrackID IN ($TrackIds) 
                            AND HLGB.DataID IS NOT NULL AND HLGB.HLWeight !=0 
                            GROUP BY HLVA.TypeProduct";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $results = '';
                while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $results .= $dataRow['WeightsByType'].',<br>';
                }
                return rtrim($results, ',<br>');
            }
            return false;
        }
    }
?>