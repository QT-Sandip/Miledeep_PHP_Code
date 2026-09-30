<?php

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
//include_once 'Controller/UtilController.php';

class PKCreateStoreLayoutController
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
                                'Remarks' => 'Packing store rack slot created',
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
                $existingRack = $this->auditLogger->fetchRowById('CsStoresRackData', 'RowID', $RackID);
                $stmt = $this->conn->prepare($SqlUpdate);
                $stmt->execute();
                if ($stmt) {
                    $updatedRack = $this->auditLogger->fetchRowById('CsStoresRackData', 'RowID', $RackID);
                    $this->auditLogger->logUpdate('CsStoresRackData', $RackID, $existingRack, $updatedRack, $RequestBody, [
                        'ReferenceNo' => $StoreID,
                        'Remarks' => 'Packing store rack layout updated',
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




        return $AryData;
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

            $sql="SELECT COUNT(CPSD.ID) Total
                FROM `CsPackingStoreData` AS CPSD
                LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPSD.PoNo
                LEFT JOIN ScrmPoData AS SPD2 ON SPD2.PoID=CPSD.DummyPOID
                LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPSD.PackingStyleID
                LEFT JOIN CsStores AS CSS ON CSS.StoreID=CPSD.UnitID
                LEFT JOIN RmProcessingStations AS RPS ON RPS.ProcessingStationID=CPSD.PlantID
                LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandID=CPSD.Brand
                LEFT JOIN CsStoresRackData as CSRD on CSRD.RowID=CPSD.StoreRackID
                LEFT JOIN CsStoresBlockData AS CSBD on CSBD.BlockID=CSRD.BlockID
                WHERE StoreRackID='".$ItemData['RackID']."'";
            $smt = $this->conn->prepare($sql);
            $smt->execute();
            $row = $smt->fetch(PDO::FETCH_ASSOC);

                $boxcount =$row['Total'];


                $ItemData['boxcount'] = $boxcount;




            $RowData[] = $ItemData;

        }
        return $RowData;

    }





}

?>
