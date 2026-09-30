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

	$SqlBShiftEntries="SELECT * FROM `HrmDailyAttendnessLog` WHERE MONTH(Date)='$CurrentMonth' 
					and YEAR(Date)='$CurrentYear' and TimeOut < TimeIn and Shift=3  and NextDayOut=0 
					 ORDER BY BioMetricID ASC, 
					Date ASC";
	$StmtBShiftEntries=$iconn->query($SqlBShiftEntries);

 while($RowAttData=$StmtBShiftEntries->fetch_array()){
 
 	$Date=$RowAttData['Date'];
	$NextDate=date('Y-m-d',(strtotime ( '+1 day' , strtotime ( $Date) ) ));
	$BioMetricID=$RowAttData['BioMetricID'];
	
	echo $SqlBk="SELECT *,(SELECT COALESCE(MIN(TimeIn),0) from HrmDailyAttendnessBackUp as bkup 
	WHERE bkup.DateBioID=log.DateBioID and bkup.Status=1 and bkup.TimeIn BETWEEN '00:00:00' and '8:00:00') as TimeOut 
				FROM `HrmDailyAttendnessLog` as log WHERE log.Date='$NextDate' and log.BioMetricID='$BioMetricID'";

	 $StmtBk=$iconn->query($SqlBk);
	 $RowData=$StmtBk->fetch_array();
     $Time=$RowData['TimeOut'];  echo '<br>';


 
// if($Time!=0){
 
 echo $SqlUpdate="UPDATE HrmDailyAttendnessLog set TimeOut='$Time',NextDayOut=1  WHERE BioMetricID='$BioMetricID' and Date='$Date'";
  $StmtHmHolidays=$iconn->query($SqlUpdate);
// }
  echo '<br>';
  
 /*echo  $SqlUpdate="UPDATE HrmDailyAttendnessLog set TimeOut='0'  WHERE BioMetricID='$BioMetricID'
   and Date='$NextDate' and  TimeIn ='0'";
    echo '<br>';
  $StmtHmHolidays=$iconn->query($SqlUpdate);*/
 }
 
 //}



?>