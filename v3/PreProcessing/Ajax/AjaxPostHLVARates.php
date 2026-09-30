<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
$PostedDate=$dateTime->format("Y-m-d H:i:s"); 

$UserID=$_SESSION['UserID'];

$Grades=$_POST['Grades'];
$Rates=$_POST['Rates'];

 $StationID=$_POST['StationID'];
 $SupplierType=$_POST['SupplierType'];
 $VarietyId=$_POST['VarietyId'];
 $Shift=$_POST['Shift'];
 $EffectiveDate=$_POST['EffectiveDate'];
$AryData=checkRatesExist();
$TrackId=$AryData['TrackId'];
$IsEdit=$AryData['IsEdit'];
if($TrackId!=''){
	postPpcHLVARates($TrackId,$IsEdit);
}else{
	echo "Rates Already Exist For Given Data";
}

function checkRatesExist(){
	 global 
	 $PostedDate,
		$UserID,
		$iconn,
		$StationID,
		$SupplierType,
		$VarietyId,
		$Shift,
		$EffectiveDate;	
	
$sqlCheckHistory="SELECT * FROM `PpcHLVARatesHistory` WHERE 
 StationID='$StationID' and 
 ContractorType='$SupplierType' and 
 Variety='$VarietyId' and 
 Shift='$Shift' and 
 EffectiveDate='$EffectiveDate'";
 $stmtCheckHistory=$iconn->query($sqlCheckHistory);	
 $RowHistory=$stmtCheckHistory->fetch_array();	
 
 $IsEdit=true;
 $TrackId=$RowHistory['ID'];
 
 if($TrackId==''){
	 $IsEdit=false; 
	 $SqlPostVaRatesHis="INSERT INTO `PpcHLVARatesHistory` ( 
	 `UserID`, `EffectiveDate`, `PostedDate`, `StationID`, 
	 `ContractorType`, `Variety`, `Shift`) 
	 VALUES ('$UserID','$EffectiveDate','$PostedDate',
	 '$StationID','$SupplierType','$VarietyId','$Shift')";
	 $stmtPostVaRatesHis=$iconn->query($SqlPostVaRatesHis);	
	 	$TrackId = $iconn->insert_id;
		legacyAuditLogInsertById('PpcHLVARatesHistory', 'ID', $TrackId, $_POST, [
			'ReferenceNo' => $TrackId,
			'Remarks' => 'HL/VA rates history created',
		], 'PreProcessing');
 }
	 
	 $Data['TrackId']=$TrackId;
	 $Data['IsEdit']=$IsEdit;
	
	return $Data;
}


function postPpcHLVARates($TrackId,$IsEdit){
	 global 
	 $iconn,
	 $Grades,
	 $Rates,
	 $VarietyId
	 ;



for($i=0; $i < count($Grades) ; $i++ ){

$Grade=$Grades[$i];
$PricePerKg=$Rates[$i]; 

  
if($IsEdit){
 $oldRateData = legacyAuditFetchRowById('PpcHLVARates', 'RateID', $TrackId, 'PreProcessing');
 $SqlInsert="UPDATE `PpcHLVARates` set `PricePerKg`='$PricePerKg' WHERE `RateID`='$TrackId' and `Grade`='$Grade'";
}else if(!$IsEdit){
	if($VarietyId!=1){
		$SType='VA';
		
	}else{
		$SType='HL';
	}
   $SqlGradeData="select * from PpcGradesGroupMaster where SType ='$SType' and Grade='$Grade'";
	$StmtGradeData=$iconn->query($SqlGradeData);
	$RowGradeData=$StmtGradeData->fetch_array();
	$LowerLimit=$RowGradeData['LowerLimit'];
	$UpperLimit=$RowGradeData['UpperLimit'];



 $SqlInsert="INSERT INTO `PpcHLVARates`(`LowerLimit`, `UpperLimit`,`Grade`, 
			`VarietyID`, `PricePerKg`, `RateID`) VALUES 
			('$LowerLimit','$UpperLimit','$Grade','$VarietyId','$PricePerKg',
			'$TrackId')";
	
}

$StmtInsert=$iconn->query($SqlInsert);
if($IsEdit && $StmtInsert){
	legacyAuditLogUpdateById('PpcHLVARates', 'RateID', $TrackId, $oldRateData, $_POST, [
		'ReferenceNo' => $TrackId,
		'Remarks' => 'HL/VA rates updated for grade ' . $Grade,
	], 'PreProcessing');
}
}
//   echo $SqlInsert;

if($StmtInsert){
	echo 'Success';
	if($IsEdit){
		$Msg='Costing Rates Updated For Date - ';
	}else{
		$Msg='Costing Rates Added For Date - ';
	}
	
}else{
	echo 'Failed';
}


return $Data;
}

?>
