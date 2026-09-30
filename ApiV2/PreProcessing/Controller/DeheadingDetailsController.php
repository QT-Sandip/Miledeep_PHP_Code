<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class DeheadingDetailsController
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

	public function getDeheadingDetails($RequestBody)
	{
		$ArrivalID = $RequestBody['ArrivalID'];
		$TransactionDate = $RequestBody['TransactionDate'];
		$DeheadingData = $this->getDeheadingData($ArrivalID,$TransactionDate, $RequestBody['RequestFrom'], $RequestBody['ConsolidateLotCount']);



		$Data['Error'] = null;
		$Data['Msg'] = ' Posted Successful';


		$Data['Data'] = $DeheadingData;

		return $Data;
	}

	public function postDeleteDeheading($RequestBody)
	{
		$ArrivalID = $RequestBody['ArrivalID'];
		$BreakUpID = $RequestBody['BreakUpID'];
		$TransactionDate = $RequestBody['TransactionDate'];
		$existingDeheading = $this->auditLogger->fetchRowById('PpcDeheading', 'DHID', $BreakUpID);

		$sqlQuery = "DELETE FROM `PpcDeheading` WHERE DHID='$BreakUpID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();



		$ArrivalID = $RequestBody['ArrivalID'];
		$DeheadingData = $this->getDeheadingData($ArrivalID,$TransactionDate, $RequestBody['RequestFrom'], $RequestBody['ConsolidateLotCount']);



		$Data['Error'] = null;
		$Data['Msg'] = 'Deleted  Successfully';
		$Data['Data'] = $DeheadingData;
		$this->auditLogger->logDelete('PpcDeheading', $BreakUpID, $existingDeheading, $RequestBody, [
			'ReferenceNo' => $ArrivalID,
			'Remarks' => 'PreProcessing deheading deleted',
		]);


		return $Data;
	}

	public function postDeheadingDetails($RequestBody)
	{

		//	var_dump($RequestBody);
		$PostedUserID = 1;
		//$ArrivalNumber = $RequestBody['ArrivalNumber'];
		$ArrivalID = $RequestBody['ArrivalID'];
		$PlantID = $RequestBody['Plant'];
		$StationID = $RequestBody['Center'];
		$ProcessingDate = $RequestBody['TransactionDate'];
		$Shift = $RequestBody['Shift'];
		$Supervisor = $RequestBody['Supervisor'];
		$CheckingCount = $RequestBody['HeadonCountAfterChecking'];
		$HLCount = $RequestBody['HLCount'];
		$HONCount = isset($RequestBody['HONCount'])?$RequestBody['HONCount']:"";
		$HLQuantity = $RequestBody['HLQuantity'];
		$HonWeight = $RequestBody['HonQuantity'];
		$NetCount = $RequestBody['CountNetYield'];
		$IceConsumption = $RequestBody['IceConsumption'];
		$Remarks = $RequestBody['Remarks'];

		$AryCurrentDate = getCurrentDateTime();

		$CreatedDateTime = $AryCurrentDate['TimeStamp'];

		if($ArrivalID != 'melting'){
			$Fields = 'PurchaseWeight,ProductionWeight,';
			$GroupBy = '';
			$LoNumbers = $ArrivalID;
			if($RequestBody['RequestFrom'] == 'V2' && $RequestBody['ConsolidateLotCount'] == 0){
				$Fields = 'SUM(PurchaseWeight) PurchaseWeight,SUM(ProductionWeight) ProductionWeight,';
				$GroupBy = ' GROUP BY RmReferenceID,PurchaseCount';

				$sqlLoNumbers = "SELECT GROUP_CONCAT(RLD2.`RmLotID`) AS RmLotID
									FROM RmLotDetatils RLD 
									JOIN RmLotDetatils RLD2 ON RLD2.RmReferenceID = RLD.RmReferenceID and RLD2.PurchaseCount = RLD.PurchaseCount
									WHERE RLD.`RmLotID` IN($ArrivalID)";
				$stmtLoNumbers = $this->conn->prepare($sqlLoNumbers);
				$stmtLoNumbers->execute();
				$LoNumbers = $stmtLoNumbers->fetch(PDO::FETCH_ASSOC)['RmLotID'];
			}

		
			$sqlCheckWeight = "SELECT $Fields
			(SELECT sum(WtAfterDH) as HeadlessWeight FROM `PpcDeheading`  WHERE LotID IN($LoNumbers)  AND CreatedFrom=0) as HeadlessWeight ,
			(SELECT sum(HonWeight) as HeadonWeight FROM `PpcDeheading`  WHERE LotID IN($LoNumbers)  AND CreatedFrom=0) as HeadonWeight 
			FROM `RmLotDetatils` WHERE RmLotID IN($LoNumbers)
			$GroupBy";
			$stmtCheckingWeight = $this->conn->prepare($sqlCheckWeight);
			$stmtCheckingWeight->execute();
			$dataRowCheckingWeight = $stmtCheckingWeight->fetch(PDO::FETCH_ASSOC);



			$PurchaseWeight = $dataRowCheckingWeight['PurchaseWeight'];
			$ProductionWeight = $dataRowCheckingWeight['ProductionWeight'];
			$HeadlessWeight = $dataRowCheckingWeight['HeadlessWeight'] + $HLQuantity;
			$DeheadingHonWeight = $dataRowCheckingWeight['HeadonWeight'] + $HonWeight;
		}else{
			$HeadlessWeight = $HLQuantity;
				$DeheadingHonWeight = $HonWeight;
		}

		if ($ProductionWeight > 0) {
			$HeadonWeight = $ProductionWeight;
		} else {
			$HeadonWeight = $PurchaseWeight;
		}
		if($ArrivalID != 'melting'){
			$condition = round((float)$DeheadingHonWeight, 2) <= round((float)$HeadonWeight, 2) && round((float)$HLQuantity, 2) <= round((float)$HonWeight, 2);
			//$condition = $DeheadingHonWeight <= $HeadonWeight && $HLQuantity <= $HonWeight;
		}else{
			$condition = round((float)$HLQuantity, 2) <= round((float)$HonWeight, 2);
			//$condition = $HLQuantity <= $HonWeight;
		}
		//echo $condition;die;
		if ($condition) {

			if ($HeadlessWeight <= $HeadonWeight || $condition) {



				$stmt = false;

				//if ($this->Error == null && $ArrivalID == '') {
				$sqlQuery = "INSERT INTO `PpcDeheading`(`LotID`, `HonWeight`,`ReceivedWeight`, 
             `ProcessingDate`, `PostedDate`, `WtAfterDH`, `CountAfterDH`, `HonCount`,`ShiftID`, `SupervisorID`, `StationID`, `PlantID`, `UserID`,
             `Remarks`, `NetCount`,`IceConsumption`, `CheckingCount`,CreatedFrom) 
             VALUES ('$ArrivalID','$HonWeight','$HonWeight','$ProcessingDate','$CreatedDateTime','$HLQuantity','$HLCount','$HONCount','$Shift','$Supervisor',
             '$StationID','$PlantID','$PostedUserID','$Remarks','$NetCount','$IceConsumption','$CheckingCount',0)";

				$stmt = $this->conn->prepare($sqlQuery);
				$stmt->execute();

				//$ArrivalID = $this->conn->lastInsertId();

				//}



				if ($stmt) {
					$lastInsertId = $this->conn->lastInsertId();
					$createdDeheading = $this->auditLogger->fetchRowById('PpcDeheading', 'DHID', $lastInsertId);
					$this->auditLogger->logInsert('PpcDeheading', $lastInsertId, $createdDeheading, $RequestBody, [
						'ReferenceNo' => $ArrivalID,
						'Remarks' => 'PreProcessing deheading created',
					]);

					$DeheadingData = $this->getDeheadingData($ArrivalID,$ProcessingDate, $RequestBody['RequestFrom'], $RequestBody['ConsolidateLotCount']);
					$Data['Error'] = null;
					$Data['Msg'] = 'Success';
					$Data['Data'] = $DeheadingData;
					return $Data;
				} else {
					return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
				}
			} else {
				$DeheadingData = $this->getDeheadingData($ArrivalID,$ProcessingDate, $RequestBody['RequestFrom'], $RequestBody['ConsolidateLotCount']);
				$Data['Error'] = null;
				$Data['Msg'] = 'HL Weight Exceeds';
				$Data['Data'] = $DeheadingData;
				return $Data;
			}
		} else {
			$DeheadingData = $this->getDeheadingData($ArrivalID,$ProcessingDate, $RequestBody['RequestFrom'], $RequestBody['ConsolidateLotCount']);
			$Data['Error'] = null;
			$Data['Msg'] = ' Weight Exceeds';
			$Data['Data'] = $DeheadingData;
			return $Data;
		}
	}



	function getDeheadingData($ArrivalID,$ProcessingData, $RequestFrom, $ConsolidateLotCount)
	{


		$dataRow = null;
		if($ArrivalID!= "melting"){
			$Fields = 'PurchaseWeight,ProductionWeight,';
			$GroupBy = '';
			$LoNumbers = $ArrivalID;
			if($RequestFrom == 'V2' && $ConsolidateLotCount == 0){
				$Fields = 'SUM(PurchaseWeight) PurchaseWeight,SUM(ProductionWeight) ProductionWeight,';
				$GroupBy = ' GROUP BY RLD.RmReferenceID,RLD.PurchaseCount';

				$sqlLoNumbers = "SELECT GROUP_CONCAT(RLD2.`RmLotID`) AS RmLotID
									FROM RmLotDetatils RLD 
									JOIN RmLotDetatils RLD2 ON RLD2.RmReferenceID = RLD.RmReferenceID and RLD2.PurchaseCount = RLD.PurchaseCount
									WHERE RLD.`RmLotID` IN($ArrivalID)";
				$stmtLoNumbers = $this->conn->prepare($sqlLoNumbers);
				$stmtLoNumbers->execute();
				$LoNumbers = $stmtLoNumbers->fetch(PDO::FETCH_ASSOC)['RmLotID'];
			}
	
			$sqlBalanceWeight = "SELECT $Fields
			(SELECT sum(WtAfterDH) as HeadlessWeight FROM `PpcDeheading`  WHERE LotID IN($LoNumbers)  AND CreatedFrom=0) as HeadlessWeight ,
			(SELECT sum(HonWeight) as HeadonWeight FROM `PpcDeheading`  WHERE LotID IN($LoNumbers)  AND CreatedFrom=0) as HeadonWeight 
			FROM `RmLotDetatils` WHERE RmLotID IN($LoNumbers)
			$GroupBy";
			$stmtBalanceWeight = $this->conn->prepare($sqlBalanceWeight);
			$stmtBalanceWeight->execute();
			$dataRowBalanceWeight = $stmtBalanceWeight->fetch(PDO::FETCH_ASSOC);

			
			
			$PurchaseWeight = $dataRowBalanceWeight['PurchaseWeight'];
			$ProductionWeight = $dataRowBalanceWeight['ProductionWeight'];
			

			if ($ProductionWeight > 0) {
				$HeadonWeight = $ProductionWeight;
			} else {
				$HeadonWeight = $PurchaseWeight;
				}
				// echo $HeadonWeight;
				
			$BalanceWeight = $HeadonWeight - $dataRowBalanceWeight['HeadonWeight'];
		}else{
			
			$BalanceWeight = 0;

		}
		if($ArrivalID!= "melting"){
			if($ArrivalID!=null && $ArrivalID!='Select'){
				$query = "AND LotID IN($LoNumbers)";
			}else{
				$query = "AND ProcessingDate='$ProcessingData'";
			}
		}else{
			$query = "AND LotID='$ArrivalID' AND ProcessingDate='$ProcessingData'";
		}
		
		$sqlFields = "DHID,ProcessingDate,LotNumber as ArrivalNumber,StationName,UnitName,UserName,Name ,CheckingCount,CountAfterDH,HonCount as HONCount,HonWeight,
		WtAfterDH,NetCount,IceConsumption,PDH.StationID,PDH.PlantID,PDH.LotID,PDH.ShiftID,PDH.SupervisorID,
        PDH.Remarks";
		$sqlQuery = "SELECT  $sqlFields FROM `PpcDeheading`   PDH 
        left join PpcVAStations  PVS on PVS.StationID=PDH.StationID
        left join RmUsersList  RUL on RUL.ID=PDH.SupervisorID        
		left join RmLotDetatils as RLD on RLD.RmLotID=PDH.LotID
		left join RmArrivalDetatils  RAD on RAD.RmArrivalID=RLD.RmReferenceID
        left join PpcOptions  PPO on PPO.OptID=PDH.ShiftID
        left join RmProcessingStations  RPS on RPS.ProcessingStationID=PDH.PlantID
         where 1=1 AND PDH.CreatedFrom=0 $query  ORDER BY `DHID` asc";//0 means V2
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$RowCount = $stmt->rowCount();
		//  $ArrayData[]='';
		$TotalHLQuantity = 0;
		$i = 0;
		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
			// $Yield = round((($dataRow['WtAfterDH'] / $dataRow['HonWeight']) * 100), 2);
			$Yield = ($dataRow['WtAfterDH'] / $dataRow['HonWeight']) * 100;

			$DeheadingData['SNo'] = ++$i;
			$DeheadingData['BreakUpID'] = $dataRow['DHID'];
			$DeheadingData['TransactionDate'] = $this->utilController->getFormatedDate($dataRow['ProcessingDate']);
			if($dataRow['LotID'] != 0 && $ArrivalID!= "melting"){
			$DeheadingData['LotNo'] = $dataRow['ArrivalNumber'];
		}else{
			$DeheadingData['LotNo'] = "melting";
		}
			$DeheadingData['Plant'] = $dataRow['StationName'];
			$DeheadingData['Center'] = $dataRow['UnitName'];
			$DeheadingData['UserName'] = $dataRow['UserName'];
			$DeheadingData['Shift'] = $dataRow['Name'];
			$DeheadingData['HeadonCountAfterCheckingCount2'] = $dataRow['CheckingCount'];
			$DeheadingData['HLCount'] = $dataRow['CountAfterDH'];
			$DeheadingData['HONCount'] = $dataRow['HONCount'];
			$DeheadingData['HLQuantityKgs'] = $dataRow['WtAfterDH'];
			$DeheadingData['CountNetYield'] = $dataRow['NetCount'];
			$DeheadingData['HonQuantity'] = $dataRow['HonWeight'];
			$DeheadingData['IceConsumption'] = $dataRow['IceConsumption'];
			$DeheadingData['Remarks'] = $dataRow['Remarks'];
			$DeheadingData['CenterID'] = $dataRow['StationID'];
			$DeheadingData['PlantID'] = $dataRow['PlantID'];
			if($dataRow['LotID'] != 0 && $ArrivalID!= "melting"){
			$DeheadingData['ArrivalID'] = $dataRow['LotID'];
		}else{
			$DeheadingData['ArrivalID'] = "melting";
		}
			
			$DeheadingData['ShiftID'] = $dataRow['ShiftID'];
			$DeheadingData['SupervisorID'] = $dataRow['SupervisorID'];
			$DeheadingData['Yield'] = round($Yield, 2);


			$ArrayData[] = $DeheadingData;
			$TotalHLQuantity += $DeheadingData['HLQuantityKgs'];
			$TotalHOnQuantity += $DeheadingData['HonQuantity'];
			$AvgYield += $Yield;
			// $Count += $DeheadingData['SNo'];

		}
		$ArrayTotals['TotalHLQuantity'] = $TotalHLQuantity;
		$ArrayTotals['TotalHOnQuantity'] = $TotalHOnQuantity;
		$ArrayTotals['AvgYield'] = round($AvgYield / $RowCount, 2);
		// echo	$Count;  exit;


		$Data['BalanceWeight'] = $BalanceWeight;
		$Data['DeheadingData'] = $ArrayData;
		$Data['Totals'] = $ArrayTotals;

		return $Data;
	}

}
