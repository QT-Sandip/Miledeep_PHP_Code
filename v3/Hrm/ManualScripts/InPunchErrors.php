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


$SqlInPunchErrors = "SELECT *,
		(SELECT COUNT(*) from HrmDailyAttendnessBackUp atl 
		 WHERE atl.BioMetricID=Log.BioMetricID and atl.Date=Log.Date and atl.Status=1 ) as Rcount
		 FROM `HrmDailyAttendnessLog` as Log WHERE  
			month(Date)='$CurrentMonth' 
			and year(Date)='$CurrentYear' 
			and TimeIn='00:00:00' 
			and TimeOut!='00:00:00' 
			GROUP BY BioMetricID,Date
			HAVING Rcount > 1			
			ORDER BY BioMetricID ASC";

$StmtInPunchErrors = $iconn->query($SqlInPunchErrors);
$TotalRecords = mysqli_num_rows($StmtInPunchErrors);
$Count = 0;

// echo '<br>';

// if ($TotalRecords > 0) {

	while ($RowDDData = $StmtInPunchErrors->fetch_array()) {

		$DateBioID = $RowDDData['DateBioID'];

		$SqlTimeGap = "SELECT min(TimeIn) as TimeIn ,TIMESTAMPDIFF(MINUTE,MIN(TimeIn),Max(TimeIn)) as MinTotal 
	  FROM `HrmDailyAttendnessBackUp` WHERE DateBioID='$DateBioID' and Status=1";
		$StmtSqlTimeGap = $iconn->query($SqlTimeGap);
		$RowTimeOut = $StmtSqlTimeGap->fetch_array();
		$MinTotal = $RowTimeOut['MinTotal'];
		$TimeIn = $RowTimeOut['TimeIn'];

		if ($MinTotal > 999) {
			$SqlTimeGap;

		}

		if ($MinTotal >= 240 && $MinTotal < 1001) {

			 $SqlUpdate = "update `HrmDailyAttendnessLog` set TimeIn='$TimeIn' where DateBioID='$DateBioID' ";
			$StmtHmHolidays = $iconn->query($SqlUpdate);
			$TotalCount=++$Count;
			// if ($StmtHmHolidays) {
			// 	// $TotalCount=++$Count;
			// 	$TotalCount=3;
			// 	echo $Count . ' Records Updated';
			// 	echo '<br>';
			// }
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