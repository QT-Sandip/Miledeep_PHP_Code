<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");



$BUY_ID=$_POST['BUYERID'];
	

if($BUY_ID!=''){
$sql="SELECT * FROM $TblScrmConsigneeDetails where ConID='$BUY_ID'";
}else{
$sql="SELECT VALUE as GRADE FROM `OPTIONS` WHERE DEPT='CS'   
and TYPE='GRADES' order by SortOrder";
}
$stmt=$iconn->query($sql);
$Data =array();
if($stmt){ $Data=$stmt->fetch_array(); }
echo json_encode($Data);




?>
