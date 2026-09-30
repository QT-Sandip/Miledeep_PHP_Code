<?php 
set_time_limit(0);
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
$current_date = date("Y-m-d");

// Single query to update all employees inactive for more than 30 days
$update_sql = "
    UPDATE `HrmEmployeeDetails` e
    INNER JOIN (
        SELECT `BioMetricID`, MAX(`Date`) AS LastAttendance
        FROM `HrmDailyAttendnessLog`
        GROUP BY `BioMetricID`
    ) a ON e.`BioMetricID` = a.`BioMetricID`
    SET e.`EmpStatus` = 0
    WHERE e.`EmpStatus` = 1
      AND DATEDIFF('$current_date', a.LastAttendance) > 30
";

if ($iconn->query($update_sql)) {
    echo "Successfully updated inactive employees.";
} else {
    echo "Error updating records: " . $iconn->error;
}
