<?php

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
//include_once 'Controller/UtilController.php';

class CreateStoreLayoutController
{
    private $conn;
    private $auditLogger;
    // private $db_table = "ScrmPIData";
    // private $utilController;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'ColdStore');
        // $this->utilController = new UtilController($this->conn);

    }
    // GET ALL




    public function createStoreLayout($RequestBody)
    {
        $Error = null;
        $BlockID = $RequestBody['BlockID'];
        $FloorID = $RequestBody['FloorID'];
        $StoreID = $RequestBody['StoreID'];//uma appoved by surya 04-02-2024

        $MaxFloor = $RequestBody['FloorID'];
        $MaxRowNumber = $RequestBody['Rows'];
        $MaxColumnNumber = $RequestBody['Column'];

        // $SqlCount = "SELECT RowNumber FROM `CsStoresRackData` WHERE BlockID= $BlockID AND FloorID=$FloorID GROUP BY RowNumber";
        $SqlCount = "SELECT RowNumber FROM `CsStoresRackData` WHERE BlockID= $BlockID AND FloorID=$FloorID AND StoreID=$StoreID GROUP BY RowNumber";
        $stmtCount = $this->conn->prepare($SqlCount);
        $stmtCount->execute();

        if ($stmtCount->rowCount() == 0) {

           //for ($FloorID = 1; $FloorID <= $MaxFloor; $FloorID++) {
                $RackID = 1;

                for ($ColumnNumber = 1; $ColumnNumber <= $MaxColumnNumber; $ColumnNumber++) {
                    $RackName = '';


                    for ($RowNumber = 1; $RowNumber <= $MaxRowNumber; $RowNumber++) {
                        $SqlInsert = "INSERT INTO `CsStoresRackData` (`StoreID`,`BlockID`, `FloorID`, `RowNumber`, `ColumnNumber`, `RackID`, `Position`, `Path`,
                                    `RackName`, `RackBarcode`, `RowCapacityInTonnes`, `RowPresentStock`, `BarCodeID`, `Status`)
                VALUES ('$StoreID','$BlockID','$FloorID','$RowNumber','$ColumnNumber','$RackID','0','0','$RackName','1','0','0','0','0')";
                        $stmt = $this->conn->prepare($SqlInsert);
                        $stmt->execute();
                        if ($stmt) {
                            $lastInsertedId = $this->conn->lastInsertId();
                            $createdRack = $this->auditLogger->fetchRowById('CsStoresRackData', 'RowID', $lastInsertedId);
                            $this->auditLogger->logInsert('CsStoresRackData', $lastInsertedId, $createdRack, $RequestBody, [
                                'ReferenceNo' => $StoreID,
                                'Remarks' => 'Cold store rack slot created',
                                'Async' => 1,
                            ]);
                        }
                    }
                }
           // }


        }

        return $this->getStoreLayout($RequestBody);


    }


    public function updateStoreLayout($RequestBody)
    {
        $Error = null;

        $ResObj = $RequestBody['ResObj'];

        foreach ($ResObj as $value) {
            foreach ($value['RowData'] as $Item) {

                $RackID = $Item['RackID'];
                $RackType = $Item['RackType'];
                $RackName = $Item['Name'];

                $SqlUpdate = "update `CsStoresRackData`  set RackName='$RackName' , Path='$RackType' where RowID=$RackID";
                $existingRack=[];
                //$existingRack = $this->auditLogger->fetchRowById('CsStoresRackData', 'RowID', $RackID);
                $stmt = $this->conn->prepare($SqlUpdate);
                $stmt->execute();
                if ($stmt) {
                    $updatedRack = $this->auditLogger->fetchRowById('CsStoresRackData', 'RowID', $RackID);
                    $this->auditLogger->logUpdate('CsStoresRackData', $RackID, $existingRack, $updatedRack, $RequestBody, [
                        'ReferenceNo' => $StoreID,
                        'Remarks' => 'Cold store rack layout updated',
                        'Async' => 1,
                    ]);
                }


            }
        }

return $this->getStoreLayout($RequestBody);


    }




    public function getStoreLayout($RequestBody)
    {




        $Error = null;
        $BlockID = $RequestBody['BlockID'];
        $FloorID = $RequestBody['FloorID'];
        $StoreID = $RequestBody['StoreID']; // uma (23-04-2024)

        $MaxFloor = $RequestBody['FloorID'];
        $MaxRowNumber = $RequestBody['Rows'];
        $MaxColumnNumber = $RequestBody['Column'];

        // $SqlCount = "SELECT RowNumber FROM `CsStoresRackData` WHERE BlockID= $BlockID AND FloorID=$FloorID GROUP BY RowNumber";
        $SqlCount = "SELECT RowNumber FROM `CsStoresRackData` WHERE BlockID= $BlockID AND FloorID=$FloorID AND StoreID=$StoreID GROUP BY RowNumber";
        $stmtCount = $this->conn->prepare($SqlCount);
        $stmtCount->execute();
        while ($rowNumber = $stmtCount->fetch(PDO::FETCH_ASSOC)) {
            $RowNumber = $rowNumber['RowNumber'];
            $ItemData['Row'] = $RowNumber;
            $ItemData['Name'] = 'test';
            $ItemData['RowData'] = $this->getRowData($RequestBody, $RowNumber);
            $AryData[] = $ItemData;

        }

        $totalAnalytics = array();

        // Total Cold Store Capacity (All Racks)
        $sqlGetTotalCapacity = "SELECT COUNT(*) AS TotalCapacity FROM CsStoresRackData WHERE path = 1  AND BlockID= $BlockID AND FloorID=$FloorID AND StoreID=$StoreID ";
        //error_log($sqlGetTotalCapacity);
        $stmtTotalCapacity = $this->conn->prepare($sqlGetTotalCapacity);
        $stmtTotalCapacity->execute();
        $rowTotalCapacity = $stmtTotalCapacity->fetch(PDO::FETCH_ASSOC);
        $totalAnalytics["TotalCapacity"] = $rowTotalCapacity['TotalCapacity'];

        // Used Capacity (Occupied Racks)
$sqlGetUsedCapacity = "
SELECT COUNT(DISTINCT CSR.RowID) AS UsedCapacity
FROM CsStoresRackData CSR
INNER JOIN CsStockData CSD
    ON CSD.StoreRackID = CSR.RowID
WHERE CSR.Status = 1
  AND CSR.BlockID = $BlockID
  AND CSR.FloorID = $FloorID
  AND CSR.StoreID = $StoreID
  AND CSD.StockStatus = 1
  AND (CSD.AvalMcs <> 0 OR CSD.LooseBags <> 0)
";

$stmtUsedCapacity = $this->conn->prepare($sqlGetUsedCapacity);
$stmtUsedCapacity->execute();
$rowUsedCapacity = $stmtUsedCapacity->fetch(PDO::FETCH_ASSOC);

$totalAnalytics["UsedCapacity"] = (int)$rowUsedCapacity['UsedCapacity'];


        // Free Capacity
        $totalAnalytics["FreeCapacity"] = $rowTotalCapacity['TotalCapacity'] - $rowUsedCapacity['UsedCapacity'];

    //variety-wise-storage
        $sqlGetVarietyWiseStorage = "SELECT
                                    V.ShortName AS Variety,
                                    SUM(S.AvalMcs) AS TotalMcs,
                                    SUM(S.AvalMcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor) AS Kgs,
                                    SUM(S.LooseBags * APS.SlabWeight * APS.MFactor) AS Lkgs
                                FROM CsStockData S
                                JOIN ScrmPIVarietyList V
                                    ON V.VarietyID = S.VarietyID
                                JOIN AdminPackingStyles APS
                                    ON APS.PCKID = S.PackingStyleID
                                JOIN CsStoresRackData R
                                    ON R.RowID = S.StoreRackID
                                WHERE
                                    (S.AvalMcs <> 0 OR S.LooseBags <> 0)
                                    AND R.BlockID = $BlockID
                                    AND R.FloorID = $FloorID
                                    AND R.StoreID = $StoreID

                                    /*AND S.StockStatus = 1*/

                                GROUP BY
                                    V.ShortName
                                ORDER BY
                                    Kgs DESC;
        ";

        //error_log($sqlGetVarietyWiseStorage);

        $stmtVarietyWiseStorage = $this->conn->prepare($sqlGetVarietyWiseStorage);
        $stmtVarietyWiseStorage->execute();
        $VarietyWiseStorage = [];
        while( $rowVarietyWiseStorage = $stmtVarietyWiseStorage->fetch(PDO::FETCH_ASSOC) ){
            $VarietyWiseStorage[] = $rowVarietyWiseStorage;
        }
        $totalAnalytics["VarietyWiseStorage"] = $VarietyWiseStorage;

        //Total Stored Weight
        $sqlGetTotalStoredWeight = "SELECT

            SUM(COALESCE(S.AvalKgs, 0)) AS TotalKgs,
            SUM(
                S.AvalMcs + (S.LooseBags / APS.TotalSlabs)

            ) AS TotalMcs,
            SUM(S.LooseBags * APS.SlabWeight * APS.MFactor) AS TotalLooseKgs,
            SUM(

                COALESCE(S.AvalKgs, 0)

            ) AS TotalStoredWeight
        FROM CsStockData S
        JOIN AdminPackingStyles APS
            ON APS.PCKID = S.PackingStyleID
        JOIN CsStoresRackData R
            ON R.RowID = S.StoreRackID
        JOIN CsStoresFloorData F
            ON F.FloorID = R.FloorID
        JOIN CsStoresBlockData B
            ON B.BlockID = R.BlockID
        JOIN CsStores ST
            ON ST.StoreID = R.StoreID
        WHERE
            (S.AvalMcs <> 0 OR S.LooseBags <> 0)
            AND R.BlockID = $BlockID
            AND R.FloorID = $FloorID
            AND R.StoreID = $StoreID

            /*AND S.StockStatus = 1*/";





        $stmtTotalStoredWeight = $this->conn->prepare($sqlGetTotalStoredWeight);

        $stmtTotalStoredWeight->execute();
        $TotalStoredWeight =[];
        $rowTotalStoredWeight= $stmtTotalStoredWeight->fetch(PDO::FETCH_ASSOC);
        $totalAnalytics['TotalStoredWeight'] = $rowTotalStoredWeight;

        //
        $sqlGetPackingType = "SELECT
            s.PackingTypeID,
            (
                SUM(s.AvalMcs * ps.TotalSlabs * ps.SlabWeight * ps.MFactor)
                +
                SUM(s.LooseBags * ps.SlabWeight * ps.MFactor)
            ) AS TotalKgs
        FROM CsStockData s
        JOIN AdminPackingStyles ps
            ON ps.PCKID = s.PackingStyleID
        JOIN CsStoresRackData r
            ON r.RowID = s.StoreRackID
        WHERE
            (s.AvalMcs > 0 OR s.LooseBags > 0)
            AND r.BlockID = $BlockID
            AND r.FloorID = $FloorID
            AND r.StoreID = $StoreID

            /*AND s.StockStatus = 1*/

        GROUP BY
            s.PackingTypeID
        ORDER BY
            TotalKgs DESC;";
        //$totalAnalytics['query'] =$sqlGetPackingType;
        $stmtPackingType = $this->conn->prepare($sqlGetPackingType);

        $stmtPackingType->execute();
        $PackingType =[];
        while( $rowPackingType = $stmtPackingType->fetch(PDO::FETCH_ASSOC) ){
            $PackingType[] = $rowPackingType;
        }
        $totalAnalytics['PackingType'] = $PackingType;


        return array( "AryData" => $AryData, "totalAnalytics" => $totalAnalytics );
    }


    function getRowData($RequestBody, $RowNumber)
    {

        $BlockID = $RequestBody['BlockID'];
        $FloorID = $RequestBody['FloorID'];
        $StoreID = $RequestBody['StoreID']; // uma (23-04-2024)

        $RowData = array();
        // $SqlCount = "SELECT * FROM `CsStoresRackData` WHERE BlockID=$BlockID AND FloorID=$FloorID and RowNumber=$RowNumber";
        $SqlCount = "SELECT * FROM `CsStoresRackData` WHERE BlockID=$BlockID AND FloorID=$FloorID and RowNumber=$RowNumber AND StoreID=$StoreID";
        $stmtCount = $this->conn->prepare($SqlCount);
        $stmtCount->execute();
        $c = 0;
        while ($rowNumber = $stmtCount->fetch(PDO::FETCH_ASSOC)) {
            $ItemData['Name'] = $rowNumber['RackName'];
            $ItemData['RackID'] = $rowNumber['RowID'];
            $ItemData['RackType'] = $rowNumber['Path'];
            $ItemData['RackStatus'] = $rowNumber['Status'];
            if($rowNumber['PlacedStockID']){

                $PlacedStockID = $rowNumber['PlacedStockID'];
                $sqlTotals = "
                    SELECT
                        COUNT(CSD.StockID) AS TotalRecords,
                        SUM(AvalMcs) AS TotalMcs,
                        SUM(LooseBags) AS TotalLooseBags,
                        SUM(AvalMcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor) AS TotalKgs
                    FROM CsStockData CSD
                    LEFT JOIN AdminPackingStyles APS ON APS.PCKID = CSD.PackingStyleID
                    WHERE CSD.StockID IN ($PlacedStockID)
                    AND StockStatus = 1
                    AND (AvalMcs != 0 OR LooseBags != 0)
                ";

                $stmtTotals = $this->conn->prepare($sqlTotals);
                $stmtTotals->execute();
                $totals = $stmtTotals->fetch(PDO::FETCH_ASSOC);

                $ItemData['TotalMcs']       = $totals['TotalMcs'];
                $ItemData['TotalLooseBags'] = $totals['TotalLooseBags'];
                $ItemData['TotalKgs']       = round(($totals['TotalKgs']), 2);
                if($ItemData['TotalMcs'] && $ItemData['TotalLooseBags'] && $ItemData['TotalKgs'] ){
                    $boxcount = count(explode(",", $rowNumber['PlacedStockID']));
                }else{
                   $boxcount = count(explode(",", $rowNumber['PlacedStockID']));
                   // $ItemData['RackStatus'] =0;
                }
                $boxcount = $totals['TotalRecords'];
            }else{
                $boxcount =0;
                $ItemData['TotalMcs']       = 0;
                $ItemData['TotalLooseBags'] = 0;
                $ItemData['TotalKgs']       = 0;
                $ItemData['RackStatus'] =0;
            }
            $ItemData['boxcount'] = $boxcount;
            $RowData[] = $ItemData;

        }
        return $RowData;

    }





}

?>
