<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class ValueAdditionTrackingDetailsController
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

	public function getValueAdditionDetails($RequestBody)
	{
		$TrackID = $RequestBody['TrackID'];
		$ValueAdditionData = $this->getValueAdditionData($RequestBody);
		$GradingBreakUpData = $this->getGradingBreakUp($RequestBody);
		$BalanceDetails = $this->getBalanceQty($RequestBody);
		$Data['Error'] = $this->Error;
		$Data['Msg'] = $this->Msg;
		$Data['ValueAdditionDetails'] = $ValueAdditionData;
		$Data['GradingBreakUpDetails'] = $GradingBreakUpData;
		$Data['BalanceDetails'] = $BalanceDetails;
		return $Data;
	}

	function getValueAdditionData($RequestBody)
	{
		$TrackID = $RequestBody['TrackID'];
		$Plant = $RequestBody['PlantID'];
		$Qry = '';
		if ($Plant != '') {
			$Qry = " AND PVA.PlantID = '$Plant'";
		}

		//uma 19-08-2024 store material
		// $SqlIsStoreMaterial = "SELECT IsStoreMaterial FROM `RmReceivingDetails` WHERE ReceivingID='$TrackID'";
		// $stmtIsStoreMaterial = $this->conn->prepare($SqlIsStoreMaterial);
		// $stmtIsStoreMaterial->execute();
		// $dataRowIsStoreMaterial = $stmtIsStoreMaterial->fetch(PDO::FETCH_ASSOC);

		$IsStoreMaterialData = $this->getIsStoreMaterial($TrackID);
		$IsStoreMaterial = $IsStoreMaterialData['IsStoreMaterial'];
		$OutStage = $IsStoreMaterialData['OutStage'];
		//uma 19-08-2024 store material
		// $dataRow = null;
		$sqlFields = "RLD.LotNumber as ArrivalNumber,VLID,ProcessingDate,RPS.StationName AS PlantName,RPS2.StationName AS SoakingPlant,UnitName,UserName,ASD.ShiftName as Shift ,
		CountAftrVA,WtAftrVA,NetCount,Supervisor,IceConsumption,SentCount,SentWeight,SoakingPlantID,
        ReturnWeight,ReceivedWeight,PPOV.ShortName as Variety, PPOV.Species, PVA.LotID ,PVA.VarietyID ,PVA.StationID ,PVA.ShiftID ,PVA.PlantID ,
		PVA.NoofCrates,PVA.Remarks,PVA.GradingBreakupID
		,CASE WHEN PVA.VarietyID != PHGB.`VarietyID` THEN 'text-primary' ELSE '' END AS VarietyStatus,PVA.Grade
		,PVS.UnitName AS VAStation";

		// $sqlQuery = "SELECT  $sqlFields  FROM PpcValueAddition as PVA
		// left join RmLotDetatils as RLD on RLD.RmLotID=PVA.LotID  
		// left join PpcVAStations PVS on PVS.StationID=PVA.StationID 
		// left join RmUsersList RUL on RUL.ID=PVA.Supervisor 
		// left join AdminShiftDetails ASD on ASD.ShiftID=PVA.ShiftID 
		// left join PpcHLVAVarietyList  PPOV on PPOV.VarietyID=PVA.VarietyID
		// left join RmProcessingStations RPS on RPS.ProcessingStationID=PVA.PlantID
		// left join RmProcessingStations RPS2 on RPS2.ProcessingStationID=PVA.SoakingPlantID
		//  where PVA.TrackID='$TrackID' AND PVA.PlantID='$Plant' ORDER BY `VLID` asc";


		//sriram
		$sqlQuery = "SELECT $sqlFields 
		FROM PpcValueAddition AS PVA
		LEFT JOIN RmLotDetatils AS RLD ON RLD.RmLotID = PVA.LotID  
		LEFT JOIN PpcVAStations AS PVS ON PVS.StationID = PVA.StationID 
		LEFT JOIN RmUsersList AS RUL ON RUL.ID = PVA.Supervisor 
		LEFT JOIN AdminShiftDetails AS ASD ON ASD.ShiftID = PVA.ShiftID 
		LEFT JOIN PpcHLVAVarietyList AS PPOV ON PPOV.VarietyID = PVA.VarietyID
		LEFT JOIN RmProcessingStations AS RPS ON RPS.ProcessingStationID = PVA.PlantID
		LEFT JOIN RmProcessingStations AS RPS2 ON RPS2.ProcessingStationID = PVA.SoakingPlantID
		-- LEFT JOIN PpcHLGradingBreakup PHGB ON PHGB.`LotID`= PVA.LotID   AND PHGB.HLCount=PVA.SentCount
		LEFT JOIN PpcHLGradingBreakup PHGB ON PHGB.DataID=PVA.GradingBreakupID
		WHERE PVA.TrackID = '$TrackID' $Qry  AND PVA.CreatedFrom=1 ";

		$sqlQuery .= "ORDER BY VLID ASC";

		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();

		// $errorInfo = $stmt->errorInfo();
		// var_dump($errorInfo);

		$ArrayData = [];

		$TotalHLQuantity = 0;
		$TotalYield = 0;
		$TotalWtAftrVA = 0;
		$i = 0;
		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
			//uma 19-08-2024 store material
			if ($IsStoreMaterial == 1 && $OutStage == 1) {
				$ArrivalNumber = 'Store Material';
			} else {
				if($OutStage == 1){
					$sqlTrack = "SELECT `TrackNumber` FROM `RmReceivingDetails` WHERE `ReceivingID`=$TrackID";
					$stmtTrack = $this->conn->prepare($sqlTrack);
					$stmtTrack->execute();
					$rowTrack = $stmtTrack->fetch(PDO::FETCH_ASSOC);
					$trackNo = $rowTrack['TrackNumber'];
					$ArrivalNumber = 'REJ-' . str_pad($trackNo, 3, '0', STR_PAD_LEFT);
				}else{
					$ArrivalNumber = $dataRow['ArrivalNumber'];
				}
			}
			//uma 19-08-2024 store material
			$RequestBody['LotID'] = $dataRow['LotID'];
			$RequestBody['ValueAdditionID'] = $dataRow['VLID'];
			$RequestBody['GradingBreakupID'] = $dataRow['GradingBreakupID'];
			$RequestBody['IsStoreMaterial'] = $IsStoreMaterial;
			$RequestBody['PlantID'] = $dataRow['SoakingPlantID'];

			$DeleteAccess = $this->getDeleteAccess($RequestBody);
			$WtAftrVA = $dataRow['WtAftrVA'];
			$HLQuantityKgs = $dataRow['SentWeight'];
			$Yield = ($WtAftrVA / $HLQuantityKgs) * 100;

			$ValueAdditionData['SNo'] = ++$i;
			$ValueAdditionData['BreakUpID'] = $dataRow['VLID'];
			$ValueAdditionData['ProcessingDate'] = $this->utilController->getFormatedDate($dataRow['ProcessingDate']);
			$ValueAdditionData['TransactionDate'] = $dataRow['ProcessingDate'];
			$ValueAdditionData['LotNo'] = $ArrivalNumber;
			$ValueAdditionData['Plant'] = $dataRow['PlantName'];
			$ValueAdditionData['Center'] = $dataRow['UnitName'];
			$ValueAdditionData['UserName'] = $dataRow['UserName'];
			$ValueAdditionData['Shift'] = $dataRow['Shift'];
			$ValueAdditionData['ReceivedWeight'] = $dataRow['ReceivedWeight'];
			$ValueAdditionData['ReturnWeight'] = $dataRow['ReturnWeight'];
			$ValueAdditionData['CountAftrVA'] = $dataRow['CountAftrVA'];
			$ValueAdditionData['WtAftrVA'] = $dataRow['WtAftrVA'];
			// $ValueAdditionData['VAOutQuantity'] = $dataRow['WtAftrVA'];
			$ValueAdditionData['HLCount'] = $dataRow['SentCount'];
			$ValueAdditionData['HLQuantityKgs'] = $dataRow['SentWeight'];
			$ValueAdditionData['Yield'] = round($Yield, 2);
			$ValueAdditionData['CountNetYield'] = $dataRow['NetCount'];
			$ValueAdditionData['IceConsumption'] = $dataRow['IceConsumption'];
			$ValueAdditionData['Remarks'] = $dataRow['Remarks'];
			$ValueAdditionData['Variety'] = $dataRow['Variety'];
			$ValueAdditionData['Species'] = $dataRow['Species'];
			$ValueAdditionData['ArrivalID'] = $dataRow['LotID'];
			$ValueAdditionData['VarietyID'] = $dataRow['VarietyID'];
			$ValueAdditionData['StationID'] = $dataRow['StationID'];
			$ValueAdditionData['ShiftID'] = $dataRow['ShiftID'];
			$ValueAdditionData['PlantID'] = $dataRow['PlantID'];
			$ValueAdditionData['SoakingPlantID'] = $dataRow['SoakingPlantID'];
			$ValueAdditionData['SoakingPlant'] = $dataRow['SoakingPlant'] ? $dataRow['SoakingPlant'] : 'NA';
			$ValueAdditionData['Supervisor'] = $dataRow['Supervisor'];
			$ValueAdditionData['GradingBreakupID'] = $dataRow['GradingBreakupID'];
			$ValueAdditionData['NoofCrates'] = $dataRow['NoofCrates'];
			$ValueAdditionData['DeleteAccess'] = $DeleteAccess;
			$ValueAdditionData['EditAccess'] = $DeleteAccess;
			$ValueAdditionData['VarietyStatus'] = $dataRow['VarietyStatus'];
			$ValueAdditionData['Grade'] = $dataRow['Grade'];
			$ValueAdditionData['VAStation'] = $dataRow['VAStation'];

			//sriram
			// if (($dataRow['SoakCount'] + $dataRow['FreezeCount'] + $dataRow['directFreezeCount']) > 0) {
			// 	$ValueAdditionData['disable'] = $dataRow['SoakCount'] . " - " . $dataRow['FreezeCount'] . " - " . $dataRow['directFreezeCount'];
			// } else {
			// 	$ValueAdditionData['disable'] =  $dataRow['SoakCount'] . " - " . $dataRow['FreezeCount'] . " - " . $dataRow['directFreezeCount'];
			// }
			if ($DeleteAccess == 0 || ($dataRow['PlantID'] != $dataRow['SoakingPlantID'])) {
				$ValueAdditionData['disable'] = 1;
			} else {
				$ValueAdditionData['disable'] = 0;
			}

			$ArrayData[] = $ValueAdditionData;
			$TotalHLQuantity += $ValueAdditionData['HLQuantityKgs'];
			$TotalYield += $ValueAdditionData['Yield'];
			$TotalWtAftrVA += $ValueAdditionData['WtAftrVA'];
		}

		$ArrayTotals['TotalHLQuantity'] = round($TotalHLQuantity, 3);
		$ArrayTotals['TotalYield'] = round($TotalYield, 3);
		$ArrayTotals['TotalWtAftrVA'] = round($TotalWtAftrVA, 3);



		//	$Data['HLCounts'] = $this->getLotBreakUpDetails($ArrivalID);
		$Data['ValueAdditionData'] = $ArrayData;
		$Data['Totals'] = $ArrayTotals;

		return $Data;

		//return $this->getValueAdditionDetails($RequestBody);
	}

	function getGradingBreakUp($RequestBody)
	{
		$ReceivalBreakupID = $RequestBody['ReceivalBreakupID'];
		$TrackID = $RequestBody['TrackID'];
		//uma 19-08-2024
		// $sqlQueryIsStoreMaterial = "SELECT  IsStoreMaterial  from RmReceivingDetails WHERE  ReceivingID='$TrackID' ";
		// $stmtIsStoreMaterial = $this->conn->prepare($sqlQueryIsStoreMaterial);
		// $stmtIsStoreMaterial->execute();
		// $dataRowIsStoreMaterial = $stmtIsStoreMaterial->fetch(PDO::FETCH_ASSOC);
		// $IsStoreMaterial = $dataRowIsStoreMaterial['IsStoreMaterial'];
		$IsStoreMaterialData = $this->getIsStoreMaterial($TrackID);
		$IsStoreMaterial = $IsStoreMaterialData['IsStoreMaterial'];
		$OutStage = $IsStoreMaterialData['OutStage'];
		$IsRejInVA = $RequestBody['IsRejInVA'];
		$AddvalueAddition = $RequestBody['AddvalueAddition'];
		$TotalWeight = 0;
		//echo '<pre>';print_r('OutStage='.$OutStage.',IsStoreMaterial='.$IsStoreMaterial);die;
		//uma 19-08-2024
		if ($IsStoreMaterial == 0 && $OutStage == 0) { //uma

			$VAFields = '';
			if($AddvalueAddition == 1){
				$VAFields = ',PHGB.valueAddition';
			}else{
				$VAFields = '';
			}

			$sqlFields = "ReceivalBreakupID,TypeProduct,ShortName,NoCrates,PHGB.LotID as RmArrivalID,TrackBreakUpID,PHGB.Treatment, PHGB.IsBlock,
			HLCount,HLWeight,DataID, PHGB.VarietyID,RAD.ArrivalNumber,PHGB.PackingGrade$VAFields";

			$sqlQuery = "SELECT  $sqlFields FROM PpcHLGradingBreakup AS PHGB 
			LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID  AND PHGD.CreatedFrom=1
			LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ReceivalBreakupID=PHGD.TrackBreakUpID 
			LEFT JOIN RmReceivingDetails as RRD on RRD.ReceivingID=RRB.ReceivingID
			LEFT JOIN PpcHLVAVarietyList AS HLVA ON HLVA.VarietyID=PHGB.VarietyID
			LEFT JOIN RmArrivalDetatils AS RAD ON RRB.ArrivalID=RAD.RmArrivalID
			WHERE RRD.ReceivingID='$TrackID' and HLCount!=0  AND PHGB.CreatedFrom=1 ORDER BY `HLID` ASC	";
			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();

			// $errorInfo = $stmt->errorInfo();
			// var_dump($errorInfo);

			$Data = array();
			$i = 0;

			while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
				// echo print_r($dataRow);
				$GradingBreakUpID = $dataRow['DataID'];
				$RequestBody['GradingBreakUpID'] = $GradingBreakUpID;
				$BalanceDetails = $this->getBalanceQty($RequestBody);

				$GradingData['SNo'] = ++$i;
				$GradingData['ReceivalBreakupID'] = $dataRow['ReceivalBreakupID'];
				$GradingData['GradingBreakUpID'] = $GradingBreakUpID;
				$GradingData['ArrivalID'] = $dataRow['RmArrivalID'];
				$GradingData['NoofCrates'] = $dataRow['NoCrates'];
				$GradingData['CrateQuantity'] = '27';
				$GradingData['HLCount'] = $dataRow['HLCount'];
				$GradingData['GradingQty'] = $dataRow['HLWeight']; // HL Weight as Grading Qty
				$GradingData['VarietyID'] = $dataRow['VarietyID'];
				$GradingData['Variety'] = $dataRow['TypeProduct'] . '-' . $dataRow['ShortName'];
				$GradingData['ArrivalNumber'] = $dataRow['ArrivalNumber'];
				$GradingData['Grade'] = $dataRow['PackingGrade'];
				$GradingData['BalanceQty'] = $BalanceDetails['BalanceQty'];
				$GradingData['IsStoreMaterial'] = $IsStoreMaterial; //uma 2025-04-01

				
				$Treatment = $dataRow['Treatment'];
				$IsBlock = $dataRow['IsBlock'];
				// if ($BalanceDetails['BalanceQty'] > 0) {
				// 	$GradingData['disable'] = 1;
				// } else {
				// 	$GradingData['disable'] = 0;
				// }

				if ($BalanceDetails['BalanceQty'] <= 0 || $BalanceDetails['BalanceCrates'] <= 0 ) {
                    $GradingData['disable'] = 0;
                } else {
                    $GradingData['disable'] = 1;
                }

				$isShow = true;

				if($AddvalueAddition == 1){
					$ValueAddition =  $dataRow['valueAddition'];
					if ($ValueAddition == '1') {
						$isShow = true;	
					}else{
						// if ($IsBlock == '0' && $Treatment !== 'UNT') {
						// 	$isShow = true;
						// } else {
							$isShow = false;
						// }
					}
				}else{
					if ($IsBlock == '0') {
						$isShow = true;
					} else {
						$isShow = false;
					}
				}
				


				if ($isShow) {
					// $sqlBal = "SELECT SUM(SentWeight) AS TotalQty,SUM(NoofCrates) AS TotalCrates FROM PpcValueAddition 
					// WHERE  GradingBreakupID='$GradingBreakUpID'";

					// $stmtBal = $this->conn->prepare($sqlBal);
					// $stmtBal->execute();

					// $rowBal = $stmtBal->fetch(PDO::FETCH_ASSOC);

					// $HLQty = $rowBal['TotalQty'];
					// $Crates = $rowBal['TotalCrates'];

					// $GradingData['BalanceQty'] = $dataRow['HLWeight'] - $HLQty;
					// $GradingData['BalanceCrates'] = $dataRow['NoCrates'] - $Crates;
					// $GradingData['TotalQty'] = $HLQty;
					// $GradingData['TotalCrates'] = $Crates;

					$ArrayData[] = $GradingData;
					$TotalWeight += $dataRow['HLWeight'];
				}
				// }
			}
		} else { //uma 19-08-2024 store material
			$sqlStockOutDetails = "SELECT group_concat( DISTINCT CSD.Grade) as Grade,group_concat( DISTINCT SPV.ShortName) as Variety,
			group_concat( DISTINCT SPV.TypeProduct) as TypeProduct,CSD.VarietyID,
			sum(ReceivingQty) as HLWeight
			,GROUP_CONCAT(DISTINCT CASE WHEN RRB.CsStockID != 0 THEN RRB.CsStockID END) CsStockIDs
			 FROM RmReceivingBreakup as RRB 
			left join CsStockData as CSD on CSD.StockID=RRB.CsStockID 
			left join ScrmPIVarietyList as SPV on CSD.VarietyID=SPV.VarietyID 
			LEFT JOIN RmArrivalDetatils AS RAD ON RRB.ArrivalID=RAD.RmArrivalID
			 WHERE ReceivingID='$TrackID' and ReceivingQty != 0 group by CsStockOutID";

			$stmtStockOutDetails = $this->conn->prepare($sqlStockOutDetails);
			$stmtStockOutDetails->execute();
			$rowStockOutDetails = $stmtStockOutDetails->fetch(PDO::FETCH_ASSOC);

			$sqlBalance = "SELECT SUM(SentWeight) AS TotalQty,SUM(NoofCrates) AS TotalCrates FROM PpcValueAddition 
			WHERE  TrackID='$TrackID'  AND CreatedFrom=1";
			$stmtBalance = $this->conn->prepare($sqlBalance);
			$stmtBalance->execute();

			$rowBalance = $stmtBalance->fetch(PDO::FETCH_ASSOC);
			$HLQty = $rowBalance['TotalQty'];

			// $sqlBal = "SELECT SUM(SentWeight) AS TotalQty,SUM(NoofCrates) AS TotalCrates FROM PpcValueAddition 
			// WHERE  TrackID='$TrackID'";

			// $stmtBal = $this->conn->prepare($sqlBal);
			// $stmtBal->execute();

			// $rowBal = $stmtBal->fetch(PDO::FETCH_ASSOC);
			$lotNumber = '';
			if(!empty($rowStockOutDetails['CsStockIDs'])){
                $lotNumber = $this->getLotNumberByStockId($rowStockOutDetails['CsStockIDs']);
            }

			for ($i = 1; $i <= 300; $i++) {
				if ($rowStockOutDetails['HLWeight'] - $HLQty > 0) {
					$GradingData['HLCount'] = $i . '.00';
					// $HLQty = $rowBal['TotalQty'];
					// $Crates = $rowBal['TotalCrates'];
					// $GradingData['BalanceQty'] = $rowStockOutDetails['HLWeight'] - $HLQty;
					$GradingData['BalanceCrates'] = 1;
					$GradingData['GradingQty'] = $rowStockOutDetails['HLWeight'];
					$GradingData['NoofCrates'] = 1;
					// $GradingData['TotalQty'] = $HLQty;
					// $GradingData['TotalCrates'] = $Crates;
					$GradingData['VarietyID'] = $rowStockOutDetails['VarietyID'];
					$GradingData['IsStoreMaterial'] = $IsStoreMaterial; //uma 2025-04-01

					if($OutStage == 1 && $IsStoreMaterial == 1){
						$GradingData['Variety'] = $rowStockOutDetails['TypeProduct'] . '-' . $rowStockOutDetails['Variety'];
					}else{
						$GradingData['Variety'] = "REJ - ";
					}

					$GradingData['ArrivalNumber'] = $lotNumber;
                	$GradingData['Grade'] = $rowStockOutDetails['Grade'];

					$ArrayData[] = $GradingData;
				}
			}
			$TotalWeight += $rowStockOutDetails['HLWeight'];
			$Grade = $rowStockOutDetails['Grade'];
		} //uma 19-08-2024 store material

		$ArrayTotals['TotalWeight'] = $TotalWeight;
		$Data['Variety'] = $this->getVarietyDetails();
		$Data['GradingData'] = $ArrayData;
		$Data['Totals'] = $ArrayTotals;
		$Data['Grade'] = $Grade;

		return $Data;
	}


	function getVarietyDetails()
	{

		$sqlFields = "VarietyID,ShortName";

		$sqlQueryVariety = "SELECT $sqlFields FROM PpcHLVAVarietyList";

		$stmtVariety = $this->conn->prepare($sqlQueryVariety);
		$stmtVariety->execute();
		while ($dataRowVariety = $stmtVariety->fetch(PDO::FETCH_ASSOC)) {

			$Variety['VarietyID'] = $dataRowVariety['VarietyID'];
			$Variety['ShortName'] = $dataRowVariety['ShortName'];
			$ArrayVariety[] = $Variety;
		}
		return $ArrayVariety;
	}

	function postValueAdditionDetails($RequestBody)
	{

		// var_dump($RequestBody); exit;
		$PostedUserID = 1;
		$BreakUpID = $RequestBody['BreakUpID'];
		$ArrivalID = $RequestBody['ArrivalID'];
		// $PlantID = $RequestBody['PlantID'];
		$PlantID = ($RequestBody['PlantID'] != '' ? $RequestBody['PlantID'] : 0); // sriram
		$StationID = $RequestBody['StationID'];
		$ProcessingDate = $RequestBody['TransactionDate'];
		$Shift = $RequestBody['Shift'];
		$Supervisor = $RequestBody['Supervisor'];
		$WtAftrVA = $RequestBody['WeightAfterVA'];
		$CountAftrVA = $RequestBody['CountAfterVA'];
		$HLCount = $RequestBody['HLCount'];
		$HLQuantity = $RequestBody['HLQuantity'];
		$NoofCrates = $RequestBody['Crates'];

		$NetCount = $RequestBody['CountNetYield'];
		$IceConsumption = $RequestBody['IceConsumption'];
		$Remarks = $RequestBody['Remarks'];
		$ReceivedWeight = $RequestBody['ReceivedWeight'];
		$VarietyID = $RequestBody['VarietyID'];
		$ReturnWeight = $RequestBody['ReturnWeight'];
		$HLBreakupID = $RequestBody['GradingBreakupID'];
		$TrackID = $RequestBody['TrackID'];
		$TrackBreakUpID = $RequestBody['ReceivalBreakupID'];
		// $SoakingPlantID = $RequestBody['SoakingPlantID'];
		$SoakingPlantID = ($RequestBody['SoakingPlantID'] != '' ? $RequestBody['SoakingPlantID'] : 0); // sriram
		$TransferPlant = $RequestBody['TransferPlant'];
		// $Client = $RequestBody['ClientID'];
		$hasVACrates = $RequestBody['hasVACrates'];
		$IsRejInVA = $RequestBody['IsRejInVA'];
		$Grade = !empty($RequestBody['Grade']) ? $RequestBody['Grade'] : '';
		$existingValueAddition = null;

		// $sqlhasVACrates = "SELECT hasVACrates FROM `AdminConfigDetails` WHERE ClientID=$Client";
		// echo $sqlhasVACrates;
		// $stmthasVACrates = $this->conn->prepare($sqlhasVACrates);
		// $stmthasVACrates->execute();
		// $dataRowHasVACrates = $stmthasVACrates->fetch(PDO::FETCH_ASSOC);
		// $hasVACrates = $dataRowHasVACrates['hasVACrates'];

		if ($hasVACrates == 0) {
			$NoofCrates = 1;
		}
		$HLQuantityTotal = $HLQuantity * $NoofCrates;
		if($ReceivedWeight != ''){
			$ReceivedWeight = (double)$ReceivedWeight;
		}else{
			$ReceivedWeight = 0;
		}

		if($ReturnWeight != ''){
			$ReturnWeight = (double)$ReturnWeight;
			if($ReturnWeight > 25){
				$this->Error = "Rejection Weight Exceeded";
				$this->Msg = "Rejection Weight Exceeded";
				return $this->getValueAdditionDetails($RequestBody);
			}
		}

		$AryCurrentDate = getCurrentDateTime();

		$CreatedDateTime = $AryCurrentDate['TimeStamp'];

		//uma 19-08-2024
		// $SqlIsStoreMaterial = "SELECT IsStoreMaterial FROM `RmReceivingDetails` WHERE ReceivingID='$TrackID'";
		// $stmtIsStoreMaterial = $this->conn->prepare($SqlIsStoreMaterial);
		// $stmtIsStoreMaterial->execute();
		// $dataRowIsStoreMaterial = $stmtIsStoreMaterial->fetch(PDO::FETCH_ASSOC);
		$IsStoreMaterialData = $this->getIsStoreMaterial($TrackID);
		$IsStoreMaterial = $IsStoreMaterialData['IsStoreMaterial'];
		$OutStage = $IsStoreMaterialData['OutStage'];
		if ($IsStoreMaterial == 1 && $OutStage == 1) {
			$BalCrates = $NoofCrates;
			$SqlWeight = "SELECT sum(ReceivingQty) as GradingHLWeight,IsStoreMaterial,
			(SELECT sum(SentWeight)  FROM `PpcValueAddition` WHERE TrackID='$TrackID'  AND CreatedFrom=1)  as VAHLWeight,
			(SELECT sum(SentWeight)  FROM `PpcValueAddition` WHERE VLID='$BreakUpID' AND CreatedFrom=1)  as OldVAHLWeight
			FROM RmReceivingDetails as RRD 
			left join RmReceivingBreakup as RRB  on RRD.ReceivingID=RRB.ReceivingID
			left join CsStockData as CSD on CSD.StockID=RRB.CsStockID  WHERE RRD.ReceivingID='$TrackID' group by CsStockOutID";
		}
		else if($IsStoreMaterial == 0 && $OutStage == 1){
			$BalCrates = $NoofCrates;
			$SqlWeight = "SELECT COALESCE(SUM(ReceivingQty), 0) AS GradingHLWeight,IsStoreMaterial,
			(SELECT COALESCE(SUM(SentWeight), 0) FROM PpcValueAddition WHERE TrackID = '$TrackID' AND CreatedFrom=1) AS VAHLWeight
			FROM RmReceivingDetails as RRD 
			left join RmReceivingBreakup as RRB  on RRD.ReceivingID=RRB.ReceivingID
			WHERE RRD.ReceivingID='$TrackID'";

		}

		//uma 19-08-2024
		else {
			$sqlCrates = "SELECT NoCrates AS GradingCrates,
			(SELECT SUM(NoofCrates) FROM PpcValueAddition WHERE GradingBreakupID='$HLBreakupID' AND CreatedFrom=1) AS VACrates
			FROM PpcHLGradingBreakup WHERE DataID='$HLBreakupID' AND LotID='$ArrivalID' AND CreatedFrom=1";
			$stmtCrates = $this->conn->prepare($sqlCrates);
			$stmtCrates->execute();
			$dataRowCrates = $stmtCrates->fetch(PDO::FETCH_ASSOC);

			$BalCrates = ($dataRowCrates['GradingCrates'] + $NoofCrates) - $dataRowCrates['VACrates'];

			$SqlWeight = "SELECT sum(HLWeight) as GradingHLWeight,
			(SELECT sum(SentWeight)  FROM `PpcValueAddition` WHERE GradingBreakupID='$HLBreakupID' AND CreatedFrom=1)  as VAHLWeight,
			(SELECT sum(SentWeight)  FROM `PpcValueAddition` WHERE VLID='$BreakUpID' AND CreatedFrom=1)  as OldVAHLWeight
			FROM `PpcHLGradingBreakup` WHERE DataID='$HLBreakupID' and LotID='$ArrivalID' AND CreatedFrom=1";
		}

		$stmtWeight = $this->conn->prepare($SqlWeight);
		$stmtWeight->execute();
		$dataRowWeight = $stmtWeight->fetch(PDO::FETCH_ASSOC);

		// $OldVAHLWeight = $dataRowWeight['OldVAHLWeight'];
		$OldVAHLWeight = isset($dataRowWeight['OldVAHLWeight']) ? $dataRowWeight['OldVAHLWeight'] : 0;
		$GradingHLWeight = isset($dataRowWeight['GradingHLWeight']) ? $dataRowWeight['GradingHLWeight'] : 0;
		$VAHLWeight = isset($dataRowWeight['VAHLWeight']) ? $dataRowWeight['VAHLWeight'] : 0;

		$balanceWeight = $GradingHLWeight - $VAHLWeight + $OldVAHLWeight;
		// $GradingHLWeight = $dataRowWeight['GradingHLWeight'];
		if ($BreakUpID != '') {
			$VAHLWeight = ($VAHLWeight + $HLQuantityTotal + $ReturnWeight) - $OldVAHLWeight;
		} else {
			$VAHLWeight = $VAHLWeight + $HLQuantityTotal + $ReturnWeight; //Doubt
		}

		// echo $VAHLWeight . " -- " . $GradingHLWeight . " -- " . $WtAftrVA . " -- " . $balanceWeight;exit;
 		$stmt = false;

		if ($TransferPlant == "true") {

			$sqlTransfer = "SELECT Count(*) as rowCount FROM PpcValueAddition 
			WHERE `SoakingPlantID`='$SoakingPlantID' AND `PlantID`='$PlantID' AND `VLID` IN ($BreakUpID) AND CreatedFrom=1";
			$stmtTransfer = $this->conn->prepare($sqlTransfer);
			$stmtTransfer->execute();

			$dataCount = $stmtTransfer->fetch(PDO::FETCH_ASSOC);
			$rowCount = $dataCount['rowCount'];

			if ($rowCount == 0) {
				//$existingTransferRows = $this->auditLogger->fetchAll("SELECT * FROM PpcValueAddition WHERE VLID IN ($BreakUpID)", []);

				$sqlTransfer = "UPDATE `PpcValueAddition` SET `SoakingPlantID` = '$SoakingPlantID'
				WHERE `VLID` IN ($BreakUpID)";

				$stmtTransfer = $this->conn->prepare($sqlTransfer);
				$stmtTransfer->execute();
				if ($stmtTransfer) {
					//$updatedTransferRows = $this->auditLogger->fetchAll("SELECT * FROM PpcValueAddition WHERE VLID IN ($BreakUpID)", []);
					$this->auditLogger->logUpdate('PpcValueAddition', $BreakUpID, $existingTransferRows, $updatedTransferRows, $RequestBody, [
						'ReferenceNo' => $TrackID,
						'Remarks' => 'PreProcessing VA transfer plant updated',
					]);
				}

				$this->Error = null;
				$this->Msg = "Transfer To Plant Success";
				// $this->Msg = "hi";
				return $this->getValueAdditionDetails($RequestBody);
			} else {
				$this->Error = "Value Addition plant and Transfer Plant are should not Same";
				$this->Msg = "Value Addition plant and Transfer Plant are should not Same";
				return $this->getValueAdditionDetails($RequestBody);
			}
		} else {
			if ($VAHLWeight <= $GradingHLWeight && $WtAftrVA <= $balanceWeight && ($HLQuantityTotal > ($WtAftrVA + $ReturnWeight))) {

				// echo $BalCrates. " -- " . $NoofCrates;exit;
				if ($hasVACrates == 1 && $BalCrates < $NoofCrates) {
					$this->Error = 'Crates Exceeds';
					return $this->getValueAdditionDetails($RequestBody);
				}
				if ($BreakUpID == '') {

					$sqlQuery = " INSERT INTO `PpcValueAddition`( `LotID`,  `SentCount`, `SentWeight`,`ReceivedWeight`,`ReturnWeight`,  `ProcessingDate`,
					`PostedDate`, `WtAftrVA`, `CountAftrVA`, `VarietyID`, `ShiftID`, `StationID`, `PlantID`,`PostedUserID`, `Remarks`, `Supervisor`, 
					`IceConsumption`, `NetCount`, `TrackID`,`TrackBreakUpID`,`GradingBreakupID`,`NoofCrates`,`SoakingPlantID`,`Grade`) 
					VALUES ('$ArrivalID','$HLCount','$HLQuantityTotal','$ReceivedWeight','$ReturnWeight','$ProcessingDate','$CreatedDateTime','$WtAftrVA',
					'$CountAftrVA','$VarietyID','$Shift','$StationID','$PlantID','$PostedUserID','$Remarks','$Supervisor','$IceConsumption','$NetCount',
					'$TrackID','$TrackBreakUpID','$HLBreakupID','$NoofCrates','$PlantID','$Grade')";

					$stmt = $this->conn->prepare($sqlQuery);
					$stmt->execute();
					$lastInsertId = $this->conn->lastInsertId();
					if ($stmt) {
						$createdValueAddition = $this->auditLogger->fetchRowById('PpcValueAddition', 'VLID', $lastInsertId);
						$this->auditLogger->logInsert('PpcValueAddition', $lastInsertId, $createdValueAddition, $RequestBody, [
							'ReferenceNo' => $TrackID,
							'Remarks' => 'PreProcessing value addition tracking row created',
						]);
					}
					// $errorInfo = $stmt->errorInfo();
					// var_dump($errorInfo);
					$RequestBody['recentId'] = $lastInsertId;
					$this->Error = null;
					$this->Msg = "Data Posted Successfully";
				} else {
					$existingValueAddition = $this->auditLogger->fetchRowById('PpcValueAddition', 'VLID', $BreakUpID);

					$sqlQuery = "UPDATE `PpcValueAddition`SET `LotID` = '$ArrivalID',`SentCount` = '$HLCount',`SentWeight` = '$HLQuantityTotal',
					`ReceivedWeight` = '$ReceivedWeight',`ReturnWeight` = '$ReturnWeight',`ProcessingDate` = '$ProcessingDate',
					`PostedDate` = '$CreatedDateTime',`WtAftrVA` = '$WtAftrVA',`CountAftrVA` = '$CountAftrVA',`VarietyID` = '$VarietyID',
					`ShiftID` = '$Shift',`StationID` = '$StationID',`PlantID` = '$PlantID',`PostedUserID` = '$PostedUserID',
					`Remarks` = '$Remarks',`Supervisor` = '$Supervisor',`IceConsumption` = '$IceConsumption',`NetCount` = '$NetCount',
					`TrackID` = '$TrackID',`TrackBreakUpID` = '$TrackBreakUpID',
					`GradingBreakupID` = '$HLBreakupID',`NoofCrates` = '$NoofCrates',`SoakingPlantID`='$PlantID',`Grade` = '$Grade'
					WHERE `VLID` = '$BreakUpID'";

					$stmt = $this->conn->prepare($sqlQuery);
					$stmt->execute();
					$RequestBody['recentId'] = $BreakUpID;
					$this->Error = null;
					$this->Msg = "Data Updated Successfully";
					if ($stmt) {
						$updatedValueAddition = $this->auditLogger->fetchRowById('PpcValueAddition', 'VLID', $BreakUpID);
						$this->auditLogger->logUpdate('PpcValueAddition', $BreakUpID, $existingValueAddition, $updatedValueAddition, $RequestBody, [
							'ReferenceNo' => $TrackID,
							'Remarks' => 'PreProcessing value addition tracking row updated',
						]);
					}
					// }
				}

				if ($stmt) {
					if($IsRejInVA == 1){
						if($ReturnWeight > 0){
							$this->getAutogeneratedTrackCode($RequestBody);
						}
					}	
					$this->utilController->UpdateTrackWiseRawHLWeight($TrackID, '', '', $RequestBody);
					$this->utilController->UpdateTrackWiseRawMeatWeight($TrackID, '',$RequestBody);
					// $this->utilController->UpdateTrackWiseCookedMeatWeight($TrackID, '');
					$this->utilController->UpdateTrackWiseThawingMeatWeight($TrackID);

					return $this->getValueAdditionDetails($RequestBody);
				} else {
					return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
				}
			} else {
				if ($VAHLWeight > $GradingHLWeight && $WtAftrVA > $balanceWeight) {
					$this->Error = 'HL Weight Exceeds and VA Out Quantity Exceeds';
				} else if ($VAHLWeight > $GradingHLWeight) {
					$this->Error = 'HL Weight Exceeds';
				} else{
					$this->Error = 'VA Out Quantity Exceeds';
				}
				return $this->getValueAdditionDetails($RequestBody);
			}
		}
	}

	public function postDeleteValueAddition($RequestBody)
	{
		$BreakUpID = $RequestBody['BreakUpID'];
		$existingValueAddition = $this->auditLogger->fetchRowById('PpcValueAddition', 'VLID', $BreakUpID);
		$sqlTrackID = "SELECT TrackID,SentWeight FROM `PpcValueAddition` WHERE VLID='$BreakUpID' AND CreatedFrom=1";
		$stmtTrackID = $this->conn->prepare($sqlTrackID);
		$stmtTrackID->execute();
		$dataRowTrackID = $stmtTrackID->fetch(PDO::FETCH_ASSOC);
		$TrackID = $dataRowTrackID['TrackID'];
		//update trackwise raw and cooked weights for chill room report
		$this->utilController->UpdateTrackWiseRawMeatWeight($TrackID, $BreakUpID, $RequestBody);
		// $this->utilController->UpdateTrackWiseCookedMeatWeight($TrackID, $BreakUpID);

		$sqlQuery = "DELETE FROM `PpcValueAddition` WHERE VLID='$BreakUpID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		if ($stmt) {
			$this->auditLogger->logDelete('PpcValueAddition', $BreakUpID, $existingValueAddition, $RequestBody, [
				'ReferenceNo' => $TrackID,
				'Remarks' => 'PreProcessing value addition tracking row deleted',
			]);
			$this->utilController->UpdateTrackWiseRawHLWeight($TrackID, '', '', $RequestBody);

			$this->utilController->UpdateTrackWiseThawingMeatWeight($TrackID);
		}
		$this->Msg = "Item Deleted";
		return $this->getValueAdditionDetails($RequestBody);
	}

	function getDeleteAccess($RequestBody)
	{
		$Query = '';
		$DeleteAccess = 1;
		$GradingBreakupID = $RequestBody['GradingBreakupID'];
		$ValueAdditionID = $RequestBody['ValueAdditionID'];
		$LotID = $RequestBody['LotID'];
		$TrackID = $RequestBody['TrackID'];
		$IsStoreMaterial = $RequestBody['IsStoreMaterial'];
		$PlantID = $RequestBody['PlantID'];


		$sqlIsBlock = "SELECT Treatment,IsBlock FROM `PpcHLGradingBreakup` WHERE  DataID='$GradingBreakupID'  AND CreatedFrom=1";
		$stmtIsBlock = $this->conn->prepare($sqlIsBlock);
		$stmtIsBlock->execute();
		$dataRowIsBlock = $stmtIsBlock->fetch(PDO::FETCH_ASSOC);
		$Treatment = $dataRowIsBlock['Treatment'];
		$IsBlock = $dataRowIsBlock['IsBlock'];

		if ($IsStoreMaterial == 1) { //Soaking

			$sqlCount = "SELECT count(*)Count FROM `ProdSoakingDetails` WHERE   TrackID='$TrackID'";
		} else if ($Treatment != 'UNT' && $IsBlock == 0) { //Soaking

			$sqlCount = "SELECT count(*)Count FROM `ProdSoakingDetails` WHERE   LotID in ($LotID) and TrackID='$TrackID' and VABreakupID='$GradingBreakupID' AND PlantID ='$PlantID' AND ValueAdditionID='$ValueAdditionID'";
		} else if ($Treatment == 'UNT' && $IsBlock == 0) { //Freezing

			$sqlCount = "SELECT count(*)Count FROM `ProdFreezingData` WHERE    TrackID='$TrackID' and TubNo='$ValueAdditionID' AND PlantID ='$PlantID'";
		}
		// else {

		// 	$sqlCount = "SELECT count(*) as Count  FROM `PpcValueAddition` WHERE LotID in ($ArrivalID) and TrackID='$TrackID' and GradingBreakupID='$GradingBreakupID'";
		// }
		// echo $sqlCount;
		$stmtCount = $this->conn->prepare($sqlCount);
		$stmtCount->execute();
		$dataRowCount = $stmtCount->fetch(PDO::FETCH_ASSOC);

		if ($dataRowCount['Count'] > 0) {
			$DeleteAccess = 0;
		}
		return $DeleteAccess;
	}

	function getBalanceQty($RequestBody)
	{

		$GradingBreakUpID = $RequestBody['GradingBreakUpID'];
		$TrackID = $RequestBody['TrackID'];
		$IsStoreMaterialData = $this->getIsStoreMaterial($TrackID);
		$IsStoreMaterial = $IsStoreMaterialData['IsStoreMaterial'];
		$OutStage = $IsStoreMaterialData['OutStage'];
		// echo print_r($IsStoreMaterialData);exit;

		if ($IsStoreMaterial == 0 && $OutStage == 0) {

			$sqlHlWeight = "SELECT NoCrates,HLWeight FROM `PpcHLGradingBreakup`  WHERE DataID='$GradingBreakUpID' AND CreatedFrom=1";
			$stmtHlWeight = $this->conn->prepare($sqlHlWeight);
			$stmtHlWeight->execute();
			$rowHlWeight = $stmtHlWeight->fetch(PDO::FETCH_ASSOC);

			$sqlBalance = "SELECT SUM(SentWeight) AS TotalQty,SUM(NoofCrates) AS TotalCrates FROM PpcValueAddition 
					WHERE  GradingBreakupID='$GradingBreakUpID' and TrackID='$TrackID' AND CreatedFrom=1";
			$stmtBalance = $this->conn->prepare($sqlBalance);
			$stmtBalance->execute();
			$rowBalance = $stmtBalance->fetch(PDO::FETCH_ASSOC);
			$HLQty = $rowBalance['TotalQty'];
			$Crates = $rowBalance['TotalCrates'];
			$BalanceData['BalanceQty'] = $rowHlWeight['HLWeight'] - $HLQty;
			$BalanceData['BalanceCrates'] = $rowHlWeight['NoCrates'] - $Crates;
		} else {
			if ($IsStoreMaterial == 1 && $OutStage == 1) {
				$sqlStockOutDetails = "SELECT group_concat( DISTINCT CSD.Grade) as Grade,group_concat( DISTINCT SPV.ShortName) as Variety,
				group_concat( DISTINCT SPV.TypeProduct) as TypeProduct,CSD.VarietyID,
				sum(ReceivingQty) as HLWeight FROM RmReceivingBreakup as RRB 
				left join CsStockData as CSD on CSD.StockID=RRB.CsStockID 
				left join ScrmPIVarietyList as SPV on CSD.VarietyID=SPV.VarietyID 
				WHERE ReceivingID='$TrackID' group by CsStockOutID";

				$stmtStockOutDetails = $this->conn->prepare($sqlStockOutDetails);
				$stmtStockOutDetails->execute();
				$rowStockOutDetails = $stmtStockOutDetails->fetch(PDO::FETCH_ASSOC);

				$sqlBalance = "SELECT SUM(SentWeight) AS TotalQty,SUM(NoofCrates) AS TotalCrates FROM PpcValueAddition 
				WHERE  TrackID='$TrackID' AND CreatedFrom=1";
				$stmtBalance = $this->conn->prepare($sqlBalance);
				$stmtBalance->execute();

				$rowBalance = $stmtBalance->fetch(PDO::FETCH_ASSOC);
				$HLQty = $rowBalance['TotalQty'];
				// $Crates = $rowBalance['TotalCrates'];
				$BalanceData['BalanceQty'] = $rowStockOutDetails['HLWeight'] - $HLQty;
				$BalanceData['BalanceCrates'] = 1;
			}else{
				$sqlStockOutDetails = "SELECT 
				sum(ReceivingQty) as HLWeight FROM RmReceivingBreakup as RRB
				WHERE ReceivingID='$TrackID'";

				$stmtStockOutDetails = $this->conn->prepare($sqlStockOutDetails);
				$stmtStockOutDetails->execute();
				$rowStockOutDetails = $stmtStockOutDetails->fetch(PDO::FETCH_ASSOC);

				$sqlBalance = "SELECT SUM(SentWeight) AS TotalQty,SUM(NoofCrates) AS TotalCrates FROM PpcValueAddition 
				WHERE  TrackID='$TrackID' AND CreatedFrom=1";
				$stmtBalance = $this->conn->prepare($sqlBalance);
				$stmtBalance->execute();

				$rowBalance = $stmtBalance->fetch(PDO::FETCH_ASSOC);
				$HLQty = $rowBalance['TotalQty'];
				// $Crates = $rowBalance['TotalCrates'];
				$BalanceData['BalanceQty'] = $rowStockOutDetails['HLWeight'] - $HLQty;
				$BalanceData['BalanceCrates'] = 1;
			}
		}


		$BalanceData['TotalQty'] = $HLQty;
		$BalanceData['TotalCrates'] = $Crates;

		return $BalanceData;
	}
	function getIsStoreMaterial($TrackID)
	{
		$SqlIsStoreMaterial = "SELECT IsStoreMaterial,OutStage FROM `RmReceivingDetails` WHERE ReceivingID='$TrackID'";
		$stmtIsStoreMaterial = $this->conn->prepare($SqlIsStoreMaterial);
		$stmtIsStoreMaterial->execute();
		$dataRowIsStoreMaterial = $stmtIsStoreMaterial->fetch(PDO::FETCH_ASSOC);
		$IsStoreMaterial = $dataRowIsStoreMaterial['IsStoreMaterial'];
		return $dataRowIsStoreMaterial;
	}

	function getAutogeneratedTrackCode($RequestBody)
    {

        $PlantID = $RequestBody['PlantID'];
        $ArrivalDate = $RequestBody['ArrivalDate'];
		$IsVAStationLinked = $RequestBody['IsVAStationLinked'];
		$VAId = $RequestBody['recentId'];
        $ReStockinStage = 1;

        $sqlgetRejectionWeight = "SELECT sum(ReturnWeight) as RejectionWeight FROM `PpcValueAddition` AS PV LEFT JOIN RmReceivingDetails AS RD ON PV.TrackID=RD.ReceivingID  WHERE RD.ArrivalDate='$ArrivalDate' 
        and PV.PlantID='$PlantID'  AND PV.CreatedFrom=1";

        $stmtgetRejectionWeight = $this->conn->prepare($sqlgetRejectionWeight);
        $stmtgetRejectionWeight->execute();
        $dataRowgetRejectionWeight = $stmtgetRejectionWeight->fetch(PDO::FETCH_ASSOC);
        $RejectionWeight = $dataRowgetRejectionWeight['RejectionWeight'];

        if ($RejectionWeight == 0) {
            $this->deleteRejectionTrackIds($ArrivalDate);
        } else {

			if($PlantID != '' && $PlantID != '0' && $PlantID != 0){
				$sqlSelect = "SELECT ProdCode as PlantCode FROM RmProcessingStations  WHERE ProcessingStationID='$PlantID'";
			}else{
				$sqlSelect = "SELECT PVS.UnitName as PlantCode FROM PpcValueAddition AS PPC LEFT JOIN PpcVAStations AS PVS ON PPC.StationID=PVS.StationID WHERE VLID='$VAId' AND PPC.CreatedFrom=1";
			}
			$stmtSelect = $this->conn->prepare($sqlSelect);
			$stmtSelect->execute();
			$dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
			$PlantCode = $dataRow['PlantCode'];

            $sqlTrackId = "SELECT max(TrackNumber) as TrackNumber,ReceivingID,
			(select count(*)  FROM RmReceivingDetails WHERE ArrivalDate='$ArrivalDate' and ReceivingPlantID='$PlantID' and IsStoreMaterial=0 and OutStage=1 AND TrackCode LIKE '".$PlantCode."%')as TrackCount FROM RmReceivingDetails 
			WHERE ArrivalDate='$ArrivalDate' and ReceivingPlantID='$PlantID' and IsStoreMaterial=0  and OutStage=1 AND TrackCode LIKE '".$PlantCode."%'";

            $stmtTrackId = $this->conn->prepare($sqlTrackId);
            $stmtTrackId->execute();
            $dataRowTrackId = $stmtTrackId->fetch(PDO::FETCH_ASSOC);
            $TrackNumber = $dataRowTrackId['TrackNumber'] + 1;
            $TrackCount = $dataRowTrackId['TrackCount'];

            $AutoTrackCode = str_replace("O", "0", $TrackNumber);
            $AutoTrackCode = ltrim($AutoTrackCode, "0");
            $AutoTrackCode = preg_replace("/[^0-9]/", "", $AutoTrackCode);
            $AutoTrackCode = sprintf('%04d', $AutoTrackCode);


            $AutoTrackCode = $PlantCode . '-VA-REJ-' . sprintf('%03d', $TrackNumber);
            if ($TrackCount == 0 && $RejectionWeight > 0) {
                $sqlInsertQuery = "INSERT INTO RmReceivingDetails(ArrivalDate,ReceivingPlantID,TrackNumber,TrackCode,OutStage,IsStoreMaterial) 
				VALUES ('$ArrivalDate','$PlantID','$TrackNumber','$AutoTrackCode','$ReStockinStage','0')";
                $stmtInsertQuery = $this->conn->prepare($sqlInsertQuery);
                $stmtInsertQuery->execute();
                $ReceivingID = $this->conn->lastInsertId();


                if ($stmtInsertQuery) {

                    $sqlInsertBreakup = "INSERT INTO RmReceivingBreakup(ReceivingID,ReceivingQty) 
					VALUES ('$ReceivingID','$RejectionWeight')";
                    $stmtInsertBreakup = $this->conn->prepare($sqlInsertBreakup);
                    $stmtInsertBreakup->execute();
                }
            } else {
                $ReceivingID = $dataRowTrackId['ReceivingID'];
                $sqlUpdateBreakup = "update RmReceivingBreakup set ReceivingQty='$RejectionWeight' where ReceivingID='$ReceivingID'";
                $stmtUpdateBreakup = $this->conn->prepare($sqlUpdateBreakup);
                $stmtUpdateBreakup->execute();
            }
        }

        return "Success";
    }

	function deleteRejectionTrackIds($ArrivalDate)
    {
        $SqlRejectionTrackIDCount = "SELECT count(*) as RejectionCount,sum(ReturnWeight) as RejectionWeight FROM `PpcValueAddition` AS PV LEFT JOIN RmReceivingDetails AS RD ON PV.TrackID=RD.ReceivingID 
		WHERE RD.ArrivalDate='$ArrivalDate' and ReturnWeight>0 AND PV.CreatedFrom=1";
        $stmtRejectionTrackIDCount = $this->conn->prepare($SqlRejectionTrackIDCount);
        $stmtRejectionTrackIDCount->execute();
        $dataRowRejectionTrackIDCount = $stmtRejectionTrackIDCount->fetch(PDO::FETCH_ASSOC);
        $RejectionCount = $dataRowRejectionTrackIDCount['RejectionCount'];
        $RejectionWeight = $dataRowRejectionTrackIDCount['RejectionWeight'];

        $SqlReceivingIds = "SELECT  ReceivingID FROM `RmReceivingDetails` WHERE ArrivalDate='$ArrivalDate' and OutStage=1 and IsStoreMaterial=0";
        $stmtReceivingIds = $this->conn->prepare($SqlReceivingIds);
        $stmtReceivingIds->execute();
        $dataRowReceivingIds = $stmtReceivingIds->fetch(PDO::FETCH_ASSOC);
        $ReceivingID = $dataRowReceivingIds['ReceivingID'];

        if ($RejectionCount == 0) {


            $SqlDeleteBreakUp = "DELETE FROM `RmReceivingBreakup` WHERE ReceivingID='$ReceivingID'";
            $stmtDeleteBreakUp = $this->conn->prepare($SqlDeleteBreakUp);
            $stmtDeleteBreakUp->execute();

            if ($stmtDeleteBreakUp) {
                $SqlDeleteRejectionTrack = "DELETE FROM `RmReceivingDetails` WHERE  ArrivalDate='$ArrivalDate' and OutStage=1 and IsStoreMaterial=0";
                $stmtDeleteRejectionTrack = $this->conn->prepare($SqlDeleteRejectionTrack);
                $stmtDeleteRejectionTrack->execute();
            }
        } else {
            $SqlUpdateBreakUp = "UPDATE `RmReceivingBreakup` SET `ReceivingQty`='$RejectionWeight' where ReceivingID='$ReceivingID'";
            $stmtUpdateBreakUp = $this->conn->prepare($SqlUpdateBreakUp);
            $stmtUpdateBreakUp->execute();
        }
        return "success";
    }
	function getLotNumberByStockId($stockIdS){
        if(!empty($stockIdS)){
            $sql = "SELECT group_concat(DISTINCT RLD.LotNumber ) LotNumber
                    FROM CsStockData CSD
                    LEFT JOIN CsDailyProductionData AS CDPD ON CDPD.ProductionID=CSD.ProductionID
                    LEFT JOIN (
                        SELECT TrackID, TubNo, LineNumber, MAX(ProductionID) AS MaxDataID
                        FROM ProdFreezingData
                        GROUP BY TrackID, TubNo, LineNumber
                        ) PFDx
                            ON CDPD.TrackID = PFDx.TrackID 
                            AND CDPD.TubNo = PFDx.TubNo 
                            AND CDPD.LineNumber = PFDx.LineNumber
                    LEFT JOIN ProdFreezingData PFD ON PFD.ProductionID = PFDx.MaxDataID
                    LEFT JOIN PpcHLGradingBreakup AS PGB ON PFD.GradingBreakupID=PGB.DataID  AND PGB.CreatedFrom=1
                    LEFT JOIN RmLotDetatils AS RLD ON PGB.LotID=RLD.RmLotID
                    WHERE `StockID` IN($stockIdS)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $rowData = $stmt->fetch(PDO::FETCH_ASSOC);
            return $rowData['LotNumber'];
        }
    }

}
