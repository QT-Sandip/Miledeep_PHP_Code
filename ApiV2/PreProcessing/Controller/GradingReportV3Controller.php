<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class GradingReportV3Controller
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    public function getReportDetails($RequestBody)
    {
        $Results = [];
        if(!empty($RequestBody['FromDate']) && !empty($RequestBody['ToDate'])){
            $Results = $this->getReportData($RequestBody);
            //echo '<pre>';print_r($Results);die;
            $this->Msg = 'Success';
        }else{
            $this->Error = 'Please choose the dates first.';
        }

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['Results'] = $Results;
        return $Data;
    }

    function getReportData($RequestBody){
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $StationID = $RequestBody['PsId'];

        $Query = '';
        if ($StationID != '') {
            $Query .= "and PS.StationID='$StationID'";
        }

        $aval_hl_reports_sql = "SELECT IM.RmLotID,IM.PurchaseDate, IM.LotNumber, RUG.UserName AS GraderName, RUA.UserName AS AgentName, 
                                IM.PurchaseWeight,IM.ProductionWeight, IM.PurchaseCount, AGM.MachineName, HW.IsMix,HW.GradingDate, HW.MixIDs,
                               -- SUM(ROUND(VA.WtAfterDH,2)) AS WAVA,
                               SUM(ROUND(VA.TotalDeheadingWeight,2)) AS WAVA,
                                AVG(ROUND(NetCount,2)) AS AvgNetCount,
                                SUM(ROUND(VA.ReceivedWeight ,2)) AS ReceivedWeight,
                                ROUND(AVG(VA.CountAfterDH ),2) AS CAVA,
                                SUM(ROUND(VA.ReturnWeight ,2))  AS ReturnWeight
                                FROM RmLotDetatils AS IM 

                                LEFT JOIN PpcDeheading  AS VA ON VA.LotID=IM.RmLotID AND CreatedFrom = 1
                                LEFT JOIN PpcHLGradingDetails AS HW ON HW.LotID=IM.RmLotID  AND HW.CreatedFrom = 1
                                LEFT JOIN RmUsersList AS RUG ON RUG.ID=IM.GraderID 
                                LEFT JOIN RmUsersList AS RUA ON RUA.ID=IM.AgentID 
                                LEFT JOIN AdminGradingMachines AS AGM ON AGM.MachineID=HW.MachineID
                                LEFT JOIN PpcVAStations AS PS ON HW.StationID =PS.StationID
                                WHERE GradingDate  BETWEEN '$FromDate'  AND '$ToDate'   
                                $Query
                                GROUP BY IM.RmLotID ORDER BY
                                IM.PurchaseDate,IM.LotNumber ASC ";

        $stmt_hl = $this->conn->prepare($aval_hl_reports_sql);
		$stmt_hl->execute();
		$RowCount = $stmt_hl->rowCount();

        return $this->getGrading($RequestBody, $stmt_hl);


    }

    function getGrading($RequestBody, $stmt_hl){
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $StationID = $RequestBody['PsId'];

        $Data['Grades'] = $this->GetGrades();

        $ArrayDayGrades = [];

        $SNO = 1;
        $PREV_DATE = '';
        $i = 0;
        $j = 0;

        $H_ON = 0;
        $TOTAL_HO_COUNT = 0;
        $avg_count = 0;
        $RowData = [];
        while ($DataRow = $stmt_hl->fetch(PDO::FETCH_ASSOC)) {
            $RmLotID = $DataRow['RmLotID'];
            $GradingBrkLotID = $DataRow['RmLotID'];
            $IsMix = $DataRow['IsMix'];
            $MixIDs = $DataRow['MixIDs'];
            $MixIDs = $DataRow['MixIDs'];
            if ($IsMix == 1) {
                if (in_array($MixIDs, $AryMixedLots)) {
                    continue;
                } else {
                    $AryMixedLots[] = $MixIDs;
                    $IDS = str_replace('_', ',', $MixIDs);
                    $GradingBrkLotID = str_replace('_', ',', $MixIDs);
                    $DataRow = $this->getMixedData($IDS, $DataRow);
                    $CountNetQty = $DataRow['CountNetQty'];
                }
            } else {
                // $DataRow = getMixedData($RmLotID, $DataRow); //uma
                $HLQty = isset($DataRow['WAVA']) ? $DataRow['WAVA'] : 'NA';
                $CountNetYield = $DataRow['AvgNetCount'];
                $CalHon = $CountNetYield / 100;
                $CountNetQty = 'NA';
                if($CalHon != 0){
                    $CountNetQty = $HLQty / $CalHon;
                }
            }

            $ReceivedWeight = $DataRow['ReceivedWeight'];
            $ReturnWeight = $DataRow['ReturnWeight'];
            //$HLWeight = $DataRow['HLWeight'];
            //$HLCount = $DataRow['HLCount'];
            $GradData = $this->getGradesData($GradingBrkLotID);
            $DataRow['TotalData'] = $GradData['TotalData'];

            $RmQty = $DataRow['ProductionWeight'];
            //$GradingMachine = $DataRow['MachineName'];
            $DataRow['RmDate'] = $this->utilController->getFormatedDate($DataRow['PurchaseDate']);
            $DataRow['GradingDate'] = $this->utilController->getFormatedDate($DataRow['GradingDate']);
            $LotNo = $DataRow['LotNumber'];
            $GradName = $DataRow['GraderName'];
            $AgentName = $DataRow['AgentName'];           
            $RmCount = $DataRow['PurchaseCount'];
            $RmAvgCount = $DataRow['RmAvgCount'];
            $Weight = $DataRow['PurchaseWeight'];
            $PurchaseDate = $DataRow['PurchaseDate'];
            $RmQty = $DataRow['PurchaseWeight'];

            $HLCount = isset($DataRow['CAVA']) ? $DataRow['CAVA'] : 'NA';
            $HLQty = isset($DataRow['WAVA']) ? $DataRow['WAVA'] : 'NA';
            $HLYeild = round(($HLQty / $RmQty) * 100, 2);

            $DataRow['HLCount'] = $HLCount;
            $DataRow['HLQty'] = $HLQty;
            $DataRow['HLYeild'] = $HLYeild;

            $DataRow['CountNetYieldPerc'] = round(($CountNetQty - $RmQty), 2);

            $standardYield = $this->getStandardYield($RmCount, $PurchaseDate);
            $stanYield = $standardYield / 100;
            $standardYieldPerc = 'NA';
            if($stanYield != 0){
                $standardYieldPerc = round((($HLQty / $stanYield) - $CountNetQty), 2);
            }

            $DataRow['standardYieldPerc'] = $standardYieldPerc;

            $GradQty = $GradData['GradQty'];
            $GradCount = $GradData['GradCount'];
            $GradCount = round($GradCount / $RmQty, 2);
            $GradYield = round(($GradQty / $RmQty) * 100, 2);
            $QtyVariation = round(($GradQty - $HLQty), 2);

            $DataRow['GradQty'] = $GradQty;
            $DataRow['GradCount'] = $GradCount;
            $DataRow['GradYield'] = $GradYield;
            $DataRow['QtyVariation'] = $QtyVariation;

            if ($IsMix == 1) {
                $CountVariation = round(($RmAvgCount - $GradCount), 2);
            } else {
                $CountVariation = round(($RmCount - $GradCount), 2);
            }

            $DataRow['CountVariation'] = $CountVariation;

            $RmWeighTotal += $RmQty;
            $HLWEIGHTALL += $HLQty;
            $GradingTotal += $GradQty;

            $RowData[] = $DataRow;
            
        }
       // $GradeBreakUp[] = $RowData;

        $Data['RmWeighTotal'] = $RmWeighTotal;
        $Data['HLWEIGHTALL'] = $HLWEIGHTALL;
        $Data['GradingTotal'] = $GradingTotal;
        $Data['GradeBreakUp'] = $RowData;
        return $Data;

    }

    function getMixedData($IDS, $DataRow)
    {
        if(empty($IDS)){
            return;
        }
        $sql_LOTS = "SELECT
                    GROUP_CONCAT(LotNumber SEPARATOR ', ') as LotNumber,
                    GROUP_CONCAT(PurchaseCount SEPARATOR ', ') as PurchaseCount,
                    AVG(PurchaseCount) as RmAvgCount,
                    (select SUM(round((WtAfterDH/(NetCount/100)),2)) from PpcDeheading WHERE  LotID in ($IDS) AND CreatedFrom = 1  ) as AvgNetCount,
                    sum(round(PurchaseWeight,2)) as PurchaseWeight,
                    sum(round(ProductionWeight,2)) as ProductionWeight,
                    (select sum(round(WtAfterDH,2)) from PpcDeheading WHERE  LotID in ($IDS) AND CreatedFrom = 1 ) as WAVA,
                    (select GROUP_CONCAT(CountAfterDH  SEPARATOR ', ')  from PpcDeheading WHERE  LotID in ($IDS) AND CreatedFrom = 1 ) as CAVA
                    from RmLotDetatils
                    WHERE RmLotID in ($IDS)";


        $stmt_LOTS = $this->conn->prepare($sql_LOTS);
		$stmt_LOTS->execute();
        $ROW_LOTS = $stmt_LOTS->fetch(PDO::FETCH_ASSOC);

        $LotNumbers = $ROW_LOTS['LotNumber'];
        $PurchaseCount = $ROW_LOTS['PurchaseCount'];
        $PurchaseWeight = $ROW_LOTS['PurchaseWeight'];
        $LotNumbers1 = $ROW_LOTS['LotNumber'];
        $AvgNetCount = $ROW_LOTS['AvgNetCount'];
        $ProductionWeight = $ROW_LOTS['ProductionWeight'];

        if ($ProductionWeight != 0) {
            $LotWeight = $ProductionWeight;
        } else {
            $LotWeight = $PurchaseWeight;
        }
        $DataRow['WAVA'] = $ROW_LOTS['WAVA'];
        $DataRow['CAVA'] = $ROW_LOTS['CAVA'];
        $DataRow['RmAvgCount'] = $ROW_LOTS['RmAvgCount'];
        $DataRow['LotNumber'] = $LotNumbers;
        $DataRow['PurchaseCount'] = $PurchaseCount;
        $DataRow['PurchaseWeight'] = $PurchaseWeight;
        $DataRow['CountNetQty'] = $AvgNetCount;
        $DataRow['ProductionWeight'] = $LotWeight;
        return $DataRow;
    }
    function getGradesData($GradingBrkLotID)
    {
        if(empty($GradingBrkLotID)){
            return;
        }
        $GradQty = 0;
        $GradCount = 0;

        $SqlGrdBrk = "SELECT sum(HLWeight) as HLWeight,HLCount FROM `PpcHLGradingBreakup` 
                WHERE LotID in ($GradingBrkLotID)  AND CreatedFrom = 1 GROUP by HLCount ORDER by HLCount ASC";


        $StmtGrdBrk = $this->conn->prepare($SqlGrdBrk);
		$StmtGrdBrk->execute();
        
        while ($RowBrk = $StmtGrdBrk->fetch(PDO::FETCH_ASSOC)) {

            $HLCount = $RowBrk['HLCount'];
            $HLWeight = $RowBrk['HLWeight'];
            $AryCounts[] = $HLCount;
            $ArySamples[] = $RowBrk;
            $GradQty += $HLWeight;
            $GradCount += ($HLWeight * $HLCount * 2.2);
        }

        $ary_lot_grade = array();
        $AryGrades = $this->GetGrades();

        foreach ($AryGrades as $RowCosting) {

            $LowLimit = $RowCosting['LowerLimit'];
            $UpLimit = $RowCosting['UpperLimit'];

            $HLWeight = 0;

            foreach ($ArySamples as $RowGrading) {
                $CheckCount = $RowGrading['HLCount'];
                $CheckCount = number_format(floor($RowGrading['HLCount']), 2);
                //$CheckCount = number_format(round($RowGrading['HLCount'], 0), 2);
                if ($CheckCount >= $LowLimit && $CheckCount <= $UpLimit) {
                    $HLWeight += $RowGrading['HLWeight'];
                    $HLCount = $RowGrading['HLCount'];
                    $CheckCount;
                    // break;
                } else {
                    // $HLWeight='0';
                }
            }


            $sample['GRADE'] = $RowCosting['Grade'];
            $sample['WEIGHT'] = $HLWeight;
            $sample['COUNT'] = $HLCount;
            $ary_lot_grade[] = $sample;
        }

        //$ArrayDayGrades[] = $ary_lot_grade;

        $Data['GradCount'] = $GradCount;
        $Data['GradQty'] = $GradQty;
        $Data['TotalData'] = $ary_lot_grade;

        return $Data;
    }

    function GetGrades(){
        $SqlCosting = "SELECT * FROM ProductionGradesMaster order by abs(Grade) asc";
        $StmtCosting =  $this->conn->prepare($SqlCosting);
        $StmtCosting->execute();
        $ArrayCostingRates = [];
        while ($RowCosting = $StmtCosting->fetch(PDO::FETCH_ASSOC)) {
            $ArrayCostingRates[] = $RowCosting;
        }

        return $ArrayCostingRates;
    }

    function getStandardYield($PurhaseCount, $PurchaseDate)
    {
        $yields = [];
        $counts = explode(',', $PurhaseCount);

        foreach ($counts as $count) {
            $sqlSYield = "SELECT Yield 
                    FROM RmStandardYields 
                    WHERE Type = 'HL' 
                    AND EffectiveDate <= '$PurchaseDate' 
                    AND $count BETWEEN LowerCount AND UpperCount 
                    ORDER BY ID DESC 
                    LIMIT 1";

            $stmtSYield =  $this->conn->prepare($sqlSYield);
            $stmtSYield->execute();
            $rowSYield = $stmtSYield->fetch(PDO::FETCH_ASSOC);

            $yields[] = $rowSYield['Yield'];
        }
        $averageYield = 0;
        if (count($yields) > 0) {
            $averageYield = array_sum($yields) / count($yields);
        }
        $StandardYeild = $averageYield;
        return $StandardYeild;
    }
}
