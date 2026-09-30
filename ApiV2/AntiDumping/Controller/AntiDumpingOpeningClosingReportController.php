<?php

include_once '../Global/Util.php';

class AntiDumpingOpeningClosingReportController
{
    private $conn;
    private $Error = null;
    private $Msg = '';

    public function __construct($db)
    {
        $this->conn = $db;
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * ===============================
     *   OPENING / CLOSING STOCK
     * ===============================
     */
    public function getPoTotal($RequestBody)
    {
        $PoID = $RequestBody['PoID'];
        $UnitID = $RequestBody['ColdstoreID'];
        $ProductionDate = $RequestBody['ProductionDate'];
        $VarietyID = $RequestBody['VarietyID'];

        $Query = '';
        $openingStockData = [];

        if ($VarietyID != '') {
            $Query .= " AND SPVL.VarietyID='$VarietyID'";
            $Query .= " AND ProductionDate='$ProductionDate'";

            $sqlOpeningQuery = "SELECT * FROM `advarietywiseopeningstock`
                                WHERE VarietyID='$VarietyID'
                                AND StockDate='$ProductionDate'
                                AND PlantID IN ($UnitID)";
            $stmtOpening = $this->conn->prepare($sqlOpeningQuery);
            $stmtOpening->execute();
            $rowOpening = $stmtOpening->fetch(PDO::FETCH_ASSOC);

            if ($rowOpening) {
                $openingStockData['OpeningStock'] = $rowOpening['OpeningStock'];
                $openingStockData['ClosingStock'] = $rowOpening['ClosingStock'];
            } else {
                $sqlOpeningQuery = "SELECT * FROM `advarietywiseopeningstock`
                                    WHERE VarietyID='$VarietyID'
                                    AND StockDate<'$ProductionDate'
                                    AND PlantID In ($UnitID)
                                    ORDER BY StockDate DESC LIMIT 1";
                $stmtOpening = $this->conn->prepare($sqlOpeningQuery);
                $stmtOpening->execute();
                $rowOpening = $stmtOpening->fetch(PDO::FETCH_ASSOC);
                if ($rowOpening) {
                    $openingStockData['OpeningStock'] = $rowOpening['ClosingStock'];
                    $openingStockData['ClosingStock'] = $rowOpening['ClosingStock'];
                }
            }
        } elseif ($PoID != '') {
            $Query .= " AND PoID='$PoID'";
        } else {
            $Query .= " AND ProductionDate='$ProductionDate'";
        }

        if ($UnitID != '') {
            $Query .= " and PlantID IN ($UnitID)";
        }

        $sqlQuery = "SELECT CAD.GroupType, SUM(ActualSlabs) AS Total
                     FROM ADAntiDumpingDetails AS CAD
                     LEFT JOIN scrmpivarietylist AS SPVL ON CAD.VarietyID=SPVL.VarietyID
                     WHERE CAD.ProductionTypeID != 'Opening Stock' $Query
                     GROUP BY CAD.GroupType";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $Result = [];
        while ($rowSlabs = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($rowSlabs['GroupType'] == 1) {
                $Result['TotalInwardSlabs'] = $rowSlabs['Total'];
            } elseif ($rowSlabs['GroupType'] == 2) {
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

    /**
     * Builds a null-safe equality clause for a single column: if the
     * reference value is PHP null/empty, matches "column IS NULL" instead
     * of "column=''" (which would never match a genuinely NULL column in
     * SQL). Used by getAllGrades() below.
     */
    private function nullSafeEq($column, $value)
    {
        if ($value === null || $value === '') {
            return "($column IS NULL OR $column = '')";
        }
        $escaped = str_replace("'", "''", $value);
        return "$column = '$escaped'";
    }

    /**
     * ===============================
     *   GET ALL GRADES
     * ===============================
     */
    public function getAllGrades($RequestBody)
    {
        $PoID = $RequestBody['PoID'];
        $VarietyID = $RequestBody['VarietyID'];

        $Query = '';
        if ($PoID != '') {
            $Query .= " WHERE PoID='$PoID'";
        }

        $sqlQuery = "SELECT * FROM ScrmPIVarietyList WHERE VarietyID='$VarietyID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $rowVariety = $stmt->fetch(PDO::FETCH_ASSOC);

        $result = [];
        if (!empty($rowVariety)) {

            /**
             * FIX: previously every one of these conditions was built as
             * "Column='" . $rowVariety['Column'] . "'". When the reference
             * variety had a NULL value in any of these columns, PHP turned
             * that into an empty string, producing "Column=''" -- which
             * does NOT match rows where the column is genuinely NULL in
             * SQL. Any variety with a NULL in one of these fields was
             * silently excluded from its own grade group. Now built with
             * nullSafeEq() so NULL correctly matches NULL.
             */
            $matchConditions = [
                $this->nullSafeEq('PackingStyle', $rowVariety['PackingStyle']),
                $this->nullSafeEq('FreezingType', $rowVariety['FreezingType']),
                $this->nullSafeEq('Treatment', $rowVariety['Treatment']),
                $this->nullSafeEq('ShortName', $rowVariety['ShortName']),
                $this->nullSafeEq('Species', $rowVariety['Species']),
                $this->nullSafeEq('TypeProduct', $rowVariety['TypeProduct']),
                $this->nullSafeEq('Glaze', $rowVariety['Glaze']),
                $this->nullSafeEq('BrandName', $rowVariety['BrandName']),
                $this->nullSafeEq('UltimateConsignee', $rowVariety['UltimateConsignee']),
                $this->nullSafeEq('PreparationType', $rowVariety['PreparationType']),
            ];
            $matchWhere = implode(" AND ", $matchConditions);

            $glazePercVal = $rowVariety['GlazePerc'];
            $glazePercCondition = (empty($glazePercVal))
                ? "(GlazePerc IS NULL OR GlazePerc = 0)"
                : "GlazePerc = '" . str_replace("'", "''", $glazePercVal) . "'";

            $sqlGradesQuery = "
                SELECT spvl.*
                FROM ScrmPIVarietyList spvl
                INNER JOIN (
                    SELECT Grade, MAX(VarietyID) AS MaxVarietyID
                    FROM ScrmPIVarietyList
                    WHERE $matchWhere
                      AND $glazePercCondition
                      AND Status=1
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

    /**
     * ===============================
     *   GET ALL PO NUMBERS
     * ===============================
     */
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


    /**
     * ===============================
     *   DAILY PRODUCTION DETAILS
     * ===============================
     */
    public function getDailyProductionDetails($RequestBody)
    {
        $ProductionDate = $RequestBody['ProductionDate'];
        $ProductionStartDate = date("Y-m-d", strtotime("-6 months", strtotime($ProductionDate)));

        $ProductionEndDate = $RequestBody['ProductionToDate'];
        $UnitID = $RequestBody['ProductionPlant'];
        $PoNumber = $RequestBody['PoNumber'];
        $Variety = $RequestBody['Variety'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $Grade = $RequestBody['Grade'];
        $FreezingType = $RequestBody['FreezingType'];
        $Soaking = $RequestBody['Soaking'];

        $Query = '';
        if ($ProductionEndDate != '') {
            $Query = " WHERE ProductionDate BETWEEN '$ProductionDate' AND '$ProductionEndDate' ";
        } else {
            $Query = " WHERE ProductionDate='$ProductionDate'";
        }

        if ($PoNumber != '') {
            $Query .= " and CSD.PoNumber='$PoNumber'";
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

        $MonthlySummary = [];

        if (!empty($UnitID)) {

            $UnitIds = array_filter(explode(',', $UnitID));

            foreach ($UnitIds as $Unit) {

                $Unit = trim($Unit);

                $TempRequest = $RequestBody;
                $TempRequest['ProductionPlant'] = $Unit;

                try {
                    $MonthData = $this->getMonthlyGroupVarietySummary($TempRequest);
                    $MonthlySummary[] = $MonthData;
                } catch (Exception $e) {
                    echo $e->getMessage();
                    exit;
                }
            }
        } else {
            $MonthlySummary[] = $this->getMonthlyGroupVarietySummary($RequestBody);
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['MonthlySummary'] = $MonthlySummary;
        $Data['MonthsData'] = $MonthlySummary[0]['MonthsData'];
        $Data['ColumnTotals'] = $MonthlySummary[0]['ColumnTotals'];
        return $Data;
    }

    public function getMonthlyGroupVarietySummary($RequestBody)
    {
        $ProductionDate = $RequestBody['ProductionDate'];
        $ProductionStartDate = date("Y-m-d", strtotime("-6 months", strtotime($ProductionDate)));
        $ProductionEndDate = !empty($RequestBody['ProductionToDate'])
            ? $RequestBody['ProductionToDate']
            : $ProductionDate;

        $UnitID          = $RequestBody['ProductionPlant'];
        $Variety         = $RequestBody['Variety'];
        $PackingStyle    = $RequestBody['PackingStyle'];
        $Grade           = $RequestBody['Grade'];
        $FreezingType    = $RequestBody['FreezingType'];
        $Soaking         = $RequestBody['Soaking'];
        $Type = $RequestBody['Type'];

        /* ==============================
           STEP 1: Groups
        ============================== */
        $AllGroups = array();
        $stmtGroups = $this->conn->query("SELECT DISTINCT AdDisplayName FROM CsTransactionTypes");
        while ($g = $stmtGroups->fetch(PDO::FETCH_ASSOC)) {
            $AllGroups[] = $g['AdDisplayName'];
        }

        /* ==============================
           STEP 2: WHERE
        ============================== */
        $conditions = array();
        $conditions[] = "CSD.ProductionDate BETWEEN '$ProductionDate' AND '$ProductionEndDate'";

        if (!empty($UnitID)) $conditions[] = "CSD.PlantID IN ($UnitID)";
        if (!empty($Variety)) $conditions[] = "CSD.VarietyID='$Variety'";
        if (!empty($PackingStyle)) $conditions[] = "CSD.PackingStyleID='$PackingStyle'";
        if (!empty($Grade)) $conditions[] = "CSD.Grade='$Grade'";
        if (!empty($FreezingType)) $conditions[] = "CSD.FreezingType='$FreezingType'";
        if (!empty($Soaking)) $conditions[] = "CSD.SockingType='$Soaking'";

        $where = "WHERE " . implode(" AND ", $conditions);

        /* ==============================
           STEP 3: Production Data
        ============================== */
        $sql = "
            SELECT 
                DATE_FORMAT(CSD.ProductionDate, '%Y-%m') AS YearMonth,
                CSD.ProductionTypeID,
                CST.AdDisplayName,
                CSD.VarietyID,
                CSD.PlantID,
                SUM(CSD.AvalSlabs) AS TotalAvalSlabs,
                SUM(CSD.ActualSlabs - CSD.AvalSlabs) AS TotalBalanceSlabs,
                SUM(CSD.AvalKgs) AS TotalKgs,
                SPV.DeviSupc,
                SPV.ProductDescription,
                CSD.Grade,
                MAX(RPS.StationName) AS StationName,
                APS.PackingStyle AS PackingStyleName,
                APS.TotalSlabs AS packingSlabs,
                APS.SlabWeight,APS.MFactor
            FROM AdAntiDumpingDetails CSD
            LEFT JOIN ScrmPIVarietyList SPV ON SPV.VarietyID = CSD.VarietyID
            LEFT JOIN AdminPackingStyles APS ON APS.PCKID = CSD.PackingStyleID
            LEFT JOIN RmProcessingStations RPS ON RPS.ProcessingStationID = CSD.PlantID
            LEFT JOIN CsTransactionTypes CST 
                ON CST.TransCategory = CSD.ProductionTypeID AND CST.TransTypeID != 9
            $where
            GROUP BY YearMonth, CSD.ProductionTypeID, CSD.VarietyID,CSD.PlantID
            ORDER BY YearMonth ASC
        ";

        $stmt = $this->conn->query($sql);
        $productionRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /* ==============================
           STEP 4: STOCK QUERY
           (Deliberately queries from the 6-month lookback start, not the
           requested start, so a carry-forward value is available even if
           a variety has no stock snapshot within the requested range.)
        ============================== */
        $stockConditions = array();
        $stockConditions[] = "StockDate BETWEEN '$ProductionDate' AND '$ProductionEndDate'";
        if (!empty($Variety)) $stockConditions[] = "VarietyID='$Variety'";
        if (!empty($UnitID)) $stockConditions[] = "PlantID IN ($UnitID)";

        $stockWhere = "WHERE " . implode(" AND ", $stockConditions);

        $sqlStock = "
        SELECT 
            t.VarietyID,
            t.YearMonth,
            (
                SELECT s1.OpeningStock
                FROM AdVarietyWiseOpeningStock s1
                $stockWhere
                  AND s1.VarietyID = t.VarietyID
                  AND DATE_FORMAT(s1.StockDate,'%Y-%m') = t.YearMonth
                ORDER BY s1.StockDate ASC
                LIMIT 1
            ) AS OpeningStock,
            (
                SELECT s2.ClosingStock
                FROM AdVarietyWiseOpeningStock s2
                $stockWhere
                  AND s2.VarietyID = t.VarietyID
                  AND DATE_FORMAT(s2.StockDate,'%Y-%m') = t.YearMonth
                ORDER BY s2.StockDate DESC
                LIMIT 1
            ) AS ClosingStock
        FROM (
            SELECT DISTINCT 
                VarietyID,
                DATE_FORMAT(StockDate,'%Y-%m') AS YearMonth
            FROM AdVarietyWiseOpeningStock
            $stockWhere
        ) t
        ORDER BY t.YearMonth
        ";

        $stockStmt = $this->conn->query($sqlStock);

        $stockData = array();
        while ($s = $stockStmt->fetch(PDO::FETCH_ASSOC)) {
            $stockData[$s['VarietyID']][$s['YearMonth']] = array(
                'OpeningStock' => (float)$s['OpeningStock'],
                'ClosingStock' => (float)$s['ClosingStock']
            );
        }

        /* ==============================
           STEP 5: Month lists
           - $MonthsData: the requested/displayed range.
           - $FullMonthsData: lookback range through end date, used ONLY
             internally to correctly seed the carry-forward balance before
             the displayed range begins.
        ============================== */
        $MonthsData = array();
        $start = new DateTime($ProductionDate);
        $end   = new DateTime($ProductionEndDate);
        $end->modify('first day of next month');
        while ($start < $end) {
            $MonthsData[] = $start->format('Y-m');
            $start->modify('+1 month');
        }

        $FullMonthsData = array();
        $fstart = new DateTime($ProductionDate);
        $fend   = new DateTime($ProductionEndDate);
        $fend->modify('first day of next month');
        while ($fstart < $fend) {
            $FullMonthsData[] = $fstart->format('Y-m');
            $fstart->modify('+1 month');
        }

        /* ==============================
           STEP 6: CARRY FORWARD FIX
           FIX: previously this loop walked $MonthsData (the requested
           range only), so $prevClosing always started at 0 and the 6-month
           lookback fetched in STEP 4 was never actually consulted. Any
           variety whose last stock snapshot fell within the lookback
           window (rather than exactly in the first requested month) showed
           OpeningStock=0 instead of its real carried-forward balance. Now
           walks $FullMonthsData so $prevClosing is correctly established
           before the displayed range begins.
        ============================== */
        $allVarieties = array();
        foreach ($stockData as $vid => $v) {
            $allVarieties[$vid] = true;
        }
        foreach ($productionRows as $r) {
            $allVarieties[$r['VarietyID']] = true;
        }

        foreach ($allVarieties as $vid => $_) {

            if (!isset($stockData[$vid])) {
                $stockData[$vid] = array();
            }

            $prevClosing = 0;

            foreach ($FullMonthsData as $month) {

                if (isset($stockData[$vid][$month])) {

                    $open  = isset($stockData[$vid][$month]['OpeningStock']) ? $stockData[$vid][$month]['OpeningStock'] : 0;
                    $close = isset($stockData[$vid][$month]['ClosingStock']) ? $stockData[$vid][$month]['ClosingStock'] : 0;

                    if ($open == 0) {
                        $open = $prevClosing;
                    }

                    $stockData[$vid][$month] = array(
                        'OpeningStock' => $open,
                        'ClosingStock' => $close
                    );

                    $prevClosing = $close;

                } else {
                    $stockData[$vid][$month] = array(
                        'OpeningStock' => $prevClosing,
                        'ClosingStock' => $prevClosing
                    );
                }
            }
        }

        /* ==============================
           STEP 7: BUILD SUMMARY
        ============================== */
        $MonthlySummary = array();
        $ColumnTotals   = array();

        foreach ($productionRows as $row) {

            $vid     = $row['VarietyID'];
            $month   = $row['YearMonth'];
            $grpName = $row['AdDisplayName'];
            $packingSlabs = $row['packingSlabs'];
            $SlabWeight  = $row['SlabWeight'];
            $MFactor  = $row['MFactor'];

            if (!isset($MonthlySummary[$vid])) {
                $MonthlySummary[$vid] = array(
                    'varietyId' => $vid,
                    'ProductDescription' => $row['ProductDescription'],
                    'Grade' => $row['Grade'],
                    'ProductionPlantName' => $row['StationName'],
                    'PackingStyle' => $row['PackingStyleName'],
                    'OurSupc' => $row['DeviSupc'],
                    'varietyData' => array()
                );
            }

            if (!isset($MonthlySummary[$vid]['varietyData'][$month])) {

                foreach ($AllGroups as $g) {

                    if ($Type === 'MCS') {
                        $open  = isset($stockData[$vid][$month]['OpeningStock']) ? round(($stockData[$vid][$month]['OpeningStock'] / $packingSlabs), 2) : 0;
                        $close = isset($stockData[$vid][$month]['ClosingStock']) ? round(($stockData[$vid][$month]['ClosingStock'] / $packingSlabs), 2) : 0;
                    } else if ($Type === 'KGS') {
                        $open  = isset($stockData[$vid][$month]['OpeningStock']) ? ($stockData[$vid][$month]['OpeningStock'] * $SlabWeight * $MFactor) : 0;
                        $close = isset($stockData[$vid][$month]['ClosingStock']) ? ($stockData[$vid][$month]['ClosingStock'] * $SlabWeight * $MFactor) : 0;
                    } else {
                        $open  = isset($stockData[$vid][$month]['OpeningStock']) ? $stockData[$vid][$month]['OpeningStock'] : 0;
                        $close = isset($stockData[$vid][$month]['ClosingStock']) ? $stockData[$vid][$month]['ClosingStock'] : 0;
                    }

                    $MonthlySummary[$vid]['varietyData'][$month][$g] = array(
                        'TotalAvalSlabs' => 0,
                        'TotalBalanceSlabs' => 0,
                        'TotalKgs' => "0.000",
                        'OpeningStock' => $open,
                        'ClosingStock' => $close
                    );
                }
            }

            $MonthlySummary[$vid]['varietyData'][$month][$grpName]['TotalAvalSlabs'] =
                (int)$row['TotalAvalSlabs'];

            $MonthlySummary[$vid]['varietyData'][$month][$grpName]['TotalBalanceSlabs'] =
                (int)$row['TotalBalanceSlabs'];

            $MonthlySummary[$vid]['varietyData'][$month][$grpName]['TotalKgs'] =
                sprintf('%.3f', $row['TotalKgs']);

            if ($Type === 'MCS') {
                $Mcs = round($row['TotalAvalSlabs'] / $packingSlabs, 2);
                $MonthlySummary[$vid]['varietyData'][$month][$grpName]['TotalAvalSlabs'] = $Mcs;
            } else if ($Type === 'KGS') {
                $MonthlySummary[$vid]['varietyData'][$month][$grpName]['TotalAvalSlabs'] = sprintf('%.3f', $row['TotalKgs']);
            } else {
                $MonthlySummary[$vid]['varietyData'][$month][$grpName]['TotalAvalSlabs'] = (int)$row['TotalAvalSlabs'];
            }
        }

        return array(
            'Error' => null,
            'Msg' => 'Success',
            'MonthlySummary' => array_values($MonthlySummary),
            'MonthsData' => $MonthsData,
            'ColumnTotals' => $ColumnTotals
        );
    }

    /**
     * ===============================
     *   GET PRODUCTION LIST
     * ===============================
     */
    function getProductionlist($Query, $RequestBody)
    {
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $Type = $RequestBody['Type'];
        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage;
        if ($Limit > 0) {
            $Limit .= ' OFFSET ' . $LimitMax;
        }
        if ($LoadAll == 1) {
            $Limit = '';
        }

        $SearchQuery = '';
        if ($SearchKey != '') {
            $SearchQuery = " AND CONCAT_WS('', SPV.DeviSupc, ProductDescription, ADID, AvalSlabs, CSD.Grade,
             CSD.FreezingType, CSD.VarietyID, Galze, SockingType, ProductionDate, ProductionTypeID, ProductCategoryID, UnitID,
             CSD.PackingStyleID, CSD.Remarks, CSD.PlantID,
             StockStatus, ShortName, APS.PackingStyle, StoreName, RPS.StationName,
             AvalKgs, ActualSlabs, CSD.PoID, CSD.PoGradeID, SPD.PoNo)
             LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(*) as Total FROM AdAntiDumpingDetails as CSD
            left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
            left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
            left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
            left join CsStores as CSS on CSS.StoreID=CSD.UnitID
            left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID
            $Query $SearchQuery order by ProductionDate Desc";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $rowCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $rowCount['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "SPV.DeviSupc, ProductDescription, ADID, AvalSlabs, CSD.Grade,
            CSD.FreezingType, CSD.VarietyID, Galze, SockingType, ProductionDate, ProductionTypeID, ProductCategoryID, UnitID,
            CSD.PackingStyleID, CSD.Remarks, CSD.PlantID,
            StockStatus, ShortName as Variety, CSD.GroupType,
            APS.PackingStyle, StoreName, RPS.StationName,
            AvalKgs as Kgs, ActualSlabs, CSD.PoID, CSD.PoGradeID, SPD.PoNo,
            RPS1.StationName AS FromPlant, RPS2.StationName AS ToPlant";

        $sqlQuery = "SELECT $sqlFields FROM AdAntiDumpingDetails as CSD
            left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
            left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
            left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
            left join CsStores as CSS on CSS.StoreID=CSD.UnitID
            left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID
            left join RmProcessingStations as RPS1 on RPS1.ProcessingStationID=CSD.FromPlantID
            left join RmProcessingStations as RPS2 on RPS2.ProcessingStationID=CSD.ToPlantID
            $Query $SearchQuery
            order by ProductionDate Desc $Limit";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $AryData = [];
        $i = $LimitMax;

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $TotalWeight = round($dataRow['Kgs'], 3);
            $aryBalance = $this->getBalanceKgs($TotalWeight, $dataRow['ADID'], $dataRow);

            $BalanceSlabs = $aryBalance['BalanceSlabs'];
            $BalanceKgs = $aryBalance['BalanceWeight'];
            $Status = ($BalanceKgs <= 0 && $BalanceSlabs <= 0) ? 1 : 0;
            if ($BalanceSlabs <= 0) {
                $BalanceKgs = 0;
                $Status = 1;
            }

            $ArrayStockData = [];
            $ArrayStockData['SNo'] = ++$i;
            $ArrayStockData['ProductionID'] = $dataRow['ADID'];
            $ArrayStockData['ProductionDate'] = date('d-m-Y', strtotime($dataRow['ProductionDate']));
            $ArrayStockData['Group'] = $dataRow['ProductionTypeID'];
            $ArrayStockData['GroupType'] = $dataRow['GroupType'];
            $ArrayStockData['StoreName'] = $dataRow['StoreName'];
            $ArrayStockData['ProductionPlantName'] = $dataRow['StationName'];
            $ArrayStockData['Variety'] = $dataRow['Variety'];
            $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStockData['Grade'] = $dataRow['Grade'];
            $ArrayStockData['FreezingType'] = $dataRow['FreezingType'];
            $ArrayStockData['Soaking'] = $dataRow['SockingType'];
            $ArrayStockData['PoNo'] = $dataRow['PoNo'];
            $ArrayStockData['AvalSlabs'] = $dataRow['AvalSlabs'];
            $ArrayStockData['Kgs'] = sprintf('%.3f', $TotalWeight);
            $ArrayStockData['BalanceSlabs'] = $BalanceSlabs;
            $ArrayStockData['BalanceKgs'] = sprintf('%.3f', $BalanceKgs);
            $ArrayStockData['Status'] = $Status;

            if ($dataRow['ProductionTypeID'] == 'IBT IN') {
                $ArrayStockData['FromPlant'] = $dataRow['FromPlant'];
                $ArrayStockData['ToPlant'] = $dataRow['StoreName'];
            } elseif ($dataRow['ProductionTypeID'] == 'IBT Out') {
                $ArrayStockData['FromPlant'] = $dataRow['StoreName'];
                $ArrayStockData['ToPlant'] = $dataRow['ToPlant'];
            } else {
                $ArrayStockData['FromPlant'] = '';
                $ArrayStockData['ToPlant'] = '';
            }

            $AryData[] = $ArrayStockData;
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['ArrayData'] = $AryData;
        $Data['TotalCount'] = $TotalCount;
        $Data['PageCount'] = $PageCount;
        return $Data;
    }

    /**
     * ===============================
     *   BALANCE KGS
     * ===============================
     */
    public function getBalanceKgs($TotalWeight, $ProductionID, $proddataRow)
    {
        $BalanceSlabs = $proddataRow['AvalSlabs'];
        $BalanceWeight = $TotalWeight;
        $ReturnData['BalanceSlabs'] = $BalanceSlabs;
        $ReturnData['BalanceWeight'] = $BalanceWeight;
        return $ReturnData;
    }

    /**
     * ===============================
     *   PACKING STYLE DETAILS
     * ===============================
     */
    public function getPackingStyleDetails($RequestBody)
    {
        $PoID = $RequestBody['PoID'];
        $VarietyID = $RequestBody['VarietyID'];

        $Query = '';
        if ($PoID != '') {
            $Query .= " AND PoID='$PoID'";
        }

        $result = [];
        $sqlQuery = "SELECT SPVL.VarietyID, SPVL.PackingStyle, AP.PackingStyle, SPVL.Grade
                     FROM ScrmPIVarietyList as SPVL
                     LEFT JOIN AdminPackingStyles as AP on AP.PCKID=SPVL.PackingStyle
                     WHERE SPVL.VarietyID='$VarietyID' $Query";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($rowStyle = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $rowStyle;
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['data'] = $result;
        return $Data;
    }
}