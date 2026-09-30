<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once '../App/Controller/AppUtilController.php';
include_once __DIR__ . '/AuditLogger.php';

$ArrivalAutoGeneration = $ClientConfigData->ArrivalAutoGeneration;

class RmArrivalController
{
	private $conn;
	private $db_table = "ScrmApplicants";
	private $utilController;
	private $appController;
	private $auditLogger;
	private $Error = null;
	private $Msg = '';
	private $showAccess = "1";




	// Db connection
	public function __construct($db)
	{
		$this->conn = $db;
		$this->utilController = new UtilController($this->conn);
		$this->appController = new AppUtilController($this->conn);
		$this->auditLogger = new AuditLogger($this->conn, 'Purchase');
	}

	public function getArrivalDetails($RequestBody)
	{

		$ArrivalID = $RequestBody['ArrivalID'];
		$ClientID = $RequestBody['ClientID']; // uma 20-04-2024


		if ($ArrivalID == '') {
			$BillDate = $RequestBody['BillDate'];
			$ArrivalStationID = $RequestBody['ArrivalStationID'];

			if ($ArrivalStationID != '' && $BillDate != '') {
				// $ArrivalNumberData = $this->utilController->getFormatedArrivalNumber($ArrivalStationID, $BillDate, null);
				$ArrivalNumberData = $this->utilController->getFormatedArrivalNumber($ArrivalStationID, $BillDate, $ClientID, null); // uma 20-04-2024
				$Data['ArrivalNumberData'] = isset($ArrivalNumberData) ? $ArrivalNumberData : 0;
			}
		} else {

			// $ArrivalNumberData = $this->utilController->getFormatedArrivalNumber($ArrivalStationID, $BillDate);					
			//  $Data['ArrivalNumberData'] = $ArrivalNumberData;
			$BreakUpData = $this->getArrivalBreakUp($ArrivalID);
			$ArrivalData = $this->getArrivalData($ArrivalID);
			$GrandTotal = $this->getGrandTotal($ArrivalID);
			$overAllData = $this->getOverAllArrival($ArrivalID);


			$ArrivalData['OverAllWeight'] = $overAllData['OverAllWeight'];
			$ArrivalData['OverAllAmount'] = $overAllData['OverAllAmount'];
			$ArrivalData['OverAllBalWeight'] = $overAllData['OverAllBalWeight'];
			$ArrivalData['OverAllBalAmount'] = $overAllData['OverAllBalAmount'];

			$Data['Error'] = null;
			$Data['Msg'] = 'Arrival Posted Successful';
			$Data['BreakUpData'] = $BreakUpData;
			$Data['ArrivalData'] = $ArrivalData;
			$Data['GrandTotal'] = $GrandTotal;
			$Data['ArrivalNumberData'] = 0;
		}
		$Data['ShowAccess'] = $this->showAccess;
		return $Data;
	}

	public function postDeleteArrivalBrkUP($RequestBody)
	{
		$ArrivalID = $RequestBody['ArrivalID'];
		$BreakUpID = $RequestBody['BreakUpID'];
		$ClientID = $RequestBody['ClientID'];
		$ConsolidateLotCount = $this->appController->getClientConfig($ClientID)['ConsolidateLotCount'];


		$sqlCQuery = "select Count from  `RmArrivalBreakUpDetails` where ArrivalBrkUpID='$BreakUpID'";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		$dataRow = $stmtC->fetch(PDO::FETCH_ASSOC);
		$ArrivalCount = $dataRow['Count'];
		$existingBreakup = $this->auditLogger->fetchRowById('RmArrivalBreakUpDetails', 'ArrivalBrkUpID', $BreakUpID);

		$sqlQuery = "delete  from `RmArrivalBreakUpDetails` where ArrivalBrkUpID='$BreakUpID'";
		$stmtDelete = $this->conn->prepare($sqlQuery);
		$stmtDelete->execute();
		if ($stmtDelete && $existingBreakup != null) {
			$this->auditLogger->logDelete('RmArrivalBreakUpDetails', $BreakUpID, $existingBreakup, $RequestBody, [
				'ReferenceNo' => $ArrivalID,
				'Remarks' => 'Arrival breakup deleted',
			]);
		}

		if ($stmtDelete) {

			$AryupdateArrivalBillAmountandTds = $this->utilController->updateArrivalBillAmountandTds($ArrivalID);
			$AryPostpanwiseBillAmount = $this->postPanwiseData($ArrivalID); //uma 23-05-2024 Tds calculation purpose


		}

		/*$sqlQuery = "delete  from  `RmLotDetatils` where RmReferenceID='$ArrivalID' and BreakUpID='$BreakUpID'";
																		  $stmt = $this->conn->prepare($sqlQuery);
																		  $stmt->execute(); 
																		  */
		// $this->getRmBreakupGroup($ArrivalID, $ArrivalCount);
		$this->getRmBreakupGroup($ArrivalID, $ArrivalCount, $BreakUpID, $ConsolidateLotCount);


		$sqlQuery = "select * from  `RmArrivalBreakUpDetails` where ArrivalID='$ArrivalID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();



		if ($stmt->rowCount() == 0) {
			$existingArrival = $this->auditLogger->fetchRowById('RmArrivalDetatils', 'RmArrivalID', $ArrivalID);
			$sqlQuery = "delete  from `RmArrivalDetatils` where RmArrivalID='$ArrivalID'";
			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();
			if ($stmt && $existingArrival != null) {
				$this->auditLogger->logDelete('RmArrivalDetatils', $ArrivalID, $existingArrival, $RequestBody, [
					'ReferenceNo' => isset($existingArrival['ArrivalNumber']) ? $existingArrival['ArrivalNumber'] : (string) $ArrivalID,
					'Remarks' => 'Arrival deleted after last breakup removal',
				]);
			}

			////uma 23-05-2024 Tds calculation purpose//
			$sqlDeletePanWiseSuppliedAmount = "delete  from `RmPanWiseSuppliedAmount` where PanwiseArrivalID='$ArrivalID'";
			$stmtDeletePanWiseSuppliedAmount = $this->conn->prepare($sqlDeletePanWiseSuppliedAmount);
			$stmtDeletePanWiseSuppliedAmount->execute();
			//uma 23-05-2024 Tds calculation purpose
		}


		$ArrivalID = $RequestBody['ArrivalID'];
		$BreakUpData = $this->getArrivalBreakUp($ArrivalID);
		$ArrivalData = $this->getArrivalData($ArrivalID);
		$GrandTotal = $this->getGrandTotal($ArrivalID);
		$overAllData = $this->getOverAllArrival($ArrivalID);


		$ArrivalData['OverAllWeight'] = $overAllData['OverAllWeight'];
		$ArrivalData['OverAllAmount'] = $overAllData['OverAllAmount'];
		$ArrivalData['OverAllBalWeight'] = $overAllData['OverAllBalWeight'];
		$ArrivalData['OverAllBalAmount'] = $overAllData['OverAllBalAmount'];


		$Data['Error'] = null;
		$Data['Msg'] = 'Deleted Posted Successful';
		$Data['BreakUpData'] = $BreakUpData;
		$Data['ArrivalData'] = $ArrivalData;

		return $Data;
	}


	public function postArrivalDetails($RequestBody)
	{

		//var_dump($RequestBody);
		$ClientID = $RequestBody['ClientID'];
		$PostedUserID = 1;
		$ArrivalNumber = $RequestBody['ArrivalNumber'];
		$ArrivalStationID = $RequestBody['ArrivalStationID'];
		$VehicalID = $RequestBody['VehicalID'];
		$AgentID = $RequestBody['AgentID'];
		$DriverID = $RequestBody['DriverID'];
		$GraderID = $RequestBody['GraderID'];
		$SupevisorID = $RequestBody['SupervisorID'];
		$ArrivalID = $RequestBody['ArrivalID'];
		$Species = $RequestBody['Species'];
		$RmSupplierID = $RequestBody['RmSupplierID'];
		$ProcessingStationID = $RequestBody['ProcessingStationID'];
		$Extra = $RequestBody['Extra'];
		$HarvestCommission = $RequestBody['HarvestCommission'];

		$PurchaseCategoryID = $RequestBody['PurchaseCategoryID'];

		$FarmAddress = $RequestBody['FarmAddress'];
		$Remarks = $RequestBody['Remarks'];
		$ArrivalDate = date("Y-m-d", strtotime($RequestBody['ArrivalDate']));
		$BillDate = date("Y-m-d", strtotime($RequestBody['BillDate']));
		$ArrivalNumber = $RequestBody['ArrivalNumber']; //uma
		$AutoArrivalReferenceNo = $RequestBody['AutoArrivalReferenceNo']; //uma

		$ConfigData = $this->appController->getClientConfig($ClientID);
		$ArrivalAutoGeneration = $ConfigData['ArrivalAutoGeneration'];

		if ($ArrivalAutoGeneration == 0) {
			$OriginalNumber = $AutoArrivalReferenceNo;
			$AutoArrivalReferenceNo = substr($OriginalNumber, 3, 4);
			$ArrivalNumber = $ArrivalNumber;
		} else {
			$ArrivalNumber = $this->utilController->getFormatedArrivalNumber($ArrivalStationID, $BillDate, $ClientID, $AutoArrivalReferenceNo)['ArrivalNumber'];
		}
		$ArrivalNoAval = $this->utilController->checkArrivalNoAval($BillDate, $ArrivalNumber, $ArrivalID, $ArrivalStationID);

		if (!$ArrivalNoAval) {
			$this->Error = "Arrival Number Already Exist";
			//echo "hi not";
		}

		$Proceed = $this->utilController->checkVehicleIsUsedWithSameDateAndAnotherPlant($VehicalID, $ArrivalDate, $ProcessingStationID);
		if (!$Proceed) {
			$this->Error = "This vehicle is already being used in another plant on the same date.";
		}

		$AryCurrentDate = getCurrentDateTime();

		$CreatedDateTime = $AryCurrentDate['TimeStamp'];
		$Year = $AryCurrentDate['Year'];

		$stmt = false;

		if ($this->Error == null && $ArrivalID == '') {
			$sqlQuery = "INSERT INTO `RmArrivalDetatils`
				(`PostedUserID`, `Species`,`AutoArrivalReferenceNo`,`ArrivalNumber`,`ProcessingStationID`, 
				`ArrivalStationID`, `VehicalID`, `AgentID`,
				`DriverID`, `GraderID`, `SupervisorID`, `PurchaseCategoryID`,`RmSupplierID`,`Extra`,
			    `FarmAddress`,`Remarks`,`CreatedDateTime`, `ArrivalDate`, 
				`LotStatus`,`HarvestCommission`,`BillDate`) VALUES  
				('$PostedUserID','$Species','$AutoArrivalReferenceNo','$ArrivalNumber','$ProcessingStationID',	
				'$ArrivalStationID','$VehicalID','$AgentID',
				'$DriverID','$GraderID', '$SupevisorID',
				'$PurchaseCategoryID','$RmSupplierID','$Extra',
				'$FarmAddress','$Remarks','$CreatedDateTime','$ArrivalDate',
				'1','$HarvestCommission','$BillDate')";

			// echo $sqlQuery;exit;
			//error_log("⚠️ RmArrivalDetatils Query: " . print_r($sqlQuery, true));
			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();
			$ArrivalID = $this->conn->lastInsertId();
			if ($stmt) {
				$newArrival = $this->auditLogger->fetchRowById('RmArrivalDetatils', 'RmArrivalID', $ArrivalID);
				$this->auditLogger->logInsert('RmArrivalDetatils', $ArrivalID, $newArrival, $RequestBody, [
					'ReferenceNo' => $ArrivalNumber,
					'Remarks' => 'Arrival created',
				]);
			}
		} else if ($this->Error == null && $ArrivalID != '') {
			$existingArrival = $this->auditLogger->fetchRowById('RmArrivalDetatils', 'RmArrivalID', $ArrivalID);
			$OLdArrivalNumber =$this->CheckArrivalNumberUpdate($ArrivalNumber,$ArrivalID);

			// `ArrivalNumber`='$ArrivalNumber',  because Auto Number
			$sqlQuery = "Update `RmArrivalDetatils` set
				`RmSupplierID`='$RmSupplierID',
				`Extra`='$Extra',
				`ArrivalNumber`='$ArrivalNumber',
				`AutoArrivalReferenceNo`='$AutoArrivalReferenceNo',
				 `ProcessingStationID`='$ProcessingStationID',
				 `Species`='$Species',
				`ArrivalStationID`='$ArrivalStationID',
				 `VehicalID`='$VehicalID',
				 `Remarks`='$Remarks',
				 `AgentID`='$AgentID',
				 `DriverID`='$DriverID',
				`GraderID`='$GraderID',
				`SupervisorID`='$SupevisorID',
				`PurchaseCategoryID`='$PurchaseCategoryID',
			    `FarmAddress`='$FarmAddress',
				`CreatedDateTime`='$CreatedDateTime',
				`ArrivalDate`='$ArrivalDate', 
				`HarvestCommission`='$HarvestCommission', 
				`BillDate`='$BillDate', 
				`IsEdited`=1 where 	RmArrivalID ='$ArrivalID'";

			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();
			if ($stmt) {
				$updatedArrival = $this->auditLogger->fetchRowById('RmArrivalDetatils', 'RmArrivalID', $ArrivalID);
				$this->auditLogger->logUpdate('RmArrivalDetatils', $ArrivalID, $existingArrival, $updatedArrival, $RequestBody, [
					'ReferenceNo' => $ArrivalNumber,
					'Remarks' => 'Arrival updated',
				]);
			}
		}



		if ($stmt) {
			if(!empty($OLdArrivalNumber) && $OLdArrivalNumber != $ArrivalNumber){
				$updateLotSql = "UPDATE RmLotDetatils SET LotNumber = '$ArrivalNumber' WHERE RmReferenceID='$ArrivalID' AND LotNumber != '$ArrivalNumber'";
				$stmtUpdateLot = $this->conn->prepare($updateLotSql);
				$stmtUpdateLot->execute();
			}

			$this->postArrivalBreakUp($ArrivalID, $RequestBody);
			if ($this->Error == null) {
				$RequestBody['ArrivalID'] = $ArrivalID;
				$BreakUpData = $this->getArrivalDetails($RequestBody);
				return $this->getArrivalDetails($RequestBody);
			} else {
				return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
			}
		} else {
			return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
		}
	}

	// function getRmBreakupGroup($ArrivalID, $ArrivalCount)
	function getRmBreakupGroup($ArrivalID, $ArrivalCount, $BreakUpID, $ConsolidateLotCount)
	{
		// $sqlQuery = "SELECT GROUP_CONCAT(ArrivalBrkUpID) as BreakUp,sum(Weight) as Weight,Count FROM `RmArrivalBreakUpDetails` 
		//  WHERE ArrivalID='$ArrivalID' and Count='$ArrivalCount' GROUP by Count";
		$Query = '';
		$RmLotQuery = '';

		if ($ConsolidateLotCount == 0) {
			$Query .= " and ArrivalBrkUpID='$BreakUpID'";
			$RmLotQuery .= " and BreakUpID='$BreakUpID'";
		}

		$sqlQuery = "SELECT GROUP_CONCAT(ArrivalBrkUpID) as BreakUp,sum(Weight) as Weight,Count FROM `RmArrivalBreakUpDetails` 
		 WHERE ArrivalID='$ArrivalID' and Count='$ArrivalCount' $Query GROUP by Count";


		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();

		if ($stmt->rowCount() == 0) {
			//delete rmlotId in that count 

			// $sqlRmLotDelete = "delete  FROM `RmLotDetatils` WHERE RmReferenceID='$ArrivalID' and PurchaseCount='$ArrivalCount'"; //commented by uma
			$sqlRmLotDelete = "delete  FROM `RmLotDetatils` WHERE RmReferenceID='$ArrivalID' and PurchaseCount='$ArrivalCount' $RmLotQuery";
			$stmtRmDelete = $this->conn->prepare($sqlRmLotDelete);
			$stmtRmDelete->execute();
		} else {
			//Update
			$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
			$Weight = $dataRow['Weight'];
			$Count = round($dataRow['Count'], 2);

			// $SqlPostLot = "update `RmLotDetatils` 
			// set `PurchaseWeight`='$Weight' where `RmReferenceID`='$ArrivalID' and `PurchaseCount`='$Count'  "; //commented by uma

			$SqlPostLot = "update `RmLotDetatils` 
			set `PurchaseWeight`='$Weight' where `RmReferenceID`='$ArrivalID' and `PurchaseCount`='$Count' $RmLotQuery ";

			$stmt = $this->conn->prepare($SqlPostLot);
			$stmt->execute();
		}
		$stmt->execute();
		//$Data = array();
	}

	function postArrivalBreakUp($ArrivalID, $RequestBody)
	{


		$ClientID = $RequestBody['ClientID'];
		$Variety = $RequestBody['Variety'];
		$ArrivalCount = $RequestBody['ArrivalCount'];
		$ArrivalWeight = $RequestBody['ArrivalWeight'];
		$ArrivalRate = $RequestBody['ArrivalRate'];
		$ArrivalDate = $RequestBody['ArrivalDate'];
		$Species = $RequestBody['Species'];
		$Extra = $RequestBody['Extra'];
		$ArrivalBrkUpID = $RequestBody['ArrivalBrkUpID'];
		$Species = $RequestBody['Species'];
		$BillDate = $RequestBody['BillDate'];
		$ArrivalData = $this->getArrivalData($ArrivalID);
		$CenterID = $ArrivalData['ArrivalStationID'];

		$AutoArrivalPrice = $this->appController->getClientConfig($ClientID)['AutoArrivalPrice'];
		$AryPriceData = $this->utilController->getRmPrice($Species, $BillDate, $ArrivalCount, $CenterID);
		if ($AutoArrivalPrice == 1) {
			$MasterRate = isset($AryPriceData['RmPrice']) ? $AryPriceData['RmPrice'] : 1;
			if ($Variety != 'Head On') {
				$MasterRate = $this->utilController->getDefectPrice($Species, $Variety, $MasterRate);
			}
			$RequestBody['ArrivalRate'] = $MasterRate;
		}


		$ArrivalCount = 0;
		$ArrivalWeight = 0;
		$ArrivalRate = 0;
		if (isset($RequestBody['Variety'])) {
			$Variety = $RequestBody['Variety'];
		}
		if (isset($RequestBody['ArrivalCount'])) {
			$ArrivalCount = $RequestBody['ArrivalCount'];
		}
		if (isset($RequestBody['ArrivalWeight'])) {
			$ArrivalWeight = $RequestBody['ArrivalWeight'];
		}
		if (isset($RequestBody['ArrivalRate'])) {
			$ArrivalRate = $RequestBody['ArrivalRate'];
		}

		// echo $ArrivalRate; 

		if ($ArrivalCount > 0 && $ArrivalWeight > 0 && $ArrivalRate > 0) {


			if (isset($ArrivalBrkUpID) && $ArrivalBrkUpID != '') {
				$existingBreakup = $this->auditLogger->fetchRowById('RmArrivalBreakUpDetails', 'ArrivalBrkUpID', $ArrivalBrkUpID);
				$sqlQuery = "UPDATE `RmArrivalBreakUpDetails` SET  `Variety`='$Variety',`Count`='$ArrivalCount',
				`Weight`='$ArrivalWeight',`Rate`='$ArrivalRate' WHERE ArrivalBrkUpID='$ArrivalBrkUpID'";
				$stmt = $this->conn->prepare($sqlQuery);
				$stmt->execute();
				if ($stmt) {
					$updatedBreakup = $this->auditLogger->fetchRowById('RmArrivalBreakUpDetails', 'ArrivalBrkUpID', $ArrivalBrkUpID);
					$this->auditLogger->logUpdate('RmArrivalBreakUpDetails', $ArrivalBrkUpID, $existingBreakup, $updatedBreakup, $RequestBody, [
						'ReferenceNo' => $ArrivalID,
						'Remarks' => 'Arrival breakup updated',
					]);
				}
				$BrkID = $ArrivalBrkUpID;
				$this->editRmLotDetails($ArrivalID, $BrkID, $ArrivalCount, $ArrivalWeight, $ClientID);
			} else {


				$sqlQuery = "SELECT ArrivalBrkUpID as BreakUp,Weight,Count FROM `RmArrivalBreakUpDetails` 
		 			WHERE ArrivalID='$ArrivalID' and Count='$ArrivalCount' and `Variety`='$Variety'";
				$stmtCheckCount = $this->conn->prepare($sqlQuery);
				$stmtCheckCount->execute();
				$rowCount = $stmtCheckCount->rowCount();
				$rowCount = 0; // By Teja Adviced By Ramesh.Bill Will Effect                      

				if ($rowCount == 0) {
					$sqlQuery = "INSERT INTO `RmArrivalBreakUpDetails`
			( `ArrivalID`, `Variety`,`Count`, `Weight`, `Rate`) VALUES  
		('$ArrivalID','$Variety','$ArrivalCount','$ArrivalWeight','$ArrivalRate')"; //uma

					$stmt = $this->conn->prepare($sqlQuery);
					$stmt->execute();
					$BrkID = $this->conn->lastInsertId();
					if ($stmt) {
						$newBreakup = $this->auditLogger->fetchRowById('RmArrivalBreakUpDetails', 'ArrivalBrkUpID', $BrkID);
						$this->auditLogger->logInsert('RmArrivalBreakUpDetails', $BrkID, $newBreakup, $RequestBody, [
							'ReferenceNo' => $ArrivalID,
							'Remarks' => 'Arrival breakup created',
						]);
					}

					$this->postRmLotDetails($ArrivalID, $BrkID, $ArrivalCount, $ArrivalWeight, $ClientID);
				} else {
					$this->Error = "Count Already Exists";
				}
			}

			// $stmt = $this->conn->prepare($sqlQuery);
			// $stmt->execute();
			// $BrkID = $this->conn->lastInsertId();

			if ($stmt) {
				$AryupdateArrivalBillAmountandTds = $this->utilController->updateArrivalBillAmountandTds($ArrivalID);
				$AryPostpanwiseBillAmount = $this->postPanwiseData($ArrivalID); //uma 23-05-2024 Tds calculation purpose

			}
		} else {
			$this->Error = "Rates and Other Values Missing";
		}
	}


	function editRmLotDetails($ArrivalID, $BrkID, $ArrivalCount, $ArrivalWeight, $ClientID = '')
	{

		$ArrivalData = $this->getArrivalData($ArrivalID);

		$PostedUserID = $ArrivalData['PostedUserID'];
		$LotNumber = $ArrivalData['ArrivalNumber'];
		$PurchaseStationID = $ArrivalData['ArrivalStationID'];
		$VehicalID = $ArrivalData['VehicalID'];
		$AgentID = $ArrivalData['AgentID'];
		$DriverID = $ArrivalData['DriverID'];
		$GraderID = $ArrivalData['GraderID'];
		$SupevisorID = $ArrivalData['SupervisorID'];
		$PurchaseCategoryID = $ArrivalData['PurchaseCategoryID'];
		$PurchaseCount = $ArrivalCount;
		$PurchaseWeight = $ArrivalWeight;
		$ProductionWeight = '';
		$FarmAddress = $ArrivalData['FarmAddress'];
		$CreatedDateTime = $ArrivalData['CreatedDateTime'];
		$PurchaseDate = $ArrivalData['BillDate'];
		$BreakUpID = $BrkID;
		$LotStatus = '1';
		$SupevisorID = '1';
		$NetWeight = '';
		$SealNo = '';
		$Remarks = $ArrivalData['Remarks'];
		$SupplierID = $ArrivalData['RmSupplierID'];

		$ConfigData = $this->appController->getClientConfig($ClientID);
		$ConsolidateLotCount = $ConfigData['ConsolidateLotCount'];
		$Query = '';
		$RmLotQuery = '';

		if ($ConsolidateLotCount == 0) {
			$Query .= " and ArrivalBrkUpID='$BreakUpID'";
			$RmLotQuery .= " and BreakUpID='$BreakUpID'";
		}

		$sqlQuery = "SELECT GROUP_CONCAT(ArrivalBrkUpID) as BreakUp,sum(Weight) as Weight,Count FROM `RmArrivalBreakUpDetails` 
		 WHERE ArrivalID='$ArrivalID' and Count='$ArrivalCount' $Query GROUP by Count";


		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		$Weight = $dataRow['Weight'];
		$Count = round($dataRow['Count'], 2);


		// `LotNumber`='$LotNumber',  Because Rm Lot Number
		$SqlPostLot = "update `RmLotDetatils` set 
				`PostedUserID`='$PostedUserID',
				`PurchaseStationID`='$PurchaseStationID',
				`VehicalID`='$VehicalID', 
				`LotNumber`='$LotNumber', 
				`AgentID`='$AgentID', 
				`SupplierID` = $SupplierID,
				`DriverID`='$DriverID', 
				`GraderID`='$GraderID',
				`PurchaseCategoryID`='$PurchaseCategoryID',
				`FarmAddress`='$FarmAddress',
				`PurchaseDate`='$PurchaseDate',
				`PurchaseCount`='$PurchaseCount',
				-- `PurchaseWeight`='$PurchaseWeight',
				`PurchaseWeight`='$Weight',
				`SupevisorID`='$SupevisorID',	 
				`Remarks`='$Remarks' where RmReferenceID = '$ArrivalID'  and `PurchaseCount`='$ArrivalCount' $RmLotQuery";
		// echo $SqlPostLot;exit;

		$stmt = $this->conn->prepare($SqlPostLot);
		$stmt->execute();
	}

	function postRmLotDetails($ArrivalID, $BrkID, $ArrivalCount, $ArrivalWeight, $ClientID)
	{

		$ArrivalData = $this->getArrivalData($ArrivalID);
		$ConsolidateLotCount = $this->appController->getClientConfig($ClientID)['ConsolidateLotCount'];




		$PostedUserID = $ArrivalData['PostedUserID'];
		$SupplierID = $ArrivalData['RmSupplierID'];
		$LotNumber = $ArrivalData['ArrivalNumber'];
		$PurchaseStationID = $ArrivalData['ArrivalStationID'];
		$VehicalID = $ArrivalData['VehicalID'];
		$AgentID = $ArrivalData['AgentID'];
		$DriverID = $ArrivalData['DriverID'];
		$GraderID = $ArrivalData['GraderID'];
		$SupevisorID = $ArrivalData['SupervisorID'];
		$PurchaseCategoryID = $ArrivalData['PurchaseCategoryID'];
		$PurchaseCount = $ArrivalCount;
		$PurchaseWeight = $ArrivalWeight;
		$ProductionWeight = '';
		$FarmAddress = $ArrivalData['FarmAddress'];
		$CreatedDateTime = $ArrivalData['CreatedDateTime'];
		$PurchaseDate = $ArrivalData['BillDate'];
		$BreakUpID = $BrkID;
		$LotStatus = '1';
		$SupevisorID = '1';
		$NetWeight = '';
		$SealNo = '';
		$Remarks = $ArrivalData['Remarks'];

		$sqlQuery = "SELECT * FROM `RmLotDetatils` 
		WHERE RmReferenceID='$ArrivalID' and PurchaseCount='$PurchaseCount'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$rowCount = $stmt->rowCount(); //uma 14-05-2024 devi grading purpose



		if ($ConsolidateLotCount == 0) {
			$rowCount = 0;
		}

		// if ($stmt->rowCount() == 0) {
		if ($rowCount == 0) {

			$SqlPostLot = "INSERT INTO `RmLotDetatils` 
				( `RmReferenceID`,`BreakUpID`,`SupplierID`,`PostedUserID`, `LotNumber`, 
				`PurchaseStationID`, `VehicalID`, `AgentID`, 
				`DriverID`, `GraderID`, 
				`PurchaseCategoryID`, `PurchaseCount`, `PurchaseWeight`,`ProductionWeight`, 
				`FarmAddress`, `CreatedDateTime`, `PurchaseDate`,
				`Year`, `LotStatus`,`SupevisorID`,`NetWeight`,`SealNo`,`Remarks`) VALUES  
				('$ArrivalID','$BreakUpID','$SupplierID','$PostedUserID','$LotNumber',	
				'$PurchaseStationID','$VehicalID','$AgentID',
				'$DriverID','$GraderID',
				'$PurchaseCategoryID','$PurchaseCount','$PurchaseWeight','$ProductionWeight',
				'$FarmAddress','$CreatedDateTime','$PurchaseDate',
				'','1','$SupevisorID','$NetWeight','$SealNo','$Remarks')";
			$stmt = $this->conn->prepare($SqlPostLot);
			$stmt->execute();
		} else {
			// $this->getRmBreakupGroup($ArrivalID, $ArrivalCount);
			$this->getRmBreakupGroup($ArrivalID, $ArrivalCount, $BreakUpID, $ConsolidateLotCount);
		}
	}


	function getArrivalData($ArrivalID)
	{
		$dataRow = null;
		$sqlFields = "`RmArrivalID`, `PostedUserID`, `Species`,`Remarks`,`Extra`,`RmSupplierID`,RAD.`ProcessingStationID`,`ArrivalNumber`,AutoArrivalReferenceNo,`BillDate`,
		 `ArrivalStationID`, `VehicalID`, `AgentID`, `DriverID`, `GraderID`, `SupervisorID`,
         `PurchaseCategoryID`, `FarmAddress`, `CreatedDateTime`, `ArrivalDate`, `IsEdited`, `HarvestCommission`,`LotStatus`,LotCode as StationCode";
		$sqlQuery = "SELECT  $sqlFields FROM `RmArrivalDetatils` as RAD 
		left join RmPurchaseStations as RPS on RPS.PurchaseStationID=RAD.ArrivalStationID
		WHERE  RmArrivalID='$ArrivalID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		return $dataRow;
	}

	function getArrivalBreakUp($ArrivalID)
	{
		$sqlQuery = "SELECT  HarvestCommission FROM `RmArrivalDetatils` WHERE  RmArrivalID='$ArrivalID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		$HarvestCommission = $dataRow['HarvestCommission'];


		$Data = null;
		$ArrivalData = $this->getArrivalData($ArrivalID);
		//var_dump($ArrivalData);
		$Species = $ArrivalData['Species'];
		$CenterID = $ArrivalData['ArrivalStationID'];
		$ArrivalDate = $ArrivalData['ArrivalDate'];
		$BillDate = $ArrivalData['BillDate'];

		$sqlFields = "`ArrivalBrkUpID`, `ArrivalID`, `Variety`,`Count`, `Weight`, `Rate`, `BrkUpStatus`";
		$sqlQuery = "select $sqlFields  from `RmArrivalBreakUpDetails` where ArrivalID='$ArrivalID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$Data = array();

		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$AryPriceData = $this->utilController->getRmPrice($Species, $BillDate, $dataRow['Count'], $CenterID);
			$dataRow['MasterRate'] = sprintf('%0.2f', isset($AryPriceData['RmPrice']) ? $AryPriceData['RmPrice'] : 0);
			$dataRow['BreakupTotal'] = sprintf('%0.2f', round($dataRow['Weight'] * $dataRow['Rate'], 2));
			$dataRow['Count'] = sprintf('%0.2f', round($dataRow['Count'], 2));
			$dataRow['Rate'] = sprintf('%0.2f', round($dataRow['Rate'], 2));
			$dataRow['Weight'] = sprintf('%0.3f', round($dataRow['Weight'], 3));
			$dataRow['HarvestCommission'] = sprintf('%0.2f', round($HarvestCommission, 2));
			$dataRow['HarvestCommissionTotal'] = sprintf('%0.2f', round($dataRow['Weight'] * $HarvestCommission, 2));
			$dataRow['BillAmt'] = sprintf('%0.2f', round($dataRow['BreakupTotal'] + $dataRow['HarvestCommissionTotal'], 2));
			$ArrivalBreakUpID = $dataRow['ArrivalBrkUpID'];
			$RecievalCount = $this->getReceivalCount($ArrivalBreakUpID);
			$dataRow['RecievalCount'] = $RecievalCount;


			$GradeReceivingCount = $this->getGradeReceivingData($ArrivalBreakUpID);
			$dataRow['GradeReceivingCount'] = $GradeReceivingCount;

			if ($GradeReceivingCount > 0) {
				$Access = "0";
				$this->showAccess = "0";
			} else {
				$Access = "1";
			}
			$dataRow['Access'] = $Access;


			$Data[] = $dataRow;
		}

		return $Data;
	}


	function getGrandTotal($ArrivalID)
	{

		$sqlQuery = "SELECT * FROM `RmArrivalDetatils` WHERE RmArrivalID='$ArrivalID'";
		$stmt = $this->conn->prepare($sqlQuery);

		$Data = array();


		$sqlQuery = "SELECT sum(RABD.Weight) as WeightTotal,sum(RABD.Weight*RAD.HarvestCommission) as HarvestCommission,
		sum(RABD.Weight*RABD.Rate) as ValueTotal FROM `RmArrivalDetatils`  as RAD left JOIN RmArrivalBreakUpDetails as RABD on RAD.RmArrivalID=RABD.ArrivalID
		where ArrivalID='$ArrivalID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		$dataRow['WeightTotal'] = sprintf('%0.3f', round($dataRow['WeightTotal'], 3));
		$dataRow['HarvestCommission'] = sprintf('%0.2f', round($dataRow['HarvestCommission'], 2));
		$dataRow['ValueTotal'] = sprintf('%0.2f', round($dataRow['ValueTotal'], 2));
		$dataRow['BillAmtTotal'] = sprintf('%0.2f', round($dataRow['ValueTotal'] + $dataRow['HarvestCommission'], 2));


		return $dataRow;
	}


	function getOverAllArrival($ArrivalID)
	{

		$responseData = array();

		$sqlQuery = "SELECT * FROM `RmArrivalDetatils` WHERE RmArrivalID='$ArrivalID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

		$ArrivalStationID = $dataRow['ArrivalStationID'];
		$ProcessingStationID = $dataRow['ProcessingStationID'];
		$Species = $dataRow['Species'];
		$ArrivalDate = $dataRow['ArrivalDate'];

		/*	$sqlQuery="SELECT TotalAmount,TotalWeight FROM `RmOverAllArrival` WHERE 
																					   CompanyID='$ProcessingStationID' and PlantID='$ArrivalStationID' 
																					   and Species='$Species' and ArrivalDate='$ArrivalDate'";																	
																			   $stmt = $this->conn->prepare($sqlQuery);
																			   $stmt->execute();
																			   $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
																			   */

		$sqlQuery = "SELECT TotalAmount,TotalWeight FROM `RmOverAllArrival` WHERE 
									CenterID='$ArrivalStationID' and PlantID='$ProcessingStationID'  
									and Species='$Species' and ArrivalDate='$ArrivalDate'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		$responseData['OverAllWeight'] = $dataRow['TotalWeight'];
		$responseData['OverAllAmount'] = $dataRow['TotalAmount'];

		$sqlQuery = "SELECT sum(RABD.Weight) as RcvdWeight,sum(RABD.Weight*RAD.HarvestCommission) as HarvestCommission,
		sum(RABD.Weight*RABD.Rate) as BillAmount FROM `RmArrivalDetatils`  as RAD left JOIN RmArrivalBreakUpDetails as RABD on RAD.RmArrivalID=RABD.ArrivalID
		WHERE  ProcessingStationID= '$ProcessingStationID' and ArrivalStationID='$ArrivalStationID' and Species='$Species' and ArrivalDate='$ArrivalDate'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();

		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		$responseData['OverAllBalWeight'] = $responseData['OverAllWeight'] - $dataRow['RcvdWeight'];
		$responseData['OverAllBalAmount'] = $responseData['OverAllAmount'] - ($dataRow['HarvestCommission'] - $dataRow['BillAmount']);

		return $responseData;
	}

	function postPanwiseData($ArrivalID) //uma 23-05-2024 Tds calculation purpose
	{

		$SqlCount = "SELECT Count(*) as Count FROM `RmPanWiseSuppliedAmount`  WHERE PanwiseArrivalID='$ArrivalID'";
		$stmtCount = $this->conn->prepare($SqlCount);
		$stmtCount->execute();
		$dataRowCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
		$Count = $dataRowCount['Count'];


		$SqlFields = "AutoArrivalReferenceNo,ArrivalBillAmount,ArrivalHarvestAmount,BillDate,RmSupplierID,AgentID";
		$SqlArrivalData = "SELECT  $SqlFields FROM `RmArrivalDetatils` WHERE RmArrivalID='$ArrivalID'";
		$stmtArrivalData = $this->conn->prepare($SqlArrivalData);
		$stmtArrivalData->execute();
		$dataRowArrivalData = $stmtArrivalData->fetch(PDO::FETCH_ASSOC);

		$AgentID = $dataRowArrivalData['AgentID'];
		$RmSupplierID = $dataRowArrivalData['RmSupplierID'];
		$AutoArrivalReferenceNo = $dataRowArrivalData['AutoArrivalReferenceNo'];
		$ArrivalBillAmount = $dataRowArrivalData['ArrivalBillAmount'];
		$ArrivalHarvestAmount = $dataRowArrivalData['ArrivalHarvestAmount'];
		$BillDate = $dataRowArrivalData['BillDate'];

		if ($AgentID != 0) {
			$AgentPanNumber = $this->utilController->getAgentName($AgentID)['PanNumber'];
		} else {
			$AgentPanNumber = $this->utilController->getRMSuppliers($RmSupplierID)['PanNumber'];
		}

		$SupplierPanNumber = $this->utilController->getRMSuppliers($RmSupplierID)['PanNumber'];

		if ($Count == 0) {
			$SqlInsert = "INSERT INTO `RmPanWiseSuppliedAmount`( `PanwiseArrivalID`, `PanwiseBillDate`, `PanwiseAutoArrivalReferenceNO`,
		 `PanwiseAmount`, `BillPanNumber`, `PanAmountType`) 
		VALUES ('$ArrivalID','$BillDate','$AutoArrivalReferenceNo','$ArrivalBillAmount','$SupplierPanNumber',0),
		('$ArrivalID','$BillDate','$AutoArrivalReferenceNo','$ArrivalHarvestAmount','$AgentPanNumber',1)
				";
		} else {

			$SqlInsert = "UPDATE RmPanWiseSuppliedAmount
			SET 
			PanwiseBillDate ='$BillDate',
			PanwiseAutoArrivalReferenceNO ='$AutoArrivalReferenceNo',
				
			PanwiseAmount = CASE 
					WHEN `PanwiseArrivalID`='$ArrivalID' and `PanAmountType`='0' THEN '$ArrivalBillAmount'
					WHEN `PanwiseArrivalID`='$ArrivalID' and `PanAmountType`='1' THEN '$ArrivalHarvestAmount'
					
					END,
					BillPanNumber = CASE 
					WHEN `PanwiseArrivalID`='$ArrivalID' and `PanAmountType`='0' THEN '$SupplierPanNumber'
					WHEN `PanwiseArrivalID`='$ArrivalID' and `PanAmountType`='1' THEN '$AgentPanNumber'
				END
			WHERE `PanwiseArrivalID`='$ArrivalID';";
		}
		// var_dump($SqlInsert);
		// exit;
		$stmtInsert = $this->conn->prepare($SqlInsert);
		$stmtInsert->execute();

		return 'Success';
	}

	function getReceivalCount($ArrivalBreakUpID)
	{
		$sqlReceiving = "SELECT COUNT(*) AS RowCount FROM RmReceivingBreakup WHERE ArrivalBreakupID='$ArrivalBreakUpID'";
		$stmtReceiving = $this->conn->prepare($sqlReceiving);
		$stmtReceiving->execute();

		$dataRow = $stmtReceiving->fetch(PDO::FETCH_ASSOC);
		$RowCount = $dataRow['RowCount'];

		return $RowCount;
	}


	public function getGradeReceivingData($LotID)
	{
		$sqlGradeReceiving = "SELECT COUNT(*) AS RowCount FROM PpcHLGradingDetails WHERE LotID ='$LotID'";
		$stmtGradeReceiving = $this->conn->prepare($sqlGradeReceiving);
		$stmtGradeReceiving->execute();

		$dataRow = $stmtGradeReceiving->fetch(PDO::FETCH_ASSOC);
		$RowCount = $dataRow['RowCount'];

		return $RowCount;
	}
	function CheckArrivalNumberUpdate($NewArrivalNumber,$ArrivalID){
		if(!empty($NewArrivalNumber) && !empty($ArrivalID))
		$OldArrivalSql = "SELECT ArrivalNumber FROM RmArrivalDetatils WHERE RmArrivalID ='$ArrivalID'";
		$stmtOldArrival = $this->conn->prepare($OldArrivalSql);
		$stmtOldArrival->execute();
		$OldArrivalNumber = $stmtOldArrival->fetch(PDO::FETCH_ASSOC)['ArrivalNumber'];
		if(!empty($OldArrivalNumber) && ($OldArrivalNumber != $NewArrivalNumber)){
			return $OldArrivalNumber;
		}
		return false;
	}
}
