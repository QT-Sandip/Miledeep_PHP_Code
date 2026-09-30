<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");


$PoID = $_POST['PoID'];
$TYPE = $_POST['TYPE'];
//$UnitID=$_POST['UnitID'];
$UnitID = $_POST['ColdstoreID'];
$TenantID = $_POST['TenantID'];



if ($PoID != '') {
	$Query = " and CPID.PoID in($PoID)";
} else {

	$SqlPoList = "SELECT Group_concat(PoID) as PoID FROM `ScrmPoData` as SPD
LEFT Join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
WHERE (ShippedOn='' or ShippedOn is Null);";
	$StmtPoList = $iconn->query($SqlPoList);
	$RowPo = $StmtPoList->fetch_array();
	$PoID = $RowPo['PoID'];
	$Query = " and CPID.PoID in($PoID)";
}
if ($TenantID != 'BOTH') {
	$Query .= " and CPID.StockHolderID='$TenantID'";
}

if ($TYPE == 'LIST') {


	/*$sql="SELECT cb.BarCodeID,ShortName,BarCodeValue,coalesce(PoNo,'NA') as PoNo,RowID,cr.BlockID,RackName,
			StockID,AvalMcs,BlockName,PackingTypeID,PackingStyle,Grade,
			ProductionDate, `ProductionTypeID`, ProductCategoryID,
			UnitID FROM `CsBarCodes` as cb 
			left join `CsStoresRackData` as cr on cb.BarCodeID = cr.BarCodeID 
			left join CsStoresBlockData as CBD on CBD.BlockID=cr.BlockID

			left join `CsStockData` as CPID on cb.BarCodeID = CPID.BarCodeID
	        left join ScrmPIVarietyList as AGO on CPID.VarietyID=AGO.VarietyID 

			left join ScrmPoData as CPOS on CPOS.PoID  = CPID.PoID 
			left join AdminPackingStyles as CSP on CSP.PCKID = CPID.PackingStyleID			
			 WHERE  UnitID='$UnitID' $Query order by PoNo asc";*/
	$sql = "SELECT cb.BarCodeID,ShortName,BarCodeValue,coalesce(PoNo,'NA') as PoNo,RowID,cr.BlockID,RackName,StoreName,
			StockID,AvalMcs,BlockName,PackingTypeID,CSP.PackingStyle,CPID.Grade,
			ProductionDate, `ProductionTypeID`, ProductCategoryID,
			UnitID FROM `CsBarCodes` as cb 
			left join `CsStoresRackData` as cr on cb.BarCodeID = cr.BarCodeID 
			left join CsStoresBlockData as CBD on CBD.BlockID=cr.BlockID

			left join `CsStockData` as CPID on cb.BarCodeID = CPID.BarCodeID
	        left join ScrmPIVarietyList as AGO on CPID.VarietyID=AGO.VarietyID 

			left join ScrmPoData as CPOS on CPOS.PoID  = CPID.PoID 
			left join AdminPackingStyles as CSP on CSP.PCKID = CPID.PackingStyleID			
			left join CsStores as CS on CS.StoreID = CPID.UnitID			
			 WHERE   (AvalMcs !=0  or LooseBags !=0)  and UnitID='$UnitID'
			 $Query order by PoNo asc";


	$stmt = $iconn->query($sql);



	$data = ' 
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
      
                 <tr>	  
        <th>ID</th>
		<th>Bar Code</th>
		<th>Variety</th>
        <th>Grade</th>	
		<th>Mcs</th>
		<th>Packing Style</th>
		<th>Packing Type</th>
		<th>Store</th>
		<th>Location</th>
      </tr>               
                </thead>
                <tbody id >';



	while ($RowData = $stmt->fetch_array()) {


		$BID = $RowData['BarCodeID'];
		$BarcodeValue = $RowData['BarCodeValue'];
		$AvalMcs = $RowData['AvalMcs'];
		$VARITY = $RowData['ShortName'];
		$GRADE = $RowData['Grade'];
		$PACKING = $RowData['PackingStyle'];
		$PoNo = $RowData['PoNo'];
		$BlockName = $RowData['BlockName'];
		$RackName = $RowData['RackName'];
		$PackingType = $RowData['PackingTypeID'];
		$StoreName = $RowData['StoreName'];




		$data .= '<td >' . $BID . '</td>
		
		<td >' . $BarcodeValue . '</td>
		<td >' . $VARITY . '</td>
		<td >' . $GRADE . '</td>
		<td >' . $AvalMcs . '</td>
		<td >' . $PACKING . '</td>
		<td >' . $PackingType . '</td>
		<td >' . $StoreName . '</td>
		<td >' . $BlockName . ' - ' . $RackName . '</td>

			</tr>';
	}


	echo $data .= '</tbody></table>';
} else if ($TYPE == 'UPDATE') {

	$sql_cspo = "SELECT * from ScrmPoData WHERE  PoID='$PoID' ";
	$stmt_cspo = $iconn->query($sql_cspo);
	$count = $stmt_cspo->num_rows;
	$row_cspo = $stmt_cspo->fetch_array();

	if ($count != 0 && $row_cspo['Status'] != 4) {

		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$ProductionDate = $dateTime->format("Y-m-d  H:i:s");

		//Update PoStatus To Shipped Out Status

		$oldPoData = legacyAuditFetchRowById('ScrmPoData', 'PoID', $PoID, 'ColdStore');
		$SqlPoUpdate = "UPDATE `ScrmPoData` SET Status ='4', ShippedOutDate='$ProductionDate'
						WHERE PoID ='$PoID'";
		$StmtPoUpdate = $iconn->query($SqlPoUpdate);
		if($StmtPoUpdate){
			legacyAuditLogUpdateById('ScrmPoData', 'PoID', $PoID, $oldPoData, $_POST, ['Remarks' => 'PO status updated to shipped out'], 'ColdStore');
		}


		//Inorder To Active or InActive BarCodes
		$SqlActiveBarCodes = "SELECT DISTINCT(BarCodeID) FROM `CsStockData` WHERE PoID='$PoID' and UnitID='$UnitID'";
		$StmtActiveBarCodes = $iconn->query($SqlActiveBarCodes);

		while ($RowActiveBarCodes = $StmtActiveBarCodes->fetch_array()) {
			$BID = $RowActiveBarCodes['BarCodeID'];

			$SqlMcsCheck = "SELECT sum(AvalMcs) as BarCodeMcs,
					(SELECT sum(AvalMcs) FROM `CsStockData` WHERE PoID='$PoID' and BarCodeID='$BID') as PoMcs 
					FROM `CsStockData` as CSD 
					left join CsBarCodes as CBC on CSD.BarCodeID=CBC.BarCodeID  WHERE CSD.BarCodeID='$BID'";
			$StmtMcsCheck = $iconn->query($SqlMcsCheck);
			$RowMcsCheck = $StmtMcsCheck->fetch_array();

			if ($RowMcsCheck['BarCodeMcs'] == $RowMcsCheck['PoMcs']) {
				$BarcodeStatus = true;
			} else {
				$BarcodeStatus = false;
			}

			$sql_csr = "SELECT RowID FROM `CsStoresRackData` WHERE `BarCodeID`='$BID'";
			$stmt_csr = $iconn->query($sql_csr);
			$row_csr = $stmt_csr->fetch_array();
			$RackID = $row_csr['RowID'];


			$SqlOutStockMcs = "SELECT * FROM `CsStockData` join CsBarCodes on CsStockData.BarCodeID=CsBarCodes.BarCodeID 
			WHERE CsStockData.BarCodeID='$BID' and CsStockData.PoID='$PoID'
			and CsBarCodes.BarCodeStatus =1";

			$StmtOutStockMcs = $iconn->query($SqlOutStockMcs);

			while ($RowOutMcs = $StmtOutStockMcs->fetch_array()) {
				$StockID = $RowOutMcs['StockID'];
				$OutMcs = $RowOutMcs['AvalMcs'];
				$OutType = 'Loading';

				$SqlStockOutLog = "INSERT INTO `CsStockOutDetails`(`PostedDate`, `BarCodeID`, `StockID`,`RackID`, `OutMcs`,  `Remarks`, `OutType`, `OutStatus`)
				VALUES ('$ProductionDate','$BID','$StockID','$RackID','$OutMcs','$PoID','$OutType','4')";
				$StmtStockOutLog = $iconn->query($SqlStockOutLog);
				if($StmtStockOutLog){
					legacyAuditLogInsertById('CsStockOutDetails', 'ID', $iconn->insert_id, $_POST, ['Remarks' => 'Stock out logged for PO shipment'], 'ColdStore');
				}
			}


			if ($BarcodeStatus) {
				$oldBarCodeData = legacyAuditFetchRowById('CsBarCodes', 'BarCodeID', $BID, 'ColdStore');
				$sql_BCD_update = "UPDATE  `CsBarCodes` SET BarCodeStatus ='0' WHERE BarCodeID ='$BID'";
				$stmt_BCD_update = $iconn->query($sql_BCD_update);
				if($stmt_BCD_update){
					legacyAuditLogUpdateById('CsBarCodes', 'BarCodeID', $BID, $oldBarCodeData, $_POST, ['Remarks' => 'Barcode deactivated - PO shipment'], 'ColdStore');
				}

				$oldRackData = legacyAuditFetchRowById('CsStoresRackData', 'BarCodeID', $BID, 'ColdStore');
				$sql_RD_update = "UPDATE `CsStoresRackData` SET BarCodeID ='0' , Status ='0' where BarCodeID='$BID'";
				$stmt_RD_update = $iconn->query($sql_RD_update);
				if($stmt_RD_update){
					legacyAuditLogUpdateById('CsStoresRackData', 'BarCodeID', $BID, $oldRackData, $_POST, ['Remarks' => 'Rack data cleared - PO shipment'], 'ColdStore');
				}
			} else {
				$oldStockData = legacyAuditFetchRowById('CsStockData', 'BarCodeID', $BID, 'ColdStore');
				$sql_RD_update = "UPDATE `CsStockData` SET AvalMcs ='0' , LooseBags ='0' where PoID='$PoID' and BarCodeID='$BID'";
				$stmt_RD_update = $iconn->query($sql_RD_update);
				if($stmt_RD_update){
					legacyAuditLogUpdateById('CsStockData', 'BarCodeID', $BID, $oldStockData, $_POST, ['Remarks' => 'Stock zeroed out - partial PO shipment'], 'ColdStore');
				}
			}
		}

		if ($stmt_RD_update) {
			$row['sts'] = 'success';
			$row['msg'] = 'Data Updated Sucessfully';
			echo json_encode($row);
		} else {
			$row['sts'] = 'Failed';
			$row['msg'] = 'Faild To Update Data';
			echo json_encode($row);
		}
	} else {
		$row['sts'] = 'Failed';
		if ($count == 0) {
			$row['msg'] = 'No Purchase Order Available For Given Lot Number,Please Check Purchase Order List';
		} else {
			$row['msg'] = 'Lot Already Loaded';
		}
		echo json_encode($row);
	}
}
