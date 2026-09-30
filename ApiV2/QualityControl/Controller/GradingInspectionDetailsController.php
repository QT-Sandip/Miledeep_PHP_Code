<?php

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class GradingInspectionDetailsController{
    private $conn;
    private $tableName = "GradingInspectionDetails";
    private $utilController;
    private $auditLogger;

    // Db connection
    public function __construct($db)
    {   
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'QualityControl');
    }

    // GET ALL
    public function getGradingInspectionDetails($RequestBody)
    {
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $sqlGradingInspectionDetails = "SELECT * FROM `" . $this->tableName . "` WHERE `ArrivalDate` = '$ArrivalDate' ORDER BY `ArrivalDate` DESC";
        $stmt = $this->conn->prepare($sqlGradingInspectionDetails);
        $stmt->execute();
        $i = 0;
        $Data = array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayBookingList = [];
            $ArrayBookingList['InspectionID'] = $dataRow['InspectionID'];
            $ArrayBookingList['ArrivalDate'] = $dataRow['ArrivalDate'];
            $ArrayBookingList['BatchCode'] = $dataRow['BatchCode'];
            $ArrayBookingList['Variety'] = $dataRow['Variety'];
            $ArrayBookingList['Grade'] = $dataRow['Grade'];
            $ArrayBookingList['GeneralAppearance'] = $dataRow['GeneralAppearance'];
            $ArrayBookingList['Color'] = $dataRow['Color'];
            $ArrayBookingList['Freshness'] = $dataRow['Freshness'];
            $ArrayBookingList['Odour'] = $dataRow['Odour'];
            $ArrayBookingList['Texture'] = $dataRow['Texture'];
            $ArrayBookingList['Temperature'] = $dataRow['Temperature'];
            $ArrayBookingList['CountPerLb'] = $dataRow['CountPerLb'];
            $ArrayBookingList['BigPieceperGram'] = $dataRow['BigPieceperGram'];
            $ArrayBookingList['SmallPieceperGram'] = $dataRow['SmallPieceperGram'];
            $ArrayBookingList['Uniformity'] = $dataRow['Uniformity'];
            $ArrayBookingList['TotalPiecesperGram'] = $dataRow['TotalPiecesperGram'];
            $ArrayBookingList['Deterioration'] = $dataRow['Deterioration'];
            $ArrayBookingList['Discolouration'] = $dataRow['Discolouration'];
            $ArrayBookingList['BlackSpots'] = $dataRow['BlackSpots'];
            $ArrayBookingList['SoftShell'] = $dataRow['SoftShell'];
            $ArrayBookingList['HONQuantity'] = $dataRow['HONQuantity'];
            $ArrayBookingList['HLQuantity'] = $dataRow['HLQuantity'];
            $ArrayBookingList['Yield'] = $dataRow['Yield'];
            $ArrayBookingList['HangingMeat'] = $dataRow['HangingMeat'];
            $ArrayBookingList['VerifiedBy'] = $dataRow['VerifiedBy'];
            $ArrayBookingList['InspectedBy'] = $dataRow['InspectedBy'];
               $ArrayBookingList['Remarks'] = $dataRow['Remarks'];
            $ArrayBookingList['ArrivalID'] = $dataRow['ArrivalID'];
            $Data[] = $ArrayBookingList;
        }
        $result = [];
        $result['Error'] = false;
        $result['Msg'] = "Fetch Grading Inspection Details Success "+$sqlGradingInspectionDetails ;
        $result['Data'] = $Data;
        return $result;
    }
    public function postGradingInspectionDetails($RequestBody)
    {
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $BatchCode = $RequestBody['BatchCode'];
        $Variety = $RequestBody['Variety'];
        $Grade = $RequestBody['Grade'];
        $GeneralAppearance = $RequestBody['GeneralAppearance'];
        $Color = $RequestBody['Color'];
        $Freshness = $RequestBody['Freshness'];
        $Odour = $RequestBody['Odour'];
        $Texture = $RequestBody['Texture'];
        $Temperature = $RequestBody['Temperature'];
        $CountPerLb = $RequestBody['CountPerLb'];
        $BigPieceperGram = $RequestBody['BigPieceperGram'];
        $SmallPieceperGram = $RequestBody['SmallPieceperGram'];
        $Uniformity = $RequestBody['Uniformity'];
        $TotalPiecesperGram = $RequestBody['TotalPiecesperGram'];
        $Deterioration = $RequestBody['Deterioration'];
        $Discolouration = $RequestBody['Discolouration'];
        $BlackSpots = $RequestBody['BlackSpots'];
        $SoftShell = $RequestBody['SoftShell'];
        $HONQuantity = $RequestBody['HONQuantity'];
        $HLQuantity = $RequestBody['HLQuantity'];
        $VerifiedBy = $RequestBody['VerifiedBy'];
        $InspectedBy = $RequestBody['InspectedBy'];
        $Yield = $RequestBody['Yield'];
        $HangingMeat = $RequestBody['HangingMeat'];
          $Remarks = $RequestBody['Remarks'];
        $ArrivalID = $RequestBody['ArrivalID'] ;
        $sqlInsertGradingInspectionDetails = "INSERT INTO `" . $this->tableName . "` 
        (`ArrivalDate`, `BatchCode`, `Variety`, `Grade`, `GeneralAppearance`, `Color`, `Freshness`, `Odour`,
        `Texture`, `Temperature`, `CountPerLb`, `BigPieceperGram`, `SmallPieceperGram`, `Uniformity`, `TotalPiecesperGram`, `Deterioration`, `Discolouration`, `BlackSpots`, `SoftShell`, `HONQuantity`, `HLQuantity`, `Yield`, `HangingMeat`, `ArrivalID`,`VerifiedBy`,`InspectedBy`,`Remarks`) 
        VALUES ('$ArrivalDate', '$BatchCode','$Variety', '$Grade', '$GeneralAppearance', '$Color', '$Freshness', '$Odour',
        '$Texture', '$Temperature', '$CountPerLb', '$BigPieceperGram', '$SmallPieceperGram', '$Uniformity', '$TotalPiecesperGram', '$Deterioration', '$Discolouration', '$BlackSpots', '$SoftShell', '$HONQuantity', '$HLQuantity', '$Yield', '$HangingMeat', '$ArrivalID','$VerifiedBy','$InspectedBy','$Remarks')";
        $stmt = $this->conn->prepare($sqlInsertGradingInspectionDetails);
        $stmt->execute();
        if($stmt){
            $lastInsertId = $this->conn->lastInsertId();
            $createdRecord = $this->auditLogger->fetchRowById($this->tableName, 'InspectionID', $lastInsertId);
            $this->auditLogger->logInsert($this->tableName, $lastInsertId, $createdRecord, $RequestBody, [
                'ReferenceNo' => $ArrivalDate,
                'Remarks' => 'QC grading inspection created',
            ]);
            $result = [];
            $result['Error'] = false;
            $result['Msg'] = "Post Grading Inspection Details Success";
            $result['Data'] = [];
            return $result;
        }else{
            $result = [];
            $result['Error'] = true;
            $result['Msg'] = "Post Grading Inspection Details Failed";
            $result['Data'] = [];
            return $result;
        }
    }
    public function updateGradingInspectionDetails($RequestBody)
    {
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $BatchCode = $RequestBody['BatchCode'];
        $Variety = $RequestBody['Variety'];
        $Grade = $RequestBody['Grade'];
        $GeneralAppearance = $RequestBody['GeneralAppearance'];
        $Color = $RequestBody['Color'];
        $Freshness = $RequestBody['Freshness'];
        $Odour = $RequestBody['Odour'];
        $Texture = $RequestBody['Texture'];
        $Temperature = $RequestBody['Temperature'];
        $CountPerLb = $RequestBody['CountPerLb'];
        $BigPieceperGram = $RequestBody['BigPieceperGram'];
        $SmallPieceperGram = $RequestBody['SmallPieceperGram'];
        $Uniformity = $RequestBody['Uniformity'];
        $TotalPiecesperGram = $RequestBody['TotalPiecesperGram'];
        $Deterioration = $RequestBody['Deterioration'];
        $Discolouration = $RequestBody['Discolouration'];
        $BlackSpots = $RequestBody['BlackSpots'];
        $SoftShell = $RequestBody['SoftShell'];
        $HONQuantity = $RequestBody['HONQuantity'];
        $HLQuantity = $RequestBody['HLQuantity'];
        $Yield = $RequestBody['Yield'];
        $HangingMeat = $RequestBody['HangingMeat'];
        $VerifiedBy = $RequestBody['VerifiedBy'];
        $InspectedBy = $RequestBody['InspectedBy'];
        $ArrivalID = $RequestBody['ArrivalID'];
         $Remarks = $RequestBody['Remarks'];
        $InspectionID = $RequestBody['InspectionID'];
        $existingRecord = $this->auditLogger->fetchRowById($this->tableName, 'InspectionID', $InspectionID);
        $sqlUpdateGradingInspectionDetails = "UPDATE `" . $this->tableName . "`
        SET `ArrivalDate` = '$ArrivalDate', `ArrivalID` = '$ArrivalID',`BatchCode` = '$BatchCode', `Variety` = '$Variety', `Grade` = '$Grade',
        `GeneralAppearance` = '$GeneralAppearance', `Color` = '$Color', `Freshness` = '$Freshness', `Odour` = '$Odour',
        `Texture` = '$Texture', `Temperature` = '$Temperature', `CountPerLb` = '$CountPerLb', `BigPieceperGram` = '$BigPieceperGram',
        `SmallPieceperGram` = '$SmallPieceperGram', `Uniformity` = '$Uniformity', `TotalPiecesperGram` = '$TotalPiecesperGram',
        `Deterioration` = '$Deterioration', `Discolouration` = '$Discolouration', `BlackSpots` = '$BlackSpots',
        `SoftShell` = '$SoftShell', `HONQuantity` = '$HONQuantity', `HLQuantity` = '$HLQuantity', `Yield` = '$Yield',  `HangingMeat` = '$HangingMeat', `VerifiedBy`= '$VerifiedBy',
        `InspectedBy`= '$InspectedBy',`Remarks`= '$Remarks'
        WHERE `InspectionID` = '$InspectionID'";
        $stmt = $this->conn->prepare($sqlUpdateGradingInspectionDetails);
        $stmt->execute();

        if(!$stmt){
            $result = [];
            $result['Error'] = true;
            $result['Msg'] = "Update Grading Inspection Details Failed";
            $result['Data'] = [];
            return $result;
        }
        $updatedRecord = $this->auditLogger->fetchRowById($this->tableName, 'InspectionID', $InspectionID);
        $this->auditLogger->logUpdate($this->tableName, $InspectionID, $existingRecord, $updatedRecord, $RequestBody, [
            'ReferenceNo' => $InspectionID,
            'Remarks' => 'QC grading inspection updated',
        ]);
        $result = [];
        $result['Error'] = false;
        $result['Msg'] = "Update Grading Inspection Details Success";
        $result['Data'] = [];
        return $result;
    }
    public function deleteGradingInspectionDetails($RequestBody)
    {
        $InspectionID = $RequestBody['InspectionID'];
        $existingRecord = $this->auditLogger->fetchRowById($this->tableName, 'InspectionID', $InspectionID);
        $sqlDeleteGradingInspectionDetails = "DELETE FROM `" . $this->tableName . "` WHERE `InspectionID` = '$InspectionID'";
        $stmt = $this->conn->prepare($sqlDeleteGradingInspectionDetails);
        $stmt->execute();
        if(!$stmt){
            $result = [];
            $result['Error'] = true;
            $result['Msg'] = "Delete Grading Inspection Details Failed";
            $result['Data'] = [];
            return $result;
        }
        $this->auditLogger->logDelete($this->tableName, $InspectionID, $existingRecord, $RequestBody, [
            'ReferenceNo' => $InspectionID,
            'Remarks' => 'QC grading inspection deleted',
        ]);
        $result = [];
        $result['Error'] = false;
        $result['Msg'] = "Delete Grading Inspection Details Success";
        $result['Data'] = [];
        return $result;
    }
    public function getLotsDetails($RequestBody)
    {
        $ArrivalDate = $RequestBody['ArrivalDate'];
      $sqlLotsDetails = "SELECT 
                                RAD.ArrivalNumber,
                                RAD.RmArrivalID,
                                RAD.ArrivalDate, 
                                DCL.Variety
                            FROM `DeheadingCheckList` AS DCL 
                            LEFT JOIN `RmArrivalDetatils` AS RAD
                                ON FIND_IN_SET(RAD.RmArrivalID, DCL.BatchCode)
                            WHERE RAD.ArrivalDate = '$ArrivalDate' 
                            ORDER BY RAD.ArrivalDate, RAD.ArrivalNumber DESC
                            ";
        $stmt = $this->conn->prepare($sqlLotsDetails);
        $stmt->execute();
        $i = 0;
        $Data = array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArrayBookingList = [];
            $ArrayBookingList['SNO'] = ++$i;
            $ArrayBookingList['RmArrivalID'] = $dataRow['RmArrivalID'];
            $ArrayBookingList['ArrivalNumber'] = $dataRow['ArrivalNumber'];
            $ArrayBookingList['ArrivalDate'] = $dataRow['ArrivalDate'];
            $ArrayBookingList['Variety'] = $dataRow['Variety'];

            $Data[] = $ArrayBookingList;
        }
        $result = [];
        $result['Error'] = false;
        $result['Msg'] = "Fetch Lots Details Success";
        $result['Data'] = $Data;
        return $result;
    }
   public function getGrade($RequestBody)
{

   $BatchCode = $RequestBody['batchCode'];
  
    //  $sqlGrade = "SELECT 
    //     PGB.PackingGrade, 
    //     PGB.HLWeight
    // FROM GradingInspectionDetails GID
    // JOIN RmLotDetatils RLD ON GID.BatchCode = RLD.LotNumber
    // JOIN PpcHLGradingBreakup PGB ON RLD.RmLotID = PGB.LotID
    // WHERE GID.BatchCode = '$BatchCode'
    //   AND PGB.PackingGrade IS NOT NULL
    // ORDER BY PGB.PackingGrade ASC";

     $sqlGrade = "SELECT
    PGB.PackingGrade,
    PGB.HLWeight
FROM
    PpcHLGradingBreakup PGB
LEFT JOIN RmLotDetatils RLD ON
    RLD.RmLotID = PGB.LotID
WHERE
    RLD.LotNumber = '$BatchCode' AND PGB.PackingGrade IS NOT NULL
ORDER BY
    PGB.PackingGrade ASC";


        // echo $sqlGrade;
        // exit;

 $stmt = $this->conn->prepare($sqlGrade);
        $stmt->execute();
        $Data = array();

        
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
       
        //     echo $dataRow;
        // exit;
          $ArrayBookingList = [];
            $ArrayBookingList['packingGrade'] = $dataRow['PackingGrade'];
            $ArrayBookingList['hLWeight'] = $dataRow['HLWeight'];

            $Data[] = $ArrayBookingList;

        }
        $result = [];
        $result['Error'] = false;
        $result['Msg'] = "Fetch Grade Details Success";
        $result['Data'] = $Data;
        return $result;
        
}

    public function getGradingInspectionReportDetails($RequestBody)
{
    $ArrivalDate = $RequestBody['ArrivalDate'];
    $BatchCode = isset($RequestBody['BatchCode']) ? trim($RequestBody['BatchCode']) : '';

    $sqlGradingInspectionDetails = "SELECT * FROM `" . $this->tableName . "` WHERE `ArrivalDate` = '$ArrivalDate'";

    if (!empty($BatchCode)) {
        $sqlGradingInspectionDetails .= " AND `BatchCode` = '$BatchCode'";
    }

    $sqlGradingInspectionDetails .= " ORDER BY `ArrivalDate` DESC";

    $stmt = $this->conn->prepare($sqlGradingInspectionDetails);
    $stmt->execute();

    $Data = array();
    while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ArrayBookingList = [];
        $ArrayBookingList['InspectionID'] = $dataRow['InspectionID'];
        $ArrayBookingList['ArrivalDate'] = $dataRow['ArrivalDate'];
        $ArrayBookingList['BatchCode'] = $dataRow['BatchCode'];
        $ArrayBookingList['Variety'] = $dataRow['Variety'];
        $ArrayBookingList['Grade'] = $dataRow['Grade'];
        $ArrayBookingList['GeneralAppearance'] = $dataRow['GeneralAppearance'];
        $ArrayBookingList['Color'] = $dataRow['Color'];
        $ArrayBookingList['Freshness'] = $dataRow['Freshness'];
        $ArrayBookingList['Odour'] = $dataRow['Odour'];
        $ArrayBookingList['Texture'] = $dataRow['Texture'];
        $ArrayBookingList['Temperature'] = $dataRow['Temperature'];
        $ArrayBookingList['CountPerLb'] = $dataRow['CountPerLb'];
        $ArrayBookingList['BigPieceperGram'] = $dataRow['BigPieceperGram'];
        $ArrayBookingList['SmallPieceperGram'] = $dataRow['SmallPieceperGram'];
        $ArrayBookingList['Uniformity'] = $dataRow['Uniformity'];
        $ArrayBookingList['TotalPiecesperGram'] = $dataRow['TotalPiecesperGram'];
        $ArrayBookingList['Deterioration'] = $dataRow['Deterioration'];
        $ArrayBookingList['Discolouration'] = $dataRow['Discolouration'];
        $ArrayBookingList['BlackSpots'] = $dataRow['BlackSpots'];
        $ArrayBookingList['SoftShell'] = $dataRow['SoftShell'];
        $ArrayBookingList['HONQuantity'] = $dataRow['HONQuantity'];
        $ArrayBookingList['HLQuantity'] = $dataRow['HLQuantity'];
        $ArrayBookingList['Yield'] = $dataRow['Yield'];
        $ArrayBookingList['HangingMeat'] = $dataRow['HangingMeat'];
        $ArrayBookingList['VerifiedBy'] = $dataRow['VerifiedBy'];
        $ArrayBookingList['InspectedBy'] = $dataRow['InspectedBy'];
        $ArrayBookingList['Remarks'] = $dataRow['Remarks'];
        $ArrayBookingList['ArrivalID'] = $dataRow['ArrivalID'];

        $Data[] = $ArrayBookingList;
    }

    $result = [];
    $result['Error'] = false;
    $result['Msg'] = "Fetch Grading Inspection Details Success";
    $result['Data'] = $Data;
    return $result;
}

}
