<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class ConsolidationDailyAndMonthlyReportController
{
    private $conn;
    private $auditLogger;
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $VarietyData;
    private $VarietyDataProd;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'PreProcessing');
    }
    public function getConsolidationDetails($RequestBody)
    {

        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $ArrivalPlant = $RequestBody['ReceivingPlantID'];

        $Query = '';
        if ($ArrivalPlant != '') {
            $Query .= " and RmRD.ReceivingPlantID='$ArrivalPlant'";
        }

        $DateFilters = " BETWEEN '$FromDate' AND '$ToDate'";

        $this->updateProductionData($DateFilters, $Query);
        $this->VarietyData = $this->getVarietyData($DateFilters, $Query);
        $this->VarietyDataProd = $this->getVarietyDataProd($DateFilters, $Query);
        $GradewiseData = $this->getGradewiseData($DateFilters, $Query, $this->VarietyData);

        $ArrayCountGroupData = $this->getCountGroupWiseData($RequestBody);
        $ArrayPurchaseData = $this->getArrivalWiseData($RequestBody);
        // $TotalsData = $this->getConsolidationData($DateFilters, $Query, $VarietyData, '');
        //  $TotalsData = $this->getConsolidationData($DateFilters, $Query, '');
        $TotalsData = $this->getVarietyTotal();
        $ResultData = $this->getDifferenceHLWeight($TotalsData);
        $GrandTotals = $this->getGrandTotals($TotalsData);
        // var_dump($TotalsData);
        // exit;

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['ArrayCountGroupData'] = $ArrayCountGroupData;
        $Data['ArrayPurchaseData'] = $ArrayPurchaseData;
        $Data['GradewiseData'] = $GradewiseData;
        $Data['VarietyData'] = $this->VarietyData;
        $Data['TotalsData'] = $TotalsData;
        $Data['ResultData'] = $ResultData;
        $Data['GrandTotals'] = $GrandTotals;

        return $Data;
    }

    public function getCountGroupWiseData($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $ArrivalPlant = $RequestBody['ReceivingPlantID'];
        // $Species = $RequestBody['Species'];
        // $Variety = $RequestBody['Variety'];

        $Query = '';

        if ($ArrivalPlant != '') {
            $Query .= " and RAD.ProcessingStationID='$ArrivalPlant'";
        }

        $DateFilters = " BETWEEN '$FromDate' AND '$ToDate'";

        $sqlFields = "Count, sum(Weight) as Weight, sum(Weight*Rate) as TotalAmount";
        $sqlQuery = "SELECT $sqlFields FROM RmArrivalDetatils as RAD
        LEFT JOIN RmArrivalBreakUpDetails as RAB ON RAB.ArrivalID=RAD.RmArrivalID
        LEFT JOIN RmOverAllArrival AS OAL ON OAL.CenterID=RAB.ArrivalBrkUpID
        WHERE RAD.ArrivalDate $DateFilters $Query GROUP BY Count ORDER BY Count ASC";


        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayData = [];
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
        $Data['CountGroupData'] = $ArrayData;

        return $Data;
    }

    public function getArrivalWiseData($RequestBody)
    {

        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $ArrivalPlant = $RequestBody['ReceivingPlantID'];

        $Query = '';

        if ($ArrivalPlant != '') {
            $Query .= " and RAD.ProcessingStationID='$ArrivalPlant'";
        }

        $DateFilters = " BETWEEN '$FromDate' AND '$ToDate'";

        $sqlFields = "Count,sum(Weight) as Weight, sum(Weight*Rate) as TotalAmount";
        $sqlQuery = "SELECT $sqlFields FROM RmArrivalDetatils AS RAD
        LEFT JOIN RmArrivalBreakUpDetails AS RAB ON RAB.ArrivalID=RAD.RmArrivalID
        LEFT JOIN RmOverAllArrival AS OAL ON OAL.CenterID=RAB.ArrivalBrkUpID
        WHERE RAD.ArrivalDate $DateFilters $Query GROUP BY Count ORDER BY Count ASC";

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayData = [];
        $TotalWeight = 0;
        $TotalAmount = 0;
        $i = 0;

        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayAssumedList['SNO'] = ++$i;
            $ArrayAssumedList['Count'] = $dataRow['Count'];
            $ArrayAssumedList['Weight'] = $dataRow['Weight'];

            $TotalWeight += $dataRow['Weight'];

            $ArrayData[] = $ArrayAssumedList;
        }

        $Data['TotalPurchaseWeight'] = $TotalWeight;
        $Data['TotalAmount'] = $TotalAmount;
        $Data['PurchaseData'] = $ArrayData;

        return $Data;
    }



    function getGradewiseData($DateFilters, $Query, $VarietyData)
    {

        $sqlSelect = "SELECT GradingGrade,round(SUM(GradingWeight),3) AS GradingWeight FROM `ConsolidatedTempTable` GROUP by GradingGrade ORDER by abs(GradingGrade) asc";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ArrayData = [];
        //$ArrayData[] = $ArrayList;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $PackingGrade = $dataRow['GradingGrade'];
            $GradingGrade = $dataRow['GradingGrade'];
            $GradingWeight = $dataRow['GradingWeight'];
            $ConsolidationData = $this->getConsolidationData($DateFilters, $Query, $PackingGrade);
            $ArrayList['PackingGrade'] = $PackingGrade;
            $ArrayList['GradingGrade'] = $GradingGrade;
            $ArrayList['GradingWeight'] = $GradingWeight;
            $ArrayList['ConsolidationData'] = $ConsolidationData;
            $ArrayData[] = $ArrayList;
        }

        return $ArrayData;
    }




    function updateProductionData($DateFilters, $Query)
    {


        $sqlSelect = "DROP TABLE IF EXISTS `ConsolidatedTempTable`";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();


        $sqlSelect = "SELECT GROUP_CONCAT(ReceivingID) as TrackID FROM `RmReceivingDetails` as
        RmRD WHERE RmRD.IsStoreMaterial=0 and RmRD.ArrivalDate $DateFilters $Query ";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        $TrackID = $dataRow['TrackID'];



        $sqlSelect = "UPDATE ProdFreezingData AS PFD JOIN (
        SELECT
        PFD1.GradingBreakupID,
        SUM(PFD1.ProductionWeight) / (SELECT SUM(HLWeight)
                                      FROM PpcHLGradingBreakup
                                      WHERE DataID = PFD1.GradingBreakupID) AS AHLYield
        FROM ProdFreezingData AS PFD1
        GROUP BY PFD1.GradingBreakupID ) AS subquery
        ON PFD.GradingBreakupID = subquery.GradingBreakupID
        SET PFD.AHLWeight = round((PFD.ProductionWeight)/subquery.AHLYield,3) WHERE TrackID in ($TrackID)";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $this->auditLogger->logUpdate('ProdFreezingData', $TrackID, null, null, $RequestBody, [
            'ReferenceNo' => $TrackID,
            'Remarks' => 'Production data updated for consolidation report',
        ]);

        // SUM(ProductionWeight) ProductionWeight,
        $sqlSelect = "CREATE TABLE ConsolidatedTempTable AS
SELECT 
HLVA.Species as GradingSpecies,
HLVL.Species as ProductionSpecies,
HLVA.ProductID as GradingVariety,
HLVL.ShortName as ProductionVarity,
HLGB.PackingGrade as GradingGrade,
PFD.Grade as ProductionGrade,
HLVL.TypeProduct as ProductionTypeProduct,
HLVL.FreezingType as ProductionFreezingType,
PFD.PackingStyleID as PackingStyleID,
APS.PackingStyle,
HLGB.Treatment as GradingTreatment,
PFD.SockingType as ProductionTreatment,
SUM(AHLWeight) as GradingWeight,
SUM(PFD.ProductionWeight) ProductionWeight,
GROUP_CONCAT(HLGB.DataID) as GradingBreakingID,
GROUP_CONCAT(DISTINCT(GradingBreakupID)) as GBK_ID,
PFD.VarietyID as VarietyID

FROM `PpcHLGradingDetails` AS HLGD
LEFT JOIN PpcHLGradingBreakup AS HLGB  ON HLGD.LotID = HLGB.LotID
LEFT JOIN PpcHLVAVarietyList as HLVA ON HLGB.VarietyID=HLVA.VarietyID
left join  ProdFreezingData as PFD on HLGB.DataID=PFD.GradingBreakupID
left join ScrmPIVarietyList as HLVL on PFD.VarietyID=HLVL.VarietyID 
LEFT join AdminPackingStyles as  APS on APS.PCKID=PFD.PackingStyleID

 WHERE HLGD.TrackID in ($TrackID) and HLGB.DataID IS NOT NULL and HLGB.HLWeight !=0 
 GROUP by HLVL.Species,PFD.Grade,HLGB.PackingGrade,HLGB.Treatment,PFD.SockingType,PFD.PackingStyleID,HLVL.ShortName,HLVL.FreezingType, 
 HLVL.TypeProduct
  ORDER by HLVL.Species,HLVL.TypeProduct DESC,abs(PFD.Grade) ASC;"; //uma FreezingType
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $sqlSelect = "UPDATE ConsolidatedTempTable AS ytn1
SET GradingWeight = (
    SELECT SUM(HLWeight)
    FROM PpcHLGradingBreakup AS HLGB
    WHERE FIND_IN_SET(HLGB.DataID, ytn1.GradingBreakingID) > 0
)
WHERE EXISTS (
    SELECT 1
    FROM PpcHLGradingBreakup AS HLGB
    WHERE FIND_IN_SET(HLGB.DataID, ytn1.GradingBreakingID) > 0 and  ytn1.GBK_ID is NULL
)";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

    }


    function getConsolidationData($DateFilters, $Query, $PackingGrade)
    {
        $ArrayMainData = [];
        $VarietyData = $this->VarietyData;
        $VarietyDataProd = $this->VarietyDataProd;


        $ItemGradewiseProductionWeight = 0;
        $ItemProductionWeight = 0;
        $ItemHLWeight = 0;
        $ItemDifference = 0;


        foreach ($VarietyData as $ItemVariety) {


            $ItemGradewiseProductionWeight = 0;
            $ItemProductionWeight = 0;
            $ItemHLWeight = 0;
            $ItemDifference = 0;


            $ProdGradingSpecies = $ItemVariety['GradingSpecies'];
            $ProdProductionSpecies = $ItemVariety['ProductionSpecies'];
            $ProdProductionVarity = $ItemVariety['ProductionVarity'];

            if ($ProdProductionVarity == null) {

                foreach ($VarietyDataProd as $ItemVarietyProd) {
                    $GradingGradeProd = $ItemVarietyProd['GradingGrade'];
                    $ProductionVarity = $ItemVarietyProd['ProductionVarity'];
                    if ($GradingGradeProd == $PackingGrade && $ProductionVarity == null) {
                        $ItemSpeciesProd = $ItemVarietyProd['GradingSpecies'];
                        $ItemShortNameProd = $ItemVarietyProd['GradingVariety'];
                        $ItemTreatmentProd = $ItemVarietyProd['GradingTreatment'];
                        $GradingGradeProd = $ItemVarietyProd['GradingGrade'];
                        $GradingWeight = $ItemVarietyProd['GradingWeight'] ? $ItemVarietyProd['GradingWeight'] : 0;
                        $ProductionWeight = $ItemVarietyProd['ProductionWeight'] ? $ItemVarietyProd['ProductionWeight'] : 0;
                        $ItemVarietyProd['GradingWeight'];

                        $ItemGradewiseProductionWeight += $ProductionWeight;
                        $ItemProductionWeight += $ProductionWeight;
                        $ItemHLWeight += $GradingWeight;
                        $ItemDifference = $ItemHLWeight - $ItemProductionWeight;
                    }

                }

                $ItemDifference = $ItemHLWeight - $ItemProductionWeight;
                $MainDataList['Species'] = $ItemSpeciesProd;
                $MainDataList['ShortName'] = $ItemShortNameProd;
                $MainDataList['ProductionWeight'] = $ItemProductionWeight;
                $MainDataList['GradewiseProductionWeight'] = $ItemGradewiseProductionWeight;
                $MainDataList['HLWeight'] = $ItemHLWeight;
                $MainDataList['Difference'] = $ItemDifference;

                $ArrayMainData[] = $MainDataList;

            }
        }


        /************************************************** */

        foreach ($VarietyData as $ItemVariety) {


            $ItemGradewiseProductionWeight = 0;
            $ItemProductionWeight = 0;
            $ItemHLWeight = 0;
            $ItemDifference = 0;


            $ProdGradingSpecies = $ItemVariety['GradingSpecies'];
            $ProdProductionSpecies = $ItemVariety['ProductionSpecies'];
            $ProdGradingVariety = $ItemVariety['GradingVariety'];
            $ProdProductionVarity = $ItemVariety['ProductionVarity'];
            $ProdGradingGrade = $ItemVariety['GradingGrade'];
            $ProdProductionGrade = $ItemVariety['ProductionGrade'];
            $ProdGradingTreatment = $ItemVariety['GradingTreatment'];
            $ProdProductionTreatment = $ItemVariety['ProductionTreatment'];
            $ProdPackingStyleID = $ItemVariety['PackingStyleID'];
            $ProdProductionTypeProduct = $ItemVariety['ProductionTypeProduct'];


            if ($ProdProductionVarity != null) {
                foreach ($VarietyDataProd as $ItemVarietyProd) {

                    $ItemSpecies = $ItemVarietyProd['ProductionSpecies'];
                    $ItemShortName = $ItemVarietyProd['ProductionVarity'];
                    $ItemTreatment = $ItemVarietyProd['ProductionTreatment'];
                    $ItemGradingGrade = $ItemVarietyProd['GradingGrade'];
                    $PackingStyleID = $ItemVarietyProd['PackingStyleID'];
                    $ItemProductionTypeProduct = $ItemVarietyProd['ProductionTypeProduct'];




                    if (
                        ($ItemSpecies == $ProdProductionSpecies) &&
                        ($ItemShortName == $ProdProductionVarity) &&
                        ($ItemTreatment == $ProdProductionTreatment) &&
                        ($PackingStyleID == $ProdPackingStyleID) &&
                        ($ItemGradingGrade == $PackingGrade) &&
                        ($ItemProductionTypeProduct == $ProdProductionTypeProduct)
                    ) {

                        $GradingWeight = $ItemVarietyProd['GradingWeight'] ? $ItemVarietyProd['GradingWeight'] : 0;
                        $ProductionWeight = $ItemVarietyProd['ProductionWeight'] ? $ItemVarietyProd['ProductionWeight'] : 0;
                        $ItemProductionWeight += $ProductionWeight;
                        $ItemGradewiseProductionWeight += $ProductionWeight;
                        $ItemHLWeight += $GradingWeight;
                    }

                }

                $ItemDifference = $ItemHLWeight - $ItemProductionWeight;
                $MainDataList['Species'] = $ItemSpeciesProd;
                $MainDataList['ShortName'] = $ItemShortNameProd;
                $MainDataList['ProductionWeight'] = $ItemProductionWeight;
                $MainDataList['GradewiseProductionWeight'] = $ItemGradewiseProductionWeight;
                $MainDataList['HLWeight'] = $ItemHLWeight;
                $MainDataList['Difference'] = $ItemDifference;

                $ArrayMainData[] = $MainDataList;


            }



        }



        return $ArrayMainData;
    }


    // Variety Details
    function getVarietyData($DateFilters, $Query)
    {


        $sqlSelect = "SELECT *,SUM(GradingWeight) as TotalGradingWeight,
        SUM(ProductionWeight) as TotalProductionWeight,GROUP_CONCAT(DISTINCT VarietyID) AS VarietyIds FROM `ConsolidatedTempTable` 
        GROUP by ProductionSpecies,ProductionVarity,ProductionTreatment,PackingStyleID ,ProductionTypeProduct,ProductionFreezingType
        ORDER by ProductionVarity,abs(GradingGrade) ASC";//uma 

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $sqlRejTracks = "SELECT GROUP_CONCAT(DISTINCT ReceivingID) AS TrackIds,ArrivalDate FROM RmReceivingDetails AS RmRD WHERE RmRD.ArrivalDate $DateFilters $Query AND VehicleID='0' AND SupervisorID='0' AND `TrackCode` REGEXP 'REJ' AND OutStage=3 AND IsStoreMaterial=0";

        $stmtRejTracks = $this->conn->prepare($sqlRejTracks);
        $stmtRejTracks->execute();
        $dataRejTracks = $stmtRejTracks->fetch(PDO::FETCH_ASSOC);

        $updatedFreezing = '';

        // $ArrayData = [];
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            // echo $dataRow['ReceivingID']; exit;

            if ($dataRejTracks && isset($dataRejTracks['TrackIds']) && $dataRow['VarietyIds'] != null && $dataRow['VarietyIds'] != '') {
                $sqlFreezing = "SELECT COALESCE(SUM(COALESCE(ProductionWeight,0)),0) AS RejectionWeight,GROUP_CONCAT(DISTINCT ProductionID) AS FreezingIds FROM ProdFreezingData WHERE TrackID IN (" . $dataRejTracks['TrackIds'] . ") AND VarietyID IN (" . $dataRow['VarietyIds'] . ")";
                $stmtFreezing = $this->conn->prepare($sqlFreezing);
                $stmtFreezing->execute();
                $dataFreezing = $stmtFreezing->fetch(PDO::FETCH_ASSOC);
                $ArrayList['RejectionWeight'] = $dataFreezing['RejectionWeight'];
                if ($dataFreezing['FreezingIds'] != null && $dataFreezing['FreezingIds'] != '') {
                    if ($updatedFreezing != '') {
                        $updatedFreezing = $updatedFreezing . "," . $dataFreezing['FreezingIds'];
                    } else {
                        $updatedFreezing = $updatedFreezing . $dataFreezing['FreezingIds'];
                    }
                }
            } else {
                $ArrayList['RejectionWeight'] = 0.000;
            }

            $ArrayList['GradingSpecies'] = $dataRow['GradingSpecies'];
            $ArrayList['ProductionSpecies'] = $dataRow['ProductionSpecies'];
            $ArrayList['GradingVariety'] = $dataRow['GradingVariety'];
            $ArrayList['ProductionVarity'] = $dataRow['ProductionVarity'];
            $ArrayList['GradingTreatment'] = $dataRow['GradingTreatment'];
            $ArrayList['ProductionTreatment'] = $dataRow['ProductionTreatment'];
            $ArrayList['GradingGrade'] = $dataRow['GradingGrade'];
            $ArrayList['ProductionGrade'] = $dataRow['ProductionGrade'];
            $ArrayList['GradingWeight'] = $dataRow['TotalGradingWeight'];
            $ArrayList['ProductionWeight'] = $dataRow['TotalProductionWeight'];
            $ArrayList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayList['PackingStyleID'] = $dataRow['PackingStyleID'];
            $ArrayList['ProductionTypeProduct'] = $dataRow['ProductionTypeProduct'];
            $ArrayList['ProductionFreezingType'] = $dataRow['ProductionFreezingType'];


            $ArrayData[] = $ArrayList;
        }
        if ($dataRejTracks && isset($dataRejTracks['TrackIds'])) {
            $sqlFreezing = "SELECT SUM(COALESCE(PFD.ProductionWeight,0)) AS RejectionWeight,GROUP_CONCAT(DISTINCT PFD.ProductionID) AS FreezingIds,PFD.VarietyID,PFD.PackingStyleID,PVL.Grade AS GradingGrade,PVL.ShortName,PVL.Species,PFD.FreezingType,PFD.SockingType,PVL.Treatment,PFD.Grade AS ProductionGrade,PVL.TypeProduct,APS.PackingStyle
            FROM ProdFreezingData AS PFD LEFT JOIN ScrmPIVarietyList AS PVL ON PFD.VarietyID=PVL.VarietyID LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=PFD.PackingStyleID 
            WHERE PFD.TrackID IN (" . $dataRejTracks['TrackIds'] . ") AND PFD.ProductionID NOT IN (" . $updatedFreezing . ") GROUP BY PFD.VarietyID";

            $stmtFreezing = $this->conn->prepare($sqlFreezing);
            $stmtFreezing->execute();
            while ($dataRow = $stmtFreezing->fetch(PDO::FETCH_ASSOC)) {
                if ($dataRow && isset($dataRow)) {
                    $ArrayList['GradingSpecies'] = $dataRow['Species'];
                    $ArrayList['ProductionSpecies'] = $dataRow['Species'];
                    $ArrayList['GradingVariety'] = $dataRow['ShortName'];
                    $ArrayList['ProductionVarity'] = $dataRow['ShortName'];
                    $ArrayList['GradingTreatment'] = $dataRow['Treatment'];
                    $ArrayList['ProductionTreatment'] = $dataRow['SockingType'];
                    $ArrayList['GradingGrade'] = $dataRow['GradingGrade'];
                    $ArrayList['ProductionGrade'] = $dataRow['ProductionGrade'];
                    $ArrayList['GradingWeight'] = 0.000;
                    $ArrayList['ProductionWeight'] = 0.000;
                    $ArrayList['PackingStyle'] = $dataRow['PackingStyle'];
                    $ArrayList['PackingStyleID'] = $dataRow['PackingStyleID'];
                    $ArrayList['ProductionTypeProduct'] = $dataRow['TypeProduct'];
                    $ArrayList['ProductionFreezingType'] = $dataRow['FreezingType'];
                    $ArrayList['RejectionWeight'] = $dataRow['RejectionWeight'];

                    $ArrayData[] = $ArrayList;
                }
            }
        }

        return $ArrayData;
    }

    function getVarietyDataProd($DateFilters, $Query)
    {

        $sqlSelect = "SELECT * FROM `ConsolidatedTempTable` ORDER by abs(GradingGrade) ASC";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        $ArrayData = [];
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            // echo $dataRow['ReceivingID']; exit;

            $ArrayList['GradingSpecies'] = $dataRow['GradingSpecies'];
            $ArrayList['ProductionSpecies'] = $dataRow['ProductionSpecies'];
            $ArrayList['GradingVariety'] = $dataRow['GradingVariety'];
            $ArrayList['ProductionVarity'] = $dataRow['ProductionVarity'];
            $ArrayList['GradingTreatment'] = $dataRow['GradingTreatment'];
            $ArrayList['ProductionTreatment'] = $dataRow['ProductionTreatment'];
            $ArrayList['GradingGrade'] = $dataRow['GradingGrade'];
            $ArrayList['ProductionGrade'] = $dataRow['ProductionGrade'];
            $ArrayList['GradingWeight'] = $dataRow['GradingWeight'];
            $ArrayList['ProductionWeight'] = $dataRow['ProductionWeight'];
            $ArrayList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayList['PackingStyleID'] = $dataRow['PackingStyleID'];
            $ArrayList['ProductionTypeProduct'] = $dataRow['ProductionTypeProduct'];


            // $ArrayList['VarietyName'] = $dataRow['ProductionVarity'];
            // $ArrayList['ShortName'] = $dataRow['ProductionVarity'];
            // $ArrayList['Species'] = $dataRow['GradingSpecies'];
            // $ArrayList['PackingStyle'] = $dataRow['PackingStyle'];
            // $ArrayList['PackingStyleID'] = $dataRow['PackingStyleID'];
            // $ArrayList['Treatment'] = $dataRow['GradingTreatment'];
            // $ArrayList['GradingWeight'] = $dataRow['TotalGradingWeight'];
            // $ArrayList['ProductionWeight'] = $dataRow['TotalProductionWeight'];
            $ArrayData[] = $ArrayList;
        }

        return $ArrayData;
    }







    function getVarietyTotal()
    {


        $VarietyData = $this->VarietyData;

        foreach ($VarietyData as $ItemVariety) {
            $ItemSpecies = $ItemVariety['ProductionSpecies'];
            $ItemShortName = $ItemVariety['ProductionVarity'];
            $GradingWeight = $ItemVariety['GradingWeight'];
            $ProductionWeight = $ItemVariety['ProductionWeight'];
            $RejectionWeight = $ItemVariety['RejectionWeight'];
            $ItemGradewiseProductionWeight = 0;
            $ItemProductionWeight = 0;
            $ItemHLWeight = 0;
            $ItemDifference = 0;

            $ProductionWeight = $ProductionWeight + $RejectionWeight;

            $MainDataList['Species'] = $ItemSpecies;
            $MainDataList['ShortName'] = $ItemShortName;
            $MainDataList['ProductionWeight'] = $ProductionWeight;
            $MainDataList['GradewiseProductionWeight'] = $ProductionWeight;
            $MainDataList['HLWeight'] = $GradingWeight;
            $MainDataList['Difference'] = $GradingWeight - $ProductionWeight;

            $ArrayMainData[] = $MainDataList;
        }

        return $ArrayMainData;
    }




    function getDifferenceHLWeight($TotalsData)
    {
        $ResultData = [];
        foreach ($TotalsData as $ItemTotals) {
            $HLWeight = $ItemTotals['HLWeight'];
            // $ProductionWeight = $ItemTotals['ProductionWeight'];
            $Difference = $ItemTotals['Difference'];

            $Result = ($Difference / $HLWeight) * 100;

            $ResultList['DiffernceYield'] = round($Result, 2);

            $ResultData[] = $ResultList;
        }

        return $ResultData;
    }

    function getGrandTotals($TotalsData)
    {

        $VarietyData = $this->VarietyData;

        $RejectionTotalWeight = 0;
         foreach ($VarietyData as $ItemVariety) {
            $RejectionTotalWeight = $RejectionTotalWeight + $ItemVariety['RejectionWeight'];
         }



        $sqlSelect = "SELECT sum(GradingWeight) as  GradingWeight,sum(ProductionWeight) as ProductionWeight FROM `ConsolidatedTempTable` ";


        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        $totalProduction = $dataRow['ProductionWeight'] + $RejectionTotalWeight;
        $ResultData = [
            'TotalRejection' => $RejectionTotalWeight,
            'TotalHLWeight' => $dataRow['GradingWeight'],
            'TotalProductionWeight' => $dataRow['ProductionWeight'],
            'TotalWeight' => $totalProduction,
            'TotalDifference' => $totalProduction - $dataRow['GradingWeight']
        ];

        return $ResultData;
    }


    function largestMultipleOfTenLessThan($number)
    {
        return floor($number - ($number % 10));
    }

    function smallestMultipleOfTenGreaterThan($number)
    {
        return floor($number + (10 - ($number % 10)));
    }
}
