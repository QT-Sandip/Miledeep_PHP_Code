<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
//include_once 'Controller/UtilController.php';

class PlacePalletController
{
    private $conn;
    //private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'ColdStore');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postPlacePalletDetails($RequestBody)
    {
        $BarCodeID = $RequestBody['BarCodeID'];
        $RowID = $RequestBody['RackID'];
        $StockID = $RequestBody['StockID'];

        $sqlBarcodeCount = "SELECT   RackName,BarCodeID,
        (SELECT count(*) FROM `CsStoresRackData` WHERE `BarCodeID`='$BarCodeID') as Count  ,
        (SELECT RackName FROM CsStoresRackData WHERE BarCodeID='$BarCodeID') as FilledRackname FROM `CsStoresRackData` WHERE  RowID='$RowID'";  //uma
        $stmtBarcodeCount = $this->conn->prepare($sqlBarcodeCount);
        $stmtBarcodeCount->execute();

        // $errorInfo = $stmtBarcodeCount->errorInfo();
        // var_dump($errorInfo);

        $dataRow = $stmtBarcodeCount->fetch(PDO::FETCH_ASSOC);
        // var_dump($dataRow);
        // exit;
        $count = $dataRow['Count'];
        $RackName = $dataRow['RackName'];
        $PresentBarCodeID = $dataRow['BarCodeID'];
        $FilledRackname = $dataRow['FilledRackname'];

        if ($PresentBarCodeID != 0) {
            $Data['Error'] = 'This Rack Number  is filled with another Bar Code ' . $PresentBarCodeID;
            $Data['Msg'] = 'Data Upload Failed';

        } else if ($count != 0) {
            $Data['Error'] = 'This Bar Code is filled in another Rack Number ' . $FilledRackname;
            $Data['Msg'] = 'Data Upload Failed';

        } else if ($PresentBarCodeID == 0 && $count == 0) {
            $existingRack = $this->auditLogger->fetchRowById('CsStoresRackData', 'RowID', $RowID);
            $existingStock = $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID);
            $SqlRackData = "UPDATE `CsStoresRackData` SET `BarCodeID`='$BarCodeID',`PlacedStockID` = '$StockID', `Status`= 1 WHERE `RowID`='$RowID'";
            $stmtRackData = $this->conn->prepare($SqlRackData);
            $stmtRackData->execute();

            if ($stmtRackData) {
                $Data['Error'] = null;
                $Data['Msg'] = 'Data Updated';
                $sqlUpdateBarcodes = "UPDATE `CsStockData` SET `StockFloorStatus`= 1, `StoreRackID` = '$RowID' WHERE `BarCodeID`='$BarCodeID' AND `StockID`='$StockID'";
                $stmtUpdateBarcodes = $this->conn->prepare($sqlUpdateBarcodes);
                $stmtUpdateBarcodes->execute();
                $updatedRack = $this->auditLogger->fetchRowById('CsStoresRackData', 'RowID', $RowID);
                $updatedStock = $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID);
                $this->auditLogger->logUpdate('CsStoresRackData', $RowID, $existingRack, $updatedRack, $RequestBody, [
                    'ReferenceNo' => $BarCodeID,
                    'Remarks' => 'Cold store pallet placed on rack',
                ]);
                $this->auditLogger->logUpdate('CsStockData', $StockID, $existingStock, $updatedStock, $RequestBody, [
                    'ReferenceNo' => $BarCodeID,
                    'Remarks' => 'Cold store stock placed on rack',
                ]);
            } else {
                $Data['Error'] = 'Data Upload Failed';
                $Data['Msg'] = 'Data Upload Failed';
            }
        } else {
            // $Data['Error'] = 'the Bar Code is filled in another Rack Number ' . $RackName;
            $Data['Error'] = 'the Bar Code is filled in another Rack Number ' . $FilledRackname;
            // $Data['Error'] = 'hi';
            $Data['Msg'] = 'Data Upload Failed';
        }
        return $Data;
    }
}
