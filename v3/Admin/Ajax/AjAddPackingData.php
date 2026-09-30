<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$AvalMcs=$_POST['AvalMcs'];
$MailSentDate=$_POST['MailSentDate'];
$ApprovalDate=$_POST['ApprovalDate'];
$MaterialStatus=$_POST['MaterialStatus'];
$OrderedDate=$_POST['OrderedDate'];
$ReceivedDate=$_POST['ReceivedDate'];

$Type=$_POST['Type'];
$POID=$_POST['POID'];

$SqlProduction="SELECT * FROM `$ScrmPackingData` WHERE PIID='$POID'"; 
  
  	 $StmtProduction=$iconn->query($SqlProduction);
	 /*$RowProduction=$StmtProduction->fetch_array();
	 $EmpID=$RowProduction['EmpID'];*/ 
$LastUpdatedDate=date('Y-m-d');
	 
IF($Type=='AddPackingData'){
	

      
	 $Count=mysqli_num_rows($StmtProduction);
	 if($Count==0){
		 if($AvalMcs>=100){
	 $sql="INSERT INTO `$ScrmPackingData`(`PIID`, `LastUpdatedDate`, `AvalMcs`, `MaterialStatus`, `MailSentDate`, 
	 `ApprovalDate`, `OrderedDate`, `ReceivedDate`) 
	 VALUES ('$POID','$LastUpdatedDate','$AvalMcs','$MaterialStatus','$MailSentDate','$ApprovalDate','$OrderedDate','$ReceivedDate')"; 
   
   $stmt=$iconn->query($sql);
	 legacyAuditLogInsertById($ScrmPackingData, 'ID', $iconn->insert_id, [], [], 'Admin');
	 
	 }
	 /*else{
		$row['sts']='Failed';
		$row['msg']='Failed';
		 echo json_encode($row);
	 }*/
	 
	 
	 }
	 else{
	 	 
		 
				 if($AvalMcs>=100){
 
	 $oldPackingRow = legacyAuditFetchRowById($ScrmPackingData, 'PIID', $POID, 'Admin');
	 $sql="UPDATE `$ScrmPackingData` SET `LastUpdatedDate`='$LastUpdatedDate',`AvalMcs`='$AvalMcs',
	 `MaterialStatus`='$MaterialStatus',`MailSentDate`='$MailSentDate',`ApprovalDate`='$ApprovalDate',
	 `OrderedDate`='$OrderedDate',`ReceivedDate`='$ReceivedDate'
	 WHERE `PIID`='$POID'"; 
   
  	 $stmt=$iconn->query($sql);
	 legacyAuditLogUpdateById($ScrmPackingData, 'PIID', $POID, $oldPackingRow, [], [], 'Admin');
	 if($stmt){
		$SqlLogInsert="INSERT INTO `$ScrmPackingDataLog`(`PIID`, `LastUpdatedDate`, `Mcs`, `MaterialStatus`, `MailSentDate`, 
	 `ApprovalDate`, `OrderedDate`, `ReceivedDate`) 
	 VALUES ('$POID','$LastUpdatedDate','$AvalMcs','$MaterialStatus','$MailSentDate','$ApprovalDate',
	 '$OrderedDate','$ReceivedDate')"; 
   
  	 $StmtLogInsert=$iconn->query($SqlLogInsert); 
	 }
				 }	 
	 }
	 if($stmt){
		 $row['sts']='success';
		$row['msg']=' Success';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed';
		 echo json_encode($row);
	 }
	 
	
}
?>
