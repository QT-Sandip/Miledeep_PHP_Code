<?php


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$days_ago = date('Y-m-d', strtotime('-3 days', strtotime('2021-09-04')));
include ($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include ($cp . "Includes/IncludePage.php");
$QueryParms = $_GET['Q'];
if ($QueryParms != '') {
  $QueryParms = urlDecode64($QueryParms);
  $queries = array();
  parse_str($QueryParms, $_GET);
}

//  print_r($_GET);
$CurrentMonth = $_GET['Month'];
$CurrentYear = $_GET['Year'];
$DeptID = $_GET['DeptID'];
$Date = $_GET['Date'];






$SqlScriptLog = "SELECT * FROM `ScriptLog` WHERE ReportId='1' and Month='$CurrentMonth' and Year='$CurrentYear'";
$StmtScriptLog = $iconn->query($SqlScriptLog);
$RowScriptLog = $StmtScriptLog->fetch_array();
$BioMetricID = $RowScriptLog['LastUpdatedBioId'];

if ($DeptID == '') {
  //$qry="and AttLog.BioMetricID >$BioMetricID"; 	
} else {
  $qry = "and DeptID='$DeptID'";
}

$SqlTimeDiff = "SELECT DISTINCT(AttLog.BioMetricID) FROM `HrmDailyAttendnessLog`  as AttLog
					LEFT join HrmEmployeeDetails as HED on HED.BioMetricID=AttLog.BioMetricID
					WHERE AttLog.Date='$Date' 
					$qry ORDER by AttLog.BioMetricID ASC,Date Asc";
$StmtTimeDiff = $iconn->query($SqlTimeDiff);
$TotalRecords = mysqli_num_rows($StmtTimeDiff);
$Count = 0;
if ($TotalRecords > 0) {

  while ($RowTimeData = $StmtTimeDiff->fetch_array()) {
    $SqlTempArray = array();

    $AttID = $RowTimeData['AttID'];
    $BioMetricID = $RowTimeData['BioMetricID'];
    //$Date=$RowTimeData['Date']; 
    $SqlBckTimeDate = "select ai.BioMetricID,ai.DateBioID,ai.Date,ai.Status,COALESCE(ai.DateBioID ,0) as Ref,
(SELECT COALESCE(min(TimeIn),'0') from HrmDailyAttendnessBackUp WHERE BioMetricID=ai.BioMetricID and Date=ai.Date and Status=0 ) as TimeIn,
(SELECT COALESCE(max(TimeIn),'0') from HrmDailyAttendnessBackUp WHERE BioMetricID=ai.BioMetricID and Date=ai.Date and Status=1 ) as TimeOut,
(SELECT AttID from HrmDailyAttendnessLog WHERE DateBioID=ai.DateBioID  limit 1 ) as AttID,
count(*) as count,ai.DeviceID
from HrmDailyAttendnessBackUp as ai WHERE ai.Date='$Date' and ai.Date='$Date'
and ai.BioMetricID='$BioMetricID'
group by BioMetricID,Date; ";


    $StmtBckTimeDate = $iconn->query($SqlBckTimeDate);
    while ($RowBckTimeDate = $StmtBckTimeDate->fetch_array()) {

      $AttID = $RowBckTimeDate['AttID'];
      $TimeIn = $RowBckTimeDate['TimeIn'];
      $TimeOut = $RowBckTimeDate['TimeOut'];
      $Date = $RowBckTimeDate['Date'];
      $BioMetricID = $RowBckTimeDate['BioMetricID'];
      $DateBioID = $RowBckTimeDate['DateBioID'];
      $DeviceID = $RowBckTimeDate['DeviceID'];
      $Shift = 0;
      $CStatus = 0;


      $SqlTempArray[] = "('$AttID','$BioMetricID','$DateBioID','$Date','$TimeIn',
 '$TimeOut','$DeviceID','$Shift',0)";


    }

    $SqlTempValues = implode(',', $SqlTempArray);


    $SqlTimeUpdate = "INSERT into `HrmDailyAttendnessLog` (AttID,`BioMetricID`,`DateBioID`, 
 `Date`, `TimeIn`, `TimeOut`,`DeviceID`,
 Shift,CStatus)
VALUES  $SqlTempValues 
ON DUPLICATE KEY UPDATE 
BioMetricID = VALUES(BioMetricID),
DateBioID = VALUES(DateBioID),
Date = VALUES(Date),
DeviceID = VALUES(DeviceID),
TimeIn = VALUES(TimeIn),
TimeOut=VALUES(TimeOut),
Shift=VALUES(Shift),
CStatus=VALUES(CStatus);";


    $StmtTimeUpdate = $iconn->query($SqlTimeUpdate);

    if ($StmtTimeUpdate) {
      date_default_timezone_set('Asia/Kolkata');
      $ModifiedDateTime = date('Y-m-d h:i:s');

      $SqlScript = "UPDATE `ScriptLog` SET `LastUpdatedBioId`='$BioMetricID',
	 `ModifiedDateTime`='$ModifiedDateTime',`Month`='$CurrentMonth',`Year`='$CurrentYear' WHERE `ReportId`='1'";
      $StmtScript = $iconn->query($SqlScript);
      ++$Count;
      echo $Count . '/' . $TotalRecords . ' Records Updated';
      echo '<br>';
    }

  }
} else {
  echo 'No data currently available to update';
}

?>