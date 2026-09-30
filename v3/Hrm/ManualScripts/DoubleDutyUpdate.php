<?php

set_time_limit(0);


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


$SqlDoubleDuty = "SELECT DISTINCT(BioMetricID) FROM `HrmDailyAttendnessBackUp` 
	  WHERE  month(Date)='$CurrentMonth' and year(Date)='$CurrentYear' 
	  AND Status=0  GROUP BY BioMetricID,Date  HAVING COUNT(*) > 1 
	  ORDER BY BioMetricID ASC";
//   and BioMetricID=3
$StmtDoubleDuty = $iconn->query($SqlDoubleDuty);
$TotalReords = mysqli_num_rows($StmtDoubleDuty);
$Count = 0;

// echo '<br>';



while ($RowEmpData = $StmtDoubleDuty->fetch_array()) {

	$BioMetricID = $RowEmpData['BioMetricID'];

	$Date = $RowEmpData['Date'];

	$SqlDDTimes = "SELECT 
AttID, AttLog.BioMetricID,DateBioID, Date, TimeIn, TimeOut,DeviceID,ShiftDtl.ShiftID,ShiftName,ShiftRotation,
(SELECT COUNT(*) from HrmDailyAttendnessLog atl WHERE atl.BioMetricID=AttLog.BioMetricID 
and Date=AttLog.Date and TimeIn=AttLog.TimeIn) as recordStatus
FROM HrmDailyAttendnessBackUp as AttLog 
LEFT JOIN HrmShiftDetails as ShiftDtl 
on ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,ShiftDtl.StartTime))=  
(SELECT MIN( ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmShiftDetails ) 
 where AttLog.Shift='0' and Status=0 and BioMetricID='$BioMetricID'  and month(Date)='$CurrentMonth' 
 and year(Date)='$CurrentYear'  
 ORDER BY AttLog.Date Asc, AttLog.TimeIn Asc;";

	$StmtDDTimes = $iconn->query($SqlDDTimes);

	while ($RowDDData = $StmtDDTimes->fetch_array()) {

		// echo  $RowDDData['ShiftRotation']; exit;

		if ($RowDDData['recordStatus'] == 0 && $RowDDData['ShiftRotation'] == 1) {

			$Date = $RowDDData['Date'];
			$TimeIn = $RowDDData['TimeIn'];
			$NextDate = date('Y-m-d', (strtotime('+1 day', strtotime($Date))));
			$BioMetricID = $RowDDData['BioMetricID'];
			$DateBioID = $RowDDData['DateBioID'];
			$DeviceID = $RowDDData['DeviceID'];
			$Shift = $RowDDData['Shift'];

			$SqlBk = "SELECT BioMetricID,Date, COALESCE(min(TimeIn),'0') as Time FROM `HrmDailyAttendnessBackUp`  
				WHERE BioMetricID='$BioMetricID' and Date='$NextDate' and Status=1";
			$StmtBk = $iconn->query($SqlBk);
			$RowData = $StmtBk->fetch_array();
			// echo '<br>';

			$TimeOut = $RowData['Time'];

			if ($RowData['Time'] != 0) {


				$Time2 = $NextDate . " " . $TimeOut;
				$Time1 = $Date . " " . $TimeIn;

				$Time1 = strtotime($Time1);

				$Time2 = strtotime($Time2);
				// echo '=';
				$CalMinutes = round(abs($Time1 - $Time2) / 60, 2);
			}

			// echo '<br>';

			if ($TimeOut != 0 && $CalMinutes < 13) {

				$SqlTempArray[] = "('$BioMetricID','$DateBioID','$Date','$TimeIn','$TimeOut','$Shift','0','$DeviceID')";
				//  echo"hi";

			}

		}

	}
}

$SqlTempValues = implode(',', $SqlTempArray);
//    print_r($SqlTempValues);

 $SqlUpdate = "INSERT INTO `HrmDailyAttendnessLog` 
			(`BioMetricID`,`DateBioID`, `Date`, `TimeIn`, `TimeOut`,`Shift`,`Status`,`DeviceID`)
			VALUES  $SqlTempValues";
$StmtHmHolidays = $iconn->query($SqlUpdate);

// echo '<br>';

if ($StmtHmHolidays) {
	$TotalRecords = mysqli_num_rows($StmtHmHolidays);
	// $Count=0;
	echo $TotalRecords . ' Records Updated';
	echo '<br>';
} else {
	echo 'No data currently available to update';
}




?>