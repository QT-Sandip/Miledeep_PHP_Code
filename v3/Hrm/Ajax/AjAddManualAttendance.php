<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$ClientConfigData = $_SESSION['ClientConfigData'];
$hrms_manual_attendance = $ClientConfigData['hrms_manual_attendance'];

$TimeIn=$_POST['TimeIn'];
$TimeOut=$_POST['TimeOut'];
$Date=$_POST['Date'];
$Type=$_POST['Type'];
$EmpBioID=$_POST['EmpBioID'];

if($hrms_manual_attendance){

	IF($Type=='AddManualAttd'){	

		$SqlCount="SELECT count(*) as  Count FROM `HrmDailyAttendnessLog` WHERE BioMetricID='$EmpBioID' and Date='$Date'"; 
		//echo "<div>$SqlCount</div>";
		$StmtCount=$iconn->query($SqlCount);
		$RowCount=$StmtCount->fetch_array();
		$Count=$RowCount['Count']; 
		$DateBioID = str_replace('-', '', $Date);
        $DateBioID = $DateBioID . $EmpBioID;
		if($Count==0){

			$sql="INSERT INTO `HrmDailyAttendnessLog`(`DateBioID`,`BioMetricID`, `Date`, `TimeIn`, `TimeOut`, `IsManual`) 
			VALUES ('$DateBioID','$EmpBioID','$Date','$TimeIn','$TimeOut','1')"; 

		}else{		 
		$oldAttManual = legacyAuditFetchRowById('HrmDailyAttendnessLog', 'DateBioID', $DateBioID, 'Hrm');
			$sql="UPDATE `HrmDailyAttendnessLog` SET IsManual = 1, `Date`='$Date',`TimeIn`='$TimeIn',`TimeOut`='$TimeOut' WHERE BioMetricID='$EmpBioID' and Date='$Date'"; 

		}
		//echo $sql; exit;
		$stmt=$iconn->query($sql);

		$sql_backup = "INSERT INTO `HrmDailyAttendnessBackUp`
               (`DateBioID`,`BioMetricID`, `Date`, `TimeIn`, `Status`)
               VALUES ('$DateBioID','$EmpBioID', '$Date', '$TimeIn', '0')";
        $stmt=$iconn->query($sql_backup);

		$sql_backup2 = "INSERT INTO `HrmDailyAttendnessBackUp`
               (`DateBioID`,`BioMetricID`, `Date`, `TimeIn`, `Status`)
               VALUES ('$DateBioID','$EmpBioID', '$Date', '$TimeOut', '1')";
        $stmt=$iconn->query($sql_backup2);

		$sqlUpdateMinutes = "UPDATE HrmDailyAttendnessLog
        SET Minutes =
            CASE        
                WHEN TimeOut < TimeIn THEN
                    TIMESTAMPDIFF(
                        MINUTE,
                        CONCAT(`Date`, ' ', TimeIn),
                        CONCAT(DATE_ADD(`Date`, INTERVAL 1 DAY), ' ', TimeOut)
                    )        
                ELSE
                    TIMESTAMPDIFF(
                        MINUTE,
                        CONCAT(`Date`, ' ', TimeIn),
                        CONCAT(`Date`, ' ', TimeOut)
                    )
            END
        WHERE
            TimeIn  IS NOT NULL
            AND TimeOut IS NOT NULL
            AND TimeIn  <> '00:00:00'
            AND TimeOut <> '00:00:00' 
            AND BioMetricID='$EmpBioID' and Date='$Date'
            ";
        $oldAttMinutes = legacyAuditFetchRowById('HrmDailyAttendnessLog', 'DateBioID', $DateBioID, 'Hrm');
        $iconn->query($sqlUpdateMinutes);
        legacyAuditLogUpdateById('HrmDailyAttendnessLog', 'DateBioID', $DateBioID, $oldAttMinutes, $_POST, ['EmpBioID' => $EmpBioID, 'Remarks' => 'Manual attendance minutes calculated'], 'Hrm');
		if($stmt){
			if($Count==0){
				legacyAuditLogInsertById('HrmDailyAttendnessLog', 'DateBioID', $DateBioID, $_POST, ['EmpBioID' => $EmpBioID, 'Remarks' => 'Manual attendance added'], 'Hrm');
			}else{
				legacyAuditLogUpdateById('HrmDailyAttendnessLog', 'DateBioID', $DateBioID, $oldAttManual, $_POST, ['EmpBioID' => $EmpBioID, 'Remarks' => 'Manual attendance updated'], 'Hrm');
			}
			$row['sts']='success';
			$row['msg']=' Success';
			echo json_encode($row);
		}else{
			$row['sts']='Failed';
			$row['msg']='Failed';
			echo json_encode($row);
		}
	 
	
	}
}else{
	$row['sts']='Failed';
	$row['msg']='Failed';
	 echo json_encode($row);
 }
?>
