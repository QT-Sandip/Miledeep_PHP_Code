<?php 
 //session_start();
//error_reporting(0);
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$cid=$_POST['id'];
$sql1="SELECT * FROM `SCRM_CONSIGNEE_DETAILS` WHERE `CONSIGNEE_ID`='$cid'";
//echo "$sql1";
$stmt=$iconn->query($sql1);
$count=$stmt->num_rows;
//echo 'hiiii';
//echo $count;
$cdetails=array();
if($count !=''){
 while($row=$stmt->fetch_assoc()){
	 $cdetails=$row; 
echo json_encode($cdetails);
 }
}
else {
	 echo '<option value="">No Data</option>';

}
?>