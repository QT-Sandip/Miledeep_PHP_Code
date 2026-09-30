<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$TYPE = $_POST['TYPE'];
$Persons = $_POST['Persons'];
$Adjustments = $_POST['Adjustments'];
$Extras = $_POST['Extras'];
$Remarks = $_POST['Remarks'];
$BatchID = $_POST['BatchID'];
$ShiftID = $_POST['ShiftID'];
$Date = $_POST['Date'];
$PurchaseStationId = $_POST['PurchaseStationId'];
$PostedDateTime = date('Y-m-d h:i:s');
$PostedUserId = $_SESSION['UserID'];

if ($TYPE == 'AddBatchDetails') {
	$EntryType = $_POST['EntryType'];

	$SqlInsert = " INSERT INTO `PpcDailyBatchDetails`( `BatchID`, `Persons`, `Adjustments`, `Extras`, `Date`,`StationID`, `Remarks`,`ShiftID`, `Type`, `PostedUserId`, `PostedDateTime`, `Status`) 
		 VALUES ('$BatchID','$Persons','$Adjustments','$Extras','$Date','$PurchaseStationId','$Remarks','$ShiftID'
		 ,'$EntryType','$PostedUserId','$PostedDateTime','0')";

	$StmtInsert = $iconn->query($SqlInsert);
	$InsertedId = $iconn->insert_id;


	if ($StmtInsert) {
		legacyAuditLogInsertById('PpcDailyBatchDetails', 'ID', $InsertedId, $_POST, [
			'ReferenceNo' => $InsertedId,
			'Remarks' => 'Daily batch details added',
		], 'PreProcessing');

		$row['sts'] = 'success';
		$row['msg'] = 'Success';
		$row['InsertedId'] = $InsertedId;
		echo json_encode($row);
	} else {
		$row['sts'] = 'Failed';
		$row['msg'] = 'Failed';

		echo json_encode($row);
	}

} else if ($TYPE == 'UpdateBatchDetails') {

	$oldBatchData = legacyAuditFetchRowById('PpcDailyBatchDetails', 'ID', $BatchID, 'PreProcessing');
	$SqlUpdate = "UPDATE `PpcDailyBatchDetails` SET `Persons`='$Persons',`Adjustments`='$Adjustments',`Extras`='$Extras',`Remarks`='$Remarks',`ShiftID`='$ShiftID',`PostedUserId`='$PostedUserId',`PostedDateTime`='$PostedDateTime'
		 WHERE `ID`='$BatchID'";

	$StmtUpdate = $iconn->query($SqlUpdate);

	if ($StmtUpdate) {
		legacyAuditLogUpdateById('PpcDailyBatchDetails', 'ID', $BatchID, $oldBatchData, $_POST, [
			'ReferenceNo' => $BatchID,
			'Remarks' => 'Daily batch details updated',
		], 'PreProcessing');

		$row['sts'] = 'success';
		$row['msg'] = 'Success';
		$row['BatchID'] = $BatchID;
		echo json_encode($row);
	} else {
		$row['sts'] = 'Failed';
		$row['msg'] = 'Failed';
		echo json_encode($row);
	}

} else if ($TYPE == 'Approval') {

	echo $BatchID;
	$oldBatchData = legacyAuditFetchRowById('PpcDailyBatchDetails', 'ID', $BatchID, 'PreProcessing');
	$SqlUpdate = "UPDATE `PpcDailyBatchDetails` SET `ApprovalStatus`='1' WHERE `ID`='$BatchID'";

	$StmtUpdate = $iconn->query($SqlUpdate);

	if ($StmtUpdate) {
		legacyAuditLogUpdateById('PpcDailyBatchDetails', 'ID', $BatchID, $oldBatchData, $_POST, [
			'ReferenceNo' => $BatchID,
			'Remarks' => 'Daily batch approved',
		], 'PreProcessing');

		$row['sts'] = 'success';
		$row['msg'] = 'Success';
		$row['BatchID'] = $BatchID;
		echo json_encode($row);
	} else {
		$row['sts'] = 'Failed';
		$row['msg'] = 'Failed';
		echo json_encode($row);
	}

}

/*else if($TYPE=='AddBreakupDetails'){
	$DailyBatchID=$_POST['DailyBatchId'];
	$HLQty=$_POST['HLQty'];
	$LotID=$_POST['LotID'];

	   
		  $SqlUpdate="INSERT INTO `PpcDailyBatchBreakUpDeatils`(`DailyBatchID`, `LotID`, `HLQty`, `PostedUserId`, `PostedDateTime`, `BatchId`) VALUES ('$DailyBatchID','$LotID','$HLQty','$PostedUserId','$PostedDateTime','$BatchID')";
		 
	   $StmtUpdate=$iconn->query($SqlUpdate);
	 
	 if($StmtUpdate){
		
			
		 $row['sts']='success';
		$row['msg']='Success';
		
		echo json_encode($row);
	 }
	 
	 else{
		$row['sts']='Failed';
		$row['msg']='Failed';
		 echo json_encode($row);
	 }
	
}*/
?>
