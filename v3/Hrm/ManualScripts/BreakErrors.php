<?php

set_time_limit(0);


$days_ago = date('Y-m-d', strtotime('-3 days', strtotime('2021-09-04')));
include ($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");
include ($cp . "Includes/IncludePage.php");
$QueryParms = $_GET['Q'];
if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);
	$queries = array();
	parse_str($QueryParms, $_GET);
}

$CurrentMonth = $_GET['Month'];
$CurrentYear = $_GET['Year'];

$SqlLog = "truncate`HrmDailyAttendnessLogSalaryTemp` ";
$StmtLog = $iconn->query($SqlLog);

$SqlLog = "INSERT INTO `HrmDailyAttendnessLogSalaryTemp` 
 SELECT * FROM `HrmDailyAttendnessLog` WHERE month(Date)='$CurrentMonth' and year(Date)='$CurrentYear';";
$StmtLog = $iconn->query($SqlLog);


$SqlInPunchErrors = "SELECT *   FROM `HrmDailyAttendnessLog` as Log WHERE  
			month(Date)='$CurrentMonth' 
			and year(Date)='$CurrentYear' 
			and TimeIn !='00:00:00' 
			and TimeOut='00:00:00' and CStatus=0 
			ORDER BY BioMetricID ASC";

$StmtInPunchErrors = $iconn->query($SqlInPunchErrors);
$TotalReords = mysqli_num_rows($StmtInPunchErrors);
$Count = 0;

// echo '<br>';




while ($RowDDData = $StmtInPunchErrors->fetch_array()) {

	$DateBioID = $RowDDData['DateBioID'];

	$SqlTimeGap = "SELECT Max(TimeIn) as TimeOut ,TIMESTAMPDIFF(MINUTE,MIN(TimeIn),Max(TimeIn)) as MinTotal 
	  FROM `HrmDailyAttendnessLogSalaryTemp` WHERE DateBioID='$DateBioID'";
	$StmtSqlTimeGap = $iconn->query($SqlTimeGap);
	$RowTimeOut = $StmtSqlTimeGap->fetch_array();
	$MinTotal = $RowTimeOut['MinTotal'];
	$TimeOut = $RowTimeOut['TimeOut'];

	if ($MinTotal > 999) {
		$SqlTimeGap;
	}

	if ($MinTotal >= 240 && $MinTotal < 1001) {
		$SqlUpdate = "update `HrmDailyAttendnessLog` set TimeOut='$TimeOut' where DateBioID='$DateBioID' ";
		$StmtHmHolidays = $iconn->query($SqlUpdate);
		$TotalCount=++$Count;
		// echo '<br>';
	}



}

if($TotalCount>0){
	echo $TotalCount . ' Records Updated';
}
else {
		echo 'No data currently available to update';
	}


?>