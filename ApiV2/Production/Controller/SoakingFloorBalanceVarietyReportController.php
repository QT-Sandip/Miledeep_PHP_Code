<?php
include_once '../Global/Util.php';
class SoakingFloorBalanceVarietyReportController
{
    private $conn;
    private $Error = null;
    private $Msg = '';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Build a comma-separated list of placeholders for IN clauses.
     * Returns array of (placeholder_string, values_array) for use in prepared statements.
     */
    private function buildInClause($ids)
    {
        return implode(',', array_map('intval', $ids));
    }

    /**
     * Main orchestrator method
     */
    public function getGradingTrackNumbers($RequestBody)
    {
        $StartDate = "2025-11-18";
        if(!empty($RequestBody['ReceivingPlantID']) && $RequestBody['ReceivingPlantID'] == 7){// unit 1 - CAM-3215
            $StartDate = "2026-08-01";
        }elseif(!empty($RequestBody['ReceivingPlantID']) && $RequestBody['ReceivingPlantID'] == 2){// unit 1 - CAM-3262
            $StartDate = "2026-06-01";
        }
        $StartDate = "2026-08-01";
        
        $EndDate = $RequestBody['EndDate'];
        $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
        $ChillRoomPreparationType = $RequestBody['ChillRoomPreparationType'];

        $DateFilter = "RmRD.ArrivalDate BETWEEN :StartDate AND :EndDate";
        $baseParams = [':StartDate' => $StartDate, ':EndDate' => $EndDate];

        $Query = '';
        $queryParams = [];
        if ($ReceivingPlantID != '') {
            $Query .= " AND RmRD.ReceivingPlantID = :ReceivingPlantID";
            $queryParams[':ReceivingPlantID'] = $ReceivingPlantID;
        }

        $allParams = array_merge($baseParams, $queryParams);

        $Data = ['Error' => null, 'Msg' => 'Success'];

        // Raw HL
        if ($ChillRoomPreparationType == '' || $ChillRoomPreparationType == 'RAW HL QTY') {
            $trackData = $this->getTrackData($DateFilter, $Query, $allParams, 'TrackWiseRawHLQuantity', 1, 0, 0, 'RAW');
            if (!empty($trackData)) {
                $trackIDs = array_column($trackData, 'TrackID');
                $Data['RawHLTrackData'] = $trackData;
                $Data['RawHLBalanceData'] = $this->getHLBalanceData($trackIDs, 'RAW');
            }
        }

        // Cooked HL
        if ($ChillRoomPreparationType == '' || $ChillRoomPreparationType == 'COOKED HL QTY') {
            $trackData = $this->getTrackData($DateFilter, $Query, $allParams, 'TrackWiseRawHLQuantity', 1, 0, 0, 'COOKED');
            if (!empty($trackData)) {
                $trackIDs = array_column($trackData, 'TrackID');
                $Data['CookedHLTrackData'] = $trackData;
                $Data['CookedHLBalanceData'] = $this->getHLBalanceData($trackIDs, 'COOKED');
            }
        }

        // Raw Meat
        $TransferSql = "
                        UNION

                        SELECT ReceivingID AS TrackID, CONCAT(TrackCode,' (Transferred)') AS TrackCode, ReceivingPlantID, ArrivalDate,
                        TrackWiseRawMeatQuantity AS Quantity, 1 AS IsTransfer
                        FROM RmReceivingDetails AS RmRD
                        LEFT JOIN  PpcHLGradingDetails AS PHGD ON PHGD.TrackID=RmRD.ReceivingID AND PHGD.CreatedFrom=1
                        LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID = PHGD.LotID AND PHGB.CreatedFrom=1
                        LEFT JOIN PpcValueAddition AS PVA ON PVA.TrackID = RmRD.ReceivingID  AND PVA.CreatedFrom=1 -- for transfer track ids
                        WHERE 
                        Treatment != 'UNT' AND RmRD.ArrivalDate BETWEEN '$StartDate' AND '$EndDate' 
                        AND PVA.SoakingPlantID = '$ReceivingPlantID'  AND RmRD.ReceivingPlantID != '$ReceivingPlantID'
                        AND TrackWiseRawMeatQuantity >= 1
                        AND IsStoreMaterial = 0
                        AND OutStage = 0 ";
        if ($ChillRoomPreparationType == '' || $ChillRoomPreparationType == 'RAW MEAT QTY') {
            $trackData = $this->getTrackData($DateFilter, $Query, $allParams, 'TrackWiseRawMeatQuantity', 1, 0, 0, 'RAW', $TransferSql);
            if (!empty($trackData)) {
                $trackIDs = array_column($trackData, 'TrackID');
                $TransferTrackID = array_column($trackData, 'TransferTrackID');
                $AllTrackIds = array_column($trackData, 'AllTrackIds');
                $Data['RawMeatTrackData'] = $trackData;
                $Data['RawMeatBalanceData'] = $this->getMeatBalanceData($AllTrackIds, $trackIDs, $ReceivingPlantID, 'RAW', $TransferTrackID);
            }
        }

        // Cooked Meat
        if ($ChillRoomPreparationType == '' || $ChillRoomPreparationType == 'COOKING MEAT QTY') {
             $trackData = $this->getTrackData($DateFilter, $Query, $allParams, 'TrackWiseCookedMeatQuantity', 1, 0, 0, 'COOKED', $TransferSql);
            if (!empty($trackData)) {
                $trackIDs = array_column($trackData, 'TrackID');
                $TransferTrackID = array_column($trackData, 'TransferTrackID');
                $AllTrackIds = array_column($trackData, 'AllTrackIds');
                $Data['CookedMeatTrackData'] = $trackData;
                $Data['CookedMeatBalanceData'] = $this->getMeatBalanceData($AllTrackIds, $trackIDs, $ReceivingPlantID, 'COOKED', $TransferTrackID);
            }
        }

        // Block Meat
        if ($ChillRoomPreparationType == '' || $ChillRoomPreparationType == 'BLOCK MEAT QTY') {
            $trackData = $this->getTrackData($DateFilter, $Query, $allParams, 'TrackWiseBlockMeatQuantity', 1, 0, 0, 'RAW');
            if (!empty($trackData)) {
                $trackIDs = array_column($trackData, 'TrackID');
                $Data['BlockMeatTrackData'] = $trackData;
                $Data['BlockMeatBalanceData'] = $this->getBlockMeatBalanceData($trackIDs);
            }
        }

        // Thawing Meat
        if ($ChillRoomPreparationType == '' || $ChillRoomPreparationType == 'THAWING MEAT QTY') {
            $trackData = $this->getTrackData($DateFilter, $Query, $allParams, 'TrackWiseThawingMeatQuantity', 1, 1, 1, null);
            if (!empty($trackData)) {
                $trackIDs = array_column($trackData, 'TrackID');
                $Data['ThawingMeatTrackData'] = $trackData;
                $Data['ThawingMeatBalanceData'] = $this->getThawingMeatBalanceData($trackIDs);
            }
        }

        return $Data;
    }

    /**
     * OPTIMIZATION: Merged 6 nearly-identical track query methods into 1.
     * Uses prepared statement parameters instead of string interpolation.
     *
     * @param string $dateFilter  WHERE clause for date with :param placeholders
     * @param string $query       Extra WHERE clause with :param placeholders
     * @param array  $params      Bound parameters for prepared statement
     * @param string $qtyColumn   Which quantity column to filter on
     * @param int    $minQty      Minimum quantity threshold
     * @param int    $isStoreMaterial  IsStoreMaterial filter value
     * @param int    $outStage    OutStage filter value
     * @param string|null $typeProduct  TypeProduct filter (null to skip)
     * @return array
     */
    private function getTrackData($dateFilter, $query, $params, $qtyColumn, $minQty, $isStoreMaterial, $outStage, $typeProduct, $TransferSql = '')
    {
        $sql = "SELECT ReceivingID AS TrackID, TrackCode, ReceivingPlantID, ArrivalDate,
                       $qtyColumn AS Quantity, 0 AS IsTransfer
                FROM RmReceivingDetails AS RmRD
                WHERE $dateFilter
                  $query
                  AND $qtyColumn >= :minQty
                  AND IsStoreMaterial = :isStoreMaterial
                  AND OutStage = :outStage
                  
                  $TransferSql
                  
                  ORDER BY ArrivalDate,TrackID ASC";

        $params[':minQty'] = $minQty;
        $params[':isStoreMaterial'] = $isStoreMaterial;
        $params[':outStage'] = $outStage;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        $i = 0;
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $TransferTrackID = 0;
            $TrackID = $row['TrackID'];
            if($row['IsTransfer'] == 1 ){
                $TransferTrackID = $row['TrackID'];
                $TrackID = 0;
            }
            $result[] = [
                'SNo' => ++$i,
                'ArrivalDate' => !empty($row['ArrivalDate']) ? date('d-m-Y', strtotime($row['ArrivalDate'])) : '',
                'TrackID' => (int)$TrackID,
                'TransferTrackID' => (int)$TransferTrackID,
                 'AllTrackIds' => (int)$row['TrackID'],
                 'TrackCode' => $row['TrackCode'],
                'ReceivingPlantID' => (int)$row['ReceivingPlantID'],
                'Quantity' => $row['Quantity'],
                'rowCount' => 1,
            ];
        }
        return $result;
    }

    /**
     * OPTIMIZATION: Replaced N+1 query pattern with single bulk query.
     * Original: 1 temp table CREATE + 1 summary query + (N varieties × M tracks) individual queries
     * Now: 1 temp table CREATE + 1 bulk query + PHP array indexing
     *
     * For 10 varieties × 20 tracks = 200 queries reduced to 1 query.
     */
    private function getHLBalanceData( $trackIDs,  $typeProduct)
    {
        if (empty($trackIDs)) {
            return [];
        }

        $trackIDList = $this->buildInClause($trackIDs);

        // Use unique temp table name per type to avoid collision
        $tempTable = $typeProduct === 'RAW' ? 'HLPivotRawTemp' : 'HLPivotCookedTemp';

        $this->conn->prepare("DROP TABLE IF EXISTS `$tempTable`")->execute();

        // Single query: aggregate everything into pivot table
        $sql = "CREATE TABLE `$tempTable` AS
            SELECT
                PHGD.TrackID AS PivotTrackID,
                PHVL.ShortName AS Variety,
                PHVL.Species AS Species,
                PHGB.PackingGrade AS Grade,
                SUM(PHGB.HLWeight) - COALESCE(SUM(PVA_SUM.TotalSentWeight), 0) AS Weight,
                SUM(PHGB.NoCrates) - COALESCE(SUM(PVA_SUM.TotalUsedCrates), 0) AS Crates
            FROM PpcHLGradingBreakup AS PHGB
            LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID = PHVL.VarietyID
            LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID = PHGB.LotID
            LEFT JOIN (
                SELECT GradingBreakupID, SUM(SentWeight) AS TotalSentWeight, SUM(NoofCrates) AS TotalUsedCrates
                FROM PpcValueAddition
                GROUP BY GradingBreakupID
            ) AS PVA_SUM ON PVA_SUM.GradingBreakupID = PHGB.DataID
            WHERE PHGD.TrackID IN ($trackIDList)
              AND PHGB.VarietyID != 0
              AND PHGB.Treatment != 'UNT'
              AND PHGB.IsBlock = 0
              AND PHVL.TypeProduct = :typeProduct
            GROUP BY PHVL.ShortName, PHVL.Species, PHGB.PackingGrade, PHGD.TrackID
            ORDER BY ABS(PHGB.PackingGrade)";

        $stmtCreate = $this->conn->prepare($sql);
        $stmtCreate->execute([':typeProduct' => $typeProduct]);

        // OPTIMIZATION: Single bulk query instead of N individual per-TrackID queries
        // Returns ALL (TrackID, Variety, Species, Grade, Weight) in one shot
        $sqlBulk = "SELECT PivotTrackID, Variety, Species, Grade, Weight, Crates FROM `$tempTable`";
        $stmtBulk = $this->conn->prepare($sqlBulk);
        $stmtBulk->execute();

        // Build PHP-side lookup: [variety-species-grade => [trackID => weight]]
        $lookup = [];
        $summary = [];
        while ($row = $stmtBulk->fetch(PDO::FETCH_ASSOC)) {
            $key = $row['Variety'] . '|' . $row['Species'] . '|' . $row['Grade'];
            $trackID = (int)$row['PivotTrackID'];
            $weight = (float)$row['Weight'];
            $crates = (float)$row['Crates'];

            $lookup[$key][$trackID] = ['weight' => $weight, 'crates' => $crates];

            if (!isset($summary[$key])) {
                $summary[$key] = [
                    'Variety' => $row['Variety'],
                    'Species' => $row['Species'],
                    'Grade' => $row['Grade'],
                    'TotalWeight' => 0,
                    'TotalCrates' => 0,
                ];
            }
            if ($weight > 1 && $crates > 0) {
                $summary[$key]['TotalWeight'] += $weight;
                $summary[$key]['TotalCrates'] += $crates;
            }
        }

        // Build response structure
        $arrayVarietyData = [];
        $grandBalanceQty = 0;
        $sno = 0;

        foreach ($summary as $key => $info) {
            $balanceData = [];
            $totalQty = 0;

            foreach ($trackIDs as $trackID) {
                $val = $lookup[$key][$trackID]['weight'] ?: 0;
                $crateVal = $lookup[$key][$trackID]['crates'] ?: 0;
                $balanceData[] = [
                    'Balance' => ($val <= 1 || $crateVal <= 0) ? '-' : number_format($val, 3)
                ];
                if ($val > 1 && $crateVal > 0) {
                    $totalQty += $val;
                }
            }

            $arrayVarietyData[] = [
                'SNO' => ++$sno,
                'PackingGrade' => $info['Grade'],
                'ShortName' => $info['Variety'] . '-' . $info['Species'],
                'BalanceQty' => $info['TotalWeight'],
                'TotalBalanceQty' => number_format($totalQty, 3),
                'BalanceData' => $balanceData,
            ];
            $grandBalanceQty += $totalQty;
        }

        if ($grandBalanceQty != 0) {
            return [
                'GrandBalanceQty' => number_format($grandBalanceQty, 3),
                'ArrayVarietyData' => $arrayVarietyData,
            ];
        }

        return [];
    }

    /**
     * OPTIMIZATION: Same N+1 elimination for Meat balance data (Raw Meat, Cooked Meat)
     * Uses CTEs with single bulk fetch instead of per-TrackID queries.
     */
    private function getMeatBalanceData( $AllTrackIds, $trackIDs,  $ReceivingPlantID,  $typeProduct, $TransferTrackID = '')
    {
        if (empty($AllTrackIds) && empty($trackIDs) && empty($TransferTrackID)) {
            return [];
        }

        $trackIDList = $this->buildInClause($trackIDs);
        $TransferTrackIDList = $this->buildInClause($TransferTrackID);
        $tempTable = 'MeatPivot_' . $typeProduct;

        $this->conn->prepare("DROP TABLE IF EXISTS `$tempTable`")->execute();

        $plantFilter = '';
        $params = [':typeProduct' => $typeProduct];
        if (!empty($ReceivingPlantID)) {
            $plantFilter = 'AND PVA.SoakingPlantID = :plantID';
            $params[':plantID'] = $ReceivingPlantID;
        }

        $sql = "CREATE TABLE `$tempTable` AS
            WITH VA AS (
                SELECT
                    PVA.TrackID, PVA.GradingBreakupID, PVA.VarietyID,
                    PHVL.Species, PHGB.PackingGrade, PHVL.ShortName, PVA.VLID,
                    SUM(PVA.WtAftrVA) AS VAQty,PHGB.HLCount
                FROM PpcValueAddition PVA
                LEFT JOIN PpcHLGradingBreakup PHGB ON PHGB.DataID = PVA.GradingBreakupID
                LEFT JOIN PpcHLVAVarietyList PHVL ON PHVL.VarietyID = PVA.VarietyID
                WHERE PVA.TrackID IN ($trackIDList)
                  AND PHGB.IsBlock = 0
                  AND PHGB.Treatment != 'UNT'
                  AND PHVL.TypeProduct = :typeProduct
                  $plantFilter
                GROUP BY PVA.TrackID, PVA.GradingBreakupID, PVA.VarietyID,
                         PHVL.Species, PHGB.PackingGrade, PHVL.ShortName, PVA.VLID,PHGB.HLCount
            ),
            SOAK AS (
                SELECT ValueAdditionID AS VLID, SUM(SoakInQty) AS SoakQty
                FROM ProdSoakingDetails
                WHERE TrackID IN ($trackIDList)
                GROUP BY ValueAdditionID
            ),
            VATransfer AS (
                SELECT
                    PVA.TrackID, PVA.GradingBreakupID, PVA.VarietyID,
                    PHVL.Species, PHGB.PackingGrade, PHVL.ShortName, PVA.VLID,
                    SUM(PVA.WtAftrVA) AS VAQty,PHGB.HLCount
                FROM PpcValueAddition PVA
                LEFT JOIN PpcHLGradingBreakup PHGB ON PHGB.DataID = PVA.GradingBreakupID
                LEFT JOIN PpcHLVAVarietyList PHVL ON PHVL.VarietyID = PVA.VarietyID
                WHERE PVA.TrackID IN ($TransferTrackIDList)
                    AND PHGB.IsBlock = 0
                AND PHGB.Treatment != 'UNT'
                AND PHVL.TypeProduct = '$typeProduct'
                AND PVA.SoakingPlantID = '$ReceivingPlantID'
                GROUP BY PVA.TrackID, PVA.GradingBreakupID, PVA.VarietyID,
                        PHVL.Species, PHGB.PackingGrade, PHVL.ShortName, PVA.VLID,PHGB.HLCount
            ),
            SOAKTransfer AS (
                SELECT ValueAdditionID AS VLID, SUM(SoakInQty) AS SoakQty
                FROM ProdSoakingDetails
                WHERE TrackID IN ($TransferTrackIDList)
                GROUP BY ValueAdditionID
            )
            SELECT
                VA.TrackID AS PivotTrackID,
                VA.ShortName AS Variety,
                VA.Species AS Species,
                VA.PackingGrade AS Grade,
                SUM(VA.VAQty) - SUM(COALESCE(SOAK.SoakQty, 0)) AS Weight,VA.HLCount
            FROM VA
            LEFT JOIN SOAK ON SOAK.VLID = VA.VLID
            GROUP BY VA.TrackID, VA.ShortName, VA.Species, VA.PackingGrade,VA.HLCount
            -- HAVING Weight >= 1
            -- ORDER BY ABS(VA.PackingGrade)
            
            UNION 

             SELECT
                VATransfer.TrackID AS PivotTrackID,
                VATransfer.ShortName AS Variety,
                VATransfer.Species AS Species,
                VATransfer.PackingGrade AS Grade,
                SUM(VATransfer.VAQty) - SUM(COALESCE(SOAKTransfer.SoakQty, 0)) AS Weight,VATransfer.HLCount
            FROM VATransfer
            LEFT JOIN SOAKTransfer ON SOAKTransfer.VLID = VATransfer.VLID
            GROUP BY VATransfer.TrackID, VATransfer.ShortName, VATransfer.Species, VATransfer.PackingGrade,VATransfer.HLCount
            
            HAVING Weight >= 1
            ORDER BY ABS(Grade)
            ";

        $stmtCreate = $this->conn->prepare($sql);
        $stmtCreate->execute($params);

        // Single bulk query
        $sqlBulk = "SELECT PivotTrackID, Variety, Species, Grade, Weight,HLCount FROM `$tempTable`";
        $stmtBulk = $this->conn->prepare($sqlBulk);
        $stmtBulk->execute();

        $lookup = [];
        $summary = [];
        while ($row = $stmtBulk->fetch(PDO::FETCH_ASSOC)) {
            $key = $row['Variety'] . '|' . $row['Species'] . '|' . $row['Grade']. '|' . $row['HLCount'];
            $trackID = (int)$row['PivotTrackID'];
            $weight = (float)$row['Weight'];

            $lookup[$key][$trackID] = $weight;

            if (!isset($summary[$key])) {
                $summary[$key] = [
                    'Variety' => $row['Variety'],
                    'Species' => $row['Species'],
                    'Grade' => $row['Grade'],
                    'TotalWeight' => 0,
                    'HLCount' => $row['HLCount'],
                ];
            }
            if ($weight > 1) {
                $summary[$key]['TotalWeight'] += $weight;
            }
        }

        $arrayVarietyData = [];
        $grandBalanceQty = 0;
        $sno = 0;

        foreach ($summary as $key => $info) {
            $balanceData = [];
            $totalQty = 0;

            foreach ($AllTrackIds as $trackID) {
                $val = $lookup[$key][$trackID] ?: 0;
                $balanceData[] = ['Balance' => ($val <= 1) ? '-' : number_format($val, 3)];
                if ($val > 1) {
                    $totalQty += $val;
                }
            }

            $arrayVarietyData[] = [
                'SNO' => ++$sno,
                'PackingGrade' => $info['Grade'],
                'ShortName' => $info['Variety'] . '-' . $info['Species'],
                'BalanceQty' => $info['TotalWeight'],
                'TotalBalanceQty' => number_format($totalQty, 3),
                'BalanceData' => $balanceData,
                'HLCount' => $info['HLCount'],// Newly added
            ];
            $grandBalanceQty += $totalQty;
        }

        if ($grandBalanceQty != 0) {
            return [
                'GrandBalanceQty' => number_format($grandBalanceQty, 3),
                'ArrayVarietyData' => $arrayVarietyData,
            ];
        }

        return [];
    }

    /**
     * OPTIMIZATION: Same N+1 elimination for Block Meat balance data.
     */
    private function getBlockMeatBalanceData( $trackIDs)
    {
        if (empty($trackIDs)) {
            return [];
        }

        $trackIDList = $this->buildInClause($trackIDs);

        $this->conn->prepare("DROP TABLE IF EXISTS `BlockMeatPivotTemp`")->execute();

        $sql = "CREATE TABLE BlockMeatPivotTemp AS
            SELECT
                d.TrackID AS PivotTrackID,
                PHGB.PackingGrade AS Grade,
                PHVL.ShortName AS Variety,
                PHVL.Species AS Species,
                SUM(PHGB.SoakingStageBlockWeight + PHGB.FreezingStageBlockWeight) AS Weight
            FROM PpcHLGradingBreakup AS PHGB
            LEFT JOIN (
                SELECT PHGD.*
                FROM PpcHLGradingDetails PHGD
                INNER JOIN (
                    SELECT MixIDs, MIN(HLID) AS MinDetailID
                    FROM PpcHLGradingDetails
                    WHERE TrackID IN ($trackIDList)
                    GROUP BY MixIDs
                ) x ON PHGD.MixIDs = x.MixIDs AND PHGD.HLID = x.MinDetailID
            ) d ON d.MixIDs = PHGB.MixedID
            LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHVL.VarietyID = PHGB.VarietyID
            WHERE d.TrackID IN ($trackIDList)
            GROUP BY PHGB.PackingGrade, PHVL.ShortName, PHVL.Species, d.TrackID
            HAVING Weight > 1
            ORDER BY ABS(PHGB.PackingGrade)";

        $stmtCreate = $this->conn->prepare($sql);
        $stmtCreate->execute();

        // Single bulk query
        $sqlBulk = "SELECT PivotTrackID, Variety, Species, Grade, Weight FROM BlockMeatPivotTemp";
        $stmtBulk = $this->conn->prepare($sqlBulk);
        $stmtBulk->execute();

        $lookup = [];
        $summary = [];
        while ($row = $stmtBulk->fetch(PDO::FETCH_ASSOC)) {
            $key = $row['Variety'] . '|' . $row['Species'] . '|' . $row['Grade'];
            $trackID = (int)$row['PivotTrackID'];
            $weight = (float)$row['Weight'];

            $lookup[$key][$trackID] = $weight;

            if (!isset($summary[$key])) {
                $summary[$key] = [
                    'Variety' => $row['Variety'],
                    'Species' => $row['Species'],
                    'Grade' => $row['Grade'],
                    'TotalWeight' => 0,
                ];
            }
            if ($weight > 1) {
                $summary[$key]['TotalWeight'] += $weight;
            }
        }

        $arrayVarietyData = [];
        $grandBalanceQty = 0;
        $sno = 0;

        foreach ($summary as $key => $info) {
            $balanceData = [];
            $totalQty = 0;

            foreach ($trackIDs as $trackID) {
                $val = $lookup[$key][$trackID] ?: 0;
                $balanceData[] = ['Balance' => ($val <= 1) ? '-' : number_format($val, 3)];
                if ($val > 1) {
                    $totalQty += $val;
                }
            }

            $arrayVarietyData[] = [
                'SNO' => ++$sno,
                'PackingGrade' => $info['Grade'],
                'ShortName' => $info['Variety'] . '-' . $info['Species'],
                'BalanceQty' => $info['TotalWeight'],
                'TotalBalanceQty' => number_format($totalQty, 3),
                'BalanceData' => $balanceData,
            ];
            $grandBalanceQty += $totalQty;
        }

        if ($grandBalanceQty != 0) {
            return [
                'GrandBalanceQty' => number_format($grandBalanceQty, 3),
                'ArrayVarietyData' => $arrayVarietyData,
            ];
        }

        return [];
    }

    /**
     * OPTIMIZATION: Same N+1 elimination for Thawing Meat balance data.
     * Uses single bulk query instead of per-TrackID queries.
     */
    private function getThawingMeatBalanceData( $trackIDs)
    {
        if (empty($trackIDs)) {
            return [];
        }

        $trackIDList = $this->buildInClause($trackIDs);

        $this->conn->prepare("DROP TABLE IF EXISTS `ThawingMeatPivotTemp`")->execute();

        $sql = "CREATE TABLE ThawingMeatPivotTemp AS
            SELECT
                PHGB.ReceivingID AS PivotTrackID,
                ShortName AS Variety,
                Species,
                CSD.Grade AS Grade,
                (SELECT COALESCE(ROUND(SUM(COALESCE(WtAftrVA, 0)), 3), 0)
                 FROM PpcValueAddition AS PVA WHERE PVA.TrackID = PHGB.ReceivingID)
                -
                (SELECT COALESCE(ROUND(SUM(COALESCE(SoakInQty, 0)), 3), 0)
                 FROM ProdSoakingDetails AS PSD WHERE PSD.TrackID = PHGB.ReceivingID)
                AS Weight
            FROM RmReceivingBreakup AS PHGB
            LEFT JOIN CsStockData AS CSD ON CSD.StockID = PHGB.CsStockID
            LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID = CSD.VarietyID
            WHERE ReceivingID IN ($trackIDList)
            GROUP BY ShortName, Species, CSD.Grade, ReceivingID
            HAVING Weight > 0
            ORDER BY ABS(CSD.Grade)";

        $stmtCreate = $this->conn->prepare($sql);
        $stmtCreate->execute();

        // Single bulk query
        $sqlBulk = "SELECT PivotTrackID, Variety, Species, Grade, Weight FROM ThawingMeatPivotTemp";
        $stmtBulk = $this->conn->prepare($sqlBulk);
        $stmtBulk->execute();

        $lookup = [];
        $summary = [];
        while ($row = $stmtBulk->fetch(PDO::FETCH_ASSOC)) {
            $key = $row['Variety'] . '|' . $row['Species'] . '|' . $row['Grade'];
            $trackID = (int)$row['PivotTrackID'];
            $weight = (float)$row['Weight'];

            $lookup[$key][$trackID] = $weight;

            if (!isset($summary[$key])) {
                $summary[$key] = [
                    'Variety' => $row['Variety'],
                    'Species' => $row['Species'],
                    'Grade' => $row['Grade'],
                    'TotalWeight' => 0,
                ];
            }
            if ($weight > 1) {
                $summary[$key]['TotalWeight'] += $weight;
            }
        }

        $arrayVarietyData = [];
        $grandBalanceQty = 0;
        $sno = 0;

        foreach ($summary as $key => $info) {
            $balanceData = [];
            $totalQty = 0;

            foreach ($trackIDs as $trackID) {
                $val = $lookup[$key][$trackID] ?: 0;
                $balanceData[] = ['Balance' => ($val <= 1) ? '-' : number_format($val, 3)];
                if ($val > 1) {
                    $totalQty += $val;
                }
            }

            $arrayVarietyData[] = [
                'SNO' => ++$sno,
                'PackingGrade' => $info['Grade'],
                'ShortName' => $info['Variety'] . '-' . $info['Species'],
                'BalanceQty' => $info['TotalWeight'],
                'TotalBalanceQty' => number_format($totalQty, 3),
                'BalanceData' => $balanceData,
            ];
            $grandBalanceQty += $totalQty;
        }

        if ($grandBalanceQty != 0) {
            return [
                'GrandBalanceQty' => number_format($grandBalanceQty, 3),
                'ArrayVarietyData' => $arrayVarietyData,
            ];
        }

        return [];
    }
}