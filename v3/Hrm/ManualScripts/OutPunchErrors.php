<?php
/**
 * Fix Missing Out Punches (includes night shift logic)
 * Author: [Sreenivas Mudragada]
 * Date: [04-Nov-2025]
 */

set_time_limit(0);
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

// === Get month/year from query or default ===
$QueryParms=$_GET['Q']; 
if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
  
$CurrentMonth=$_GET['Month'];
$CurrentYear=$_GET['Year'];

// Calculate dynamic date range for the month
$StartDate = date("Y-m-01", strtotime("$CurrentYear-$CurrentMonth-01"));
$EndDate   = date("Y-m-d", strtotime("$StartDate +1 month")); // exclusive

$TotalUpdated = 0;

/* --------------------------------------------------------------
   PART 1: Fix Same-Day Missing Out Punches
   -------------------------------------------------------------- */
 /*$SqlOutPunchErrors= "SELECT *,
		(SELECT COUNT(*) from HrmDailyAttendnessBackUp atl 
		 WHERE atl.BioMetricID=Log.BioMetricID and atl.Date=Log.Date and atl.Status=0 ) as Rcount
		 FROM `HrmDailyAttendnessLog` as Log WHERE  
			month(Date)='$CurrentMonth' 
			and year(Date)='$CurrentYear' 
			and TimeIn!='00:00:00' 
			and TimeOut='00:00:00' 
			GROUP BY BioMetricID,Date
			HAVING Rcount > 1			
			ORDER BY BioMetricID ASC";

	$StmtOutPunchErrors=$iconn->query($SqlOutPunchErrors);
	$TotalReords = mysqli_num_rows($StmtOutPunchErrors); 
	$Count=0;

 while($RowDDData=$StmtOutPunchErrors->fetch_array()){
	 
	  $DateBioID=$RowDDData['DateBioID'];
	  
	  $SqlTimeGap="SELECT MAX(TimeIn) as TimeOut ,TIMESTAMPDIFF(MINUTE,MIN(TimeIn),Max(TimeIn)) as MinTotal 
	  FROM `HrmDailyAttendnessBackUp` WHERE DateBioID='$DateBioID' and Status=0";
	  $StmtSqlTimeGap=$iconn->query($SqlTimeGap);
	  $RowTimeOut=$StmtSqlTimeGap->fetch_array();
	  $MinTotal=$RowTimeOut['MinTotal'];
	  $TimeOut=$RowTimeOut['TimeOut'];
	  
	  if($MinTotal >999){
		    $SqlTimeGap;
	  }
	  
		if($MinTotal >= 240 && $MinTotal < 1001){
		   $SqlUpdate="update `HrmDailyAttendnessLog` set TimeOut='$TimeOut' where DateBioID='$DateBioID' ";
			$StmtHmHolidays=$iconn->query($SqlUpdate);
			$TotalCount=++$Count;
		// echo '<br>';
		  }
	 
   }
   
   if($TotalCount>0){
	echo $TotalCount . ' Records Updated';
}
else {
		echo 'No data currently available to update';
	}*/

/* --------------------------------------------------------------
   PART 2: Fix Night Shift Cross-Day Out Punches
   -------------------------------------------------------------- */
$sqlNight = "
    SELECT BioMetricID, Date, MAX(TimeIn) AS TimeOut
    FROM HrmDailyAttendnessBackUp
    WHERE TimeIn > '00:00:00'
      AND TimeIn < '05:00:00'
      AND Status = 1
      AND Date >= '$StartDate'
      AND Date < '$EndDate'
    GROUP BY DateBioID
    ORDER BY AttID ASC
";

$resultNight = $iconn->query($sqlNight);

if ($resultNight && $resultNight->num_rows > 0) {
    while ($row = $resultNight->fetch_assoc()) {
        $previous_day = date("Y-m-d", strtotime($row['Date'] . " -1 day"));
        $bioId   = $row['BioMetricID'];
        $TimeOut = $row['TimeOut'];

        // Find matching previous day record
        $checkPrev = "
            SELECT Date, BioMetricID 
            FROM HrmDailyAttendnessLog 
            WHERE Date = '$previous_day' 
              AND BioMetricID = '$bioId'
        ";
        $resPrev = $iconn->query($checkPrev);
        $prevRow = $resPrev->fetch_assoc();

        if (!empty($prevRow)) {
            // Update regardless of existing TimeOut
            $SqlUpdateNight = "
                UPDATE HrmDailyAttendnessLog 
                SET TimeOut = '$TimeOut' 
                WHERE Date = '$previous_day' 
                  AND BioMetricID = '$bioId'
            ";
            //echo $SqlUpdateNight; echo "<br>";
            $iconn->query($SqlUpdateNight);
            $TotalUpdated++;
        }
    }
}

/* --------------------------------------------------------------
   FINAL OUTPUT
   -------------------------------------------------------------- */
if ($TotalUpdated > 0) {
    echo "<b>$TotalUpdated attendance records updated successfully.</b>";
} else {
    echo "No missing or cross-day out punches found to update.";
}

?>