<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once '../App/Controller/AppUtilController.php';

class CsDashBoardController
{
	private $conn;
	private $db_table = "ScrmApplicants";
	private $utilController;
	private $appController;
	private $Error = null;
	private $Msg = '';


	// Db connection
	public function __construct($db)
	{
		$this->conn = $db;
		$this->utilController = new UtilController($this->conn);
		$this->appController = new AppUtilController($this->conn);
	}

	public function getDashBoardData($RequestBody)
	{

		$RequestBody['DateFilter'] = '';
		if ($RequestBody['Year'] != '') {
			$FinClsYear = $RequestBody['Year'];
			$Month = trim($RequestBody['Month']);
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

			$RequestBody['DateFilter'] = $DateFilter;
		}

		$Qry = $this->getFilters($RequestBody);

		$SpeciesData = $this->getSpeciesData($RequestBody, $Qry);
		$ProductCategoryData = $this->getProductCategoryData($RequestBody, $Qry);
		$PackingTypeData = $this->getPackingTypeData($RequestBody, $Qry);
		$PlantData = $this->getPlantData($RequestBody, $Qry);
		$StoreData = $this->getStoreData($RequestBody, $Qry);
		$VarietyData = $this->getVarietyData($RequestBody, $Qry);
		//$SupplierData = $this->getSupplierData($RequestBody, $Qry);
		$GroupQtyData = $this->getGroupQtyData($RequestBody, $Qry);
		$OrderData = $this->getOrderData($RequestBody, $Qry);


		$Data['Error'] = null;
		$Data['Msg'] = 'Successful';
		$Data['SpeciesData'] = $SpeciesData;
		$Data['ProductCategoryData'] = $ProductCategoryData;
		$Data['PackingTypeData'] = $PackingTypeData;
		$Data['PlantData'] = $PlantData;
		$Data['StoreData'] = $StoreData;
		$Data['VarietyData'] = $VarietyData;
		//$Data['SupplierData'] = $SupplierData;
		$Data['GroupQtyData'] = $GroupQtyData;
		$Data['OrderData'] = $OrderData;


		return $Data;
	}



	public function getSpeciesData($RequestBody, $Qry)
	{
		$sqlCQuery = "SELECT SPIVL.Species as SpeciesName,round(sum(AvalKgs),3) as SpeciesQty,
		round(sum(AvalMcs),3) as AvalMcs FROM `CsStockData` as CSD 
		LEFT join ScrmPIVarietyList as SPIVL on SPIVL.VarietyID=CSD.VarietyID 
		 $Qry GROUP by SPIVL.Species ORDER by Species DESC";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

			$aryData[] = $dataRow;

		}
		return $aryData;
	}

	public function getProductCategoryData($RequestBody, $Qry)
	{
		$sqlCQuery = "SELECT SPIVL.TypeProduct as ProductCategoryName,SPIVL.FreezingType,round(sum(AvalKgs),3) as ProductCategoryQty,
		round(sum(AvalMcs),3) as AvalMcs FROM `CsStockData` as CSD 
		LEFT join ScrmPIVarietyList as SPIVL on SPIVL.VarietyID=CSD.VarietyID 
		 $Qry GROUP by SPIVL.TypeProduct,SPIVL.FreezingType ORDER by ProductCategoryQty DESC";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

			$dataRow['ProductTypeName'] = $dataRow['ProductCategoryName'] . '-' . $dataRow['FreezingType'];
			//$dataRow['ProductCategoryName'] = $dataRow['ProductCategoryName'];
			//$dataRow['FreezingType'] = $dataRow['FreezingType'];
			$aryData[] = $dataRow;

		}
		return $aryData;
	}


	public function getPackingTypeData($RequestBody, $Qry)
	{
		$sqlCQuery = "SELECT CSD.PackingTypeID as PackingTypeName,round(sum(AvalKgs),3) as PackingTypeQty,round(sum(AvalMcs),3) as AvalMcs FROM `CsStockData` as CSD 
		LEFT join ScrmPIVarietyList as SPIVL on SPIVL.VarietyID=CSD.VarietyID 
		 $Qry GROUP by CSD.PackingTypeID ORDER by PackingTypeQty DESC";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

			$aryData[] = $dataRow;

		}
		return $aryData;
	}
	public function getOrderData($RequestBody, $Qry)
	{
		if ($RequestBody['PoID'] != '') {

			$PoID = $RequestBody['PoID'];
			$sqlCQuery = "SELECT PlantID,RPS.StationName,
						(SELECT PoNo FROM ScrmPoData WHERE PoID=CSD.PoID ) as PoNo,
						(SELECT sum(Mcs) FROM ScrmPoGradeData WHERE InvId=CSD.PoID ) as OrderMcs,
						sum(AvalMcs) as AvalMcs from `CsStockData` as CSD 
						LEFT JOIN RmProcessingStations as RPS ON RPS.ProcessingStationID=CSD.PlantID
						WHERE  PackingTypeID='MASTER'  and PoID='$PoID' AND (CSD.AvalMcs>0 OR CSD.LooseBags >0) GROUP by PoID,CSD.PlantID  ORDER by AvalMcs DESC";

			$stmtC = $this->conn->prepare($sqlCQuery);
			$stmtC->execute();
			while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

				$aryData[] = $dataRow;

			}
		}
		return $aryData;
	}

	public function getPlantData($RequestBody, $Qry)
	{
		$sqlCQuery = "SELECT CSD.PlantID as PlantID,RPS.StationName as PlantName,round(sum(AvalKgs),3) as PlantQty,SUM(LooseBags) AS LooseBags,APS.TotalSlabs,
						ROUND(SUM(IFNULL(AvalMcs,0)),3) AS AvalMcs FROM `CsStockData` as CSD 
						left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID
						left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
						LEFT join ScrmPIVarietyList as SPIVL on SPIVL.VarietyID=CSD.VarietyID $Qry AND (CSD.AvalMcs>0 OR CSD.LooseBags >0) GROUP by CSD.PlantID,CSD.PackingStyleID ORDER by PlantQty DESC";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		$PlantId = 0;
		$PlantName = '';
		$PlantQty = 0;
		$PlantMcs = 0;
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

			$PlantId = $dataRow['PlantID'];
			$PlantName = $dataRow['PlantName'];
			$PlantQty += $dataRow['PlantQty'];
			$LooseMcs = floor($dataRow['LooseBags'] / $dataRow['TotalSlabs']);
			$PlantMcs += ($dataRow['AvalMcs'] + $LooseMcs);

		}
		$Data['PlantId'] = $PlantId;
		$Data['PlantName'] = $PlantName;
		$Data['PlantQty'] = $PlantQty;
		$Data['AvalMcs'] = $PlantMcs;
		$aryData[] = $Data;
		return $aryData;
	}

	public function getStoreData($RequestBody, $Qry)
	{
		$sqlCQuery = "SELECT CSRD.BlockID ,CSBD.BlockName,COUNT(*) as TotalRacks,
(SELECT COUNT(*) FROM `CsStoresRackData` WHERE Path=1 and Status=1 and BlockID =CSBD.BlockID) as Filled
FROM `CsStoresRackData` as CSRD LEFT join CsStoresBlockData as CSBD on CSRD.BlockID=CSBD.BlockID
WHERE Path=1 GROUP by CSRD.BlockID Order by CSRD.StoreID,CSRD.BlockID asc";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

			$dataRow['AvalMcs']=0;
			$aryData[] = $dataRow;

		}
		return $aryData;
	}
	public function getVarietyData($RequestBody, $Qry)
	{
		$sqlCQuery = "SELECT SPIVL.ShortName as VarietyName,round(sum(AvalKgs),3) as VarietyQty ,round(sum(AvalMcs),3) as AvalMcs FROM `CsStockData` as CSD 
		LEFT join ScrmPIVarietyList as SPIVL on SPIVL.VarietyID=CSD.VarietyID $Qry
		  GROUP by SPIVL.ShortName HAVING sum(AvalKgs)> 0  ORDER by VarietyQty DESC";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {

			$aryData[] = $dataRow;

		}
		return $aryData;
	}

	public function getGroupQtyData($RequestBody, $Qry)
	{


		$sqlCQuery = "SELECT CSD.Grade as Grade,round(sum(AvalKgs),3) as GradeQty,round(sum(AvalMcs),3) as AvalMcs FROM `CsStockData` as CSD 
		LEFT join ScrmPIVarietyList as SPIVL on SPIVL.VarietyID=CSD.VarietyID $Qry
		  GROUP by CSD.Grade HAVING sum(AvalKgs)> 0 ORDER by abs(CSD.Grade) asc";
		$stmtC = $this->conn->prepare($sqlCQuery);
		$stmtC->execute();
		while ($dataRow = $stmtC->fetch(PDO::FETCH_ASSOC)) {



			$aryData[] = $dataRow;

		}
		return $aryData;
	}

	public function getFilters($RequestBody)
	{
		$DateFilter = $RequestBody['DateFilter'];

		$Qry = "";
		$Qry = "Where 1=1 ";

		// if ($RequestBody['DateFilter'] != '') {
		// 	$Qry .= " and  CSD.ProductionDate $DateFilter";
		// }


		if ($RequestBody['Species'] != '') {
			$Species = explode(",", $RequestBody['Species']);
			$Species = implode("','", $Species);
			$Qry .= " and  SPIVL.Species in  ('$Species')";
		}
		if ($RequestBody['ProductCategory'] != '') {
			$ProductCategory = explode(",", $RequestBody['ProductCategory']);
			$ProductCategory = implode("','", $ProductCategory);
			$Qry .= " and  SPIVL.TypeProduct in  ('$ProductCategory')";
		}
		if ($RequestBody['FreezingType'] != '') {
			$FreezingType = explode(",", $RequestBody['FreezingType']);
			$FreezingType = implode("','", $FreezingType);
			$Qry .= " and  SPIVL.FreezingType in  ('$FreezingType')";
		}
		if ($RequestBody['PackingTypeData'] != '') {
			$PackingTypeData = explode(",", $RequestBody['PackingTypeData']);
			$PackingTypeData = implode("','", $PackingTypeData);
			$Qry .= " and  CSD.PackingTypeID in  ('$PackingTypeData')";
		}

		if ($RequestBody['PlantID'] != '') {
			$PlantID = $RequestBody['PlantID'];
			$Qry .= " and  CSD.PlantID in  ($PlantID)";
		}

		if ($RequestBody['StoreID'] != '') {
			$StoreID = $RequestBody['StoreID'];
			$Qry .= " and  CSD.UnitID in  ($StoreID)";
		}

		if ($RequestBody['Variety'] != '') {
			$Variety = explode(",", $RequestBody['Variety']);
			$Variety = implode("','", $Variety);
			$Qry .= " and  SPIVL.ShortName in  ('$Variety')";
		}
		if ($RequestBody['Grade'] != '') {
			$Grade = explode(",", $RequestBody['Grade']);
			$Grade = implode("','", $Grade);
			$Qry .= " and  CSD.Grade in  ('$Grade')";
		}



		// if( $RequestBody['SupplierID'] !=''){
		// 	$SupplierID=$RequestBody['SupplierID'];
		// 	$Qry .= " and  RAD.RmSupplierID in  ($SupplierID)";	
		// }




		return $Qry;
	}


}
