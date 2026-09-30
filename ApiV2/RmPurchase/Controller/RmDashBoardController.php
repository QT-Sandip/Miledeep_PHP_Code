<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once '../App/Controller/AppUtilController.php';

$ArrivalAutoGeneration = $ClientConfigData->ArrivalAutoGeneration;

class RmDashBoardController
{
	private $conn;
	private $db_table = "ScrmApplicants";
	private $utilController;
	private $appController;
	private $Error = null;
	private $Msg = '';

	private $TrackData = array();

	// Db connection
	public function __construct($db)
	{
		$this->conn = $db;
		$this->utilController = new UtilController($this->conn);
		$this->appController = new AppUtilController($this->conn);
	}

	public function getDashBoardData($RequestBody)
	{

		$FinClsYear = $RequestBody['Year'];
		$Month = trim($RequestBody['Month']);
		$FromDate = trim($RequestBody['FromDate']);
		$ToDate = trim($RequestBody['ToDate']);
		$Counts = trim($RequestBody['Counts']);

		//var_dump($RequestBody);
		//$FinClsYear=2023;

		if ($Month != '') {
			$Month = sprintf('%02d', $Month);
			if ($Month > 3) {
				$DateStart = $FinClsYear . '-' . $Month . '-01';
				$DateEnd = ($FinClsYear) . '-' . $Month . '-31';
			} else {
				$DateStart = ($FinClsYear + 1) . '-' . $Month . '-01';
				$DateEnd = ($FinClsYear + 1) . '-' . $Month . '-31';
			}
			$DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";
		} else {
			$DateStart = $FinClsYear . '-04-01';
			$DateEnd = ($FinClsYear + 1) . '-03-31';
			$DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";
		}

		$DateFilter = " BETWEEN '$FromDate' and '$ToDate'";
		$RequestBody['DateFilter'] = $DateFilter;

		$Qry = $this->getFilters($RequestBody);
		$OAAQry = $this->getOverAllArrivalFilters($RequestBody);
		$this->TrackData = $this->getTrackIDs($RequestBody, $Qry);
		$SpeciesData = $this->getSpeciesData($RequestBody, $Qry, $OAAQry);
		$PlantData = $this->getPlantData($RequestBody, $Qry, $OAAQry);
		$CenterData = $this->getCenterData($RequestBody, $Qry, $OAAQry);
		$GroupQtyData = $this->getGroupQtyData($RequestBody, $Qry);
		$aryProductionData = $this->getProductionData($RequestBody, $Qry);
		$RmRates = $this->getRmRates($RequestBody, $Qry);
		$RmCountsList = $this->getRmCounts($RequestBody, $Qry);
		$RmRateID = $this->getCurrentRateID($RequestBody, $Qry);
		// $RmFreightData = $this->getRmFreightCharges($RequestBody, $Qry);
		$ProductionData = $aryProductionData['ProductionData'];


		$VarietyData = $this->getVarietyData($RequestBody, $Qry, $OAAQry);
		$SupplierData = $this->getSupplierData($RequestBody, $Qry, $OAAQry);



		$Data['Error'] = null;
		$Data['Msg'] = 'Successful';
		$Data['RmRateID'] = $RmRateID;
		$Data['CountRange'] = implode(",", $RmCountsList);
		$Data['RmRates'] = $RmRates;
		$Data['SpeciesData'] = $SpeciesData;
		$Data['PlantData'] = $PlantData;
		$Data['CenterData'] = $CenterData;
		$Data['VarietyData'] = $VarietyData;
		$Data['SupplierData'] = $SupplierData;
		$Data['GroupQtyData'] = $GroupQtyData;
		$Data['ProductionData'] = $ProductionData;
		// $Data['FreightData'] = $RmFreightData;
		//$Data['VAData'] = $VAData;


		return $Data;
	}



	public function getSpeciesData($RequestBody, $Qry, $OAAQry)
	{
		$sqlCQuery = "SELECT Species,sum(Weight) as SpeciesQty  FROM `RmArrivalDetatils` as RAD 
		left join RmArrivalBreakUpDetails as RABD on RAD.RmArrivalID=RABD.ArrivalID $Qry GROUP by Species order by Species asc";

		$sqlCQuery = "SELECT Species,round(sum(TotalWeight),3) as SpeciesQty  FROM `RmOverAllArrival` as RAD $OAAQry GROUP by Species order by Species asc";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {
			$aryData[] = $dataRow;
		}
		return $aryData;
	}

	public function getPlantData($RequestBody, $Qry, $OAAQry)
	{
		$sqlCQuery = "SELECT RPS.ProcessingStationID as PlantID,StationName as PlantName,sum(Weight) as PlantQty  FROM `RmArrivalDetatils` as RAD 
		left join RmArrivalBreakUpDetails as RABD on RAD.RmArrivalID=RABD.ArrivalID 
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=RAD.ProcessingStationID $Qry GROUP by RPS.ProcessingStationID order by Weight desc";


		$sqlCQuery = "SELECT RPS.ProcessingStationID as PlantID,StationName as PlantName,round(sum(TotalWeight),3) as PlantQty FROM `RmOverAllArrival` RAD 
					  left join RmProcessingStations as RPS on RPS.ProcessingStationID=RAD.PlantID $OAAQry GROUP by RPS.ProcessingStationID order by PlantQty desc";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

			$aryData[] = $dataRow;

		}
		return $aryData;
	}


	public function getCenterData($RequestBody, $Qry, $OAAQry)
	{
		$sqlCQuery = "SELECT RPS.PurchaseStationID as CenterID,StationName as CenterName,sum(Weight) as CenterQty  FROM `RmArrivalDetatils` as RAD 
		left join RmArrivalBreakUpDetails as RABD on RAD.RmArrivalID=RABD.ArrivalID 
        left join RmPurchaseStations as RPS on RPS.PurchaseStationID=RAD.ArrivalStationID $Qry GROUP by RPS.PurchaseStationID order by Weight desc";


		$sqlCQuery = "SELECT RPS.PurchaseStationID as CenterID,StationName as CenterName,round(sum(TotalWeight),3) as CenterQty FROM `RmOverAllArrival` RAD 
						left join RmPurchaseStations as RPS on RPS.PurchaseStationID=RAD.CenterID  $OAAQry
						 GROUP by RPS.PurchaseStationID order by CenterQty desc";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

			$aryData[] = $dataRow;

		}
		return $aryData;
	}



	public function getVarietyData($RequestBody, $Qry)
	{
		$sqlCQuery = "SELECT RABD.Variety as VarietyName,round(sum(Weight),3) as CenterQty  FROM `RmArrivalDetatils` as RAD 
		left join RmArrivalBreakUpDetails as RABD on RAD.RmArrivalID=RABD.ArrivalID  $Qry GROUP by RABD.Variety order by Variety asc";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

			$aryData[] = $dataRow;

		}
		return $aryData;
	}
	public function getSupplierData($RequestBody, $Qry)
	{
		// $sqlCQuery = "SELECT RS.ID as SupplierID,RS.FinancierName as SupplierName,sum(Weight) as SupplierQty  FROM `RmArrivalDetatils` as RAD 
		// left join RmArrivalBreakUpDetails as RABD on RAD.RmArrivalID=RABD.ArrivalID 
		// left join RmSuppliers as RS on RS.ID=RAD.RmSupplierID   $Qry GROUP by RAD.RmSupplierID order by SupplierQty DESC limit 20";
		// $stmtC = $this->conn->prepare($sqlCQuery);
		// $stmtC->execute();
		// while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

		// 	$aryData[] = $dataRow;

		// }
		return $aryData;
	}




	public function getGroupQtyData($RequestBody, $Qry)
	{

		$Gap = 5;
		$sqlCQuery = "SELECT 
		FLOOR(RABD.Count / $Gap) * $Gap AS CountRangeStart, 
    FLOOR(RABD.Count / $Gap) * $Gap + $Gap AS CountRangeEnd,
    round(SUM(RABD.Weight),3) AS GroupQty
FROM `RmArrivalDetatils` AS RAD
LEFT JOIN `RmArrivalBreakUpDetails` AS RABD ON RAD.RmArrivalID = RABD.ArrivalID
		 $Qry GROUP BY CountRangeStart
ORDER BY RABD.Count ASC;";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

			$GroupName = $dataRow['CountRangeStart'] . "-" . $dataRow['CountRangeEnd'];
			$dataRow['GroupName'] = $GroupName;

			$aryData[] = $dataRow;

		}
		return $aryData;
	}



	/***************************************************************************************************************************** */


	public function getProductionData($RequestBody, $Qry)
	{

		/**************************Out Quantities**************************/
		$HonQty = $this->getHonQty($RequestBody, $Qry);
		$GradingQty = $this->getGradingQty($RequestBody, $Qry);
		$VAQty = $this->getVAQty($RequestBody, $Qry);
		$SoakOutQty = $SoakingQty = $this->getSoakingQty($RequestBody, $Qry);
		$FreezingQty = $this->getFreezingQty($RequestBody, $Qry);


		/**************************In Quantities**************************/

		$FreezingQtyF1 = $this->getFreezingQtyF1($RequestBody, $Qry); //
		$FreezingQtyF2 = $this->getFreezingQtyF2($RequestBody, $Qry); // 

		$SoakInQty = $this->getSoakingInQty($RequestBody, $Qry); //
		$SoakingInQtyS1 = $this->getSoakingInQtyS1($RequestBody, $Qry); //
		$SoakingInQtyS2 = $this->getSoakingQtyS2($RequestBody, $Qry); //


		$VaGradingQty = $this->getVaGradingQty($RequestBody, $Qry); //
		// $ValueAdditionInQty = $this->getValueAdditionInQty($ReceivingID, $ReceivingPlantID); // 


		//var_dump($VaGradingQty);
		$FreezingInQty = $this->getFreezingInQty($RequestBody, $Qry); //

		//$SoakingInQtyS12 = $SoakingInQtyS1 + $SoakingInQtyS2; // Grd Sent Qty && VA Sent Qty
		// $VAINQty = $SoakOutQty + $FreezingQtyF1 + $FreezingQtyF2; //
		// $SoakingBalanceQty = $SoakInQty - $SoakingInQtyS12; //

		//$VAINQty=$this->getVAQty($RequestBody, $Qry);
		$SoakingINQty = $this->getSoakingQty($RequestBody, $Qry);
		$FreezingINQty = $this->getFreezingQty($RequestBody, $Qry);



		foreach ($HonQty as $HonItem) {

			$dataRow = array();
			$dataRow['PlantID'] = $HonItem['PlantID'];
			$dataRow['PlantName'] = $HonItem['PlantName'];
			$dataRow['HeadOnQty'] = round($HonItem['HeadOnQty'], 3);
			$dataRow['GradingQty'] = 0;
			$dataRow['VaInQty'] = 0;
			$dataRow['VaOutQty'] = 0;
			$dataRow['VaSentQty'] = 0;

			$dataRow['SoakOutQty'] = 0;
			$dataRow['FreezingQty'] = 0;
			$dataRow['GradingBalQty'] = 0;
			$dataRow['VaBalQty'] = 0;
			$dataRow['SoakBalQty'] = 0;
			$dataRow['FreezingBalQty'] = 0;

			foreach ($GradingQty as $GardingItem) {
				if ($HonItem['PlantID'] == $GardingItem['PlantID']) {
					$dataRow['GradingQty'] = round($GardingItem['GradingQty'], 3);
				}
			}


			foreach ($SoakingQty as $SoakingItem) {
				if ($HonItem['PlantID'] == $SoakingItem['PlantID']) {
					$dataRow['SoakOutQty'] = round($SoakingItem['SoakOutQty'], 3);
				}
			}

			foreach ($FreezingQty as $FreezingItem) {
				if ($HonItem['PlantID'] == $FreezingItem['PlantID']) {
					$dataRow['FreezingQty'] = round($FreezingItem['FreezingQty'], 3);
				}
			}


			foreach ($VAQty as $VAItem) {
				if ($HonItem['PlantID'] == $VAItem['PlantID']) {
					$dataRow['VaInQty'] = round($VAItem['VaInQty'], 3);
				}
			}
			foreach ($VAQty as $VAItem) {
				if ($HonItem['PlantID'] == $VAItem['PlantID']) {
					$dataRow['VaOutQty'] = round($VAItem['VaOutQty'], 3);
				}
			}
			foreach ($VaGradingQty as $VAGItem) {
				if ($HonItem['PlantID'] == $VAGItem['PlantID']) {
					$dataRow['VaGradingQty'] = round($VAGItem['VaGradingQty'], 3);
				}
			}



			$dataRow['VaBalQty'] = $dataRow['VaGradingQty'] - $dataRow['VaInQty'];
			//$dataRow['VaBalQty']=$dataRow['VaGradingQty'];


			foreach ($SoakingInQtyS1 as $S1Item) {
				if ($HonItem['PlantID'] == $S1Item['PlantID']) {
					$dataRow['SoakinQtyS1'] = round($S1Item['SoakingInQty'], 3);
				}
			}

			foreach ($SoakingInQtyS2 as $S2Item) {
				if ($HonItem['PlantID'] == $S2Item['PlantID']) {
					$dataRow['SoakinQtyS2'] = round($S2Item['SoakingVAQty'], 3);
				}
			}

			foreach ($SoakInQty as $SItem) {
				if ($HonItem['PlantID'] == $SItem['PlantID']) {
					$dataRow['SoakInQty'] = round($SItem['SoakInQty'], 3);
				}
			}

			$dataRow['SoakBalQty'] = $dataRow['SoakinQtyS1'] + $dataRow['SoakinQtyS2'] - $dataRow['SoakInQty'];
			//$FreezingQtyF1

			foreach ($FreezingQtyF1 as $F1Item) {
				//echo $F1Item['FreezingQty'];
				if ($HonItem['PlantID'] == $F1Item['PlantID']) {

					$dataRow['FreezingHLQty'] = round($F1Item['FreezingQty'], 3);
				}
			}
			//	echo $dataRow['FreezingQty'];
//var_dump($dataRow['FreezingQty']);;
			foreach ($FreezingQtyF2 as $F2Item) {
				if ($HonItem['PlantID'] == $F2Item['PlantID']) {
					$dataRow['FreezingVAQty'] = round($F2Item['FreezingVAQty'], 3);
				}
			}

			//$dataRow['FreezingBalQty'] = $dataRow['SoakOutQty']+ $dataRow['FreezingVAQty'] - $dataRow['FreezingQty'] ;
			$dataRow['FreezingBalQty'] = ($dataRow['FreezingHLQty'] + $dataRow['FreezingVAQty'] + $dataRow['SoakOutQty']) - $dataRow['FreezingQty'];


			$HLYield = round($dataRow['GradingQty'] / $dataRow['HeadOnQty'] * 100, 2);
			$FinishedYield = round($dataRow['FreezingQty'] / $dataRow['HeadOnQty'] * 100, 2);
			$dataRow['PlantName'] = $HonItem['PlantName'];
			$dataRow['PlantNameWithYield'] = $HonItem['PlantName'] . ' HL-' . $HLYield . '%, F-' . $FinishedYield . '%';

			$aryData[] = $dataRow;
		}

		$data['ProductionData'] = $aryData;
		//$data['ProductionPendingData']=$aryDataPending;

		return $data;
	}
	public function getHonQty($RequestBody, $Qry)
	{
		$sqlCQuery = "SELECT RAD.ProcessingStationID as PlantID,round(sum(ReceivingQty),3) as HeadOnQty,StationName as PlantName  FROM `RmArrivalDetatils` as RAD 
					  left join RmReceivingBreakup as RABD on RAD.RmArrivalID=RABD.ArrivalID 
					   left join RmProcessingStations as RPS on RPS.ProcessingStationID=RAD.ProcessingStationID
					  $Qry GROUP by RAD.ProcessingStationID order by RAD.ProcessingStationID asc;";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {
			$aryData[] = $dataRow;
		}
		return $aryData;
	}

	public function getGradingQty($RequestBody, $Qry)
	{
		$TrackId = $this->TrackData['TrackId'];
		$TrackBrkId = $this->TrackData['TrackBrkId'];

		$sqlCQuery = "SELECT HLGD.PlantID as PlantID,round(sum(HLGDB.HLWeight),3) as GradingQty FROM `PpcHLGradingDetails` as HLGD 
		INNER JOIN PpcHLGradingBreakup as HLGDB on HLGDB.LotID=HLGD.LotID  where TrackID in ($TrackId) GROUP by HLGD.PlantID
		ORDER BY `PlantID` ASC";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {
			$aryData[] = $dataRow;
		}
		return $aryData;
	}
	public function getVAQty($RequestBody, $Qry)
	{
		$TrackId = $this->TrackData['TrackId'];
		$TrackBrkId = $this->TrackData['TrackBrkId'];

		$sqlCQuery = "SELECT PlantID,SUM(SentWeight) as VaInQty,round(sum(WtAftrVA),3) as VaOutQty FROM `PpcValueAddition` where TrackID in ($TrackId) GROUP by PlantID ORDER by PlantID;";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {
			$aryData[] = $dataRow;
		}
		return $aryData;
	}
	public function getSoakingQty($RequestBody, $Qry)
	{
		$TrackId = $this->TrackData['TrackId'];
		$TrackBrkId = $this->TrackData['TrackBrkId'];

		$sqlCQuery = "SELECT PlantID,round(SUM(SoakInQty),3) as SoakInQty,round(sum(SoakOutQty),3) as SoakOutQty FROM `ProdSoakingDetails` where TrackID in ($TrackId) GROUP by PlantID ORDER by PlantID;";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {
			$aryData[] = $dataRow;
		}
		return $aryData;
	}
	public function getFreezingQty($RequestBody, $Qry)
	{
		$TrackId = $this->TrackData['TrackId'];
		$TrackBrkId = $this->TrackData['TrackBrkId'];

		$Qry = '';
		if ($RequestBody['PlantID'] != '') {
			$PlantID = $RequestBody['PlantID'];
			$Qry .= " and  ReceivingPlantID in ($PlantID)";
		}
		$DateFilter = $RequestBody['DateFilter'];
		$sqlTracks = "SELECT GROUP_CONCAT(DISTINCT(ReceivingID)) as TrackId FROM RmReceivingDetails WHERE ArrivalDate $DateFilter AND VehicleID=0 AND OutStage=3 AND IsStoreMaterial=0 $Qry";
		$stmtTracks = $this->conn->prepare($sqlTracks);
		$stmtTracks->execute();
		$dataRow = $stmtTracks->fetch(PDO::FETCH_ASSOC);

		if (!empty($dataRow['TrackId'])) {
			$TrackId = !empty($TrackId) ? $TrackId . ',' . $dataRow['TrackId'] : $dataRow['TrackId'];
		}

		$sqlCQuery = "SELECT PlantID,round(SUM(ProductionWeight),3) as FreezingQty FROM `ProdFreezingData` where TrackID in ($TrackId) GROUP by PlantID ORDER by PlantID;";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {
			$aryData[] = $dataRow;
		}
		return $aryData;
	}


	/***********************************************RM  Prices Data************************************************************ */


	public function getRmRates($RequestBody, $Qry)
	{

		$RmFromDate = $RequestBody["RmFromDate"];
		$RmToDate = $RequestBody["RmToDate"];

		if ($RequestBody["Species"] == 'BT') {
			$DefCounts = '10,25,35';
			$Stype = $RequestBody["Species"];
		} else {
			$DefCounts = '80,90,100';
			$Stype = 'VM';
		}





		$RmSelectedRmCounts = $RequestBody['Counts'] ? $RequestBody['Counts'] : $DefCounts;
		$SType = $RequestBody["Species"] ? $RequestBody["Species"] : "VM";
		$query = "GROUP_CONCAT( RmCount, ':', RmPrice)  ";
		$sqlCQuery = "SELECT  RmPriceDate, $query as RmCount ,GROUP_CONCAT(RmPrice) as RmPrice FROM `RmPurchaseRates`  as  RPR LEFT join RmPurchaseRatesHistory as RPRH on RPR.DateID=RPRH.ID
	WHERE RmPriceDate BETWEEN '$RmFromDate' and '$RmToDate' and RmCount in ($RmSelectedRmCounts) and SType='$SType'  GROUP by RmPriceDate order by RmPriceDate asc";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

			$aryData[] = $dataRow;

		}
		$arySelectedCounts = explode(',', $RmSelectedRmCounts);


		foreach ($aryData as $Item) {
			$DataItem = array();

			$DataItem['Date'] = $Item['RmPriceDate'];
			$RmCount = $Item['RmCount'];

			$aryRmCount = explode(',', $RmCount);

			// Initialize an empty array to hold key-value pairs
			$result = [];

			// Loop through each pair
			foreach ($aryRmCount as $pair) {
				list($key, $value) = explode(':', $pair);
				$result[$key] = (int) $value; // Optionally cast value to an integer
			}

			$result['Date'] = $Item['RmPriceDate'];
			$aryRateData[] = $result;
		}



		return $aryRateData;
	}

	/***********************************************RM  Freight Data************************************************************ */


	public function getRmFreightCharges($RequestBody, $Qry)
	{

		$RmFromDate = $RequestBody["FreightFromDate"];
		$RmToDate = $RequestBody["FreightToDate"];
		$sqlCQuery = "SELECT 
						Liner,
						FromDate,
						GROUP_CONCAT(Rate) AS RmRate,
    					GROUP_CONCAT( LinerName, ':', Rate) AS RmLinerRate
					FROM 
						ScrmFreightRates AS SFR
    					LEFT join ScrmLinerMaster as SLM on SLM.LinerID=SFR.Liner
					WHERE 
						FromDate BETWEEN '$RmFromDate' AND '$RmToDate'
						GROUP by FromDate
					ORDER BY 
						FromDate ASC, Liner";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {
			$aryData[] = $dataRow;
		}


		foreach ($aryData as $Item) {
			$DataItem = array();

			$DataItem['Date'] = $Item['FromDate'];
			$RmLinerRate = $Item['RmLinerRate'];

			$arrayRmLinerRate = explode(',', $RmLinerRate);

			// Initialize an empty array to hold key-value pairs
			$result = [];

			// Loop through each pair
			foreach ($arrayRmLinerRate as $pair) {
				list($key, $value) = explode(':', $pair);
				$result[$key] = (int) $value; // Optionally cast value to an integer
			}

			$result['Date'] = $Item['FromDate'];
			$aryFreightData[] = $result;
		}



		return $aryFreightData;
	}


	/***********************************************Pending Production Data************************************************************ */

	public function getRmCounts($RequestBody, $Qry)
	{

		$TrackId = $this->TrackData['TrackId'];
		$TrackBrkId = $this->TrackData['TrackBrkId'];

		if ($RequestBody["Species"] == 'BT') {
			$Stype = $RequestBody["Species"];
		} else {
			$Stype = 'VM';
		}


		$sqlQuery = "SELECT DISTINCT (RmCount)  RmCount FROM `RmRatesRange` WHERE Stype='$Stype' ORDER BY RmCount ASC";
		$stmtSqlQuery = $this->conn->prepare($sqlQuery);
		$stmtSqlQuery->execute();
		//$dataRowSoakingInQtyS1 = $stmtSoakingInQtyS1->fetch(PDO::FETCH_ASSOC);

		while ($dataRow = $stmtSqlQuery->fetch(PDO::FETCH_ASSOC)) {
			//var_dump($dataRow);
			$aryData[] = $dataRow['RmCount'];
		}

		sort($aryData);
		return $aryData;

	}
	public function getCurrentRateID($RequestBody, $Qry)
	{

		if ($RequestBody["Species"] == 'BT') {
			$Stype = $RequestBody["Species"];
		} else {
			$Stype = 'VM';
		}

		$sqlQuery = "SELECT ID FROM `RmPurchaseRatesHistory`  WHERE SType ='$Stype' ORDER by RmPriceDate DESC limit 1";
		$stmtSqlQuery = $this->conn->prepare($sqlQuery);
		$stmtSqlQuery->execute();
		//$dataRowSoakingInQtyS1 = $stmtSoakingInQtyS1->fetch(PDO::FETCH_ASSOC);

		$dataRow = $stmtSqlQuery->fetch(PDO::FETCH_ASSOC);

		//sort($aryData);
		return $dataRow['ID'];

	}


	public function getVaGradingQty($ReceivingID, $ReceivingPlantID)
	{

		$TrackId = $this->TrackData['TrackId'];
		$TrackBrkId = $this->TrackData['TrackBrkId'];

		$sqlFields = "PlantID,sum(HLWeight) AS VaGradingQty";
		$sqlVaGradingQty = "SELECT $sqlFields FROM `PpcHLGradingDetails` AS PHGD 
	LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID=PHGD.LotID 
	WHERE TrackID in ($TrackId) AND IsBlock = 0 GROUP by PlantID ORDER by PlantID";
		$stmtVaGradingQty = $this->conn->prepare($sqlVaGradingQty);
		$stmtVaGradingQty->execute();
		while ($dataRow = $stmtVaGradingQty->fetch(PDO::FETCH_ASSOC)) {
			$aryData[] = $dataRow;
		}
		return $aryData;
	}

	public function getSoakingInQtyS1($ReceivingID, $ReceivingPlantID)
	{

		$TrackId = $this->TrackData['TrackId'];
		$TrackBrkId = $this->TrackData['TrackBrkId'];

		$sqlFields = "PlantID, round(sum(HLWeight),3) AS SoakingInQty";
		$sqlSoakingInQtyS1 = "SELECT $sqlFields FROM `PpcHLGradingDetails` AS PHGD 
	LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID=PHGD.LotID 
	WHERE TrackID in ($TrackId)   AND Treatment != 'UNT' AND IsBlock = 1 GROUP by PlantID ORDER by PlantID";
		$stmtSoakingInQtyS1 = $this->conn->prepare($sqlSoakingInQtyS1);
		$stmtSoakingInQtyS1->execute();
		//$dataRowSoakingInQtyS1 = $stmtSoakingInQtyS1->fetch(PDO::FETCH_ASSOC);

		while ($dataRow = $stmtSoakingInQtyS1->fetch(PDO::FETCH_ASSOC)) {
			//var_dump($dataRow);
			$aryData[] = $dataRow;
		}
		return $aryData;

	}

	public function getSoakingQtyS2($ReceivingID, $ReceivingPlantID)
	{

		$TrackId = $this->TrackData['TrackId'];
		$TrackBrkId = $this->TrackData['TrackBrkId'];


		$sqlFields = "PlantID,round(sum(WtAftrVA),3) as SoakingVAQty";
		$sqlSoakingQty2 = "SELECT $sqlFields FROM `PpcValueAddition` AS PVA
	LEFT JOIN PpcHLGradingBreakup AS PHGB ON PVA.GradingBreakupID = PHGB.DataID
	WHERE TrackID in ($TrackId)  AND Treatment != 'UNT' AND IsBlock = 0 GROUP by PlantID ORDER by PlantID";
		$stmtSoakingQty2 = $this->conn->prepare($sqlSoakingQty2);
		$stmtSoakingQty2->execute();
		while ($dataRow = $stmtSoakingQty2->fetch(PDO::FETCH_ASSOC)) {
			$aryData[] = $dataRow;
		}
		return $aryData;
	}

	public function getSoakingInQty($ReceivingID, $ReceivingPlantID)
	{

		$TrackId = $this->TrackData['TrackId'];
		$TrackBrkId = $this->TrackData['TrackBrkId'];


		$sqlFields = "PlantID,SUM(SoakInQty) as SoakInQty";
		$sqlSoakingInQty = "SELECT $sqlFields FROM `ProdSoakingDetails` 
	WHERE TrackID in ($TrackId) GROUP by PlantID ORDER by PlantID";
		$stmtSoakingInQty = $this->conn->prepare($sqlSoakingInQty);
		$stmtSoakingInQty->execute();
		//$dataRowSoakingQty = $stmtSoakingInQty->fetch(PDO::FETCH_ASSOC);
		while ($dataRow = $stmtSoakingInQty->fetch(PDO::FETCH_ASSOC)) {
			$aryData[] = $dataRow;
		}
		return $aryData;
	}


	// public function getSoakingOutQty($ReceivingID, $ReceivingPlantID)
// {
// 	$sqlFields = "SUM(SoakOutQty) as SoakOutQty";
// 	$sqlSoakingInQty = "SELECT $sqlFields FROM `ProdSoakingDetails` 
// 	WHERE TrackID='$ReceivingID' AND ProductionPlantID='$ReceivingPlantID'";
// 	$stmtSoakingInQty = $this->conn->prepare($sqlSoakingInQty);
// 	$stmtSoakingInQty->execute();
// 	$dataRowSoakingQty = $stmtSoakingInQty->fetch(PDO::FETCH_ASSOC);
// 	return $dataRowSoakingQty;
// }


	public function getFreezingQtyF1($ReceivingID, $ReceivingPlantID)
	{

		$TrackId = $this->TrackData['TrackId'];
		$TrackBrkId = $this->TrackData['TrackBrkId'];

		$sqlFields = "PlantID,sum(HLWeight)as FreezingQty";
		$sqlFreezingQtyF1 = "SELECT $sqlFields FROM `PpcHLGradingDetails` AS PHGD 
	LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID=PHGD.LotID 
	WHERE TrackID in ($TrackId)  AND Treatment = 'UNT' AND IsBlock = 1  GROUP by PlantID ORDER by PlantID";
		$stmtFreezingQtyF1 = $this->conn->prepare($sqlFreezingQtyF1);
		$stmtFreezingQtyF1->execute();


		while ($dataRowFreezingQtyF1 = $stmtFreezingQtyF1->fetch(PDO::FETCH_ASSOC)) {
			$aryData[] = $dataRowFreezingQtyF1;
		}

		return $aryData;
	}



	public function getFreezingQtyF2($ReceivingID, $ReceivingPlantID)
	{

		$TrackId = $this->TrackData['TrackId'];
		$TrackBrkId = $this->TrackData['TrackBrkId'];
		$sqlFields = "PlantID,sum(WtAftrVA) as FreezingVAQty";
		$sqlFreezingQty2 = "SELECT $sqlFields FROM `PpcValueAddition` AS PVA
	LEFT JOIN PpcHLGradingBreakup AS PHGB ON PVA.GradingBreakupID = PHGB.DataID
	WHERE TrackID in ($TrackId) and Treatment = 'UNT' AND IsBlock = 0 GROUP by PlantID ORDER by PlantID";
		$stmtFreezingQty2 = $this->conn->prepare($sqlFreezingQty2);
		$stmtFreezingQty2->execute();
		//$dataRowFreezingQty2 = $stmtFreezingQty2->fetch(PDO::FETCH_ASSOC);

		while ($dataRowFreezingQty2 = $stmtFreezingQty2->fetch(PDO::FETCH_ASSOC)) {
			$aryData[] = $dataRowFreezingQty2;
		}
		return $aryData;
	}


	public function getFreezingInQty($ReceivingID, $ReceivingPlantID)
	{
		$sqlFields = "sum(ProductionWeight) as FreezingInQty";
		$sqlFreezingInQty = "SELECT $sqlFields FROM `ProdFreezingData` 
	WHERE TrackID='$ReceivingID' AND PlantID='$ReceivingPlantID'";
		$stmtFreezingInQty = $this->conn->prepare($sqlFreezingInQty);
		$stmtFreezingInQty->execute();
		$dataRowFreezingInQty = $stmtFreezingInQty->fetch(PDO::FETCH_ASSOC);
		return $dataRowFreezingInQty;
	}

	// 	public function getPlantGradingData($RequestBody, $Qry)
// 	{


	// 		 $sqlCQuery = "SELECT  
//     HGD.PlantID as PlantID,  
//     RPS.StationName as PlantName,
//     round(SUM(COALESCE(HLWeight, 0)),3) as PlantHLQty

	// FROM  
//     `RmReceivingDetails` AS RRD
// INNER JOIN 
//     `RmReceivingBreakup` AS RRB 
//     ON RRB.ReceivingID = RRD.ReceivingID
// INNER JOIN 
//     `RmArrivalDetatils` AS RAD 
//     ON RAD.RmArrivalID = RRB.ArrivalID 

	// INNER JOIN 
//     `PpcHLGradingDetails` AS HGD  
//     ON HGD.TrackBreakUpID = RRB.ReceivalBreakupID
// INNER JOIN 
//     `PpcHLGradingBreakup` AS HLGB 
//     ON HLGB.LotID = HGD.LotID

	// INNER JOIN 
//     `RmProcessingStations` AS RPS 
//     ON RPS.ProcessingStationID = HGD.PlantID


	// $Qry
//     AND RRD.IsStoreMaterial = 0
// GROUP BY 
//     HGD.PlantID
// ORDER BY 
//     PlantHLQty DESC;";
// 		$stmtC = $this->conn->prepare($sqlCQuery);
// 		$stmtC->execute();
// 		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

	// 			$dataRow['HeadOnQty']=$dataRow['PlantHLQty'] + 200;
// 			$dataRow['GradingQty']=$dataRow['PlantHLQty'] -200; ;
// 			$dataRow['VAQty']=$dataRow['PlantHLQty'] -400;
// 			$dataRow['SoakingQty']=$dataRow['PlantHLQty'] -300;
// 			$dataRow['FreezingQty']=$dataRow['PlantHLQty'] -300;
// 			$dataRow['HlYield']='70';
// 			$dataRow['FinalYield']='69.5';
// 			//$dataRow['PlantName']=$dataRow['PlantName'];
// 			$aryData[] = $dataRow;

	// 		}
// 		return $aryData;
// 	}


	// 	public function getVaGradingQty($RequestBody, $Qry)
//     {
// 			 $sqlCQuery = "SELECT  
// 		HGD.PlantID as PlantID,  
// 		RPS.StationName as PlantName,
// 		round(SUM(COALESCE(SentWeight, 0)),3) as PlantVAQty
// 	FROM  
// 		`RmReceivingDetails` AS RRD
// 	INNER JOIN 
// 		`RmReceivingBreakup` AS RRB 
// 		ON RRB.ReceivingID = RRD.ReceivingID
// 	INNER JOIN 
// 		`RmArrivalDetatils` AS RAD 
// 		ON RAD.RmArrivalID = RRB.ArrivalID 

	// 	INNER JOIN 
// 		`PpcHLGradingDetails` AS HGD  
// 		ON HGD.TrackBreakUpID = RRB.ReceivalBreakupID
// 	INNER JOIN 
// 		`PpcHLGradingBreakup` AS HLGB 
// 		ON HLGB.LotID = HGD.LotID
// 	INNER JOIN 
// 		`PpcValueAddition` AS PVA 
// 		ON PVA.GradingBreakupID = HLGB.DataID
// 	INNER JOIN 
// 		`RmProcessingStations` AS RPS 
// 		ON RPS.ProcessingStationID = HGD.PlantID


	// $Qry
// 		AND RRD.IsStoreMaterial = 0
// 	GROUP BY 
// 		HGD.PlantID
// 	ORDER BY 
// 		PlantVAQty DESC;";
// 			$stmtC = $this->conn->prepare($sqlCQuery);
// 			$stmtC->execute();
// 			while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

	// 				$aryData[] = $dataRow;

	// 			}
// 			return $aryData;
//     }




	public function getFilters($RequestBody)
	{
		$DateFilter = $RequestBody['DateFilter'];

		$Qry = "";

		$Qry = "Where RAD.ArrivalDate $DateFilter";

		if ($RequestBody['Species'] != '') {
			$Species = explode(",", $RequestBody['Species']);
			$Species = implode("','", $Species);
			$Qry .= " and  RAD.Species in  ('$Species')";
		}

		if ($RequestBody['Variety'] != '') {
			$Variety = explode(",", $RequestBody['Variety']);
			$Variety = implode("','", $Variety);
			$Qry .= " and  RABD.Variety in  ('$Variety')";
		}

		if ($RequestBody['PlantID'] != '') {
			$PlantID = $RequestBody['PlantID'];
			$Qry .= " and  RAD.ProcessingStationID in  ($PlantID)";
		}
		if ($RequestBody['CenterID'] != '') {
			$CenterID = $RequestBody['CenterID'];
			$Qry .= " and  RAD.ArrivalStationID in  ($CenterID)";
		}
		if ($RequestBody['SupplierID'] != '') {
			$SupplierID = $RequestBody['SupplierID'];
			$Qry .= " and  RAD.RmSupplierID in  ($SupplierID)";
		}



		return $Qry;
	}

	public function getOverAllArrivalFilters($RequestBody)
	{
		$DateFilter = $RequestBody['DateFilter'];

		$Qry = "";

		$Qry = "Where RAD.ArrivalDate $DateFilter";

		if ($RequestBody['Species'] != '') {
			$Species = explode(",", $RequestBody['Species']);
			$Species = implode("','", $Species);
			$Qry .= " and  RAD.Species in  ('$Species')";
		}

		if ($RequestBody['Variety'] != '') {
			$Variety = explode(",", $RequestBody['Variety']);
			$Variety = implode("','", $Variety);
			$Qry .= " and  RABD.Variety in  ('$Variety')";
		}

		if ($RequestBody['PlantID'] != '') {
			$PlantID = $RequestBody['PlantID'];
			$Qry .= " and  RAD.PlantID in  ($PlantID)";
		}
		if ($RequestBody['CenterID'] != '') {
			$CenterID = $RequestBody['CenterID'];
			$Qry .= " and  RAD.CenterID in  ($CenterID)";
		}
		if ($RequestBody['SupplierID'] != '') {
			$SupplierID = $RequestBody['SupplierID'];
			$Qry .= " and  RAD.RmSupplierID in  ($SupplierID)";
		}



		return $Qry;
	}


	public function getTrackIDs($RequestBody, $Qry)
	{

		$sqlCQuery = "SELECT GROUP_CONCAT(DISTINCT(RRD.ReceivingID)) as TrackId,GROUP_CONCAT(RRB.ReceivalBreakupID) as TrackBrkID FROM `RmReceivingDetails` as RRD 
						INNER JOIN `RmReceivingBreakup` as RRB on RRB.ReceivingID=RRD.ReceivingID
						INNER JOIN `RmArrivalDetatils`  as RAD on RAD.RmArrivalID=RRB.ArrivalID
 						 $Qry and RRD.IsStoreMaterial=0";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		// while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

		// 	$aryData[] = $dataRow;

		// }
		$dataRow = $stmtC->fetch(PDO::FETCH_ASSOC);
		return $dataRow;

	}


}
