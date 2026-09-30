<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class HoToFinishedReportController
{
    private $conn;
    private $auditLogger;
    private $utilController;
    private $Error = null;
    private $Msg = 'Success';
    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Production');
    }

    public function getHoToFinishedReportDetails($RequestBody)
    {
        // error_log("REQUEST BODYHOTO: " . print_r($RequestBody, true));

        $DataType = $RequestBody['DataType'];
        $ArrivalID = '';
        if (isset($RequestBody['ArrivalID'])) {
            $ArrivalID = $RequestBody['ArrivalID'];
        }
        // $PlantID = $RequestBody['PlantID'];

        $Data['Error'] = $this->Error;
        $Data['Msg'] = "Success";

        if ($DataType == "getArrivals") {
            $Data['ArrivalsList'] = $this->getArrivals($RequestBody);
        } else if ($DataType == "getArrivalsByFY") {
            $Data['ArrivalsList'] = $this->getArrivalsByFinancialYear($RequestBody);

        } else if ($DataType == "getReportData") {
            if ($ArrivalID != '') {
                $Data['HoToFinishedReportData'] = $this->getHoToFinishedReportData($RequestBody);

            } else {
                $Data['Msg'] = "ArrivalID not found";
            }

        }


        return $Data;
    }

    function getArrivalsByFinancialYear($RequestBody)
    {
        //  error_log(print_r($RequestBody, true));
        // echo print_r($RequestBody);
        // exit;
        $fromDate = $RequestBody['FromDate'];
        $toDate = $RequestBody['ToDate'];
        $PlantID = $RequestBody['PlantID'];
        if (!$fromDate && !$toDate) {
            return false;
        }
        $Query = "";
        if ($fromDate) {
            $Query .= " AND ArrivalDate >= '$fromDate'";
        }
        if ($toDate) {
            $Query .= " AND ArrivalDate <= '$toDate'";
        }
        if ($PlantID != '') {
            $Query .= " AND ProcessingStationID = '$PlantID'";
        }



        $SqlArrivalsList = "SELECT RmArrivalID, ArrivalNumber,ArrivalDate
                            FROM RmArrivalDetatils
                            WHERE 1
                            $Query
                            ORDER BY ArrivalDate DESC";

        // error_log(print_r($SqlArrivalsList, true));
        //  echo print_r($SqlArrivalsList);exit;

        $stmt = $this->conn->prepare($SqlArrivalsList);
        $stmt->execute();
        $ArrayArrivalList = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayArrivalList[] = $row;
        }

        return $ArrayArrivalList;
    }


    function getArrivals($RequestBody)
    {
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $DataType = $RequestBody['DataType'];
        $PlantID = $RequestBody['PlantID'];

        $Query = "";
        if ($PlantID != '') {
            $Query = " AND ProcessingStationID='$PlantID'";
        }

        $SqlArrivalsList = "SELECT RmArrivalID,ArrivalNumber  FROM `RmArrivalDetatils`  WHERE ArrivalDate='$ArrivalDate' $Query";
        $stmtArrivalsList = $this->conn->prepare($SqlArrivalsList);


        $stmtArrivalsList->execute();
        //   echo print_r($SqlArrivalsList);exit;
        $ArrayArrivalList = [];

        while ($dataRowArrivalsList = $stmtArrivalsList->fetch(PDO::FETCH_ASSOC)) {
            $ArrayArrivalList[] = $dataRowArrivalsList;

        }
        return $ArrayArrivalList;

    }

    function getHoToFinishedReportData($RequestBody)
    {
        $ArrivalID = $RequestBody['ArrivalID'];
        $MergeHonLoose = $RequestBody['mergeHonLoose'];
        //    $GradesData= $this->getGrades();
        $sqlIsHON = "SELECT `HONPacking` FROM `RmReceivingBreakup` WHERE `ArrivalID`=$ArrivalID";
        $stmtIsHON = $this->conn->prepare($sqlIsHON);
        $stmtIsHON->execute();
        $HONPacking = $stmtIsHON->fetch(PDO::FETCH_ASSOC)['HONPacking'];
        $totalProdWeight = $ArrivalWeight = 0;
        if ($HONPacking == 0) {
            $sqlSelect = "DROP TABLE IF EXISTS `HeadonToFinishedReportTemp`";
            $stmtSelect = $this->conn->prepare($sqlSelect);
            $stmtSelect->execute();

            $sqlCreate = "CREATE TABLE HeadonToFinishedReportTemp (
                ID INT AUTO_INCREMENT PRIMARY KEY,
                GradingBreakUpID VARCHAR(50),
                Grade VARCHAR(50),
                HlCount VARCHAR(100),
                GradingVariety VARCHAR(100),
                HlQuantity VARCHAR(30),
                VaQuantity VARCHAR(30),
                SoakingQuantity VARCHAR(30),
                HlToVaYieldPercentage VARCHAR(30),
                PickupPercentage VARCHAR(30),
                FreezingVariety VARCHAR(100),
                Packing VARCHAR(100),

                ProductionQuantity VARCHAR(30),
                RejectionQuantity VARCHAR(30),

                FinishedQuantity VARCHAR(30)
            )";

            //  error_log("REQUEST BODYHOTO: " . print_r( $sqlCreate, true));
            $stmtCreate = $this->conn->prepare($sqlCreate);
            $stmtCreate->execute();



            //         $SqlInsert = "INSERT INTO HeadonToFinishedReportTemp (
            //     GradingBreakUpID,
            //     HlCount,
            //     GradingVariety,
            //     HlQuantity,
            //     VaQuantity,
            //     SoakingQuantity,
            //     PickupPercentage,
            //     FinishedQuantity,
            //     HlToVaYieldPercentage,
            //     FreezingVariety,
            //     Packing,
            //     Grade
            // )
            // SELECT
            //     Sub.DataIDs AS GradingBreakUpID,
            //     Sub.HLCounts AS HlCount,
            //     Sub.Variety AS GradingVariety,
            //     Sub.TotalHLWeight AS HlQuantity,
            //     Sub.ValueAdditionWeight AS VaQuantity,
            //     Sub.SoakInQty AS SoakingQuantity,

            //     -- Pickup Percentage
            //     ROUND(
            //         CASE
            //             WHEN Sub.SoakInQty > 0 THEN ((Sub.ProductionWeight - Sub.SoakInQty) / Sub.ProductionWeight) * 100
            //             ELSE 0
            //         END, 2
            //     ) AS PickupPercentage,

            //     Sub.ProductionWeight AS FinishedQuantity,

            //     -- HL to VA Yield Percentage
            //     ROUND(
            //         CASE
            //             WHEN Sub.TotalHLWeight > 0 THEN (Sub.ValueAdditionWeight / Sub.TotalHLWeight) * 100
            //             ELSE 0
            //         END, 2
            //     ) AS HlToVaYieldPercentage,

            //     Sub.FreezingVariety,
            //     Sub.Packing,
            //     Sub.PackingGrade AS Grade

            // FROM (
            //     SELECT
            //          GROUP_CONCAT(distinct PHGB.PackingGrade) AS PackingGrade,
            //         GROUP_CONCAT(distinct PHGB.DataID) AS DataIDs,
            //         GROUP_CONCAT(distinct PHGB.HLCount) AS HLCounts,
            //         GROUP_CONCAT(PHGB.HLWeight) AS TotalHLWeight,
            //         GROUP_CONCAT(DISTINCT PVA.ShortName) AS Variety,

            //         -- Value Addition Weight
            //         COALESCE((
            //             SELECT SUM(WtAftrVA)
            //             FROM PpcValueAddition
            //             WHERE FIND_IN_SET(GradingBreakupID, GROUP_CONCAT(PHGB.DataID))
            //         ), 0) AS ValueAdditionWeight,

            //         -- Soak In Quantity
            //         COALESCE((
            //             SELECT SUM(SoakInQty)
            //             FROM ProdSoakingDetails
            //             WHERE FIND_IN_SET(VABreakupID, GROUP_CONCAT(PHGB.DataID))
            //         ), 0) AS SoakInQty,

            //         -- Production Weight
            //         COALESCE((
            //             SELECT SUM(ProductionWeight+RejectionWeight)
            //             FROM ProdFreezingData
            //             WHERE FIND_IN_SET(GradingBreakupID, GROUP_CONCAT(PHGB.DataID))
            //         ), 0) AS ProductionWeight,

            //         -- Freezing Variety
            //         COALESCE((
            //             SELECT GROUP_CONCAT(DISTINCT SPVL.ShortName)
            //             FROM ProdFreezingData PFD
            //             LEFT JOIN ScrmPIVarietyList SPVL ON SPVL.VarietyID = PFD.VarietyID
            //             WHERE FIND_IN_SET(PFD.GradingBreakupID, GROUP_CONCAT(PHGB.DataID))
            //         ), '') AS FreezingVariety,

            //         -- Packing Style
            //         COALESCE((
            //             SELECT GROUP_CONCAT(DISTINCT CONCAT(APS.PackingStyle, ' (', ROUND(((PD.WtAfterDH / PD.HonWeight) * 100), 1), ')'))
            //             FROM ProdFreezingData PFD
            //             LEFT JOIN AdminPackingStyles APS ON APS.PCKID = PFD.PackingStyleID
            //             LEFT JOIN PpcHLGradingBreakup PGB ON PGB.DataID=PFD.GradingBreakupID
            //             LEFT JOIN PpcDeheading PD ON PGB.LotID=PD.LotID
            //             WHERE FIND_IN_SET(PFD.GradingBreakupID, GROUP_CONCAT(PHGB.DataID))
            //         ), '') AS Packing

            //       FROM  PpcHLGradingBreakup PHGB
            // left join PpcHLGradingDetails PHGD  on PHGB.MixedID=PHGD.MixIDs
            //     LEFT JOIN PpcHLVAVarietyList PVA ON PVA.VarietyID = PHGB.VarietyID
            //     LEFT JOIN RmLotDetatils RLD ON RLD.RmLotID = PHGD.LotID
            //     WHERE RLD.RmReferenceID IN ($ArrivalID) AND PHGB.HLWeight != 0
            //     GROUP BY PHGB.PackingGrade,PVA.ShortName order by abs(PHGB.PackingGrade) asc
            // ) AS Sub";


            $SqlInsert = "INSERT INTO HeadonToFinishedReportTemp (
                GradingBreakUpID,
                HlCount,
                GradingVariety,
                HlQuantity,
                VaQuantity,
                SoakingQuantity,
                PickupPercentage,
                ProductionQuantity,
                RejectionQuantity,
                FinishedQuantity,
                HlToVaYieldPercentage,
                FreezingVariety,
                Packing,
                Grade
            )
            SELECT
                GROUP_CONCAT(DISTINCT PHGB.DataID ORDER BY PHGB.DataID) AS GradingBreakUpID,
                GROUP_CONCAT(DISTINCT PHGB.HLCount ORDER BY PHGB.DataID) AS HlCount,
                GROUP_CONCAT(DISTINCT PVA.ShortName ORDER BY PHGB.DataID) AS GradingVariety,
                GROUP_CONCAT(DISTINCT PHGB.HLWeight ORDER BY PHGB.DataID) AS HlQuantity,

                GROUP_CONCAT(DISTINCT COALESCE(VA.TotalVA, 0) ORDER BY PHGB.DataID) AS VaQuantity,
                GROUP_CONCAT(DISTINCT COALESCE(SD.TotalSoak, 0) ORDER BY PHGB.DataID) AS SoakingQuantity,
                GROUP_CONCAT(DISTINCT COALESCE(ROUND(
                    CASE
                        WHEN FD.TotalFinished > 0 AND SD.TotalSoak IS NOT NULL THEN
                            ((FD.TotalFinished - SD.TotalSoak) / FD.TotalFinished) * 100
                        ELSE 0
                    END, 2), 0) ORDER BY PHGB.DataID) AS PickupPercentage,

                GROUP_CONCAT(DISTINCT COALESCE(FD.ProductionQuantity, 0) ORDER BY PHGB.DataID) AS ProductionQuantity,
                GROUP_CONCAT(DISTINCT COALESCE(FD.RejectionQuantity, 0) ORDER BY PHGB.DataID) AS RejectionQuantity,

                GROUP_CONCAT(DISTINCT COALESCE(FD.TotalFinished, 0) ORDER BY PHGB.DataID) AS FinishedQuantity,
                GROUP_CONCAT(DISTINCT ROUND(
                    CASE
                        WHEN PHGB.HLWeight > 0 THEN (COALESCE(VA.TotalVA, 0) / PHGB.HLWeight) * 100
                        ELSE 0
                    END, 2) ORDER BY PHGB.DataID) AS HlToVaYieldPercentage,

                GROUP_CONCAT(DISTINCT FV.Varieties ORDER BY PHGB.DataID) AS FreezingVariety,
                GROUP_CONCAT(DISTINCT PK.Packings ORDER BY PHGB.DataID) AS Packing,
                GROUP_CONCAT(DISTINCT PHGB.PackingGrade) AS Grade

            FROM PpcHLGradingBreakup PHGB
            LEFT JOIN PpcHLGradingDetails PHGD ON PHGB.MixedID = PHGD.MixIDs
            LEFT JOIN PpcHLVAVarietyList PVA ON PVA.VarietyID = PHGB.VarietyID
            LEFT JOIN RmLotDetatils RLD ON RLD.RmLotID = PHGD.LotID

            -- Value Addition total
                LEFT JOIN (
                    SELECT GradingBreakupID, SUM(WtAftrVA) AS TotalVA
                    FROM PpcValueAddition
                    GROUP BY GradingBreakupID
                ) VA ON VA.GradingBreakupID = PHGB.DataID

                -- Soaking total
                LEFT JOIN (
                    SELECT VABreakupID, SUM(SoakInQty) AS TotalSoak
                    FROM ProdSoakingDetails
                    GROUP BY VABreakupID
                ) SD ON SD.VABreakupID = PHGB.DataID

                -- Freezing finished qty
                LEFT JOIN (
                    SELECT GradingBreakupID, SUM(ProductionWeight + RejectionWeight) AS TotalFinished,
                    SUM(ProductionWeight) AS ProductionQuantity,
                    SUM(RejectionWeight) AS RejectionQuantity
                    FROM ProdFreezingData
                    GROUP BY GradingBreakupID
                ) FD ON FD.GradingBreakupID = PHGB.DataID

                -- Freezing variety
                LEFT JOIN (
                    SELECT PFD.GradingBreakupID, GROUP_CONCAT(SPVL.ShortName) AS Varieties
                    FROM ProdFreezingData PFD
                    LEFT JOIN ScrmPIVarietyList SPVL ON SPVL.VarietyID = PFD.VarietyID
                    GROUP BY PFD.GradingBreakupID
                ) FV ON FV.GradingBreakupID = PHGB.DataID

                -- Packing info
                LEFT JOIN (
                    SELECT
                        PFD.GradingBreakupID,
                        GROUP_CONCAT(APS.PackingStyle) AS Packings
                    FROM ProdFreezingData PFD
                    LEFT JOIN AdminPackingStyles APS ON APS.PCKID = PFD.PackingStyleID
                    LEFT JOIN PpcHLGradingBreakup PGB2 ON PGB2.DataID = PFD.GradingBreakupID
                    LEFT JOIN PpcDeheading PD ON PGB2.LotID = PD.LotID
                    GROUP BY PFD.GradingBreakupID
            ) PK ON PK.GradingBreakupID = PHGB.DataID

            WHERE
                RLD.RmReferenceID IN ($ArrivalID)
                AND PHGB.HLWeight != 0

            GROUP BY PHGB.DataID,PHGB.PackingGrade
            ORDER BY ABS(PHGB.PackingGrade)";
            //ECHO '<pre>';print_r($SqlInsert);die;

            //error_log("REQUEST BODYHOTO: " . print_r($SqlInsert, true));

            // echo $SqlInsert; exit;
            $stmtInsert = $this->conn->prepare($SqlInsert);
            $stmtInsert->execute();

           /* $this->auditLogger->logInsert('HeadonToFinishedReportTemp', null, null, $RequestBody, [
                'ReferenceNo' => $ArrivalID,
                'Remarks' => 'Head-on to finished report generated',
            ]);*/


            $SqlInsertTotals = "
            DROP TEMPORARY TABLE IF EXISTS TempNormalizedData;

            CREATE TEMPORARY TABLE TempNormalizedData (
                HlQuantity DECIMAL(10,2),
                VaQuantity DECIMAL(10,2),
                SoakingQuantity DECIMAL(10,2),
                PickupPercentage DECIMAL(10,2),

                ProductionQuantity DECIMAL(10,2),
                RejectionQuantity DECIMAL(10,2),

                FinishedQuantity DECIMAL(10,2),
                HlToVaYieldPercentage DECIMAL(10,2),
                GradingBreakUpID VARCHAR(100),
                FreezingVariety VARCHAR(100)
            );


            INSERT INTO TempNormalizedData (
                HlQuantity, VaQuantity, SoakingQuantity, PickupPercentage,

                ProductionQuantity,
                RejectionQuantity,

                FinishedQuantity, HlToVaYieldPercentage,
                GradingBreakUpID, FreezingVariety
            )
            SELECT
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(HlQuantity, ',', numbers.n), ',', -1) AS DECIMAL(10,2)),
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(VaQuantity, ',', numbers.n), ',', -1) AS DECIMAL(10,2)),
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(SoakingQuantity, ',', numbers.n), ',', -1) AS DECIMAL(10,2)),
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(PickupPercentage, ',', numbers.n), ',', -1) AS DECIMAL(10,2)),

                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(ProductionQuantity, ',', numbers.n), ',', -1) AS DECIMAL(10,2)),
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(RejectionQuantity, ',', numbers.n), ',', -1) AS DECIMAL(10,2)),

                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(FinishedQuantity, ',', numbers.n), ',', -1) AS DECIMAL(10,2)),
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(HlToVaYieldPercentage, ',', numbers.n), ',', -1) AS DECIMAL(10,2)),
                GradingBreakUpID,
                FreezingVariety
            FROM HeadonToFinishedReportTemp
            JOIN (
                SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
            ) numbers ON CHAR_LENGTH(HlQuantity) - CHAR_LENGTH(REPLACE(HlQuantity, ',', '')) >= numbers.n - 1
            WHERE GradingBreakUpID NOT LIKE 'TOTAL%';

            INSERT INTO HeadonToFinishedReportTemp (
                GradingBreakUpID,
                HlCount,
                GradingVariety,
                HlQuantity,
                VaQuantity,
                SoakingQuantity,
                PickupPercentage,
                FinishedQuantity,
                ProductionQuantity,
                HlToVaYieldPercentage,
                FreezingVariety,
                Packing,
                Grade
            )
            SELECT
                'TOTAL',
                NULL,
                NULL,
                SUM(HlQuantity),
                SUM(VaQuantity),
                SUM(SoakingQuantity),
                SUM(PickupPercentage),
                SUM(FinishedQuantity),
                SUM(FinishedQuantity),
                ROUND(AVG(NULLIF(HlToVaYieldPercentage, 0)), 2),
                NULL,
                NULL,
                'TOTALS'
            FROM TempNormalizedData;
            ";


            // Enable multi-statement execution
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);

            // Use exec() instead of prepare/execute for multiple statements
            $this->conn->exec($SqlInsertTotals);

            $SqlHeadonToFinishedReportData = "SELECT * FROM `HeadonToFinishedReportTemp`";
            $stmtHeadonToFinishedReportData = $this->conn->prepare($SqlHeadonToFinishedReportData);
            $stmtHeadonToFinishedReportData->execute();
            //error_log("REQUEST BODYHOTO: " . print_r($stmtHeadonToFinishedReportData, true));

            // $ArrayHeadonToFinishedReportData = [];
            while ($dataRowHeadonToFinishedReportData = $stmtHeadonToFinishedReportData->fetch(PDO::FETCH_ASSOC)) {
                if ($dataRowHeadonToFinishedReportData['Packing'] != '' && count(explode(',', $dataRowHeadonToFinishedReportData['Packing'])) > 1) {
                    $dataIds = $dataRowHeadonToFinishedReportData['GradingBreakUpID'];
                    $FreezingQty = $ProductionQuantity = $RejectionQuantity = '';
                    $PackingStyle = '';
                    $Varieties = '';
                    $sqlFreezing = "  SELECT
                            SUM(ProductionWeight + RejectionWeight) AS PWeight,

                            SUM(ProductionWeight) AS ProductionQuantity,
                            SUM(RejectionWeight) AS RejectionQuantity,

                            GROUP_CONCAT(DISTINCT CONCAT(APS.PackingStyle, ' (', PFD.Grade, ')') ORDER BY PFD.ProductionID) AS PackingStyle,
                            GROUP_CONCAT(DISTINCT SPVL.ShortName ORDER BY PFD.ProductionID) AS Varieties,
                            PFD.Grade,
                            PFD.GradingBreakupID,
                            GROUP_CONCAT(NULLIF(TRIM(PFD.Remarks), '') SEPARATOR ', ') AS Remarks
                        FROM ProdFreezingData AS PFD
                        LEFT JOIN AdminPackingStyles APS ON APS.PCKID = PFD.PackingStyleID
                        LEFT JOIN ScrmPIVarietyList SPVL ON SPVL.VarietyID = PFD.VarietyID
                        LEFT JOIN PpcHLGradingBreakup PGB ON PGB.DataID = PFD.GradingBreakupID
                        WHERE PFD.GradingBreakupID IN ($dataIds)
                        GROUP BY
                        -- PFD.GradingBreakupID,PFD.PackingStyleID,PFD.Grade,PFD.VarietyID,PFD.TubNo
                        PFD.GradingBreakupID,PFD.PackingStyleID,PFD.Grade,PFD.VarietyID
                        ORDER BY PFD.GradingBreakupID
                    ";
                    //  error_log("REQUEST BODYHOTO: " . print_r( $sqlFreezing, true));
                    $stmtFreezing = $this->conn->prepare($sqlFreezing);
                    $stmtFreezing->execute();
                    // $ArrayHeadonToFinishedReportData = [];
                    $PrviousId = '';
                    $PrviousId1 = '';
                    $PrviousId2 = '';
                    $remarks = '';
                    while ($dataFreezing = $stmtFreezing->fetch(PDO::FETCH_ASSOC)) {
                        if ($PrviousId == '') {
                            $PrviousId = $dataFreezing['GradingBreakupID'];
                        }
                        if ($PrviousId1 == '') {
                            $PrviousId1 = $dataFreezing['GradingBreakupID'];
                        }
                        if ($PrviousId2 == '') {
                            $PrviousId2 = $dataFreezing['GradingBreakupID'];
                        }
                        if ($PackingStyle != '') {
                            if ($dataFreezing['GradingBreakupID'] == $PrviousId) {
                                $PackingStyle .= ',' . $dataFreezing['PackingStyle'];
                            } else {
                                $PackingStyle .= '&' . $dataFreezing['PackingStyle'];
                                $PrviousId = $dataFreezing['GradingBreakupID'];
                            }
                        } else {
                            $PackingStyle .= $dataFreezing['PackingStyle'];
                        }
                        if ($FreezingQty != '') {

                            if ($dataFreezing['GradingBreakupID'] == $PrviousId1) {
                                $FreezingQty .= ',' . $dataFreezing['PWeight'];
                            } else {
                                $FreezingQty .= '&' . $dataFreezing['PWeight'];
                                $PrviousId1 = $dataFreezing['GradingBreakupID'];
                            }
                        } else {
                            $FreezingQty .= $dataFreezing['PWeight'];
                        }

                        if ($ProductionQuantity != '') {

                            if ($dataFreezing['GradingBreakupID'] == $PrviousId1) {
                                $ProductionQuantity .= ',' . $dataFreezing['ProductionQuantity'];
                            } else {
                                $ProductionQuantity .= '&' . $dataFreezing['ProductionQuantity'];
                                $PrviousId1 = $dataFreezing['GradingBreakupID'];
                            }
                        } else {
                            $ProductionQuantity .= $dataFreezing['ProductionQuantity'];
                        }

                        if ($RejectionQuantity != '') {

                            if ($dataFreezing['GradingBreakupID'] == $PrviousId1) {
                                $RejectionQuantity .= ',' . $dataFreezing['RejectionQuantity'];
                            } else {
                                $RejectionQuantity .= '&' . $dataFreezing['RejectionQuantity'];
                                $PrviousId1 = $dataFreezing['GradingBreakupID'];
                            }
                        } else {
                            $RejectionQuantity .= $dataFreezing['RejectionQuantity'];
                        }

                        if ($Varieties != '') {
                            if ($dataFreezing['GradingBreakupID'] == $PrviousId2) {
                                $Varieties .= ',' . $dataFreezing['Varieties'];
                            } else {
                                $Varieties .= '&' . $dataFreezing['Varieties'];
                                $PrviousId2 = $dataFreezing['GradingBreakupID'];
                            }
                        } else {
                            $Varieties .= $dataFreezing['Varieties'];
                        }
                        $remarksStr = trim($dataFreezing['Remarks']);
                        if ($remarksStr != '' && $remarksStr != null) {
                            if ($remarks == '') {
                                $remarks .= $remarksStr;
                            } else {
                                $remarks .= ',' . $remarksStr;
                            }
                        }


                    }
                    $dataRowHeadonToFinishedReportData['Remarks'] = $remarks;
                    $dataRowHeadonToFinishedReportData['Packing'] = $PackingStyle;
                    $dataRowHeadonToFinishedReportData['FreezingVariety'] = $Varieties;
                    $dataRowHeadonToFinishedReportData['FinishedQuantity'] = $FreezingQty;

                    $dataRowHeadonToFinishedReportData['ProductionQuantity'] = $ProductionQuantity;
                    $dataRowHeadonToFinishedReportData['RejectionQuantity'] = $RejectionQuantity;

                } else {
                    if ($dataRowHeadonToFinishedReportData['Packing'] != '') {
                        $dataIds = $dataRowHeadonToFinishedReportData['GradingBreakUpID'];
                        $FreezingQty = '';
                        $PackingStyle = '';
                        $sqlFreezing = " SELECT (ProductionWeight + RejectionWeight) AS PWeight,
                        APS.PackingStyle,PFD.Grade,PD.WtAfterDH,PD.HonWeight,
                                                    GROUP_CONCAT(NULLIF(TRIM(PFD.Remarks), '') SEPARATOR ', ') AS Remarks
                            FROM ProdFreezingData AS PFD
                            LEFT JOIN AdminPackingStyles APS ON APS.PCKID = PFD.PackingStyleID
                            LEFT JOIN PpcHLGradingBreakup PGB ON PGB.DataID=PFD.GradingBreakupID
                            LEFT JOIN PpcDeheading PD ON PGB.LotID=PD.LotID
                            WHERE PFD.GradingBreakupID IN ($dataIds) GROUP BY PFD.PackingStyleID,PFD.Grade,PFD.VarietyID,PFD.TubNo";
                        $stmtFreezing = $this->conn->prepare($sqlFreezing);
                        $stmtFreezing->execute();
                        // $ArrayHeadonToFinishedReportData = [];
                        $dataFreezing = $stmtFreezing->fetch(PDO::FETCH_ASSOC);
                        $PackingStyle .= $dataFreezing['PackingStyle'] . ' (' . $dataFreezing['Grade'] . ')';
                        $dataRowHeadonToFinishedReportData['Packing'] = $PackingStyle;
                        $dataRowHeadonToFinishedReportData['Remarks'] = ($dataFreezing['Remarks'] != null) ? $dataFreezing['Remarks'] : '';


                    }
                }

                $ArrayHeadonToFinishedReportData[] = $dataRowHeadonToFinishedReportData;

            }
            $SqlMixlotIDS = "SELECT GROUP_CONCAT(distinct(MixIDs) ) as LotIDS FROM PpcHLGradingDetails PHGD  left join  `RmLotDetatils` RLD on RLD.RmLotID =PHGD.LotID
            WHERE RmReferenceID='$ArrivalID' ORDER BY `RmLotID` DESC";
            $stmtMixlotIDS = $this->conn->prepare($SqlMixlotIDS);
            $stmtMixlotIDS->execute();
            $dataRowMixlotIDS = $stmtMixlotIDS->fetch(PDO::FETCH_ASSOC);
            $MixedLotIDS = $dataRowMixlotIDS['LotIDS'];
        } else {
            $SqlHeadonToFinishedReportData = "SELECT RRB.ReceivingID,RRB.ReceivalBreakupID,
                                                RABD.Count AS ArrivalCount,RABD.Weight AS ArrivalWeight
                                                FROM RmArrivalBreakUpDetails AS RABD
                                                LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID = RABD.ArrivalID
                                                LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalBreakupID = RABD.ArrivalBrkUpID
                                                LEFT JOIN RmReceivingDetails AS RRD ON RRD.ReceivingID = RRB.ReceivingID
                                                WHERE RAD.`RmArrivalID`='$ArrivalID'";
            $stmtHeadonToFinishedReportData = $this->conn->prepare($SqlHeadonToFinishedReportData);
            $stmtHeadonToFinishedReportData->execute();
            while ($dataRowHeadonToFinishedReportData = $stmtHeadonToFinishedReportData->fetch(PDO::FETCH_ASSOC)) {
                $trackId = $dataRowHeadonToFinishedReportData['ReceivingID'];
                $tubNo = $dataRowHeadonToFinishedReportData['ReceivalBreakupID'];
                $ArrivalWeight += $dataRowHeadonToFinishedReportData['ArrivalWeight'];
                $FreezingQty = $ProductionQuantity = $RejectionQuantity = '';
                $PackingStyle = '';
                $Varieties = '';
                $sqlFreezing = "  SELECT
                        SUM(ProductionWeight + RejectionWeight) AS PWeight,
                        SUM(ProductionWeight) AS ProductionQuantity,
                        SUM(RejectionWeight) AS RejectionQuantity,
                        GROUP_CONCAT(DISTINCT CONCAT(APS.PackingStyle, ' (', PFD.Grade, ')') ORDER BY PFD.ProductionID) AS PackingStyle,
                        GROUP_CONCAT(DISTINCT SPVL.ShortName ORDER BY PFD.ProductionID) AS Varieties,
                        PFD.Grade,TubNo
                    FROM ProdFreezingData AS PFD
                    LEFT JOIN AdminPackingStyles APS ON APS.PCKID = PFD.PackingStyleID
                    LEFT JOIN ScrmPIVarietyList SPVL ON SPVL.VarietyID = PFD.VarietyID
                    WHERE PFD.TubNo IN ($tubNo) AND TrackID = '$trackId'
                    GROUP BY
                    -- PFD.GradingBreakupID,PFD.PackingStyleID,PFD.Grade,PFD.VarietyID,PFD.TubNo
                    PFD.TubNo,PFD.PackingStyleID,PFD.Grade,PFD.VarietyID
                    ORDER BY PFD.TubNo";
                $stmtFreezing = $this->conn->prepare($sqlFreezing);
                $stmtFreezing->execute();
                // $ArrayHeadonToFinishedReportData = [];
                $PrviousId = '';
                $PrviousId1 = '';
                $PrviousId2 = '';
                $remarks = '';
                while ($dataFreezing = $stmtFreezing->fetch(PDO::FETCH_ASSOC)) {
                    if ($PrviousId == '') {
                        $PrviousId = $dataFreezing['TubNo'];
                    }
                    if ($PrviousId1 == '') {
                        $PrviousId1 = $dataFreezing['TubNo'];
                    }
                    if ($PrviousId2 == '') {
                        $PrviousId2 = $dataFreezing['TubNo'];
                    }
                    if ($PackingStyle != '') {
                        if ($dataFreezing['TubNo'] == $PrviousId) {
                            $PackingStyle .= ',' . $dataFreezing['PackingStyle'];
                        } else {
                            $PackingStyle .= '&' . $dataFreezing['PackingStyle'];
                            $PrviousId = $dataFreezing['TubNo'];
                        }
                    } else {
                        $PackingStyle .= $dataFreezing['PackingStyle'];
                    }
                    if ($FreezingQty != '') {

                        if ($dataFreezing['TubNo'] == $PrviousId1) {
                            $FreezingQty .= ',' . $dataFreezing['PWeight'];
                        } else {
                            $FreezingQty .= '&' . $dataFreezing['PWeight'];
                            $PrviousId1 = $dataFreezing['TubNo'];
                        }
                    } else {
                        $FreezingQty .= $dataFreezing['PWeight'];
                    }
                    $totalProdWeight += $dataFreezing['PWeight'];

                    if ($ProductionQuantity != '') {

                        if ($dataFreezing['TubNo'] == $PrviousId1) {
                            $ProductionQuantity .= ',' . $dataFreezing['ProductionQuantity'];
                        } else {
                            $ProductionQuantity .= '&' . $dataFreezing['ProductionQuantity'];
                            $PrviousId1 = $dataFreezing['TubNo'];
                        }
                    } else {
                        $ProductionQuantity .= $dataFreezing['ProductionQuantity'];
                    }

                    if ($RejectionQuantity != '') {

                        if ($dataFreezing['TubNo'] == $PrviousId1) {
                            $RejectionQuantity .= ',' . $dataFreezing['RejectionQuantity'];
                        } else {
                            $RejectionQuantity .= '&' . $dataFreezing['RejectionQuantity'];
                            $PrviousId1 = $dataFreezing['TubNo'];
                        }
                    } else {
                        $RejectionQuantity .= $dataFreezing['RejectionQuantity'];
                    }

                    if ($Varieties != '') {
                        if ($dataFreezing['TubNo'] == $PrviousId2) {
                            $Varieties .= ',' . $dataFreezing['Varieties'];
                        } else {
                            $Varieties .= '&' . $dataFreezing['Varieties'];
                            $PrviousId2 = $dataFreezing['TubNo'];
                        }
                    } else {
                        $Varieties .= $dataFreezing['Varieties'];
                    }
                }
                $dataRowHeadonToFinishedReportData['Packing'] = $PackingStyle;
                $dataRowHeadonToFinishedReportData['FreezingVariety'] = $Varieties;
                $dataRowHeadonToFinishedReportData['FinishedQuantity'] = $FreezingQty;
                $dataRowHeadonToFinishedReportData['ProductionQuantity'] = $ProductionQuantity;
                $dataRowHeadonToFinishedReportData['RejectionQuantity'] = $RejectionQuantity;

                $ArrayHeadonToFinishedReportData[] = $dataRowHeadonToFinishedReportData;
            }

            $SqlMixlotIDS = "SELECT GROUP_CONCAT(DISTINCT(RmLotID)) AS LotIDS FROM  RmLotDetatils WHERE RmReferenceID='$ArrivalID' ORDER BY `RmLotID` DESC";
            $stmtMixlotIDS = $this->conn->prepare($SqlMixlotIDS);
            $stmtMixlotIDS->execute();
            $dataRowMixlotIDS = $stmtMixlotIDS->fetch(PDO::FETCH_ASSOC);
            $MixedLotIDS = $dataRowMixlotIDS['LotIDS'];

        }


        $Data['packingType'] = $HONPacking;//1=head on, 0= head less
        $Data['PurchaseData'] = $this->getPurchaseandReceivingData($ArrivalID);
        // $Data['Yields']= $this->getYields($ArrivalID);
        if ($HONPacking == 0) {
            $Data['Yields'] = $this->getYields($MixedLotIDS, $MergeHonLoose);
        } else {
            $Data['Yields'][]['HoToFinishedYield'] = round((($totalProdWeight / $ArrivalWeight) * 100), 3);
        }
        $Data['Totals']['totalHonQty'] = $ArrivalWeight;
        $Data['Totals']['totalFinishedQty'] = $totalProdWeight;
        $Data['AbStatus'] = $this->getAbStatus($ArrivalID);
        $Data['HeadonToFinishedReportData'] = $ArrayHeadonToFinishedReportData;


        return $Data;
    }

    function getAbStatus($MixedLotIDS)
    {
        //$MixedLotIDS = 2043;
        $SqlAbstatus = "SELECT RmLotID,AbStatus,Name,Colour from RmLotDetatils as RLD left join QcRmAbReport as QCAB on RLD.RmLotID=QCAB.LotID LEFT join QcABOptions as ABO on ABO.OptID=QCAB.AbStatus where RmReferenceID='" . $MixedLotIDS . "'";
        $AbStatusName = 'TEST PENDING';
        $AbStatusColour = '#666';
        $stmtAbstatus = $this->conn->prepare($SqlAbstatus);
        $stmtAbstatus->execute();
        while ($dataAbStatusData = $stmtAbstatus->fetch(PDO::FETCH_ASSOC)) {
            if (isset($dataAbStatusData['Name']) && $dataAbStatusData['Name']) {
                if ($dataAbStatusData['Name'] == "POSITIVE") {
                    $AbStatusName = $dataAbStatusData['Name'];
                    $AbStatusColour = $dataAbStatusData['Colour'];
                    break;
                } else {
                    $AbStatusName = $dataAbStatusData['Name'];
                    $AbStatusColour = $dataAbStatusData['Colour'];
                }
            }
        }
        return ["Name" => $AbStatusName, "Colour" => $AbStatusColour];
    }

    function getPurchaseandReceivingData($ArrivalID)
    {
        $SqlFileds = "DATE_FORMAT(RAD.ArrivalDate, '%d-%m-%Y') AS Date, ArrivalNumber as BatchNumber,GROUP_CONCAT(distinct Count)as PurchaseCount ,sum(Weight) as PurchaseHoQty ,
    GROUP_CONCAT(distinct ReceivingCount) as PlantCount,StationName as Place";

        $SqlPurchaseData = " SELECT  $SqlFileds FROM `RmArrivalDetatils` RAD
left join RmArrivalBreakUpDetails RAB on RAB.ArrivalID=RAD.RmArrivalID
-- left join RmReceivingBreakup RRB on RRB.ArrivalID=RAD.RmArrivalID
LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalBreakupID = RAB.ArrivalBrkUpID
left join RmReceivingDetails RRD on RRD.ReceivingID=RRB.ReceivingID
left join RmProcessingStations RPS on RPS.ProcessingStationID=RRD.ReceivingPlantID
WHERE  RmArrivalID='$ArrivalID'";

        $stmtPurchaseData = $this->conn->prepare($SqlPurchaseData);
        $stmtPurchaseData->execute();


        // $SqlBatchNumber=" SELECT * FROM PpcHLGradingDetails PHGD  left join  `RmLotDetatils` RLD on RLD.RmLotID =PHGD.LotID
//  WHERE RmReferenceID='$ArrivalID' ORDER BY `RmLotID` DESC";

        //  $stmtBatchNumber = $this->conn->prepare($SqlBatchNumber);
//  $stmtBatchNumber->execute();

        while ($dataRowPurchaseData = $stmtPurchaseData->fetch(PDO::FETCH_ASSOC)) {
            $ArrayPurchaseData[] = $dataRowPurchaseData;

        }
        return $ArrayPurchaseData;

    }

    // function getGrades()
//  {
//      $sqlQueryGrade = "SELECT value AS Grade FROM ProductionGrades ORDER BY ABS(CAST(value AS SIGNED)) ASC";

    //      $stmtGrade = $this->conn->prepare($sqlQueryGrade);
//      $stmtGrade->execute();
//      while ($dataRowGrade = $stmtGrade->fetch(PDO::FETCH_ASSOC)) {
//          $Grade['Grade'] = $dataRowGrade['Grade'];
//          $ArrayGrade[] = $Grade;
//      }
//      return $ArrayGrade;
//  }

    // function getYields($ArrivalID){
    function getYields($MixedLotIDS, $MergeHonLoose)
    {

        // echo $MixedLotIDS; exit;
        $length = count(explode(',', $MixedLotIDS));
        //             // echo $length;
        //             $DisplayText="";
        //             $DisplayText2="";
        // $Colour="";
        // $BatchNumber="";
        // $MixedHeadonWeight="";
        // $PurchaseCount="";



        //    $sqlHeadOnweight=  " SELECT sum(Weight) as HeadOnWeight FROM `RmArrivalBreakUpDetails` WHERE ArrivalID='$ArrivalID'";
        if ($length > 0) {
            if ($MergeHonLoose == 1) {
                $sqlHeadOnweight = "  SELECT GROUP_CONCAT(distinct RmReferenceID) ArrivalID, GROUP_CONCAT(distinct LotNumber) BatchNumber,sum(Weight) as HeadOnWeight,
                                    GROUP_CONCAT(distinct Count) as PurchaseCount ,
                                    COALESCE((     SELECT GROUP_CONCAT(distinct ReceivingCount) FROM RmReceivingBreakup RABD   
                                    WHERE FIND_IN_SET(RABD.ArrivalID, GROUP_CONCAT(RLD.RmReferenceID))    ), '') AS ReceivingCount  FROM RmArrivalDetatils AS RAD
                                    LEFT JOIN RmArrivalBreakUpDetails AS RABD ON RAD.RmArrivalID = RABD.ArrivalID 
                                    LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalID = RABD.ArrivalID
                                    left join RmLotDetatils as RLD on RLD.BreakUpID=RRB.ArrivalBreakupID  WHERE RmLotID in ($MixedLotIDS)";
            } else {
                $sqlHeadOnweight = "  SELECT GROUP_CONCAT(distinct RmReferenceID) ArrivalID, GROUP_CONCAT(distinct LotNumber) BatchNumber,sum(PurchaseWeight) as HeadOnWeight,
                                    GROUP_CONCAT(distinct PurchaseCount) as PurchaseCount ,
                                    COALESCE((     SELECT GROUP_CONCAT(distinct ReceivingCount) FROM RmReceivingBreakup RABD   
                                    WHERE FIND_IN_SET(RABD.ArrivalID, GROUP_CONCAT(RLD.RmReferenceID))    ), '') AS ReceivingCount  FROM `RmLotDetatils`  RLD WHERE RmLotID in ($MixedLotIDS)";
            }

            // $sqlHeadOnweight=  "  SELECT GROUP_CONCAT(distinct RmReferenceID) ArrivalID, GROUP_CONCAT(distinct LotNumber) BatchNumber,COALESCE((
            //     SELECT sum(Weight) FROM RmArrivalBreakUpDetails RABD   
            //     WHERE FIND_IN_SET(RABD.ArrivalID, GROUP_CONCAT(RLD.RmReferenceID))    ), '') AS HeadOnWeight FROM `RmLotDetatils`  RLD
            // WHERE RmLotID in ($MixedLotIDS)";


            $stmtHeadOnweight = $this->conn->prepare($sqlHeadOnweight);
            $stmtHeadOnweight->execute();
            $dataRowHeadOnweight = $stmtHeadOnweight->fetch(PDO::FETCH_ASSOC);

            $HeadOnWeight = $dataRowHeadOnweight['HeadOnWeight'];
            $BatchNumber = $dataRowHeadOnweight['BatchNumber'];
            $PurchaseCount = $dataRowHeadOnweight['PurchaseCount'];
            $ReceivingCount = $dataRowHeadOnweight['ReceivingCount'];

            $sqlBodyYield = "SELECT WtAfterDH,HonWeight FROM PpcDeheading WHERE LotID IN ($MixedLotIDS) AND WtAfterDH>0";
            $stmtBodyYield = $this->conn->prepare($sqlBodyYield);
            $stmtBodyYield->execute();
            $bodyYield = '';
            while ($dataBodyYield = $stmtBodyYield->fetch(PDO::FETCH_ASSOC)) {
                if ($bodyYield == '') {
                    $bodyYield .= (($dataBodyYield['WtAfterDH'] / $dataBodyYield['HonWeight']) * 100);
                } else {
                    $bodyYield .= ',' . (($dataBodyYield['WtAfterDH'] / $dataBodyYield['HonWeight']) * 100);
                }
            }
        }

        //  if($length>1){
        //     $DisplayText="Mixed Batch Numbers : ";
        //     $Colour="red";
        //     $BatchNumber=$dataRowHeadOnweight['BatchNumber'];
        //     $MixedHeadonWeight=$HeadOnWeight;
        //     $DisplayText2="Total mixed batches purchase weight : ";
        //     $PurchaseCount = $dataRowHeadOnweight['PurchaseCount'];


        //                 }
        //  $ArrayGrade[] = $Grade;


        $sqlYieldsQuery = "  SELECT  HlQuantity, FinishedQuantity FROM `HeadonToFinishedReportTemp` WHERE GradingBreakUpID='TOTAL'";
        $stmtYields = $this->conn->prepare($sqlYieldsQuery);
        $stmtYields->execute();
        $dataRowYields = $stmtYields->fetch(PDO::FETCH_ASSOC);

        $HlQuantity = $dataRowYields['HlQuantity'];
        $FinishedQuantity = $dataRowYields['FinishedQuantity'];

        $ArrayYields['HoToHlYield'] = number_format((($HlQuantity / $HeadOnWeight) * 100), 2);
        $ArrayYields['HoToFinishedYield'] = number_format((($FinishedQuantity / $HeadOnWeight) * 100), 2);
        $ArrayYields['HlToFinishedYield'] = number_format((($FinishedQuantity / $HlQuantity) * 100), 2);
        $ArrayYields['DisplayText'] = $DisplayText;
        $ArrayYields['Colour'] = $Colour;
        $ArrayYields['BatchNumber'] = $BatchNumber;
        $ArrayYields['bodyYield'] = $bodyYield;
        $ArrayYields['MixedHeadonWeight'] = $HeadOnWeight;
        $ArrayYields['PurchaseCount'] = $PurchaseCount;
        $ArrayYields['ReceivingCount'] = $ReceivingCount;


        $AryYields[] = $ArrayYields;

        return $AryYields;

    }

}
