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
// date("Y-m-d H:i:s");
	$PostedDateTime=date('Y-m-d H:i:s');
$PostedUserId=$_SESSION['UserID'];
$HLQty=$_POST['HLQty'];
$LotID=$_POST['LotID'];
$BatchID=$_POST['BatchID'];
$Count=$_POST['Count'];
$VarietyID=$_POST['VarietyId'];
$Datee=$RowBatch['Date'];

 $SqlWeight="SELECT sum(HLWeight) as HLWeight,(SELECT sum(HLQty) FROM PpcDailyBatchPeelingBreakUpDeatils WHERE LotID='$LotID' and Count='$Count')as BreakupQty FROM `PpcHLGradingBreakup` as PGB WHERE PGB.LotID='$LotID' and HLCount='$Count'";
$StmtWeight=$iconn->query($SqlWeight);
$RowWeight=$StmtWeight->fetch_array();
$Weight=$RowWeight['HLWeight'];
$BreakupQty=$RowWeight['BreakupQty']+$HLQty;


	  // if($BreakupQty<=$Weight){
		  $SqlUpdate="INSERT INTO `PpcDailyBatchPeelingBreakUpDeatils`(`DailyBatchID`, `LotID`, `HLQty`, `PostedUserId`, `PostedDateTime`, `BatchId`, `Count`, `VarietyID`) VALUES ('$DailyBatchID','$LotID','$HLQty','$PostedUserId','$PostedDateTime','$BatchID','$Count','$VarietyID')";
		 
  	 $StmtUpdate=$iconn->query($SqlUpdate);
	 $PeelingBreakupID = $iconn->insert_id;
	 
	 if($StmtUpdate){
		legacyAuditLogInsertById('PpcDailyBatchPeelingBreakUpDeatils', 'BreakupID', $PeelingBreakupID, $_POST, [
			'ReferenceNo' => $PeelingBreakupID,
			'Remarks' => 'Peeling breakup details added',
		], 'PreProcessing');
		
			
		 $row['sts']='success';
		$row['msg']='Success';
		
		echo json_encode($row);
	 }
	  //}	
		

else{
$row['sts']='Failed';
		$row['msg']='Weight exceeds';
		 echo json_encode($row);	
}
	
	
}
else if($TYPE=='PeelingBreakUpDelete'){
	
	$BreakupID=$_POST['BreakupID'];
	$oldPeelingData = legacyAuditFetchRowById('PpcDailyBatchPeelingBreakUpDeatils', 'BreakupID', $BreakupID, 'PreProcessing');
  $SqlDelete="DELETE FROM `PpcDailyBatchPeelingBreakUpDeatils` WHERE BreakupID='$BreakupID'";
$StmtDelete=$iconn->query($SqlDelete);

 if($StmtDelete){
		legacyAuditLogDeleteById('PpcDailyBatchPeelingBreakUpDeatils', 'BreakupID', $BreakupID, $oldPeelingData, $_POST, [
			'ReferenceNo' => $BreakupID,
			'Remarks' => 'Peeling breakup details deleted',
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
