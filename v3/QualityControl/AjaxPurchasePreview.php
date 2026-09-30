<?php
include "../includes/config.php";
$r_id=$_REQUEST['r_id'];
//print($r_id);
//print('test');

$sql="SELECT * FROM `TIME_TRACK` where REFERENCE_ID='$r_id'";
//$sql="SELECT * FROM `TIME_TRACK` where REFERENCE_ID='B009/2'";

$M_P_D_STMT=$iconn->query($sql);
$out=array();

while($M_P_D_ROW=$M_P_D_STMT->fetch_array()){
	$out[]=$M_P_D_ROW;
}

//print(count($result));

//$out = array_values($result);
print(json_encode($out));

?>