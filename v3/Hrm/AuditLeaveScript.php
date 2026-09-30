<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

//echo 'Already Genereted';
//exit;

$SqlEmpList = "SELECT * FROM `jlsfauditleaves`";


$StmtEmpList = $iconn->query($SqlEmpList);


while ($DataRow = $StmtEmpList->fetch_array()) {
	echo $EmpCout++;


	$BioMetricID = $DataRow['BIO_ID'];
	$Year = $DataRow['YEAR'];
	$Month = $DataRow['MONTH'];
	$Leaves = $DataRow['LEAVES'];
	$Coffs = $DataRow['C_OFF'];
	$Absents = $DataRow['ABSENT'];

	if ($Absents != '') {

		$SqlAbsent = "update HrmAuditAttendnessAuditLog set Status=2 WHERE BioMetricID='$BioMetricID' 
	and Year(Date)='$Year' and Month(Date)='$Month' and Day(Date) in ($Absents)";
		$StmtAbsent = $iconn->query($SqlAbsent);
	}

	if ($Leaves != '') {
		$SqlLeaves = "update HrmAuditAttendnessAuditLog set Status=4 WHERE BioMetricID='$BioMetricID' 
	and Year(Date)='$Year' and Month(Date)='$Month' and Day(Date) in ($Leaves)";
		$StmtLeaves = $iconn->query($SqlLeaves);
	}

	if ($Coffs != '') {
		$SqlCoff = "update HrmAuditAttendnessAuditLog set Status=5 WHERE BioMetricID='$BioMetricID' 
	and Year(Date)='$Year' and Month(Date)='$Month' and Day(Date) in ($Coffs)";
		$StmtCoff = $iconn->query($SqlCoff);
	}
}

?>