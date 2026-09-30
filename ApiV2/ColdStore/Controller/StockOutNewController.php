<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
//include_once 'Controller/UtilController.php';

class StockOutNewController
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
        // $this->utilController = new UtilController($this->conn);
    }

    public function getStockOutNewDetails($RequestBody)
    {
        $UnitID = $RequestBody['ColdStore'];
        $StockBelongsTo = $RequestBody['StockBelongsTo'];
        $Group = $RequestBody['Group'];
        $Grade = $RequestBody['Grade'];
        $FreezingType = $RequestBody['FreezingType'];
        $Soaking = $RequestBody['Soaking'];
        $ProductCategory = $RequestBody['ProductCategory'];
        $Abstatus = $RequestBody['AbStatus'];
        $Variety = $RequestBody['Variety'];
        $PoID = $RequestBody['PoID'];
        $PackingType = $RequestBody['PackingType'];
        $Brand = $RequestBody['Brand'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $Species = $RequestBody['Species'];
        $VarietyID = $RequestBody['VarietyID'];
        
        $Query = '';
        if ($UnitID != '') {
            $Query .= " and CSD.UnitID='$UnitID'";
        }
        if ($StockBelongsTo != '') {
            $Query .= " and CSD.StockHolderID='$StockBelongsTo'";
        }
        if ($Group != '') {  // hold
            $Query .= " and PackingTypeID='$Group'";
        }
        if ($Grade != '') {
            $Query .= " and CSD.Grade='$Grade'";
        }

        if ($FreezingType != '') {
            $Query .= " and CSD.FreezingType='$FreezingType'";
        }
        if ($Soaking != '') {
            $Query .= " and CSD.SockingType='$Soaking'";
        }
        if ($Abstatus != '') {
            $Query .= " and CSD.AbStatus='$Abstatus'";
        }
        if ($ProductCategory != '') {
            $Query .= " and CSD.ProductCategoryID='$ProductCategory'";
        }

        if ($Variety != '') {
            $Query .= " and CSD.VarietyID='$Variety'";
        }
        if ($PoID != '') {
            $Query .= " and CSD.PoID='$PoID'";
        }
        if ($PackingType != '') {
            $Query .= " and CSD.PackingTypeID='$PackingType'";
        }
        if ($Brand != '') { 
            $Query .= " and SPV.BrandName='$Brand'";
        }

        if ($TypeProduct != '') {
            $Query .= " and ProductionTypeID='$TypeProduct'";
        }
        if ($Species != '') { 
            $Query .= " and SPV.Species='$Species'";
        }
        if ($VarietyID != '') {
            $Query .= " and SPV.ProductID='$VarietyID'";
        }
        

        $dataRow = null;
        $sqlFields = "PoNo, CSD.ProductionID,AvalMcs,LooseBags,ShortName,CSD.Grade,PackingTypeID,APS.PackingStyle,StockID,CSD.BarCodeID,
        CSRD.StoreID,CSRD.RowID as RackID,CSRD.RackName,CSRD.BlockID,CSD.ProductCategoryID as ProductCategory,
        (AvalMcs*TotalSlabs*SlabWeight*MFactor) as AvalMcsKgs, (LooseBags*TotalSlabs*SlabWeight*MFactor) as LooseKgs,
        BlockName,StoreName,UnitID,CSD.PoID";

        $sqlQuery = "SELECT $sqlFields FROM `CsStockData` AS CSD
        LEFT JOIN ScrmPIVarietyList AS SPV ON SPV.VarietyID=CSD.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD.PackingStyleID
        LEFT JOIN CsStoresRackData AS CSRD ON CSRD.RowID =CSD.StoreRackID
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CSD.PoID
        LEFT JOIN CsStoresBlockData AS CSBD ON CSBD.BlockID=CSRD.BlockID
        LEFT JOIN CsStores AS CS ON CS.StoreID=CSD.UnitID
        WHERE StockID!='' $Query AND (AvalMcs!=0 OR LooseBags!=0)
        ORDER BY StockID ASC";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        // $errorInfo = $stmt->errorInfo();
        // var_dump($errorInfo);

        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $Kgs = $dataRow['AvalMcsKgs'] + $dataRow['LooseKgs'];

            $ArrayStockOutData['SNo'] = ++$i;
            $ArrayStockOutData['ProductionID'] = $dataRow['ProductionID'];
            $ArrayStockOutData['Variety'] = $dataRow['ShortName'];
            $ArrayStockOutData['Grade'] = $dataRow['Grade'];
            $ArrayStockOutData['PackingType'] = $dataRow['PackingTypeID'];
            $ArrayStockOutData['Mcs'] = $dataRow['AvalMcs'];
            $ArrayStockOutData['Bags'] = $dataRow['LooseBags'];
            $ArrayStockOutData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStockOutData['Kgs'] = sprintf('%0.2f', round($Kgs, 2));
            $ArrayStockOutData['StockID'] = $dataRow['StockID'];
            $ArrayStockOutData['BarCodeID'] = $dataRow['BarCodeID'];
            $ArrayStockOutData['StoreID'] = $dataRow['StoreID'];
            $ArrayStockOutData['RackID'] = $dataRow['RackID'];
            $ArrayStockOutData['BlockID'] = $dataRow['BlockID'];
            $ArrayStockOutData['ProductCategory'] = $dataRow['ProductCategory'];
            $ArrayStockOutData['PoID'] = $dataRow['PoID'];
            $ArrayStockOutData['PoNumber'] = $dataRow['PoNo'];
            $ArrayStockOutData['UnitID'] = $dataRow['UnitID'];
            $ArrayStockOutData['StoreName'] = $dataRow['StoreName'];
            $ArrayStockOutData['Location'] = $dataRow['BlockName'] . ' - ' . $dataRow['RackName'];

            $AryData[] = $ArrayStockOutData;
        }
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['DailyStockOutData'] = $AryData;
        return $Data;
    }

    public function postStockOutDetails($Request)  // need execute loop 03-05-2024
    {

        $ProductionID = $Request['ProductionID'];
        $ProductionDate = $Request['ProductionDate'];
        $StockOutDate = $Request['StockOutDate'];
        $StockOutReason = $Request['StockOutReason'];
        $TotalMsc = $Request['TotalMsc'];
        $TotalLooseBags = $Request['TotalLooseBags'];
        $StockItems = $Request['StockItems'];
        $Remarks = $Request['Remarks'];

        $sqlQuery = "select * from  `CsDailyStockOutData` where StockOutDate='$StockOutDate'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $rowCount = $stmt->rowCount();
        $rowCount = $rowCount + 1;

        $BatchCode = str_replace('-', "", $StockOutDate);
        $BatchCode = $BatchCode . "-" . $rowCount;

        $sqlQuery = "INSERT INTO `CsDailyStockOutData` 
           ( `ProductionID`,  `StockOutDate`, `Reason`, `ActualOutMcs`, `ActualOutLooseBags`, 
           `AvalOutMcs`, `AvalOutLooseBags`, `Remarks`,`OutBatchCode`) 
           VALUES ('$ProductionID','$StockOutDate','$StockOutReason','$TotalMsc','$TotalLooseBags',
           '$TotalMsc','$TotalLooseBags','$Remarks','$BatchCode')";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $lastInsertId = $this->conn->lastInsertId(); //uma

        if ($stmt) {
            $Request['CsStockOutID'] = $lastInsertId;
            $createdStockOut = $this->auditLogger->fetchRowById('CsDailyStockOutData', 'CsStockOutID', $lastInsertId);
            $this->auditLogger->logInsert('CsDailyStockOutData', $lastInsertId, $createdStockOut, $Request, [
                'ReferenceNo' => $BatchCode,
                'Remarks' => 'Cold store stock out created',
            ]);
            foreach ($StockItems as $Item) {
                $Data = $this->postStockOutItems($Request, $Item, $lastInsertId); //uma
            }
        }

        return $Data;
    }


    function postStockOutNewItems($Request, $Item, $CsStockOutID)
    {

        $Error = null;
        $Msg = "Data Updated successfully";

        $AryCurrentDate = getCurrentDateTime();
        $PostedDate = $AryCurrentDate['TimeStamp'];


        $StockOutReason = $Request['StockOutReason'];
        $Remarks = $Request['Remarks'];
        $ProductionID = $Item['ProductionID'];
        $StockOutDate = $Request['StockOutDate'];
        $BarCodeID = $Item['BarCodeID'];
        $RackID = $Item['RackID'];
        $BatchCode = str_replace('-', "", $StockOutDate);
        $StockID = $Item['StockID'];

        $OutMcs = $Item['OutMcs'];
        $OutLooseBags = $Item['OutLooseBags'];


        $Sqlfields = "BarCodeID,AvalMcs,LooseBags,AvalKgs,PackingStyleID";
        $SqlStockDetails = "select $Sqlfields from  `CsStockData` WHERE StockID ='$StockID'";
        $stmt = $this->conn->prepare($SqlStockDetails);
        $stmt->execute();
        $stockRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $existingStock = $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID);
        $BarCodeID = $stockRow['BarCodeID'];
        $PackingStyleID = $stockRow['PackingStyleID'];
        $BalanceMcs = $stockRow['AvalMcs'] - $OutMcs;
        $BalanceLooseBags = $stockRow['LooseBags'] - $OutLooseBags;
        $RowStockDetails = json_encode($stockRow, true);

        $sqlKgsQuery = "SELECT TotalSlabs,SlabWeight,MFactor FROM `AdminPackingStyles` WHERE PCKID='$PackingStyleID'";
        $stmtKgsQuery = $this->conn->prepare($sqlKgsQuery);
        $stmtKgsQuery->execute();
        $RowStockData = $stmtKgsQuery->fetch(PDO::FETCH_ASSOC);
        $McsKgs = $OutMcs * $RowStockData['TotalSlabs'] * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
        $LooseKgs = $OutLooseBags * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
        $OutKgs = $McsKgs + $LooseKgs;

        $BalanceAvailableKgs = $stockRow['AvalKgs'] - $OutKgs;

        $SqlStockUpdate = "UPDATE  `CsStockData` SET AvalMcs ='$BalanceMcs' , LooseBags='$BalanceLooseBags', AvalKgs='$BalanceAvailableKgs', StockStatus='2' WHERE StockID ='$StockID'";
        $StmtStockUpdate = $this->conn->prepare($SqlStockUpdate);
        $StmtStockUpdate->execute();

        if ($StmtStockUpdate) {
            $updatedStock = $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID);
            $this->auditLogger->logUpdate('CsStockData', $StockID, $existingStock, $updatedStock, $Request, [
                'ReferenceNo' => $BarCodeID,
                'Remarks' => 'Cold store stock updated for stock out',
                'Async' => 1,
            ]);
            $SqlStockOutData = "INSERT INTO `CsStockOutDetails` 
            (`PostedDate`,`BarCodeID`,`StockID`,`RackID`, `OutMcs`,`OutLooseBags`,`Remarks`,`OutType`,`OutStatus`,`BatchCode`,`OutDate`,
            `ProductionID`,`CsStockOutID`) VALUES 
            ('$PostedDate','$BarCodeID','$StockID','$RackID','$OutMcs','$OutLooseBags','$Remarks','$StockOutReason','3','$BatchCode','$StockOutDate',
            '$ProductionID','$CsStockOutID')";
            $StmtStockOutData = $this->conn->prepare($SqlStockOutData);
            $StmtStockOutData->execute();
            if ($StmtStockOutData) {
                $stockOutDetailId = $this->conn->lastInsertId();
                $createdOutDetail = $this->auditLogger->fetchRowById('CsStockOutDetails', 'ID', $stockOutDetailId);
                $this->auditLogger->logInsert('CsStockOutDetails', $stockOutDetailId, $createdOutDetail, $Request, [
                    'ReferenceNo' => $BatchCode,
                    'Remarks' => 'Cold store stock out detail created',
                    'Async' => 1,
                ]);
            }
        } else {
            $Error = "Unable To Update Stock";
        }

        if ($StmtStockOutData) {

            $SqlStockNo = "SELECT sum(AvalMcs) AS McsTotal,sum(LooseBags) as LooseTotal,BarCodeID 
            from CsStockData where BarCodeID='$BarCodeID'";

            $StmtStockNo = $this->conn->prepare($SqlStockNo);
            $StmtStockNo->execute();
            $RowStock = $StmtStockNo->fetch(PDO::FETCH_ASSOC);
            $McsTotal = $RowStock['McsTotal'];
            $LooseTotal = $RowStock['LooseTotal'];

            if ($McsTotal == 0 && $LooseTotal == 0) {
                $existingRackRows = $this->auditLogger->fetchRowsByConditions('CsStoresRackData', ['BarCodeID' => $BarCodeID]);
                $SqlRackData = "UPDATE `CsStoresRackData` SET Status ='0', BarCodeID='0' WHERE BarCodeID ='$BarCodeID'";
                $StmtRackData = $this->conn->prepare($SqlRackData);
                $StmtRackData->execute();
                if ($StmtRackData) {
                    foreach ($existingRackRows as $existingRack) {
                        $rackId = $existingRack['RowID'];
                        $updatedRack = $this->auditLogger->fetchRowById('CsStoresRackData', 'RowID', $rackId);
                        $this->auditLogger->logUpdate('CsStoresRackData', $rackId, $existingRack, $updatedRack, $Request, [
                            'ReferenceNo' => $BarCodeID,
                            'Remarks' => 'Cold store rack cleared after stock out',
                            'Async' => 1,
                        ]);
                    }
                }
            } else {
                $StmtRackData = true;
            }
        } else {
            $Error = "Unable To Update Out Stock";
        }

        if ($StmtRackData) {
            $SqlTransactionHistory = "INSERT INTO `CsTransactionHistory`( `TrnsDate`,`TrnsType`, `Data`) VALUES 
            ('$PostedDate','$StockOutReason','$RowStockDetails')";

            $StmtTransactionHistory = $this->conn->prepare($SqlTransactionHistory);
            $StmtTransactionHistory->execute();
            if ($StmtTransactionHistory) {
                $transactionHistoryId = $this->conn->lastInsertId();
                $createdTransaction = $this->auditLogger->fetchRowById('CsTransactionHistory', 'ID', $transactionHistoryId);
                $this->auditLogger->logInsert('CsTransactionHistory', $transactionHistoryId, $createdTransaction, $Request, [
                    'ReferenceNo' => $BatchCode,
                    'Remarks' => 'Cold store stock out transaction history created',
                    'Async' => 1,
                ]);
            }
        } else {
            $Error = "Unable To Update Rack Details ";
        }

        $Data['Error'] = $Error;
        $Data['Msg'] = $Msg;


        return $Data;
    }
}
