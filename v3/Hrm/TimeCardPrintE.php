<?php
//require('fpdf/fpdf.php');
// include 'includes/session.php';

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$Month = $_GET['Month'];
$EmpID = $_GET['EmpID'];
$Year = $_GET['Year'];
include 'ManualScripts/ScriptRemoveDuplicateAuditAttendance.php';
$date_start = $Year . "-" . $Month . "-01";
$date_end = $Year . "-" . $Month . "-31 23:59:59";

$MonthDis = date('M-Y', strtotime($date_start));

$DateFilter = "BETWEEN '$date_start' AND '$date_end'";


$SqlHolidaysList = "SELECT * FROM `HrmHolidays` where Date $DateFilter";
$QueryHolidays = $iconn->query($SqlHolidaysList);
while ($HolidaysDetails = $QueryHolidays->fetch_array()) {
	$AryHolidays[] = $HolidaysDetails['Date'];
}


$SqlEmpList = "SELECT * FROM `HrmAuditEmployeeDetails` where EmpID=$EmpID";
$query = $iconn->query($SqlEmpList);
$EmpDetails = $query->fetch_array();
$BioMetricID = $EmpDetails['BioMetricID'];

//echo '<h1> ??? </h1>';

echo '<p><B>Name           : </B>  ' . $EmpDetails['EmpName'] . '	 </p>';
echo '<p><B>Bio Metric ID  : </B>' . $EmpDetails['BioMetricID'] . '	 </p>';
echo '<p><B>Month          : </B> ' . $MonthDis . '	 </p>';
$data = '
	
	<table border="1" cellspacing="0" cellpadding="6">
   <thead>
                 
                  <th>Date</th>
                  <th>Bio ID</th>
                  <th>Time In</th>
                  <th>TIme Out</th>
                  <th>Break</th>
                  <th>Time In</th>
                  <th>Time Out</th>
                  <th>Hours</th>
                 <th>Absent</th> 
                 <th>WeekOff</th> 
                 <th>Leave</th> 
                 <th>C-OFF</th> 
                </thead>
                <tbody id >';


if ($SubDeptID != '') {
	$SubQuery = "and EmpList.SubDeptID='$SubDeptID'";
} else if ($DeptID != '') {
	$SubQuery = "and Dept.DeptID='$DeptID'";
} else {
	$SubQuery = "";
}



/*$SqlEmpList = "SELECT Status, AttID,BreakTimeIn,BreakTimeOut, BioMetricID, Date,
TimeIn, TimeOut,GraceTime, OnTime,StartTime,ShiftName, 
TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,AttLog.TimeOut) as WorkMin,
TIMESTAMPDIFF(MINUTE,AttLog.BreakTimeOut,AttLog.BreakTimeIn) as BreakMin, 
(SELECT MIN(ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmShiftDetails ) as Diff FROM HrmAuditAttendnessAuditLog as AttLog 
LEFT JOIN HrmShiftDetails as ShiftDtl on 
ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,ShiftDtl.StartTime))=(SELECT MIN( ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmShiftDetails ) 
where AttLog.Date $DateFilter and  
BioMetricID='$BioMetricID' $SubQuery ORDER BY AttLog.Date Asc, AttLog.TimeIn Asc;";*/
 $SqlEmpList = "SELECT Status, AttID,BreakTimeIn,BreakTimeOut, BioMetricID, Date,
 TimeIn, TimeOut,GraceTime, OnTime,StartTime,ShiftName, 
 TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,AttLog.TimeOut) as WorkMin,
 TIMESTAMPDIFF(MINUTE,AttLog.BreakTimeOut,AttLog.BreakTimeIn) as BreakMin, 
(SELECT MIN(ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmAuditShiftDetails ) as Diff FROM HrmAuditAttendnessAuditLog as AttLog 
LEFT JOIN HrmAuditShiftDetails as ShiftDtl on 
ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,ShiftDtl.StartTime))=(SELECT MIN( ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmAuditShiftDetails ) 
 where AttLog.Date $DateFilter and  
BioMetricID='$BioMetricID' $SubQuery ORDER BY AttLog.Date Asc, AttLog.TimeIn Asc;";



$query = $iconn->query($SqlEmpList);
while ($row = $query->fetch_array()) {

	$BioMetricID = $row['BioMetricID'];
	$Status = $row['Status'];
	$BreakTimeIn = $row['BreakTimeIn'];
	$BreakTimeOut = $row['BreakTimeOut'];
	$ShiftName = $row['ShiftName'];

	$TimeIn = date('h:i A', strtotime($row['TimeIn']));
	$TimeOut = date('h:i A', strtotime($row['TimeOut']));

	$loginTime = strtotime($TimeIn);
	$loginOut = strtotime($TimeOut);

	$diff = $row['WorkMin'] - $row['BreakMin'];
	$diff = $row['WorkMin'];
	$Work_Hours = (int) ($diff / 60);
	$Work_Min = $diff % 60;

	$diff = $diff / (60);

	if ($diff > 0) {
		$ot = $diff - 8;
	} else {
		$ot = 0;
	}

	$diff = round($diff, 2);
	$ot = round($ot, 2);
	$ot = $ot * 60;

	$Ot_Hours = (int) ($ot / 60);
	$Ot_Min = $ot % 60;


	$diff = $Work_Hours . ' H : ' . $Work_Min . ' M';
	$ot = $Ot_Hours . ' H : ' . $Ot_Min . ' M';

	// echo $row['Date'];
	if (in_array($row['Date'], $AryHolidays, true)) {
		$PublicHoliday = '- Public Holiday';
	} else {
		$PublicHoliday = '';
	}



	$StatusName = '';
	$SatusCode = '1';

	if ($Status == 3) {
		$StatusName = 'Week Off';
		$SatusCode = '';
	} else if ($Status == 2) {
		$StatusName = 'Absent';
		$SatusCode = '';
	} else if ($Status == 4) {
		$StatusName = 'Leave';
		$SatusCode = '';
	} else if ($Status == 5) {
		$StatusName = 'Compensatory-Off';
		$SatusCode = '';
	} else if ($Status == 6) {
		$StatusName = 'PH-Present';
		$SatusCode = '1';
	}

	$StatusName = $StatusName . $PublicHoliday;



	//if($Status >1){
	if ($Status > 1 && $Status < 6) {


		$data .= "<tr>
                          <td>" . date('d-m-Y', strtotime($row['Date'])) . "</td>
                          <td>" . $row['BioMetricID'] . "</td>
                           
						<td></td>
						<td></td>
						<td><b>" . $StatusName . "</b></td>
						<td></td>
						<td></td>
						<td></td>";

		$data .= '<td><a href="Status?D=' . $row['AttID'] . '&S=1&Month=' . $Month . '&Year=' . $Year . '&EmpID=' . $EmpID . '" >P</a></td>
                        </tr>';



	}
	//else if(in_array($row['Date'],$AryHolidays,true)){
	else if (in_array($row['Date'], $AryHolidays, true) && $Status == 6) {
		$data .= "<tr>
                          <td>" . date('d-m-Y', strtotime($row['Date'])) . "</td>
                          <td>" . $row['BioMetricID'] . "</td>
                           
						  <td>" . $TimeIn . "</td>
                          <td>" . $BreakTimeOut . "</td>
						<td><b>" . $StatusName . "</b></td>
						<td>" . $BreakTimeIn . ' ' . $status . "</td>
						<td>" . $TimeOut . "</td>
						<td>" . $diff . "</td>";
		$data .= '
						 
						<td><a href="Status?D=' . $row['AttID'] . '&S=1&Month=' . $Month . '&Year=' . $Year . '&EmpID=' . $EmpID . '" >P</a></td>
                        </tr>';
	} else if (in_array($row['Date'], $AryHolidays, true)) {
		$data .= "<tr>
                          <td>" . date('d-m-Y', strtotime($row['Date'])) . "</td>
                          <td>" . $row['BioMetricID'] . "</td>
                           
						<td></td>
						<td></td>
						<td><b>" . $StatusName . "</b></td>
						<td></td>
						<td></td>
						<td></td>";
		$data .= '
						 
						  <td>
						  <a  href="Status?D=' . $row['AttID'] . '&S=6&Month=' . $Month . '&Year=' . $Year . '&EmpID=' . $EmpID . '" >PH-Present
						 </a>
						 </td>
						 
						 <td>
						  <a  href="Status?D=' . $row['AttID'] . '&S=3&Month=' . $Month . '&Year=' . $Year . '&EmpID=' . $EmpID . '" >W
						 </a>
						 </td>
                        </tr>';
	} else {


		$data .= "
                        <tr>
                          <td>" . date('d-m-Y', strtotime($row['Date'])) . "</td>
                          <td>" . $row['BioMetricID'] . "</td>
                           <td>" . $TimeIn . "</td>
                          <td>" . $BreakTimeOut . "</td>
                          <td>" . $row['BreakMin'] . " Min</td>
                          <td>" . $BreakTimeIn . ' ' . $status . "</td>
						<td>" . $TimeOut . "</td>
						<td>" . $diff . "</td>";
		$data .= '<td>
						<a  href="Status?D=' . $row['AttID'] . '&S=2&Month=' . $Month . '&Year=' . $Year . '&EmpID=' . $EmpID . '" >A
						 </a>
						 
						
						 </td>
						 
						 <td>
						  <a  href="Status?D=' . $row['AttID'] . '&S=3&Month=' . $Month . '&Year=' . $Year . '&EmpID=' . $EmpID . '" >W
						 </a>
						 </td>
						 
						 <td>
						  <a  href="Status?D=' . $row['AttID'] . '&S=4&Month=' . $Month . '&Year=' . $Year . '&EmpID=' . $EmpID . '" >L
						 </a>
						 </td>
						 <td>
						  <a  href="Status?D=' . $row['AttID'] . '&S=5&Month=' . $Month . '&Year=' . $Year . '&EmpID=' . $EmpID . '" >C-Off
						 </a>
						 </td>
						
						 
						 
						  
                         
                        </tr>'
		;
	}

}

echo $data .= '</tbody></table>';



?>