<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");



$AryData=$_POST['AryData'];
	
foreach($AryData as $Data){
	$ShipByDate= $Data['ShipByDate'];
	 $PoID= $Data['PoID'];
	$Port= $Data['Port'];
	$FinalDestination= $Data['FinalDestination'];
	
	$SqlCount="SELECT count(*) as Count FROM `ScrmPoData` WHERE PoID='$PoID'";
	$StmtCount=$iconn->query($SqlCount);
	$RowCount=$StmtCount->fetch_array();
	$Count=$RowCount['Count'];
	
	
     $oldPiData = legacyAuditFetchRowById('ScrmPIData', 'PoID', $PoID, 'Sales');
     $SqlPIData="UPDATE `ScrmPIData` SET `FinalDestination`='$FinalDestination' ,`ShipByDate`='$ShipByDate' ,`PortOfDischarge`='$Port' where PoID='$PoID'";	
     $StmtPIData=$iconn->query($SqlPIData);
	 if($StmtPIData){ legacyAuditLogUpdateById('ScrmPIData', 'PoID', $PoID, $oldPiData, ['FinalDestination' => $FinalDestination, 'ShipByDate' => $ShipByDate, 'PortOfDischarge' => $Port], ['Remarks' => 'PI data updated'], 'Sales'); }
	 
	 if($StmtPIData){
		 if($Count!=0){
			$oldPoDtlsData = legacyAuditFetchRowById('ScrmPoData', 'PoID', $PoID, 'Sales');
		$SqlPOData="UPDATE `ScrmPoData` SET `FinalDestination`='$FinalDestination' ,`ShipByDate`='$ShipByDate' ,`PortOfDischarge`='$Port' where PoID='$PoID'";	
     $StmtPOData=$iconn->query($SqlPOData);
		 if($StmtPOData){ legacyAuditLogUpdateById('ScrmPoData', 'PoID', $PoID, $oldPoDtlsData, ['FinalDestination' => $FinalDestination, 'ShipByDate' => $ShipByDate, 'PortOfDischarge' => $Port], ['Remarks' => 'PO data updated from PI details'], 'Sales'); }
	  
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
