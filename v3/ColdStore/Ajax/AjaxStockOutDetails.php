<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
$ColdstoreID = $_POST['ColdstoreID'];
$TenantID = $_POST['TenantID'];

if (isset($_POST['ToDate'])) {

	$FromDate = $_POST['FromDate'];
	$ToDate = $_POST['ToDate'] . ' 23:59:59';

	if ($_POST['OutType'] != "BOTH") {
		$OutType = $_POST['OutType'];
		$query = " and OutType='$OutType'";
	}

	if ($_POST['PackData'] != 'BOTH') {
		$PackData = $_POST['PackData'];
		$query .= " and PackingTypeID='$PackData'";
	}

	/*if($_POST['Station']!='BOTH'){
			 $Station=$_POST['Station'];
			 $query.=" and UnitID='$Station'";	
			 }*/
	if ($TenantID != 'BOTH') {
		$query .= " and CS.StockHolderID='$TenantID'";
	}
	if ($ColdstoreID != '') {
		$query .= " and CS.PlantID ='$ColdstoreID'";
	}


	$SqlOutStock = "SELECT *,AGOU.TenantName as Unit,COALESCE(PoNo,'NA')AS PoNo,CS.Remarks,CSD.PostedDate,
	
	CS.FreezingType as FreezingType,CS.Grade,CSP.PackingStyle,
	(SELECT sum(OutMcs*TotalSlabs*SlabWeight*MFactor) from CsStockData as MCPID where MCPID.StockID=CSD.StockID) as KGS, 
	(SELECT sum(OutLooseBags*SlabWeight*MFactor) 
	from CsStockData as MCPID where MCPID.StockID=CSD.StockID) as LKGS 
	 FROM `CsStockOutDetails` as CSD 
	 left join CsStockData as CS  on CSD.StockID=CS.StockID
	left join CsBarCodes as CBC on CS.BarCodeID=CBC.BarCodeID 
left join AdminPackingStyles as CSP on CSP.PCKID = CS.PackingStyleID 
	left join ScrmPIVarietyList as AGO on CS.VarietyID=AGO.VarietyID 

	left join CsTenant as AGOU on CS.UnitID=AGOU.TenantID 

	left join ScrmPoData as CSPOS on CSPOS.PoID = CS.PoID 

	where CSD.PostedDate between '$FromDate' and '$ToDate' $query group by StockOutID; ";
	$StmtOutStock = $iconn->query($SqlOutStock);






	$data .= '	<table  id="key-datatable" data-paging="false" class="table dt-responsive wrap w-100" >										
		
<thead class="thead-light">
		
		
		<tr>
		<th>StockID</th>
		<th>In Date</th>
		<th>Out Date</th>
		<th>Station</th>
		<th>Po NO</th>
		<th>Bar Code</th>
		<th>Variety</th>
		<th>Freezing</th>	
		<th>Group</th>
		<th>Grade</th>
		<th>Packing Style</th>
		<th>Category</th>
		<th>MCS</th>	
		<th>Loose</th>	
		<th>Kgs</th>	
		<th>Remarks</th>	

		<!--<th>Date</th>
		<th>PO No</th>
		<th>Bar Code</th>
		<th>Unit</th>
		<th>Variety</th>
		<th>Grade</th>
		
		<th>Packing Style</th>
		<th>Out Type</th>
		<th>Remarks</th>
		<th>MCS</th>-->
		
		
		</tr>
		</thead>
		
		<tbody>';



	$TotalKGs = '';
	$TotalAvalMcs = '';
	$TotalLooseBags = '';
	while ($RowStock = $StmtOutStock->fetch_array()) {


		$KGS = $RowStock['KGS'] + $RowStock['LKGS'];
		$Remarks = $RowStock['Remarks'];

		$StockID = $RowStock['StockID'];
		$RackName = $RowStock['RackName'];
		$BlockID = $RowStock['BlockID'];
		$BarCodeStatus = $RowStock['BarCodeStatus'];
		$OnFloor = $RowStock['OnFloor'];
		$LKGS = $RowStock['LKGS'];

		$LooseBags = $RowStock['OutLooseBags'];
		$AvalMcs = $RowStock['OutMcs'];
		$LotNumber = $RowStock['PoNo'];

		$TotalAvalMcs += $AvalMcs;
		$TotalLooseBags += $LooseBags;
		$TotalKGs += $KGS;
		$BlockName = '';



		$data .= '
				<tr>
		
				<td >' . $StockID . '</td>
		<td >' . getFormatedDate($RowStock['ProductionDate']) . '</td>
		<td >' . getFormatedDate($RowStock['PostedDate']) . '</td>
		<td >' . $RowStock['Unit'] . '</td>
		<td >' . $RowStock['PoNo'] . '</td>
		<!--<td >' . $LotNumber . '</td> -->
		<td >' . $RowStock['BarCodeValue'] . '</td>
		<td >' . $RowStock['ShortName'] . '</td>
		<td >' . $RowStock['FreezingType'] . '</td>
		<td >' . $RowStock['ProductionTypeID'] . '</td>
		<td >' . $RowStock['Grade'] . '</td>
		
		<td >' . $RowStock['PackingStyle'] . '</td>
		
		<td >' . $RowStock['ProductCategoryID'] . '</td>
		
		<td >' . $AvalMcs . '</td>
		
		<td >' . $LooseBags . '</td>
		<td >' . round($KGS, 2) . '</td>
		<td >' . $Remarks . '</td>
		
		
		<!--<td >' . getFormatedDate($RowOutStock['PostedDate']) . '</td>
		<td >' . $RowOutStock['PoNo'] . '</td>
		<td >' . $RowOutStock['BarCodeValue'] . '</td>
		<td >' . $RowOutStock['Unit'] . '</td>
		<td >' . $RowOutStock['ShortName'] . '</td>
		<td >' . $RowOutStock['Grade'] . '</td>
		
		<td >' . $RowOutStock['PackingStyle'] . '</td>
		
		<td >' . $RowOutStock['OutType'] . '</td>
		<td >' . $RowOutStock['Remarks'] . '</td>
		<td >' . $RowOutStock['OutMcs'] . '</td>-->
		
		
		
			</tr>';
	}

	echo $data .= '</tbody>
	<tfoot>
	<tr>
		<th></th>
		<th>Total</th>
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
		<th>' . $TotalAvalMcs . '</th>	
		<th>' . $TotalLooseBags . '</th>	
		<th>' . $TotalKGs . '</th>	
		<th></th>	
	</tr>
	</tfoot>
	</table>';





	//	echo json_encode($OUT);
}
