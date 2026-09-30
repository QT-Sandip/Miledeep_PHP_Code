<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");


$block = $_POST['block'];
/*$floor=$_POST['floor'];
$rackid=$_POST['rackid'];*/
$TYPE = $_POST['type'];
$MONTH = $_POST['MONTH'];
$YEAR = $_POST['YEAR'];

if ($TYPE == 'repck') {
	$query = '';

	if (isset($block) && $block != '') {
		$query .= " and cr.BlockID='" . $block . "'";
	}

	/*  if(isset($floor)){
	  if($floor!='' && $rackid ==''){
		$query.=" and cr.FloorID='".$floor."'";
	  }else if($floor=''){
		$query='';
	  }
	 }
	  
	  if(isset($rackid) && $rackid!=''){
		$query.=" and cr.RackID='".$rackid."'";
	  }*/

	$sql = "SELECT cb.BarCodeID,BlockName,coalesce(PoNo,'NA') AS PoNo,RowID,cr.BlockID,RackName,ShortName,CPID.FreezingType,CPID.Grade,
	PackingTypeID,ProductionTypeID,StockID,AvalMcs,LooseBags,PackingTypeID,CSP.PackingStyle,CPID.PostedDate, CPID.PoID,cb.BarCodeValue,
	ProductCategoryID,CPID.UnitID FROM `CsBarCodes` AS cb 
	LEFT JOIN `CsStoresRackData` AS cr ON cb.BarCodeID = cr.BarCodeID
 	LEFT JOIN `CsStockData` AS CPID ON cb.BarCodeID = CPID.BarCodeID			
	LEFT JOIN `AdminGeneralOptions` AS CGOPG ON CGOPG.OptID = CPID.ProductionTypeID 
	LEFT JOIN `CsStoresBlockData` AS CBD ON CBD.BlockID = cr.BlockID 		
	LEFT JOIN `ScrmPoData` AS CPOS ON CPOS.PoID  = CPID.PoID 
 	LEFT JOIN `ScrmPIVarietyList` AS VL ON VL.VarietyID=CPID.VarietyID
	LEFT JOIN `AdminPackingStyles` AS CSP ON CSP.PckID = CPID.PackingStyleID
 	WHERE cb.BarCodeStatus=1 AND Year(ProductionDate)='$YEAR' AND Month(ProductionDate)='$MONTH'
	$query";

	$stmt = $iconn->query($sql);

	$data = '
		<div class="card">
			<div class="card-body" >
				<div id="table-scroll"> 
					<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
			<thead class="thead-light">
                 <tr>	 
					<th>ID</th>
					<th>Entry Date</th>
					<th>Po No</th>
					<th>Variety</th>
					<th>Grade</th>	
					<th>MCS</th>
					<th>Loose</th>
					<th>Packing Style</th> 
					<th>Freezing</th>
					<th>Group</th>		
					<th>Packing Type</th>
					<th>Batch Code</th>	
					<th>Actions</th>
				</tr>               
        	</thead>
        <tbody id >';

	$i = '0';

	while ($row1 = $stmt->fetch_array()) {
		$i++;

		if ($row1['RackName'] == '') {
			$RACK_NAME = 'NA';
		} else {
			$RACK_NAME = $row1['RackName'];
		}


		$BarCodeValue = $row1['BarCodeValue'];
		$BLOCK_NAME = $row1['BlockName'];
		$PO_NUM     = $row1['PoNo'];
		$FREEZING   = $row1['FreezingType'];
		$GRADE = $row1['Grade'];
		$VARITY = $row1['ShortName'];
		$PACKING = $row1['PackingStyle'];
		$CARTONS_NUMBER = $row1['AvalMcs'];
		$StockID = $row1['StockID'];
		$DUMMY_MASTER = $row1['PackingTypeID'];
		$ENTRY_DATE = $row1['PostedDate'];
		$GROUP = $row1['ProductionTypeID'];
		$LooseBags = $row1['LooseBags'];

		$ENTRYDate = date("d-m-Y", strtotime($ENTRY_DATE));


		$data .= '
		
		<tr id="tr' . $row1['BarCodeID'] . '">
		
		<td >' . $StockID . '</td>
		<td >' . $ENTRYDate . '</td> 
		<td >' . $PO_NUM . '</td>
		<td >' . $VARITY . '</td>	
		<td >' . $GRADE . '</td>
		<td >' . $CARTONS_NUMBER . '</td>
		<td >' . $LooseBags . '</td>
		<td >' . $PACKING . '</td>
		<td >' . $FREEZING . '</td>
		<td >' . $GROUP . '</td>
		<td >' . $DUMMY_MASTER . '</td>
		<!--<td >' . $RACK_NAME . '</td>-->
		<td >' . $BarCodeValue . '</td>
		
	
		<td> 
		<!--<a target="_blank" href="CreateStock?TYPE=0&BID=' . $row1['BarCodeID'] . '" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Add Stock" data-original-title="Edit"
						 ><i class="mdi mdi-plus font-size-16"></i></a>-->
		
		<a target="_blank" href="CsStockEdit.php?id=' . $row1['StockID'] . '
					&TYPE=&BID=' . $row1['BarCodeID'] . '" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Edit Stock" data-original-title="Edit"
						 ><i class="mdi mdi-pencil font-size-16"></i></a>
		
		<a target="_blank" href="CsBarCodeLabelPrint.php?BID=' . $row1['BarCodeID'] . '"	
		class="mr-3 text-primary"  data-placement="top"
						 title="Print Label" data-original-title="Edit"
						 ><i class="mdi mdi-printer font-size-16"></i></a>
						
						
<!--<Button type="text" class="btn btn-danger repck_bt" name="repck_bt" id="' . $row1['BarCodeID'] . '">Out</Button>
-->
<button  class="btn btn-danger"
					 data-toggle="popover" data-trigger="focus" 
					 
					 data-id="' . $row1['StockID'] . '" id="repck_bt" 
							 value="' . $row1['StockID'] . '">Out
							 </button>							
		
		
		<!-- <div class="btn-group">
                  <button type="button" class="btn btn-info">Options</button>
                  <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
				  <li>
					<a target="_blank" 
					 href="CsPalletInputData.php?TYPE=&BID=' . $row1['BarCodeID'] . '" >Add Stock</a>
				  </li>
					
                    <li>
					<a target="_blank" href="CsStockEdit.php?id=' . $row1['StockID'] . '
					&TYPE=&BID=' . $row1['BarCodeID'] . '" >Edit Stock </a>
					</li>
					
                    
                  
				  
				 
				<li>
					<a target="_blank" href="CsBarCodePrint.php?BID=' . $row1['BarCodeID'] . '" >Print Label</a>
				</li>
					
					<li>
				  <button 
					value="' . $row1['BarCodeID'] . '"id="unplace">UnPlace</button>
				  </li> 
				 
					
               
			
                  </ul>
                </div>-->


				
				
				</td>
			</tr>';

		$PO_NUM = '';
		$VARITY = '';
		$GRADE = '';
		$CARTONS_NUMBER = '';
		$PACKING = '';
		$FREEZING = '';
		$BLOCK_NAME = '';
		$RACK_NAME = '';
	}

	echo $data .= '</tbody></table></div></div></div>';
} else if ($TYPE == 'repck_updt') {
	$BID = $_POST['bid'];
	$rckid = $_POST['rckid'];
	$OUT_TYPE = $_POST['OUT_TYPE'];



	$sql = "SELECT * FROM `CsBarCodes` WHERE `BarCodeID`='$BID'";
	$stmt = $iconn->query($sql);
	$row1 = $stmt->fetch_array();
	$row_id = $row1['BarCodeID'];

	$oldBarCodeData = legacyAuditFetchRowById('CsBarCodes', 'BarCodeID', $BID, 'ColdStore');
	$sql_BCD_update = "UPDATE  `CsBarCodes` SET BarCodeStatus ='3' WHERE BarCodeID ='$BID'";
	$stmt_BCD_update = $iconn->query($sql_BCD_update);
	if($stmt_BCD_update){
		legacyAuditLogUpdateById('CsBarCodes', 'BarCodeID', $BID, $oldBarCodeData, $_POST, ['Remarks' => 'Barcode status set to 3 (stock out) - repck_updt'], 'ColdStore');
	}

	$oldRackData = legacyAuditFetchRowById('CsStoresRackData', 'BarCodeID', $BID, 'ColdStore');
	$sql_RD_update = "UPDATE `CsStoresRackData` SET STATUS ='0',BarCodeID='0' WHERE BarCodeID ='$BID'";
	$stmt_RD_update = $iconn->query($sql_RD_update);
	if($stmt_RD_update){
		legacyAuditLogUpdateById('CsStoresRackData', 'BarCodeID', $BID, $oldRackData, $_POST, ['Remarks' => 'Rack data cleared for stock out - repck_updt'], 'ColdStore');
	}

	if ($sql_RD_update) {
		$row['sts'] = 'success';
		$row['msg'] = 'Data Updated Sucessfully';
		echo json_encode($row);
	} else {
		$row['sts'] = 'Failed';
		$row['msg'] = 'Faild To Update Data';
		echo json_encode($row);
	}
} else if ($TYPE == 'PALLET_UNPLACE') {
	$BarCodeID = $_POST['BID'];
	$StockID = $_POST['StockID'];
	$StoreRackId = $_POST['storeRackId'];

	$oldStockData = legacyAuditFetchRowById('CsStockData', 'BarCodeID', $BarCodeID, 'ColdStore');
	$sql_BCD_update = "UPDATE `CsStockData` SET `StockFloorStatus`='0', `StoreRackID` = '0' WHERE `BarCodeID`='$BarCodeID' AND StockID='$StockID'";
	$stmt_BCD_update = $iconn->query($sql_BCD_update);

	if ($stmt_BCD_update) {
		legacyAuditLogUpdateById('CsStockData', 'BarCodeID', $BarCodeID, $oldStockData, $_POST, ['Remarks' => 'Stock floor status cleared - PALLET_UNPLACE'], 'ColdStore');
		$sqlStockBarcodeUpdate = "UPDATE  `CsStockData` SET BarCodeID ='$BarCodeID' WHERE BarCodeID='$BarCodeID' and StockID='$StockID'";
		$stmtStockBarcodeUpdate = $iconn->query($sqlStockBarcodeUpdate);

		$SqlFloorstockCount = "SELECT * FROM CsStockData WHERE StoreRackID='$StoreRackId'";
		$stmtFloorstockCount = $iconn->query($SqlFloorstockCount);
		$FloorstockCount = mysqli_num_rows($stmtFloorstockCount);

		if ($FloorstockCount == 0) {
			$oldRackData2 = legacyAuditFetchRowById('CsStoresRackData', 'RowID', $StoreRackId, 'ColdStore');
			$sqlUnplacePallet = "UPDATE `CsStoresRackData` SET `PlacedStockID` = '',Status = 0 ,BarCodeID= 0 WHERE RowID ='$StoreRackId'";
		} else {
			$sqlPlacedStockId = "SELECT PlacedStockID FROM CsStoresRackData WHERE `RowID` = '$StoreRackId'";
			$stmtPlacedStockId = $iconn->query($sqlPlacedStockId);
			$rowPlacedStockId = $stmtPlacedStockId->fetch_array();
			$PlacedStockIds = $rowPlacedStockId['PlacedStockID'];
			$stockIdsArray = explode(",", $PlacedStockIds);
			// Filter out any empty elements
			$stockIdsArray = array_filter($stockIdsArray);
			$stockIdsArray = array_diff($stockIdsArray, [$StockID]);
			$newPlacedStockIds = implode(",", $stockIdsArray);
			$oldRackData2 = legacyAuditFetchRowById('CsStoresRackData', 'RowID', $StoreRackId, 'ColdStore');
			if(sizeof($stockIdsArray) > 0){
				$sqlUnplacePallet = "UPDATE `CsStoresRackData` SET `PlacedStockID` = '$newPlacedStockIds' WHERE `RowID` = '$StoreRackId'";
			}else{
				$sqlUnplacePallet = "UPDATE `CsStoresRackData` SET `PlacedStockID` = '',Status = 0 ,BarCodeID= 0 WHERE RowID ='$StoreRackId'";
			}
		}
		$stmtUnplacePallet = $iconn->query($sqlUnplacePallet);
		if($stmtUnplacePallet){
			legacyAuditLogUpdateById('CsStoresRackData', 'RowID', $StoreRackId, $oldRackData2, $_POST, ['Remarks' => 'Rack pallet unplaced - PALLET_UNPLACE'], 'ColdStore');
		}
	}

	$row['sts'] = 'success';
	$row['msg'] = 'Data Updated Sucessfully';
	echo json_encode($row);
} else if ($TYPE == 'PALLET_DELETE') {
	$PID = $_POST['PID'];

	$oldStockData = legacyAuditFetchRowById('CsStockData', 'StockID', $PID, 'ColdStore');
	$sql_BCD_update = "DELETE FROM CsStockData WHERE StockID ='$PID'";
	$stmt_BCD_update = $iconn->query($sql_BCD_update);


	if ($stmt_BCD_update) {
		legacyAuditLogDeleteById('CsStockData', 'StockID', $PID, $oldStockData, $_POST, ['Remarks' => 'Stock data deleted - PALLET_DELETE'], 'ColdStore');
		$row['sts'] = 'success';
		$row['msg'] = 'Data Updated Sucessfully';
		echo json_encode($row);
	} else {
		$row['sts'] = 'Failed';
		$row['msg'] = 'Faild To Update Data';
		echo json_encode($row);
	}
} else if ($TYPE == 'PALLET_UNPLACE_FOR_PACKING_STORE') {
	$BarCodeID = $_POST['BID'];
	$StockID = $_POST['StockID'];
	$StoreRackId = $_POST['storeRackId'];

	$oldPackingData = legacyAuditFetchRowById('CsPackingStoreData', 'BarCodeID', $BarCodeID, 'ColdStore');
	$sql_BCD_update = "UPDATE `CsPackingStoreData` SET `StockFloorStatus`='0', `StoreRackID` = '0' WHERE `BarCodeID`='$BarCodeID' AND ID='$StockID'";
	$stmt_BCD_update = $iconn->query($sql_BCD_update);

	if ($stmt_BCD_update) {
		legacyAuditLogUpdateById('CsPackingStoreData', 'BarCodeID', $BarCodeID, $oldPackingData, $_POST, ['Remarks' => 'Packing store floor status cleared - PALLET_UNPLACE_FOR_PACKING_STORE'], 'ColdStore');
		$sqlStockBarcodeUpdate = "UPDATE  `CsPackingStoreData` SET BarCodeID ='$BarCodeID' WHERE BarCodeID='$BarCodeID' and ID='$StockID'";
		$stmtStockBarcodeUpdate = $iconn->query($sqlStockBarcodeUpdate);

		$SqlFloorstockCount = "SELECT * FROM CsPackingStoreData WHERE StoreRackID='$StoreRackId'";
		$stmtFloorstockCount = $iconn->query($SqlFloorstockCount);
		$FloorstockCount = mysqli_num_rows($stmtFloorstockCount);

		if ($FloorstockCount == 0) {
			$oldRackData2 = legacyAuditFetchRowById('CsStoresRackData', 'RowID', $StoreRackId, 'ColdStore');
			$sqlUnplacePallet = "UPDATE `CsStoresRackData` SET `PlacedStockID` = '',Status = 0 ,BarCodeID= 0 WHERE RowID ='$StoreRackId'";
		} else {
			$sqlPlacedStockId = "SELECT PlacedStockID FROM CsStoresRackData WHERE `RowID` = '$StoreRackId'";
			$stmtPlacedStockId = $iconn->query($sqlPlacedStockId);
			$rowPlacedStockId = $stmtPlacedStockId->fetch_array();
			$PlacedStockIds = $rowPlacedStockId['PlacedStockID'];
			$stockIdsArray = explode(",", $PlacedStockIds);
			// Filter out any empty elements
			$stockIdsArray = array_filter($stockIdsArray);
			$stockIdsArray = array_diff($stockIdsArray, [$StockID]);
			$newPlacedStockIds = implode(",", $stockIdsArray);
			$oldRackData2 = legacyAuditFetchRowById('CsStoresRackData', 'RowID', $StoreRackId, 'ColdStore');
			if(sizeof($stockIdsArray) > 0){
				$sqlUnplacePallet = "UPDATE `CsStoresRackData` SET `PlacedStockID` = '$newPlacedStockIds' WHERE `RowID` = '$StoreRackId'";
			}else{
				$sqlUnplacePallet = "UPDATE `CsStoresRackData` SET `PlacedStockID` = '',Status = 0 ,BarCodeID= 0 WHERE RowID ='$StoreRackId'";
			}
		}
		$stmtUnplacePallet = $iconn->query($sqlUnplacePallet);
		if($stmtUnplacePallet){
			legacyAuditLogUpdateById('CsStoresRackData', 'RowID', $StoreRackId, $oldRackData2, $_POST, ['Remarks' => 'Rack pallet unplaced - PALLET_UNPLACE_FOR_PACKING_STORE'], 'ColdStore');
		}
	}

	$row['sts'] = 'success';
	$row['msg'] = 'Data Updated Sucessfully';
	echo json_encode($row);
}
