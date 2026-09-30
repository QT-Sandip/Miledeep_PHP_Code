<?php
//require('fpdf/fpdf.php');
// include 'includes/session.php';

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$Month = $_GET['Month'];
$EmpID = $_GET['EmpID'];
$Year = $_GET['Year'];
$date_start = $Year . "-" . $Month . "-01";
$date_end = $Year . "-" . $Month . "-31 23:59:59";

$MonthDis = date('M-Y', strtotime($date_start));


$SqlHolidaysList = "SELECT * FROM `HrmHolidays` where Date between '$date_start' and '$date_end'";
$QueryHolidays = $iconn->query($SqlHolidaysList);
while ($HolidaysDetails = $QueryHolidays->fetch_array()) {
	$AryHolidays[] = $HolidaysDetails['Date'];
}


$SqlEmpList = "SELECT * FROM `HrmEmployeeDetails`  as HED 
left join HrmDepartments as HD on HED.DeptID=HD.DeptID 
left join HrmDesignations as HDG on HED.DesigID=HDG.DesigID
left join HrmContractorList HCL on HCL.ContractorID=HED.ContractorID

where  EmpID=$EmpID";
$query = $iconn->query($SqlEmpList);
$EmpDetails = $query->fetch_array();
$BioMetricID = $EmpDetails['BioMetricID'];

echo '<h1 align="center">  </h1>';
echo '<h3 >Employee Time Card - ' . $MonthDis . '</h3>';

echo '<table style="width: 100%" border="1" cellspacing="0" cellpadding="6">
   <thead>
    <th>NAME</th>
    <th>BIO METRIC ID</th>
    <th>MONTH</th>
    <th>DEPARTMENT</th>
    <th>DESIGNATION</th>
    <th>CONTRACTOR</th>
   </thead>
   <tbody>
    <tr>
	<td>' . $EmpDetails['EmpName'] . '</td>
	<td>' . $EmpDetails['BioMetricID'] . '</td>
	<td>' . $MonthDis . '</td>
	<td>' . $EmpDetails['DepartmentName'] . '</td>
	<td>' . $EmpDetails['Designation'] . '</td>
	<td>' . $EmpDetails['ContractorName'] . '</td>
	</tr>
   </tbody>
   </table>';

/*  

echo '<p><B>NAME           : </B>  '.$EmpDetails['EmpName'].'	 </p>'; 
echo '<p><B>BIO METRIC ID  : </B>'.$EmpDetails['BioMetricID'].'	 </p>'; 
echo '<p><B>MONTH          : </B> '.$MonthDis.'	 </p>'; 
echo '<p><B>DEPARTMENT     : </B> '.$MonthDis.'	 </p>'; 
echo '<p><B>DESIGNATION    : </B> '.$MonthDis.'	 </p>';  */

echo '<br>';
$data = '
	
	<table style="width: 100%" border="1" cellspacing="0" cellpadding="6">
   <thead>
                 <th>S.no</th>
                  <th>Date</th>
                <!--  <th>Bio ID</th> -->
                  <th>Time In</th>
                  <th>Time Out</th>
                  <th>Hours</th>
                  <th>Status</th>
               <!--  <th>Absent</th> 
                 <th>WeekOff</th> 
                 <th>Leave</th> -->
                </thead>
                <tbody id >';



$SqlEmpList = "select *,d.Date as Date,TIMESTAMPDIFF(MINUTE,t.TimeIn,t.TimeOut) as WorkMin from HrmMonthDays d
							left join ( SELECT * from HrmDailyAttendnessLog  WHERE Month(Date)='$Month' and Year(Date)='$Year'  
							and BioMetricID='$BioMetricID' GROUP by Date ) t on t.Date = d.Date
							WHERE d.Month='$Month' and d.Year='$Year' ORDER BY d.Date Asc";
/*
echo $SqlEmpList = "SELECT  Status,Shift,ShiftRotation, AttID,BreakTimeIn,BreakTimeOut, AttLog.BioMetricID, Date, TimeIn, 
 TimeOut, OnTime,
TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,AttLog.TimeOut) as WorkMin, 
TIMESTAMPDIFF(MINUTE,AttLog.BreakTimeOut,AttLog.BreakTimeIn) as BreakMin FROM HrmDailyAttendnessLog as AttLog
left join HrmShiftDetails as Hsd on Hsd.ShiftID=AttLog.Shift



where    AttLog.Date BETWEEN '$date_start' AND '$date_end'  and BioMetricID='$BioMetricID'
				   
				   ORDER BY AttLog.Date Asc,
				   AttLog.TimeIn Asc";
				   
				   */



$query = $iconn->query($SqlEmpList);
while ($row = $query->fetch_array()) {

	$BioMetricID = $row['BioMetricID'];
	$Status = $row['Status'];
	$BreakTimeIn = $row['BreakTimeIn'];
	$BreakTimeOut = $row['BreakTimeOut'];
	$ShiftName = $row['ShiftName'];
	$ShiftID = $row['Shift'];
	$ShiftRotation = $row['ShiftRotation'];
	$ShiftRotation = $row['NextDayOut'];

	$TimeIn = date('h:i A', strtotime($row['TimeIn']));
	$TimeOut = date('h:i A', strtotime($row['TimeOut']));


	$loginTime = strtotime($TimeIn);
	$loginOut = strtotime($TimeOut);


	if ($ShiftRotation == 1) {
		$row['TimeOut'];
		$NSFT_DATE = date('Y-m-d h:i A', strtotime($row['Date'] . $row['TimeOut'] . ' +1 day'));
		$NSPFT_DATE = date('Y-m-d h:i A', strtotime($row['Date'] . $row['TimeIn']));

		$NSFT_DATE = strtotime($NSFT_DATE);
		echo '<br>';
		$NSPFT_DATE = strtotime($NSPFT_DATE);

		round(abs($NSFT_DATE - $NSPFT_DATE) / 60, 2);
		$row['WorkMin'] = round(abs($NSPFT_DATE - $NSFT_DATE) / 60, 2);

	}


	if ($loginTime > $loginOut) {
		$row['TimeOut'];
		$NSFT_DATE = date('Y-m-d h:i A', strtotime($row['Date'] . $row['TimeOut'] . ' +1 day'));
		$NSPFT_DATE = date('Y-m-d h:i A', strtotime($row['Date'] . $row['TimeIn']));

		$NSFT_DATE = strtotime($NSFT_DATE); //echo '<br>'; uma
		$NSPFT_DATE = strtotime($NSPFT_DATE);

		round(abs($NSFT_DATE - $NSPFT_DATE) / 60, 2);
		$row['WorkMin'] = round(abs($NSPFT_DATE - $NSFT_DATE) / 60, 2);
	}

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
		$StatusName = 'PH-Pressent';
		$SatusCode = '1';
	}

	$StatusName = $StatusName . $PublicHoliday;


	if ($row['TimeIn'] == '00:00:00' || $row['TimeIn'] == null) {
		$TimeIn = 'NA';
		$diff = 'NA';
	}

	if ($row['TimeOut'] == '00:00:00' || $row['TimeOut'] == null) {
		$TimeOut = 'NA';
		$diff = 'NA';
	}

	if ($TimeIn == 'NA' && $TimeOut == 'NA') {
		$StatusName = 'Absent';
	}

	$Date = date('d-m-Y', strtotime($row['Date']));

	if ($row['TimeIn'] == null && $row['TimeOut'] == null) {
		$StatusName = 'NA';
		//$Date='NA';
	}





	$data .= "
                        <tr>
						<td>" . ++$Sno . "</td>
                          <td>" . $Date . "</td>
                         <!-- <td>" . $row['BioMetricID'] . "</td> -->
                           <td>" . $TimeIn . "</td>
                        
						<td>" . $TimeOut . "</td>
						<td>" . $diff . "</td>
						<td>" . $StatusName . "</td>
                        </tr>";


}

echo $data .= '</tbody></table>';



?>