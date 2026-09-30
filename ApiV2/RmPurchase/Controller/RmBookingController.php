<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/AuditLogger.php';

class RmBookingController
{
	private $conn;
	private $db_table = "ScrmApplicants";
	private $utilController;
	private $auditLogger;
	private $Error = null;
	private $Msg = '';


	// Db connection
	public function __construct($db)
	{
		$this->conn = $db;
		$this->utilController = new UtilController($this->conn);
		$this->auditLogger = new AuditLogger($this->conn, 'Purchase');
	}

	public function getBookingDetails($RequestBody)
	{
		$BookingID = $RequestBody['BookingID'];
		$BreakUpData = $this->getBookingBreakUp($BookingID);
		$BookingData = $this->getBookingData($BookingID,$RequestBody);
		$GrandTotal = $this->getGrandTotal($BookingID);

		//var_dump($BookingData);
		$Data['Error'] = null;
		$Data['Msg'] = 'Booking Posted Successful';
		$Data['BreakUpData'] = $BreakUpData;
		$Data['BookingData'] = $BookingData;
		$Data['GrandTotal'] = $GrandTotal;

		return $Data;
	}
	public function postDeleteBookingBrkUP($RequestBody)
	{
		$BookingID = $RequestBody['BookingID'];
		$BreakUpID = $RequestBody['BreakUpID'];
		$existingBreakUp = $this->auditLogger->fetchRowById('RmBookingBreakUpDetails', 'BookingBrkUpID', $BreakUpID);


		$sqlQuery = "delete  from `RmBookingBreakUpDetails` where BookingBrkUpID='$BreakUpID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		if ($stmt && $existingBreakUp != null) {
			$this->auditLogger->logDelete('RmBookingBreakUpDetails', $BreakUpID, $existingBreakUp, $RequestBody, [
				'ReferenceNo' => $BookingID,
				'Remarks' => 'Booking breakup deleted',
			]);
		}


		$BreakUpData = $this->getBookingBreakUp($BookingID);
		$BookingData = $this->getBookingData($BookingID,$RequestBody);

		//var_dump($BookingData);
		$Data['Error'] = null;
		$Data['Msg'] = 'BreakUp Deleted Successful';
		$Data['BreakUpData'] = $BreakUpData;
		$Data['BookingData'] = $BookingData;

		return $Data;
	}

	public function postBookingDetails($RequestBody)
	{
		$PostedUserID = 1;
		$BookingNumber = $RequestBody['BookingNumber'];
		$BookingStationID = $RequestBody['BookingStationID'];
		$VehicalID = $RequestBody['VehicalID'];
		$AgentID = $RequestBody['AgentID'];
		$DriverID = $RequestBody['DriverID'];
		$GraderID = $RequestBody['GraderID'];
		$BookingID = $RequestBody['BookingID'];
		$Species = $RequestBody['Species'];
		$RmSupplierID = $RequestBody['RmSupplierID'];
		$Extra = $RequestBody['Extra'];
		$BookingDayWise = $RequestBody['BookingDayWise'];

		$PurchaseCategoryID = $RequestBody['PurchaseCategoryID'];

		$FarmAddress = $RequestBody['FarmAddress'];
		$Remarks = $RequestBody['Remarks'];
		$BookingDate = date("Y-m-d", strtotime($RequestBody['BookingDate']));

		$SampledBy = $RequestBody['SampledBy'];
		$TagId = $RequestBody['TagId'];
		$SampleStatus = $RequestBody['SampleStatus'];

		$BookingNoAval = $this->utilController->checkBookingNoAval($BookingDate, $BookingNumber, $BookingID,$BookingDayWise);

		if (!$BookingNoAval) {
			$this->Error = "Booking Number Already Exist";
		}
		$AryCurrentDate = getCurrentDateTime();

		$CreatedDateTime = $AryCurrentDate['TimeStamp'];
		$Year = $AryCurrentDate['Year'];

		$stmt = false;
		$HarvestFormNewDesignForSME = !empty($RequestBody["HarvestFormNewDesignForSME"])?$RequestBody["HarvestFormNewDesignForSME"]:'';//SMEV3
		$insertColmn = $insertVal = $updateQry = '';
		if($HarvestFormNewDesignForSME == 1){
			$insertColmn = ',SampledBy,TagId,SampleStatus';
			$insertVal = ",'$SampledBy','$TagId','$SampleStatus'";
			$updateQry = ",`SampledBy`='$SampledBy',`TagId`='$TagId',`SampleStatus`='$SampleStatus'";
		}

		if ($this->Error == null) {
			if ($BookingID == '') {
				$sqlQuery = "INSERT INTO `RmBookingDetatils`
				(`PostedUserID`, `Species`,`BookingNumber`, 
				`BookingStationID`, `VehicalID`, `AgentID`,
				`DriverID`, `GraderID`, `PurchaseCategoryID`,`RmSupplierID`,`Extra`,
			    `FarmAddress`,`Remarks`,`CreatedDateTime`, `BookingDate`, 
				`LotStatus` $insertColmn) VALUES  
				('$PostedUserID','$Species','$BookingNumber',	
				'$BookingStationID','$VehicalID','$AgentID',
				'$DriverID','$GraderID',
				'$PurchaseCategoryID','$RmSupplierID','$Extra',
				'$FarmAddress','$Remarks','$CreatedDateTime','$BookingDate',
				'1' $insertVal)";

				$stmt = $this->conn->prepare($sqlQuery);
				$stmt->execute();
				$BookingID = $this->conn->lastInsertId();
				if ($stmt) {
					$newBookingData = $this->getBookingData($BookingID, $RequestBody);
					$this->auditLogger->logInsert('RmBookingDetatils', $BookingID, $newBookingData, $RequestBody, [
						'ReferenceNo' => $BookingNumber,
						'Remarks' => 'Booking created',
					]);
				}
			} else if ($BookingID != '') {
				$existingBookingData = $this->getBookingData($BookingID, $RequestBody);
				$sqlQuery = "Update `RmBookingDetatils` set
				 `RmSupplierID`='$RmSupplierID',
				 `Extra`='$Extra',
				 `BookingNumber`='$BookingNumber',
				 `Species`='$Species',
				`BookingStationID`='$BookingStationID',
				 `VehicalID`='$VehicalID',
				 `Remarks`='$Remarks',
				 `AgentID`='$AgentID',
				 `DriverID`='$DriverID',
				`GraderID`='$GraderID',
				`PurchaseCategoryID`='$PurchaseCategoryID',
			    `FarmAddress`='$FarmAddress',
				`CreatedDateTime`='$CreatedDateTime',
				`BookingDate`='$BookingDate', 
				`IsEdited`=1 $updateQry where 	RmBookingID ='$BookingID'";

				$stmt = $this->conn->prepare($sqlQuery);
				$stmt->execute();
				if ($stmt) {
					$updatedBookingData = $this->getBookingData($BookingID, $RequestBody);
					$this->auditLogger->logUpdate('RmBookingDetatils', $BookingID, $existingBookingData, $updatedBookingData, $RequestBody, [
						'ReferenceNo' => $BookingNumber,
						'Remarks' => 'Booking updated',
					]);
				}
			}
		}

		if ($stmt) {

			$this->postBookingBreakUp($BookingID, $RequestBody);
			$RequestBody['BookingID'] = $BookingID;
			return $this->getBookingDetails($RequestBody);
		} else {
			return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
		}
	}

	function postBookingBreakUp($BookingID, $RequestBody)
	{

		$BookingCount = 0;
		$BookingWeight = 0;
		$BookingRate = 0;

		if (isset($RequestBody['BookingCount'])) {
			$BookingCount = $RequestBody['BookingCount'];
		}
		if (isset($RequestBody['BookingWeight'])) {
			$BookingWeight = $RequestBody['BookingWeight'];
		}
		if (isset($RequestBody['BookingRate'])) {
			$BookingRate = $RequestBody['BookingRate'];
		}

		if ($BookingCount > 0 && $BookingWeight > 0 && $BookingRate > 0) {

			$sqlQuery = "INSERT INTO `RmBookingBreakUpDetails`
				( `BookingID`, `Count`, `Weight`, `Rate`) VALUES  
				('$BookingID',' $BookingCount','$BookingWeight','$BookingRate')";

			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();
			if ($stmt) {
				$breakUpId = $this->conn->lastInsertId();
				$newBreakUpData = $this->auditLogger->fetchRowById('RmBookingBreakUpDetails', 'BookingBrkUpID', $breakUpId);
				$this->auditLogger->logInsert('RmBookingBreakUpDetails', $breakUpId, $newBreakUpData, $RequestBody, [
					'ReferenceNo' => $BookingID,
					'Remarks' => 'Booking breakup created',
				]);
			}
		}
	}


	function getBookingData($BookingId, $RequestBody = [])
	{
		$HarvestFormNewDesignForSME = !empty($RequestBody["HarvestFormNewDesignForSME"])?$RequestBody["HarvestFormNewDesignForSME"]:'';//SMEV3
		$queryFilds = '';
		if($HarvestFormNewDesignForSME == 1){
			$queryFilds = ',SampledBy,TagId,SampleStatus';
		}
		$dataRow = null;
		$sqlFields = "`RmBookingID`,`Species`, `PostedUserID`,Remarks, `BookingNumber`,`RmSupplierID`,`Extra`, `BookingStationID`, `VehicalID`, `AgentID`, `DriverID`, `GraderID`, `PurchaseCategoryID`, `FarmAddress`, `CreatedDateTime`, `BookingDate`, `IsEdited`, `LotStatus`
						$queryFilds";
		$sqlQuery = "SELECT  $sqlFields FROM `RmBookingDetatils` WHERE  RmBookingID='$BookingId'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();

		// $errorInfo = $stmt->errorInfo();
		// var_dump($errorInfo);
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		return $dataRow;
	}

	function getBookingBreakUp($BookingId)
	{
		$Data = array();
		$sqlFields = "`BookingBrkUpID`, `BookingID`, `Count`, `Weight`, `Rate`, `BrkUpStatus`";
		$sqlQuery = "select $sqlFields  from `RmBookingBreakUpDetails` where BookingID='$BookingId'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$dataRow['BreakupTotal'] = round($dataRow['Weight'] * $dataRow['Rate'], 2);
			$Data[] = $dataRow;
		}

		return $Data;
	}


	function getGrandTotal($BookingId)
	{
		$Data = array();
		$sqlFields = "sum(Weight) as WeightTotal ,sum(Weight*Rate) as ValueTotal";
		$sqlQuery = "select $sqlFields  from `RmBookingBreakUpDetails` where BookingID='$BookingId'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

		return $dataRow;
	}
}
