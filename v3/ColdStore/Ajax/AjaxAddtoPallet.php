<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


 $StockID=$_POST['StockID'];  
$RowID=$_POST['RowID'];
$Type=$_POST['Type'];
$BarCodeID=$_POST['BarCodeID'];
 

if($Type=='AddToPallet'){
   $SqlRack="SELECT * FROM `CsStoresRackData` WHERE RowID='$RowID'";
   $StmtRack=$iconn->query($SqlRack);
   $RowRack=$StmtRack->fetch_array();
   $RackBarCodeID=$RowRack['BarCodeID'];
   
	
	
	$SqlStock="SELECT * FROM `CsStockData` WHERE StockID='$StockID'";
	$StmtStock=$iconn->query($SqlStock);  
	$RowStock=$StmtStock->fetch_array();
	$StockBarCodeID=$RowStock['BarCodeID']; 
	
	if($RackBarCodeID==0){
		$oldRackData = legacyAuditFetchRowById('CsStoresRackData', 'RowID', $RowID, 'ColdStore');
		 $SqlUpdate="UPDATE `CsStoresRackData` SET `BarCodeID`='$StockBarCodeID' ,Status=1 WHERE `RowID`='$RowID'";
		$Stmtupdate=$iconn->query($SqlUpdate);
		if($Stmtupdate){
		legacyAuditLogUpdateById('CsStoresRackData', 'RowID', $RowID, $oldRackData, $_POST, ['Remarks' => 'Add barcode to rack - AddToPallet'], 'ColdStore');
		$oldBarCodeData = legacyAuditFetchRowById('CsBarCodes', 'BarCodeID', $StockBarCodeID, 'ColdStore');
		$SqlFloorUpdate="UPDATE `CsBarCodes` SET `OnFloor`='1' WHERE `BarCodeID`='$StockBarCodeID'";
		$StmtFloorUpdate=$iconn->query($SqlFloorUpdate);
		if($StmtFloorUpdate){
			legacyAuditLogUpdateById('CsBarCodes', 'BarCodeID', $StockBarCodeID, $oldBarCodeData, $_POST, ['Remarks' => 'Set OnFloor=1 - AddToPallet'], 'ColdStore');
		}
		}
}else{
	$oldStockData = legacyAuditFetchRowById('CsStockData', 'StockID', $StockID, 'ColdStore');
	$SqlUpdate="UPDATE `CsStockData` SET `BarCodeID`='$RackBarCodeID' WHERE `StockID`='$StockID'";
		$Stmtupdate=$iconn->query($SqlUpdate);
		if($Stmtupdate){
			legacyAuditLogUpdateById('CsStockData', 'StockID', $StockID, $oldStockData, $_POST, ['Remarks' => 'Update stock barcode - AddToPallet'], 'ColdStore');
		}
}

}
else if($Type=='AddStocktoPallet'){
	$SqlRack="SELECT * FROM `CsStoresRackData` WHERE RowID='$RowID'";
   $StmtRack=$iconn->query($SqlRack);
   $RowRack=$StmtRack->fetch_array();
   $RackBarCodeID=$RowRack['BarCodeID'];
   
	
	
	
	$StockBarCodeID=$BarCodeID; 
	
	if($RackBarCodeID==0){
		$oldRackData = legacyAuditFetchRowById('CsStoresRackData', 'RowID', $RowID, 'ColdStore');
		 $SqlUpdate="UPDATE `CsStoresRackData` SET `BarCodeID`='$StockBarCodeID' ,Status=1 WHERE `RowID`='$RowID'";
		$Stmtupdate=$iconn->query($SqlUpdate);
		if($Stmtupdate){
		legacyAuditLogUpdateById('CsStoresRackData', 'RowID', $RowID, $oldRackData, $_POST, ['Remarks' => 'Add stock barcode to rack - AddStocktoPallet'], 'ColdStore');
		$oldBarCodeData = legacyAuditFetchRowById('CsBarCodes', 'BarCodeID', $StockBarCodeID, 'ColdStore');
		$SqlFloorUpdate="UPDATE `CsBarCodes` SET `OnFloor`='1' WHERE `BarCodeID`='$StockBarCodeID'";
		$StmtFloorUpdate=$iconn->query($SqlFloorUpdate);
		if($StmtFloorUpdate){
			legacyAuditLogUpdateById('CsBarCodes', 'BarCodeID', $StockBarCodeID, $oldBarCodeData, $_POST, ['Remarks' => 'Set OnFloor=1 - AddStocktoPallet'], 'ColdStore');
		}
		}
}else{
	$oldStockData = legacyAuditFetchRowById('CsStockData', 'BarCodeID', $BarCodeID, 'ColdStore');
	$SqlUpdate="UPDATE `CsStockData` SET `BarCodeID`='$RackBarCodeID' WHERE `BarCodeID`='$BarCodeID'";
		$Stmtupdate=$iconn->query($SqlUpdate);
		if($Stmtupdate){
			legacyAuditLogUpdateById('CsStockData', 'BarCodeID', $BarCodeID, $oldStockData, $_POST, ['Remarks' => 'Update stock barcode - AddStocktoPallet'], 'ColdStore');
		}
}

	
}
?>
