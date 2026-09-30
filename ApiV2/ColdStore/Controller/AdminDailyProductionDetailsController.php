<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class AdminDailyProductionDetailsController
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
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'ColdStore');
    }

    public function getDailyProductionDetails($RequestBody)
    {

        $ProductionDate = $RequestBody['ProductionDate'];
        $UnitID = $RequestBody['ColdStoreID'];
        $StockHolderID = $RequestBody['StockHolderID'];
        $ProductionTypeID = $RequestBody['Group'];
        $PackingTypeID = $RequestBody['PackingType'];
        $ProductionID = $RequestBody['ProductionID'];

        $Query = '';



        if ($ProductionID != '') {
            $PQuery = " where ProductionID='$ProductionID'";
        }

        if ($ProductionID != '') {
            $DailyProductionData = $this->getProductionDetails($PQuery);
            $ProductionDate = $DailyProductionData['ProductionDate'];
        } else {
            $DailyProductionData['ProductionCode'] =  str_replace("-", "", $ProductionDate);
            $DailyProductionData['BatchCode'] = $DailyProductionData['ProductionCode'] . "-" . rand(10, 100);
        }


        if ($ProductionDate != '') {
            $Query = "  WHERE ProductionDate='$ProductionDate'";
        }


        if ($UnitID != '') {
            $Query .= " and UnitID='$UnitID'";
        }
        if ($StockHolderID != '') {
            $Query .= " and StockHolderID='$StockHolderID'";
        }
        if ($ProductionTypeID != '') {
            $Query .= " and ProductionTypeID='$ProductionTypeID'";
        }
        if ($PackingTypeID != '') {
            $Query .= " and PackingTypeID='$PackingTypeID'";
        }

        // } else {
        //     $DailyProductionList=$this->getProductionlist($Query);
        // }

        $DailyProductionList = $this->getProductionlist($Query);


        $sqlLastest = "SELECT LAST_INSERT_ID() AS LastID FROM CsDailyProductionData";
        $stmtLastestID = $this->conn->prepare($sqlLastest);
        $stmtLastestID->execute();
        $dataLastID = $stmtLastestID->fetch(PDO::FETCH_ASSOC);

        $LastID = $dataLastID['LastID'];


        // $ProdID = $this->postDailyProductionDetails($RequestBody)['NewAddedProdId'];

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['NewAddedProdId'] = $LastID;
        $Data['DailyProductionList'] = $DailyProductionList;
        $Data['DailyProductionData'] = $DailyProductionData;
        //ar_dump($Data);
        return $Data;
    }

    function getProductionlist($Query)
    {
        $sqlFields = " ProductionID ,ActualMcs as Mcs,SPV.DeviSupc,ProductDescription,ActualLooseBags as Bags,ShortName,CSD.Grade,PackingTypeID,APS.PackingStyle,
        (ActualMcs*TotalSlabs*SlabWeight*MFactor) as Kgs, (ActualLooseBags*SlabWeight*MFactor) as LKgs,LineNumber,ProdBatchCode,AvalMcs,LooseBags,
        ActualMcs,ActualLooseBags";



        $sqlQuery = "SELECT $sqlFields FROM `CsDailyProductionData` as CSD
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        $Query ";



        $sqlFields = "SPV.DeviSupc, ProductDescription,ProductionID,`AvalMcs`, `LooseBags`, `PackingTypeID`, CSD.`Grade`,
        `PoGradeId`, CSD.`FreezingType`, CSD.`VarietyID`, `Galze`,
`SockingType`, `Salt`, `ProductionDate`, CSD.`PoID`, `RmLotID`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`,
`ShiftID`, `BarCodeID`, CSD.`PackingStyleID`, `StockHolderID`, CSD.`Remarks`, CSD.PlantID,
`StockStatus`, `AbStatus`, `ProdDayCode`,`ProdBatchCode`, `SoakingDate`, `TubNo`, `LineNumber`,`PoNo`,AGO.Name as Shift,ShortName as Variety,
APS.PackingStyle,TenantName,StoreName,PoNo,RPS.StationName,
(ActualMcs*TotalSlabs*SlabWeight*MFactor) as Kgs, (ActualLooseBags*SlabWeight*MFactor) as LKgs,ActualMcs,ActualLooseBags";

        $sqlQuery = "SELECT $sqlFields  FROM `CsDailyProductionData` as CSD
left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
left join AdminGeneralOptions as AGO on AGO.OptID=CSD.ShiftID
left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
left join CsTenant as CST on CST.TenantID=CSD.StockHolderID
left join CsStores as CSS on CSS.StoreID=CSD.UnitID
left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID $Query order by  DeviSupc,ShortName asc ";


        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $AvalMcs = $dataRow['AvalMcs'];
            $LooseBags = $dataRow['LooseBags'];
            $ActualMcs = $dataRow['ActualMcs'];
            $ActualLooseBags = $dataRow['ActualLooseBags'];
            $BalanceMcs = $ActualMcs - $AvalMcs;
            $BalanceBags = $ActualLooseBags - $LooseBags;

            $TotalWeight = round($dataRow['Kgs'] + $dataRow['LKgs'], 2);
            $aryBalance = $this->getBalanceKgs($TotalWeight, $dataRow['ProductionID'], $dataRow);
            $BalanceMcs = $aryBalance['BalanceMcs'];
            $BalanceBags = $aryBalance['BalanceLooseBags'];
            $BalanceKgs = $aryBalance['BalanceWeight'];
            if ($BalanceKgs <= 0) {
                $Status = 1;
            } else {
                $Status = 0;
            }


            // $ArrayDailyProductionData['SNo'] = ++$i;
            /*   $ArrayDailyProductionData['ProductionID'] = $dataRow['ProductionID'];
            $ArrayDailyProductionData['Variety'] = $dataRow['ShortName'];
            $ArrayDailyProductionData['Grade'] = $dataRow['Grade'];
            $ArrayDailyProductionData['PackingType'] = $dataRow['PackingTypeID'];
            $ArrayDailyProductionData['Mcs'] = $dataRow['Mcs'];
            $ArrayDailyProductionData['Bags'] = $dataRow['Bags'];
            $ArrayDailyProductionData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayDailyProductionData['ProductDescription'] = $dataRow['ProductDescription'];
            $ArrayDailyProductionData['OurSupc'] = $dataRow['DeviSupc'];
            $ArrayDailyProductionData['LineNumber'] = $dataRow['LineNumber'];
            $ArrayDailyProductionData['BatchCode'] = $dataRow['ProdBatchCode'];
            $ArrayDailyProductionData['BalanceMcs'] = $BalanceMcs;
            $ArrayDailyProductionData['BalanceBags'] = $BalanceBags;
            $ArrayDailyProductionData['Kgs'] = sprintf('%0.2f', $TotalWeight);
            $ArrayDailyProductionData['BalanceKgs'] = sprintf('%0.2f', $BalanceKgs);
            $ArrayDailyProductionData['Status'] = $Status;
*/
            $ArrayStockData['SNo'] = ++$i;
            $ArrayStockData['OurSupc'] = $dataRow['DeviSupc'];
            $ArrayStockData['ProductDescription'] = $dataRow['ProductDescription'];
            $ArrayStockData['ProductionID'] = $dataRow['ProductionID'];
            $ArrayStockData['TransactionDate'] = $dataRow['TransactionDate'];
            $ArrayStockData['ProductionDate'] = $dataRow['ProductionDate'];
            $ArrayStockData['Group'] = $dataRow['ProductionTypeID'];
            $ArrayStockData['StockHolderID'] = $dataRow['StockHolderID'];
            $ArrayStockData['StockHolderName'] = $dataRow['TenantName'];
            $ArrayStockData['PoNo'] = $dataRow['PoNo'];
            $ArrayStockData['PoID'] = $dataRow['PoID'];
            $ArrayStockData['StoreID'] = $dataRow['UnitID'];
            $ArrayStockData['StoreName'] = $dataRow['StoreName'];
            $ArrayStockData['PlantID'] = $dataRow['PlantID'];
            $ArrayStockData['ProductionPlantName'] = $dataRow['StationName'];
            $ArrayStockData['Coldstore'] = $dataRow['UnitID'];
            $ArrayStockData['ShiftID'] = $dataRow['ShiftID'];
            $ArrayStockData['Shift'] = $dataRow['Shift'];
            $ArrayStockData['BalanceMcs'] = $BalanceMcs;
            $ArrayStockData['BalanceBags'] = $BalanceBags;

            $ArrayStockData['ProductionCode'] = $dataRow['ProdDayCode'];
            $ArrayStockData['RmLotID'] = $dataRow['RmLotID'];
            $ArrayStockData['BatchCode'] = $dataRow['ProdBatchCode'];
            $ArrayStockData['PoNumber'] = $dataRow['PoNo'];
            $ArrayStockData['Variety'] = $dataRow['Variety'];
            $ArrayStockData['VarietyID'] = $dataRow['VarietyID'];
            $ArrayStockData['PoGradeId'] = $dataRow['PoGradeId'];
            $ArrayStockData['PackingType'] = $dataRow['PackingTypeID'];
            $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStockData['PackingStyleID'] = $dataRow['PackingStyleID'];
            $ArrayStockData['Grade'] = $dataRow['Grade'];
            $ArrayStockData['FreezingType'] = $dataRow['FreezingType'];
            $ArrayStockData['Galze'] = $dataRow['Galze'];
            $ArrayStockData['GlazePerc'] = $dataRow['GlazePerc'];
            $ArrayStockData['Soaking'] = $dataRow['SockingType'];
            // $ArrayStockData['Salt'] = $dataRow['Salt'];
            $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
            $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
            $ArrayStockData['AbStatus'] = $dataRow['AbStatus'];
            // $ArrayStockData['TubNo'] = $dataRow['TubNo'];
            $ArrayStockData['LineNumber'] = $dataRow['LineNumber'];
            $ArrayStockData['Remarks'] = $dataRow['Remarks'];
            $ArrayStockData['Mcs'] = $dataRow['AvalMcs'];
            $ArrayStockData['LooseBags'] = $dataRow['LooseBags'];
            $ArrayStockData['BalanceMcs'] = $BalanceMcs;
            $ArrayStockData['BalanceBags'] = $BalanceBags;
            $ArrayStockData['Kgs'] = sprintf('%0.2f', $TotalWeight);
            $ArrayStockData['BalanceKgs'] = sprintf('%0.2f', $BalanceKgs);
            $ArrayStockData['Status'] = $Status;


            $AryData[] = $ArrayStockData;
        }

        return $AryData;
    }

    function getProductionDetails($Query)
    {




        $dataRow = null;
        $sqlFields = " ProductionID,`AvalMcs`, `LooseBags`, `PackingTypeID`, CSD.`Grade`,   `PoGradeId`, CSD.`FreezingType`, CSD.`VarietyID`, `Galze`,
        `SockingType`, `Salt`, `ProductionDate`, CSD.`PoID`, `RmLotID`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`,
        `ShiftID`, `BarCodeID`, CSD.`PackingStyleID`, `StockHolderID`, CSD.`Remarks`, CSD.PlantID,
        `StockStatus`, `AbStatus`, `ProdDayCode`,`ProdBatchCode`, `SoakingDate`, `TubNo`, `LineNumber`,`PoNo`,AGO.Name as Shift,ShortName as Variety,
        APS.PackingStyle, APS1.PackingStyle as ActualPackingStyle,TenantName,StoreName,PoNo,RPS.StationName,
        (ActualMcs*APS.TotalSlabs*APS.SlabWeight*APS.MFactor) as Kgs, (ActualLooseBags*APS.SlabWeight*APS.MFactor) as LKgs,ActualMcs,ActualLooseBags";

        $sqlQuery = "SELECT $sqlFields  FROM `CsDailyProductionData` as CSD
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        left join AdminGeneralOptions as AGO on AGO.OptID=CSD.ShiftID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        left join AdminPackingStyles as APS1 on APS1.PCKID=SPV.PackingStyle
        left join CsTenant as CST on CST.TenantID=CSD.StockHolderID
        left join CsStores as CSS on CSS.StoreID=CSD.UnitID
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID $Query";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $ArrayStockData = array();

        $AvalMcs = $dataRow['AvalMcs'];
        $LooseBags = $dataRow['LooseBags'];
        $ActualMcs = $dataRow['ActualMcs'];
        $ActualLooseBags = $dataRow['ActualLooseBags'];

        $TotalWeight = round($dataRow['Kgs'] + $dataRow['LKgs'], 2);
        $aryBalance = $this->getBalanceKgs($TotalWeight, $dataRow['ProductionID'], $dataRow);
        $BalanceMcs = $aryBalance['BalanceMcs'];
        $BalanceBags = $aryBalance['BalanceLooseBags'];
        $BalanceKgs = $aryBalance['BalanceWeight'];



        if ($BalanceKgs == 0) {
            $Status = 1;
        } else if ($BalanceKgs < 0) {
            $Status = 2;
        } else {
            $Status = 0;
        }


        $ArrayStockData['ProductionID'] = $dataRow['ProductionID'];
        $ArrayStockData['TransactionDate'] = $dataRow['TransactionDate'];
        $ArrayStockData['ProductionDate'] = $dataRow['ProductionDate'];
        $ArrayStockData['Group'] = $dataRow['ProductionTypeID'];
        $ArrayStockData['StockHolderID'] = $dataRow['StockHolderID'];
        $ArrayStockData['StockHolderName'] = $dataRow['TenantName'];
        $ArrayStockData['PoNo'] = $dataRow['PoNo'];
        $ArrayStockData['PoID'] = $dataRow['PoID'];
        $ArrayStockData['StoreID'] = $dataRow['UnitID'];
        $ArrayStockData['StoreName'] = $dataRow['StoreName'];
        $ArrayStockData['PlantID'] = $dataRow['PlantID'];
        $ArrayStockData['ProductionPlantName'] = $dataRow['StationName'];
        $ArrayStockData['Coldstore'] = $dataRow['UnitID'];
        $ArrayStockData['ShiftID'] = $dataRow['ShiftID'];
        $ArrayStockData['Shift'] = $dataRow['Shift'];
        $ArrayStockData['BalanceMcs'] = $BalanceMcs;
        $ArrayStockData['BalanceBags'] = $BalanceBags;

        $ArrayStockData['ProductionCode'] = $dataRow['ProdDayCode'];
        $ArrayStockData['RmLotID'] = $dataRow['RmLotID'];
        $ArrayStockData['BatchCode'] = $dataRow['ProdBatchCode'];
        $ArrayStockData['PoNumber'] = $dataRow['PoNo'];
        $ArrayStockData['Variety'] = $dataRow['Variety'];
        $ArrayStockData['VarietyID'] = $dataRow['VarietyID'];
        $ArrayStockData['PoGradeId'] = $dataRow['PoGradeId'];
        $ArrayStockData['PackingType'] = $dataRow['PackingTypeID'];
        $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
        $ArrayStockData['ActualPackingStyle'] = $dataRow['ActualPackingStyle'];
        $ArrayStockData['PackingStyleID'] = $dataRow['PackingStyleID'];
        $ArrayStockData['Grade'] = $dataRow['Grade'];
        $ArrayStockData['FreezingType'] = $dataRow['FreezingType'];
        $ArrayStockData['Galze'] = $dataRow['Galze'];
        $ArrayStockData['GlazePerc'] = $dataRow['Galze'];
        $ArrayStockData['Soaking'] = $dataRow['SockingType'];
        // $ArrayStockData['Salt'] = $dataRow['Salt'];
        $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
        $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
        $ArrayStockData['AbStatus'] = $dataRow['AbStatus'];
        // $ArrayStockData['TubNo'] = $dataRow['TubNo'];
        $ArrayStockData['LineNumber'] = $dataRow['LineNumber'];
        $ArrayStockData['Remarks'] = $dataRow['Remarks'];
        $ArrayStockData['Mcs'] = $dataRow['AvalMcs'];
        $ArrayStockData['LooseBags'] = $dataRow['LooseBags'];
        $dataRow = $ArrayStockData['Kgs'] = sprintf('%0.2f', $TotalWeight);
        $dataRow = $ArrayStockData['BalanceKgs'] = sprintf('%0.2f', $BalanceKgs);
        $dataRow = $ArrayStockData['Status'] = $Status;

        return  $ArrayStockData;
    }


    public function postDailyProductionDetails($RequestBody)
    {


        $PostedUserID = 1;

        $ProductionID = $RequestBody['ProductionID'];
        $TransactionDate = $RequestBody['TransactionDate'];
        $ProductionDate = $RequestBody['ProductionDate'];
        $ProductionTypeID = $RequestBody['Group'];
        $StockHolderID = $RequestBody['StockHolderID'];
        $UnitID = $RequestBody['ColdstoreID'];
        $PlantID = $RequestBody['PlantID'];
        $ShiftID = $RequestBody['Shift'];
        $ProdDayCode = $RequestBody['ProductionCode'];
        $RmLotID = $RequestBody['RmLotID'];
        $ProdBatchCode = $RequestBody['BatchCode'];
        $PoID = $RequestBody['PoID'];
        $VarietyID = $RequestBody['Variety'];
        $PoGradeID = $RequestBody['PoGradeID'];
        $PackingTypeID = $RequestBody['PackingType'];
        $PackingStyleID = $RequestBody['PackingStyle'];
        $Grade = $RequestBody['Grade'];
        $FreezingType = $RequestBody['FreezingType'];
        $Galze = $RequestBody['Glaze'];
        $SockingType = $RequestBody['Soaking'];
        // $Salt = $RequestBody['Salt'];
        $ProductCategoryID = $RequestBody['ProductCategory'];
        $AbStatus = $RequestBody['AbStatus'];
        // $TubNo = $RequestBody['TubNo'];
        $LineNumber = $RequestBody['LineNumber'];
        $Remarks = $RequestBody['Remarks'];
        $StockOutID = $RequestBody['StockOutID'];
        // $TotalWeight = $RequestBody['TotalWeight']; //Display only
        // $BalanceWeight = $RequestBody['BalanceWeight']; //Display only
        $AvalMcs = $RequestBody['Mcs'];
        $LooseBags = $RequestBody['LooseBags'];
        $AryCurrentDate = getCurrentDateTime();

        $CreatedDateTime = $AryCurrentDate['TimeStamp'];
        $Year = $AryCurrentDate['Year'];


        $ActualKgs = $this->utilController->getCsStockAvalWeightinKgs($PackingStyleID, $AvalMcs, $LooseBags);


        if ($PoGradeID != 0) {
            $SqlVarietyId = "SELECT VarietyID FROM `ScrmPoGradeData` WHERE ID='$PoGradeID'";
            $stmtVarietyId = $this->conn->prepare($SqlVarietyId);
            $stmtVarietyId->execute();
            $RowVarietyId = $stmtVarietyId->fetch(PDO::FETCH_ASSOC);
            $VarietyID = $RowVarietyId['VarietyID'];
        }
        $stmt = false;

        if ($ProductionID == '') {
            if ($AvalMcs > 0 || $LooseBags > 0) {
                $sqlQuery = "INSERT INTO `CsDailyProductionData`(`ActualMcs`, `ActualLooseBags`, `AvalMcs`, `LooseBags`,
                `ActualKgs`,`AvalKgs`,
             `PackingTypeID`, `Grade`, `PoGradeId`, `FreezingType`, `VarietyID`, `Galze`, `SockingType`,
             `ProductionDate`, `PostedDate`,
              `PoID`, `RmLotID`, `ProductionTypeID`, `ProductCategoryID`, `UnitID`,`PlantID`, `ShiftID`, `BarCodeID`,
              `PackingStyleID`, `StockHolderID`, `Remarks`, `AbStatus`, `ProdDayCode`, `ProdBatchCode`,`LineNumber`,`StockOutID`)

              values('$AvalMcs','$LooseBags','$AvalMcs','$LooseBags','$ActualKgs','$ActualKgs','$PackingTypeID','$Grade','$PoGradeID',
              '$FreezingType','$VarietyID','$Galze','$SockingType','$ProductionDate','$CreatedDateTime','$PoID','$RmLotID','$ProductionTypeID',
              '$ProductCategoryID','$UnitID','$PlantID','$ShiftID','0','$PackingStyleID',
              '$StockHolderID','$Remarks','$AbStatus', '$ProdDayCode', '$ProdBatchCode','$LineNumber','$StockOutID')";

                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
                $ProductionID = $this->conn->lastInsertId();
                $this->auditLogger->logInsert('CsDailyProductionData', $ProductionID, $this->auditLogger->fetchRowById('CsDailyProductionData', 'ProductionID', $ProductionID));
                $RequestBody['ProductionID'] = $ProductionID;
                $Data['Error'] = null;
                $Data['Msg'] = 'Posted Successfully';
                $Data['NewAddedProdId'] = $ProductionID;
                $Data = $this->getDailyProductionDetails($RequestBody);
                return $Data;
            } else {
                $Data['Error'] = 'Please Enter Valid Mcs';
                $Data['Msg'] = 'Please Enter Valid Mcs';
                return $Data;
            }
        } else if ($ProductionID != '') {

            $SqlStockData = "SELECT * from CsDailyProductionData where ProductionID ='$ProductionID'";
            $stmtStockData = $this->conn->prepare($SqlStockData);
            $stmtStockData->execute();
            $RowStockData = $stmtStockData->fetch(PDO::FETCH_ASSOC);

            if ($RowStockData['ActualMcs'] == $RowStockData['AvalMcs']) {

                $sqlQuery = "UPDATE `CsDailyProductionData` SET  `ActualMcs`='$AvalMcs',
            `ActualLooseBags`='$LooseBags',`AvalMcs`='$AvalMcs',`LooseBags`='$LooseBags',`ActualKgs`='$ActualKgs',`AvalKgs`='$ActualKgs',
            `PackingTypeID`='$PackingTypeID',
            `Grade`='$Grade',`PoGradeId`='$PoGradeID',`FreezingType`='$FreezingType',`VarietyID`='$VarietyID',`Galze`='$Galze',`SockingType`='$SockingType',
            `ProductionDate`='$ProductionDate',`PostedDate`='$CreatedDateTime',`PoID`='$PoID',`RmLotID`='$RmLotID',
            `ProductionTypeID`='$ProductionTypeID',`ProductCategoryID`='$ProductCategoryID',`UnitID`='$UnitID',`PlantID`='$PlantID',`ShiftID`='$ShiftID',
            `PackingStyleID`='$PackingStyleID',`StockHolderID`='$StockHolderID',`Remarks`='$Remarks',`AbStatus`='$AbStatus',
            `ProdDayCode`='$ProdDayCode',`ProdBatchCode`='$ProdBatchCode'  ,`LineNumber` ='$LineNumber',`StockOutID` ='$StockOutID'
             where `ProductionID`='$ProductionID'";

                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
                $this->auditLogger->logUpdate('CsDailyProductionData', $ProductionID, $RowStockData, $this->auditLogger->fetchRowById('CsDailyProductionData', 'ProductionID', $ProductionID));
                // $fetchRequest['ProductionDate'] = $RequestBody['ProductionDate'];
                $fetchRequest['ProductionID'] = $ProductionID;
                if ($stmt) {

                    $Data['Error'] = null;
                    $Data['Msg'] = 'Updated Successfully';
                    return $this->getDailyProductionDetails($fetchRequest);
                }
            } else {
                // $fetchRequest['ProductionDate'] = $RequestBody['ProductionDate'];
                $fetchRequest['ProductionID'] = $ProductionID;
                $Data['Error'] = null;
                $Data['Msg'] = 'No Access To Edit';
                return $this->getDailyProductionDetails($fetchRequest);
            }
        }
    }

    public function getBalanceKgs($TotalWeight, $ProductionID, $proddataRow)
    {
        $sqlFields = "sum((ActualMcs*TotalSlabs*SlabWeight*MFactor)) as Kgs,
        sum((ActualLooseBags*SlabWeight*MFactor)) as LKgs,
        sum(ActualMcs) as ActualMcs,
        sum(ActualLooseBags) as ActualLooseBags";

        $sqlQuery = "SELECT $sqlFields
         FROM `CsStockData` as CSD
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID where ProductionID='$ProductionID' ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();


        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalWeight;
        $TotalWeight = round($TotalWeight - ($dataRow['Kgs'] + $dataRow['LKgs']), 2);
        $ArrayBalance['BalanceMcs'] = $proddataRow['ActualMcs'] - $dataRow['ActualMcs'];
        $ArrayBalance['BalanceLooseBags'] = $proddataRow['ActualLooseBags'] - $dataRow['ActualLooseBags'];
        $ArrayBalance['BalanceWeight'] = $TotalWeight;

        return  $ArrayBalance;
    }

    public function deleteDailyProduction($RequestBody)
    {
        $ProdID = $RequestBody['ProductionID'];
        $ProductionDate = $RequestBody['ProductionDate'];

        $sqlDelete = "SELECT count(*) StockCount FROM CsStockData  WHERE ProductionID ='$ProdID'";

        $stmtStockCount = $this->conn->prepare($sqlDelete);
        $stmtStockCount->execute();

        $Count = $stmtStockCount->fetch(PDO::FETCH_ASSOC);
        $StockCount = $Count['StockCount'];
        // echo "StockCount ".$StockCount;
        // exit;

        if ($StockCount == 0) {

            $sqlDelete = "DELETE FROM CsDailyProductionData WHERE ProductionID='$ProdID'";
        } else {
            $this->Error = 'Stock already available';
            $this->Msg = 'Stock already available';
        }

        $oldRow = $this->auditLogger->fetchRowById('CsDailyProductionData', 'ProductionID', $ProdID);
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();
        if ($ProdID != '') {
            $this->auditLogger->logDelete('CsDailyProductionData', $ProdID, $oldRow);
        }
        $RequestBody['ProductionID'] = '';

        if ($stmtDelete) {
            $this->Error = null;
            $this->Msg = 'Delete Successfull';
        }

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        return $this->getDailyProductionDetails($RequestBody);
    }
}
