<?php


// error_reporting(E_ALL);
// error_reporting(-1);
// ini_set('error_reporting', E_ALL);


//require('fpdf/fpdf.php');
// include 'includes/session.php';

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


/*
$Month=9;
$Year=2022;
for($i=0; $i< 3; $i++){
	$Month++;
	
	$SqlEmpList = "SELECT * FROM `HrmAuditEmployeeDetails`";
	$StmtEmpList = $iconn->query($SqlEmpList);
while($RowEmpList = $StmtEmpList->fetch_array()){
	$BioMetricID=$RowEmpList['BioMetricID'];
	
	
}
}
*/

$Month=$_GET['Month'];
$EmpID=$_GET['EmpID'];
$Year=$_GET['Year'];
include 'ManualScripts/ScriptRemoveDuplicateAuditAttendance.php';
updateAttLog($EmpID,$Month,$Year);

function updateAttLog($EmpID,$Month,$Year){
	
global $iconn;

/*
$Month=$_GET['Month'];
$EmpID=$_GET['EmpID'];
$Year=$_GET['Year'];*/
$date_start=$Year."-".$Month."-01";
$date_end=$Year."-".$Month."-31 23:59:59";

$MonthDis=date('M-Y', strtotime($date_start)); 


  $SqlHolidaysList = "SELECT * FROM `HrmHolidays` where Date between '$date_start' and '$date_end'"; 
$QueryHolidays = $iconn->query($SqlHolidaysList);
while($HolidaysDetails = $QueryHolidays->fetch_array()){
$AryHolidays[]=	$HolidaysDetails['Date'];
}


$SqlEmpList = "SELECT * FROM `HrmAuditEmployeeDetails` as ED 
left join  HrmDepartments as HD on HD.DeptID=ED.DeptID 
left join  HrmDesignations as HDE on HDE.DesigID=ED.DesigID 
left join  HrmBranchMaster as HBM on HBM.BranchID=ED.BranchID 
where EmpID=$EmpID";
$query = $iconn->query($SqlEmpList);
$EmpDetails = $query->fetch_array();
$BioMetricID=$EmpDetails['BioMetricID'];
$BranchName=$EmpDetails['BranchName'];

echo '<h1> '.$BranchName.'</h1>'; 

echo '<p><B>Name           : </B>  '.$EmpDetails['EmpName'].'	
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    
  <B>Designation : </B>'.$EmpDetails['Designation'].' </p>'; 
echo '<p><B>Employee ID  : </B>'.$EmpDetails['BioMetricID'].' 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    
  <B>Department Name : </B>'.$EmpDetails['DepartmentName'].'	 </p>'; 
echo '<p><B>Month          : </B> '.$MonthDis.'
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    
  <B>Branch  : </B>'.$EmpDetails['BranchName'].'
	 </p>'; 
	$data='<table style="width:100%;" border="1" cellspacing="0" cellpadding="6">
				<thead>
                 
                  <th>DATE</th>
                <!--  <th>Bio ID</th> -->
                  <th>TIME IN</th>
                  <th>BREAK OUT</th>
                  <th>BREAK IN</th>
                 <!-- <th>MINUTES</th>-->
                  <th>BREAK OUT</th>
                  <th>BREAK IN</th>
                  <th>TIME OUT</th>
                  <th>HOURS</th>
                  <th>SHIFT</th>
                </thead>
                <tbody id >';
				
				
if($SubDeptID!=''){
	$SubQuery="and EmpList.SubDeptID='$SubDeptID'";
}else if($DeptID !=''){
	$SubQuery="and Dept.DeptID='$DeptID'";
}else{
	$SubQuery="";
}	

			 $SqlEmpList = "SELECT EmpName, DepartmentName,Status,ShiftDtl.ShiftID, AttID,BreakTimeIn,BreakTimeOut,
AttLog.LunchTimeStart,AttLog.LunchTimeEnd,EmpList.BioMetricID, Date, TimeIn, TimeOut,GraceTime, OnTime,StartTime,ShiftName,
TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,AttLog.TimeOut) as WorkMin,
TIMESTAMPDIFF(MINUTE,AttLog.BreakTimeOut,AttLog.BreakTimeIn) as BreakMin,
TIMESTAMPDIFF(MINUTE,AttLog.LunchTimeStart,AttLog.LunchTimeEnd) as LunchBreakMin, 

(SELECT MIN(ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmAuditShiftDetails ) as Diff 
FROM HrmAuditAttendnessAuditLog as AttLog LEFT JOIN HrmAuditEmployeeDetails as EmpList 
ON EmpList.BioMetricID=AttLog.BioMetricID LEFT JOIN HrmDepartments as Dept 
ON EmpList.DeptID=Dept.DeptID LEFT JOIN HrmAuditShiftDetails as ShiftDtl 
on ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,ShiftDtl.StartTime))= (SELECT MIN( ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmAuditShiftDetails ) 
where EmpList.EmpStatus=1 and AttLog.Date BETWEEN '$date_start' AND '$date_end'  
and EmpList.BioMetricID='$BioMetricID'
					$SubQuery
					ORDER BY AttLog.Date Asc,
					AttLog.TimeIn Asc";
					
	

                    $query = $iconn->query($SqlEmpList);
                    while($row = $query->fetch_array()){
						
						$BioMetricID=$row['BioMetricID'];
						$Status=$row['Status'];
						$BreakTimeIn= $row['BreakTimeIn'];
						$BreakTimeOut= $row['BreakTimeOut'];
						
						if($row['ShiftID']==1){
						$LunchTimeStart= $row['LunchTimeStart'];
						$LunchTimeEnd= $row['LunchTimeEnd'];
						$LunchBreakMin= $row['LunchBreakMin'];
						$LunchTimeStart= date('h:i A', strtotime($row['LunchTimeStart']));
						$LunchTimeEnd= date('h:i A', strtotime($row['LunchTimeEnd']));
						
						}else{
						$LunchTimeStart='--';
						$LunchTimeEnd= '--';
						$row['LunchBreakMin']= 0;
						}
						
						$ShiftName= $row['ShiftName'];
						
						$TimeIn= date('h:i A', strtotime($row['TimeIn'])); 
						$TimeOut= date('h:i A', strtotime($row['TimeOut']));
						
						$BreakTimeIn= date('h:i A', strtotime($row['BreakTimeIn']));
						$BreakTimeOut= date('h:i A', strtotime($row['BreakTimeOut']));
						
						 $loginTime = strtotime($TimeIn);
						 $loginOut = strtotime($TimeOut);
						 
						 
						 if($row['ShiftID']==4){
							  $row['TimeOut'];
							  $NSFT_DATE = date('Y-m-d h:i A', strtotime($row['Date'] .$row['TimeOut'] . ' +1 day'));
							  $NSPFT_DATE = date('Y-m-d h:i A', strtotime($row['Date'] .$row['TimeIn']));
							  
							  $NSFT_DATE = strtotime( $NSFT_DATE);//echo '<br>'; uma 
							  $NSPFT_DATE = strtotime( $NSPFT_DATE);
							
							  round(abs($NSFT_DATE - $NSPFT_DATE) / 60,2);
							 $row['WorkMin']= round(abs($NSPFT_DATE - $NSFT_DATE) / 60,2);
						 
						 }
						 
						  $diff = $row['WorkMin']-$row['BreakMin'];
						  $diff = $row['WorkMin']-$row['LunchBreakMin'];
						  //$diff = $row['WorkMin'];
						  
						  
						  
						$Work_Hours = (int)($diff / 60);
					 $Work_Min = $diff % 60;
					
					$diff = $diff/(60);
					
					 if($diff > 0){
						 $ot = $diff - 8;
					 }else{
						 $ot =0;
					 }
					 
					 $diff=round($diff,2);
					 $ot=round($ot,2);
					 $ot=$ot*60;
					 
					 $Ot_Hours = (int)($ot / 60);
					 $Ot_Min = $ot % 60;
					
					
					 $diff = $Work_Hours .' H : ' .$Work_Min.' M';
					 $ot = $Ot_Hours .' H : ' .$Ot_Min.' M';
					 
					// echo $row['Date'];
					 if(in_array($row['Date'],$AryHolidays,true)){
					  $PublicHoliday='- Public Holiday';				  
					 }else{
					 $PublicHoliday='';
					 }
					
/*					
					 if( $Status > 1 && $PublicHoliday=='' ){
					 }else {
						 $Status=5;
					 }
					 */
					 
					  $StatusName='';
					  $SatusCode='1';
					 
					 if($Status==3){
						 $StatusName='Week Off';
						 $SatusCode='';
					 }else if($Status==2){
						 $StatusName='Absent';
						 $SatusCode='';
					 }else if($Status==4){
						 $StatusName='Leave';
						 $SatusCode='';
					 }else if($Status==5){
						 $StatusName='Compensatory-Off';
						 $SatusCode='';
					 }else if($Status==6){
						 $StatusName='PH-Pressent';
						 $SatusCode='1';
					 }
					 
					 $StatusName=$StatusName.$PublicHoliday;


			if($Status > 1 && $Status < 6){


					$data .="<tr>
                          <td>".date('d-m-Y', strtotime($row['Date']))."</td>
                         
                           
						<td></td>
						<td></td>
						<td></td>
						<td><b>".$StatusName."</b></td>
						<td></td>
						<td></td>
						<td></td>";
						
						$data .='<td></td>
                        </tr>';
						
	
	
}else if(in_array($row['Date'],$AryHolidays,true)&& $Status !=6){
					  $data .="<tr>
                          <td>".date('d-m-Y', strtotime($row['Date']))."</td>
                        
                           
						<td></td>
						<td></td>
						<td><b>".$StatusName."</b></td>
						<td></td>
						<td></td>
						<td></td>";
						$data .='
                        </tr>';			  
					 }else{
		
			
	
		
									 
							if($TimeOut=='12:00 AM'){
$TimeOut='NA';
$diff='NA';
							}								
								
								
					 $data .="
                        <tr>
                          <td>".date('d-m-Y', strtotime($row['Date']))."</td>
                         
                           <td>".$TimeIn."</td>
                          <td>".$BreakTimeOut."</td>
                          
                          <td>".$BreakTimeIn. ' ' .$status."</td>
						 <!-- <td>".$row['BreakMin']." Min</td>-->
						  <td>".$LunchTimeStart." </td>
						  <td>".$LunchTimeEnd." </td>
						<td>".$TimeOut."</td>
						<td>".$diff."</td>
						<td>".$ShiftName."</td>
						";
						$data .='</tr>'
                      ;
                    }
					
					}

				echo $data .='</tbody></table>';
				
				
				
				$SqlCountData="SELECT BioMetricID,
(SELECT COUNT(*) from HrmAuditAttendnessAuditLog WHERE Status=1 and Month(Date)='$Month' and Year(Date)='$Year' and BioMetricID='$BioMetricID') As TotalPresent,
(SELECT COUNT(*) from HrmAuditAttendnessAuditLog WHERE Status=2 and Month(Date)='$Month' and Year(Date)='$Year' and BioMetricID='$BioMetricID') As Absents,
(SELECT COUNT(*) from HrmAuditAttendnessAuditLog WHERE Status=4 and Month(Date)='$Month' and Year(Date)='$Year' and BioMetricID='$BioMetricID') As Leaves,
(SELECT COUNT(*) from HrmAuditAttendnessAuditLog WHERE Status=3 and Month(Date)='$Month' and Year(Date)='$Year' and BioMetricID='$BioMetricID') As WeekOffs,
(SELECT COUNT(*) from HrmAuditAttendnessAuditLog WHERE Status=5 and Month(Date)='$Month' and Year(Date)='$Year' and BioMetricID='$BioMetricID') As C_Offs,
(SELECT COUNT(*) from HrmAuditAttendnessAuditLog WHERE Status=1 and Month(Date)='$Month' and Year(Date)='$Year' and BioMetricID='$BioMetricID') As WageDays,
(SELECT COUNT(*) from HrmAuditAttendnessAuditLog WHERE Status=6 and Month(Date)='$Month' and Year(Date)='$Year' and BioMetricID='$BioMetricID') As PhPreDays,
(SELECT COUNT(*) from HrmHolidays WHERE  Month(Date)='$Month' and Year(Date)='$Year' ) As PhDays
FROM `HrmAuditAttendnessAuditLog` WHERE BioMetricID='$BioMetricID' GROUP BY BioMetricID"; 

$QueryCountData = $iconn->query($SqlCountData);
$CountData = $QueryCountData->fetch_array();

$TotalPresentDays=($CountData['TotalPresent'] 
								+ $CountData['C_Offs'] 
								+ $CountData['PhPreDays'] +$CountData['PhPreDays'] 
								- $CountData['PhDays']);
								
                      $WageDays=$CountData['WageDays']
								+$CountData['PhPreDays']+$CountData['PhPreDays']
								+ $CountData['C_Offs'] 
								+$CountData['Leaves'];

				
			echo '<br><br>';echo	$DetailsData='<table style="
    width: 100%;" border="1" cellspacing="0" cellpadding="6"> 
				<tr>                
                  <td>TOTAL DAYS PRESENT:</td>
                  <td>TOTAL LEAVE DAYS:</td>
				  <td>TOTAL C-OFFS</td>
				  <td>TOTAL PH</td>
                  <td>TOTAL WEEKLY OFFS:</td>
                  <td>TOTAL ABSENTS:</td>
                  
                  <td>TOTALWAGE DAYS:</td>
                  
                </tr>
				<tr>
				<td>'.($TotalPresentDays).'</td>
				<td>'.$CountData['Leaves'].'</td>
				<td>'.$CountData['C_Offs'].'</td>
				<td>'.($CountData['PhDays']-$CountData['PhPreDays']).'</td>
				<td>'.$CountData['WeekOffs'].'</td>
				<td>'.$CountData['Absents'].'</td>
				
				<td>'.($WageDays).'</td>
				<!--<td>'.$CountData['PhPreDays'].'</td>-->
				
				</tr>
				</table>';

}

?> 