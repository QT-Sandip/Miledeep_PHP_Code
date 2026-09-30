<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once '../App/Controller/AppUtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';


class ReceivalTrackIDDetailsController
{
	private $conn;
	private $utilController;
	private $Error = null;
	private $Msg = '';
	private $appUtilController;
	private $restApi;
	private $auditLogger;


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

	function getArrivalDetails($RequestBody)
	{
	
		$Token = $this->getToken();

		$ClientId = $Token['ClientID'];
		$rowClientDetails = $this->appUtilController->getClientConfig($ClientId);
		$CratePageAccess = $rowClientDetails['CratePageAccess'];

		$Data['RmReceivingDetails'] = $this->getReceivalDetails($RequestBody, $CratePageAccess);
		$Data['RmReceivingBreakUp'] = $this->getReceivalBreakuDetails($RequestBody);
		$Data['Totals'] = $this->getBreakupTotals($RequestBody);
		$Data['IsHONPacking'] = $RequestBody['IsHONPacking'];
		if($RequestBody['ReceivingID'] != '' && $RequestBody['ReceivingID'] != 'null' && $RequestBody['ReceivingID'] != null){
			$sqlSelect = "SELECT COUNT(*) AS total FROM PpcDeheading WHERE TrackID=" . $RequestBody['ReceivingID'];
			$stmtSelect = $this->conn->prepare($sqlSelect);
			$stmtSelect->execute();
			$dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
			if($dataRow['total'] > 0){
				$Data['deleteAccess'] = 0;
			}else{
				$Data['deleteAccess'] = 1;
			}

		}else{
			$Data['deleteAccess'] = 1;
		}
		$responseData['Error'] = $this->Error;
		$responseData['Msg'] = $this->Msg;
		$responseData['Data'] = $Data;
		$ArrivalBreakupID = $RequestBody['ArrivalBreakupID'];
		$sqlBreakUp = "SELECT RmLotID,BreakUpID FROM RmLotDetatils WHERE BreakUpID=" . $ArrivalBreakupID;
		$stmtBreakUp = $this->conn->prepare($sqlBreakUp);
		$stmtBreakUp->execute();
		$dataBreakUp = $stmtBreakUp->fetch(PDO::FETCH_ASSOC);
		$LotId = $dataBreakUp['RmLotID'];
		$ArrivalID = $RequestBody['ArrivalID'];
		$responseData['LotId'] = $LotId;
		$responseData['ArrivalID'] = $ArrivalID;

		return $responseData;
	}
	function getReceivalDetails($RequestBody, $CratePageAccess)
	{

		$ArrivalDate = $RequestBody['ArrivalDate'];
		$VehicleID = $RequestBody['VehicleID'];
		$ReceivingPlantID = $RequestBody['ReceivingPlantID'];
		$ReceivingID = $RequestBody['ReceivingID'];
		$IsVehicleTrack = $RequestBody['IsVehicleTrack'];

		$Query = '';

		if ($ReceivingID != '' && $ReceivingID != 'null' && $ReceivingID != null) {
			$Query = "RmRD.ReceivingID='$ReceivingID'";
		} else if ($IsVehicleTrack == 0) {
			$Query = "RmRD.ArrivalDate='$ArrivalDate'";
		} else {
			$Query = "RmRD.ArrivalDate='$ArrivalDate' AND RmRD.VehicleID='$VehicleID' ";
		}

		if ($ReceivingPlantID != '') {
			$Query .= " and ReceivingPlantID='$ReceivingPlantID'";
		}

		$sqlFields = "RmRD.ReceivingID, RmRD.ArrivalDate, RmRD.VehicleID, RmRD.VehicleNumber, RmRD.ReceivingPlantID, 
		RmPS.FullName, RmPS.StationName, RmRD.TrackCode, RmRD.TrackNumber,RmRD.SealNo, RmRD.SupervisorID, RmUL.UserName, RmRD.Remarks, 
		 RmPS.ProdCode as PlantCode";

		$sqlQuery = "SELECT  $sqlFields FROM `RmReceivingDetails` AS RmRD
		LEFT JOIN RmProcessingStations AS RmPS ON RmRD.ReceivingPlantID=RmPS.ProcessingStationID
		
		LEFT JOIN RmUsersList AS RmUL ON RmRD.SupervisorID=RmUL.ID 
		WHERE $Query AND (RmRD.TrackCode NOT REGEXP 'REJ') AND ((RmRD.TrackCode NOT REGEXP 'SM') OR ((RmRD.TrackCode REGEXP 'SM') AND RmRD.IsStoreMaterial=0))";

		$stmtSelect = $this->conn->prepare($sqlQuery);
		$stmtSelect->execute();
		$aryTrackDetails = $this->generateTrackCode($RequestBody);
		$TrackNumber = $aryTrackDetails['TrackNumber'];
		$TrackCode = $aryTrackDetails['AutoTrackCode'];

		$ReceivalData['TestNumber'] = $TrackNumber;
		$ReceivalData['TrackCode'] = $TrackCode;
		$ReceivalData['TestCode'] = $TrackCode;

		$i = 0;
		while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

			$ReceivalData['SNo'] = ++$i;
			$ReceivalData['ReceivingID'] = $dataRow['ReceivingID']; // ReceivingID treating as TrackID
			$ReceivalData['ArrivalDate'] = $dataRow['ArrivalDate'];
			$ReceivalData['VehicleID'] = $dataRow['VehicleID'];
			$ReceivalData['VehicleNumber'] = $dataRow['VehicleNumber'];
			$ReceivalData['ReceivingPlantID'] = $dataRow['ReceivingPlantID'];
			$ReceivalData['FullName'] = $dataRow['FullName'];
			$ReceivalData['StationName'] = $dataRow['StationName'];
			$ReceivalData['TrackNumber'] = $dataRow['TrackNumber'];
			$ReceivalData['TrackCode'] = $dataRow['TrackCode'] ? $dataRow['TrackCode'] : $TrackCode;
			$ReceivalData['SealNo'] = $dataRow['SealNo'];
			$ReceivalData['SupervisorID'] = $dataRow['SupervisorID'];
			$ReceivalData['SupervisorName'] = $dataRow['UserName'];
			$ReceivalData['Remarks'] = $dataRow['Remarks'];
			$ReceivalData['CratePageAccess'] = $CratePageAccess;

		}

		return $ReceivalData;
	}

	function getReceivalBreakuDetails($RequestBody)
	{
		$ArrivalDate = $RequestBody['ArrivalDate'];
		$VehicleID = $RequestBody['VehicleID'];
		$PlantID = $RequestBody['ReceivingPlantID'];
		$IsVehicleTrack = $RequestBody['IsVehicleTrack'];
		$IsHONPacking = $RequestBody['IsHONPacking']; // sriram
		$MergeHonLoose = $RequestBody['MergeHonLoose']; // sriram

		// $IsPlantFilter = $RequestBody['IsPlantFilter']; // sriram

		$Query = '';
		// if ($PlantID != '' && $PlantID != 0) {
		// 	$Query = "AND RAD.ProcessingStationID='$PlantID'";
		// }

		// sriram
		if ($PlantID != '' && $PlantID != 0) {
			$Query = "AND RAD.ProcessingStationID='$PlantID'";
		}


		if ($IsVehicleTrack != 0) {
			$Query .= " AND RAD.VehicalID='$VehicleID' ";// and ReceivingPlantID='$ReceivingPlantID' ";
		}

		$HonFields = '';
		if($IsHONPacking == 1){
			$HonFields = ',RRB.HONPacking';
		}
		$GroupQuery = "";
		if($MergeHonLoose == 1){
			$sqlFields = "RAD.ArrivalNumber, GROUP_CONCAT(RABD.Count) as ArrivalCount, SUM(RABD.Weight) as ArrivalWeight, RABD.Variety as ArrivalVariety,GROUP_CONCAT(CONCAT(RAD.Species, '-', RABD.Variety)) as OverallVariety,
			RAD.Species as ArrivalSpecies, RRB.SampleQuantity, RRB.NoofPieces, RRB.ReceivingCount, RRB.ReceivingQty,
			RRB.CountDifference, RRB.QuantityDifference, RRD.Remarks, RmArrivalID as ArrivalID, 
			RRB.ArrivalBreakupID, RRB.ReceivalBreakupID, GROUP_CONCAT(CONCAT(RABD.ArrivalBrkUpID,'-',RABD.Variety)) AS ArrivalBrkUpID, RRD.ReceivingID, RLD.RmLotID";
			$GroupQuery = " GROUP BY RAD.ArrivalNumber";
		}else{
			$sqlFields = "RAD.ArrivalNumber, RABD.Count as ArrivalCount, RABD.Weight as ArrivalWeight, RABD.Variety as ArrivalVariety,CONCAT(RAD.Species, '-', RABD.Variety) as OverallVariety,
			RAD.Species as ArrivalSpecies, RRB.SampleQuantity, RRB.NoofPieces, RRB.ReceivingCount, RRB.ReceivingQty,
			RRB.CountDifference, RRB.QuantityDifference, RRD.Remarks, RmArrivalID as ArrivalID, 
			RRB.ArrivalBreakupID, RRB.ReceivalBreakupID, RABD.ArrivalBrkUpID, RRD.ReceivingID, RLD.RmLotID";
			$GroupQuery = "";
		}

		$sqlQuery = "SELECT $sqlFields $HonFields
		FROM RmArrivalBreakUpDetails AS RABD
		LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID = RABD.ArrivalID
		LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalBreakupID = RABD.ArrivalBrkUpID
		LEFT JOIN RmReceivingDetails AS RRD ON RRD.ReceivingID = RRB.ReceivingID
		LEFT JOIN (
			SELECT *
			FROM (
				SELECT RLD.*,
					ROW_NUMBER() OVER (
						PARTITION BY RLD.RmReferenceID
						ORDER BY
							CASE
								WHEN RLD.BreakUpID IS NOT NULL THEN 1
								ELSE 2
							END
					) AS rn
				FROM RmLotDetatils RLD
			) RankedLots
			WHERE rn = 1
		) AS RLD ON RLD.RmReferenceID = RABD.ArrivalID
				AND (RLD.BreakUpID = RABD.ArrivalBrkUpID OR RLD.BreakUpID IS NULL)
		WHERE RAD.ArrivalDate = '$ArrivalDate' $Query $GroupQuery
		ORDER BY ArrivalNumber ASC";

	
		$stmtSelect = $this->conn->prepare($sqlQuery);
		$stmtSelect->execute();

		$i = 0;
		while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
			$DeleteAccess = 1;
			$EditAccess = 1;

			$TrackID = $dataRow['ReceivingID'];
			$ReceivalBreakupID = $dataRow['ReceivalBreakupID'];

			$sqlDeheadingCount = "SELECT (select count(*)  FROM `PpcDeheading` 
			WHERE TrackID='$TrackID'  and TrackBreakUpID='$ReceivalBreakupID'  and HonWeight!=0)as DeheadingCount,(SELECT count(*)  FROM `PpcHLGradingDetails` 
			WHERE TrackID='$TrackID'  and TrackBreakUpID='$ReceivalBreakupID')as GradingCount,(SELECT count(*)  FROM `ProdFreezingData` 
			WHERE TrackID='$TrackID' AND TubNo=$ReceivalBreakupID) as freezingCount from RmReceivingDetails where ReceivingID='$TrackID'";
			$stmtDeheadingCount = $this->conn->prepare($sqlDeheadingCount);
			$stmtDeheadingCount->execute();
			$dataRowDeheadingCount = $stmtDeheadingCount->fetch(PDO::FETCH_ASSOC);
			if ($dataRowDeheadingCount['DeheadingCount'] > 0 || $dataRowDeheadingCount['GradingCount'] > 0 || $dataRowDeheadingCount['freezingCount'] > 0) {
				$DeleteAccess = 0;
				$EditAccess = 0;
			}

			$ArrivalBreakUpId = $dataRow['ArrivalBrkUpID'];
			$BreakUpIds = explode(',', $ArrivalBreakUpId); // this should not be wrapped again in array
			if (count($BreakUpIds) > 1) {
				foreach ($BreakUpIds as $id) {
					$varieties = explode('-' , $id);
					if($varieties[1] == 'Head On'){
						$ArrivalBreakUpId = $varieties[0];
					}
				}
			}



			$ReceivalBreakUpData['SNo'] = ++$i;
			$ReceivalBreakUpData['ArrivalID'] = $dataRow['ArrivalID'];
			$ReceivalBreakUpData['ArrivalBreakupID'] = $ArrivalBreakUpId;
			$ReceivalBreakUpData['ReceivalBreakupID'] = $dataRow['ReceivalBreakupID'];
			$ReceivalBreakUpData['ArrivalNumber'] = $dataRow['ArrivalNumber'];
			$ReceivalBreakUpData['ArrivalCount'] = $dataRow['ArrivalCount'];
			$ReceivalBreakUpData['ArrivalWeight'] = $dataRow['ArrivalWeight'];
			$ReceivalBreakUpData['ArrivalVariety'] = $dataRow['ArrivalVariety'];
			$ReceivalBreakUpData['OverallVariety'] = $dataRow['OverallVariety'];
			$ReceivalBreakUpData['ArrivalSpecies'] = $dataRow['ArrivalSpecies'];
			$ReceivalBreakUpData['SampleQuantity'] = ($dataRow['SampleQuantity'] == 0) ? ' ' : $dataRow['SampleQuantity'];
			$ReceivalBreakUpData['NoofPieces'] = round(($dataRow['NoofPieces'] == 0) ? ' ' : $dataRow['NoofPieces']);
			$ReceivalBreakUpData['ReceivingCount'] = ($dataRow['ReceivingCount'] == 0) ? ' ' : $dataRow['ReceivingCount'];
			$ReceivalBreakUpData['ReceivingQty'] = ($dataRow['ReceivingQty'] == 0) ? ' ' : $dataRow['ReceivingQty'];
			$ReceivalBreakUpData['CountDifference'] = round($dataRow['ReceivingCount'] - $dataRow['ArrivalCount'], 3);
			$ReceivalBreakUpData['QuantityDifference'] = round($dataRow['ReceivingQty'] - $dataRow['ArrivalWeight'], 3);
			$ReceivalBreakUpData['Remarks'] = $dataRow['Remarks'];
			$ReceivalBreakUpData['LotID'] = $dataRow['RmLotID']; //uma

			if($IsHONPacking == 1){
				$ReceivalBreakUpData['HONPacking'] = $dataRow['HONPacking'];
			}
			$ReceivalBreakUpData['DeleteAccess'] = $DeleteAccess;
			$ReceivalBreakUpData['EditAccess'] = $EditAccess;

			$ArrayBreakUpData[] = $ReceivalBreakUpData;
		}

		return $ArrayBreakUpData;
	}

	public function getBreakupTotals($RequestBody)
	{
		$ArrivalDate = $RequestBody['ArrivalDate'];
		$VehicleID = $RequestBody['VehicleID'];
		$PlantID = $RequestBody['ReceivingPlantID'];

		if ($PlantID != '' && $PlantID != 0) {
			$Query = "AND RAD.ProcessingStationID='$PlantID'";
		}
		

		$sqlFields = "SUM(Weight) AS TotalWeight, SUM(SampleQuantity) AS TotalSampQty, SUM(ReceivingQty) AS TotalRecvQty";
		$sqlTotals = "SELECT $sqlFields FROM RmArrivalBreakUpDetails AS RABD
		LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID=RABD.ArrivalID 
		LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalBreakupID=RABD.ArrivalBrkUpID
		WHERE RAD.ArrivalDate='$ArrivalDate' AND RAD.VehicalID='$VehicleID' $Query";

		$stmtTotals = $this->conn->prepare($sqlTotals);
		$stmtTotals->execute();

		while ($dataTotals = $stmtTotals->fetch(PDO::FETCH_ASSOC)) {
			$ArrayTotals['TotalWeight'] = $dataTotals['TotalWeight'];
			$ArrayTotals['TotalSampQty'] = $dataTotals['TotalSampQty'];
			$ArrayTotals['TotalRecvQty'] = round($dataTotals['TotalRecvQty'], 3);
			$ArrayTotals['QtyDiff'] = round($dataTotals['TotalRecvQty'] - $dataTotals['TotalWeight'], 3);

			$Totals[] = $ArrayTotals;
		}
		return $Totals;
	}

	// Get Details End

	public function postReceivalData($RequestBody)
	{

		if ($RequestBody['BreakUpdate'] == true) {

			$breakup = $this->postBreakUpData($RequestBody);

			if ($breakup) {
				$this->Error = null;
				$this->Msg = "BreakUp Success";
			} else {
				$this->Error = "BreakUp Failed";
				$this->Msg = "BreakUp Failed";
			}
		} else {

			$staus = $this->postReceivalDetails($RequestBody);

			if ($staus) {
				$this->Error = null;
				$this->Msg = "Updated Successfully";
			} else {
				$this->Error = "Unable To Update Data";
				$this->Msg = "Updated Fail";
			}
		}
		return $this->getArrivalDetails($RequestBody);

	}
	public function postReceivalDetails($RequestBody)
	{

		$ReceivingID = $RequestBody['ReceivingID'];
		$ArrivalDate = $RequestBody['ArrivalDate'];
		$VehicleID = $RequestBody['VehicleID'];
		$VehicleNumber = $RequestBody['VehicleNumber'];
		// $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
		$ReceivingPlantID = ($RequestBody['ReceivingPlantID'] != '' ? $RequestBody['ReceivingPlantID'] : 0); // sriram
		$SealNo = $RequestBody['SealNo'];
		$SupervisorID = $RequestBody['SupervisorID'];
		$Remarks = $RequestBody['Remarks'];
		$existingReceiving = null;

		$sqlSelect = "SELECT ReceivingID FROM RmReceivingDetails WHERE ArrivalDate='$ArrivalDate' AND VehicleID='$VehicleID' and ReceivingPlantID='$ReceivingPlantID' AND IsStoreMaterial=0 AND OutStage=0";
		$stmtSelect = $this->conn->prepare($sqlSelect);
		$stmtSelect->execute();
		$rowCount = $stmtSelect->rowCount();
		if ($rowCount > 0) {
			$existingReceiving = $this->auditLogger->fetchOne("SELECT * FROM RmReceivingDetails WHERE ArrivalDate = ? AND VehicleID = ? AND ReceivingPlantID = ? AND IsStoreMaterial = 0 AND OutStage = 0", [$ArrivalDate, $VehicleID, $ReceivingPlantID]);
			$sqlQuery = "UPDATE RmReceivingDetails SET VehicleNumber='$VehicleNumber',
			ReceivingPlantID='$ReceivingPlantID',SealNo='$SealNo',
			SupervisorID='$SupervisorID', Remarks='$Remarks' 
			WHERE ArrivalDate= '$ArrivalDate' AND VehicleID='$VehicleID'";
		} else {
			$TrackNumber = $this->generateTrackCode($RequestBody)['TrackNumber'];
			$TrackCode = $this->generateTrackCode($RequestBody)['AutoTrackCode'];
			$sqlQuery = "INSERT INTO RmReceivingDetails(ReceivingID, ArrivalDate, VehicleID, VehicleNumber, ReceivingPlantID, 
			TrackNumber, TrackCode, SealNo, SupervisorID, Remarks) 
			VALUES ('$ReceivingID','$ArrivalDate','$VehicleID','$VehicleNumber','$ReceivingPlantID','$TrackNumber',
			'$TrackCode','$SealNo','$SupervisorID', '$Remarks')";
		}
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		if ($stmt) {
			if ($rowCount > 0) {
				$entityId = isset($existingReceiving['ReceivingID']) ? $existingReceiving['ReceivingID'] : $ReceivingID;
				$updatedReceiving = $this->auditLogger->fetchRowById('RmReceivingDetails', 'ReceivingID', $entityId);
				$this->auditLogger->logUpdate('RmReceivingDetails', $entityId, $existingReceiving, $updatedReceiving, $RequestBody, [
					'ReferenceNo' => $entityId,
					'Remarks' => 'PreProcessing receival header updated',
				]);
			} else {
				$lastInsertId = $this->conn->lastInsertId();
				$createdReceiving = $this->auditLogger->fetchRowById('RmReceivingDetails', 'ReceivingID', $lastInsertId);
				$this->auditLogger->logInsert('RmReceivingDetails', $lastInsertId, $createdReceiving, $RequestBody, [
					'ReferenceNo' => $lastInsertId,
					'Remarks' => 'PreProcessing receival header created',
				]);
			}
				
			$Token = $this->getToken();
			$ClientId = $Token['ClientID'];
			$rowClientDetails = $this->appUtilController->getClientConfig($ClientId);
			$CratePageAccess = $rowClientDetails['CratePageAccess'];
			$receivalData = $this->getReceivalDetails($RequestBody,$CratePageAccess);
			$Data['Error'] = null;
			$Data['Msg'] = 'Successfully Added';
			$Data['Data'] = $receivalData['Data'];
			return $Data;
		} else {
			return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
		}
	}
	public function postBreakUpData($RequestBody)
	{
		$ReceivalBreakupID = $RequestBody['ReceivalBreakupID'];

		if ($ReceivalBreakupID == '') {
			$Data = $this->insertBreakUpData($RequestBody);
		} else {
			$Data = $this->updateBreakUpData($RequestBody);
		}
		return $Data;
	}

	public function insertBreakUpData($RequestBody)
	{
		$ReceivingID = $RequestBody['ReceivingID'];
		$ReceivalBreakupID = $RequestBody['ReceivalBreakupID'];
		$ArrivalBreakupID = $RequestBody['ArrivalBreakupID'];
		$ArrivalID = $RequestBody['ArrivalID'];
		$SampleQuantity = $RequestBody['SampleQuantity'];
		$NoofPieces = $RequestBody['NoofPieces'];
		$ReceivingCount = $RequestBody['ReceivingCount'];
		$ReceivingQty = $RequestBody['ReceivingQty'];
		$HONPacking = $RequestBody['HONPacking'];
		$IsHONPacking = $RequestBody['IsHONPacking'];
		$existingBreakup = null;

		$sqlCheck = "SELECT  ArrivalBreakupID FROM RmReceivingBreakup  WHERE ArrivalBreakupID='$ArrivalBreakupID'";
		$stmtCheck = $this->conn->prepare($sqlCheck);
		$stmtCheck->execute();
		$rowCount = $stmtCheck->rowCount();

		if ($rowCount == 0) {
			if($IsHONPacking == 1){
				$sqlInsert = "INSERT INTO RmReceivingBreakup(ReceivalBreakupID,ArrivalID, ArrivalBreakupID, SampleQuantity, ReceivingID,NoofPieces, ReceivingCount, 
				ReceivingQty,HONPacking) 
				VALUES ('$ReceivalBreakupID', '$ArrivalID', '$ArrivalBreakupID', '$SampleQuantity','$ReceivingID','$NoofPieces','$ReceivingCount','$ReceivingQty','$HONPacking')";

			}else{
				$sqlInsert = "INSERT INTO RmReceivingBreakup(ReceivalBreakupID,ArrivalID, ArrivalBreakupID, SampleQuantity, ReceivingID,NoofPieces, ReceivingCount, 
				ReceivingQty) 
				VALUES ('$ReceivalBreakupID', '$ArrivalID', '$ArrivalBreakupID', '$SampleQuantity','$ReceivingID','$NoofPieces','$ReceivingCount','$ReceivingQty')";

			}
			
			$stmtInsert = $this->conn->prepare($sqlInsert);
			$stmtInsert->execute();

			if ($stmtInsert) {
				$lastInsertId = $this->conn->lastInsertId();
				$createdBreakup = $this->auditLogger->fetchRowById('RmReceivingBreakup', 'ReceivalBreakupID', $lastInsertId);
				$this->auditLogger->logInsert('RmReceivingBreakup', $lastInsertId, $createdBreakup, $RequestBody, [
					'ReferenceNo' => $ReceivingID,
					'Remarks' => 'PreProcessing receival breakup created',
				]);
				$Data['Error'] = null;
				$Data['Msg'] = 'Receival BreakUp Added';

				return $Data;
			} else {
				return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
			}
		}
	}

	public function updateBreakUpData($RequestBody)
	{

		$ReceivingID = $RequestBody['ReceivingID'];
		$ReceivalBreakupID = $RequestBody['ReceivalBreakupID'];
		$ArrivalBreakupID = $RequestBody['ArrivalBreakupID'];
		$ArrivalID = $RequestBody['ArrivalID'];
		$SampleQuantity = $RequestBody['SampleQuantity'];
		$NoofPieces = $RequestBody['NoofPieces'];
		$ReceivingCount = $RequestBody['ReceivingCount'];
		$ReceivingQty = $RequestBody['ReceivingQty'];
		$HONPacking = $RequestBody['HONPacking'];
		$IsHONPacking = $RequestBody['IsHONPacking'];
		$existingBreakup = $this->auditLogger->fetchOne("SELECT * FROM RmReceivingBreakup WHERE ReceivalBreakupID = ?", [$ReceivalBreakupID]);

		if($IsHONPacking == 1){
			$sqlUpdate = "UPDATE RmReceivingBreakup SET SampleQuantity='$SampleQuantity',
			NoofPieces='$NoofPieces',ReceivingCount='$ReceivingCount',ReceivingQty='$ReceivingQty', ReceivingID='$ReceivingID',
			ArrivalID='$ArrivalID',ArrivalBreakupID='$ArrivalBreakupID',HONPacking='$HONPacking'
			WHERE ReceivalBreakupID= '$ReceivalBreakupID'";
		}else{
			$sqlUpdate = "UPDATE RmReceivingBreakup SET SampleQuantity='$SampleQuantity',
			NoofPieces='$NoofPieces',ReceivingCount='$ReceivingCount',ReceivingQty='$ReceivingQty', ReceivingID='$ReceivingID',
			ArrivalID='$ArrivalID',ArrivalBreakupID='$ArrivalBreakupID'
			WHERE ReceivalBreakupID= '$ReceivalBreakupID'";
		}

		$stmtUpdate = $this->conn->prepare($sqlUpdate);
		$stmtUpdate->execute();

		if ($stmtUpdate) {
			$updatedBreakup = $this->auditLogger->fetchOne("SELECT * FROM RmReceivingBreakup WHERE ReceivalBreakupID = ?", [$ReceivalBreakupID]);
			$this->auditLogger->logUpdate('RmReceivingBreakup', $ReceivalBreakupID, $existingBreakup, $updatedBreakup, $RequestBody, [
				'ReferenceNo' => $ReceivingID,
				'Remarks' => 'PreProcessing receival breakup updated',
			]);

			$Data['Error'] = null;
			$Data['Msg'] = 'Receival BreakUp Updated';

			return $Data;
		} else {
			return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
		}
	}

	public function deleteBreakUpData($RequestBody)
	{
		$ReceivalBreakupID = $RequestBody['ReceivalBreakupID'];
		$existingBreakup = $this->auditLogger->fetchOne("SELECT * FROM RmReceivingBreakup WHERE ReceivalBreakupID = ?", [$ReceivalBreakupID]);

		$sqlDelete = "DELETE FROM RmReceivingBreakup WHERE ReceivalBreakupID='$ReceivalBreakupID'";
		$stmtDelete = $this->conn->prepare($sqlDelete);
		$stmtDelete->execute();

		$responseData['Error'] = null;
		$responseData['Msg'] = 'Deleted  Successfully';
		$this->auditLogger->logDelete('RmReceivingBreakup', $ReceivalBreakupID, $existingBreakup, $RequestBody, [
			'ReferenceNo' => $ReceivalBreakupID,
			'Remarks' => 'PreProcessing receival breakup deleted',
		]);

		$Token = $this->getToken();
		$ClientId = $Token['ClientID'];
		$rowClientDetails = $this->appUtilController->getClientConfig($ClientId);
		$CratePageAccess = $rowClientDetails['CratePageAccess'];

		$Data['RmReceivingDetails'] = $this->getReceivalDetails($RequestBody,$CratePageAccess);
		$Data['RmReceivingBreakUp'] = $this->getReceivalBreakuDetails($RequestBody);
		$Data['Totals'] = $this->getBreakupTotals($RequestBody);
		$responseData['Data'] = $Data;

		return $responseData;
	}

	public function generateTrackCode($RequestBody)
	{
		$ArrivalDate = $RequestBody['ArrivalDate'];
		$ReceivingPlantID = $RequestBody['ReceivingPlantID'];
		$VehicleID = $RequestBody['VehicleID'];
		$IsVehicleTrack = $RequestBody['IsVehicleTrack'];

		$IsPlantFilter = $RequestBody['IsPlantFilter']; // sriram
		$RemovePlantCode = $RequestBody['RemovePlantCode']; // sriram

		$Query = '';
		if ($IsVehicleTrack != 0) {
			$Query .= " AND RAD.VehicalID='$VehicleID' ";// and ReceivingPlantID='$ReceivingPlantID' ";
		}

		//Sriram
		if ($ReceivingPlantID == '' && $IsPlantFilter == 0) {
			$PlantQuery = " AND ReceivingPlantID=0";
			$sqlFields = "GROUP_CONCAT(DISTINCT LotCode SEPARATOR '-') as CenterCode,GROUP_CONCAT(DISTINCT COALESCE(ProdCode, '') SEPARATOR '-') as PlantCode, CASE WHEN COUNT(DISTINCT RAD.Species) > 1 THEN 'Both'
       			ELSE GROUP_CONCAT(DISTINCT RAD.Species) END AS Species";
			$ProccesingStationQuery = " AND RAD.ProcessingStationID IN (SELECT ProcessingStationID FROM `RmArrivalDetatils`)";
			$Grouping = 'RAD.ArrivalDate';
		} else {
			$PlantQuery = " AND ReceivingPlantID=$ReceivingPlantID";
			$sqlFields = "LotCode as CenterCode,ProdCode as PlantCode, CASE WHEN COUNT(DISTINCT RAD.Species) > 1 THEN 'Both'
        		ELSE GROUP_CONCAT(DISTINCT RAD.Species) END AS Species";
			$ProccesingStationQuery = " AND RAD.ProcessingStationID='$ReceivingPlantID'";
			$Grouping = 'RAD.VehicalID';
		}
		$sqlTrackId = "SELECT max(TrackNumber) as TrackNumber FROM RmReceivingDetails 
		WHERE month(ArrivalDate)=month('$ArrivalDate') and year(ArrivalDate)=year('$ArrivalDate') $PlantQuery AND IsStoreMaterial=0";

		// $sqlTrackId = "SELECT max(TrackNumber) as TrackNumber FROM RmReceivingDetails 
		// WHERE month(ArrivalDate)=month('$ArrivalDate') and year(ArrivalDate)=year('$ArrivalDate') and ReceivingPlantID=$ReceivingPlantID AND IsStoreMaterial=0";

		$stmtTrackId = $this->conn->prepare($sqlTrackId);
		$stmtTrackId->execute();
		$dataRowTrackId = $stmtTrackId->fetch(PDO::FETCH_ASSOC);
		$TrackNumber = $dataRowTrackId['TrackNumber'] + 1;


		// $sqlFields = "LotCode as CenterCode,ProdCode as PlantCode, CASE WHEN COUNT(DISTINCT RAD.Species) > 1 THEN 'Both'
		// ELSE GROUP_CONCAT(DISTINCT RAD.Species) END AS Species";

		//old
		// $sqlSelect = "SELECT $sqlFields FROM RmArrivalDetatils as RAD 
		//  left join RmProcessingStations As RPRS on RAD.ProcessingStationID=RPRS.ProcessingStationID 
		//  left join RmPurchaseStations As RPS on RAD.ArrivalStationID=RPS.PurchaseStationID
		//  WHERE RAD.ArrivalDate='$ArrivalDate'  and RAD.VehicalID='$VehicleID'  and RAD.ProcessingStationID='$ReceivingPlantID' GROUP by RAD.VehicalID
		//  ORDER BY RAD.ArrivalDate";

		//sriram
		$sqlSelect = "SELECT $sqlFields FROM RmArrivalDetatils as RAD 
		left join RmProcessingStations As RPRS on RAD.ProcessingStationID=RPRS.ProcessingStationID 
		left join RmPurchaseStations As RPS on RAD.ArrivalStationID=RPS.PurchaseStationID
		WHERE RAD.ArrivalDate='$ArrivalDate' $Query  $ProccesingStationQuery GROUP by $Grouping
		ORDER BY RAD.ArrivalDate";
		// $sqlSelect = "SELECT $sqlFields FROM RmArrivalDetatils as RAD 
		// left join RmProcessingStations As RPRS on RAD.ProcessingStationID=RPRS.ProcessingStationID 
		// left join RmPurchaseStations As RPS on RAD.ArrivalStationID=RPS.PurchaseStationID
		// WHERE RAD.ArrivalDate='$ArrivalDate' $Query  and RAD.ProcessingStationID='$ReceivingPlantID' GROUP by RAD.VehicalID
		// ORDER BY RAD.ArrivalDate";


		$stmtSelect = $this->conn->prepare($sqlSelect);
		$stmtSelect->execute();
		while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

			$CenterCode = $dataRow['CenterCode'];
			$PlantCode = $dataRow['PlantCode'];
			$Species = $dataRow['Species'];

			$AutoTrackCode = str_replace("O", "0", $TrackNumber);
			$AutoTrackCode = ltrim($AutoTrackCode, "0");
			$AutoTrackCode = preg_replace("/[^0-9]/", "", $AutoTrackCode);
			$AutoTrackCode = sprintf('%04d', $AutoTrackCode);
			if($RemovePlantCode != 1){
				$AutoTrackCode = $Species . '-' . $PlantCode . '-' . $CenterCode . '-' . sprintf('%03d', $TrackNumber);
			}else{
				$AutoTrackCode = $Species . '-' . $PlantCode . '-' . sprintf('%03d', $TrackNumber);
			}

			$ArrayTrackCode['CenterCode'] = $CenterCode;
			$ArrayTrackCode['PlantCode'] = $PlantCode;
			$ArrayTrackCode['ReceivingPlantID'] = $dataRow['ReceivingPlantID'];
			$ArrayTrackCode['TestNumber'] = $dataRow['TrackNumber'];
			$ArrayTrackCode['AutoTrackCode'] = $AutoTrackCode;
			$ArrayTrackCode['TrackNumber'] = $TrackNumber;
		}

		return $ArrayTrackCode;
	}
}
