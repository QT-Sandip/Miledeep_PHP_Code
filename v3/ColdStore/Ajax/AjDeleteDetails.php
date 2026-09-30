<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$TYPE=$_POST['Type'];
$ODId=$_POST['ODId'];

  $oldGradeData = legacyAuditFetchRowById($CsPoGradeData, 'PoGradeID', $ODId, 'ColdStore');
  echo $sql="DELETE  FROM $CsPoGradeData WHERE `PoGradeID`='$ODId'";
	$stmt=$iconn->query($sql);
	if($stmt && $oldGradeData){
		legacyAuditLogDeleteById($CsPoGradeData, 'PoGradeID', $ODId, $oldGradeData, $_POST, ['Remarks' => 'PO grade data deleted'], 'ColdStore');
	}
	


?>
