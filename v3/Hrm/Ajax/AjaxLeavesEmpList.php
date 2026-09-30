<?php
session_start();
include "../../includes/config.php";


$DEPT_CODE=$_POST['DEPT_CODE'];
$M_DEPT=$_POST['Dept'];
$S_DEPT=$_POST['SubDept'];
$DATE=$_POST['CURRDATE'];
$MONTH=$_POST['MONTH'];
$YEAR=$_POST['YEAR'];


if($DEPT_CODE!=''){

  $sql="SELECT * FROM `HrmSubDepartments` WHERE `DeptID`='$M_DEPT'";
 

	$stmt=$iconn->query($sql);
	$arySubDprts=array();

	
	
?>	<option value="">All Sub Departments</option> <?php

				
					while($row=$stmt->fetch_array()){
	
					$SubDprtName=$row['SubDeptName'];
					 if(in_array($SubDprtName, $arySubDprts)){
					
					}else{
						$arySubDprts[]=$SubDprtName;?>	
						
					<option value="<?php echo $row["SubDeptName"]; ?>"><?php echo $row["SubDeptName"]; ?></option>
				<?php   }}


}else{
	
	$date_start=$DATE;
	$date_end=$date_start.' 23:59:59';?>
	
	<!-- <table id="example1" class="table table-bordered">-->
	 <table id="scroll-vertical-datatable" class="table dt-responsive nowrap w-100">
   <thead>
                  <th class="hidden"></th>
                  <th>Employee ID</th>
                  <th>Employee Name</th>
                   <th>Department</th>
                  <th>Month</th>
                                  
				 <th>Present</th>
                  <th>Absent</th>
                    <th>OT</th>
                    <th>OD</th>
                  <th>W/0</th>
                  <th>CL</th>
                  <th>SL</th>
                  <th>Total</th>
                                  
				 <th>Tools</th>
                </thead>
                <tbody id >
				
		<?php		if($S_DEPT !='' ){
					  
					  $sql = "SELECT *,SUM(cashadvance.amount)as amount,SUM(cashadvance.due_amount) as dueamount 
					  FROM `HRM_EMPLOYEE_LIST` LEFT JOIN cashadvance ON 
					  HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`=cashadvance.employee_id 
					  JOIN HRM_DEPARTMENT_MASTER ON
					  HRM_EMPLOYEE_LIST.DEPARTMENT_ID= HRM_DEPARTMENT_MASTER.DEPARTMENT_ID
					  LEFT JOIN HRM_FINAL_ATTENDANCE ON 
					  HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`=HRM_FINAL_ATTENDANCE.EMP_ID
					  where HRM_EMPLOYEE_LIST.`SUB_ID`='$S_DEPT' 
					  AND HRM_FINAL_ATTENDANCE.MONTH='$MONTH' and  HRM_FINAL_ATTENDANCE.YEAR='$YEAR'
					  and HRM_EMPLOYEE_LIST.`EMP_STATUS`=1 
					  group by HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE` order by HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`";
				
				}else if($M_DEPT !='' ){
					
					 $sql = "SELECT * FROM `HRM_DEPARTMENT_MASTER` where DEPARTMENT_ID= '$M_DEPT'";
					$query = $iconn->query($sql);
                    $row = $query->fetch_array();
					//$M_DEPT = $row['DEPARTMENT_NAME'];
					 
					 $sql = "SELECT *,SUM(cashadvance.amount)as amount,SUM(cashadvance.due_amount) as dueamount FROM 
					 `HRM_EMPLOYEE_LIST` LEFT JOIN cashadvance ON 
					  HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`=cashadvance.employee_id
					  JOIN HRM_DEPARTMENT_MASTER ON
					  HRM_EMPLOYEE_LIST.DEPARTMENT_ID= HRM_DEPARTMENT_MASTER.DEPARTMENT_ID
					  LEFT JOIN HRM_FINAL_ATTENDANCE ON 
					  HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`=HRM_FINAL_ATTENDANCE.EMP_ID				  
					  where HRM_EMPLOYEE_LIST.DEPARTMENT_ID = '$M_DEPT'  
					  and HRM_EMPLOYEE_LIST.`EMP_STATUS`=1 
					  AND HRM_FINAL_ATTENDANCE.MONTH='$MONTH' and  HRM_FINAL_ATTENDANCE.YEAR='$YEAR'
					  group by HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE` order by HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`"; 
				
				}else {
					 
					 $sql = "SELECT *,SUM(cashadvance.amount)as amount,SUM(cashadvance.due_amount) as dueamount FROM 
					 `HRM_EMPLOYEE_LIST` LEFT JOIN cashadvance ON 
					  HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`=cashadvance.employee_id 
					  JOIN HRM_DEPARTMENT_MASTER ON
					  HRM_EMPLOYEE_LIST.DEPARTMENT_ID= HRM_DEPARTMENT_MASTER.DEPARTMENT_ID
					  LEFT JOIN HRM_FINAL_ATTENDANCE ON 
					  HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`=HRM_FINAL_ATTENDANCE.EMP_ID			
					  where HRM_EMPLOYEE_LIST.`EMP_STATUS`=1 
					  AND HRM_FINAL_ATTENDANCE.MONTH='$MONTH' and  HRM_FINAL_ATTENDANCE.YEAR='$YEAR'
					  group by HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE` order by HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`";
				
				}
				
		//echo	 $sql; exit;
				
	 

                    $query = $iconn->query($sql);
                    while($row = $query->fetch_array()){
						
						
					$current_month= $MONTH;
						//echo $no_of_days;
					//$current_month= date('m');
					//echo $MONTH;
					$no_of_days = cal_days_in_month(CAL_GREGORIAN, $MONTH, $YEAR);
					$monthName = date("F", mktime(0, 0, 0, $current_month, 10));
					$current_year= date('Y');
					$month_dt= date('Y-'.$MONTH.'-01');
					
					
					$start_date = date('Y-m-01', strtotime($month_dt));
					
					$end_date = date('Y-m-t', strtotime($month_dt));
					
					
					$sundays=0;  //doe
					$holidays=0;  //doe
					$absent_count=0;
					$present_count=0;
					
					$total_days=$no_of_days;
					
					for($i=1;$i<=$no_of_days;$i++)
					if(date('N',strtotime($current_year.'-'.$current_month.'-'.$i))==7){
					$sundays++;
					}
					
					$employeeid=$row['EMPLOYEE_CODE'];
					
					$attendance_count_sql="SELECT count(*) as count FROM `attendance` WHERE `employee_id`='$employeeid' and `date` BETWEEN 
					'$start_date' AND '$end_date'";					
 			
					$attendance_count_query = $iconn->query($attendance_count_sql);
					//$present_count=$attendance_count_query->num_rows;
					$present_count_row = $attendance_count_query->fetch_array();
					 $present_count_=$present_count_row['count'];
					// echo $present_count_;
					$present_count =$present_count_ + $sundays +$holidays;
					
					
					$st="SELECT SUM(NO_OF_DAYS) as count FROM `LEAVE_REGISTER` WHERE `EMPLOYEE_ID`='$employeeid' and `LEAVE_TYPE`='CL' and( 
					`FROMDATE` BETWEEN 
					'$start_date' AND '$end_date'OR `TODATE` BETWEEN 
					'$start_date' AND '$end_date' )";
				 	
					$c_leve=$iconn->query("SELECT SUM(NO_OF_DAYS) as count FROM `LEAVE_REGISTER` WHERE `EMPLOYEE_ID`='$employeeid' and `LEAVE_TYPE`='CL' and( 
					`FROMDATE` BETWEEN 
					'$start_date' AND '$end_date'OR `TODATE` BETWEEN 
					'$start_date' AND '$end_date' )");
					$c_leve_row = $c_leve->fetch_array();
					 $cl_count=$c_leve_row['count'];
					 
					if($cl_count==''){
						$cl_count='0';
					}
					//sickleave
					$s_leve=$iconn->query("SELECT SUM(NO_OF_DAYS) as count FROM `LEAVE_REGISTER` WHERE 
					`EMPLOYEE_ID`='$employeeid' and `LEAVE_TYPE`='SL' and( 
					`FROMDATE` BETWEEN 
					'$start_date' AND '$end_date'OR `TODATE` BETWEEN 
					'$start_date' AND '$end_date' )");
				// 	echo $s_leve;die();
					$s_leve_row = $s_leve->fetch_array();
					 $sl_count=$s_leve_row['count'];
					if($sl_count==''){
						$sl_count='0';
					}
					
					$ot='0';
					$wo=$sundays;
					
					if($total_days > $present_count ){
					$absent_count	 = $total_days - $present_count;
					}

					 $working_days=$present_count;
					 //$total_days=31;
					 //$working_days=31;
					 
					$od_sql=$iconn->query("SELECT SUM(NO_OF_DAYS) as count FROM `OD_REGISTER` WHERE 
					`EMPLOYEE_ID`='$employeeid'  and( 
					`FROMDATE` BETWEEN 
					'$start_date' AND '$end_date'OR `TODATE` BETWEEN 
					'$start_date' AND '$end_date' )");
					
					$od_sql_row = $od_sql->fetch_array();
					 $od_count=$od_sql_row['count'];
					if($od_count==''){
						$od_count='0';
					}
						
						if($row['time_out']!="00:00:00"){
							$time_out= date('h:i A', strtotime($row['time_out']));
							}else{ 
							$time_out= "--" ;
							}
							
							if($row['time_in']!="00:00:00"){
							$time_in= date('h:i A', strtotime($row['time_in']));
							}else{ 
							$time_in= "--" ;
							}
                      $status = ($row['status'])?'<span class="label label-warning pull-right">ontime</span>':'<span class="label label-danger pull-right">late</span>';
                     if( $row['amount']==''){
						 $row['amount']='0';
						 
					 }
					 if( $row['dueamount']==''){
						 $row['dueamount']='0';
						 
					 }
					 if($absent_count<='0'){
						 $absent_count='0';
						 
					 }
					 //echo $row['SICK_LEAVES'];
					 $emp_sl_count=$sl_count-$row['SICK_LEAVES'];
					 if($emp_sl_count<0){
$emp_sl_count='0';
					 }
					 //echo $emp_sl_count;
					$emp_cl_count=$cl_count-$row['CASUAL_LEAVES'];
					
					 if($emp_cl_count<0){
$emp_cl_count='0';
					 }
					 
					if($sundays==5){

					if($present_count_>=26){
						 
						$wo=5; 
					 }elseif($present_count_<26 & $present_count_>=24){
						 
						$wo=4; 
					 }elseif($present_count_<24 & $present_count_>=18){
						 
						$wo=3; 
					 }
					 elseif($present_count_<18 & $present_count_>12){
						 
						$wo=3; 
					 }elseif($present_count_< 12 & $present_count_>6){
						 
						$wo=2; 
					 }elseif($present_count_< 6){
						 
						 $wo=0;
					 }
					}else{
					
					if($present_count_>=24){
						 
						$wo=4; 
					 }elseif($present_count_<24 & $present_count_>=18){
						 
						$wo=3; 
					 }elseif($present_count_<=17 & $present_count_>=12){
						 
						$wo=2; 
					 }
					 elseif($present_count_<=11 & $present_count_>6){
						 
						$wo=1; 
					 }elseif($present_count_<= 6){
						 
						 $wo=0;
					 }
					
					}
					 
					 
				 
					 $total=$present_count_+$wo+$ot+$od_count+ $emp_cl_count+$emp_sl_count;
					
					 $monthName = date('F', mktime(0, 0, 0, $MONTH, 10));
					 
					 if($total<0){
						 $total='0';
					 }
					 
					 ?>
					 
					 
                        <tr>
                          <td class='hidden'></td>
                          <td><?php echo $row['EMPLOYEE_CODE'];?></td>
                          
						  <td><?php echo $row['EMPLOYEE_NAME'];?></td>
					  <td><?php echo $row['DEPARTMENT_NAME'];?></td>
                          <td><?php echo $monthName;?></td>
                          
						   <td><?php echo $present_count_;?></td>
                          <td><?php echo $absent_count;?></td>
                          <td><?php echo $ot;?></td>
                          <td><?php echo $od_count;?></td>
                          <td><?php echo $wo;?></td>
                          <td><?php echo $cl_count;?></td>
                          <td><?php echo $sl_count;?></td>
                          <td><?php echo $total;?></td>
                         
                          <td>
						    
                            <button class='btn btn-success btn-sm btn-flat edit' data-id='<?php $row['EMPLOYEE_CODE']?>'><i class='fa fa-edit'></i> Add</button>
                        </td>
                        </tr>
                      
                 <?php   }?>
					
					</tbody></table>
<?php }


?>