<?php

class VarietywiseStockReportNewController
{
    private $conn;
    private $Error = null;
    private $Msg = '';
    private $VarietyOrderBy;
    private $PackingStyleOrderBy;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->initializeOrderByStatements();
    }

    /**
     * Initialize ORDER BY clauses
     */
    private function initializeOrderByStatements()
    {
        $this->PackingStyleOrderBy = " CASE 
                WHEN APS.PackingStyle LIKE '%KGS%' THEN 1
                WHEN APS.PackingStyle LIKE '%OZ%' THEN 2
                ELSE 3 END ";
                
        $this->VarietyOrderBy = " ORDER BY 
                                    CASE 
                                    WHEN TypeProduct = 'RAW' THEN 1
                                    WHEN TypeProduct = 'COOKED' THEN 2
                                    ELSE 3 END,
                                    CASE
                                    WHEN ShortName = 'HLSO' THEN 1
                                    WHEN ShortName = 'PVPD' THEN 2
                                    WHEN ShortName = 'PDTO' THEN 3
                                    WHEN ShortName = 'PD' THEN 4
                                    WHEN ShortName = 'EZPL' THEN 5
                                    ELSE 6 END,
                                    CASE
                                    WHEN Treatment = 'UNT' THEN 1
                                    WHEN Treatment = 'NPT' THEN 2
                                    WHEN Treatment = 'SALT' THEN 3
                                    WHEN Treatment = 'STPP' THEN 4
                                    ELSE 5 END ";
    }

    /**
     * Build WHERE clause from request body parameters
     * Prevents SQL Injection using prepared statements
     */
    private function buildWhereConditions($RequestBody, $tablePrefix = 'CPID')
    {
        $conditions = [];
        $params = [];

        if (!empty($RequestBody['ColdstoreID'])) {
            $conditions[] = " {$tablePrefix}.UnitID = ? ";
            $params[] = $RequestBody['ColdstoreID'];
        }

        if (!empty($RequestBody['FreezingType'])) {
            $conditions[] = " PIVL.FreezingType = ? ";
            $params[] = $RequestBody['FreezingType'];
        }

        if (!empty($RequestBody['PackingStyle'])) {
            $conditions[] = " {$tablePrefix}.PackingStyleID = ? ";
            $params[] = $RequestBody['PackingStyle'];
        }

        if (!empty($RequestBody['Grade'])) {
            $conditions[] = " {$tablePrefix}.Grade = ? ";
            $params[] = $RequestBody['Grade'];
        }

        if (!empty($RequestBody['Species'])) {
            $conditions[] = " PIVL.Species = ? ";
            $params[] = $RequestBody['Species'];
        }

        return [
            'whereClause' => !empty($conditions) ? ' AND ' . implode(' AND ', $conditions) : '',
            'params' => $params
        ];
    }

    /**
     * Main report generation method
     */
    public function getStockReportData($RequestBody)
    {
        try {
            $ProductionDate = $RequestBody['ProductionDate'];
            
            $conditions = $this->buildWhereConditions($RequestBody);
            $whereClause = $conditions['whereClause'];
            $params = $conditions['params'];

            // Get variety data
            $ArrayTypeData = $this->getVarietyTypeData(
                $RequestBody, 
                $whereClause, 
                $params
            );

            // Get grand totals
            $GrandTotals = $this->getGrandTotalsOptimized(
                $RequestBody, 
                $whereClause, 
                $params
            );

            $ReturnCorgos = $this->GetReturnCargosOptimized($RequestBody);
            $AntiBioticData = $this->getAntiBioticDataOptimized($RequestBody);
            $ShipmentData = $this->getShipmentData($ProductionDate);

            $StockOutDate = date('Y-m-d', strtotime($ProductionDate . '-1 day'));
            $IBTInData = $this->getIBTInDataByDateOptimized($StockOutDate, $whereClause, $params);
            $IBTOutData = $this->getIBTOutDataByDateOptimized($StockOutDate, $whereClause, $params);
            $ConversionsData = $this->getConversionsDataByDateOptimized($StockOutDate, $whereClause, $params);

            return [
                'Error' => null,
                'Msg' => 'Success',
                'Data' => $ArrayTypeData,
                'ReturnCorgosData' => $ReturnCorgos,
                'AntiBioticData' => $AntiBioticData,
                'GrandTotals' => $GrandTotals,
                'ShipmentData' => $ShipmentData,
                'IBTInData' => $IBTInData,
                'ConversionsData' => $ConversionsData,
                'IBTOutData' => $IBTOutData
            ];
        } catch (Exception $e) {
            error_log("Error in getStockReportData: " . $e->getMessage());
            return [
                'Error' => $e->getMessage(),
                'Msg' => 'Error processing stock report',
                'Data' => []
            ];
        }
    }

    /**
     * Get variety type data
     */
    private function getVarietyTypeData($RequestBody, $whereClause, $params)
    {
        $ProductionDate = $RequestBody['ProductionDate'];
        $Type = $RequestBody['Type'];

        $varietyQuery = "SELECT DISTINCT TypeProduct, ShortName, Treatment 
                        FROM ScrmPIVarietyList 
                        WHERE 1=1 
                        " . $this->buildVarietyFilters($RequestBody) . "
                        GROUP BY TypeProduct, ShortName, Treatment
                        " . $this->VarietyOrderBy;

        $stmt = $this->conn->prepare($varietyQuery);
        $varietyParams = $this->buildVarietyParams($RequestBody);
        $stmt->execute($varietyParams);

        $ArrayTypeData = [];
        while ($rowType = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $typeData = $this->buildTypeProductData(
                $rowType,
                $RequestBody,
                $whereClause,
                $params
            );
            
            if ($typeData !== null) {
                $ArrayTypeData[] = $typeData;
            }
        }

        return $ArrayTypeData;
    }

    /**
     * Build variety filter conditions
     */
    private function buildVarietyFilters($RequestBody)
    {
        $filters = '';
        
        if (!empty($RequestBody['TypeProduct'])) {
            $filters .= " AND TypeProduct = ? ";
        }
        if (!empty($RequestBody['Variety'])) {
            $filters .= " AND ShortName = ? ";
        }
        if (!empty($RequestBody['PackingStyle'])) {
            $filters .= " AND PackingStyle = ? ";
        }
        if (!empty($RequestBody['Treatment'])) {
            $filters .= " AND Treatment = ? ";
        }
        
        return $filters;
    }

    /**
     * Build variety query parameters
     */
    private function buildVarietyParams($RequestBody)
    {
        $params = [];
        
        if (!empty($RequestBody['TypeProduct'])) {
            $params[] = $RequestBody['TypeProduct'];
        }
        if (!empty($RequestBody['Variety'])) {
            $params[] = $RequestBody['Variety'];
        }
        if (!empty($RequestBody['PackingStyle'])) {
            $params[] = $RequestBody['PackingStyle'];
        }
        if (!empty($RequestBody['Treatment'])) {
            $params[] = $RequestBody['Treatment'];
        }
        
        return $params;
    }

    /**
     * Build type product data with all related information
     */
    private function buildTypeProductData($rowType, $RequestBody, $whereClause, $params)
    {
        $TypeProductRow = $rowType['TypeProduct'];
        $ShortNameRow = $rowType['ShortName'];
        $SockingTypeRow = $rowType['Treatment'];
        $ProductionDate = $RequestBody['ProductionDate'];
        $Type = $RequestBody['Type'];

        $CombinationData = $this->getCombinationDataOptimized(
            $whereClause,
            $params,
            $TypeProductRow,
            $ProductionDate,
            $ShortNameRow,
            $SockingTypeRow,
            $Type
        );

        if (empty($CombinationData)) {
            return null;
        }

        return [
            'TypeProduct' => "{$TypeProductRow} {$ShortNameRow} {$SockingTypeRow}",
            'ShortName' => $ShortNameRow,
            'Treatment' => $SockingTypeRow,
            'VarietyCombinationData' => $CombinationData['combinations'],
            'GradesData' => $CombinationData['grades'],
            'GrandTotals' => $CombinationData['totals'],
            'FreezingData' => $CombinationData['freezing']
        ];
    }

    /**
     * Get combination data with grades, totals, and freezing info
     */
    private function getCombinationDataOptimized(
        $whereClause,
        $params,
        $TypeProduct,
        $ProductionDate,
        $ShortName,
        $SockingType,
        $Type
    ) {
        try {
            $sqlCombinations = "SELECT 
                                CPID.PackingStyleID, 
                                APS.PackingStyle, 
                                PIVL.ShortName, 
                                CPID.SockingType, 
                                PIVL.Species, 
                                PIVL.Glaze, 
                                PIVL.GlazePerc,
                                CPID.Grade
                            FROM CsStockData AS CPID
                            LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID = PIVL.VarietyID  
                            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
                            LEFT JOIN CsBarCodes AS CBS ON CBS.BarCodeID = CPID.BarCodeID
                            WHERE CPID.ProductionDate <= ?
                            AND PIVL.TypeProduct = ?
                            AND PIVL.ShortName = ?
                            AND CPID.SockingType = ?
                            " . $whereClause . "
                            GROUP BY CPID.PackingStyleID, PIVL.Species, PIVL.Glaze, CPID.Grade
                            ORDER BY PIVL.ShortName, " . $this->PackingStyleOrderBy;

            $stmtCombinations = $this->conn->prepare($sqlCombinations);
            $combinationParams = array_merge(
                [$ProductionDate, $TypeProduct, $ShortName, $SockingType],
                $params
            );
            $stmtCombinations->execute($combinationParams);

            $combinations = [];
            $gradesMap = [];
            $totals = [];

            while ($row = $stmtCombinations->fetch(PDO::FETCH_ASSOC)) {
                $combinations[] = [
                    'PackingStyle' => $row['PackingStyle'],
                    'PackingStyleID' => $row['PackingStyleID'],
                    'ShortName' => $row['ShortName'],
                    'Species' => $row['Species'],
                    'SockingType' => $row['SockingType'],
                    'Glaze' => $row['Glaze'],
                    'GlazePerc' => $row['GlazePerc']
                ];

                // Process grade data
                $gradeValue = $this->getGradewiseValuesOptimized(
                    $row,
                    $Type,
                    $TypeProduct,
                    $ProductionDate,
                    $whereClause,
                    $params
                );

                if (!isset($gradesMap[$row['Grade']])) {
                    $gradesMap[$row['Grade']] = [
                        'Grade' => $row['Grade'],
                        'GradeValueData' => []
                    ];
                }
                $gradesMap[$row['Grade']]['GradeValueData'][] = $gradeValue;
            }

            $totals = $this->getVarietywiseTotalsOptimized(
                $combinations,
                $Type,
                $TypeProduct,
                $ProductionDate,
                $whereClause,
                $params
            );

            return [
                'combinations' => $combinations,
                'grades' => array_values($gradesMap),
                'totals' => $totals,
                'freezing' => $this->getVarietywiseFreezingDataOptimized(
                    $combinations,
                    $ProductionDate,
                    $Type
                )
            ];

        } catch (Exception $e) {
            error_log("Error in getCombinationDataOptimized: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get gradewise values using prepared statements
     */
    private function getGradewiseValuesOptimized(
        $combinationRow,
        $Type,
        $TypeProduct,
        $ProductionDate,
        $whereClause,
        $params
    ) {
        try {
            $sqlInFields = "SUM(ActualMcs) AS AvalMcs, 
                            SUM(ActualLooseBags) AS LooseBags, 
                            SUM(ActualKgs) AS AvalKgs, 
                            FLOOR(SUM(ActualLooseBags)/COALESCE(TotalSlabs, 1)) AS LooseMcs";

            $sqlInSelect = "SELECT $sqlInFields FROM CsStockData AS CPID
                            LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID = PIVL.VarietyID  
                            LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
                            LEFT JOIN CsBarCodes AS CBS ON CBS.BarCodeID = CPID.BarCodeID
                            WHERE CPID.ProductionDate <= ?
                            AND CPID.Grade = ?
                            AND APS.PackingStyle = ?
                            AND PIVL.ShortName = ?
                            AND CPID.SockingType = ?
                            AND PIVL.Species = ?
                            AND PIVL.Glaze = ?
                            AND PIVL.TypeProduct = ?
                            " . $whereClause;

            $stmtInSelect = $this->conn->prepare($sqlInSelect);
            $inParams = array_merge(
                [
                    $ProductionDate,
                    $combinationRow['Grade'],
                    $combinationRow['PackingStyle'],
                    $combinationRow['ShortName'],
                    $combinationRow['SockingType'],
                    $combinationRow['Species'],
                    $combinationRow['Glaze'],
                    $TypeProduct
                ],
                $params
            );
            $stmtInSelect->execute($inParams);
            $result = $stmtInSelect->fetch(PDO::FETCH_ASSOC);
            $inData = $result !== null ? $result : [];

            // Get OUT data
            $sqlOutFields = "SUM(OutMcs) AS OutMcs, 
                             SUM(OutLooseBags) AS OutLooseBags, 
                             SUM(OutMcs*COALESCE(TotalSlabs, 1)*SlabWeight*MFactor) AS OutKGs, 
                             SUM(OutLooseBags*SlabWeight*MFactor) AS OutLKGs,
                             FLOOR(SUM(OutLooseBags)/COALESCE(TotalSlabs, 1)) AS OutLooseMcs";

            $sqlOutSelect = "SELECT $sqlOutFields FROM CsStockOutDetails AS CSOD
                             LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
                             LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID = PIVL.VarietyID  
                             LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
                             LEFT JOIN CsBarCodes AS CBS ON CBS.BarCodeID = CPID.BarCodeID
                             WHERE CSOD.OutDate <= ?
                             AND CPID.Grade = ?
                             AND APS.PackingStyle = ?
                             AND PIVL.ShortName = ?
                             AND CPID.SockingType = ?
                             AND PIVL.Species = ?
                             AND PIVL.Glaze = ?
                             AND PIVL.TypeProduct = ?
                             " . $whereClause;

            $stmtOutSelect = $this->conn->prepare($sqlOutSelect);
            $outParams = array_merge(
                [
                    $ProductionDate,
                    $combinationRow['Grade'],
                    $combinationRow['PackingStyle'],
                    $combinationRow['ShortName'],
                    $combinationRow['SockingType'],
                    $combinationRow['Species'],
                    $combinationRow['Glaze'],
                    $TypeProduct
                ],
                $params
            );
            $stmtOutSelect->execute($outParams);
            $resultOut = $stmtOutSelect->fetch(PDO::FETCH_ASSOC);
            $outData = $resultOut !== null ? $resultOut : [];

            return $this->calculateBalance($inData, $outData, $Type);
        } catch (Exception $e) {
            error_log("Error in getGradewiseValuesOptimized: " . $e->getMessage());
            return ['Value' => '0'];
        }
    }

    /**
     * Calculate balance between IN and OUT data
     */
    private function calculateBalance($inData, $outData, $Type)
    {
        $InMcs = ($inData['AvalMcs'] ? $inData['AvalMcs'] : 0) + ($inData['LooseMcs'] ? $inData['LooseMcs'] : 0);
        $OutMcs = ($outData['OutMcs'] ? $outData['OutMcs'] : 0) + ($outData['OutLooseMcs'] ? $outData['OutLooseMcs'] : 0);
        $Mcs = $InMcs - $OutMcs;

        $InKgs = $inData['AvalKgs'] ? $inData['AvalKgs'] : 0;
        $OutKGs = ($outData['OutKGs'] ? $outData['OutKGs'] : 0) + ($outData['OutLKGs'] ? $outData['OutLKGs'] : 0);
        $Kgs = $InKgs - $OutKGs;

        if ($Type == 'MCS') {
            return ['Value' => intval($Mcs)];
        } else if ($Type == 'KGs') {
            return ['Value' => $Kgs ? round($Kgs, 3) : '0'];
        }

        return ['Value' => '0'];
    }

    /**
     * Get varietywise totals (optimized)
     */
    private function getVarietywiseTotalsOptimized(
        $combinations,
        $Type,
        $TypeProduct,
        $ProductionDate,
        $whereClause,
        $params
    ) {
        $TotalValues = [];

        foreach ($combinations as $Item) {
            try {
                $sql = "SELECT 
                        SUM(CPID.ActualMcs) AS AvalMcs,
                        SUM(CPID.ActualLooseBags) AS LooseBags,
                        SUM(CPID.ActualKgs) AS AvalKgs,
                        FLOOR(SUM(CPID.ActualLooseBags)/COALESCE(CPID.TotalSlabs, 1)) AS LooseSlabs
                        FROM CsStockData AS CPID
                        LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID = PIVL.VarietyID  
                        LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
                        LEFT JOIN CsBarCodes AS CBS ON CBS.BarCodeID = CPID.BarCodeID
                        WHERE CPID.ProductionDate <= ?
                        AND APS.PackingStyle = ?
                        AND PIVL.ShortName = ?
                        AND CPID.SockingType = ?
                        AND PIVL.Species = ?
                        AND PIVL.Glaze = ?
                        AND PIVL.TypeProduct = ?
                        " . $whereClause;

                $stmt = $this->conn->prepare($sql);
                $totalParams = array_merge(
                    [
                        $ProductionDate,
                        $Item['PackingStyle'],
                        $Item['ShortName'],
                        $Item['SockingType'],
                        $Item['Species'],
                        $Item['Glaze'],
                        $TypeProduct
                    ],
                    $params
                );
                $stmt->execute($totalParams);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                $inMcs = ($result['AvalMcs'] ? $result['AvalMcs'] : 0) + ($result['LooseSlabs'] ? $result['LooseSlabs'] : 0);

                // Get OUT totals
                $sqlOut = "SELECT SUM(OutMcs) AS OutMcs, 
                           FLOOR(SUM(OutLooseBags)/COALESCE(TotalSlabs, 1)) AS OutLooseMcs,
                           SUM(OutMcs*COALESCE(TotalSlabs, 1)*SlabWeight*MFactor) AS OutKGs,
                           SUM(OutLooseBags*SlabWeight*MFactor) AS OutLKGs
                           FROM CsStockOutDetails AS CSOD
                           LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
                           LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID = PIVL.VarietyID  
                           LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
                           LEFT JOIN CsBarCodes AS CBS ON CBS.BarCodeID = CPID.BarCodeID
                           WHERE CSOD.OutDate <= ?
                           AND APS.PackingStyle = ?
                           AND PIVL.ShortName = ?
                           AND CPID.SockingType = ?
                           AND PIVL.Species = ?
                           AND PIVL.Glaze = ?
                           AND PIVL.TypeProduct = ?
                           " . $whereClause;

                $stmtOut = $this->conn->prepare($sqlOut);
                $stmtOut->execute(array_merge(
                    [
                        $ProductionDate,
                        $Item['PackingStyle'],
                        $Item['ShortName'],
                        $Item['SockingType'],
                        $Item['Species'],
                        $Item['Glaze'],
                        $TypeProduct
                    ],
                    $params
                ));
                $resultOut = $stmtOut->fetch(PDO::FETCH_ASSOC);

                $outMcs = ($resultOut['OutMcs'] ? $resultOut['OutMcs'] : 0) + ($resultOut['OutLooseMcs'] ? $resultOut['OutLooseMcs'] : 0);
                $totalMcs = $inMcs - $outMcs;

                if ($Type == 'KGs') {
                    $inKgs = $result['AvalKgs'] ? $result['AvalKgs'] : 0;
                    $outKgs = ($resultOut['OutKGs'] ? $resultOut['OutKGs'] : 0) + ($resultOut['OutLKGs'] ? $resultOut['OutLKGs'] : 0);
                    $totalMcs = round($inKgs - $outKgs, 2);
                }

                $TotalValues[] = ['TotalValue' => $totalMcs];
            } catch (Exception $e) {
                error_log("Error calculating total for combination: " . $e->getMessage());
                $TotalValues[] = ['TotalValue' => 0];
            }
        }

        return $TotalValues;
    }

    /**
     * Get varietywise freezing data
     */
    private function getVarietywiseFreezingDataOptimized($combinations, $ProductionDate, $Type)
    {
        $TotalValues = [];

        foreach ($combinations as $Item) {
            try {
                $sql = "SELECT 
                        SUM(ProductionWeight) AS ProductionWeight,
                        APS.MFactor,
                        APS.SlabWeight,
                        APS.TotalSlabs 
                        FROM ProdFreezingData AS PFD
                        LEFT JOIN ScrmPIVarietyList AS SPVL ON PFD.VarietyID = SPVL.VarietyID 
                        LEFT JOIN AdminPackingStyles AS APS ON PFD.PackingStyleID = APS.PCKID
                        WHERE PFD.ProductionDate = ?
                        AND APS.PackingStyle = ?
                        AND SPVL.ShortName = ?
                        AND SPVL.Treatment = ?
                        AND SPVL.Species = ?
                        AND SPVL.Glaze = ?";

                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    $ProductionDate,
                    $Item['PackingStyle'],
                    $Item['ShortName'],
                    $Item['SockingType'],
                    $Item['Species'],
                    $Item['Glaze']
                ]);

                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $ProductionWeight = $result['ProductionWeight'] ? $result['ProductionWeight'] : 0;

                if ($Type == 'MCS' && $ProductionWeight > 0) {
                    $slabs = (($ProductionWeight / $result['MFactor']) / $result['SlabWeight']) / $result['TotalSlabs'];
                    $TotalValues[] = ['FreezingWeight' => round($slabs, 2)];
                } else {
                    $TotalValues[] = ['FreezingWeight' => $Type == 'MCS' ? 0 : round($ProductionWeight, 2)];
                }
            } catch (Exception $e) {
                error_log("Error in getVarietywiseFreezingDataOptimized: " . $e->getMessage());
                $TotalValues[] = ['FreezingWeight' => 0];
            }
        }

        return $TotalValues;
    }

    /**
     * Get grand totals (optimized version)
     */
    private function getGrandTotalsOptimized($RequestBody, $whereClause, $params)
    {
        try {
            $ProductionDate = $RequestBody['ProductionDate'];
            $Type = $RequestBody['Type'];

            $sql = "SELECT 
                    TypeProduct, 
                    FreezingType,
                    SUM(ActualMcs) AS InMcs,
                    SUM(ActualLooseBags) AS LooseBags,
                    SUM(ActualKgs) AS Kgs
                    FROM CsStockData AS CPID
                    LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID = PIVL.VarietyID
                    LEFT JOIN AdminPackingStyles AS APS ON CPID.PackingStyleID = APS.PCKID
                    WHERE CPID.ProductionDate <= ?
                    " . $whereClause . "
                    GROUP BY PIVL.TypeProduct, PIVL.FreezingType";

            $stmt = $this->conn->prepare($sql);
            $totalParams = array_merge([$ProductionDate], $params);
            $stmt->execute($totalParams);

            $ArrayTypeData = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $TypeProductCombination = $row['TypeProduct'] . ' - ' . $row['FreezingType'];
                $value = $Type == 'MCS' ? intval($row['InMcs']) : round($row['Kgs'], 3);
                $ArrayTypeData[$TypeProductCombination]['Value'] = $value;
            }

            return $ArrayTypeData;
        } catch (Exception $e) {
            error_log("Error in getGrandTotalsOptimized: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Return Cargos data (optimized)
     */
    private function GetReturnCargosOptimized($RequestBody)
    {
        try {
            $ProductionDate = $RequestBody['ProductionDate'];
            $Type = $RequestBody['Type'];

            $conditions = $this->buildWhereConditions($RequestBody);
            $whereClause = $conditions['whereClause'];
            $params = $conditions['params'];

            // Add return container filter
            $whereClause .= " AND ProductionTypeID = ? ";
            $params[] = 'Return Container';

            $sql = "SELECT DISTINCT TypeProduct, ShortName, Treatment 
                    FROM ScrmPIVarietyList SPVL 
                    LEFT JOIN CsStockData AS CPID ON CPID.VarietyID = SPVL.VarietyID  
                    WHERE 1=1 " . $whereClause . "
                    GROUP BY TypeProduct, ShortName, Treatment
                    " . $this->VarietyOrderBy;

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);

            $ArrayTypeData = [];
            while ($rowType = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $TypeProductRow = $rowType['TypeProduct'];
                $ShortNameRow = $rowType['ShortName'];
                $SockingTypeRow = $rowType['Treatment'];

                $CombinationData = $this->getCombinationDataOptimized(
                    $whereClause,
                    $params,
                    $TypeProductRow,
                    $ProductionDate,
                    $ShortNameRow,
                    $SockingTypeRow,
                    $Type
                );

                if (!empty($CombinationData)) {
                    $ArrayTypeData[] = [
                        'TypeProduct' => "{$TypeProductRow} {$ShortNameRow} {$SockingTypeRow}",
                        'ShortName' => $ShortNameRow,
                        'Treatment' => $SockingTypeRow,
                        'VarietyCombinationData' => $CombinationData['combinations'],
                        'GradesData' => $CombinationData['grades'],
                        'GrandTotals' => $CombinationData['totals'],
                        'FreezingData' => $CombinationData['freezing']
                    ];
                }
            }

            return $ArrayTypeData;
        } catch (Exception $e) {
            error_log("Error in GetReturnCargosOptimized: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get AntiBoitic data (optimized)
     */
    private function getAntiBioticDataOptimized($RequestBody)
    {
        try {
            $ProductionDate = $RequestBody['ProductionDate'];
            $Type = $RequestBody['Type'];

            $conditions = $this->buildWhereConditions($RequestBody, 'PFD');
            $whereClause = $conditions['whereClause'];
            $params = $conditions['params'];

            // Add antibiotic filter
            $whereClause .= " AND AbStatus = ? ";
            $params[] = 'AB+';

            $sql = "SELECT DISTINCT TypeProduct, ShortName, Treatment 
                    FROM ScrmPIVarietyList SPVL
                    LEFT JOIN ProdFreezingData PFD ON PFD.VarietyID = SPVL.VarietyID
                    WHERE 1=1 " . $whereClause . "
                    GROUP BY TypeProduct, ShortName, Treatment
                    " . $this->VarietyOrderBy;

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);

            $ArrayTypeData = [];
            while ($rowType = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $TypeProductRow = $rowType['TypeProduct'];
                $ShortNameRow = $rowType['ShortName'];
                $SockingTypeRow = $rowType['Treatment'];

                $CombinationData = $this->getCombinationDataOptimized(
                    $whereClause,
                    $params,
                    $TypeProductRow,
                    $ProductionDate,
                    $ShortNameRow,
                    $SockingTypeRow,
                    $Type
                );

                if (!empty($CombinationData)) {
                    $ArrayTypeData[] = [
                        'TypeProduct' => "{$TypeProductRow} {$ShortNameRow} {$SockingTypeRow}",
                        'ShortName' => $ShortNameRow,
                        'Treatment' => $SockingTypeRow,
                        'VarietyCombinationData' => $CombinationData['combinations'],
                        'GradesData' => $CombinationData['grades'],
                        'GrandTotals' => $CombinationData['totals'],
                        'FreezingData' => $CombinationData['freezing']
                    ];
                }
            }

            return $ArrayTypeData;
        } catch (Exception $e) {
            error_log("Error in getAntiBioticDataOptimized: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get shipment data (optimized)
     */
    public function getShipmentData($ProductionDate)
    {
        try {
            if (empty($ProductionDate)) {
                return [];
            }

            $sql = "SELECT 
                    SPD.InvNo,
                    CSD.PoID,
                    CSOD.OutType,
                    SPD.PoNo,
                    SPD.InvNumFull,
                    SCD.ConsigneeName,
                    (SELECT SUM(Mcs) FROM ScrmPoGradeData WHERE InvId = SPD.PoID) AS Mcs
                    FROM CsStockOutDetails CSOD
                    RIGHT JOIN CsStockData CSD ON CSD.StockID = CSOD.StockID
                    LEFT JOIN ScrmPoData SPD ON SPD.PoID = CSD.PoID
                    LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID = SPD.ConsigneeID
                    WHERE CSOD.OutType = 'Shipment Out' 
                    AND CSOD.OutDate = ?
                    GROUP BY CSD.PoID";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$ProductionDate]);

            $ShipmentArr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ShipmentInfo = [
                    'ShipmentData' => 'SHIPMENT LESS : ' . ($row['ConsigneeName'] ? $row['ConsigneeName'] : 'N/A') .
                                     ', PO.NO : ' . ($row['PoNo'] ? $row['PoNo'] : 'N/A') .
                                     ', ' . ($row['InvNumFull'] ? $row['InvNumFull'] : 'N/A') .
                                     ' = ' . ($row['Mcs'] ? $row['Mcs'] : 0) . ' M/CTNS'
                ];

                // Get shipment breakup
                $breakupSql = "SELECT CONCAT(
                            (SELECT InvShortCode FROM AdminExporterDetails AED 
                             LEFT JOIN ScrmPoData SPD ON AED.ExpID = SPD.ExporterID
                             WHERE SPD.PoID = PGD.InvId GROUP BY AED.ExpID),
                            ' : ', Species, ' ', ShortName, ' - ', APS.PackingStyle, 
                            ' (', PVL.Treatment, ') - ', PGD.Grade, ' = ', Mcs, ' M/CTNS'
                            ) AS Description 
                        FROM ScrmPoGradeData AS PGD
                        LEFT JOIN ScrmPIVarietyList AS PVL ON PGD.VarietyID = PVL.VarietyID
                        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = PVL.PackingStyle
                        WHERE PGD.InvId = ?
                        ORDER BY PGD.ID ASC";

                $stmtBreakup = $this->conn->prepare($breakupSql);
                $stmtBreakup->execute([$row['PoID']]);

                $breakupArr = [];
                while ($breakupRow = $stmtBreakup->fetch(PDO::FETCH_ASSOC)) {
                    $breakupArr[] = $breakupRow['Description'];
                }
                $ShipmentInfo['ShipmentBreakUp'] = $breakupArr;
                $ShipmentArr[] = $ShipmentInfo;
            }

            return $ShipmentArr;
        } catch (Exception $e) {
            error_log("Error in getShipmentData: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get IBT IN data by date (optimized)
     */
    private function getIBTInDataByDateOptimized($StockOutDate, $whereClause, $params)
    {
        try {
            if (empty($StockOutDate)) {
                return [];
            }

            $sql = "SELECT 
                    CIRD.FromStore,
                    RPS.StationName AS FromStoreName
                    FROM CsStockData AS CPID
                    LEFT JOIN CsDailyProductionData AS CSDP ON CSDP.ProductionID = CPID.ProductionID
                    LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID = CPID.VarietyID
                    LEFT JOIN CsIBTRequestDetails AS CIRD ON CSDP.IBTOutID = CIRD.IBTID
                    LEFT JOIN RmProcessingStations AS RPS ON RPS.ProcessingStationID = CIRD.FromPlantID
                    WHERE CPID.ProductionDate = ?
                    AND CPID.ProductionTypeID = 'IBT IN'
                    " . $whereClause . "
                    GROUP BY CIRD.FromStore";

            $stmt = $this->conn->prepare($sql);
            $stmtParams = array_merge([$StockOutDate], $params);
            $stmt->execute($stmtParams);

            $IBTInArr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ResArr['FromStoreName'] = 'RECEIVED FROM ' . ($row['FromStoreName'] ? $row['FromStoreName'] : 'N/A');
                $fromStore = $row['FromStore'];

                // Get breakup
                $breakupSql = "SELECT 
                            SUM(CPID.ActualMcs) AS AvalMcs,
                            SUM(CPID.ActualLooseBags) AS LooseBags, 
                            PIVL.Species,
                            PIVL.ShortName, 
                            CPID.Grade,
                            APS.PackingStyle
                        FROM CsStockData AS CPID 
                        LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID = CPID.VarietyID
                        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CPID.PackingStyleID
                        LEFT JOIN CsDailyProductionData AS CSDP ON CSDP.ProductionID = CPID.ProductionID
                        LEFT JOIN CsIBTRequestDetails AS CIRD ON CSDP.IBTOutID = CIRD.IBTID
                        WHERE CIRD.FromStore = ?
                        AND CPID.ProductionDate = ?
                        " . $whereClause . "
                        GROUP BY CPID.VarietyID";

                $stmtBreakup = $this->conn->prepare($breakupSql);
                $breakupParams = array_merge([$fromStore, $StockOutDate], $params);
                $stmtBreakup->execute($breakupParams);

                $ResArr['Breakup'] = [];
                while ($rowBreakup = $stmtBreakup->fetch(PDO::FETCH_ASSOC)) {
                    $Breakup = $rowBreakup['Species'] . ' ' . $rowBreakup['ShortName'] . 
                              ' - ' . $rowBreakup['PackingStyle'] . ' - ' . $rowBreakup['Grade'] . 
                              ' = ' . $rowBreakup['AvalMcs'] . ' M/CTNS';
                    if ($rowBreakup['LooseBags'] != 0) {
                        $Breakup .= ' ' . $rowBreakup['LooseBags'] . ' LOOSE';
                    }
                    $ResArr['Breakup'][] = $Breakup;
                }
                $IBTInArr[] = $ResArr;
            }

            return $IBTInArr;
        } catch (Exception $e) {
            error_log("Error in getIBTInDataByDateOptimized: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get IBT OUT data by date (optimized)
     */
    private function getIBTOutDataByDateOptimized($StockOutDate, $whereClause, $params)
    {
        try {
            if (empty($StockOutDate)) {
                return [];
            }

            $sql = "SELECT 
                    CIRD.ToPlantID,
                    RPS2.StationName AS ToStoreName
                    FROM CsIBTRequestBreakUpDetails AS CITBD
                    LEFT JOIN CsIBTRequestDetails AS CIRD ON CITBD.IBTRequestID = CIRD.IBTID
                    LEFT JOIN CsStockData AS CPID ON CITBD.StockID = CPID.StockID
                    LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID = CPID.VarietyID
                    LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CPID.PackingStyleID
                    LEFT JOIN RmProcessingStations AS RPS2 ON RPS2.ProcessingStationID = CIRD.ToPlantID
                    WHERE CIRD.OrderDate = ?
                    " . $whereClause . "
                    GROUP BY CIRD.ToPlantID";

            $stmt = $this->conn->prepare($sql);
            $stmtParams = array_merge([$StockOutDate], $params);
            $stmt->execute($stmtParams);

            $IBTOutArr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ResArr['ToStoreName'] = 'SENT TO ' . ($row['ToStoreName'] ? $row['ToStoreName'] : 'N/A');
                $ToStore = $row['ToPlantID'];

                // Get breakup
                $breakupSql = "SELECT 
                            SUM(CITBD.NoOfMcs) AS MCS,
                            SUM(CITBD.NoOfLooseBags) AS LooseBags,
                            PIVL.Species,
                            PIVL.ShortName,
                            CPID.Grade,
                            APS.PackingStyle
                        FROM CsIBTRequestBreakUpDetails AS CITBD
                        LEFT JOIN CsIBTRequestDetails AS CIRD ON CITBD.IBTRequestID = CIRD.IBTID
                        LEFT JOIN CsStockData AS CPID ON CITBD.StockID = CPID.StockID
                        LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID = CPID.VarietyID
                        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CPID.PackingStyleID
                        WHERE CIRD.ToPlantID = ?
                        AND CIRD.OrderDate = ?
                        " . $whereClause . "
                        GROUP BY CPID.VarietyID";

                $stmtBreakup = $this->conn->prepare($breakupSql);
                $breakupParams = array_merge([$ToStore, $StockOutDate], $params);
                $stmtBreakup->execute($breakupParams);

                $ResArr['Breakup'] = [];
                while ($rowBreakup = $stmtBreakup->fetch(PDO::FETCH_ASSOC)) {
                    $Breakup = $rowBreakup['Species'] . ' ' . $rowBreakup['ShortName'] . 
                              ' - ' . $rowBreakup['PackingStyle'] . ' - ' . $rowBreakup['Grade'] . 
                              ' = ' . $rowBreakup['MCS'] . ' M/CTNS';
                    if ($rowBreakup['LooseBags'] != 0) {
                        $Breakup .= ' ' . $rowBreakup['LooseBags'] . ' LOOSE';
                    }
                    $ResArr['Breakup'][] = $Breakup;
                }
                $IBTOutArr[] = $ResArr;
            }

            return $IBTOutArr;
        } catch (Exception $e) {
            error_log("Error in getIBTOutDataByDateOptimized: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get conversions data by date (optimized)
     */
    private function getConversionsDataByDateOptimized($StockOutDate, $whereClause, $params)
    {
        try {
            if (empty($StockOutDate)) {
                return [];
            }

            $sql = "SELECT 
                    CSOD.OutType,
                    GROUP_CONCAT(DISTINCT CSOD.StockOutID) AS StockOutIDs,
                    SUM(CSOD.OutMcs) AS OutMcs,
                    SUM(CSOD.OutLooseBags) AS OutLooseBags,
                    PIVL.Species,
                    PIVL.ShortName,
                    CPID.Grade,
                    APS.PackingStyle
                    FROM CsStockOutDetails AS CSOD
                    LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
                    LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID = CPID.VarietyID
                    LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CPID.PackingStyleID
                    LEFT JOIN CsDailyProductionData AS CDPD ON CPID.ProductionID = CDPD.ProductionID
                    WHERE CSOD.OutDate = ?
                    AND CSOD.OutType IN ('Melting OUT', 'Melting IN', 'Melting', 'Repacking Out', 'Repacking')
                    " . $whereClause . "
                    GROUP BY CPID.VarietyID";

            $stmt = $this->conn->prepare($sql);
            $stmtParams = array_merge([$StockOutDate], $params);
            $stmt->execute($stmtParams);

            $ConversionArr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $Name = $row['Species'] . ' ' . $row['ShortName'] . ' ' . $row['PackingStyle'] . 
                       ' ' . $row['Grade'] . ' = ' . $row['OutMcs'] . ' M/CTNS';
                if ($row['OutLooseBags'] != 0) {
                    $Name .= ' ' . $row['OutLooseBags'] . ' LOOSE';
                }

                $StockOutIDs = $row['StockOutIDs'];

                // Get breakdown
                $breakdownSql = "SELECT 
                            CPID.StockID,
                            PIVL.Species,
                            PIVL.ShortName,
                            APS.PackingStyle,
                            CPID.Grade,
                            SUM(CPID.ActualMcs) AS AvalMcs,
                            SUM(CPID.ActualLooseBags) AS LooseBags
                        FROM CsDailyProductionData CDPD
                        LEFT JOIN CsStockData AS CPID ON CPID.ProductionID = CDPD.ProductionID
                        LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID = CPID.VarietyID
                        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CPID.PackingStyleID
                        WHERE CDPD.StockOutID IN (" . $StockOutIDs . ")
                        GROUP BY CPID.VarietyID";

                $stmtBreakdown = $this->conn->prepare($breakdownSql);
                $stmtBreakdown->execute();

                $breakdownArr = [];
                while ($rowB = $stmtBreakdown->fetch(PDO::FETCH_ASSOC)) {
                    $breakdownStr = $rowB['Species'] . ' ' . $rowB['ShortName'] . ' ' . 
                                   $rowB['PackingStyle'] . ' ' . $rowB['Grade'] . ' = ' . 
                                   $rowB['AvalMcs'] . ' M/CTNS';
                    if ($rowB['LooseBags'] != 0) {
                        $breakdownStr .= ' ' . $rowB['LooseBags'] . ' LOOSE';
                    }
                    $breakdownArr[] = $breakdownStr;
                }

                if (!empty($breakdownArr)) {
                    $Name .= ' CNVTD TO ' . implode('/', $breakdownArr);
                }

                $ConversionArr[] = ['Name' => $Name];
            }

            return $ConversionArr;
        } catch (Exception $e) {
            error_log("Error in getConversionsDataByDateOptimized: " . $e->getMessage());
            return [];
        }
    }
}
