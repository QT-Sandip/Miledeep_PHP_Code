<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/AuditLogger.php';

class RmOverAllArrivalController
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

	public function getArrivalDetails($RequestBody)
	{
		$ArrivalID = isset($RequestBody['ArrivalID']) ? $RequestBody['ArrivalID'] : '';
		$ArrivalDate = $RequestBody['ArrivalDate'];
		$ArrivalData = $this->getArrivalData($RequestBody);
		$GrandTotal = $this->getGrandTotal($RequestBody);

		$ArrayBalanceWeight = $this->getOverAllArrival($RequestBody, $ArrivalID);
		$Data['Error'] = null;
		$Data['Msg'] = 'Arrival Posted Successful';
		$Data['ArrivalData'] = $ArrivalData;
		$Data['OverArrivalData'] = $ArrayBalanceWeight;
		$Data['GrandTotal'] = $GrandTotal;
		return $Data;
	}

	public function postDeleteArrivalBrkUP($RequestBody)
	{

		$ArrivalID = $RequestBody['ArrivalID'];
		$ArrivalDate = $RequestBody['ArrivalDate'];
		$existingOverallArrival = $this->auditLogger->fetchRowById('RmOverAllArrival', 'ID', $ArrivalID);
		$sqlQuery = "delete  from `RmOverAllArrival` where ID='$ArrivalID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		if ($stmt && $existingOverallArrival != null) {
			$this->auditLogger->logDelete('RmOverAllArrival', $ArrivalID, $existingOverallArrival, $RequestBody, [
				'ReferenceNo' => $ArrivalDate,
				'Remarks' => 'Overall arrival deleted',
			]);
		}
		$ArrivalID = $RequestBody['ArrivalID'];
		$ArrivalDate = $RequestBody['ArrivalDate'];
		$ArrivalData = $this->getArrivalData($RequestBody);
		$GrandTotal = $this->getGrandTotal($RequestBody);

		$Data['Error'] = null;
		$Data['Msg'] = 'Deleted Posted Successful';
		$Data['ArrivalData'] = $ArrivalData;
		$Data['GrandTotal'] = $GrandTotal;
		return $Data;
	}
	public function postArrivalDetails($RequestBody)
	{
		$PostedUserID = 1;
		$ArrivalDate = date("Y-m-d", strtotime($RequestBody['ArrivalDate']));
		$CompanyID = $RequestBody['CompanyID'];
		$PlantID = $RequestBody['PlantID'];
		$CenterID = $RequestBody['CenterID'];
		$Species = $RequestBody['Species'];
		$TotalAmount = $RequestBody['TotalAmount'];
		$TotalWeight = $RequestBody['TotalWeight'];
		$ArrivalID = isset($RequestBody['ArrivalID']) ? $RequestBody['ArrivalID'] : '';
		$AryCurrentDate = getCurrentDateTime();
		$CreatedDateTime = $AryCurrentDate['TimeStamp'];
		$Year = $AryCurrentDate['Year'];
		$stmt = false;
		if ($this->Error == null && $ArrivalID == '') {
			$SqlOverallCount = "SELECT COUNT(*) as Count FROM `RmOverAllArrival` WHERE ArrivalDate='$ArrivalDate' and CompanyID='$CompanyID' 
			and Species='$Species' and PlantID='$PlantID'
			and CenterID='$CenterID' ORDER BY `ID` DESC";
			$stmtOverallCount = $this->conn->prepare($SqlOverallCount);
			$stmtOverallCount->execute();
			$dataRowCount = $stmtOverallCount->fetch(PDO::FETCH_ASSOC);
			$Count = $dataRowCount['Count'];
			if ($Count == 0) {
				$sqlQuery = "INSERT INTO `RmOverAllArrival`( `ArrivalDate`, `CompanyID`, `PlantID`, `CenterID`,
			 `Species`, `TotalWeight`, `TotalAmount`,`PostedID`) VALUES   
				('$ArrivalDate', '$CompanyID', '$PlantID', '$CenterID',
			 '$Species', '$TotalWeight', '$TotalAmount','$PostedUserID')";
				$stmt = $this->conn->prepare($sqlQuery);
				$stmt->execute();
				$ArrivalID = $this->conn->lastInsertId();
				if ($stmt) {
					$newOverallArrival = $this->auditLogger->fetchRowById('RmOverAllArrival', 'ID', $ArrivalID);
					$this->auditLogger->logInsert('RmOverAllArrival', $ArrivalID, $newOverallArrival, $RequestBody, [
						'ReferenceNo' => $ArrivalDate,
						'Remarks' => 'Overall arrival created',
					]);
				}
				$RequestBody['ArrivalID'] = $ArrivalID;
				$RequestBody['ArrivalDate'] = $ArrivalDate;
				$ArrivalData = $this->getArrivalData($RequestBody);
				$GrandTotal = $this->getGrandTotal($RequestBody);
				$Data['Msg'] = "Posted Successfully";
				$Data['ArrivalData'] = $ArrivalData;
				$Data['GrandTotal'] = $GrandTotal;
				return $Data;
			} else {
				$ArrivalData = $this->getArrivalData($RequestBody);
				$GrandTotal = $this->getGrandTotal($RequestBody);
				$Data['Error'] = null;
				$Data['Msg'] = "Duplicate Entry";
				$Data['ArrivalData'] = $ArrivalData;
				$Data['GrandTotal'] = $GrandTotal;
				return $Data;
			}
		} else if ($ArrivalID != '') {

			$SqlOverallCount = "SELECT COUNT(*) as Count FROM `RmOverAllArrival` 
			WHERE ArrivalDate='$ArrivalDate' and CompanyID='$CompanyID' 
			and Species='$Species' and PlantID='$PlantID'
			and CenterID='$CenterID'  and ID !='$ArrivalID' ORDER BY `ID` DESC";
			$stmtOverallCount = $this->conn->prepare($SqlOverallCount);
			$stmtOverallCount->execute();
			$dataRowCount = $stmtOverallCount->fetch(PDO::FETCH_ASSOC);
			$Count = $dataRowCount['Count'];
			if ($Count == 0) {
				$existingOverallArrival = $this->auditLogger->fetchRowById('RmOverAllArrival', 'ID', $ArrivalID);
				$sqlQuery = "Update `RmOverAllArrival` set
				`ArrivalDate`='$ArrivalDate',
				`CompanyID`='$CompanyID',
				 `PlantID`='$PlantID',
				 `CenterID`='$CenterID',
				 `Species`='$Species',
				`TotalWeight`='$TotalWeight',
				 `TotalAmount`='$TotalAmount',
				 `PostedID`='$PostedUserID',
				`IsEdited`=1 where 	ID ='$ArrivalID'";
				$stmt = $this->conn->prepare($sqlQuery);
				$stmt->execute();
				if ($stmt) {
					$updatedOverallArrival = $this->auditLogger->fetchRowById('RmOverAllArrival', 'ID', $ArrivalID);
					$this->auditLogger->logUpdate('RmOverAllArrival', $ArrivalID, $existingOverallArrival, $updatedOverallArrival, $RequestBody, [
						'ReferenceNo' => $ArrivalDate,
						'Remarks' => 'Overall arrival updated',
					]);
					return $this->getArrivalDetails($RequestBody);
				}
			} else {
				$ArrivalData = $this->getArrivalData($RequestBody);
				$GrandTotal = $this->getGrandTotal($RequestBody);
				$Data['Error'] = null;
				$Data['Msg'] = "Already Exists";
				$Data['ArrivalData'] = $ArrivalData;
				$Data['GrandTotal'] = $GrandTotal;
				return $Data;
			}
		}
	}
	function getArrivalData($RequestBody)
	{
		$ArrivalDate = $RequestBody['ArrivalDate'];
		$ArrivalFromDate = trim($RequestBody['ArrivalFromDate']);


		if ($ArrivalFromDate == '') {
			$Qry = "OAL.ArrivalDate='$ArrivalDate'";
		} else {
			$Qry = "OAL.ArrivalDate between '$ArrivalFromDate' and '$ArrivalDate'";
		}
		if ($RequestBody['CompanyID']) {
			$CompanyID = $RequestBody['CompanyID'];
			$Qry .= " and OAL.CompanyID='$CompanyID'";
		}
		if ($RequestBody['PlantID']) {
			$PlantID = $RequestBody['PlantID'];
			$Qry .= " and OAL.PlantID='$PlantID'";
		}
		if ($RequestBody['CenterID']) {
			$CenterID = $RequestBody['CenterID'];
			$Qry .= " and OAL.CenterID='$CenterID'";
		}
		if ($RequestBody['Species']) {
			$Species = $RequestBody['Species'];
			$Qry .= " and OAL.Species='$Species'";
		}
		$StatusType = '';
		if ($RequestBody['StatusType'] != '') {
			$StatusType = $RequestBody['StatusType'];
		}
		$dataRow = null;
		$sqlFields = "`ID` as ArrivalID, RPS.StationName as Center,PLANT.StationName as Plant,`ArrivalDate`, `CompanyID`, `PlantID`, `CenterID`, `Species`, `TotalWeight`, `TotalAmount`";
		$sqlQuery = "SELECT  $sqlFields FROM `RmOverAllArrival` as OAL
		left join RmPurchaseStations as RPS on OAL.CenterID=RPS.PurchaseStationID
		left join RmProcessingStations as PLANT on PLANT.ProcessingStationID=OAL.PlantID
		WHERE  $Qry";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$ArrivalID = $dataRow['ArrivalID'];
			$ArrayBalanceWeight = $this->getOverAllArrival($RequestBody, $ArrivalID);
			$TotalWeight = $dataRow['TotalWeight'];
			$OverAllBalAmount = $ArrayBalanceWeight['OverAllBalAmount'];
			$ExceedAmount = 0;
			if ($OverAllBalAmount < 0 && $ArrayBalanceWeight['OverAllBalWeight'] == 0) {
				$Status = 'Exceed';
				$StatusColour = '#FA0F04';
				$ExceedAmount = abs($OverAllBalAmount);
			} else if ($ArrayBalanceWeight['OverAllBalWeight'] == 0 && $ArrayBalanceWeight['OverAllBalAmount'] == 0) {
				$Status = 'Completed';
				$StatusColour = '#025009';
				$ExceedAmount = 0;
			} else {
				$Status = 'Pending';
				$StatusColour = '#FA0F04';
				$ExceedAmount = 0;
			}
			$dataRow['Status'] = $Status;
			$dataRow['StatusColour'] = $StatusColour;
			$dataRow['BalanceWeight'] = $ArrayBalanceWeight['OverAllBalWeight'];
			$dataRow['BalanceAmount'] = $ArrayBalanceWeight['OverAllBalAmount'] > 0 ?$ArrayBalanceWeight['OverAllBalAmount']: 0;
			$dataRow['ExceedAmount'] = $ExceedAmount;
			$dataRow['AvgRate'] = round($dataRow['TotalAmount'] / $dataRow['TotalWeight'], 2);

			if (
				$StatusType == '' || ($Status == 'Pending' && $StatusType == 0) || ($Status == 'Completed' && $StatusType == 1) || ($Status == 'Exceed' && $StatusType == 2)
			) {
				$Data[] = $dataRow;
			}
			//  $Data[] = $dataRow;
		}
		return $Data;
	}
	function getGrandTotal($RequestBody)
	{
		$ArrivalDate = $RequestBody['ArrivalDate'];
		$ArrivalFromDate = $RequestBody['ArrivalFromDate'];
		if ($ArrivalFromDate == '') {
			$Qry = "OAL.ArrivalDate='$ArrivalDate'";
		} else {
			$Qry = "OAL.ArrivalDate between '$ArrivalFromDate' and '$ArrivalDate'";
		}
		if ($RequestBody['CompanyID']) {
			$CompanyID = $RequestBody['CompanyID'];
			$Qry .= " and OAL.CompanyID='$CompanyID'";
		}
		if ($RequestBody['PlantID']) {
			$PlantID = $RequestBody['PlantID'];
			$Qry .= " and OAL.PlantID='$PlantID'";
		}
		if ($RequestBody['CenterID']) {
			$CenterID = $RequestBody['CenterID'];
			$Qry .= " and OAL.CenterID='$CenterID'";
		}
		if ($RequestBody['Species']) {
			$Species = $RequestBody['Species'];
			$Qry .= " and OAL.Species='$Species'";
		}
		$sqlFields = "round(sum(TotalWeight),2) as WeightTotal ,round(sum(TotalAmount),2) as ValueTotal";
		$sqlQuery = "SELECT  $sqlFields FROM `RmOverAllArrival` as OAL
					left join RmPurchaseStations as RPS on OAL.CenterID=RPS.PurchaseStationID
					left join RmProcessingStations as PLANT on PLANT.ProcessingStationID=OAL.PlantID
					WHERE  $Qry";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		return $dataRow;
	}
	function getOverAllArrival($RequestBody, $ArrivalID)
	{
		$responseData = array();
		$ArrivalStationID = $RequestBody['CenterID'];
		$ProcessingStationID = $RequestBody['PlantID'];
		$Species = $RequestBody['Species'];
		$ArrivalDate = $RequestBody['ArrivalDate'];
		$ArrivalFromDate = trim($RequestBody['ArrivalFromDate']); //uma 25-05-2024
		$Query = '';
		$OverallQuery = '';
		if ($ArrivalFromDate == '') {
			$DateFilter = " ArrivalDate='$ArrivalDate'";
		} else {
			$DateFilter = " ArrivalDate between '$ArrivalFromDate' and '$ArrivalDate'";
		}
		if ($ArrivalStationID != '') {
			$OverallQuery .= " and CenterID='$ArrivalStationID'";
		}
		if ($ProcessingStationID != '') {
			$OverallQuery .= " and PlantID='$ProcessingStationID'";
		}
		if ($Species != '') {
			$OverallQuery .= " 	";
		}
		if ($ArrivalID != '') {
			$OverallQuery .= " and ID='$ArrivalID'";
		}
		$SqlFields = ' TotalAmount,TotalWeight,CompanyID,PlantID,CenterID,Species,ArrivalDate';
		$sqlQuery = "SELECT $SqlFields  FROM `RmOverAllArrival` WHERE   $DateFilter  $OverallQuery";
		// $SqlFields = ' sum(TotalAmount) as TotalAmount,sum(TotalWeight) as TotalWeight,CompanyID,PlantID,CenterID,Species';//uma 17-06-2024
		// echo $sqlQuery = "SELECT $SqlFields  FROM `RmOverAllArrival` WHERE   $DateFilter  $OverallQuery group by CompanyID,PlantID,CenterID,Species";//uma 17-06-2024 
		// 
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		$CompanyID = $dataRow['CompanyID'];
		$PlantID = $dataRow['PlantID'];
		$CenterID = $dataRow['CenterID'];
		$Species = $dataRow['Species'];
		$OverallArrivalDate = $dataRow['ArrivalDate'];

		$responseData['CompanyID'] = $dataRow['CompanyID'];
		$responseData['PlantID'] = $dataRow['PlantID'];
		$responseData['CenterID'] = $dataRow['CenterID'];
		$responseData['Species'] = $dataRow['Species'];
		$responseData['ArrivalDate'] = $OverallArrivalDate;

		$responseData['OverAllWeight'] = $dataRow['TotalWeight'];
		$responseData['OverAllAmount'] = $dataRow['TotalAmount'];

		$SqlArrivalweightQueryfields = "sum(RABD.Weight) as RcvdWeight,sum(RABD.Weight*RAD.HarvestCommission) as HarvestCommission,
		sum(RABD.Weight*RABD.Rate) as BillAmount";

		$sqlQuery = "SELECT $SqlArrivalweightQueryfields FROM `RmArrivalDetatils`  as RAD 
		left JOIN RmArrivalBreakUpDetails as RABD on RAD.RmArrivalID=RABD.ArrivalID
		WHERE  ArrivalDate='$OverallArrivalDate'   and ArrivalStationID='$CenterID' and ProcessingStationID='$PlantID' and Species='$Species' group by ArrivalDate";
		// $DateFilter
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();

		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		$responseData['OverAllBalWeight'] = round((round($responseData['OverAllWeight'], 2) - round($dataRow['RcvdWeight'], 2)), 2);
		$responseData['OverAllBalAmount'] = round((round($responseData['OverAllAmount'], 2) - round(($dataRow['HarvestCommission'] + $dataRow['BillAmount']), 2)), 2);

		return $responseData;
	}
}
