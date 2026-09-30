<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class GradingDetailsController
{
	private $conn;
	private $db_table = "ScrmApplicants";
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

	public function getGradingDetails($RequestBody)
	{

		$ArrivalID = $RequestBody['ArrivalID'];
		$GradingData = $this->getGradingData($ArrivalID);

		$Data['Error'] = null;
		$Data['Msg'] = 'Grading Posted Successful';
		$Data['Data'] = $GradingData;
		return $Data;
	}

	public function postDeleteGradingBreakUP($RequestBody)
	{
		$ArrivalID = $RequestBody['ArrivalID'];
		$BreakUpID = $RequestBody['BreakUpID'];
		$Type = $RequestBody['Type'];

		if ($Type == 'DeleteTotalGrading') {
			$existingDetails = $this->auditLogger->fetchOne("SELECT * FROM PpcHLGradingDetails WHERE LotID = ?", [$ArrivalID]);
			$sqlQueryIsMix = "SELECT IsMix,MixIDs FROM `PpcHLGradingDetails` where LotID='$ArrivalID'";
			$stmtIsMix = $this->conn->prepare($sqlQueryIsMix);
			$stmtIsMix->execute();
			$dataRowIsMix = $stmtIsMix->fetch(PDO::FETCH_ASSOC);

			$IsMix = $dataRowIsMix['IsMix'];

			if ($IsMix == 1) {
				$MixIds = $dataRowIsMix['MixIDs'];
				$SqlGradingDetails = "delete from `PpcHLGradingDetails`  where MixIDs='$MixIds'";
				$SqlBreakup = "DELETE FROM `PpcHLGradingBreakup` WHERE MixedID='$MixIds'";
			} else {
				$SqlGradingDetails = "delete from  `PpcHLGradingDetails`  where LotID='$ArrivalID'";
				$SqlBreakup = "delete from  `PpcHLGradingBreakup`  where LotID='$ArrivalID'";
			}
			$stmtGradingDetails = $this->conn->prepare($SqlGradingDetails);
			$stmtGradingDetails->execute();

			$StmtBreakup = $this->conn->prepare($SqlBreakup);
			$StmtBreakup->execute();
			$this->auditLogger->logDelete('PpcHLGradingDetails', $ArrivalID, $existingDetails, $RequestBody, [
				'ReferenceNo' => $ArrivalID,
				'Remarks' => 'PreProcessing grading header deleted',
			]);
		} else {
			$existingBreakup = $this->auditLogger->fetchRowById('PpcHLGradingBreakup', 'DataID', $BreakUpID);
			$sqlQuery = "delete  from `PpcHLGradingBreakup` where DataID='$BreakUpID'";
			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();
			$this->auditLogger->logDelete('PpcHLGradingBreakup', $BreakUpID, $existingBreakup, $RequestBody, [
				'ReferenceNo' => $ArrivalID,
				'Remarks' => 'PreProcessing grading breakup deleted',
			]);
		}

		$ArrivalID = $RequestBody['ArrivalID'];
		$GradingData = $this->getGradingData($ArrivalID);

		$Data['Error'] = null;
		$Data['Msg'] = 'Deleted Posted Successful';
		$Data['Data'] = $GradingData;

		return $Data;
	}

	public function postGradingDetails($RequestBody)
	{

		$PostedUserID = 1;
		$ArrivalID = $RequestBody['ArrivalID'];
		$PlantID = $RequestBody['Plant'];
		$StationID = $RequestBody['Center'];
		$GradingDate = $RequestBody['TransactionDate'];
		$MachineID = $RequestBody['MachineID'];
		$StartTime = $RequestBody['StartTime'];
		$EndTime = $RequestBody['EndTime'];
		$BreakTime = $RequestBody['BreakTime'];
		$Remarks = $RequestBody['Remarks'];
		$MixIDs = $RequestBody['MixIDs'];
		$Status = $RequestBody['Status'];
		$VarietyID = $RequestBody['Variety'];
		$Grade = $RequestBody['Grade'];

		$AryArrivalID = explode(',', $ArrivalID);
		$MixIDs = $AryArrivalID[0];
		
		if (count($AryArrivalID) > 1) {
			$IsMix = 1;
		} else {
			$IsMix = 0;
		}

		$AryCurrentDate = getCurrentDateTime();

		$CreatedDateTime = $AryCurrentDate['TimeStamp'];
		$Year = $AryCurrentDate['Year'];

		if ($IsMix == 1) {
			$sqlQueryCount = "SELECT Count(*) as  Count FROM `PpcHLGradingDetails` where  MixIDs  in ($ArrivalID) or LotID  in ($ArrivalID) ";
		} else {
			$sqlQueryCount = "SELECT Count(*) as  Count FROM `PpcHLGradingDetails` where LotID='$ArrivalID'";
		}

		$stmtCount = $this->conn->prepare($sqlQueryCount);
		$stmtCount->execute();
		$dataRowCount = $stmtCount->fetch(PDO::FETCH_ASSOC);

		$Count = $dataRowCount['Count'];

		$stmt = false;

		if ($Count == 0) {

			foreach ($AryArrivalID as $CArrivalID) {
				$sqlQuery = "INSERT INTO `PpcHLGradingDetails`( `LotID`, `UserID`, `GradingDate`, `StartTime`, `EndTime`, 
				`BreakTime`, `PostedDate`, `MachineID`, `StationID`, `PlantID`,`VarietyID`, `Grade`,  `IsMix`, `Remarks`, `MixIDs`, `Status`)
				values('$CArrivalID','$PostedUserID','$GradingDate','$StartTime','$EndTime','$BreakTime','$CreatedDateTime','$MachineID',
				'$StationID','$PlantID','$VarietyID','$Grade','$IsMix','$Remarks','$MixIDs','1')";

				$stmt = $this->conn->prepare($sqlQuery);
				$stmt->execute();
				if ($stmt) {
					$lastInsertId = $this->conn->lastInsertId();
					$createdDetail = $this->auditLogger->fetchRowById('PpcHLGradingDetails', 'HLID', $lastInsertId);
					$this->auditLogger->logInsert('PpcHLGradingDetails', $lastInsertId, $createdDetail, $RequestBody, [
						'ReferenceNo' => $CArrivalID,
						'Remarks' => 'PreProcessing grading header created',
					]);
				}
			}
		} else {
			$sqlIsMix = "SELECT distinct (MixIDs) as MixIDs,IsMix  FROM `PpcHLGradingDetails`  WHERE LotID in($ArrivalID)";
			$stmtIsMix = $this->conn->prepare($sqlIsMix);
			$stmtIsMix->execute();
			while ($dataRowIsMix = $stmtIsMix->fetch(PDO::FETCH_ASSOC)) {

				$IsMix = $dataRowIsMix['IsMix'];
				$MixIDs = $dataRowIsMix['MixIDs'];

				if ($IsMix) {
					$sqlIsMixIds = "SELECT LotID , LotNumber,PurchaseCount,PurchaseWeight FROM `PpcHLGradingDetails` PHGD
					left join RmLotDetatils RLD on RLD.RmLotID=PHGD.LotID
					WHERE MixIDs in($MixIDs!=0)";
					$stmtIsMixIds = $this->conn->prepare($sqlIsMixIds);
					$stmtIsMixIds->execute();
					$dataRowIsMixIds = $stmtIsMixIds->fetch(PDO::FETCH_ASSOC);
					$ArrayArrivalIDs[] = $dataRowIsMixIds['LotID'];
					$ArrivalID = implode(',', $ArrayArrivalIDs);
					$ArrayMixIDs[] = $dataRowIsMixIds['LotNumber'] . '(' . $dataRowIsMixIds['PurchaseCount'] . 'C - ' . $dataRowIsMixIds['PurchaseWeight'] . ' Kgs)';
					$MixedIDs = implode(',', $ArrayMixIDs);
					
				}
				$Data['Error'] = null;
				$Data['Msg'] = "Mixed IDs : " . $MixedIDs;	
			}
		}


		$this->postGradingBreakUp($ArrivalID, $RequestBody);
		$RequestBody['ArrivalID'] = $ArrivalID;

		$GradingData = $this->getGradingData($ArrivalID);
		$Data['Data'] = $GradingData;
		return $Data;
	}

	function postGradingBreakUp($ArrivalID, $RequestBody)
	{

		$NoCrates = $RequestBody['NoCrates'];

		if ($NoCrates == 0) {
			$NoCrates = 1;
		}

		$LotID = $RequestBody['ArrivalID'];
		$Defect = $RequestBody['Defect'];
		$Count = $RequestBody['Count'];
		$Weight = $RequestBody['Weight'];
		$Weight = ($Weight * $NoCrates);

		$SqlGradingReport = "SELECT * FROM `PpcHLGradingDetails` where LotID='$LotID'";

		$StmtGradingReport = $this->conn->prepare($SqlGradingReport);
		$StmtGradingReport->execute();
		$GradingReportRow = $StmtGradingReport->fetch(PDO::FETCH_ASSOC);

		$IsMix = $GradingReportRow['IsMix'];
		$MixIDs = $GradingReportRow['MixIDs'];

		if ($IsMix == 1) {
			$SqlGradingReport = "SELECT * FROM `PpcHLGradingBreakup` 
			where MixedID='$MixIDs' and HLCount='$Count' ";
			$StmtGradingReport = $this->conn->prepare($SqlGradingReport);
			$StmtGradingReport->execute();
			$RowCount = $StmtGradingReport->rowCount();
		} else {
			$SqlGradingReport = "SELECT * FROM `PpcHLGradingBreakup` 
			where LotID='$LotID' and HLCount='$Count' ";
			$StmtGradingReport = $this->conn->prepare($SqlGradingReport);
			$StmtGradingReport->execute();
			$RowCount = $StmtGradingReport->rowCount();
		}


		if ($RowCount > 0) {

			$GradingReportRow = $StmtGradingReport->fetch(PDO::FETCH_ASSOC);

			$HLWeight = $GradingReportRow['HLWeight'] + $Weight;
			$NoCrates = $GradingReportRow['NoCrates'] + $NoCrates;
			$DataID = $GradingReportRow['DataID'];

			$HlGradingSql = "Update  `PpcHLGradingBreakup` set 
	 		HLWeight='$HLWeight' ,NoCrates='$NoCrates' where DataID ='$DataID' ";

			$HlGradingSqlExec = $this->conn->prepare($HlGradingSql);
			$HlGradingSqlExec->execute();
			if ($HlGradingSqlExec) {
				$updatedBreakup = $this->auditLogger->fetchRowById('PpcHLGradingBreakup', 'DataID', $DataID);
				$this->auditLogger->logUpdate('PpcHLGradingBreakup', $DataID, $GradingReportRow, $updatedBreakup, $RequestBody, [
					'ReferenceNo' => $LotID,
					'Remarks' => 'PreProcessing grading breakup updated',
				]);
			}
		} else {
			$HlGradingSql = "INSERT INTO `PpcHLGradingBreakup` 
			( `LotID`, `MixedID`, `HLWeight`, `HLCount`, `NoCrates`, `DefectID`,`PackingGrade`, `Usd`) VALUES 
			('$LotID','$MixIDs','$Weight','$Count','$NoCrates','$Defect','','')";

			$HlGradingSqlExec = $this->conn->prepare($HlGradingSql);
			$HlGradingSqlExec->execute();
			if ($HlGradingSqlExec) {
				$lastInsertId = $this->conn->lastInsertId();
				$createdBreakup = $this->auditLogger->fetchRowById('PpcHLGradingBreakup', 'DataID', $lastInsertId);
				$this->auditLogger->logInsert('PpcHLGradingBreakup', $lastInsertId, $createdBreakup, $RequestBody, [
					'ReferenceNo' => $LotID,
					'Remarks' => 'PreProcessing grading breakup created',
				]);
			}
		}

		if ($HlGradingSqlExec) {
			$Error = null;
			$Msg = 'Report Added Successful';
		} else {
			$Error = null;
			$Msg = 'Report Already Exist';
		}

		$Data['Error'] = $Error;
		$Data['Msg'] = $Msg;

		return $Data;
	}

	function getGradingData($ArrivalID)
	{


		$sqlQuery = " SELECT  * FROM `PpcHLGradingDetails` as PHGD WHERE PHGD.LotID in ('$ArrivalID')";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($dataRow['IsMix'] == 1) {
			$ArrivalID = $dataRow['MixIDs'];
		}

		$sqlFields = "MachineName,`GradingDate`,LotNumber,DataID,DefectName,HLCount,HLWeight,NoCrates,`StartTime`,PHGD.LotID as RmArrivalID,
         `EndTime`, `BreakTime`,PHGD.`StationID`, `PlantID`,PHGD.MachineID,PHGD.Remarks,StationName,UnitName,PHGD.Grade,PHGD.VarietyID";
        //  `EndTime`, `BreakTime`,PHGD.`StationID`, `PlantID`,PHGD.MachineID,PHGD.Remarks,StationName,UnitName,PHGD.Grade,VarietyID";
		$sqlQuery = " SELECT  $sqlFields FROM `PpcHLGradingDetails` as PHGD
         left join RmLotDetatils RAD on RAD.RmLotID=PHGD.LotID 
         left join PpcHLGradingBreakup as  PHGB on PHGB.LotID=PHGD.LotID
         left join RmDefectsList as RDL on RDL.DefectID=PHGB.DefectID 
         left join AdminGradingMachines as AGD on AGD.MachineID=PHGD.MachineID 
		 left join RmProcessingStations  RPS on RPS.ProcessingStationID=PHGD.PlantID
		 left join PpcVAStations  PVS on PVS.StationID=PHGD.StationID
         WHERE PHGD.LotID in ('$ArrivalID')";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$Data = array();
		$i = 0;
		$TotalWeight = 0;
		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$GradingData['SNo'] = ++$i;
			$GradingData['BreakUpID'] = $dataRow['DataID'];
			$GradingData['ArrivalID'] = $dataRow['RmArrivalID'];
			$GradingData['GradingDate'] = $dataRow['GradingDate'];
			$GradingData['TransactionDate'] = $this->utilController->getFormatedDate($dataRow['GradingDate']);
			$GradingData['LotNo'] = $dataRow['LotNumber'];
			$GradingData['Defect'] = $dataRow['DefectName'];
			$GradingData['Count'] = $dataRow['HLCount'];
			$GradingData['Weight'] = $dataRow['HLWeight'];
			$GradingData['StartTime'] = $dataRow['StartTime'];
			$GradingData['EndTime'] = $dataRow['EndTime'];
			$GradingData['BreakTime'] = $dataRow['BreakTime'];
			$GradingData['StationID'] = $dataRow['StationID'];
			$GradingData['PlantID'] = $dataRow['PlantID'];
			$GradingData['MachineID'] = $dataRow['MachineID'];
			$GradingData['MachineName'] = $dataRow['MachineName'];
			$GradingData['Plant'] = $dataRow['StationName'];
			$GradingData['Center'] = $dataRow['UnitName'];
			$GradingData['NoCrates'] = $dataRow['NoCrates'];
			$GradingData['Remarks'] = $dataRow['Remarks'];
			$GradingData['Grade'] = $dataRow['Grade'];
			$GradingData['Variety'] = $dataRow['Variety'];

			$ArrayData[] = $GradingData;
			$TotalWeight += $dataRow['HLWeight'];
		}
		$ArrayTotals['TotalWeight'] = $TotalWeight;
		$Data['GradingData'] = $ArrayData;
		$Data['Totals'] = $ArrayTotals;
		return $Data;
	}
}
