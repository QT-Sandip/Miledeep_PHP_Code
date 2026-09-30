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

$sql = "
    SELECT
        id,
        frozen_by,
        frozen_at
    FROM salary_sheet_freeze
    WHERE
     salary_month = ?
      AND salary_year = ?

    LIMIT 1
";

$stmt = $iconn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $iconn->error);
}

$stmt->bind_param(
    "ii",
    $CurrentMonth,
    $CurrentYear
);

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();
$freeze = $result->fetch_assoc();

if ($freeze) {

    echo '<span class="label label-success">
            Already Frozen
          </span>';
    exit;

}

$stmt->close();


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
//exit;
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

  $SqlTimeDiff="SELECT
    BiometricID,
    COUNT(*) AS TotalPresents,

    (
        SELECT COUNT(*)
        FROM `HrmDailyAttendnessLogSalaryTemp`
        WHERE STATUS = 4 
          AND MONTH(Date) = '$CurrentMonth' 
          AND YEAR(Date) = '$CurrentYear' 
          AND BioMetricID = HlMlog.BioMetricID
    ) AS Leaves,

    (
        SELECT COUNT(*)
        FROM `HrmDailyAttendnessLogSalaryTemp`
        WHERE Minutes > 900 
          AND MONTH(Date) = '$CurrentMonth' 
          AND YEAR(Date) = '$CurrentYear' 
          AND BioMetricID = HlMlog.BioMetricID
    ) AS DoubleDuty,

    (
        SELECT COUNT(*)
        FROM `HrmDailyAttendnessLogSalaryTemp`
        WHERE Minutes BETWEEN 240 AND 420 
          AND MONTH(Date) = '$CurrentMonth' 
          AND YEAR(Date) = '$CurrentYear' 
          AND BioMetricID = HlMlog.BioMetricID
    ) AS HalfDay,

    (
        SELECT COUNT(*)
        FROM `HrmDailyAttendnessLogSalaryTemp`
        WHERE Minutes < 240 
          AND MONTH(Date) = '$CurrentMonth' 
          AND YEAR(Date) = '$CurrentYear' 
          AND BioMetricID = HlMlog.BioMetricID
    ) AS Absent,

    (
        SELECT COUNT(DISTINCT Date)
        FROM `HrmDailyAttendnessLogSalaryTemp`
        WHERE Minutes > 420 
          AND MONTH(Date) = '$CurrentMonth' 
          AND YEAR(Date) = '$CurrentYear' 
          AND BioMetricID = HlMlog.BioMetricID
    ) AS SingleDuty

FROM
    `HrmDailyAttendnessLogSalaryTemp` AS HlMlog
WHERE
    MONTH(Date) = '$CurrentMonth' 
    AND YEAR(Date) = '$CurrentYear'
GROUP BY
    BiometricID
ORDER BY
    BiometricID ASC;
";

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

	  $SqlLeaves="SELECT count(*) as Leaves FROM `HrmLeaveRegister`  
	 WHERE Month(StartDate)='$CurrentMonth' and Year(StartDate)='$CurrentYear' AND EmpID=(SELECT EmpID FROM HrmEmployeeDetails WHERE BiometricID='$BioMetricID')";
	 $StmtLeaves=$iconn->query($SqlLeaves);
	 $RowLeavesData=$StmtLeaves->fetch_array();
	 $LeavesCount=$RowLeavesData['Leaves'];
	 $Leaves=$LeavesCount; //Overriding here 


	 $Holidays=$HolidayCount; // Single Query Form HrmHolidays Table
	 $Total=$RowTimeData['TotalPresents'];
	 
	 
	//echo $DaysWithoutWeekOffs=$SingleDuty+($DoubleDuty * 2)+($HalfDay/2)+$Holidays;
	echo $DaysWithoutWeekOffs=$SingleDuty+($HalfDay/2);
	 
	  $WeekOff=floor($DaysWithoutWeekOffs/6);
	  
	  if($DaysWithoutWeekOffs >=$ReqWorkingDays){
			$WeekOff=$MaxWeekOffs;
		}

	 $WeekOff = CalculateWeekoffs(floor($DaysWithoutWeekOffs));  
	 
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
	 $Others=$DaysWithoutWeekOffs;
	 $SinglePunches=0;
 
  $SqlTempArray[]="(
 '$Month','$Year','$BioMetricID','$SingleDuty','$DoubleDuty','$WeekOff','$HalfDay','$Absent',
 '$ShiftOff','$Leaves','$Holidays','$Total','$ExcessDaysInMonth','$MessInDays','$PF','$ESI',
 '$TDS','$PT','$Adavance','$Arrears','$WageDays','$Others','$SinglePunches','$ReportSubCategory')";
}


$SqlTempValues=implode(',',$SqlTempArray);

  $SqlTimeUpdate="INSERT INTO `HrmMonthlySalaryDetails`(`Month`, `Year`, `BioMetricID`, 
 `SingleDuty`, `DoubleDuty`, `WeekOff`, `HalfDay`, `Absent`, `ShiftOff`, `Leaves`, 
 `Holidays`, `Total`, `ExcessDaysInMonth`, `MessInDays`, `PF`, `ESI`, `TDS`, `PT`,
 `Adavance`, `Arrears`, `WageDays`, `Others`, `SinglePunches`,`ReportType`) VALUES $SqlTempValues ";



 $StmtTimeUpdate=$iconn->query($SqlTimeUpdate);
 
 /*** comment above for just update**/ 
 
/*
  ( SELECT SUM((CurrentPending *Installments)/100) as AdvAmount   
  FROM `HrmCashAdvance` WHERE EmpID=HED.EmpID) as AdvAmount, */
 
  $SqlSalaryDetails="SELECT SalaryID,Total,HESD.*,HED.EmpID,HEPD.PFNumber,HEPD.EsiNumber,HEPD.MessChargePerDay,HEPD.PFPerc,
 (select NoOfShortDays from HrmMonthlyShortDaysList where Year='$CurrentYear' and Month='$CurrentMonth' 
   and EmpID=HED.EmpID) as NoOfShortDays,HEPD.MonthlyDeduction,HEDD.PF,HEDD.ESI,HEDD.Advance,HEDD.MessCharges,HEDD.BusChargeDeductions,HEDD.LateLoginFine,HEDD.DessCodeDeductions,HEDD.HelmetDeductions,HEADDI.ArrearAmount,HEADDI.ShipmentCharges FROM `HrmMonthlySalaryDetails` as HMSD 
 LEFT JOIN HrmEmployeeDetails as HED on HMSD.BioMetricID=HED.BioMetricID 
 LEFT JOIN HrmEmpSalaryDetails as HESD on HESD.EmpID=HED.EmpID 
 LEFT JOIN EmpDeductionDetails as HEPD on HEPD.EmpID=HED.EmpID
 LEFT JOIN HrmEmpDeductionDetails as HEDD on HEDD.EmpID=HED.EmpID 
 AND HEDD.Year='$CurrentYear' AND HEDD.Month='$CurrentMonth'
 LEFT JOIN HrmEmpAdditionDetails as HEADDI on HEADDI.EmpID=HED.EmpID 
 AND HEADDI.Year='$CurrentYear' AND HEADDI.Month='$CurrentMonth'
 WHERE HMSD.Month='$CurrentMonth' and HMSD.Year='$CurrentYear' 	 AND 	HMSD.ReportType=1  ORDER By HMSD.BioMetricID ASC";
 
 $StmtSalaryDetails=$iconn->query($SqlSalaryDetails);
 

 $SqlTempArray=array();

 while($RowSalaryDetails=$StmtSalaryDetails->fetch_array()){
	$EmpID=$RowSalaryDetails['EmpID'];
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
	$MessCharges=$RowSalaryDetails['MessCharges'];
	$LateLoginFine=$RowSalaryDetails['LateLoginFine'];
	$HelmetFine=$RowSalaryDetails['HelmetDeductions'];
	//Additions 
	$ArrearAmount=$RowSalaryDetails['ArrearAmount'];
	$ShipmentCharges=$RowSalaryDetails['ShipmentCharges'];	
	
	$TotalSalary=$Basic+$HRA+$DailyAllowance+$SpecialAllowance+$MedicalAllowance+$Conveyence+$Others+$Incentives;
	
	if($PFNO !='' && $PFNO!='0'){
		$PFAmount=round($Basic*0.12);
		$PFAmount=$RowSalaryDetails['PFPerc'];
	}else{
		$PFAmount=0;
		$PFAmount=$RowSalaryDetails['PFPerc'];
	}
	$PFAmount=$RowSalaryDetails['PF'];	

	if($ESICNo !='' && $ESICNo!='0'){
		$ESIAmount=round($TotalSalary*0.0075);
		$ESIAmount=$RowSalaryDetails['ESI'];
	}else{
		$ESIAmount=0;
		$ESIAmount=$RowSalaryDetails['ESI'];
	}
	$ESIAmount=$RowSalaryDetails['ESI'];	
	$Advance=$RowSalaryDetails['Advance'];

 $SqlTempArray[]="('$SalaryID','$TotalSalary','$PFAmount','$ESIAmount','$Advance','$MessCharges','$LateLoginFine','$HelmetFine','$ArrearAmount','$ShipmentCharges','$ReportSubCategory')";		 

 }
 
  $SqlTempValues=implode(',',$SqlTempArray);
  
  $SqlMonthlysalaryUpdate="INSERT into `HrmMonthlySalaryDetails` (SalaryID,EarnedSalaryWithoutDud,
		PF,ESI,Adavance,MessInDays,LateLoginFine,HelmetFine,ArrearAmount,ShipmentCharges,ReportType)
		VALUES $SqlTempValues ON DUPLICATE KEY UPDATE 
	EarnedSalaryWithoutDud=VALUES(EarnedSalaryWithoutDud),
	PF=VALUES(PF),
	ESI=VALUES(ESI),
	Adavance=VALUES(Adavance),
	MessInDays=VALUES(MessInDays),
	LateLoginFine=VALUES(LateLoginFine),
	HelmetFine=VALUES(HelmetFine),
	ArrearAmount=VALUES(ArrearAmount),
	ShipmentCharges=VALUES(ShipmentCharges),
	ReportType=VALUES(ReportType)";
	
	$StmtMonthlysalaryUpdate=$iconn->query($SqlMonthlysalaryUpdate);
	
	

?>
