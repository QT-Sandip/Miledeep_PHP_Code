<style>

	
	td {
        font-size: 12px;
    }
	
	th {
        font-size: 12px;
    }
</style>

<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");
  $QueryParms=$_GET['Q']; 

if($QueryParms!=''){
   $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
$Month=$_GET['Month'];
$EmpID=$_GET['EmpID'];
$Year=$_GET['Year'];
$DeptID=$_GET['DeptID'];
$SubDeptID=$_GET['SubDeptID'];
$date_start=$Year."-".$Month."-01";
$date_end=$Year."-".$Month."-31 23:59:59";

$MonthDis=date('M-Y', strtotime($date_start)); 

if($DeptID==''){	
	echo "Please Select Department";
	exit;	
}

$SubQuery="";

if($SubDeptID!=''){
	$SubQuery.=" and HED.SubDeptID='$SubDeptID'";
}

 if($DeptID !=''){
	$SubQuery.=" and HED.DeptID='$DeptID'";
}



 $SqlHolidaysList = "SELECT * FROM `HrmHolidays` where Date between '$date_start' and '$date_end'";
$QueryHolidays = $iconn->query($SqlHolidaysList);
while($HolidaysDetails = $QueryHolidays->fetch_array()){
$AryHolidays[]=	$HolidaysDetails['Date'];
}


  $SqlEmpList = "SELECT 
 EmpName,BioMetricID,
 DepartmentName,Designation,
 ContractorName,
(select count(*) from HrmDailyAttendnessLog WHERE Month(Date)='$Month' 
and Year(Date)='$Year' and BioMetricID=HED.BioMetricID) as AttCount
 FROM `HrmEmployeeDetails`  as HED 
left join HrmDepartments as HD on HED.DeptID=HD.DeptID 
left join HrmDesignations as HDG on HED.DesigID=HDG.DesigID
left join HrmContractorList HCL on HCL.ContractorID=HED.ContractorID

where EmpStatus=1 $SubQuery order by BioMetricID asc"; 
$query = $iconn->query($SqlEmpList);

echo '<h1 align="center"> Jaya Lakshmi Sea Foods Pvt Ltd </h1>'; 
echo '<h3 >Employee Time Card - '.$MonthDis.'</h3>'; 

while($EmpDetails = $query->fetch_array()){
	if($EmpDetails['AttCount']!=0){
echo getData($iconn,$EmpDetails);
	}	
}



function getData($iconn,$EmpDetails){
	global $MonthDis;
	$BioMetricID=$EmpDetails['BioMetricID'];
	
	$Month=$_GET['Month'];
	$EmpID=$_GET['EmpID'];
	$Year=$_GET['Year'];

$data= '<table style="width: 100%" border="1" cellspacing="0" cellpadding="3">
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
	<td>'.$EmpDetails['EmpName'].'</td>
	<td>'.$EmpDetails['BioMetricID'].'</td>
	<td>'.$MonthDis.'</td>
	<td>'.$EmpDetails['DepartmentName'].'</td>
	<td>'.$EmpDetails['Designation'].'</td>
	<td>'.$EmpDetails['ContractorName'].'</td>
	</tr>
   </tbody>
   </table>';
   

$data.= '<br>';
	$data.='
	
	<table style="width: 100%" border="1" cellspacing="0" cellpadding="3">
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

					
	

                    $query = $iconn->query($SqlEmpList);
                    while($row = $query->fetch_array()){
						
						$BioMetricID=$row['BioMetricID'];
						$Status=$row['Status'];
						$BreakTimeIn= $row['BreakTimeIn'];
						$BreakTimeOut= $row['BreakTimeOut'];
						$ShiftName= $row['ShiftName'];
						$ShiftID= $row['Shift'];
						$ShiftRotation= $row['ShiftRotation'];
						$ShiftRotation= $row['NextDayOut'];
						
						$TimeIn= date('h:i A', strtotime($row['TimeIn'])); 
						$TimeOut= date('h:i A', strtotime($row['TimeOut']));
						
						
						 $loginTime = strtotime($TimeIn);
						 $loginOut = strtotime($TimeOut);
						 
						 
						 if($ShiftRotation==1){
							  $row['TimeOut'];
							  $NSFT_DATE = date('Y-m-d h:i A', strtotime($row['Date'] .$row['TimeOut'] . ' +1 day'));
							  $NSPFT_DATE = date('Y-m-d h:i A', strtotime($row['Date'] .$row['TimeIn']));
							  
							  $NSFT_DATE = strtotime( $NSFT_DATE); echo '<br>';
							  $NSPFT_DATE = strtotime( $NSPFT_DATE);
							
							  round(abs($NSFT_DATE - $NSPFT_DATE) / 60,2);
							 $row['WorkMin']= round(abs($NSPFT_DATE - $NSFT_DATE) / 60,2);
						 
						 }
						 
						 
						  if($loginTime >  $loginOut){
							 	  $row['TimeOut'];
							  $NSFT_DATE = date('Y-m-d h:i A', strtotime($row['Date'] .$row['TimeOut'] . ' +1 day'));
							  $NSPFT_DATE = date('Y-m-d h:i A', strtotime($row['Date'] .$row['TimeIn']));
							  
							  $NSFT_DATE = strtotime( $NSFT_DATE); //echo '<br>'; uma
							  $NSPFT_DATE = strtotime( $NSPFT_DATE);
							
							  round(abs($NSFT_DATE - $NSPFT_DATE) / 60,2);
							 $row['WorkMin']= round(abs($NSPFT_DATE - $NSFT_DATE) / 60,2);
						 }
						 
						  $diff = $row['WorkMin']-$row['BreakMin'];
						  $diff = $row['WorkMin'];
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
					 
					 if(in_array($row['Date'],$AryHolidays,true)){
					  $PublicHoliday='- Public Holiday';				  
					 }else{
					 $PublicHoliday='';
					 }
					
					 
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
					 
					 
					 if($row['TimeIn']=='00:00:00' || $row['TimeIn']==null ){
					 $TimeIn='NA';
					 $diff='NA';
					 }
					 
					 if($row['TimeOut']=='00:00:00' || $row['TimeOut']==null){
					 $TimeOut='NA';
					  $diff='NA';
					 }
					 
					 if($TimeIn=='NA' && $TimeOut=='NA'){
						$StatusName='Absent';
					 }
					 
					 $Date= date('d-m-Y', strtotime($row['Date']));
					
					if($row['TimeIn']==null && $row['TimeOut']==null){
						$StatusName='NA';
						//$Date='NA';
					 }
					 
					
						 
					 
					 			
					 $data .="
                        <tr>
						<td>".++$Sno."</td>
                        <td>".$Date."</td>
                        <td>".$TimeIn."</td>
						<td>".$TimeOut."</td>
						<td>".$diff."</td>
						<td>".$StatusName."</td>
                        </tr>";

					
					}

				return $data .='</tbody></table> <br> <br><hr>';

}

?>



