<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class HlYieldReportController
{
	private $conn;
	//	private $db_table = "ScrmApplicants";
	private $utilController;
	private $Error = null;
	private $Msg = '';


	// Db connection
	public function __construct($db)
	{
		$this->conn = $db;
		$this->utilController = new UtilController($this->conn);
	}

	public function getDeheadingDetails($RequestBody)
	{

		$DeheadingData = $this->getHlYieldData($RequestBody);



		$Data['Error'] = null;
		$Data['Msg'] = ' Posted Successful';

		$Data['Data'] = $DeheadingData;

		return $Data;
	}





	function getHlYieldData($RequestBody)
	{


		//$ArrivalID = $RequestBody['ArrivalID'];
		$StartDate = $RequestBody['StartDate'];
		$EndDate = $RequestBody['EndDate'];

		// $Qry="PurchaseDate between '$StartDate' and '$EndDate'";
		$Qry = "PDH.ProcessingDate between '$StartDate' and '$EndDate'"; //durga

		$dataRow = null;
		//$sqlFields

		// $sqlFields = "DHID,ProcessingDate,LotNumber as ArrivalNumber,StationName,UnitName,UserName,Name ,CheckingCount,CountAfterDH,
		// WtAfterDH,NetCount,IceConsumption,PDH.StationID,PDH.PlantID,PDH.LotID,PDH.ShiftID,PDH.SupervisorID,
		// PDH.Remarks";

		$sqlFields = "RmLotID,RLD.PurchaseDate,ProcessingDate,PDH.StationID,PDH.SupervisorID,RLD.GraderID,RLD.AgentID,
		PurchaseWeight,ProductionWeight,PurchaseCount,
		RLD.LotNumber,sum(WtAfterDH) as HLQty,round(avg(CheckingCount),2) as CheckingCount2, 
		round(avg(NetCount),2) as AvgNetCount,RLD.NetWeight as CountNetQty,PYD.CheckingCount1,
		Batch1,Batch2,Batch3,Batch4,Batch5,Batch6
		,RAD.ArrivalStationID,RAD.Species";


		$sqlQuery = "SELECT $sqlFields FROM `RmLotDetatils`   RLD 
		LEFT JOIN RmArrivalDetatils RAD ON RAD.RmArrivalID = RLD.RmReferenceID
		left join PpcDeheading PDH on PDH.LotID=RLD.RmLotID AND PDH.CreatedFrom=0
		left join PpcHLYeildData as PYD on PYD.LotID=RLD.RmLotID	
        where  $Qry   group by RLD.RmLotID ORDER BY PurchaseDate,`LotNumber` asc";
		$stmt = $this->conn->prepare($sqlQuery); //
		$stmt->execute();
		//  $ArrayData[]='';
		$TotalHLQuantity = 0;
		$i = 0;
		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

			$RmLotID = $dataRow['RmLotID'];
			$vaStationID = $dataRow['StationID'];
			$SupervisorID = $dataRow['SupervisorID'];
			$AgentID = $dataRow['AgentID'];
			$GraderID = $dataRow['GraderID'];
			$Weight = $dataRow['PurchaseWeight'];
			$ProductionWeight = $dataRow['ProductionWeight'];
			$Count = $dataRow['PurchaseCount'];
			$PurchaseDate = $dataRow['PurchaseDate'];

			/*if($ProductionWeight > 0){
								  $Weight=$ProductionWeight;
							  }else{
								  $Weight=$PurchaseWeight;
							  }
				  */
			//$BatchDetails=$this->getBatchDetails($RmLotID);
			//$DeHeadingDetails=$this->getDeheadingData($RmLotID);
			//$DeHeadingDetails=$this->getDeheadingData($RmLotID);


			$aryGradingQty = $this->getGradingQty($RmLotID);
			$vaStationDetails = $this->getVaStation($vaStationID);
			$StandardYeild = $this->getStandardYield($Count, $PurchaseDate);
			$vaSupervisor = $this->utilController->getUsers($SupervisorID);
			$vaAgent = $this->utilController->getUsers($AgentID);
			$vaGrader = $this->utilController->getUsers($GraderID);

			$HLQty = $dataRow['HLQty'];
			$CountNetQty = $dataRow['CountNetQty'];
			$GradingQty = $aryGradingQty['GradingWeight'];

			// $HlYield=round(($GradingQty/$Weight*100),2);
			$HlYield = round(($HLQty / $Weight * 100), 2); //durga

			$CountNetYield = $dataRow['AvgNetCount'];
			$CalHon = $CountNetYield / 100;

			$CountNetYieldPerc = round((($HLQty / $CalHon) - $Weight), 2);			

			$DeheadingData['SNo'] = ++$i;
			$DeheadingData['BreakUpID'] = $dataRow['DHID'];
			$DeheadingData['RmDate'] = $this->utilController->getFormatedDate($dataRow['PurchaseDate']);
			$DeheadingData['DeHeadingDate'] = $this->utilController->getFormatedDate($dataRow['ProcessingDate']);
			$DeheadingData['UnitName'] = $vaStationDetails['UnitName'];
			$DeheadingData['LotNo'] = $dataRow['LotNumber'];
			$DeheadingData['AgentName'] = $vaAgent['UserName']; //$this->utilController-> getUserName($ID);
			$DeheadingData['GraderName'] = $vaGrader['UserName'];
			$DeheadingData['PurchaseWeight'] = $Weight;
			$DeheadingData['BillCount'] = $Count;
			$DeheadingData['ChkCount1'] = $dataRow['CheckingCount1'];
			$DeheadingData['ChkCount2'] = $dataRow['CheckingCount2'];
			$DeheadingData['HLQty'] = $HLQty;
			$DeheadingData['GradingQty'] = $GradingQty;
			$DeheadingData['HlYield'] = $HlYield;
			$DeheadingData['CountNetYield'] = $CountNetYield;
			$DeheadingData['CountNetQty'] = $CountNetQty;
			$DeheadingData['StandardYield'] = $StandardYeild;
			$DeheadingData['CountNetYieldPerc'] = $CountNetYieldPerc;
			$DeheadingData['Supervisor'] = $vaSupervisor['UserName'];

			$Center = $dataRow['ArrivalStationID'];
			$Species = $dataRow['Species'];
			$BillDate = $dataRow['PurchaseDate'];
			//$Count,ArrivalStationID
			$DeheadingData['AsPerStdYieldPerc'] = $DeheadingData['CountRate'] = $DeheadingData['StdYieldKgsAmount'] = $DeheadingData['CountNetYieldKgsAmount'] = '';
			if($dataRow['ProcessingDate'] >= '2025-04-01'){
				$percentStandardYeild = round((($HLQty / $StandardYeild) * 100 ) - $Weight , 3);
				$DeheadingData['AsPerStdYieldPerc'] = $percentStandardYeild;

				$AryPriceData = $this->GetRmPriceByCount($Count, $Center, $Species, $BillDate);
				$DeheadingData['CountRate'] = $AryPriceData['RmPrice'];
				$DeheadingData['StdYieldKgsAmount'] = ($percentStandardYeild * $AryPriceData['RmPrice']);
				$DeheadingData['CountNetYieldKgsAmount'] = ($CountNetYieldPerc * $AryPriceData['RmPrice']);
			}
			//$DeheadingData['Supervisor']=$dataRow['ArrivalNumber'];

			/*
					 $DeheadingData['Plant']=$dataRow['StationName'];
					 $DeheadingData['UserName']=$dataRow['UserName'];
					 $DeheadingData['Shift']=$dataRow['Name'];
					 $DeheadingData['HeadonCountAfterCheckingCount2']=$dataRow['CheckingCount'];
					 $DeheadingData['HLCount']=$dataRow['CountAfterDH'];
					 $DeheadingData['HLQuantityKgs']=$dataRow['WtAfterDH'];
					 $DeheadingData['CountNetYield']=$dataRow['NetCount'];
					 $DeheadingData['IceConsumption']=$dataRow['IceConsumption'];
					 $DeheadingData['Remarks']=$dataRow['Remarks'];
					 $DeheadingData['CenterID']=$dataRow['StationID'];
					 $DeheadingData['PlantID']=$dataRow['PlantID'];
					 $DeheadingData['ArrivalID']=$dataRow['LotID'];
					 $DeheadingData['ShiftID']=$dataRow['ShiftID'];
					 $DeheadingData['SupervisorID']=$dataRow['SupervisorID'];
					 */

			$DeheadingData['Batch1'] = $dataRow['Batch1'];
			$DeheadingData['Batch2'] = $dataRow['Batch2'];
			$DeheadingData['Batch3'] = $dataRow['Batch3'];
			$DeheadingData['Batch4'] = $dataRow['Batch4'];
			$DeheadingData['Batch5'] = $dataRow['Batch5'];
			$DeheadingData['Batch6'] = $dataRow['Batch6'];


			$ArrayData[] = $DeheadingData;
			$TotalHLQuantity += $DeheadingData['HLQuantityKgs'];

		}
		$ArrayTotals['TotalHLQuantity'] = $TotalHLQuantity;
		$Data['DeheadingData'] = $ArrayData;
		$Data['Totals'] = $ArrayTotals;

		return $Data;

	}




	function getBatchDetails($RmLotID)
	{

		$sqlQuery = "SELECT * FROM `PpcHLYeildData`  WHERE LotID = '26029'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$BatchDetails = $stmt->fetch(PDO::FETCH_ASSOC);

		return $BatchDetails;
	}

	function getVaStation($VaStationID)
	{

		$sqlQuery = "SELECT UnitName FROM `PpcVAStations` where StationID=$VaStationID";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$VaStation = $stmt->fetch(PDO::FETCH_ASSOC);

		return $VaStation;
	}

	function getGradingQty($RmLotID)
	{

		$sqlQuery = "SELECT sum(HLWeight) as GradingWeight FROM `PpcHLGradingBreakup` WHERE LotID=$RmLotID AND CreatedFrom=0";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$GradingQty = $stmt->fetch(PDO::FETCH_ASSOC);

		return $GradingQty;
	}
	function getStandardYield($BillCount, $PurchaseDate)
	{


		//  $sqlQuery = "SELECT * FROM `RmStandardYields` WHERE Type='HL' AND $BillCount BETWEEN LowerCount AND UpperCount";
		 $sqlQuery = "SELECT * FROM `RmStandardYields` WHERE Type='HL' and EffectiveDate<='$PurchaseDate' AND (($BillCount BETWEEN LowerCount AND UpperCount) or ($BillCount >= LowerCount )) order by ID  desc limit 1";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$RowYeildsList = $stmt->fetch(PDO::FETCH_ASSOC);
		$StandardYeild = $RowYeildsList['Yield'];

		return $StandardYeild;
	}
	function GetRmPriceByCount($Count, $Center, $Species, $BillDate){		
		if(!empty($Count) && !empty($Center) && !empty($Species) && !empty($BillDate)){
			$sqlQuery = "SELECT ID
							FROM RmPurchaseRatesHistory
							WHERE  RmPriceDate<='$BillDate 23:59:59' 
									and SType='$Species' and ((FIND_IN_SET('$Center', IF(centerID = '', '0', centerID)) > 0 ) or FIND_IN_SET('0', IF(centerID = '', '0', centerID)) > 0)
								ORDER BY
								CASE
									WHEN FIND_IN_SET('$Center', IF(centerID = '', '0', centerID)) > 0 THEN 1
									WHEN FIND_IN_SET('0', IF(centerID = '', '0', centerID)) > 0 THEN 2
								END,
							RmPriceDate  DESC, ID DESC limit 1 ";
			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();
			if ($stmt->rowCount() != 0) {
				$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
				$TARCK_ID = $dataRow['ID'];
				//$Count = 60.1
				$baseCount = floor($Count);//60
				$Diff = 0;	
				////CAM-2957
				if ($Count > 100){
					$temp_c = 100;
                	$Diff = $baseCount - $temp_c;
					$RateSql = "SELECT RmPrice FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and CONVERT(RmCount, DECIMAL(10, 2)) = CONVERT('$temp_c', DECIMAL(10, 2))";
					$RateStmt = $this->conn->prepare($RateSql);
					$RateStmt->execute();
					$RmPrice = ($RateStmt->fetch(PDO::FETCH_ASSOC)['RmPrice']) - $Diff;
				} elseif ($Count <= ($baseCount + 0.20)) {//60.0 <= 60.2 take 60 count rate				
					$RateSql = "SELECT RmPrice FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and CONVERT(RmCount, DECIMAL(10, 2)) = CONVERT('$baseCount', DECIMAL(10, 2))";
					$RateStmt = $this->conn->prepare($RateSql);
					$RateStmt->execute();
					$RmPrice = $RateStmt->fetch(PDO::FETCH_ASSOC)['RmPrice'];
				} elseif ($Count <= ($baseCount + 0.70)) {// 60.21 to 60.70 - take avarage rate of 60 and 61
					$finalCnt = $baseCount + 1;
					$RateSql = "SELECT AVG(RmPrice) RmPrice FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and CONVERT(RmCount, DECIMAL(10, 2)) BETWEEN CONVERT('$Count', DECIMAL(10, 2)) AND CONVERT('$finalCnt', DECIMAL(10, 2))";

					$CntStrat = $baseCount +  0.21;
					$CntEnd = $baseCount +  0.70;
					$RateSql = "SELECT AVG(RmPrice) RmPrice FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and CONVERT(RmCount, DECIMAL(10, 2)) BETWEEN CONVERT('$CntStrat', DECIMAL(10, 2)) AND CONVERT('$CntEnd', DECIMAL(10, 2))";
					$RateStmt = $this->conn->prepare($RateSql);
					$RateStmt->execute();
					$RmPrice = $RateStmt->fetch(PDO::FETCH_ASSOC)['RmPrice'];
					

					if ($RmPrice == null && trim($RmPrice) == '' && $RmPrice == 0) {
						$finalCnt = $baseCount + 1;
						$RateSql = "SELECT AVG(RmPrice) RmPrice FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and CONVERT(RmCount, DECIMAL(10, 2)) BETWEEN CONVERT('$baseCount', DECIMAL(10, 2)) AND CONVERT('$finalCnt', DECIMAL(10, 2))";
						
						$RateStmt = $this->conn->prepare($RateSql);
						$RateStmt->execute();
						$RmPrice = $RateStmt->fetch(PDO::FETCH_ASSOC)['RmPrice'];
					}
				} else {// morethen 60.71 teke next count rate
					$finalCnt = $baseCount + 1;
					$RateSql = "SELECT RmPrice FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and CONVERT(RmCount, DECIMAL(10, 2)) = CONVERT('$finalCnt', DECIMAL(10, 2))";
					$RateStmt = $this->conn->prepare($RateSql);
					$RateStmt->execute();
					$RmPrice = $RateStmt->fetch(PDO::FETCH_ASSOC)['RmPrice'];
				}
				/* if($Count == '90.6' || $Count == '90.60'){
					echo '<pre>';print_r($RateSql);die;
				} */
				if (!$RmPrice) {
					$RmPrice = 1;
				}
				$data['RmPrice'] = sprintf('%0.2f', isset($RmPrice) ? $RmPrice : 0);
				$data['BillHonCount'] = $Count;
				return $data;

			}
		}
	}


}
?>