<?php
session_start();
include "../../includes/config.php";


$DEPT_CODE=$_POST['DEPT_CODE'];
$M_DEPT=$_POST['M_DEPT'];
$S_DEPT=$_POST['S_DEPT'];
$DATE=$_POST['CURRDATE'];

$employee_id=$_POST['emp_id'];
$time_in=$_POST['time_in'];

$time_out=$_POST['time_out'];


$time_in= date('H:i:s', strtotime($time_in));
$time_out= date('H:i:s', strtotime($time_out));

 //ECHO $sql="SELECT count(*) as total  from `attendance` 
 //where `employee_id`='$employee_id' and `date`='$DATE'"; EXIT;
 $sql="SELECT count(*) as total from attendance where employee_id='$employee_id' and `date`='$DATE'";
$query = $iconn->query($sql);
$data = $query->fetch_array();

$SqlSch="SELECT HRM_EMPLOYEE_LIST.SHIFT, schedules.TIME_IN, 
schedules.TIME_OUT, schedules.GRACE_PERIOD from HRM_EMPLOYEE_LIST  JOIN schedules 
on HRM_EMPLOYEE_LIST.SHIFT=schedules.ID 
where HRM_EMPLOYEE_LIST.EMPLOYEE_CODE='$employee_id'"; 
$StmtSch = $iconn->query($SqlSch);
$SchItem = $StmtSch->fetch_array();

$count=$data['total'];
  $StimeIn=$SchItem['TIME_IN'];
  $GPeriod=$SchItem['GRACE_PERIOD'];
 //echo strtotime($GPeriod);
  $TimeGap=(strtotime($time_in)-strtotime($StimeIn))/60;
 
  $TimeInSec=strtotime($time_in);
  $LowLimit=strtotime($StimeIn)-3600;
 
 $UpLimit=strtotime($StimeIn)+3600;


if($TimeGap < $GPeriod){
	$ON_TIME=1;	
}else{
	$ON_TIME=0;	
}


					   if ($TimeInSec > $LowLimit && $TimeInSec < $UpLimit) {
							$Shift=$SchItem['SHIFT'];
						}else{
						$Shift=0;
						}
						 $Shift;

 
if($count > 0){
	
	$oldAttData = legacyAuditFetchRowById('attendance', 'employee_id', $employee_id, 'Hrm');
	$sql="UPDATE `attendance` SET 
						`time_in`='$time_in',`time_out`='$time_out',
						`SHIFT`='$Shift',`ON_TIME`='$ON_TIME'
						WHERE `employee_id`='$employee_id' and `date`='$DATE'";
	$sqlca_post = $iconn->query($sql);
	if($sqlca_post) legacyAuditLogUpdateById('attendance', 'employee_id', $employee_id, $oldAttData, null, ['EmpID' => $employee_id, 'Remarks' => 'Updated Manual Attendance'], 'Hrm');
	
  /*$sql="UPDATE `attendance` 
			SET `time_in`='$time_in',`time_out`='$time_out',`status`='1' 
			WHERE `employee_id`='$employee_id' and `date`='$DATE'"; */
}else{

$sql="INSERT INTO `attendance`(`employee_id`, `date`, `time_in`,`time_out`,
						`status`,`SHIFT`,`ON_TIME`) VALUES ('$employee_id','$DATE','$time_in','$time_out','0',
						'$Shift','$ON_TIME')";	
	$sqlca_post = $iconn->query($sql);
	if($sqlca_post) legacyAuditLogInsertById('attendance', 'employee_id', $employee_id, null, ['EmpID' => $employee_id, 'Remarks' => 'Inserted Manual Attendance'], 'Hrm');
	/*
 $sql="INSERT INTO `attendance`(`employee_id`, `date`, `time_in`,`time_out`, `status`) VALUES
 ('$employee_id','$DATE','$time_in','$time_out','0')"; */
} 

//echo $sql;






	
	$date_start=$DATE;
	$date_end=$date_start.' 23:59:59';
	
	
	$data=' <table id="example1" class="table table-bordered">
   <thead>
                  <th class="hidden"></th>
                 
                  <th>Bio ID</th>
                  <th>Name</th>
                  <th>Contractor</th>
                  <th>DEPARTMENT</th>
                  <th>SHIFT</th>
                  <th>Time In</th>
                  <th>Time Out</th>
                 
                  <th>Tools</th>
                </thead>
                <tbody id >';
				
				if($S_DEPT !='' ){
					  
				$sql = "SELECT *  FROM HRM_EMPLOYEE_LIST 
					LEFT JOIN HRM_DEPARTMENT_MASTER ON HRM_EMPLOYEE_LIST.DEPARTMENT_ID=HRM_DEPARTMENT_MASTER.DEPARTMENT_ID 
					LEFT JOIN HRM_SUB_DEPARTMENT_MASTER ON HRM_EMPLOYEE_LIST.SUB_ID=HRM_SUB_DEPARTMENT_MASTER.SUB_ID 
					where HRM_EMPLOYEE_LIST.SUB_ID ='$S_DEPT' and 
					EMP_STATUS=1 ";  
				
				}else if($M_DEPT !='' ){
					$sql = "SELECT *  FROM HRM_EMPLOYEE_LIST 
					LEFT JOIN HRM_DEPARTMENT_MASTER ON HRM_EMPLOYEE_LIST.DEPARTMENT_ID=HRM_DEPARTMENT_MASTER.DEPARTMENT_ID 
					LEFT JOIN HRM_SUB_DEPARTMENT_MASTER ON HRM_EMPLOYEE_LIST.SUB_ID=HRM_SUB_DEPARTMENT_MASTER.SUB_ID 
					where HRM_EMPLOYEE_LIST.DEPARTMENT_ID ='$M_DEPT' and 
					EMP_STATUS=1 "; 
					
				}else {
					 $sql = "SELECT *  FROM HRM_EMPLOYEE_LIST 
					LEFT JOIN HRM_DEPARTMENT_MASTER ON HRM_EMPLOYEE_LIST.DEPARTMENT_ID=HRM_DEPARTMENT_MASTER.DEPARTMENT_ID 
					LEFT JOIN HRM_SUB_DEPARTMENT_MASTER ON HRM_EMPLOYEE_LIST.SUB_ID=HRM_SUB_DEPARTMENT_MASTER.SUB_ID and 
					EMP_STATUS=1";
				}
				
				//echo $sql; exit;
				
	 

                    $query = $iconn->query($sql);
                    while($row = $query->fetch_array()){
						 $employee_id=$row['EMPLOYEE_CODE'];
						  $sql = "SELECT * from attendance where date BETWEEN '$date_start' AND '$date_end' and employee_id='$employee_id'";
						 $query_att = $iconn->query($sql);
							
							
							//echo mysqli_num_rows($query_att);
						
						if(mysqli_num_rows($query_att) == 0){
							$time_out= "--" ;
							$time_in= "--" ;
							$Shift ='NA';
							$status ='';
						}else{
							$row_att = $query_att->fetch_array();
							
							
							 $row_att['time_out'];
						if($row_att['time_out']!="00:00:00"){
							$time_out= date('h:i A', strtotime($row_att['time_out']));
							}else{ 
							$time_out= "--" ;
							}
							
							if($row['time_in']!="00:00:00"){
							$time_in= date('h:i A', strtotime($row_att['time_in']));
							}else{ 
							$time_in= "--" ;
							}
							
							if($row_att['ON_TIME']==1){
								$status = ("on time")?'<span class="label label-success pull-right">ontime</span>':'<span class="label label-danger pull-right">late</span>';
								}else{
									$status = ("late")?'<span class="label label-danger pull-right">late</span>':'<span class="label label-success pull-right">ontime</span>';
								}
								
		
								
		$ShiftId=$row_att['SHIFT'];
						 $SqlShift = "SELECT * FROM `schedules` WHERE ID='$ShiftId'";
						 $StmtShift = $iconn->query($SqlShift);
						 $ItemShift=$StmtShift->fetch_array();
						 $Shift=$ItemShift['SHIFT_NAME'];

						}
							

					 $data .="
                        <tr>
                          <td class='hidden'></td>
                         
                          <td >".$employee_id."</td>
                          <td id=empname".$employee_id.">".$row['EMPLOYEE_NAME']."</td>
						  <td>".$row['AGENT']."</td>
                          <td>".$row['DEPARTMENT_NAME']."</td>
						  <td>".$Shift.$status."</td>
                          <td id=time_in".$employee_id.">".$time_in."</td>
						  <td id=time_out".$employee_id.">".$time_out."</td>
						 
                          <td>
                            <button class='btn btn-success btn-sm btn-flat edit' data-id='".$employee_id."'><i class='fa fa-edit'></i> Edit</button>
                            <button class='btn btn-danger btn-sm btn-flat delete' data-id='".$employee_id."'><i class='fa fa-trash'></i> Delete</button>
                          </td>
                        </tr>
                      ";
                    }
					
					echo $data .='</tbody></table>';

?>
