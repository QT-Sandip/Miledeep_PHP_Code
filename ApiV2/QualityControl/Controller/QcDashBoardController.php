<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once '../App/Controller/AppUtilController.php';

$ArrivalAutoGeneration = $ClientConfigData->ArrivalAutoGeneration;

class QcDashBoardController
{
	private $conn;
	private $db_table = "ScrmApplicants";
	private $utilController;
	private $appController;
	private $Error = null;
	private $Msg = '';

	private $OrderData=array();

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

		$DateFilter=" BETWEEN '$FromDate' and '$ToDate'";
		$RequestBody['DateFilter'] = $DateFilter;

		// $Qry = $this->getFilters($RequestBody);
		// $OAAQry = $this->getOverAllArrivalFilters($RequestBody);
		//$VaGradingQty = $this->getCodeListStatus($RequestBody, $Qry); //
		$this->OrderData = $this->getOrderIDs($RequestBody, $Qry);
		$PlantData = $this->getPlantData($RequestBody, $Qry,$OAAQry);
		$LabData = $this->getExtLabData($RequestBody, $Qry,$OAAQry);


		$Data['Error'] = null;
		$Data['Msg'] = 'Successful';
		$Data['RmRateID'] = $RmRateID;
		$Data['CountRange'] =implode(",",$RmCountsList);
		$Data['RmRates'] = $RmRates;
		$Data['SpeciesData'] = $SpeciesData;
		$Data['PlantData'] = $PlantData;
		$Data['LabData'] = $LabData;
		$Data['CenterData'] = $CenterData;
		$Data['VarietyData'] = $VarietyData;
		$Data['SupplierData'] = $SupplierData;
		$Data['GroupQtyData'] = $GroupQtyData;
		$Data['ProductionData'] = $ProductionData;
		//$Data['VAData'] = $VAData;


		return $Data;
	}


	public function getPlantData($RequestBody, $Qry,$OAAQry)
	{
		
		$OrderIDs=$this->OrderData['OrderIDs'];
		$TotalOrders=$this->OrderData['TotalOrders'];


		$sqlCQuery = "SELECT ProcessorID as PlantID,ExpDisplayName as PlantName,count(*) as PlantQty
			FROM ScrmPoData as POD LEFT join AdminExporterDetails as EXP on EXP.ExpID=POD.ProcessorID
        	WHERE POD.PoID  in ($OrderIDs) GROUP by ProcessorID order by PlantQty DESC";

		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

			$aryPendingOrdersData[] = $dataRow;

		}


		 $sqlCQuery = "SELECT COUNT(*) as PendingOrders,ProcessorID as PlantID from ScrmPoData 
		WHERE PoID NOT in (SELECT DISTINCT(InvID) as OrderID  FROM `QcCodeList`)  and PoID  in ($OrderIDs) GROUP by ProcessorID order by PlantID DESC";

		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
	while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {
		//var_dump($dataRow);
		$aryPendingCodeData[] = $dataRow;

	} 


	foreach($aryPendingOrdersData as $PendingOrderItem){

		$dataRow=array();
		$dataRow['PlantID']=$PendingOrderItem['PlantID'];
		$dataRow['PlantName']=$PendingOrderItem['PlantName'];
		$dataRow['PlantQty']=round($PendingOrderItem['PlantQty'],3);
		
		foreach($aryPendingCodeData as $PendingCodeItem ){
			if($PendingOrderItem['PlantID']==$PendingCodeItem['PlantID']){
			$dataRow['PendinCodeQty']=round($PendingCodeItem['PendingOrders'],3);
			}
		}
		$aryData[] = $dataRow;
	}


		return $aryData;
	}



	public function getExtLabData($RequestBody, $Qry,$OAAQry)
	{
		
		$OrderIDs=$this->OrderData['OrderIDs'];
		$TotalOrders=$this->OrderData['TotalOrders'];

		 $sqlCQuery = "SELECT COUNT(*) as TotalSamples, TestingLab FROM `QcSamplingLettersExt` 
					  where InvID in ($OrderIDs) GROUP by TestingLab"; //

		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {
			$aryPendingOrdersData[] = $dataRow;
		}

		 $sqlCQuery = "SELECT COUNT(*) as PendingReports, TestingLab FROM `QcSamplingLettersExt` where
					  InvID in ($OrderIDs) and ReportDate =''  GROUP by TestingLab";

		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
	while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {
	
		$aryPendingCodeData[] = $dataRow;

	} 


	foreach($aryPendingOrdersData as $PendingOrderItem){

		$dataRow=array();
		//$dataRow['PlantID']=$PendingOrderItem['PlantID'];
		$dataRow['TestingLab']=$PendingOrderItem['TestingLab'];
		$dataRow['TotalSamples']=round($PendingOrderItem['TotalSamples'],3);
		$dataRow['PendingReports']=0;
		
		foreach($aryPendingCodeData as $PendingCodeItem ){
			if($PendingOrderItem['TestingLab']==$PendingCodeItem['TestingLab']){
			$dataRow['PendingReports']=round($PendingCodeItem['PendingReports'],3);
			}
		}
		$aryData[] = $dataRow;
	}


		return $aryData;
	}


	public function getOrderIDs($RequestBody, $Qry ){

		$sqlCQuery = "SELECT GROUP_CONCAT(PoID) as OrderIDs, count(*) as TotalOrders  
		FROM ScrmPoData as POD  WHERE PoID  not in (SELECT PiID FROM `ScrmCustomInvoices` ) and  Status not in (2,3,4) order by ProcessorID DESC;";
	   $stmtC = $this->conn->prepare($sqlCQuery);
	   $stmtC->execute();
	   $dataRow = $stmtC->fetch(PDO::FETCH_ASSOC);
	   return $dataRow;

   }



}
