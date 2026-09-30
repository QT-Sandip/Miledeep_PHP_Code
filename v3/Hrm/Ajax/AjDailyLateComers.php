<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$DeptID=$_POST['Dept'];
$SubDeptID=$_POST['SubDept'];
$Month=$_POST['Month'];
$Year=$_POST['Year'];
$Type=$_POST['Type'];

 $date_start=$Year."-".$Month."-01";
	$date_end=$Year."-".$Month."-31 23:59:59";
	
	

$Date=$_POST['Date'];

	
	$date_start=$Date;
	$date_end=$date_start.' 23:59:59';
	
	$data=' <table id="example1" class="table table-bordered">
   <thead>
                 
                  <th>Date</th>
                  <th>Bio ID</th>
                  <th>Name</th>
                  <th>Department</th>
                  <th>Shift</th>
                  <th>Time In</th>
                  <th>Time Out</th>
                  <th>Hours</th>
                 <!-- <th>Tools</th> -->
                </thead>
                <tbody id >';
				
				
if($SubDeptID!=''){
	$SubQuery="and EmpList.SubDeptID='$SubDeptID'";
}else if($DeptID !=''){
	$SubQuery="and Dept.DeptID='$DeptID'";
}else{
	$SubQuery="";
}	
	
 $SqlEmpList = "SELECT EmpName, DepartmentName, AttID, EmpList.BioMetricID, Date, TimeIn, TimeOut,GraceTime, 
OnTime,StartTime,ShiftName,
TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,AttLog.TimeOut) as WorkMin,
(SELECT  MIN(ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmShiftDetails ) as Diff 
FROM HrmDailyAttendnessLog as AttLog 
LEFT JOIN HrmEmployeeDetails as EmpList ON EmpList.BioMetricID=AttLog.BioMetricID
LEFT JOIN HrmDepartments as Dept ON EmpList.DeptID=Dept.DeptID
LEFT JOIN HrmShiftDetails  as ShiftDtl 
on ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,ShiftDtl.StartTime))=
(SELECT  MIN( ABS(TIMESTAMPDIFF(MINUTE,AttLog.TimeIn,StartTime))) from HrmShiftDetails )
where  EmpList.EmpStatus=1  
					and AttLog.Date BETWEEN '$date_start' AND '$date_end' 
					$SubQuery
					ORDER BY AttLog.Date DESC,
					AttLog.TimeIn DESC";
//exit;				
	/*echo $SqlEmpList = "SELECT 
	EmpName,
	DepartmentName,
	AttID,
	EmpList.BioMetricID,
	Date,
	TimeIn,
	TimeOut,
	OnTime,
	Shift FROM $HrmDailyAttendnessLog as AttLog
					LEFT JOIN $HrmEmployeeDetails as EmpList ON EmpList.BioMetricID=AttLog.BioMetricID 
					LEFT JOIN $HrmDepartments  as Dept ON EmpList.DeptID=Dept.DeptID	
					LEFT JOIN $HrmShiftDetails  as ShiftDtl ON ShiftDtl.ShiftID=AttLog.Shift	
					where  EmpList.EmpStatus=1  
					and AttLog.Date BETWEEN '$date_start' AND '$date_end' 
					$SubQuery
					ORDER BY AttLog.Date DESC,
					AttLog.TimeIn DESC";	*/		
//exit;

                    $query = $iconn->query($SqlEmpList);
                    while($row = $query->fetch_array()){
						
						$BioMetricID=$row['BioMetricID'];
						
							if($row['TimeOut']!="00:00:00"){
							$TimeOut= date('h:i A', strtotime($row['TimeOut']));
							}else{ 
							$TimeOut= "--" ;
							}
							
							if($row['TimeIn']!="00:00:00"){
							  $TimeIn= date('h:i A', strtotime($row['TimeIn'])); 
							}else{ 
							 $TimeIn= "--" ;
							 }
	
		$ShiftName= $row['ShiftName'];	
	
		
		$Shift= 'ROTATION';
		 if($row['SHIFT']==5){
			$Shift= 'NIGHT SHIFT';
			
			$employee_id=$row['employee_id'];
			$next_day=date('Y-m-d', strtotime('+1 day', strtotime($date_start)));
							
			echo $sql = "SELECT * from HrmDailyAttendnessLog where BioMetricID='$BioMetricID' and Date='$next_day'";
			$night_query = $iconn->query($sql);
			$night_row = $night_query->fetch_array();
			if($night_row['time_out']!="00:00:00"  && $night_row['time_out']!=""){
			//$time_out= date('h:i A', strtotime($night_row['time_out'])) .'(ND)';
			$time_out= date('h:i A', strtotime($night_row['time_out']));
			}else{ 
			$time_out= "--" ;
			}
		}else{
			$Shift= 'UNKNOWN SHIFT';
		} 
		
		
		
		if($row['Diff'] < $row['GraceTime']){
		$status = ("on time")?'<span class="label label-success pull-right">ontime</span>':'<span class="label label-danger pull-right">late</span>';
		}else{
		$status = ("late")?'<span class="label label-danger pull-right">late</span>':'<span class="label label-success pull-right">ontime</span>';
		}
								
				if($TimeIn !='--' && $TimeOut !='--'){
					 
					 $loginTime = strtotime($TimeIn);
					 $loginOut = strtotime($TimeOut);
					 
					 if($row['SHIFT']==4){
						 $loginTime = strtotime($date_start .' '. $time_in );
						 $loginOut = strtotime( $next_day .' '.$time_out );
						
						 $diff = $loginOut - $loginTime;

						$time_out.='(ND)'	;					 
					 }else{
						 $diff = $loginOut - $loginTime; 
					 }
					
					 //$diff = $diff/(60);
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


					}else{
						$diff =0; 
					 }
		
									
								
								
					 $data .="
                        <tr>
                          <td>".date('d-m-y', strtotime($row['Date']))."</td>
                          <td>".$row['BioMetricID']."</td>
                          <td>".$row['EmpName']."</td>
                          <td>".$row['DepartmentName']."</td>
                          <td>".$ShiftName."</td>
                          <td>".$TimeIn. ' ' .$status."</td>
						<td>".$TimeOut."</td>
						<td>".$diff."</td>
                         <!-- <td>
                            <button class='btn btn-success btn-sm btn-flat edit' data-id='".$row['attid']."'><i class='fa fa-edit'></i> Edit</button>
                            <button class='btn btn-danger btn-sm btn-flat delete' data-id='".$row['attid']."'><i class='fa fa-trash'></i> Delete</button>
                          </td> -->
                        </tr>
                      ";
                    }

				echo $data .='</tbody></table>';



?>