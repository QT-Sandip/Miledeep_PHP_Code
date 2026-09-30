<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class DeheadingTrackingDetailsController
{
	private $conn;
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

	public function getDeheadingDetails($RequestBody)
	{
		$TrackID = $RequestBody['TrackID'];
		$PlantID = $RequestBody['PlantID'];
		$IsHONPacking = $RequestBody['IsHONPacking'];
		$MergeHonLoose = $RequestBody['mergeHonLoose'];
		$DeheadingData = $this->getDeheadingData($TrackID,$IsHONPacking);
		$DeheadingBreakUpDetails = $this->getDeheadingBreakUp($TrackID, $PlantID,$IsHONPacking,$MergeHonLoose);
		$Data['Error'] = $this->Error;
		$Data['Msg'] = $this->Msg;
		$Data['DeheadingDetails'] = $DeheadingData;
		$Data['DeheadingBreakUp'] = $DeheadingBreakUpDetails;
		return $Data;
	}

	function getDeheadingData($TrackID,$IsHONPacking)
	{

		$HonQuery = '';
		if($IsHONPacking == 1){
			$HonQuery = ' and RRB.HONPacking = 0';
		}
		$sqlFields = "DHID,ProcessingDate,ArrivalNumber,RPS.StationName AS VAStation,UnitName,UserName,Name ,CheckingCount,CountAfterDH,HonWeight,
		WtAfterDH,NetCount,IceConsumption,PDH.StationID,PDH.PlantID,PDH.LotID,PDH.ShiftID,PDH.SupervisorID,RABD.Count as ArrivalCount,
        RABD.Weight as ArrivalWeight,RABD.Variety as ArrivalVariety,RAD.RmArrivalID as ArrivalID,ReceivalBreakupID,RAD.ArrivalDate,
        PDH.Remarks,PDH.GradingPlantID,RPS2.FullName,RPS2.StationName AS GradingStation";

		 $sqlQuery = "SELECT  $sqlFields FROM RmArrivalBreakUpDetails as RABD 
		LEFT JOIN RmArrivalDetatils as RAD on RAD.RmArrivalID=RABD.ArrivalID 
		LEFT JOIN RmReceivingBreakup  as RRB on RRB.ArrivalBreakupID=RABD.ArrivalBrkUpID
		LEFT JOIN PpcDeheading as PDH on  PDH.TrackID =RRB.ReceivingID  AND PDH.CreatedFrom=1
		LEFT JOIN PpcVAStations PVS on PVS.StationID=PDH.StationID 
		LEFT JOIN RmUsersList RUL on RUL.ID=PDH.SupervisorID 
		LEFT JOIN PpcOptions PPO on PPO.OptID=PDH.ShiftID 
		LEFT JOIN RmProcessingStations RPS on RPS.ProcessingStationID=PDH.PlantID
		LEFT JOIN RmProcessingStations RPS2 on RPS2.ProcessingStationID=PDH.GradingPlantID
		WHERE RRB.ReceivingID='$TrackID' $HonQuery ORDER BY `DHID` asc";

		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$RowCount = $stmt->rowCount();

		// $errorInfo = $stmt->errorInfo();
		// var_dump($errorInfo);

		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

		$i = 0;
		$Yield = ($dataRow['WtAfterDH'] / $dataRow['HonWeight']) * 100;

		$GradingPlantID = $dataRow['GradingPlantID'] ? $dataRow['GradingPlantID'] : '0';
		$DeheadingData['ArrivalDate'] = $dataRow['ArrivalDate'];
		$DeheadingData['TransactionDate'] = $dataRow['ProcessingDate'];
		$DeheadingData['Remarks'] = $dataRow['Remarks'];
		$DeheadingData['CenterID'] = $dataRow['StationID'];
		$DeheadingData['PlantID'] = $dataRow['PlantID'];
		$DeheadingData['GradingPlantID'] = $GradingPlantID;
		if ($GradingPlantID != 0) {
			$DeheadingData['GradingPlantName'] = $dataRow['FullName'] . ' - ' . $dataRow['GradingStation'];
		} else {
			$DeheadingData['GradingPlantName'] = '';
		}
		$DeheadingData['ShiftID'] = $dataRow['ShiftID'];
		$DeheadingData['SupervisorID'] = $dataRow['SupervisorID'];

		return $DeheadingData;
		;
	}

	function getDeheadingBreakUp($TrackID,$PlantId,$IsHONPacking,$MergeHonLoose)
	{
		$dataRow = null;

		$HonQuery = '';
		if($IsHONPacking == 1){
			$HonQuery = ' and RRB.HONPacking = 0';
		}

		$GroupQuery = "";
		if($MergeHonLoose == 1){
			$sqlFields = "DHID,ProcessingDate,ArrivalNumber,CheckingCount,CountAfterDH,HonWeight,
						WtAfterDH,NetCount,IceConsumption,PDH.StationID,PDH.PlantID,PDH.LotID,PDH.ShiftID,PDH.SupervisorID, GROUP_CONCAT(RABD.Count) as ArrivalCount,
						SUM(RABD.Weight) as ArrivalWeight,RABD.Variety as ArrivalVariety,RAD.RmArrivalID as ArrivalID,ReceivalBreakupID,
						PDH.Remarks,GROUP_CONCAT(CONCAT(RAD.Species, '-', RABD.Variety)) as OverallVariety,GROUP_CONCAT(CONCAT(RABD.ArrivalBrkUpID,'-',RABD.Variety)) AS ArrivalBrkUpID
						,PDH.TotalDeheadingWeight,PDH.CheckingCount2,PDH.HonQty,RRB.ReceivingQty";
			$GroupQuery = " GROUP BY RAD.ArrivalNumber,PDH.TotalDeheadingWeight,PDH.CheckingCount2";
			$sqlQuery = "SELECT  $sqlFields FROM RmArrivalDetatils AS RAD
					LEFT JOIN RmArrivalBreakUpDetails AS RABD ON RAD.RmArrivalID = RABD.ArrivalID 
					LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalID = RABD.ArrivalID 
					left join PpcDeheading as PDH on  PDH.TrackBreakUpID =RRB.ReceivalBreakupID  AND PDH.CreatedFrom=1
					left join PpcVAStations PVS on PVS.StationID=PDH.StationID 
					left join RmUsersList RUL on RUL.ID=PDH.SupervisorID 
					left join PpcOptions PPO on PPO.OptID=PDH.ShiftID 
					left join RmProcessingStations RPS on RPS.ProcessingStationID=PDH.PlantID
					where RRB.ReceivingID='$TrackID' and PDH.LotID != 0  $HonQuery $GroupQuery";
		}else{
			$sqlFields = "DHID,ProcessingDate,ArrivalNumber,CheckingCount,CountAfterDH,HonWeight,
						WtAfterDH,NetCount,IceConsumption,PDH.StationID,PDH.PlantID,PDH.LotID,PDH.ShiftID,PDH.SupervisorID,RABD.Count as ArrivalCount,
						RABD.Weight as ArrivalWeight,RABD.Variety as ArrivalVariety,RAD.RmArrivalID as ArrivalID,ReceivalBreakupID,RABD.Variety as OverallVariety,
						PDH.Remarks,PDH.TotalDeheadingWeight,PDH.CheckingCount2,PDH.HonQty,RRB.ReceivingQty";
			$GroupQuery = "";
			$sqlQuery = "SELECT  $sqlFields FROM RmArrivalBreakUpDetails as RABD 
					left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RABD.ArrivalID 
					left join RmReceivingBreakup  as RRB on RRB.ArrivalBreakupID=RABD.ArrivalBrkUpID
					left join PpcDeheading as PDH on  PDH.TrackBreakUpID =RRB.ReceivalBreakupID  AND PDH.CreatedFrom=1
					left join PpcVAStations PVS on PVS.StationID=PDH.StationID 
					left join RmUsersList RUL on RUL.ID=PDH.SupervisorID 
					left join PpcOptions PPO on PPO.OptID=PDH.ShiftID 
					left join RmProcessingStations RPS on RPS.ProcessingStationID=PDH.PlantID
					where RRB.ReceivingID='$TrackID' and PDH.LotID != 0   $HonQuery";
		}
		

	//    echo $sqlQuery;exit;
	// where RRB.ReceivingID='$TrackID'";


		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		// $errorInfo = $stmt->errorInfo();
		// var_dump($errorInfo);
		$RowCount = $stmt->rowCount();
		//  $ArrayData[]='';
		$TotalHLQuantity = 0;
		$i = 0;
		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

			$DeleteAccess = 1;
			$EditAccess = 1;

			$ReceivalBreakupID = $dataRow['ReceivalBreakupID'];
			$sqlGradingCount = "SELECT count(*) as GradingCount FROM `PpcHLGradingDetails`
			WHERE TrackID='$TrackID' and TrackBreakUpID='$ReceivalBreakupID'  AND  CreatedFrom=1";
			$stmtGradingCount = $this->conn->prepare($sqlGradingCount);
			$stmtGradingCount->execute();
			$dataRowGradingCount = $stmtGradingCount->fetch(PDO::FETCH_ASSOC);
			if ($dataRowGradingCount['GradingCount'] > 0) {
				$DeleteAccess = 0;
				$EditAccess = 0;
			}
			$Yield = $HLYield = 0.00;
			if($dataRow['HonWeight'] != 0.00){
				$Yield = ($dataRow['WtAfterDH'] / $dataRow['HonWeight']) * 100;
			}
			if($dataRow['TotalDeheadingWeight'] != 0.00 && !empty($dataRow['ReceivingQty'])){
				$HLYield = ($dataRow['TotalDeheadingWeight'] / $dataRow['ReceivingQty']) * 100;
			}

			$DeheadingData['SNo'] = ++$i;
			$DeheadingData['ReceivalBreakUpID'] = $dataRow['ReceivalBreakupID'];
			$DeheadingData['DeheadingBreakUpID'] = $dataRow['DHID'];
			$DeheadingData['TransactionDate'] = $this->utilController->getFormatedDate($dataRow['ProcessingDate']);
			$DeheadingData['ArrivalID'] = $dataRow['ArrivalID'];
			$DeheadingData['LotNo'] = $dataRow['ArrivalNumber'];
			$DeheadingData['HLCount'] = $dataRow['CountAfterDH'];
			$DeheadingData['OverallVariety'] = $dataRow['OverallVariety'];
			$DeheadingData['HLQuantityKgs'] = $dataRow['WtAfterDH'];
			$DeheadingData['HonQuantity'] = $dataRow['HonWeight'];
			$DeheadingData['ArrivalWeight'] = $dataRow['ArrivalWeight'];
			$DeheadingData['ArrivalCount'] = $dataRow['ArrivalCount'];
			$DeheadingData['ArrivalVariety'] = $dataRow['ArrivalVariety'];
			$DeheadingData['TotalDeheadingWeight'] = $dataRow['TotalDeheadingWeight'];
			$DeheadingData['CheckingCount2'] = $dataRow['CheckingCount2'];
			$DeheadingData['HonQty'] = $dataRow['HonQty'];
			$DeheadingData['Yield'] = round($Yield, 2);
			$DeheadingData['HLYield'] = round($HLYield, 2);
			$DeheadingData['DeleteAccess'] = $DeleteAccess;
			$DeheadingData['EditAccess'] = $EditAccess;
			$ArrayData[] = $DeheadingData;
			$TotalHLQuantity += $DeheadingData['HLQuantityKgs'];
			$TotalHOnQuantity += $DeheadingData['HonQuantity'];
			$TotalArrivalWeight += $dataRow['ArrivalWeight'];
			$AvgYield += $Yield;
		}
		$ArrayTotals['TotalHLQuantity'] = $TotalHLQuantity;
		$ArrayTotals['TotalHOnQuantity'] = $TotalHOnQuantity;
		$ArrayTotals['TotalArrivalWeight'] = round($TotalArrivalWeight, 3);
		$ArrayTotals['AvgYield'] = round($AvgYield / $RowCount, 2);

		// echo print_r($ArrayData);
		// exit;
		$Data['DeheadingData'] = $ArrayData;
		$Data['Totals'] = $ArrayTotals;

		return $Data;
	}

	public function postDeheadingDetails($RequestBody)
	{ 
		$PostedUserID = 1;
		$ArrivalID = $RequestBody['ArrivalID'];
		// $PlantID = $RequestBody['Plant'];
		$PlantID = ($RequestBody['Plant'] != '' ? $RequestBody['Plant'] : 0); // sriram
		$GradingPlantID = $RequestBody['GradingPlantID'];
		$StationID = $RequestBody['Center'];
		$ProcessingDate = $RequestBody['TransactionDate'];
		$Shift = $RequestBody['Shift'];
		$Supervisor = $RequestBody['Supervisor'];
		$HLCount = $RequestBody['HLCount'];
		$HLQuantity = $RequestBody['HLQuantity'];
		$HonWeight = $RequestBody['HonQuantity'];
		$Remarks = $RequestBody['Remarks'];
		$TrackID = $RequestBody['TrackID'];
		$TrackBreakUpID = $RequestBody['ReceivalBreakUpID'];
		$DeheadingBreakUpID = $RequestBody['DeheadingBreakUpID'];
		$Type = $RequestBody['Type'];
		$AryCurrentDate = getCurrentDateTime();
		$CreatedDateTime = $AryCurrentDate['TimeStamp'];
		
		$totalDeheadingWeight = $RequestBody['totalDeheadingWeight'];
		$CheckingCount2 = $RequestBody['CheckingCount2'];
		$HONQuantity = $RequestBody['HONQuantity'];

		if ($RequestBody['GradingPlantID'] == 0) {
			// $GradingPlantID = $RequestBody['Plant'];
			$GradingPlantID = $PlantID; //sriram
		} else {
			$GradingPlantID = $RequestBody['GradingPlantID'];
		}

		$SqlDeheadingCount = "SELECT count(*) as ReceivingCount,(SELECT count(*) FROM `PpcDeheading` WHERE TrackID='$TrackID' AND CreatedFrom=1  )as DeheadingCount , 
		(SELECT GROUP_CONCAT(ReceivalBreakupID) FROM `RmReceivingBreakup` WHERE ReceivingID='$TrackID' and ReceivalBreakupID not in (SELECT TrackBreakUpID FROM `PpcDeheading` WHERE TrackID='$TrackID'  AND CreatedFrom=1 ))as TrackBreakUpIDs 
		FROM `RmReceivingBreakup`WHERE ReceivingID='$TrackID'";

		$stmtDeheadingCount = $this->conn->prepare($SqlDeheadingCount);
		$stmtDeheadingCount->execute();
		$RowDeheadingCount = $stmtDeheadingCount->fetch(PDO::FETCH_ASSOC);
		$DeheadingCount = $RowDeheadingCount['DeheadingCount'];
		$ReceivingCount = $RowDeheadingCount['ReceivingCount'];
		$TrackBreakUpIDs = $RowDeheadingCount['TrackBreakUpIDs'];

		$stmt = false;

		if ($DeheadingCount == 0) {
			$stmt = $this->PostInsertDeheadingData($RequestBody, '');
		} else if ($Type == '' && $DeheadingCount > 0) {

			$TransferPlant = $RequestBody['TransferPlant'];

			if ($TransferPlant == true) {
				//$existingRows = $this->auditLogger->fetchAll("SELECT * FROM PpcDeheading WHERE TrackID = ? AND CreatedFrom = 1", [$TrackID]);

				$sqlTransfer = "SELECT GradingPlantID,PlantID FROM PpcDeheading 
				WHERE `TrackID`='$TrackID'  AND CreatedFrom=1  ORDER BY DHID desc LIMIT 1";

				$stmtTransfer = $this->conn->prepare($sqlTransfer);
				$stmtTransfer->execute();

				// $errorInfo = $stmtTransfer->errorInfo();
				// var_dump($errorInfo);

				$dataCount = $stmtTransfer->fetch(PDO::FETCH_ASSOC);
				$GradingPlantOld = $dataCount['GradingPlantID'];
				$PlantIDOld = $dataCount['PlantID'];

				if (($GradingPlantID != $GradingPlantOld) && ($GradingPlantID != $PlantIDOld)) {

					$sqlTransferUpdate = "UPDATE `PpcDeheading` SET `ProcessingDate`='$ProcessingDate',
					`PostedDate`='$CreatedDateTime',
					`ShiftID`='$Shift',`SupervisorID`='$Supervisor',`StationID`='$StationID',
					`UserID`='$PostedUserID',`Remarks`='$Remarks',				
					`GradingPlantID`='$GradingPlantID' WHERE `TrackID`='$TrackID'";

					$stmt = $this->conn->prepare($sqlTransferUpdate);
					$stmt->execute();

					// $errorInfo = $stmtTransferUpdate->errorInfo();
					// var_dump($errorInfo);

					$this->Error = null;
					$this->Msg = "Transfer To Plant Success";
					//$updatedRows = $this->auditLogger->fetchAll("SELECT * FROM PpcDeheading WHERE TrackID = ? AND CreatedFrom = 1", [$TrackID]);
					$this->auditLogger->logUpdate('PpcDeheading', $TrackID, $existingRows, $updatedRows, $RequestBody, [
						'ReferenceNo' => $TrackID,
						'Remarks' => 'PreProcessing deheading transfer plant updated',
					]);
				} else {
					$this->Error = "Deheading plant and Transfer Plant are should not Same";
					$this->Msg = "Deheading plant and Transfer Plant are should not Same";
				}
			} else {
				//$existingRows = $this->auditLogger->fetchAll("SELECT * FROM PpcDeheading WHERE TrackID = ? AND CreatedFrom = 1", [$TrackID]);

				$sqlQuery = "UPDATE `PpcDeheading` SET `ProcessingDate`='$ProcessingDate',
				`PostedDate`='$CreatedDateTime',
				`ShiftID`='$Shift',`SupervisorID`='$Supervisor',`StationID`='$StationID',
				`PlantID`='$PlantID',`UserID`='$PostedUserID',`Remarks`='$Remarks',`GradingPlantID`='$GradingPlantID'					
				WHERE `TrackID`='$TrackID'";

				$stmt = $this->conn->prepare($sqlQuery);
				$stmt->execute();

				// $errorInfo = $stmt->errorInfo();
				// var_dump($errorInfo);
				$this->Error = null;
				$this->Msg = "Form Data Updated Successfully";
				//$updatedRows = $this->auditLogger->fetchAll("SELECT * FROM PpcDeheading WHERE TrackID = ? AND CreatedFrom = 1", [$TrackID]);
				$this->auditLogger->logUpdate('PpcDeheading', $TrackID, $existingRows, $updatedRows, $RequestBody, [
					'ReferenceNo' => $TrackID,
					'Remarks' => 'PreProcessing deheading header updated',
				]);
			}

			if ($DeheadingCount != $ReceivingCount) {
				$this->PostInsertDeheadingData($RequestBody, $TrackBreakUpIDs);
			}
		} else {
			$existingBreakup = $this->auditLogger->fetchOne("SELECT * FROM PpcDeheading WHERE TrackBreakUpID = ? AND DHID = ?", [$TrackBreakUpID, $DeheadingBreakUpID]);

			$Query = "";
			if ($DeheadingBreakUpID != '') {
				$Query = " and `DHID`='$DeheadingBreakUpID'";
			}

			$sqlQuery = "UPDATE `PpcDeheading` SET `HonWeight`='$HonWeight',
						`ReceivedWeight`='$HonWeight',`PostedDate`='$CreatedDateTime',
						`WtAfterDH`='$HLQuantity',`CountAfterDH`='$HLCount',
						`UserID`='$PostedUserID',
						TotalDeheadingWeight='$totalDeheadingWeight'	,
						CheckingCount2='$CheckingCount2',
						HonQty='$HONQuantity'								
						 WHERE `TrackBreakUpID`='$TrackBreakUpID' $Query";


			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();
			$this->Error = null;
			$this->Msg = "BreakUp Data Posted Successfully";
			$updatedBreakup = $this->auditLogger->fetchOne("SELECT * FROM PpcDeheading WHERE TrackBreakUpID = ? AND DHID = ?", [$TrackBreakUpID, $DeheadingBreakUpID]);
			$this->auditLogger->logUpdate('PpcDeheading', $DeheadingBreakUpID, $existingBreakup, $updatedBreakup, $RequestBody, [
				'ReferenceNo' => $TrackID,
				'Remarks' => 'PreProcessing deheading breakup updated',
			]);
		}

		if ($stmt) {
			$DeheadingData = $this->getDeheadingDetails($RequestBody);
			return $DeheadingData;
		} else {
			return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
		}
	}

	public function PostInsertDeheadingData($RequestBody, $TrackBreakUpIDs)
	{
		$PostedUserID = 1;
		// $PlantID = $RequestBody['Plant'];
		$PlantID = ($RequestBody['Plant'] != '' ? $RequestBody['Plant'] : 0); // sriram
		$GradingPlantID = $RequestBody['GradingPlantID'];
		$StationID = $RequestBody['Center'];
		$ProcessingDate = $RequestBody['TransactionDate'];
		$Shift = $RequestBody['Shift'];
		$Supervisor = $RequestBody['Supervisor'];
		$Remarks = $RequestBody['Remarks'];
		$TrackID = $RequestBody['TrackID'];
		$TrackBreakUpID = $RequestBody['ReceivalBreakUpID'];
		$DeheadingBreakUpID = $RequestBody['DeheadingBreakUpID'];
		$Type = $RequestBody['Type'];
		$AryCurrentDate = getCurrentDateTime();
		$CreatedDateTime = $AryCurrentDate['TimeStamp'];
		$totalDeheadingWeight = $RowDeheadingCount['totalDeheadingWeight'];
		$CheckingCount2 = $RequestBody['CheckingCount2'];
		$HONQuantity = $RequestBody['HONQuantity'];

		if ($RequestBody['GradingPlantID'] == '' || $RequestBody['GradingPlantID'] == 0) {
			// $GradingPlantID = $RequestBody['Plant'];
			$GradingPlantID = $PlantID; // sriram
		} else {
			$GradingPlantID = $RequestBody['GradingPlantID'];
		}
		// echo $GradingPlantID;
		$Query = '';

		if ($TrackBreakUpIDs != '') {
			$Query = " and ReceivalBreakupID in (" . $TrackBreakUpIDs . ")";
		}

		$SqlTrackDetails = "SELECT RmLotID as ArrivalID,ReceivalBreakupID FROM `RmReceivingBreakup` AS RRB 
		 join RmLotDetatils as RLD on RLD.BreakUpID=RRB.ArrivalBreakupID WHERE ReceivingID='$TrackID' $Query "; // uma 15-04-2024

		$stmtTrackDetails = $this->conn->prepare($SqlTrackDetails);
		$stmtTrackDetails->execute();

		while ($RowTrackDetails = $stmtTrackDetails->fetch(PDO::FETCH_ASSOC)) {
			$ArrivalID = $RowTrackDetails['ArrivalID'];
			$TrackBreakUpID = $RowTrackDetails['ReceivalBreakupID'];


			// GradingPlantID
			$sqlQuery = "INSERT INTO `PpcDeheading`(`LotID`, `ProcessingDate`, 
			 `PostedDate`,  `ShiftID`, `SupervisorID`, `StationID`, `PlantID`, `UserID`, `Remarks`, 
			 `TrackID`, `TrackBreakUpID`,`GradingPlantID`,TotalDeheadingWeight,CheckingCount2,HonQty)
			 VALUES ('$ArrivalID','$ProcessingDate','$CreatedDateTime','$Shift','$Supervisor',
			 '$StationID','$PlantID','$PostedUserID','$Remarks','$TrackID','$TrackBreakUpID','$GradingPlantID','$totalDeheadingWeight','$CheckingCount2','$HONQuantity')";


			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();

			// $errorInfo = $stmt->errorInfo();
			// var_dump($errorInfo);

			$this->Error = null;
			$this->Msg = "Form Data Posted Successfully";
			if ($stmt) {
				$lastInsertId = $this->conn->lastInsertId();
				$createdDeheading = $this->auditLogger->fetchRowById('PpcDeheading', 'DHID', $lastInsertId);
				$this->auditLogger->logInsert('PpcDeheading', $lastInsertId, $createdDeheading, $RequestBody, [
					'ReferenceNo' => $TrackID,
					'Remarks' => 'PreProcessing deheading tracking row created',
				]);
			}
		}

		return $stmt;
	}
	public function postDeleteDeheading($RequestBody)
	{
		$TrackID = $RequestBody['TrackID'];
		$DeheadingBreakUpID = $RequestBody['DeheadingBreakUpID'];
		$AryCurrentDate = getCurrentDateTime();
		$CreatedDateTime = $AryCurrentDate['TimeStamp'];
		$existingBreakup = $this->auditLogger->fetchRowById('PpcDeheading', 'DHID', $DeheadingBreakUpID);


		// $sqlQuery = "DELETE FROM `PpcDeheading` WHERE DHID='$DeheadingBreakUpID'";

		//Sriram
		$sqlQuery = "UPDATE `PpcDeheading` SET `HonWeight`='0',
						`ReceivedWeight`='0',`PostedDate`='$CreatedDateTime',
						`WtAfterDH`='0',`CountAfterDH`='0',TotalDeheadingWeight='0',CheckingCount2='0',HonQty='0',
						`UserID`='1'					
						 WHERE DHID='$DeheadingBreakUpID'";

		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$this->Error = null;
		$this->Msg = "Breakup Deleted Successfully";
		$updatedBreakup = $this->auditLogger->fetchRowById('PpcDeheading', 'DHID', $DeheadingBreakUpID);
		$this->auditLogger->logUpdate('PpcDeheading', $DeheadingBreakUpID, $existingBreakup, $updatedBreakup, $RequestBody, [
			'ReferenceNo' => $TrackID,
			'Remarks' => 'PreProcessing deheading breakup zeroed',
		]);
		$TrackID = $RequestBody['TrackID'];


		$DeheadingData = $this->getDeheadingDetails($RequestBody);

		return $DeheadingData;
	}
}
