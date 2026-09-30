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


  $SqlTimeDiff="SELECT *,TIMESTAMPDIFF(MINUTE,TimeIn,TimeOut) as WorkMin FROM `HrmDailyAttendnessLog` WHERE 
 month(Date)='$CurrentMonth' and year(Date)='$CurrentYear' and
( TimeIn !='00:00:00' and TimeOut !='00:00:00')"; 
 $StmtTimeDiff=$iconn->query($SqlTimeDiff);
$TotalReords = mysqli_num_rows($StmtTimeDiff); 
 while($row=$StmtTimeDiff->fetch_array()){

  $AttID=$row['AttID']; 
  $BioMetricID = $row['BioMetricID'];
  $Status = $row['Status'];
  $BreakTimeIn = $row['BreakTimeIn'];
  $BreakTimeOut = $row['BreakTimeOut'];
  $ShiftName = $row['ShiftName'];
  $ShiftID = $row['Shift'];
  $ShiftRotation = $row['ShiftRotation'];
  $ShiftRotation = $row['NextDayOut'];

  $TimeIn = date('h:i A', strtotime($row['TimeIn']));
  $TimeOut = date('h:i A', strtotime($row['TimeOut']));

  $loginTime = strtotime($TimeIn);
  $loginOut = strtotime($TimeOut);


  if ($ShiftRotation == 1) {
    $row['TimeOut'];
    $NSFT_DATE = date('Y-m-d h:i A', strtotime($row['Date'] . $row['TimeOut'] . ' +1 day'));
    $NSPFT_DATE = date('Y-m-d h:i A', strtotime($row['Date'] . $row['TimeIn']));

    $NSFT_DATE = strtotime($NSFT_DATE);
    $NSPFT_DATE = strtotime($NSPFT_DATE);

    round(abs($NSFT_DATE - $NSPFT_DATE) / 60, 2);
    $row['WorkMin'] = round(abs($NSPFT_DATE - $NSFT_DATE) / 60, 2);

  }


  if ($loginTime > $loginOut) {
    $row['TimeOut'];
    $NSFT_DATE = date('Y-m-d h:i A', strtotime($row['Date'] . $row['TimeOut'] . ' +1 day'));
    $NSPFT_DATE = date('Y-m-d h:i A', strtotime($row['Date'] . $row['TimeIn']));

    $NSFT_DATE = strtotime($NSFT_DATE); //echo '<br>'; uma
    $NSPFT_DATE = strtotime($NSPFT_DATE);

    round(abs($NSFT_DATE - $NSPFT_DATE) / 60, 2);
    $row['WorkMin'] = round(abs($NSPFT_DATE - $NSFT_DATE) / 60, 2);
  }

  $diff = $row['WorkMin'] - $row['BreakMin'];
  $diff = $row['WorkMin'];

 
 // echo $diff;
 //  echo "<br/>";
 


 
  $SqlTimeUpdate="UPDATE HrmDailyAttendnessLog set Minutes='$diff'  WHERE AttID='$AttID'";
 
//  echo '<br>';
  $StmtTimeUpdate=$iconn->query($SqlTimeUpdate);
  if($StmtTimeUpdate){
   ++$Count;
  }
  
 }
 
 echo $Count .'/'.$TotalReords .' Records Updated';

?>


<?php
exit;
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


  $SqlTimeDiff="SELECT *,TIMESTAMPDIFF(MINUTE,TimeIn,TimeOut) as CalMinutes FROM `HrmDailyAttendnessLog` WHERE 
 month(Date)='$CurrentMonth' and year(Date)='$CurrentYear' and
( TimeIn !='00:00:00' and TimeOut !='00:00:00') "; 
 $StmtTimeDiff=$iconn->query($SqlTimeDiff);
$TotalReords = mysqli_num_rows($StmtTimeDiff); 
 while($RowTimeData=$StmtTimeDiff->fetch_array()){
 
 $AttID=$RowTimeData['AttID'];
 $CalMinutes=$RowTimeData['CalMinutes'];
 $Shift=$RowTimeData['Shift'];
 $NextDayOut=$RowTimeData['NextDayOut'];
 $TimeIn="2022-01-01 ".$RowTimeData['TimeIn'];
 $TimeOut="2022-01-02 ".$RowTimeData['TimeOut'];
 
 if($NextDayOut==1){
$Time1= strtotime($TimeIn);
$Time2= strtotime($TimeOut);
$CalMinutes=round(abs($Time1 - $Time2) / 60,2);
   
 }
 
 
 


 
   $SqlTimeUpdate="UPDATE HrmDailyAttendnessLog set Minutes='$CalMinutes'  WHERE AttID='$AttID'";
 
//  echo '<br>';
  $StmtTimeUpdate=$iconn->query($SqlTimeUpdate);
  if($StmtTimeUpdate){
   ++$Count;
  }
  
 }
 
 echo $Count .'/'.$TotalReords .' Records Updated';

?>