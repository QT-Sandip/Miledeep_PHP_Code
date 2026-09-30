<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class CreatePpcStationController
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

	public function postPpcStationsDetails($RequestBody)
	{
		$StationID = $RequestBody['StationID'];

		if ($StationID == '') {
			// echo 'hello insert';
			$Data = $this->insertPpcStationsDetails($RequestBody);
		} else {
			// echo 'Hello Update';
			$Data = $this->updatePpcStationsDetails($RequestBody);
		}

		return $Data;
	}

	public function insertPpcStationsDetails($RequestBody)
	{
		$StationID = $RequestBody['StationID'];
		$UnitName = $RequestBody['UnitName'];
		$UnitLocation = $RequestBody['UnitLocation'];
		$UnitStatus = $RequestBody['UnitStatus'];
		$ExtraPercPerKgs = $RequestBody['ExtraPercPerKgs'];
		$ExtraAmountPerPerson = $RequestBody['ExtraAmountPerPerson'];

		$existingRecord = $this->checkDuplicateUnitName($UnitName); //abe

		if (!$existingRecord) { //abc

			// echo
			$sqlInsert = "INSERT INTO PpcVAStations(StationID, UnitName, UnitLocation, UnitStatus, ExtraPercPerKgs, ExtraAmountPerPerson) 
			VALUES ('$StationID', '$UnitName', '$UnitLocation', '$UnitStatus', '$ExtraPercPerKgs', '$ExtraAmountPerPerson')";

			$stmtInsert = $this->conn->prepare($sqlInsert);
			$stmtInsert->execute();

			if ($stmtInsert) {

				$lastInsertedId = $this->conn->lastInsertId();
				$RequestBody['StationID'] = $lastInsertedId;
				$createdStation = $this->auditLogger->fetchRowById('PpcVAStations', 'StationID', $lastInsertedId);
				$this->auditLogger->logInsert('PpcVAStations', $lastInsertedId, $createdStation, $RequestBody, [
					'ReferenceNo' => $lastInsertedId,
					'Remarks' => 'PreProcessing VA station created',
				]);
				// echo $lastInsertedId;

				$StationData = $this->getPpcStationDetails($RequestBody);
				$Data['Error'] = null;
				$Data['Msg'] = 'Insert Success';
				$Data['Data'] = $StationData;
				
				return $Data;
			} else {
				$Data['Error'] = 'Unable to Insert Station';
				$Data['Msg'] = $this->Error;

				return $Data;
			}
		} else {
			$Data['Error'] = 'Station already exists';
			$Data['Msg'] = $this->Error;
			return $Data;
		}
	}

	public function updatePpcStationsDetails($RequestBody)
	{
		$StationID = $RequestBody['StationID'];
		$UnitName = $RequestBody['UnitName'];
		$UnitLocation = $RequestBody['UnitLocation'];
		$UnitStatus = $RequestBody['UnitStatus'];
		$ExtraPercPerKgs = $RequestBody['ExtraPercPerKgs'];
		$ExtraAmountPerPerson = $RequestBody['ExtraAmountPerPerson'];
		$existingStation = $this->auditLogger->fetchRowById('PpcVAStations', 'StationID', $StationID);

		$existingUpdateRecord = $this->checkDuplicateUpdateUnitName($UnitName, $StationID);

		if (!$existingUpdateRecord) {

			$sqlUpdate = "UPDATE `PpcVAStations` SET `UnitName`='$UnitName', `UnitLocation`='$UnitLocation',`UnitStatus`='$UnitStatus',
			`ExtraPercPerKgs`='$ExtraPercPerKgs',`ExtraAmountPerPerson`='$ExtraAmountPerPerson' WHERE `StationID`='$StationID'";
	
			$stmtUpdate = $this->conn->prepare($sqlUpdate);
			$stmtUpdate->execute();

			if ($stmtUpdate) {
				$updatedStation = $this->auditLogger->fetchRowById('PpcVAStations', 'StationID', $StationID);
				$this->auditLogger->logUpdate('PpcVAStations', $StationID, $existingStation, $updatedStation, $RequestBody, [
					'ReferenceNo' => $StationID,
					'Remarks' => 'PreProcessing VA station updated',
				]);
				$StationData = $this->getPpcStationDetails($RequestBody);
				$Data['Error'] = null;
				$Data['Msg'] = 'Update Success';
				$Data['Data'] = $StationData;

				return $Data;
			} else {
				$Data['Error'] = 'Unable to update Station';
				$Data['Msg'] = $this->Error;

				return $Data;
			}
		} else {
			$Data['Error'] = 'Unit Name already exists';
			$Data['Msg'] = $this->Error;
			return $Data;
		}
	}

	public function checkDuplicateUnitName($UnitName)
	{
		$sqlCheck = "SELECT StationID FROM PpcVAStations WHERE UnitName = :UnitName";
		$stmtCheck = $this->conn->prepare($sqlCheck);
		$stmtCheck->bindParam(':UnitName', $UnitName, PDO::PARAM_STR);
		$stmtCheck->execute();

		$existingRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);

		return $existingRecord;
	}

	public function checkDuplicateUpdateUnitName($UnitName, $StationID)
	{
		$sqlCheck = "SELECT StationID FROM PpcVAStations WHERE UnitName = :UnitName AND StationID != :StationID";
		$stmtCheck = $this->conn->prepare($sqlCheck);
		$stmtCheck->bindParam(':UnitName', $UnitName, PDO::PARAM_STR);
		$stmtCheck->bindParam(':StationID', $StationID, PDO::PARAM_INT);
		$stmtCheck->execute();

		$existingUpdateRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);

		return $existingUpdateRecord;
	}

	function getPpcStationDetails($RequestBody)
	{
		$StationID = $RequestBody['StationID'];

		if ($StationID != '') {
			$sqlQuery = "SELECT * FROM `PpcVAStations` WHERE StationID='$StationID'";
		} else {
			$sqlQuery = "SELECT  * FROM `PpcVAStations`";
		}
		$stmtSelect = $this->conn->prepare($sqlQuery);
		$stmtSelect->execute();

		$i = 0;
		while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
			$StationData['SNo'] = ++$i;
			$StationData['StationID'] = $dataRow['StationID'];
			$StationData['UnitName'] = $dataRow['UnitName'];
			$StationData['UnitLocation'] = $dataRow['UnitLocation'];
			$StationData['UnitStatus'] = $dataRow['UnitStatus'];
			$StationData['ExtraPercPerKgs'] = $dataRow['ExtraPercPerKgs'];
			$StationData['ExtraAmountPerPerson'] = $dataRow['ExtraAmountPerPerson'];

			$ArrayStationData[] =  $StationData;
		}

		$data['Data'] = $ArrayStationData;

		return $data;
	}
}
