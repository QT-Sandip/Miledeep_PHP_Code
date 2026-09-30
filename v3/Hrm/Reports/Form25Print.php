<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");
?>
<!--<link href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css" rel="stylesheet" />-->
<link href="https://cdn.datatables.net/fixedcolumns/3.3.0/css/fixedColumns.dataTables.min.css" rel="stylesheet" />
<?php
$ClientDtls="SELECT * FROM `AdminClientDetails`";
$StmtClientDtls= $iconn->query($ClientDtls); 
$RowsClientDtls = $StmtClientDtls->fetch_assoc();


 $QueryParms=$_GET['Q']; 

if($QueryParms!=''){
   $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }

   $DeptID=$_GET['DeptID']; 
  $SubDeptID=$_GET['SubDeptID'];  
  $Month=$_GET['Month'];  
  $Year=$_GET['Year'];  
  $BranchID=$_GET['BranchID'];  
  $Category=$_GET['Category'];  
  
  //echo $BranchID; exit;

$Type=$_GET['Type'];
$Roll=$_GET['Roll'];

$SqlBranchName="SELECT BranchCode,BranchName,BranchAddress FROM `HrmBranchMaster` WHERE BranchID='$BranchID'";
$stmtBranchName=$iconn->query($SqlBranchName);
$RowBranch=$stmtBranchName->fetch_array();

$BranchCode=$RowBranch['BranchCode'];
$BranchName=$RowBranch['BranchName'];
$BranchAddress=$RowBranch['BranchAddress'];

$Heading=$BranchName.",".$BranchCode.",".$BranchAddress;	
 $date_start=$Year."-".$Month."-01";
	$date_end=$Year."-".$Month."-31 23:59:59";
$dateObj   = DateTime::createFromFormat('!m', $Month);
$MonthName = $dateObj->format('F');
$content1 .= '<h3 align="center"> FORM-25 (Muster Roll)-'.$Category.'<BR>
 '.$Heading.' <br> ('. $MonthName.'- '. $Year.') 
</h3>';
echo $content1;

	
	
	$data=' <table id="MonAttTable" class="table table-bordered" border="1" cellspacing="0" cellpadding="5"
	style="background:#fff;margin-top:6px !important;margin-bottom:-6px !important; height:100px">
   <thead>
   <thead>
                  <th >S.No</th>
                  <th >Name</th>
                  <th >BID</th> 
                 
                 ';
               
				
				$list=array();


 $SqlHolidays = "SELECT *  FROM `HrmHolidays` WHERE Date BETWEEN 
 '$date_start' and '$date_end'";
 $query = $iconn->query($SqlHolidays);
 $Holidays=mysqli_num_rows($query);
 
 while($HolidaysRows = $query->fetch_assoc()){
	$PublicHoliday[]=$HolidaysRows['Date']; 
 }

$DaysInMonth=cal_days_in_month(CAL_GREGORIAN,$Month,$Year);
for($d=1; $d<=$DaysInMonth; $d++){
    $time=mktime(12, 0, 0, $Month, $d, $Year);          
    if (date('m', $time)==$Month)       
        $date=date('d', $time);
        $day=date('D', $time);
		 $CheckDate=date('Y-m-d', $time);
        if(in_array($CheckDate,$PublicHoliday,true)){
			$Ph="<span style=color:red><b>PH</b></span>";
		}else{
			$Ph="";
		}
        $list[]=date('Y-m-d', $time);
		$data .="<th>".$d."<br>".$day."<br>".$Ph."</th>";
		
}

		$value=$list[0];
		  
		$date_start=$value;
		$date_end=$value.' 23:59:59';
		$date_end=count($list)-1;
		$date_end=$list[$date_end].' 23:59:59';

$data .="<th>Total<br>Present<br>Days </th>";
 $Leaves=0;

		
$data .=' </thead>
                <tbody>';		
				if($BranchID!=''){
				$BranchQuery="and HED.BranchID='$BranchID'";
				}
				if($Category!=''){
				$CategoryQuery="and HED.EmpCategory='$Category'";
				}
				if($date_start!=''){
				$JoiningDate="and HED.JoiningDate<='$date_start'";	
				}else{
					$JoiningDate="";
				}
				
				
				if($SubDeptID !='' ){					  
					  $sql = "SELECT * FROM HrmAuditEmployeeDetails HED
LEFT JOIN HrmContractorList HCL ON HED.ContractorID=HCL.ContractorID 
LEFT JOIN HrmDepartments HD  ON HD.DeptID=HED.DeptID
LEFT JOIN HrmSubDepartments HSD  ON HSD.SubDeptID=HED.SubDeptID
LEFT JOIN HrmDesignations HDG  ON HDG.DesigID=HED.DesigID

					  where HED.SubDeptID='$SubDeptID' and EmpStatus='1' $BranchQuery $CategoryQuery $JoiningDate";			
				}else if($DeptID !='' ){
					 $sql = "SELECT * FROM HrmAuditEmployeeDetails HED
LEFT JOIN HrmContractorList HCL ON HED.ContractorID=HCL.ContractorID 
LEFT JOIN HrmDepartments HD  ON HD.DeptID=HED.DeptID
LEFT JOIN HrmSubDepartments HSD  ON HSD.SubDeptID=HED.SubDeptID
LEFT JOIN HrmDesignations HDG  ON HDG.DesigID=HED.DesigID 
where HED.DeptID = '$DeptID' and EmpStatus='1' $BranchQuery $CategoryQuery $JoiningDate";		 
				}else {
					  $sql = "SELECT EmpName,BioMetricID,DepartmentName,SubDeptName,Designation,ContractorName FROM HrmAuditEmployeeDetails HED
LEFT JOIN HrmContractorList HCL ON HED.ContractorID=HCL.ContractorID 
LEFT JOIN HrmDepartments HD  ON HD.DeptID=HED.DeptID
LEFT JOIN HrmSubDepartments HSD  ON HSD.SubDeptID=HED.SubDeptID
LEFT JOIN HrmDesignations HDG  ON HDG.DesigID=HED.DesigID
					  
					  where EmpStatus='1' $BranchQuery $CategoryQuery $JoiningDate";		
				} 
				
				//echo $BranchQuery; exit;

$SNo=0;
$query = $iconn->query($sql);


                   
                    while($row = $query->fetch_assoc()){
						
					$BioMetricID=$row['BioMetricID'];
					$cust_present_var = 0;
						
						
						
						
                      $status = ($row['status'])?'<span class="label label-warning pull-right">ontime</span>':
					  '<span class="label label-danger pull-right">late</span>';
                      $data .="<tr>
							<td >".++$SNo."</td>
							<td >".$row['EmpName']."</td>
							<td >".$row['BioMetricID']."</td>
							
						   ";
							$BioMetricID =$row['BioMetricID'];
							$Presents=0;
				 
				// foreach($list as $value){
					
				 
				  $sql = "SELECT * FROM HrmAuditAttendnessAuditLog where BioMetricID='$BioMetricID' and
				 Date BETWEEN '$date_start' AND '$date_end' order by Date asc"; 
				 
				 
				 
				  $sql = "select * from HrmMonthDays d
							left join ( SELECT * from HrmAuditAttendnessAuditLog  WHERE Month(Date)='$Month' and Year(Date)='$Year'  
							and BioMetricID='$BioMetricID' GROUP by Date ) t on t.Date = d.Date
							WHERE d.Month='$Month' and d.Year='$Year'";

				 
				
				$query_m = $iconn->query($sql);
				
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
				
				
				  while($row_m = $query_m->fetch_assoc()){
					$presents=array();
					$time_dif=array();
					
					
					if($row_m['AttID']==''){					
						$ATT_AB=true;
					}else{
						$ATT_AB=false;
						$Presents++;
					}
					
					$TimeIn = $row_m['TimeIn'];
					$TimeOut = $row_m['TimeOut'];
					$Status = $row_m['Status'];
					$OnTime = $row_m['OnTime'];
					
					$Time1= strtotime($TimeIn);
					$Time2= strtotime($TimeOut);
					//$WorkHours=round(abs($Time1 - $Time2) / (60*60),2);
					$WorkHours=round(abs($Time1 - $Time2) / (60),2);
					$WorkMin=$WorkHours%60;
					$WorkHours=$WorkHours-$WorkMin;
					$WorkHours=round($WorkHours/60);
					
					$WorkHours="(".$WorkHours.":".$WorkMin ."M)";
					
					if($TimeIn =='00:00:00'){
						$TimeInText="<span style=color:red>".SUBSTR($TimeIn,0,5)."</span>";
					}else{
						$TimeInText="<span style=color:green>".SUBSTR($TimeIn,0,5)."</span>";
					}
					
					if($TimeOut =='00:00:00'){
						$TimeOutText="<span style=color:red>".SUBSTR($TimeOut,0,5)."</span>";
					}else{
						$TimeOutText="<span style=color:green>".SUBSTR($TimeOut,0,5)."</span>";
					}
					
					
					
					
					
					$vaalue = 'P';
					
					
						$date=$row_m['Date'];
						$timestamp = strtotime($date);
						$date = date('Y-m-d', $timestamp);
						
			

					 $present_date = date('d');
					 $present_month = date('m');
					 
					 
					$ame_day =date('D', strtotime($value));
						 $e_day =date('d', strtotime($value));
						 
						 if($present_month == $Month &&  $present_date < $e_day){ 
							$data.="<td>--</td>";
						} if($Status==3){
							$data.= "<td><span style=color:blue><b>WO</b></span></td>";
							
						}else if($Status==6){
						$data.="<td><span style=color:green;allign=center><b>P</b></span></td>";
						$cust_present_var++;
						}else if(in_array($date,$PublicHoliday)){
							$data.= "<td><span style=color:orange><b>PH</b></span></td>";
						}else if($Status==2){
							$data.="<td><span style=color:red><b>A</b></span></td>";
						}else if($Status==4){
							$data.="<td><span style=color:red><b>L</b></span></td>";
						}else if($Status==5){
						 $data.="<td><span style=color:red><b>C-OFF</b></span></td>";
					
						}else if($Status==6){
						 $data.="<td><span style=color:green;allign=center><b>P</b><br>".$TimeInText."-".$TimeOutText."".$WorkHours ."</span></td>";		
						 $cust_present_var++;
						}else if($ATT_AB){
							$data.="<td><span style=color:red><b>A</b></span></td>";						   
						}else if(!$ATT_AB){							
							// $data.= "<td>".$TimeInText."<br>".$TimeOutText."</td>";

							$data.="<td><span style=color:green;allign=center><b>P</b></span>
							<span style=color:blue;allign=center></span></td>";							 
							$cust_present_var++;
						}  
						

					
				 }

				 
$WageDays=$CountData['WageDays']+$CountData['PhPreDays']+$CountData['PhPreDays']
								+ $CountData['C_Offs'] +$CountData['Leaves'];				 
$data .="<td><span style=color:BLACK;allign=center><b>".$cust_present_var."</b></span></td>";
//$WeekOffs=round($Presents/6);	
$TotalPayDays=$Presents+$WeekOffs+$Holidays+$Leaves;
if($DaysInMonth>$TotalPayDays){
$Absents=$DaysInMonth-$TotalPayDays;
}else{
	$Absents=0;
}


				 
                        $data.="</tr>";
                    }
					

					
					echo $data .='</tbody></table>';



?>


