<?php
session_start();
include "../includes/config.php";

$GRADES=$_POST['GRADES'];
$RATES=$_POST['RATES'];
$WEIGHTS=$_POST['WEIGHTS'];


for($i=0; $i < count($GRADES) ; $i++ ){

$GRAD=$GRADES[$i];
$RATE=$RATES[$i];
$WEIGHT=$WEIGHTS[$i];

$oldRow = legacyAuditFetchRowById('PURCHASE_FINAL_GRADES', 'ID', $GRAD, 'Accounts');
$sql_org_insert="UPDATE  `PURCHASE_FINAL_GRADES` SET USD='$RATE',WEIGHT='$WEIGHT' WHERE ID='$GRAD'";
$stmt_uh=$iconn->query($sql_org_insert);
if($stmt_uh){
    legacyAuditLogUpdateById('PURCHASE_FINAL_GRADES', 'ID', $GRAD, $oldRow, array('USD' => $RATE, 'WEIGHT' => $WEIGHT), array(), 'Accounts');
}
}

?>
