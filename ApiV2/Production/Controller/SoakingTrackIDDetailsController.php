<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class SoakingTrackIDDetailsController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = 'Success';
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Production');
    }

    public function getSoakingDetails($RequestBody)
    {
        $TrackID = $RequestBody['TrackID'];
        $VABreakUpID = $RequestBody['VABreakUpID'];
        $PlantID = $RequestBody['PlantID'];
        $MaterialType = $RequestBody['MaterialType'];

        $SoakingData = $this->getSoakingData($RequestBody);
        $VaDetails = $this->getVaBreakUp($RequestBody);
        $SourceCount = $this->getSourcCountData($VABreakUpID, $TrackID, $MaterialType);
        $BalanceQty = $this->balanceSoakingInQty($VABreakUpID, $TrackID, $PlantID, '');

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['VaDetails'] = $VaDetails;
        $Data['SoakingDetails'] = $SoakingData;
        $Data['SourceCount'] = $SourceCount;
        $Data['BalanceData'] = $BalanceQty;

        return $Data;
    }

    public function deleteSoaking($RequestBody)
    {
        $ArrivalID = $RequestBody['ArrivalID'];
        $BreakUpID = $RequestBody['BreakUpID'];
        $existingSoaking = $this->auditLogger->fetchRowById('ProdSoakingDetails', 'SckID', $BreakUpID);

        $sqlSelect = "SELECT TrackID FROM `ProdSoakingDetails` WHERE SckID='$BreakUpID' AND CreatedFrom=1";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        $TrackID = $dataRow['TrackID'];

        $sqlQuery = "DELETE FROM `ProdSoakingDetails` WHERE SckID='$BreakUpID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        if ($stmt) {
            $this->auditLogger->logDelete('ProdSoakingDetails', $BreakUpID, $existingSoaking, $RequestBody, [
                'ReferenceNo' => $TrackID,
                'Remarks' => 'Production soaking tracking row deleted',
            ]);
        }

        $this->utilController->UpdateTrackWiseRawMeatWeight($TrackID, '', $RequestBody);
        // $this->utilController->UpdateTrackWiseCookedMeatWeight($TrackID, '');
        $this->utilController->UpdateTrackWiseBlockMeatWeight($TrackID,$RequestBody);

        $this->Msg = "Item Deleted";

        return $this->getSoakingDetails($RequestBody);
    }
     public function getAutoTub($RequestBody)
    {
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $TrackID = $RequestBody['TrackID'];
        $PlantID = ($RequestBody['PlantID'] != '' ? $RequestBody['PlantID'] : 0); // sriram
        $sqlSelect = "SELECT MAX(CAST(TubNumber AS UNSIGNED)) AS Total FROM `ProdSoakingDetails` AS PSD LEFT JOIN RmReceivingDetails AS RRD ON PSD.TrackID=RRD.ReceivingID WHERE RRD.ArrivalDate='$ArrivalDate' AND PlantID='$PlantID' AND TrackID='$TrackID'  AND PSD.CreatedFrom=1";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        $Data['Error'] = '';
        $Data['Msg'] = '';
        $Data['Data'] = $dataRow;
        return $Data;
    }

    public function postSoakingDetails($RequestBody)
    {

        $PostedUserID = 1;
        $SoakingID = $RequestBody['SoakingID']; // Temporary assumption
        $ArrivalID = $RequestBody['ArrivalID'];
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $Source = $RequestBody['SourceStation'];
        // $PlantID = $RequestBody['PlantID'];
        $PlantID = ($RequestBody['PlantID'] != '' ? $RequestBody['PlantID'] : 0); // sriram
        $SoakingDate = $RequestBody['SoakingDate'];
        $SoakingTreatment = $RequestBody['SoakingTreatment'];
        $Shift = $RequestBody['Shift'];
        $SoakInTime = $RequestBody['StartTime'];
        $TimePeriod = $RequestBody['TimePeriod'];
        $SaltQty = $RequestBody['SaltKg'];
        $Remarks = $RequestBody['Remarks'];
        $ChemicalQty = $RequestBody['ChemicalQuantity'];
        $Variety = $RequestBody['VarietyID'];
        $TubNumber = $RequestBody['TubNumber']; //(18-09-2024)@Komali Remove teja"A-" . $RequestBody['TubNumber']; //A-123
        $TubCode = 'SKM-' . $RequestBody['TubNumber']; //(18-09-2024)@Komali Remove teja"A-" . $RequestBody['TubNumber']; //A-123
        $LineNumber = $RequestBody['LineNumber'];
        $SoakInCount = $RequestBody['InCount'];
        $SoakInQty = $RequestBody['InQuantity'];
        $SoakOutCount = $RequestBody['OutCount'];
        $SoakOutQty = $RequestBody['OutQuantity'];
        $VABreakupID = $RequestBody['VABreakupID'];
        $ReceivalBreakupID = $RequestBody['ReceivalBreakupID'];
        $TrackID = $RequestBody['TrackID'];
        $ChemicalID = $RequestBody['ChemicalID'];
        $VAGrade = $RequestBody['VAGrade'];
        $VLID = $RequestBody['VLID'];
        $IceQtyPercentage = $RequestBody['IceQtyPercentage'];
        $WaterQtyPercentage = $RequestBody['WaterQtyPercentage'];
        $SoakInTemperature = $RequestBody['SoakInTemperature'];
        $SoakOutTemperature = $RequestBody['SoakOutTemperature'];
        $AddExtraQty = $RequestBody['AddExtraQty'];
        $ExcessQuantity = $RequestBody['ExcessQuantity'];
        $DoContinueTubNo = $RequestBody['DoContinueTubNo'];
         $InCounts = $RequestBody['InCounts'];
        // $Shift = $RequestBody['Shift'];

        if($DoContinueTubNo == 1){
            $soakQuery = "";
            if($SoakingID != ''){
                $soakQuery = " AND SckID != '$SoakingID'";
            }
            $sqlSelect = "SELECT Count(*) AS Total FROM `ProdSoakingDetails` AS PSD LEFT JOIN RmReceivingDetails AS RRD ON PSD.TrackID=RRD.ReceivingID WHERE TubNumber='$TubNumber' AND TrackID='$TrackID' $soakQuery  AND PSD.CreatedFrom=1";
            $stmtSelect = $this->conn->prepare($sqlSelect);
            $stmtSelect->execute();
            $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
            $TotalCount = $dataRow['Total'];
            if($TotalCount > 0){
                $this->Error = 'Tub Number Already Exists';
                $this->Msg = 'Tub Number Already Exists';
                return $Data = array('Error' => $this->Error, "Msg" => $this->Msg);
            }
        }

        $AryCurrentDate = getCurrentDateTime();

        $CreatedDateTime = $AryCurrentDate['TimeStamp'];
        $existingSoaking = null;
        $SqlWeight = "SELECT sum(HLWeight) as GradingHLWeight,
		(SELECT sum(SoakInQty)  FROM `ProdSoakingDetails` WHERE LotID='$ArrivalID'  AND CreatedFrom=1)  as SoakingINWeight
		FROM `PpcHLGradingBreakup` WHERE DataID='$VABreakupID' and LotID='$ArrivalID'  AND CreatedFrom=1";
        
        $stmtWeight = $this->conn->prepare($SqlWeight);
        $stmtWeight->execute();
        $dataRowWeight = $stmtWeight->fetch(PDO::FETCH_ASSOC);

        // $GradingHLWeight = $dataRowWeight['GradingHLWeight'] = 50;
        $GradingHLWeight = $dataRowWeight['GradingHLWeight'];
        $SoakingINWeight = $dataRowWeight['SoakingINWeight'] + $SoakInQty;

        // $SoakingINWeight = 20;

        $BalanceQty = $this->balanceSoakingInQty($VABreakupID, $TrackID, $PlantID, $SoakingID, $VLID);
        
        // echo $SoakInQty;
        //echo '<pre>'; print_r($BalanceQty);exit;

        if($AddExtraQty == 1){
            $BalanceQty = (float)$BalanceQty['BalanceQty'] + (float)$ExcessQuantity;
        }else{
            $BalanceQty = (float)$BalanceQty['BalanceQty'];
        }
        $stmt = false;
        // if ($SoakingINWeight <= $GradingHLWeight) {
        if ($BalanceQty >= $SoakInQty) {
            if ($SoakingID == "") { // Temporary assumption
                if($AddExtraQty == 1){
                    $sqlQuery = "INSERT INTO `ProdSoakingDetails`(`SoakingDate`,`SoakingTreatment`, `LotID`, `Source`, `PlantID`, `Variety`, `TubNumber`,`TubCode`, `SaltQty`, 
                    `ChemicalQty`, `SoakInTime`, `TimePeriod`, `SoakInCount`, `SoakInQty`, `SoakOutCount`, `SoakOutQty`,  `PostedUserID`, 
                    `Remarks`, `Shift`, `PostedTimeDate`,`TrackID`,`ProductionPlantID`,`TrackBreakUpID`,`VABreakupID`,`ChemicalID`,`VAGrade`,
                    `LineNumber`,`ValueAdditionID`,`IceQtyPercentage`,`WaterQtyPercentage`,`SoakInTemperature`,`SoakOutTemperature`,`ExcessQuantity`,`InCounts`) 
                    VALUES ('$SoakingDate','$SoakingTreatment','$ArrivalID','$Source','$PlantID','$Variety','$TubNumber','$TubCode','$SaltQty','$ChemicalQty','$SoakInTime',
                    '$TimePeriod','$SoakInCount','$SoakInQty','$SoakOutCount','$SoakOutQty','$PostedUserID','$Remarks','$Shift','$CreatedDateTime',
                    '$TrackID',
                    '$PlantID',
                    '$ReceivalBreakupID',
                    '$VABreakupID',
                    '$ChemicalID',
                    '$VAGrade',
                    '$LineNumber',
                    '$VLID','$IceQtyPercentage','$WaterQtyPercentage','$SoakInTemperature','$SoakOutTemperature','$ExcessQuantity','$InCounts')";
                }else{
                    $sqlQuery = "INSERT INTO `ProdSoakingDetails`(`SoakingDate`,`SoakingTreatment`, `LotID`, `Source`, `PlantID`, `Variety`, `TubNumber`,`TubCode`, `SaltQty`, 
                    `ChemicalQty`, `SoakInTime`, `TimePeriod`, `SoakInCount`, `SoakInQty`, `SoakOutCount`, `SoakOutQty`,  `PostedUserID`, 
                    `Remarks`, `Shift`, `PostedTimeDate`,`TrackID`,`ProductionPlantID`,`TrackBreakUpID`,`VABreakupID`,`ChemicalID`,`VAGrade`,
                    `LineNumber`,`ValueAdditionID`,`IceQtyPercentage`,`WaterQtyPercentage`,`SoakInTemperature`,`SoakOutTemperature`,`InCounts`) 
                    VALUES ('$SoakingDate','$SoakingTreatment','$ArrivalID','$Source','$PlantID','$Variety','$TubNumber','$TubCode','$SaltQty','$ChemicalQty','$SoakInTime',
                    '$TimePeriod','$SoakInCount','$SoakInQty','$SoakOutCount','$SoakOutQty','$PostedUserID','$Remarks','$Shift','$CreatedDateTime',
                    '$TrackID',
                    '$PlantID',
                    '$ReceivalBreakupID',
                    '$VABreakupID',
                    '$ChemicalID',
                    '$VAGrade',
                    '$LineNumber',
                    '$VLID','$IceQtyPercentage','$WaterQtyPercentage','$SoakInTemperature','$SoakOutTemperature','$InCounts' )";
                }
            } else {
                $existingSoaking = $this->auditLogger->fetchRowById('ProdSoakingDetails', 'SckID', $SoakingID);
                if($AddExtraQty == 1){
                    $sqlQuery = "UPDATE `ProdSoakingDetails` SET `SoakingDate` = '$SoakingDate',`LotID` = '$ArrivalID',`Source` = '$Source',
                    `PlantID` = '$PlantID', `Variety` = '$Variety',`TubNumber` = '$TubNumber',`TubCode` = '$TubCode',`SaltQty` = '$SaltQty',`ChemicalQty` = '$ChemicalQty',
                    `SoakInTime` = '$SoakInTime',`TimePeriod` = '$TimePeriod',`SoakInCount` = '$SoakInCount',`SoakInQty` = '$SoakInQty',`InCounts` = '$InCounts',
                    `SoakOutCount` = '$SoakOutCount',`SoakOutQty` = '$SoakOutQty',`PostedUserID` = '$PostedUserID',`Remarks` = '$Remarks',
                    `Shift` = '$Shift',`PostedTimeDate` = '$CreatedDateTime',
                    `TrackID`='$TrackID',
                    `ChemicalID`='$ChemicalID',
                    `ProductionPlantID`='$PlantID',
                    `TrackBreakUpID`='$ReceivalBreakupID',
                    `VABreakupID`='$VABreakupID',
                    `VAGrade`='$VAGrade',
                    `SoakingTreatment`='$SoakingTreatment',
                    `LineNumber`='$LineNumber',
                    `ValueAdditionID`='$VLID',
                     `IceQtyPercentage`='$IceQtyPercentage',
                    `WaterQtyPercentage`='$WaterQtyPercentage',
                    `SoakInTemperature`='$SoakInTemperature',
                    `SoakOutTemperature`='$SoakOutTemperature',
                    `ExcessQuantity`=$ExcessQuantity
                    WHERE `SckID` = '$SoakingID'";
                }else{
                    $sqlQuery = "UPDATE `ProdSoakingDetails` SET `SoakingDate` = '$SoakingDate',`LotID` = '$ArrivalID',`Source` = '$Source',
                    `PlantID` = '$PlantID', `Variety` = '$Variety',`TubNumber` = '$TubNumber',`TubCode` = '$TubCode',`SaltQty` = '$SaltQty',`ChemicalQty` = '$ChemicalQty',
                    `SoakInTime` = '$SoakInTime',`TimePeriod` = '$TimePeriod',`SoakInCount` = '$SoakInCount',`SoakInQty` = '$SoakInQty',`InCounts` = '$InCounts',
                    `SoakOutCount` = '$SoakOutCount',`SoakOutQty` = '$SoakOutQty',`PostedUserID` = '$PostedUserID',`Remarks` = '$Remarks',
                    `Shift` = '$Shift',`PostedTimeDate` = '$CreatedDateTime',
                    `TrackID`='$TrackID',
                    `ChemicalID`='$ChemicalID',
                    `ProductionPlantID`='$PlantID',
                    `TrackBreakUpID`='$ReceivalBreakupID',
                    `VABreakupID`='$VABreakupID',
                    `VAGrade`='$VAGrade',
                    `SoakingTreatment`='$SoakingTreatment',
                    `LineNumber`='$LineNumber',
                    `ValueAdditionID`='$VLID',
                    `IceQtyPercentage`='$IceQtyPercentage',
                    `WaterQtyPercentage`='$WaterQtyPercentage',
                    `SoakInTemperature`='$SoakInTemperature',
                    `SoakOutTemperature`='$SoakOutTemperature'
                    WHERE `SckID` = '$SoakingID'";
                }
            }
            // echo $sqlQuery; exit;
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();

            if ($stmt) {
                if ($SoakingID == "") {
                    $lastInsertId = $this->conn->lastInsertId();
                    $createdSoaking = $this->auditLogger->fetchRowById('ProdSoakingDetails', 'SckID', $lastInsertId);
                    $this->auditLogger->logInsert('ProdSoakingDetails', $lastInsertId, $createdSoaking, $RequestBody, [
                        'ReferenceNo' => $TrackID,
                        'Remarks' => 'Production soaking tracking row created',
                    ]);
                } else {
                    $updatedSoaking = $this->auditLogger->fetchRowById('ProdSoakingDetails', 'SckID', $SoakingID);
                    $this->auditLogger->logUpdate('ProdSoakingDetails', $SoakingID, $existingSoaking, $updatedSoaking, $RequestBody, [
                        'ReferenceNo' => $TrackID,
                        'Remarks' => 'Production soaking tracking row updated',
                    ]);
                }
                $this->utilController->UpdateTrackWiseRawMeatWeight($TrackID, '', $RequestBody);
                // $this->utilController->UpdateTrackWiseCookedMeatWeight($TrackID, '');
                $this->utilController->UpdateTrackWiseBlockMeatWeight($TrackID,$RequestBody);


                return $this->getSoakingDetails($RequestBody);
            } else {
                return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
            }
        } else {
            $this->Error = 'Weight Exceeds';
            $this->Msg = 'Weight Exceeds';
            return $this->getSoakingDetails($RequestBody);
        }
    }
    function getSoakingData($RequestBody)
    {

        $TrackID = $RequestBody['TrackID'];
        $PlantID = $RequestBody['PlantID'];
        $VABreakupID = $RequestBody['VABreakUpID'];
        $AddExtraQty = $RequestBody['AddExtraQty'];

        $ExcessFields = '';
        if($AddExtraQty == 1){
            $ExcessFields = ',ExcessQuantity';
        }
        $Query = "";
        if($PlantID != ''){
            $Query = " and PDH.PlantID ='$PlantID'";
        }

        // $VAQuery = '';
        // if($VABreakupID != ''){
        //     $VAQuery = ' AND VABreakupID='.$VABreakupID;
        // }
        $sqlFields = "SckID,SoakingDate,PDH.SoakingTreatment,ChemicalName,LotNumber as ArrivalNumber,StationName,UnitName,Shift,PDH.Source,PDH.PlantID,PDH.LotID, 
        PDH.Remarks,SoakInTime,TimePeriod,SaltQty,ChemicalQty,Variety,TubNumber,SoakInCount,SoakInQty,SoakOutCount,SoakOutQty,VABreakupID,InCounts,SoakOutTemperature,
        PDH.Variety as VarietyID,TrackCode, PDH.ChemicalID, ShiftName,PHVL.ShortName,PHVL.Species,PDH.VAGrade,LineNumber,TubCode,IceQtyPercentage,WaterQtyPercentage,PHVL.TypeProduct,
        SoakInTemperature,SoakOutTemperature$ExcessFields";

        $sqlQuery = "SELECT  $sqlFields FROM `ProdSoakingDetails`  PDH 
        left join PpcVAStations PVS on PVS.StationID=PDH.Source 
		left join RmLotDetatils RAD on RAD.RmLotID=PDH.LotID 
		left join AdminShiftDetails ASD on ASD.ShiftID=PDH.Shift 
		left join RmReceivingDetails RRD on RRD.ReceivingID=PDH.TrackID 
        left join PpcHLVAVarietyList PHVL on PHVL.VarietyID=PDH.Variety 
		left join RmProcessingStations RPS on RPS.ProcessingStationID=PDH.PlantID
		left join GsChemicalMaster GCM on GCM.ChemID=PDH.ChemicalID
        where TrackID='$TrackID'$Query  AND PDH.CreatedFrom=1 ORDER BY `SckID` asc";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $TotalSoakOutQty = 0;
        $TotalSoakInQty = 0;
        // $TotalPickUp = 0;
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $DeleteAccess = 1;
            $EditAccess = 1;


            $TubNumber = $dataRow['TubNumber'];
            $sqlFreezingCount = "SELECT count(*) as FreezingCount FROM `ProdFreezingData` WHERE TrackID='$TrackID' and TubNo='$TubNumber'";
            $stmtFreezingCount = $this->conn->prepare($sqlFreezingCount);
            $stmtFreezingCount->execute();
            $dataRowFreezingCount = $stmtFreezingCount->fetch(PDO::FETCH_ASSOC);
            if ($dataRowFreezingCount['FreezingCount'] > 0) {
                $DeleteAccess = 0;
                $EditAccess = 0;
            }

            $SoakOutQty = $dataRow['SoakOutQty'];
            $SoakInQty = $dataRow['SoakInQty'];
            // $PickUp = ($SoakOutQty / $SoakInQty) / 100;
            $PickUp = ($SoakOutQty - $SoakInQty) / $SoakInQty * 100;

            $SoakingData['SNo'] = ++$i;
            $SoakingData['BreakUpID'] = $dataRow['SckID'];
            $SoakingData['TransactionDate'] = $this->utilController->getFormatedDate($dataRow['SoakingDate']);
            $SoakingData['LotNo'] = $dataRow['ArrivalNumber'];
            $SoakingData['Plant'] = $dataRow['StationName'];
            $SoakingData['Center'] = $dataRow['UnitName'];
            $SoakingData['SoakingTreatment'] = $dataRow['SoakingTreatment'];
            $SoakingData['SourceStationID'] = $dataRow['Source'];
            $SoakingData['Shift'] = $dataRow['ShiftName'];
            $SoakingData['ShiftID'] = $dataRow['Shift'];

            $SoakingData['Remarks'] = $dataRow['Remarks'];
            $SoakingData['PlantID'] = $dataRow['PlantID'];
            $SoakingData['ArrivalID'] = $dataRow['LotID'];
            $SoakingData['SoakInTime'] = $dataRow['SoakInTime'];
            $SoakingData['TimePeriod'] = $dataRow['TimePeriod'];
            $SoakingData['SaltQty'] = $dataRow['SaltQty'];
            $SoakingData['ChemicalQty'] = $dataRow['ChemicalQty'];
            $SoakingData['Variety'] = $dataRow['ShortName'];
            $SoakingData['Species'] = $dataRow['Species'];
            $SoakingData['TubNumber'] = $dataRow['TubNumber'];
            $SoakingData['TubCode'] = $dataRow['TubCode'];
            $SoakingData['PickUp'] = round($PickUp, 2);
            // $SoakingData['PickUp'] = SoakOutCount/SoakInCount/100;
            $SoakingData['SoakInCount'] = $dataRow['SoakInCount'];
            $SoakingData['SoakInQty'] = $dataRow['SoakInQty'];
            $SoakingData['SoakOutCount'] = $dataRow['SoakOutCount'];
            $SoakingData['OutQuantity'] = $dataRow['SoakOutQty'];
            $SoakingData['SoakOutQty'] = $dataRow['SoakOutQty'];
            $SoakingData['VarietyID'] = $dataRow['VarietyID'];
            $SoakingData['ChemicalID'] = $dataRow['ChemicalID'];
            $SoakingData['VAGrade'] = $dataRow['VAGrade'];
            $SoakingData['TypeProduct'] = $dataRow['TypeProduct'];
            $SoakingData['TrackCode'] = $dataRow['TrackCode'];
            $SoakingData['ChemicalName'] = $dataRow['ChemicalName'];
            $SoakingData['LineNumber'] = $dataRow['LineNumber'];
            $SoakingData['IceQtyPercentage'] = $dataRow['IceQtyPercentage'];
            $SoakingData['WaterQtyPercentage'] = $dataRow['WaterQtyPercentage'];
            $SoakingData['SoakInTemperature'] = $dataRow['SoakInTemperature'];
            $SoakingData['InCounts'] = $dataRow['InCounts'];
             $SoakingData['SoakOutTemperature'] = $dataRow['SoakOutTemperature'];


             
            if($AddExtraQty == 1){
                $SoakingData['ExcessQuantity'] = $dataRow['ExcessQuantity'];
            }
            $SoakingData['DeleteAccess'] = $DeleteAccess;
            $SoakingData['EditAccess'] = $EditAccess;

            $ArrayData[] = $SoakingData;
            if($VABreakupID != '' && $dataRow['VABreakupID'] == $VABreakupID){
                $SoakingData['VABreakupID'] = $dataRow['VABreakupID'];
                $SelectedSoakingData[] = $SoakingData;
            }
            $TotalSoakInQty += $SoakingData['SoakInQty'];
            $TotalSoakOutQty += $SoakingData['SoakOutQty'];
        }
        $ArrayTotals['TotalSoakInQty'] = $TotalSoakInQty;
        $ArrayTotals['TotalSoakOutQty'] = $TotalSoakOutQty;

        $Data['HLCounts'] = $this->getLotBreakUpDetails($TrackID);
        $Data['SoakingData'] = $ArrayData;
        $Data['SelectedSoakingData'] = $SelectedSoakingData;
        $Data['Totals'] = $ArrayTotals;

        return $Data;
    }

    function getLotBreakUpDetails($TrackID)
    {

        $sqlFields = "HLCount,HLWeight,DataID,NoCrates,MixedID";
        $SqlHLData = "Select $sqlFields From PpcHLGradingBreakup WHERE LotID='$TrackID'  AND CreatedFrom=1 ";
        $stmt = $this->conn->prepare($SqlHLData);
        $stmt->execute();

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $LotBreakUpData['HLBreakupID'] = $dataRow['DataID'];
            $LotBreakUpData['Title'] = $dataRow['HLCount'] . ' - ' . $dataRow['HLWeight'];
            $LotBreakUpData['HLCount'] = $dataRow['HLCount'];
            $LotBreakUpData['HLWeight'] = $dataRow['HLWeight'];

            $ArrayData[] = $LotBreakUpData;
        }

        return $ArrayData;
    }


    function getVaBreakUp($RequestBody)
    {

        $TrackID = $RequestBody['TrackID'];
        $PlantID = $RequestBody['PlantID'] != '' ? $RequestBody['PlantID'] : 0;
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $AddvalueAddition = (isset($RequestBody['AddvalueAddition']) && $RequestBody['AddvalueAddition'] !== '') ? $RequestBody['AddvalueAddition'] : 0;
        $OutStage = '';

        $comparisonDate = "2025-03-09"; // Date of seperate value addition tubs
        $ArrivalDateObj = new DateTime($ArrivalDate);
        $ComparisonDateObj = new DateTime($comparisonDate);

        //uma 19-08-2024

        $IsStoreMaterial = $this->checkIsStoreMaterial($TrackID, $OutStage)['IsStoreMaterial'];
        $OutStage = $this->checkIsStoreMaterial($TrackID, $OutStage)['OutStage'];

        $TotalWeight = 0;
        //uma 19-08-2024 
        if($IsStoreMaterial == 0 && $OutStage == 1){
            $sqlFields = " distinct SentCount as HLCount, TypeProduct,ShortName,NoofCrates as NoCrates,PVA.TrackBreakUpID,
            sum(WtAftrVA) as HLWeight, PVA.VarietyID
            ,GROUP_CONCAT(DISTINCT RAD.ArrivalNumber) ArrivalNumber,GROUP_CONCAT(DISTINCT PHGB.PackingGrade) PackingGrade,CASE WHEN PHGD.`Grade` = '' THEN PHGD.`Grade` ELSE HLVA.`Grade` END AS Grade";
            $sqlQuery = "SELECT $sqlFields  FROM PpcValueAddition AS PVA
            LEFT JOIN PpcHLVAVarietyList AS HLVA ON HLVA.VarietyID=PVA.VarietyID 

            LEFT JOIN PpcHLGradingBreakup AS PHGB ON PVA.GradingBreakupID = PHGB.DataID  AND PHGB.CreatedFrom=1
		    LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID = PHGB.LotID  AND PHGD.CreatedFrom=1
		    LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ReceivalBreakupID = PHGD.TrackBreakUpID
		    LEFT JOIN RmArrivalDetatils AS RAD ON RRB.ArrivalID=RAD.RmArrivalID

            WHERE PVA.TrackID='$TrackID' and SoakingPlantID='$PlantID'  AND PVA.CreatedFrom=1 order by VLID asc";

            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();

            $Data = array();
            $i = 0;

            while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $GradingBreakupID = $dataRow['DataID'];
                $Treatment = $dataRow['Treatment'];
                $IsBlock = $dataRow['IsBlock'];
                $VABreakUpID = $GradingBreakupID;

                // sriram
                $sqlSoakQty = "SELECT sum(SoakInQty) AS TotalSoakQty FROM `ProdSoakingDetails` WHERE  TrackID='$TrackID' AND PlantID='$PlantID'  AND CreatedFrom=1 ";
                $stmtSoakQty = $this->conn->prepare($sqlSoakQty);
                $stmtSoakQty->execute();
                $dataBalRow = $stmtSoakQty->fetch(PDO::FETCH_ASSOC);
                $TotalSoakQty = $dataBalRow['TotalSoakQty'];

                $sqlSoakFields = "TypeProduct,ShortName,sum(WtAftrVA) AS TotalQty,GROUP_CONCAT(WtAftrVA) AS VAOutQty,PVA.VarietyID";
                $sqlSoaking = "SELECT $sqlSoakFields FROM PpcValueAddition AS PVA
                LEFT JOIN PpcHLVAVarietyList AS HLVA ON HLVA.VarietyID=PVA.VarietyID 
                WHERE SoakingPlantID='$PlantID' AND TrackID='$TrackID'  AND PVA.CreatedFrom=1 ";
                $MaterialType = "REJ";
                $stmtSoaking = $this->conn->prepare($sqlSoaking);
                $stmtSoaking->execute();
                $VAQty = $stmtSoaking->fetch(PDO::FETCH_ASSOC);

                $GradingData['SNo'] = ++$i;
                $GradingData['ReceivalBreakupID'] = $dataRow['ReceivalBreakupID'];
                $GradingData['VABreakUpID'] = $GradingBreakupID;
                $GradingData['ArrivalID'] = $dataRow['RmArrivalID'];
                $GradingData['NoofCrates'] = $dataRow['NoCrates'];
                $GradingData['CrateQuantity'] = '27';
                $GradingData['VACount'] = $dataRow['HLCount'];
                $GradingData['VAQty'] = $dataRow['HLWeight']; // HL Weight as Grading Qty               
                $GradingData['VAOutQty'] = $VAQty['VAOutQty'];
                $GradingData['VarietyID'] = $VAQty['VarietyID'];
                $GradingData['PackingGrade'] =  $dataRow['PackingGrade'];
                $GradingData['VAQty'] = (float)$VAQty['TotalQty'] ;
                $GradingData['Variety'] = $VAQty['TypeProduct'] . '-' . $VAQty['ShortName'];
                $GradingData['MaterialType'] = $MaterialType;
                $GradingData['BalanceQty'] = $GradingData['VAQty'] - $TotalSoakQty; // sriram
                $GradingData['IsStoreMaterial'] = $IsStoreMaterial;  //uma 2025-04-01

                $GradingData['ArrivalNumber'] = $dataRow['ArrivalNumber'];

                $ArrayData[] = $GradingData;
                $TotalWeight += $dataRow['HLWeight'];
            }
        }
        else if ($IsStoreMaterial == 0) {
           $VAQuery = '';
           $VAFields = '';
           if($AddvalueAddition == 1){
                $VAQuery = " OR (PHGD2.ValueAdditionPlantID = '$PlantID' AND PHGD2.TrackID = '$TrackID' AND PHGB2.valueAddition = 0 AND PHGB.valueAddition = 0)";
                $VAFields = ",PHGB.valueAddition";
           }else{
                $VAQuery = '';
                $VAFields = '';
           }
                    $sqlFields = "DISTINCT ReceivalBreakupID,
                        TypeProduct,
                        ShortName,
                        PHGB.NoCrates,
                        PHGB.LotID AS RmArrivalID,
                        PHGD.TrackBreakUpID,
                        PHGB.HLCount,
                        PHGB.HLWeight,
                        PHGB.DataID,
                        PHGB.VarietyID,
                        PHGB.PackingGrade,
                        PHGB.Treatment,
                        PHGB.IsBlock,
                        RAD.ArrivalNumber$VAFields";
            $sqlQuery = "SELECT $sqlFields 
                FROM PpcHLGradingBreakup AS PHGB
                LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID = PHGB.LotID  AND PHGD.CreatedFrom=1
                LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ReceivalBreakupID = PHGD.TrackBreakUpID
                LEFT JOIN RmReceivingDetails AS RRD ON RRD.ReceivingID = RRB.ReceivingID
                LEFT JOIN PpcHLVAVarietyList AS HLVA ON HLVA.VarietyID = PHGB.VarietyID
                LEFT JOIN PpcValueAddition AS PVA ON PVA.GradingBreakupID = PHGB.DataID AND PVA.TrackID = '$TrackID'  AND PVA.CreatedFrom=1
                LEFT JOIN PpcHLGradingDetails AS PHGD2 ON PHGD2.LotID = PHGB.LotID  AND PHGD2.CreatedFrom=1
                LEFT JOIN PpcHLGradingBreakup AS PHGB2 ON PHGB2.LotID = PHGD2.LotID  AND PHGB2.CreatedFrom=1
                LEFT JOIN RmArrivalDetatils AS RAD ON RRB.ArrivalID=RAD.RmArrivalID
                WHERE 
                    RRD.ReceivingID = '$TrackID'  AND PHGB.CreatedFrom=1
                    AND (
                        (PVA.SoakingPlantID = '$PlantID' AND PHGB.Treatment != 'UNT' AND PHGB2.Treatment != 'UNT') 
                        OR (PHGD2.ValueAdditionPlantID = '$PlantID' AND PHGD2.TrackID = '$TrackID' AND PHGB2.Treatment != 'UNT' AND PHGB2.IsBlock = 1 AND PHGB.Treatment != 'UNT' AND PHGB.IsBlock = 1) $VAQuery
                    )
                ORDER BY PHGD.HLID ASC";
                
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();

            $Data = array();
            $i = 0;

            while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $GradingBreakupID = $dataRow['DataID'];
                $Treatment = $dataRow['Treatment'];
                $IsBlock = $dataRow['IsBlock'];
                $VABreakUpID = $GradingBreakupID;

                $ValueAdd = 1;
                if($AddvalueAddition == 1){
                    $ValueAdd = $dataRow['valueAddition'] ;
                }

                if (($Treatment != 'UNT' && $IsBlock == 1) || ($Treatment != 'UNT' && $ValueAdd == 0)) {
                    $sqlSoakFields = "TypeProduct,ShortName,HLWeight AS TotalQty,PHGB.VarietyID";
                    $sqlSoaking = "SELECT $sqlSoakFields FROM `PpcHLGradingBreakup` AS PHGB 
                    LEFT JOIN PpcHLVAVarietyList AS HLVA ON HLVA.VarietyID=PHGB.VarietyID 
                    WHERE DataID='$GradingBreakupID'  AND PHGB.CreatedFrom=1";
                    $MaterialType = "GM";
                } else {
                    if((($ArrivalDateObj > $ComparisonDateObj))){
                        $sqlSoakFields = "TypeProduct,ShortName,WtAftrVA AS TotalQty,WtAftrVA AS VAOutQty,PVA.VarietyID,VLID";
                        $sqlSoaking = "SELECT $sqlSoakFields FROM PpcValueAddition AS PVA
                        LEFT JOIN PpcHLVAVarietyList AS HLVA ON HLVA.VarietyID=PVA.VarietyID 
                        WHERE SoakingPlantID='$PlantID' AND TrackID='$TrackID' AND GradingBreakupID='$GradingBreakupID'  AND PVA.CreatedFrom=1";
                        $MaterialType = "VM";
                    }else{
                        $sqlSoakFields = "TypeProduct,ShortName,sum(WtAftrVA) AS TotalQty, GROUP_CONCAT(WtAftrVA) AS VAOutQty,PVA.VarietyID";
                        $sqlSoaking = "SELECT $sqlSoakFields FROM PpcValueAddition AS PVA
                        LEFT JOIN PpcHLVAVarietyList AS HLVA ON HLVA.VarietyID=PVA.VarietyID 
                        WHERE SoakingPlantID='$PlantID' AND TrackID='$TrackID' AND GradingBreakupID='$GradingBreakupID'  AND PVA.CreatedFrom=1 GROUP by PVA.VarietyID";
                        $MaterialType = "VM";
                    }
                }
                $stmtSoaking = $this->conn->prepare($sqlSoaking);
                $stmtSoaking->execute();
                while ($VAQty = $stmtSoaking->fetch(PDO::FETCH_ASSOC)) {

                    $Query = '';
                    if($MaterialType == "VM" && (($ArrivalDateObj > $ComparisonDateObj))){
                        $Query = ' AND ValueAdditionID='.$VAQty['VLID'];
                    }

                    //sriram
                    $sqlSoakQty = "SELECT sum(SoakInQty) AS TotalSoakQty FROM `ProdSoakingDetails`WHERE  TrackID='$TrackID' AND VABreakupID='$VABreakUpID' AND PlantID='$PlantID' $Query AND CreatedFrom=1 ";

                    $stmtSoakQty = $this->conn->prepare($sqlSoakQty);
                    $stmtSoakQty->execute();
                    $dataBalRow = $stmtSoakQty->fetch(PDO::FETCH_ASSOC);
                    $TotalSoakQty = isset($dataBalRow['TotalSoakQty']) ? $dataBalRow['TotalSoakQty'] : 0;


                    // echo $TotalSoakQty;
                    // echo print_r($VAQty);

                    $GradingData['SNo'] = ++$i;
                    $GradingData['ReceivalBreakupID'] = $dataRow['ReceivalBreakupID'];
                    $GradingData['VABreakUpID'] = $GradingBreakupID;
                    $GradingData['VLID'] = $VAQty['VLID'];
                    $GradingData['ArrivalID'] = $dataRow['RmArrivalID'];
                    $GradingData['NoofCrates'] = $dataRow['NoCrates'];
                    $GradingData['CrateQuantity'] = '27';
                    $GradingData['VACount'] = $dataRow['HLCount'];
                    $GradingData['VAQty'] = $dataRow['HLWeight'];
                    $GradingData['VAOutQty'] = $VAQty['VAOutQty'];
                    $GradingData['VarietyID'] = $VAQty['VarietyID'];
                    $GradingData['PackingGrade'] = $dataRow['PackingGrade'];
                    $GradingData['VAQty'] = (float)$VAQty['TotalQty'] ;
                    $GradingData['Variety'] = $VAQty['TypeProduct'] . '-' . $VAQty['ShortName'];
                    $GradingData['MaterialType'] = $MaterialType;
                    $GradingData['BalanceQty'] =  $GradingData['VAQty'] - $TotalSoakQty; // sriram
                    $GradingData['ArrivalNumber'] = $dataRow['ArrivalNumber']; 
                    $GradingData['IsStoreMaterial'] = $IsStoreMaterial;  //uma 2025-04-01

                    
                    
                    $ArrayData[] = $GradingData;
                    $TotalWeight += $dataRow['HLWeight'];
                }
            }

        } else if ($OutStage == 1) {
            $sqlFields = " distinct SentCount as HLCount, TypeProduct,ShortName,NoofCrates as NoCrates,PVA.LotID as RmArrivalID,PVA.TrackBreakUpID,
            sum(WtAftrVA) as HLWeight, PVA.VarietyID
            ,group_concat(DISTINCT RAD.ArrivalNumber) ArrivalNumber,group_concat(DISTINCT PHGB.PackingGrade) PackingGrade,VLID";
           
           $sqlFields = " DISTINCT
                            SentCount AS HLCount,
                            TypeProduct,
                            ShortName,
                            NoofCrates AS NoCrates,
                            PVA.LotID AS RmArrivalID,
                            PVA.TrackBreakUpID,
                            WtAftrVA AS HLWeight,
                            PVA.VarietyID,
                            RAD.ArrivalNumber,
                            PHGB.PackingGrade,
                            VLID";
           
           $sqlQuery = "SELECT $sqlFields  FROM PpcValueAddition AS PVA
            LEFT JOIN PpcHLVAVarietyList AS HLVA ON HLVA.VarietyID=PVA.VarietyID 

            LEFT JOIN PpcHLGradingBreakup AS PHGB ON PVA.GradingBreakupID = PHGB.DataID  AND PHGB.CreatedFrom=1
            LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID = PHGB.LotID  AND PHGD.CreatedFrom=1
            LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ReceivalBreakupID = PVA.TrackBreakUpID
            LEFT JOIN RmArrivalDetatils AS RAD ON RRB.ArrivalID=RAD.RmArrivalID

            WHERE PVA.TrackID='$TrackID' and SoakingPlantID='$PlantID'  AND PVA.CreatedFrom=1 order by VLID asc";
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();

            $Data = array();
            $i = 0;

            while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $GradingBreakupID = $dataRow['DataID'];
                $Treatment = $dataRow['Treatment'];
                $IsBlock = $dataRow['IsBlock'];
                $VABreakUpID = $GradingBreakupID;

                $VLID = $dataRow['VLID'];
                $where = " AND VABreakupID='$VABreakUpID' ";
                if((empty($GradingBreakupID) || $GradingBreakupID == 'null') && !empty($VLID)){ 
                    $where = " AND ValueAdditionID='$VLID' ";
                }

                // sriram
                $sqlSoakQty = "SELECT sum(SoakInQty) AS TotalSoakQty FROM `ProdSoakingDetails`WHERE  TrackID='$TrackID' AND PlantID='$PlantID' AND CreatedFrom=1 $where ";
                $stmtSoakQty = $this->conn->prepare($sqlSoakQty);
                $stmtSoakQty->execute();
                $dataBalRow = $stmtSoakQty->fetch(PDO::FETCH_ASSOC);
                $TotalSoakQty = $dataBalRow['TotalSoakQty'];
               

                $sqlSoakFields = "TypeProduct,ShortName,sum(WtAftrVA) AS TotalQty,GROUP_CONCAT(WtAftrVA) AS VAOutQty,PVA.VarietyID";
                $sqlSoaking = "SELECT $sqlSoakFields FROM PpcValueAddition AS PVA
                LEFT JOIN PpcHLVAVarietyList AS HLVA ON HLVA.VarietyID=PVA.VarietyID 
                WHERE SoakingPlantID='$PlantID' AND TrackID='$TrackID'  AND PVA.CreatedFrom=1 ";
                if( !empty($VLID)){
                  $sqlSoaking .= " AND VLID='$VLID' " ;
                }
                $MaterialType = "VM";
                $stmtSoaking = $this->conn->prepare($sqlSoaking);
                $stmtSoaking->execute();
                $VAQty = $stmtSoaking->fetch(PDO::FETCH_ASSOC);
                // echo '<pre>';print_r($sqlSoaking);die;

                $GradingData['SNo'] = ++$i;
                $GradingData['ReceivalBreakupID'] = $dataRow['ReceivalBreakupID'];
                $GradingData['VABreakUpID'] = $GradingBreakupID;
                $GradingData['ArrivalID'] = $dataRow['RmArrivalID'];
                $GradingData['NoofCrates'] = $dataRow['NoCrates'];
                $GradingData['CrateQuantity'] = '27';
                $GradingData['VACount'] = $dataRow['HLCount'];
                $GradingData['VAQty'] = $dataRow['HLWeight']; // HL Weight as Grading Qty               
                $GradingData['VAOutQty'] = $VAQty['VAOutQty'];
                $GradingData['VarietyID'] = $VAQty['VarietyID'];
                $GradingData['PackingGrade'] =   $dataRow['PackingGrade'] ;
                $GradingData['VAQty'] = (float)$VAQty['TotalQty'] ;
                $GradingData['Variety'] = $VAQty['TypeProduct'] . '-' . $VAQty['ShortName'];
                $GradingData['MaterialType'] = $MaterialType;
                $GradingData['BalanceQty'] = $GradingData['VAQty'] - $TotalSoakQty; // sriram
                $GradingData['IsStoreMaterial'] = $IsStoreMaterial;  //uma 2025-04-01

                $GradingData['ArrivalNumber'] = $dataRow['ArrivalNumber'];

                $GradingData['VLID'] = $dataRow['VLID'];

                $ArrayData[] = $GradingData;
                $TotalWeight += $dataRow['HLWeight'];
            }
        } else { //uma 19-08-2024 store material
            $TotalVAQty = $this->checkIsStoreMaterial($TrackID, $OutStage)['TotalVAQty'];
            $sqlStockOutDetails = "SELECT group_concat( DISTINCT CSD.Grade) as Grade,group_concat( DISTINCT SPV.ShortName) as Variety,
           COALESCE(SUM(PSD.SoakInQty), 0) as HLWeight,GROUP_CONCAT(DISTINCT CASE WHEN RRB.CsStockID != 0 THEN RRB.CsStockID END) CsStockIDs FROM RmReceivingBreakup as RRB 
            left join CsStockData as CSD on CSD.StockID=RRB.CsStockID 
            left join ScrmPIVarietyList as SPV on CSD.VarietyID=SPV.VarietyID 
            LEFT JOIN ProdSoakingDetails as PSD on PSD.TrackID=RRB.ReceivingID AND PSD.CreatedFrom=1
            WHERE ReceivingID='$TrackID' group by CsStockOutID";
            // $sqlStockOutDetails = "SELECT group_concat(DISTINCT SPV.ShortName) as Variety,
            // sum(SoakInQty) as HLWeight FROM RmReceivingBreakup as RRB 
            // LEFT JOIN ProdSoakingDetails as PSD on PSD.ReceivingID=RRB.ReceivingID
            // left join CsStockData as CSD on CSD.StockID=RRB.CsStockID
            // left join ScrmPIVarietyList as SPV on CSD.VarietyID=SPV.VarietyID 
            // WHERE RRB.ReceivingID='$TrackID'";

            $stmtStockOutDetails = $this->conn->prepare($sqlStockOutDetails);
            $stmtStockOutDetails->execute();
            $rowStockOutDetails = $stmtStockOutDetails->fetch(PDO::FETCH_ASSOC);
            $lotNumber = '';
            if(!empty($rowStockOutDetails['CsStockIDs'])){
                $lotNumber = $this->getLotNumberByStockId($rowStockOutDetails['CsStockIDs']);
            }

            for ($i = 1; $i <= 300; $i++) {
                $GradingData['VACount'] = $i . '.00';
                $GradingData['VAQty'] = (float)$TotalVAQty ;
                $GradingData['VAOutQty'] = $rowStockOutDetails['HLWeight'] != '' ? $rowStockOutDetails['HLWeight'] : 0;
                $GradingData['Variety'] = $rowStockOutDetails['Variety'];
                $GradingData['BalanceQty'] = $GradingData['VAQty'] - $rowStockOutDetails['HLWeight'] ;
                $GradingData['IsStoreMaterial'] = $IsStoreMaterial;  //uma 2025-04-01
                $GradingData['ArrivalNumber'] = $lotNumber;
                $GradingData['PackingGrade'] = $rowStockOutDetails['Grade'];
                $ArrayData[] = $GradingData;
            }
            $TotalWeight += $rowStockOutDetails['HLWeight'];
            $Grade = $rowStockOutDetails['Grade'];
        } //uma 19-08-2024 store material
        // else{

        // }
        $ArrayTotals['TotalWeight'] = $TotalWeight;
        //	$Data['Variety'] = $this->getVarietyDetails();
        $Data['VaData'] = $ArrayData;
        $Data['Totals'] = $ArrayTotals;
        $Data['Grade'] = $Grade;

        return $Data;
    }

    public function getSourcCountData($VABreakUpID, $TrackID, $MaterialType)
    {
        if ($VABreakUpID) {
            $Query = " AND DataID=$VABreakUpID";
        }

        if ($MaterialType == "GM") {
            $sqlSCDFields = "ReceivalBreakupID,TypeProduct,ShortName,Species,NoCrates,PHGB.LotID as RmArrivalID,TrackBreakUpID,
            HLCount,HLWeight,DataID, PHGB.VarietyID , PHGB.PackingGrade, PHGB.Treatment";
            $sqlSCDQuery = "SELECT  $sqlSCDFields FROM PpcHLGradingBreakup AS PHGB 
            LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID  AND PHGD.CreatedFrom=1
            LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ReceivalBreakupID=PHGD.TrackBreakUpID 
            LEFT JOIN RmReceivingDetails as RRD on RRD.ReceivingID=RRB.ReceivingID
            LEFT JOIN PpcHLVAVarietyList AS HLVA ON HLVA.VarietyID=PHGB.VarietyID
            WHERE RRD.ReceivingID='$TrackID' $Query  AND PHGB.CreatedFrom=1 ORDER BY `HLID` ASC";
        } else if ($MaterialType == "VM") {
            $sqlSCDFields = "distinct PVA.TrackBreakUpID as ReceivalBreakupID,TypeProduct,ShortName,Species,NoCrates,
            PHGB.LotID as RmArrivalID,PHGD.TrackBreakUpID, HLCount,HLWeight,DataID, PVA.VarietyID , PHGB.PackingGrade, 
            PHGB.Treatment";
            $sqlSCDQuery = "SELECT  $sqlSCDFields FROM PpcValueAddition AS PVA 
            LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PVA.LotID  AND PHGD.CreatedFrom=1
            LEFT JOIN RmReceivingDetails as RRD on RRD.ReceivingID=PHGD.TrackID 
            LEFT JOIN PpcHLGradingBreakup AS PHGB ON PVA.GradingBreakupID = PHGB.DataID  AND PHGB.CreatedFrom=1
            LEFT JOIN PpcHLVAVarietyList AS HLVA ON HLVA.VarietyID=PVA.VarietyID 
            WHERE RRD.ReceivingID='$TrackID' $Query  AND PVA.CreatedFrom=1 ORDER BY `HLID` ASC";
        }
        // echo $sqlSCDQuery;
        // exit;
        $stmtSCD = $this->conn->prepare($sqlSCDQuery);
        $stmtSCD->execute();
        // $errorInfo = $stmtSCD->errorInfo();
        // var_dump($errorInfo);

        while ($dataSCDRow = $stmtSCD->fetch(PDO::FETCH_ASSOC)) {
            $ArraySCData['VarietyID'] = $dataSCDRow['VarietyID'];
            $ArraySCData['PackingGrade'] = $dataSCDRow['PackingGrade'];
            $ArraySCData['Treatment'] = $dataSCDRow['Treatment'];

            $ArrayData[] = $ArraySCData;
        }

        return $ArrayData;
    }
    function balanceSoakingInQty($VABreakUpID, $TrackID, $PlantID, $SoakingID, $VLID)
    {
        $Squery = '';
        $OutStage = 0;
        if ($SoakingID != '') {
            $Squery = " AND SckID!='$SoakingID'";
        }

        $StoreMaterialData = $this->checkIsStoreMaterial($TrackID, $OutStage);
        $IsStoreMaterial = $StoreMaterialData['IsStoreMaterial'];
        $OutStage = $StoreMaterialData['OutStage'];

        if ($IsStoreMaterial == 0) {
            // $sqlIsBlock = "SELECT Treatment,IsBlock FROM `PpcHLGradingBreakup` WHERE DataID='$VABreakUpID'  AND CreatedFrom=1 ";
            // $stmtIsBlock = $this->conn->prepare($sqlIsBlock);
            // $stmtIsBlock->execute();
            // $dataRowIsBlock = $stmtIsBlock->fetch(PDO::FETCH_ASSOC);

            // Step 1: Check if column exists
            $checkColumnSql = "SHOW COLUMNS FROM PpcHLGradingBreakup LIKE 'valueAddition'";
            $stmtCheck = $this->conn->prepare($checkColumnSql);
            $stmtCheck->execute();

            $columnExists = $stmtCheck->rowCount() > 0;

            // Step 2: Build query based on column existence
            if ($columnExists) {
                $sqlIsBlock = "SELECT Treatment, IsBlock,valueAddition
                            FROM PpcHLGradingBreakup 
                            WHERE DataID = $VABreakUpID 
                            AND CreatedFrom = 1 ";
            } else {
                $sqlIsBlock = "SELECT Treatment, IsBlock 
                            FROM PpcHLGradingBreakup 
                            WHERE DataID = $VABreakUpID 
                            AND CreatedFrom = 1";
            }

            // Step 3: Execute query
            $stmtIsBlock = $this->conn->prepare($sqlIsBlock);
            $stmtIsBlock->execute();

            $dataRowIsBlock = $stmtIsBlock->fetch(PDO::FETCH_ASSOC);

            if (($dataRowIsBlock['Treatment'] != 'UNT' && $dataRowIsBlock['IsBlock'] == 1) || ($dataRowIsBlock['Treatment'] != 'UNT' && $dataRowIsBlock['IsBlock'] == 0 && $dataRowIsBlock['valueAddition'] == 0)) {
                $sqlVAQty = "SELECT HLWeight AS TotalVAQty FROM `PpcHLGradingBreakup` WHERE DataID='$VABreakUpID'  AND CreatedFrom=1";
            } 
            else {
                $sqlVAQty = "SELECT sum(WtAftrVA) AS TotalVAQty FROM PpcValueAddition WHERE SoakingPlantID='$PlantID' AND TrackID='$TrackID' AND GradingBreakupID='$VABreakUpID'  AND CreatedFrom=1 ";
            }
            $stmtVAQty = $this->conn->prepare($sqlVAQty);
            $stmtVAQty->execute();
            $dataVARow = $stmtVAQty->fetch(PDO::FETCH_ASSOC);
            $TotalVAQty = $dataVARow['TotalVAQty'];
        } else {
            if($OutStage == 2){
                $TotalVAQty = $this->checkIsStoreMaterial($TrackID, $OutStage)['TotalVAQty'];
            } else{
                $sqlVAQty = "SELECT sum(WtAftrVA) AS TotalVAQty FROM PpcValueAddition WHERE SoakingPlantID='$PlantID' AND TrackID='$TrackID' AND VLID='$VLID'  AND CreatedFrom=1 ";
                $stmtVAQty = $this->conn->prepare($sqlVAQty);
                $stmtVAQty->execute();
                $dataVARow = $stmtVAQty->fetch(PDO::FETCH_ASSOC);
                $TotalVAQty = $dataVARow['TotalVAQty'];
            }
        }

        // $TotalVAQty = (float)$TotalVAQty + ($TotalVAQty * 0.25);
        $where = " AND VABreakupID='$VABreakUpID' ";
        if((empty($VABreakUpID) || $VABreakUpID == 'null') && !empty($VLID)){
            $where = " AND ValueAdditionID='$VLID' ";
        }
        $sqlSoakQty = "SELECT sum(SoakInQty) AS TotalSoakQty FROM `ProdSoakingDetails`WHERE  TrackID='$TrackID' AND PlantID='$PlantID' $Squery AND CreatedFrom=1  $where ";
        $stmtSoakQty = $this->conn->prepare($sqlSoakQty);
        $stmtSoakQty->execute();
        $dataBalRow = $stmtSoakQty->fetch(PDO::FETCH_ASSOC);
        $TotalSoakQty = $dataBalRow['TotalSoakQty'];
        $ArrayQty['BalanceQty'] = $TotalVAQty - $TotalSoakQty;
        $ArrayQty['sqlIsBlock'] = $sqlIsBlock;
        $ArrayQty['sqlVAQty'] = $sqlVAQty;
        $ArrayQty['sqlSoakQty'] = $sqlSoakQty;

        return $ArrayQty;
    }
    function checkIsStoreMaterial($TrackID, $OutStage)
    {
        $Query = '';
        if ($OutStage == 2) {
            $Query = " and OutStage=2";
        }
        $sqlQueryIsStoreMaterial = "SELECT   IsStoreMaterial ,sum(ReceivingQty) as StoreQty,OutStage from RmReceivingDetails 
        as RAD left join RmReceivingBreakup as RRB on RRB.ReceivingID=RAD.ReceivingID WHERE  RAD.ReceivingID='$TrackID' $Query";
        $stmtIsStoreMaterial = $this->conn->prepare($sqlQueryIsStoreMaterial);
        $stmtIsStoreMaterial->execute();
        $dataRowIsStoreMaterial = $stmtIsStoreMaterial->fetch(PDO::FETCH_ASSOC);
        $ArrayStoreMaterial['IsStoreMaterial'] = $dataRowIsStoreMaterial['IsStoreMaterial'];
        $ArrayStoreMaterial['TotalVAQty'] = $dataRowIsStoreMaterial['StoreQty'];
        $ArrayStoreMaterial['OutStage'] = $dataRowIsStoreMaterial['OutStage'];
        return $ArrayStoreMaterial;
    }
    function getLotNumberByStockId($stockIdS){
        if(!empty($stockIdS)){
            $sql = "SELECT group_concat(DISTINCT RLD.LotNumber ) LotNumber
                    FROM CsStockData CSD
                    LEFT JOIN CsDailyProductionData AS CDPD ON CDPD.ProductionID=CSD.ProductionID
                    LEFT JOIN (
                        SELECT TrackID, TubNo, LineNumber, MAX(ProductionID) AS MaxDataID
                        FROM ProdFreezingData
                        GROUP BY TrackID, TubNo, LineNumber
                        ) PFDx
                            ON CDPD.TrackID = PFDx.TrackID 
                            AND CDPD.TubNo = PFDx.TubNo 
                            AND CDPD.LineNumber = PFDx.LineNumber
                    LEFT JOIN ProdFreezingData PFD ON PFD.ProductionID = PFDx.MaxDataID
                    LEFT JOIN PpcHLGradingBreakup AS PGB ON PFD.GradingBreakupID=PGB.DataID  AND PGB.CreatedFrom=1
                    LEFT JOIN RmLotDetatils AS RLD ON PGB.LotID=RLD.RmLotID
                    WHERE `StockID` IN($stockIdS)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $rowData = $stmt->fetch(PDO::FETCH_ASSOC);
            return $rowData['LotNumber'];
        }
    }
}
