<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$PageTitle="Barcode List";
$PageTitleFolder="Cold Store";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$month=$_GET["month"];
$year=$_GET["year"];
$date=$_GET["date"];
$fulldate=$year."-".$month."-".$date;
$time = $_GET['time'];


$sql = "SELECT * FROM HrmAuditAttendnessAuditLog WHERE Date >='$fulldate' AND TimeIn>='$time' ORDER BY Date,TimeIn DESC";   
$stmt = $iconn->query($sql); // Execute the first query
while ($row1 = $stmt->fetch_array()) {
    echo $row1['Date']." : ".$row1['TimeIn'];
    echo "<br/>";
    //$Deletesql = "DELETE FROM HrmAuditAttendnessAuditLog WHERE Date >='$fulldate' AND TimeIn>='$time'";
    //$iconn->query($Deletesql);
    //echo "Deleted";
}


exit;
?>
