<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
 //echo 'hiii';
 

$Type=$_POST['Type'];
$FarmId=$_POST['FarmId'];
 
 
if($Type=='FARMERS'){

$sql="SELECT * FROM `QcTracebilityList` where TraceID='$FarmId'";

$stmt=$iconn->query($sql);
$Data=$stmt->fetch_array();
echo json_encode($Data);


}else{
	
	
}
 
 
?>