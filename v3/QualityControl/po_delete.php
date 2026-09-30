<?php
session_start();
include "../includes/config.php";


$STATUS=$_POST['STATUS'];
$INVOICE_ID=$_POST['INVOICE_ID'];

if($INVOICE_ID!=''){
  $oldRow = legacyAuditFetchRowById('INVOICE_DATA', 'ID', $INVOICE_ID, 'QualityControl');

  $sql="DELETE FROM `INVOICE_DATA` WHERE  ID='$INVOICE_ID'";
 $stmt=$iconn->query($sql);
	
	if($stmt){
		legacyAuditLogDeleteById('INVOICE_DATA', 'ID', $INVOICE_ID, $oldRow, ['Remarks' => 'PO Deleted'], 'QualityControl');
		echo "DONE";
	}
}else{
	echo "Failed To Delete Po";
}

?>
