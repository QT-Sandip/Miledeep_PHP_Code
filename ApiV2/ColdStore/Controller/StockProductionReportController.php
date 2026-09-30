<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class StockProductionReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }

 function getStockReportDetails($RequestBody)
{
    $RecordsPerPage = 100;

    $ColdstoreID = $RequestBody['ColdstoreID'];
    $PackData = $RequestBody['PackData'];
    $Group = $RequestBody['Group'];
    $Category = $RequestBody['Category'];
    $FreezingType = $RequestBody['FreezingType'];
    $PackingStyle = $RequestBody['PackingStyle'];
    $Grade = $RequestBody['Grade'];
    $PONumber = $RequestBody['PONumber'];
    $VarietyID = $RequestBody['VarietyID'];
    $SName = $RequestBody['VarietyID'];
    $AntiBiotic = $RequestBody['AntiBiotic'];
    $Soaking = $RequestBody['Soaking'];
    $TenantID = $RequestBody['TenantID'];
    $SearchKey = $RequestBody['SearchKey'];
    $Species = $RequestBody['Species'];
    $TypeProduct = $RequestBody['TypeProduct'];
    $StockDate = $RequestBody['StockDate'];
    $Brand = $RequestBody['Brand'];
    $Blocks = $RequestBody['Blocks'];
    $ConvertLooseToMcs = $RequestBody['ConvertLooseToMcs'];

    $PageNo = $RequestBody['PageNo'];
    $OutputType = $RequestBody['Type'];
    $LoadAll = $RequestBody['LoadAll'];

    $totalMCS = 0;
    $totalKGs = 0;
    $overAllMcs = 0;

    $Query = '';
    $DayQuery = '';

    if ($ColdstoreID != '') {
        $Query .= "AND CPID.`UnitID`='$ColdstoreID'";
    }
    if ($PackData != '') {
        $Query .= "AND CPID.`PackingTypeID`='$PackData'";
    }
    if ($Group != '') {
        $Query .= "AND CPID.`ProductionTypeID`='$Group'";
    }
    if ($PackingStyle != '') {
        $Query .= "AND CPID.`PackingStyleID`='$PackingStyle'";
    }
    if ($Grade != '') {
        $Query .= "AND CPID.`Grade`='$Grade'";
    }
    if ($Category != '') {
        $Query .= "AND CPID.`ProductCategoryID`='$Category'";
    }
    if ($FreezingType != '') {
        $Query .= "AND CPID.`FreezingType`='$FreezingType'";
    }
    if ($PONumber != '') {
        $Query .= "AND CPID.`PoID`='$PONumber'";
    }
    if ($VarietyID != '') {
        $Query .= "AND PIVL.ShortName='$VarietyID'";
    }
    if ($Brand != '') {
        $Query .= " AND PIVL.`BrandName`='$Brand'";
    }
    if ($AntiBiotic != '') {
        $Query .= " AND CPID.`AbStatus` = '$AntiBiotic' ";
    }
    if ($Soaking != '') {
        $Query .= " AND CPID.`SockingType`='$Soaking'";
    }
    if ($TenantID != '') {
        $Query .= " AND CPID.StockHolderID='$TenantID'";
    }
    if ($Species != '') {
        $Query .= " AND PIVL.Species='$Species'";
    }
    if ($TypeProduct != '') {
        $Query .= " AND PIVL.TypeProduct='$TypeProduct'";
    }
    if ($Blocks != "") {
        $Query .= " and CSBD.BlockID='$Blocks'";
    }

    if ($StockDate != '') {
        $DayQuery .= " AND CPID.ProductionDate='$StockDate'";
        $Query .= " AND CPID.ProductionDate BETWEEN '2025-04-01' AND '$StockDate'";
    }

    $AryGardesNames = $this->getGradeNames($Query);

    $RecordsPerPage = 100;
    $LimitMax = ($PageNo - 1) * $RecordsPerPage;
    $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

    if ($LoadAll == 1) {
        $Limit = '';
    }

    if ($SearchKey != '') {
        $Query = " and CONCAT_WS('', CPID.VarietyID, ShortName, PackingStyle, AvalMcs, LooseBags) LIKE '%$SearchKey%'";
    }

    $SqlFields = "GROUP_CONCAT(CPID.StockID) as StockIDs,
        GROUP_CONCAT(DISTINCT(CPID.VarietyID)) as VarietyID,
        PIVL.ProductDescription,
        PIVL.Species,
        PIVL.ShortName,
        PIVL.ProductID,
        PIVL.TypeProduct,
        CPS.PackingStyle,
        PIVL.GlazePerc,
        PIVL.Treatment as Soaking,
        CPID.ProductionDate,
        CPID.AbStatus,
        PackingStyleID,
        CPID.PackingTypeID as PackingType,
        SUM(AvalMcs) as MCS,
        PIVL.Glaze,
        ProductCategoryID,
        SUM(LooseBags) as LooseBags,
        GROUP_CONCAT(LooseBags) AS mcscheck,
        SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as KGS,
        SUM(LooseBags*SlabWeight*MFactor) as LKGS,
        SBM.BrandName,
        CPID.UnitID,
        CPID.FreezingType,
        SBM.BrandID AS BrandID";

    $sqlQuery = "SELECT $SqlFields FROM `CsStockData` AS CPID
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID
        LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
        LEFT JOIN ScrmBrandsMaster AS SBM ON SBM.BrandID=PIVL.BrandName
        LEFT JOIN `CsBarCodes` as cb on cb.BarCodeID = CPID.BarCodeID
        LEFT JOIN CsStoresRackData as CSRD on CSRD.RowID=CPID.StoreRackID
        LEFT JOIN CsStoresBlockData AS CSBD on CSBD.BlockID=CSRD.BlockID
        WHERE 1=1
        $Query
        GROUP BY CPID.PackingTypeID,PIVL.ShortName,PIVL.Species,PIVL.TypeProduct,CPID.PackingStyleID,
        CPID.ProductCategoryID,CPID.AbStatus,PIVL.Glaze,CPID.SockingType,PIVL.GlazePerc,PIVL.BrandName,CPID.FreezingType
        ORDER BY PIVL.ShortName ASC";

    $stmtSelect = $this->conn->prepare($sqlQuery);
    $stmtSelect->execute();

    $TotalCount = $stmtSelect->rowCount();

    $SNo = 0;
    $ArrayStoreData = array();

    /*
     * ------------------------------------------------------------------
     * IMPORTANT OPTIMIZATION STRATEGY
     *
     * We deliberately optimize PER MAIN GROUPED ROW, not globally.
     *
     * The original code does:
     *
     *   for each main row
     *       for each grade
     *           stock query
     *           stock-out query
     *           day query
     *
     * This version does:
     *
     *   for each main row
     *       ONE stock query for ALL grades
     *       ONE stock-out query for ALL grades
     *       ONE day query for ALL grades
     *
     * Therefore StockIDs can safely be:
     *
     *   123
     *   123,124,125
     *
     * without mixing one main row with another.
     *
     * The WHERE conditions remain equivalent to the original query.
     * ------------------------------------------------------------------
     */
    while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

        $Species = $dataRow['Species'];
        $ProductID = $dataRow['ProductID'];
        $TypeProduct = $dataRow['TypeProduct'];
        $StockIDs = trim($dataRow['StockIDs']);
        $VarietyID = $dataRow['VarietyID'];
        $PackStyleId = $dataRow['PackingStyleID'];

        $TopRow = array();
        $TopRow['SNo'] = ++$SNo;
        $TopRow['VarietyID'] = $dataRow['VarietyID'];
        $TopRow['ProductDescription'] = $dataRow['ProductDescription'];
        $TopRow['Variety'] = $dataRow['ShortName'];
        $TopRow['Species'] = $dataRow['Species'];
        $TopRow['TypeProduct'] = $dataRow['TypeProduct'];
        $TopRow['PackingType'] = $dataRow['PackingType'];
        $TopRow['Soaking'] = $dataRow['Soaking'];
        $TopRow['ProductCategory'] = $dataRow['ProductCategoryID'];
        $TopRow['AbStatus'] = $dataRow['AbStatus'];
        $TopRow['UnitID'] = $dataRow['UnitID'];
        $TopRow['Glaze'] = $dataRow['Glaze'];
        $TopRow['GlazePerc'] = $dataRow['GlazePerc'];
        $TopRow['ProductionDate'] = $dataRow['ProductionDate'];
        $TopRow['FreezingType'] = $dataRow['FreezingType'];
        $TopRow['Brand'] = $dataRow['BrandName'];
        $TopRow['BrandID'] = $dataRow['BrandID'];
        $TopRow['PackingStyleID'] = $PackStyleId;
        $TopRow['PackingStyle'] = $dataRow['PackingStyle'];

        $Glaze = $dataRow['GlazePerc'];

        $AryGradeValues = array();
        $ProductMcs = 0;
        $ProductLooseBags = 0;
        $ProductTotalKgs = 0;
        $ProductSlabs = 0;

        /*
         * Convert:
         *   "123"
         *   "123,124,125"
         *
         * into:
         *   '123'
         *   '123','124','125'
         *
         * This is equivalent to the original IN ($StockIDs)
         * while safely supporting string StockIDs too.
         */
        $StockIdValues = array();

        if ($StockIDs !== '') {
            $StockIdParts = explode(',', $StockIDs);

            foreach ($StockIdParts as $StockIdPart) {
                $StockIdPart = trim($StockIdPart);

                if ($StockIdPart !== '') {
                    $StockIdValues[] = $this->conn->quote($StockIdPart);
                }
            }
        }

        $StockIdIn = implode(',', $StockIdValues);

        /*
         * Build Grade IN list once for this main row.
         */
        $GradeValues = array();

        foreach ($AryGardesNames as $GradeName) {
            $GradeValues[] = $this->conn->quote($GradeName);
        }

        $GradeIn = implode(',', $GradeValues);

        /*
         * Lookup arrays.
         *
         * Key = Grade
         *
         * This is the exact grouping dimension needed because
         * PackingStyleID is already fixed by this main result row.
         */
        $StockByGrade = array();
        $OutByGrade = array();
        $DayByGrade = array();

        /*
         * ==============================================================
         * 1. BULK CURRENT STOCK
         * ==============================================================
         */
        if ($StockIdIn !== '' && $GradeIn !== '') {

            $sqlBulkStock = "
                SELECT
                    CPID.Grade,
                    CPS.PackingStyle,
                    CPID.PackingStyleID,
                    CPID.PackingTypeID AS PackingType,
                    SUM(ActualMcs) AS MCS,
                    SUM(ActualLooseBags) AS LooseBags,
                    SUM(ActualMcs*TotalSlabs*SlabWeight*MFactor) AS KGS,
                    SUM(ActualLooseBags*SlabWeight*MFactor) AS LKGS,
                    ROUND((SUM(ActualLooseBags)/TotalSlabs),2) AS LooseMcs,
                    SUM(ActualLooseBags) % TotalSlabs AS RemainLooseBags,
                    SUM(ActualMcs*TotalSlabs) AS McsSlabs,
                    CPS.TotalSlabs,
                    SUM(CPID.ActualKgs) AS AvalKgs
                FROM `CsStockData` AS CPID
                LEFT JOIN AdminPackingStyles AS CPS
                    ON CPS.PCKID=CPID.PackingStyleID
                LEFT JOIN ScrmPIVarietyList as PIVL
                    ON CPID.VarietyID=PIVL.VarietyID
                WHERE CPID.StockID IN ($StockIdIn)
                  AND CPID.PackingStyleID='$PackStyleId'
                  AND CPID.Grade IN ($GradeIn)
                GROUP BY CPID.Grade, CPID.PackingStyleID
            ";

            $stmtBulkStock = $this->conn->prepare($sqlBulkStock);
            $stmtBulkStock->execute();

            while ($row = $stmtBulkStock->fetch(PDO::FETCH_ASSOC)) {
                $StockByGrade[$row['Grade']] = $row;
            }

            /*
             * ==========================================================
             * 2. BULK STOCK OUT
             * ==========================================================
             */
            $sqlBulkOut = "
                SELECT
                    CPID.Grade,
                    CPS.PackingStyle,
                    CPID.PackingStyleID,
                    CPID.PackingTypeID AS PackingType,
                    SUM(OutMcs) AS MCS,
                    SUM(OutLooseBags) AS LooseBags,
                    SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS KGS,
                    SUM(OutLooseBags*SlabWeight*MFactor) AS LKGS,
                    ROUND((SUM(OutLooseBags)/TotalSlabs),2) AS LooseMcs,
                    SUM(OutLooseBags) % TotalSlabs AS RemainLooseBags,
                    SUM(OutMcs*TotalSlabs) AS McsSlabs,
                    CPS.TotalSlabs,
                    SUM(CSOD.ActualOutKgs) AS AvalKgs
                FROM `CsStockOutDetails` AS CSOD
                LEFT JOIN `CsStockData` AS CPID
                    ON CSOD.StockID=CPID.StockID
                LEFT JOIN AdminPackingStyles AS CPS
                    ON CPS.PCKID=CPID.PackingStyleID
                LEFT JOIN ScrmPIVarietyList as PIVL
                    ON CPID.VarietyID=PIVL.VarietyID
                WHERE CPID.StockID IN ($StockIdIn)
                  AND CPID.PackingStyleID='$PackStyleId'
                  AND CPID.Grade IN ($GradeIn)
                  AND CSOD.OutDate<='$StockDate'
                GROUP BY CPID.Grade, CPID.PackingStyleID
            ";

            $stmtBulkOut = $this->conn->prepare($sqlBulkOut);
            $stmtBulkOut->execute();

            while ($row = $stmtBulkOut->fetch(PDO::FETCH_ASSOC)) {
                $OutByGrade[$row['Grade']] = $row;
            }

            /*
             * ==========================================================
             * 3. BULK DAY STOCK
             * ==========================================================
             */
            $sqlBulkDay = "
                SELECT
                    CPID.Grade,
                    CPS.PackingStyle,
                    CPID.PackingStyleID,
                    CPID.PackingTypeID AS PackingType,
                    SUM(ActualMcs) AS MCS,
                    SUM(ActualLooseBags) AS LooseBags,
                    SUM(ActualMcs*TotalSlabs*SlabWeight*MFactor) AS KGS,
                    SUM(ActualLooseBags*SlabWeight*MFactor) AS LKGS,
                    ROUND((SUM(ActualLooseBags)/TotalSlabs),2) AS LooseMcs,
                    SUM(ActualLooseBags) % TotalSlabs AS RemainLooseBags,
                    SUM(ActualMcs*TotalSlabs) AS McsSlabs,
                    CPS.TotalSlabs,
                    SUM(CPID.ActualKgs) AS AvalKgs
                FROM `CsStockData` AS CPID
                LEFT JOIN AdminPackingStyles AS CPS
                    ON CPS.PCKID=CPID.PackingStyleID
                LEFT JOIN ScrmPIVarietyList as PIVL
                    ON CPID.VarietyID=PIVL.VarietyID
                WHERE CPID.StockID IN ($StockIdIn)
                  AND CPID.PackingStyleID='$PackStyleId'
                  AND CPID.Grade IN ($GradeIn)
                  $DayQuery
                GROUP BY CPID.Grade, CPID.PackingStyleID
            ";

            $stmtBulkDay = $this->conn->prepare($sqlBulkDay);
            $stmtBulkDay->execute();

            while ($row = $stmtBulkDay->fetch(PDO::FETCH_ASSOC)) {
                $DayByGrade[$row['Grade']] = $row;
            }
        }

        /*
         * ==============================================================
         * NOW BUILD EACH GRADE
         *
         * No SQL inside this loop.
         * ==============================================================
         */
        foreach ($AryGardesNames as $Grades) {

            /*
             * If a grade has no stock row, use NULL values just like
             * the original aggregate query would return.
             */
            if (isset($StockByGrade[$Grades])) {
                $dataFormMCS = $StockByGrade[$Grades];
            } else {
                $dataFormMCS = array(
                    'KGS' => null,
                    'LKGS' => null,
                    'AvalKgs' => null,
                    'MCS' => null,
                    'LooseBags' => null,
                    'McsSlabs' => null,
                    'TotalSlabs' => null
                );
            }

            $KGS = $dataFormMCS['KGS'];
            $LKGS = $dataFormMCS['LKGS'];
            $AvalKgs = $dataFormMCS['AvalKgs'];
            $MCS = $dataFormMCS['MCS'];
            $LOOSE = $dataFormMCS['LooseBags'];
            $Slabs = $dataFormMCS['McsSlabs'] + $dataFormMCS['LooseBags'];
            $TotalSlabs = $dataFormMCS['TotalSlabs'];


            $AvalKgs = $KGS + $LKGS;

            $MCS = $dataFormMCS['MCS'];
            $MCS = $MCS ? $MCS : '0';

            /*
             * Stock Out
             */
            if (isset($OutByGrade[$Grades])) {
                $dataOutFormMCS = $OutByGrade[$Grades];
            } else {
                $dataOutFormMCS = array(
                    'KGS' => null,
                    'LKGS' => null,
                    'AvalKgs' => null,
                    'MCS' => null,
                    'LooseBags' => null,
                    'McsSlabs' => null,
                    'TotalSlabs' => null
                );
            }

            $outKGS = $dataOutFormMCS['KGS'];
            $outLKGS = $dataOutFormMCS['LKGS'];

            $MCS -= $dataOutFormMCS['MCS'];
            $LOOSE -= $dataOutFormMCS['LooseBags'];
            $Slabs -= $dataOutFormMCS['McsSlabs'] + $dataOutFormMCS['LooseBags'];

            $AvalKgs -= ($outKGS + $outLKGS);
            $MCS = $MCS ? $MCS : '0';

            if ($OutputType == 'MCS') {

                if ($ConvertLooseToMcs == 1) {
                    $LooseMcs = ROUND(($LOOSE / $TotalSlabs), 2);
                    $LOOSE = $LOOSE - ($LooseMcs * $TotalSlabs);
                    $MCS = $MCS + $LooseMcs;
                }

                $GradeValue = isset($MCS) ? $MCS : 0;

            } else if ($OutputType == 'KGS') {

                $GradeValue = isset($AvalKgs) ? round(($AvalKgs), 2) : 0;

            } else if ($OutputType == 'Slabs') {

                $GradeValue = isset($Slabs) ? $Slabs : 0;
            }

            /*
             * Day data
             */
            if (isset($DayByGrade[$Grades])) {
                $dataFormMCSDay = $DayByGrade[$Grades];
            } else {
                $dataFormMCSDay = array(
                    'KGS' => null,
                    'LKGS' => null,
                    'AvalKgs' => null,
                    'MCS' => null,
                    'LooseBags' => null,
                    'McsSlabs' => null,
                    'TotalSlabs' => null
                );
            }

            $KGSDay = $dataFormMCSDay['KGS'];
            $LKGSDay = $dataFormMCSDay['LKGS'];
            $AvalKgsDay = $dataFormMCSDay['AvalKgs'];
            $MCSDay = $dataFormMCSDay['MCS'];
            $LOOSEDay = $dataFormMCSDay['LooseBags'];
            $SlabsDay = $dataFormMCSDay['McsSlabs'] + $dataFormMCSDay['LooseBags'];
            $TotalSlabsDay = $dataFormMCSDay['TotalSlabs'];

            $MCSDay = $dataFormMCSDay['MCS'];
            $MCSDay = $MCSDay ? $MCSDay : '0';

            if ($OutputType == 'MCS') {

                if ($ConvertLooseToMcs == 1) {
                    $LooseMcsDay = ROUND(($LOOSEDay / $TotalSlabsDay), 2);
                    $LOOSEDay = $LOOSEDay - ($LooseMcsDay * $TotalSlabsDay);
                    $MCSDay = $MCSDay + $LooseMcsDay;
                }

                $GradeValueDay = isset($MCSDay) ? $MCSDay : 0;

            } else if ($OutputType == 'KGS') {

                $GradeValueDay = isset($AvalKgsDay) ? round(($AvalKgsDay), 2) : 0;

            } else if ($OutputType == 'Slabs') {

                $GradeValueDay = isset($SlabsDay) ? $SlabsDay : 0;
            }

            $gradeItem = array();
            $gradeItem['GradeName'] = $Grades;

            if ($GradeValue == 0) {
                $gradeItem['GradeValue'] = "-";
            } else {
                if ($GradeValueDay > 0) {
                    $gradeItem['GradeValue'] = $GradeValue . "( P- " . $GradeValueDay . ")";
                } else {
                    $gradeItem['GradeValue'] = $GradeValue;
                }
            }

            $gradeItem['GradeMcs'] = $MCS;
            $gradeItem['GradeLooseBags'] = $LOOSE;
            $gradeItem['GradeSlabs'] = $Slabs;
            $gradeItem['GradeMcsDay'] = $MCSDay;
            $gradeItem['GradeLooseBagsDay'] = $LOOSEDay;


            if($Glaze > 0 && $AvalKgs > 0){
                $AvalKgs = $AvalKgs - ($AvalKgs * ($Galze/100));
            }

            if($Glaze > 0 && $AvalKgsDay > 0){
                $AvalKgsDay = $AvalKgsDay - ($AvalKgsDay * ($Galze/100));
            }

            $gradeItem['GradeKgs'] = $AvalKgs;
            $gradeItem['GradeKgsDay'] = $AvalKgsDay;
            $gradeItem['GradeSlabsDay'] = $SlabsDay;

            $AryGradeValues[] = $gradeItem;

            $ProductMcs += $MCS;
            $ProductLooseBags += $LOOSE;
            $ProductTotalKgs += ($AvalKgs);
            $ProductSlabs += $Slabs;
        }

        $TopRow['SubGrades'] = $AryGradeValues;
        $TopRow['ProductMcs'] = $ProductMcs;
        $TopRow['ProductLooseBags'] = $ProductLooseBags;
        $TopRow['ProductTotalKgs'] = round($ProductTotalKgs, 2);
        $TopRow['ProductSlabs'] = $ProductSlabs;

        $ArrayStoreData[] = $TopRow;
    }

    /*
     * Grade totals
     */
    $ArrayStockTotals = array();

    foreach ($AryGardesNames as $refGrade) {

        $GradeWeight = 0;
        $GradeMcs = 0;
        $GradeLooseBags = 0;
        $GradeSlabs = 0;

        $GradeWeightDay = 0;
        $GradeMcsDay = 0;
        $GradeLooseBagsDay = 0;
        $GradeSlabsDay = 0;

        foreach ($ArrayStoreData as $Item) {
            foreach ($Item['SubGrades'] as $SubItem) {

                if ($SubItem['GradeName'] == $refGrade) {
                    $GradeWeight += $SubItem['GradeKgs'];
                    $GradeMcs += $SubItem['GradeMcs'];
                    $GradeLooseBags += $SubItem['GradeLooseBags'];
                    $GradeSlabs += $SubItem['GradeSlabs'];
                    $GradeWeightDay += $SubItem['GradeKgsDay'];
                    $GradeMcsDay += $SubItem['GradeMcsDay'];
                    $GradeLooseBagsDay += $SubItem['GradeLooseBagsDay'];
                    $GradeSlabsDay += $SubItem['GradeSlabsDay'];
                }
            }
        }

        if ($OutputType == 'MCS') {
            $GradeValue = $GradeMcs;
        } else if ($OutputType == 'KGS') {
            $GradeValue = round(($GradeWeight), 2);
        } else if ($OutputType == 'Slabs') {
            $GradeValue = $GradeSlabs;
        }

        if ($GradeValue == "/") {
            $GradeValue = "";
        }

        if ($OutputType == 'MCS') {
            $GradeValueDay = $GradeMcsDay;
        } else if ($OutputType == 'KGS') {
            $GradeValueDay = round(($GradeWeightDay), 2);
        } else if ($OutputType == 'Slabs') {
            $GradeValueDay = $GradeSlabsDay;
        }

        if ($GradeValueDay == "/") {
            $GradeValueDay = "";
        }

        $totalgradeItem = array();
        $totalgradeItem['GradeName'] = $refGrade;

        if ($GradeValueDay > 0) {
            $totalgradeItem['GradeValue'] = $GradeValue . "( P- " . $GradeValueDay . ")";
        } else {
            $totalgradeItem['GradeValue'] = $GradeValue;
        }

        $ArrayStockTotals[] = $totalgradeItem;
    }

    /*
     * Product totals.
     *
     */
    foreach ($ArrayStoreData as $Item) {
        foreach ($Item['SubGrades'] as $SubItem) {

            $TotalWeight += $SubItem['GradeKgs'];
            $TotalMcs += $SubItem['GradeMcs'];
            $TotalLooseBags += $SubItem['GradeLooseBags'];
            $TotalLSlabs += $SubItem['GradeSlabs'];

            $TotalWeightDay += $SubItem['GradeKgsDay'];
            $TotalMcsDay += $SubItem['GradeMcsDay'];
            $TotalLooseBagsDay += $SubItem['GradeLooseBagsDay'];
            $TotalLSlabsDay += $SubItem['GradeSlabsDay'];
        }

        $StockTotals['TotalWeight'] = round($TotalWeight, 2) . "(" . round($TotalWeightDay, 2) . ")";
        $StockTotals['TotalMcs'] = $TotalMcs . "(" . $TotalMcsDay . ")";
        $StockTotals['TotalLooseBags'] = $TotalLooseBags . "(" . $TotalLooseBagsDay . ")";
        $StockTotals['TotalLSlabs'] = $TotalLSlabs . "(" . $TotalLSlabsDay . ")";
    }

    $data['Error'] = null;
    $data['Msg'] = 'Success';
    $data['TotalCount'] = $TotalCount;
    $data['Grades'] = $AryGardesNames;
    $data['Data'] = $ArrayStoreData;
    $data['StockTotal'] = $ArrayStockTotals;
    $data['ProductTotal'] = $StockTotals;

    return $data;
}




    public function getGradeNames($Query)
    {

        $sqlGrades = "SELECT DISTINCT CPID.Grade as GradeName  FROM `CsStockData` as CPID
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN CsStoresRackData as CSRD on CSRD.RowID=CPID.StoreRackID
        LEFT JOIN CsStoresBlockData AS CSBD on CSBD.BlockID=CSRD.BlockID
        WHERE 1=1  $Query order by abs(CPID.Grade) asc";
        $stmtGrades = $this->conn->prepare($sqlGrades);
        $stmtGrades->execute();

        $Data = array();
        while ($dataGrade = $stmtGrades->fetch(PDO::FETCH_ASSOC)) {
            $GradeName = $dataGrade['GradeName'];
            $Data[] = $GradeName;
        }
        return $Data;
    }

    public function getPackingStyle($VarietyID)
    {
        $sqlPackingStyle = "SELECT DISTINCT PackingStyleID,PackingStyle FROM `CsStockData` AS CPID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPID.PackingStyleID = CPS.PCKID 
        WHERE VarietyID in ($VarietyID) ORDER BY ABS(PackingStyle) ASC";

        $stmtPackingStyle = $this->conn->prepare($sqlPackingStyle);
        $stmtPackingStyle->execute();

        while ($dataRow = $stmtPackingStyle->fetch(PDO::FETCH_ASSOC)) {
            $Data['PackingStyleID'] = $dataRow['PackingStyleID'];
            $Data['PackingStyle'] = $dataRow['PackingStyle'];
        }
        return $Data;
    }
}
