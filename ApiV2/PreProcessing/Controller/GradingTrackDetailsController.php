<?php
session_start();
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once '../App/Controller/AppUtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class GradingTrackDetailsController
{
	private $conn;
	private $db_table = "ScrmApplicants";
	private $utilController;
	private $Error = null;
	private $Msg = '';

	private $restApi;
	private $appUtilController;
	private $auditLogger;

	// Db connection
	public function __construct($db)
	{
		$this->conn = $db;
		$this->restApi = new REST();

		$this->utilController = new UtilController($this->conn);
		$this->appUtilController = new AppUtilController($this->conn); //uma
		$this->auditLogger = new CentralAuditLogger($this->conn, 'PreProcessing');
	}


	function getToken()
	{
		$authToken = $this->restApi->getRequestHeader();
		return $payload = Token::Verify($authToken);
	}

	public function getGradingDetails($RequestBody)
	{


		$TrackID = $RequestBody['TrackID'];

		$GradingData = $this->getGradingData($RequestBody);
		$GradingBreakUpData = $this->getGradingBreakUp($RequestBody);

		// $Data['Session'] = $_SESSION['ApiUrl'];
		$Data['Error'] = $this->Error;
		$Data['Msg'] = $this->Msg;
		$Data['GradingDetails'] = $GradingData;
		$Data['GradingBreakUpDetails'] = $GradingBreakUpData;

		return $Data;
	}

	public function postGradingDetails($RequestBody)
	{
		$Type = $RequestBody['Type'];
		$DataID = $RequestBody['BreakUpID'];
		$HLID = $RequestBody['GradingID'];

		if ($Type == "BreakUpData") {

			if ($DataID == "") {
				$this->insertGradingBreakUp($RequestBody);
			} else {
				$this->updateGradingBreakUp($RequestBody);
			}
		} else {
			$this->insertGradingDetails($RequestBody);
		}

		$Data['Error'] = $this->Error;
		$Data['Msg'] = $this->Msg;
		$Data = $this->getGradingDetails($RequestBody);

		return $Data;
	}


	function getGradingData($RequestBody)
	{
		$ArrivalID = $RequestBody['ArrivalID'];
		$TrackID = $RequestBody['TrackID'];
		$ReceivalBreakupID = $RequestBody['ReceivalBreakupID'];

		$sqlFields = " ReceivalBreakupID,RAD.ArrivalDate,PHGD.`StationID`, `PlantID`,`GradingDate`,PHGD.MachineID,PHGD.Remarks,PHGD.GradingSupervisorID,
		 `StartTime`, `EndTime`, `BreakTime`,PHGD.LotID, HLID";

		$sqlQuery = "  SELECT  $sqlFields FROM RmArrivalBreakUpDetails AS RABD 
		LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID=RABD.ArrivalID 
		LEFT JOIN RmReceivingBreakup  AS RRB ON RRB.ArrivalBreakupID=RABD.ArrivalBrkUpID
		LEFT JOIN PpcHLGradingDetails AS PHGD ON  PHGD.TrackID =RRB.ReceivingID   AND PHGD.CreatedFrom=1
		LEFT JOIN PpcVAStations AS PVS on PVS.StationID=PHGD.StationID
		LEFT JOIN RmProcessingStations AS RPS ON RPS.ProcessingStationID=PHGD.PlantID
		LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID=PHGD.LotID  AND PHGB.CreatedFrom=1
        LEFT JOIN RmDefectsList AS RDL ON RDL.DefectID=PHGB.DefectID 
        LEFT JOIN AdminGradingMachines AS AGD ON AGD.MachineID=PHGD.MachineID 
		WHERE PHGD.LotID in ($ArrivalID) and TrackID='$TrackID' and TrackBreakUpID in ($ReceivalBreakupID)
		ORDER BY `HLID`";

		// echo print_r($sqlQuery);exit;

		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();

		$GradingData = [];

		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

		$GradingData['GradingID'] = $dataRow['HLID'];
		$GradingData['ArrivalDate'] = $dataRow['ArrivalDate'];
		$GradingData['TransactionDate'] = $dataRow['GradingDate'];
		$GradingData['ReceivigCount'] = $dataRow['LotID'];
		$GradingData['StartTime'] = $dataRow['StartTime'];
		$GradingData['EndTime'] = $dataRow['EndTime'];
		$GradingData['BreakTime'] = $dataRow['BreakTime'];
		$GradingData['StationID'] = $dataRow['StationID'];
		$GradingData['PlantID'] = $dataRow['PlantID'];
		$GradingData['MachineID'] = $dataRow['MachineID'];
		$GradingData['Remarks'] = $dataRow['Remarks'];
		$GradingData['GradingSupervisorID'] = $dataRow['GradingSupervisorID'];

		
		$GradingData['ReceivingCountDeatils'] = $this->getReceivingCountDetails($RequestBody);

		return $GradingData;
	}
	function getGradingBreakUp($RequestBody)
	{

		// echo print_r($RequestBody);
		$ReceivalBreakupID = $RequestBody['ReceivalBreakupID'];
		$ArrivalID = $RequestBody['ArrivalID'];
		$TrackID = $RequestBody['TrackID'];
		$Id = $RequestBody['BreakUpID'];
		
		$IsHONPacking = $RequestBody['IsHONPacking']; //sriram
		$IsShowAddValueAddtion = $RequestBody['IsShowAddValueAddtion']; //sriram

		$HonQuery = '';
		if($IsHONPacking == 1){
			$HonQuery = ' AND RRB.HONPacking = 0';
		}

		$ValueFields = '';
		if($IsShowAddValueAddtion == 1){
			$ValueFields = ',PHGB.valueAddition';
		}

		$sqlFields = "ReceivalBreakupID,NoCrates,PHGB.LotID as RmArrivalID,TrackBreakUpID,HLCount,HLWeight,DataID, PHGB.VarietyID, PHGB.Treatment, PHGB.IsBlock,MixedID,
		PHGB.PackingGrade,(SELECT sum(ReceivingQty) as HonWeight FROM `RmLotDetatils` as RLD
		 left join RmArrivalBreakUpDetails as RABD on RABD.ArrivalBrkUpID=RLD.BreakUpID
		 LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalBreakupID=RABD.ArrivalBrkUpID WHERE RmLotID in ($ArrivalID))as HonWeight $ValueFields";

		$sqlQuery = "SELECT  $sqlFields FROM PpcHLGradingBreakup AS PHGB 
		LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID AND PHGD.CreatedFrom=1
		LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ReceivalBreakupID=PHGD.TrackBreakUpID 
		WHERE PHGB.LotID in ($ArrivalID) AND PHGB.CreatedFrom=1 AND RRB.ReceivalBreakupID IS NOT NULL $HonQuery";

		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();

		$Data = array();
		$i = 0;
		$TotalWeight = 0;
		$TotalCrates = 0;

		$Token = $this->getToken();
		$ClientId = $Token['ClientID'];

		$rowClientDetails = $this->appUtilController->getClientConfig($ClientId);
		$DefaultCrate = $rowClientDetails['DefaultCrate'];


		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$RequestBody['DataBreakUpID'] = $dataRow['DataID'];
			$RequestBody['ArrivalID'] = $dataRow['MixedID'];
			$DeleteAccess = $this->getDeleteAccess($RequestBody);

			$GradingData['SNo'] = ++$i;
			$GradingData['ReceivalBreakupID'] = $dataRow['ReceivalBreakupID'];
			$GradingData['GradingBreakUpID'] = $dataRow['DataID'];
			$GradingData['ArrivalID'] = $dataRow['RmArrivalID'];
			$GradingData['NoofCrates'] = $dataRow['NoCrates'];
			$GradingData['Treatment'] = $dataRow['Treatment'];
			$GradingData['IsBlock'] = $dataRow['IsBlock'];
			$GradingData['DeleteAccess'] = $DeleteAccess;
			if($IsShowAddValueAddtion == 1){
				$GradingData['valueAddition'] = $dataRow['valueAddition'];
			}

			// $GradingData['CrateQuantity'] = '27';
			if ($dataRow['NoCrates'] == 1) {
				$GradingData['CrateQuantity'] = round($dataRow['HLWeight']);
			} else {
				$GradingData['CrateQuantity'] = $DefaultCrate;
			}
			$GradingData['HLCount'] = ($dataRow['HLCount'] === 0.00 || $dataRow['HLCount'] === '0.00') ? ' ' : $dataRow['HLCount'];
			$GradingData['GradingQty'] = $dataRow['HLWeight']; // HL Weight as Grading Qty
			$GradingData['Variety'] = $dataRow['VarietyID'];
			$GradingData['Grade'] = $dataRow['PackingGrade'];

			$ArrayData[] = $GradingData;
			$TotalWeight += $dataRow['HLWeight'];
			$TotalHONQty += $dataRow['HonWeight'];
			$TotalCrates += $dataRow['NoCrates'];
			$TotalHLWeight += $dataRow['HLWeight'];
		}

		$ArrayTotals['TotalWeight'] = $TotalWeight;
		$ArrayTotals['TotalHONQty'] = $TotalHONQty; //uma
		$ArrayTotals['TotalHLWeight'] = $TotalHLWeight; //uma
		$ArrayTotals['TotalCrates'] = $TotalCrates;


		$sqlVarietyQuery = "SELECT Species FROM `RmLotDetatils` as RLD LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID =RLD.RmReferenceID WHERE RLD.RmLotID in ($ArrivalID)";
		$stmt1 = $this->conn->prepare($sqlVarietyQuery);
		$stmt1->execute();
		$dataRow1 = $stmt1->fetch(PDO::FETCH_ASSOC);
		$Species = $dataRow1['Species'];


		$Data['Variety'] = $this->getVarietyDetails($Species);
		$Data['ProductionGrades'] = $this->getGrades();
		$Data['Treatment'] = $this->getTreatment();
		$Data['GradingData'] = $ArrayData;
		$Data['Totals'] = $ArrayTotals;
		$Data['GrandTotals'] = $this->getTotals($RequestBody);
		if(!empty($RequestBody['ShowBatchCode'])){
			/*arrival details*/
			$sqlBill = "SELECT GROUP_CONCAT(CONCAT(RLD.LotNumber, '(', RABD.Count, ')') SEPARATOR ',\n' ) AS BillDetails
								FROM RmLotDetatils RLD
								LEFT JOIN RmArrivalBreakUpDetails RABD 
								ON RABD.ArrivalBrkUpID = RLD.BreakUpID
								WHERE RLD.RmLotID IN ($ArrivalID)";
			$stmtBill = $this->conn->prepare($sqlBill);
			$stmtBill->execute();
			$Data['BillDetails'] = $stmtBill->fetch(PDO::FETCH_ASSOC)['BillDetails'];
			/*arrival details ends*/
		}
		return $Data;

	}

	public function getTotals($RequestBody)
	{
		$ArrivalID = $RequestBody['ArrivalID'];
		$TrackID = $RequestBody['TrackID'];

		$sqlHONQuery = "SELECT sum(ReceivingQty) as HonWeight FROM `RmLotDetatils` as RLD
		 left join RmArrivalBreakUpDetails as RABD on RABD.ArrivalBrkUpID=RLD.BreakUpID
		 LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalBreakupID=RABD.ArrivalBrkUpID WHERE RmLotID in ($ArrivalID)";
		$stmtHON = $this->conn->prepare($sqlHONQuery);
		$stmtHON->execute();
		// $errorInfo = $stmtHON->errorInfo();
		// var_dump($errorInfo);
		$dataRowHON = $stmtHON->fetch(PDO::FETCH_ASSOC);
		$TotalHON = $dataRowHON['HonWeight'];

		// Total HL
		$sqlHLQuery = "SELECT SUM(HLWeight) AS TotalQty FROM PpcHLGradingBreakup WHERE LotID in ($ArrivalID) AND CreatedFrom=1";
		$stmtHL = $this->conn->prepare($sqlHLQuery);
		$stmtHL->execute();
		$dataRowHL = $stmtHL->fetch(PDO::FETCH_ASSOC);
		$TotalHL = $dataRowHL['TotalQty'];

		$Yield = $TotalHL / $TotalHON * 100;

		$ArrayTotals['TotalHON'] = $TotalHON;
		$ArrayTotals['TotalHL'] = number_format($TotalHL, 3);
		$ArrayTotals['Yield'] = round($Yield, 2);

		return $ArrayTotals;
	}

	public function insertGradingDetails($RequestBody)
	{

		$PostedUserID = 1;
		$HLID = $RequestBody['GradingID'];
		$ArrivalID = $RequestBody['ArrivalID'];
		$TrackID = $RequestBody['TrackID'];
		$ReceivalBreakupID = $RequestBody['ReceivalBreakupID'];
		// $PlantID = $RequestBody['Plant'];  	// Same Value for ValueAdditionPlantID and PlantID
		$PlantID = ($RequestBody['Plant'] != '' ? $RequestBody['Plant'] : 0); // sriram
		$StationID = $RequestBody['Center'];
		$GradingDate = $RequestBody['TransactionDate'];
		$MachineID = $RequestBody['MachineID'];
		$StartTime = $RequestBody['StartTime'];
		$EndTime = $RequestBody['EndTime'];
		$BreakTime = $RequestBody['BreakTime'];
		$Remarks = $RequestBody['Remarks'];
		$MixIds = $RequestBody['MixIds'];
		$GradingSupervisorID = $RequestBody['GradingSupervisorID'];

		$aryMixIds = 0;
		$aryMixIds = count(explode(',', $MixIds));

		if ($aryMixIds > 1) {
			$IsMix = 1;
			$ArrivalID = $MixIds; //uma 12-04-2024
		} else {
			$IsMix = 0;
			$MixIds = $ArrivalID;
		}

		$sqlCount = "SELECT Count(*) AS Count,MixIDs as OldMixIDs FROM PpcHLGradingDetails WHERE LotID in ($ArrivalID) and TrackID='$TrackID' AND CreatedFrom=1
		and TrackBreakUpID in ($ReceivalBreakupID) order by HLID desc limit 1";

		$stmtCount = $this->conn->prepare($sqlCount);
		$stmtCount->execute();
		$dataRow = $stmtCount->fetch(PDO::FETCH_ASSOC);
		$RowCount = $dataRow['Count'];
		$OldMixIDs = $dataRow['OldMixIDs'];

		if ($RowCount == 0) {

			for ($i = 0; $i < $aryMixIds; $i++) { //uma 12-04-2024

				if ($IsMix == 1) { //uma 12-04-2024
					// $MixIds = sort($MixIds);
					$AryMixIds = explode(',', $MixIds);

					// $AryMixIds = explode(',', sort($MixIds));
					// print_r($AryMixIds);

					$ArrivalID = $AryMixIds[$i];
					$MixReceivalBreakupIDs = $RequestBody['ReceivalBreakupID'];
					$AryReceivalBreakupID = explode(',', $MixReceivalBreakupIDs);
					$ReceivalBreakupID = $AryReceivalBreakupID[$i];
				}

				$sqlInsert = "INSERT INTO `PpcHLGradingDetails`( `LotID`, `TrackID`, `TrackBreakUpID`, `PlantID`, `ValueAdditionPlantID`, `StationID`,
				`GradingDate`, `MachineID`, `StartTime`, `EndTime`, `BreakTime`, `Remarks`, `UserID`, `PostedDate`,`IsMix`,`MixIDs`,`GradingSupervisorID`)
				values('$ArrivalID', '$TrackID', '$ReceivalBreakupID', '$PlantID', '$PlantID', '$StationID', '$GradingDate', '$MachineID', '$StartTime', '$EndTime',
				'$BreakTime', '$Remarks', '$PostedUserID', NOW(),'$IsMix','$MixIds','$GradingSupervisorID')";

				$stmtFormInsert = $this->conn->prepare($sqlInsert);
				$stmtFormInsert->execute();
				if ($stmtFormInsert) {
					$lastInsertId = $this->conn->lastInsertId();
					$createdDetail = $this->auditLogger->fetchRowById('PpcHLGradingDetails', 'HLID', $lastInsertId);
					$this->auditLogger->logInsert('PpcHLGradingDetails', $lastInsertId, $createdDetail, $RequestBody, [
						'ReferenceNo' => $TrackID,
						'Remarks' => 'PreProcessing grading tracking header created',
					]);
				}

				// $errorInfo = $stmtFormInsert->errorInfo();
				// var_dump($errorInfo);
				// $j++;
			}


			if ($stmtFormInsert) {
				$RequestBody['MixIds'] = $MixIds;
				$this->Error = null;
				$this->Msg = 'Form Data Posted';
			} else {
				$this->Error = 'Failed to insert Form Data';
				$this->Msg = 'Failed to insert Form Data';
			}
		} else if ($MixIds == $OldMixIDs) {
			for ($i = 0; $i < $aryMixIds; $i++) { //uma  12-04-2024
				if ($IsMix == 1) { //uma 12-04-2024
					$AryMixIds = explode(',', $MixIds);
					$ArrivalID = $AryMixIds[$i];
				}
				$existingDetail = $this->auditLogger->fetchOne("SELECT * FROM PpcHLGradingDetails WHERE LotID in($ArrivalID) and TrackID=? and TrackBreakUpID in ($ReceivalBreakupID)", [$TrackID]);

				$sqlUpdate = "UPDATE `PpcHLGradingDetails` SET  `LotID`='$ArrivalID', `TrackID`='$TrackID', `TrackBreakUpID`='$ReceivalBreakupID', `PlantID`='$PlantID', 
			`ValueAdditionPlantID`='$PlantID', `StationID`='$StationID', `GradingDate`='$GradingDate', `MachineID`='$MachineID', `StartTime`='$StartTime', `EndTime`='$EndTime', 
			`BreakTime`='$BreakTime', `Remarks`='$Remarks', `UserID`='$PostedUserID',`IsMix`='$IsMix', `MixIDs`='$MixIds', `PostedDate`=NOW() ,`GradingSupervisorID`='$GradingSupervisorID'  
			WHERE LotID in($ArrivalID) and TrackID='$TrackID' and TrackBreakUpID in ($ReceivalBreakupID)";

				$stmtFormUpdate = $this->conn->prepare($sqlUpdate);
				$stmtFormUpdate->execute();
				if ($stmtFormUpdate) {
					$updatedDetail = $this->auditLogger->fetchOne("SELECT * FROM PpcHLGradingDetails WHERE LotID in($ArrivalID) and TrackID=? and TrackBreakUpID in ($ReceivalBreakupID)", [$TrackID]);
					$entityId = isset($updatedDetail['HLID']) ? $updatedDetail['HLID'] : $TrackID;
					$this->auditLogger->logUpdate('PpcHLGradingDetails', $entityId, $existingDetail, $updatedDetail, $RequestBody, [
						'ReferenceNo' => $TrackID,
						'Remarks' => 'PreProcessing grading tracking header updated',
					]);
				}
			}
			if ($stmtFormUpdate) {
				$RequestBody['MixIds'] = $MixIds;
				$this->Error = null;
				$this->Msg = 'Form Data Posted';
			} else {
				$this->Error = 'Failed to insert Form Data';
				$this->Msg = 'Failed to insert Form Data';
			}
		} else {
			$this->Error = 'Please  delete grading to update or mix the data';
			$this->Msg = 'Please  delete grading to  update or mix the data';
		}
		//echo $stmtFormInsert;


	}

	function insertGradingBreakUp($RequestBody)
	{
		$TrackID = $RequestBody['TrackID'];
		$LotID = $RequestBody['ArrivalID']; // LotID = ArrivalID
		$Type = $RequestBody['Type'];
		$MixIds = $RequestBody['MixIds'];
		// $aryMixIds = count(explode(',', $MixIds));


		$sqlCount = "SELECT distinct  MixIDs as OldMixIDs  FROM PpcHLGradingDetails WHERE LotID in ($LotID) AND CreatedFrom=1";
		$stmtCount = $this->conn->prepare($sqlCount);
		$stmtCount->execute();
		$dataRow = $stmtCount->fetch(PDO::FETCH_ASSOC);
		$OldMixIDs = $dataRow['OldMixIDs'];


		$sqlBrkupInsert = "INSERT INTO `PpcHLGradingBreakup` (`LotID`,`MixedID`) VALUES ('$LotID','$OldMixIDs')";
		$stmtBrkupInsert = $this->conn->prepare($sqlBrkupInsert);
		$stmtBrkupInsert->execute();

		if ($stmtBrkupInsert) {
			$lastInsertId = $this->conn->lastInsertId();
			$createdBreakup = $this->auditLogger->fetchRowById('PpcHLGradingBreakup', 'DataID', $lastInsertId);
			$this->auditLogger->logInsert('PpcHLGradingBreakup', $lastInsertId, $createdBreakup, $RequestBody, [
				'ReferenceNo' => $TrackID,
				'Remarks' => 'PreProcessing grading tracking breakup created',
			]);
			$this->utilController->UpdateTrackWiseRawHLWeight($TrackID, '', '', $RequestBody);
			$this->utilController->UpdateTrackWiseBlockMeatWeight($TrackID,$RequestBody);
			$this->Error = null;
			$this->Msg = 'New Row Insert';
		} else {
			$this->Error = 'Unable to Insert New Row';
			$this->Msg = 'Unable to Insert New Row';
		}

	}

	function updateGradingBreakUp($RequestBody)
	{
		$TrackID = $RequestBody['TrackID'];
		$DataID = $RequestBody['BreakUpID']; // DataID as BreakUpID, DataBreakUpID, GradingBreakUpID
		$LotID = $RequestBody['ArrivalID']; // LotID as ArrivalID
		$VarietyID = $RequestBody['VarietyID'];
		$HLWeight = $RequestBody['GradingQty']; // HLWeight as GradingQty
		$HLCount = $RequestBody['HLCount'];
		$NoCrates = $RequestBody['NoCrates'];
		$CrateQty = $RequestBody['CrateQty'];
		$ActionType = $RequestBody['ActionType'];
		$Type = $RequestBody['Type'];
		$MixIds = $RequestBody['MixIds'];
		$PackingGrade = $RequestBody['GradeID'];
		$Treatment = $RequestBody['Treatment'];
		$IsBlock = $RequestBody['IsBlock'];

		$sqlCounts = "SELECT sum(HLWeight) AS TotalHLWeight, sum(NoCrates) AS TotalCrates,Treatment FROM PpcHLGradingBreakup WHERE DataID='$DataID' AND CreatedFrom=1";
		$stmtCounts = $this->conn->prepare($sqlCounts);
		$stmtCounts->execute();
		$dataRow = $stmtCounts->fetch(PDO::FETCH_ASSOC);
		$existingBreakup = $this->auditLogger->fetchRowById('PpcHLGradingBreakup', 'DataID', $DataID);
		if ($CrateQty == '') {
			$CrateQty = $dataRow['TotalHLWeight'];
			$NoCrates = $dataRow['TotalCrates'];
		}


		$Token = $this->getToken();
		$ClientId = $Token['ClientID'];
		if ($ActionType == "AddCrate") {

			$HLWeight = $CrateQty;
			$rowClientDetails = $this->appUtilController->getClientConfig($ClientId);
			$DefaultCrate = $rowClientDetails['DefaultCrate'];
			$CrateCutoffValue = $rowClientDetails['CrateCutoffValue'];

			if($DefaultCrate == ''){
				$DefaultCrate = 27;
			}
			//Crate conversion//

			$Crates = floor($HLWeight / $DefaultCrate);
			$BalanceCrateQuantity = $HLWeight % $DefaultCrate;

			//Crate conversion//



			// if ($BalanceCrateQuantity > 8 || ($HLWeight < $DefaultCrate && $HLWeight != 0 && $HLWeight != '')) {
			if ($BalanceCrateQuantity > $CrateCutoffValue || ($HLWeight < $DefaultCrate && $HLWeight != 0 && $HLWeight != '')) {
				$NoCrates = $Crates + 1;
			} else {
				$NoCrates = $Crates;
			}
			//Crate conversion//

			$sqlCount = "SELECT distinct  MixIDs as OldMixIDs  FROM PpcHLGradingDetails WHERE LotID in ($LotID) AND CreatedFrom=1";
			$stmtCount = $this->conn->prepare($sqlCount);
			$stmtCount->execute();
			$dataRow = $stmtCount->fetch(PDO::FETCH_ASSOC);
			$OldMixIDs = $dataRow['OldMixIDs'];

			$sqlBreakUpUpdateAdd = "UPDATE `PpcHLGradingBreakup` SET `LotID`='$LotID', `VarietyID`='$VarietyID', `HLWeight`='$HLWeight', 
			`HLCount`='$HLCount', `NoCrates`='$NoCrates',`MixedID`='$OldMixIDs',`PackingGrade`='$PackingGrade',
			`Treatment`='$Treatment',`IsBlock`='$IsBlock' 
			WHERE `DataID`='$DataID'";

			$stmtBreakUpUpdateAdd = $this->conn->prepare($sqlBreakUpUpdateAdd);
			$stmtBreakUpUpdateAdd->execute();



			if ($stmtBreakUpUpdateAdd) {
				$updatedBreakup = $this->auditLogger->fetchRowById('PpcHLGradingBreakup', 'DataID', $DataID);
				$this->auditLogger->logUpdate('PpcHLGradingBreakup', $DataID, $existingBreakup, $updatedBreakup, $RequestBody, [
					'ReferenceNo' => $TrackID,
					'Remarks' => 'PreProcessing grading tracking breakup updated',
				]);
				$this->utilController->UpdateTrackWiseRawHLWeight($TrackID, $DataID, $IsBlock,$RequestBody);
				$this->utilController->UpdateTrackWiseBlockMeatWeight($TrackID,$RequestBody);
				$this->utilController->getCostingBreakUp($LotID);
				$this->Error = null;
				$this->Msg = 'Crate Added Successfully';
			}
		} else if ($ActionType == "RemoveCrate") {


			$HLWeight = $CrateQty;
			$Token = $this->getToken();
			$ClientId = $Token['ClientID'];

			$rowClientDetails = $this->appUtilController->getClientConfig($ClientId);
			$DefaultCrate = $rowClientDetails['DefaultCrate'];
			$CrateCutoffValue = $rowClientDetails['CrateCutoffValue'];

			if($DefaultCrate == ''){
				$DefaultCrate = 27;
			}
			
			$Crates = floor($HLWeight / $DefaultCrate);
			$BalanceCrateQuantity = $HLWeight % $DefaultCrate;

			//Crate conversion//


			if ($BalanceCrateQuantity > $CrateCutoffValue || ($HLWeight < $DefaultCrate && $HLWeight != 0 && $HLWeight != '')) {
				$NoCrates = $Crates + 1;
			} else {
				$NoCrates = $Crates;
			}
			//Crate conversion//

			if ($NoCrates < 0) {
				$this->Error = "No Crates Availble";
				$this->Msg = "No Crates Availble";
			} else {

				// ,`Treatment`='$Treatment',`IsBlock`='$IsBlock'
				$sqlBreakUpUpdateRemove = "UPDATE `PpcHLGradingBreakup` SET `LotID`='$LotID', `VarietyID`='$VarietyID', `HLWeight`='$HLWeight', 
				`HLCount`='$HLCount', `NoCrates`='$NoCrates' 
				WHERE `DataID`='$DataID'";

				$stmtBreakUpUpdateRemove = $this->conn->prepare($sqlBreakUpUpdateRemove);
				$stmtBreakUpUpdateRemove->execute();

				if ($stmtBreakUpUpdateRemove) {
					$updatedBreakup = $this->auditLogger->fetchRowById('PpcHLGradingBreakup', 'DataID', $DataID);
					$this->auditLogger->logUpdate('PpcHLGradingBreakup', $DataID, $existingBreakup, $updatedBreakup, $RequestBody, [
						'ReferenceNo' => $TrackID,
						'Remarks' => 'PreProcessing grading tracking breakup updated',
					]);
					$this->utilController->UpdateTrackWiseRawHLWeight($TrackID, $DataID, $IsBlock,$RequestBody);
					$this->utilController->UpdateTrackWiseBlockMeatWeight($TrackID,$RequestBody);
				    $this->utilController->getCostingBreakUp($LotID);
					$this->Error = null;
					$this->Msg = 'Crate Removed Successfully';
				}
			}
		} else {
			$this->Error = 'Unable to Update Grading BreakUp';
			$this->Msg = 'Unable to Update Grading BreakUp';
		}
	}

	public function deleteGradingBreakUP($RequestBody)
	{
		$TrackID = $RequestBody['TrackID'];
		$DataID = $RequestBody['DataBreakUpID']; // DataID as BreakUpID
		$Type = $RequestBody['Type']; // DeleteTotalGradingDetails
		$ArrivalID = $RequestBody['ArrivalID'];



		if ($Type == 'DeleteTotalGradingDetails') {
			//$existingDetails = $this->auditLogger->fetchAll("SELECT * FROM PpcHLGradingDetails WHERE MixIDs in ($ArrivalID) AND CreatedFrom=1", []);
			$sqlDelete = "DELETE FROM `PpcHLGradingDetails` WHERE MixIDs in ($ArrivalID) AND CreatedFrom=1";
			$stmtDelete = $this->conn->prepare($sqlDelete);
			$stmtDelete->execute();

			if ($stmtDelete) {
				$this->auditLogger->logDelete('PpcHLGradingDetails', $TrackID, $existingDetails, $RequestBody, [
					'ReferenceNo' => $TrackID,
					'Remarks' => 'PreProcessing grading tracking header deleted',
				]);
				$sqlBreakUpDelete = "DELETE FROM `PpcHLGradingBreakup` WHERE MixedID in ($ArrivalID) AND CreatedFrom=1";
				$stmtBreakUpDelete = $this->conn->prepare($sqlBreakUpDelete);
				$stmtBreakUpDelete->execute();
			}
		} else {
			$existingBreakup = $this->auditLogger->fetchRowById('PpcHLGradingBreakup', 'DataID', $DataID);
			$sqlDelete = "DELETE FROM `PpcHLGradingBreakup` WHERE DataID='$DataID' AND CreatedFrom=1";
			$stmtDelete = $this->conn->prepare($sqlDelete);
			$stmtDelete->execute();
			if ($stmtDelete) {
				$this->auditLogger->logDelete('PpcHLGradingBreakup', $DataID, $existingBreakup, $RequestBody, [
					'ReferenceNo' => $TrackID,
					'Remarks' => 'PreProcessing grading tracking breakup deleted',
				]);
			}
		}
		if ($stmtDelete) {
			$this->utilController->UpdateTrackWiseRawHLWeight($TrackID, '', '',$RequestBody);
			$this->utilController->UpdateTrackWiseBlockMeatWeight($TrackID,$RequestBody);
			$Data['Error'] = null;
			$Data['Msg'] = 'Row Data Deleted Successful';
			$Data['GradingDetails'] = $this->getGradingData($RequestBody);
			$Data['GradingBreakUpDetails'] = $this->getGradingBreakUp($RequestBody);
			return $Data;
		} else {
			$Data['Error'] = 'Unable to Delete';
			$Data['Msg'] = 'Unable to Delete';
			return $Data;
		}
	}

	function getReceivingCountDetails($RequestBody)
	{
		$TrackID = $RequestBody['TrackID'];
		$ArrivalID = $RequestBody['ArrivalID'];//uma
		$IsHONPacking = $RequestBody['IsHONPacking']; //sriram
		$MergeHonLoose = $RequestBody['mergeHonLoose']; //sriram

		$HonQuery = '';
		if($IsHONPacking == 1){
			$HonQuery = ' AND RRB.HONPacking = 0';
		}


		$sqlFields = " ArrivalNumber,Count,	Weight,HLID as GradingID";

		$SqlGradingCount = "SELECT  PGD.RmLotID,PHGD.MixIDs,PHGD.IsMix
 		FROM RmReceivingBreakup as RRB
		left join PpcDeheading  as PD  on RRB.ReceivalBreakupID=PD.TrackBreakUpID
		left join RmLotDetatils  as PGD  on RRB.ArrivalBreakupID=PGD.BreakUpID
        LEFT join PpcHLGradingDetails  as PHGD on PHGD.LotID=PGD.RmLotID AND PHGD.CreatedFrom=1
		where ReceivingID='$TrackID' $HonQuery AND PD.WtAfterDH != 0 -- GROUP by IFNULL(PHGD.MixIDs, PGD.RmLotID)
		GROUP BY 
        CASE 
            WHEN PHGD.MixIDs != '' AND PHGD.MixIDs != 'NA' THEN PHGD.MixIDs 
            ELSE PGD.RmLotID 
        END ";
		$stmtGradingCount = $this->conn->prepare($SqlGradingCount);
		$stmtGradingCount->execute();
		while ($dataRowGradingCount = $stmtGradingCount->fetch(PDO::FETCH_ASSOC)) {
			$IsMix = $dataRowGradingCount['IsMix'];

			$LotID = $dataRowGradingCount['RmLotID'];
			$MixIDs = $dataRowGradingCount['MixIDs'];

			$OverallDeleteAccess = 1;

			$sqlOverallDelete = "SELECT distinct TrackID,(SELECT count(*) FROM `PpcValueAddition` WHERE LotID in ($MixIDs) and TrackID=$TrackID ) as VaCount ,
		(SELECT count(*) FROM `ProdSoakingDetails` WHERE LotID in ($MixIDs) and TrackID=$TrackID ) as SoakingCount ,
		(SELECT count(*) FROM `ProdFreezingData` WHERE TrackID=$TrackID ) as FreezingCount FROM `PpcHLGradingDetails` where TrackID=$TrackID AND CreatedFrom=1";
			$stmtOverallDelete = $this->conn->prepare($sqlOverallDelete);
			$stmtOverallDelete->execute();
			$dataRowOverallDelete = $stmtOverallDelete->fetch(PDO::FETCH_ASSOC);			
			if ($dataRowOverallDelete['VaCount'] > 0 || $dataRowOverallDelete['SoakingCount'] > 0 || $dataRowOverallDelete['FreezingCount'] > 0) {
				$OverallDeleteAccess = 0;
			}



			if ($IsMix == 1) {
				//$GroupQuery = " and RHGD.MixIDs in ($MixIDs) group by MixIDs";
				$MixIDs = str_replace('_',',',$MixIDs);
				$GroupQuery = " and RHGD.MixIDs in ($MixIDs) group by MixIDs
				";
					/* if($MergeHonLoose == 1){
						$BillNumberField = ",GROUP_CONCAT(ReceivalBreakupID) as ReceivalBreakupID,GROUP_CONCAT(RmLotID)as RmArrivalID, GROUP_CONCAT(ArrivalNumber,'(',Count ,' - ',SUM(Weight),')') as BillNumber";
					}else{ */
						$BillNumberField = ",GROUP_CONCAT(ReceivalBreakupID) as ReceivalBreakupID,GROUP_CONCAT(RmLotID)as RmArrivalID, GROUP_CONCAT(ArrivalNumber,'(',Count ,' - ',Weight,')') as BillNumber";
					//}
				$gradingBreakupStatusSql = "SELECT COUNT(`DataID`) AS CNT FROM PpcHLGradingBreakup  PHGB
											LEFT JOIN PpcHLGradingDetails PHGD ON PHGD.`MixIDs`=PHGB.`MixedID` AND PHGD.`CreatedFrom`=1
											WHERE PHGB.`MixedID` IN($MixIDs) AND `TrackID`='$TrackID' AND PHGB.`CreatedFrom`=1";
			} else {
				$BillNumberField = ",ReceivalBreakupID,RmLotID as RmArrivalID";
				// $GroupQuery = " and RLD.RmLotID='$LotID' ";
				$GroupQuery = " and RLD.RmLotID in ($LotID )";
				$gradingBreakupStatusSql = "SELECT COUNT(`DataID`) AS CNT FROM PpcHLGradingBreakup  PHGB
		LEFT JOIN PpcHLGradingDetails PHGD ON PHGD.`MixIDs`=PHGB.`MixedID` AND PHGD.`CreatedFrom`=1
		WHERE PHGB.`MixedID` IN($LotID) AND `TrackID`='$TrackID' AND PHGB.`CreatedFrom`=1";

			}


		if($MergeHonLoose == 1){
			$sqlFields = " ArrivalNumber,Count,	SUM(Weight) AS Weight,HLID as GradingID";

			$GroupHonQuery = " GROUP BY RAD.ArrivalNumber";
			if($IsMix == 1){
				//$GroupHonQuery = ",RAD.ArrivalNumber";
				$GroupHonQuery = "";
			}
			$sqlQueryReceivingCounts = "SELECT $sqlFields $BillNumberField FROM RmArrivalDetatils AS RAD
				LEFT JOIN RmArrivalBreakUpDetails AS RABD ON RAD.RmArrivalID = RABD.ArrivalID 
				LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalID = RABD.ArrivalID 
			 	left join RmLotDetatils as RLD on RLD.BreakUpID=RRB.ArrivalBreakupID 
			 	left join PpcHLGradingDetails as RHGD on RHGD.LotID=RLD.RmLotID AND RHGD.CreatedFrom=1
			 	 where RRB.ReceivingID= '$TrackID' $HonQuery $GroupQuery $GroupHonQuery";
		}else{
			$sqlFields = " ArrivalNumber,Count,	Weight,HLID as GradingID";
			$sqlQueryReceivingCounts = "SELECT $sqlFields $BillNumberField FROM RmArrivalBreakUpDetails as RABD 
			  	left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RABD.ArrivalID 
				left join RmReceivingBreakup as RRB on RRB.ArrivalBreakupID=RABD.ArrivalBrkUpID 
			 	left join RmLotDetatils as RLD on RLD.BreakUpID=RRB.ArrivalBreakupID 
			 	left join PpcHLGradingDetails as RHGD on RHGD.LotID=RLD.RmLotID  AND RHGD.CreatedFrom=1
			 	 where RRB.ReceivingID= '$TrackID' $HonQuery $GroupQuery ";
		}

		
			// ORDER BY `ArrivalBrkUpID` asc
			$stmtReceivingCounts = $this->conn->prepare($sqlQueryReceivingCounts);
			$stmtReceivingCounts->execute();
			$dataRowReceivingCounts = $stmtReceivingCounts->fetch(PDO::FETCH_ASSOC);
			$ReceivingCount = array();

			// $this->addUniqueStrings($ReceivingCount,$dataRowReceivingCounts['RmArrivalID']);


			$ReceivingCount['ReceivalBreakupID'] = $dataRowReceivingCounts['ReceivalBreakupID'];
			$ReceivingCount['ArrivalID'] = $dataRowReceivingCounts['RmArrivalID'];
			if ($IsMix == 0 && $dataRowReceivingCounts['ArrivalNumber'] && $dataRowReceivingCounts['Count'] && $dataRowReceivingCounts['Weight']) {
				$ReceivingCount['BillNumber'] = $dataRowReceivingCounts['ArrivalNumber'] . '(' . $dataRowReceivingCounts['Count'] . ' - ' . $dataRowReceivingCounts['Weight'] . ')';
			} else {
				$ReceivingCount['BillNumber'] = $dataRowReceivingCounts['BillNumber'];
				$ReceivingCount['BillNumber'] = str_replace(',', ',<br>', $dataRowReceivingCounts['BillNumber']);
			}

			if ($dataRowReceivingCounts['GradingID'] > 0) {
				$ReceivingCount['GradingStatus'] = 1;
			} else {
				$ReceivingCount['GradingStatus'] = 0;
			}
			$ReceivingCount['OverallDeleteAccess'] = $OverallDeleteAccess;
			// print_r($ReceivingCount);
			$stmtGradingBreakupStatus = $this->conn->prepare($gradingBreakupStatusSql);
			$stmtGradingBreakupStatus->execute();
			$ReceivingCount['GradingBreakUpStatus'] = $stmtGradingBreakupStatus->fetch(PDO::FETCH_ASSOC)['CNT'];
			
			$ArrayReceivingCount[] = $ReceivingCount;
		}
		return $ArrayReceivingCount;
		// return $AryReceivingCount;
	}




	function getVarietyDetails($Species)
	{
		$SpeciesQry = '';
		if (!empty($Species)) {
			$SpeciesQry = "WHERE Species = '$Species'";
		}

		$sqlFields = "VarietyID,ShortName,Species";

		$sqlQueryVariety = "SELECT $sqlFields FROM PpcHLVAVarietyList $SpeciesQry";

		$stmtVariety = $this->conn->prepare($sqlQueryVariety);
		$stmtVariety->execute();
		while ($dataRowVariety = $stmtVariety->fetch(PDO::FETCH_ASSOC)) {

			$Variety['VarietyID'] = $dataRowVariety['VarietyID'];
			$Variety['ShortName'] = $dataRowVariety['ShortName'];
			$Variety['Species'] = $dataRowVariety['Species'];
			$ArrayVariety[] = $Variety;
		}
		return $ArrayVariety;
	}

	function getGrades()
	{

		// 14-06-2024 
		// $sqlFields = "Grade";
		// $sqlQueryGrade = "SELECT $sqlFields FROM ProductionGradesMaster";
		$sqlQueryGrade = "SELECT value AS Grade FROM ProductionGrades ORDER BY ABS(CAST(value AS SIGNED)) ASC";

		$stmtGrade = $this->conn->prepare($sqlQueryGrade);
		$stmtGrade->execute();
		while ($dataRowGrade = $stmtGrade->fetch(PDO::FETCH_ASSOC)) {
			$Grade['Grade'] = $dataRowGrade['Grade'];
			$ArrayGrade[] = $Grade;
		}
		return $ArrayGrade;
	}

	function getTreatment()
	{

		$sqlQueryTreatment = "SELECT ShortCode,TreatmentName FROM `ScrmTreatmentsMaster` where Status=1 ORDER BY SortOrder asc";

		$stmtTreatment = $this->conn->prepare($sqlQueryTreatment);
		$stmtTreatment->execute();
		while ($dataRowTreatment = $stmtTreatment->fetch(PDO::FETCH_ASSOC)) {
			$Treatment['ShortCode'] = $dataRowTreatment['ShortCode'];
			$Treatment['TreatmentName'] = $dataRowTreatment['TreatmentName'];
			$ArrayTreatment[] = $Treatment;
		}
		return $ArrayTreatment;
	}
	function getDeleteAccess($RequestBody)
	{
		$Query = '';
		$DeleteAccess = 1;
		$GradingBreakupID = $RequestBody['DataBreakUpID'];
		$LotID = $RequestBody['ArrivalID'];
		$TrackID = $RequestBody['TrackID'];


		$sqlIsBlock = "SELECT Treatment,IsBlock FROM `PpcHLGradingBreakup` WHERE LotID in ($LotID) and DataID='$GradingBreakupID' AND CreatedFrom=1";
		$stmtIsBlock = $this->conn->prepare($sqlIsBlock);
		$stmtIsBlock->execute();
		$dataRowIsBlock = $stmtIsBlock->fetch(PDO::FETCH_ASSOC);
		$Treatment = $dataRowIsBlock['Treatment'];
		$IsBlock = $dataRowIsBlock['IsBlock'];
		if ($Treatment != 'UNT' && $IsBlock == 1) {//Soaking

			$sqlCount = "SELECT count(*) as Count FROM `ProdSoakingDetails` WHERE LotID in ($LotID) and TrackID='$TrackID' and VABreakupID='$GradingBreakupID'";
		} else if ($Treatment == 'UNT' && $IsBlock == 1) { //Freezing

			$sqlCount = "SELECT count(*) as Count FROM `ProdFreezingData` WHERE TrackID='$TrackID' and TubNo='$GradingBreakupID'";
		} else {

			$sqlCount = "SELECT count(*) as Count  FROM `PpcValueAddition` WHERE LotID in ($LotID) and TrackID='$TrackID' and GradingBreakupID='$GradingBreakupID'";
		}
		$stmtCount = $this->conn->prepare($sqlCount);
		$stmtCount->execute();
		$dataRowCount = $stmtCount->fetch(PDO::FETCH_ASSOC);

		if ($dataRowCount['Count'] > 0) {
			$DeleteAccess = 0;
		}

		return $DeleteAccess;
	}
}
