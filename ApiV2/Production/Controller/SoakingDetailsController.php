<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class SoakingDetailsController
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
		$this->auditLogger = new CentralAuditLogger($this->conn, 'Production');
	}

	public function getSoakingDetails($RequestBody)
	{
		$ArrivalID = $RequestBody['ArrivalID'];
		$SoakingData = $this->getSoakingData($ArrivalID);



		$Data['Error'] = null;
		$Data['Msg'] = ' Posted Successful';

		$Data['Data'] = $SoakingData;

		return $Data;
	}

	public function postDeleteSoaking($RequestBody)
	{
		$ArrivalID = $RequestBody['ArrivalID'];
		$BreakUpID = $RequestBody['BreakUpID'];
		$existingSoaking = $this->auditLogger->fetchRowById('ProdSoakingDetails', 'SckID', $BreakUpID);


		$sqlQuery = "DELETE FROM `ProdSoakingDetails` WHERE SckID='$BreakUpID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();



		$ArrivalID = $RequestBody['ArrivalID'];

		$SoakingData = $this->getSoakingData($ArrivalID);



		$Data['Error'] = null;
		$Data['Msg'] = 'Deleted  Successfully';
		$Data['Data'] = $SoakingData;
		$this->auditLogger->logDelete('ProdSoakingDetails', $BreakUpID, $existingSoaking, $RequestBody, [
			'ReferenceNo' => $ArrivalID,
			'Remarks' => 'Production soaking deleted',
		]);


		return $Data;
	}

	public function postSoakingDetails($RequestBody)
	{


		$PostedUserID = 1;

		$ArrivalID = $RequestBody['ArrivalID'];
		$Source = $RequestBody['SourceStation'];
		$PlantID = $RequestBody['Plant'];
		$SoakingDate = $RequestBody['TransactionDate'];
		$Shift = $RequestBody['Shift'];
		$SoakInTime = $RequestBody['StartTime'];
		$TimePeriod = $RequestBody['TimePeriod'];
		$SaltQty = $RequestBody['Salt'];
		$Remarks = $RequestBody['Remarks'];
		$ChemicalQty = $RequestBody['ChemicalQuantity'];
		$Variety = $RequestBody['Variety'];
		$TubNumber = $RequestBody['TubNo'];
		$SoakInCount = $RequestBody['InCount']; // Dropdown value
		$SoakingInCount = $RequestBody['SoakingInCount']; // entry value
		$SoakInQty = $RequestBody['InQuantity'];
		$SoakOutCount = $RequestBody['OutCount'];
		$SoakOutQty = $RequestBody['OutQuantity'];
		$HLBreakupID = $RequestBody['HLBreakupID'];

		$AryCurrentDate = getCurrentDateTime();

		$CreatedDateTime = $AryCurrentDate['TimeStamp'];
		$SqlWeight = "SELECT sum(HLWeight) as GradingHLWeight,
		(SELECT sum(SoakInQty)  FROM `ProdSoakingDetails` WHERE LotID='$ArrivalID' AND SoakInCount='$SoakInCount')  as SoakingINWeight
		FROM `PpcHLGradingBreakup` WHERE DataID='$HLBreakupID' and LotID='$ArrivalID'";
		$stmtWeight = $this->conn->prepare($SqlWeight);
		$stmtWeight->execute();
		$dataRowWeight = $stmtWeight->fetch(PDO::FETCH_ASSOC);

		$GradingHLWeight = $dataRowWeight['GradingHLWeight'];
		$SoakingINWeight = $dataRowWeight['SoakingINWeight'] + $SoakInQty;


		if ($SoakingINWeight <= $GradingHLWeight) {
			//if ($this->Error == null && $ArrivalID == '') {
			$sqlQuery = "INSERT INTO `ProdSoakingDetails`(`SoakingDate`, `LotID`, `Source`, `PlantID`, `Variety`, `TubNumber`, `SaltQty`, 
			`ChemicalQty`, `SoakInTime`, `TimePeriod`, `SoakInCount`, `SoakInQty`, `SoakingInCount`,
			 `SoakOutCount`, `SoakOutQty`,  `PostedUserID`, `Remarks`, `Shift`, `PostedTimeDate`) 
			VALUES ('$SoakingDate','$ArrivalID','$Source','$PlantID','$Variety','$TubNumber','$SaltQty','$ChemicalQty',
			'$SoakInTime','$TimePeriod','$SoakInCount','$SoakInQty', '$SoakingInCount','$SoakOutCount','$SoakOutQty','$PostedUserID','$Remarks',
			'$Shift','$CreatedDateTime')";

			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();



			if ($stmt) {
				$lastInsertId = $this->conn->lastInsertId();
				$createdSoaking = $this->auditLogger->fetchRowById('ProdSoakingDetails', 'SckID', $lastInsertId);
				$this->auditLogger->logInsert('ProdSoakingDetails', $lastInsertId, $createdSoaking, $RequestBody, [
					'ReferenceNo' => $ArrivalID,
					'Remarks' => 'Production soaking created',
				]);


				$SoakingData = $this->getSoakingData($ArrivalID);
				$Data['Error'] = null;
				$Data['Msg'] = 'Success';
				$Data['Data'] = $SoakingData;
				return $Data;
			} else {
				return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
			}
		} else {
			$SoakingData = $this->getSoakingData($ArrivalID);
			$Data['Error'] = null;
			$Data['Msg'] = 'Weight Exceeds';
			$Data['Data'] = $SoakingData;
			return $Data;
		}
	}



	function getSoakingData($ArrivalID)
	{


		$dataRow = null;


		$sqlFields = "SckID,SoakingDate,LotNumber as ArrivalNumber,StationName,UnitName,Shift,PDH.Source,PDH.PlantID,PDH.LotID, 
		PDH.Remarks,SoakInTime,TimePeriod,SaltQty,ChemicalQty,Variety,TubNumber,SoakInCount,SoakingInCount,SoakInQty,
		SoakOutCount,SoakOutQty,ShiftName,PHVL.ShortName";
		$sqlQuery = "SELECT  $sqlFields FROM `ProdSoakingDetails`   PDH 
        left join PpcVAStations PVS on PVS.StationID=PDH.Source 
		left join RmLotDetatils RAD on RAD.RmLotID=PDH.LotID 
		left join AdminShiftDetails ASD on ASD.ShiftID=PDH.Shift 
        left join PpcHLVAVarietyList PHVL on PHVL.VarietyID=PDH.Variety 
		left join RmProcessingStations RPS on RPS.ProcessingStationID=PDH.PlantID
        where LotID='$ArrivalID' ORDER BY `SckID` asc";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		//  $ArrayData[]='';
		$TotalSoakOutQty = 0;
		$TotalSoakInQty = 0;
		$i = 0;
		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

			$SoakingData['SNo'] = ++$i;
			$SoakingData['BreakUpID'] = $dataRow['SckID'];
			$SoakingData['TransactionDate'] = $this->utilController->getFormatedDate($dataRow['SoakingDate']);
			$SoakingData['LotNo'] = $dataRow['ArrivalNumber'];
			$SoakingData['Plant'] = $dataRow['StationName'];
			$SoakingData['Center'] = $dataRow['UnitName'];
			$SoakingData['SourceStationID'] = $dataRow['Source'];
			$SoakingData['Shift'] = $dataRow['ShiftName'];
			$SoakingData['Remarks'] = $dataRow['Remarks'];
			$SoakingData['PlantID'] = $dataRow['PlantID'];
			$SoakingData['ArrivalID'] = $dataRow['LotID'];
			$SoakingData['SoakInTime'] = $dataRow['SoakInTime'];
			$SoakingData['TimePeriod'] = $dataRow['TimePeriod'];
			$SoakingData['SaltQty'] = $dataRow['SaltQty'];
			$SoakingData['ChemicalQty'] = $dataRow['ChemicalQty'];
			$SoakingData['Variety'] = $dataRow['ShortName'];
			$SoakingData['TubNumber'] = $dataRow['TubNumber'];
			$SoakingData['SoakInCount'] = $dataRow['SoakInCount'];
			$SoakingData['SoakingInCount'] = $dataRow['SoakingInCount'];
			$SoakingData['SoakInQty'] = $dataRow['SoakInQty'];
			$SoakingData['SoakOutCount'] = $dataRow['SoakOutCount'];
			$SoakingData['SoakOutQty'] = $dataRow['SoakOutQty'];
			//$SoakingData['ShiftID']=$dataRow['ShiftID'];
			//$SoakingData['SupervisorID']=$dataRow['SupervisorID'];


			$ArrayData[] = $SoakingData;
			$TotalSoakInQty += $SoakingData['SoakInQty'];
			$TotalSoakOutQty += $SoakingData['SoakOutQty'];
		}
		$ArrayTotals['TotalSoakInQty'] = $TotalSoakInQty;
		$ArrayTotals['TotalSoakOutQty'] = $TotalSoakOutQty;
		$Data['HLCounts'] = $this->getLotBreakUpDetails($ArrivalID);
		$Data['SoakingData'] = $ArrayData;
		$Data['Totals'] = $ArrayTotals;

		return $Data;
	}

	function getLotBreakUpDetails($ArrivalID)
	{


		$sqlFields = "HLCount,HLWeight,DataID,NoCrates,MixedID";
		$SqlHLData = "Select $sqlFields From PpcHLGradingBreakup WHERE LotID='$ArrivalID'";
		$stmt = $this->conn->prepare($SqlHLData);
		$stmt->execute();
		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {


			$LotBreakUpData['HLBreakupID'] = $dataRow['DataID'];
			$LotBreakUpData['Title'] = $dataRow['HLCount'] . ' - ' . $dataRow['HLWeight'];
			$LotBreakUpData['HLCount'] = $dataRow['HLCount'];
			$LotBreakUpData['HLWeight'] = $dataRow['HLWeight'];
			// $LotBreakUpData['DataID'] = $dataRow['DataID'];
			// $LotBreakUpData['NoCrates'] = $dataRow['NoCrates'];
			// $LotBreakUpData['MixedID'] = $dataRow['MixedID'];

			$ArrayData[] = $LotBreakUpData;
		}

		return $ArrayData;
	}
}
