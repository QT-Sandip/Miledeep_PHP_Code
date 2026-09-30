<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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



/*echo $SqlHrmHolidays="SELECT BioMetricID,Date FROM `HrmDailyAttendnessLog` WHERE Shift=4 and CStatus=0 and BioMetricID='853'
 ORDER by BioMetricID asc,Date asc ";*/
$SqlHrmHolidays = "SELECT BioMetricID,Date FROM `HrmDailyAttendnessLog` WHERE Shift=4 and CStatus=0 and month(Date)='$CurrentMonth' 
 and year(Date)='$CurrentYear' and TimeOut='00:00:00'
 ORDER by BioMetricID asc,Date asc ";
$StmtHrmHolidays = $iconn->query($SqlHrmHolidays);
while ($RowAttData = $StmtHrmHolidays->fetch_array()) {

	$Date = $RowAttData['Date'];
	$NextDate = date('Y-m-d', (strtotime('+1 day', strtotime($Date))));
	$BioMetricID = $RowAttData['BioMetricID'];

	$SqlBk = "SELECT BioMetricID,Date, COALESCE(min(TimeIn),'0') as Time FROM `HrmDailyAttendnessBackUp`  
 WHERE BioMetricID='$BioMetricID' and Date='$NextDate' and Status=1";
	$StmtBk = $iconn->query($SqlBk);
	$RowData = $StmtBk->fetch_array();
	// echo $Time=$RowData['Time'];  echo '<br>';
	$Time = $RowData['Time'];
	;



	if ($Time != 0) {

		$SqlUpdate = "UPDATE HrmDailyAttendnessLog set TimeOut='$Time',CStatus=1,NextDayOut=1  WHERE BioMetricID='$BioMetricID' and Date='$Date'";
		$StmtHmHolidays = $iconn->query($SqlUpdate);

		//   echo '<br>';

		$SqlUpdate = "UPDATE HrmDailyAttendnessLog set TimeOut='0'  WHERE BioMetricID='$BioMetricID'
   and Date='$NextDate' and  TimeIn ='0'";
		// echo '<br>';
		$StmtHmHolidays = $iconn->query($SqlUpdate);
	}

}

/* C and B Update */

$SqlCShiftTimeDiff = "SELECT *,HOUR(TIMEDIFF(CONCAT(DATE_ADD(Date, INTERVAL 1 DAY), ' ', TimeOut)  ,CONCAT(Date, ' ', TimeIn))) as TimeDiff FROM `HrmDailyAttendnessLog`
 WHERE BioMetricID=715 and Shift =4 ORDER BY Date ASC;";
$StmtCShiftTimeDiff = $iconn->query($SqlCShiftTimeDiff);
$TotalRecords = mysqli_num_rows($StmtCShiftTimeDiff);
$Count = 0;
if ($TotalRecords > 0) {
	while ($RowData = $StmtCShiftTimeDiff->fetch_array()) {
		$TimeDiff = $RowData['TimeDiff'];

		if ($TimeDiff > 13) {

			$AttID = $RowData['AttID'];
			$BioMetricID = $RowData['BioMetricID'];
			$Date = $RowData['Date'];
			$NextDate = date('Y-m-d', (strtotime('+1 day', strtotime($Date))));

			$SqlNextDayData = "SELECT COALESCE(MIN(TimeIn),0) as TimeOut FROM `HrmDailyAttendnessBackUp` WHERE BioMetricID='$BioMetricID' 
							and Date='$NextDate' and Status=1;";
			$StmtNextDayData = $iconn->query($SqlNextDayData);
			$NextDayRowData = $StmtNextDayData->fetch_array();

			$TimeOut = $NextDayRowData['TimeOut'];

			$SqlUpdate = "update HrmDailyAttendnessLog set TimeOut='$TimeOut' where AttID=$AttID";
			$StmtUpdate = $iconn->query($SqlUpdate);

			if ($StmtUpdate) {
				++$Count;
				// echo $Count . '/' . $TotalupdtedRecords . ' Records Updated';
				echo $Count . ' Records Updated';
				echo '<br>';
			}

		}

	}
} else {
	echo 'No data currently available to update';
}
?>