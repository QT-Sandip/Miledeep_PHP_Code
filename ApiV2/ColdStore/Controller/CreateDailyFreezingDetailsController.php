<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
//include_once 'Controller/UtilController.php';

class CreateDailyFreezingDetailsController
{
    private $conn;
    //private $db_table = "ScrmApplicants";
    private $utilController;
    private $BalanceKgs;
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

    public function getFreezingDetails($RequestBody,$IsUpdate='')
    {
        $ProdID = $RequestBody['FreezingID'];
        
        $FreezingData = $this->getFreezingData($RequestBody,$IsUpdate);
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['FreezingData'] = $FreezingData;
        return $Data;
    }



    public function postFreezingDetails($RequestBody)
    {


        $PostedUserID = 1;
        $ProdID = $RequestBody['FreezingID'];
        $ProductionID = $RequestBody['ProductionID'];
        $TransactionDate = $RequestBody['TransactionDate'];
        $BarCodeID = $RequestBody['BarCodeID'];
        $Remarks = $RequestBody['Remarks'];
        $AvalMcs = $RequestBody['Mcs'];
        $LooseBags = $RequestBody['LooseBags'];
        $TubNo = $RequestBody['TubNo'];
        // if ($BarCodeID == '') {
        //     $BarCodeID = $this->getNewBarcode();
        // }

        $ProductDetails = $this->getProductionDetails($ProductionID);


        $TransactionDate = $ProductionDate = $ProductDetails['ProductionDate'];
        $ProductionTypeID = $ProductDetails['ProductionTypeID'];
        $StockHolderID = $ProductDetails['StockHolderID'];
        $UnitID = $ProductDetails['UnitID'];
        $PlantID = $ProductDetails['PlantID'];
        $ShiftID = $ProductDetails['ShiftID'];
        $ProdDayCode = $ProductDetails['ProdDayCode'];
        $RmLotID = $ProductDetails['RmLotID'];
        $ProdBatchCode = $ProductDetails['ProdBatchCode'];
        $PoID = $ProductDetails['PoID'];
        $VarietyID = $ProductDetails['VarietyID'];
        $PoGradeID = $ProductDetails['PoGradeID'];
        $PackingTypeID = $ProductDetails['PackingTypeID'];
        $PackingStyleID = $ProductDetails['PackingStyleID'];
        $Grade = $ProductDetails['Grade'];
        $FreezingType = $ProductDetails['FreezingType'];
        $Galze = $ProductDetails['Galze'];
        $SockingType = $ProductDetails['SockingType'];
        $ProductCategoryID = $ProductDetails['ProductCategoryID'];
        $AbStatus = $ProductDetails['AbStatus'];


        $AryCurrentDate = getCurrentDateTime();

        $CreatedDateTime = $AryCurrentDate['TimeStamp'];
        $Year = $AryCurrentDate['Year'];




        if ($this->Error == null && $ProdID == '') {
            if ($AvalMcs > 0 || $LooseBags > 0) {
                $sqlQuery = "INSERT INTO `ProdProductionData`(`ProductionID`,`Mcs`,`PackingType`, `LoosePouches`,
              `Grade`,  `FreezingType`,`PackingStyleID`, `Variety`, `ProductionDate`, `PoID`, `LotID`, `Group`, `Category`, `Unit`,`Shift`,
              `Remarks`,`TubNo`, `SockingType`,  `PostedDate`,`PlantID`,`StockHolderID`,`AbStatus`,
              `ProdDayCode`, `ProdBatchCode`, `TransactionDate`,               
                 `PoGradeId`, `Galze`)
              
              values('$ProductionID','$AvalMcs','$PackingTypeID','$LooseBags','$Grade',
              '$FreezingType','$PackingStyleID','$VarietyID','$ProductionDate','$PoID','$RmLotID',
              '$ProductionTypeID', '$ProductCategoryID','$UnitID','$ShiftID','$Remarks','$TubNo','$SockingType', '$CreatedDateTime','$PlantID',
              '$StockHolderID','$AbStatus', '$ProdDayCode', '$ProdBatchCode','$TransactionDate', '$PoGradeID','$Galze')";

                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
                // $StockID = $this->conn->lastInsertId();
                $savedProdId = $this->conn->lastInsertId();
                // $RequestBody['BarCodeID'] = $BarCodeID;
                $RequestBody['ProductionID'] = $ProductionID;
                $RequestBody['FreezingID'] = $savedProdId;
                if ($stmt) {
                    $createdFreezing = $this->auditLogger->fetchRowById('ProdProductionData', 'ProdID', $savedProdId);
                    $this->auditLogger->logInsert('ProdProductionData', $savedProdId, $createdFreezing, $RequestBody, [
                        'ReferenceNo' => $ProductionID,
                        'Remarks' => 'Cold store freezing detail created',
                    ]);
                }
                $Data['Error'] = null;
                $Data['Msg'] = 'Posted Successfully';
                return $this->getFreezingDetails($RequestBody,'');

            } else {
                $Data['Error'] = null;
                $Data['Msg'] = 'Please Enter Valid Mcs';
                return $Data;
            }
        } else if ($ProdID != '') {

            $SqlStockData = "SELECT * from ProdProductionData where ProdID ='$ProdID'";
            $stmtStockData = $this->conn->prepare($SqlStockData);
            $stmtStockData->execute();
            $RowStockData = $stmtStockData->fetch(PDO::FETCH_ASSOC);



            // if ($RowStockData['Mcs'] == $RowStockData['Mcs']) {
                $existingFreezing = $this->auditLogger->fetchRowById('ProdProductionData', 'ProdID', $ProdID);
                // `ArrivalNumber`='$ArrivalNumber',  because Auto Number
                $sqlQuery = "UPDATE `ProdProductionData` SET `Mcs`='$AvalMcs',
                `PackingType`='$PackingTypeID',`LoosePouches`='$LooseBags',`Grade`='$Grade',`FreezingType`='$FreezingType',
                `PackingStyleID`='$PackingStyleID',`Variety`='$VarietyID',`ProductionDate`='$ProductionDate',`PoID`='$PoID',
                `LotID`='$RmLotID',`Group`='$ProductionTypeID',`Category`='$ProductCategoryID',`Unit`='$UnitID',`Shift`='$ShiftID',
               `Remarks`='$Remarks', `TubNo`='$TubNo',`SockingType`='$SockingType',
                `PostedDate`='$CreatedDateTime',`PlantID`='$PlantID',`StockHolderID`='$StockHolderID',`AbStatus`='$AbStatus',
                `ProdDayCode`='$ProdDayCode',`ProdBatchCode`='$ProdBatchCode',
                `TransactionDate`='$TransactionDate',`PoGradeId`='$PoGradeID',`Galze`='$Galze' WHERE `ProdID`='$ProdID'";

                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
                if ($stmt) {
                    $updatedFreezing = $this->auditLogger->fetchRowById('ProdProductionData', 'ProdID', $ProdID);
                    $this->auditLogger->logUpdate('ProdProductionData', $ProdID, $existingFreezing, $updatedFreezing, $RequestBody, [
                        'ReferenceNo' => $ProductionID,
                        'Remarks' => 'Cold store freezing detail updated',
                    ]);
                    // $RequestBody['BarCodeID'] = $BarCodeID;
                    $RequestBody['ProductionID'] = $ProductionID;
                    $Data['Error'] = null;
                    $Data['Msg'] = 'Updated Successfully';
                    return $this->getFreezingDetails($RequestBody,'T');

                }
            // } else {
            //     $Data['Error'] = null;
            //     $Data['Msg'] = 'No Access To Edit';
            //     // return $Data;
            //     return $this->getFreezingDetails($RequestBody);
            // }


        }

        // else {
        //     return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
        // }
    }


    // function getNewBarcode()
    // {



    //     $SqlBarCodes = "SELECT BarCodeID FROM CsBarCodes ORDER by BarCodeID DESC LIMIT 1";
    //     $StmtBarCodes = $this->conn->prepare($SqlBarCodes);
    //     $StmtBarCodes->execute();
    //     $RecentBarCode = $StmtBarCodes->fetch(PDO::FETCH_ASSOC);
    //     $LastBarCodeID = $RecentBarCode['BarCodeID'];

    //     if ($LastBarCodeID == '') {
    //         $LastBarCodeID = 1;
    //     } else {
    //         $LastBarCodeID = $LastBarCodeID + 1;
    //     }


    //     date_default_timezone_set("Asia/Kolkata");
    //     $BarCodeDate = date("Y-m-d");
    //     $BarCodeValue = str_replace("-", "", $BarCodeDate);
    //     $BarCodeValue = $BarCodeValue . $LastBarCodeID;

    //     $sqlInsertBarCodeData = "INSERT INTO `CsBarCodes`(`BarCodeDate`, `BarCodeValue`, `BarCodeStatus`, `OnFloor`, `PostedUserID`) 
    //     VALUES ('$BarCodeDate','$BarCodeValue','1','0','1')";

    //     $stmtInsertBarCodeData = $this->conn->prepare($sqlInsertBarCodeData);
    //     $stmtInsertBarCodeData->execute();
    //     return $BarCodeID = $this->conn->lastInsertId();

    // }


    function getFreezingData($RequestBody,$IsUpdate)
    {

        // $StockID = $RequestBody['StockID'];
        // $BarCodeID = $RequestBody['BarCodeID'];
        $ProdID = $RequestBody['FreezingID'];  
        $ProductionID = $RequestBody['ProductionID'];


        if ($ProdID != '' &&  $IsUpdate=='') {
           
            $Query .= " and ProdID='$ProdID'";
        }

        // if($ProductionID!=''){
        //     $Query .= " and ProductionID='$ProductionID'";
        // }
        // $ProductionID = $RequestBody['ProductionID'];
        // $ProductDetails['TotalWeight']= $this->getProductionWeight($ProductionID);
        // $this->BalanceKgs = $this->getBalanceKgs($ProductDetails['TotalWeight'],$ProductionID);



        $dataRow = null;
        $sqlFields = " ProdID,`ProductionID`,`Mcs`, `LoosePouches`, CSD.`Group`, CSD.`Grade`,  CSD.`FreezingType`,
        CSD.`PackingStyleID`, CSD.`Variety` as VarietyID, `ProductionDate`,CSD.`PoID`,CSD.LotID,CSD.Group,CSD.Category,CSD.`Unit` as UnitID,
        CSD.`Shift` as ShiftID,AGO.Name as Shift,APS.PackingStyle,`ProductionLine`,CSD.`Remarks`,CSD.`TubNo`, `SockingType`,
        `StockHolderID`,CSD.PlantID,`AbStatus`,`ProdDayCode`,`ProdBatchCode`,ShortName as Variety,
        APS.PackingStyle,TenantName,StoreName,PoNo,RPS.StationName,CSD.`TransactionDate`, CSD.`PoGradeId`, CSD.`PackingType`, CSD.`Galze`";



        $sqlQuery = "  SELECT  ProdID,`ProductionID`,`Mcs`, `LoosePouches`, CSD.`Group`, CSD.`Grade`,  CSD.`FreezingType`,CSD.`PackingType`,
       CSD.`PackingStyleID`, CSD.`Variety` as VarietyID, `ProductionDate`,CSD.`PoID`,CSD.LotID,CSD.Group,CSD.Category,CSD.`Unit` as UnitID,
       CSD.`Shift` as ShiftID,AGO.Name as Shift,APS.PackingStyle,`ProductionLine`,CSD.`Remarks`,CSD.`TubNo`, `SockingType`,
       `StockHolderID`,CSD.PlantID,`AbStatus`,`ProdDayCode`,`ProdBatchCode`,ShortName as Variety,
       APS.PackingStyle,TenantName,StoreName,PoNo,RPS.StationName,CSD.`TransactionDate`, CSD.`PoGradeId`,  CSD.`Galze`  
       FROM `ProdProductionData` as CSD 
       left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
       left join AdminGeneralOptions as AGO on AGO.OptID=CSD.Shift
       left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.Variety
       left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
       left join CsTenant as CST on CST.TenantID=CSD.StockHolderID        
       left join CsStores as CSS on CSS.StoreID=CSD.Unit
       left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID
       
       WHERE   (Mcs !=0  or LoosePouches !=0) and  ProductionID='$ProductionID' $Query";

// echo $sqlQuery;exit;
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $Data['FreezingID'] = $ProdID;
        $Data['BalanceKgs'] = $this->BalanceKgs;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArrayFreezingData['FreezingID'] = $dataRow['ProdID'];
            $ArrayFreezingData['TransactionDate'] = $dataRow['TransactionDate'];
            $ArrayFreezingData['ProductionDate'] = $dataRow['ProductionDate'];
            $ArrayFreezingData['Group'] = $dataRow['Group'];
            $ArrayFreezingData['StockHolderID'] = $dataRow['StockHolderID'];
            $ArrayFreezingData['StockHolderName'] = $dataRow['TenantName'];
            $ArrayFreezingData['PoNo'] = $dataRow['PoNo'];
            $ArrayFreezingData['PoID'] = $dataRow['PoID'];
            $ArrayFreezingData['StoreID'] = $dataRow['UnitID'];
            $ArrayFreezingData['StoreName'] = $dataRow['StoreName'];
            $ArrayFreezingData['PlantID'] = $dataRow['PlantID'];
            $ArrayFreezingData['ProductionPlantName'] = $dataRow['StationName'];
            $ArrayFreezingData['Coldstore'] = $dataRow['UnitID'];
            $ArrayFreezingData['ShiftID'] = $dataRow['ShiftID'];
            $ArrayFreezingData['Shift'] = $dataRow['Shift'];
            $ArrayFreezingData['ProductionCode'] = $dataRow['ProdDayCode'];
            $ArrayFreezingData['RmLotID'] = $dataRow['LotID'];
            $ArrayFreezingData['BatchCode'] = $dataRow['ProdBatchCode'];
            $ArrayFreezingData['PoNumber'] = $dataRow['PoNo'];
            $ArrayFreezingData['Variety'] = $dataRow['Variety'];
            $ArrayFreezingData['VarietyID'] = $dataRow['VarietyID'];
            $ArrayFreezingData['PoGradeId'] = $dataRow['PoGradeId'];
            $ArrayFreezingData['PackingType'] = $dataRow['PackingType'];
            $ArrayFreezingData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayFreezingData['PackingStyleID'] = $dataRow['PackingStyleID'];
            $ArrayFreezingData['Grade'] = $dataRow['Grade'];
            $ArrayFreezingData['FreezingType'] = $dataRow['FreezingType'];
            $ArrayFreezingData['Galze'] = $dataRow['Galze'];
            $ArrayFreezingData['Soaking'] = $dataRow['SockingType'];
            $ArrayFreezingData['ProductCategory'] = $dataRow['Category'];
            $ArrayFreezingData['AbStatus'] = $dataRow['AbStatus'];
            $ArrayFreezingData['Remarks'] = $dataRow['Remarks'];
            $ArrayFreezingData['Mcs'] = $dataRow['Mcs'];
            $ArrayFreezingData['LooseBags'] = $dataRow['LoosePouches'];
            $ArrayFreezingData['TubNo'] = $dataRow['TubNo'];

            $AryFreezingData[] = $ArrayFreezingData;
        }

        $Data['Data'] = $AryFreezingData;
       // var_dump($ArrayFreezingData);
        return $Data;
    }

    function getProductionDetails($ProductID)
    {
        $dataRow = null;
        $SqlProdData = "SELECT * FROM `CsDailyProductionData`  WHERE ProductionID=$ProductID";
        $StmtProdData = $this->conn->prepare($SqlProdData);
        $StmtProdData->execute();
        $rowCount = $StmtProdData->rowCount();
        if ($rowCount > 0) {
            $dataRow = $StmtProdData->fetch(PDO::FETCH_ASSOC);
            $TotalWeight = round($dataRow['Kgs'] + $dataRow['LKgs'], 2);
            $dataRow['TotalWeight'] = $TotalWeight;
        }
        return $dataRow;

    }

    function getProductionWeight($ProductID)
    {
        $sqlFields = " 
        (ActualMcs*TotalSlabs*SlabWeight*MFactor) as Kgs, (ActualLooseBags*SlabWeight*MFactor) as LKgs";

        $sqlQuery = "SELECT $sqlFields  FROM `CsDailyProductionData` as CSD 
         left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
         WHERE ProductionID=$ProductID";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalWeight = round($dataRow['Kgs'] + $dataRow['LKgs'], 2);

        return $TotalWeight;
    }

    public function getBalanceKgs($TotalWeight, $ProductionID)
    {
        $sqlFields = "sum((ActualMcs*TotalSlabs*SlabWeight*MFactor)) as Kgs,sum((ActualLooseBags*SlabWeight*MFactor)) as LKgs";

        $sqlQuery = "SELECT $sqlFields FROM `CsStockData` as CSD
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        where ProductionID='$ProductionID' ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();


        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalWeight;
        $TotalWeight = round($TotalWeight - ($dataRow['Kgs'] + $dataRow['LKgs']), 2);

        return $TotalWeight;

    }

}
?>
