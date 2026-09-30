<?php
session_start();
include "../includes/config.php";


$STATUS=$_POST['STATUS'];
$INVOICE_ID=$_POST['INVOICE_ID'];


 $sql="UPDATE `INVOICE_DATA` SET `PO_STATUS`='$STATUS' WHERE `ID`='$INVOICE_ID'";
	$stmt=$iconn->query($sql);
	
	if($stmt){
		echo "DONE";
	}

?>