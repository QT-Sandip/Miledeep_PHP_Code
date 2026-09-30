<?php

session_start();

include "../includes/config.php";

include "../includes/Globals.php";
 $B_TYPE=$_GET['TYPE'];
 $BAR_CODE_ID=$_GET['BID'];
 $LOT_ID=$_GET['ID'];

  //$date=$_GET['date']; 
  $FromDate=$_GET['FromDate']; 
  $ToDate=$_GET['ToDate'];  
  $month_name= date("F", strtotime($FromDate)); 


 
$file_name='COOKED PACKING -'.$FromDate.'-'.$ToDate.'.pdf';
	global $B_TYPE;
	 
	$date_end=$ToDate.' 23:59:59';  
	
	
	if($B_TYPE == 1){ 
	
	$query.="and imd.id='0'";
}
 	
	 $SqlLateIns="SELECT HED.BioMetricID , HED.DeptID,HED.EmpName,HDMS.DepartmentName,HED.ShiftID,HSD.ShiftName,HSD.StartTime,HSD.GraceTime,HDAL.Date ,HDAL.TimeIn FROM `HrmEmployeeDetails` as HED 
     LEFT JOIN HrmShiftDetails AS HSD ON HSD.ShiftID=HED.ShiftID
     LEFT JOIN HrmDepartments AS HDMS ON HDMS.DeptID=HED.DeptID
     LEFT JOIN HrmDailyAttendnessLog AS HDAL ON HDAL.BioMetricID=HED.BioMetricID
     WHERE HDAL.Date BETWEEN '$FromDate' AND '$ToDate' ORDER BY DeptID"; 
			  
		//	 echo  $SqlLateIns;
			  
	 $StmtLateIns=$iconn->query($SqlLateIns);
			 	  
 
	
 
	$content='';


	$size=40;	  
	$size1=60;	


			  
			  $S_NO=1;
			  $width=11;
			  $width_s=10;
			  $width_w=$width+5;
              
			  
	$content .= '
			  <h3 align="center"> Late In Report -('.$FromDate.'-'.$ToDate.')</h3>
			  
			  
			  <table border="1" cellspacing="0" cellpadding="5">
			   <tr>     		

               <th  align="center"  width="'.$width_s.'%"><B>S No.</B></th>              
     			<th  align="center" width="'.$width.'%" ><B> BioMetric ID</B></th>
				<th  align="center"  width="'.$width.'%"><B>Employee Name</B></th>			
				<th  align="center"  width="'.$width.'%"><B>Departmet Name</B></th>			
				<th  align="center" width="'.$width.'%" ><B>Shift </B></th>
				<th  align="center" width="'.$width.'%" ><B>Date </B></th>
                <th  align="center"  width="'.$width.'%" <B>Time In </B></th>
                <th  align="center"  width="'.$width.'%" ><B>Late By</B></th>
                
               </tr> 
			   
			  '; 
			  
	
		while($RowLateIns = $StmtLateIns->fetch_array()){
					
		
		$BioMetricID=$RowLateIns['BioMetricID'];
		$DepartmentName=$RowLateIns['DepartmentName'];
		$EmployeeName=$RowLateIns['EmpName'];
		$ShiftName=$RowLateIns['ShiftName'];
		$StartTime=$RowLateIns['StartTime'];
		$GraceTime=$RowLateIns['GraceTime'];
		$TimeIn=$RowLateIns['TimeIn'];
		$PresentDate=$RowLateIns['Date'];
		$PresentDate = date("d-m-Y", strtotime($PresentDate));
		
				    $content .= 
					
					'<tr>
				 <th  align="center" width="'.$width_s.'%">'.$S_NO.'</th>

				 <th  align="center" width="'.$width.'%">'.$BioMetricID.'</th>
				 <th  align="center" width="'.$width.'%">'.$EmployeeName.'</th>
				 <th  align="center" width="'.$width.'%">'.$DepartmentName.'</th>
				 <th  align="center" width="'.$width.'%" >'.$ShiftName.'</th>
				 <th  align="center" width="'.$width.'%" >'.$PresentDate.'</th>
				 <th  align="center" width="'.$width.'%" >'.$StartTime.'</th>
				 <th  align="center" width="'.$width.'%" >'.$RowLateIns['GRADES'].'</th>
				 
				
				 
				 </tr>';

$TWeight +=$RowLateIns['NET_WT'];
 $S_NO++;			 
}
		  $content .= 
					
		 '<tr>               
		 <th  align="center" width=""></th>				               				 
               <th  align="center" ></th>				 
               <th  align="center" ></th>				 
               <th  align="center" ></th>				 
               <th  align="center" ></th>				 
               <th  align="center" ></th>				 
               <th  align="center" ></th>				 
               <th  align="center" ></th>				 

 
		 </tr>';

		$content .= '</table>';


	echo $content;	

//}

?>