<?php
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
	if ($_POST['Group'] != 'BOTH') {
		$Group = $_POST['Group'];
		$query .= " and ProductionTypeID='$Group'";
	}
	if ($ColdstoreID != '') {
		//$Station=$_POST['Station'];
		$query .= " and CSD.PlantID ='$ColdstoreID'";
	}

	if ($TenantID != 'BOTH') {
		$query .= " and CSD.StockHolderID='$TenantID'";
	}

	$TotalKGs = '';
	$TotalMcs = '';
	$TotalLooseBags = '';
	$SqlStockIds = "SELECT COALESCE(GROUP_CONCAT(CSOD.StockID),0) as OutStockIds,
					(SELECT COALESCE(GROUP_CONCAT(CSD.StockID),0) from CsStockData AS CSD WHERE CSD.ProductionDate
					$DateFilter and (AvalMcs !=0  or LooseBags !=0) $query) as AvalStockId
					from CsStockData AS CSD 
					left join CsStockOutDetails as CSOD on CSD.StockID=CSOD.StockID
					WHERE CSD.ProductionDate $DateFilter $query ";
	$StmtStockIds = $iconn->query($SqlStockIds);
	$RowStockIds = $StmtStockIds->fetch_array();
	$AvalStockId = $RowStockIds['AvalStockId'];
	$OutStockIds = $RowStockIds['OutStockIds'];
	if ($OutStockIds != 0) {
		$TotalIds = $AvalStockId . ',' . $OutStockIds;
	}

	if ($AvalStockId != 0) {
		$SqlStock = "SELECT *,CSD.FreezingType,HSD.ShiftName,CSD.Grade,CSD.Remarks as Remarks,
		CSP.PackingStyle,AGOU.StationName as Unit, 
		(SELECT sum(AvalMcs*TotalSlabs*SlabWeight*MFactor) 
		from CsStockData as MCPID where MCPID.StockID=CSD.StockID) as KGS, 
		(SELECT sum(LooseBags*SlabWeight*MFactor) 
		from CsStockData as MCPID where MCPID.StockID=CSD.StockID) as LKGS 
		
		FROM
		`CsStockData` as CSD left join AdminPackingStyles as CSP on CSP.PCKID = CSD.PackingStyleID
		left join ScrmPoData as CSPOS on CSPOS.PoID = CSD.PoID 
		left join CsBarCodes as CBD on CSD.BarCodeID=CBD.BarCodeID
		left join ScrmPIVarietyList as AGO on CSD.VarietyID=AGO.VarietyID 
		left join RmProcessingStations as AGOU on CSD.UnitID=AGOU.ProcessingStationID 
		left join AdminShiftDetails as HSD on HSD.ShiftName=CSD.ShiftID 
		left join CsStoresRackData as CRD
		on CRD.BarCodeID=CBD.BarCodeID where CSD.StockID in ($AvalStockId) ";
		$StmtStock = $iconn->query($SqlStock);

		while ($RowStock = $StmtStock->fetch_array()) {
			$AryTotalStock[] = $RowStock;

			$Mcs = $RowStock['AvalMcs'];
			$LooseBags = $RowStock['LooseBags'];
			$KGs = $RowStock['KGS'] + $RowStock['LKGS'];
			$TotalKGs += $KGs;
			$TotalMcs += $Mcs;
			$TotalLooseBags += $LooseBags;

		}
	}

	if ($OutStockIds != 0) {

		$SqlStock = "SELECT *,AGOU.StationName as Unit, 
		(SELECT sum(OutMcs*TotalSlabs*SlabWeight*MFactor) from CsStockData as MCPID where MCPID.StockID=CSD.StockID) as KGS, 
		(SELECT sum(OutLooseBags*SlabWeight*MFactor) from CsStockData as MCPID where MCPID.StockID=CSD.StockID) as LKGS 
		FROM `CsStockData` as CSD 
		left join `CsStockOutDetails` as CSOD on CSOD.StockID = CSD.StockID 
		left join AdminPackingStyles as CSP on CSP.PCKID = CSD.PackingStyleID 
		left join ScrmPoData as CSPOS on CSPOS.PoID = CSD.PoID left join CsBarCodes as CBD on CSD.BarCodeID=CBD.BarCodeID 
		left join ScrmPIVarietyList as AGO on CSD.VarietyID=AGO.VarietyID 
		left join RmProcessingStations as AGOU on CSD.UnitID=AGOU.ProcessingStationID 
		left join CsStoresRackData as CRD on CRD.BarCodeID=CBD.BarCodeID 
		left join AdminShiftDetails as HSD on HSD.ShiftID=CSD.ShiftID 
		where  CSD.StockID in ($OutStockIds) ";

		$StmtStock = $iconn->query($SqlStock);

		while ($RowStock = $StmtStock->fetch_array()) {
			// echo json_encode($RowStock,JSON_PRETTY_PRINT);
			// exit;
			$RowStock['BarCodeStatus'] = 3;
			// $PackingStyle = $RowStock['PackingStyle'];
			$AryTotalStock[] = $RowStock;

			$Mcs = $RowStock['AvalMcs'];
			$LooseBags = $RowStock['LooseBags'];
			$KGs = $RowStock['KGS'] + $RowStock['LKGS'];
			$TotalKGs += $KGs;
			$TotalMcs += $Mcs;
			$TotalLooseBags += $LooseBags;

		}
	}

	// echo json_encode($PackingStyle, JSON_PRETTY_PRINT);
	// exit;

	// echo $TotalKGs;
	// exit;

	$data = '	<table  id="key-datatable" data-paging="false" 
	 class="table dt-responsive wrap w-100" >										
		
<thead class="thead-light">
		<tr>
		
		<th>StockID</th>
		<th>Date</th>
		<th>Station</th>
		<th>Po NO</th>
		<!--<th>Lot NO</th>-->
		<th>Bar Code</th>
		<th>Shift</th>
		<th>Variety</th>
		<th>Freezing</th>	
		<th>Group</th>
		<th>Grade</th>
		<th>Packing Style</th>
		<th>Category</th>
		<th>Location</th>
		<th>MCS</th>	
		<th>Loose</th>	
		<th>Kgs</th>	
		<th>Remarks</th>	
		</tr></thead><tbody>
		
		
		';
	foreach ($AryTotalStock as $RowStock) {
		$KGS = $RowStock['KGS'] + $RowStock['LKGS'];
		$Remarks = $RowStock['Remarks'];

		$StockID = $RowStock['StockID'];
		$RackName = $RowStock['RackName'];
		$BlockID = $RowStock['BlockID'];
		$BarCodeStatus = $RowStock['BarCodeStatus'];
		$OnFloor = $RowStock['OnFloor'];
		$LKGS = $RowStock['LKGS'];

		$LooseBags = $RowStock['LooseBags'];
		$AvalMcs = $RowStock['AvalMcs'];
		$LotNumber = $RowStock['PoNo'];

		$BlockName = '';


		if ($BarCodeStatus == 3) {
			$BlockName = 'Stock Out';
		} else if ($OnFloor == 0) {
			$BlockName = '--';
		} else {
			$BlockName .= ' Store -' . $RackName;
		}

		$data .= '
				<tr>
			

		
		<td >' . $StockID . '</td>
		<td >' . getFormatedDate($RowStock['ProductionDate']) . '</td>
		<td >' . $RowStock['Unit'] . '</td>
		<td >' . $RowStock['PoNo'] . '</td>
		<!--<td >' . $LotNumber . '</td> -->
		<td >' . $RowStock['BarCodeValue'] . '</td>
		<td >' . $RowStock['ShiftName'] . '</td>
		<td >' . $RowStock['ShortName'] . '</td>
		<td >' . $RowStock['FreezingType'] . '</td>
		<td >' . $RowStock['ProductionTypeID'] . '</td>
		<td >' . $RowStock['Grade'] . '</td>
		
		<td >' . $RowStock['PackingStyle'] . '</td>
		
		<td >' . $RowStock['ProductCategoryID'] . '</td>
		<td >' . $BlockName . '</td>
		<td >' . $AvalMcs . '</td>
		<td >' . $LooseBags . '</td>
		<td >' . round($KGS, 2) . '</td>
		<td >' . $Remarks . '</td>
		
	
			</tr>';
	}

	echo $data .= '</tbody>
	<tfoot>
	<tr>	
		<th></th>
		<th>Totals</th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>	
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>	
		<th>'.$TotalMcs.'</th>
		<th>'.$TotalLooseBags.'</th>	
		<th>'.$TotalKGs.'</th>	
		<th></th>	
	</tr>
	</tfoot>
	</table>';





	//	echo json_encode($OUT);
}

?>
<script>
	$('document').ready(function() {
		var table = $('#key-datatable').DataTable({
			scrollY: "600px",
			scrollX: true,
			sScrollX: "100%",
			sScrollXInner: "150%",
			// scrollCollapse:true,
			//paging:true,
			destroy: true,
			fixedColumns: true
			/*"columnDefs": [
			    { "width": "20%", "targets": 0 }
			  ]*/
		});
	});
</script>