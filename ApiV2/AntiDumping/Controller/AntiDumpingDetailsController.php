<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';

class AntiDumpingDetailsController
{
    private $conn;
    //private $db_table = "ScrmApplicants";
    private $dailyProductionDetailsController;
    private $dailyInwardDetailsController;
    private $stockOutController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // FIX: make sure PDO throws exceptions instead of silently failing.
        // (Best done where the connection is originally created, but set here
        // too as a safety net so failures are never swallowed silently.)
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * FIX: small helper so every string value going into a raw SQL string is
     * safely quoted/escaped instead of being concatenated as-is. This does not
     * fully replace parameter binding (a much bigger refactor), but it closes
     * the immediate hole that was silently breaking UPDATE/INSERT statements
     * whenever a value (e.g. Remarks) contained a single quote.
     */
    private function q($value)
    {
        if ($value === null) {
            return 'NULL';
        }
        return $this->conn->quote((string) $value);
    }

    public function getPoTotal($RequestBody)
    {
        $PoID = $RequestBody['PoID'];
        $UnitID = $RequestBody['ColdstoreID'];
        $ProductionDate = $RequestBody['ProductionDate'];
        $VarietyID = $RequestBody['VarietyID'];

        $Query = '';
        $openingStockData = [];

        if ($VarietyID != '') {
            // $sqlQuery = "SELECT ShortName FROM ScrmPIVarietyList where VarietyID=$VarietyID";

            // $stmt = $this->conn->prepare($sqlQuery);
            // $stmt->execute();
            // $rowVariety = $stmt->fetch(PDO::FETCH_ASSOC);
            // $Variety = $rowVariety['ShortName'];
            // $Query .= " AND SPVL.ShortName='$Variety'";
            $Query .= " AND CAD.VarietyID=" . (int) $VarietyID;
            $Query .= " AND ProductionDate=" . $this->q($ProductionDate);

            $sqlOpeningQuery = "SELECT * FROM `AdVarietyWiseOpeningStock` WHERE  VarietyID=" . (int) $VarietyID . " AND StockDate=" . $this->q($ProductionDate) . " AND `PlantID`=" . $this->q($UnitID);
            // echo $sqlOpeningQuery;exit;
            $stmtOpening = $this->conn->prepare($sqlOpeningQuery);
            $stmtOpening->execute();
            $rowOpening = $stmtOpening->fetch(PDO::FETCH_ASSOC);
            if ($rowOpening) {
                $openingStockData['OpeningStock'] = $rowOpening['OpeningStock'];
                $openingStockData['ClosingStock'] = $rowOpening['ClosingStock'];
            } else {
                $sqlOpeningQuery = "SELECT * FROM `AdVarietyWiseOpeningStock` WHERE  VarietyID=" . (int) $VarietyID . " AND StockDate<" . $this->q($ProductionDate) . " AND `PlantID`=" . $this->q($UnitID) . " ORDER BY StockDate DESC LIMIT 1";

                $stmtOpening = $this->conn->prepare($sqlOpeningQuery);
                $stmtOpening->execute();
                $rowOpening = $stmtOpening->fetch(PDO::FETCH_ASSOC);

                if ($rowOpening) {
                    $openingStockData['OpeningStock'] = $rowOpening['ClosingStock'];
                    $openingStockData['ClosingStock'] = $rowOpening['ClosingStock'];
                }

            }
        } else if ($PoID != '') {
            $Query .= " AND PoID=" . $this->q($PoID);
        } else {
            $Query .= " AND ProductionDate=" . $this->q($ProductionDate);
        }

        if ($UnitID != '') {
            $Query .= " and PlantID=" . $this->q($UnitID);
        }

        $sqlQuery = "SELECT CAD.GroupType,sum(ActualSlabs) AS Total  FROM `AdAntiDumpingDetails` AS CAD LEFT JOIN ScrmPIVarietyList AS SPVL ON CAD.VarietyID=SPVL.VarietyID WHERE CAD.ProductionTypeID != 'Opening Stock' $Query GROUP BY CAD.GroupType";

        // echo $sqlQuery;exit;
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $Result = [];
        while ($rowSlabs = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($rowSlabs['GroupType'] == 1) {
                $Result['TotalInwardSlabs'] = $rowSlabs['Total'];
            } else if ($rowSlabs['GroupType'] == 2) {
                $Result['TotalOutwardSlabs'] = $rowSlabs['Total'];
            } else {
                $Result['TotalOtherSlabs'] = $rowSlabs['Total'];
            }
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['TotalSlabs'] = $Result;
        $Data['OpeningStock'] = $openingStockData;

        return $Data;
    }

    public function getAllGrades($RequestBody)
    {
        $PoID = $RequestBody['PoID'];
        $VarietyID = $RequestBody['VarietyID'];

        $Query = '';

        if ($PoID != '') {
            $Query .= " WHERE PoID=" . $this->q($PoID);
        }

        $sqlQuery = "SELECT * FROM ScrmPIVarietyList where VarietyID=" . (int) $VarietyID;

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
                                WHERE PackingStyle = " . $this->q($rowVariety['PackingStyle']) . "
                                    AND FreezingType = " . $this->q($rowVariety['FreezingType']) . "
                                    AND Treatment = " . $this->q($rowVariety['Treatment']) . "
                                    AND ShortName = " . $this->q($rowVariety['ShortName']) . "
                                    AND Species = " . $this->q($rowVariety['Species']) . "
                                    AND TypeProduct=" . $this->q($rowVariety['TypeProduct']) . "
                                    AND Glaze=" . $this->q($rowVariety['Glaze']) . "
                                    AND BrandName=" . $this->q($rowVariety['BrandName']) . "
                                    AND UltimateConsignee=" . $this->q($rowVariety['UltimateConsignee']) . "
                                    AND PreparationType=" . $this->q($rowVariety['PreparationType']) . "
                                    AND (
                                            ((" . $this->q($rowVariety['GlazePerc']) . " IS NULL OR " . $this->q($rowVariety['GlazePerc']) . " = 0)
                                                AND (GlazePerc IS NULL OR GlazePerc = 0))
                                        OR (GlazePerc = " . $this->q($rowVariety['GlazePerc']) . ")
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

        $Query = '';
        if ($ProductionDate != '') {
            $Query = "WHERE SCI.`StockOutDate`=" . $this->q($ProductionDate);
            if ($ProductionPlant != '') {
                $Query .= " AND RPS.ProcessingStationID=" . (int) $ProductionPlant . " AND AED.ExpID IS NOT NULL";
            }
        } else if ($ProductionPlant != '') {
            if ($Query != '') {
                $Query .= " AND RPS.ProcessingStationID=" . (int) $ProductionPlant . " AND AED.ExpID IS NOT NULL";
            } else {
                $Query .= " WHERE RPS.ProcessingStationID=" . (int) $ProductionPlant . " AND AED.ExpID IS NOT NULL";
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

    public function getDataWithoutBrand($RequestBody)
    {

        $LoadAll = $RequestBody['LoadAll'];
        $VarietyID = $RequestBody['VarietyID'];
        $ProductionDate = $RequestBody['ProductionDate'];
        $Status = $RequestBody['Status'];

        $varietyQuery = "SELECT * FROM `ScrmPIVarietyList` WHERE `VarietyID` = " . (int) $VarietyID;
        $StmtvarietyQuery = $this->conn->prepare($varietyQuery);
        $StmtvarietyQuery->execute();
        $DataVarietyQuery = $StmtvarietyQuery->fetch(PDO::FETCH_ASSOC);

        $getAllVarietyQuery = "SELECT GROUP_CONCAT(VarietyID) AS VarietyIds FROM `ScrmPIVarietyList` WHERE `TypeProduct` = " . $this->q($DataVarietyQuery['TypeProduct']) . "
            AND `ShortName` = " . $this->q($DataVarietyQuery['ShortName']) . " AND `Glaze` = " . $this->q($DataVarietyQuery['Glaze']) . " AND `Treatment` = " . $this->q($DataVarietyQuery['Treatment']) . "
             AND `Grade` = " . $this->q($DataVarietyQuery['Grade']) . " AND `FreezingType` = " . $this->q($DataVarietyQuery['FreezingType']) . " AND `Species` = " . $this->q($DataVarietyQuery['Species']) . "
              AND `PreparationType` = " . $this->q($DataVarietyQuery['PreparationType']) . " AND `PackingStyle` = " . $this->q($DataVarietyQuery['PackingStyle']);
        $stmtAllVarietyQuery = $this->conn->prepare($getAllVarietyQuery);
        $stmtAllVarietyQuery->execute();
        $dataAllVarietyQuery = $stmtAllVarietyQuery->fetch(PDO::FETCH_ASSOC);

        // FIX: GROUP_CONCAT can legitimately return NULL (no matching rows), which
        // previously produced "IN ()" -> SQL error. Guard against that.
        $varietyIdsList = isset($dataAllVarietyQuery['VarietyIds']) ? $dataAllVarietyQuery['VarietyIds'] : '';
        if ($varietyIdsList === '' || $varietyIdsList === null) {
            $varietyIdsList = '0';
        }

        $Query = " WHERE StockDate <= " . $this->q($ProductionDate) . " AND `VarietyID` IN (" . $varietyIdsList . ")";


        $sqlFields = "SPV.DeviSupc, ProductDescription, SPV.`Grade`,CSD.DataID,
            SPV.`FreezingType`, CSD.`VarietyID`, `Glaze`, `GlazePerc`,
            `Treatment`, `StockDate`,
            SPV.`PackingStyle` AS PackingStyleID, CSD.PlantID,ShortName as Variety,
            APS.PackingStyle,CSD.ClosingStock,CSD.OpeningStock";

        $sqlQuery = "SELECT $sqlFields  FROM `AdVarietyWiseOpeningStock` as CSD 
            INNER JOIN (
                    SELECT
                        VarietyID,
                        MAX(StockDate) AS MaxStockDate
                    FROM AdVarietyWiseOpeningStock
                    $Query
                    GROUP BY VarietyID
                ) Latest
                    ON CSD.VarietyID = Latest.VarietyID
                AND CSD.StockDate = Latest.MaxStockDate
            left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
            left join AdminPackingStyles as APS on APS.PCKID=SPV.PackingStyle
            order by StockDate Desc ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $TotalSlabs = 0;
        $TotalKgs = 0;
        $i = 0; // FIX: was undefined before increment ($i = ++$i), relied on PHP notices being suppressed
        $AryData = []; // FIX: initialise so an empty result set returns [] not undefined
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            // echo print_r($dataRow);
            // exit;


            // $this->dailyInwardDetailsController->postDailyproductionStatus($dataRow['ProductionID'], $AvalMcs, $LooseBags, $dataRow['Galze']);
            $ArrayStockData['SNo'] = ++$i;
            $ArrayStockData['OurSupc'] = $dataRow['DeviSupc'];
            $ArrayStockData['ProductDescription'] = $dataRow['ProductDescription'];
            $ArrayStockData['ProductionDate'] = date('d-m-Y', strtotime($dataRow['StockDate']));
            $ArrayStockData['PlantID'] = $dataRow['PlantID'];
            $ArrayStockData['DataID'] = $dataRow['DataID'];
            $ArrayStockData['Variety'] = $dataRow['Variety'];
            $ArrayStockData['VarietyID'] = $dataRow['VarietyID'];
            $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStockData['PackingStyleID'] = $dataRow['PackingStyleID'];
            $ArrayStockData['Grade'] = $dataRow['Grade'];
            $ArrayStockData['FreezingType'] = $dataRow['FreezingType'];
            $ArrayStockData['Galze'] = $dataRow['Glaze'];
            $ArrayStockData['GlazePerc'] = $dataRow['GlazePerc'];
            $ArrayStockData['Soaking'] = $dataRow['Treatment'];
            $ArrayStockData['ClosingStock'] = $dataRow['ClosingStock'];
            $ArrayStockData['OpeningStock'] = $dataRow['OpeningStock'];


            $AryData[] = $ArrayStockData;
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['DailyProductionList'] = $AryData;
        $Data['TotalSlabs'] = $TotalSlabs;
        $Data['TotalKgs'] = $TotalKgs;
        return $Data;


    }

    public function getDailyProductionDetails($RequestBody)
    {

        $ProductionDate = $RequestBody['ProductionDate'];
        $ProductionEndDate = $RequestBody['ProductionEndDate'];
        $UnitID = $RequestBody['ColdstoreID'];
        $ProductionTypeID = $RequestBody['Group'];
        $ProductionID = $RequestBody['ProductionID'];
        $Supc = $RequestBody['Supc'];
        $Status = $RequestBody['Status'];

        // print_r("Status". $Status);
        // print_r("StProductionDateatus". $ProductionDate);
        $Query = '';
        $DailyProductionData = []; // FIX: initialise so it's always defined in the response

        if ($Supc != '') {
            $Query = " WHERE SPV.DeviSUPC=" . $this->q($Supc) . " ";
        }

        if ($ProductionID != '') {
            $PQuery = " where ADID=" . $this->q($ProductionID);
        }

        if ($ProductionID != '') {
            $DailyProductionData = $this->getProductionDetails($PQuery);
            $ProductionDate = $DailyProductionData['ProductionDate'];
        }

        $ProductionDateFilter = '';

        if ($ProductionEndDate != '') {
            $ProductionDateFilter = " WHERE ProductionDate BETWEEN " . $this->q($ProductionDate) . " AND " . $this->q($ProductionEndDate) . " ";
        } else {
            $ProductionDateFilter = " WHERE ProductionDate=" . $this->q($ProductionDate);
        }

        if ($ProductionDate != '' && $Status == '') {
            $Query = $ProductionDateFilter;
        } else if ($ProductionDate != '' && $Status == 1) {

            $Query = "  $ProductionDateFilter and StockStatus=1 "; //uma 13-09-2024
        } else { //uma 13-09-2024
            if ($ProductionDate != '') {
                $Query = "  $ProductionDateFilter and StockStatus=0";
            } else {
                $Query = "  WHERE  StockStatus=0";
            }
        }

        if ($UnitID != '') {
            $Query .= " and CSD.PlantID=" . $this->q($UnitID);
        }
        if ($ProductionTypeID != '') {
            $Query .= " and ProductionTypeID=" . $this->q($ProductionTypeID);
        }


        $DailyProductionList = $this->getProductionlist($Query, $RequestBody);


        // NOTE: LAST_INSERT_ID() only reflects the last insert done on THIS
        // connection/session, not "the last row in the table". This is unrelated
        // to the reported saving bug but is worth flagging: if you need the max
        // ADID, use "SELECT MAX(ADID) AS LastID FROM AdAntiDumpingDetails" instead.
        $sqlLastest = "SELECT LAST_INSERT_ID() AS LastID FROM AdAntiDumpingDetails";
        $stmtLastestID = $this->conn->prepare($sqlLastest);
        $stmtLastestID->execute();
        $dataLastID = $stmtLastestID->fetch(PDO::FETCH_ASSOC);

        $LastID = $dataLastID['LastID'];


        // $ProdID = $this->postDailyProductionDetails($RequestBody)['NewAddedProdId'];

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['NewAddedProdId'] = $LastID;
        $Data['DailyProductionList'] = $DailyProductionList['ArrayData'];
        $Data['DailyProductionData'] = $DailyProductionData;
        $Data['TotalCount'] = $DailyProductionList['TotalCount'];
        $Data['TotalSlabs'] = $DailyProductionList['TotalSlabs'];
        $Data['TotalKgs'] = $DailyProductionList['TotalKgs'];
        $Data['PageCount'] = $DailyProductionList['PageCount'];
        //ar_dump($Data);
        return $Data;
    }

    function getProductionlist($Query, $RequestBody)
    {
        $SearchKey = isset($RequestBody['SearchKey']) ? $RequestBody['SearchKey'] : '';
        $PageNo    = isset($RequestBody['PageNo']) && $RequestBody['PageNo'] !== '' ? (int) $RequestBody['PageNo'] : 1;
        $LoadAll   = isset($RequestBody['LoadAll']) ? $RequestBody['LoadAll'] : '';
        $Status    = isset($RequestBody['Status']) ? $RequestBody['Status'] : '';

        if ($PageNo < 1) {
            $PageNo = 1;
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        $SearchQuery = ''; // FIX: initialise so it's never "undefined" when $SearchKey is empty
        if ($SearchKey != '') {
            $SearchQuery = " and CONCAT_WS('', SPV.DeviSupc, ProductDescription,ADID,`AvalSlabs`, CSD.`Grade`,
         CSD.`FreezingType`, CSD.`VarietyID`, `Galze`, 
        `SockingType`, `ProductionDate`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`,
         CSD.`PackingStyleID`, CSD.`Remarks`, CSD.PlantID,
        `StockStatus`,ShortName as Variety,
        APS.PackingStyle,StoreName,RPS.StationName,
        AvalKgs as Kgs,ActualSlabs,CSD.`PoID`,CSD.`PoGradeID`,SPD.PoNo) LIKE " . $this->q('%' . $SearchKey . '%');
        }

        $sqlCount = "SELECT COUNT(*) as Total  FROM `AdAntiDumpingDetails` as CSD 
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        left join CsStores as CSS on CSS.StoreID=CSD.UnitID
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID $Query order by ProductionDate Desc";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();

        $rowCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $rowCount['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "SPV.DeviSupc, ProductDescription,ADID,`AvalSlabs`, CSD.`Grade`,
         CSD.`FreezingType`, CSD.`VarietyID`, `Galze`, 
        `SockingType`, `ProductionDate`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`,
         CSD.`PackingStyleID`, CSD.`Remarks`, CSD.PlantID,
        `StockStatus`,ShortName as Variety,CSD.GroupType,CSD.LastUpdatedDate,
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

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $i = $LimitMax;
        $TotalSlabs = 0;
        $TotalKgs = 0;
        $AryData = []; // FIX: initialise so an empty result set returns [] not undefined
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
            if ($BalanceKgs <= 0 && $BalanceSlabs <= 0) {
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

            if ($dataRow['ProductionTypeID'] == 'IBT IN') {
                $ArrayStockData['FromPlant'] = $dataRow['FromPlant'];
                $ArrayStockData['ToPlant'] = $dataRow['StoreName'];
            } /* else {
           $ArrayStockData['FromPlant'] = '';
           $ArrayStockData['ToPlant'] = '';
       } */ elseif ($dataRow['ProductionTypeID'] == 'IBT Out') {
                $ArrayStockData['FromPlant'] = $dataRow['StoreName'];
                $ArrayStockData['ToPlant'] = $dataRow['ToPlant'];
            } else {
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
            $ArrayStockData['LastUpdatedDate'] = $dataRow['LastUpdatedDate'];
            // $ArrayStockData['Salt'] = $dataRow['Salt'];
            $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
            $ArrayStockData['Remarks'] = $dataRow['Remarks'];
            $ArrayStockData['AvalSlabs'] = $dataRow['AvalSlabs'];
            $TotalSlabs += $dataRow['AvalSlabs'];
            $ArrayStockData['BalanceSlabs'] = $BalanceSlabs;
            // $ArrayStockData['Kgs'] = round($TotalWeight,3);
            // $ArrayStockData['BalanceKgs'] = round( $BalanceKgs ,3);
            $ArrayStockData['Kgs'] = sprintf('%.3f', $TotalWeight);
            $TotalKgs += $ArrayStockData['Kgs'];
            $ArrayStockData['BalanceKgs'] = sprintf('%.3f', $BalanceKgs);
            $ArrayStockData['Status'] = $Status;


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
        $Data['TotalKgs'] = $TotalKgs;
        $Data['PageCount'] = $PageCount;
        $Data['RecordsAval'] = $RecordsAval;
        return $Data;
    }

    function getProductionDetails($Query)
    {
        $sqlFields = "ADID,`AvalSlabs`, CSD.`Grade`, CSD.`FreezingType`, CSD.`VarietyID`, `Galze`, 
        `SockingType`,`ProductionDate`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`, 
         CSD.`PackingStyleID`, CSD.`Remarks`, CSD.PlantID,
        `StockStatus`,ShortName as Variety,CSD.GroupType,CSD.LastUpdatedDate,
        APS.PackingStyle, APS1.PackingStyle as ActualPackingStyle,StoreName,RPS.StationName,
        ActualKgs AS Kgs,ActualSlabs,CSD.`PoID`,CSD.`PoGradeID`,SPD.PoNo,RPS1.StationName AS FromPlant,RPS2.StationName AS ToPlant";

        $sqlQuery = "SELECT $sqlFields  FROM `AdAntiDumpingDetails` as CSD
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        left join AdminPackingStyles as APS1 on APS1.PCKID=SPV.PackingStyle
        left join CsStores as CSS on CSS.StoreID=CSD.UnitID
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID
        left join RmProcessingStations as RPS1 on RPS1.ProcessingStationID=CSD.FromPlantID
        left join RmProcessingStations as RPS2 on RPS2.ProcessingStationID=CSD.ToPlantID $Query";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $ArrayStockData = [];

        // FIX: if no row was found for the given ADID, $dataRow is false and
        // every $dataRow[...] access below would previously throw/notice. Bail
        // out cleanly instead.
        if (!$dataRow) {
            $ArrayStockData['Error'] = 'Record not found';
            return $ArrayStockData;
        }

        // $AvalMcs = $dataRow['AvalMcs'];
        // $LooseBags = $dataRow['LooseBags'];
        // $ActualMcs = $dataRow['ActualMcs'];
        // $ActualLooseBags = $dataRow['ActualLooseBags'];

        $TotalWeight = round($dataRow['Kgs'], 3);
        $aryBalance = $this->getBalanceKgs($TotalWeight, $dataRow['ADID'], $dataRow);
        $BalanceSlabs = $aryBalance['BalanceSlabs'];
        $BalanceKgs = $aryBalance['BalanceWeight'];

        if ($BalanceKgs == 0) {
            $Status = 1;
        } else if ($BalanceKgs < 0) {
            $Status = 2;
        } else {
            $Status = 0;
        }


        $ArrayStockData['ProductionID'] = $dataRow['ADID'];
        $ArrayStockData['ProductionDate'] = $dataRow['ProductionDate'];
        $ArrayStockData['Group'] = $dataRow['ProductionTypeID'];
        $ArrayStockData['StoreID'] = $dataRow['UnitID'];
        $ArrayStockData['StoreName'] = $dataRow['StoreName'];
        $ArrayStockData['PlantID'] = $dataRow['PlantID'];
        $ArrayStockData['StationName'] = $dataRow['StationName'];
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

        $ArrayStockData['Variety'] = $dataRow['Variety'];
        $ArrayStockData['VarietyID'] = $dataRow['VarietyID'];
        $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
        $ArrayStockData['ActualPackingStyle'] = $dataRow['ActualPackingStyle'];
        $ArrayStockData['PackingStyleID'] = $dataRow['PackingStyleID'];
        $ArrayStockData['Grade'] = $dataRow['Grade'];
        $ArrayStockData['FreezingType'] = $dataRow['FreezingType'];
        $ArrayStockData['Galze'] = $dataRow['Galze'];
        $ArrayStockData['GlazePerc'] = $dataRow['Galze'];
        $ArrayStockData['Soaking'] = $dataRow['SockingType'];
        $ArrayStockData['PoID'] = $dataRow['PoID'];
        $ArrayStockData['PoGradeID'] = $dataRow['PoGradeID'];
        $ArrayStockData['PoNo'] = $dataRow['PoNo'];
        $ArrayStockData['GroupType'] = $dataRow['GroupType'];
        $ArrayStockData['LastUpdatedDate'] = $dataRow['LastUpdatedDate'];
        // $ArrayStockData['Salt'] = $dataRow['Salt'];
        $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
        $ArrayStockData['Remarks'] = $dataRow['Remarks'];
        $ArrayStockData['AvalSlabs'] = $dataRow['AvalSlabs'];
        $ArrayStockData['Kgs'] = sprintf('%.3f', $TotalWeight);
        $ArrayStockData['BalanceKgs'] = sprintf('%.3f', $BalanceKgs);
        $ArrayStockData['Status'] = $Status;

        return $ArrayStockData;
    }

    public function postShipmentOutDetails($RequestBody)
    {

        $ProductionDate = $RequestBody['ProductionDate'];
        $PlantID = $RequestBody['PlantID'];
        $UnitID = $RequestBody['UnitID'];

        $VarietyID = $RequestBody['VarietyID'];
        $AvalSlabs = $RequestBody['AvalSlabs'];


        $ProductionTypeID = $RequestBody['Group'];
        $GroupType = $RequestBody['GroupType'];
        $PoID = $RequestBody['PoID'];
        $PoGradeID = $RequestBody['PoGradeID'];
        $OpeningStock = isset($RequestBody['OpeningStock']) && $RequestBody['OpeningStock'] !== ''
            ? $RequestBody['OpeningStock']
            : 0;

        $ClosingStock = isset($RequestBody['ClosingStock']) && $RequestBody['ClosingStock'] !== ''
            ? $RequestBody['ClosingStock']
            : 0;
        $OldAvalSlabs = 0;

        $Data = ['Error' => null, 'Msg' => '']; // FIX: always return a well-formed array

        if ($VarietyID != '' && $VarietyID != '0' && $VarietyID != 0) {
            $SqlVariety = "SELECT Glaze,GlazePerc,PackingStyle,Grade,Treatment,FreezingType FROM `ScrmPIVarietyList` WHERE VarietyID=" . (int) $VarietyID;
            $stmtVariety = $this->conn->prepare($SqlVariety);
            $stmtVariety->execute();
            $RowVariety = $stmtVariety->fetch(PDO::FETCH_ASSOC);
            $GlazeType = $RowVariety['Glaze'];
            $Galze = $RowVariety['GlazePerc'];
            $PackingStyleID = $RowVariety['PackingStyle'];
            $Grade = $RowVariety['Grade'];
            $FreezingType = $RowVariety['FreezingType'];
            $SockingType = $RowVariety['Treatment'];

            $stmt = false;
            $ActualKgs = $this->getActualKgs($AvalSlabs, $PackingStyleID);

            if ($AvalSlabs > 0) {

                // FIX: wrap the multi-statement write (AdAntiDumpingDetails insert +
                // AdVarietyWiseOpeningStock insert/update) in a transaction so a
                // failure partway through cannot leave the two tables out of sync,
                // and so the caller finds out about failures instead of silently
                // continuing.
                $this->conn->beginTransaction();
                try {
                    $sqlLastUpdatedDate = "SELECT `ProductionDate`,ADID from AdAntiDumpingDetails where ProductionDate<" . $this->q($ProductionDate) . " AND VarietyID=" . (int) $VarietyID . " AND PlantID=" . $this->q($PlantID) . "  ORDER BY ProductionDate DESC LIMIT 1";
                    $stmtLastUpdatedDate = $this->conn->prepare($sqlLastUpdatedDate);
                    $stmtLastUpdatedDate->execute();
                    $rowLastUpdatedDate = $stmtLastUpdatedDate->fetch(PDO::FETCH_ASSOC);
                    if ($rowLastUpdatedDate) {
                        $LastUpdatedDate = $rowLastUpdatedDate['ProductionDate'];
                        $LastUpdatedId = $rowLastUpdatedDate['ADID'];
                    } else {
                        $LastUpdatedDate = $ProductionDate;
                        $LastUpdatedId = 0;
                    }
                    $AryCurrentDate = getCurrentDateTime();
                    $CreatedDateTime = $AryCurrentDate['TimeStamp'];

                    $sqlQuery = "INSERT INTO `AdAntiDumpingDetails`(`ActualSlabs`, `AvalSlabs`, 
                    `ActualKgs`,`AvalKgs`,
                  `Grade`, `FreezingType`,`PoID`,`PoGradeID`, `VarietyID`, `Galze`, `SockingType`,  
                 `ProductionDate`, `PostedDate`,`GroupType`,
                    `ProductionTypeID`, `ProductCategoryID`, `UnitID`,`PlantID`,`FromPlantID`,`ToPlantID`, 
                  `PackingStyleID`, `Remarks`,`LastUpdatedDate`,`LastUpdatedId`)
                  
                  values(" . $this->q($AvalSlabs) . "," . $this->q($AvalSlabs) . "," . $this->q($ActualKgs) . "," . $this->q($ActualKgs) . "," . $this->q($Grade) . ",
                  " . $this->q($FreezingType) . "," . $this->q($PoID) . "," . $this->q($PoGradeID) . "," . (int) $VarietyID . "," . $this->q($Galze) . "," . $this->q($SockingType) . "," . $this->q($ProductionDate) . "," . $this->q($CreatedDateTime) . "," . $this->q($GroupType) . ",
                  " . $this->q($ProductionTypeID) . ",'G1'," . $this->q($UnitID) . "," . $this->q($PlantID) . ",'0','0'," . $this->q($PackingStyleID) . ",''," . $this->q($LastUpdatedDate) . "," . (int) $LastUpdatedId . ")";

                    $stmt = $this->conn->prepare($sqlQuery);
                    // FIX: check execute() result instead of just truthiness of $stmt
                    if (!$stmt->execute()) {
                        $errorInfo = $stmt->errorInfo();
                        throw new RuntimeException('Failed to insert AdAntiDumpingDetails: ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'unknown error'));
                    }

                    $ProductionID = $this->conn->lastInsertId();

                    $sqlOpeningQuery = "SELECT * FROM `AdVarietyWiseOpeningStock` WHERE  VarietyID=" . (int) $VarietyID . " AND StockDate=" . $this->q($ProductionDate) . " AND `PlantID`=" . $this->q($PlantID) . " ORDER BY StockDate DESC";

                    $stmtOpening = $this->conn->prepare($sqlOpeningQuery);
                    $stmtOpening->execute();
                    $rowOpening = $stmtOpening->fetch(PDO::FETCH_ASSOC);
                    if ($rowOpening) {
                        $DataID = $rowOpening['DataID'];
                        $OpeningStock = $rowOpening['OpeningStock'];
                        $ClosingStock = $rowOpening['ClosingStock'];
                    } else {
                        $DataID = '';
                        $sqlOpeningQueryNew = "SELECT * FROM `AdVarietyWiseOpeningStock` WHERE  VarietyID=" . (int) $VarietyID . " AND StockDate<" . $this->q($ProductionDate) . " AND `PlantID`=" . $this->q($PlantID) . " ORDER BY StockDate DESC LIMIT 1";

                        $stmtOpeningNew = $this->conn->prepare($sqlOpeningQueryNew);
                        $stmtOpeningNew->execute();
                        $rowOpeningNew = $stmtOpeningNew->fetch(PDO::FETCH_ASSOC);

                        if ($rowOpeningNew) {
                            $OpeningStock = $rowOpeningNew['ClosingStock'];
                            $ClosingStock = $rowOpeningNew['ClosingStock'];
                        } else {
                            $ClosingStock = $OpeningStock;
                        }
                    }
                    $updateQuery = "";
                    if ($GroupType == 1) {
                        if ($ProductionTypeID == "Opening Stock") {
                            $OpeningStock = (double) $AvalSlabs;
                            $updateQuery = ",`OpeningStock`=" . $this->q($OpeningStock);
                        } else {
                            $updateQuery = "";
                        }
                        $ClosingStock = (double) $ClosingStock + (double) $AvalSlabs - (double) $OldAvalSlabs;
                    } else if ($GroupType == 2) {
                        $ClosingStock = (double) $ClosingStock - (double) $AvalSlabs + (double) $OldAvalSlabs;
                    }

                    if ($DataID != '') {
                        $sqlUpdateVariety = "UPDATE `AdVarietyWiseOpeningStock` SET `ClosingStock`=" . $this->q($ClosingStock) . " $updateQuery WHERE `DataID`=" . $this->q($DataID);
                    } else {
                        $sqlUpdateVariety = "INSERT INTO `AdVarietyWiseOpeningStock`(`StockDate`, `VarietyID`, `OpeningStock`, `ClosingStock`,`PlantID`,`UnitID`) VALUES (" . $this->q($ProductionDate) . "," . (int) $VarietyID . "," . $this->q($OpeningStock) . "," . $this->q($ClosingStock) . "," . $this->q($PlantID) . "," . $this->q($UnitID) . ")";
                    }

                    $stmtUpdateVariety = $this->conn->prepare($sqlUpdateVariety);
                    // FIX: check execute() result here too
                    if (!$stmtUpdateVariety->execute()) {
                        $errorInfo = $stmtUpdateVariety->errorInfo();
                        throw new RuntimeException('Failed to update AdVarietyWiseOpeningStock: ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'unknown error'));
                    }

                    $this->updateOpeningAndClosing($ProductionID, 0);

                    $this->conn->commit(); // FIX: only commit once every step succeeded

                    $Data['ClosingStock'] = $ClosingStock;
                    $Data['OpeningStock'] = $OpeningStock;
                    $Data['NewAddedProdId'] = $ProductionID;
                    $Data['Error'] = null;
                    $Data['Msg'] = 'Posted Successfully';
                } catch (Exception $e) {
                    // FIX: roll back everything from this call if any step failed,
                    // and surface the error instead of silently pretending to succeed.
                    $this->conn->rollBack();
                    $Data['Error'] = $e->getMessage();
                    $Data['Msg'] = 'Unable to save';
                }

                // $Data = $this->getDailyProductionDetails($RequestBody);
            } else {
                $Data['ClosingStock'] = 0;
                $Data['OpeningStock'] = 0;
            }

            return $Data;

        }

        return $Data; // FIX: previously returned NULL/undefined when VarietyID was empty
    }

    public function postDailyProductionDetails($RequestBody)
    {


        $PostedUserID = 1;

        $ProductionID = $RequestBody['ProductionID'];
        $TransactionDate = $RequestBody['TransactionDate'];
        $ProductionDate = $RequestBody['ProductionDate'];
        $ProductionTypeID = $RequestBody['Group'];
        $UnitID = $RequestBody['ColdstoreID'];
        $PlantID = $RequestBody['PlantID'];
        $VarietyID = $RequestBody['Variety'];
        if ($VarietyID == '') {
            $Data['Error'] = 'Unable to save';
            $Data['Msg'] = 'Unable to save';
            return $Data;
        }
        $PackingStyleID = $RequestBody['PackingStyle'];
        $Grade = $RequestBody['Grade'];
        $FreezingType = $RequestBody['FreezingType'];
        $Galze = $RequestBody['Glaze'];
        if ($Galze == '') {
            $Galze = 0;
            $RequestBody['Glaze'] = 0;
        }
        $PoID = $RequestBody['PoID'];
        if ($PoID == '') {
            $PoID = 0;
            $RequestBody['PoID'] = 0;
        }
        $PoGradeID = $RequestBody['PoGradeID'];
        if ($PoGradeID == '') {
            $PoGradeID = 0;
            $RequestBody['PoGradeID'] = 0;
        }
        $SockingType = $RequestBody['Soaking'];
        $ProductCategoryID = $RequestBody['ProductCategory'];
        $Remarks = $RequestBody['Remarks'];
        $GroupType = $RequestBody['GroupType'];
        $AvalSlabs = $RequestBody['Slabs'];
        $AryCurrentDate = getCurrentDateTime();
        $CreatedDateTime = $AryCurrentDate['TimeStamp'];
        $FromPlant = $RequestBody['FromPlant'];
        if ($FromPlant == '') {
            $FromPlant = 0;
            $RequestBody['FromPlant'] = 0;
        }
        $ToPlant = $RequestBody['ToPlant'];
        if ($ToPlant == '') {
            $ToPlant = 0;
            $RequestBody['ToPlant'] = 0;
        }

        // $OpeningStock = $RequestBody['OpeningStock'];
        // $ClosingStock = $RequestBody['ClosingStock'];

        $OpeningStock = isset($RequestBody['OpeningStock']) && $RequestBody['OpeningStock'] !== ''
            ? $RequestBody['OpeningStock']
            : 0;

        $ClosingStock = isset($RequestBody['ClosingStock']) && $RequestBody['ClosingStock'] !== ''
            ? $RequestBody['ClosingStock']
            : 0;


        $Group = $RequestBody['Group'];
        if ($Group === "Shipment Out") {
            $jsonData = $RequestBody['jsonData'];
        } else {
            $jsonData = "";
        }

        $SumMcs = $RequestBody['Slabs'];

        if ($jsonData != "") {

            $JsonArr = json_decode($RequestBody['jsonData'], true);

            foreach ($JsonArr as $row) {

                $VarietyID = $row['varietyId'];
                $AvalSlabs = $row['slabsValue'];
                $DupRequestBody = $RequestBody;
                $DupRequestBody['VarietyID'] = $VarietyID;
                $DupRequestBody['AvalSlabs'] = $AvalSlabs;
                $DupRequestBody['GroupType'] = '2';
                $DupRequestBody['Group'] = 'Repacking Out';
                $DupRequestBody['OpeningStock'] = $row['OpeningStock'];
                $DupRequestBody['ClosingStock'] = $row['ClosingStock'];
                $outWardData = $this->postShipmentOutDetails($DupRequestBody);
                // FIX: bail out early if the outward leg failed instead of silently
                // continuing to post the inward leg with inconsistent stock data.
                if (!empty($outWardData['Error'])) {
                    $Data['Error'] = $outWardData['Error'];
                    $Data['Msg'] = 'Unable to save (outward leg failed)';
                    return $Data;
                }

                $DupRequestBody = $RequestBody;
                $DupRequestBody['AvalSlabs'] = $AvalSlabs;
                $DupRequestBody['GroupType'] = '1';
                $DupRequestBody['Group'] = 'Repacking In';
                $SumMcs += $AvalSlabs;
                $inwardData = $this->postShipmentOutDetails($DupRequestBody);
                // FIX: same check for the inward leg
                if (!empty($inwardData['Error'])) {
                    $Data['Error'] = $inwardData['Error'];
                    $Data['Msg'] = 'Unable to save (inward leg failed)';
                    return $Data;
                }

                $RequestBody['OpeningStock'] = $inwardData['OpeningStock'];
                $RequestBody['ClosingStock'] = $inwardData['ClosingStock'];
            }

            $AvalSlabs = $SumMcs;


        }

        if ($GroupType == 2) {
            $sqlLastUpdatedDate = "SELECT `StockDate`,OpeningStock,ClosingStock from AdVarietyWiseOpeningStock where StockDate<=" . $this->q($ProductionDate) . " AND VarietyID=" . (int) $VarietyID . " AND PlantID=" . $this->q($PlantID) . "  ORDER BY StockDate DESC LIMIT 1";
            $stmtLastUpdatedDate = $this->conn->prepare($sqlLastUpdatedDate);
            $stmtLastUpdatedDate->execute();
            $rowLastUpdatedDate = $stmtLastUpdatedDate->fetch(PDO::FETCH_ASSOC);
            $OpeningStockRemove = isset($rowLastUpdatedDate['OpeningStock']) ? $rowLastUpdatedDate['OpeningStock'] : 0;
            $ClosingStockRemove = isset($rowLastUpdatedDate['ClosingStock']) ? $rowLastUpdatedDate['ClosingStock'] : 0;
            if ($ProductionID != '') {
                $SqlStockData = "SELECT * from AdAntiDumpingDetails where ADID =" . $this->q($ProductionID);
                $stmtStockData = $this->conn->prepare($SqlStockData);
                $stmtStockData->execute();
                $RowStockData = $stmtStockData->fetch(PDO::FETCH_ASSOC);
                $OldAvalSlabs = $RowStockData['ActualSlabs'];
                $OldGroupType = $RowStockData['GroupType'];
                if ($OldGroupType == 1) {
                    $ClosingStockRemove = $ClosingStockRemove - $OldAvalSlabs;
                } else if ($OldGroupType == 2) {
                    $ClosingStockRemove = $ClosingStockRemove + $OldAvalSlabs;
                }
            }
            if ($ClosingStockRemove < $AvalSlabs) {
                $Data['Error'] = 'Entered Slabs is more than the Available Slabs';
                $Data['Msg'] = 'Entered Slabs is more than the Available Slabs';
                return $Data;
            }
        }

        $OldAvalSlabs = 0;

        // if ($PoGradeID != 0) {
        //     $SqlVarietyId = "SELECT VarietyID FROM `ScrmPoGradeData` WHERE ID='$PoGradeID'";
        //     $stmtVarietyId = $this->conn->prepare($SqlVarietyId);
        //     $stmtVarietyId->execute();
        //     $RowVarietyId = $stmtVarietyId->fetch(PDO::FETCH_ASSOC);
        //     $VarietyID = $RowVarietyId['VarietyID'];
        // }

        $GlazeType = '';
        if ($VarietyID != '' && $VarietyID != '0' && $VarietyID != 0) {
            $SqlVariety = "SELECT Glaze FROM `ScrmPIVarietyList` WHERE VarietyID=" . (int) $VarietyID;
            $stmtVariety = $this->conn->prepare($SqlVariety);
            $stmtVariety->execute();
            $RowVariety = $stmtVariety->fetch(PDO::FETCH_ASSOC);
            $GlazeType = $RowVariety['Glaze'];
        }

        $stmt = false;
        $ActualKgs = $this->getActualKgs($AvalSlabs, $PackingStyleID);

        // FIX: wrap the whole insert/update path (AdAntiDumpingDetails write +
        // all related AdVarietyWiseOpeningStock writes) in a single transaction,
        // and actually check every execute() result. This is the core fix for
        // "AdVarietyWiseOpeningStock is correct but AdAntiDumpingDetails is not
        // saving": previously `if ($stmt)` after `$stmt->execute()` only checked
        // that prepare() succeeded (always true), not that the write succeeded,
        // so a failed UPDATE/INSERT on AdAntiDumpingDetails (e.g. because a
        // Remarks value contained an unescaped quote) was silently ignored while
        // the opening/closing stock logic kept running and reporting success.
        $this->conn->beginTransaction();
        try {

            if ($ProductionID == '') {
                $OldAvalSlabs = 0;
                if ($AvalSlabs > 0) {

                    $sqlLastUpdatedDate = "SELECT `ProductionDate`,ADID from AdAntiDumpingDetails where ProductionDate<" . $this->q($ProductionDate) . " AND VarietyID=" . (int) $VarietyID . " AND PlantID=" . $this->q($PlantID) . "  ORDER BY ProductionDate DESC LIMIT 1";
                    $stmtLastUpdatedDate = $this->conn->prepare($sqlLastUpdatedDate);
                    $stmtLastUpdatedDate->execute();
                    $rowLastUpdatedDate = $stmtLastUpdatedDate->fetch(PDO::FETCH_ASSOC);
                    if ($rowLastUpdatedDate) {
                        $LastUpdatedDate = $rowLastUpdatedDate['ProductionDate'];
                        $LastUpdatedId = $rowLastUpdatedDate['ADID'];
                    } else {
                        $LastUpdatedDate = $ProductionDate;
                        $LastUpdatedId = 0;
                    }

                    $sqlQuery = "INSERT INTO `AdAntiDumpingDetails`(`ActualSlabs`, `AvalSlabs`, 
                    `ActualKgs`,`AvalKgs`,
                  `Grade`, `FreezingType`,`PoID`,`PoGradeID`, `VarietyID`, `Galze`, `SockingType`,  
                 `ProductionDate`, `PostedDate`,`GroupType`,
                    `ProductionTypeID`, `ProductCategoryID`, `UnitID`,`PlantID`,`FromPlantID`,`ToPlantID`, 
                  `PackingStyleID`, `Remarks`,`LastUpdatedDate`,`LastUpdatedId`)
                  
                  values(" . $this->q($AvalSlabs) . "," . $this->q($AvalSlabs) . "," . $this->q($ActualKgs) . "," . $this->q($ActualKgs) . "," . $this->q($Grade) . ",
                  " . $this->q($FreezingType) . "," . $this->q($PoID) . "," . $this->q($PoGradeID) . "," . (int) $VarietyID . "," . $this->q($Galze) . "," . $this->q($SockingType) . "," . $this->q($ProductionDate) . "," . $this->q($CreatedDateTime) . "," . $this->q($GroupType) . ",
                  " . $this->q($ProductionTypeID) . "," . $this->q($ProductCategoryID) . "," . $this->q($UnitID) . "," . $this->q($PlantID) . "," . $this->q($FromPlant) . "," . $this->q($ToPlant) . "," . $this->q($PackingStyleID) . "," . $this->q($Remarks) . "," . $this->q($LastUpdatedDate) . "," . (int) $LastUpdatedId . ")";

                    $stmt = $this->conn->prepare($sqlQuery);
                    // FIX: this is the exact bug reported — execute()'s return value
                    // was previously discarded and `if ($stmt)` (always truthy) was
                    // checked instead. Now a failed insert throws and rolls back.
                    if (!$stmt->execute()) {
                        $errorInfo = $stmt->errorInfo();
                        throw new RuntimeException('Failed to insert AdAntiDumpingDetails: ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'unknown error'));
                    }

                    $ProductionID = $this->conn->lastInsertId();

                    $sqlOpeningQuery = "SELECT * FROM `AdVarietyWiseOpeningStock` WHERE  VarietyID=" . (int) $VarietyID . " AND StockDate=" . $this->q($ProductionDate) . " AND `PlantID`=" . $this->q($PlantID) . " ORDER BY StockDate DESC";

                    $stmtOpening = $this->conn->prepare($sqlOpeningQuery);
                    $stmtOpening->execute();
                    $rowOpening = $stmtOpening->fetch(PDO::FETCH_ASSOC);
                    if ($rowOpening) {
                        $DataID = $rowOpening['DataID'];
                        $OpeningStock = $rowOpening['OpeningStock'];
                        $ClosingStock = $rowOpening['ClosingStock'];
                    } else {
                        $DataID = '';
                        $sqlOpeningQueryNew = "SELECT * FROM `AdVarietyWiseOpeningStock` WHERE  VarietyID=" . (int) $VarietyID . " AND StockDate<" . $this->q($ProductionDate) . " AND `PlantID`=" . $this->q($PlantID) . " ORDER BY StockDate DESC LIMIT 1";

                        $stmtOpeningNew = $this->conn->prepare($sqlOpeningQueryNew);
                        $stmtOpeningNew->execute();
                        $rowOpeningNew = $stmtOpeningNew->fetch(PDO::FETCH_ASSOC);

                        if ($rowOpeningNew) {
                            $OpeningStock = $rowOpeningNew['ClosingStock'];
                            $ClosingStock = $rowOpeningNew['ClosingStock'];
                        } else {
                            $ClosingStock = $OpeningStock;
                        }
                    }
                    $updateQuery = "";
                    if ($GroupType == 1) {
                        if ($ProductionTypeID == "Opening Stock") {
                            $OpeningStock = (double) $AvalSlabs;
                            $updateQuery = ",`OpeningStock`=" . $this->q($OpeningStock);
                        } else {
                            $updateQuery = "";
                        }
                        $ClosingStock = (double) $ClosingStock + (double) $AvalSlabs - (double) $OldAvalSlabs;
                    } else if ($GroupType == 2) {
                        $ClosingStock = (double) $ClosingStock - (double) $AvalSlabs + (double) $OldAvalSlabs;
                    }

                    if ($DataID != '') {
                        $sqlUpdateVariety = "UPDATE `AdVarietyWiseOpeningStock` SET `ClosingStock`=" . $this->q($ClosingStock) . " $updateQuery WHERE `DataID`=" . $this->q($DataID);
                    } else {
                        $sqlUpdateVariety = "INSERT INTO `AdVarietyWiseOpeningStock`(`StockDate`, `VarietyID`, `OpeningStock`, `ClosingStock`,`PlantID`,`UnitID`) VALUES (" . $this->q($ProductionDate) . "," . (int) $VarietyID . "," . $this->q($OpeningStock) . "," . $this->q($ClosingStock) . "," . $this->q($PlantID) . "," . $this->q($UnitID) . ")";
                    }

                    $stmtUpdateVariety = $this->conn->prepare($sqlUpdateVariety);
                    if (!$stmtUpdateVariety->execute()) {
                        $errorInfo = $stmtUpdateVariety->errorInfo();
                        throw new RuntimeException('Failed to update AdVarietyWiseOpeningStock: ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'unknown error'));
                    }
                    $this->updateOpeningAndClosing($ProductionID, 0);

                    $Data['Error'] = null;
                    $Data['Msg'] = 'Posted Successfully';
                    $Data['NewAddedProdId'] = $ProductionID;

                    // $Data = $this->getDailyProductionDetails($RequestBody);
                } else {
                    $Data['Error'] = 'Please Enter Valid Mcs';
                    $Data['Msg'] = 'Please Enter Valid Mcs';
                    $this->conn->rollBack();
                    return $Data;
                }
            } else if ($ProductionID != '') {


                $SqlStockData = "SELECT * from AdAntiDumpingDetails where ADID =" . $this->q($ProductionID);
                $stmtStockData = $this->conn->prepare($SqlStockData);
                $stmtStockData->execute();
                $RowStockData = $stmtStockData->fetch(PDO::FETCH_ASSOC);
                // FIX: guard against a non-existent ProductionID instead of using
                // an empty row silently (which previously masked failures too).
                if (!$RowStockData) {
                    throw new RuntimeException("AdAntiDumpingDetails record not found for ADID=$ProductionID");
                }
                $OldAvalSlabs = $RowStockData['ActualSlabs'];
                $OldDate = $RowStockData['ProductionDate'];
                $OldVariety = $RowStockData['VarietyID'];
                $OldGroupType = $RowStockData['GroupType'];
                $OldGroup = $RowStockData['ProductionTypeID'];

                // if ($RowStockData['ActualMcs'] == $RowStockData['AvalMcs']) {

                if ($RequestBody['Type'] == "ProductUpdate") {

                    if ($GroupType != $OldGroupType) {

                        $this->updateOpeningAndClosingInDelete($ProductionID, $OldDate);
                        $sqlOpeningQuery = "SELECT * FROM `AdVarietyWiseOpeningStock` WHERE  VarietyID=" . (int) $VarietyID . " AND StockDate=" . $this->q($ProductionDate) . " AND `PlantID`=" . $this->q($PlantID) . " ORDER BY StockDate DESC";

                        $stmtOpening = $this->conn->prepare($sqlOpeningQuery);
                        $stmtOpening->execute();
                        $rowOpening = $stmtOpening->fetch(PDO::FETCH_ASSOC);
                        if ($rowOpening) {
                            $DataID = $rowOpening['DataID'];
                            $OpeningStock = $rowOpening['OpeningStock'];
                            $ClosingStock = $rowOpening['ClosingStock'];

                            $updateQuery = "";
                            if ($OldGroupType == '1') {
                                if ($OldGroup == "Opening Stock") {
                                    $OpeningStock = (double) $OpeningStock - (double) $OldAvalSlabs;
                                    $updateQuery = ",`OpeningStock`=" . $this->q($OpeningStock);
                                } else {
                                    $updateQuery = "";
                                }
                                $ClosingStock = (double) $ClosingStock - (double) $OldAvalSlabs - (double) $OldAvalSlabs;
                            } else if ($OldGroupType == '2') {
                                $ClosingStock = (double) $ClosingStock + (double) $OldAvalSlabs + (double) $OldAvalSlabs;
                                if ($ProductionTypeID == "Opening Stock") {
                                    $OpeningStock = (double) $OpeningStock + (double) $OldAvalSlabs;
                                    $updateQuery = ",`OpeningStock`=" . $this->q($OpeningStock);
                                }
                            }

                            if ($DataID != '') {
                                $sqlUpdateVariety = "UPDATE `AdVarietyWiseOpeningStock` SET `ClosingStock`=" . $this->q($ClosingStock) . " $updateQuery WHERE `DataID`=" . $this->q($DataID);
                            } else {
                                $sqlUpdateVariety = "INSERT INTO `AdVarietyWiseOpeningStock`(`StockDate`, `VarietyID`, `OpeningStock`, `ClosingStock`,`PlantID`,`UnitID`) VALUES (" . $this->q($ProductionDate) . "," . (int) $VarietyID . "," . $this->q($OpeningStock) . "," . $this->q($ClosingStock) . "," . $this->q($PlantID) . "," . $this->q($UnitID) . ")";
                            }

                            $stmtUpdateVariety = $this->conn->prepare($sqlUpdateVariety);
                            if (!$stmtUpdateVariety->execute()) {
                                $errorInfo = $stmtUpdateVariety->errorInfo();
                                throw new RuntimeException('Failed to update AdVarietyWiseOpeningStock (group change): ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'unknown error'));
                            }

                            $sqlQuery = "UPDATE `AdAntiDumpingDetails` SET  `GroupType`=" . $this->q($GroupType) . ",`ProductionTypeID`=" . $this->q($ProductionTypeID) . " where `ADID`=" . $this->q($ProductionID);
                            $stmtUpdateQuery = $this->conn->prepare($sqlQuery);
                            if (!$stmtUpdateQuery->execute()) {
                                $errorInfo = $stmtUpdateQuery->errorInfo();
                                throw new RuntimeException('Failed to update GroupType/ProductionTypeID: ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'unknown error'));
                            }
                        } else {
                            // FIX: previously, if no matching AdVarietyWiseOpeningStock
                            // row existed, the GroupType/ProductionTypeID change on
                            // AdAntiDumpingDetails was silently skipped entirely because
                            // this whole block lived inside `if ($rowOpening)`. Apply it
                            // here too so the group-type change is never dropped.
                            $sqlQuery = "UPDATE `AdAntiDumpingDetails` SET  `GroupType`=" . $this->q($GroupType) . ",`ProductionTypeID`=" . $this->q($ProductionTypeID) . " where `ADID`=" . $this->q($ProductionID);
                            $stmtUpdateQuery = $this->conn->prepare($sqlQuery);
                            if (!$stmtUpdateQuery->execute()) {
                                $errorInfo = $stmtUpdateQuery->errorInfo();
                                throw new RuntimeException('Failed to update GroupType/ProductionTypeID: ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'unknown error'));
                            }
                        }

                    }
                    if ($ProductionDate > $OldDate || $VarietyID != $OldVariety) {
                        $Data['delete'] = $ProductionID;
                        $this->updateOpeningAndClosingInDelete($ProductionID, $OldDate);
                    }
                    $sqlQuery = "UPDATE `AdAntiDumpingDetails` SET  `ActualSlabs`=" . $this->q($AvalSlabs) . ",
                    `AvalSlabs`=" . $this->q($AvalSlabs) . ",`ActualKgs`=" . $this->q($ActualKgs) . ",`AvalKgs`=" . $this->q($ActualKgs) . ",
                    `Grade`=" . $this->q($Grade) . ",`FreezingType`=" . $this->q($FreezingType) . ",`PoID`=" . $this->q($PoID) . ",`PoGradeID`=" . $this->q($PoGradeID) . ",`VarietyID`=" . (int) $VarietyID . ",`Galze`=" . $this->q($Galze) . ",`SockingType`=" . $this->q($SockingType) . ",
                    `ProductionDate`=" . $this->q($ProductionDate) . ",`PostedDate`=" . $this->q($CreatedDateTime) . ",`GroupType`=" . $this->q($GroupType) . ",
                    `ProductionTypeID`=" . $this->q($ProductionTypeID) . ",`ProductCategoryID`=" . $this->q($ProductCategoryID) . ",`UnitID`=" . $this->q($UnitID) . ",`PlantID`=" . $this->q($PlantID) . ",`FromPlantID`=" . $this->q($FromPlant) . ",`ToPlantID`=" . $this->q($ToPlant) . ", 
                    `PackingStyleID`=" . $this->q($PackingStyleID) . ",`Remarks`=" . $this->q($Remarks) . "
                    where `ADID`=" . $this->q($ProductionID);
                    $stmt = $this->conn->prepare($sqlQuery);
                    // FIX: this is the core reported bug. Previously:
                    //     $stmt->execute();
                    //     if ($stmt) { ... }
                    // `$stmt` is the PDOStatement object returned by prepare(), which
                    // is always truthy — so this block ran (and reported "Updated
                    // Successfully") even when the UPDATE itself failed, e.g. because
                    // an unescaped quote in $Remarks broke the SQL. Now we check the
                    // actual execute() result and throw (rolling back the whole
                    // transaction, including the AdVarietyWiseOpeningStock writes
                    // below) if the update didn't really happen.
                    if (!$stmt->execute()) {
                        $errorInfo = $stmt->errorInfo();
                        throw new RuntimeException('Failed to update AdAntiDumpingDetails: ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'unknown error'));
                    }

                    if ($ProductionDate != $OldDate || $VarietyID != $OldVariety) {

                        $sqlOpeningQuery = "SELECT * FROM `AdVarietyWiseOpeningStock` WHERE  VarietyID=" . $this->q($OldVariety) . " AND StockDate=" . $this->q($OldDate) . " AND `PlantID`=" . $this->q($PlantID) . " ORDER BY StockDate DESC";

                        $stmtOpening = $this->conn->prepare($sqlOpeningQuery);
                        $stmtOpening->execute();
                        $rowOpening = $stmtOpening->fetch(PDO::FETCH_ASSOC);
                        if ($rowOpening) {
                            $DataID = $rowOpening['DataID'];
                            $OpeningStock = $rowOpening['OpeningStock'];
                            $ClosingStock = $rowOpening['ClosingStock'];

                            $updateQuery = "";

                            if ($GroupType == 1) {
                                if ($ProductionTypeID == "Opening Stock") {
                                    $OpeningStock = (double) $OpeningStock - (double) $OldAvalSlabs;
                                    $updateQuery = ",`OpeningStock`=" . $this->q($OpeningStock);
                                }
                                $ClosingStock = (double) $ClosingStock - (double) $OldAvalSlabs;
                                if ($ProductionDate < $OldDate) {
                                    $OpeningStock = (double) $OpeningStock + (double) $AvalSlabs;
                                    $updateQuery = ",`OpeningStock`=" . $this->q($OpeningStock);
                                    $ClosingStock = (double) $ClosingStock + (double) $AvalSlabs;
                                }

                            } else if ($GroupType == 2) {
                                if ($ProductionDate < $OldDate) {
                                    $OpeningStock = (double) $OpeningStock - (double) $AvalSlabs;
                                    $updateQuery = ",`OpeningStock`=" . $this->q($OpeningStock);
                                    $ClosingStock = (double) $ClosingStock + (double) $OldAvalSlabs - (double) $AvalSlabs;
                                } else {
                                    $updateQuery = "";
                                    $ClosingStock = (double) $ClosingStock + (double) $OldAvalSlabs;
                                }
                            }
                            $sqlUpdateVariety = "UPDATE `AdVarietyWiseOpeningStock` SET `ClosingStock`=" . $this->q($ClosingStock) . " $updateQuery WHERE `DataID`=" . $this->q($DataID);
                            $stmtUpdateVariety = $this->conn->prepare($sqlUpdateVariety);
                            if (!$stmtUpdateVariety->execute()) {
                                $errorInfo = $stmtUpdateVariety->errorInfo();
                                throw new RuntimeException('Failed to update AdVarietyWiseOpeningStock (old date row): ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'unknown error'));
                            }
                            //                              if($ProductionDate > $OldDate || $VarietyID != $OldVariety){
                            //                             $this->updateOpeningAndClosing($DataID,$OldAvalSlabs);
                            //                              }else{
                            // $this->updateOpeningAndClosing($DataID, 0);
                            //                              }
                        }

                        $sqlOpeningQueryAd = "SELECT * FROM `AdVarietyWiseOpeningStock` WHERE  VarietyID=" . (int) $VarietyID . " AND StockDate=" . $this->q($ProductionDate) . " AND `PlantID`=" . $this->q($PlantID) . " ORDER BY StockDate DESC";

                        $stmtOpeningQueryAd = $this->conn->prepare($sqlOpeningQueryAd);
                        $stmtOpeningQueryAd->execute();
                        $rowOpeningAD = $stmtOpeningQueryAd->fetch(PDO::FETCH_ASSOC);
                        if ($rowOpeningAD) {
                            $DataIDAd = $rowOpeningAD['DataID'];
                            $OpeningStockAd = $rowOpeningAD['OpeningStock'];
                            $ClosingStockAd = $rowOpeningAD['ClosingStock'];
                        } else {
                            $DataIDAd = '';
                            $sqlOpeningQueryNew = "SELECT * FROM `AdVarietyWiseOpeningStock` WHERE  VarietyID=" . (int) $VarietyID . " AND StockDate<" . $this->q($ProductionDate) . " AND `PlantID`=" . $this->q($PlantID) . " ORDER BY StockDate DESC LIMIT 1";

                            $stmtOpeningNew = $this->conn->prepare($sqlOpeningQueryNew);
                            $stmtOpeningNew->execute();
                            $rowOpeningNew = $stmtOpeningNew->fetch(PDO::FETCH_ASSOC);

                            if ($rowOpeningNew) {
                                $OpeningStockAd = $rowOpeningNew['ClosingStock'];
                                $ClosingStockAd = $rowOpeningNew['ClosingStock'];
                            } else {
                                $OpeningStockAd = $OpeningStock;
                                $ClosingStockAd = $OpeningStockAd;
                            }
                        }
                        $updateQuery = "";
                        if ($GroupType == 1) {
                            if ($ProductionDate > $OldDate || $VarietyID != $OldVariety) {
                                $OldAvalSlabs = 0;
                            }
                            if ($ProductionTypeID == "Opening Stock") {
                                $OpeningStockAd = (double) $OpeningStockAd + (double) $AvalSlabs;
                                $updateQuery = ",`OpeningStock`=" . $this->q($OpeningStockAd);
                            }
                            $ClosingStockAd = (double) $ClosingStockAd + (double) $AvalSlabs;
                        } else if ($GroupType == 2) {
                            if ($ProductionDate > $OldDate || $VarietyID != $OldVariety) {
                                $ClosingStockAd = (double) $ClosingStockAd - (double) $AvalSlabs;

                                $OldAvalSlabs = 0;
                            } else {
                                $updateQuery = "";
                                $ClosingStockAd = (double) $ClosingStockAd - (double) $AvalSlabs;
                            }
                        }

                        if ($DataIDAd != '') {
                            $sqlUpdateVariety = "UPDATE `AdVarietyWiseOpeningStock` SET `ClosingStock`=" . $this->q($ClosingStockAd) . " $updateQuery WHERE `DataID`=" . $this->q($DataIDAd);
                        } else {
                            $sqlUpdateVariety = "INSERT INTO `AdVarietyWiseOpeningStock`(`StockDate`, `VarietyID`, `OpeningStock`, `ClosingStock`,`PlantID`,`UnitID`) VALUES (" . $this->q($ProductionDate) . "," . (int) $VarietyID . "," . $this->q($OpeningStockAd) . "," . $this->q($ClosingStockAd) . "," . $this->q($PlantID) . "," . $this->q($UnitID) . ")";
                        }

                        $Data['sqlUpdateVariety'] = $sqlUpdateVariety;
                        $stmtUpdateVariety = $this->conn->prepare($sqlUpdateVariety);
                        if (!$stmtUpdateVariety->execute()) {
                            $errorInfo = $stmtUpdateVariety->errorInfo();
                            throw new RuntimeException('Failed to update AdVarietyWiseOpeningStock (new date row): ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'unknown error'));
                        }

                        $Data['edit'] = $ProductionID;

                        $this->updateOpeningAndClosing($ProductionID, $OldAvalSlabs);
                        // }
                    } else {

                        $sqlOpeningQuery = "SELECT * FROM `AdVarietyWiseOpeningStock` WHERE  VarietyID=" . (int) $VarietyID . " AND StockDate=" . $this->q($ProductionDate) . " AND `PlantID`=" . $this->q($PlantID) . " ORDER BY StockDate DESC";

                        $stmtOpening = $this->conn->prepare($sqlOpeningQuery);
                        $stmtOpening->execute();
                        $rowOpening = $stmtOpening->fetch(PDO::FETCH_ASSOC);
                        if ($rowOpening) {
                            $DataID = $rowOpening['DataID'];
                            $OpeningStock = $rowOpening['OpeningStock'];
                            $ClosingStock = $rowOpening['ClosingStock'];
                        } else {
                            $DataID = '';
                            $sqlOpeningQueryNew = "SELECT * FROM `AdVarietyWiseOpeningStock` WHERE  VarietyID=" . (int) $VarietyID . " AND StockDate<" . $this->q($ProductionDate) . " AND `PlantID`=" . $this->q($PlantID) . " ORDER BY StockDate DESC LIMIT 1";

                            $stmtOpeningNew = $this->conn->prepare($sqlOpeningQueryNew);
                            $stmtOpeningNew->execute();
                            $rowOpeningNew = $stmtOpeningNew->fetch(PDO::FETCH_ASSOC);

                            if ($rowOpeningNew) {
                                // FIX: original code used $rowOpening (the empty/false
                                // result) instead of $rowOpeningNew here — copy-paste
                                // bug that would emit a PHP warning and read NULLs.
                                $OpeningStock = $rowOpeningNew['ClosingStock'];
                                $ClosingStock = $rowOpeningNew['ClosingStock'];
                            } else {
                                $ClosingStock = $OpeningStock;
                            }
                        }
                        $updateQuery = "";
                        if ($GroupType == 1) {
                            if ($ProductionTypeID == "Opening Stock") {
                                $OpeningStock = (double) $AvalSlabs;
                                $updateQuery = ",`OpeningStock`=" . $this->q($OpeningStock);
                            } else {
                                $updateQuery = "";
                            }
                            $ClosingStock = (double) $ClosingStock + (double) $AvalSlabs - (double) $OldAvalSlabs;
                        } else if ($GroupType == 2) {
                            $ClosingStock = (double) $ClosingStock - (double) $AvalSlabs + (double) $OldAvalSlabs;
                        }

                        if ($DataID != '') {
                            $sqlUpdateVariety = "UPDATE `AdVarietyWiseOpeningStock` SET `ClosingStock`=" . $this->q($ClosingStock) . " $updateQuery WHERE `DataID`=" . $this->q($DataID);
                        } else {
                            $sqlUpdateVariety = "INSERT INTO `AdVarietyWiseOpeningStock`(`StockDate`, `VarietyID`, `OpeningStock`, `ClosingStock`,`PlantID`,`UnitID`) VALUES (" . $this->q($ProductionDate) . "," . (int) $VarietyID . "," . $this->q($OpeningStock) . "," . $this->q($ClosingStock) . "," . $this->q($PlantID) . "," . $this->q($UnitID) . ")";
                        }

                        $stmtUpdateVariety = $this->conn->prepare($sqlUpdateVariety);
                        if (!$stmtUpdateVariety->execute()) {
                            $errorInfo = $stmtUpdateVariety->errorInfo();
                            throw new RuntimeException('Failed to update AdVarietyWiseOpeningStock (same date row): ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'unknown error'));
                        }
                        // $this->updateOpeningAndClosing($ProductionID);
                        if ($GroupType != $OldGroupType) {
                            $OldAvalSlabs = 0;
                        }
                        $this->updateOpeningAndClosing($ProductionID, $OldAvalSlabs);
                    }

                    $Data['Error'] = null;
                    $Data['Msg'] = 'Updated Successfully';
                } else {
                    $Data['ProductionID'] = $ProductionID;
                    $Data['Error'] = null;
                    $Data['Msg'] = 'No Access To Edit';
                    $this->conn->rollBack(); // FIX: nothing was written, no need to keep an open transaction
                    return $Data;
                }
            }

            $this->conn->commit(); // FIX: only commit after every write in this call succeeded

        } catch (Exception $e) {
            // FIX: any failure anywhere in the insert/update path now rolls back
            // ALL writes made in this call (AdAntiDumpingDetails +
            // AdVarietyWiseOpeningStock), instead of leaving the opening/closing
            // stock table updated while AdAntiDumpingDetails silently failed.
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $Data['Error'] = $e->getMessage();
            $Data['Msg'] = 'Unable to save';
        }

        return $Data;
    }

    public function getActualKgs($Slabs, $PackingStyle)
    {
        $sqlPackingStyle = "SELECT TotalSlabs,SlabWeight,MFactor FROM AdminPackingStyles WHERE PCKID=" . $this->q($PackingStyle);
        $stmtPackingStyle = $this->conn->prepare($sqlPackingStyle);
        $stmtPackingStyle->execute();
        $dataRowPackingStyle = $stmtPackingStyle->fetch(PDO::FETCH_ASSOC);

        // FIX: guard against an unknown PackingStyle producing no row, which
        // previously would trigger "undefined array key" and coerce to 0 silently.
        if (!$dataRowPackingStyle) {
            return 0;
        }

        $Kgs = $Slabs * $dataRowPackingStyle['SlabWeight'] * $dataRowPackingStyle['MFactor'];
        $ActualKgs = round($Kgs, 3);
        return $ActualKgs;
    }

    public function getBalanceKgs($TotalWeight, $ProductionID, $proddataRow)
    {
        $sqlFields = "sum((ActualMcs*TotalSlabs*SlabWeight*MFactor)) as Kgs,
        sum((ActualLooseBags*SlabWeight*MFactor)) as LKgs, 
        sum(ActualMcs*TotalSlabs) as ActualMcs,
        sum(ActualLooseBags) as ActualLooseBags";

        $sqlQuery = "SELECT $sqlFields  
         FROM `CsStockData` as CSD 
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID where ProductionID=" . $this->q($ProductionID);
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

    public function deleteSelectedDailyProduction($RequestBody)
    {
        $ProdID = $RequestBody['ProductionID'];  // e.g. "101,102,103"
        $ProductionIds = explode(',', $ProdID);  // ["101","102","103"]
        $data = [];
        foreach ($ProductionIds as $id) {
            $RequestBody['ProductionID'] = $id;
            $data[] = $this->deleteDailyProduction($RequestBody);
        }

        return $data;
    }
    public function deleteDailyProduction($RequestBody)
    {
        $ProdID = $RequestBody['ProductionID'];
        $ProductionDate = $RequestBody['ProductionDate'];

        // FIX: wrap the delete + stock rebalancing in a transaction so a failure
        // partway through (e.g. the DELETE failing) can't leave
        // AdVarietyWiseOpeningStock adjusted for a row that still exists.
        $this->conn->beginTransaction();
        try {
            $sqlDelete = "SELECT * FROM AdAntiDumpingDetails WHERE ADID =" . $this->q($ProdID);

            $stmtStockCount = $this->conn->prepare($sqlDelete);
            $stmtStockCount->execute();

            $Count = $stmtStockCount->fetch(PDO::FETCH_ASSOC);

            // FIX: guard against deleting a non-existent ADID
            if (!$Count) {
                throw new RuntimeException("AdAntiDumpingDetails record not found for ADID=$ProdID");
            }

            $sqlOpeningQuery = "SELECT * FROM `AdVarietyWiseOpeningStock` WHERE  VarietyID=" . $this->q($Count['VarietyID']) . " AND StockDate=" . $this->q($Count['ProductionDate']) . " AND `PlantID`=" . $this->q($Count['PlantID']);

            $stmtOpening = $this->conn->prepare($sqlOpeningQuery);
            $stmtOpening->execute();
            $rowOpening = $stmtOpening->fetch(PDO::FETCH_ASSOC);
            if ($rowOpening) {
                $ClosingStock = $rowOpening['ClosingStock'];
                $OpeningStock = $rowOpening['OpeningStock'];
                if ($Count['GroupType'] == 1) {
                    if ($Count['ProductionTypeID'] == 'IBT IN') {

                    }
                    if ($Count['ProductionTypeID'] == 'Opening Stock') {
                        $OpeningStock = $OpeningStock - $Count['ActualSlabs'];
                    }
                    $ClosingStock = $ClosingStock - $Count['ActualSlabs'];
                } else if ($Count['GroupType'] == 2) {
                    if ($Count['ProductionTypeID'] == 'IBT Out') {

                    }
                    $ClosingStock = $ClosingStock + $Count['ActualSlabs'];
                }
                $sqlUpdateVariety = "UPDATE `AdVarietyWiseOpeningStock` SET `OpeningStock`=" . $this->q($OpeningStock) . ",`ClosingStock`=" . $this->q($ClosingStock) . " WHERE `DataID`=" . $this->q($rowOpening['DataID']);
                $stmtUpdateVariety = $this->conn->prepare($sqlUpdateVariety);
                if (!$stmtUpdateVariety->execute()) {
                    $errorInfo = $stmtUpdateVariety->errorInfo();
                    throw new RuntimeException('Failed to update AdVarietyWiseOpeningStock before delete: ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'unknown error'));
                }
            }
            // FIX: if no matching AdVarietyWiseOpeningStock row exists, previously
            // this whole recalculation was silently skipped (no error) and the code
            // proceeded straight to deleting the AdAntiDumpingDetails row, leaving
            // opening/closing stock stale. That's a pre-existing data-integrity gap
            // worth reviewing with the business logic owner if it's expected to
            // always have a matching row at this point.

            $this->updateOpeningAndClosingInDelete($ProdID, '');

            $sqlDelete = "DELETE FROM AdAntiDumpingDetails WHERE ADID=" . $this->q($ProdID);

            $stmtDelete = $this->conn->prepare($sqlDelete);
            // FIX: check the actual execute() result instead of the always-truthy
            // PDOStatement object.
            if (!$stmtDelete->execute()) {
                $errorInfo = $stmtDelete->errorInfo();
                throw new RuntimeException('Failed to delete AdAntiDumpingDetails: ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'unknown error'));
            }

            $this->conn->commit();

            $this->Error = null;
            $this->Msg = 'Deleted Successfully';
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->Error = $e->getMessage();
            $this->Msg = 'Unable to delete';
        }

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        // return $this->getDailyProductionDetails($RequestBody);
        return $Data;
    }

    function getStatus($ProductionID, $AvalSlabs)
    {
        $sqlPackingStyle = "SELECT TotalSlabs,SlabWeight,MFactor FROM `AdAntiDumpingDetails` as CSD
                left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
                WHERE ADID=" . $this->q($ProductionID);
        $stmtPackingStyle = $this->conn->prepare($sqlPackingStyle);
        $stmtPackingStyle->execute();
        $dataRowPackingStyle = $stmtPackingStyle->fetch(PDO::FETCH_ASSOC);

        $Kgs = $AvalSlabs * $dataRowPackingStyle['SlabWeight'] * $dataRowPackingStyle['MFactor'];
        ;
        $ActualKgs = $TotalWeight = round($Kgs, 3);

        $dataRowAvalMcs['ActualSlabs'] = $AvalSlabs;
        $aryBalance = $this->getBalanceKgs($TotalWeight, $ProductionID, $dataRowAvalMcs);

        $BalanceKgs = $aryBalance['BalanceWeight'];
        if ($BalanceKgs <= 0) {
            $Status = 1;
        } else {
            $Status = 0;
        }
        $ArrayStatus['Status'] = $Status;
        $ArrayStatus['ActualKgs'] = $ActualKgs;
        $ArrayStatus['BalanceKgs'] = $BalanceKgs;

        return $ArrayStatus;
    }
    public function getPackingStyleDetails($RequestBody)
    {

        $PackingStyle = $RequestBody['PackingStyle'];


        $Query = '';

        if ($PackingStyle != '') {
            $Query .= "WHERE PCKID=" . $this->q($PackingStyle);
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

    public function updateOpeningAndClosing($ProdID, $oldSlabs)
    {
        $sqlDelete = "SELECT * FROM AdAntiDumpingDetails WHERE ADID =" . $this->q($ProdID);

        $stmtStockCount = $this->conn->prepare($sqlDelete);
        $stmtStockCount->execute();

        $Count = $stmtStockCount->fetch(PDO::FETCH_ASSOC);

        // FIX: guard against a missing row instead of using false/nulls below
        if (!$Count) {
            return;
        }

        $sqlOpeningQuery = "SELECT * FROM `AdVarietyWiseOpeningStock` WHERE  VarietyID=" . $this->q($Count['VarietyID']) . " AND StockDate>" . $this->q($Count['ProductionDate']) . " AND `PlantID`=" . $this->q($Count['PlantID']);

        $stmtOpening = $this->conn->prepare($sqlOpeningQuery);
        $stmtOpening->execute();
        while ($rowOpening = $stmtOpening->fetch(PDO::FETCH_ASSOC)) {
            $ClosingStock = $rowOpening['ClosingStock'];
            $OpeningStock = $rowOpening['OpeningStock'];
            if ($Count['GroupType'] == 1) {
                $OpeningStock = $OpeningStock + $Count['ActualSlabs'] - $oldSlabs;
                $ClosingStock = $ClosingStock + $Count['ActualSlabs'] - $oldSlabs;
            } else if ($Count['GroupType'] == 2) {
                $OpeningStock = $OpeningStock - $Count['ActualSlabs'] + $oldSlabs;
                $ClosingStock = $ClosingStock - $Count['ActualSlabs'] + $oldSlabs;
            }
            $sqlUpdateVariety = "UPDATE `AdVarietyWiseOpeningStock` SET `OpeningStock`=" . $this->q($OpeningStock) . ",`ClosingStock`=" . $this->q($ClosingStock) . " WHERE `DataID`=" . $this->q($rowOpening['DataID']);
            $stmtUpdateVariety = $this->conn->prepare($sqlUpdateVariety);
            // FIX: check execute() result and surface a failure instead of
            // continuing silently through the rest of the cascade.
            if (!$stmtUpdateVariety->execute()) {
                $errorInfo = $stmtUpdateVariety->errorInfo();
                throw new RuntimeException('Failed to cascade-update AdVarietyWiseOpeningStock: ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'unknown error'));
            }
        }
    }

    public function updateOpeningAndClosingInDelete($ProdID, $OldDate)
    {
        $sqlDelete = "SELECT * FROM AdAntiDumpingDetails WHERE ADID =" . $this->q($ProdID);

        $stmtStockCount = $this->conn->prepare($sqlDelete);
        $stmtStockCount->execute();

        $Count = $stmtStockCount->fetch(PDO::FETCH_ASSOC);

        // FIX: guard against a missing row instead of using false/nulls below
        if (!$Count) {
            return;
        }

        if ($OldDate != '') {
            $ProductionDate = $OldDate;
        } else {
            $ProductionDate = $Count['ProductionDate'];
        }

        $sqlOpeningQuery = "SELECT * FROM `AdVarietyWiseOpeningStock` WHERE  VarietyID=" . $this->q($Count['VarietyID']) . " AND StockDate>" . $this->q($ProductionDate) . " AND `PlantID`=" . $this->q($Count['PlantID']);

        $stmtOpening = $this->conn->prepare($sqlOpeningQuery);
        $stmtOpening->execute();
        while ($rowOpening = $stmtOpening->fetch(PDO::FETCH_ASSOC)) {
            $ClosingStock = $rowOpening['ClosingStock'];
            $OpeningStock = $rowOpening['OpeningStock'];
            if ($Count['GroupType'] == 1) {
                $OpeningStock = $OpeningStock - $Count['ActualSlabs'];
                $ClosingStock = $ClosingStock - $Count['ActualSlabs'];
            } else if ($Count['GroupType'] == 2) {
                $OpeningStock = $OpeningStock + $Count['ActualSlabs'];
                $ClosingStock = $ClosingStock + $Count['ActualSlabs'];
            }
            $sqlUpdateVariety = "UPDATE `AdVarietyWiseOpeningStock` SET `OpeningStock`=" . $this->q($OpeningStock) . ",`ClosingStock`=" . $this->q($ClosingStock) . " WHERE `DataID`=" . $this->q($rowOpening['DataID']);
            $stmtUpdateVariety = $this->conn->prepare($sqlUpdateVariety);
            // FIX: check execute() result and surface a failure instead of
            // continuing silently through the rest of the cascade.
            if (!$stmtUpdateVariety->execute()) {
                $errorInfo = $stmtUpdateVariety->errorInfo();
                throw new RuntimeException('Failed to cascade-update AdVarietyWiseOpeningStock (delete path): ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'unknown error'));
            }
        }
    }

}