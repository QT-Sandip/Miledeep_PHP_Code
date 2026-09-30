<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';

class AntiDumpingReportController
{
    private $conn;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getPoTotal($RequestBody){
        $PoID = $RequestBody['PoID'];
        $UnitID = $RequestBody['ColdstoreID'];
        $ProductionDate = $RequestBody['ProductionDate'];
        $VarietyID = $RequestBody['VarietyID'];

        $Query = '';
        $openingStockData = [];

        if($VarietyID != ''){
            // $sqlQuery = "SELECT ShortName FROM scrmpivarietylist where VarietyID='$VarietyID'";

            // $stmt = $this->conn->prepare($sqlQuery);
            // $stmt->execute();
            // $rowVariety = $stmt->fetch(PDO::FETCH_ASSOC);
            // $Variety = $rowVariety['ShortName'];
            $Query .= " AND SPVL.VarietyID='$VarietyID'";
            $Query .= " AND ProductionDate='$ProductionDate'";

            $sqlOpeningQuery = "SELECT * FROM `advarietywiseopeningstock` WHERE  VarietyID='$VarietyID' AND StockDate='$ProductionDate' AND `PlantID`='$UnitID'";

            $stmtOpening = $this->conn->prepare($sqlOpeningQuery);
            $stmtOpening->execute();
            $rowOpening = $stmtOpening->fetch(PDO::FETCH_ASSOC);
            if($rowOpening){
                $openingStockData['OpeningStock'] = $rowOpening['OpeningStock'];
                $openingStockData['ClosingStock'] = $rowOpening['ClosingStock'];
            } else{
                $sqlOpeningQuery = "SELECT * FROM `advarietywiseopeningstock` WHERE  VarietyID='$VarietyID' AND StockDate<'$ProductionDate' AND `PlantID`='$UnitID' ORDER BY StockDate DESC LIMIT 1";

                $stmtOpening = $this->conn->prepare($sqlOpeningQuery);
                $stmtOpening->execute();
                $rowOpening = $stmtOpening->fetch(PDO::FETCH_ASSOC);

                if($rowOpening){
                    $openingStockData['OpeningStock'] = $rowOpening['ClosingStock'];
                    $openingStockData['ClosingStock'] = $rowOpening['ClosingStock'];
                }

            }
        } else if($PoID != ''){
            $Query .= " AND PoID='$PoID'";
        }else{
            $Query .= " AND ProductionDate='$ProductionDate'";
        }

        if ($UnitID != '') {
            $Query .= " and PlantID='$UnitID'";
        }

        $sqlQuery = "SELECT CAD.GroupType,sum(ActualSlabs) AS Total  FROM `ADAntiDumpingDetails` AS CAD LEFT JOIN scrmpivarietylist AS SPVL ON CAD.VarietyID=SPVL.VarietyID WHERE CAD.ProductionTypeID != 'Opening Stock' $Query GROUP BY CAD.GroupType";

        // echo $sqlQuery;exit;
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $Result = [];
        while($rowSlabs = $stmt->fetch(PDO::FETCH_ASSOC)){
            if($rowSlabs['GroupType'] == 1){
                $Result['TotalInwardSlabs'] = $rowSlabs['Total'];
            } else if($rowSlabs['GroupType'] == 2){
                $Result['TotalOutwardSlabs'] = $rowSlabs['Total'];
            } else{
                $Result['TotalOtherSlabs'] = $rowSlabs['Total'];
            }
        }
        
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['TotalSlabs'] = $Result;
        $Data['OpeningStock'] = $openingStockData;

        return $Data;
    }

    public function getAllGrades($RequestBody){
        $PoID = $RequestBody['PoID'];
        $VarietyID = $RequestBody['VarietyID'];

        $Query = '';
        
        if($PoID != ''){
            $Query .= " WHERE PoID='$PoID'";
        }

        $sqlQuery = "SELECT * FROM ScrmPIVarietyList where VarietyID='$VarietyID'";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $rowVariety = $stmt->fetch(PDO::FETCH_ASSOC);

        $result = [];
        if(!empty($rowVariety)){
            $sqlGradesQuery = "
SELECT spvl.*
FROM ScrmPIVarietyList spvl
INNER JOIN (
    SELECT Grade, MAX(VarietyID) AS MaxVarietyID
    FROM ScrmPIVarietyList
    WHERE PackingStyle = '" . $rowVariety['PackingStyle'] . "'
      AND FreezingType = '" . $rowVariety['FreezingType'] . "'
      AND Treatment = '" . $rowVariety['Treatment'] . "'
      AND ShortName = '" . $rowVariety['ShortName'] . "'
      AND Species = '" . $rowVariety['Species'] . "'
      AND TypeProduct='" . $rowVariety['TypeProduct'] . "' 
      AND Glaze='" . $rowVariety['Glaze'] . "'
        AND BrandName='" . $rowVariety['BrandName'] . "'
    AND UltimateConsignee='" . $rowVariety['UltimateConsignee'] . "'
                                    AND PreparationType='" . $rowVariety['PreparationType'] . "'
                                    AND (
                                            (('" . $rowVariety['GlazePerc'] . "' IS NULL OR '" . $rowVariety['GlazePerc'] . "' = 0) 
                                                AND (GlazePerc IS NULL OR GlazePerc = 0))
                                        OR (GlazePerc = '" . $rowVariety['GlazePerc'] . "')
                                    ) AND Status=1
                                GROUP BY Grade
) AS latest
ON spvl.Grade = latest.Grade
AND spvl.VarietyID = latest.MaxVarietyID
ORDER BY spvl.Grade
";
            $stmt = $this->conn->prepare($sqlGradesQuery);
            $stmt->execute();
            while ($rowGrade = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $rowGrade;
            }
        }
        
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['data'] = $result;

        return $Data;
    }

    public function getAllPoNumbers($RequestBody)
    {
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $ProductionDate = $RequestBody['ProductionDate'];
        $ProductionToDate = $RequestBody['ProductionToDate'];

        $Query = '';
        if ($ProductionDate != '' && $ProductionToDate != '') {
            $Query = "WHERE SCI.StockOutDate BETWEEN '$ProductionDate' AND '$ProductionToDate'";
            if ($ProductionPlant != '') {
                $Query .= " AND FIND_IN_SET($ProductionPlant, SPD.ProductionPlantID)";
            }
        } elseif ($ProductionDate != '') {
            $Query = "WHERE SCI.StockOutDate='$ProductionDate'";
            if ($ProductionPlant != '') {
                $Query .= " AND RPS.ProcessingStationID=$ProductionPlant AND AED.ExpID IS NOT NULL";
            }
        }
        if ($ProductionPlant != '') {
            if ($Query != '') {
                $Query .= " AND RPS.ProcessingStationID=$ProductionPlant AND AED.ExpID IS NOT NULL";
            } else {
                 $Query .= " WHERE RPS.ProcessingStationID=$ProductionPlant AND AED.ExpID IS NOT NULL";
            }
        }

        $result = [];
        $sqlQuery = "SELECT SPD.PoNo,SPD.ProductionPlantID,SPD.PoID FROM `ScrmPoData` AS SPD LEFT JOIN ScrmCustomInvoices AS SCI ON SPD.PoID=SCI.PiID
        LEFT JOIN AdminExporterDetails AS AED ON SPD.ProcessorID=AED.ExpID 
        LEFT JOIN RmProcessingStations AS RPS ON AED.InvShortCode=RPS.ProdCode $Query";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($rowGrade = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $rowGrade;
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['qry'] = $sqlQuery;
        $Data['data'] = $result;
        return $Data;
    }

    public function getDailyProductionDetails($RequestBody)
    {

        $ProductionDate = $RequestBody['ProductionDate'];
        $ProductionEndDate = $RequestBody['ProductionToDate'];
        $UnitID = $RequestBody['ProductionPlant'];
        $ProductionTypeID = $RequestBody['Group'];
        $GroupType = $RequestBody['GroupType'];
        $PoNumber = $RequestBody['PoNumber'];
        $Variety = $RequestBody['Variety'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $Grade = $RequestBody['Grade'];
        $FreezingType = $RequestBody['FreezingType'];
        $Soaking = $RequestBody['Soaking'];
        $ProductCategory = $RequestBody['ProductCategory'];

        // print_r("Status". $Status);
        // print_r("StProductionDateatus". $ProductionDate);
        $Query = '';

        if ($ProductionEndDate != '') {
            $Query = " WHERE ProductionDate BETWEEN '$ProductionDate' AND '$ProductionEndDate' ";
        } else {
            $Query = " WHERE ProductionDate='$ProductionDate'";
        }

        if ($UnitID != '') {
            $Query .= " and CSD.PlantID='$UnitID'";
        }
        if ($ProductionTypeID != '') {
            $Query .= " and CSD.ProductionTypeID='$ProductionTypeID'";
        }

         if ($GroupType != '') {
            $Query .= " and CSD.GroupType='$GroupType'";
        }
        if ($PoNumber != '') {
            $Query .= " and CSD.PoID='$PoNumber'";
        }

        if ($Variety != '') {
            $Query .= " and CSD.VarietyID='$Variety'";
        }
        if ($PackingStyle != '') {
            $Query .= " and CSD.PackingStyleID='$PackingStyle'";
        }

        if ($Grade != '') {
            $Query .= " and CSD.Grade='$Grade'";
        }
        if ($FreezingType != '') {
            $Query .= " and CSD.FreezingType='$FreezingType'";
        }
         if ($Soaking != '') {
            $Query .= " and CSD.SockingType='$Soaking'";
        }
        if ($ProductCategory != '') {
            $Query .= " and CSD.ProductCategoryID='$ProductCategory'";
        }


        $DailyProductionList = $this->getProductionlist($Query, $RequestBody);



        // $ProdID = $this->postDailyProductionDetails($RequestBody)['NewAddedProdId'];

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['DailyProductionList'] = $DailyProductionList['ArrayData'];
        $Data['TotalCount'] = $DailyProductionList['TotalCount'];
        $Data['PageCount'] = $DailyProductionList['PageCount'];
        $Data['Totals'] = $DailyProductionList['Totals'];
        //ar_dump($Data);
        return $Data;
    }

    function getProductionlist($Query, $RequestBody)
    {

        $SearchKey = trim($RequestBody['MoreSearch']);
        $PageNo = !empty($RequestBody['PageNo'])?$RequestBody['PageNo']:1;
        $LoadAll = $RequestBody['LoadAll'];

        $Status = $RequestBody['Status'];


        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }
        
        if ($SearchKey != '') {
        $SearchQuery = " and CONCAT_WS('', SPV.DeviSupc, ProductDescription,ADID,`AvalSlabs`, CSD.`Grade`,
                        CSD.`FreezingType`, CSD.`VarietyID`, `Galze`, 
                        `SockingType`, `ProductionDate`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`,
                        CSD.`PackingStyleID`, CSD.`Remarks`, CSD.PlantID,
                        `StockStatus`,ShortName,
                        APS.PackingStyle,StoreName,RPS.StationName,
                        AvalKgs,ActualSlabs,CSD.`PoID`,CSD.`PoGradeID`,SPD.PoNo) LIKE '%$SearchKey%'";
        }
        $TotalCount = $PageCount = 0;
        if ($LoadAll != 1) {
            $sqlCount = "SELECT COUNT(*) as Total  FROM `AdAntiDumpingDetails` as CSD 
                        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
                        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
                        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
                        left join CsStores as CSS on CSS.StoreID=CSD.UnitID
                        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID $Query  $SearchQuery   order by ProductionDate Desc";

            $stmtCount = $this->conn->prepare($sqlCount);
            $stmtCount->execute();

            $rowCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
            
            $TotalCount = $rowCount['Total'];
            $PageCount = ceil($TotalCount / $RecordsPerPage);
        }
        //echo '<pre>';print_r($sqlCount);die;
        $sqlFields = "SPV.DeviSupc, ProductDescription,ADID,`AvalSlabs`, CSD.`Grade`,
         CSD.`FreezingType`, CSD.`VarietyID`, `Galze`, 
        `SockingType`, `ProductionDate`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`,
         CSD.`PackingStyleID`, CSD.`Remarks`, CSD.PlantID,
        `StockStatus`,ShortName as Variety,CSD.GroupType,
        APS.PackingStyle,StoreName,RPS.StationName,
        AvalKgs as Kgs,ActualSlabs,CSD.`PoID`,CSD.`PoGradeID`,SPD.PoNo,RPS1.StationName AS FromPlant,RPS2.StationName AS ToPlant";

        $sqlQuery = "SELECT $sqlFields  FROM `AdAntiDumpingDetails` as CSD 
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        left join CsStores as CSS on CSS.StoreID=CSD.UnitID
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID
        left join RmProcessingStations as RPS1 on RPS1.ProcessingStationID=CSD.FromPlantID
        left join RmProcessingStations as RPS2 on RPS2.ProcessingStationID=CSD.ToPlantID $Query $SearchQuery
        order by ProductionDate Desc " . $Limit;
        //echo '<pre>';print_r($sqlQuery);die;
         //echo $sqlQuery;exit;

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $i = $LimitMax;
        $TotalSlabs = 0;
        $GrandTotalWeight = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            // echo print_r($dataRow);
            // exit;

            $AvalSlabs = $dataRow['AvalSlabs'];
            $ActualSlabs = $dataRow['ActualSlabs'];
            $BalanceSlabs = $ActualSlabs - $AvalSlabs;

            $TotalWeight = round($dataRow['Kgs'], 3);
            $aryBalance = $this->getBalanceKgs($TotalWeight, $dataRow['ADID'], $dataRow);
            $BalanceSlabs = $aryBalance['BalanceSlabs'];
            $BalanceKgs = $aryBalance['BalanceWeight'];
            if ($BalanceKgs <= 0 && $BalanceSlabs <= 0 ) {
                $Status = 1;
            } else {
                if ($BalanceSlabs <= 0) {
                    $BalanceKgs = 0;
                    $Status = 1;
                } else {
                    $Status = 0;
                }
            }


            // $this->dailyInwardDetailsController->postDailyproductionStatus($dataRow['ProductionID'], $AvalMcs, $LooseBags, $dataRow['Galze']);
            $ArrayStockData['SNo'] = ++$i;
            $ArrayStockData['OurSupc'] = $dataRow['DeviSupc'];
            $ArrayStockData['ProductDescription'] = $dataRow['ProductDescription'];
            $ArrayStockData['ProductionID'] = $dataRow['ADID'];
            $ArrayStockData['TransactionDate'] = $dataRow['TransactionDate'];
            $ArrayStockData['ProductionDate'] = date('d-m-Y', strtotime($dataRow['ProductionDate']));
            $ArrayStockData['Group'] = $dataRow['ProductionTypeID'];
            $ArrayStockData['StoreID'] = $dataRow['UnitID'];
            $ArrayStockData['StoreName'] = $dataRow['StoreName'];
            $ArrayStockData['PlantID'] = $dataRow['PlantID'];
            $ArrayStockData['ProductionPlantName'] = $dataRow['StationName'];
            $ArrayStockData['Coldstore'] = $dataRow['UnitID'];
            // $ArrayStockData['FromPlant'] = $dataRow['FromPlant'];
            // $ArrayStockData['ToPlant'] = $dataRow['ToPlant'];
            $ArrayStockData['BalanceSlabs'] = $BalanceSlabs;

            if($dataRow['ProductionTypeID'] == 'IBT IN'){
                $ArrayStockData['FromPlant'] = $dataRow['FromPlant'];
                $ArrayStockData['ToPlant'] = $dataRow['StoreName'];
            } /* else{
                $ArrayStockData['FromPlant'] = '';
                $ArrayStockData['ToPlant'] = '';
            } */
            elseif($dataRow['ProductionTypeID'] == 'IBT Out'){
                $ArrayStockData['FromPlant'] = $dataRow['StoreName'];
                $ArrayStockData['ToPlant'] = $dataRow['ToPlant'];
            } else{
                $ArrayStockData['FromPlant'] = '';
                $ArrayStockData['ToPlant'] = '';
            }

            $ArrayStockData['Variety'] = $dataRow['Variety'];
            $ArrayStockData['VarietyID'] = $dataRow['VarietyID'];
            $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStockData['PackingStyleID'] = $dataRow['PackingStyleID'];
            $ArrayStockData['Grade'] = $dataRow['Grade'];
            $ArrayStockData['FreezingType'] = $dataRow['FreezingType'];
            $ArrayStockData['Galze'] = $dataRow['Galze'];
            $ArrayStockData['GlazePerc'] = $dataRow['GlazePerc'];
            $ArrayStockData['Soaking'] = $dataRow['SockingType'];
            $ArrayStockData['PoID'] = $dataRow['PoID'];
            $ArrayStockData['PoGradeID'] = $dataRow['PoGradeID'];
            $ArrayStockData['PoNo'] = $dataRow['PoNo'];
            $ArrayStockData['GroupType'] = $dataRow['GroupType'];
            // $ArrayStockData['Salt'] = $dataRow['Salt'];
            $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
            $ArrayStockData['Remarks'] = $dataRow['Remarks'];
            $ArrayStockData['AvalSlabs'] = $dataRow['AvalSlabs'];
            $TotalSlabs += $dataRow['AvalSlabs'];
            $ArrayStockData['BalanceSlabs'] = $BalanceSlabs;
            // $ArrayStockData['Kgs'] = round($TotalWeight,3);
            // $ArrayStockData['BalanceKgs'] = round( $BalanceKgs ,3);
            $ArrayStockData['Kgs'] = sprintf('%.3f', $TotalWeight);
            $ArrayStockData['BalanceKgs'] = sprintf('%.3f', $BalanceKgs);
            $ArrayStockData['Status'] = $Status;
            $GrandTotalWeight += $TotalWeight;


            $AryData[] = $ArrayStockData;
        }
        if (count($AryData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['ArrayData'] = $AryData;
        $Data['TotalCount'] = $TotalCount;
        $Data['TotalSlabs'] = $TotalSlabs;
        $Data['PageCount'] = $PageCount;
        $Data['RecordsAval'] = $RecordsAval;
        $Data['Totals'] = ['TotalSlabs' => $TotalSlabs,'GrandTotalWeight' => sprintf('%.3f',$GrandTotalWeight)];
        return $Data;
    }

    public function getBalanceKgs($TotalWeight, $ProductionID, $proddataRow)
    {
        $sqlFields = "sum((ActualMcs*TotalSlabs*SlabWeight*MFactor)) as Kgs,
        sum((ActualLooseBags*SlabWeight*MFactor)) as LKgs, 
        sum(ActualMcs*TotalSlabs) as ActualMcs,
        sum(ActualLooseBags) as ActualLooseBags";

        $sqlQuery = "SELECT $sqlFields  
         FROM `CsStockData` as CSD 
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID where ProductionID='$ProductionID' ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $ProductionTotalWeight = ($dataRow['Kgs'] + $dataRow['LKgs']);

        if ($proddataRow['Galze'] > 0) {
            $GlazeWeight = ($ProductionTotalWeight * $proddataRow['Galze']) / 100;
            $ProductionTotalWeight = $ProductionTotalWeight - $GlazeWeight;
        }

        $TotalWeight = round($TotalWeight - $ProductionTotalWeight, 3);
        $ArrayBalance['BalanceSlabs'] = $proddataRow['ActualSlabs'] - ($dataRow['ActualMcs'] + $dataRow['ActualLooseBags']);
        $ArrayBalance['BalanceWeight'] = $TotalWeight;

        return $ArrayBalance;
    }

    
    
    public function getPackingStyleDetails($RequestBody)
    {

        $PackingStyle = $RequestBody['PackingStyle'];


        $Query = '';

        if ($PackingStyle != '') {
            $Query .= "WHERE PCKID=" . $PackingStyle;
        }


        $sqlPacking = "SELECT TotalSlabs,SlabWeight,MFactor,PackingStyle FROM AdminPackingStyles $Query";
        $stmtPacking = $this->conn->prepare($sqlPacking);
        $stmtPacking->execute();
        $PackingStyleData = $stmtPacking->fetch(PDO::FETCH_ASSOC);




        // $ProdID = $this->postDailyProductionDetails($RequestBody)['NewAddedProdId'];

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['Data'] = $PackingStyleData;
        //ar_dump($Data);
        return $Data;
    }
}
