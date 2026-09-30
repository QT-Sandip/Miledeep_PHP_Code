<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$Grades=$_POST['Grades'];
$Rates=$_POST['Rates'];
$RmPriceDate=$_POST['SelectDate'];
$IsEdit=$_POST['IsEdit'];
$DateId=$_POST['DateId'];



		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
		 $PostedDate=$dateTime->format("Y-m-d H:i:s"); 
		$UserID= $_SESSION['UserID'];

if($IsEdit==1){

}else if($IsEdit==0){
	 $SqlInsert="INSERT INTO `$RmPurchaseRatesHistory` 
  (`UserID`,`RmPriceDate`,`PostedDate`) VALUES ('$UserID','$RmPriceDate','$PostedDate')";
$StmtInsert=$iconn->query($SqlInsert);
$DateId = $iconn->insert_id;
if($StmtInsert){
  legacyAuditLogInsertById($RmPurchaseRatesHistory, 'ID', $DateId, array('UserID'=>$UserID,'RmPriceDate'=>$RmPriceDate,'PostedDate'=>$PostedDate), array(), 'Accounts');
}
}
 


for($i=0; $i < count($Grades) ; $i++ ){

$GRAD=$Grades[$i];
$RATE=$Rates[$i];
if($IsEdit==1){
  $oldRowData = null;
  $oldRowId = null;
  $SelSql = "SELECT * FROM `RmPurchaseRates` WHERE DateID='$DateId' and RmCount='$GRAD'";
  $SelStmt = $iconn->query($SelSql);
  if($SelStmt && $SelStmt->num_rows > 0){
    $oldRowData = $SelStmt->fetch_array();
    $oldRowId = $oldRowData['ID'];
  }

  $SqlInsert="UPDATE `RmPurchaseRates` 
 SET RmPrice='$RATE' 
 where DateID='$DateId' and RmCount='$GRAD'";
}else if($IsEdit==0 && $DateId!==0){

$SqlInsert="INSERT INTO `RmPurchaseRates`
( `RmCount`, `RmPrice`, `DateID`) 
VALUES ('$GRAD','$RATE','$DateId')";
	
}
  $SqlInsert;
$StmtInsert=$iconn->query($SqlInsert);
if($StmtInsert && $IsEdit==1 && $oldRowId){
  legacyAuditLogUpdateById('RmPurchaseRates', 'ID', $oldRowId, $oldRowData, array('RmCount'=>$GRAD,'DateID'=>$DateId,'RmPrice'=>$RATE), array(), 'Accounts');
}else if($StmtInsert && $IsEdit==0){
  legacyAuditLogInsertById('RmPurchaseRates', 'ID', $iconn->insert_id, array('RmCount'=>$GRAD,'RmPrice'=>$RATE,'DateID'=>$DateId), array(), 'Accounts');
}
}


if($StmtInsert){
	echo 'Success';
	
	if($IsEdit){
		$Msg='RM Prices Updated For Date - ';
	}else{
		$Msg='RM Prices Added For Date - ';
	}
	
	  $SqlInsert="Select * from $RmPurchaseRatesHistory where ID='$DateId' ";
	  $StmtInsert=$iconn->query($SqlInsert);
	  $RowHis=$StmtInsert->fetch_array();
	 $RmPriceDate= $RowHis['RmPriceDate'];
	
	include "../includes/FuncMsg.php";
	$number='9396344037';
	$T_MSG=$Msg.$RmPriceDate.' Ignore This ,If you Changed' ;
	sendSms($number,$T_MSG);
}else{
	echo 'Failed';
}


?>
