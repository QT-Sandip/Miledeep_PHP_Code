<?php
ini_set('memory_limit', '-1');
set_time_limit(600); 
$days_ago = date('Y-m-d', strtotime('-3 days', strtotime('2021-09-04')));
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");
include ($cp."Includes/IncludePage.php");


 $QueryParms=$_GET['Q']; 
if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
  
  $CurrentMonth=$_GET['Month'];
  $CurrentYear=$_GET['Year'];
  $ReportSubCategory = (isset($_GET['ReportSubCategory']) && $_GET['ReportSubCategory']!='' ) ? $_GET['ReportSubCategory'] : 0 ;

  $DaysInMonth = cal_days_in_month( 0, $CurrentMonth, $CurrentYear);
  $MaxWeekOffs=$WorkingWeeks=floor($DaysInMonth/6);
  $ReqWorkingDays=$DaysInMonth-$MaxWeekOffs;
  
 $iconn->query("DELETE FROM HrmMonthlySalaryDetails WHERE Month='$CurrentMonth' 
 and  Year='$CurrentYear' AND ReportType='$ReportSubCategory';");
  $SqlLog= "SELECT count(*) as Total FROM HrmMonthlySalaryDetails WHERE Month='$CurrentMonth' 
 and  Year='$CurrentYear' AND ReportType='$ReportSubCategory';";  
 $StmtLog=$iconn->query($SqlLog);
 $RowLog=$StmtLog->fetch_array();
 
 
 echo $Total=$RowLog['Total'];
 if($Total > 0){
echo "Salary Already Generated";
exit;
 }
  
  $SqlLog= "truncate`HrmDailyAttendnessLogSalaryTemp` ";
  $StmtLog=$iconn->query($SqlLog);
  
   $SqlLog= "INSERT INTO `HrmDailyAttendnessLogSalaryTemp` 
 SELECT * FROM `HrmDailyAttendnessLog` WHERE month(Date)='$CurrentMonth' 
 and year(Date)='$CurrentYear';";
$StmtLog=$iconn->query($SqlLog);
 


 




  $SqlHolidays="SELECT count(*) as Holidays FROM `HrmHolidays`  
 WHERE Month(Date)='$CurrentMonth' and Year(Date)='$CurrentYear';";
 $StmtHolidays=$iconn->query($SqlHolidays);
 $RowHolidaysData=$StmtHolidays->fetch_array();
 
 
 
 $HolidayCount=$RowHolidaysData['Holidays'];
 
 
 /*comment start for update only**/

  $SqlTimeDiff="SELECT BiometricID,COUNT(*) as TotalPresents,
(SELECT COUNT(*) FROM `HrmDailyAttendnessLogSalaryTemp` WHERE Status =4    and month(Date)='$CurrentMonth' and year(Date)='$CurrentYear' 
 and BioMetricID=HlMlog.BioMetricID) as Leaves,
(SELECT COUNT(*)  FROM `HrmDailyAttendnessLogSalaryTemp` WHERE Minutes > 900  and month(Date)='$CurrentMonth' and year(Date)='$CurrentYear' 
 and BioMetricID=HlMlog.BioMetricID) as DoubleDuty,
 (SELECT COUNT(*)  FROM `HrmDailyAttendnessLogSalaryTemp` WHERE Minutes BETWEEN 240 and 450  and month(Date)='$CurrentMonth' and year(Date)='$CurrentYear' 
 and BioMetricID=HlMlog.BioMetricID) as HalfDay,
  (SELECT COUNT(*) FROM `HrmDailyAttendnessLogSalaryTemp` WHERE Minutes < 240   and month(Date)='$CurrentMonth' and year(Date)='$CurrentYear' 
 and BioMetricID=HlMlog.BioMetricID) as Absent,
 (SELECT COUNT(*) FROM `HrmDailyAttendnessLogSalaryTemp` WHERE Minutes BETWEEN 450 and 900 and month(Date)='$CurrentMonth' and year(Date)='$CurrentYear' and BioMetricID=HlMlog.BioMetricID) as SingleDuty
FROM `HrmDailyAttendnessLogSalaryTemp` as HlMlog WHERE  month(Date)='$CurrentMonth' 
and year(Date)='$CurrentYear' GROUP by BiometricId   ORDER BY BiometricId ASC"; 
 $StmtTimeDiff=$iconn->query($SqlTimeDiff);

 while($RowTimeData=$StmtTimeDiff->fetch_array()){
	 
	 
	 
	 
	 $Month=$CurrentMonth;
	 $Year=$CurrentYear;
	 $BioMetricID=$RowTimeData['BiometricID'];
	 $SingleDuty=$RowTimeData['SingleDuty'];
	 $DoubleDuty=$RowTimeData['DoubleDuty'];
	 $HalfDay=$RowTimeData['HalfDay'];
	 $Absent=$RowTimeData['Absent'];
	 $ShiftOff=0;  //Check Later 
	 $Leaves=$RowTimeData['Leaves']; // Calculate From Leaves Table
	 $Holidays=$HolidayCount; // Single Query Form HrmHolidays Table
	 $Total=$RowTimeData['TotalPresents'];
	 
	 
	echo $DaysWithoutWeekOffs=$SingleDuty+($DoubleDuty * 2)+($HalfDay/2)+$Holidays;
	 
	  $WeekOff=floor($DaysWithoutWeekOffs/6);
	  
	  if($DaysWithoutWeekOffs >=$ReqWorkingDays){
			$WeekOff=$MaxWeekOffs;
		}

 
	 
	 $EarnedPayDays=$DaysWithoutWeekOffs+$WeekOff;
	 
	 $ExcessDaysInMonth=$EarnedPayDays-$DaysInMonth;
	 
	 if($ExcessDaysInMonth > 0){
		$Absent=0;
	 }else{
		$Absent=abs($ExcessDaysInMonth);
	 }
	 
	
	 
	 
	 $Total=$DaysInMonth ;
	 //Based On WageDays Salary Calculated, full salary => $Total=$DaysInMonth,based
	//	 on working days $Total=$EarnedPayDays
	 $MessInDays=0;
	 $PF=0; 
	 $ESI=0;
	 $TDS=0;
	 $PT=0;
	 $Adavance=0;
	 $Arrears=0;
	 $WageDays=$EarnedPayDays;    
	 $Others=0;
	 $SinglePunches=0;
 
  $SqlTempArray[]="(
 '$Month','$Year','$BioMetricID','$SingleDuty','$DoubleDuty','$WeekOff','$HalfDay','$Absent',
 '$ShiftOff','$Leaves','$Holidays','$Total','$ExcessDaysInMonth','$MessInDays','$PF','$ESI',
 '$TDS','$PT','$Adavance','$Arrears','$WageDays','$Others','$SinglePunches'	)";
}


$SqlTempValues=implode(',',$SqlTempArray);

  $SqlTimeUpdate="INSERT INTO `HrmMonthlySalaryDetails`(`Month`, `Year`, `BioMetricID`, 
 `SingleDuty`, `DoubleDuty`, `WeekOff`, `HalfDay`, `Absent`, `ShiftOff`, `Leaves`, 
 `Holidays`, `Total`, `ExcessDaysInMonth`, `MessInDays`, `PF`, `ESI`, `TDS`, `PT`,
 `Adavance`, `Arrears`, `WageDays`, `Others`, `SinglePunches`) VALUES $SqlTempValues ";


 $StmtTimeUpdate=$iconn->query($SqlTimeUpdate);
 
 /*** comment above for just update**/ 
 
/*
  ( SELECT SUM((CurrentPending *Installments)/100) as AdvAmount   
  FROM `HrmCashAdvance` WHERE EmpID=HED.EmpID) as AdvAmount, */
 
  $SqlSalaryDetails="SELECT SalaryID,Total,HESD.*,HEPD.PFNumber,HEPD.EsiNumber,HEPD.ESI,HEPD.MessChargePerDay,HEPD.PFPerc,
 (select NoOfShortDays from HrmMonthlyShortDaysList where Year='$CurrentYear' and Month='$CurrentMonth' 
   and EmpID=HED.EmpID) as NoOfShortDays,
 HEPD.MonthlyDeduction FROM `HrmMonthlySalaryDetails` as HMSD 
 LEFT JOIN HrmEmployeeDetails as HED on HMSD.BioMetricID=HED.BioMetricID 
 LEFT JOIN HrmEmpSalaryDetails as HESD on HESD.EmpID=HED.EmpID 
 LEFT JOIN EmpDeductionDetails as HEPD on HEPD.EmpID=HED.EmpID 

 WHERE HMSD.Month='$CurrentMonth' and Year='$CurrentYear' AND HMSD.ReportType=0 ORDER By HMSD.BioMetricID ASC";
 
 $StmtSalaryDetails=$iconn->query($SqlSalaryDetails);
 

 $SqlTempArray=array();

 while($RowSalaryDetails=$StmtSalaryDetails->fetch_array()){
	$SalaryID=$RowSalaryDetails['SalaryID'];
	$TotalDays=$RowSalaryDetails['Total'];
	$PFNO=$RowSalaryDetails['PFNumber'];
	$ESICNo=$RowSalaryDetails['EsiNumber'];
	
	$Basic=$RowSalaryDetails['Basic'];
	$HRA=$RowSalaryDetails['HRA'];
	$DailyAllowance=$RowSalaryDetails['DailyAllowance'];
	$SpecialAllowance=$RowSalaryDetails['SpecialAllowance'];
	$MedicalAllowance=$RowSalaryDetails['MedicalAllowance'];
	$Conveyence=$RowSalaryDetails['Conveyence'];
	$Others=$RowSalaryDetails['Others'];
	$Incentives=$RowSalaryDetails['Incentives'];
	$AdvAmount=$RowSalaryDetails['AdvAmount'];
	$AdvAmount=0;
	$MessInDays=$RowSalaryDetails['MessChargePerDay'];
	
	$TotalSalary=$Basic+$HRA+$DailyAllowance+$SpecialAllowance+$MedicalAllowance+$Conveyence+$Others+$Incentives;
	
	if($PFNO !='' && $PFNO!='0'){
		$PFAmount=round($Basic*0.12);
		$PFAmount=$RowSalaryDetails['PFPerc'];
	}else{
		$PFAmount=0;
		$PFAmount=$RowSalaryDetails['PFPerc'];
	}
	
	if($ESICNo !='' && $ESICNo!='0'){
		$ESIAmount=round($TotalSalary*0.0075);
		$ESIAmount=$RowSalaryDetails['ESI'];
	}else{
		$ESIAmount=0;
		$ESIAmount=$RowSalaryDetails['ESI'];
	}
	

	
 $SqlTempArray[]="('$SalaryID','$TotalSalary','$PFAmount','$ESIAmount','$AdvAmount','$MessInDays','$ReportSubCategory')";
 

 }
 
  $SqlTempValues=implode(',',$SqlTempArray);
 
  $SqlMonthlysalaryUpdate="INSERT into `HrmMonthlySalaryDetails` (SalaryID,EarnedSalaryWithoutDud,
		PF,ESI,Adavance,MessInDays,ReportType)
		VALUES $SqlTempValues ON DUPLICATE KEY UPDATE 
	EarnedSalaryWithoutDud=VALUES(EarnedSalaryWithoutDud),
	PF=VALUES(PF),
	ESI=VALUES(ESI),
	Adavance=VALUES(Adavance),
	MessInDays=VALUES(MessInDays),
	MessInDays=VALUES(ReportType)";
	
	$StmtMonthlysalaryUpdate=$iconn->query($SqlMonthlysalaryUpdate);
	
	

?>