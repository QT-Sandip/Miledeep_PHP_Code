<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$Month=$_GET['Month'];
$Year=$_GET['Year'];
$EmpID=$_GET['EmpID'];
$AttID=$_GET['D'];
$S=$_GET['S'];
  $Old_Status = legacyAuditFetchRowById('HrmAuditAttendnessAuditLog', 'AttID', $AttID, 'Hrm');
$SqlEmpList="UPDATE `HrmAuditAttendnessAuditLog` SET `Status` = '$S' WHERE `HrmAuditAttendnessAuditLog`.`AttID` = '$AttID';";
$StmtEmpList=$iconn->query($SqlEmpList);

if($StmtEmpList){
	legacyAuditLogUpdateById('HrmAuditAttendnessAuditLog', 'AttID', $AttID, $Old_Status, null, array(), 'Hrm');
header("Location: TimeCardPrintE?Month=".$Month."&Year=".$Year."&EmpID=".$EmpID);
}

?>
