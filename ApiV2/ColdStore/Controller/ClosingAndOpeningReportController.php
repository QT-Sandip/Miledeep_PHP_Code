<?php

include_once '../Global/Util.php';

class ClosingAndOpeningReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    function getClosingAndOpeningDetails($RequestBody)
    {
        $ColdstoreID = $RequestBody['ColdstoreID'];
        $ProductionDate = $RequestBody['ProductionDate'];
        $VarietyID = $RequestBody['VarietyID'];
        $Grade = $RequestBody['Grade'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $SockingType = $RequestBody['Soaking'];
        $StockType = $RequestBody['StockType']; // 'ClosingDate' or 'OpeningDate'
        $TypeProduct = $RequestBody['TypeProduct'];
        $ProductCategory = $RequestBody['ProductCategory'];
        $PackingType = $RequestBody['PackingType'];
        $FreezingType = $RequestBody['FreezingType'];
        $Species = $RequestBody['Species'];
        $AbStatus = $RequestBody['AbStatus'];

        /**
         * FIX: both "closing of today" and "opening of tomorrow" must use the
         * SAME operator relationship, just shifted by one day at the caller
         * level (i.e. the frontend passes tomorrow's date with StockType =
         * 'OpeningDate', or today's date with StockType = 'ClosingDate').
         * Closing(D) uses <=, Opening(D) uses < -- so Opening(D+1) always
         * equals Closing(D) as long as the same cutoff date is used for both
         * the "was this batch produced yet" check AND the "was this out
         * transaction posted yet" check. Previously only ProductionDate used
         * this operator; stock-out transactions were not date-filtered at
         * all, which is why today's outs bled into both columns.
         */
        $Operator = ($StockType == 'ClosingDate') ? '<=' : '<';
        $QuotedDate = $this->conn->quote($ProductionDate);

        // Build the optional filter list (applied against the ledger's own
        // columns, which mirror CsStockData's columns under the same names).
        $Filters = [];
        if ($ColdstoreID != '') {
            $Filters[] = "CPID.UnitID = " . $this->conn->quote($ColdstoreID);
        }
        if ($Grade != '') {
            $Filters[] = "CPID.Grade = " . $this->conn->quote($Grade);
        }
        if ($PackingStyle != '') {
            $Filters[] = "CPID.PackingStyleID = " . $this->conn->quote($PackingStyle);
        }
        if ($SockingType != '') {
            $Filters[] = "CPID.SockingType = " . $this->conn->quote($SockingType);
        }
        if ($ProductCategory != '') {
            $Filters[] = "CPID.ProductCategoryID = " . $this->conn->quote($ProductCategory);
        }
        if ($PackingType != '') {
            $Filters[] = "CPID.PackingTypeID = " . $this->conn->quote($PackingType);
        }
        if ($FreezingType != '') {
            $Filters[] = "CPID.FreezingType = " . $this->conn->quote($FreezingType);
        }
        if ($AbStatus != '') {
            $Filters[] = "CPID.AbStatus = " . $this->conn->quote($AbStatus);
        }
        // These reference the variety join, so they go in the outer query.
        $PivlFilters = [];
        if ($VarietyID != '') {
            $PivlFilters[] = "PIVL.ShortName = " . $this->conn->quote($VarietyID);
        }
        if ($TypeProduct != '') {
            $PivlFilters[] = "PIVL.TypeProduct = " . $this->conn->quote($TypeProduct);
        }
        if ($Species != '') {
            $PivlFilters[] = "PIVL.Species = " . $this->conn->quote($Species);
        }

        $FilterSql = $Filters ? ' AND ' . implode(' AND ', $Filters) : '';
        $PivlFilterSql = $PivlFilters ? ' AND ' . implode(' AND ', $PivlFilters) : '';

        /**
         * Per-StockID ledger:
         *   OriginalMcs/LooseBags/Kgs = what was ever produced into this
         *     StockID (derived as current live balance + everything ever
         *     taken out of it -- see the caveat in my message above).
         *   BalanceMcs/LooseBags/Kgs = OriginalMcs minus only the outs that
         *     had happened as of the report's cutoff date, using $Operator
         *     so Closing(D) and Opening(D+1) are computed identically.
         *   TodayOutMcs/LooseBags/Kgs = outs posted on exactly the report
         *     date, shown as their own column instead of being silently
         *     folded into the balance (this replaces the old undefined
         *     $arrayStockOutData / $TotalOutSlabs variables).
         */
        $LedgerSubquery = "
            SELECT
                CSD.StockID,
                CSD.ProductionID,
                CSD.ProductionDate,
                CSD.VarietyID,
                CSD.PackingStyleID,
                CSD.Grade,
                CSD.ProductCategoryID,
                CSD.PackingTypeID,
                CSD.FreezingType,
                CSD.SockingType,
                CSD.AbStatus,
                CSD.UnitID,
                CSD.PlantID,
                CSD.Galze,
                (CSD.AvalMcs + IFNULL(AllOuts.OutMcsAllTime, 0))
                    - IFNULL(OutsToDate.OutMcsToDate, 0) AS AvalMcs,
                (CSD.LooseBags + IFNULL(AllOuts.OutLooseAllTime, 0))
                    - IFNULL(OutsToDate.OutLooseToDate, 0) AS LooseBags,
                (CSD.AvalKgs + IFNULL(AllOuts.OutKgsAllTime, 0))
                    - IFNULL(OutsToDate.OutKgsToDate, 0) AS AvalKgs,
                IFNULL(OutsToday.OutMcsToday, 0) AS TodayOutMcs,
                IFNULL(OutsToday.OutLooseToday, 0) AS TodayOutLooseBags,
                IFNULL(OutsToday.OutKgsToday, 0) AS TodayOutKgs
            FROM CsStockData CSD
            LEFT JOIN (
                SELECT StockID,
                    SUM(OutMcs) AS OutMcsAllTime,
                    SUM(OutLooseBags) AS OutLooseAllTime,
                    SUM(ActualOutKgs) AS OutKgsAllTime
                FROM CsStockOutDetails
                GROUP BY StockID
            ) AllOuts ON AllOuts.StockID = CSD.StockID
            LEFT JOIN (
                SELECT StockID,
                    SUM(OutMcs) AS OutMcsToDate,
                    SUM(OutLooseBags) AS OutLooseToDate,
                    SUM(ActualOutKgs) AS OutKgsToDate
                FROM CsStockOutDetails
                WHERE OutDate $Operator $QuotedDate
                GROUP BY StockID
            ) OutsToDate ON OutsToDate.StockID = CSD.StockID
            LEFT JOIN (
                SELECT StockID,
                    SUM(OutMcs) AS OutMcsToday,
                    SUM(OutLooseBags) AS OutLooseToday,
                    SUM(ActualOutKgs) AS OutKgsToday
                FROM CsStockOutDetails
                WHERE OutDate = $QuotedDate
                GROUP BY StockID
            ) OutsToday ON OutsToday.StockID = CSD.StockID
            WHERE CSD.ProductionDate $Operator $QuotedDate
        ";

        $sqlFields = "PIVL.Species, StationName, PIVL.ShortName AS Variety, PIVL.TypeProduct,
        CPID.SockingType, CPS.PackingStyle, CPS.TotalSlabs AS PckTotalSlabs,
        CPID.Grade, SUM(CPID.AvalMcs) AS Mcs, SUM(CPID.LooseBags) AS LooseBags,
        SUM(CPID.AvalKgs) AS Kgs, SlabWeight, MFactor, TotalSlabs, CPID.VarietyID, CPID.PackingStyleID, PIVL.Glaze,
        SUM(CPID.AvalMcs * TotalSlabs) AS McsSlabls, CPID.ProductCategoryID, CPID.PackingTypeID, CPID.FreezingType,
        SUM(CPID.TodayOutMcs) AS TodayOutMcs, SUM(CPID.TodayOutLooseBags) AS TodayOutLooseBags,
        SUM(CPID.TodayOutKgs) AS TodayOutKgs,CPID.Galze";

        $sqlSlabs = "SELECT $sqlFields FROM ($LedgerSubquery) AS CPID
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID = CPID.PackingStyleID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID = PIVL.VarietyID
        LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID = CPL.ProductID
        LEFT JOIN RmProcessingStations AS RPS ON CPID.PlantID = RPS.ProcessingStationID
        WHERE (CPID.AvalMcs != 0 OR CPID.LooseBags != 0) $FilterSql $PivlFilterSql
        GROUP BY PIVL.ShortName, CPID.PackingStyleID, PIVL.Treatment, PIVL.TypeProduct, CPID.Grade, PIVL.Glaze, PIVL.Species,
        CPID.PackingTypeID, CPID.FreezingType";

        $stmtSlabs = $this->conn->query($sqlSlabs);
        if ($stmtSlabs === false) {
            return [
                'Error' => "Unable to load report data: " . implode(' | ', $this->conn->errorInfo()),
                'Msg' => null,
                'Query' => $sqlSlabs
            ];
        }

        $ArrayVarietyData = [];
        $SNo = 0;
        $TotalStockMcs = 0;
        $TotalStockSlabs = 0;

        while ($rowSlabs = $stmtSlabs->fetch(PDO::FETCH_ASSOC)) {

            $McsSlabls = $rowSlabs['McsSlabls'];
            $LooseBags = $rowSlabs['LooseBags'];
            $PckTotalSlabs = $rowSlabs['PckTotalSlabs'];
             $Galze = $rowSlabs['Galze'];

            // Re-normalize into whole cases + remainder loose bags after
            // aggregation, since summing across multiple StockData rows can
            // push LooseBags past a full case's worth.
            $TotalSlabsCombined = $McsSlabls + $LooseBags;
            $BalanceMcs = $PckTotalSlabs > 0 ? round(($TotalSlabsCombined / $PckTotalSlabs), 2) : 0;
            $BalanceKgs = round($rowSlabs['Kgs'], 2);


            $BalanceKgs = round($TotalSlabsCombined * $rowSlabs['SlabWeight'] * $rowSlabs['MFactor'], 2);
            if($Galze > 0){
                $BalanceKgs = $BalanceKgs - ($BalanceKgs * ($Galze/100));
            }

            $ArrayList = [];
            $ArrayList['SNo'] = ++$SNo;
            $ArrayList['Plant'] = $rowSlabs['StationName'];
            $ArrayList['Variety'] = $rowSlabs['Variety'];
            $ArrayList['Species'] = $rowSlabs['Species'];
            $ArrayList['SockingType'] = $rowSlabs['SockingType'];
            $ArrayList['PackingStyle'] = $rowSlabs['PackingStyle'];
            $ArrayList['Grade'] = $rowSlabs['Grade'];
            $ArrayList['TypeProduct'] = $rowSlabs['TypeProduct'];
            $ArrayList['Glaze'] = $rowSlabs['Glaze'];
            $ArrayList['Slabs'] = $TotalSlabsCombined > 0 ? $TotalSlabsCombined : 0;
            $ArrayList['Mcs'] = $BalanceMcs > 0 ? $BalanceMcs : 0;
            $ArrayList['Kgs'] = $BalanceKgs > 0 ? $BalanceKgs : 0;
            $ArrayList['ProductCategory'] = $rowSlabs['ProductCategoryID'];
            $ArrayList['FreezingType'] = $rowSlabs['FreezingType'];
            $ArrayList['PackingType'] = $rowSlabs['PackingTypeID'];

            // Real "today's transactions" figures, replacing the old
            // undefined $arrayStockOutData placeholder.
            $ArrayList['TodayOutMcs'] = $rowSlabs['TodayOutMcs'] > 0 ? $rowSlabs['TodayOutMcs'] : 0;
            $ArrayList['TodayOutLooseBags'] = $rowSlabs['TodayOutLooseBags'] > 0 ? $rowSlabs['TodayOutLooseBags'] : 0;
            $ArrayList['TodayOutKgs'] = $rowSlabs['TodayOutKgs'] > 0 ? round($rowSlabs['TodayOutKgs'], 2) : 0;

            $TotalStockMcs += $ArrayList['Mcs'];
            $TotalStockSlabs += $ArrayList['Slabs'];
            $ArrayVarietyData[] = $ArrayList;
        }

        $ArrayTotals['TotalStockMcs'] = $TotalStockMcs;
        $ArrayTotals['TotalStockSlabs'] = $TotalStockSlabs;

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['Totals'] = $ArrayTotals;
        $data['Data'] = $ArrayVarietyData;
        $data['Query'] = $sqlSlabs;

        return $data;
    }
}