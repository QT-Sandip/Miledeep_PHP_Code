<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

 $TYPE=$_POST['TYPE'];


$LoginId=$_SESSION["UserID"];


 IF($TYPE=='CollectionBreakUpdelete'){
	
	$FCNumber=$_POST['FcNumber'];
	$CollectionBreakUpID=$_POST['CollectionBreakUpID'];
	$PoID=$_POST['PoID'];
	
	$SqlCollectiondelete="DELETE FROM `ScrmCollectionBreakUP`  WHERE ColBrkID='$CollectionBreakUpID'";
$oldCollectionBrk = $iconn->query("SELECT * FROM `ScrmCollectionBreakUP` WHERE ColBrkID='$CollectionBreakUpID'")->fetch_assoc();
$StmtCollectiondelete=$iconn->query($SqlCollectiondelete);

	if($StmtCollectiondelete){
		legacyAuditLogDeleteById('ScrmCollectionBreakUP', 'ColBrkID', $CollectionBreakUpID, $oldCollectionBrk, $_POST, [
			'ReferenceNo' => $FCNumber,
			'Remarks' => 'Collection breakup deleted',
		], 'Exports');
	}

	if($StmtCollectiondelete && $FCNumber!=''){
	 $SqlUtilisedValue="SELECT NetValue,
	(select sum(UtilizedValue) from ScrmCollectionBreakUP where FCNumber='$FCNumber') as UtilizedValue
	FROM `ScrmForwardContractsDetails` where FCNumber='$FCNumber'";

	$StmtUtilisedValue=$iconn->query($SqlUtilisedValue);
	$RowUtilised=$StmtUtilisedValue->fetch_array();
	$NetValue=$RowUtilised['NetValue'];
	$UtilizedValue=$RowUtilised['UtilizedValue'];
	$Balance=$NetValue-$UtilizedValue;

  $SqlFcDetails="UPDATE `ScrmForwardContractsDetails` SET
 `UtilizedValue` ='$UtilizedValue',
 BalancedValue='$Balance' where FCNumber='$FCNumber'";
 $oldFcDetails = $iconn->query("SELECT * FROM `ScrmForwardContractsDetails` WHERE FCNumber='$FCNumber'")->fetch_assoc();
$StmtFcDetails=$iconn->query($SqlFcDetails);
if($StmtFcDetails){
	legacyAuditLogUpdateById('ScrmForwardContractsDetails', 'FCNumber', $FCNumber, $oldFcDetails, $_POST, [
		'ReferenceNo' => $FCNumber,
		'Remarks' => 'Forward contract utilized value updated after collection delete',
	], 'Exports');
}



}


//else if($StmtCollectiondelete){
	$AryPrices=getPaymentValues($iconn,$PoID);
$InvValueUSD=$AryPrices['InvValueUSD'];

	$SqlCollections="SELECT * FROM `ScrmPaymentCollections` WHERE PIID='$PoID'";
$StmtCollections=$iconn->query($SqlCollections);
$RowCollections=$StmtCollections->fetch_array();

$BankCharges=$RowCollections['BnkChrges'];
$ExtraAmount=$RowCollections['EtraAmount'];
$OtherAmount=$RowCollections['OtherAmount'];

$SqlUtilisedValues="SELECT sum(UtilizedValue) as UtilizedValue FROM `ScrmCollectionBreakUP` WHERE PiID='$PoID'";
$StmtUtilisedValues=$iconn->query($SqlUtilisedValues);
$RowUtilisedValues=$StmtUtilisedValues->fetch_array();
$UtilizedValueOld=$RowUtilisedValues['UtilizedValue'];

$SqlRealized="SELECT * FROM `ScrmRealizationDetails` WHERE PoID='$PoID'";
$StmtRealized=$iconn->query($SqlRealized);
$Count=$StmtRealized->fetch_array();


	 $ShortFall=$InvValueUSD+$ExtraAmount-$BankCharges-$UtilizedValueOld-$OtherAmount;

if($ShortFall==0){
	$Status=1;

}else{
	$Status=0;
	if($Count!=0){
	// $SqlDelete="UPDATE `ScrmRealizationDetails` SET `RezStatus`='$Status' WHERE PoID='$PoID'";
	$oldRealization = $iconn->query("SELECT * FROM `ScrmRealizationDetails` WHERE PoID='$PoID'")->fetch_assoc();
	$SqlDelete="Delete from ScrmRealizationDetails where `PoID`='$PoID'";
	$StmtDelete=$iconn->query($SqlDelete);
	if($StmtDelete){
		legacyAuditLogDeleteById('ScrmRealizationDetails', 'PoID', $PoID, $oldRealization, $_POST, [
			'ReferenceNo' => $PoID,
			'Remarks' => 'Realization details deleted after collection delete',
		], 'Exports');
	}
	}
}

$SqlStatusUpdate="UPDATE `ScrmPaymentCollections` SET `Status`='$Status' WHERE
`PiID`='$PoID'";
$oldPaymentCollection = $iconn->query("SELECT * FROM `ScrmPaymentCollections` WHERE PiID='$PoID'")->fetch_assoc();
$StmtStatusUpdate=$iconn->query($SqlStatusUpdate);
if($StmtStatusUpdate){
	legacyAuditLogUpdateById('ScrmPaymentCollections', 'PiID', $PoID, $oldPaymentCollection, $_POST, [
		'ReferenceNo' => $PoID,
		'Remarks' => 'Payment collection status updated after collection delete',
	], 'Exports');
}



//}

if($StmtStatusUpdate){
	 $row['sts']='success';
		$row['msg']='Deleted sucessfully';
		echo json_encode($row);
}


	 else{
		$row['sts']='Failed';
		$row['msg']='Failed to Delete';
		 echo json_encode($row);
	 }
	 
	 
	
}


 ?>
