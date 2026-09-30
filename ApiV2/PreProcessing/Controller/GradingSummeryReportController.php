<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';

class GradingSummeryReportController
{
    private $conn;
    private $Error = null;
    private $Msg = '';

    public function __construct($db)
    {
        $this->conn = $db;
    }
    function getGradingSummeryReportData($RequestBody){
        $GradingDate = $RequestBody['GradingDate'];
        $Plant = $RequestBody['Plant'];
        $resultData = [];
        if(!empty($GradingDate)){
            $VarietyCombinationData = $this->getVarietyCombinationData($GradingDate, $Plant);
            $Grades = $this->getGradesByGradungDate($GradingDate, $Plant, $VarietyCombinationData);
            $VarietyWiseTotals = $this->getVarietyWiseTotals($GradingDate, $Plant, $VarietyCombinationData);
            $arrayList['GradeData'] = $Grades;
            $arrayList['VarietyCombinationData'] = $VarietyCombinationData;
            $arrayList['VarietyWiseTotals'] = $VarietyWiseTotals;

            $resultData = $arrayList;
        }
        
        //echo '<pre>';print_r($resultData);die;
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['Data'] = $resultData;
        return $Data;
    }
    function getVarietyCombinationData($GradingDate, $Plant = ''){
        $results = [];
        if(!empty($GradingDate)){ 
            $sqlQuery = "SELECT PHGB.`VarietyID`,CONCAT(Species,'-',ShortName) AS Variety
                    FROM PpcHLGradingDetails PHGD
                    LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID = PHGD.LotID  AND PHGB.CreatedFrom = 1
                    LEFT JOIN PpcHLVAVarietyList PHV ON PHV.VarietyID = PHGB.`VarietyID`
                    WHERE PHGB.`DataID` IS NOT NULL AND `GradingDate` = '$GradingDate'  AND PHGD.CreatedFrom = 1 
                   ";
            if(!empty($Plant)){
                $sqlQuery .= "  AND `PlantID` = $Plant";
            }
            $sqlQuery .= "  GROUP BY PHGB.`VarietyID` 
                            HAVING SUM(HLWeight) != 0
                             ";
            //echo '<pre>';print_r($sqlQuery);die;
            $stmt = $this->conn->prepare($sqlQuery);
		    $stmt->execute();
            while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results [] = $dataRow;
            }
            $results [] = [
                                'VarietyID' => 'GRADE_TOTAL',
                                'Variety' => 'Total'
                            ];
        }
        return $results;
    }
    function getGradesByGradungDate($GradingDate, $Plant = '', $VarietyCombinationData = null){
        $results = [];
        if(!empty($GradingDate)){  
            if($VarietyCombinationData === null){
                $VarietyCombinationData = $this->getVarietyCombinationData($GradingDate, $Plant);
            } 
            $sqlQuery = "SELECT DISTINCT `PackingGrade` AS GradeName
                    FROM PpcHLGradingDetails PHGD
                    LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID = PHGD.LotID  AND PHGB.CreatedFrom = 1
                    WHERE PHGB.DataID IS NOT NULL AND `GradingDate` = '$GradingDate' AND PHGD.CreatedFrom = 1
                    
                     ";
            if(!empty($Plant)){
                $sqlQuery .= "  AND `PlantID` = $Plant";
            }
            $sqlQuery .= " group by PackingGrade
                     HAVING SUM(HLWeight) != 0
                     ";
                    // echo '<pre>';print_r($sqlQuery);die;
            $stmtGrades = $this->conn->prepare($sqlQuery);
		    $stmtGrades->execute();
            $gradeOrder = [];
            $gradeRows = [];
            while ($dataGrade = $stmtGrades->fetch(PDO::FETCH_ASSOC)) {
                $gradeName = $dataGrade['GradeName'];
                $gradeOrder[] = $gradeName;
                $gradeRows[$gradeName] = [
                    'Grade' => $gradeName,
                    'GradeValueData' => []
                ];
            } 
            foreach($VarietyCombinationData as $Item){                
                $VarietyID = $Item['VarietyID'];
                $WeightByGrade = [];
                $WeightSql = "SELECT SUM(`HLWeight`) AS Weight,PackingGrade
                                FROM PpcHLGradingDetails PHGD
                                LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID = PHGD.LotID  AND PHGB.CreatedFrom = 1 
                                WHERE `GradingDate`= '$GradingDate'  AND PHGD.CreatedFrom = 1 AND PHGB.VarietyID='$VarietyID' ";
                if(!empty($Plant)){
                    $WeightSql .= "AND `PlantID` = $Plant ";
                }
                $WeightSql .= "GROUP BY PackingGrade";
                //echo '<pre>';print_r($WeightSql);die;
                $stmtWeight = $this->conn->prepare($WeightSql);
                $stmtWeight->execute();
                while ($rowWeight = $stmtWeight->fetch(PDO::FETCH_ASSOC)) { 
                    $WeightByGrade[$rowWeight['PackingGrade']] =  $rowWeight['Weight'];
                }
                foreach ($gradeOrder as $gradeName) {
                    if($Item['VarietyID'] === 'GRADE_TOTAL'){
                        $gradeRows[$gradeName]['GradeValueData'][] = $this->getGradeWiseTotal($gradeName, $GradingDate, $Plant);
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
        return $results;
    }
    function getGradeWiseTotal($gradeName, $GradingDate, $Plant = ''){
        if(!empty($gradeName) && !empty($GradingDate)){
            $WeightSql = "SELECT SUM(`HLWeight`) AS Weight,PackingGrade
                                FROM PpcHLGradingDetails PHGD
                                LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID = PHGD.LotID  AND PHGB.CreatedFrom = 1 
                                WHERE `GradingDate`= '$GradingDate'  AND PHGD.CreatedFrom = 1 AND PHGB.PackingGrade='$gradeName' ";
            if(!empty($Plant)){
                $WeightSql .= "AND `PlantID` = $Plant ";
            }
            $stmtWeight = $this->conn->prepare($WeightSql);
            $stmtWeight->execute();
           return $rowWeight = $stmtWeight->fetch(PDO::FETCH_ASSOC)['Weight'];
        }
    }
    function getVarietyWiseTotals($GradingDate, $Plant = '', $VarietyCombinationData = null){
        $WeightByVariety = [];
        if(!empty($GradingDate)){
            if($VarietyCombinationData === null ){
                $VarietyCombinationData = $this->getVarietyCombinationData($GradingDate, $Plant);
            }
            foreach($VarietyCombinationData as $Item){
                if( $Item['VarietyID'] == 'GRADE_TOTAL'){
                    $WeightSql = "SELECT SUM(`HLWeight`) AS Weight,PackingGrade
                                    FROM PpcHLGradingDetails PHGD
                                    LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID = PHGD.LotID  AND PHGB.CreatedFrom = 1 
                                    WHERE `GradingDate`= '$GradingDate'  AND PHGD.CreatedFrom = 1  ";
                }else{
                    $VarietyID = $Item['VarietyID'];
                    $WeightSql = "SELECT SUM(`HLWeight`) AS Weight,PackingGrade
                                    FROM PpcHLGradingDetails PHGD
                                    LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID = PHGD.LotID  AND PHGB.CreatedFrom = 1 
                                    WHERE `GradingDate`= '$GradingDate'  AND PHGD.CreatedFrom = 1 AND PHGB.VarietyID='$VarietyID' ";
                }               
                if(!empty($Plant)){
                    $WeightSql .= "AND `PlantID` = $Plant ";
                }
                $stmtWeight = $this->conn->prepare($WeightSql);
                $stmtWeight->execute();
                $WeightByVariety[] = $stmtWeight->fetch(PDO::FETCH_ASSOC)['Weight'];
            }
        }
        return $WeightByVariety;
    }
}
