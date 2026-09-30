<?php  
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	
	
	$SqlScrmReports="SELECT * FROM `ScrmShipmentDetailsBreif` "; 
	$StmtScrmReports=$iconn->query($SqlScrmReports);
	while($RowAvalRep=$StmtScrmReports->fetch_assoc()){
echo 	$invoicenum=$RowAvalRep['InvNo'];
	
	 $SqlData="SELECT * FROM `ScrmPoData`  where InvNo='$invoicenum'"; 
	$StmtData=$iconn->query($SqlData);
	$RowData=$StmtData->fetch_assoc();
	$PoID=$RowData['PoID'];
	
	$oldRow = legacyAuditFetchRowById('ScrmShipmentDetailsBreif', 'InvNo', $invoicenum, 'Admin');
	echo $SqlDataU="update ScrmShipmentDetailsBreif set PiID='$PoID' where InvNo='$invoicenum'"; 
	$StmtDataU=$iconn->query($SqlDataU);
	legacyAuditLogUpdateById('ScrmShipmentDetailsBreif', 'InvNo', $invoicenum, $oldRow, [], [], 'Admin');
	
		
	}
 	
	
	?>
