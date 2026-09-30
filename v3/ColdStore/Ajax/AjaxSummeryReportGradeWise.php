<?php
//ini_set( 'display_errors', 1 );
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$ColdstoreID = $_POST['ColdstoreID'];
$TenantID = $_POST['TenantID'];


if (isset($_POST['ToDate'])) {

	$FromDate = $_POST['FromDate'];
	$ToDate = $_POST['ToDate'] . ' 23:59:59';
	$DateFilter = "between '$FromDate' and '$ToDate'";
	$query = '';

	/*if($_POST['Station']!='BOTH'){
			$Station=$_POST['Station'];
			$query.=" and UnitID='$Station'";	
			}*/
	if ($_POST['PackData'] != 'BOTH') {
		$PackData = $_POST['PackData'];
		$query .= " and PackingTypeID='$PackData'";
	}
	if ($_POST['FreezingType'] != 'BOTH') {
		$FreezingType = $_POST['FreezingType'];
		$query .= " and CSD.FreezingType='$FreezingType'";
	}
	if ($TenantID != 'BOTH') {
		$query .= " and CSD.StockHolderID='$TenantID'";
	}
	if ($ColdstoreID != '') {
		$query .= " and CSD.PlantID='$ColdstoreID'";
	}


	/*$SqlStockIds="SELECT COALESCE(GROUP_CONCAT(CSOD.StockID),0) as ProdStockIds,
					
                    (SELECT COALESCE(GROUP_CONCAT(CSD.StockID),0) from CsStockData AS CSD 
					WHERE CSD.ProductionDate $DateFilter  and (AvalMcs !=0  or LooseBags !=0)  $query ) as AvalStockId,
                   
                   (SELECT COALESCE(GROUP_CONCAT(CSD.StockID),0) from CsStockOutDetails AS CSD 
				   WHERE CSD.PostedDate $DateFilter  $query  ) as OutStockIds
					from CsStockData AS CSD 
					left join CsStockOutDetails as CSOD on CSD.StockID=CSOD.StockID
					WHERE CSD.ProductionDate $DateFilter and CSD.UnitID ='$ColdstoreID' $query ";  */
	$SqlStockIds = "SELECT COALESCE(GROUP_CONCAT(CSOD.StockID),0) as ProdStockIds,
					
                    (SELECT COALESCE(GROUP_CONCAT(CSD.StockID),0) from CsStockData AS CSD 
					WHERE CSD.ProductionDate $DateFilter  and (AvalMcs !=0  or LooseBags !=0)  $query ) as AvalStockId,
                   
                   (SELECT COALESCE(GROUP_CONCAT(CSD.StockID),0) from CsStockOutDetails AS CSD 
				   WHERE CSD.PostedDate $DateFilter   ) as OutStockIds
					from CsStockData AS CSD 
					left join CsStockOutDetails as CSOD on CSD.StockID=CSOD.StockID
					WHERE CSD.ProductionDate $DateFilter $query ";

	$StmtStockIds = $iconn->query($SqlStockIds);
	$RowStockIds = $StmtStockIds->fetch_array();
	$ProdStockIds = $RowStockIds['ProdStockIds'];
	$AvalStockId = $RowStockIds['AvalStockId'];
	$OutStockIds = $RowStockIds['OutStockIds'];

	$TotalIds = $ProdStockIds . ',' . $AvalStockId . ',' . $OutStockIds;

	$SqlVariety = "SELECT CSD.Grade from CsStockData as CSD WHERE CSD.StockID in ($TotalIds)
group by CSD.Grade ORDER BY Abs(CSD.Grade) ASC
";
	$StmtVariety = $iconn->query($SqlVariety);


	//'$FromDate' and '$ToDate' $query 
	//echo $SqlSummary;	 exit;



	$data = '<table id="key-datatable"  data-paging="false"
										class="table dt-responsive nowrap w-100">
		
		
		<thead class="thead-light">
		<tr>
		
		<th>Grade</th>
		<th>InWard(Kgs)</th>
		<th>OutWard(Kgs)</th>
		<th>Present Stock(Kgs)</th>
		
		</tr></thead><tbody>
		
		
		';
	while ($RowVariety = $StmtVariety->fetch_array()) {


		$Grade = $RowVariety['Grade'];


		$SqlOsData = "SELECT CSD.VarietyID,APS.PackingStyle,ShortName,
		  COALESCE(sum(AvalMcs*TotalSlabs*SlabWeight*MFactor) ,0) as OsKgs,
		  COALESCE(sum(LooseBags*SlabWeight*MFactor) ,0) as OsLkgs
		  from CsStockData as CSD 
		  left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
          left join ScrmPIVarietyList as VL on VL.VarietyID=CSD.VarietyID
          WHERE CSD.Grade='$Grade'  and 
		  StockID in ($AvalStockId) 
          ORDER BY VarietyID,Abs(APS.PackingStyle) ASC";
		$StmtOsData = $iconn->query($SqlOsData);
		$RowOsData = $StmtOsData->fetch_array();

		$SqlProdOutData = "SELECT CSD.VarietyID,APS.PackingStyle,ShortName,
							COALESCE(sum(OutMcs*TotalSlabs*SlabWeight*MFactor) ,0) as ProdOutKgs,
							COALESCE(sum(OutLooseBags*SlabWeight*MFactor) ,0) as ProdOutLkgs
							from CsStockData as CSD 
							left join CsStockOutDetails as CSOD on CSOD.StockID=CSD.StockID
							left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
							left join ScrmPIVarietyList as VL on VL.VarietyID=CSD.VarietyID
							WHERE CSD.Grade='$Grade' and   CSD.StockID in ($ProdStockIds) 
							
							ORDER BY VarietyID,Abs(APS.PackingStyle) ASC";


		$StmtProdOutData = $iconn->query($SqlProdOutData);
		$RowProdOutData = $StmtProdOutData->fetch_array();


		$SqlOutData = "SELECT CSD.VarietyID,APS.PackingStyle,ShortName,
							COALESCE(sum(OutMcs*TotalSlabs*SlabWeight*MFactor) ,0) as OutKgs,
							COALESCE(sum(OutLooseBags*SlabWeight*MFactor) ,0) as OutLkgs
							from CsStockData as CSD 
							left join CsStockOutDetails as CSOD on CSOD.StockID=CSD.StockID
							left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
							left join ScrmPIVarietyList as VL on VL.VarietyID=CSD.VarietyID
							WHERE CSD.Grade='$Grade' and  CSD.StockID in ($OutStockIds) 
							
							ORDER BY VarietyID,Abs(APS.PackingStyle) ASC";


		$StmtOutData = $iconn->query($SqlOutData);
		$RowOutData = $StmtOutData->fetch_array();

		$OutStanding = $RowOsData['OsKgs'] + $RowOsData['OsLkgs'];
		$Production = $RowProdOutData['ProdOutKgs'] + $RowProdOutData['ProdOutLkgs'];
		$Issues = $RowOutData['OutKgs'] + $RowOutData['OutLkgs'];









		$data .= '
				<tr>
		<td >' . $Grade . '</td>
		<td >' . round(($OutStanding + $Production), 2) . '</td>
		<td >' . $Issues . '</td>
		<td >' . $OutStanding . '</td>
		
	
			</tr>';
	}

	echo $data .= '</tbody></table>';





	//	echo json_encode($OUT);
}
