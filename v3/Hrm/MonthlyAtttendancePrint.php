<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
?>
<!--<link href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css" rel="stylesheet" />-->
<link href="https://cdn.datatables.net/fixedcolumns/3.3.0/css/fixedColumns.dataTables.min.css" rel="stylesheet" />
<?php

$DeptID=$_GET['Dept'];
$SubDept=$_GET['SubDept'];
$Month=$_GET['Month'];
$Year=$_GET['Year'];
$Type=$_GET['Type'];
$Roll=$_GET['Roll'];
$EmpCategory=$_GET['EmpCategory'];
$WorkerCategory=$_GET['WorkerCategory'];

$SqlWorkerCategory = "SELECT * FROM `EmpWorkerSubCategories`";
$SqlWorkerCategoryResult = $iconn->query($SqlWorkerCategory);
$WorkerSubCategorylist = [];
while ($RowWorkerType = $SqlWorkerCategoryResult->fetch_array()){
    $WorkerSubCategorylist[$RowWorkerType['CategoryID']] = $RowWorkerType['CategoryName'];
}

 $date_start=$Year."-".$Month."-01";
	$date_end=$Year."-".$Month."-31 23:59:59";
$dateObj   = DateTime::createFromFormat('!m', $Month);
$MonthName = $dateObj->format('F');
$content1 .= '<h3 align="center"> Company Name ('. $MonthName.'- '. $Year.') <br>
FORM-25</h3>';
echo $content1;



	$data=' <table id="MonAttTable" class="table table-bordered" border="1" cellspacing="0" cellpadding="5"
	style="background:#fff;margin-top:6px !important;margin-bottom:-6px !important; height:100px">
   <thead>
   <thead>
                  <th >Name</th>
                  <th >BID</th>
                  <th >Category</th>
                  <th >SubCategory</th>
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

/*

$data .="<th>Presents</th>";
$data .="<th>Week Offs</th>";
$data .="<th>Public Holidays</th>";
$data .="<th>Leaves</th>";
$data .="<th>Absents</th>";

*/


 //ECHO $DatesArray=implode(',', $list);


 //print_r($list);
 //exit;
 $Leaves=0;


$data .=' </thead>
                <tbody>';

				if($SubDept !='' ){
					  $sql = "SELECT * FROM HrmEmployeeDetails where SubDeptID='$SubDept' and EmpStatus='1'";
				}else if($DeptID !='' ){
					 $sql = "SELECT * FROM HrmEmployeeDetails where DeptID = '$DeptID' and EmpStatus='1'";
				}else {
					  $sql = "SELECT * FROM HrmEmployeeDetails where EmpStatus='1'";
				}

				//$sql .= " and EmpCategory='$Roll'";

				if(!empty($EmpCategory)){
					$sql .= " and EmpCategory='$EmpCategory'";
				}
				if(!empty($WorkerCategory)){
					$sql .= " and WorkerSubCategory='$WorkerCategory'";
				}

				//echo $sql;
                $query = $iconn->query($sql);

                    while($row = $query->fetch_assoc()){

					$BioMetricID=$row['BioMetricID'];


						$SqlWeekOffCount="SELECT COUNT(*) as WeekOff FROM `HrmDailyAttendnessLog` WHERE  BioMetricID='$BioMetricID'
						and month(Date)='$Month' and year(Date)='$Year' and Status=3";
						$StmtWeekOffCount = $iconn->query($SqlWeekOffCount);
						$RowWeekOff = $StmtWeekOffCount->fetch_assoc();
						$WeekOff=$RowWeekOff['WeekOff'];

						if($WeekOff==0){
						//	continue;
						}




                      $status = ($row['status'])?'<span class="label label-warning pull-right">ontime</span>':
					  '<span class="label label-danger pull-right">late</span>';
                      $data .="<tr>
							<td >".$row['EmpName']."</td>
							<td >".$row['BioMetricID']."</td>
							<td >".$row['EmpCategory']."</td>
							<td >".$WorkerSubCategorylist[$row['WorkerSubCategory']]."</td>
						   ";
							$BioMetricID =$row['BioMetricID'];
							$Presents=0;
				 
				// foreach($list as $value){
					
					$value=$list[0];
					  
					$date_start=$value;
					$date_end=$value.' 23:59:59';
					$date_end=count($list)-1;
					$date_end=$list[$date_end].' 23:59:59';
				 
				  $sql = "SELECT * FROM $HrmDailyAttendnessLog where BioMetricID='$BioMetricID' and
				 Date BETWEEN '$date_start' AND '$date_end' order by Date asc"; 
				 
				 
				 
				  $sql = "select * from HrmMonthDays d
							left join ( SELECT * from HrmDailyAttendnessLog  WHERE Month(Date)='$Month' and Year(Date)='$Year'  
							and BioMetricID='$BioMetricID' GROUP by Date ) t on t.Date = d.Date
							WHERE d.Month='$Month' and d.Year='$Year'";

				 
				
				$query_m = $iconn->query($sql);
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
					
					if($TimeIn =='00:00:00'){
						$TimeInText="<span style=color:red><b>".SUBSTR($TimeIn,0,5)."</b></span>";
					}else{
						$TimeInText="<span style=color:green><b>".SUBSTR($TimeIn,0,5)."</b></span>";
					}
					
					if($TimeOut =='00:00:00'){
						$TimeOutText="<span style=color:red><b>".SUBSTR($TimeOut,0,5)."</b></span>";
					}else{
						$TimeOutText="<span style=color:green><b>".SUBSTR($TimeOut,0,5)."</b></span>";
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
						 $data.="<td><span style=color:green><b>P</b></span></td>";
						}else if(in_array($date,$PublicHoliday)){
							$data.= "<td><span style=color:orange><b>PH</b></span></td>";
						}else if($Status==2){
							$data.="<td><span style=color:red><b>A</b></span></td>";
						}else if($Status==4){
							$data.="<td><span style=color:red><b>L</b></span></td>";
						}else if($Status==5){
						 $data.="<td><span style=color:red><b>C-OFF</b></span></td>"; 
						}else if($Status==6){
						 $data.="<td><span style=color:green><b>P</b></span></td>";
						}else if($ATT_AB){
							$data.="<td><span style=color:red><b>A</b></span></td>";						   
						}else if(!$ATT_AB){							
							// $data.= "<td>".$TimeInText."<br>".$TimeOutText."</td>";

							$data.="<td><span style=color:green><b>P</b></span></td>";							 
						}  
						
						/*
						else if($ame_day=='Sun'){
							$data.= "<td><span style=color:blue><b>H</b></span></td>";
						}else{
							$data.="<td><span style=color:red><b>A</b></span></td>";
						}*/
						
					
					                             
					
				 }
$WeekOffs=round($Presents/6);	
$TotalPayDays=$Presents+$WeekOffs+$Holidays+$Leaves;
if($DaysInMonth>$TotalPayDays){
$Absents=$DaysInMonth-$TotalPayDays;
}else{
	$Absents=0;
}

/*				 
$data .="<td>".$Presents."</td>";
$data .="<td>".$WeekOffs."</td>";
$data .="<td>".$Holidays."</td>";
$data .="<td>".$Leaves."</td>";
$data .="<td>".$Absents."</td>";
*/
				 
                        $data.="</tr>";
                    }
					
					echo $data .='</tbody></table>';



?>

<script src="https://cdn.datatables.net/fixedcolumns/3.3.0/js/dataTables.fixedColumns.min.js"></script>
<script>
$(document).ready( function () {
    // $('#example1').DataTable();
    var table = $('#MonAttTable').DataTable( {
        scrollY:"400px",
         scrollX:true,
          //sScrollX: "100%",
  sScrollXInner: "150%", 
        // scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true
        /*"columnDefs": [
            { "width": "20%", "targets": 0 }
          ]*/
    } );
} );
</script>
