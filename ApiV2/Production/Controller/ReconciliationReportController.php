<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL); 

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class ReconciliationReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = 'Success';
    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }

    public function getReconciliationReportDetails($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $DateFilter = " ArrivalDate between '$FromDate' and '$ToDate'";

        $sqlFields = " distinct RPS.ProcessingStationID,ProdCode";

        $sqlProcessingStations = "SELECT $sqlFields FROM RmArrivalDetatils as RAD 
             left join RmLotDetatils as RLD on RLD.RmReferenceID=RAD.RmArrivalID
           left join PpcHLGradingBreakup as PHGB on PHGB.LotID=RLD.RmLotID 
                left join `RmProcessingStations` as RPS on RAD.ProcessingStationID=RPS.`ProcessingStationID`
       where $DateFilter and RAD.ProcessingStationID is not null  and HLWeight !=0 ORDER BY RPS.`ProcessingStationID` ASC";


        $stmtProcessingStations = $this->conn->prepare($sqlProcessingStations);
        $stmtProcessingStations->execute();
        $i = 0;

        while ($dataRowProcessingStations = $stmtProcessingStations->fetch(PDO::FETCH_ASSOC)) {

            $PlantID = $dataRowProcessingStations['ProcessingStationID'];

            $sqlFields = "Species,sum(HLWeight) as RMConsumedHLkg,group_concat(distinct TrackID) as TrackID,
            (SELECT group_concat(distinct ReceivingID)  FROM `RmReceivingDetails` WHERE $DateFilter and ReceivingPlantID='$PlantID') as TrackIDs,
            PurchaseWeight";

            $sqlPlantwiseWeights = "SELECT $sqlFields FROM `PpcHLGradingDetails` as PHGD 
             left join PpcHLGradingBreakup as PHGB on PHGD.LotID=PHGB.LotID 
             left join RmLotDetatils as RLD on RLD.RmLotID=PHGB.LotID 
             left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RLD.RmReferenceID
             WHERE  $DateFilter and PlantID='$PlantID'  and HLWeight !=0 group by Species ORDER BY Species desc"; 

            $stmtPlantwiseWeights = $this->conn->prepare($sqlPlantwiseWeights);
            $stmtPlantwiseWeights->execute();
            $TotalRMProcuredHLKg = 0;
            $TotalRMConsumedHLkg = 0;
            $TotalFarmHLkg = 0;
            $TotalVariancekg = 0;
            $TotalVariancePercentage = 0;
            $GrandTotalRmProcured = $TotalHeadOnRawMeterialQty = $TotalHeadOnFarmRawMeterialQty = 0;
            while ($dataRowPlantwiseWeights = $stmtPlantwiseWeights->fetch(PDO::FETCH_ASSOC)) {


                $TrackIDs = $dataRowPlantwiseWeights['TrackIDs'];
                $Species = $dataRowPlantwiseWeights['Species'];

                $RMConsumedHLkg = $dataRowPlantwiseWeights['RMConsumedHLkg'];

                $RMProcuredHLKg = $this->getRMProcuredHLKg($Species, $PlantID, $TrackIDs);
                $FarmHLkg = $this->getFarmHLkg($Species, $PlantID, $DateFilter);
                $Variancekg = $RMConsumedHLkg - ($RMProcuredHLKg + $FarmHLkg);
                $totalRmProcured =$RMProcuredHLKg + $FarmHLkg;
                //$VariancePercentage = $Variancekg / $RMConsumedHLkg;
                $VariancePercentage = $Variancekg / $totalRmProcured;

                $ReconsolationData['Plant'] = $dataRowProcessingStations['ProdCode'];
                $ArrayPlantwiseConsolidationWeights['Species'] = $Species;
                $ArrayPlantwiseConsolidationWeights['RMConsumedHLkg'] = number_format($dataRowPlantwiseWeights['RMConsumedHLkg'], 2);
                                // $ArrayPlantwiseConsolidationWeights['RMConsumedHLkg'] = $dataRowPlantwiseWeights['RMConsumedHLkg'];

                // $ArrayPlantwiseConsolidationWeights['RMConsumedHLkg'] = floor($dataRowPlantwiseWeights['RMConsumedHLkg']*100 )/100;

                // $truncated = floor($value * 100) / 100;
                $ArrayPlantwiseConsolidationWeights['RMProcuredHLKg'] = number_format($RMProcuredHLKg, 2);
                // $ArrayPlantwiseConsolidationWeights['RMProcuredHLKg'] = floor($RMProcuredHLKg*100 )/100;
                // $ArrayPlantwiseConsolidationWeights['RMProcuredHLKg'] = $RMProcuredHLKg;



                $ArrayPlantwiseConsolidationWeights['FarmHLkg'] = number_format($FarmHLkg, 2);
                // $ArrayPlantwiseConsolidationWeights['FarmHLkg'] = $FarmHLkg;
                // $ArrayPlantwiseConsolidationWeights['FarmHLkg'] = floor($FarmHLkg*100 )/100;


                $ArrayPlantwiseConsolidationWeights['Variancekg'] = number_format($Variancekg, 2);
                // $ArrayPlantwiseConsolidationWeights['Variancekg'] =$Variancekg;
                // $ArrayPlantwiseConsolidationWeights['Variancekg'] = floor($Variancekg*100 )/100;


                $ArrayPlantwiseConsolidationWeights['VariancePercentage'] = number_format($VariancePercentage, 2);
                // $ArrayPlantwiseConsolidationWeights['VariancePercentage'] = $VariancePercentage;
                // $ArrayPlantwiseConsolidationWeights['VariancePercentage'] = floor($VariancePercentage*100 )/100;
                // $ArrayPlantwiseConsolidationWeights['VariancePercentage'] = number_format((floor($VariancePercentage*100 )/100),2);
                $ArrayPlantwiseConsolidationWeights['totalRmProcured'] = number_format($totalRmProcured, 2);
                

                $HeadOnRawMeterialQty = $this->getHONWeight($Species, $PlantID, $DateFilter,0);//non farm
                $HeadOnFarmRawMeterialQty = $this->getHONWeight($Species, $PlantID, $DateFilter,1);//farm
                $ArrayPlantwiseConsolidationWeights['HeadOnRawMeterialQty'] = number_format($HeadOnRawMeterialQty, 2);
                $ArrayPlantwiseConsolidationWeights['HeadOnFarmRawMeterialQty'] = number_format($HeadOnFarmRawMeterialQty, 2);

                $TotalRMConsumedHLkg += $RMConsumedHLkg;
                $TotalRMProcuredHLKg += $RMProcuredHLKg;
                $TotalFarmHLkg += $FarmHLkg;
                $TotalVariancekg += $Variancekg;
                $GrandTotalRmProcured += $totalRmProcured;
                $TotalHeadOnRawMeterialQty += $HeadOnRawMeterialQty;
                $TotalHeadOnFarmRawMeterialQty += $HeadOnFarmRawMeterialQty;

                $AryBreakUp[] = $ArrayPlantwiseConsolidationWeights;

            }
            // echo $TotalRMConsumedHLkg . '<br>';
            //$TotalVariancePercentage = $TotalVariancekg / $TotalRMConsumedHLkg;
            $TotalVariancePercentage = $TotalVariancekg / $GrandTotalRmProcured;

            $ArrayTotals['TotalRMConsumedHLkg'] = number_format($TotalRMConsumedHLkg, 2);
            $ArrayTotals['TotalRMProcuredHLKg'] = number_format($TotalRMProcuredHLKg, 2);
            $ArrayTotals['TotalFarmHLkg'] = number_format($TotalFarmHLkg, 2);
            $ArrayTotals['TotalVariancekg'] = number_format($TotalVariancekg, 2);
            $ArrayTotals['TotalVariancePercentage'] = number_format($TotalVariancePercentage, 2);
            $ArrayTotals['GrandTotalRmProcured'] = number_format($GrandTotalRmProcured, 2);
            $ArrayTotals['TotalHeadOnRawMeterialQty'] = number_format($TotalHeadOnRawMeterialQty, 2);
            $ArrayTotals['TotalHeadOnFarmRawMeterialQty'] = number_format($TotalHeadOnFarmRawMeterialQty, 2);

          


            $ReconsolationData['PlantWiseWeightTotals'] = $ArrayTotals;
            $ReconsolationData['BreakUpData'] = $AryBreakUp;
            $AryBreakUp = [];
            // $AryTotals = [];
            $ArrayReconsolationData[] = $ReconsolationData;
        }
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['ReconsolationData'] = $ArrayReconsolationData;

        return $Data;
    }

    function getRMProcuredHLKg($Species, $PlantID, $TrackIDs)
    {


        $sqlRMProcuredHLKgFields = " SUM(CSD.ActualKgs) as KGs, SPVL.ProductDescription, CSD.ProductionTypeID,SPVL.ProductID, SPVL.TypeProduct,SPVL.Treatment,SPVL.FreezingType";

        $sqlRMProcuredHLKg = "SELECT $sqlRMProcuredHLKgFields FROM CsStockData AS CSD 
        left join CsDailyProductionData as CDPD on CDPD.ProductionID=CSD.ProductionID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID = CSD.VarietyID 
        LEFT JOIN AdminPackingStyles AS APS ON CSD.PackingStyleID = APS.PCKID
        LEFT JOIN RmProcessingStations AS RPS ON CSD.PlantID = RPS.ProcessingStationID
        LEFT JOIN CsStores AS CSR ON CSD.UnitID = CSR.StoreID
        WHERE CSD.PlantID='$PlantID' and Species='$Species' and CSD.ProductionTypeID!='Melting' and TrackID in ($TrackIDs)
        GROUP BY SPVL.ProductID, SPVL.TypeProduct,SPVL.Treatment,SPVL.FreezingType;";



        $stmtRMProcuredHLKg = $this->conn->prepare($sqlRMProcuredHLKg);
        $stmtRMProcuredHLKg->execute();
        // $RMProcuredHLKg = 0;
        while ($dataRowRMProcuredHLKg = $stmtRMProcuredHLKg->fetch(PDO::FETCH_ASSOC)) {
            $ProductID = $dataRowRMProcuredHLKg['ProductID'];
            $TypeProduct = $dataRowRMProcuredHLKg['TypeProduct'];
            $Treatment = $dataRowRMProcuredHLKg['Treatment'];
            $FreezingType = $dataRowRMProcuredHLKg['FreezingType'];
            $ActualKGs = $dataRowRMProcuredHLKg['KGs'];

            $SqlYieldPercentage = " SELECT YieldID, IFNULL((select YieldPercentage FROM `ProdStandardYields` where ProductID='$ProductID' and TypeOfProduct='$TypeProduct' 
           and Species='$Species' and TypeOfMaterial='HL' and Treatment='$Treatment'and FreezingType='$FreezingType' and status=1  ), 
        '')as HLYieldPercentage 
FROM `ProdStandardYields`order by YieldID desc  limit 1";



            $StmtYieldPercentage = $this->conn->prepare($SqlYieldPercentage);
            $StmtYieldPercentage->execute();
            $dataRowYieldPercentage = $StmtYieldPercentage->fetch(PDO::FETCH_ASSOC);
            $HLYieldPercentage = $dataRowYieldPercentage['HLYieldPercentage'];
            if ($HLYieldPercentage != '') {
                // $RMProcuredHLKg += ($ActualKGs / ($dataRowYieldPercentage['HLYieldPercentage'] * 100));
                $RMProcuredHLKg += (($ActualKGs / $dataRowYieldPercentage['HLYieldPercentage']) * 100);

            } else {
                $RMProcuredHLKg += $ActualKGs;
            }


        }

        return $RMProcuredHLKg;
    }

    function getFarmHLkg($Species, $PlantID, $DateFilter)
    {

        $sqlFarms = "SELECT group_concat(PurchaseStationID) as FarmID FROM `RmPurchaseStations` WHERE IsFarm=1";

        $stmtFarms = $this->conn->prepare($sqlFarms);
        $stmtFarms->execute();
        $dataRowFarms = $stmtFarms->fetch(PDO::FETCH_ASSOC);
        $Farms = $dataRowFarms['FarmID'];

        $sqlFields = "sum(HLWeight) as FarmHLkg";
        $sqlFarmHLkg = "SELECT $sqlFields FROM `PpcHLGradingDetails` as PHGD 
             left join PpcHLGradingBreakup as PHGB on PHGD.LotID=PHGB.LotID 
             left join RmLotDetatils as RLD on RLD.RmLotID=PHGB.LotID 
             left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RLD.RmReferenceID
             WHERE  $DateFilter and PlantID='$PlantID'   and Species ='$Species' and ArrivalStationID in ($Farms) ";


        $stmtFarmHLkg = $this->conn->prepare($sqlFarmHLkg);
        $stmtFarmHLkg->execute();
        $dataRowFarmHLkg = $stmtFarmHLkg->fetch(PDO::FETCH_ASSOC);
        $FarmHLkg = $dataRowFarmHLkg['FarmHLkg'];
        return $FarmHLkg;
    }

    function getHONWeight($Species, $PlantID, $DateFilter, $IsFarm)
    {

        $sqlFarms = "SELECT group_concat(PurchaseStationID) as CenteRIds FROM `RmPurchaseStations` WHERE IsFarm=$IsFarm";

        $stmtFarms = $this->conn->prepare($sqlFarms);
        $stmtFarms->execute();
        $dataRowFarms = $stmtFarms->fetch(PDO::FETCH_ASSOC);
        $Farms = $dataRowFarms['CenteRIds'];
//ArrivalDate
       $sql = "SELECT SUM(TotalWeight) TotalWeight FROM RmOverAllArrival WHERE $DateFilter and PlantID='$PlantID'   and Species ='$Species' and CenterID in ($Farms)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $dataStmt = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalWeight = $dataStmt['TotalWeight'];
        return $TotalWeight;
    }

}
