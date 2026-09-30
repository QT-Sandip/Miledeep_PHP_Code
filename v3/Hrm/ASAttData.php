<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

echo 'Already Genereted';
//exit;

$SqlEmpList = "SELECT * FROM `HrmAuditEmployeeDetails` as HED 
LEFT JOIN HrmAuditBasicDetails ABD on HED.BioMetricID=ABD.BioMetricID 
order by ABD.BioMetricID asc";


$StmtEmpList = $iconn->query($SqlEmpList);


while ($DataRow = $StmtEmpList->fetch_array()) {
	echo $EmpCout++;
	//$AryValues=array();	
	$StartDate = $DataRow['AttStartDate'];
	$EndDate = $DataRow['AttEndDate'];
	$WeekOffStartDate = $DataRow['WeekOffStartDate'];
	$CurrentWeekOff = $DataRow['WeekOffStartDate'];
	$BioMetricID = $DataRow['BioMetricID'];
	$Shift = $DataRow['StartShift'];
	$ShiftStart = $DataRow['StartShift'];
	$ShiftOrder = $DataRow['ShiftOrder'];
	$ShiftOrder = explode(',', $ShiftOrder);
	$ShiftOrder = count($ShiftOrder);


	// End Shift AB =3 ABC=4
	if ($ShiftOrder == 1) {
		$ShiftEnd = $ShiftStart;
	} else if ($ShiftOrder == 2) {
		$ShiftEnd = 3;
	} else if ($ShiftOrder == 3) {
		$ShiftEnd = 4;
	} else {
		$ShiftEnd = $ShiftStart;
	}




	$date1 = date_create($StartDate);
	$date2 = date_create($EndDate);
	$diff = date_diff($date1, $date2);
	$maxDays = $diff->format("%a");

	for ($x = 0; $x <= $maxDays; $x++) {

		$Count++;

		$CurrentDate = date('Y-m-d', strtotime($StartDate . ' +' . $x . ' day'));


		if ($CurrentDate == $CurrentWeekOff) {
			$DayPlus = 7;
			$CurrentWeekOff = date('Y-m-d', strtotime($CurrentDate . ' +' . $DayPlus . ' day'));
			$Status = 3;
		} else {
			$Status = 1;
		}





		if ($Status == 3) {

			if ($Shift == 1) {
				$Shift = $ShiftStart;
			} else if ($Shift == $ShiftEnd) {
				$Shift = $ShiftStart;
			} else {
				$Shift++;
			}
		}

		$SqlShift = "SELECT * FROM `HrmAuditShiftDetails` WHERE ShiftID='$Shift'";
		$StmtSift = $iconn->query($SqlShift);
		$ShiftRow = $StmtSift->fetch_array();

		$StartTime = $ShiftRow['StartTime'];
		$EndTime = $ShiftRow['EndTime'];
		$BreakStartTime = $ShiftRow['BreakStartTime'];
		$BreakEndTime = $ShiftRow['BreakEndTime'];


		$StartTime1 = '2021-10-10 ' . $ShiftRow['StartTime'] . '-5 minute';
		$StartTime2 = '2021-10-10 ' . $ShiftRow['StartTime'] . '+5 minute';

		$EndTime1 = '2021-10-10 ' . $ShiftRow['EndTime'] . '-5 minute';
		$EndTime2 = '2021-10-10 ' . $ShiftRow['EndTime'] . '+5 minute';

		$BreakStartTime1 = '2021-10-10 ' . $ShiftRow['BreakStartTime'] . '-2 minute';
		$BreakStartTime2 = '2021-10-10 ' . $ShiftRow['BreakStartTime'] . '+2 minute';


		$BreakEndTime1 = '2021-10-10 ' . $ShiftRow['BreakEndTime'] . '-2 minute';
		$BreakEndTime2 = '2021-10-10 ' . $ShiftRow['BreakEndTime'] . '+2 minute';



		$LunchStartTime1 = '2021-10-10 ' . $ShiftRow['LunchTimeStart'] . '-2 minute';
		$LunchStartTime2 = '2021-10-10 ' . $ShiftRow['LunchTimeStart'] . '+2 minute';


		$LunchEndTime1 = '2021-10-10 ' . $ShiftRow['LunchTimeEnd'] . '-2 minute';
		$LunchEndTime2 = '2021-10-10 ' . $ShiftRow['LunchTimeEnd'] . '+2 minute';



		$unix_start = date("H:i:s", strtotime($StartTime1));
		$unix_end = date("H:i:s", strtotime($StartTime2));


		$unix_start = strtotime($unix_start);
		$unix_end = strtotime($unix_end);
		$diff = $unix_end - $unix_start;
		$rndtime = $unix_start + mt_rand(0, $diff);
		$TimeIn = date("H:i:s", $rndtime);


		$unix_start = date("H:i:s", strtotime($BreakStartTime1));
		$unix_end = date("H:i:s", strtotime($BreakStartTime2));


		$unix_start = strtotime($unix_start);
		$unix_end = strtotime($unix_end);
		$diff = $unix_end - $unix_start;
		$rndtime = $unix_start + mt_rand(0, $diff);
		$BreakTimeOut = date("H:i:s", $rndtime);





		$unix_start = date("H:i:s", strtotime($BreakEndTime1));
		$unix_end = date("H:i:s", strtotime($BreakEndTime2));


		$unix_start = strtotime($unix_start);
		$unix_end = strtotime($unix_end);
		$diff = $unix_end - $unix_start;
		$rndtime = $unix_start + mt_rand(0, $diff);
		$BreakTimeIn = date("H:i:s", $rndtime);


		$unix_start = date("H:i:s", strtotime($EndTime1));
		$unix_end = date("H:i:s", strtotime($EndTime2));




		$unix_start = strtotime($unix_start);
		$unix_end = strtotime($unix_end);
		$diff = $unix_end - $unix_start;
		$rndtime = $unix_start + mt_rand(0, $diff);
		$TimeOut = date("H:i:s", $rndtime);





		$unix_start = date("H:i:s", strtotime($LunchStartTime1));
		$unix_end = date("H:i:s", strtotime($LunchStartTime2));


		$unix_start = strtotime($unix_start);
		$unix_end = strtotime($unix_end);
		$diff = $unix_end - $unix_start;
		$rndtime = $unix_start + mt_rand(0, $diff);
		$LunchTimeStart = date("H:i:s", $rndtime);





		$unix_start = date("H:i:s", strtotime($LunchEndTime1));
		$unix_end = date("H:i:s", strtotime($LunchEndTime2));


		$unix_start = strtotime($unix_start);
		$unix_end = strtotime($unix_end);
		$diff = $unix_end - $unix_start;
		$rndtime = $unix_start + mt_rand(0, $diff);
		$LunchTimeEnd = date("H:i:s", $rndtime);



		// echo '<br>';

		$AryValues[] = "('$BioMetricID','$CurrentDate','$TimeIn','$Status','$TimeOut','1','$Shift','$BreakTimeIn','$BreakTimeOut',
	'$LunchTimeStart','$LunchTimeEnd')";

	}




}


$Values = '';
$Values = implode(',', $AryValues);
$SqlIs = "INSERT INTO `HrmAuditAttendnessAuditLog`(`BioMetricID`, `Date`, `TimeIn`, 
	`Status`, `TimeOut`, `OnTime`, `Shift`, `BreakTimeIn`, `BreakTimeOut`,`LunchTimeStart`,`LunchTimeEnd`) 
	VALUES $Values
	";

echo '<br>';

$StmtIs=$iconn->query($SqlIs);
if($StmtIs){
	legacyAuditLogInsertById('HrmAuditAttendnessAuditLog', 'AttID', $iconn->insert_id, null, array(), 'Hrm');
}


echo $Count;

?>
