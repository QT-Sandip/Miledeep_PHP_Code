<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class AttachStocktoAlreadyFilledPalletController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'ColdStore');
    }

    public function postAttachStocktoPallet($RequestBody)
    {
        // $Error=null;
        // $RowID = $RequestBody['RackID'];
        // $StockID = $RequestBody['StockID'];
        //  $isShifting = $RequestBody['isShifting'];
        $existingRackID = $RequestBody['existingRackID'];

        /*packing store starts*/
        if(!empty($RequestBody['page']) && $RequestBody['page'] == 'PackingStore'){
            if ($existingRackID != 0) {
                return $this->shiftRackForPackingStore($RequestBody);
            } else {
                return $this->attachStockForPackingStore($RequestBody);
            }
            /*packing store ends*/
        }else{
            if ($existingRackID != 0) {
                return $this->shiftRack($RequestBody);
            } else {
                return $this->attachStock($RequestBody);
            }
        }






    }


    function attachStock($RequestBody)
    {

        $RowID = $RequestBody['RackID'];
        $StockID = $RequestBody['StockID'];
        // $isShifting = $RequestBody['isShifting'];
        // $existingRackID = $RequestBody['existingRackID'];


        $SqlRack = "SELECT * FROM `CsStoresRackData` WHERE RowID='$RowID'";
        $StmtRack = $this->conn->prepare($SqlRack);
        $StmtRack->execute();
        $RowRack = $StmtRack->fetch(PDO::FETCH_ASSOC);
        $RackBarCodeID = $RowRack['BarCodeID'];

        $SqlStock = "SELECT * FROM `CsStockData` WHERE StockID='$StockID'";
        $StmtStock = $this->conn->prepare($SqlStock);
        $StmtStock->execute();
        $RowStock = $StmtStock->fetch(PDO::FETCH_ASSOC);
        $StockBarCodeID = $RowStock['BarCodeID'];


        // if ($RackBarCodeID == 0) {
        //     $SqlUpdate = "UPDATE `CsStoresRackData` SET `BarCodeID`='$StockBarCodeID', `PlacedStockID`='$StockID' ,Status=1 WHERE `RowID`='$RowID'";
        // } else {
        $sqlGetPlacedStockID = "SELECT PlacedStockID FROM CsStoresRackData WHERE `RowID`='$RowID'";
        $stmtGetPlacedStockID = $this->conn->prepare($sqlGetPlacedStockID);
        $stmtGetPlacedStockID->execute();
        $dataPlacedID = $stmtGetPlacedStockID->fetch(PDO::FETCH_ASSOC);
        $PlacedStockID = $dataPlacedID['PlacedStockID'];
        if (!empty($PlacedStockID)) {
            $PlacedStockID = $PlacedStockID . ',' . $StockID;
        } else {
            $PlacedStockID = $StockID;
        }

        $SqlUpdate = "UPDATE `CsStoresRackData` SET `PlacedStockID`='$PlacedStockID',`Status`=1 WHERE `RowID`='$RowID'";
        // }

        $oldRackRow = $this->auditLogger->fetchRowById('CsStoresRackData', 'RowID', $RowID);
        $Stmtupdate = $this->conn->prepare($SqlUpdate);
        $Stmtupdate->execute();
        $this->auditLogger->logUpdate('CsStoresRackData', $RowID, $oldRackRow, $this->auditLogger->fetchRowById('CsStoresRackData', 'RowID', $RowID), $RequestBody);

        if ($Stmtupdate) {
            $oldStockRow = $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID);
            $SqlFloorUpdate = "UPDATE `CsStockData` SET `StockFloorStatus`= 1, `StoreRackID` = '$RowID' WHERE `StockID`='$StockID'";
            $StmtFloorUpdate = $this->conn->prepare($SqlFloorUpdate);
            $StmtFloorUpdate->execute();
            $this->auditLogger->logUpdate('CsStockData', $StockID, $oldStockRow, $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID), $RequestBody);

            $Data['Error'] = null;
            $Data['Msg'] = 'Data Updated';
        } else {

            $Data['Error'] = null;
            $Data['Msg'] = 'Data Updated';
        }
        return $Data;

    }
    function shiftRack($RequestBody)
    {

        $RowID = $RequestBody['RackID'];
        $StockID = $RequestBody['StockID'];
        // $isShifting = $RequestBody['isShifting'];
        $existingRackID = $RequestBody['existingRackID'];
        $shiftToFilledPallet = $RequestBody['shiftToFilledPallet'];

        $SqlRack = "SELECT * FROM `CsStoresRackData` WHERE RowID='$RowID'";
        $StmtRack = $this->conn->prepare($SqlRack);
        $StmtRack->execute();
        $RowRack = $StmtRack->fetch(PDO::FETCH_ASSOC);
        $RackStatus = $RowRack['Status'];

        if ($RackStatus == 0 || $shiftToFilledPallet == 1) {



            $SqlStock = "SELECT GROUP_CONCAT(StockID) AS PlacedStockID,StoreRackID,BarCodeID FROM `CsStockData` WHERE StockID='$StockID'";
            $StmtStock = $this->conn->prepare($SqlStock);
            $StmtStock->execute();
            $RowStock = $StmtStock->fetch(PDO::FETCH_ASSOC);
            $StockBarCodeID = $RowStock['BarCodeID'];
            // $StoreRackID = $RowStock['StoreRackID'];

            // $sqlGetPlacedStockID = "SELECT GROUP_CONCAT(StockID) AS PlacedStockID,StoreRackID FROM `CsStockData` where StoreRackID='$existingRackID'";
            // $stmtGetPlacedStockID = $this->conn->prepare($sqlGetPlacedStockID);
            // $stmtGetPlacedStockID->execute();
            // $dataPlacedID = $stmtGetPlacedStockID->fetch(PDO::FETCH_ASSOC);

            $SqlStoreStock = "SELECT * FROM `CsStoresRackData` WHERE RowID='$RowID'";
            $StmtStoreStock = $this->conn->prepare($SqlStoreStock);
            $StmtStoreStock->execute();
            $rowStoreStock = $StmtStoreStock->fetch(PDO::FETCH_ASSOC);
            $StockIdsPlaced = $rowStoreStock['PlacedStockID'];

            if ($StockIdsPlaced == '') {
                $PlacedStockID = $RowStock['PlacedStockID'];
            } else {
                $PlacedStockID = $StockIdsPlaced . ',' . $RowStock['PlacedStockID'];
            }

            $SqlUpdate = "UPDATE `CsStoresRackData` SET `BarCodeID`='$StockBarCodeID', `PlacedStockID`='$PlacedStockID' ,`Status`=1 WHERE `RowID`='$RowID'";
            $oldRackRow2 = $this->auditLogger->fetchRowById('CsStoresRackData', 'RowID', $RowID);
            $Stmtupdate = $this->conn->prepare($SqlUpdate);
            $Stmtupdate->execute();
            $this->auditLogger->logUpdate('CsStoresRackData', $RowID, $oldRackRow2, $this->auditLogger->fetchRowById('CsStoresRackData', 'RowID', $RowID), $RequestBody);

            $oldStockRow2 = $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID);
            $SqlFloorUpdate = "UPDATE `CsStockData` SET `StockFloorStatus`= 1, `StoreRackID` = '$RowID' WHERE StockID='$StockID'";
            $StmtFloorUpdate = $this->conn->prepare($SqlFloorUpdate);
            $StmtFloorUpdate->execute();
            $this->auditLogger->logUpdate('CsStockData', $StockID, $oldStockRow2, $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID), $RequestBody);


            if ($Stmtupdate) {

                $SqlRackData = "
                        UPDATE CsStoresRackData
                        SET
                            PlacedStockID = TRIM(BOTH ',' FROM
                                REPLACE(
                                    CONCAT(',', PlacedStockID, ','),
                                    CONCAT(',', '$StockID', ','),
                                    ','
                                )
                            )
                        WHERE RowID = '$existingRackID'
                    ";

                $StmtRackData = $this->conn->prepare($SqlRackData);
                $StmtRackData->execute();


                $SqlClearRack = "
                        UPDATE CsStoresRackData
                        SET Status='0', BarCodeID='0'
                        WHERE RowID = '$existingRackID'
                        AND (PlacedStockID IS NULL OR PlacedStockID = '')
                    ";

                $StmtClearRack = $this->conn->prepare($SqlClearRack);
                $StmtClearRack->execute();

                // $SqlUpdate = "UPDATE `CsStoresRackData` SET `BarCodeID`='0', `PlacedStockID`='' ,`Status`=0 WHERE `RowID`='$existingRackID'";
                // $Stmtupdate = $this->conn->prepare($SqlUpdate);
                // $Stmtupdate->execute();

                $Data['Error'] = null;
                $Data['Msg'] = 'Data Updated';
            } else {

                $Data['Error'] = null;
                $Data['Msg'] = 'Data Updated';
            }
        } else {
            $Data['Error'] = null;
            $Data['Msg'] = 'Rack Already Filled';
        }

        return $Data;

    }
        /*packing store starts*/
    function attachStockForPackingStore($RequestBody)
    {
        $RowID = $RequestBody['RackID'];
        $StockID = $RequestBody['StockID'];
        $PostedUserId = $RequestBody['PostedUserId'];
        // $isShifting = $RequestBody['isShifting'];
        // $existingRackID = $RequestBody['existingRackID'];


        $SqlRack = "SELECT * FROM `CsStoresRackData` WHERE RowID='$RowID'";
        $StmtRack = $this->conn->prepare($SqlRack);
        $StmtRack->execute();
        $RowRack = $StmtRack->fetch(PDO::FETCH_ASSOC);
        $RackBarCodeID = $RowRack['BarCodeID'];

        $SqlStock = "SELECT * FROM `CsPackingStoreData` WHERE ID='$StockID'";
        $StmtStock = $this->conn->prepare($SqlStock);
        $StmtStock->execute();
        $RowStock = $StmtStock->fetch(PDO::FETCH_ASSOC);
        $StockBarCodeID = $RowStock['BarCodeID'];

        $AryCurrentDate = getCurrentDateTime();
        $CreatedDateTime = $AryCurrentDate['TimeStamp'];


        if ($RackBarCodeID == 0) {
            $SqlUpdate = "UPDATE `CsStoresRackData` SET `BarCodeID`='$StockBarCodeID', `PlacedPackingStoreID`='$StockID' ,Status=1 WHERE `RowID`='$RowID'";
        } else {
            $sqlGetPlacedStockID = "SELECT PlacedPackingStoreID AS PlacedStockID FROM CsStoresRackData WHERE `RowID`='$RowID'";
            $stmtGetPlacedStockID = $this->conn->prepare($sqlGetPlacedStockID);
            $stmtGetPlacedStockID->execute();
            $dataPlacedID = $stmtGetPlacedStockID->fetch(PDO::FETCH_ASSOC);
            $PlacedStockID = $dataPlacedID['PlacedStockID'];
            $PlacedStockID = !empty($PlacedStockID)?$PlacedStockID . ',' . $StockID : $StockID;

            $SqlUpdate = "UPDATE `CsStoresRackData` SET `PlacedPackingStoreID`='$PlacedStockID' WHERE `RowID`='$RowID'";
        }

        $Stmtupdate = $this->conn->prepare($SqlUpdate);
        $Stmtupdate->execute();

        if ($Stmtupdate) {
            $oldPackRow = $this->auditLogger->fetchRowById('CsPackingStoreData', 'ID', $StockID);
            $SqlFloorUpdate = "UPDATE `CsPackingStoreData` SET `StockFloorStatus`= 1, `StoreRackID` = '$RowID',UpdatedBy='$PostedUserId',UpdatedOn='$CreatedDateTime' WHERE `ID`='$StockID'";
            $StmtFloorUpdate = $this->conn->prepare($SqlFloorUpdate);
            $StmtFloorUpdate->execute();
            $this->auditLogger->logUpdate('CsPackingStoreData', $StockID, $oldPackRow, $this->auditLogger->fetchRowById('CsPackingStoreData', 'ID', $StockID), $RequestBody);

            $Data['Error'] = null;
            $Data['Msg'] = 'Data Updated';
        } else {

            $Data['Error'] = null;
            $Data['Msg'] = 'Data Updated';
        }
        return $Data;

    }
    function shiftRackForPackingStore($RequestBody)
    {

        $RowID = $RequestBody['RackID'];
        $StockID = $RequestBody['StockID'];
        // $isShifting = $RequestBody['isShifting'];
        $existingRackID = $RequestBody['existingRackID'];
        $PostedUserId = $RequestBody['PostedUserId'];

        $AryCurrentDate = getCurrentDateTime();
        $CreatedDateTime = $AryCurrentDate['TimeStamp'];

        $SqlRack = "SELECT * FROM `CsStoresRackData` WHERE RowID='$RowID'";
        $StmtRack = $this->conn->prepare($SqlRack);
        $StmtRack->execute();
        $RowRack = $StmtRack->fetch(PDO::FETCH_ASSOC);
        $RackStatus = $RowRack['Status'];

        if ($RackStatus == 0) {



            $SqlStock = "SELECT GROUP_CONCAT(ID) AS PlacedStockID,StoreRackID,BarCodeID FROM `CsPackingStoreData` WHERE ID='$StockID'";
            $StmtStock = $this->conn->prepare($SqlStock);
            $StmtStock->execute();
            $RowStock = $StmtStock->fetch(PDO::FETCH_ASSOC);
            $StockBarCodeID = $RowStock['BarCodeID'];
            // $StoreRackID = $RowStock['StoreRackID'];

            // $sqlGetPlacedStockID = "SELECT GROUP_CONCAT(StockID) AS PlacedStockID,StoreRackID FROM `CsStockData` where StoreRackID='$existingRackID'";
            // $stmtGetPlacedStockID = $this->conn->prepare($sqlGetPlacedStockID);
            // $stmtGetPlacedStockID->execute();
            // $dataPlacedID = $stmtGetPlacedStockID->fetch(PDO::FETCH_ASSOC);

            $SqlStoreStock = "SELECT * FROM `CsStoresRackData` WHERE RowID='$RowID'";
            $StmtStoreStock = $this->conn->prepare($SqlStoreStock);
            $StmtStoreStock->execute();
            $rowStoreStock = $StmtStoreStock->fetch(PDO::FETCH_ASSOC);
            $StockIdsPlaced = $rowStoreStock['PlacedStockID'];

            if ($StockIdsPlaced == '') {
                $PlacedStockID = $RowStock['PlacedStockID'];
            } else {
                $PlacedStockID = $StockIdsPlaced . ',' . $RowStock['PlacedStockID'];
            }

            $SqlUpdate = "UPDATE `CsStoresRackData` SET `BarCodeID`='$StockBarCodeID', `PlacedPackingStoreID`='$PlacedStockID' ,`Status`=1 WHERE `RowID`='$RowID'";
            $oldRackRow3 = $this->auditLogger->fetchRowById('CsStoresRackData', 'RowID', $RowID);
            $Stmtupdate = $this->conn->prepare($SqlUpdate);
            $Stmtupdate->execute();
            $this->auditLogger->logUpdate('CsStoresRackData', $RowID, $oldRackRow3, $this->auditLogger->fetchRowById('CsStoresRackData', 'RowID', $RowID), $RequestBody);

            $oldPackRow2 = $this->auditLogger->fetchRowById('CsPackingStoreData', 'ID', $StockID);
            $SqlFloorUpdate = "UPDATE `CsPackingStoreData` SET `StockFloorStatus`= 1, `StoreRackID` = '$RowID',UpdatedBy='$PostedUserId',UpdatedOn='$CreatedDateTime' WHERE ID='$StockID'";
            $StmtFloorUpdate = $this->conn->prepare($SqlFloorUpdate);
            $StmtFloorUpdate->execute();
            $this->auditLogger->logUpdate('CsPackingStoreData', $StockID, $oldPackRow2, $this->auditLogger->fetchRowById('CsPackingStoreData', 'ID', $StockID), $RequestBody);


            if ($Stmtupdate) {

                $SqlRackData = "
                        UPDATE CsStoresRackData
                        SET 
                            PlacedPackingStoreID = TRIM(BOTH ',' FROM 
                                REPLACE(
                                    CONCAT(',', PlacedPackingStoreID, ','),
                                    CONCAT(',', '$StockID', ','),
                                    ','
                                )
                            )
                        WHERE RowID = '$existingRackID'
                    ";

                $StmtRackData = $this->conn->prepare($SqlRackData);
                $StmtRackData->execute();


                $SqlClearRack = "
                        UPDATE CsStoresRackData
                        SET Status='0', BarCodeID='0'
                        WHERE RowID = '$existingRackID'
                        AND (PlacedPackingStoreID IS NULL OR PlacedPackingStoreID = '')
                    ";

                $StmtClearRack = $this->conn->prepare($SqlClearRack);
                $StmtClearRack->execute();

                // $SqlUpdate = "UPDATE `CsStoresRackData` SET `BarCodeID`='0', `PlacedStockID`='' ,`Status`=0 WHERE `RowID`='$existingRackID'";
                // $Stmtupdate = $this->conn->prepare($SqlUpdate);
                // $Stmtupdate->execute();

                $Data['Error'] = null;
                $Data['Msg'] = 'Data Updated';
            } else {

                $Data['Error'] = null;
                $Data['Msg'] = 'Data Updated';
            }
        } else {
            $Data['Error'] = null;
            $Data['Msg'] = 'Rack Already Filled';
        }

        return $Data;
    }


}
