<?php
session_start();
include "../../includes/config.php";
include "../../includes/Func.php";

					$MONTH='08';
					$YEAR='2020';

				
				$sql_select="SELECT count(*) AS TOTAL FROM `HRM_FINAL_ATTENDANCE` where MONTH='$MONTH' and `YEAR`='$YEAR'";
			    $stmt_select=$iconn->query($sql_select);
				$row_select= $stmt_select->fetch_array();
 
 

IF($row_select['TOTAL'] > 0){   

echo "Already Sheet Prepared"; 
}else{
	
	//  and HRM_EMPLOYEE_LIST.`SUB_ID`=1
				
					 $sql = "SELECT *,SUM(cashadvance.amount)as amount,SUM(cashadvance.due_amount) as dueamount FROM 
					 `HRM_EMPLOYEE_LIST` LEFT JOIN cashadvance ON 
					  HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`=cashadvance.employee_id where HRM_EMPLOYEE_LIST.`EMP_STATUS`=1 
					
					  group by HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE` order by HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`";
				
	
				
				 $sql;
				 $query = $iconn->query($sql);
				 
				 
				 
				  while($row = $query->fetch_array()){
						
						
					$current_month= $MONTH;
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
					
					$attendance_count_sql="SELECT count(*) as count FROM `attendance` WHERE `employee_id`='$employeeid'
					and `date` BETWEEN 
					'$start_date' AND '$end_date'";	
					
					$attendance_count_query = $iconn->query($attendance_count_sql);
					$present_count_row = $attendance_count_query->fetch_array();
					$present_count_=$present_count_row['count'];
					$present_days =$present_count_;
					 
					$sundays=getWeekOffFunctions($present_days,$MONTH,$YEAR);
					 
					$present_count =$present_count_ + $sundays +$holidays;
					
					
			
				
				
					$c_leve=$iconn->query("SELECT SUM(NO_OF_DAYS) as count FROM `LEAVE_REGISTER` 
					WHERE `EMPLOYEE_ID`='$employeeid' and `LEAVE_TYPE`='casualleave' and( 
					`FROMDATE` BETWEEN 
					'$start_date' AND '$end_date'OR `TODATE` BETWEEN 
					'$start_date' AND '$end_date' )");
					 $c_leve_row = $c_leve->fetch_array();
					 $cl_count=$c_leve_row['count'];
					 
					 
					 $s_leve=$iconn->query("SELECT SUM(NO_OF_DAYS) as count FROM `LEAVE_REGISTER` WHERE 
					`EMPLOYEE_ID`='$employeeid' and `LEAVE_TYPE`='sickleave' and( 
					`FROMDATE` BETWEEN 
					'$start_date' AND '$end_date'OR `TODATE` BETWEEN 
					'$start_date' AND '$end_date' )");
				
				
					$s_leve_row = $s_leve->fetch_array();
					$sl_count=$s_leve_row['count'];
					
					
					$on_duty=$iconn->query("SELECT SUM(NO_OF_DAYS) as count FROM `OD_REGISTER` WHERE
					`EMPLOYEE_ID`='$employeeid'  and( 
					`FROMDATE` BETWEEN 
					'$start_date' AND '$end_date'OR `TODATE` BETWEEN 
					'$start_date' AND '$end_date' )");
					$on_duty_row = $on_duty->fetch_array();
					$on_duty_count=$on_duty_row['count'];
					
					
					
					if($cl_count==''){
						$cl_count='0';
					}
					
					if($sl_count==''){
						$sl_count='0';
					}
					
					if($on_duty_count==''){
						$on_duty_count='0';
					}
					
					
					$ot='0';
					$wo=$sundays;
					
					$working_days=$present_count;
					
					
					
					 
					 if($absent_count<='0'){
						 $absent_count='0';
						 }
					 
					 
					 if($sl_count >= $row['SICK_LEAVES'] ){
					  $ab_sl_count=$sl_count-$row['SICK_LEAVES'];
					   $sick_leaves=$row['SICK_LEAVES'];
					 }else{
					  $ab_sl_count='0';
					  $sick_leaves='0';
					 }

					 if($cl_count >= $row['CASUAL_LEAVES'] ){
					  $ab_cl_count=$cl_count-$row['CASUAL_LEAVES'];
					  $causal_leaves=$row['CASUAL_LEAVES'];
					 }else{
					  $ab_cl_count='0';
					  $causal_leaves='0';
					 }
					
					 
					 
					 $total_working_days=$present_count_+$wo+$ot+$causal_leaves+$sick_leaves+$on_duty_count;
					 
					 
					 if($total_days > $total_working_days ){
						echo 'Ab-';echo $absent_count	 = $total_days  - $total_working_days;
					 }
					 
					 $absent_days=$ab_cl_count+$ab_sl_count+$absent_count;
					
					
					 $monthName = date('F', mktime(0, 0, 0, $MONTH, 10));
					 
					 if($total<0){
						 $total='0';
					 }
					 
				  					 
					 $ID=$row['EMPLOYEE_CODE'];
					
					$DEPT_ID= $row['DEPARTMENT_ID'];
					$SUB_ID= $row['SUB_ID'];
					
					 
					 
				 	$sql_insert="INSERT INTO `HRM_FINAL_ATTENDANCE` (`EMP_ID`, `DEPT_ID`, `SUB_DEPT_ID`, `MONTH`, `PRESENT_DAYS`,
	`ABSENT_DAYS`, `O_T`, `WEEK_OFF`, `C_L`, `S_L`, `TOTAL_WORKNG_DAYS`, `ADVANCE_RECOVERY`, 
	`ON_DUTY`, `YEAR`) VALUES ('$ID','$DEPT_ID','$SUB_ID','$MONTH','$present_days','$absent_days','$ot',
	'$wo','$causal_leaves','$sick_leaves','$total_working_days','$ADVANCE_RECOVERY','$on_duty_count','$YEAR')";
	

				$stmt_insert=$iconn->query($sql_insert);
				if($stmt_insert) legacyAuditLogInsertById('HRM_FINAL_ATTENDANCE', 'EMP_ID', $ID, null, ['EmpID' => $ID, 'Remarks' => 'Inserted Final Attendance'], 'Hrm');
                    }


}
   

	

?>
