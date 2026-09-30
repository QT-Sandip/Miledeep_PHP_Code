<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$AvalMcs=$_POST['AvalMcs'];
$MailSentDate=$_POST['MailSentDate'];
$ApprovalDate=$_POST['ApprovalDate'];
$MaterialStatus=$_POST['MaterialStatus'];
$OrderedDate=$_POST['OrderedDate'];
$ReceivedDate=$_POST['ReceivedDate'];

$Type=$_POST['Type'];
 $PoID=$_POST['POID'];
$POGradeId=$_POST['POGradeId'];

$SqlProduction="SELECT * FROM `$ScrmPackingData` WHERE PoID='$PoID'"; 
//$SqlProduction="SELECT * FROM `ScrmPackingData` WHERE POGradeId='$POGradeId'"; 
  
  	 $StmtProduction=$iconn->query($SqlProduction);
	 /*$RowProduction=$StmtProduction->fetch_array();
	 $EmpID=$RowProduction['EmpID'];*/ 
$LastUpdatedDate=date('Y-m-d');
	 
IF($Type=='AddPackingData'){
		 //if($AvalMcs>=100){
	 
	 $Count=mysqli_num_rows($StmtProduction);
	 if($Count==0){
		 //if($AvalMcs>=100){
	echo $sql="INSERT INTO `ScrmPackingData`(`PoID`,`LastUpdatedDate`, `AvalMcs`, `MaterialStatus`, `MailSentDate`, 
	 `ApprovalDate`, `OrderedDate`, `ReceivedDate`, `POGradeId`) 
	 VALUES ('$PoID','$LastUpdatedDate','$AvalMcs','$MaterialStatus','$MailSentDate','$ApprovalDate','$OrderedDate','$ReceivedDate','$POGradeId')"; 
   exit;
  	 $stmt=$iconn->query($sql);
	
	 
	 }
	 else{
		$oldPackData = legacyAuditFetchRowById($ScrmPackingData, 'PoID', $PoID, 'Sales');
		 
				// if($AvalMcs>=100){
 
	 $sql="UPDATE `ScrmPackingData` SET `PoID`='$PoID',`LastUpdatedDate`='$LastUpdatedDate',`AvalMcs`='$AvalMcs',
	 `MaterialStatus`='$MaterialStatus',`MailSentDate`='$MailSentDate',`ApprovalDate`='$ApprovalDate',
	 `OrderedDate`='$OrderedDate',`ReceivedDate`='$ReceivedDate'
	 WHERE `PoID`='$PoID'"; 
   
  	 $stmt=$iconn->query($sql);
	 if($stmt){
		legacyAuditLogUpdateById($ScrmPackingData, 'PoID', $PoID, $oldPackData, $_POST, [
			'Remarks' => 'Packing data updated',
		], 'Sales');
		$SqlLogInsert="INSERT INTO `ScrmPackingDataLog`(`PoID`,`LastUpdatedDate`, `Mcs`, `MaterialStatus`, `MailSentDate`, 
	 `ApprovalDate`, `OrderedDate`, `ReceivedDate`, `POGradeId`) 
	 VALUES ('$PoID','$LastUpdatedDate','$AvalMcs','$MaterialStatus','$MailSentDate','$ApprovalDate',
	 '$OrderedDate','$ReceivedDate','$POGradeId')"; 
   
  	 $StmtLogInsert=$iconn->query($SqlLogInsert); 
	
		
	 }
	 }
	 if($stmt){
		 $row['sts']='success';
		$row['msg']='Success';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed';
		 echo json_encode($row);
	 }
	 
	
}


?>
