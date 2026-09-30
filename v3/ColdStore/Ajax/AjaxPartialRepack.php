<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");

	if(isset($_POST['PALLET_ID'])){
			
	$BID=$_POST['bid'];
	$rckid=$_POST['rckid'];
	$OUT_TYPE=$_POST['OUT_TYPE'];
	$aval_mcs=$_POST['aval_mcs'];
	$StockID=$_POST['PALLET_ID'];
	$out_mcs=$_POST['out_mcs'];
	$REMARKS=$_POST['REMARKS'];
	$AvalLooseBags=$_POST['AvalLooseBags'];
	$LooseBagsOut=$_POST['LooseBagsOut'];
	
	
	
	 $SqlStockDetails = "select * from  `CsStockData` WHERE StockID ='$StockID'";
	 $StmtStockDetails = $iconn->query($SqlStockDetails);
	 $RowStockDetails  = $StmtStockDetails->fetch_array();	
	 $BarCodeID = $RowStockDetails['BarCodeID'];	 
	 $RowStockDetails=json_encode($RowStockDetails,true);


	 $oldStockData = legacyAuditFetchRowById('CsStockData', 'StockID', $StockID, 'ColdStore');
	 $SqlStockUpdate = "UPDATE  `CsStockData` SET AvalMcs ='$aval_mcs' , LooseBags='$AvalLooseBags' 
						WHERE StockID ='$StockID'";
	 $StmtStockUpdate = $iconn->query($SqlStockUpdate);	
	 if($StmtStockUpdate){
		 legacyAuditLogUpdateById('CsStockData', 'StockID', $StockID, $oldStockData, $_POST, ['Remarks' => 'Partial repack - stock updated'], 'ColdStore');
	 }


	 $SqlStockNo="SELECT sum(AvalMcs) AS McsTotal,sum(LooseBags) as LooseTotal,BarCodeID 
			      from CsStockData where BarCodeID='$BarCodeID'";
	 $StmtStockNo=$iconn->query($SqlStockNo);
	 $RowStock = $StmtStockNo->fetch_array();
     $McsTotal=$RowStock['McsTotal'];
     $LooseTotal=$RowStock['LooseTotal'];
	 
	 
	 $AryCurrentDate=getCurrentDateTime();
	 $PostedDate=$AryCurrentDate['TimeStamp'];
	 $OutDate=date('Y-m-d');
	 $BatchCode=date('Ymd');
	 
	 
	 if($McsTotal == 0 && $LooseTotal == 0){		 
	 $oldRackData = legacyAuditFetchRowById('CsStoresRackData', 'BarCodeID', $BarCodeID, 'ColdStore');
	 $SqlRackData = "UPDATE `CsStoresRackData` SET Status ='0',BarCodeID='0' WHERE BarCodeID ='$BarCodeID'";
	 $StmtRackData = $iconn->query($SqlRackData);
	 if($StmtRackData){
		 legacyAuditLogUpdateById('CsStoresRackData', 'BarCodeID', $BarCodeID, $oldRackData, $_POST, ['Remarks' => 'Rack cleared - zero stock'], 'ColdStore');
	 }
	 }
	 
	 
	 $SqlStockOutData = "INSERT INTO `CsStockOutDetails` 
			(`PostedDate`,`BarCodeID`,`StockID`, `OutMcs`,`OutLooseBags`,`Remarks`,`OutType`,`OutStatus`,`BatchCode`,`OutDate`) VALUES 
			('$PostedDate','$BarCodeID','$StockID','$out_mcs','$LooseBagsOut','$REMARKS','$OUT_TYPE','3','$BatchCode','$OutDate')"; 
	
	 $StmtStockOutData = $iconn->query($SqlStockOutData); 
	 if($StmtStockOutData){
		 legacyAuditLogInsertById('CsStockOutDetails', 'ID', $iconn->insert_id, $_POST, ['Remarks' => 'Partial repack - stock out logged'], 'ColdStore');
	 }
	 
	 
	 
	 $SqlTransactionHistory = "INSERT INTO `CsTransactionHistory`( `TrnsDate`,`TrnsType`, `Data`) VALUES 
						('$PostedDate','$OUT_TYPE','$RowStockDetails')";
	
	 $StmtTransactionHistory = $iconn->query($SqlTransactionHistory);
	 if($StmtTransactionHistory){
		 legacyAuditLogInsertById('CsTransactionHistory', 'ID', $iconn->insert_id, $_POST, ['Remarks' => 'Partial repack - transaction history logged'], 'ColdStore');
	 }
	 
	 
	 
	 
	 
	 if($StmtStockOutData){
		 $row['Error']=null;
		 $row['Msg']='Data Updated Sucessfully';
		echo json_encode($row);
	 }else{
		$row['Error']='Faild To Update Data';
		$row['Msg']='Faild To Update Data';
		 echo json_encode($row);
	 }	
}
	
?>
