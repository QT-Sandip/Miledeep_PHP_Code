<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");



$Type=$_POST['Type'];


 $sql="SELECT * FROM PpcContractMaster where Type='$Type' and Status=1";
$stmt=$iconn->query($sql);
if($Type==1){
	$SelectType="Select Contractor";
}else{
	$SelectType="Select Mestri";
}

echo '<option value="">'.$SelectType.'</option>';

while($Data=$stmt->fetch_array()){
	

	echo '<option value="'.$Data['ContractorID'].'">'.$Data['ContrctrName'].'</option>';
}





?>