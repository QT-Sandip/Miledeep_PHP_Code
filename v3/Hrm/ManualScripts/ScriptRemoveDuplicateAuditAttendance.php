<?php
  // 1. Calculate date range for the month/year to maintain performance (index-friendly)
  $startDate = "$Year-$Month-01";
  $endDate = date("Y-m-t", strtotime($startDate));

	$employee_sql = $iconn->query("SELECT BioMetricID FROM HrmAuditEmployeeDetails WHERE EmpID=$EmpID");
	$employee_res = $employee_sql->fetch_array();
	$emp_bio_id = $employee_res['BioMetricID'];
	//echo "<h1>$emp_bio_id</h1>";
	$sql = "SELECT BioMetricID, `Date`,
	               GROUP_CONCAT(AttID ORDER BY AttID) AS ids
	        FROM HrmAuditAttendnessAuditLog
	        WHERE `Date` BETWEEN '$startDate' AND '$endDate'
	          AND BioMetricID = $emp_bio_id
	        GROUP BY BioMetricID, `Date`
	        HAVING COUNT(*) > 1";

	$result = $iconn->query($sql);

	if ($result && $result->num_rows > 0) {

	    while ($row = $result->fetch_assoc()) {

	        $ids = explode(',', $row['ids']);

	        $keepId = array_shift($ids);
	        $deleteIds = implode(',', $ids);

	        //echo "Keeping: $keepId<br>";
	        //echo "Deleting: $deleteIds<br>";

	        if (!empty($deleteIds)) {

	            $deleteSql = "DELETE FROM HrmAuditAttendnessAuditLog
	                          WHERE AttID IN ($deleteIds)";

	            if ($iconn->query($deleteSql)) {
	                //echo "Deleted successfully.<hr>";
	            } else {
	                //echo "Delete failed: " . $iconn->error . "<hr>";
	            }
	        }
	    }

	} else {
	    //echo "No duplicate records found.";
	}

?>
