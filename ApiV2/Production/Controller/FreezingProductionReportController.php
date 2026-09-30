<?php
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class FreezingProductionReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }

    public function getFreezingReportData($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $PlantID = $RequestBody['PlantID'];
        $LineNumber = $RequestBody['LineNumber'];
        $FreezingType = $RequestBody['FreezingType'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $Shift = $RequestBody['Shift'];
        $FreezingReportGrouping = $RequestBody['FreezingReportGrouping'];
        $trackCodeType = $RequestBody['trackCodeType'];

        $Query = '';

        if ($PlantID != '') {
            $Query .= " AND PFD.PlantID='$PlantID' ";
        }

        if ($LineNumber != '') {
            $Query .= " and PFD.LineNumber='$LineNumber'";
        }
        if ($FreezingType != '') {
            $Query .= " and PFD.FreezingType='$FreezingType'";
        }
        if ($TypeProduct != '') {
            $Query .= " and SPV.TypeProduct='$TypeProduct'";
        }
        if ($Shift != '') {
            $Query .= " and PFD.ShiftID='$Shift'";
        }
        if ($trackCodeType != '') {
            if($trackCodeType == 'Rej'){
                $Query .= " and TrackCode LIKE '%$trackCodeType%'";
            }else{
                $Query .= " and TrackCode NOT LIKE '%Rej%'";
            }            
        }

        $DateFilter = " ProductionDate between '$FromDate' and '$ToDate'";
        $i = 0;

        $sqlFields = "SPV.DeviSupc,ProductDescription,PFD.`ProductionWeight`,PFD.GradingBreakupID,
            `PackingTypeID`, PFD.`Grade`, `ProductionTypeID`, `ProductCategoryID`,  PFD.`Remarks`,
            `AbStatus`,  `TubNo`, `LineNumber`,`PoNo`,AGO.Name as Shift,ShortName as Variety,PFD.TypeofMaterial,
            APS.PackingStyle,RPS.StationName,TotalSlabs,SlabWeight,MFactor,TrackCode,ProductionDate,RRD.ArrivalDate,
            COALESCE(PSD.SoakingInQuantity, 0) AS SoakingInQuantity, 
            COALESCE(VA.VASoakingInQuantity, 0) AS VASoakingInQuantity, 
            COALESCE(HLG.GradeSoakingInQuantity, 0) AS GradeSoakingInQuantity, 
            COALESCE(RRB.ReceivingQty, 0) AS ReceivingQtyTotal, 
            RejectionWeight, PFD.ProductionSlabs,PFD.StartTimer,PFD.EndTimer,RLD.LotNumber,PFD.VarietyID";

        $sqlFreezingBreakupData = "SELECT $sqlFields  FROM `ProdFreezingData` as PFD 
         left join ScrmPoData as SPD on SPD.PoID=PFD.PoID
         LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.DataID=PFD.GradingBreakupID 
        LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID=PHGB.LotID 
        left join RmLotDetatils AS RLD on RLD.RmLotID=PHGD.LotID
        left join AdminGeneralOptions as AGO on AGO.OptID=PFD.ShiftID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=PFD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=PFD.PackingStyleID
        left join CsTenant as CST on CST.TenantID=PFD.StockHolderID        
        left join CsStores as CSS on CSS.StoreID=PFD.UnitID
        left join RmReceivingDetails as RRD on RRD.ReceivingID=PFD.TrackID   
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=PFD.PlantID 
        LEFT JOIN (SELECT TrackID, TubNumber, SUM(SoakInQty) AS SoakingInQuantity FROM ProdSoakingDetails
            GROUP BY TrackID, TubNumber) PSD ON PSD.TrackID = PFD.TrackID AND PSD.TubNumber = PFD.TubNo
        LEFT JOIN (SELECT GradingBreakupID, SUM(WtAftrVA) AS VASoakingInQuantity FROM PpcValueAddition
            GROUP BY GradingBreakupID) VA ON VA.GradingBreakupID = PFD.GradingBreakupID
        LEFT JOIN (SELECT DataID, SUM(HLWeight) AS GradeSoakingInQuantity FROM PpcHLGradingBreakup
            GROUP BY DataID) HLG ON HLG.DataID = PFD.GradingBreakupID
        LEFT JOIN (SELECT ReceivalBreakupID, SUM(ReceivingQty) AS ReceivingQty FROM RmReceivingBreakup GROUP BY ReceivalBreakupID) AS RRB ON PFD.TubNo=RRB.ReceivalBreakupID
        where $DateFilter $Query ORDER BY `ProductionDate` asc";
        if($FreezingReportGrouping == 1){
            $sqlFreezingBreakupData = "SELECT a.*,
                                        -- GROUP_CONCAT(TrackCode) TrackCode,
                                        -- GROUP_CONCAT(PoNo) PoNo,
                                        -- GROUP_CONCAT(LineNumber) LineNumber,
                                        -- GROUP_CONCAT(TubNo) TubNo,
                                        SUM(a.`ProductionWeight`) AS `ProductionWeight`,
                                        SUM(SoakingInQuantity) AS SoakingInQuantity ,
                                        -- SUM(TotalSlabs) TotalSlabs,
                                        TotalSlabs,
                                        SUM(ProductionSlabs) ProductionSlabs,
                                        SUM(SlabWeight) SlabWeight,
                                        SUM(RejectionWeight) RejectionWeight
                                        FROM (
                                            $sqlFreezingBreakupData
                                        ) a GROUP BY VarietyID,ProductionDate,TrackCode,TubNo,`PackingTypeID`,`PoNo`  ORDER BY `ProductionDate` ASC";
        }
        //echo '<pre>';print_r($sqlFreezingBreakupData);die;
        $stmtFreezingBreakupData = $this->conn->prepare($sqlFreezingBreakupData);
        $stmtFreezingBreakupData->execute();
        while ($dataRow = $stmtFreezingBreakupData->fetch(PDO::FETCH_ASSOC)) {

            if ($dataRow['ProductionSlabs'] != null) {
                $Slabs = $dataRow['ProductionSlabs'];
            } else {
                $Slabs = ($dataRow['ProductionWeight'] / ($dataRow['SlabWeight'] * $dataRow['MFactor']));
            }
            
            if($dataRow['GradingBreakupID'] != 0){
                $soakingInQty = $dataRow['SoakingInQuantity'];
                if($soakingInQty == '' || $soakingInQty == 0){
                    $soakingInQty = $dataRow['VASoakingInQuantity'];
                }
                if($soakingInQty == '' || $soakingInQty == 0){
                    $soakingInQty = $dataRow['GradeSoakingInQuantity'];
                }
            }else{
                if($dataRow['TypeofMaterial'] == "HON"){
                    $soakingInQty = $dataRow['ReceivingQtyTotal'];
                }else{
                    $soakingInQty = 0;
                }
            }
            // $Mcs = ($Slabs / $dataRow['TotalSlabs']);
            $Mcs = ($Slabs / $dataRow['TotalSlabs']);
            // $FreezingYield = ($dataRow['ProductionWeight'] + $dataRow['RejectionWeight']) / $dataRow['InQuantity'];
            $FreezingYield = ($dataRow['ProductionWeight'] / $soakingInQty) * 100;

//hours calculation

$StartTimer = $dataRow['StartTimer'];  // Example: "05:15"
$EndTimer = $dataRow['EndTimer'];      // Example: "17:15"

if (!empty($StartTimer) && !empty($EndTimer)) {

    // $today = date('Y-m-d');  // Get current date
    // $start_timestamp = strtotime($today . ' ' . $StartTimer);
    // $end_timestamp = strtotime($today . ' ' . $EndTimer);

    $start_timestamp = strtotime($today . ' ' . $StartTimer);
$end_timestamp = strtotime($today . ' ' . $EndTimer);

// If end time is before start time, assume it's on the next day
if ($end_timestamp < $start_timestamp) {
    // Add 1 day to end time
    $end_timestamp = strtotime('+1 day', $end_timestamp);
}

    $execution_time = $end_timestamp - $start_timestamp;
    $hours = floor($execution_time / 3600);
    $minutes = floor(($execution_time % 3600) / 60);

    $WorkingHours = "{$hours} hr {$minutes} mins";
    
} 
            $ArrayFreezingData['SNo'] = ++$i;
            $ArrayFreezingData['TrackCode'] = $dataRow['TrackCode'];
            $ArrayFreezingData['ProductionDate'] = $this->utilController->getFormatedDate($dataRow['ProductionDate']);
            $ArrayFreezingData['ArrivalDate'] = $this->utilController->getFormatedDate($dataRow['ArrivalDate']);
            $ArrayFreezingData['Supc'] = $dataRow['DeviSupc'];
            $ArrayFreezingData['Product'] = $dataRow['ProductDescription'];
            $ArrayFreezingData['ProductionPlant'] = $dataRow['StationName'];
            $ArrayFreezingData['Group'] = $dataRow['ProductionTypeID'];
            $ArrayFreezingData['Shift'] = $dataRow['Shift'];
            $ArrayFreezingData['PoNo'] = $dataRow['PoNo'];
            $ArrayFreezingData['Grade'] = $dataRow['Grade'];
            $ArrayFreezingData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayFreezingData['PackingType'] = $dataRow['PackingTypeID'];
            $ArrayFreezingData['ProductCategory'] = $dataRow['ProductCategoryID'];
            $ArrayFreezingData['AbStatus'] = $dataRow['AbStatus'];
            $ArrayFreezingData['NoofCases'] = round($Mcs, 2);
            $ArrayFreezingData['NoofSlabs'] = $Slabs;
            $ArrayFreezingData['ProductionWeight'] = number_format($dataRow['ProductionWeight'], 3);
            $ArrayFreezingData['RejectionWeight'] = number_format($dataRow['RejectionWeight'], 3);
            $ArrayFreezingData['TubNo'] = $dataRow['TubNo'];
            $ArrayFreezingData['LineNumber'] = $dataRow['LineNumber'];
            $ArrayFreezingData['Remarks'] = $dataRow['Remarks'];
            $ArrayFreezingData['StartTimer'] = $dataRow['StartTimer'];
            $ArrayFreezingData['EndTimer'] = $dataRow['EndTimer'];
            $ArrayFreezingData['LotNumber'] = $dataRow['LotNumber'];
            $ArrayFreezingData['WorkingHours'] = $WorkingHours;           
            $ArrayFreezingData['FreezingYield'] = number_format($FreezingYield, 2);

            $TotalProductionWeight += $dataRow['ProductionWeight'];
            $TotalRejectionWeight += $dataRow['RejectionWeight'];

            $TotalMinutes = ($hours * 60) + $minutes; // Convert total working time to minutes
            $TotalWorkingMinutes +=$TotalMinutes; 

            $TotalSlabs += $Slabs;
            $TotalMcs += $Mcs;
            $AryFreezingData[] = $ArrayFreezingData;
        }
        $TotalHours = floor($TotalWorkingMinutes / 60);
        $TotalMinutes = $TotalWorkingMinutes % 60;
        // $TotalWorkingHours = "{$TotalHours} hr {$TotalMinutes} mins";

        //consumption
        $totalHoursDecimal = $TotalHours + ($TotalMinutes / 60);

if ($totalHoursDecimal > 0) {
    $AveragePerHourConsumption = ($TotalProductionWeight + $TotalRejectionWeight) / $totalHoursDecimal;
} else {
    $AveragePerHourConsumption = 0;
}

        $Data['Error'] = $this->Error;
        $Data['Msg'] = 'Success';
        $ArrayTotals['TotalProductionWeight'] = number_format($TotalProductionWeight, 3);
        $ArrayTotals['TotalRejectionWeight'] = number_format($TotalRejectionWeight, 3);
        $TotalWorkingHours = "{$TotalHours} hr {$TotalMinutes} mins";
        $ArrayTotals['TotalWorkingHours'] = $TotalWorkingHours;
        $ArrayTotals['AveragePerHourConsumption'] = number_format($AveragePerHourConsumption,3);

        

        $ArrayTotals['TotalSlabs'] = $TotalSlabs;
        $ArrayTotals['TotalMcs'] = round($TotalMcs, 2);
        $Data['FreezingData'] = $AryFreezingData;
        $Data['Totals'] = $ArrayTotals;
        return $Data;
    }
}
