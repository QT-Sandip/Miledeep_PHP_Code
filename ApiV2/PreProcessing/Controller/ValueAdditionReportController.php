<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class ValueAdditionReportController
{
	private $conn;
	private $utilController;
	private $Error = null;
	private $Msg = '';


	// Db connection
	public function __construct($db)
	{
		$this->conn = $db;
		$this->utilController = new UtilController($this->conn);
	}

	public function getValueAdditionDetails($RequestBody)
	{
		
		$ValueAdditionData = $this->getValueAdditionData($RequestBody);
		
		$Data['Error'] = $this->Error;
		$Data['Msg'] = "Success";
		$Data['ValueAdditionDetails'] = $ValueAdditionData;
		
		return $Data;
	}

	function getValueAdditionData($RequestBody)
	{

        $PlantID = $RequestBody['PlantID'];
		$FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
		$Station = $RequestBody['Station'];
		$ArrivalID = $RequestBody['ArrivalID'];

		
		$Qry = '';
		if ($PlantID != '') {
			$Qry = " AND PVA.PlantID = '$PlantID'";
		}
		if($Station!=''){
			$Qry = " AND PVA.StationID = '$Station'";
		}
		if ($ArrivalID != '') {
			$Qry .= " AND RLD.RmReferenceID = '$ArrivalID'";
		}
        //$DateFilter ="RRD.ArrivalDate between '$FromDate' and '$ToDate'";
		$DateFilter ="ProcessingDate between '$FromDate' and '$ToDate'";
		
                $sqlFields = "RLD.LotNumber as ArrivalNumber,ProcessingDate,RPS.StationName AS PlantName,COALESCE(UnitName,'')UnitName,ASD.ShiftName as Shift ,PackingGrade
		,WtAftrVA,SentCount,SentWeight, PPOV.ShortName as Variety,PVA.Remarks, RRD.TrackCode,RRD.ArrivalDate";

		       $sqlQuery = " SELECT $sqlFields		FROM PpcValueAddition AS PVA
		LEFT JOIN RmLotDetatils AS RLD ON RLD.RmLotID = PVA.LotID  
		LEFT JOIN PpcVAStations AS PVS ON PVS.StationID = PVA.StationID 
		LEFT JOIN AdminShiftDetails AS ASD ON ASD.ShiftID = PVA.ShiftID 
		LEFT JOIN PpcHLVAVarietyList AS PPOV ON PPOV.VarietyID = PVA.VarietyID
		LEFT JOIN RmProcessingStations AS RPS ON RPS.ProcessingStationID = PVA.PlantID
        left join PpcHLGradingBreakup as PHGB on PHGB.DataID=PVA.GradingBreakupID AND PHGB.CreatedFrom=1
		LEFT JOIN RmReceivingDetails AS RRD ON RRD.ReceivingID = PVA.TrackID  
		WHERE $DateFilter $Qry AND PVA.CreatedFrom=1 "; 

		$sqlQuery .= "ORDER BY RLD.LotNumber ASC";
		
		
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();

		$ArrayData = [];

		$TotalHLQuantity = 0;
		$TotalYield = 0;
		$TotalWtAftrVA = 0;
		$i = 0;
		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

           if ($dataRow['ArrivalNumber']=='') {

				$ArrivalNumber = 'Store Material';
			} else {
				$ArrivalNumber = $dataRow['ArrivalNumber'];
			}
			
			$WtAftrVA = $dataRow['WtAftrVA'];
			$HLQuantityKgs = $dataRow['SentWeight'];
			$Yield = ($WtAftrVA / $HLQuantityKgs) * 100;

			$ValueAdditionData['SNo'] = ++$i;
            $ValueAdditionData['BatchNumber'] = $ArrivalNumber;
			$ValueAdditionData['TrackCode'] = $dataRow['TrackCode'];
            $ValueAdditionData['ProductionPlant'] = $dataRow['PlantName'];
            $ValueAdditionData['Station'] = $dataRow['UnitName'];
            $ValueAdditionData['ValueAdditionDate'] = $this->utilController->getFormatedDate($dataRow['ProcessingDate']);
            $ValueAdditionData['Shift'] = $dataRow['Shift'];
            $ValueAdditionData['Variety'] = $dataRow['Variety'];
            $ValueAdditionData['Grade'] = $dataRow['PackingGrade'];
            $ValueAdditionData['HLWtKgs'] = $dataRow['SentWeight'];
            $ValueAdditionData['HLCount'] = $dataRow['SentCount'];
            $ValueAdditionData['WtAftrVA'] = $dataRow['WtAftrVA'];
            $ValueAdditionData['Yield'] = round($Yield, 2);
            $ValueAdditionData['Remarks'] = $dataRow['Remarks'];
			$ValueAdditionData['ArrivalDate'] = $this->utilController->getFormatedDate($dataRow['ArrivalDate']);

			

			$ArrayData[] = $ValueAdditionData;
			$TotalHLQuantity += $ValueAdditionData['HLWtKgs'];
			$TotalYield += $ValueAdditionData['Yield'];
			$TotalWtAftrVA += $ValueAdditionData['WtAftrVA'];
		}

		$ArrayTotals['TotalHLWtQuantity'] = $TotalHLQuantity;
		//$ArrayTotals['TotalYield'] = round($TotalYield/$i, 2);
		$ArrayTotals['TotalWtAftrVA'] = $TotalWtAftrVA;
		$ArrayTotals['TotalYield'] = round(($TotalWtAftrVA / $TotalHLQuantity)* 100, 2);

		$Data['ValueAdditionData'] = $ArrayData;
		$Data['qry'] = $sqlQuery;
		$Data['Totals'] = $ArrayTotals;

		return $Data;

	}

	function getArrivalsByFinancialYear($RequestBody)
	{
		//   error_log(print_r($RequestBody, true));
// echo print_r($RequestBody);
// exit;
		$fromDate = $RequestBody['FromDate'];
		$toDate = $RequestBody['ToDate'];
		$PlantID = $RequestBody['PlantID'];
		if (!$fromDate && !$toDate) {
			return false;
		}
		$Query = "";
		if ($fromDate) {
			$Query .= " AND ArrivalDate >= '$fromDate'";
		}
		if ($toDate) {
			$Query .= " AND ArrivalDate <= '$toDate'";
		}
		if ($PlantID != '') {
			$Query .= " AND ProcessingStationID = '$PlantID'";
		}



		$SqlArrivalsList = "SELECT RmArrivalID, ArrivalNumber
                            FROM RmArrivalDetatils
                            WHERE 1 
                            $Query
                            ORDER BY ArrivalDate DESC";

		// error_log(print_r($SqlArrivalsList, true));
		//  echo print_r($SqlArrivalsList);exit;

		$stmt = $this->conn->prepare($SqlArrivalsList);
		$stmt->execute();
		$ArrayArrivalList = [];

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$ArrayArrivalList[] = $row;
		}

		return $ArrayArrivalList;
	}

	
}
