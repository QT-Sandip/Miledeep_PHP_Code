<?php
session_start();
include '../../includes/config.php';

include "../includes/session.php";
include "../includes/Func.php";


$DEPT_CODE=$_POST['DEPT_CODE'];
$M_DEPT=$_POST['M_DEPT'];
$S_DEPT=$_POST['S_DEPT'];
$DATE=$_POST['CURRDATE'];
$MONTH=$_POST['MONTH'];
 $TYPE=$_POST['TYPE'];
 $YEAR=$_POST['YEAR'];





if($DEPT_CODE!=''){
	

				
	
	

  $sql="SELECT * FROM `HRM_SUB_DEPARTMENT_MASTER` WHERE `DEPARTMENT_ID`='$DEPT_CODE'";
 

	$stmt=$iconn->query($sql);
	$ary_prod_codes=array();

	
	
?>	<option value="">All Sub Departments</option> <?php

				
					while($row=$stmt->fetch_array()){
	
					$FARM_REG_ID=$row['SUB_DEPARTMENT_NAME'];
					 if(in_array($FARM_REG_ID, $ary_prod_codes)){
					
					}else{
						$ary_prod_codes[]=$FARM_REG_ID;?>	
						
					<option value="<?php echo $row["SUB_ID"]; ?>"><?php echo $row["SUB_DEPARTMENT_NAME"]; ?></option>
				<?php   }}


}else{
	
	//echo $TYPE ;
	//echo 'Hi';exit;
	if($TYPE=='UPDATE'){
	
	
	$id=$_POST['id'];
	$presents=$_POST['presents'];
	$a_count=$_POST['a_count'];
	$ot=$_POST['ot'];
	$ON_DUTY=$_POST['ON_DUTY'];
	$wo_count=$_POST['wo_count'];
	$cl_count=$_POST['cl_count'];
	$sl_count=$_POST['sl_count'];
	$totall=$_POST['totall'];
	
	$totall=$presents+$wo_count+$cl_count+$sl_count+$ot+$ON_DUTY;
	
	
 $sql_select="SELECT count(*) AS TOTAL FROM `HRM_FINAL_ATTENDANCE` WHERE `EMP_ID`='$id' and MONTH='$MONTH' and `YEAR`='$YEAR'";
$stmt_select=$iconn->query($sql_select);
$row_select= $stmt_select->fetch_array();
 
 

IF($row_select['TOTAL'] !=0){      
	$oldFinalAtt = legacyAuditFetchRowById('HRM_FINAL_ATTENDANCE', 'EMP_ID', $id, 'Hrm');
	$sql_insert="UPDATE `HRM_FINAL_ATTENDANCE` SET 
	`PRESENT_DAYS`='$presents',
	`ABSENT_DAYS`='$a_count',
	`ON_DUTY`='$ON_DUTY',
	`O_T`='$ot',`WEEK_OFF`='$wo_count',`C_L`='$cl_count',
	`S_L`='$sl_count',`TOTAL_WORKNG_DAYS`='$totall',
	`ADVANCE_RECOVERY`='$ADVANCE_RECOVERY' where EMP_ID='$id' and MONTH='$MONTH' and `YEAR`='$YEAR'";
	
	
}ELSE{
	
	$sql_insert="INSERT INTO `HRM_FINAL_ATTENDANCE` (`EMP_ID`, `DEPT_ID`, `SUB_DEPT_ID`, `MONTH`, `PRESENT_DAYS`,
	`ABSENT_DAYS`, `O_T`, `WEEK_OFF`, `C_L`, `S_L`, `TOTAL_WORKNG_DAYS`, `ADVANCE_RECOVERY`, 
	`ON_DUTY`, `YEAR`) VALUES ('$id','$DEPT_ID','$SUB_DEPT_ID','$MONTH','$presents','$a_count','$ot',
	'$wo_count','$cl_count','$sl_count','$totall','$ADVANCE_RECOVERY','$ON_DUTY','$YEAR')";
	
	
}

 $sql_insert; 

$stmt_insert=$iconn->query($sql_insert);
if($stmt_insert){
	if($row_select['TOTAL'] !=0){
		legacyAuditLogUpdateById('HRM_FINAL_ATTENDANCE', 'EMP_ID', $id, $oldFinalAtt, $_POST, ['EMP_ID' => $id, 'MONTH' => $MONTH, 'YEAR' => $YEAR, 'Remarks' => 'Final attendance updated'], 'Hrm');
	}else{
		legacyAuditLogInsertById('HRM_FINAL_ATTENDANCE', 'EMP_ID', $iconn->insert_id, $_POST, ['EMP_ID' => $id, 'MONTH' => $MONTH, 'YEAR' => $YEAR, 'Remarks' => 'Final attendance inserted'], 'Hrm');
	}
}   

	
		
	    
	}
	
	$date_start=$DATE;
	$date_end=$date_start.' 23:59:59';
	
	
	
	
	
	


//	<table id="example1" class="table table-bordered"> "
	
   
	$data=' <table id="example1" class="table table-bordered" > 
	
	
   <thead>
                  <th class="hidden"></th>
                  <th>Employee ID</th>
                  <th>Employee Name</th>

                 
                                  
				 <th>Present</th>
				 <th>On Duty</th>
                  <th>Absent</th>
                    <th>OT</th>
                  <th>W/O</th>
                  <th>CL</th>
                  <th>SL</th>
                  <th>Total Working Days</th>
                                  
				 <th>Tools</th>
                </thead>
                <tbody id >';
				
				if($S_DEPT !='' ){
					  
					   $sql = "SELECT *
					  FROM `HRM_EMPLOYEE_LIST` LEFT JOIN HRM_FINAL_ATTENDANCE ON 
					  HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`=HRM_FINAL_ATTENDANCE.EMP_ID 
					  
					  where HRM_FINAL_ATTENDANCE.`SUB_DEPT_ID`='$S_DEPT' 
					  and  HRM_FINAL_ATTENDANCE.MONTH='$MONTH' and  HRM_FINAL_ATTENDANCE.YEAR='$YEAR'
					  and HRM_EMPLOYEE_LIST.`EMP_STATUS`=1 
					  group by HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE` order by HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`"; 
				
				}else if($M_DEPT !='' ){ 
				
				$sql = "SELECT * FROM 
					 `HRM_EMPLOYEE_LIST` LEFT JOIN HRM_FINAL_ATTENDANCE ON 
					  HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`=HRM_FINAL_ATTENDANCE.EMP_ID where 
					  HRM_FINAL_ATTENDANCE.DEPT_ID = '$M_DEPT'
					 and  HRM_FINAL_ATTENDANCE.MONTH='$MONTH' and  HRM_FINAL_ATTENDANCE.YEAR='$YEAR'					  
					  and HRM_EMPLOYEE_LIST.`EMP_STATUS`=1 
					  group by HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE` order by HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`";  
				
				}else {
					 
					 $sql = "SELECT * FROM 
					 `HRM_EMPLOYEE_LIST` LEFT JOIN HRM_FINAL_ATTENDANCE ON 
					  HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`=HRM_FINAL_ATTENDANCE.EMP_ID
					  where HRM_EMPLOYEE_LIST.`EMP_STATUS`=1
					AND HRM_FINAL_ATTENDANCE.MONTH='$MONTH' and  HRM_FINAL_ATTENDANCE.YEAR='$YEAR'					  
					  group by HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE` order by HRM_EMPLOYEE_LIST.`EMPLOYEE_CODE`"; 
				
				}
				
		// $sql;
				


                    $query = $iconn->query($sql);
                    while($row = $query->fetch_array()){

					$Presentdays= $row['PRESENT_DAYS']	;
					$wo=$row['WEEK_OFF'];
					
					 
					 $ID=$row['EMPLOYEE_CODE'];
					 
				$WEEK_OFF_CL=	 getWeekOffFunctions($Presentdays,$MONTH,$YEAR);
				
				
					 
					  $data .='
                        <tr>
                          <td class="hidden"></td>
                          <td>'.$row['EMPLOYEE_CODE'].'</td>
                         
	


						 
						  <td>'.$row['EMPLOYEE_NAME'].'</td>
						 
                     
						  
						  
		
						
						<td > <input  type="text" class="form-control"   name="PRESENT" 
						id="PRESENT'.$ID.'"placeholder="PRESENT DAYS" value="'.$Presentdays.'" <div style="width:80%;" ></div></input> </td>
						
						<td > <input  type="text" class="form-control"   name="ON_DUTY" 
						id="ON_DUTY'.$ID.'"placeholder="ON DUTY DAYS" value="'.$row['ON_DUTY'].'" <div style="width:80%;" ></div></input> </td>
						
						<td > <input  type="text" class="form-control"  name="ABSENT"
						id="ABSENT'.$ID.'"placeholder="ABSENT Days" value="'.$row['ABSENT_DAYS'].'"<div style="width:80%;" ></div></input></td>
						
						<td > <input  type="text" class="form-control"  name="OT"
						id="OT'.$ID.'"placeholder="OVER TIME" value="'.$row['O_T'].'"<div style="width:80%;" ></div></input></td>
						
						<td > <input  type="text" class="form-control"  name="WO_COUNT"
						id="WO_COUNT'.$ID.'"placeholder="WEEK OFFS" value="'.$WEEK_OFF_CL.'"<div style="width:80%;" ></div></input></td>
						
						<td > <input  type="text" class="form-control"  name="CL_COUNT"
						id="CL_COUNT'.$ID.'"placeholder="CASUAL LEAVES" value="'.$row['C_L'].'"<div style="width:80%;" ></div></input></td>
						
						<td > <input  type="text" class="form-control"  name="SL_COUNT"
						id="SL_COUNT'.$ID.'"placeholder="SICK LEAVES" value="'.$row['S_L'].'" <div style="width:80%;" ></div></input></td>
						
						<td > <input  type="text" READONLY class="form-control"  name="TOTAL"
						id="TOTAL'.$ID.'"placeholder="Total Working Days" value="'.$row['TOTAL_WORKNG_DAYS'].'" <div style="width:80%;" ></div> </input></td>
						
						  
                          <td>			    
                            <button class="btn btn-success btn-sm btn-flat edit" data-id='.$ID.'><i class="fa fa-edit"></i> Update</button>
                        </td>
                        </tr>'
                      ;
                    }
					
					 //$data .='</tbody></table>';
					echo $data .='</tbody></table>';
					
					$sql_update;
}


?>
