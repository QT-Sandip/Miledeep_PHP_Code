<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
//include_once 'Controller/UtilController.php';

class CreateStockController
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

    public function getStockDetails($RequestBody)
    {
        $StockID = $RequestBody['StockID'];
        $StockData = $this->getStockData($RequestBody);
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['StockData'] = $StockData;
        return $Data;
    }

    // public function postDeleteArrivalBrkUP($RequestBody)
    // {
    // 	$ArrivalID = $RequestBody['ArrivalID'];
    // 	$BreakUpID = $RequestBody['BreakUpID'];

    // 	$sqlCQuery = "select Count from  `RmArrivalBreakUpDetails` where ArrivalBrkUpID='$BreakUpID'";
    // 	$stmtC = $this->conn->prepare($sqlCQuery);
    // 	$stmtC->execute();
    // 	$dataRow = $stmtC->fetch(PDO::FETCH_ASSOC);
    // 	$ArrivalCount = $dataRow['Count'];

    // 	$sqlQuery = "delete  from `RmArrivalBreakUpDetails` where ArrivalBrkUpID='$BreakUpID'";
    // 	$stmt = $this->conn->prepare($sqlQuery);
    // 	$stmt->execute();

    // 	/*$sqlQuery = "delete  from  `RmLotDetatils` where RmReferenceID='$ArrivalID' and BreakUpID='$BreakUpID'";
    // 		  $stmt = $this->conn->prepare($sqlQuery);
    // 		  $stmt->execute(); 
    // 		  */
    // 	$this->getRmBreakupGroup($ArrivalID, $ArrivalCount);


    // 	$sqlQuery = "select * from  `RmArrivalBreakUpDetails` where ArrivalID='$ArrivalID'";
    // 	$stmt = $this->conn->prepare($sqlQuery);
    // 	$stmt->execute();



    // 	if ($stmt->rowCount() == 0) {
    // 		$sqlQuery = "delete  from `RmArrivalDetatils` where RmArrivalID='$ArrivalID'";
    // 		$stmt = $this->conn->prepare($sqlQuery);
    // 		$stmt->execute();
    // 	}


    // 	$ArrivalID = $RequestBody['ArrivalID'];
    // 	$BreakUpData = $this->getArrivalBreakUp($ArrivalID);
    // 	$ArrivalData = $this->getArrivalData($ArrivalID);
    // 	$GrandTotal = $this->getGrandTotal($ArrivalID);
    // 	$overAllData = $this->getOverAllArrival($ArrivalID);


    // 	$ArrivalData['OverAllWeight'] = $overAllData['OverAllWeight'];
    // 	$ArrivalData['OverAllAmount'] = $overAllData['OverAllAmount'];
    // 	$ArrivalData['OverAllBalWeight'] = $overAllData['OverAllBalWeight'];
    // 	$ArrivalData['OverAllBalAmount'] = $overAllData['OverAllBalAmount'];


    // 	$Data['Error'] = null;
    // 	$Data['Msg'] = 'Deleted Posted Successful';
    // 	$Data['BreakUpData'] = $BreakUpData;
    // 	$Data['ArrivalData'] = $ArrivalData;
    // 	$Data['GrandTotal'] = $GrandTotal;

    // 	return $Data;
    // }

    public function postStockDetails($RequestBody)
    {

      
        $PostedUserID = 1;
        $StockID = $RequestBody['StockID'];
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
        // $LineNumber = $RequestBody['LineNumber']; //new field need to add in DB
        
       
        // $TotalWeight = $RequestBody['TotalWeight']; //Display only
        // $BalanceWeight = $RequestBody['BalanceWeight']; //Display only
        $Remarks = $RequestBody['Remarks'];
        $AvalMcs = $RequestBody['Mcs'];
        $LooseBags = $RequestBody['LooseBags'];
        $BarCodeID = $RequestBody['BarCodeID'];
        $AryCurrentDate = getCurrentDateTime();

        $CreatedDateTime = $AryCurrentDate['TimeStamp'];
        $Year = $AryCurrentDate['Year'];
        if ($BarCodeID == '') {
            $BarCodeType = 0;
        } else {
            $BarCodeType = 1;
        }


        if ($BarCodeType == 0) {

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
         VALUES ('$BarCodeDate','$BarCodeValue','1','0','$PostedUserID')";
        
            $stmtInsertBarCodeData = $this->conn->prepare($sqlInsertBarCodeData);
            $stmtInsertBarCodeData->execute();
            $BarCodeID = $this->conn->lastInsertId();
            if ($stmtInsertBarCodeData) {
                $createdBarcode = $this->auditLogger->fetchRowById('CsBarCodes', 'BarCodeID', $BarCodeID);
                $this->auditLogger->logInsert('CsBarCodes', $BarCodeID, $createdBarcode, $RequestBody, [
                    'ReferenceNo' => $BarCodeValue,
                    'Remarks' => 'Cold store stock barcode created',
                ]);
            }
        }

        if ($PoGradeID != 0) {
            $SqlVarietyId = "SELECT VarietyID FROM `ScrmPoGradeData` WHERE ID='$PoGradeID'";           
            $stmtVarietyId = $this->conn->prepare($SqlVarietyId);
            $stmtVarietyId->execute();
            $RowVarietyId = $stmtVarietyId->fetch(PDO::FETCH_ASSOC);
            $VarietyID = $RowVarietyId['VarietyID'];
        }
        $stmt = false;

        if ($this->Error == null && $StockID == '') {
            if ($AvalMcs > 0 || $LooseBags > 0) {
                $sqlQuery = "INSERT INTO `CsStockData`(`ActualMcs`, `ActualLooseBags`, `AvalMcs`, `LooseBags`, 
             `PackingTypeID`, `Grade`, `PoGradeId`, `FreezingType`, `VarietyID`, `Galze`, `SockingType`,  
             `ProductionDate`,`TransactionDate`, `PostedDate`,
              `PoID`, `RmLotID`, `ProductionTypeID`, `ProductCategoryID`, `UnitID`,`PlantID`, `ShiftID`, `BarCodeID`,
              `PackingStyleID`, `StockHolderID`, `Remarks`, `AbStatus`, `ProdDayCode`, `ProdBatchCode`)
              
              values('$AvalMcs','$LooseBags','$AvalMcs','$LooseBags','$PackingTypeID','$Grade','$PoGradeID',
              '$FreezingType','$VarietyID','$Galze','$SockingType','$ProductionDate','$TransactionDate','$CreatedDateTime','$PoID','$RmLotID','$ProductionTypeID',
              '$ProductCategoryID','$UnitID','$PlantID','$ShiftID','$BarCodeID','$PackingStyleID',
              '$StockHolderID','$Remarks','$AbStatus', '$ProdDayCode', '$ProdBatchCode')";

                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
                $savedStockId = $this->conn->lastInsertId();
                $RequestBody['BarCodeID']= $BarCodeID;
                $RequestBody['StockID'] = $savedStockId;
                if ($stmt) {
                    $createdStock = $this->auditLogger->fetchRowById('CsStockData', 'StockID', $savedStockId);
                    $this->auditLogger->logInsert('CsStockData', $savedStockId, $createdStock, $RequestBody, [
                        'ReferenceNo' => $BarCodeID,
                        'Remarks' => 'Cold store stock created',
                    ]);
                }
               $Data['Error'] = null;
               $Data['Msg'] = 'Posted Successfully';
               return $this->getStockDetails($RequestBody);

            } else {
                $Data['Error'] = null;
                $Data['Msg'] = 'Please Enter Valid Mcs';
                return $Data;
            }
        } else if ($StockID != '') {

            $SqlStockData = "SELECT * from CsStockData where StockID ='$StockID'";
            $stmtStockData = $this->conn->prepare($SqlStockData);
            $stmtStockData->execute();
            $RowStockData = $stmtStockData->fetch(PDO::FETCH_ASSOC);

            if ($RowStockData['ActualMcs'] == $RowStockData['AvalMcs']) {
                $existingStock = $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID);
                // `ArrivalNumber`='$ArrivalNumber',  because Auto Number
                $sqlQuery = "UPDATE `CsStockData` SET  `ActualMcs`='$AvalMcs',
            `ActualLooseBags`='$LooseBags',`AvalMcs`='$AvalMcs',`LooseBags`='$LooseBags',`PackingTypeID`='$PackingTypeID',
            `Grade`='$Grade',`PoGradeId`='$PoGradeID',`FreezingType`='$FreezingType',`VarietyID`='$VarietyID',`Galze`='$Galze',`SockingType`='$SockingType',
            `ProductionDate`='$ProductionDate',`TransactionDate`='$TransactionDate',`PostedDate`='$CreatedDateTime',`PoID`='$PoID',`RmLotID`='$RmLotID',
            `ProductionTypeID`='$ProductionTypeID',`ProductCategoryID`='$ProductCategoryID',`UnitID`='$UnitID',`PlantID`='$PlantID',`ShiftID`='$ShiftID',
            `PackingStyleID`='$PackingStyleID',`StockHolderID`='$StockHolderID',`Remarks`='$Remarks',`AbStatus`='$AbStatus',
            `ProdDayCode`='$ProdDayCode',`ProdBatchCode`='$ProdBatchCode' where `StockID`='$StockID'";

                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
                if ($stmt) {
                    $updatedStock = $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID);
                    $this->auditLogger->logUpdate('CsStockData', $StockID, $existingStock, $updatedStock, $RequestBody, [
                        'ReferenceNo' => $BarCodeID,
                        'Remarks' => 'Cold store stock updated',
                    ]);
                    $RequestBody['BarCodeID']= $BarCodeID;
                      $Data['Error'] = null;
                    $Data['Msg'] = 'Updated Successfully';
                    return $this->getStockDetails($RequestBody);
                  
                }
            } else {
                $Data['Error'] = null;
                $Data['Msg'] = 'No Access To Edit';
               // return $Data;
                return $this->getStockDetails($RequestBody);
            }


        }

        // else {
        //     return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
        // }
    }




    function getStockData($RequestBody)
    {

        $StockID=$RequestBody['StockID'];
        $BarCodeID=$RequestBody['BarCodeID'];

       
        if($StockID!=''){
        $Query=" and StockID='$StockID'";
        }
        $dataRow = null;
        $sqlFields = " StockID,`AvalMcs`, `LooseBags`, `PackingTypeID`, CSD.`Grade`,   `PoGradeId`, CSD.`FreezingType`, CSD.`VarietyID`, `Galze`, 
        `SockingType`, `Salt`, `ProductionDate`, CSD.`PoID`, `RmLotID`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`, 
        `ShiftID`, `BarCodeID`, CSD.`PackingStyleID`, `StockHolderID`, CSD.`Remarks`, CSD.PlantID,
        `StockStatus`,TransactionDate, `AbStatus`, `ProdDayCode`,`ProdBatchCode`, `SoakingDate`, `TubNo`, `LineNumber`,`PoNo`,AGO.Name as Shift,ShortName as Variety,
        APS.PackingStyle,TenantName,StoreName,PoNo,RPS.StationName";

          $sqlQuery = "SELECT $sqlFields  FROM `CsStockData` as CSD 
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        left join AdminGeneralOptions as AGO on AGO.OptID=CSD.ShiftID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        left join CsTenant as CST on CST.TenantID=CSD.StockHolderID        
        left join CsStores as CSS on CSS.StoreID=CSD.UnitID
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID
        
        WHERE  BarCodeID ='$BarCodeID' and (AvalMcs !=0  or LooseBags !=0) $Query"; 
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $Data['BarCodeID']=$BarCodeID;
       while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){

        $ArrayStockData['StockID'] = $dataRow['StockID'];
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
        $ArrayStockData['Soaking'] = $dataRow['SockingType'];
        // $ArrayStockData['Salt'] = $dataRow['Salt'];
        $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
        $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
        $ArrayStockData['AbStatus'] = $dataRow['AbStatus'];
        // $ArrayStockData['TubNo'] = $dataRow['TubNo'];
        // $ArrayStockData['LineNumber'] = $dataRow['LineNumber'];
        $ArrayStockData['Remarks'] = $dataRow['Remarks'];
        $ArrayStockData['Mcs'] = $dataRow['AvalMcs'];
        $ArrayStockData['LooseBags'] = $dataRow['LooseBags'];
     //   $ArrayStockData['Msg']= $this->DisplayErrorMessage();
     $AryStockData[]=$ArrayStockData;
    }

$Data['Data']=$AryStockData;
        //  $AryStockData['BarCodeID'] = $BarCodeID;
        return $Data;
    }

   }
?>
