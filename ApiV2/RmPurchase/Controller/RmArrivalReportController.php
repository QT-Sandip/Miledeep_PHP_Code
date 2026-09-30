<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class RmAllArrivalReportController
{
	private $conn;
	private $db_table = "ScrmApplicants";
	private $utilController;
	private $Error = null;
	private $Msg = '';
	private $userFilterRequest = '';
	private $currentFilterRequest = '';


	// Db connection
	public function __construct($db)
	{
		$this->conn = $db;
		$this->utilController = new UtilController($this->conn);
	}

	public function getArrivalDetails($RequestBody)
	{
		$this->userFilterRequest = $RequestBody;
		$ArrivalID = isset($RequestBody['ArrivalID']) ? $RequestBody['ArrivalID'] : '';
		$ArrivalDate = $RequestBody['ArrivalDate'];

		//$ArrivalData = $this->getArrivalData($RequestBody);
		$ArrivalData = $this->getCenterWiseData();

		$GrandTotal = $this->getGrandTotal($RequestBody);
		$Summary = $this->getGrandTotalOverAll($RequestBody);

		$SubRequestBody['ArrivalDate'] = $ArrivalDate;
		$subTotal = $this->getSubTotal($SubRequestBody);

		$Data['Error'] = null;
		$Data['Msg'] = 'Arrival Posted Successful';
		$Data['Headers'] = $this->getPageHeaders();
		$Data['ArrivalData'] = $ArrivalData;
		$Data['SubTotal'] = $subTotal;
		$Data['GrandTotal'] = $GrandTotal;
		$Data['Summary'] = $Summary;
		$Data['CompanyName'] = 'DEVI FISHERIES LIMITED';

		return $Data;
	}

	function getPageHeaders()
	{

		$sqlQuery = "SELECT * FROM `RmProcessingStations` ORDER by SortOrder ASC ";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$AryHeaders[] = $dataRow['StationName'];
		}

		return $AryHeaders;
	}

	function getCenterWiseData()
	{

		$sqlQuery = "SELECT PurchaseStationID FROM `RmPurchaseStations`   order by SortOrder asc ";
		$stmtPS = $this->conn->prepare($sqlQuery);
		$stmtPS->execute();

		while ($dataRowPs = $stmtPS->fetch(PDO::FETCH_ASSOC)) {

			$Req = array();
			$CurrentCenterID = $dataRowPs['PurchaseStationID'];
			$Req['CenterID'] = $CurrentCenterID;

			$AryPurchaseCenters = $this->utilController->getRmPurchaseCenters($CurrentCenterID);
			$PlantData["Center"] = $AryPurchaseCenters['StationName'];

			$Qry = $this->getFilter($Req);
			$sqlQuery = "SELECT * FROM `RmOverAllArrival` as OAL WHERE  $Qry GROUP by CenterID,Species order by Species desc ";
			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();

			if ($stmt->rowCount() > 0) {
				while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$userFilter = $this->userFilterRequest;
					$Req['Species'] = $dataRow['Species'];
					if ($userFilter['PlantID'] != '') {
						$Req['PlantID'] = $userFilter['PlantID'];
					}

					if ($dataRow['Species'] == 'BT') {
						$PlantData["Center"] = $AryPurchaseCenters['StationName'] . '(BT)';
					} else {
						$PlantData["Center"] = $AryPurchaseCenters['StationName'];
					}

					$GrandTotal = $this->getCenterTotal($Req);

					if ($userFilter['Species'] != '' && $userFilter['Species'] != $dataRow['Species']) {
						$PlantData["Value"] = '0.00';
						$PlantData["Qty"] = '0.000';
					} else if ($userFilter['CenterID'] != '' && $userFilter['CenterID'] != $CurrentCenterID) {
						$PlantData["Value"] = '0.00';
						$PlantData["Qty"] = '0.000';
					} else {
						$PlantData["Value"] = sprintf('%0.2f', ($GrandTotal['ValueTotal']));
						$PlantData["Qty"] = sprintf('%0.3f', ($GrandTotal['WeightTotal']));
					}

					$PlantData['item'] = $this->getPlantWiseData($Req);

					$AryData[] = $PlantData;
				}
				//return $Data;

			} else {
				$PlantData["Center"] = $AryPurchaseCenters['StationName'];
				$PlantData["Value"] = '0.00';
				$PlantData["Qty"] = '0.000';
				$PlantData['item'] = $this->getPlantWiseData($Req);
				$AryData[] = $PlantData;
			}
		}
		return $AryData;
	}

	// function getSpeciesWiseData($Req)
	// {

	// 	$Qry = $this->getFilter($Req);

	// 	$sqlQuery = "SELECT * FROM `RmOverAllArrival` as OAL WHERE  $Qry GROUP by CenterID,Species order by Species desc "; exit;
	// 	$stmt = $this->conn->prepare($sqlQuery);
	// 	$stmt->execute();
	// 	if ($stmt->rowCount() > 0) {
	// 		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
	// 			$Req['Species'] = $dataRow['Species'];
	// 			$AryPurchaseCenters = $this->utilController->getRmPurchaseCenters($Req['CenterID']);
	// 			if ($dataRow['Species'] == 'BT') {
	// 				$PlantData["Center"] = $AryPurchaseCenters['StationName'] . '(BT)';
	// 			} else {
	// 				$PlantData["Center"] = $AryPurchaseCenters['StationName'];
	// 			}

	// 			$GrandTotal = $this->getGrandTotal($Req);

	// 			$PlantData["Value"] = round($GrandTotal['ValueTotal'], 3);
	// 			$PlantData["Qty"] = round($GrandTotal['WeightTotal'], 3);
	// 			$PlantData['item'] = $this->getPlantWiseData($Req);

	// 			$Data[] = $PlantData;
	// 		}
	// 		return $Data;
	// 	}
	// }

	function getPlantWiseData($CurrentReq)
	{

		$CurrentSpecies = $CurrentReq['Species'];
		$CurrentCenterID = $CurrentReq['CenterID'];
		$reqData = $this->userFilterRequest;
		$PlantID = $reqData['PlantID'];

		$sqlQuery = "SELECT * FROM `RmProcessingStations`  order by SortOrder asc";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$CurrentPlantID = $dataRow['ProcessingStationID'];
			if ($reqData['PlantID'] != '' && $PlantID != $dataRow['ProcessingStationID']) {
				$dataRow['Kgs'] = "";
				$dataRow['Value'] = "";
				$dataRow['AvgRate'] = "";
			} else if ($reqData['CenterID'] != '' && $reqData['CenterID'] != $CurrentCenterID) {
				$dataRow['Kgs'] = "";
				$dataRow['Value'] = "";
				$dataRow['AvgRate'] = "";
			} else if ($reqData['Species'] != '' && $reqData['Species'] != $CurrentSpecies) {
				$dataRow['Kgs'] = "";
				$dataRow['Value'] = "";
				$dataRow['AvgRate'] = "";
			} else {

				$Req['PlantID'] = $CurrentPlantID;
				$Req['CenterID'] = $CurrentCenterID;
				$Req['Species'] = $CurrentSpecies;
				//$dataRow = $this->getSpeciesWiseData($Req);
				$dataRow = $this->getArrivalDatagrp($Req);
			}

			$Data[] = $dataRow;
		}
		return $Data;
	}

	function getArrivalDatagrp($Req)
	{

		$Qry = $this->getFilter($Req);

		$AryArrivalPlant = $this->utilController->getRmProcessingCenters($Req['PlantID']);

		$dataRow = null;
		$sqlFields = "round(sum(`TotalWeight`),3) as Kgs, round(sum(`TotalAmount`),2) as Value";
		$sqlQuery = "SELECT  $sqlFields FROM `RmOverAllArrival` as OAL WHERE  $Qry ";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();

		if ($stmt->rowCount() > 0) {
			$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
			$AvgRate = round($dataRow['Value'] / $dataRow['Kgs'], 2);
			if ($AvgRate == 0) {
				$dataRow['AvgRate'] = '';
			} else {
				$dataRow['AvgRate'] = sprintf('%0.2f', ($dataRow['Value'] / $dataRow['Kgs']));
				$dataRow['Kgs'] = sprintf('%0.3f', ($dataRow['Kgs']));
				$dataRow['Value'] = sprintf('%0.2f', ($dataRow['Value']));

				//$dataRow['AvgRate'] = 'NA';
			}
		} else {
			$dataRow['Kgs'] = "";
			$dataRow['Value'] = "";
			$dataRow['AvgRate'] = "";
		}

		$dataRow['Center'] = $AryArrivalPlant['StationName'];
		return $dataRow;
	}



	function getCenterTotal($Req)
	{
		$Qry = $this->getFilter($Req);

		$sqlFields = "round(sum(TotalWeight),2) as WeightTotal ,
						 round(sum(TotalAmount),2) as ValueTotal";
		$sqlQuery = "SELECT  $sqlFields FROM `RmOverAllArrival` as OAL
				   left join RmPurchaseStations as RPS on OAL.CenterID=RPS.PurchaseStationID
				   left join RmProcessingStations as PLANT on PLANT.ProcessingStationID=OAL.PlantID
				   WHERE  $Qry GROUP by OAL.CenterID,OAL.Species";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

		return $dataRow;
	}

	function getPlantTotal() {}

	function getSubTotal($RequestBody)
	{

		$sqlQuery = "SELECT ProcessingStationID FROM `RmProcessingStations` ORDER BY SortOrder ASC;";
		$stmtPS = $this->conn->prepare($sqlQuery);
		$stmtPS->execute();
		$userReq = $this->userFilterRequest;
		while ($dataRowPs = $stmtPS->fetch(PDO::FETCH_ASSOC)) {
			$PlantID = $dataRowPs['ProcessingStationID'];
			$RequestBody['PlantID'] = $PlantID;
			$RequestBody['CenterID'] = $userReq['CenterID'];
			$RequestBody['Species'] = $userReq['Species'];
			$data = $this->getArrivalDatagrp($RequestBody);
			$subTotal[] = $data;
		}

		return $subTotal;
	}

	function getGrandTotal($RequestBody)
	{

		$Qry = $this->getFilter($RequestBody);

		$sqlFields = "round(sum(TotalWeight),2) as WeightTotal ,
		  				round(sum(TotalAmount),2) as ValueTotal";
		$sqlQuery = "SELECT  $sqlFields FROM `RmOverAllArrival` as OAL
					left join RmPurchaseStations as RPS on OAL.CenterID=RPS.PurchaseStationID
					left join RmProcessingStations as PLANT on PLANT.ProcessingStationID=OAL.PlantID
					WHERE  $Qry GROUP by OAL.Species";
		// WHERE  $Qry";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

		$dataRow['WeightTotal'] = sprintf('%0.3f', ($dataRow['WeightTotal']));
		$dataRow['ValueTotal'] = sprintf('%0.2f', ($dataRow['ValueTotal']));
		//$dataRow['ValueTotal']='number_format($dataRow['ValueTotal'],2)';

		return $dataRow;
	}


	function getGrandTotalOverAll($RequestBody)
	{

		$Qry = $this->getFilter($RequestBody);

		$sqlFields = "CONCAT('Ho ',OAL.Species) as Species,round(sum(TotalWeight),3) as WeightTotal ,round(sum(TotalAmount),2) as ValueTotal";
		$sqlQuery = "SELECT  $sqlFields FROM `RmOverAllArrival` as OAL
					left join RmPurchaseStations as RPS on OAL.CenterID=RPS.PurchaseStationID
					left join RmProcessingStations as PLANT on PLANT.ProcessingStationID=OAL.PlantID
				 WHERE  $Qry GROUP by OAL.Species order by  Species desc";
		//  WHERE  $Qry GROUP by OAL.CenterID,OAL.Species order by  Species desc";

		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$TotalWeight = 0;
		$TotalValue = 0;
		$TotalAvgRate = 0;
		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$AvgRate = $dataRow['ValueTotal'] / $dataRow['WeightTotal'];
			$WeightTotal = $dataRow['WeightTotal'];
			$ValueTotal = $dataRow['ValueTotal'];

			$dataRow['AvgRate'] = sprintf('%0.2f', (round($AvgRate, 2)));
			$dataRow['WeightTotal'] = sprintf('%0.3f', ($WeightTotal));
			$dataRow['ValueTotal'] = sprintf('%0.2f', ($ValueTotal));
			//$dataRow['ValueTotal'] ='NA';
			$TotalWeight += $WeightTotal;
			$TotalValue += $ValueTotal;
			$TotalAvgRate += $AvgRate;

			$ArySummery[] = $dataRow;
		}

		// $AryTotal = $this->getGrandTotal($RequestBody);
		$dataRow['Species'] = 'Total';
		// $dataRow['WeightTotal'] = sprintf('%0.3f', ($AryTotal['WeightTotal']));
		// $dataRow['ValueTotal'] = sprintf('%0.2f', ($AryTotal['ValueTotal']));
		// $dataRow['AvgRate'] = sprintf('%0.3f', round(($AryTotal['ValueTotal'] / $AryTotal['WeightTotal']), 2));

		$dataRow['WeightTotal'] = sprintf('%0.3f', ($TotalWeight));
		$dataRow['ValueTotal'] = sprintf('%0.2f', ($TotalValue));
		$dataRow['AvgRate'] = sprintf('%0.3f', round($TotalValue / $TotalWeight, 2));
		$ArySummery[] = $dataRow;
		return $ArySummery;
	}


	function getFilter($orgReq)
	{

		$userFilterReq = $this->userFilterRequest;
		$RequestBody = $orgReq;
		$ArrivalDate = $userFilterReq['ArrivalDate'];
		$ArrivalFromDate = trim($userFilterReq['ArrivalFromDate']);

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

		return $Qry;
	}
}
