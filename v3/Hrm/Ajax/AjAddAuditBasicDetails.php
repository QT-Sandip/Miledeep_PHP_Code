<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$AryWeekOffs=array();
$AryResDays=array();
$BioMetricID=$_POST['BioMetricID'];
$AttStartDate=$_POST['AttStartDate'];
$AttEndDate=$_POST['AttEndDate'];
$WeekOffStartDate=$_POST['WeekOffStartDate'];
$StartShift=$_POST['StartShift'];
$ShiftOrder=$_POST['ShiftOrder'];
$ShiftOrder=$_POST['ShiftOrder'];
$AttAbsents=$_POST['AttAbsents'];

$Month=$_POST['Month'];
$Year=$_POST['Year'];

$StartDateYearCheck=date("Y", strtotime($AttStartDate));
$StartDateMonthCheck=date("m", strtotime($AttStartDate));

$EndDateMonthCheck=date("m", strtotime($AttEndDate));
$EndDateYearCheck=date("Y", strtotime($AttEndDate));

if($StartDateYearCheck !=$Year || $StartDateMonthCheck!=$Month){
	$row['sts']='Failed';
	$row['msg']='Start Date Mismatch';
	echo json_encode($row);
	exit;
}

if($AttEndDate !='' && ($EndDateYearCheck !=$Year || $EndDateMonthCheck!=$Month)){
	$row['sts']='Failed';
	$row['msg']='End Date Mismatch '.$AttEndDate;
	echo json_encode($row);
	exit;
}


if($WeekOffStartDate==''){
	$row['sts']='Failed';
	$row['msg']='WeekOff Date Required';
	echo json_encode($row);
	exit;
}

/*
Previous Month WeekOff StartDate
PresentMonth Weekoff StartDate

$CurrentWeekOff=$WeekOffStartDate=getWeekOffStartDate($AttStartDate,$WeekOffStartDate);
	 
$SqlWeekOffQuery="SELECT Date,DATE(Date) + INTERVAL 7 DAY as NextWeakOffDate 
						FROM `HrmAuditAttendnessAuditLog` 
						WHERE BioMetricID='$BioMetricID' and Status=3 
						ORDER by Date DESC LIMIT 1";
	 $StmtWeekOffQuery=$iconn->query($SqlWeekOffQuery);

	 $DataWeekOffRow=$StmtWeekOffQuery->fetch_array();
	 $NextWeakOffDate=$DataWeekOffRow['NextWeakOffDate'];
	 if($CurrentWeekOff!=$NextWeakOffDate){
			$row['sts']='Failed';
		    $row['msg']='Weakoff  Mismatch with Previous Month';
			echo json_encode($row);
			exit; 
	 }

*/


$AttLeave=sprintf('%02d',$_POST['AttLeave']);

if($AttLeave!=0){
	$LeaveDate=date("Y-m", strtotime($AttStartDate));
$LeaveDate=$LeaveDate .'-'.$AttLeave;
}

$MonthEndDate= date("Y-m-t", strtotime($AttStartDate));


if($AttEndDate==''){
	
	$AttEndDate= date("Y-m-t", strtotime($AttStartDate));
}

if(strtotime($AttEndDate) > strtotime($MonthEndDate)){
	$AttEndDate=$MonthEndDate;
}

if($AttEndDate==$MonthEndDate){
$EmpAudStatus=1;
}else{
$EmpAudStatus=0;
}





$Type=$_POST['Type'];
 
$SqlProduction="SELECT * FROM `HrmAuditBasicDetails` WHERE BioMetricID='$BioMetricID'"; 
  
  	$StmtProduction=$iconn->query($SqlProduction);
	$LastUpdatedDate=date('Y-m-d');
	 
IF($Type=='AddAuditBasicDetails'){	 
	 $Count=mysqli_num_rows($StmtProduction);
	 if($Count==0){
	 $sql="INSERT INTO `HrmAuditBasicDetails`(`BioMetricID`, `AttStartDate`, `AttEndDate`, `WeekOffStartDate`,
	 `StartShift`, `ShiftOrder`,`EmpAudStatus`) 
	 VALUES ('$BioMetricID','$AttStartDate','$AttEndDate','$WeekOffStartDate','$StartShift','$ShiftOrder','$EmpAudStatus')"; 
	 }
	 else{
	 $oldBasicDetails = legacyAuditFetchRowById('HrmAuditBasicDetails', 'BioMetricID', $BioMetricID, 'Hrm');
	 $sql="UPDATE `HrmAuditBasicDetails` SET `AttStartDate`='$AttStartDate',
	 `AttEndDate`='$AttEndDate',`WeekOffStartDate`='$WeekOffStartDate',`StartShift`='$StartShift',
	 `EmpAudStatus`='$EmpAudStatus',
	 `ShiftOrder`='$ShiftOrder'
	  WHERE `BioMetricID`='$BioMetricID'"; 	 
	 }
	 
	 
	 $stmt=$iconn->query($sql);
	 
	// updateAttLog($BioMetricID);
	 
	 if($stmt){
		if($Count==0){
			legacyAuditLogInsertById('HrmAuditBasicDetails', 'BioMetricID', $BioMetricID, $_POST, ['BioMetricID' => $BioMetricID, 'Remarks' => 'Audit basic details added'], 'Hrm');
		}else{
			legacyAuditLogUpdateById('HrmAuditBasicDetails', 'BioMetricID', $BioMetricID, $oldBasicDetails, $_POST, ['BioMetricID' => $BioMetricID, 'Remarks' => 'Audit basic details updated'], 'Hrm');
		}
		 updateAttLog($BioMetricID);
	 }
	
	 
}

	 
function updateAttLog($BioMetricID){
	global $iconn,$AryResDays,$AryWeekOffs,$MonthEndDate;	 
		 
		 $SqlEmpList="SELECT * FROM `HrmAuditEmployeeDetails` as HED 
LEFT JOIN HrmAuditBasicDetails ABD on HED.BioMetricID=ABD.BioMetricID  where
HED.BioMetricID=$BioMetricID
order by ABD.BioMetricID asc"; 
$StmtEmpList=$iconn->query($SqlEmpList);

$DataRow=$StmtEmpList->fetch_array();
$StartDate=$DataRow['AttStartDate'];

	$SqlDeleteRecord="DELETE  from  HrmAuditAttendnessAuditLog 
				where BioMetricID='$BioMetricID' and Month(Date)=Month('$StartDate') 
				and Year(Date)=Year('$StartDate')";
	
	$oldAttendLogsDelete = legacyAuditFetchRowById('HrmAuditAttendnessAuditLog', 'BioMetricID', $BioMetricID, 'Hrm');
	$StmtDeleteRecord=$iconn->query($SqlDeleteRecord);
	if($StmtDeleteRecord){
		legacyAuditLogDeleteById('HrmAuditAttendnessAuditLog', 'BioMetricID', $BioMetricID, $oldAttendLogsDelete, $_POST, ['BioMetricID' => $BioMetricID, 'Remarks' => 'Attendance logs deleted before regeneration'], 'Hrm');
	}


$StmtEmpList=$iconn->query($SqlEmpList);


while($DataRow=$StmtEmpList->fetch_array()){
	 $EmpCout++;
	 
	$StartDate=$DataRow['AttStartDate'];
	$EndDate=$DataRow['AttEndDate'];
	$WeekOffStartDate=$DataRow['WeekOffStartDate'];
	$CurrentWeekOff=$DataRow['WeekOffStartDate'];
	$BioMetricID=$DataRow['BioMetricID'];
	$Shift=$DataRow['StartShift'];
	$ShiftIndex=0;// Array Start Index
	$ShiftStart=$DataRow['StartShift'];
	$ShiftOrder=$DataRow['ShiftOrder'];
	$AryShiftOrder=explode(',',$ShiftOrder);
	 $ShiftOrder="'" . implode("','",$AryShiftOrder) . "'";
	$ShiftCount=count($AryShiftOrder);
	
	 $SqlShift="SELECT * FROM `HrmAuditShiftDetails` 
	WHERE ShiftName in ($ShiftOrder) order by FIELD (ShiftName,$ShiftOrder)"; 
	$StmtShift=$iconn->query($SqlShift);
	$AryShiftID=array();
	while($ShiftOrderRow=$StmtShift->fetch_array()){
		$AryShiftID[]=$ShiftOrderRow['ShiftID'];		
	}
	$ShiftIndex=array_search($Shift, $AryShiftID);
	//print_r($AryShiftID);
	
	$date1=date_create($StartDate);
	$date2=date_create($EndDate);
	$diff=date_diff($date1,$date2);
	$maxDays=$diff->format("%a");
	
	 $CurrentWeekOff=$WeekOffStartDate=getWeekOffStartDate($StartDate,$CurrentWeekOff);
	 
	 
	
	 
	 
	for ($x = 0; $x <= $maxDays; $x++) { 
	
$Count++;
	
	 $CurrentDate = date('Y-m-d', strtotime($StartDate . ' +'.$x.' day'));
	
		
	if($CurrentDate==$CurrentWeekOff){
		$DayPlus=7;
		 $CurrentWeekOff = date('Y-m-d', strtotime($CurrentDate . ' +'.$DayPlus.' day'));
		$AryWeekOffs[]=$CurrentDate;
		$AryResDays[]=$CurrentDate;
		$Status=3;
	}else{
		$Status=1;
	}

	if($Status==3){
		$ShiftIndex++;	
	}
	
	if($ShiftIndex==$ShiftCount){
		$ShiftIndex=0;
	}
	
	
	$Shift=$AryShiftID[$ShiftIndex];
		
	$SqlShift="SELECT * FROM `HrmAuditShiftDetails` WHERE ShiftID='$Shift'";
    $StmtSift=$iconn->query($SqlShift);	
	$ShiftRow=$StmtSift->fetch_array();
	
	$StartTime=$ShiftRow['StartTime'];
	$EndTime=$ShiftRow['EndTime'];
	$BreakStartTime=$ShiftRow['BreakStartTime'];
	$BreakEndTime=$ShiftRow['BreakEndTime'];
	
	 
	 $StartTime1='2021-10-10 ' .$ShiftRow['StartTime'].'-5 minute';
	 $StartTime2='2021-10-10 ' .$ShiftRow['StartTime'].'+5 minute';
	 
	 $EndTime1='2021-10-10 ' .$ShiftRow['EndTime'].'-5 minute';
	 $EndTime2='2021-10-10 ' .$ShiftRow['EndTime'].'+5 minute';
	 
	 $BreakStartTime1='2021-10-10 ' .$ShiftRow['BreakStartTime'].'-2 minute';
	 $BreakStartTime2='2021-10-10 ' .$ShiftRow['BreakStartTime'].'+2 minute';
	 
	 
	 $BreakEndTime1='2021-10-10 ' .$ShiftRow['BreakEndTime'].'-2 minute';
	 $BreakEndTime2='2021-10-10 ' .$ShiftRow['BreakEndTime'].'+2 minute';
	 
	 
	 
	 $LunchStartTime1='2021-10-10 ' .$ShiftRow['LunchTimeStart'].'-2 minute';
	 $LunchStartTime2='2021-10-10 ' .$ShiftRow['LunchTimeStart'].'+2 minute';
	 
	 
	 $LunchEndTime1='2021-10-10 ' .$ShiftRow['LunchTimeEnd'].'-2 minute';
	 $LunchEndTime2='2021-10-10 ' .$ShiftRow['LunchTimeEnd'].'+2 minute';

	

 	$unix_start=date("H:i:s", strtotime($StartTime1));
	$unix_end=date("H:i:s", strtotime($StartTime2));
	
		
	$unix_start = strtotime($unix_start);
	$unix_end = strtotime($unix_end);
	$diff = $unix_end - $unix_start ;
	$rndtime =  $unix_start + mt_rand(0,$diff);
	$TimeIn= date("H:i:s",$rndtime);
	
	
	$unix_start=date("H:i:s", strtotime($BreakStartTime1));
	$unix_end=date("H:i:s", strtotime($BreakStartTime2));
	
	
	$unix_start = strtotime($unix_start);
	$unix_end = strtotime($unix_end);
	$diff = $unix_end - $unix_start ;
	$rndtime =  $unix_start + mt_rand(0,$diff);
	$BreakTimeOut= date("H:i:s",$rndtime);
	
	
		

	
	$unix_start=date("H:i:s", strtotime($BreakEndTime1));
	$unix_end=date("H:i:s", strtotime($BreakEndTime2));
	
	
	$unix_start = strtotime($unix_start);
	$unix_end = strtotime($unix_end);
	$diff = $unix_end - $unix_start ;
	$rndtime =  $unix_start + mt_rand(0,$diff);
	$BreakTimeIn= date("H:i:s",$rndtime);
	
	
		$unix_start=date("H:i:s", strtotime($EndTime1));
	$unix_end=date("H:i:s", strtotime($EndTime2));
	

	
	
	$unix_start = strtotime($unix_start);
	$unix_end = strtotime($unix_end);
	$diff = $unix_end - $unix_start ;
	$rndtime =  $unix_start + mt_rand(0,$diff);
	$TimeOut= date("H:i:s",$rndtime);
	
	
	
	
	
	$unix_start=date("H:i:s", strtotime($LunchStartTime1));
	$unix_end=date("H:i:s", strtotime($LunchStartTime2));
	
	
	$unix_start = strtotime($unix_start);
	$unix_end = strtotime($unix_end);
	$diff = $unix_end - $unix_start ;
	$rndtime =  $unix_start + mt_rand(0,$diff);
	$LunchTimeStart= date("H:i:s",$rndtime);
	
	
		

	
	$unix_start=date("H:i:s", strtotime($LunchEndTime1));
	$unix_end=date("H:i:s", strtotime($LunchEndTime2));
	
	
	$unix_start = strtotime($unix_start);
	$unix_end = strtotime($unix_end);
	$diff = $unix_end - $unix_start ;
	$rndtime =  $unix_start + mt_rand(0,$diff);
	$LunchTimeEnd= date("H:i:s",$rndtime);
	
	
	$SqlCheckRecord="select * from HrmAuditAttendnessAuditLog 
	where BioMetricID='$BioMetricID' and Date='$CurrentDate'";
	
	$StmtCheckRecord=$iconn->query($SqlCheckRecord);	 
	 $RecordCount=mysqli_num_rows($StmtCheckRecord);
	 if($RecordCount==0){
	$SqlIs="INSERT INTO `HrmAuditAttendnessAuditLog`(`BioMetricID`, `Date`, `TimeIn`, 
			`Status`, `TimeOut`, `OnTime`, `Shift`, `BreakTimeIn`, `BreakTimeOut`,
			`LunchTimeStart`,`LunchTimeEnd`) 
			VALUES ('$BioMetricID','$CurrentDate','$TimeIn','$Status','$TimeOut','1','$Shift','$BreakTimeIn','$BreakTimeOut',
					'$LunchTimeStart','$LunchTimeEnd')"; 
	 }else{
	$oldAttendLog = legacyAuditFetchRowById('HrmAuditAttendnessAuditLog', 'BioMetricID', $BioMetricID, 'Hrm');	 
	 $SqlIs="update `HrmAuditAttendnessAuditLog` 
		set TimeIn='$TimeIn',
		 Status='$Status',
		 BreakTimeIn='$BreakTimeIn',
		 BreakTimeOut='$BreakTimeOut',
		 LunchTimeStart='$LunchTimeStart',
		 LunchTimeEnd='$LunchTimeEnd',
		 TimeOut='$TimeOut'
		 where BioMetricID='$BioMetricID' and Date='$CurrentDate'";
		 
	 }
	 
	 //echo $SqlIs; exit;
	/*echo '<br>';*/
	$StmtIs=$iconn->query($SqlIs);
	if($StmtIs){
		if($RecordCount==0){
			legacyAuditLogInsertById('HrmAuditAttendnessAuditLog', 'BioMetricID', $BioMetricID, $_POST, ['BioMetricID' => $BioMetricID, 'Date' => $CurrentDate, 'Remarks' => 'Generated attendance record'], 'Hrm');
		}else{
			legacyAuditLogUpdateById('HrmAuditAttendnessAuditLog', 'BioMetricID', $BioMetricID, $oldAttendLog, $_POST, ['BioMetricID' => $BioMetricID, 'Date' => $CurrentDate, 'Remarks' => 'Updated generated attendance'], 'Hrm');
		}
	}
	
	}
	
	 $oldBasicUp = legacyAuditFetchRowById('HrmAuditBasicDetails', 'BioMetricID', $BioMetricID, 'Hrm');
	 $sqlAudUp="UPDATE `HrmAuditBasicDetails` SET `WeekOffStartDate`='$WeekOffStartDate',
	  `StartShift`='$Shift'
	 WHERE `BioMetricID`='$BioMetricID'";  
     $stmtsqlAudUp=$iconn->query($sqlAudUp);
	 if($stmtsqlAudUp){
		legacyAuditLogUpdateById('HrmAuditBasicDetails', 'BioMetricID', $BioMetricID, $oldBasicUp, $_POST, ['BioMetricID' => $BioMetricID, 'Remarks' => 'Updated shift/weekoff after attendance generation'], 'Hrm');
	 }
	

}




global $AttStartDate, $AttEndDate;
randomDater($AttStartDate, $AttEndDate);
}
	


function randomDater($AttStartDate, $AttEndDate){
	global $iconn,$LeaveDate,$AryWeekOffs,$AryResDays,$AttAbsents,$BioMetricID;
	
	//print_r($AryWeekOffs);
	//print_r($AryResDays);
   $SqlHolidaysList = "SELECT * FROM `HrmHolidays` where Date between '$AttStartDate' and '$AttEndDate'";
$QueryHolidays = $iconn->query($SqlHolidaysList);

	while($HolidaysDetails = $QueryHolidays->fetch_array()){
		
		$AryResDays[]=$HolidaysDetails['Date'];
		if(in_array($HolidaysDetails['Date'],$AryWeekOffs)){ 
			$AryCoffs[]=date('Y-m-d', strtotime($HolidaysDetails['Date'] . ' +1 day'));	
			$AryResDays[]=date('Y-m-d', strtotime($HolidaysDetails['Date'] . ' +1 day'));	
		}
		}
		
		//print_r($AryCoffs);

	while(in_array($LeaveDate,$AryWeekOffs)){
		  $LeaveDate=date('Y-m-d', strtotime($LeaveDate . ' +1 day'));
	}
	
	while(in_array($LeaveDate,$AryCoffs)){
		  $LeaveDate=date('Y-m-d', strtotime($LeaveDate . ' +1 day'));
	}

	$AryResDays[]=$LeaveDate;
	$AryAbsents=array();
	
	for($i=0; $i<$AttAbsents ; $i++){
		$isInValidAbsent=true;
		while($isInValidAbsent){
			 $AbsentDate=getAbsentDates($AttStartDate,$AttEndDate);
			if(!in_array($AbsentDate,$AryResDays) && 
			   !in_array($AbsentDate,$AryWeekOffs)&&
			   !in_array($AbsentDate,$AryAbsents)
			   ){
				$AryAbsents[]=$AbsentDate;
				$isInValidAbsent=false;
			}
			
		}
	}

/*	
echo "Leave";	print_r($AryResDays);
echo "WeakOff";	print_r($AryWeekOffs);
echo "Absent";	print_r($AryAbsents);
*/	
	

	 $Coffs=implode("','",$AryCoffs);
	$Coffs="'".$Coffs ."'";
	
	
	$Absents=implode("','",$AryAbsents);
	$Absents="'".$Absents ."'";
	
	if($Coffs!=''){
	$SqlCoff="update HrmAuditAttendnessAuditLog set Status=5 WHERE BioMetricID='$BioMetricID' 
	and Year(Date)='$Year' and Month(Date)='$Month' and Day(Date) in ($Coffs)";
	
    $SqlCoff="update HrmAuditAttendnessAuditLog set Status=5 WHERE BioMetricID='$BioMetricID' 
	and Date in ($Coffs)";
	$StmtCoff=$iconn->query($SqlCoff);
	if($StmtCoff){
		legacyAuditLogUpdateById('HrmAuditAttendnessAuditLog', 'BioMetricID', $BioMetricID, null, $_POST, ['BioMetricID' => $BioMetricID, 'Status' => '5 (Coff)', 'Dates' => $Coffs, 'Remarks' => 'Bulk Coff status update'], 'Hrm');
	}
	}


	if($Absents!=''){
	
	$SqlAbsent="update HrmAuditAttendnessAuditLog set Status=2 WHERE BioMetricID='$BioMetricID' 
	and Year(Date)='$Year' and Month(Date)='$Month' and Day(Date) in ($Absents)";
	
	 $SqlAbsent="update HrmAuditAttendnessAuditLog set Status=2 WHERE BioMetricID='$BioMetricID' 
	and Date in ($Absents)";
	$StmtAbsent=$iconn->query($SqlAbsent);
	if($StmtAbsent){
		legacyAuditLogUpdateById('HrmAuditAttendnessAuditLog', 'BioMetricID', $BioMetricID, null, $_POST, ['BioMetricID' => $BioMetricID, 'Status' => '2 (Absent)', 'Dates' => $Absents, 'Remarks' => 'Bulk absent status update'], 'Hrm');
	}
	}
	
	if($LeaveDate!=''){
	$SqlLeaves="update HrmAuditAttendnessAuditLog set Status=4 WHERE BioMetricID='$BioMetricID' 
	and Year(Date)='$Year' and Month(Date)='$Month' and Day(Date) in ($Leaves)";
	
	$SqlLeaves="update HrmAuditAttendnessAuditLog set Status=4 WHERE BioMetricID='$BioMetricID' 
	and Date='$LeaveDate'";
	$StmtLeaves=$iconn->query($SqlLeaves);
	if($StmtLeaves){
		legacyAuditLogUpdateById('HrmAuditAttendnessAuditLog', 'BioMetricID', $BioMetricID, null, $_POST, ['BioMetricID' => $BioMetricID, 'Status' => '4 (Leave)', 'Date' => $LeaveDate, 'Remarks' => 'Leave status update'], 'Hrm');
	}
	}
	
	
	 
	 if(1==1){
		 $row['sts']='success';
		$row['msg']='Success';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed';
		 echo json_encode($row);
	 }
	

}
	



function getAbsentDates($start_date, $end_date)
{
    // Convert to timetamps
    $min = strtotime($start_date);
    $max = strtotime($end_date);

    // Generate random number using above bounds
    $val = rand($min, $max);

    // Convert back to desired date format
    return date('Y-m-d', $val);
}


function getWeekOffStartDate($StartDate, $CurrentWeekOff)
{
	
	if($StartDate!=$CurrentWeekOff){
	$date1=date_create($StartDate);
	$WkDate1=date_create($CurrentWeekOff);
	$Wkdiff=date_diff($date1,$WkDate1);
	$WkmaxDays=$Wkdiff->format("%a");
	
	$DayPlus=7;
	
	 if(strtotime($StartDate) > strtotime($CurrentWeekOff)){

		for ($x = 0; $x <= $WkmaxDays; $x++) { 
		if(strtotime($StartDate) < strtotime($CurrentWeekOff . ' +'.$DayPlus.' day')){
		$CurrentWeekOff = date('Y-m-d', strtotime($CurrentWeekOff . ' +'.$DayPlus.' day'));
			break;
		}else{
			
		$CurrentWeekOff = date('Y-m-d', strtotime($CurrentWeekOff . ' +'.$DayPlus.' day'));		 
				
		}
	 }
	 }else{
		for ($x = 0; $x <= $WkmaxDays; $x++) {  
		if(strtotime($StartDate) >  strtotime($CurrentWeekOff . ' -'.$DayPlus.' day')){
			break;
		}else{
		     $CurrentWeekOff = date('Y-m-d', strtotime($CurrentWeekOff . ' -'.$DayPlus.' day'));		
		}		
	 }
	 }
	// echo "Hii";
	// echo $CurrentWeekOff;
	// echo $WeekOffStartDate;
	}
	 return $CurrentWeekOff;
}


?>
