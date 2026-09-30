<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class ValueAdditionDetailsController
{
	private $conn;
	//	private $db_table = "ScrmApplicants";
	private $utilController;
	private $Error = null;
	private $Msg = '';
	private $auditLogger;


	// Db connection
	public function __construct($db)
	{
		$this->conn = $db;
		$this->utilController = new UtilController($this->conn);
		$this->auditLogger = new CentralAuditLogger($this->conn, 'PreProcessing');
	}

	public function getValueAdditionDetails($RequestBody)
	{
		$ArrivalID = $RequestBody['ArrivalID'];
		$ValueAdditionData = $this->getValueAdditionData($RequestBody);
		// $ValueAdditionData = $this->getValueAdditionData($ArrivalID);

		$Data['Error'] = null;
		$Data['Msg'] = ' Posted Successful';

		$Data['Data'] = $ValueAdditionData;

		return $Data;
	}



	public function postDeleteValueAddition($RequestBody)
	{
		$ArrivalID = $RequestBody['ArrivalID'];
		$BreakUpID = $RequestBody['BreakUpID'];
		$TransactionDate = $RequestBody['TransactionDate'];
		$existingValueAddition = $this->auditLogger->fetchRowById('PpcValueAddition', 'VLID', $BreakUpID);

		$sqlQuery = "DELETE FROM `PpcValueAddition` WHERE VLID='$BreakUpID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$ArrivalID = $RequestBody['ArrivalID'];

		// $ValueAdditionData = $this->getValueAdditionData($ArrivalID);
		$ValueAdditionData = $this->getValueAdditionData($RequestBody);

		$Data['Error'] = null;
		$Data['Msg'] = 'Deleted  Successfully';
		$Data['Data'] = $ValueAdditionData;
		$this->auditLogger->logDelete('PpcValueAddition', $BreakUpID, $existingValueAddition, $RequestBody, [
			'ReferenceNo' => $ArrivalID,
			'Remarks' => 'PreProcessing value addition deleted',
		]);


		return $Data;
	}

	public function postValueAdditionDetails($RequestBody)
	{

		// var_dump($RequestBody); exit;
		$PostedUserID = 1;
		//$ArrivalNumber = $RequestBody['ArrivalNumber'];
		$ArrivalID = $RequestBody['ArrivalID'];
		$ValueAdditionID = $RequestBody['BreakUpID'];
		$PlantID = $RequestBody['Plant'];
		$StationID = $RequestBody['Center'];
		$ProcessingDate = $RequestBody['TransactionDate'];
		$Shift = $RequestBody['Shift'];
		$Supervisor = $RequestBody['Supervisor'];
		//$CheckingCount = $RequestBody['HeadonCountAfterChecking'];
		$WtAftrVA = $RequestBody['WeightAfterVA'];
		$CountAftrVA = $RequestBody['CountAfterVA'];
		$HLCount = $RequestBody['HLCount'];
		$HLQuantity = $RequestBody['HLQuantity'];

		$NetCount = $RequestBody['CountNetYield'];
		$IceConsumption = $RequestBody['IceConsumption'];
		$Remarks = $RequestBody['Remarks'];
		$ReceivedWeight = $RequestBody['ReceivedWeight'];
		$VarietyID = $RequestBody['VarietyID'];
		$ReturnWeight = $RequestBody['ReturnWeight'];
		$BrokenWeight = $RequestBody['BrokenWeight'];
		$HLBreakupID = $RequestBody['HLBreakupID']; //gradingbreakupID
		$WageID = $RequestBody['WageID'];
		$existingValueAddition = null;
		$AryCurrentDate = getCurrentDateTime();

		$CreatedDateTime = $AryCurrentDate['TimeStamp'];

		$SqlWeight = "SELECT sum(HLWeight) as GradingHLWeight,
		(SELECT sum(SentWeight)  FROM `PpcValueAddition` WHERE LotID='$ArrivalID' AND CreatedFrom=0)  as VAHLWeight,				
		(SELECT sum(SentWeight)  FROM `PpcValueAddition` WHERE LotID='$ArrivalID' and SentCount='$HLCount'  AND CreatedFrom=0)  as VAHLWeightCountwise,				
		(SELECT sum(HLWeight)  FROM `PpcHLGradingBreakup` WHERE DataID='$HLBreakupID' and LotID='$ArrivalID'   AND CreatedFrom=0)  as GradingHLWeightCountwise
		FROM `PpcHLGradingBreakup` WHERE  LotID='$ArrivalID'   AND CreatedFrom=0";
		$stmtWeight = $this->conn->prepare($SqlWeight);
		$stmtWeight->execute();
		$dataRowWeight = $stmtWeight->fetch(PDO::FETCH_ASSOC);

		$GradingHLWeight = $dataRowWeight['GradingHLWeight'];
		$GradingHLWeightCountwise = $dataRowWeight['GradingHLWeightCountwise'];
		if ($ValueAdditionID == '') {
			$VAHLWeight = $dataRowWeight['VAHLWeight'] + $HLQuantity;
		} else {
			$VAHLWeight = ($dataRowWeight['VAHLWeight'] + $HLQuantity) - $dataRowWeight['VAHLWeightCountwise'];
		}

		$stmt = false;


		if ($ValueAdditionID == "") {

			$sqlQuery = " INSERT INTO `PpcValueAddition`( `LotID`,  `SentCount`, `SentWeight`,`ReceivedWeight`,`ReturnWeight`,`BrokenWeight`,  `ProcessingDate`,
					`PostedDate`, `WtAftrVA`, `CountAftrVA`, `VarietyID`, `ShiftID`, 
					`StationID`, `PlantID`,`PostedUserID`, `Remarks`, `Supervisor`, `IceConsumption`, `NetCount`,`GradingBreakupID`,`WageID`,CreatedFrom) 
					VALUES ('$ArrivalID','$HLCount','$HLQuantity','$ReceivedWeight','$ReturnWeight', '$BrokenWeight','$ProcessingDate','$CreatedDateTime','$WtAftrVA','$CountAftrVA','$VarietyID','$Shift',
					'$StationID','$PlantID','$PostedUserID','$Remarks','$Supervisor','$IceConsumption','$NetCount','$HLBreakupID', '$WageID',0)";
		} else {
			$existingValueAddition = $this->auditLogger->fetchRowById('PpcValueAddition', 'VLID', $ValueAdditionID);

			$sqlQuery = " UPDATE  PpcValueAddition SET  SentCount = '$HLCount', SentWeight = '$HLQuantity', ReceivedWeight = '$ReceivedWeight', ReturnWeight = '$ReturnWeight',
					`BrokenWeight` = '$BrokenWeight', ProcessingDate = '$ProcessingDate', PostedDate = '$CreatedDateTime', WtAftrVA = '$WtAftrVA', CountAftrVA = '$CountAftrVA', VarietyID = '$VarietyID',
					ShiftID = '$Shift', StationID = '$StationID', PlantID = '$PlantID', PostedUserID = '$PostedUserID', Remarks = '$Remarks', Supervisor = '$Supervisor',
					IceConsumption = '$IceConsumption',NetCount = '$NetCount', GradingBreakupID = '$HLBreakupID', WageID = '$WageID'
					WHERE  VLID = '$ValueAdditionID'";
		}
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();

		// $errorInfo = $stmt->errorInfo();
		// var_dump($errorInfo);

		if ($stmt) {
			if ($ValueAdditionID == '') {
				$lastInsertId = $this->conn->lastInsertId();
				$createdValueAddition = $this->auditLogger->fetchRowById('PpcValueAddition', 'VLID', $lastInsertId);
				$this->auditLogger->logInsert('PpcValueAddition', $lastInsertId, $createdValueAddition, $RequestBody, [
					'ReferenceNo' => $ArrivalID,
					'Remarks' => 'PreProcessing value addition created',
				]);
			} else {
				$updatedValueAddition = $this->auditLogger->fetchRowById('PpcValueAddition', 'VLID', $ValueAdditionID);
				$this->auditLogger->logUpdate('PpcValueAddition', $ValueAdditionID, $existingValueAddition, $updatedValueAddition, $RequestBody, [
					'ReferenceNo' => $ArrivalID,
					'Remarks' => 'PreProcessing value addition updated',
				]);
			}

			// $ValueAdditionData = $this->getValueAdditionData($ArrivalID);
			$ValueAdditionData = $this->getValueAdditionData($RequestBody);
			$HlGradingData = $this->getLotBreakUpDetails($ArrivalID);
			$Data['Error'] = null;
			$Data['Msg'] = 'Success';
			$Data['Data'] = $ValueAdditionData;
			$Data['HlGradingData'] = $HlGradingData;
			return $Data;
		} else {
			return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
		}
	}

	function getValueAdditionData($RequestBody)
	{
		$TransactionDate = $RequestBody['TransactionDate'];
		$ArrivalID = $RequestBody['ArrivalID'];

		// var_dump($TransactionDate);

		$Query = '';

		if ($ArrivalID != 0 && $ArrivalID != '') {
			$Query .= " AND PVA.LotID='$ArrivalID'";
		} else if($ArrivalID == 0) {
			$Query .= " AND PVA.ProcessingDate='$TransactionDate' AND LotID=0";
		} else if($ArrivalID == 1) {
			$Query .= " AND PVA.ProcessingDate='$TransactionDate' AND LotID=1";
		}

		// $dataRow = null;
		$sqlFields = "VLID,ProcessingDate,LotNumber as ArrivalNumber,StationName,UnitName,UserName,PPO.Name as Shift ,CountAftrVA,WtAftrVA,NetCount,
		IceConsumption,SentCount,SentWeight,
        ReturnWeight,BrokenWeight,ReceivedWeight,PPOV.ShortName as Variety,PVA.LotID ,PVA.VarietyID ,PVA.StationID ,PVA.ShiftID ,PVA.PlantID ,
        PVA.Remarks, PSY.Yield as StandardYield, AGO.Name as WageName, AGO.OptID as WageID";

		$sqlQuery = "SELECT  $sqlFields FROM `PpcValueAddition`   PVA 
        left join PpcVAStations  PVS on PVS.StationID=PVA.StationID
        left join RmUsersList  RUL on RUL.ID=PVA.Supervisor
		left join RmLotDetatils as RLD on RLD.RmLotID=PVA.LotID
		left join RmArrivalDetatils  RAD on RAD.RmArrivalID=RLD.RmReferenceID
		left join PpcOptions  PPO on PPO.OptID=PVA.ShiftID
        left join PpcHLVAVarietyList  PPOV on PPOV.VarietyID=PVA.VarietyID
        left join RmProcessingStations  RPS on RPS.ProcessingStationID=PVA.PlantID
		left join AdminGeneralOptions as AGO on AGO.OptionType='WAGE_TYPE' and AGO.Status=1 and AGO.OptID = PVA.WageID
		left join PpcStandardYields PSY on PSY.Type = PPOV.ShortName and PVA.SentCount between PSY.LowerCount and PSY.UpperCount
         where 1=1 $Query AND PVA.CreatedFrom=0 ORDER BY `VLID` asc";

		//ECHO $sqlQuery; EXIT;
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();

		// $errorInfo = $stmt->errorInfo();
		// var_dump($errorInfo);

		$ArrayData = [];

		$TotalHLQuantity = 0;
		$i = 0;
		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

			$ReceivedWeight = $dataRow['ReceivedWeight'];
			$WtAftrVA = $dataRow['WtAftrVA'];

			$Yield = ($WtAftrVA / $ReceivedWeight) * 100;
			// YIELD  % =(Wieght after VA (Kgs) / Recieved  HL Wt(kgs)  ) * 100
			$StandardYield = $dataRow['StandardYield'];
			$Diff = 0;
			if ($StandardYield > 0) {
				$Diff = $Yield - $StandardYield;
				$Diff = round($Diff, 2);
			}
			$ValueAdditionData['SNo'] = ++$i;
			$ValueAdditionData['BreakUpID'] = $dataRow['VLID'];
			$ValueAdditionData['ProcessingDate'] = $this->utilController->getFormatedDate($dataRow['ProcessingDate']);
			$ValueAdditionData['TransactionDate'] = $dataRow['ProcessingDate'];
			$ValueAdditionData['LotNo'] = $dataRow['ArrivalNumber'];
			$ValueAdditionData['Plant'] = $dataRow['StationName'];
			$ValueAdditionData['Center'] = $dataRow['UnitName'];
			$ValueAdditionData['UserName'] = $dataRow['UserName'];
			$ValueAdditionData['Shift'] = $dataRow['Shift'];
			$ValueAdditionData['ReceivedWeight'] = $dataRow['ReceivedWeight'];
			$ValueAdditionData['ReturnWeight'] = $dataRow['ReturnWeight'];
			$ValueAdditionData['BrokenWeight'] = $dataRow['BrokenWeight'];
			$ValueAdditionData['CountAftrVA'] = $dataRow['CountAftrVA'];
			$ValueAdditionData['WtAftrVA'] = $dataRow['WtAftrVA'];
			$ValueAdditionData['HLCount'] = $dataRow['SentCount'];
			$ValueAdditionData['HLQuantityKgs'] = $dataRow['SentWeight'];
			$ValueAdditionData['CountNetYield'] = $dataRow['NetCount'];
			$ValueAdditionData['IceConsumption'] = $dataRow['IceConsumption'];
			$ValueAdditionData['Remarks'] = $dataRow['Remarks'];
			$ValueAdditionData['Variety'] = $dataRow['Variety'];
			$ValueAdditionData['ArrivalID'] = $dataRow['LotID'];
			$ValueAdditionData['VarietyID'] = $dataRow['VarietyID'];
			$ValueAdditionData['CenterID'] = $dataRow['StationID'];
			$ValueAdditionData['ShiftID'] = $dataRow['ShiftID'];
			$ValueAdditionData['PlantID'] = $dataRow['PlantID'];
			$ValueAdditionData['Yield'] = round($Yield, 2);
			$ValueAdditionData['Diff'] = $Diff;
			$ValueAdditionData['standardYield'] = $StandardYield;
			$ValueAdditionData['WageID'] = $dataRow['WageID'];

			$ArrayData[] = $ValueAdditionData;
			$TotalHLQuantity += $ValueAdditionData['HLQuantityKgs'];
		}
		$ArrayTotals['TotalHLQuantity'] = $TotalHLQuantity;

		$Data['HLCounts'] = $this->getLotBreakUpDetails($ArrivalID);
		$Data['ValueAdditionData'] = $ArrayData;
		$Data['Totals'] = $ArrayTotals;

		return $Data;
	}


	function getLotBreakUpDetails($ArrivalID)
	{

		if ($ArrivalID != 0) {
			$sqlFields = "HLCount,HLWeight,DataID,NoCrates,MixedID";
			$SqlHLData = "Select $sqlFields From PpcHLGradingBreakup WHERE LotID='$ArrivalID' AND CreatedFrom=0";
			$stmt = $this->conn->prepare($SqlHLData);
			$stmt->execute();
			while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$LotBreakUpData['HLBreakupID'] = $dataRow['DataID'];
				$LotBreakUpData['Title'] = $dataRow['HLCount'] . ' - ' . $dataRow['HLWeight'];
				$LotBreakUpData['HLCount'] = $dataRow['HLCount'];
				$LotBreakUpData['HLWeight'] = $dataRow['HLWeight'];
				$ArrayData[] = $LotBreakUpData;
			}
		} else {
			for ($i = 1; $i <= 300; $i++) {
				$LotBreakUpData['Title'] = $i . '.00';
				$LotBreakUpData['HLCount'] = $i . '.00';
				$ArrayData[] = $LotBreakUpData;
			}
		}
		// $LotBreakUpData['DataID'] = $dataRow['DataID'];
		// $LotBreakUpData['NoCrates'] = $dataRow['NoCrates'];
		// $LotBreakUpData['MixedID'] = $dataRow['MixedID'];
		return $ArrayData;
	}
}
