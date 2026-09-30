<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';

class AntiDumpingNegativeReportController
{
    private $conn;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllGrades($RequestBody)
    {
        $PoID = $RequestBody['PoID'];
        $VarietyID = $RequestBody['VarietyID'];

        $Query = '';

        if ($PoID != '') {
            $Query .= " WHERE PoID='$PoID'";
        }

        $sqlQuery = "SELECT * FROM ScrmPIVarietyList where VarietyID='$VarietyID'";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $rowVariety = $stmt->fetch(PDO::FETCH_ASSOC);

        $result = [];
        if (!empty($rowVariety)) {
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
        $Variety = $RequestBody['Variety'];

        // print_r("Status". $Status);
        // print_r("StProductionDateatus". $ProductionDate);
        $Query = '';
        if ($UnitID != '') {
            $Query .= " AND CSD.PlantID='$UnitID'";
        }
        if ($ProductionEndDate != '') {
            $Query .= " AND CSD.StockDate BETWEEN '$ProductionDate' AND '$ProductionEndDate' ";
        } else if ($ProductionDate != '') {
            $Query .= " AND CSD.StockDate='$ProductionDate'";
        }

        if ($Variety != '') {
            $Query .= " AND CSD.VarietyID='$Variety'";
        }


        $DailyProductionList = $this->getProductionlist($Query, $RequestBody);



        // $ProdID = $this->postDailyProductionDetails($RequestBody)['NewAddedProdId'];

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['DailyProductionList'] = $DailyProductionList['ArrayData'];
        $Data['TotalCount'] = $DailyProductionList['TotalCount'];
        $Data['PageCount'] = $DailyProductionList['PageCount'];
        $Data['sqlNegativeData'] = $DailyProductionList['sqlNegativeData'];
        //ar_dump($Data);
        return $Data;
    }

    function getProductionlist($Query, $RequestBody)
    {

        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Status = $RequestBody['Status'];


        $RecordsPerPage = 100;
        $LimitMax = ($PageNo) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $SearchQuery = " and CONCAT_WS('', SPV.DeviSupc, ProductDescription,ADID,`AvalSlabs`, CSD.`Grade`,
         CSD.`FreezingType`, CSD.`VarietyID`, `Galze`, 
        `SockingType`, `ProductionDate`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`,
         CSD.`PackingStyleID`, CSD.`Remarks`, CSD.PlantID,
        `StockStatus`,ShortName as Variety,
        APS.PackingStyle,StoreName,RPS.StationName,
        AvalKgs as Kgs,ActualSlabs,CSD.`PoID`,CSD.`PoGradeID`,SPD.PoNo) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(*) as Total  FROM `AdVarietyWiseOpeningStock` as CSD WHERE (CSD.OpeningStock < 0 OR CSD.ClosingStock < 0)
         $Query order by StockDate Desc";

        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();

        $rowCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $rowCount['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlNegativeData = "SELECT CSD.*,SPV.DeviSupc, ProductDescription,ShortName as Variety, SPV.`FreezingType`, `Glaze`, `Grade`,
            APS.PackingStyle,StationName,Treatment FROM `AdVarietyWiseOpeningStock` as CSD
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=SPV.PackingStyle
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID
        WHERE (CSD.OpeningStock < 0 OR CSD.ClosingStock < 0)
        $Query order by StockDate Desc ";

        $stmtNegativeData = $this->conn->prepare($sqlNegativeData);
        $stmtNegativeData->execute();


        $ArrayData = [];
        while ($rowNegativeData = $stmtNegativeData->fetch(PDO::FETCH_ASSOC)) {

            $negativeQuery = " WHERE CSD.ProductionDate='" . $rowNegativeData['StockDate'] . "' AND CSD.VarietyID='" . $rowNegativeData['VarietyID'] . "' AND CSD.PlantID='" . $rowNegativeData['PlantID'] . "'";

            $sqlFields = "ADID,`AvalSlabs`, `ProductionTypeID`, `ProductCategoryID`, `UnitID`,CSD.`Remarks`, 
            CSD.PlantID,`StockStatus`,CSD.GroupType,StoreName,AvalKgs as Kgs,ActualSlabs,CSD.`PoID`,CSD.`PoGradeID`,
            SPD.PoNo,RPS1.StationName AS FromPlant,RPS2.StationName AS ToPlant";

            $sqlQuery = "SELECT $sqlFields  FROM `AdAntiDumpingDetails` as CSD 
            left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
            left join CsStores as CSS on CSS.StoreID=CSD.UnitID
            left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID
            left join RmProcessingStations as RPS1 on RPS1.ProcessingStationID=CSD.FromPlantID
            left join RmProcessingStations as RPS2 on RPS2.ProcessingStationID=CSD.ToPlantID
            $negativeQuery
            order by ProductionDate Desc ";

            // echo $sqlQuery;exit;

            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();


            $rowCount = $stmt->rowCount();
            if ($rowCount > 0) {

                $i = $LimitMax;
                $TotalSlabs = 0;
                $BreakUpData = [];
                while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

                    // echo print_r($dataRow);
                    // exit;

                    $AvalSlabs = $dataRow['AvalSlabs'];
                    $ActualSlabs = $dataRow['ActualSlabs'];
                    $BalanceSlabs = $ActualSlabs - $AvalSlabs;

                    $TotalWeight = round($dataRow['Kgs'], 3);

                    // $this->dailyInwardDetailsController->postDailyproductionStatus($dataRow['ProductionID'], $AvalMcs, $LooseBags, $dataRow['Galze']);
                    $ArrayStockData['SNo'] = ++$i;
                    $ArrayStockData['ProductionID'] = $dataRow['ADID'];
                    $ArrayStockData['Group'] = $dataRow['ProductionTypeID'];
                    $ArrayStockData['StoreID'] = $dataRow['UnitID'];
                    $ArrayStockData['StoreName'] = $dataRow['StoreName'];
                    $ArrayStockData['PlantID'] = $dataRow['PlantID'];
                    $ArrayStockData['ProductionPlantName'] = $dataRow['StationName'];
                    $ArrayStockData['Coldstore'] = $dataRow['UnitID'];
                    // $ArrayStockData['FromPlant'] = $dataRow['FromPlant'];
                    // $ArrayStockData['ToPlant'] = $dataRow['ToPlant'];
                    $ArrayStockData['BalanceSlabs'] = $BalanceSlabs;

                    if ($dataRow['ProductionTypeID'] == 'IBT IN') {
                        $ArrayStockData['FromPlant'] = $dataRow['FromPlant'];
                        $ArrayStockData['ToPlant'] = $dataRow['StoreName'];
                    } else {
                        $ArrayStockData['FromPlant'] = '';
                        $ArrayStockData['ToPlant'] = '';
                    }
                    if ($dataRow['ProductionTypeID'] == 'IBT Out') {
                        $ArrayStockData['FromPlant'] = $dataRow['StoreName'];
                        $ArrayStockData['ToPlant'] = $dataRow['ToPlant'];
                    } else {
                        $ArrayStockData['FromPlant'] = '';
                        $ArrayStockData['ToPlant'] = '';
                    }

                    $ArrayStockData['PoID'] = $dataRow['PoID'];
                    $ArrayStockData['PoGradeID'] = $dataRow['PoGradeID'];
                    $ArrayStockData['PoNo'] = $dataRow['PoNo'];
                    $ArrayStockData['GroupType'] = $dataRow['GroupType'];
                    // $ArrayStockData['Salt'] = $dataRow['Salt'];
                    $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
                    $ArrayStockData['Remarks'] = $dataRow['Remarks'];
                    $ArrayStockData['AvalSlabs'] = $dataRow['AvalSlabs'];
                    $TotalSlabs += $dataRow['AvalSlabs'];
                    // $ArrayStockData['Kgs'] = round($TotalWeight,3);
                    // $ArrayStockData['BalanceKgs'] = round( $BalanceKgs ,3);
                    $ArrayStockData['Kgs'] = sprintf('%.3f', $TotalWeight);

                    $BreakUpData[] = $ArrayStockData;
                }
                // echo print_r($BreakUpData);exit;
                $rowNegativeData['BreakUpData'] = $BreakUpData;
                $ArrayData[] = $rowNegativeData;
            }
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['ArrayData'] = $ArrayData;
        $Data['TotalCount'] = $TotalCount;
        $Data['PageCount'] = $PageCount;
        $Data['sqlNegativeData'] = $sqlNegativeData;
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
