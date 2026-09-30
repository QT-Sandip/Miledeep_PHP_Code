<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
include_once '../ColdStore/Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class StockShiftingtoAnotherRackController
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
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'ColdStore');
    }

    public function postAttachStocktoPallet($RequestBody)
    {
       // $RequestBody = json_decode(file_get_contents('php://input'), true);

        $RowID = $RequestBody['RackID'];
        $shiftToFilledPallet = $RequestBody['shiftToFilledPallet'];
        $StockData = !empty($RequestBody['StockData']) ? $RequestBody['StockData'] : [];//multiple shift palette
        $SqlRack = "SELECT * FROM `CsStoresRackData` WHERE RowID='$RowID'";
        $StmtRack = $this->conn->prepare($SqlRack);
        $StmtRack->execute();
        $RowRack = $StmtRack->fetch(PDO::FETCH_ASSOC);
        $RackStatus = $RowRack['Status'];
        //if ($RackStatus == 0 || $shiftToFilledPallet == 1) {
            foreach($StockData as $item){
                $stockId = $item['stock_Id'];
                $mcs = $item['mcs'];
                $LooseBags = $item['LooseBags'];
                $existingRackID = $item['existingRackID'];

                $stockSql = "SELECT `ActualMcs`,`ActualLooseBags` FROM CsStockData WHERE StockID=$stockId";
                $StmtStock = $this->conn->prepare($stockSql);
                $StmtStock->execute();
                $RowStock = $StmtStock->fetch(PDO::FETCH_ASSOC);

                if($RowStock['ActualMcs'] == $mcs && $RowStock['ActualLooseBags'] == $LooseBags){
                    //just doing rack shifting
                    $rackData = [];
                    $rackData['RackID'] = $RowID;
                    $rackData['StockID'] = $stockId;
                    $rackData['existingRackID'] = $existingRackID;

                    $res = $this->shiftRack($rackData);
                }else{
                    //create stock Id
                   // $stockData = $item;
                    $item['RackID'] = $RowID;
                    $item['IsDeductGlazeFW'] = $RequestBody['IsDeductGlazeFW'];
                    $item['IsDeductGlaze'] = $RequestBody['IsDeductGlaze'];
                    $res = $this->CreateStockIdFormSourceIdAndAssignToRack($item);
                    if($res['error']){
                        $Data['Error'] = null;
                        $Data['Msg'] = $res['error'].' for BID '.$item['bid'];
                        return $Data;
                    }
                }

            }
            if($res){
                $Data['Error'] = null;
                $Data['Msg'] = 'Data Updated';
            }else{
                $Data['Error'] = null;
                $Data['Msg'] = 'Unable to Update';
            }
        /* }else {
            $Data['Error'] = 'Rack Already Filled';
            $Data['Msg'] = 'Rack Already Filled';
        } */
        return $Data;
    }
    function shiftRack($RequestBody){
        $RowID = $RequestBody['RackID'];
        $StockID = $RequestBody['StockID'];
        // $isShifting = $RequestBody['isShifting'];
        $existingRackID = $RequestBody['existingRackID'];

        $SqlStock = "SELECT GROUP_CONCAT(StockID) AS PlacedStockID,StoreRackID,BarCodeID FROM `CsStockData` WHERE StockID='$StockID'";
        $StmtStock = $this->conn->prepare($SqlStock);
        $StmtStock->execute();
        $RowStock = $StmtStock->fetch(PDO::FETCH_ASSOC);
        $StockBarCodeID = $RowStock['BarCodeID'];

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

        $oldRackRow = $this->auditLogger->fetchRowById('CsStoresRackData', 'RowID', $RowID);
        $SqlUpdate = "UPDATE `CsStoresRackData` SET `BarCodeID`='$StockBarCodeID', `PlacedStockID`='$PlacedStockID' ,`Status`=1 WHERE `RowID`='$RowID'";
        $Stmtupdate = $this->conn->prepare($SqlUpdate);
        $Stmtupdate->execute();
        $this->auditLogger->logUpdate('CsStoresRackData', $RowID, $oldRackRow, $this->auditLogger->fetchRowById('CsStoresRackData', 'RowID', $RowID));

        $oldStockRow = $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID);
        $SqlFloorUpdate = "UPDATE `CsStockData` SET `StockFloorStatus`= 1, `StoreRackID` = '$RowID' WHERE StockID='$StockID'";
        $StmtFloorUpdate = $this->conn->prepare($SqlFloorUpdate);
        $StmtFloorUpdate->execute();
        $this->auditLogger->logUpdate('CsStockData', $StockID, $oldStockRow, $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID));


        if ($Stmtupdate) {
            if($existingRackID){
                $SqlRackData = "UPDATE CsStoresRackData
                                SET
                                    PlacedStockID = TRIM(BOTH ',' FROM
                                        REPLACE(
                                            CONCAT(',', PlacedStockID, ','),
                                            CONCAT(',', '$StockID', ','),
                                            ','
                                        )
                                    )
                                WHERE RowID = '$existingRackID'";

                            $StmtRackData = $this->conn->prepare($SqlRackData);
                            $StmtRackData->execute();


                            $SqlClearRack = "UPDATE CsStoresRackData
                                            SET Status='0', BarCodeID='0'
                                            WHERE RowID = '$existingRackID'
                                            AND (PlacedStockID IS NULL OR PlacedStockID = '')";

                            $StmtClearRack = $this->conn->prepare($SqlClearRack);
                            $StmtClearRack->execute();
            }

            return true;
        }
        return true;
    }
    function CreateStockIdFormSourceIdAndAssignToRack($RequestBody){
        if(!empty($RequestBody['stock_Id'])){
            $IsDeductGlazeFW = $RequestBody['IsDeductGlazeFW'];
            $IsDeductGlaze = $RequestBody['IsDeductGlaze'];
            $stockId = $RequestBody['stock_Id'];
            $RackID = $RequestBody['RackID'];
            $TxtMcs = $RequestBody['mcs'];
            $TxtLooseBags = $RequestBody['LooseBags'];
            $Galze = 0;$GlazeType = '';
            $StockSql = "SELECT `ProductionID`,`ActualMcs`,`ActualLooseBags`,`AvalMcs`,`LooseBags` ,`ActualKgs`,`AvalKgs`
                        ,APS.`TotalSlabs`,APS.`SlabWeight`,APS.`MFactor`,CSD.PackingStyleID,SPV.`GlazePerc`,SPV.Glaze
                            FROM CsStockData CSD
                            LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD.PackingStyleID
                            LEFT JOIN ScrmPIVarietyList AS SPV ON SPV.VarietyID=CSD.VarietyID
                            WHERE StockID=$stockId";
            $stmtStock = $this->conn->prepare($StockSql);
            $stmtStock->execute();
            if ($stmtStock) {
                $dataRow = $stmtStock->fetch(PDO::FETCH_ASSOC);
                $PackingStyleID = $dataRow['PackingStyleID'];
                $TotalSlabs = $dataRow['TotalSlabs'];
                $SlabWeight = $dataRow['SlabWeight'];
                $MFactor = $dataRow['MFactor'];

                $AvalMcs = $dataRow['AvalMcs'] - $TxtMcs;//62-2
                $ActualMcs = $dataRow['ActualMcs'] - $TxtMcs;//112-2=110
                if($dataRow['LooseBags'] >= $TxtLooseBags){
                    $LooseBags = $dataRow['LooseBags'] - $TxtLooseBags;//10-2
                    $ActualLooseBags = $dataRow['ActualLooseBags'] - $TxtLooseBags;//15-2
                }else{
                    $TotalAvalMcsSlabs = $AvalMcs * $TotalSlabs;//300
                    $TotalActualMcsSlabs = $ActualMcs * $TotalSlabs;//550

                    $LooseBags = $dataRow['LooseBags'] - $TxtLooseBags;//1-2=-1
                    if($LooseBags < 0){
                        $LooseBags = abs($LooseBags);//1

                        $TotalAvalMcsSlabs -= $LooseBags;//299
                        $AvalMcs = floor($TotalAvalMcsSlabs / $TotalSlabs);//59.8

                        $TotalActualMcsSlabs -= $LooseBags;
                        $ActualMcs = floor($TotalActualMcsSlabs / $TotalSlabs);//109

                        $ActualLooseBags = ((($dataRow['ActualMcs'] - $TxtMcs) - $ActualMcs) * $TotalSlabs) - $LooseBags;//112-2
                        $LooseBags = ((($dataRow['AvalMcs'] - $TxtMcs) - $AvalMcs) * $TotalSlabs) - $LooseBags;
                    }
                }

                $ActualKgs = $this->utilController->getCsStockAvalWeightinKgs($PackingStyleID, $TxtMcs, $TxtLooseBags);
                if (($IsDeductGlaze == 1 && $Galze > 0) ) {
                    $GlazeWeight = ($ActualKgs * $Galze) / 100;
                    $ActualKgs = $ActualKgs - $GlazeWeight;
                }
                if ($IsDeductGlaze == 0 && $IsDeductGlazeFW == 1 && (strpos($GlazeType, 'FW') === 0) && $Galze > 0){
                    $GlazeWeight = ($ActualKgs * $Galze) / 100;
                    $ActualKgs = $ActualKgs + $GlazeWeight;
                }

                if(isset($AvalMcs) && isset($ActualMcs) && isset($LooseBags) && isset($ActualLooseBags) ){
                    $AryCurrentDate = getCurrentDateTime();
                    $CreatedDateTime = $AryCurrentDate['TimeStamp'];

                    $BarCodeID = $this->getNewBarcode();

                    $InsertSql = "INSERT INTO CsStockData
                                (`ProductionID`,`PackingCount`,`PackingTypeID`,`Grade`,`PoGradeId`,`FreezingType`,`VarietyID`,`Galze`,`SockingType`,`Salt`,
                                `ProductionDate`,`TransactionDate`,`PoID`,`RmLotID`,`ProductionTypeID`,`ProductCategoryID`,`UnitID`,`PlantID`,`ShiftID`,
                                `PackingStyleID`,`StockHolderID`,`Remarks`,`StockStatus`,`AbStatus`,`ProdDayCode`,`ProdBatchCode`,`SoakingDate`,`TubNo`,`LineNumber`,StockFloorStatus,
                                `PostedDate`,`BarCodeID`,SourceStockID,`ActualMcs`,`ActualLooseBags`,`AvalMcs`,`LooseBags`,`ActualKgs`,`AvalKgs`,StoreRackID,BatchCode)
                                SELECT
                                    `ProductionID`,`PackingCount`,`PackingTypeID`,`Grade`,`PoGradeId`,`FreezingType`,`VarietyID`,`Galze`,`SockingType`,`Salt`,
                                    `ProductionDate`,`TransactionDate`,`PoID`,`RmLotID`,`ProductionTypeID`,`ProductCategoryID`,`UnitID`,`PlantID`,`ShiftID`,
                                    `PackingStyleID`,`StockHolderID`,`Remarks`,`StockStatus`,`AbStatus`,`ProdDayCode`,`ProdBatchCode`,`SoakingDate`,`TubNo`,`LineNumber`,StockFloorStatus,
                                    '$CreatedDateTime' AS `PostedDate`,
                                    '$BarCodeID' AS BarCodeID,
                                    '$stockId' AS SourceStockID,
                                    '$TxtMcs' AS `ActualMcs`,
                                    '$TxtLooseBags' AS `ActualLooseBags`,
                                    '$TxtMcs' AS `AvalMcs`,
                                    '$TxtLooseBags' AS `LooseBags`,
                                    '$ActualKgs' AS `ActualKgs`,
                                    '$ActualKgs' AS `AvalKgs`,
                                    '$RackID' AS StoreRackID,
                                    BatchCode
                                FROM CsStockData
                                WHERE StockID = $stockId";


                    $stmtInsert = $this->conn->prepare($InsertSql);
                    if(!$stmtInsert || !$stmtInsert->execute() ){
                        return ['error' => 'Unable to Insert Data'];
                    }
                    $stock_Id = $this->conn->lastInsertId();
                    $this->auditLogger->logInsert('CsStockData', $stock_Id, $this->auditLogger->fetchRowById('CsStockData', 'StockID', $stock_Id));


                   //

                    $AvalKgs = $dataRow['AvalKgs'] - $ActualKgs;
                    $ActualKgs = $dataRow['ActualKgs'] - $ActualKgs;
                    $oldSourceStockRow = $this->auditLogger->fetchRowById('CsStockData', 'StockID', $stockId);
                    $updateSql = "UPDATE CsStockData SET `ActualMcs`='$ActualMcs',`ActualLooseBags`='$ActualLooseBags',`AvalMcs`='$AvalMcs',`LooseBags`='$LooseBags',`ActualKgs`='$ActualKgs',`AvalKgs`='$AvalKgs' WHERE StockID=$stockId";
                    $stmtUpdate = $this->conn->prepare($updateSql);
                    $stmtUpdate->execute();
                    $this->auditLogger->logUpdate('CsStockData', $stockId, $oldSourceStockRow, $this->auditLogger->fetchRowById('CsStockData', 'StockID', $stockId));
                    if(!$stmtUpdate || !$stmtUpdate->execute() ){
                        return ['error' => 'Stock Update Failed.'];
                    }
                    if($stock_Id){
                        $RequestBody['StockID'] = $stock_Id;
                        $this->shiftRack($RequestBody);
                        return true;
                    }else{
                        return false;
                    }

                }

            }
        }else{
            return ['error' => 'No Stock ID provided'];
        }
    }

    function getNewBarcode()
    {
        $SqlBarCodes = "SELECT BarCodeID FROM CsBarCodes ORDER by BarCodeID DESC LIMIT 1";
        $StmtBarCodes = $this->conn->prepare($SqlBarCodes);
        $StmtBarCodes->execute();
        $RecentBarCode = $StmtBarCodes->fetch(PDO::FETCH_ASSOC);
        $LastBarCodeID = $RecentBarCode['BarCodeID'];

        if ($LastBarCodeID == '') {
            $LastBarCodeID = 1;
        } else {
            $LastBarCodeID = $LastBarCodeID + 1;
        }


        date_default_timezone_set("Asia/Kolkata");
        $BarCodeDate = date("Y-m-d");
        $BarCodeValue = str_replace("-", "", $BarCodeDate);
        $BarCodeValue = $BarCodeValue . $LastBarCodeID;

        $sqlInsertBarCodeData = "INSERT INTO `CsBarCodes`(`BarCodeDate`, `BarCodeValue`, `BarCodeStatus`, `OnFloor`, `PostedUserID`)
        VALUES ('$BarCodeDate','$BarCodeValue','1','0','1')";

        $stmtInsertBarCodeData = $this->conn->prepare($sqlInsertBarCodeData);
        $stmtInsertBarCodeData->execute();
        $BarCodeID = $this->conn->lastInsertId();
        $this->auditLogger->logInsert('CsBarCodes', $BarCodeID, $this->auditLogger->fetchRowById('CsBarCodes', 'BarCodeID', $BarCodeID));
        return $BarCodeID;
    }
}
