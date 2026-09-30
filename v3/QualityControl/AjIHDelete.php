<?php
session_start();
include "../includes/config.php";


$TYPE=$_POST['TYPE'];
$D_ID=$_POST['D_ID'];
$REFERENCE_ID=$_POST['ref_id'];


if($TYPE=='IH_MB_REPORT'){
    $oldRow = legacyAuditFetchRowById('QcIHMBParmResult', 'ID', $D_ID, 'QualityControl');
    $sql="DELETE  FROM `QcIHMBParmResult` WHERE `ID`='$D_ID'";
	$stmt=$iconn->query($sql);
	if($stmt){
		legacyAuditLogDeleteById('QcIHMBParmResult', 'ID', $D_ID, $oldRow, $_POST, ['Remarks' => 'IH MB parm result deleted'], 'QualityControl');
	}
 } 

?>
