<?php
//ini_set('display_errors', 1);

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");


$DailyBatchID=$_POST['DailyBatchId'];

$TYPE=$_POST['TYPE']; 


 $SqlBatch="SELECT ContrctrName,Date,SubContractorName,BatchID FROM  PpcDailyBatchDetails as PDB  
left join PpcSubContractorMaster as PSC  on PSC.SubContactorID=PDB.BatchID
left join PpcContractMaster as PCM  on PCM.ContractorID=PSC.ContactorID
 WHERE ID='$DailyBatchID'"; 
$StmtBatch=$iconn->query($SqlBatch);	
$RowBatch=$StmtBatch->fetch_array();

$BatchID=$RowBatch['BatchID'];
$Date=getFormatedDatelines($RowBatch['Date']);
$Batch=$RowBatch['ContrctrName'].'/'.$RowBatch['SubContractorName'].'(Batch)';


 if($TYPE=='AddBreakupDetails'){
	 date_default_timezone_set('Asia/Kolkata'); 
	$PostedDateTime=date('Y-m-d H:i:s');
$PostedUserId=$_SESSION['UserID'];
$HLQty=$_POST['HLQty'];
$LotID=$_POST['LotID'];
$BatchID=$_POST['BatchID'];
$Datee=$RowBatch['Date'];

  $SqlWeight="SELECT sum(PurchaseWeight) as PurchaseWeight,(SELECT sum(HLQty) FROM PpcDailyBatchBreakUpDeatils 
 WHERE LotID='$LotID')as BreakupQty FROM `RmLotDetatils` as RLD WHERE RLD.RmLotID='$LotID'
";
$StmtWeight=$iconn->query($SqlWeight);
$RowWeight=$StmtWeight->fetch_array();
$Weight=$RowWeight['PurchaseWeight'];
$BreakupQty=$RowWeight['BreakupQty']+$HLQty;


 /*$SqlBreakupCount="SELECT count(*) as Count FROM `PpcDailyBatchBreakUpDeatils` as PDBR
left join PpcDailyBatchDetails as PDB on PDB.ID=PDBR.DailyBatchID
WHERE DailyBatchID='$DailyBatchID'and LotID='$LotID' and Date='$Datee'and Type=1";
$StmtBreakupCount=$iconn->query($SqlBreakupCount);
$RowBreakupCount=$StmtBreakupCount->fetch_array();
$Count=$RowBreakupCount['Count'];*/
 if($BreakupQty<=$Weight){
	   
		  $SqlUpdate="INSERT INTO `PpcDailyBatchBreakUpDeatils`(`DailyBatchID`, `LotID`, `HLQty`, `PostedUserId`, `PostedDateTime`, `BatchId`) VALUES ('$DailyBatchID','$LotID','$HLQty','$PostedUserId','$PostedDateTime','$BatchID')";
		 
		  $StmtUpdate=$iconn->query($SqlUpdate);
		  $BreakupID = $iconn->insert_id;
		  if($StmtUpdate){
				legacyAuditLogInsertById('PpcDailyBatchBreakUpDeatils', 'BreakupID', $BreakupID, $_POST, [
					'ReferenceNo' => $BreakupID,
					'Remarks' => 'Batch breakup details added',
				], 'PreProcessing');
				 
			  $row['sts']='success';
			 $row['msg']='Success';
			 
			 echo json_encode($row);
		  }
 }


 
else{
$row['sts']='Failed';
		$row['msg']='Weight exceeds';
		 echo json_encode($row);	
}
	 
		
}
else if($TYPE=='HLBreakUpDelete'){
	
	$BreakupID=$_POST['BreakupID'];
	$oldBreakupData = legacyAuditFetchRowById('PpcDailyBatchBreakUpDeatils', 'BreakupID', $BreakupID, 'PreProcessing');
  $SqlDelete="DELETE FROM `PpcDailyBatchBreakUpDeatils` WHERE BreakupID='$BreakupID'";
$StmtDelete=$iconn->query($SqlDelete);

 if($StmtDelete){
		legacyAuditLogDeleteById('PpcDailyBatchBreakUpDeatils', 'BreakupID', $BreakupID, $oldBreakupData, $_POST, [
			'ReferenceNo' => $BreakupID,
			'Remarks' => 'Batch breakup details deleted',
		], 'PreProcessing');
			
		 $row['sts']='success';
		$row['msg']='Deleted Successfully';
		
		echo json_encode($row);
	 }
 
else{
$row['sts']='Failed';
		$row['msg']='Failed';
		 echo json_encode($row);	
}

}


?>
