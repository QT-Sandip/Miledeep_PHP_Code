<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");



$AryData=$_POST['AryData'];
	
foreach($AryData as $Data){
	$ShipByDate= $Data['ShipByDate'];
	 $PoID= $Data['PoID'];
	$Port= $Data['Port'];
	
	$SqlCount="SELECT count(*) as Count FROM `ScrmPoData` WHERE PoID='$PoID'";
	$StmtCount=$iconn->query($SqlCount);
	$RowCount=$StmtCount->fetch_array();
	$Count=$RowCount['Count'];
	
	
	 $oldPiDtlsRow = legacyAuditFetchRowById('ScrmPIData', 'PoID', $PoID, 'Admin');
     $SqlPIData="UPDATE `ScrmPIData` SET `ShipByDate`='$ShipByDate' ,`PortOfDischarge`='$Port' where PoID='$PoID'";	
     $StmtPIData=$iconn->query($SqlPIData);
	 legacyAuditLogUpdateById('ScrmPIData', 'PoID', $PoID, $oldPiDtlsRow, [], [], 'Admin');
	 
	 if($StmtPIData){
		 if($Count!=0){
		 	$oldPoDtlsRow = legacyAuditFetchRowById('ScrmPoData', 'PoID', $PoID, 'Admin');
			$SqlPOData="UPDATE `ScrmPoData` SET `ShipByDate`='$ShipByDate' ,`PortOfDischarge`='$Port' where PoID='$PoID'";	
     $StmtPOData=$iconn->query($SqlPOData);
	 legacyAuditLogUpdateById('ScrmPoData', 'PoID', $PoID, $oldPoDtlsRow, [], [], 'Admin');
	  
		 }

	 }
	
}
echo "Success";

/*if($StmtPIData){
	echo "Success";
}
else{
	echo "Failed";
}*/

//echo json_encode($Data);




?>
