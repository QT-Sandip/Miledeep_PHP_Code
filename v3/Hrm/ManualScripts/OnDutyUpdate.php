<?php

 $days_ago = date('Y-m-d', strtotime('-3 days', strtotime('2021-09-04')));
 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");
include ($cp."Includes/IncludePage.php");
$QueryParms=$_GET['Q']; 
if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
$CurrentMonth=$_GET['Month'];
$CurrentYear=$_GET['Year'];

	$SqlOnDutyTotal="SELECT BioMetricID,SUM(DaysTotal) AS TotalOnDuties 
	FROM `HrmOnDutyRegister`
    left join HrmEmployeeDetails on HrmEmployeeDetails.EmpID=HrmOnDutyRegister.EmpID	
	WHERE MONTH(StartDate)='$CurrentMonth' and YEAR(StartDate)='$CurrentYear'
	and MONTH(EndDate)='$CurrentMonth' ORDER BY EmpID ASC, 
					Date ASC";
	$StmtOnDutyTotal=$iconn->query($SqlOnDutyTotal);

 while($RowOnDutyTotal=$StmtOnDutyTotal->fetch_array()){
    
	$TotalOnDuties=$RowOnDutyTotal['TotalOnDuties'];
	$EmpID=$RowOnDutyTotal['EmpID'];
	$BioMetricID=$RowOnDutyTotal['BioMetricID'];
	
 	/*$Date=$RowAttData['Date'];
	$NextDate=date('Y-m-d',(strtotime ( '+1 day' , strtotime ( $Date) ) ));
	$BioMetricID=$RowAttData['BioMetricID'];
	*/

 echo $SqlUpdate="UPDATE HrmMonthlySalaryDetails
 set MonthlyTotalOnDuties='$TotalOnDuties'
 WHERE BioMetricID='$BioMetricID' 
 and Month='$CurrentMonth'and Year='$CurrentYear'";
  $StmtHmHolidays=$iconn->query($SqlUpdate);

  echo '<br>';


 
 }
 
 


?>